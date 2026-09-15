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
- Waitlist controls injected into the event editor
- Review & Rating controls injected into the event editor
- Pro form-builder and access controls
- Every server-rendered or dynamically added repeater field within those sections
- Advanced-step content injected by either Free or Pro hooks
- Related global email, PDF, reminder, registration, deposit, access, and display settings

The work includes field-level render, submit, sanitize, persistence, reload, and runtime-consumer verification. Before implementation, the inventory must freeze the finite list of Advanced and related global controls found in the installed Free and Pro source; additions discovered later must first be added to the matrix. It excludes unrelated redesigns and changes to external mail transport or provider configuration.

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

The inventory is generated from the server-rendered panel classes, modern-editor JavaScript mounts, Free `save_post` handler, `mpwem_settings_save` extensions, global settings handlers, and frontend/email/PDF consumers. Each row must also record its precise sanitizer/validator, named fixture, mutation, stored-value assertion, reload assertion, consumer assertion, and cleanup result. The matrix is frozen before source implementation and is a validation artifact; it does not become a second runtime schema.

### Semantic section toggles

- Attendee Form uses `mep_event_reg_form_status` (`on`/`off`) only while Pro owns and renders the canonical field.
- Terms uses `mep_disable_term_condition` with its existing nonintuitive contract (`yes` means displayed); the repair must not invert it.
- FAQ uses `mep_faq_status` (`on`/`off`).
- Timeline uses `mep_timeline_status` (`on`/`off`).
- Related Events uses `mep_related_event_status` (`on`/`off`).
- SEO uses `mep_rich_text_status` (`enable`/`disable`).
- Missing-meta defaults must be copied from each current renderer and runtime consumer into the matrix. JavaScript must not replace a legacy enabled-by-default fallback with a generic off fallback.
- Header switches mirror exactly one canonical named form control. Duplicate same-named controls are removed or have their `name` removed before submit.
- A change updates both the visible header switch and canonical control value.
- Initial card state derives from stored server-rendered state, never a JavaScript constant.
- Email Reminder continues to use each reminder row's established enabled field. The card header is a disclosure control unless source inspection identifies an established section-wide status.
- PDF Custom Text and Access & Settings use disclosure controls because they have content/settings but no coherent section-wide enable state.
- Disclosure controls are `button type="button"` elements with `aria-expanded`, `aria-controls`, native keyboard operation, and a visible focus state. No unnamed checkbox may be styled as though it were a saved feature switch.

### Event-specific Email Message

Add a canonical event-level status key, `mep_event_cc_email_status`, with values `on` and `off`.

- New saves submit and sanitize this status explicitly.
- If the status meta does not exist, an event with non-empty `mep_event_cc_email_text` is treated as enabled for backward compatibility.
- Disabling preserves `mep_event_cc_email_text` and makes the global confirmation body the fallback.
- Enabling selects the event-specific body.
- Recipient selection and qualifying order statuses continue to obey global delivery settings. The event switch does not bypass a globally disabled recipient or send on a disallowed order status.
- TinyMCE is synchronized before form submission so the latest visual-editor content reaches the textarea.
- Existing dynamic tags and filters remain intact.
- Classic Editor receives the same event-specific status control adjacent to its body editor. Classic and Modern read and write the same key; an unrelated Classic save cannot infer or overwrite an existing explicit `off`.
- Cross-editor tests include Modern off -> Classic edit/save -> Modern reload and Classic on/off -> Modern edit/save -> Classic reload.

All confirmation entry points call one email-body resolver. Its required truth table is:

| Stored status | Stored event body | Selected body |
| --- | --- | --- |
| absent | non-empty | event body (legacy enabled) |
| absent | empty | global body, then preset fallback |
| `on` | non-empty | event body |
| `on` | empty | global body, then preset fallback |
| `off` | non-empty or empty | global body, then preset fallback |

Delivery eligibility remains separate from body selection. The matrix must enumerate WooCommerce billing, form-builder attendee, RSVP, native checkout, and custom checkout entry points. Order-based paths obey configured qualifying statuses; statusless RSVP obeys its existing RSVP eligibility; recipient-specific global disable settings must produce zero `wp_mail` calls. Allowed-path tests assert exact call count, recipient, subject, headers, selected body, and dynamic-tag replacements.

### Save orchestration

The modern form continues through its nonce-protected event save endpoint and existing `save_post` / `mpwem_settings_save` hooks. It adds an explicit modern-save mode marker and per-section presence markers. Handlers must distinguish an intentionally unchecked or cleared field from a section omitted because it is unavailable or outside a quiet save. Repairs are limited to:

