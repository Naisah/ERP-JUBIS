# Jubis Marketing ERP QA pack

**Current fixes/retest:** [FIX_REPORT.md](FIX_REPORT.md). **Publishing steps:** [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md). Application fixes are local; the hosted site still needs deployment.

Start with [TEST_STRATEGY.md](TEST_STRATEGY.md), [FUNCTIONAL_SCENARIOS.md](FUNCTIONAL_SCENARIOS.md), [ENDPOINT_MATRIX.csv](ENDPOINT_MATRIX.csv), and [RELEASE_CHECKLIST.md](RELEASE_CHECKLIST.md). [EXECUTION_REPORT.md](EXECUTION_REPORT.md) records what actually ran; [KNOWN_FINDINGS.md](KNOWN_FINDINGS.md) lists reproducible release defects.

This pack uses the attached four-page ERP inventory and the current Laravel routes, controllers, migrations and Vue pages. A specification's “production verified” label is not release evidence. Some inequality symbols in the supplied PDF render as question marks; the >=1000 credit threshold and <=10 stock alert threshold are resolved against the implementation and the repository inventory document. The PDF's example real accounts are deliberately not used.

## Isolated automated suite

From the `jubis-erp` repository root (PHP8.2+, existing Composer dependencies and SQLite driver required):

```powershell
./qa/run.ps1
# Baseline behavior only; this is NOT release sign-off:
./qa/run.ps1 -BaselineOnly
```

Equivalent individual commands:

```powershell
php qa/export-routes.php
node qa/build-matrix.mjs
php vendor/bin/phpunit -c qa/phpunit.xml --group baseline --log-junit qa/results/baseline.xml
php vendor/bin/phpunit -c qa/phpunit.xml --group release-gates --log-junit qa/results/release-gates.xml
node qa/frontend-check.mjs
node qa/validate-pack.mjs
php qa/report.php
```

The runner returns nonzero while any required gate fails. Do not skip release-gates to make CI green. Baseline tests document supported contracts; gates assert necessary integrity/security behavior. All previously failing gates pass in the current local candidate. Any future failure blocks sign-off; it is not a successful test. PHPUnit groups use the same guarded database, not a different environment. Node22+ is required for the matrix/browser tools; root npm dependencies are required for Vue compilation. `report.php` reads saved evidence only; rerun after browser/static checks to refresh the report. `validate-pack.mjs` checks generated JSON contracts and document links.

`bootstrap.php` overrides the environment before Laravel loads `.env`, ignores cached production configuration, and forces SQLite `:memory:`. `IsolatedTestCase` refuses migrations unless that exact connection is active. Real migrations create the ephemeral schema; synthetic fixtures replace real customer data. Mail and notifications are faked, queues synchronous, credentials blank and unexpected HTTP prevented. No production migrations, cache clearing, SMTP delivery or courier booking occurs. Test timestamps are fixed for deterministic due dates. CLI tests normally bypass CSRF; a separate test explicitly re-enables it.

JUnit/logs go to ignored `qa/results/`. Keep copies with the release evidence; they may contain synthetic payloads. Production/staging keys, cookies, downloaded invoices and screenshots must not be committed. The route inventory is generated without database access. Refresh it whenever routes change.

## Browser suite

The browser runner is separate from the regular site and its tunnel. It always targets `http://127.0.0.1:9001`, requires an isolated-server response marker plus QA fixture data, and blocks third-party browser requests. `qa/ui/environment.php` guards the exact SQLite file `qa/results/ui.sqlite`, isolates session files, fakes mail and disables external service calls. CSRF is active (`APP_ENV=qa-browser`). The server uses compiled assets and ignores a production/development Vite hot file. It runs public-page rendering, filter/sort persistence, guest login routing, client quote submission, client admin denial, all six staff role logins and selected allowed pages, and JavaScript error checks. This is not full cross-browser or all-module UI coverage.

```powershell
# Only if Playwright is not already supplied by your runtime:
npm --prefix qa install
# Install a browser once, or set QA_BROWSER_PATH to an existing Chrome executable:
./qa/node_modules/.bin/playwright install chromium
php qa/ui/seed.php
# Separate terminal; stop it after testing:
php -S 127.0.0.1:9001 -t public qa/ui/server.php
# Another terminal:
node qa/ui/run.mjs
```

