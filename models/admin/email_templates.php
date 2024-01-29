<?php

class EmailTemplates
{
    public static $price_per_hour;
    public static $registration_fee;
    public $discount_coupon;

    public function __construct($price_per_hour)
    {
        self::$price_per_hour = $price_per_hour;
        self::$registration_fee = get_option('registration_fee');
        $this->discount_coupon = 'sf2zxd6b';

        add_action('init', array($this, 'create_email_templates_cpt'), 11);

        // Add a new submenu item under 'Email Templates'
        add_action('admin_menu', array($this, 'email_template_editor_menu'), 11);

        // Add custom schedules for weekly and monthly events
        add_filter('cron_schedules', array($this, 'my_cron_schedules'), 11);

        // Send scheduled emails
        add_action('gycrm_scheduled_email', array($this, 'gycrm_scheduled_email'), 11, 7);
    }

    function my_cron_schedules($schedules){
        if(!isset($schedules["5min"])){
            $schedules["5min"] = array(
                'interval' => 5*60,
                'display' => __('Once every 5 minutes'));
        }
        return $schedules;
    }


    public function create_email_templates_cpt()
    {
        $labels = array(
            'name' => 'Email Templates',
            'singular_name' => 'Email Template',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Email Template',
            'edit_item' => 'Edit Email Template',
            'new_item' => 'New Email Template',
            'all_items' => 'All Email Templates',
            'view_item' => 'View Email Template',
            'search_items' => 'Search Email Templates',
            'not_found' => 'No email templates found',
            'not_found_in_trash' => 'No email templates found in Trash',
            'parent_item_colon' => '',
            'menu_name' => 'Email Templates',
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => array('title', 'editor'),
            'menu_position' => 30,
            'menu_icon' => 'dashicons-email',
            'show_in_rest' => true,
            'rest_base'  => 'email_template',
            'capabilities' => array ('edit_email_templates' => true),
        );

        register_post_type('email_template', $args);
    }

    public function email_template_editor_menu()
    {
        $parent_slug = 'edit.php?post_type=email_template';
        add_submenu_page($parent_slug, 'Send Emails', 'Send Emails', 'edit_email_templates', 'send-emails', array($this, 'send_emails_page'));
        add_submenu_page($parent_slug, 'Scheduled Emails List', 'Scheduled Emails List', 'edit_email_templates', 'scheduled-emails', array($this, 'scheduled_emails_page'));
    }

    public function scheduled_emails_page() {
        $from = !empty(get_option('custom_note_from')) ? get_option('custom_note_from') : 'ca@gymnasticsofyork.com';
        $replyto = !empty(get_option('custom_note_replyto')) ? get_option('custom_note_replyto') : 'ca@gymnasticsofyork.com';
        $bcc  = !empty(get_option('custom_note_bcc')) ? get_option('custom_note_bcc') : 'ca@gymnasticsofyork.com';

        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: <'.$from.'>';
        $headers[] = 'Reply-To: <'.$replyto.'>';
        $headers[] = 'Bcc: '.$bcc;

        $saved_schedules =_get_cron_array();

        if (isset($_POST['save_schedule'])) {
            $template_message = wp_kses_post($_POST['message']);
            $subject = $_POST['subject'];
            $email_type = $_POST['email_type'];
            $email_schedule = $_POST['email_schedule'];
            $comma_email = $_POST['comma_email'];
            $schedule_status = $_POST['schedule_status'];
            $id = $_POST['event_id'];

            $cron = get_option('cron');

            foreach ($cron as &$item) {
                if (isset($item["gycrm_scheduled_email"])) {
                    foreach($item["gycrm_scheduled_email"] as $key => &$schedule) {
                        if ($key == $id) {
                            $schedule['args']['subject'] = $subject;
                            $schedule['args']['email_type'] = $email_type;
                            $schedule['args']['template_message'] = $template_message;
                            $schedule['args']['email_schedule'] = $email_schedule;
                            $schedule['args']['schedule_status'] = $schedule_status;
                            $schedule['args']['comma_email'] = $comma_email;
                        }
                    }
                }
            }

            update_option('cron', $cron);

            wp_redirect('/wp-admin/edit.php?post_type=email_template&page=scheduled-emails', 301);
        }

        if (isset($_POST['delete_schedule'])) {
            $id = $_POST['schedule_id'];

            $cron = get_option('cron');

            foreach ($cron as $key1 => &$item) {
                if (isset($item["gycrm_scheduled_email"])) {
                    foreach($item["gycrm_scheduled_email"] as $key2 => &$schedule) {
                        if ($key2 == $id) {
                            unset($cron[$key1]);
                        }
                    }
                }
            }

            update_option('cron', $cron);

            wp_redirect('/wp-admin/edit.php?post_type=email_template&page=scheduled-emails', 301);
        }

        $list = '';

        foreach ($saved_schedules as $cron) {
            if (isset($cron["gycrm_scheduled_email"])) {
                foreach($cron["gycrm_scheduled_email"] as $key => $schedule) {
                    $email_type_name = $schedule['args']['email_type'];

                    switch($email_type_name) {
                        case 'all_admin':
                            $email_type = 'All Administrators';
                        break;
                        case 'all_customers':
                            $email_type = 'All Customers';
                        break;
                        case 'accounts-owing':
                            $email_type = 'All Accounts Owing';
                        break;
                        case 'comma':
                            $email_type = 'Comma Separated List';
                        break;
                    }

                    $list .= '<tr id="'.$key.'">
                                <td class="subject">'.$schedule['args']['subject'].'</td>
                                <td class="hover-message">
                                    <div class="template-message">'.$schedule['args']['template_message'].'</div>
                                    <div class="message-expanded">
                                        '.$schedule['args']['template_message'].'
                                    </div>
                                </td>
                                <td class="email_schedule">'.$schedule['args']['email_schedule'].'</td>
                                <td class="email_type">'.$email_type.'</td>
                                <td class="comma_email">'.(isset($schedule['args']['comma_email']) ? $schedule['args']['comma_email'] : '').'</td>
                                <td class="schedule_status">'.$schedule['args']['schedule_status'].'</td>
                                <td>'.$schedule['args']['by'].'</td>
                                <td class="edit-button edit-btn" data-modal="#edit_schedule" data-row="'.$key.'">
                                    <svg xmlns="http://www.w3.org/2000/svg" 
                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                                    style="width: 24px; height: 24px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                </td>
                                <td class="delete-btn edit-btn" data-modal="#confirm_delete_schedule" data-row="'.$key.'">
                                    <svg xmlns="http://www.w3.org/2000/svg" 
                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                                    style="width: 24px; height: 24px; color:red;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </td>
                            </tr>';
                }
            }
        }

        if (empty($list)) {
            $list = '<tr>
                    <td colspan="8">No scheduled Emails</td>
                </tr>';
        }

        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/email_templates/scheduled_emails_list.php';
    }

