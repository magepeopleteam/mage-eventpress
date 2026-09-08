<?php
	/*
	* @Author 		engr.sumonazma@gmail.com
	* Copyright: 	mage-people.com
	*
	* Announcement mode (mep_reg_status = 'announcement'): a notice shown in place of
	* the ticket / RSVP box, optionally followed by an enquiry ("query") form.
	* Renders with or without a date, so it also covers undated events.
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id = $event_id ?? 0;
	if ( $event_id <= 0 ) {
		return;
	}
	$event_infos = $event_infos ?? [];
	$event_infos = ( is_array( $event_infos ) && sizeof( $event_infos ) > 0 ) ? $event_infos : MPWEM_Functions::get_all_info( $event_id );

	$title = trim( (string) ( is_array( $event_infos ) && array_key_exists( 'mep_announcement_title', $event_infos ) ? $event_infos['mep_announcement_title'] : '' ) );
	// The body is stored with wp_kses_post() on save, so read it raw rather than
	// through get_all_info() - that helper strips tags from every string it returns.
	$text   = (string) get_post_meta( $event_id, 'mep_announcement_text', true );
	$labels = MPWEM_Global_Function::get_enquiry_labels( $event_id );

	$enquiry_status = get_post_meta( $event_id, 'mep_enquiry_status', true );
	$show_enquiry   = '' === $enquiry_status ? true : ( 'on' === $enquiry_status );
	$show_enquiry   = (bool) apply_filters( 'mpwem_show_enquiry_form', $show_enquiry, $event_id );

	if ( '' === $title && '' === trim( wp_strip_all_tags( $text ) ) && ! $show_enquiry ) {
		return;
	}
	?>
    <div class="mpwem_announcement_area">
		<?php if ( '' !== $title || '' !== trim( wp_strip_all_tags( $text ) ) ) { ?>
            <div class="mpwem_announcement_notice">
                <span class="mpwem_announcement_notice__icon" aria-hidden="true"><i class="fas fa-bullhorn"></i></span>
                <div class="mpwem_announcement_notice__body">
					<?php if ( '' !== $title ) { ?>
                        <h4 class="mpwem_announcement_notice__title"><?php echo esc_html( $title ); ?></h4>
					<?php } ?>
					<?php if ( '' !== trim( wp_strip_all_tags( $text ) ) ) { ?>
                        <div class="mpwem_announcement_notice__text"><?php echo wp_kses_post( wpautop( $text ) ); ?></div>
					<?php } ?>
                </div>
            </div>
		<?php } ?>

		<?php do_action( 'mpwem_after_announcement_notice', $event_id, $event_infos ); ?>

		<?php if ( $show_enquiry ) { ?>
            <div class="mpwem_enquiry_area">
                <h4 class="mpwem_enquiry_title"><?php echo esc_html( $labels['mep_enquiry_title'] ); ?></h4>
                <form class="mpwem_enquiry_form" method="post" novalidate>
                    <input type="hidden" name="action" value="mep_submit_enquiry"/>
                    <input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>"/>
					<?php wp_nonce_field( 'mep_enquiry_nonce', 'nonce' ); ?>
                    <?php // Honeypot: real visitors never see it, bots fill everything in. ?>
                    <div class="mpwem_enquiry_hp" aria-hidden="true">
                        <label><?php esc_html_e( 'Leave this field empty', 'mage-eventpress' ); ?>
                            <input type="text" name="enquiry_website" tabindex="-1" autocomplete="off" value=""/>
                        </label>
                    </div>

                    <div class="mpwem_enquiry_fields">
                        <div class="mpwem_enquiry_field">
                            <label for="mpwem_enquiry_name_<?php echo esc_attr( $event_id ); ?>"><?php echo esc_html( $labels['mep_enquiry_name_label'] ); ?> <span>*</span></label>
                            <input type="text" id="mpwem_enquiry_name_<?php echo esc_attr( $event_id ); ?>" name="enquiry_name" required placeholder="<?php echo esc_attr( $labels['mep_enquiry_name_label'] ); ?>"/>
                        </div>
                        <div class="mpwem_enquiry_field">
                            <label for="mpwem_enquiry_email_<?php echo esc_attr( $event_id ); ?>"><?php echo esc_html( $labels['mep_enquiry_email_label'] ); ?> <span>*</span></label>
                            <input type="email" id="mpwem_enquiry_email_<?php echo esc_attr( $event_id ); ?>" name="enquiry_email" required placeholder="<?php echo esc_attr( $labels['mep_enquiry_email_label'] ); ?>"/>
                        </div>
                        <div class="mpwem_enquiry_field">
                            <label for="mpwem_enquiry_phone_<?php echo esc_attr( $event_id ); ?>"><?php echo esc_html( $labels['mep_enquiry_phone_label'] ); ?></label>
                            <input type="text" id="mpwem_enquiry_phone_<?php echo esc_attr( $event_id ); ?>" name="enquiry_phone" placeholder="<?php echo esc_attr( $labels['mep_enquiry_phone_label'] ); ?>"/>
                        </div>
                        <div class="mpwem_enquiry_field">
                            <label for="mpwem_enquiry_subject_<?php echo esc_attr( $event_id ); ?>"><?php echo esc_html( $labels['mep_enquiry_subject_label'] ); ?></label>
                            <input type="text" id="mpwem_enquiry_subject_<?php echo esc_attr( $event_id ); ?>" name="enquiry_subject" placeholder="<?php echo esc_attr( $labels['mep_enquiry_subject_label'] ); ?>"/>
                        </div>
                        <div class="mpwem_enquiry_field mpwem_enquiry_field--full">
                            <label for="mpwem_enquiry_message_<?php echo esc_attr( $event_id ); ?>"><?php echo esc_html( $labels['mep_enquiry_message_label'] ); ?> <span>*</span></label>
                            <textarea id="mpwem_enquiry_message_<?php echo esc_attr( $event_id ); ?>" name="enquiry_message" rows="4" required placeholder="<?php echo esc_attr( $labels['mep_enquiry_message_label'] ); ?>"></textarea>
                        </div>
                    </div>

                    <div class="mpwem_enquiry_message" role="status" aria-live="polite"></div>

                    <button type="submit" class="mpwem_enquiry_submit_btn">
                        <span><?php echo esc_html( $labels['mep_enquiry_button_label'] ); ?></span>
                    </button>
                </form>
            </div>
		<?php } ?>
    </div>
