<?php
// Agregar contenido personalizado después del botón "Sign up now" en el checkout
add_action( 'woocommerce_review_order_after_submit', 'box_monthly' );
function box_monthly() {
	$cart = WC()->cart;
	 $recurring_total = 0;
	foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {

        $product = $cart_item['data'];
        
        
        if ( $product->is_type( 'subscription' ) ) {
            // Get the regular price of the product
            $regular_price = $product->get_regular_price();
            
            // Get the quantity of the product in the cart
            $quantity = $cart_item['quantity'];
            
            // Calculate the recurring total for this product
            $recurring_total += $regular_price * $quantity;
        }
    }
    if ( $recurring_total > 0 ) {
        echo '<div class="total-amount-box" style="border: 1px solid #ddd;border-radius: 8px;padding: 20px;margin-top: 60px">';
        echo '<p>Total amount to be paid monthly: ' . wc_price( $recurring_total ) . '</p>';
        echo '</div>';
   }
}

function hide_payment_methods_first_purchase($available_gateways) {
    // Verify if the customer has made previous purchases
    $user_id = wp_get_current_user();
    $args = array(
        'customer_id' =>$user_id->ID,
    );
    $order=wc_get_orders($args);

    if ( count($order) === 0) {
        // If this is your first purchase, disable check and cash payment methods.
        unset($available_gateways['cheque']);
        unset($available_gateways['cod']);
    }

return $available_gateways;

}
add_filter('woocommerce_available_payment_gateways', 'hide_payment_methods_first_purchase');


// Shortcode to display the password recovery form
function lost_password_shortcode() {
    ob_start();
    lost_password_form();
    return ob_get_clean();
}
add_shortcode('lost_password', 'lost_password_shortcode');


// Function to generate the password recovery form
function lost_password_form() {
    // Check if the user is already logged in.
    if (is_user_logged_in()) {
        return '<p>' . __('You are already logged in.', 'text-domain') . '</p>';
    }

    // Display the password recovery form
    ?>
    <form method="post" class= "lostpassword" action="<?php echo esc_url(wp_lostpassword_url()); ?>">
        <p>
            <label for="user_login"><?php _e('Username or Email', 'text-domain'); ?></label>
            <input type="text" name="user_login" id="user_login" class="input" value="" size="20" autocapitalize="off" />
        </p>
        <input type="hidden" name="redirect_to" value="<?php echo esc_url(site_url('/confirm-message')); ?>" />
        <p>
            <input type="submit" class="button" style="fill: #FFFFFF;color: #FFFFFF;background-color: var(--e-global-color-423fde4 );border: none;" value="<?php _e('Get New Password', 'text-domain'); ?>" />
        </p>
    </form>
    <?php
}

// Filter to modify the contents of the password recovery email
function custom_retrieve_password_message($message, $key, $user_login, $user_data) {
    $site_url = site_url('/reset-password');
    $reset_url = add_query_arg(array('action' => 'rp', 'key' => $key, 'login' => rawurlencode($user_login)), $site_url);
    
    $message = __('Someone has requested a password reset for the following account:', 'text-domain') . "\r\n\r\n";
    $message .= sprintf(__('Username: %s', 'text-domain'), $user_login) . "\r\n\r\n";
    $message .= __('If this was a mistake, just ignore this email and nothing will happen.', 'text-domain') . "\r\n\r\n";
    $message .= __('To reset your password, visit the following link:', 'text-domain') . "\r\n\r\n";
    $message .= $reset_url . "\r\n\r\n";
    $message .= __('Thanks!', 'text-domain') . "\r\n";

    return $message;
}
add_filter('retrieve_password_message', 'custom_retrieve_password_message', 10, 4);

// Filter to redirect user to custom URL after submitting password recovery request
function custom_lostpassword_redirect() {
    return site_url('/reset-password');
}
add_filter('lostpassword_redirect', 'custom_lostpassword_redirect');


function custom_reset_password_form() {
    ob_start();
        if (isset($_GET['reset_password'])) {
            if ($_GET['reset_password'] == 'invalid') {
                echo '<div class="reset-password-message error">Invalid password reset link.</div>';
            } elseif ($_GET['reset_password'] == 'success') {
                echo '<div class="reset-password-message success">Password reset successful! You can now log in with your new password.</div>';
            }
        }
        ?>
        <form method="post" class= "lostpassword" action="<?php echo esc_url(home_url('/reset-password')); ?>">
            <input type="hidden" name="key" value="<?php echo esc_attr($_REQUEST['key']); ?>" />
            <input type="hidden" name="login" value="<?php echo esc_attr($_REQUEST['login']); ?>" />

            <p>
                <label for="password"><?php _e('New Password', 'textdomain'); ?></label>
                <input type="password" name="password" id="password" />
            </p>

            <p>
                <input type="submit" name="reset_password_submit" value="<?php _e('Reset Password', 'textdomain'); ?>" class="lostpassword-button" style="fill: #FFFFFF;color: #FFFFFF;background-color: var(--e-global-color-423fde4 );border: none;"/>
            </p>
        </form>
    <?php
    return ob_get_clean();
}
add_shortcode('custom_reset_password_form', 'custom_reset_password_form');


/// Function to handle password reset
function custom_handle_password_reset() {
    if (isset($_POST['reset_password_submit']) && $_POST['reset_password_submit'] == 'Reset Password') {
        if (isset($_POST['key']) && isset($_POST['login'])) {
            $user = check_password_reset_key($_POST['key'], $_POST['login']);

            if (!$user || is_wp_error($user)) {
                wp_redirect(add_query_arg('reset_password', 'invalid',home_url('/login')));
                exit;
            }

            if (isset($_POST['password']) && $_POST['password'] != '') {
                reset_password($user, $_POST['password']);
                wp_redirect(add_query_arg('reset_password', 'success', home_url('/login')));
                exit;
            }
        }
    }
}
add_action('init', 'custom_handle_password_reset');

function get_customer_transactions($user_id) {
    global $wpdb;
    $transactions = array();
    $table = ' <table class="gy-table user_balance_table custom-table responsive_table">';
    $table .= '<thead>
                <tr>
                    <th class="date" >Date</th>
                    <th>Item</th>
                    <th>Description</th>
                    <th class="credit">Credits (+$)</th>
                    <th class="debit">Debits (-$)</th>
					<th class="combined">Amount</th>
                    <th>Balance</th>
                </tr>
                </thead>
                <tbody>';
    $credit = 0;
    
    $refunds = wc_get_orders(array(
        'customer_id' => $user_id,
        'status'      => array('processing', 'refunded'),
        'limit' => -1,
    ));
    $order_ids = array(); 

    foreach ($refunds as $order) {
        $order_ids[] = $order->get_id();
    }
    $orders = implode(',',$order_ids);

    if (!empty($orders)) {
        $sql = "SELECT id, post_title
                FROM {$wpdb->prefix}posts 
                WHERE post_parent IN ( $orders) AND post_type = 'shop_order_refund'";
        $refund = $wpdb->get_results($sql);
        
        if (!empty($refund)) {
            
            foreach ($refund as $id) {
                $order = wc_get_order($id->id);
                $type_refund = get_post_meta($order->get_id(), '_type_refund', true);
    
                if ($type_refund == 'credit') {
                    $transactions[] = array(
                        'order_id' => $order->get_id(),
                        'fecha' => $order->get_date_created(),
                        'item' => 'Order #' . $order->get_id(),
                        'descripcion' => $id->post_title,
                        'credito' =>  $order->get_amount(),
                        'debito' => 0,
                        'balance' => 0
                    );
                } else {
                    $transactions[] = array(
                        'order_id' => $order->get_id(),
                        'fecha' => $order->get_date_created(),
                        'item' => 'Order #' . $order->get_id(),
                        'descripcion' => $id->post_title,
                        'credito' =>  0,
                        'debito' => $order->get_amount(),
                        'balance' => 0
                    );
                }
    
            }
        }
    }

    $customer_orders = wc_get_orders(array(
        'customer_id' => $user_id,
        'status'      => array('completed', 'processing'),
        'limit' => -1,
    ));

    foreach ($customer_orders as $order) {
        // Get the name of the order item
        $items = $order->get_items();
        $fees = $order->get_fees();

        foreach ($items as $item_id => $item_data) {
            $product = $item_data->get_product();
            if ($product) {
                $item_name = $item_data->get_name();
                $item_total = floatval($item_data->get_total()); // Get the total amount for this item
                
                $credit_meta = wc_get_order_item_meta($item_id, '_credit_editable', true);

                if ($credit_meta !== '') {
                    // If the meta exists, use its value as credit
                    $credit = floatval($credit_meta);
                } else {
                    $credit = $item_total;
                }
                $transactions[] = array(
                    'fecha' => $order->get_date_created(),
                    'item' => 'Order #' . $order->get_id(),
                    'descripcion' => $item_name,
                    'credito' => $credit,
                    'debito' => 0,
                    'balance' => 0
                );
            }
        }

        foreach ($fees as $fee_id => $fee_data) {
            $item_total = floatval($fee_data->get_total());
            // Check if the _debit_editable meta exists for the product
            $credit_meta = wc_get_order_item_meta($fee_id, '_credit_editable', true);
            //$descrip_meta = wc_get_order_item_meta($item_id, '_description_editable'. $invoice->comment_id, true);
            if ($credit_meta !== '') {
                // If the meta exists, use its value as debit
                $credit = floatval($credit_meta);
            } else {
                $credit = $item_total;
            }
            $item_name = $fee_data->get_name();

            $transactions[] = array(
                'fecha' => $order->get_date_created(),
                'item' => 'Order #' . $order->get_id(),
                'descripcion' => $item_name,
                'credito' => $credit,
                'debito' => 0,
                'balance' => 0
            );  
        }
    }

    // Obtain invoice-related comments from the customer
    $subscriptions = wcs_get_subscriptions(array('customer_id' => $user_id, 'limit' => -1));
    foreach ($subscriptions as $subscription) {
        $subs_id = $subscription->get_id();
        $sql = "SELECT comment_id, comment_date, comment_content 
                FROM {$wpdb->comments} 
                WHERE comment_content LIKE '%Invoice%' 
                AND comment_author = '{$subs_id}' 
                AND comment_approved = 1;";
        $customer_invoice = $wpdb->get_results($sql);

        foreach ($customer_invoice as $invoice) {
            $is_due = false;
            $due_date = get_comment_meta($invoice->comment_id, 'due_date', true);
            
            if (!empty($due_date)) {
                if (date('Y-m-d') >= $due_date) {
                    $is_due = true;
                }
            } else {
                $is_due = true;
            }
            // Get the name of the order item associated with the invoice
          /*   $order_id = $subscription->get_parent_id(); // Get the order ID associated with the subscription
            $order = wc_get_order($order_id); */
            $item_name = '';
            
            if ($subscription && $is_due) {
                $fees = $subscription->get_fees();
                $items = $subscription->get_items();
                foreach ($items as $item_id => $item_data) {
                    $product = $item_data->get_product();
                    if ($product) {
                        $item_total = floatval($item_data->get_total()); // Get the total amount for this item
                        $debit_meta_key = '_debit_editable' . $invoice->comment_id;
                        // Check if the _debit_editable meta exists for the product
                        $debit_meta = wc_get_order_item_meta($item_id, $debit_meta_key, true);
                        //$descrip_meta = wc_get_order_item_meta($item_id, '_description_editable'. $invoice->comment_id, true);
                        if ($debit_meta !== '') {
                            // If the meta exists, use its value as debit
                            $debit = floatval($debit_meta);
                        } else {
                            $debit = $item_total;
                        }
                        $item_name = $item_data->get_name();
                        
                        $transactions[] = array(
                            'fecha' => $invoice->comment_date,
                            'item' => 'Invoice #' . $invoice->comment_id,
                            'descripcion' => $item_name,
                            'credito' => 0,
                            'debito' => $debit
                        );  
                    }
                }

                foreach ($fees as $fee_id => $fee_data) {
                    $item_total = floatval($fee_data->get_total()); // Get the total amount for this item
                    $debit_meta_key = '_debit_editable' . $invoice->comment_id;
                    // Check if the _debit_editable meta exists for the product
                    $debit_meta = wc_get_order_item_meta($fee_id, $debit_meta_key, true);
                    //$descrip_meta = wc_get_order_item_meta($item_id, '_description_editable'. $invoice->comment_id, true);
                    if ($debit_meta !== '') {
                        // If the meta exists, use its value as debit
                        $debit = floatval($debit_meta);
                    } else {
                        $debit = $item_total;
                    }
                    $item_name = $fee_data->get_name();

                    $transactions[] = array(
                        'fecha' => $invoice->comment_date,
                        'item' => 'Invoice #' . $invoice->comment_id,
                        'descripcion' => $item_name,
                        'credito' => 0,
                        'debito' => $debit
                    );  
                }
            }
        }
    }
    
    $orders = wc_get_orders(array('customer_id' => $user_id, 'limit' => -1));
    foreach ($orders as $order) {
        $order_id = $order->get_id();
        $sql = "SELECT comment_id, comment_date, comment_content
                FROM {$wpdb->comments}
                WHERE comment_content LIKE '%Invoice%'
                AND comment_author = '{$order_id}'
                AND comment_approved = 1;";
        $customer_invoice = $wpdb->get_results($sql);
        foreach ($customer_invoice as $invoice) {
            $is_due = false;
            $due_date = get_comment_meta($invoice->comment_id, 'due_date', true);
            
            if (!empty($due_date)) {
                if (date('Y-m-d') >= $due_date) {
                    $is_due = true;
                }
            } else {
                $is_due = true;
            }
            
            if ($is_due) {
                // Get the name of the order item associated with the invoice
                $items = $order->get_items();
                $fees = $order->get_fees();

                foreach ($items as $item_id => $item_data) {
                    $product = $item_data->get_product();
                    if ($product) {
                        $item_total = floatval($item_data->get_total()); // Get the total amount for this item
                        $debit_meta_key = '_debit_editable' . $invoice->comment_id;
                       // $descrip_meta = wc_get_order_item_meta($item_id, '_description_editable'. $invoice->comment_id, true);
                
                        // Check if the _debit_editable meta exists for the product
                        $debit_meta = wc_get_order_item_meta($item_id, $debit_meta_key, true);
                        if ($debit_meta !== '') {
                            // If the meta exists, use its value as debit
                            $debit = floatval($debit_meta);
                        } else {
                            $debit = $item_total;
                        }
                        $item_name = $item_data->get_name();
    
                        $transactions[] = array(
                            'fecha' => $invoice->comment_date,
                            'item' => 'Invoice #' . $invoice->comment_id,
                            'descripcion' => $item_name,
                            'credito' => 0,
                            'debito' => $debit
                        );  
                    }
                }

                foreach ($fees as $fee_id => $fee_data) {
                    $item_total = floatval($fee_data->get_total());
                    // Check if the _debit_editable meta exists for the product
                    $debit_meta_key = '_debit_editable' . $invoice->comment_id;
                    $debit_meta = wc_get_order_item_meta($fee_id, $debit_meta_key, true);
                    //$descrip_meta = wc_get_order_item_meta($item_id, '_description_editable'. $invoice->comment_id, true);
                    if ($debit_meta !== '') {
                        // If the meta exists, use its value as debit
                        $debit = floatval($debit_meta);
                    } else {
                        $debit = $item_total;
                    }
                    $item_name = $fee_data->get_name();

                    $transactions[] = array(
                        'fecha' => $invoice->comment_date,
                        'item' => 'Invoice #' . $invoice->comment_id,
                        'descripcion' => $item_name,
                        'credito' => 0,
                        'debito' => $debit
                    );  
                }
            }
        }
    }
    usort($transactions, function($a, $b) {
        // First, compare the dates
        $dateComparison = strtotime($a['fecha']) - strtotime($b['fecha']);
    
         // If the dates are different, return the date comparison
        if ($dateComparison !== 0) {
            return $dateComparison;
        }
    
        // If the dates are the same, compare description
        return strcmp($a['descripcion'], $b['descripcion']);
    });

    // Calculate the cumulative balance
    $balance = 0;
    $transactions_with_balance = array();
    foreach ($transactions as $transaction) {
        $balance += $transaction['credito'] - $transaction['debito'];
        
        if ($balance < 0) {
            $formatted_balance = '-' . wc_price(abs($balance));
        } else {
            $formatted_balance = wc_price($balance);
        }
        
        $transaction['balance'] = $formatted_balance;
        $transaction['not_formatted_balance'] = $balance;
        $transactions_with_balance[] = $transaction;
    }

    usort($transactions_with_balance, function($a, $b) {
        // First, compare the dates
        $dateComparison = strtotime($b['fecha']) - strtotime($a['fecha']);
    
        // If the dates are different, return the date comparison.
        if ($dateComparison !== 0) {
            return $dateComparison;
        }

        if ($a['descripcion'] == $b['descripcion'] || $a['item'] == $b['item']) {
            return $a['not_formatted_balance'] - $b['not_formatted_balance'];
        }
    
        // If the dates are the same, compare description
        return strcmp($b['descripcion'], $a['descripcion']);
    });

    // Create the table with transactions within the specified range
    foreach ($transactions_with_balance  as $key => $transaction) {
        $table .= '<tr class="original-row row-' . $key . '">';
        $table .= '<td class="rsp-date">' . date("Y-m-d H:i:s", strtotime($transaction['fecha'])) . '</td>';
        $table .= '<td>' . $transaction['item'] . '</td>';
        $table .= '<td id="description'.$key.'">' . $transaction['descripcion'] . '</td>';
        $table .= '<td id="credit'.$key.'">' . wc_price(abs($transaction['credito'])) . '</td>';
        $table .= '<td class="debit" >' . wc_price(abs($transaction['debito'])) . '</td>';
        $table .= '<td class="combined">';
		if($transaction['debito'] !== 0) {
			$table .= '-' . wc_price(abs($transaction['debito']));
		}
		elseif($transaction['credito'] !== 0) {
			$table .= '+' . wc_price(abs($transaction['credito']));
		}
		else {
			$table .= wc_price(0.00);
		}
		$table .= '</td>';
        $table .= '<td class="balance">' . $transaction['balance'] . '</td>';
        $table .= '</tr>';
    }
    $table .= '</tbody>
                </table>';

    return $table;
}

