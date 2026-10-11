# Functional E2E scenarios and acceptance criteria

Run against synthetic fixtures in an isolated environment. Each row couples an action to its acceptance criterion; all steps in a case must pass. Database expectations use a fresh opening balance and immutable price snapshots. UI checks alone are insufficient for financial/stock cases: pair them with API responses, DB/audit evidence and mail/provider sink results. Automation references indicate supplied coverage; **manual/staging** means planned but not executed here. Priorities describe impact if acceptance fails.

## Authentication and identity

### AUTH-01 P1 — Client registration, login and logout

Precondition: unused lowercase test email, valid phone/address, no session. Automation: registration/profile/login/logout HTTP tests; UI04/05, UI08.

| Step | Action | Acceptance |
|---|---|---|
|1|Open registration; enter name/company/phone/address/email and matching strong password; submit|Required labels/errors accessible; exactly one user saved; role=client, credit=pending regardless of injected privilege fields; password hashed, not serialized|
|2|Log out, then log in with created credentials|Authenticated destination appropriate to client; session ID rotates; no cross-user cart/history|
|3|Open profile, change email/name while supplying role=super_admin via direct request|Only validated profile fields change; role/credit unchanged; verification timestamp cleared on email change|
|4|Log out; reuse old cookie to open cart/dashboard|Session invalidated; browser login redirect or JSON401; no private cached page on shared device|

### AUTH-02 P1 — Password recovery, change and verification

Precondition: synthetic user; mail sink; controllable clock. Coverage: required field tests; SessionAndTokenTest verifies successful reset, token replay/expiry/foreign-user denial, password change and verification throttling. Real email transport, verification signed link and session revocation policy remain **staging**.

| Step | Action | Acceptance |
|---|---|---|
|1|Request reset for registered email, then unknown email|Safe generic response; only legitimate account receives sink mail; no account enumeration or token in logs|
|2|Open valid reset link and set confirmed password|Token bound to user, unexpired, password hash updated; new password works|
|3|Reuse token, use expired/other-user token, mismatched confirmation|Rejected without changing hash; token single-use and actual expiry enforced|
|4|Change password with current/wrong current password; verify valid/bad signed email link|Wrong current password422; signed URL validated; verification affects correct user only; session invalidation policy evidenced|

### AUTH-03 P0 — Staff permissions and user administration

Precondition: all seven role fixtures, second superadmin, staff/client lists. Automation:273 middleware boundaries,12 HTTP lists, CRUD/profile tests, six staff browser checks. Complete method/controller testing **staging**.

| Step | Action | Acceptance |
|---|---|---|
|1|Log in each role; open allowed sidebar modules|Correct menus and authorized pages; no missing route/handler crash|
|2|Directly call each forbidden method/URL, including guessed IDs|403 and zero mutation; unauth JSON401/browser302; owner boundaries403/404; hidden buttons do not replace server checks|
|3|Admin creates staff, changes role, optionally changes password|Validated7-role allowlist, duplicate email denied, hash stored; changed role effective on next request|
|4|Delete own account/last superadmin/customer with posted documents|Self deletion refused; last-admin control explicit; accounting history and actor attribution preserved; unauthorized delete denied|

## Public catalog and CRM

### CAT-01 P2 — Search, filter, sort, detail and pagination

Precondition:25 active masters/standalone products across2 brands/categories; variants; inactive/deleted records; missing and broken image paths. Automation: catalog literal search and active list; UI01/06/07; full filters/pagination/images **staging**.

| Step | Action | Acceptance |
|---|---|---|
|1|Browse catalog as guest; search name/SKU/brand; combine category and brand|Only active eligible catalog records; matching count correct; empty query restores eligible catalog|
|2|Sort relevant/name/brand, go to page2, reload and browser back|Stable expected order with ID tie-break;12 rows/page; no duplicate/omitted rows; filters/sort retained in URL and inputs|
|3|Open master/detail; inspect variants, stock, price, images|Correct relationship and units; inactive/deleted detail denied per public policy; image missing/error shows intact placeholder; master stock not misrepresented as variant stock|
|4|Click add as guest and for out-of-stock item|Guest routed to login with clear intent; out-of-stock unavailable; direct POST also blocks purchase; no cart created for guest|
|5|Use narrow mobile layout, keyboard and zoom200%|Controls remain reachable; no horizontal page clipping; labels/focus accessible; cards/placeholders do not jump or hide price|