    public function send_emails_page()
    {
        $users = get_users(array(
            'status' => 'active',
        ));

        $programs = get_posts(array(
            'post_type' => 'class',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'post_title',
            'order' => 'ASC',
        ));

        if (!empty($_POST)) {

            if (!empty($_POST['email_template']) && !empty($_POST['email_subject']) && !empty($_POST['email_schedule'])) {

                $selected_template = sanitize_text_field($_POST['email_template']);
                $subject = sanitize_text_field($_POST['email_subject']);
                // $selected_role = sanitize_text_field($_POST['user_role']);
                $email_schedule = sanitize_text_field($_POST['email_schedule']);

                $from = !empty(get_option('custom_note_from')) ? get_option('custom_note_from') : 'ca@gymnasticsofyork.com';
                $replyto = !empty(get_option('custom_note_replyto')) ? get_option('custom_note_replyto') : 'ca@gymnasticsofyork.com';
                $bcc  = !empty(get_option('custom_note_bcc')) ? get_option('custom_note_bcc') : 'ca@gymnasticsofyork.com';

                $headers[] = 'Content-Type: text/html; charset=UTF-8';
                $headers[] = 'From: <'.$from.'>';
                $headers[] = 'Reply-To: <'.$replyto.'>';
                $headers[] = 'Bcc: '.$bcc;

                $users = get_users();

                if (isset($_POST['submit_email']) && !empty($_POST['email_content'])) {
                    
                    $template_message = wp_kses_post($_POST['email_content']);
                    $template_message = nl2br($template_message);
                    check_admin_referer('send_emails_action');
                    
                    if ($email_schedule == 'now') {
                        
                        if ($_POST['email_type'] == "single") {
                            
                            $email = sanitize_text_field($_POST['to']);
                            $user = get_user_by('email', $email);

                            switch ($subject) {
                                case 'Subscription Renewal Invoice': 
                                    self::get_subscription_invoice($email, $template_message, $subject, $headers);

                                    break;

                                default:
                                
                                    $template_message = $this->merge_tags($template_message, $user);

                                    $is_sent = wp_mail($email, $subject, $template_message, $headers);
                                    if ($is_sent) {
                                        $current = get_current_user_id();
                                        $current_user = get_user_by('id', $current);
                                        $comment_user = array(
                                            'comment_author' => $current_user->display_name,
                                            'comment_content' => 'Email "'.$subject.'" sent to '. $user->user_email .'. ',
                                            'user_id' => $user->ID,
                                            'comment_meta'         => array(
                                                'is_customer_note'       => sanitize_text_field(1),
                                                )
                                            );
                            
                                        wp_insert_comment($comment_user);
                                    }
                                break;
                            }       
                            

                        } else if ($_POST['email_type'] == "all") {
                            // Send masive emails using Bcc  
                            $this->sendEmailByBatches($users, 'customer',  $subject, $template_message, $headers);

                            // Accounts Owing Bulk email
                        } else if ($_POST['email_type'] == "accounts-owing") {
                            $template_message = wp_kses_post($_POST['email_content']);
                            $template_message = nl2br($template_message);
                            if (isset($_POST['selected_users_owing'])) {
                                foreach ($_POST['selected_users_owing'] as $id) {
                                    $user = get_user_by('id', $id);
                                    if ($user) {
                                        
                                        switch ($subject) {
                                            case 'Subscription Renewal Invoice': 
                                                $this->get_subscription_invoice($user->user_email, $template_message, $subject, $headers);
            
                                                break;
            
                                            default:
                                                $template_message = $this->merge_tags($template_message, $user);

                                                $is_sent = wp_mail($user->user_email, $subject, $template_message, $headers);
                                                if ($is_sent) {
                                                    $current = get_current_user_id();
                                                    $current_user = get_user_by('id', $current);
                                                    $comment_user = array(
                                                        'comment_author' => $current_user->display_name,
                                                        'comment_content' => 'Email "'.$subject.'" sent to '. $user->user_email .'. ',
                                                        'user_id' => $user->ID,
                                                        'comment_meta'         => array(
                                                            'is_customer_note'       => sanitize_text_field(1),
                                                            )
                                                        );
                                        
                                                    wp_insert_comment($comment_user);
                                                }
                                            break;
                                        }
                                    }   
                                }
                            }
                            // Send e-mails to those who do not have a saved card
                        } else if ($_POST['email_type'] == "no-credit") {
                            $template_message = wp_kses_post($_POST['email_content']);
                            $template_message = nl2br($template_message);
                            if (isset($_POST['selected_users_credit'])) {
                                foreach ($_POST['selected_users_credit'] as $id) {
                                    $user = get_user_by('id', $id);
                                    if ($user) {
                                        switch ($subject) {
                                            case 'Subscription Renewal Invoice': 
                                                $this->get_subscription_invoice($user->user_email, $template_message, $subject, $headers);
            
                                                break;
            
                                            default:
                                                $template_message = $this->merge_tags($template_message, $user);

                                                $is_sent = wp_mail($user->user_email, $subject, $template_message, $headers);
                                                if ($is_sent) {
                                                    $current = get_current_user_id();
                                                    $current_user = get_user_by('id', $current);
                                                    $comment_user = array(
                                                        'comment_author' => $current_user->display_name,
                                                        'comment_content' => 'Email "'.$subject.'" sent to '. $user->user_email .'. ',
                                                        'user_id' => $user->ID,
                                                        'comment_meta'         => array(
                                                            'is_customer_note'       => sanitize_text_field(1),
                                                            )
                                                        );
                                        
                                                    wp_insert_comment($comment_user);
                                                }
                                            break;
                                        }
                                    }   
                                }
                            }

                            
                            // Send e-mails using a comma as a separator
                        } else if ($_POST['email_type'] == "comma") {      
                            global $wpdb;
                            // Get the value of the email input field.
                            $email_addresses = sanitize_text_field($_POST['comma_email']);
                    
                            // Convert comma-separated text to an array of e-mail addresses.
                            $email_list = explode(',', $email_addresses);
                    
                            foreach ($email_list as $email) {
                                $cleaned_email = sanitize_email(trim($email));
                                if (is_email($cleaned_email)) {
                                    $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_email = %s", $cleaned_email));
                                    
                                    $template_message = $this->merge_tags($template_message, $user);

                                    $is_sent = wp_mail($cleaned_email, $subject, $template_message, $headers);
                                    if ($is_sent) {
                                        $current = get_current_user_id();
                                        $current_user = get_user_by('id', $current);
                                        $comment_user = array(
                                            'comment_author' => $current_user->display_name,
                                            'comment_content' => 'Email "'.$subject.'" sent to '. $cleaned_email .'. ',
                                            'user_id' => $user->ID,
                                            'comment_meta'         => array(
                                                'is_customer_note'       => sanitize_text_field(1),
                                                )
                                            );
                            
                                        wp_insert_comment($comment_user);
                                    }
                                }
                            }

                        } else if ($_POST['email_type'] == 'program') {
                            if (isset($_POST['selected_users_programs'])) {

                                foreach ($_POST['selected_users_programs'] as $id) {
                                    $user = get_user_by('id', $id);
                                    
                                    if ($user) {
                                        
                                        switch ($subject) {
                                            case 'Subscription Renewal Invoice': 
                                                $this->get_subscription_invoice($user->user_email, $template_message, $subject, $headers);
            
                                                break;
            
                                            default:
                                            $template_message = $this->merge_tags($template_message, $user);

                                            $is_sent = wp_mail($user->user_email, $subject, $template_message, $headers);
                                            if ($is_sent) {
                                                $current = get_current_user_id();
                                                $current_user = get_user_by('id', $current);
                                                $comment_user = array(
                                                    'comment_author' => $current_user->display_name,
                                                    'comment_content' => 'Email "'.$subject.'" sent to '. $user->user_email .'. ',
                                                    'user_id' => $user->ID,
                                                    'comment_meta'         => array(
                                                        'is_customer_note'       => sanitize_text_field(1),
                                                        )
                                                    );
                                    
                                                wp_insert_comment($comment_user);
                                            }
                                            break;
                                        }
                                    }   
                                }
                            } 

                            if (isset($_POST['programs_classes']) && $_POST['programs_classes'] == 'all_programs') {

                                $enrolled_users = $this->get_enrolled_users_only();

                                $this->sendEmailByBatches($enrolled_users, 'customer', $subject, $template_message, $headers);

                            }
                        } else if ($_POST['email_type'] == 'tag') {
                            if (isset($_POST['selected_users_tags'])) {

                            foreach ($_POST['selected_users_tags'] as $id) {
                                $user = get_user_by('email', $id);
                                switch ($subject) {
                                    case 'Subscription Renewal Invoice': 
                                        $this->get_subscription_invoice($user->user_email, $template_message, $subject, $headers);
                                        break;
                                    default:
                                    $template_message = $this->merge_tags($template_message, $user);

                                    $is_sent = wp_mail($user->user_email, $subject, $template_message, $headers);
                                    if ($is_sent) {
                                        $current = get_current_user_id();
                                        $current_user = get_user_by('id', $current);
                                        $comment_user = array(
                                            'comment_author' => $current_user->display_name,
                                            'comment_content' => 'Email "'.$subject.'" sent to '. $uuser->user_email .'. ',
                                            'user_id' => $user->ID,
                                            'comment_meta'         => array(
                                                'is_customer_note'       => sanitize_text_field(1),
                                                )
                                            );
                            
                                        wp_insert_comment($comment_user);
                                    }
                                    break;
                                }
                            }
                        }
    
                    echo '<div class="notice notice-success is-dismissible">Success: Email sent.</div>';
    
                } else if (isset($_POST['submit_schedule']) && !empty($_POST['email_content_schedule']) && !empty($_POST['schedule_status'])) {
                    $admin = wp_get_current_user();
                    
                    check_admin_referer('send_schedule_emails_action');
                    $template_message = wp_kses_post($_POST['email_content_schedule']);
                    $template_message = nl2br($template_message);

                    $saved_schedules =_get_cron_array();
                    $is_schedule = false;

                    foreach ($saved_schedules as $cron) {
                        if (isset($cron["gycrm_scheduled_email"])) {
                            foreach($cron["gycrm_scheduled_email"] as $schedule) {
                                if ($schedule['args']['email_schedule'] == $email_schedule && $schedule['args']['subject'] == $subject && $schedule['args']['email_type'] == $_POST['email_type']) {
                                    $is_schedule = true;
                                }
                            }
                        }
                    }

                    if (!$is_schedule) {
                        $timestamp = time();
                        if ($_POST['email_type'] === 'comma') {
                            wp_schedule_event($timestamp, 'daily', 'gycrm_scheduled_email', array('subject' => $subject, 'template_message' => $template_message, 'headers' => $headers, 'email_type' => $_POST['email_type'], 'email_schedule' => $email_schedule, 'schedule_status' => $_POST['schedule_status'], 'by' => $admin->display_name, 'comma_email' => $_POST['comma_email']));
                        } else {
                            wp_schedule_event($timestamp, 'daily', 'gycrm_scheduled_email', array('subject' => $subject, 'template_message' => $template_message, 'headers' => $headers, 'email_type' => $_POST['email_type'], 'email_schedule' => $email_schedule, 'schedule_status' => $_POST['schedule_status'], 'by' => $admin->display_name));
                        }
                        echo '<div class="notice notice-success is-dismissible">Success: Email scheduled.</div>';
                    } else {
                        echo '<div class="notice notice-warning is-dismissible">Error: <span>"'.$subject.'"</span> email is already scheduled to that group</div>';
                    }
                }
        
                if (isset($_POST['save_template_email'])) {
                    $my_post = array(
                        'post_type'     => 'email_template',
                        'post_title'    => wp_strip_all_tags($_POST['template_name']),
                        'post_content'  => wp_kses_post($_POST['email_content']),
                        'post_status'   => 'publish',
                        'posts_per_page' => -1,
                    );
        
                    wp_insert_post($my_post);
                }
            } else {
                echo '<div class="notice notice-warning is-dismissible">Error: Please fill in all fields.</div>';
            }
        }
        }
    }
       
        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/email_templates/send_email.php';
    }

