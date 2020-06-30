<?php
get_header();
the_post();
global $post, $woocommerce;
$event_meta            = get_post_custom(get_the_id());
$author_terms          = get_the_terms(get_the_id(), 'mep_org');
$book_count            = get_post_meta(get_the_id(), 'total_booking', true);
$user_api              = mep_get_option('google-map-api', 'general_setting_sec', '');
$mep_full_name         = strip_tags($event_meta['mep_full_name'][0]);
$mep_reg_email         = strip_tags($event_meta['mep_reg_email'][0]);
$mep_reg_phone         = strip_tags($event_meta['mep_reg_phone'][0]);
$mep_reg_address       = strip_tags($event_meta['mep_reg_address'][0]);
$mep_reg_designation   = strip_tags($event_meta['mep_reg_designation'][0]);
$mep_reg_website       = strip_tags($event_meta['mep_reg_website'][0]);
$mep_reg_veg           = strip_tags($event_meta['mep_reg_veg'][0]);
$mep_reg_company       = strip_tags($event_meta['mep_reg_company'][0]);
$mep_reg_gender        = strip_tags($event_meta['mep_reg_gender'][0]);
$mep_reg_tshirtsize    = strip_tags($event_meta['mep_reg_tshirtsize'][0]);
$global_template       = mep_get_option('mep_global_single_template', 'general_setting_sec', 'default-theme.php');
$current_template      = $event_meta['mep_event_template'][0];
$_current_template     = $current_template ? $current_template : $global_template;
$currency_pos           = get_option('woocommerce_currency_pos');
do_action('mep_event_single_page_after_header');
?>
<div class="mep-events-wrapper">
    <?php
    do_action('woocommerce_before_single_product');
    $theme_name = "/themes/$_current_template";
    require_once(mep_template_file_path($theme_name));
    if (comments_open() || get_comments_number()) {
        comments_template();
    }
    ?>
</div>
<div class="mep-related-events-sec">
    <?php do_action('after-single-events'); ?>
</div>
<?php 
$builder_version = mep_get_builder_version();

