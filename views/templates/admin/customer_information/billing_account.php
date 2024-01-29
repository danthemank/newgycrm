<div>

    <div>
    <form action="" method="POST" class="customer-actions-container">
            <h2>Customer Actions</h2>
            <div class="user_field">
                <label for="action_required">Action</label>
                <select name="action_required" id="action_required" class="user-actions">
                    <option value="">Select Action</option>
                    <option value="Phone Call">Phone Call</option>
                    <option value="Email" >Email</option>
                    <option value="Billing Action">Billing Action</option>
                    <option value="Follow Up">Follow Up</option>
                </select>
            </div>
            <div class="user_field">
                <label for="action_name">Responsibility of</label>
                <select name="action_name" id="action_name">
                    <option value="">Select</option>
                    <option value="Mr. A">Mr. A</option>
                    <option value="Ms. Stacy">Ms. Stacy</option>
                    <option value="Michael" >Michael</option>
                    <option value="Ms. Betty" >Ms. Betty</option>
                    <option value="Dan Kemper" >Dan Kemper</option>
                    <option value="Office">Office</option>
                    <option value="Marketing">Marketing</option>
                </select>
            </div>

            <div id="current_actions_list">
                <div>
                    <label for="current_actions" class="current-actions">Current Actions</label>
                    <input type="checkbox" id="current_actions" style="display: none;"/>
                    <ul class="actions-list hidden">
                    <?= $this->get_user_actions_list($_GET['user']) ?>
                    </ul>
                </div>
            </div>

            <div class="user_field">
                <button type="button" data-user="<?= $_GET['user'] ?>" class="add-item save-customer-actions" name="assign_actions">Save Action</button>
                <div class="global-success is-dismissible hidden">Success: Action saved.</div>
            </div>
        </form>

        <form method="post" action="">
            <h2>Billing Account</h2>
            <div class="user_field">
                <input type="submit" class="submit_user_info submit-user-data" name="submit_data" value="Update User">
            </div>
            <?php wp_nonce_field('edit_user_info'); ?>
            <div class="user_field">
                <label for="start_date">Start Date</label>
                <?php
            if ($is_capable) {
                $user = get_users('ID', $_GET['user']);
                ?>
                <input type="text" name="start_date" id="start_date" value="<?= !empty(get_user_meta($children_list[1], 'start_date', true)) ? get_user_meta($children_list[1], 'start_date', true) : '' ?>" disabled>
                <?php 
                } else {
                    echo '<p>'.(!empty(get_user_meta($children_list[1], 'start_date', true)) ? get_user_meta($children_list[1], 'start_date', true) : '-').'</p>';
                }
            ?>
            </div>
            <div class="user_field">
                <label for="due_registration_month">Annual Registration Date</label>
                <?php

            if ($is_capable) {
                ?>
                <select name="due_registration_month" id="due_registration_month">
                    <option value="January" <?= isset($user_meta['due_registration_month'][0]) && $user_meta['due_registration_month'][0] == 'January' ? 'selected' : "" ?>>January</option>
                    <option value="February" <?= isset($user_meta['due_registration_month'][0]) && $user_meta['due_registration_month'][0] == 'February' ? 'selected' : "" ?>>February</option>
                    <option value="March" <?= isset($user_meta['due_registration_month'][0]) && $user_meta['due_registration_month'][0] == 'March' ? 'selected' : "" ?>>March</option>
                    <option value="April" <?= isset($user_meta['due_registration_month'][0]) && $user_meta['due_registration_month'][0] == 'April' ? 'selected' : "" ?>>April</option>
                    <option value="May" <?= isset($user_meta['due_registration_month'][0]) && $user_meta['due_registration_month'][0] == 'May' ? 'selected' : "" ?>>May</option>
                    <option value="June" <?= isset($user_meta['due_registration_month'][0]) && $user_meta['due_registration_month'][0] == 'June' ? 'selected' : "" ?>>June</option>
                    <option value="July" <?= isset($user_meta['due_registration_month'][0]) && $user_meta['due_registration_month'][0] == 'July' ? 'selected' : "" ?>>July</option>
                    <option value="August" <?= isset($user_meta['due_registration_month'][0]) && $user_meta['due_registration_month'][0] == 'August' ? 'selected' : "" ?>>August</option>
                    <option value="September" <?= isset($user_meta['due_registration_month'][0]) && $user_meta['due_registration_month'][0] == 'September' ? 'selected' : "" ?>>September</option>
                    <option value="October" <?= isset($user_meta['due_registration_month'][0]) && $user_meta['due_registration_month'][0] == 'October' ? 'selected' : "" ?>>October</option>
                    <option value="November" <?= isset($user_meta['due_registration_month'][0]) && $user_meta['due_registration_month'][0] == 'November' ? 'selected' : "" ?>>November</option>
                    <option value="December" <?= isset($user_meta['due_registration_month'][0]) && $user_meta['due_registration_month'][0] == 'December' ? 'selected' : "" ?>>December</option>
                </select>
                <?php 
                } else {
                    echo '<p>'.(isset($user_meta['due_registration_month'][0]) ? $user_meta['due_registration_month'][0] : "-").'</p>';
                }
            ?>
            </div>
            <div class="user_field">
                <label for="first_name">First Name*</label>
                <?php
            if ($is_capable) {
                ?>
                <input type="text" name="first_name" id="first_name" value="<?php echo (isset($user_meta['first_name'][0]) ? $user_meta['first_name'][0] : "") ?>" required>
                <?php
                } else {
                    echo '<p>'.(isset($user_meta['first_name'][0]) ? $user_meta['first_name'][0] : "-").'</p>';
                }
            ?>
            </div>
            <div class="user_field">
                <label for="last_name">Last Name*</label>
            <?php
            if ($is_capable) {
                ?>
                <input type="text" name="last_name" id="last_name" value="<?php echo (isset($user_meta['last_name'][0]) ? $user_meta['last_name'][0] : "") ?>" required>
                <?php
                } else {
                    echo '<p>'.(isset($user_meta['last_name'][0]) ? $user_meta['last_name'][0] : "-").'</p>';
                }
            ?>
            </div>
            <div class="user_field">
                <label for="email">Email Address*</label>
                <?php
            if ($is_capable) {
                ?>
                <input type="email" name="user_email" id="user_email" value="<?php echo (isset($user_meta['user_email']) ? $user_meta['user_email'] : "") ?>" required>
                <?php
                } else {
                    echo '<p>'.(isset($user_meta['user_email']) ? $user_meta['user_email'] : "-").'</p>';
                }
                ?>
            </div>
            <div class="user_field">
                <label for="alternate_email_1">Alternate Email 1</label>
                <?php
            if ($is_capable) {
                ?>
                <input type="email" name="alternate_email_1" id="alternate_email_1" value="<?php echo (isset($user_meta['alternate_email_1'][0]) ? $user_meta['alternate_email_1'][0] : "") ?>">
                <?php
                } else {
                    echo '<p>'.(isset($user_meta['alternate_email_1'][0]) ? $user_meta['alternate_email_1'][0] : "-").'</p>';
                }
                ?>
            </div>
            <div class="user_field">
                <label for="alternate_email_2">Alternate Email 2</label>
                <?php
            if ($is_capable) {
                ?>
                <input type="email" name="alternate_email_2" id="alternate_email_2" value="<?php echo (isset($user_meta['alternate_email_2'][0]) ? $user_meta['alternate_email_2'][0] : "") ?>">
                <?php
                } else {
                    echo '<p>'.(isset($user_meta['alternate_email_2'][0]) ? $user_meta['alternate_email_2'][0] : "-").'</p>';
                }
                ?>
            </div>
            <div class="user_field">
                <label for="mobile_sms">Mobile / SMS</label>
                <?php
            if ($is_capable) {
                ?>
                <input type="tel" name="mobile_sms" id="mobile_sms" value="<?php echo (isset($user_meta['mobile_sms'][0]) ? $user_meta['mobile_sms'][0] : "") ?>">
                <?php
                } else {
                    echo '<p>'.(isset($user_meta['mobile_sms'][0]) ? $user_meta['mobile_sms'][0] : "-").'</p>';
                }
                ?>
            </div>
            <div class="user_field">
                <label for="carrier">Carrier</label>
                <?php
            if ($is_capable) {
                ?>
                <select name="carrier" id="carrier" class="select " data-placeholder="">
                    <option value="">None</option>
                    <?php
                        foreach($this->carrier_options as $val => $option) {
                            if ($val == $user_meta['carrier'][0]) {
                                echo '<option value="'.$val.'" selected>'.$option.'</option>';
                            } else {
                                echo '<option value="'.$val.'">'.$option.'</option>';
                            }
                        }
                    ?>
                </select>
                <?php
                } else {
                    echo '<p>'.(isset($user_meta['carrier'][0]) ? $user_meta['carrier'][0] : "-").'</p>';
                }
                ?>
            </div>
            <div class="user_field">
                <label for="referral">Referral</label>
                <?php
                $referral = get_user_meta($_GET['user'], 'referral', true);
                if ($is_capable) {
                    ?>
                    <input type="text" name="referral" id="referral" value="<?= !empty($referral) ? $referral : '' ?>" disabled>
                    <?php 
                    } else {
                        echo '<p>'.(!empty($referral) ? $referral : '-').'</p>';
                    }
                ?>
            </div>
        </form>
    </div>


        <div class="email-message-container">
            <div class="flex-container">
                <form action="" method="POST" class="flex-container"><button type="submit" name="email_login_instructions" class="easy-pos-btn">Email Login Instructions</button></form>
                <button type="button" class="easy-pos-btn" id="reset-password-btn" data-user="<?php echo esc_attr($_GET['user']); ?>">Email Password Reset</button>
            </div>

            <form method="post">
                <div class="user_field">
                    <label for="send_email">Send Template Email</label>
                    <select name="send_email" id="send_email">
                        <?= get_email_templates() ?>
                    </select>
                </div>
                <div class="user_field">
                    <button type="submit" name="submit_send_email">Send Email Now</button>
                </div>
            </form>
            
            <form method="post">
                <div class="user_field">
                    <label for="send_message">Send Text Message Template</label>
                    <select name="send_message" id="send_message">
                        <?= get_email_templates() ?>
                    </select>
                </div>
                <div class="user_field">
                    <button type="submit" name="submit_send_message">Send Text Message Now</button>
                </div>
            </form>
        </div>


        <div>
            <h3>Billing Information</h3>
            <form method="post" action="">
            <?php wp_nonce_field('edit_user_info'); ?>
            <div class="user_field">
                <label for="billing_first_name">First Name*</label>
                <?php
            if ($is_capable) {
                ?>
                <input type="text" name="billing_first_name" id="billing_first_name" value="<?php echo (isset($user_meta['billing_first_name'][0]) ? $user_meta['billing_first_name'][0] : "") ?>" required>
                <?php
                } else {
                    echo '<p>'.(isset($user_meta['billing_first_name'][0]) ? $user_meta['billing_first_name'][0] : "-").'</p>';
                }
                ?>
            </div>
            <div class="user_field">
                <label for="billing_last_name">Last Name*</label>
                <?php
            if ($is_capable) {
                ?>
                <input type="text" name="billing_last_name" id="billing_last_name" value="<?php echo (isset($user_meta['billing_last_name'][0]) ? $user_meta['billing_last_name'][0] : "") ?>" required>
                <?php
                } else {
                    echo '<p>'.(isset($user_meta['billing_last_name'][0]) ? $user_meta['billing_last_name'][0] : "-").'</p>';
                }
                ?>
            </div>
            <div class="user_field">
                <label for="billing_address_1">Billing Address*</label>
                <?php
            if ($is_capable) {
                ?>
                <input type="text" name="billing_address_1" id="billing_address_1" value="<?php echo (isset($user_meta['billing_address_1'][0]) ? $user_meta['billing_address_1'][0] : "") ?>" required>
                <?php
                } else {
                    echo '<p>'.(isset($user_meta['billing_address_1'][0]) ? $user_meta['billing_address_1'][0] : "-").'</p>';
                }
                ?>
            </div>
            <div class="user_field">
                <label for="billing_city">City*</label>
                <?php
            if ($is_capable) {
                ?>
                <input type="text" name="billing_city" id="billing_city" value="<?php echo (isset($user_meta['billing_city'][0]) ? $user_meta['billing_city'][0] : "") ?>" required>
                <?php
                } else {
                    echo '<p>'.(isset($user_meta['billing_city'][0]) ? $user_meta['billing_city'][0] : "-").'</p>';
                }
                ?>
            </div>
            <div class="user_field">
                <label for="billing_state">State*</label>
                <?php
            if ($is_capable) {
                ?>
                <input type="text" name="billing_state" id="billing_state" value="<?php echo (isset($user_meta['billing_state'][0]) ? $user_meta['billing_state'][0] : "") ?>" required>
                <?php
                } else {
                    echo '<p>'.(isset($user_meta['billing_state'][0]) ? $user_meta['billing_state'][0] : "-").'</p>';
                }
                ?>
            </div>
            <div class="user_field">
                <label for="billing_postcode">Zip Code*</label>
                <?php
            if ($is_capable) {
                ?>
                <input type="text" name="billing_postcode" id="billing_postcode" value="<?php echo (isset($user_meta['billing_postcode'][0]) ? $user_meta['billing_postcode'][0] : "") ?>" required>
                <?php
                } else {
                    echo '<p>'.(isset($user_meta['billing_postcode'][0]) ? $user_meta['billing_postcode'][0] : "-").'</p>';
                }
                ?>
            </div>
            <div class="user_field">
                <label for="billing_phone">Phone*</label>
                <?php
            if ($is_capable) {
                ?>
                <input type="tel" name="billing_phone" id="billing_phone" value="<?php echo (isset($user_meta['billing_phone'][0]) ? $user_meta['billing_phone'][0] : "") ?>" required>
                <?php
                } else {
                    echo '<p>'.(isset($user_meta['billing_phone'][0]) ? $user_meta['billing_phone'][0] : "-").'</p>';
                }
                ?>
            </div>
            <div class="user_field">
                <label for="billing_phone_2">Alternate Phone</label>
                <?php
            if ($is_capable) {
                ?>
                <input type="tel" name="billing_phone_2" id="billing_phone_2" value="<?php echo (isset($user_meta['billing_phone_2'][0]) ? $user_meta['billing_phone_2'][0] : "") ?>">
                <?php
                } else {
                    echo '<p>'.(isset($user_meta['billing_phone_2'][0]) ? $user_meta['billing_phone_2'][0] : "-").'</p>';
                }
                ?>
            </div>
            <div class="user_field">
            <?php
            if ($is_capable) {
                ?>
                <input type="submit" class="submit_user_info" name="submit_data" value="Update User">
                <?php
                } else {
                    echo '<input type="hidden" id="is_edit_capable" value="false">';
                }
                ?>
            </div>
        </form>
    </div>


</div>