<?php
	/*
	* @Author 		engr.sumonazma@gmail.com
	* Copyright: 	mage-people.com
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Welcome' ) ) {
		class MPWEM_Welcome {
			public function __construct() {
				add_action( 'admin_menu', array( $this, 'welcome_menu' ) );
			}

			public function welcome_menu() {
				add_submenu_page(
					'edit.php?post_type=mep_events',
					__( 'Welcome', 'mage-eventpress' ),
					'<span style="color:#10dd10">' . __( 'Welcome', 'mage-eventpress' ) . '</span>',
					'manage_options',
					'mep_event_welcome_page',
					array( $this, 'welcome_page' )
				);
			}

			public function welcome_page() {
				$pro_badge = '<span class="mpwem-badge pro-badge">' . esc_html__( 'PRO', 'mage-eventpress' ) . '</span>';
				$addon_badge = '<span class="mpwem-badge addon-badge">' . esc_html__( 'Addon', 'mage-eventpress' ) . '</span>';
				$event_lists_url = admin_url( 'edit.php?post_type=mep_events&page=mep_event_lists' );
				$new_event_url   = admin_url( 'post-new.php?post_type=mep_events' );
				$settings_url    = admin_url( 'edit.php?post_type=mep_events&page=mep_event_settings_page' );
				?>
                <style>
                    :root {
                        --mpwem-primary-color: var(--color_theme, #6046FF);
                        --mpwem-secondary-color: #2c3e50;
                        --mpwem-bg-light: #f8f9fa;
                        --mpwem-white: #ffffff;
                        --mpwem-text-muted: #6c757d;
                        --mpwem-border: #e9ecef;
                        --mpwem-shadow: 0 4px 6px rgba(0,0,0,0.1);
                        --mpwem-radius: 8px;
                    }
                    .mpwem_welcome_wrap {
                        margin: 20px 20px 0 0;
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                    }
                    .mpwem_welcome_wrap .updated,
                    .mpwem_welcome_wrap .notice { display: none !important; }
                    .mpwem-welcome-header {
                        background: var(--mpwem-white);
                        padding: 30px;
                        border-radius: var(--mpwem-radius);
                        box-shadow: var(--mpwem-shadow);
                        margin-bottom: 20px;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        gap: 20px;
                        position: relative;
                        overflow: hidden;
                    }
                    .mpwem-welcome-header::before {
                        content: '';
                        position: absolute;
                        top: 0; left: 0; width: 5px; height: 100%;
                        background: var(--mpwem-primary-color);
                    }
                    .mpwem-welcome-header h1 {
                        margin: 0;
                        font-size: 28px;
                        color: var(--mpwem-secondary-color);
                    }
                    .mpwem-welcome-header p {
                        margin: 10px 0 0;
                        font-size: 16px;
                        color: var(--mpwem-text-muted);
                    }
                    .mpwem-tabs-container {
                        display: flex;
                        gap: 20px;
                    }
                    .mpwem-tabs-nav {
                        width: 250px;
                        background: var(--mpwem-white);
                        border-radius: var(--mpwem-radius);
                        box-shadow: var(--mpwem-shadow);
                        padding: 10px;
                        height: fit-content;
                        flex-shrink: 0;
                    }
                    .mpwem-tab-link {
                        display: flex;
                        align-items: center;
                        padding: 12px 15px;
                        margin-bottom: 5px;
                        border-radius: 6px;
                        cursor: pointer;
                        color: var(--mpwem-secondary-color);
                        font-weight: 500;
                        transition: all 0.3s ease;
                        text-decoration: none;
                    }
                    .mpwem-tab-link i {
                        margin-right: 12px;
                        width: 20px;
                        text-align: center;
                        font-size: 18px;
                    }
                    .mpwem-tab-link:hover {
                        background: #f0f4f8;
                        color: var(--mpwem-primary-color);
                    }
                    .mpwem-tab-link.active {
                        background: var(--mpwem-primary-color);
                        color: var(--mpwem-white);
                    }
                    .mpwem-tabs-content {
                        flex: 1;
                        background: var(--mpwem-white);
                        border-radius: var(--mpwem-radius);
                        box-shadow: var(--mpwem-shadow);
                        padding: 30px;
                        min-height: 500px;
                        min-width: 0;
                    }
                    .mpwem-tab-pane {
                        display: none;
                        animation: mpwemFadeIn 0.4s ease;
                    }
                    .mpwem-tab-pane.active { display: block; }
                    @keyframes mpwemFadeIn {
                        from { opacity: 0; transform: translateY(10px); }
                        to { opacity: 1; transform: translateY(0); }
                    }
                    .mpwem-section-title {
                        margin: 0 0 20px;
                        font-size: 22px;
                        padding-bottom: 10px;
                        border-bottom: 2px solid var(--mpwem-bg-light);
                        color: var(--mpwem-secondary-color);
                    }
                    .mpwem-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                        gap: 20px;
                        margin-top: 20px;
                    }
                    .mpwem-card {
                        background: var(--mpwem-bg-light);
                        border-radius: var(--mpwem-radius);
                        padding: 20px;
                        border: 1px solid var(--mpwem-border);
                        transition: transform 0.3s ease;
                    }
                    .mpwem-card:hover { transform: translateY(-5px); }
                    .mpwem-card h3 {
                        margin: 0 0 15px;
                        font-size: 18px;
                        color: var(--mpwem-secondary-color);
                    }
                    .mpwem-card p {
                        color: #444;
                        line-height: 1.6;
                        margin: 0;
                    }
                    .mpwem-card .mpwem-card-icon {
                        font-size: 28px;
                        color: var(--mpwem-primary-color);
                        margin-bottom: 12px;
                    }
                    .mpwem-video-wrapper {
                        position: relative;
                        padding-bottom: 56.25%;
                        height: 0;
                        overflow: hidden;
                        border-radius: 6px;
                        margin-bottom: 15px;
                    }
                    .mpwem-video-wrapper iframe {
                        position: absolute;
                        top: 0; left: 0; width: 100%; height: 100%;
                    }
                    .mpwem-steps-table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    .mpwem-steps-table td {
                        padding: 20px;
                        vertical-align: top;
                        border-bottom: 1px solid var(--mpwem-border);
                    }
                    .mpwem-step-num {
                        background: var(--mpwem-primary-color);
                        color: #fff;
                        width: 30px;
                        height: 30px;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: bold;
                        flex-shrink: 0;
                    }
                    .mpwem-data-table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 20px;
                    }
                    .mpwem-data-table th {
                        background: var(--mpwem-bg-light);
                        text-align: left;
                        padding: 12px 15px;
                        border: 1px solid var(--mpwem-border);
                        font-weight: 600;
                    }
                    .mpwem-data-table td {
                        padding: 12px 15px;
                        border: 1px solid var(--mpwem-border);
                        vertical-align: top;
                    }
                    .mpwem-data-table code {
                        background: #f0edff;
                        color: #4a3db8;
                        padding: 3px 6px;
                        border-radius: 4px;
                        font-size: 13px;
                        display: inline-block;
                        margin: 2px 0;
                    }
                    .mpwem-badge {
                        padding: 3px 8px;
                        border-radius: 4px;
                        font-size: 10px;
                        font-weight: bold;
                        margin-left: 5px;
                        vertical-align: middle;
                    }
                    .pro-badge { background: #ffd700; color: #000; }
                    .addon-badge { background: #e8f0fe; color: #1a73e8; }
                    .mpwem-btn {
                        display: inline-block;
                        padding: 10px 20px;
                        background: var(--mpwem-primary-color);
                        color: #fff !important;
                        text-decoration: none !important;
                        border-radius: 5px;
                        font-weight: 500;
                        transition: opacity 0.3s;
                        border: none;
                        cursor: pointer;
                        font-size: 14px;
                        line-height: 1.4;
                    }
                    .mpwem-btn:hover { opacity: 0.9; color: #fff !important; }
                    .pro-btn { background: #27ae60; }
                    .mpwem-btn-dark { background: #34495e; }
                    .mpwem-btn-blue { background: #2980b9; }
                    .mpwem-btn-sm {
                        padding: 6px 12px;
                        font-size: 12px;
                        margin-top: 8px;
                    }
                    .mpwem-feature-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                        gap: 12px;
                        margin-top: 20px;
                    }
                    .mpwem-feature-item {
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        background: var(--mpwem-bg-light);
                        border: 1px solid var(--mpwem-border);
                        border-radius: 6px;
                        padding: 12px 14px;
                    }
                    .mpwem-feature-item i {
                        color: var(--mpwem-primary-color);
                        width: 20px;
                        text-align: center;
                    }
                    .mpwem-doc-tabs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
                    .mpwem-doc-tab-btn {
                        padding: 8px 16px; border: 1px solid var(--mpwem-border); border-radius: 6px;
                        background: var(--mpwem-bg-light); color: var(--mpwem-secondary-color);
                        cursor: pointer; font-size: 13px; font-weight: 500; transition: all .25s;
                    }
                    .mpwem-doc-tab-btn:hover { background: #e8eaed; }
                    .mpwem-doc-tab-btn.active {
                        background: var(--mpwem-primary-color); color: #fff;
                        border-color: var(--mpwem-primary-color);
                    }
                    .mpwem-doc-panel { display: none; animation: mpwemFadeIn .4s ease; }
                    .mpwem-doc-panel.active { display: block; }
                    .mpwem-doc-panel h3 { margin: 0 0 12px; font-size: 18px; color: var(--mpwem-secondary-color); }
                    .mpwem-doc-panel h4 { margin: 18px 0 8px; font-size: 15px; color: var(--mpwem-secondary-color); }
                    .mpwem-doc-panel p { line-height: 1.7; color: #444; }
                    .mpwem-doc-panel ul { padding-left: 20px; }
                    .mpwem-doc-panel li { margin-bottom: 8px; line-height: 1.6; color: #444; }
                    .mpwem-doc-panel .doc-note {
                        background: #fff8e1; border-left: 4px solid #ffc107; padding: 12px 16px;
                        border-radius: 4px; margin: 12px 0; font-size: 13px;
                    }
                    .mpwem-doc-panel .doc-tip {
                        background: #e8f5e9; border-left: 4px solid #4caf50; padding: 12px 16px;
                        border-radius: 4px; margin: 12px 0; font-size: 13px;
                    }
                    .mpwem-doc-panel .doc-pro-section {
                        background: #f3e5f5; border: 1px solid #ce93d8; border-radius: 8px;
                        padding: 16px 20px; margin: 16px 0;
                    }
                    .mpwem-doc-panel .doc-pro-section h4 { color: #7b1fa2; margin-top: 0; }
                    .mpwem-doc-tabs .doc-tab-pro {
                        background: linear-gradient(135deg, #f3e5f5, #e1bee7); border-color: #ce93d8;
                    }
                    .mpwem-doc-tabs .doc-tab-pro.active {
                        background: linear-gradient(135deg, #7b1fa2, #9c27b0); border-color: #7b1fa2;
                    }
                    .mpwem-faq-item {
                        border: 1px solid var(--mpwem-border);
                        border-radius: 6px;
                        margin-bottom: 10px;
                        overflow: hidden;
                    }
                    .mpwem-faq-q {
                        padding: 14px 16px;
                        background: var(--mpwem-bg-light);
                        cursor: pointer;
                        font-weight: 600;
                        color: var(--mpwem-secondary-color);
                        display: flex;
                        align-items: center;
                        gap: 10px;
                    }
                    .mpwem-faq-q i { color: var(--mpwem-primary-color); width: 16px; }
                    .mpwem-faq-a {
                        display: none;
                        padding: 14px 16px;
                        line-height: 1.7;
                        color: #444;
                        border-top: 1px solid var(--mpwem-border);
                    }
                    .mpwem-faq-item.open .mpwem-faq-a { display: block; }
                    .mpwem-faq-item.open .mpwem-faq-q { background: #f0edff; }
                    @media (max-width: 900px) {
                        .mpwem-tabs-container { flex-direction: column; }
                        .mpwem-tabs-nav { width: 100%; }
                        .mpwem-welcome-header { flex-direction: column; align-items: flex-start; }
                    }
                </style>

                <div class="wrap mpwem_welcome_wrap mpwem_welcome_page">
                    <div class="mpwem-welcome-header">
                        <div>
                            <h1><?php esc_html_e( 'Event Booking Manager for WooCommerce', 'mage-eventpress' ); ?></h1>
                            <p><?php esc_html_e( 'Thank you for choosing the complete event ticketing & registration solution for WordPress.', 'mage-eventpress' ); ?></p>
                        </div>
                        <a href="https://mage-people.com/product/mage-woo-event-booking-manager-pro/" class="mpwem-btn pro-btn" target="_blank" rel="noopener noreferrer">
                            <i class="fas fa-crown"></i> <?php esc_html_e( 'Unlock PRO Features', 'mage-eventpress' ); ?>
                        </a>
                    </div>

                    <div class="mpwem-tabs-container">
                        <div class="mpwem-tabs-nav">
                            <div class="mpwem-tab-link active" data-tab="tab-get-started">
                                <i class="fas fa-rocket"></i> <?php esc_html_e( 'Get Started', 'mage-eventpress' ); ?>
                            </div>
                            <div class="mpwem-tab-link" data-tab="tab-tutorials">
                                <i class="fas fa-play-circle"></i> <?php esc_html_e( 'Video Tutorials', 'mage-eventpress' ); ?>
                            </div>
                            <div class="mpwem-tab-link" data-tab="tab-shortcodes">
                                <i class="fas fa-code"></i> <?php esc_html_e( 'Shortcodes', 'mage-eventpress' ); ?>
                            </div>
                            <div class="mpwem-tab-link" data-tab="tab-documents">
                                <i class="fas fa-book-open"></i> <?php esc_html_e( 'Documents', 'mage-eventpress' ); ?>
                            </div>
                            <div class="mpwem-tab-link" data-tab="tab-addons">
                                <i class="fas fa-puzzle-piece"></i> <?php esc_html_e( 'Addons', 'mage-eventpress' ); ?>
                            </div>
                            <div class="mpwem-tab-link" data-tab="tab-support">
                                <i class="fas fa-headset"></i> <?php esc_html_e( 'Help & Support', 'mage-eventpress' ); ?>
                            </div>
                        </div>

                        <div class="mpwem-tabs-content">

                            <!-- Get Started -->
                            <div id="tab-get-started" class="mpwem-tab-pane active">
                                <h2 class="mpwem-section-title"><?php esc_html_e( 'Get Started in Minutes', 'mage-eventpress' ); ?></h2>
                                <p><?php esc_html_e( 'Follow these steps to create your first event or import sample data to explore the plugin.', 'mage-eventpress' ); ?></p>

                                <table class="mpwem-steps-table">
                                    <tr>
                                        <td width="50"><div class="mpwem-step-num">1</div></td>
                                        <td>
                                            <h3><?php esc_html_e( 'Import Dummy Events (Optional)', 'mage-eventpress' ); ?></h3>
                                            <p><?php esc_html_e( 'Quickly preview layouts and features by importing sample events from the Event Lists screen.', 'mage-eventpress' ); ?></p>
                                            <div style="margin-top: 15px;">
                                                <a href="<?php echo esc_url( $event_lists_url ); ?>" class="mpwem-btn mpwem-btn-dark">
                                                    <i class="fas fa-download"></i> <?php esc_html_e( 'Go to Event Lists', 'mage-eventpress' ); ?>
                                                </a>
                                            </div>
                                            <p style="margin-top:10px; font-size: 13px; font-style: italic; color: var(--mpwem-text-muted);">
                                                <?php esc_html_e( 'Note: The Import Dummy Data button appears when you have no published events yet.', 'mage-eventpress' ); ?>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><div class="mpwem-step-num">2</div></td>
                                        <td>
                                            <h3><?php esc_html_e( 'Create Your First Event', 'mage-eventpress' ); ?></h3>
                                            <p><?php printf(
												/* translators: %s: Add New Event admin link */
												esc_html__( 'Go to %s and fill in venue, tickets, dates, and settings using the step-by-step editor.', 'mage-eventpress' ),
												'<strong><a href="' . esc_url( $new_event_url ) . '">' . esc_html__( 'Events → Add New Event', 'mage-eventpress' ) . '</a></strong>'
											); ?></p>
                                            <div style="margin-top: 15px;">
                                                <a href="<?php echo esc_url( $new_event_url ); ?>" class="mpwem-btn">
                                                    <i class="fas fa-plus"></i> <?php esc_html_e( 'Add New Event', 'mage-eventpress' ); ?>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><div class="mpwem-step-num">3</div></td>
                                        <td>
                                            <h3><?php esc_html_e( 'Configure Global Settings', 'mage-eventpress' ); ?></h3>
                                            <p><?php printf(
												/* translators: %s: Settings admin link */
												esc_html__( 'Open %s to set labels, slug, email defaults, style, and display options for all events.', 'mage-eventpress' ),
												'<strong><a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Events → Settings', 'mage-eventpress' ) . '</a></strong>'
											); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><div class="mpwem-step-num">4</div></td>
                                        <td>
                                            <h3><?php esc_html_e( 'Display Events on Your Site', 'mage-eventpress' ); ?></h3>
                                            <p><?php esc_html_e( 'Use shortcodes like [event-list] or [event-calendar] on any page. See the Shortcodes tab for the full list.', 'mage-eventpress' ); ?></p>
                                        </td>
                                    </tr>
                                </table>

                                <h2 class="mpwem-section-title" style="margin-top:40px;"><?php esc_html_e( "Pro Features You'll Love", 'mage-eventpress' ); ?></h2>
                                <div class="mpwem-feature-grid">
									<?php
									$pro_features = array(
										array( 'icon' => 'fas fa-tasks', 'label' => __( 'Attendee Management', 'mage-eventpress' ) ),
										array( 'icon' => 'fab fa-wpforms', 'label' => __( 'Attendee Custom Form', 'mage-eventpress' ) ),
										array( 'icon' => 'fas fa-file-pdf', 'label' => __( 'PDF Ticketing', 'mage-eventpress' ) ),
										array( 'icon' => 'far fa-envelope', 'label' => __( 'Custom Emailing', 'mage-eventpress' ) ),
										array( 'icon' => 'fas fa-user-edit', 'label' => __( 'Attendee Edit Feature', 'mage-eventpress' ) ),
										array( 'icon' => 'fas fa-file-alt', 'label' => __( 'Attendee CSV Export', 'mage-eventpress' ) ),
										array( 'icon' => 'far fa-file-alt', 'label' => __( 'Report Overview', 'mage-eventpress' ) ),
										array( 'icon' => 'fas fa-palette', 'label' => __( 'Custom Style Settings', 'mage-eventpress' ) ),
										array( 'icon' => 'fas fa-language', 'label' => __( 'Translation Settings', 'mage-eventpress' ) ),
									);
									foreach ( $pro_features as $feature ) :
										?>
                                        <div class="mpwem-feature-item">
                                            <i class="<?php echo esc_attr( $feature['icon'] ); ?>"></i>
                                            <span><?php echo esc_html( $feature['label'] ); ?></span>
                                        </div>
									<?php endforeach; ?>
                                </div>
                                <div style="margin-top: 20px;">
                                    <a href="https://mage-people.com/product/mage-woo-event-booking-manager-pro/" class="mpwem-btn pro-btn" target="_blank" rel="noopener noreferrer">
                                        <i class="fas fa-crown"></i> <?php esc_html_e( 'Get PRO Now', 'mage-eventpress' ); ?>
                                    </a>
                                    <a href="https://wpevently.com/" class="mpwem-btn mpwem-btn-dark" style="margin-left:8px;" target="_blank" rel="noopener noreferrer">
                                        <?php esc_html_e( 'View Demo', 'mage-eventpress' ); ?>
                                    </a>
                                </div>
                            </div>

                            <!-- Tutorials -->
                            <div id="tab-tutorials" class="mpwem-tab-pane">
                                <h2 class="mpwem-section-title"><?php esc_html_e( 'Video Learning Center', 'mage-eventpress' ); ?></h2>
                                <div class="mpwem-grid">
									<?php
									$videos = array(
										array( 'title' => __( 'Create an Event in 10 Minutes', 'mage-eventpress' ), 'id' => '6eu3MowK8CQ' ),
										array( 'title' => __( 'Event Manager Pro Features Overview', 'mage-eventpress' ), 'id' => '9ur9Mnq20mc', 'pro' => true ),
									);
									foreach ( $videos as $video ) :
										?>
                                        <div class="mpwem-card">
                                            <div class="mpwem-video-wrapper">
                                                <iframe src="https://www.youtube.com/embed/<?php echo esc_attr( $video['id'] ); ?>" frameborder="0" allowfullscreen loading="lazy" title="<?php echo esc_attr( $video['title'] ); ?>"></iframe>
                                            </div>
                                            <h3 style="font-size: 15px; margin-bottom: 0;">
												<?php echo esc_html( $video['title'] ); ?>
												<?php if ( ! empty( $video['pro'] ) ) { echo $pro_badge; } ?>
                                            </h3>
                                        </div>
									<?php endforeach; ?>
                                </div>
                                <div class="doc-tip" style="background:#e8f5e9;border-left:4px solid #4caf50;padding:12px 16px;border-radius:4px;margin:24px 0 0;font-size:13px;">
									<?php esc_html_e( 'Tip: More guides are available in the Documentation section under Help & Support.', 'mage-eventpress' ); ?>
                                </div>
                            </div>

                            <!-- Shortcodes -->
                            <div id="tab-shortcodes" class="mpwem-tab-pane">
                                <h2 class="mpwem-section-title"><?php esc_html_e( 'Available Shortcodes', 'mage-eventpress' ); ?></h2>
                                <p style="color:var(--mpwem-text-muted);margin-bottom:10px;"><?php esc_html_e( 'Place these shortcodes on any page or post to display events.', 'mage-eventpress' ); ?></p>
                                <table class="mpwem-data-table">
                                    <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Name', 'mage-eventpress' ); ?></th>
                                        <th><?php esc_html_e( 'Shortcode', 'mage-eventpress' ); ?></th>
                                        <th><?php esc_html_e( 'Parameters', 'mage-eventpress' ); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
									<?php
									$shortcodes = array(
										array(
											'name' => __( 'Events List / Grid', 'mage-eventpress' ),
											'code' => "[event-list show='8' style='grid' column='3']",
											'params' => __( 'show, style (grid|list|minimal|native|timeline|title|spring|winter), column, pagination, search-filter, cat, org, city, country', 'mage-eventpress' ),
											'demo' => 'https://event.mage-people.com/events-grid-style/',
										),
										array(
											'name' => __( 'Events with Search Filter', 'mage-eventpress' ),
											'code' => "[event-list search-filter='yes' column='4']",
											'params' => __( "search-filter='yes|no' — Default: no", 'mage-eventpress' ),
											'demo' => 'https://event.mage-people.com/events-list-style-with-search-box/',
										),
										array(
											'name' => __( 'Recurring Events', 'mage-eventpress' ),
											'code' => "[event-list-recurring column='3']",
											'params' => __( "column — 3 or 4 | Default: 3", 'mage-eventpress' ),
											'demo' => 'https://event.mage-people.com/recurring-events/',
										),
										array(
											'name' => __( 'Expired Events', 'mage-eventpress' ),
											'code' => '[expire-event-list]',
											'params' => __( 'No parameters required.', 'mage-eventpress' ),
											'demo' => 'https://event.mage-people.com/expired-events/',
										),
										array(
											'name' => __( 'Event Speakers', 'mage-eventpress' ),
											'code' => '[event-speaker-list event=14829]',
											'params' => __( 'event — Event ID (required)', 'mage-eventpress' ),
											'demo' => 'https://event.mage-people.com/speakers/',
										),
										array(
											'name' => __( 'Events City List', 'mage-eventpress' ),
											'code' => '[event-city-list]',
											'params' => __( 'No parameters required.', 'mage-eventpress' ),
											'demo' => 'https://event.mage-people.com/events-city-list/',
										),
										array(
											'name' => __( 'Single Event Registration', 'mage-eventpress' ),
											'code' => '[event-add-cart-section event=10408]',
											'params' => __( 'event — Event ID (required)', 'mage-eventpress' ),
											'demo' => 'https://event.mage-people.com/single-event-registration/',
										),
										array(
											'name' => __( 'Events Calendar', 'mage-eventpress' ),
											'code' => '[event-calendar]',
											'params' => __( 'No parameters required.', 'mage-eventpress' ),
											'demo' => 'https://event.mage-people.com/events-calendar/',
										),
										array(
											'name' => __( 'Events Calendar Pro', 'mage-eventpress' ),
											'code' => "[mep-event-calendar]<br>[mep-event-calendar cat_id='44']<br>[mep-event-calendar-month month='2028-09']",
											'params' => __( 'cat_id — Category ID; month — yyyy-mm', 'mage-eventpress' ),
											'demo' => 'https://event.mage-people.com/events-calendar-pro/',
											'addon' => true,
										),
									);
									foreach ( $shortcodes as $row ) :
										?>
                                        <tr>
                                            <td>
												<?php echo esc_html( $row['name'] ); ?>
												<?php if ( ! empty( $row['addon'] ) ) { echo $addon_badge; } ?>
												<?php if ( ! empty( $row['demo'] ) ) : ?>
                                                    <br><a class="mpwem-btn mpwem-btn-sm mpwem-btn-dark" href="<?php echo esc_url( $row['demo'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View Demo', 'mage-eventpress' ); ?></a>
												<?php endif; ?>
                                            </td>
                                            <td><?php echo wp_kses_post( '<code>' . str_replace( '<br>', '</code><br><code>', $row['code'] ) . '</code>' ); ?></td>
                                            <td><?php echo esc_html( $row['params'] ); ?></td>
                                        </tr>
									<?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Documents -->
                            <div id="tab-documents" class="mpwem-tab-pane">
                                <h2 class="mpwem-section-title"><?php esc_html_e( 'Documentation', 'mage-eventpress' ); ?></h2>
                                <p style="margin-bottom:20px;color:var(--mpwem-text-muted);"><?php esc_html_e( 'Select a topic below to learn about each section of the event settings.', 'mage-eventpress' ); ?></p>

                                <div class="mpwem-doc-tabs">
                                    <button type="button" class="mpwem-doc-tab-btn active" data-doc="doc-venue"><?php esc_html_e( 'Venue / Location', 'mage-eventpress' ); ?></button>
                                    <button type="button" class="mpwem-doc-tab-btn" data-doc="doc-ticket"><?php esc_html_e( 'Ticket & Pricing', 'mage-eventpress' ); ?></button>
                                    <button type="button" class="mpwem-doc-tab-btn" data-doc="doc-datetime"><?php esc_html_e( 'Date & Time', 'mage-eventpress' ); ?></button>
                                    <button type="button" class="mpwem-doc-tab-btn" data-doc="doc-settings"><?php esc_html_e( 'Event Settings', 'mage-eventpress' ); ?></button>
                                    <button type="button" class="mpwem-doc-tab-btn" data-doc="doc-faq-meta"><?php esc_html_e( 'Event FAQ', 'mage-eventpress' ); ?></button>
                                    <button type="button" class="mpwem-doc-tab-btn" data-doc="doc-tax"><?php esc_html_e( 'Tax', 'mage-eventpress' ); ?></button>
                                    <button type="button" class="mpwem-doc-tab-btn" data-doc="doc-seo"><?php esc_html_e( 'SEO Content', 'mage-eventpress' ); ?></button>
                                    <button type="button" class="mpwem-doc-tab-btn" data-doc="doc-email"><?php esc_html_e( 'Email Text', 'mage-eventpress' ); ?></button>
                                    <button type="button" class="mpwem-doc-tab-btn" data-doc="doc-template"><?php esc_html_e( 'Template', 'mage-eventpress' ); ?></button>
                                    <button type="button" class="mpwem-doc-tab-btn" data-doc="doc-speakers"><?php esc_html_e( 'Speakers', 'mage-eventpress' ); ?></button>
                                    <button type="button" class="mpwem-doc-tab-btn" data-doc="doc-timeline"><?php esc_html_e( 'Timeline', 'mage-eventpress' ); ?></button>
                                    <button type="button" class="mpwem-doc-tab-btn" data-doc="doc-gallery"><?php esc_html_e( 'Gallery', 'mage-eventpress' ); ?></button>
                                    <button type="button" class="mpwem-doc-tab-btn" data-doc="doc-global"><?php esc_html_e( 'Global Settings', 'mage-eventpress' ); ?></button>
                                    <button type="button" class="mpwem-doc-tab-btn doc-tab-pro" data-doc="doc-attendee"><?php esc_html_e( 'Attendee Form', 'mage-eventpress' ); ?> <?php echo $pro_badge; ?></button>
                                    <button type="button" class="mpwem-doc-tab-btn doc-tab-pro" data-doc="doc-pdf"><?php esc_html_e( 'PDF Tickets', 'mage-eventpress' ); ?> <?php echo $pro_badge; ?></button>
                                    <button type="button" class="mpwem-doc-tab-btn" data-doc="doc-faq"><?php esc_html_e( 'FAQ', 'mage-eventpress' ); ?></button>
                                </div>

                                <div id="doc-venue" class="mpwem-doc-panel active">
                                    <h3><i class="fas fa-map-marker-alt"></i> <?php esc_html_e( 'Venue / Location', 'mage-eventpress' ); ?></h3>
                                    <p><?php esc_html_e( 'Configure where the event takes place — offline, online, or hybrid.', 'mage-eventpress' ); ?></p>
                                    <ul>
                                        <li><strong><?php esc_html_e( 'Event Type', 'mage-eventpress' ); ?></strong> — <?php esc_html_e( 'Choose Offline (physical), Online (virtual), or Hybrid.', 'mage-eventpress' ); ?></li>
                                        <li><strong><?php esc_html_e( 'Venue Details', 'mage-eventpress' ); ?></strong> — <?php esc_html_e( 'Set venue name, street, city, state, postcode, and country.', 'mage-eventpress' ); ?></li>
                                        <li><strong><?php esc_html_e( 'Map Coordinates', 'mage-eventpress' ); ?></strong> — <?php esc_html_e( 'Add latitude/longitude or use Google Maps integration to help attendees find the location.', 'mage-eventpress' ); ?></li>
                                    </ul>
                                    <div class="doc-tip"><?php esc_html_e( 'Tip: For virtual events, use the Virtual template and hide location sections from list/details pages in Global Settings.', 'mage-eventpress' ); ?></div>
                                </div>

                                <div id="doc-ticket" class="mpwem-doc-panel">
                                    <h3><i class="fas fa-ticket-alt"></i> <?php esc_html_e( 'Ticket & Pricing', 'mage-eventpress' ); ?></h3>
                                    <p><?php esc_html_e( 'Create ticket types, set quantities, prices, and extra services.', 'mage-eventpress' ); ?></p>
                                    <ul>
                                        <li><strong><?php esc_html_e( 'Ticket Types', 'mage-eventpress' ); ?></strong> — <?php esc_html_e( 'Add Adult, Child, VIP, or any custom ticket name with its own price and quantity.', 'mage-eventpress' ); ?></li>
                                        <li><strong><?php esc_html_e( 'Extra Services', 'mage-eventpress' ); ?></strong> — <?php esc_html_e( 'Offer paid add-ons (lunch, parking, merchandise) during booking.', 'mage-eventpress' ); ?></li>
                                        <li><strong><?php esc_html_e( 'RSVP Mode', 'mage-eventpress' ); ?></strong> — <?php esc_html_e( 'Turn the event into a free RSVP instead of paid ticketing.', 'mage-eventpress' ); ?></li>
                                    </ul>
                                    <div class="doc-note"><?php esc_html_e( 'Note: Ticket prices are tax-exclusive when WooCommerce tax is enabled — WooCommerce applies tax at checkout.', 'mage-eventpress' ); ?></div>
                                </div>

                                <div id="doc-datetime" class="mpwem-doc-panel">
                                    <h3><i class="fas fa-calendar-alt"></i> <?php esc_html_e( 'Date & Time', 'mage-eventpress' ); ?></h3>
                                    <p><?php esc_html_e( 'Control when the event runs — single date, multiple dates, or recurring schedule.', 'mage-eventpress' ); ?></p>
                                    <ul>
                                        <li><strong><?php esc_html_e( 'Start / End', 'mage-eventpress' ); ?></strong> — <?php esc_html_e( 'Set the primary event start and end date & time.', 'mage-eventpress' ); ?></li>
                                        <li><strong><?php esc_html_e( 'Multiple Dates', 'mage-eventpress' ); ?></strong> — <?php esc_html_e( 'Add extra fixed dates so customers can pick their preferred session.', 'mage-eventpress' ); ?></li>
                                        <li><strong><?php esc_html_e( 'Recurring Events', 'mage-eventpress' ); ?></strong> — <?php esc_html_e( 'Configure daily, weekly, or custom repeating schedules with time slots.', 'mage-eventpress' ); ?></li>
                                    </ul>
                                </div>

                                <div id="doc-settings" class="mpwem-doc-panel">
                                    <h3><i class="fas fa-cog"></i> <?php esc_html_e( 'Event Settings', 'mage-eventpress' ); ?></h3>
                                    <p><?php esc_html_e( 'Per-event display and behavior options such as SKU, seat left display, and registration controls.', 'mage-eventpress' ); ?></p>
                                    <ul>
                                        <li><strong><?php esc_html_e( 'Registration Toggle', 'mage-eventpress' ); ?></strong> — <?php esc_html_e( 'Enable or disable ticket registration for this event.', 'mage-eventpress' ); ?></li>
                                        <li><strong><?php esc_html_e( 'Display Options', 'mage-eventpress' ); ?></strong> — <?php esc_html_e( 'Control what attendees see on the event page (remaining seats, dates, etc.).', 'mage-eventpress' ); ?></li>
                                    </ul>
                                </div>

                                <div id="doc-faq-meta" class="mpwem-doc-panel">
                                    <h3><i class="fas fa-question-circle"></i> <?php esc_html_e( 'Event FAQ', 'mage-eventpress' ); ?></h3>
                                    <p><?php esc_html_e( 'Add frequently asked questions that appear on the single event page to reduce support load.', 'mage-eventpress' ); ?></p>
                                </div>

                                <div id="doc-tax" class="mpwem-doc-panel">
                                    <h3><i class="fas fa-percentage"></i> <?php esc_html_e( 'Tax Configuration', 'mage-eventpress' ); ?></h3>
                                    <p><?php esc_html_e( 'Available when WooCommerce tax calculation is enabled. Configure tax class for this event’s tickets.', 'mage-eventpress' ); ?></p>
                                    <div class="doc-tip"><?php esc_html_e( 'Tip: Prefer global WooCommerce → Settings → Tax rules unless a specific event needs a different tax class.', 'mage-eventpress' ); ?></div>
                                </div>

                                <div id="doc-seo" class="mpwem-doc-panel">
                                    <h3><i class="fas fa-search"></i> <?php esc_html_e( 'SEO Content', 'mage-eventpress' ); ?></h3>
                                    <p><?php esc_html_e( 'Add rich-text / schema-friendly content to help event pages rank better in search engines.', 'mage-eventpress' ); ?></p>
                                </div>

                                <div id="doc-email" class="mpwem-doc-panel">
                                    <h3><i class="fas fa-envelope"></i> <?php esc_html_e( 'Email Text', 'mage-eventpress' ); ?></h3>
                                    <p><?php esc_html_e( 'Customize confirmation email text for this event. Global From Name / From Email are set under Events → Settings.', 'mage-eventpress' ); ?></p>
                                </div>

                                <div id="doc-template" class="mpwem-doc-panel">
                                    <h3><i class="fas fa-th-large"></i> <?php esc_html_e( 'Template', 'mage-eventpress' ); ?></h3>
                                    <p><?php esc_html_e( 'Choose a single-event page template (default, virtual, or other bundled layouts). You can also override templates in your theme.', 'mage-eventpress' ); ?></p>
                                </div>

                                <div id="doc-speakers" class="mpwem-doc-panel">
                                    <h3><i class="fas fa-microphone"></i> <?php esc_html_e( 'Speaker Information', 'mage-eventpress' ); ?></h3>
                                    <p><?php esc_html_e( 'Attach speakers to the event and display them with [event-speaker-list event=ID].', 'mage-eventpress' ); ?></p>
                                </div>

                                <div id="doc-timeline" class="mpwem-doc-panel">
                                    <h3><i class="fas fa-stream"></i> <?php esc_html_e( 'Timeline Details', 'mage-eventpress' ); ?></h3>
                                    <p><?php esc_html_e( 'Build an agenda / schedule timeline that shows sessions throughout the event day.', 'mage-eventpress' ); ?></p>
                                </div>

                                <div id="doc-gallery" class="mpwem-doc-panel">
                                    <h3><i class="fas fa-images"></i> <?php esc_html_e( 'Gallery', 'mage-eventpress' ); ?></h3>
                                    <p><?php esc_html_e( 'Upload gallery images for the event detail page slider. Reorder by dragging; the first image is often used as the featured visual.', 'mage-eventpress' ); ?></p>
                                </div>

                                <div id="doc-global" class="mpwem-doc-panel">
                                    <h3><i class="fas fa-sliders-h"></i> <?php esc_html_e( 'Global Settings', 'mage-eventpress' ); ?></h3>
                                    <p><?php esc_html_e( 'These settings apply to all events under Events → Settings.', 'mage-eventpress' ); ?></p>
                                    <ul>
                                        <li><strong><?php esc_html_e( 'General', 'mage-eventpress' ); ?></strong> — <?php esc_html_e( 'Events label, URL slug, expiry rules, email from name/address.', 'mage-eventpress' ); ?></li>
                                        <li><strong><?php esc_html_e( 'Style / Display', 'mage-eventpress' ); ?></strong> — <?php esc_html_e( 'Theme colors and which sections appear on list & details pages.', 'mage-eventpress' ); ?></li>
                                        <li><strong><?php esc_html_e( 'Permalink note', 'mage-eventpress' ); ?></strong> — <?php esc_html_e( 'After changing the slug, go to Settings → Permalinks and click Save to avoid 404 errors.', 'mage-eventpress' ); ?></li>
                                    </ul>
                                </div>

                                <div id="doc-attendee" class="mpwem-doc-panel">
                                    <div class="doc-pro-section">
                                        <h3><i class="fas fa-clipboard-list"></i> <?php esc_html_e( 'Attendee Registration Form', 'mage-eventpress' ); ?> <?php echo $pro_badge; ?></h3>
                                        <p><?php esc_html_e( 'Build custom attendee forms with conditional logic. Available with the Form Builder / PRO features.', 'mage-eventpress' ); ?></p>
                                        <ul>
                                            <li><?php esc_html_e( 'Edit any event → scroll to Attendee Registration Form.', 'mage-eventpress' ); ?></li>
                                            <li><?php esc_html_e( 'Add text, select, checkbox, and other field types.', 'mage-eventpress' ); ?></li>
                                            <li><?php esc_html_e( 'Apply conditions (e.g. show School Name only for Child tickets).', 'mage-eventpress' ); ?></li>
                                        </ul>
                                    </div>
                                </div>

                                <div id="doc-pdf" class="mpwem-doc-panel">
                                    <div class="doc-pro-section">
                                        <h3><i class="fas fa-file-pdf"></i> <?php esc_html_e( 'PDF Tickets & Email', 'mage-eventpress' ); ?> <?php echo $pro_badge; ?></h3>
                                        <p><?php esc_html_e( 'Generate PDF tickets and email them after payment (Processing / Completed order statuses).', 'mage-eventpress' ); ?></p>
                                        <ul>
                                            <li><?php esc_html_e( 'Configure PDF & email under Events → Settings → PDF / Email tabs.', 'mage-eventpress' ); ?></li>
                                            <li><?php esc_html_e( 'Export attendees as CSV from the Attendee List menu.', 'mage-eventpress' ); ?></li>
                                        </ul>
                                        <div class="doc-note"><?php esc_html_e( 'Note: PDF emails are sent only when the order status is Processing or Completed (after payment).', 'mage-eventpress' ); ?></div>
                                    </div>
                                </div>

                                <div id="doc-faq" class="mpwem-doc-panel">
                                    <h3><i class="fas fa-comments"></i> <?php esc_html_e( 'Frequently Asked Questions', 'mage-eventpress' ); ?></h3>
									<?php foreach ( $this->faq_array() as $key => $faq ) : ?>
                                        <div class="mpwem-faq-item">
                                            <div class="mpwem-faq-q"><i class="fas fa-plus"></i> <?php echo esc_html( $faq['title'] ); ?></div>
                                            <div class="mpwem-faq-a"><?php echo esc_html( $faq['des'] ); ?></div>
                                        </div>
									<?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Addons -->
                            <div id="tab-addons" class="mpwem-tab-pane">
                                <h2 class="mpwem-section-title"><?php esc_html_e( 'Available Addons', 'mage-eventpress' ); ?></h2>
                                <p style="color:var(--mpwem-text-muted);"><?php esc_html_e( 'Extend Event Manager with these powerful addons.', 'mage-eventpress' ); ?></p>
                                <div class="mpwem-grid">
									<?php foreach ( $this->get_addons_list() as $addon ) : ?>
                                        <div class="mpwem-card">
                                            <div class="mpwem-card-icon"><i class="<?php echo esc_attr( $addon['icon'] ); ?>"></i></div>
                                            <h3 style="font-size:16px;"><?php echo esc_html( $addon['name'] ); ?></h3>
                                            <p style="font-size:13px;min-height:60px;"><?php echo esc_html( $addon['description'] ); ?></p>
                                            <div style="margin-top:16px;">
                                                <a href="<?php echo esc_url( $addon['link'] ); ?>" class="mpwem-btn mpwem-btn-sm" target="_blank" rel="noopener noreferrer">
													<?php esc_html_e( 'View Details', 'mage-eventpress' ); ?>
                                                </a>
                                            </div>
                                        </div>
									<?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Support -->
                            <div id="tab-support" class="mpwem-tab-pane">
                                <h2 class="mpwem-section-title"><?php esc_html_e( 'Help & Support', 'mage-eventpress' ); ?></h2>
                                <div class="mpwem-grid" style="grid-template-columns: 1fr 1fr;">
                                    <div class="mpwem-card">
                                        <h3><i class="fas fa-book-open"></i> <?php esc_html_e( 'Documentation', 'mage-eventpress' ); ?></h3>
                                        <p><?php esc_html_e( 'Explore detailed guides and the knowledge base for advanced configurations.', 'mage-eventpress' ); ?></p>
                                        <div style="margin-top: 20px;">
                                            <a href="https://docs.mage-people.com/plugins/wpevently/overview" class="mpwem-btn" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Read Documentation', 'mage-eventpress' ); ?></a>
                                        </div>
                                    </div>
                                    <div class="mpwem-card">
                                        <h3><i class="fas fa-life-ring"></i> <?php esc_html_e( 'Technical Support', 'mage-eventpress' ); ?></h3>
                                        <p><?php esc_html_e( 'Stuck? Our expert developers are here to help you solve any technical issues.', 'mage-eventpress' ); ?></p>
                                        <div style="margin-top: 20px;">
                                            <a href="https://support.mage-people.com/portal/en/newticket" class="mpwem-btn mpwem-btn-blue" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open Support Ticket', 'mage-eventpress' ); ?></a>
                                        </div>
                                    </div>
                                    <div class="mpwem-card">
                                        <h3><i class="fas fa-globe"></i> <?php esc_html_e( 'Live Demo', 'mage-eventpress' ); ?></h3>
                                        <p><?php esc_html_e( 'Try Evently on the live demo site before configuring your own events.', 'mage-eventpress' ); ?></p>
                                        <div style="margin-top: 20px;">
                                            <a href="https://wpevently.com/" class="mpwem-btn mpwem-btn-dark" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View Demo', 'mage-eventpress' ); ?></a>
                                        </div>
                                    </div>
                                    <div class="mpwem-card">
                                        <h3><i class="fas fa-crown"></i> <?php esc_html_e( 'Upgrade to PRO', 'mage-eventpress' ); ?></h3>
                                        <p><?php esc_html_e( 'Unlock PDF tickets, attendee forms, CSV export, reports, and more.', 'mage-eventpress' ); ?></p>
                                        <div style="margin-top: 20px;">
                                            <a href="https://mage-people.com/product/mage-woo-event-booking-manager-pro/" class="mpwem-btn pro-btn" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Buy PRO', 'mage-eventpress' ); ?></a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <script type="text/javascript">
                    jQuery(document).ready(function ($) {
                        $('.mpwem-tab-link').on('click', function () {
                            var tab_id = $(this).attr('data-tab');
                            $('.mpwem-tab-link').removeClass('active');
                            $('.mpwem-tab-pane').removeClass('active');
                            $(this).addClass('active');
                            $('#' + tab_id).addClass('active');
                        });

                        $('.mpwem-doc-tab-btn').on('click', function () {
                            var doc_id = $(this).attr('data-doc');
                            $('.mpwem-doc-tab-btn').removeClass('active');
                            $('.mpwem-doc-panel').removeClass('active');
                            $(this).addClass('active');
                            $('#' + doc_id).addClass('active');
                        });

                        $('.mpwem-faq-q').on('click', function () {
                            var $item = $(this).closest('.mpwem-faq-item');
                            var $icon = $(this).find('i');
                            $item.toggleClass('open');
                            $icon.toggleClass('fa-plus fa-minus');
                        });
                    });
                </script>
				<?php
			}

			public function get_addons_list() {
				return array(
					array(
						'name'        => __( 'Global/Common Qty Addon', 'mage-eventpress' ),
						'description' => __( 'Manage ticket quantity as a shared/global pool across ticket types.', 'mage-eventpress' ),
						'icon'        => 'fas fa-layer-group',
						'link'        => 'https://mage-people.com/product/global-common-qty-addon-for-event-manager/#mage_product_price',
					),
					array(
						'name'        => __( 'Event Max-Min Quantity Limiting Addon', 'mage-eventpress' ),
						'description' => __( 'Set minimum and maximum purchase quantities per ticket type.', 'mage-eventpress' ),
						'icon'        => 'fas fa-sort-numeric-up',
						'link'        => 'https://mage-people.com/product/event-max-min-quantity-limiting-addon-for-woocommerce-event-manager/#mage_product_price',
					),
					array(
						'name'        => __( 'Marketplace / Event Frontend Submit Addon', 'mage-eventpress' ),
						'description' => __( 'Let organizers submit and manage events from the frontend marketplace.', 'mage-eventpress' ),
						'icon'        => 'fas fa-store',
						'link'        => 'https://mage-people.com/product/event-frontend-submit-addon-for-event-manager/#mage_product_price',
					),
					array(
						'name'        => __( 'WooCommerce Event QR Code Addon', 'mage-eventpress' ),
						'description' => __( 'Generate and scan QR codes to validate tickets at the door.', 'mage-eventpress' ),
						'icon'        => 'fas fa-qrcode',
						'link'        => 'https://mage-people.com/product/woocommerce-event-qr-code-addon/#mage_product_price',
					),
					array(
						'name'        => __( 'WooCommerce Event Calendar Addon', 'mage-eventpress' ),
						'description' => __( 'Display events in a polished calendar view for easier browsing.', 'mage-eventpress' ),
						'icon'        => 'fas fa-calendar-alt',
						'link'        => 'https://mage-people.com/product/woocommerce-event-calendar-addon/#mage_product_price',
					),
					array(
						'name'        => __( 'Book an Event From Dashboard', 'mage-eventpress' ),
						'description' => __( 'Create offline / phone bookings and add attendees from the admin dashboard.', 'mage-eventpress' ),
						'icon'        => 'fas fa-user-plus',
						'link'        => 'https://mage-people.com/product/woocommerce-event-book-an-event-from-dashboard/#mage_product_price',
					),
					array(
						'name'        => __( 'Email Reminder Addon', 'mage-eventpress' ),
						'description' => __( 'Send automated reminder emails before the event starts.', 'mage-eventpress' ),
						'icon'        => 'fas fa-envelope',
						'link'        => 'https://mage-people.com/product/event-email-reminder-addon/#mage_product_price',
					),
					array(
						'name'        => __( 'Early Bird Ticketing Discount Addon', 'mage-eventpress' ),
						'description' => __( 'Offer date-based early bird discounts to boost early sales.', 'mage-eventpress' ),
						'icon'        => 'fas fa-percentage',
						'link'        => 'https://mage-people.com/product/early-bird-pricing-addon-for-event-manager/#mage_product_price',
					),
					array(
						'name'        => __( 'WooCommerce Event Waitlist Addon', 'mage-eventpress' ),
						'description' => __( 'Collect waitlist subscriptions when tickets sell out.', 'mage-eventpress' ),
						'icon'        => 'fas fa-clock',
						'link'        => 'https://mage-people.com/product/woocommerce-event-waitlist-addon/#mage_product_price',
					),
					array(
						'name'        => __( 'Event Seat Plan Addon', 'mage-eventpress' ),
						'description' => __( 'Let customers pick seats from an interactive seat map.', 'mage-eventpress' ),
						'icon'        => 'fas fa-chair',
						'link'        => 'https://mage-people.com/product/seat-plan-addon-for-event-manager/#mage_product_price',
					),
					array(
						'name'        => __( 'Membership Price Addon', 'mage-eventpress' ),
						'description' => __( 'Offer role-based membership pricing for tickets.', 'mage-eventpress' ),
						'icon'        => 'fas fa-users',
						'link'        => 'https://mage-people.com/product/membership-pricing-for-event-manager-plugin/#mage_product_price',
					),
					array(
						'name'        => __( 'WooCommerce Events Duplicator Addon', 'mage-eventpress' ),
						'description' => __( 'Duplicate existing events with all settings to save setup time.', 'mage-eventpress' ),
						'icon'        => 'fas fa-copy',
						'link'        => 'https://mage-people.com/product/woocommerce-event-duplicator-addon/#mage_product_price',
					),
					array(
						'name'        => __( 'WooCommerce Event Coupon Code Addon', 'mage-eventpress' ),
						'description' => __( 'Create event-specific coupon codes for marketing campaigns.', 'mage-eventpress' ),
						'icon'        => 'fas fa-ticket-alt',
						'link'        => 'https://mage-people.com/product/woocommerce-event-coupon-code-addon/',
					),
					array(
						'name'        => __( 'Review and Rating Addon', 'mage-eventpress' ),
						'description' => __( 'Collect attendee reviews and ratings after events.', 'mage-eventpress' ),
						'icon'        => 'fas fa-star',
						'link'        => 'https://mage-people.com/product/review-and-rating-addon-for-event-manager/#mage_product_price',
					),
					array(
						'name'        => __( 'Mage WP Login Page Designer', 'mage-eventpress' ),
						'description' => __( 'Design a professional custom WordPress login page.', 'mage-eventpress' ),
						'icon'        => 'fas fa-sign-in-alt',
						'link'        => 'https://mage-people.com/product/mage-wp-login-page-designer/',
					),
				);
			}

			public function faq_array() {
				return array(
					1  => array(
						'title' => __( 'Where can I find the Attendee registration Form?', 'mage-eventpress' ),
						'des'   => __( 'To enable attendee form you must first install a premium addon name “Form Builder”. Once you are done with installing – Click on “Events” -> Click on “All Events” -> Click on Edit of any existing event -> Scroll down below to find "Attendee Registration Form"', 'mage-eventpress' ),
					),
					2  => array(
						'title' => __( 'How can I see event wise registered attendee list?', 'mage-eventpress' ),
						'des'   => __( 'If you visit attendee list menu in event section then you will see all attendee list here. You can filter choosing event name and date if event is recurring event.', 'mage-eventpress' ),
					),
					3  => array(
						'title' => __( 'How can I Export attendee list as CSV?', 'mage-eventpress' ),
						'des'   => __( 'If you visit attendee list menu in event section then you will see all attendee list here. You can filter choosing event name and date if event is recurring event. After filtering right section there is 2 button to export attendee and extra service.', 'mage-eventpress' ),
					),
					4  => array(
						'title' => __( 'My plugin page shows 404 error?', 'mage-eventpress' ),
						'des'   => __( 'Please re-save the permalink to solve the problem.', 'mage-eventpress' ),
					),
					5  => array(
						'title' => __( 'Where Can I change Event Slug Url?', 'mage-eventpress' ),
						'des'   => __( 'In Event Settings area we have slug changing option. You can change it and resave permalink to avoid 404 error.', 'mage-eventpress' ),
					),
					6  => array(
						'title' => __( 'Where Can I configure Pdf Email?', 'mage-eventpress' ),
						'des'   => __( 'If you visit Event settings page then You will see PDF email tab top right, you can configure pdf email here.', 'mage-eventpress' ),
					),
					7  => array(
						'title' => __( 'I have configured correctly but pdf email I am not getting.', 'mage-eventpress' ),
						'des'   => __( 'PDF email with pdf send based on some configuration. If order status processing or complete then only pdf email will send as we considered these 2 order status come after order payment done. If order status holds or pending, then email of pdf will not send.', 'mage-eventpress' ),
					),
					8  => array(
						'title' => __( 'Can I hide any section from event list and details page?', 'mage-eventpress' ),
						'des'   => __( 'Yes You can hide any section from event list and details page. If you go event settings area in general section, you will find lots of settings regarding all section.', 'mage-eventpress' ),
					),
					9  => array(
						'title' => __( 'How Can I configure Virtual Event?', 'mage-eventpress' ),
						'des'   => __( 'For virtual event we know there should not have any location or physical address so we recommend to use template virtual that we have during event adding time and also you can use location hide settings from list and details page.', 'mage-eventpress' ),
					),
					10 => array(
						'title' => __( 'I installed event manager plugin but it does not work?', 'mage-eventpress' ),
						'des'   => __( 'Please install WooCommerce plugin first, before installing any plugin.', 'mage-eventpress' ),
					),
					11 => array(
						'title' => __( 'Do you offer customization?', 'mage-eventpress' ),
						'des'   => __( 'Yes! we offer customization service for our client. If you want any new features don’t hesitate to contact us. Email: magepeopleteam@gmail.com.', 'mage-eventpress' ),
					),
				);
			}
		}
		new MPWEM_Welcome();
	}
