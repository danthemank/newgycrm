<?php 

class StaffAccess{

    public function __construct()
    {
        add_shortcode( 'gy_staff_login', array($this, 'gy_staff_login_shortcode') );
        add_action('init', array($this, 'gy_is_staff'));
        add_action('admin_init', array($this, 'redirect_staff_to_dashboard'));
        add_action('wp_dashboard_setup', array($this, 'add_custom_dashboard_widget_attendance'));
        add_action('wp_dashboard_setup', array($this, 'add_custom_dashboard_widget_customer_lookup'));
		add_action('wp_dashboard_setup', array($this, 'add_custom_dashboard_widget_pos'));
		add_action('wp_dashboard_setup', array($this, 'add_custom_dashboard_widget_new_order'));
		add_action('wp_dashboard_setup', array($this, 'add_custom_dashboard_widget_reports'));
		
		add_action('admin_notices',  array($this, 'custom_admin_notice'));

        add_role( 'staff', 'Manager', array(
            'manage_options' => true,
            'read' => true,
            'edit_posts' => true,
            'edit_others_posts' => true,
            'publish_posts' => true,
            'edit_published_posts' => true,
            'edit_private_posts' => true,
            'edit_dashboard' => true,
            'edit_classes' => true,
            'delete_classes' => true,
            'delete_email_templates' => true,
            'edit_published_classes' => true,
            'edit_published_email_templates' => true,
            'delete_published_classes' => true,
            'delete_published_email_templates' => true,
            'edit_others_classes' => true,
            'edit_others_email_templates' => true,
            'delete_others_classes' => true,
            'delete_others_email_templates' => true,
            'edit_private_classes' => true,
            'edit_private_email_templates' => true,
            'delete_private_classes' => true,
            'delete_private_email_templates' => true,
            'publish_classes' => true,
            'publish_email_templates' => true,
            'read_private_classes' => true,
            'read_private_email_templates' => true,
            'edit_customer_information' => true,
            'read_customer_information' => true,
            'edit_customer_information_parents' => true,
            'edit_customer_information_children_parents' => true,
            'edit_pos' => true,
            'edit_pos_payments' => true,
            'edit_email_templates' => true,
            'edit_attendance' => true,
            'read_private_posts' => true,
        ) );
        
        add_role('seniorstaff', 'Senior Staff', array(
            'manage_options' => true,
            'read' => true,
            'edit_dashboard' => true,
            'edit_customer_information' => true,
            'edit_customer_information_parents' => true,
            'edit_customer_information_children_parents' => true,
            'edit_pos' => true,
            'edit_pos_payments' => true,
            'edit_attendance' => true,
            'read_customer_information' => true,
            'edit_posts' => true,
            'edit_others_posts' => true,
            'publish_posts' => true,
            'edit_published_posts' => true,
            'edit_private_posts' => true,
            'read_private_posts' => true,
        ));
        
        add_role('regularstaff', 'Regular Staff', array(
            'manage_options' => true,
            'read' => true,
            'edit_dashboard' => true,
            'edit_customer_information' => true,
            'edit_customer_information_children' => true,
            'edit_customer_information_children_parents' => true,
            'edit_attendance' => true,
            'read_customer_information' => true,
            'edit_posts' => true,
            'edit_others_posts' => true,
            'publish_posts' => true,
            'edit_published_posts' => true,
            'edit_private_posts' => true,
            'read_private_posts' => true,

        ));
        
        add_role('juniorstaff', 'Junior Staff', array(
            'manage_options' => true,
            'read' => true,
            'edit_dashboard' => true,
            'edit_customer_information_children' => true,
            'edit_attendance' => true,
            'read_customer_information' => true,
            'edit_posts' => true,
            'edit_others_posts' => true,
            'publish_posts' => true,
            'edit_published_posts' => true,
            'edit_private_posts' => true,
            'read_private_posts' => true,
        ));
        
        add_role('entrystaff', 'Entry Level', array(
            'manage_options' => true,
            'read' => true,
            'edit_dashboard' => true,
            'edit_attendance' => true,
            'edit_posts' => true,
            'edit_others_posts' => true,
            'publish_posts' => true,
            'edit_published_posts' => true,
            'edit_private_posts' => true,
            'read_private_posts' => true,
        ));

        $administrator = get_role('administrator');

        $administrator->add_cap('read_customer_information', true);
        $administrator->add_cap('edit_customer_information', true);
        $administrator->add_cap('edit_customer_information_parents', true);
        $administrator->add_cap('edit_customer_information_children_parents', true);
        $administrator->add_cap('edit_pos', true);
        $administrator->add_cap('edit_pos_payments', true);
        $administrator->add_cap('edit_attendance', true);
        
        add_filter('update_footer', function() {
            $text = '<div id="mtg-admin-footer-text">';
            $text .= '<span class="mtg-admin-footer-text-top">A website made just for you by <a href="https://mediatech.group" target="blank"><img src="' . esc_url(plugins_url('gy-crm/views/img/mt-group-logo-white-1000x148.png')) . '" alt="Media & Technology Group, LLC" /></a></span><br />';
            $text .= 'For support, call <a href="tel:+17172563886">(717) 256-3886</a>, email <a href="mailto:support@mediatech.group">support@mediatech.group</a>, or use the chat located in the bottom-right!';
            $text .= '</div>';
            $text .= '<script type="text/javascript">var $zoho=$zoho || {};$zoho.salesiq = $zoho.salesiq || {widgetcode:"f113ceb939d559a4b3d44373860f0336508886057c56238b8a60a559056f5cd3066d5c72205787973d7542a2716d72ae", values:{},ready:function(){}};var d=document;s=d.createElement("script");s.type="text/javascript";s.id="zsiqscript";s.defer=true;s.src="https://salesiq.zoho.com/widget";t=d.getElementsByTagName("script")[0];t.parentNode.insertBefore(s,t);d.write("<div id=\'zsiqwidget\'></div>");</script>';
            return $text;
        }, 11);

    }

