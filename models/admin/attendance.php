<?php

require_once GY_CRM_PLUGIN_DIR . 'models/admin/plugin-list-table.php';

class Attendance {
    public $slot_week;

    public function __construct($slot_week)
    {
        $this->slot_week = $slot_week;

        add_shortcode( 'attendance-list', array($this, 'gy_attendance_list_shortcode') );

        add_action( 'wp_ajax_update_attendance', array($this, 'gy_update_attendance') );
        add_action( 'wp_ajax_nopriv_update_attendance', array($this, 'gy_update_attendance') );

        add_action( 'admin_menu', array($this, 'gy_attendance_list_page') );
    }

    function gy_attendance_list_shortcode() {

        global $wpdb;

        $slot_ids = get_post_meta($_GET['class'], 'slot_ids', true);
        
        if (isset($_GET['sd']) && !empty($slot_ids)) {
            $slot = $_GET['sd'];

            $sql = 'SELECT meta_key FROM wp_postmeta WHERE meta_id = %s';
            $where = [$slot];

            $results = $wpdb->get_results($wpdb->prepare($sql, $where));

            foreach($slot_ids as $slot) {
                $pattern = "/{$slot}/";
                $is_match = preg_match($pattern, $results[0]->meta_key);

                if ($is_match) {
                    $day = $results[0]->meta_key;
                    $current_slot = $slot;
                }
            }

        }

        if (isset($_GET['class']) && isset($_GET['date'])) {
            
            $is_not_date = false;
            $class = $_GET['class'];
            $date = $_GET['date'];
            
            $day_week = strtolower(date('l', strtotime($date)));
            if(strpos(strtolower($day), $day_week) == false) {
                $is_not_date = true;
            }

            $sql_in_class = $this->query_builder('');
            $sql_not_in_class = $this->query_builder('NOT');
            
            $where = ["%$current_slot%", $class, $date];
            
            if (isset($_GET['orderby'])) {
                
                echo '<style>
                .order-filter:hover::after {
                    content: " ↑" !important;
                }
                </style>';
                
                $sql_in_class = $this->query_builder('', true);
                $sql_not_in_class = $this->query_builder('NOT', true);

                $where = ["%$current_slot%", $class, $date];
                
            }

            if (!$is_not_date) {
                $in_class = $this->get_active_subscriptions($wpdb->get_results(
                    $wpdb->prepare( $sql_in_class, $where)
                ));
                $complementary_classes = $this->get_complementary_classes($class, $current_slot, $date);
                $in_class = array_merge($in_class, $complementary_classes);
                
                $not_in_class = $wpdb->get_results(
                                $wpdb->prepare( $sql_not_in_class, $where)
                            );
            }

        }

        echo '<div id="attendance">';
        
            require GY_CRM_PLUGIN_DIR . 'views/templates/admin/attendance/attendance_list.php';
    
        echo '</div>';
    }

    public function get_complementary_classes($class, $current_slot, $date) {
        global $wpdb;

        $sql ='SELECT u.ID, CONCAT(um3.meta_value, " ", um2.meta_value) name, u.display_name, c.attendance
                FROM wp_users u
                JOIN wp_usermeta um ON u.ID = um.user_id
                    AND um.meta_key = "complementary_slots"
                    AND um.meta_value LIKE %s
                LEFT JOIN wp_class_attendance c ON c.user_id = u.ID
                    AND c.post_id = %s
                    AND c.date = %s
                LEFT JOIN wp_usermeta um3 ON (um.user_id = um3.user_id AND um3.meta_key = "first_name")
                LEFT JOIN wp_usermeta um2 ON (um.user_id = um2.user_id AND um2.meta_key = "last_name") ORDER BY u.display_name ASC';
        $where = ["%$current_slot%", $class, $date];
        $complementary = $wpdb->get_results(
            $wpdb->prepare( $sql, $where)
        );

        return $complementary;
    }

    public function get_active_subscriptions($users) {
        foreach ($users as $key => $user) {
            $program_status = get_user_meta($user->ID, 'status_program_participant', true);
            if ($program_status !== 'active') {
                unset($users[$key]);
            }
        }

        return $users;
    }

