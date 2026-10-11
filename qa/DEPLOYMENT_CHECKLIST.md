# Deploying the tested ERP fixes

Candidate: local changes described in [FIX_REPORT.md](FIX_REPORT.md). Deployment source identified by the `erp` remote: `Naisah/ERP-JUBIS`. Target presentation host: `https://jubis-marketing-production-m2g1zm.laravel.cloud/`. The owner has approved publication; confirm the deployed commit and migration result before hosted sign-off.

## Before publishing

1. Review the candidate changes and obtain approval to publish them to the connected GitHub deployment branch and update the hosted database. The new migration adds settings, payment-event/link history and uniqueness constraints. Confirm Laravel Cloud's actual source branch and deployment steps.
2. Back up the database and rehearse the migration on a disposable MySQL clone. Check for duplicate `invoices.quote_id`, `shipments.invoice_id`, `r_m_a_s.invoice_id`, and non-null invoice payment references. The migration refuses duplicates; it never silently deletes business records. Resolve duplicates through an approved reconciliation, then retry. MySQL DDL is not fully transactional; retain the backup and migration log.
3. Keep secrets, `.env`, `qa/results/`, downloaded PDFs, browser sessions and screenshots out of Git. Do not run the QA seed/runner against the hosted database. No new Composer/npm package is required by these fixes.
4. Confirm company name, legal address, contact details, VAT and tax ID. Existing stored settings are retained; an empty tax ID is safer than an invented one.

## Presentation configuration

For a clearly labelled simulation, configure these Laravel Cloud environment values and refresh cached configuration through the platform deployment process:

```dotenv
BUSINESS_TIMEZONE=Asia/Manila
PAYMENT_SIMULATION_ENABLED=true
LOGISTICS_SIMULATION_ENABLED=true
```

The simulated services are fallbacks used when provider keys are absent. **Do not supply real payment/courier keys for a no-money/no-dispatch rehearsal.** Keep `APP_ENV=production` and `APP_DEBUG=false`; do not turn the entire host into a development environment. With simulation disabled, mock routes return 404. Authorized simulated checkout still requires the invoice owner's login. Real-operation deployments should disable both simulation flags and complete provider validation first.

SMTP settings live in the server environment, not the public Settings form. Use the provider's approved `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_SCHEME`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` and `MAIL_FROM_NAME`; alternatively use the supported provider driver. `MAIL_TIMEOUT` defaults to 15 seconds. `CONTACT_RECIPIENT` sets the Contact form destination. This Laravel configuration reads `MAIL_SCHEME`, not a legacy `MAIL_ENCRYPTION` setting. A log mailer does not deliver messages to an inbox. Confirm sender authorization and actual receipt before demonstrating email delivery.

Real PayMongo additionally requires `PAYMONGO_SECRET_KEY` and the matching endpoint's `PAYMONGO_WEBHOOK_SECRET`. The HTTPS callback is `/webhooks/paymongo`. Use a test-mode key and valid test-mode signature for sandbox checks; do not confuse a demo simulator with PayMongo settlement. Live Lalamove booking is unavailable in this candidate: book with the courier manually, then enter the carrier/tracking number, or use the presentation simulator without provider keys.

## Deployment and immediate smoke checks

1. Publish the reviewed candidate to the confirmed connected branch. Build production assets with `npm ci` / `npm run build` according to the existing Cloud build configuration. Install Composer dependencies through that configuration.
2. Run the additive migration with the platform's production migration step (`php artisan migrate --force`) and refresh application/configuration/route caches. Do not use `migrate:fresh` or seed real customer data.
3. Verify the new deployment completed, assets load, and old hosted sessions still behave correctly. Record the deployed commit and migration result alongside the release evidence.
4. Rehearse a new labelled QA quote: price, VAT, stock reservation, approval, one invoice, optional one simulated shipment, one authorized GCash simulation and matching invoice/PDF/dashboard totals. Keep existing historical QA invoices unchanged unless separately reconciled.
5. Confirm super-admin PDF downloads; a guest cannot open checkout; a different client receives 403; unknown tracking receives 404. Open the generated tracking link and verify its demo label/map.
6. Check out-of-stock counts on pages 1 and 2, Reports/dashboard top sellers, Manila dates, and the footer at 768px. Confirm Contact failures show a useful error and a permitted real message arrives in the intended inbox before claiming SMTP works.
7. Run forced MySQL concurrency and provider-originated webhook tests on disposable staging before real business use. Presentation smoke success alone is not production release certification.

## Recovery

If migration preflight fails, stop and reconcile duplicates; do not delete them to force deployment. If the deployment fails after DDL, inspect the migration log and database schema before retrying. Restore the backup or use a reviewed forward repair. After successful migration, prefer a compatible code rollback that leaves the additive tables/columns in place. Do not automatically roll the migration down after payment events exist: doing so discards payment history. Suspend new business actions while recovering, then reconcile invoices, stock and payments before resuming.
