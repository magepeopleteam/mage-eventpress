# Optional MailSenpai SMTP delivery

The EventPress confirmation, enquiry, and low-stock notification paths covered by this guide use WordPress `wp_mail()`. You can route these messages through MailSenpai with a site-level SMTP transport; an EventPress-specific provider module is not required.

> **Scope:** This is an optional configuration recipe, not a provider certification or an inbox-delivery guarantee. Validate the complete stack on staging before changing a live site.

## Prerequisites

- A MailSenpai account with a verified sending domain.
- The SMTP username and SMTP password from the MailSenpai customer area. These credentials are separate from the MailSenpai API key.
- A site-level WordPress SMTP transport. MailSenpai provides configuration examples for WP Mail SMTP and FluentSMTP; EventPress does not bundle or require a particular mailer plugin.
- An isolated staging site, a recipient mailbox you own, and access to both the WordPress mailer logs and the MailSenpai event logs.
- The DNS records shown in the MailSenpai customer area, including DKIM and SPF. Add a DMARC record appropriate to your domain policy; MailSenpai suggests starting with `p=none` when no policy is already present.

Keep credentials out of Git, tickets, screenshots, and documentation. Use the secret storage provided by your mailer or environment. A site-level transport can also affect email from WordPress, WooCommerce, and other plugins, so keep only one mailer in control of `wp_mail()` and test that wider scope before production.

## SMTP settings

| Setting | Value |
| --- | --- |
| Host | `relay.mailsenpai.com` |
| Port | `2525`, or `587` as the alternative shown in the customer area |
| Encryption | `STARTTLS` (often labelled `TLS`); mandatory |
| Authentication | Enabled; `LOGIN` or `PLAIN` |
| Username | Your MailSenpai SMTP username |
| Password | Your MailSenpai SMTP password, not an API key |
| Sender (`From`) | An address on your verified domain or one of its subdomains |
| Certificate verification | Enabled |
| Maximum message size | 50 MB, including attachments |

STARTTLS upgrades the connection after connecting; it is not the same as implicit SSL. Do not disable encryption or certificate verification to make a test pass.

## Configure the transport

1. Verify the sending domain and publish the required DNS records.
2. Enter the SMTP settings above in the site-level mailer. Enable authentication and STARTTLS, and keep certificate validation enabled.
3. Set the sender to the verified domain. Check EventPress sender settings and any mailer-level “force sender” option so they do not replace it with an unverified address.
4. Use `Reply-To`, rather than an unverified `From` address, when replies must go to another person.
5. Disable overlapping SMTP transports so two plugins cannot rewrite or send the same message.

## Validate on staging

1. **Confirm the environment and hooks.** Ensure the site is actually staging. Check `pre_wp_mail`, sender filters, and must-use or sandbox plugins. A capture/block interceptor can return success without contacting MailSenpai, and a sender filter can change the final `From` address.
2. **Test the confirmation email.** Use the EventPress email-settings test with an owned recipient. Verify HTML rendering, the final sender, the mailer result, the MailSenpai event, and receipt in the owned mailbox.
3. **Test an enquiry.** Use controlled event data. Confirm that `From` remains on the verified domain and `Reply-To` contains the intended submitter address.
4. **Test low-stock notification if it is in scope.** Use fresh controlled event/order state; the notification is throttled, so repeatedly triggering the same state is not a reliable transport test.
5. **Test an immediate failure only on isolated staging.** For example, use temporarily invalid SMTP credentials or an unreachable endpoint. Confirm the immediate error is surfaced or logged and restore the working configuration afterwards. Do not expect EventPress to enqueue a durable retry.
6. **Test suppression and negative outcomes with provider-approved test data.** Check MailSenpai’s `dropped`, `bounce`, `defer`, and `complaint` records where applicable. Do not manufacture complaints or use real recipients for failure testing.
7. **Check the whole site.** Because the transport is shared, also verify relevant WooCommerce and WordPress notifications before enabling it in production.

## Interpret results and retries correctly

- `wp_mail() === true` means WordPress accepted the handoff. SMTP acceptance does not prove recipient-server delivery or inbox placement.
- MailSenpai can answer SMTP “OK” for a suppressed recipient while recording the message as `dropped` and not sending it. Use the provider event log as well as the WordPress result.
- The reviewed EventPress paths do not provide a durable application-level retry queue. Immediate connection or authentication failures need operational handling.
- MailSenpai automatically retries provider-accepted deferred SMTP messages. This does not retry a message that the relay never accepted; consult the provider documentation for the current retry policy.
- SMTP configuration does not add the HTTPS API’s idempotency keys or webhook handling. Do not blindly resend after an ambiguous timeout; first establish whether the original message was accepted.
- Hard bounces and spam complaints can add recipients to MailSenpai’s suppression list. Site owners remain responsible for consent, unsubscribe requests, and any WordPress-side suppression workflow.
- Stay within the current plan’s monthly and hourly limits and the 50 MB SMTP message limit.

## Provider reference

See [MailSenpai’s SMTP and API documentation](https://en.mailsenpai.com/smtp-api/) for current settings, account limits, event meanings, suppression behavior, and DNS guidance. A direct HTTPS API integration is a separate project and is not provided by this SMTP recipe.
