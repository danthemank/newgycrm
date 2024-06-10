<div class="edit-container">

    <?php $nonce = wp_create_nonce('edit_nonce'); ?>

    <div id="success-notice">
    </div>

    <div class="global-error notice notice-warning is-dismissible hidden"></div>
    <div class="global-success notice notice-success is-dismissible <?= isset($_GET['success']) ? '' : 'hidden'?>">Success: Payment added</div>

    <div class="account-user flex-container">
        <h2><?= $meta['first_name'][0] .' '. $meta['last_name'][0] ?></h2>
        <?= wp_loginout() ?>
        <span class="hidden" data-id="<?= $user_id ?>" id="user_id"></span>
    </div>

    <hr class="divider">

    <div class="flex-container input-container-md account-info">
        <div class="flex-container">
            <div id="main-account">
                <button type="button" class="modal-btn edit-btn" data-modal="#account_details">Account Details</button>
                <button type="button" class="modal-btn edit-btn" data-modal="#account_billing">Billing Information</button>
                <button type="button" class="modal-btn edit-btn" data-modal="#add_payment">Make a Payment</button>
                <a class="modal-btn" style="color: white;"  href="<?php echo esc_url( wc_get_account_endpoint_url( 'payment-methods' ) ); ?>">Payment Methods</a>
            </div>
            <div class="multiaccount">
                <div class="flex-container">
                    <h3>Athletes</h3>
                    <button class="btn edit-btn" data-modal="#add_subaccount" type="button">Add Athlete</button>
                </div>
            <?php
            if ($multiaccount_number >= 1) {
                    echo '<ul id="multiaccount-list">';
                        foreach ($child_name as $child) {
                            echo '<li class="child edit-btn" data-modal="#child_details" data-id="'.$child['child_id'].'"><span>'.$child['child_first_name'] . ' '.$child['child_last_name'].'</span><i class="fa-solid fa-pen-to-square" style="color: #ffffff;"></i></li>';
                        }
                    echo '</ul>';
                }
            ?>
            </div>
        </div>

        <div class="shop-container">
            <?= do_shortcode( '[woocommerce_my_account]' ) ?>
            <div class="left">
                <br>
                <div class="scroll-table">
                    <?= get_customer_transactions( $user_id) ?>;
                </div>
            </div>

            <div class="form-modals">
                
                <?php
                    require GY_CRM_PLUGIN_DIR . 'views/templates/public/profile_editing/main_account_editing.php';
                    require GY_CRM_PLUGIN_DIR . 'views/templates/public/profile_editing/account_billing_editing.php';
                    require GY_CRM_PLUGIN_DIR . 'views/templates/public/profile_editing/child_details_editing.php';
                    require GY_CRM_PLUGIN_DIR . 'views/templates/public/profile_editing/make_payment.php';
                    require GY_CRM_PLUGIN_DIR . 'views/templates/public/profile_editing/add_subaccount.php';
                ?>
            </div>
    </div>
</div>
<script>
     jQuery(document).ready(function($) {
        $('.custom-table tbody tr').each(function() {
            var creditCell = $(this).find('td.credit');
            var debitCell = $(this).find('td.debit');
            
            if (creditCell.text() !== "$0.00") {
                $(this).addClass('highlight-row-credit');
            }
            if (debitCell.text() !== "$0.00") {
                $(this).addClass('highlight-row-debit');
            }
        });
    });		
</script>