<?php
add_action( 'admin_footer', 'get_attendance' );

function get_attendance() { ?>
    <script type="text/javascript" >
    jQuery(document).ready(function($) {

        
        $('.class-slot-filter #class-filter-dropdown').on('change', function() {
            $('.class-slot-filter#slot-filter').hide()
            let id = $(this).val()
            
            $.ajax({
                url: <?php echo '"'.admin_url( 'admin-ajax.php' ).'"'; ?>,
                data : {
                    action: 'get_all_slots',
                    class: id
                },
                success: function(response) {
                    $('.class-slot-filter #slot-filter-dropdown').html(JSON.parse(response))
                    $('.class-slot-filter #slot-filter').show()
                }
            });
        })

        let date = $('#attendance-date-filter').val();
        let scheduleId = $('#schedule_id').val()
        let programClass = $('#post_id').val()

        if (date) {
            if (programClass && scheduleId) {
                changeClassesByDate(date, programClass, scheduleId)
            } else {
                changeClassesByDate(date)
            }
        }

        
        $('#attendance_tables #slot-filter-dropdown').on('change', getClass)
        $('#attendance-date-filter').on('change', getClass)
        $('#attendance-date-filter').on('change', function() {
            changeClassesByDate($(this).val())
        })

        $('body').on('change', '#attendance_tables #class-filter-dropdown', function() {
            $('#slot-filter').hide()
            let date = $('#attendance-date-filter').val();
            let classId = $(this).val()

            if (classId !== '') {
                getClassesAccordingDay(date, {class: classId, type: 'get_slots'}).then(response => {
                    $('#slot-filter-dropdown').html(response)
                    $('#slot-filter').show()
                })
            }

        })

        function getClass() {
            let date = $('#attendance-date-filter').val();
            let classId = $('#class-filter-dropdown').val();
            scheduleId = $('#slot-filter-dropdown').val()
            programClass = $('#class-filter-dropdown').val()
            
            if (programClass) {
                window.location.href = '/wp-admin/admin.php?page=user-list&class='+programClass+'&sd='+scheduleId+'&date='+date;
            }

        }

        async function getClassesAccordingDay(object, type) {
            let response = await $.ajax({
                    url: <?php echo '"'.admin_url( 'admin-ajax.php' ).'"'; ?>,
                    data : {action: 'get_classes_by_day',
                        object: object,
                        type: type
                    }
                });
            return Promise.resolve(JSON.parse(response));
        }

        function changeClassesByDate(date, programClass = '', scheduleId = '') {
            if (programClass == '' && scheduleId == '') {
                getClassesAccordingDay(date, {}).then(response => {
                    $('#class-filter-dropdown').html(response)
                })
            }
            if (programClass !== '' && scheduleId !== '') {
                getClassesAccordingDay(date, {class: programClass, type: 'get_class'}).then(response => {
                    $('#class-filter-dropdown').html(response)
                })
                getClassesAccordingDay(date, {class: programClass, slot: scheduleId, type: 'get_slots'}).then(response => {
                    $('#slot-filter').show()
                    $('#slot-filter-dropdown').html(response)
                })
            }
        }

        $('#attendance .search-submit').on('click', getUsers)
        $('#attendance .search-bar').on('keyup', getUsers)

        let inClassList = $('#in_class tbody tr')
        let notInClassList = $('#not_in_class tbody tr')

        function getUsers(e) {
            e.preventDefault();

            let parent = $(this).parent()
            let id = parent.data('id');
            let search = $('#'+id+' input[name="search"').val();

            if (id == 'in_class') {
                searchUser(inClassList, search, id)
            } else {
                searchUser(notInClassList, search, id)
            }
        }

        function searchUser(usersList, search, id) {
            $.each(usersList, function() {
                $('.class_deselected').addClass('hidden')

                let child = $(this).children(':first-child')
                let name = child.text().toLowerCase()

                if (name.includes(search.toLowerCase()) && name !== 'no items') {
                    $(this).removeClass('hidden');
                    $(this).addClass('row-show');
                } else {
                    $(this).removeClass('row-show');
                    $(this).addClass('hidden');
                }
            })

            let currentList = $('#'+id+' tbody tr')
            let isEmpty = false

            $.each(currentList, function() {
                if (!$(this).hasClass('hidden')) {
                    isEmpty = true
                }
            })

            if (!isEmpty) {
                $('#'+id+' .class_deselected').removeClass('hidden')
            }
        }

        $('input[name^="save_attendance"]').on('change', function() {

            let user = $(this).data('user');            
            let attendance = $(this).val();
            let post = $('#post_id').val()
            let date = $('#date').val()
            let schedule = $('#schedule_id').val()
            let nonce = $('#nonce').val()

            $.ajax({
                url: <?php echo '"'.admin_url( 'admin-ajax.php' ).'"'; ?>,
                data : {action: "save_attendance", 
                        user: user,
                        attendance:  attendance,
                        post: post,
                        date: date,
                        schedule: schedule,
                        nonce: nonce,
                },
                success: function(response) {
                    console.log(response);
                }
            });

            $.ajax({
                url: <?php echo '"'.admin_url( 'admin-ajax.php' ).'"'; ?>,
                data : {action: "non_payment", 
                        user: user,
                        attendance:  attendance,
                        post: post,
                        date: date,
                        schedule: schedule,
                        nonce: nonce,
                },
                success: function(response) {
                    console.log(response);

                   // alert(response);
                }
            });
            
        });

    })
    </script> 
    <?php
}


