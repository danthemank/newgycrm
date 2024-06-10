<?php 

add_action( 'admin_footer', 'pos_ajax_response' );
add_action( 'wp_footer', 'pos_ajax_response' );

function pos_ajax_response() {
    ?>
    <script>
        (function ($) {
            $(document).ready(function () {

                $('body').on('click', '.easy-pos .save-button', function() {
                    var rowClass = $(this).closest('tr.editable-row').data('row');
                    var idRow = rowClass.split('-')[1]
                    var editedDescription = $('.editable-row.' + rowClass + ' .edit-description').val();
                    var editedCredit = $('.editable-row.' + rowClass + ' .edit-credit').val();
                    var editedDebit = $('.editable-row.' + rowClass + ' .edit-debit').val();
                    // Extraer el número de orden del elemento "Order #9328"

                    var fullItem = $('#item'+idRow).text();
                    var orderNumber = $('#item'+idRow).text().split('#')[1];
                    
                    // Obtener los valores originales
                    var originalDescription = $('#description'+idRow).text();
                    var originalCredit = parseFloat($('#credit'+idRow).text().replace("$", ""));
                    var originalDebit = parseFloat($('#debit'+idRow).text().replace("$", ""));
                    
                    // Realizar llamada AJAX para actualizar la orden
                    $.ajax({
                        type: 'POST',
                        url: obj.ajaxurl, // Cambia esto por la URL de tu archivo PHP
                        data: {
                            action: 'update_order', // Acción que identificará la función a ejecutar en el lado del servidor
                            orderNumber: orderNumber,
                            fullItem:fullItem,
                            rowClass: rowClass,
                            description: editedDescription,
                            credit: editedCredit,
                            debit: editedDebit,
                            originalDescription: originalDescription,
                            originalCredit: originalCredit,
                            originalDebit: originalDebit
                        },
                        success: function(response) {
                            let customerId = $('.easy-pos #customer').val()
                            window.location = '/wp-admin/admin.php?page=pos-admin-page&id='+customerId;
                        }
                    });
                }); 

                $('.easy-pos #amount').on('keyup', calcDiscountAndFees)

                $('.easy-pos #discount_percentage').on('keyup', function() {
                    calcDiscountAndFees()
                    calcAmountToBringAccountCurrent()
                })
                $('.easy-pos .fee_percentage').on('keyup', calcDiscountAndFees)
                

                $('.easy-pos input[name="card_exists"]').on('change', function() {
                    if ($(this).val() == 'add_card') {
                        $('.easy-pos .add_card').show()
                    } else {
                        $('.easy-pos .add_card').hide()
                    }
                })

                $('.easy-pos input[name="ach_exists"]').on('change', function() {
                    if ($(this).val() == 'add_ach') {
                        $('.easy-pos .add_ach').show()
                    } else {
                        $('.easy-pos .add_ach').hide()
                    }
                })

                $('.easy-pos #is_discount').on('change', function() {
                    if ($(this).is(":checked")) {
                        $('.easy-pos #is_discount + span').show()
                        $('.easy-pos .discount-container').show()

                        defaultDiscountAndFees()

                        calcDiscountAndFees()
                        calcAmountToBringAccountCurrent()

                    } else {
                        $('.easy-pos #is_discount + span').hide()
                        $('.easy-pos .discount-container').hide()
                    }
                })

                $('.easy-pos #is_fee').on('change', function() {
                    if ($(this).is(":checked")) {
                        $('.easy-pos #is_fee + span').show()
                        $('.easy-pos .fee-container').show()

                        defaultDiscountAndFees()

                        calcDiscountAndFees()

                    } else {
                        $('.easy-pos #is_fee + span').hide()
                        $('.easy-pos .fee-container').hide()
                    }
                })

                // When the customer dropdown changes, fetch and populate the orders dropdown
                $('#customer').on('change', function (e) {
console.log('object');
console.log(e.target);
                    $('.easy-pos #card_exists').prop('checked', false)
                    $('.easy-pos .card_exists').hide()
                    $('.easy-pos .add_card').show()

                    let customerId = $(this).val();

                    if (customerId !== 'no_account') {
                        // Fetch the orders for the selected customer using AJAX
                        $.ajax({
                            url: '<?php echo admin_url("admin-ajax.php"); ?>',
                            type: 'POST',
                            data: {
                                action: 'get_customer_data',
                                customer_id: customerId
                            },
                            success: function (response) {

                                response = JSON.parse(response)

                                $('#order').prop('disabled', false);
                                $('#order').html(response.orders);

                                if (response.card_info) {
                                    $('.easy-pos .card_exists').show()
                                    $('.easy-pos #card_exists').prop('checked', true)
                                    $('.easy-pos .add_card').hide()

                                    let card = response.card_info

                                    $('.easy-pos #card_id').html(card)
                                }

                                if (response.ach_info) {
                                    $('.easy-pos .ach_exists').show()
                                    $('.easy-pos #ach_exists').prop('checked', true)
                                    $('.easy-pos .add_ach').hide()

                                    let ach = response.ach_info

                                    $('.easy-pos #ach_id').html(ach)
                                }

                                getInvoices(customerId).then(response => {
                                    let amount = response.amount 

                                    $('.easy-pos #amount').val(amount)
                                    $('.easy-pos #my_account_amount').text(amount)
                                    $('.easy-pos #balance_table').html(response.table)

                                    billingHistoryStyle()

                                    $('#submit_payment').prop('disabled', false)
                                    $('#amount').prop('disabled', false)
                                    $('#submit_payment').toggleClass('disabled')
                                    
                                    calcDiscountAndFees()
                                    calcAmountToBringAccountCurrent()

                                })

                            }
                        });
                    } else {
                        // If "No Account" is selected, disable and empty the orders dropdown
                        $('#order').prop('disabled', true).html('');

                    }
                });

                $('.easy-pos #payment_method').on('change', function () {

                    const orderId = $('.easy-pos #order').val();
                    const customerId = $('.easy-pos #customer').val();

                    if ($(this).val() == 'cash') {
                        $('.payment-section .cash').show()
                        $('.payment-section .credit-card').hide()
                        $('.payment-section .check').hide()
                        $('.payment-section .ach').hide()
                        $('.easy-pos .discount-section').show()
                    }
                    
                    if ($(this).val() == 'card') {
                        $('.payment-section .cash').hide()
                        $('.payment-section .credit-card').show()
                        $('.payment-section .check').hide()
                        $('.payment-section .ach').hide()
                        $('.easy-pos .discount-section').show()
                        $('.easy-pos .fee-section').show()
                        $('.easy-pos .order-section').show()

                        $('.my-account-fee').text(3.5)

                    }
                    
                    if ($(this).val() == 'check') {
                        $('.payment-section .cash').hide()
                        $('.payment-section .credit-card').hide()
                        $('.payment-section .check').show()
                        $('.payment-section .ach').hide()
                        $('.easy-pos .discount-section').hide()
                        $('.easy-pos .fee-section').hide()
                        $('.easy-pos .order-section').show()

                    }

                    if ($(this).val() == 'adjustment') {
                        $('.payment-section .cash').hide()
                        $('.payment-section .credit-card').hide()
                        $('.payment-section .check').hide()
                        $('.payment-section .ach').hide()
                        $('.easy-pos .discount-section').hide()
                        $('.easy-pos .fee-section').hide()
                        $('.easy-pos .order-section').hide()

                    }

                    if ($(this).val() == 'credit') {
                        $('.payment-section .cash').hide()
                        $('.payment-section .credit-card').hide()
                        $('.payment-section .check').hide()
                        $('.payment-section .ach').hide()
                        $('.easy-pos .discount-section').hide()
                        $('.easy-pos .fee-section').hide()
                        $('.easy-pos .order-section').hide()
                    }

                    if ($(this).val() == 'ach') {
                        $('.payment-section .cash').hide()
                        $('.payment-section .credit-card').hide()
                        $('.payment-section .check').hide()
                        $('.payment-section .ach').show()
                        $('.easy-pos .discount-section').hide()
                        $('.easy-pos .fee-section').show()
                        $('.easy-pos .order-section').show()

                        $('.my-account-fee').text(1)
                    }

                    defaultDiscountAndFees()
                    calcDiscountAndFees()
                    calcAmountToBringAccountCurrent()

                });

                $('.easy-pos #order').on('change', function () {

                    const orderId = $(this).val();
                    const customer = $('.easy-pos #customer').val();

                    if (orderId !== '') {
                        getInvoices(customer, $(this).val()).then(response => {
    
                            // Fetch the orders for the selected customer using AJAX                            
                            let amount = response.amount
                            
                            $('.easy-pos #amount').val(amount)
                            $('#submit_payment').prop('disabled', false)
                            $('#amount').prop('disabled', false)
                            $('#submit_payment').toggleClass('disabled')

                            calcDiscountAndFees()
                            calcAmountToBringAccountCurrent()

                        })
                    }   
                });

                async function getInvoices(customerId, orderId = '') {
                    $('#submit_payment').prop('disabled', true)
                    $('#amount').prop('disabled', true)
                    $('#submit_payment').toggleClass('disabled')
                    let response = await $.ajax({
                        url: '<?php echo admin_url("admin-ajax.php"); ?>',
                            type: 'POST',
                            data: {
                                action: 'get_amount',
                                order_id: orderId,
                                customer_id: customerId
                            }
                        });
                        // console.log(response);
                        return Promise.resolve(JSON.parse(response));
                }

                function calcDiscountAndFees() {
                    let amount = $('.easy-pos #amount').val()
 
                    if (amount !== '') {
                        if ($('.easy-pos #discount_percentage').val() !== '') {
                            let amount = $('.easy-pos #amount').val()
                            let discountPercentage = $('.easy-pos #discount_percentage').val()

                            let discountGiven = amount * (discountPercentage / 100)
                            
                            $('.easy-pos #discount_given').val(discountGiven.toFixed(2))
                        }

                        if ($('.easy-pos .fee_percentage').val() !== '') {
                            let amount = $('.easy-pos #amount').val()
                            let feePercentage = $('.easy-pos .fee_percentage').val()

                            let feeGiven = amount * parseFloat(feePercentage) / 100

                            $('.easy-pos #fee_given').val(feeGiven)

                            let amountFee = parseFloat(amount) + parseFloat(feeGiven)

                            $('.easy-pos #amount_fee').val(amountFee.toFixed(2))
                        }
                    }
                }

                function calcAmountToBringAccountCurrent() {
                    let amount = $('.easy-pos #amount').val()
                    let discountPercentage = parseInt($('.easy-pos #discount_percentage').val()) / 100

                    let amountToBring = amount / (1 + discountPercentage)

                    $('.easy-pos #amount_current').val(amountToBring.toFixed(2))
                }

                function defaultDiscountAndFees() {
                    switch($('.easy-pos #payment_method').val()) {
                        case 'card': 
                        $('.fee_percentage').val(3.5)
                        break;

                        case 'cash': 
                        $('#discount_percentage').val(10)
                        $('.fee_percentage').val(0)
                        break;

                        case 'ach': 
                        $('#discount_percentage').val(0)
                        $('.fee_percentage').val(1)
                        break;

                        default: 
                        $('#discount_percentage').val(0)
                        $('.fee_percentage').val(0)
                        break;
                    }
                }

                function billingHistoryStyle() {
                    $('.easy-pos #balance_table tbody tr.original-row').each(function(index) {
                        var creditCell = $(this).find('#credit' + index);
                        var debitCell = $(this).find('#debit' + index);
                        var creditValue = creditCell.text().trim();
                        var debitValue = debitCell.text().trim();

                        if (creditValue !== "$0.00") {
                            $(this).addClass('highlight-row-credit');
                        }
                        
                        if (debitValue !== "$0.00") {
                            $(this).addClass('highlight-row-debit');
                        }
                    });
                }
            });
        })(jQuery);
    </script>
    <?php
}


