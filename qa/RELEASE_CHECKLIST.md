# Pre-release execution and severity framework

## Severity and scheduling

Priority is release urgency; severity is impact. Assign both if they differ. Proposed default mappings:

| Priority / severity | Definition and concrete ERP examples | Release treatment |
|---|---|---|
|P0 Blocker / catastrophic|Unauthorized payment state mutation; oversell/negative/manufactured stock; lost/duplicated financial postings; production data corruption; cross-client financial disclosure; primary core flow entirely unavailable|Stop release and contain exposure; owner immediately; no waiver for exploitable financial/inventory corruption|
|P1 Critical / high|Wrong invoice amount/export; missing required audit; duplicate booking/refund risk; broken provider integration; legitimate role denied core function; unsafe settings/secrets; missing MySQL/integration evidence|Must fix and retest before release; unexecuted mandatory P1 is blocked sign-off|
|P2 Major / medium|Broken filtering/pagination, secondary route 500, reporting presentation/history inconsistency without incorrect finance, weak recovery message; low-stock alert omission|Fix before affected scope release, or documented business exception with owner, workaround and expiry; escalate to P1 if decisions depend on wrong figures|
|P3 Minor / low|Cosmetic alignment, noncritical wording, optional icon/spacing; no data/security or workflow effect|May ship with tracked issue, owner and scheduled fix|

A 500 in an unused extra resource action may be P2; the same failure on a primary quote/payment path is P0/P1. A failed test fixture or runner is not an app defect; repair harness and rerun. Do not infer severity solely from HTTP status or test count.

## Prioritized checklist

Use unchecked until actual evidence exists for the release candidate. Local execution report is supporting evidence only; it does not check these deployment-wide items automatically.

### P0 blockers

- [ ] Unsigned/invalid payment callbacks rejected; simulator unreachable in production and owner-protected in local mode (SEC01–03).
- [ ] No overpayment, zero posting, paid invoice reopening, lost update or duplicate callback/payment posting (FIN01–03, CON-04).
- [ ] Quote multi-item rollback, concurrent last-stock submissions, same-draft double submit and reject cycling reconcile exactly (QUO-02/03, INV01, CON-01/02/03).
- [ ] PO receipt/reversal races preserve stock>=0 and one valid effect per transition (INV03,CON-05).
- [ ] One invoice per quote, unique business document IDs/references under concurrency; no partially committed parent/lines.
- [ ] No cross-client private document/payment/cart/RMA access; all forbidden roles/methods denied server-side with no mutation.
- [ ] Production configuration disables debug and shared/default test credentials; production data never used by destructive tests.

### P1 critical

- [ ] All baseline/release-gate failures investigated, corrected and retested; route inventory current; no suppressed tests or hidden error exits.
- [ ] Invoice PDF lines, VAT, total, shipping, paid/balance, company identity/TIN and immutable document number match ledger snapshot; privileged export roles correct.
- [ ] CSV round-trips all rows and cells safely, exact financial/stock figures and formula injection protection; declared export scope accurate.
- [ ] PayMongo sandbox link and verified callback work end to end; exact centavos; reference persists; raw HMAC, expiry, mode, deduplication and amount/currency verified.
- [ ] Lalamove sandbox booking/map/tracking uses correct coordinates/reference/fee; simulation clearly scoped; timeouts/failures visible and retries idempotent.
- [ ] SMTP sink/sandbox validates correct recipients, escaping, TLS/auth, timeout and retry for contact, dispatch, delivery and low-stock; no duplicate transition email.
- [ ] All critical actions have durable actor/time/ref/before-after audit and secrets redaction; movement reconciliation includes manual adjustments.
- [ ] Settings allowlist, valid tax rate, durable storage/restoration and guest props contain no secrets; issued invoices unaffected by config edits.
- [ ] MySQL8 schema precision, FK/uniqueness, atomic rollback, all eight race cases and report reconciliation evidence attached.
- [ ] Session fixation/expiry/logout/reset, CSRF/CORS/HTTPS/cookie settings, upload/XSS/SQL/IDOR tests complete; ASVS applicable controls individually mapped/verified.
- [ ] Backup restoration and deployment rollback rehearsed against sanitized isolated data; monitoring detects failed callbacks, mail/queue errors, negative stock and duplicates.
- [ ] Missing RMA approval/refund, GL, double-entry or XLSX features explicitly excluded from agreed release scope or implemented and tested; no false coverage claim.

### P2 major

- [ ] Public/master/variant catalog active policy, search, brand/category combination, sort, pagination, images and empty/null states correct.
- [ ] All registered routes either have working handlers or are removed; unsupported action cannot return 500.
- [ ] Browser flows pass on Chromium, Firefox, WebKit at 360/768/1440 widths; keyboard/focus/errors and 200% zoom usable.
- [ ] Manila 06:00 scheduler, thresholds 0/1/10/11, repeated scan/alert/reorder policy and standalone/variant behavior verified.
- [ ] Proposed performance SLOs accepted and measured; rate-limit policy documented and abuse controls tested; timeouts bounded.
- [ ] Error/retry UX preserves valid form data, no uncontrolled resubmission, back/reload/query persistence correct.

### P3 minor

- [ ] Visual/text/placeholder inconsistencies tracked with owner; no misleading finance/status labels.
- [ ] Noncritical UI polish/accessibility items recorded with due date.

## Execution order and evidence

1. Freeze candidate commit/build/config, migration version, scope and rounding/state/role policies. Check synthetic environment and isolated database/mail/provider guards.
2. Generate routes/matrix. Run baseline and release-gates with JUnit/logs; Vue compilation and candidate asset build. Fail closed on crash/no tests/nonzero.
3. Run isolated browser workflows; then complete all module/cross-browser cases on disposable MySQL staging.
4. Run synchronized MySQL races and rollback/precision/uniqueness tests; read-only reconciliation; reports and export snapshot comparisons.
5. Run provider sandbox/SMTP sink workflows and injected 401/429/500/timeouts, replay/error recovery; never send real charges/mail.
6. Complete security/operational checks, load/soak, backup restore and deployment/rollback rehearsal. Sanitize evidence.
7. Retest every failure on the corrected build and run affected regression; finance/warehouse/support owners validate business figures/behavior.
8. QA publishes case outcomes, coverage gaps, open severity counts and residual risks. Release authority signs only when criteria below hold.

Release acceptance: zero open P0/P1; all mandatory checks PASS; no unexplained financial/stock/audit discrepancies; P2 waivers named/dated/expiring; scope exclusions explicitly approved; backup/rollback plan and monitoring ready. NOT RUN/BLOCKED is not PASS. Passing the baseline checks alone does not satisfy these criteria.

## Sign-off record template

| Field | Value to fill |
|---|---|
|Candidate commit/build; date/time/timezone| |
|Environment/PHP/MySQL/browser versions; migrations| |
|Specification revision; scope exclusions; role/state/rounding decisions| |
|Case totals PASS/FAIL/BLOCKED/NOT RUN/N/A by module and priority| |
|P0/P1 count; issue IDs and fix/retest evidence| |
|Reconciliation/race/export/integration/security evidence paths| |
|Performance/rate/timeout results; operational restore/rollback proof| |
|P2 exception owner/workaround/expiry; P3 backlog| |
|QA lead; finance; warehouse/procurement; security/ops reviewer| |
|Release authority decision and timestamp| |

Defect template: ID, title, priority/severity, build/environment, case/step, synthetic preconditions, reproduction, expected versus actual, DB before/after, evidence, suspected component, owner, status, fix commit, retest result, regression scope. Sensitive cookies/keys/real customer data are redacted.