add_action("wp_ajax_save_attendance", "save_attendance");
add_action("wp_ajax_non_payment", "non_payment");
add_action("wp_ajax_search_attendance", "search_attendance");
add_action("wp_ajax_get_classes_by_day", "get_classes_by_day");

function get_classes_by_day() {
    if ($_GET['object']) {
        $date = $_GET['object'];
        $type = $_GET['type'];

        $day_week = strtolower(date('l', strtotime($date)));
        $slot_week = ['_slot_time_monday' => 'MON', '_slot_time_tuesday' => 'TUE', '_slot_time_wednesday' => 'WED', '_slot_time_thursday' => 'THU', '_slot_time_friday' => 'FRI', '_slot_time_saturday' => 'SAT', '_slot_time_sunday' => 'SUN'];
        
        $args = array('post_type' => 'class',
        'publish_status' => 'published',
        'posts_per_page' => -1,
        'order' => 'ASC',
        'orderby' => 'title',
    );
    
        if ($type['type'] == 'get_slots') {
            $args['post__in'] = array($type['class']);
        }

        $posts = get_posts( $args );

        $output = '';
        $classes_slots = [];
        $classes = [];
        
        foreach($posts as $post) {

            foreach($slot_week as $key => $day) {
                $class_slot = get_class_days($key, $day, $post, $day_week);
                if (!empty($class_slot)) {
                    $classes_slots[] = $class_slot;
                }
            }
        }

        if (!empty($classes_slots)) {
            $output = '<option value="">Select Option</option>';
    
            foreach($classes_slots as $class) {
                foreach ($class as $slot) {
                    if ($type['type'] == 'get_slots') {
                        if (isset($type['slot']) && $type['slot'] == $slot['meta_id']) {
                            $output .= '<option class="class-option" value="'.$slot['meta_id'].'" selected>'. $slot['schedule'].'</option>';
                        } else {
                            $output .= '<option class="class-option" value="'.$slot['meta_id'].'">'. $slot['schedule'].'</option>';
                        }
                    } else {
                        if (!isset($classes[$slot['id']])) {
                            $classes[$slot['id']] = 1;
                            if (isset($type['class']) && $type['class'] == $slot['id']) {
                                $output .= '<option class="class-option" value="'.$slot['id'].'" selected>'.$slot['class'].'</option>';
                            } else {
                                $output .= '<option class="class-option" value="'.$slot['id'].'">'.$slot['class'].'</option>';
                            }
                        }
                    }
                }
            }
        }


        if (empty($output)) {
            $output = '<option>Empty</option>';
        }

        echo json_encode($output);
        }

        die();
}

    function get_class_days($key, $day, $post, $day_week = null) {
        global $wpdb;

        $slot_ids = get_post_meta($post->ID, 'slot_ids', true);

        if (!empty($slot_ids)) {
            foreach ($slot_ids as $slot) {
                $sql = 'SELECT * FROM wp_postmeta WHERE meta_key LIKE %s AND post_id = %s';
                $where = ["%$slot%", $post->ID];

                $results = wp_list_pluck($wpdb->get_results($wpdb->prepare($sql, $where)), 'meta_value', 'meta_key');

                if (!empty($results)) {
                    $slots[$slot] = $results;
                }
            }

            $count = 0;
            foreach($slots as $slot_id => $slot) {

                $count += 1;

                $slot_status = get_post_meta($post->ID, $slot_id.'_slot_status', true);

                if (isset($day_week)) {
                    $is_day = strpos($day_week, strtolower($day));
                } else {
                    $is_day = true;
                }

                if (!empty($slot[$slot_id.$key]) && $is_day !== false && $slot_status !== 'inactive') {
        
                    $time = date('g:i A', strtotime($slot[$slot_id.$key]));
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
        
                    $full_time = $time .' - '.$hours.':'.$minutes.$ampm;

                    $slot_number = ' SLOT #'.$count;
                    $full_time = ' ('.$day .': '.$full_time.')';
                    $start_time = ' ('.$day.': '.$time.')';

                    $sql = 'SELECT meta_id
                        FROM '.$wpdb->postmeta.'
                            WHERE post_id = %s
                            AND meta_key = %s';
                    
                    $where = [$post->ID, $slot_id.$key];
        
                    $data = $wpdb->get_results(
                        $wpdb->prepare( $sql, $where)
                    );

                    $dd_classes[] = array(
                        'id' => $post->ID,
                        'class' => $post->post_title,
                        'schedule' => $slot_number.$full_time,
                        'slot_id' => $slot_id,
                        'meta_id' => $data[0]->meta_id,
                        'start_time' => $slot_number.$start_time,
                    );
                }
            }
        }

        return $dd_classes;
    }

