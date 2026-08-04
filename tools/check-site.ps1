param(
  [string] $SiteDirectory = "docs"
)

$ErrorActionPreference = "Stop"
$repoRoot = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path
$siteRoot = Join-Path $repoRoot $SiteDirectory
$mainJavaScript = Join-Path $siteRoot "js/main.js"
$errors = [System.Collections.Generic.List[string]]::new()

if (-not (Test-Path -LiteralPath $siteRoot -PathType Container)) {
  throw "Site directory was not found: $siteRoot"
}

$node = Get-Command node -ErrorAction SilentlyContinue
if (-not $node) {
  $errors.Add("Node.js was not found, so JavaScript syntax could not be checked.")
} elseif (-not (Test-Path -LiteralPath $mainJavaScript -PathType Leaf)) {
  $errors.Add("JavaScript entry file was not found: $mainJavaScript")
} else {
  & $node.Source --check $mainJavaScript
  if ($LASTEXITCODE -ne 0) {
    $errors.Add("JavaScript syntax check failed: $mainJavaScript")
  }
}

$htmlFiles = @(Get-ChildItem -LiteralPath $siteRoot -Filter "*.html" -File | Sort-Object Name)
if ($htmlFiles.Count -eq 0) {
  $errors.Add("No HTML files were found: $siteRoot")
}

$descriptionSet = [System.Collections.Generic.HashSet[string]]::new()
$canonicalUrlSet = [System.Collections.Generic.HashSet[string]]::new()
$siteNameSet = [System.Collections.Generic.HashSet[string]]::new()
$openGraphImageSet = [System.Collections.Generic.HashSet[string]]::new()
$requiredOpenGraphProperties = @(
  "og:locale",
  "og:type",
  "og:title",
  "og:description",
  "og:url",
  "og:site_name",
  "og:image",
  "og:image:type",
  "og:image:width",
  "og:image:height",
  "og:image:alt"
)

