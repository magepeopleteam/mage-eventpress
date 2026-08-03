/**
 * Modern reskin for Event Category taxonomy (mep_cat — edit-tags.php).
 * Only enqueued on that screen — see MPWEM_Taxonomy_Modern::enqueue().
 * Layout/UX mirrors the bus plugin's wbtm-taxonomy-modern.js.
 */
(function () {
	'use strict';

	var cfg = window.mpwemTaxonomyModern || {};
	var strings = cfg.strings || {};

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
		var pages = tablenavTop.querySelector('.tablenav-pages');
		if (pages) {
			right.appendChild(pages);
		}
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
		var match = /^tag-(\d+)$/.exec(row.id || '');
		return match ? parseInt(match[1], 10) : 0;
	}

	function buildActionsColumn(wrap) {
		var table = wrap.querySelector('#col-right table.wp-list-table');
		if (!table) {
			return;
		}

		var label = strings.actionsColumn || 'Actions';

		table.querySelectorAll('thead tr').forEach(function (headerRow) {
			var th = el('th', { scope: 'col', class: 'manage-column column-mpwem-actions' });
			th.textContent = label;
			headerRow.appendChild(th);
		});

		table.querySelectorAll('tbody tr').forEach(function (row) {
			var td = el('td', { class: 'column-mpwem-actions', 'data-colname': label });
			var rowActions = row.querySelector('.row-actions');
			var termId = getRowTermId(row);

			if (rowActions) {
				var editLink = rowActions.querySelector('.edit a');
				var deleteLink = rowActions.querySelector('.delete a');

				if (editLink) {
					editLink.classList.add('mpwem-row-action-icon', 'mpwem-row-action-edit');
					editLink.innerHTML = '<span class="dashicons dashicons-edit" aria-hidden="true"></span>';
					editLink.addEventListener('click', function (evt) {
						evt.preventDefault();
						openEditModal(cfg.taxonomy, termId);
					});
					td.appendChild(editLink);
				}
				if (deleteLink) {
					deleteLink.classList.add('mpwem-row-action-icon', 'mpwem-row-action-delete');
					deleteLink.innerHTML = '<span class="dashicons dashicons-trash" aria-hidden="true"></span>';
					deleteLink.addEventListener('click', function (evt) {
						evt.preventDefault();
						deleteTerm(cfg.taxonomy, termId, deleteLink);
					});
					td.appendChild(deleteLink);
				}
			}

			row.appendChild(td);
		});
	}

	function removeBulkSelection(wrap) {
		var table = wrap.querySelector('#col-right table.wp-list-table');
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
			var text = cell.textContent.trim();
			if (!text || cell.querySelector('.mpwem-slug-badge')) {
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
					window.location.reload();
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

	function deleteTerm(taxonomy, termId, triggerEl) {
		taxonomy = taxonomy || cfg.taxonomy;
		if (!termId) {
			return;
		}
		if (!window.confirm(bundleFor(taxonomy).confirmDelete || 'Delete this item?')) {
			return;
		}

		var body = new URLSearchParams();
		body.set('action', 'mpwem_delete_taxonomy_term');
		body.set('nonce', cfg.deleteNonce || '');
		body.set('taxonomy', taxonomy || '');
		body.set('term_id', String(termId));

		if (triggerEl) {
			triggerEl.style.pointerEvents = 'none';
			triggerEl.style.opacity = '.5';
		}

		fetch(cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		})
			.then(function (response) { return response.json(); })
			.then(function (json) {
				if (json && json.success) {
					window.location.reload();
					return;
				}
				var message = (json && json.data && json.data.message) ? json.data.message : (strings.deleteFailed || 'Could not delete this item.');
				window.alert(message);
			})
			.catch(function () {
				window.alert(strings.deleteFailed || 'Could not delete this item.');
			})
			.finally(function () {
				if (triggerEl) {
					triggerEl.style.pointerEvents = '';
					triggerEl.style.opacity = '';
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

		try {
			swapHeading(wrap);
			replaceSearchWithAddButton(wrap);
			buildPromoPanel(wrap);
			removeTableFooter(wrap);
			buildActionsColumn(wrap);
			removeNameLinks(wrap);
			removeBulkSelection(wrap);
			badgeSlugs(wrap);
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
