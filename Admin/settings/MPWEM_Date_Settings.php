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
			public function date_time_tab($post_id) {
				//$meta_values = get_post_custom($post_id);
				$event_label = mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
				$event_type = MP_Global_Function::get_post_info($post_id, 'mep_enable_recurring', 'no');
				$periods = MP_Global_Function::get_post_info($post_id, 'mep_repeated_periods', 1);
				$start_date = MP_Global_Function::get_post_info($post_id, 'event_start_date');
				$start_time = MP_Global_Function::get_post_info($post_id, 'event_start_time');
				$end_date = MP_Global_Function::get_post_info($post_id, 'event_end_date');
				$end_time = MP_Global_Function::get_post_info($post_id, 'event_end_time');
				$more_dates = MP_Global_Function::get_post_info($post_id, 'mep_event_more_date', []);
				$display_time = MP_Global_Function::get_post_info($post_id, 'mep_disable_ticket_time');
				$active_time = $display_time == 'no' ? '' : 'mActive';
				$checked_time = $display_time == 'no' ? '' : 'checked';
				//echo '<pre>';print_r(MP_Global_Function::get_post_info($post_id, 'mep_ticket_offdays'));				echo '</pre>';
				?>
                <div class="mp_tab_item" data-tab-item="#mp_event_time">
					<h3><?php esc_html_e('Date & Time','mage-eventpress') ?></h3>
					<p><?php esc_html_e('Configure Your Date and Time Settings Here','mage-eventpress') ?></p>
					
					<section class="bg-light">
						<h2><?php esc_html_e('General Settings','mage-eventpress') ?></h2>
						<span><?php esc_html_e('Configure Event Locations and Virtual Venues','mage-eventpress') ?></span>
					</section>

					<section>
						<label class="label">
							<div>
								<h2><span><?php esc_html_e('Event Type', 'mage-eventpress'); ?></span></h2>
								<span><?php _e('Select your event type','mage-eventpress'); ?></span>
							</div>
							<select class="formControl" name="mep_enable_recurring" data-collapse-target required>
                                <option disabled selected><?php esc_html_e('Please select ...', 'mage-eventpress'); ?></option>
                                <option value="no" data-option-target="#mep_normal_event" <?php echo esc_attr($event_type == 'no' ? 'selected' : ''); ?>><?php esc_html_e('Normal Event', 'mage-eventpress'); ?></option>
                                <option value="yes" data-option-target="#mep_normal_event" <?php echo esc_attr($event_type == 'yes' ? 'selected' : ''); ?>><?php esc_html_e('Particular Event', 'mage-eventpress'); ?></option>
                                <option value="everyday" data-option-target="#mep_everyday_event" <?php echo esc_attr($event_type == 'everyday' ? 'selected' : ''); ?>><?php esc_html_e('Repeated Event', 'mage-eventpress'); ?></option>
                            </select>
						</label>
					</section>

                    <div class="mpStyle">
                        <section class="mp_settings_area <?php echo esc_attr($event_type == 'no' || $event_type == 'yes' ? 'mActive' : ''); ?>" data-collapse="#mep_normal_event">
                            <table>
                                <thead>
                                <tr>
                                    <th><?php esc_html_e('Start Date', 'mage-eventpress'); ?></th>
                                    <th><?php esc_html_e('Start Time', 'mage-eventpress'); ?></th>
                                    <th><?php esc_html_e('End Date', 'mage-eventpress'); ?></th>
                                    <th><?php esc_html_e('End Time', 'mage-eventpress'); ?></th>
                                    <th><?php esc_html_e('Action', 'mage-eventpress'); ?></th>
                                </tr>
                                </thead>
                                <tbody class="mp_sortable_area mp_item_insert">
                                <tr>
                                    <td><?php self::date_item('event_start_date', $start_date); ?></td>
                                    <td><?php self::time_item('event_start_time', $start_time); ?></td>
                                    <td><?php self::date_item('event_end_date', $end_date); ?></td>
                                    <td><?php self::time_item('event_end_time', $end_time); ?></td>
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
                                        <td><?php MP_Custom_Layout::move_remove_button(); ?></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                        <div class="<?php echo esc_attr($event_type == 'everyday' ? 'mActive' : ''); ?>" data-collapse="#mep_everyday_event">
                            <div class="dFlex">
                                <h6 class="min_200"><?php esc_html_e('Start Date & Time', 'mage-eventpress'); ?></h6>
								<?php self::date_item('event_start_date_everyday', $start_date); ?>
								<?php self::time_item('event_start_time_everyday', $start_time); ?>
                            </div>
                            <div class="dFlex">
                                <h6 class="min_200"><?php esc_html_e('End Date & Time', 'mage-eventpress'); ?></h6>
								<?php self::date_item('event_end_date_everyday', $end_date); ?>
								<?php self::time_item('event_end_time_everyday', $end_time); ?>
                            </div>
                            <label>
                                <span class="min_200"><?php esc_html_e('Repeated After', 'mage-eventpress'); ?></span>
                                <input type="number" class="formControl max_200 mp_number_validation" name='mep_repeated_periods' value='<?php echo $periods; ?>'/><?php _e(' Days', 'mage-eventpress'); ?>
                            </label>
                            <d.iv class="_dFlex">
                                <h6 class="min_200"><?php esc_html_e('Ticket Offdays', 'mage-eventpress'); ?></h6>
								<?php
									$off_day_array = MP_Global_Function::get_post_info($post_id, 'mep_ticket_offdays', []);
									$off_days = $off_day_array ? implode(',', $off_day_array) : '';
									$days = MP_Global_Function::week_day();
								?>
                                <div class="groupCheckBox flexWrap">
                                    <input type="hidden" name="mep_ticket_offdays" value="<?php echo esc_attr($off_days); ?>"/>
									<?php foreach ($days as $key => $day) { ?>
                                        <label class="customCheckboxLabel min_200">
                                            <input type="checkbox" <?php echo esc_attr(in_array($key, $off_day_array) ? 'checked' : ''); ?> data-checked="<?php echo esc_attr($key); ?>"/>
                                            <span class="customCheckbox"><?php echo esc_html($day); ?></span>
                                        </label>
									<?php } ?>
                                </div>
                            </d.iv>
                            <div class="_dFlex">
                                <h6 class="min_200"><?php esc_html_e('Ticket Off Dates List', 'mage-eventpress'); ?></h6>
                                <div class="mp_settings_area max_400">
                                    <div class="mp_item_insert mp_sortable_area">
										<?php
											$off_day_lists = MP_Global_Function::get_post_info($post_id, 'mep_ticket_off_dates', array());
											//echo '<pre>';	print_r($off_day_lists);echo '</pre>';
											if (sizeof($off_day_lists)) {
												foreach ($off_day_lists as $off_day) {
													if ($off_day['mep_ticket_off_date']) {
														$this->date_item('mep_ticket_off_dates[]', $off_day['mep_ticket_off_date']);
													}
												}
											}
										?>
                                    </div>
									<?php MP_Custom_Layout::add_new_button(esc_html__('Add New Off date', 'mage-eventpress')); ?>
                                    <div class="mp_hidden_content">
                                        <div class="mp_hidden_item">
											<?php $this->date_item('mep_ticket_off_dates[]'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <label>
                                <span class="min_200"><?php esc_html_e('Display Time?', 'mage-eventpress'); ?></span>
								<?php MP_Custom_Layout::switch_button('mep_disable_ticket_time', $checked_time); ?>
                            </label>
                            <div class="<?php echo esc_attr($active_time == 'no' ? '' : 'mActive'); ?>" data-collapse="#mep_disable_ticket_time">
                                <div class="_divider"></div>
                                <div class="mpTabs topTabs tabBorder">
                                    <ul class="tabLists">
                                        <li data-tabs-target="#mep_ticket_times_global">
											<?php esc_html_e('Default Time', 'mage-eventpress'); ?>
                                        </li>
                                        <li data-tabs-target="#mep_ticket_times_sat">
											<?php esc_html_e('Saturday Time', 'mage-eventpress'); ?>
                                        </li>
                                        <li data-tabs-target="#mep_ticket_times_sun">
											<?php esc_html_e('Sunday Time', 'mage-eventpress'); ?>
                                        </li>
                                        <li data-tabs-target="#mep_ticket_times_mon">
											<?php esc_html_e('Monday Time', 'mage-eventpress'); ?>
                                        </li>
                                        <li data-tabs-target="#mep_ticket_times_tue">
											<?php esc_html_e('Tuesday Time', 'mage-eventpress'); ?>
                                        </li>
                                        <li data-tabs-target="#mep_ticket_times_wed">
											<?php esc_html_e('Wednesday Time', 'mage-eventpress'); ?>
                                        </li>
                                        <li data-tabs-target="#mep_ticket_times_thu">
											<?php esc_html_e('Thursday Time', 'mage-eventpress'); ?>
                                        </li>
                                        <li data-tabs-target="#mep_ticket_times_fri">
											<?php esc_html_e('Friday Time', 'mage-eventpress'); ?>
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
                            </div>
                        </div>
                    </div>
					<?php do_action('mp_event_recurring_every_day_setting', $post_id); ?>
                </div>
				<?php
				//echo '<pre>';print_r($meta_values);echo'</pre>';
			}
			public function time_line($post_id, $key) {
				$time_infos = MP_Global_Function::get_post_info($post_id, $key, []);
				//echo '<pre>';				print_r($time_infos);				echo '</pre>';
				?>
                <div class="mp_settings_area">
                    <table class="_layoutFixed mpwem_time_setting_table">
                        <tbody class="mp_sortable_area mp_item_insert">
						<?php if (sizeof($time_infos) > 0) {
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
                        <label><input type="text" class="formControl" value="<?php echo esc_attr($label); ?>" name="<?php echo esc_attr($key . '_label[]'); ?>"/></label>
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
			public function settings_save($post_id) {
				if (get_post_type($post_id) == 'mep_events') {
				
					//************************************//
					$date_type = MP_Global_Function::get_submit_info('mep_enable_recurring', 'no');
					update_post_meta($post_id, 'mep_enable_recurring', $date_type);
					//**********************//
					if ($date_type == 'no' || $date_type == 'yes') {
						$start_date = MP_Global_Function::get_submit_info('event_start_date');
						$start_time = MP_Global_Function::get_submit_info('event_start_time');
						$end_date = MP_Global_Function::get_submit_info('event_end_date');
						$end_time = MP_Global_Function::get_submit_info('event_end_time');
						update_post_meta($post_id, 'event_start_date', $start_date);
						update_post_meta($post_id, 'event_start_time', $start_time);
						update_post_meta($post_id, 'event_end_date', $end_date);
						update_post_meta($post_id, 'event_end_time', $end_time);
						$start_date_more = MP_Global_Function::get_submit_info('event_more_start_date', []);
						$start_time_more = MP_Global_Function::get_submit_info('event_more_start_time', []);
						$end_date_more = MP_Global_Function::get_submit_info('event_more_end_date', []);
						$end_time_more = MP_Global_Function::get_submit_info('event_more_end_time', []);
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
						$start_date = MP_Global_Function::get_submit_info('event_start_date_everyday');
						$start_time = MP_Global_Function::get_submit_info('event_start_time_everyday');
						$end_date = MP_Global_Function::get_submit_info('event_end_date_everyday');
						$end_time = MP_Global_Function::get_submit_info('event_end_time_everyday');
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
					}
					//**********************//
				}
			}
			public function day_wise_slot_save($post_id, $name) {
				$all_global = [];
				$global_time = MP_Global_Function::get_submit_info($name . '_label', []);
				$global_label = MP_Global_Function::get_submit_info($name . '_time', []);
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