# Reproduced release-gate findings

> Historical pre-fix evidence. Local application fixes now pass the complete QA suite; see [FIX_REPORT.md](FIX_REPORT.md). The fixes have since been deployed and smoke-tested; see [DEPLOYED_RETEST_REPORT.md](DEPLOYED_RETEST_REPORT.md). The observations below record the original pre-fix run.

Generated from saved release-gates JUnit evidence on 2026-10-12T00:53:56+08:00. Application code is unchanged. Each row is a failing gate instance; related cases may describe one root cause. These are isolated synthetic reproductions, not production exploits or live transactions.

Open gate instances: P0=9, P1=14, P2=6, P3=0. All require triage and retest; severity is provisional engineering impact.

Reproduce from repo root: `php vendor/bin/phpunit -c qa/phpunit.xml --group release-gates --filter test_<lowercase ID>`. Full output/JUnit are under ignored qa/results/. Gate assertions intentionally remain strict rather than marking defects skipped.

## FIN01 P0 — Paid invoice reopens as Net30 credit

Paid invoice with1000 units becomes on_terms; no status-transition guard.

Component: `routes/web.php: accept-terms`. Reproduction: `test_fin01_paid_invoice_cannot_be_reopened_as_credit_terms` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_fin01_paid_invoice_cannot_be_reopened_as_credit_terms

## FIN02 P0 — Manual payment exceeds invoice balance

225 is accepted for invoice224; expected422/no mutation.

Component: `app/Http/Controllers/Admin/InvoiceController.php`. Reproduction: `test_fin02_manual_payment_cannot_exceed_outstanding_balance` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_fin02_manual_payment_cannot_exceed_outstanding_balance

## INV01 P0 — Reject/approve/reject manufactures stock

Stock opens100, reserves2, restores twice and ends102; status transitions do not reserve again or block cycling.

Component: `app/Http/Controllers/Admin/QuoteController.php`. Reproduction: `test_inv01_rejected_quote_cannot_be_reapproved_then_restore_stock_twice` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_inv01_rejected_quote_cannot_be_reapproved_then_restore_stock_twice

## INV03 P0 — Received PO reversal can make stock negative

Remaining stock2 minus reversal5 becomes negative; no sufficiency/locking guard.

Component: `app/Http/Controllers/Admin/PurchaseOrderController.php`. Reproduction: `test_inv03_po_reversal_cannot_make_stock_negative` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_inv03_po_reversal_cannot_make_stock_negative

## SCHEMA01 P0 — Database permits duplicate invoices for a quote

Second invoice insert succeeds; no quote_id uniqueness. This proves missing safeguard, not an executed MySQL approval race.

Component: `database/migrations/2026_09_27_135033_create_extended_erp_tables.php`. Reproduction: `test_schema01_one_invoice_per_quote_is_enforced_in_database` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_schema01_one_invoice_per_quote_is_enforced_in_database

## SEC01 P0 — Unsigned payment webhook posts an invoice as paid

Signature check is conditional on both header and secret; authentication must fail closed.

Component: `app/Http/Controllers/WebhookController.php`. Reproduction: `test_sec01_unsigned_payment_event_is_rejected_without_mutation` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_sec01_unsigned_payment_event_is_rejected_without_mutation

## SEC02 P0 — Guest can process simulator payment

No auth middleware/owner check; synthetic guest changed an unpaid invoice to paid. Production mock endpoints must be unavailable.

Component: `routes/web.php; WebhookController::processPayment`. Reproduction: `test_sec02_guest_cannot_process_simulator_payment` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_sec02_guest_cannot_process_simulator_payment

## SEC03 P0 — Another client can process someone else’s simulator payment

Authenticated unrelated client changed another invoice to paid.

Component: `app/Http/Controllers/WebhookController.php`. Reproduction: `test_sec03_other_client_cannot_process_simulator_payment` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_sec03_other_client_cannot_process_simulator_payment

## SEC04 P0 — Hour-old signed payment event is accepted

Valid HMAC alone does not prevent replay; no signature timestamp freshness/dedup validation.

Component: `app/Http/Controllers/WebhookController.php`. Reproduction: `test_sec04_stale_signed_webhook_cannot_change_financial_state` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_sec04_stale_signed_webhook_cannot_change_financial_state

## CAT01 P1 — Inactive product can be added by direct request

Catalog visibility is not enforced in cart.add; one quote line is created for inactive product.