function save_attendance() {

    $nonce = $_GET['nonce'];

    if (wp_verify_nonce($nonce, 'attendance_nonce')) {

        $attendance_obj = array(
            'user' => $_GET['user'],
            'attendance' => $_GET['attendance'],
            'date' => $_GET['date'],
            'schedule' => $_GET['schedule'],
            'post' => $_GET['post'],
            'is_edit' => $_GET['is_edit'],
        );
    
        validate_action($attendance_obj);
    
        echo json_encode(1);

    } else {
        echo json_encode(0);
    }

	die();
}

function non_payment(){
    global $wpdb;
    $from = !empty(get_option('custom_note_from')) ? get_option('custom_note_from') : 'ca@gymnasticsofyork.com';
    $replyto = !empty(get_option('custom_note_replyto')) ? get_option('custom_note_replyto') : 'ca@gymnasticsofyork.com';
    $bcc  = !empty(get_option('custom_note_bcc')) ? get_option('custom_note_bcc') : 'ca@gymnasticsofyork.com';

    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'From: <'.$from.'>';
    $headers[] = 'Reply-To: <'.$replyto.'>';
    $headers[] = 'Bcc: '.$bcc;  $headers[] = 'Content-Type: text/html; charset=UTF-8';
    // information received
    $attendance_obj = array(
        'user' => $_GET['user'],
        'attendance' => $_GET['attendance'],
        'date' => $_GET['date'],
        'schedule' => $_GET['schedule'],
        'post' => $_GET['post'],
        'is_edit' => $_GET['is_edit'],
    );

    if ($attendance_obj['attendance'] === 'present') {
        
        // Search for the parent_id
        $parent_id = get_user_meta($attendance_obj['user'], 'smuac_account_parent', true);
        // Get the users subscriptions
        $subscriptions = wcs_get_users_subscriptions($parent_id);
        
        foreach ($subscriptions as $subscription) {
            if ( !empty( $subscription ) ) {
                $subscription_id = $subscription->get_id();
                $class_meta = get_post_meta($subscription_id, '_wcs_subscription_classes',true);
                // If the class is the same as the one that owns the subscription
                if ( !empty($class_meta) &&  $class_meta[0] === $attendance_obj['post'] ) {
                    $status = $subscription->get_status();
                    $next_payment_date = $subscription->get_date('next_payment');
                    // Check if the subscription is expired
                    if ($status !== 'completed' && $next_payment_date < $attendance_obj['date'] ) { 
                        $sql = "SELECT count(user_id) as id FROM wp_class_attendance wca 
                                WHERE user_id ={$attendance_obj['user']}
                                AND post_id = {$attendance_obj['post'] }
                                AND attendance = '{$attendance_obj['attendance']}' 
                                AND date between '{$next_payment_date}' AND '{$attendance_obj['date']}'";
                        $attendance =  $wpdb->get_results($sql);  
                        $order = wc_get_order($subscription_id);
                        echo '<pre>';
                        print_r($attendance);
                        echo '<pre>';
                        if ( intval($attendance[0]->id ) === 2 ) {
                            $order->add_order_note("For date {$attendance_obj['date']}, this user was attending without paying the subscription");
                            $order->save();
                        }
                        if ( intval($attendance[0]->id) === 3 ) {
                            $headers[] = 'Content-Type: text/html; charset=UTF-8';
                            $email_temp = get_posts(array(
                                'post_type' => 'email_template',
                                'post_status' => 'publish',
                                'title' => 'Third Class Without Payment',
                                'orderby' => 'title',
                                'order' => 'ASC',
                            ));
                            $user = get_user_by('ID',$parent_id);
                             // Message assembly
                             $message = $email_temp[0]->post_content;
                             $message = str_replace(
                                 '{{user_name}}',
                                 $user->display_name,
                                 $message
                             );
                             $message = str_replace(
                                 '{{subscription_id}}',
                                 $subscription_id,
                                 $message
                             );
                             //TO DO{{CLASS NAME}}
                             $message = str_replace(
                                '{{next_payment}}',
                                $next_payment_date,
                                $message
                            );
                          
                            $is_sent = wp_mail($user->user_email, $email_temp[0]->post_title, $message, $headers);

                            if ($is_sent) {
                                $current = get_current_user_id();
                                $current_user = get_user_by('id', $current);
                                $comment_user = array(
                                    'comment_author' => $current_user->display_name,
                                    'comment_content' => 'Email "'.$email_temp[0]->post_title.'" sent to '.$user->user_email.', third class attending without paying the subscription fee.',
                                    'user_id' => $user->id,
                                    'comment_meta'         => array(
                                        'is_customer_note'       => sanitize_text_field(1),
                                        )
                                    );
                
                                wp_insert_comment($comment_user);
                            }
                        $user = get_user_by('ID', $attendance_obj['user']);
                        $user_name = $user ? $user->display_name : '';
                

                        }
 
                    }
                }
            }
        }
    }
    if ($attendance_obj['attendance']  === 'absent' ) {
        // Search for the parent_id
        $parent_id = get_user_meta($attendance_obj['user'], 'smuac_account_parent', true);
        // Get the users subscriptions
        $subscriptions = wcs_get_users_subscriptions($parent_id);

        foreach ($subscriptions as $subscription) {
            if ( !empty( $subscription ) ) {
                $subscription_id = $subscription->get_id();
                $class_meta = get_post_meta($subscription_id, '_wcs_subscription_classes',true);
                // If the class is the same as the one that owns the subscription
                if ( !empty($class_meta) &&  $class_meta[0] === $attendance_obj['post'] ) {
                
                    $sql = "SELECT DISTINCT c1.user_id, c1.post_id, c1.date AS first_absent_session, 
                                c2.date AS second_absent_session, DATEDIFF(c2.date, c1.date) AS days_difference
                            FROM wp_class_attendance c1
                            JOIN wp_class_attendance c2 ON c2.user_id = c1.user_id AND c2.post_id = c1.post_id
                            WHERE c1.attendance = 'absent'
                            AND c2.attendance = 'absent'
                            AND c2.date > c1.date
                            AND c1.post_id = {$attendance_obj['post']}
                            AND c1.user_id = {$attendance_obj['user']}
                            AND DATEDIFF(c2.date, c1.date) >= 7
                            AND NOT EXISTS (
                            SELECT 1
                            FROM wp_class_attendance c3
                            WHERE c3.user_id = c1.user_id
                            AND c3.post_id = c1.post_id
                            AND c3.attendance = 'present'
                            AND c3.date > c1.date
                            AND c3.date < c2.date
                            )
                            ORDER BY c1.user_id, c1.post_id, c1.date";
                    $attendance =  $wpdb->get_results($sql);  
                    if ( intval( $attendance[0]->days_difference ) >= 7 &&  intval( $attendance[0]->days_difference ) < 15) {
                        $email_temp = get_posts(array(
                            'post_type' => 'email_template',
                            'post_status' => 'publish',
                            'title' => 'Absence From Classes',
                            'orderby' => 'title',
                            'order' => 'ASC',
                        ));
                        $user = get_user_by('ID',$parent_id);
                        // Message assembly
                        $message = $email_temp[0]->post_content;
                        $message = str_replace(
                            '{{user_name}}',
                            $user->display_name,
                            $message
                        );
                        $message = str_replace(
                            '{{number_times}}',
                            '2nd.',
                            $message
                        );
                        $message = str_replace(
                            '{{subscription_id}}',
                            $subscription_id,
                            $message
                        );
                        //TO DO{{CLASS NAME}}
                        $is_sent = wp_mail($user->user_email, $email_temp[0]->post_title, $message, $headers);
                        if ($is_sent) {
                            $current = get_current_user_id();
                            $current_user = get_user_by('id', $current);
                            $comment_user = array(
                                'comment_author' => $current_user->display_name,
                                'comment_content' => 'Email "'.$email_temp[0]->post_title.'" sent to '.$user->user_email.', absence for the  2nd consecutive time.',
                                'user_id' => $user->id,
                                'comment_meta'         => array(
                                    'is_customer_note'       => sanitize_text_field(1),
                                    )
                                );
            
                            wp_insert_comment($comment_user);
                        }
                        break;
                    }
                    
                    $query = "SELECT DISTINCT c1.user_id, c1.post_id,
                                        c1.date AS first_absent_session,
                                        c2.date AS second_absent_session,
                                        c3.date AS third_absent_session,
                                        DATEDIFF(c3.date, c1.date) AS days_difference
                        FROM wp_class_attendance c1
                        JOIN wp_class_attendance c2 ON c2.user_id = c1.user_id AND c2.post_id = c1.post_id
                        JOIN wp_class_attendance c3 ON c3.user_id = c1.user_id AND c3.post_id = c1.post_id
                        WHERE c1.attendance = 'absent'
                        AND c2.attendance = 'absent'
                        AND c3.attendance = 'absent'
                        AND c2.date > c1.date
                        AND c3.date > c2.date
                        AND c1.post_id = {$attendance_obj['post']}
                        AND c1.user_id = {$attendance_obj['user']}
                        AND DATEDIFF(c3.date, c1.date) >= 15
                        AND NOT EXISTS (
                            SELECT 1
                            FROM wp_class_attendance c4
                            WHERE c4.user_id = c1.user_id
                            AND c4.post_id = c1.post_id
                            AND c4.attendance = 'present'
                            AND c4.date > c1.date
                            AND c4.date < c3.date
                        )
                        ORDER BY c1.user_id, c1.post_id, c1.date";
                    $results =  $wpdb->get_results($query);  
                    if ( intval( $results[0]->days_difference ) >= 15 ) {
                        $email_temp = get_posts(array(
                            'post_type' => 'email_template',
                            'post_status' => 'publish',
                            'title' => 'Absence From Classes',
                            'orderby' => 'title',
                            'order' => 'ASC',
                        ));
                        $user = get_user_by('ID',$parent_id);
                        echo '<pre>';
                        var_dump($user->user_email);
                        echo '<pre>';
                        // Message assembly
                        $message = $email_temp[0]->post_content;
                        $message = str_replace(
                            '{{user_name}}',
                            $user->display_name,
                            $message
                        );
                        $message = str_replace(
                            '{{number_times}}',
                            '3rd.',
                            $message
                        );
                        $message = str_replace(
                            '{{subscription_id}}',
                            $subscription_id,
                            $message
                        );
                        //TO DO{{CLASS NAME}}
                      
                        $is_sent = wp_mail($user->user_email, $email_temp[0]->post_title, $message, $headers);

                        if ($is_sent) {
                            $current = get_current_user_id();
                            $current_user = get_user_by('id', $current);
                            $comment_user = array(
                                'comment_author' => $current_user->display_name,
                                'comment_content' => 'Email "'.$email_temp[0]->post_title.'" sent to '.$user->user_email.', absence for the  3rd consecutive time.',
                                'user_id' => $user->id,
                                'comment_meta'         => array(
                                    'is_customer_note'       => sanitize_text_field(1),
                                    )
                                );
            
                            wp_insert_comment($comment_user);
                        }
                    }
                }
            }
        }
    }
}


