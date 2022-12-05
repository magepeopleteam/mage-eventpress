<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('mep_event_location', 'mep_ev_location_ticket');
add_action('mep_event_location_ticket', 'mep_ev_location_ticket', 10, 2);

if (!function_exists('mep_ev_location_cart')) {
    function mep_ev_location_cart($event_id, $event_meta)
    {
        $location_sts = get_post_meta($event_id, 'mep_org_address', true) ? get_post_meta($event_id, 'mep_org_address', true) : '';
        ob_start();
        if ($location_sts) {
            $org_arr    = get_the_terms($event_id, 'mep_org');
            $org_id     = $org_arr[0]->term_id;
            $location   =  get_term_meta($org_id, 'org_location', true) ? get_term_meta($org_id, 'org_location', true) : '';
            $street     =  get_term_meta($org_id, 'org_street', true) ? get_term_meta($org_id, 'org_street', true) : '';
            $city       =  get_term_meta($org_id, 'org_city', true) ? get_term_meta($org_id, 'org_city', true) : '';
            $state      =  get_term_meta($org_id, 'org_state', true) ? get_term_meta($org_id, 'org_state', true) : '';
            $zip        =  get_term_meta($org_id, 'org_postcode', true) ? get_term_meta($org_id, 'org_postcode', true) : '';
            $country    =  get_term_meta($org_id, 'org_country', true) ? get_term_meta($org_id, 'org_country', true) : '';
        } else {
            $location   =  get_post_meta($event_id, 'mep_location_venue', true) ? get_post_meta($event_id, 'mep_location_venue', true) : '';
            $street     =  get_post_meta($event_id, 'mep_street', true) ? get_post_meta($event_id, 'mep_street', true) : '';
            $city       =  get_post_meta($event_id, 'mep_city', true) ? get_post_meta($event_id, 'mep_city', true) : '';
            $state      =  get_post_meta($event_id, 'mep_state', true) ? get_post_meta($event_id, 'mep_state', true) : '';
            $zip        =  get_post_meta($event_id, 'mep_postcode', true) ? get_post_meta($event_id, 'mep_postcode', true) : '';
            $country    =  get_post_meta($event_id, 'mep_country', true) ? get_post_meta($event_id, 'mep_country', true) : '';
        }

        $location_arr = [$location, $street, $city, $state, $zip, $country];
        echo esc_html(implode(', ', array_filter($location_arr)));

        $content = ob_get_clean();

        $address_arr = array(
            'location'  => $location,
            'street'    => $street,
            'state'     => $state,
            'zip'       => $zip,
            'city'      => $city,
            'country'   => $country
        );


        echo apply_filters('mage_event_location_in_cart', $content, $event_id, $event_meta, $address_arr);
    }
}


if (!function_exists('mep_ev_location_ticket')) {
    function mep_ev_location_ticket($event_id, $event_meta='')
    {
        $location_sts = get_post_meta($event_id, 'mep_org_address', true) ? get_post_meta($event_id, 'mep_org_address', true) : '';
        ob_start();
        if ($location_sts) {
            $org_arr    = get_the_terms($event_id, 'mep_org');
            $org_id     = $org_arr[0]->term_id;
            $location   =  get_term_meta($org_id, 'org_location', true) ? get_term_meta($org_id, 'org_location', true) : '';
            $street     =  get_term_meta($org_id, 'org_street', true) ? get_term_meta($org_id, 'org_street', true) : '';
            $city       =  get_term_meta($org_id, 'org_city', true) ? get_term_meta($org_id, 'org_city', true) : '';
            $state      =  get_term_meta($org_id, 'org_state', true) ? get_term_meta($org_id, 'org_state', true) : '';
            $zip        =  get_term_meta($org_id, 'org_postcode', true) ? get_term_meta($org_id, 'org_postcode', true) : '';
            $country    =  get_term_meta($org_id, 'org_country', true) ? get_term_meta($org_id, 'org_country', true) : '';
        } else {
            $location   =  get_post_meta($event_id, 'mep_location_venue', true) ? get_post_meta($event_id, 'mep_location_venue', true) : '';
            $street     =  get_post_meta($event_id, 'mep_street', true) ? get_post_meta($event_id, 'mep_street', true) : '';
            $city       =  get_post_meta($event_id, 'mep_city', true) ? get_post_meta($event_id, 'mep_city', true) : '';
            $state      =  get_post_meta($event_id, 'mep_state', true) ? get_post_meta($event_id, 'mep_state', true) : '';
            $zip        =  get_post_meta($event_id, 'mep_postcode', true) ? get_post_meta($event_id, 'mep_postcode', true) : '';
            $country    =  get_post_meta($event_id, 'mep_country', true) ? get_post_meta($event_id, 'mep_country', true) : '';
        }


        $location_arr   = [$location, $street, $city, $state, $zip, $country];
        echo esc_html(implode(', ', array_filter($location_arr)));
        $content = ob_get_clean();
        $address_arr = array(
            'location'  => $location,
            'street'    => $street,
            'state'     => $state,
            'zip'       => $zip,
            'city'      => $city,
            'country'   => $country
        );
        echo apply_filters('mage_event_location_in_ticket', $content, $event_id, $event_meta, $address_arr);
    }
}


