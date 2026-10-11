param([switch]$BaselineOnly)
$ErrorActionPreference = 'Stop'
$repoRoot = Split-Path -Parent $PSScriptRoot
Push-Location -LiteralPath $repoRoot
try {
    New-Item -ItemType Directory -Force -Path 'qa/results' | Out-Null
    & php qa/export-routes.php
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
    & node qa/build-matrix.mjs
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
    & php vendor/bin/phpunit -c qa/phpunit.xml --group baseline --log-junit qa/results/baseline.xml 2>&1 | Tee-Object -FilePath qa/results/baseline.txt
    $baselineCode = $LASTEXITCODE
    $gateCode = 0
    if (!$BaselineOnly) {
        & php vendor/bin/phpunit -c qa/phpunit.xml --group release-gates --log-junit qa/results/release-gates.xml 2>&1 | Tee-Object -FilePath qa/results/release-gates.txt
        $gateCode = $LASTEXITCODE
        & php qa/report.php
        if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
    }
    if ($baselineCode -ne 0 -or $gateCode -ne 0) { exit 1 }
    exit 0
} finally { Pop-Location }
