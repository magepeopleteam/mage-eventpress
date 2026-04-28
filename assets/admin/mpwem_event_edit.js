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
        mountPanel($root, '#mp_event_venue', 'mpwem_wizard_venue_mount');
        
        // Mount into Tickets Step
        mountPanel($root, '#mpwem_ticket_pricing_settings', 'mpwem_wizard_tickets_mount');

        // Move Extra Services into its own card
        const $exService = $('#mpwem_ticket_pricing_settings').find('input[name="option_name[]"]').first().closest('._layout_default_xs_mp_zero');
        if ($exService.length) {
            const $extraMount = $('#mpwem_wizard_extra_services_mount');
            if ($extraMount.length) {
                $exService.detach().appendTo($extraMount);
                $('#mpwem_wizard_extra_services_card').show();
                // Remove the legacy header from inside the card body since our new card has its own head
                $exService.find('._bg_light_padding').first().hide();
            }
        }

        // Move the Ticket Pricing documentation section into the wizard sidebar
        const $ticketDoc = $('#mpwem_wizard_tickets_mount').find('.bg-light').first();
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

        // Mount into Date Step
        mountPanel($root, '#mpwem_date_settings', 'mpwem_wizard_date_mount');

        // Mount into Display Step
        mountPanel($root, '#mep_event_faq_meta', 'mpwem_wizard_faq_mount');
        mountPanel($root, '#mp_event_rich_text', 'mpwem_wizard_seo_mount');
        mountPanel($root, '#mpwem_email_text_settings', 'mpwem_wizard_email_mount');
        mountPanel($root, '#mep_event_template', 'mpwem_wizard_template_mount');

        // Mount into Related Step
        mountPanel($root, '#mep_related_event_meta', 'mpwem_wizard_related_mount');

        // Mount into Timeline Step
        mountPanel($root, '#mep_event_timeline_meta', 'mpwem_wizard_timeline_mount');
        
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

    function enhanceSelects($container) {
        $container.find('select').each(function() {
            const $select = $(this);
            if ($select.hasClass('mpwem-enhanced') || $select.is('[multiple]') || $select.closest('.mpwem-select-wrapper').length) return;

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

    function setActiveStep($root, stepKey, options) {
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

    $(function () {
        const $root = getWizardRoot();
        if (!$root.length) return;

        const markReady = function() {
            $root.removeClass('is-loading').addClass('is-ready');
        };

        window.setTimeout(markReady, 2500);
        bindCreateEvent($root);
        bindFeaturedImage($root);

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
                $('#mpwem-event-edit-form').submit();
            }
        });

        // Topbar "Save" Button Handler
        $root.on('click', '.mpwem-wizard-save-draft', function(e) {
            e.preventDefault();
            $('#mpwem-event-edit-form').submit();
        });

        // Add Premium Toaster Notification on form submit
        $('#mpwem-event-edit-form').on('submit', function() {
            let $toast = $('.mpwem-toast');
            if ($toast.length === 0) {
                $toast = $(`
                    <div class="mpwem-toast">
                        <span class="dashicons dashicons-update-alt"></span>
                        <span class="mpwem-toast-text">Saving changes... Please wait</span>
                    </div>
                `);
                $('body').append($toast);
            }
            // Trigger layout reflow for animation
            $toast[0].offsetHeight;
            $toast.addClass('show');
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