function get_customer_transactions_edit($user_id) {
    global $wpdb;
    $transactions = array();
    $table = ' <table class="gy-table user_balance_table custom-table responsive_table" id="balance_table">';
    $table .= '<thead>
                <tr>
                    <th>Date</th>
                    <th>Item</th>
                    <th>Description</th>
                    <th>Credits (+$)</th>
                    <th>Debits (-$)</th>
                    <th>Balance</th>
                    <th colspan="2">Action</th>
                </tr>
                </thead>
                <tbody>';
    $credit = 0;
    $refunds = wc_get_orders(array(
        'customer_id' => $user_id,
        'status'      => array('processing', 'refunded'),
        'limit' => -1,
    ));
    $order_ids = array(); 

    foreach ($refunds as $order) {
        $order_ids[] = $order->get_id();
    }
    $orders = implode(',',$order_ids);

    if (!empty($orders)) {
        $sql = "SELECT id, post_title
                FROM {$wpdb->prefix}posts 
                WHERE post_parent IN ( $orders) AND post_type = 'shop_order_refund'";
        $refund = $wpdb->get_results($sql);
        
        if (!empty($refund)) {
            
            foreach ($refund as $id) {
                $order = wc_get_order($id->id);
                $type_refund = get_post_meta($order->get_id(), '_type_refund', true);
    
                if ($type_refund == 'credit') {
                    $transactions[] = array(
                        'order_id' => $order->get_id(),
                        'fecha' => $order->get_date_created(),
                        'item' => 'Order #' . $order->get_id(),
                        'descripcion' => $id->post_title,
                        'credito' =>  $order->get_amount(),
                        'debito' => 0,
                        'balance' => 0
                    );
                } else {
                    $transactions[] = array(
                        'order_id' => $order->get_id(),
                        'fecha' => $order->get_date_created(),
                        'item' => 'Order #' . $order->get_id(),
                        'descripcion' => $id->post_title,
                        'credito' =>  0,
                        'debito' => $order->get_amount(),
                        'balance' => 0
                    );
                }
    
            }
        }
    }

    $customer_orders = wc_get_orders(array(
        'customer_id' => $user_id,
        'status'      => array('completed', 'processing'),
        'limit' => -1,
    ));
    
    foreach ($customer_orders as $order) {
        // Get the name of the order item
        $items = $order->get_items();
        $fees = $order->get_fees();

        foreach ($items as $item_id => $item_data) {
            $product = $item_data->get_product();
            if ($product) {
                $item_name = $item_data->get_name();
                $item_total = floatval($item_data->get_total()); // Get the total amount for this item
                
                $credit_meta = wc_get_order_item_meta($item_id, '_credit_editable', true);

                if ($credit_meta !== '') {
                    // If the meta exists, use its value as credit
                    $credit = floatval($credit_meta);
                } else {
                    $credit = $item_total;
                }
                $transactions[] = array(
                    'order_id' => $order->get_id(),
                    'fecha' => $order->get_date_created(),
                    'item' => 'Order #' . $order->get_id(),
                    'descripcion' => $item_name,
                    'credito' => $credit,
                    'debito' => 0,
                    'balance' => 0,
                    'item_id' => $item_id
                );
            }
        }

        foreach ($fees as $fee_id => $fee_data) {
            $item_total = floatval($fee_data->get_total());
            // Check if the _debit_editable meta exists for the product
            $credit_meta = wc_get_order_item_meta($fee_id, '_credit_editable', true);
            //$descrip_meta = wc_get_order_item_meta($item_id, '_description_editable'. $invoice->comment_id, true);
            if ($credit_meta !== '') {
                // If the meta exists, use its value as debit
                $credit = floatval($credit_meta);
            } else {
                $credit = $item_total;
            }
            $item_name = $fee_data->get_name();

            $transactions[] = array(
                'order_id' => $order->get_id(),
                'fecha' => $order->get_date_created(),
                'item' => 'Order #' . $order->get_id(),
                'descripcion' => $item_name,
                'credito' => $credit,
                'debito' => 0,
                'balance' => 0,
                'item_id' => $fee_id
            );  
        }
    }

    // Obtain invoice-related comments from the customer
    $subscriptions = wcs_get_subscriptions(array('customer_id' => $user_id, 'limit' => -1));
    foreach ($subscriptions as $subscription) {
        $subs_id = $subscription->get_id();
        $sql = "SELECT comment_id, comment_date, comment_content 
                FROM {$wpdb->comments} 
                WHERE comment_content LIKE '%Invoice%' 
                AND comment_author = '{$subs_id}' 
                AND comment_approved = 1;";
        $customer_invoice = $wpdb->get_results($sql);
 
        foreach ($customer_invoice as $invoice) {
            $is_due = false;
            $due_date = get_comment_meta($invoice->comment_id, 'due_date', true);
            
            if (!empty($due_date)) {
                if (date('Y-m-d') >= $due_date) {
                    $is_due = true;
                }
            } else {
                $is_due = true;
            }

            // Get the name of the order item associated with the invoice
          /*   $order_id = $subscription->get_parent_id(); // Get the order ID associated with the subscription
            $order = wc_get_order($order_id); */
            $item_name = '';
            if ($subscription && $is_due) {
                $fees = $subscription->get_fees();
                $items = $subscription->get_items();
                foreach ($items as $item_id => $item_data) {
                    $product = $item_data->get_product();
                    if ($product) {
                        $item_total = floatval($item_data->get_total()); // Get the total amount for this item
                        $debit_meta_key = '_debit_editable' . $invoice->comment_id;
                        // Check if the _debit_editable meta exists for the product
                        $debit_meta = wc_get_order_item_meta($item_id, $debit_meta_key, true);
                        //$descrip_meta = wc_get_order_item_meta($item_id, '_description_editable'. $invoice->comment_id, true);
                        if ($debit_meta !== '') {
                            // If the meta exists, use its value as debit
                            $debit = floatval($debit_meta);
                        } else {
                            $debit = $item_total;
                        }
                        $item_name = $item_data->get_name();

                        $transactions[] = array(
                            'invoice_id' => $invoice->comment_id,
                            'order_id' => $subs_id,
                            'fecha' => $invoice->comment_date,
                            'item' => 'Invoice #' . $invoice->comment_id,
                            'descripcion' => $item_name,
                            'credito' => 0,
                            'debito' => $debit,
                            'item_id' => $item_id
                        );  
                    }
                }
                foreach ($fees as $fee_id => $fee_data) {
                    $item_total = floatval($fee_data->get_total()); // Get the total amount for this item
                    $debit_meta_key = '_debit_editable' . $invoice->comment_id;
                    // Check if the _debit_editable meta exists for the product
                    $debit_meta = wc_get_order_item_meta($fee_id, $debit_meta_key, true);
                    //$descrip_meta = wc_get_order_item_meta($item_id, '_description_editable'. $invoice->comment_id, true);
                    if ($debit_meta !== '') {
                        // If the meta exists, use its value as debit
                        $debit = floatval($debit_meta);
                    } else {
                        $debit = $item_total;
                    }
                    $item_name = $fee_data->get_name();

                    $transactions[] = array(
                        'invoice_id' => $invoice->comment_id,
                        'order_id' => $subs_id,
                        'fecha' => $invoice->comment_date,
                        'item' => 'Invoice #' . $invoice->comment_id,
                        'descripcion' => $item_name,
                        'credito' => 0,
                        'debito' => $debit,
                        'item_id' => $fee_id,
                    );  
                }
            }
        }
    }
    
    $orders = wc_get_orders(array('customer_id' => $user_id, 'limit' => -1));

    foreach ($orders as $order) {
        $order_id = $order->get_id();
        $sql = "SELECT comment_id, comment_date, comment_content
                FROM {$wpdb->comments}
                WHERE comment_content LIKE '%Invoice%'
                AND comment_author = '{$order_id}'
                AND comment_approved = 1;";
        $customer_invoice = $wpdb->get_results($sql);
        foreach ($customer_invoice as $invoice) {
            $is_due = false;
            $due_date = get_comment_meta($invoice->comment_id, 'due_date', true);

            if (!empty($due_date)) {
                if (date('Y-m-d') >= $due_date) {
                    $is_due = true;
                }
            } else {
                $is_due = true;
            }
            // Get the name of the order item associated with the invoice

            if ($is_due) {
                $items = $order->get_items();
                $fees = $order->get_fees();

                foreach ($items as $item_id => $item_data) {
                    $product = $item_data->get_product();
                    if ($product) {
                        $item_total = floatval($item_data->get_total()); // Get the total amount for this item
                        $debit_meta_key = '_debit_editable' . $invoice->comment_id;
                    // $descrip_meta = wc_get_order_item_meta($item_id, '_description_editable'. $invoice->comment_id, true);
                
                        // Check if the _debit_editable meta exists for the product
                        $debit_meta = wc_get_order_item_meta($item_id, $debit_meta_key, true);
                        if ($debit_meta !== '') {
                            // If the meta exists, use its value as debit
                            $debit = floatval($debit_meta);
                        } else {
                            $debit = $item_total;
                        }
                        $item_name = $item_data->get_name();
                    
                        $transactions[] = array(
                            'invoice_id' => $invoice->comment_id,
                            'order_id' => $order_id,
                            'fecha' => $invoice->comment_date,
                            'item' => 'Invoice #' . $invoice->comment_id,
                            'descripcion' => $item_name,
                            'credito' => 0,
                            'debito' => $debit,
                            'item_id' => $item_id
                        );  
                    }
                }

                foreach ($fees as $fee_id => $fee_data) {
                    $item_total = floatval($fee_data->get_total());
                    // Check if the _debit_editable meta exists for the product
                    $debit_meta_key = '_debit_editable' . $invoice->comment_id;
                    $debit_meta = wc_get_order_item_meta($fee_id, $debit_meta_key, true);
                    //$descrip_meta = wc_get_order_item_meta($item_id, '_description_editable'. $invoice->comment_id, true);
                    if ($debit_meta !== '') {
                        // If the meta exists, use its value as debit
                        $debit = floatval($debit_meta);
                    } else {
                        $debit = $item_total;
                    }
                    $item_name = $fee_data->get_name();

                    $transactions[] = array(
                        'invoice_id' => $invoice->comment_id,
                        'order_id' => $order_id,
                        'fecha' => $invoice->comment_date,
                        'item' => 'Invoice #' . $invoice->comment_id,
                        'descripcion' => $item_name,
                        'credito' => 0,
                        'debito' => $debit,
                        'item_id' => $fee_id
                    );  
                }
            }
        }
    }

    usort($transactions, function($a, $b) {
        // First, compare the dates
        $dateComparison = strtotime($a['fecha']) - strtotime($b['fecha']);
    
         // If the dates are different, return the date comparison
        if ($dateComparison !== 0) {
            return $dateComparison;
        }
    
        // If the dates are the same, compare description
        return strcmp($a['descripcion'], $b['descripcion']);
    });

    // Calculate the cumulative balance
    $balance = 0;
    $transactions_with_balance = array();
    foreach ($transactions as $transaction) {
        $balance += $transaction['credito'] - $transaction['debito'];
        
        if ($balance < 0) {
            $formatted_balance = '-' . wc_price(abs($balance));
        } else {
            $formatted_balance = wc_price($balance);
        }
        
        $transaction['balance'] = $formatted_balance;
        $transaction['not_formatted_balance'] = $balance;
        $transactions_with_balance[] = $transaction;
    }

    usort($transactions_with_balance, function($a, $b) {
        // First, compare the dates
        $dateComparison = strtotime($b['fecha']) - strtotime($a['fecha']);
    
        // If the dates are different, return the date comparison.
        if ($dateComparison !== 0) {
            return $dateComparison;
        }

        if ($a['descripcion'] == $b['descripcion'] || $a['item'] == $b['item']) {
            return $a['not_formatted_balance'] - $b['not_formatted_balance'];
        }
    
        // If the dates are the same, compare description
        return strcmp($b['descripcion'], $a['descripcion']);
    });

    // Create the table with transactions within the specified range
    
    foreach ($transactions_with_balance  as $key => $transaction) {
        $table .= '<tr class="original-row row-' . $key . '" data-table="#balance_table" data-key="'.$key.'">';
        $table .= '<td style="position:relative;">' . date("Y-m-d H:i:s", strtotime($transaction['fecha'])) . '
                    <div id="hover-'. $key .'" class="hover_modal">
                        <p>'. (isset($transaction['invoice_id']) ? get_invoice_table($transaction['invoice_id']) : '') .'</p>
                        <div class="flex-container resend_invoice_item">
                            <button type="button" class="easy-pos-btn" id="resend-invoice'.$key.'"
                            data-item="'.$transaction['invoice_id'].'" data-row="row-'.$key.'"
                            style="
                                font-size: 13px;
                                height: 30px;
                                text-align: center;
                                padding: revert;
                                ">Resend Invoice</button>
                            <svg class="hidden resend-invoice-success" viewBox="0 0 24 24" width="25px" height="25px"fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#50db06"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M4 12.6111L8.92308 17.5L20 6.5" stroke="#2de208" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
                        </div>
                    </div>
                    <div id="payment-hover-'. $key .'" data-table="#balance_table" class="payment_modal">
                        <ul>'.description_comment($transaction['order_id']).'</ul>
                    </div>
                    </td>';
        $table .= '<td class="item-'. $key .'" data-table="#balance_table" style="position:relative;">' . $transaction['item'] . '</td>';
        $table .= '<td id="description'.$key.'" data-table="#balance_table" class="description-'.$key.'">' . $transaction['descripcion'] . '</td>';
        $table .= '<td id="credit'.$key.'">' . wc_price(abs($transaction['credito'])) . '</td>';
        $table .= '<td id="debit'.$key.'">' . wc_price(abs($transaction['debito'])) . '</td>';
        $table .= '<td>' . $transaction['balance'] . '</td>';
        $table .= '<td class="edit-button" data-row="row-' . $key . '" data-table="#balance_table">
                    <svg xmlns="http://www.w3.org/2000/svg" 
                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                    style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                </td>';
        if (current_user_can('administrator')) {
            $table .='<td class="delete-btn" data-item="'.$transaction['item_id'].'" data-row="row-' . $key . '" data-table="#balance_table">
                        <svg xmlns="http://www.w3.org/2000/svg" 
                        fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                        style="width: 24px; height: 24px; color:red;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </td>';
        }
        $table .= '</tr>';
        $table .= '<tr class="editable-row row-' . $key . '" data-row="row-' . $key . '" data-table="#balance_table" style="display: none;">';
        $table .= '<td>'.esc_attr($transaction['fecha']).'</td>';
        $table .= '<td id="item'.$key.'">'.esc_attr($transaction['item']).'</td>';
        $table .= '<td><input type="text" class="edit-description" value="' . esc_attr($transaction['descripcion']) . '"></td>';
        $table .= '<td><input type="number" class="edit-credit" style="width: 75px;" value="' . esc_attr($transaction['credito']) . '"></td>';
        $table .= '<td><input type="number" class="edit-debit" style="width: 75px;" value="' . esc_attr($transaction['debito']) . '"></td>';
        $table .= '<td>' . $transaction['balance'] . '</td>';
        $table .= '<td>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                class="cancel-button" data-table="#balance_table" style="width: 24px; height: 24px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
            class="save-button" data-table="#balance_table" style="width: 24px; height: 24px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg></td>';
        $table .= '</tr>';
    }

    $table .= '</tbody>
                </table>';
    
    return $table;
}

