# Presentation checklist — verified hosted ERP

Site: https://jubis-marketing-production-m2g1zm.laravel.cloud/

Verified 12 October 2026 after application fixes and presentation configuration were deployed. See [DEPLOYED_RETEST_REPORT.md](DEPLOYED_RETEST_REPORT.md) for evidence and limits. Historical defects remain documented in [HOSTED_QA_REPORT.md](HOSTED_QA_REPORT.md); they are not the current presentation status.

## Ready to demonstrate

| Workflow | Verified result |
|---|---|
| Catalog, client/admin login and quote approval | Hosted workflow works. |
| Quote confirmation | In-page Submit/Cancel and Remove/Cancel work; cancelling preserves the draft. |
| GCash demo checkout | Pays a labelled QA invoice without requiring card fields. No real money is charged. |
| Delivery tracking map | Loads with clear demo labels; no actual driver location or dispatch. |
| Invoice PDF | Correct item, VAT, shipping, paid amount and zero balance; rendered page has no clipping. |
| Stock and reports | One stock reservation per submitted unit; payment does not deduct stock again. Dashboard/report cash totals agree. |
| Email notification | Approved Gmail SMTP configuration is deployed. Owner confirmed the processing/demo shipment email arrived in Gmail Inbox and supplied a screenshot. |

## Rehearsed demonstration

1. Open Home, About and Products. Search `PD-35-THHN` and show its variants.
2. Sign in with the supplied client account. Show approved QTE-00004 and paid INV-00004: goods 125.00 + VAT 15.00 + simulated shipping 310.00 = 450.00. Download its verified PDF and open its tracking link.
3. Describe the payment as a simulation and the map as illustrative. The existing paid QA invoice cannot be paid again.
4. For a new live demonstration, add one THHN-2.0 unit with notes beginning `PRESENTATION TEST — do not dispatch or charge` and a clearly dummy delivery address. The initial quote is 140.00. Submitting reserves another stock unit and creates test data.
5. Sign in as admin, review only that new labelled quote, and approve it. Create its simulated shipment before payment if demonstrating shipping fees. Use Lalamove as carrier and leave tracking blank; demo mode does not book a driver. The simulator chooses a fee, so use the actual updated invoice total rather than assuming 450.00.
6. Sign in as client. Select Pay via GCash/Card for that new QA invoice, select GCash, continue and pay only through the visibly labelled demo checkout. Do not use real payment details or older unpaid invoices.
7. Show the client paid status, PDF, demo tracking, admin invoice and reports. Show the notification email only after actual inbox receipt is confirmed.

## Before starting

- [ ] Use the hosted Laravel Cloud address, not localtunnel.
- [ ] Confirm both supplied accounts can sign in in the presentation browser.
- [x] Owner confirmed the QA email titled “Demo shipment created — Jubis Marketing (#4)” arrived in Gmail Inbox.
- [ ] Keep other customers' invoices and real payment details out of the demonstration.
- [ ] Have the verified PDF and screenshots in local `qa/results/fixes-2026-10-12/presentation/` available if connectivity drops.
- [ ] Explain that the rehearsed QA records affect demo totals: INV-00004 added 450.00 simulated paid revenue, reserved one additional unit, and created one processing demo shipment. THHN-2.0 stock is now 148; total recorded demo cash is 590.00 across the two paid QA invoices.
- [ ] The cart is empty after confirmation testing. Its empty draft QTE-00005 remains; it did not reserve stock or become an invoice.

Do not replay payment on INV-00003 or INV-00004. Older unpaid invoices remain unchanged. Passwords and email credentials are intentionally excluded from this checklist.

## Remaining production work

The owner confirmed courier simulation and test payments are intentional for this demo. Actual dispatch and live-money settlement are outside presentation scope. The hosted rehearsal used the application's payment simulator; PayMongo's own test checkout and provider-originated webhook still require a separate sandbox check if that integration is to be demonstrated. Forced MySQL concurrency/load also remains unverified. Procurement and RMA have automated local coverage, but their complete hosted business workflows were not exercised in this presentation rehearsal.

Demo payment/tracking flags are deliberately enabled for the presentation. Disable them before treating this environment as real business accounting.
