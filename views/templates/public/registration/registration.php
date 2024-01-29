<div class="membership_form">
        
    <div style="margin-bottom: 1rem;" class="billing-registration-failed notice notice-warning is-dismissible hidden">Billing Account Registration Failed. Please try again later.</div>
    <div style="margin-bottom: 1rem;" class="billing-registration-success notice notice-success is-dismissible hidden">Billing Account Registration Succeded.</div>
    <div style="margin-bottom: 1rem;" class="athlete-registration-failed notice notice-warning is-dismissible hidden">Athlete Registration Failed.</div>
    <div style="margin-bottom: 1rem;" class="athlete-registration-success notice notice-success is-dismissible hidden">Athlete Registration Succeded.</div>
    <div style="margin-bottom: 1rem;" class="global-error notice notice-warning is-dismissible hidden"></div>
    <div style="margin-bottom: 1rem;" class="payment-success notice notice-success is-dismissible hidden">Registration Succeded.</div>

    <form id="membership_form" class="registration" action="<?= get_permalink() ?>" method="post">
        <input type="hidden" name="nonce" value="<?= $nonce ?>">

        <div class="form-fields billing-account-section">
            <div class="flex-container input-container-lg">
                <div class="form-section">
                    <h2 class="custom-registration-form-heading">Billing Account Registration</h2>
                    <div class="form-row flex-container input-container-lg custom-registration-form-field">

                        <div>
                            <label for="first_name">First Name *</label>
                            <input type="text" class="reg-input" name="first_name" id="first_name"/>
                            <div class="notice notice-warning is-dismissible hidden"><p>Error: Please enter your first name</p></div>
                        </div>
                        <div style="margin: 0 !important">
                            <label for="last_name">Last Name *</label>
                            <input type="text" class="reg-input" name="last_name" id="last_name" />
                            <div class="notice notice-warning is-dismissible hidden"><p>Error: Please enter your last name</p></div>
                        </div>
                    </div>

                    <input type="hidden" name="username" id="username" />
                    
                    <div class="form-row">
                        <label for="email">Email Address *</label>
                        <input type="email" name="email" id="email" />
                        <div class="notice notice-warning is-dismissible hidden"><p>Please enter a valid email address.</p></div>
                        <div class="notice notice-warning is-dismissible hidden email-exists"><a href="<?= site_url().'/login' ?>">Account already exists with this email address, click here to sign in.</a></div>
                    </div>
                    <div class="form-row">
                        <label for="password">Password *</label>
                        <input type="password" name="password" min="8" id="password"/>
                        <div class="notice notice-warning is-dismissible hidden"><p>Please enter a password. Password must be 8 characters minimum.</p></div>
                    </div>

                    <div class="form-row flex-container input-container-lg custom-registration-form-field">
                        <div>
                            <label for="billing_phone">Phone Number *</label>
                            <input type="tel" class="int-phone" name="billing_phone" id="billing_phone"/>
                            <div class="notice notice-warning is-dismissible hidden"><p>Please insert a valid phone number.</p></div>
                            <div class="notice notice-warning is-dismissible hidden" data-id="billing_phone"><p></p></div>
                        </div>
                        <div style="margin: 0 !important">
                            <label for="billing_address_1">Billing Address *</label>
                            <input type="text" name="billing_address_1" id="billing_address_1"/>
                            <div class="notice notice-warning is-dismissible hidden"><p>Please enter an address.</p></div>
                        </div>
                    </div>

                    <div class="form-row flex-container input-container-lg custom-registration-form-field">
                        <div style="margin: 0 !important">
                            <label for="billing_city">City *</label>
                            <input type="text" name="billing_city" id="billing_city"/>
                            <div class="notice notice-warning is-dismissible hidden"><p>Please enter a city.</p></div>
                        </div>
                        <div style="margin: 0 !important">
                            <label for="billing_state">State *</label>
                            <input type="text" name="billing_state" id="billing_state"/>
                            <div class="notice notice-warning is-dismissible hidden"><p>Please enter a state.</p></div>
                        </div>
                        <div style="margin: 0 !important">
                            <label for="billing_postcode">Zip Code *</label>
                            <input type="text" name="billing_postcode" id="billing_postcode"/>
                            <div class="notice notice-warning is-dismissible hidden"><p>Please enter a zip code.</p></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="custom-registration-form-heading">Guardian 2</h2>
                    <div class="form-row">
                        <label for="first_name">First Name</label>
                        <input type="text" class="reg-input" name="guardian_first_name_2" id="guardian_first_name_2"/>
                    </div>
                    <div class="form-row">
                        <label for="last_name">Last Name</label>
                        <input type="text" class="reg-input" name="guardian_last_name_2" id="guardian_last_name_2"/>
                    </div>
                    <div class="form-row">
                        <label for="guardian_mobile_phone_2">Phone Number</label>
                        <input type="tel" class="int-phone" name="guardian_mobile_phone_2" id="guardian_mobile_phone_2"/>
                        <div class="notice notice-warning is-dismissible hidden" data-id="guardian_mobile_phone_2"><p></p></div>
                    </div>
                </div>

            </div>
        </div>

        <div class="form-fields athlete-section form-section">
            <?php
                $athlete_id = 0;
                $count = 1; 
                require GY_CRM_PLUGIN_DIR . 'views/templates/public/registration/athlete_form.php'
            ?>
        </div>

        <div class="form-section"><button type="button" class="add-athlete-btn">Enroll Another Athlete</button></div>

        <div class="form-fields referrals-section">
            <div class="flex-container input-container-lg">
                <div class="form-section">
                    <h2>Referrals</h2>
                    <div class="form-row">
                        <label for="referral_type">How did you join?</label>
                        <select name="referral[type]" id="referral_type">
                            <option value="">Select Option</option>
                            <option value="Another Customer">Another Customer</option>
                            <option value="Social Media">Social Media</option>
                            <option value="Staff">Staff</option>
                            <option value="Another Gym">Another Gym</option>
                            <option value="Event">Event</option>
                        </select>
                    </div>
                    <div class="form-row hidden referral-customers-container">
                        <label for="referral_customer">Please enter the customer's name</label>
                        <div class="referral-customers-list-container">
                            <input type="hidden" name="referral[customer_id]" id="referral_customer_id">
                            <input type="text" name="referral[customer_name]" id="referral_customer_name">
                            <ul class="hidden" id="referral_customers_list">
                                <?php
                                    $users = get_users();

                                    foreach($users as $user) {
                                        if (in_array('customer', $user->roles) && empty(get_user_meta($user->ID, 'smuac_account_parent', true))) {
                                            echo '<li data-id="'.$user->ID.'">'.$user->first_name.' '.$user->last_name.'</li>';
                                        }
                                    }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-fields payment-section">
            <div class="flex-container input-container-lg">
                <div class="form-section">
                    <h2>Registration</h2>
                    <div class="form-row start-date-row">
                        <label for="start_date">Start Date *</label>
                        <input type="date" class="reg-input" value="<?= date('Y-m-d') ?>" name="start_date" id="start_date"/>
                        <div class="notice notice-warning is-dismissible hidden"><p>Please enter a date in the future.</p></div>
                    </div>

                    <div class="form-row coupon-row">
                        <label for="free_class_coupon">Free Class Coupon Code</label>
                        <div class="flex-container">
                            <input type="text" class="reg-input" name="free_class_coupon" id="free_class_coupon"/>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <label>Due today: $<?= get_option('registration_fee') ?> Registration Fee *</label>
                        <p class="disclaimer-sm">Credit Card transactions incur a 3.5% processing fee.</p>
                        <div class="add_card">
                        </div>
                        <div style="margin-top: 1rem;" class="notice notice-warning is-dismissible hidden" id="card_errors"></div>
                    </div>
                
                    <div class="form-row form-section submit-container">
                        <input type="submit" class="btn submit-btn" value="Register & Enroll Athletes">
                    </div>
                </div>
            </div>
        </div>

        <div class="absolute hidden">
            <div class="lds-ring">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
    
    </form>
</div>