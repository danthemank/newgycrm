<?php 

class Registration {
    
    public $carrier_options;
    public $suffix_options;
    public $gender_options;
    public $slot_week;
    
    
    public function __construct($carrier_options, $suffix_options, $gender_options, $slot_week)
    {
        $this->slot_week = $slot_week;
        $this->carrier_options = $carrier_options;
        $this->suffix_options = $suffix_options;
        $this->gender_options = $gender_options;
        add_action('init', array($this, 'app_output_buffer'));
        add_shortcode( 'billing_athletes_membership', array($this, 'gy_registration_form_shortcode') );
    }

    function app_output_buffer() {
          ob_start();
    }

    function gy_registration_form_shortcode() {
        $current_user = wp_get_current_user();

        if ( is_user_logged_in() && in_array( 'member', $current_user->roles ) ) {
            wp_redirect( home_url( '/membership' ) );
            exit();
        }

        $id = get_current_user_id();
        $user_roles = get_user_by('id', $id)->roles;

        if ( is_user_logged_in() && !in_array('administrator', $user_roles) ) {

            wp_safe_redirect(home_url());
            exit;

        } else {
            if (isset($_POST['athletes'])) {
                foreach($_POST['athletes'] as $athlete) {
                    if (isset($athlete['classes']) || isset($athlete['lessons'])) {
                        $is_enrolled = true;
                    }
                }
            }

            $is_coupon = false;
            if (isset($_POST['free_class_coupon']) && !empty($_POST['free_class_coupon'])) {
                $is_coupon = check_gy_coupon($_POST['free_class_coupon']);
            }

            if (isset($_POST['stripeToken']) && isset($_POST['customer_id'])) {
                $is_invalid = $this->register_payment($_POST['customer_id'], $_POST['stripeToken'], $_POST);

                if (!empty($is_invalid)) {
                    $this->retry_payment($is_invalid, $_POST, $_POST['customer_id']);
                } else {
                    ?>
                    <script>
                        jQuery(document).ready(function($) {

                            $('.billing-account-section').remove()
                            $('.athlete-section').remove()
                            $('.payment-section').remove()
                            $('.submit-btn').remove()
                            $('.start-date-row').remove()
                            $('.coupon-row').remove()
                            $('.add-athlete-btn').remove()
                            $('.referrals-section').remove()

                            $('.payment-success').show()

                            setTimeout(function() {
                                window.location.href = "/my-account";
                                }, 3000);
                        })
                    </script>
                    <?php
                }

            } else if (!isset($_POST['customer_id']) && $is_enrolled) {

                $is_valid = $this->validate_registration($_POST);
                if ($is_valid) {

                    $customer_id = $this->register_new_customer($_POST, $is_coupon);
                    if ($customer_id) {

                        $result = $this->register_athletes($customer_id, $_POST, $is_coupon);
                        if (!empty($result)) {

                            if (count($result) !== count($_POST['athletes'])) {
                                foreach ($_POST['athletes'] as $athlete) {
                                    if (!in_array($athlete['child_first_name'], $result)) {
                                        ?>
                                        <script>
                                            jQuery(document).ready(function($) {
                                                $('.global-error').append('<div>Error saving <?= $athlete['child_first_name'] ?>\'s account. Please add the athlete through your account page <a href="/my-account">here</a>.</div>')
                                                $('.global-error').show()
                                            })
                                        </script>
                                        <?php
                                    }
                                }
                            }

                            if (isset($_POST['stripeToken'])) {
                                $is_invalid = $this->register_payment($customer_id, $_POST['stripeToken'], $_POST);
                            }

                            if (!empty($is_invalid)) {
                                $this->retry_payment($is_invalid, $_POST, $customer_id);
                            } else {
                                ?>
                                <script>
                                    jQuery(document).ready(function($) {

                                        $('.billing-account-section').remove()
                                        $('.athlete-section').remove()
                                        $('.payment-section').remove()
                                        $('.submit-btn').remove()
                                        $('.start-date-row').remove()
                                        $('.add-athlete-btn').remove()
                                        $('.referrals-section').remove()

                                        $('.payment-success').show()

                                        setTimeout(function() {
                                            window.location.href = "/my-account";
                                            }, 7000);
                                    })
                                </script>
                                <?php
                            }

                        }
                    }
                } else {
                    ?>
                    <script>
                        jQuery(document).ready(function($) {
                            $('.global-error').text('Please fill in all fields.');
                            $('.global-error').show()
                        })
                    </script>
                    <?php
                }
            }

            $nonce = wp_create_nonce('registration_nonce');

            require GY_CRM_PLUGIN_DIR . 'views/templates/public/registration/registration.php';
        }


    
    }