Component: `app/Http/Controllers/QuoteController.php`. Reproduction: `test_cat01_inactive_product_cannot_be_added_by_direct_api_request` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_cat01_inactive_product_cannot_be_added_by_direct_api_request

## CFG01 P1 — Settings accepts unknown secret and privilege fields

Unvalidated request.all is cached; shared settings can expose those values on public pages. Regression proves storage; public-sharing risk is confirmed by source inspection.

Component: `routes/web.php; HandleInertiaRequests::share`. Reproduction: `test_cfg01_unknown_settings_fields_are_rejected_or_discarded` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_cfg01_unknown_settings_fields_are_rejected_or_discarded

## CFG02 P1 — Negative VAT setting is accepted

No schema validation; vat_rate=-1 succeeds302 instead of422.

Component: `routes/web.php: admin/settings`. Reproduction: `test_cfg02_invalid_vat_rate_is_rejected` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_cfg02_invalid_vat_rate_is_rejected

## EXP01 P1 — Invoice PDF uses wrong line-price fields

Template reads unit_price/total_price but quote_items stores quoted_price; priced lines render0.

Component: `resources/views/pdf/invoice.blade.php`. Reproduction: `test_exp01_invoice_lines_match_quoted_price_and_line_total` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_exp01_invoice_lines_match_quoted_price_and_line_total

## EXP02 P1 — Superadmin cannot download client invoice

Policy allows owner or role admin only; super_admin is403. Finance export policy also needs business agreement.

Component: `routes/web.php: invoices.pdf`. Reproduction: `test_exp02_super_admin_can_download_another_clients_invoice` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_exp02_super_admin_can_download_another_clients_invoice

## EXP03 P1 — CSV cells with commas shift columns

SKU/brand not CSV-quoted; combined gate also requires formula prefix neutralization. Column assertion fails first; formula execution was not exercised in Excel.

Component: `app/Http/Controllers/Admin/ProductController.php`. Reproduction: `test_exp03_csv_escapes_commas_quotes_and_formula_prefixes` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_exp03_csv_escapes_commas_quotes_and_formula_prefixes

## FIN03 P1 — Zero manual payment is accepted

min:0 accepts zero; payment must be positive and state-safe.

Component: `app/Http/Controllers/Admin/InvoiceController.php`. Reproduction: `test_fin03_payment_must_be_positive` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_fin03_payment_must_be_positive

## FIN04 P1 — Approval drops PayMongo link reference

Controller reads link.id but service returns reference_id; invoice reference stays null.

Component: `app/Http/Controllers/Admin/QuoteController.php`. Reproduction: `test_fin04_payment_reference_is_persisted_when_quote_is_approved` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_fin04_payment_reference_is_persisted_when_quote_is_approved

## FIN05 P1 — Generated simulation checkout URL has no route

Service generates /api/mock/payment/link_…; only /api/mock/payment/invoice/{id} is registered.

Component: `app/Services/PayMongoService.php`. Reproduction: `test_fin05_simulation_checkout_url_resolves_to_a_real_route` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_fin05_simulation_checkout_url_resolves_to_a_real_route

## FIN06 P1 — Centavo conversion truncates0.29 to28

intval(total_amount*100) truncates binary float instead of exact centavos.

Component: `app/Services/PayMongoService.php`. Reproduction: `test_fin06_centavo_conversion_does_not_truncate_binary_float` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_fin06_centavo_conversion_does_not_truncate_binary_float

## INT02 P1 — Tracking page fails to render shipment

Route passes shipments while template expects shipment; known tracking returns500.

Component: `routes/web.php; resources/views/mock-tracking.blade.php`. Reproduction: `test_int02_tracking_view_renders_known_shipment` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_int02_tracking_view_renders_known_shipment

## INV02 P1 — Manual stock edit has no movement audit

Stock adjustment commits without signed delta/actor/reference movement.

Component: `app/Http/Controllers/Admin/ProductController.php`. Reproduction: `test_inv02_direct_stock_adjustment_records_actor_and_delta` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_inv02_direct_stock_adjustment_records_actor_and_delta

## RMA01 P1 — RMA line failure leaves an incomplete parent

Injected item insert failure returns500 and leaves r_m_a_s parent committed; no transaction.

Component: `app/Http/Controllers/QuoteController.php: requestRMA`. Reproduction: `test_rma01_item_failure_rolls_back_entire_return_request` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_rma01_item_failure_rolls_back_entire_return_request