### CRM-01 P1 — Contact inquiry and SMTP handling

Precondition: mail sink and SMTP fault toggle. Automation: missing/malformed fields, escaped mail rendering; delivery/fault UI **staging**.

| Step | Action | Acceptance |
|---|---|---|
|1|Submit complete synthetic inquiry|Exactly one intended sink recipient, sender reply-to=user email, content complete and escaped, success only when acceptance by transport is known|
|2|Omit each required name/email/message; use invalid email/overlong phone|422 JSON or visible browser field error; no mail and entered valid values retained|
|3|Submit HTML/SQL literals and international/Unicode names|Rendered as safe text in UI/mail, no execution/query alteration; no truncation of accepted message|
|4|Inject SMTP auth/connect/TLS failure and repeat submit|Bounded wait, visible safe failure/retry; no false delivery claim or uncontrolled duplicate sends; operational log excludes credentials|

## Sales and B2B quotations

### QUO-01 P0 — Cart to approved quote and invoice

Precondition: clientA approved, itemA100 units at100.00. Automation: complete HTTP business workflow and UI08 client submission.

| Step | Action | Acceptance |
|---|---|---|
|1|Add3 units to cart, inspect summary|One own draft with one line; quantity3, quoted_price100.00; subtotal300.00,VAT36.00,total336.00; stock still100|
|2|Submit address/notes|One pending quote; stock97; one sale movement -3 with client ID/time/reference; address/notes persisted; browser clear success|
|3|Sales reviews and approves quote|Exactly one invoice matching quote/customer/totals; valid checkout URL and persisted provider reference; no second stock subtraction|
|4|Client opens dashboard; finance opens invoice|Same business status, totals and number; unauthorized other client cannot inspect/export/pay this invoice|
|5|Retry submission/approval|No duplicate reservation, invoice, checkout or email; conflict/idempotent result clearly reported|

### QUO-02 P0 — Inventory exhaustion and rollback

Precondition: draft with itemAqty2 stock100 and itemBqty1 stock0. Automation: rollback/quantity/out-of-stock tests.

| Step | Action | Acceptance |
|---|---|---|
|1|Try quantity0,-1,1.5,NaN,overflow and stock+1|Schema/domain rejection; no draft-line mutation or stock movement|
|2|Add up to stock then add1 more|Cumulative quantity capped by stock; existing line preserved; clear flash error|
|3|Submit multi-item draft where later item cannot be fulfilled|Entire transaction rolled back: itemA100, itemB0, quote draft, movement count0, no invoice|
|4|Another user reserves stock while first is viewing cart; first submits stale cart|Fresh locked stock governs decision; no overselling; stale view produces clear recoverable error|

### QUO-03 P0 — Reject and status transitions

Precondition: reserved pending quote qty2, opening100/current98. Automation: sequential rejection baseline and INV01 gate; concurrent transitions **staging**.

| Step | Action | Acceptance |
|---|---|---|
|1|Authorized sales rejects quote|Stock returns100; one +2 adjustment with actor/time/ref; status rejected; invoice/payment state reconciled under defined cancellation policy|
|2|Repeat reject|Still100; no new movement or duplicate client notification|
|3|Try rejected->approved->rejected or paid invoice's quote rejection|Invalid transition rejected; stock never102; no reopened paid invoice or unapproved refund|
|4|Two staff change status simultaneously|One valid committed transition; single invoice/reference, stock/audit consistent; loser conflict or idempotent no-op|

## Billing, finance and tax

### FIN-01 P0 — Partial/manual payment reconciliation

Precondition: invoice total336.00 unpaid, finance role. Automation: business workflow and FIN02/03 gates; posting audit/concurrency **staging**.