    public function query_builder($like = '', $orderby = false)
    {
        global $wpdb;

        $sql = 'SELECT u.ID, CONCAT(um3.meta_value, " ", um2.meta_value) name, u.display_name, c.attendance
                FROM wp_users u
                JOIN wp_usermeta um ON u.ID = um.user_id
                AND um.meta_key = "slots"
                AND um.meta_value '.$like.' LIKE %s
                LEFT JOIN wp_class_attendance c ON c.user_id = u.ID
                AND c.post_id = %s
                AND c.date = %s 
                LEFT JOIN wp_usermeta um3 ON (um.user_id = um3.user_id AND um3.meta_key = "first_name")
                LEFT JOIN wp_usermeta um2 ON (um.user_id = um2.user_id AND um2.meta_key = "last_name")';
        
        if ($orderby) {
            $sql .= ' ORDER BY u.display_name DESC';
        } else {
            $sql .= ' ORDER BY u.display_name ASC';
        }
    
        return $sql;
    }

    public function get_classes($id = null) {

        $args = array('post_type' => 'class',
        'publish_status' => 'published',
        'posts_per_page' => -1, 
        );

        if ($id) {
            $args['post__in'] = [$id];
    
            $posts = get_posts( $args );
            $schedule_id = $_GET['sd'];

            if (!empty($posts)) {

                if (!is_null(self::get_class_days($posts[0], $schedule_id))) {
                    $class = self::get_class_days($posts[0], $schedule_id);
                    return $class['class'].' '.$class['schedule'];
                }

            } else {
                return 'Class doesn\'t exist';
            }

        }
    }

    public function get_class_days($post, $schedule_id)
    {
        global $wpdb;

        $sql = 'SELECT meta_key, meta_value FROM wp_postmeta WHERE meta_id = %s';
        $where = [$schedule_id];
        $current_slot = $wpdb->get_results($wpdb->prepare($sql, $where));

        foreach($this->slot_week as $key => $day) {
            $pattern = "/{$key}/";
            $is_match = preg_match($pattern, $current_slot[0]->meta_key);

            if ($is_match) {
                $class_day = $day;
            }
        }

        $slots_ids = get_post_meta($post->ID, 'slot_ids', true);

        foreach ($slots_ids as $key => $slot) {
            $pattern = "/{$slot}/";
            $is_match = preg_match($pattern, $current_slot[0]->meta_key);

            if ($is_match) {
                $slot_number = $key + 1;
            }
        }

        if (isset($class_day) && isset($slot_number)) {
            $time = date('g:i A', strtotime($current_slot[0]->meta_value));
            $start = strtotime($time);

            $duration = get_field('duration', $post->ID);
    
            $end = $start + 3600 * $duration + 00 * 60;
            $hours = date('H', $end);
            $minutes = date('i', $end);
    
            if ($hours >= 12) {
                $hours = $hours - 12;
                $ampm = ' PM';
            } else {
                $ampm = ' AM';
            }

            $schedule_time = $time .' - '.$hours.':'.$minutes.$ampm;
            $schedule = ' SLOT #'.$slot_number .' ('.$class_day .': '.$schedule_time.')';


            return array(
                'class' => $post->post_title,
                'schedule' => $schedule,
            );
        }

    }

    public function get_links()
    {
        if (isset($_GET['class'])) {
            if (isset($_GET['orderby']) && $_GET['orderby'] == 'desc') {
                echo '/wp-admin/admin.php?page=user-list&class='.$_GET['class'].'&sd='.$_GET['sd'].'&date='.$_GET['date'];
            } else {
                echo '/wp-admin/admin.php?page=user-list&class='.$_GET['class'].'&sd='.$_GET['sd'].'&date='.$_GET['date'].'&orderby=desc';
            }
        }
    }
    
    function gy_attendance_list_page() {

        add_menu_page(
        'Attendance', // Page Title
        'Attendance', // Menu Title
        'edit_attendance', // Capability
        'user-list', // Menu Slug
        array($this, 'gy_attendance_list_callback'), // Callback function
        'dashicons-yes', // Icon
        6 // Position
        );
    }
    
    function gy_attendance_list_callback(){
        echo do_shortcode( '[attendance-list]' );
    }
    
}