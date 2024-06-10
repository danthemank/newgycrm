<div class="wrap easy-pos-order easy-pos-admin">
    <div class="modal-header">
        <h1>Add Order</h1>
    </div>
    <div class="global-success is-dismissible hidden">Success: </div>
    <div class="global-error hidden">Error: Unknown.</div>
    <div class="empty-fields global-error hidden">Error: </div>

    <div class="order-details">
        <form action="" method="POST" id="pos-order-form">
        <div class="flex-container">
            <h2>Order Details <?= isset($_GET['minvoice']) ? '- <a href="/wp-admin/admin.php?page=user-information-edit&user='. $_GET['user'] .'&child=no">'. get_user_by('id', $_GET['user'])->first_name . ' '. get_user_by('id', $_GET['user'])->last_name.'</a>' : '' ?>
            </h2>
            <?php if (isset($_GET['minvoice'])) {?>
            <div class="show-billing-history edit-btn submit_user_info flex-container" data-modal="#show_billing_history"><svg width="25px" height="25px" fill="#ffffff" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 442.04 442.04" xml:space="preserve" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M221.02,341.304c-49.708,0-103.206-19.44-154.71-56.22C27.808,257.59,4.044,230.351,3.051,229.203 c-4.068-4.697-4.068-11.669,0-16.367c0.993-1.146,24.756-28.387,63.259-55.881c51.505-36.777,105.003-56.219,154.71-56.219 c49.708,0,103.207,19.441,154.71,56.219c38.502,27.494,62.266,54.734,63.259,55.881c4.068,4.697,4.068,11.669,0,16.367 c-0.993,1.146-24.756,28.387-63.259,55.881C324.227,321.863,270.729,341.304,221.02,341.304z M29.638,221.021 c9.61,9.799,27.747,27.03,51.694,44.071c32.83,23.361,83.714,51.212,139.688,51.212s106.859-27.851,139.688-51.212 c23.944-17.038,42.082-34.271,51.694-44.071c-9.609-9.799-27.747-27.03-51.694-44.071 c-32.829-23.362-83.714-51.212-139.688-51.212s-106.858,27.85-139.688,51.212C57.388,193.988,39.25,211.219,29.638,221.021z"></path> </g> <g> <path d="M221.02,298.521c-42.734,0-77.5-34.767-77.5-77.5c0-42.733,34.766-77.5,77.5-77.5c18.794,0,36.924,6.814,51.048,19.188 c5.193,4.549,5.715,12.446,1.166,17.639c-4.549,5.193-12.447,5.714-17.639,1.166c-9.564-8.379-21.844-12.993-34.576-12.993 c-28.949,0-52.5,23.552-52.5,52.5s23.551,52.5,52.5,52.5c28.95,0,52.5-23.552,52.5-52.5c0-6.903,5.597-12.5,12.5-12.5 s12.5,5.597,12.5,12.5C298.521,263.754,263.754,298.521,221.02,298.521z"></path> </g> <g> <path d="M221.02,246.021c-13.785,0-25-11.215-25-25s11.215-25,25-25c13.786,0,25,11.215,25,25S234.806,246.021,221.02,246.021z"></path> </g> </g> </g></svg><span>Billing History</span></div>
            <?php } ?>
        </div>
        <form action="" method="POST">
            <input type="hidden" name="pos_nonce" value="<?= $nonce ?>">
            <div class="flex-container not-container">
            <?php
                if ($args['search_users'] == true) {
                    ?>
                    <div class="customer">
                        <div><label for="customer">Select Customer</label></div>
                        <div style="min-height: 30px">
                            <select class="hidden" name="customer" id="customer">
                                <option value="">Select customer</option>
                                <?php
                                $customers = $this->pos_get_all_customers();
                                if (!empty($customers)) {
                                    foreach ($customers as $customer) {
                                        if (isset($args['set_user']) && $args['set_user'] == $customer['id']) {
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
                                ?>
                            </select>
                        </div>
                    </div>

            <?php
            } else {
                ?>
                <input type="hidden" name="customer" value="<?= $_GET['user'] ?>">
        <?php
            }
            if (!isset($_GET['minvoice'])) {
            ?>
                <div class="due_date">
                    <div><label for="due_date">Due / Start Date</label></div>
                    <div><input type="date" name="due_date" id="due_date"></div>
                </div>
            <?php
            }
            ?>
            </div>
            <?php
            if (!isset($_GET['minvoice'])) {
            ?>
            <div>
                <input type="checkbox" name="is_monthly" id="is_monthly">
                <label for="due_date">Subscription type Invoice</label>
            </div>
            <div class="payment-plan-container">
                <div class="is-payment-plan">
                    <input type="checkbox" value="1" name="payment_plan[on]" id="payment_plan">
                    <label for="payment_plan">Payment Plan</label>
                </div>
                
                <div class="hidden payment-plan-due-container">
                    <div class="flex-container payment-plan-due not-container">
                        <label for="payment_plan">Due months: <span class="hidden" id="total_monthly"><span>$</span><span class="total-monthly"></span></span></label>
                        <input type="number" name="payment_plan[months]" id="payment_plan_months">
                    </div>
                </div>
            </div>

            <?php
            }
            ?>

            <div class="order-items-container">
                <?php
                if (isset($_GET['minvoice'])) {
                    echo $this->render_invoice($_GET['user']);
                } else {
                    ?>
                <div class="flex-container">
                    <h3>Items</h3>
                    <div class="flex-container">
                        <?php if (current_user_can('administrator')) { ?>
                        <button type="button" class="edit-btn add-item" data-modal="#pos_add_category">Create category</button>
                        <button type="button" class="edit-btn add-item" data-modal="#pos_add_product">Create product</button>
                        <?php } ?>
                        <button type="button" id="add_button" class="add-item">Add item</button>
                    </div>
                </div>
                <div class="panel">
                    <table>
                        <thead>
                            <tr>
                                <th></th>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="order-items">
                            <tr class="order-item" id="order-item-0">
                                <td class="delete-order-item"><div class="delete-order-item-icon"></div></td>
                                <td>
                                    <select class="order-item-product" name="order_item[0][product_id]" data-id="order-item-0" required>
                                        <?= pos_get_products() ?>
                                    </select>
                                </td>
                                <td>
                                    <label class="pos-dd">
                                        <div class="dd-button">Categories</div>
                                        <input type="checkbox" class="dd-input">
                                        <ul class="dd-menu" data-id="order-item-0">
                                            <?= pos_get_categories() ?>
                                        </ul>
                                    </label>
                                </td>
                                <td class="order-item-description"><input type="text" name="order_item[0][description]"></td>
                                <td><input type="number" step=".01" class="order-item-price" name="order_item[0][price]" required></td>
                                <td><input type="number" value="1" class="order-item-quantity" name="order_item[0][quantity]"></td>
                                <td><span>$</span><span class="order-item-total">0.00</></td>
                            </tr>
                        </tbody>
                        <tfoot class="totals">
                            <tr>
                                <th colspan="6"></th>
                                <td><span>$</span><span id="order-total">0.00</span>
                                <input type="hidden" name="payment_plan[total]" id="payment_plan_total">
                            </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                        <?php 
                        }
                        ?>
            </div>

            <div class="submit-order flex-container not-container">
                <?php
                if (isset($_GET['minvoice'])) {
                    echo '<input type="submit" value="Create & Send Invoice" class="add-item save-order" name="add_invoice"/>';
                } else {
                    ?>
                <input type="submit" value="Add order" class="add-item save-order" name="add_order"/>
                <?php 
                }
                ?>
            </div>

        </form>
    </div>
            
<?php
    require GY_CRM_PLUGIN_DIR . 'views/templates/admin/easy_pos/add_category.php';
    require GY_CRM_PLUGIN_DIR . 'views/templates/admin/easy_pos/add_product.php';

    if ($_GET['minvoice']) {
        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/easy_pos/view_billing_history.php';
    }
?>
</div>