| Step | Action | Acceptance |
|---|---|---|
|1|Record100.00 payment|Paid amount100.00, remaining236.00, status partially_paid; unique durable payment/actor record|
|2|Record remaining236.00|Paid336.00, balance0,status paid; reports/fulfillment eligibility update once|
|3|Try0,-1,0.001,NaN and337.00 on fresh invoice|422/domain rejection, no change; no overpayment/negative balance; agreed decimal policy enforced|
|4|Repeat same payment or post concurrently from two finance sessions|Idempotency prevents duplicate, row locking/versioning prevents lost increments; no posting without corresponding audit|

### FIN-02 P1 — Net30 eligibility and due dates

Precondition: own approved invoices with aggregate qty999,1000,1001; date clock at month-end. Automation: thresholds/owner tests, FIN01 gate; credit/retry policy **staging**.

| Step | Action | Acceptance |
|---|---|---|
|1|Accept terms for999 units|400 eligibility rejection; original unpaid status/due date unchanged|
|2|Accept for1000 and1001 units with permitted credit|Status on_terms; date=acceptance+30 calendar days in Manila; amount_paid unchanged; clear client confirmation|
|3|Other client, pending/suspended credit or already-paid invoice calls directly|Owner denied403; other disallowed state rejected; paid remains paid and historical due date intact|
|4|Retry after1 day, at leap day and month/year rollover|No due extension on replay; correct30-day calculation; no implicit payment or double stock effect|

### FIN-03 P0 — Payment link, signed callback and simulator isolation

Precondition: PayMongo sandbox credentials/callback secret in disposable staging; invoice224.00. Automation: mocked response/fault/signed event, SEC01/02/03 and FIN04/05 gates.

| Step | Action | Acceptance |
|---|---|---|
|1|Approve quote and inspect created link|Exact22400 centavos, PHP, safe return URL, actual provider ID persisted; generated simulator URL resolves only in local QA mode|
|2|Complete sandbox payment; provider emits signed callback|Exact raw-body HMAC validated; event/link/amount/currency/mode match; matching invoice only becomes paid224.00|
|3|Replay callback, send unsigned/bad/stale/mode-mismatched event|Replay no extra posting; invalid auth401/no mutation; no secret/signature/full payment data in logs|
|4|Guest/other client calls simulator; open simulator in production|Guest401/foreign403 in controlled local mode; production mock endpoints404; client cannot self-certify a real payment|
|5|Timeout/429/invalid response during link creation|Bounded failure/retry; no false URL/reference or duplicate financial event; orphan provider links reconciled|

### FIN-04 P1 — Invoice PDF and tax figures

Precondition: priced invoice with2 lines, shipping fee and long names; finance-authorized export policy. Automation: EXP01/02 template/download gates; actual multipage binary **staging**.

| Step | Action | Acceptance |
|---|---|---|
|1|Owner/authorized admin downloads invoice|200 application/pdf, correct attachment/immutable invoice number, no403 for agreed privileged role, other client denied|
|2|Extract PDF text and compare every financial line to DB snapshot|quantity*quoted_price exact; subtotal/VAT/total/paid/balance match; shipping payable matches ledger/gateway; no0 unit prices on priced lines|
|3|Render1,50,200-line invoices with long SKU/address/Unicode/large numbers|Every first/last row visible, no clipped totals/overlap, repeated headers/footer accurate; no placeholder official company/TIN|
|4|Change product price/client address after invoice issue and export again|Historical approved prices/billing snapshot stable; no retrospectively altered tax document|

## Inventory and warehouse

### INV-01 P0 — Inventory create/edit/delete and stock audit

Precondition: warehouse role, existing category, referenced and unreferenced products. Automation: field negatives, schema tests, INV02 gate; upload/history policy **staging**.

| Step | Action | Acceptance |
|---|---|---|
|1|Create item with valid unique SKU,price100.00,stock100|One item saved/listed; correct category/unit/active flag; mandatory fields and decimals validated|
|2|Edit stock to101 with reason|Stock101; one +1 adjustment with warehouse actor/time/before-after/reason; summary reflects full filtered total|
|3|Use duplicate SKU,-1/1.5 stock,negative/3dp price or invalid image|422/no partial record/file; original item unchanged; no executable or overwritten upload|
|4|Delete unreferenced item then item referenced by invoice/PO|Soft deletion hides catalog record; historical SKU/prices/document links retained or deletion safely refused; movements retain actor/product attribution|

