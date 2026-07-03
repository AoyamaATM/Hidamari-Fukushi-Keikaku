$ErrorActionPreference = "Stop"
$runSass = Join-Path $PSScriptRoot "run-sass.ps1"

& $runSass --style=expanded --no-source-map `
  "hidamari-fukushi-keikaku/scss/style.scss" `
  "hidamari-fukushi-keikaku/css/style.css"
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

& $runSass --style=expanded --source-map `
  "hidamari-fukushi-keikaku/scss/style.scss" `
  "hidamari-fukushi-keikaku/scss/style.css"
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

& $runSass --style=compressed --source-map `
  "hidamari-fukushi-keikaku/scss/style.scss" `
  "hidamari-fukushi-keikaku/scss/style.min.css"
exit $LASTEXITCODE
