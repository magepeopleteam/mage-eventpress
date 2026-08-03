/**
 * Modern reskin for secondary Event CPT list screens (edit.php).
 * Speakers / Waitlist Email / Reg Form / Reviews.
 * Add & Edit navigate to post-new.php / post.php (rich editors stay intact).
 */
(function () {
	'use strict';

	var cfg = window.mpwemPostListModern || {};
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

		if (cfg.subheading && !wrap.querySelector('.mpwem-post-list-subtitle')) {
			var subtitle = el('p', { class: 'mpwem-post-list-subtitle' });
			subtitle.textContent = cfg.subheading;
			heading.insertAdjacentElement('afterend', subtitle);
		}
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

		var right = el('div', { class: 'mpwem-tablenav-right' });
		var pages = tablenavTop.querySelector('.tablenav-pages');
		if (pages) {
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

			if (rowActions) {
				var editLink = rowActions.querySelector('.edit a, .inline.hide-if-no-js + .edit a');
				if (!editLink) {
					editLink = rowActions.querySelector('span.edit a');
				}
				var trashLink = rowActions.querySelector('.trash a, .delete a');

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

	function init() {
		var wrap = document.querySelector('.wrap');
		if (!wrap) {
			document.body.classList.add('mpwem-post-list-ready');
			return;
		}
		wrap.classList.add('mpwem-post-list-modern-wrap');

		try {
			swapHeading(wrap);
			buildLayout(wrap);
			dockAddButton(wrap);
			removeTableFooter(wrap);
			buildActionsColumn(wrap);
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