### INV-02 P1 — Export and low-stock alert/reorder

Precondition: variant and standalone products with stock0,1,10,11, reordered suppliers and image placeholders. Automation:CSV gate, variant threshold mail test; schedule/reorder correctness **staging**.

| Step | Action | Acceptance |
|---|---|---|
|1|Export inventory with >15 items|Complete declared data scope,8 stable columns, unique IDs/SKUs, exact values; not UI pagination only|
|2|Include comma/quote/newline/Unicode and formula-like SKU/name/brand|Parser preserves cells and row count; spreadsheet does not execute formulas; accepted strings untruncated|
|3|Execute stock scan at06:00 Manila via mail sink|Correct eligible items at<=10, not11; active saleable standalone/variants follow agreed policy; correct purchasing recipient; no real mail|
|4|Run scan/low-stock edit twice and test configured reorder threshold|Controlled alert dedup; any automatic PO has correct total_cost/unit_cost, exactly one draft and safe failure; no trigger creating malformed PO|

## Procurement and suppliers

### PROC-01 P1 — Supplier lifecycle and PO drafting

Precondition: purchasing role, synthetic supplier/item. Automation: supplier CRUD/required validation and PO workflow.

| Step | Action | Acceptance |
|---|---|---|
|1|Create/update supplier and search by name/contact/email|Valid values persist; duplicate name/invalid email rejected; filtering/pagination retain query|
|2|Create PO for5 units at80.25 with expected date and notes|Draft header+line atomic; total401.25; no stock change until received|
|3|Omit nullable date/notes, supply empty/duplicate lines or unknown/inactive product|Optional omission safely defaults; empty/invalid lines422; duplicate line policy explicit; no master/deleted/inactive item receipt|
|4|Inject failure creating second PO line; delete referenced supplier|Zero parent/lines after failure; stock unchanged; supplier/history preserved or deletion blocked|

### PROC-02 P0 — Receipt, repeated receipt and reversal

Precondition: item opening100, PO qty5 ordered. Automation: baseline receipt/reversal, INV03 gate; races **staging**.

| Step | Action | Acceptance |
|---|---|---|
|1|Change ordered->received|Stock105; one +5 purchase movement with purchasing actor/time/PO ref|
|2|Repeat received|Still105, single movement; no double receipt or duplicate email|
|3|Reverse before stock consumed|Stock100; one -5 adjustment; net movements0; status/audit consistent|
|4|After sales leave only2 units, attempt reversal of5|Reject or documented compensating process; never negative stock; clear user error; unchanged state on rejection|
|5|Set partially_received without received quantities, or send concurrent receipt|No fabricated partial inventory; actual line quantities must be modeled before partial posting; concurrent requests idempotent|

## Logistics, maps and fulfillment

### LOG-01 P1 — Shipment booking and dispatch

Precondition: paid/on_terms eligible invoice, warehouse/purchasing actor; provider sandbox and mail sink. Automation: duplicate validation, simulated booking; live booking/map/fault paths **staging**.

| Step | Action | Acceptance |
|---|---|---|
|1|Create manual shipment with address/carrier|One shipment for eligible invoice,processing status,correct immutable destination; duplicate/unpaid/foreign unauthorized create rejected|
|2|Select Lalamove/auto carrier in sandbox|Real supported booking/order reference and appropriate coordinates/PH service; fee and tracking persisted; quote-only response is not booking success|
|3|Dispatch shipment|Valid status transition,shipped_at correct,one dispatch email to synthetic client; no second stock decrement|
|4|Use ambiguous address or courier401/429/timeout/malformed response|Clear safe failure/retry, no bogus successful booking or duplicate shipment; no hardcoded coordinates presented as client's map|

### LOG-02 P1 — Tracking, delivery and notification idempotency

Precondition: known tracking number,in_transit shipment. Automation: INT01/02 gates; privacy/maps/UI failure **staging**.

