<?php
	/**
	 * Google Sheets settings chrome — delegates body to Pro hook.
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'MPWEM_Google_Sheets_Settings_UI' ) ) {
		class MPWEM_Google_Sheets_Settings_UI {

			const SECTION = 'mep_gsheet_settings';

			public static function render() {
				?>
				<div class="mep-gsheet-shell" data-ms-section="<?php echo esc_attr( self::SECTION ); ?>">
					<?php
					if ( has_action( 'wsa_form_bottom_mep_gsheet_settings' ) ) {
						do_action( 'wsa_form_bottom_mep_gsheet_settings', array( 'id' => self::SECTION ) );
					} else {
						?>
						<div class="mep-gsheet-wrap">
							<div class="mep-gsheet__header">
								<h2 class="mep-gsheet__title"><?php esc_html_e( 'Google Sheets', 'mage-eventpress' ); ?></h2>
								<p class="mep-gsheet__subtitle"><?php esc_html_e( 'Sync event orders to a Google Spreadsheet in real time.', 'mage-eventpress' ); ?></p>
							</div>
							<div class="mep-gsheet__card">
								<div class="mep-gsheet__card-body">
									<p class="mep-gsheet-empty"><?php esc_html_e( 'Google Sheets sync is available with Event Manager Pro.', 'mage-eventpress' ); ?></p>
								</div>
							</div>
						</div>
						<?php
					}
					?>
				</div>
				<?php
			}
		}
	}
