<?php 

class Billing {

    private $discount_coupon;

    public $registration_fee;
    public $price_per_hour;

    public function __construct($price_per_hour) {
        $this->discount_coupon = 'sf2zxd6b';
        $this->registration_fee = get_option( 'registration_fee' );
        $this->price_per_hour = $price_per_hour;

        // add_action('init', array($this, 'add_to_cart'));

        // add_action('woocommerce_cart_calculate_fees', array($this, 'apply_registration_fee'));
        // add_action('woocommerce_cart_calculate_fees', array($this, 'apply_siblings_coupon'));

        add_action('woocommerce_thankyou', array($this, 'subscription_purchase_made'), 10, 1);
        
        add_action( 'wp', array($this, 'remove_woocommerce_prorated_price'), 10);
        add_filter( 'woocommerce_subscriptions_cart_get_price', array($this, 'set_prorated_price_from_start_date'), 10, 2 );
        add_filter( 'wc_stripe_force_save_payment_method', '__return_true', 10, 3 );
        add_action('woocommerce_add_to_cart', array($this, 'disable_subscription_products_cart'), 10, 6);
    }

    public function disable_subscription_products_cart($cart_item_key, $product_id, $quantity, $variation_id, $cart_item_data) {
        $product = wc_get_product($product_id);
        
        if (WC_Subscriptions_Product::is_subscription( $product)) {
            WC()->cart->remove_cart_item($cart_item_key);
        }
    }

    public function gycrm_save_ach_payment_method($order, $gateway_id){

        if($order->get_payment_method() === 'stripe_ach'){
            $gateway = WC()->payment_gateways()->payment_gateways()[$gateway_id];
            if($gateway->supports('add_payment_method') && !$gateway->use_saved_source()){
                $_POST[ $gateway->save_source_key ] = true;
            }
        }
    }

    public function remove_woocommerce_prorated_price() {
        remove_filter('woocommerce_subscriptions_cart_get_price', array('WC_Subscriptions_Synchroniser', 'set_prorated_price_for_calculation'));
    }

    public function set_prorated_price_from_start_date( $price, $product ) {
        $id = get_current_user_id();
        
		if ( WC_Subscriptions_Product::is_subscription( $product ) && WC_Subscriptions_Synchroniser::is_product_prorated( $product ) && 'none' == WC_Subscriptions_Cart::get_calculation_type() ) {

            $start_date = get_user_meta($id, 'start_date', true);

            if (!empty($start_date)) {
                $next_payment_date = strtotime($start_date);
            } else {
                $next_payment_date = WC_Subscriptions_Synchroniser::calculate_first_payment_date( $product, 'timestamp' );
            }

			if ( WC_Subscriptions_Synchroniser::is_today( $next_payment_date ) ) {
				return $price;
			}

			switch ( WC_Subscriptions_Product::get_period( $product ) ) {
				case 'week':
					$days_in_cycle = 7 * WC_Subscriptions_Product::get_interval( $product );
					break;
				case 'month':
					$days_in_cycle = gmdate( 't' ) * WC_Subscriptions_Product::get_interval( $product );
					break;
				case 'year':
					$days_in_cycle = ( 365 + gmdate( 'L' ) ) * WC_Subscriptions_Product::get_interval( $product );
					break;
			}

            
			$days_until_next_payment = ceil( ( $next_payment_date - gmdate( 'U' ) ) / ( 60 * 60 * 24 ) );

			$sign_up_fee = WC_Subscriptions_Product::get_sign_up_fee( $product );

			if ( $sign_up_fee > 0 && 0 == WC_Subscriptions_Product::get_trial_length( $product ) ) {
				$price = $sign_up_fee + ( $days_until_next_payment * ( ( $price - $sign_up_fee ) / $days_in_cycle ) );
			} else {
				$price = $days_until_next_payment * ( $price / $days_in_cycle );
			}

			// Now round the amount to the number of decimals displayed for prices to avoid rounding errors in the total calculations (we don't want to use WC_DISCOUNT_ROUNDING_PRECISION here because it can still lead to rounding errors). For full details, see: https://github.com/Prospress/woocommerce-subscriptions/pull/1134#issuecomment-178395062
			$price = round( $price, wc_get_price_decimals() );
		}

		return $price;
	}