| Step | Action | Acceptance |
|---|---|---|
|1|Open known tracking link|200 render with correct shipment/status/carrier, no undefined variable; destination disclosure follows privacy policy|
|2|Open unknown/malicious tracking value and unavailable external map|Safe404/empty state, no server error/XSS or unsafe redirect; usable fallback status|
|3|Set delivered|One transition,delivered_at correct,one email,invoice/payment unchanged|
|4|Repeat delivered, move backwards, inject SMTP disconnect|No duplicate mail or date overwrite; invalid transitions rejected; failed transport tracked with bounded retry, no false success|

## Returns / RMA

### RMA-01 P1 — Itemized return request and administrative visibility

Precondition: own paid or on_terms invoice with2 items, other client's invoice. Automation: sequential itemized/duplicate test; rollback/concurrency **staging**.

| Step | Action | Acceptance |
|---|---|---|
|1|Client requests return for eligible invoice|One pending RMA with user/invoice and complete item quantities,refund0; request does not immediately alter stock/payment|
|2|Repeat request; request unpaid/foreign invoice|No duplicate parent/lines; unpaid visible rejection; foreign404; no mutation|
|3|Finance opens RMA list|Correct client,invoice,item quantities,status; sales/client denied admin list|
|4|Inject second-line insert failure and simultaneous requests|Whole parent/item set rolls back or exactly one complete RMA remains; no orphan/incomplete record|
|5|Look for approve/reject/refund actions|Documented absent functionality; cannot claim approval/restock/refund coverage. Implement and define partial return/credit note rules if required for release|

## Analytics and BI

### BI-01 P1 — Finance dashboard and report reconciliation

Precondition: MySQL8 staging seeded known unpaid/partial/paid/on_terms/overdue invoices,POs,movements and products; frozen Manila time. Coverage: **manual/MySQL staging**, not passed by SQLite HTTP suite.

| Step | Action | Acceptance |
|---|---|---|
|1|Open dashboard/reports as finance/admin|200 render,charts accessible,authorized roles only; no SQL dialect error|
|2|Compare paid sales,procurement,inventory/open receivables to independent SQL snapshot|Exact agreed totals; AR includes remaining partial/on_terms/overdue balances; master/variant not double counted|
|3|Compare top products/brand values/funnel counts and recent movements|Correct limits/order,ties stable,underlying doc counts unique,actor/time/ref consistent; disabled/deleted policy explicit|
|4|Apply supported filters/navigate/persist view; export if feature exists|UI totals and table use same scope; unsupported date/export features not treated as implemented|

### BI-02 P2 — Empty data, month boundaries and performance

Precondition: disposable MySQL snapshots empty and large; date controls. Coverage: **manual/MySQL staging**.

| Step | Action | Acceptance |
|---|---|---|
|1|Open reports with no orders/stock/suppliers|Safe zero/empty states, no NaN/divide-by-zero,exactly6 intended calendar buckets|
|2|Add transactions at six-month boundary,midnight/monthend/leap day/future|Correct Manila grouping and inclusion; no seventh extra bucket or excluded included amount|
|3|Reverse/cancel/refund/deactivate referenced records|Approved accounting definitions reflected consistently; historical counts/prices preserved|
|4|Seed10k/100k records and measure supported queries|Agreed p95 and export budget, no uncontrolled N+1/timeouts/memory growth; counts and totals still exact|

## Global settings

### CFG-01 P1 — Validated durable configuration

Precondition: admin and finance role; disposable cache; old issued invoice. Automation:CFG01/02 gates; durability/VAT propagation **staging**.

| Step | Action | Acceptance |
|---|---|---|
|1|Admin updates company/address/contact/about fields and VAT12|Only allowlisted validated fields stored; public pages show intended fields; no secrets serialized|
|2|Send vat=-1,101,NaN and unknown smtp_password/role|422 or unknown field discarded; no invalid tax rate or public secret; non-admin403|
|3|Restart/clear disposable cache|Required business config persists or explicit documented restore/fallback policy accepted; default placeholders cannot silently replace official invoice identity|
|4|Change VAT for new quote; re-export issued invoice|New calculation follows approved configuration or UI accurately states fixed12%; historical issued values unchanged|

## Negative and boundary execution table

