param(
  [ValidateSet("all", "main", "scss", "min")]
  [string] $Target = "all"
)

$ErrorActionPreference = "Stop"
$runSass = Join-Path $PSScriptRoot "run-sass.ps1"
$runPostcss = Join-Path $PSScriptRoot "run-postcss.ps1"
$sourceScss = "docs/scss/style.scss"

function Invoke-Step {
  param(
    [string] $Command,
    [string[]] $Arguments
  )

  & $Command @Arguments
  if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
}

function Build-CssTarget {
  param(
    [string] $Output,
    [string] $Style,
    [bool] $SourceMap
  )

  $sassArgs = @("--style=$Style")
  if ($SourceMap) {
    $sassArgs += "--source-map"
  } else {
    $sassArgs += "--no-source-map"
  }
  $sassArgs += @($sourceScss, $Output)
  Invoke-Step $runSass $sassArgs

  $postcssArgs = @($Output, "--replace")
  if ($SourceMap) {
    $postcssArgs += "--map"
  } else {
    $postcssArgs += "--no-map"
  }
  Invoke-Step $runPostcss $postcssArgs
}

switch ($Target) {
  "all" {
    Build-CssTarget "docs/css/style.css" "expanded" $false
    Build-CssTarget "docs/scss/style.css" "expanded" $true
    Build-CssTarget "docs/scss/style.min.css" "compressed" $true
  }
  "main" {
    Build-CssTarget "docs/css/style.css" "expanded" $false
  }
  "scss" {
    Build-CssTarget "docs/scss/style.css" "expanded" $true
  }
  "min" {
    Build-CssTarget "docs/scss/style.min.css" "compressed" $true
  }
}
