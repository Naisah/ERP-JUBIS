# Frontend review — 7 October 2026

## Fixed

- Catalog sorting now orders the server query and retains the selected sort option.
- Catalog filter controls synchronize with navigation; pending searches are cancelled when leaving the page.
- Public navigation has a mobile menu and a linked logo; the logo fits narrow screens.
- Admin navigation has a mobile drawer, responsive content padding, and route-aware highlighting.
- Contact fields now bind to their correct values; the button submits, shows a sending state, and prevents duplicate submissions. Labels are associated with inputs.
- Product cards no longer nest links or put the quote button inside a link.
- Stock checks handle zero and negative quantities; master products with variants no longer show a misleading out-of-stock badge.
- Quote actions reject invalid quantities and prevent concurrent repeated submissions.
- Product images have a local fallback for missing or broken URLs. This does not infer or change product-to-image database mappings.
- Product creation and editing show upload previews and release temporary image URLs.
- Public and admin pages show validation errors and success/failure feedback; supplier modal errors are visible inside the modal.
- Invoices, purchase orders, shipments, suppliers, and returns have pagination. User pagination uses the shared component and disables unavailable links.
- Admin list tables and pagination accommodate narrow screens.
- Admin navigation and dashboard action links respect the existing route permissions; finance users no longer see inaccessible shipment actions.
- Cart handles archived/missing products without crashing and prevents submitting unavailable items.
- Payment buttons honor invoice payment links while retaining support for legacy simulator links.
- Password visibility controls have accessible names. A configured zero VAT rate is preserved in the settings form.

## Verification

- All 54 Vue components compiled and passed the component-import audit (`node frontend-audit.mjs`).
- Production frontend build passed.
- PHP route syntax and Git whitespace checks passed.
- Four regression tests passed, with 58 assertions: name sorting, brand sorting, filtered count/selection, and Contact submission. Tests use an isolated in-memory SQLite database and fake mail delivery.
- Browser checks covered catalog sorting/filtering, mobile navigation, homepage, About, registration, login, password-reset navigation, and Contact field bindings. Final mobile catalog and Contact checks showed no horizontal overflow. The inspected catalog had no broken image elements after fallback handling.
- Local app and public tunnel both returned HTTP 200 and referenced the same rebuilt frontend bundle. Tunnel sorting returned the expected first product.

## Limits

Admin changes were checked in source and by compilation; authenticated admin workflows were not exercised in a live browser session. No real emails, payments, account creations, inventory changes, or database recovery scripts were run. Image associations and payment processing/webhooks were not audited or repaired as part of this frontend pass. A full guarantee of no remaining bugs would require authenticated workflow testing across the app's roles.