function validate_action($attendance_obj)
{
    $user = get_user_by('id', $attendance_obj['user']);
    $post = get_post($attendance_obj['post']);

    $schedule = metadata_exists('post', $attendance_obj['post'], $attendance_obj['schedule']);
    if (isset($user) && isset($post) && isset($schedule) && !empty($attendance_obj['date'])) {
        save_class_attendance($attendance_obj);
    }
}

function edit_class_attendance($attendance) {
    global $wpdb;

    $sql = 'SELECT *
        FROM wp_class_attendance c
        WHERE c.user_id = %s
            AND c.post_id = %s
            AND c.post_meta = %s
            AND c.date = %s';

    $where = [$attendance['user'], $attendance['post'], $attendance['schedule'], $attendance['date']];

    $is_attendance = $wpdb->get_results(
        $wpdb->prepare( $sql, $where)
    );

    if (!empty($is_attendance)) {
        $sql = 'UPDATE wp_class_attendance c
                SET attendance = "'.$attendance['attendance'].'"
                WHERE c.user_id = '.$attendance['user'].'
                    AND c.post_id = '.$attendance['post'].'
                    AND c.post_meta = '.$attendance['schedule'].'
                    AND c.date = "'.$attendance['date'].'"';

        $wpdb->query($sql);
    }
}

