<?php 
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action( 'admin_menu', 'mep_addon_list_menu',10,99);
function mep_addon_list_menu() {
	add_submenu_page('edit.php?post_type=mep_events', __('Get Addon','wbtm-menu'), __('<span style="color:red">Get Addons</span>','mage-eventpress'), 'manage_options', 'mep_all_addon_list', 'mep_addon_list_sec');
}

function mep_addon_list_sec(){

?>
<style type="text/css">
	.addon_list_sec .mep_addon_list li img{
		max-width: 100%;
		width: auto;
	}
	.addon_list_sec .mep_addon_list li {
    border: 1px solid #ddd!important;
    display: inline-block!important;
    float: left;
    margin: 10px!important;
    min-height: 405px;
    padding: 0px;
    position: relative;
    text-align: center;
    vertical-align: top;
    width: 31.2%!important;
    box-shadow: 3px 4px 5px #d9e1e7;
    background: #fff;
    overflow: hidden;
}
.addon_list_sec .mep_addon_list li a {
    display: block;
    background: #0000b5;
    color: #fff;
    width: 95%;
    margin: 0 auto;
    padding: 10px;
    text-decoration: none;
    font-size: 15px;
    position: absolute;
    bottom: 0;
    left: 0;
    right: auto;
}
.addon_list_sec .mep_addon_list li h3 {
    margin-top: 10px;
}

.addon_list_sec .mep_addon_list li p {
    padding: 0 15px;
    font-size: 12px;
}

.addon_list_sec .mep_addon_list li h4 {
    background: #e74635;
    color: #fff;
    font-size: 20px;
    padding: 10px;
    width: 50px;
    position: absolute;
    bottom: 40%;
    top: auto;
}
</style>
<?php
$json = file_get_contents('http://vaincode.com/update/addon-list.json');
$obj = json_decode($json, true);
if(is_array($obj) && sizeof($obj) > 0){
echo '<div class="addon_list_sec"><ul class="mep_addon_list">';
foreach ($obj as $list) {
echo '<li>';
	echo '<img src='.$list['banner'].'>';
	echo '<h3>'.$list['name'].'</h3>';
	echo '<p>'.$list['excerpt'].'</p>';
	echo '<h4>'.$list['price'].'</h4>';
	echo '<a href='.$list['url'].' target="_blank">'.$list['btn_txt'].'</a>';
echo '</li>';
}
echo '</ul></div>';
}
}