<?php

class CronSchedules
{
    public  $price_per_hour;
    private $discount_coupon;
    public $registration_fee;
    public function __construct($price_per_hour)
    {
        $this->price_per_hour = $price_per_hour;
        $this->discount_coupon = 'sf2zxd6b';
        $this->registration_fee = get_option( 'registration_fee' );

        add_action('init', array($this, 'schedule_gy_cron_actions'));
        add_action('gy_cron_actions', array($this, 'gy_cron_actions'));

        add_action('init', array($this, 'schedule_classes_to_programs'));
        add_action('classes_to_products', array($this, 'classes_to_products'));

        add_filter('cron_schedules', array($this, 'cron_schedules'));

        // add_action('wp', array($this, 'test_fn'));
    }

    public function test_fn() {
        // $stripe = new \Stripe\StripeClient(STRIPE_TEST_KEY);
        // dd($stripe->paymentMethods->all(['limit' => 5]));
    }

    public function cron_schedules($schedules)
    {

        $schedules['fifteen_minutes'] = array(
            'interval' => 15 * 60,
            'display'  => esc_html__( 'Every Fifteen Minutes' ),
        );
        return $schedules;
    }

    
    public function schedule_gy_cron_actions() {
        $saved_schedules =_get_cron_array();
        $is_schedule = false;
    
        foreach($saved_schedules as $schedules) {
            if (array_key_exists('gy_cron_actions', $schedules)) {
                $is_schedule = true;
            }
        }

        if (!$is_schedule) {
            wp_schedule_event(time(), 'daily', 'gy_cron_actions');
        }
    }

    public function gy_cron_actions() {
        $this->update_orders_statuses();

        $this->applied_late_fees();

        $this->remove_old_notifications();
    }

    public function remove_old_notifications() {
        global $wpdb;

        $sql = 'SELECT c.comment_ID, c.comment_date
                FROM wp_comments c
                JOIN wp_commentmeta co
                    ON c.comment_ID = co.comment_id
                    AND co.meta_key = "is_notification"
        ORDER BY comment_date ASC';

        $results = $wpdb->get_results($sql);

        foreach ($results as $notif) {
            $comment_date = new DateTime($notif->comment_date);
            $today = new DateTime();
    
            $interval = $comment_date->diff($today);

            if ($interval->days >= 15) {
                wp_delete_comment($notif->comment_ID);
            }
        }

    }

