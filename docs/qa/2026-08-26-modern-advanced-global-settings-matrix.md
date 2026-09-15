# Modern Editor Advanced and Global Settings Matrix

Audit date: 2026-08-26

## Event Advanced step

| Section | Persisted contract | Frontend/runtime consumer | Result |
| --- | --- | --- | --- |
| Page Template | `mep_event_template` | Single-event template resolver | Pass; filename validation retained |
| Attendee Form (Pro) | `mep_event_reg_form_status`, `mep_event_reg_form_id`, `mep_fb_formbuilder_json`, `mep_fb_send_email_confirmation`, built-in/custom field data and conditional rules | Registration form renderer and attendee confirmation pipeline | Fixed presence-aware Free/Pro ownership and validated selected form IDs |
| Terms & Conditions (Pro) | `mep_disable_term_condition`, `mep_term_condition[]` | Checkout/event terms renderer | Fixed save presence handling and secured add/update/delete AJAX |
| FAQs | `mep_faq_status`, `mep_faq_description`, `mep_event_faq[]` | Single-event FAQ renderer | Pass; strict status and safe mismatched-array handling added |
| Timeline | `mep_timeline_status`, `mep_event_day[]` | Single-event timeline renderer | Pass; strict status and safe mismatched-array handling added |
| Related Events | `mep_related_event_status`, `related_section_label`, `event_list[]` | Related-event frontend query | Fixed event-ID/type validation, self exclusion, status allowlist, and AJAX capability check |
| Email Message | `mep_event_cc_email_status`, `mep_event_cc_email_text` | Confirmation email body resolver | Fixed canonical toggle, legacy migration behavior, TinyMCE sync, body fallback, and purchase delivery gates |
| Email Reminder (Pro) | `_mep_er_reminders[]`: `enabled`, `event_date_type`, `reminder_type`, `reminder_unit`, `reminder_value`, `email_template`, `custom_subject` | Reminder scheduler/cron sender | Fixed duplicate scheduling and added enum/range/template validation |
| PDF Custom Text (Pro) | `mep_pdf_custom_text` | PDF ticket templates | Fixed absent-field data loss and rich-text sanitization |
| SEO & Schema | `mep_rich_text_status`, `mep_rt_event_status`, `mep_rt_event_attandence_mode`, `mep_rt_event_prvdate` | Event JSON-LD/rich results | Fixed saved selection hydration, wrong rescheduled comparison, allowlists, and output escaping |
| Deposit / Partial Payment (Pro) | `mep_event_deposit_override`, `mep_event_deposit_type`, `mep_event_deposit_value` | Cart, checkout, orders, attendee records, email and PDF | Fixed enum/range validation; percentages are bounded to 0-100 |
| Access & Settings | `_sku`, `mep_show_end_datetime`, `mep_available_seat`, `mep_member_only_event`, `mep_member_only_user_role[]`; reset-booking action | Event display/access and inventory reset | Fixed role allowlist/live reveal and removed public reset AJAX registration |
| Waitlist Form (Pro) | `mep_show_waitlist` | Waitlist frontend form | Fixed Modern Editor save ownership; only touched when addon is loaded |
| Review & Rating (Pro) | `mep_show_review` | Review button/form | Pass; unset-meta legacy default remains enabled |

The section header control is now a real persistence control only where a canonical enabled state exists. Reminder, PDF custom text, and Access & Settings use disclosure buttons, so expanding/collapsing them cannot silently alter stored data.

## Global settings

The shared settings API now validates every registered field by its declared type, preserves unsubmitted/legacy keys, rejects unknown submitted keys, validates select/radio/multicheck allowlists, bounds declared numeric fields, sanitizes email/URL/color/rich-text values, and honors explicit field callbacks. Five invalid Free select defaults (map type, expiry basis, date-picker format, carousel autoplay and carousel loop) were aligned with their registered option values; the complete Free/Pro select-default audit now has zero mismatches.

### Free plugin: 117 registered fields

