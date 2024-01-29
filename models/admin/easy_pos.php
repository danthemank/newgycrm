<?php 
class EasyPos{

    public $price_per_hour;
    public $registration_fee;

    public function __construct()
    {
        $this->price_per_hour = ['0,5' => get_option('halfhour_week'), '0.5' => get_option('halfhour_week'), 1 => get_option('onehour_week'), '1.5' => get_option('onehalfhour_week'), '1,5' => get_option('onehalfhour_week'), '2' => get_option('twohour_week'), '3' => get_option('threehour_week'), '4' => get_option('fourhour_week'), '5' => get_option('fivehour_week'), '6' => get_option('sixhour_week'), '7' => get_option('sevenhour_week'), '8' => get_option('eighthour_week'), '9' => get_option('ninehour_week'), '12' => get_option('twelvehour_week'), '15' => get_option('fifteenhour_week'), '20' => get_option('twentyhour_week'),];
        $this->registration_fee = get_option('registration_fee');
        
        add_action('admin_menu', array($this, 'pos_admin_menu'));


        add_shortcode('easy_pos_shortcode', array($this, 'easy_pos_shortcode'));
        add_shortcode('easy_pos_order_shortcode', array($this, 'easy_pos_order_shortcode'));
    }

    function pos_invoices_list() {
        echo '<h1 style="margin-top: 20px;"> Invoice List </h1>';
        
        // Agregar formulario de búsqueda
        echo '<form method="post">';
        echo '<label for="name">Name:</label>';
        echo '<input type="text" name="name" id="name">';
        
        echo '<label for="email">Email:</label>';
        echo '<input type="text" name="email" id="email">';
        
        echo '<label for="start_date">Start Date:</label>';
        echo '<input type="date" name="start_date" id="start_date">';
        
        echo '<label for="end_date">End Date:</label>';
        echo '<input type="date" name="end_date" id="end_date">';
        
        echo '<input type="submit" value="Search">';
        echo '</form>';
        
        // Renderizar la tabla de facturas
        echo '<table class="user_balance_table responsive_table">'
        .'<thead>
        <tr>
        <th>Name</th>
        <th>Invoice</th>
        <th>Date</th>
        <th>Amount</th>
        <th></th>
        </tr>
        </thead>
        <tbody>';
        $table = $this->invoices_list();
        foreach ($table as $tb) {
        echo '<tr id="invoice_'.$tb['id'].'">';
        echo '<td>' . $tb['name'] . '</td>';
        echo '<td>' . $tb['invoice'] . '</td>';
        echo '<td>' . $tb['date'] . '</td>';
        echo '<td>' . $tb['amount'] . '</td>';
        echo '<td class="edit-btn delete-invoice" data-invoiceid="' . $tb['id'] . '" data-modal="#confirm_delete_invoice">Delete</td>';
        echo '</tr>';
        }
        echo '</tbody>
        </table>';

        echo '<div class="hidden custom-modal" class="confirm-delete" id="confirm_delete_invoice">
            <div class="modal-header"></div>
            <form method="post" action="" class="flex-container confirm-delete">
                    <h2>Are you sure you want to delete Invoice #<span class="invoice-id"></span>?</h2>
                    
                    <input type="hidden" id="invoice_id">

                    <div class="flex-container confirm-action">
                        <input type="submit" class="submit_user_info confirm-delete" value="Delete">
                        <button class="submit_user_info cancel-btn" type="button">Cancel</button>
                    </div>
                </form>
            </div>
            ';
    }
    
    function invoices_list($name_filter = '', $email_filter = '', $start_date_filter = '', $end_date_filter = '') {
        global $wpdb;
        $args = array(
            'status' => 'any',
            'limit' => -1,
        );

        $name_filter = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $email_filter = isset($_POST['email']) ? sanitize_text_field($_POST['email']) : '';
        $start_date_filter = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $end_date_filter = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
    
        // Obtener IDs de suscripciones y pedidos
        $order_ids = array();
        $subscription_ids = array();
        $processed_invoices = array(); // Array para almacenar los números de factura procesados
        $invoices = array();
        $all_idsb = array();
        $all_ids = array();
        $subscriptions = wc_get_orders(array(
            'post_type' => 'shop_subscription',
            'post_status' => 'any',
            'numberposts' => -1,
        ));
        $orders = wc_get_orders($args);
    
        foreach ($subscriptions as $subscription) {
            $subs_id = $subscription->get_id();
            $subscription_ids[] = $subs_id;
        }
        foreach ($orders as $order) {
            $order_id = $order->get_id();
            $order_ids[] = $order_id;
        }
    
        $all_idsb = array_merge($subscription_ids, $order_ids);
        $all_ids = array_unique($all_idsb);
        
       foreach ($all_ids as $id) {
        $sql = "SELECT user_id, comment_id, comment_date, comment_content 
            FROM {$wpdb->comments} 
            WHERE comment_approved = 1
            AND user_id != 0
            AND comment_author = ".$id;
        
        if (!empty($name_filter)) {
            $sql .= " AND (user_id IN (SELECT ID FROM {$wpdb->users} wu
                    LEFT JOIN wp_usermeta AS first_name ON wu.ID = first_name.user_id AND first_name.meta_key = 'first_name'
                    LEFT JOIN wp_usermeta AS last_name ON wu.ID = last_name.user_id AND last_name.meta_key = 'last_name'
                    WHERE first_name.meta_value  LIKE '%$name_filter%' OR last_name.meta_value  LIKE '%$name_filter%'))";
        }
        if (!empty($email_filter)) {
            $sql .= " AND (user_id IN (SELECT ID FROM {$wpdb->users} WHERE user_email LIKE '%$email_filter%'))";
        }

        if (!empty($start_date_filter)) {
            $sql .= " AND comment_date >= '$start_date_filter'";
        }
        
        if (!empty($end_date_filter)) {
            $sql .= " AND comment_date <= '$end_date_filter'";
        }
    
