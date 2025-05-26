<?php 
$location = MPWEM_Functions::get_location( $event_id ); 
$show_map   	   = get_post_meta($event_id, 'mep_sgm', true);
$show_map   	   = $show_map? $show_map : 0;
$event_template   	   = get_post_meta($event_id, 'mep_event_template', true);
?>
<div class="location-widgets">
    <div>
        <div class="location-title"><?php esc_html_e( 'Event Location', 'mage-eventpress' ); ?></div>
        <p><?php echo esc_html( implode( ', ', $location ) ); ?> </p>
    </div>
</div>
<?php if($show_map): ?>
    <button type="button" class="mep-location-btn" onclick="window.location.href = '#mep-map-location'">
        <i class="fas fa-map-marker-alt"></i><?php esc_html_e( 'Find In Map', 'mage-eventpress' ); ?>
    </button>
<?php endif; ?>