add_action('wp_ajax_get_customer_data', 'get_customer_data_callback');
add_action('wp_ajax_get_amount', 'get_amount');
add_action('wp_ajax_get_categories_display', 'get_categories_display');
add_action('wp_ajax_create_ach_setup_intent', 'create_ach_setup_intent');
add_action('wp_ajax_delete_invoice', 'delete_invoice');
add_action('wp_ajax_get_invoice_classes', 'get_invoice_classes');
add_action('wp_ajax_get_class_hourly_fee', 'get_class_hourly_fee');
add_action('wp_ajax_get_class_fee', 'get_class_fee');
add_action('wp_ajax_get_ach_payment_methods', 'get_ach_payment_methods');
add_action('wp_ajax_remove_ach_payment_method', 'remove_ach_payment_method');
add_action('wp_ajax_pos_get_product_details', 'pos_get_product_details');

function pos_get_product_details() {
    if (isset($_GET['product_id'])) {
        $product_id = $_GET['product_id'];
        $product = wc_get_product($product_id);
        $product_price = $product->get_price();
        $product_desc = $product->get_description();
        $product_cat = $product->get_category_ids();

        echo json_encode(array('price' => $product_price, 'desc' => $product_desc, 'cat' => $product_cat));
    }
    
    die();
}

function remove_ach_payment_method() {
    $stripe = new \Stripe\StripeClient(STRIPE_TEST_KEY);
    
    if (isset($_GET['id'])) {

        $ach_id = $_GET['id'];

        $payment_method = $stripe->paymentMethods->retrieve(
            $ach_id
        );

        if ($payment_method) {
            $stripe->paymentMethods->detach(
                $ach_id,
                []
            );
        }
    }

    die();
}
function get_ach_payment_methods() {
    $stripe = new \Stripe\StripeClient(STRIPE_TEST_KEY);
    
    if (isset($_GET['customer'])) {

        $stripe_cus = get_user_meta($_GET['customer'], 'wp__stripe_customer_id', true);

        $ach_info = '';
        $stripe_ach = $stripe->paymentMethods->all([
            'customer' => $stripe_cus,
            'type' => 'us_bank_account'
        ]);
        foreach ($stripe_ach->data as $ach) {
            if (isset($ach['us_bank_account']->last4)) {
                $ach_info .=  '
                <tr class="payment-method default-payment-method">
                    <td class="woocommerce-PaymentMethod woocommerce-PaymentMethod--method payment-method-method" data-title="Method">'.$ach['us_bank_account']->bank_name.' Account ending in '.$ach['us_bank_account']->last4.'</td>
                    <td class="woocommerce-PaymentMethod woocommerce-PaymentMethod--expires payment-method-expires" data-title="Expires">'.(isset($ach['us_bank_account']->exp_month) && $ach['us_bank_account']->exp_year ? $ach['us_bank_account']->exp_month.'/'.$ach['us_bank_account']->exp_year : '-').'</td>
                    <td class="woocommerce-PaymentMethod woocommerce-PaymentMethod--actions payment-method-actions" data-title="&nbsp;"><a class="button delete delete-ach" data-id="'.$ach->id.'">Delete</a>&nbsp;</td>
                </tr>';
            }
        }
    }

    echo json_encode($ach_info);
    die();
}

