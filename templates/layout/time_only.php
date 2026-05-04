<?php
    if (!defined('ABSPATH')) {
        die;
    } // Cannot access pages directly.
    $event_id = $event_id ?? 0;
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    $event_infos = $event_infos ?? [];
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    if ($event_id > 0) {
        $event_infos = MPWEM_Functions::get_all_info($event_id);
        $all_dates = MPWEM_Functions::get_dates($event_id);
        $upcoming_date = is_array($event_infos) && array_key_exists( 'upcoming_date', $event_infos ) ? $event_infos['upcoming_date'] : '';
        $all_times = MPWEM_Functions::get_times($event_id, $all_dates, $upcoming_date);
        $_single_event_setting_sec = is_array($event_infos) && array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
        $single_event_setting_sec = is_array($_single_event_setting_sec) && !empty($_single_event_setting_sec) ? $_single_event_setting_sec : [];
        $hide_time_details = is_array($single_event_setting_sec) && array_key_exists( 'mep_event_hide_time_from_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_time_from_details'] : 'no';
        if ($hide_time_details == 'no' && $upcoming_date && MPWEM_Global_Function::check_time_exit_date($upcoming_date)) {
            $icon_setting_sec = is_array($event_infos) && array_key_exists( 'icon_setting_sec', $event_infos ) ? $event_infos['icon_setting_sec'] : [];
            $icon_setting_sec = empty($icon_setting_sec) && !is_array($icon_setting_sec) ? [] : $icon_setting_sec;
            $mep_event_time_icon = is_array($icon_setting_sec) && array_key_exists( 'mep_event_time_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_time_icon'] : 'fas fa-clock';
            $date_type = MPWEM_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
            if ($date_type == 'no' || $date_type == 'yes') {
                $first_date = is_array($all_dates) && !empty($all_dates) ? current($all_dates) : [];
                $start_time = is_array($first_date) && array_key_exists('time', $first_date) ? $first_date['time'] : '';
            } else {
                $date = current($all_dates);
                $all_times = MPWEM_Functions::get_times($event_id, $all_dates, $date);
                if (is_array($all_times) && sizeof($all_times) > 0) {
                    $time = current($all_times);
                    $time_info = is_array($time) && array_key_exists( 'start', $time ) ? $time['start'] : [];
                    if (is_array($time_info) && sizeof($time_info) > 0) {
                        $time = is_array($time_info) && array_key_exists( 'time', $time_info ) ? $time_info['time'] : '';
                        if ($time) {
                            $start_time = $date . ' ' . $time;
                        }
                    }
                }
            }
            $url_date = isset( $_GET['date'] ) ? sanitize_text_field( wp_unslash( $_GET['date'] ) ) : null;
            $url_date_2 = isset( $_GET['date_time'] ) ? sanitize_text_field( wp_unslash( $_GET['date_time'] ) ) : null;
            $url_date=$url_date?:$url_date_2;
            $url_date=$url_date ? date( 'Y-m-d H:i', $url_date ) : '';
            $date_format = MPWEM_Global_Function::check_time_exit_date( $url_date ) ? 'Y-m-d H:i' : 'Y-m-d';
            $url_date    = $url_date ? date( $date_format, strtotime($url_date) ) : '';
            $all_dates   = MPWEM_Functions::get_dates( $event_id );
            $all_times   = MPWEM_Functions::get_times( $event_id, $all_dates, $url_date );
            $upcoming_date = is_array($event_infos) && array_key_exists( 'event_upcoming_datetime', $event_infos ) && $date_type == 'no' ? $event_infos['event_start_datetime'] : $event_infos['event_upcoming_datetime'];
            $date                    = $url_date ?: $upcoming_date;
            if (MPWEM_Global_Function::check_time_exit_date($date)) {
                ?>
                <div class="short_item">
                    <h4 class="__icon_circle_mr"><span class="<?php echo esc_attr($mep_event_time_icon); ?>"></span></h4>
                    <div class="_fdColumn">
                        <h6><?php esc_html_e('Event Time:', 'mage-eventpress'); ?></h6>
                        <p class="mep_time_status"><?php echo get_mep_datetime( $date, 'time' ); ?></p>

                    </div>
                </div>
                <?php
            }
        }
    }