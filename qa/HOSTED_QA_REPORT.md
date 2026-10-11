# Hosted QA report — presentation readiness

> Historical pre-fix evidence. Local application fixes now pass the complete QA suite; see [FIX_REPORT.md](FIX_REPORT.md). The fixes have since been deployed and smoke-tested; see [DEPLOYED_RETEST_REPORT.md](DEPLOYED_RETEST_REPORT.md). The observations below record the original pre-fix run.

Tested site: https://jubis-marketing-production-m2g1zm.laravel.cloud/

Date: 12 October 2026, Asia/Manila. Browser: Codex in-app Chromium. Sizes checked: 1280px desktop, 360px phone, and 768px tablet. This report records observations from the deployed website, not just the local test suite.

**Decision: the main quote-to-invoice demonstration works, but a complete presentation of payments, tracking and invoice PDFs has blockers. Production release sign-off is not granted.**

62 checks were recorded: **42 passed, 13 failed, 7 partially tested**. The failed checks describe 12 distinct findings; shipping reconciliation fails both the PDF and checkout checks. Page-load passes mean the page loaded successfully, not that every action in that module was validated. See the [case results](results/hosted-2026-10-12/results.json) for all executed observations.

The user authorized testing with the supplied client and admin accounts, labelled test records, test notifications and sandbox payments. Application code, credentials, account roles and configuration were not changed. No deployment was performed. A card **simulation**, not a real charge, was completed. The actual admin role was `super_admin`.

## What works for the presentation

- Public homepage, About, Contact, catalog and product details load.
- Search by SKU, no-results search, alphabetical sort, pagination, brand filters and combined brand/category filtering work.
- Guest prices are hidden; guest variant Add leads to login. The sampled out-of-stock catalog button is disabled.
- Client and super admin logins work. Client access to the admin dashboard returns 403. Guest admin and invoice PDF access redirect to login.
- Client quote creation, admin review and approval, invoice creation, one-unit stock reservation, and the test-card simulator work.
- All 11 admin navigation destinations load. Staff/client tabs and product, supplier and PO forms open.
- Current inventory CSV downloads with 914 data rows, eight columns per row, and the tested SKU's correct price and stock.
- The paid QA invoice agrees with dashboard revenue and receivables. The Reports page shows the sale and conversion funnel.
- Phone navigation works; sampled Home, About, catalog and variant pages have no whole-page horizontal overflow at 360px. The variant table scrolls inside its container.

## Confirmed deployed findings

Severity follows the [release checklist](RELEASE_CHECKLIST.md). P1 findings block the affected demonstration or expose sensitive invoice information. They are not proof that a real payment or customer record was exploited.

