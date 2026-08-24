<?php
	/**
	 * Reusable helper that groups several existing Events submenu pages under a
	 * single parent item, rendered as a third-level hover flyout:
	 *
	 *     Events -> Settings -> Event Settings
	 *                           Calendar Settings
	 *                           Status
	 *                           Quick Setup
	 *                           Template Override
	 *
	 * WordPress renders only two menu levels natively, so the parent is a real
	 * submenu page (which redirects to a chosen landing page) and the configured
	 * child pages are moved into a CSS/JS flyout inside it. The child pages stay
	 * registered in their own files — this only regroups how they appear.
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'MPWEM_Admin_Menu_Group' ) ) {
		class MPWEM_Admin_Menu_Group {

			private $parent_slug;
			private $parent_label;
			private $landing_slug;
			private $children;
			private $capability;

			private static $css_printed = false;

			/**
			 * @param array $args {
			 *     @type string   parent_slug   Unique slug for the parent submenu.
			 *     @type string   parent_label  Visible label, e.g. "Settings".
			 *     @type string[] children       Slugs of existing submenu pages to nest.
			 *     @type string   landing_slug   Where clicking the parent lands (default: first child).
			 *     @type string   capability     Required cap (default manage_options).
			 * }
			 */
			public function __construct( array $args ) {
				$this->parent_slug  = $args['parent_slug'];
				$this->parent_label = $args['parent_label'];
				$this->children     = isset( $args['children'] ) ? array_values( $args['children'] ) : array();
				$this->landing_slug = ! empty( $args['landing_slug'] ) ? $args['landing_slug'] : ( $this->children ? $this->children[0] : '' );
				$this->capability   = isset( $args['capability'] ) ? $args['capability'] : 'manage_options';

				add_action( 'admin_menu', array( $this, 'register_parent' ), 99 );
				add_action( 'admin_head', array( __CLASS__, 'print_styles' ) );
				add_action( 'admin_footer', array( $this, 'print_script' ) );
			}

			private function cpt() {
				return class_exists( 'MPWEM_Functions' ) ? MPWEM_Functions::get_cpt() : 'mep_events';
			}

			private function landing_url() {
				return admin_url( 'edit.php?post_type=' . $this->cpt() . '&page=' . $this->landing_slug );
			}

			public function register_parent() {
				$hook = add_submenu_page(
					'edit.php?post_type=' . $this->cpt(),
					$this->parent_label,
					$this->parent_label,
					$this->capability,
					$this->parent_slug,
					array( $this, 'render_parent' )
				);

				if ( $hook ) {
					add_action( 'load-' . $hook, array( $this, 'redirect_to_landing' ) );
				}
			}

			public function redirect_to_landing() {
				if ( $this->landing_slug ) {
					wp_safe_redirect( $this->landing_url() );
					exit;
				}
			}

			public function render_parent() {
				// Fallback only — the load-hook redirect normally handles this.
				echo '<div class="wrap"><p>';
				echo esc_html( $this->parent_label );
				echo '</p><script>window.location.href=' . wp_json_encode( $this->landing_url() ) . ';</script></div>';
			}

			public static function print_styles() {
				if ( self::$css_printed ) {
					return;
				}
				self::$css_printed = true;
				?>
				<style id="mep-menu-flyout-css">
					#adminmenu .mep-menu-parent { position: relative; }
					#adminmenu .mep-menu-arrow { float: right; opacity: .65; font-size: 11px; line-height: 1.6; }
					#adminmenu .mep-menu-flyout {
						display: none;
						position: absolute;
						left: 100%;
						top: 0;
						margin: 0;
						padding: 6px 0;
						min-width: 215px;
						background: #2c3338;
						box-shadow: 0 3px 8px rgba(0,0,0,.25);
						border-radius: 0 4px 4px 0;
						z-index: 10000;
					}
					#adminmenu .mep-menu-parent:hover > .mep-menu-flyout,
					#adminmenu .mep-menu-parent.mep-open > .mep-menu-flyout { display: block; }
					#adminmenu .mep-menu-flyout > li { margin: 0; padding: 0; float: none; display: block; width: auto; }
					#adminmenu .mep-menu-flyout > li > a {
						display: block;
						padding: 8px 14px;
						margin: 0;
						color: #c3c4c7;
						font-size: 13px;
						white-space: nowrap;
						box-shadow: none;
					}
					#adminmenu .mep-menu-flyout > li > a:hover,
					#adminmenu .mep-menu-flyout > li > a:focus { color: #72aee6; background: #1d2327; box-shadow: none; }
					#adminmenu .mep-menu-flyout > li.current > a,
					#adminmenu .mep-menu-flyout > li.current > a:hover { color: #fff; background: #2271b1; }
					#adminmenu .mep-menu-parent.mep-current > a.menu-top,
					#adminmenu .mep-menu-parent.mep-current > a { color: #fff; background: #2271b1; font-weight: 600; }
				</style>
				<?php
			}

			public function print_script() {
				?>
				<script>
				(function () {
					var PARENT  = <?php echo wp_json_encode( $this->parent_slug ); ?>;
					var LANDING = <?php echo wp_json_encode( $this->landing_slug ); ?>;
					var SLUGS   = <?php echo wp_json_encode( array_values( $this->children ) ); ?>;

					function build() {
						var menu = document.getElementById('adminmenu');
						if (!menu) { return; }

						var parentLink = menu.querySelector('a[href*="page=' + PARENT + '"]');
						if (!parentLink) { return; }
						var parentLi = parentLink.closest('li');
						if (!parentLi || parentLi.getAttribute('data-mep-flyout') === '1') { return; }

						var children = [];
						SLUGS.forEach(function (slug) {
							// querySelectorAll: some pages (e.g. Quick Setup) are
							// registered more than once, so move every matching item.
							var anchors = menu.querySelectorAll('a[href*="page=' + slug + '"]');
							for (var i = 0; i < anchors.length; i++) {
								var li = anchors[i].closest('li');
								if (li && li !== parentLi && children.indexOf(li) === -1) { children.push(li); }
							}
						});
						if (!children.length) { return; }

						var flyout = document.createElement('ul');
						flyout.className = 'mep-menu-flyout';

						var anyCurrent = false;
						children.forEach(function (li) {
							if (/(^|\s)current(\s|$)/.test(li.className)) { anyCurrent = true; }
							flyout.appendChild(li);
						});

						parentLi.classList.add('mep-menu-parent');
						if (anyCurrent) { parentLi.classList.add('mep-current'); }
						parentLi.appendChild(flyout);

						if (LANDING) {
							var href = parentLink.getAttribute('href') || '';
							parentLink.setAttribute('href', href.replace('page=' + PARENT, 'page=' + LANDING));
						}

						if (!parentLink.querySelector('.mep-menu-arrow')) {
							var arrow = document.createElement('span');
							arrow.className = 'mep-menu-arrow';
							arrow.setAttribute('aria-hidden', 'true');
							arrow.textContent = '▸';
							parentLink.appendChild(arrow);
						}

						parentLi.setAttribute('data-mep-flyout', '1');
					}

					if (document.readyState !== 'loading') { build(); }
					else { document.addEventListener('DOMContentLoaded', build); }
				})();
				</script>
				<?php
			}
		}
	}

	// Group the core settings pages under a single "Settings" flyout.
	// Deferred to 'init' — this file loads immediately as part of the plugin's
	// eager bootstrap (MPWEM_Dependencies -> MPWEM_Admin -> here), long before
	// 'after_setup_theme'/'init'. The __('Settings', ...) call below used to run
	// at that same immediate point, which is exactly what triggers WordPress's
	// "Translation loading ... triggered too early" notice for this domain.
	add_action( 'init', function () {
		new MPWEM_Admin_Menu_Group( array(
			'parent_slug'  => 'mpwem_settings_hub',
			'parent_label' => __( 'Settings', 'mage-eventpress' ),
			'landing_slug' => 'mep_event_settings_page',
			'capability'   => 'manage_options',
			/**
			 * Slugs nested inside the Settings flyout.
			 *
			 * Filterable so add-ons can nest their own settings page here instead of
			 * adding another top-level Events item — EventPress Pro uses it to place
			 * "PDF Template Override" directly under "Template Override".
			 *
			 * @param string[] $children Submenu page slugs, in display order.
			 */
			'children'     => (array) apply_filters( 'mpwem_settings_group_children', array(
				'mep_event_settings_page', // Event Settings (now also hosts the Status tab)
				'mep_calendar_settings',   // Calendar Settings
				'mpwem_quick_setup',       // Quick Setup
				'mep_template_override',    // Template Override
			) ),
		) );
	} );