    public function sendEmailByBatches($users, $role, $subject, $template_message, $headers) {
        $batchSize = 100;

        if (!empty($users)) {
            for ($i = 0; $i < count($users); $i += $batchSize) {
    
                $batchRecipients = array_slice($users, $i, $batchSize);
    
                foreach ($batchRecipients as $recipient) {
                    if ($recipient->roles[0] == $role) {
                        switch ($subject) {
                            case 'Subscription Renewal Invoice': 
                                    self::get_subscription_invoice($recipient->user_email, $template_message, $subject, $headers);
                            break;
    
                        default:
                            $template_message = $this->merge_tags($template_message, $recipient);

                            wp_mail($recipient->user_email, $subject, $template_message, $headers);
                        break;
                        }
                    } 
                }
    
                sleep(10);
            }
        }

    }

    public static function get_subscription_invoice($email, $template_message, $subject, $headers, $args = null) {
        $automatic_invoice = get_option('automatic_monthly_invoices');
        
        if ($automatic_invoice || isset($args['not_automatic'])) {
            $user = get_user_by('email', $email);

            $is_due_registration = false;
            $registration_month = get_user_meta($user->ID, 'due_registration_month', true);

            if ($registration_month == date('F')) {
                $is_due_registration = true;
            }

            $has_siblings = false;
            $sibling_rate = array();
            $siblings_total = 0;
            $sibling_count = 0;
            $products_ids = [];

            $total_hours = 0;

            $product_list = '';

            $invoice_table = '
                    <table style="border-collapse: collapse; width: 100%;">
                        <thead>
                            <tr>
                                <th style="border: 1px solid black;">Class</th>
                                <th style="border: 1px solid black;">Hours / Week</th>
                                <th style="border: 1px solid black;">Monthly fee</th>
                            </tr>
                        </thead>
                        <tbody>';

            if (isset($args['children'])) {
                $children = $args['children'];
            } else {
                $children = get_user_meta($user->ID, 'smuac_multiaccounts_list', true);
            }
            
            
            if (!empty($children)) {

                if (isset($args['children'])) {
                    if (count($children) >= 2) {
                        $has_siblings = true;
                    }
                } else {
                    $children = explode(',', $children);
                    if (count($children) >= 3) {
                        $has_siblings = true;
                    }
                }

                foreach($children as $key => $child) {

                    if (!empty($child)) {
                        $counter = 0;
                        
                        $total_hours_per_child = 0;

                        if (isset($args['children'])) {
                            $classes = explode(',', $child['classes']);
                            $status = 'active';
                        } else{
                            $classes = get_user_meta($child, 'classes', true);
                            $status = get_user_meta($child, 'status_program_participant', true);
                        }


                    if (is_array($classes[0])) {
                        $classes = $classes[0];
                    }

                    foreach($classes as $class) {
                        if (!empty($class)) {
                            $is_class = true;
                        }
                    }

                    if (!empty($classes) && $is_class && $status == 'active') {
                        $first_name = get_user_meta($child, 'first_name', true);
                        $product_list .= '<li>'.$first_name.'</li>
                                            <ul>';
                        $sibling_count += 1;

                        foreach ($classes as $class) {

                            if (!empty($class) && !empty(get_post($class))) {
                                $product_id = get_post_meta($class, 'product_id', true);
                                $products_ids[] = $product_id;

                                $hours_per_week = get_post_meta($class, 'hours_per_week', true);
                                if ($hours_per_week) {

                                    $counter += 1;

                                    $post_args = array('post_type' => 'class',
                                        'post_status' => 'publish',
                                        'p' => $class,
                                        'posts_per_page' => -1,
                                    );

                                    $post_title = wp_list_pluck( get_posts( $post_args ), 'post_title' );

                                    $total_hours_per_child += floatval($hours_per_week);
                                    $total_hours += floatval($hours_per_week);
                                    $price = self::$price_per_hour[strval($hours_per_week)];
                                    $price_per_child = self::$price_per_hour[strval($total_hours_per_child)];
                                    
                                    $product_list .= '<li>'.$post_title[0].': <strong>'.$hours_per_week.' Hours Training / Week</strong></li>';

                                    $invoice_table .= '
                                                <tr style="text-align: center;">
                                                    <td style="border: 1px solid black;">'.$post_title[0].'</td>
                                                    <td style="border: 1px solid black;">'.$hours_per_week.'</td>
                                                    <td style="border: 1px solid black;">'.wc_price($price).'</td>
                                                </tr>';

                                }
                            }
                        }

                            if ($has_siblings) {
                                $sibling_rate[] = $price_per_child;
                            }
                            $siblings_total += $price_per_child;

                            $invoice_table .= '<tr>
                                <th style="text-align: right; border: 1px solid black;">Total hours</th>
                                <th style="text-align: center; border: 1px solid black;">'.$total_hours_per_child.'</th>
                                <th style="text-align: center; border: 1px solid black;">'.wc_price($price_per_child).'</th>
                            </tr>';

                            $product_list .= '</ul>';
                        }
                    }
                }

                if ($total_hours > 0) {

                    if (count($sibling_rate) > 1 && $sibling_count >= 1) {
                        $total_amount = $siblings_total;
                    } else {
                        $total_amount = self::$price_per_hour[strval($total_hours)];
                    }

                    $subtotal = '';
                    $original_total = $total_amount;

                    if (count($sibling_rate) > 1 && $sibling_count >= 1) {
                        $last_sibling_rate = min($sibling_rate);

                        $discount = $last_sibling_rate * (10 / 100);
                        $total_amount = $total_amount - $discount;

                        $subtotal .= '<tr>
                                        <th style="text-align: right; border: 1px solid black;">Sibling Discount</th>
                                        <th style="text-align: center; border: 1px solid black;">-10%</th>
                                        <th style="text-align: center; border: 1px solid black;">'.wc_price(-abs($discount)).'</th>
                                    </tr>';
                    }
                    
                    if ($is_due_registration) {
                        $total_amount += self::$registration_fee;

                        $subtotal .= '<tr>
                                        <th style="text-align: right; border: 1px solid black;">Registration Fee</th>
                                        <th style="text-align: center; border: 1px solid black;">'.wc_price(self::$registration_fee).'</th>
                                        <th style="text-align: center; border: 1px solid black;">'.wc_price($total_amount).'</th>
                                    </tr>';
                    } 

                    if (!empty($subtotal)) {
                        $invoice_table .= $subtotal;
                    }

                    $invoice_table .= '
                    </tbody>
                    <tfoot>
                        <tr>
                            <th style="text-align: right; border: 1px solid black;" colspan="2">Total</th>
                            <th style="text-align: center; border: 1px solid black;">'.wc_price($total_amount).'</th>
                        </tr>
                    </tfoot>
                </table>';

                $discount = isset($discount) ? $discount : 0;
                $subscriptions = wcs_get_subscriptions(array('customer_id' => $user->ID));
                if (empty($subscriptions)) {
                    $active_subscription = EmailTemplates::create_order_subscription($user, $products_ids, $is_due_registration, $discount, $original_total);
                } else {
                    foreach($subscriptions as $subscription) {
                        $sub_product_ids = [];
                        $items = $subscription->get_items();
                        
                        foreach($items as $item) {
                            $data = $item->get_data();
                            $sub_product_ids[] = $data['product_id'];
                        }
                        
                        if ($sub_product_ids == $products_ids) {
                            $parent_id = $subscription->get_data()['id'];
                        }

                    }
                }

                if (isset($parent_id) && !empty($subscriptions)) {
                    $parent_subscription = wc_get_order($parent_id);
                    EmailTemplates::update_subscription_order($parent_subscription, $is_due_registration, $discount);
                    $active_subscription = EmailTemplates::create_order($user, array('products_ids' => $products_ids, 'is_due_registration' => $is_due_registration, 'parent_id' => $parent_id, 'discount' => $discount, 'total_amount' => $original_total));
                } else {
                    $active_subscription = EmailTemplates::create_order_subscription($user, $products_ids, false, $discount, $original_total);
                }

                if ($active_subscription !== 0) {
                    
                        $payment_date = date('F');
                        $message = str_replace(
                            array('{{program_list}}', '{{date}}', '{{amount}}', '{{invoice}}'),
                            array($product_list, $payment_date, $total_amount, $invoice_table),
                            $template_message
                        );

                        $message = self::self_merge_tags($message, $user);

                        wp_mail($user->user_email, $subject, $message, $headers);
                        
                        if (!isset($args['not_comment'])) {

                            $comment_invoice = array(
                                'comment_author' => $active_subscription,
                                'comment_content' => 'Invoice generated for $'.$total_amount,
                                'user_id' => $user->ID,
                                'comment_meta'         => array(
                                    'is_monthly'       => 1,
                                    'is_invoice'       => sanitize_text_field(1),
                                    'invoice_total'    => $total_amount,
                                    'invoice_table'    => $invoice_table,
                                    'message' => $message
                                    )
                                );
                
                            $comment_id = wp_insert_comment($comment_invoice);
                            $current = get_current_user_id();
                            $current_user = get_user_by('id', $current);
                            $comment_user = array(
                                'comment_author' => !empty($current_user->display_name) ? $current_user->display_name : 'System',
                                'comment_content' => 'Email "'.$subject.'" sent to '.$user->user_email.' Invoice #'.$comment_id.' generated for $'.$total_amount,
                                'user_id' => $user->ID,
                                'comment_meta'         => array(
                                    'is_customer_note'       => sanitize_text_field(1),
                                    'invoice_id'       => $comment_id,
                                    )
                                );
                
                            wp_insert_comment($comment_user);
                        }

                    }
                }
            }
        }
    }