        $customer_invoice = $wpdb->get_results($sql);
        foreach ($customer_invoice as $id=>$invoice) {
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
                $pattern = '/\$(\d+(\.\d{2})?)/';
                preg_match($pattern, $invoice->comment_content, $matches);
                $amount = floatval($matches[1]);
                $user_id = intval($invoice->user_id);
                $user_meta = get_user_meta($user_id);

                
                // Check if the invoice number has already been processed
                $invoice_number = '#' . $invoice->comment_id;
                if (in_array($invoice_number, $processed_invoices)) {
                    continue; // If already processed, omit this entry
                }
                // Add invoice number to the array of processed invoice numbers
                $processed_invoices[] = $invoice_number;
                
                $invoices[] =array(
                    'id' => $invoice->comment_id,
                    'name' => $user_meta['first_name'][0] . ' ' . $user_meta['last_name'][0],
                    'invoice' => $invoice_number,
                    'date' => date("Y-m-d", strtotime($invoice->comment_date)),
                    'amount' =>  wc_price(abs($amount)) 
                ) ;
            }

        }
        }
    
        usort($invoices, function ($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });
    
        return $invoices;
    }

    function pos_owes_list() {
        if (isset($_GET['card'])) {
            $filter = $_GET['card'];

            $clients = get_clients_with_outstanding_payments(array('card' => $filter));
        } else {
            $clients = get_clients_with_outstanding_payments();
        }
        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/easy_pos/accounts_owing.php';
    }

    function credit_card_list() {
        $clients = $this->cc_report('on_file');
        
        $table ='';
        foreach ($clients as $client){
            $table .= '<tr>
                        <td><a href="/wp-admin/admin.php?page=user-information-edit&user='. $client['id'] .'&child=no" target="_blank">'.$client['first_name'].'</a></td>
                        <td><a href="/wp-admin/admin.php?page=user-information-edit&user='. $client['id'] .'&child=no" target="_blank">'.$client['last_name'].'</a></td>
                        <td><select>';
                        foreach($client['cards'] as $card) {
                            $table .= '<option>'.$card.'</option>';
                        }
                $table .= '</select></td>
                        <td>';
                        if (isset($client['athletes'])) {
                            $table .= '<select>';
                            foreach($client['athletes'] as $athlete) {
                                $table .= '<option class="subaccount-name">'.$athlete.'</option>';
                            }
                            $table .= '</select>';
                        }
                $table .= '</td>
                        </tr>';
        }

        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/easy_pos/cc_report.php';
    }

    function no_credit_card_list() {
        $clients = $this->cc_report('not_on_file');
        $table ='';
        foreach ($clients as $client){
            $table .= '<tr>
                        <td><a href="/wp-admin/admin.php?page=user-information-edit&user='. $client['id'] .'&child=no" target="_blank">'.$client['first_name'].'</a></td>
                        <td><a href="/wp-admin/admin.php?page=user-information-edit&user='. $client['id'] .'&child=no" target="_blank">'.$client['last_name'].'</a></td>
                        <td>';
                        if (isset($client['athletes'])) {
                            $table .= '<select>';
                            foreach($client['athletes'] as $athlete) {
                                $table .= '<option class="subaccount-name">'.$athlete.'</option>';
                            }
                            $table .= '</select>';
                        }
                        $table .= '</td>
                        </tr>';
        }
        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/easy_pos/no_cc_report.php';
    }

    function cc_report($type) {
        $stripe = new \Stripe\StripeClient(STRIPE_TEST_KEY);
        $sub = $_GET['sub'];
        $users = get_users();
        $list = [];
        $on_file = [];
        foreach ($users as $user) {
            $is_parent = get_user_meta($user->ID, 'smuac_account_parent', true);
            if (empty($is_parent)) {
                $stripe_cus_id = get_user_meta($user->ID, 'wp__stripe_customer_id', true);
                if (!empty($stripe_cus_id)) {
                    try {
                        $pm = $stripe->customers->allPaymentMethods(
                                $stripe_cus_id,
                                ['type' => 'card']
                            );
                        if ($type == 'on_file') {
                            if (!empty($pm->data)) {
                                $list[] = $user;
                                foreach ( $pm->data as $payment_method ) {
                                    if ( count( $payment_method->card ) !== 0 ) {
                                        $cards[$user->ID][] = $payment_method->card->last4;
                                    }
                                }
                            }
                        } else {
                            if (empty($pm->data)) {
                                $list[] = $user;
                            }
                        }
                    } catch ( Exception $e ) {
                        continue;
                    }
                } else if ($type !== 'on_file') {
                    $list[] = $user;
                }
            }
        }
        foreach($list as $key => $user) {
            $user_list = [];
            $user_list['id'] = $user->ID;
            $children = explode(',', get_user_meta($user->ID, 'smuac_multiaccounts_list', true));
            foreach($children as $child) {
                if (!empty($child)) {
                    $first_name = get_user_meta($child, 'first_name', true);
                    $last_name = get_user_meta($child, 'last_name', true);
                    $user_list['athletes'][] = $first_name. ' ' .$last_name;
                }
            }
            $user_list['first_name'] = $user->first_name;
            $user_list['last_name'] = $user->last_name;
            if (empty($user->first_name) && empty($user->last_name)) {
                $user_list['first_name'] = $user->display_name;
            }
            if ($type == 'on_file') {
                $user_list['cards'] = $cards[$user->ID];
            }
            array_push($on_file, $user_list);
            if (isset($sub)) {
                if ($sub == 'has_subaccount') {
                    if (!isset($on_file[$key]['athletes'])) {
                        unset($on_file[$key]);
                    }
                } else if ($sub == 'no_subaccount') {
                    if (isset($on_file[$key]['athletes'])) {
                        unset($on_file[$key]);
                    }
                }
            }
        }
        if (isset($_GET['by'])) {
            $by = $_GET['by'];
        } else {
            $by = 'first_name';
        }
        if (isset($_GET['ord'])) {
            usort($on_file, function($a, $b) use ($by) {
                if ($by !== 'subaccount' && $by !== 'cards') {
                    if ($_GET['ord'] == 'DESC') {
                        return strcmp(strtolower($b[$by]), strtolower($a[$by]));
                    } else {
                        return strcmp(strtolower($a[$by]), strtolower($b[$by]));
                    }
                } else if ($by == 'subaccount') {
                    $athletes_a = isset($a['athletes']) ? $a['athletes'] : [];
                    $athletes_b = isset($b['athletes']) ? $b['athletes'] : [];
                    natsort($athletes_a);
                    natsort($athletes_b);
                    if ($_GET['ord'] == 'DESC') {
                        if ($athletes_a > $athletes_b) {
                            return -1;
                        } elseif ($athletes_b > $athletes_a) {
                            return 1;
                        } else {
                            return 0;
                        }
                    } else {
                        if ($athletes_b > $athletes_a) {
                            return -1;
                        } elseif ($athletes_a > $athletes_b) {
                            return 1;
                        } else {
                            return 0;
                        }
                    }
                } else if ($by == 'cards') {
                    natsort($a['cards']);
                    natsort($b['cards']);
                    if ($_GET['ord'] == 'DESC') {
                        if ($a['cards'] > $b['cards']) {
                            return -1;
                        } elseif ($b['cards'] > $a['cards']) {
                            return 1;
                        } else {
                            return 0;
                        }
                    } else {
                        if ($b['cards'] > $a['cards']) {
                            return -1;
                        } elseif ($a['cards'] > $b['cards']) {
                            return 1;
                        } else {
                            return 0;
                        }
                    }
                }
            });
        }
        return $on_file;
    }

    public function pos_add_order() {
        if (isset($_GET['minvoice'])) {
            do_shortcode('[easy_pos_order_shortcode]');
        } else {
            do_shortcode('[easy_pos_order_shortcode search_users="true"]');
        }
    }

    public function pos_order_scripts() {
        ?>

            
        <script>
            (function ($) {
                $(document).ready(function () {

                    let AddButton = $(".easy-pos-order #add_button");
                    let letters = 'abcdefghijklmnopqrstuvwxyz';
                    let products = $('.order-item-product').first().html()

                    let addDiscount = $('.add-discount')
                    let addFee = $('.add-fee')

                    $('body').on('change', '.dd-menu input', function() {
                        let catNames = []
                        let itemId = $(this).data('id')

                        $('#'+itemId+' .dd-menu input').each(function(i, el) {
                            if ($(el).prop('checked')) {
                                catNames.push($(el).parent().text())
                            }
                        })

                        if (catNames.length > 0) {
                            $('#'+itemId+' .dd-button').text(catNames.join(', '))
                        } else {
                            $('#'+itemId+' .dd-button').text('Categories')
                        }

                    })

                    $('body').on('change', '.order-item-product', function() {
                        let productId = $(this).val()
                        let itemId = $(this).data('id')

                        if (productId !== '') {
                            $.ajax({
                                url: "<?= admin_url("admin-ajax.php") ?>",
                                data: {
                                    action: 'pos_get_product_details',
                                    product_id: productId
                                },
                                success: function (response) {
                                    response = JSON.parse(response)
                                    let catNames = []

                                    if (response.cat.length > 0) {
                                        $('#'+itemId+' .dd-menu input').each(function(i, el) {
                                            if ($.inArray(parseFloat($(el).val()), response.cat) >= 0) {
                                                $(el).prop('checked', true)
                                                catNames.push($(el).parent().text())
                                            } else {
                                                $(el).removeAttr('checked')
                                            }
                                        })
    
                                        $('#'+itemId+' .dd-button').text(catNames.join(', '))
                                    } else {
                                        $('#'+itemId+' .dd-menu input').removeAttr('checked')
                                        $('#'+itemId+' .dd-button').text('Categories')
                                    }

                                    $('#'+itemId+' .order-item-description input').val(response.desc)

                                    if (parseFloat(response.price)) {
                                        $('#'+itemId + ' .order-item-price').val(parseFloat(response.price))
                                        let quantity = $('#'+itemId+' .order-item-quantity').val()
    
                                        changeOrderItemTotal(itemId, parseFloat(response.price), quantity)
                                        paymentPlans()
                                    }
                                }
                            })
                        }
                    })
                    
                    function calculateHours() {
                        $('.athlete-items').each(function(i, el) {
                            let athleteTotal = 0
                            let totalHours = $(el).find('.total-hours')

                            totalHours.each(function(i, el) {
                                athleteTotal += parseFloat($(el).val())
                            })

                            $.ajax({
                                url: "<?= admin_url("admin-ajax.php") ?>",
                                data: {
                                    action: 'get_class_fee',
                                    hours: athleteTotal
                                },
                                success: function (response) {
                                    response = JSON.parse(response)

                                    $('[data-athlete="'+$(el).data('athlete')+'"] .order-total').val(response)
                                    
                                    calculateInvoice()
                                }
                            })
                            
                        })

                    }

                    function calculateInvoice() {
                        let totalInvoice = 0
                        $('.panel .order-total').each(function(i, el) {
                            if ($(el).val()) {
                                totalInvoice += parseFloat($(el).val())
                            }
                        })

                        if (totalInvoice) {
                            $('#subtotal .order-total').text(totalInvoice)
                            $('#subtotal').data('amount', totalInvoice)
    
                            if ($('#discount-fee-section .order-item-discount').length < 1 &&
                                $('#discount-fee-section .order-item-fee').length < 1) {
                                    $('#total .order-total').text(totalInvoice)
                            }
    
                            if ($('#discount-fee-section .order-item-discount').length == 1) {
                                calculateDiscount()
                            }
    
                            if ($('#discount-fee-section .order-item-fee').length == 1) {
                                calculateFee()
                            }
                        }

                    }

                    $('body').on('focusout', '.total-hours', function() {
                        calculateHours()
                    })

                    $('body').on('focusout', '.order-total', function() {
                        calculateInvoice()
                    })

                    $('body').on('focusout', '.order-item-disfee', function() {
                        if ($(this).val() !== '') {
                            calculateDiscount()
                            calculateFee()
                        }
                    })

                    $('body').on('change', '.easy-pos-order .className', function() {
                        let id = $(this).parent().parent().attr('id');
                        $.ajax({
                            url: "<?= admin_url("admin-ajax.php") ?>",
                            data: {
                                action: 'get_class_hourly_fee',
                                id: $(this).val()
                            },
                            success: function (response) {
                                response = JSON.parse(response)
                                $('#'+id+' .total-hours').val(response.hours)

                                calculateHours()
                            }
                        })
                    })

                    $(AddButton).click(function (e) {

                        switch($(this).val()) {
                            case 'class':

                                let id = $(this).data('id')
                                let contenedor = $('.easy-pos-order #athlete_' +id)

                                
                                $.ajax({
                                    url: "<?= admin_url("admin-ajax.php") ?>",
                                    type: 'POST',
                                    data: {
                                        action: 'get_invoice_classes',
                                    },
                                    success: function (response) {
                                        let classes = JSON.parse(response)

                                        let rowId = ''
                                        for (let i = 0; i < 3; i++) {
                                            rowId += letters.charAt(Math.floor(Math.random() * letters.length));
                                        }
                                        
                                        let html = `
                                        <tr class="order-item" id="order-item-${id}">
                                            <td class="delete-order-item"><div class="delete-order-item-icon"></div></td>
                                            <td>
                                                <select class="className" data-athlete="${id}" name="order_item[${id}][classes][${rowId}][id]">
                                                ${classes}
                                                </select>
                                            </td>
                                            <td><input class="total-hours" type="number" value"0" name="order_item[${id}][classes][${rowId}][hours]" /></td>
                                        </tr>
                                        `

                                        $(contenedor).append(html);
                                    }
                                })
                            break;
                            case 'discount':
                                let discountContainer = $('#discount-fee-section')

                                let discountId = ''
                                for (let i = 0; i < 3; i++) {
                                    discountId += letters.charAt(Math.floor(Math.random() * letters.length));
                                }

                                if ($('#discount-fee-section .order-item-discount').length < 1) {
                                    let min = getCheaperAthlete()

                                    let discount = min * (10 / 100)
                                    let discounted = parseFloat($('#subtotal').data('amount')) - discount
    
                                    let discountItem = `
                                    <tr class="order-item" id="order-item-${discountId}">
                                        <td class="delete-order-item"><div class="delete-order-item-icon"></div></td>
                                        <td>Discount</td>
                                        <td><input type="text" name="item[discount][description]" value="Sibling Discount"/></td>
                                        <td><input type="number" step="0.1" value="10" name="item[discount][amount]" class="order-item-discount order-item-disfee" ></td>
                                        <td><span>-$</span><span class="order-discount-amount">${discount}</span></td>
                                        <td><span>$</span><span class="order-discount-total">${discounted}</span></td>
                                    </tr>
                                    `

                                    if ($('#discount-fee-section .order-item-fee').length == 1) {
                                        let fee = parseInt($('.order-fee-amount').text()) + discounted

                                        $('.order-item-fee-total').text(fee)

                                        discounted = fee
                                    }

                                    $('#order-total').text(discounted)
                                    $('#total .order-total').text(discounted)
    
                                    $(discountItem).prependTo(discountContainer);
                                    $(this).hide()
                                    $('tfoot').show()

                                }

                            break;
                            case 'fee':
                                let feeContainer = $('#discount-fee-section')

                                if ($('#discount-fee-section .order-item-fee').length < 1) {

                                    let feeId = ''
                                    for (let i = 0; i < 3; i++) {
                                        feeId += letters.charAt(Math.floor(Math.random() * letters.length));
                                    }

                                    let total = 0
                                    
                                    if ($('#discount-fee-section .order-item-discount').length == 1) {
                                        total = parseFloat($('.order-discount-total').text())
                                    } else {
                                        total = parseInt($('#subtotal').data('amount'))
                                    }

                                    let fee = total + 25

                                    let feeItem = `
                                    <tr class="order-item" id="order-item-${feeId}">
                                        <td class="delete-order-item"><div class="delete-order-item-icon"></div></td>
                                        <td>Fee</td>
                                        <td><input type="text" name="tem[fee][description]" value="Registration Fee"/></td>
                                        <td><input type="number" name="item[fee][amount]" class="order-item-fee order-item-disfee" step="0.1" value="25"/></td>
                                        <td><span>$</span><span class="order-fee-amount">25</span></td>
                                        <td><span>$</span><span class="order-fee-total">${fee}</span></td>
                                    </tr>
                                    `

                                    $('#order-total').text(fee)
                                    $('#total .order-total').text(fee)

                                    $(feeContainer).append(feeItem);
                                    $(this).hide()
                                    $('tfoot').show()

                                }
                            break;
                            default:
                                 
                                let rowId = ''
                                for (let i = 0; i < 3; i++) {
                                    rowId += letters.charAt(Math.floor(Math.random() * letters.length));
                                }

                                $.ajax({
                                    url: "<?= admin_url("admin-ajax.php") ?>",
                                    type: 'POST',
                                    data: {
                                        action: 'get_categories_display',
                                        row: rowId
                                    },
                                    success: function (response) {
                                        let items = JSON.parse(response)
                                        let contenedor = $(".easy-pos-order #order-items");
                                        
                                        let html = `
                                            <tr class="order-item" id="order-item-${rowId}">
                                                <td class="delete-order-item"><div class="delete-order-item-icon"></div></td>
                                                <td>
                                                    <select class="order-item-product" name="order_item[${rowId}][product_id]" data-id="order-item-${rowId}" required>
                                                    ${products}
                                                    </select>
                                                </td>
                                                <td>
                                                    <label class="pos-dd">
                                                        <div class="dd-button">Categories</div>
                                                        <input type="checkbox" class="dd-input" id="test">
                                                            <ul class="dd-menu" data-id="order-item-${rowId}">
                                                            ${items.categories}
                                                            </ul>
                                                    </label>
                                                </td>
                                                <td class="order-item-description"><input type="text" name="order_item[${rowId}][description]"></td>
                                                <td><input type="number" step=".01" class="order-item-price" name="order_item[${rowId}][price]" required></td>
                                                <td><input type="number" value="1" class="order-item-quantity" name="order_item[${rowId}][quantity]"></td>
                                                <td><span>$</span><span class="order-item-total">0.00</></td>
                                            </tr>
                                        `
                                        $(contenedor).append(html);
                                    }
                                })
                            break;
                        }


                    });

                    function getCheaperAthlete() {
                        let values = []

                        $('.panel .order-total').each(function(i, el) {
                            if ($(el).val()) {
                                values.push(parseFloat($(el).val()))
                            }
                        });

                        let min = values.reduce(function(a, b) {
                            return a < b ? a : b;
                        });

                        return min
                    }

                    function calculateDiscount() {

                        let min = getCheaperAthlete()

                        let discount = min * (parseFloat($('.order-item-discount').val()) / 100)
                        let discounted = parseFloat($('#subtotal').data('amount')) - discount

                        if (discount && discounted) {
                            $('.order-discount-amount').text(discount)
                            $('.order-discount-total').text(discounted)
                            $('#order-total').text(discounted)
                            $('#total .order-total').text(discounted)
                        }
                    }

                    function calculateFee() {
                        let total = 0
                        if ($('#discount-fee-section .order-item-discount').length == 1) {
                            total = parseFloat($('.order-discount-total').text())
                        } else {
                            total = parseInt($('#subtotal').data('amount'))
                        }
                        
                        let fee = total + parseInt($('.order-item-fee').val())

                        if (fee) {
                            $('.order-fee-amount').text($('.order-item-fee').val())
                            $('.order-fee-total').text(fee)
    
                            $('#order-total').text(fee)
                            $('#total .order-total').text(fee)
                        }

                    }

                    $("body").on("click", '.easy-pos-order .delete-order-item', function(e){
                        $(this).parent().remove();

                        if ($('#discount-fee-section .order-item-discount').length < 1) {
                            $(addDiscount).show()
                        }

                        if ($('#discount-fee-section .order-item-fee').length < 1) {
                            $(addFee).show()
                        }

                        calculateHours()

                        return false;
                    });

                    $('body').on('focusout', '.easy-pos-order .order-item-price', function() {
                        let id = $(this).parent().parent().attr('id')

                        let price = $(this).val()
                        let quantity = $('#'+id+' .order-item-quantity').val()

                        changeOrderItemTotal(id, price, quantity)
                        paymentPlans()
                    })

                    $('body').on('focusout','.easy-pos-order .order-item-quantity', function() {
                        let id = $(this).parent().parent().attr('id')

                        let price = $('#'+id+' .order-item-price').val()
                        let quantity = $(this).val()

                        changeOrderItemTotal(id, price, quantity)
                        paymentPlans()
                    })

                    function paymentPlans() {
                        let months = $('#payment_plan_months').val()
                        let total = $('#order-total').text()

                        if (months !== '' && total !== '') {
                            let plan = total / months

                            $('#total_monthly').show()
                            $('#total_monthly .total-monthly ').text(plan.toFixed(2))
                        } else {
                            $('#total_monthly').hide()
                        }
                    }

                    function changeOrderItemTotal(id, price, quantity) {
                        let itemTotal = price * quantity
                        $('#'+id+' .order-item-total').text(itemTotal)

                        let total = 0
                        $('.order-item-total').each(function(i, el) {
                            total += parseInt($(el).text())
                        })

                        $('#order-total').text(total)
                        $('#payment_plan_total').val(total)
                    }
                    
                    
                });
            })(jQuery);
        </script>
        <?php
    }


    public function easy_pos_shortcode($atts) {
        $args = shortcode_atts(array(
            'search_users' => false,
            'get_billing_history' => false,
            'set_user' => false,
            'my_account_page' => false,
        ), $atts);
    
        if (!empty($_POST['pos_nonce']) && isset($_POST['customer'])) {

            if (wp_verify_nonce($_POST['pos_nonce'], 'pos_nonce') && $_POST['customer'] !== 'no_account') {
                $customer_id = $_POST['customer'];
                $amount = $_POST['amount'];

                $is_discount = $_POST['is_discount'];
                $is_fee = $_POST['is_fee'];

                $discount_percentage = intval($_POST['percentage']['discount']) / 100;
                $fee_percentage = $_POST['percentage']['fee'];

                $balance = get_invoice_balance($customer_id);

                if (!empty($customer_id) &&
                    !empty($amount)
                ) {

                    $order_id = isset($_POST['order']) ? $_POST['order'] : '';

                    $payment_method = $_POST['payment_method'];

                    $email_subject = $_POST['email_subject'];
                    $email_content = $_POST['email_content'];
                    
                    $card_exists = $_POST['card_exists'];
                    $check_number = $_POST['check_number'];
                    $cash_receipt = $_POST['cash_receipt'];
                    $stripe_token = $_POST['stripeToken'];
                    $card_id = $_POST['card_id'];
                    
                    $setup_id = $_POST['setup_id'];
                    $setup_pm = $_POST['setup_pm'];
                    $ach_exists = $_POST['ach_exists'];
                    $ach_id = $_POST['ach_id'];
                    
                    $staff_note = $_POST['staff_note'];

                    if (!empty($order_id)) {
                        $order = wc_get_order($order_id);
                    }
                    
                    if ($amount <= 0 && $payment_method !== 'adjustment') {
                        $invalid_fields[] = 'Please enter an amount greater than 0';
                    }

                    $original_amount = $amount;

                    if ($is_discount == 1){
                        $discount_given = $original_amount * ($discount_percentage);
                        $amount_discount = $original_amount - $discount_given;
                        $amount = $amount_discount;

                        if (isset($order)) {
                            $total = $order->get_total();
                        } else {
                            $total = $balance['amount'];
                        }

                        if ($original_amount > $total) {
                            $amount = $original_amount;
                        }
                    }

                    if ($is_fee == 1 && $payment_method == 'card' || $payment_method == 'ach'){
                        $fee_given = $original_amount * floatval($fee_percentage) / 100;
                        $amount = $original_amount + $fee_given;

                        $amounts = array('original_amount' => $original_amount, 'amount' => $amount);
                        $amount = $amounts;
                    }

                    if ($is_discount == 1 && $is_fee == 1) {
                        $amount = $original_amount + $fee_given - $discount_given;
                        $amounts = array('original_amount' => $amount_discount, 'amount' => $amount);
                        $amount = $amounts;
                    }

                    if (!isset($invalid_fields)) {


                        switch ($payment_method) {
                            case 'card':
                                $invalid_fields = $this->card_method($customer_id, $card_id, $stripe_token, $order, $amount, $card_exists, $staff_note, array('is_discount' => $discount_percentage, 'is_fee' => $fee_percentage));
                            break;
                            
                            case 'check': 
                                $invalid_fields = $this->check_method($customer_id, $amount, $check_number, $order, $staff_note);
                            break;
                            
                            case 'cash': 
                                $invalid_fields = $this->cash_method($customer_id, $amount, $order, $cash_receipt, $staff_note, array('is_discount' => $discount_percentage, 'is_fee' => $fee_percentage));
                            break;

                            case 'credit': 
                                $order_id = $this->pos_create_order($customer_id, $amount, null, array('credit' => 1), $staff_note);
                                $invalid_fields['id'] = $order_id;
                            break;

                            case 'adjustment': 
                                $invalid_fields = $this->adjustment_method($customer_id, $amount, $staff_note);
                            break;

                            case 'ach': 
                                $invalid_fields = $this->ach_method($customer_id, $ach_id, $setup_id, $setup_pm, $order, $amount, $ach_exists, $staff_note, array('is_discount' => $discount_percentage, 'is_fee' => $fee_percentage));
                            break;
                        }

                        if (isset($invalid_fields)) {

                            if (isset($invalid_fields['id'])) {
                                if ($is_discount == 1 && $payment_method !== 'adjustment' && $payment_method !== 'credit' && $payment_method !== 'check') {
                                    $this->pos_create_order($customer_id, $discount_given, null, array('discount' => 1, 'percentage' => $discount_percentage, 'payment_method' => $payment_method, 'parent_id' => $invalid_fields['id']), $staff_note);
                                }

                                if (!empty($email_subject) && !empty($email_content)) {
                                    $admin = wp_get_current_user();
                                    $customer = get_user_by('id', $customer_id);

                                    $from = !empty(get_option('custom_note_from')) ? get_option('custom_note_from') : 'ca@gymnasticsofyork.com';
                                    $replyto = !empty(get_option('custom_note_replyto')) ? get_option('custom_note_replyto') : 'ca@gymnasticsofyork.com';
                                    $bcc  = !empty(get_option('custom_note_bcc')) ? get_option('custom_note_bcc') : 'ca@gymnasticsofyork.com';

                                    $headers[] = 'Content-Type: text/html; charset=UTF-8';
                                    $headers[] = 'From: <'.$from.'>';
                                    $headers[] = 'Reply-To: <'.$replyto.'>';
                                    $headers[] = 'Bcc: '.$bcc;
                                    
                                    $email_content = wp_kses_post($email_content);
                                    $email_content = nl2br($email_content);
                                    
                                    $message = EmailTemplates::self_merge_tags($email_content, $customer);
                                    $is_sent = wp_mail($customer->user_email, $email_subject, $message, $headers);

                                    if ($is_sent) {
                                        $comment_user = array(
                                            'comment_author' => $admin->display_name,
                                            'comment_content' => 'Email "'.$email_subject.'" sent to '.$customer->user_email.'. ',
                                            'user_id' => $customer->ID,
                                            'comment_meta'         => array(
                                                'is_customer_note'       => sanitize_text_field(1),
                                                )
                                            );
                            
                                        wp_insert_comment($comment_user);
                                    }
                                }
    
                                if (isset($_GET['user'])) {
                                    wp_redirect( site_url()."/wp-admin/admin.php?page=user-information-edit&user=".$customer_id.'&child=no', 301 );
                                } elseif (!strstr($_SERVER['REQUEST_URI'], '/wp-admin/admin.php?page=pos-admin-page')) {
                                    wp_redirect( site_url()."/my-account?success=1");
                                } else {
                                    wp_redirect( site_url()."/wp-admin/admin.php?page=pos-admin-page&id=".$customer_id, 301 );
                                }
                            } else {
                                ?>
                                <script>
                                    jQuery(document).ready(function($){
                                        $('.global-error').text("<?= $invalid_fields[0] ?>")
                                        $('.global-error').show()
                                        $('.global-success').hide()
                                    })
                                </script>
                            <?php
                            }
                    }
                } else {
                    $invalid_fields = $_POST;
                }
            } else {
                $invalid_fields[] = 'Customer doesn\'t exist';
            }
            
            echo '<style>';
            foreach($invalid_fields as $field) {
                echo '#'.$field.' + .notice-warning {
                    display: block !important;
                }';
            }
            echo '</style>';

        }
    }

        $nonce = wp_create_nonce('pos_nonce');

        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/easy_pos/easy_pos.php';

    }

    public function create_payment_plan($payment_plan, $payment_plan_total, $due_date, $customer_id, $order_items) {
        global $wpdb;

        $remaining_balance = $payment_plan_total;
        $start_date = $due_date;
            
        $due_monthly =  $payment_plan_total / $payment_plan;

        $user = get_user_by('id', $customer_id);
        $admin_id = get_current_user_id();
        $admin = get_user_by('id', $admin_id);

        $fname     = get_user_meta($user->ID, 'billing_first_name', true);
        $lname     = get_user_meta($user->ID, 'billing_last_name', true);
        $email     = $user->user_email;
        $address_1 = get_user_meta( $user->ID, 'billing_address_1', true );
        $city      = get_user_meta( $user->ID, 'billing_city', true );
        $postcode  = get_user_meta( $user->ID, 'billing_postcode', true );
        $state     = get_user_meta( $user->ID, 'billing_state', true );

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

        $date_time = new DateTime();
        $date_time = $date_time->format('H:i:s');
        $date_time = new DateTime($due_date .' '. $date_time);
        $date_time = $date_time->format('Y-m-d H:i:s');

        for($i = 0; $i < $payment_plan; $i++) {
            $notes = [];
            
            $order = wc_create_order( array( 'customer_id' => $user->ID) );
            $order->set_address( $address, 'billing' );
            $order->set_address( $address, 'shipping' );
    
            $sql = 'SELECT ID FROM wp_posts WHERE post_title = %s AND post_type = "product"';
            foreach($order_items as $item) {
                
                if ($item['product_id']) {
                    $product = wc_get_product($item['product_id']);
            
                    if (!empty($product)) {
                        $product_id = $product->get_id();
                        $product->set_price($item['price']);
                        $product->save();
                    }
                }
                
                if (isset($item['category'])) {
                    foreach ($item['category'] as $category) {
                        wp_set_object_terms($product_id, intval($category), 'product_cat', true);
                    }
                }
    
                if (isset($item['quantity'])) {
                    $order->add_product( $product, $item['quantity']);
                }
    
                if ($item['description']) {
                    $notes[] = $product->get_name().': '.$item['description'];
                }
    
                $order->calculate_totals();
            }

            $remaining_balance -= $due_monthly;

            if ($order->get_id()) {

                $counter = 0;
                $invoice_table = '
                                <table style="border-collapse: collapse; width: 100%;">
                                    <thead>
                                        <tr>
                                            <th style="border: 1px solid black;">#</th>
                                            <th style="border: 1px solid black;">Item</th>
                                            <th style="border: 1px solid black;">Quantity</th>
                                            <th style="border: 1px solid black;">Total</th>
                                        </tr>
                                    </thead>
                                <tbody>';

                foreach($order->get_items() as $item) {
                    $counter += 1;
                    $invoice_table .= '
                    <tr style="text-align: center;">
                        <td style="border: 1px solid black;">'.$counter.'</td>
                        <td style="border: 1px solid black;">'.$item['name'].'</td>
                        <td style="border: 1px solid black;">'.$item['quantity'].'</td>
                        <td style="border: 1px solid black;">'.$item['total'].'</td>
                    </tr>';
                }
    
                $invoice_table .= '
                    </tbody>
                    <tfoot>
                        <tr>
                            <th style="text-align: right; border: 1px solid black;" colspan="3">Original Balance</th>
                            <th style="text-align: center; border: 1px solid black;">$'.$payment_plan_total.'</th>
                        </tr>
                        <tr>
                            <th style="text-align: right; border: 1px solid black;" colspan="3">Remaining Balance</th>
                            <th style="text-align: center; border: 1px solid black;">$'.round(($remaining_balance < 0 ? 0 : $remaining_balance), 2).'</th>
                        </tr>
                    </tfoot>
                </table>';

                $comment_invoice = array(
                    'comment_author' => $order->get_id(),
                    'comment_content' => 'Invoice generated through POS for $'.$order->get_total(),
                    'user_id' => $user->ID,
                    'comment_meta'         => array(
                        'is_payment_plan' => 1,
                        'payment_plan_total' => round($payment_plan_total, 2),
                        'payment_plan_months' => $payment_plan,
                        'invoice_table' => $invoice_table,
                        'is_invoice'       => sanitize_text_field(1),
                        'invoice_total'    => $order->get_total(),
                        'due_date'    => $due_date,
                        'remaining_balance' => round(($remaining_balance < 0 ? 0 : $remaining_balance), 2),
                        )
                );

                if (isset($plan_id)) {
                    $comment_invoice['comment_meta']['plan_id'] = $plan_id; 
                }
                
                $comment_id = wp_insert_comment($comment_invoice);
                if ($i == 0) {
                    $plan_id = $comment_id;
                }

                $comment_user = array(
                    'comment_author' => $admin->display_name,
                    'comment_content' => 'Invoice #'.$comment_id.' generated for $'.$order->get_total() . (!empty($due_date) ? ' with due date: '. $due_date : ''),
                    'user_id' => $user->ID,
                    'comment_meta'         => array(
                        'is_customer_note'       => sanitize_text_field(1),
                        'invoice_id'       => $comment_id,
                        )
                    );
    
                wp_insert_comment($comment_user);

                $time = new DateTime($date_time);

                $time->add(new DateInterval('PT1M'));
                $stamp = $time->format('Y-m-d H:i:s');

                $sql = 'UPDATE wp_comments SET comment_date = "'.$stamp.'" WHERE comment_ID = '.$comment_id;
                $wpdb->query($sql);
                
                $time->add(new DateInterval('PT1M'));
                $stamp = $time->format('Y-m-d H:i:s');

                $order->set_date_created($stamp);
                $due_date = date('Y-m-d', strtotime('+1 months', strtotime($due_date)));
                $date_time = date('Y-m-d H:i:s', strtotime('+1 months', strtotime($stamp)));
                if (isset($notes)) {
                    foreach($notes as $note) {
                        $order->add_order_note($note);
                    }
                }
                $order->add_order_note('Payment Plan with start date '.$start_date.' created by user '.$admin->display_name);
                update_post_meta($order->get_id(), 'is_payment_plan', 1);
                $order->save();
            }
            
        }

        echo '<style>
            .easy-pos-order .global-success {
                display: block !important;
            }

            .easy-pos-order .global-success::after {
                content: "Payment Plan added succesfully";
            }
        </style>';
    }

    public function ach_method($customer_id, $ach_id, $setup_id, $setup_pm, $order, $amount, $ach_exists, $staff_note, $metadata) {
        $is_invalid = [];
        $stripe = new \Stripe\StripeClient(STRIPE_TEST_KEY);
        
        $user = get_user_by('id', $customer_id);
        $stripe_cus_id = get_user_meta($customer_id, 'wp__stripe_customer_id', true);
        
        if (isset($amount['amount'])) {
            $amounts = $amount;
            $amount = $amounts['amount'];
        }
        
        $amount_in_cents = intval($amount * 100);

        if (!empty($setup_id) && !empty($setup_pm) && $ach_exists !== 'ach_exists') {

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
                }

                if ($setup_intent->status == 'succeeded' || $setup_intent->status == 'processing') {
                    $payment_method = $setup_pm;
                } else {
                    return $is_invalid;
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
        } if ($ach_exists == 'ach_exists') {
            if (!empty($ach_id)) {
                $payment_method = $stripe->paymentMethods->retrieve(
                    $ach_id
                );

                if (isset($payment_method)) {
                    $payment_method = $payment_method->id;
                }
            }
        }   

        if (empty($is_invalid) && !empty($amount) && isset($payment_method)) {

            if (isset($amounts)) {
                $amount = $amounts['original_amount'];
            }
    
            if(!empty($order)) {
                $parent_order_id = $this->pos_update_order($order, $amount, 'card', $staff_note);
            }
    
            $order_id = $this->pos_create_order($customer_id, $amount, $order, array('stripe_cus_id' => $stripe_cus_id, 'payment_method' => $payment_method, 'ach' => 1, 'is_discount' => $metadata['is_discount'], 'is_fee' => $metadata['is_fee']), $staff_note);
                
            $metadata = ['order_id' => $order_id, 'customer_email' => $user->user_email, 'customer_name' => $user->first_name . ' ' . $user->last_name, 'save_payment_method' => 'true', 'site_url' => site_url()];
    
            try {
                $payment_intent = $stripe->paymentIntents->create([
                    'amount' => $amount_in_cents,
                    'currency' => 'usd',
                    'customer' => $stripe_cus_id,
                    'metadata' => $metadata,
                    'payment_method' => $payment_method,
                    'payment_method_types' => ['us_bank_account'],
                    'description' => 'Gymnastics of York - Order #'. $order_id,
                    'confirmation_method' => 'manual',
                    'confirm' => true,
                    'mandate_data' => ['customer_acceptance' => ['type' => 'online', 'online' => ['ip_address' => '35.245.151.137', 'user_agent' => 'device']]]
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

            sleep(15);

            $payment_intent = $stripe->paymentIntents->retrieve($payment_intent->id, []);

            if ($payment_intent->last_payment_error) {
                $is_invalid[] = $payment_intent->last_payment_error->message;
            }
    
            if (empty($is_invalid) && $payment_intent->status == 'succeeded' || $payment_intent->status == 'processing') {
                update_post_meta($order_id, '_stripe_intent_id', $payment_intent->id);
                return array('id' => $order_id);
            } else {
                if (isset($is_invalid[0])){
                    $note = $is_invalid[0];
                } else {
                    $note = 'ACH Payment failed';
                }
                $new_order = wc_get_order($order_id);
                $new_order->update_status('cancelled', $note);
    
                if (isset($parent_order_id)) {
                    $parent_order = wc_get_order($parent_order_id);
                    $parent_order->update_status('on-hold', $note);
                }
    
                return $is_invalid;
            }
        } else {
            return $is_invalid;
        }
    }

    public function add_invoice($customer_id, $order_items, $due_date) {
        global $wpdb;

        $user = get_user_by('id', $customer_id);
        $admin_id = get_current_user_id();
        $admin = get_user_by('id', $admin_id);

        $order = wc_create_order( array( 'customer_id' => $user->ID) );

        $fname     = get_user_meta($user->ID, 'billing_first_name', true);
        $lname     = get_user_meta($user->ID, 'billing_last_name', true);
        $email     = $user->user_email;
        $address_1 = get_user_meta( $user->ID, 'billing_address_1', true );
        $city      = get_user_meta( $user->ID, 'billing_city', true );
        $postcode  = get_user_meta( $user->ID, 'billing_postcode', true );
        $state     = get_user_meta( $user->ID, 'billing_state', true );
    
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

        foreach($order_items as $item) {
            
            if ($item['product_id']) {
                $product = wc_get_product($item['product_id']);
        
                if (!empty($product)) {
                    $product_id = $product->get_id();
                    $product->set_price($item['price']);
                    $product->save();
                }

                if (isset($item['category'])) {
                    foreach ($item['category'] as $category) {
                        wp_set_object_terms($product_id, intval($category), 'product_cat', true);
                    }
                }
    
                if (isset($item['quantity'])) {
                    $order->add_product( $product, $item['quantity']);
                }
    
                if ($item['description']) {
                    $notes[] = $product->get_name().': '.$item['description'];
    
                }
            }

        }

        $order->calculate_totals();
        
        $note = 'Order recorded through GYCRM by user '.$admin->display_name;
        $order->update_status( 'pending', $note);

        if ($order->get_id()) {
            $counter = 0;

            $invoice_table = '
                            <table style="border-collapse: collapse; width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="border: 1px solid black;">#</th>
                                        <th style="border: 1px solid black;">Item</th>
                                        <th style="border: 1px solid black;">Quantity</th>
                                        <th style="border: 1px solid black;">Price</th>
                                        <th style="border: 1px solid black;">Total</th>
                                    </tr>
                                </thead>
                            <tbody>';

            foreach($order->get_items() as $item) {
                $counter += 1;
                $invoice_table .= '
                    <tr style="text-align: center;">
                        <td style="border: 1px solid black;">'.$counter.'</td>
                        <td style="border: 1px solid black;">'.$item['name'].'</td>
                        <td style="border: 1px solid black;">'.$item['quantity'].'</td>
                        <td style="border: 1px solid black;">'.$item->get_total().'</td>
                        <td style="border: 1px solid black;">'.$item['total'].'</td>
                    </tr>';
            }

            $invoice_table .= '
                </tbody>
                <tfoot>
                    <tr>
                        <th style="text-align: right; border: 1px solid black;" colspan="4">Total</th>
                        <th style="text-align: center; border: 1px solid black;">$'.$order->get_total().'</th>
                    </tr>
                </tfoot>
            </table>';

            if ($order->get_total() > 0) {
                $template = get_posts(array('post_type' => 'email_template', 'name' => 'Your Gymnastics of York order has been received!'))[0];
                $subject = $template->post_title;
                $template_message = $template->post_content;
    
                $from = !empty(get_option('custom_note_from')) ? get_option('custom_note_from') : 'ca@gymnasticsofyork.com';
                $replyto = !empty(get_option('custom_note_replyto')) ? get_option('custom_note_replyto') : 'ca@gymnasticsofyork.com';
                $bcc  = !empty(get_option('custom_note_bcc')) ? get_option('custom_note_bcc') : 'ca@gymnasticsofyork.com';
                        
                $headers[] = 'Content-Type: text/html; charset=UTF-8';
                $headers[] = 'From: <'.$from.'>';
                $headers[] = 'Reply-To: <'.$replyto.'>';
                $headers[] = 'Bcc: '.$bcc;
    
                $message = str_replace(
                    array('{{first_name}}', '{{order_id}}', '{{invoice_table}}', '{{date}}'),
                    array($user->first_name, $order->get_id(), $invoice_table, date('F j, Y')),
                    $template_message
                );
                wp_mail($user->user_email, $subject, $message, $headers);
                
                $now = new DateTime();
                $comment_invoice = array(
                    'comment_author' => $order->get_id(),
                    'comment_content' => 'Invoice generated through POS for $'.$order->get_total(),
                    'user_id' => $user->ID,
                    'comment_date' => $now->format('Y-m-d H:i:s'),
                    'comment_meta'         => array(
                        'invoice_table' => $invoice_table,
                        'message' => $message,
                        'is_invoice'       => sanitize_text_field(1),
                        'invoice_total'    => $order->get_total(),
                        'due_date'    => $due_date,
                        )
                    );
    
                if (isset($_POST['is_monthly'])) {
                    $comment_invoice['comment_meta']['is_monthly'] = 1;
                }
                $comment_id = wp_insert_comment($comment_invoice);
    
                if (!empty($due_date)) {
                    $date_time = new DateTime();
                    $date_time = $date_time->format('H:i:s');
                    $date_time = new DateTime($due_date .' '. $date_time);
                    $date_time->add(new DateInterval('PT1M'));
                    $date_time = $date_time->format('Y-m-d H:i:s');
                    
                    $sql = 'UPDATE wp_comments SET comment_date = "'.$date_time.'" WHERE comment_ID = '.$comment_id;
                    $wpdb->query($sql);
                }
    
                $comment_user = array(
                    'comment_author' => $admin->display_name,
                    'comment_content' => 'Email "'.$subject.'" sent to '.$user->user_email.'. Invoice #'.$comment_id.' generated for $'.$order->get_total() . (!empty($due_date) ? ' with due date: '. $due_date : ''),
                    'comment_date' => $now->format('Y-m-d H:i:s'),
                    'user_id' => $user->ID,
                    'comment_meta'         => array(
                        'is_customer_note'       => sanitize_text_field(1),
                        'invoice_id'       => $comment_id,
                        )
                    );
                wp_insert_comment($comment_user);
    
                if (isset($notes)) {
                    foreach($notes as $note) {
                        $order->add_order_note($note);
                    }
                }
                $order->add_order_note('Invoice #'.$comment_id.' generated for $'.$order->get_total().' by user '.$admin->display_name);
    
                $order->save();
    
                echo '<style>
                    .easy-pos-order .global-success {
                        display: block !important;
                    }
    
                    .easy-pos-order .global-success::after {
                        content: "Order #'.$order->get_id().' and Invoice #'.$comment_id.' added";
                    }
                </style>';
            } else {
                wp_delete_post($order->get_id(), true);
            }
        } else {
            echo '<style>
                .easy-pos-order .global-error:not(.empty-fields) {
                    display: block !important;
                }
                </style>';
        }

    }

    public function create_manual_invoice() {

        $user = get_user_by('id', $_POST['customer']);
        $is_due_registration = isset($_POST['item']['fee']) ? true : false;

        $has_siblings = false;
        $sibling_rate = array();
        $siblings_total = 0;
                
        $products_ids = [];

        $total_hours = 0;

        $product_list = '';

        $invoice_table = '
                <table style="border-collapse: collapse; width: 100%;">
                <thead>
                    <tr>
                        <th style="border: 1px solid black;">Class</th>
                        <th style="border: 1px solid black;">Hours / Week</th>
                        <th style="border: 1px solid black;">Monthly fee</th>
                    </tr>
                </thead>
                <tbody>';
                            
        $children = $_POST['order_item'];

        if ($children) {

            if (isset($_POST['item']['discount'])) {
                $has_siblings = true;
            }

            foreach($children as $key => $child) {

                if (!empty($child)) {
                    $counter = 0;
                    
                    $total_hours_per_child = 0;
                    
                    $classes = $child['classes'];

                if (!empty($classes)) {
                    $first_name = get_user_meta($key, 'first_name', true);
                    $product_list .= '<li>'.$first_name.'</li>
                                        <ul>';

                    foreach ($classes as $class) {
                        if (!empty($class['id']) && !empty(get_post($class['id']))) {
                            $product_id = get_post_meta($class['id'], 'product_id', true);
                            $products_ids[] = $product_id;

                            $hours_per_week = $class['hours'];

                            if ($hours_per_week) {

                                $counter += 1;

                                $args = array('post_type' => 'class',
                                    'post_status' => 'publish',
                                    'p' => $class['id'],
                                    'posts_per_page' => -1,
                                );

                                $post_title = wp_list_pluck( get_posts( $args ), 'post_title' );

                                $total_hours_per_child += floatval($hours_per_week);
                                $total_hours += floatval($hours_per_week);
                                
                                if (!empty($_POST['order_item'][$key]['amount'])) {
                                    $price_per_child = $_POST['order_item'][$key]['amount'];
                                    $price = $this->price_per_hour[strval($class['hours'])];
                                } else {
                                    $price_per_child = $this->price_per_hour[strval($total_hours_per_child)];
                                    $price = $this->price_per_hour[strval($hours_per_week)];
                                }

                                $product_list .= '<li>'.$post_title[0].': <strong>'.$hours_per_week.' Hours Training / Week</strong></li>';

                                $invoice_table .= '
                                    <tr style="text-align: center;">
                                        <td style="border: 1px solid black;">'.$post_title[0].'</td>
                                        <td style="border: 1px solid black;">'.$hours_per_week.'</td>
                                        <td style="border: 1px solid black;">'.wc_price($price).'</td>
                                    </tr>';
                            }
                            
                        }
                        }

                        if ($has_siblings) {
                            $sibling_rate[] = $price_per_child;
                        }

                        $siblings_total += $price_per_child;

                        $invoice_table .= '<tr>
                                <th style="text-align: right; border: 1px solid black;">Total hours</th>
                                <th style="text-align: center; border: 1px solid black;">'.$total_hours_per_child.'</th>
                                <th style="text-align: center; border: 1px solid black;">'.wc_price($price_per_child).'</th>
                            </tr>';

                        $product_list .= '</ul>';
                    }
                }
            }

            if ($total_hours > 0) {

                $total_amount = $siblings_total;
                $original_total = $total_amount;
                $subtotal = '';

                if ($has_siblings) {
                    $last_sibling_rate = min($sibling_rate);

                    $discount = $last_sibling_rate * ($_POST['item']['discount']['amount'] / 100);
                    $total_amount = $total_amount - $discount;

                    $subtotal .= '<tr>
                            <th style="text-align: right; border: 1px solid black;">'.$_POST['item']['discount']['description'].'</th>
                            <th style="text-align: center; border: 1px solid black;">-'.$_POST['item']['discount']['amount'].'%</th>
                            <th style="text-align: center; border: 1px solid black;">'.wc_price(-abs($discount)).'</th>
                        </tr>';
                }
                
                if ($is_due_registration) {
                    $total_amount += $_POST['item']['fee']['amount'];

                    $subtotal .= '<tr>
                                    <th style="text-align: right; border: 1px solid black;">'.(empty($_POST['item']['fee']['description']) ? 'Registration Fee' : $_POST['item']['fee']['description']).'</th>
                                    <th style="text-align: center; border: 1px solid black;">'.wc_price($_POST['item']['fee']['amount']).'</th>
                                    <th style="text-align: center; border: 1px solid black;">'.wc_price($total_amount).'</th>
                                </tr>';
                } 

                if (!empty($subtotal)) {
                    $invoice_table .= $subtotal;
                }

                $invoice_table .= '
                </tbody>
                <tfoot>
                    <tr>
                        <th style="text-align: right; border: 1px solid black;" colspan="2">Total</th>
                        <th style="text-align: center; border: 1px solid black;">'.wc_price($total_amount).'</th>
                    </tr>
                </tfoot>
            </table>';

            $discount = isset($discount) ? $discount : 0;
            $subscriptions = wcs_get_subscriptions(array('customer_id' => $user->ID));
                if (empty($subscriptions)) {
                    $active_subscription = EmailTemplates::create_order_subscription($user, $products_ids, $is_due_registration, $discount, $original_total);
                } else {
                    foreach($subscriptions as $subscription) {
                        $sub_product_ids = [];
                        $items = $subscription->get_items();
                        
                        foreach($items as $item) {
                            $data = $item->get_data();
                            $sub_product_ids[] = $data['product_id'];
                        }
                        
                        if ($sub_product_ids == $products_ids) {
                            $parent_id = $subscription->get_data()['id'];
                        }

                    }
                }

                if (isset($parent_id) && !empty($subscriptions)) {
                    $parent_subscription = wc_get_order($parent_id);
                    EmailTemplates::update_subscription_order($parent_subscription, $is_due_registration, $discount);
                    $active_subscription = EmailTemplates::create_order($user, array('products_ids' => $products_ids, 'is_due_registration' => $is_due_registration, 'parent_id' => $parent_id, 'discount' => $discount, 'total_amount' => $original_total));
                } else {
                    $active_subscription = EmailTemplates::create_order_subscription($user, $products_ids, $is_due_registration, $discount, $original_total);
                }
                
                if ($active_subscription !== 0) {
                    
                    $payment_date = date('F');
                            
                    $template = get_posts(array('post_type' => 'email_template', 'name' => 'Subscription Renewal Invoice'))[0];
                    $subject = $template->post_title;
                    $template_message = $template->post_content;

                    $from = !empty(get_option('custom_note_from')) ? get_option('custom_note_from') : 'ca@gymnasticsofyork.com';
                    $replyto = !empty(get_option('custom_note_replyto')) ? get_option('custom_note_replyto') : 'ca@gymnasticsofyork.com';
                    $bcc  = !empty(get_option('custom_note_bcc')) ? get_option('custom_note_bcc') : 'ca@gymnasticsofyork.com';
                            
                    $headers[] = 'Content-Type: text/html; charset=UTF-8';
                    $headers[] = 'From: <'.$from.'>';
                    $headers[] = 'Reply-To: <'.$replyto.'>';
                    $headers[] = 'Bcc: '.$bcc;

                    $template_message = EmailTemplates::self_merge_tags($template_message, $user);
                    $message = str_replace(
                        array('{{program_list}}', '{{date}}', '{{amount}}', '{{invoice}}'),
                        array($product_list, $payment_date, $total_amount, $invoice_table),
                        $template_message
                    );

                    wp_mail($user->user_email, $subject, $message, $headers);

                    $now = new DateTime();

                    $comment_invoice = array(
                        'comment_author' => $active_subscription,
                        'comment_content' => 'Invoice generated for $'.$total_amount,
                        'user_id' => $user->ID,
                        'comment_date' => $now->format('Y-m-d H:i:s'),
                        'comment_meta'         => array(
                            'is_monthly'       => 1,
                            'is_invoice'       => sanitize_text_field(1),
                            'invoice_total'    => $total_amount,
                            'invoice_table'    => $invoice_table,
                            'message' => $message
                            )
                        );
        
                    $comment_id = wp_insert_comment($comment_invoice);
        
                    $current = get_current_user_id();
                    $current_user = get_user_by('id', $current);
                    $comment_user = array(
                        'comment_author' => $current_user->display_name,
                        'comment_content' => 'Email "'.$subject.'" sent to '.$user->user_email.' Invoice #'.$comment_id.' generated for $'.$total_amount,
                        'user_id' => $user->ID,
                        'comment_date' => $now->format('Y-m-d H:i:s'),
                        'comment_meta'         => array(
                            'is_customer_note'       => sanitize_text_field(1),
                            'invoice_id'       => $comment_id,
                            )
                        );
        
                    wp_insert_comment($comment_user);
                    $order = wc_get_order($active_subscription);
                    $order->add_order_note('Invoice #'.$comment_id.' generated for $'.$order->get_total().' by user '.$current_user->display_name);

                    wp_redirect('/wp-admin/admin.php?page=user-information-edit&user='.$user->ID.'&child=no');

                }
            }
        }
    }

    public function save_category() {
        $cat_name = $_POST['category_name']; 
        $cat_parent = $_POST['parent_category']; 
        $cat_descr = $_POST['category_description']; 

        if (!empty($cat_name)) {

            $category = get_term_by('name', $cat_name, 'product_cat');

            if (empty($category)) {
                $category_id = wp_insert_term(
                    $cat_name,
                    'product_cat',
                    array('description' => $cat_descr,
                    'parent' => $cat_parent)
                );

                if ($category_id['term_id']) {
                    ?>
                    <style>
                    .easy-pos-order .global-success {
                            display: block !important;
                    }
                    .easy-pos-order .global-success::after {
                        content: "Category Saved.";
                    }
                    </style>
                    <?php
                } else {
                    ?>
                    <style>
                        .global-error:not(.empty-fields) {
                            display: block !important;
                        }
                    </style>
                    <?php
                }
            } else {
                ?>
                <style>
                    .easy-pos-order .empty-fields {
                        display: block !important;
                    }
                    .easy-pos-order .empty-fields::after {
                        content: 'Category "<?=$cat_name?>" already exists.'
                    }
                </style>
                <?php

            }

        } else {
            ?>
            <style>
                .empty-fields {
                    display: block !important;
                }
                .easy-pos-order .empty-fields::after {
                        content: 'Please enter a Category Name';
                    }
            </style>
            <?php
        }
    }

    public function easy_pos_order_shortcode($atts) {
        $args = shortcode_atts(array(
            'search_users' => false,
        ), $atts);

        if (isset($_POST['add_order'])) {

            if (wp_verify_nonce($_POST['pos_nonce'], 'easy-pos-add-order')) {
                
                $customer_id = $_POST['customer'];
                $order_items = $_POST['order_item'];
                $due_date = isset($_POST['due_date']) ? $_POST['due_date'] : '';

                $payment_plan = $_POST['payment_plan']['months'];
                $payment_plan_total = $_POST['payment_plan']['total'];
                $payment_plan_on = $_POST['payment_plan']['on'];
    
                if (!empty($customer_id)) {
                    if (isset($payment_plan_on) && $payment_plan_on == 1) {
                        if (isset($payment_plan) && $payment_plan > 0 && !empty($due_date)) {
                            $this->create_payment_plan($payment_plan, $payment_plan_total, $due_date, $customer_id, $order_items);
                        }
                    } else {
                        if (!empty($customer_id)) {
                            $this->add_invoice($customer_id, $order_items, $due_date);
                        }
                    }
                } else {
                    ?>
                    <style>
                        .empty-fields {
                            display: block !important;
                        }
                        .empty-fields::after {
                            content: 'Please enter a customer.'
                        }
                    </style>
                    <?php
                }
            }

        } else if (isset($_POST['add_invoice'])) {
            $this->create_manual_invoice();
        } else if (isset($_POST['save_category'])) {
            $this->save_category();
        } else if (isset($_POST['save_product'])) {
            $this->save_product();
        }

        $nonce = wp_create_nonce('easy-pos-add-order');

        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/easy_pos/add_order.php';

        $this->pos_order_scripts();

    }

    public function save_product() {
        global $wpdb;

        $prod_name = $_POST['product_name'];
        $prod_description = $_POST['product_description'];
        $prod_categories = $_POST['order_item'][0]['category'];
        $prod_price = $_POST['product_price'];
        
        if (!empty($prod_name) && !empty($prod_price)) {
            $sql = 'SELECT ID FROM wp_posts WHERE post_title = %s AND post_type = "product"';
            $where = [$prod_name];
            $product_id = $wpdb->get_results($wpdb->prepare($sql, $where));

            if (empty($product_id)) {
                $product = new WC_Product_Simple();
                $product->set_name( $prod_name );
                $product->set_regular_price( floatval($prod_price) );
                $product->set_description( $prod_description );
                $product->save();

                if (!empty($prod_categories) && count($prod_categories) > 0) {
                    foreach ($prod_categories as $category) {
                        wp_set_object_terms($product->get_id(), intval($category), 'product_cat', true);
                    }
                    $uncategorized = get_term_by('slug', 'uncategorized', 'product_cat');
                    wp_remove_object_terms($product->get_id(), intval($uncategorized->term_id), 'product_cat');
                }

                ?>
                <style>
                .easy-pos-order .global-success {
                        display: block !important;
                }
                .easy-pos-order .global-success::after {
                    content: "Product Saved.";
                }
                </style>
                <?php
            } else {
                ?>
                <style>
                    .empty-fields {
                        display: block !important;
                    }
                    .empty-fields::after {
                        content: 'Product "<?=$prod_name?>" already exists.'
                    }
                </style>
                <?php
            }

        } else {
            ?>
            <style>
                .empty-fields {
                    display: block !important;
                }
            </style>
            <?php
        }
    }

    public function adjustment_method($customer_id, $amount, $staff_note) {
        
        if ($amount < 0) {
            $order_id = $this->pos_create_order($customer_id, abs($amount), null, array('adjustment' => 'debit'), $staff_note);
            
            $comment_invoice = array(
                'comment_author' => $order_id,
                'comment_content' => 'Adjustment for $'.abs($amount),
                'user_id' => $customer_id,
                'comment_meta'         => array(
                    'is_customer_note'       => sanitize_text_field(1),
                    'is_adjustment'       => sanitize_text_field(1),
                    'adjustment_total'    => abs($amount)
                    )
                );

                wp_insert_comment($comment_invoice);
        } else {
            $order_id = $this->pos_create_order($customer_id, $amount, null, array('adjustment' => 'credit'), $staff_note);
        }

        return array('id' => $order_id);
    }

    public function cash_method($customer_id, $amount, $order, $cash_receipt, $staff_note, $metadata) {

        $invalid_fields = [];

        if (!empty($cash_receipt)) {
            if(!empty($order)) {
                $this->pos_update_order($order, $amount, 'cash', $staff_note);
            }
            $order_id = $this->pos_create_order($customer_id, $amount, $order, array('cash' => $cash_receipt, 'is_discount' => $metadata['is_discount'], 'is_fee' => $metadata['is_fee']), $staff_note);
            return array('id' => $order_id);
        } else {
            $invalid_fields[] = 'Please enter a cash receipt';
            return $invalid_fields;
        }
        

    }

    public function card_method($customer_id, $card_id, $stripe_token, $order, $amount, $card_exists, $staff_note, $metadata) {
        if (isset($amount['amount'])) {
            $amounts = $amount;
            $amount = $amounts['amount'];
        }
        $stripe = new \Stripe\StripeClient(STRIPE_TEST_KEY);

        $is_invalid = [];

        $amount_in_cents = intval($amount * 100);
        
        if ($card_exists == 'add_card') {
            if (empty($stripe_token)
            ) {
                $is_invalid[] = 'Please enter your credit card details.';
            }
            
        } else if ($card_exists == 'card_exists') {
            if (!empty($card_id)) {
                $payment_method = $stripe->paymentMethods->retrieve(
                    $card_id
                );

                if (isset($payment_method)) {
                    $payment_method = $payment_method->id;
                }
            }
        }

        if (empty($is_invalid) && !empty($amount) ) {

            $user = get_user_by('id', $customer_id);
            $stripe_cus_id = get_user_meta($customer_id, 'wp__stripe_customer_id', true);
            if (empty($stripe_cus_id)) {

                try {
                    $customer = $stripe->customers->create([
                        'email' => $user->user_email,
                        'source' => $stripe_token
                    ]);
                    $payment_method = $customer->default_source;
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
                } else {
                    $stripe_cus_id = $customer->id;
                    update_user_meta($customer_id, 'wp__stripe_customer_id', $stripe_cus_id);
                }
            } elseif (!empty($stripe_cus_id) && !empty($stripe_token)) {
                try {
                    $payment_method = $stripe->customers->createSource($stripe_cus_id, ['source' => $stripe_token]);
                    $payment_method = $payment_method->id;
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
            }

            if (isset($amounts)) {
                $amount = $amounts['original_amount'];
            }

            if(!empty($order)) {
                $parent_order_id = $this->pos_update_order($order, $amount, 'card', $staff_note);
            }

            $order_id = $this->pos_create_order($customer_id, $amount, $order, array('stripe_cus_id' => $stripe_cus_id, 'payment_method' => $payment_method, 'card' => 1, 'is_discount' => $metadata['is_discount'], 'is_fee' => $metadata['is_fee']), $staff_note);
            
            $metadata = ['order_id' => $order_id, 'customer_email' => $user->user_email, 'customer_name' => $user->first_name . ' ' . $user->last_name, 'save_payment_method' => 'true', 'site_url' => site_url()];

            try {
                $payment_intent = $stripe->paymentIntents->create([
                    'amount' => $amount_in_cents,
                    'currency' => 'usd',
                    'customer' => $stripe_cus_id,
                    'metadata' => $metadata,
                    'payment_method' => $payment_method,
                    'description' => 'Gymnastics of York - Order #'. $order_id,
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

            if (empty($is_invalid) && $payment_intent->status == 'succeeded') {
                update_post_meta($order_id, '_stripe_intent_id', $payment_intent->id);
                return array('id' => $order_id);
            } else {
                if (isset($is_invalid[0])){
                    $note = $is_invalid[0];
                } else {
                    $note = 'Credit Card Payment failed';
                }
                $new_order = wc_get_order($order_id);
                $new_order->update_status('cancelled', $note);

                if (isset($parent_order_id)) {
                    $parent_order = wc_get_order($parent_order_id);
                    $parent_order->update_status('on-hold', $note);
                    update_post_meta($parent_order_id, 'is_paid', 0);
                }

                return $is_invalid;
            }
        
        } else {
            return $is_invalid;
        }

    }

    public function check_method($customer_id, $amount, $check_number, $order, $staff_note) {
        $is_invalid = [];
        
        if (!empty($check_number)) {
            if(!empty($order)) {
                $this->pos_update_order($order, $amount, 'check', $staff_note);
            }
            $order_id = $this->pos_create_order($customer_id, $amount, $order, array('check_number' => $check_number), $staff_note);
            return array('id' => $order_id);
        } else {
            $is_invalid[] = 'Please enter a check number.';
            return $is_invalid;
        }

    }

    public function pos_update_order($order, $amount, $method, $staff_note) {
        global $wpdb;

        $sql = 'SELECT post_type FROM '.$wpdb->posts.' WHERE ID = %d';
        $where = [$order->get_id()];
        $result = $wpdb->get_results($wpdb->prepare($sql, $where));

        $admin_id = get_current_user_id();
        $admin = get_user_by('id', $admin_id);

        $note = 'Payment recorded through GYCRM by user '.$admin->display_name;

        switch($method) {
            case 'card':
                $note = 'Credit Card (Stripe) '.$note;
            break;
            case 'check':
                $note = 'Check '.$note;
            break;
            case 'cash':
                $note = 'Cash '.$note;
            break;
        }

        if ($result[0]->post_type == 'shop_subscription') {
            $parent_id = $order->get_parent_id();
            
            $parent_order = wc_get_order($parent_id);
            
            $parent_order->update_status('on-hold');
            $order->update_status('on-hold', $note);
        } else {
            $order->update_status('on-hold', $note);
        }

        $is_payment_plan = get_post_meta($order->get_id(), 'is_payment_plan', true);

        if ($is_payment_plan == 1) {
            update_post_meta($order->get_id(), 'is_paid', 1);
        }

        if (!empty($staff_note)) {
            $order->add_order_note($staff_note);
        }

        $order->add_order_note($note);

        return $order->get_id();
    }

    public function pos_create_order($customer_id, $amount, $parent_order, $metadata, $staff_note) {
        global $wpdb;

        $user = get_user_by('id', $customer_id);
        $admin_id = get_current_user_id();
        $admin = get_user_by('id', $admin_id);

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

        $note = 'Payment recorded through GYCRM by user '.$admin->display_name;
        
        if (isset($parent_order)) {
            $note .= ' for Order #'.$parent_order->get_id();
            update_post_meta($order->get_id(), '_invoice_id', $parent_order->get_id());
        }
        
        if (isset($metadata['card'])) {
            $note = 'Credit Card (Stripe) '.$note;
            $item_name = 'Credit Card (Stripe)';

            update_post_meta($order->get_id(), '_stripe_customer_id', $metadata['stripe_cus_id']);
            update_post_meta($order->get_id(), '_stripe_source_id', $metadata['payment_method']);
            update_post_meta($order->get_id(), '_payment_method', 'stripe');
        }

        if (isset($metadata['check_number'])) {
            $note = 'Check '.$note;
            $item_name = 'Check No '.$metadata['check_number'];

            update_post_meta($order->get_id(), '_payment_receipt', $metadata['check_number']);            
            update_post_meta($order->get_id(), '_payment_method', 'cheque');
        }
        
        if (isset($metadata['cash'])) {
            $note = 'Cash '.$note;
            $item_name = 'Cash Receipt No #'.$metadata['cash'];

            update_post_meta($order->get_id(), '_payment_receipt', $metadata['cash']);            
            update_post_meta($order->get_id(), '_payment_method', 'cash');
        }
        
        if (isset($metadata['credit'])) {
            $note = 'Credit added through GYCRM by user '.$admin->display_name;
            $item_name = 'Account Credit';
        }

        if (isset($metadata['discount'])) {
            $note = 'Discount of '.$metadata['percentage'].'% added to Order No '.$order->get_id() - 1 .' through GYCRM by user '.$admin->display_name;
            $item_name = ucwords($metadata['payment_method']).' Credit';
        }

        if (isset($metadata['fee'])) {
            $note = 'Fee of '.$metadata['percentage'].'% added to Order No '.$order->get_id() - 1 .' through GYCRM by user '.$admin->display_name;
            $item_name = ucwords($metadata['payment_method']).' Fee';
        }

        if (isset($metadata['adjustment'])) {
            $note = 'Adjustment added through GYCRM by user '.$admin->display_name;
            $item_name = 'Entry Adjustment';
        }

        if (isset($metadata['ach'])) {
            $note = 'ACH (Stripe) '.$note;
            $item_name = 'ACH (Stripe)';

            update_post_meta($order->get_id(), '_stripe_customer_id', $metadata['stripe_cus_id']);
            update_post_meta($order->get_id(), '_stripe_source_id', $metadata['payment_method']);
            update_post_meta($order->get_id(), '_payment_method', 'stripe_ach');
        }

        if (isset($metadata['is_discount']) && !empty($metadata['is_discount'])) {
            $discount_note = 'Added Discount of %'.$metadata['is_discount'];
            $order->add_order_note($discount_note);
            update_post_meta($order->get_id(), '_discount_percentage', $metadata['is_discount']);
        }
        
        if (isset($metadata['is_fee']) && !empty($metadata['is_fee'])) {
            $fee_note = 'Added Fee of %'.$metadata['is_fee'];
            $order->add_order_note($fee_note);
            update_post_meta($order->get_id(), '_fee_percentage', $metadata['is_fee']);
        }
        
        foreach( $order->get_items() as $item_id => $item ){
            $item->set_name( $item_name );
            $item->set_subtotal($amount); 
            $item->set_total( $amount);
            $item->calculate_taxes();
            $item->save();
        }

        $order->calculate_totals();
        
        if (isset($metadata['adjustment']) && $metadata['adjustment'] == 'debit') {
            $order->update_status( 'pending', $note);
        } else {
            $order->update_status( 'processing', $note);
        }

        if (isset($metadata['parent_id'])) {
            update_post_meta($order->get_id(), '_parent_id', $metadata['parent_id']);
        }
        
        update_post_meta($order->get_id(), '_user_id', $admin_id);

        if (!empty($staff_note)) {
            $order->add_order_note($staff_note);
        }
        
        return $order->get_id();
    }

    public function pos_admin_menu() {
        add_menu_page(
            'Point of Sale',
            'Point of Sale',
            'edit_pos_payments',
            'pos-admin-page',
            array($this, 'pos_admin_page_content'),
            'dashicons-cart', // Icon
        );
        add_submenu_page(
            'pos-admin-page',  // this is the name of the menu that exists
            'Add Order',          // submenu title
            'Add Order', // submenu title 
            'edit_pos',         
            'pos-add-order',      // slug or url of the submenu
            array($this,'pos_add_order') // callback
        );
        add_submenu_page(
            'pos-admin-page',
            'Accounts Owing',
            'Accounts Owing',
            'edit_pos',
            'pos_owes_list',
            array($this,'pos_owes_list')
        );
        add_submenu_page(
            'pos-admin-page',
            'Invoices List',
            'Invoices List',
            'edit_pos',
            'pos-invoices-list',
            array($this,'pos_invoices_list')
        );
        add_submenu_page(
            'pos-admin-page',
            'Manual Invoices',
            'Manual Invoices',
            'edit_pos',
            'manual_invoices',
            array($this,'manual_invoices')
        );
        add_submenu_page(
            'pos-admin-page',
            'Credit Card Report',
            'Credit Card Report',
            'edit_pos',
            'credit_card_list',
            array($this,'credit_card_list')
        );
        add_submenu_page(
            'pos-admin-page',
            'No Credit Card Report',
            'No Credit Card Report',
            'edit_pos',
            'no_credit_card_list',
            array($this,'no_credit_card_list')
        );
        add_submenu_page(
            'pos-admin-page',
            'Payment Plans',
            'Payment Plans',
            'edit_pos',
            'payment_plans',
            array($this,'payment_plans_list')
        );
        add_submenu_page(
            'pos-admin-page',
            'Payment Refund',
            'Payment Refund',
            'edit_pos',
            'payment_refund',
            array($this,'payment_refund')
        );
        add_submenu_page(
            'pos-admin-page',
            'Manage Categories',
            'Manage Categories',
            'edit_pos',
            'manage_categories',
            array($this,'manage_categories')
        );
    }

    public function get_payments() {
        global $wpdb;
        $html = '';
        $sql = 'SELECT p.ID, p.post_date, po.meta_value AS payment_method
                FROM wp_posts p
                JOIN wp_postmeta po
                    ON p.ID = po.post_id
                    AND po.meta_key = "_payment_method"
                    AND p.post_status != "trash"
                ORDER BY p.post_date DESC';

        $results = $wpdb->get_results($sql);

        foreach($results as $result) {
            $order = wc_get_order($result->ID);
            $amount = $order->get_total();

            if ($amount > 0) {
                if ($result->payment_method == 'ach_stripe' || $result->payment_method == 'stripe_ach') {
                    $payment_method = 'ACH (Stripe)';
                } else if ($result->payment_method == 'stripe') {
                    $payment_method = 'Credit Card (Stripe)';
                } else {
                    $payment_method = ucfirst($result->payment_method);
                }
    
                $html .= '<tr>
                            <td>'.wc_price($amount).'</td>
                            <td>'.$result->post_date.'</td>
                            <td>'.$payment_method.'</td>
                            <td class="edit-btn" data-id="'.$result->ID.'" data-modal="#view_payment"><svg fill="#2271b1" width="30px" height="30px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 442.04 442.04" xml:space="preserve" stroke="#2271b1"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M221.02,341.304c-49.708,0-103.206-19.44-154.71-56.22C27.808,257.59,4.044,230.351,3.051,229.203 c-4.068-4.697-4.068-11.669,0-16.367c0.993-1.146,24.756-28.387,63.259-55.881c51.505-36.777,105.003-56.219,154.71-56.219 c49.708,0,103.207,19.441,154.71,56.219c38.502,27.494,62.266,54.734,63.259,55.881c4.068,4.697,4.068,11.669,0,16.367 c-0.993,1.146-24.756,28.387-63.259,55.881C324.227,321.863,270.729,341.304,221.02,341.304z M29.638,221.021 c9.61,9.799,27.747,27.03,51.694,44.071c32.83,23.361,83.714,51.212,139.688,51.212s106.859-27.851,139.688-51.212 c23.944-17.038,42.082-34.271,51.694-44.071c-9.609-9.799-27.747-27.03-51.694-44.071 c-32.829-23.362-83.714-51.212-139.688-51.212s-106.858,27.85-139.688,51.212C57.388,193.988,39.25,211.219,29.638,221.021z"></path> </g> <g> <path d="M221.02,298.521c-42.734,0-77.5-34.767-77.5-77.5c0-42.733,34.766-77.5,77.5-77.5c18.794,0,36.924,6.814,51.048,19.188 c5.193,4.549,5.715,12.446,1.166,17.639c-4.549,5.193-12.447,5.714-17.639,1.166c-9.564-8.379-21.844-12.993-34.576-12.993 c-28.949,0-52.5,23.552-52.5,52.5s23.551,52.5,52.5,52.5c28.95,0,52.5-23.552,52.5-52.5c0-6.903,5.597-12.5,12.5-12.5 s12.5,5.597,12.5,12.5C298.521,263.754,263.754,298.521,221.02,298.521z"></path> </g> <g> <path d="M221.02,246.021c-13.785,0-25-11.215-25-25s11.215-25,25-25c13.786,0,25,11.215,25,25S234.806,246.021,221.02,246.021z"></path> </g> </g> </g></svg></td>
                        </tr>';
            }

        }

        if (!empty($html)) {
            $html .= '<tr>
                    <td colspan="4">No items</td>
                </tr>';
        }
        
        return $html;
    }

    public function payment_refund() {
        global $wpdb;
        $admin = wp_get_current_user();

        $stripe = new \Stripe\StripeClient(STRIPE_TEST_KEY);

        if (isset($_POST['payment_order_id'])) {

            if (isset($_POST['parent_order_id']) && !empty($_POST['parent_order_id'])) {
                $invoice_id = $_POST['parent_order_id'];
                $invoice = wc_get_order($invoice_id);

                if ($invoice->order_type == 'shop_subscription') {
                    $invoice_id = $invoice->get_parent_id();
                    $invoice = wc_get_order($invoice_id);
                }
            }
            
            $order_id = $_POST['payment_order_id'];
            $order = wc_get_order( $order_id );

            $refunded_total = $order->get_total_refunded();
            $order_total = $order->get_total();

            $net_payment = $order_total - $refunded_total;

            $type = $_POST['type_refund'];
            $pm = $_POST['payment_method'];
            $partial_refund_items = $_POST['partial_refund_item'];
            $partial_refund_amount = $_POST['refund_amount'];
            $electronic_refund = $_POST['electronic_processing'];
            $refund_amount = 0;

            if ($type == 'partial_refund') {
                if (!empty($partial_refund_items) && isset($invoice)) {
                    foreach ($invoice->get_items() as $item_id => $item) {
                        if (isset($partial_refund_items[$item_id]['id'])) {
                            $item_amount = $partial_refund_items[$item_id]['amount'];
                            $refund_amount += $item_amount;
                            $refund_items[$item_id] = $item_amount;
                        }
                    }
                }

                if (!isset($refund_items)) {
                    $refund_amount = $partial_refund_amount;
                }
                
            } else {
                if ($refunded_total > 0) {
                    $refund_amount = $net_payment;
                } else {
                    $refund_amount = $type;
                }
            }

            if ($refund_amount <= $net_payment) {
                $args = array(
                    'amount'         => $refund_amount,
                    'reason'         => '',
                    'order_id'       => $order_id,
                    'line_items'     => [],
                    'refund_payment' => false,

                );
    
                $result = wc_create_refund($args);

                if (isset($result->errors['error'])) {
                    ?>
                    <style>
                        .error.notice-warning {
                            display: block !important;
                        }
                        .error.notice-warning::after {
                            content: ': <?= $result->errors['error'][0] ?>';
                        }
                    </style>
                    <?php
                } else {
                    update_post_meta($result->get_id(), '_order_note', 'Payment for $'.$net_payment.' refunded for $'.$refund_amount.' by user '.$admin->display_name);

                    if (isset($refund_items)) {
                        foreach($refund_items as $item_id => $item) {
                            $refunded = wc_get_order_item_meta($item_id, '_refund_amount', $item);
                            if (!empty($refunded)) {
                                $item += $refunded;
                            }

                            wc_update_order_item_meta($item_id, '_refund_amount', $item);
                        }
                    }

                    if (isset($invoice)) {
                        $invoice->update_status('pending');
                    } else {
                        update_post_meta($result->get_id(), '_type_refund', 'credit');
                    }

                    $sql = 'UPDATE wp_posts SET post_status = "wc-processing" WHERE ID = '.$order->get_id();
                    
                    if ($pm == 'stripe' || $pm == 'ach_stripe' || $pm == 'stripe_ach') {
                        if (!isset($electronic_refund)) {
                            $wpdb->query($sql);
                            $payment_intent = get_post_meta($order_id, '_stripe_intent_id', true);
                            $cents = floatval($refund_amount) * 100;
                            
                            try {
                                $refund_id = $stripe->refunds->create([
                                    'payment_intent' => $payment_intent,
                                    'amount' => intval($cents)
                                ]);
                            } catch (\Stripe\Exception\RateLimitException $e) {
                                $is_invalid = 'Our server is currently experiencing high traffic. Please try again later.';
                            } catch (\Stripe\Exception\InvalidRequestException $e) {
                                $is_invalid = 'We are sorry, we cannot process your request. Please try again later.';
                            } catch (\Stripe\Exception\AuthenticationException $e) {
                                $is_invalid = 'We are sorry, we cannot authenticate your request. Please try again later.';
                            } catch (\Stripe\Exception\ApiConnectionException $e) {
                                $is_invalid = 'We are sorry, we are experiencing connection issues. Please try again later.';
                            } catch (\Stripe\Exception\ApiErrorException $e) {
                                $is_invalid = 'We are sorry, we are experiencing connection issues. Please try again later.';
                            } catch (Exception $e) {
                                $is_invalid = 'Unknown Error. Please try again later.';
                            }

                            if (!isset($is_invalid)) {
                                update_post_meta($result->get_id(), '_stripe_refund_id', $refund_id->id);
                            } else {
                                wp_delete_post($result->get_id(), true);
                                ?>
                                <style>
                                    .error.notice-warning {
                                        display: block !important;
                                    }
                                    .error.notice-warning::after {
                                        content: ' from Stripe: <?=$is_invalid?>';
                                    }
                                </style>
                                <?php
                            }
                        } else {
                            update_post_meta($result->get_id(), '_type_refund', 'credit');
                        }
                    } else {
                        $wpdb->query($sql);
                    }


                    if (!isset($is_invalid)) {
                        ?>
                        <style>
                            .notice-success {
                                display: block !important;
                            }
                            .notice-success::after {
                                content: 'Order #<?= $order_id?> refunded.';
                            }
                        </style>
                        <?php
                    }


                }
            } else {
                ?>
                <style>
                    .global.notice-warning {
                        display: block !important;
                    }
                </style>
                <?php
            }
        }

        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/easy_pos/payment_refund.php';
    }

    public function get_payment_plans() {
        global $wpdb;
        
        $plans = [];
        $html = '';

        $sql = 'SELECT c.comment_ID, c.user_id, u1.meta_value AS first_name, u2.meta_value AS last_name, c.comment_date AS start_date, co2.meta_value AS original_balance, co3.meta_value AS remaining_balance, co4.meta_value AS payment_plan_months
                FROM wp_comments c
                JOIN wp_commentmeta co
                    ON c.comment_ID = co.comment_id
                JOIN wp_commentmeta co2
                    ON c.comment_ID = co2.comment_id
                JOIN wp_commentmeta co3
                    ON c.comment_ID = co3.comment_id
                JOIN wp_commentmeta co4
                    ON c.comment_ID = co4.comment_id
                JOIN wp_usermeta u1
                    ON c.user_id = u1.user_id
                JOIN wp_usermeta u2
                    ON c.user_id = u2.user_id
                    AND u1.meta_key = "first_name"
                    AND u2.meta_key = "last_name"
                    AND co4.meta_key = "payment_plan_months"
                    AND co3.meta_key = "remaining_balance"
                    AND co2.meta_key = "payment_plan_total"
                    AND co.meta_key = "is_payment_plan"
                    AND c.comment_ID NOT IN (
                            SELECT c.comment_ID
                            FROM wp_comments c
                            JOIN wp_commentmeta co
                                ON c.comment_ID = co.comment_id
                        AND co.meta_key = "plan_id"
                )';

        $results = $wpdb->get_results($sql);
        $today = date('Y-m-d');

        if (!empty($results)) {
            foreach($results as $result) {

                $start_date = date('Y-m-d', strtotime($result->start_date));
                $months = $result->payment_plan_months;
                $remaining_balance = '';

                $plans[$result->comment_ID] = array(
                    'user_id' => $result->user_id,
                    'first_name' => $result->first_name,
                    'last_name' => $result->last_name,
                    'start_date' => $start_date,
                    'end_date' => date('Y-m-d', strtotime('+'.$months.' months', strtotime($start_date))),
                    'payment_plan_months' => $months,
                    'original_balance' => $result->original_balance,
                );

                $sql = 'SELECT co2.meta_value AS remaining_balance, co3.meta_value AS due_date
                        FROM wp_comments c
                        JOIN wp_commentmeta co
                            ON c.comment_ID = co.comment_id
                        JOIN wp_commentmeta co2
                            ON c.comment_ID = co2.comment_id
                        JOIN wp_commentmeta co3
                            ON c.comment_ID = co3.comment_id
                                AND co.meta_key = "plan_id"
                                AND co.meta_value = %s
                                AND co2.meta_key = "remaining_balance"
                                AND co3.meta_key = "due_date"
                        ORDER BY c.comment_author ASC';
                
                $child_plan = $wpdb->get_results($wpdb->prepare($sql, [$result->comment_ID]));

                foreach ($child_plan as $plan) {
                    if ($today >= $plan->due_date) {
                        $remaining_balance = $plan->remaining_balance;
                    }
                }

                if (!empty($remaining_balance)) {
                    $plans[$result->comment_ID]['remaining_balance'] =  $remaining_balance;
                } else {
                    if ($today < $start_date) {
                        $plans[$result->comment_ID]['remaining_balance'] = $result->original_balance;
                    } else {
                        $plans[$result->comment_ID]['remaining_balance'] = $result->remaining_balance;
                    }
                }

            }
        }

        foreach ($plans as $plan) {
            $html .= '<tr>
                        <td><a href="/wp-admin/admin.php?page=user-information-edit&user='.$plan['user_id'].'&child=no" targer="_blank">'.$plan['first_name'].'</a></td>
                        <td><a href="/wp-admin/admin.php?page=user-information-edit&user='.$plan['user_id'].'&child=no" targer="_blank">'.$plan['last_name'].'</a></td>
                        <td>'.$plan['start_date'].'</td>
                        <td>'.$plan['end_date'].'</td>
                        <td>'.$plan['payment_plan_months'].'</td>
                        <td>'.wc_price($plan['original_balance'], array('decimals' => 2)).'</td>
                        <td>'.wc_price($plan['remaining_balance'], array('decimals' => 2)).'</td>
                    </tr>';
        }

        if (empty($plans)){
            $html .= '<tr>
                        <td colspan="7">No items</td>
                    </tr>';
        }

        return $html;
    }
    public function payment_plans_list() {
        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/easy_pos/payment_plans.php';
    }

    public function manage_categories() {
        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/easy_pos/manage_categories.php';
    }

    public function render_invoice($id) {
        $user = get_user_by('id', $id);

        $is_due_registration = false;
        $registration_month = get_user_meta($user->ID, 'due_registration_month', true);
        $current_month = date('F');

        if ($registration_month == $current_month) {
            $is_due_registration = true;
        }

        $html = '';

        $has_siblings = false;
        $sibling_count = 0;
        $sibling_rate = array();
        $siblings_total = 0;
        
        $total_hours = 0;

        $children = get_user_meta($user->ID, 'smuac_multiaccounts_list', true);

        $html .= '<div class="athletes-container">
                    <h2>Enrolled Athletes</h2>
                    <hr class="divider">';

        if ($children) {

            $children = explode(',', get_user_meta($user->ID, 'smuac_multiaccounts_list', true));

            if (count($children) >= 3){
                $has_siblings = true;
            }

            foreach($children as $key => $child) {
                $is_class = false;

                if (!empty($child)) {
                    
                    $counter = 0;
                    
                    $total_hours_per_child = 0;
                    
                    $classes = get_user_meta($child, 'classes', true);
                    $status = get_user_meta($child, 'status_program_participant', true);

                    if (is_array($classes[0])) {
                        $classes = $classes[0];
                    }

                    foreach($classes as $class) {
                        if (!empty($class)) {
                            $is_class = true;
                        }
                    }

                    if (!empty($classes) && $is_class && $status == 'active') {
                    $sibling_count += 1;
                    
                    $html .= '<div data-athlete="athlete_'.$child.'" class="athlete-items">
                    <div class="flex-container athlete-section">
                            <h3>'.get_user_meta($child, 'first_name', true).'</h3>
                        <div class="flex-container">
                            <select>
                                '.get_attendance_history($child).'
                            </select>
                            <button type="button" value="class" data-id="'.$child.'" id="add_button" class="add-item">Add item</button>
                        </div>
                    </div>
                    <div class="panel">
                        <table>
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Class</th>
                                    <th>Total Hours</th>
                                </tr>
                            </thead>
                            <tbody id="athlete_'.$child.'">';
                    
                    foreach ($classes as $class) {
                        if (!empty($class) && !empty(get_post($class))) {
                            $product_id = get_post_meta($class, 'product_id', true);
                            $products_ids[] = $product_id;

                            $hours_per_week = get_post_meta($class, 'hours_per_week', true);

                            if ($hours_per_week) {

                                $counter += 1;

                                $total_hours_per_child += floatval($hours_per_week);
                                $total_hours += floatval($hours_per_week);

                                $price_per_child = $this->price_per_hour[strval($total_hours_per_child)];
                                
                                $html .= '<tr class="order-item" id="order-item-'.$class.'-'.$child.'">
                                <td class="delete-order-item"><div class="delete-order-item-icon"></div></td>
                                    <td>
                                        <select class="className" data-athlete="'.$child.'" name="order_item['.$child.'][classes]['.$counter.'][id]">
                                        '.get_classes($class).'
                                        </select>
                                    </td>
                                    <td><input class="total-hours" type="number" name="order_item['.$child.'][classes]['.$counter.'][hours]" value="'.$hours_per_week.'"/></td>
                                </tr>';
                            }
                        }
                        }

                        if ($has_siblings) {
                            $sibling_rate[] = $price_per_child;
                        }

                        $siblings_total += $price_per_child;

                        $html .= '</tbody>
                                    <tfoot class="totals">
                                        <tr>
                                            <th colspan="2" class="total-label">Total</th>
                                            <td><span>$</span><span><input type="number" name="order_item['.$child.'][amount]" class="order-total" step="0.1" value="'.$price_per_child.'"</span></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>';
                    }
                }
            }

            echo '<div>';

            if ($total_hours > 0) {
                if (count($sibling_rate) > 1 && $sibling_count >= 1) {
                    $total_amount = $siblings_total;
                } else {
                    $total_amount = $this->price_per_hour[strval($total_hours)];
                }

                $html .= '<div class="flex-container not-container total-accent" id="subtotal" data-amount="'.$total_amount.'"><span>SUBTOTAL: $</span><span class="order-total">'.$total_amount.'</span></div>';

                $html .= '<div class="discount-fee-section">
                        <h2>Discounts/Fees</h2>
                        <hr class="divider">
                        <div class="flex-container discount-fee-buttons not-container">
                            <div class="flex-container">
                                <button type="button" id="add_button" value="discount" class="add-item add-discount '.(count($sibling_rate) <= 3 && $sibling_count <= 1 ? '' : 'hidden').'">Add Sibling Discount</button>
                                <button type="button" id="add_button" value="fee" class="add-item add-fee '.(count($sibling_rate) <= 3 && $sibling_count <= 1 ? '' : 'hidden').'">Add Registration Fee</button>
                            </div>
                        </div>
                <div class="panel">
                    <table>
                        <thead>
                            <tr>
                                <th></th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Percentage/Fee</th>
                                <th>Amount</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="discount-fee-section">';

                if (count($sibling_rate) > 1 && $sibling_count >= 1) {
                    $last_sibling_rate = min($sibling_rate);

                    $discount = $last_sibling_rate * (10 / 100);
                    $total_amount = $total_amount - $discount;

                    $html .= '<tr class="order-item" id="order-item-discount-0">
                                <td class="delete-order-item"><div class="delete-order-item-icon"></div></td>
                                <td>Discount</td>
                                <td><input type="text" name="item[discount][description]" value="Sibling Discount"/></td>
                                <td><input type="number" step="0.1" value="10" class="order-item-discount order-item-disfee" name="item[discount][amount]"></td>
                                <td><span>-$</span><span class="order-discount-amount">'.$discount.'</span></td>
                                <td><span>$</span><span class="order-discount-total">'.$total_amount.'</span></td>
                            </tr>';
                }

                if ($is_due_registration) {
                    $total_amount += $this->registration_fee;

                    $html .= '<tr class="order-item" id="order-item-fee-0">
                                <td class="delete-order-item"><div class="delete-order-item-icon"></div></td>
                                <td>Fee</td>
                                <td><input type="text" name="item[fee][description]" value="Registration Fee"/></td>
                                <td><input type="number" name="item[fee][amount]" class="order-item-fee order-item-disfee" step="0.1" value="'.$this->registration_fee.'"/></td>
                                <td><span>$</span><span class="order-fee-amount">'.$this->registration_fee.'</span></td>
                                <td><span>$</span><span class="order-fee-total">'.$total_amount.'</span></td>
                            </tr>';
                }

                $html .= '</tbody>';

                if (count($sibling_rate) > 1 || $is_due_registration) {
                    $html .= '<tfoot class="totals">';
                                        
                } else {
                    $html .= '<tfoot class="totals hidden">';
                }

                $html .= '<tr>
                                <th colspan="5"></th>
                                <td><span>$</span><span id="order-total">'.$total_amount.'</span></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="flex-container total-accent not-container" id="total"><span>TOTAL: $</span><span class="order-total">'.$total_amount.'</span></div>';

            }
        }

        return $html;
    }

    public function manual_invoices() {
        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/easy_pos/manual_invoices.php';
    }

    public function get_monthly_invoices() {
        global $wpdb;

        if (isset($_GET['date'])) {
            $current_date = $_GET['date'];
        } else {
            $current_date = date('Y-m');
        }

        $sql = 'SELECT u.ID, um2.meta_value AS first_name, um3.meta_value AS last_name, c.comment_ID AS invoice_id
                FROM wp_users u
                JOIN wp_usermeta um ON u.ID = um.user_id AND um.meta_key = "smuac_multiaccounts_list"
                JOIN wp_usermeta um2 ON u.ID = um2.user_id AND um2.meta_key = "first_name"
                JOIN wp_usermeta um3 ON u.ID = um3.user_id AND um3.meta_key = "last_name"
                LEFT JOIN wp_comments c
                    ON c.user_id = u.ID
                    AND c.comment_date LIKE %s
                JOIN wp_commentmeta co
                    ON c.comment_ID = co.comment_id
                    AND co.meta_key = "is_monthly"
                UNION
                SELECT u.ID, um2.meta_value AS first_name, um3.meta_value AS last_name, NULL AS invoice_id
                FROM wp_users u
                JOIN wp_usermeta um ON u.ID = um.user_id AND um.meta_key = "smuac_multiaccounts_list"
                JOIN wp_usermeta um2 ON u.ID = um2.user_id AND um2.meta_key = "first_name"
                JOIN wp_usermeta um3 ON u.ID = um3.user_id AND um3.meta_key = "last_name"
                    AND u.ID NOT IN (
                        SELECT u.ID
                        FROM wp_users u
                        JOIN wp_usermeta um ON u.ID = um.user_id AND um.meta_key = "smuac_multiaccounts_list"
                        JOIN wp_usermeta um2 ON u.ID = um2.user_id AND um2.meta_key = "first_name"
                        JOIN wp_usermeta um3 ON u.ID = um3.user_id AND um3.meta_key = "last_name"
                        LEFT JOIN wp_comments c
                                ON c.user_id = u.ID
                                AND c.comment_date LIKE %s
                            JOIN wp_commentmeta co
                                ON c.comment_ID = co.comment_id
                                AND co.meta_key = "is_monthly"
                            )';
                
        if (isset($_GET['ord'])) {
            $order = $_GET['ord'];
        } else {
            $order = 'ASC';
        }

        if (isset($_GET['by'])) {
            $by = $_GET['by'];
        } else {
            $by = 'first_name';
        }
        
        $sql .= ' ORDER BY '.$by . ' ' .$order;

        $list = $wpdb->get_results($wpdb->prepare($sql, ["%$current_date%","%$current_date%"]));

        $html = '';

        if (!empty($list)) {

            foreach ($list as $user) {
                $is_enrolled = get_user_meta($user->ID, 'smuac_multiaccounts_list', true);
                $is_active = false;
                $children = explode(',', $is_enrolled);

                foreach($children as $child) {
                    if (!empty($child)) {
                        $status = get_user_meta($child, 'status_program_participant', true);

                        if ($status == 'active') {
                            $is_active = true;
                        }
                    }
                }

                if ($is_active) {
                    $html .= '<tr>
                                <td><a href="/wp-admin/admin.php?page=user-information-edit&user='.$user->ID.'&child=no" target="_blank">'.$user->first_name.'</a></td>
                                <td><a href="/wp-admin/admin.php?page=user-information-edit&user='.$user->ID.'&child=no" target="_blank">'.$user->last_name.'</a></td>
                                <td>'.(!empty($user->invoice_id) ? '#'.$user->invoice_id : '<a href="/wp-admin/admin.php?page=pos-add-order&minvoice=1&user='.$user->ID.'" target="_blank">Send invoice</a>' ).'</td>
                            </tr>';
                }
            }
        } else {
            $html .= '<tr>
                        <td colspan="3">No items</td>
                    </tr>';
        }

        return $html;
    }

    // Callback for the admin page content
    public function pos_admin_page_content() {

        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            require GY_CRM_PLUGIN_DIR . 'views/js/easy-pos.php';
            
            echo do_shortcode('[easy_pos_shortcode search_users="true" get_billing_history="true" set_user="'.$_GET['id'].'"]');
        } else {
            echo do_shortcode('[easy_pos_shortcode search_users="true" get_billing_history="true"]');
        }
    }
    public function pos_get_all_customers() {
        $users = get_users();

        $customers = array();

        foreach($users as $user) {
            $children = get_user_meta($user->ID, 'smuac_multiaccounts_list', true);
            
            if (!empty($children)) {
                $child_names = array();
                $children = explode(',', $children);

                foreach($children as $child) {
                    $first_name = get_user_meta($child, 'first_name', true);
                    $last_name = get_user_meta($child, 'last_name', true);

                    if (!empty($first_name) && !empty($last_name)) {
                        $child_names[] = $first_name.' '.$last_name;
                    }
                }

                if (isset($child_names)) {
                    $customers[] = array(
                        'id' => $user->ID,
                        'parent_name' => get_user_meta($user->ID, 'first_name', true).' '.get_user_meta($user->ID, 'last_name', true),
                        'children' => $child_names,
                    );
                }
            }
        }
    
        return $customers;
    }
    
    // Custom function to fetch orders by customer ID from the database
    public function get_orders_by_customer_id($customer_id) {
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

    public function pos_display_categories($cat_parents, $multi = true) {

        if ($multi) {
            $html = '<ul class="dd-menu">';
        } else {
            $html = '<select id="parent_category" name="parent_category"><option value="">Select category</option>';
        }

        foreach($cat_parents as $key => $cat) {
            if (is_array($cat)) {
                $parent_id = array_keys($cat);
                if ($multi) {
                    $html .= '<li><input name="order_item[0][category][]" value="'.$parent_id[0].'" type="checkbox">'.$key.'</li>';
                    $html .= '<ul>';
                } else {
                    $html .= '<option value="'.$parent_id[0].'">'.$key.'</option>';
                }
                foreach($cat[$parent_id[0]] as $id => $child) {
                    if ($multi) {
                        $html .= '<li><input name="order_item[0][category][]" value="'.$id.'" type="checkbox">'.$child.'</li>';
                    } else {
                        $html .= '<option value="'.$id.'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$child.'</option>';
                    }
                }
                if ($multi) {
                    $html .= '</ul>';
                }
            } else {
                if ($multi) {
                    $html .= '<li><input name="order_item[0][category][]" value="'.$key.'" type="checkbox">'.$cat.'</li>';
                } else {
                    $html .= '<option value="'.$key.'">'.$cat.'</option>';
                }
            }
        }

        if ($multi) {
            $html .= '</ul>';
        } else {
            $html .= '</select>';
        }
    

        return $html;
    }
}

