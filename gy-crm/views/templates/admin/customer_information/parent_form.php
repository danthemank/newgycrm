<?php

if (str_contains($user_meta["smuac_multiaccounts_list"][0], ",")) {
    
    $children_list = explode(",", $user_meta["smuac_multiaccounts_list"][0]);
    unset($children_list[0]);
} else {
    $children_list = $user_meta["smuac_multiaccounts_list"][0];
}
?>
<main id="user_notes">
    <div class="global-error is-dismissible hidden"></div>
    <script>
        var userId = <?php echo $_GET['user']; ?>;
    </script>
    <div class="two_columns">
        <div class="right">
            <div class="associated-Children">
                <div class="flex-container">
                    <h2>Associated Athletes</h2>
                    <?php

                $is_capable = get_customer_information::get_customer_information_capability('edit_customer_information');

                if ($is_capable) {
                    ?>
                    <a class="add-child-btn" href="/wp-admin/admin.php?page=user-information-edit&user=<?= $_GET["user"] ?>&child=no&create=1">Add</a>
                    <?php
                }
                ?>
                </div>
                <?php
                echo "<ul class='ul-children'>";
                foreach ($children_list as $key => $value) :
                    $name = get_user_meta($value, "first_name", true);
                    $last_name = get_user_meta($value, "last_name", true);
                    echo "<a target='_blank' href='/wp-admin/admin.php?page=user-information-edit&user={$value}&child=yes'><li> $name </li></a>";
                endforeach;
                echo "</ul>";
                ?>
            </div>
            <div class="user_form">
                <?php
                require GY_CRM_PLUGIN_DIR . 'views/templates/admin/customer_information/billing_account.php';
                ?>

                <?php
                require GY_CRM_PLUGIN_DIR . 'views/templates/admin/customer_information/athlete_accounts.php';
                ?>

            </div>
        </div>
        
        <div class="left">
            <div id="order-notes" class="postbox " style="zoom: 1;">
                <div class="absolute">
                    <div class="lds-ring">
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                </div>
                <div class="postbox-header">
                    <h2 class="hndle ui-sortable-handle">Notes</h2>
                </div>
                <div class="inside">
                    <ul class="order_notes">
                    </ul>
                        <?php
                        if ($is_capable) {
                            ?>
                    <div class="add_note">
                        <p class="write_note">
                            <label for="add_order_note">Add note <span class="woocommerce-help-tip"></span></label>
                            <textarea type="text" name="content_note" id="content_note" class="input-text" cols="20" rows="5" required></textarea>
                        </p>
                        <p>
                            <label for="order_note_type" class="screen-reader-text">Note type</label>
                            <select name="order_note_type" id="order_note_type">
                                <option value="0">Private note</option>
                                <option value="1">Note to customer</option>
                                <option value="enrollment">Enrollment</option>
                                <option value="unenrollment">Unenrollment</option>
                            </select>
                            <button type="button" class="add_note_button button">Add</button>
                        </p>
                    </div>
                        <?php 
                        }
                        ?>
                </div>
            </div>
            <div class="flex-container billing-actions-container">
                <?php
                if ($is_capable) {
                ?>
                <div class="flex-container billing-notes-container">
                    <label for="billing_note">Billing Notes</label>
                    <input type="hidden" id="billing_note_user" value="<?= $_GET['user'] ?>">
                    <input type="text" id="billing_note" value="<?php 
                        $note = get_user_meta($_GET['user'], 'gycrm_billing_note', true);
                        echo $note;
                    ?>" <?= !empty($note) ? 'style="color: red;"' : '' ?>>
                </div>
                <?php
                    }
                $is_capable = get_customer_information::get_customer_information_capability('edit_pos');
                if ($is_capable) {
                    ?>
                <button type="button" class="modal-btn edit-btn easy-pos-btn" data-modal="#easy-pos-modal">Take Payment</button>
                <button type="button" class="modal-btn edit-btn easy-pos-btn" data-modal="#easy-pos-order-modal">Create Order</button>
                <?php
                    }
                ?>
            </div>
            <div class="hidden" id="easy-pos-modal">
                <?php
                if ($is_capable) {
                    echo do_shortcode('[easy_pos_shortcode set_user="'.$_GET['user'].'"]');
                }
                ?>
            </div>
            <div class="hidden" id="easy-pos-order-modal">
                <?php

                if ($is_capable) {
                    echo do_shortcode('[easy_pos_order_shortcode]');
                }
                ?>
            </div>
            <div class="scroll-table">
                <?php
                    if ($is_capable) {
                        $table = get_customer_transactions_edit($_GET['user']);
                        echo $table;
                    } else {
                        $table = get_customer_transactions($_GET['user']);
                        echo $table;
                    }
                ?>
            </div>
            <div class="future-invoices scroll-table">
                <label for="future-invoice" class="future-invoice-label"> Future invoices table</label>
                <?php
                    $table_future = get_future_invoices($_GET['user']);
                    echo $table_future;
                ?>
            </div>
        </div>
    </div>

    <div class="two_columns">
        <div class="left customer-payment-methods">
            <div class="flex-container payment-methods-header">
                <h2>Payment Methods</h2>
                <?php
                $is_capable = get_customer_information::get_customer_information_capability('edit_customer_information');
                if ($is_capable) {
                    ?>
                <div class="flex-container">
                    <button type="button" class="add-payment-method add-card-payment-method">Add Card</button>
                    <button type="button" class="add-payment-method add-ach-payment-method">Connect Bank Account</button>
                </div>
                <?php
                }
                ?>
            </div>
            <div class="manage-payment-methods">
                <div>
                    <table class="payment-methods-list">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Method</th>
                                <th>Expires</th>
                                <?php
                                if ($is_capable) {
                                    echo '<th></th>';
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if (!empty($payment_methods)) {
                            foreach ($payment_methods as $key => $card) {
                                $key += 1;
                                echo '<tr>
                                        <td>'.$key.'</td>
                                        <td>Ending in '.$card['last4'].'</td>
                                        <td>'.(!empty($card['exp_month']) && !empty($card['exp_year']) ? $card['exp_month'].'/'.$card['exp_year'] : '-').'</td>';
                                            if ($is_capable) {
                                            echo '<td>
                                                <button type="button" data-modal="#confirm_delete_pm" value="'.$card['id'].'" class="edit-btn remove_card delete-method-item-icon"></button>
                                                </td>';
                                            }
                                        echo '</tr>';
                                        }
                                    } else {
                                        echo '<tr>
                                        <td colspan="4">No items</td>
                                        </tr>';
                                        $add_form = true;
                                    }
                                    ?>
                        </tbody>
                    </table>
                </div>

                <?php 

                if ($is_capable) {
                    ?>
                <div class="<?= isset($add_form) ? '' : 'hidden' ?> add-payment-method-container">
                    <form method="POST" action="" id="add-pm-form" class="stripe-form">
                        <?php 
                            $nonce = wp_create_nonce('pm_nonce');
                        ?>
                        <input type="hidden" name="pm_nonce" value="<?= $nonce ?>">
                        <input type="hidden" name="setup_cm_id" id="setup_cm_id">
                        <input type="hidden" name="setup_cm_pm" id="setup_cm_pm">
                        <div class="add_card">
                        </div>
                        <div class="flex-container save-payment-method-btn"><input type="submit" class="add-payment-method" name="save_payment_method" value="Save"></div>
                    </form>
                    <div class="global-error is-dismissible hidden"></div>
                </div>
                <?php
                }
                ?>
            </div>
        </div>

        <?php
            $pm_nonce = wp_create_nonce('pm_nonce');
        ?>

        <div class="hidden" class="confirm-delete custom-modal" id="confirm_delete_pm">
            <div class="modal-header"></div>
            <form method="post" action="" class="flex-container confirm-delete">
                <input type="hidden" name="pm_nonce" value="<?= $pm_nonce ?>">
                <h2>Are you sure you want to delete this payment method?</h2>
                
                <input type="hidden" name="remove_card" id="remove_card">

                <div class="flex-container confirm-action">
                    <input type="submit" class="submit_user_info confirm-delete" value="Delete">
                    <button class="submit_user_info cancel-btn" type="button">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        

    </script>
</main>