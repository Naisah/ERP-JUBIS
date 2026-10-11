# Hosted deployment and fix verification — 12 October 2026

**The application fixes are live on the presentation host.** Source commit: `e376d2a448a6f0c58db61f9e5fc48c02539b46c6`, published to `Naisah/ERP-JUBIS` main with owner approval. Laravel Cloud deployment 10 reports **Deployed**; build, deploy and traffic routing all finished. The deployment log confirms `2026_10_12_020000_harden_erp_integrity` completed successfully in 784.55ms.

Host: `https://jubis-marketing-production-m2g1zm.laravel.cloud/`. Browser: Chromium. These are focused post-deployment smoke checks, not a repeat of the entire production test strategy.

## Confirmed on the host

| Check | Result |
|---|---|
| Public About and footer use the new content | Pass; logo replaces the placeholder; settings-based company details appear. |
| Guest invoice simulator access | Pass; redirected to login instead of exposing the invoice and payment controls. |
| Simulation defaults outside local/testing | Pass; mock payment returned 404 after login while disabled. |
| Super-admin dashboard | Pass; page and charts load; existing recorded cash total remains unchanged. |
| Dashboard top-selling chart and Reports | Pass; both show the same completed QA sale. |
| Super-admin invoice PDF | Pass; existing labelled QA invoice downloads successfully. |
| PDF prices | Pass; unit price and line amount 125.00, VAT 15.00, invoice total 140.00. |
| PDF reconciliation with existing invoice | Pass; matches its stored billed/paid total; the old unbilled courier estimate is explicitly identified as an estimate rather than added to the invoice total. |
| PDF identity, dates and layout | Pass; correct company address, no sample tax ID, Manila issue date; one rendered page inspected without clipping. |
| Inventory page 1 and page 2 | Pass; both show the full-dataset summaries of 6 low-stock and 15 out-of-stock items. |
| Out-of-stock filter | Pass; 15 rows and matching summary; no master-folder rows. |
| Responsive About/footer | Pass; 768px viewport/document 753px and 360px viewport/document 345px, allowing for the scrollbar; no page overflow. |

No new financial or inventory records were created during these hosted smoke checks. Existing QA and other invoices were read only. The invoice with the earlier simulated shipping estimate was not retrospectively repriced. See [HOSTED_QA_REPORT.md](HOSTED_QA_REPORT.md) for historical QA records and effects.

Local validation before publication: **391 automated tests passed, 1,662 assertions**, including all previously failing release gates; production frontend build passed. MySQL schema migration success is now confirmed on the actual host, but forced concurrent transactions and load tests have not been run there.

## Presentation configuration still pending

- Demo checkout and tracking are currently disabled by the production default. Enabling `PAYMENT_SIMULATION_ENABLED` and `LOGISTICS_SIMULATION_ENABLED` on this presentation environment awaits the owner's explicit choice. GCash and the tracking map passed locally; their enabled hosted rehearsal remains pending. Do not describe the map as real driver tracking or demo checkout as payment settlement.
- Cloud's inspected custom/injected environment variable names include no SMTP provider settings. The application default mailer is `log`; no real inbox delivery is established. SMTP/provider credentials and an independently confirmed received message are required before demonstrating email delivery.
- Real automated courier booking remains unavailable; manually booked tracking numbers are supported. Real PayMongo sandbox settlement/provider-originated webhook verification remains separate.

Evidence stays local in ignored `qa/results/fixes-2026-10-12/`: deployment screenshot, hosted dashboard, invoice PDF/render, tablet screenshot and `host-retest-results.json`. Personal account details, customer figures and provider secrets are not included in this committed report.