function custom_checkout_field_text( $translated_text, $text, $domain ) {
    if ( $text === 'Subtotal' ) {
        return 'Prorated Joining Month';
    }
    return $translated_text;
}
add_filter( 'gettext', 'custom_checkout_field_text', 20, 3 );


function hide_shipping_on_zero_amount() {
    // Get shipping amount from cart
    $shipping_total = WC()->cart->get_shipping_total();

    // Check if the shipping amount is equal to zero
    if (floatval($shipping_total) == 0) {
        echo '<style type="text/css">
            tr.woocommerce-shipping-totals.shipping {
                display: none !important;
            }
        </style>';
    }
}
add_action('woocommerce_review_order_after_shipping', 'hide_shipping_on_zero_amount');

add_filter( 'woocommerce_checkout_fields', 'change_order_notes_label' );
function change_order_notes_label( $fields ) {
    $fields['order']['order_comments']['label'] = 'Anything you would like us to know about your child or your child\'s history with gymnastics?';
    return $fields;
}


// modal add new parent

add_action('wp_ajax_create_new_parent', 'create_new_parent');
add_action('wp_ajax_nopriv_create_new_parent', 'create_new_parent'); 
add_action('wp_ajax_delete_category', 'delete_category'); 
add_action('wp_ajax_update_category', 'update_category'); 
add_action('wp_ajax_get_order_details', 'get_order_details'); 
add_action('wp_ajax_get_slots_by_class', 'get_slots_by_class'); 
add_action('wp_ajax_save_tag_to_athlete', 'save_tag_to_athlete'); 
add_action('wp_ajax_delete_athlete_tag', 'delete_athlete_tag'); 
add_action('wp_ajax_save_new_athlete_tag', 'save_new_athlete_tag');
add_action('wp_ajax_get_athlete_tags_count', 'get_athlete_tags_count');
add_action('wp_ajax_update_notifications_status', 'update_notifications_status');
add_action ('wp_ajax_update_notifications_status', 'update_notifications_status');
add_action('wp_ajax_get_athletes_with_tags', 'get_athletes_with_tags');

function get_athletes_with_tags() {
    global $wpdb;

    $sql = 'SELECT ID, user_email, pm3.meta_value AS first_name, pm4.meta_value AS last_name
            FROM wp_usermeta pm1
            JOIN wp_usermeta pm2
                ON pm1.user_id = pm2.user_id
                AND pm1.meta_key = "athlete_tags"
                AND pm2.meta_key = "smuac_account_parent"
                '.(isset($_GET['tag']) ? ' AND pm1.meta_value LIKE "%'.$_GET['tag'].'%"' : '').'
            JOIN wp_usermeta pm3
                ON pm2.meta_value = pm3.user_id
                AND pm3.meta_key = "first_name"
            JOIN wp_usermeta pm4
                ON pm3.user_id = pm4.user_id
                AND pm4.meta_key = "last_name"
            JOIN wp_users
                ON ID = pm4.user_id';
    
    $results = $wpdb->get_results($sql);
    $html = '';

    foreach($results as $result) {
        $html .= '<label><input type="checkbox" name="selected_users_tags[]" value="'.$result->user_email.'" checked>'.$result->first_name.' '.$result->last_name.'</label><br>';
    }

    echo json_encode($html);
    die();
}

