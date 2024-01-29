<script>
    (function ($) {
        $(document).ready(function () {

            $.ajax({
                url: '<?= admin_url("admin-ajax.php") ?>',
                type: 'POST',
                data: {
                    action: 'get_customer_data',
                    customer_id: <?= $id ?>
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

                    getInvoices(<?= $id ?>).then(response => {
                        let amount = response.amount 

                        $('.easy-pos #amount').val(amount)
                        $('.easy-pos #my_account_amount').text(amount)
                        $('.easy-pos #balance_table').html(response.table)
                        calcDiscountAndFees()

                        $('.easy-pos #balance_table tbody tr.original-row').each(function(index) {
                            var creditCell = $(this).find('#credit' + index);
                            var debitCell = $(this).find('#debit' + index);
                            var creditValue = creditCell.text().trim();
                            var debitValue = debitCell.text().trim();

                            if (creditValue !== '$0.00') {
                                $(this).addClass('highlight-row-credit');
                            }
                            
                            if (debitValue !== '$0.00') {
                                $(this).addClass('highlight-row-debit');
                            }
                        });

                        $('#submit_payment').prop('disabled', false)
                        $('#amount').prop('disabled', false)
                        $('#submit_payment').toggleClass('disabled')
                        
                        if ($('.easy-pos #is_discount').is(':checked')) {
                            calcDiscountAndFees()
                        }
                    })

                }
            });

            async function getInvoices(customerId, orderId = '') {
                $('#submit_payment').prop('disabled', true)
                $('#amount').prop('disabled', true)
                $('#submit_payment').toggleClass('disabled')

                let response = await $.ajax({
                    url: '<?= admin_url("admin-ajax.php") ?>',
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
        });
    })(jQuery);
    </script>