foreach ($file in $htmlFiles) {
  $html = Get-Content -LiteralPath $file.FullName -Encoding UTF8 -Raw
  $idMatches = [regex]::Matches($html, '\bid\s*=\s*"([^"]+)"')
  $ids = @($idMatches | ForEach-Object { $_.Groups[1].Value })
  $idSet = [System.Collections.Generic.HashSet[string]]::new([string[]] $ids)

  $titleMatches = [regex]::Matches($html, '<title>([^<]+)</title>')
  if ($titleMatches.Count -ne 1) {
    $errors.Add("$($file.Name): expected exactly one title element.")
  } else {
    $title = $titleMatches[0].Groups[1].Value
  }

  $descriptionMatches = [regex]::Matches($html, '<meta name="description" content="([^"]+)">')
  if ($descriptionMatches.Count -ne 1) {
    $errors.Add("$($file.Name): expected exactly one non-empty meta description.")
  } else {
    $description = $descriptionMatches[0].Groups[1].Value
    if (-not $descriptionSet.Add($description)) {
      $errors.Add("$($file.Name): meta description is not unique.")
    }
  }

  $canonicalMatches = [regex]::Matches($html, '<link rel="canonical" href="(https://[^"]+)">')
  if ($canonicalMatches.Count -ne 1) {
    $errors.Add("$($file.Name): expected exactly one absolute HTTPS canonical URL.")
  } else {
    $canonicalUrl = $canonicalMatches[0].Groups[1].Value
    if (-not $canonicalUrlSet.Add($canonicalUrl)) {
      $errors.Add("$($file.Name): canonical URL is not unique.")
    }
  }

  $openGraphValues = @{}
  foreach ($property in $requiredOpenGraphProperties) {
    $pattern = '<meta property="' + [regex]::Escape($property) + '" content="([^"]+)">'
    $propertyMatches = [regex]::Matches($html, $pattern)
    if ($propertyMatches.Count -ne 1) {
      $errors.Add("$($file.Name): expected exactly one non-empty $property value.")
    } else {
      $openGraphValues[$property] = $propertyMatches[0].Groups[1].Value
    }
  }

  if ($titleMatches.Count -eq 1 -and $openGraphValues["og:title"] -ne $title) {
    $errors.Add("$($file.Name): og:title does not match title.")
  }
  if ($titleMatches.Count -eq 1 -and $openGraphValues.ContainsKey("og:site_name")) {
    $siteName = $openGraphValues["og:site_name"]
    [void] $siteNameSet.Add($siteName)
    if (-not ($title.EndsWith(" | $siteName"))) {
      $errors.Add("$($file.Name): title does not use its og:site_name as the suffix.")
    }
  }
  if ($descriptionMatches.Count -eq 1 -and $openGraphValues["og:description"] -ne $description) {
    $errors.Add("$($file.Name): og:description does not match meta description.")
  }
  if ($canonicalMatches.Count -eq 1 -and $openGraphValues["og:url"] -ne $canonicalUrl) {
    $errors.Add("$($file.Name): og:url does not match the canonical URL.")
  }
  if ($openGraphValues.ContainsKey("og:image")) {
    $openGraphImage = $openGraphValues["og:image"]
    [void] $openGraphImageSet.Add($openGraphImage)
    if ($openGraphImage -notmatch '^https://') {
      $errors.Add("$($file.Name): og:image is not an absolute HTTPS URL.")
    }
  }
  if ($html -notmatch '<link rel="icon"[^>]*href="[^"]+"[^>]*>') {
    $errors.Add("$($file.Name): favicon link is missing.")
  }

  $ids |
    Group-Object |
    Where-Object { $_.Count -gt 1 } |
    ForEach-Object {
      $errors.Add("$($file.Name): duplicate id '$($_.Name)'.")
    }

  [regex]::Matches($html, '\baria-controls\s*=\s*"([^"]+)"') | ForEach-Object {
    $targetId = $_.Groups[1].Value
    if (-not $idSet.Contains($targetId)) {
      $errors.Add("$($file.Name): aria-controls references missing id '$targetId'.")
    }
  }

  [regex]::Matches($html, '<label\b[^>]*\bfor\s*=\s*"([^"]+)"[^>]*>') | ForEach-Object {
    $targetId = $_.Groups[1].Value
    if (-not $idSet.Contains($targetId)) {
      $errors.Add("$($file.Name): label for references missing id '$targetId'.")
    }
  }

  [regex]::Matches($html, '<img\b[^>]*>') | ForEach-Object {
    if ($_.Value -notmatch '\balt\s*=\s*"[^"]*"') {
      $errors.Add("$($file.Name): image is missing an alt attribute: $($_.Value)")
    }
  }

  [regex]::Matches($html, '\b(?:src|href)\s*=\s*"([^"]+)"') | ForEach-Object {
    $reference = $_.Groups[1].Value
    if (-not $reference -or $reference -match '^(?:[a-z][a-z0-9+.-]*:|//|#)') {
      return
    }

    $localReference = ($reference -replace '[?#].*$', '')
    if (-not $localReference) {
      return
    }

    $targetPath = Join-Path $file.DirectoryName $localReference
    if (-not (Test-Path -LiteralPath $targetPath)) {
      $errors.Add("$($file.Name): local reference was not found: $reference")
    }
  }

  if ($html -notmatch '<body\b[^>]*\bdata-type\s*=') {
    $errors.Add("$($file.Name): body is missing data-type.")
  }
  if ($html -notmatch '<header\b[^>]*\bdata-site-header\b') {
    $errors.Add("$($file.Name): shared header host is missing.")
  }
  if ($html -notmatch '<footer\b[^>]*\bdata-site-footer\b') {
    $errors.Add("$($file.Name): shared footer host is missing.")
  }
  if (-not $idSet.Contains("main-content")) {
    $errors.Add("$($file.Name): main-content skip target is missing.")
  }
}

if ($siteNameSet.Count -ne 1) {
  $errors.Add("Open Graph site names are not consistent across all HTML files.")
}
if ($openGraphImageSet.Count -ne 1) {
  $errors.Add("Open Graph images are not consistent across all HTML files.")
}

if ($errors.Count -gt 0) {
  $errors | ForEach-Object { Write-Host "ERROR: $_" -ForegroundColor Red }
  throw "Site validation failed with $($errors.Count) error(s)."
}

Write-Host "Site validation passed for $($htmlFiles.Count) HTML files and js/main.js."