## SCHEMA02 P1 — Database permits duplicate shipments for an invoice

Second direct insert succeeds; validation uniqueness alone is not concurrency protection.

Component: `database/migrations/2026_09_27_135033_create_extended_erp_tables.php`. Reproduction: `test_schema02_one_shipment_per_invoice_is_enforced_in_database` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_schema02_one_shipment_per_invoice_is_enforced_in_database

## API01-1 P2 — Registered resource action has no controller handler

Inventory show and quote create/store/edit/destroy are registered but methods are absent. Remove unsupported routes or implement documented contracts.

Component: `routes/web.php; Admin/ProductController.php; Admin/QuoteController.php`. Reproduction: `test_api01_registered_controller_actions_exist with data set "/admin/inventory/{product}|GET"` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_api01_registered_controller_actions_exist with data set "/admin/inventory/{product}|GET"

## API01-2 P2 — Registered resource action has no controller handler

Inventory show and quote create/store/edit/destroy are registered but methods are absent. Remove unsupported routes or implement documented contracts.

Component: `routes/web.php; Admin/ProductController.php; Admin/QuoteController.php`. Reproduction: `test_api01_registered_controller_actions_exist with data set "/admin/quotes/create|GET"` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_api01_registered_controller_actions_exist with data set "/admin/quotes/create|GET"

## API01-3 P2 — Registered resource action has no controller handler

Inventory show and quote create/store/edit/destroy are registered but methods are absent. Remove unsupported routes or implement documented contracts.

Component: `routes/web.php; Admin/ProductController.php; Admin/QuoteController.php`. Reproduction: `test_api01_registered_controller_actions_exist with data set "/admin/quotes|POST"` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_api01_registered_controller_actions_exist with data set "/admin/quotes|POST"

## API01-4 P2 — Registered resource action has no controller handler

Inventory show and quote create/store/edit/destroy are registered but methods are absent. Remove unsupported routes or implement documented contracts.

Component: `routes/web.php; Admin/ProductController.php; Admin/QuoteController.php`. Reproduction: `test_api01_registered_controller_actions_exist with data set "/admin/quotes/{quote}/edit|GET"` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_api01_registered_controller_actions_exist with data set "/admin/quotes/{quote}/edit|GET"

## API01-5 P2 — Registered resource action has no controller handler

Inventory show and quote create/store/edit/destroy are registered but methods are absent. Remove unsupported routes or implement documented contracts.

Component: `routes/web.php; Admin/ProductController.php; Admin/QuoteController.php`. Reproduction: `test_api01_registered_controller_actions_exist with data set "/admin/quotes/{quote}|DELETE"` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_api01_registered_controller_actions_exist with data set "/admin/quotes/{quote}|DELETE"

## INT01 P2 — Repeated delivered status sends duplicate email

Same delivered transition causes two fake mail sends; no old-status/idempotency guard.

Component: `app/Http/Controllers/Admin/ShipmentController.php`. Reproduction: `test_int01_repeated_delivered_status_does_not_duplicate_email` in `qa/tests/ReleaseGateTest.php`. Observed assertion: JubisQA\ReleaseGateTest::test_int01_repeated_delivered_status_does_not_duplicate_email

## Additional source-inspected risks, not yet runtime-verified release results

- P1: reports calculate open AR from unpaid only, excluding partial/on_terms balances; MySQL reconciliation required.
- P1: invoice PDF shipping is added to displayed total without a corresponding invoice/payment ledger change; verify with an actual shipped invoice.
- P1: live Lalamove path appears to request a quotation then fabricate booking/tracking details with hardcoded coordinates; no live/sandbox booking evidence.
- P1: low-stock automatic PO event uses total_amount/unit_price/total_price versus schema total_cost/unit_cost; exercise with matching supplier to validate failure/rollback.
- P1: RMA duplicate query has no DB uniqueness; concurrent request not executed.
- P1: settings are cache-only and VAT calculations remain hardcoded12%; validate durability/config behavior before release.
- P1: production HTTPS/session/reset/audit/backup controls have not been verified.
- P2: stock scan checks variants only and mails a hardcoded address; standalone/custom threshold/recipient and dedup policy unresolved.

These items must be reproduced on disposable staging and assigned evidence; do not count them as additional executed failures. See TEST_STRATEGY.md for absent GL/XLSX/RMA approval scope.
