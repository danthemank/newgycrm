<?php

class ProgramStatus {
    public $slot_week;

    public function __construct($slot_week)
    {
        $this->slot_week = $slot_week;

        add_shortcode( 'gy_program_status', array($this, 'gy_program_status') );

        add_action( 'admin_menu', array($this, 'gy_program_status_page') );
    }

    public function gy_program_status() {

        global $wpdb;

        if (!isset($_GET['class']) || $_GET['class'] == 'no') {

            $sql = $this->query_builder(true);
            
            if (isset($_GET['search'])) {
                $name = $_GET['search'];

                $sql = $this->query_builder(true, true);

                if (isset($_GET['orderby'])) {
                    $sql = $this->query_builder(true, true, true);
                }

                $where = ["%$name%"];
            } else {
                if (isset($_GET['orderby'])) {
                    $sql = $this->query_builder(true, false, true);
                }
            }
            
            
        } else {
            $class = $_GET['class'];

            $sql = $this->query_builder(false);
            
            $where = ["%$class%"];

            if (isset($_GET['search'])) {
                $name = $_GET['search'];

                $sql = $this->query_builder(false, true);

                if (isset($_GET['orderby'])) {
                    $sql = $this->query_builder(false, true, true);
                }

                $where = ["%$class%", "%$name%"];
            } else {
                if (isset($_GET['orderby'])) {
                    $sql = $this->query_builder(false, false, true);
                }
            }
            
            
        }

        if (isset($_GET['orderby'])) {
            echo '<style>
                .order-filter:hover::after {
                    content: " ↑" !important;
                }
            </style>';
        }

        $data = isset($where) ?
                $wpdb->get_results(
                    $wpdb->prepare( $sql, $where)
                ) : 
                $wpdb->get_results($sql);


        echo '<div id="program_status">';

        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/program_status/filters.php';
        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/program_status/program_status_list.php';

        echo '</div>';
    }

    public function query_builder($unenrolled = false, $search = false, $orderby = false)
    {

        global $wpdb;

        if ($unenrolled) {

            $sql = 'SELECT ID, display_name
            FROM '.$wpdb->users.'
            WHERE ID NOT IN (
                SELECT user_id
                FROM '.$wpdb->usermeta.'
                    WHERE meta_key = "classes"
            ) AND ID IN (
                SELECT user_id
                FROM '.$wpdb->usermeta.'
                    WHERE meta_key = "smuac_account_parent"
            )';

        } else {

        $sql = 'SELECT ID, display_name, um1.meta_value AS status_program_participant
            FROM '.$wpdb->users.' u
            JOIN '.$wpdb->usermeta.' um1
                ON u.ID = um1.user_id
                AND um1.meta_key = "status_program_participant"
            JOIN '.$wpdb->usermeta.' um2
                ON um1.user_id = um2.user_id
                AND um2.meta_key = "classes"
                AND um2.meta_value LIKE %s';
        }

        if ($search) {
            $sql .= ' AND display_name LIKE %s';
        }
        
        if ($orderby) {
            $sql .= ' ORDER BY display_name DESC';
        } else {
            $sql .= ' ORDER BY display_name ASC';
        }

        return $sql;
    }

    public function get_links()
    {
        if (isset($_GET['class'])) {
            if (isset($_GET['search'])) {
                if (isset($_GET['orderby']) && $_GET['orderby'] == 'desc') {
                    echo '/wp-admin/admin.php?page=program-status&class='.$_GET['class'].'&search='.$_GET['search'];
                } else {
                    echo '/wp-admin/admin.php?page=program-status&class='.$_GET['class'].'&search='.$_GET['search'].'&orderby=desc';
                }
            } else {
                if (isset($_GET['orderby']) && $_GET['orderby'] == 'desc') {
                    echo '/wp-admin/admin.php?page=program-status&class='.$_GET['class'];
                } else {
                    echo '/wp-admin/admin.php?page=program-status&class='.$_GET['class'].'&orderby=desc';
                }

            }
        }
    }

    public static function get_classes($id = null, $type = null) {
        $args = array('post_type' => 'class',
            'publish_status' => 'published',
            'posts_per_page' => -1,
            'order' => 'ASC',
            'orderby' => 'title',
        );

        if ($id && !$type) {
            $args['post__in'] = [$id];
            $posts = get_posts( $args );
            
            if (!empty($posts)) {

                if (isset($_GET['sd'])) {
                    if (!is_null(self::get_class_days($posts[0], $_GET['sd']))) {
                        $class = self::get_class_days($posts[0], $_GET['sd']);
                        return $class['class'].' '.$class['schedule'];
                    }
                } else {
                    return $posts[0]->post_title;
                }
            }

        } else if ($id && $type) {
            $posts = get_posts( $args );
            $html = '<option value="">Select Option</option>';

            foreach($posts as $post) {
                if ($id == $post->ID) {
                    $html .= '<option value="'.$post->ID.'" selected>'.$post->post_title.'</option>';
                } else {
                    $html .= '<option value="'.$post->ID.'">'.$post->post_title.'</option>';
                }
            }
            return $html;
        } else {
            $posts = get_posts( $args );
            $html = '<option value="">Select Option</option>';

            foreach($posts as $post) {
                $html .= '<option value="'.$post->ID.'">'.$post->post_title.'</option>';
            }
            return $html;
        }
    }

    public function get_class_slots($class_id, $slot_id) {
        $output = '';

        $classes = get_slots_by_class($class_id);

        foreach($classes as $class) {
            if ($class['slot'] == $slot_id) {
                $output .= '<option class="class-option" value="'.$class['slot'].'" data-meta="'.$class['meta_id'].'" data-number="'. $class['slot_number'].'" selected>'. $class['slot_number'].' '.$class['days'].'</option>';
            } else {
                $output .= '<option class="class-option" value="'.$class['slot'].'" data-meta="'.$class['meta_id'].'" data-number="'. $class['slot_number'].'">'. $class['slot_number'].' '.$class['days'].'</option>';
            }
        }
        return $output;

    }

    public static function get_class_days($post, $schedule_id)
    {
        global $wpdb;

        $sql = 'SELECT meta_key, meta_value FROM wp_postmeta WHERE meta_id = %s';
        $where = [$schedule_id];
        $current_slot = $wpdb->get_results($wpdb->prepare($sql, $where));

        foreach(self::$slot_week as $key => $day) {
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

    
    public function gy_program_status_page() {

        add_menu_page(
        'Program Status', // Page Title
        'Program Status', // Menu Title
        'manage_options', // Capability
        'program-status', // Menu Slug
        array($this, 'gy_program_status_callback'), // Callback function
        'dashicons-welcome-write-blog', // Icon
        9 // Position
        );
    }
    
    public function gy_program_status_callback() {
        echo do_shortcode('[gy_program_status]');
    }
    
}