| Option section | Count | Registered fields |
| --- | ---: | --- |
| `general_setting_sec` | 46 | `seat_reserved_order_status`, `mep_disable_block_editor`, `mep_event_list_page_style`, `mep_event_edit_page_mode`, `mep_rest_api_status`, `mep_multi_lang_plugin`, `mep_event_list_order_by`, `mep_event_label`, `mep_event_slug`, `mep_event_icon`, `mep_event_cat_label`, `mep_event_cat_slug`, `mep_event_org_label`, `mep_event_org_slug`, `mep_google_map_type`, `google-map-api`, `mep_event_expire_on_datetimes`, `mep_hide_old_date`, `mep_hide_expire_ticket`, `mep_hide_location_from_order_page`, `mep_hide_date_from_order_page`, `mep_hide_expired_date_in_calendar`, `mep_event_direct_checkout`, `mep_show_zero_as_free`, `mep_ticket_expire_time`, `mep_ticket_expire_time_on_cart`, `mep_load_fontawesome_from_theme`, `mep_load_flaticon_from_theme`, `mep_load_assets_only_on_event_pages`, `mep_speed_up_list_page`, `mep_hide_not_available_event_from_list_page`, `mep_show_sold_out_ribbon_list_page`, `mep_show_limited_availability_ribbon`, `mep_limited_availability_threshold`, `mep_limited_availability_text`, `mep_show_low_stock_warning`, `mep_low_stock_threshold`, `mep_low_stock_text`, `mep_enable_low_stock_email`, `mep_show_hidden_wc_product`, `mep_google_map_zoom_level`, `mep_show_event_sidebar`, `mep_clear_cart_after_checkout`, `mep_manual_seat_Left_fix`, `mep_fix_details_page_fatal_error`, `mep_datepicker_format` |
| `event_list_setting_sec` | 8 | `mep_event_price_show`, `mep_date_list_in_event_listing`, `mep_event_hide_organizer_list`, `mep_event_hide_location_list`, `mep_event_hide_time_list`, `mep_event_hide_end_time_list`, `mep_hide_event_hover_btn`, `mep_hide_event_list_msg` |
| `single_event_setting_sec` | 16 | `mep_enable_speaker_list`, `mep_show_product_cat_in_event`, `mep_global_single_template`, `mep_event_product_type`, `mep_event_hide_date_from_details`, `mep_event_hide_time_from_details`, `mep_event_hide_location_from_details`, `mep_event_hide_total_seat_from_details`, `mep_event_hide_org_from_details`, `mep_event_hide_address_from_details`, `mep_event_hide_event_schedule_details`, `mep_event_hide_share_this_details`, `mep_event_hide_calendar_details`, `mep_event_hide_description_title`, `mep_event_hide_left_sidebar_title`, `mep_event_hide_time` |
| `email_setting_sec` | 6 | `mep_email_sending_order_status`, `mep_email_form_name`, `mep_email_form_email`, `mep_email_subject`, `mep_confirmation_email_text`, `mep_send_confirmation_to_billing_email` |
| `style_setting_sec` | 2 | `mpev_primary_color`, `mpev_secondary_color` |
| `icon_setting_sec` | 10 | Event date/time/location/organizer/list and Facebook/Twitter/LinkedIn/WhatsApp/email icons |
| `mp_slider_settings` | 9 | `slider_type`, `slider_style`, `indicator_visible`, `indicator_type`, `showcase_visible`, `showcase_position`, `popup_image_indicator`, `popup_icon_indicator`, `slider_height` |
| `carousel_setting_sec` | 4 | `mep_load_carousal_from_theme`, `mep_autoplay_carousal`, `mep_loop_carousal`, `mep_speed_carousal` |
| `payment_setting_sec` | 9 | Payment headings/UI plus WooCommerce enable, redirects, login, billing, ticket status, and gateway manager fields |
| `mep_currency_settings` | 6 | Currency information, symbol, position, decimal separator, thousand separator, decimals |
| `mep_eb_settings` | 1 | `mp_event_eb_type` |
| Licensing/status display sections | 0 | Display-only sections; no submitted fields |

### Pro plugin: 108 registered fields

| Option section | Count | Scope |
| --- | ---: | --- |
| `csv_checkout_export_fileds_sec` | 14 | Export order status plus 13 billing/export columns; fixed previously orphaned status field and invalid default |
| `mep_certificate_settings` | 12 | Enable, template, logo, company/title/subtitle/body/footer/attended text and colors |
| `mep_ai_assistant_settings` | 16 | Enable/provider plus API key and model for seven providers |
| `mep_deposit_settings` | 5 | Enable, customer choice, type, value, balance due days |
| `mep_review_permission_settings` | 2 | Review permission and average display |
| `mep_social_card_setting_sec` | 9 | Enable, statuses, button, networks, frame, font and colors |
| `mep_waitlist_email_settings` | 10 | Admin/customer/availability email enables, recipients, subjects and templates |
| `mep_pdf_gen_settings` | 22 | PDF library/theme/assets/colors/company/terms and billing fields |
| `mep_pdf_email_settings` | 18 | Trigger statuses, sender/recipient rules, body/subject/calendar, attendee and billing controls, display-only headings |

## Runtime acceptance cases

- Existing event body with no status meta resolves to the event body.
- Explicit `on` plus non-empty body resolves to the event body.
- Explicit `off`, or `on` plus empty body, resolves to global body then preset.
- Processing/completed billing emails require both the billing toggle and matching global order status.
- Modern save reload retains enabled/disabled values, rich text, lists, access roles, and legacy Pro data.
- All temporary posts/options used by WP-CLI smoke tests are deleted/restored.