    public function enroll_athletes($user_id, $fields) {
        global $wpdb;
        
        $is_coupon = false;
        if (isset($fields['free_class_coupon']) && !empty($fields['free_class_coupon'])) {
            $is_coupon = check_gy_coupon($fields['free_class_coupon']);
        }

        $children = get_user_meta($user_id, 'smuac_multiaccounts_list', true);

        $children = explode(',', $children);

        foreach($children as $child) {
            if (!empty($child)) {
                $name = get_user_meta($child, 'first_name', true);
                foreach($fields['athletes'] as $athlete) {

                    if (strtolower($athlete['child_first_name']) == strtolower($name)) {
                        $slot_ids = [];
                        $slots = explode(',', $athlete['slots']);
                        $selected_programs = explode(',', $athlete['classes']);
                        
                        foreach($slots as $slot) {
                            $sql = 'SELECT post_id FROM wp_postmeta WHERE meta_value LIKE %s';
                            $where = ["%$slot%"];
        
                            $results = $wpdb->get_results($wpdb->prepare($sql, $where));
                            $slot_ids[$results[0]->post_id][] = $slot;
                        }
                        
                        if (isset($athlete['lessons'])) {
                            $slot_ids[$athlete['private_lessons']][] = $athlete['lessons'];
                            $slots[] = $athlete['lessons'];
                            $selected_programs[] = $athlete['private_lessons'];
                        }

                        if ($is_coupon) {
                            update_user_meta( $child, 'complementary_classes', array($selected_programs) );
                            update_user_meta( $child, 'complementary_classes_slots', array($slot_ids) );
                            update_user_meta( $child, 'complementary_slots', array($slots) );
                        } else {
                            update_user_meta( $child, 'status_program_participant', 'active' );
                            update_user_meta( $child, 'classes', array( $selected_programs ) );
                            update_user_meta( $child, 'classes_slots', array( $slot_ids ) );
                            update_user_meta( $child, 'slots', array($slots));
                        }

                        
                    }
            
                }
            }
        }

    }

    public function retry_payment($is_invalid, $fields, $customer_id) {
        ?>
        <script>
            jQuery(document).ready(function($) {
                const form = $('#membership_form');

                $('.global-error').append('<div><?= $is_invalid[0] ?></div>')
                $('.global-error').show()
                $('.billing-account-section').remove()
                $('.athlete-section').remove()
                $('.start-date-row').remove()
                $('.coupon-row').remove()
                $('.add-athlete-btn').remove()
                $('.referrals-section').remove()

                $('<input/>').appendTo(form).attr('type', 'hidden').attr('name', 'free_class_coupon').attr('value', '<?= $fields['free_class_coupon'] ?>');
                $('<input/>').appendTo(form).attr('type', 'hidden').attr('name', 'password').attr('value', '<?= $fields['password'] ?>');
                $('<input/>').appendTo(form).attr('type', 'hidden').attr('name', 'customer_id').attr('value', '<?= $customer_id ?>');
                $('<input/>').appendTo(form).attr('type', 'hidden').attr('name', 'referral[customer_id]').attr('value', '<?= $fields['referral']['customer_id'] ?>');

                <?php
                foreach($fields['athletes'] as $key => $athlete) {
                    if (isset($athlete['classes']) && isset($athlete['slots'])) {
                        ?>
                        $('<input/>').appendTo(form).attr('type', 'checkbox').attr('name', '<?= 'athletes['.$key.'][enrolled][]' ?>').addClass('programs-checked').prop('checked', true).val('classes').hide();
                        $('<input/>').appendTo(form).attr('type', 'hidden').attr('name', '<?= 'athletes['.$key.'][classes]' ?>').attr('value', '<?= $athlete['classes'] ?>');
                        $('<input/>').appendTo(form).attr('type', 'hidden').attr('name', '<?= 'athletes['.$key.'][slots]' ?>').attr('value', '<?= $athlete['slots'] ?>');
                        <?php
                    }

                    if (isset($athlete['lessons'])) {
                        ?>
                        $('<input/>').appendTo(form).attr('type', 'checkbox').attr('name', '<?= 'athletes['.$key.'][enrolled][]' ?>').prop('checked', true).val('lessons').hide();
                        $('<input/>').appendTo(form).attr('type', 'hidden').attr('name', '<?= 'athletes['.$key.'][lessons]' ?>').attr('value', '<?= $athlete['lessons'] ?>');
                        $('<input/>').appendTo(form).attr('type', 'hidden').attr('name', '<?= 'athletes['.$key.'][private_lessons]' ?>').attr('value', '<?= $athlete['private_lessons'] ?>');
                        <?php
                    }
                    ?>
                        $('<input/>').appendTo(form).attr('type', 'hidden').attr('name', '<?= 'athletes['.$key.'][child_first_name]' ?>').attr('value', '<?= $athlete['child_first_name'] ?>');
                    <?php
                }
                ?>
            })
        </script>
        <?php
    }

