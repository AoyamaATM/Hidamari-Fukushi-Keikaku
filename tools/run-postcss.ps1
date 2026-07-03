param(
  [Parameter(ValueFromRemainingArguments = $true)]
  [string[]] $PostcssArgs
)

$ErrorActionPreference = "Stop"
$repoRoot = Split-Path -Parent $PSScriptRoot
$postcssScript = Join-Path $repoRoot "node_modules\postcss-cli\index.js"

if (-not (Test-Path -LiteralPath $postcssScript)) {
  Write-Error "PostCSS CLI is not installed. Run 'pnpm install' first."
}

$nodeCommand = Get-Command node -ErrorAction SilentlyContinue
if ($nodeCommand) {
  $nodePath = $nodeCommand.Source
} else {
  $codexNode = Join-Path $env:USERPROFILE ".cache\codex-runtimes\codex-primary-runtime\dependencies\node\bin\node.exe"
  if (-not (Test-Path -LiteralPath $codexNode)) {
    Write-Error "Node.js was not found on PATH or in the Codex bundled runtime."
  }
  $nodePath = $codexNode
}

& $nodePath $postcssScript @PostcssArgs
exit $LASTEXITCODE