if (!function_exists('mep_ev_location')) {
    function mep_ev_location()
    {
        global $post, $event_meta;
        $event_id = $post->ID;
        $location_sts       = get_post_meta($post->ID, 'mep_org_address', true) ? get_post_meta($post->ID, 'mep_org_address', true) : '';
        ob_start();
        if ($location_sts) {
            $org_arr    = get_the_terms($event_id, 'mep_org');
            $org_id     = $org_arr[0]->term_id;
            $location   =  get_term_meta($org_id, 'org_location', true) ? '<p>' . get_term_meta($org_id, 'org_location', true) . '</p>' : '';
            $street     =  get_term_meta($org_id, 'org_street', true) ? '<p>' . get_term_meta($org_id, 'org_street', true) . '</p>' : '';
            $city       =  get_term_meta($org_id, 'org_city', true) ? '<p>' . get_term_meta($org_id, 'org_city', true) . '</p>' : '';
            $state      =  get_term_meta($org_id, 'org_state', true) ? '<p>' . get_term_meta($org_id, 'org_state', true) . '</p>' : '';
            $zip        =  get_term_meta($org_id, 'org_postcode', true) ? '<p>' . get_term_meta($org_id, 'org_postcode', true) . '</p>' : '';
            $country    =  get_term_meta($org_id, 'org_country', true) ? '<p>' . get_term_meta($org_id, 'org_country', true) . '</p>' : '';
        } else {
            $location   =  get_post_meta($event_id, 'mep_location_venue', true) ? '<p>' . get_post_meta($event_id, 'mep_location_venue', true) . '</p>' : '';
            $street     =  get_post_meta($event_id, 'mep_street', true) ? '<p>' . get_post_meta($event_id, 'mep_street', true) . '</p>' : '';
            $city       =  get_post_meta($event_id, 'mep_city', true) ? '<p>' . get_post_meta($event_id, 'mep_city', true) . '</p>' : '';
            $state      =  get_post_meta($event_id, 'mep_state', true) ? '<p>' . get_post_meta($event_id, 'mep_state', true) . '</p>' : '';
            $zip        =  get_post_meta($event_id, 'mep_postcode', true) ? '<p>' . get_post_meta($event_id, 'mep_postcode', true) . '</p>' : '';
            $country    =  get_post_meta($event_id, 'mep_country', true) ? '<p>' . get_post_meta($event_id, 'mep_country', true) . '</p>' : '';
        }


        $location_arr = [$location, $street, $city, $state, $zip, $country];
        echo esc_html(implode(', ', array_filter($location_arr)));
        $content = ob_get_clean();
        $address_arr = array(
            'location'  => $location,
            'street'    => $street,
            'state'     => $state,
            'zip'       => $zip,
            'city'      => $city,
            'country'   => $country
        );
        echo apply_filters('mage_event_location_content', $content, $post->ID, $event_meta, $address_arr);
    }
}



