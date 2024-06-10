<?php

require GY_CRM_PLUGIN_DIR . 'models/admin/spreadsheet_reader/SpreadsheetReader.php';
class ImportUsers {

    public function __construct()
    {
        add_action('init', array($this, 'validate_role'), 10);
    }

    public function validate_role() {
        $user = wp_get_current_user();

        if (!in_array('staff', $user->roles)) {
            add_shortcode( 'gy_import_users', array($this, 'gy_import_users'));
            add_action( 'admin_menu', array($this, 'gy_attendance_list_page'), 11 );
        }
    }

    public function gy_import_users() {

        echo '<div class="flex-container" id="import_users_container">';
        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/import_users/import_form.php';

        if (isset($_POST['submit_import'])) {

            check_admin_referer('import_nonce');
            $file = $_FILES['import_users'];
            
            if ($file['size'] > 0 && in_array($file['type'], ['csv', 'text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.oasis.opendocument.spreadsheet'])) {
                
                $path = GY_CRM_PLUGIN_DIR . 'tmp/' . $file['name'];
                move_uploaded_file($file['tmp_name'], $path);
                $reader = new SpreadsheetReader($path);
                
                foreach($reader as $key => $row) {
                    if ($key !== 0) {
                        $this->save_account($row);
                    }
                }

                
                echo '<div class="notice notice-success is-dismissible"><p>Success: Users imported.</p></div>';
                unlink( $path );
                die();

            } else {
                echo '<div class="notice notice-warning is-dismissible"><p>Error: Only <span>ODS, XLSX, CSV</span> accepted</p></div>';
            }
        }
        
        echo '</div>';
    }