- correct canonical field values before submit;
- synchronization of every mounted TinyMCE/`wp_editor` instance before every full or quiet submission, including Free email/SEO editors and Pro terms, reminder, PDF, and other rich editors;
- presence-aware handling that preserves omitted fields during partial or add-on-absent saves;
- per-field sanitization in the owning handler;
- prevention of duplicate controls overriding one another.

Step-scoped quiet saves update only their owned section. A full save may clear a field only when its section presence marker proves that the canonical control was rendered and submitted. Free must neither render nor submit `mep_event_reg_form_status` or any other Pro-owned canonical field while Pro is inactive. Pro active -> deactivate -> Free full/quiet save -> reactivate tests must prove all Pro-owned metadata is byte-for-byte unchanged.

Validation and normalization run before `wp_update_post()` or any action that can trigger `save_post` writers. This applies to draft, update, publish, and quiet saves according to the owned section's rules. Invalid input returns to Advanced without any post/meta mutation; submitted values and a field-specific error are retained for correction.

### Global settings

For every related global control, verify that the rendered option key matches the save key and runtime lookup key. Resolve mismatches at the narrowest compatibility point and accept legacy keys when required. Global precedence remains:

1. Delivery eligibility and recipients from global settings.
2. Event-specific enabled content when present.
3. Global confirmation content.
4. Existing preset fallback when neither contains content.

Global save/reload tests cover every inventoried setting and verify that unrelated option sections remain byte-for-byte unchanged.

## Error Handling and Security

- Retain the existing event-save nonce and `edit_post` capability checks.
- Sanitize every status, SEO mode, reminder unit/type, deposit type, and similar enum against a field-specific allowlist recorded in the matrix.
- Use `wp_kses_post()` only for fields intentionally supporting HTML, including email/PDF/template content; safely handle every raw-output consumer.
- Use `sanitize_text_field()` for plain text, `esc_url_raw()` for stored URLs, `sanitize_email()` plus validity checks for email, `absint()` for IDs, and bounded numeric validation for quantities, percentages, monetary values, dates, and offsets.
- Validate event/template/form IDs for expected existence and post type. Intersect submitted roles with registered roles. Recursively validate reminder/repeater arrays instead of accepting arbitrary keys.
- Escape all values at their output context.
- Do not log webinar links, recipient addresses, message bodies, or credentials.
- A malformed section should fail validation with the user returned to Advanced; unrelated saved data must remain intact.
- Global settings handlers independently require their settings nonce, required capability, and strict section allowlist; unauthorized or cross-section option writes are rejected.

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
9. Snapshot all event metadata before and after every omitted-section case and require byte-for-byte equality outside the owned keys.

### Runtime tests

- Confirm template, FAQ, timeline, related events, terms, SEO/schema, access restrictions, and attendee form affect their current frontend consumers.
- Confirm deposit values affect checkout calculations without repricing unrelated orders.
- Confirm PDF custom text reaches generated PDF output.
- Confirm reminders persist and their scheduler reads the saved configuration.
- Intercept `wp_mail` for a disposable event/order and verify qualifying status, billing/attendee recipient rules, event-specific body selection, dynamic tag replacement, and global fallback.
- Verify an event-level Email Message does not bypass globally disabled delivery.
- For reminders, assert scheduled hooks are added, updated, and removed without duplicates.
- For deposits, assert exact checkout totals for inherit, percent, fixed, invalid, and disabled cases.
- For PDF custom text, assert the generated document contains the expected sanitized content.

### Regression and quality checks

- PHP syntax checks for every modified PHP file.
- JavaScript syntax/lint checks supported by the repository.
- Existing automated tests for Free and Pro.
- Focused tests for default, legacy, enabled, disabled, empty, malformed, and missing-addon states.
- Acceptance combinations cover Free/Pro x Classic/Modern, new/existing events with missing meta, full save, every quiet-save owner, global save/reload, Pro deactivate/reactivate, JavaScript initialization failure, and no-JavaScript behavior where the editor supports it.
- Review diffs for unrelated UI/style or behavior changes.

## Completion Criteria

The work is complete only when the field matrix has no unexplained gaps; every control saves and reloads correctly; each setting reaches its runtime consumer; legacy data and Classic Editor behavior remain intact; email delivery is demonstrated through an intercepted real WordPress mail call; and relevant Free/Pro regressions pass.