add_action('mep_event_location_venue', 'mep_ev_venue');
if (!function_exists('mep_ev_venue')) {
    function mep_ev_venue($event_id = '')
    {
        global $post, $event_meta;
        if ($event_id) {
            $event = $event_id;
        } else {
            $event = $post->ID;
        }
        $location_sts = get_post_meta($event, 'mep_org_address', true);
        if ($location_sts) {
            $org_arr = get_the_terms($event, 'mep_org');
            $org_id = $org_arr[0]->term_id;
            echo get_term_meta($org_id, 'org_location', true);
        } else {
            echo get_post_meta($event, 'mep_location_venue', true);
        }
    }
}
/**
 * Event Location Get Functions
 */
if (!function_exists('mep_get_event_location')) {
    function mep_get_event_location($event_id)
    {
        $location_sts = get_post_meta($event_id, 'mep_org_address', true);
        if ($location_sts) {
            $org_arr = get_the_terms($event_id, 'mep_org');
            $org_id = $org_arr[0]->term_id;
            return get_term_meta($org_id, 'org_location', true);
        } else {
            return get_post_meta($event_id, 'mep_location_venue', true);
        }
    }
}

if (!function_exists('mep_get_event_location_street')) {
    function mep_get_event_location_street($event_id)
    {
        $location_sts = get_post_meta($event_id, 'mep_org_address', true);
        if ($location_sts) {
            $org_arr = get_the_terms($event_id, 'mep_org');
            $org_id = $org_arr[0]->term_id;
            return get_term_meta($org_id, 'org_street', true);
        } else {
            return get_post_meta($event_id, 'mep_street', true);
        }
    }
}

if (!function_exists('mep_get_event_location_city')) {
    function mep_get_event_location_city($event_id)
    {
        $location_sts = get_post_meta($event_id, 'mep_org_address', true);
        if ($location_sts) {
            $org_arr = get_the_terms($event_id, 'mep_org');
            $org_id = $org_arr[0]->term_id;
            return get_term_meta($org_id, 'org_city', true);
        } else {
            return get_post_meta($event_id, 'mep_city', true);
        }
    }
}

if (!function_exists('mep_get_event_location_state')) {
    function mep_get_event_location_state($event_id)
    {
        $location_sts = get_post_meta($event_id, 'mep_org_address', true);
        if ($location_sts) {
            $org_arr = get_the_terms($event_id, 'mep_org');
            $org_id = $org_arr[0]->term_id;
            return get_term_meta($org_id, 'org_state', true);
        } else {
            return get_post_meta($event_id, 'mep_state', true);
        }
    }
}

function mep_get_location_name_for_list($event_id)
{
}

if (!function_exists('mep_get_event_location_postcode')) {
    function mep_get_event_location_postcode($event_id)
    {
        $location_sts = get_post_meta($event_id, 'mep_org_address', true);
        if ($location_sts) {
            $org_arr = get_the_terms($event_id, 'mep_org');
            $org_id = $org_arr[0]->term_id;
            return get_term_meta($org_id, 'org_postcode', true);
        } else {
            return get_post_meta($event_id, 'mep_postcode', true);
        }
    }
}

if (!function_exists('mep_get_event_location_country')) {
    function mep_get_event_location_country($event_id)
    {
        $location_sts = get_post_meta($event_id, 'mep_org_address', true);
        if ($location_sts) {
            $org_arr = get_the_terms($event_id, 'mep_org');
            $org_id = $org_arr[0]->term_id;
            return get_term_meta($org_id, 'org_country', true);
        } else {
            return get_post_meta($event_id, 'mep_country', true);
        }
    }
}






add_action('mep_event_location_street', 'mep_ev_street');
if (!function_exists('mep_ev_street')) {
    function mep_ev_street()
    {
        global $post, $event_meta;
        $location_sts = get_post_meta($post->ID, 'mep_org_address', true);
        if ($location_sts) {
            $org_arr = get_the_terms($post->ID, 'mep_org');
            $org_id = $org_arr[0]->term_id;
            ?>
            <span><?php echo get_term_meta($org_id, 'org_street', true); ?></span>
            <?php } else { ?>
            <span><?php echo esc_html($event_meta['mep_street'][0]); ?></span>
        <?php
        }
    }
}


