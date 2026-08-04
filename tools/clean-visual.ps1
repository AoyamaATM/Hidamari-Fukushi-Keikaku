param(
  [string] $OutputDir = "visual-check",
  [string] $ProfileDir = ".chrome-check"
)

$ErrorActionPreference = "Stop"
$repoRoot = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path

function Resolve-RepoPath {
  param([string] $Path)

  if ([System.IO.Path]::IsPathRooted($Path)) {
    return [System.IO.Path]::GetFullPath($Path)
  }

  return [System.IO.Path]::GetFullPath((Join-Path $repoRoot $Path))
}

$repoWithSeparator = $repoRoot.TrimEnd("\") + "\"
$targets = @($OutputDir, $ProfileDir)

foreach ($path in $targets) {
  $target = Resolve-RepoPath $path
  if ($target -eq $repoRoot -or -not $target.StartsWith($repoWithSeparator, [System.StringComparison]::OrdinalIgnoreCase)) {
    throw "Refusing to clean outside the repository: $target"
  }

  if (-not (Test-Path -LiteralPath $target)) {
    Write-Host "Nothing to clean: $target"
    continue
  }

  Remove-Item -LiteralPath $target -Recurse -Force
  Write-Host "Cleaned $target"
}
