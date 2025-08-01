<?php

/*
* @Author 		Analytics Dashboard for Mage EventPress
* Copyright: 	mage-people.com
*/
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

if (!class_exists('MPWEM_Analytics_Dashboard')) {
    class MPWEM_Analytics_Dashboard {
        
        public function __construct() {
            add_action('admin_menu', array($this, 'analytics_menu'));
            add_action('wp_ajax_mpwem_get_analytics_data', array($this, 'get_analytics_data'));
            add_action('wp_ajax_mpwem_get_chart_data', array($this, 'get_chart_data'));
            add_action('wp_ajax_mpwem_get_pie_chart_data', array($this, 'get_pie_chart_data'));
            add_action('wp_ajax_mpwem_export_analytics', array($this, 'export_analytics'));
            
            // Add a test AJAX handler
    
        }

        public function analytics_menu() {
            add_submenu_page(
                'edit.php?post_type=mep_events', 
                __('Analytics Dashboard', 'mage-eventpress'), 
                __('Analytics Dashboard', 'mage-eventpress'), 
                'manage_options', 
                'mep_analytics_dashboard', 
                array($this, 'display_analytics_dashboard')
            );
        }

        public function display_analytics_dashboard() {
            // Check user capabilities
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }
            
            // Enqueue dashboard assets
            $this->enqueue_dashboard_assets();
            
            // Include the dashboard template
            $template_path = MPWEM_Functions::template_path('layout/analytics_dashboard.php');
            if (file_exists($template_path)) {
                require $template_path;
            } else {
                echo '<div class="wrap">';
                echo '<h1>' . __('Analytics Dashboard', 'mage-eventpress') . '</h1>';
                echo '<p>' . __('Dashboard template not found.', 'mage-eventpress') . '</p>';
                echo '</div>';
            }
        }
        
        /**
         * Enqueue dashboard assets
         */
        private function enqueue_dashboard_assets() {
            // Enqueue Chart.js
            wp_enqueue_script(
                'chartjs',
                'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js',
                array(),
                '3.9.1',
                true
            );
            
            // Enqueue dashboard CSS
            wp_enqueue_style(
                'mpwem-analytics-dashboard',
                plugin_dir_url(__FILE__) . '../assets/css/analytics-dashboard.css',
                array(),
                '1.0.0'
            );
            
            // Enqueue dashboard JS
            wp_enqueue_script(
                'mpwem-analytics-dashboard',
                plugin_dir_url(__FILE__) . '../assets/js/analytics-dashboard.js',
                array('jquery', 'chartjs'),
                '1.0.0',
                true
            );
            
            // Note: Localization is handled in MPWEM_Dependencies.php
        }



        public function get_analytics_data() {
            // Add debugging
            error_log('MPWEM Analytics: AJAX request received');
            error_log('MPWEM Analytics: POST data: ' . print_r($_POST, true));
            
            // Verify nonce
            if (!wp_verify_nonce($_POST['nonce'], 'mpwem_analytics_nonce')) {
                error_log('MPWEM Analytics: Nonce verification failed');
                wp_send_json_error(array('message' => 'Security check failed'));
            }

            error_log('MPWEM Analytics: Nonce verification passed');

            // Handle filters parameter (JSON string from JavaScript)
            $filters = array();
            if (isset($_POST['filters'])) {
                // Check if filters is a string (JSON) or already an array
                if (is_string($_POST['filters'])) {
                    $filters = json_decode(stripslashes($_POST['filters']), true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $filters = array();
                    }
                } elseif (is_array($_POST['filters'])) {
                    $filters = $_POST['filters'];
                }
            }

            error_log('MPWEM Analytics: Parsed filters: ' . print_r($filters, true));

            $date_from = sanitize_text_field($filters['dateFrom'] ?? $_POST['date_from'] ?? '');
            $date_to = sanitize_text_field($filters['dateTo'] ?? $_POST['date_to'] ?? '');
            $category_filter = sanitize_text_field($filters['category'] ?? $_POST['category_filter'] ?? '');
            $event_filter = sanitize_text_field($filters['event'] ?? $_POST['event_filter'] ?? '');
            $chart_type = sanitize_text_field($filters['chartType'] ?? 'revenue');
            $period = sanitize_text_field($filters['period'] ?? 'daily');

            // Set default date range if not provided
            if (empty($date_from)) {
                $date_from = date('Y-m-01'); // First day of current month
            }
            if (empty($date_to)) {
                $date_to = date('Y-m-t'); // Last day of current month
            }

            error_log('MPWEM Analytics: Processing data for date range: ' . $date_from . ' to ' . $date_to);
            error_log('MPWEM Analytics: Event filter: ' . $event_filter);
            error_log('MPWEM Analytics: Category filter: ' . $category_filter);

            $analytics_data = $this->calculate_analytics_data($date_from, $date_to, $category_filter, $event_filter);
            $chart_data = $this->get_chart_data_by_type($chart_type, $date_from, $date_to, $period, $category_filter, $event_filter);
            
            // Calculate percentage changes from previous period
            $previous_period_data = $this->calculate_previous_period_data($date_from, $date_to, $category_filter, $event_filter);
            $percentage_changes = $this->calculate_percentage_changes($analytics_data['summary'], $previous_period_data);
            
            // Format data for JavaScript
            $response_data = array(
                'summary' => array(
                    'totalRevenue' => floatval($analytics_data['summary']['total_revenue']),
                    'netRevenue' => floatval($analytics_data['summary']['net_revenue']),
                    'totalOrders' => intval($analytics_data['summary']['total_orders']),
                    'totalTickets' => intval($analytics_data['summary']['total_tickets']),
                    'averageOrder' => floatval($analytics_data['summary']['average_order_value']),
                    'totalRefunds' => floatval($analytics_data['summary']['refunds']),
                    'revenueChange' => $percentage_changes['revenue_change'],
                    'ordersChange' => $percentage_changes['orders_change'],
                    'ticketsChange' => $percentage_changes['tickets_change'],
                    'averageChange' => $percentage_changes['average_change'],
                    'refundsChange' => $percentage_changes['refunds_change'],
                    'profitChange' => $percentage_changes['profit_change']
                ),
                'chart' => array(
                    'labels' => array_column($chart_data, 'date'),
                    'data' => array_column($chart_data, 'value')
                ),
                'events' => $this->format_events_for_table($analytics_data['events']),
                'categories' => $this->format_categories_for_table($analytics_data['categories']),
                'currency_symbol' => $analytics_data['currency_symbol']
            );
            
            error_log('MPWEM Analytics: Sending response with ' . count($response_data['events']) . ' events');
            error_log('MPWEM Analytics: Summary data - Revenue: ' . $response_data['summary']['totalRevenue'] . ', Revenue Change: ' . $response_data['summary']['revenueChange'] . '%, Orders: ' . $response_data['summary']['totalOrders'] . ', Orders Change: ' . $response_data['summary']['ordersChange'] . '%');
            wp_send_json_success($response_data);
        }

        public function get_chart_data() {
            // Verify nonce
            if (!wp_verify_nonce($_POST['nonce'], 'mpwem_analytics_nonce')) {
                wp_send_json_error(array('message' => 'Security check failed'));
            }

            $chart_type = sanitize_text_field($_POST['chart_type'] ?? 'revenue');
            $date_from = sanitize_text_field($_POST['date_from'] ?? '');
            $date_to = sanitize_text_field($_POST['date_to'] ?? '');
            $period = sanitize_text_field($_POST['period'] ?? 'daily');

            // Handle filters parameter (JSON string from JavaScript)
            $filters = array();
            if (isset($_POST['filters'])) {
                // Check if filters is a string (JSON) or already an array
                if (is_string($_POST['filters'])) {
                    $filters = json_decode(stripslashes($_POST['filters']), true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $filters = array();
                    }
                } elseif (is_array($_POST['filters'])) {
                    $filters = $_POST['filters'];
                }
            }

            $category_filter = sanitize_text_field($filters['category'] ?? $_POST['category_filter'] ?? '');
            $event_filter = sanitize_text_field($filters['event'] ?? $_POST['event_filter'] ?? '');

            $chart_data = $this->get_chart_data_by_type($chart_type, $date_from, $date_to, $period, $category_filter, $event_filter);
            
            wp_send_json_success($chart_data);
        }

        private function calculate_analytics_data($date_from, $date_to, $category_filter = '', $event_filter = '') {
            global $wpdb;

            // Add debugging for filters
            error_log('MPWEM Analytics: Filters - Event: ' . $event_filter . ', Category: ' . $category_filter);

            // Get orders in date range
            $order_status = array('wc-completed', 'wc-processing');
            $start_date = $date_from . ' 00:00:00';
            $end_date = $date_to . ' 23:59:59';

            $orders = wc_get_orders([
                'limit' => -1,
                'status' => $order_status,
                'date_created' => $start_date . '...' . $end_date,
                'return' => 'ids',
            ]);

            $total_revenue = 0;
            $total_orders = 0; // Changed: Only count filtered orders
            $total_tickets = 0;
            $refunds = 0;
            $cancellations = 0;
            $event_data = array();
            $category_data = array();

            foreach ($orders as $order_id) {
                $order = wc_get_order($order_id);
                if (!$order) continue;

                $order_total = 0; // Changed: Only count filtered order total
                $order_has_matching_items = false; // Track if order has matching items

                // Get order items
                foreach ($order->get_items() as $item_id => $item) {
                    $product_id = $item->get_product_id();
                    $event_id = get_post_meta($product_id, 'link_mep_event', true) ?: $product_id;
                    
                    if (get_post_type($event_id) == 'mep_events') {
                        // Apply event filter - convert both to strings for comparison
                        if (!empty($event_filter) && (string)$event_id !== (string)$event_filter) {
                            error_log('MPWEM Analytics: Skipping event ' . $event_id . ' due to filter ' . $event_filter);
                            continue;
                        }

                        // Apply category filter
                        $event_categories = wp_get_post_terms($event_id, 'mep_cat', array('fields' => 'slugs'));
                        if (!empty($category_filter) && !in_array($category_filter, $event_categories)) {
                            error_log('MPWEM Analytics: Skipping event ' . $event_id . ' due to category filter ' . $category_filter . ' (event categories: ' . implode(',', $event_categories) . ')');
                            continue;
                        }

                        // If we reach here, the item matches our filters
                        $order_has_matching_items = true;
                        $item_total = $item->get_total();
                        $order_total += $item_total;
                        $total_revenue += $item_total;

                        $quantity = $item->get_quantity();
                        $total_tickets += $quantity;

                        // Event data
                        $event_title = get_the_title($event_id);
                        if (!isset($event_data[$event_id])) {
                            $event_data[$event_id] = array(
                                'title' => $event_title,
                                'revenue' => 0,
                                'tickets' => 0,
                                'orders' => 0
                            );
                        }
                        $event_data[$event_id]['revenue'] += $item_total;
                        $event_data[$event_id]['tickets'] += $quantity;
                        $event_data[$event_id]['orders']++;

                        // Category data
                        $categories = wp_get_post_terms($event_id, 'mep_cat');
                        foreach ($categories as $category) {
                            if (!isset($category_data[$category->slug])) {
                                $category_data[$category->slug] = array(
                                    'name' => $category->name,
                                    'revenue' => 0,
                                    'tickets' => 0,
                                    'events' => array()
                                );
                            }
                            $category_data[$category->slug]['revenue'] += $item_total;
                            $category_data[$category->slug]['tickets'] += $quantity;
                            $category_data[$category->slug]['events'][$event_id] = $event_title;
                        }
                    }
                }

                // Only count this order if it has matching items
                if ($order_has_matching_items) {
                    $total_orders++;
                }

                // Check for refunds (only for filtered orders)
                if ($order_has_matching_items) {
                    $refunds += $order->get_total_refunded();
                }
                
                // Check for cancellations (only for filtered orders)
                if ($order_has_matching_items && $order->get_status() == 'cancelled') {
                    $cancellations++;
                }
            }

            // Calculate additional metrics
            $average_order_value = $total_orders > 0 ? $total_revenue / $total_orders : 0;
            $net_revenue = $total_revenue - $refunds;
            $profit_loss = $net_revenue; // Simplified - you might want to subtract costs

            error_log('MPWEM Analytics: Final results - Revenue: ' . $total_revenue . ', Orders: ' . $total_orders . ', Tickets: ' . $total_tickets . ', Events: ' . count($event_data));

            return array(
                'summary' => array(
                    'total_revenue' => $total_revenue,
                    'net_revenue' => $net_revenue,
                    'total_orders' => $total_orders,
                    'total_tickets' => $total_tickets,
                    'average_order_value' => $average_order_value,
                    'refunds' => $refunds,
                    'cancellations' => $cancellations,
                    'profit_loss' => $profit_loss
                ),
                'events' => $event_data,
                'categories' => $category_data,
                'currency_symbol' => get_woocommerce_currency_symbol()
            );
        }

        private function get_chart_data_by_type($chart_type, $date_from, $date_to, $period, $category_filter = '', $event_filter = '') {
            error_log('MPWEM Analytics: Chart data - Type: ' . $chart_type . ', Period: ' . $period . ', Event Filter: ' . $event_filter . ', Category Filter: ' . $category_filter);
            
            $start_date = new DateTime($date_from);
            $end_date = new DateTime($date_to);
            $chart_data = array();
            
            // Determine interval based on period
            if ($period == 'weekly') {
                $interval = new DateInterval('P7D');
                $format = 'M j';
            } elseif ($period == 'monthly') {
                $interval = new DateInterval('P1M');
                $format = 'M Y';
            } else {
                $interval = new DateInterval('P1D');
                $format = 'M j';
            }

            $current = clone $start_date;
            
            while ($current <= $end_date) {
                $current_date = $current->format('Y-m-d');
                $next_date = clone $current;
                $next_date->add($interval);
                $next_date_str = $next_date->format('Y-m-d');
                
                // Get orders for this period
                $orders = wc_get_orders([
                    'limit' => -1,
                    'status' => array('wc-completed', 'wc-processing'),
                    'date_created' => $current_date . ' 00:00:00...' . $next_date_str . ' 00:00:00',
                    'return' => 'ids',
                ]);

                $value = 0;
                if ($chart_type == 'revenue') {
                    foreach ($orders as $order_id) {
                        $order = wc_get_order($order_id);
                        if ($order) {
                            // Apply filters to chart data
                            $order_value = 0;
                            foreach ($order->get_items() as $item) {
                                $product_id = $item->get_product_id();
                                $event_id = get_post_meta($product_id, 'link_mep_event', true) ?: $product_id;
                                
                                if (get_post_type($event_id) == 'mep_events') {
                                    // Apply event filter
                                    if (!empty($event_filter) && (string)$event_id !== (string)$event_filter) {
                                        continue;
                                    }
                                    
                                    // Apply category filter
                                    $event_categories = wp_get_post_terms($event_id, 'mep_cat', array('fields' => 'slugs'));
                                    if (!empty($category_filter) && !in_array($category_filter, $event_categories)) {
                                        continue;
                                    }
                                    
                                    $order_value += floatval($item->get_total());
                                }
                            }
                            $value += $order_value;
                        }
                    }
                } elseif ($chart_type == 'orders') {
                    // For orders, we need to check if any items in the order match our filters
                    $filtered_orders = 0;
                    foreach ($orders as $order_id) {
                        $order = wc_get_order($order_id);
                        if ($order) {
                            $has_matching_items = false;
                            foreach ($order->get_items() as $item) {
                                $product_id = $item->get_product_id();
                                $event_id = get_post_meta($product_id, 'link_mep_event', true) ?: $product_id;
                                
                                if (get_post_type($event_id) == 'mep_events') {
                                    // Apply event filter
                                    if (!empty($event_filter) && (string)$event_id !== (string)$event_filter) {
                                        continue;
                                    }
                                    
                                    // Apply category filter
                                    $event_categories = wp_get_post_terms($event_id, 'mep_cat', array('fields' => 'slugs'));
                                    if (!empty($category_filter) && !in_array($category_filter, $event_categories)) {
                                        continue;
                                    }
                                    
                                    $has_matching_items = true;
                                    break;
                                }
                            }
                            if ($has_matching_items) {
                                $filtered_orders++;
                            }
                        }
                    }
                    $value = $filtered_orders;
                } elseif ($chart_type == 'tickets') {
                    foreach ($orders as $order_id) {
                        $order = wc_get_order($order_id);
                        if ($order) {
                            foreach ($order->get_items() as $item) {
                                $product_id = $item->get_product_id();
                                $event_id = get_post_meta($product_id, 'link_mep_event', true) ?: $product_id;
                                
                                if (get_post_type($event_id) == 'mep_events') {
                                    // Apply event filter
                                    if (!empty($event_filter) && (string)$event_id !== (string)$event_filter) {
                                        continue;
                                    }
                                    
                                    // Apply category filter
                                    $event_categories = wp_get_post_terms($event_id, 'mep_cat', array('fields' => 'slugs'));
                                    if (!empty($category_filter) && !in_array($category_filter, $event_categories)) {
                                        continue;
                                    }
                                    
                                    $value += intval($item->get_quantity());
                                }
                            }
                        }
                    }
                }

                $chart_data[] = array(
                    'date' => $current->format($format),
                    'value' => $value
                );
                
                $current->add($interval);
            }

            error_log('MPWEM Analytics: Chart data generated: ' . count($chart_data) . ' data points');
            return $chart_data;
        }

        public function export_analytics() {
            // Verify nonce
            if (!wp_verify_nonce($_POST['nonce'], 'mpwem_analytics_nonce')) {
                wp_send_json_error(array('message' => 'Security check failed'));
            }

            $date_from = sanitize_text_field($_POST['dateFrom'] ?? $_POST['date_from'] ?? '');
            $date_to = sanitize_text_field($_POST['dateTo'] ?? $_POST['date_to'] ?? '');
            $format = sanitize_text_field($_POST['format'] ?? 'csv');

            // Handle filters if provided
            $filters = array();
            if (isset($_POST['filters'])) {
                // Check if filters is a string (JSON) or already an array
                if (is_string($_POST['filters'])) {
                    $filters = json_decode(stripslashes($_POST['filters']), true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $filters = array();
                    }
                } elseif (is_array($_POST['filters'])) {
                    $filters = $_POST['filters'];
                }
            }

            $category_filter = sanitize_text_field($filters['category'] ?? '');
            $event_filter = sanitize_text_field($filters['event'] ?? '');

            $analytics_data = $this->calculate_analytics_data($date_from, $date_to, $category_filter, $event_filter);

            if ($format == 'csv') {
                $this->export_to_csv($analytics_data, $date_from, $date_to);
            } else {
                $this->export_to_pdf($analytics_data, $date_from, $date_to);
            }
        }

        private function export_to_csv($data, $date_from, $date_to) {
            $filename = 'analytics_' . $date_from . '_to_' . $date_to . '.csv';
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // Summary data
            fputcsv($output, array('Summary Report', $date_from . ' to ' . $date_to));
            fputcsv($output, array('Total Revenue', $data['summary']['total_revenue']));
            fputcsv($output, array('Net Revenue', $data['summary']['net_revenue']));
            fputcsv($output, array('Total Orders', $data['summary']['total_orders']));
            fputcsv($output, array('Total Tickets', $data['summary']['total_tickets']));
            fputcsv($output, array('Average Order Value', $data['summary']['average_order_value']));
            fputcsv($output, array('Refunds', $data['summary']['refunds']));
            fputcsv($output, array('Cancellations', $data['summary']['cancellations']));
            fputcsv($output, array(''));
            
            // Event data
            fputcsv($output, array('Event Performance'));
            fputcsv($output, array('Event Name', 'Revenue', 'Tickets Sold', 'Orders'));
            foreach ($data['events'] as $event) {
                fputcsv($output, array(
                    $event['title'],
                    $event['revenue'],
                    $event['tickets'],
                    $event['orders']
                ));
            }
            
            fclose($output);
            exit;
        }

        private function export_to_pdf($data, $date_from, $date_to) {
            // Check if mPDF is available and load it if needed
            if (!class_exists('mPDF') && !class_exists('\Mpdf\Mpdf')) {
                // Try to load mPDF from the PDF support plugin
                $pdf_support_path = WP_PLUGIN_DIR . '/magepeople-pdf-support-master/lib/vendor/autoload.php';
                if (file_exists($pdf_support_path)) {
                    require_once $pdf_support_path;
                }
                
                // Check again after loading
                if (!class_exists('mPDF') && !class_exists('\Mpdf\Mpdf')) {
                    error_log('MPWEM Analytics: mPDF library not available, falling back to HTML');
                    $this->export_to_html_fallback($data, $date_from, $date_to);
                    return;
                }
            }
            
            $currency_symbol = get_woocommerce_currency_symbol();
            $filename = 'analytics_' . $date_from . '_to_' . $date_to . '.pdf';
            
            // Generate HTML content for PDF
            $html = $this->generate_pdf_html($data, $date_from, $date_to, $currency_symbol);
            
            try {
                error_log('MPWEM Analytics: Starting PDF generation');
                
                // Initialize mPDF
                if (class_exists('\Mpdf\Mpdf')) {
                    error_log('MPWEM Analytics: Using \Mpdf\Mpdf class');
                    $mpdf = new \Mpdf\Mpdf([
                        'mode' => 'utf-8',
                        'format' => 'A4',
                        'margin_left' => 15,
                        'margin_right' => 15,
                        'margin_top' => 15,
                        'margin_bottom' => 15
                    ]);
                } else {
                    error_log('MPWEM Analytics: Using mPDF class');
                    $mpdf = new mPDF('utf-8', 'A4', 15, 15, 15, 15);
                }
                
                error_log('MPWEM Analytics: mPDF initialized successfully');
                
                // Configure mPDF settings
                $mpdf->allow_charset_conversion = true;
                $mpdf->autoScriptToLang = true;
                $mpdf->baseScript = 1;
                $mpdf->autoVietnamese = true;
                $mpdf->autoArabic = true;
                $mpdf->autoLangToFont = true;
                
                error_log('MPWEM Analytics: Writing HTML to PDF');
                
                // Write HTML to PDF
                $mpdf->WriteHTML($html);
                
                error_log('MPWEM Analytics: Outputting PDF file: ' . $filename);
                
                // Output PDF
                $mpdf->Output($filename, 'D');
                exit;
                
            } catch (Exception $e) {
                // Fallback to HTML if PDF generation fails
                error_log('MPWEM Analytics: PDF generation failed: ' . $e->getMessage());
                $this->export_to_html_fallback($data, $date_from, $date_to);
            }
        }

        private function generate_pdf_html($data, $date_from, $date_to, $currency_symbol) {
            // Debug logging
            error_log('MPWEM Analytics: generate_pdf_html called with data structure: ' . print_r($data, true));
            error_log('MPWEM Analytics: Date range: ' . $date_from . ' to ' . $date_to);
            error_log('MPWEM Analytics: Currency symbol: ' . $currency_symbol);
            
            // Create compact HTML to prevent extra page
            $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Analytics Report</title><style>body{font-family:Arial,sans-serif;margin:0;padding:15px;color:#333;font-size:10px;line-height:1.3}.header{text-align:center;margin-bottom:20px;border-bottom:2px solid #007cba;padding-bottom:15px;page-break-after:avoid}.header h1{color:#007cba;margin:0;font-size:18px;page-break-after:avoid}.header p{color:#666;margin:8px 0 0 0;font-size:11px;page-break-after:avoid}.summary{margin-bottom:20px;page-break-inside:avoid}.summary h2{color:#007cba;border-bottom:1px solid #ddd;padding-bottom:8px;font-size:14px;page-break-after:avoid}.summary table{width:100%;border-collapse:collapse;margin-top:10px;page-break-inside:avoid}.summary th,.summary td{border:1px solid #ddd;padding:8px;text-align:left;font-size:10px}.summary th{background-color:#f8f9fa;font-weight:bold;color:#333}.summary td{background-color:#fff}.events{margin-bottom:20px;page-break-inside:avoid}.events h2{color:#007cba;border-bottom:1px solid #ddd;padding-bottom:8px;font-size:14px;page-break-after:avoid}.events table{width:100%;border-collapse:collapse;margin-top:10px;page-break-inside:avoid}.events th,.events td{border:1px solid #ddd;padding:8px;text-align:left;font-size:10px}.events th{background-color:#f8f9fa;font-weight:bold;color:#333}.events td{background-color:#fff}.categories{margin-bottom:20px;page-break-inside:avoid}.categories h2{color:#007cba;border-bottom:1px solid #ddd;padding-bottom:8px;font-size:14px;page-break-after:avoid}.categories table{width:100%;border-collapse:collapse;margin-top:10px;page-break-inside:avoid}.categories th,.categories td{border:1px solid #ddd;padding:8px;text-align:left;font-size:10px}.categories th{background-color:#f8f9fa;font-weight:bold;color:#333}.categories td{background-color:#fff}.footer{margin-top:20px;text-align:center;color:#666;font-size:10px;border-top:1px solid #ddd;padding-top:15px;page-break-before:avoid}tr{page-break-inside:avoid}h1,h2,h3{page-break-after:avoid}p,div{page-break-inside:avoid}</style></head><body><div class="header"><h1>Analytics Report</h1><p>Period: ' . $date_from . ' to ' . $date_to . '</p></div>';
            
            // Add summary section
            if (!empty($data['summary'])) {
                $html .= '<div class="summary"><h2>Summary</h2><table><tr><th>Metric</th><th>Value</th></tr><tr><td>Total Revenue</td><td>' . $currency_symbol . number_format($data['summary']['total_revenue'] ?? 0, 2) . '</td></tr><tr><td>Net Revenue</td><td>' . $currency_symbol . number_format($data['summary']['net_revenue'] ?? 0, 2) . '</td></tr><tr><td>Total Orders</td><td>' . number_format($data['summary']['total_orders'] ?? 0) . '</td></tr><tr><td>Total Tickets</td><td>' . number_format($data['summary']['total_tickets'] ?? 0) . '</td></tr><tr><td>Average Order Value</td><td>' . $currency_symbol . number_format($data['summary']['average_order_value'] ?? 0, 2) . '</td></tr><tr><td>Refunds</td><td>' . $currency_symbol . number_format($data['summary']['refunds'] ?? 0, 2) . '</td></tr><tr><td>Cancellations</td><td>' . number_format($data['summary']['cancellations'] ?? 0) . '</td></tr></table></div>';
            }
            
            // Add events section
            if (!empty($data['events'])) {
                $html .= '<div class="events"><h2>Event Performance</h2><table><tr><th>Event</th><th>Revenue</th><th>Tickets Sold</th><th>Orders</th></tr>';
                foreach ($data['events'] as $event) {
                    $html .= '<tr><td>' . esc_html($event['title'] ?? 'Unknown Event') . '</td><td>' . $currency_symbol . number_format($event['revenue'] ?? 0, 2) . '</td><td>' . number_format($event['tickets'] ?? 0) . '</td><td>' . number_format($event['orders'] ?? 0) . '</td></tr>';
                }
                $html .= '</table></div>';
            }
            
            // Add categories section
            if (!empty($data['categories'])) {
                $html .= '<div class="categories"><h2>Category Performance</h2><table><tr><th>Category</th><th>Revenue</th><th>Tickets Sold</th><th>Events Count</th></tr>';
                foreach ($data['categories'] as $category) {
                    $html .= '<tr><td>' . esc_html($category['name'] ?? 'Unknown Category') . '</td><td>' . $currency_symbol . number_format($category['revenue'] ?? 0, 2) . '</td><td>' . number_format($category['tickets'] ?? 0) . '</td><td>' . number_format(count($category['events'] ?? array())) . '</td></tr>';
                }
                $html .= '</table></div>';
            }
            
            // Add footer
            $html .= '<div class="footer"><p>Generated by MageEventPress Analytics Dashboard</p></div></body></html>';
            
            error_log('MPWEM Analytics: HTML generated successfully, length: ' . strlen($html));
            
            return $html;
        }

        private function export_to_html_fallback($data, $date_from, $date_to) {
            $filename = 'analytics_' . $date_from . '_to_' . $date_to . '.html';
            
            header('Content-Type: text/html');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $currency_symbol = get_woocommerce_currency_symbol();
            $html = $this->generate_pdf_html($data, $date_from, $date_to, $currency_symbol);
            
            echo $html;
            exit;
        }

        private function format_events_for_table($events_data) {
            error_log('MPWEM Analytics: Formatting events data: ' . print_r($events_data, true));
            
            $formatted_events = array();
            
            foreach ($events_data as $event_id => $event) {
                $event_date = get_post_meta($event_id, 'event_start_date', true);
                $formatted_date = $event_date ? date('M j, Y', strtotime($event_date)) : 'N/A';
                
                $formatted_events[] = array(
                    'name' => $event['title'],
                    'date' => $formatted_date,
                    'revenue' => floatval($event['revenue']),
                    'tickets' => intval($event['tickets']),
                    'orders' => intval($event['orders']),
                    'averageOrder' => $event['orders'] > 0 ? floatval($event['revenue'] / $event['orders']) : 0
                );
            }
            
            // Sort by revenue descending
            usort($formatted_events, function($a, $b) {
                return $b['revenue'] <=> $a['revenue'];
            });
            
            error_log('MPWEM Analytics: Formatted events: ' . count($formatted_events) . ' events');
            return $formatted_events;
        }

        private function format_categories_for_table($categories_data) {
            error_log('MPWEM Analytics: Formatting categories data: ' . print_r($categories_data, true));
            
            $formatted_categories = array();
            
            foreach ($categories_data as $category_slug => $category) {
                $formatted_categories[] = array(
                    'name' => $category['name'],
                    'description' => '', // Add description if available
                    'revenue' => floatval($category['revenue']),
                    'tickets' => intval($category['tickets']),
                    'events' => count($category['events'])
                );
            }
            
            // Sort by revenue descending
            usort($formatted_categories, function($a, $b) {
                return $b['revenue'] <=> $a['revenue'];
            });
            
            error_log('MPWEM Analytics: Formatted categories: ' . count($formatted_categories) . ' categories');
            return $formatted_categories;
        }

        /**
         * Calculate data for the previous period (same duration as current period)
         */
        private function calculate_previous_period_data($date_from, $date_to, $category_filter = '', $event_filter = '') {
            $start_date = new DateTime($date_from);
            $end_date = new DateTime($date_to);
            
            // Calculate the duration of the current period
            $duration = $start_date->diff($end_date);
            
            // Calculate the previous period dates
            $previous_end = clone $start_date;
            $previous_end->sub(new DateInterval('P1D')); // One day before current start
            
            $previous_start = clone $previous_end;
            $previous_start->sub($duration); // Same duration as current period
            
            $previous_date_from = $previous_start->format('Y-m-d');
            $previous_date_to = $previous_end->format('Y-m-d');
            
            error_log('MPWEM Analytics: Previous period - From: ' . $previous_date_from . ' To: ' . $previous_date_to);
            
            return $this->calculate_analytics_data($previous_date_from, $previous_date_to, $category_filter, $event_filter);
        }

        /**
         * Calculate percentage changes between current and previous period
         */
        private function calculate_percentage_changes($current_data, $previous_data) {
            $changes = array();
            
            // Helper function to calculate percentage change
            $calculate_change = function($current, $previous) {
                if ($previous == 0) {
                    return $current > 0 ? 100 : 0; // If previous was 0, show 100% if current > 0, else 0%
                }
                return (($current - $previous) / $previous) * 100;
            };
            
            // Calculate changes for each metric
            $changes['revenue_change'] = $calculate_change(
                $current_data['total_revenue'], 
                $previous_data['summary']['total_revenue']
            );
            
            $changes['orders_change'] = $calculate_change(
                $current_data['total_orders'], 
                $previous_data['summary']['total_orders']
            );
            
            $changes['tickets_change'] = $calculate_change(
                $current_data['total_tickets'], 
                $previous_data['summary']['total_tickets']
            );
            
            $changes['average_change'] = $calculate_change(
                $current_data['average_order_value'], 
                $previous_data['summary']['average_order_value']
            );
            
            $changes['refunds_change'] = $calculate_change(
                $current_data['refunds'], 
                $previous_data['summary']['refunds']
            );
            
            $changes['profit_change'] = $calculate_change(
                $current_data['net_revenue'], 
                $previous_data['summary']['net_revenue']
            );
            
            error_log('MPWEM Analytics: Percentage changes calculated - Revenue: ' . $changes['revenue_change'] . '%, Orders: ' . $changes['orders_change'] . '%, Tickets: ' . $changes['tickets_change'] . '%');
            
            return $changes;
        }

        /**
         * Get pie chart data for analytics dashboard
         */
        public function get_pie_chart_data() {
            // Verify nonce
            if (!wp_verify_nonce($_POST['nonce'], 'mpwem_analytics_nonce')) {
                wp_send_json_error(array('message' => 'Security check failed'));
            }

            $date_from = sanitize_text_field($_POST['date_from'] ?? '');
            $date_to = sanitize_text_field($_POST['date_to'] ?? '');
            
            // Handle filters parameter (JSON string from JavaScript)
            $filters = array();
            if (isset($_POST['filters'])) {
                if (is_string($_POST['filters'])) {
                    $filters = json_decode(stripslashes($_POST['filters']), true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $filters = array();
                    }
                } elseif (is_array($_POST['filters'])) {
                    $filters = $_POST['filters'];
                }
            }

            $category_filter = sanitize_text_field($filters['category'] ?? $_POST['category_filter'] ?? '');
            $event_filter = sanitize_text_field($filters['event'] ?? $_POST['event_filter'] ?? '');

            $pie_chart_data = array(
                'revenue_by_category' => $this->get_revenue_by_category_data($date_from, $date_to, $category_filter, $event_filter),
                'revenue_by_event' => $this->get_revenue_by_event_data($date_from, $date_to, $category_filter, $event_filter),
                'tickets_by_category' => $this->get_tickets_by_category_data($date_from, $date_to, $category_filter, $event_filter),
                'orders_by_status' => $this->get_orders_by_status_data($date_from, $date_to, $category_filter, $event_filter)
            );

            wp_send_json_success($pie_chart_data);
        }

        /**
         * Get revenue breakdown by category
         */
        private function get_revenue_by_category_data($date_from, $date_to, $category_filter = '', $event_filter = '') {
            global $wpdb;

            $start_date = $date_from . ' 00:00:00';
            $end_date = $date_to . ' 23:59:59';

            $orders = wc_get_orders([
                'limit' => -1,
                'status' => array('wc-completed', 'wc-processing'),
                'date_created' => $start_date . '...' . $end_date,
                'return' => 'ids',
            ]);

            $category_revenue = array();

            foreach ($orders as $order_id) {
                $order = wc_get_order($order_id);
                if (!$order) continue;

                foreach ($order->get_items() as $item) {
                    $product_id = $item->get_product_id();
                    $event_id = get_post_meta($product_id, 'link_mep_event', true) ?: $product_id;
                    
                    if (get_post_type($event_id) == 'mep_events') {
                        // Apply event filter
                        if (!empty($event_filter) && (string)$event_id !== (string)$event_filter) {
                            continue;
                        }

                        // Apply category filter
                        $event_categories = wp_get_post_terms($event_id, 'mep_cat', array('fields' => 'slugs'));
                        if (!empty($category_filter) && !in_array($category_filter, $event_categories)) {
                            continue;
                        }

                        $item_total = floatval($item->get_total());
                        
                        // Get categories for this event
                        $categories = wp_get_post_terms($event_id, 'mep_cat');
                        foreach ($categories as $category) {
                            if (!isset($category_revenue[$category->slug])) {
                                $category_revenue[$category->slug] = array(
                                    'name' => $category->name,
                                    'revenue' => 0
                                );
                            }
                            $category_revenue[$category->slug]['revenue'] += $item_total;
                        }
                    }
                }
            }

            // Convert to chart format
            $chart_data = array();
            foreach ($category_revenue as $slug => $data) {
                $chart_data[] = array(
                    'label' => $data['name'],
                    'value' => $data['revenue'],
                    'color' => $this->get_category_color($slug)
                );
            }

            // Sort by revenue descending
            usort($chart_data, function($a, $b) {
                return $b['value'] <=> $a['value'];
            });

            return $chart_data;
        }

        /**
         * Get revenue breakdown by event
         */
        private function get_revenue_by_event_data($date_from, $date_to, $category_filter = '', $event_filter = '') {
            global $wpdb;

            $start_date = $date_from . ' 00:00:00';
            $end_date = $date_to . ' 23:59:59';

            $orders = wc_get_orders([
                'limit' => -1,
                'status' => array('wc-completed', 'wc-processing'),
                'date_created' => $start_date . '...' . $end_date,
                'return' => 'ids',
            ]);

            $event_revenue = array();

            foreach ($orders as $order_id) {
                $order = wc_get_order($order_id);
                if (!$order) continue;

                foreach ($order->get_items() as $item) {
                    $product_id = $item->get_product_id();
                    $event_id = get_post_meta($product_id, 'link_mep_event', true) ?: $product_id;
                    
                    if (get_post_type($event_id) == 'mep_events') {
                        // Apply event filter
                        if (!empty($event_filter) && (string)$event_id !== (string)$event_filter) {
                            continue;
                        }

                        // Apply category filter
                        $event_categories = wp_get_post_terms($event_id, 'mep_cat', array('fields' => 'slugs'));
                        if (!empty($category_filter) && !in_array($category_filter, $event_categories)) {
                            continue;
                        }

                        $item_total = floatval($item->get_total());
                        $event_title = get_the_title($event_id);
                        
                        if (!isset($event_revenue[$event_id])) {
                            $event_revenue[$event_id] = array(
                                'name' => $event_title,
                                'revenue' => 0
                            );
                        }
                        $event_revenue[$event_id]['revenue'] += $item_total;
                    }
                }
            }

            // Convert to chart format
            $chart_data = array();
            foreach ($event_revenue as $event_id => $data) {
                $chart_data[] = array(
                    'label' => $data['name'],
                    'value' => $data['revenue'],
                    'color' => $this->get_event_color($event_id)
                );
            }

            // Sort by revenue descending
            usort($chart_data, function($a, $b) {
                return $b['value'] <=> $a['value'];
            });

            return $chart_data;
        }

        /**
         * Get tickets breakdown by category
         */
        private function get_tickets_by_category_data($date_from, $date_to, $category_filter = '', $event_filter = '') {
            global $wpdb;

            $start_date = $date_from . ' 00:00:00';
            $end_date = $date_to . ' 23:59:59';

            $orders = wc_get_orders([
                'limit' => -1,
                'status' => array('wc-completed', 'wc-processing'),
                'date_created' => $start_date . '...' . $end_date,
                'return' => 'ids',
            ]);

            $category_tickets = array();

            foreach ($orders as $order_id) {
                $order = wc_get_order($order_id);
                if (!$order) continue;

                foreach ($order->get_items() as $item) {
                    $product_id = $item->get_product_id();
                    $event_id = get_post_meta($product_id, 'link_mep_event', true) ?: $product_id;
                    
                    if (get_post_type($event_id) == 'mep_events') {
                        // Apply event filter
                        if (!empty($event_filter) && (string)$event_id !== (string)$event_filter) {
                            continue;
                        }

                        // Apply category filter
                        $event_categories = wp_get_post_terms($event_id, 'mep_cat', array('fields' => 'slugs'));
                        if (!empty($category_filter) && !in_array($category_filter, $event_categories)) {
                            continue;
                        }

                        $quantity = intval($item->get_quantity());
                        
                        // Get categories for this event
                        $categories = wp_get_post_terms($event_id, 'mep_cat');
                        foreach ($categories as $category) {
                            if (!isset($category_tickets[$category->slug])) {
                                $category_tickets[$category->slug] = array(
                                    'name' => $category->name,
                                    'tickets' => 0
                                );
                            }
                            $category_tickets[$category->slug]['tickets'] += $quantity;
                        }
                    }
                }
            }

            // Convert to chart format
            $chart_data = array();
            foreach ($category_tickets as $slug => $data) {
                $chart_data[] = array(
                    'label' => $data['name'],
                    'value' => $data['tickets'],
                    'color' => $this->get_category_color($slug)
                );
            }

            // Sort by tickets descending
            usort($chart_data, function($a, $b) {
                return $b['value'] <=> $a['value'];
            });

            return $chart_data;
        }

        /**
         * Get orders breakdown by status
         */
        private function get_orders_by_status_data($date_from, $date_to, $category_filter = '', $event_filter = '') {
            global $wpdb;

            $start_date = $date_from . ' 00:00:00';
            $end_date = $date_to . ' 23:59:59';

            $orders = wc_get_orders([
                'limit' => -1,
                'status' => array('wc-completed', 'wc-processing', 'wc-pending', 'wc-cancelled', 'wc-refunded'),
                'date_created' => $start_date . '...' . $end_date,
                'return' => 'ids',
            ]);

            $status_counts = array(
                'completed' => 0,
                'processing' => 0,
                'pending' => 0,
                'cancelled' => 0,
                'refunded' => 0
            );

            foreach ($orders as $order_id) {
                $order = wc_get_order($order_id);
                if (!$order) continue;

                // Check if order has matching event items
                $has_matching_items = false;
                foreach ($order->get_items() as $item) {
                    $product_id = $item->get_product_id();
                    $event_id = get_post_meta($product_id, 'link_mep_event', true) ?: $product_id;
                    
                    if (get_post_type($event_id) == 'mep_events') {
                        // Apply event filter
                        if (!empty($event_filter) && (string)$event_id !== (string)$event_filter) {
                            continue;
                        }

                        // Apply category filter
                        $event_categories = wp_get_post_terms($event_id, 'mep_cat', array('fields' => 'slugs'));
                        if (!empty($category_filter) && !in_array($category_filter, $event_categories)) {
                            continue;
                        }

                        $has_matching_items = true;
                        break;
                    }
                }

                if ($has_matching_items) {
                    $status = $order->get_status();
                    switch ($status) {
                        case 'completed':
                            $status_counts['completed']++;
                            break;
                        case 'processing':
                            $status_counts['processing']++;
                            break;
                        case 'pending':
                            $status_counts['pending']++;
                            break;
                        case 'cancelled':
                            $status_counts['cancelled']++;
                            break;
                        case 'refunded':
                            $status_counts['refunded']++;
                            break;
                    }
                }
            }

            // Convert to chart format
            $chart_data = array();
            $status_labels = array(
                'completed' => 'Completed',
                'processing' => 'Processing',
                'pending' => 'Pending',
                'cancelled' => 'Cancelled',
                'refunded' => 'Refunded'
            );

            $status_colors = array(
                'completed' => '#10b981',
                'processing' => '#3b82f6',
                'pending' => '#f59e0b',
                'cancelled' => '#ef4444',
                'refunded' => '#8b5cf6'
            );

            foreach ($status_counts as $status => $count) {
                if ($count > 0) {
                    $chart_data[] = array(
                        'label' => $status_labels[$status],
                        'value' => $count,
                        'color' => $status_colors[$status]
                    );
                }
            }

            return $chart_data;
        }

        /**
         * Get color for category
         */
        private function get_category_color($slug) {
            $colors = array(
                '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6',
                '#06b6d4', '#84cc16', '#f97316', '#ec4899', '#6366f1'
            );
            
            $index = crc32($slug) % count($colors);
            return $colors[$index];
        }

        /**
         * Get color for event
         */
        private function get_event_color($event_id) {
            $colors = array(
                '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6',
                '#06b6d4', '#84cc16', '#f97316', '#ec4899', '#6366f1'
            );
            
            $index = $event_id % count($colors);
            return $colors[$index];
        }
    }
    
    new MPWEM_Analytics_Dashboard();
}