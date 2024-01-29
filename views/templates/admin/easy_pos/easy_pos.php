<div class="wrap easy-pos <?= $args['search_users'] == 'true' ? 'easy-pos-admin' : '' ?> ">
            <?php if (isset($args['my_account_page']) && $args['my_account_page'] == 'true') {
                echo '<h3 class="modal-header">Make Payment</h3>';
            } else {
                echo (isset($args['search_users']) && $args['search_users'] == false ? '<h3>' : '<h1>');
                echo '<div class="modal-header">Point of Sale</div>';
                echo (isset($args['search_users']) && $args['search_users'] == false ? '</h3>' : '</h1>');
            } 
            ?>

            <div class="global-error notice notice-warning is-dismissible hidden"></div>
            <div class="global-success notice notice-success is-dismissible hidden">Success: Payment added</div>
            <?php 
            if ($args['get_billing_history'] == 'true') {
                echo '<div id="balance_table">
                </div>';
                } 
            ?>
            <form method="post" action="" id="pos-form" class="stripe-form">
                <div class="flex-container" style="max-width: 1400px; width: 100%; gap: 2rem !important;">
                    <?php if ($args['set_user'] !== false && !isset($_GET['id'])) {
                        echo '<input type="hidden" id="customer" name="customer" value="'.$args['set_user'].'">';
                    }
                    ?>
                                <input type="hidden" id="pos_nonce" name="pos_nonce" value="<?= $nonce ?>">
                    <div class="easy-pos-form-container easy-pos-payment">
                    <?php 
                                if ($args['search_users'] == 'true') {
                    echo '<div class="flex-container">
                            <label for="customer">Select Customer:</label>
                            <select class="hidden" name="customer" id="customer">
                                <option value="no_account">No Account</option>';
                                // Fetch and populate the list of existing customers
                                $customers = $this->pos_get_all_customers();
                                if (!empty($customers)) {
                                    foreach ($customers as $customer) {
                                        if ($args['set_user'] == $customer['id']) {
                                            echo '<option value="' . $customer['id'] . '" data-children="';
                                                foreach($customer['children'] as $child) {
                                                    echo $child.',';
                                                }
                                            echo '" selected>' . $customer['parent_name'] . '</option>';
                                        } else {
                                            echo '<option value="' . $customer['id'] . '"  data-children="';
                                                foreach($customer['children'] as $child) {
                                                    echo $child.',';
                                                }
                                            echo '">' . $customer['parent_name'] . '</option>';
                                        }
                                    }
                                }
                            echo '</select>
                        </div>
                        ';
                        }
                        if ($args['my_account_page'] !== 'true') {
                        ?>
                        <div class="order-section flex-container">
                            <label for="order">Select Order:</label>
                            <select name="order" id="order" <?php if ($args['search_users'] == 'true' && $args['set_user'] == false) { echo 'disabled'; } ?> >
                                <?php
                                    if ($args['set_user']) {
                                        $orders = $this->get_orders_by_customer_id($args['set_user']);
                                        if (!empty($orders)) {
                                            echo '<option value="">Select order</option>';
                                            foreach ($orders as $order) {
                                                echo '<option value="' . $order['order_id'] . '">Invoice #' .$order['invoice_id']. ' ('.$order['date'].')</option>';
                                            }
                                        } else {
                                            echo '<option value="">No orders available</option>';
                                        }
                                    }
                                ?>
                            </select>
                        </div>
                        <?php
                            }
                        ?>
                        <div class="flex-container">
                            <label for="payment_method">Payment Method</label>
                            <select name="payment_method" id="payment_method">
                                <option value="card" selected>Card</option>
                                <option value="ach">ACH</option>
                            <?php
                            if ($args['my_account_page'] !== 'true') {
                            ?>
                                <option value="cash">Cash</option>
                                <option value="check">Check</option>
                                <option value="credit">Account Credit</option>
                                <option value="adjustment">Entry Adjustment</option>
                                        <?php
                                }
                            ?>
                            </select>
                        </div>
                        <?php
                            if ($args['my_account_page'] !== 'true') {
                        ?>
                        <div class="discount-section flex-container">
                            <label for="is_discount">Apply Discount?</label>
                            <div>
                                <input type="checkbox" name="is_discount" id="is_discount" value="1" />
                                <span class="hidden"><input class="percentage" type="number" step="any" name="percentage[discount]" id="discount_percentage" /><span>  %</span></span>
                            </div>
                        </div>
                        <div class="discount-container hidden">
                            <div class="flex-container not-margin">
                                <label for="amount_current">Amount to bring account current</label>
                                <input type="number" step="any" id="amount_current"  disabled/>
                            </div>
                        </div>
                        <?php
                            } else {
                                ?>
                                <div class="flex-container">
                                    <label for="my_account_amount">Amount To Bring Account Current</label>
                                    <span id="my_account_amount"></span>
                                </div>
                                <?php
                            }
                        ?>
                        <div class="flex-container not-margin">
                            <label for="amount">Amount <?= ($args['my_account_page'] !== 'true' ? 'Received' : '') ?></label>
                            <input type="number" step="any" name="amount" id="amount" />
                        </div>
                        <?php if ($args['my_account_page'] !== 'true') {
                                ?>
                        <div class="discount-container hidden">
                            <div class="flex-container">
                                <label for="discount_given">Discount given</label>
                                <input type="number" step="any" id="discount_given" disabled/>
                            </div>
                        </div>
                        <div class="flex-container fee-section">
                            <label for="is_fee">Apply Fee?</label>
                            <div>
                                <input type="checkbox" name="is_fee" id="is_fee" value="1" />
                                <span class="hidden"><input type="number" step="any" name="percentage[fee]" class="fee_percentage" /><span>  %</span></span>
                            </div>
                        </div>
                        <?php
                        } else {
                            ?>
                            <div class="flex-container" <?= $args['my_account_page'] == 'true' ? 'style="margin-top: 2rem;"' : ''?>>
                                <label>Processing Fee</label>
                                <span><span class="my-account-fee">3.5</span>%</span>
                                <input type="hidden" step="any" value="3.5" class="fee_percentage" name="percentage[fee]"/>
                                <input type="hidden" name="is_fee" value="1">
                            </div>
                            <?php
                        }
                        ?>
                        <div class="fee-container <?= ($args['my_account_page'] !== 'true') ? 'hidden' : ''?>">
                            <div class="flex-container not-margin">
                                <label><?= ($args['my_account_page'] !== 'true') ? 'Amount To Bring Account Current' : 'Total'?></label>
                                <input type="number" step="any" id="amount_fee"  disabled/>
                            </div>
                        </div>
                        <?php if ($args['my_account_page'] !== 'true') {
                                ?>
                        <div class="fee-container hidden">
                            <div class="flex-container">
                                <label>Fee given</label>
                                <input type="number" step="any" id="fee_given" disabled/>
                            </div>
                        </div>
                        <?php
                        }
                        ?>
                        <div class="payment-section" <?= $args['my_account_page'] == 'true' ? 'style="margin-top: 2rem;"' : ''?>>
                            <div>
                                <div class="payment-container credit-card">
                                    <h4>Credit Card</h4>
                                    <div class="card_exists hidden">
                                        <div class="flex-container">
                                            <div>
                                                <div>
                                                    <input type="radio" id="card_exists" value="card_exists" name="card_exists">
                                                    <label for="card_exists" for="card_id">Card ending in
                                                        <select name="card_id" id="card_id" class="last4">
                    
                                                        </select>
                                                    </label>
                                                </div>
                                                <div>
                                                    <input type="radio" id="add_card" value="add_card" name="card_exists">
                                                    <label for="add_card">Add new payment method</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="add_card">
                                    </div>
                                    <div style="margin-top: 1rem;" class="notice notice-warning is-dismissible hidden" id="card_errors"></div>
                                </div>
                                <div class="payment-container ach hidden">
                                    <h2>ACH</h2>
                                    <div class="notice notice-warning is-dismissible hidden" id="ach-error-message"></div>
                                    <div class="ach_exists hidden">
                                        <div class="flex-container">
                                            <div>
                                                <div>
                                                    <input type="radio" id="ach_exists" value="ach_exists" name="ach_exists">
                                                    <label for="ach_exists">Account ending in
                                                        <select name="ach_id" id="ach_id" class="last4">
                    
                                                        </select>
                                                    </label>
                                                </div>
                                                <div>
                                                    <input type="radio" id="add_ach" value="add_ach" name="ach_exists">
                                                    <label for="add_ach">Add new payment method</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                    
                                    <div class="add_ach">
                                        <button type="button" id="add_ach_pos">Connect New Account</button>
                                        <div class="disclaimer-sm hidden">Account Saved!</div>
                                        <input type="hidden" id="setup_id" name="setup_id"/>
                                        <input type="hidden" id="setup_pm" name="setup_pm"/>
                                    </div>
                                </div>
                                <?php if ($args['my_account_page'] !== 'true') {
                                        ?>
                                <div class="payment-container check hidden">
                                    <h2>Check</h2>
                                    <div class="flex-container">
                                        <label for="check_number">Check Number</label>
                                        <input type="text" id="check_number" name="check_number">
                                    </div>
                                </div>
                                <div class="payment-container cash hidden">
                                    <h2>Cash</h2>
                                    <div class="flex-container">
                                        <label for="cash_receipt">Receipt Number</label>
                                        <input type="text" id="cash_receipt" name="cash_receipt">
                                    </div>
                                </div>
                                <div class="payment-container note">
                                    <div class="flex-container">
                                        <label for="staff_note">Note (optional)</label>
                                        <input type="text" id="staff_note" name="staff_note">
                                    </div>
                                </div>
                                <?php
                            }
                        ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($args['my_account_page'] !== 'true') {
                    ?>
                    <div id="tab1" class="easy-pos-form-container easy-pos-email">
                        <div class="flex-container">
                            <label for="email_template">Email Template</label>
                            <select class="email_template" data-id="tab1" name="email_template" id="email_template">
                                <?= get_email_templates() ?>
                            </select>
                        </div>
                        <div class="flex-container">
                            <label for="subject">Subject</label>
                            <input type="text" name="email_subject" id="email_subject">
                        </div>
                        <div class="flex-container">
                            <label for="merge_tags">Merge Tags:</label>
                            <select id="merge_tags" class="regular-text" onchange="insertMergeTag()">
                                <option value=""> {{...}} </option>
                                <option value="{{user_name}}">User Name</option>
                                <option value="{{first_name}}">First Name</option>
                                <option value="{{last_name}}">Last Name</option>
                                <option value="{{full_name}}">Full Name</option>
                                <option value="{{athletes_first_name}}">Athletes First Name</option>
                                <option value="{{athletes_last_name}}">Athletes Last Name</option>
                                <option value="{{athletes_full_name}}">Athletes Full Name</option>
                            </select>
                        </div>
                        <div>
                            <div valign="top">
                                <label for="email_content">Message</label>
                            </div>
                            <div>
                            <?php if ($args['set_user'] !== false && !isset($_GET['id'])) {
                            ?>
                            <textarea name="email_content" id="email_content" cols="30" rows="10"></textarea>
                            <?php
                            } else {
                                echo wp_editor('', 'email_content', array('textarea_name' => 'email_content', 'default_editor' => 'tmce', 'editor_height' => '450px'));
                            }
                            ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    }
                    ?>
                </div>
                <input type="submit" id="submit_payment" name="submit_payment" value="Submit Payment">
            </form>

            <div class="absolute hidden">
                <div class="lds-ring">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>
        </div>