function create_ach_setup_intent() {
    if (isset($_GET['customer'])) {
        $stripe = new \Stripe\StripeClient(STRIPE_TEST_KEY);

        $is_invalid = [];
        
        $user = get_user_by('id', $_GET['customer']);
        $stripe_cus_id = get_user_meta($user->ID, 'wp__stripe_customer_id', true);

        if (empty($stripe_cus_id)) {
            try {
                $customer = $stripe->customers->create([
                    'email' => $user->user_email,
                ]);
                $stripe_cus_id = $customer->id;
                update_user_meta($user->ID, 'wp__stripe_customer_id', $stripe_cus_id);
            
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
        }

        if (!empty($is_invalid)) {
            echo json_encode($is_invalid);
            die();
        }

        try {
            $setup_intent = $stripe->setupIntents->create([
                'customer' => $stripe_cus_id,
                'payment_method_types' => ['us_bank_account'],
                'payment_method_options' => [
                    'us_bank_account' => [
                    'financial_connections' => ['permissions' => ['payment_method']],
                    ],
                ],
            ]);
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
            $result = $is_invalid;
        } else {
            
            $name = $user->first_name . ' '.$user->last_name;
            $email = $user->user_email;

            $result = array(
                'client_secret' => $setup_intent->client_secret,
                'billing_details' => array(
                        'name' => $name, 
                        'email' => $email, 
                    ),
                'stripe_cus_id' => $stripe_cus_id 
                );
        }

        echo json_encode($result);
        }
    die();
}


function get_class_fee() {
    if (isset($_GET['hours'])) {
        $price_per_hour = [ '0,5' => get_option('halfhour_week'), '0.5' => get_option('halfhour_week'), 1 => get_option('onehour_week'), '1.5' => get_option('onehalfhour_week'), '1,5' => get_option('onehalfhour_week'), '2' => get_option('twohour_week'), '3' => get_option('threehour_week'), '4' => get_option('fourhour_week'), '5' => get_option('fivehour_week'), '6' => get_option('sixhour_week'), '7' => get_option('sevenhour_week'), '8' => get_option('eighthour_week'), '9' => get_option('ninehour_week'), '12' => get_option('twelvehour_week'), '15' => get_option('fifteenhour_week'), '20' => get_option('twentyhour_week'),];
        echo json_encode($price_per_hour[strval($_GET['hours'])]);
    }

    die();
}

function get_class_hourly_fee() {
    if (isset($_GET['id'])) {

        $hours_per_week = get_post_meta($_GET['id'], 'hours_per_week', true);
        echo json_encode(array('hours' => $hours_per_week));
    }

    die();
}

function get_invoice_classes() {
    echo json_encode(get_classes());
    die();
}
    
function get_classes($id = '') {
    $classes = get_posts(array('post_type' => 'class', 'post_status' => 'published', 'posts_per_page' => -1));

    $html = '<option value="">Select Class</option>';

    foreach ($classes as $class) {
        $hours_per_week = get_post_meta($class->ID, 'hours_per_week', true);
        $product_id = get_post_meta($class->ID, 'product_id', true);

        if (!empty($hours_per_week) && !empty($product_id)) {
            if ($id == $class->ID) {
                $html .= '<option value="'.$class->ID.'" selected>'.$class->post_title.'</option>';
            } else {
                $html .= '<option value="'.$class->ID.'">'.$class->post_title.'</option>';
            }
        }
    }
    return $html;
}

function delete_invoice() {
    global $wpdb;

    if ($_GET['invoice_id']) {
        $invoice_id = $_GET['invoice_id'];

        $sql = 'DELETE FROM wp_comments WHERE comment_ID = '.$invoice_id;
        $wpdb->query($sql);

        echo 1;
    }

    die();
}

function pos_get_products() {
    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'limit' => -1
    );
    $products = wc_get_products($args);

    $html = '<option value="">Select Option</option>';
    foreach($products as $product) {
        $html .= '<option value="'.$product->get_id().'">'.$product->get_name().'</option>';
    }

    return $html;
}