    public function register_payment($user_id, $stripe_token, $fields) {
        global $wpdb;

        $headers = array('Content-Type: text/html; charset=UTF-8');
        $is_invalid = [];

        $user = get_user_by('id', $user_id);

        $stripe = new \Stripe\StripeClient(STRIPE_TEST_KEY);

        $registration_fee = intval(get_option('registration_fee'));
        $amount = $registration_fee + ($registration_fee * (3.5 / 100));
        $amount = intval($amount * 100);

        $invoice_template = get_posts(['post_type' => 'email_template', 'name' => 'Subscription Renewal Invoice'])[0];
        $template_message = $invoice_template->post_content;
        $subject = $invoice_template->post_title;

        $sql = 'SELECT * FROM wp_comments WHERE user_id = %d';
        $where = [$user->ID];
        $results = $wpdb->get_results($wpdb->prepare($sql, $where));

        if (empty($results)) {
            EmailTemplates::get_subscription_invoice($user->user_email, $template_message, $subject, $headers, array('children' => $fields['athletes'], 'not_automatic' => 1));
            $order_id = $this->create_order($user_id, $registration_fee, array('type' => 'fee_invoice'));
        }

        try {
            $customer = $stripe->customers->create([
                'email' => $user->user_email,
                'source' => $stripe_token
            ]);
    
            $payment_method = $customer->default_source;
            $stripe_cus_id = $customer->id;
    
        } catch(\Stripe\Exception\CardException $e) {

            if ($e->getError()->code == 'expired_card') {
                $is_invalid[] = 'The card has expired. Check the expiration date or use a different card.';
            } elseif ($e->getError()->code == 'card_declined') {
                $is_invalid[] = 'Your card has been declined by issuer.';
            } elseif ($e->getError()->code == 'incorrect_zip') {
                $is_invalid[] = 'The card’s postal code is incorrect. Check the card’s postal code or use a different card.';
            } elseif ($e->getError()->code == 'incorrect_number') {
                $is_invalid[] = 'The card number is incorrect. Check the card’s number or use a different card.';
            } elseif ($e->getError()->code == 'invalid_expiry_month') {
                $is_invalid[] = 'The card’s expiration month is incorrect. Check the expiration date or use a different card.';
            } elseif ($e->getError()->code == 'invalid_expiry_year') {
                $is_invalid[] = 'The card’s expiration year is incorrect. Check the expiration date or use a different card.';
            } elseif ($e->getError()->code == 'incorrect_cvc') {
                $is_invalid[] = 'The card’s security code is incorrect. Check the card’s security code or use a different card.';
            } else {
                $is_invalid[] = 'Unknown Error with your credit card. Please try again later.';
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
    
        try {
            $payment_intent = $stripe->paymentIntents->create([
                'amount' => $amount,
                'currency' => 'usd',
                'customer' => $stripe_cus_id,
                'payment_method' => $payment_method,
                'confirmation_method' => 'manual',
                'confirm' => true,
            ]);
    
            } catch(\Stripe\Exception\CardException $e) {
    
                if ($e->getError()->code == 'expired_card') {
                    $is_invalid[] = 'The card has expired. Check the expiration date or use a different card.';
                } elseif ($e->getError()->code == 'card_declined') {
                    $is_invalid[] = 'Your card has been declined by issuer.';
                } elseif ($e->getError()->code == 'incorrect_zip') {
                    $is_invalid[] = 'The card’s postal code is incorrect. Check the card’s postal code or use a different card.';
                } elseif ($e->getError()->code == 'incorrect_number') {
                    $is_invalid[] = 'The card number is incorrect. Check the card’s number or use a different card.';
                } elseif ($e->getError()->code == 'invalid_expiry_month') {
                    $is_invalid[] = 'The card’s expiration month is incorrect. Check the expiration date or use a different card.';
                } elseif ($e->getError()->code == 'invalid_expiry_year') {
                    $is_invalid[] = 'The card’s expiration year is incorrect. Check the expiration date or use a different card.';
                } elseif ($e->getError()->code == 'incorrect_cvc') {
                    $is_invalid[] = 'The card’s security code is incorrect. Check the card’s security code or use a different card.';
                } elseif ($e->getError()->code == 'insufficient_funds') {
                    $is_invalid[] = 'Insufficient funds. Please add more funds to your account and try again.';
                } else {
                    $is_invalid[] = 'Unknown Error with your credit card. Please try again later.';
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
            } else {
                update_user_meta($user_id, 'wp__stripe_customer_id', $stripe_cus_id);
                update_user_meta($user_id, 'status', 'active');

                $this->enroll_athletes($user_id, $fields);

                $order_id = $this->create_order($user_id, $registration_fee, array('type' => 'fee_payment'));
                update_post_meta($order_id, '_stripe_intent_id', $payment_intent->id);
                $stripe->paymentIntents->update(
                    $payment_intent->id,
                    ['metadata' => ['order_id' => $order_id], 'description' => 'Gymnastics of York - Order #'. $order_id]
                );

                if (!empty($fields['referral']['customer_id'])) {
                    $referrer_id = $fields['referral']['customer_id'];
                    $referrer = get_user_by('id', $referrer_id);

                    if (!empty($referrer)) {
                        $this->create_order($referrer_id, get_option('referral_credit'), array('type' => 'referral_credit', 'new_member' => $user));
                    }
                }

                $info = array();
                $info['user_login'] = sanitize_user($user->user_login);
                $info['user_password'] = $fields['password'];
                wp_signon($info, false);
            }
    }

    function create_order($customer_id, $amount, $args) {
        global $wpdb;
    
        $user = get_user_by('id', $customer_id);
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
    
    
        $sql = 'SELECT ID FROM wp_posts WHERE post_title = %s AND post_type = "product"';
        $where = ['Staff Payment'];
        $product_id = $wpdb->get_results($wpdb->prepare($sql, $where));
        if ($product_id) {
            $product = wc_get_product( $product_id[0]->ID );
            $order->add_product( $product, 1 );
        }

        if ($args['type'] == 'referral_credit') {
            $new_customer = $args['new_member']->first_name . ' ' .$args['new_member']->last_name;
            $note = 'Credit for Referral of user '.$new_customer.' ID #'.$args['new_member']->ID;
            $item_name = 'Referral Credit';
        } else if ($args['type'] == 'fee_payment') {
            $note = 'Payment for Registration Fee on '.date('Y-m-d');
            $item_name = 'Registration Fee';
            $order->update_status( 'processing', $note);
        } else {
            $note = 'Invoice for Registration Fee on '.date('Y-m-d').' generated by System';
            $item_name = 'Registration Fee';

            $comment_invoice = array(
                'comment_author' => $order->get_id(),
                'comment_content' => 'Invoice generated for $'.$amount,
                'user_id' => $user->ID,
                'comment_meta'         => array(
                    'is_invoice'       => sanitize_text_field(1),
                    'invoice_total'    => $amount
                    )
                );
            wp_insert_comment($comment_invoice);

            $order->update_status( 'pending', $note);
        }
    
        
        foreach( $order->get_items() as $item_id => $item ){
            $item->set_name( $item_name );
            $item->set_subtotal($amount); 
            $item->set_total( $amount);
            $item->calculate_taxes();
            $item->save();
        }
    
        $order->calculate_totals();
        
        if ($args['type'] == 'referral_credit' && $order->get_id()) {
            $template = get_posts(array('post_type' => 'email_template', 'name' => 'Referral Credit in your Gymnastics of York Account'))[0];
            $subject = $template->post_title;
            $template_message = $template->post_content;
            $order->update_status( 'processing', $note);
            
            $from = !empty(get_option('custom_note_from')) ? get_option('custom_note_from') : 'ca@gymnasticsofyork.com';
            $replyto = !empty(get_option('custom_note_replyto')) ? get_option('custom_note_replyto') : 'ca@gymnasticsofyork.com';
            $bcc  = !empty(get_option('custom_note_bcc')) ? get_option('custom_note_bcc') : 'ca@gymnasticsofyork.com';
            
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
            $headers[] = 'From: <'.$from.'>';
            $headers[] = 'Reply-To: <'.$replyto.'>';
            $headers[] = 'Bcc: '.$bcc;

            $message = str_replace(
                array('{{user_name}}', '{{new_customer}}', '{{credit_amount}}'),
                array($user->user_login, $new_customer, $amount),
                $template_message
            );
            
            wp_mail($user->user_email, $subject, $message, $headers);
        }
    
        return $order->get_id();
    }
    

    public function validate_registration($fields) {

        $is_valid = true;

        echo '<style>';
        foreach ($fields as $key => $field) {
            if ($key !== 'athletes') {          

                if ($key !== 'guardian_first_name_2' && $key !== 'guardian_last_name_2' && $key !== 'guardian_mobile_phone_2' && $key !== 'referral' && $key !== 'free_class_coupon') {
                    $validated = sanitize_text_field( $field );
    
                    if (empty($validated) ) {
                        echo '#membership_form #'.$key.' + .notice-warning {
                            display:  block!important;
                        }';
                        $is_valid = false;
                    } else {
                        if ($key == 'start_date') {
                            if ($field < date('Y-m-d')) {
                                echo '#membership_form #start_date + .notice-warning {
                                        display:  block!important;
                                    }';
                                $is_valid = false;
                            }
                        }

                        if ($key == 'password') {
                            if (strlen($field) < 8) {
                                echo '#membership_form #password + .notice-warning {
                                        display:  block!important;
                                    }';
                                $is_valid = false;
                            }
                        }
                    } 
                }

            } 
            else {
                foreach($field as $key => $athlete) {
                    foreach($athlete as $i => $item) {
                        if ($i !== 'child_middle_name') {

                            if ($i == 'enrolled') {
                                foreach($item as $enroll) {
                                    if (empty($athlete[$enroll])) {
                                        echo '#membership_form #'.$i.'_'.$key.' + .notice-warning {
                                            display:  block!important;
                                        }';
                                        $is_valid = false;
                                    }
                                }
                            } else {
                                $validated = sanitize_text_field( $item);
                                if (empty($validated) ) {
                                    echo '#membership_form #'.$i.'_'.$key.' + .notice-warning {
                                            display:  block!important;
                                        }';
                                    $is_valid = false;
                                }
                            }
                            
                        }
                    }
                }
            }
        }

        echo '</style>';

        return $is_valid;
    }

    function register_new_customer($fields, $is_coupon) {

        $headers = array('Content-Type: text/html; charset=UTF-8');
        $is_email = get_user_by('email', $fields['email']);

        if (empty($is_email)) {
            $user_id = wp_create_user( $fields['username'], $fields['password'], $fields['email'] );
            $user = new WP_User( $user_id );

            if ( ! ( is_wp_error( $user ) ) ) {

                $user->remove_role( 'subscriber' );
                $user->add_role( 'customer' );
            
                update_user_meta( $user_id, 'first_name', $fields['first_name'] );
                update_user_meta( $user_id, 'last_name', $fields['last_name'] );
                update_user_meta( $user_id, 'new_customer', true );
                update_user_meta($user_id, 'billing_first_name', $fields['first_name']);
                update_user_meta($user_id, 'billing_last_name', $fields['last_name']);
                update_user_meta($user_id, 'billing_phone', $fields['billing_phone']);
                update_user_meta($user_id, 'billing_address_1', $fields['billing_address_1']);
                update_user_meta($user_id, 'billing_state', $fields['billing_state']);
                update_user_meta($user_id, 'billing_city', $fields['billing_city']);
                update_user_meta($user_id, 'billing_postcode', $fields['billing_postcode']);
                update_user_meta($user_id, 'due_registration_month', date('F'));
                update_user_meta($user_id, 'status', 'pending');

                
                if (!empty($fields['referral']['type']) && $fields['referral']['type'] == 'Another Customer') {
                    !empty($fields['referral']['customer_name']) ? update_user_meta($user_id, 'referral', $fields['referral']['customer_name']) : null;
                } else {
                    !empty($fields['referral']['type']) ? update_user_meta($user_id, 'referral', $fields['referral']['type']) : null;
                }
            
                $headers = array('Content-Type: text/html; charset=UTF-8');
                if (get_userdata( $user_id )) {
                    $welcome_template = get_posts(array(
                        'post_type' => 'email_template',
                        'posts_per_page' => -1,
                        'post_name__in' => array('welcome-to-gymnastics-of-york')
                        ));        
            
                    if (!empty($welcome_template)) {
            
                        $message = str_replace(
                            ['%user_firstname%'],
                            [$fields['first_name']],
                            $welcome_template[0]->post_content
                        );
            
                        wp_mail($fields['email'], $welcome_template[0]->post_title, $message, $headers);
                    }

                $link_1 = '<a target="_blank" href="'.get_site_url().'/wp-admin/admin.php?page=user-information-edit&user='.$user_id.'&child=no">here</a>';
                $link_2 = '<a target="_blank" href="'.get_site_url().'/wp-admin/user-edit.php?user_id='.$user_id.'&wp_http_referer=%2Fwp-admin%2Fusers.php">here</a>';
            
                $notice_admin_template = get_posts(array(
                    'post_type' => 'email_template',
                    'posts_per_page' => -1,
                    'post_name__in' => array('new-user-registration-user_name')
                ));
                
                if ($is_coupon) {
                    $new_coupon = get_posts(array(
                        'post_type' => 'email_template',
                        'posts_per_page' => -1,
                        'post_name__in' => array('user_name-has-successfully-entered-a-free-class-coupon')
                        ));
                }
                
                $administrators = get_users();
            
                foreach ($administrators as $admin) {
                    if ($admin->roles[0] == 'administrator') {
                        if (!empty($notice_admin_template)) {
                
                            $message = str_replace(
                                ['{{admin_username}}', '{{user_name}}', '{{user_email}}', '{{user_registration}}', '{{link_1}}', '{{link_2}}'],
                                [$admin->user_login, $fields['username'], $fields['email'], date('Y-m-d'), $link_1, $link_2],
                                $notice_admin_template[0]->post_content
                            );
            
                            $subject = str_replace(
                                ['{{user_name}}'],
                                [$fields['username']],
                                $notice_admin_template[0]->post_title
                            );
            
                            wp_mail($admin->user_email, $subject, $message, $headers);
                        }

                        if (isset($new_coupon) && !empty($new_coupon)) {
                            $subject = str_replace(
                                ['{{user_name}}'],
                                [$fields['username']],
                                $new_coupon[0]->post_title
                            );
                            
                            $message = str_replace(
                                ['{{admin_username}}', '{{user_first_name}}', '{{user_email}}'],
                                [$admin->user_login, $fields['username'], $fields['email']],
                                $new_coupon[0]->post_content
                            );

                            wp_mail($admin->user_email, $subject, $message, $headers);
                        } 
                    }
                }

                if ($is_coupon) {
                    $new_action = array('action' => 'Follow Up', 'name' => 'Mr. A');
                    update_user_meta($user_id, 'action_required', array($new_action));
                }

                ?>
                <style>
                    .billing-registration-success.notice-success {
                        display: block !important;
                    }
                </style>
                <?php

                return $user_id;
            
            } else {
                ?>
                <style>
                    .billing-registration-failed.notice-warning {
                        display: block !important;
                    }
                </style>
                <?php
            }

        } else {
            ?>
            <style>
                .billing-registration-failed.notice-warning {
                    display: block !important;
                }
            </style>
            <?php
        }

        } else {
            ?>
            <style>
                #membership_form #email + .notice-warning {
                    display: block !important;
                }
            </style>
            <?php
        }
    }

    public function register_athletes($user_id, $fields, $is_coupon) {
        $parent_user_id              = $user_id;
        $multiaccounts_maximum_limit = 500;
        $max_free_class = get_option('max_free_registration');
        $athlete_count = 0;

        foreach($fields['athletes'] as $athlete) {
            $athlete_count += 1;

            $current_multiaccounts_number = get_user_meta( $parent_user_id, 'smuac_multiaccounts_number', true );
            if ( '' == $current_multiaccounts_number ) {
                $current_multiaccounts_number = 0;  
            }
            if ( intval( $current_multiaccounts_number ) < $multiaccounts_maximum_limit ) {
    
                $date = date('mdYHis');
                $email_domain_extension = '@gymnasticsofyork.com';

                $parent_name = $fields['first_name'];
		        $parent_name = preg_replace('/[^a-zA-Z0-9._@-]/', '', $parent_name);
                $childemail = $parent_name.'_'.$athlete['child_first_name'].'_'.$date.$email_domain_extension;
    
                $childusername = $athlete['child_first_name'].$athlete['child_last_name'];
                
                $validated_childusername = check_existing_athlete($childusername);
    
                $child_user_id = wc_create_new_customer( $childemail, $validated_childusername, $fields['password'] );

                if ( ! ( is_wp_error( $child_user_id ) ) ) {

    
                    update_user_meta( $child_user_id, 'first_name', $athlete['child_first_name'] );
                    update_user_meta( $child_user_id, 'last_name', $athlete['child_last_name'] );
                    update_user_meta( $child_user_id, 'child_birth', $athlete['child_birth'] );
                    update_user_meta( $child_user_id, 'smuac_account_type', 'multiaccount' );
                    update_user_meta( $child_user_id, 'smuac_account_type', 'multiaccount' );
                    update_user_meta( $child_user_id, 'smuac_account_parent', $parent_user_id );
                    update_user_meta( $child_user_id, 'smuac_account_name', $validated_childusername );
                    update_user_meta( $child_user_id, 'smuac_account_phone', '' );
                    update_user_meta( $child_user_id, 'smuac_account_job_title', '' );
                    update_user_meta( $child_user_id, 'smuac_account_permission_buy', '' ); // true or false
                    update_user_meta( $child_user_id, 'smuac_account_permission_view_orders', '' ); // true or false
                    update_user_meta( $child_user_id, 'smuac_account_permission_view_bundles', '' ); // true or false
                    update_user_meta( $child_user_id, 'smuac_account_permission_view_discussions', ''); // true or false
                    update_user_meta( $child_user_id, 'smuac_account_permission_view_lists', '' ); // true or false
                    update_user_meta( $child_user_id, 'start_date', $fields['start_date']);
                    update_user_meta($child_user_id, 'guardian_first_name_1', $fields['first_name']);
                    update_user_meta($child_user_id, 'guardian_last_name_1', $fields['last_name']);
                    update_user_meta( $child_user_id, 'status_program_participant', 'pending' );
                    !empty($athlete['gender']) ? update_user_meta($child_user_id, 'gender', $athlete['gender']) : null;
                    !empty($athlete['child_middle_name']) ? update_user_meta($child_user_id, 'child_middle_name', $athlete['child_middle_name']) : null;
                    !empty($fields['guardian_first_name_2']) ? update_user_meta($child_user_id, 'guardian_first_name_2', $fields['guardian_first_name_2']) : null;
                    !empty($fields['guardian_last_name_2']) ? update_user_meta($child_user_id, 'guardian_last_name_2', $fields['guardian_last_name_2']) : null;
                    !empty($fields['guardian_mobile_phone_2']) ? update_user_meta($child_user_id, 'guardian_mobile_phone_2', $fields['guardian_mobile_phone_2']) : null;
                    
                    if ($athlete_count <= $max_free_class && $is_coupon) {
                        update_user_meta( $child_user_id, 'complementary_classes_number', '1' );
                    }

                    // set parent multiaccount details meta
                    $current_multiaccounts_number++;
                    update_user_meta( $parent_user_id, 'smuac_multiaccounts_number', $current_multiaccounts_number );
    
                    $current_multiaccounts_list = get_user_meta( $parent_user_id, 'smuac_multiaccounts_list', true );
                    $current_multiaccounts_list = $current_multiaccounts_list . ',' . $child_user_id;
                    update_user_meta( $parent_user_id, 'smuac_multiaccounts_list', $current_multiaccounts_list );
    
                    $userobj = new WP_User( $child_user_id );
                    $userobj->set_role( 'customer' );


                    ?>
                    <style>
                        .athlete-registration-success.notice-success {
                            display: block !important;
                        }
                    </style>
                    <?php

                    $result[] = $athlete['child_first_name'];


                } else {
                    ?>
                    <style>
                        .athlete-registration-failed.notice-warning {
                            display: block !important;
                        }
                    </style>
                    <?php
                }
            }
        }

        return $result;
    }

 
}