    public static function create_order_subscription($user, $products_ids, $is_due_registration, $discount, $total_amount) {

        if( ! function_exists( 'wc_create_order' ) || ! function_exists( 'wcs_create_subscription' ) || ! class_exists( 'WC_Subscriptions_Product' ) ){
            return false;
        }

        $order = wc_create_order( array( 'customer_id' => $user->ID) );

        $fname     = $user->first_name;
        $lname     = $user->last_name;
        $email     = $user->user_email;
        $address_1 = get_user_meta( $user->ID, 'billing_address_1', true );
        $address_2 = get_user_meta( $user->ID, 'billing_address_2', true );
        $city      = get_user_meta( $user->ID, 'billing_city', true );
        $postcode  = get_user_meta( $user->ID, 'billing_postcode', true );
        $country   = get_user_meta( $user->ID, 'billing_country', true );
        $state     = get_user_meta( $user->ID, 'billing_state', true );
    
        $address         = array(
            'first_name' => $fname,
            'last_name'  => $lname,
            'email'      => $email,
            'address_1'  => $address_1,
            'address_2'  => $address_2,
            'city'       => $city,
            'state'      => $state,
            'postcode'   => $postcode,
            'country'    => 'United States',
        );

        $order->set_address( $address, 'billing' );
        $order->set_address( $address, 'shipping' );

        foreach($products_ids as $product_id) {
            $product = wc_get_product( $product_id );
            $order->add_product( $product, 1 );
        }
        
        $sub = wcs_create_subscription(array(
            'order_id' => $order->get_id(),
            'status' => 'pending',
            'billing_period' => WC_Subscriptions_Product::get_period( $product ),
            'billing_interval' => WC_Subscriptions_Product::get_interval( $product )
        ));
        

        if( is_wp_error( $sub ) ){
            return false;
        }

        $sub->set_address( $address, 'billing' );
        $sub->set_address( $address, 'shipping' );
    
        foreach($products_ids as $product_id) {
            $product = wc_get_product( $product_id );
            if ($product) {
                $sub->add_product( $product, 1 );
            }
        }

        if ($is_due_registration) {
            $order->add_item( self::create_registration_fee('Registration Fee', self::$registration_fee));
            $sub->add_item( self::create_registration_fee('Registration Fee', self::$registration_fee));
        }

        if ($discount > 0) {
            $order->add_item( self::create_registration_fee('Sibling Discount', -abs($discount)));
            $sub->add_item( self::create_registration_fee('Sibling Discount', -abs($discount)));
        }

        $total_product = $total_amount / count($products_ids);
        foreach( $order->get_items() as $item_id => $item ){
            $item->set_subtotal( $total_product ); 
            $item->set_total( $total_product );
            $item->calculate_taxes();
            $item->save();
        }
        foreach( $sub->get_items() as $item_id => $item ){
            $item->set_subtotal( $total_product ); 
            $item->set_total( $total_product );
            $item->calculate_taxes();
            $item->save();
        }
    
        $order->calculate_totals();
        $sub->calculate_totals();
        $order->save();
        $sub->save();

        $note = 'Subscription Renewal Invoice added by System.';
        $order->update_status( 'pending', $note, true );
        $sub->update_status( 'pending', $note, true );

        return $sub->get_id();
    }

