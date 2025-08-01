<?php
$currency_symbol = get_woocommerce_currency_symbol();
$nonce = wp_create_nonce('mpwem_analytics_nonce');

// Get all categories for filter
$categories = get_terms(array(
    'taxonomy' => 'mep_cat',
    'hide_empty' => false,
));

// Get all events for filter
$events = get_posts(array(
    'post_type' => 'mep_events',
    'post_status' => 'publish',
    'numberposts' => -1
));
?>

<div class="wrap mpwem-analytics-dashboard">
    <div class="mpwem-analytics-header">
        <h1 class="wp-heading-inline">
            <i class="dashicons dashicons-chart-area"></i>
            <?php esc_html_e('Analytics Dashboard', 'mage-eventpress'); ?>
        </h1>
        <div class="mpwem-analytics-actions">
            <button id="mpwem-export-btn" class="button button-secondary">
                <i class="dashicons dashicons-download"></i>
                <?php esc_html_e('Export Data', 'mage-eventpress'); ?>
            </button>
            <button id="mpwem-refresh-btn" class="button button-primary">
                <i class="dashicons dashicons-update"></i>
                <?php esc_html_e('Refresh', 'mage-eventpress'); ?>
            </button>

        </div>
    </div>

    <!-- Filters Section -->
    <div class="mpwem-analytics-filters">
        <div class="mpwem-filter-row">
            <div class="mpwem-filter-group">
                <label for="mpwem-date-from"><?php esc_html_e('Date From:', 'mage-eventpress'); ?></label>
                <input type="date" id="mpwem-date-from" value="<?php echo date('Y-m-01'); ?>">
            </div>
            <div class="mpwem-filter-group">
                <label for="mpwem-date-to"><?php esc_html_e('Date To:', 'mage-eventpress'); ?></label>
                <input type="date" id="mpwem-date-to" value="<?php echo date('Y-m-t'); ?>">
            </div>
            <div class="mpwem-filter-group">
                <label for="mpwem-category-filter"><?php esc_html_e('Category:', 'mage-eventpress'); ?></label>
                <select id="mpwem-category-filter">
                    <option value=""><?php esc_html_e('All Categories', 'mage-eventpress'); ?></option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo esc_attr($category->slug); ?>">
                            <?php echo esc_html($category->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mpwem-filter-group">
                <label for="mpwem-event-filter"><?php esc_html_e('Event:', 'mage-eventpress'); ?></label>
                <select id="mpwem-event-filter">
                    <option value=""><?php esc_html_e('All Events', 'mage-eventpress'); ?></option>
                    <?php foreach ($events as $event): ?>
                        <option value="<?php echo esc_attr($event->ID); ?>">
                            <?php echo esc_html($event->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mpwem-filter-group">
                <button id="mpwem-apply-filters" class="button button-primary">
                    <?php esc_html_e('Apply Filters', 'mage-eventpress'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div id="mpwem-loading" class="mpwem-loading" style="display: none;">
        <div class="mpwem-spinner"></div>
        <p><?php esc_html_e('Loading analytics data...', 'mage-eventpress'); ?></p>
    </div>

    <!-- Summary Cards -->
    <div class="mpwem-analytics-summary" id="mpwem-summary-cards">
        <div class="mpwem-summary-card revenue">
            <div class="mpwem-card-icon">
                <i class="dashicons dashicons-money-alt"></i>
            </div>
            <div class="mpwem-card-content">
                <h3><?php esc_html_e('Total Revenue', 'mage-eventpress'); ?></h3>
                <div class="mpwem-card-value" id="total-revenue">
                    <?php echo $currency_symbol; ?>0.00
                </div>
                <div class="mpwem-card-change" id="revenue-change"></div>
            </div>
        </div>

        <div class="mpwem-summary-card orders">
            <div class="mpwem-card-icon">
                <i class="dashicons dashicons-cart"></i>
            </div>
            <div class="mpwem-card-content">
                <h3><?php esc_html_e('Total Orders', 'mage-eventpress'); ?></h3>
                <div class="mpwem-card-value" id="total-orders">0</div>
                <div class="mpwem-card-change" id="orders-change"></div>
            </div>
        </div>

        <div class="mpwem-summary-card tickets">
            <div class="mpwem-card-icon">
                <i class="dashicons dashicons-tickets-alt"></i>
            </div>
            <div class="mpwem-card-content">
                <h3><?php esc_html_e('Tickets Sold', 'mage-eventpress'); ?></h3>
                <div class="mpwem-card-value" id="total-tickets">0</div>
                <div class="mpwem-card-change" id="tickets-change"></div>
            </div>
        </div>

        <div class="mpwem-summary-card average">
            <div class="mpwem-card-icon">
                <i class="dashicons dashicons-chart-line"></i>
            </div>
            <div class="mpwem-card-content">
                <h3><?php esc_html_e('Avg Order Value', 'mage-eventpress'); ?></h3>
                <div class="mpwem-card-value" id="average-order">
                    <?php echo $currency_symbol; ?>0.00
                </div>
                <div class="mpwem-card-change" id="average-change"></div>
            </div>
        </div>

        <div class="mpwem-summary-card refunds">
            <div class="mpwem-card-icon">
                <i class="dashicons dashicons-undo"></i>
            </div>
            <div class="mpwem-card-content">
                <h3><?php esc_html_e('Refunds', 'mage-eventpress'); ?></h3>
                <div class="mpwem-card-value" id="total-refunds">
                    <?php echo $currency_symbol; ?>0.00
                </div>
                <div class="mpwem-card-change negative" id="refunds-change"></div>
            </div>
        </div>

        <div class="mpwem-summary-card profit">
            <div class="mpwem-card-icon">
                <i class="dashicons dashicons-chart-pie"></i>
            </div>
            <div class="mpwem-card-content">
                <h3><?php esc_html_e('Net Revenue', 'mage-eventpress'); ?></h3>
                <div class="mpwem-card-value" id="net-revenue">
                    <?php echo $currency_symbol; ?>0.00
                </div>
                <div class="mpwem-card-change" id="profit-change"></div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="mpwem-analytics-charts">
        <div class="mpwem-chart-container">
            <div class="mpwem-chart-header">
                <h3><?php esc_html_e('Revenue Trends', 'mage-eventpress'); ?></h3>
                <div class="mpwem-chart-controls">
                    <select id="mpwem-chart-period">
                        <option value="daily"><?php esc_html_e('Daily', 'mage-eventpress'); ?></option>
                        <option value="weekly"><?php esc_html_e('Weekly', 'mage-eventpress'); ?></option>
                        <option value="monthly"><?php esc_html_e('Monthly', 'mage-eventpress'); ?></option>
                    </select>
                    <select id="mpwem-chart-type">
                        <option value="revenue"><?php esc_html_e('Revenue', 'mage-eventpress'); ?></option>
                        <option value="orders"><?php esc_html_e('Orders', 'mage-eventpress'); ?></option>
                        <option value="tickets"><?php esc_html_e('Tickets', 'mage-eventpress'); ?></option>
                    </select>
                </div>
            </div>
            <div class="mpwem-chart-wrapper">
                <canvas id="mpwem-revenue-chart"></canvas>
            </div>
        </div>
    </div>

    <!-- Pie Charts Grid Section -->
    <div class="mpwem-analytics-pie-charts">
        <div class="mpwem-pie-charts-header">
            <h3><?php esc_html_e('Data Breakdown', 'mage-eventpress'); ?></h3>
        </div>
        <div class="mpwem-pie-charts-grid">
            <!-- Revenue by Category Pie Chart -->
            <div class="mpwem-pie-chart-container">
                <div class="mpwem-pie-chart-header">
                    <h4><?php esc_html_e('Revenue by Category', 'mage-eventpress'); ?></h4>
                </div>
                <div class="mpwem-pie-chart-wrapper">
                    <canvas id="mpwem-revenue-category-pie"></canvas>
                </div>
            </div>

            <!-- Revenue by Event Pie Chart -->
            <div class="mpwem-pie-chart-container">
                <div class="mpwem-pie-chart-header">
                    <h4><?php esc_html_e('Revenue by Event', 'mage-eventpress'); ?></h4>
                </div>
                <div class="mpwem-pie-chart-wrapper">
                    <canvas id="mpwem-revenue-event-pie"></canvas>
                </div>
            </div>

            <!-- Tickets by Category Pie Chart -->
            <div class="mpwem-pie-chart-container">
                <div class="mpwem-pie-chart-header">
                    <h4><?php esc_html_e('Tickets by Category', 'mage-eventpress'); ?></h4>
                </div>
                <div class="mpwem-pie-chart-wrapper">
                    <canvas id="mpwem-tickets-category-pie"></canvas>
                </div>
            </div>

            <!-- Orders by Status Pie Chart -->
            <div class="mpwem-pie-chart-container">
                <div class="mpwem-pie-chart-header">
                    <h4><?php esc_html_e('Orders by Status', 'mage-eventpress'); ?></h4>
                </div>
                <div class="mpwem-pie-chart-wrapper">
                    <canvas id="mpwem-orders-status-pie"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables -->
    <div class="mpwem-analytics-tables">
        <div class="mpwem-table-row">
            <!-- Event Performance Table -->
            <div class="mpwem-table-container">
                <div class="mpwem-table-header">
                    <h3><?php esc_html_e('Event Performance', 'mage-eventpress'); ?></h3>
                    <div class="mpwem-table-actions">
                        <input type="text" id="mpwem-event-search" placeholder="<?php esc_attr_e('Search events...', 'mage-eventpress'); ?>">
                    </div>
                </div>
                <div class="mpwem-table-wrapper">
                    <table id="mpwem-events-table" class="mpwem-data-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Event Name', 'mage-eventpress'); ?></th>
                                <th><?php esc_html_e('Revenue', 'mage-eventpress'); ?></th>
                                <th><?php esc_html_e('Tickets Sold', 'mage-eventpress'); ?></th>
                                <th><?php esc_html_e('Orders', 'mage-eventpress'); ?></th>
                                <th><?php esc_html_e('Avg. Order Value', 'mage-eventpress'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="mpwem-events-tbody">
                            <!-- Data will be populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Category Performance Table -->
            <div class="mpwem-table-container">
                <div class="mpwem-table-header">
                    <h3><?php esc_html_e('Category Performance', 'mage-eventpress'); ?></h3>
                    <div class="mpwem-table-actions">
                        <input type="text" id="mpwem-category-search" placeholder="<?php esc_attr_e('Search categories...', 'mage-eventpress'); ?>">
                    </div>
                </div>
                <div class="mpwem-table-wrapper">
                    <table id="mpwem-categories-table" class="mpwem-data-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Category', 'mage-eventpress'); ?></th>
                                <th><?php esc_html_e('Revenue', 'mage-eventpress'); ?></th>
                                <th><?php esc_html_e('Tickets Sold', 'mage-eventpress'); ?></th>
                                <th><?php esc_html_e('Events Count', 'mage-eventpress'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="mpwem-categories-tbody">
                            <!-- Data will be populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Modal -->
    <div id="mpwem-export-modal" class="mpwem-modal" style="display: none;">
        <div class="mpwem-modal-content">
            <div class="mpwem-modal-header">
                <h3><?php esc_html_e('Export Analytics Data', 'mage-eventpress'); ?></h3>
                <span class="mpwem-modal-close">&times;</span>
            </div>
            <div class="mpwem-modal-body">
                <div class="mpwem-export-options">
                    <label>
                        <input type="radio" name="export-format" value="csv" checked>
                        <?php esc_html_e('CSV Format', 'mage-eventpress'); ?>
                    </label>
                    <label>
                        <input type="radio" name="export-format" value="pdf">
                        <?php esc_html_e('PDF Format', 'mage-eventpress'); ?>
                    </label>
                </div>
                <div class="mpwem-export-date-range">
                    <label for="export-date-from"><?php esc_html_e('Date From:', 'mage-eventpress'); ?></label>
                    <input type="date" id="export-date-from" value="<?php echo date('Y-m-01'); ?>">
                    
                    <label for="export-date-to"><?php esc_html_e('Date To:', 'mage-eventpress'); ?></label>
                    <input type="date" id="export-date-to" value="<?php echo date('Y-m-t'); ?>">
                </div>
            </div>
            <div class="mpwem-modal-footer">
                <button id="mpwem-export-confirm" class="button button-primary">
                    <?php esc_html_e('Export', 'mage-eventpress'); ?>
                </button>
                <button class="button mpwem-modal-close">
                    <?php esc_html_e('Cancel', 'mage-eventpress'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Localization data is handled via wp_localize_script in MPWEM_Dependencies.php -->