    public function gy_staff_login_shortcode() {
        $id = get_current_user_id();
        $user_roles = get_user_by('id', $id)->roles;

        if ( is_user_logged_in() && !in_array('administrator', $user_roles) ) {

            wp_safe_redirect(home_url());
            exit;

        } else {

            $staff_roles = ['staff', 'seniorstaff', 'regularstaff', 'juniorstaff', 'entrystaff'];
            $staff_members = '';

            foreach($staff_roles as $role) {
                $staff_members .= gycrm_get_members($role);
            }

            if (isset($_POST['staff_login'])) {
                $staff_id = $_POST['staff_id'];
                $pin = $_POST['staff_pin'];

                if (!empty($staff_id) && !empty($pin)) {
                    $user = get_user_by('id', $staff_id);
                    $stored_pin = get_user_meta($staff_id, 'gy_login_pin', true);
                    $is_pin = password_verify($pin, $stored_pin);
                    
                    if ($is_pin) {
                        wp_set_current_user ( $user->ID );
                        wp_set_auth_cookie  ( $user->ID );
                        wp_safe_redirect( user_admin_url() );
                        exit();

                    } else {
                        ?>
                            <style>
                                #staff_login_form .notice-warning {
                                    display: block !important;
                                }
                                #staff_login_form .notice-warning::after {
                                    content: 'Wrong PIN for username "<?= $user->user_login ?>".';
                                }
                            </style>
                        <?php
                    }
                }
            }

            ?>
                <form action="" method="POST">
                    <div id="staff_login_form">
                        <div>
                            <div><label for="staff_members">Staff Member</label></div>
                            <div>
                                <select id="staff_members" name="staff_id">
                                    <option value="">Select Member</option>
                                    <?= $staff_members ?>
                                </select>
                            </div>
                        </div>

                        <div>
                            <div><label for="staff_pin">PIN</label></div>
                            <div><input type="password" minlength="4" maxlength="4" id="staff_pin" name="staff_pin"></div>
                        </div>
                        