function get_categories_display() {
    $categories = pos_get_categories(array('row' => $_POST['row']));

    echo json_encode(array('categories' => $categories));

    die();
}
function pos_get_categories($args = null) {
    $product_categories = get_terms(
        array(
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        )
    );

    $cat_parents = [];

    foreach($product_categories as $key => $category) {
        $parent = $category->parent;
        
        if ($parent) {
            $cat_parents[get_term($parent)->name][$parent][$category->term_id] = $category->name;
        } else {
            if (!isset($cat_parents[$category->name])) {
                $cat_parents[$category->term_id] = $category->name;
            }
        }
    }

    $html = '';

    foreach($cat_parents as $key => $cat) {
        if (isset($args['option'])) {
            if (is_array($cat)) {
                $parent_id = array_keys($cat);
                $html .= '<option value="'.$parent_id[0].'" '.(isset($args['selected']) && $args['selected'] == $parent_id[0] ? 'selected' : '').'>&nbsp;&nbsp;&nbsp;'.$key.'</option>';
                foreach($cat[$parent_id[0]] as $id => $child) {
                    $html .= '<option value="'.$id.'" '.(isset($args['selected']) && $args['selected'] == $id ? 'selected' : '').'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$child.'</option>';
                }
            } else {
                $html .= '<option value="'.$key.'">'.$cat.'</option>';
            }
        } else {
            if (is_array($cat)) {
                $parent_id = array_keys($cat);
                $html .= '<li><input data-id="order-item-'.(isset($args['row']) ? $args['row'] : '0').'" name="order_item['.(isset($args['row']) ? $args['row'] : '0').'][category][]" value="'.$parent_id[0].'" type="checkbox">'.$key.'</li>';
                $html .= '<ul>';
                foreach($cat[$parent_id[0]] as $id => $child) {
                    $html .= '<li><input data-id="order-item-'.(isset($args['row']) ? $args['row'] : '0').'" name="order_item['.(isset($args['row']) ? $args['row'] : '0').'][category][]" value="'.$id.'" type="checkbox">'.$child.'</li>';
                }
                $html .= '</ul>';
            } else {
                $html .= '<li><input data-id="order-item-'.(isset($args['row']) ? $args['row'] : '0').'" name="order_item['.(isset($args['row']) ? $args['row'] : '0').'][category][]" value="'.$key.'" type="checkbox">'.$cat.'</li>';
            }
        }
    }

    return $html;
}