Optional `QA_NODE_MODULES` points to a runtime containing Playwright; `QA_BROWSER_PATH` points to installed Chrome/Edge. The synthetic account pattern is `{role}-ui@example.test`, password `QaTestOnly123!`; these accounts exist only in the guarded QA file. Re-running seed updates fixtures; use a fresh QA file for independent release runs. UI quote submission changes only the QA database. The built-in server and SQLite cannot prove parallel MySQL row-lock behavior. Report queries now run on both SQLite and MySQL; database behavior and concurrency still require MySQL staging validation.

## API collection

Import [api/jubis.postman_collection.json](api/jubis.postman_collection.json) in Postman or Newman. Start with CSRF-cookie and login; keep the cookie jar enabled and redirect following disabled. Set `QA_TARGET=disposable`, `QA_ALLOW_MUTATIONS=true` only on a disposable clone, choose the proper synthetic role, and fill the fixture ID variables. Remote staging additionally requires `QA_ALLOW_REMOTE=true`. The collection's pre-request script guards those choices. It copies the URL-decoded XSRF cookie to `X-XSRF-TOKEN`. Do not commit environment files or credentials.

The collection is a runnable endpoint smoke starter, not an automatic fixture provisioning workflow. Separate guest/auth folders or sessions when sequencing requests; a logged-in user cannot run guest-only forms. Each request requires its described resource state. A signed webhook requires a valid provider signature over the exact substituted raw body. Verification links require valid signed URL query parameters generated by Laravel. Do not run the whole collection blindly in arbitrary order. The PHPUnit suite provides deterministic fixtures and stronger database assertions.

## Concurrency and read-only audits

Prepare a disposable MySQL8 staging clone behind a multi-worker web server. Prepare distinct session cookies and decoded CSRF tokens, then adapt [concurrency/example.json](concurrency/example.json) according to CON-01 through CON-08 in the strategy.

```powershell
$env:QA_TARGET='disposable'
$env:QA_ALLOW_MUTATIONS='true'
# Supply QA_CLIENT_A_COOKIE, QA_CLIENT_A_XSRF, QA_CLIENT_B_COOKIE,
# QA_CLIENT_B_XSRF, QA_WAREHOUSE_COOKIE, QA_WAREHOUSE_XSRF securely.
node qa/concurrency/race.mjs qa/concurrency/example.json
```

The harness issues requests concurrently, verifies initial/final state and refuses cross-origin requests. It requires manual reset of fresh fixtures between races; 302 alone cannot identify business success. The example validates final stock; complete the associated quote status/movement assertions with [sql/reconciliation.mysql.sql](sql/reconciliation.mysql.sql). For other races supply observation paths for invoice amounts, document counts and status. The script is not a load generator and Promise.all does not prove a collision occurred; use a test-only barrier or DB lock to force overlap, repeat at least20 times and collect server timing/transaction evidence. Never use the single-worker browser server for concurrency sign-off.

## Files and evidence

| Artifact | Purpose |
|---|---|
| TEST_STRATEGY.md | Scope, assumptions, test layers, data integrity, security, integration and concurrency rules |
| FUNCTIONAL_SCENARIOS.md | Step-by-step module cases and boundary/negative cases |
| ENDPOINT_MATRIX.md / .csv / data/endpoint-matrix.json | All registered method/endpoint contracts, full schema/auth/rate/timeout test cases |
| tests/*.php | Deterministic HTTP, model, schema, integration and release-gate checks |
| ui/ | Isolated browser fixture server and executable Playwright suite |
| concurrency/ | Guarded real parallel staging request harness and example |
| sql/reconciliation.mysql.sql | Read-only reconciliation/anomaly queries |
| RELEASE_CHECKLIST.md | P0–P3 severity, evidence and sign-off rules |
| KNOWN_FINDINGS.md / EXECUTION_REPORT.md | Observed defects and explicit unexecuted scope |
| HOSTED_QA_REPORT.md | Deployed-site checks, presentation blockers, evidence and remaining release scope |
| PRESENTATION_CHECKLIST.md | Rehearsed hosted demonstration and current limitations |

The original QA pack documented failures before application fixes. The current candidate also changes the application and adds an integrity migration; see FIX_REPORT.md for verified behavior and remaining provider/staging requirements. GitHub/Laravel Cloud will only receive these fixes after publication and deployment.