                        <div><input type="submit" value="Login" name="staff_login"></div>
                        <div><a href="/lost" target="_blank">Lost your password?</a></div>
                        <div class="notice notice-warning is-dismissible hidden"></div>
                    </div>
                </form>
            <?php
        }

    }

    public function gy_is_staff() {
        $user_roles = wp_get_current_user()->roles;
            if (in_array('staff', $user_roles) ||
            in_array('regularstaff', $user_roles) ||
            in_array('seniorstaff', $user_roles) ||
            in_array('juniorstaff', $user_roles) ||
            in_array('entrystaff', $user_roles)
        ) {
            add_action('admin_menu', array($this, 'gy_staff_profile'));
        }
    }

    public function gy_staff_profile() {
        add_menu_page(
            'My Account',
            'My Account',
            'edit_posts', 
            'gy_my_profile',
            array($this, 'gy_profile_callback'),
            'dashicons-admin-users',
            6 
        );
    }

    public function gy_profile_callback() {
        ?>
        <h1 style="margin-bottom: 2rem;">My Account</h1>

        <div id="set_member_pin">
            <h2>Login PIN</h2>
            <input type="hidden" id="gy_staff_members" value="<?= get_current_user_id(); ?>">
            <div style="margin-bottom: .5rem">
                <div class="flex-container member-pin">
                    <input type="password" placeholder="Enter new PIN..." id="gycrm_pin"/>
                    <input type="button" value="Save PIN" id="save_pin"/>
                </div>
                <div class="notice notice-warning is-dismissible hidden">PIN must be 4 characters long.</div>
                <div class="notice notice-success is-dismissible hidden">PIN saved.</div>
            </div>
        </div>
        <?php
    }
    
    public function redirect_staff_to_dashboard() {
        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;
        if (is_admin() && in_array('staff', $user_roles) ||
            in_array('regularstaff', $user_roles) ||
            in_array('seniorstaff', $user_roles) ||
            in_array('juniorstaff', $user_roles) ||
            in_array('entrystaff', $user_roles)
        ) {
            $target_pages = array(
                'admin.php?page=kinsta-tools',
                'upload.php',
                'media-new.php',
                'edit.php?post_type=page',
                'post-new.php?post_type=page',
                'edit-comments.php',
                '/admin.php?page=elementor',
                'edit.php?post_type=elementor_library&tabs_group=library',
                'themes.php',
                'customize.php?return=%2Fwp-admin%2Fplugins.php%3Fplugin_status%3Dall%26paged%3D1%26s',
                'nav-menus.php',
                'theme-editor.php',
                'plugins.php',
                'plugin-install.php',
                'plugin-editor.php',
                'users.php',
                'user-new.php',
                'profile.php',
                'tools.php',
                'import.php',
                'export.php',
                'site-health.php',
                'export-personal-data.php',
                'erase-personal-data.php',
                'options-general.php',
                'options-writing.php',
                'options-reading.php',
                'options-discussion.php',
                'options-media.php',
                'options-permalink.php',
                'options-privacy.php',
            );
    
            $current_page = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';


            // Check if the current page URL matches any of the target pages
            foreach ($target_pages as $target_page) {
                if(strpos($current_page, $target_page) !== false) {
                    wp_redirect(admin_url());
                    exit;
                }
            }
        }
    }
    
    public function custom_dashboard_widget_attendance() {
		echo '<a class="admin-dashboard-image" href="/wp-admin/admin.php?page=user-list"><img src="' . esc_url( plugins_url( 'gy-crm/views/img/admin-attendance.png' ) ) . '" /></a>';
		echo '<p>Click <a href="/wp-admin/admin.php?page=user-list">here</a> to take attendance.</p>';
        echo '<p>For instructions on how to take attendance, click here.</p>';
    }
    
    public function add_custom_dashboard_widget_attendance() {
        // Widget Title
        $widget_title = 'Class Attendance';
    
        // Widget ID (should be unique)
        $widget_id = 'custom_dashboard_widget_attendance';
    
        // Function to render the widget content
        $callback = array($this, 'custom_dashboard_widget_attendance');
    
        // Widget Priority (high, core, default, low)
        $widget_priority = 'high';
    
        // Add the dashboard widget
        $is_capable = $this->get_capability('edit_attendance');

        if ($is_capable) {
            wp_add_dashboard_widget($widget_id, $widget_title, $callback, $control_callback = null, $widget_priority);
        }
    }
    
    public function custom_dashboard_widget_customer_lookup() {
        $is_capable = $this->get_capability('edit_customer_information_parents');

        if ($is_capable) {
            $link = 'user-information';
        } else {
            $link = 'user-information-children';
        }

        echo '<a class="admin-dashboard-image" href="/wp-admin/admin.php?page='.$link.'"><img src="' . esc_url( plugins_url( 'gy-crm/views/img/admin-customer-search.png' ) ) . '" /></a>';
        echo '<p>Click <a href="/wp-admin/admin.php?page='.$link.'">here</a> to look for a customer.</p>';
        echo '<p>For instructions on how to find and update customer information, click here.</p>';
    }
    
    public function add_custom_dashboard_widget_customer_lookup() {
        // Widget Title
        $widget_title = 'Customer Lookup';
    
        // Widget ID (should be unique)
        $widget_id = 'custom_dashboard_widget_customer_lookup';
    
        // Function to render the widget content
        $callback = array($this, 'custom_dashboard_widget_customer_lookup');
    
        // Widget Priority (high, core, default, low)
        $widget_priority = 'high';
    
        // Add the dashboard widget
        $is_capable = $this->get_capability('edit_customer_information');

        if ($is_capable) {
            wp_add_dashboard_widget($widget_id, $widget_title, $callback, $control_callback = null, $widget_priority);
        }
    }
	
	public function custom_dashboard_widget_pos() {
        echo '<a class="admin-dashboard-image" href="/wp-admin/admin.php?page=pos-admin-page"><img src="' . esc_url( plugins_url( 'gy-crm/views/img/admin-point-of-sale.png' ) ) . '" /></a>';
		echo '<p>Click <a href="/wp-admin/admin.php?page=pos-admin-page">here</a> to accept or record a payment.</p>';
        echo '<p>For instructions on how to take or record payments, click here.</p>';
    }
    
    public function add_custom_dashboard_widget_pos() {
        // Widget Title
        $widget_title = 'Point of Sale';
    
        // Widget ID (should be unique)
        $widget_id = 'custom_dashboard_widget_pos';
    
        // Function to render the widget content
        $callback = array($this, 'custom_dashboard_widget_pos');
    
        // Widget Priority (high, core, default, low)
        $widget_priority = 'high';
    
        $is_capable = $this->get_capability('edit_pos_payments');

        if ($is_capable) {
        // Add the dashboard widget
            wp_add_dashboard_widget($widget_id, $widget_title, $callback, $control_callback = null, $widget_priority);
        }
        
    }
	
	public function custom_dashboard_widget_reports() {
        echo '<a class="admin-dashboard-image" href="/wp-admin/admin.php?page=pos-invoices-list"><img src="' . esc_url( plugins_url( 'gy-crm/views/img/admin-reports.png' ) ) . '" /></a>';
		echo '<p>Click <a href="/wp-admin/admin.php?page=pos-invoices-list">here</a> to see all invoices.</p>';
        echo '<p>For instructions on how to view and manage invoices, click here.</p>';
    }
    
    public function add_custom_dashboard_widget_reports() {
        // Widget Title
        $widget_title = 'Reports';
    
        // Widget ID (should be unique)
        $widget_id = 'custom_dashboard_widget_reports';
    
        // Function to render the widget content
        $callback = array($this, 'custom_dashboard_widget_reports');
    
        // Widget Priority (high, core, default, low)
        $widget_priority = 'high';

        $is_capable = $this->get_capability('edit_pos');

        if ($is_capable) {
            wp_add_dashboard_widget($widget_id, $widget_title, $callback, $control_callback = null, $widget_priority);
        }
        // Add the dashboard widget
    }
	
	public function custom_dashboard_widget_new_order() {
        echo '<a class="admin-dashboard-image" href="/wp-admin/admin.php?page=pos-add-order"><img src="' . esc_url( plugins_url( 'gy-crm/views/img/admin-new-order.png' ) ) . '" /></a>';
		echo '<p>Click <a href="/wp-admin/admin.php?page=pos-add-order">here</a> to create a new order.</p>';
        echo '<p>For instructions on how to create new orders, click here.</p>';
    }
    
    public function add_custom_dashboard_widget_new_order() {
        // Widget Title
        $widget_title = 'New Order';
    
        // Widget ID (should be unique)
        $widget_id = 'custom_dashboard_widget_new_order';
    
        // Function to render the widget content
        $callback = array($this, 'custom_dashboard_widget_new_order');
    
        // Widget Priority (high, core, default, low)
        $widget_priority = 'high';
    
        // Add the dashboard widget
        $is_capable = $this->get_capability('edit_pos');

        if ($is_capable) {
            wp_add_dashboard_widget($widget_id, $widget_title, $callback, $control_callback = null, $widget_priority);
        }
    }
	
	public function custom_admin_notice() {
		// Check if the current page is the dashboard
		if (is_admin() && 'index.php' === $GLOBALS['pagenow']) {
			?>
			<div class="notice notice-info is-dismissible">
				<p>Welcome to the Gymnastics of York Membership Management and Customer Relationship Management System.</p>
				<p>Since the system is very new and in continuous development, please report any issues directly to Dan at <a href="mailto:support@mediatech.group">Media & Technology Group</a>.</p>
			</div>
			<?php
		}
	}

    public function get_capability($capability_name) {
        $is_capable = false;
        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;

        foreach ($user_roles as $role) {
            $role_object = get_role($role);
            $role_capabilities = $role_object->capabilities;

            if (array_key_exists($capability_name, $role_capabilities)) {
                $is_capable = true;
            }
        }

        return $is_capable;
    }
	
}

