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

        if (!isset($_GET['class']) && !isset($_GET['slot'])) {
            $sql = 'SELECT ID, CONCAT(um3.meta_value, " ", um4.meta_value) name
                FROM '.$wpdb->users.' u
                LEFT JOIN wp_usermeta um3 ON (u.ID = um3.user_id AND um3.meta_key = "first_name")
                LEFT JOIN wp_usermeta um4 ON (u.ID = um4.user_id AND um4.meta_key = "last_name")
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
            $sql = 'SELECT ID, CONCAT(um4.meta_value, " ", um5.meta_value) name, um1.meta_value AS status_program_participant
                    FROM '.$wpdb->users.' u
                    JOIN '.$wpdb->usermeta.' um1
                        ON u.ID = um1.user_id
                        AND um1.meta_key = "status_program_participant"';

                    if (isset($_GET['status'])) {
                        if ($_GET['status'] !== 'all') {
                            $sql .= ' AND um1.meta_value = "'.$_GET['status'].'"';
                        }
                    } else {
                        $sql .= ' AND um1.meta_value = "active"';
                    }

                $sql .= ' JOIN '.$wpdb->usermeta.' um3
                            ON u.ID = um3.user_id
                    AND um3.meta_key = "slots"
                    AND um3.meta_value LIKE "%'.$_GET['slot'].'%"
                        LEFT JOIN wp_usermeta um4 ON (u.ID = um4.user_id AND um4.meta_key = "first_name")
                        LEFT JOIN wp_usermeta um5 ON (u.ID = um5.user_id AND um5.meta_key = "last_name")';
        }

        
        if (isset($_GET['orderby'])) {
            $sql .= ' ORDER BY name DESC';
        } else {
            $sql .= ' ORDER BY name ASC';
        }

        if (isset($_GET['orderby'])) {
            echo '<style>
                .order-filter:hover::after {
                    content: " ↑" !important;
                }
            </style>';
        }

        $data = $wpdb->get_results( $sql);

        echo '<div id="program_status">';

        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/program_status/filters.php';
        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/program_status/program_status_list.php';

        echo '</div>';
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

    public function get_class_slots($class_id) {
        $output = '';

        $classes = get_slots_by_class($class_id);

        foreach($classes as $class) {
            $output .= '<li class="class-option" data-slot="'.$class['slot'].'" data-meta="'.$class['meta_id'].'" data-number="'. $class['slot_number'].'">'. $class['slot_number'].'</li>
                        <ul class="hidden">';
                        foreach($class['days'] as $day) {
                            $output .= '<li>'.$day.'</li>';
                        }
                        $output .= '</ul>';
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