function get_athlete_tags_count() {
    global $wpdb;

    $terms = get_terms( 'athlete_tags', array(
        'orderby'           => 'name', 
        'order'             => 'ASC',
        'hide_empty'        => false, 
        'fields'            => 'all', 
        'hierarchical'      => true, 
        'child_of'          => 0,
        'childless'         => false,
        'pad_counts'        => false, 
        'cache_domain'      => 'core',
        'limit' => -1
    ) );

    $sql = 'SELECT * FROM wp_usermeta WHERE meta_key = "athlete_tags" AND meta_value LIKE %s';

    foreach($terms as $term) {
        $results = $wpdb->get_results($wpdb->prepare($sql, ["%$term->term_id%"]));

        $tags_count[$term->name] = array('athlete_count' => (!empty($results) ? count($results) : 0),
                                'term_id' => $term->term_id);
    }

    echo json_encode($tags_count);

    die();
}


function update_notifications_status() {
    global $wpdb;
    
    if (isset($_POST['notifications'])) {
        $notifications = $_POST['notifications'];
        
        foreach($notifications as $notif) {
            $sql = 'UPDATE wp_commentmeta SET meta_value = 1 WHERE meta_key = "is_read" AND comment_id = '.$notif;
            $wpdb->query($sql);
        }

        echo json_encode(1);
    }

    die();
}

function get_athlete_tags($is_option = false) {
    $terms = get_terms( 'athlete_tags', array(
        'orderby'           => 'name', 
        'order'             => 'ASC',
        'hide_empty'        => false, 
        'fields'            => 'all', 
        'hierarchical'      => true, 
        'child_of'          => 0,
        'childless'         => false,
        'pad_counts'        => false, 
        'cache_domain'      => 'core',
        'limit' => -1
    ) );

    $html = '';

    foreach($terms as $term) {
        if ($is_option) {
            $html .= '<li data-id="'.$term->term_id.'">'.$term->name.'</li>';
        } else {
            $html .= '<option value="'.$term->term_id.'">'.$term->name.'</option>';
        }
    }
    
    return $html;
}

function save_new_athlete_tag() {
    if (isset($_GET['name']) && isset($_GET['user'])) {
        $tag = $_GET['name'];
        $user = $_GET['user'];

        $is_tag = get_term_by('name', $tag);

        if (empty($is_tag)) {
            $tag = wp_insert_term(
                $tag,
                'athlete_tags'
            );

            $tag_id = $tag['term_id'];
        } else {
            $tag_id = $is_tag->term_id;
        }

        echo json_encode(save_athlete_tag($user, $tag_id));
    } 

    die();
}

function delete_athlete_tag() {
    if (isset($_GET['id']) && isset($_GET['user'])) {
        $tag = $_GET['id'];
        $user = $_GET['user'];

        $athlete_tags = get_user_meta($user, 'athlete_tags', true);
        $athlete_tags = explode(',', $athlete_tags);

        $new_athlete_tags = '';

        foreach($athlete_tags as $tag_id) {
            if (!empty($tag_id)) {
                if ($tag !== $tag_id) {
                    $new_athlete_tags .= $tag_id. ',';
                }
            }
        }
    
        update_user_meta($user, 'athlete_tags', $new_athlete_tags);
        echo json_encode(1);
    } 

    die();
}

function save_athlete_tag($user, $tag) {
    $athlete_tags = get_user_meta($user, 'athlete_tags', true);

    if (!str_contains($athlete_tags, $tag)) {
        if (!empty($athlete_tags) ) {
            $athlete_tags .= $tag .',';
        } else {
            $athlete_tags = $tag .',';
        }

        update_user_meta($user, 'athlete_tags', $athlete_tags);
        return $tag;
    } else {
        return 0;
    }
}

function save_tag_to_athlete() {
    if (isset($_GET['id']) && isset($_GET['user'])) {
        $tag = $_GET['id'];
        $user = $_GET['user'];

        echo json_encode(save_athlete_tag($user, $tag));
    } 

    die();
}

add_action('wp_ajax_get_all_slots', 'get_all_slots'); 
add_action('wp_ajax_get_multiselect_slots', 'get_multiselect_slots'); 
add_action('wp_ajax_enroll_athlete', 'enroll_athlete'); 

function enroll_athlete() {
    if (isset($_GET['programs']) && isset($_GET['slots']) && isset($_GET['athleteId'])) {
        global $wpdb;

        $current_slots = get_user_meta( $_GET['athleteId'], 'classes_slots', true );

        if (!empty($_GET['programs']) && !empty($_GET['slots'])) {
            $selected_programs = explode(',', $_GET['programs']);
            $selected_slots = explode(',', $_GET['slots']);

            $slot_ids = [];
            $sql = 'SELECT post_id FROM wp_postmeta WHERE meta_value LIKE %s';
            foreach($selected_slots as $slot) {
                $where = ["%$slot%"];

                $results = $wpdb->get_results($wpdb->prepare($sql, $where));
                $slot_ids[$results[0]->post_id][] = $slot;
            }

            if (!empty($current_slots)) {
                add_enrollment_note( $current_slots[0], $slot_ids, 'Leaving ');
                add_enrollment_note( $slot_ids, $current_slots[0], 'Enrolled in ');
            } else {
                add_enrollment_note( $slot_ids, [], 'Enrolled in ');
            }

            update_user_meta( $_GET['athleteId'], 'classes', array($selected_programs) );
            update_user_meta( $_GET['athleteId'], 'classes_slots', array($slot_ids) );
            update_user_meta( $_GET['athleteId'], 'slots', array($selected_slots) );

        } else if (empty($_GET['programs']) && empty($_GET['slots'])) {

            if (!empty($current_slots)) {
                add_enrollment_note( $current_slots[0], [], 'Leaving ');
            }
            
            update_user_meta( $_GET['athleteId'], 'classes', '' );
            update_user_meta( $_GET['athleteId'], 'classes_slots', '' );
            update_user_meta( $_GET['athleteId'], 'slots', '' );
        }
    }

    echo json_encode(1);
    die();
}

function add_enrollment_note($array, $haystack, $type) {
    $admin = wp_get_current_user();
    $parent_id = get_user_meta($_GET['athleteId'], 'smuac_account_parent', true);

    foreach($array as $key => $class) {
        if (!isset($haystack[$key])) {
            foreach($class as $program) {
                $ids = get_post_meta($key, 'slot_ids', true);
                $index = array_search( $program, $ids );
                $post = get_post($key);

                if ($type == 'Leaving ') {
                    if (in_array($program, $array[$key])) {
                        $note = $type .$post->post_title.' SLOT #'.($index + 1);
                    }
                } else {
                    $note = $type .$post->post_title.' SLOT #'.($index + 1);
                }
                
            }
        }
    }

    if (isset($note)) {
        $comment_user = array(
            'comment_author' => $admin->display_name,
            'comment_content' => $note,
            'user_id' => $parent_id,
            'comment_meta'         => array(
                'is_customer_note'       => sanitize_text_field(1),
                )
            );

        wp_insert_comment($comment_user);
    }

}

function get_slots_by_class($class) {
    global $wpdb;
    $slot_week = ['_slot_time_monday' => 'MON', '_slot_time_tuesday' => 'TUE', '_slot_time_wednesday' => 'WED', '_slot_time_thursday' => 'THU', '_slot_time_friday' => 'FRI', '_slot_time_saturday' => 'SAT', '_slot_time_sunday' => 'SUN'];

    $slot_ids = get_post_meta($class, 'slot_ids', true);
    $dd_classes = [];

    if (!empty($slot_ids)) {
        foreach ($slot_ids as $slot) {
            $sql = 'SELECT * FROM wp_postmeta WHERE meta_key LIKE %s AND post_id = %s';
            $where = ["%$slot%", $class];

            $results = wp_list_pluck($wpdb->get_results($wpdb->prepare($sql, $where)), 'meta_value', 'meta_key');

            if (!empty($results)) {
                $slots[$slot] = $results;
            }
        }

        foreach($slot_week as $key => $day) {
            foreach($slots as $slot_id => $slot) {
                $index = array_search( $slot_id, $slot_ids );
                $index += 1;
                $slot_status = get_post_meta($class, $slot_id.'_slot_status', true);
    
                if (!empty($slot[$slot_id.$key]) && $slot_status !== 'inactive') {
        
                    $time = date('g:i A', strtotime($slot[$slot_id.$key]));
                    $start = strtotime($time);
    
                    $duration = get_field('duration', $class);
            
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
                    $schedule = ' SLOT #'.$index .' ('.$day .': '.$schedule_time.')';
    
                    $sql = 'SELECT meta_id
                        FROM '.$wpdb->postmeta.'
                            WHERE post_id = %s
                            AND meta_key = %s';
                    
                    $where = [$class, $slot_id.$key];
        
                    $data = $wpdb->get_results(
                        $wpdb->prepare( $sql, $where)
                    );
    
                    $dd_classes[$slot_id][] = array(
                        'schedule' => $schedule,
                        'slot' => $slot_id,
                        'meta_id' => $data[0]->meta_id,
                        'time' => '('.$day .': '.$schedule_time.')',
                        'slot_number' => ' SLOT #'.$index,
                    );
                }
            }
        }
    }

    foreach($dd_classes as $slot) {
        $days = [];
        foreach($slot as $schedule) {
            $days[] = $schedule['time'];
            $classes[$schedule['slot']]['slot'] = $schedule['slot'];
            $classes[$schedule['slot']]['days'] = implode(', ', $days);
            $classes[$schedule['slot']]['slot_number'] = $schedule['slot_number'];
            $classes[$schedule['slot']]['meta_id'] = $schedule['meta_id'];
        }
    }
    
    usort($classes, function($a, $b) {
        return strcmp(strtolower($a['slot_number']), strtolower($b['slot_number']));
    });

    return $classes;
}


function get_all_slots() {
    if ($_GET['class']) {
        $class = $_GET['class'];
        $output = '<option value="">Select Option</option>';

        $classes = get_slots_by_class($class);

        foreach($classes as $class) {
            $output .= '<option class="class-option" value="'.$class['slot'].'" data-meta="'.$class['meta_id'].'" data-number="'. $class['slot_number'].'">'. $class['slot_number'].' '.$class['days'].'</option>';
        }

        echo json_encode($output);
        die();
    }
}

function get_multiselect_slots() {
    if ($_GET['class']) {
        $output = '';
        $classes = get_slots_by_class($_GET['class']);

        foreach($classes as $class) {
            $output .= '<label><input type="checkbox" name="selected_slots[]" value="'.$class['slot'].'" checked>'. $class['slot_number'].' '.$class['days'].'</label><br>';
        }

        echo json_encode($output);
        die();
    }
}

function create_new_parent() {

    if (empty($_POST['firstName']) || empty($_POST['lastName']) || empty($_POST['email']) || empty($_POST['userName']) || empty($_POST['password'])) {
        $error_message = 'All fields are required.';
        $response = array('error_message' => $error_message);
        echo json_encode($response);
        die();
    }

    $email = sanitize_email($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Invalid email format.';
        $response = array('error_message' => $error_message);
        echo json_encode($response);
        die();
    }

    $first_name = sanitize_text_field($_POST['firstName']);
    $last_name = sanitize_text_field($_POST['lastName']);
    $username = sanitize_text_field($_POST['userName']);
    $password = sanitize_text_field($_POST['password']);
    // Crear el nuevo usuario
    
    $user_data = array(
        'user_login' => $username,
        'user_pass' => $password,
        'user_email' => $email,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'role' => 'customer' // Cambia el rol según tus necesidades
    );

    $new_user_id = wp_insert_user($user_data);
    if (is_wp_error($new_user_id)) {
        $error_message = $new_user_id->get_error_message();
        $response = array('error_message' => $error_message);
        echo json_encode($response);
        die();
    }
    $response = array('newUserId' => $new_user_id);
    echo json_encode($response);
    die();
}


