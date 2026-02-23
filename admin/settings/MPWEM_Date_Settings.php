<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Date_Settings' ) ) {
		class MPWEM_Date_Settings {
			public function __construct() {
				add_action( 'mpwem_event_tab_setting_item', [ $this, 'date_time_tab' ], 10, 2 );
			}
			public function date_time_tab( $event_id, $event_infos = [] ) {
				?>
                <div class="mpwem_style mp_tab_item mpwem_date_settings" data-tab-item="#mpwem_date_settings">
					<?php $this->setting_head( $event_id ); ?>
					<?php $this->normal_date( $event_id ); ?>
					<?php $this->particular_section( $event_id ); ?>
					<?php $this->repeat_section( $event_id ); ?>
					<?php $this->event_date_format( $event_id, $event_infos ); ?>
                </div>
				<?php
				//echo '<pre>';print_r($meta_values);echo'</pre>';
			}
			public function setting_head( $event_id ) {
				$event_label = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_label', 'Events' );
				$event_type  = MPWEM_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
				$buffer_time = MPWEM_Global_Function::get_post_info( $event_id, 'mep_buffer_time', 0 );
				?>
                <div class="_layout_default_xs_mp_zero">
                    <div class="_bg_light_padding">
                        <h4><?php echo esc_html( $event_label ) . ' ' . esc_html__( 'Date & Time Settings', 'mage-eventpress' ); ?></h4>
                        <span class="_mp_zero"><?php esc_html_e( 'Configure Your Date and Time Settings Here', 'mage-eventpress' ); ?></span>
                    </div>
                    <div class="_padding_bt">
                        <label class="_justify_between_align_center_wrap ">
                            <span class="_mr"><?php esc_html_e( 'Event Date Type', 'mage-eventpress' ); ?></span>
                            <select class="formControl" name="mep_enable_recurring" data-collapse-target required>
                                <option disabled selected><?php esc_html_e( 'Please select ...', 'mage-eventpress' ); ?></option>
                                <option value="no" data-option-target="#mep_normal_event" <?php echo esc_attr( $event_type == 'no' ? 'selected' : '' ); ?>><?php esc_html_e( 'Single Event', 'mage-eventpress' ); ?></option>
                                <option value="yes" data-option-target="#mep_particular_event" <?php echo esc_attr( $event_type == 'yes' ? 'selected' : '' ); ?>><?php esc_html_e( 'Particular Event', 'mage-eventpress' ); ?></option>
                                <option value="everyday" data-option-target="#mep_everyday_event" <?php echo esc_attr( $event_type == 'everyday' ? 'selected' : '' ); ?>><?php esc_html_e( 'Repeated Event', 'mage-eventpress' ); ?></option>
                            </select>
                        </label>
                        <span class="label-text"><?php esc_html_e( 'Select your event Date type', 'mage-eventpress' ); ?></span>
                    </div>
                    <div class="_padding_bt">
                        <label class="_justify_between_align_center_wrap">
                            <span class="_mr"><?php esc_html_e( 'Ticket sales close X minutes before the event starts.', 'mage-eventpress' ); ?></span>
                            <input type="number" class="formControl _max_100 number_validation" name='mep_buffer_time' value='<?php echo esc_attr( $buffer_time ); ?>'/>
                        </label>
                        <span class="label-text"><?php esc_html_e( 'Ticket sales close X minutes before the event starts.', 'mage-eventpress' ); ?></span>
                    </div>
                </div>
				<?php
			}
			public function normal_date( $event_id ) {
				$now          = current_time( 'Y-m-d' );
				$event_label = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_label', 'Events' );
				$event_type  = MPWEM_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
				$start_date  = MPWEM_Global_Function::get_post_info( $event_id, 'event_start_date' );
				$start_time  = MPWEM_Global_Function::get_post_info( $event_id, 'event_start_time' );
				$end_date    = MPWEM_Global_Function::get_post_info( $event_id, 'event_end_date' );
				$end_date=strtotime( $end_date )<strtotime($start_date)?$start_date:$end_date;
				$end_time    = MPWEM_Global_Function::get_post_info( $event_id, 'event_end_time' );
				$more_dates  = MPWEM_Global_Function::get_post_info( $event_id, 'mep_event_more_date', [] );
				?>
                <div class="_mt <?php echo esc_attr( $event_type == 'no' ? 'mActive' : '' ); ?>" data-collapse="#mep_normal_event">
                    <div class="_layout_default_xs_mp_zero">
                        <div class="_bg_light_padding">
                            <h4><?php echo esc_html( $event_label ) . ' ' . esc_html__( 'Single Date & Time Settings', 'mage-eventpress' ); ?></h4>
                            <span class="_mp_zero"><?php esc_html_e( 'Configure Your Single Date and Time Settings Here', 'mage-eventpress' ); ?></span>
                        </div>
                        <div class="_padding_bt mpwem_settings_area">
                            <div class="_ov_auto">
                                <table>
                                    <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Start Date', 'mage-eventpress' ); ?></th>
                                        <th><?php esc_html_e( 'Start Time', 'mage-eventpress' ); ?></th>
                                        <th><?php esc_html_e( 'End Date', 'mage-eventpress' ); ?></th>
                                        <th><?php esc_html_e( 'End Time', 'mage-eventpress' ); ?></th>
                                        <th><?php esc_html_e( 'Action', 'mage-eventpress' ); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody class="mpwem_sortable_area mpwem_item_insert">
                                    <tr>
                                        <td><?php

		                                        $date_format  = MPWEM_Global_Function::date_picker_format();
		                                        $now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
		                                        $now_current          = current_time( 'Y-m-d' );
		                                        $hidden_start_date  = $start_date ? date( 'Y-m-d', strtotime( $start_date ) ) : '';
		                                        $visible_start_date = $start_date ? date_i18n( $date_format, strtotime( $start_date ) ) : '';
		                                        $start_year  = date( 'Y', strtotime( $now_current ) );
		                                        $start_month = ( date( 'n', strtotime( $now_current ) ) - 1 );
		                                        $start_day   = date( 'j', strtotime( $now_current ) );
				$mep_hide_old_date = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_hide_old_date', 'yes' );
				if($mep_hide_old_date=='yes'){
	                                        ?>
                                            <label>
                                                <input type="hidden" name="event_start_date_normal" value="<?php echo esc_attr( $hidden_start_date ); ?>"/>
                                                <input type="text" value="<?php echo esc_attr( $visible_start_date ); ?>" class="formControl" id="event_start_date_normal" placeholder="<?php echo esc_attr( $now ); ?>"/>
                                                <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                                            </label>
                                            <script>
                                                jQuery(document).ready(function () {
                                                    jQuery("#event_start_date_normal").datepicker({
                                                        dateFormat: mpwem_date_format,
                                                        minDate: new Date(<?php echo esc_attr( $start_year ); ?>, <?php echo esc_attr( $start_month ); ?>, <?php echo esc_attr( $start_day ); ?>),
                                                        autoSize: true,
                                                        changeMonth: true,
                                                        changeYear: true,
                                                        onSelect: function (dateString, data) {
                                                            let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                                                            jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
                                                        }
                                                    });
                                                });
                                            </script>
				<?php }else{ ?>
                    <label>
                        <input type="hidden" name="event_start_date_normal" value="<?php echo esc_attr( $hidden_start_date ); ?>"/>
                        <input type="text" value="<?php echo esc_attr( $visible_start_date ); ?>" class="formControl new-date_type-new"  placeholder="<?php echo esc_attr( $now ); ?>"/>
                        <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                    </label>
				<?php } ?>
	                                        </td>
                                        <td><?php self::time_item( 'event_start_time_normal', $start_time ); ?></td>
                                        <td class="event_end_date_normal-td"><?php


		                                        $date_format  = MPWEM_Global_Function::date_picker_format();
		                                        $now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
		                                        $hidden_end_date  = $end_date ? date( 'Y-m-d', strtotime( $end_date ) ) : '';
		                                        $visible_end_date = $end_date ? date_i18n( $date_format, strtotime( $end_date ) ) : '';
		                                        $start_year  = date( 'Y', strtotime( $start_date ) );
		                                        $start_month = ( date( 'n', strtotime( $start_date ) ) - 1 );
		                                        $start_day   = date( 'j', strtotime( $start_date ) );
				$mep_hide_old_date = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_hide_old_date', 'yes' );
				if($mep_hide_old_date=='yes'){
	                                        ?>
                                            <label>
                                                <input type="hidden" name="event_end_date_normal" value="<?php echo esc_attr( $hidden_end_date ); ?>"/>
                                                <input type="text" value="<?php echo esc_attr( $visible_end_date ); ?>" class="formControl" id="event_end_date_normal" placeholder="<?php echo esc_attr( $now ); ?>"/>
                                                <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                                            </label>
                                            <script>
                                                jQuery(document).ready(function () {
                                                    jQuery("#event_end_date_normal").datepicker({
                                                        dateFormat: mpwem_date_format,
                                                        minDate: new Date(<?php echo esc_attr( $start_year ); ?>, <?php echo esc_attr( $start_month ); ?>, <?php echo esc_attr( $start_day ); ?>),
                                                        autoSize: true,
                                                        changeMonth: true,
                                                        changeYear: true,
                                                        onSelect: function (dateString, data) {
                                                            let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                                                            jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
                                                        }
                                                    });
                                                });
                                            </script>
				<?php }else{ ?>
                    <label>
                        <input type="hidden" name="event_end_date_normal" value="<?php echo esc_attr( $hidden_end_date ); ?>"/>
                        <input type="text" value="<?php echo esc_attr( $visible_end_date ); ?>" class="formControl new-date_type-new"  placeholder="<?php echo esc_attr( $now ); ?>"/>
                        <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                    </label>
				<?php } ?>
                                        </td>

                                        <td><?php self::time_item( 'event_end_time_normal', $end_time ); ?></td>
                                        <td></td>
                                    </tr>
									<?php if ( is_array($more_dates) && sizeof( $more_dates ) > 0 ) {
                                        $count=0;
                                        ?>
										<?php foreach ( $more_dates as $more_date ) { ?>
											<?php $more_start_date = array_key_exists( 'event_more_start_date', $more_date ) ? $more_date['event_more_start_date'] : ''; ?>
											<?php $more_start_time = array_key_exists( 'event_more_start_time', $more_date ) ? $more_date['event_more_start_time'] : ''; ?>
											<?php $more_end_date = array_key_exists( 'event_more_end_date', $more_date ) ? $more_date['event_more_end_date'] : ''; ?>
											<?php $more_end_time = array_key_exists( 'event_more_end_time', $more_date ) ? $more_date['event_more_end_time'] : ''; ?>
                                            <tr class="mpwem_remove_area">
                                                <td><?php
                                                        //self::date_item( 'event_more_start_date_normal[]', $more_start_date );

		                                                $date_format  = MPWEM_Global_Function::date_picker_format();
		                                                $now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
		                                                $hidden_more_start_date   = $more_start_date ? date( 'Y-m-d', strtotime( $more_start_date ) ) : '';
		                                                $visible_more_start_date = $more_start_date ? date_i18n( $date_format, strtotime( $more_start_date ) ) : '';
		                                                $_more_start_date=strtotime( $more_start_date )<strtotime(current_time( 'Y-m-d' ) )?current_time( 'Y-m-d' ) :$more_start_date;
		                                                $start_year  = date( 'Y', strtotime( $_more_start_date ) );
		                                                $start_month = ( date( 'n', strtotime( $_more_start_date ) ) - 1 );
		                                                $start_day   = date( 'j', strtotime( $_more_start_date ) );
                                                        $id='event_more_start_date_normal_'.$count;
		                                                $mep_hide_old_date = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_hide_old_date', 'yes' );
                                                        if($mep_hide_old_date=='yes'){
	                                                ?>
                                                    <label>
                                                        <input type="hidden" name="event_more_start_date_normal[]" value="<?php echo esc_attr( $hidden_more_start_date ); ?>"/>
                                                        <input type="text" value="<?php echo esc_attr( $visible_more_start_date ); ?>" class="formControl" id="<?php echo esc_attr($id); ?>"  placeholder="<?php echo esc_attr( $now ); ?>"/>
                                                        <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                                                    </label>
                                                            <?php }else{ ?>
                                                            <label>
                                                                <input type="hidden" name="event_more_start_date_normal[]" value="<?php echo esc_attr( $hidden_more_start_date ); ?>"/>
                                                                <input type="text" value="<?php echo esc_attr( $visible_more_start_date ); ?>" class="formControl new-date_type-new"   placeholder="<?php echo esc_attr( $now ); ?>"/>
                                                                <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                                                            </label>
                                                            <?php } ?>
                                                    <script>
                                                        jQuery(document).ready(function () {
                                                            jQuery("#<?php echo esc_attr($id); ?>").datepicker({
                                                                dateFormat: mpwem_date_format,
                                                                minDate: new Date(<?php echo esc_attr( $start_year ); ?>, <?php echo esc_attr( $start_month ); ?>, <?php echo esc_attr( $start_day ); ?>),
                                                                autoSize: true,
                                                                changeMonth: true,
                                                                changeYear: true,
                                                                onSelect: function (dateString, data) {
                                                                    let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                                                                    jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
                                                                }
                                                            });
                                                        });
                                                    </script>
                                                </td>
                                                <td><?php self::time_item( 'event_more_start_time_normal[]', $more_start_time ); ?></td>
                                                <td><?php
                                                        //self::date_item( 'event_more_end_date_normal[]', $more_end_date );

                                                        $date_format  = MPWEM_Global_Function::date_picker_format();
		                                                $now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
		                                                $hidden_more_end_date   = $more_end_date ? date( 'Y-m-d', strtotime( $more_end_date ) ) : '';
		                                                $visible_more_end_date = $more_end_date ? date_i18n( $date_format, strtotime( $more_end_date ) ) : '';
		                                                $_more_end_date=strtotime( $more_end_date )<strtotime($_more_start_date )?$_more_start_date :$more_end_date;
		                                                $start_year  = date( 'Y', strtotime( $_more_end_date ) );
		                                                $start_month = ( date( 'n', strtotime( $_more_end_date ) ) - 1 );
		                                                $start_day   = date( 'j', strtotime( $_more_end_date ) );
                                                        $id='event_more_end_date_normal_'.$count;
                                                        $count++;
						$mep_hide_old_date = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_hide_old_date', 'yes' );
						if($mep_hide_old_date=='yes'){
	                                                ?>
                                                    <label>
                                                        <input type="hidden" name="event_more_end_date_normal[]" value="<?php echo esc_attr( $hidden_more_end_date ); ?>"/>
                                                        <input type="text" value="<?php echo esc_attr( $visible_more_end_date ); ?>" class="formControl" id="<?php echo esc_attr($id); ?>"  placeholder="<?php echo esc_attr( $now ); ?>"/>
                                                        <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                                                    </label>
                                                    <script>
                                                        jQuery(document).ready(function () {
                                                            jQuery("#<?php echo esc_attr($id); ?>").datepicker({
                                                                dateFormat: mpwem_date_format,
                                                                minDate: new Date(<?php echo esc_attr( $start_year ); ?>, <?php echo esc_attr( $start_month ); ?>, <?php echo esc_attr( $start_day ); ?>),
                                                                autoSize: true,
                                                                changeMonth: true,
                                                                changeYear: true,
                                                                onSelect: function (dateString, data) {
                                                                    let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                                                                    jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
                                                                }
                                                            });
                                                        });
                                                    </script>
						<?php }else{ ?>
                            <label>
                                <input type="hidden" name="event_more_end_date_normal[]" value="<?php echo esc_attr( $hidden_more_end_date ); ?>"/>
                                <input type="text" value="<?php echo esc_attr( $visible_more_end_date ); ?>" class="formControl new-date_type-new"  placeholder="<?php echo esc_attr( $now ); ?>"/>
                                <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                            </label>
						<?php } ?>
                                                </td>
                                                <td><?php self::time_item( 'event_more_end_time_normal[]', $more_end_time ); ?></td>
                                                <td><?php MPWEM_Custom_Layout::move_remove_button(); ?></td>
                                            </tr>
										<?php } ?>
									<?php } ?>
                                    </tbody>
                                </table>
                            </div>
							<?php MPWEM_Custom_Layout::add_new_button( esc_html__( 'Add More Dates', 'mage-eventpress' ) ); ?>
                            <div class="mpwem_hidden_content">
                                <table>
                                    <tbody class="mpwem_hidden_item">
                                    <tr class="mpwem_remove_area">
                                        <td><?php
                                                //self::date_item( 'event_more_start_date_normal[]', '' );
				$mep_hide_old_date = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_hide_old_date', 'yes' );
				if($mep_hide_old_date=='yes'){
                                                ?>
                                            <label>
                                                <input type="hidden" name="event_more_start_date_normal[]" value=""/>
                                                <input type="text" value="" class="formControl new-date_type" placeholder="<?php echo esc_attr( $now ); ?>" />
                                                <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                                            </label>
				<?php }else{ ?>
                    <label>
                        <input type="hidden" name="event_more_start_date_normal[]" value=""/>
                        <input type="text" value="" class="formControl new-date_type-new" placeholder="<?php echo esc_attr( $now ); ?>" />
                        <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                    </label>
				<?php } ?>
                                        </td>
                                        <td><?php self::time_item( 'event_more_start_time_normal[]', '' ); ?></td>
                                        <td><?php

                                                //self::date_item( 'event_more_end_date_normal[]', '' );

                                                ?>
                                            <label>
                                                <input type="hidden" name="event_more_end_date_normal[]" value=""/>
                                                <input type="text" value="" class="formControl " placeholder="<?php echo esc_attr( $now ); ?>" />
                                                <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                                            </label>
                                        </td>
                                        <td><?php self::time_item( 'event_more_end_time_normal[]', '' ); ?></td>
                                        <td><?php MPWEM_Custom_Layout::move_remove_button(); ?></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function particular_section( $post_id ) {
				$event_label = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_label', 'Events' );
				$event_type  = MPWEM_Global_Function::get_post_info( $post_id, 'mep_enable_recurring', 'no' );
				$start_date  = MPWEM_Global_Function::get_post_info( $post_id, 'event_start_date' );
				$start_time  = MPWEM_Global_Function::get_post_info( $post_id, 'event_start_time' );
				$end_date    = MPWEM_Global_Function::get_post_info( $post_id, 'event_end_date' );
				$end_time    = MPWEM_Global_Function::get_post_info( $post_id, 'event_end_time' );
				$more_dates  = MPWEM_Global_Function::get_post_info( $post_id, 'mep_event_more_date', [] );
				?>
                <div class="_mt <?php echo esc_attr( $event_type == 'yes' ? 'mActive' : '' ); ?>" data-collapse="#mep_particular_event">
                    <div class="_layout_default_xs_mp_zero">
                        <div class="_bg_light_padding">
                            <h4><?php echo esc_html( $event_label ) . ' ' . esc_html__( 'Particular Date & Time Settings', 'mage-eventpress' ); ?></h4>
                            <span class="_mp_zero"><?php esc_html_e( 'Configure Your Particular Date and Time Settings Here', 'mage-eventpress' ); ?></span>
                        </div>
                        <div class="_padding_bt mpwem_settings_area">
                            <div class="_ov_auto">
                                <table>
                                    <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Start Date', 'mage-eventpress' ); ?></th>
                                        <th><?php esc_html_e( 'Start Time', 'mage-eventpress' ); ?></th>
                                        <th><?php esc_html_e( 'End Date', 'mage-eventpress' ); ?></th>
                                        <th><?php esc_html_e( 'End Time', 'mage-eventpress' ); ?></th>
										<?php do_action( 'mep_date_table_head', $post_id ); ?>
                                        <th><?php esc_html_e( 'Action', 'mage-eventpress' ); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody class="mpwem_sortable_area mpwem_item_insert">
                                    <tr>
                                        <td><?php
                                                //self::date_item( 'event_start_date', $start_date );

		                                        $date_format  = MPWEM_Global_Function::date_picker_format();
		                                        $now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
		                                        $now_current          = current_time( 'Y-m-d' );
		                                        $hidden_start_date  = $start_date ? date( 'Y-m-d', strtotime( $start_date ) ) : '';
		                                        $visible_start_date = $start_date ? date_i18n( $date_format, strtotime( $start_date ) ) : '';
		                                        $start_year  = date( 'Y', strtotime( $now_current ) );
		                                        $start_month = ( date( 'n', strtotime( $now_current ) ) - 1 );
		                                        $start_day   = date( 'j', strtotime( $now_current ) );
				$mep_hide_old_date = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_hide_old_date', 'yes' );
				if($mep_hide_old_date=='yes'){
                                                ?>
                                            <label>
                                                <input type="hidden" name="event_start_date" value="<?php echo esc_attr( $hidden_start_date ); ?>"/>
                                                <input type="text" value="<?php echo esc_attr( $visible_start_date ); ?>" class="formControl" id="event_start_date" placeholder="<?php echo esc_attr( $now ); ?>"/>
                                                <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                                            </label>
                                            <script>
                                                jQuery(document).ready(function () {
                                                    jQuery("#event_start_date").datepicker({
                                                        dateFormat: mpwem_date_format,
                                                        minDate: new Date(<?php echo esc_attr( $start_year ); ?>, <?php echo esc_attr( $start_month ); ?>, <?php echo esc_attr( $start_day ); ?>),
                                                        autoSize: true,
                                                        changeMonth: true,
                                                        changeYear: true,
                                                        onSelect: function (dateString, data) {
                                                            let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                                                            jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
                                                        }
                                                    });
                                                });
                                            </script>
                    <?php }else{ ?>
                    <label>
                        <input type="hidden" name="event_start_date" value="<?php echo esc_attr( $hidden_start_date ); ?>"/>
                        <input type="text" value="<?php echo esc_attr( $visible_start_date ); ?>" class="formControl new-date_type-new"  placeholder="<?php echo esc_attr( $now ); ?>"/>
                        <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                    </label>
                    <?php } ?>
                                        </td>
                                        <td><?php self::time_item( 'event_start_time', $start_time ); ?></td>
                                        <td class="event_end_date-td"><?php

                                                //self::date_item( 'event_end_date', $end_date );


		                                        $date_format  = MPWEM_Global_Function::date_picker_format();
		                                        $now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
		                                        $hidden_end_date  = $end_date ? date( 'Y-m-d', strtotime( $end_date ) ) : '';
		                                        $visible_end_date = $end_date ? date_i18n( $date_format, strtotime( $end_date ) ) : '';
		                                        $start_year  = date( 'Y', strtotime( $start_date ) );
		                                        $start_month = ( date( 'n', strtotime( $start_date ) ) - 1 );
		                                        $start_day   = date( 'j', strtotime( $start_date ) );
				$mep_hide_old_date = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_hide_old_date', 'yes' );
				if($mep_hide_old_date=='yes'){
                                                ?>

                                            <label>
                                                <input type="hidden" name="event_end_date" value="<?php echo esc_attr( $hidden_end_date ); ?>"/>
                                                <input type="text" value="<?php echo esc_attr( $visible_end_date ); ?>" class="formControl" id="event_end_date" placeholder="<?php echo esc_attr( $now ); ?>"/>
                                                <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                                            </label>
                                            <script>
                                                jQuery(document).ready(function () {
                                                    jQuery("#event_end_date").datepicker({
                                                        dateFormat: mpwem_date_format,
                                                        minDate: new Date(<?php echo esc_attr( $start_year ); ?>, <?php echo esc_attr( $start_month ); ?>, <?php echo esc_attr( $start_day ); ?>),
                                                        autoSize: true,
                                                        changeMonth: true,
                                                        changeYear: true,
                                                        onSelect: function (dateString, data) {
                                                            let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                                                            jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
                                                        }
                                                    });
                                                });
                                            </script>
				<?php }else{ ?>
                    <label>
                        <input type="hidden" name="event_end_date" value="<?php echo esc_attr( $hidden_end_date ); ?>"/>
                        <input type="text" value="<?php echo esc_attr( $visible_end_date ); ?>" class="formControl new-date_type-new" placeholder="<?php echo esc_attr( $now ); ?>"/>
                        <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                    </label>
				<?php } ?>
                                        </td>
                                        <td><?php self::time_item( 'event_end_time', $end_time ); ?></td>
										<?php do_action( 'mep_date_table_body_default_date', $post_id ); ?>
                                        <td></td>
                                    </tr>
									<?php if ( is_array($more_dates) && sizeof( $more_dates ) > 0 ) {
										$count=0;
                                        ?>
										<?php foreach ( $more_dates as $more_date ) { ?>
											<?php $more_start_date = array_key_exists( 'event_more_start_date', $more_date ) ? $more_date['event_more_start_date'] : ''; ?>
											<?php $more_start_time = array_key_exists( 'event_more_start_time', $more_date ) ? $more_date['event_more_start_time'] : ''; ?>
											<?php $more_end_date = array_key_exists( 'event_more_end_date', $more_date ) ? $more_date['event_more_end_date'] : ''; ?>
											<?php $more_end_time = array_key_exists( 'event_more_end_time', $more_date ) ? $more_date['event_more_end_time'] : ''; ?>
                                            <tr class="mpwem_remove_area">
                                                <td>
                                                    <?php
                                                        //self::date_item( 'event_more_start_date[]', $more_start_date );


		                                                $date_format  = MPWEM_Global_Function::date_picker_format();
		                                                $now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
		                                                $hidden_more_start_date   = $more_start_date ? date( 'Y-m-d', strtotime( $more_start_date ) ) : '';
		                                                $visible_more_start_date = $more_start_date ? date_i18n( $date_format, strtotime( $more_start_date ) ) : '';
		                                                $_more_start_date=strtotime( $more_start_date )<strtotime(current_time( 'Y-m-d' ) )?current_time( 'Y-m-d' ) :$more_start_date;
		                                                $start_year  = date( 'Y', strtotime( $_more_start_date ) );
		                                                $start_month = ( date( 'n', strtotime( $_more_start_date ) ) - 1 );
		                                                $start_day   = date( 'j', strtotime( $_more_start_date ) );
		                                                $id='event_more_start_date_'.$count;
					$mep_hide_old_date = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_hide_old_date', 'yes' );
				if($mep_hide_old_date=='yes'){
                                                        ?>

                                                    <label>
                                                        <input type="hidden" name="event_more_start_date[]" value="<?php echo esc_attr( $hidden_more_start_date ); ?>"/>
                                                        <input type="text" value="<?php echo esc_attr( $visible_more_start_date ); ?>" class="formControl" id="<?php echo esc_attr($id); ?>"  placeholder="<?php echo esc_attr( $now ); ?>"/>
                                                        <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                                                    </label>
                                                    <script>
                                                        jQuery(document).ready(function () {
                                                            jQuery("#<?php echo esc_attr($id); ?>").datepicker({
                                                                dateFormat: mpwem_date_format,
                                                                minDate: new Date(<?php echo esc_attr( $start_year ); ?>, <?php echo esc_attr( $start_month ); ?>, <?php echo esc_attr( $start_day ); ?>),
                                                                autoSize: true,
                                                                changeMonth: true,
                                                                changeYear: true,
                                                                onSelect: function (dateString, data) {
                                                                    let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                                                                    jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
                                                                }
                                                            });
                                                        });
                                                    </script>
                    <?php }else{ ?>
                    <label>
                        <input type="hidden" name="event_more_start_date[]" value="<?php echo esc_attr( $hidden_more_start_date ); ?>"/>
                        <input type="text" value="<?php echo esc_attr( $visible_more_start_date ); ?>" class="formControl new-date_type-new" placeholder="<?php echo esc_attr( $now ); ?>"/>
                        <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                    </label>
                    <?php } ?>
                                                </td>
                                                <td><?php self::time_item( 'event_more_start_time[]', $more_start_time ); ?></td>
                                                <td><?php

                                                        //self::date_item( 'event_more_end_date[]', $more_end_date );


		                                                $date_format  = MPWEM_Global_Function::date_picker_format();
		                                                $now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
		                                                $hidden_more_end_date   = $more_end_date ? date( 'Y-m-d', strtotime( $more_end_date ) ) : '';
		                                                $visible_more_end_date = $more_end_date ? date_i18n( $date_format, strtotime( $more_end_date ) ) : '';
		                                                $_more_end_date=strtotime( $more_end_date )<strtotime($_more_start_date )?$_more_start_date :$more_end_date;
		                                                $start_year  = date( 'Y', strtotime( $_more_end_date ) );
		                                                $start_month = ( date( 'n', strtotime( $_more_end_date ) ) - 1 );
		                                                $start_day   = date( 'j', strtotime( $_more_end_date ) );
		                                                $id='event_more_end_date_'.$count;
		                                                $count++;
					$mep_hide_old_date = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_hide_old_date', 'yes' );
				if($mep_hide_old_date=='yes'){
                                                        ?>

                                                    <label>
                                                        <input type="hidden" name="event_more_end_date[]" value="<?php echo esc_attr( $hidden_more_end_date ); ?>"/>
                                                        <input type="text" value="<?php echo esc_attr( $visible_more_end_date ); ?>" class="formControl" id="<?php echo esc_attr($id); ?>"  placeholder="<?php echo esc_attr( $now ); ?>"/>
                                                        <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                                                    </label>
                                                    <script>
                                                        jQuery(document).ready(function () {
                                                            jQuery("#<?php echo esc_attr($id); ?>").datepicker({
                                                                dateFormat: mpwem_date_format,
                                                                minDate: new Date(<?php echo esc_attr( $start_year ); ?>, <?php echo esc_attr( $start_month ); ?>, <?php echo esc_attr( $start_day ); ?>),
                                                                autoSize: true,
                                                                changeMonth: true,
                                                                changeYear: true,
                                                                onSelect: function (dateString, data) {
                                                                    let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                                                                    jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
                                                                }
                                                            });
                                                        });
                                                    </script>
				<?php }else{ ?>
                    <label>
                        <input type="hidden" name="event_more_end_date[]" value="<?php echo esc_attr( $hidden_more_end_date ); ?>"/>
                        <input type="text" value="<?php echo esc_attr( $visible_more_end_date ); ?>" class="formControl new-date_type-new"   placeholder="<?php echo esc_attr( $now ); ?>"/>
                        <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                    </label>
				<?php }?>
                                                </td>
                                                <td><?php self::time_item( 'event_more_end_time[]', $more_end_time ); ?></td>
												<?php do_action( 'mep_date_table_body_more_date', $post_id, $more_date ); ?>
                                                <td><?php MPWEM_Custom_Layout::move_remove_button(); ?></td>
                                            </tr>
										<?php } ?>
									<?php } ?>
                                    </tbody>
                                </table>
                            </div>
							<?php MPWEM_Custom_Layout::add_new_button( esc_html__( 'Add More Dates', 'mage-eventpress' ) ); ?>
                            <div class="mpwem_hidden_content">
                                <table>
                                    <tbody class="mpwem_hidden_item">
                                    <tr class="mpwem_remove_area">
                                        <td>
                                            <?php //self::date_item( 'event_more_start_date[]', '' );
                                                //
				$mep_hide_old_date = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_hide_old_date', 'yes' );
				if($mep_hide_old_date=='yes'){
                                                 ?>
                                            <label>
                                                <input type="hidden" name="event_more_start_date[]" value=""/>
                                                <input type="text" value="" class="formControl  new-particular-date_type" placeholder="<?php echo esc_attr( $now ); ?>" />
                                                <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                                            </label>
				<?php }else{ ?>
                    <label>
                        <input type="hidden" name="event_more_start_date[]" value=""/>
                        <input type="text" value="" class="formControl  new-date_type-new" placeholder="<?php echo esc_attr( $now ); ?>" />
                        <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                    </label>
				<?php } ?>
                                        </td>
                                        <td><?php self::time_item( 'event_more_start_time[]', '' ); ?></td>
                                        <td>
                                            <?php //self::date_item( 'event_more_end_date[]', '' ); ?>
                                            <label>
                                                <input type="hidden" name="event_more_end_date[]" value=""/>
                                                <input type="text" value="" class="formControl " placeholder="<?php echo esc_attr( $now ); ?>" />
                                                <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                                            </label>
                                        </td>
                                        <td><?php self::time_item( 'event_more_end_time[]', '' ); ?></td>
										<?php do_action( 'mep_date_table_empty', $post_id ); ?>
                                        <td><?php MPWEM_Custom_Layout::move_remove_button(); ?></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
	                        <?php
		                        //self::date_item( 'event_start_date', $start_date );

		                        $date_format  = MPWEM_Global_Function::date_picker_format();
		                        $now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
		                        $now_current          = current_time( 'Y-m-d' );
		                        $hidden_start_date  = $start_date ? date( 'Y-m-d', strtotime( $start_date ) ) : '';
		                        $visible_start_date = $start_date ? date_i18n( $date_format, strtotime( $start_date ) ) : '';
		                        $start_year  = date( 'Y', strtotime( $now_current ) );
		                        $start_month = ( date( 'n', strtotime( $now_current ) ) - 1 );
		                        $start_day   = date( 'j', strtotime( $now_current ) );

	                        ?>
                            <script>
                                jQuery(document).ready(function () {
                                    jQuery("#mep_load_date_picker").datepicker({
                                        dateFormat: mpwem_date_format,
                                        minDate: new Date(<?php echo esc_attr( $start_year ); ?>, <?php echo esc_attr( $start_month ); ?>, <?php echo esc_attr( $start_day ); ?>),
                                        autoSize: true,
                                        changeMonth: true,
                                        changeYear: true,
                                        onSelect: function (dateString, data) {
                                            let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                                            jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
                                        }
                                    });
                                });
                            </script>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function repeat_section( $event_id ) {
				$event_label = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_label', 'Events' );
				$event_type  = MPWEM_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
				?>
                <div class="_mt <?php echo esc_attr( $event_type == 'everyday' ? 'mActive' : '' ); ?>" data-collapse="#mep_everyday_event">
                    <div class="_layout_default_xs_mp_zero">
                        <div class="_bg_light_padding">
                            <h4><?php echo esc_html( $event_label ) . ' ' . esc_html__( 'Repeated Date & Time Settings', 'mage-eventpress' ); ?></h4>
                            <span class="_mp_zero"><?php esc_html_e( 'Configure Your Repeated Date and Time Settings Here', 'mage-eventpress' ); ?></span>
                        </div>
						<?php $this->date_time_section( $event_id ); ?>
						<?php $this->off_days_section( $event_id ); ?>
                    </div>
					<?php $this->time_settings_section( $event_id ); ?>
					<?php $this->special_on_dates_setting( $event_id ); ?>
                </div>
				<?php
			}
			public function date_time_section( $post_id ) {
				$start_date = MPWEM_Global_Function::get_post_info( $post_id, 'event_start_date' );
				$start_time = MPWEM_Global_Function::get_post_info( $post_id, 'event_start_time' );
				$end_date   = MPWEM_Global_Function::get_post_info( $post_id, 'event_end_date' );
				$end_time   = MPWEM_Global_Function::get_post_info( $post_id, 'event_end_time' );
				$periods    = MPWEM_Global_Function::get_post_info( $post_id, 'mep_repeated_periods', 1 );
				?>
                <div class="_padding_bt">
                    <div class="_justify_between_align_center_wrap ">
                        <label><span class="_mr"><?php esc_html_e( 'Start Date & Time', 'mage-eventpress' ); ?></span></label>
                        <div class="dFlex">
							<?php //self::date_item( 'event_start_date_everyday', $start_date ); ?>
	                        <?php

		                        $date_format  = MPWEM_Global_Function::date_picker_format();
		                        $now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
		                        $now_current          = current_time( 'Y-m-d' );
		                        $hidden_start_date  = $start_date ? date( 'Y-m-d', strtotime( $start_date ) ) : '';
		                        $visible_start_date = $start_date ? date_i18n( $date_format, strtotime( $start_date ) ) : '';
		                        $start_year  = date( 'Y', strtotime( $now_current ) );
		                        $start_month = ( date( 'n', strtotime( $now_current ) ) - 1 );
		                        $start_day   = date( 'j', strtotime( $now_current ) );
				$mep_hide_old_date = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_hide_old_date', 'yes' );
				if($mep_hide_old_date=='yes'){
	                        ?>
                            <label>
                                <input type="hidden" name="event_start_date_everyday" value="<?php echo esc_attr( $hidden_start_date ); ?>"/>
                                <input type="text" value="<?php echo esc_attr( $visible_start_date ); ?>" class="formControl" id="event_start_date_everyday" placeholder="<?php echo esc_attr( $now ); ?>"/>
                                <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                            </label>
                            <script>
                                jQuery(document).ready(function () {
                                    jQuery("#event_start_date_everyday").datepicker({
                                        dateFormat: mpwem_date_format,
                                        minDate: new Date(<?php echo esc_attr( $start_year ); ?>, <?php echo esc_attr( $start_month ); ?>, <?php echo esc_attr( $start_day ); ?>),
                                        autoSize: true,
                                        changeMonth: true,
                                        changeYear: true,
                                        onSelect: function (dateString, data) {
                                            let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                                            jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
                                        }
                                    });
                                });
                            </script>
<?php }else{ ?>
<label>
                                <input type="hidden" name="event_start_date_everyday" value="<?php echo esc_attr( $hidden_start_date ); ?>"/>
                                <input type="text" value="<?php echo esc_attr( $visible_start_date ); ?>" class="formControl  new-date_type-new" placeholder="<?php echo esc_attr( $now ); ?>"/>
                                <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                            </label>
<?php } ?>
							<?php self::time_item( 'event_start_time_everyday', $start_time ); ?>
                        </div>
                    </div>
                    <span class="label-text"><?php esc_html_e( 'Select Start Date & Time', 'mage-eventpress' ); ?></span>
                </div>
                <div class="_padding_bt">
                    <div class="_justify_between_align_center_wrap ">
                        <label><span class="_mr"><?php esc_html_e( 'End Date & Time', 'mage-eventpress' ); ?></span></label>
                        <div class="dFlex">
							<?php //self::date_item( 'event_end_date_everyday', $end_date ); ?>
<div class="mep_load_every_day">
	                        <?php

		                        $end_date=strtotime( $end_date )<strtotime($start_date)?$start_date:$end_date;
		                        $date_format  = MPWEM_Global_Function::date_picker_format();
		                        $now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
		                        $hidden_end_date  = $end_date ? date( 'Y-m-d', strtotime( $end_date ) ) : '';
		                        $visible_end_date = $end_date ? date_i18n( $date_format, strtotime( $end_date ) ) : '';
		                        $start_year  = date( 'Y', strtotime( $start_date ) );
		                        $start_month = ( date( 'n', strtotime( $start_date ) ) - 1 );
		                        $start_day   = date( 'j', strtotime( $start_date ) );
	                        ?>
                            <label>
                                <input type="hidden" name="event_end_date_everyday" value="<?php echo esc_attr( $hidden_end_date ); ?>"/>
                                <input type="text" value="<?php echo esc_attr( $visible_end_date ); ?>" class="formControl" id="event_end_date_everyday" placeholder="<?php echo esc_attr( $now ); ?>"/>
                                <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                            </label>
                            <script>
                                jQuery(document).ready(function () {
                                    jQuery("#event_end_date_everyday").datepicker({
                                        dateFormat: mpwem_date_format,
                                        minDate: new Date(<?php echo esc_attr( $start_year ); ?>, <?php echo esc_attr( $start_month ); ?>, <?php echo esc_attr( $start_day ); ?>),
                                        autoSize: true,
                                        changeMonth: true,
                                        changeYear: true,
                                        onSelect: function (dateString, data) {
                                            let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                                            jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
                                        }
                                    });
                                });
                            </script>
</div>
							<?php self::time_item( 'event_end_time_everyday', $end_time ); ?>
                        </div>
                    </div>
                    <span class="label-text"><?php esc_html_e( 'Select End Date & Time', 'mage-eventpress' ); ?></span>
                </div>
                <div class="_padding_bt">
                    <label class="_justify_between_align_center_wrap ">
                        <span class="_mr"><?php esc_html_e( 'After Repeated Days', 'mage-eventpress' ); ?></span>
                        <input type="number" class="formControl _max_100 number_validation" name='mep_repeated_periods' value='<?php echo esc_attr( $periods ); ?>'/>
                    </label>
                    <span class="label-text"><?php esc_html_e( 'Select After Repeated Days', 'mage-eventpress' ); ?></span>
                </div>
				<?php
			}
			public function off_days_section( $post_id ) {
				$off_day_array = MPWEM_Global_Function::get_post_info( $post_id, 'mep_ticket_offdays' );
				if ( ! is_array( $off_day_array ) ) {
					$maybe_unserialized = @unserialize( $off_day_array );
					if ( is_array( $maybe_unserialized ) ) {
						$off_day_array = $maybe_unserialized;
					} else {
						$off_day_array = explode( ',', (string) $off_day_array );
					}
				}
				$off_days = $off_day_array ? implode( ',', $off_day_array ) : '';
				$days     = MPWEM_Global_Function::week_day();
				?>
                <div class="_padding_bt">
                    <div class=" _justify_between_align_center_wrap">
                        <label><span class="_mr"><?php esc_html_e( 'Ticket Off days Setting', 'mage-eventpress' ); ?></span></label>
                        <div class="_dFlex groupCheckBox">
                            <input type="hidden" name="mep_ticket_offdays" value="<?php echo esc_attr( $off_days ); ?>"/>
							<?php foreach ( $days as $key => $day ) { ?>
                                <label class="customCheckboxLabel ">
                                    <input type="checkbox" <?php echo esc_attr( in_array( $key, $off_day_array ) ? 'checked' : '' ); ?> data-checked="<?php echo esc_attr( $key ); ?>"/>
                                    <span class="customCheckbox"><?php echo esc_html( $day ); ?></span>
                                </label>
							<?php } ?>
                        </div>
                    </div>
                    <span class="label-text"><?php esc_html_e( 'Select Off days', 'mage-eventpress' ); ?></span>
                </div>
                <div class="_padding_bt">
                    <div class="justify_between">
                        <div class="fdColumn">
                            <label><span><?php esc_html_e( 'Ticket Off Dates Setting', 'mage-eventpress' ); ?></span></label>
                            <span class="label-text"><?php esc_html_e( 'Configure Tour Off Dates', 'mage-eventpress' ); ?></span>
                        </div>
                        <div class="mpwem_settings_area">
                            <div class="mpwem_item_insert mpwem_sortable_area">
								<?php
                                    $all_off_dates = MPWEM_Global_Function::get_post_info( $post_id, 'mep_ticket_off_dates', array() );
                                    $off_dates = array();
                                    if ( is_array( $all_off_dates ) ) {
                                        foreach ( $all_off_dates as $off_date ) {
                                            if ( is_array( $off_date ) && isset( $off_date['mep_ticket_off_date'] ) ) {
                                                $off_dates[] = $off_date['mep_ticket_off_date'];
                                            }
                                            // যদি string হয় (backward compatibility)
                                            elseif ( is_string( $off_date ) ) {
                                                $off_dates[] = $off_date;
                                            }
                                        }
                                    }
                                    if ( ! empty( $off_dates ) ) {
                                        foreach ( $off_dates as $off_date ) {
                                            if ( ! empty( $off_date ) ) {
                                                self::off_date_item( $off_date );
                                            }
                                        }
                                    }
								?>
                            </div>
							<?php MPWEM_Custom_Layout::add_new_button( esc_html__( 'Add Off Date', 'mage-eventpress' ) ); ?>
                            <div class="mpwem_hidden_content">
                                <div class="mpwem_hidden_item">
									<?php self::off_date_item(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function off_date_item( $date = '' ) {
				$date_format  = MPWEM_Global_Function::date_picker_format();
				$now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
				$hidden_date  = $date ? date_i18n( 'Y-m-d', strtotime( $date ) ) : '';
				$visible_date = $date ? date_i18n( $date_format, strtotime( $date ) ) : '';
				?>
                <div class="mpwem_remove_area _mt_xs">
                    <div class="_dFlex">
                        <label>
                            <input type="hidden" name="mep_ticket_off_dates[]" value="<?php echo esc_attr( $hidden_date ); ?>"/>
                            <input value="<?php echo esc_attr( $visible_date ); ?>" class="formControl date_type" placeholder="<?php echo esc_attr( $now ); ?>"/>
                        </label>
						<?php MPWEM_Custom_Layout::move_remove_button(); ?>
                    </div>
                </div>
				<?php
			}
			public function time_settings_section( $post_id ) {
				$display_time = MPWEM_Global_Function::get_post_info( $post_id, 'mep_disable_ticket_time', 'no' );
				?>
                <section class="bg-light" style="margin-top: 20px;">
                    <h2><?php esc_html_e( 'Time Settings', 'mage-eventpress' ) ?></h2>
                    <span><?php esc_html_e( 'Configure Event Locations and Virtual Venues', 'mage-eventpress' ) ?></span>
                </section>
                <section>
                    <div class="mpev-label">
                        <div>
                            <h2><span><?php esc_html_e( 'Display Time?', 'mage-eventpress' ); ?> </span></h2>
                            <span class="label-text"><?php esc_html_e( 'You can change the date and time format by going to the settings', 'mage-eventpress' ); ?></span>
                        </div>
                        <label class="mpev-switch">
                            <input type="checkbox" name="mep_disable_ticket_time" value="<?php echo esc_attr( $display_time ); ?>" <?php echo esc_attr( ( $display_time == 'yes' ) ? 'checked' : '' ); ?> data-collapse-target="#mep_disable_ticket_time" data-toggle-values="yes,no">
                            <span class="mpev-slider"></span>
                        </label>
                    </div>
                </section>
                <div class="mpwem_style">
                    <section style="display:<?php echo esc_attr( $display_time == 'yes' ? 'block' : 'none' ); ?>" id="mep_disable_ticket_time">
                        <div class="mpTabs topTabs tabBorder">
                            <ul class="tabLists">
                                <li data-tabs-target="#mep_ticket_times_global">
									<?php esc_html_e( 'Default', 'mage-eventpress' ); ?>
                                </li>
                                <li data-tabs-target="#mep_ticket_times_sat">
									<?php esc_html_e( 'Saturday', 'mage-eventpress' ); ?>
                                </li>
                                <li data-tabs-target="#mep_ticket_times_sun">
									<?php esc_html_e( 'Sunday', 'mage-eventpress' ); ?>
                                </li>
                                <li data-tabs-target="#mep_ticket_times_mon">
									<?php esc_html_e( 'Monday', 'mage-eventpress' ); ?>
                                </li>
                                <li data-tabs-target="#mep_ticket_times_tue">
									<?php esc_html_e( 'Tuesday', 'mage-eventpress' ); ?>
                                </li>
                                <li data-tabs-target="#mep_ticket_times_wed">
									<?php esc_html_e( 'Wednesday', 'mage-eventpress' ); ?>
                                </li>
                                <li data-tabs-target="#mep_ticket_times_thu">
									<?php esc_html_e( 'Thursday', 'mage-eventpress' ); ?>
                                </li>
                                <li data-tabs-target="#mep_ticket_times_fri">
									<?php esc_html_e( 'Friday', 'mage-eventpress' ); ?>
                                </li>
                            </ul>
                            <div class="tabsContent">
                                <div class="tabsItem" data-tabs="#mep_ticket_times_global">
									<?php $this->time_line( $post_id, 'mep_ticket_times_global' ); ?>
                                </div>
                                <div class="tabsItem" data-tabs="#mep_ticket_times_sat">
									<?php $this->time_line( $post_id, 'mep_ticket_times_sat' ); ?>
                                </div>
                                <div class="tabsItem" data-tabs="#mep_ticket_times_sun">
									<?php $this->time_line( $post_id, 'mep_ticket_times_sun' ); ?>
                                </div>
                                <div class="tabsItem" data-tabs="#mep_ticket_times_mon">
									<?php $this->time_line( $post_id, 'mep_ticket_times_mon' ); ?>
                                </div>
                                <div class="tabsItem" data-tabs="#mep_ticket_times_tue">
									<?php $this->time_line( $post_id, 'mep_ticket_times_tue' ); ?>
                                </div>
                                <div class="tabsItem" data-tabs="#mep_ticket_times_wed">
									<?php $this->time_line( $post_id, 'mep_ticket_times_wed' ); ?>
                                </div>
                                <div class="tabsItem" data-tabs="#mep_ticket_times_thu">
									<?php $this->time_line( $post_id, 'mep_ticket_times_thu' ); ?>
                                </div>
                                <div class="tabsItem" data-tabs="#mep_ticket_times_fri">
									<?php $this->time_line( $post_id, 'mep_ticket_times_fri' ); ?>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
				<?php
			}
			public function time_line( $post_id, $key ) {
				$time_infos = MPWEM_Global_Function::get_post_info( $post_id, $key, [] );
				?>
                <div class="mpwem_settings_area">
                    <table class="mpwem_time_setting_table">
                        <tbody class="mpwem_sortable_area mpwem_item_insert">
						<?php if ( is_array( $time_infos ) && sizeof( $time_infos ) > 0 ) {
							foreach ( $time_infos as $time_info ) {
								$this->time_line_item( $key, $time_info );
							}
						} ?>
                        </tbody>
                    </table>
					<?php MPWEM_Custom_Layout::add_new_button( esc_html__( 'Add new Time Slot', 'mage-eventpress' ) ); ?>
                    <div class="mpwem_hidden_content">
                        <table>
                            <tbody class="mpwem_hidden_item">
							<?php $this->time_line_item( $key ); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
				<?php
			}
			public function time_line_item( $key, $time_info = [] ) {
				$label = array_key_exists( 'mep_ticket_time_name', $time_info ) ? $time_info['mep_ticket_time_name'] : '';
				$time  = array_key_exists( 'mep_ticket_time', $time_info ) ? $time_info['mep_ticket_time'] : '';
				?>
                <tr class="mpwem_remove_area">
                    <td>
                        <label><input type="text" class="formControl" value="<?php echo esc_attr( $label ); ?>" name="<?php echo esc_attr( $key . '_label[]' ); ?>" placeholder="<?php esc_attr_e( 'Time Slot Label', 'mage-eventpress' ); ?>"/></label>
                    </td>
                    <td><?php self::time_item( $key . '_time[]', $time ); ?></td>
                    <td class="_w_150"><?php MPWEM_Custom_Layout::move_remove_button(); ?></td>
                </tr>
				<?php
			}
			public static function date_item( $name, $date = '' ): void {
				$date_format  = MPWEM_Global_Function::date_picker_format();
				$now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
				$hidden_date  = $date ? date( 'Y-m-d', strtotime( $date ) ) : '';
				$visible_date = $date ? date_i18n( $date_format, strtotime( $date ) ) : '';
				?>
                <label>
                    <input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $hidden_date ); ?>"/>
                    <input type="text" value="<?php echo esc_attr( $visible_date ); ?>" class="formControl date_type" placeholder="<?php echo esc_attr( $now ); ?>"/>
                    <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
                </label>
				<?php
			}
			public static function time_item( $name, $time ): void {
				?>
                <label>
                    <input type="time" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $time ); ?>" class="formControl" placeholder="<?php echo esc_attr( $time ); ?>"/>
                </label>
				<?php
			}
			/*************************************/
			public function special_on_dates_setting( $post_id ) {
				$special_dates       = MPWEM_Global_Function::get_post_info( $post_id, 'mep_special_date_info', array() );
				$display_ticket_time = MPWEM_Global_Function::get_post_info( $post_id, 'mep_disable_ticket_time', 'off' );
				?>
                <div class="mpwem_style mep-special-datetime" style="display:<?php echo esc_attr( $display_ticket_time == 'off' ? 'none' : 'block' ); ?>">
                    <section class="bg-light" style="margin-top: 20px;">
                        <div>
                            <h2><?php esc_html_e( 'Special  Dates Time Settings', 'mage-eventpress' ); ?></h2>
                            <span class="text"><?php esc_html_e( 'Here you can set special date and time for event.', 'mage-eventpress' ); ?></span>
                        </div>
                    </section>
                    <section>
                        <div class="mpwem_settings_area">
                            <table class="mep_special_on_dates_table">
                                <thead>
                                <tr>
                                    <th class="w-20"><?php esc_html_e( 'Label', 'mage-eventpress' ); ?></th>
                                    <th class="w-20"><?php esc_html_e( 'Start Date', 'mage-eventpress' ); ?><span class="textRequired">&nbsp;*</span></th>
                                    <th class="w-20"><?php esc_html_e( 'End Date', 'mage-eventpress' ); ?><span class="textRequired">&nbsp;*</span></th>
                                    <th class="w-30"><?php esc_html_e( 'Times', 'mage-eventpress' ); ?><span class="textRequired">&nbsp;*</span></th>
                                    <th class="w-10"><?php esc_html_e( 'Action', 'mage-eventpress' ); ?></th>
                                </tr>
                                </thead>
                                <tbody class="mpwem_sortable_area mpwem_item_insert">
								<?php
									if ( is_array( $special_dates ) && sizeof( $special_dates ) > 0 ) {
										foreach ( $special_dates as $special_date ) {
											$this->special_on_day_item( $special_date );
										}
									}
								?>
                                </tbody>
                            </table>
                            <div class="mt-2"></div>
							<?php MPWEM_Custom_Layout::add_new_button( esc_html__( 'Add New Special Date', 'mage-eventpress' ), 'ttbm_add_new_special_date' ); ?>
							<?php $this->hidden_special_on_day_item(); ?>
                        </div>
                    </section>
                </div>
				<?php
			}
			public function special_on_day_item( $special_date = array() ) {
				$date_format        = MPWEM_Global_Function::date_picker_format();
				$now                = date_i18n( $date_format, time() );
				$special_date       = $special_date && is_array( $special_date ) ? $special_date : array();
				$date_name          = array_key_exists( 'date_label', $special_date ) ? $special_date['date_label'] : '';
				$start_date         = array_key_exists( 'start_date', $special_date ) ? $special_date['start_date'] : '';
				$hidden_start_date  = $start_date ? date( 'Y-m-d', strtotime( $start_date ) ) : '';
				$visible_start_date = $start_date ? date_i18n( $date_format, strtotime( $start_date ) ) : '';
				$end_date           = array_key_exists( 'end_date', $special_date ) ? $special_date['end_date'] : '';
				$hidden_end_date    = $end_date ? date( 'Y-m-d', strtotime( $end_date ) ) : '';
				$visible_end_date   = $end_date ? date_i18n( $date_format, strtotime( $end_date ) ) : '';
				$time               = array_key_exists( 'time', $special_date ) ? maybe_unserialize( $special_date['time'] ) : array();
				$unique_name        = uniqid();
				$slot_name          = 'mep_special_time_label_' . $unique_name . '[]';
				$time_name          = 'mep_special_time_value_' . $unique_name . '[]';
				?>
                <tr class="mpwem_remove_area">
                    <td>
                        <label>
                            <input type="hidden" name="mep_special_date_hidden_name[]" value="<?php echo esc_attr( $unique_name ); ?>"/>
                            <input type="text" name="mep_special_date_name[]" class="name_validation" value="<?php echo esc_attr( $date_name ); ?>" placeholder="<?php esc_attr_e( 'Date Label ', 'mage-eventpress' ); ?>" style="width:180px"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="hidden" name="mep_special_start_date[]" value="<?php echo esc_attr( $hidden_start_date ); ?>"/>
                            <input name="" class="formControl date_type" value="<?php echo esc_attr( $visible_start_date ); ?>" placeholder="<?php echo esc_attr( $now ); ?>"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="hidden" name="mep_special_end_date[]" value="<?php echo esc_attr( $hidden_end_date ); ?>"/>
                            <input name="" class="formControl date_type" value="<?php echo esc_attr( $visible_end_date ); ?>" placeholder="<?php echo esc_attr( $now ); ?>"/>
                        </label>
                    </td>
                    <td><?php $this->time_slot_setting( '', '', $slot_name, $time_name, $time ); ?></td>
                    <td>
						<?php MPWEM_Custom_Layout::move_remove_button(); ?>
                    </td>
                </tr>
				<?php
			}
			public function hidden_special_on_day_item() {
				?>
                <div class="mpwem_hidden_content">
                    <table>
                        <tbody class="mpwem_hidden_item">
						<?php $this->special_on_day_item(); ?>
                        </tbody>
                    </table>
                </div>
				<?php
			}
			public function time_slot_setting( $tour_id, $key, $slot_name, $time_name, $time_slots = array() ) {
				?>
                <div class="mpwem_settings_area">
                    <table>
                        <thead>
                        <tr>
                            <th style="width:30%"><?php esc_html_e( 'Label', 'mage-eventpress' ); ?><span class="textRequired">&nbsp;*</span></th>
                            <th style="width:40%"><?php esc_html_e( 'Time', 'mage-eventpress' ); ?><span class="textRequired">&nbsp;*</span></th>
                            <th style="width:30%"><?php esc_html_e( 'Action', 'mage-eventpress' ); ?></th>
                        </tr>
                        </thead>
                        <tbody class="mpwem_sortable_area mpwem_item_insert">
						<?php
							$time_slots = is_array($time_slots) && sizeof( $time_slots ) > 0 ? $time_slots : maybe_unserialize( MPWEM_Global_Function::get_post_info( $tour_id, $key, array() ) );
							if ( is_array($time_slots) && sizeof( $time_slots ) > 0 ) {
								foreach ( $time_slots as $time_slot ) {
									$this->time_slot_item( $slot_name, $time_name, $time_slot );
								}
							}
						?>
                        </tbody>
                    </table>
					<?php MPWEM_Custom_Layout::add_new_button( esc_html__( 'Add New Time', 'mage-eventpress' ), 'mpwem_add_item', '_button_default_xs_mt_xs' ); ?>
					<?php $this->hidden_time_slot_item( $slot_name, $time_name ); ?>
                </div>
				<?php
			}
			public function time_slot_item( $slot_name, $time_name, $time_slots = array() ) {
				$slot_label = array_key_exists( 'mep_ticket_time_name', $time_slots ) ? $time_slots['mep_ticket_time_name'] : '';
				$slot_time  = array_key_exists( 'mep_ticket_time', $time_slots ) ? $time_slots['mep_ticket_time'] : '';
				?>
                <tr class="mpwem_remove_area">
                    <td>
                        <label>
                            <input type="text" name="<?php echo esc_attr( $slot_name ); ?>" class="formControl name_validation" value="<?php echo esc_attr( $slot_label ); ?>" placeholder="<?php esc_attr_e( 'Time Label', 'mage-eventpress' ); ?>" style="width:70px;"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="time" name="<?php echo esc_attr( $time_name ); ?>" class="formControl" value="<?php echo esc_attr( $slot_time ); ?>" style="width:100px;"/>
                        </label>
                    </td>
                    <td>
						<?php MPWEM_Custom_Layout::move_remove_button(); ?>
                    </td>
                </tr>
				<?php
			}
			public function hidden_time_slot_item( $slot_name, $time_name ) {
				?>
                <div class="mpwem_hidden_content">
                    <table>
                        <tbody class="mpwem_hidden_item">
						<?php $this->time_slot_item( $slot_name, $time_name ); ?>
                        </tbody>
                    </table>
                </div>
				<?php
			}
			/*************************************/
			public function event_date_format( $event_id, $event_infos ) {
				$time_zone_display   = array_key_exists( 'mep_time_zone_display', $event_infos ) ? $event_infos['mep_time_zone_display'] : '';
				$display             = array_key_exists( 'mep_enable_custom_dt_format', $event_infos ) ? $event_infos['mep_enable_custom_dt_format'] : 'off';
				$checked             = $display == 'off' ? '' : 'checked';
				$active              = $display == 'off' ? '' : 'mActive';
				$date_formats        = array_key_exists( 'mep_event_date_format', $event_infos ) ? $event_infos['mep_event_date_format'] : '';
				$custom_date_formats = array_key_exists( 'mep_event_custom_date_format', $event_infos ) ? $event_infos['mep_event_custom_date_format'] : '';
				$date_format_lists   = MPWEM_Global_Function::date_format_list();
				$time_formats        = array_key_exists( 'mep_event_time_format', $event_infos ) ? $event_infos['mep_event_time_format'] : '';
				$custom_time_formats = array_key_exists( 'mep_custom_event_time_format', $event_infos ) ? $event_infos['mep_custom_event_time_format'] : '';
				$time_format_lists   = MPWEM_Global_Function::time_format_list();
				?>
                <div class="_layout_default mpwem_date_format_settings">
                    <div class="_bg_light_padding">
                        <div class="_justify_between_align_center_wrap ">
                            <h4><?php esc_html_e( 'Date Time format Settings', 'mage-eventpress' ); ?></h4>
							<?php MPWEM_Custom_Layout::switch_button( 'mep_enable_custom_dt_format', $checked ); ?>
                        </div>
                        <span class="label-text"><?php esc_html_e( 'You can change the date and time format by going to the settings', 'mage-eventpress' ); ?></span>
                    </div>
                    <div class="_padding_bt <?php echo esc_attr( $active ); ?>" data-collapse="#mep_enable_custom_dt_format">
                        <div class="">
                            <label class="_justify_between_align_center_wrap ">
                                <span class="_mr"><?php esc_html_e( 'Date Format', 'mage-eventpress' ); ?></span>
                                <select class="formControl" name="mep_event_date_format" data-collapse-target required>
                                    <option disabled selected><?php esc_html_e( 'Please select ...', 'mage-eventpress' ); ?></option>
									<?php foreach ( $date_format_lists as $key => $date_format_list ) { ?>
                                        <option value="<?php echo esc_attr( $key ); ?>" data-option-target="#selected_date_format" <?php echo esc_attr( $date_formats == $key ? 'selected' : '' ); ?>><?php echo esc_html( $date_format_list ); ?></option>
									<?php } ?>
                                    <option value="custom" data-option-target="#custom_date_format" <?php echo esc_attr( $date_formats == 'custom' ? 'selected' : '' ); ?>><?php esc_html_e( 'Custom Date Formats', 'mage-eventpress' ); ?></option>
                                </select>
                            </label>
                            <span class="label-text"><?php esc_html_e( 'Please select your preferred date format from the options below. If you wish to use a custom date format, select the Custom option and enter your desired date format. Please note that this date format will only apply to events.', 'mage-eventpress' ); ?></span>
                        </div>
                        <div class="_pt_mt_bt <?php echo esc_attr( $date_formats == 'custom' ? 'mActive' : '' ); ?>" data-collapse="#custom_date_format">
                            <label class="_justify_between_align_center_wrap ">
                                <span class="_mr"><?php esc_html_e( 'Custom Date Format', 'mage-eventpress' ); ?></span>
                                <input type="text" class="formControl" name='mep_event_custom_date_format' value='<?php echo esc_attr( $custom_date_formats ); ?>'/>
                            </label>
                            <span class="label-text"><a href="https://wordpress.org/support/article/formatting-date-and-time/"><?php esc_html_e( 'Documentation on date and time formatting.', 'mage-eventpress' ); ?></a></span>
                        </div>
                        <div class="_pt_mt_bt">
                            <label class="_justify_between_align_center_wrap ">
                                <span class="_mr"><?php esc_html_e( 'Time Format', 'mage-eventpress' ); ?></span>
                                <select class="formControl" name="mep_event_time_format" data-collapse-target required>
                                    <option disabled selected><?php esc_html_e( 'Please select ...', 'mage-eventpress' ); ?></option>
									<?php foreach ( $time_format_lists as $key => $time_format_list ) { ?>
                                        <option value="<?php echo esc_attr( $key ); ?>" data-option-target="#selected_time_format" <?php echo esc_attr( $time_formats == $key ? 'selected' : '' ); ?>><?php echo esc_html( $time_format_list ); ?></option>
									<?php } ?>
                                    <option value="custom" data-option-target="#custom_time_format" <?php echo esc_attr( $time_formats == 'custom' ? 'selected' : '' ); ?>><?php esc_html_e( 'Custom Time Formats', 'mage-eventpress' ); ?></option>
                                </select>
                            </label>
                            <span class="label-text"><?php esc_html_e( 'Please select the time format from the list. If you want to use a custom time format, select Custom and write your desired time format. This time format will only apply to events. ', 'mage-eventpress' ); ?></span>
                        </div>
                        <div class="_pt_mt_bt <?php echo esc_attr( $time_formats == 'custom' ? 'mActive' : '' ); ?>" data-collapse="#custom_time_format">
                            <label class="_justify_between_align_center_wrap ">
                                <span class="_mr"><?php esc_html_e( 'Custom Time Format', 'mage-eventpress' ); ?></span>
                                <input type="text" class="formControl" name='mep_custom_event_time_format' value='<?php echo esc_attr( $custom_time_formats ); ?>'/>
                            </label>
                            <span class="label-text"><a href="https://wordpress.org/support/article/formatting-date-and-time/"><?php esc_html_e( 'Documentation on date and time formatting.', 'mage-eventpress' ); ?></a></span>
                        </div>
                        <div class="_pt_mt_bt">
                            <label class="_justify_between_align_center_wrap ">
                                <span class="_mr"><?php esc_html_e( 'Show Timezone', 'mage-eventpress' ); ?></span>
                                <select class="formControl" name="mep_time_zone_display">
                                    <option selected><?php esc_html_e( 'Please select ...', 'mage-eventpress' ); ?></option>
                                    <option value="yes" <?php echo esc_attr( $time_zone_display == 'yes' ? 'selected' : '' ); ?>><?php esc_html_e( 'Yes', 'mage-eventpress' ); ?></option>
                                    <option value="no" <?php echo esc_attr( $time_zone_display == 'no' ? 'selected' : '' ); ?>><?php esc_html_e( 'No', 'mage-eventpress' ); ?></option>
                                </select>
                            </label>
                            <span class="label-text"><?php esc_html_e( 'If you want to show the date and time in your local timezone, please select Yes.', 'mage-eventpress' ); ?></span>
                        </div>
                    </div>
                </div>
				<?php
			}
		}
		new MPWEM_Date_Settings();
	}
