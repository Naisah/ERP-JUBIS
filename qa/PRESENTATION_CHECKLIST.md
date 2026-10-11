# Presentation checklist — hosted ERP

> Historical pre-fix evidence. Local application fixes now pass the complete QA suite; see [FIX_REPORT.md](FIX_REPORT.md). The fixes have since been deployed and smoke-tested; see [DEPLOYED_RETEST_REPORT.md](DEPLOYED_RETEST_REPORT.md). The observations below record the original pre-fix run.

Site: https://jubis-marketing-production-m2g1zm.laravel.cloud/

Verified 12 October 2026. Read the [hosted QA report](HOSTED_QA_REPORT.md) before presenting the entire system as complete. The main quote/invoice flow works; several advanced screens still have confirmed defects.

## Rehearsed demonstration

1. Open Home, then Products. Search `PD-35-THHN`; show the product and its variants. Catalog search, sorting, pagination and filters worked.
2. Sign in with the supplied client account. Show the client dashboard and the approved **QTE-00003** QA quotation for **₱140**. Its invoice is already paid by a simulation.
3. For a new live demonstration, add one THHN-2.0 unit and use notes beginning `PRESENTATION TEST — do not dispatch`. Expected cart: subtotal ₱125, VAT ₱15, total ₱140. Submitting creates another test record and reserves another unit of stock.
4. Log out, then sign in with the supplied admin account. Open Quotation Manager, review only that new test quote, and approve it. Open Invoicing & Finance to show the matching invoice.
5. If demonstrating a payment, explicitly describe it as the **test-card simulation**. With the client signed in, select Credit / Debit Card, use the displayed test card `4242 4242 4242 4242`, expiry `12/28`, CVC `123`, and pay only the newly labelled test invoice. This path worked in QA. Do not use real payment details.
6. Return to admin Overview and Reports & Analytics. The sale should appear in revenue; the invoice should be paid. Reports have the working top-seller view.
7. Explain logistics as a simulated integration. Its record can be shown in Logistics & Dispatch; the generated tracking link currently fails.

Do not replay payment against INV-00003; it is already paid. Existing INV-00001/00002 were left unpaid. Passwords are intentionally not stored in this checklist.

## Presentation blockers and alternatives

| Area | Current problem | Presentation choice until fixed |
|---|---|---|
| GCash | Pay is blocked by hidden card fields | Use the explicitly labelled test-card simulator. |
| Tracking/map link | Generated link shows 500 Server Error | Show the shipment list only; disclose that tracking is incomplete. |
| Invoice PDF | Zero item prices; PDF ₱304 versus website ₱140 | Use the invoice screen to explain accurate item/VAT values. Do not present the PDF as a correct financial document. |
| Admin PDF | Super admin receives 403 | Avoid this action; client download works but its figures are still wrong. |
| Email | Website says success; inbox delivery not confirmed | Check Inbox and Spam before presenting email delivery as working. |
| Stock summary | Counters depend on page and include master folders | Demonstrate a searched sellable SKU's actual stock row, not summary cards. |
| Top sellers | Overview stays empty after a sale | Show Reports & Analytics. |
| Tablet | Footer email overflows at 768px | Use the tested desktop size, around 1280px. |
| Procurement/RMA | No supplier, PO or RMA records; complete save/receive/refund flows not run | Present these as inspected modules, not fully validated workflows. |

## Before starting

- [ ] Use the hosted Laravel Cloud address, not localtunnel.
- [ ] Confirm both supplied accounts can sign in in the presentation browser.
- [ ] Keep real payment details and other customers' invoices out of the demonstration.
- [ ] Have the [verified QA dashboard screenshot](results/hosted-2026-10-12/admin-dashboard-after-qa.jpg) and [case evidence](results/hosted-2026-10-12/results.json) ready if connectivity drops.
- [ ] Confirm the QA email arrived before demonstrating notifications.
- [ ] Rehearse the chosen route once in the presentation browser. This run used Chromium; browser-specific behavior outside it is unverified.
- [ ] Explain that the QA records affect demo totals: invoice ₱140 paid by simulation, one stock unit reserved, one processing shipment. Application code was unchanged.

## Follow-up priorities

1. Fix tracking, GCash hidden-field validation, PDF line values/totals and authorized PDF access; deploy and rerun those exact cases.
2. Restrict/remove publicly accessible payment simulators and run the full webhook/ownership security gates before real usage.
3. Align stock calculations, dates, company details and dashboard top sellers; resolve tablet overflow and unfinished About content.
4. Verify SMTP delivery and complete the remaining API, concurrency, audit and role checks before production sign-off.
