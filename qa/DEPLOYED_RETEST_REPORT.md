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

No new financial or inventory records were created during the initial hosted smoke checks above. The later authorized presentation rehearsal below did create labelled QA records. The invoice with the earlier simulated shipping estimate was not retrospectively repriced. See [HOSTED_QA_REPORT.md](HOSTED_QA_REPORT.md) for historical QA records and effects.

Local validation before publication: **391 automated tests passed, 1,662 assertions**, including all previously failing release gates; production frontend build passed. MySQL schema migration success is now confirmed on the actual host, but forced concurrent transactions and load tests have not been run there.

## Presentation configuration follow-up

- The owner subsequently approved enabling demo tools. `PAYMENT_SIMULATION_ENABLED=true` and `LOGISTICS_SIMULATION_ENABLED=true` were added as separate Cloud shared settings linked only to this environment. Deployment 12 completed successfully without opening or changing existing application/database credentials. Hosted GCash and tracking checks now pass. Do not describe the map as real driver tracking or demo checkout as payment settlement.
- The owner explicitly approved copying the existing local Gmail SMTP setup. Nine mail settings were saved privately as shared secrets linked only to this environment. Deployment 14 (`97808adc915235b077d32128a96e4c6ab1ba8ae8`) reports **Deployed**, with build, deploy and routing finished in 1m38s. The labelled QA shipment notification returned **Notification accepted for sending** with SMTP enabled. Independent Inbox/Spam receipt is awaiting the owner's confirmation; acceptance alone is not delivery proof. No credentials were committed.
- Real automated courier booking remains unavailable; manually booked tracking numbers are supported. Real PayMongo sandbox settlement/provider-originated webhook verification remains separate.

The browser-native quote confirmation stalled this test browser. It was replaced with an accessible in-page confirmation retaining Submit/Cancel and Remove/Cancel choices, published in commit `97808ad` and deployed in deployment 13. Local browser cancellation kept the draft, and confirmed submission produced exactly one pending one-unit quote with stock 100 -> 99. The production frontend build passed. On the host, Submit/Cancel and Remove/Cancel both kept a temporary draft item; confirming Remove emptied the cart. This check left an empty draft QTE-00005 and created no additional submitted quote, invoice or stock reservation.

## Authorized hosted presentation rehearsal

| Check | Observed result |
|---|---|
| QA quote and approval | QTE-00004 contains one THHN-2.0 unit and explicit QA/no dispatch/no charge notes. Approval created one INV-00004 for 125.00 goods + 15.00 VAT. |
| New simulated shipment | One processing shipment; clearly identified as simulated, no real courier dispatched. Fee 310.00 added once; invoice total became 450.00. |
| SMTP notification | Real SMTP transport accepted the demo shipment message to the approved client test address. Inbox confirmation remains pending. |
| New tracking link | Opens an OpenStreetMap map with Demo Delivery Tracking, Processing, and no live driver/arrival claim. |
| Hosted GCash checkout | Selected GCash, continued without card fields, paid 450.00 in the simulator. Dashboard confirms Payment Complete and no real money charged. |
| Reopening paid checkout | Redirects to the client dashboard with an already-paid warning; payment controls are unavailable. |
| Paid PDF | One rendered page visually inspected. Goods 125.00 + VAT 15.00 + shipping 310.00 = total/paid 450.00; balance 0.00; Paid in Full; no clipping. |
| Inventory | Stock 149 -> 148 for the one submitted unit. After approval, shipment, payment and temporary cart removal, it remains 148. |
| Admin reconciliation | Recorded cash 140.00 -> 590.00, exactly the 450.00 simulated payment. Pending quotes 0; prior unpaid invoices and receivables unchanged. |
| Reports and stock ledger | Reports cash also 590.00; top seller THHN-2.0 is two units / 250.00 goods revenue across the two paid QA invoices. Stock ledger contains one -1 entry for QUOTE-4, attributed to the client. |

These authorized QA records affect hosted presentation totals and reserve one additional unit. They are not real sales, real payment settlement or actual delivery bookings. Do not replay payment on INV-00003 or INV-00004, or process older customer invoices during a demonstration.

See [EMAIL_AND_COURIER_SETUP.md](EMAIL_AND_COURIER_SETUP.md) for credential entry and provider acceptance requirements.

Evidence stays local in ignored `qa/results/fixes-2026-10-12/`, including the `presentation/` screenshots, paid QA PDF/render and rehearsal results. Personal account details, unrelated customer figures and provider secrets are not included in this committed report.