    public function save_account($row) {

        global $wpdb;

        $email = $row[52];
        $alternate_email_1 = $row[53];
        $alternate_email_2 = $row[55];
        $notes = $row[5];
        $mobile_sms = $row[77];
        $billing_phone = $row[29];
        $first_name = $row[39];
        $last_name = $row[40];
        $billing_address = $row[43];
        $billing_city = $row[45];
        $billing_phone_2 = $row[84];
        $billing_state = $row[87];
        $billing_postcode = $row[89];


        if (!empty($row[6])) {
            $parsed_date = DateTime::createFromFormat("m/d/Y", $row[6]);
            if ($parsed_date) {
                $child_birth = $parsed_date->format("Y-m-d");
            }
        }
        
        
        $class_title = $row[0];

        $gender = $row[11];
        $med_name = $row[16];
        $med_phone = $row[17];
        $med_notes = $row[18];
        $medication = $row[19];
        // $child_email = $row[16];
        $child_first_name = $row[22];
        $child_last_name = $row[23];
        $child_middle_name = $row[24];
        $suffix = $row[25];
        $preferred_name = $row[30];
        $emergency_name_1 = $row[60];
        $emergency_phone_1 = $row[61];
        $emergency_name_2 = $row[85];
        $emergency_phone_2 = $row[86];
        $team_level = $row[34];

        if (!empty($row[9])) {
            $parsed_date = DateTime::createFromFormat("m/d/Y", $row[9]);
            if ($parsed_date) {
                $date_inactive = $parsed_date->format("Y-m-d");
            }
        }

        $member_status = strtolower($row[27]);

        $guardian_first_name_1 = $row[63];
        $guardian_last_name_1 = $row[65];
        $guardian_home_phone_1 = $row[64];
        $guardian_mobile_phone_1 = $row[66];
        $guardian_work_phone_1 = $row[67];
        $guardian_first_name_2 = $row[68];
        $guardian_last_name_2 = $row[70];
        $guardian_home_phone_2 = $row[69];
        $guardian_mobile_phone_2 = $row[71];
        $guardian_work_phone_2 = $row[72];


        if (!empty($email)) {
            $is_user = get_user_by('email', $email);

            if (empty($is_user)) {

                $username = $first_name.$last_name;
                $password = $username;

                $user_id = wp_create_user( $username, $password, $email );

                $user = new WP_User( $user_id );
                $user->remove_role( 'subscriber' );
                $user->add_role( 'customer' );

            } else {
                $user_id = $is_user->ID;
                $username = $is_user->user_login;
            }


            update_user_meta( $user_id, 'first_name', $first_name );
            update_user_meta( $user_id, 'last_name', $last_name );

            !empty($alternate_email_1) && $alternate_email_1 !== 'None' && $alternate_email_1 !== 'No' ? update_user_meta( $user_id, 'alternate_email_1', $alternate_email_1 ) : null;
            !empty($alternate_email_2) && $alternate_email_2 !== 'None' && $alternate_email_2 !== 'No' ? update_user_meta( $user_id, 'alternate_email_2', $alternate_email_2 ) : null;
            !empty($mobile_sms) ? update_user_meta( $user_id, 'mobile_sms', $mobile_sms ) : null;
            !empty($first_name) ? update_user_meta( $user_id, 'billing_first_name', $first_name ) : null;
            !empty($last_name) ? update_user_meta( $user_id, 'billing_last_name', $last_name ) : null;
            !empty($billing_address) ? update_user_meta( $user_id, 'billing_address', $billing_address ) : null;
            !empty($billing_city) ? update_user_meta( $user_id, 'billing_city', $billing_city ) : null;
            !empty($billing_state) ? update_user_meta( $user_id, 'billing_state', $billing_state ) : null;
            !empty($billing_postcode) ? update_user_meta( $user_id, 'billing_postcode', $billing_postcode ) : null;
            !empty($billing_phone) ? update_user_meta( $user_id, 'billing_phone', $billing_phone ) : null;
            !empty($billing_phone_2) ? update_user_meta( $user_id, 'billing_phone_2', $billing_phone_2 ) : null;

            
            if (!empty($notes)) {

                $sql = 'SELECT comment_ID
                        FROM '.$wpdb->comments.'
                        WHERE comment_author = %s
                            AND comment_content = %s
                            AND user_id = %s';

                        $where = [$username, $notes, $user_id];

                        $is_comment =  $wpdb->get_results(
                            $wpdb->prepare( $sql, $where)
                        );

                        if (empty($is_comment)) {
                            $comment_data = array(
                                'comment_author' => $username,
                                'comment_content' => $notes,
                                'user_id' => $user_id,
                                'comment_meta'         => array(
                                    'is_customer_note'       => sanitize_text_field(1),
                                )
                                );

                                wp_insert_comment($comment_data);
                        }
            }


            if (get_userdata( $user_id )) {
                $parent_user_id              = $user_id;
                $multiaccounts_maximum_limit = 500;
                // Test multiaccounts number
                $current_multiaccounts_number = get_user_meta( $parent_user_id, 'smuac_multiaccounts_number', true );
                if ( null === $current_multiaccounts_number ) {
                    $current_multiaccounts_number = 0;	
                }
                if ( intval( $current_multiaccounts_number ) < $multiaccounts_maximum_limit && !empty($child_first_name) ) {
    
                    $date = date('mdYHis');
                    $email_domain_extension = '@gymnasticsofyork.com';
                    $parent_name = $first_name ;
                    $child_email = $parent_name.'_'.$child_first_name.'_'.$date.$email_domain_extension;
    
                    $childusername = $child_first_name.$child_last_name;

                    if (empty($is_user)) {
                        $child_user_id = wc_create_new_customer( $child_email, $childusername, $childusername );
                    } else {

                        $meta_key = 'smuac_account_parent';

                        $sql = 'SELECT user_id
                            FROM '.$wpdb->usermeta.'
                            WHERE meta_key = %s
                            AND meta_value = %s';
                
                        $where = [$meta_key, $user_id];
                
                        $subaccounts = $wpdb->get_results(
                            $wpdb->prepare( $sql, $where)
                        );

                        foreach($subaccounts as $subaccount) {
                            $get_child_name = get_user_meta($subaccount->user_id, 'first_name', true);

                            if ($get_child_name !== $child_first_name) {
                                $child_user_id = wc_create_new_customer( $child_email, $childusername, $childusername );
                            }
                        }
                    }

                    if ( isset($child_user_id) && ! ( is_wp_error( $child_user_id ) ) ) {
                        // no errors, proceed
                        // set user meta
                        update_user_meta( $child_user_id, 'first_name', $child_first_name );
                        update_user_meta( $child_user_id, 'last_name', $child_last_name );
                        if (isset($child_birth)) {
                            update_user_meta( $child_user_id, 'child_birth', $child_birth );
                        }
                        update_user_meta( $child_user_id, 'smuac_account_type', 'multiaccount' );
                        update_user_meta( $child_user_id, 'smuac_account_type', 'multiaccount' );
                        update_user_meta( $child_user_id, 'smuac_account_parent', $parent_user_id );
                        update_user_meta( $child_user_id, 'smuac_account_name', $childusername );
                        update_user_meta( $child_user_id, 'smuac_account_phone', '' );
                        update_user_meta( $child_user_id, 'smuac_account_job_title', '' );
                        update_user_meta( $child_user_id, 'smuac_account_permission_buy', '' ); // true or false
                        update_user_meta( $child_user_id, 'smuac_account_permission_view_orders', '' ); // true or false
                        update_user_meta( $child_user_id, 'smuac_account_permission_view_bundles', '' ); // true or false
                        update_user_meta( $child_user_id, 'smuac_account_permission_view_discussions', ''); // true or false
                        update_user_meta( $child_user_id, 'smuac_account_permission_view_lists', '' ); // true or false
                        update_user_meta( $child_user_id, 'status_program_participant', $member_status );
                        update_user_meta( $child_user_id, 'team_level', $team_level );
                        
                        !empty($gender) ? update_user_meta( $child_user_id, 'gender', $gender ) : null;
                        !empty($child_middle_name) ? update_user_meta( $child_user_id, 'child_middle_name', $child_middle_name ) : null;
                        !empty($med_name) ? update_user_meta( $child_user_id, 'medic_name', $med_name ) : null;
                        !empty($med_phone) ? update_user_meta( $child_user_id, 'medic_phone', $med_phone ) : null;
                        !empty($med_notes) ? update_user_meta( $child_user_id, 'medic_notes', $med_notes ) : null;
                        !empty($medication) ? update_user_meta( $child_user_id, 'medication', $medication ) : null;
                        !empty($suffix) ? update_user_meta( $child_user_id, 'suffix', $suffix ) : null;
                        !empty($preferred_name) ? update_user_meta( $child_user_id, 'preferred_name', $preferred_name ) : null;
                        !empty($emergency_name_1) && $emergency_name_1 !== 'None' && $emergency_name_1 !== 'No' ? update_user_meta( $child_user_id, 'emergency_name_1', $emergency_name_1 ) : null;
                        !empty($emergency_phone_1) && $emergency_phone_1 !== 'None' && $emergency_phone_1 !== 'No'? update_user_meta( $child_user_id, 'emergency_phone_1', $emergency_phone_1) : null;
                        !empty($emergency_name_2) && $emergency_name_2 !== 'None' && $emergency_name_2 !== 'No' ? update_user_meta( $child_user_id, 'emergency_name_2', $emergency_name_2 ) : null;
                        !empty($emergency_phone_2) && $emergency_name_2 !== 'None' && $emergency_name_2 !== 'No' ? update_user_meta( $child_user_id, 'emergency_phone_2', $emergency_phone_2 ) : null;
                        
                        !empty($guardian_first_name_1) && $guardian_first_name_1 !== 'None' && $guardian_first_name_1 !== 'No' ? update_user_meta( $child_user_id, 'guardian_first_name_1', $guardian_first_name_1 ) : null;
                        !empty($guardian_last_name_1) && $guardian_last_name_1 !== 'None' && $guardian_last_name_1 !== 'No' ? update_user_meta( $child_user_id, 'guardian_last_name_1', $guardian_last_name_1 ) : null;
                        !empty($guardian_home_phone_1) && $guardian_home_phone_1 !== 'None' && $guardian_home_phone_1 !== 'No' ? update_user_meta( $child_user_id, 'guardian_home_phone_1', $guardian_home_phone_1 ) : null;
                        !empty($guardian_mobile_phone_1) && $guardian_mobile_phone_1 !== 'None' && $guardian_mobile_phone_1 !== 'No' ? update_user_meta( $child_user_id, 'guardian_mobile_phone_1', $guardian_mobile_phone_1 ) : null;
                        !empty($guardian_work_phone_1) && $guardian_work_phone_1 !== 'None' && $guardian_work_phone_1 !== 'No' ? update_user_meta( $child_user_id, 'guardian_work_phone_1', $guardian_work_phone_1 ) : null;
                        !empty($guardian_first_name_2) && $guardian_first_name_2 !== 'None' && $guardian_first_name_2 !== 'No' ? update_user_meta( $child_user_id, 'guardian_first_name_2', $guardian_first_name_2 ) : null;
                        !empty($guardian_last_name_2) && $guardian_last_name_2 !== 'None' && $guardian_last_name_2 !== 'No' ? update_user_meta( $child_user_id, 'guardian_last_name_2', $guardian_last_name_2 ) : null;
                        !empty($guardian_home_phone_2) && $guardian_home_phone_2 !== 'None' && $guardian_home_phone_2 !== 'No' ? update_user_meta( $child_user_id, 'guardian_home_phone_2', $guardian_home_phone_2 ) : null;
                        !empty($guardian_mobile_phone_2) && $guardian_mobile_phone_2 !== 'None' && $guardian_mobile_phone_2 !== 'No' ? update_user_meta( $child_user_id, 'guardian_mobile_phone_2', $guardian_mobile_phone_2 ) : null;
                        !empty($guardian_work_phone_2) && $guardian_work_phone_2 !== 'None' && $guardian_work_phone_2 !== 'No' ? update_user_meta( $child_user_id, 'guardian_work_phone_2', $guardian_work_phone_2 ) : null;
    
                        if (!empty($class_title)) {

                            $class_slot = str_replace('#', '', $row[1]);
                            $slot_day = $row[2];
                            $slot_time = preg_replace("/\s+/", "", preg_replace('/AM|PM/', '', $row[3]));

                            $class = get_posts(array(
                                'post_type' => 'class',
                                'title' => $class_title
                            ));

                            if (!empty($class)) {
                                $class_slot_ids = get_post_meta($class[0]->ID, 'slot_ids', true);

                                $post_id = $class[0]->ID;

                                if (!empty($class_slot_ids)) {

                                    foreach($class_slot_ids as $slot_exist) {
                                        $sql = 'SELECT meta_value FROM wp_postmeta WHERE meta_key = %s AND meta_value = %d';
                                        $where = [$slot_exist.'_slot_id', $class_slot];

                                        $results = $wpdb->get_results($wpdb->prepare($sql, $where));

                                        if (empty($results)) {
                                            if(!$class_slot_ids[$class_slot - 1]) {
                                                $slot_id = $this->create_slot($class_slot, $slot_day, $slot_time, $class[0]->ID, true);
                                            } else {
                                                $slot_id = $class_slot_ids[$class_slot - 1];
                                            }
                                        } else {
                                            $slot_id = $slot_exist;
                                        }
                                    }

                                } else {
                                    $slot_id = $this->create_slot($class_slot, $slot_day, $slot_time, $post_id);
                                }

                        } else {
                            $post_data = array(
                                'post_type' => 'class',
                                'post_title' => $class_title,
                                'post_status' => 'publish',
                                );
                                
                            $post_id = wp_insert_post( $post_data );
                            $slot_id = $this->create_slot($class_slot, $slot_day, $slot_time, $post_id);
                        }

                        $slot_ids[$post_id][] = $slot_id;

                        update_user_meta( $child_user_id, 'classes', array( $post_id ) );
                        update_user_meta( $child_user_id, 'classes_slots', $slot_ids );
                        update_user_meta( $child_user_id, 'slots', array($slot_id));
                    }

                    if (!empty($date_inactive)) {
                        $sql = 'SELECT comment_ID
                        FROM '.$wpdb->comments.'
                        WHERE comment_author = %s
                            AND comment_content = %s
                            AND user_id = %s';

                        $where = [$childusername, $date_inactive, $child_user_id];

                        $is_comment =  $wpdb->get_results(
                            $wpdb->prepare( $sql, $where)
                        );

                        if (empty($is_comment)) {
                            $comment_data = array(
                                'comment_author' => $childusername,
                                'comment_content' => $date_inactive,
                                'user_id' => $user_id,
                                'comment_meta'         => array(
                                    'is_customer_note'       => sanitize_text_field(1),
                                )
                                );

                                wp_insert_comment($comment_data);
                        }
                    }
                    $current_multiaccounts_number++;
                    update_user_meta( $parent_user_id, 'smuac_multiaccounts_number', $current_multiaccounts_number );

                    $current_multiaccounts_list = get_user_meta( $parent_user_id, 'smuac_multiaccounts_list', true );
                    $current_multiaccounts_list = $current_multiaccounts_list . ',' . $child_user_id;
                    update_user_meta( $parent_user_id, 'smuac_multiaccounts_list', $current_multiaccounts_list );

                    $userobj = new WP_User( $child_user_id );
                    $userobj->set_role( 'customer' );
                }
            }

        }
    }
    }

