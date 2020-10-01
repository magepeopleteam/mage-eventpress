<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.



add_action('admin_enqueue_scripts', 'mep_event_welcome_enqueue_scripts', 10, 1);
function mep_event_welcome_enqueue_scripts()
{
    $current_screen = get_current_screen();
    if ( 'mep_events_page_mep_event_welcome_page' != $current_screen->base ) {
        return;
    }
    wp_enqueue_style('mep-welcome-style', plugin_dir_url(__DIR__) . 'inc/welcome/css/welcome.css', array());
}


//Add admin page to the menu
add_action('admin_menu', 'mep_event_welcome_admin_menu');
function mep_event_welcome_admin_menu()
{
    add_submenu_page('edit.php?post_type=mep_events', __('Welcome', 'mage-eventpress'), __('<span style="color:green">Welcome</span>', 'mage-eventpress'), 'manage_options', 'mep_event_welcome_page',  'mep_event_welcome_page');
}
function mep_event_welcome_page()
{

?>
    <!-- Create a header in the default WordPress 'wrap' container -->
    <div class="wrap">
        <?php
        $active_tab = 'welcome';
        if (isset($_GET['tab'])) {
            $active_tab = $_GET['tab'];
        } // end if
        ?>

        <h2 class="nav-tab-wrapper mage-event-welcome-tab">
            <a href="edit.php?post_type=mep_events&page=mep_event_welcome_page&tab=welcome" class="nav-tab <?php echo $active_tab == 'welcome' ? 'nav-tab-active' : ''; ?>">Welcome</a>
            <a href="edit.php?post_type=mep_events&page=mep_event_welcome_page&tab=kwb" class="nav-tab <?php echo $active_tab == 'kwb' ? 'nav-tab-active' : ''; ?>">Support & Knowladgebase</a>
        </h2>

        <div class="tab-content">
            <?php switch ($active_tab):

                case 'welcome':
                    echo '<div class="mage-event-welcome">';
                    require_once(dirname(__DIR__) . "/inc/welcome/welcome.php");
                    echo '</div>';
                break;

                case 'kwb':
                    echo '<div class="mage-event-welcome">';
                    require_once(dirname(__DIR__) . "/inc/welcome/support.php");
                    echo '</div>';
                break;
                
                default:
                    echo '<div class="mage-event-welcome">';
                    require_once(dirname(__DIR__) . "/inc/welcome/welcome.php");
                    echo '</div>';
                    break;

            endswitch; ?>
        </div>

    </div><!-- /.wrap -->
<?php
}