    public static function create_order($user, $args) {
        global $wpdb;
        $order = wc_create_order( array( 'customer_id' => $user->ID) );
            
        $fname     = !empty(get_user_meta($user->ID, 'billing_first_name', true)) ? get_user_meta($user->ID, 'billing_first_name', true) : '';
        $lname     = !empty(get_user_meta($user->ID, 'billing_last_name', true)) ? get_user_meta($user->ID, 'billing_last_name', true) : '';
        $email     = $user->user_email;
        $address_1 = !empty(get_user_meta( $user->ID, 'billing_address_1', true )) ? get_user_meta( $user->ID, 'billing_address_1', true ) : '';
        $city      = !empty(get_user_meta( $user->ID, 'billing_city', true )) ? get_user_meta( $user->ID, 'billing_city', true ) : '';
        $postcode  = !empty(get_user_meta( $user->ID, 'billing_postcode', true )) ? get_user_meta( $user->ID, 'billing_postcode', true ) : '';
        $state     = !empty(get_user_meta( $user->ID, 'billing_state', true )) ? get_user_meta( $user->ID, 'billing_state', true ) : '';
    
        $address         = array(
            'first_name' => $fname,
            'last_name'  => $lname,
            'email'      => $email,
            'address_1'  => $address_1,
            'city'       => $city,
            'state'      => $state,
            'postcode'   => $postcode,
            'country'    => 'United States',
        );

        $order->set_address( $address, 'billing' );
        $order->set_address( $address, 'shipping' );

        if (isset($args['products_ids'])) {
            foreach($args['products_ids'] as $product_id) {
                $product = wc_get_product( $product_id );
                if ($product) {
                    $order->add_product( $product, 1 );
                }
            }

            if ($args['is_due_registration']) {
                $order->add_item( self::create_registration_fee('Registration Fee', self::$registration_fee));
            }

            if (isset($args['discount']) && $args['discount'] > 0) {
                $order->add_item( self::create_registration_fee('Sibling Discount', -abs($args['discount'])));
            }

            if (isset($args['total_amount'])) {
                $total_product = $args['total_amount'] / count($args['products_ids']);
                foreach( $order->get_items() as $item_id => $item ){
                    $item->set_subtotal( $total_product ); 
                    $item->set_total( $total_product );
                    $item->calculate_taxes();
                    $item->save();
                }
            }

            $sql = 'UPDATE wp_posts SET post_parent = '.$args['parent_id'].' WHERE ID = '.$order->get_id();
            $wpdb->query($sql);
        }


        $order->calculate_totals();
        return $order->get_id();
    }
    