    public function create_slot($class_slot, $slot_day, $slot_time, $class_id, $slots_exists = false) {

        $date = date('mdYHis');

        $id = $this->get_random_string();
        $slot_id = $id.$date;

        if (!empty($slot_day) && !empty($slot_time)) {
            switch(strtolower($slot_day)) {
                case 'monday': 
                    update_post_meta($class_id, $slot_id.'_slot_time_monday', $slot_time);
                break;
                case 'tuesday': 
                    update_post_meta($class_id, $slot_id.'_slot_time_tuesday', $slot_time);
                break;
                case 'wednesday': 
                    update_post_meta($class_id, $slot_id.'_slot_time_wednesday', $slot_time);
                break;
                case 'thursday': 
                    update_post_meta($class_id, $slot_id.'_slot_time_thursday', $slot_time);
                break;
                case 'friday': 
                    update_post_meta($class_id, $slot_id.'_slot_time_friday', $slot_time);
                break;
                case 'saturday': 
                    update_post_meta($class_id, $slot_id.'_slot_time_saturday', $slot_time);
                break;
                case 'sunday': 
                    update_post_meta($class_id, $slot_id.'_slot_time_sunday', $slot_time);
                break;
            }
        }


        if ($slots_exists) {
            $slot_ids = get_post_meta($class_id, 'slot_ids', true);
            array_push($slot_ids, $slot_id);
        } else {
            $slot_ids = array($slot_id);
        }

        update_post_meta($class_id, 'slot_ids', $slot_ids);
        update_post_meta($class_id, $slot_id.'_slot_id', $class_slot);

        return $slot_id;
    }

    public function get_random_string() {
        $letters = 'abcdefghijklmnopqrstuvwxyz';
        $id = '';
        for ($i = 0; $i < 3; $i++) {
            $id .= $letters[rand(0, strlen($letters) - 1)];
        }

        return $id;
    }
    
    public function gy_attendance_list_page() {

        add_menu_page(
        'Import Users', // Page Title
        'Import Users', // Menu Title
        'manage_options', // Capability
        'import-users', // Menu Slug
        array($this, 'gy_import_users_callback'), // Callback function
        'dashicons-admin-users', // Icon
        8 // Position
        );
    }
    
    public function gy_import_users_callback() {
        echo do_shortcode('[gy_import_users]');
    }
    
}