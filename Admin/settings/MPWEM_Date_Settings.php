<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('MPWEM_Date_Settings')) {
		class MPWEM_Date_Settings {
			public function __construct() {
				add_action('add_mep_date_time_tab', [$this, 'date_time_tab']);
				add_action('mpwem_settings_save', [$this, 'settings_save']);
			}
			public function event_type_section($post_id) {
				$event_type = MP_Global_Function::get_post_info($post_id, 'mep_enable_recurring', 'no');
				?>
                <section class="mpStyle">
                    <label class="mpev-label">
                        <div>
                            <h2><?php esc_html_e('Event Type', 'mage-eventpress'); ?></h2>
                            <span><?php _e('Select your event type', 'mage-eventpress'); ?></span>
                        </div>
                        <select class="formControl" name="mep_enable_recurring" data-collapse-target required>
                            <option disabled selected><?php esc_html_e('Please select ...', 'mage-eventpress'); ?></option>
                            <option value="no" data-option-target="#mep_normal_event" <?php echo esc_attr($event_type == 'no' ? 'selected' : ''); ?>><?php esc_html_e('Normal Event', 'mage-eventpress'); ?></option>
                            <option value="yes" data-option-target="#mep_normal_event" <?php echo esc_attr($event_type == 'yes' ? 'selected' : ''); ?>><?php esc_html_e('Particular Event', 'mage-eventpress'); ?></option>
                            <option value="everyday" data-option-target="#mep_everyday_event" <?php echo esc_attr($event_type == 'everyday' ? 'selected' : ''); ?>><?php esc_html_e('Repeated Event', 'mage-eventpress'); ?></option>
                        </select>
                    </label>
                </section>
				<?php
			}
			public function normal_particular_section($post_id) {
				$event_type = MP_Global_Function::get_post_info($post_id, 'mep_enable_recurring', 'no');
				$start_date = MP_Global_Function::get_post_info($post_id, 'event_start_date');
				$start_time = MP_Global_Function::get_post_info($post_id, 'event_start_time');
				$end_date 	= MP_Global_Function::get_post_info($post_id, 'event_end_date');
				$end_time 	= MP_Global_Function::get_post_info($post_id, 'event_end_time');
				$more_dates = MP_Global_Function::get_post_info($post_id, 'mep_event_more_date', []);
				?>
                <div class="mpStyle">
                    <section class="mp_settings_area <?php echo esc_attr($event_type == 'no' || $event_type == 'yes' ? 'mActive' : ''); ?>" data-collapse="#mep_normal_event">
                        <table>
                            <thead>
                            <tr>
                                <th><?php esc_html_e('Start Date', 'mage-eventpress'); ?></th>
                                <th><?php esc_html_e('Start Time', 'mage-eventpress'); ?></th>
                                <th><?php esc_html_e('End Date', 'mage-eventpress'); ?></th>
                                <th><?php esc_html_e('End Time', 'mage-eventpress'); ?></th>
								<?php do_action('mep_date_table_head', $post_id); ?>
                                <th><?php esc_html_e('Action', 'mage-eventpress'); ?></th>
                            </tr>
                            </thead>
                            <tbody class="mp_sortable_area mp_item_insert">
                            <tr>
                                <td><?php self::date_item('event_start_date', $start_date); ?></td>
                                <td><?php self::time_item('event_start_time', $start_time); ?></td>
                                <td><?php self::date_item('event_end_date', $end_date); ?></td>
                                <td><?php self::time_item('event_end_time', $end_time); ?></td>
								<?php do_action('mep_date_table_body_default_date', $post_id); ?>
                                <td></td>
                            </tr>
							<?php if (sizeof($more_dates) > 0) { ?>
								<?php foreach ($more_dates as $more_date) { ?>
									<?php $more_start_date = array_key_exists('event_more_start_date', $more_date) ? $more_date['event_more_start_date'] : ''; ?>
									<?php $more_start_time = array_key_exists('event_more_start_time', $more_date) ? $more_date['event_more_start_time'] : ''; ?>
									<?php $more_end_date = array_key_exists('event_more_end_date', $more_date) ? $more_date['event_more_end_date'] : ''; ?>
									<?php $more_end_time = array_key_exists('event_more_end_time', $more_date) ? $more_date['event_more_end_time'] : ''; ?>
                                    <tr class="mp_remove_area">
                                        <td><?php self::date_item('event_more_start_date[]', $more_start_date); ?></td>
                                        <td><?php self::time_item('event_more_start_time[]', $more_start_time); ?></td>
                                        <td><?php self::date_item('event_more_end_date[]', $more_end_date); ?></td>
                                        <td><?php self::time_item('event_more_end_time[]', $more_end_time); ?></td>
										<?php do_action('mep_date_table_body_more_date', $post_id, $more_date); ?>
                                        <td><?php MP_Custom_Layout::move_remove_button(); ?></td>
                                    </tr>
								<?php } ?>
							<?php } ?>
                            </tbody>
                        </table>
						<?php MP_Custom_Layout::add_new_button(esc_html__('Add More Dates', 'mage-eventpress')); ?>
                        <div class="mp_hidden_content">
                            <table>
                                <tbody class="mp_hidden_item">
                                <tr class="mp_remove_area">
                                    <td><?php self::date_item('event_more_start_date[]', ''); ?></td>
                                    <td><?php self::time_item('event_more_start_time[]', ''); ?></td>
                                    <td><?php self::date_item('event_more_end_date[]', ''); ?></td>
                                    <td><?php self::time_item('event_more_end_time[]', ''); ?></td>
									<?php do_action('mep_date_table_empty', $post_id); ?>
                                    <td><?php MP_Custom_Layout::move_remove_button(); ?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
				<?php
			}
			public function date_time_section($post_id) {
				$start_date = MP_Global_Function::get_post_info($post_id, 'event_start_date');
				$start_time = MP_Global_Function::get_post_info($post_id, 'event_start_time');
				$end_date 	= MP_Global_Function::get_post_info($post_id, 'event_end_date');
				$end_time 	= MP_Global_Function::get_post_info($post_id, 'event_end_time');
				$periods 	= MP_Global_Function::get_post_info($post_id, 'mep_repeated_periods', 1);
				?>
                <div class="mpStyle">
                    <section>
                        <label class="mpev-label">
                            <div>
                                <h2><?php esc_html_e('Start Date & Time', 'mage-eventpress'); ?></h2>
                                <span><?php _e('Select Start Date & Time', 'mage-eventpress'); ?></span>
                            </div>
                            <div class="dFlex">
								<?php self::date_item('event_start_date_everyday', $start_date); ?>
								<?php self::time_item('event_start_time_everyday', $start_time); ?>
                            </div>
                        </label>
                    </section>
                    <section>
                        <label class="mpev-label">
                            <div>
                                <h2><span><?php esc_html_e('End Date & Time', 'mage-eventpress'); ?></span></h2>
                                <span><?php _e('Select Start Date & Time', 'mage-eventpress'); ?></span>
                            </div>
                            <div class="dFlex">
								<?php self::date_item('event_end_date_everyday', $end_date); ?>
								<?php self::time_item('event_end_time_everyday', $end_time); ?>
                            </div>
                        </label>
                    </section>
                    <section>
                        <label class="mpev-label">
                            <div>
                                <h2><span><?php esc_html_e('After Repeated Days', 'mage-eventpress'); ?></span></h2>
                                <span><?php _e('Select Start Date & Time', 'mage-eventpress'); ?></span>
                            </div>
                            <input type="number" class="formControl max_100 mp_number_validation" name='mep_repeated_periods' value='<?php echo $periods; ?>'/>
                        </label>
                    </section>
                </div>
				<?php
			}
			public function off_days_section($post_id) {
				?>
                <section class="bg-light" style="margin-top: 20px;">
                    <h2><?php esc_html_e('Off Days Setting', 'mage-eventpress') ?></h2>
                    <span><?php esc_html_e('Configure Event Locations and Virtual Venues', 'mage-eventpress') ?></span>
                </section>
                <div class="mpStyle">
                    <section>
                        <label class="mpev-label">
                            <div>
                                <h2><span><?php esc_html_e('Ticket Offdays', 'mage-eventpress'); ?></span></h2>
                                <span><?php _e('Select Offdays', 'mage-eventpress'); ?></span>
                            </div>
							<?php
                                $off_day_array = MP_Global_Function::get_post_info($post_id, 'mep_ticket_offdays', []);
                                if (!is_array($off_day_array)) {
                                    // Try to unserialize if it's serialized
                                    $maybe_unserialized = @unserialize($off_day_array);
                                    if (is_array($maybe_unserialized)) {
                                        $off_day_array = $maybe_unserialized;
                                    } else {
                                        $off_day_array = explode(',', (string)$off_day_array);
                                    }
                                }
                                $off_days = $off_day_array ? implode(',', $off_day_array) : '';
							?>
                            <div class="dFlex groupCheckBox">
                                <input type="hidden" name="mep_ticket_offdays" value="<?php echo esc_attr($off_days); ?>"/>
								<?php foreach ($days as $key => $day) { ?>
                                    <label class="customCheckboxLabel ">
                                        <input type="checkbox" <?php echo esc_attr(in_array($key, $off_day_array) ? 'checked' : ''); ?> data-checked="<?php echo esc_attr($key); ?>"/>
                                        <span class="customCheckbox"><?php echo esc_html($day); ?></span>
                                    </label>
								<?php } ?>
                            </div>
                        </label>
                    </section>
                    <section>
                        <label class="mpev-label">
                            <div>
                                <h2><span><?php esc_html_e('Ticket Off Dates List', 'mage-eventpress'); ?></span></h2>
                                <span><?php _e('Ticket Off Dates List', 'mage-eventpress'); ?></span>
                            </div>
                            <div class="mp_settings_area ">
                                <table>
                                    <tbody class="mp_item_insert mp_sortable_area">
                                        <?php
                                            $off_day_lists = MP_Global_Function::get_post_info($post_id, 'mep_ticket_off_dates', array());
                                            // If it's not an array, try unserializing or fallback
                                            if (!is_array($off_day_lists)) {
                                                $maybe_unserialized = @unserialize($off_day_lists);
                                                if (is_array($maybe_unserialized)) {
                                                    $off_day_lists = $maybe_unserialized;
                                                } else {
                                                    $off_day_lists = []; // fallback to empty array
                                                }
                                            }
                                            if (!empty($off_day_lists)) {
                                                foreach ($off_day_lists as $off_day) {
                                                    if (is_array($off_day) && !empty($off_day['mep_ticket_off_date'])) {
                                                        ?>
                                                        <tr class="mp_remove_area">
                                                            <td><?php $this->date_item('mep_ticket_off_dates[]', $off_day['mep_ticket_off_date']); ?></td>
                                                            <td><?php MP_Custom_Layout::move_remove_button(); ?></td>
                                                        </tr>
                                                        <?php
                                                    }
                                                }
                                            }
                                        ?>
                                    </tbody>
                                </table>
                                <?php MP_Custom_Layout::add_new_button(esc_html__('Add New Off date', 'mage-eventpress')); ?>
                                <div class="mp_hidden_content">
                                    <table>
                                        <tbody class="mp_hidden_item">
                                            <tr class="mp_remove_area">
                                                <td><?php $this->date_item('mep_ticket_off_dates[]'); ?></td>
                                                <td><?php MP_Custom_Layout::move_remove_button(); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </label>
                    </section>
                </div>
				<?php
			}
			public function time_settings_section($post_id) {
				$display_time = MP_Global_Function::get_post_info($post_id, 'mep_disable_ticket_time', 'no');
				?>
                
                    <section class="bg-light" style="margin-top: 20px;">
                        <h2><?php esc_html_e('Time Settings', 'mage-eventpress') ?></h2>
                        <span><?php esc_html_e('Configure Event Locations and Virtual Venues', 'mage-eventpress') ?></span>
                    </section>
                    <section>
                        <div class="mpev-label">
                            <div>
                                <h2><span><?php esc_html_e('Display Time?', 'mage-eventpress'); ?> </span></h2>
                                <span><?php _e('You can change the date and time format by going to the settings', 'mage-eventpress'); ?></span>
                            </div>
							<label class="mpev-switch">
								<input type="checkbox" name="mep_disable_ticket_time" value="<?php echo esc_attr( $display_time ); ?>" <?php echo esc_attr( ( $display_time == 'yes' ) ? 'checked' : '' ); ?> data-collapse-target="#mep_disable_ticket_time" data-toggle-values="yes,no">
								<span class="mpev-slider"></span>
							</label>
						</div>
                    </section>
				<div class="mpStyle">
                    <section style="display:<?php echo esc_attr($display_time == 'yes' ? 'block' : 'none'); ?>" id="mep_disable_ticket_time">
                        <div class="mpTabs topTabs tabBorder">
                            <ul class="tabLists">
                                <li data-tabs-target="#mep_ticket_times_global">
									<?php esc_html_e('Default', 'mage-eventpress'); ?>
                                </li>
                                <li data-tabs-target="#mep_ticket_times_sat">
									<?php esc_html_e('Saturday', 'mage-eventpress'); ?>
                                </li>
                                <li data-tabs-target="#mep_ticket_times_sun">
									<?php esc_html_e('Sunday', 'mage-eventpress'); ?>
                                </li>
                                <li data-tabs-target="#mep_ticket_times_mon">
									<?php esc_html_e('Monday', 'mage-eventpress'); ?>
                                </li>
                                <li data-tabs-target="#mep_ticket_times_tue">
									<?php esc_html_e('Tuesday', 'mage-eventpress'); ?>
                                </li>
                                <li data-tabs-target="#mep_ticket_times_wed">
									<?php esc_html_e('Wednesday', 'mage-eventpress'); ?>
                                </li>
                                <li data-tabs-target="#mep_ticket_times_thu">
									<?php esc_html_e('Thursday', 'mage-eventpress'); ?>
                                </li>
                                <li data-tabs-target="#mep_ticket_times_fri">
									<?php esc_html_e('Friday', 'mage-eventpress'); ?>
                                </li>
                            </ul>
                            <div class="tabsContent">
                                <div class="tabsItem" data-tabs="#mep_ticket_times_global">
									<?php $this->time_line($post_id, 'mep_ticket_times_global'); ?>
                                </div>
                                <div class="tabsItem" data-tabs="#mep_ticket_times_sat">
									<?php $this->time_line($post_id, 'mep_ticket_times_sat'); ?>
                                </div>
                                <div class="tabsItem" data-tabs="#mep_ticket_times_sun">
									<?php $this->time_line($post_id, 'mep_ticket_times_sun'); ?>
                                </div>
                                <div class="tabsItem" data-tabs="#mep_ticket_times_mon">
									<?php $this->time_line($post_id, 'mep_ticket_times_mon'); ?>
                                </div>
                                <div class="tabsItem" data-tabs="#mep_ticket_times_tue">
									<?php $this->time_line($post_id, 'mep_ticket_times_tue'); ?>
                                </div>
                                <div class="tabsItem" data-tabs="#mep_ticket_times_wed">
									<?php $this->time_line($post_id, 'mep_ticket_times_wed'); ?>
                                </div>
                                <div class="tabsItem" data-tabs="#mep_ticket_times_thu">
									<?php $this->time_line($post_id, 'mep_ticket_times_thu'); ?>
                                </div>
                                <div class="tabsItem" data-tabs="#mep_ticket_times_fri">
									<?php $this->time_line($post_id, 'mep_ticket_times_fri'); ?>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
				<?php
			}
			public function date_time_tab($post_id) {
				$event_type = MP_Global_Function::get_post_info($post_id, 'mep_enable_recurring', 'no');
				?>
                <div class="mp_tab_item" data-tab-item="#mp_event_time">
                    <h3><?php esc_html_e('Date & Time', 'mage-eventpress') ?></h3>
                    <p><?php esc_html_e('Configure Your Date and Time Settings Here', 'mage-eventpress') ?></p>
                    <section class="bg-light">
                        <h2><?php esc_html_e('General Settings', 'mage-eventpress') ?></h2>
                        <span><?php esc_html_e('Configure Event Locations and Virtual Venues', 'mage-eventpress') ?></span>
                    </section>
					<?php $this->event_type_section($post_id); ?>
					<?php $this->normal_particular_section($post_id); ?>
                    <div style="display:<?php echo esc_attr($event_type == 'everyday' ? 'block' : 'none'); ?>" data-collapse="#mep_everyday_event">
						<?php $this->date_time_section($post_id); ?>
						<?php $this->off_days_section($post_id); ?>
						<?php $this->time_settings_section($post_id); ?>
						<?php $this->special_on_dates_setting($post_id); ?>
                    </div>
					<?php do_action('mp_event_recurring_every_day_setting', $post_id); ?>
                </div>
				<?php
				//echo '<pre>';print_r($meta_values);echo'</pre>';
			}
			public function time_line($post_id, $key) {
				$time_infos = MP_Global_Function::get_post_info($post_id, $key, []);
				?>
                <div class="mp_settings_area">
                    <table class="_layoutFixed mpwem_time_setting_table">
                        <tbody class="mp_sortable_area mp_item_insert">
						<?php if (is_array($time_infos) && sizeof($time_infos) > 0) {
							foreach ($time_infos as $time_info) {
								$this->time_line_item($key, $time_info);
							}
						} ?>
                        </tbody>
                    </table>
					<?php MP_Custom_Layout::add_new_button(esc_html__('Add new Time Slot', 'mage-eventpress')); ?>
                    <div class="mp_hidden_content">
                        <table>
                            <tbody class="mp_hidden_item">
							<?php $this->time_line_item($key); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
				<?php
			}
			public function time_line_item($key, $time_info = []) {
				$label = array_key_exists('mep_ticket_time_name', $time_info) ? $time_info['mep_ticket_time_name'] : '';
				$time = array_key_exists('mep_ticket_time', $time_info) ? $time_info['mep_ticket_time'] : '';
				?>
                <tr class="mp_remove_area">
                    <td>
                        <label><input type="text" class="formControl" value="<?php echo esc_attr($label); ?>" name="<?php echo esc_attr($key . '_label[]'); ?>" placeholder="<?php esc_attr_e('Time Slot Label', 'mage-eventpress'); ?>"/></label>
                    </td>
                    <td><?php self::time_item($key . '_time[]', $time); ?></td>
                    <td class="_w_150"><?php MP_Custom_Layout::move_remove_button(); ?></td>
                </tr>
				<?php
			}
			public static function date_item($name, $date = ''): void {
				$date_format = MP_Global_Function::date_picker_format();
				$now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
				$hidden_date = $date ? date('Y-m-d', strtotime($date)) : '';
				$visible_date = $date ? date_i18n($date_format, strtotime($date)) : '';
				?>
                <label>
                    <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($hidden_date); ?>"/>
                    <input type="text" value="<?php echo esc_attr($visible_date); ?>" class="formControl date_type" placeholder="<?php echo esc_attr($now); ?>"/>
                </label>
				<?php
			}
			public static function time_item($name, $time): void {
				?>
                <label>
                    <input type="time" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($time); ?>" class="formControl" placeholder="<?php echo esc_attr($time); ?>"/>
                </label>
				<?php
			}
			/*************************************/
			public function special_on_dates_setting($post_id) {
				$special_dates = MP_Global_Function::get_post_info($post_id, 'mep_special_date_info', array());
				$display_ticket_time = MP_Global_Function::get_post_info($post_id, 'mep_disable_ticket_time', 'off');
				?>
                <div class="mpStyle mep-special-datetime" style="display:<?php echo esc_attr($display_ticket_time=='off'?'none':'block'); ?>">
                    <section class="bg-light" style="margin-top: 20px;">
                        <div>
                            <h2><?php _e('Special  Dates Time Settings', 'mage-eventpress'); ?></h2>
                            <span class="text"><?php _e('Here you can set special date and time for event.', 'mage-eventpress'); ?></span>
                        </div>
                    </section>
                    <section>
                        <div class="mp_settings_area">
                            <table class="mep_special_on_dates_table">
                                <thead>
                                <tr>
                                    <th class="w-20"><?php _e('Label', 'mage-eventpress'); ?></th>
                                    <th class="w-20"><?php _e('Start Date', 'mage-eventpress'); ?><span class="textRequired">&nbsp;*</span></th>
                                    <th class="w-20"><?php _e('End Date', 'mage-eventpress'); ?><span class="textRequired">&nbsp;*</span></th>
                                    <th class="w-30"><?php _e('Times', 'mage-eventpress'); ?><span class="textRequired">&nbsp;*</span></th>
                                    <th class="w-10"><?php _e('Action', 'mage-eventpress'); ?></th>
                                </tr>
                                </thead>
                                <tbody class="mp_sortable_area mp_item_insert">
								<?php
									if (sizeof($special_dates) > 0) {
										foreach ($special_dates as $special_date) {
											$this->special_on_day_item($special_date);
										}
									}
								?>
                                </tbody>
                            </table>
                            <div class="mt-2"></div>
							<?php MP_Custom_Layout::add_new_button(esc_html__('Add New Special Date', 'mage-eventpress'), 'ttbm_add_new_special_date'); ?>
							<?php $this->hidden_special_on_day_item(); ?>
                        </div>
                    </section>
                </div>
				<?php
			}
			public function special_on_day_item($special_date = array()) {
				$date_format 		= MP_Global_Function::date_picker_format();
				$now 				= date_i18n($date_format, time());
				$special_date 		= $special_date && is_array($special_date) ? $special_date : array();
				$date_name 			= array_key_exists('date_label', $special_date) ? $special_date['date_label'] : '';
				$start_date 		= array_key_exists('start_date', $special_date) ? $special_date['start_date'] : '';
				$hidden_start_date 	= $start_date ? date('Y-m-d', strtotime($start_date)) : '';
				$visible_start_date = $start_date ? date_i18n($date_format, strtotime($start_date)) : '';
				$end_date 			= array_key_exists('end_date', $special_date) ? $special_date['end_date'] : '';
				$hidden_end_date 	= $end_date ? date('Y-m-d', strtotime($end_date)) : '';
				$visible_end_date 	= $end_date ? date_i18n($date_format, strtotime($end_date)) : '';
				$time 				= array_key_exists('time', $special_date) ? maybe_unserialize($special_date['time']) : array();
				$unique_name 		= uniqid();
				$slot_name 			= 'mep_special_time_label_' . $unique_name . '[]';
				$time_name 			= 'mep_special_time_value_' . $unique_name . '[]';
				?>
                <tr class="mp_remove_area">
                    <td>
                        <label>
                            <input type="hidden" name="mep_special_date_hidden_name[]" value="<?php echo esc_attr($unique_name); ?>"/>
                            <input type="text" name="mep_special_date_name[]" class="mp_name_validation" value="<?php echo $date_name; ?>" placeholder="<?php esc_attr_e('Date Label ', 'mage-eventpress'); ?>" style="width:180px"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="hidden" name="mep_special_start_date[]" value="<?php echo esc_attr($hidden_start_date); ?>"/>
                            <input name="" class="formControl date_type" value="<?php echo esc_attr($visible_start_date); ?>" placeholder="<?php echo esc_attr($now); ?>"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="hidden" name="mep_special_end_date[]" value="<?php echo esc_attr($hidden_end_date); ?>"/>
                            <input name="" class="formControl date_type" value="<?php echo esc_attr($visible_end_date); ?>" placeholder="<?php echo esc_attr($now); ?>"/>
                        </label>
                    </td>
                    <td><?php $this->time_slot_setting('', '', $slot_name, $time_name, $time); ?></td>
                    <td>
						<?php MP_Custom_Layout::move_remove_button(); ?>
                    </td>
                </tr>
				<?php
			}
			public function hidden_special_on_day_item() {
				?>
                <div class="mp_hidden_content">
                    <table>
                        <tbody class="mp_hidden_item">
						<?php $this->special_on_day_item(); ?>
                        </tbody>
                    </table>
                </div>
				<?php
			}
			public function time_slot_setting($tour_id, $key, $slot_name, $time_name, $time_slots = array()) {
				?>
                <div class="mp_settings_area">
                    <table>
                        <thead>
                        <tr>
                            <th style="width:30%"><?php _e('Label', 'mage-eventpress'); ?><span class="textRequired">&nbsp;*</span></th>
                            <th style="width:40%"><?php _e('Time', 'mage-eventpress'); ?><span class="textRequired">&nbsp;*</span></th>
                            <th style="width:30%"><?php _e('Action', 'mage-eventpress'); ?></th>
                        </tr>
                        </thead>
                        <tbody class="mp_sortable_area mp_item_insert">
						<?php
							$time_slots = sizeof($time_slots) > 0 ? $time_slots : maybe_unserialize(MP_Global_Function::get_post_info($tour_id, $key, array()));
							if (sizeof($time_slots) > 0) {
								foreach ($time_slots as $time_slot) {
									$this->time_slot_item($slot_name, $time_name, $time_slot);
								}
							}
						?>
                        </tbody>
                    </table>
					<?php MP_Custom_Layout::add_new_button(esc_html__('Add New Time', 'mage-eventpress'), 'mp_add_item', '_dButton_xs_mt_xs'); ?>
					<?php $this->hidden_time_slot_item($slot_name, $time_name); ?>
                </div>
				<?php
			}
			public function time_slot_item($slot_name, $time_name, $time_slots = array()) {
				$slot_label = array_key_exists('mep_ticket_time_name', $time_slots) ? $time_slots['mep_ticket_time_name'] : '';
				$slot_time = array_key_exists('mep_ticket_time', $time_slots) ? $time_slots['mep_ticket_time'] : '';
				?>
                <tr class="mp_remove_area">
                    <td>
                        <label>
                            <input type="text" name="<?php echo $slot_name; ?>" class="formControl mp_name_validation" value="<?php echo $slot_label; ?>" placeholder="<?php _e('Time Label', 'mage-eventpress'); ?>" style="width:70px;"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="time" name="<?php echo $time_name; ?>" class="formControl" value="<?php echo $slot_time; ?>" style="width:100px;"/>
                        </label>
                    </td>
                    <td>
						<?php MP_Custom_Layout::move_remove_button(); ?>
                    </td>
                </tr>
				<?php
			}
			public function hidden_time_slot_item($slot_name, $time_name) {
				?>
                <div class="mp_hidden_content">
                    <table>
                        <tbody class="mp_hidden_item">
						<?php $this->time_slot_item($slot_name, $time_name); ?>
                        </tbody>
                    </table>
                </div>
				<?php
			}
			/*************************************/
			public function settings_save($post_id) {

                if ( ! isset( $_POST['mep_event_ticket_type_nonce'] ) ||
                    ! wp_verify_nonce( $_POST['mep_event_ticket_type_nonce'], 'mep_event_ticket_type_nonce' ) ) {
                    return;
                }
                if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                    return;
                }
                if ( ! current_user_can( 'edit_post', $post_id ) ) {
                    return;
                }


				if (get_post_type($post_id) == 'mep_events') {

					//************************************//

                        $date_type = isset( $_POST['mep_enable_recurring']) ? sanitize_text_field( $_POST['mep_enable_recurring'] ) : 'no';
                        $allowed_values = array( 'no', 'yes', 'everyday' );
                        // Optionally validate as boolean '0' or '1'
                        if ( in_array( $date_type, $allowed_values, true ) ) {
                            update_post_meta( $post_id, 'mep_enable_recurring', $date_type );
                        } else {
                            // Invalid value — maybe set a default or reject
                            update_post_meta( $post_id, 'mep_enable_recurring', 'no' );
                        }


					//**********************//
					if ($date_type == 'no' || $date_type == 'yes') {
						$start_date 	= MP_Global_Function::get_submit_info('event_start_date');
						$start_time 	= MP_Global_Function::get_submit_info('event_start_time');
						$end_date 		= MP_Global_Function::get_submit_info('event_end_date');
						$end_time 		= MP_Global_Function::get_submit_info('event_end_time');
						update_post_meta($post_id, 'event_start_date', $start_date);
						update_post_meta($post_id, 'event_start_time', $start_time);
						update_post_meta($post_id, 'event_end_date', $end_date);
						update_post_meta($post_id, 'event_end_time', $end_time);
						$start_date_more 	= MP_Global_Function::get_submit_info('event_more_start_date', []);
						$start_time_more 	= MP_Global_Function::get_submit_info('event_more_start_time', []);
						$end_date_more 		= MP_Global_Function::get_submit_info('event_more_end_date', []);
						$end_time_more 		= MP_Global_Function::get_submit_info('event_more_end_time', []);
						$more_dates = [];
						if (sizeof($start_date_more) > 0 && sizeof($end_date_more)) {
							foreach ($start_date_more as $key => $start_date) {
								if ($start_date && $end_date_more[$key]) {
									$more_dates[$key]['event_more_start_date'] = $start_date;
									$more_dates[$key]['event_more_start_time'] = $start_time_more[$key];
									$more_dates[$key]['event_more_end_date'] = $end_date_more[$key];
									$more_dates[$key]['event_more_end_time'] = $end_time_more[$key];
								}
							}
						}
						update_post_meta($post_id, 'mep_event_more_date', $more_dates);
					} else {
						$start_date 	= MP_Global_Function::get_submit_info('event_start_date_everyday');
						$start_time 	= MP_Global_Function::get_submit_info('event_start_time_everyday');
						$end_date 		= MP_Global_Function::get_submit_info('event_end_date_everyday');
						$end_time 		= MP_Global_Function::get_submit_info('event_end_time_everyday');
						update_post_meta($post_id, 'event_start_date', $start_date);
						update_post_meta($post_id, 'event_start_time', $start_time);
						update_post_meta($post_id, 'event_end_date', $end_date);
						update_post_meta($post_id, 'event_end_time', $end_time);
						//*******************//
						$periods = MP_Global_Function::get_submit_info('mep_repeated_periods', 1);
						update_post_meta($post_id, 'mep_repeated_periods', $periods);
						$offdays = MP_Global_Function::get_submit_info('mep_ticket_offdays');
						$off_days = $offdays ? explode(',', $offdays) : '';
						update_post_meta($post_id, 'mep_ticket_offdays', $off_days);
						$all_off_dates = [];
						$off_dates = MP_Global_Function::get_submit_info('mep_ticket_off_dates', []);
						if (sizeof($off_dates) > 0) {
							foreach ($off_dates as $key => $off_date) {
								if ($off_date) {
									$all_off_dates[$key]['mep_ticket_off_date'] = $off_date;
								}
							}
						}
						update_post_meta($post_id, 'mep_ticket_off_dates', $all_off_dates);
						/******************************/
						$display_time = MP_Global_Function::get_submit_info('mep_disable_ticket_time');
						$display_time = $display_time ? 'yes' : 'no';
						update_post_meta($post_id, 'mep_disable_ticket_time', $display_time);
						/******************************/
						$this->day_wise_slot_save($post_id, 'mep_ticket_times_global');
						$this->day_wise_slot_save($post_id, 'mep_ticket_times_sat');
						$this->day_wise_slot_save($post_id, 'mep_ticket_times_sun');
						$this->day_wise_slot_save($post_id, 'mep_ticket_times_mon');
						$this->day_wise_slot_save($post_id, 'mep_ticket_times_tue');
						$this->day_wise_slot_save($post_id, 'mep_ticket_times_wed');
						$this->day_wise_slot_save($post_id, 'mep_ticket_times_thu');
						$this->day_wise_slot_save($post_id, 'mep_ticket_times_fri');
						//***************//
						$special_dates = array();
						$hidden_name 	= MP_Global_Function::get_submit_info('mep_special_date_hidden_name', array());
						$date_labels 	= MP_Global_Function::get_submit_info('mep_special_date_name', array());
						$start_date 	= MP_Global_Function::get_submit_info('mep_special_start_date', array());
						$end_date 		= MP_Global_Function::get_submit_info('mep_special_end_date', array());
						if (count($start_date) > 0) {
							for ($i = 0; $i < count($start_date); $i++) {
								$time_labels = MP_Global_Function::get_submit_info('mep_special_time_label_' . $hidden_name[$i], array());
								$times = MP_Global_Function::get_submit_info('mep_special_time_value_' . $hidden_name[$i], array());
								if ($start_date[$i] != '' && $end_date[$i] != '' && sizeof($time_labels) > 0 && sizeof($times) > 0) {
									$special_dates[$i]['date_label'] = $date_labels[$i];
									$special_dates[$i]['start_date'] = date('Y-m-d', strtotime($start_date[$i]));
									$special_dates[$i]['end_date'] = date('Y-m-d', strtotime($end_date[$i]));
									$time_slot = array();
									for ($j = 0; $j < count($time_labels); $j++) {
										if ($time_labels[$j] && $times[$j] != '') {
											$time_slot[$j]['mep_ticket_time_name'] = $time_labels[$j];
											$time_slot[$j]['mep_ticket_time'] = $times[$j];
										}
									}
									$special_dates[$i]['time'] = $time_slot;
								}
							}
						}
						update_post_meta($post_id, 'mep_special_date_info', $special_dates);
					}
					//**********************//
				}
			}
			public function day_wise_slot_save($post_id, $name) {
				$all_global = [];
				$global_label = MP_Global_Function::get_submit_info($name . '_label', []);
				$global_time = MP_Global_Function::get_submit_info($name . '_time', []);
				if (sizeof($global_time) > 0 && sizeof($global_label)) {
					foreach ($global_time as $key => $time) {
						if ($time && $global_label[$key]) {
							$all_global[$key]['mep_ticket_time_name'] = $global_label[$key];
							$all_global[$key]['mep_ticket_time'] = $time;
						}
					}
				}
				update_post_meta($post_id, $name, $all_global);
			}
		}
		new MPWEM_Date_Settings();
	}