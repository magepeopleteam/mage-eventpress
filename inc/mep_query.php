<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

/**
 * This is the Main Query Function For Query the Event List, Just Pass the Required values It will return the Query As Object.
 */
function mep_event_query($show, $sort = '', $cat = '', $org = '', $city = '', $country = '', $evnt_type = 'upcoming', $state = '')
{
    $event_expire_on_old    = mep_get_option('mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime');
    $event_order_by         = mep_get_option('mep_event_list_order_by', 'general_setting_sec', 'meta_value');    
    $event_expire_on        = $event_expire_on_old == 'event_end_datetime' ? 'event_expire_datetime' : $event_expire_on_old;
    $now                    = current_time('Y-m-d H:i:s');


    if ( get_query_var('paged') ) {
        $paged = get_query_var('paged');    
    } elseif ( get_query_var('page') ) {    
        $paged = get_query_var('page');    
    } else {    
        $paged = 1;   
    }
    $etype              = $evnt_type == 'expired' ? '<' : '>';


    $cat_id = explode(',',$cat);
    $org_id = explode(',',$org);


    $cat_filter = !empty($cat) ? array(
        'taxonomy'  => 'mep_cat',
        'field'     => 'term_id',
        'terms'     => $cat_id
    ) : '';


    $org_filter = !empty($org) ? array(
        'taxonomy'  => 'mep_org',
        'field'     => 'term_id',
        'terms'     => $org_id
    ) : '';

    $city_filter = !empty($city) ? array(
        'key'       => 'mep_city',
        'value'     => $city,
        'compare'   => 'LIKE'
    ) : '';

    $state_filter = !empty($state) ? array(
        'key'       => 'mep_state',
        'value'     => $state,
        'compare'   => 'LIKE'
    ) : '';

    $country_filter = !empty($country) ? array(
        'key'       => 'mep_country',
        'value'     => $country,
        'compare'   => 'LIKE'
    ) : '';

    $expire_filter = !empty($event_expire_on) ? array(
        'key'       => $event_expire_on,
        'value'     => $now,
        'compare'   => $etype,
        'type'      => 'DATETIME'
    ) : '';

    $meta_query = array(
        'relation' => 'AND',
        $expire_filter,
        $city_filter,
        $state_filter,
        $country_filter
    );

    $tax_query = array(
        'relation' => 'AND',
        $cat_filter,
        $org_filter
    );

    $args = array(
        'post_type'         => array('mep_events'),
        'paged'             => $paged,
        'posts_per_page'    => $show,
        'order'             => $sort,
        'orderby'           => $event_order_by,
        'meta_key'          => 'event_upcoming_datetime',
        'meta_query'        => array_filter($meta_query),
        'tax_query'         => array_filter($tax_query)
    );
    return new WP_Query($args);
}

/**
 * This is the Main Function For Pagination the Event List, Just Pass the Required values It will return the Pagination with HTML
 */
function mep_event_pagination($total_page)
{

    if ( get_query_var('paged') ) {

        $paged = get_query_var('paged');
    
    } elseif ( get_query_var('page') ) {
    
        $paged = get_query_var('page');
    
    } else {
    
        $paged = 1;
    
    }

?>
    <div class="row">
        <div class="col-md-12">
            <?php
                                $pargs = array(
                                    "current" => $paged,
                                    "total" => $total_page
                                );
                                ?>
                                <div class='pagination-sec'><?php echo paginate_links($pargs); ?></div>
        </div>
    </div>
<?php
}