param(
  [string] $OutputDir = "visual-check"
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

$target = Resolve-RepoPath $OutputDir
$repoWithSeparator = $repoRoot.TrimEnd("\") + "\"
if ($target -eq $repoRoot -or -not $target.StartsWith($repoWithSeparator, [System.StringComparison]::OrdinalIgnoreCase)) {
  throw "Refusing to clean outside the repository: $target"
}

if (-not (Test-Path -LiteralPath $target)) {
  Write-Host "Nothing to clean: $target"
  exit 0
}

Get-ChildItem -LiteralPath $target -Force | Remove-Item -Recurse -Force
Write-Host "Cleaned $target"
