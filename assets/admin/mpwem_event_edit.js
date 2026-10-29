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

    // -------------------------------------------------------------------------
    // Shared body-scroll lock helpers used by all modal open/close functions.
    // -------------------------------------------------------------------------
    function lockBodyScroll() {
        $('body').addClass('mpwem-ticket-modal-open');
    }

    function unlockBodyScroll() {
        $('body').removeClass('mpwem-ticket-modal-open');
    }

    /**
     * Scroll a modal body so that $row is visible, then briefly highlight it.
     * Used by ticket, extra-service, and date modal contexts.
     *
     * @param {jQuery} $row - The row element to bring into view and flash.
     */
    function highlightRow($row) {
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

    /**
     * Attaches a drag-to-scroll interaction to a horizontally scrollable element.
     * Safe to call multiple times — guards with a data flag.
     *
     * @param {jQuery} $scroller  - The scrollable container element.
     * @param {string} namespace  - jQuery event namespace (e.g. 'mpwemDragScroll').
     * @param {string} flagName   - jQuery .data() key used to prevent double init.
     */
    function initializeDragScroll($scroller, namespace, flagName) {
        if (!$scroller.length || $scroller.data(flagName)) {
            return;
        }

        const interactiveSelector = 'input, textarea, select, button, a, .mpwem-select-wrapper, .ui-datepicker, .wp-picker-container';
        let isDragging = false;
        let startX = 0;
        let startScrollLeft = 0;

        $scroller.on('mousedown.' + namespace, function(e) {
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

        $(document).on('mousemove.' + namespace, function(e) {
            if (!isDragging) {
                return;
            }

            const deltaX = e.pageX - startX;
            $scroller.scrollLeft(startScrollLeft - deltaX);
        });

        $(document).on('mouseup.' + namespace + ' mouseleave.' + namespace, function() {
            if (!isDragging) {
                return;
            }

            isDragging = false;
            $scroller.removeClass('is-dragging');
        });

        $scroller.data(flagName, true);
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
        const iconClass = toastType === 'error'
            ? 'dashicons-warning'
            : (toastType === 'warning'
                ? 'dashicons-info-outline'
                : (toastType === 'success' ? 'dashicons-yes-alt' : 'dashicons-update-alt'));

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
        $toast.removeClass('show is-error is-success is-info is-warning').addClass('is-' + toastType);
        $toast.find('.dashicons').attr('class', 'dashicons ' + iconClass);
        $toast.find('.mpwem-toast-text').text(message || '');
        $toast[0].offsetHeight;
        $toast.addClass('show');

        const timer = window.setTimeout(function() {
            $toast.removeClass('show');
        }, toastType === 'error' ? 4200 : (toastType === 'warning' ? 3600 : (toastType === 'success' ? 3200 : 2600)));

        $toast.data('mpwemToastTimer', timer);
    }

    /* Success modal shown right after an event is published/updated/saved,
       offering a "Preview" button that opens the front-end page in a new tab. */
    function showSaveSuccessModal(title, message, previewUrl) {
        let $modal = $('#mpwem_save_success_modal');
        if (!$modal.length) {
            $modal = $(
                '<div class="mpwem-ticket-modal mpwem-success-modal" id="mpwem_save_success_modal" aria-hidden="true">' +
                    '<div class="mpwem-ticket-modal__backdrop" data-mpwem-success-modal-close></div>' +
                    '<div class="mpwem-success-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="mpwem_save_success_modal_title">' +
                        '<button type="button" class="mpwem-success-modal__close" data-mpwem-success-modal-close aria-label="Close">' +
                            '<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>' +
                        '</button>' +
                        '<div class="mpwem-success-modal__icon"><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span></div>' +
                        '<h2 class="mpwem-success-modal__title" id="mpwem_save_success_modal_title"></h2>' +
                        '<p class="mpwem-success-modal__message"></p>' +
                        '<div class="mpwem-success-modal__actions">' +
                            '<a href="#" target="_blank" rel="noopener noreferrer" class="button button-primary mpwem-success-modal__preview">' +
                                '<span class="dashicons dashicons-external" aria-hidden="true"></span> Preview' +
                            '</a>' +
                            '<button type="button" class="button mpwem-success-modal__dismiss" data-mpwem-success-modal-close>Close</button>' +
                        '</div>' +
                    '</div>' +
                '</div>'
            );
            $('body').append($modal);

            $modal.on('click', '[data-mpwem-success-modal-close]', function(e) {
                e.preventDefault();
                $modal.attr('aria-hidden', 'true').removeClass('is-open');
            });
            $modal.on('click', '.mpwem-success-modal__preview', function() {
                window.setTimeout(function() {
                    $modal.attr('aria-hidden', 'true').removeClass('is-open');
                }, 150);
            });
            $(document).on('keydown.mpwemSuccessModal', function(e) {
                if (e.key === 'Escape' && $modal.hasClass('is-open')) {
                    $modal.attr('aria-hidden', 'true').removeClass('is-open');
                }
            });
        }

        $modal.find('.mpwem-success-modal__title').text(title);
        $modal.find('.mpwem-success-modal__message').text(message);

        const $preview = $modal.find('.mpwem-success-modal__preview');
        if (previewUrl) {
            $preview.attr('href', previewUrl).show();
        } else {
            $preview.hide();
        }

        $modal.attr('aria-hidden', 'false').addClass('is-open');
    }

    /* Scroll so $el lands centered in the viewport, instead of just under
       the top edge - used wherever focus should land on a specific card
       or row (ticket add/edit form, newly saved ticket). */
    function scrollToViewportCenter($el, duration) {
        if (!$el || !$el.length) {
            return;
        }
        const offset = $el.offset();
        if (!offset) {
            return;
        }
        const target = offset.top - (window.innerHeight / 2) + ($el.outerHeight() / 2);
        $('html, body').animate({ scrollTop: Math.max(target, 0) }, duration || 200);
    }

    /* Briefly pulse an element (e.g. a button) to draw attention to it. */
    function flashAttention($el) {
        if (!$el || !$el.length) {
            return;
        }
        $el.removeClass('mpwem-attention-highlight');
        // Force reflow so the animation restarts on repeated calls.
        void $el[0].offsetWidth;
        $el.addClass('mpwem-attention-highlight');
        window.setTimeout(function() {
            $el.removeClass('mpwem-attention-highlight');
        }, 2600);
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

    function initSpeakerPicker($root) {
        const $panels = $root.find('.mpwem_speaker_settings');
        if (!$panels.length) {
            return;
        }

        const cfg = getConfig();
        const strings = cfg.speaker || {};
        let speakerModalSaving = false;
        let speakerMediaFrame = null;

        const escapeHtml = function(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        };

        const updateCount = function($panel) {
            const count = $panel.find('.mpwem-speaker-option input[type="checkbox"]:checked').length;
            const $badge = $panel.find('[data-speaker-count]');
            if ($badge.length) {
                $badge.text(count + ' selected');
            }
        };

        const getSpeakerModal = function() {
            return document.getElementById('mpwem-event-speaker-modal');
        };

        const setSpeakerImagePreview = function(url, id) {
            const preview = document.getElementById('mpwem-event-speaker-image-preview');
            const removeBtn = document.getElementById('mpwem-event-speaker-image-remove');
            const selectBtn = document.getElementById('mpwem-event-speaker-image-select');
            const idInput = document.getElementById('mpwem-event-speaker-image-id');
            if (!preview || !idInput) {
                return;
            }
            idInput.value = String(id || 0);
            if (url) {
                preview.classList.add('has-image');
                preview.innerHTML = '<img src="' + escapeHtml(url) + '" alt="" />';
                if (removeBtn) {
                    removeBtn.hidden = false;
                }
                if (selectBtn) {
                    selectBtn.textContent = strings.imageChange || 'Change Image';
                }
            } else {
                preview.classList.remove('has-image');
                preview.innerHTML = '<span class="dashicons dashicons-format-image"></span>';
                if (removeBtn) {
                    removeBtn.hidden = true;
                }
                if (selectBtn) {
                    selectBtn.textContent = strings.imageSelect || 'Select Image';
                }
            }
        };

        const setSpeakerModalStatus = function(message, type) {
            const status = document.getElementById('mpwem-event-speaker-modal-status');
            if (!status) {
                return;
            }
            if (!message) {
                status.hidden = true;
                status.textContent = '';
                status.className = 'mpwem-speaker-modal__status';
                return;
            }
            status.hidden = false;
            status.textContent = message;
            status.className = 'mpwem-speaker-modal__status is-' + (type || 'error');
        };

        const closeSpeakerModal = function() {
            const modal = getSpeakerModal();
            if (!modal) {
                return;
            }
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('mpwem-speaker-modal-open');
            speakerModalSaving = false;
        };

        const resetSpeakerModalForm = function() {
            const form = document.getElementById('mpwem-event-speaker-create-form');
            if (form) {
                form.reset();
            }
            setSpeakerImagePreview('', 0);
            setSpeakerModalStatus('', '');
            const saveBtn = document.getElementById('mpwem-event-speaker-save-btn');
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.textContent = strings.save || 'Create Speaker';
            }
        };

        const openSpeakerMedia = function() {
            if (typeof wp === 'undefined' || !wp.media) {
                return;
            }
            if (!speakerMediaFrame) {
                speakerMediaFrame = wp.media({
                    title: strings.imageSelect || 'Select Image',
                    button: { text: strings.imageSelect || 'Select Image' },
                    library: { type: 'image' },
                    multiple: false
                });
                speakerMediaFrame.on('select', function() {
                    const attachment = speakerMediaFrame.state().get('selection').first().toJSON();
                    const url = (attachment.sizes && attachment.sizes.thumbnail)
                        ? attachment.sizes.thumbnail.url
                        : attachment.url;
                    setSpeakerImagePreview(url, attachment.id);
                });
            }
            speakerMediaFrame.open();
        };

        const ensureSpeakerSelectGrid = function($panel) {
            let $select = $panel.find('.mpwem-speaker-select').first();
            if (!$select.length) {
                $panel.find('.mpwem-speaker-select__empty-state').remove();
                $select = $(
                    '<div class="mpwem-speaker-select">' +
                        '<div class="mpwem-speaker-select__grid"></div>' +
                    '</div>'
                );
                $panel.find('.mpwem-speaker-picker').append($select);
            }
            return $select.find('.mpwem-speaker-select__grid').first();
        };

        const appendSpeakerOption = function($panel, data) {
            const id = parseInt(data.id, 10) || 0;
            if (!id) {
                return;
            }
            if ($panel.find('.mpwem-speaker-option input[value="' + id + '"]').length) {
                $panel.find('.mpwem-speaker-option input[value="' + id + '"]').prop('checked', true).trigger('change');
                return;
            }

            const name = data.name || '';
            const role = data.role || '';
            const imageUrl = data.image_url || '';
            const initial = name ? name.charAt(0).toUpperCase() : '?';
            const avatarHtml = imageUrl
                ? '<img src="' + escapeHtml(imageUrl) + '" alt="" />'
                : '<span class="mpwem-speaker-option__initial">' + escapeHtml(initial) + '</span>';

            const $option = $(
                '<label class="mpwem-speaker-option is-selected">' +
                    '<input type="checkbox" name="mep_event_speakers_list[]" value="' + id + '" checked="checked" data-no-mpwem-switch="1" />' +
                    '<span class="mpwem-speaker-option__avatar' + (imageUrl ? ' has-image' : '') + '" aria-hidden="true">' +
                        avatarHtml +
                        '<span class="mpwem-speaker-option__check"><span class="dashicons dashicons-yes"></span></span>' +
                    '</span>' +
                    '<span class="mpwem-speaker-option__meta">' +
                        '<span class="mpwem-speaker-option__name">' + escapeHtml(name) + '</span>' +
                        (role ? '<span class="mpwem-speaker-option__role">' + escapeHtml(role) + '</span>' : '') +
                    '</span>' +
                '</label>'
            );

            const $grid = ensureSpeakerSelectGrid($panel);
            $grid.prepend($option);
            updateCount($panel);
        };

        const submitSpeakerForm = function(event) {
            event.preventDefault();
            if (speakerModalSaving) {
                return;
            }

            const nameInput = document.getElementById('mpwem-event-speaker-name');
            const name = nameInput ? nameInput.value.trim() : '';
            if (!name) {
                setSpeakerModalStatus(strings.nameRequired || 'Please enter a speaker name.', 'error');
                if (nameInput) {
                    nameInput.focus();
                }
                return;
            }

            speakerModalSaving = true;
            setSpeakerModalStatus('', '');
            const saveBtn = document.getElementById('mpwem-event-speaker-save-btn');
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.textContent = strings.saving || 'Creating…';
            }

            const body = new FormData();
            body.append('action', 'mpwem_speaker_create');
            body.append('nonce', cfg.speaker_nonce || '');
            body.append('name', name);
            body.append('excerpt', (document.getElementById('mpwem-event-speaker-role') || {}).value || '');
            body.append('description', (document.getElementById('mpwem-event-speaker-desc') || {}).value || '');
            body.append('image_id', (document.getElementById('mpwem-event-speaker-image-id') || {}).value || '0');
            body.append('status', (document.getElementById('mpwem-event-speaker-status') || {}).value || 'publish');

            fetch(cfg.ajax_url || window.ajaxurl || '', {
                method: 'POST',
                credentials: 'same-origin',
                body: body
            })
                .then(function(res) {
                    return res.json();
                })
                .then(function(json) {
                    if (!json || !json.success) {
                        const msg = (json && json.data) ? json.data : (strings.createError || 'Could not create speaker.');
                        throw new Error(typeof msg === 'string' ? msg : (strings.createError || 'Could not create speaker.'));
                    }
                    const data = json.data || {};
                    $panels.each(function() {
                        appendSpeakerOption($(this), data);
                    });
                    closeSpeakerModal();
                    if (typeof showToast === 'function') {
                        showToast(data.message || strings.createSuccess || 'Speaker created successfully.', 'success');
                    }
                })
                .catch(function(err) {
                    setSpeakerModalStatus(err.message || strings.createError || 'Could not create speaker.', 'error');
                    speakerModalSaving = false;
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.textContent = strings.save || 'Create Speaker';
                    }
                });
        };

        const ensureSpeakerModal = function() {
            let modal = getSpeakerModal();
            if (modal) {
                return modal;
            }

            modal = document.createElement('div');
            modal.id = 'mpwem-event-speaker-modal';
            modal.className = 'mpwem-speaker-modal';
            modal.setAttribute('aria-hidden', 'true');
            modal.innerHTML =
                '<div class="mpwem-speaker-modal__backdrop" data-event-speaker-modal-close="1"></div>' +
                '<div class="mpwem-speaker-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="mpwem-event-speaker-modal-title">' +
                    '<button type="button" class="mpwem-speaker-modal__close" data-event-speaker-modal-close="1" aria-label="' + escapeHtml(strings.cancel || 'Cancel') + '">' +
                        '<span class="dashicons dashicons-no-alt"></span>' +
                    '</button>' +
                    '<div class="mpwem-speaker-modal__header">' +
                        '<span class="mpwem-speaker-modal__icon dashicons dashicons-groups" aria-hidden="true"></span>' +
                        '<div>' +
                            '<h2 id="mpwem-event-speaker-modal-title">' + escapeHtml(strings.modalTitle || 'Add New Speaker') + '</h2>' +
                            '<p>' + escapeHtml(strings.modalSubtitle || 'Create a speaker profile and assign it to this event.') + '</p>' +
                        '</div>' +
                    '</div>' +
                    '<form id="mpwem-event-speaker-create-form" class="mpwem-speaker-modal__form" novalidate>' +
                        '<div class="mpwem-speaker-modal__grid">' +
                            '<div class="mpwem-speaker-modal__field mpwem-speaker-modal__field--full">' +
                                '<label for="mpwem-event-speaker-name">' + escapeHtml(strings.nameLabel || 'Speaker Name') + ' <span>*</span></label>' +
                                '<input type="text" id="mpwem-event-speaker-name" name="name" required maxlength="200" placeholder="' + escapeHtml(strings.namePlaceholder || '') + '" />' +
                            '</div>' +
                            '<div class="mpwem-speaker-modal__field mpwem-speaker-modal__field--full">' +
                                '<label for="mpwem-event-speaker-role">' + escapeHtml(strings.roleLabel || 'Role / Title') + '</label>' +
                                '<input type="text" id="mpwem-event-speaker-role" name="excerpt" maxlength="200" placeholder="' + escapeHtml(strings.rolePlaceholder || '') + '" />' +
                            '</div>' +
                            '<div class="mpwem-speaker-modal__field mpwem-speaker-modal__field--full">' +
                                '<label for="mpwem-event-speaker-desc">' + escapeHtml(strings.descLabel || 'Description') + '</label>' +
                                '<textarea id="mpwem-event-speaker-desc" name="description" rows="4" placeholder="' + escapeHtml(strings.descPlaceholder || '') + '"></textarea>' +
                            '</div>' +
                            '<div class="mpwem-speaker-modal__field">' +
                                '<label>' + escapeHtml(strings.imageLabel || 'Featured Image') + '</label>' +
                                '<div class="mpwem-speaker-modal__image">' +
                                    '<div class="mpwem-speaker-modal__preview" id="mpwem-event-speaker-image-preview">' +
                                        '<span class="dashicons dashicons-format-image"></span>' +
                                    '</div>' +
                                    '<div class="mpwem-speaker-modal__image-actions">' +
                                        '<button type="button" class="button" id="mpwem-event-speaker-image-select">' + escapeHtml(strings.imageSelect || 'Select Image') + '</button>' +
                                        '<button type="button" class="button-link-delete" id="mpwem-event-speaker-image-remove" hidden>' + escapeHtml(strings.imageRemove || 'Remove') + '</button>' +
                                        '<input type="hidden" id="mpwem-event-speaker-image-id" name="image_id" value="0" />' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="mpwem-speaker-modal__field">' +
                                '<label for="mpwem-event-speaker-status">' + escapeHtml(strings.statusLabel || 'Status') + '</label>' +
                                '<select id="mpwem-event-speaker-status" name="status">' +
                                    '<option value="publish">' + escapeHtml(strings.statusPublish || 'Publish') + '</option>' +
                                    '<option value="draft">' + escapeHtml(strings.statusDraft || 'Draft') + '</option>' +
                                '</select>' +
                            '</div>' +
                        '</div>' +
                        '<div class="mpwem-speaker-modal__status" id="mpwem-event-speaker-modal-status" hidden></div>' +
                        '<div class="mpwem-speaker-modal__actions">' +
                            '<button type="button" class="button" data-event-speaker-modal-close="1">' + escapeHtml(strings.cancel || 'Cancel') + '</button>' +
                            '<button type="submit" class="button button-primary" id="mpwem-event-speaker-save-btn">' + escapeHtml(strings.save || 'Create Speaker') + '</button>' +
                        '</div>' +
                    '</form>' +
                '</div>';

            document.body.appendChild(modal);

            modal.addEventListener('click', function(event) {
                if (event.target.closest('[data-event-speaker-modal-close]')) {
                    closeSpeakerModal();
                }
            });
            document.getElementById('mpwem-event-speaker-image-select').addEventListener('click', openSpeakerMedia);
            document.getElementById('mpwem-event-speaker-image-remove').addEventListener('click', function() {
                setSpeakerImagePreview('', 0);
            });
            document.getElementById('mpwem-event-speaker-create-form').addEventListener('submit', submitSpeakerForm);
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                    closeSpeakerModal();
                }
            });

            return modal;
        };

        const openSpeakerModal = function() {
            const modal = ensureSpeakerModal();
            resetSpeakerModalForm();
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('mpwem-speaker-modal-open');
            window.setTimeout(function() {
                const nameInput = document.getElementById('mpwem-event-speaker-name');
                if (nameInput) {
                    nameInput.focus();
                }
            }, 30);
        };

        $panels.each(function() {
            const $panel = $(this);
            updateCount($panel);

            // Classic editor still uses the nested enable toggle; the modern
            // wizard card owns enable/disable and strips this control's name.
            if ($panel.hasClass('mpwem-embedded-panel')) {
                $panel.find('#mep_event_enable_speaker').removeAttr('name');
                $panel.find('#mpwem-speaker-fields').show();
            }
        });

        $root.off('click.mpwemSpeakerAdd', '[data-mpwem-speaker-add]')
            .on('click.mpwemSpeakerAdd', '[data-mpwem-speaker-add]', function(event) {
                event.preventDefault();
                openSpeakerModal();
            });

        $root.off('change.mpwemSpeakerSelect', '.mpwem-speaker-option input[type="checkbox"]')
            .on('change.mpwemSpeakerSelect', '.mpwem-speaker-option input[type="checkbox"]', function() {
                const $option = $(this).closest('.mpwem-speaker-option');
                $option.toggleClass('is-selected', $(this).is(':checked'));
                updateCount($option.closest('.mpwem_speaker_settings'));
            });
    }

    function normalizePanelLabel(text) {
        return (text || '')
            .toString()
            .toLowerCase()
            .replace(/&/g, ' and ')
            .replace(/[^a-z0-9]+/g, ' ')
            .trim();
    }

    function panelLooksLikeTermsSection($panel) {
        if (!$panel || !$panel.length) {
            return false;
        }

        const candidateTexts = [];
        const panelId = (($panel.data('tab-item') || '') + ' ' + ($panel.attr('id') || '')).trim();
        if (panelId) {
            candidateTexts.push(panelId);
        }

        $panel.find('h1, h2, h3, h4, .section-title, .mpev-label, .label-text').slice(0, 8).each(function() {
            const text = $.trim($(this).text());
            if (text) {
                candidateTexts.push(text);
            }
        });

        return candidateTexts.some(function(text) {
            const normalized = normalizePanelLabel(text);
            return normalized.indexOf('terms and conditions') !== -1
                || (normalized.indexOf('terms') !== -1 && normalized.indexOf('condition') !== -1);
        });
    }

    function panelLooksLikeAttendeeFormSection($panel) {
        if (!$panel || !$panel.length) {
            return false;
        }

        const candidateTexts = [];
        const panelId = (($panel.data('tab-item') || '') + ' ' + ($panel.attr('id') || '')).trim();
        if (panelId) {
            candidateTexts.push(panelId);
        }

        $panel.find('h1, h2, h3, h4, .section-title, .mpev-label, .label-text, .event_meta_help_txt').slice(0, 10).each(function() {
            const text = $.trim($(this).text());
            if (text) {
                candidateTexts.push(text);
            }
        });

        return candidateTexts.some(function(text) {
            const normalized = normalizePanelLabel(text);
            return normalized.indexOf('attendee form') !== -1
                || normalized.indexOf('attendee registration form') !== -1
                || normalized.indexOf('attendee form settings') !== -1
                || normalized.indexOf('mp event reg form menu') !== -1;
        });
    }

    function panelLooksLikeWaitlistSection($panel) {
        if (!$panel || !$panel.length) {
            return false;
        }

        const candidateTexts = [];
        const panelId = (($panel.data('tab-item') || '') + ' ' + ($panel.attr('id') || '') + ' ' + ($panel.attr('class') || '')).trim();
        if (panelId) {
            candidateTexts.push(panelId);
        }

        $panel.find('h1, h2, h3, h4, h5, .section-title, .mpev-label, .label-text, .event_meta_help_txt, label, strong').slice(0, 20).each(function() {
            const text = $.trim($(this).text());
            if (text) {
                candidateTexts.push(text);
            }
        });

        const fieldHints = $panel.find('input, select, textarea').map(function() {
            return [$(this).attr('name'), $(this).attr('id')].filter(Boolean).join(' ');
        }).get();

        candidateTexts.push.apply(candidateTexts, fieldHints);

        return candidateTexts.some(function(text) {
            const normalized = normalizePanelLabel(text);
            return normalized.indexOf('waitlist') !== -1
                || normalized.indexOf('show wait list') !== -1
                || normalized.indexOf('show waitlist') !== -1
                || normalized.indexOf('wait list form') !== -1
                || normalized.indexOf('waitlist form') !== -1;
        });
    }

    function panelLooksLikePdfCustomTextSection($panel) {
        if (!$panel || !$panel.length) {
            return false;
        }

        const candidateTexts = [];
        const panelId = (($panel.data('tab-item') || '') + ' ' + ($panel.attr('id') || '')).trim();
        if (panelId) {
            candidateTexts.push(panelId);
        }

        $panel.find('h1, h2, h3, h4, .section-title, .mpev-label, .label-text, .event_meta_help_txt').slice(0, 12).each(function() {
            const text = $.trim($(this).text());
            if (text) {
                candidateTexts.push(text);
            }
        });

        return candidateTexts.some(function(text) {
            const normalized = normalizePanelLabel(text);
            return normalized.indexOf('pdf custom text') !== -1
                || normalized.indexOf('custom pdf text') !== -1
                || normalized.indexOf('pdf ticket custom text') !== -1
                || (normalized.indexOf('pdf') !== -1 && normalized.indexOf('custom') !== -1 && normalized.indexOf('text') !== -1);
        });
    }

    function panelLooksLikeEmailReminderSection($panel) {
        if (!$panel || !$panel.length) {
            return false;
        }

        const candidateTexts = [];
        const panelId = (($panel.data('tab-item') || '') + ' ' + ($panel.attr('id') || '') + ' ' + ($panel.attr('class') || '')).trim();
        if (panelId) {
            candidateTexts.push(panelId);
        }

        $panel.find('h1, h2, h3, h4, h5, .section-title, .mpev-label, .label-text, .event_meta_help_txt, strong').slice(0, 16).each(function() {
            const text = $.trim($(this).text());
            if (text) {
                candidateTexts.push(text);
            }
        });

        return candidateTexts.some(function(text) {
            const normalized = normalizePanelLabel(text);
            return normalized.indexOf('email reminder') !== -1
                || normalized.indexOf('emails reminder') !== -1
                || normalized.indexOf('event email reminder') !== -1
                || (normalized.indexOf('reminder') !== -1 && normalized.indexOf('email') !== -1);
        });
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
        mountPanel($root, '#mpwem_speaker_settings', 'mpwem_wizard_speaker_mount');
        
        // Mount into Tickets Step
        mountPanel($root, '#mpwem_ticket_pricing_settings', 'mpwem_wizard_tickets_mount');

        // Move the payment-method warning/status banner out of the mounted
        // panel and into its own standalone sidebar card, right before the
        // "Event Mode Manual" help card, instead of leaving it stacked
        // above the ticket editor.
        const $paymentWarnings = $('#mpwem_wizard_tickets_mount').find('.mpwem-ticket-warnings').first();
        const $modeHelpCardBanner = $('#mpwem_wizard_mode_help_card_banner');
        if ($paymentWarnings.length && $modeHelpCardBanner.length && !$modeHelpCardBanner.children().length) {
            $modeHelpCardBanner.append($paymentWarnings.detach());
        }

        // Only rendered by the legacy hook when WooCommerce "Enable taxes" is on.
        mountPanel($root, '#mp_event_tax_settings', 'mpwem_wizard_tax_mount');
        const $taxMount = $('#mpwem_wizard_tax_mount');
        if ($taxMount.children().length) {
            const $taxPanel = $taxMount.children('.mp_tab_item').first();
            // Our card head already shows "Tax Settings" - hide the legacy
            // tab title/description and the inner banner that repeat it.
            $taxPanel.children('h3, p').hide();
            $taxPanel.find('section.bg-light').first().hide();
            $('#mpwem_wizard_tax_card').show();
        }

        const $ticketPricingPanel = getPanel($root, '#mpwem_ticket_pricing_settings');
        decorateMinMaxSettings($root);

        // Move Extra Services into its own card
        const $exService = $ticketPricingPanel.find('input[name="option_name[]"]').first().closest('._layout_default_xs_mp_zero');
        if ($exService.length) {
            const $extraMount = $('#mpwem_wizard_extra_services_mount');
            if ($extraMount.length) {
                $exService.addClass('mpwem_style');
                $exService.detach().appendTo($extraMount);
                $('#mpwem_wizard_extra_services_card').show().data('mpwem-mounted', true);
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
                $cardHead.append($('<span class="mpwem-section-pill"></span>').text('Docs'));
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
        initializeParticularDateModal($root);
        bindLiveTicketPreviewQtyButtons();

        // Mount into Date Step
        mountPanel($root, '#mpwem_date_settings', 'mpwem_wizard_date_mount');

        // Mount into Advanced Step
        mountPanel($root, '#mep_event_template', 'mpwem_wizard_template_mount');
        mountPanel($root, '#mep_event_faq_meta', 'mpwem_wizard_faq_mount');
        mountPanel($root, '#mep_event_timeline_meta', 'mpwem_wizard_timeline_mount');
        mountPanel($root, '#mep_related_event_meta', 'mpwem_wizard_related_mount');
        mountPanel($root, '#mpwem_email_text_settings', 'mpwem_wizard_email_mount');
        mountPanel($root, '#mp_event_rich_text', 'mpwem_wizard_seo_mount');
        mountPanel($root, '#mp_event_reg_form_menu', 'mpwem_wizard_attendee_form_mount');
        mountPanel($root, '#mep_deposit_settings_tab', 'mpwem_wizard_deposit_mount');
        mountDangerZone($root);

        const $legacySettingsPanel = $root.find('.mp_tab_item[data-tab-item="#mpwem_event_settings"]').first();
        if ($legacySettingsPanel.length) {
            $legacySettingsPanel
                .addClass('mpwem-legacy-panel--disabled')
                .hide()
                .find('input, select, textarea, button')
                .prop('disabled', true);
        }
        
        // Handle remaining legacy panels (Additional Sections)
        const $steps = $root.find('.mpwem-step');
        const stepPanelSelectors = [];
        $steps.each(function() { stepPanelSelectors.push($(this).data('panel')); });
        
        const $additionalMount = $('#mpwem_additional_sections_mount');
        const $termsMount = $('#mpwem_wizard_terms_mount');
        const $attendeeFormMount = $('#mpwem_wizard_attendee_form_mount');
        const $emailReminderMount = $('#mpwem_wizard_email_reminder_mount');
        const $pdfCustomTextMount = $('#mpwem_wizard_pdf_custom_text_mount');
        if ($additionalMount.length) {
            $root.find('.mpwem-wizard-panels > .mp_tab_item').each(function() {
                const $p = $(this);
                const id = $p.data('tab-item') || ('#' + this.id);
                // Don't move if it's a step panel or already handled
                if (stepPanelSelectors.indexOf(id) !== -1 || id === '#mp_event_venue' || id === '#ttbm_settings_gallery' || id === '#mpwem_event_settings' || id === '#mpwem_speaker_settings' || $p.hasClass('mpwem-wizard-panel')) {
                    return;
                }

                if ($termsMount.length && !$termsMount.children().length && panelLooksLikeTermsSection($p)) {
                    $p.addClass('mpwem-embedded-panel mpwem-legacy-panel').detach().appendTo($termsMount).show();
                    return;
                }

                if ($attendeeFormMount.length && !$attendeeFormMount.children().length && panelLooksLikeAttendeeFormSection($p)) {
                    $p.addClass('mpwem-embedded-panel mpwem-legacy-panel').detach().appendTo($attendeeFormMount).show();
                    return;
                }

                if (panelLooksLikeWaitlistSection($p)) {
                    $p
                        .addClass('mpwem-legacy-panel--disabled')
                        .hide()
                        .find('input, select, textarea, button')
                        .prop('disabled', true);
                    return;
                }
                
                if ($emailReminderMount.length && !$emailReminderMount.children().length && panelLooksLikeEmailReminderSection($p)) {
                    $p.addClass('mpwem-embedded-panel mpwem-legacy-panel').detach().appendTo($emailReminderMount).show();
                    return;
                }

                if ($pdfCustomTextMount.length && !$pdfCustomTextMount.children().length && panelLooksLikePdfCustomTextSection($p)) {
                    $p.addClass('mpwem-embedded-panel mpwem-legacy-panel').detach().appendTo($pdfCustomTextMount).show();
                    return;
                }

                $p.addClass('mpwem-embedded-panel mpwem-legacy-panel').detach().appendTo($additionalMount).show();
            });
            if ($additionalMount.children().length) $('#mpwem_edit_page_additional').show();
        }

        enhanceDisplayStep($root);
    }

    function getTicketModalContext($root) {
        const $ticketPanel = getPanel($root, '#mpwem_ticket_pricing_settings');
        let $summary = $ticketPanel.find('#mpwem_ticket_summary').first();

        if ($ticketPanel.length && !$summary.length) {
            const $summaryMarkup = $(
                '<div class="mpwem-ticket-summary" id="mpwem_ticket_summary">' +
                    '<div class="mpwem-ticket-summary__toolbar">' +
                        '<span class="mpwem-ticket-summary__toolbar-icon" aria-hidden="true">' +
                            '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4Z"></path><path d="M13 5v2M13 17v2M13 10.5v3"></path></svg>' +
                        '</span>' +
                        '<div class="mpwem-ticket-summary__intro">' +
                            '<h3>Simple Ticket Type</h3>' +
                            '<p>View pricing at a glance, then open the full editor only when you need details.</p>' +
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
            $summary: $summary,
            $summaryList: $summary.find('#mpwem_ticket_summary_list').first(),
            $modal: $root.find('#mpwem_ticket_editor_modal').first(),
            $modalMount: $root.find('#mpwem_ticket_modal_mount').first(),
            $modalAdvanceToggle: $root.find('#mpwem_ticket_modal_advance_toggle').first(),
            $legacyMount: $root.find('#mpwem_wizard_tickets_mount').first()
        };
    }

    function getTicketRows($root) {
        const $container = $root.find('#mpwem_ticket_modal_mount tbody.mpwem-ticket-cards-container.mpwem_item_insert, #mpwem_ticket_modal_mount .mpwem-ticket-cards-container.mpwem_item_insert').first();
        if (!$container.length) {
            return $();
        }

        return $container.children('.mpwem-ticket-card.mpwem_remove_area, tr.mpwem_remove_area').filter(function() {
            return $(this).closest('.mpwem_hidden_content').length === 0;
        });
    }

    function getTicketRowsContainer($root) {
        return $root.find('#mpwem_ticket_modal_mount tbody.mpwem-ticket-cards-container.mpwem_item_insert, #mpwem_ticket_modal_mount .mpwem-ticket-cards-container.mpwem_item_insert').first();
    }

    function isRegistrationEnabled($root) {
        const $wizardInput = $root.find('#mep_reg_status').first();
        if ($wizardInput.length) {
            return $wizardInput.val() === 'on';
        }
        const $ticketPanel = getPanel($root, '#mpwem_ticket_pricing_settings');
        const $legacyCheckbox = $ticketPanel.find('input[name="mep_reg_status"]').first();
        if ($legacyCheckbox.length) {
            return $legacyCheckbox.is(':checked');
        }
        return false;
    }

    function storeSavedTicketSummarySnapshot($root) {
        const context = getTicketModalContext($root);
        const $ticketPanel = getPanel($root, '#mpwem_ticket_pricing_settings');
        const $container = getTicketRowsContainer($root);
        const $globalQtyToggle = context.$modalMount.find('input[name="enable_global_qty"]').first().add(
            $ticketPanel.find('input[name="enable_global_qty"]').first()
        ).add(
            $root.find('input[name="enable_global_qty"]').first()
        ).first();
        const $globalQtyInput = context.$modalMount.find('input[name="mep_gq_total_seat"]').first().add(
            $ticketPanel.find('input[name="mep_gq_total_seat"]').first()
        ).add(
            $root.find('input[name="mep_gq_total_seat"]').first()
        ).first();

        context.$modal
            .data('mpwemTicketSavedRowsHtml', $container.length ? $container.html() : '')
            .data('mpwemTicketSavedRegEnabled', isRegistrationEnabled($root))
            .data('mpwemTicketSavedGlobalQtyEnabled', $globalQtyToggle.is(':checked'))
            .data('mpwemTicketSavedGlobalTotalQty', (($globalQtyInput.val() || '').toString().trim()));
    }

    function getSavedTicketSummaryRows($root) {
        const context = getTicketModalContext($root);
        const savedRowsHtml = context.$modal.data('mpwemTicketSavedRowsHtml');

        if (typeof savedRowsHtml !== 'string') {
            return getTicketRows($root);
        }

        return $('<tbody></tbody>').html(savedRowsHtml).children('.mpwem-ticket-card.mpwem_remove_area, tr.mpwem_remove_area');
    }

    function beginTicketModalSession($root) {
        const context = getTicketModalContext($root);
        const $container = getTicketRowsContainer($root);
        if (!context.$modal.length) {
            return;
        }

        context.$modal
            .data('mpwemTicketSessionRowsHtml', $container.length ? $container.html() : '')
            .data('mpwemTicketPersistDraftRows', false);
    }

    function captureTicketDraftRows($root) {
        return $root;
    }

    function persistTicketDraftRows($root) {
        const context = getTicketModalContext($root);
        context.$modal.data('mpwemTicketPersistDraftRows', true);
    }

    function discardTicketDraftRows($root) {
        const context = getTicketModalContext($root);
        const $container = getTicketRowsContainer($root);
        const sessionRowsHtml = context.$modal.data('mpwemTicketSessionRowsHtml');
        if (!$container.length || typeof sessionRowsHtml !== 'string') {
            return;
        }

        if (context.$modal.data('mpwemTicketPersistDraftRows')) {
            context.$modal.removeData('mpwemTicketSessionRowsHtml').removeData('mpwemTicketPersistDraftRows');
            return;
        }

        $container.html(sessionRowsHtml);
        context.$modal.removeData('mpwemTicketSessionRowsHtml').removeData('mpwemTicketPersistDraftRows');
        enhanceSelects(context.$modalMount);
        enhanceDateFields(context.$modalMount);
        enhanceCustomCalendar(context.$modalMount);
        renderTicketSummary($root);
        syncTicketAdvancedColumns($root);
    }

    function decorateMinMaxSettings($root) {
        const $toggle = $root.find('input[name="mpwem_show_mm"]').first();
        if (!$toggle.length) {
            return;
        }

        const $card = $toggle.closest('._padding_bt');
        if (!$card.length || $card.data('mpwemMinMaxDecorated')) {
            return;
        }

        $card
            .addClass('mpwem-minmax-settings-card')
            .data('mpwemMinMaxDecorated', true);

        $card.children('._justify_between_align_center_wrap').first().addClass('mpwem-minmax-settings-card__head');
        $card.children('.des_info').first().addClass('mpwem-minmax-settings-card__intro');

        const $body = $card.children('[data-collapse="#mpwem_show_mm"]').first();
        $body.addClass('mpwem-minmax-settings-card__body');
        $body.children('div').first().addClass('mpwem-minmax-settings-card__type-row');
        $body.children('[data-collapse="#mep_mm_global"]').addClass('mpwem-minmax-settings-card__global-row');
        $body.children('[data-collapse="#mep_mm_ticket_type"]').addClass('mpwem-minmax-settings-card__ticket-row');
    }

    function ticketRowName($row) {
        const $nameInput = $row.find('[name="option_name_t[]"]').first();
        const inputValue = ($nameInput.val() || '').toString().trim();
        if (inputValue) {
            return inputValue;
        }

        const textValue = $row.find('.mpwem-ticket-card__locked-name').first().text().trim();
        return textValue || 'Untitled Ticket';
    }

    function ticketRowPrice($row) {
        const value = ($row.find('[name="option_price_t[]"]').first().val() || '').toString().trim();
        if (!value.length) {
            return 'Price not set';
        }

        if (value === '0') {
            return 'Free';
        }

        if (typeof window.mpwem_price_format === 'function' && !Number.isNaN(Number(value))) {
            return $('<div></div>').html(window.mpwem_price_format(Number(value))).text();
        }

        return value;
    }

    function ticketRowCapacity($row) {
        const value = ($row.find('[name="option_qty_t[]"]').first().val() || '').toString().trim();
        return value.length ? value : 'Unlimited';
    }

    function isTicketGlobalQtyEnabled($root) {
        const context = getTicketModalContext($root);
        const savedValue = context.$modal.data('mpwemTicketSavedGlobalQtyEnabled');

        if (typeof savedValue === 'boolean') {
            return savedValue;
        }

        const $toggle = context.$modalMount.find('input[name="enable_global_qty"]').first().add(
            $root.find('input[name="enable_global_qty"]').first()
        ).first();

        return $toggle.is(':checked');
    }

    function ticketGlobalTotalQty($root) {
        const context = getTicketModalContext($root);
        const savedValue = context.$modal.data('mpwemTicketSavedGlobalTotalQty');

        if (typeof savedValue === 'string') {
            return savedValue.length ? savedValue : '0';
        }

        const $input = context.$modalMount.find('input[name="mep_gq_total_seat"]').first().add(
            $root.find('input[name="mep_gq_total_seat"]').first()
        ).first();
        const value = ($input.val() || '').toString().trim();

        return value.length ? value : '0';
    }

    function ticketSummaryQty($root, $row) {
        if (isTicketGlobalQtyEnabled($root)) {
            return ticketGlobalTotalQty($root);
        }

        return ticketRowCapacity($row);
    }

    function ticketRowDescription($row) {
        const value = ($row.find('[name="option_details_t[]"]').first().val() || '').toString().trim();
        return value || 'No short description yet.';
    }

    function isEarlyBirdEnabled($root) {
        const context = getTicketModalContext($root);
        return context.$modalMount.find('input[name="mep_enable_early_bird_status"]').first().is(':checked');
    }

    function ticketRowEarlyBirdDates($row) {
        const startDate = ($row.find('[name="option_sale_start_date[]"]').first().val() || '').toString().trim();
        const startTime = ($row.find('[name="option_sale_start_time[]"]').first().val() || '').toString().trim();
        const endDate = ($row.find('[name="option_sale_end_date[]"]').first().val() || '').toString().trim();
        const endTime = ($row.find('[name="option_sale_end_time[]"]').first().val() || '').toString().trim();

        const startCombined = (startDate + ' ' + startTime).trim();
        const endCombined = (endDate + ' ' + endTime).trim();

        return { start: startCombined, end: endCombined };
    }

    function formatEarlyBirdDate(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr.replace(/-/g, '/'));
        if (isNaN(d.getTime())) return dateStr;
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        const year = d.getFullYear();
        return month + '/' + day + '/' + year;
    }

    /* Delegates to the shared highlightRow helper defined near the top of this module. */
    function highlightTicketRow($row) {
        highlightRow($row);
    }

    function renderTicketSummaryGlobalQty($root) {
        const context = getTicketModalContext($root);
        if (!context.$summary.length) {
            return;
        }

        let $gq = context.$summary.find('#mpwem_ticket_summary_gq').first();
        if (!$gq.length) {
            $gq = $('<div class="mpwem-ticket-summary__gq" id="mpwem_ticket_summary_gq"></div>');
            const $toolbar = context.$summary.find('.mpwem-ticket-summary__toolbar').first();
            if ($toolbar.length) {
                $toolbar.after($gq);
            } else {
                context.$summary.prepend($gq);
            }
        }

        const $realToggle = context.$modalMount.find('input[name="enable_global_qty"]').first();
        const $realTypeSelect = context.$modalMount.find('select[name="mep_gq_type"]').first();
        const $realTotal = context.$modalMount.find('input[name="mep_gq_total_seat"]').first();
        const $realReserve = context.$modalMount.find('input[name="mep_gq_total_resv_seat"]').first();

        $gq.empty();

        if (!$realToggle.length) {
            return;
        }

        const enabled = $realToggle.is(':checked');
        const gqType = ($realTypeSelect.val() || 'global').toString();
        const isFull = gqType !== 'date_wise';

        const $row = $('<div class="mpwem-ticket-summary__gq-row"></div>')
            .append(
                $('<div class="mpwem-ticket-summary__gq-copy"></div>')
                    .append($('<span class="mpwem-ticket-summary__gq-title"></span>').text('Share one quantity pool across all ticket types'))
                    .append($('<span class="mpwem-ticket-summary__gq-hint"></span>').text('Turn this on if ticket types should draw from a single shared seat count instead of their own.'))
            );

        const $toggleLabel = $('<label class="round_switch_label mpwem-ticket-summary__gq-switch"></label>');
        const $toggleInput = $('<input type="checkbox">').prop('checked', enabled);
        $toggleLabel.append($toggleInput).append('<span class="round_switch"></span>');
        $row.append($toggleLabel);
        $gq.append($row);

        $toggleInput.on('change', function() {
            $realToggle.prop('checked', $(this).is(':checked')).trigger('change');
        });

        if (!enabled) {
            return;
        }

        const $panel = $('<div class="mpwem-ticket-summary__gq-panel"></div>');

        const $pillField = $('<div class="mpwem-ticket-summary__gq-field"></div>')
            .append($('<label class="mpwem-ticket-summary__gq-label"></label>').text('Quantity applies'));
        const $pills = $('<div class="mpwem-ticket-summary__gq-pills"></div>');
        const $pillFull = $('<button type="button" class="mpwem-ticket-summary__gq-pill"></button>').text('Across the whole event').toggleClass('is-active', isFull);
        const $pillDate = $('<button type="button" class="mpwem-ticket-summary__gq-pill"></button>').text('Per scheduled date').toggleClass('is-active', !isFull);
        $pills.append($pillFull).append($pillDate);
        $pillField.append($pills);
        $panel.append($pillField);

        $pillFull.on('click', function() {
            $pillFull.addClass('is-active');
            $pillDate.removeClass('is-active');
            $realTypeSelect.val('global').trigger('change');
        });
        $pillDate.on('click', function() {
            $pillDate.addClass('is-active');
            $pillFull.removeClass('is-active');
            $realTypeSelect.val('date_wise').trigger('change');
        });

        if (isFull) {
            const $grid = $('<div class="mpwem-ticket-summary__gq-grid"></div>');

            const $totalGroup = $('<div class="mpwem-ticket-summary__gq-group"></div>')
                .append($('<label class="mpwem-ticket-summary__gq-label"></label>').text('Total Qty'));
            const $totalInput = $('<input type="number" placeholder="0">').val(($realTotal.val() || '').toString());
            $totalGroup.append($totalInput);

            const $reserveGroup = $('<div class="mpwem-ticket-summary__gq-group"></div>')
                .append($('<label class="mpwem-ticket-summary__gq-label"></label>').text('Reserve Qty'));
            const $reserveInput = $('<input type="number" placeholder="0">').val(($realReserve.val() || '').toString());
            $reserveGroup.append($reserveInput);

            $grid.append($totalGroup).append($reserveGroup);
            $panel.append($grid);

            $totalInput.on('input', function() {
                $realTotal.val($(this).val());
            }).on('change', function() {
                renderTicketSummary($root);
            });
            $reserveInput.on('input', function() {
                $realReserve.val($(this).val());
            }).on('change', function() {
                renderTicketSummary($root);
            });
        } else {
            const $notice = $('<div class="mpwem-ticket-summary__gq-notice"></div>')
                .append($('<span></span>').text('Quantity for each date is set in the Date & Time step.'))
                .append(
                    $('<button type="button" class="button"></button>')
                        .attr('data-mpwem-open-particular-date-modal', '')
                        .text('Open Date Table')
                );
            $panel.append($notice);
        }

        $gq.append($panel);
    }

    function ticketRowIsSold($row) {
        return $row.find('.mpwem_show_hide_button').length > 0;
    }

    const TICKET_SUMMARY_EDIT_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>';
    const TICKET_SUMMARY_DELETE_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>';
    const TICKET_SUMMARY_GRIP_ICON = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="9" cy="6" r="1.6"/><circle cx="15" cy="6" r="1.6"/><circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/><circle cx="9" cy="18" r="1.6"/><circle cx="15" cy="18" r="1.6"/></svg>';

    function buildTicketSummaryActions($root, index, isSold, isDisabled) {
        const $wrap = $('<div class="mpwem-ticket-summary__item-actions"></div>');

        if (isSold) {
            const $status = $('<button type="button" class="mpwem-ticket-summary__status-toggle"></button>')
                .append('<span class="mpwem-ticket-summary__status-dot" aria-hidden="true"></span>')
                .append($('<span></span>').text(isDisabled ? 'Inactive' : 'Active'))
                .toggleClass('is-inactive', isDisabled)
                .attr('title', 'Click to ' + (isDisabled ? 'activate' : 'deactivate') + ' this ticket type');

            $status.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const $liveRow = getTicketRows($root).eq(index);
                const $realToggle = $liveRow.find('.mpwem_show_hide_button').first();
                if ($realToggle.length) {
                    $realToggle.trigger('click');
                    storeSavedTicketSummarySnapshot($root);
                    renderTicketSummary($root);
                }
            });

            $wrap.append($status);
        } else {
            $wrap.append(
                $('<span class="mpwem-ticket-summary__status-badge"></span>')
                    .append('<span class="mpwem-ticket-summary__status-dot" aria-hidden="true"></span>')
                    .append($('<span></span>').text('Active'))
            );
        }

        const $editBtn = $('<button type="button" class="mpwem-ticket-summary__action-btn mpwem-ticket-summary__action-btn--edit" title="Edit" aria-label="Edit ticket type"></button>')
            .append(TICKET_SUMMARY_EDIT_ICON);
        $editBtn.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openInlineTicketEdit($root, index);
        });

        const $deleteBtn = $('<button type="button" class="mpwem-ticket-summary__action-btn mpwem-ticket-summary__action-btn--delete" title="Delete" aria-label="Delete ticket type"></button>')
            .append(TICKET_SUMMARY_DELETE_ICON);
        $deleteBtn.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const $liveRow = getTicketRows($root).eq(index);
            const $realRemove = $liveRow.find('.mpwem_item_remove').first();
            if ($realRemove.length) {
                $realRemove.trigger('click');
            }
        });

        const $sortHandle = $('<span class="mpwem-ticket-summary__sort-handle" title="Drag to reorder" aria-label="Drag to reorder" role="img"></span>')
            .append(TICKET_SUMMARY_GRIP_ICON);
        $sortHandle.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
        });

        $wrap.append($editBtn).append($deleteBtn).append($sortHandle);

        return $wrap;
    }

    function enableTicketSummarySort($root, context) {
        if (!context.$summaryList.length || typeof context.$summaryList.sortable !== 'function') {
            return;
        }

        if (context.$summaryList.data('ui-sortable')) {
            context.$summaryList.sortable('destroy');
        }

        context.$summaryList.sortable({
            items: '> .mpwem-ticket-summary__item',
            handle: '.mpwem-ticket-summary__sort-handle',
            axis: 'y',
            update: function() {
                const $container = getTicketRowsContainer($root);
                if (!$container.length) {
                    return;
                }

                const $liveRows = getTicketRows($root);
                context.$summaryList.find('.mpwem-ticket-summary__item').each(function() {
                    const oldIndex = parseInt($(this).attr('data-ticket-row-index'), 10);
                    if (!Number.isNaN(oldIndex) && $liveRows.eq(oldIndex).length) {
                        $container.append($liveRows.eq(oldIndex));
                    }
                });

                storeSavedTicketSummarySnapshot($root);
                renderTicketSummary($root);
            }
        });
    }

    function renderTicketSummary($root) {
        const context = getTicketModalContext($root);
        const regEnabled = isRegistrationEnabled($root);
        const $rows = getSavedTicketSummaryRows($root);
        if (!context.$summaryList.length) {
            return;
        }

        // The inline add/edit card lives inside the list, positioned right
        // before the "Add New Ticket Type" button - detach it (not
        // remove/empty) before clearing the list so an open card survives
        // the rebuild, then reinsert it in the same spot below.
        const $openAddCard = context.$summaryList.find('#mpwem_ticket_summary_add').detach();

        context.$summaryList.empty();

        getPanel($root, '#mpwem_ticket_pricing_settings').find('#mpwem_ticket_summary').toggle(!!regEnabled);

        if (!regEnabled) {
            closeTicketModal($root);
            if ($openAddCard.length) {
                context.$summaryList.append($openAddCard);
            }
            renderLiveTicketPreview($root, false, false, $());
            return;
        }

        const earlyBirdEnabled = isEarlyBirdEnabled($root);

        getPanel($root, '#mpwem_ticket_pricing_settings').find('#mpwem_ticket_summary').toggleClass('is-earlybird', earlyBirdEnabled);

        renderTicketSummaryGlobalQty($root);

        if (!$rows.length) {
            // Header row only makes sense once a ticket exists under it -
            // same pattern as renderParticularDateSummary's empty state.
            context.$summaryList.append(
                $('<div class="mpwem-ticket-summary__empty mpwem-ticket-summary__empty--centered"></div>')
                    .append(
                        $('<span class="mpwem-ticket-summary__empty-icon" aria-hidden="true"></span>')
                            .html('<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4Z"></path><path d="M13 5v2M13 17v2M13 10.5v3"></path></svg>')
                    )
                    .append($('<h4></h4>').text('No ticket types yet'))
                    .append(
                        $('<button type="button" class="button button-primary"></button>')
                            .attr('data-mpwem-ticket-modal-open', 'new')
                            .html('<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg> Create First Ticket')
                    )
            );
            if ($openAddCard.length) {
                context.$summaryList.append($openAddCard);
            }
            renderLiveTicketPreview($root, regEnabled, earlyBirdEnabled, $());
            return;
        }

        const $headerRow = $('<div class="mpwem-ticket-summary__header"></div>')
            .append($('<span class="mpwem-ticket-summary__header-ticket"></span>').text('Ticket Type'))
            .append($('<span class="mpwem-ticket-summary__header-price"></span>').text('Price'))
            .append($('<span class="mpwem-ticket-summary__header-capacity"></span>').text('Total Qty'));

        if (earlyBirdEnabled) {
            $headerRow.append($('<span class="mpwem-ticket-summary__header-earlybird"></span>').text('Sale Period'));
        }

        $headerRow.append($('<span class="mpwem-ticket-summary__header-action"></span>').text('Action'));

        context.$summaryList.append($headerRow);

        $rows.each(function(index) {
            const $row = $(this);
            const name = ticketRowName($row);
            const price = ticketRowPrice($row);
            const capacity = ticketSummaryQty($root, $row);
            const isDisabled = $row.hasClass('disable_row');
            const description = ($row.find('[name="option_details_t[]"]').first().val() || '').toString().trim();

            const $itemMain = $('<div class="mpwem-ticket-summary__item-main"></div>')
                .append(
                    $('<div class="mpwem-ticket-summary__item-head"></div>')
                        .append($('<h4></h4>').text(name))
                );

            if (description) {
                $itemMain.append($('<p class="mpwem-ticket-summary__item-desc"></p>').text(description));
            }

            const $item = $('<article class="mpwem-ticket-summary__item"></article>')
                .attr('data-ticket-row-index', index)
                .toggleClass('is-disabled', isDisabled)
                .append($itemMain)
                .append(
                    $('<div class="mpwem-ticket-summary__meta"></div>')
                        .append($('<span class="mpwem-ticket-summary__price"></span>').text(price))
                )
                .append(
                    $('<div class="mpwem-ticket-summary__meta mpwem-ticket-summary__meta--capacity"></div>')
                        .append($('<span class="mpwem-ticket-summary__capacity"></span>').text(capacity))
                );

            if (earlyBirdEnabled) {
                const dates = ticketRowEarlyBirdDates($row);
                const startStr = formatEarlyBirdDate(dates.start);
                const endStr = formatEarlyBirdDate(dates.end);
                let periodText = '';
                if (startStr && endStr) {
                    periodText = startStr + ' - ' + endStr;
                } else if (startStr) {
                    periodText = 'From ' + startStr;
                } else if (endStr) {
                    periodText = 'Until ' + endStr;
                } else {
                    periodText = 'Not set';
                }

                $item.append(
                    $('<div class="mpwem-ticket-summary__meta mpwem-ticket-summary__meta--earlybird"></div>')
                        .append($('<span class="mpwem-ticket-summary__earlybird"></span>').text(periodText))
                );
            }

            $item.append(buildTicketSummaryActions($root, index, ticketRowIsSold($row), isDisabled));

            context.$summaryList.append($item);
        });

        // "Add New Ticket Type" renders after every row, same as
        // "Add Schedule"/"Add Particular Date" on the Date step.
        const $addTicketBtn = $('<button type="button" class="button button-primary mpwem-date-primary-fields__add"></button>')
            .attr('data-mpwem-ticket-modal-open', 'new')
            .text('Add New Ticket Type');
        context.$summaryList.append($addTicketBtn);

        // The open add/edit card, if any, goes right before that button -
        // so it shows above "Add New Ticket Type" instead of appearing
        // further down, after the whole summary.
        if ($openAddCard.length) {
            $openAddCard.insertBefore($addTicketBtn);
        }

        enableTicketSummarySort($root, context);
        renderLiveTicketPreview($root, regEnabled, earlyBirdEnabled, $rows);
    }

    function formatLiveTicketPreviewPrice(num) {
        if (typeof window.mpwem_price_format === 'function' && !Number.isNaN(num)) {
            return $('<div></div>').html(window.mpwem_price_format(num)).text();
        }
        return '$' + num.toFixed(2);
    }

    // Qty chosen per item in the preview persists across re-renders (every
    // ticket/extra-service edit rebuilds the whole card) so a click doesn't
    // get wiped out by the very re-render it triggers. Keyed by a
    // type-prefixed name since ticket and extra-service names are separate
    // namespaces on the real frontend too.
    function getLiveTicketPreviewQtyState($root) {
        let state = $root.data('mpwemLivePreviewQty');
        if (!state) {
            state = {};
            $root.data('mpwemLivePreviewQty', state);
        }
        return state;
    }

    function buildLiveTicketPreviewItem(options) {
        const stockState = options.stockState;
        const max = options.max;
        const qty = options.qty;

        const $item = $('<div class="mpwem-live-ticket-preview__item"></div>')
            .attr('data-mpwem-preview-key', options.key)
            .attr('data-mpwem-preview-max', max === Infinity ? '' : max)
            .attr('data-mpwem-preview-price', Number.isNaN(options.priceRaw) ? '' : options.priceRaw)
            .toggleClass('is-disabled', stockState === 'soldout');

        if (stockState === 'soldout') {
            $item.append(
                $('<div class="mpwem-live-ticket-preview__ribbon"></div>')
                    .append($('<span class="mpwem-live-ticket-preview__ribbon-badge is-soldout"></span>').text('Sold Out'))
            );
        } else if (stockState === 'low') {
            $item.append(
                $('<div class="mpwem-live-ticket-preview__ribbon"></div>')
                    .append($('<span class="mpwem-live-ticket-preview__ribbon-badge"></span>').text('Only ' + options.capacityNum + ' left'))
            );
        }

        const $nameRow = $('<div class="mpwem-live-ticket-preview__name"></div>').text(options.name);
        if (options.earlyBird) {
            $nameRow.append($('<span class="mpwem-live-ticket-preview__badge--earlybird"></span>').text('Early Bird'));
        }

        const $info = $('<div class="mpwem-live-ticket-preview__info"></div>').append($nameRow);
        if (options.description) {
            $info.append($('<p class="mpwem-live-ticket-preview__desc"></p>').text(options.description));
        }

        $item.append(
            $('<div class="mpwem-live-ticket-preview__top"></div>')
                .append($info)
                .append($('<div class="mpwem-live-ticket-preview__price"></div>').text(options.priceText))
        );

        const $bottom = $('<div class="mpwem-live-ticket-preview__bottom"></div>');

        if (stockState === 'available' || stockState === 'low') {
            $bottom.append(
                $('<div class="mpwem-live-ticket-preview__availability"></div>')
                    .toggleClass('is-low', stockState === 'low')
                    .append('<span class="mpwem-live-ticket-preview__availability-dot" aria-hidden="true"></span>')
                    .append(document.createTextNode(options.capacityNum + (options.unit || ' tickets available')))
            );
        } else if (stockState === 'soldout') {
            $bottom.append(
                $('<div class="mpwem-live-ticket-preview__availability"></div>')
                    .css('color', '#b91c1c')
                    .text('Sold out')
            );
        }

        if (options.showQty) {
            $bottom.append(
                $('<div class="mpwem-live-ticket-preview__qty"></div>')
                    .append($('<button type="button" class="mpwem-live-ticket-preview__qty-btn mpwem-live-ticket-preview__qty-btn--dec"></button>').attr('aria-label', 'Decrease quantity').toggleClass('is-disabled', stockState === 'soldout' || qty <= 0).text('−'))
                    .append($('<span class="mpwem-live-ticket-preview__qty-val"></span>').text(qty))
                    .append($('<button type="button" class="mpwem-live-ticket-preview__qty-btn mpwem-live-ticket-preview__qty-btn--inc"></button>').attr('aria-label', 'Increase quantity').toggleClass('is-disabled', stockState === 'soldout' || qty >= max).text('+'))
            );
        }

        if ($bottom.children().length) {
            $item.append($bottom);
        }
        return $item;
    }

    function renderLiveTicketPreview($root, regEnabled, earlyBirdEnabled, $rows) {
        // Shown on the Ticket & Pricing, Date & Time and Advanced steps -
        // every instance shares the .mpwem-live-ticket-preview class (ids
        // can't repeat per page), so build the content once and clone it
        // into each card instead of re-deriving it per step.
        const $cards = $root.find('.mpwem-live-ticket-preview');
        if (!$cards.length) {
            return;
        }

        $cards.toggle(!!regEnabled);
        if (!regEnabled) {
            return;
        }

        const qtyState = getLiveTicketPreviewQtyState($root);

        // Mirrors templates/layout/ticket_type.php's own enable check, so a
        // ticket hidden from the frontend is hidden here too.
        const $visibleRows = $rows.filter(function() {
            return !$(this).hasClass('disable_row');
        });

        const items = [];
        let subtotal = 0;
        let subtotalCount = 0;
        let totalTicketQty = 0;

        $visibleRows.each(function() {
            const $row = $(this);
            const name = ticketRowName($row);
            const priceText = ticketRowPrice($row);
            const priceRaw = parseFloat(($row.find('[name="option_price_t[]"]').first().val() || '').toString().trim());
            const description = ($row.find('[name="option_details_t[]"]').first().val() || '').toString().trim();

            const capacityRaw = ($row.find('[name="option_qty_t[]"]').first().val() || '').toString().trim();
            const capacityNum = isTicketGlobalQtyEnabled($root)
                ? parseInt((ticketGlobalTotalQty($root) || '').toString().trim(), 10)
                : (capacityRaw === '' ? NaN : parseInt(capacityRaw, 10));

            let stockState = 'unlimited';
            if (!Number.isNaN(capacityNum)) {
                if (capacityNum <= 0) {
                    stockState = 'soldout';
                } else if (capacityNum <= 10) {
                    stockState = 'low';
                } else {
                    stockState = 'available';
                }
            }

            const max = stockState === 'soldout' ? 0 : (Number.isNaN(capacityNum) ? Infinity : capacityNum);
            const key = 'ticket:' + name;

            let qty;
            if (Object.prototype.hasOwnProperty.call(qtyState, key)) {
                qty = Math.max(0, Math.min(max, qtyState[key]));
            } else {
                const defaultQtyRaw = ($row.find('[name="option_default_qty_t[]"]').first().val() || '').toString().trim();
                qty = stockState === 'soldout' ? 0 : Math.max(0, Math.min(max, parseInt(defaultQtyRaw, 10) || 0));
            }
            qtyState[key] = qty;

            const dates = earlyBirdEnabled ? ticketRowEarlyBirdDates($row) : { start: '', end: '' };

            items.push(buildLiveTicketPreviewItem({
                key: key,
                name: name,
                priceText: priceText,
                priceRaw: priceRaw,
                description: description,
                stockState: stockState,
                capacityNum: capacityNum,
                max: max,
                qty: qty,
                earlyBird: !!(dates.start || dates.end),
                showQty: true
            }));

            totalTicketQty += qty;

            if (qty > 0 && !Number.isNaN(priceRaw)) {
                subtotal += priceRaw * qty;
                subtotalCount += qty;
            }
        });

        // Extra Services only show once at least one ticket is selected -
        // same rule mpwem_price_calculation() uses on the real frontend
        // (slideDown/slideUp .mpwem_ex_service based on total_qty).
        const extraItems = [];
        if (totalTicketQty > 0) {
            getSavedExtraServiceSummaryRows($root).each(function() {
                const $row = $(this);
                const name = extraServiceRowName($row);
                const priceText = extraServiceRowPrice($row);
                const priceRaw = parseFloat(($row.find('[name="option_price[]"]').first().val() || '').toString().trim());

                const capacityRaw = ($row.find('[name="option_qty[]"]').first().val() || '').toString().trim();
                const capacityNum = capacityRaw === '' ? NaN : parseInt(capacityRaw, 10);

                // Matches extra_service.php's own `$ticket_qty > 0` gate -
                // an add-on explicitly capped at 0 never shows at all.
                if (!Number.isNaN(capacityNum) && capacityNum <= 0) {
                    return;
                }

                let stockState = 'unlimited';
                if (!Number.isNaN(capacityNum)) {
                    stockState = capacityNum <= 10 ? 'low' : 'available';
                }

                const max = Number.isNaN(capacityNum) ? Infinity : capacityNum;
                const key = 'extra:' + name;

                let qty;
                if (Object.prototype.hasOwnProperty.call(qtyState, key)) {
                    qty = Math.max(0, Math.min(max, qtyState[key]));
                } else {
                    qty = 0;
                }
                qtyState[key] = qty;

                extraItems.push(buildLiveTicketPreviewItem({
                    key: key,
                    name: name,
                    priceText: priceText,
                    priceRaw: priceRaw,
                    description: '',
                    stockState: stockState,
                    capacityNum: capacityNum,
                    max: max,
                    qty: qty,
                    earlyBird: false,
                    showQty: true,
                    unit: ' available'
                }));

                if (qty > 0 && !Number.isNaN(priceRaw)) {
                    subtotal += priceRaw * qty;
                    subtotalCount += qty;
                }
            });
        }

        const footerNodes = [];
        if (subtotalCount > 0) {
            footerNodes.push(
                $('<div class="mpwem-live-ticket-preview__footer-row"></div>')
                    .append($('<span class="mpwem-live-ticket-preview__footer-label"></span>').text('Subtotal · ' + subtotalCount + ' item' + (subtotalCount === 1 ? '' : 's')))
                    .append($('<span class="mpwem-live-ticket-preview__footer-amount"></span>').text(formatLiveTicketPreviewPrice(subtotal)))
            );
            footerNodes.push(
                $('<p class="mpwem-live-ticket-preview__footer-note"></p>').text('Click +/- to try different quantities.')
            );
        }

        // Matches add_to_cart.php's own button exactly (label, icon, and
        // the disabled-until-a-ticket-is-selected rule mpwem_price_calculation()
        // enforces on the real frontend).
        footerNodes.push(
            $('<button type="button" class="mpwem-live-ticket-preview__register"></button>')
                .prop('disabled', totalTicketQty <= 0)
                .append('<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>')
                .append($('<span></span>').text('Register For This Event'))
        );

        $cards.each(function() {
            const $body = $(this).find('.mpwem-live-ticket-preview__body').first();
            const $footer = $(this).find('.mpwem-live-ticket-preview__footer').first();

            $body.empty();
            $footer.empty();

            if (!items.length) {
                $body.append(
                    $('<div class="mpwem-live-ticket-preview__empty"></div>')
                        .text('Add a ticket type to see how it looks on your event page.')
                );
                return;
            }

            items.forEach(function($item) {
                $body.append($item.clone(true));
            });

            if (extraItems.length) {
                const $extras = $('<div class="mpwem-live-ticket-preview__extras"></div>')
                    .append($('<div class="mpwem-live-ticket-preview__extras-head"></div>').text('Extra Services'));
                extraItems.forEach(function($item) {
                    $extras.append($item.clone(true));
                });
                $body.append($extras);
            }

            footerNodes.forEach(function($node) {
                $footer.append($node.clone(true));
            });
        });
    }

    function bindLiveTicketPreviewQtyButtons() {
        if (window.mpwemLiveTicketPreviewQtyBound) {
            return;
        }

        $(document).on('click', '.mpwem-live-ticket-preview__qty-btn', function(e) {
            e.preventDefault();
            const $btn = $(this);
            if ($btn.hasClass('is-disabled')) {
                return;
            }

            const $item = $btn.closest('.mpwem-live-ticket-preview__item');
            const $root = $btn.closest('.mpwem-event-wizard').first();
            const key = $item.attr('data-mpwem-preview-key');
            if (!key || !$root.length) {
                return;
            }

            const maxAttr = $item.attr('data-mpwem-preview-max');
            const max = maxAttr === '' ? Infinity : (parseInt(maxAttr, 10) || 0);
            const delta = $btn.hasClass('mpwem-live-ticket-preview__qty-btn--inc') ? 1 : -1;

            const qtyState = getLiveTicketPreviewQtyState($root);
            const current = qtyState[key] || 0;
            const next = Math.max(0, Math.min(max, current + delta));
            if (next === current) {
                return;
            }

            qtyState[key] = next;

            // Full re-render keeps every preview card instance (Ticket &
            // Pricing / Date & Time / Advanced), the subtotal, and the
            // Extra Services visibility all in sync with the new quantity.
            renderTicketSummary($root);
        });

        window.mpwemLiveTicketPreviewQtyBound = true;
    }

    function getLiveTicketPreviewDateLabel($root, type) {
        const activeType = type || getActiveDateModalType($root);

        if (activeType === 'no') {
            const context = getParticularDateModalContext($root);
            const $section = context.$modalMount.find('[data-collapse="#mep_normal_event"]').first();
            const $scope = $section.length ? $section : context.$modalMount;
            const startDate = ($scope.find('input[name="event_start_date_normal"]').first().val() || '').toString().trim();
            const startTime = ($scope.find('input[name="event_start_time_normal"]').first().val() || '').toString().trim();
            return buildScheduleDateTimeLabel(startDate, startTime) || 'No date set yet';
        }

        // Particular Dates with just one date configured: plain label, same
        // as everywhere else. Two or more goes through the dropdown in
        // updateLiveTicketPreviewDate instead (see getLiveTicketPreviewDateOptions).
        const $rows = getParticularDateRows($root);
        if (!$rows.length) {
            return 'No date set yet';
        }

        const $first = $rows.first();
        const startDate = ($first.find('input[name="event_more_start_date[]"]').first().val() || '').toString().trim();
        const startTime = ($first.find('input[name="event_more_start_time[]"]').first().val() || '').toString().trim();
        return buildScheduleDateTimeLabel(startDate, startTime) || 'No date set yet';
    }

    // Particular Dates: one option per configured date, in the same order
    // they're listed in the Date & Time step. Unlike Repeated Event's
    // computed occurrences, these are exact rows the admin typed in, so a
    // flat dropdown (not a calendar) is the natural picker. Like
    // renderParticularDateSummary's own 'yes' branch, the FIRST date lives
    // in its own primary fields outside the rows table - missing it here
    // was why a 2-date event only showed 1 option (the row, not the
    // primary).
    function getLiveTicketPreviewDateOptions($root) {
        const context = getParticularDateModalContext($root);
        const $modalMount = context.$modalMount.length ? context.$modalMount : getSavedDateSummaryMount($root);
        const options = [];

        const primaryDate = ($modalMount.find('input[name="event_start_date"]').first().val() || '').toString().trim();
        const primaryTime = ($modalMount.find('input[name="event_start_time"]').first().val() || '').toString().trim();
        if (primaryDate) {
            options.push({
                iso: primaryDate,
                label: buildScheduleDateTimeLabel(primaryDate, primaryTime) || primaryDate
            });
        }

        getParticularDateRows($root).each(function() {
            const $row = $(this);
            const startDate = ($row.find('input[name="event_more_start_date[]"]').first().val() || '').toString().trim();
            const startTime = ($row.find('input[name="event_more_start_time[]"]').first().val() || '').toString().trim();
            if (!startDate) {
                return;
            }
            options.push({
                iso: startDate,
                label: buildScheduleDateTimeLabel(startDate, startTime) || startDate
            });
        });
        return options;
    }

    // Repeated Event has many selectable occurrences (like the real
    // frontend's own date picker for a recurring event), not one fixed
    // date - so the preview row offers a dropdown instead of static text.
    // Mirrors the server's own rules: step by the repeat interval between
    // Start/End, skipping any checked off-day weekday or off-date.
    function computeRepeatOccurrenceDates($root, limit) {
        const $repeatSection = $root.find('[data-collapse="#mep_everyday_event"]').first();
        if (!$repeatSection.length) {
            return [];
        }

        const startDate = parseIsoDate(($repeatSection.find('input[name="event_start_date_everyday"]').first().val() || '').toString().trim());
        if (!startDate) {
            return [];
        }

        const endDate = parseIsoDate(($repeatSection.find('input[name="event_end_date_everyday"]').first().val() || '').toString().trim());
        const interval = Math.max(1, parseInt(($repeatSection.find('input[name="mep_repeated_periods"]').first().val() || '').toString().trim(), 10) || 1);

        const offDays = {};
        $repeatSection.find('.mpwem-offday-option input[type="checkbox"]:checked').each(function() {
            const dayIndex = parseInt(($(this).attr('data-checked') || '').toString().trim(), 10);
            if (!Number.isNaN(dayIndex)) {
                offDays[dayIndex] = true;
            }
        });

        const offDates = {};
        $repeatSection.find('input[name="mep_ticket_off_dates[]"]').each(function() {
            const value = ($(this).val() || '').toString().trim();
            if (value) {
                offDates[value] = true;
            }
        });

        // No end date configured yet: still offer a reasonable run of
        // upcoming occurrences instead of nothing.
        const hardStop = endDate || new Date(startDate.getFullYear() + 1, startDate.getMonth(), startDate.getDate());

        const occurrences = [];
        const cursor = new Date(startDate.getTime());
        let guard = 0;

        while (cursor <= hardStop && occurrences.length < limit && guard < 3000) {
            guard++;
            if (!offDays[cursor.getDay()] && !offDates[formatIsoDate(cursor)]) {
                occurrences.push(new Date(cursor.getTime()));
            }
            cursor.setDate(cursor.getDate() + interval);
        }

        return occurrences;
    }

    function updateLiveTicketPreviewDate($root, type) {
        const $labels = $root.find('.mpwem-live-ticket-preview__date-label');
        const $selects = $root.find('.mpwem-live-ticket-preview__date-select');
        const $pickers = $root.find('.mpwem-live-ticket-preview__date-picker');
        if (!$labels.length && !$selects.length && !$pickers.length) {
            return;
        }

        const activeType = type || getActiveDateModalType($root);

        if (activeType === 'yes') {
            const options = getLiveTicketPreviewDateOptions($root);

            $labels.hide();
            $pickers.hide();
            $selects.show();

            $selects.each(function() {
                const $select = $(this);
                const previousValue = $select.val();
                $select.empty();

                if (!options.length) {
                    $select.append($('<option></option>').attr('value', '').text('No date set yet'));
                    $select.val('');
                    return;
                }

                options.forEach(function(option) {
                    $select.append($('<option></option>').attr('value', option.iso).text(option.label));
                });

                const stillValid = previousValue && options.some(function(option) {
                    return option.iso === previousValue;
                });
                $select.val(stillValid ? previousValue : options[0].iso);
            });
            return;
        }

        if (activeType !== 'everyday') {
            $selects.hide();
            $pickers.hide();
            $labels.show().text(getLiveTicketPreviewDateLabel($root, activeType));
            return;
        }

        const occurrences = computeRepeatOccurrenceDates($root, 60);

        if (!occurrences.length) {
            $selects.hide();
            $pickers.hide();
            $labels.show().text('No date set yet');
            return;
        }

        $labels.hide();
        $selects.hide();
        $pickers.show();

        const isoList = occurrences.map(formatIsoDate);

        $pickers.each(function() {
            const $hidden = $(this).find('input[type="hidden"]').first();
            const $visible = $(this).find('.mpwem-live-ticket-preview__date-input').first();
            const previousValue = $hidden.val();

            const stillValid = previousValue && isoList.indexOf(previousValue) !== -1;
            const chosenIso = stillValid ? previousValue : isoList[0];
            const chosenDate = parseIsoDate(chosenIso);

            // The popup calendar reads its allowed-dates constraint off the
            // visible input (see openCustomCalendar), so every re-render
            // refreshes it here even while the calendar itself is closed.
            $visible.data('mpwemAllowedDates', isoList);

            $hidden.val(chosenIso);
            $visible.val(chosenDate ? formatVisibleDate(chosenDate) : '');
        });
    }

    function getInlineTicketAddCard($root) {
        const context = getTicketModalContext($root);
        if (!context.$summary.length) {
            return $();
        }

        let $card = $root.find('#mpwem_ticket_summary_add').first();
        if (!$card.length) {
            $card = $('<div class="mpwem-ticket-add" id="mpwem_ticket_summary_add"></div>');
            const $addBtn = context.$summaryList.find('.mpwem-date-primary-fields__add[data-mpwem-ticket-modal-open="new"]').last();
            if ($addBtn.length) {
                $card.insertBefore($addBtn);
            } else {
                context.$summaryList.append($card);
            }
        }

        return $card;
    }

    const TICKET_ROW_FIELD_NAMES = [
        'option_name_t[]',
        'option_price_t[]',
        'option_qty_t[]',
        'option_qty_t_type[]',
        'option_details_t[]',
        'option_default_qty_t[]',
        'option_rsv_t[]',
        'option_ticket_mode_t[]',
        'option_sale_start_date[]',
        'option_sale_start_time[]',
        'option_sale_end_date[]',
        'option_sale_end_time[]'
    ];

    function syncRealFieldValue($el, val) {
        $el.val(val);
        if ($el.is('select')) {
            $el.find('option').each(function() {
                if (this.value === val) {
                    this.setAttribute('selected', 'selected');
                } else {
                    this.removeAttribute('selected');
                }
            });
        } else {
            $el.attr('value', val);
        }
    }

    function captureTicketRowFieldValues($row) {
        const values = {};
        TICKET_ROW_FIELD_NAMES.forEach(function(name) {
            const $field = $row.find('[name="' + name + '"]').first();
            if ($field.length) {
                values[name] = ($field.val() || '').toString();
            }
        });
        return values;
    }

    function closeInlineTicketAdd($root, opts) {
        opts = opts || {};
        const $card = getInlineTicketAddCard($root);
        const $row = $card.data('mpwemInlineAddRow');
        const mode = $card.data('mpwemInlineAddMode') || 'add';
        const original = $card.data('mpwemInlineAddOriginal');

        if (!opts.save) {
            if (mode === 'edit' && $row && $row.length && original) {
                Object.keys(original).forEach(function(name) {
                    const $field = $row.find('[name="' + name + '"]').first();
                    if ($field.length) {
                        syncRealFieldValue($field, original[name]);
                    }
                });
            } else if ($row && $row.length && $row.closest('body').length) {
                $row.remove();
            }
        }

        // Remember which row to focus after the summary re-renders, before
        // clearing the card's data - a newly saved ticket gets scrolled to
        // and highlighted in its new spot in the list.
        const focusRowIndex = (opts.save && mode === 'add' && $row && $row.length)
            ? getTicketRows($root).index($row)
            : -1;

        $card.removeData('mpwemInlineAddRow mpwemInlineAddMode mpwemInlineAddOriginal').removeClass('is-open').empty();

        storeSavedTicketSummarySnapshot($root);
        renderTicketSummary($root);

        if (focusRowIndex > -1) {
            const context = getTicketModalContext($root);
            const $newItem = context.$summaryList.find('.mpwem-ticket-summary__item[data-ticket-row-index="' + focusRowIndex + '"]').first();
            if ($newItem.length) {
                scrollToViewportCenter($newItem);
                flashAttention($newItem);
            }
        }
    }

    function renderInlineTicketAddForm($root, $row, opts) {
        opts = opts || {};
        const isEdit = opts.mode === 'edit';
        const context = getTicketModalContext($root);
        const $card = getInlineTicketAddCard($root);
        $card.empty().addClass('is-open');

        const isHybrid = ($root.find('input[name="mep_event_type"]').val() || 'offline') === 'hybrid';
        const globalQtyOn = context.$modalMount.find('input[name="enable_global_qty"]').first().is(':checked');

        const $nameInput = $row.find('[name="option_name_t[]"]').first();
        const $priceInput = $row.find('[name="option_price_t[]"]').first();
        const $qtyInput = $row.find('[name="option_qty_t[]"]').first();
        const $qtyTypeSelect = $row.find('[name="option_qty_t_type[]"]').first();
        const $descInput = $row.find('[name="option_details_t[]"]').first();
        const $defaultQtyInput = $row.find('[name="option_default_qty_t[]"]').first();
        const $reserveQtyInput = $row.find('[name="option_rsv_t[]"]').first();
        const $modeSelect = $row.find('[name="option_ticket_mode_t[]"]').first();
        const $saleStartDate = $row.find('[name="option_sale_start_date[]"]').first();
        const $saleStartTime = $row.find('[name="option_sale_start_time[]"]').first();
        const $saleEndDate = $row.find('[name="option_sale_end_date[]"]').first();
        const $saleEndTime = $row.find('[name="option_sale_end_time[]"]').first();

        const headIcon = isEdit
            ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>'
            : '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>';

        const $headTitleRow = $('<div class="mpwem-ticket-add__head-title"></div>')
            .append($('<h3></h3>').text(isEdit ? 'Edit Ticket Type' : 'Add New Ticket Type'));

        const existingName = ($nameInput.val() || '').toString().trim();
        if (isEdit && existingName) {
            $headTitleRow.append($('<span class="mpwem-ticket-add__head-chip"></span>').text(existingName));
        }

        $card.append(
            $('<div class="mpwem-ticket-add__head"></div>')
                .append($('<span class="mpwem-ticket-add__head-icon"></span>').html(headIcon))
                .append(
                    $('<div class="mpwem-ticket-add__head-copy"></div>')
                        .append($headTitleRow)
                        .append($('<p></p>').text(isEdit ? 'Update the essentials, or open More options for everything else.' : 'The essentials are up front. Everything else is one click away.'))
                )
        );

        const $body = $('<div class="mpwem-ticket-add__body"></div>');

        const $core = $('<div class="mpwem-ticket-add__core"></div>').toggleClass('mpwem-ticket-add__core--gq', globalQtyOn);

        const $nameProxy = $('<input type="text" placeholder="e.g. General Admission">').val(($nameInput.val() || '').toString());
        $core.append(
            $('<div class="mpwem-ticket-add__group"></div>')
                .append($('<label></label>').text('Ticket Name'))
                .append($nameProxy)
        );

        const $priceProxy = $('<input type="text" inputmode="decimal" placeholder="0.00">').val(($priceInput.val() || '').toString());
        $core.append(
            $('<div class="mpwem-ticket-add__group"></div>')
                .append($('<label></label>').text('Price'))
                .append($priceProxy)
        );

        let $qtyProxy = null;
        if (!globalQtyOn) {
            $qtyProxy = $('<input type="number" placeholder="100">').val(($qtyInput.val() || '').toString());
            $core.append(
                $('<div class="mpwem-ticket-add__group"></div>')
                    .append($('<label></label>').text('Capacity'))
                    .append($qtyProxy)
            );
        }

        const qtyTypeVal = ($qtyTypeSelect.val() || 'inputbox').toString();

        const $qtyTypeProxy = $('<select class="mpwem-ticket-add__select"></select>')
            .append($('<option value="inputbox"></option>').text('Input Box'))
            .append($('<option value="dropdown"></option>').text('Dropdown List'))
            .val(qtyTypeVal);

        // "Quantity shown as" lands in the core grid as its own column,
        // aligned with Ticket Name / Price / Capacity, instead of its own
        // full-width row below.
        $core.append(
            $('<div class="mpwem-ticket-add__group"></div>')
                .append($('<label></label>').text('Quantity shown as'))
                .append($qtyTypeProxy)
        );

        $body.append($core);

        if (globalQtyOn) {
            $body.append(
                $('<p class="mpwem-ticket-add__hint"></p>').text('Capacity is set by the shared quantity pool above, not per ticket type.')
            );
        }

        $qtyTypeProxy.on('change', function() {
            const val = $(this).val();
            syncRealFieldValue($qtyTypeSelect, val);
            $qtyTypeSelect.trigger('change');
        });

        const $moreToggle = $('<button type="button" class="mpwem-ticket-add__more-toggle" aria-expanded="false"></button>')
            .append(
                $('<span class="mpwem-ticket-add__more-icon" aria-hidden="true"></span>')
                    .html('<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="7" x2="20" y2="7"/><circle cx="9" cy="7" r="2.3"/><line x1="4" y1="17" x2="20" y2="17"/><circle cx="16" cy="17" r="2.3"/></svg>')
            )
            .append(
                $('<span class="mpwem-ticket-add__more-text"></span>')
                    .append($('<span class="mpwem-ticket-add__more-label"></span>').text('More options'))
                    .append($('<span class="mpwem-ticket-add__more-sub"></span>').text('Description, reserved & default quantity, ticket mode, sale period'))
            )
            .append(
                $('<span class="mpwem-ticket-add__chev"></span>')
                    .html('<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>')
            );
        $body.append($moreToggle);

        const $more = $('<div class="mpwem-ticket-add__more"></div>').hide();

        const $descProxy = $('<textarea rows="2" placeholder="Shown to attendees on the checkout page"></textarea>').val(($descInput.val() || '').toString());
        $more.append(
            $('<div class="mpwem-ticket-add__group"></div>')
                .append($('<label></label>').text('Description'))
                .append($descProxy)
        );

        const $defaultProxy = $('<input type="number" placeholder="1">').val(($defaultQtyInput.val() || '').toString());
        const $reserveProxy = $('<input type="number" placeholder="0">').val(($reserveQtyInput.val() || '').toString());
        $more.append(
            $('<div class="mpwem-ticket-add__grid"></div>')
                .append(
                    $('<div class="mpwem-ticket-add__group"></div>')
                        .append($('<label></label>').text('Default Qty'))
                        .append($defaultProxy)
                )
                .append(
                    $('<div class="mpwem-ticket-add__group"></div>')
                        .append($('<label></label>').text('Reserve Qty'))
                        .append($reserveProxy)
                )
        );

        let $modeProxy = null;
        if (isHybrid) {
            const modeVal = ($modeSelect.val() || 'inperson').toString();
            $modeProxy = $('<select></select>')
                .append($('<option value="inperson"></option>').text('In Person (Physical)'))
                .append($('<option value="online"></option>').text('Online Event'))
                .val(modeVal);
            $more.append(
                $('<div class="mpwem-ticket-add__group"></div>')
                    .append($('<label></label>').text('Ticket Mode'))
                    .append($modeProxy)
            );

            $modeProxy.on('change', function() {
                syncRealFieldValue($modeSelect, $(this).val());
                $modeSelect.trigger('change');
            });
        }

        const $saleStartDateProxy = $('<input type="date">').val(($saleStartDate.val() || '').toString());
        const $saleStartTimeProxy = $('<input type="time">').val(($saleStartTime.val() || '').toString());
        const $saleEndDateProxy = $('<input type="date">').val(($saleEndDate.val() || '').toString());
        const $saleEndTimeProxy = $('<input type="time">').val(($saleEndTime.val() || '').toString());

        $saleStartDateProxy.addClass('mpwem-ticket-add__date-part');
        $saleStartTimeProxy.addClass('mpwem-ticket-add__time-part');
        $saleEndDateProxy.addClass('mpwem-ticket-add__date-part');
        $saleEndTimeProxy.addClass('mpwem-ticket-add__time-part');

        $more.append(
            $('<div class="mpwem-ticket-add__grid"></div>')
                .append(
                    $('<div class="mpwem-ticket-add__group mpwem-ticket-add__group--date"></div>')
                        .append($('<label></label>').text('Sale Starts'))
                        .append(
                            $('<div class="mpwem-ticket-add__date-row"></div>')
                                .append($saleStartDateProxy)
                                .append($saleStartTimeProxy)
                        )
                )
                .append(
                    $('<div class="mpwem-ticket-add__group mpwem-ticket-add__group--date"></div>')
                        .append($('<label></label>').text('Sale Ends'))
                        .append(
                            $('<div class="mpwem-ticket-add__date-row"></div>')
                                .append($saleEndDateProxy)
                                .append($saleEndTimeProxy)
                        )
                )
        );

        $body.append($more);

        $moreToggle.on('click', function() {
            const willOpen = !$more.is(':visible');
            $more.slideToggle(150);
            $moreToggle.toggleClass('is-open', willOpen).attr('aria-expanded', willOpen ? 'true' : 'false');
        });

        $card.append($body);

        const $footer = $('<div class="mpwem-ticket-add__footer"></div>');
        const $cancelBtn = $('<button type="button" class="button"></button>').text('Cancel');
        const $saveBtn = $('<button type="button" class="button button-primary mpwem-ticket-add__save"></button>').text(isEdit ? 'Save Changes' : 'Save & Add More');
        $footer.append($cancelBtn);
        let $saveCloseBtn = $();
        if (!isEdit) {
            $saveCloseBtn = $('<button type="button" class="button button-primary mpwem-ticket-add__save-close"></button>').text('Save & Close');
            $footer.append($saveCloseBtn);
        }
        $footer.append($saveBtn);
        $card.append($footer);

        $nameProxy.on('input', function() { syncRealFieldValue($nameInput, $(this).val()); setTicketAddError($nameProxy, ''); });
        $priceProxy.on('input', function() { syncRealFieldValue($priceInput, $(this).val()); setTicketAddError($priceProxy, ''); });
        if ($qtyProxy) {
            $qtyProxy.on('input', function() { syncRealFieldValue($qtyInput, $(this).val()); setTicketAddError($qtyProxy, ''); });
        }
        $descProxy.on('input', function() { syncRealFieldValue($descInput, $(this).val()); });
        $defaultProxy.on('input', function() { syncRealFieldValue($defaultQtyInput, $(this).val()); });
        $reserveProxy.on('input', function() { syncRealFieldValue($reserveQtyInput, $(this).val()); });
        $saleStartDateProxy.on('input change', function() { syncRealFieldValue($saleStartDate, $(this).val()); });
        $saleStartTimeProxy.on('input change', function() { syncRealFieldValue($saleStartTime, $(this).val()); });
        $saleEndDateProxy.on('input change', function() { syncRealFieldValue($saleEndDate, $(this).val()); });
        $saleEndTimeProxy.on('input change', function() { syncRealFieldValue($saleEndTime, $(this).val()); });

        $cancelBtn.on('click', function() {
            closeInlineTicketAdd($root, { save: false });
        });

        const saveTicket = function(addMore) {
            const name = ($nameProxy.val() || '').toString().trim();
            const price = ($priceProxy.val() || '').toString().trim();

            if (!name) {
                setTicketAddError($nameProxy, 'Ticket name is required.');
                $nameProxy.trigger('focus');
                return;
            }
            if (!price) {
                setTicketAddError($priceProxy, 'Price is required.');
                $priceProxy.trigger('focus');
                return;
            }

            syncRealFieldValue($nameInput, name);
            syncRealFieldValue($priceInput, price);
            closeInlineTicketAdd($root, { save: true });
            if (addMore) {
                window.setTimeout(function() {
                    openInlineTicketAdd($root);
                }, 150);
            }
        };

        $saveBtn.on('click', function() { saveTicket(!isEdit); });
        $saveCloseBtn.on('click', function() { saveTicket(false); });

        window.setTimeout(function() {
            $nameProxy.trigger('focus');
        }, 0);
    }

    // Red state + inline message on one input of the inline ticket form.
    function setTicketAddError($input, message) {
        const $group = $input.closest('.mpwem-ticket-add__group');
        $group.find('.mpwem-field-error-msg').remove();
        $input.toggleClass('has-error', !!message);
        if (message) {
            $('<p class="mpwem-field-error-msg" role="alert"></p>').text(message).appendTo($group);
        }
    }

    function openInlineTicketAdd($root) {
        const $card = getInlineTicketAddCard($root);
        if ($card.hasClass('is-open')) {
            const wasAdding = $card.data('mpwemInlineAddMode') !== 'edit';
            closeInlineTicketAdd($root, { save: false });
            if (wasAdding) {
                return;
            }
        }

        const context = getTicketModalContext($root);
        const $addButton = context.$modalMount.find('.mpwem_add_item').first();
        if (!$addButton.length) {
            return;
        }

        try {
            $addButton.trigger('click');
        } catch (err) {
            /* A shared add-item handler may throw (e.g. a dependency not yet
               loaded) after it has already appended the row; don't let that
               stop the inline form below from rendering. */
        }

        window.setTimeout(function() {
            const $row = getTicketRows($root).last();
            if (!$row.length) {
                return;
            }
            $card.data('mpwemInlineAddRow', $row);
            $card.data('mpwemInlineAddMode', 'add');
            renderInlineTicketAddForm($root, $row, { mode: 'add' });

            scrollToViewportCenter($card);
            $card.find('.mpwem-ticket-add__core .mpwem-ticket-add__group').first().find('input').trigger('focus');
        }, 100);
    }

    function openInlineTicketEdit($root, rowIndex) {
        const $card = getInlineTicketAddCard($root);
        const $row = getTicketRows($root).eq(rowIndex);
        if (!$row.length) {
            return;
        }

        const editingThisRow = $card.hasClass('is-open')
            && $card.data('mpwemInlineAddMode') === 'edit'
            && $card.data('mpwemInlineAddRow')
            && $card.data('mpwemInlineAddRow')[0] === $row[0];

        if ($card.hasClass('is-open')) {
            closeInlineTicketAdd($root, { save: false });
            if (editingThisRow) {
                return;
            }
        }

        $card.data('mpwemInlineAddRow', $row);
        $card.data('mpwemInlineAddMode', 'edit');
        $card.data('mpwemInlineAddOriginal', captureTicketRowFieldValues($row));

        renderInlineTicketAddForm($root, $row, { mode: 'edit' });

        scrollToViewportCenter($card);
    }

    function openTicketModal($root, mode, rowIndex) {
        const context = getTicketModalContext($root);
        if (!context.$modal.length) {
            return;
        }

        beginTicketModalSession($root);
        context.$modal.attr('aria-hidden', 'false').addClass('is-open');
        lockBodyScroll();

        const isNew = mode === 'new';
        $root.find('#mpwem_ticket_modal_title').text('Manage ticket types');
        $root.find('#mpwem_ticket_modal_description').text('Edit pricing, capacities, advanced columns, and ticket settings without leaving this step.');

        if (isNew) {
            const $addButton = context.$modalMount.find('.mpwem_add_item').first();
            if ($addButton.length) {
                $addButton.trigger('click');
                window.setTimeout(function() {
                    captureTicketDraftRows($root);
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

        discardTicketDraftRows($root);
        context.$modal.attr('aria-hidden', 'true').removeClass('is-open');
        unlockBodyScroll();
    }

    function syncTicketAdvancedColumns($root) {
        const context = getTicketModalContext($root);
        const toggles = [
            'mep_enable_early_bird_status',
            'enable_global_qty',
            'mep_show_advanced_column'
        ];

        toggles.forEach(function(name) {
            const $toggle = context.$modalMount.find('input[name="' + name + '"]').first();
            const isVisible = $toggle.is(':checked');
            const targetSelector = '[data-collapse="#' + name + '"]';
            const $targets = context.$modalMount.find(targetSelector);

            $targets.toggleClass('mpwem-ticket-col-hidden', !isVisible);

            if (name === 'mep_enable_early_bird_status') {
                context.$modal.toggleClass('is-advanced-visible', isVisible);
            }
        });

        const isGlobalQtyEnabled = context.$modalMount.find('input[name="enable_global_qty"]').first().is(':checked');
        context.$modalMount.find('.mpwem-ticket-card__capacity').toggleClass('mpwem-ticket-col-hidden', isGlobalQtyEnabled);

        const $minMaxToggle = context.$modalMount.find('input[name="mpwem_show_mm"]').first().add(
            $root.find('input[name="mpwem_show_mm"]').first()
        ).first();
        const $minMaxType = context.$modalMount.find('select[name="mep_mm_min_max_type"]').first().add(
            $root.find('select[name="mep_mm_min_max_type"]').first()
        ).first();
        const isMinMaxEnabled = $minMaxToggle.is(':checked');
        const isTicketTypeMinMax = $minMaxType.val() === 'ticket_type';
        context.$modalMount
            .find('[data-collapse="#mep_mm_ticket_type"]')
            .toggleClass('mpwem-ticket-col-hidden', !(isMinMaxEnabled && isTicketTypeMinMax));

        const isAdvancedColumnsVisible = context.$modalMount.find('input[name="mep_show_advanced_column"]').first().is(':checked');
        context.$modalMount
            .find('.mpwem-ticket-card')
            .toggleClass('mpwem-ticket-card--compact-identity', !isAdvancedColumnsVisible);

        context.$modal.toggleClass(
            'is-advanced-columns-visible',
            isAdvancedColumnsVisible
        );
        context.$modal.toggleClass(
            'is-sale-period-visible',
            context.$modalMount.find('input[name="mep_enable_early_bird_status"]').first().is(':checked')
        );
        context.$modal.toggleClass(
            'is-global-qty-visible',
            context.$modalMount.find('input[name="enable_global_qty"]').first().is(':checked')
        );

        syncDateWiseGlobalQtyColumns($root);
        syncGlobalQtyTypeWarning($root);

        // Sync Hybrid-only ticket mode column
        var isHybrid = ($root.find('input[name="mep_event_type"]').val() || 'offline') === 'hybrid';
        context.$modalMount.find('[data-hybrid-col]').toggleClass('mpwem-ticket-col-hidden', !isHybrid);
        context.$modalMount.find('.mpwem-ticket-mode-select').each(function() {
            $(this).attr('data-mode', $(this).val());
        });
    }

    function syncDateWiseGlobalQtyColumns($root) {
        const ticketContext = getTicketModalContext($root);
        const $globalQtyToggle = ticketContext.$modalMount.find('input[name="enable_global_qty"]').first().add(
            $root.find('input[name="enable_global_qty"]').first()
        ).first();
        const $globalQtyTypeSelect = ticketContext.$modalMount.find('select[name="mep_gq_type"]').first().add(
            $root.find('select[name="mep_gq_type"]').first()
        ).first();
        const isGlobalQtyEnabled = $globalQtyToggle.is(':checked');
        const globalQtyType = ($globalQtyTypeSelect.val() || '').toString();
        const shouldShowDateWiseColumns = isGlobalQtyEnabled && globalQtyType === 'date_wise';
        const $targets = $root.find(
            '#mpwem_particular_date_modal_mount [data-collapse="#mep_gq_type_date_wise"], ' +
            '#mpwem_wizard_date_mount [data-collapse="#mep_gq_type_date_wise"]'
        );

        $targets.each(function() {
            const $target = $(this);
            const tagName = (this.tagName || '').toLowerCase();
            const displayValue = tagName === 'th' || tagName === 'td' ? 'table-cell' : 'block';

            $target.toggleClass('mActive', shouldShowDateWiseColumns);
            $target.toggleClass('mpwem-ticket-col-hidden', !shouldShowDateWiseColumns);
            $target.css('display', shouldShowDateWiseColumns ? displayValue : 'none');
        });

        const $extraGlobalQtyToggle = $root.find('input[name="ex_enable_global_qty"]').first();
        const $extraGlobalQtyTypeSelect = $root.find('select[name="ex_mep_gq_type"]').first();
        const isExtraGlobalQtyEnabled = $extraGlobalQtyToggle.is(':checked');
        const extraGlobalQtyType = ($extraGlobalQtyTypeSelect.val() || '').toString();
        const shouldShowExtraDateWiseColumns = isGlobalQtyEnabled && globalQtyType === 'date_wise' && isExtraGlobalQtyEnabled && extraGlobalQtyType === 'date_wise';
        const $extraTargets = $root.find(
            '#mpwem_particular_date_modal_mount [data-collapse="#ex_mep_gq_type_date_wise"], ' +
            '#mpwem_wizard_date_mount [data-collapse="#ex_mep_gq_type_date_wise"]'
        );

        $extraTargets.each(function() {
            const $target = $(this);
            const tagName = (this.tagName || '').toLowerCase();
            const displayValue = tagName === 'th' || tagName === 'td' ? 'table-cell' : 'block';

            $target.toggleClass('mActive', shouldShowExtraDateWiseColumns);
            $target.toggleClass('mpwem-ticket-col-hidden', !shouldShowExtraDateWiseColumns);
            $target.css('display', shouldShowExtraDateWiseColumns ? displayValue : 'none');
        });
    }

    function syncGlobalQtyTypeWarning($root) {
        const ticketContext = getTicketModalContext($root);
        const $globalQtyToggle = ticketContext.$modalMount.find('input[name="enable_global_qty"]').first().add(
            $root.find('input[name="enable_global_qty"]').first()
        ).first();
        const $globalQtyTypeSelect = ticketContext.$modalMount.find('select[name="mep_gq_type"]').first().add(
            $root.find('select[name="mep_gq_type"]').first()
        ).first();
        const isGlobalQtyEnabled = $globalQtyToggle.is(':checked');
        const globalQtyType = ($globalQtyTypeSelect.val() || '').toString();
        const shouldShowWarning = isGlobalQtyEnabled && globalQtyType === 'date_wise';

        $root.find('.mpwem-global-qty-warning').toggleClass('is-visible', shouldShowWarning);
        
        $root.find('input[name="mep_gq_total_seat"]').closest('.mpwem-ticket-card__group').toggleClass('mpwem-ticket-col-hidden', globalQtyType === 'date_wise');
        $root.find('input[name="mep_gq_total_resv_seat"]').closest('.mpwem-ticket-card__group').toggleClass('mpwem-ticket-col-hidden', globalQtyType === 'date_wise');
    }

    function initializeTicketTableDragScroll($root) {
        const $scroller = getTicketModalContext($root).$modalMount.find('._ov_auto').first();
        initializeDragScroll($scroller, 'mpwemDragScroll', 'mpwemDragScrollInit');
    }

    function initializeTicketPricingModal($root) {
        const context = getTicketModalContext($root);
        const $ticketPanel = getPanel($root, '#mpwem_ticket_pricing_settings');
        const $ticketSettingsBlock = $ticketPanel.find('.mpwem-ticket-editor-section').first();
        const $footerStart = $root.find('#mpwem_ticket_modal_footer_start').first();

        if (!$ticketPanel.length || !context.$modalMount.length || !$ticketSettingsBlock.length) {
            return;
        }

        if ($ticketSettingsBlock.parent()[0] !== context.$modalMount[0]) {
            context.$modalMount.append($ticketSettingsBlock.detach());
        }

        context.$modalMount.find('.mpwem-ticket-editor-section > ._bg_light_padding').first().hide();
        context.$modalMount.find('.mpwem_settings_area > p').first().addClass('mpwem-ticket-modal__note');
        context.$modalMount.find('.mpwem_add_new_button_area').first().addClass('mpwem-ticket-modal__inline-actions');

        if ($footerStart.length && !$footerStart.find('.mpwem-ticket-modal__add').length) {
            const addLabel = $.trim(context.$modalMount.find('.mpwem_add_item').first().text()) || 'Add New Ticket Type';
            $footerStart.append(
                $('<button type="button" class="button button-link mpwem-ticket-modal__add"></button>')
                    .text(addLabel)
            );
        }
        
        enhanceSelects(context.$modalMount);
        enhanceDateFields(context.$modalMount);
        enhanceCustomCalendar(context.$modalMount);
        storeSavedTicketSummarySnapshot($root);
        renderTicketSummary($root);
        syncTicketAdvancedColumns($root);
        initializeTicketTableDragScroll($root);

        context.$modalMount.find('.mpwem-ticket-cards-container.mpwem_item_insert').each(function() {
            const $container = $(this);
            if (!$container.data('mpwemSummaryObserver')) {
                const observer = new MutationObserver(function() {
                    enhanceSelects(context.$modalMount);
                    enhanceDateFields(context.$modalMount);
                    enhanceCustomCalendar(context.$modalMount);
                    renderTicketSummary($root);
                    syncTicketAdvancedColumns($root);
                    initializeTicketTableDragScroll($root);
                });
                observer.observe($container[0], { childList: true, subtree: true });
                $container.data('mpwemSummaryObserver', observer);
            }
        });

        $root.off('.mpwemTicketModal');

        $root.on('click.mpwemTicketModal', '[data-mpwem-ticket-modal-open]', function(e) {
            e.preventDefault();
            const mode = $(this).attr('data-mpwem-ticket-modal-open') || 'list';

            if (mode === 'new') {
                openInlineTicketAdd($root);
                return;
            }

            const rowIndex = parseInt($(this).attr('data-ticket-row-index'), 10);
            openTicketModal($root, mode, Number.isNaN(rowIndex) ? null : rowIndex);
        });

        $root.on('click.mpwemTicketModal', '[data-mpwem-ticket-modal-close]', function(e) {
            e.preventDefault();
            closeTicketModal($root);
        });

        $root.on('click.mpwemTicketModal', '.mpwem-ticket-modal__add', function(e) {
            e.preventDefault();
            const $addButton = context.$modalMount.find('.mpwem_add_item').first();
            if ($addButton.length) {
                $addButton.trigger('click');
                window.setTimeout(function() {
                    captureTicketDraftRows($root);
                }, 100);
            }
        });

        $root.on('click.mpwemTicketModal', '#mpwem_ticket_modal_mount .mpwem_add_item', function() {
            window.setTimeout(function() {
                captureTicketDraftRows($root);
            }, 100);
        });

        $root.on('click.mpwemTicketModal', '.mpwem-ticket-modal__save', function(e) {
            e.preventDefault();
            const $button = $(this);

            if ($button.prop('disabled')) {
                return;
            }

            if (!validateDateWiseGlobalQty($root, { focusStep: true })) {
                return;
            }

            persistTicketDraftRows($root);
            $button.prop('disabled', true).addClass('is-saving');
            submitEventForm($root, '', { skipPaymentCheck: true, skipOtherSteps: true });

            window.setTimeout(function() {
                if ($button.closest('body').length) {
                    $button.prop('disabled', false).removeClass('is-saving');
                }
            }, 5000);
        });

        $root.on('input.mpwemTicketModal change.mpwemTicketModal', '#mpwem_ticket_modal_mount [name="option_name_t[]"], #mpwem_ticket_modal_mount [name="option_details_t[]"], #mpwem_ticket_modal_mount [name="option_price_t[]"], #mpwem_ticket_modal_mount [name="option_qty_t[]"], #mpwem_ticket_modal_mount [name="option_ticket_enable[]"]', function() {
            renderTicketSummary($root);
        });

        $root.on('change.mpwemTicketModal', '[name="mep_reg_status"]', function() {
            renderTicketSummary($root);
        });

        $root.on('change.mpwemTicketModal', '#mpwem_ticket_modal_mount input[name="mep_enable_early_bird_status"], #mpwem_ticket_modal_mount input[name="enable_global_qty"], #mpwem_ticket_modal_mount input[name="mep_show_advanced_column"], #mpwem_ticket_modal_mount select[name="mep_gq_type"], input[name="mpwem_show_mm"], select[name="mep_mm_min_max_type"]', function() {
            syncTicketAdvancedColumns($root);
            renderTicketSummary($root);
        });

        $root.on('click.mpwemTicketModal', '[data-mpwem-open-particular-date-modal]', function(e) {
            e.preventDefault();
            setActiveStep($root, 'date', { pushHash: true, validate: false });
            window.setTimeout(function() {
                // Repeated Event's real controls live inline in the Date &
                // Time step (see initializeParticularDateModal), not inside
                // the shared date modal - openParticularDateModal has
                // nothing to show for this type, which is why "Open Date
                // Table" opened an empty popup. Jump to the real section
                // instead of opening it.
                if (getActiveDateModalType($root) === 'everyday') {
                    const $repeatSection = getPanel($root, '#mpwem_date_settings').find('[data-collapse="#mep_everyday_event"]').first();
                    if ($repeatSection.length) {
                        scrollToViewportCenter($repeatSection, 250);
                        flashAttention($repeatSection);
                    }
                    return;
                }
                openParticularDateModal($root, 'list');
            }, 120);
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
                        '<span class="mpwem-ticket-summary__toolbar-icon" aria-hidden="true">' +
                            '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7h-3a2 2 0 0 1-2-2V2"></path><path d="M9 18a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8.5L20 6.5V16a2 2 0 0 1-2 2Z"></path><path d="M3 7.6v12.8A1.6 1.6 0 0 0 4.6 22h9.8"></path></svg>' +
                        '</span>' +
                        '<div class="mpwem-ticket-summary__intro">' +
                            '<h3>Simple add-on list</h3>' +
                            '<p>Review optional services at a glance, then open the full editor when you need pricing or quantity details.</p>' +
                        '</div>' +
                    '</div>' +
                    '<div class="mpwem-ticket-summary__list" id="mpwem_extra_service_summary_list"></div>' +
                '</div>'
            );

            $mount.append($summaryMarkup);
            $summary = $summaryMarkup;
        }

        return {
            $summary: $summary,
            $summaryList: $summary.find('#mpwem_extra_service_summary_list').first(),
            $modal: $root.find('#mpwem_extra_service_editor_modal').first(),
            $modalMount: $root.find('#mpwem_extra_service_modal_mount').first(),
            $legacyMount: $mount
        };
    }

    function getExtraServiceRows($root) {
        const $cardContainer = $root.find('#mpwem_extra_service_modal_mount .mpwem-ticket-cards-container.mpwem_item_insert').first();
        if ($cardContainer.length) {
            return $cardContainer.children('.mpwem-ticket-card.mpwem_remove_area').filter(function() {
                return $(this).closest('.mpwem_hidden_content').length === 0;
            });
        }

        const $tbody = $root.find('#mpwem_extra_service_modal_mount tbody.mpwem_item_insert').first();
        if (!$tbody.length) {
            return $();
        }

        return $tbody.children('tr.mpwem_remove_area').filter(function() {
            return $(this).closest('.mpwem_hidden_content').length === 0;
        });
    }

    function getExtraServiceRowsContainer($root) {
        const $cardContainer = $root.find('#mpwem_extra_service_modal_mount .mpwem-ticket-cards-container.mpwem_item_insert').first();
        if ($cardContainer.length) {
            return $cardContainer;
        }

        return $root.find('#mpwem_extra_service_modal_mount tbody.mpwem_item_insert').first();
    }

    function storeSavedExtraServiceSummarySnapshot($root) {
        const context = getExtraServiceModalContext($root);
        const $container = getExtraServiceRowsContainer($root);

        context.$modal.data('mpwemExtraServiceSavedRowsHtml', $container.length ? $container.html() : '');
    }

    function getSavedExtraServiceSummaryRows($root) {
        const context = getExtraServiceModalContext($root);
        const savedRowsHtml = context.$modal.data('mpwemExtraServiceSavedRowsHtml');

        if (typeof savedRowsHtml !== 'string') {
            return getExtraServiceRows($root);
        }

        return $('<div></div>').html(savedRowsHtml).children('.mpwem-ticket-card.mpwem_remove_area, tr.mpwem_remove_area');
    }

    function beginExtraServiceModalSession($root) {
        const context = getExtraServiceModalContext($root);
        const $container = getExtraServiceRowsContainer($root);
        if (!context.$modal.length) {
            return;
        }

        context.$modal
            .data('mpwemExtraServiceSessionRowsHtml', $container.length ? $container.html() : '')
            .data('mpwemExtraServicePersistDraftRows', false);
    }

    function persistExtraServiceDraftRows($root) {
        const context = getExtraServiceModalContext($root);
        context.$modal.data('mpwemExtraServicePersistDraftRows', true);
    }

    function discardExtraServiceDraftRows($root) {
        const context = getExtraServiceModalContext($root);
        const $container = getExtraServiceRowsContainer($root);
        const sessionRowsHtml = context.$modal.data('mpwemExtraServiceSessionRowsHtml');
        if (!$container.length || typeof sessionRowsHtml !== 'string') {
            return;
        }

        if (context.$modal.data('mpwemExtraServicePersistDraftRows')) {
            context.$modal.removeData('mpwemExtraServiceSessionRowsHtml').removeData('mpwemExtraServicePersistDraftRows');
            return;
        }

        $container.html(sessionRowsHtml);
        context.$modal.removeData('mpwemExtraServiceSessionRowsHtml').removeData('mpwemExtraServicePersistDraftRows');
        renderExtraServiceSummary($root);
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

        if (value === '0') {
            return 'Free';
        }

        if (typeof window.mpwem_price_format === 'function' && !Number.isNaN(Number(value))) {
            return $('<div></div>').html(window.mpwem_price_format(Number(value))).text();
        }

        return value;
    }

    function extraServiceRowQty($row) {
        const value = ($row.find('[name="option_qty[]"]').first().val() || '').toString().trim();
        return value.length ? value : 'Unlimited';
    }

    /* Delegates to the shared highlightRow helper defined near the top of this module. */
    function highlightExtraServiceRow($row) {
        highlightRow($row);
    }

    function renderExtraServiceSummary($root) {
        const context = getExtraServiceModalContext($root);
        const $rows = getSavedExtraServiceSummaryRows($root);
        if (!context.$summaryList.length) {
            return;
        }

        // The inline add/edit card lives inside the list, positioned right
        // before the "Add New Extra Service" button - detach it (not
        // remove/empty) before clearing the list so an open card survives
        // the rebuild, then reinsert it in the same spot below.
        const $openAddCard = context.$summaryList.find('#mpwem_extra_service_summary_add').detach();

        context.$summaryList.empty();

        if (!$rows.length) {
            // Header row only makes sense once a service exists under it -
            // same pattern as renderTicketSummary's empty state.
            context.$summaryList.append(
                $('<div class="mpwem-ticket-summary__empty mpwem-ticket-summary__empty--centered"></div>')
                    .append(
                        $('<span class="mpwem-ticket-summary__empty-icon" aria-hidden="true"></span>')
                            .html('<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7h-3a2 2 0 0 1-2-2V2"></path><path d="M9 18a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8.5L20 6.5V16a2 2 0 0 1-2 2Z"></path><path d="M3 7.6v12.8A1.6 1.6 0 0 0 4.6 22h9.8"></path></svg>')
                    )
                    .append($('<h4></h4>').text('No extra services yet'))
                    .append($('<p></p>').text('Create optional add-ons like meals, merchandise, or upgrades, then manage them in the full editor.'))
                    .append(
                        $('<button type="button" class="button button-primary"></button>')
                            .attr('data-mpwem-extra-modal-open', 'new')
                            .html('<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg> Create First Extra Service')
                    )
            );
            if ($openAddCard.length) {
                context.$summaryList.append($openAddCard);
            }
            return;
        }

        context.$summaryList.append(
            $('<div class="mpwem-ticket-summary__header"></div>')
                .append($('<span class="mpwem-ticket-summary__header-ticket"></span>').text('Extra Service'))
                .append($('<span class="mpwem-ticket-summary__header-price"></span>').text('Price'))
                .append($('<span class="mpwem-ticket-summary__header-capacity"></span>').text('Qty'))
                .append($('<span class="mpwem-ticket-summary__header-action"></span>').text('Action'))
        );

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
                    )
                    .append(
                        $('<div class="mpwem-ticket-summary__meta mpwem-ticket-summary__meta--capacity"></div>')
                            .append($('<span class="mpwem-ticket-summary__capacity"></span>').text(extraServiceRowQty($row)))
                    )
                    .append(buildExtraServiceSummaryActions($root, index))
            );
        });

        // "Add New Extra Service" renders after every row, same as
        // "Add New Ticket Type" on the Ticket & Pricing step.
        const $addExtraBtn = $('<button type="button" class="button button-primary mpwem-date-primary-fields__add"></button>')
            .attr('data-mpwem-extra-modal-open', 'new')
            .text('Add New Extra Service');
        context.$summaryList.append($addExtraBtn);

        // The open add/edit card, if any, goes right before that button -
        // so it shows above "Add New Extra Service" instead of appearing
        // further down, after the whole summary.
        if ($openAddCard.length) {
            $openAddCard.insertBefore($addExtraBtn);
        }

        enableExtraServiceSummarySort($root, context);
    }

    function buildExtraServiceSummaryActions($root, index) {
        const $wrap = $('<div class="mpwem-ticket-summary__item-actions"></div>');

        const $editBtn = $('<button type="button" class="mpwem-ticket-summary__action-btn mpwem-ticket-summary__action-btn--edit" title="Edit" aria-label="Edit extra service"></button>')
            .html(TICKET_SUMMARY_EDIT_ICON);
        $editBtn.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openInlineExtraServiceEdit($root, index);
        });

        const $deleteBtn = $('<button type="button" class="mpwem-ticket-summary__action-btn mpwem-ticket-summary__action-btn--delete" title="Delete" aria-label="Delete extra service"></button>')
            .html(TICKET_SUMMARY_DELETE_ICON);
        $deleteBtn.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const $liveRow = getExtraServiceRows($root).eq(index);
            const $realRemove = $liveRow.find('.mpwem_item_remove').first();
            if ($realRemove.length) {
                $realRemove.trigger('click');
            }
        });

        const $sortHandle = $('<span class="mpwem-ticket-summary__sort-handle" title="Drag to reorder" aria-label="Drag to reorder" role="img"></span>')
            .html(TICKET_SUMMARY_GRIP_ICON);
        $sortHandle.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
        });

        $wrap.append($editBtn).append($deleteBtn).append($sortHandle);

        return $wrap;
    }

    function enableExtraServiceSummarySort($root, context) {
        if (!context.$summaryList.length || typeof context.$summaryList.sortable !== 'function') {
            return;
        }

        if (context.$summaryList.data('ui-sortable')) {
            context.$summaryList.sortable('destroy');
        }

        context.$summaryList.sortable({
            items: '> .mpwem-ticket-summary__item',
            handle: '.mpwem-ticket-summary__sort-handle',
            axis: 'y',
            update: function() {
                const $container = getExtraServiceRowsContainer($root);
                if (!$container.length) {
                    return;
                }

                const $liveRows = getExtraServiceRows($root);
                context.$summaryList.find('.mpwem-ticket-summary__item').each(function() {
                    const oldIndex = parseInt($(this).attr('data-extra-service-row-index'), 10);
                    if (!Number.isNaN(oldIndex) && $liveRows.eq(oldIndex).length) {
                        $container.append($liveRows.eq(oldIndex));
                    }
                });

                storeSavedExtraServiceSummarySnapshot($root);
                renderExtraServiceSummary($root);
            }
        });
    }

    const EXTRA_SERVICE_ROW_FIELD_NAMES = [
        'option_name[]',
        'option_price[]',
        'option_qty[]',
        'option_qty_type[]'
    ];

    function captureExtraServiceRowFieldValues($row) {
        const values = {};
        EXTRA_SERVICE_ROW_FIELD_NAMES.forEach(function(name) {
            const $field = $row.find('[name="' + name + '"]').first();
            if ($field.length) {
                values[name] = ($field.val() || '').toString();
            }
        });
        return values;
    }

    function getInlineExtraServiceAddCard($root) {
        const context = getExtraServiceModalContext($root);
        if (!context.$summary.length) {
            return $();
        }

        let $card = $root.find('#mpwem_extra_service_summary_add').first();
        if (!$card.length) {
            $card = $('<div class="mpwem-ticket-add" id="mpwem_extra_service_summary_add"></div>');
            const $addBtn = context.$summaryList.find('.mpwem-date-primary-fields__add[data-mpwem-extra-modal-open="new"]').last();
            if ($addBtn.length) {
                $card.insertBefore($addBtn);
            } else {
                context.$summaryList.append($card);
            }
        }

        return $card;
    }

    function closeInlineExtraServiceAdd($root, opts) {
        opts = opts || {};
        const $card = getInlineExtraServiceAddCard($root);
        const $row = $card.data('mpwemInlineAddRow');
        const mode = $card.data('mpwemInlineAddMode') || 'add';
        const original = $card.data('mpwemInlineAddOriginal');

        if (!opts.save) {
            if (mode === 'edit' && $row && $row.length && original) {
                Object.keys(original).forEach(function(name) {
                    const $field = $row.find('[name="' + name + '"]').first();
                    if ($field.length) {
                        syncRealFieldValue($field, original[name]);
                    }
                });
            } else if ($row && $row.length && $row.closest('body').length) {
                $row.remove();
            }
        }

        $card.removeData('mpwemInlineAddRow mpwemInlineAddMode mpwemInlineAddOriginal').removeClass('is-open').empty();

        storeSavedExtraServiceSummarySnapshot($root);
        renderExtraServiceSummary($root);
    }

    function renderInlineExtraServiceAddForm($root, $row, opts) {
        opts = opts || {};
        const isEdit = opts.mode === 'edit';
        const $card = getInlineExtraServiceAddCard($root);
        $card.empty().addClass('is-open');

        const $nameInput = $row.find('[name="option_name[]"]').first();
        const $priceInput = $row.find('[name="option_price[]"]').first();
        const $qtyInput = $row.find('[name="option_qty[]"]').first();
        const $qtyTypeSelect = $row.find('[name="option_qty_type[]"]').first();

        const headIcon = isEdit
            ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>'
            : '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>';

        const $headTitleRow = $('<div class="mpwem-ticket-add__head-title"></div>')
            .append($('<h3></h3>').text(isEdit ? 'Edit Extra Service' : 'Add Extra Service'));

        const existingName = ($nameInput.val() || '').toString().trim();
        if (isEdit && existingName) {
            $headTitleRow.append($('<span class="mpwem-ticket-add__head-chip"></span>').text(existingName));
        }

        $card.append(
            $('<div class="mpwem-ticket-add__head"></div>')
                .append($('<span class="mpwem-ticket-add__head-icon"></span>').html(headIcon))
                .append(
                    $('<div class="mpwem-ticket-add__head-copy"></div>')
                        .append($headTitleRow)
                        .append($('<p></p>').text(isEdit ? 'Update the price, quantity, or how it’s shown.' : 'Optional add-ons like meals, merchandise, or upgrades.'))
                )
        );

        const $body = $('<div class="mpwem-ticket-add__body"></div>');
        const $core = $('<div class="mpwem-ticket-add__core"></div>');

        const $nameProxy = $('<input type="text" placeholder="e.g. T-Shirt">').val(($nameInput.val() || '').toString());
        $core.append(
            $('<div class="mpwem-ticket-add__group"></div>')
                .append($('<label></label>').text('Service Name'))
                .append($nameProxy)
        );

        const $priceProxy = $('<input type="text" inputmode="decimal" placeholder="0.00">').val(($priceInput.val() || '').toString());
        $core.append(
            $('<div class="mpwem-ticket-add__group"></div>')
                .append($('<label></label>').text('Price'))
                .append($priceProxy)
        );

        const $qtyProxy = $('<input type="number" placeholder="100">').val(($qtyInput.val() || '').toString());
        $core.append(
            $('<div class="mpwem-ticket-add__group"></div>')
                .append($('<label></label>').text('Available Qty'))
                .append($qtyProxy)
        );

        $body.append($core);

        const qtyTypeVal = ($qtyTypeSelect.val() || 'inputbox').toString();

        const $qtyIndicator = $('<div class="mpwem-ticket-add__pill-indicator"></div>');
        const $pillInput = $('<button type="button" class="mpwem-ticket-add__pill"></button>')
            .html('<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="10" rx="2.5"/><path d="M7 12h6"/></svg><span>Input Box</span>')
            .toggleClass('is-active', qtyTypeVal === 'inputbox');
        const $pillDropdown = $('<button type="button" class="mpwem-ticket-add__pill"></button>')
            .html('<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="10" rx="2.5"/><path d="M15 10l2 2-2 2"/></svg><span>Dropdown List</span>')
            .toggleClass('is-active', qtyTypeVal === 'dropdown');

        function updateExtraQtyIndicator(type) {
            $qtyIndicator.css('left', type === 'dropdown' ? 'calc(50% + 1px)' : '3px');
        }
        updateExtraQtyIndicator(qtyTypeVal);

        $body.append(
            $('<div class="mpwem-ticket-add__field"></div>')
                .append($('<label></label>').text('Quantity shown as'))
                .append(
                    $('<div class="mpwem-ticket-add__pills"></div>').append($qtyIndicator).append($pillInput).append($pillDropdown)
                )
        );

        $pillInput.on('click', function() {
            syncRealFieldValue($qtyTypeSelect, 'inputbox');
            $qtyTypeSelect.trigger('change');
            $pillInput.addClass('is-active');
            $pillDropdown.removeClass('is-active');
            updateExtraQtyIndicator('inputbox');
        });
        $pillDropdown.on('click', function() {
            syncRealFieldValue($qtyTypeSelect, 'dropdown');
            $qtyTypeSelect.trigger('change');
            $pillDropdown.addClass('is-active');
            $pillInput.removeClass('is-active');
            updateExtraQtyIndicator('dropdown');
        });

        $card.append($body);

        const $footer = $('<div class="mpwem-ticket-add__footer"></div>');
        const $cancelBtn = $('<button type="button" class="button"></button>').text('Cancel');
        const $saveBtn = $('<button type="button" class="button button-primary mpwem-ticket-add__save"></button>').text(isEdit ? 'Save Changes' : 'Save & Add More');
        $footer.append($cancelBtn);
        let $saveCloseBtn = $();
        if (!isEdit) {
            $saveCloseBtn = $('<button type="button" class="button button-primary mpwem-ticket-add__save-close"></button>').text('Save & Close');
            $footer.append($saveCloseBtn);
        }
        $footer.append($saveBtn);
        $card.append($footer);

        $nameProxy.on('input', function() { syncRealFieldValue($nameInput, $(this).val()); $nameProxy.removeClass('has-error'); });
        $priceProxy.on('input', function() { syncRealFieldValue($priceInput, $(this).val()); });
        $qtyProxy.on('input', function() { syncRealFieldValue($qtyInput, $(this).val()); });

        $cancelBtn.on('click', function() {
            closeInlineExtraServiceAdd($root, { save: false });
        });

        const saveExtraService = function(addMore) {
            const name = ($nameProxy.val() || '').toString().trim();
            if (!name) {
                $nameProxy.addClass('has-error').trigger('focus');
                return;
            }

            syncRealFieldValue($nameInput, name);
            syncRealFieldValue($priceInput, ($priceProxy.val() || '').toString());
            syncRealFieldValue($qtyInput, ($qtyProxy.val() || '').toString());
            closeInlineExtraServiceAdd($root, { save: true });
            if (addMore) {
                window.setTimeout(function() {
                    openInlineExtraServiceAdd($root);
                }, 150);
            }
        };

        $saveBtn.on('click', function() { saveExtraService(!isEdit); });
        $saveCloseBtn.on('click', function() { saveExtraService(false); });

        window.setTimeout(function() {
            $nameProxy.trigger('focus');
        }, 0);
    }

    function openInlineExtraServiceAdd($root) {
        const $card = getInlineExtraServiceAddCard($root);
        if ($card.hasClass('is-open')) {
            const wasAdding = $card.data('mpwemInlineAddMode') !== 'edit';
            closeInlineExtraServiceAdd($root, { save: false });
            if (wasAdding) {
                return;
            }
        }

        const context = getExtraServiceModalContext($root);
        const $addButton = context.$modalMount.find('.mpwem_add_item').first();
        if (!$addButton.length) {
            return;
        }

        try {
            $addButton.trigger('click');
        } catch (err) {
            /* A shared add-item handler may throw (e.g. a dependency not yet
               loaded) after it has already appended the row; don't let that
               stop the inline form below from rendering. */
        }

        window.setTimeout(function() {
            const $row = getExtraServiceRows($root).last();
            if (!$row.length) {
                return;
            }
            $card.data('mpwemInlineAddRow', $row);
            $card.data('mpwemInlineAddMode', 'add');
            renderInlineExtraServiceAddForm($root, $row, { mode: 'add' });
        }, 100);
    }

    function openInlineExtraServiceEdit($root, rowIndex) {
        const $card = getInlineExtraServiceAddCard($root);
        const $row = getExtraServiceRows($root).eq(rowIndex);
        if (!$row.length) {
            return;
        }

        const editingThisRow = $card.hasClass('is-open')
            && $card.data('mpwemInlineAddMode') === 'edit'
            && $card.data('mpwemInlineAddRow')
            && $card.data('mpwemInlineAddRow')[0] === $row[0];

        if ($card.hasClass('is-open')) {
            closeInlineExtraServiceAdd($root, { save: false });
            if (editingThisRow) {
                return;
            }
        }

        $card.data('mpwemInlineAddRow', $row);
        $card.data('mpwemInlineAddMode', 'edit');
        $card.data('mpwemInlineAddOriginal', captureExtraServiceRowFieldValues($row));

        renderInlineExtraServiceAddForm($root, $row, { mode: 'edit' });
    }

    function openExtraServiceModal($root, mode, rowIndex) {
        const context = getExtraServiceModalContext($root);
        if (!context.$modal.length) {
            return;
        }

        beginExtraServiceModalSession($root);
        context.$modal.attr('aria-hidden', 'false').addClass('is-open');
        lockBodyScroll();

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

        discardExtraServiceDraftRows($root);
        context.$modal.attr('aria-hidden', 'true').removeClass('is-open');
        unlockBodyScroll();
    }

    function initializeExtraServiceModal($root) {
        const context = getExtraServiceModalContext($root);
        const $serviceBlock = context.$legacyMount.children('._layout_default_xs_mp_zero').first();
        const $footerStart = $root.find('#mpwem_extra_service_modal_footer_start').first();

        if (!context.$legacyMount.length || !context.$modalMount.length || !$serviceBlock.length) {
            return;
        }

        if ($serviceBlock.parent()[0] !== context.$modalMount[0]) {
            context.$modalMount.append($serviceBlock.detach());
        }

        context.$modalMount.find('> ._layout_default_xs_mp_zero > ._bg_light_padding').first().hide();
        context.$modalMount.find('.mpwem_add_new_button_area').first().addClass('mpwem-ticket-modal__inline-actions');
        context.$modalMount.find('.mpwem_settings_area').first().addClass('mpwem-extra-service-settings-area');

        if ($footerStart.length && !$footerStart.find('.mpwem-extra-service-modal__add').length) {
            const addLabel = $.trim(context.$modalMount.find('.mpwem_add_item').first().text()) || 'Add Extra Service';
            $footerStart.append(
                $('<button type="button" class="button button-link mpwem-extra-service-modal__add"></button>')
                    .text(addLabel)
            );
        }

        storeSavedExtraServiceSummarySnapshot($root);
        renderExtraServiceSummary($root);
        initializeExtraServiceTableDragScroll($root);

        context.$modalMount.find('.mpwem-ticket-cards-container.mpwem_item_insert, tbody.mpwem_item_insert').each(function() {
            const $container = $(this);
            if ($container.data('mpwemExtraSummaryObserver')) {
                return;
            }

            const observer = new MutationObserver(function() {
                renderExtraServiceSummary($root);
                initializeExtraServiceTableDragScroll($root);
            });

            observer.observe($container[0], { childList: true, subtree: true });
            $container.data('mpwemExtraSummaryObserver', observer);
        });

        $root.off('.mpwemExtraModal');

        $root.on('click.mpwemExtraModal', '[data-mpwem-extra-modal-open]', function(e) {
            e.preventDefault();
            const mode = $(this).attr('data-mpwem-extra-modal-open') || 'list';

            if (mode === 'new') {
                openInlineExtraServiceAdd($root);
                return;
            }

            const rowIndex = parseInt($(this).attr('data-extra-service-row-index'), 10);
            openExtraServiceModal($root, mode, Number.isNaN(rowIndex) ? null : rowIndex);
        });

        $root.on('click.mpwemExtraModal', '[data-mpwem-extra-modal-close]', function(e) {
            e.preventDefault();
            closeExtraServiceModal($root);
        });

        $root.on('click.mpwemExtraModal', '.mpwem-extra-service-modal__add', function(e) {
            e.preventDefault();
            const $addButton = context.$modalMount.find('.mpwem_add_item').first();
            if ($addButton.length) {
                $addButton.trigger('click');
                window.setTimeout(function() {
                    const $rows = getExtraServiceRows($root);
                    highlightExtraServiceRow($rows.last());
                    renderExtraServiceSummary($root);
                }, 100);
            }
        });

        $root.on('click.mpwemExtraModal', '.mpwem-extra-service-modal__save', function(e) {
            e.preventDefault();
            const $button = $(this);

            if ($button.prop('disabled')) {
                return;
            }

            if (!validateDateWiseGlobalQty($root, { focusStep: true })) {
                return;
            }

            persistExtraServiceDraftRows($root);
            $button.prop('disabled', true).addClass('is-saving');
            submitEventForm($root, '', { skipOtherSteps: true });

            window.setTimeout(function() {
                if ($button.closest('body').length) {
                    $button.prop('disabled', false).removeClass('is-saving');
                }
            }, 5000);
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
        const $scroller = getExtraServiceModalContext($root).$modalMount.find('._ov_auto').first();
        initializeDragScroll($scroller, 'mpwemExtraDragScroll', 'mpwemExtraDragScrollInit');
    }

    function getDateModalTypeConfig(type) {
        const map = {
            no: {
                eyebrow: 'Single Event',
                summaryTitle: 'Single event schedule',
                summaryText: 'Review the main event date and any extra dates, then open the full editor when you need to update the schedule.',
                secondaryAction: 'Add Schedule',
                modalTitle: 'Manage single event schedule',
                modalDescription: 'Edit the main event date, time, and any additional single-event dates without leaving this step.',
                modalNewTitle: 'Add schedule',
                modalNewDescription: 'Create an additional schedule, then fill in the date and time details.'
            },
            yes: {
                eyebrow: 'Particular Dates',
                summaryTitle: 'Specific event dates',
                summaryText: 'Review particular dates at a glance, then open the full editor when you need to change time or quantity details.',
                secondaryAction: 'Add Particular Date',
                modalTitle: 'Manage particular dates',
                modalDescription: 'Edit specific event dates, times, and quantities without leaving this step.',
                modalNewTitle: 'Add particular date',
                modalNewDescription: 'Create a new date, then fill in time and quantity details.'
            },
            everyday: {
                eyebrow: 'Repeated Event',
                summaryTitle: 'Repeated event schedule',
                summaryText: 'Review the repeating schedule, off days, and special date settings, then open the full editor to adjust them.',
                primaryAction: 'Open Settings',
                modalTitle: 'Manage repeated event schedule',
                modalDescription: 'Edit the repeated event range, repeat interval, off days, and special date settings without leaving this step.'
            }
        };

        return map[type] || map.no;
    }

    function getActiveDateModalType($root) {
        const $datePanel = getPanel($root, '#mpwem_date_settings');
        const value = ($datePanel.find('select[name="mep_enable_recurring"]').first().val() || 'no').toString();
        return value || 'no';
    }

    function getParticularDateModalContext($root) {
        const $datePanel = getPanel($root, '#mpwem_date_settings');
        /* Repeated Event ("#mep_everyday_event") is deliberately excluded here:
           its real fields stay inline in the step (see
           initializeParticularDateModal) instead of being relocated into the
           modal, so it's never part of the modal-hosted "legacy mount" set. */
        const $modalSections = $root.find('[data-collapse="#mep_normal_event"], [data-collapse="#mep_particular_event"]');
        let $summary = $datePanel.find('#mpwem_particular_date_summary').first();

        if ($datePanel.length && !$summary.length) {
            const $summaryMarkup = $(
                '<div class="mpwem-ticket-summary" id="mpwem_particular_date_summary" style="display:none;">' +
                    '<div class="mpwem-ticket-summary__toolbar">' +
                        '<span class="mpwem-ticket-summary__icon" aria-hidden="true">' +
                            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="3"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>' +
                        '</span>' +
                        '<div class="mpwem-ticket-summary__intro">' +
                            '<span class="mpwem-ticket-summary__eyebrow"></span>' +
                            '<h3></h3>' +
                            '<p></p>' +
                        '</div>' +
                        '<div class="mpwem-ticket-summary__actions"></div>' +
                    '</div>' +
                    '<div class="mpwem-ticket-summary__list" id="mpwem_particular_date_summary_list"></div>' +
                '</div>'
            );

            const $dateTypeField = $datePanel.find('select[name="mep_enable_recurring"]').closest('._padding_bt');
            if ($dateTypeField.length) {
                $dateTypeField.after($summaryMarkup);
            } else {
                $datePanel.find('select[name="mep_enable_recurring"]').after($summaryMarkup);
            }
            $summary = $summaryMarkup;
        }

        return {
            $summaryList: $summary.find('#mpwem_particular_date_summary_list').first(),
            $summary: $summary,
            $summaryIntro: $summary.find('.mpwem-ticket-summary__intro').first(),
            $summaryActions: $summary.find('.mpwem-ticket-summary__actions').first(),
            $modal: $root.find('#mpwem_particular_date_modal').first(),
            $modalMount: $root.find('#mpwem_particular_date_modal_mount').first(),
            $footerStart: $root.find('#mpwem_particular_date_modal_footer_start').first(),
            $legacyMounts: $modalSections
        };
    }

    function getParticularDateRows($root) {
        const context = getParticularDateModalContext($root);
        const $tbody = context.$modalMount
            .find('[data-collapse="#mep_particular_event"] tbody.mpwem_item_insert')
            .first();
        if (!$tbody.length) {
            return $();
        }

        return $tbody.children('tr.mpwem_remove_area').filter(function() {
            return $(this).closest('.mpwem_hidden_content').length === 0;
        });
    }

    function getSingleEventDateRows($root) {
        const context = getParticularDateModalContext($root);
        const $tbody = context.$modalMount
            .find('[data-collapse="#mep_normal_event"] tbody.mpwem_item_insert')
            .first();
        if (!$tbody.length) {
            return $();
        }

        return $tbody.children('tr.mpwem_remove_area').filter(function() {
            return $(this).closest('.mpwem_hidden_content').length === 0;
        });
    }

    function getActiveDateModalSection($root, type) {
        const context = getParticularDateModalContext($root);
        const activeType = (type || getActiveDateModalType($root) || 'no').toString();
        const target = activeType === 'no'
            ? '#mep_normal_event'
            : (activeType === 'yes' ? '#mep_particular_event' : '#mep_everyday_event');

        return context.$modalMount.find('[data-collapse="' + target + '"]').first();
    }

    function getActiveDateModalRows($root, type) {
        const activeType = (type || getActiveDateModalType($root) || 'no').toString();

        if (activeType === 'no') {
            return getSingleEventDateRows($root);
        }

        if (activeType === 'yes') {
            return getParticularDateRows($root);
        }

        return $();
    }

    function addDateModalRow($root, type) {
        const activeType = (type || getActiveDateModalType($root) || 'no').toString();
        const $activeSection = getActiveDateModalSection($root, activeType);
        const $addButton = $activeSection.find('.mpwem_add_item, .mp_add_item').first();

        if (!$addButton.length) {
            return;
        }

        $addButton.trigger('click');

        window.setTimeout(function() {
            enhanceDateFields($root);
            enhanceCustomCalendar($root);
            initializeParticularDateTableDragScroll($root);
            syncDateWiseGlobalQtyColumns($root);
            syncParticularDateModalFooter($root);
            renderParticularDateSummary($root);

            const $rows = getActiveDateModalRows($root, activeType);
            if ($rows.length) {
                highlightRow($rows.last());
            }
        }, 100);
    }

    function ensureParticularDateTableHints($root) {
        const context = getParticularDateModalContext($root);
        const hintText = 'Tip: Drag this table left and right to view all columns.';

        context.$modalMount
            .find('[data-collapse="#mep_particular_event"] ._ov_auto, [data-collapse="#mep_normal_event"] ._ov_auto')
            .each(function() {
                const $wrap = $(this);
                const $next = $wrap.next('.mpwem-ticket-table-hint');

                if ($next.length) {
                    $next.text(hintText);
                    return;
                }

                $('<p class="mpwem-ticket-table-hint"></p>')
                    .text(hintText)
                    .insertAfter($wrap);
            });
    }

    function storeSavedParticularDateSummarySnapshot($root) {
        const context = getParticularDateModalContext($root);
        context.$modal
            .data('mpwemDateSavedMountHtml', context.$modalMount.length ? context.$modalMount.html() : '')
            .data('mpwemDateSavedType', getActiveDateModalType($root));
    }

    function getSavedDateModalType($root) {
        const context = getParticularDateModalContext($root);
        return (context.$modal.data('mpwemDateSavedType') || getActiveDateModalType($root) || 'no').toString();
    }

    function getSavedDateSummaryMount($root) {
        const context = getParticularDateModalContext($root);
        const savedMountHtml = context.$modal.data('mpwemDateSavedMountHtml');

        if (typeof savedMountHtml !== 'string') {
            return context.$modalMount;
        }

        return $('<div></div>').html(savedMountHtml);
    }

    function getSavedParticularDateRows($root) {
        const $savedMount = getSavedDateSummaryMount($root);
        const $tbody = $savedMount.find('[data-collapse="#mep_particular_event"] tbody.mpwem_item_insert').first();
        if (!$tbody.length) {
            return $();
        }

        return $tbody.children('tr.mpwem_remove_area').filter(function() {
            return $(this).closest('.mpwem_hidden_content').length === 0;
        });
    }

    function beginParticularDateModalSession($root) {
        const context = getParticularDateModalContext($root);
        if (!context.$modal.length) {
            return;
        }

        context.$modal
            .data('mpwemDateSessionMountHtml', context.$modalMount.length ? context.$modalMount.html() : '')
            .data('mpwemDatePersistDraftRows', false);
    }

    function persistParticularDateDraftRows($root) {
        const context = getParticularDateModalContext($root);
        context.$modal.data('mpwemDatePersistDraftRows', true);
    }

    function discardParticularDateDraftRows($root) {
        const context = getParticularDateModalContext($root);
        const sessionMountHtml = context.$modal.data('mpwemDateSessionMountHtml');
        if (!context.$modalMount.length || typeof sessionMountHtml !== 'string') {
            return;
        }

        if (context.$modal.data('mpwemDatePersistDraftRows')) {
            context.$modal.removeData('mpwemDateSessionMountHtml').removeData('mpwemDatePersistDraftRows');
            return;
        }

        context.$modalMount.html(sessionMountHtml);
        context.$modal.removeData('mpwemDateSessionMountHtml').removeData('mpwemDatePersistDraftRows');
        context.$modalMount.find('table').not('.ui-datepicker-calendar').addClass('mpwem-date-table');
        context.$modalMount.find('.mpwem_add_new_button_area').addClass('mpwem-ticket-modal__inline-actions').hide();
        context.$modalMount.find('[data-collapse] > ._layout_default_xs_mp_zero > ._bg_light_padding').hide();
        context.$modalMount.find('section.bg-light').hide();
        context.$modalMount.find('.mep-special-datetime section.bg-light').hide();
        decorateDateSections(context.$modalMount);
        enhanceDateFields(context.$modalMount);
        enhanceCustomCalendar(context.$modalMount);
        enhanceOffDayPicker(context.$modalMount);
        enhanceRepeatedScheduleLayout(context.$modalMount);
        ensureParticularDateTableHints($root);
        initializeParticularDateTableDragScroll($root);
        syncDateWiseGlobalQtyColumns($root);
        syncParticularDateModalFooter($root);
        renderParticularDateSummary($root);
    }

    function renderDateModalSummaryActions($root, context, type) {
        const config = getDateModalTypeConfig(type);
        if (!context.$summaryIntro.length || !context.$summaryActions.length) {
            return;
        }

        context.$summaryIntro.find('.mpwem-ticket-summary__eyebrow').text(config.eyebrow);
        context.$summaryIntro.find('h3').text(config.summaryTitle);
        context.$summaryIntro.find('p').text(config.summaryText);

        context.$summaryActions.empty();

        if (config.primaryAction) {
            context.$summaryActions.append(
                $('<button type="button" class="button button-secondary"></button>')
                    .attr('data-mpwem-date-modal-open', 'list')
                    .text(config.primaryAction)
            );
        }

        // Single Event and Particular Dates have no "Open Settings"/
        // "Show Details" button, and their "+ Add..." action renders
        // after their own date list instead of here (see
        // renderParticularDateSummary).
        if (config.secondaryAction && type !== 'no' && type !== 'yes') {
            context.$summaryActions.append(
                $('<button type="button" class="button button-primary"></button>')
                    .attr('data-mpwem-date-modal-open', 'new')
                    .text(config.secondaryAction)
            );
        }
    }

    function buildDateSummaryActions($root, type, rowIndex, isPrimary) {
        const $wrap = $('<div class="mpwem-ticket-summary__item-actions"></div>');

        const $editBtn = $('<button type="button" class="mpwem-ticket-summary__action-btn mpwem-ticket-summary__action-btn--edit" title="Edit" aria-label="Edit date"></button>')
            .html(TICKET_SUMMARY_EDIT_ICON);
        $editBtn.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openInlineDateEdit($root, type, isPrimary ? -1 : rowIndex);
        });
        $wrap.append($editBtn);

        if (!isPrimary) {
            const $deleteBtn = $('<button type="button" class="mpwem-ticket-summary__action-btn mpwem-ticket-summary__action-btn--delete" title="Delete" aria-label="Delete date"></button>')
                .html(TICKET_SUMMARY_DELETE_ICON);
            $deleteBtn.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const $rows = getActiveDateModalRows($root, type);
                const $row = $rows.eq(rowIndex);
                const $realRemove = $row.find('.mpwem_item_remove').first();
                if ($realRemove.length) {
                    $realRemove.trigger('click');
                }
            });
            $wrap.append($deleteBtn);
        }

        return $wrap;
    }

    // Live "Schedule Summary" sidebar card (Step 3) - keeps the full
    // start/end date & time of every event date in sync with the real
    // fields, instead of the static placeholder copy it used to show.
    function updateScheduleSummaryCard($root, type, context) {
        const $body = $root.find('#mpwem_schedule_summary_body').first();
        if (!$body.length) {
            return;
        }

        function fieldVal($scope, name) {
            return ($scope.find('input[name="' + name + '"], select[name="' + name + '"]').first().val() || '').toString().trim();
        }

        $body.empty();

        if (type === 'everyday') {
            const $section = $root.find('[data-collapse="#mep_everyday_event"]').first();
            const startLabel = buildScheduleDateTimeLabel(fieldVal($section, 'event_start_date_everyday'), fieldVal($section, 'event_start_time_everyday'));
            const endLabel = buildScheduleDateTimeLabel(fieldVal($section, 'event_end_date_everyday'), fieldVal($section, 'event_end_time_everyday'));

            if (startLabel || endLabel) {
                appendScheduleSummaryItem($body, startLabel || 'Start not set', endLabel || 'End not set');
            } else {
                appendScheduleSummaryEmpty($body, 'Set a repeat range to see it here.');
            }

            // Repeat interval, weekly off days, and one-off skip dates -
            // shown only when actually configured, as icon rows instead
            // of a run-on sentence.
            const $rules = $('<div class="mpwem-schedule-summary__rules"></div>');

            const interval = parseInt(fieldVal($section, 'mep_repeated_periods'), 10) || 1;
            const freqText = interval === 1 ? 'Every day' : 'Every ' + interval + ' days';
            appendScheduleSummaryRule($rules, 'repeat', freqText);

            const skippedDayNames = [];
            $section.find('.mpwem-offday-option').each(function() {
                const $opt = $(this);
                if (!$opt.find('input[type="checkbox"]').is(':checked')) {
                    return;
                }
                const full = $opt.data('mpwemOffDayFullLabel') || $opt.attr('title');
                if (full) {
                    skippedDayNames.push(full);
                }
            });
            if (skippedDayNames.length) {
                appendScheduleSummaryRule($rules, 'skip', 'Weekly off', skippedDayNames);
            }

            const offDateCount = $section.find('input[name="mep_ticket_off_dates[]"]').filter(function() {
                return ($(this).val() || '').toString().trim() !== '';
            }).length;
            if (offDateCount > 0) {
                appendScheduleSummaryRule($rules, 'calendar-off', offDateCount + (offDateCount === 1 ? ' one-off date skipped' : ' one-off dates skipped'));
            }

            $body.append($rules);
            return;
        }

        let items;

        if (type === 'no') {
            // Single Event's fields use the "_normal" suffixed names (see
            // getDateFieldNames/collectSingleEventSummaryItems) - not the
            // plain event_start_date/event_start_time Particular Dates
            // uses, which was why newly added Single Event dates never
            // showed up here.
            const modalContext = context || getParticularDateModalContext($root);
            const $scope = modalContext.$modalMount && modalContext.$modalMount.length ? modalContext.$modalMount : getSavedDateSummaryMount($root);
            items = collectSingleEventSummaryItems($scope);
        } else {
            const modalContext = context || getParticularDateModalContext($root);
            const $scope = modalContext.$modalMount && modalContext.$modalMount.length ? modalContext.$modalMount : getSavedDateSummaryMount($root);
            items = [];

            const primaryDate = fieldVal($scope, 'event_start_date');
            if (primaryDate) {
                items.push({
                    startLabel: buildScheduleDateTimeLabel(primaryDate, fieldVal($scope, 'event_start_time')) || 'Start not set',
                    endLabel: buildScheduleDateTimeLabel(fieldVal($scope, 'event_end_date'), fieldVal($scope, 'event_end_time')) || 'End not set'
                });
            }

            getParticularDateRows($root).each(function() {
                const $row = $(this);
                items.push({
                    startLabel: buildScheduleDateTimeLabel(fieldVal($row, 'event_more_start_date[]'), fieldVal($row, 'event_more_start_time[]')) || 'Start not set',
                    endLabel: buildScheduleDateTimeLabel(fieldVal($row, 'event_more_end_date[]'), fieldVal($row, 'event_more_end_time[]')) || 'End not set'
                });
            });
        }

        if (!items.length) {
            appendScheduleSummaryEmpty($body, type === 'yes' ? 'Add a date to see it here.' : 'Set a start date to see it here.');
            return;
        }

        items.forEach(function(item) {
            appendScheduleSummaryItem($body, item.startLabel, item.endLabel);
        });
    }

    function appendScheduleSummaryItem($body, startLabel, endLabel) {
        $body.append(
            $('<div class="mpwem-schedule-summary__item"></div>')
                .append(
                    $('<span class="mpwem-schedule-summary__item-icon" aria-hidden="true"></span>')
                        .html('<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="3"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>')
                )
                .append(
                    $('<div class="mpwem-schedule-summary__item-body"></div>')
                        .append(
                            $('<p class="mpwem-schedule-summary__line"></p>')
                                .append($('<span class="mpwem-schedule-summary__label"></span>').text('Start'))
                                .append(document.createTextNode(startLabel))
                        )
                        .append(
                            $('<p class="mpwem-schedule-summary__line mpwem-schedule-summary__line--muted"></p>')
                                .append($('<span class="mpwem-schedule-summary__label"></span>').text('End'))
                                .append(document.createTextNode(endLabel))
                        )
                )
        );
    }

    function appendScheduleSummaryEmpty($body, message) {
        $body.append($('<p class="mpwem-schedule-summary__line mpwem-schedule-summary__line--muted"></p>').text(message));
    }

    function appendScheduleSummaryRule($rules, iconKey, text, chips) {
        const icons = {
            repeat: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path></svg>',
            skip: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line><line x1="9" y1="14" x2="15" y2="18"></line><line x1="15" y1="14" x2="9" y2="18"></line></svg>',
            'calendar-off': '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>'
        };

        const $row = $('<div class="mpwem-schedule-summary__rule"></div>')
            .append($('<span class="mpwem-schedule-summary__rule-icon" aria-hidden="true"></span>').html(icons[iconKey] || ''))
            .append($('<span class="mpwem-schedule-summary__rule-text"></span>').text(text));

        if (chips && chips.length) {
            const $chips = $('<span class="mpwem-schedule-summary__chips"></span>');
            chips.forEach(function(label) {
                $chips.append($('<span class="mpwem-schedule-summary__chip"></span>').text(label));
            });
            $row.append($chips);
        }

        $rules.append($row);
    }

    function renderParticularDateSummary($root) {
        const context = getParticularDateModalContext($root);
        if (!context.$summaryList.length) {
            return;
        }

        // The inline add/edit card lives inside the list, positioned right
        // before the "+ Add Schedule"/"+ Add Particular Date" button -
        // detach it (not remove/empty) before clearing the list so an open
        // card survives the rebuild, then reinsert it in the same spot
        // wherever the button ends up below.
        const $openAddCard = context.$summaryList.find('#mpwem_particular_date_summary_add').detach();

        const type = getActiveDateModalType($root);

        // Keeps the Live Ticket Preview card's date line in sync regardless
        // of which branch below this runs (type switch, empty state, or a
        // populated list), since all of them share this one entry point.
        updateLiveTicketPreviewDate($root, type);
        updateScheduleSummaryCard($root, type, context);

        if (type === 'everyday') {
            /* Repeated Event's real fields now live inline in the step
               (see initializeParticularDateModal) instead of behind a
               modal, so there's nothing left for this summary card to
               summarize or open - hide it entirely. */
            context.$summary.hide();
            context.$summaryList.empty();
            if ($openAddCard.length) {
                context.$summaryList.append($openAddCard);
            }
            return;
        }

        context.$summary.show();

        const config = getDateModalTypeConfig(type);
        const $modalMount = context.$modalMount.length ? context.$modalMount : getSavedDateSummaryMount($root);
        renderDateModalSummaryActions($root, context, type);
        context.$summaryList.empty();

        if (type === 'yes') {
            const $rows = getParticularDateRows($root);

            const primaryDateVal = ($modalMount.find('input[name="event_start_date"]').first().val() || '').toString().trim();
            const primaryTimeVal = ($modalMount.find('input[name="event_start_time"]').first().val() || '').toString().trim();
            const primaryEndDateVal = ($modalMount.find('input[name="event_end_date"]').first().val() || '').toString().trim();
            const primaryEndTimeVal = ($modalMount.find('input[name="event_end_time"]').first().val() || '').toString().trim();

            if (!primaryDateVal && !$rows.length) {
                // No header row here either - nothing to label yet.
                context.$summaryList.append(
                    $('<div class="mpwem-ticket-summary__empty mpwem-ticket-summary__empty--centered"></div>')
                        .append(
                            $('<span class="mpwem-ticket-summary__empty-icon" aria-hidden="true"></span>')
                                .html('<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="3"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>')
                        )
                        .append($('<h4></h4>').text('No dates added yet'))
                        .append($('<p></p>').text('Add specific dates and times for this event to run on.'))
                        .append(
                            $('<button type="button" class="button button-primary"></button>')
                                .attr('data-mpwem-date-modal-open', 'new')
                                .html('<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg> Create First Date')
                        )
                );
                if ($openAddCard.length) {
                    context.$summaryList.append($openAddCard);
                }
                return;
            }

            context.$summaryList.append(
                $('<div class="mpwem-ticket-summary__header"></div>')
                    .append($('<span class="mpwem-ticket-summary__header-ticket">Start Date</span>'))
                    .append($('<span class="mpwem-ticket-summary__header-capacity">End Date</span>'))
                    .append($('<span class="mpwem-ticket-summary__header-action">Action</span>'))
            );

            if (primaryDateVal) {
                const primaryDisplayStart = buildScheduleDateTimeLabel(primaryDateVal, primaryTimeVal) || 'Start date not set';
                const primaryDisplayEnd = buildScheduleDateTimeLabel(primaryEndDateVal, primaryEndTimeVal) || 'End date not set';

                context.$summaryList.append(
                    $('<article class="mpwem-ticket-summary__item"></article>')
                        .attr('data-date-row-index', -1)
                        .append(
                            $('<div class="mpwem-ticket-summary__item-main"></div>')
                                .append(
                                    $('<div class="mpwem-ticket-summary__item-head"></div>')
                                        .append($('<h4></h4>').text(primaryDisplayStart))
                                )
                        )
                        .append(
                            $('<div class="mpwem-ticket-summary__meta"></div>')
                                .append($('<span class="mpwem-ticket-summary__datetime"></span>').text(primaryDisplayEnd))
                        )
                        .append(buildDateSummaryActions($root, type, -1, true))
                );
            }

            $rows.each(function(index) {
                const $row = $(this);
                const dateVal = ($row.find('input[name="event_more_start_date[]"]').first().val() || '').toString().trim();
                const timeVal = ($row.find('input[name="event_more_start_time[]"]').first().val() || '').toString().trim();
                const endDateVal = ($row.find('input[name="event_more_end_date[]"]').first().val() || '').toString().trim();
                const endTimeVal = ($row.find('input[name="event_more_end_time[]"]').first().val() || '').toString().trim();

                const displayStart = buildScheduleDateTimeLabel(dateVal, timeVal) || 'Start date not set';
                const displayEnd = buildScheduleDateTimeLabel(endDateVal, endTimeVal) || 'End date not set';

                context.$summaryList.append(
                    $('<article class="mpwem-ticket-summary__item"></article>')
                        .attr('data-date-row-index', index)
                        .append(
                            $('<div class="mpwem-ticket-summary__item-main"></div>')
                                .append(
                                    $('<div class="mpwem-ticket-summary__item-head"></div>')
                                        .append($('<h4></h4>').text(displayStart))
                                )
                        )
                        .append(
                            $('<div class="mpwem-ticket-summary__meta"></div>')
                                .append($('<span class="mpwem-ticket-summary__datetime"></span>').text(displayEnd))
                        )
                        .append(buildDateSummaryActions($root, type, index, false))
                );
            });

            // "+ Add Particular Date" renders after every row, same as
            // Single Event's "+ Add Schedule".
            if (config.secondaryAction) {
                const $addDateBtn = $('<button type="button" class="button button-primary mpwem-date-primary-fields__add"></button>')
                    .attr('data-mpwem-date-modal-open', 'new')
                    .text(config.secondaryAction);
                context.$summaryList.append($addDateBtn);
                if ($openAddCard.length) {
                    $openAddCard.insertBefore($addDateBtn);
                }
            } else if ($openAddCard.length) {
                context.$summaryList.append($openAddCard);
            }

            return;
        }

        /* Only "no" (Single Event) reaches here - "yes" returned above,
           "everyday" returned at the top of this function. The primary
           Start/End row reads like any other summary row (Edit only, no
           delete since it can't be removed) instead of rendering its
           fields always-editable; its own inline edit card (opened via
           the Edit button, same one "+ Add Schedule" opens) is the only
           place those fields are actually editable. */
        const items = collectSingleEventSummaryItems($modalMount);
        const primaryItem = items.find(function(item) { return item.isPrimary; });
        const extraItems = items.filter(function(item) {
            return !item.isPrimary;
        });

        if (!primaryItem && !extraItems.length) {
            // Without this, the step showed nothing but a bare
            // "+ Add Schedule" button with no explanation of what was
            // missing. Only shown when there is truly nothing yet -
            // "+ Add Schedule" adds a row via the real add-item button
            // (see openInlineDateAdd), which can land as an additional
            // date even while the primary stays unset, same as
            // Particular Dates' "Create First Date" already allows.
            context.$summaryList.append(
                $('<div class="mpwem-ticket-summary__empty mpwem-ticket-summary__empty--centered"></div>')
                    .append(
                        $('<span class="mpwem-ticket-summary__empty-icon" aria-hidden="true"></span>')
                            .html('<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="3"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>')
                    )
                    .append($('<h4></h4>').text('No schedule set yet'))
                    .append($('<p></p>').text('Add a start and end date & time so people know when this event happens.'))
                    .append(
                        $('<button type="button" class="button button-primary"></button>')
                            .attr('data-mpwem-date-modal-open', 'new')
                            .html('<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg> Add Schedule')
                    )
            );
            if ($openAddCard.length) {
                context.$summaryList.append($openAddCard);
            }
            return;
        }

        // Shown by default as soon as there's a primary date and/or any
        // extra dates - not gated behind extraItems.length, so it's visible
        // from the very first row instead of only once a 2nd date exists.
        context.$summaryList.append(
            $('<div class="mpwem-ticket-summary__header"></div>')
                .append($('<span class="mpwem-ticket-summary__header-ticket"></span>').text('Start Date'))
                .append($('<span class="mpwem-ticket-summary__header-capacity"></span>').text('End Date'))
                .append($('<span class="mpwem-ticket-summary__header-action"></span>').text('Action'))
        );

        if (primaryItem) {
            context.$summaryList.append(
                $('<article class="mpwem-ticket-summary__item"></article>')
                    .attr('data-date-row-index', -1)
                    .append(
                        $('<div class="mpwem-ticket-summary__item-main"></div>')
                            .append(
                                $('<div class="mpwem-ticket-summary__item-head"></div>')
                                    .append($('<h4></h4>').text(primaryItem.startLabel))
                            )
                    )
                    .append(
                        $('<div class="mpwem-ticket-summary__meta"></div>')
                            .append($('<span class="mpwem-ticket-summary__datetime"></span>').text(primaryItem.endLabel))
                    )
                    .append(buildDateSummaryActions($root, type, -1, true))
            );
        }

        if (extraItems.length) {
            extraItems.forEach(function(item) {
                context.$summaryList.append(
                    $('<article class="mpwem-ticket-summary__item"></article>')
                        .append(
                            $('<div class="mpwem-ticket-summary__item-main"></div>')
                                .append(
                                    $('<div class="mpwem-ticket-summary__item-head"></div>')
                                        .append($('<h4></h4>').text(item.startLabel))
                                )
                        )
                        .append(
                            $('<div class="mpwem-ticket-summary__meta"></div>')
                                .append($('<span class="mpwem-ticket-summary__datetime"></span>').text(item.endLabel))
                        )
                        .append(buildDateSummaryActions($root, type, item.rowIndex, false))
                );
            });
        }

        // "+ Add Schedule" renders after every row (the primary one and
        // any additional dates), not between them.
        if (config.secondaryAction) {
            const $addScheduleBtn = $('<button type="button" class="button button-primary mpwem-date-primary-fields__add"></button>')
                .attr('data-mpwem-date-modal-open', 'new')
                .text(config.secondaryAction);
            context.$summaryList.append($addScheduleBtn);
            if ($openAddCard.length) {
                $openAddCard.insertBefore($addScheduleBtn);
            }
        } else if ($openAddCard.length) {
            context.$summaryList.append($openAddCard);
        }
    }

    function getDateFieldNames(type) {
        return type === 'no'
            ? {
                primary: { start: 'event_start_date_normal', startTime: 'event_start_time_normal', end: 'event_end_date_normal', endTime: 'event_end_time_normal' },
                row: { start: 'event_more_start_date_normal[]', startTime: 'event_more_start_time_normal[]', end: 'event_more_end_date_normal[]', endTime: 'event_more_end_time_normal[]' }
            }
            : {
                primary: { start: 'event_start_date', startTime: 'event_start_time', end: 'event_end_date', endTime: 'event_end_time' },
                row: { start: 'event_more_start_date[]', startTime: 'event_more_start_time[]', end: 'event_more_end_date[]', endTime: 'event_more_end_time[]' }
            };
    }

    function buildPrimaryDateTimePill($realDateInput, $realTimeInput) {
        const isoDate = ($realDateInput.val() || '').toString();
        const parsedDate = parseIsoDate(isoDate);
        const visibleDate = parsedDate ? formatVisibleDate(parsedDate) : '';

        const $dateHidden = $('<input type="hidden">').val(isoDate).data('mpwemRealDate', $realDateInput);
        const $dateVisible = $('<input type="text" class="formControl mpwem-date-input" autocomplete="off">')
            .val(visibleDate)
            .data('mpwemRealDate', $realDateInput)
            .attr('placeholder', formatVisibleDate(new Date()));
        const $dateWrap = $('<label class="mpwem-date-input-wrap"></label>')
            .append($dateHidden, $dateVisible)
            .append($('<span class="fas fa-times remove_icon mpwem_date_reset" aria-hidden="true" title="Clear date"></span>'));

        $dateHidden.on('change', function() {
            syncRealFieldValue($realDateInput, $(this).val());
        });

        const $timeInput = $('<input type="time" class="formControl" placeholder="Select time">').val(($realTimeInput.val() || '').toString()).data('mpwemRealTime', $realTimeInput);
        const $timeWrap = $('<label class="mpwem-time-input-wrap"></label>')
            .append($timeInput)
            .append($('<span class="fas fa-times remove_icon mpwem_time_reset" aria-hidden="true" title="Clear time"></span>'));

        $timeInput.on('change', function() {
            syncRealFieldValue($realTimeInput, $(this).val());
        });

        const $pill = $('<div class="dFlex mpwem-repeat-dt-pill"></div>').append($dateWrap, $timeWrap);
        // Wires up the same custom calendar/time-picker popups used
        // everywhere else in the wizard (incl. the reset ✕ buttons,
        // handled by the existing global delegated click handlers) -
        // this proxy never holds a real field `name`, so it's never
        // submitted; it only mirrors value changes onto the real
        // start/end date & time inputs via the listeners above.
        enhanceCustomCalendar($pill);

        return $pill;
    }

    function getInlineDateAddCard($root) {
        const context = getParticularDateModalContext($root);
        if (!context.$summary.length) {
            return $();
        }

        let $card = $root.find('#mpwem_particular_date_summary_add').first();
        if (!$card.length) {
            $card = $('<div class="mpwem-ticket-add" id="mpwem_particular_date_summary_add"></div>');
            const $addBtn = context.$summaryList.find('.mpwem-date-primary-fields__add[data-mpwem-date-modal-open="new"]').last();
            if ($addBtn.length) {
                $card.insertBefore($addBtn);
            } else {
                context.$summaryList.append($card);
            }
        }

        return $card;
    }

    function closeInlineDateAdd($root, opts) {
        opts = opts || {};
        const $card = getInlineDateAddCard($root);
        const $scope = $card.data('mpwemInlineDateScope');
        const $row = $card.data('mpwemInlineDateRow');
        const mode = $card.data('mpwemInlineDateMode') || 'add';
        const original = $card.data('mpwemInlineDateOriginal');
        const fieldNames = $card.data('mpwemInlineDateFieldNames');

        if (!opts.save) {
            if (mode === 'add') {
                if ($row && $row.length && $row.closest('body').length) {
                    $row.remove();
                }
            } else if (original && fieldNames && $scope && $scope.length) {
                Object.keys(fieldNames).forEach(function(key) {
                    const $field = $scope.find('[name="' + fieldNames[key] + '"]').first();
                    if ($field.length && Object.prototype.hasOwnProperty.call(original, key)) {
                        syncRealFieldValue($field, original[key]);
                    }
                });
            }
        }

        $card.removeData('mpwemInlineDateScope mpwemInlineDateRow mpwemInlineDateMode mpwemInlineDateOriginal mpwemInlineDateFieldNames').removeClass('is-open').empty();

        initializeParticularDateTableDragScroll($root);
        syncDateWiseGlobalQtyColumns($root);
        syncParticularDateModalFooter($root);
        storeSavedParticularDateSummarySnapshot($root);
        renderParticularDateSummary($root);

        // "+ Add Schedule" always appends a brand-new row, so after the
        // rebuild above it's the last item in the summary list - bring it
        // into view and give it the same soft reveal glow as the
        // "Add Event Date" form itself, instead of leaving the user to
        // scan the whole list for what they just saved.
        if (opts.save && mode === 'add') {
            const context = getParticularDateModalContext($root);
            const $newItem = context.$summaryList.find('.mpwem-ticket-summary__item').last();
            if ($newItem.length) {
                scrollToViewportCenter($newItem, 300);
                $newItem.removeClass('mpwem-date-add-reveal');
                void $newItem[0].offsetWidth;
                $newItem.addClass('mpwem-date-add-reveal');
                window.setTimeout(function() {
                    $newItem.removeClass('mpwem-date-add-reveal');
                }, 1600);
            }
        }
    }

    function renderInlineDateAddForm($root, opts) {
        const type = opts.type;
        const mode = opts.mode;
        const $scope = opts.scope;
        const fieldNames = opts.fieldNames;
        const isEdit = mode !== 'add' && !opts.firstEntry;
        const $card = getInlineDateAddCard($root);
        $card.empty().addClass('is-open');

        const $startDateInput = $scope.find('[name="' + fieldNames.start + '"]').first();
        const $startTimeInput = $scope.find('[name="' + fieldNames.startTime + '"]').first();
        const $endDateInput = $scope.find('[name="' + fieldNames.end + '"]').first();
        const $endTimeInput = $scope.find('[name="' + fieldNames.endTime + '"]').first();

        const typeLabel = type === 'no' ? 'Event Date' : 'Particular Date';
        const headIcon = isEdit
            ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>'
            : '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>';

        $card.append(
            $('<div class="mpwem-ticket-add__head"></div>')
                .append($('<span class="mpwem-ticket-add__head-icon"></span>').html(headIcon))
                .append(
                    $('<div class="mpwem-ticket-add__head-copy"></div>')
                        .append(
                            $('<div class="mpwem-ticket-add__head-title"></div>')
                                .append($('<h3></h3>').text((isEdit ? 'Edit ' : 'Add ') + typeLabel))
                        )
                        .append($('<p></p>').text(isEdit ? 'Update the start and end date & time.' : 'Set the start and end date & time for this schedule.'))
                )
        );

        const $body = $('<div class="mpwem-ticket-add__body"></div>');

        const $startPill = buildPrimaryDateTimePill($startDateInput, $startTimeInput);
        const $endPill = buildPrimaryDateTimePill($endDateInput, $endTimeInput);
        // Start time -> End date auto-open (see openEndDateAfterStartTime).
        $startPill.find('input[type="time"]').data('mpwemEndDateInput', $endPill.find('.mpwem-date-input').first());

        $body.append(
            $('<div class="mpwem-ticket-add__grid"></div>')
                .append(
                    $('<div class="mpwem-ticket-add__group mpwem-ticket-add__group--date"></div>')
                        .append($('<label></label>').text('Start Date'))
                        .append($startPill)
                )
                .append(
                    $('<div class="mpwem-ticket-add__group mpwem-ticket-add__group--date"></div>')
                        .append($('<label></label>').text('End Date'))
                        .append($endPill)
                )
        );

        $card.append($body);

        const $footer = $('<div class="mpwem-ticket-add__footer"></div>');
        const $cancelBtn = $('<button type="button" class="button"></button>').text('Cancel');
        const $saveBtn = $('<button type="button" class="button button-primary mpwem-ticket-add__save"></button>').text(isEdit ? 'Save Changes' : 'Save & Add More');
        $footer.append($cancelBtn);
        let $saveCloseBtn = $();
        if (!isEdit) {
            $saveCloseBtn = $('<button type="button" class="button button-primary mpwem-ticket-add__save-close"></button>').text('Save & Close');
            $footer.append($saveCloseBtn);
        }
        $footer.append($saveBtn);
        $card.append($footer);

        $startPill.find('.mpwem-date-input-wrap input[type="hidden"]').on('change', function() {
            $startPill.removeClass('has-error');
        });
        $endPill.find('.mpwem-date-input-wrap input[type="hidden"]').on('change', function() {
            $endPill.removeClass('has-error');
        });

        $cancelBtn.on('click', function() {
            closeInlineDateAdd($root, { save: false });
        });

        const saveDate = function(addMore) {
            const startOk = !!($startPill.find('.mpwem-date-input-wrap input[type="hidden"]').val() || '').toString();
            const endOk = !!($endPill.find('.mpwem-date-input-wrap input[type="hidden"]').val() || '').toString();
            if (!startOk || !endOk) {
                $startPill.toggleClass('has-error', !startOk);
                $endPill.toggleClass('has-error', !endOk);
                (startOk ? $endPill : $startPill).find('.mpwem-date-input').first().trigger('focus');
                return;
            }

            // Same-day schedule: the end time must be after the start time.
            const $endTime = $endPill.find('input[type="time"]').first();
            if (getEndTimeMinimum($endTime) !== null) {
                const endMin = timeToMinutes($endTime.val());
                if (endMin !== null && endMin <= getEndTimeMinimum($endTime)) {
                    $endPill.addClass('has-error');
                    showToast('End time must be after the start time on the same day.', 'error');
                    return;
                }
            }

            closeInlineDateAdd($root, { save: true });
            if (addMore) {
                window.setTimeout(function() {
                    openInlineDateAdd($root);
                }, 200);
            }
        };

        $saveBtn.on('click', function() { saveDate(!isEdit); });
        $saveCloseBtn.on('click', function() { saveDate(false); });
    }

    function openInlineDateAdd($root) {
        const $card = getInlineDateAddCard($root);
        if ($card.hasClass('is-open')) {
            const wasAdding = $card.data('mpwemInlineDateMode') === 'add';
            closeInlineDateAdd($root, { save: false });
            if (wasAdding) {
                return;
            }
        }

        const type = getActiveDateModalType($root);
        if (type !== 'no' && type !== 'yes') {
            return;
        }

        const $activeSection = getActiveDateModalSection($root, type);

        // The event's main Start/End fields (event_start_date_normal etc.) are
        // what publish requires. While they are still empty, the first schedule
        // belongs in them - "Add" would otherwise only create an extra
        // "more dates" row and leave the main date unset.
        const primaryStartName = getDateFieldNames(type).primary.start;
        const primaryStartVal = ($activeSection.find('[name="' + primaryStartName + '"]').first().val() || '').toString().trim();
        if (!primaryStartVal) {
            openInlineDateEdit($root, type, -1, { firstEntry: true });
            return;
        }

        const $addButton = $activeSection.find('.mpwem_add_item, .mp_add_item').first();
        if (!$addButton.length) {
            return;
        }

        try {
            $addButton.trigger('click');
        } catch (err) {
            /* A shared add-item handler may throw (e.g. a dependency not yet
               loaded) after it has already appended the row; don't let that
               stop the inline form below from rendering. */
        }

        window.setTimeout(function() {
            enhanceDateFields($root);
            enhanceCustomCalendar($root);
            syncDateWiseGlobalQtyColumns($root);

            const $row = getActiveDateModalRows($root, type).last();
            if (!$row.length) {
                return;
            }

            const fieldNames = getDateFieldNames(type).row;
            $card.data('mpwemInlineDateScope', $row);
            $card.data('mpwemInlineDateRow', $row);
            $card.data('mpwemInlineDateMode', 'add');
            $card.data('mpwemInlineDateFieldNames', fieldNames);
            renderInlineDateAddForm($root, { mode: 'add', type: type, scope: $row, fieldNames: fieldNames });

            // "+ Add Schedule" can open this card well below the fold -
            // bring it into view and give it a soft highlight so it's
            // obvious where the new "Add Event Date" form landed. Not
            // flashAttention()/.mpwem-attention-highlight: that one is
            // red, meant for validation errors, and would read as a
            // problem here rather than a normal reveal.
            scrollToViewportCenter($card, 300);
            $card.removeClass('mpwem-date-add-reveal');
            void $card[0].offsetWidth;
            $card.addClass('mpwem-date-add-reveal');
            window.setTimeout(function() {
                $card.removeClass('mpwem-date-add-reveal');
            }, 1600);
            $card.find('.mpwem-ticket-add__group--date').first().find('.mpwem-date-input').trigger('focus');
        }, 100);
    }

    function openInlineDateEdit($root, type, rowIndex, editOptions) {
        const $card = getInlineDateAddCard($root);
        const names = getDateFieldNames(type);
        const context = getParticularDateModalContext($root);
        const isPrimary = rowIndex === -1 || rowIndex === null || typeof rowIndex === 'undefined';

        let $scope;
        let fieldNames;
        let $row = null;

        if (isPrimary) {
            const $activeSection = getActiveDateModalSection($root, type);
            $scope = $activeSection.length ? $activeSection : context.$modalMount;
            fieldNames = names.primary;
        } else {
            $row = getActiveDateModalRows($root, type).eq(rowIndex);
            if (!$row.length) {
                return;
            }
            $scope = $row;
            fieldNames = names.row;
        }

        const editingSame = $card.hasClass('is-open')
            && $card.data('mpwemInlineDateMode') !== 'add'
            && $card.data('mpwemInlineDateScope')
            && $card.data('mpwemInlineDateScope')[0] === $scope[0];

        if ($card.hasClass('is-open')) {
            closeInlineDateAdd($root, { save: false });
            if (editingSame) {
                return;
            }
        }

        const original = {};
        Object.keys(fieldNames).forEach(function(key) {
            const $field = $scope.find('[name="' + fieldNames[key] + '"]').first();
            original[key] = ($field.val() || '').toString();
        });

        const mode = isPrimary ? 'edit-primary' : 'edit-row';
        $card.data('mpwemInlineDateScope', $scope);
        $card.data('mpwemInlineDateRow', $row);
        $card.data('mpwemInlineDateMode', mode);
        $card.data('mpwemInlineDateOriginal', original);
        $card.data('mpwemInlineDateFieldNames', fieldNames);

        renderInlineDateAddForm($root, { mode: mode, type: type, scope: $scope, fieldNames: fieldNames, firstEntry: !!(editOptions && editOptions.firstEntry) });
    }

    function openParticularDateModal($root, mode, rowIndex) {
        const context = getParticularDateModalContext($root);
        if (!context.$modal.length) {
            return;
        }

        beginParticularDateModalSession($root);
        const activeType = getActiveDateModalType($root);
        const config = getDateModalTypeConfig(activeType);
        const $activeSection = getActiveDateModalSection($root, activeType);
        const $dateModeSections = context.$modalMount.children('[data-collapse="#mep_normal_event"], [data-collapse="#mep_particular_event"], [data-collapse="#mep_everyday_event"]');

        $dateModeSections.removeClass('mActive mpwem-date-mode-active').hide();
        if ($activeSection.length) {
            $activeSection.addClass('mActive mpwem-date-mode-active').show();
        }
        initializeParticularDateTableDragScroll($root);
        syncDateWiseGlobalQtyColumns($root);
        enhanceDateFields(context.$modalMount);
        enhanceCustomCalendar(context.$modalMount);

        context.$modal.attr('aria-hidden', 'false').addClass('is-open');
        lockBodyScroll();

        const isNew = mode === 'new' && activeType === 'yes';
        $root.find('#mpwem_particular_date_modal_title').text(isNew ? (config.modalNewTitle || config.modalTitle) : config.modalTitle);
        $root.find('#mpwem_particular_date_modal_description').text(
            isNew
                ? (config.modalNewDescription || config.modalDescription)
                : config.modalDescription
        );

        if (isNew) {
            addDateModalRow($root, activeType);
            return;
        }

        if (typeof rowIndex === 'number' && rowIndex >= 0) {
            const $row = getParticularDateRows($root).eq(rowIndex);
            window.setTimeout(function() {
                const $container = $row.closest('.mpwem-ticket-modal__body');
                if ($container.length) {
                    const top = $row.position().top + $container.scrollTop() - 24;
                    $container.animate({ scrollTop: Math.max(top, 0) }, 250);
                }
                $row.addClass('mpwem-ticket-row-focus');
                window.setTimeout(function() {
                    $row.removeClass('mpwem-ticket-row-focus');
                }, 1800);
            }, 60);
        }
    }

    function closeParticularDateModal($root) {
        const context = getParticularDateModalContext($root);
        if (!context.$modal.length) {
            return;
        }

        discardParticularDateDraftRows($root);
        context.$modal.attr('aria-hidden', 'true').removeClass('is-open');
        unlockBodyScroll();
    }

    function syncParticularDateModalFooter($root) {
        const context = getParticularDateModalContext($root);

        if (!context.$footerStart.length) {
            return;
        }

        let $footerAdd = context.$footerStart.find('.mpwem-date-modal__add').first();
        if (!$footerAdd.length) {
            $footerAdd = $('<button type="button" class="button button-link mpwem-date-modal__add"></button>');
            context.$footerStart.append($footerAdd);
        }

        const activeType = getActiveDateModalType($root);
        const $activeSection = getActiveDateModalSection($root, activeType);
        const addLabel = $.trim($activeSection.find('.mpwem_add_item, .mp_add_item').first().text());
        const shouldShowAdd = (activeType === 'no' || activeType === 'yes') && !!addLabel;

        $footerAdd
            .text(addLabel || 'Add New Row')
            .toggle(shouldShowAdd);

        context.$footerStart.toggle(shouldShowAdd);
        if (!shouldShowAdd) {
            context.$footerStart.hide();
        }
    }

    function initializeParticularDateModal($root) {
        const context = getParticularDateModalContext($root);
        
        if (!context.$legacyMounts.length || !context.$modalMount.length) {
            return;
        }

        context.$legacyMounts.each(function() {
            const $section = $(this);
            if ($section.parent()[0] !== context.$modalMount[0]) {
                context.$modalMount.append($section.detach());
            }
            $section.show();
        });

        context.$modalMount.find('table').not('.ui-datepicker-calendar').addClass('mpwem-date-table');
        context.$modalMount.find('.mpwem_add_new_button_area').addClass('mpwem-ticket-modal__inline-actions');
        context.$modalMount.find('.mpwem_add_new_button_area').hide();
        context.$modalMount.find('[data-collapse] > ._layout_default_xs_mp_zero > ._bg_light_padding').hide();
        context.$modalMount.find('section.bg-light').hide();
        context.$modalMount.find('.mep-special-datetime section.bg-light').hide();


        decorateDateSections(context.$modalMount);
        enhanceDateFields(context.$modalMount);
        enhanceCustomCalendar(context.$modalMount);
        enhanceOffDayPicker(context.$modalMount);
        enhanceRepeatedScheduleLayout(context.$modalMount);
        ensureParticularDateTableHints($root);
        initializeParticularDateTableDragScroll($root);
        syncDateWiseGlobalQtyColumns($root);
        storeSavedParticularDateSummarySnapshot($root);
        renderParticularDateSummary($root);
        syncParticularDateModalFooter($root);

        context.$modalMount.find('tbody.mpwem_item_insert').each(function() {
            const $tbody = $(this);
            if ($tbody.data('mpwemDateSummaryObserver')) {
                return;
            }
            const observer = new MutationObserver(function() {
                syncDateWiseGlobalQtyColumns($root);
                renderParticularDateSummary($root);
            });
            observer.observe($tbody[0], { childList: true, subtree: true });
            $tbody.data('mpwemDateSummaryObserver', observer);
        });

        $root.off('.mpwemDateModal');

        // Clicking anywhere on a saved ticket / extra service / schedule row
        // opens it for editing, same as its pen icon (buttons, the drag
        // handle and the status toggle keep their own behaviour).
        $root.on('click.mpwemSummaryRowEdit', '.mpwem-ticket-summary__list > .mpwem-ticket-summary__item', function(e) {
            if ($(e.target).closest('.mpwem-ticket-summary__item-actions, button, a, input, select, textarea, label').length) {
                return;
            }
            $(this).find('.mpwem-ticket-summary__action-btn--edit').first().trigger('click');
        });

        $root.on('click.mpwemDateModal', '[data-mpwem-date-modal-open]', function(e) {
            e.preventDefault();
            const mode = $(this).attr('data-mpwem-date-modal-open') || 'list';

            if (mode === 'new') {
                const activeType = getActiveDateModalType($root);
                if (activeType === 'no' || activeType === 'yes') {
                    openInlineDateAdd($root);
                    return;
                }
            }

            const rowIndex = parseInt($(this).attr('data-date-row-index'), 10);
            openParticularDateModal($root, mode, Number.isNaN(rowIndex) ? null : rowIndex);
        });

        $root.on('click.mpwemDateModal', '[data-mpwem-date-modal-close]', function(e) {
            e.preventDefault();
            closeParticularDateModal($root);
        });

        $root.on('click.mpwemDateModal', '.mpwem-date-modal__add', function(e) {
            e.preventDefault();
            addDateModalRow($root);
        });

        $root.on('click.mpwemDateModal', '.mpwem-date-modal__save', function(e) {
            e.preventDefault();
            const $button = $(this);

            if ($button.prop('disabled')) {
                return;
            }

            if (!validateDateWiseGlobalQty($root, { focusStep: true })) {
                return;
            }

            persistParticularDateDraftRows($root);
            $button.prop('disabled', true).addClass('is-saving');
            submitEventForm($root, '', { skipOtherSteps: true });

            window.setTimeout(function() {
                if ($button.closest('body').length) {
                    $button.prop('disabled', false).removeClass('is-saving');
                }
            }, 5000);
        });

        $root.on('input.mpwemDateModal change.mpwemDateModal', '#mpwem_particular_date_modal_mount input', function() {
            renderParticularDateSummary($root);
        });

        // Repeated Event's own date-range, interval, and skip-day fields
        // render inline in the step (not inside the modal mount above), so
        // they need their own listener to keep the Schedule Summary
        // sidebar card live too.
        $root.on(
            'input.mpwemDateModal change.mpwemDateModal',
            '[data-collapse="#mep_everyday_event"] input[name="event_start_date_everyday"], ' +
            '[data-collapse="#mep_everyday_event"] input[name="event_start_time_everyday"], ' +
            '[data-collapse="#mep_everyday_event"] input[name="event_end_date_everyday"], ' +
            '[data-collapse="#mep_everyday_event"] input[name="event_end_time_everyday"], ' +
            '[data-collapse="#mep_everyday_event"] input[name="mep_repeated_periods"], ' +
            '[data-collapse="#mep_everyday_event"] .mpwem-offday-option input[type="checkbox"], ' +
            '[data-collapse="#mep_everyday_event"] input[name="mep_ticket_off_dates[]"]',
            function() {
                window.setTimeout(function() {
                    updateScheduleSummaryCard($root, 'everyday');
                }, 20);
            }
        );

        // Adding/removing a one-off skip date doesn't fire change on an
        // existing field, so it needs its own click listener too.
        $root.on(
            'click.mpwemDateModal',
            '[data-collapse="#mep_everyday_event"] .mpwem_add_item, [data-collapse="#mep_everyday_event"] .mp_add_item, [data-collapse="#mep_everyday_event"] .mpwem_item_remove',
            function() {
                window.setTimeout(function() {
                    updateScheduleSummaryCard($root, 'everyday');
                }, 280);
            }
        );

        $root.on('change.mpwemDateModal', 'select[name="mep_enable_recurring"]', function() {
            window.setTimeout(function() {
                syncParticularDateModalFooter($root);
                renderParticularDateSummary($root);
            }, 20);
        });

        $root.on('change.mpwemDateModal', 'input[name="enable_global_qty"], select[name="mep_gq_type"]', function() {
            window.setTimeout(function() {
                syncDateWiseGlobalQtyColumns($root);
                syncGlobalQtyTypeWarning($root);
            }, 20);
        });

        $root.on('click.mpwemDateModal', '#mpwem_particular_date_modal_mount .mpwem_item_remove, #mpwem_particular_date_modal_mount .mp_add_item, #mpwem_particular_date_modal_mount .mpwem_add_item', function() {
            window.setTimeout(function() {
                initializeParticularDateTableDragScroll($root);
                syncDateWiseGlobalQtyColumns($root);
                syncParticularDateModalFooter($root);
                renderParticularDateSummary($root);
            }, 280);
        });

        $(document)
            .off('keydown.mpwemDateModal')
            .on('keydown.mpwemDateModal', function(e) {
                if (e.key === 'Escape' && context.$modal.hasClass('is-open')) {
                    closeParticularDateModal($root);
                }
            });
    }

    function initializeParticularDateTableDragScroll($root) {
        const context = getParticularDateModalContext($root);
        const blockedSelector = 'input, textarea, select, button, a, .mpwem-select-wrapper, .ui-datepicker, .wp-picker-container';
        context.$modalMount.find('._ov_auto').each(function() {
            const $scroller = $(this);
            if ($scroller.data('mpwemDateDragScrollInit')) {
                return;
            }

            let isPointerDown = false;
            let isDragging = false;
            let startX = 0;
            let startScrollLeft = 0;
            let dragBlocked = false;
            let hasHorizontalOverflow = false;
            let suppressClick = false;

            $scroller.on('mousedown.mpwemDateDragScroll', function(e) {
                if (e.button !== 0) {
                    return;
                }

                hasHorizontalOverflow = this.scrollWidth > this.clientWidth + 2;
                if (!hasHorizontalOverflow) {
                    return;
                }

                isPointerDown = true;
                dragBlocked = $(e.target).closest(blockedSelector).length > 0;
                isDragging = false;
                suppressClick = false;
                startX = e.pageX;
                startScrollLeft = this.scrollLeft;

                if (!dragBlocked) {
                    e.preventDefault();
                }
            });

            $(document).on('mousemove.mpwemDateDragScroll', function(e) {
                if (!isPointerDown || dragBlocked || !hasHorizontalOverflow) {
                    return;
                }

                const deltaX = e.pageX - startX;
                if (!isDragging) {
                    if (Math.abs(deltaX) < 8) {
                        return;
                    }

                    isDragging = true;
                    suppressClick = true;
                    $scroller.addClass('is-dragging');
                    $('body').addClass('mpwem-drag-scroll-active');

                    const activeElement = document.activeElement;
                    if (activeElement && $scroller.has(activeElement).length) {
                        activeElement.blur();
                    }
                }

                $scroller.scrollLeft(startScrollLeft - deltaX);
                e.preventDefault();
            });

            $(document).on('mouseup.mpwemDateDragScroll mouseleave.mpwemDateDragScroll', function() {
                isPointerDown = false;
                dragBlocked = false;
                hasHorizontalOverflow = false;
                isDragging = false;
                $scroller.removeClass('is-dragging');
                $('body').removeClass('mpwem-drag-scroll-active');
            });

            $scroller.on('click.mpwemDateDragScroll', function(e) {
                if (!suppressClick) {
                    return;
                }

                suppressClick = false;
                e.preventDefault();
                e.stopPropagation();
            });

            $scroller.on('wheel.mpwemDateDragScroll', function(e) {
                const originalEvent = e.originalEvent;
                if (!originalEvent) {
                    return;
                }

                const horizontalDelta = Math.abs(originalEvent.deltaX || 0);
                const verticalDelta = Math.abs(originalEvent.deltaY || 0);
                const canScrollHorizontally = this.scrollWidth > this.clientWidth + 2;

                if (!canScrollHorizontally) {
                    return;
                }

                if (verticalDelta > horizontalDelta) {
                    this.scrollLeft += originalEvent.deltaY;
                    e.preventDefault();
                }
            });

            $scroller.data('mpwemDateDragScrollInit', true);
        });
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
                mount: '#mpwem_wizard_attendee_form_mount',
                className: 'mpwem-display-section--attendee-form',
                title: 'Attendee Form',
                desc: 'Choose registration fields and attendee form behavior for this event.',
                icon: 'dashicons-id'
            },
            {
                mount: '#mpwem_wizard_terms_mount',
                className: 'mpwem-display-section--terms',
                title: 'Terms & Conditions',
                desc: 'Show the attendee-facing terms content for this event.',
                icon: 'dashicons-media-text'
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
                mount: '#mpwem_wizard_email_reminder_mount',
                className: 'mpwem-display-section--email-reminder',
                title: 'Email Reminder',
                desc: 'Schedule attendee reminder emails without changing the addon settings flow.',
                icon: 'dashicons-clock'
            },
            {
                mount: '#mpwem_wizard_pdf_custom_text_mount',
                className: 'mpwem-display-section--pdf-custom-text',
                title: 'PDF Custom Text',
                desc: 'Manage the custom text used in PDF ticket output.',
                icon: 'dashicons-media-document'
            },
            {
                mount: '#mpwem_wizard_seo_mount',
                className: 'mpwem-display-section--seo',
                title: 'SEO & Schema',
                desc: 'Control rich result details used by search engines.',
                icon: 'dashicons-chart-bar'
            },
            {
                mount: '#mpwem_wizard_deposit_mount',
                className: 'mpwem-display-section--deposit',
                title: 'Deposit / Partial Payment',
                desc: 'Override the global deposit setting for this specific event.',
                icon: 'dashicons-money-alt'
            },
            {
                mount: '#mpwem_wizard_settings_mount',
                className: 'mpwem-display-section--settings',
                title: 'Access & Settings',
                desc: 'Control event SKU, visibility, and member-only access.',
                icon: 'dashicons-admin-settings'
            }
        ];

        sections.forEach(function(section) {
            const $mount = $root.find(section.mount).first();
            if (!$mount.length) return;
            if (!$mount.children().length) return;

            $mount.addClass('mpwem-display-section ' + section.className);
            if (!$mount.children('.mpwem-display-section__head').length) {
                const $badge = $('<span class="mpwem-display-section__badge" aria-hidden="true"></span>')
                    .append($('<span class="dashicons"></span>').addClass(section.icon || 'dashicons-admin-generic'));
                const $headMain = $('<div class="mpwem-display-section__head-main"></div>')
                    .append($badge)
                    .append($('<h3></h3>').text(section.title))
                    .append($('<p></p>').text(section.desc));
                $mount.prepend(
                    $('<div class="mpwem-display-section__head"></div>').append($headMain)
                );
            }

            if (section.className === 'mpwem-display-section--template' && $displayMainStack.length) {
                if (!$mount.parent().is($displayMainStack) || !$mount.is($displayMainStack.children().first())) {
                    $displayMainStack.prepend($mount);
                }
            }

            if (section.className === 'mpwem-display-section--attendee-form') {
                const $head = $mount.children('.mpwem-display-section__head').first();
                let $body = $mount.children('.mpwem-display-section__body').first();

                // Attendee Form is powered by Event Booking Manager PRO — flag it as a PRO feature.
                if ($head.length && !$head.find('.mpwem-pro-badge').length) {
                    const $title = $head.find('h3').first();
                    if ($title.length) {
                        $title.append($('<span class="mpwem-pro-badge">PRO</span>'));
                    }
                }

                if (!$body.length) {
                    const $bodyChildren = $mount.children().not('.mpwem-display-section__head');
                    $body = $('<div class="mpwem-display-section__body"></div>');
                    $body.append($bodyChildren);
                    $mount.append($body);
                }

                // Find the real mep_event_reg_form_status checkbox inside the content
                const $realCheckbox = $body.find('input[name="mep_event_reg_form_status"]').first();
                const isInitiallyEnabled = $realCheckbox.length ? $realCheckbox.is(':checked') : false;

                if ($head.length && !$head.find('.mpwem-display-toggle-wrap').length) {
                    $head.append(
                        $('<label class="mpwem-switch-wrap mpwem-display-toggle-wrap" aria-label="Toggle section"></label>')
                            .append('<input type="checkbox" class="mpwem-switch-input mpwem-display-toggle" />')
                            .append('<span class="mpwem-switch-slider"></span>')
                    );
                }

                const $toggle = $head.find('.mpwem-display-toggle').first();
                const syncAttendeeFormToggle = function(isExpanded, useAnimation) {
                    $mount.toggleClass('is-collapsed', !isExpanded);
                    $mount.toggleClass('is-expanded', isExpanded);
                    $toggle.prop('checked', isExpanded).attr('aria-expanded', isExpanded ? 'true' : 'false');
                    // Keep the real checkbox in sync so the value is submitted with the form
                    if ($realCheckbox.length) {
                        $realCheckbox.prop('checked', isExpanded);
                    }

                    if (useAnimation) {
                        $body.stop(true, true)[isExpanded ? 'slideDown' : 'slideUp'](220);
                    } else {
                        $body.toggle(isExpanded);
                    }
                };

                $toggle.off('change.mpwemSectionToggle').on('change.mpwemSectionToggle', function() {
                    const isExpanded = $(this).is(':checked');
                    syncAttendeeFormToggle(isExpanded, true);
                    if (isExpanded && typeof window.mepFbEventBuilderInit === 'function') {
                        window.setTimeout(function() {
                            window.mepFbEventBuilderInit(true);
                        }, 240);
                    }
                });

                syncAttendeeFormToggle(isInitiallyEnabled, false);
                // A selected/saved form is configuration, not the enabled state. Keep
                // disabled sections closed and initialize the builder only when the
                // persisted status checkbox is on.
                if (isInitiallyEnabled && $root.find('.mpwem-step[data-step-key="display"]').hasClass('is-active')) {
                    window.setTimeout(function() {
                        if (typeof window.mepFbEventBuilderInit === 'function') {
                            window.mepFbEventBuilderInit(true);
                        }
                    }, 80);
                }
            }

            if (section.className === 'mpwem-display-section--email') {
                const $head = $mount.children('.mpwem-display-section__head').first();
                let $body = $mount.children('.mpwem-display-section__body').first();

                if (!$body.length) {
                    const $bodyChildren = $mount.children().not('.mpwem-display-section__head');
                    $body = $('<div class="mpwem-display-section__body"></div>');
                    $body.append($bodyChildren);
                    $mount.append($body);
                }

                const $status = $body.find('input[type="checkbox"][name="mep_event_cc_email_status"]').first();
                const $statusSwitch = $status.closest('.mpev-switch');
                if ($head.length && $statusSwitch.length && !$head.find('input[name="mep_event_cc_email_status"]').length) {
                    $statusSwitch.addClass('mpwem-display-toggle-wrap').attr('aria-label', 'Use event-specific email message');
                    $head.append($statusSwitch);
                    $body.find('.mpwem-email-status-row').hide();
                }

                const syncEmailToggle = function(isEnabled, useAnimation) {
                    $mount.toggleClass('is-collapsed', !isEnabled);
                    $mount.toggleClass('is-expanded', isEnabled);
                    $status.prop('checked', isEnabled).val(isEnabled ? 'on' : 'off').attr('aria-expanded', isEnabled ? 'true' : 'false');

                    if (useAnimation) {
                        $body.stop(true, true)[isEnabled ? 'slideDown' : 'slideUp'](220);
                    } else {
                        $body.toggle(isEnabled);
                    }
                };

                $status.off('change.mpwemEmailToggle').on('change.mpwemEmailToggle', function() {
                    syncEmailToggle($(this).is(':checked'), true);
                });

                syncEmailToggle($status.is(':checked'), false);
            }

            if (section.className === 'mpwem-display-section--seo') {
                const $head = $mount.children('.mpwem-display-section__head').first();
                let $body = $mount.children('.mpwem-display-section__body').first();

                if (!$body.length) {
                    const $bodyChildren = $mount.children().not('.mpwem-display-section__head');
                    $body = $('<div class="mpwem-display-section__body"></div>').append($bodyChildren);
                    $mount.append($body);
                }

                const $status = $body.find('select[name="mep_rich_text_status"]').first();
                if ($head.length && $status.length && !$head.find('.mpwem-display-toggle-wrap').length) {
                    $head.append(
                        $('<label class="mpwem-switch-wrap mpwem-display-toggle-wrap" aria-label="Enable SEO and schema settings"></label>')
                            .append('<input type="checkbox" class="mpwem-switch-input mpwem-display-toggle" />')
                            .append('<span class="mpwem-switch-slider"></span>')
                    );
                }

                const $toggle = $head.find('.mpwem-display-toggle').first();
                const syncSeoToggle = function(isEnabled, useAnimation) {
                    $status.val(isEnabled ? 'enable' : 'disable').trigger('change.mpwemCanonicalStatus');
                    $toggle.prop('checked', isEnabled).attr('aria-expanded', isEnabled ? 'true' : 'false');
                    $mount.toggleClass('is-collapsed', !isEnabled).toggleClass('is-expanded', isEnabled);
                    if (useAnimation) {
                        $body.stop(true, true)[isEnabled ? 'slideDown' : 'slideUp'](220);
                    } else {
                        $body.toggle(isEnabled);
                    }
                };

                $toggle.off('change.mpwemSeoToggle').on('change.mpwemSeoToggle', function() {
                    syncSeoToggle($(this).is(':checked'), true);
                });
                syncSeoToggle($status.val() !== 'disable', false);
            }

            if (
                section.className === 'mpwem-display-section--email-reminder' ||
                section.className === 'mpwem-display-section--pdf-custom-text' ||
                section.className === 'mpwem-display-section--settings'
            ) {
                const $head = $mount.children('.mpwem-display-section__head').first();
                let $body = $mount.children('.mpwem-display-section__body').first();
                if (!$body.length) {
                    const $bodyChildren = $mount.children().not('.mpwem-display-section__head');
                    $body = $('<div class="mpwem-display-section__body"></div>').append($bodyChildren);
                    $mount.append($body);
                }

                const bodyId = ($mount.attr('id') || section.className) + '-body';
                $body.attr('id', bodyId);
                if ($head.length && !$head.find('.mpwem-display-disclosure').length) {
                    $head.append(
                        $('<button type="button" class="mpwem-display-disclosure" aria-expanded="false"></button>')
                            .attr('aria-controls', bodyId)
                            .attr('aria-label', 'Expand ' + section.title)
                            .append('<span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>')
                    );
                }

                const $button = $head.find('.mpwem-display-disclosure').first();
                const syncDisclosure = function(isExpanded, useAnimation) {
                    $button.attr('aria-expanded', isExpanded ? 'true' : 'false')
                        .attr('aria-label', (isExpanded ? 'Collapse ' : 'Expand ') + section.title);
                    $mount.toggleClass('is-collapsed', !isExpanded).toggleClass('is-expanded', isExpanded);
                    if (useAnimation) {
                        $body.stop(true, true)[isExpanded ? 'slideDown' : 'slideUp'](220);
                    } else {
                        $body.toggle(isExpanded);
                    }
                };

                $button.off('click.mpwemDisclosure').on('click.mpwemDisclosure', function() {
                    syncDisclosure($(this).attr('aria-expanded') !== 'true', true);
                });
                syncDisclosure(false, false);
            }

            if (section.className === 'mpwem-display-section--settings') {
                // "Member Only Event" reveals the "Allowed User Roles" checklist. The
                // roles panel is server-rendered with/without .mActive based on the
                // saved value (mpwem_event_edit.css hides it unless .mActive is set),
                // but nothing wired the live checkbox to that class before — so toggling
                // it in the browser (without saving + reloading) had no visible effect.
                const $memberToggle = $mount.find('input[name="mep_member_only_event"]').first();
                const $rolesPanel = $mount.find('[data-collapse="#mep_member_only_event"]').first();

                if ($memberToggle.length && $rolesPanel.length) {
                    const syncMemberRoles = function(useAnimation) {
                        const isChecked = $memberToggle.is(':checked');
                        $rolesPanel.toggleClass('mActive', isChecked);
                        if (useAnimation) {
                            $rolesPanel.stop(true, true)[isChecked ? 'slideDown' : 'slideUp'](220);
                        } else {
                            $rolesPanel.toggle(isChecked);
                        }
                    };

                    $memberToggle.off('change.mpwemMemberRoles').on('change.mpwemMemberRoles', function() {
                        syncMemberRoles(true);
                    });

                    syncMemberRoles(false);
                }
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
                        const toggleValues = ($checkbox.attr('data-toggle-values') || 'on,off').split(',');
                        $checkbox.val(isChecked ? toggleValues[0] : toggleValues[1]);
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

            if (section.className === 'mpwem-display-section--terms') {
                const $head = $mount.children('.mpwem-display-section__head').first();
                let $body = $mount.children('.mpwem-display-section__body').first();

                if (!$body.length) {
                    const $bodyChildren = $mount.children().not('.mpwem-display-section__head');
                    $body = $('<div class="mpwem-display-section__body"></div>');
                    $body.append($bodyChildren);
                    $mount.append($body);
                }

                const $sourceRow = $body.find('> .mp_tab_item > ._layout_default_xs_mp_zero > ._padding_bt, > .mp_tab_item > ._layout_default_xs_mp_zero > ._padding_bB, > .mp_tab_item > section').filter(function() {
                    return $(this).find('.round_switch_label, .mpev-switch, .mpwem-switch-wrap').length > 0;
                }).first();
                let $switch = $sourceRow.find('.round_switch_label').first();
                if (!$switch.length) {
                    $switch = $sourceRow.find('.mpev-switch, .mpwem-switch-wrap').first();
                }
                const $checkbox = $switch.find('input[type="checkbox"]').first();
                const $legacySlider = $switch.find('.round_switch').first();

                if ($head.length && $switch.length && !$head.find('.round_switch_label, .mpev-switch, .mpwem-switch-wrap').length) {
                    $head.append($switch);
                    $sourceRow.addClass('mpwem-terms-toggle-row');
                }

                if ($sourceRow.length) {
                    $sourceRow.find('.round_switch_label, .mpev-switch, .mpwem-switch-wrap').not($head.find('.round_switch_label, .mpev-switch, .mpwem-switch-wrap')).each(function() {
                        $(this).closest('.round_switch_label, .mpev-switch, .mpwem-switch-wrap').remove();
                    });
                }

                if ($checkbox.length) {
                    const fallbackTarget = $mount.find('[data-collapse]').first().attr('data-collapse') || '';
                    const target = $checkbox.attr('data-collapse-target') || $legacySlider.attr('data-collapse-target') || fallbackTarget;
                    const $collapse = target ? $mount.find('[data-collapse="' + target + '"]').first() : $();

                    if (target) {
                        $checkbox.attr('data-collapse-target', target);
                    }
                    if (!$checkbox.attr('data-toggle-values')) {
                        $checkbox.attr('data-toggle-values', 'on,off');
                    }

                    if ($collapse.length && !$collapse.children('.mpwem-terms-list').length) {
                        const $termsList = $('<div class="mpwem-terms-list"></div>');
                        let $currentItem = null;
                        let $currentContent = null;

                        $collapse.children().each(function() {
                            const $node = $(this);
                            const $headerRow = $node.is('.justify_between, ._justify_between_align_center_wrap')
                                ? $node
                                : $node.children('.justify_between, ._justify_between_align_center_wrap').first();
                            const actionCount = $headerRow.find('a, button, .button, .buttonGroup > *, .dashicons, .fas, .far, .fa').length;
                            const looksLikeItemHeader = $headerRow.length && actionCount >= 2;

                            if (looksLikeItemHeader) {
                                $currentItem = $('<div class="mpwem-terms-item"></div>');
                                $currentContent = $('<div class="mpwem-terms-item__content"></div>');
                                const $headerWrap = $('<div class="mpwem-terms-item__head"></div>');

                                $headerWrap.append($node);
                                $currentItem.append($headerWrap).append($currentContent);
                                $termsList.append($currentItem);
                                return;
                            }

                            if ($currentContent) {
                                $currentContent.append($node);
                                return;
                            }

                            $termsList.append($node);
                        });

                        if ($termsList.children('.mpwem-terms-item').length) {
                            $collapse.append($termsList);
                        }
                    }

                    const syncTermsToggle = function(useAnimation) {
                        const isChecked = $checkbox.is(':checked');
                        const toggleValues = ($checkbox.attr('data-toggle-values') || 'on,off').split(',');
                        $checkbox.val(isChecked ? toggleValues[0] : toggleValues[1]);
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

                    $checkbox.off('change.mpwemTermsToggle').on('change.mpwemTermsToggle', function() {
                        syncTermsToggle(true);
                    });

                    syncTermsToggle(false);
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
                        const toggleValues = ($checkbox.attr('data-toggle-values') || 'on,off').split(',');
                        $checkbox.val(isChecked ? toggleValues[0] : toggleValues[1]);
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
                        const toggleValues = ($checkbox.attr('data-toggle-values') || 'on,off').split(',');
                        $checkbox.val(isChecked ? toggleValues[0] : toggleValues[1]);
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
                    .append($('<span></span>').append($('<strong></strong>').text('PDF Custom Text')).append($('<small></small>').text('Use this when Event Pro is active and you want custom copy printed on PDF tickets.')))
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

        // ── 1. Convert the manual-entry address table into a CSS grid ──────────
        const $manualWrap = $venue.find('#mpwem-manual-entry-wrap').first();
        if ($manualWrap.length) {
            const $table = $manualWrap.find('table').first();
            if ($table.length) {
                const $grid = $('<div class="mpwem-venue-grid"></div>');
                $table.find('tr').each(function() {
                    const $ths = $(this).find('th');
                    const $tds = $(this).find('td');
                    $ths.each(function(i) {
                        const label = $(this).text().trim().replace(':', '');
                        const $td   = $tds.eq(i);
                        if (!label && !$td.length) return;

                        const $field = $('<div class="mpwem-venue-field"></div>');
                        if (label) $field.append('<label class="mpwem-venue-label">' + label + '</label>');
                        $field.append($td.contents());

                        // Country and single-column cells get full width
                        const $input = $field.find('input, select').first();
                        if ($input.attr('name') === 'mep_country' || $td.hasClass('is-full')) {
                            $field.addClass('is-full');
                        }
                        $grid.append($field);
                    });
                });
                $table.replaceWith($grid);
            }
        }

        // ── 2. Wire the Manual Entry toggle button ─────────────────────────────
        const $toggleBtn   = $venue.find('#mpwem-manual-entry-toggle').first();
        const $manualPanel = $venue.find('#mpwem-manual-entry-wrap').first();

        if ($toggleBtn.length && $manualPanel.length) {
            $toggleBtn.on('click', function() {
                const isOpen = $toggleBtn.hasClass('is-open');
                if (isOpen) {
                    $manualPanel.slideUp(220);
                    $toggleBtn.removeClass('is-open');
                    $toggleBtn.find('.mpwem-manual-entry-btn__label').text('Manual Entry');
                } else {
                    $manualPanel.slideDown(220);
                    $toggleBtn.addClass('is-open');
                    $toggleBtn.find('.mpwem-manual-entry-btn__label').text('Hide Manual Fields');
                }
            });

            // If any manual address field already has a saved value, show the panel immediately on load
            const hasManualValues = $manualPanel.find('input[type="text"]').toArray()
                .some(function(el) { return $(el).val().trim() !== ''; });
            if (hasManualValues) {
                $manualPanel.show();
                $toggleBtn.addClass('is-open');
                $toggleBtn.find('.mpwem-manual-entry-btn__label').text('Hide Manual Fields');
            }
        }

        // ── 3. Real-time iframe map update while typing in the venue field ─────
        // (Only for iframe map type — the API map has its own geocoder already)
        const $venueInput = $venue.find('#mpwem_location_venue, [name="mep_location_venue"]').first();
        const $mapDiv     = $venue.find('#show_gmap').first();
        const $manualInputs = $venue.find(
            '#mpwem-manual-entry-wrap [name="mep_street"], ' +
            '#mpwem-manual-entry-wrap [name="mep_city"], ' +
            '#mpwem-manual-entry-wrap [name="mep_state"], ' +
            '#mpwem-manual-entry-wrap [name="mep_postcode"], ' +
            '#mpwem-manual-entry-wrap [name="mep_country"]'
        );

        if ($venueInput.length && $mapDiv.length) {
            var mapDebounceTimer = null;

            var refreshMap = function() {
                clearTimeout(mapDebounceTimer);

                // The venue field alone is the common case ("type a full address or
                // place name"). The manual street/city/state/postcode/country fields
                // are an opt-in, normally-hidden alternative — only fold them into the
                // query while that panel is actually open, otherwise leftover saved
                // values sitting in those hidden fields would silently corrupt an
                // otherwise-clean venue address.
                var venue = $venueInput.val().trim();
                var location = venue;
                var $manualPanel = $venue.find('#mpwem-manual-entry-wrap').first();
                if ($manualPanel.length && $manualPanel.is(':visible')) {
                    var extra = $manualInputs.toArray()
                        .map(function(el) { return $(el).val().trim(); })
                        .filter(function(part) { return part !== ''; });
                    location = [venue].concat(extra).filter(function(part) { return part !== ''; }).join(', ');
                }
                if (!location) return;

                mapDebounceTimer = window.setTimeout(function() {
                    var coordinatePattern = /^-?\d+\.?\d*\s*,\s*-?\d+\.?\d*$/;
                    var queryParam = coordinatePattern.test(location)
                        ? location
                        : encodeURIComponent(location);

                    $mapDiv.html(
                        '<iframe id="gmap_canvas" src="https://maps.google.com/maps?q=' + queryParam +
                        '&t=&z=15&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" ' +
                        'marginheight="0" marginwidth="0"></iframe>'
                    );
                }, 250);
            };

            $venueInput.on('input', refreshMap);
            $manualInputs.on('input', refreshMap);
        }

        $venue.data('mpwemGridEnhanced', true);
    }

    function enhanceEventType($root, opts) {
        const skipTicketSync = !!(opts && opts.skipTicketSync);
        const $venue = getPanel($root, '#mp_event_venue');
        if (!$venue.length) return;

        const $checkbox = $venue.find('input[name="mep_event_type"]').first();
        if (!$checkbox.length || $checkbox.data('mpwemEnhanced')) return;

        // The checkbox is in a section, let's find the container section
        const $origSection = $checkbox.closest('section');
        if (!$origSection.length) return;

        const $wrapper = $('<div class="mpwem-event-type-card"></div>');
        const $sectionTitle = $('<h4 class="mpwem-event-type-title">Event Type</h4>');
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
            '  <div class="mpwem-event-type-option" data-value="hybrid">' +
            '    <span class="dashicons dashicons-groups"></span>' +
            '    <div><strong>Hybrid</strong><small>In-Person & Online</small></div>' +
            '  </div>' +
            '</div>'
        );

        $wrapper.append($sectionTitle);
        $wrapper.append($toggle);
        $origSection.before($wrapper);
        $origSection.hide(); // Hide the original section entirely

        // Track whether virtual editor has been properly re-initialized
        var virtualEditorInitialized = false;

        function reinitVirtualEditor() {
            // Re-initialize TinyMCE for the virtual details editor after it becomes visible.
            // TinyMCE breaks when initialized inside a hidden (display:none) container.
            window.setTimeout(function() {
                var editorId = 'mp_event_virtual_type_des';
                try {
                    if (typeof tinymce !== 'undefined') {
                        var existingEditor = tinymce.get(editorId);
                        if (existingEditor) {
                            existingEditor.remove();
                        }
                        if (typeof mpwem_initWpEditor === 'function') {
                            mpwem_initWpEditor(editorId);
                        } else {
                            tinymce.init({
                                selector: '#' + editorId,
                                toolbar1: 'formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link unlink | removeformat | undo redo',
                                toolbar2: '',
                                plugins: 'link,lists,wordpress,wpeditimage,wplink,wpview',
                                menubar: false,
                                statusbar: true
                            });
                        }
                    }
                } catch (e) {
                    console.warn('mpwem: could not re-init virtual editor', e);
                }
            }, 100);
        }

        function syncHybridTicketCols(val) {
            var isHybrid = val === 'hybrid';
            $root.find('[data-hybrid-col]').toggleClass('mpwem-ticket-col-hidden', !isHybrid);
            $root.find('.mpwem-ticket-mode-select').each(function() {
                $(this).attr('data-mode', $(this).val());
            });
        }

        function updateUI(val) {
            $checkbox.val(val);
            $toggle.find('.mpwem-event-type-option').removeClass('is-active');
            $toggle.find('[data-value="' + val + '"]').addClass('is-active');

            // Trigger legacy collapses
            const target = $checkbox.data('collapse-target');
            const close = $checkbox.data('close-target');

            var wasHidden = target ? $(target).is(':hidden') : false;

            if (val === 'online') {
                if (close) $(close).hide();
                if (target) $(target).show();
            } else if (val === 'offline') {
                if (target) $(target).hide();
                if (close) $(close).show();
            } else if (val === 'hybrid') {
                if (target) $(target).show();
                if (close) $(close).show();
            }

            // Re-initialize TinyMCE if the virtual editor section just became visible
            // and hasn't been properly initialized yet (was hidden on page load).
            if ((val === 'online' || val === 'hybrid') && (wasHidden || !virtualEditorInitialized)) {
                virtualEditorInitialized = true;
                reinitVirtualEditor();
            }

            syncHybridTicketCols(val);
            if (!skipTicketSync) {
                // Builds/refreshes the wizard's "Simple Ticket Type" summary widget,
                // which relies on initializeTicketPricingModal() also having run to
                // wire up its buttons. The classic editor keeps its own separate
                // ticket table UI, so skip this there to avoid injecting an inert
                // duplicate widget onto that tab.
                syncTicketAdvancedColumns($root);
            }

            // Hide extra services card for virtual/online events (no physical services needed)
            var $extraServicesCard = $('#mpwem_wizard_extra_services_card');
            if ($extraServicesCard.length && $extraServicesCard.data('mpwem-mounted')) {
                $extraServicesCard.toggle(val !== 'online');
            }
        }

        // Keep hybrid columns in sync when new ticket rows are added
        $root.on('click.mpwemHybridCol', '.mpwem_add_item', function() {
            window.setTimeout(function() {
                syncHybridTicketCols($checkbox.val() || 'offline');
            }, 60);
        });

        // Live color-code the ticket mode selects based on selected value
        $root.on('change.mpwemTicketMode input.mpwemTicketMode', '.mpwem-ticket-mode-select', function() {
            $(this).attr('data-mode', $(this).val());
        });
        $root.find('.mpwem-ticket-mode-select').each(function() {
            $(this).attr('data-mode', $(this).val());
        });

        const initialVal = $checkbox.val() || 'offline';
        // If starting as online/hybrid, mark editor as already visible so TinyMCE
        // initialized correctly. If offline, it will need re-init on first show.
        virtualEditorInitialized = (initialVal === 'online' || initialVal === 'hybrid');
        updateUI(initialVal);

        $toggle.on('click', '.mpwem-event-type-option', function() {
            updateUI($(this).data('value'));
        });

        $checkbox.data('mpwemEnhanced', true);
    }

    function enhanceRegistrationMode($root) {
        const $panel = getPanel($root, '#mpwem_ticket_pricing_settings');
        if (!$panel.length) return;

        const $toggle = $panel.find('.mpwem-registration-mode__toggle').first();
        const $checkbox = $panel.find('input[name="mep_reg_status"]').first();
        const $collapse = $panel.find('[data-collapse="#mep_reg_status"]');
        const $ticketSummary = $panel.find('#mpwem_ticket_summary').first();
        const $extraServicesCard = $root.find('#mpwem_wizard_extra_services_card').first();

        if (!$toggle.length || !$checkbox.length || $toggle.data('mpwemEnhanced')) {
            return;
        }

        const syncRegistrationMode = function(useAnimation) {
            const isEnabled = $checkbox.is(':checked');
            const activeValue = isEnabled ? 'on' : 'off';

            $checkbox.val('on');
            $toggle.find('.mpwem-event-type-option').removeClass('is-active');
            $toggle.find('[data-value="' + activeValue + '"]').addClass('is-active');

            if ($collapse.length) {
                if (useAnimation) {
                    $collapse.stop(true, true)[isEnabled ? 'slideDown' : 'slideUp'](250);
                } else {
                    $collapse.toggle(isEnabled);
                }
                $collapse.toggleClass('mActive', isEnabled);
            }

            if ($extraServicesCard.length) {
                if (useAnimation) {
                    $extraServicesCard.stop(true, true)[isEnabled ? 'slideDown' : 'slideUp'](250);
                } else {
                    $extraServicesCard.toggle(isEnabled);
                }
            }

            if ($ticketSummary.length) {
                if (useAnimation) {
                    $ticketSummary.stop(true, true)[isEnabled ? 'slideDown' : 'slideUp'](250);
                } else {
                    $ticketSummary.toggle(isEnabled);
                }
            }

            if (!isEnabled) {
                closeTicketModal($root);
                closeExtraServiceModal($root);
            }
        };

        $toggle.on('click', '.mpwem-event-type-option', function() {
            const nextValue = $(this).data('value') === 'on';
            if ($checkbox.is(':checked') === nextValue) {
                return;
            }

            $checkbox.prop('checked', nextValue).trigger('change');
        });

        $checkbox.on('change.mpwemRegistrationMode', function() {
            syncRegistrationMode(true);
        });

        syncRegistrationMode(false);
        $toggle.data('mpwemEnhanced', true);
    }

    function enhanceSwitches($container) {
        function getSwitchTargets(target) {
            if (!target) {
                return $();
            }

            const $directTarget = $(target);
            if ($directTarget.length) {
                return $directTarget;
            }

            return $('[data-collapse="' + target + '"]');
        }

        $container.find('input[type="checkbox"]').each(function() {
            const $cb = $(this);
            if ($cb.is('[data-no-mpwem-switch]') || $cb.data('mpwemSwitch') || $cb.closest('.mpwem-event-type-toggle').length) return;
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

            const target = $cb.data('collapse-target');
            const close = $cb.data('close-target');
            const isChecked = $cb.is(':checked');
            const $target = getSwitchTargets(target);
            const $close = getSwitchTargets(close);

            if ($target.length) {
                $target.toggle(isChecked).toggleClass('mActive', isChecked);
            }

            if ($close.length) {
                $close.toggle(!isChecked).toggleClass('mActive', !isChecked);
            }

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
                const $target = getSwitchTargets(target);
                const $close = getSwitchTargets(close);

                // Sync value if toggle values exist
                if (toggleValues && toggleValues.length === 2) {
                    $cb.val(isChecked ? toggleValues[0] : toggleValues[1]);
                }

                // Handle sliding sections
                if (isChecked) {
                    if ($target.length) $target.stop(true, true).slideDown(250).addClass('mActive');
                    if ($close.length) $close.stop(true, true).slideUp(250).removeClass('mActive');
                } else {
                    if ($target.length) $target.stop(true, true).slideUp(250).removeClass('mActive');
                    if ($close.length) $close.stop(true, true).slideDown(250).addClass('mActive');
                }

                // Legacy specific: ticket time toggle
                if ($cb.attr('name') === 'mep_disable_ticket_time') {
                    const $timeSettingsScope = $cb.closest('.mpwem_date_settings, #mpwem_particular_date_modal_mount, #mpwem_wizard_date_mount');
                    const $specialDatePanel = $timeSettingsScope.find('.mep-special-datetime');

                    if (isChecked) {
                        $specialDatePanel.stop(true, true).slideDown(200);
                    } else {
                        $specialDatePanel.stop(true, true).slideUp(200);
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

    function shouldKeepNativeSelect($select) {
        const name = ($select.attr('name') || '').toString();

        // Ticket / extra-service tables scroll sideways, which clips the absolutely
        // positioned custom dropdown to one row. Add-on columns (e.g. Membership Plan)
        // must stay native so every option can be reached.
        return $select.closest('.mpwem_ticket_table, .mpwem_extra_service_table').length > 0
            || $select.closest('#mp_event_custom_form_table, .mp_event_custom_form_table').length > 0
            || $select.closest('#mpwem_wizard_attendee_form_mount table, .mpwem-display-section--attendee-form table').length > 0
            || $select.closest('.mep_condition_item, .mep_conditional_form_hidden, .conditional_form_area').length > 0
            || name === 'mep_event_reg_form_id'
            || name === 'mep_type_condition[]'
            || name === 'mep_conditional_parent[]'
            || name === 'mep_conditional_parent_value[]'
            || name === 'mep_conditional_child[]'
            || /\[mep_global_single_template\]$/.test(name)
            // Its options are (re)built after this one-time enhancement pass
            // runs (Particular Dates' configured dates), so the custom
            // dropdown's snapshot would go stale the moment they change -
            // keep it a plain native <select> instead.
            || $select.hasClass('mpwem-live-ticket-preview__date-select');
    }

    function normalizeCustomFormTableSelects($container) {
        $container.find('.mpwem-select-wrapper').each(function() {
            const $wrapper = $(this);
            const $select = $wrapper.children('select').first();

            if (!$select.length || !shouldKeepNativeSelect($select)) {
                return;
            }

            $select.removeClass('mpwem-enhanced').show();
            $wrapper.replaceWith($select);
        });
    }

    function initializeEnhancedSelectEvents() {
        if (window.mpwemEnhancedSelectEventsInitialized) {
            return;
        }

        $(document).on('click', '.mpwem-select-trigger', function(e) {
            const $trigger = $(this);
            const $wrapper = $trigger.closest('.mpwem-select-wrapper');

            e.preventDefault();
            e.stopPropagation();

            const isOpen = $wrapper.hasClass('is-open');
            $('.mpwem-select-wrapper').removeClass('is-open');

            if (!isOpen) {
                $wrapper.addClass('is-open');
            }
        });

        $(document).on('click', '.mpwem-select-options .mpwem-select-option', function(e) {
            const $option = $(this);
            const $wrapper = $option.closest('.mpwem-select-wrapper');
            const $select = $wrapper.children('select').first();
            const value = $option.data('value');
            const text = $option.text();

            e.preventDefault();
            e.stopPropagation();

            if (!$select.length) {
                return;
            }

            $select.val(value).trigger('change');
            $wrapper.find('.mpwem-select-trigger span').text(text);
            $wrapper.find('.mpwem-select-option').removeClass('is-selected');
            $option.addClass('is-selected');
            $wrapper.removeClass('is-open');
        });

        window.mpwemEnhancedSelectEventsInitialized = true;
    }

    function enhanceSelects($container) {
        normalizeHiddenSelectTemplates($container);
        normalizeNativeModalSelects($container);
        normalizeCustomFormTableSelects($container);
        initializeEnhancedSelectEvents();

        $container.find('select').each(function() {
            const $select = $(this);
            if (
                shouldKeepNativeSelect($select) ||
                $select.is('#mpwem_ticket_modal_mount select[name="option_qty_t_type[]"], #mpwem_ticket_modal_mount select[name="option_ticket_mode_t[]"], #mpwem_extra_service_modal_mount select[name="option_qty_type[]"]') ||
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
            if ($help.data('mpwemTooltip') || $help.hasClass('mpwem-tooltip-skip') || $help.closest('.mpwem-venue-field, .mpwem-registration-mode').length) return;
            
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

    function syncDateModalView($wizardRoot, value) {
        if (!$wizardRoot || !$wizardRoot.length) {
            return;
        }

        const context = getParticularDateModalContext($wizardRoot);
        const config = getDateModalTypeConfig(value || 'no');
        const $activeSection = getActiveDateModalSection($wizardRoot, value);
        const $dateModeSections = context.$modalMount.children('[data-collapse="#mep_normal_event"], [data-collapse="#mep_particular_event"], [data-collapse="#mep_everyday_event"]');

        if ($dateModeSections.length) {
            $dateModeSections.removeClass('mActive mpwem-date-mode-active').hide();

            if ($activeSection.length) {
                $activeSection.addClass('mActive mpwem-date-mode-active').show();
            }
        }

        syncParticularDateModalFooter($wizardRoot);
        syncDateWiseGlobalQtyColumns($wizardRoot);
        initializeParticularDateTableDragScroll($wizardRoot);
        refreshRepeatedEndDateFieldUI($wizardRoot);

        if (context.$modal.length && context.$modal.hasClass('is-open')) {
            context.$modal.find('#mpwem_particular_date_modal_title').text(config.modalTitle);
            context.$modal.find('#mpwem_particular_date_modal_description').text(config.modalDescription);
        }
    }

    function syncDateTypeSections($panel, value) {
        const map = {
            no: '#mep_normal_event',
            yes: '#mep_particular_event',
            everyday: '#mep_everyday_event'
        };
        const $wizardRoot = $panel.closest('.mpwem-event-wizard');
        const $scope = $wizardRoot.length ? $wizardRoot : $panel;

        $.each(map, function(type, selector) {
            $scope.find('[data-collapse="' + selector + '"]').each(function() {
                const $section = $(this);
                const isModalSection = !!$wizardRoot.length && $section.closest('#mpwem_particular_date_modal_mount').length;

                if (isModalSection) {
                    return;
                }

                if (type === value) {
                    $section.addClass('mActive mpwem-date-mode-active').show();
                } else {
                    $section.removeClass('mActive mpwem-date-mode-active').hide();
                }
            });
        });

        const $particularSummary = $panel.find('#mpwem_particular_date_summary');
        if ($particularSummary.length) {
            $particularSummary.addClass('mActive mpwem-date-mode-active').show();
        }

        $panel.find('.mpwem-date-type-option').removeClass('is-active');
        $panel.find('.mpwem-date-type-option').attr('aria-checked', 'false');
        $panel.find('.mpwem-date-type-option[data-value="' + value + '"]').addClass('is-active').attr('aria-checked', 'true');

        if ($wizardRoot.length) {
            syncDateModalView($wizardRoot, value);
            renderParticularDateSummary($wizardRoot);
        }
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
            if ($label.data('mpwemDateEnhanced')) return;
            const $dateInput = $label.find('input.formControl:not([type]), input[type="text"].formControl, input[type="text"].date_type, input[type="text"].new-date_type, input[type="text"].new-date_type-new, input[type="text"].new-particular-date_type').first();
            const hasHiddenDate = $label.find('input[type="hidden"]').filter(function() {
                const name = $(this).attr('name') || '';
                return /(^|_)event_.*date|mep_ticket_off_dates|mep_special_(start|end)_date|option_sale_(start|end)_date/.test(name);
            }).length > 0;
            if ($dateInput.length && hasHiddenDate) {
                $label.addClass('mpwem-date-input-wrap');
                $dateInput.addClass('mpwem-date-input');
                
                const $hidden = $label.find('input[type="hidden"]').first();
                if ($hidden.val()) {
                    const parsed = parseIsoDate($hidden.val());
                    if (parsed) {
                        $dateInput.val(formatVisibleDate(parsed));
                    }
                }
            }

            const $timeInput = $label.find('input[type="time"]').first();
            if ($timeInput.length) {
                $label.addClass('mpwem-time-input-wrap');
                $timeInput.addClass('mpwem-time-input');

                const timeFieldName = ($timeInput.attr('name') || '').toString();
                const shouldShowTimeReset = timeFieldName === 'event_start_time_everyday' || timeFieldName === 'event_end_time_everyday';

                if (shouldShowTimeReset && !$label.find('.mpwem_time_reset').length) {
                    $label.append(
                        $('<span class="fas fa-times remove_icon mpwem_time_reset" aria-hidden="true" title="Clear time"></span>')
                    );
                }
            }

            $label.data('mpwemDateEnhanced', true);
        });

        enhanceCustomTimePicker($panel);
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
        const format = 'D d M , yy';
        if ($.datepicker && $.datepicker.formatDate) {
            return $.datepicker.formatDate(format, date);
        }
        return formatIsoDate(date);
    }

    function formatScheduleDateValue(value) {
        const parsed = parseIsoDate((value || '').toString().trim());
        return parsed ? formatVisibleDate(parsed) : (value || '').toString().trim();
    }

    function buildScheduleRangeLabel(startDate, startTime, endDate, endTime) {
        const formattedStartDate = formatScheduleDateValue(startDate);
        const formattedEndDate = formatScheduleDateValue(endDate);
        const startLabel = [formattedStartDate, startTime].filter(Boolean).join(' ');
        const endLabel = [formattedEndDate, endTime].filter(Boolean).join(' ');

        if (!startLabel && !endLabel) {
            return '';
        }

        if (startLabel && endLabel) {
            return startLabel + ' to ' + endLabel;
        }

        return startLabel || endLabel;
    }

    function buildScheduleDateTimeLabel(dateValue, timeValue) {
        return [formatScheduleDateValue(dateValue), (timeValue || '').toString().trim()].filter(Boolean).join(' ');
    }

    function collectSingleEventSummaryItems($modalMount) {
        const items = [];
        const $singleEventSection = $modalMount.find('[data-collapse="#mep_normal_event"]').first();
        const $scope = $singleEventSection.length ? $singleEventSection : $modalMount;
        const mainStartDate = ($scope.find('input[name="event_start_date_normal"]').first().val() || '').toString().trim();
        const mainStartTime = ($scope.find('input[name="event_start_time_normal"]').first().val() || '').toString().trim();
        const mainEndDate = ($scope.find('input[name="event_end_date_normal"]').first().val() || '').toString().trim();
        const mainEndTime = ($scope.find('input[name="event_end_time_normal"]').first().val() || '').toString().trim();

        if (mainStartDate || mainStartTime || mainEndDate || mainEndTime) {
            items.push({
                startLabel: buildScheduleDateTimeLabel(mainStartDate, mainStartTime) || 'Start date not set',
                endLabel: buildScheduleDateTimeLabel(mainEndDate, mainEndTime) || 'End date not set',
                isPrimary: true,
                rowIndex: -1
            });
        }

        $scope
            .find('tbody.mpwem_item_insert tr.mpwem_remove_area')
            .filter(function() {
                return $(this).closest('.mpwem_hidden_content').length === 0;
            })
            .each(function(rowIndex) {
                const $row = $(this);
                const startDate = ($row.find('input[name="event_more_start_date_normal[]"]').first().val() || '').toString().trim();
                const startTime = ($row.find('input[name="event_more_start_time_normal[]"]').first().val() || '').toString().trim();
                const endDate = ($row.find('input[name="event_more_end_date_normal[]"]').first().val() || '').toString().trim();
                const endTime = ($row.find('input[name="event_more_end_time_normal[]"]').first().val() || '').toString().trim();

                if (!startDate && !startTime && !endDate && !endTime) {
                    return;
                }

                items.push({
                    startLabel: buildScheduleDateTimeLabel(startDate, startTime) || 'Start date not set',
                    endLabel: buildScheduleDateTimeLabel(endDate, endTime) || 'End date not set',
                    isPrimary: false,
                    rowIndex: rowIndex
                });
            });

        return items;
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

    function getTodayDateOnly() {
        const now = new Date();
        return new Date(now.getFullYear(), now.getMonth(), now.getDate());
    }

    function syncRepeatedDateConstraints($scope) {
        const $context = $scope && $scope.length ? $scope : $('.mpwem-event-wizard').first();
        const $repeatSection = $context.find('[data-collapse="#mep_everyday_event"]').first();
        if (!$repeatSection.length) return;

        const today = getTodayDateOnly();
        const $startLabel = $repeatSection.find('input[name="event_start_date_everyday"]').first().closest('label');
        const $endLabel = $repeatSection.find('input[name="event_end_date_everyday"]').first().closest('label');
        const $startInput = $startLabel.find('.mpwem-date-input, input.formControl').first();
        const $endInput = $endLabel.find('.mpwem-date-input, input.formControl').first();
        const startValue = ($startLabel.find('input[name="event_start_date_everyday"]').first().val() || '').toString().trim();
        const startDate = parseIsoDate(startValue);
        const endMinDate = startDate && startDate > today ? startDate : today;

        if ($startInput.length) {
            $startInput.data('mpwemMinDate', today);
            if ($.datepicker && $startInput.hasClass('hasDatepicker')) {
                try {
                    $startInput.datepicker('option', 'minDate', today);
                } catch (error) {
                    // Ignore incomplete legacy datepicker state.
                }
            }
        }

        if ($endInput.length) {
            $endInput.data('mpwemMinDate', endMinDate);
            if ($.datepicker && $endInput.hasClass('hasDatepicker')) {
                try {
                    $endInput.datepicker('option', 'minDate', endMinDate);
                } catch (error) {
                    // Ignore incomplete legacy datepicker state.
                }
            }
        }
    }

    function getDynamicDateMinDate($input) {
        const today = getTodayDateOnly();
        const resolved = resolveDynamicDateMinDate($input, today);
        // Whatever rule resolved the minimum (paired start/end, previous row,
        // a captured legacy datepicker value, or none at all), past dates are
        // never selectable - floor every result to today.
        return resolved && resolved > today ? resolved : today;
    }

    function resolveDynamicDateMinDate($input, today) {
        // Proxy date fields (inline Add/Edit date forms) carry no `name` of
        // their own - resolve the rule from the real field they mirror.
        const $proxyInput = $input;
        const $realDate = $input.data('mpwemRealDate');
        if ($realDate && $realDate.length) {
            $input = $realDate;
        }
        // $input is the visible date field; its name lives on the hidden sibling.
        const $hidden = $input.closest('label').find('input[type="hidden"]').first();
        const fieldName = ($hidden.attr('name') || $input.attr('name') || '').toString();

        // Start-date fields cannot be earlier than today.
        if (fieldName === 'event_start_date_everyday') {
            return today;
        }

        // End-date fields: the minimum is the paired Start date, so any date
        // before the start is disabled in the picker (End is always >= Start).
        const endPairs = {
            'event_end_date_everyday':      { start: 'event_start_date_everyday',      scope: '[data-collapse="#mep_everyday_event"]', floorToday: true },
            'event_end_date_normal':        { start: 'event_start_date_normal',        scope: 'tr' },
            'event_end_date':               { start: 'event_start_date',               scope: 'tr' },
            'event_more_end_date_normal[]': { start: 'event_more_start_date_normal[]', scope: 'tr' },
            'event_more_end_date[]':        { start: 'event_more_start_date[]',         scope: 'tr' }
        };

        const pair = endPairs[fieldName];
        if (pair) {
            const $scope = pair.scope === 'tr' ? $input.closest('tr') : $input.closest(pair.scope);
            let startValue = ($scope.find('input[name="' + pair.start + '"]').first().val() || '').toString().trim();

            // For the single main pairs (no [] in the name), fall back to the
            // enclosing date section if the row lookup misses.
            if (startValue === '' && pair.start.indexOf('[]') === -1) {
                startValue = ($input.closest('[data-collapse]').find('input[name="' + pair.start + '"]').first().val() || '').toString().trim();
            }

            const startDate = parseIsoDate(startValue);
            if (startDate) {
                return pair.floorToday && startDate < today ? today : startDate;
            }
            return pair.floorToday ? today : normalizeMinDate($proxyInput.data('mpwemMinDate'));
        }

        // "Add More Date" start fields: the minimum is the previous row's End
        // date, so each additional schedule begins at/after the one above it
        // (the first more-row starts from the main/top row's End date).
        const moreStartPrevEnds = {
            'event_more_start_date_normal[]': ['event_end_date_normal', 'event_more_end_date_normal[]'],
            'event_more_start_date[]':        ['event_end_date', 'event_more_end_date[]']
        };
        const prevEndNames = moreStartPrevEnds[fieldName];
        if (prevEndNames) {
            const $prevRow = $input.closest('tr').prevAll('tr').filter(function() {
                return $(this).closest('.mpwem_hidden_content').length === 0;
            }).first();

            if ($prevRow.length) {
                let prevEndValue = '';
                prevEndNames.forEach(function(name) {
                    if (prevEndValue) return;
                    const v = ($prevRow.find('input[name="' + name + '"]').first().val() || '').toString().trim();
                    if (v) prevEndValue = v;
                });
                const prevEnd = parseIsoDate(prevEndValue);
                if (prevEnd) {
                    return prevEnd;
                }
            }
            return normalizeMinDate($proxyInput.data('mpwemMinDate'));
        }

        return normalizeMinDate($proxyInput.data('mpwemMinDate'));
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
        const wasOpen = $calendar.hasClass('is-open');
        const previousVisibility = $calendar.css('visibility');

        if (!wasOpen) {
            $calendar.css('visibility', 'hidden').addClass('is-open');
        }

        const width = $calendar.outerWidth();
        const height = $calendar.outerHeight();
        const inputHeight = $input.outerHeight();
        const scrollTop = $(window).scrollTop();
        const viewportRight = $(window).scrollLeft() + $(window).width();
        const viewportBottom = scrollTop + $(window).height();
        const spaceBelow = viewportBottom - (offset.top + inputHeight);
        const spaceAbove = offset.top - scrollTop;
        let left = offset.left;
        let top = offset.top + inputHeight + 8;

        if (left + width > viewportRight - 12) {
            left = Math.max(12, viewportRight - width - 12);
        }

        if (height && spaceBelow < height + 12 && spaceAbove > spaceBelow) {
            top = Math.max(scrollTop + 12, offset.top - height - 8);
        }

        $calendar.css({
            top: top,
            left: left
        });

        if (!wasOpen) {
            $calendar.removeClass('is-open').css('visibility', previousVisibility);
        }
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

            const isAllowed = !state.allowedDates || !state.allowedDates.size || state.allowedDates.has(iso);
            if ((state.minDate && date < state.minDate) || !isAllowed) $button.prop('disabled', true).addClass('is-disabled');
            if (iso === todayIso) $button.addClass('is-today');
            if (iso === selectedIso) $button.addClass('is-selected');

            $days.append($button);
        }
    }

    function openCustomCalendar($input) {
        closeCustomTimePicker();

        if ($.datepicker && $input.hasClass('hasDatepicker')) {
            try {
                $input.datepicker('destroy');
            } catch (error) {
                // Ignore incomplete legacy datepicker instances.
            }
        }

        const $calendar = ensureCustomCalendar();
        const $hidden = $input.closest('label').find('input[type="hidden"]').first();
        const selected = parseIsoDate($hidden.val());
        const now = new Date();
        const minDate = getDynamicDateMinDate($input);

        // Repeated Event's preview picker restricts selection to this
        // event's actual occurrence dates (see updateLiveTicketPreviewDate),
        // stored on the input rather than threaded through every caller.
        const allowedList = $input.data('mpwemAllowedDates');
        const allowedDates = Array.isArray(allowedList) ? new Set(allowedList) : null;
        const firstAllowed = allowedDates && allowedDates.size ? parseIsoDate(allowedList[0]) : null;

        const viewDate = selected || firstAllowed || minDate || now;
        $calendar.data('mpwemState', {
            $input: $input,
            minDate: minDate,
            allowedDates: allowedDates,
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

        // Picking a Starts/Ends date flows straight into picking its time -
        // the matching time field sits right after the date field's own
        // wrap, inside the same row/pill.
        const $dateWrap = state.$input.closest('.mpwem-date-input-wrap');
        // The time field is usually a later sibling of the date field's label,
        // but Repeated Event's End date is wrapped in `.mep_load_every_day`
        // (AJAX-reloaded), which puts the time label beside that wrapper instead.
        let $timeWrap = $dateWrap.nextAll('.mpwem-time-input-wrap').first();
        if (!$timeWrap.length) {
            $timeWrap = $dateWrap.parent().nextAll('.mpwem-time-input-wrap').first();
        }
        const $timeInput = $dateWrap.length
            ? $timeWrap.find('.mpwem-time-input.mpwem-custom-time-enabled').first()
            : $();
        if ($timeInput.length) {
            window.setTimeout(function() {
                openCustomTimePicker($timeInput);
            }, 10);
        }
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

        $(document).on('click', '.mpwem-event-wizard .mpwem-time-input-wrap .mpwem_time_reset', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const $label = $(this).closest('label');
            $label.find('.mpwem-time-input').first().val('').trigger('change');
            closeCustomTimePicker();
        });

        $(document).on('input change', '.mpwem-event-wizard input[name="event_start_date_everyday"], .mpwem-event-wizard input[name="event_end_date_everyday"]', function() {
            const $wizard = $('.mpwem-event-wizard').first();
            if (!$wizard.length) return;
            syncRepeatedDateConstraints($wizard);
        });

        // Changing a Start date reloads the paired End-date field via AJAX
        // (load_event_*date actions) which injects fresh jQuery UI datepicker
        // markup. Re-enhance it into the wizard's custom calendar so the End
        // date picker keeps working for every event type (Single/Particular/
        // Repeated, including "Add More Date" rows).
        $(document).ajaxComplete(function(event, xhr, settings) {
            if (!settings || typeof settings.data !== 'string') return;
            if (!/action=load_event_[a-z_]*date/.test(settings.data)) return;

            const $wizard = $('.mpwem-event-wizard').first();
            if (!$wizard.length) return;

            ['#mpwem_wizard_date_mount', '#mpwem_particular_date_modal_mount'].forEach(function(selector) {
                const $mount = $wizard.find(selector);
                if ($mount.length) {
                    enhanceDateFields($mount);
                    enhanceCustomCalendar($mount);
                }
            });
            refreshRepeatedEndDateFieldUI($wizard);
        });

        $(document).on('mousedown', function(e) {
            const $target = $(e.target);
            if (!$target.closest('.mpwem-custom-calendar, .mpwem-date-input-wrap').length) {
                closeCustomCalendar();
            }
            if (!$target.closest('.mpwem-custom-time-picker, .mpwem-time-input-wrap').length) {
                closeCustomTimePicker();
            }
        });

        $(window).on('resize scroll', function() {
            const $calendar = $('#mpwem_custom_calendar.is-open');
            const calendarState = $calendar.data('mpwemState');
            if ($calendar.length && calendarState && calendarState.$input) {
                positionCustomCalendar($calendar, calendarState.$input);
            }

            const $picker = $('#mpwem_custom_time_picker.is-open');
            const timeState = $picker.data('mpwemState');
            if ($picker.length && timeState && timeState.$input) {
                positionCustomTimePicker($picker, timeState.$input);
            }
        });

        window.mpwemCustomCalendarBound = true;
    }

    function enhanceCustomCalendar($panel) {
        bindCustomCalendar();
        $panel.find('.mpwem-date-input').each(function() {
            const $input = $(this);
            if ($input.data('mpwemCustomCalendar')) return;

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
        enhanceCustomTimePicker($panel);
    }

    function parseTimeValue(value) {
        const raw = (value || '').toString().trim();
        if (!raw) return null;

        const match24 = raw.match(/^(\d{1,2}):(\d{2})(?::\d{2})?$/);
        if (match24) {
            let hour = parseInt(match24[1], 10);
            const minute = parseInt(match24[2], 10);
            if (hour >= 0 && hour <= 23 && minute >= 0 && minute <= 59) {
                return { hour: hour, minute: minute };
            }
        }

        const match12 = raw.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
        if (match12) {
            let hour = parseInt(match12[1], 10);
            const minute = parseInt(match12[2], 10);
            const period = match12[3].toUpperCase();
            if (hour >= 1 && hour <= 12 && minute >= 0 && minute <= 59) {
                if (period === 'AM') {
                    hour = hour === 12 ? 0 : hour;
                } else {
                    hour = hour === 12 ? 12 : hour + 12;
                }
                return { hour: hour, minute: minute };
            }
        }

        return null;
    }

    function formatTimeValue(hour, minute) {
        return String(hour).padStart(2, '0') + ':' + String(minute).padStart(2, '0');
    }

    function formatTimeDisplay(hour, minute) {
        const period = hour >= 12 ? 'PM' : 'AM';
        let hour12 = hour % 12;
        if (hour12 === 0) hour12 = 12;
        return String(hour12).padStart(2, '0') + ':' + String(minute).padStart(2, '0') + ' ' + period;
    }

    function toPickerParts(hour24, minute) {
        const period = hour24 >= 12 ? 'PM' : 'AM';
        let hour12 = hour24 % 12;
        if (hour12 === 0) hour12 = 12;
        return { hour12: hour12, minute: minute, period: period };
    }

    function fromPickerParts(hour12, minute, period) {
        let hour24 = hour12 % 12;
        if (period === 'PM') hour24 += 12;
        return { hour: hour24, minute: minute };
    }

    function ensureCustomTimePicker() {
        let $picker = $('#mpwem_custom_time_picker');
        if ($picker.length) return $picker;

        const hourButtons = [];
        for (let h = 1; h <= 12; h++) {
            hourButtons.push('<button type="button" class="mpwem-custom-time-picker__chip" data-time-hour="' + h + '">' + String(h).padStart(2, '0') + '</button>');
        }

        const minuteButtons = [];
        for (let m = 0; m < 60; m += 5) {
            minuteButtons.push('<button type="button" class="mpwem-custom-time-picker__chip" data-time-minute="' + m + '">' + String(m).padStart(2, '0') + '</button>');
        }

        $picker = $(
            '<div id="mpwem_custom_time_picker" class="mpwem-custom-time-picker" role="dialog" aria-modal="false" aria-label="Choose time">' +
            '  <div class="mpwem-custom-time-picker__preview" aria-live="polite">' +
            // Hour and minute are typeable: the chips only step by 5, so any other
            // minute (e.g. 10:07 or 11:59) is entered here.
            '    <input type="text" class="mpwem-custom-time-picker__preview-hour" data-time-part="hour" inputmode="numeric" maxlength="2" autocomplete="off" aria-label="Hour" value="12">' +
            '    <span class="mpwem-custom-time-picker__preview-colon">:</span>' +
            '    <input type="text" class="mpwem-custom-time-picker__preview-minute" data-time-part="minute" inputmode="numeric" maxlength="2" autocomplete="off" aria-label="Minute" value="00">' +
            '    <span class="mpwem-custom-time-picker__preview-period">AM</span>' +
            '  </div>' +
            '  <div class="mpwem-custom-time-picker__period" role="group" aria-label="AM or PM">' +
            '    <button type="button" class="mpwem-custom-time-picker__period-btn" data-time-period="AM">AM</button>' +
            '    <button type="button" class="mpwem-custom-time-picker__period-btn" data-time-period="PM">PM</button>' +
            '  </div>' +
            '  <div class="mpwem-custom-time-picker__grid">' +
            '    <div class="mpwem-custom-time-picker__section">' +
            '      <div class="mpwem-custom-time-picker__label">Hour</div>' +
            '      <div class="mpwem-custom-time-picker__hours">' + hourButtons.join('') + '</div>' +
            '    </div>' +
            '    <div class="mpwem-custom-time-picker__section">' +
            '      <div class="mpwem-custom-time-picker__label">Minute</div>' +
            '      <div class="mpwem-custom-time-picker__minutes">' + minuteButtons.join('') + '</div>' +
            '    </div>' +
            '  </div>' +
            '  <div class="mpwem-custom-time-picker__presets" role="group" aria-label="Quick times">' +
            '    <button type="button" class="mpwem-custom-time-picker__preset" data-preset="09:00">9:00 AM</button>' +
            '    <button type="button" class="mpwem-custom-time-picker__preset" data-preset="12:00">12:00 PM</button>' +
            '    <button type="button" class="mpwem-custom-time-picker__preset" data-preset="15:00">3:00 PM</button>' +
            '    <button type="button" class="mpwem-custom-time-picker__preset" data-preset="18:00">6:00 PM</button>' +
            '    <button type="button" class="mpwem-custom-time-picker__preset" data-preset="20:00">8:00 PM</button>' +
            // End of day, one click: a sale ending the day before a new price starts.
            '    <button type="button" class="mpwem-custom-time-picker__preset" data-preset="23:59">11:59 PM</button>' +
            '  </div>' +
            '  <div class="mpwem-custom-time-picker__foot">' +
            '    <button type="button" class="mpwem-custom-time-picker__now">Now</button>' +
            '    <div class="mpwem-custom-time-picker__foot-actions">' +
            '      <button type="button" class="mpwem-custom-time-picker__clear">Clear</button>' +
            '      <button type="button" class="mpwem-custom-time-picker__done">Done</button>' +
            '    </div>' +
            '  </div>' +
            '</div>'
        );

        $('body').append($picker);
        return $picker;
    }

    function positionCustomTimePicker($picker, $input) {
        const offset = $input.offset();
        const wasOpen = $picker.hasClass('is-open');
        const previousVisibility = $picker.css('visibility');

        if (!wasOpen) {
            $picker.css('visibility', 'hidden').addClass('is-open');
        }

        const width = $picker.outerWidth();
        const height = $picker.outerHeight();
        const inputHeight = $input.outerHeight();
        const scrollTop = $(window).scrollTop();
        const viewportRight = $(window).scrollLeft() + $(window).width();
        const viewportBottom = scrollTop + $(window).height();
        const spaceBelow = viewportBottom - (offset.top + inputHeight);
        const spaceAbove = offset.top - scrollTop;
        let left = offset.left;
        let top = offset.top + inputHeight + 8;

        if (left + width > viewportRight - 12) {
            left = Math.max(12, viewportRight - width - 12);
        }

        if (height && spaceBelow < height + 12 && spaceAbove > spaceBelow) {
            top = Math.max(scrollTop + 12, offset.top - height - 8);
        }

        $picker.css({
            top: top,
            left: left
        });

        if (!wasOpen) {
            $picker.removeClass('is-open').css('visibility', previousVisibility);
        }
    }

    function renderCustomTimePicker($picker) {
        const state = $picker.data('mpwemState');
        if (!state) return;

        const parts = toPickerParts(state.hour, state.minute);
        // Leave the field being typed in alone, or "5" would be rewritten to "05"
        // before the second digit arrives.
        const focused = document.activeElement;
        $picker.find('.mpwem-custom-time-picker__preview-hour').not(focused).val(String(parts.hour12).padStart(2, '0'));
        $picker.find('.mpwem-custom-time-picker__preview-minute').not(focused).val(String(parts.minute).padStart(2, '0'));
        $picker.find('.mpwem-custom-time-picker__preview-period').text(parts.period);

        $picker.find('[data-time-hour]').removeClass('is-selected').filter('[data-time-hour="' + parts.hour12 + '"]').addClass('is-selected');
        $picker.find('[data-time-minute]').removeClass('is-selected').filter('[data-time-minute="' + parts.minute + '"]').addClass('is-selected');
        $picker.find('[data-time-period]').removeClass('is-selected').filter('[data-time-period="' + parts.period + '"]').addClass('is-selected');

        // Same-day End time: grey out anything at/before the Start time.
        const minM = state.minMinutes;
        const tooEarly = function(h24, m) { return minM != null && h24 * 60 + m <= minM; };
        $picker.find('[data-time-hour]').each(function() {
            const h24 = fromPickerParts(parseInt($(this).data('time-hour'), 10), 59, parts.period).hour;
            $(this).toggleClass('is-disabled', tooEarly(h24, 59));
        });
        $picker.find('[data-time-minute]').each(function() {
            $(this).toggleClass('is-disabled', tooEarly(state.hour, parseInt($(this).data('time-minute'), 10)));
        });
        $picker.find('[data-time-period]').each(function() {
            const h24 = fromPickerParts(11, 59, $(this).data('time-period')).hour;
            $(this).toggleClass('is-disabled', tooEarly(h24, 59));
        });
        $picker.find('[data-preset]').each(function() {
            const t = parseTimeValue($(this).data('preset'));
            $(this).toggleClass('is-disabled', !!t && tooEarly(t.hour, t.minute));
        });

        const $minutes = $picker.find('.mpwem-custom-time-picker__minutes');
        $minutes.find('[data-time-minute].is-custom').remove();
        if (parts.minute % 5 !== 0) {
            const $custom = $('<button type="button" class="mpwem-custom-time-picker__chip is-custom is-selected"></button>')
                .attr('data-time-minute', parts.minute)
                .text(String(parts.minute).padStart(2, '0'));
            $minutes.append($custom);
        }
    }

    function timeToMinutes(value) {
        const parsed = parseTimeValue((value || '').toString());
        return parsed ? parsed.hour * 60 + parsed.minute : null;
    }

    // For an End time field: the Start time (in minutes) it must be later
    // than - only when the Start and End dates are the same day, else null.
    function getEndTimeMinimum($timeInput) {
        const hiddenOf = function($visible) {
            return $visible.closest('label').find('input[type="hidden"]').first().val();
        };
        let startTime = '';
        let startDate = null;
        let endDate = null;

        const $pill = $timeInput.closest('.mpwem-repeat-dt-pill');
        const $pills = $pill.closest('.mpwem-ticket-add__grid').find('.mpwem-repeat-dt-pill');
        if ($pill.length && $pills.length === 2 && $pills.eq(1)[0] === $pill[0]) {
            // Inline Add/Edit date form (proxy inputs): first pill = Start.
            const $start = $pills.eq(0);
            startTime = $start.find('input[type="time"]').first().val();
            startDate = parseIsoDate(hiddenOf($start.find('.mpwem-date-input').first()));
            endDate = parseIsoDate(hiddenOf($pill.find('.mpwem-date-input').first()));
        } else {
            const name = ($timeInput.attr('name') || '').toString();
            if (name.indexOf('_end_time') === -1) return null;
            const $scope = $timeInput.closest('tr').length
                ? $timeInput.closest('tr')
                : $timeInput.closest('[data-collapse], .mpwem-event-wizard');
            const find = function(n) { return $scope.find('input[name="' + n + '"]').first().val(); };
            startTime = find(name.replace('_end_time', '_start_time'));
            startDate = parseIsoDate(find(name.replace('_end_time', '_start_date')));
            endDate = parseIsoDate(find(name.replace('_end_time', '_end_date')));
        }

        if (!startDate || !endDate || formatIsoDate(startDate) !== formatIsoDate(endDate)) return null;
        return timeToMinutes(startTime);
    }

    function applyCustomTimePickerValue($picker, closeAfter) {
        const state = $picker.data('mpwemState');
        if (!state || !state.$input) return;

        // End time on the same day as the Start must be after the Start time.
        if (state.minMinutes != null && state.hour * 60 + state.minute <= state.minMinutes) {
            showToast('End time must be after the start time on the same day.', 'error');
            return;
        }

        state.$input.val(formatTimeValue(state.hour, state.minute)).trigger('change');
        if (closeAfter) {
            closeCustomTimePicker();
            openEndDateAfterStartTime(state.$input);
        }
    }

    // Setting a Start time flows straight into picking the matching End date
    // (Single / Particular / Repeated and every "Add More Date" row), the same
    // way picking a date flows into its time. Skipped when the End date is already
    // later than the Start, so re-editing a time doesn't pop it open.
    function openEndDateAfterStartTime($timeInput) {
        let $endVisible = $timeInput.data('mpwemEndDateInput');
        let startDate = null;
        let endDate = null;

        if ($endVisible && $endVisible.length) {
            // Inline Add/Edit date form: the pill proxies mirror the real fields.
            const $startVisible = $timeInput.closest('.mpwem-repeat-dt-pill').find('.mpwem-date-input').first();
            startDate = parseIsoDate($startVisible.closest('label').find('input[type="hidden"]').first().val());
            endDate = parseIsoDate($endVisible.closest('label').find('input[type="hidden"]').first().val());
        } else {
            const name = ($timeInput.attr('name') || '').toString();
            if (name.indexOf('_start_time') === -1) return;

            const $scope = $timeInput.closest('tr').length
                ? $timeInput.closest('tr')
                : $timeInput.closest('[data-collapse], .mpwem-event-wizard');
            const $endHidden = $scope.find('input[type="hidden"][name="' + name.replace('_start_time', '_end_date') + '"]').first();
            if (!$endHidden.length) return;
            $endVisible = $endHidden.closest('label').find('.mpwem-date-input').first();
            startDate = parseIsoDate($scope.find('input[name="' + name.replace('_start_time', '_start_date') + '"]').first().val());
            endDate = parseIsoDate($endHidden.val());
        }

        if (!$endVisible.length || !$endVisible.is(':visible')) return;
        // Repeated Event pre-fills End with the Start date, so a same-day End
        // still counts as "not chosen yet"; only a later End is left alone.
        if (endDate && startDate && endDate > startDate) return;

        window.setTimeout(function() {
            openCustomCalendar($endVisible);
        }, 10);
    }

    function openCustomTimePicker($input) {
        closeCustomCalendar();

        const $picker = ensureCustomTimePicker();
        const parsed = parseTimeValue($input.val());
        const now = new Date();
        const hour = parsed ? parsed.hour : now.getHours();
        const minute = parsed ? parsed.minute : Math.round(now.getMinutes() / 5) * 5 % 60;

        const minMinutes = getEndTimeMinimum($input);
        let startHour = hour;
        let startMinute = minute;
        if (minMinutes !== null && startHour * 60 + startMinute <= minMinutes) {
            const next = Math.min(minMinutes + 1, 23 * 60 + 59);
            startHour = Math.floor(next / 60);
            startMinute = next % 60;
        }

        $picker.data('mpwemState', {
            $input: $input,
            hour: startHour,
            minute: startMinute,
            minMinutes: minMinutes
        });

        renderCustomTimePicker($picker);
        positionCustomTimePicker($picker, $input);
        $picker.addClass('is-open');
        $input.addClass('is-time-picker-open');
    }

    function closeCustomTimePicker() {
        const $picker = $('#mpwem_custom_time_picker');
        const state = $picker.data('mpwemState');
        if (state && state.$input) {
            state.$input.removeClass('is-time-picker-open');
        }
        $picker.removeClass('is-open');
    }

    function bindCustomTimePicker() {
        if (window.mpwemCustomTimePickerBound) return;

        $(document).on('mousedown click focus', '.mpwem-time-input.mpwem-custom-time-enabled', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const $input = $(this);
            const $picker = $('#mpwem_custom_time_picker');
            const state = $picker.data('mpwemState');
            if ($picker.hasClass('is-open') && state && state.$input && state.$input[0] === $input[0]) {
                return;
            }
            if (typeof this.blur === 'function') {
                this.blur();
            }
            openCustomTimePicker($input);
        });

        $(document).on('keydown', '.mpwem-time-input.mpwem-custom-time-enabled', function(e) {
            if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
                e.preventDefault();
                openCustomTimePicker($(this));
            } else if (e.key === 'Escape') {
                closeCustomTimePicker();
            } else if (e.key.length === 1 || e.key === 'Backspace' || e.key === 'Delete') {
                // Keep the field keyboard-locked; the custom picker owns edits.
                e.preventDefault();
            }
        });

        $(document).on('click', '.mpwem-custom-time-picker__chip[data-time-hour]', function(e) {
            e.preventDefault();
            if ($(this).hasClass('is-disabled')) return;
            const $picker = $('#mpwem_custom_time_picker');
            const state = $picker.data('mpwemState');
            if (!state) return;

            const parts = toPickerParts(state.hour, state.minute);
            const next = fromPickerParts(parseInt($(this).data('time-hour'), 10), parts.minute, parts.period);
            state.hour = next.hour;
            state.minute = next.minute;
            if (state.minMinutes != null && state.hour * 60 + state.minute <= state.minMinutes) {
                // Same-day End time: nudge the minute up to just after the Start.
                state.minute = (state.minMinutes + 1) % 60;
            }
            $picker.data('mpwemState', state);
            renderCustomTimePicker($picker);
            applyCustomTimePickerValue($picker, false);
        });

        $(document).on('click', '.mpwem-custom-time-picker__chip[data-time-minute]', function(e) {
            e.preventDefault();
            if ($(this).hasClass('is-disabled')) return;
            const $picker = $('#mpwem_custom_time_picker');
            const state = $picker.data('mpwemState');
            if (!state) return;

            state.minute = parseInt($(this).data('time-minute'), 10);
            $picker.data('mpwemState', state);
            renderCustomTimePicker($picker);
            applyCustomTimePickerValue($picker, false);
        });

        $(document).on('focus', '.mpwem-custom-time-picker [data-time-part]', function() {
            this.select();
        });

        $(document).on('input', '.mpwem-custom-time-picker [data-time-part]', function() {
            const $picker = $('#mpwem_custom_time_picker');
            const state = $picker.data('mpwemState');
            const digits = this.value.replace(/\D/g, '').slice(0, 2);
            if (this.value !== digits) this.value = digits;
            if (!state || digits === '') return;

            const value = parseInt(digits, 10);
            const parts = toPickerParts(state.hour, state.minute);
            let next;
            if ($(this).data('time-part') === 'hour') {
                if (value < 1 || value > 12) return;
                next = fromPickerParts(value, parts.minute, parts.period);
            } else {
                if (value > 59) return;
                next = fromPickerParts(parts.hour12, value, parts.period);
            }
            state.hour = next.hour;
            state.minute = next.minute;
            $picker.data('mpwemState', state);
            renderCustomTimePicker($picker);
            applyCustomTimePickerValue($picker, false);
        });

        // Leaving the field (or an out-of-range entry) snaps it back to the real value.
        $(document).on('blur', '.mpwem-custom-time-picker [data-time-part]', function() {
            const $picker = $('#mpwem_custom_time_picker');
            setTimeout(function() {
                renderCustomTimePicker($picker);
            }, 0);
        });

        $(document).on('keydown', '.mpwem-custom-time-picker [data-time-part]', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyCustomTimePickerValue($('#mpwem_custom_time_picker'), true);
            } else if (e.key === 'Escape') {
                e.preventDefault();
                closeCustomTimePicker();
            }
        });

        $(document).on('click', '.mpwem-custom-time-picker__period-btn', function(e) {
            e.preventDefault();
            if ($(this).hasClass('is-disabled')) return;
            const $picker = $('#mpwem_custom_time_picker');
            const state = $picker.data('mpwemState');
            if (!state) return;

            const parts = toPickerParts(state.hour, state.minute);
            const next = fromPickerParts(parts.hour12, parts.minute, $(this).data('time-period'));
            state.hour = next.hour;
            state.minute = next.minute;
            $picker.data('mpwemState', state);
            renderCustomTimePicker($picker);
            applyCustomTimePickerValue($picker, false);
        });

        $(document).on('click', '.mpwem-custom-time-picker__preset', function(e) {
            e.preventDefault();
            if ($(this).hasClass('is-disabled')) return;
            const $picker = $('#mpwem_custom_time_picker');
            const state = $picker.data('mpwemState');
            const parsed = parseTimeValue($(this).data('preset'));
            if (!state || !parsed) return;

            state.hour = parsed.hour;
            state.minute = parsed.minute;
            $picker.data('mpwemState', state);
            renderCustomTimePicker($picker);
            applyCustomTimePickerValue($picker, true);
        });

        $(document).on('click', '.mpwem-custom-time-picker__now', function(e) {
            e.preventDefault();
            const $picker = $('#mpwem_custom_time_picker');
            const state = $picker.data('mpwemState');
            if (!state) return;

            const now = new Date();
            state.hour = now.getHours();
            state.minute = Math.round(now.getMinutes() / 5) * 5 % 60;
            $picker.data('mpwemState', state);
            renderCustomTimePicker($picker);
            applyCustomTimePickerValue($picker, true);
        });

        $(document).on('click', '.mpwem-custom-time-picker__clear', function(e) {
            e.preventDefault();
            const $picker = $('#mpwem_custom_time_picker');
            const state = $picker.data('mpwemState');
            if (!state || !state.$input) return;

            state.$input.val('').trigger('change');
            closeCustomTimePicker();
        });

        $(document).on('click', '.mpwem-custom-time-picker__done', function(e) {
            e.preventDefault();
            applyCustomTimePickerValue($('#mpwem_custom_time_picker'), true);
        });

        window.mpwemCustomTimePickerBound = true;
    }

    function enhanceCustomTimePicker($panel) {
        bindCustomTimePicker();
        $panel.find('input[type="time"]').each(function() {
            const $input = $(this);
            if ($input.data('mpwemCustomTime')) return;

            const $label = $input.closest('label');
            if ($label.length) {
                $label.addClass('mpwem-time-input-wrap');
            } else if (!$input.parent().hasClass('mpwem-time-input-wrap')) {
                $input.wrap('<span class="mpwem-time-input-wrap"></span>');
            }

            $input
                .addClass('mpwem-time-input mpwem-custom-time-enabled')
                .attr({
                    autocomplete: 'off',
                    readonly: 'readonly',
                    inputmode: 'none'
                })
                .data('mpwemCustomTime', true);

            if (this.addEventListener) {
                this.addEventListener('showPicker', function(event) {
                    event.preventDefault();
                });
            }
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
            '#mep_particular_event': 'Specific date schedule'
            /* '#mep_everyday_event' deliberately excluded: its own panel
               head now has an icon-badge "Repeated Event" eyebrow (see
               enhanceRepeatedScheduleLayout), so this pill said the same
               thing a second time right above it. */
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
            $group.find('.customCheckbox').each(function() {
                const $chip = $(this).addClass('mpwem-offday-chip');
                const fullLabel = $.trim($chip.text());
                if (fullLabel && !$chip.data('mpwemOffDayFullLabel')) {
                    $chip.data('mpwemOffDayFullLabel', fullLabel);
                    $chip.closest('.mpwem-offday-option').attr('title', fullLabel);
                    $chip.text(fullLabel.charAt(0).toUpperCase());
                }
            });
            $group.find('input[type="checkbox"]').off('change.mpwemOffDays').on('change.mpwemOffDays', function() {
                syncOffDayPickerValue($group);
            });
            syncOffDayPickerValue($group);
            $group.data('mpwemOffDaysEnhanced', true);
        });
    }

    function syncOffDayPickerValue($group) {
        let value = '';

        $group.find('input[type="checkbox"]').each(function() {
            if ($(this).is(':checked')) {
                const currentValue = ($(this).attr('data-checked') || '').toString().trim();
                value += value && currentValue ? ',' + currentValue : currentValue;
            }
        });

        $group.find('input[type="hidden"]').first().val(value).trigger('change');
    }

    function enhanceRepeatedScheduleLayout($panel) {
        const $repeatSection = $panel.find('[data-collapse="#mep_everyday_event"]').first();
        if (!$repeatSection.length) return;

        $repeatSection.addClass('mpwem-repeat-schedule-modal');
        $repeatSection.children('._layout_default_xs_mp_zero').addClass('mpwem-repeat-schedule-shell');
        $repeatSection.find('.mep-special-datetime').addClass('mpwem-repeat-special-panel');
        $repeatSection.find('#mep_disable_ticket_time').addClass('mpwem-repeat-times-panel');
        /* These leftover "section.bg-light" headers (mismatched copy from a
           shared template, e.g. "Configure Event Locations and Virtual
           Venues" above the time settings) were only ever hidden while this
           section lived inside the modal; hide them here too now that it
           renders inline. */
        $repeatSection.find('section.bg-light').hide();
        $repeatSection.find('.mep-special-datetime section.bg-light').hide();

        /* Repeated Event has no separate collapsed summary card (its
           real fields render inline, see initializeParticularDateModal),
           so its own panel head is the only header this section shows -
           give it the same icon-badge/eyebrow look the Single Event and
           Particular Dates summary toolbars already have. */
        const $head = $repeatSection.find('> ._layout_default_xs_mp_zero > .mpwem-date-card__head').first();
        if ($head.length && !$head.data('mpwemIconHeadEnhanced')) {
            const $icon = $('<span class="mpwem-date-card__icon" aria-hidden="true"></span>')
                .html('<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="3"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>');
            const $eyebrow = $('<span class="mpwem-date-card__eyebrow"></span>').text('Repeated Event');
            const $intro = $('<div class="mpwem-date-card__intro"></div>').append($eyebrow);

            $head.children().appendTo($intro);
            $head.empty().append($icon, $intro);
            $head.data('mpwemIconHeadEnhanced', true);
        }

        $repeatSection.find('> ._layout_default_xs_mp_zero > ._padding_bt').each(function() {
            const $row = $(this);
            const $fieldWrap = $row.find('> ._justify_between_align_center_wrap').first();

            $row.addClass('mpwem-repeat-row');

            if ($row.find('input[name="event_start_date_everyday"], input[name="event_start_time_everyday"]').length) {
                $row.addClass('mpwem-repeat-row--start');
            } else if ($row.find('input[name="event_end_date_everyday"], input[name="event_end_time_everyday"]').length) {
                $row.addClass('mpwem-repeat-row--end');
            } else if ($row.find('input[name="mep_repeated_periods"]').length) {
                $row.addClass('mpwem-repeat-row--interval');
            } else if ($row.find('input[name="mep_ticket_offdays"]').length) {
                $row.addClass('mpwem-repeat-row--offdays');
            } else if ($row.find('input[name="mep_ticket_off_dates[]"]').length) {
                $row.addClass('mpwem-repeat-row--offdates');
            }

            $fieldWrap.find('> .dFlex, > ._dFlex').addClass('mpwem-repeat-field-group');
        });
    }

    function setMpwemRowLabelText($mr, text) {
        if (!$mr || !$mr.length) return;
        const $tip = $mr.children('.mpwem-info-tip').first().detach();
        $mr.empty().text(text);
        if ($tip.length) {
            $mr.append($tip);
        }
    }

    function enhanceRepeatRangeLayout($panel) {
        const $repeatSection = $panel.find('[data-collapse="#mep_everyday_event"]').first();
        if (!$repeatSection.length || $repeatSection.data('mpwemRangeLayoutEnhanced')) {
            return;
        }

        // Start/End: merge each row's date + time inputs into one bordered,
        // single-line pill instead of two separate boxed inputs, and shorten
        // the row labels to read as a sentence with the schedule below it.
        ['start', 'end'].forEach(function(kind) {
            const $row = $repeatSection.find('.mpwem-repeat-row--' + kind).first();
            if (!$row.length) return;

            const $fieldWrap = $row.find('> ._justify_between_align_center_wrap').first();
            const $dFlex = $fieldWrap.find('> .dFlex, > ._dFlex').first();
            if ($dFlex.length) {
                $dFlex.addClass('mpwem-repeat-dt-pill');
            }

            const $mr = $fieldWrap.find('> label:first-child > ._mr').first();
            setMpwemRowLabelText($mr, kind === 'start' ? 'Starts' : 'Ends');
        });

        const $intervalRow = $repeatSection.find('.mpwem-repeat-row--interval').first();
        if ($intervalRow.length) {
            const $mr = $intervalRow.find('> label._justify_between_align_center_wrap > ._mr').first();
            setMpwemRowLabelText($mr, 'Repeat every');

            const $intervalInput = $intervalRow.find('input[name="mep_repeated_periods"]').first();
            if ($intervalInput.length && !$intervalInput.parent().hasClass('mpwem-repeat-interval-field')) {
                // Wrap the input together with its "day(s)" suffix so the
                // label stays a plain two-item (text, field) flex row -
                // a bare 3rd sibling here would wrap onto its own line.
                $intervalInput.wrap('<span class="mpwem-repeat-interval-field"></span>');
                $intervalInput.after($('<span class="mpwem-repeat-interval-suffix"></span>').text('day(s)'));
            }
        }

        $repeatSection.data('mpwemRangeLayoutEnhanced', true);
    }

    function buildRepeatSummaryText($repeatSection) {
        const startDateText = $.trim($repeatSection.find('#event_start_date_everyday').val() || '');
        const endDateText = $.trim($repeatSection.find('#event_end_date_everyday').val() || '');
        const interval = parseInt($repeatSection.find('input[name="mep_repeated_periods"]').val(), 10) || 1;
        const freqText = interval === 1 ? 'every day' : 'every ' + interval + ' days';

        let text = 'Repeats ' + freqText;
        if (startDateText) {
            text += ' from ' + startDateText;
            if (endDateText) {
                text += ' to ' + endDateText;
            }
        }

        const skippedDayNames = [];
        $repeatSection.find('.mpwem-repeat-row--offdays .mpwem-offday-option').each(function() {
            const $opt = $(this);
            if (!$opt.find('input[type="checkbox"]').is(':checked')) return;
            const full = $opt.data('mpwemOffDayFullLabel') || $opt.attr('title');
            if (full) skippedDayNames.push(full);
        });
        if (skippedDayNames.length) {
            text += ', skipping ' + skippedDayNames.join(', ');
        }

        return text + '.';
    }

    function refreshRepeatSummaryBanner($repeatSection) {
        let $banner = $repeatSection.find('.mpwem-repeat-summary').first();
        if (!$banner.length) {
            $banner = $(
                '<div class="mpwem-repeat-summary">' +
                    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 3"/></svg>' +
                    '<span class="mpwem-repeat-summary__text"></span>' +
                '</div>'
            );
            const $head = $repeatSection.find('> ._layout_default_xs_mp_zero > ._bg_light_padding').first();
            if ($head.length) {
                $head.after($banner);
            } else {
                $repeatSection.find('> ._layout_default_xs_mp_zero').first().prepend($banner);
            }
        }
        $banner.find('.mpwem-repeat-summary__text').text(buildRepeatSummaryText($repeatSection));
    }

    function enhanceRepeatSummary($repeatSection) {
        refreshRepeatSummaryBanner($repeatSection);

        if ($repeatSection.data('mpwemSummaryBound')) return;

        $repeatSection.on(
            'change',
            'input[name="event_start_date_everyday"], input[name="event_end_date_everyday"], input[name="mep_repeated_periods"], .mpwem-repeat-row--offdays input[type="checkbox"]',
            function() {
                refreshRepeatSummaryBanner($repeatSection);
                const $wizardRoot = $repeatSection.closest('.mpwem-event-wizard');
                updateLiveTicketPreviewDate($wizardRoot.length ? $wizardRoot : $('.mpwem-event-wizard').first(), 'everyday');
            }
        );

        $repeatSection.data('mpwemSummaryBound', true);
    }

    function refreshRepeatedEndDateFieldUI($scope) {
        const $context = $scope && $scope.length ? $scope : $('.mpwem-event-wizard').first();
        const $repeatSection = $context.find('[data-collapse="#mep_everyday_event"]').first();
        if (!$repeatSection.length) return;

        enhanceDateFields($repeatSection);
        enhanceCustomCalendar($repeatSection);
        enhanceRepeatedScheduleLayout($context);
        enhanceRepeatRangeLayout($context);
        enhanceRepeatSkipSection($context);
        enhanceRepeatSummary($repeatSection);
        syncRepeatedDateConstraints($repeatSection);
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

    function countRepeatSkipExceptions($repeatSection) {
        const offDaysCount = $repeatSection.find('.mpwem-repeat-row--offdays input[type="checkbox"]:checked').length;
        const offDatesCount = $repeatSection.find('.mpwem-repeat-row--offdates .mpwem_item_insert').children().length;
        return offDaysCount + offDatesCount;
    }

    function refreshRepeatSkipToggleCount($repeatSection) {
        const $count = $repeatSection.find('.mpwem-repeat-skip-toggle__count');
        if (!$count.length) return;
        const total = countRepeatSkipExceptions($repeatSection);
        $count.text(total ? ' · ' + total + ' skipped' : '');
    }

    function enhanceRepeatSkipSection($panel) {
        const $repeatSection = $panel.find('[data-collapse="#mep_everyday_event"]').first();
        if (!$repeatSection.length) return;

        if ($repeatSection.data('mpwemSkipSectionEnhanced')) {
            refreshRepeatSkipToggleCount($repeatSection);
            return;
        }

        const $offDaysRow = $repeatSection.find('.mpwem-repeat-row--offdays').first();
        const $offDatesRow = $repeatSection.find('.mpwem-repeat-row--offdates').first();
        if (!$offDaysRow.length && !$offDatesRow.length) {
            return;
        }

        setMpwemRowLabelText($offDaysRow.find('> ._justify_between_align_center_wrap > label > ._mr').first(), 'Weekly off days');
        setMpwemRowLabelText($offDatesRow.find('.fdColumn label > span').first(), 'One-off dates');

        const $anchor = $offDaysRow.length ? $offDaysRow : $offDatesRow;
        const $toggleLeft = $('<span class="mpwem-repeat-skip-toggle__left"></span>')
            .append(
                $('<span class="mpwem-repeat-skip-toggle__icon"></span>')
                    .html('<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>')
            )
            .append($('<span></span>').text('Skip specific days or dates'))
            .append($('<span class="mpwem-repeat-skip-toggle__count"></span>'));
        const $toggle = $('<button type="button" class="mpwem-ticket-add__more-toggle mpwem-repeat-skip-toggle" aria-expanded="false"></button>')
            .append($toggleLeft)
            .append(
                $('<span class="mpwem-ticket-add__chev"></span>')
                    .html('<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>')
            );
        const $body = $('<div class="mpwem-ticket-add__more mpwem-repeat-skip-body"></div>').hide();

        $anchor.before($toggle, $body);
        if ($offDaysRow.length) {
            $body.append($offDaysRow.detach());
        }
        if ($offDatesRow.length) {
            $body.append($offDatesRow.detach());
        }

        $toggle.on('click', function() {
            const willOpen = !$body.is(':visible');
            $body.slideToggle(150);
            $toggle.toggleClass('is-open', willOpen).attr('aria-expanded', willOpen ? 'true' : 'false');
        });

        $body.on('change', 'input[type="checkbox"]', function() {
            refreshRepeatSkipToggleCount($repeatSection);
        });
        const $offDatesInsert = $offDatesRow.find('.mpwem_item_insert').first();
        if ($offDatesInsert.length && window.MutationObserver) {
            new MutationObserver(function() {
                refreshRepeatSkipToggleCount($repeatSection);
            }).observe($offDatesInsert[0], { childList: true });
        }

        refreshRepeatSkipToggleCount($repeatSection);

        $repeatSection.data('mpwemSkipSectionEnhanced', true);
    }

    function enhanceDateStep($root) {
        const $panel = getDatePanel($root);
        if (!$panel.length) return;

        decorateDateSections($panel);
        enhanceDateTypeSelector($panel);
        enhanceDateFields($panel);
        enhanceCustomCalendar($panel);
        enhanceOffDayPicker($panel);
        enhanceRepeatSkipSection($panel);
        enhanceDateConditionalSelects($panel);
        syncRepeatedDateConstraints($panel);
        alignDateFormatTooltips($panel);
    }

    function enhanceTaxonomyCard($root) {
        const $cards = $root.find('.mpwem-taxonomy-card');
        if (!$cards.length) {
            return;
        }

        const showTaxonomyCreateMessage = function($message, text, type) {
            if (!$message.length) {
                return;
            }

            $message
                .removeClass('is-success is-error')
                .toggleClass('is-success', type === 'success')
                .toggleClass('is-error', type === 'error')
                .text(text || '');
        };

        const setTaxonomyCreateOpenState = function($card, isOpen) {
            const $panel = $card.find('[data-taxonomy-create-panel]').first();
            const $toggle = $card.find('[data-taxonomy-create-toggle]').first();

            if (!$panel.length || !$toggle.length) {
                return;
            }

            const openLabel = ($toggle.attr('data-open-label') || 'Add').toString();
            const closeLabel = ($toggle.attr('data-close-label') || 'Close').toString();

            $card.toggleClass('is-taxonomy-create-open', isOpen);
            $panel.toggleClass('is-open', isOpen).prop('hidden', !isOpen);
            $toggle
                .attr('aria-expanded', isOpen ? 'true' : 'false')
                .text(isOpen ? closeLabel : openLabel)
                .toggleClass('is-active', isOpen);

            if (isOpen) {
                $panel.find('[data-taxonomy-create-message]').first().removeClass('is-success is-error').text('');
                window.setTimeout(function() {
                    $panel.find('.mpwem-taxonomy-create__input').first().trigger('focus');
                }, 30);
            }
        };

        const ensureChecklistTerm = function($checklist, taxonomy, term) {
            const termId = parseInt(term && term.id, 10) || 0;
            const termName = term && term.name ? term.name.toString() : '';

            if (!termId || !termName || !$checklist.length) {
                return $();
            }

            let $existing = $checklist.find('input[name="' + taxonomy + '[]"][value="' + termId + '"]').closest('.mpwem-taxonomy-checklist__item');
            if ($existing.length) {
                return $existing;
            }

            const $item = $('<label class="mpwem-taxonomy-checklist__item"></label>');
            const $input = $('<input type="checkbox" data-no-mpwem-switch="1" checked="checked" />')
                .attr('name', taxonomy + '[]')
                .val(termId);
            const $label = $('<span class="mpwem-taxonomy-checklist__label"></span>').text(termName);

            $item.append($input, $label);
            $checklist.prepend($item);

            return $item;
        };

        $cards.each(function() {
            const $card = $(this);

            const $slugInput = $card.find('#post_name');
            if ($slugInput.length) {
                $slugInput.on('input.mpwemSlug', function() {
                    const $input = $(this);
                    const $preview = $('#mpwem_slug_preview');
                    const originalUrl = $preview.data('original-url');
                    const originalSlug = $input.data('original-slug');
                    // Simple slug sanitization for preview
                    const newSlug = $input.val().toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-');
                    
                    if (originalUrl && originalSlug) {
                        const newUrl = originalUrl.replace(originalSlug, newSlug);
                        $preview.text(newUrl).attr('href', newUrl);
                    }
                });
            }

            $card.find('input[name="mep_member_only_event"]').each(function() {
                const $toggle = $(this);
                const $roles = $card.find('[data-collapse="#mep_member_only_event"]').first();
                const syncRoles = function() {
                    const isChecked = $toggle.is(':checked');
                    $roles.toggleClass('mActive', isChecked).toggle(isChecked);
                };

                if (!$toggle.data('mpwemMemberOnlyInit')) {
                    $toggle.on('change.mpwemMemberOnly', syncRoles);
                    $toggle.data('mpwemMemberOnlyInit', true);
                }

                syncRoles();
            });

            $card.find('[data-tag-input]').each(function() {
                const $input = $(this);
                const $preview = $card.find('[data-tag-preview]').first();
                const $suggestButtons = $card.find('[data-tag-suggest]');

                if ($input.data('mpwemTagPreviewInit')) {
                    return;
                }

                const getTags = function() {
                    return ($input.val() || '')
                        .toString()
                        .split(',')
                        .map(function(tag) {
                            return tag.trim();
                        })
                        .filter(function(tag, index, allTags) {
                            return tag.length && allTags.indexOf(tag) === index;
                        });
                };

                const renderPreview = function() {
                    const tags = getTags();

                    $preview.empty();

                    if (!tags.length) {
                        $preview.removeClass('has-items');
                        return;
                    }

                    $preview.addClass('has-items');
                    tags.forEach(function(tag) {
                        $('<span class="mpwem-taxonomy-card__chip"></span>').text(tag).appendTo($preview);
                    });
                };

                const renderSuggestions = function() {
                    const tags = getTags();

                    $suggestButtons.each(function() {
                        const $btn = $(this);
                        const tagName = ($btn.data('tag-suggest') || '').toString();
                        $btn.prop('hidden', tags.indexOf(tagName) !== -1);
                    });
                };

                $suggestButtons.on('click.mpwemTagSuggest', function() {
                    const tagName = ($(this).data('tag-suggest') || '').toString();

                    if (!tagName) {
                        return;
                    }

                    const tags = getTags();

                    if (tags.indexOf(tagName) === -1) {
                        tags.push(tagName);
                    }

                    $input.val(tags.join(', ')).trigger('input.mpwemTagPreview');
                });

                $input
                    .on('input.mpwemTagPreview change.mpwemTagPreview blur.mpwemTagPreview', function() {
                        renderPreview();
                        renderSuggestions();
                    })
                    .data('mpwemTagPreviewInit', true);

                renderPreview();
                renderSuggestions();
            });

            $card.find('[data-taxonomy-create-form]').each(function() {
                const $form = $(this);
                const $input = $form.find('.mpwem-taxonomy-create__input').first();
                const $button = $form.find('.mpwem-taxonomy-create__button').first();
                const $message = $form.find('[data-taxonomy-create-message]').first();
                const taxonomy = ($form.data('taxonomy') || '').toString();
                const $checklist = $card.find('.mpwem-taxonomy-checklist').first();
                const postId = parseInt($root.find('[name="post_ID"]').val(), 10) || 0;
                const taxonomyLabel = taxonomy === 'mep_org' ? 'organizer' : 'category';

                if ($form.data('mpwemTaxonomyCreateInit')) {
                    return;
                }

                setTaxonomyCreateOpenState($card, false);

                const submitCreateTerm = function() {
                    const config = getConfig();
                    const termName = ($input.val() || '').toString().trim();

                    if (!termName) {
                        showTaxonomyCreateMessage($message, 'Please enter a ' + taxonomyLabel + ' name.', 'error');
                        $input.trigger('focus');
                        return;
                    }

                    if (!config.ajax_url || !config.term_nonce) {
                        showTaxonomyCreateMessage($message, taxonomyLabel.charAt(0).toUpperCase() + taxonomyLabel.slice(1) + ' creation is not configured.', 'error');
                        return;
                    }

                    $form.addClass('is-loading');
                    $button.prop('disabled', true);
                    showTaxonomyCreateMessage($message, 'Adding ' + taxonomyLabel + '...', 'success');

                    $.post(config.ajax_url, {
                        action: 'mpwem_add_event_taxonomy_term',
                        nonce: config.term_nonce,
                        taxonomy: taxonomy,
                        term_name: termName,
                        post_id: postId
                    }).done(function(response) {
                        if (!(response && response.success && response.data && response.data.term)) {
                            const fallbackMessage = response && response.data && response.data.message ? response.data.message : 'The ' + taxonomyLabel + ' could not be added.';
                            showTaxonomyCreateMessage($message, fallbackMessage, 'error');
                            return;
                        }

                        const $item = ensureChecklistTerm($checklist, taxonomy, response.data.term);
                        $item.find('input[type="checkbox"]').prop('checked', true).trigger('change');
                        $input.val('');
                        showTaxonomyCreateMessage($message, response.data.message || (taxonomyLabel.charAt(0).toUpperCase() + taxonomyLabel.slice(1) + ' added.'), 'success');
                        window.setTimeout(function() {
                            setTaxonomyCreateOpenState($card, false);
                        }, 220);
                    }).fail(function(xhr) {
                        const response = xhr.responseJSON || {};
                        const fallbackMessage = response.data && response.data.message ? response.data.message : 'The ' + taxonomyLabel + ' could not be added.';
                        showTaxonomyCreateMessage($message, fallbackMessage, 'error');
                    }).always(function() {
                        $form.removeClass('is-loading');
                        $button.prop('disabled', false);
                    });
                };

                $button.on('click.mpwemTaxonomyCreate', function(e) {
                    e.preventDefault();
                    submitCreateTerm();
                });

                $card.find('[data-taxonomy-create-toggle]').first().on('click.mpwemTaxonomyCreateToggle', function(e) {
                    e.preventDefault();
                    setTaxonomyCreateOpenState($card, !$card.hasClass('is-taxonomy-create-open'));
                });

                $input.on('keydown.mpwemTaxonomyCreate', function(e) {
                    if (e.key !== 'Enter') {
                        return;
                    }

                    e.preventDefault();
                    submitCreateTerm();
                });

                $form.data('mpwemTaxonomyCreateInit', true);
            });
        });
    }

    function validateDateWiseGlobalQty($root, options) {
        const $datePanel = getDatePanel($root);
        const shouldFocusStep = !!(options && options.focusStep);
        const blockOnError = !!(options && options.blockOnError);
        if (!$datePanel.length) {
            $root.removeData('mpwemMissingDateWiseGlobalQty');
            return true;
        }

        const isGlobalQtyEnabled = $root.find('input[name="enable_global_qty"]').first().is(':checked');
        const globalQtyType = ($root.find('select[name="mep_gq_type"]').first().val() || '').toString();

        if (!isGlobalQtyEnabled || globalQtyType !== 'date_wise') {
            $root.removeData('mpwemMissingDateWiseGlobalQty');
            return true;
        }

        const $requiredFields = $root.find('#mpwem_particular_date_modal_mount input[name="event_date_gq"], #mpwem_particular_date_modal_mount input[name="event_date_gq_md[]"], #mpwem_wizard_date_mount input[name="event_date_gq"], #mpwem_wizard_date_mount input[name="event_date_gq_md[]"]').filter(function() {
            return $(this).closest('.mpwem_hidden_content').length === 0;
        });
        const recurringType = ($datePanel.find('select[name="mep_enable_recurring"]').first().val() || '').toString();

        if (recurringType !== 'yes') {
            $requiredFields.removeClass('mpwem-field-error');
            $root.removeData('mpwemMissingDateWiseGlobalQty');
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
            $root.removeData('mpwemMissingDateWiseGlobalQty');
            return true;
        }

        $root.data('mpwemMissingDateWiseGlobalQty', true);

        if (blockOnError && shouldFocusStep) {
            setActiveStep($root, 'date', { pushHash: true, validate: false });
        }

        if (blockOnError && $firstInvalid.length) {
            window.setTimeout(function() {
                openParticularDateModal($root, 'list');
                window.setTimeout(function() {
                    const $scrollWrap = $firstInvalid.closest('._ov_auto');
                    if ($scrollWrap.length) {
                        const left = $firstInvalid.position().left + $scrollWrap.scrollLeft() - 24;
                        $scrollWrap.animate({ scrollLeft: Math.max(left, 0) }, 220);
                    }
                    $firstInvalid.trigger('focus');
                }, 220);
            }, 80);
        }

        showToast('Global Qty is not set in Particular Date & Time Settings while Global Qty Type is set to Particular Date Wise.', 'warning');
        return !blockOnError;
    }

    // Final-step save button: a not-yet-published event publishes on save, an
    // already-published event updates in place. The button label mirrors the
    // action it performs.
    function isPublishedEvent($root) {
        return String($root.data('is-published')) === '1';
    }
    function finalSaveAction($root) {
        return isPublishedEvent($root) ? '' : 'publish';
    }
    function finalSaveLabel($root) {
        return isPublishedEvent($root) ? 'Save & Update Event' : 'Save & Publish Event';
    }

    // Keeps the bottom nav bar (progress line, step badge/name, Back/Next) in
    // sync with whichever step is active among the currently visible steps.
    function syncWizardFooter($root, $visibleSteps, current) {
        const total = $visibleSteps.length;
        const $activeStep = $visibleSteps.eq(current - 1);
        const stepName = $activeStep.find('.mpwem-step__label').text();
        const $nextStep = $visibleSteps.eq(current);
        const nextName = $nextStep.length ? $nextStep.find('.mpwem-step__label').text() : '';

        $root.find('.mpwem-wizard-footer-progress > span').css('width', (current / total * 100) + '%');
        $root.find('.mpwem-wizard-step-num').text(current);
        $root.find('.mpwem-wizard-step-name').text(stepName);
        $root.find('.mpwem-wizard-progress').text('Step ' + current + ' of ' + total);
        $root.find('.mpwem-wizard-prev').prop('disabled', current === 1);
        $root.find('.mpwem-wizard-next').text(
            current === total ? finalSaveLabel($root) : (nextName ? 'Next: ' + nextName : 'Next Step')
        );
    }

    // Quietly saves the whole wizard form in the background (no status change,
    // no validation, no page reload) when the user moves between steps, so
    // what was entered in the step being left is stored before the next one
    // opens. Same server path as a Save as Draft / Update, flagged quiet.
    // PHP drops form fields past max_input_vars (default 1000), and this form
    // is bigger than that. Every field travels as one JSON string instead;
    // MPWEM_Event_Edit_Page::unpack_form_payload() rebuilds $_POST from it.
    function packFormEntries(form) {
        const entries = [];
        new window.FormData(form).forEach(function(value, key) {
            if (typeof value === 'string' && key !== 'mpwem_form_json') {
                entries.push([key, value]);
            }
        });
        return JSON.stringify(entries);
    }

    let stepAutoSaveRunning = false;
    let stepAutoSaveQueued = false;

    function autoSaveEventForm($root) {
        const form = document.getElementById('mpwem-event-edit-form');
        if (!form || typeof window.fetch !== 'function' || typeof window.FormData !== 'function') {
            return;
        }
        if (stepAutoSaveRunning) {
            stepAutoSaveQueued = true;
            return;
        }

        if (window.tinymce && typeof window.tinymce.triggerSave === 'function') {
            window.tinymce.triggerSave();
        }

        $('#mpwem_post_status_action').val('');
        $('#mpwem_quiet_save').val('1');
        $('#mpwem_active_step').val(($root.find('.mpwem-step.is-active').data('step-key') || STEP_KEY_FALLBACK).toString());

        const data = new window.FormData();
        data.set('action', 'mpwem_event_edit_save');
        data.set('mpwem_form_json', packFormEntries(form));

        stepAutoSaveRunning = true;
        window.fetch(form.action, {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
            redirect: 'manual'
        }).then(function() {
            showToast('Progress saved', 'success');
        }).catch(function() {
            showToast('Could not save your progress. Check your connection.', 'error');
        }).then(function() {
            stepAutoSaveRunning = false;
            if (stepAutoSaveQueued) {
                stepAutoSaveQueued = false;
                autoSaveEventForm($root);
            }
        });
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
            // Require a valid Basic step before advancing to a later step.
            if (!validateBasicStep($root, { focus: true })) {
                return;
            }
            // When leaving the Tickets step forward, enforce its requirements.
            if (($activeStep.data('step-key') === 'tickets') && !validateTicketsStep($root, { focus: true })) {
                return;
            }
            // When leaving the Date step forward, enforce its requirements.
            if (($activeStep.data('step-key') === 'date') && !validateDateStep($root, { focus: true })) {
                return;
            }
            if (!validateDateWiseGlobalQty($root)) {
                return;
            }
        }

        // Leaving a step by Next / the stepper: store what was entered so far.
        if (options && options.validate && $activeStep.length && $targetStep[0] !== $activeStep[0]) {
            autoSaveEventForm($root);
        }

        $steps.removeClass('is-active is-complete');
        $steps.each(function () {
            if ($(this).index() < $targetStep.index()) {
                $(this).addClass('is-complete');
            }
        });
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
            enhanceTaxonomyCard($root);
            if (stepKey === 'date') {
                enhanceDateStep($root);
            }
            // Attendee Form (PRO formBuilder) must init only when Advanced is painted —
            // building while the step is display:none leaves an empty canvas.
            if (stepKey === 'display') {
                // Initialize Attendee Form only when its persisted status is enabled.
                // A saved form selection must not silently turn the feature back on.
                window.requestAnimationFrame(function() {
                    window.setTimeout(function() {
                        const $attendeeMount = $root.find('#mpwem_wizard_attendee_form_mount').first();
                        const $status = $attendeeMount.find('input[name="mep_event_reg_form_status"]').first();
                        const attendeeFormEnabled = !$status.length || $status.is(':checked');
                        if (attendeeFormEnabled && typeof window.mepFbEventBuilderInit === 'function') {
                            window.mepFbEventBuilderInit(true);
                        }
                        $(document).trigger('mpwem:display-step-active', [$root, $panel]);
                    }, 120);
                });
            }
        }

        // Legacy hook: #mpwem_event_edit_sidebar may be rendered by add-on panels
        // (e.g. gallery, SEO) that hook into the basic step sidebar area.
        // #mpwem_edit_page_additional is the non-basic step supplemental area.
        if (stepKey === 'basic') {
            $('#mpwem_event_edit_sidebar').show();
            $('#mpwem_edit_page_additional').hide();
        } else {
            $('#mpwem_event_edit_sidebar').hide();
            $('#mpwem_edit_page_additional').show();
        }

        // Progress
        const $visibleSteps = $steps.filter(function() { return $(this).css('display') !== 'none'; });
        const current = $visibleSteps.index($targetStep) + 1;
        syncWizardFooter($root, $visibleSteps, current);
        $root.find('#mpwem_active_step').val(stepKey);

        if (options && options.pushHash) {
            setHash(parseInt($root.data('event-id'), 10) > 0 ? 'edit' : 'new', $root.data('event-id'), stepKey);
        }
    }

    function updateStepsVisibility($root) {
        const mode = ($('#mep_reg_status').val() || '').toString();
        const $steps = $root.find('.mpwem-step');
        const $ticketStep = $steps.filter('[data-step-key="tickets"]');
        
        // Tickets & Pricing step is always kept visible
        $ticketStep.show();

        // Toggle ticket details visibility inside Step 2 based on mode
        if (mode === 'on') {
            $('#mpwem_wizard_ticket_details_section').show();
            $('#mpwem_wizard_ticket_details_section').find('.mpwem-ticket-settings-head').show();
            $('.mpwem-ticket-warnings').show();
            $('#mpwem_wizard_payment_status_card').show();
            $('#mpwem_wizard_ticket_details_section').find('._bg_light_padding').show();
            
            // Only show extra services card if it actually has content
            if ($('#mpwem_wizard_extra_services_mount').children().length > 0) {
                $('#mpwem_wizard_extra_services_card').show();
            }
            
            $('#mpwem_wizard_ticket_details_section').find('.mpwem-ticket-cards-container, #mpwem_ticket_summary, .mpwem_settings_area, .mpwem-ticket-footer').show();
            $('#mpwem_wizard_ticket_details_section').find('.mpwem-rsvp-settings-area').hide();
            $('#mpwem_wizard_ticket_details_section').find('.mpwem-announcement-settings-area').hide();
            $('#mpwem_wizard_ticket_details_section').find('.mpwem-ticket-action-bar__item').show();
            $('#mpwem_wizard_ticket_details_section').find('.mpwem-ticket-action-bar__divider').show();
            $('#mpwem_wizard_ticket_details_section').find('.mpwem-ticket-global-card').show();
            $('#mpwem_wizard_pricing_help_card').show();
            $('#mpwem_wizard_tickets_sidebar').show();
        } else if (mode === 'rsvp') {
            $('#mpwem_wizard_ticket_details_section').show();
            
            // Hide the main Ticket & Pricing Settings header card
            $('#mpwem_wizard_ticket_details_section').find('.mpwem-ticket-settings-head').hide();
            $('.mpwem-ticket-warnings').hide();
            $('#mpwem_wizard_payment_status_card').hide();

            // Hide only the ticket editor's header, not the RSVP one
            $('#mpwem_wizard_ticket_details_section').find('.mpwem-ticket-editor-section ._bg_light_padding, .mpwem-extra-service-section ._bg_light_padding').hide();
            $('#mpwem_wizard_extra_services_card').hide();
            
            // Hide the ticket lists and summaries, explicitly excluding RSVP area children
            $('#mpwem_wizard_ticket_details_section').find('.mpwem-ticket-editor-section .mpwem-ticket-cards-container, .mpwem-ticket-editor-section .mpwem_settings_area, .mpwem-ticket-editor-section .mpwem-ticket-footer').hide();
            $('#mpwem_wizard_ticket_details_section').find('#mpwem_ticket_summary').hide();
            
            $('#mpwem_wizard_ticket_details_section').find('.mpwem-rsvp-settings-area').show();
            $('#mpwem_wizard_ticket_details_section').find('.mpwem-announcement-settings-area').hide();
            $('#mpwem_wizard_ticket_details_section').find('.mpwem-rsvp-settings-area .mpwem-ticket-cards-container').show();
            $('#mpwem_wizard_ticket_details_section').find('.mpwem-rsvp-settings-area .mpwem_settings_area').show();
            $('#mpwem_wizard_ticket_details_section').find('.mpwem-rsvp-settings-area ._bg_light_padding').show();

            // Hide EARLY BIRD and SHOW ADVANCED COLUMN options, keep only ENABLE GLOBAL QTY
            $('#mpwem_wizard_ticket_details_section').find('.mpwem-ticket-action-bar__item').each(function() {
                const $item = $(this);
                if ($item.find('label').text().toUpperCase().indexOf('GLOBAL') !== -1) {
                    $item.show();
                } else {
                    $item.hide();
                }
            });
            // Hide dividers since we only show one option
            $('#mpwem_wizard_ticket_details_section').find('.mpwem-ticket-action-bar__divider').hide();
            // Show global settings card
            $('#mpwem_wizard_ticket_details_section').find('.mpwem-ticket-global-card').show();
            $('#mpwem_wizard_pricing_help_card').hide();
            $('#mpwem_wizard_tickets_sidebar').show();
        } else if (mode === 'announcement') {
            const $details = $('#mpwem_wizard_ticket_details_section');
            $details.show();
            // Everything that belongs to selling or RSVP is irrelevant here.
            $details.find('.mpwem-ticket-settings-head').hide();
            $('.mpwem-ticket-warnings').hide();
            $('#mpwem_wizard_payment_status_card').hide();
            $details.find('.mpwem-ticket-editor-section, .mpwem-extra-service-section').hide();
            $details.find('#mpwem_ticket_summary').hide();
            $details.find('.mpwem-ticket-action-bar__item, .mpwem-ticket-action-bar__divider').hide();
            $details.find('.mpwem-ticket-global-card').hide();
            $details.find('.mpwem-rsvp-settings-area').hide();
            $('#mpwem_wizard_extra_services_card').hide();
            $('#mpwem_wizard_pricing_help_card').hide();
            $('#mpwem_wizard_tickets_sidebar').show();
            // ...and the announcement + enquiry form settings take their place.
            $details.find('.mpwem-announcement-settings-area').show();
        } else {
            $('#mpwem_wizard_ticket_details_section').hide();
            $('#mpwem_wizard_extra_services_card').hide();
            $('#mpwem_wizard_pricing_help_card').hide();
            $('#mpwem_wizard_tickets_sidebar').show();
            $('.mpwem-ticket-warnings').hide();
            $('#mpwem_wizard_payment_status_card').hide();
            $('#mpwem_wizard_ticket_details_section').find('.mpwem-rsvp-settings-area').hide();
            $('#mpwem_wizard_ticket_details_section').find('.mpwem-announcement-settings-area').hide();
        }

        // Show/hide legacy registration closed message setting card
        if (mode === 'off') {
            $('.reg_close_msg_dash').show();
        } else {
            $('.reg_close_msg_dash').hide();
        }

        // Re-calculate data-step indices for visible steps
        const $visibleSteps = $steps.filter(function() { return $(this).css('display') !== 'none'; });
        $visibleSteps.each(function (index) {
            $(this).attr('data-step', index + 1);
        });

        // Update progress counters for the active step
        const $activeStep = $steps.filter('.is-active');
        if ($activeStep.length) {
            const current = $visibleSteps.index($activeStep) + 1;
            syncWizardFooter($root, $visibleSteps, current);
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
                    $wrap.find('.mpwem-featured-image__preview').find('img').remove();
                    $wrap.find('.mpwem-featured-image__preview').prepend($image);
                    $wrap.find('.mpwem-featured-image__filled').show();
                    $wrap.find('.mpwem-featured-image__dropzone').hide();
                    setFieldError($wrap, $wrap, '');
                });
            }

            mediaFrame.open();
        });

        $root.on('click', '.mpwem-featured-remove', function(e) {
            e.preventDefault();

            const $wrap = $root.find('.mpwem-featured-image').first();
            $root.find('#mpwem_featured_image_id').val('');
            $wrap.attr('data-has-image', '0');
            $wrap.find('.mpwem-featured-image__preview').find('img').remove();
            $wrap.find('.mpwem-featured-image__filled').hide();
            $wrap.find('.mpwem-featured-image__dropzone').show();
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

    function bindTrashConfirmation($root) {
        const $modal = $root.find('#mpwem_trash_event_modal').first();
        if (!$modal.length) return;

        const closeModal = function() {
            $modal.attr('aria-hidden', 'true').removeClass('is-open');
        };

        const openModal = function() {
            $modal.attr('aria-hidden', 'false').addClass('is-open');
        };

        $modal.on('click', '.mpwem-confirm-modal__backdrop, .mpwem-confirm-modal__close, .mpwem-confirm-cancel', function(e) {
            e.preventDefault();
            closeModal();
        });

        $(document).on('keydown.mpwemTrashConfirm', function(e) {
            if (e.key === 'Escape' && $modal.hasClass('is-open')) {
                closeModal();
            }
        });

        $root.on('click', 'button.mpwem-status-actions__menu-item', function(e) {
            const action = ($(this).data('status-action') || '').toString();
            if (action !== 'trash') {
                return;
            }

            e.preventDefault();
            if (!validateDateWiseGlobalQty($root)) {
                return;
            }

            closeStatusActionMenus();
            openModal();
        });

        $modal.on('click', '.mpwem-confirm-trash', function(e) {
            e.preventDefault();
            closeModal();
            submitEventForm($root, 'trash');
        });
    }

    function getVirtualDetailsValue() {
        const editorId = 'mp_event_virtual_type_des';
        let content = '';

        if (typeof tinymce !== 'undefined') {
            const editor = tinymce.get(editorId);
            if (editor && typeof editor.getContent === 'function' && !editor.isHidden()) {
                content = editor.getContent();
            }
        }

        if (!content) {
            const $textarea = $('#' + editorId);
            if ($textarea.length) {
                content = $textarea.val() || '';
            }
        }

        return $.trim(
            (content || '')
                .replace(/<[^>]*>/g, '')
                .replace(/&nbsp;/gi, ' ')
        );
    }

    // Toggle the red error state on a field and show/hide an inline message
    // right below it ($anchor = element the message is inserted after).
    function setFieldError($field, $anchor, message) {
        $anchor = ($anchor && $anchor.length) ? $anchor : $field;
        $anchor.siblings('.mpwem-field-error-msg[data-for="basic"]').remove();
        $field.toggleClass('mpwem-field-error', !!message);
        if (message) {
            $('<p class="mpwem-field-error-msg" data-for="basic" role="alert"></p>')
                .text(message)
                .insertAfter($anchor);
        }
    }

    function validateBasicStep($root, options) {
        options = options || {};
        const focus = options.focus !== false;
        const messages = [];
        let $firstInvalid = $();

        // Event title is always required.
        let $title = $root.find('input[name="post_title"]').first();
        if (!$title.length) {
            $title = $('#title');
        }
        const titleVal = $.trim(($title.val() || '').toString());
        setFieldError($title, $title, titleVal === '' ? 'Event title is required.' : '');
        if (titleVal === '') {
            messages.push('Event title is required.');
            if (!$firstInvalid.length) $firstInvalid = $title;
        }

        // Featured image is always required.
        const $thumbId = $root.find('#mpwem_featured_image_id').first();
        const $thumbWrap = $root.find('.mpwem-featured-image').first();
        if ($thumbId.length && $thumbWrap.length) {
            const hasImage = $.trim(($thumbId.val() || '').toString()) !== '' && $thumbId.val() !== '0';
            setFieldError($thumbWrap, $thumbWrap, hasImage ? '' : 'Featured image is required.');
            if (!hasImage) {
                messages.push('Featured image is required.');
                if (!$firstInvalid.length) $firstInvalid = $thumbWrap;
            }
        }

        // Event type drives the venue / virtual requirements.
        const eventType = ($root.find('input[name="mep_event_type"]').val() || 'offline').toString();
        const needsVenue = (eventType === 'offline' || eventType === 'hybrid');
        const needsVirtual = (eventType === 'online' || eventType === 'hybrid');

        const $venue = $root.find('input[name="mep_location_venue"]').first();
        if (needsVenue) {
            const venueVal = $.trim(($venue.val() || '').toString());
            setFieldError($venue, $venue, venueVal === '' ? 'Location/Venue is required.' : '');
            if (venueVal === '') {
                messages.push('Location/Venue is required for in-person events.');
                if (!$firstInvalid.length) $firstInvalid = $venue;
            }
        } else {
            setFieldError($venue, $venue, '');
        }

        const $virtualSection = $('#mpev-online-event');
        if (needsVirtual) {
            // A join link alone is enough to run an online event.
            const virtualVal = getVirtualDetailsValue() || $.trim(($('#mep_event_virtual_link').val() || '').toString());
            $virtualSection.toggleClass('mpwem-field-error', virtualVal === '');
            if (virtualVal === '') {
                messages.push('Virtual event details or link are required for online events.');
                if (!$firstInvalid.length) $firstInvalid = $virtualSection;
            }
        } else {
            $virtualSection.removeClass('mpwem-field-error');
        }

        if (!messages.length) {
            return true;
        }

        if (focus) {
            setActiveStep($root, 'basic', { pushHash: true, validate: false });
            if ($firstInvalid && $firstInvalid.length) {
                window.setTimeout(function() {
                    try {
                        $firstInvalid[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    } catch (e) {}
                    if ($firstInvalid.is('input, textarea')) {
                        $firstInvalid.trigger('focus');
                    }
                }, 120);
            }
            showToast(messages[0], 'error');
        }

        return false;
    }

    function rowFieldValue($row, name) {
        return ($row.find('[name="' + name + '"]').first().val() || '').toString().trim();
    }

    // True when any ticket type or extra service has a non-zero price. A price
    // counts as paid if it holds any digit 1-9, so "0", "0.00" and "" are free
    // while "0,50" (comma decimal) is not mistaken for zero.
    function hasPaidItems($root) {
        const isPaid = function(value) {
            return /[1-9]/.test((value || '').toString());
        };
        let paid = false;
        getTicketRows($root).each(function() {
            if (isPaid(rowFieldValue($(this), 'option_price_t[]'))) {
                paid = true;
                return false;
            }
        });
        if (paid) {
            return true;
        }
        $root.find('[name="option_price[]"]').each(function() {
            if ($(this).closest('.mpwem_hidden_content').length === 0 && isPaid($(this).val())) {
                paid = true;
                return false;
            }
        });
        return paid;
    }

    function validateTicketsStep($root, options) {
        options = options || {};
        const focus = options.focus !== false;
        const skipPaymentCheck = options.skipPaymentCheck === true;

        // Requirements only apply to the Ticket-Selling (paid) event mode.
        if (!isRegistrationEnabled($root)) {
            return true;
        }

        let message = '';
        let onFix = null;

        // 1) At least one payment method must be configured.
        //    Skipped when saving ticket types from the modal so the user can
        //    build their ticket list first and configure payment later.
        //    The check still runs on Next Step navigation and Publish/Update.
        //    Free-only events need no gateway: both checkouts complete a zero
        //    total order without payment.
        if (!skipPaymentCheck && hasPaidItems($root)) {
            const $paymentWarning = $('.mpwem-payment-warning');
            if ($paymentWarning.length && $paymentWarning.is(':visible')) {
                message = 'Please configure at least one payment method to sell tickets.';
                onFix = function() {
                    try {
                        $paymentWarning[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    } catch (e) {}
                    flashAttention($paymentWarning.find('.mep-payment-settings-trigger').first());
                };
            }
        }

        // 2) At least one ticket type is required.
        const $rows = getTicketRows($root);
        if (!message && $rows.length === 0) {
            message = 'Please add at least one ticket type.';
            // Same as clicking "Create First Ticket" itself - opens the
            // inline "Add New Ticket Type" form (scrolled into view and
            // focused), not the old ticket modal popup.
            onFix = function() {
                openInlineTicketAdd($root);
            };
        }

        // 3) Each ticket type needs a name, price and capacity.
        if (!message) {
            const requireCapacity = !isTicketGlobalQtyEnabled($root);
            let $invalidRow = $();
            let $invalidField = $();

            $rows.each(function() {
                if (message) return;
                const $row = $(this);
                const name = rowFieldValue($row, 'option_name_t[]');
                const price = rowFieldValue($row, 'option_price_t[]');
                const capacity = rowFieldValue($row, 'option_qty_t[]');

                const $nameField = $row.find('[name="option_name_t[]"]').first();
                const $priceField = $row.find('[name="option_price_t[]"]').first();
                const $capacityField = $row.find('[name="option_qty_t[]"]').first();

                $nameField.toggleClass('mpwem-field-error', name === '');
                $priceField.toggleClass('mpwem-field-error', price === '');
                $capacityField.toggleClass('mpwem-field-error', requireCapacity && capacity === '');

                if (name === '') {
                    message = 'Each ticket type needs a name.';
                    $invalidRow = $row; $invalidField = $nameField;
                } else if (price === '') {
                    message = 'Each ticket type needs a price.';
                    $invalidRow = $row; $invalidField = $priceField;
                } else if (requireCapacity && capacity === '') {
                    message = 'Each ticket type needs a capacity.';
                    $invalidRow = $row; $invalidField = $capacityField;
                }
            });

            if (message) {
                const invalidMessage = message;
                const invalidFieldName = $invalidField.attr('name');
                onFix = function() {
                    // Show the problem in the inline ticket form (opening it
                    // for that row if needed) instead of the ticket modal popup.
                    const rowIndex = getTicketRows($root).index($invalidRow);
                    const $card = getInlineTicketAddCard($root);
                    const openRow = $card.data('mpwemInlineAddRow');
                    const alreadyOpen = $card.hasClass('is-open') && openRow && openRow[0] === $invalidRow[0];
                    if (!alreadyOpen && rowIndex > -1) {
                        openInlineTicketEdit($root, rowIndex);
                    }
                    window.setTimeout(function() {
                        const $groups = $card.find('.mpwem-ticket-add__core .mpwem-ticket-add__group');
                        const groupIndex = invalidFieldName === 'option_name_t[]' ? 0 : (invalidFieldName === 'option_price_t[]' ? 1 : 2);
                        const $input = $groups.eq(groupIndex).find('input').first();
                        setTicketAddError($input, invalidMessage.replace('Each ticket type needs a', 'Ticket') .replace(/\.$/, '') + ' is required.');
                        scrollToViewportCenter($card);
                        $input.trigger('focus');
                    }, 160);
                };
            }
        }

        // 4) Global Qty "Full Event Base" requires a Total Qty.
        if (!message) {
            const globalOn = $root.find('input[name="enable_global_qty"]').first().is(':checked');
            const gqType = ($root.find('select[name="mep_gq_type"]').first().val() || 'global').toString();
            if (globalOn && gqType === 'global') {
                const $totalQty = $root.find('input[name="mep_gq_total_seat"]').first();
                const totalVal = ($totalQty.val() || '').toString().trim();
                const invalid = totalVal === '' || isNaN(Number(totalVal)) || Number(totalVal) <= 0;
                $totalQty.toggleClass('mpwem-field-error', invalid);
                if (invalid) {
                    message = 'Enter the Total Qty for the Full Event Base global quantity.';
                    onFix = function() {
                        openTicketModal($root, 'edit');
                        window.setTimeout(function() {
                            try { $totalQty[0].scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) {}
                            flashAttention($totalQty);
                            if ($totalQty.is(':visible')) $totalQty.trigger('focus');
                        }, 160);
                    };
                }
            }
        }

        // 5) Early bird: each ticket needs a Start and End sale date/time, End after Start.
        if (!message) {
            const earlyBirdOn = $root.find('input[name="mep_enable_early_bird_status"]').first().is(':checked');
            if (earlyBirdOn) {
                let $invalidRow = $();
                let $invalidField = $();

                $rows.each(function() {
                    if (message) return;
                    const $row = $(this);
                    const $startDate = $row.find('input[name="option_sale_start_date[]"]').first();
                    const $startTime = $row.find('input[name="option_sale_start_time[]"]').first();
                    const $endDate = $row.find('input[name="option_sale_end_date[]"]').first();
                    const $endTime = $row.find('input[name="option_sale_end_time[]"]').first();

                    // The hidden date input holds the Y-m-d value; the paired
                    // visible .date_type field is what we highlight/focus.
                    const $startDateUI = $startDate.closest('label').find('.date_type').first();
                    const $endDateUI = $endDate.closest('label').find('.date_type').first();

                    const startDate = ($startDate.val() || '').toString().trim();
                    const startTime = ($startTime.val() || '').toString().trim();
                    const endDate = ($endDate.val() || '').toString().trim();
                    const endTime = ($endTime.val() || '').toString().trim();

                    [$startTime, $endTime].forEach(function($f) { $f.removeClass('mpwem-field-error'); });
                    [$startDateUI, $endDateUI].forEach(function($f) { $f.removeClass('mpwem-field-error'); });

                    if (startDate === '') {
                        $startDateUI.addClass('mpwem-field-error');
                        message = 'Early bird is on — set the sale Start date for each ticket.';
                        $invalidRow = $row; $invalidField = $startDateUI;
                    } else if (startTime === '') {
                        $startTime.addClass('mpwem-field-error');
                        message = 'Early bird is on — set the sale Start time for each ticket.';
                        $invalidRow = $row; $invalidField = $startTime;
                    } else if (endDate === '') {
                        $endDateUI.addClass('mpwem-field-error');
                        message = 'Early bird is on — set the sale End date for each ticket.';
                        $invalidRow = $row; $invalidField = $endDateUI;
                    } else if (endTime === '') {
                        $endTime.addClass('mpwem-field-error');
                        message = 'Early bird is on — set the sale End time for each ticket.';
                        $invalidRow = $row; $invalidField = $endTime;
                    } else {
                        const startDT = new Date(startDate + 'T' + startTime);
                        const endDT = new Date(endDate + 'T' + endTime);
                        if (!(endDT.getTime() > startDT.getTime())) {
                            $endDateUI.addClass('mpwem-field-error');
                            $endTime.addClass('mpwem-field-error');
                            message = 'The sale End date & time must be later than the Start date & time.';
                            $invalidRow = $row; $invalidField = $endTime;
                        }
                    }
                });

                if (message) {
                    onFix = function() {
                        openTicketModal($root, 'edit');
                        window.setTimeout(function() {
                            highlightRow($invalidRow);
                            if ($invalidField.is(':visible')) $invalidField.trigger('focus');
                        }, 160);
                    };
                }
            }
        }

        // 6) Extra services are optional, but a named service needs price + quantity.
        if (!message) {
            const $extraRows = getExtraServiceRows($root);
            let $invalidRow = $();
            let $invalidField = $();

            $extraRows.each(function() {
                if (message) return;
                const $row = $(this);
                const name = rowFieldValue($row, 'option_name[]');
                const $priceField = $row.find('[name="option_price[]"]').first();
                const $qtyField = $row.find('[name="option_qty[]"]').first();

                if (name === '') {
                    $priceField.removeClass('mpwem-field-error');
                    $qtyField.removeClass('mpwem-field-error');
                    return;
                }

                const price = rowFieldValue($row, 'option_price[]');
                const qty = rowFieldValue($row, 'option_qty[]');

                $priceField.toggleClass('mpwem-field-error', price === '');
                $qtyField.toggleClass('mpwem-field-error', qty === '');

                if (price === '') {
                    message = 'Add a price for the extra service "' + name + '".';
                    $invalidRow = $row; $invalidField = $priceField;
                } else if (qty === '') {
                    message = 'Add an available quantity for the extra service "' + name + '".';
                    $invalidRow = $row; $invalidField = $qtyField;
                }
            });

            if (message) {
                onFix = function() {
                    openExtraServiceModal($root, 'edit');
                    window.setTimeout(function() {
                        highlightRow($invalidRow);
                        if ($invalidField.is(':visible')) $invalidField.trigger('focus');
                    }, 140);
                };
            }
        }

        if (!message) {
            return true;
        }

        if (focus) {
            setActiveStep($root, 'tickets', { pushHash: true, validate: false });
            if (onFix) {
                window.setTimeout(onFix, 160);
            }
            showToast(message, 'error');
        }

        return false;
    }

    // The visible date field paired with a hidden Y-m-d input (datepicker display).
    function dateVisibleField($hidden) {
        const $v = $hidden.closest('label').find('input').not('[type="hidden"]').first();
        return $v.length ? $v : $hidden;
    }

    // True when a <select> has a real, non-placeholder option chosen.
    function selectHasRealValue($select) {
        if (!$select.length) return false;
        const $opt = $select.find('option:selected').first();
        if (!$opt.length || $opt.is('[disabled]')) return false;
        if (typeof $opt.attr('value') === 'undefined') return false;
        return ($select.val() || '').toString().trim() !== '';
    }

    // Validate a start/end date+time pair. Marks invalid fields and returns
    // { message, $field } on the first problem, or null when valid.
    function checkDateTimePair($sd, $st, $ed, $et, label) {
        const $sdUI = dateVisibleField($sd);
        const $edUI = dateVisibleField($ed);
        [$sdUI, $st, $edUI, $et].forEach(function($f) { $f.removeClass('mpwem-field-error'); });

        const sd = ($sd.val() || '').toString().trim();
        const st = ($st.val() || '').toString().trim();
        const ed = ($ed.val() || '').toString().trim();
        const et = ($et.val() || '').toString().trim();

        if (sd === '') { $sdUI.addClass('mpwem-field-error'); return { message: label + ' start date is required.', $field: $sdUI }; }
        if (st === '') { $st.addClass('mpwem-field-error'); return { message: label + ' start time is required.', $field: $st }; }
        if (ed === '') { $edUI.addClass('mpwem-field-error'); return { message: label + ' end date is required.', $field: $edUI }; }
        if (et === '') { $et.addClass('mpwem-field-error'); return { message: label + ' end time is required.', $field: $et }; }

        if (!(new Date(ed + 'T' + et).getTime() > new Date(sd + 'T' + st).getTime())) {
            $edUI.addClass('mpwem-field-error');
            $et.addClass('mpwem-field-error');
            return { message: label + ' end date & time must be later than the start date & time.', $field: $edUI };
        }
        return null;
    }

    // Each row is { $sd, $st, $ed, $et } and has already passed
    // checkDateTimePair(). Marks the End of the first row that runs into the
    // next one (by start time) and returns { message, $field }, or null.
    function checkDateRowsOverlap(rows) {
        const parsed = rows.map(function(row) {
            const sd = (row.$sd.val() || '').toString().trim();
            const st = (row.$st.val() || '').toString().trim();
            const ed = (row.$ed.val() || '').toString().trim();
            const et = (row.$et.val() || '').toString().trim();
            return {
                row: row,
                sd: sd,
                st: st,
                start: new Date(sd + 'T' + st).getTime(),
                end: new Date(ed + 'T' + et).getTime()
            };
        }).filter(function(item) {
            return !isNaN(item.start) && !isNaN(item.end);
        }).sort(function(a, b) {
            return a.start - b.start;
        });

        for (let i = 1; i < parsed.length; i++) {
            const prev = parsed[i - 1];
            const next = parsed[i];
            if (prev.end > next.start) {
                const $edUI = dateVisibleField(prev.row.$ed);
                $edUI.addClass('mpwem-field-error');
                prev.row.$et.addClass('mpwem-field-error');
                return {
                    message: 'The date starting ' + buildScheduleDateTimeLabel(prev.sd, prev.st) + ' ends after the next date starts (' + buildScheduleDateTimeLabel(next.sd, next.st) + '). Change its End so it finishes before the next date starts.',
                    $field: $edUI
                };
            }
        }
        return null;
    }

    function validateDateStep($root, options) {
        options = options || {};
        const focus = options.focus !== false;

        // "Undated Event": the whole date area is hidden and cleared on save, so there
        // is nothing here to validate. Mirrors the server-side skip in
        // MPWEM_Event_Edit_Page::handle_save().
        const $noDate = $root.find('input[name="mep_event_no_date"]').first();
        if ($noDate.length && $noDate.is(':checked')) {
            $root.find('.mpwem-field-error').removeClass('mpwem-field-error');
            return true;
        }

        const type = ($root.find('select[name="mep_enable_recurring"]').first().val() || 'no').toString();
        const findByName = function(name) {
            return $root.find('[name="' + name + '"]').filter(function() {
                return $(this).closest('.mpwem_hidden_content').length === 0;
            });
        };

        let error = null;

        let mainNames;
        let moreNames = null;
        if (type === 'yes') {
            mainNames = { sd: 'event_start_date', st: 'event_start_time', ed: 'event_end_date', et: 'event_end_time' };
            moreNames = { sd: 'event_more_start_date[]', st: 'event_more_start_time[]', ed: 'event_more_end_date[]', et: 'event_more_end_time[]' };
        } else if (type === 'everyday') {
            mainNames = { sd: 'event_start_date_everyday', st: 'event_start_time_everyday', ed: 'event_end_date_everyday', et: 'event_end_time_everyday' };
        } else {
            mainNames = { sd: 'event_start_date_normal', st: 'event_start_time_normal', ed: 'event_end_date_normal', et: 'event_end_time_normal' };
            moreNames = { sd: 'event_more_start_date_normal[]', st: 'event_more_start_time_normal[]', ed: 'event_more_end_date_normal[]', et: 'event_more_end_time_normal[]' };
        }

        // Main schedule pair.
        error = checkDateTimePair(
            findByName(mainNames.sd).first(),
            findByName(mainNames.st).first(),
            findByName(mainNames.ed).first(),
            findByName(mainNames.et).first(),
            'Event'
        );
        if (error) {
            // The event's main schedule is what the server requires (and what the
            // front end reads first). An extra "more dates" row can't stand in
            // for it, so send the user to fill the main slot: the fix below
            // triggers "+ Add Schedule", which now writes into the empty main
            // fields before it ever creates an extra row.
            error.isMissingMainSchedule = true;
        }

        // "Add More Date" rows (Single / Particular).
        if (!error && moreNames) {
            const $sds = findByName(moreNames.sd);
            const $sts = findByName(moreNames.st);
            const $eds = findByName(moreNames.ed);
            const $ets = findByName(moreNames.et);
            $sds.each(function(i) {
                if (error) return;
                error = checkDateTimePair($sds.eq(i), $sts.eq(i), $eds.eq(i), $ets.eq(i), 'Additional date');
            });
        }

        // Single / Particular rows must not overlap: a row that ends after the
        // next one starts is shown on the front end as one continuous range.
        // Mirrors the 'date_overlap' check in MPWEM_Event_Edit_Page::handle_save().
        if (!error && moreNames) {
            const rows = [
                { $sd: findByName(mainNames.sd).first(), $st: findByName(mainNames.st).first(), $ed: findByName(mainNames.ed).first(), $et: findByName(mainNames.et).first() }
            ];
            const $sds = findByName(moreNames.sd);
            const $sts = findByName(moreNames.st);
            const $eds = findByName(moreNames.ed);
            const $ets = findByName(moreNames.et);
            $sds.each(function(i) {
                rows.push({ $sd: $sds.eq(i), $st: $sts.eq(i), $ed: $eds.eq(i), $et: $ets.eq(i) });
            });
            error = checkDateRowsOverlap(rows);
        }

        // Repeated-event extras.
        if (!error && type === 'everyday') {
            const $periods = findByName('mep_repeated_periods').first();
            const pv = ($periods.val() || '').toString().trim();
            $periods.removeClass('mpwem-field-error');
            if (pv === '' || isNaN(Number(pv)) || Number(pv) < 1) {
                $periods.addClass('mpwem-field-error');
                error = { message: 'After Repeated Days must be a number of at least 1.', $field: $periods };
            }

            // Off dates: each present row needs a date.
            if (!error) {
                const $offs = findByName('mep_ticket_off_dates[]');
                $offs.each(function(i) {
                    if (error) return;
                    const $h = $offs.eq(i);
                    if (($h.val() || '').toString().trim() === '') {
                        const $ui = dateVisibleField($h);
                        $ui.addClass('mpwem-field-error');
                        error = { message: 'Please set a date for each off date (or remove the row).', $field: $ui };
                    }
                });
            }

            // Display time slots (only when "Display Time" is enabled).
            if (!error && $root.find('input[name="mep_disable_ticket_time"]').first().is(':checked')) {
                ['global', 'sat', 'sun', 'mon', 'tue', 'wed', 'thu', 'fri'].forEach(function(day) {
                    if (error) return;
                    const $labels = findByName('mep_ticket_times_' + day + '_label[]');
                    const $times = findByName('mep_ticket_times_' + day + '_time[]');
                    $labels.each(function(i) {
                        if (error) return;
                        const lv = ($labels.eq(i).val() || '').toString().trim();
                        const tv = ($times.eq(i).val() || '').toString().trim();
                        if (lv === '' && tv === '') return;
                        if (lv === '') { $labels.eq(i).addClass('mpwem-field-error'); error = { message: 'Each time slot needs a label.', $field: $labels.eq(i) }; }
                        else if (tv === '') { $times.eq(i).addClass('mpwem-field-error'); error = { message: 'Each time slot needs a time.', $field: $times.eq(i) }; }
                    });
                });
            }

            // Special dates.
            if (!error) {
                const $spNames = findByName('mep_special_date_name[]');
                const $spStart = findByName('mep_special_start_date[]');
                const $spEnd = findByName('mep_special_end_date[]');
                $spNames.each(function(i) {
                    if (error) return;
                    const nm = ($spNames.eq(i).val() || '').toString().trim();
                    const $sH = $spStart.eq(i);
                    const $eH = $spEnd.eq(i);
                    const sv = ($sH.val() || '').toString().trim();
                    const ev = ($eH.val() || '').toString().trim();
                    if (nm === '' && sv === '' && ev === '') return;
                    const $sUI = dateVisibleField($sH);
                    const $eUI = dateVisibleField($eH);
                    if (nm === '') { $spNames.eq(i).addClass('mpwem-field-error'); error = { message: 'Each special date needs a label.', $field: $spNames.eq(i) }; return; }
                    if (sv === '') { $sUI.addClass('mpwem-field-error'); error = { message: 'Each special date needs a start date.', $field: $sUI }; return; }
                    if (ev === '') { $eUI.addClass('mpwem-field-error'); error = { message: 'Each special date needs an end date.', $field: $eUI }; return; }
                    if (new Date(ev).getTime() < new Date(sv).getTime()) { $eUI.addClass('mpwem-field-error'); error = { message: 'Special date end must be on or after the start date.', $field: $eUI }; }
                });
            }
        }

        // Date/Time format settings.
        if (!error && $root.find('input[name="mep_enable_custom_dt_format"]').first().is(':checked')) {
            const $df = $root.find('select[name="mep_event_date_format"]').first();
            const $tf = $root.find('select[name="mep_event_time_format"]').first();
            const $tz = $root.find('select[name="mep_time_zone_display"]').first();
            [$df, $tf, $tz].forEach(function($f) { $f.removeClass('mpwem-field-error'); });

            if (!selectHasRealValue($df)) {
                $df.addClass('mpwem-field-error'); error = { message: 'Please select a Date Format.', $field: $df };
            } else if ($df.val() === 'custom' && ($root.find('input[name="mep_event_custom_date_format"]').first().val() || '').toString().trim() === '') {
                const $c = $root.find('input[name="mep_event_custom_date_format"]').first(); $c.addClass('mpwem-field-error'); error = { message: 'Please enter the Custom Date Format.', $field: $c };
            } else if (!selectHasRealValue($tf)) {
                $tf.addClass('mpwem-field-error'); error = { message: 'Please select a Time Format.', $field: $tf };
            } else if ($tf.val() === 'custom' && ($root.find('input[name="mep_custom_event_time_format"]').first().val() || '').toString().trim() === '') {
                const $c = $root.find('input[name="mep_custom_event_time_format"]').first(); $c.addClass('mpwem-field-error'); error = { message: 'Please enter the Custom Time Format.', $field: $c };
            } else if (!selectHasRealValue($tz)) {
                $tz.addClass('mpwem-field-error'); error = { message: 'Please select Show Timezone.', $field: $tz };
            }
        }

        if (!error) {
            return true;
        }

        if (focus) {
            const $field = error.$field;
            setActiveStep($root, 'date', { pushHash: true, validate: false });
            window.setTimeout(function() {
                // Single Event / Particular Dates with nothing scheduled yet -
                // same fix as "Create First Date"/"+ Add Schedule" itself
                // (openInlineDateAdd already scrolls, reveals and focuses
                // the embedded form), not the old date modal popup.
                // Repeated Event's fields already render inline, so this
                // never applies there - the plain scroll/focus below
                // already reaches them directly.
                if (error.isMissingMainSchedule && (type === 'no' || type === 'yes')) {
                    openInlineDateAdd($root);
                    return;
                }

                if ($field && $field.length && !$field.is(':visible') && $field.closest('#mpwem_particular_date_modal_mount').length) {
                    openParticularDateModal($root, 'list');
                }
                window.setTimeout(function() {
                    if ($field && $field.length) {
                        try { $field[0].scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) {}
                        if ($field.is(':visible')) $field.trigger('focus');
                        flashAttention($field);
                    }
                }, 220);
            }, 120);
            showToast(error.message, 'error');
        }
        return false;
    }

    // Advanced (Display) step: FAQ, Timeline and Related Events requirements.
    function validateDisplayStep($root, options) {
        options = options || {};
        const focus = options.focus !== false;

        let error = null; // { message, $field, $flash }

        // Sync any TinyMCE editors back to their textareas so the FAQ/Timeline
        // descriptions can be read even while the visual editor is active.
        if (window.tinymce && typeof window.tinymce.triggerSave === 'function') {
            try { window.tinymce.triggerSave(); } catch (e) {}
        }

        // A wp_editor body counts as empty when it has no text once tags and
        // non-breaking spaces are stripped (an "empty" editor is often <p></p>).
        const isBlankHtml = function(value) {
            return (value || '').toString().replace(/<[^>]*>/g, '').replace(/&nbsp;/gi, '').replace(/\s+/g, '') === '';
        };

        const checkItemList = function(toggleName, containerId, itemSelector, titleName, descName, addId, label, noun) {
            const $toggle = $root.find('input[name="' + toggleName + '"]').first();
            if (!$toggle.length || !$toggle.is(':checked')) return null;

            // Require at least one item, and every non-blank item needs BOTH a
            // title and a description. Fully blank rows are dropped on save, so
            // they don't block on their own.
            const $items = $root.find(containerId + ' ' + itemSelector);
            let started = 0;
            let $errField = $();
            let $errFlash = $();
            let errMsg = '';

            $items.each(function() {
                const $item = $(this);
                const $t = $item.find('input[name="' + titleName + '"]').first();
                const $d = $item.find('[name="' + descName + '"]').first();
                $t.removeClass('mpwem-field-error');
                $d.removeClass('mpwem-field-error');

                const titleVal = ($t.val() || '').toString().trim();
                const descBlank = isBlankHtml($d.val());

                if (titleVal === '' && descBlank) {
                    return; // fully blank row — ignored
                }
                started++;
                if (!$errField.length) {
                    if (titleVal === '') {
                        $t.addClass('mpwem-field-error');
                        $errField = $t; $errFlash = $item;
                        errMsg = label + ' is enabled — each ' + noun + ' needs a title.';
                    } else if (descBlank) {
                        $d.addClass('mpwem-field-error');
                        $errField = $d; $errFlash = $item;
                        errMsg = label + ' is enabled — each ' + noun + ' needs a description.';
                    }
                }
            });

            if (started === 0) {
                const $firstTitle = $items.first().find('input[name="' + titleName + '"]').first();
                if ($firstTitle.length) {
                    $firstTitle.addClass('mpwem-field-error');
                    return { message: label + ' is enabled — add at least one ' + noun + ' with a title and description.', $field: $firstTitle, $flash: $firstTitle.closest(itemSelector) };
                }
                const $add = $root.find(addId).first();
                return { message: label + ' is enabled — add at least one ' + noun + ' with a title and description.', $field: $add, $flash: $add };
            }
            if ($errField.length) {
                return { message: errMsg, $field: $errField, $flash: $errFlash };
            }
            return null;
        };

        // FAQ.
        error = checkItemList('mep_faq_status', '#faq-items-container', '.faq-item', 'mep_faq_title[]', 'mep_faq_content[]', '#add-faq-item', 'FAQ', 'FAQ item');

        // Timeline.
        if (!error) {
            error = checkItemList('mep_timeline_status', '#timeline-items-container', '.timeline-item', 'mep_day_title[]', 'mep_day_content[]', '#add-timeline-item', 'Timeline', 'timeline item');
        }

        // Related Events — when enabled, need a section label and at least one event.
        if (!error) {
            const $relToggle = $root.find('input[name="mep_related_event_status"]').first();
            if ($relToggle.length && $relToggle.is(':checked')) {
                const $label = $root.find('input[name="related_section_label"]').first();
                $label.removeClass('mpwem-field-error');
                const labelVal = ($label.val() || '').toString().trim();

                let hasEvent = false;
                $root.find('input[name="event_list[]"]').each(function() {
                    if (($(this).val() || '').toString().trim() !== '') { hasEvent = true; }
                });

                if (labelVal === '') {
                    $label.addClass('mpwem-field-error');
                    error = { message: 'Related Events is enabled — add a section label.', $field: $label, $flash: $label };
                } else if (!hasEvent) {
                    const $search = $root.find('.mpwem-related-search-input').first();
                    $search.addClass('mpwem-field-error');
                    error = { message: 'Related Events is enabled — select at least one event.', $field: $search, $flash: $search };
                }
            }
        }

        if (!error) {
            return true;
        }

        if (focus) {
            const $field = error.$field;
            const $flash = error.$flash && error.$flash.length ? error.$flash : $field;
            setActiveStep($root, 'display', { pushHash: true, validate: false });
            window.setTimeout(function() {
                if ($flash && $flash.length) {
                    try { $flash[0].scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) {}
                }
                if ($field && $field.length && $field.is(':visible')) {
                    $field.trigger('focus');
                }
                flashAttention($flash);
            }, 160);
            showToast(error.message, 'error');
        }
        return false;
    }

    function submitEventForm($root, action, options) {
        options = options || {};
        const $form = $('#mpwem-event-edit-form');
        if (!$form.length) {
            return;
        }

        // WordPress does not guarantee that dynamically mounted wp_editor
        // instances copy their current Visual value back to the textarea when
        // the form is submitted through jQuery. Synchronize every editor before
        // validation and before both full and quiet saves.
        if (window.tinymce && typeof window.tinymce.triggerSave === 'function') {
            window.tinymce.triggerSave();
        }

        if ((action || '') !== 'trash') {
            // Required-field checks (title/venue/virtual details/date/display)
            // only apply when actually publishing, matching the server-side
            // validation in handle_save() — Draft/Update saves of an existing
            // event must not be blocked by fields the site may intentionally
            // leave empty (e.g. virtual event details when a separate
            // confirmation email is already handled elsewhere).
            const isPublishAttempt = (action || '') === 'publish';

            // When saving from a step-specific modal (ticket/extra-service/date),
            // only validate that step's own rules. Skipping cross-step validation
            // prevents date/display failures from switching the active tab.
            if (isPublishAttempt && !options.skipOtherSteps && !validateBasicStep($root, { focus: true })) {
                return;
            }
            if (!validateTicketsStep($root, { focus: true, skipPaymentCheck: options.skipPaymentCheck })) {
                return;
            }
            if (isPublishAttempt && !options.skipOtherSteps && !validateDateStep($root, { focus: true })) {
                return;
            }
            if (isPublishAttempt && !options.skipOtherSteps && !validateDisplayStep($root, { focus: true })) {
                return;
            }
        }

        $form.find('#mpwem_post_status_action').val(action || '');
        $form.find('#mpwem_active_step').val($root.find('.mpwem-step.is-active').data('step-key') || STEP_KEY_FALLBACK);
        // Step-scoped mini-saves (ticket/extra-service/date modal) shouldn't pop
        // the success notice — only an explicit Publish/Update/Save as Draft should.
        $form.find('#mpwem_quiet_save').val(options.skipOtherSteps ? '1' : '');
        $form.trigger('submit');
    }

    // Native submits of the wizard form (Publish / Update / Draft / mini-saves):
    // send the fields as one JSON value (see packFormEntries) and keep the
    // individual inputs out of the request so max_input_vars can't truncate it.
    $(document).on('submit.mpwemPack', '#mpwem-event-edit-form', function(e) {
        if (e.isDefaultPrevented()) return;
        const form = this;
        let $json = $(form).find('#mpwem_form_json');
        if (!$json.length) {
            $json = $('<input type="hidden" name="mpwem_form_json" id="mpwem_form_json" />').appendTo(form);
        }
        $json.val('');
        $json.val(packFormEntries(form));

        const muted = [];
        $(form).find('input, select, textarea, button').each(function() {
            if (this.name && this.name !== 'mpwem_form_json' && this.name !== 'action' && !this.disabled) {
                this.disabled = true;
                muted.push(this);
            }
        });
        // The request body is built synchronously at submit; restore right after.
        window.setTimeout(function() {
            muted.forEach(function(el) { el.disabled = false; });
        }, 0);
    });

    function closeStatusActionMenus() {
        $('.mpwem-status-actions__menu-wrap').removeClass('is-open')
            .find('.mpwem-status-actions__toggle')
            .attr('aria-expanded', 'false');
    }

    function enforceSingleAdvancedAccordion($section, itemSelector, $preferredItem) {
        if (!$section || !$section.length) {
            return;
        }

        const $items = $section.find(itemSelector);
        if (!$items.length) {
            return;
        }

        if ($preferredItem && $preferredItem.length && $preferredItem.hasClass('open')) {
            $items.not($preferredItem).removeClass('open');
            return;
        }

        const $openItems = $items.filter('.open');
        if ($openItems.length > 1) {
            $openItems.not($openItems.first()).removeClass('open');
        }
    }

    function initializeAdvancedAccordions($root) {
        const accordionConfigs = [
            {
                sectionSelector: '.mpwem-display-section--faq',
                itemSelector: '.faq-item',
                triggerSelector: '.edit-faq-item',
                addButtonSelector: '#add-faq-item'
            },
            {
                sectionSelector: '.mpwem-display-section--timeline',
                itemSelector: '.timeline-item',
                triggerSelector: '.edit-timeline-item',
                addButtonSelector: '#add-timeline-item'
            }
        ];

        accordionConfigs.forEach(function(config) {
            $root.find(config.sectionSelector).each(function() {
                enforceSingleAdvancedAccordion($(this), config.itemSelector);
            });

            $root.on('click.mpwemAccordion', config.sectionSelector + ' ' + config.triggerSelector, function() {
                const $section = $(this).closest(config.sectionSelector);
                const $item = $(this).closest(config.itemSelector);

                window.setTimeout(function() {
                    enforceSingleAdvancedAccordion($section, config.itemSelector, $item);
                }, 0);
            });

            $root.on('click.mpwemAccordion', config.sectionSelector + ' ' + config.addButtonSelector, function() {
                const $section = $(this).closest(config.sectionSelector);

                window.setTimeout(function() {
                    const $newItem = $section.find(config.itemSelector).last();
                    if (!$newItem.length) {
                        return;
                    }

                    $newItem.addClass('open');
                    enforceSingleAdvancedAccordion($section, config.itemSelector, $newItem);
                }, 0);
            });
        });
    }

    $(function () {
        const $root = getWizardRoot();
        if (!$root.length) return;

        const markReady = function() {
            $root.removeClass('is-loading').addClass('is-ready');
        };

        // Timeout is a safety-net: if the initialization throws and the finally
        // branch's requestAnimationFrame callback never runs, we still unblock the
        // UI after 2.5 seconds. Both fire if init succeeds — that is intentional
        // and harmless (idempotent class swap).
        window.setTimeout(markReady, 1000);
        bindCreateEvent($root);
        bindFeaturedImage($root);
        bindDangerZone($root);
        bindTrashConfirmation($root);

        // Speaker Information section: enable/disable toggle controls visibility
        // of the speaker selection. When off, the speaker section is hidden in
        // the editor and will not render on the front end.
        const syncSpeakerToggle = function() {
            const $toggle = $root.find('#mpwem_enable_speaker_toggle');
            if (!$toggle.length) return;
            const enabled = $toggle.is(':checked');
            $root.find('#mpwem_speaker_card_body').toggle(enabled);
            // Card-head toggle owns the saved value in the modern editor; strip
            // the nested panel control so we never submit two same-named fields.
            const $nested = $root.find('#mpwem_wizard_speaker_mount #mep_event_enable_speaker');
            if ($nested.length) {
                $nested.prop('checked', enabled).attr('value', enabled ? 'yes' : 'no').removeAttr('name');
            }
            $root.find('#mpwem_wizard_speaker_mount #mpwem-speaker-fields').toggle(enabled);
        };
        $root.on('change', '#mpwem_enable_speaker_toggle', syncSpeakerToggle);
        syncSpeakerToggle();
        initSpeakerPicker($root);

        // Description editor: keep visual padding inside the TinyMCE content area.
        (function padDescriptionEditor() {
            const applyPadding = function(editor) {
                if (!editor || !editor.getBody) {
                    return;
                }
                const body = editor.getBody();
                if (!body) {
                    return;
                }
                body.style.padding = '16px 18px';
                body.style.margin = '0';
                body.style.boxSizing = 'border-box';
            };

            if (window.tinymce) {
                const existing = window.tinymce.get('mpwem_wizard_content');
                if (existing) {
                    if (existing.initialized) {
                        applyPadding(existing);
                    } else {
                        existing.on('init', function() {
                            applyPadding(existing);
                        });
                    }
                }
                window.tinymce.on('AddEditor', function(event) {
                    if (!event || !event.editor || event.editor.id !== 'mpwem_wizard_content') {
                        return;
                    }
                    event.editor.on('init', function() {
                        applyPadding(event.editor);
                    });
                });
            }
        })();

        // Clear the required-field highlight as soon as the user fills the field.
        $root.on('input change', '.mpwem-field-error', function() {
            const value = ($(this).val() || '').toString().trim();
            if (value !== '') {
                $(this).removeClass('mpwem-field-error')
                    .siblings('.mpwem-field-error-msg[data-for="basic"]').remove();
            }
        });

        // Minimum-of-1 fields (e.g. "After Repeated Days"): never allow a value
        // below 1 — typing 0/negative snaps to 1; clearing it resets to 1 on blur.
        $root.on('input', '.mpwem-min-one', function() {
            const value = ($(this).val() || '').toString();
            if (value !== '' && Number(value) < 1) {
                $(this).val(1);
            }
        });
        $root.on('blur change', '.mpwem-min-one', function() {
            const value = ($(this).val() || '').toString().trim();
            if (value === '' || isNaN(Number(value)) || Number(value) < 1) {
                $(this).val(1);
            }
        });

        // Surface server-side validation errors after a blocked publish redirect.
        (function() {
            let errorCode = '';
            try {
                errorCode = new URLSearchParams(window.location.search).get('mpwem_error') || '';
            } catch (e) {
                errorCode = '';
            }
            if (!errorCode) return;

            const errorMessages = {
                title: 'Event title is required.',
                thumbnail: 'Featured image is required.',
                venue: 'Location/Venue is required for in-person events.',
                virtual: 'Virtual event details or link are required for online events.',
                tickets: 'Please add at least one ticket type.',
                ticket_fields: 'Each ticket type needs a name, price and capacity.',
                extra_service: 'Each extra service with a name needs a price and available quantity.',
                global_qty: 'Enter the Total Qty for the Full Event Base global quantity.',
                early_bird: 'Early bird is on — set the sale Start and End date/time for each ticket.',
                early_bird_order: 'The sale End date & time must be later than the Start date & time.',
                date_required: 'Set the event Start and End date & time.',
                date_order: 'The event End date & time must be later than the Start.',
                date_overlap: 'Two event dates overlap — each date must end before the next date starts.',
                repeat_periods: 'After Repeated Days must be a number of at least 1.',
                date_format: 'Complete the Date/Time format settings (Date Format, Time Format and Show Timezone).',
                faq_items: 'FAQ is enabled — each FAQ item needs a title and a description.',
                timeline_items: 'Timeline is enabled — each timeline item needs a title and a description.',
                related_label: 'Related Events is enabled — add a section label.',
                related_list: 'Related Events is enabled — select at least one event.'
            };
            let message = errorMessages[errorCode] || 'Please complete the required fields.';
            if (errorCode === 'date_required') {
                let missing = '';
                try {
                    missing = new URLSearchParams(window.location.search).get('mpwem_missing') || '';
                } catch (e) {
                    missing = '';
                }
                if (missing) {
                    message += ' (not received: ' + missing.split(',').join(', ') + ')';
                }
            }
            window.setTimeout(function() {
                showNotice($root, message, 'error');
                showToast(message, 'error');
            }, 400);
        })();

        // Show a success popup with a "Preview" button right after a
        // publish/update/save redirect (see $notice_key in handle_save()).
        // Prefer the server-rendered data-save-notice attribute so the modal
        // still appears if the query string is altered before JS runs.
        (function() {
            const successMessages = {
                published: ['Event published', 'Your event is live. Attendees can now view and register for it.'],
                drafted: ['Event switched to draft', 'Your event is saved as a draft and is not visible to the public yet.'],
                saved: ['Event saved', 'Your changes have been saved successfully.']
            };

            let noticeKey = ($root.attr('data-save-notice') || '').toString();
            if (!successMessages[noticeKey]) {
                noticeKey = '';
            }

            let params = null;
            try {
                params = new URLSearchParams(window.location.search);
            } catch (e) {
                params = null;
            }

            if (!noticeKey && params) {
                Object.keys(successMessages).some(function(key) {
                    const val = params.get(key);
                    if (val === '1' || val === 'true') {
                        noticeKey = key;
                        return true;
                    }
                    return false;
                });
            }
            if (!noticeKey) return;

            // Strip the notice param so refreshing the page doesn't re-show the popup.
            if (params) {
                params.delete(noticeKey);
                const cleanedSearch = params.toString();
                const cleanedUrl = window.location.pathname + (cleanedSearch ? '?' + cleanedSearch : '') + window.location.hash;
                if (window.history && window.history.replaceState) {
                    window.history.replaceState(null, '', cleanedUrl);
                }
            }
            $root.removeAttr('data-save-notice');

            const previewUrl = ($root.attr('data-frontend-url') || $root.data('frontendUrl') || '').toString();
            const pair = successMessages[noticeKey];
            const title = pair[0];
            const message = pair[1];
            window.setTimeout(function() {
                showSaveSuccessModal(title, message, previewUrl);
                showNotice($root, message, 'success');
            }, 500);
        })();

        try {
            mountAll($root);
            enhanceVenueGrid($root);
            enhanceEventType($root);
            enhanceRegistrationMode($root);
            enhanceSwitches($root);
            enhanceTooltips($root);
            enhanceSelects($root);
            enhanceTaxonomyCard($root);
            initializeAdvancedAccordions($root);

            const h = parseHash();
            setActiveStep($root, h.stepKey || STEP_KEY_FALLBACK, { pushHash: false });
            updateStepsVisibility($root);
        } catch (error) {
            if (window.console && window.console.error) {
                window.console.error('MPWEM event edit initialization failed.', error);
            }
        } finally {
            window.requestAnimationFrame(markReady);
        }

        $(document).on('change', '#mep_reg_status', function() {
            updateStepsVisibility($root);
        });

        $(document).on('click', '#mpwem_event_mode_toggle .mpwem-event-type-option', function() {
            if ($(this).hasClass('is-disabled')) {
                return;
            }
            const val = $(this).data('value');
            $('#mpwem_event_mode_toggle .mpwem-event-type-option').removeClass('is-active');
            $(this).addClass('is-active');
            $('#mep_reg_status').val(val).trigger('change');
        });

        $root.on('click', '.mpwem-step', function() {
            setActiveStep($root, $(this).data('step-key'), { pushHash: true, validate: true });
        });

        $root.on('click', '.mpwem-wizard-prev', function() {
            const $steps = $root.find('.mpwem-step').filter(function() { return $(this).css('display') !== 'none'; });
            const idx = $steps.index($steps.filter('.is-active'));
            if (idx > 0) setActiveStep($root, $steps.eq(idx - 1).data('step-key'), { pushHash: true });
        });

        $root.on('click', '.mpwem-wizard-next', function() {
            const $steps = $root.find('.mpwem-step').filter(function() { return $(this).css('display') !== 'none'; });
            const idx = $steps.index($steps.filter('.is-active'));
            if (idx < $steps.length - 1) {
                setActiveStep($root, $steps.eq(idx + 1).data('step-key'), { pushHash: true, validate: true });
            } else {
                if (!validateDateWiseGlobalQty($root)) {
                    return;
                }
                submitEventForm($root, finalSaveAction($root));
            }
        });

        // Topbar "Save as Draft" — keep status as draft and show the drafted notice.
        $root.on('click', '.mpwem-wizard-save-draft', function(e) {
            e.preventDefault();
            if (!validateDateWiseGlobalQty($root)) {
                return;
            }
            submitEventForm($root, 'draft');
        });

        $root.on('click', '.mpwem-status-actions__primary', function(e) {
            e.preventDefault();
            if (!validateDateWiseGlobalQty($root)) {
                return;
            }
            closeStatusActionMenus();
            submitEventForm($root, $(this).data('status-action') || '');
        });

        $root.on('click', '.mpwem-status-actions__toggle', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $menuWrap = $(this).closest('.mpwem-status-actions__menu-wrap');
            const willOpen = !$menuWrap.hasClass('is-open');

            closeStatusActionMenus();

            if (willOpen) {
                $menuWrap.addClass('is-open');
                $(this).attr('aria-expanded', 'true');
            }
        });

        $root.on('click', 'button.mpwem-status-actions__menu-item', function(e) {
            e.preventDefault();
            if (!validateDateWiseGlobalQty($root)) {
                return;
            }
            if (($(this).data('status-action') || '').toString() === 'trash') {
                return;
            }
            closeStatusActionMenus();
            submitEventForm($root, $(this).data('status-action') || '');
        });

        $root.on('click', '#mpwem_wizard_date_mount .mpwem_add_item, #mpwem_wizard_date_mount .ttbm_add_new_special_date', function() {
            window.setTimeout(function() {
                enhanceDateStep($root);
            }, 80);
        });

        // "Use the Attendee Form fields for RSVP" is disabled when the PRO
        // form-builder add-on isn't active - a disabled checkbox never
        // fires its own click/change, but its <label> still does, so bind
        // there and tell the user why nothing happened.
        $(document).on('click', 'label[for="mep_rsvp_use_attendee_form"]', function() {
            const $checkbox = $('#mep_rsvp_use_attendee_form');
            if ($checkbox.length && $checkbox.data('mpwemProLocked') == '1') {
                showToast('This is a PRO feature. Activate Event Booking Manager PRO to use it.', 'warning');
            }
        });

        $(document).on('click', '[data-mpwem-copy-shortcode]', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var text = $btn.closest('.mpwem-shortcode-help__row').find('.mpwem-shortcode-help__code').text().trim();
            if (!text) {
                return;
            }

            // Plain synchronous execCommand copy - no Clipboard API / Promise
            // involved, so there is nothing async that can silently fail.
            var $temp = $('<textarea readonly></textarea>').val(text).css({
                position: 'fixed',
                top: 0,
                left: 0,
                opacity: 0
            }).appendTo('body');
            $temp[0].focus();
            $temp[0].select();
            try {
                document.execCommand('copy');
            } catch (err) {
                // Nothing else to do - the text is at least selected for a manual copy.
            }
            $temp.remove();

            var checkIcon = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
            var $icon = $btn.find('svg').first();
            if (!$btn.data('mpwemCopyOriginalIcon')) {
                $btn.data('mpwemCopyOriginalIcon', $icon.prop('outerHTML'));
            }
            $icon.replaceWith(checkIcon);
            $btn.addClass('is-copied');
            window.clearTimeout($btn.data('mpwemCopyResetTimer'));
            var resetTimer = window.setTimeout(function() {
                $btn.removeClass('is-copied');
                $btn.find('svg').first().replaceWith($btn.data('mpwemCopyOriginalIcon'));
            }, 1500);
            $btn.data('mpwemCopyResetTimer', resetTimer);
        });

        // Add Premium Toaster Notification on form submit
        $('#mpwem-event-edit-form').on('submit', function(e) {
            if (!validateDateWiseGlobalQty($root, { focusStep: true })) {
                e.preventDefault();
                return false;
            }
            const hasMissingDateWiseGlobalQty = !!$root.data('mpwemMissingDateWiseGlobalQty');
            const statusAction = ($(this).find('#mpwem_post_status_action').val() || '').toString();
            let toastMessage = 'Saving changes... Please wait';
            let toastType = 'info';

            if (statusAction === 'publish') {
                toastMessage = 'Publishing event... Please wait';
            } else if (statusAction === 'draft') {
                toastMessage = 'Switching event to draft... Please wait';
            } else if (statusAction === 'trash') {
                toastMessage = 'Moving event to trash... Please wait';
            }

            if (hasMissingDateWiseGlobalQty) {
                toastMessage = 'Saving changes. Global Qty is still missing in Particular Date & Time Settings.';
                toastType = 'warning';
            }

            showToast(toastMessage, toastType);
        });

        $(window).on('hashchange', function() {
            const h = parseHash();
            setActiveStep($root, h.stepKey || STEP_KEY_FALLBACK, { pushHash: false });
        });

        $(document).on('click', function() {
            $('.mpwem-select-wrapper').removeClass('is-open');
            closeStatusActionMenus();
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                closeStatusActionMenus();
            }
        });
    });

    // Classic event editor (post.php?...&mpwem_classic=1): the Venue/Location
    // tab renders the same "Manual Entry" markup and hidden event-type input
    // as the modern wizard, but there is no .mpwem-event-wizard wrapper here,
    // so the bootstrap above never runs. Wire up just those two enhancements
    // against the legacy .mp_event_all_meta_in_tab container instead.
    $(function () {
        if (getWizardRoot().length) return;

        const $classicRoot = $('.mp_event_all_meta_in_tab').first();
        if (!$classicRoot.length) return;

        try {
            enhanceVenueGrid($classicRoot);
            enhanceEventType($classicRoot, { skipTicketSync: true });
            initSpeakerPicker($classicRoot);
        } catch (error) {
            if (window.console && window.console.error) {
                window.console.error('MPWEM classic venue/event-type enhancement failed.', error);
            }
        }
    });

})(jQuery);
// step counting and navigation
jQuery(document).ready(function ($) {

	var $steps = $('.mpwem-step');

	// Add step numbers
	$steps.each(function (index) {
		$(this).attr('data-step', index + 1);
	});

	// Active / Complete state
	$steps.on('click', function () {

		var currentIndex = $(this).index();

		$steps.removeClass('is-active is-complete');

		$steps.each(function (index) {

			if (index < currentIndex) {
				$(this).addClass('is-complete');
			}

			if (index === currentIndex) {
				$(this).addClass('is-active');
			}
		});
	});

	// ========== Related Events Search ==========

	var relatedSearchTimer;
	var $relatedWrap = $('.mpwem-related-search-wrap');

	function getSelectedRelatedIds($wrap) {
		var ids = [];
		$wrap.find('.mpwem-related-pill').each(function () {
			ids.push(parseInt($(this).data('id')));
		});
		return ids;
	}

	function updateRelatedHiddenInputs($wrap, ids) {
		$wrap.find('.mpwem-related-ids').remove();
		if (ids.length === 0) {
			$wrap.append('<input type="hidden" name="event_list[]" class="mpwem-related-ids" value="">');
			return;
		}
		$.each(ids, function (i, id) {
			$wrap.append('<input type="hidden" name="event_list[]" class="mpwem-related-ids" value="' + id + '">');
		});
	}

	function renderRelatedPills($wrap, events) {
		var $pills = $wrap.find('.mpwem-related-pills');
		$pills.empty();
		if (!events || !events.length) {
			$pills.append('<span class="mpwem-related-pills-empty">' + $pills.data('empty-msg') + '</span>');
			return;
		}
		$.each(events, function (i, ev) {
			$pills.append(
				'<span class="mpwem-related-pill" data-id="' + ev.id + '">' +
					'<span class="mpwem-related-pill__title">' + $('<span>').text(ev.title).html() + '</span>' +
					'<button type="button" class="mpwem-related-pill__remove" title="Remove" aria-label="Remove">&times;</button>' +
				'</span>'
			);
		});
	}

	function renderRelatedResults($wrap, results, selectedIds, searchTerm) {
		var $results = $wrap.find('.mpwem-related-results');
		var $empty = $wrap.find('.mpwem-related-empty-state');
		$results.empty().hide();
		$empty.hide();

		if (!results || !results.length) {
			if (searchTerm && searchTerm.length > 0) {
				$empty.show();
			}
			return;
		}

		var $list = $('<div class="mpwem-related-results-list"></div>');
		$.each(results, function (i, ev) {
			var isSelected = $.inArray(ev.id, selectedIds) !== -1;
			$list.append(
				'<div class="mpwem-related-result-item' + (isSelected ? ' is-selected' : '') + '" data-id="' + ev.id + '">' +
					'<div class="mpwem-related-result-item__info">' +
						'<span class="mpwem-related-result-item__title">' + $('<span>').text(ev.title).html() + '</span>' +
						(ev.date ? '<span class="mpwem-related-result-item__date">' + $('<span>').text(ev.date).html() + '</span>' : '') +
					'</div>' +
					'<span class="mpwem-related-result-item__icon dashicons ' + (isSelected ? 'dashicons-yes' : 'dashicons-plus') + '"></span>' +
				'</div>'
			);
		});
		$results.append($list).show();
	}

	function doRelatedSearch($input) {
		var $wrap = $input.closest('.mpwem-related-search-wrap');
		var term = $input.val().trim();

		$wrap.find('.mpwem-related-search-spinner').addClass('is-active');

		$.post(mpwemEventEdit.ajax_url, {
			action: 'mpwem_search_related_events',
			nonce: mpwemEventEdit.admin_nonce,
			search: term,
			exclude: getSelectedRelatedIds($wrap),
			post_id: $('#post_ID').val() || 0
		}).done(function (res) {
			if (res.success) {
				renderRelatedResults($wrap, res.data, getSelectedRelatedIds($wrap), term);
			}
		}).always(function () {
			$wrap.find('.mpwem-related-search-spinner').removeClass('is-active');
		});
	}

	function loadDefaultRelatedEvents($wrap) {
		doRelatedSearch($wrap.find('.mpwem-related-search-input'));
	}

	$relatedWrap.each(function () {
		var $wrap = $(this);
		var preSelected = [];
		$wrap.find('.mpwem-related-pill').each(function () {
			preSelected.push({
				id: parseInt($(this).data('id')),
				title: $(this).find('.mpwem-related-pill__title').text()
			});
		});
		if (!preSelected.length) {
			$wrap.find('.mpwem-related-pills').append(
				'<span class="mpwem-related-pills-empty">' + $wrap.find('.mpwem-related-pills').data('empty-msg') + '</span>'
			);
		}
		loadDefaultRelatedEvents($wrap);
	});

	$relatedWrap.on('input', '.mpwem-related-search-input', function () {
		var $input = $(this);
		clearTimeout(relatedSearchTimer);
		relatedSearchTimer = setTimeout(function () {
			doRelatedSearch($input);
		}, 300);
	});

	$relatedWrap.on('keydown', '.mpwem-related-search-input', function (e) {
		if (e.key === 'Escape') {
			$(this).val('').trigger('input');
		}
	});

	$relatedWrap.on('click', '.mpwem-related-result-item:not(.is-selected)', function () {
		var $item = $(this);
		var $wrap = $item.closest('.mpwem-related-search-wrap');
		var id = parseInt($item.data('id'));
		var title = $item.find('.mpwem-related-result-item__title').text();
		var date = $item.find('.mpwem-related-result-item__date').text();

		var currentIds = getSelectedRelatedIds($wrap);
		if ($.inArray(id, currentIds) !== -1) return;

		var pills = [];
		$wrap.find('.mpwem-related-pill').each(function () {
			pills.push({ id: parseInt($(this).data('id')), title: $(this).find('.mpwem-related-pill__title').text() });
		});
		pills.push({ id: id, title: title });

		renderRelatedPills($wrap, pills);
		updateRelatedHiddenInputs($wrap, $.map(pills, function (p) { return p.id; }));

		$item.addClass('is-selected');
	});

	$relatedWrap.on('click', '.mpwem-related-pill__remove', function () {
		var $btn = $(this);
		var $pill = $btn.closest('.mpwem-related-pill');
		var $wrap = $pill.closest('.mpwem-related-search-wrap');
		var id = parseInt($pill.data('id'));

		var pills = [];
		$wrap.find('.mpwem-related-pill').each(function () {
			var pid = parseInt($(this).data('id'));
			if (pid !== id) {
				pills.push({ id: pid, title: $(this).find('.mpwem-related-pill__title').text() });
			}
		});

		renderRelatedPills($wrap, pills);
		updateRelatedHiddenInputs($wrap, $.map(pills, function (p) { return p.id; }));

		$wrap.find('.mpwem-related-result-item[data-id="' + id + '"]').removeClass('is-selected');

		doRelatedSearch($wrap.find('.mpwem-related-search-input'));
	});

	$relatedWrap.on('focus', '.mpwem-related-search-input', function () {
		$(this).closest('.mpwem-related-search-wrap').find('.mpwem-related-results').show();
	});

});
