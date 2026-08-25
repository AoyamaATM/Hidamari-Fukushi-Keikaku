[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [string] $ArchivePath,

    [ValidateRange(1025, 65535)]
    [int] $Port = 8097,

    [switch] $KeepArtifacts
)

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

$archive = (Resolve-Path -LiteralPath $ArchivePath).Path
$match = [regex]::Match((Split-Path $archive -Leaf), 'wpvivid-(?<suffix>[a-z0-9]+)_')
if (-not $match.Success) {
    throw 'The archive name does not contain a WPvivid task ID.'
}

$suffix = $match.Groups['suffix'].Value
$restoreDatabase = "hidamari_restore_$suffix"
if ($restoreDatabase -notmatch '^hidamari_restore_[a-z0-9]+$') {
    throw 'The disposable database name failed validation.'
}

$restoreRoot = Join-Path $env:TEMP "hidamari-wpvivid-restore-$suffix"
$resolvedTemp = [IO.Path]::GetFullPath($env:TEMP).TrimEnd('\')
$resolvedRestoreRoot = [IO.Path]::GetFullPath($restoreRoot)
if (-not $resolvedRestoreRoot.StartsWith($resolvedTemp + '\', [StringComparison]::OrdinalIgnoreCase)) {
    throw 'The disposable restore path is outside the system temporary directory.'
}
if (Test-Path -LiteralPath $restoreRoot) {
    throw "The disposable restore path already exists: $restoreRoot"
}

$php = Join-Path $env:APPDATA 'Local\lightning-services\php-8.2.29+0\bin\win64\php.exe'
$phpIni = Join-Path $env:APPDATA 'Local\run\pg7nSqFfX\conf\php\php.ini'
$wpCli = Join-Path $env:LOCALAPPDATA 'Programs\Local\resources\extraResources\bin\wp-cli\wp-cli.phar'
$mysql = Join-Path $env:APPDATA 'Local\lightning-services\mysql-8.4.0\bin\win64\bin\mysql.exe'
$router = Join-Path $PSScriptRoot 'wp-restore-router.php'
$stateCheck = Join-Path $PSScriptRoot 'local-restored-site-check.php'
$baseUrl = "http://127.0.0.1:$Port"
$serverProcess = $null
$databaseCreated = $false
$temporaryAdminId = 0
$result = [ordered]@{}
$resultJson = $null

foreach ($requiredFile in @($archive, $php, $phpIni, $wpCli, $mysql, $router, $stateCheck)) {
    if (-not (Test-Path -LiteralPath $requiredFile -PathType Leaf)) {
        throw "Required file not found: $requiredFile"
    }
}

function Get-WpConfigValue {
    param(
        [Parameter(Mandatory = $true)] [string] $Config,
        [Parameter(Mandatory = $true)] [string] $Name
    )

    $pattern = 'define\s*\(\s*[''"]{0}[''"]\s*,\s*[''"](?<value>[^''"]*)[''"]\s*\)' -f [regex]::Escape($Name)
    $valueMatch = [regex]::Match($Config, $pattern)
    if (-not $valueMatch.Success) {
        throw "Could not read $Name from the restored wp-config.php."
    }
    return $valueMatch.Groups['value'].Value
}

function Invoke-Wp {
    param([Parameter(Mandatory = $true)] [string[]] $Arguments)

    $output = & $php -c $phpIni $wpCli "--path=$wordpressRoot" @Arguments
    if ($LASTEXITCODE -ne 0) {
        throw "WP-CLI failed: wp $($Arguments -join ' ')"
    }
    return $output
}

function Expand-ZipSafely {
    param(
        [Parameter(Mandatory = $true)] [string] $Source,
        [Parameter(Mandatory = $true)] [string] $Destination
    )

    Add-Type -AssemblyName System.IO.Compression.FileSystem
    $destinationPath = [IO.Path]::GetFullPath($Destination).TrimEnd('\')
    [IO.Directory]::CreateDirectory($destinationPath) | Out-Null
    $archiveFile = [IO.Compression.ZipFile]::OpenRead($Source)
    try {
        foreach ($entry in $archiveFile.Entries) {
            $entryPath = $entry.FullName.Replace('/', [IO.Path]::DirectorySeparatorChar)
            $targetPath = [IO.Path]::GetFullPath((Join-Path $destinationPath $entryPath))
            if (-not $targetPath.StartsWith($destinationPath + '\', [StringComparison]::OrdinalIgnoreCase)) {
                throw "Unsafe ZIP entry path: $($entry.FullName)"
            }
            if ([string]::IsNullOrEmpty($entry.Name)) {
                [IO.Directory]::CreateDirectory($targetPath) | Out-Null
                continue
            }

            [IO.Directory]::CreateDirectory((Split-Path $targetPath -Parent)) | Out-Null
            $inputStream = $entry.Open()
            $outputStream = [IO.File]::Open($targetPath, [IO.FileMode]::Create, [IO.FileAccess]::Write, [IO.FileShare]::None)
            try {
                $inputStream.CopyTo($outputStream)
            } finally {
                $outputStream.Dispose()
                $inputStream.Dispose()
            }
        }
    } finally {
        $archiveFile.Dispose()
    }
}

try {
    $packageRoot = Join-Path $restoreRoot '_packages'
    $wordpressRoot = Join-Path $restoreRoot 'wordpress'
    $databaseRoot = Join-Path $restoreRoot '_database'
    New-Item -ItemType Directory -Path $packageRoot, $wordpressRoot, $databaseRoot -Force | Out-Null
    Expand-ZipSafely -Source $archive -Destination $packageRoot

    $coreArchive = Get-ChildItem -LiteralPath $packageRoot -Filter '*_backup_core.zip' -File -ErrorAction Stop | Select-Object -First 1
    $databaseArchive = Get-ChildItem -LiteralPath $packageRoot -Filter '*_backup_db.zip' -File -ErrorAction Stop | Select-Object -First 1
    Expand-ZipSafely -Source $coreArchive.FullName -Destination $wordpressRoot
    Expand-ZipSafely -Source $databaseArchive.FullName -Destination $databaseRoot

    $wpContent = Join-Path $wordpressRoot 'wp-content'
    New-Item -ItemType Directory -Path $wpContent -Force | Out-Null
    foreach ($kind in @('themes', 'plugin', 'uploads', 'content')) {
        $component = Get-ChildItem -LiteralPath $packageRoot -Filter "*_backup_$kind.zip" -File -ErrorAction Stop | Select-Object -First 1
        Expand-ZipSafely -Source $component.FullName -Destination $wpContent
    }

    $configPath = Join-Path $wordpressRoot 'wp-config.php'
    $config = Get-Content -Raw -LiteralPath $configPath
    $dbUser = Get-WpConfigValue -Config $config -Name 'DB_USER'
    $dbPassword = Get-WpConfigValue -Config $config -Name 'DB_PASSWORD'
    $env:MYSQL_PWD = $dbPassword
    $mysqlArgs = @('--host=127.0.0.1', '--port=10031', '--protocol=tcp', "--user=$dbUser", '--default-character-set=utf8mb4', '--batch', '--skip-column-names')

    $existingDatabase = & $mysql @mysqlArgs "--execute=SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME='$restoreDatabase';"
    if ($LASTEXITCODE -ne 0) {
        throw 'Could not inspect the disposable MySQL database namespace.'
    }
    if ($existingDatabase) {
        throw "The disposable database already exists: $restoreDatabase"
    }

    & $mysql @mysqlArgs "--execute=CREATE DATABASE ``$restoreDatabase`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;"
    if ($LASTEXITCODE -ne 0) {
        throw 'Could not create the disposable restore database.'
    }
    $databaseCreated = $true

    $sqlFile = Get-ChildItem -LiteralPath $databaseRoot -Filter '*.sql' -File -ErrorAction Stop | Select-Object -First 1
    $sqlPath = $sqlFile.FullName.Replace('\', '/')
    & $mysql @mysqlArgs "--database=$restoreDatabase" "--execute=SOURCE $sqlPath;"
    if ($LASTEXITCODE -ne 0) {
        throw 'Could not import the WPvivid SQL package.'
    }

    Invoke-Wp -Arguments @('config', 'set', 'DB_NAME', $restoreDatabase, '--type=constant', '--quiet') | Out-Null
    Invoke-Wp -Arguments @('search-replace', 'http://hidamari-care-asahikawa.local', $baseUrl, '--all-tables-with-prefix', '--skip-columns=guid', '--quiet') | Out-Null
    Invoke-Wp -Arguments @('option', 'update', 'home', $baseUrl, '--quiet') | Out-Null
    Invoke-Wp -Arguments @('option', 'update', 'siteurl', $baseUrl, '--quiet') | Out-Null
    Invoke-Wp -Arguments @('rewrite', 'flush', '--hard', '--quiet') | Out-Null

    $stateOutput = Invoke-Wp -Arguments @('eval-file', $stateCheck)
    $stateText = $stateOutput -join "`n"
    $jsonStart = $stateText.IndexOf('{')
    $jsonEnd = $stateText.LastIndexOf('}')
    if ($jsonStart -lt 0 -or $jsonEnd -lt $jsonStart) {
        throw 'The restored-site state check did not return JSON.'
    }
    $stateJson = $stateText.Substring($jsonStart, $jsonEnd - $jsonStart + 1) | ConvertFrom-Json
    $result['state_checks'] = "$($stateJson.passed)/$($stateJson.total)"
    $result['form_id'] = $stateJson.form_id
    $result['form_entries'] = $stateJson.entry_count

    $temporaryLogin = "restore_check_$suffix"
    $temporaryEmail = "$temporaryLogin@example.invalid"
    $passwordBytes = New-Object byte[] 24
    $passwordGenerator = [Security.Cryptography.RandomNumberGenerator]::Create()
    try {
        $passwordGenerator.GetBytes($passwordBytes)
    } finally {
        $passwordGenerator.Dispose()
    }
    $temporaryPassword = [Convert]::ToBase64String($passwordBytes)
    $userOutput = Invoke-Wp -Arguments @('user', 'create', $temporaryLogin, $temporaryEmail, '--role=administrator', "--user_pass=$temporaryPassword", '--porcelain')
    $temporaryAdminId = [int](($userOutput | Select-Object -Last 1).Trim())
    if ($temporaryAdminId -le 0) {
        throw 'Could not create the disposable administrator.'
    }

    $stdoutLog = Join-Path $restoreRoot 'php-server.stdout.log'
    $stderrLog = Join-Path $restoreRoot 'php-server.stderr.log'
    $serverArguments = @('-c', $phpIni, '-S', "127.0.0.1:$Port", '-t', $wordpressRoot, $router)
    $serverProcess = Start-Process -FilePath $php -ArgumentList $serverArguments -PassThru -WindowStyle Hidden -RedirectStandardOutput $stdoutLog -RedirectStandardError $stderrLog

    $ready = $false
    for ($attempt = 0; $attempt -lt 30; $attempt++) {
        Start-Sleep -Milliseconds 500
        try {
            $response = Invoke-WebRequest -Uri "$baseUrl/" -MaximumRedirection 5 -TimeoutSec 3
            if ($response.StatusCode -eq 200) {
                $ready = $true
                break
            }
        } catch {
            if ($serverProcess.HasExited) {
                break
            }
        }
    }
    if (-not $ready) {
        throw 'The disposable restored site did not start.'
    }

    $paths = @('/', '/about-us/', '/facilities/', '/price/', '/faq/', '/contact/', '/news/', '/category/news/', '/2026/06/', '/july-business-days-2026/', '/privacy-policy/', '/wp-sitemap.xml')
    $httpResults = foreach ($path in $paths) {
        $page = Invoke-WebRequest -Uri ($baseUrl + $path) -MaximumRedirection 5 -TimeoutSec 10
        [pscustomobject]@{
            path = $path
            status = $page.StatusCode
            demo_notice = $page.Content.Contains('class="demo-site-notice"')
            leaked_original_url = $page.Content.Contains('http://hidamari-care-asahikawa.local')
            php_error = $page.Content -match 'PHP (Warning|Fatal error|Parse error)'
        }
    }
    $httpFailures = @($httpResults | Where-Object { $_.status -ne 200 -or $_.leaked_original_url -or $_.php_error })
    if ($httpFailures.Count -gt 0) {
        throw "Restored public URL checks failed: $($httpFailures.path -join ', ')"
    }
    $result['public_urls'] = "$($httpResults.Count)/$($httpResults.Count)"
    $result['demo_notice_pages'] = @($httpResults | Where-Object { $_.demo_notice }).Count

    $webSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    Invoke-WebRequest -Uri "$baseUrl/wp-login.php" -WebSession $webSession -TimeoutSec 10 | Out-Null
    $loginResponse = Invoke-WebRequest -Uri "$baseUrl/wp-login.php" -Method Post -WebSession $webSession -Body @{
        log = $temporaryLogin
        pwd = $temporaryPassword
        'wp-submit' = 'Log In'
        redirect_to = "$baseUrl/wp-admin/"
        testcookie = '1'
    } -MaximumRedirection 5 -TimeoutSec 10
    $dashboard = Invoke-WebRequest -Uri "$baseUrl/wp-admin/" -WebSession $webSession -MaximumRedirection 5 -TimeoutSec 10
    $loginHasAdminBar = $loginResponse.Content.Contains('wpadminbar')
    $dashboardHasAdminBar = $dashboard.Content.Contains('wpadminbar')
    $dashboardHasWidgets = $dashboard.Content.Contains('dashboard-widgets-wrap')
    $adminLoginSucceeded = $loginResponse.StatusCode -eq 200 -and $dashboard.StatusCode -eq 200 -and $dashboardHasAdminBar -and $dashboardHasWidgets
    if (-not $adminLoginSucceeded) {
        $loginUri = $loginResponse.BaseResponse.RequestMessage.RequestUri.AbsoluteUri
        $dashboardUri = $dashboard.BaseResponse.RequestMessage.RequestUri.AbsoluteUri
        throw "The disposable administrator could not reach the restored dashboard. login_status=$($loginResponse.StatusCode) dashboard_status=$($dashboard.StatusCode) login_adminbar=$loginHasAdminBar dashboard_adminbar=$dashboardHasAdminBar dashboard_widgets=$dashboardHasWidgets login_uri=$loginUri dashboard_uri=$dashboardUri"
    }
    $result['admin_login'] = 'passed'

    Invoke-Wp -Arguments @('user', 'delete', "$temporaryAdminId", '--yes', '--quiet') | Out-Null
    $temporaryAdminId = 0

    $result['archive_sha256'] = (Get-FileHash -Algorithm SHA256 -LiteralPath $archive).Hash
    $result['database'] = $restoreDatabase
    $result['base_url'] = $baseUrl
    $result['cleanup_planned'] = -not $KeepArtifacts
    $resultJson = [pscustomobject]$result | ConvertTo-Json -Depth 5
} finally {
    if ($serverProcess -and -not $serverProcess.HasExited) {
        Stop-Process -Id $serverProcess.Id -Force
        $serverProcess.WaitForExit()
    }
    if ($serverProcess) {
        $serverProcess.Dispose()
    }
    if ($temporaryAdminId -gt 0 -and $databaseCreated) {
        try {
            Invoke-Wp -Arguments @('user', 'delete', "$temporaryAdminId", '--yes', '--quiet') | Out-Null
        } catch {
        }
    }
    if ($databaseCreated) {
        if ($restoreDatabase -notmatch '^hidamari_restore_[a-z0-9]+$') {
            throw 'Refusing to remove a database outside the disposable restore namespace.'
        }
        & $mysql @mysqlArgs "--execute=DROP DATABASE ``$restoreDatabase``;"
        if ($LASTEXITCODE -ne 0) {
            throw "Could not remove the disposable database: $restoreDatabase"
        }
    }
    Remove-Item Env:MYSQL_PWD -ErrorAction SilentlyContinue
    if (-not $KeepArtifacts -and (Test-Path -LiteralPath $restoreRoot)) {
        $cleanupPath = [IO.Path]::GetFullPath($restoreRoot)
        if ($cleanupPath.StartsWith($resolvedTemp + '\', [StringComparison]::OrdinalIgnoreCase) -and (Split-Path $cleanupPath -Leaf) -eq "hidamari-wpvivid-restore-$suffix") {
            for ($cleanupAttempt = 0; $cleanupAttempt -lt 5 -and (Test-Path -LiteralPath $cleanupPath); $cleanupAttempt++) {
                try {
                    Remove-Item -LiteralPath $cleanupPath -Recurse -Force -ErrorAction Stop
                } catch {
                    if ($cleanupAttempt -eq 4) {
                        throw
                    }
                    Start-Sleep -Milliseconds 300
                }
            }
        } else {
            throw 'Refusing to remove an unexpected restore path.'
        }
    }
}

if ($null -ne $resultJson) {
    $resultJson
}