function save_class_attendance($attendance)
{
    global $wpdb;

    $table_name = 'wp_class_attendance';

    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {

        $sql = "CREATE TABLE `wp_class_attendance` (
            `user_id` BIGINT(19) NOT NULL,
            `post_id` BIGINT(19) NOT NULL,
            `date` DATE NOT NULL,
            `attendance` ENUM('absent','present','makeup') NOT NULL,
            `post_meta` BIGINT(19) NOT NULL
        );";

        $wpdb->query( $sql );

        if ( $wpdb->last_error) {
            $error = true;
        }
    }

    if (!isset($error)) {

        $sql = 'SELECT *
        FROM wp_class_attendance c
        WHERE c.user_id = %s
            AND c.post_id = %s
            AND c.post_meta = %s
            AND c.date = %s';

        $where = [$attendance['user'], $attendance['post'], $attendance['schedule'], $attendance['date']];

        $is_attendance = $wpdb->get_results(
            $wpdb->prepare( $sql, $where)
        );
        echo '<pre>';
        var_dump($is_attendance);
        echo '<pre>'; 

        if (!empty($is_attendance)) {
            $sql = 'UPDATE wp_class_attendance c
                    SET attendance = "'.$attendance['attendance'].'"
                    WHERE c.user_id = '.$attendance['user'].'
                        AND c.post_id = '.$attendance['post'].'
                        AND c.post_meta = '.$attendance['schedule'].'
                        AND c.date = "'.$attendance['date'].'"';

            $wpdb->query($sql);

        } else {
            $data = array(
                'user_id' => $attendance['user'],
                'post_id' => $attendance['post'],
                'date' => $attendance['date'],
                'attendance' => $attendance['attendance'],
                'post_meta' => $attendance['schedule'],
            );
            
            $wpdb->insert( $table_name, $data );
        }

    }

}

function is_empty($attendance) {
    return array_filter($attendance, function ($value) {
        return empty($value);
    });
}

?>