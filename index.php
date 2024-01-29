<?php
/*
Plugin Name: Gymnastics of York CRM System
Description: A custom-built CRM system for Gymnastics of York
Version: 1.4
Author: Media & Technology Group, LLC
*/

define( 'GY_CRM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

function gycrm_libraries() {
	echo '<script src="https://kit.fontawesome.com/c43f5acad1.js" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/js/intlTelInput-jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/js/utils.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/css/intlTelInput.min.css" />
	<script src="https://js.stripe.com/v3/"></script>
	<script src="https://cdn.tiny.cloud/1/7eab6pokr8d2kd190xpypkm9b59q4m7bhawi1th0syj4svos/tinymce/4/tinymce.min.js" referrerpolicy="origin"></script>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-resizable@1.0.6/resizable.min.css" />
	<script src="https://cdn.jsdelivr.net/npm/jquery-resizable@1.0.6/resizable.min.js"></script>
	';
}

add_action('wp_head', 'gycrm_libraries');
add_action('admin_enqueue_scripts', 'gycrm_libraries');
add_action('admin_head', 'gycrm_libraries');


add_action('woocommerce_init', 'ajax_init');
function  ajax_init(){
	add_action("wp_ajax_update_order", "update_order");
	add_action("wp_ajax_nopriv_update_order", "update_order");
}

function gy_enqueue_crm_scripts() {
	wp_enqueue_script("jquery-ui-draggable");
	wp_enqueue_style( 'multiselect', plugins_url( 'views/css/multiSelect.css', __FILE__ ), array(), rand(111,9999), false );
	wp_enqueue_style( 'gy-crm-styles', plugins_url( 'views/css/gy-crm.css', __FILE__ ), array(), rand(111,9999), false );
	wp_enqueue_script( 'gy-crm-scripts', plugins_url( 'views/js/gy-crm-scripts.js', __FILE__ ), array( 'jquery' ), rand(111,9999), true );
	wp_enqueue_script( 'program-status-scripts', plugins_url( 'views/js/program-status.js', __FILE__ ), array( 'jquery' ), rand(111,9999), true );
	wp_enqueue_script( 'registration-scripts', plugins_url( 'views/js/registration.js', __FILE__ ), array( 'jquery' ), rand(111,9999), true );
	wp_enqueue_script( 'editing-scripts', plugins_url( 'views/js/edit-form.js', __FILE__ ), array( 'jquery' ), rand(111,9999), true );
	wp_localize_script(  'gy-crm-scripts', 'obj', array('ajaxurl' => admin_url( 'admin-ajax.php' )) );
	if (strpos($_SERVER["REQUEST_URI"], "user-information-edit")) {
		wp_enqueue_script('notes-users', plugins_url('views/js/notes-users.js', __FILE__), array('jquery'), rand(111,9999), true);
	}

	$current_user = wp_get_current_user();
	$user_roles = $current_user->roles;

	if (in_array('staff', $user_roles) ||
		in_array('seniorstaff', $user_roles) ||
		in_array('regularstaff', $user_roles) ||
		in_array('entrystaff', $user_roles) ||
		in_array('juniorstaff', $user_roles)
	) {
		wp_enqueue_style('staff-member-access-styles', plugins_url('views/css/staff-member-access.css', __FILE__ ), array(), '1.4');
		wp_enqueue_script('staff-member-access-scripts', plugins_url('views/js/staff-member-access.js', __FILE__ ), array('jquery'), '1.4', true);
		add_filter('show_admin_bar', '__return_false');
	}
}

add_action( 'init', 'gy_enqueue_crm_scripts' );


function my_enqueue_scripts() {
    wp_enqueue_script( 'jquery' );
}


add_action( 'admin_enqueue_scripts', 'my_enqueue_scripts' );

require_once GY_CRM_PLUGIN_DIR . 'controllers/AdminController.php';
require_once GY_CRM_PLUGIN_DIR . 'controllers/PublicController.php';

// AJAX
require_once GY_CRM_PLUGIN_DIR . 'ajax/attendance.php';
require_once GY_CRM_PLUGIN_DIR . 'ajax/register_edit_user.php';
require_once GY_CRM_PLUGIN_DIR . 'ajax/get_modal_child.php';
require_once GY_CRM_PLUGIN_DIR . 'hooks/hook.php';
require_once GY_CRM_PLUGIN_DIR . 'ajax/email_templates.php';
require_once GY_CRM_PLUGIN_DIR . 'ajax/easy-pos.php';
require_once GY_CRM_PLUGIN_DIR . 'ajax/staff-member-access.php';
require_once GY_CRM_PLUGIN_DIR . 'stripe/init.php';

new PublicController();
new AdminController();


// $stripe = new \Stripe\StripeClient("sk_test_51NDzTLGDW5CVzHx1WbiR9hGKqvZEapnPq5Zi5hl4QLSs1m7kZjdw8dN29EYF5X0Uiijr4M3XDVzzAWeEws7jKd69003dtMuzrl");
// var_dump($stripe->paymentIntents->all(['limit' => 3]));
?>






