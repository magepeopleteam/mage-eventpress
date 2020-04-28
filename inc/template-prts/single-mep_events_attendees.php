<?php 
the_post();
$values = get_post_custom(get_the_id());
if ( is_user_logged_in() ) {

$user = wp_get_current_user();

$ticket_user_id = $values['ea_user_id'][0];
$current_user_id = get_current_user_id();

$qr_user_role = mep_get_option( 'mep_qr_user_role', 'qr_general_attendee_sec', 'administrator' );
	
if($ticket_user_id == $current_user_id ||  in_array( 'administrator', (array) $user->roles ) || in_array( $qr_user_role, (array) $user->roles )){
?>
<!DOCTYPE html>
<html lang="en">
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2.0">
	<?php wp_head(); ?>
<style>
	.mep-reg-user-details {
    width: 400px;
    margin: 0 auto;
    border-top: 1px solid #ddd;
    border-left: 1px solid #ddd;
    background: #fff;
    border-right: 1px solid #ddd;
}
.mep-reg-user-details table tr td {
    border-color: #ddd;
        padding: 0px 10px;
    border-bottom: 1px solid #ddd;
}
.mep-reg-user-details table tr td img {
    border-radius: 100%;
    margin: 20px 0;
    width: 100px;
    height: auto;
}
.mep-reg-user-details table tr td h2 {
    font-size: 25px;
    margin: 0;
    padding: 0;
}
.mep-reg-user-details table tr td h3 {
    font-size: 14px;
    margin: 10px 0;
    padding: 0;
    font-weight: bold;
}
.mep-reg-user-details table tr td h4 {
    font-size: 16px;
}
.mep-reg-user-details table {
    width: 100%;
}
.mep-reg-user-details h4, .mep-reg-user-details h2 {
    padding: 0;
    margin: 0;
}

.mep-new-user-details {
    width: 400px;
    border: 1px solid #ddd;
    margin: 20px auto;
}

.user-details-list ul {
    padding: 0;
    margin: 0;
    list-style: none;
}

.user-details-list ul li {
    width: 49%;
    display: inline-block;
    border-bottom: 1px dashed #ddd;
    padding: 5px;
}
.mep_event_error {
    border: 1px solid red;
    text-align: center;
    display: block;
    margin: 150px auto 0;
    text-align: center;
    width: 430px;
    background: red;
    color: #fff;
    font-size: 20px;
    text-transform: capitalize;
    padding: 20px;
}
</style>	
</head>
<body>
	<div class="mep-wrapper">
	<div class="mep-reg-user-details">
		<table>
			<tr>
				<td colspan="2" align="center">
					<center>
					<?php echo get_avatar( $values['ea_email'][0], 128 ); ?>
					<h2><?php echo $values['ea_name'][0]; ?></h2>
					<?php do_action('mep_qr_code_checkin_btn',$values['ea_user_id'][0],get_the_id()); ?>
					<h4><?php echo $values['ea_event_name'][0]; ?></h4>
				</center>
				</td>
			</tr>
			<?php do_action('mep_attendee_table_row_start',get_the_id()); ?>			
			<tr>
				<td><?php _e('Ticket No','mage-eventpress'); ?></td>
				<td><?php echo $values['ea_user_id'][0].$values['ea_order_id'][0].get_the_id(); ?></td>
			</tr>
					
			<tr>
				<td><?php _e('Order ID','mage-eventpress'); ?></td>
				<td><?php echo $values['ea_order_id'][0]; ?></td>
			</tr>			
			<?php if($values['ea_email'][0]){ ?>
			<tr>
				<td><?php _e('Email','mage-eventpress'); ?></td>
				<td><?php echo $values['ea_email'][0]; ?></td>
			</tr>
			<?php } if($values['ea_phone'][0]){ ?>
			<tr>
				<td><?php _e('Phone','mage-eventpress'); ?></td>
				<td><?php echo $values['ea_phone'][0]; ?></td>
			</tr>
			<?php } if($values['ea_address_1'][0]){ ?>
			<tr>
				<td><?php _e('Address','mage-eventpress'); ?></td>
				<td><?php echo $values['ea_address_1'][0]; ?> </td>
			</tr>
			<?php } if($values['ea_desg'][0]){ ?>
			<tr>
				<td><?php _e('Designation','mage-eventpress'); ?></td>
				<td><?php echo $values['ea_desg'][0]; ?></td>
			</tr>
			<?php } if($values['ea_company'][0]){ ?>
			<tr>
				<td><?php _e('Company','mage-eventpress'); ?></td>
				<td><?php echo $values['ea_company'][0]; ?></td>
			</tr>
			<?php } if($values['ea_website'][0]){ ?>
			<tr>
				<td><?php _e('Website','mage-eventpress'); ?></td>
				<td><?php echo $values['ea_website'][0]; ?> </td>
			</tr>
			<?php } if($values['ea_gender'][0]){ ?>
			<tr>
				<td><?php _e('Gender','mage-eventpress'); ?></td>
				<td><?php echo $values['ea_gender'][0]; ?> </td>
			</tr>

			<?php } if($values['ea_vegetarian'][0]){ ?>
			<tr>
				<td><?php _e('Vegetarian','mage-eventpress'); ?></td>
				<td><?php echo $values['ea_vegetarian'][0]; ?> </td>
			</tr>	
		

			<?php } if($values['ea_tshirtsize'][0]){ ?>
			<tr>
				<td><?php _e('T Shirt Size','mage-eventpress'); ?></td>
				<td><?php echo $values['ea_tshirtsize'][0]; ?> </td>
			</tr>		
			<?php } if($values['ea_ticket_type'][0]){ ?>
			<tr>
				<td><?php _e('Ticket Type','mage-eventpress'); ?></td>
				<td><?php echo $values['ea_ticket_type'][0]; ?> </td>
			</tr>	
			<?php } 
			$mep_form_builder_data = get_post_meta($values['ea_event_id'][0], 'mep_form_builder_data', true);
			  if ( $mep_form_builder_data ) {
			    foreach ( $mep_form_builder_data as $_field ) {
			$vname = "ea_".$_field['mep_fbc_id']; 
			$vals = $values[$vname][0];
			if($vals){
			?>
			<tr>
				<td><?php echo $_field['mep_fbc_label']; ?></td>
				<td><?php echo $vals; ?></td>
			</tr>	
		<?php
		}
	}
}
do_action('mep_attendee_table_row_end',get_the_id());
?>
		</table>
	</div>
</div>
<?php
}else{
?>
<html>
    <head>
        <title><?php _e('Sorry, You Can not see this page, Because Its not your Attendee Information.','mage-eventpress'); ?></title>
    </head>    
<body>
<h3 style="text-align: center;border: 2px solid red;color: red;font-size: 30px;width: 60%;margin: 100px auto;padding: 30px;"><?php _e('Sorry, You Can not see this page, Because Its not your Attendee Information.','mage-eventpress'); ?></h3>
<?php
}
do_action('at_footer'); 
}else{
 wp_redirect(wp_login_url(get_the_permalink()));
}
wp_footer();
?>
</body>
</html>