    public static function update_subscription_order($sub, $is_due_registration, $discount) {

        if ($is_due_registration) {
            $isFee = false;
            foreach( $sub->get_items( 'fee' ) as $item_id => $item ) {
                if( 'Registration Fee' === $item['name'] ) {
                    $isFee = true;
                }
            }

            if (!$isFee) {
                $sub->add_item( self::create_registration_fee('Registration Fee', self::$registration_fee) );
            }

        } else {

            foreach( $sub->get_items( 'fee' ) as $item_id => $item ) {
                if( 'Registration Fee' === $item['name'] ) {
                    $sub->remove_item($item_id);
                }
            }
        }

        if ($discount > 0) {
            $isDiscount = false;
            foreach( $sub->get_items( 'fee' ) as $item_id => $item ) {
                if( 'Sibling Discount' === $item['name'] ) {
                    $isDiscount = true;
                }
            }

            if (!$isDiscount) {
                $sub->add_item( self::create_registration_fee('Sibling Discount', -abs($discount)) );
            }

        } else {

            foreach( $sub->get_items( 'fee' ) as $item_id => $item ) {
                if( 'Sibling Discount' === $item['name'] ) {
                    $sub->remove_item($item_id);
                }
            }
        }

        $sub->save();
    }

    public static function create_registration_fee($name, $fee) {
        $item_fee = new WC_Order_Item_Fee();
        $item_fee->set_name( $name );
        $item_fee->set_amount( $fee );
        $item_fee->set_total( $fee );

        return $item_fee;
    }


    public static function ten_years($users) {
        if (!isset($users)) {
            $users = get_users(array('meta_key' => 'child_birth', 'meta_value' => '', 'meta_compare' => '!='));
        }

        foreach ($users as $user) {
            $child_birth = get_user_meta($user->id, 'child_birth', true);

            $month = date('Y-m-d', strtotime('+10 years -1 month', strtotime($child_birth)));
            
            if ($month === date('Y-m-d')) {
                $parent_id = get_user_meta($user->id, 'smuac_account_parent', true);
                $parent = get_user_by('id', $parent_id);
                $users_list[] = $parent;
            }
        }

        return $users_list;
    }

    public static function no_card_saved($users) {
        $stripe = new \Stripe\StripeClient(STRIPE_TEST_KEY);

        $users_list = [];

        foreach($users as $user) {
            $stripe_cus_id = get_user_meta($user->ID, 'wp__stripe_customer_id', true);
            if (!empty($stripe_cus_id)) {
                try {

                    $pm = $stripe->customers->allPaymentMethods(
                        $stripe_cus_id,
                        ['type' => 'card']
                    );

                    if (count($pm->data) == 0) {
                        $users_list[] = $user;
                    }
                } catch (Exception $e) {
                }
            } else {
                $users_list[] = $user;
            }
        }

        return $users_list;
    }

    function list_unpaid($message) {

        $list = '<ul>';

        $users = get_clients_with_outstanding_payments();

            foreach($users as $user) {
                if ($user->balance < 0) {
                    $list .= '<li><a href="gymnasticsofyork.com/wp-admin/admin.php?page=user-information-edit&user='.$user->ID.'&child=no" target="_blank">'.$user->first_name.' '.$user->last_name.'</a></li>';
                }
            }

        $list = '</ul>';

        $message = str_replace(
            ['{{list}}'],
            [$list],
            $message
        );


        return $message;
    }

    public static function no_payment_recorded($users, $args = null) {

        $all_users = get_clients_with_outstanding_payments();

        foreach($all_users as $user) {
            if (isset($args['single']) && $user->ID == $users[0]->ID) {
                if ($user->balance < 0) {
                    $users_list[] = $users[0];
                }
            } else {
                if ($user->balance < 0) {
                    $users_list[] = $user;
                }
            }
        }

        return $users_list;
    }

    public static function notice_suspension($users, $args = null) {
        $all_users = get_clients_with_outstanding_payments();

        foreach($all_users as $user) {
            if (isset($args['single']) && $user->ID == $users[0]->ID) {
                if ($user->balance < 0) {
                    $users_list[] = $users[0];
                }
            } else {
                if ($user->balance < 0) {
                    $users_list[] = $user;
                }
            }
        }

        return $users_list;
    }

