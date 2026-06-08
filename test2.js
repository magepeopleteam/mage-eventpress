					jQuery(document).ready(function($) {
						if ($('.payment-sub-tabs').length > 0) {
							// WooCommerce Setting Toggle Logic
							function toggleWcSettings() {
								var isChecked = $('#wpuf-payment_setting_sec\\[mep_enable_wc_payment\\]').is(':checked');
								var $wcFields = $('tr.woocommerce-field').not('tr.woocommerce-main-toggle');
								if (isChecked) {
									$wcFields.fadeIn(200);
								} else {
									$wcFields.hide();
								}
							}
							
							$('#wpuf-payment_setting_sec\\[mep_enable_wc_payment\\]').on('change', toggleWcSettings);
							function updateTabs() {
								var activeTabId = $(".payment-sub-tabs .nav-tab-active").attr("href").replace("#", "");
								$("tr.woocommerce-field, div.woocommerce-field, tr.no-woocommerce-field").hide();
								
								// Special handling: if we have a div.woocommerce-field (the warning), show it
								if (activeTabId === 'woocommerce-field') {
									$("div.woocommerce-field").show();
									$("div.mep-wc-warning-alert").show();
									$("tr.woocommerce-field").show();
									toggleWcSettings();
								} else {
									$("tr." + activeTabId).show();
								}
							}
							
							// Gateway Cards Configure Toggle
							$(".gateway-configure-btn").click(function(e) {
								e.preventDefault();
								var $body = $(this).closest(".gateway-card").find(".gateway-body");
								$body.slideToggle(300);
								$(this).text($body.is(":visible") ? "Close Settings" : "Configure");
							});
							
							$(".payment-sub-tabs .nav-tab").click(function(e) {
								e.preventDefault();
								$(".payment-sub-tabs .nav-tab").removeClass("nav-tab-active");
								$(this).addClass("nav-tab-active");
								updateTabs();
							});
							updateTabs();
							


							// Modal logic
							$('.mep-install-wc-trigger').click(function(e) {
								e.preventDefault();
								$('#mep-wc-install-modal').css('display', 'flex').hide().fadeIn(200);
							});

							$('.mep-close-modal').click(function() {
								$('#mep-wc-install-modal').fadeOut(200);
							});

							$('#mep-wc-start-install').click(function() {
								var $btn = $(this);
								var $progress = $('#mep-wc-install-progress');
								var $status = $('#mep-wc-install-status');
								
								$btn.prop('disabled', true);
								$btn.css('opacity', '0.6');
								$progress.fadeIn(200);
								$status.text( "test" );
								
								$.ajax({
									url: ajaxurl,
									type: "POST",
									data: {
										action: "mep_install_activate_wc",
										nonce: "nonce"
									},
									success: function(response) {
										if (response.success) {
											$status.css('color', '#0f5132');
											$status.text( "test" );
											setTimeout(function() {
												location.reload();
											}, 1500);
										} else {
											$status.css('color', '#dc3545');
											$status.text( "test" + (response.data || "Unknown error") );
											$btn.prop("disabled", false);
											$btn.css('opacity', '1');
										}
									},
									error: function() {
										$status.css('color', '#dc3545');
										$status.text( "test" );
										$btn.prop("disabled", false);
										$btn.css('opacity', '1');
									}
								});
							});
							// Move the wrapper out of the table so it displays like a real tab bar spanning full width
							var $tabContainer = $('.payment-sub-tabs-wrapper');
							var $table = $tabContainer.closest('table.form-table');
							$tabContainer.insertBefore($table);
							
							// Move warning full width
							var $warning = $('.mep-wc-warning-alert');
							if ($warning.length > 0) {
								$warning.insertBefore($table);
							}
							// The tab container was originally inside a tr. We should hide that tr to prevent an empty row.
							// But since we already moved $tabContainer, we need to hide the tr that has an empty th and a td containing just a p.description
							$table.find('tr').each(function() {
								if ($(this).find('.payment-sub-tabs-wrapper').length === 0 && $(this).text().trim() === '') {
									$(this).hide();
								}
							});
							// Add styles for text color
							$('.payment-sub-tabs .nav-tab').css('color', 'black');
						}
					});
                (function ($) {
                    'use strict';
                    jQuery('.import_template').on('click', function () {
                        if (confirm('Are You Sure to Import this Template ? \n\n 1. Ok : To Import . \n 2. Cancel : To Cancel .')) {
                            let file = jQuery(this).data('file');
                            let type = jQuery(this).data('type');
                            let editor = jQuery(this).data('editor');
                            let name = jQuery(this).data('name');
                            jQuery.ajax({
                                type: 'POST',
                                url: mpwem_ajax_url,
                                data: {
                                    "action": "mep_import_ajax_template",
                                    "nonce": '<?php echo wp_create_nonce( 'mep-ajax-import-template-nonce' ); ?>',
                                    "file": file,
                                    "editor": editor,
                                    "name": name,
                                    "type": type
                                },
                                beforeSend: function () {
                                    jQuery('.mep_licensae_info').html('<h5 class="mep-msg mep-msg-process">Please wait.. Importing Template..</h5>');
                                },
                                success: function (data) {
                                    jQuery('.mep_licensae_info').html(data);
                                    window.location.reload();
                                }
                            });
                        } else {
                            return false;
                        }
                        return false;
                    });
                })(jQuery);
