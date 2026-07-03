param(
  [int] $IntervalSeconds = 1,
  [switch] $Once
)

$ErrorActionPreference = "Stop"
$repoRoot = Split-Path -Parent $PSScriptRoot
$scssRoot = Join-Path $repoRoot "hidamari-fukushi-keikaku\scss"
$buildCss = Join-Path $PSScriptRoot "build-css.ps1"

function Get-ScssStamp {
  Get-ChildItem -LiteralPath $scssRoot -Recurse -Filter "*.scss" |
    Sort-Object FullName |
    ForEach-Object { "$($_.FullName)|$($_.LastWriteTimeUtc.Ticks)|$($_.Length)" }
}

function Invoke-MainCssBuild {
  & $buildCss main
  if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
}

Invoke-MainCssBuild
if ($Once) { exit 0 }

Write-Host "Watching hidamari-fukushi-keikaku/scss for changes. Press Ctrl+C to stop."
$lastStamp = Get-ScssStamp

while ($true) {
  Start-Sleep -Seconds $IntervalSeconds
  $nextStamp = Get-ScssStamp
  if (Compare-Object -ReferenceObject $lastStamp -DifferenceObject $nextStamp) {
    Invoke-MainCssBuild
    $lastStamp = $nextStamp
  }
}
