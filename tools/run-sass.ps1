param(
  [Parameter(ValueFromRemainingArguments = $true)]
  [string[]] $SassArgs
)

$ErrorActionPreference = "Stop"
$repoRoot = Split-Path -Parent $PSScriptRoot
$sassScript = Join-Path $repoRoot "node_modules\sass\sass.js"

if (-not (Test-Path -LiteralPath $sassScript)) {
  Write-Error "Sass is not installed. Run 'pnpm install' first."
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

& $nodePath $sassScript @SassArgs
exit $LASTEXITCODE
