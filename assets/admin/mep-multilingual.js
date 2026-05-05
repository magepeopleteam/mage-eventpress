/**
 * Multilingual Admin JavaScript for MageEventPress
 */
(function($) {
    'use strict';

    const MPWEM_Multilingual = {
        init: function() {
            this.initTabs();
            this.bindEvents();
        },

        initTabs: function() {
            // Handle tab switching if any
            $('.mep-ml-tabs').on('click', '.mep-ml-tab', function(e) {
                e.preventDefault();
                const target = $(this).data('tab');

                $('.mep-ml-tab').removeClass('active');
                $(this).addClass('active');

                $('.mep-ml-tab-content').hide();
                $('#' + target).show();
            });
        },

        bindEvents: function() {
            // Create translation button
            $(document).on('click', '.mep-create-translation', this.handleCreateTranslation.bind(this));

            // Sync products button
            $(document).on('click', '.mep-sync-products', this.handleSyncProducts.bind(this));

            // Translation link in row actions
            $(document).on('click', '.mep-translate-link', this.handleTranslateLink.bind(this));

            // Close modal
            $(document).on('click', '.mep-translation-modal-close, .mep-translation-modal', this.handleCloseModal.bind(this));

            // Bulk sync all products from settings page
            this.handleBulkSyncAllProducts();
        },

        handleCreateTranslation: function(e) {
            e.preventDefault();
            const $btn = $(e.currentTarget);
            const eventId = $btn.data('event');
            const lang = $btn.data('lang');

            if (!eventId || !lang) {
                this.showToast('Invalid parameters', 'error');
                return;
            }

            $btn.prop('disabled', true).text('Creating...');

            $.ajax({
                url: mep_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'mep_create_event_translation',
                    event_id: eventId,
                    lang: lang,
                    nonce: mep_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        this.showToast('Translation created successfully', 'success');
                        // Reload the page to show the new translation
                        if (response.data.edit_url) {
                            window.location.href = response.data.edit_url;
                        } else {
                            location.reload();
                        }
                    } else {
                        this.showToast(response.data.message || 'Failed to create translation', 'error');
                        $btn.prop('disabled', false).text('Create');
                    }
                }.bind(this),
                error: function() {
                    this.showToast('An error occurred', 'error');
                    $btn.prop('disabled', false).text('Create');
                }.bind(this)
            });
        },

        handleSyncProducts: function(e) {
            e.preventDefault();
            const $btn = $(e.currentTarget);
            const eventId = $btn.data('event');

            if (!eventId) {
                this.showToast('Invalid event', 'error');
                return;
            }

            $btn.prop('disabled', true).text('Syncing...');

            $.ajax({
                url: mep_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'mep_sync_all_product_translations',
                    event_id: eventId,
                    nonce: mep_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        this.showToast('Products synced successfully', 'success');
                        location.reload();
                    } else {
                        this.showToast(response.data.message || 'Sync failed', 'error');
                        $btn.prop('disabled', false).text('Sync Now');
                    }
                }.bind(this),
                error: function() {
                    this.showToast('An error occurred', 'error');
                    $btn.prop('disabled', false).text('Sync Now');
                }.bind(this)
            });
        },

        /**
         * Handle bulk sync all products from settings page
         */
        handleBulkSyncAllProducts: function() {
            const $btn = $('#mep_bulk_sync_all_products');
            const $status = $('#mep_sync_status');

            if (!$btn.length) return;

            $btn.on('click', function(e) {
                e.preventDefault();

                $btn.prop('disabled', true).text('Syncing...');
                $status.show().text('Please wait...');

                $.ajax({
                    url: mep_multilingual.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'mep_bulk_sync_all_products',
                        nonce: mep_multilingual.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $btn.text('Sync Complete');
                            $status.html('<span style="color: #00a32a;">' + response.data.message + '</span>');
                            this.showToast(response.data.message, 'success');
                        } else {
                            $btn.prop('disabled', false).text('Sync All Products');
                            $status.html('<span style="color: #d63638;">' + (response.data.message || 'Sync failed') + '</span>');
                            this.showToast(response.data.message || 'Sync failed', 'error');
                        }
                    }.bind(this),
                    error: function() {
                        $btn.prop('disabled', false).text('Sync All Products');
                        $status.html('<span style="color: #d63638;">An error occurred</span>');
                        this.showToast('An error occurred', 'error');
                    }.bind(this)
                });
            }.bind(this));
        },

        handleTranslateLink: function(e) {
            e.preventDefault();
            const $link = $(e.currentTarget);
            const eventId = $link.data('event-id');

            if (!eventId) {
                return;
            }

            // Fetch translations via AJAX and show modal
            $.ajax({
                url: mep_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'mep_get_translations',
                    event_id: eventId,
                    nonce: mep_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        this.showTranslationModal(eventId, response.data);
                    } else {
                        this.showToast('Failed to load translations', 'error');
                    }
                }.bind(this),
                error: function() {
                    this.showToast('An error occurred', 'error');
                }.bind(this)
            });
        },

        showTranslationModal: function(eventId, data) {
            // Remove existing modal
            $('.mep-translation-modal').remove();

            const availableLangs = data.available_langs || [];
            const translations = data.translations || {};
            const missing = data.missing || [];

            let languagesHtml = '';
            availableLangs.forEach(function(lang) {
                const isTranslated = translations.hasOwnProperty(lang);
                const statusClass = isTranslated ? 'translated' : 'missing';
                const statusText = isTranslated ? '✓ Translated' : '✗ Missing';
                const actionText = isTranslated ? 'Edit' : 'Create';

                languagesHtml += `
                    <div class="mep-language-item ${statusClass}">
                        <div class="lang-code">${lang.toUpperCase()}</div>
                        <div class="lang-name">${this.getLanguageName(lang)}</div>
                        <div class="status-icon">${statusText}</div>
                        <div class="action">
                            ${isTranslated ?
                                '<a href="#" class="button button-small mep-edit-translation" data-event="' + (translations[lang] || '') + '">' + actionText + '</a>' :
                                '<button type="button" class="button button-small mep-create-translation" data-event="' + eventId + '" data-lang="' + lang + '">' + actionText + '</button>'
                            }
                        </div>
                    </div>
                `;
            }.bind(this));

            const modalHtml = `
                <div class="mep-translation-modal" id="mep-translation-modal">
                    <div class="mep-translation-modal-content">
                        <div class="mep-translation-modal-header">
                            <h2>${mep_js_strings.translations || 'Manage Translations'}</h2>
                            <span class="mep-translation-modal-close">&times;</span>
                        </div>
                        <div class="mep-ml-status-panel">
                            <div class="mep-language-grid">
                                ${languagesHtml}
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(modalHtml);
            $('#mep-translation-modal').fadeIn(300);
        },

        handleCloseModal: function(e) {
            if (e.target === e.currentTarget || $(e.target).hasClass('mep-translation-modal-close')) {
                $('#mep-translation-modal').fadeOut(300, function() {
                    $(this).remove();
                });
            }
        },

        getLanguageName: function(code) {
            const names = {
                'en': 'English',
                'es': 'Spanish',
                'fr': 'French',
                'de': 'German',
                'it': 'Italian',
                'nl': 'Dutch',
                'pt': 'Portuguese',
                'ru': 'Russian',
                'zh': 'Chinese',
                'ja': 'Japanese',
                'ar': 'Arabic',
                'hi': 'Hindi',
                'bn': 'Bengali',
                'pa': 'Punjabi',
                'vi': 'Vietnamese',
                'cs': 'Czech',
                'pl': 'Polish',
                'sv': 'Swedish',
                'da': 'Danish',
                'fi': 'Finnish',
                'no': 'Norwegian',
                'tr': 'Turkish',
                'ko': 'Korean',
                'th': 'Thai',
                'id': 'Indonesian',
                'ms': 'Malay',
                'el': 'Greek',
                'he': 'Hebrew',
                'uk': 'Ukrainian',
                'ro': 'Romanian',
                'hu': 'Hungarian',
                'sk': 'Slovak',
                'bg': 'Bulgarian',
                'hr': 'Croatian',
                'sl': 'Slovenian',
                'et': 'Estonian',
                'lv': 'Latvian',
                'lt': 'Lithuanian',
                'sr': 'Serbian',
                'ca': 'Catalan',
                'gl': 'Galician',
                'eu': 'Basque'
            };

            return names[code] || code.toUpperCase();
        },

        showToast: function(message, type) {
            // Remove existing toasts
            $('.mep-toast').remove();

            const toastHtml = '<div class="mep-toast ' + (type || 'info') + '">' + message + '</div>';
            $('body').append(toastHtml);

            // Auto remove after 4 seconds
            setTimeout(function() {
                $('.mep-toast').fadeOut(300, function() {
                    $(this).remove();
                });
            }, 4000);
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        MPWEM_Multilingual.init();
    });

    // Expose to global scope
    window.MPWEM_Multilingual = MPWEM_Multilingual;

})(jQuery);