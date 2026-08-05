param(
  [int] $IntervalSeconds = 1,
  [switch] $Once,
  [ValidateSet("static", "wordpress")]
  [string] $Scope = "static"
)

$ErrorActionPreference = "Stop"
$repoRoot = Split-Path -Parent $PSScriptRoot
$buildCss = Join-Path $PSScriptRoot "build-css.ps1"

if ($Scope -eq "wordpress") {
  $scssRoot = Join-Path $repoRoot "wordpress\themes\hidamari-care-asahikawa\assets\scss"
  $buildTarget = "wordpress"
} else {
  $scssRoot = Join-Path $repoRoot "docs\scss"
  $buildTarget = "main"
}

function Get-ScssStamp {
  Get-ChildItem -LiteralPath $scssRoot -Recurse -Filter "*.scss" |
    Sort-Object FullName |
    ForEach-Object { "$($_.FullName)|$($_.LastWriteTimeUtc.Ticks)|$($_.Length)" }
}

function Invoke-MainCssBuild {
  & $buildCss $buildTarget
  if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
}

Invoke-MainCssBuild
if ($Once) { exit 0 }

Write-Host "Watching $scssRoot for changes. Press Ctrl+C to stop."
$lastStamp = Get-ScssStamp

while ($true) {
  Start-Sleep -Seconds $IntervalSeconds
  $nextStamp = Get-ScssStamp
  if (Compare-Object -ReferenceObject $lastStamp -DifferenceObject $nextStamp) {
    Invoke-MainCssBuild
    $lastStamp = $nextStamp
  }
}
