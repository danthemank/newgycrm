<div class="account-billing-editing edit-form" id="account_billing">
    <form method="post" action="<?= get_permalink() ?>">
        <input type="hidden" id="bill_edit_nonce" name="bill_edit_nonce" value="<?= $nonce ?>">

        <div class="modal-header">
            <h3>Billing</h3>
        </div>

        <div class="form-section">
            <div class="form-row flex-container custom-registration-form-field">
                <div>
                    <label for="billing_first_name"><?php _e( 'First Name', 'woocommerce' ); ?></label>
                    <input type="text" name="billing_first_name" value="<?= isset($meta['billing_first_name'][0]) ? $meta['billing_first_name'][0] : '' ?>" id="billing_first_name"    />
                </div>
                <div>
                    <label for="billing_last_name"><?php _e( 'Last Name', 'woocommerce' ); ?></label>
                    <input type="text" name="billing_last_name" value="<?= isset($meta['billing_last_name'][0]) ? $meta['billing_last_name'][0] : '' ?> " id="billing_last_name"    />
                </div>
            </div>
            <div class="form-row billing-address">
                <label for="billing_address_1"><?php _e( 'Billing Address', 'woocommerce' ); ?></label>
                <input type="text" name="billing_address_1" value="<?= isset($meta['billing_address_1'][0]) ? $meta['billing_address_1'][0] : '' ?>" id="billing_address_1"    />
            </div>
            <div class="form-row flex-container custom-registration-form-field">
                <div>
                    <label for="billing_city"><?php _e( 'City', 'woocommerce' ); ?></label>
                    <input type="text" name="billing_city" value="<?= isset($meta['billing_city'][0]) ? $meta['billing_city'][0] : '' ?>" id="billing_city"    />
                </div>
                <div>
                    <label for="billing_state"><?php _e( 'State', 'woocommerce' ); ?></label>
                    <input type="text" name="billing_state" value="<?= isset($meta['billing_state'][0]) ? $meta['billing_state'][0] : '' ?>" id="billing_state"    />
                </div>
                <div>
                    <label for="billing_postcode"><?php _e( 'Zip Code', 'woocommerce' ); ?></label>
                    <input type="number" name="billing_postcode" value="<?= isset($meta['billing_postcode'][0]) ? $meta['billing_postcode'][0] : '' ?>" id="billing_postcode"    />
                </div>
            </div>
            <div class="form-row flex-container input-container-md custom-registration-form-field">
                <div>
                    <label for="billing_phone"><?php _e( 'Phone', 'woocommerce' ); ?></label>
                    <input type="text" name="billing_phone" value="<?= isset($meta['billing_phone'][0]) ? $meta['billing_phone'][0] : '' ?>" id="billing_phone" />
                </div>
                    <?php
                        woocommerce_form_field(
                            'billing_phone_2',
                            array(
                                'type'        => 'tel',
                                'label'       => 'Alternate Phone',
                                'default'       => isset($meta['billing_phone_2'][0]) ? $meta['billing_phone_2'][0] : ''
                            ));
                    ?>
            </div>
        </div>

        <div>
            <div class="form-row flex-container custom-registration-form-field">
                <?php
                    woocommerce_form_field(
                        'alternate_email_1',
                        array(
                            'type'        => 'email',
                            'label'       => 'Alternate Email 1',
                            'default'       => isset($meta['alternate_email_1'][0]) ? $meta['alternate_email_1'][0] : ''
                        ));
                ?>
                <?php
                    woocommerce_form_field(
                        'alternate_email_2',
                        array(
                            'type'        => 'email',
                            'label'       => 'Alternate Email 2',
                            'default'       => isset($meta['alternate_email_2'][0]) ? $meta['alternate_email_2'][0] : ''
                        ));
                ?>
            </div>
            <div class="form-row flex-container input-container-md custom-registration-form-field">
                <?php
                    woocommerce_form_field(
                        'mobile_sms',
                        array(
                            'type'        => 'tel',
                            'label'       => 'Mobile/SMS',
                            'default'       => isset($meta['mobile_sms'][0]) ? $meta['mobile_sms'][0] : ''
                        ));
                ?>
                <?php
                    woocommerce_form_field(
                        'carrier',
                        array(
                            'type'        => 'select',
                            'label'       => 'Carrier',
                            'options' => $this->carrier_options
                        ));
                ?>
            </div>
        </div>
    
        <div class="form-row">
            <div class="hidden" id="carrier_option"><?= isset($meta['carrier'][0]) ? $meta['carrier'][0] : '' ?></div>
            <button type="submit" class="btn submit-btn" data-form="billing_account">Update</button>
        </div>
    
    </form>
</div>