// login error message

function custom_login_redirect($redirect_to, $request, $user) {
    if (is_wp_error($user)) {
        $error_message = $user->get_error_message();
        $redirect_url = add_query_arg('login_error', $error_message, site_url('/login'));
        setcookie('custom_login_error', $error_message, time() + 60, COOKIEPATH, COOKIE_DOMAIN);
        wp_safe_redirect($redirect_url);
        exit;
    }
    if (isset($user->roles) && is_array($user->roles)) {
        if (
            in_array('administrator', $user->roles) ||
            in_array('staff', $user->roles) ||
            in_array('seniorstaff', $user->roles) ||
            in_array('regularstaff', $user->roles) ||
            in_array('juniorstaff', $user->roles) ||
            in_array('entrystaff', $user->roles)
        ) {   // Redirects to the administrator dashboard
            return admin_url();
        } else {
            // Redirects to the "My Account" page for non-administrator users.
            return home_url('/my-account/');
        }
        return $redirect_to;
    }
}
add_filter('login_redirect', 'custom_login_redirect', 10, 3);


add_action('wp_footer', function() {
    if (isset($_COOKIE['custom_login_error'])) {
        $error_message = sanitize_text_field($_COOKIE['custom_login_error']);
        echo '<div id="custom-login-error" style="display: none; position: fixed; top:500px; left: 0; right: 0; background:#D8782D; padding-left:15px color: white; text-align: center; padding: 10px;">' . esc_html($error_message) . '</div>';
        echo '<script>
            jQuery(document).ready(function($) {
                $("#custom-login-error").fadeIn().delay(5000).fadeOut(function() {
                    document.cookie = "custom_login_error=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                });
            });
        </script>';
    }
});


function update_order() {
	global $wpdb;
	$fullitem = $_POST['fullItem'];

    $admin = wp_get_current_user();

	$pos = strpos($fullitem, "#");
	$letters_before_hash = substr($fullitem, 0, $pos);
	preg_match_all('/[a-zA-Z]/', $letters_before_hash, $matches); 
   	$all_letters = implode("", $matches[0]);
    $response = array();
	if ($all_letters === 'Order') {

		$sql = "SELECT order_item_id FROM wp_woocommerce_order_items WHERE order_id = {$_POST['orderNumber']} AND order_item_name = '{$_POST['originalDescription']}'";
		$result = $wpdb->get_results($sql);
		$item_id = intval($result[0]->order_item_id);
		$credit = $_POST['credit'];
		$debit = $_POST['debit'];
		$amount = $_POST['amount'];
		$description = $_POST['description'];
		$meta_key_credit = '_credit_editable';
		$meta_key_debit = '_debit_editable';
	
		if ($_POST['credit'] != $_POST['originalCredit']) {
            $edited['credit']['amount'] = $_POST['credit'];
            $edited['credit']['original'] = $_POST['originalCredit'];
			update_or_insert_meta($item_id, $meta_key_credit, $credit);
		}
		if ($_POST['debit'] != $_POST['originalDebit']) {
            $edited['debit']['amount'] = $_POST['debit'];
            $edited['debit']['original'] = $_POST['originalDebit'];
			update_or_insert_meta($item_id, $meta_key_debit, $debit);
		}
		if ($_POST['description'] != $_POST['originalDescription']) {
            $edited['description']['amount'] = $_POST['description'];
            $edited['description']['original'] = $_POST['originalDescription'];
			$update_query = $wpdb->prepare("UPDATE {$wpdb->prefix}woocommerce_order_items	
											SET order_item_name = '{$description}' 
											WHERE order_item_id = {$item_id}");
			$wpdb->query($update_query);

		}
	
		$response = array(
			'status' => 'success', // Puedes usar 'success', 'error', u otros valores según tu lógica
			'message' => 'Actualización exitosa' // Mensaje de éxito o error
		);
	
	} else if ($all_letters === 'Invoice'){
		$query = "SELECT comment_ID, comment_author 
                FROM {$wpdb->comments} 
                WHERE comment_content LIKE '%Invoice%' 
                AND comment_id = '{$_POST['orderNumber']}' 
                AND comment_approved = 1;";
        $resp = $wpdb->get_results($query);
		$order_id = $resp[0]->comment_author;
		$sql = "SELECT order_item_id FROM wp_woocommerce_order_items WHERE order_id = {$order_id} AND order_item_name = '{$_POST['originalDescription']}'";
		$result = $wpdb->get_results($sql);
		$item_id = intval($result[0]->order_item_id);
		$credit = $_POST['credit'];
		$debit = $_POST['debit'];
		$description = $_POST['description'];
		$meta_key_credit = '_credit_editable'.$_POST['orderNumber'];
		$meta_key_debit = '_debit_editable'.$_POST['orderNumber'];
		//$meta_key_description = '_description_editable'.$_POST['orderNumber'];
		if ($_POST['credit'] != $_POST['originalCredit']) {
            $edited['credit']['amount'] = $_POST['credit'];
            $edited['credit']['original'] = $_POST['originalCredit'];
			update_or_insert_meta($item_id, $meta_key_credit, $credit);
		}
		if ($_POST['debit'] != $_POST['originalDebit']) {
            $edited['debit']['amount'] = $_POST['debit'];
            $edited['debit']['original'] = $_POST['originalDebit'];
			update_or_insert_meta($item_id, $meta_key_debit, $debit);
		}
		if ($_POST['description'] != $_POST['originalDescription']) {
            $edited['description']['text'] = $_POST['description'];
            $edited['description']['original'] = $_POST['originalDescription'];
			$update_query = $wpdb->prepare("UPDATE {$wpdb->prefix}woocommerce_order_items	
                                            SET order_item_name = '{$description}' 
                                            WHERE order_item_id = {$item_id}");
			$wpdb->query($update_query);
		}

        if (!empty($_POST['date'])) {
            $date_time = new DateTime();
            $date_time = $date_time->format('H:i:s');
            $date_time = new DateTime($_POST['date'] .' '. $date_time);
            $date_time = $date_time->format('Y-m-d H:i:s');
    
            $sql = 'UPDATE wp_comments SET comment_date = "'.$date_time.'" WHERE comment_ID = '.$resp[0]->comment_ID.'';
            $wpdb->query($sql);
            update_comment_meta($resp[0]->comment_ID, 'due_date', $_POST['date']);
        }

        $order = wc_get_order($order_id);
        if (isset($edited['credit']) && $_POST['credit'] != $_POST['originalCredit']) {
            $order->add_order_note('Credit updated from $'.$edited['credit']['original'].' to $'.$edited['credit']['amount'].' by user '.$admin->display_name);
        }

        if (isset($edited['debit']) && $_POST['debit'] != $_POST['originalDebit']) {
            $order->add_order_note('Debit updated from $'.$edited['debit']['original'].' to $'.$edited['debit']['amount'].' by user '.$admin->display_name);
        }
        
        if (isset($edited['description']) && $_POST['description'] != $_POST['originalDescription']) {
            $order->add_order_note('Description updated from "'.$edited['description']['original'].'" to "'.$edited['description']['text'].'" by user '.$admin->display_name);
        }

        $order->save();

		$response = array(
			'status' => 'success', // Puedes usar 'success', 'error', u otros valores según tu lógica
			'message' => 'Actualización exitosa' // Mensaje de éxito o error
		);
	}
    // Envía la respuesta JSON al AJAX
    echo json_encode($response);
    die();

}

function update_or_insert_meta($item_id, $meta_key, $meta_value) {
    global $wpdb;

    $existing_meta = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->prefix}woocommerce_order_itemmeta
            WHERE order_item_id = %d AND meta_key = %s",
            $item_id,
            $meta_key
        )
    );

    if ($existing_meta !== null) {
        // Actualizar el valor existente
        $update_query = $wpdb->prepare(
            "UPDATE {$wpdb->prefix}woocommerce_order_itemmeta
            SET meta_value = %s
            WHERE order_item_id = %d AND meta_key = %s",
            $meta_value,
            $item_id,
            $meta_key
        );

        $wpdb->query($update_query);
    } else {
        // Insertar un nuevo registro
        $insert_query = $wpdb->prepare(
            "INSERT INTO {$wpdb->prefix}woocommerce_order_itemmeta (order_item_id, meta_key, meta_value)
            VALUES (%d, %s, %s)",
            $item_id,
            $meta_key,
            $meta_value
        );

        $wpdb->query($insert_query);
    }
}

add_action('wp_ajax_reset_password_button', 'reset_password_button'); // For logged-in users
add_action('wp_ajax_nopriv_reset_password_button', 'reset_password_button'); // For non-logged-in users

// Email For reset password
function reset_password_button() {
    $user_id = $_POST['userId'];
    $user_meta = get_user_by('ID',$user_id);
    $name = get_user_meta( $user_id, 'first_name', true);
    $url = home_url().'/lost-password/';
    
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $email_temp = get_posts(array(
        'post_type' => 'email_template',
        'post_status' => 'publish',
        'title' => 'Gymnastics of York Account Password Recovery',
        'orderby' => 'title',
        'order' => 'ASC',
    ));
    $message = $email_temp[0]->post_content;
    $to = $user_meta->data->user_email;
    $message = str_replace(
        '{{user_name}}',
        $name,
        $message
    );
    $message = str_replace(
        '{{url}}',
        $url,
        $message
    );
    $is_sent = wp_mail($to, $email_temp[0]->post_title, $message, $headers);
    if ($is_sent) {
        $response = array('message' => 'Successful email sending');
        echo json_encode($response); 
        die();
    } else {
        $response = array('message' => 'Unsuccessful email sending');
        http_response_code(400); 
        echo json_encode($response); 
        die();
    }
}

/**
 * Adding a logout button in My Account.
 */
function add_logout_button_to_my_account() {
    $logout_url = wc_get_endpoint_url('customer-logout', '', wc_get_page_permalink('myaccount'));

    echo '<p class="woocommerce-MyAccount-navigation-link woocommerce-MyAccount-navigation-link--customer-logout custom-logout-link"><a href="' . esc_url($logout_url) . '">' . esc_html__('Log Out', 'woocommerce') . '</a></p>';
}

add_action('woocommerce_after_account_navigation', 'add_logout_button_to_my_account');

function get_card_on_file_users($owing, $filter) {
    $stripe = new \Stripe\StripeClient(STRIPE_TEST_KEY);
    $users = [];

    foreach($owing as $user) {
        $stripe_cus_id = get_user_meta($user, 'wp__stripe_customer_id', true);

        if (!empty($stripe_cus_id)) {
            try {
                $pm = $stripe->paymentMethods->all([
                    'customer' => $stripe_cus_id,
                    'type' => 'card'
                ]);

                if ($filter['card'] == 'file') {
                    if (!empty($pm->data)) {
                        $users[] = $user;
                    }
                } else {
                    if (empty($pm->data) || empty($pm)) {
                        $users[] = $user;
                    }
                }
            } catch(Exception $e) {
                continue;
            }
        } else if ($filter['card'] !== 'file') {
            $users[] = $user;
        }
    }

    return $users;
}

function get_clients_with_outstanding_payments($filter = null) {
    global $wpdb;


    // Obtain IDs of users with active subscriptions
    $subscription_user_ids = $wpdb->get_col("
        SELECT DISTINCT(pm2.meta_value)
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id
        WHERE p.post_type = 'shop_subscription'
        AND p.post_status IN ('wc-pending', 'wc-on-hold')
        AND pm.meta_key = '_customer_user'
        AND pm2.meta_key = '_customer_user'
    ");

    if (isset($filter['card'])) {
        $owing_subscriptions = get_card_on_file_users($subscription_user_ids, $filter);
    } else {
        $owing_subscriptions = $subscription_user_ids;
    }


    // Get IDs of users with pending or on-hold orders
    $order_user_ids = $wpdb->get_col("
        SELECT DISTINCT(pm.meta_value)
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = 'shop_order'
        AND p.post_status IN ('wc-pending', 'wc-on-hold')
        AND pm.meta_key = '_customer_user'
    ");

    if (isset($filter['card'])) {
        $owing_orders = get_card_on_file_users($order_user_ids, $filter);
    } else {
        $owing_orders = $subscription_user_ids;
    }

    // Combine user IDs of subscriptions and orders into a single array
    $all_user_ids = array_merge($owing_subscriptions, $owing_orders);
    $all_user_ids = array_unique($all_user_ids);

    // Obtain detailed information on users and orders/subscriptions in debt
    $users_with_outstanding_payments = array();
    foreach ($all_user_ids as $user_id) {
        $user_info = get_userdata($user_id); 

        // Get additional user metadata (usermeta)
        $user_meta = get_user_meta($user_id);
        
        if ($user_info && $user_meta) {
            $user_info->usermeta = $user_meta; 
        
            // Obtain orders/subscriptions in debt for the user
            $outstanding_orders = array();

            $orders = wc_get_orders(array(
                'customer' => $user_id,
                'status' => array('pending', 'on-hold'),
                'limit' => -1  
            ));

            foreach ($orders as $order) {
                $outstanding_orders[] = array(
                    'order_number' => $order->get_order_number(),
                    'status' => $order->get_status(),
                    'limit' => -1  
                );
            }
        }
        $transactions = array();
        $credit = 0;
        $refunds = wc_get_orders(array(
            'customer_id' => $user_id,
            'status'      => array('processing', 'refunded'),
            'limit' => -1,
        ));

        $order_ids = array(); 

    foreach ($refunds as $order) {
        $order_ids[] = $order->get_id();
    }
    $orders = implode(',',$order_ids);
    if (!empty($orders)) {
        $sql = "SELECT id, post_title
                FROM {$wpdb->prefix}posts 
                WHERE post_parent IN ( $orders) AND post_type = 'shop_order_refund'";
        $refund = $wpdb->get_results($sql);
        
        if (!empty($refund)) {
            
            foreach ($refund as $id) {
                $order = wc_get_order($id->id);
                $type_refund = get_post_meta($order->get_id(), '_type_refund', true);
    
                if ($type_refund == 'credit') {
                    $transactions[] = array(
                        'fecha' => $order->get_date_created(),
                        'descripcion' => $id->post_title,
                        'credito' =>  $order->get_amount(),
                        'debito' => 0,
                    );
                } else {
                    $transactions[] = array(
                        'fecha' => $order->get_date_created(),
                        'descripcion' => $id->post_title,
                        'credito' =>  0,
                        'debito' => $order->get_amount(),
                    );
                }
    
            }
        }
    }

        $customer_orders = wc_get_orders(array(
            'customer_id' => $user_id,
            'status'      => array('completed', 'processing'),
            'limit' => -1  
        ));
    
        foreach ($customer_orders as $order) {
            $items = $order->get_items();
            $fees = $order->get_fees();

            foreach ($items as $item_id => $item_data) {
                $product = $item_data->get_product();
                if ($product) {
                    $item_total = floatval($item_data->get_total()); // Get the total amount for this item
                    
                    $credit_meta = wc_get_order_item_meta($item_id, '_credit_editable', true);
    
                    if ($credit_meta !== '') {
                        // If the meta exists, use its value as credit
                        $credit = floatval($credit_meta);
                    } else {
                        $credit = $item_total;
                    }
                    $item_name = $item_data->get_name();

                    $transactions[] = array(
                        'fecha' => $order->get_date_created(),
                        'descripcion' => $item_name,
                        'credito' => $credit,
                        'debito' => 0,
                    ); 
                }
            }

            foreach ($fees as $fee_id => $fee_data) {
                $item_total = floatval($fee_data->get_total());
                $credit_meta = wc_get_order_item_meta($fee_id, '_credit_editable', true);
                if ($credit_meta !== '') {
                    $credit = floatval($credit_meta);
                } else {
                    $credit = $item_total;
                }
                $item_name = $fee_data->get_name();

                $transactions[] = array(
                    'fecha' => $order->get_date_created(),
                    'descripcion' => $item_name,
                    'credito' => $credit,
                    'debito' => 0,
                );  
            }
        }

        
    
        $subscriptions = wcs_get_subscriptions(array('customer_id' => $user_id, 'limit' => -1));
        foreach ($subscriptions as $subscription) {
            $subs_id = $subscription->get_id();
            $sql = "SELECT comment_id, comment_date, comment_content 
                    FROM {$wpdb->comments} 
                    WHERE comment_content LIKE '%Invoice%' 
                    AND comment_author = '{$subs_id}' 
                    AND comment_approved = 1;";
            $customer_invoice = $wpdb->get_results($sql);
    
            foreach ($customer_invoice as $invoice) {
                $is_due = false;
                $due_date = get_comment_meta($invoice->comment_id, 'due_date', true);
                
                if (!empty($due_date)) {
                    if (date('Y-m-d') >= $due_date) {
                        $is_due = true;
                    }
                } else {
                    $is_due = true;
                }

                if ($subscription && $is_due) {
                    $items = $subscription->get_items();
                    $fees = $subscription->get_fees();
                    foreach ($items as $item_id => $item_data) {
                        $product = $item_data->get_product();
                        if ($product) {
                            $item_total = floatval($item_data->get_total()); // Get the total amount for this item
                            $debit_meta_key = '_debit_editable' . $invoice->comment_id;
                            // Check if the _debit_editable meta exists for the product
                            $debit_meta = wc_get_order_item_meta($item_id, $debit_meta_key, true);
                            //$descrip_meta = wc_get_order_item_meta($item_id, '_description_editable'. $invoice->comment_id, true);
                            if ($debit_meta !== '') {
                                // If the meta exists, use its value as debit
                                $debit = floatval($debit_meta);
                            } else {
                                $debit = $item_total;
                            }
                            $item_name = $item_data->get_name();

                            $transactions[] = array(
                                'fecha' => $invoice->comment_date,
                                'descripcion' => $item_name,
                                'credito' => 0,
                                'debito' => $debit,
                            );  
                        }
                    }

                    foreach ($fees as $fee_id => $fee_data) {
                        $item_total = floatval($fee_data->get_total()); // Get the total amount for this item
                        $debit_meta_key = '_debit_editable' . $invoice->comment_id;
                        // Check if the _debit_editable meta exists for the product
                        $debit_meta = wc_get_order_item_meta($fee_id, $debit_meta_key, true);
                        //$descrip_meta = wc_get_order_item_meta($item_id, '_description_editable'. $invoice->comment_id, true);
                        if ($debit_meta !== '') {
                            // If the meta exists, use its value as debit
                            $debit = floatval($debit_meta);
                        } else {
                            $debit = $item_total;
                        }
                        $item_name = $fee_data->get_name();
    
                        $transactions[] = array(
                            'fecha' => $invoice->comment_date,
                            'descripcion' => $item_name,
                            'credito' => 0,
                            'debito' => $debit
                        );  
                    }
                }
            }
        }
        
        $orders = wc_get_orders(array('customer_id' => $user_id, 'limit' => -1));
        foreach ($orders as $order) {
            $order_id = $order->get_id();
            $sql = "SELECT comment_id, comment_date, comment_content
                    FROM {$wpdb->comments}
                    WHERE comment_content LIKE '%Invoice%'
                    AND comment_author = '{$order_id}'
                    AND comment_approved = 1;";
            $customer_invoice = $wpdb->get_results($sql);
            foreach ($customer_invoice as $invoice) {
                $is_due = false;
                $due_date = get_comment_meta($invoice->comment_id, 'due_date', true);

                if (!empty($due_date)) {
                    if (date('Y-m-d') >= $due_date) {
                        $is_due = true;
                    }
                } else {
                    $is_due = true;
                }

                if ($is_due) {
                    $items = $order->get_items();
                    $fees = $order->get_fees();

                    foreach ($items as $item_id => $item_data) {
                        $product = $item_data->get_product();
                        if ($product) {
                            $item_total = floatval($item_data->get_total()); // Get the total amount for this item
                            $debit_meta_key = '_debit_editable' . $invoice->comment_id;
                    
                            // Check if the _debit_editable meta exists for the product
                            $debit_meta = wc_get_order_item_meta($item_id, $debit_meta_key, true);
                            if ($debit_meta !== '') {
                                // If the meta exists, use its value as debit
                                $debit = floatval($debit_meta);
                            } else {
                                $debit = $item_total;
                            }
                            $item_name = $item_data->get_name();

                            $transactions[] = array(
                                'fecha' => $invoice->comment_date,
                                'descripcion' => $item_name,
                                'credito' => 0,
                                'debito' => $debit
                            );  
                        }
                    }

                    foreach ($fees as $fee_id => $fee_data) {
                        $item_total = floatval($fee_data->get_total());
                        // Check if the _debit_editable meta exists for the product
                        $debit_meta_key = '_debit_editable' . $invoice->comment_id;
                        $debit_meta = wc_get_order_item_meta($fee_id, $debit_meta_key, true);
                        //$descrip_meta = wc_get_order_item_meta($item_id, '_description_editable'. $invoice->comment_id, true);
                        if ($debit_meta !== '') {
                            // If the meta exists, use its value as debit
                            $debit = floatval($debit_meta);
                        } else {
                            $debit = $item_total;
                        }
                        $item_name = $fee_data->get_name();
    
                        $transactions[] = array(
                            'fecha' => $invoice->comment_date,
                            'descripcion' => $item_name,
                            'credito' => 0,
                            'debito' => $debit
                        );  
                    }
                }
            }
        }

        usort($transactions, function($a, $b) {
            // First, compare the dates
            $dateComparison = strtotime($a['fecha']) - strtotime($b['fecha']);
        
             // If the dates are different, return the date comparison
            if ($dateComparison !== 0) {
                return $dateComparison;
            }
        
            // If the dates are the same, compare description
            return strcmp($a['descripcion'], $b['descripcion']);
        });

        $balance = 0;
        foreach ($transactions as $transaction) {
            $balance += $transaction['credito'] - $transaction['debito'];
            
            if ($balance < 0) {
                $formatted_balance = '-' . wc_price(abs($balance));
            } else {
                $formatted_balance = wc_price($balance);
            }
            $last_formatted_balance = $formatted_balance;
        }

        if ($user_info && !empty($outstanding_orders)) {
            $user_info->outstanding_orders = $outstanding_orders;
            $user_info->balance = $last_formatted_balance;
            $users_with_outstanding_payments[] = $user_info;
        }
    }

    return $users_with_outstanding_payments;
}


add_action('wp_ajax_delete_item', 'delete_item'); // For logged-in users
add_action('wp_ajax_nopriv_delete_item', 'delete_item'); // For non-logged-in users

function delete_item() {
    if ( $_POST['itemType'] == 'Order' ) {
        $order = wc_get_order($_POST['itemId']);
    } else if($_POST['itemType'] == 'Invoice') {
        $comment = get_comment($_POST['itemId']);
        $order = wc_get_order($comment->comment_author);
    } else {
        $response = array('response' => 'This type of item cannot be deleted.');
        http_response_code(400); 
    }

    if (isset($order)) {
        $items = $order->get_items();
        $fees = $order->get_fees();

        if (count($items) == 1) {
            wp_delete_post($order->get_id(),true);

            if($_POST['itemType'] == 'Invoice') {
                wp_delete_comment($_POST['itemId'], true);
            }

            $response = array('response' => 'Item has been successfully deleted.');
        } else {
            foreach ($items as $item_id => $item_data) {
                $product = $item_data->get_product();
                if ($product) {
                    if ($item_id == $_POST['rowId']) {
                        $item_name = $item_data->get_name();
                        $order->remove_item($item_id);
                        $order->calculate_totals();
                        $response = array('response' => 'Item has been successfully deleted.');
                    }
                }
            }

            foreach ($fees as $fee_id => $fee_data) {
                if ($fee_id == $_POST['rowId']) {
                    $item_name = $fee_data->get_name();
                    $order->remove_item($fee_id);
                    $order->calculate_totals();
                    $response = array('response' => 'Item has been successfully deleted.');
                }
            }
        }

        $admin = wp_get_current_user();
        $order->add_order_note('Item "'.$item_name.'" removed by user '.$admin->display_name);
        $order->save();
    }

    echo json_encode($response);
    die();
}

function get_invoice_table($item) {
    $comment = get_comment_meta($item, 'invoice_table', true);   
    return $comment;
}

function description_comment($item) {
    $notes = wc_get_order_notes(array('limit' => 10, 'order_id' => $item));
    $html = '';

    foreach($notes as $note) {
        $html .= '<li>'.$note->content.'</li>';
    }

    $note = get_post_meta($item, '_order_note', true);
    if (!empty($note)) {
        $html .= '<li>'.$note.'</li>';
    }

    return $html;
}

add_action('wp_ajax_send_invoice_email', 'send_invoice_email'); // For logged-in users
add_action('wp_ajax_nopriv_send_invoice_email', 'send_invoice_email'); // For non-logged-in users


function send_invoice_email() {
    $from = !empty(get_option('custom_note_from')) ? get_option('custom_note_from') : 'ca@gymnasticsofyork.com';
    $replyto = !empty(get_option('custom_note_replyto')) ? get_option('custom_note_replyto') : 'ca@gymnasticsofyork.com';
    $bcc  = !empty(get_option('custom_note_bcc')) ? get_option('custom_note_bcc') : 'ca@gymnasticsofyork.com';

    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'From: <'.$from.'>';
    $headers[] = 'Reply-To: <'.$replyto.'>';
    $headers[] = 'Bcc: '.$bcc;
    
    $itemId = $_POST['item'];
    $comment = get_comment($itemId);

    $message = get_comment_meta($itemId, 'invoice_id', true);

    $user_id = $comment->user_id;
    $template_message = get_posts(array(
        'post_type' => 'email_template',
        'post_status' => 'publish',
        'title' => 'GY Invoice Due Reminder',
        'orderby' => 'title',
        'order' => 'ASC',
    ));

    $user = get_userdata($user_id);
    $invoice_table = get_comment_meta($itemId, 'invoice_table', true);
    $order_id = $comment->comment_author;
    $date = $comment->comment_date;
    // $message = EmailTemplates::self_merge_tags($template_message[0]->post_content, $user->ID);

    $message = str_replace(
        ['{{order_id}}', '{{date}}', '{{invoice_table}}'],
        [$order_id, $date, $message, $invoice_table],
        $template_message[0]->post_content
    );

    if (!empty($order_id) && !empty($message) && !empty($invoice_table)) {
        $is_sent = wp_mail($user->data->user_email, $template_message[0]->post_title, $message, $headers);
        if ($is_sent) {
            $current = get_current_user_id();
            $current_user = get_user_by('id', $current);
            $comment_user = array(
                'comment_author' => $current_user->display_name,
                'comment_content' => 'Email resended "'.$template_message[0]->post_title.'" sent to '. $user->data->user_email .'.',
                'user_id' => $user->data->ID,
                'comment_meta'         => array(
                    'is_customer_note'       => sanitize_text_field(1),
                    )
                );
    
            wp_insert_comment($comment_user);
            echo json_encode(1);
        } else {
            echo json_encode(0);
        }
    } else {
        echo json_encode(0);
    }
    

    die();
}

// Function to add the search box in the wpadminbar
function custom_search_form_in_admin_bar() {
    global $wp_admin_bar;

    // Add a new menu in the wpadminbar
    $wp_admin_bar->add_menu(
        array(
            'id' => 'custom-search',
            'title' => '<form action="'.admin_url('admin.php?page=user-information').'" method="post"><input type="text" name="custom-search-input" id="custom-search-input" placeholder="Account/Athlete Search"></form>',
            'meta' => array(
                'class' => 'custom-search'
            )
        )
    );
}

// Add the search box to the wpadminbar
add_action('wp_before_admin_bar_render', 'custom_search_form_in_admin_bar');

function get_future_invoices($user_id) {
    global $wpdb;
    $transactions = array();
    $table = ' <table class="gy-table future_table" id="future_table">';
    $table .= '<thead>
                <tr>
                    <th>Date</th>
                    <th>Item</th>
                    <th>Description</th>
                    <th>Amount ($)</th>
                    <th colspan="2">Action</th>
                </tr>
                </thead>
                <tbody>';

    // Obtain invoice-related comments from the customer
    $subscriptions = wcs_get_subscriptions(array('customer_id' => $user_id, 'limit' => -1));
    foreach ($subscriptions as $subscription) {
        $subs_id = $subscription->get_id();
        $sql = "SELECT comment_id, comment_date, comment_content 
                FROM {$wpdb->comments} 
                WHERE comment_content LIKE '%Invoice%' 
                AND comment_author = '{$subs_id}' 
                AND comment_approved = 1;";
        $customer_invoice = $wpdb->get_results($sql);
        
        foreach ($customer_invoice as $invoice) {
            $is_due = false;
            $due_date = get_comment_meta($invoice->comment_id, 'due_date', true);
            
            if (!empty($due_date)) {
                if (date('Y-m-d') >= $due_date) {
                    $is_due = true;
                }
            }
            $item_name = '';
            if ($subscription && $is_due) {
                $fees = $subscription->get_fees();
                $items = $subscription->get_items();
                foreach ($items as $item_id => $item_data) {
                    $product = $item_data->get_product();
                    if ($product) {
                        $item_total = floatval($item_data->get_total()); // Get the total amount for this item
                        $debit_meta_key = '_debit_editable' . $invoice->comment_id;
                        
                        $debit_meta = wc_get_order_item_meta($item_id, $debit_meta_key, true);

                        if ($debit_meta !== '') {
                            // If the meta exists, use its value as debit
                            $amount = floatval($debit_meta);
                        } else {
                            $amount = $item_total;
                        }

                        // $amount = $item_total;
                        $item_name = $item_data->get_name();

                        $transactions[] = array(
                            'invoice_id' => $invoice->comment_id,
                            'order_id' => $subs_id,
                            'fecha' => $invoice->comment_date,
                            'item' => 'Invoice #' . $invoice->comment_id,
                            'descripcion' => $item_name,
                            'amount' => $amount
                        );    
                    }
                }
                foreach ($fees as $fee_id => $fee_data) {
                    $item_total = floatval($fee_data->get_total()); // Get the total amount for this item
                    
                    $debit_meta_key = '_debit_editable' . $invoice->comment_id;
                    // Check if the _debit_editable meta exists for the product
                    $debit_meta = wc_get_order_item_meta($fee_id, $debit_meta_key, true);
                    //$descrip_meta = wc_get_order_item_meta($item_id, '_description_editable'. $invoice->comment_id, true);
                    if ($debit_meta !== '') {
                        // If the meta exists, use its value as debit
                        $amount = floatval($debit_meta);
                    } else {
                        $amount = $item_total;
                    }

                    // $amount = $item_total;
                    $item_name = $fee_data->get_name();

                    $transactions[] = array(
                        'invoice_id' => $invoice->comment_id,
                        'order_id' => $subs_id,
                        'fecha' => $invoice->comment_date,
                        'item' => 'Invoice #' . $invoice->comment_id,
                        'descripcion' => $item_name,
                        'amount' => $amount
                    );    
                }
            }
        }
    }
    
    $orders = wc_get_orders(array('customer_id' => $user_id, 'limit' => -1));
    foreach ($orders as $order) {
        $order_id = $order->get_id();
        $is_paid = get_post_meta($order_id, 'is_paid', true);

        if ($is_paid !== '1') {
            $sql = "SELECT comment_id, comment_date, comment_content
                    FROM {$wpdb->comments}
                    WHERE comment_content LIKE '%Invoice%'
                    AND comment_author = '{$order_id}'
                    AND comment_approved = 1;";
            $customer_invoice = $wpdb->get_results($sql);
            foreach ($customer_invoice as $invoice) {
                $is_due = false;
                $due_date = get_comment_meta($invoice->comment_id, 'due_date', true);
                if (!empty($due_date)) {
                    if (date('Y-m-d')< $due_date) {
                        $is_due = true;
                    }
                } 
    
                // Get the name of the order item associated with the invoice
    
                if ($is_due) {
                    $items = $order->get_items();
                    foreach ($items as $item_id => $item_data) {
                        $product = $item_data->get_product();
                        if ($product) {
                            $item_total = floatval($item_data->get_total()); // Get the total amount for this item
                            $debit_meta_key = '_debit_editable' . $invoice->comment_id;
                            // $descrip_meta = wc_get_order_item_meta($item_id, '_description_editable'. $invoice->comment_id, true);
                    
                            // Check if the _debit_editable meta exists for the product
                            $debit_meta = wc_get_order_item_meta($item_id, $debit_meta_key, true);
                            if ($debit_meta !== '') {
                                // If the meta exists, use its value as debit
                                $amount = floatval($debit_meta);
                            } else {
                                $amount = $item_total;
                            }
                            
                            // $amount = $item_total;
                            $item_name = $item_data->get_name();
                            
                            $transactions[] = array(
                                'invoice_id' => $invoice->comment_id,
                                'order_id' => $order_id,
                                'fecha' => $invoice->comment_date,
                                'item' => 'Invoice #' . $invoice->comment_id,
                                'descripcion' => $item_name,
                                'amount' => $amount
                            ); 
                        }
                    }
                }
            }
        }
        
    }
    
    usort($transactions, function($a, $b) {
        // First, compare the dates
        $dateComparison = strtotime($b['fecha']) - strtotime($a['fecha']);
    
        // If the dates are different, return the date comparison.
        if ($dateComparison !== 0) {
            return $dateComparison;
        }
    
        // If the dates are the same, compare description
        return strcmp($b['descripcion'], $a['descripcion']);
    });

    // Create the table with transactions within the specified range
    $total = 0;
    foreach ($transactions  as $key => $transaction) {
        $date = new DateTime($transaction['fecha']);
        $date = $date->format('Y-m-d');

        $table .= '<tr class="original-row row-' . $key . '" data-table="#future_table" data-key="'.$key.'" id="future_table">';
        $table .= '<td style="position:relative;" id="date'.$key.'">' . date("Y-m-d H:i:s", strtotime($transaction['fecha'])) . '
                    <div id="hover-'. $key .'" class="hover_modal">
                        <p>'. (isset($transaction['invoice_id']) ? get_invoice_table($transaction['invoice_id']) : '') .'</p>
                    </div>
                    <div id="payment-hover-'. $key .'" data-table="#future_table" class="payment_modal">
                        <ul>'.description_comment($transaction['order_id']).'</ul>
                    </div>
                    </td>';
        $table .= '<td class="item-'. $key .'" data-table="#future_table" style="position:relative;">' . $transaction['item'] . '</td>';
        $table .= '<td id="description'.$key.'" data-table="#future_table" class="description-'.$key.'">' . $transaction['descripcion'] . '</td>';
        $table .= '<td id="debit'.$key.'">' . wc_price(abs($transaction['amount'])) . '</td>';
        
        if (current_user_can('administrator')) {
            $table .= '<td class="edit-button" data-row="row-' . $key . '" data-table="#future_table">
                    <svg xmlns="http://www.w3.org/2000/svg" 
                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                    style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                </td>';
            $table .='<td class="delete-btn" data-row="row-' . $key . '" data-table="#future_table">
                        <svg xmlns="http://www.w3.org/2000/svg" 
                        fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                        style="width: 24px; height: 24px; color:red;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </td>';

            $table .= '</tr>';
            $table .= '<tr class="editable-row row-' . $key . '" data-row="row-' . $key . '" data-table="#future_table" style="display: none;">';
            $table .= '<td><input type="date" class="edit-date" class="edit-date" value="'.$date.'"/></td>';
            $table .= '<td id="item'.$key.'">'.esc_attr($transaction['item']).'</td>';
            $table .= '<td><input type="text" class="edit-description" value="' . esc_attr($transaction['descripcion']) . '"></td>';
            $table .= '<td><input type="number" class="edit-debit" style="width: 75px;" value="' . esc_attr($transaction['amount']) . '"></td>';
            $table .= '<td>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                    class="cancel-button" data-table="#future_table" style="width: 24px; height: 24px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                class="save-button" data-table="#future_table" style="width: 24px; height: 24px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg></td>';
            $table .= '</tr>';
        }

        $total += $transaction['amount'];
    }
    $table .= '</tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" style="font-weight: 600; text-align:right;">Future Billing Total</td>
                        <td>'.wc_price($total, array('decimals' => 2)).'</td>
                    </tr>
                </tfoot>
                </table>';
    return $table;
}

function save_ach_method($setup_id, $setup_pm) {
    $stripe = new \Stripe\StripeClient(STRIPE_TEST_KEY);
    $is_invalid = [];

    if (!empty($setup_id) && !empty($setup_pm)) {

        try {
            $setup_intent = $stripe->setupIntents->confirm(
                $setup_id,
                ['payment_method' => $setup_pm, 'mandate_data' => ['customer_acceptance' => ['type' => 'online', 'online' => ['ip_address' => '35.245.151.137', 'user_agent' => 'device']]]]
            );

            if ($setup_intent->status == 'requires_action') {
                try {
                    $setup_intent = $stripe->setupIntents->verifyMicrodeposits(
                        $setup_id,
                        ['amounts' => [32, 45]]
                    );
                } catch (\Stripe\Exception\RateLimitException $e) {
                $is_invalid[] = 'Our server is currently experiencing high traffic. Please try again later.';
                } catch (\Stripe\Exception\InvalidRequestException $e) {
                    $is_invalid[] = 'We are sorry, we cannot process your request. Please try again later.';
                } catch (\Stripe\Exception\AuthenticationException $e) {
                    $is_invalid[] = 'We are sorry, we cannot authenticate your request. Please try again later.';
                } catch (\Stripe\Exception\ApiConnectionException $e) {
                    $is_invalid[] = 'We are sorry, we are experiencing connection issues. Please try again later.';
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    $is_invalid[] = 'We are sorry, we are experiencing connection issues. Please try again later.';
                } catch (Exception $e) {
                    $is_invalid[] = 'Unknown Error. Please try again later.';
                }

                if (!empty($is_invalid)) {
                    return $is_invalid;
                }
            }
        } catch (\Stripe\Exception\RateLimitException $e) {
            $is_invalid[] = 'Our server is currently experiencing high traffic. Please try again later.';
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            $is_invalid[] = 'We are sorry, we cannot process your request. Please try again later.';
        } catch (\Stripe\Exception\AuthenticationException $e) {
            $is_invalid[] = 'We are sorry, we cannot authenticate your request. Please try again later.';
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            $is_invalid[] = 'We are sorry, we are experiencing connection issues. Please try again later.';
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $is_invalid[] = 'We are sorry, we are experiencing connection issues. Please try again later.';
        } catch (Exception $e) {
            $is_invalid[] = 'Unknown Error. Please try again later.';
        }
        
        if (!empty($is_invalid)) {
            return $is_invalid;
        }
    }

    return $is_invalid;
}

function get_categories_info() {
    $product_categories = get_terms(
        array(
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        )
    );

    $html = '';

    foreach ($product_categories as $key => $cat) {

        $html .= '<tr class="cat_'.$cat->term_id.' not-editable">
                    <td class="cat-name">'.$cat->name.'</td>
                    <td class="cat-parent">'.(!empty($cat->parent) ? get_term($cat->parent)->name : '-').'</td>
                    <td class="cat-descr">'.(!empty($cat->description) ? $cat->description : '-') .'</td>
                    <td class="edit-cat" data-id="'.$cat->term_id.'"><svg xmlns="http://www.w3.org/2000/svg" 
                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                    style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg></td>
                    <td class="delete-cat edit-btn" data-catname="'.$cat->name.'" data-id="'.$cat->term_id.'" data-modal="#confirm_delete_category"><svg xmlns="http://www.w3.org/2000/svg" 
                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                    style="width: 24px; height: 24px; color:red;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg></td>
                </tr>
                <tr class="hidden cat_'.$cat->term_id.' editable">
                    <td><input id="cat_name_'.$cat->term_id.'" value="'.$cat->name.'"/></td>
                    <td>
                        <select id="cat_parent_'.$cat->term_id.'" name="parent_category">
                            <option value="">Select Category</option>
                            '.pos_get_categories(array('option' => 1, 'selected' => $cat->parent)).'
                        </select>
                    </td> 
                    <td><input id="cat_descr_'.$cat->term_id.'" value="'.(!empty($cat->description) ? $cat->description : '') .'"/></td>
                    <td data-id="'.$cat->term_id.'" class="cancel-btn"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                    style="width: 24px; height: 24px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg></td>
                    <td class="save-cat" data-id="'.$cat->term_id.'"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                    style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg></td>
                </tr>';
    }

    if (empty($html)) {
        $html .= '<tr>
                    <td colspan="5">No items</td>
                <tr>';
    }

    return $html;

}

function delete_category() {
    if (isset($_GET['cat_id'])) {
        $delete = wp_delete_term($_GET['cat_id'], 'product_cat'); 
        if (is_wp_error($delete)) {
            echo json_encode(0);
        } else {
            echo json_encode(1);
        }
    }

    die();
}

function update_category() {
    if (isset($_GET['cat_id'])) {
        $category = get_term($_GET['cat_id']);
        $cat_name = !empty($_GET['cat_name']) ? $_GET['cat_name'] : $category->name;

        $new = wp_update_term($_GET['cat_id'], 'product_cat', array(
            'name' => $cat_name,
            'description' => $_GET['cat_descr'],
            'parent' => $_GET['cat_parent'],
        ));

        if (is_wp_error($new)) {
            echo json_encode(0);
        } else {
            $new = get_term($new['term_id']);
            echo json_encode(array('name' => $new->name, 'descr' => $new->description, 'parent' => get_term($new->parent)->name));
        }
    }

    die();
}

function get_attendance_history($user_id) {
    global $wpdb;

    $sql = 'SELECT attendance, DATE AS day FROM wp_class_attendance WHERE user_id = %s AND attendance !="" ORDER BY date DESC';
    $where = [$user_id];

    $results = $wpdb->get_results($wpdb->prepare($sql, $where));

    $html = '';

    if (!empty($results)) {

        foreach($results as $res) {
            $day = date('l', strtotime($res->day));
            $abbr = strtoupper(substr($day, 0, 3));

            $html .= '<option>'.$res->attendance.' - '.$abbr.' '.$res->day.'</option>';
        }
    } else {
        $html .= '<option>Empty</option>';
    }

    return $html;
}

function get_order_details() {
    $stripe = new \Stripe\StripeClient(STRIPE_TEST_KEY);

    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $order = wc_get_order($id);

        $original_amount = $order->get_total();
        $date = $order->get_date_created();
        $date = date('Y-m-d H:i:s', strtotime($date));
        $customer = $order->get_user();
        $customer_name = $customer->first_name . ' ' . $customer->last_name;

        $notes = wc_get_order_notes(['order_id' => $order->get_id(),
        ]);
        $description = $notes[0]->content . '<br>' . $notes[1]->content;

        $source = get_post_meta($id, '_user_id', true);
        if (!empty($source)) {
            $user = get_user_by('id', $source);
            $user = $user->first_name . ' ' . $user->last_name;
        } else {
            $user = 'System';
        }

        $discount = get_post_meta($id, '_discount_percentage', true);
        $fee = get_post_meta($id, '_fee_percentage', true);

        if (!empty($discount)) {
            $before_discount = $original_amount * floatval($discount) / 100;
            $before = ceil($original_amount + $before_discount);
            $discount_given = $before * ($discount);
            $amount_extra = round($discount_given, 2);
            $amount = $original_amount;
        }
        
        if (!empty($fee)) {
            $fee_given = $original_amount * floatval($fee) / 100;
            $amount = $original_amount + $fee_given;
            $amount_extra = $fee_given;
        }

        if (!empty($fee) && !empty($discount)) {
            $amount = $amount + $fee_given - $discount_given;
            $amount_extra = $fee_given - $discount_given;
        }

        $payment_method = get_post_meta($id, '_payment_method', true);
        if ($payment_method == 'ach_stripe' || $payment_method == 'stripe_ach') {
            $payment_method_formatted = 'ACH (Stripe)';
        } else if ($payment_method == 'stripe') {
            $payment_method_formatted = 'Credit Card (Stripe)';
        } else {
            $payment_method_formatted = ucfirst($payment_method);
        }

        $payment_intent = get_post_meta($id, '_stripe_intent_id', true);
        $payment_method_id = get_post_meta($id, '_stripe_source_id', true);
        if (!empty($payment_method_id)) {
            $pm = $stripe->paymentMethods->retrieve($payment_method_id, []);

            $type = $pm['type'];
            $last4 = $pm[$type]['last4'];
            $payment_method_id = $last4;
        }

        $invoice_id = get_post_meta($id, '_invoice_id', true);
        $html = '';

        $refunded_total = $order->get_total_refunded();

        if (!empty($invoice_id)) {
            $parent_order = wc_get_order($invoice_id);

            if ($parent_order->order_type == 'shop_subscription') {
                $invoice_id = $parent_order->get_parent_id();
                $parent_order = wc_get_order($invoice_id);
            }

            foreach($parent_order->get_items() as $item_id => $item) {
                $refunded_item = wc_get_order_item_meta($item_id, '_refund_amount', true);

                if ($order->get_total() < $parent_order->get_total()) {
                    $price = $order->get_total() / count($parent_order->get_items());
                    $item_total = round($price, 1);
                } else {
                    $item_total = $item->get_total();
                }

                $html .= '<tr id="item_'.$id.$item_id.'">
                            <td class="hidden refund-item"><input type="checkbox" value="'.$item_id.'" data-id="#item_'.$id.$item_id.'" class="partial-refund-item partial-refund-item-check" name="partial_refund_item['.$item_id.'][id]" disabled></td>
                            <td>'.$item->get_name().'</td>
                            <td class="not-partial-amount"><div class="item-total" data-amount="'.$item_total.'">'.wc_price($item_total).'</div>'.(!empty($refunded_item) ? '<small class="refunded-item refunded-item-total" data-amount="'.$refunded_item.'">-'.wc_price($refunded_item).'</small>' : '').'</td>
                            <td class="hidden partial-refund-item"><input class="partial-refund-item partial-refund-item-amount" step="0.1" name="partial_refund_item['.$item_id.'][amount]" type="number" value="0" disabled/></td>
                        </tr>';
            }
        }

        if (!empty($refunded_total)) {
            $net_payment = $original_amount - $refunded_total;
            $net_payment = $net_payment < 0 ? '' : $net_payment;
        }

        echo json_encode(array(
            'amount' => round($amount, 2),
            'amount_extra' => (isset($amount_extra) ? round($amount_extra, 2) : ''),
            'date' => $date,
            'description' => (isset($description) ? $description : ''),
            'user' => $user,
            'payment_method_formatted' => $payment_method_formatted,
            'payment_method' => $payment_method,
            'payment_intent' => (!empty($payment_intent) ? $payment_intent : ''),
            'payment_method_id' => (!empty($payment_method_id) ? $payment_method_id : ''),
            'order_items' => $html,
            'customer_name' => $customer_name,
            'customer_id' => $customer->ID,
            'refunded_total_formatted' => (isset($refunded_total) && !empty($refunded_total) ? wc_price($refunded_total) : ''),
            'net_payment_formatted' => (isset($net_payment) ? wc_price($net_payment) : ''),
            'net_payment' => (isset($net_payment) ? $net_payment : ''),
            'parent_order' => (!empty($invoice_id) ? $invoice_id : ''),
            'payment_order' => $id,
            'refunded_total' => (isset($refunded_total) && !empty($refunded_total) ? $refunded_total : ''),
            'original_amount' => $original_amount,
        ));
    }

    die();
}

function check_existing_athlete($childusername) {
    $letters = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
    
    if (!empty(get_user_by('login', $childusername))) {
        $random_letter_1 = $letters[rand(0, count($letters) - 1)];
        $random_letter_2 = $letters[rand(0, count($letters) - 1)];
        $random_letter_3 = $letters[rand(0, count($letters) - 1)];
        $random_word = '_'.$random_letter_1 . $random_letter_2 . $random_letter_3;

        $childusername .=  $random_word;

        if (!empty(get_user_by('login', $childusername))) {
            check_existing_athlete($childusername);
        } else {
            return $childusername;
        }

    } else {
        return $childusername;
    }
}

function get_email_templates() {
    $email_templates = get_posts(array(
        'post_type' => 'email_template',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ));

    $html = '<option value="">Select an Email Template</option>';
    foreach ($email_templates as $template) {
        if (!empty($template->post_title)) {
            $html .= '<option value="'.$template->ID.'">'.$template->post_title.'</option>';
        }
    }

    return $html;
}