add_action('mep_event_location_city', 'mep_ev_city');
if (!function_exists('mep_ev_city')) {
    function mep_ev_city()
    {
        global $post, $event_meta;
        $location_sts = get_post_meta($post->ID, 'mep_org_address', true);
        if ($location_sts) {
            $org_arr = get_the_terms($post->ID, 'mep_org');
            $org_id = $org_arr[0]->term_id;
            ?>
            <span><?php echo get_term_meta($org_id, 'org_city', true); ?></span>
       <?php } else {  ?>
            <span><?php echo esc_html($event_meta['mep_city'][0]); ?></span>
        <?php
        }
    }
}



add_action('mep_event_location_state', 'mep_ev_state');
if (!function_exists('mep_ev_state')) {
    function mep_ev_state()
    {
        global $post, $event_meta;
        $location_sts = get_post_meta($post->ID, 'mep_org_address', true);
        if ($location_sts) {
            $org_arr = get_the_terms($post->ID, 'mep_org');
            $org_id = $org_arr[0]->term_id;
            ?>
            <span><?php echo get_term_meta($org_id, 'org_state', true); ?></span>
        <?php } else { ?>
            <span><?php echo esc_html($event_meta['mep_state'][0]); ?></span>
        <?php
        }
    }
}



add_action('mep_event_location_postcode', 'mep_ev_postcode');
if (!function_exists('mep_ev_postcode')) {
    function mep_ev_postcode()
    {
        global $post, $event_meta;
        $location_sts = get_post_meta($post->ID, 'mep_org_address', true);
        if ($location_sts) {
            $org_arr = get_the_terms($post->ID, 'mep_org');
            $org_id = $org_arr[0]->term_id;
            ?>
            <span><?php echo get_term_meta($org_id, 'org_postcode', true); ?></span>
        <?php } else { ?>
            <span><?php echo esc_html($event_meta['mep_postcode'][0]); ?></span>
        <?php
        }
    }
}


add_action('mep_event_location_country', 'mep_ev_country');
if (!function_exists('mep_ev_country')) {
    function mep_ev_country()
    {
        global $post, $event_meta;
        $location_sts = get_post_meta($post->ID, 'mep_org_address', true);
        if ($location_sts) {
            $org_arr = get_the_terms($post->ID, 'mep_org');
            $org_id = $org_arr[0]->term_id;
            ?>
            <span><?php echo get_term_meta($org_id, 'org_country', true); ?></span>
        <?php } else { ?>
            <span><?php echo esc_html($event_meta['mep_country'][0]); ?></span>
        <?php
        }
    }
}

add_action('mep_event_address_list_sidebar', 'mep_event_address_list_sidebar_html');
if (!function_exists('mep_event_address_list_sidebar_html')) {
    function mep_event_address_list_sidebar_html($event_id)
    {
        $location_sts   = get_post_meta($event_id, 'mep_org_address', true);
        $org_arr        = get_the_terms($event_id, 'mep_org') ? get_the_terms($event_id, 'mep_org') : '';
        $org_id         = !empty($org_arr) ? $org_arr[0]->term_id : '';
        $venue          = !empty($location_sts) ? get_term_meta($org_id, 'org_location', true) : get_post_meta($event_id, 'mep_location_venue', true);
        $street         = !empty($location_sts) ? get_term_meta($org_id, 'org_street', true) : get_post_meta($event_id, 'mep_street', true);
        $city           = !empty($location_sts) ? get_term_meta($org_id, 'org_city', true) : get_post_meta($event_id, 'mep_city', true);
        $state          = !empty($location_sts) ? get_term_meta($org_id, 'org_state', true) : get_post_meta($event_id, 'mep_state', true);
        $country        = !empty($location_sts) ? get_term_meta($org_id, 'org_country', true) : get_post_meta($event_id, 'mep_country', true);

        ob_start();
        require(mep_template_file_path('single/location_list.php'));
        echo ob_get_clean();
    }
}
