# QA execution report

> Historical pre-fix evidence. Local application fixes now pass the complete QA suite; see [FIX_REPORT.md](FIX_REPORT.md). The hosted website has not yet been redeployed or retested with this candidate.

Evidence snapshot generated 2026-10-12T00:53:56+08:00. This is a test-plan delivery and an isolated local execution, not production sign-off. Results below come from saved artifacts; rerun report.php after fresh test runs. The candidate commit and deployment metadata must still be attached at release.

| Check | Actual outcome | Evidence / scope |
|---|---|---|
| Baseline PHPUnit | 349/349 pass; 1357 assertions; 0 failures; 0 errors | qa/results/baseline.xml; 273 middleware role cases and 76 other HTTP/workflow/integration/validation cases |
| Required release gates | 29 tests; 29 failures; 0 errors; 0 skipped | qa/results/release-gates.xml; known defects remain failing |
| Browser checks | 16/16 pass; executed 2026-10-12T00:43:55+08:00 | qa/results/ui/report.json; Chromium, isolated SQLite QA server, blocked external services |
| Route/matrix inventory | 77 routes; 81 method/endpoint rows | Includes PUT/PATCH independently; inventory is planned coverage, not all endpoint HTTP execution |
| Vue static compilation | 54 components; 0 errors | qa/results/frontend.json; syntax/template checks only, not browser correctness |

Release status: **BLOCKED** while mandatory gate failures or unexecuted staging criteria remain. Do not substitute baseline-only success for release-gate success.

## What the automated results demonstrate

Real migrations/validations and in-process Laravel HTTP workflow actions execute against SQLite :memory: with synthetic fixtures. Successful checks include quote/VAT/stock reserve -> invoice -> partial/full manual payments; rollback of multi-item insufficient stock and injected PO item failure; sequential reject/receipt/RMA/shipment handling; owner-denied cart/PDF/terms; input/schema negatives; registration/profile escalation protection; login throttling/logout/CSRF; mocked payment transport and signed callback; mail escaping and variant low-stock notification. Middleware role cases are not end-to-end requests to every controller.

Browser scope: public pages, filter/sort persistence, guest add-to-quote redirect, synthetic client cart submission, forbidden client admin page, six staff logins/selected allowed pages and JavaScript errors.


## Not executed / remaining release evidence

- MySQL8 row-lock races, decimal strict-mode/storage boundary/FK behavior and report reconciliation. SQLite is not equivalent.
- Live/sandbox PayMongo or Lalamove end-to-end provider checks, real Gmail/SMTP transport and production map/geocoding behavior. Local integrations are faked/simulated.
- Actual multipage PDF extraction/rendering and Excel execution/formula safety; gate checks template/CSV parser behavior only. XLSX/GL/RMA approval features are absent.
- Full81 method/endpoint happy/negative/controller contracts; Postman collection was generated/validated, not sent wholesale.
- Cross-browser/mobile/accessibility, performance/load/soak/rate policy, production TLS/cookies/token expiry, full audit/backup restore/deployment rollback and dependency vulnerability triage.

## Safety and reproducibility

Only qa/ files are added. No production app routes/controllers, real inventory/orders, normal port8000 server or tunnel configuration were edited. CLI QA forces in-memory SQLite; UI QA guards qa/results/ui.sqlite and separate sessions. No real mail/payment/courier call was made. Scripts return nonzero on failures; evidence files are ignored by Git. README.md provides all execution/preparation commands.