function pos_get_all_customers() {
    $users = get_users();

    return $users;
}

// AJAX callback to fetch orders for the selected customer
function get_customer_data_callback() {
    if (isset($_POST['customer_id'])) {
        $customer_id = $_POST['customer_id'];

        $stripe_cus = get_user_meta($customer_id, 'wp__stripe_customer_id', true);

        if (!empty($stripe_cus)) {
            $stripe = new \Stripe\StripeClient(STRIPE_TEST_KEY);
            
            $card_info = '';
            $stripe_cards = $stripe->paymentMethods->all([
                'customer' => $stripe_cus,
                'type' => 'card'
            ]);
            foreach ($stripe_cards->data as $card) {
                if (isset($card['card']->last4)) {
                    $card_info .=  '<option value="'.$card->id.'">'.$card['card']->last4.'</option>';
                }
            }
            
            $ach_info = '';
            $stripe_ach = $stripe->paymentMethods->all([
                'customer' => $stripe_cus,
                'type' => 'us_bank_account'
            ]);
            foreach ($stripe_ach->data as $ach) {
                if (isset($ach['us_bank_account']->last4)) {
                    $ach_info .=  '<option value="'.$ach->id.'">'.$ach['us_bank_account']->last4.'</option>';
                }
            }

        }

        $orders = get_orders_by_customer_id($customer_id);

        if (!empty($orders)) {
            $options_html = '<option value="">Select order</option>';
            foreach ($orders as $order) {
                $options_html .= '<option value="' . $order['order_id'] . '">Invoice #' .$order['invoice_id']. ' ('.$order['date'].')</option>';
            }
            $result = $options_html;
        } else {
            $result = '<option value="">No orders available</option>';
        }

        echo json_encode(array('orders' => $result, 'card_info' => !empty($card_info) ? $card_info : null, 'ach_info' => !empty($ach_info) ? $ach_info : null));
    }

    wp_die(); // Always use wp_die() at the end of AJAX callbacks.
}