    public static function suspension_enrollment($usersusers, $args = null) {

        $all_users = get_clients_with_outstanding_payments();

        foreach($all_users as $user) {
            if (isset($args['single']) && $user->ID == $users[0]->ID) {
                if ($user->balance < 0) {
                    $users_list[] = $users[0];
                }
            } else {
                if ($user->balance < 0) {
                    $users_list[] = $user;
                }
            }
        }

        return $users_list;
    }

    public static function weekly_income($message) {
        global $wpdb;
        
        $start_date = date( 'Y-m-d', strtotime( '-1 week' ) );
        $end_date = date( 'Y-m-d' );
    
        $query = "
            SELECT SUM(meta.meta_value) AS total_sales
            FROM {$wpdb->prefix}posts AS posts
            INNER JOIN {$wpdb->prefix}postmeta AS meta ON posts.ID = meta.post_id
            WHERE posts.post_type = 'shop_order'
            AND posts.post_status IN ( 'wc-processing', 'wc-completed' )
            AND meta.meta_key = '_order_total'
            AND posts.post_date >= '$start_date' AND posts.post_date <= '$end_date'
        ";
    
        $result = $wpdb->get_var( $query );

        $message = str_replace(
            '{{first_date}}',
            $start_date,
            $message
        );
        $message = str_replace(
            '{{second_date}}',
            $end_date,
            $message
        );
        $message = str_replace(
            '{{income}}',
            wc_price(floatval($result)),
            $message
        );

        return $message;
    }

    public static function applied_late_fees() {
        global $wpdb;

        $users = get_users();

        if (get_option('automatic_applied_late_fees') == 1) {

            $type = get_option('applied_late_fees_type');
            $amount = get_option('applied_late_fees');
            $days_late = get_option('days_before_late_fees');

            foreach ($users as $user) {

                $orders = wc_get_orders(array(
                    'customer_id' => $user->ID,
                    'status'      => array('pending', 'wc_pending')
                ));

                $subscriptions = wcs_get_subscriptions(array('customer_id' => $user->ID, 'status' => array('pending', 'wc_pending')));
                $sql = 'SELECT comment_ID FROM wp_comments WHERE comment_author = %s';
    
                foreach($orders as $order) {
                    $is_applied = false;
                    $where = [$order->get_id()];

                    $results = $wpdb->get_results($wpdb->prepare($sql, $where));
    
                    if (!empty($results)) {
                        $current_time = new DateTime();
                        $invoice_creation = $order->get_date_created();

                        foreach( $order->get_items( 'fee' ) as $item ) {
                            if( 'Applied Late Fees' == $item['name'] ) {
                                $is_applied = true;
                            }
                        }

                        if (!$is_applied) {
                            if ($current_time->diff($invoice_creation)->days >= $days_late) {
            
                                if ($type == 'percentage') {
                                    $fee = floatval($order->get_total() * ($amount / 100));
                                } else {
                                    $fee = floatval($amount);
                                }
        
                                $order->add_item(self::create_registration_fee('Applied Late Fees', $fee));
                                $order->calculate_totals();
                                $order->save();
                            }
                        }
                    }
                
                }
    
                foreach($subscriptions as $sub) {
                    $is_applied = false;
                    $where = [$sub->get_id()];
                    $results = $wpdb->get_results($wpdb->prepare($sql, $where));
    
                    if (!empty($results)) {
                        $current_time = new DateTime();
                        $invoice_creation = $sub->get_date_created();

                        foreach( $sub->get_items( 'fee' ) as $item ) {
                            if( 'Applied Late Fees' == $item['name'] ) {
                                $is_applied = true;
                            }
                        }

                        if (!$is_applied) {
                            if ($current_time->diff($invoice_creation)->days >= $days_late) {
            
                                if ($type == 'percentage') {
                                    $fee = floatval($sub->get_total() * ($amount / 100));
                                } else {
                                    $fee = floatval($amount);
                                }
        
                                $sub->add_item(self::create_registration_fee('Applied Late Fees', $fee));
                                $sub->calculate_totals();
                                $sub->save();
                            }
                        }
                    }
                }
    
            }
        }
    }

    function gycrm_scheduled_email($subject, $template_message, $headers, $email_type, $email_schedule, $schedule_status, $comma_email = null)
    {

        switch($email_type) {
            case 'all_customers':
                $customers = get_users(); 
                $users = [];

                foreach($customers as $user) {
                    $is_active = false;
                    $children = get_user_meta($user->ID, 'smuac_multiaccounts_list',true);

                    if (!empty($children)) {

                        $children = explode(',', $children);
                        
                        foreach ($children as $child) {
                            $status = get_user_meta($child, 'status_program_participant', true);

                            if ($status == 'active') {
                                $is_active = true;
                            }

                            if ($is_active) {
                                $users[] = $user;
                            }
                        }
                    }
                }
            break;
            case 'comma':
                if (isset($comma_email)) {
                    $email_addresses = sanitize_text_field($comma_email);
                    $list_email = explode(',', $email_addresses);
                    
                    foreach ($list_email as $key => $email) {
                        $users[] = new stdClass();
                        $user = get_user_by('email', $email);
                        if (!empty($user)) {
                            $users[$key]->ID = $user->ID;
                            $users[$key]->user_email = $user->user_email;
                        } else {
                            $users[$key]->ID = '';
                            $users[$key]->user_email = $email;
                        }
                    }
                }
            break;
            case 'all_admin':
                $users = get_users(array(
                    'role' => 'administrator',
                ));
            break;
            case 'accounts-owing': 
                $stripe = new \Stripe\StripeClient(STRIPE_TEST_KEY);

                $accounts = get_clients_with_outstanding_payments();

                foreach($accounts as $user) {
                    if ($user->balance < 0) {
                        $users[] = $user;
                    }
                }
            break;
            case 'no-credit':
                $stripe = new \Stripe\StripeClient(STRIPE_TEST_KEY);
                $accounts = get_clients_with_outstanding_payments();

                foreach($accounts as $user) {
                    if ($user->balance < 0) {
                        $stripe_cus_id = get_user_meta($user->ID, 'wp__stripe_customer_id', true);
                        if (!empty($stripe_cus_id)) {
                            try {

                                $pm = $stripe->customers->allPaymentMethods(
                                    $stripe_cus_id,
                                    ['type' => 'card']
                                );

                                if (count($pm->data) == 0) {
                                    $users[] = $user;
                                }
                            } catch (Exception $e) {
                            }
                        } else {
                            $users[] = $user;
                        }
                    }
                }
            break;
        }
        
        $today = date('Y-m-d');
        $current_day = date('d', strtotime($today));

        if ($email_schedule == $current_day && $schedule_status == 'on') {

            switch($subject) {
                case 'No Card Saved on File':
                    $users = self::no_card_saved($users);
                break;
                case 'List Of Unpaid Users':
                    $template_message = self::list_unpaid($template_message);
                break;
                case 'No Payment Recorded':
                    $users = self::no_payment_recorded($users);
                break;
                case 'Notice of Suspension':
                    $users = self::notice_suspension($users);
                break;
                case 'Suspension of Enrollment':
                    $users = self::suspension_enrollment($users);
                break;
                case 'Subscription Renewal Invoice':
                    $batchSize = 100;
                    for ($i = 0; $i < count($users); $i += $batchSize) {
                        $batchRecipients = array_slice($users, $i, $batchSize);
                        foreach ($batchRecipients as $recipient) {
                            self::get_subscription_invoice($recipient->user_email, $template_message, $subject, $headers);
                        }
                        sleep(20);
                    }
                break;
                case 'One Month to go until Athletes\'s 10th Birthday':
                    $users = self::ten_years($users);
                break;
                case 'Income Report':
                    $template_message = self::weekly_income($template_message);
                break;
            }
            
            if ($subject !== 'Subscription Renewal Invoice') {
                self::send_automated_email($subject, $template_message, $headers, $users);
            }
        }
    }