    public function get_totals($user) {
        $has_siblings = false;
        $sibling_rate = array();
        $siblings_total = 0;
                
        $products_ids = [];

        $total_hours = 0;

        $is_class = false;

        $children = get_user_meta($user->ID, 'smuac_multiaccounts_list', true);

        if ($children) {

            $children = explode(',', get_user_meta($user->ID, 'smuac_multiaccounts_list', true));

            if (count($children) >= 3) {
                $has_siblings = true;
            }

            foreach($children as $key => $child) {
                if ($child !== '') {
                $counter = 0;

                $total_hours_per_child = 0;
                    
                $classes = get_user_meta($child, 'classes', true);

                if ($classes) {
                    
                    if (is_array($classes[0])) {
                        $classes = $classes[0];
                    }
                    
                    foreach ($classes as $class) {
                        $hours_per_week = get_post_meta($class, 'hours_per_week', true);
                        if ($hours_per_week) {

                            $counter += 1;

                            $args = array('post_type' => 'class',
                                'post_status' => 'publish',
                                'p' => $class,
                                'posts_per_page' => -1,
                            );

                            $post_title = wp_list_pluck( get_posts( $args ), 'post_title' );

                            $total_hours_per_child += $hours_per_week;
                            $total_hours += $total_hours_per_child;

                            if ($has_siblings) {
                                $sibling_rate[] = $hours_per_week;
                            }
                            
                        }
                    }
                    $price_per_child = $this->price_per_hour[strval($total_hours_per_child)];
                    $siblings_total += $price_per_child;
                }
            }
        }

            if ($total_hours > 0) {

                $total_amount = $this->price_per_hour[strval($total_hours)];

                if ($has_siblings) {
                    $last_sibling_hours = $sibling_rate[count($sibling_rate) - 1];
                    $last_sibling_rate = $this->price_per_hour[strval($last_sibling_hours)];

                    $discount = $last_sibling_rate * (10 / 100);
                    $total_amount = $siblings_total - $discount;
                }
            }

            return $total_amount;
        }

    }


    public function subscription_purchase_made($order_id) {
        global $wpdb; 

        $order = wc_get_order($order_id);
        $order_items = $order->get_items();

        foreach ($order_items as $order_item) {
            $item_data = $order_item->get_data(); 

            $product_id = $item_data['product_id'];

            $posts[] = get_post_meta($product_id, 'post_id', true);
        }

        $sql = 'SELECT ID FROM '.$wpdb->posts.'
                WHERE post_parent = %s';
        $where = [$order_id];
        $order_id = $wpdb->get_results(
            $wpdb->prepare( $sql, $where)
        );

        if ($posts) {
            update_post_meta($order_id[0]->ID, '_wcs_subscription_classes', $posts);

            echo '<script>
                jQuery(document).ready(function($){
                    $("thead .woocommerce-table__product-name").text("Class")
                    let rows = $("th")

                    $.each(rows, function(i, el) {
                        if($(el).text().includes("Shipping") || $(el).text().includes("Shipment")) {
                            let shippingCost = $(el).next().text()

                            if (shippingCost.includes("Free") || !/[1-9]/.test(shippingCost)) {
                                $(el).parent().remove()
                            }
                        }
                    })

                })
            </script>';
        }
    }
    