| Finding | Severity | Simple explanation | Evidence / reproduction | Acceptance after a fix |
|---|---|---|---|---|
| HOST-01: Tracking fails | P1 | The app creates a delivery link, but opening it shows **500 Server Error**. A tracking/map demonstration stops there. | Create the permitted test shipment for INV-00003; open its generated `/api/mock/tracking/LALA-6ACBC85D7404E` link. [Screenshot](results/hosted-2026-10-12/tracking-server-error.jpg). H27. | Link loads the correct shipment and tracking display, without a server error. |
| HOST-02: GCash Pay does nothing | P1 | GCash cannot finish because three hidden card fields are still required. | Select GCash on the QA checkout, Continue, then Pay PHP 140.00. Page remains unchanged. DOM inspection shows the hidden card fields are required, missing and invalid. [Screenshot](results/hosted-2026-10-12/gcash-button-blocked.jpg). H34. | Each payment method validates only its relevant fields; GCash completes its simulation and updates the correct invoice once. |
| HOST-03: PDF item prices are zero | P1 | The website shows a ₱125 item, but the PDF prints unit price and line total as ₱0. | Client downloads INV-00003. Its line is zero while PDF subtotal is ₱125. [Actual PDF](results/hosted-2026-10-12/INV-00003-hosted.pdf), [render](results/hosted-2026-10-12/INV-00003-hosted.png). H29. | PDF SKU, quantity, unit price and line total match the invoice screen and persisted quote figures. |
| HOST-04: PDF and payment totals differ | P1 | The PDF asks for ₱304, while the invoice and checkout ask for ₱140. | Test shipment adds ₱164 shipping to the PDF only. [PDF](results/hosted-2026-10-12/INV-00003-hosted.pdf), [invoice screen](results/hosted-2026-10-12/qa-invoice-admin.jpg), [checkout](results/hosted-2026-10-12/qa-sandbox-checkout.jpg). H30/H33. | Shipping is included consistently in the invoice balance, payment amount, PDF and reports, with one rounding rule. |
| HOST-05: Super admin PDF denied | P1 | The supplied admin can manage invoices but gets **403 Forbidden** when opening their PDFs. | While signed in as super admin, visit `/invoices/3/pdf`. [Screenshot](results/hosted-2026-10-12/super-admin-pdf-denied.jpg). H21. | Authorized admin roles can download invoices; clients remain limited to their own invoices. |
| HOST-06: Guest checkout exposure | P1 | After logout, an invoice simulator page still shows its invoice number, amount and payment controls. | Logged-out GET of the already-observed `/api/mock/payment/invoice/2` displays INV-00002 and [existing invoice amount redacted]. [Screenshot](results/hosted-2026-10-12/guest-checkout-exposed.jpg). H39. **No guest payment POST was executed.** | Local simulators are unavailable on public production or require authorized ownership; guest requests cannot read or change another user's invoice. |
| HOST-07: Stock summaries are misleading | P2 | Stock counts change with pagination and include product folders that have no stock of their own. | Out-of-stock filter returns 67 results including Master Container / N/A rows. Its summary is 5 on page 1 and 0 on page 2. Reports show 67; dashboard low-stock count is 102 and includes master folders. [Page 2](results/hosted-2026-10-12/inventory-out-page2.jpg). H24. | Define sellable-item stock consistently; calculate summary counts across the filtered dataset, not only the current 15 rows. Exclude non-stock master folders. |
| HOST-08: Dates disagree | P2 | The quote is dated October 12 on the website, but the new PDF and CSV filename say October 11. | QA quote submitted around 01:27 Asia/Manila on October 12. PDF issued October 11; CSV filename includes `2026-10-11`. H31. | Business dates and export filenames consistently use the agreed timezone, including near midnight. |
| HOST-09: PDF business details differ | P2 | The invoice still contains sample company details rather than the contact details shown on the site. | PDF: 123 Warehouse Avenue, Makati; sample TIN; `sales@jubismarketing.com`. Public Contact: Gonzales Compound, Las Pinas; `Jubismarketing@gmail.com`. [PDF](results/hosted-2026-10-12/INV-00003-hosted.pdf). H32. | Owner-approved company identity, address, tax information and contact details are consistent across screens and official documents. |
| HOST-10: Tablet footer overflow | P2 | At tablet width, the email address makes the page slightly wider than the screen. | At viewport 768px, document width is 782px; footer email extends beyond the content edge. 360px and 1280px checks do not show this overflow. [Screenshot](results/hosted-2026-10-12/tablet-footer-overflow.jpg). H49. | Footer wraps at tablet sizes without whole-page horizontal scrolling. |
| HOST-11: Dashboard top sellers empty | P2 | Reports show the completed test sale, but the dashboard still says there is not enough sales data. | After INV-00003 is paid, Reports list THHN-2.0 quantity 1, revenue ₱125; Overview's Top Selling Products stays empty. [Dashboard](results/hosted-2026-10-12/admin-dashboard-after-qa.jpg), [Reports](results/hosted-2026-10-12/reports-after-qa.jpg). H51. | Both views use the same defined sales source and display the completed sale. |
| HOST-12: About photo unfinished | P3 | About Us contains a literal warehouse-photo placeholder. | Public About page displays `[WAREHOUSE / OFFICE IMAGE]`. H44. | Replace the placeholder with approved content or remove it. |

## Main workflow executed

| Step | Expected | Actual |
|---|---|---|
| Sign in as the provided client | Approved client dashboard; no admin privileges | Passed; client admin route returned 403. |
| Add one THHN-2.0 variant | Correct SKU and price in draft cart | One item, ₱125. |
| Review cart totals | Subtotal ₱125 + VAT ₱15 = ₱140 | Passed. No pre-existing draft items were present. |
| Submit labelled quote | One pending quote and one reserved unit | QTE-00003, pending, ₱140. Initial stock limit 150; inventory/CSV later 149. |
| Review as super admin | Client, item, amount and notes agree | Passed; QA notes persisted. |
| Approve and convert | One matching unpaid invoice | INV-00003, ₱140 billed, ₱0 paid. |
| Create test logistics record | Linked shipment, no real dispatch | Processing shipment, explicitly **Lalamove (API Simulated)**, dummy QA address, ₱164 simulated fee. Generated tracking link failed. |
| Download PDF as client | Accurate invoice document | Download/layout passed; prices, shipping reconciliation and business details failed. |
| Simulate GCash | Paid state after valid confirmation | Failed: hidden required card inputs block submit. |
| Simulate card instead | Test card completes, only QA invoice changes | Passed; INV-00003 paid ₱140, client shows Payment Complete. |
| Reopen paid checkout | Prevent repeat payment | Redirects to dashboard with already-paid warning. This is a GET/UI guard check, not a concurrent/replayed POST test. |
| Reconcile totals | Revenue +₱140; remaining AR unchanged | Dashboard revenue ₱140; AR [existing receivables redacted] = existing [existing invoice amount redacted] + [existing invoice amount redacted]. Existing invoices remain unpaid. Reports paid count 1 and conversion 33.3%. |