    public static function send_automated_email($subject, $template_message, $headers, $users) { 

        $batchSize = 100;

        for ($i = 0; $i < count($users); $i += $batchSize) {
    
            $batchRecipients = array_slice($users, $i, $batchSize);

            foreach ($batchRecipients as $recipient) {
                $message = self::self_merge_tags($template_message, $recipient);
        
                $is_sent = wp_mail($recipient->user_email, $subject, $message, $headers);
                if ($is_sent) {
                    $comment_user = array(
                        'comment_author' => 'System',
                        'comment_content' => 'Email "'.$subject.'" sent to '.$recipient->user_email.'. ',
                        'user_id' => $recipient->ID,
                        'comment_meta'         => array(
                            'is_customer_note'       => sanitize_text_field(1),
                            )
                        );
        
                    wp_insert_comment($comment_user);
                }
            }
            sleep(20);
        }
    }

    public static function self_merge_tags($message, $user) {
        $children = explode(',', get_user_meta($user->ID, 'smuac_multiaccounts_list', true));

        $message = str_replace(
            ['{{user_name}}'],
            [$user->user_login],
            $message
        );

        $message = str_replace(
            ['{{full_name}}'],
            [$user->first_name.' '.$user->last_name],
            $message
        );

        $message = str_replace(
            ['{{first_name}}'],
            [$user->first_name],
            $message
        );

        $message = str_replace(
            ['{{last_name}}'],
            [$user->last_name],
            $message
        );

        if (strpos($message, '{{athletes_first_name}}')) {

            if (!empty($children)) {
                foreach ($children as $child) {
                    if (!empty($child)) {
                        $first_names[] = get_user_meta($child, 'first_name', true);
                    }
                }
            }

            $message = str_replace(
                ['{{athletes_first_name}}'],
                [implode(', ', $first_names)],
                $message
            );
        }

        if (strpos($message, '{{athletes_last_name}}')) {

            if (!empty($children)) {
                foreach ($children as $child) {
                    if (!empty($child)) {
                        $last_names[] = get_user_meta($child, 'last_name', true);
                    }
                }
            }

            $message = str_replace(
                ['{{athletes_last_name}}'],
                [implode(', ', $last_names)],
                $message
            );
        }

        if (strpos($message, '{{athletes_full_name}}')) {

            if (!empty($children)) {
                foreach ($children as $child) {
                    if (!empty($child)) {
                        $full_names[] = get_user_meta($child, 'first_name', true) .' '.get_user_meta($child, 'last_name', true);
                    }
                }
            }

            $message = str_replace(
                ['{{athletes_full_name}}'],
                [implode(', ', $full_names)],
                $message
            );
        }

        return $message;
    }
    
    public function merge_tags($template_message, $user) {
        $children = explode(',', get_user_meta($user->ID, 'smuac_multiaccounts_list', true));

        $template_message = str_replace(
            ['{{full_name}}'],
            [$user->first_name.' '.$user->last_name],
            $template_message
        );

        $template_message = str_replace(
            ['{{user_name}}'],
            [$user->user_login],
            $template_message
        );

        $template_message = str_replace(
            ['{{first_name}}'],
            [$user->first_name],
            $template_message
        );

        $template_message = str_replace(
            ['{{last_name}}'],
            [$user->last_name],
            $template_message
        );

        if (strpos($template_message, '{{athletes_first_name}}')) {

            if (!empty($children)) {
                foreach ($children as $child) {
                    if (!empty($child)) {
                        $first_names[] = get_user_meta($child, 'first_name', true);
                    }
                }
            }

            $template_message = str_replace(
                ['{{athletes_first_name}}'],
                [implode(', ', $first_names)],
                $template_message
            );
        }

        if (strpos($template_message, '{{athletes_last_name}}')) {

            if (!empty($children)) {
                foreach ($children as $child) {
                    if (!empty($child)) {
                        $last_names[] = get_user_meta($child, 'last_name', true);
                    }
                }
            }

            $template_message = str_replace(
                ['{{athletes_last_name}}'],
                [implode(', ', $last_names)],
                $template_message
            );
        }

        if (strpos($template_message, '{{athletes_full_name}}')) {

            if (!empty($children)) {
                foreach ($children as $child) {
                    if (!empty($child)) {
                        $full_names[] = get_user_meta($child, 'first_name', true) .' '.get_user_meta($child, 'last_name', true);
                    }
                }
            }

            $template_message = str_replace(
                ['{{athletes_full_name}}'],
                [implode(', ', $full_names)],
                $template_message
            );
        }
        

        return $template_message;
    }

    public function get_enrolled_users_only() {
        $users = get_users(); 

        $enrolled_users = [];

        foreach($users as $user) {
            if ($user->roles[0] == 'customer') {

                $children = get_user_meta($user->ID, 'smuac_multiaccounts_list',true);

                if (!empty($children)) {

                    $children = explode(',', $children);
                    
                    foreach ($children as $child) {
                        $classes = get_user_meta($child, 'classes', true);

                        if (!empty($classes)) {
                            $enrolled_users[] = $user;
                            break;
                        }
                    }
                }
            }
        }

        return $enrolled_users;

    }
}
