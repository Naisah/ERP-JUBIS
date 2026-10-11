# ERP fixes and local retest — 12 October 2026

**Status: implemented, tested and deployed with owner approval.** See [DEPLOYED_RETEST_REPORT.md](DEPLOYED_RETEST_REPORT.md) for the hosted verification and remaining presentation configuration. This report supersedes the open-local-defect status in the earlier execution report; it does not replace the historical hosted observations.

## What changed

| Hosted finding | Implemented behavior | Retest evidence |
|---|---|---|
| HOST-01: tracking 500 | Correct shipment variable, unknown token returns 404, demo map loads and displays the stored status. Removed invented rider, vehicle and arrival estimates. | HTTP regression and browser; `tracking-fixed.jpg`. This is illustrative tracking, not GPS. |
| HOST-02: GCash blocked | Card inputs are disabled and required only for card payment. GCash and QR Ph submit their own forms. Repeat/stale submissions cannot pay twice. | All three simulated methods pass automated tests; GCash completed in the browser. |
| HOST-03: PDF zero prices | Use the persisted quoted unit price and quantity for invoice lines, including archived products. | PDF tests and downloaded, rendered one-page invoice: unit/line price 125.00. |
| HOST-04: inconsistent shipping | New automatic shipment fee becomes one invoice charge; checkout, client/admin screens and PDF use that invoice total and remaining balance. A fee added after payment leaves only shipping unpaid. | Duplicate shipment and post-payment shipping tests; browser invoice/checkout/PDF all 304.00 (125 + 15 VAT + 164 shipping). |
| HOST-05: admin PDF 403 | Super admin, admin and finance may download invoices. Clients remain restricted to their own. | Role/ownership tests and local super-admin PDF download. |
| HOST-06: public simulator | Require login and invoice ownership. Simulators default off outside local/testing; enable explicitly for an authorized demonstration. Checkout refreshes stale links using the current balance. | Guest 401/login redirect, foreign client 403, disabled simulator 404, no unauthorized mutations. |
| HOST-07: stock counts | Use the complete filtered dataset, exclude inactive products and master folders, and use each item's reorder level consistently. | Pagination page 1 and 2, folder, variant and inactive-product fixtures. |
| HOST-08: dates | Use the business timezone (default Asia/Manila) for dates, calendar due dates and export filenames. | Near-midnight PDF/CSV tests and shared frontend date formatting. |
| HOST-09: company details | PDF, Contact and footer use company settings; removed fabricated address/TIN and sample contact defaults. Settings are validated and persist in the database. | Invoice rendered with Las Pinas details; cache-clear/settings/VAT snapshot tests. Owner should confirm legal details before issuing official invoices. |
| HOST-10: footer overflow | Wrap the contact email inside its available width. | 768px viewport, page width 753px including scrollbar allowance; 360px About page width 345px. Saved screenshots show no page overflow. |
| HOST-11: missing top sellers | Dashboard and Reports share a sales query; cash totals include partial receipts and remaining receivables include credit terms. Monthly receipts use payment event dates. | HTTP reconciliation tests and browser dashboard displaying the completed sale/chart. |
| HOST-12: About placeholder | Replace the literal warehouse-photo placeholder with the existing company logo. | Browser phone layout and production frontend build. |

Additional local release-gate fixes cover overpayments, zero/invalid decimal payments, paid invoices reopening as credit, quote rejection cycles restoring stock twice, insufficient-stock PO reversal, duplicate invoice/shipment/RMA records, unsigned/stale/mode-mismatched webhooks, inactive cart products, unvalidated settings, unsafe CSV quoting/formulas, dropped payment references, wrong simulator URLs, RMA rollback and duplicate delivery notifications. Unsupported routes were removed. Cart submissions and financial/stock updates use transactions and row locks; the inventory edit form rejects a stale stock snapshot and records the adjustment actor/delta. Entering a manually booked courier tracking number does not trigger another booking.

Email exceptions now produce a visible failure message. A successful send is described as accepted for sending; it is not an inbox-delivery guarantee. SMTP has a configurable 15-second default timeout. Automatic live courier booking fails clearly instead of fabricating a successful booking.

## Executed verification

| Check | Result |
|---|---|
| Complete guarded QA suite, including all 29 formerly failing release gates | **362 tests passed, 1,542 assertions; no failures/errors/skips** |
| Existing application tests with guarded QA bootstrap | **29 tests passed, 120 assertions** |
| Combined automated tests | **391 passed, 1,662 assertions** |
| Production frontend compilation | Passed; Vite build completed |
| Route/API pack validation | Passed; 77 method/endpoint contracts and 104 Postman requests |
| Whitespace/diff check | Passed |
| Browser rehearsal | Synthetic client GCash simulation; client invoice download; demo tracking map; super-admin dashboard and invoice PDF; About and tablet footer |
| PDF visual verification | Downloaded invoice rendered and inspected; one page, correct prices, VAT, shipping and total; no clipped figures |

Automated tests used SQLite `:memory:`. Browser tests used only the guarded `qa/results/ui.sqlite` database at loopback port 9001, with fake email and blocked external service requests. No real funds, courier dispatch, customer database changes or hosted configuration changes occurred during this fix/retest work. The prior authorized hosted QA records remain as described in [HOSTED_QA_REPORT.md](HOSTED_QA_REPORT.md).

Evidence is saved locally under ignored `qa/results/fix-retest.*`, `fix-standard.*`, `fix-build.txt`, and `qa/results/fixes-2026-10-12/`. Screenshots, PDFs and test database/session files must remain outside Git. Attach the published commit and hosted deployment result to the release record.

## Remaining limits

- Laravel Cloud deployment and focused hosted smoke checks passed. Enabling presentation simulators, SMTP/provider verification and broader release requirements remain separate. Follow [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md).
- Real SMTP needs working provider settings and an independently confirmed received message. Provider access and inbox receipt were not available in this run.
- Real Lalamove automatic booking is not implemented. Use an explicitly labelled demonstration or enter a tracking number from a manually booked courier. The demo map does not track a real driver.
- Real PayMongo sandbox/live settlement and a provider-originated signed webhook have not been executed. HTTP gateway calls and webhook payloads were mocked for deterministic tests. Reconfirm the provider's legacy payment-link contract before accepting real funds.
- MySQL migration rehearsal, forced parallel row-lock races, multi-worker concurrency, load testing and non-Chromium browser coverage remain staging release requirements. SQLite sequential tests do not prove those behaviors.
- The migration preserves existing invoice amounts; it does not retrospectively bill shipping from an old shipment estimate. Historical paid amounts are snapshotted, with the legacy update timestamp as an approximate receipt date. New receipts have their own timestamp and reference. This is not a new double-entry general ledger.
