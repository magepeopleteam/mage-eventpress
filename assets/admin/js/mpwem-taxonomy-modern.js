/**
 * Modern reskin for Event Category / Organizer taxonomies (edit-tags.php).
 * Parity with Speakers Management: View/Edit/Delete actions, details modal,
 * Events column, AJAX pagination.
 */
(function () {
	'use strict';

	var cfg = window.mpwemTaxonomyModern || {};
	var strings = cfg.strings || {};
	var activeListWrap = null;
	var currentPage = 1;
	var termDeleteSaving = false;
	var pendingDelete = { id: 0, name: '' };

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

	function escapeHtml(str) {
		return String(str || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;');
	}

	function swapHeading(wrap) {
		var heading = wrap.querySelector('h1.wp-heading-inline');
		if (!heading || !cfg.heading) {
			return;
		}
		heading.textContent = cfg.heading;

		if (cfg.subheading && !wrap.querySelector('.mpwem-taxonomy-subtitle')) {
			var subtitle = el('p', { class: 'mpwem-taxonomy-subtitle' });
			subtitle.textContent = cfg.subheading;
			heading.insertAdjacentElement('afterend', subtitle);
		}
	}

	function replaceSearchWithAddButton(wrap) {
		var searchBox = wrap.querySelector('p.search-box');
		if (searchBox) {
			searchBox.remove();
		}

		var tablenavTop = wrap.querySelector('#col-right .tablenav.top');
		if (!tablenavTop) {
			return;
		}

		var button = el('button', { type: 'button', class: 'button button-primary mpwem-add-term-btn' });
		button.textContent = cfg.addButtonLabel || 'Add New Category';
		button.addEventListener('click', function () {
			openAddModal(cfg.taxonomy);
		});

		var right = el('div', { class: 'mpwem-tablenav-right' });
		right.appendChild(button);
		tablenavTop.appendChild(right);
	}

	function buildPromoPanel(wrap) {
		var colWrap = wrap.querySelector('#col-left .col-wrap');
		if (!colWrap) {
			return;
		}

		var features = (cfg.proFeatures || []).map(function (feature) {
			return '<li>' + feature + '</li>';
		}).join('');

		colWrap.innerHTML =
			'<div class="mpwem-promo-panel">' +
				'<span class="mpwem-promo-eyebrow">' + (strings.promoEyebrow || '') + '</span>' +
				'<h2>' + (strings.promoTitle || '') + '</h2>' +
				'<p class="mpwem-promo-body">' + (strings.promoBody || '') + '</p>' +
				'<ul class="mpwem-promo-features">' + features + '</ul>' +
				'<a class="button button-primary mpwem-promo-cta" href="' + (cfg.proUrl || '#') + '">' + (strings.promoCta || '') + '</a>' +
			'</div>';
	}

	function removeTableFooter(wrap) {
		var tfoot = wrap.querySelector('#col-right table.wp-list-table tfoot');
		if (tfoot) {
			tfoot.remove();
		}
	}

	function getRowTermId(row) {
		var attr = row.getAttribute('data-term-id');
		if (attr) {
			return parseInt(attr, 10) || 0;
		}
		var match = /^tag-(\d+)$/.exec(row.id || '');
		return match ? parseInt(match[1], 10) : 0;
	}

	function getTable(wrap) {
		return wrap.querySelector('#col-right table.wp-list-table');
	}

	function enhanceTableRows(wrap) {
		var table = getTable(wrap);
		if (!table) {
			return;
		}

		var label = strings.actionsColumn || 'Actions';

		if (!table.querySelector('thead th.column-mpwem-actions')) {
			table.querySelectorAll('thead tr').forEach(function (headerRow) {
				var th = el('th', { scope: 'col', class: 'manage-column column-mpwem-actions' });
				th.textContent = label;
				headerRow.appendChild(th);
			});
		}

		table.querySelectorAll('tbody tr').forEach(function (row) {
			if (row.classList.contains('no-items')) {
				var emptyCell = row.querySelector('td.colspanchange');
				if (emptyCell) {
					emptyCell.setAttribute('colspan', String(table.querySelectorAll('thead th, thead td').length));
				}
				return;
			}
			if (row.querySelector('td.column-mpwem-actions')) {
				return;
			}

			var td = el('td', { class: 'column-mpwem-actions', 'data-colname': label });
			var rowActions = row.querySelector('.row-actions');
			var termId = getRowTermId(row);
			var termName = '';
			var titleNode = row.querySelector('.row-title');
			if (titleNode) {
				termName = titleNode.textContent || '';
			}

			if (termId) {
				var viewBtn = el('button', {
					type: 'button',
					class: 'mpwem-row-action-icon mpwem-row-action-view',
					title: strings.view || 'View',
					'data-term-view': String(termId)
				});
				viewBtn.innerHTML = '<span class="dashicons dashicons-visibility" aria-hidden="true"></span>';
				td.appendChild(viewBtn);

				var canEdit = !!(rowActions && rowActions.querySelector('.edit a, [data-term-edit]'));
				var canDelete = !!(rowActions && rowActions.querySelector('.delete a, [data-term-delete]'));

				if (canEdit) {
					var editBtn = el('button', {
						type: 'button',
						class: 'mpwem-row-action-icon mpwem-row-action-edit',
						title: strings.edit || 'Edit',
						'data-term-edit': String(termId)
					});
					editBtn.innerHTML = '<span class="dashicons dashicons-edit" aria-hidden="true"></span>';
					td.appendChild(editBtn);
				}
				if (canDelete) {
					var deleteBtn = el('button', {
						type: 'button',
						class: 'mpwem-row-action-icon mpwem-row-action-delete',
						title: strings.delete || 'Delete',
						'data-term-delete': String(termId),
						'data-term-name': termName
					});
					deleteBtn.innerHTML = '<span class="dashicons dashicons-trash" aria-hidden="true"></span>';
					td.appendChild(deleteBtn);
				}
			}

			row.appendChild(td);
		});

		badgeSlugs(wrap);
		removeNameLinks(wrap);
	}

	function removeBulkSelection(wrap) {
		var table = getTable(wrap);
		if (table) {
			table.querySelectorAll('.check-column').forEach(function (cell) {
				cell.remove();
			});
		}
		wrap.querySelectorAll('#col-right .tablenav .actions.bulkactions').forEach(function (bulk) {
			bulk.remove();
		});
	}

	function badgeSlugs(wrap) {
		wrap.querySelectorAll('#col-right table.wp-list-table tbody td.column-slug').forEach(function (cell) {
			if (cell.querySelector('.mpwem-slug-badge')) {
				return;
			}
			var text = cell.textContent.trim();
			if (!text) {
				return;
			}
			cell.innerHTML = '';
			var badge = el('span', { class: 'mpwem-slug-badge' });
			badge.textContent = text;
			cell.appendChild(badge);
		});
	}

	function removeNameLinks(wrap) {
		wrap.querySelectorAll('#col-right table.wp-list-table tbody td.column-name a.row-title').forEach(function (link) {
			var span = el('span', { class: 'row-title' });
			while (link.firstChild) {
				span.appendChild(link.firstChild);
			}
			link.replaceWith(span);
		});
	}

	function hideNativeTermPagination(wrap) {
		wrap.querySelectorAll('#col-right .tablenav-pages').forEach(function (elPages) {
			elPages.style.display = 'none';
		});
	}

	function ensureTermPaginationMount(wrap) {
		var existing = wrap.querySelector('.mpwem-term-ajax-pagination');
		if (existing) {
			return existing;
		}
		var table = getTable(wrap);
		if (!table || !table.parentNode) {
			return null;
		}
		var mount = el('div', { class: 'mpwem-term-ajax-pagination' });
		table.parentNode.insertBefore(mount, table.nextSibling);
		return mount;
	}

	function loadTermPage(wrap, page) {
		var table = getTable(wrap);
		var mount = ensureTermPaginationMount(wrap);
		if (!table || !cfg.ajaxPagination) {
			return;
		}

		currentPage = page || 1;
		table.classList.add('is-ajax-loading');

		var form = new FormData();
		form.append('action', 'mpwem_taxonomy_list_paginate');
		form.append('nonce', cfg.paginateNonce || '');
		form.append('taxonomy', cfg.taxonomy || '');
		form.append('paged', String(currentPage));

		fetch(cfg.ajaxUrl || (window.ajaxurl || ''), {
			method: 'POST',
			credentials: 'same-origin',
			body: form
		})
			.then(function (res) { return res.json(); })
			.then(function (json) {
				if (!json || !json.success || !json.data) {
					throw new Error((json && json.data && json.data.message) || strings.genericError || 'Error');
				}
				var tbody = table.querySelector('tbody');
				if (tbody) {
					tbody.innerHTML = json.data.tbody || '';
				}
				if (mount) {
					mount.innerHTML = json.data.pagination || '';
				}
				currentPage = json.data.page || currentPage;
				enhanceTableRows(wrap);
			})
			.catch(function () {
				/* keep existing rows */
			})
			.finally(function () {
				table.classList.remove('is-ajax-loading');
			});
	}

	function initTermAjaxPagination(wrap) {
		if (!cfg.ajaxPagination) {
			return;
		}

		hideNativeTermPagination(wrap);
		ensureTermPaginationMount(wrap);

		var params = new URLSearchParams(window.location.search);
		currentPage = parseInt(params.get('paged') || '1', 10) || 1;
		loadTermPage(wrap, currentPage);

		wrap.addEventListener('click', function (event) {
			var btn = event.target.closest('.mpwem-term-page-btn');
			if (!btn || btn.disabled || btn.classList.contains('is-active')) {
				return;
			}
			event.preventDefault();
			var page = parseInt(btn.getAttribute('data-page'), 10);
			if (page) {
				loadTermPage(wrap, page);
			}
		});
	}

	function refreshTermList() {
		if (activeListWrap && cfg.ajaxPagination) {
			loadTermPage(activeListWrap, currentPage || 1);
			return;
		}
		window.location.reload();
	}

	/* ---- Add / Edit modal ---- */

	var modalEls = null;
	var modalState = { mode: 'add', termId: 0 };

	function buildModal() {
		if (modalEls) {
			return modalEls;
		}

		var overlay = el('div', { class: 'mpwem-modal-overlay mpwem-hidden' });
		var dialog = el('div', { class: 'mpwem-modal', role: 'dialog', 'aria-modal': 'true' });
		overlay.appendChild(dialog);

		dialog.innerHTML =
			'<div class="mpwem-modal-header">' +
				'<h2>' + (strings.modalTitle || '') + '</h2>' +
				'<button type="button" class="mpwem-modal-close" aria-label="' + (strings.cancel || 'Cancel') + '">&times;</button>' +
			'</div>' +
			'<div class="mpwem-modal-body">' +
				'<p class="mpwem-modal-error mpwem-hidden"></p>' +
				'<div class="form-field">' +
					'<label for="mpwem-modal-name">' + (strings.name || '') + '</label>' +
					'<input type="text" id="mpwem-modal-name" placeholder="' + (strings.namePlaceholder || '') + '" />' +
					'<p>' + (strings.nameHelp || '') + '</p>' +
				'</div>' +
				'<div class="form-field mpwem-slug-field">' +
					'<label for="mpwem-modal-slug">' + (strings.slug || '') + '</label>' +
					'<input type="text" id="mpwem-modal-slug" placeholder="' + (strings.slugPlaceholder || '') + '" />' +
					'<p>' + (strings.slugHelp || '') + '</p>' +
				'</div>' +
				'<div class="form-field">' +
					'<label for="mpwem-modal-description">' + (strings.description || '') + '</label>' +
					'<textarea id="mpwem-modal-description" placeholder="' + (strings.descPlaceholder || '') + '"></textarea>' +
					'<p>' + (strings.descHelp || '') + '</p>' +
				'</div>' +
				'<div class="mpwem-org-meta-fields mpwem-hidden">' +
					'<p class="mpwem-org-meta-heading">' + (strings.orgSection || '') + '</p>' +
					'<div class="form-field"><label for="mpwem-modal-org_location">' + (strings.orgLocation || '') + '</label><input type="text" id="mpwem-modal-org_location" name="org_location" /></div>' +
					'<div class="form-field"><label for="mpwem-modal-org_street">' + (strings.orgStreet || '') + '</label><input type="text" id="mpwem-modal-org_street" name="org_street" /></div>' +
					'<div class="form-field"><label for="mpwem-modal-org_city">' + (strings.orgCity || '') + '</label><input type="text" id="mpwem-modal-org_city" name="org_city" /></div>' +
					'<div class="form-field"><label for="mpwem-modal-org_state">' + (strings.orgState || '') + '</label><input type="text" id="mpwem-modal-org_state" name="org_state" /></div>' +
					'<div class="form-field"><label for="mpwem-modal-org_postcode">' + (strings.orgPostcode || '') + '</label><input type="text" id="mpwem-modal-org_postcode" name="org_postcode" /></div>' +
					'<div class="form-field"><label for="mpwem-modal-org_email">' + (strings.orgEmail || '') + '</label><input type="text" id="mpwem-modal-org_email" name="org_email" /></div>' +
					'<div class="form-field"><label for="mpwem-modal-org_country">' + (strings.orgCountry || '') + '</label><input type="text" id="mpwem-modal-org_country" name="org_country" /></div>' +
					'<input type="hidden" id="mpwem-modal-latitude" name="latitude" value="" />' +
					'<input type="hidden" id="mpwem-modal-longitude" name="longitude" value="" />' +
				'</div>' +
			'</div>' +
			'<div class="mpwem-modal-footer">' +
				'<button type="button" class="button mpwem-modal-cancel">' + (strings.cancel || '') + '</button>' +
				'<button type="button" class="button button-primary mpwem-modal-submit">' + (strings.submit || '') + '</button>' +
			'</div>';

		document.body.appendChild(overlay);

		var titleEl = dialog.querySelector('.mpwem-modal-header h2');
		var closeBtn = dialog.querySelector('.mpwem-modal-close');
		var cancelBtn = dialog.querySelector('.mpwem-modal-cancel');
		var submitBtn = dialog.querySelector('.mpwem-modal-submit');

		closeBtn.addEventListener('click', closeModal);
		cancelBtn.addEventListener('click', closeModal);
		overlay.addEventListener('click', function (evt) {
			if (evt.target === overlay) {
				closeModal();
			}
		});
		submitBtn.addEventListener('click', submitModal);

		modalEls = { overlay: overlay, dialog: dialog, submitBtn: submitBtn, titleEl: titleEl };
		return modalEls;
	}

	function showError(message) {
		var errorEl = modalEls.dialog.querySelector('.mpwem-modal-error');
		errorEl.textContent = message;
		errorEl.classList.remove('mpwem-hidden');
	}

	function clearError() {
		var errorEl = modalEls.dialog.querySelector('.mpwem-modal-error');
		errorEl.textContent = '';
		errorEl.classList.add('mpwem-hidden');
	}

	var orgMetaKeys = ['org_location', 'org_street', 'org_city', 'org_state', 'org_postcode', 'org_email', 'org_country', 'latitude', 'longitude'];

	function resetModalFields(modal) {
		clearError();
		modal.dialog.querySelector('#mpwem-modal-name').value = '';
		modal.dialog.querySelector('#mpwem-modal-slug').value = '';
		modal.dialog.querySelector('#mpwem-modal-description').value = '';
		orgMetaKeys.forEach(function (key) {
			var input = modal.dialog.querySelector('#mpwem-modal-' + key);
			if (input) {
				input.value = '';
			}
		});
	}

	function bundleFor(taxonomy) {
		var perTaxonomy = cfg.perTaxonomy || {};
		if (taxonomy && perTaxonomy[taxonomy]) {
			return perTaxonomy[taxonomy];
		}
		return {
			modalTitle: strings.modalTitle,
			editModalTitle: strings.editModalTitle,
			namePlaceholder: strings.namePlaceholder,
			nameFieldLabel: strings.name,
			slugPlaceholder: strings.slugPlaceholder,
			descPlaceholder: strings.descPlaceholder,
			submit: strings.submit,
			confirmDelete: strings.confirmDelete,
			deleteTitle: strings.deleteTitle,
			deleteText: strings.deleteText,
			viewTitle: strings.viewTitle,
			viewEdit: strings.viewEdit,
			hasOrgMeta: false
		};
	}

	function applyBundlePlaceholders(modal, bundle) {
		modal.dialog.querySelector('#mpwem-modal-name').setAttribute('placeholder', bundle.namePlaceholder || '');
		modal.dialog.querySelector('#mpwem-modal-slug').setAttribute('placeholder', bundle.slugPlaceholder || '');
		modal.dialog.querySelector('#mpwem-modal-description').setAttribute('placeholder', bundle.descPlaceholder || '');

		var nameLabel = modal.dialog.querySelector('label[for="mpwem-modal-name"]');
		if (nameLabel) {
			nameLabel.textContent = bundle.nameFieldLabel || strings.name || '';
		}

		var orgBlock = modal.dialog.querySelector('.mpwem-org-meta-fields');
		if (orgBlock) {
			orgBlock.classList.toggle('mpwem-hidden', !bundle.hasOrgMeta);
		}
	}

	function setOrgMetaValues(modal, meta) {
		meta = meta || {};
		orgMetaKeys.forEach(function (key) {
			var input = modal.dialog.querySelector('#mpwem-modal-' + key);
			if (input) {
				input.value = meta[key] || '';
			}
		});
	}

	function appendOrgMetaToBody(body, dialog) {
		orgMetaKeys.forEach(function (key) {
			var input = dialog.querySelector('#mpwem-modal-' + key);
			if (input) {
				body.set(key, input.value.trim());
			}
		});
	}

	function openAddModal(taxonomy) {
		taxonomy = taxonomy || cfg.taxonomy;
		var modal = buildModal();
		var bundle = bundleFor(taxonomy);
		modalState = { mode: 'add', termId: 0, taxonomy: taxonomy };
		resetModalFields(modal);
		applyBundlePlaceholders(modal, bundle);
		modal.titleEl.textContent = bundle.modalTitle || 'Add New Category';
		modal.submitBtn.disabled = false;
		modal.submitBtn.textContent = bundle.submit || 'Add New Category';
		modal.overlay.classList.remove('mpwem-hidden');
		document.body.classList.add('mpwem-term-modal-open');
		modal.dialog.querySelector('#mpwem-modal-name').focus();
	}

	function openEditModal(taxonomy, termId) {
		taxonomy = taxonomy || cfg.taxonomy;
		if (!termId) {
			return;
		}
		var modal = buildModal();
		var bundle = bundleFor(taxonomy);
		modalState = { mode: 'edit', termId: termId, taxonomy: taxonomy };
		resetModalFields(modal);
		applyBundlePlaceholders(modal, bundle);
		modal.titleEl.textContent = bundle.editModalTitle || 'Edit Category';
		modal.submitBtn.textContent = strings.saveChanges || 'Save Changes';
		modal.submitBtn.disabled = true;
		modal.dialog.querySelector('#mpwem-modal-name').value = strings.loadingTerm || 'Loading…';
		modal.overlay.classList.remove('mpwem-hidden');
		document.body.classList.add('mpwem-term-modal-open');

		var body = new URLSearchParams();
		body.set('action', 'mpwem_get_taxonomy_term');
		body.set('nonce', cfg.getNonce || '');
		body.set('taxonomy', taxonomy || '');
		body.set('term_id', String(termId));

		fetch(cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		})
			.then(function (response) { return response.json(); })
			.then(function (json) {
				if (modalState.mode !== 'edit' || modalState.termId !== termId || modalState.taxonomy !== taxonomy) {
					return;
				}
				if (!json || !json.success) {
					var message = (json && json.data && json.data.message) ? json.data.message : (strings.genericError || 'Something went wrong.');
					showError(message);
					modal.dialog.querySelector('#mpwem-modal-name').value = '';
					return;
				}
				var data = json.data;
				modal.dialog.querySelector('#mpwem-modal-name').value = data.name || '';
				modal.dialog.querySelector('#mpwem-modal-slug').value = data.slug || '';
				modal.dialog.querySelector('#mpwem-modal-description').value = data.description || '';
				setOrgMetaValues(modal, data.orgMeta || {});
				modal.submitBtn.disabled = false;
			})
			.catch(function () {
				if (modalState.mode === 'edit' && modalState.termId === termId && modalState.taxonomy === taxonomy) {
					showError(strings.genericError || 'Something went wrong.');
					modal.dialog.querySelector('#mpwem-modal-name').value = '';
				}
			});
	}

	function closeModal() {
		if (modalEls) {
			modalEls.overlay.classList.add('mpwem-hidden');
		}
		if ((!getTermViewModal() || !getTermViewModal().classList.contains('is-open')) &&
			(!getTermDeleteModal() || !getTermDeleteModal().classList.contains('is-open'))) {
			document.body.classList.remove('mpwem-term-modal-open');
		}
	}

	function submitModal() {
		var dialog = modalEls.dialog;
		var name = dialog.querySelector('#mpwem-modal-name').value.trim();
		var isEdit = modalState.mode === 'edit';
		var taxonomy = modalState.taxonomy || cfg.taxonomy;
		var bundle = bundleFor(taxonomy);
		clearError();

		if (!name) {
			showError(strings.nameRequired || 'Name is required.');
			return;
		}

		var body = new URLSearchParams();
		body.set('action', isEdit ? 'mpwem_edit_taxonomy_term' : 'mpwem_add_taxonomy_term');
		body.set('nonce', (isEdit ? cfg.editNonce : cfg.nonce) || '');
		body.set('taxonomy', taxonomy || '');
		if (isEdit) {
			body.set('term_id', String(modalState.termId));
		}
		body.set('tag-name', name);
		body.set('slug', dialog.querySelector('#mpwem-modal-slug').value.trim());
		body.set('description', dialog.querySelector('#mpwem-modal-description').value);
		if (bundle.hasOrgMeta) {
			appendOrgMetaToBody(body, dialog);
		}

		modalEls.submitBtn.disabled = true;
		modalEls.submitBtn.textContent = isEdit ? (strings.saving || 'Saving…') : (strings.submitting || 'Adding…');

		fetch(cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		})
			.then(function (response) { return response.json(); })
			.then(function (json) {
				if (json && json.success) {
					closeModal();
					if (!isEdit) {
						currentPage = 1;
					}
					refreshTermList();
					return;
				}
				var message = (json && json.data && json.data.message) ? json.data.message : (strings.genericError || 'Something went wrong.');
				showError(message);
			})
			.catch(function () {
				showError(strings.genericError || 'Something went wrong.');
			})
			.finally(function () {
				modalEls.submitBtn.disabled = false;
				modalEls.submitBtn.textContent = isEdit ? (strings.saveChanges || 'Save Changes') : (bundle.submit || 'Add New Category');
			});
	}

	/* ---- Delete confirm modal ---- */

	function getTermDeleteModal() {
		return document.getElementById('mpwem-term-delete-modal');
	}

	function ensureTermDeleteModal() {
		var existing = getTermDeleteModal();
		if (existing) {
			return existing;
		}

		var modal = el('div', {
			id: 'mpwem-term-delete-modal',
			class: 'mpwem-term-modal mpwem-term-delete-modal',
			'aria-hidden': 'true'
		});

		modal.innerHTML =
			'<div class="mpwem-term-modal__backdrop" data-term-delete-close="1"></div>' +
			'<div class="mpwem-term-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="mpwem-term-delete-title">' +
				'<button type="button" class="mpwem-term-modal__close" data-term-delete-close="1" aria-label="' + escapeHtml(strings.cancel || 'Cancel') + '">' +
					'<span class="dashicons dashicons-no-alt"></span>' +
				'</button>' +
				'<div class="mpwem-term-modal__header">' +
					'<span class="mpwem-term-modal__icon dashicons dashicons-trash" aria-hidden="true"></span>' +
					'<div>' +
						'<h2 id="mpwem-term-delete-title">' + escapeHtml(strings.deleteTitle || 'Delete?') + '</h2>' +
						'<p id="mpwem-term-delete-text">' + escapeHtml(strings.deleteText || '') + '</p>' +
					'</div>' +
				'</div>' +
				'<div class="mpwem-term-modal__status" id="mpwem-term-delete-status" hidden></div>' +
				'<div class="mpwem-term-modal__actions">' +
					'<button type="button" class="button" data-term-delete-close="1">' + escapeHtml(strings.cancel || 'Cancel') + '</button>' +
					'<button type="button" class="button button-primary mpwem-term-delete-confirm" id="mpwem-term-delete-confirm">' + escapeHtml(strings.deleteConfirm || 'Delete Permanently') + '</button>' +
				'</div>' +
			'</div>';

		document.body.appendChild(modal);

		modal.addEventListener('click', function (event) {
			if (event.target.closest('[data-term-delete-close]')) {
				closeTermDeleteModal();
			}
		});

		document.getElementById('mpwem-term-delete-confirm').addEventListener('click', confirmTermDelete);

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && modal.classList.contains('is-open')) {
				closeTermDeleteModal();
			}
		});

		return modal;
	}

	function closeTermDeleteModal() {
		var modal = getTermDeleteModal();
		if (!modal) {
			return;
		}
		modal.classList.remove('is-open');
		modal.setAttribute('aria-hidden', 'true');
		pendingDelete = { id: 0, name: '' };
		termDeleteSaving = false;
		if ((!modalEls || modalEls.overlay.classList.contains('mpwem-hidden')) &&
			(!getTermViewModal() || !getTermViewModal().classList.contains('is-open'))) {
			document.body.classList.remove('mpwem-term-modal-open');
		}
	}

	function openTermDeleteModal(termId, termName) {
		var modal = ensureTermDeleteModal();
		var bundle = bundleFor(cfg.taxonomy);
		var title = document.getElementById('mpwem-term-delete-title');
		var text = document.getElementById('mpwem-term-delete-text');
		var status = document.getElementById('mpwem-term-delete-status');
		var confirmBtn = document.getElementById('mpwem-term-delete-confirm');

		pendingDelete = { id: parseInt(termId, 10) || 0, name: termName || '' };
		if (title) {
			title.textContent = bundle.deleteTitle || strings.deleteTitle || 'Delete?';
		}
		if (text) {
			var base = bundle.deleteText || strings.deleteText || '';
			text.textContent = pendingDelete.name
				? (base + ' (“' + pendingDelete.name + '”)')
				: base;
		}
		if (status) {
			status.hidden = true;
			status.textContent = '';
		}
		if (confirmBtn) {
			confirmBtn.disabled = false;
			confirmBtn.textContent = strings.deleteConfirm || 'Delete Permanently';
		}

		modal.classList.add('is-open');
		modal.setAttribute('aria-hidden', 'false');
		document.body.classList.add('mpwem-term-modal-open');
	}

	function confirmTermDelete() {
		if (termDeleteSaving || !pendingDelete.id) {
			return;
		}
		termDeleteSaving = true;
		var confirmBtn = document.getElementById('mpwem-term-delete-confirm');
		var status = document.getElementById('mpwem-term-delete-status');
		if (confirmBtn) {
			confirmBtn.disabled = true;
			confirmBtn.textContent = strings.deleting || 'Deleting…';
		}

		var body = new URLSearchParams();
		body.set('action', 'mpwem_delete_taxonomy_term');
		body.set('nonce', cfg.deleteNonce || '');
		body.set('taxonomy', cfg.taxonomy || '');
		body.set('term_id', String(pendingDelete.id));

		fetch(cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		})
			.then(function (response) { return response.json(); })
			.then(function (json) {
				if (json && json.success) {
					closeTermDeleteModal();
					refreshTermList();
					return;
				}
				throw new Error((json && json.data && json.data.message) || strings.deleteFailed || 'Could not delete.');
			})
			.catch(function (err) {
				termDeleteSaving = false;
				if (confirmBtn) {
					confirmBtn.disabled = false;
					confirmBtn.textContent = strings.deleteConfirm || 'Delete Permanently';
				}
				if (status) {
					status.hidden = false;
					status.textContent = err.message || strings.deleteFailed || 'Could not delete.';
					status.className = 'mpwem-term-modal__status is-error';
				}
			});
	}

	/* ---- View modal ---- */

	function getTermViewModal() {
		return document.getElementById('mpwem-term-view-modal');
	}

	function ensureTermViewModal() {
		var existing = getTermViewModal();
		if (existing) {
			return existing;
		}

		var modal = el('div', {
			id: 'mpwem-term-view-modal',
			class: 'mpwem-term-modal mpwem-term-view-modal',
			'aria-hidden': 'true'
		});

		modal.innerHTML =
			'<div class="mpwem-term-modal__backdrop" data-term-view-close="1"></div>' +
			'<div class="mpwem-term-modal__dialog mpwem-term-view-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="mpwem-term-view-title">' +
				'<button type="button" class="mpwem-term-modal__close" data-term-view-close="1" aria-label="' + escapeHtml(strings.viewClose || 'Close') + '">' +
					'<span class="dashicons dashicons-no-alt"></span>' +
				'</button>' +
				'<div class="mpwem-term-view-modal__loading" id="mpwem-term-view-loading">' + escapeHtml(strings.loading || 'Loading…') + '</div>' +
				'<div class="mpwem-term-view-modal__body" id="mpwem-term-view-body" hidden></div>' +
				'<div class="mpwem-term-modal__status" id="mpwem-term-view-status" hidden></div>' +
				'<div class="mpwem-term-modal__actions">' +
					'<button type="button" class="button" data-term-view-close="1">' + escapeHtml(strings.viewClose || 'Close') + '</button>' +
					'<button type="button" class="button button-primary" id="mpwem-term-view-edit" hidden>' + escapeHtml(strings.viewEdit || 'Edit') + '</button>' +
				'</div>' +
			'</div>';

		document.body.appendChild(modal);

		modal.addEventListener('click', function (event) {
			if (event.target.closest('[data-term-view-close]')) {
				closeTermViewModal();
			}
		});

		document.getElementById('mpwem-term-view-edit').addEventListener('click', function () {
			var id = this.getAttribute('data-term-id');
			closeTermViewModal();
			if (id) {
				openEditModal(cfg.taxonomy, parseInt(id, 10));
			}
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && modal.classList.contains('is-open')) {
				closeTermViewModal();
			}
		});

		return modal;
	}

	function closeTermViewModal() {
		var modal = getTermViewModal();
		if (!modal) {
			return;
		}
		modal.classList.remove('is-open');
		modal.setAttribute('aria-hidden', 'true');
		if ((!modalEls || modalEls.overlay.classList.contains('mpwem-hidden')) &&
			(!getTermDeleteModal() || !getTermDeleteModal().classList.contains('is-open'))) {
			document.body.classList.remove('mpwem-term-modal-open');
		}
	}

	function renderTermViewContent(data) {
		var empty = strings.viewEmpty || '—';
		var bundle = bundleFor(cfg.taxonomy);
		var desc = (data.description || '').trim() || empty;
		var slug = (data.slug || '').trim() || empty;
		var count = (typeof data.count === 'number') ? String(data.count) : empty;

		var eventsHtml = '';
		if (data.events && data.events.length) {
			eventsHtml = '<ul class="mpwem-term-view-events">' + data.events.map(function (event) {
				if (event.url) {
					return '<li><a href="' + escapeHtml(event.url) + '">' + escapeHtml(event.title) + '</a></li>';
				}
				return '<li>' + escapeHtml(event.title) + '</li>';
			}).join('') + '</ul>';
		} else {
			eventsHtml = '<p class="mpwem-term-view-empty">' + escapeHtml(strings.viewNoEvents || 'Not assigned to any event.') + '</p>';
		}

		var orgHtml = '';
		if (bundle.hasOrgMeta && data.orgMeta) {
			var meta = data.orgMeta;
			var rows = [
				[strings.orgEmail, meta.org_email],
				[strings.orgLocation, meta.org_location],
				[strings.orgStreet, meta.org_street],
				[strings.orgCity, meta.org_city],
				[strings.orgState, meta.org_state],
				[strings.orgPostcode, meta.org_postcode],
				[strings.orgCountry, meta.org_country]
			];
			orgHtml =
				'<div class="mpwem-term-view-item mpwem-term-view-item--full">' +
					'<span class="mpwem-term-view-label">' + escapeHtml(strings.orgSection || 'Contact & location') + '</span>' +
					'<div class="mpwem-term-view-org-grid">' +
						rows.map(function (row) {
							return '<div class="mpwem-term-view-org-item">' +
								'<span class="mpwem-term-view-label">' + escapeHtml(row[0] || '') + '</span>' +
								'<div class="mpwem-term-view-value">' + escapeHtml((row[1] || '').trim() || empty) + '</div>' +
							'</div>';
						}).join('') +
					'</div>' +
				'</div>';
		}

		return '' +
			'<div class="mpwem-term-view-hero">' +
				'<div class="mpwem-term-view-avatar">' +
					'<span class="dashicons dashicons-' + (bundle.hasOrgMeta ? 'businessman' : 'category') + '"></span>' +
				'</div>' +
				'<div class="mpwem-term-view-hero-text">' +
					'<h2 id="mpwem-term-view-title">' + escapeHtml(data.name || '') + '</h2>' +
					'<span class="mpwem-term-view-badge">' + escapeHtml(slug) + '</span>' +
				'</div>' +
			'</div>' +
			'<div class="mpwem-term-view-grid">' +
				'<div class="mpwem-term-view-item">' +
					'<span class="mpwem-term-view-label">' + escapeHtml(strings.viewSlug || 'Slug') + '</span>' +
					'<div class="mpwem-term-view-value">' + escapeHtml(slug) + '</div>' +
				'</div>' +
				'<div class="mpwem-term-view-item">' +
					'<span class="mpwem-term-view-label">' + escapeHtml(strings.viewCount || 'Count') + '</span>' +
					'<div class="mpwem-term-view-value">' + escapeHtml(count) + '</div>' +
				'</div>' +
				'<div class="mpwem-term-view-item mpwem-term-view-item--full">' +
					'<span class="mpwem-term-view-label">' + escapeHtml(strings.viewDesc || 'Description') + '</span>' +
					'<div class="mpwem-term-view-value">' + escapeHtml(desc) + '</div>' +
				'</div>' +
				orgHtml +
				'<div class="mpwem-term-view-item mpwem-term-view-item--full">' +
					'<span class="mpwem-term-view-label">' + escapeHtml(strings.viewEvents || 'Assigned Events') + '</span>' +
					'<div class="mpwem-term-view-value">' + eventsHtml + '</div>' +
				'</div>' +
			'</div>';
	}

	function openTermViewModal(termId) {
		if (!termId) {
			return;
		}
		var modal = ensureTermViewModal();
		var loading = document.getElementById('mpwem-term-view-loading');
		var body = document.getElementById('mpwem-term-view-body');
		var status = document.getElementById('mpwem-term-view-status');
		var editBtn = document.getElementById('mpwem-term-view-edit');
		var bundle = bundleFor(cfg.taxonomy);

		if (loading) { loading.hidden = false; }
		if (body) { body.hidden = true; body.innerHTML = ''; }
		if (status) { status.hidden = true; status.textContent = ''; }
		if (editBtn) {
			editBtn.hidden = true;
			editBtn.setAttribute('data-term-id', '');
			editBtn.textContent = bundle.viewEdit || strings.viewEdit || 'Edit';
		}

		modal.classList.add('is-open');
		modal.setAttribute('aria-hidden', 'false');
		document.body.classList.add('mpwem-term-modal-open');

		var form = new FormData();
		form.append('action', 'mpwem_get_taxonomy_term');
		form.append('nonce', cfg.getNonce || '');
		form.append('taxonomy', cfg.taxonomy || '');
		form.append('term_id', String(termId));

		fetch(cfg.ajaxUrl || (window.ajaxurl || ''), {
			method: 'POST',
			credentials: 'same-origin',
			body: form
		})
			.then(function (res) { return res.json(); })
			.then(function (json) {
				if (!json || !json.success || !json.data) {
					throw new Error((json && json.data && json.data.message) || strings.loadError || 'Could not load details.');
				}
				if (loading) { loading.hidden = true; }
				if (body) {
					body.hidden = false;
					body.innerHTML = renderTermViewContent(json.data);
				}
				if (editBtn && json.data.can_edit) {
					editBtn.hidden = false;
					editBtn.setAttribute('data-term-id', String(json.data.id || termId));
				}
			})
			.catch(function (err) {
				if (loading) { loading.hidden = true; }
				if (status) {
					status.hidden = false;
					status.textContent = err.message || strings.loadError || 'Could not load details.';
					status.className = 'mpwem-term-modal__status is-error';
				}
			});
	}

	function initTermRowActions(wrap) {
		wrap.addEventListener('click', function (event) {
			var viewTarget = event.target.closest('[data-term-view]');
			if (viewTarget) {
				event.preventDefault();
				openTermViewModal(viewTarget.getAttribute('data-term-view'));
				return;
			}
			var editTarget = event.target.closest('[data-term-edit]');
			if (editTarget) {
				event.preventDefault();
				openEditModal(cfg.taxonomy, parseInt(editTarget.getAttribute('data-term-edit'), 10));
				return;
			}
			var deleteTarget = event.target.closest('[data-term-delete]');
			if (deleteTarget) {
				event.preventDefault();
				openTermDeleteModal(
					deleteTarget.getAttribute('data-term-delete'),
					deleteTarget.getAttribute('data-term-name') || ''
				);
			}
		});
	}

	function init() {
		var wrap = document.querySelector('.wrap');
		if (!wrap) {
			document.body.classList.add('mpwem-taxonomy-ready');
			return;
		}
		wrap.classList.add('mpwem-taxonomy-modern-wrap');
		activeListWrap = wrap;

		try {
			swapHeading(wrap);
			replaceSearchWithAddButton(wrap);
			buildPromoPanel(wrap);
			removeTableFooter(wrap);
			removeBulkSelection(wrap);
			enhanceTableRows(wrap);
			ensureTermDeleteModal();
			ensureTermViewModal();
			initTermRowActions(wrap);
			initTermAjaxPagination(wrap);
		} finally {
			document.body.classList.add('mpwem-taxonomy-ready');
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
