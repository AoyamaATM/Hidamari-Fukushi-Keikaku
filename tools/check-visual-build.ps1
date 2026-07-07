param(
  [ValidateSet("all", "pc", "sp")]
  [string] $Viewport = "pc",
  [string[]] $PageId = @(),
  [string] $OutputDir = "visual-check",
  [string] $PagesFile = "tools/visual-check-pages.json",
  [string] $ChromePath = ""
)

$ErrorActionPreference = "Stop"
$buildScript = Join-Path $PSScriptRoot "build-css.ps1"
$checkScript = Join-Path $PSScriptRoot "check-pages.ps1"

& $buildScript
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

$checkParams = @{
  Viewport = $Viewport
  OutputDir = $OutputDir
  PagesFile = $PagesFile
}
if ($PageId.Count -gt 0) {
  $checkParams.PageId = $PageId
}
if ($ChromePath) {
  $checkParams.ChromePath = $ChromePath
}

& $checkScript @checkParams
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