## Email, maps and integration limits

The Contact form rejected empty required fields. A clearly labelled QA message was then submitted using the supplied client email. The application returned a success message and cleared the form. This is an application-response pass, **not independent SMTP/inbox-delivery proof**. Inbox confirmation was requested and was not received before this report was saved.

Shipment creation also claimed that an email receipt was dispatched. This cannot establish delivery: the inspected local source catches mail errors yet still displays a success claim. No mail-provider logs, queue logs or Laravel Cloud environment settings were accessed. Do not promise working Gmail/SMTP delivery during the presentation without inbox confirmation.

Logistics identified itself as **API Simulated**. A real Lalamove booking, actual courier dispatch, PayMongo live payment, signed webhook callback and real GCash/QR Ph settlement were not validated. Contact currently has no embedded map iframe; the generated logistics tracking/map path failed with 500. Global Settings exposes company/content/VAT controls, not SMTP credentials.

## Records and effects left on the hosted database

| Record | Final state |
|---|---|
| QTE-00003 | Approved; notes begin `QA PRESENTATION TEST 2026-10-12`; one THHN-2.0 unit. |
| INV-00003 | Paid by local card simulation; billed/paid ₱140. No real funds transferred. |
| LALA-6ACBC85D7404E | Processing; carrier API Simulated; dummy address expressly says QA / do not dispatch. |
| THHN-2.0 | 149 units, down from 150 because the test quote reserved one unit. |
| Dashboard/report data | Test revenue increased by ₱140; quote and invoice counts increased by one. |
| Notification attempts | One Contact message and the simulated shipment receipt; inbox delivery remains unconfirmed. |

These labelled records remain visible for inspection. They were not deleted, refunded, rejected or reversed. The previous two invoices were only read and remain unpaid. **Testing did change the authorized hosted test data, though it did not change code.** Remove/reconcile the QA records through an approved cleanup procedure before using financial reports as real business totals; do not use arbitrary database deletion or rejection to undo a paid invoice. Sessions were logged out and temporary viewport overrides reset when testing finished.

## Scope still requiring a separate release run

- The server-side fractional-quantity submission was blocked by automatic approval review because it might persist an invalid item. Field-only 0.5 validation was checked (`rangeUnderflow` and `stepMismatch`). When asked, the user delegated the choice; the safer field-only check was retained. No approval bypass was attempted.
- Staff role boundaries (sales, warehouse, purchasing, finance and ordinary admin), cross-client ownership, login throttling, token/session expiry and stale-session tests were not fully exercised on the host.
- No malformed JSON, SQL injection, stored XSS, unsigned webhook, overpayment, stock reversal, parallel update or race traffic was sent to the hosted database. The local findings for those cases remain separate evidence, not automatically confirmed hosted defects.
- Supplier and PO creation, receiving/rollback, product save/archive, account creation/role changes, RMA creation/refund and settings persistence were not executed. Their pages/forms were inspected. The site had no suppliers, POs or RMAs before this run. No client RMA request control was observed on the inspected dashboard.
- No database connection or read-only SQL reconciliation, audit-log verification, transaction rollback proof, XLSX export, load test, mail-provider receipt, Safari/Firefox run or real payment/courier integration test was performed.
- CSV passed for the 914 current records. Comma/quote/newline/formula edge-case fixtures were not added to the host, so this does not resolve the separate local CSV escaping finding.
- Backend deployment commit/configuration was not verified through Laravel Cloud. Source inspection helped explain matching failures, but the browser results above establish only the actual observed deployed behavior.

See the [presentation checklist](PRESENTATION_CHECKLIST.md) for the rehearsed route and the [full strategy](TEST_STRATEGY.md) for production acceptance requirements.