// Custom function to fetch orders by customer ID from the database
function get_orders_by_customer_id($customer_id) {
    global $wpdb;
    $onhold_orders = [];

    $orders = wc_get_orders( array(
        'limit' => -1,
        'customer_id' => $customer_id,
        'status' => array('on-hold', 'pending'),
    ) );

    $subscriptions = wcs_get_subscriptions( array(
        'limit' => -1,
        'customer_id' => $customer_id,
        'subscription_status' => array('wc-on-hold', 'wc-pending'),
    ) );

    if ($orders) {
        foreach($orders as $order) {
            $sql = 'SELECT * FROM wp_comments WHERE comment_author = %s';
            $where = [$order->get_id()];

            $results = $wpdb->get_results($wpdb->prepare($sql, $where));

            if ($results) {
                foreach ($results as $result) {
                    $onhold_orders[] = array('order_id' => $order->get_id(), 'invoice_id' => $result->comment_ID, 'date' => $order->get_date_created()->format('Y-m-d'));
                }
            }

        }
    }

    if ($subscriptions ) {
        foreach($subscriptions as $subscription) {
            $sql = 'SELECT * FROM wp_comments WHERE comment_author = %s';
            $where = [$subscription->get_id()];

            $results = $wpdb->get_results($wpdb->prepare($sql, $where));

            if ($results) {
                foreach ($results as $result) {
                    $onhold_orders[] = array('order_id' => $subscription->get_id(), 'invoice_id' => $result->comment_ID, 'date' => $subscription->get_date_created()->format('Y-m-d'));
                }
            }

        }
    }


    return $onhold_orders;
}

function get_amount() {
    if (isset($_POST['order_id']) && isset($_POST['customer_id'])) {
        echo json_encode(get_invoice_balance($_POST['customer_id'], $_POST['order_id']));
    }

    wp_die();
}

