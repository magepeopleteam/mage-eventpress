(function ($) {
    "use strict";

    const STEP_KEY_FALLBACK = 'basic';

    function parseHash() {
        const raw = (window.location.hash || '').replace(/^#/, '');
        const parts = raw.split('/').filter(Boolean);
        if (parts.length >= 4 && parts[0] === 'events' && parts[1] === 'edit') {
            return { mode: 'edit', eventId: parseInt(parts[2], 10) || 0, stepKey: parts[3] || STEP_KEY_FALLBACK };
        }
        if (parts.length >= 3 && parts[0] === 'events' && parts[1] === 'new') {
            return { mode: 'new', eventId: 0, stepKey: parts[2] || STEP_KEY_FALLBACK };
        }
        return { mode: 'edit', eventId: 0, stepKey: STEP_KEY_FALLBACK };
    }

    function setHash(mode, eventId, stepKey) {
        const cleanKey = (stepKey || STEP_KEY_FALLBACK).toString();
        const id = parseInt(eventId, 10) || 0;
        if (mode === 'new' && !id) {
            window.location.hash = '#/events/new/' + cleanKey;
            return;
        }
        window.location.hash = '#/events/edit/' + id + '/' + cleanKey;
    }

    function getConfig() {
        return window.mpwemEventEdit || {};
    }

    function showNotice($root, message, type) {
        const $notice = $root.find('.mpwem-wizard-notice').first();
        if (!$notice.length) {
            if (message) alert(message);
            return;
        }

        $notice
            .removeClass('notice-success notice-error notice-warning')
            .addClass(type === 'error' ? 'notice-error' : 'notice-success')
            .text(message)
            .show();
    }

    function showToast(message, type) {
        let $toast = $('.mpwem-toast');
        const toastType = type || 'info';
        const iconClass = toastType === 'error' ? 'dashicons-warning' : 'dashicons-update-alt';

        if ($toast.length === 0) {
            $toast = $(`
                <div class="mpwem-toast">
                    <span class="dashicons"></span>
                    <span class="mpwem-toast-text"></span>
                </div>
            `);
            $('body').append($toast);
        }

        window.clearTimeout($toast.data('mpwemToastTimer'));
        $toast.removeClass('show is-error is-success is-info').addClass('is-' + toastType);
        $toast.find('.dashicons').attr('class', 'dashicons ' + iconClass);
        $toast.find('.mpwem-toast-text').text(message || '');
        $toast[0].offsetHeight;
        $toast.addClass('show');

        const timer = window.setTimeout(function() {
            $toast.removeClass('show');
        }, toastType === 'error' ? 4200 : 2600);

        $toast.data('mpwemToastTimer', timer);
    }

    function getWizardRoot() {
        return $('.mpwem-event-wizard').first();
    }

    function getPanel($root, panelId) {
        // Look for legacy panels (mp_tab_item) or our wizard panels
        let $panel = $root.find('.mp_tab_item[data-tab-item="' + panelId + '"]').first();
        if (!$panel.length) {
            $panel = $root.find(panelId).first();
        }
        return $panel;
    }

    function mountPanel($root, panelSelector, mountId) {
        const $mount = $('#' + mountId);
        if (!$mount.length) return;
        
        const $panel = getPanel($root, panelSelector);
        if (!$panel.length) return;

        if ($panel.parent()[0] !== $mount[0]) {
            $panel.addClass('mpwem-embedded-panel').detach().appendTo($mount).show();
        }
    }

    function mountAll($root) {
        // Mount into Basic Step Media sidebar
        mountPanel($root, '#ttbm_settings_gallery', 'mpwem_wizard_media_mount_basic');

        const $galleryMount = $('#mpwem_wizard_media_mount_basic');
        const $thumbnailMount = $('#mpwem_wizard_thumbnail_mount_basic');
        if ($galleryMount.length && $thumbnailMount.length) {
            const $galleryPanel = $galleryMount.children('.mp_tab_item').first();
            const $thumbnailBody = $galleryPanel.children('.mpwem_style').filter(function() {
                return $(this).find('input[name="mep_list_thumbnail"]').length > 0;
            }).first();
            const $thumbnailHeader = $thumbnailBody.prev('section.bg-light');

            if ($thumbnailHeader.length && $thumbnailBody.length) {
                $thumbnailMount.append($thumbnailHeader.detach(), $thumbnailBody.detach());
            }
        }

        const $galleryCardHead = $('#mpwem_gallery_images_card_toggle');
        const $galleryToggleSection = $galleryMount.find('input[name="mep_display_slider"]').first().closest('section');
        const $galleryToggle = $galleryToggleSection.find('.mpev-switch, .mpwem-switch-wrap').first();
        const $galleryBodyLabel = $galleryMount.find('#mep_display_slider > section .mpev-label').first();

        if ($galleryCardHead.length && $galleryToggle.length && !$galleryCardHead.children().length) {
            $galleryCardHead.append($galleryToggle.detach());
        }

        if ($galleryToggleSection.length) {
            $galleryToggleSection.remove();
        }

        if ($galleryBodyLabel.length) {
            $galleryBodyLabel.remove();
        }
        
        // Convert label-text into info icons for sidebar items
        $('#mpwem_wizard_media_mount_basic section').each(function() {
            const $heading = $(this).find('h2').first();
            const $info = $(this).find('.label-text').first();
            if ($heading.length && $info.length) {
                const infoText = $info.text().trim();
                if (infoText && $heading.find('.mpwem-info-icon').length === 0) {
                    // Remove any existing rogue info icons before appending ours
                    $heading.find('.dashicons-info, .fa-info-circle').remove();
                    // $heading.append(' <span class="dashicons dashicons-info mpwem-info-icon" title="' + infoText.replace(/"/g, '&quot;') + '"></span>');
                }
                $info.hide();
            }
        });
        $('#mpwem_wizard_thumbnail_mount_basic section').each(function() {
            const $heading = $(this).find('h2').first();
            const $info = $(this).find('.label-text').first();
            if ($heading.length && $info.length) {
                $info.hide();
            }
        });
        mountPanel($root, '#mp_event_venue', 'mpwem_wizard_venue_mount');
        
        // Mount into Tickets Step
        mountPanel($root, '#mpwem_ticket_pricing_settings', 'mpwem_wizard_tickets_mount');

        const $ticketPricingPanel = getPanel($root, '#mpwem_ticket_pricing_settings');

        // Move Extra Services into its own card
        const $exService = $ticketPricingPanel.find('input[name="option_name[]"]').first().closest('._layout_default_xs_mp_zero');
        if ($exService.length) {
            const $extraMount = $('#mpwem_wizard_extra_services_mount');
            if ($extraMount.length) {
                $exService.addClass('mpwem_style');
                $exService.detach().appendTo($extraMount);
                $('#mpwem_wizard_extra_services_card').show();
                // Remove the legacy header from inside the card body since our new card has its own head
                $exService.find('._bg_light_padding').first().hide();
            }
        }

        // Move the Ticket Pricing documentation section into the wizard sidebar
        const $ticketDoc = $ticketPricingPanel.find('section.bg-light').last();
        if ($ticketDoc.length) {
            const $moreDocs = $ticketDoc.next('section');
            const $ticketStep = $root.find('.mpwem-wizard-panel[data-tab-item="#mpwem_wizard_tickets"]');
            const $sidebar = $ticketStep.find('.mpwem-event-wizard__sidebar').first();
            if ($sidebar.length) {
                // Build a unified custom card for the documentation
                const title = $ticketDoc.find('h2').text() || 'Documentation Links';
                const desc = $ticketDoc.find('span').text() || 'Get Documentation';
                
                const $newCard = $('<div class="mpwem-card mpwem-docs-card mpwem-card--help mpwem-card--help-docs"></div>');
                const $cardHead = $('<div class="mpwem-card__head"></div>');
                $cardHead.append($('<h2></h2>').text(title));
                $cardHead.append($('<p></p>').text(desc));
                
                const $cardBody = $('<div class="mpwem-card__body"></div>');
                if ($moreDocs.length) {
                    $cardBody.append($moreDocs.contents());
                }
                
                $newCard.append($cardHead).append($cardBody);
                $sidebar.append($newCard);
                
                // Remove the old legacy sections entirely
                $ticketDoc.remove();
                if ($moreDocs.length) {
                    $moreDocs.remove();
                }
            }
        }

        initializeTicketPricingModal($root);
        initializeExtraServiceModal($root);

        // Mount into Date Step
        mountPanel($root, '#mpwem_date_settings', 'mpwem_wizard_date_mount');

        // Mount into Advanced Step
        mountPanel($root, '#mep_event_template', 'mpwem_wizard_template_mount');
        mountPanel($root, '#mep_event_faq_meta', 'mpwem_wizard_faq_mount');
        mountPanel($root, '#mep_event_timeline_meta', 'mpwem_wizard_timeline_mount');
        mountPanel($root, '#mep_related_event_meta', 'mpwem_wizard_related_mount');
        mountPanel($root, '#mpwem_email_text_settings', 'mpwem_wizard_email_mount');
        mountPanel($root, '#mp_event_rich_text', 'mpwem_wizard_seo_mount');
        enhanceDisplayStep($root);
        mountDangerZone($root);
        
        // Handle remaining legacy panels (Additional Sections)
        const $steps = $root.find('.mpwem-step');
        const stepPanelSelectors = [];
        $steps.each(function() { stepPanelSelectors.push($(this).data('panel')); });
        
        const $additionalMount = $('#mpwem_additional_sections_mount');
        if ($additionalMount.length) {
            $root.find('.mpwem-wizard-panels > .mp_tab_item').each(function() {
                const $p = $(this);
                const id = $p.data('tab-item') || ('#' + this.id);
                // Don't move if it's a step panel or already handled
                if (stepPanelSelectors.indexOf(id) !== -1 || id === '#mp_event_venue' || id === '#ttbm_settings_gallery' || $p.hasClass('mpwem-wizard-panel')) {
                    return;
                }
                $p.addClass('mpwem-embedded-panel mpwem-legacy-panel').detach().appendTo($additionalMount).show();
            });
            if ($additionalMount.children().length) $('#mpwem_edit_page_additional').show();
        }
    }

    function getTicketModalContext($root) {
        const $ticketPanel = getPanel($root, '#mpwem_ticket_pricing_settings');
        let $summary = $ticketPanel.find('#mpwem_ticket_summary').first();

        if ($ticketPanel.length && !$summary.length) {
            const $summaryMarkup = $(
                '<div class="mpwem-ticket-summary" id="mpwem_ticket_summary">' +
                    '<div class="mpwem-ticket-summary__toolbar">' +
                        '<div class="mpwem-ticket-summary__intro">' +
                            '<span class="mpwem-ticket-summary__eyebrow">Ticket Overview</span>' +
                            '<h3>Simple ticket list</h3>' +
                            '<p>View pricing at a glance, then open the full editor only when you need details.</p>' +
                        '</div>' +
                        '<div class="mpwem-ticket-summary__actions">' +
                            '<button type="button" class="button button-secondary mpwem-ticket-summary__open" data-mpwem-ticket-modal-open="list">Show Details</button>' +
                            '<button type="button" class="button button-primary mpwem-ticket-summary__add" data-mpwem-ticket-modal-open="new">+ Add New Ticket Type</button>' +
                        '</div>' +
                    '</div>' +
                    '<div class="mpwem-ticket-summary__list" id="mpwem_ticket_summary_list"></div>' +
                '</div>'
            );

            const $summaryHost = $ticketPanel.find('> ._layout_default_xs_mp_zero').first();
            if ($summaryHost.length) {
                $summaryHost.append($summaryMarkup);
                $summary = $summaryMarkup;
            }
        }

        return {
            $summaryList: $summary.find('#mpwem_ticket_summary_list').first(),
            $modal: $root.find('#mpwem_ticket_editor_modal').first(),
            $modalMount: $root.find('#mpwem_ticket_modal_mount').first(),
            $modalAdvanceToggle: $root.find('#mpwem_ticket_modal_advance_toggle').first(),
            $legacyMount: $root.find('#mpwem_wizard_tickets_mount').first()
        };
    }

    function getTicketRows($root) {
        const $tbody = $root.find('#mpwem_ticket_modal_mount .mpwem_ticket_table tbody.mpwem_item_insert').first();
        if (!$tbody.length) {
            return $();
        }

        return $tbody.children('tr.mpwem_remove_area').filter(function() {
            return $(this).closest('.mpwem_hidden_content').length === 0;
        });
    }

    function ticketRowName($row) {
        const $nameInput = $row.find('[name="option_name_t[]"]').first();
        const inputValue = ($nameInput.val() || '').toString().trim();
        if (inputValue) {
            return inputValue;
        }

        const textValue = $row.find('.ticket_name ._flex_wrap > span').first().text().trim();
        return textValue || 'Untitled Ticket';
    }

    function ticketRowPrice($row) {
        const value = ($row.find('[name="option_price_t[]"]').first().val() || '').toString().trim();
        if (!value.length) {
            return 'Price not set';
        }

        return value === '0' ? 'Free' : value;
    }

    function ticketRowCapacity($row) {
        const value = ($row.find('[name="option_qty_t[]"]').first().val() || '').toString().trim();
        return value.length ? value : 'Unlimited';
    }

    function ticketRowDescription($row) {
        const value = ($row.find('[name="option_details_t[]"]').first().val() || '').toString().trim();
        return value || 'No short description yet.';
    }

    function highlightTicketRow($row) {
        if (!$row || !$row.length) {
            return;
        }

        const $container = $row.closest('.mpwem-ticket-modal__body');
        if ($container.length) {
            const top = $row.position().top + $container.scrollTop() - 24;
            $container.animate({ scrollTop: Math.max(top, 0) }, 250);
        }

        $row.addClass('mpwem-ticket-row-focus');
        window.setTimeout(function() {
            $row.removeClass('mpwem-ticket-row-focus');
        }, 1800);
    }

    function renderTicketSummary($root) {
        const context = getTicketModalContext($root);
        const $ticketPanel = getPanel($root, '#mpwem_ticket_pricing_settings');
        const regEnabled = $ticketPanel.find('input[name="mep_reg_status"]').first().is(':checked');
        const $rows = getTicketRows($root);
        if (!context.$summaryList.length) {
            return;
        }

        context.$summaryList.empty();

        $ticketPanel.find('#mpwem_ticket_summary').toggle(regEnabled);

        if (!regEnabled) {
            closeTicketModal($root);
            return;
        }

        context.$summaryList.append(
            $('<div class="mpwem-ticket-summary__header"></div>')
                .append($('<span class="mpwem-ticket-summary__header-ticket"></span>').text('Ticket Type'))
                .append($('<span class="mpwem-ticket-summary__header-price"></span>').text('Price'))
                // .append($('<span class="mpwem-ticket-summary__header-action"></span>').text('Action'))
        );

        if (!$rows.length) {
            context.$summaryList.append(
                $('<div class="mpwem-ticket-summary__empty"></div>')
                    .append($('<h4></h4>').text('No ticket types yet'))
                    .append($('<p></p>').text('Start with one ticket type, then open the editor whenever you need the full pricing table.'))
                    .append($('<button type="button" class="button button-primary"></button>')
                        .attr('data-mpwem-ticket-modal-open', 'new')
                        .text('Create First Ticket'))
            );
            return;
        }

        $rows.each(function(index) {
            const $row = $(this);
            const name = ticketRowName($row);
            const price = ticketRowPrice($row);
            const isDisabled = $row.hasClass('disable_row');

            context.$summaryList.append(
                $('<article class="mpwem-ticket-summary__item"></article>')
                    .attr('data-ticket-row-index', index)
                    .toggleClass('is-disabled', isDisabled)
                    .append(
                        $('<div class="mpwem-ticket-summary__item-main"></div>')
                            .append(
                                $('<div class="mpwem-ticket-summary__item-head"></div>')
                                    .append($('<h4></h4>').text(name))
                                    // .append($('<span class="mpwem-ticket-summary__status"></span>').text(isDisabled ? 'Hidden' : 'Active'))
                            )
                    )
                    .append(
                        $('<div class="mpwem-ticket-summary__meta"></div>')
                            .append($('<span class="mpwem-ticket-summary__price"></span>').text(price))
                    )
                    // .append(
                    //     $('<div class="mpwem-ticket-summary__item-actions"></div>')
                    //         .append(
                    //             $('<button type="button" class="button button-secondary"></button>')
                    //                 .attr('data-mpwem-ticket-modal-open', 'details')
                    //                 .attr('data-ticket-row-index', index)
                    //                 .text('Details')
                    //         )
                    // )
            );
        });
    }

    function openTicketModal($root, mode, rowIndex) {
        const context = getTicketModalContext($root);
        if (!context.$modal.length) {
            return;
        }

        context.$modal.attr('aria-hidden', 'false').addClass('is-open');
        $('body').addClass('mpwem-ticket-modal-open');

        const isNew = mode === 'new';
        $root.find('#mpwem_ticket_modal_title').text(isNew ? 'Add ticket type' : 'Manage ticket types');
        $root.find('#mpwem_ticket_modal_description').text(
            isNew
                ? 'Create a new ticket type, then fill in pricing, capacity, and any advanced values.'
                : 'Edit pricing, capacities, advanced columns, and ticket settings without leaving this step.'
        );

        if (isNew) {
            const $addButton = context.$modalMount.find('.mpwem_add_item').first();
            if ($addButton.length) {
                $addButton.trigger('click');
                window.setTimeout(function() {
                    enhanceSelects(context.$modalMount);
                    const $rows = getTicketRows($root);
                    highlightTicketRow($rows.last());
                    renderTicketSummary($root);
                }, 100);
            }
            return;
        }

        if (typeof rowIndex === 'number' && rowIndex >= 0) {
            const $row = getTicketRows($root).eq(rowIndex);
            window.setTimeout(function() {
                highlightTicketRow($row);
            }, 60);
        }
    }

    function closeTicketModal($root) {
        const context = getTicketModalContext($root);
        if (!context.$modal.length) {
            return;
        }

        context.$modal.attr('aria-hidden', 'true').removeClass('is-open');
        $('body').removeClass('mpwem-ticket-modal-open');
    }

    function syncTicketAdvancedColumns($root) {
        const context = getTicketModalContext($root);
        const $toggle = context.$modalMount.find('input[name="mep_show_advance_col_status"]').first();
        const $targets = context.$modalMount.find('[data-collapse="#mep_show_advance_col_status"]');
        if (!$toggle.length || !$targets.length) {
            return;
        }

        const isVisible = $toggle.is(':checked');
        context.$modal.toggleClass('is-advanced-visible', isVisible);
        $targets.toggleClass('mpwem-ticket-col-hidden', !isVisible);
    }

    function initializeTicketTableDragScroll($root) {
        const context = getTicketModalContext($root);
        const $scroller = context.$modalMount.find('._ov_auto').first();
        if (!$scroller.length || $scroller.data('mpwemDragScrollInit')) {
            return;
        }

        const interactiveSelector = 'input, textarea, select, button, a, label, .mpwem-select-wrapper, .ui-datepicker, .wp-picker-container';
        let isDragging = false;
        let startX = 0;
        let startScrollLeft = 0;

        $scroller.on('mousedown.mpwemDragScroll', function(e) {
            if (e.button !== 0) {
                return;
            }

            if ($(e.target).closest(interactiveSelector).length) {
                return;
            }

            const hasHorizontalOverflow = this.scrollWidth > this.clientWidth + 2;
            if (!hasHorizontalOverflow) {
                return;
            }

            isDragging = true;
            startX = e.pageX;
            startScrollLeft = this.scrollLeft;
            $scroller.addClass('is-dragging');
            e.preventDefault();
        });

        $(document).on('mousemove.mpwemDragScroll', function(e) {
            if (!isDragging) {
                return;
            }

            const deltaX = e.pageX - startX;
            $scroller.scrollLeft(startScrollLeft - deltaX);
        });

        $(document).on('mouseup.mpwemDragScroll mouseleave.mpwemDragScroll', function() {
            if (!isDragging) {
                return;
            }

            isDragging = false;
            $scroller.removeClass('is-dragging');
        });

        $scroller.data('mpwemDragScrollInit', true);
    }

    function initializeTicketPricingModal($root) {
        const context = getTicketModalContext($root);
        const $ticketPanel = getPanel($root, '#mpwem_ticket_pricing_settings');
        const $ticketSettingsBlock = $ticketPanel.find('.mpwem-ticket-editor-section').first();

        if (!$ticketPanel.length || !context.$modalMount.length || !$ticketSettingsBlock.length) {
            return;
        }

        if ($ticketSettingsBlock.parent()[0] !== context.$modalMount[0]) {
            context.$modalMount.append($ticketSettingsBlock.detach());
        }

        context.$modalMount.find('.mpwem-ticket-editor-section > ._bg_light_padding').first().hide();
        context.$modalMount.find('.mpwem_settings_area > p').first().addClass('mpwem-ticket-modal__note');
        context.$modalMount.find('.mpwem_add_new_button_area').first().addClass('mpwem-ticket-modal__inline-actions');

        enhanceSelects(context.$modalMount);
        renderTicketSummary($root);
        syncTicketAdvancedColumns($root);
        initializeTicketTableDragScroll($root);

        const $tbody = context.$modalMount.find('.mpwem_ticket_table tbody.mpwem_item_insert').first();
        if ($tbody.length && !$tbody.data('mpwemSummaryObserver')) {
            const observer = new MutationObserver(function() {
                enhanceSelects(context.$modalMount);
                renderTicketSummary($root);
                syncTicketAdvancedColumns($root);
                initializeTicketTableDragScroll($root);
            });
            observer.observe($tbody[0], { childList: true, subtree: true });
            $tbody.data('mpwemSummaryObserver', observer);
        }

        $root.off('.mpwemTicketModal');

        $root.on('click.mpwemTicketModal', '[data-mpwem-ticket-modal-open]', function(e) {
            e.preventDefault();
            const mode = $(this).attr('data-mpwem-ticket-modal-open') || 'list';
            const rowIndex = parseInt($(this).attr('data-ticket-row-index'), 10);
            openTicketModal($root, mode, Number.isNaN(rowIndex) ? null : rowIndex);
        });

        $root.on('click.mpwemTicketModal', '[data-mpwem-ticket-modal-close]', function(e) {
            e.preventDefault();
            closeTicketModal($root);
        });

        $root.on('input.mpwemTicketModal change.mpwemTicketModal', '#mpwem_ticket_modal_mount [name="option_name_t[]"], #mpwem_ticket_modal_mount [name="option_details_t[]"], #mpwem_ticket_modal_mount [name="option_price_t[]"], #mpwem_ticket_modal_mount [name="option_qty_t[]"], #mpwem_ticket_modal_mount [name="option_ticket_enable[]"]', function() {
            renderTicketSummary($root);
        });

        $root.on('change.mpwemTicketModal', '[name="mep_reg_status"]', function() {
            renderTicketSummary($root);
        });

        $root.on('change.mpwemTicketModal', '#mpwem_ticket_modal_mount input[name="mep_show_advance_col_status"]', function() {
            syncTicketAdvancedColumns($root);
        });

        $root.on('click.mpwemTicketModal', '#mpwem_ticket_modal_mount .mpwem_item_remove, #mpwem_ticket_modal_mount .mpwem_show_hide_button', function() {
            window.setTimeout(function() {
                renderTicketSummary($root);
                syncTicketAdvancedColumns($root);
            }, 280);
        });

        $(document)
            .off('keydown.mpwemTicketModal')
            .on('keydown.mpwemTicketModal', function(e) {
                if (e.key === 'Escape' && context.$modal.hasClass('is-open')) {
                    closeTicketModal($root);
                }
            });
    }

    function getExtraServiceModalContext($root) {
        const $mount = $root.find('#mpwem_wizard_extra_services_mount').first();
        let $summary = $mount.find('#mpwem_extra_service_summary').first();

        if ($mount.length && !$summary.length) {
            const $summaryMarkup = $(
                '<div class="mpwem-ticket-summary" id="mpwem_extra_service_summary">' +
                    '<div class="mpwem-ticket-summary__toolbar">' +
                        '<div class="mpwem-ticket-summary__intro">' +
                            '<span class="mpwem-ticket-summary__eyebrow">Extra Service Overview</span>' +
                            '<h3>Simple add-on list</h3>' +
                            '<p>Review optional services at a glance, then open the full editor when you need pricing or quantity details.</p>' +
                        '</div>' +
                        '<div class="mpwem-ticket-summary__actions">' +
                            '<button type="button" class="button button-secondary" data-mpwem-extra-modal-open="list">Show Details</button>' +
                            '<button type="button" class="button button-primary" data-mpwem-extra-modal-open="new">+ Add Extra Service</button>' +
                        '</div>' +
                    '</div>' +
                    '<div class="mpwem-ticket-summary__list" id="mpwem_extra_service_summary_list"></div>' +
                '</div>'
            );

            $mount.append($summaryMarkup);
            $summary = $summaryMarkup;
        }

        return {
            $summaryList: $summary.find('#mpwem_extra_service_summary_list').first(),
            $modal: $root.find('#mpwem_extra_service_editor_modal').first(),
            $modalMount: $root.find('#mpwem_extra_service_modal_mount').first(),
            $legacyMount: $mount
        };
    }

    function getExtraServiceRows($root) {
        const $tbody = $root.find('#mpwem_extra_service_modal_mount tbody.mpwem_item_insert').first();
        if (!$tbody.length) {
            return $();
        }

        return $tbody.children('tr.mpwem_remove_area').filter(function() {
            return $(this).closest('.mpwem_hidden_content').length === 0;
        });
    }

    function extraServiceRowName($row) {
        const value = ($row.find('[name="option_name[]"]').first().val() || '').toString().trim();
        return value || 'Untitled Service';
    }

    function extraServiceRowPrice($row) {
        const value = ($row.find('[name="option_price[]"]').first().val() || '').toString().trim();
        if (!value.length) {
            return 'Price not set';
        }

        return value === '0' ? 'Free' : value;
    }

    function extraServiceRowQty($row) {
        const value = ($row.find('[name="option_qty[]"]').first().val() || '').toString().trim();
        return value.length ? value : 'Unlimited';
    }

    function highlightExtraServiceRow($row) {
        if (!$row || !$row.length) {
            return;
        }

        const $container = $row.closest('.mpwem-ticket-modal__body');
        if ($container.length) {
            const top = $row.position().top + $container.scrollTop() - 24;
            $container.animate({ scrollTop: Math.max(top, 0) }, 250);
        }

        $row.addClass('mpwem-ticket-row-focus');
        window.setTimeout(function() {
            $row.removeClass('mpwem-ticket-row-focus');
        }, 1800);
    }

    function renderExtraServiceSummary($root) {
        const context = getExtraServiceModalContext($root);
        const $rows = getExtraServiceRows($root);
        if (!context.$summaryList.length) {
            return;
        }

        context.$summaryList.empty();

        context.$summaryList.append(
            $('<div class="mpwem-ticket-summary__header"></div>')
                .append($('<span class="mpwem-ticket-summary__header-ticket"></span>').text('Extra Service'))
                .append($('<span class="mpwem-ticket-summary__header-price"></span>').text('Price'))
        );

        if (!$rows.length) {
            context.$summaryList.append(
                $('<div class="mpwem-ticket-summary__empty"></div>')
                    .append($('<h4></h4>').text('No extra services yet'))
                    .append($('<p></p>').text('Create optional add-ons like meals, merchandise, or upgrades, then manage them in the full editor.'))
                    .append($('<button type="button" class="button button-primary"></button>')
                        .attr('data-mpwem-extra-modal-open', 'new')
                        .text('Create First Extra Service'))
            );
            return;
        }

        $rows.each(function(index) {
            const $row = $(this);

            context.$summaryList.append(
                $('<article class="mpwem-ticket-summary__item"></article>')
                    .attr('data-extra-service-row-index', index)
                    .append(
                        $('<div class="mpwem-ticket-summary__item-main"></div>')
                            .append(
                                $('<div class="mpwem-ticket-summary__item-head"></div>')
                                    .append($('<h4></h4>').text(extraServiceRowName($row)))
                            )
                    )
                    .append(
                        $('<div class="mpwem-ticket-summary__meta"></div>')
                            .append($('<span class="mpwem-ticket-summary__price"></span>').text(extraServiceRowPrice($row)))
                            .append($('<span class="mpwem-ticket-summary__capacity"></span>').text('Qty ' + extraServiceRowQty($row)))
                    )
            );
        });
    }

    function openExtraServiceModal($root, mode, rowIndex) {
        const context = getExtraServiceModalContext($root);
        if (!context.$modal.length) {
            return;
        }

        context.$modal.attr('aria-hidden', 'false').addClass('is-open');
        $('body').addClass('mpwem-ticket-modal-open');

        const isNew = mode === 'new';
        $root.find('#mpwem_extra_service_modal_title').text(isNew ? 'Add extra service' : 'Manage extra services');
        $root.find('#mpwem_extra_service_modal_description').text(
            isNew
                ? 'Create a new optional add-on, then fill in its price, available quantity, and quantity box settings.'
                : 'Edit optional add-ons, pricing, stock, and quantity settings without leaving this step.'
        );

        if (isNew) {
            const $addButton = context.$modalMount.find('.mpwem_add_item').first();
            if ($addButton.length) {
                $addButton.trigger('click');
                window.setTimeout(function() {
                    const $rows = getExtraServiceRows($root);
                    highlightExtraServiceRow($rows.last());
                    renderExtraServiceSummary($root);
                }, 100);
            }
            return;
        }

        if (typeof rowIndex === 'number' && rowIndex >= 0) {
            const $row = getExtraServiceRows($root).eq(rowIndex);
            window.setTimeout(function() {
                highlightExtraServiceRow($row);
            }, 60);
        }
    }

    function closeExtraServiceModal($root) {
        const context = getExtraServiceModalContext($root);
        if (!context.$modal.length) {
            return;
        }

        context.$modal.attr('aria-hidden', 'true').removeClass('is-open');
        $('body').removeClass('mpwem-ticket-modal-open');
    }

    function initializeExtraServiceModal($root) {
        const context = getExtraServiceModalContext($root);
        const $serviceBlock = context.$legacyMount.children('._layout_default_xs_mp_zero').first();

        if (!context.$legacyMount.length || !context.$modalMount.length || !$serviceBlock.length) {
            return;
        }

        if ($serviceBlock.parent()[0] !== context.$modalMount[0]) {
            context.$modalMount.append($serviceBlock.detach());
        }

        context.$modalMount.find('> ._layout_default_xs_mp_zero > ._bg_light_padding').first().hide();
        context.$modalMount.find('.mpwem_add_new_button_area').first().addClass('mpwem-ticket-modal__inline-actions');
        context.$modalMount.find('.mpwem_settings_area').first().addClass('mpwem-extra-service-settings-area');

        renderExtraServiceSummary($root);
        initializeExtraServiceTableDragScroll($root);

        const $tbody = context.$modalMount.find('tbody.mpwem_item_insert').first();
        if ($tbody.length && !$tbody.data('mpwemExtraSummaryObserver')) {
            const observer = new MutationObserver(function() {
                renderExtraServiceSummary($root);
                initializeExtraServiceTableDragScroll($root);
            });
            observer.observe($tbody[0], { childList: true, subtree: true });
            $tbody.data('mpwemExtraSummaryObserver', observer);
        }

        $root.off('.mpwemExtraModal');

        $root.on('click.mpwemExtraModal', '[data-mpwem-extra-modal-open]', function(e) {
            e.preventDefault();
            const mode = $(this).attr('data-mpwem-extra-modal-open') || 'list';
            const rowIndex = parseInt($(this).attr('data-extra-service-row-index'), 10);
            openExtraServiceModal($root, mode, Number.isNaN(rowIndex) ? null : rowIndex);
        });

        $root.on('click.mpwemExtraModal', '[data-mpwem-extra-modal-close]', function(e) {
            e.preventDefault();
            closeExtraServiceModal($root);
        });

        $root.on('input.mpwemExtraModal change.mpwemExtraModal', '#mpwem_extra_service_modal_mount [name="option_name[]"], #mpwem_extra_service_modal_mount [name="option_price[]"], #mpwem_extra_service_modal_mount [name="option_qty[]"]', function() {
            renderExtraServiceSummary($root);
        });

        $root.on('click.mpwemExtraModal', '#mpwem_extra_service_modal_mount .mpwem_item_remove', function() {
            window.setTimeout(function() {
                renderExtraServiceSummary($root);
            }, 280);
        });

        $(document)
            .off('keydown.mpwemExtraModal')
            .on('keydown.mpwemExtraModal', function(e) {
                if (e.key === 'Escape' && context.$modal.hasClass('is-open')) {
                    closeExtraServiceModal($root);
                }
            });
    }

    function initializeExtraServiceTableDragScroll($root) {
        const context = getExtraServiceModalContext($root);
        const $scroller = context.$modalMount.find('._ov_auto').first();
        if (!$scroller.length || $scroller.data('mpwemExtraDragScrollInit')) {
            return;
        }

        const interactiveSelector = 'input, textarea, select, button, a, label, .mpwem-select-wrapper, .ui-datepicker, .wp-picker-container';
        let isDragging = false;
        let startX = 0;
        let startScrollLeft = 0;

        $scroller.on('mousedown.mpwemExtraDragScroll', function(e) {
            if (e.button !== 0) {
                return;
            }

            if ($(e.target).closest(interactiveSelector).length) {
                return;
            }

            const hasHorizontalOverflow = this.scrollWidth > this.clientWidth + 2;
            if (!hasHorizontalOverflow) {
                return;
            }

            isDragging = true;
            startX = e.pageX;
            startScrollLeft = this.scrollLeft;
            $scroller.addClass('is-dragging');
            e.preventDefault();
        });

        $(document).on('mousemove.mpwemExtraDragScroll', function(e) {
            if (!isDragging) {
                return;
            }

            const deltaX = e.pageX - startX;
            $scroller.scrollLeft(startScrollLeft - deltaX);
        });

        $(document).on('mouseup.mpwemExtraDragScroll mouseleave.mpwemExtraDragScroll', function() {
            if (!isDragging) {
                return;
            }

            isDragging = false;
            $scroller.removeClass('is-dragging');
        });

        $scroller.data('mpwemExtraDragScrollInit', true);
    }

    function enhanceDisplayStep($root) {
        const $displayPanel = $root.find('[data-tab-item="#mpwem_wizard_display"]').first();
        const $displayMainStack = $displayPanel.find('.mpwem-event-wizard__main .mpwem-card__body.mpwem-display-stack').first();
        const $displaySidebar = $displayPanel.find('.mpwem-event-wizard__sidebar').first();
        let $displaySidebarStack = $displaySidebar.children('.mpwem-display-sidebar-stack').first();

        if ($displaySidebar.length && !$displaySidebarStack.length) {
            $displaySidebarStack = $('<div class="mpwem-display-sidebar-stack"></div>');
            $displaySidebarStack.append($displaySidebar.children());
            $displaySidebar.append($displaySidebarStack);
        }

        const sections = [
            {
                mount: '#mpwem_wizard_template_mount',
                className: 'mpwem-display-section--template',
                title: 'Page Template',
                desc: 'Choose the public event detail layout.',
                icon: 'dashicons-layout'
            },
            {
                mount: '#mpwem_wizard_faq_mount',
                className: 'mpwem-display-section--faq',
                title: 'FAQs',
                desc: 'Answer common attendee questions before checkout.',
                icon: 'dashicons-editor-help'
            },
            {
                mount: '#mpwem_wizard_timeline_mount',
                className: 'mpwem-display-section--timeline',
                title: 'Timeline',
                desc: 'Show the event flow in an organized attendee-friendly format.',
                icon: 'dashicons-clock'
            },
            {
                mount: '#mpwem_wizard_related_mount',
                className: 'mpwem-display-section--related',
                title: 'Related Events',
                desc: 'Promote similar or upcoming events alongside this one.',
                icon: 'dashicons-networking'
            },
            {
                mount: '#mpwem_wizard_email_mount',
                className: 'mpwem-display-section--email',
                title: 'Email Message',
                desc: 'Customize attendee confirmation email content.',
                icon: 'dashicons-email-alt'
            },
            {
                mount: '#mpwem_wizard_seo_mount',
                className: 'mpwem-display-section--seo',
                title: 'SEO & Schema',
                desc: 'Control rich result details used by search engines.',
                icon: 'dashicons-chart-bar'
            }
        ];

        sections.forEach(function(section) {
            const $mount = $root.find(section.mount).first();
            if (!$mount.length) return;

            $mount.addClass('mpwem-display-section ' + section.className);
            if (!$mount.children('.mpwem-display-section__head').length) {
                $mount.prepend(
                    $('<div class="mpwem-display-section__head"></div>')
                        .append($('<div class="mpwem-display-section__head-main"></div>')
                            .append($('<span class="mpwem-display-section__badge" aria-hidden="true"></span>').append($('<span class="dashicons"></span>').addClass(section.icon || 'dashicons-admin-generic')))
                            .append($('<h3></h3>').text(section.title))
                            .append($('<p></p>').text(section.desc)))
                );
            }

            if (section.className === 'mpwem-display-section--template' && $displayMainStack.length) {
                if (!$mount.parent().is($displayMainStack) || !$mount.is($displayMainStack.children().first())) {
                    $displayMainStack.prepend($mount);
                }
            }

            if (section.className === 'mpwem-display-section--seo' || section.className === 'mpwem-display-section--email') {
                const $head = $mount.children('.mpwem-display-section__head').first();
                let $body = $mount.children('.mpwem-display-section__body').first();

                if (!$body.length) {
                    const $bodyChildren = $mount.children().not('.mpwem-display-section__head');
                    $body = $('<div class="mpwem-display-section__body"></div>');
                    $body.append($bodyChildren);
                    $mount.append($body);
                }

                if ($head.length && !$head.find('.mpwem-display-toggle-wrap').length) {
                    $head.append(
                        $('<label class="mpwem-switch-wrap mpwem-display-toggle-wrap" aria-label="Toggle section"></label>')
                            .append('<input type="checkbox" class="mpwem-switch-input mpwem-display-toggle" />')
                            .append('<span class="mpwem-switch-slider"></span>')
                    );
                }

                const $toggle = $head.find('.mpwem-display-toggle').first();
                const syncSimpleToggle = function(isExpanded, useAnimation) {
                    $mount.toggleClass('is-collapsed', !isExpanded);
                    $mount.toggleClass('is-expanded', isExpanded);
                    $toggle.prop('checked', isExpanded).attr('aria-expanded', isExpanded ? 'true' : 'false');

                    if (useAnimation) {
                        $body.stop(true, true)[isExpanded ? 'slideDown' : 'slideUp'](220);
                    } else {
                        $body.toggle(isExpanded);
                    }
                };

                $toggle.off('change.mpwemSectionToggle').on('change.mpwemSectionToggle', function() {
                    syncSimpleToggle($(this).is(':checked'), true);
                });

                syncSimpleToggle(false, false);
            }

            if (section.className === 'mpwem-display-section--faq') {
                const $head = $mount.children('.mpwem-display-section__head').first();
                const $sourceRow = $mount.find('> .mp_tab_item > ._layout_default_xs_mp_zero > ._padding_bt').first();
                const $switch = $sourceRow.find('.round_switch_label').first();
                const $checkbox = $switch.find('input[type="checkbox"]').first();
                const $legacySlider = $switch.find('.round_switch').first();

                if ($head.length && $switch.length && !$head.find('.round_switch_label').length) {
                    $head.append($switch);
                    $sourceRow.addClass('mpwem-faq-toggle-row');
                }

                if ($checkbox.length) {
                    const target = $checkbox.attr('data-collapse-target') || $legacySlider.attr('data-collapse-target') || '#mep_faq_status';
                    const $collapse = $mount.find('[data-collapse="' + target + '"]').first();

                    $checkbox.attr('data-collapse-target', target);
                    if (!$checkbox.attr('data-toggle-values')) {
                        $checkbox.attr('data-toggle-values', 'on,off');
                    }

                    const syncFaqToggle = function(useAnimation) {
                        const isChecked = $checkbox.is(':checked');
                        $checkbox.val(isChecked ? 'on' : 'off');
                        $mount.toggleClass('is-collapsed', !isChecked);
                        $mount.toggleClass('is-expanded', isChecked);

                        if (!$collapse.length) return;
                        if (useAnimation) {
                            $collapse.stop(true, true)[isChecked ? 'slideDown' : 'slideUp'](250);
                        } else {
                            $collapse.toggle(isChecked);
                        }
                        $collapse.toggleClass('mActive', isChecked);
                    };

                    $checkbox.off('change.mpwemFaqToggle').on('change.mpwemFaqToggle', function() {
                        syncFaqToggle(true);
                    });

                    syncFaqToggle(false);
                }
            }

            if (section.className === 'mpwem-display-section--timeline') {
                const $head = $mount.children('.mpwem-display-section__head').first();
                const $sourceRow = $mount.find('> .mp_tab_item > ._layout_default_xs_mp_zero > ._padding_bt').first();
                const $switch = $sourceRow.find('.round_switch_label').first();
                const $checkbox = $switch.find('input[type="checkbox"]').first();
                const $legacySlider = $switch.find('.round_switch').first();

                if ($head.length && $switch.length && !$head.find('.round_switch_label').length) {
                    $head.append($switch);
                    $sourceRow.addClass('mpwem-timeline-toggle-row');
                }

                if ($checkbox.length) {
                    const target = $checkbox.attr('data-collapse-target') || $legacySlider.attr('data-collapse-target') || '#mep_timeline_status';
                    const $collapse = $mount.find('[data-collapse="' + target + '"]').first();

                    $checkbox.attr('data-collapse-target', target);
                    if (!$checkbox.attr('data-toggle-values')) {
                        $checkbox.attr('data-toggle-values', 'on,off');
                    }

                    const syncTimelineToggle = function(useAnimation) {
                        const isChecked = $checkbox.is(':checked');
                        $checkbox.val(isChecked ? 'on' : 'off');
                        $mount.toggleClass('is-collapsed', !isChecked);
                        $mount.toggleClass('is-expanded', isChecked);

                        if (!$collapse.length) return;
                        if (useAnimation) {
                            $collapse.stop(true, true)[isChecked ? 'slideDown' : 'slideUp'](250);
                        } else {
                            $collapse.toggle(isChecked);
                        }
                        $collapse.toggleClass('mActive', isChecked);
                    };

                    $checkbox.off('change.mpwemTimelineToggle').on('change.mpwemTimelineToggle', function() {
                        syncTimelineToggle(true);
                    });

                    syncTimelineToggle(false);
                }
            }

            if (section.className === 'mpwem-display-section--related') {
                const $head = $mount.children('.mpwem-display-section__head').first();
                const $bodySwitch = $mount.find('> .mp_tab_item > section .mpev-switch').first();
                const $toggleSection = $bodySwitch.closest('section');
                const $checkbox = $bodySwitch.find('input[type="checkbox"]').first();
                let $body = $mount.children('.mpwem-display-section__body').first();

                if (!$body.length) {
                    const $bodyChildren = $mount.children().not('.mpwem-display-section__head');
                    $body = $('<div class="mpwem-display-section__body"></div>');
                    $body.append($bodyChildren);
                    $mount.append($body);
                }

                if ($head.length && $bodySwitch.length && !$head.find('.mpev-switch, .mpwem-switch-wrap').length) {
                    $head.append($bodySwitch);
                }

                if ($toggleSection.length) {
                    $toggleSection.addClass('mpwem-related-toggle-row');
                }

                if ($checkbox.length) {
                    const target = $checkbox.attr('data-collapse-target') || '#mpev-related-event-display';
                    const $collapse = $mount.find(target).first();

                    $checkbox.attr('data-collapse-target', target);
                    if (!$checkbox.attr('data-toggle-values')) {
                        $checkbox.attr('data-toggle-values', 'on,off');
                    }

                    const syncRelatedToggle = function(useAnimation) {
                        const isChecked = $checkbox.is(':checked');
                        $checkbox.val(isChecked ? 'on' : 'off');
                        $mount.toggleClass('is-collapsed', !isChecked);
                        $mount.toggleClass('is-expanded', isChecked);

                        if (!$collapse.length) return;
                        if (useAnimation) {
                            $collapse.stop(true, true)[isChecked ? 'slideDown' : 'slideUp'](250);
                        } else {
                            $collapse.toggle(isChecked);
                        }
                    };

                    $checkbox.off('change.mpwemRelatedToggle').on('change.mpwemRelatedToggle', function() {
                        syncRelatedToggle(true);
                    });

                    syncRelatedToggle(false);
                }
            }

            if (section.className === 'mpwem-display-section--template') {
                $mount.addClass('is-expanded');
            }
        });

        $root.find('#mpwem_wizard_display .mpwem-card--help-display .mpwem-card__body').each(function() {
            const $body = $(this);
            if ($body.find('.mpwem-display-guide').length) return;

            $body.append(
                $('<div class="mpwem-display-guide"></div>')
                    .append($('<span></span>').append($('<strong></strong>').text('Template')).append($('<small></small>').text('Choose the event page design first. This controls the visitor-facing layout.')))
                    .append($('<span></span>').append($('<strong></strong>').text('FAQs')).append($('<small></small>').text('Enable this when buyers may ask common questions before booking.')))
                    .append($('<span></span>').append($('<strong></strong>').text('Timeline')).append($('<small></small>').text('Use this for agenda items, session flow, or key moments during the event.')))
                    .append($('<span></span>').append($('<strong></strong>').text('Related Events')).append($('<small></small>').text('Show similar or upcoming events when you want to cross-promote more listings.')))
                    .append($('<span></span>').append($('<strong></strong>').text('Email Message')).append($('<small></small>').text('Edit the attendee confirmation message and insert dynamic tags where needed.')))
                    .append($('<span></span>').append($('<strong></strong>').text('SEO & Schema')).append($('<small></small>').text('Complete this only when search result presentation and structured data matter.')))
            );
        });
    }

    function mountDangerZone($root) {
        const $mount = $root.find('#mpwem_wizard_danger_mount').first();
        if (!$mount.length || $mount.children().length) return;

        const $sourceRow = $root.find('[data-tab-item="#mpwem_event_settings"] ._padding_bt').filter(function() {
            return $(this).find('.mpwem_reset_booking').length > 0;
        }).first();

        if (!$sourceRow.length) {
            $root.find('#mpwem_advanced_danger_zone').hide();
            return;
        }

        const helpText = $.trim($sourceRow.find('.label-text').first().text()) || 'This action is irreversible.';
        const buttonText = $.trim($sourceRow.find('.mpwem_reset_booking').first().text()) || 'Reset Booking';

        $sourceRow.remove();

        const $danger = $('<div class="mpwem-danger-zone"></div>');
        $danger.append(
            $('<div class="mpwem-danger-zone__intro"></div>')
                .append($('<span class="mpwem-danger-zone__eyebrow"></span>').text('Reset booking count'))
                .append($('<p></p>').text(helpText))
        );
        $danger.append(
            $('<button type="button" class="button mpwem-btn-danger mpwem-danger-reset-trigger"></button>')
                .text(buttonText)
        );

        $mount.append($danger);
    }

    function enhanceVenueGrid($root) {
        const $venue = getPanel($root, '#mp_event_venue');
        if (!$venue.length || $venue.data('mpwemGridEnhanced')) return;

        const $addressSection = $venue.find('.mp_event_address').first();
        if (!$addressSection.length) return;

        const $table = $addressSection.find('table').first();
        if (!$table.length) return;

        const $grid = $('<div class="mpwem-venue-grid"></div>');
        $table.find('tr').each(function() {
            const $ths = $(this).find('th');
            const $tds = $(this).find('td');
            $ths.each(function(i) {
                const label = $(this).text().trim().replace(':', '');
                const $td = $tds.eq(i);
                if (!label && !$td.length) return;
                
                const $field = $('<div class="mpwem-venue-field"></div>');
                if (label) $field.append('<label class="mpwem-venue-label">' + label + '</label>');
                $field.append($td.contents());
                
                const $input = $field.find('input, select').first();
                if ($input.attr('name') === 'mep_location_venue' || $input.attr('name') === 'mep_country') {
                    $field.addClass('is-full');
                }
                $grid.append($field);
            });
        });
        
        $table.replaceWith($grid);
        $venue.data('mpwemGridEnhanced', true);
    }

    function enhanceEventType($root) {
        const $venue = getPanel($root, '#mp_event_venue');
        if (!$venue.length) return;

        const $checkbox = $venue.find('input[name="mep_event_type"]').first();
        if (!$checkbox.length || $checkbox.data('mpwemEnhanced')) return;

        // The checkbox is in a section, let's find the container section
        const $origSection = $checkbox.closest('section');
        if (!$origSection.length) return;

        const $wrapper = $('<div class="mpwem-event-type-card"></div>');
        const $toggle = $(
            '<div class="mpwem-event-type-toggle">' +
            '  <div class="mpwem-event-type-option" data-value="offline">' +
            '    <span class="dashicons dashicons-location"></span>' +
            '    <div><strong>In-Person</strong><small>Physical location</small></div>' +
            '  </div>' +
            '  <div class="mpwem-event-type-option" data-value="online">' +
            '    <span class="dashicons dashicons-admin-site-alt3"></span>' +
            '    <div><strong>Online Event</strong><small>Virtual/Remote</small></div>' +
            '  </div>' +
            '</div>'
        );

        $wrapper.append($toggle);
        $origSection.before($wrapper);
        $origSection.hide(); // Hide the original section entirely

        function updateUI(val) {
            const isOnline = val === 'online';
            $checkbox.prop('checked', isOnline).val(isOnline ? 'online' : '');
            $toggle.find('.mpwem-event-type-option').removeClass('is-active');
            $toggle.find('[data-value="' + (isOnline ? 'online' : 'offline') + '"]').addClass('is-active');
            
            // Trigger legacy collapses
            const target = $checkbox.data('collapse-target');
            const close = $checkbox.data('close-target');
            if (isOnline) {
                if (close) $(close).hide();
                if (target) $(target).show();
            } else {
                if (target) $(target).hide();
                if (close) $(close).show();
            }
        }

        const initialVal = $checkbox.is(':checked') || $checkbox.val() === 'online' ? 'online' : 'offline';
        updateUI(initialVal);

        $toggle.on('click', '.mpwem-event-type-option', function() {
            updateUI($(this).data('value'));
        });

        $checkbox.data('mpwemEnhanced', true);
    }

    function enhanceSwitches($container) {
        $container.find('input[type="checkbox"]').each(function() {
            const $cb = $(this);
            if ($cb.data('mpwemSwitch') || $cb.closest('.mpwem-event-type-toggle').length) return;
            // Rescue data attributes from legacy sliders before destroying them
            const $legacySliders = $cb.siblings('.mpev-slider, .mp_slider, .mpev-slider-round, .round_switch');
            if ($legacySliders.length) {
                const target = $legacySliders.attr('data-collapse-target');
                if (target && !$cb.attr('data-collapse-target')) {
                    $cb.attr('data-collapse-target', target);
                }
                $legacySliders.remove();
            }
            
            if ($cb.parent().hasClass('lcs_wrap')) {
                $cb.siblings('.lcs_switch').remove();
                $cb.unwrap();
            }

            let $label = $cb.closest('label');
            if (!$label.length) {
                $cb.wrap('<label class="mpwem-switch-wrap"></label>');
                $label = $cb.parent();
            } else {
                $label.addClass('mpwem-switch-wrap');
                if ($label.hasClass('mpev-switch')) $label.removeClass('mpev-switch');
            }

            $cb.addClass('mpwem-switch-input');
            if (!$cb.next('.mpwem-switch-slider').length) {
                $cb.after('<span class="mpwem-switch-slider"></span>');
            }

            // Wrap naked text nodes in the label
            $label.contents().each(function() {
                if (this.nodeType === 3 && $.trim(this.nodeValue).length > 0) {
                    $(this).wrap('<span class="mpwem-switch-text"></span>');
                }
            });

            $cb.data('mpwemSwitch', true);
        });

        // Global delegate for switch behavior (only once)
        if (!window.mpwemSwitchesInitialized) {
            $(document).on('change', '.mpwem-switch-input', function() {
                const $cb = $(this);
                const isChecked = $cb.is(':checked');
                
                const target = $cb.data('collapse-target');
                const close = $cb.data('close-target');
                const toggleValues = $cb.data('toggle-values') ? $cb.data('toggle-values').toString().split(',') : null;

                // Sync value if toggle values exist
                if (toggleValues && toggleValues.length === 2) {
                    $cb.val(isChecked ? toggleValues[0] : toggleValues[1]);
                }

                // Handle sliding sections
                if (isChecked) {
                    if (target) $(target).slideDown(250);
                    if (close) $(close).slideUp(250);
                } else {
                    if (target) $(target).slideUp(250);
                    if (close) $(close).slideDown(250);
                }

                // Legacy specific: ticket time toggle
                if ($cb.attr('name') === 'mep_disable_ticket_time') {
                    if (isChecked) {
                        $(".mep-special-datetime").slideUp(200);
                    } else {
                        $(".mep-special-datetime").slideDown(200);
                    }
                }
            });
            window.mpwemSwitchesInitialized = true;
        }
    }

    function normalizeHiddenSelectTemplates($container) {
        $container.find('.mpwem_hidden_content .mpwem-select-wrapper').each(function() {
            const $wrapper = $(this);
            const $select = $wrapper.children('select').first();
            if (!$select.length) {
                return;
            }

            $select.removeClass('mpwem-enhanced').show();
            $wrapper.replaceWith($select);
        });
    }

    function normalizeNativeModalSelects($container) {
        $container.find('#mpwem_ticket_modal_mount select[name="option_qty_t_type[]"], #mpwem_extra_service_modal_mount select[name="option_qty_type[]"]').each(function() {
            const $select = $(this);
            const $wrapper = $select.closest('.mpwem-select-wrapper');

            if ($wrapper.length) {
                $select.removeClass('mpwem-enhanced').show();
                $wrapper.replaceWith($select);
            }
        });
    }

    function enhanceSelects($container) {
        normalizeHiddenSelectTemplates($container);
        normalizeNativeModalSelects($container);

        $container.find('select').each(function() {
            const $select = $(this);
            if (
                $select.is('#mpwem_ticket_modal_mount select[name="option_qty_t_type[]"], #mpwem_extra_service_modal_mount select[name="option_qty_type[]"]') ||
                $select.closest('.mpwem_hidden_content').length ||
                $select.hasClass('mpwem-enhanced') ||
                $select.is('[multiple]') ||
                $select.closest('.mpwem-select-wrapper').length
            ) return;

            const $wrapper = $('<div class="mpwem-select-wrapper"></div>');
            const $trigger = $('<div class="mpwem-select-trigger"><span>' + ($select.find('option:selected').text() || 'Select...') + '</span></div>');
            const $options = $('<div class="mpwem-select-options"></div>');

            $select.find('option').each(function() {
                const $opt = $(this);
                const $customOpt = $('<div class="mpwem-select-option" data-value="' + $opt.val() + '">' + $opt.text() + '</div>');
                if ($opt.is(':selected')) $customOpt.addClass('is-selected');
                $options.append($customOpt);
            });

            $select.hide().addClass('mpwem-enhanced').after($wrapper);
            $wrapper.append($select).append($trigger).append($options);

            $trigger.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const isOpen = $wrapper.hasClass('is-open');
                $('.mpwem-select-wrapper').removeClass('is-open');
                if (!isOpen) $wrapper.addClass('is-open');
            });

            $options.on('click', '.mpwem-select-option', function() {
                const val = $(this).data('value');
                const text = $(this).text();
                
                $select.val(val).trigger('change');
                $trigger.find('span').text(text);
                $options.find('.mpwem-select-option').removeClass('is-selected');
                $(this).addClass('is-selected');
                $wrapper.removeClass('is-open');
            });

            $select.on('change', function() {
                const text = $select.find('option:selected').text();
                $trigger.find('span').text(text);
                $options.find('.mpwem-select-option').removeClass('is-selected');
                $options.find('.mpwem-select-option[data-value="' + $select.val() + '"]').addClass('is-selected');
            });
        });
    }

    function enhanceTooltips($container) {
        $container.find('.label-text, small, .mep_help_text').each(function() {
            const $help = $(this);
            if ($help.data('mpwemTooltip') || $help.closest('.mpwem-venue-field').length) return;
            
            const text = $help.text().trim();
            if (text.length < 5) return;

            // Prioritize our modern field labels
            let $target = $help.parent().find('.mpwem-field__label').first();
            
            if (!$target.length) {
                $target = $help.parent().find('h2, h3, h4, span, label').first();
            }
            
            if ($target.length) {
                const $tip = $('<span class="mpwem-info-tip" title="' + text.replace(/"/g, '&quot;') + '">i</span>');
                
                // If the target is a label, try to append to its text container (span/strong) first
                if ($target.is('label')) {
                    const $innerLabel = $target.find('.mpwem-field__label, span, strong').first();
                    if ($innerLabel.length) {
                        $innerLabel.append($tip);
                    } else if ($target.find('input, select, textarea, code').length) {
                        // If it has inputs/code but no inner span, prepend to keep it at the top
                        $target.prepend($tip);
                    } else {
                        $target.append($tip);
                    }
                } else {
                    $target.append($tip);
                }

                $help.hide();
                $help.data('mpwemTooltip', true);
            }
        });
    }

    function getDatePanel($root) {
        return $root.find('#mpwem_wizard_date_mount .mpwem_date_settings').first();
    }

    function syncDateTypeSections($panel, value) {
        const map = {
            no: '#mep_normal_event',
            yes: '#mep_particular_event',
            everyday: '#mep_everyday_event'
        };

        $.each(map, function(type, selector) {
            const $section = $panel.find('[data-collapse="' + selector + '"]').first();
            if (!$section.length) return;

            if (type === value) {
                $section.addClass('mActive mpwem-date-mode-active').show();
            } else {
                $section.removeClass('mActive mpwem-date-mode-active').hide();
            }
        });

        $panel.find('.mpwem-date-type-option').removeClass('is-active');
        $panel.find('.mpwem-date-type-option').attr('aria-checked', 'false');
        $panel.find('.mpwem-date-type-option[data-value="' + value + '"]').addClass('is-active').attr('aria-checked', 'true');
    }

    function enhanceDateTypeSelector($panel) {
        const $select = $panel.find('select[name="mep_enable_recurring"]').first();
        if (!$select.length || $select.data('mpwemDateTypeEnhanced')) return;

        const details = {
            no: {
                title: 'Single Event',
                desc: 'One main start and end date.'
            },
            yes: {
                title: 'Particular Dates',
                desc: 'Specific dates with individual schedules.'
            },
            everyday: {
                title: 'Repeated Event',
                desc: 'Runs across a date range with repeat rules.'
            }
        };

        const $selector = $('<div class="mpwem-date-type-selector" role="radiogroup" aria-label="Event date type"></div>');
        $select.find('option[value]').each(function() {
            const $option = $(this);
            const value = $option.val();
            const meta = details[value] || {
                title: $option.text(),
                desc: ''
            };

            const $button = $('<button type="button" class="mpwem-date-type-option" role="radio"></button>');
            $button.attr('data-value', value);
            $button.append($('<span class="mpwem-date-type-option__icon dashicons"></span>'));
            $button.append(
                $('<span class="mpwem-date-type-option__copy"></span>')
                    .append($('<strong></strong>').text(meta.title))
                    .append($('<small></small>').text(meta.desc))
            );
            $selector.append($button);
        });

        const $field = $select.closest('._padding_bt');
        if ($field.length) {
            $field.before($selector);
            $field.addClass('mpwem-date-type-native-field').hide();
        } else {
            $select.before($selector);
            $select.hide();
        }

        $selector.on('click', '.mpwem-date-type-option', function() {
            const value = $(this).data('value');
            $select.val(value).trigger('change');
            syncDateTypeSections($panel, value);
        });

        $select.on('change', function() {
            syncDateTypeSections($panel, $(this).val());
        });

        $select.data('mpwemDateTypeEnhanced', true);
        syncDateTypeSections($panel, $select.val() || 'no');
    }

    function enhanceDateFields($panel) {
        $panel.find('label').each(function() {
            const $label = $(this);
            const $dateInput = $label.find('input.formControl:not([type]), input[type="text"].formControl, input[type="text"].date_type, input[type="text"].new-date_type, input[type="text"].new-date_type-new, input[type="text"].new-particular-date_type').first();
            const hasHiddenDate = $label.find('input[type="hidden"]').filter(function() {
                const name = $(this).attr('name') || '';
                return /(^|_)event_.*date|mep_ticket_off_dates|mep_special_(start|end)_date/.test(name);
            }).length > 0;
            if ($dateInput.length && hasHiddenDate) {
                $label.addClass('mpwem-date-input-wrap');
                $dateInput.addClass('mpwem-date-input');
            }

            const $timeInput = $label.find('input[type="time"]').first();
            if ($timeInput.length) {
                $label.addClass('mpwem-time-input-wrap');
                $timeInput.addClass('mpwem-time-input');
            }
        });
    }

    function parseIsoDate(value) {
        if (!value || !/^\d{4}-\d{2}-\d{2}$/.test(value)) return null;

        const parts = value.split('-').map(function(part) {
            return parseInt(part, 10);
        });
        const date = new Date(parts[0], parts[1] - 1, parts[2]);

        if (date.getFullYear() !== parts[0] || date.getMonth() !== parts[1] - 1 || date.getDate() !== parts[2]) {
            return null;
        }

        return date;
    }

    function formatIsoDate(date) {
        return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
    }

    function formatVisibleDate(date) {
        const format = window.mpwem_date_format || 'yy-mm-dd';
        if ($.datepicker && $.datepicker.formatDate) {
            return $.datepicker.formatDate(format, date);
        }
        return formatIsoDate(date);
    }

    function normalizeMinDate(minDate) {
        if (!minDate) return null;
        if (minDate instanceof Date) {
            return new Date(minDate.getFullYear(), minDate.getMonth(), minDate.getDate());
        }
        if (typeof minDate === 'string') {
            return parseIsoDate(minDate);
        }
        return null;
    }

    function ensureCustomCalendar() {
        let $calendar = $('#mpwem_custom_calendar');
        if ($calendar.length) return $calendar;

        $calendar = $(
            '<div id="mpwem_custom_calendar" class="mpwem-custom-calendar" role="dialog" aria-modal="false" aria-label="Choose date">' +
            '  <div class="mpwem-custom-calendar__head">' +
            '    <button type="button" class="mpwem-custom-calendar__nav" data-calendar-nav="-1" aria-label="Previous month"><span class="dashicons dashicons-arrow-left-alt2"></span></button>' +
            '    <div class="mpwem-custom-calendar__title">' +
            '      <select class="mpwem-custom-calendar__month" aria-label="Month"></select>' +
            '      <input class="mpwem-custom-calendar__year" type="number" aria-label="Year" min="1900" max="2100" />' +
            '    </div>' +
            '    <button type="button" class="mpwem-custom-calendar__nav" data-calendar-nav="1" aria-label="Next month"><span class="dashicons dashicons-arrow-right-alt2"></span></button>' +
            '  </div>' +
            '  <div class="mpwem-custom-calendar__week"></div>' +
            '  <div class="mpwem-custom-calendar__days"></div>' +
            '  <div class="mpwem-custom-calendar__foot">' +
            '    <button type="button" class="mpwem-custom-calendar__today">Today</button>' +
            '    <button type="button" class="mpwem-custom-calendar__clear">Clear</button>' +
            '  </div>' +
            '</div>'
        );

        const region = $.datepicker && $.datepicker.regional ? $.datepicker.regional[''] : null;
        const monthNames = region && region.monthNames ? region.monthNames : ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        const dayNames = region && region.dayNamesMin ? region.dayNamesMin : ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

        monthNames.forEach(function(name, index) {
            $calendar.find('.mpwem-custom-calendar__month').append($('<option></option>').val(index).text(name));
        });
        dayNames.forEach(function(name) {
            $calendar.find('.mpwem-custom-calendar__week').append($('<span></span>').text(name));
        });

        $('body').append($calendar);
        return $calendar;
    }

    function positionCustomCalendar($calendar, $input) {
        const offset = $input.offset();
        const width = $calendar.outerWidth();
        const viewportRight = $(window).scrollLeft() + $(window).width();
        let left = offset.left;

        if (left + width > viewportRight - 12) {
            left = Math.max(12, viewportRight - width - 12);
        }

        $calendar.css({
            top: offset.top + $input.outerHeight() + 8,
            left: left
        });
    }

    function renderCustomCalendar($calendar) {
        const state = $calendar.data('mpwemState');
        if (!state || !state.$input) return;

        const view = new Date(state.viewDate.getFullYear(), state.viewDate.getMonth(), 1);
        const selected = parseIsoDate(state.$input.closest('label').find('input[type="hidden"]').first().val());
        const selectedIso = selected ? formatIsoDate(selected) : '';
        const todayIso = formatIsoDate(new Date());
        const daysInMonth = new Date(view.getFullYear(), view.getMonth() + 1, 0).getDate();
        const firstDay = view.getDay();
        const $days = $calendar.find('.mpwem-custom-calendar__days').empty();

        $calendar.find('.mpwem-custom-calendar__month').val(view.getMonth());
        $calendar.find('.mpwem-custom-calendar__year').val(view.getFullYear());

        for (let i = 0; i < firstDay; i++) {
            $days.append('<span class="mpwem-custom-calendar__empty"></span>');
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(view.getFullYear(), view.getMonth(), day);
            const iso = formatIsoDate(date);
            const $button = $('<button type="button" class="mpwem-custom-calendar__day"></button>').text(day).attr('data-date', iso);

            if (state.minDate && date < state.minDate) $button.prop('disabled', true).addClass('is-disabled');
            if (iso === todayIso) $button.addClass('is-today');
            if (iso === selectedIso) $button.addClass('is-selected');

            $days.append($button);
        }
    }

    function openCustomCalendar($input) {
        const $calendar = ensureCustomCalendar();
        const $hidden = $input.closest('label').find('input[type="hidden"]').first();
        const selected = parseIsoDate($hidden.val());
        const now = new Date();
        let minDate = $input.data('mpwemMinDate') || null;

        const viewDate = selected || minDate || now;
        $calendar.data('mpwemState', {
            $input: $input,
            minDate: minDate,
            viewDate: new Date(viewDate.getFullYear(), viewDate.getMonth(), 1)
        });

        renderCustomCalendar($calendar);
        positionCustomCalendar($calendar, $input);
        if ($.datepicker) $.datepicker._hideDatepicker();
        $calendar.addClass('is-open');
    }

    function closeCustomCalendar() {
        $('#mpwem_custom_calendar').removeClass('is-open');
    }

    function selectCustomCalendarDate($calendar, date) {
        const state = $calendar.data('mpwemState');
        if (!state || !state.$input) return;

        state.$input.closest('label').find('input[type="hidden"]').first().val(formatIsoDate(date)).trigger('change');
        state.$input.val(formatVisibleDate(date)).trigger('change');
        closeCustomCalendar();
    }

    function bindCustomCalendar() {
        if (window.mpwemCustomCalendarBound) return;

        $(document).on('focus click', '.mpwem-event-wizard .mpwem-date-input', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openCustomCalendar($(this));
        });

        $(document).on('click', '.mpwem-custom-calendar__day', function(e) {
            e.preventDefault();
            const date = parseIsoDate($(this).data('date'));
            if (date) selectCustomCalendarDate($('#mpwem_custom_calendar'), date);
        });

        $(document).on('click', '.mpwem-custom-calendar__nav', function(e) {
            e.preventDefault();
            const $calendar = $('#mpwem_custom_calendar');
            const state = $calendar.data('mpwemState');
            if (!state) return;

            state.viewDate = new Date(state.viewDate.getFullYear(), state.viewDate.getMonth() + (parseInt($(this).data('calendar-nav'), 10) || 0), 1);
            $calendar.data('mpwemState', state);
            renderCustomCalendar($calendar);
        });

        $(document).on('change', '.mpwem-custom-calendar__month, .mpwem-custom-calendar__year', function() {
            const $calendar = $('#mpwem_custom_calendar');
            const state = $calendar.data('mpwemState');
            if (!state) return;

            const month = parseInt($calendar.find('.mpwem-custom-calendar__month').val(), 10);
            const year = parseInt($calendar.find('.mpwem-custom-calendar__year').val(), 10);
            if (!Number.isNaN(month) && !Number.isNaN(year)) {
                state.viewDate = new Date(year, month, 1);
                $calendar.data('mpwemState', state);
                renderCustomCalendar($calendar);
            }
        });

        $(document).on('click', '.mpwem-custom-calendar__today', function(e) {
            e.preventDefault();
            const $calendar = $('#mpwem_custom_calendar');
            const state = $calendar.data('mpwemState');
            const today = new Date();
            const cleanToday = new Date(today.getFullYear(), today.getMonth(), today.getDate());

            if (state && state.minDate && cleanToday < state.minDate) {
                state.viewDate = new Date(state.minDate.getFullYear(), state.minDate.getMonth(), 1);
                $calendar.data('mpwemState', state);
                renderCustomCalendar($calendar);
                return;
            }

            selectCustomCalendarDate($calendar, cleanToday);
        });

        $(document).on('click', '.mpwem-custom-calendar__clear', function(e) {
            e.preventDefault();
            const $calendar = $('#mpwem_custom_calendar');
            const state = $calendar.data('mpwemState');
            if (!state || !state.$input) return;

            state.$input.closest('label').find('input[type="hidden"]').first().val('').trigger('change');
            state.$input.val('').trigger('change');
            closeCustomCalendar();
        });

        $(document).on('click', '.mpwem-event-wizard .mpwem-date-input-wrap .mpwem_date_reset', function(e) {
            e.preventDefault();
            const $label = $(this).closest('label');
            $label.find('input[type="hidden"]').first().val('').trigger('change');
            $label.find('.mpwem-date-input').first().val('').trigger('change');
            closeCustomCalendar();
        });

        $(document).on('mousedown', function(e) {
            const $target = $(e.target);
            if ($target.closest('.mpwem-custom-calendar, .mpwem-date-input-wrap').length) return;
            closeCustomCalendar();
        });

        $(window).on('resize scroll', function() {
            const $calendar = $('#mpwem_custom_calendar.is-open');
            const state = $calendar.data('mpwemState');
            if ($calendar.length && state && state.$input) {
                positionCustomCalendar($calendar, state.$input);
            }
        });

        window.mpwemCustomCalendarBound = true;
    }

    function enhanceCustomCalendar($panel) {
        bindCustomCalendar();
        $panel.find('.mpwem-date-input').each(function() {
            const $input = $(this);

            if ($.datepicker && $input.hasClass('hasDatepicker')) {
                try {
                    $input.data('mpwemMinDate', normalizeMinDate($input.datepicker('option', 'minDate')));
                    $input.datepicker('destroy');
                } catch (error) {
                    // Leave the input usable if a legacy datepicker instance is incomplete.
                }
            }

            $input.attr('autocomplete', 'off');
            $input.data('mpwemCustomCalendar', true);
        });
    }

    function decorateDateSections($panel) {
        $panel.addClass('mpwem-date-step');
        $panel.children('._layout_default_xs_mp_zero, ._layout_default, [data-collapse="#mep_normal_event"], [data-collapse="#mep_particular_event"], [data-collapse="#mep_everyday_event"]').addClass('mpwem-date-card');
        $panel.find('._bg_light_padding').addClass('mpwem-date-card__head');
        $panel.find('.mpwem_settings_area').addClass('mpwem-date-repeat-area');
        $panel.find('table').not('.ui-datepicker-calendar').addClass('mpwem-date-table');
        $panel.find('table.mpwem_time_setting_table').addClass('mpwem-date-time-slots-table');
        $panel.find('.mep_special_on_dates_table').addClass('mpwem-date-special-table');
        $panel.find('input[name="mep_buffer_time"]').closest('._padding_bt').addClass('mpwem-date-buffer-field');
        $panel.find('input[name="mep_ticket_off_dates[]"]').closest('._padding_bt').addClass('mpwem-ticket-off-dates-field');

        const labels = {
            '#mep_normal_event': 'Single schedule',
            '#mep_particular_event': 'Specific date schedule',
            '#mep_everyday_event': 'Repeated schedule'
        };

        $.each(labels, function(selector, label) {
            const $section = $panel.find('[data-collapse="' + selector + '"]').first();
            if (!$section.length || $section.find('> .mpwem-date-mode-label').length) return;
            $section.prepend($('<div class="mpwem-date-mode-label"></div>').text(label));
        });
    }

    function enhanceOffDayPicker($panel) {
        $panel.find('.groupCheckBox').each(function() {
            const $group = $(this);
            if ($group.data('mpwemOffDaysEnhanced')) return;

            $group.addClass('mpwem-offday-grid');
            $group.find('.customCheckboxLabel').addClass('mpwem-offday-option');
            $group.find('.customCheckbox').addClass('mpwem-offday-chip');
            $group.data('mpwemOffDaysEnhanced', true);
        });
    }

    function syncDateOptionTargetSelect($panel, $select) {
        const targets = [];
        $select.find('option[data-option-target]').each(function() {
            const target = $(this).data('option-target');
            if (target && targets.indexOf(target) === -1) {
                targets.push(target);
            }
        });

        targets.forEach(function(target) {
            $panel.find('[data-collapse="' + target + '"]').removeClass('mActive').hide();
        });

        const selectedTarget = $select.find('option:selected').data('option-target');
        if (selectedTarget) {
            $panel.find('[data-collapse="' + selectedTarget + '"]').addClass('mActive').show();
        }
    }

    function enhanceDateConditionalSelects($panel) {
        $panel.find('select[data-collapse-target]').not('[name="mep_enable_recurring"]').each(function() {
            const $select = $(this);
            if ($select.data('mpwemDateConditionalEnhanced')) return;

            $select.on('change', function() {
                syncDateOptionTargetSelect($panel, $select);
            });
            $select.data('mpwemDateConditionalEnhanced', true);
            syncDateOptionTargetSelect($panel, $select);
        });
    }

    function alignDateFormatTooltips($panel) {
        $panel.find('.mpwem_date_format_settings label._justify_between_align_center_wrap').each(function() {
            const $label = $(this);
            const $labelText = $label.children('._mr').first();

            if (!$labelText.length) return;

            $label.children('.mpwem-info-tip').appendTo($labelText);
        });
    }

    function enhanceDateStep($root) {
        const $panel = getDatePanel($root);
        if (!$panel.length) return;

        decorateDateSections($panel);
        enhanceDateTypeSelector($panel);
        enhanceDateFields($panel);
        enhanceCustomCalendar($panel);
        enhanceOffDayPicker($panel);
        enhanceDateConditionalSelects($panel);
        alignDateFormatTooltips($panel);
    }

    function validateDateWiseGlobalQty($root, options) {
        const $datePanel = getDatePanel($root);
        const shouldFocusStep = !(options && options.focusStep === false);
        if (!$datePanel.length) {
            return true;
        }

        const isGlobalQtyEnabled = $root.find('input[name="enable_global_qty"]').first().is(':checked');
        const globalQtyType = ($root.find('select[name="mep_gq_type"]').first().val() || '').toString();

        if (!isGlobalQtyEnabled || globalQtyType !== 'date_wise') {
            return true;
        }

        const $requiredFields = $datePanel.find('[data-collapse="#mep_particular_event"] input[name="event_date_gq"], [data-collapse="#mep_particular_event"] input[name="event_date_gq_md[]"]').filter(function() {
            return $(this).closest('.mpwem_hidden_content').length === 0;
        });
        const recurringType = ($datePanel.find('select[name="mep_enable_recurring"]').first().val() || '').toString();

        if (recurringType !== 'yes') {
            $requiredFields.removeClass('mpwem-field-error');
            return true;
        }

        let hasError = false;
        let $firstInvalid = $();

        $requiredFields.each(function() {
            const $input = $(this);
            const value = ($input.val() || '').toString().trim();
            const isEmpty = value === '';

            $input.toggleClass('mpwem-field-error', isEmpty);

            if (isEmpty && !hasError) {
                hasError = true;
                $firstInvalid = $input;
            }
        });

        if (!hasError) {
            return true;
        }

        if (shouldFocusStep) {
            setActiveStep($root, 'date', { pushHash: true, validate: false });
        }

        if ($firstInvalid.length) {
            window.setTimeout(function() {
                const $scrollWrap = $firstInvalid.closest('._ov_auto');
                if ($scrollWrap.length) {
                    const left = $firstInvalid.position().left + $scrollWrap.scrollLeft() - 24;
                    $scrollWrap.animate({ scrollLeft: Math.max(left, 0) }, 220);
                }
                $firstInvalid.trigger('focus');
            }, 80);
        }

        showToast('Global Qty is required in Particular Date & Time Settings when Global Qty Type is set to Particular Date Wise.', 'error');
        return false;
    }

    function setActiveStep($root, stepKey, options) {
        if (stepKey !== 'tickets') {
            closeTicketModal($root);
        }

        const $steps = $root.find('.mpwem-step');
        const $targetStep = $steps.filter('[data-step-key="' + stepKey + '"]');
        if (!$targetStep.length) {
            if (stepKey === STEP_KEY_FALLBACK) return;
            return setActiveStep($root, STEP_KEY_FALLBACK, { pushHash: true });
        }

        const $activeStep = $steps.filter('.is-active');
        if (options && options.validate && $steps.index($targetStep) > $steps.index($activeStep)) {
            // Basic validation
            if (stepKey !== STEP_KEY_FALLBACK && !$('#title').val()) {
                alert('Please enter an event title.');
                return;
            }
            if (!validateDateWiseGlobalQty($root)) {
                return;
            }
        }

        $steps.removeClass('is-active');
        $targetStep.addClass('is-active');

        const panelSelector = $targetStep.data('panel');
        
        // Hide all top-level panels
        $root.find('.mpwem-wizard-panels > .mp_tab_item').removeClass('is-active').hide();
        
        // Show target panel
        const $panel = getPanel($root, panelSelector);
        if ($panel.length) {
            $panel.addClass('is-active').show();
            enhanceSwitches($panel);
            enhanceTooltips($panel);
            enhanceSelects($panel);
            if (stepKey === 'date') {
                enhanceDateStep($root);
            }
        }

        // Sidebar/Additional visibility
        if (stepKey === 'basic') {
            $('#mpwem_event_edit_sidebar').show();
            $('#mpwem_edit_page_additional').hide();
        } else {
            $('#mpwem_event_edit_sidebar').hide();
            $('#mpwem_edit_page_additional').show();
        }

        // Progress
        const current = $steps.index($targetStep) + 1;
        $root.find('.mpwem-wizard-progress').text('Step ' + current + ' of ' + $steps.length);
        $root.find('.mpwem-wizard-prev').prop('disabled', current === 1);
        $root.find('.mpwem-wizard-next').text(current === $steps.length ? 'Save Event' : 'Next Step');

        if (options && options.pushHash) {
            setHash(parseInt($root.data('event-id'), 10) > 0 ? 'edit' : 'new', $root.data('event-id'), stepKey);
        }
    }

    function bindCreateEvent($root) {
        $root.on('click', '.mpwem-wizard-create', function(e) {
            e.preventDefault();

            const config = getConfig();
            const $button = $(this);
            const originalText = $button.data('mpwemOriginalText') || $button.text();
            $button.data('mpwemOriginalText', originalText);
            if (!config.ajax_url || !config.create_nonce) {
                showNotice($root, 'Event creation is not configured.', 'error');
                return;
            }

            $button.prop('disabled', true).text('Creating...');
            showNotice($root, 'Creating event...', 'success');

            $.post(config.ajax_url, {
                action: 'mpwem_event_edit_create',
                nonce: config.create_nonce
            }).done(function(response) {
                if (response && response.success && response.data && response.data.event_id) {
                    const pageUrl = config.page_url || window.location.href.split('#')[0];
                    window.location.href = pageUrl + '&event_id=' + encodeURIComponent(response.data.event_id) + '#/events/edit/' + encodeURIComponent(response.data.event_id) + '/basic';
                    return;
                }

                const message = response && response.data && response.data.message ? response.data.message : 'Could not create event.';
                showNotice($root, message, 'error');
                $button.prop('disabled', false).text(originalText);
            }).fail(function(xhr) {
                const response = xhr.responseJSON || {};
                const message = response.data && response.data.message ? response.data.message : 'Could not create event.';
                showNotice($root, message, 'error');
                $button.prop('disabled', false).text(originalText);
            });
        });
    }

    function bindFeaturedImage($root) {
        let mediaFrame = null;

        $root.on('click', '.mpwem-featured-select', function(e) {
            e.preventDefault();

            if (!window.wp || !window.wp.media) {
                showNotice($root, 'The media library is not available.', 'error');
                return;
            }

            if (!mediaFrame) {
                mediaFrame = window.wp.media({
                    title: 'Select featured image',
                    button: {
                        text: 'Use this image'
                    },
                    multiple: false
                });

                mediaFrame.on('select', function() {
                    const attachment = mediaFrame.state().get('selection').first();
                    if (!attachment) return;

                    const data = attachment.toJSON();
                    const imageUrl = data.sizes && data.sizes.medium ? data.sizes.medium.url : data.url;
                    const $wrap = $root.find('.mpwem-featured-image').first();
                    const $image = $('<img />', {
                        src: imageUrl,
                        alt: ''
                    });

                    $root.find('#mpwem_featured_image_id').val(data.id || '');
                    $wrap.attr('data-has-image', data.id ? '1' : '0');
                    $wrap.find('.mpwem-featured-image__preview').empty().append($image);
                    $wrap.find('.mpwem-featured-remove').prop('disabled', false);
                });
            }

            mediaFrame.open();
        });

        $root.on('click', '.mpwem-featured-remove', function(e) {
            e.preventDefault();

            const $wrap = $root.find('.mpwem-featured-image').first();
            $root.find('#mpwem_featured_image_id').val('');
            $wrap.attr('data-has-image', '0');
            $wrap.find('.mpwem-featured-image__preview').empty().append(
                $('<div />', {
                    class: 'mpwem-featured-image__placeholder',
                    text: 'No image selected'
                })
            );
            $(this).prop('disabled', true);
        });
    }

    function bindDangerZone($root) {
        const $modal = $root.find('#mpwem_reset_booking_modal').first();
        if (!$modal.length) return;

        const closeModal = function() {
            $modal.attr('aria-hidden', 'true').removeClass('is-open');
        };

        const openModal = function() {
            $modal.attr('aria-hidden', 'false').addClass('is-open');
        };

        $root.on('click', '.mpwem-danger-reset-trigger', function(e) {
            e.preventDefault();
            openModal();
        });

        $modal.on('click', '.mpwem-confirm-modal__backdrop, .mpwem-confirm-modal__close, .mpwem-confirm-cancel', function(e) {
            e.preventDefault();
            closeModal();
        });

        $(document).on('keydown.mpwemDangerZone', function(e) {
            if (e.key === 'Escape' && $modal.hasClass('is-open')) {
                closeModal();
            }
        });

        $modal.on('click', '.mpwem-confirm-reset', function(e) {
            e.preventDefault();

            const config = getConfig();
            const postId = $root.find('[name="post_ID"]').val();
            const $button = $(this);
            const originalText = $button.data('mpwemOriginalText') || $button.text();
            $button.data('mpwemOriginalText', originalText);

            if (!config.ajax_url || !config.admin_nonce || !postId) {
                showNotice($root, 'Reset booking is not configured for this event.', 'error');
                closeModal();
                return;
            }

            $button.prop('disabled', true).text('Resetting...');

            $.post(config.ajax_url, {
                action: 'mpwem_reset_booking',
                post_id: postId,
                nonce: config.admin_nonce
            }).done(function(response) {
                const message = typeof response === 'string' && response.trim().length ? response.trim() : 'Booking reset completed.';
                showNotice($root, message, 'success');
                closeModal();
            }).fail(function(xhr) {
                const response = xhr.responseJSON || {};
                const message = response.data && response.data.message ? response.data.message : 'Booking reset unsuccessful.';
                showNotice($root, message, 'error');
            }).always(function() {
                $button.prop('disabled', false).text(originalText);
            });
        });
    }

    $(function () {
        const $root = getWizardRoot();
        if (!$root.length) return;

        const markReady = function() {
            $root.removeClass('is-loading').addClass('is-ready');
        };

        window.setTimeout(markReady, 2500);
        bindCreateEvent($root);
        bindFeaturedImage($root);
        bindDangerZone($root);

        try {
            mountAll($root);
            enhanceVenueGrid($root);
            enhanceEventType($root);
            enhanceSwitches($root);
            enhanceTooltips($root);
            enhanceSelects($root);

            const h = parseHash();
            setActiveStep($root, h.stepKey || STEP_KEY_FALLBACK, { pushHash: false });
        } catch (error) {
            if (window.console && window.console.error) {
                window.console.error('MPWEM event edit initialization failed.', error);
            }
        } finally {
            window.requestAnimationFrame(markReady);
        }

        $root.on('click', '.mpwem-step', function() {
            setActiveStep($root, $(this).data('step-key'), { pushHash: true, validate: true });
        });

        $root.on('click', '.mpwem-wizard-prev', function() {
            const $steps = $root.find('.mpwem-step:visible');
            const idx = $steps.index($steps.filter('.is-active'));
            if (idx > 0) setActiveStep($root, $steps.eq(idx - 1).data('step-key'), { pushHash: true });
        });

        $root.on('click', '.mpwem-wizard-next', function() {
            const $steps = $root.find('.mpwem-step:visible');
            const idx = $steps.index($steps.filter('.is-active'));
            if (idx < $steps.length - 1) {
                setActiveStep($root, $steps.eq(idx + 1).data('step-key'), { pushHash: true, validate: true });
            } else {
                if (!validateDateWiseGlobalQty($root)) {
                    return;
                }
                $('#mpwem-event-edit-form').submit();
            }
        });

        // Topbar "Save" Button Handler
        $root.on('click', '.mpwem-wizard-save-draft', function(e) {
            e.preventDefault();
            if (!validateDateWiseGlobalQty($root)) {
                return;
            }
            $('#mpwem-event-edit-form').submit();
        });

        $root.on('click', '#mpwem_wizard_date_mount .mpwem_add_item, #mpwem_wizard_date_mount .ttbm_add_new_special_date', function() {
            window.setTimeout(function() {
                enhanceDateStep($root);
            }, 80);
        });

        // Add Premium Toaster Notification on form submit
        $('#mpwem-event-edit-form').on('submit', function(e) {
            if (!validateDateWiseGlobalQty($root, { focusStep: true })) {
                e.preventDefault();
                return false;
            }
            showToast('Saving changes... Please wait', 'info');
        });

        $(window).on('hashchange', function() {
            const h = parseHash();
            setActiveStep($root, h.stepKey || STEP_KEY_FALLBACK, { pushHash: false });
        });

        $(document).on('click', function() {
            $('.mpwem-select-wrapper').removeClass('is-open');
        });
    });

})(jQuery);