Apply each row to every relevant endpoint/view, not just one representative form. These are required release cases; local automation only where named above.

| ID | Boundary / negative | Oracle |
|---|---|---|
|NEG-01|quantity -1,0,1,stock,stock+1,1.5,NaN,null,integer max/max+1|Integer/domain rejection outside allowed range; no negative/cumulative oversell|
|NEG-02|money0.01,0.29,0.99,100.005,3dp,comma decimal,scientific notation,NaN/infinity|Exact approved centavo rounding/validation; no silent drift or DB overflow|
|NEG-03|decimal(12,2) max9,999,999,999.99 and max+0.01|MySQL strict rejection/explicit field limit, never truncated persisted amount|
|NEG-04|SKU/email/supplier name duplicate,case/whitespace variant,soft-deleted duplicate|Defined uniqueness policy enforced in DB and API; no silent document overwrite|
|NEG-05|name255/256,unit50/51,phone20/21 registration/50/51 contact,address500/501 registration|Boundary accepted/excess422; no app/DB truncation|
|NEG-06|missing required,nullable omitted,null,wrong scalar/array type,malformed JSON|400/422/no write; nullable omission defaults safely; no500 on valid omissions|
|NEG-07|foreign/missing/deleted IDs,negative/string/overflow route IDs|403/404 or field422, no cross-client read/write|
|NEG-08|credit qty999/1000/1001; mixed lines sum; date+30 across leap/month/year|Threshold and calendar date accurate; repeat cannot extend due date|
|NEG-09|stock second-line failure,PO second-insert failure,RMA line failure|Full rollback including header/status/audit; no orphan/external false success|
|NEG-10|repeat submit/approve/reject/pay/receive/deliver/RMA|Exactly-once business effect or conflict; no invented stock/money/email|
|NEG-11|two sessions editing same stock/quote/payment/PO|Documented conflict/locking/idempotency; closing ledger balances; no lost update|
|NEG-12|empty cart,sole item removed,missing product after cart added|Empty state safe,no empty document submission,no null-property crash|
|NEG-13|page0,-1,last,last+1,noninteger,12/13 public and15/16 admin items|Correct paginator/empty state,query retained,no duplicate rows|
|NEG-14|search SQL/XSS literal,wildcards,long Unicode/comma brand|Safe query/escaped UI; filters semantics documented; no expanded private data|
|NEG-15|broken image,empty path,master with0/no variants,many Omni placeholders|Intact fallback,correct variant/action handling,no layout collapse|
|NEG-16|upload2048/2049KB,renamed executable/polyglot,same filename|Safe image-only validation and unique storage; no execution/overwrite|
|NEG-17|payment unsigned/bad/stale/future timestamp,replay,wrong mode/amount/ref|No state mutation without validated trusted event; dedup audit|
|NEG-18|paid invoice accept terms/reject quote/simulator replay|Paid terminal or documented authorized reversal; no untracked reopening|
|NEG-19|SMTP/gateway/courier 401,429,500,timeout,partial response|Bounded failure,observable retry,idempotency,no fake delivery/payment|
|NEG-20|CSV commas,quotes,newline,Unicode,=,+,-,@,tab/CR prefix|Correct parser row/column counts; no spreadsheet formula execution|
|NEG-21|PDF1/50/200 lines,long address/SKU,large sums,shippingfee|Exact snapshot values/no clipping; first/last row/number consistent|
|NEG-22|settings invalid VAT/unknown secret/cache loss|Allowlist and durability,no secrets in guest Inertia props|
|NEG-23|session expiry/logout/reset/password change,old cookie,bad CSRF/Origin|401/403/419,no write; rotation/invalidation as approved policy|
|NEG-24|zero/large reports,partial/on_terms/overdue AR,date boundaries|Independent SQL totals match charts,tables and exports; no dialect/error/NaN|
|NEG-25|delete customer/supplier/product with posted docs;lastadmin deletion|Financial and audit history retained,admin recovery preserved|

Record every step's PASS/FAIL/BLOCKED/NOT RUN result, synthetic fixture IDs, actual status/values and evidence location. A known failing gate is FAIL until corrected and retested; do not relabel it NOT APPLICABLE.