function get_invoice_balance($customer_id, $sub_id = '', $no_edit = '') {

    global $wpdb;
    // Calcular el monto total cobrado (crédito) de los pedidos

    if (!empty($sub_id)) {
        $sql = "SELECT com.meta_value as invoice_total,
                    co.user_id
                FROM {$wpdb->comments} co
                JOIN {$wpdb->commentmeta} com
                ON co.comment_ID = com.comment_id
                    AND co.comment_author = '{$sub_id}'
                    AND com.meta_key = 'invoice_total'";
        $current_invoice = $wpdb->get_results($sql);

        $order = wc_get_order($sub_id);
        $current_invoice = $order->get_total();
    }

    
        // Calcular el monto total de débito de las facturas

    $transactions = array();
    $table = '<table class="gy-table user_balance_table custom-table">';
    $table .= '<thead>
                <tr>
                    <th>Date</th>
                    <th>Item</th>
                    <th>Description</th>
                    <th>Credits (+$)</th>
                    <th>Debits (-$)</th>
                    <th>Balance</th>
                    '.(empty($no_edit) ? '<th colspan="2">Action</th>' : '').'
                </tr>
                </thead>
                <tbody>';
    $credit = 0;
    $refunds = wc_get_orders(array(
        'customer_id' => $customer_id,
        'status'      => array('processing', 'refunded'),
        'limit' => -1,
    ));
    $order_ids = array(); 

    foreach ($refunds as $order) {
        $order_ids[] = $order->get_id();
    }
    $orders = implode(',',$order_ids);
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

    $customer_orders = wc_get_orders(array(
        'customer_id' => $customer_id,
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
                'order_id' => $order->get_id(),
                'fecha' => $order->get_date_created(),
                'item' => 'Order #' . $order->get_id(),
                'descripcion' => $item_name,
                'credito' => $credit,
                'debito' => 0,
                'balance' => 0
            );  
        }
    }

    $subscriptions = wcs_get_subscriptions(array('customer_id' => $customer_id, 'limit' => -1));
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

    $orders = wc_get_orders(array('customer_id' => $customer_id, 'limit' => -1));
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
                        'invoice_id' => $invoice->comment_id,
                        'order_id' => $order_id,
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

    // Ordenar el array de transacciones por fecha ascendente
    usort($transactions, function($a, $b) {
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
        $transaction['balance_not_formatted'] = $balance;
        $transactions_with_balance[] = $transaction;
    }

    usort($transactions_with_balance, function($a, $b) {
        // First, compare the dates
        $dateComparison = strtotime($b['fecha']) - strtotime($a['fecha']);
    
        // If the dates are different, return the date comparison.
        if ($dateComparison !== 0) {
            return $dateComparison;
        }

        if ($a['descripcion'] == $b['descripcion'] && $a['item'] == $b['item']) {
            return $a['balance_not_formatted'] - $b['balance_not_formatted'];
        }
    
        // If the dates are the same, compare description
        return strcmp($b['descripcion'], $a['descripcion']);
    });

    foreach ($transactions_with_balance  as $key => $transaction) {
        $table .= '<tr class="original-row row-' . $key . '" data-table="#balance_table" data-key="'.$key.'">';
        $table .= '<td style="position:relative;">' . date("Y-m-d H:i:s", strtotime($transaction['fecha'])) . '</td>';
        $table .= '<td class="item-'. $key .'" data-table="#balance_table" style="position:relative;">' . $transaction['item'] . '</td>';
        $table .= '<td id="description'.$key.'" data-table="#balance_table" class="description-'.$key.'">' . $transaction['descripcion'] . '</td>';
        $table .= '<td id="credit'.$key.'">' . wc_price(abs($transaction['credito'])) . '</td>';
        $table .= '<td id="debit'.$key.'">' . wc_price(abs($transaction['debito'])) . '</td>';
        $table .= '<td>' . $transaction['balance'] . '</td>';
        if (empty($no_edit)) {
            $table .= '<td class="edit-button" data-row="row-' . $key . '" data-table="#balance_table">
                        <svg xmlns="http://www.w3.org/2000/svg" 
                        fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                        style="width: 24px; height: 24px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                    </td>';
            if (current_user_can('administrator')) {
                $table .='<td class="delete-btn" data-row="row-' . $key . '" data-table="#balance_table">
                            <svg xmlns="http://www.w3.org/2000/svg" 
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                            style="width: 24px; height: 24px; color:red;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </td>';
            }
        }

        $table .= '</tr>';

        if (empty($no_edit)) {
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
    }

    $table .= '</tbody></table>';

    if(!empty($sub_id)) {
        $amount = round($current_invoice, 2);
    } else {
        $balance = round($transactions_with_balance[0]['balance_not_formatted'], 2);
        if ($balance < 0) {
            $amount = abs($balance);
        } else {
            $amount = 0;
        }
    }

    return array('amount' => $amount, 'table' => $table);

}