    public function apply_siblings_coupon() {
        if (is_user_logged_in()) {

            $user_id = get_current_user_id();

            $children_list = get_user_meta($user_id, 'smuac_multiaccounts_list', true);
            $children_ids = explode(',', $children_list);

            $is_not_class = false;

            foreach (WC()->cart->get_cart() as $cart_item) {
                $product_id = $cart_item['product_id'];
                $categories = get_the_terms($product_id, 'product_cat');

                foreach ($categories as $category) {
                    if ($category->slug !== 'gymnastics-classes') {
                        $is_not_class = true;
                    }
                }
            }

            if (count($children_ids) > 2 && !$is_not_class) {
                if ( ! WC()->cart->has_discount( $this->discount_coupon ) ) {
                    WC()->cart->apply_coupon( $this->discount_coupon );
                }
            }

            if ($is_not_class && WC()->cart->has_discount( $this->discount_coupon )) {
                WC()->cart->remove_coupon( $this->discount_coupon );
            }

        }
    }

    public function apply_registration_fee() {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        if (is_user_logged_in()) {

            // $users_subscribed = $this->is_subscribed();
            $user_id = get_current_user_id();

            $new_customer = get_user_meta($user_id, 'new_customer', true);

            if ($new_customer) {
                WC()->cart->add_fee('Registration fee', $this->registration_fee);
            }

        }
    }

    public function add_to_cart() {
        
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();

            // $users_subscribed = $this->is_subscribed();
            $user = get_user_by('id', $user_id);

            $cart_updated = get_user_meta($user_id, 'cart_updated', true);

            // if (!in_array($user_id, $users_subscribed) && in_array('customer', $user->roles) && !$cart_updated) {
            if (in_array('customer', $user->roles) && !$cart_updated) {
                $children_list = get_user_meta($user_id, 'smuac_multiaccounts_list', true);
                $children_ids = explode(',', $children_list);

                        foreach($children_ids as $children) {
                            $classes = get_user_meta($children, 'classes', true);
                            
                            if ($classes) {
                                if (is_array($classes[0])) {
                                    foreach($classes[0] as $class) {
                                        $selected_programs[] = $class;
                                    }
                                } else {
                                    $selected_programs[] = $classes[0];
                                }
                            }
                        }

                    if (isset($selected_programs)) {

                        $selected_programs = array_count_values($selected_programs);

                        if ( null === WC()->session ) {
                            $session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );
                        
                            WC()->session = new $session_class();
                            WC()->session->init();
                        }
                        
                        if ( null === WC()->customer ) {
                            WC()->customer = new WC_Customer( $user_id, true );
                        }
                        
                        if ( null === WC()->cart ) {
                            WC()->cart = new WC_Cart();
                            WC()->cart->get_cart();
                        }

                        foreach($selected_programs as $class => $quantity) {
                            $product_id = get_post_meta($class, 'product_id', true);


                            if ($product_id) {
                                WC()->cart->add_to_cart($product_id, $quantity);
                            }
                        }

                        update_user_meta($user_id, 'cart_updated', true);
                    }
            }
        }
    }

    
    // public function is_subscribed() {

    //     $stripe = new \Stripe\StripeClient(STRIPE_TEST_KEY);
    //     $payments = $stripe->paymentIntents->all(['limit' => 100]);

    //     $args = array('post_type' => 'shop_subscription',
    //         'post_status' => array('wc-active'),
    //     );

    //     $subscriptions = wp_list_pluck( get_posts( $args ), 'post_parent' );

    //     $users_subscribed = array();
        
    //     foreach($payments as $payment) {
            
    //         $card_saved = $payment->payment_method_options->card->mandate_options;
    //         $status = $payment->status;
            
    //         if ($card_saved && $status == 'succeeded') {
    //             $order_id = $payment->metadata->order_id;
                
    //             if (in_array($order_id, $subscriptions)) {
    //                 $order = wc_get_order($order_id);
    //                 $customer_id = $order->get_customer_id();
                    
    //                 $users_subscribed[] = $customer_id;
    //             }
    //         }
            
    //     }

    //     return $users_subscribed;
    // }

}