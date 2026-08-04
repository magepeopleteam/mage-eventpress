/**
 * Modern reskin for secondary Event CPT list screens (edit.php).
 * Speakers / Waitlist Email / Reg Form / Reviews.
 * Add & Edit navigate to post-new.php / post.php (rich editors stay intact).
 * Speakers Management also uses AJAX pagination (10 per page).
 */
(function () {
	'use strict';

	var cfg = window.mpwemPostListModern || {};
	var strings = cfg.strings || {};
	var currentPage = 1;
	var loading = false;

	function el(tag, attrs, html) {
		var node = document.createElement(tag);
		attrs = attrs || {};
		Object.keys(attrs).forEach(function (key) {
			node.setAttribute(key, attrs[key]);
		});
		if (html !== undefined) {
			node.innerHTML = html;
		}
		return node;
	}

	function swapHeading(wrap) {
		var heading = wrap.querySelector('h1.wp-heading-inline');
		if (!heading || !cfg.heading) {
			return;
		}
		heading.textContent = cfg.heading;

		if (cfg.subheading && !wrap.querySelector('.mpwem-post-list-subtitle')) {
			var subtitle = el('p', { class: 'mpwem-post-list-subtitle' });
			subtitle.textContent = cfg.subheading;
			heading.insertAdjacentElement('afterend', subtitle);
		}
	}

	function alignSearchWithFilters(wrap) {
		if (wrap.querySelector('.mpwem-post-list-toolbar')) {
			return;
		}

		var card = wrap.querySelector('.mpwem-post-list-card');
		var sub = wrap.querySelector('ul.subsubsub');
		var search = wrap.querySelector('#posts-filter > p.search-box, form#posts-filter p.search-box');
		if (!card || !sub || !search) {
			return;
		}

		var toolbar = el('div', { class: 'mpwem-post-list-toolbar' });
		card.insertBefore(toolbar, card.firstChild);
		toolbar.appendChild(sub);

		// Keep search bound to #posts-filter after moving beside the filters.
		search.querySelectorAll('input, select, textarea, button').forEach(function (field) {
			field.setAttribute('form', 'posts-filter');
		});
		toolbar.appendChild(search);
	}

	function buildLayout(wrap) {
		if (wrap.querySelector('.mpwem-post-list-layout')) {
			return;
		}

		var layout = el('div', { class: 'mpwem-post-list-layout' });
		var main = el('div', { class: 'mpwem-post-list-main' });
		var card = el('div', { class: 'mpwem-post-list-card' });
		var aside = el('div', { class: 'mpwem-post-list-aside' });

		var moveSelectors = ['ul.subsubsub', 'form#posts-filter'];
		moveSelectors.forEach(function (selector) {
			var node = wrap.querySelector(selector);
			if (node && !card.contains(node)) {
				card.appendChild(node);
			}
		});

		main.appendChild(card);
		layout.appendChild(main);
		layout.appendChild(aside);

		var anchor = wrap.querySelector('.mpwem-post-list-subtitle') || wrap.querySelector('h1.wp-heading-inline');
		if (anchor && anchor.nextSibling) {
			wrap.insertBefore(layout, anchor.nextSibling);
		} else {
			wrap.appendChild(layout);
		}

		aside.appendChild(buildPromoPanel());
		alignSearchWithFilters(wrap);
	}

	function buildPromoPanel() {
		var features = (cfg.proFeatures || []).map(function (feature) {
			return '<li>' + feature + '</li>';
		}).join('');

		var panel = el('div', { class: 'mpwem-promo-panel' });
		panel.innerHTML =
			'<span class="mpwem-promo-eyebrow">' + (strings.promoEyebrow || '') + '</span>' +
			'<h2>' + (strings.promoTitle || '') + '</h2>' +
			'<p class="mpwem-promo-body">' + (strings.promoBody || '') + '</p>' +
			'<ul class="mpwem-promo-features">' + features + '</ul>' +
			'<a class="button button-primary mpwem-promo-cta" href="' + (cfg.proUrl || '#') + '">' + (strings.promoCta || '') + '</a>';
		return panel;
	}

	function dockAddButton(wrap) {
		var tablenavTop = wrap.querySelector('#posts-filter .tablenav.top');
		if (!tablenavTop || wrap.querySelector('.mpwem-add-post-btn')) {
			return;
		}

		var button = el('a', {
			class: 'button button-primary mpwem-add-post-btn',
			href: cfg.addNewUrl || '#'
		});
		button.textContent = cfg.addButtonLabel || 'Add New';

		if (cfg.ajaxCreate) {
			button.setAttribute('href', '#');
			button.setAttribute('role', 'button');
			button.addEventListener('click', function (event) {
				event.preventDefault();
				openSpeakerModal();
			});
		}

		var right = el('div', { class: 'mpwem-tablenav-right' });
		var pages = tablenavTop.querySelector('.tablenav-pages');
		if (pages && !cfg.ajaxPagination) {
			right.appendChild(pages);
		}
		right.appendChild(button);
		tablenavTop.appendChild(right);
	}

	function removeTableFooter(wrap) {
		var tfoot = wrap.querySelector('#posts-filter table.wp-list-table tfoot');
		if (tfoot) {
			tfoot.remove();
		}
	}

	function clearActionsColumn(wrap) {
		wrap.querySelectorAll('.column-mpwem-actions').forEach(function (node) {
			node.remove();
		});
	}

	function buildActionsColumn(wrap) {
		var table = wrap.querySelector('#posts-filter table.wp-list-table');
		if (!table || table.querySelector('.column-mpwem-actions')) {
			return;
		}

		var label = strings.actionsColumn || 'Actions';

		table.querySelectorAll('thead tr').forEach(function (headerRow) {
			var th = el('th', { scope: 'col', class: 'manage-column column-mpwem-actions' });
			th.textContent = label;
			headerRow.appendChild(th);
		});

		table.querySelectorAll('tbody tr').forEach(function (row) {
			if (row.classList.contains('no-items')) {
				var emptyTd = el('td', { class: 'column-mpwem-actions' });
				row.appendChild(emptyTd);
				return;
			}

			var td = el('td', { class: 'column-mpwem-actions', 'data-colname': label });
			var rowActions = row.querySelector('.row-actions');
			var speakerId = row.getAttribute('data-speaker-id') || (row.id || '').replace(/^post-/, '');
			var speakerName = '';
			var titleNode = row.querySelector('.row-title');
			if (titleNode) {
				speakerName = titleNode.textContent || '';
			}

			if (cfg.ajaxCreate && speakerId) {
				var forceDelete = !!(rowActions && rowActions.querySelector('[data-speaker-force="1"]'));
				var canEdit = !!(rowActions && rowActions.querySelector('[data-speaker-edit], .edit a'));
				var canDelete = !!(rowActions && rowActions.querySelector('[data-speaker-delete], .trash a, .delete a'));

				var viewBtn = el('button', {
					type: 'button',
					class: 'mpwem-row-action-icon mpwem-row-action-view',
					title: strings.view || 'View',
					'data-speaker-view': speakerId
				});
				viewBtn.innerHTML = '<span class="dashicons dashicons-visibility" aria-hidden="true"></span>';
				td.appendChild(viewBtn);

				if (canEdit) {
					var editBtn = el('button', {
						type: 'button',
						class: 'mpwem-row-action-icon mpwem-row-action-edit',
						title: strings.edit || 'Edit',
						'data-speaker-edit': speakerId
					});
					editBtn.innerHTML = '<span class="dashicons dashicons-edit" aria-hidden="true"></span>';
					td.appendChild(editBtn);
				}
				if (canDelete) {
					var deleteBtn = el('button', {
						type: 'button',
						class: 'mpwem-row-action-icon mpwem-row-action-delete',
						title: strings.delete || 'Delete',
						'data-speaker-delete': speakerId,
						'data-speaker-name': speakerName,
						'data-speaker-force': forceDelete ? '1' : '0'
					});
					deleteBtn.innerHTML = '<span class="dashicons dashicons-trash" aria-hidden="true"></span>';
					td.appendChild(deleteBtn);
				}
			} else if (rowActions) {
				var postId = (row.id || '').replace(/^post-/, '');
				var editLink = rowActions.querySelector('.edit a, .inline.hide-if-no-js + .edit a');
				if (!editLink) {
					editLink = rowActions.querySelector('span.edit a');
				}
				var trashLink = rowActions.querySelector('.trash a, .delete a');

				if (cfg.emailPreview && postId) {
					var previewBtn = el('button', {
						type: 'button',
						class: 'mpwem-row-action-icon mpwem-row-action-preview',
						title: strings.preview || 'Preview',
						'data-email-preview': postId
					});
					previewBtn.innerHTML = '<span class="dashicons dashicons-visibility" aria-hidden="true"></span>';
					td.appendChild(previewBtn);
				}

				if (editLink) {
					var editIcon = editLink.cloneNode(true);
					editIcon.className = 'mpwem-row-action-icon mpwem-row-action-edit';
					editIcon.innerHTML = '<span class="dashicons dashicons-edit" aria-hidden="true"></span>';
					td.appendChild(editIcon);
				}
				if (trashLink) {
					var trashIcon = trashLink.cloneNode(true);
					trashIcon.className = 'mpwem-row-action-icon mpwem-row-action-delete';
					trashIcon.innerHTML = '<span class="dashicons dashicons-trash" aria-hidden="true"></span>';
					td.appendChild(trashIcon);
				}
			}

			row.appendChild(td);
		});
	}

	function getSpeakerStatus() {
		var params = new URLSearchParams(window.location.search);
		return params.get('post_status') || 'all';
	}

	function getSpeakerSearch() {
		var input = document.querySelector('#posts-filter input[name="s"], #post-search-input');
		return input ? (input.value || '') : '';
	}

	function ensureSpeakerPaginationMount(wrap) {
		var card = wrap.querySelector('.mpwem-post-list-card');
		if (!card) {
			return null;
		}
		var mount = card.querySelector('#mpwem-speaker-ajax-pagination');
		if (!mount) {
			mount = el('div', { id: 'mpwem-speaker-ajax-pagination', class: 'mpwem-speaker-ajax-pagination' });
			card.appendChild(mount);
		}
		return mount;
	}

	function hideNativeSpeakerPagination(wrap) {
		wrap.querySelectorAll('#posts-filter .tablenav-pages').forEach(function (node) {
			node.style.display = 'none';
		});
	}

	function setSpeakerLoading(wrap, isLoading) {
		var table = wrap.querySelector('#posts-filter table.wp-list-table');
		if (!table) {
			return;
		}
		table.classList.toggle('is-ajax-loading', !!isLoading);
	}

	function loadSpeakerPage(wrap, page) {
		if (!cfg.ajaxPagination || loading) {
			return;
		}
		page = parseInt(page, 10) || 1;
		loading = true;
		setSpeakerLoading(wrap, true);

		var body = new FormData();
		body.append('action', 'mpwem_speaker_list_paginate');
		body.append('nonce', cfg.nonce || '');
		body.append('paged', String(page));
		body.append('post_status', getSpeakerStatus());
		body.append('s', getSpeakerSearch());

		fetch(cfg.ajaxUrl || (window.ajaxurl || ''), {
			method: 'POST',
			credentials: 'same-origin',
			body: body
		})
			.then(function (res) {
				return res.json();
			})
			.then(function (json) {
				if (!json || !json.success || !json.data) {
					throw new Error('bad response');
				}
				var data = json.data;
				var tbody = wrap.querySelector('#the-list');
				if (tbody) {
					tbody.innerHTML = data.tbody || '';
				}
				clearActionsColumn(wrap);
				buildActionsColumn(wrap);

				var mount = ensureSpeakerPaginationMount(wrap);
				if (mount) {
					mount.innerHTML = data.pagination || '';
				}

				currentPage = data.page || page;
				var url = new URL(window.location.href);
				if (currentPage > 1) {
					url.searchParams.set('paged', String(currentPage));
				} else {
					url.searchParams.delete('paged');
				}
				window.history.replaceState({}, '', url.toString());
			})
			.catch(function () {
				window.alert(strings.error || 'Could not load speakers. Please try again.');
			})
			.finally(function () {
				loading = false;
				setSpeakerLoading(wrap, false);
			});
	}

	function initSpeakerAjaxPagination(wrap) {
		if (!cfg.ajaxPagination) {
			return;
		}

		hideNativeSpeakerPagination(wrap);
		ensureSpeakerPaginationMount(wrap);

		var params = new URLSearchParams(window.location.search);
		currentPage = parseInt(params.get('paged') || '1', 10) || 1;

		// Always refresh via AJAX so pagination UI matches the 10-per-page endpoint.
		loadSpeakerPage(wrap, currentPage);

		wrap.addEventListener('click', function (event) {
			var btn = event.target.closest('.mpwem-speaker-page-btn');
			if (!btn || btn.disabled || btn.classList.contains('is-active')) {
				return;
			}
			event.preventDefault();
			var page = parseInt(btn.getAttribute('data-page'), 10);
			if (page) {
				loadSpeakerPage(wrap, page);
			}
		});
	}

	/* ---- Add / Edit Speaker modal + Delete confirm ---- */

	var speakerMediaFrame = null;
	var speakerModalSaving = false;
	var speakerDeleteSaving = false;
	var activeListWrap = null;
	var editingSpeakerId = 0;
	var pendingDelete = { id: 0, name: '', force: false };

	function escapeHtml(str) {
		return String(str || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;');
	}

	function getSpeakerModal() {
		return document.getElementById('mpwem-speaker-modal');
	}

	function setSpeakerModalMode(isEdit) {
		var title = document.getElementById('mpwem-speaker-modal-title');
		var subtitle = document.getElementById('mpwem-speaker-modal-subtitle');
		var saveBtn = document.getElementById('mpwem-speaker-save-btn');
		if (title) {
			title.textContent = isEdit
				? (strings.editModalTitle || 'Edit Speaker')
				: (strings.modalTitle || 'Add New Speaker');
		}
		if (subtitle) {
			subtitle.textContent = isEdit
				? (strings.editModalSubtitle || 'Update this speaker profile.')
				: (strings.modalSubtitle || 'Create a speaker profile to assign on event pages.');
		}
		if (saveBtn && !speakerModalSaving) {
			saveBtn.textContent = isEdit
				? (strings.update || 'Update Speaker')
				: (strings.save || 'Create Speaker');
		}
	}

	function ensureSpeakerModal() {
		if (getSpeakerModal()) {
			return getSpeakerModal();
		}

		var modal = el('div', {
			id: 'mpwem-speaker-modal',
			class: 'mpwem-speaker-modal',
			'aria-hidden': 'true'
		});

		modal.innerHTML =
			'<div class="mpwem-speaker-modal__backdrop" data-speaker-modal-close="1"></div>' +
			'<div class="mpwem-speaker-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="mpwem-speaker-modal-title">' +
				'<button type="button" class="mpwem-speaker-modal__close" data-speaker-modal-close="1" aria-label="' + escapeHtml(strings.cancel || 'Cancel') + '">' +
					'<span class="dashicons dashicons-no-alt"></span>' +
				'</button>' +
				'<div class="mpwem-speaker-modal__header">' +
					'<span class="mpwem-speaker-modal__icon dashicons dashicons-groups" aria-hidden="true"></span>' +
					'<div>' +
						'<h2 id="mpwem-speaker-modal-title">' + escapeHtml(strings.modalTitle || 'Add New Speaker') + '</h2>' +
						'<p id="mpwem-speaker-modal-subtitle">' + escapeHtml(strings.modalSubtitle || 'Create a speaker profile to assign on event pages.') + '</p>' +
					'</div>' +
				'</div>' +
				'<form id="mpwem-speaker-create-form" class="mpwem-speaker-modal__form" novalidate>' +
					'<input type="hidden" id="mpwem-speaker-id" name="id" value="0" />' +
					'<div class="mpwem-speaker-modal__grid">' +
						'<div class="mpwem-speaker-modal__field mpwem-speaker-modal__field--full">' +
							'<label for="mpwem-speaker-name">' + escapeHtml(strings.nameLabel || 'Speaker Name') + ' <span>*</span></label>' +
							'<input type="text" id="mpwem-speaker-name" name="name" required maxlength="200" placeholder="' + escapeHtml(strings.namePlaceholder || '') + '" />' +
						'</div>' +
						'<div class="mpwem-speaker-modal__field mpwem-speaker-modal__field--full">' +
							'<label for="mpwem-speaker-role">' + escapeHtml(strings.roleLabel || 'Role / Title') + '</label>' +
							'<input type="text" id="mpwem-speaker-role" name="excerpt" maxlength="200" placeholder="' + escapeHtml(strings.rolePlaceholder || '') + '" />' +
						'</div>' +
						'<div class="mpwem-speaker-modal__field mpwem-speaker-modal__field--full">' +
							'<label for="mpwem-speaker-desc">' + escapeHtml(strings.descLabel || 'Description') + '</label>' +
							'<textarea id="mpwem-speaker-desc" name="description" rows="4" placeholder="' + escapeHtml(strings.descPlaceholder || '') + '"></textarea>' +
						'</div>' +
						'<div class="mpwem-speaker-modal__field">' +
							'<label>' + escapeHtml(strings.imageLabel || 'Featured Image') + '</label>' +
							'<div class="mpwem-speaker-modal__image">' +
								'<div class="mpwem-speaker-modal__preview" id="mpwem-speaker-image-preview">' +
									'<span class="dashicons dashicons-format-image"></span>' +
								'</div>' +
								'<div class="mpwem-speaker-modal__image-actions">' +
									'<button type="button" class="button" id="mpwem-speaker-image-select">' + escapeHtml(strings.imageSelect || 'Select Image') + '</button>' +
									'<button type="button" class="button-link-delete" id="mpwem-speaker-image-remove" hidden>' + escapeHtml(strings.imageRemove || 'Remove') + '</button>' +
									'<input type="hidden" id="mpwem-speaker-image-id" name="image_id" value="0" />' +
								'</div>' +
							'</div>' +
						'</div>' +
						'<div class="mpwem-speaker-modal__field">' +
							'<label for="mpwem-speaker-status">' + escapeHtml(strings.statusLabel || 'Status') + '</label>' +
							'<select id="mpwem-speaker-status" name="status">' +
								'<option value="publish">' + escapeHtml(strings.statusPublish || 'Publish') + '</option>' +
								'<option value="draft">' + escapeHtml(strings.statusDraft || 'Draft') + '</option>' +
							'</select>' +
						'</div>' +
					'</div>' +
					'<div class="mpwem-speaker-modal__status" id="mpwem-speaker-modal-status" hidden></div>' +
					'<div class="mpwem-speaker-modal__actions">' +
						'<button type="button" class="button" data-speaker-modal-close="1">' + escapeHtml(strings.cancel || 'Cancel') + '</button>' +
						'<button type="submit" class="button button-primary" id="mpwem-speaker-save-btn">' + escapeHtml(strings.save || 'Create Speaker') + '</button>' +
					'</div>' +
				'</form>' +
			'</div>';

		document.body.appendChild(modal);

		modal.addEventListener('click', function (event) {
			if (event.target.closest('[data-speaker-modal-close]')) {
				closeSpeakerModal();
			}
		});

		document.getElementById('mpwem-speaker-image-select').addEventListener('click', openSpeakerMedia);
		document.getElementById('mpwem-speaker-image-remove').addEventListener('click', clearSpeakerImage);
		document.getElementById('mpwem-speaker-create-form').addEventListener('submit', submitSpeakerForm);

		document.addEventListener('keydown', function (event) {
			if (event.key !== 'Escape') {
				return;
			}
			if (modal.classList.contains('is-open')) {
				closeSpeakerModal();
			}
			var deleteModal = getSpeakerDeleteModal();
			if (deleteModal && deleteModal.classList.contains('is-open')) {
				closeSpeakerDeleteModal();
			}
		});

		return modal;
	}

	function ensureSpeakerDeleteModal() {
		var existing = getSpeakerDeleteModal();
		if (existing) {
			return existing;
		}

		var modal = el('div', {
			id: 'mpwem-speaker-delete-modal',
			class: 'mpwem-speaker-modal mpwem-speaker-delete-modal',
			'aria-hidden': 'true'
		});

		modal.innerHTML =
			'<div class="mpwem-speaker-modal__backdrop" data-speaker-delete-close="1"></div>' +
			'<div class="mpwem-speaker-modal__dialog mpwem-speaker-delete-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="mpwem-speaker-delete-title">' +
				'<button type="button" class="mpwem-speaker-modal__close" data-speaker-delete-close="1" aria-label="' + escapeHtml(strings.cancel || 'Cancel') + '">' +
					'<span class="dashicons dashicons-no-alt"></span>' +
				'</button>' +
				'<div class="mpwem-speaker-modal__header">' +
					'<span class="mpwem-speaker-modal__icon mpwem-speaker-modal__icon--danger dashicons dashicons-trash" aria-hidden="true"></span>' +
					'<div>' +
						'<h2 id="mpwem-speaker-delete-title">' + escapeHtml(strings.deleteTitle || 'Delete Speaker?') + '</h2>' +
						'<p id="mpwem-speaker-delete-text"></p>' +
					'</div>' +
				'</div>' +
				'<div class="mpwem-speaker-delete-modal__name" id="mpwem-speaker-delete-name"></div>' +
				'<div class="mpwem-speaker-modal__status" id="mpwem-speaker-delete-status" hidden></div>' +
				'<div class="mpwem-speaker-modal__actions">' +
					'<button type="button" class="button" data-speaker-delete-close="1">' + escapeHtml(strings.cancel || 'Cancel') + '</button>' +
					'<button type="button" class="button button-primary mpwem-speaker-delete-confirm" id="mpwem-speaker-delete-confirm">' + escapeHtml(strings.deleteConfirm || 'Move to Trash') + '</button>' +
				'</div>' +
			'</div>';

		document.body.appendChild(modal);

		modal.addEventListener('click', function (event) {
			if (event.target.closest('[data-speaker-delete-close]')) {
				closeSpeakerDeleteModal();
			}
		});
		document.getElementById('mpwem-speaker-delete-confirm').addEventListener('click', confirmSpeakerDelete);

		return modal;
	}

	function getSpeakerDeleteModal() {
		return document.getElementById('mpwem-speaker-delete-modal');
	}

	function setSpeakerImagePreview(url, id) {
		var preview = document.getElementById('mpwem-speaker-image-preview');
		var removeBtn = document.getElementById('mpwem-speaker-image-remove');
		var selectBtn = document.getElementById('mpwem-speaker-image-select');
		var idInput = document.getElementById('mpwem-speaker-image-id');
		if (!preview || !idInput) {
			return;
		}
		idInput.value = String(id || 0);
		if (url && id) {
			preview.innerHTML = '<img src="' + escapeHtml(url) + '" alt="" />';
			preview.classList.add('has-image');
			if (removeBtn) {
				removeBtn.hidden = false;
			}
			if (selectBtn) {
				selectBtn.textContent = strings.imageChange || 'Change Image';
			}
		} else {
			preview.innerHTML = '<span class="dashicons dashicons-format-image"></span>';
			preview.classList.remove('has-image');
			if (removeBtn) {
				removeBtn.hidden = true;
			}
			if (selectBtn) {
				selectBtn.textContent = strings.imageSelect || 'Select Image';
			}
		}
	}

	function clearSpeakerImage() {
		setSpeakerImagePreview('', 0);
	}

	function openSpeakerMedia() {
		if (typeof window.wp === 'undefined' || !wp.media) {
			window.alert(strings.createError || 'Media library is not available.');
			return;
		}
		if (!speakerMediaFrame) {
			speakerMediaFrame = wp.media({
				title: strings.imageSelect || 'Select Image',
				button: { text: strings.imageSelect || 'Select Image' },
				library: { type: 'image' },
				multiple: false
			});
			speakerMediaFrame.on('select', function () {
				var attachment = speakerMediaFrame.state().get('selection').first().toJSON();
				var url = (attachment.sizes && attachment.sizes.thumbnail && attachment.sizes.thumbnail.url)
					? attachment.sizes.thumbnail.url
					: attachment.url;
				setSpeakerImagePreview(url, attachment.id);
			});
		}
		speakerMediaFrame.open();
	}

	function setSpeakerModalStatus(message, type) {
		var status = document.getElementById('mpwem-speaker-modal-status');
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
	}

	function resetSpeakerModalForm() {
		var form = document.getElementById('mpwem-speaker-create-form');
		if (form) {
			form.reset();
		}
		var idInput = document.getElementById('mpwem-speaker-id');
		if (idInput) {
			idInput.value = '0';
		}
		editingSpeakerId = 0;
		clearSpeakerImage();
		setSpeakerModalStatus('', '');
		speakerModalSaving = false;
		setSpeakerModalMode(false);
		var saveBtn = document.getElementById('mpwem-speaker-save-btn');
		if (saveBtn) {
			saveBtn.disabled = false;
		}
	}

	function openSpeakerModal() {
		if (!cfg.ajaxCreate) {
			return;
		}
		var modal = ensureSpeakerModal();
		resetSpeakerModalForm();
		modal.classList.add('is-open');
		modal.setAttribute('aria-hidden', 'false');
		document.body.classList.add('mpwem-speaker-modal-open');
		var nameInput = document.getElementById('mpwem-speaker-name');
		if (nameInput) {
			setTimeout(function () {
				nameInput.focus();
			}, 50);
		}
	}

	function openSpeakerEditModal(speakerId) {
		if (!cfg.ajaxCreate || !speakerId) {
			return;
		}
		var modal = ensureSpeakerModal();
		resetSpeakerModalForm();
		setSpeakerModalMode(true);
		editingSpeakerId = parseInt(speakerId, 10) || 0;
		var idInput = document.getElementById('mpwem-speaker-id');
		if (idInput) {
			idInput.value = String(editingSpeakerId);
		}

		modal.classList.add('is-open');
		modal.setAttribute('aria-hidden', 'false');
		document.body.classList.add('mpwem-speaker-modal-open');
		setSpeakerModalStatus(strings.loading || 'Loading…', 'success');

		var body = new FormData();
		body.append('action', 'mpwem_speaker_get');
		body.append('nonce', cfg.nonce || '');
		body.append('id', String(editingSpeakerId));

		fetch(cfg.ajaxUrl || (window.ajaxurl || ''), {
			method: 'POST',
			credentials: 'same-origin',
			body: body
		})
			.then(function (res) { return res.json(); })
			.then(function (json) {
				if (!json || !json.success || !json.data) {
					throw new Error((json && json.data) || strings.loadError || 'Could not load speaker details.');
				}
				var data = json.data;
				var nameInput = document.getElementById('mpwem-speaker-name');
				var roleInput = document.getElementById('mpwem-speaker-role');
				var descInput = document.getElementById('mpwem-speaker-desc');
				var statusInput = document.getElementById('mpwem-speaker-status');
				if (nameInput) { nameInput.value = data.name || ''; }
				if (roleInput) { roleInput.value = data.excerpt || ''; }
				if (descInput) { descInput.value = data.description || ''; }
				if (statusInput) { statusInput.value = data.status || 'publish'; }
				setSpeakerImagePreview(data.image_url || '', data.image_id || 0);
				setSpeakerModalStatus('', '');
				if (nameInput) { nameInput.focus(); }
			})
			.catch(function (err) {
				setSpeakerModalStatus(err.message || strings.loadError || 'Could not load speaker details.', 'error');
			});
	}

	function closeSpeakerModal() {
		var modal = getSpeakerModal();
		if (!modal) {
			return;
		}
		modal.classList.remove('is-open');
		modal.setAttribute('aria-hidden', 'true');
		document.body.classList.remove('mpwem-speaker-modal-open');
		speakerModalSaving = false;
		editingSpeakerId = 0;
	}

	function submitSpeakerForm(event) {
		event.preventDefault();
		if (speakerModalSaving) {
			return;
		}

		var nameInput = document.getElementById('mpwem-speaker-name');
		var name = nameInput ? nameInput.value.trim() : '';
		if (!name) {
			setSpeakerModalStatus(strings.nameRequired || 'Please enter a speaker name.', 'error');
			if (nameInput) {
				nameInput.focus();
			}
			return;
		}

		var isEdit = editingSpeakerId > 0;
		speakerModalSaving = true;
		setSpeakerModalStatus('', '');
		var saveBtn = document.getElementById('mpwem-speaker-save-btn');
		if (saveBtn) {
			saveBtn.disabled = true;
			saveBtn.textContent = isEdit
				? (strings.updating || 'Updating…')
				: (strings.saving || 'Creating…');
		}

		var body = new FormData();
		body.append('action', isEdit ? 'mpwem_speaker_update' : 'mpwem_speaker_create');
		body.append('nonce', cfg.nonce || '');
		if (isEdit) {
			body.append('id', String(editingSpeakerId));
		}
		body.append('name', name);
		body.append('excerpt', (document.getElementById('mpwem-speaker-role') || {}).value || '');
		body.append('description', (document.getElementById('mpwem-speaker-desc') || {}).value || '');
		body.append('image_id', (document.getElementById('mpwem-speaker-image-id') || {}).value || '0');
		body.append('status', (document.getElementById('mpwem-speaker-status') || {}).value || 'publish');

		fetch(cfg.ajaxUrl || (window.ajaxurl || ''), {
			method: 'POST',
			credentials: 'same-origin',
			body: body
		})
			.then(function (res) {
				return res.json();
			})
			.then(function (json) {
				if (!json || !json.success) {
					var msg = (json && json.data) ? json.data : (isEdit ? strings.updateError : strings.createError);
					throw new Error(typeof msg === 'string' ? msg : (isEdit ? strings.updateError : strings.createError));
				}
				closeSpeakerModal();
				if (activeListWrap && cfg.ajaxPagination) {
					loadSpeakerPage(activeListWrap, isEdit ? currentPage : 1);
				} else {
					window.location.reload();
				}
			})
			.catch(function (err) {
				setSpeakerModalStatus(err.message || (isEdit ? strings.updateError : strings.createError), 'error');
				speakerModalSaving = false;
				if (saveBtn) {
					saveBtn.disabled = false;
					saveBtn.textContent = isEdit
						? (strings.update || 'Update Speaker')
						: (strings.save || 'Create Speaker');
				}
			});
	}

	function openSpeakerDeleteModal(speakerId, speakerName, force) {
		if (!cfg.ajaxCreate || !speakerId) {
			return;
		}
		pendingDelete = {
			id: parseInt(speakerId, 10) || 0,
			name: speakerName || '',
			force: !!force
		};
		var modal = ensureSpeakerDeleteModal();
		var text = document.getElementById('mpwem-speaker-delete-text');
		var nameEl = document.getElementById('mpwem-speaker-delete-name');
		var confirmBtn = document.getElementById('mpwem-speaker-delete-confirm');
		var status = document.getElementById('mpwem-speaker-delete-status');

		if (text) {
			text.textContent = pendingDelete.force
				? (strings.deleteForceText || 'This speaker will be permanently deleted. This cannot be undone.')
				: (strings.deleteText || 'This speaker will be moved to Trash. You can restore it later.');
		}
		if (nameEl) {
			nameEl.textContent = pendingDelete.name || ('#' + pendingDelete.id);
		}
		if (confirmBtn) {
			confirmBtn.disabled = false;
			confirmBtn.textContent = pendingDelete.force
				? (strings.deleteForceConfirm || 'Delete Permanently')
				: (strings.deleteConfirm || 'Move to Trash');
			confirmBtn.classList.toggle('is-force', pendingDelete.force);
		}
		if (status) {
			status.hidden = true;
			status.textContent = '';
		}

		speakerDeleteSaving = false;
		modal.classList.add('is-open');
		modal.setAttribute('aria-hidden', 'false');
		document.body.classList.add('mpwem-speaker-modal-open');
	}

	function closeSpeakerDeleteModal() {
		var modal = getSpeakerDeleteModal();
		if (!modal) {
			return;
		}
		modal.classList.remove('is-open');
		modal.setAttribute('aria-hidden', 'true');
		if (!getSpeakerModal() || !getSpeakerModal().classList.contains('is-open')) {
			document.body.classList.remove('mpwem-speaker-modal-open');
		}
		speakerDeleteSaving = false;
		pendingDelete = { id: 0, name: '', force: false };
	}

	function confirmSpeakerDelete() {
		if (speakerDeleteSaving || !pendingDelete.id) {
			return;
		}
		speakerDeleteSaving = true;
		var confirmBtn = document.getElementById('mpwem-speaker-delete-confirm');
		var status = document.getElementById('mpwem-speaker-delete-status');
		if (confirmBtn) {
			confirmBtn.disabled = true;
			confirmBtn.textContent = strings.deleting || 'Deleting…';
		}
		if (status) {
			status.hidden = true;
		}

		var body = new FormData();
		body.append('action', 'mpwem_speaker_delete');
		body.append('nonce', cfg.nonce || '');
		body.append('id', String(pendingDelete.id));
		body.append('force', pendingDelete.force ? '1' : '0');

		fetch(cfg.ajaxUrl || (window.ajaxurl || ''), {
			method: 'POST',
			credentials: 'same-origin',
			body: body
		})
			.then(function (res) { return res.json(); })
			.then(function (json) {
				if (!json || !json.success) {
					var msg = (json && json.data) ? json.data : (strings.deleteError || 'Could not delete speaker.');
					throw new Error(typeof msg === 'string' ? msg : strings.deleteError);
				}
				closeSpeakerDeleteModal();
				if (activeListWrap && cfg.ajaxPagination) {
					loadSpeakerPage(activeListWrap, currentPage);
				} else {
					window.location.reload();
				}
			})
			.catch(function (err) {
				speakerDeleteSaving = false;
				if (confirmBtn) {
					confirmBtn.disabled = false;
					confirmBtn.textContent = pendingDelete.force
						? (strings.deleteForceConfirm || 'Delete Permanently')
						: (strings.deleteConfirm || 'Move to Trash');
				}
				if (status) {
					status.hidden = false;
					status.textContent = err.message || strings.deleteError || 'Could not delete speaker.';
					status.className = 'mpwem-speaker-modal__status is-error';
				}
			});
	}

	function initSpeakerRowActions(wrap) {
		if (!cfg.ajaxCreate) {
			return;
		}
		wrap.addEventListener('click', function (event) {
			var viewTarget = event.target.closest('[data-speaker-view]');
			if (viewTarget) {
				event.preventDefault();
				openSpeakerViewModal(viewTarget.getAttribute('data-speaker-view'));
				return;
			}
			var editTarget = event.target.closest('[data-speaker-edit]');
			if (editTarget) {
				event.preventDefault();
				openSpeakerEditModal(editTarget.getAttribute('data-speaker-edit'));
				return;
			}
			var deleteTarget = event.target.closest('[data-speaker-delete]');
			if (deleteTarget) {
				event.preventDefault();
				openSpeakerDeleteModal(
					deleteTarget.getAttribute('data-speaker-delete'),
					deleteTarget.getAttribute('data-speaker-name') || '',
					deleteTarget.getAttribute('data-speaker-force') === '1'
				);
			}
		});
	}

	function getSpeakerViewModal() {
		return document.getElementById('mpwem-speaker-view-modal');
	}

	function ensureSpeakerViewModal() {
		var existing = getSpeakerViewModal();
		if (existing) {
			return existing;
		}

		var modal = el('div', {
			id: 'mpwem-speaker-view-modal',
			class: 'mpwem-speaker-modal mpwem-speaker-view-modal',
			'aria-hidden': 'true'
		});

		modal.innerHTML =
			'<div class="mpwem-speaker-modal__backdrop" data-speaker-view-close="1"></div>' +
			'<div class="mpwem-speaker-modal__dialog mpwem-speaker-view-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="mpwem-speaker-view-title">' +
				'<button type="button" class="mpwem-speaker-modal__close" data-speaker-view-close="1" aria-label="' + escapeHtml(strings.viewClose || 'Close') + '">' +
					'<span class="dashicons dashicons-no-alt"></span>' +
				'</button>' +
				'<div class="mpwem-speaker-view-modal__loading" id="mpwem-speaker-view-loading">' + escapeHtml(strings.loading || 'Loading…') + '</div>' +
				'<div class="mpwem-speaker-view-modal__body" id="mpwem-speaker-view-body" hidden></div>' +
				'<div class="mpwem-speaker-modal__status" id="mpwem-speaker-view-status" hidden></div>' +
				'<div class="mpwem-speaker-modal__actions">' +
					'<button type="button" class="button" data-speaker-view-close="1">' + escapeHtml(strings.viewClose || 'Close') + '</button>' +
					'<button type="button" class="button button-primary" id="mpwem-speaker-view-edit" hidden>' + escapeHtml(strings.viewEdit || 'Edit Speaker') + '</button>' +
				'</div>' +
			'</div>';

		document.body.appendChild(modal);

		modal.addEventListener('click', function (event) {
			if (event.target.closest('[data-speaker-view-close]')) {
				closeSpeakerViewModal();
			}
		});

		document.getElementById('mpwem-speaker-view-edit').addEventListener('click', function () {
			var id = this.getAttribute('data-speaker-id');
			closeSpeakerViewModal();
			if (id) {
				openSpeakerEditModal(id);
			}
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && modal.classList.contains('is-open')) {
				closeSpeakerViewModal();
			}
		});

		return modal;
	}

	function closeSpeakerViewModal() {
		var modal = getSpeakerViewModal();
		if (!modal) {
			return;
		}
		modal.classList.remove('is-open');
		modal.setAttribute('aria-hidden', 'true');
		if ((!getSpeakerModal() || !getSpeakerModal().classList.contains('is-open')) &&
			(!getSpeakerDeleteModal() || !getSpeakerDeleteModal().classList.contains('is-open'))) {
			document.body.classList.remove('mpwem-speaker-modal-open');
		}
	}

	function renderSpeakerViewContent(data) {
		var empty = strings.viewEmpty || '—';
		var role = (data.excerpt || '').trim() || empty;
		var desc = (data.description_plain || '').trim() || empty;
		var status = data.status_label || data.status || empty;
		var created = data.date
			? (data.date + (data.time ? (' ' + (strings.at || 'at') + ' ' + data.time) : ''))
			: empty;

		var eventsHtml = '';
		if (data.events && data.events.length) {
			eventsHtml = '<ul class="mpwem-speaker-view-events">' + data.events.map(function (event) {
				if (event.url) {
					return '<li><a href="' + escapeHtml(event.url) + '">' + escapeHtml(event.title) + '</a></li>';
				}
				return '<li>' + escapeHtml(event.title) + '</li>';
			}).join('') + '</ul>';
		} else {
			eventsHtml = '<p class="mpwem-speaker-view-empty">' + escapeHtml(strings.viewNoEvents || 'Not assigned to any event.') + '</p>';
		}

		var imageHtml = data.image_full
			? '<img src="' + escapeHtml(data.image_full) + '" alt="' + escapeHtml(data.name || '') + '" />'
			: '<span class="dashicons dashicons-admin-users"></span>';

		return '' +
			'<div class="mpwem-speaker-view-hero">' +
				'<div class="mpwem-speaker-view-avatar' + (data.image_full ? ' has-image' : '') + '">' + imageHtml + '</div>' +
				'<div class="mpwem-speaker-view-hero-text">' +
					'<h2 id="mpwem-speaker-view-title">' + escapeHtml(data.name || '') + '</h2>' +
					'<span class="mpwem-speaker-view-badge">' + escapeHtml(status) + '</span>' +
				'</div>' +
			'</div>' +
			'<div class="mpwem-speaker-view-grid">' +
				'<div class="mpwem-speaker-view-item">' +
					'<span class="mpwem-speaker-view-label">' + escapeHtml(strings.viewRole || 'Role / Title') + '</span>' +
					'<div class="mpwem-speaker-view-value">' + escapeHtml(role) + '</div>' +
				'</div>' +
				'<div class="mpwem-speaker-view-item">' +
					'<span class="mpwem-speaker-view-label">' + escapeHtml(strings.viewDate || 'Created') + '</span>' +
					'<div class="mpwem-speaker-view-value">' + escapeHtml(created) + '</div>' +
				'</div>' +
				'<div class="mpwem-speaker-view-item mpwem-speaker-view-item--full">' +
					'<span class="mpwem-speaker-view-label">' + escapeHtml(strings.viewDesc || 'Description') + '</span>' +
					'<div class="mpwem-speaker-view-value">' + escapeHtml(desc) + '</div>' +
				'</div>' +
				'<div class="mpwem-speaker-view-item mpwem-speaker-view-item--full">' +
					'<span class="mpwem-speaker-view-label">' + escapeHtml(strings.viewEvents || 'Assigned Events') + '</span>' +
					'<div class="mpwem-speaker-view-value">' + eventsHtml + '</div>' +
				'</div>' +
			'</div>';
	}

	function openSpeakerViewModal(speakerId) {
		if (!cfg.ajaxCreate || !speakerId) {
			return;
		}
		var modal = ensureSpeakerViewModal();
		var loading = document.getElementById('mpwem-speaker-view-loading');
		var body = document.getElementById('mpwem-speaker-view-body');
		var status = document.getElementById('mpwem-speaker-view-status');
		var editBtn = document.getElementById('mpwem-speaker-view-edit');

		if (loading) { loading.hidden = false; }
		if (body) { body.hidden = true; body.innerHTML = ''; }
		if (status) { status.hidden = true; status.textContent = ''; }
		if (editBtn) {
			editBtn.hidden = true;
			editBtn.setAttribute('data-speaker-id', '');
		}

		modal.classList.add('is-open');
		modal.setAttribute('aria-hidden', 'false');
		document.body.classList.add('mpwem-speaker-modal-open');

		var form = new FormData();
		form.append('action', 'mpwem_speaker_get');
		form.append('nonce', cfg.nonce || '');
		form.append('id', String(speakerId));

		fetch(cfg.ajaxUrl || (window.ajaxurl || ''), {
			method: 'POST',
			credentials: 'same-origin',
			body: form
		})
			.then(function (res) { return res.json(); })
			.then(function (json) {
				if (!json || !json.success || !json.data) {
					throw new Error((json && json.data) || strings.loadError || 'Could not load speaker details.');
				}
				if (loading) { loading.hidden = true; }
				if (body) {
					body.hidden = false;
					body.innerHTML = renderSpeakerViewContent(json.data);
				}
				if (editBtn && json.data.can_edit) {
					editBtn.hidden = false;
					editBtn.setAttribute('data-speaker-id', String(json.data.id || speakerId));
				}
			})
			.catch(function (err) {
				if (loading) { loading.hidden = true; }
				if (status) {
					status.hidden = false;
					status.textContent = err.message || strings.loadError || 'Could not load speaker details.';
					status.className = 'mpwem-speaker-modal__status is-error';
				}
			});
	}

	function getEmailPreviewModal() {
		return document.getElementById('mpwem-email-preview-modal');
	}

	function ensureEmailPreviewModal() {
		var existing = getEmailPreviewModal();
		if (existing) {
			return existing;
		}

		var modal = el('div', {
			id: 'mpwem-email-preview-modal',
			class: 'mpwem-speaker-modal mpwem-email-preview-modal',
			hidden: 'hidden',
			'aria-hidden': 'true'
		});
		modal.innerHTML =
			'<div class="mpwem-speaker-modal__backdrop" data-email-preview-close="1"></div>' +
			'<div class="mpwem-speaker-modal__dialog mpwem-email-preview-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="mpwem-email-preview-title">' +
				'<button type="button" class="mpwem-speaker-modal__close" data-email-preview-close="1" aria-label="' + escapeHtml(strings.previewClose || 'Close') + '">' +
					'<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>' +
				'</button>' +
				'<div class="mpwem-email-preview-modal__header">' +
					'<h2 id="mpwem-email-preview-title">' + escapeHtml(strings.previewTitle || 'Email Template Preview') + '</h2>' +
					'<p class="mpwem-email-preview-modal__subtitle" id="mpwem-email-preview-subtitle"></p>' +
				'</div>' +
				'<div class="mpwem-speaker-view-modal__loading" id="mpwem-email-preview-loading">' + escapeHtml(strings.loading || 'Loading…') + '</div>' +
				'<div class="mpwem-email-preview-modal__frame-wrap" id="mpwem-email-preview-body" hidden>' +
					'<iframe id="mpwem-email-preview-frame" class="mpwem-email-preview-modal__frame" title="' + escapeHtml(strings.previewTitle || 'Email Template Preview') + '"></iframe>' +
				'</div>' +
				'<div class="mpwem-speaker-modal__status" id="mpwem-email-preview-status" hidden></div>' +
				'<div class="mpwem-speaker-modal__actions">' +
					'<button type="button" class="button" data-email-preview-close="1">' + escapeHtml(strings.previewClose || 'Close') + '</button>' +
					'<a class="button button-primary" id="mpwem-email-preview-edit" hidden href="#">' + escapeHtml(strings.previewEdit || 'Edit Template') + '</a>' +
				'</div>' +
			'</div>';

		document.body.appendChild(modal);

		modal.addEventListener('click', function (event) {
			if (event.target.closest('[data-email-preview-close]')) {
				closeEmailPreviewModal();
			}
		});

		document.addEventListener('keydown', function (event) {
			var openModal = getEmailPreviewModal();
			if (event.key === 'Escape' && openModal && openModal.classList.contains('is-open')) {
				closeEmailPreviewModal();
			}
		});

		return modal;
	}

	function openEmailPreviewModal() {
		var modal = ensureEmailPreviewModal();
		modal.hidden = false;
		modal.classList.add('is-open');
		modal.setAttribute('aria-hidden', 'false');
		document.body.classList.add('mpwem-speaker-modal-open');
	}

	function closeEmailPreviewModal() {
		var modal = getEmailPreviewModal();
		if (!modal) {
			return;
		}
		modal.classList.remove('is-open');
		modal.hidden = true;
		modal.setAttribute('aria-hidden', 'true');
		document.body.classList.remove('mpwem-speaker-modal-open');
		var frame = document.getElementById('mpwem-email-preview-frame');
		if (frame) {
			frame.removeAttribute('srcdoc');
			try { frame.src = 'about:blank'; } catch (e) { /* ignore */ }
		}
	}

	function initEmailPreviewActions(wrap) {
		wrap.addEventListener('click', function (event) {
			var btn = event.target.closest('[data-email-preview]');
			if (!btn) {
				return;
			}
			event.preventDefault();
			loadEmailPreview(btn.getAttribute('data-email-preview'));
		});
	}

	function loadEmailPreview(postId) {
		if (!postId) {
			return;
		}

		openEmailPreviewModal();

		var loadingEl = document.getElementById('mpwem-email-preview-loading');
		var body = document.getElementById('mpwem-email-preview-body');
		var status = document.getElementById('mpwem-email-preview-status');
		var editBtn = document.getElementById('mpwem-email-preview-edit');
		var titleEl = document.getElementById('mpwem-email-preview-title');
		var subtitle = document.getElementById('mpwem-email-preview-subtitle');
		var frame = document.getElementById('mpwem-email-preview-frame');

		if (loadingEl) { loadingEl.hidden = false; }
		if (body) { body.hidden = true; }
		if (status) { status.hidden = true; status.textContent = ''; }
		if (editBtn) { editBtn.hidden = true; }
		if (subtitle) { subtitle.textContent = ''; }
		if (titleEl) { titleEl.textContent = strings.previewTitle || 'Email Template Preview'; }

		var form = new FormData();
		form.append('action', 'mpwem_waitlist_email_preview');
		form.append('nonce', cfg.nonce || '');
		form.append('post_id', String(postId));

		fetch(cfg.ajaxUrl || (window.ajaxurl || ''), {
			method: 'POST',
			credentials: 'same-origin',
			body: form
		})
			.then(function (res) { return res.json(); })
			.then(function (json) {
				if (loadingEl) { loadingEl.hidden = true; }
				if (!json || !json.success) {
					var msg = strings.previewError || 'Could not load email preview.';
					if (json && json.data) {
						msg = typeof json.data === 'string' ? json.data : (json.data.message || msg);
					}
					throw new Error(msg);
				}
				if (!json.data) {
					throw new Error(strings.previewError || 'Could not load email preview.');
				}

				var data = json.data;
				if (titleEl) {
					titleEl.textContent = strings.previewTitle || 'Email Template Preview';
				}
				if (subtitle) {
					subtitle.textContent = data.title || '';
				}

				if (data.empty || !data.html) {
					if (status) {
						status.hidden = false;
						status.textContent = strings.previewEmpty || 'This template has no content yet.';
						status.className = 'mpwem-speaker-modal__status';
					}
				} else if (frame && body) {
					body.hidden = false;
					body.removeAttribute('hidden');
					frame.srcdoc = data.html;
				}

				if (editBtn && data.editUrl) {
					editBtn.hidden = false;
					editBtn.removeAttribute('hidden');
					editBtn.setAttribute('href', data.editUrl);
				}
			})
			.catch(function (err) {
				if (loadingEl) { loadingEl.hidden = true; }
				if (status) {
					status.hidden = false;
					status.textContent = err.message || strings.previewError || 'Could not load email preview.';
					status.className = 'mpwem-speaker-modal__status is-error';
				}
			});
	}

	function init() {
		var wrap = document.querySelector('.wrap');
		if (!wrap) {
			document.body.classList.add('mpwem-post-list-ready');
			return;
		}
		wrap.classList.add('mpwem-post-list-modern-wrap');
		activeListWrap = wrap;

		try {
			swapHeading(wrap);
			buildLayout(wrap);
			dockAddButton(wrap);
			removeTableFooter(wrap);
			buildActionsColumn(wrap);
			initSpeakerAjaxPagination(wrap);
			if (cfg.ajaxCreate) {
				ensureSpeakerModal();
				ensureSpeakerDeleteModal();
				ensureSpeakerViewModal();
				initSpeakerRowActions(wrap);
			}
			if (cfg.emailPreview) {
				ensureEmailPreviewModal();
				initEmailPreviewActions(wrap);
			}
		} finally {
			document.body.classList.add('mpwem-post-list-ready');
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