    public static function update_orders_statuses() {
        if (get_option('automatic_applied_late_fees') == 1) {

            $users = get_clients_with_outstanding_payments();

            foreach($users as $user) {
                if ($user->balance >= 0) {

                    $orders = wc_get_orders(array(
                        'customer_id' => $user->ID,
                        'status'      => array('pending', 'wc_pending')
                    ));
                    $subscriptions = wcs_get_subscriptions(array('customer_id' => $user->ID, 'status' => array('pending')));
        
                    foreach($orders as $order) {
                        $order->set_status('on-hold');
                    }
                    
                    foreach($subscriptions as $sub) {
                        $sub->set_status('on-hold');
                    }
                }
            }
        }
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
                            if ($current_time->diff($invoice_creation)->days >= $days_late && $current_time >= $invoice_creation) {
            
                                if ($type == 'percentage') {
                                    $fee = floatval($order->get_total() * ($amount / 100));
                                } else {
                                    $fee = floatval($amount);
                                }
        
                                $order->add_item(EmailTemplates::create_registration_fee('Applied Late Fees', $fee));
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
                            if ($current_time->diff($invoice_creation)->days >= $days_late && $current_time >= $invoice_creation) {
            
                                if ($type == 'percentage') {
                                    $fee = floatval($sub->get_total() * ($amount / 100));
                                } else {
                                    $fee = floatval($amount);
                                }
        
                                $sub->add_item(EmailTemplates::create_registration_fee('Applied Late Fees', $fee));
                                $sub->calculate_totals();
                                $sub->save();
                            }
                        }
                    }
                }
    
            }
        }
    }

    public function schedule_classes_to_programs() {
        $saved_schedules =_get_cron_array();
        $is_schedule = false;
    
        foreach($saved_schedules as $schedules) {
            if (array_key_exists('classes_to_products', $schedules)) {
                $is_schedule = true;
            }
        }

        if (!$is_schedule) {
            wp_schedule_event(strtotime('12:00:00'), 'fifteen_minutes', 'classes_to_products');
        }
    }

    
    public function classes_to_products() {

        $is_paused = get_option('pause_classes_to_products');

        if (!$is_paused) {

            global $wpdb;
    
            $args = array('post_type' => 'product',
                'publish_status' => 'publish',
                'posts_per_page' => -1
            );
    
            $products = get_posts( $args );
    
            $not_in = array();
    
            if ($products) {
                foreach($products as $product) {
                    $is_product = get_post_meta($product->ID, 'post_id', true);
    
                    if ($is_product) {
                        $args = array('post_type' => 'class',
                            'p' => $is_product,
                            'publish_status' => 'publish',
                            'posts_per_page' => -1,
                        );
            
                        $post = wp_list_pluck( get_posts( $args ), 'ID' );
                        $not_in[] = $post;
                    }
        
                }
            }
    
            $args = array('post_type' => 'class',
                'post__not_in' => $not_in,
                'publish_status' => 'publish',
                'posts_per_page' => -1
            );
    
            $posts = get_posts( $args );
            
            foreach($posts as $post) {
                $is_product = get_post_meta($post->ID, 'product_id', true);
                $hours_per_week = get_field('hours_per_week', $post->ID);
    
                if (!empty($hours_per_week) && !$is_product) {
        
                    $product = new WC_Product_Variable();
                    $product->set_name($post->post_title);
                    $product->set_description($post->post_content);
                    $product->save();
                    $product_id = $product->get_id();

                    foreach ($this->price_per_hour as $hour => $price) {
                        if ($hours_per_week == $hour) {
                            
                            update_post_meta($product_id, '_regular_price', $price);
                            update_post_meta($product_id, '_price', $price);
                            update_post_meta($product_id, '_subscription_price',  $price);
                            update_post_meta($product_id, '_subscription_period', 'month');
                            update_post_meta($product_id, '_subscription_period_interval', 1);
                            update_post_meta($product_id, '_subscription_trial_period', 'day');
                            update_post_meta($product_id, '_subscription_limit', 'yes');
                            update_post_meta($product_id, '_subscription_one_time_shipping', 'no');
                            update_post_meta($product_id, '_subscription_payment_sync_date', 6);
                            update_post_meta($product_id, '_subscription_trial_length', 0);
                            update_post_meta($product_id, '_subscription_sign_up_fee', '');
                            update_post_meta($product_id, '_subscription_length', 0);
                            update_post_meta($product_id, '_stock_status', 'instock');
                    
                            update_post_meta($post->ID, 'product_id', $product_id);
                            update_post_meta($product_id, 'post_id', $post->ID);
                
                            $sql = 'INSERT INTO '.$wpdb->term_relationships.' (object_id, term_taxonomy_id, term_order)
                                    VALUES ('.$product_id.', 290, 0)';
                            $wpdb->query($sql);
                
                            $term = get_term_by('slug', 'gymnastics-classes', 'product_cat');
                            if (empty($term)) {
                                wp_insert_term('Gymnastics Classes', 'product_cat', array('slug' => 'gymnastics-classes'));
                            } else {
                                wp_set_object_terms($product_id, $term->term_id, 'product_cat');
                            }

                            $uncategorized = get_term_by('slug', 'uncategorized', 'product_cat');
                            wp_remove_object_terms($product_id, $uncategorized->term_id, 'product_cat');
                        }
                    }

                    if ($hours_per_week !== '') {
                        $schedule_meta['hours_per_week'] = array(
                            'name' => 'hours_per_week',
                            'value' => $hours_per_week,
                            'position' => 1,
                            'is_visible' => 1,
                            'is_variation' => 1,
                            'is_taxonomy' => 0
                        );
        
                        update_post_meta($product_id, '_product_attributes', $schedule_meta);
                    }
                }
            }
        }

    }
}