param(
  [ValidateSet("all", "pc", "sp")]
  [string] $Viewport = "pc",
  [string[]] $PageId = @(),
  [string] $OutputDir = "visual-check",
  [string] $PagesFile = "tools/visual-check-pages.json",
  [string] $ChromePath = ""
)

$ErrorActionPreference = "Stop"
$repoRoot = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path

function Resolve-RepoPath {
  param([string] $Path)

  if ([System.IO.Path]::IsPathRooted($Path)) {
    return $Path
  }

  return (Join-Path $repoRoot $Path)
}

function Resolve-Chrome {
  param([string] $ExplicitPath)

  $candidates = @()
  if ($ExplicitPath) { $candidates += $ExplicitPath }
  if ($env:CHROME_PATH) { $candidates += $env:CHROME_PATH }
  $candidates += @(
    "C:\Program Files\Google\Chrome\Application\chrome.exe",
    "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe"
  )
  if ($env:LOCALAPPDATA) {
    $candidates += (Join-Path $env:LOCALAPPDATA "Google\Chrome\Application\chrome.exe")
  }

  foreach ($candidate in $candidates) {
    if ($candidate -and (Test-Path -LiteralPath $candidate)) {
      return (Resolve-Path -LiteralPath $candidate).Path
    }
  }

  throw "Chrome executable was not found. Set CHROME_PATH or pass -ChromePath."
}

$pagesPath = Resolve-RepoPath $PagesFile
$rawPages = Get-Content -LiteralPath $pagesPath -Encoding UTF8 -Raw | ConvertFrom-Json
$pages = @()
foreach ($page in $rawPages) {
  $pages += $page
}
if ($PageId.Count -gt 0) {
  $requestedPageIds = @(
    $PageId |
      ForEach-Object { $_ -split "," } |
      ForEach-Object { $_.Trim() } |
      Where-Object { $_ }
  )
  $pages = @($pages | Where-Object { $requestedPageIds -contains [string]$_.id })
}
if ($pages.Count -eq 0) {
  throw "No pages matched the requested IDs."
}

$viewports = @(
  [pscustomobject]@{ Id = "pc"; Width = 1600; Height = 9000 },
  [pscustomobject]@{ Id = "sp"; Width = 390; Height = 12000 }
)
if ($Viewport -ne "all") {
  $viewports = @($viewports | Where-Object { $_.Id -eq $Viewport })
}

$chrome = Resolve-Chrome $ChromePath
$outputRoot = Resolve-RepoPath $OutputDir
$profileRoot = Resolve-RepoPath ".chrome-check"
New-Item -ItemType Directory -Force -Path $outputRoot | Out-Null
New-Item -ItemType Directory -Force -Path $profileRoot | Out-Null

foreach ($page in $pages) {
  $pagePath = Resolve-RepoPath $page.path
  if (-not (Test-Path -LiteralPath $pagePath)) {
    throw "Page file was not found: $($page.path)"
  }

  $pageUrl = ([System.Uri](Resolve-Path -LiteralPath $pagePath).Path).AbsoluteUri

  foreach ($viewportItem in $viewports) {
    $screenshotPath = Join-Path $outputRoot "$($page.id)-$($viewportItem.Id).png"
    $profilePath = Join-Path $profileRoot "$($page.id)-$($viewportItem.Id)"
    New-Item -ItemType Directory -Force -Path $profilePath | Out-Null

    $chromeArgs = @(
      "--headless=new",
      "--disable-gpu",
      "--no-first-run",
      "--window-size=$($viewportItem.Width),$($viewportItem.Height)",
      "--user-data-dir=$profilePath",
      "--screenshot=$screenshotPath",
      $pageUrl
    )

    $startedAt = Get-Date
    & $chrome @chromeArgs
    $chromeExitCode = $LASTEXITCODE
    $screenshot = $null
    for ($attempt = 0; $attempt -lt 40; $attempt++) {
      $candidate = Get-Item -LiteralPath $screenshotPath -ErrorAction SilentlyContinue
      if ($candidate -and $candidate.Length -gt 0 -and $candidate.LastWriteTime -ge $startedAt.AddSeconds(-2)) {
        $screenshot = $candidate
        break
      }
      Start-Sleep -Milliseconds 250
    }
    if (-not $screenshot -or $screenshot.Length -le 0) {
      throw "Screenshot failed: $screenshotPath"
    }
    if ($null -ne $chromeExitCode -and $chromeExitCode -ne 0) {
      Write-Warning "Chrome exited with code $chromeExitCode, but screenshot was created: $screenshotPath"
    }

    Write-Host ("Saved {0} ({1:n0} bytes)" -f (Resolve-Path -LiteralPath $screenshotPath).Path, $screenshot.Length)
  }
}