if($builder_version < 3.5){
?>
<script>
jQuery('#quantity_5a7abbd1bff73').click(function() {
var $form = jQuery('form'); //on a real app it would be better to have a class or ID
var $totalQuant = jQuery('#quantity_5a7abbd1bff73', $form);
jQuery('#quantity_5a7abbd1bff73', $form).change(calculateTotal);

function calculateTotal() {
   var sum = jQuery('#rowtotal').val();
   jQuery('#usertotal').html('<?php if($currency_pos=="left"){ echo get_woocommerce_currency_symbol(); } ?>' + sum * parseInt( $totalQuant.val() || 0, 10) + "<?php if($currency_pos=="right"){ echo get_woocommerce_currency_symbol(); } ?>");
}
});


jQuery(document).ready(function () {

  jQuery( "#mep-event-accordion" ).accordion({
        collapsible: true,
        active: false
  });

jQuery(document).on("change", ".etp", function() {
    var sum = 0;
    jQuery(".etp").each(function(){
        sum += +jQuery(this).val();
    });
    jQuery("#ttyttl").html(sum);
});



jQuery("#ttypelist").change(function () {
    vallllp = jQuery(this).val()+"_";
    var n = vallllp.split('_');
    var price = n[0];
    var ctt = 99;
if(vallllp!="_"){

   var currentValue = parseInt(ctt);
   jQuery('#rowtotal').val(currentValue += parseFloat(price));
}
if(vallllp=="_"){
    jQuery('#eventtp').attr('value', 0);
    jQuery('#eventtp').attr('max', 0);
    jQuery("#ttypeprice_show").html("")
}


});

function updateTotal() {
    var total = 0;
    vallllp = jQuery(this).val()+"_";
    var n = vallllp.split('_');
    var price = n[0];
    total += parseFloat(price);
    jQuery('#rowtotal').val(total);
}

//Bind the change event
jQuery(".extra-qty-box").on('change', function() {
        var sum = 0;
        var total = <?php if($event_meta['_price'][0]){ echo $event_meta['_price'][0]; }else{ echo 0; } ?>;

        jQuery('.price_jq').each(function () {
            var price = jQuery(this);
            var count = price.closest('tr').find('.extra-qty-box');
            sum = (price.html() * count.val());
            total = total + sum;
            // price.closest('tr').find('.cart_total_price').html(sum + "â‚´");

        });

        jQuery('#usertotal').html("<?php if($currency_pos=="left"){ echo get_woocommerce_currency_symbol(); } ?>" + total + "<?php if($currency_pos=="right"){ echo get_woocommerce_currency_symbol(); } ?>");
        jQuery('#rowtotal').val(total);

    }).change(); //trigger change event on page load

<?php
$mep_event_ticket_type = get_post_meta($post->ID, 'mep_event_ticket_type', true);
if($mep_event_ticket_type){
$count =1;
$event_id = $post->ID;
$event_more_date[0]['event_more_start_date']    = date('Y-m-d',strtotime(get_post_meta($event_id,'event_start_date',true)));
$event_more_date[0]['event_more_start_time']    = date('H:i',strtotime(get_post_meta($event_id,'event_start_time',true)));
$event_more_date[0]['event_more_end_date']      = date('Y-m-d',strtotime(get_post_meta($event_id,'event_end_date',true)));
$event_more_date[0]['event_more_end_time']      = date('H:i',strtotime(get_post_meta($event_id,'event_end_time',true)));
$event_more_dates                               = get_post_meta($event_id,'mep_event_more_date',true) ? get_post_meta($event_id,'mep_event_more_date',true) : array();
$recurring = get_post_meta($event_id, 'mep_enable_recurring', true) ? get_post_meta($event_id, 'mep_enable_recurring', true) : 'no';
if($recurring == 'yes'){
    $event_multi_date                               = array_merge($event_more_date,$event_more_dates);
}else{
    $event_multi_date                               = $event_more_date;
}

foreach($event_multi_date as $event_date){
    $start_date = $recurring == 'yes' ? date('Y-m-d H:i:s', strtotime($event_date['event_more_start_date'] . ' ' . $event_date['event_more_start_time'])) : date('Y-m-d H:i:s', strtotime(mep_get_event_expire_date($event_id)));
if(strtotime(current_time('Y-m-d H:i:s')) < strtotime($start_date)){
foreach ( $mep_event_ticket_type as $field ) {
$qm = $field['option_name_t'];
?>
            var inputs = jQuery("#ttyttl").html() || 0;
            var inputs = jQuery('#eventpxtp_<?php echo $count; ?>').val() || 0;
            var input = parseInt(inputs);
            var children=jQuery('#dadainfo_<?php echo $count; ?> > div').length || 0;

            var selected_ticket = jQuery('#ttyttl').html();

            if(input < children){
                jQuery('#dadainfo_<?php echo $count; ?>').empty();
                children=0;
            }
            for (var i = children+1; i <= input; i++) {
                jQuery('#dadainfo_<?php echo $count; ?>').append(
                jQuery('<div/>')
                    .attr("id", "newDiv" + i)
                    .html("<?php do_action('mep_reg_fields',$start_date, get_the_id(), $qm); ?>")
                );
            }

jQuery('#eventpxtp_<?php echo $count; ?>').on('change', function () {
        var inputs = jQuery("#ttyttl").html() || 0;
        var inputs = jQuery('#eventpxtp_<?php echo $count; ?>').val() || 0;
        var input = parseInt(inputs);
        var children=jQuery('#dadainfo_<?php echo $count; ?> > div').length || 0;
        jQuery(document).on("change", ".etp", function() {
        var TotalQty = 0;
        jQuery(".etp").each(function(){
            TotalQty += +jQuery(this).val();
         });
        });
        if(input < children){
            jQuery('#dadainfo_<?php echo $count; ?>').empty();
            children=0;
        }
        for (var i = children+1; i <= input; i++) {
            jQuery('#dadainfo_<?php echo $count; ?>').append(
            jQuery('<div/>')
                .attr("id", "newDiv" + i)
                .html("<?php do_action('mep_reg_fields',$start_date, get_the_id(), $qm); ?>")
            );
        }
    });
<?php
$count++;
    }

}
}

 }else{
?>
jQuery('#quantity_5a7abbd1bff73').on('change', function () {
        var input = jQuery('#quantity_5a7abbd1bff73').val() || 0;
        var children=jQuery('#divParent > div').length || 0;

        if(input < children){
            jQuery('#divParent').empty();
            children=0;
        }
        for (var i = children+1; i <= input; i++) {
            jQuery('#divParent').append(
            jQuery('<div/>')
                .attr("id", "newDiv" + i)
    });
<?php
}
?>
});
</script>
<?php 
}else{
    do_action('mep_event_single_template_end',get_the_id()); 
}?>
<?php 
do_action('mep_event_single_page_before_footer');
get_footer(); 
