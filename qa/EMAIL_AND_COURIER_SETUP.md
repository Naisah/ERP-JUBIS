# Completing email and courier setup

Presentation demo checkout and illustrative tracking can run without provider credentials. Real email delivery and real courier dispatch require the corresponding account setup below. Do not put passwords, API keys or private customer locations in GitHub or this document.

## Email

The owner approved reusing the existing local Gmail SMTP setup on 12 October 2026. Its nine mail settings were copied privately into Cloud shared secrets linked only to `Jubis-Marketing · production`. No password or sender login is stored in GitHub. This avoided opening or replacing existing application/database credentials. Deployment and a labelled shipment notification are verified in [DEPLOYED_RETEST_REPORT.md](DEPLOYED_RETEST_REPORT.md).

The owner confirmed the processing/demo shipment message arrived in Gmail Inbox and supplied a screenshot. That delivery case passes. Courier simulation is explicitly the intended presentation behavior; actual dispatch is outside this demo's scope.

For an approved Gmail sender, use this configuration as a template:

| Key | Value |
|---|---|
| MAIL_MAILER | smtp |
| MAIL_HOST | smtp.gmail.com |
| MAIL_PORT | 587 |
| MAIL_SCHEME | smtp |
| MAIL_USERNAME | The approved Gmail sender address |
| MAIL_PASSWORD | Enter the sender's app password privately in Cloud |
| MAIL_FROM_ADDRESS | The same approved sender address |
| MAIL_FROM_NAME | Jubis Marketing |
| MAIL_TIMEOUT | 15 |
| CONTACT_RECIPIENT | The approved mailbox that should receive contact inquiries |

Port 587 uses STARTTLS through the installed SMTP transport. For a different provider, use its documented host, port, scheme and credentials; do not copy Gmail settings blindly. The current Laravel configuration uses `MAIL_SCHEME`; an old `MAIL_ENCRYPTION` setting alone is not sufficient.

Gmail app passwords require 2-Step Verification and may be restricted on some accounts. If a new credential is needed, the mailbox owner must generate it themselves; an existing credential may be copied only with their explicit authorization. Do not disable account security or use the ERP demo login password as an email credential. See Google's [app password help](https://support.google.com/accounts/answer/185833) and [SMTP connection settings](https://support.google.com/mail/answer/7104828?hl=en-uk).

After configuration, deploy once so cached settings refresh. Send a labelled QA shipment notification only to the approved client test address, then confirm actual inbox receipt, checking spam too. Test the Contact form separately when its intended receiving mailbox is confirmed. Record the provider response and independent inbox confirmation; an application success message or a log-mailer entry alone is not delivery proof. If authentication/TLS fails, keep the error visible and correct the provider configuration before another attempt.

## Courier

The existing manual workflow accepts a carrier and tracking number from a booking made with that carrier. Providing a tracking number does not submit another API booking. The presentation simulator creates a clearly labelled demo tracking page and does not dispatch a real driver.

Lalamove's real v3 API requires both quotation and order placement, using HMAC-signed requests. Required setup includes a developer account, correct sandbox API key/secret, market, permitted service type, approved pickup address/coordinates/contact, and accurate recipient address/coordinates/contact. A quotation response by itself is not a booked delivery. See the [official Lalamove API reference](https://developers.lalamove.com/).

The current candidate does not implement real automated dispatch. Do not add live keys and assume it can book. Before enabling real dispatch, implement and verify the quotation/order flow against sandbox, persist the returned order ID as a string, use the returned tracking link, and reconcile the approved price. Test rejection, expiration, timeout and uncertain booking outcomes without automatically retrying an order that may already exist. This integration requires a separate sandbox acceptance run; actual production dispatch/payment needs its own approval.

For today's presentation, use the simulator or manually entered tracking. Do not describe an illustrative map as a live driver's GPS location.
