# Modern Editor Advanced and Global Settings Reliability Design

Date: 2026-08-26

## Objective

Make every setting exposed by the Modern Event Editor Advanced step reliable across Event Booking Manager Free and Pro. Each control must render the stored value, submit safely, persist to the established metadata or option, survive reload, and affect its existing frontend, email, reminder, PDF, checkout, or access consumer without breaking the Classic Editor or older events.

## Scope

The audit and repair cover new and existing `mep_events` posts in the modern add/edit screen:

- Page Template
- Attendee Form
- Terms & Conditions
- FAQs
- Timeline
- Related Events
- Email Message
- Email Reminder
- PDF Custom Text
- SEO & Schema
- Deposit / Partial Payment
- Access & Settings
- Advanced-step content injected by either Free or Pro hooks
- Related global email, PDF, reminder, registration, deposit, access, and display settings

The work includes field-level render, submit, sanitize, persistence, reload, and runtime-consumer verification. It excludes unrelated redesigns and changes to external mail transport or provider configuration.

## Confirmed Problem

The modern editor moves legacy Free and Pro panels into Advanced cards. Some header switches are connected to real saved fields, but a shared branch creates visual-only switches for SEO, Email Message, Email Reminder, PDF Custom Text, and Access & Settings and initializes them to off on every load. These switches have no submitted name and cannot persist.

Email Message stores its body in `mep_event_cc_email_text`, while purchase delivery is controlled separately by global recipient and WooCommerce order-status settings. The UI currently conflates expanding a card, enabling event-specific content, and enabling delivery.

## Design Principles

1. Existing metadata and option keys remain the source of truth wherever they already exist.
2. Modern controls adapt to the existing Classic Editor and add-on contracts rather than creating a parallel settings system.
3. A switch is shown only for real enable/disable behavior. Pure expand/collapse controls use disclosure semantics and do not imply a saved feature status.
4. Disabled sections preserve their previously entered data unless the established Classic Editor intentionally clears it.
5. Missing Pro panels remain absent or retain the existing upgrade state; Free must not manufacture Pro data.
6. Existing events must remain functional without a manual migration.

## Architecture and Data Flow

### Field inventory

Create a definitive matrix for every Advanced and related global control with these columns:

| Section | UI control | Submitted name | Stored key | Default / legacy fallback | Save handler | Runtime consumer | Test result |
| --- | --- | --- | --- | --- | --- | --- | --- |

The inventory is generated from the server-rendered panel classes, modern-editor JavaScript mounts, Free `save_post` handler, `mpwem_settings_save` extensions, global settings handlers, and frontend/email/PDF consumers. The matrix is a validation artifact; it does not become a second runtime schema.

### Semantic section toggles

- Attendee Form, Terms, FAQ, Timeline, Related Events, and SEO use their existing persisted status fields.
- Header switches mirror exactly one canonical named form control. Duplicate same-named controls are removed or have their `name` removed before submit.
- A change updates both the visible header switch and canonical control value.
- Initial card state derives from stored server-rendered state, never a JavaScript constant.
- Email Reminder continues to use each reminder row's established enabled field. The card header is a disclosure control unless the add-on already defines a section-wide status.
- PDF Custom Text and Access & Settings use disclosure controls because they have content/settings but no coherent section-wide enable state.

### Event-specific Email Message

Add a canonical event-level status key, `mep_event_cc_email_status`, with values `on` and `off`.

- New saves submit and sanitize this status explicitly.
- If the status meta does not exist, an event with non-empty `mep_event_cc_email_text` is treated as enabled for backward compatibility.
- Disabling preserves `mep_event_cc_email_text` and makes the global confirmation body the fallback.
- Enabling selects the event-specific body.
- Recipient selection and qualifying order statuses continue to obey global delivery settings. The event switch does not bypass a globally disabled recipient or send on a disallowed order status.
- TinyMCE is synchronized before form submission so the latest visual-editor content reaches the textarea.
- Existing dynamic tags and filters remain intact.

### Save orchestration

The modern form continues through its nonce-protected event save endpoint and existing `save_post` / `mpwem_settings_save` hooks. Repairs are limited to:

- correct canonical field values before submit;
- explicit TinyMCE synchronization;
- safe defaults that preserve omitted fields during partial or add-on-absent saves;
- per-field sanitization in the owning handler;
- prevention of duplicate controls overriding one another.

Step-scoped quiet saves must not erase Advanced values that were not present in the submitted step.

### Global settings

For every related global control, verify that the rendered option key matches the save key and runtime lookup key. Resolve mismatches at the narrowest compatibility point and accept legacy keys when required. Global precedence remains:

1. Delivery eligibility and recipients from global settings.
2. Event-specific enabled content when present.
3. Global confirmation content.
4. Existing preset fallback when neither contains content.

## Error Handling and Security

- Retain the existing event-save nonce and `edit_post` capability checks.
- Sanitize status values against strict allowlists.
- Use `wp_kses_post()` for email, terms, SEO, reminder, and PDF rich content as appropriate.
- Sanitize scalar text, URLs, email addresses, numbers, IDs, and nested reminder/repeater arrays according to their data types.
- Escape all values at their output context.
- Do not log webinar links, recipient addresses, message bodies, or credentials.
- A malformed section should fail validation with the user returned to Advanced; unrelated saved data must remain intact.

## Compatibility

- Do not rename or delete existing meta or option keys.
- Classic Editor and established add-on save hooks remain operational.
- Preserve older event values and infer email-enabled state only when the new status meta is absent.
- Pro deactivation must not delete Pro metadata, and Free-only saves must not overwrite unavailable Pro fields.
- Existing frontend template hooks, WooCommerce status hooks, mail filters, PDF generation, and reminder cron flows remain unchanged except where a verified key mismatch prevents them from receiving saved data.

## Verification Strategy

### Static field matrix

Account for every named Advanced/global input and connect it to a save handler, reload source, and consumer. Any field missing one of those links is a defect or must be identified as UI-only.

### Event lifecycle tests

For both a new draft and an existing published event:

1. Set every Advanced section to non-default values.
2. Save draft/update/publish as applicable.
3. Read the stored metadata directly.
4. Reload the modern editor and verify control state and field content.
5. Switch to Classic Editor and verify the same data remains available.
6. Switch back to Modern Editor and verify no loss.
7. Disable and re-enable sections and confirm retained content.
8. Run a step-scoped modal save and verify unrelated Advanced data is unchanged.

### Runtime tests

- Confirm template, FAQ, timeline, related events, terms, SEO/schema, access restrictions, and attendee form affect their current frontend consumers.
- Confirm deposit values affect checkout calculations without repricing unrelated orders.
- Confirm PDF custom text reaches generated PDF output.
- Confirm reminders persist and their scheduler reads the saved configuration.
- Intercept `wp_mail` for a disposable event/order and verify qualifying status, billing/attendee recipient rules, event-specific body selection, dynamic tag replacement, and global fallback.
- Verify an event-level Email Message does not bypass globally disabled delivery.

### Regression and quality checks

- PHP syntax checks for every modified PHP file.
- JavaScript syntax/lint checks supported by the repository.
- Existing automated tests for Free and Pro.
- Focused tests for default, legacy, enabled, disabled, empty, malformed, and missing-addon states.
- Review diffs for unrelated UI/style or behavior changes.

## Completion Criteria

The work is complete only when the field matrix has no unexplained gaps; every control saves and reloads correctly; each setting reaches its runtime consumer; legacy data and Classic Editor behavior remain intact; email delivery is demonstrated through an intercepted real WordPress mail call; and relevant Free/Pro regressions pass.
