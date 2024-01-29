
const protocol = window.location.protocol;
let stripe

if (protocol == 'https:') {
	stripe = Stripe('pk_live_51NDzTLGDW5CVzHx1YE4nGrDFoqHKCCAjIF4io37p7ExXVFcbxuaVRwi7RLQjU22y2N5hy1vSoLJ7i0YsWhAjjn9p007JNIX9rL');
} else if (protocol == 'http:') {
	stripe = Stripe('pk_test_51NDzTLGDW5CVzHx1w3A2N0TJEm5X3IFGEVJ2F7DaFBSUoe6oLKlTwS10EeF8f70rY3R3RuKT0GOeleGp73MBQCy500YFPD5dE2');
}

const elements = stripe.elements();
const style = {
base: {
	fontSize: '16px',
	color: '#32325d',
},
};
const card = elements.create('card', {style});

jQuery(document).ready(function($){

	//************* */ SAVE BILLING NOTE

	$('.customer-information-container #billing_note').on('focusout', function() {
		let input = $(this)
		let inputText = $(this).val()
		let userId = $('#billing_note_user').val()

		if (inputText !== '') {
			$.ajax({
				url: obj.ajaxurl,
				data: {
					action: 'save_billing_note',
					note: inputText,
					user_id: userId
				},
				success: function(response) {
					response = JSON.parse(response)
	
					if (response) {
						input.css('color', 'red')
					}
				}
			});
		}
	})

	$('.customer-information-container #billing_note').on('focusin', function() {
		$(this).css('color', 'black')
	})

	//********** */ ORDER NOTE ENROLLMENT TYPES 

	$('#order_note_type').on('change', function() {

		let text = $('#content_note').val()
		
		if ($(this).val() == 'enrollment') {
			$('#content_note').val('Enrolled in ...')
		} else if ($(this).val() == 'unenrollment') {
			$('#content_note').val('Leaving ...')
		} else {
			$('#content_note').val(text)
		}
	})
	//************** */ SET ATHLETE TAGS COUNT ON TAXONOMY PAGE

	if (window.location.href.includes("/edit-tags.php?taxonomy=athlete_tags")) {
		$.ajax({
			type: 'GET',
			url: obj.ajaxurl,
			data: {
				action: 'get_athlete_tags_count',
			},
			success: function(response) {
				response = JSON.parse(response)

				$('tbody#the-list tr').each(function(i, el) {
					let titleRow = $(el).find('.row-title')
					let title = titleRow.text().replace(/— /g, '');

					if (response[title]) {
						let count = $(el).find('.column-posts a')
						count.text(response[title].athlete_count)
						count.attr('href', '/wp-admin/admin.php?page=user-information-children&tag='+response[title].term_id)
					}
				})
			}
		});
	}

	$('#easy-pos-modal').on($.modal.BEFORE_CLOSE, function () { 
		tinymce.activeEditor.remove("textarea"); 
	});

	$('#easy-pos-modal').on($.modal.OPEN, function () { 
		tinymce.init({
			selector: "textarea",
			wpautop: true,
			plugins : ['directionality', 'fullscreen', 'hr', 'lists', 'media', 'paste', 'tabfocus'],
			toolbar1: 'bold italic underline strikethrough | bullist numlist | blockquote hr wp_more | alignleft aligncenter alignright | link unlink | fullscreen | wp_adv',
			toolbar2: 'formatselect alignjustify forecolor | pastetext removeformat charmap | outdent indent | undo redo | wp_help',
			quicktags: true,
			mediaButtons: true,
		});
	});

	let notifications = $('#staff_notifications .notification-status')
	if (notifications.length > 0) {
		updateNotificationStatus(notifications)
	}

	async function updateNotificationStatus(notifications) {
		let notif = []
		const promise = new Promise((resolve, reject) => {
			notifications.each(async function(i, el) {
				notif.push($(el).val())
			})

			resolve(notif)
		})

		await promise.then(function() {
			$.ajax({
				url: obj.ajaxurl,
				method: 'POST',
				data: {
					action: 'update_notifications_status',
					notifications:notif
				},
				success: function(response) {
					console.log(response);
				}
			});
		})
	}

	$('#balance_table tbody tr.original-row').each(function(index) {
		var creditCell = $(this).find('#credit' + index);
		var debitCell = $(this).find('#debit' + index);
		var creditValue = creditCell.text().trim();
		var debitValue = debitCell.text().trim();

		if (creditValue !== "$0.00") {
			$(this).addClass('highlight-row-credit');
		}
		
		if (debitValue !== "$0.00") {
			$(this).addClass('highlight-row-debit');
		}
	});

	//********** */ DISABLE ADD ORDER BUTTON ON SUBMISSION
	
	$('#pos-order-form').submit(function(e) {
		if ($('select#customer').length > 0 && $('select#customer').val() == '') {
			e.preventDefault();
			$('.empty-fields').text('Error: Please enter a customer')
			$('.empty-fields').show()
		} else {
			$('.save-order').addClass('hidden')
		}
	})

	//***************** */ RESEND INVOICE 

	$('#show_billing_history #billing_history tbody tr.original-row').each(function(index) {
		var creditCell = $(this).find('#credit' + index);
		var debitCell = $(this).find('#debit' + index);
		var creditValue = creditCell.text().trim();
		var debitValue = debitCell.text().trim();

		if (creditValue !== "$0.00") {
			$(this).addClass('highlight-row-credit');
		}
		
		if (debitValue !== "$0.00") {
			$(this).addClass('highlight-row-debit');
		}
	});

	$('.resend_invoice_item button').on('click', function() {
		console.log('object');
		const item = $(this).data('item')
		const rowId = $(this).data('row');

		$('.'+ rowId+' .resend_invoice_item button').attr('disabled', true)
		$('.'+ rowId+' .resend_invoice_item button').addClass('disabled')
		
		sendEmail(item, rowId);
	})

	function sendEmail(item, rowId) {
		$.ajax({
			url: obj.ajaxurl,
			method: 'POST',
			data: {
				action: 'send_invoice_email',
				item:item
			},
			success: function(response) {
				if (JSON.parse(response)) {
					$('.'+ rowId+' .resend_invoice_item .resend-invoice-success').removeClass('hidden')
					setTimeout(function() {
						$('.'+ rowId+' .resend_invoice_item .resend-invoice-success').addClass('hidden')
					}, 8000);
				} else {
					alert('SMTP Error. Please try again later.');
				}

			},
			error: function(error) {
				alert('Error sending mail', error);
			}
		});

		$('.'+ rowId+' .resend_invoice_item button').removeAttr('disabled')
		$('.'+ rowId+' .resend_invoice_item button').removeClass('disabled')
	}


	//************** */ SET ATHLETE TAGS COUNT ON TAXONOMY PAGE

	let currentUrl = window.location.href;
	
	if (currentUrl.includes("/edit-tags.php?taxonomy=athlete_tags")) {
		$.ajax({
			type: 'GET',
			url: obj.ajaxurl,
			data: {
				action: 'get_athlete_tags_count',
			},
			success: function(response) {
				response = JSON.parse(response)

				$('tbody#the-list tr').each(function(i, el) {
					let titleRow = $(el).find('.row-title')
					let title = titleRow.text().replace(/— /g, '');
					console.log(title);

					if (response[title]) {
						let count = $(el).find('.column-posts a')
						count.text(response[title].athlete_count)
						count.attr('href', '/wp-admin/admin.php?page=user-information-children&tag='+response[title].term_id)
					}
				})


				console.log(response);
			}
		});
	}

	$('#easy-pos-modal').on($.modal.BEFORE_CLOSE, function () { 
		tinymce.activeEditor.remove("textarea"); 
	});

	$('#easy-pos-modal').on($.modal.OPEN, function () { 
		tinymce.init({
			selector: "textarea",
			wpautop: true,
			plugins : ['directionality', 'fullscreen', 'hr', 'lists', 'media', 'paste', 'tabfocus'],
			toolbar1: 'bold italic underline strikethrough | bullist numlist | blockquote hr wp_more | alignleft aligncenter alignright | link unlink | fullscreen | wp_adv',
			toolbar2: 'formatselect alignjustify forecolor | pastetext removeformat charmap | outdent indent | undo redo | wp_help',
			quicktags: true,
			mediaButtons: true,
		});
	});


	//********** */ CREDIT CARD REPORTS FILTERS

	$('#cc_report #subaccount_filter').on('change', function() {
		let value = $(this).val()
		let page = $(this).data('page')

		window.location = '/wp-admin/admin.php?page='+page+'&sub='+value
	})

	//*************** */ MANUAL INVOICES SWITCH

	$('#month_switch').on('change', function() {
		let value = $(this).val()

		window.location.href = '/wp-admin/admin.php?page=manual_invoices&date='+value;
	})

	//************** */ EMAIL SCHEDULES EDIT

	$('#email_type').on('change', function() {
		if ($(this).val() == 'comma') {
			$('#comma_email').removeAttr('disabled')
		} else {
			$('#comma_email').val('')
			$('#comma_email').attr('disabled', true)
		}
	})

	$('#scheduled_emails_list .delete-btn').on('click', function() {
		let id = $(this).data('row')
		let subject = $('#'+id +' .subject').text()
		$('#confirm_delete_schedule .schedule-title span').text(subject)
		$('#confirm_delete_schedule #schedule_id').val(id)
	})

	$('#edit_schedule').on($.modal.BEFORE_CLOSE, function () { 
		tinymce.activeEditor.remove("textarea"); 
		$('tr').removeClass('active-email')
	});

	
	$('#edit_schedule').on($.modal.OPEN, function() {
		tinymce.init({
			selector: "textarea",
			wpautop: true,
			plugins : ['directionality', 'fullscreen', 'hr', 'lists', 'media', 'paste', 'tabfocus'],
			toolbar1: 'bold italic underline strikethrough | bullist numlist | blockquote hr wp_more | alignleft aligncenter alignright | link unlink | fullscreen | wp_adv',
			toolbar2: 'formatselect alignjustify forecolor | pastetext removeformat charmap | outdent indent | undo redo | wp_help',
			quicktags: true,
			mediaButtons: true,
			setup: function (editor) {
				editor.on('init', function (e) {
					let id = $('.active-email').attr('id')
					let message = $('#'+id +' .message-expanded').html()
					let subject = $('#'+id +' .subject').text()
					let day = $('#'+id +' .email_schedule').text()
					let group = $('#'+id +' .email_type').text()
					let commaEmail = $('#'+id +' .comma_email').text()
					let status = $('#'+id +' .schedule_status').text()
					let timestamp = $('#'+id +' .timestamp').text()
					
					editor.setContent(message);
					$('#edit_schedule #timestamp').val(timestamp)
					$('#edit_schedule .schedule-title span').text(subject)
					$('#edit_schedule #subject').val(subject)
					$('#edit_schedule #email_schedule').val(day)
					$('#edit_schedule #event_id').val(id)
			
					$('#edit_schedule #email_type option').each(function(i, el) {
						if ($(el).text() == group) {
							$(el).prop('selected', true)
						} else {
							$(el).removeAttr('selected')
						}
					})
			
					if (commaEmail !== '') {
						$('#edit_schedule #comma_email').removeAttr('disabled')
						$('#edit_schedule #comma_email').val(commaEmail)
					} else {
						$('#edit_schedule #comma_email').attr('disabled', true)
					}
			
					$('#edit_schedule #schedule_status option').each(function(i, el) {
						if ($(el).val() == status) {
							$(el).prop('selected', true)
						} else {
							$(el).removeAttr('selected')
						}
					})
				});
			}
		});

		
	})

	$('#scheduled_emails_list .edit-button').on('click', function() {
		let id = $(this).data('row')
		$('#'+id).addClass('active-email')
	})

	//*************** */ ATHLETE TAGS

	$('#filter_athlete_tags').on('change', function() {
		let val = $(this).val()
		if (val !== '') {
			window.location.href = '/wp-admin/admin.php?page=user-information-children&tag='+val
		}
	})

	$('#add_athlete_tag').on('keyup', function() {
		let word = $(this).val()

		$('#tags_dropdown li').each(function(i, el) {
			let name = $(el).text().toLowerCase()
			
			if (name.includes(word.toLowerCase())) {
				$('#tags_dropdown').show()
				$(el).show();
			} else {
				$(el).hide();
			}
		})
	})

	$('#tags_dropdown li').on('click', function() {
		let tagId = $(this).data('id')
		let tagName = $(this).text()
		let athleteId = $('#tag_user').val()


		$('#add_athlete_tag').val('')
		$('#add_athlete_tag').attr('disabled', true)

		$.ajax({
			type: 'GET',
			url: obj.ajaxurl,
			data: {
				action: 'save_tag_to_athlete',
				id: tagId,
				user: athleteId,
			},
			success: function(response) {
				console.log(response);
				response = JSON.parse(response)

				if (response) {
					let html = `<li class="flex-container" id="${response}"><a target="_blank" href="/wp-admin/admin.php?page=user-information-children&tag=${response}"><span>${tagName}</span></a><button type="button" data-id="${response}" class="delete-tag-item-icon"></button></li>`
					$('#athlete_tags .tags-container').append(html)
				}

				$('#add_athlete_tag').removeAttr('disabled')

			}
		});
	})

	$('body').on('click', '.delete-tag-item-icon', function() {
		let tagId = $(this).data('id')
		let athleteId = $('#tag_user').val()


		$('#'+tagId).remove()

		$.ajax({
			type: 'GET',
			url: obj.ajaxurl,
			data: {
				action: 'delete_athlete_tag',
				id: tagId,
				user: athleteId,
			}
		});
	})

	$('#add_new_tag').on('click', function() {
		let tagName = $('#add_athlete_tag').val()
		let athleteId = $('#tag_user').val()

		$('#add_athlete_tag').val('')
		$('#add_athlete_tag').attr('disabled', true)

		$.ajax({
			type: 'GET',
			url: obj.ajaxurl,
			data: {
				action: 'save_new_athlete_tag',
				name: tagName,
				user: athleteId,
			},
			success: function(response) {
				response = JSON.parse(response)

				if (response) {
					let html = `<li class="flex-container" id="${response}"><span>${tagName}</span><button type="button" data-id="${response}" class="delete-tag-item-icon"></button></li>`
					$('#athlete_tags .tags-container').append(html)
				}

				$('#add_athlete_tag').removeAttr('disabled')
			}
		});
	})
	
	//************ */ DELETE INVOICE

	$('#confirm_delete_invoice .confirm-delete').on('click', function(e) {
		e.preventDefault();
		let invoiceId = $('#invoice_id').val()

		if (invoiceId !== '') {
			$.ajax({
				type: 'GET',
				url: obj.ajaxurl,
				data: {
					action: 'delete_invoice',
					invoice_id: invoiceId,
				},
				success: function(response) {
					if (response) {
						$( '.modal' ).modal( 'hide' );
						$( '.blocker' ).hide();
						$( 'body' ).css('overflow', 'scroll');
						
						$('#invoice_'+invoiceId).remove()
					}
				}
			});
		}

	})

	$('.custom-modal .cancel-btn').on('click', function() {
		$( '.modal' ).modal( 'hide' );
		$( '.blocker' ).hide();
		$( 'body' ).css('overflow', 'scroll');
	})

	//********** */ REFUND OPTIONS PAYMENT REFUNDS PAGE

	$('#refund_options').on('click', function() {
		$('.refund-item').toggleClass('hidden')
		$('.cancel-btn').toggleClass('hidden')
		$('#refund_options').toggleClass('hidden')
		$('.payment-refunded').addClass('hidden')
		$('#full_refund').prop('checked', true)

		let method = $('#payment_method').val()

		if (method == 'cash' || 
			method == 'cheque' ||
			method == 'paypal') {
			$('.payment-electronic-processing').addClass('hidden')
		} else {
			$('.payment-electronic-processing').removeClass('hidden')
		}
	})

	$('#view_payment .cancel-btn').on('click', function() {
		$('.refund-item').addClass('hidden')
		$('.cancel-btn').addClass('hidden')
		$('.partial-refund-item').addClass('hidden')
		$('.not-partial-amount').removeClass('hidden')
		$('#refund_options').removeClass('hidden')
		$('[name="type_refund"]').prop('checked', false)


		if ($('#refunded_total').text() !== '') {
			$('.payment-refunded').removeClass('hidden')
		}
	})

	$('[name="type_refund"]').on('change', function() {
		if ($(this).attr('id') == 'partial_refund') {
			$('.partial-refund-item').removeAttr('disabled')
			$('.partial-refund-item').removeClass('hidden')
			$('.not-partial-amount').addClass('hidden')
		} else {
			$('.not-partial-amount').removeClass('hidden')
			$('.partial-refund-item').addClass('hidden')
			$('.partial-refund-item').attr('disabled', true)
			$('.partial-refund-item').prop('checked', false)
		}
	})

	$('#view_payment #refund_amount').on('focusout', function() {
		$('#result_refund_amount').text($(this).val())
	})

	$('body').on('focusout', '#view_payment .partial-refund-item-amount', function() {
		getRefundTotal()
	})

	$('body').on('change', '#view_payment .partial-refund-item-check', function() {
		let id = $(this).data('id')
		let itemAmount = $(id+' .partial-refund-item-amount')
		if ($(this).is(':checked')) {
			let refundedItem = $(id+' .refunded-item-total')
			let itemTotal = $(id+' .item-total')
			if (refundedItem.length > 0) {
				itemAmount.val(refundedItem.data('amount'))
			} else {
				itemAmount.val(itemTotal.data('amount'))
			}
		} else {
			itemAmount.val(0)
		}

		getRefundTotal()
	})

	$('#electronic_processing').on('change', function() {
		if ($(this).is(':checked')) {
			$('.payment-electronic-processing .disclaimer').addClass('hidden')
		} else {
			$('.payment-electronic-processing .disclaimer').removeClass('hidden')
		}
	})

	function getRefundTotal() {
		let total = 0

		$('#view_payment .partial-refund-item-amount').each(function(i, el) {
			total += parseFloat($(el).val())
			$('#result_refund_amount').text(total)
		})
	}

	$('#payments_list .edit-btn').on('click', function() {
		$('#view_payment .absolute').toggleClass('hidden')
		$('#view_payment .payment-refunded').addClass('hidden')
		$('.payment-refunded').addClass('hidden')
		$('#refund_options').addClass('hidden')
		$('.refund-item').addClass('hidden')
		$('.cancel-btn').addClass('hidden')
		$('#electronic_processing').prop('checked', false)
		$('.payment-electronic-processing').addClass('hidden')
		$('.payment-electronic-processing .disclaimer').removeClass('hidden')
		
		let id = $(this).data('id')

		$.ajax({
			type: 'GET',
			url: obj.ajaxurl,
			data: {
				action: 'get_order_details',
				id: id,
			},
			success: function(response) {
				// console.log(response);
				response = JSON.parse(response)
				$('#view_payment .absolute').toggleClass('hidden')
				$('#order_title_id').text(id)
				$('#payment_source').text(response.user)
				$('#payment_date').text(response.date)
				$('#payment_date').text(response.date)
				$('#pay_method_name').text(response.payment_method_formatted)
				$('#pay_method_id').text(response.payment_method_id)
				$('#payment_amount').text(response.amount)
				$('#description').html(response.description)
				$('#result_refund_amount').text(response.amount)
				$('#customer_name').text(response.customer_name)
				$('#customer_name').attr('href', '/wp-admin/admin.php?page=user-information-edit&user='+response.customer_id+'&child=no')
				$('#refunded_total').html(response.refunded_total_formatted)
				$('.refunded-total-item').html(response.refunded_total_formatted)
				$('#full_refund').val(response.original_amount)
				$('#payment_method').val(response.payment_method)
				$('#parent_order_id').val(response.parent_order)
				$('#payment_order_id').val(response.payment_order)
				$('#refund_options').removeClass('hidden')

				if (response.refunded_total !== '' &&
					parseFloat(response.refunded_total) >= parseFloat(response.original_amount)) {
						$('#refund_options').addClass('hidden')
				}

				if (response.refunded_total_formatted !== '') {
					$('#net_payment').html(response.net_payment_formatted)
					$('.net-total-item').html(response.net_payment_formatted)
					$('#full_refund').val(response.net_payment)
					$('.payment-refunded').removeClass('hidden')
				} else {
					$('.payment-refunded').addClass('hidden')
					$('.refunded-total-item').text('$0.00')
					$('.net-total-item').text('$'+response.original_amount)
				}

				if (response.payment_method_id !== '') {
					$('.pay-method-id').removeClass('hidden')
				} else {
					$('.pay-method-id').addClass('hidden')
				}

				if (response.payment_intent !== '') {
					$('#transaction_id').attr('href', 'https://dashboard.stripe.com/payments/'+response.payment_intent)
					$('#transaction_id').text(response.payment_intent)
				} else {
					$('#transaction_id').removeAttr('href')
					$('#transaction_id').text('-')
				}

				if (response.amount_extra !== '') {
					$('.transaction_fee').text(response.amount_extra)
					$('.transaction_fee').val(response.amount_extra)
				} else {
					$('.transaction_fee').text('-')
				}


				if (response.order_items !== '') {
					$('.payment-details-items').removeClass('hidden')
					$('#order_items').html(response.order_items)
					$('.partial-refund').removeClass('hidden')
				} else {
					$('.payment-details-items').addClass('hidden')
				}
			}
		});
	})

	//*********** */ CONFIRM PAYMENT METHOD DELETE
	$('.payment-methods-list .remove_card').on('click', function() {
		let id = $(this).val()
		$('#remove_card').val(id)
	})


	//************ */ SETTINGS PAGE

	let settingsPricing = $('.gycrm-admin-settings #settings_pricing')
	let settingsTasks = $('.gycrm-admin-settings #settings_tasks')
	let settingsRoles = $('.gycrm-admin-settings #settings_roles')
	let settingsNotes = $('.gycrm-admin-settings #settings_notes')
	
	settingsTasks.detach()
	settingsRoles.detach()
	settingsNotes.detach()

	let settingsSection = {'settingsPricing': settingsPricing, 'settingsTasks': settingsTasks, 'settingsRoles': settingsRoles, 'settingsNotes': settingsNotes}

	$('.gycrm-admin-settings .tab').on('click', function() {
		let id = $(this).data('id')
		$('.gycrm-admin-settings .settings-section').detach()
		$('.gycrm-admin-settings .main-section').append(settingsSection[id])
	})

	//************ */ CACHE LINK PROBLEM
	$.each($('main a'), function(i, el) {
		if ($(el).text() == 'Lost your password?') {
			$(el).attr('href', '/lost')
		}
	})
	
	$.each($('.elementor-location-header a .elementor-button-text'), function(i, el) {
		if ($(el).text() == 'New Members') {
			$(el).parent().parent().attr('href', '/membership/')
		}
	})
	

	//*********** */ change email receptor
	$('.single_user').on('change', function () {
		let target = $(this).data('id')
		$('#'+target+' #to').val(this.value);
	})

	$('#container_select').hide()
	$('#programs_classes').on('change', function() {
		$('#tab1 .program_user_select').hide()
	})
	$('#tab1 .account_owing_select').hide()
	$('#tab2 .account_owing_select').hide()
	$('#tab1 .no_credit_select').hide()
	$('#tab2 .no_credit_select').hide()

	$('.email_type').on('change', function() {
		
		let target = $(this).data('id')

		$('#'+target+" #to").val("")
		
		if(this.value=="single"){
			$('#'+target+" .single_select").show()
			$('#'+target+" .program_select").hide()
			$('#'+target+' .comma_select').hide()
			$('#'+target+' .program_user_select').hide()
			$('#'+target+' .account_owing_select').hide()
			$('#'+target+' .no_credit_select').hide()
			$('#'+target+' .users-by-tags').hide()
			$('#'+target+' .user_select_tags').hide()
		} else if (this.value == "program"){
			$('#'+target+" .single_select").hide()
			$('#'+target+" .program_select").show()
			$('#'+target+' .comma_select').hide()
			$('#'+target+' .program_user_select').hide()
			$('#'+target+' .account_owing_select').hide()
			$('#'+target+' .no_credit_select').hide()
			$('#'+target+' .users-by-tags').hide()
			$('#'+target+' .user_select_tags').hide()
		}else if (this.value == "comma"){
			$('#'+target+" .single_select").hide()
			$('#'+target+" .program_select").hide()
			$('#'+target+' .comma_select').show()
			$('#'+target+' .program_user_select').hide()
			$('#'+target+' .account_owing_select').hide()
			$('#'+target+' .no_credit_select').hide()
			$('#'+target+' .users-by-tags').hide()
			$('#'+target+' .user_select_tags').hide()
		}else if (this.value == "accounts-owing"){
			$('#'+target+" .single_select").hide()
			$('#'+target+" .program_select").hide()
			$('#'+target+' .comma_select').hide()
			$('#'+target+' .program_user_select').hide()
			$('#'+target+' .account_owing_select').show()
			$('#'+target+' .no_credit_select').hide()
			$('#'+target+' .users-by-tags').hide()
			$('#'+target+' .user_select_tags').hide()
		}else if (this.value == "no-credit"){
			$('#'+target+" .single_select").hide()
			$('#'+target+" .program_select").hide()
			$('#'+target+' .comma_select').hide()
			$('#'+target+' .program_user_select').hide()
			$('#'+target+' .account_owing_select').hide()
			$('#'+target+' .no_credit_select').show()
			$('#'+target+' .users-by-tags').hide()
			$('#'+target+' .user_select_tags').hide()
		}else if (this.value == "tag"){
			$('#'+target+" .single_select").hide()
			$('#'+target+" .program_select").hide()
			$('#'+target+' .comma_select').hide()
			$('#'+target+' .program_user_select').hide()
			$('#'+target+' .account_owing_select').hide()
			$('#'+target+' .no_credit_select').hide()
			$('#'+target+' .users-by-tags').show()
		}else{
			$('#'+target+" .single_select").hide()
			$('#'+target+" .program_select").hide()
			$('#'+target+' .comma_select').hide()
			$('#'+target+' .program_user_select').hide()
			$('#'+target+' .account_owing_select').hide()
			$('#'+target+' .no_credit_select').hide()
			$('#'+target+' .users-by-tags').hide()
			$('#'+target+' .user_select_tags').hide()
		}
	})

	 // Get the value of 'user_id' from the URL
	 const user_id = getUrlParameter('user_id');
	 // Check whether 'user_id' is present and set default values
	 if (user_id) {
		 $('#tab1 .email_type').val('single').trigger('change');
		 $('#tab1 .single_select .single_user').val(user_id);
		 const userEmail = $('#tab1 .single_select .single_user option[id="' + user_id + '"]').text().split(' - ')[1];
		 $('#tab1 .single_select .single_user').val(userEmail);
	 }
 
	 // Function to obtain URL parameters
	 function getUrlParameter(name) {
		 name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
		 const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
		 const results = regex.exec(location.search);
		 return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
	 }

	//************* */ PAYMENT PLANS

	$('#payment_plan').on('change', function() {
		let date = new Date()
		let month = date.getMonth() + 1

		if (month < 10) {
			month = '0'+month
		}
		let today = date.getFullYear() + '-' + month + '-' + date.getDate();

		if ($(this).is(':checked')) {
			$('#due_date').val(today)
		} else {
			$('#due_date').val('')
		}

		$('.payment-plan-due-container').toggleClass('hidden')
	})

	$('#payment_plan_months').on('keyup', function() {
		let months = $(this).val()
		let total = $('#order-total').text()

		if (months !== '' && total !== '') {
			let plan = total / months

			$('#total_monthly').show()
			$('#total_monthly .total-monthly').text(plan.toFixed(2))
		} else {
			$('#total_monthly').hide()
		}
	})

	//********** */  ACCOUNTS OWING FILTER

	$('#accounts_owing #card_filter').on('change', function() {
		let card = $(this).val()

		if (card !== '') {
			window.location = '/wp-admin/admin.php?page=pos_owes_list&card='+card
		}
	})

	//************ */ FILTERS IN CUSTOMER ACTIONS PAGE
	let actionCustomers = $('.pos-page tbody tr')

	$('#customers_action_required #account_type').on('change', function() {
		let accountType = $(this).val()

		window.location = '/wp-admin/admin.php?page=user-information-actions&t='+accountType
	})

	$('#customers_action_required .action-filter').on('change', function() {
		const urlSearchParams = new URLSearchParams(window.location.search);
		const accountType = urlSearchParams.get('t');
		
		let actionName = $(this).val()
		if (actionName !== 'All') {
			if (accountType) {
				window.location = '/wp-admin/admin.php?page=user-information-actions&t='+accountType+'&action='+actionName
			} else {
				window.location = '/wp-admin/admin.php?page=user-information-actions&action='+actionName
			}
		} else {
			if (accountType) {
				window.location = '/wp-admin/admin.php?page=user-information-actions'+'&t='+accountType
			} else {
				window.location = '/wp-admin/admin.php?page=user-information-actions'
			}

		}
	})


	$('.pos-page #search_account').on('keyup', function() {

		let search = $(this).val()
		
		$.each(actionCustomers, function() {

			let rows = $(this).children()
			rows = rows.text().toLowerCase()

			if (rows.includes(search.toLowerCase()) && rows !== 'no items') {
				$(this).removeClass('hidden');
				$(this).addClass('row-show');
			} else {
				$(this).removeClass('row-show');
				$(this).addClass('hidden');
			}
		})
	})

	//************* */ DROPDOWN WITH SEARCH

	create_custom_dropdowns($('.easy-pos-admin #customer'))
	
	if ($('#admin_create_child #add_athlete').length > 0) {
		create_custom_dropdowns($('#admin_create_child #add_athlete'))
	}

	function create_custom_dropdowns(el) {
		el.each(function (i, select) {
			if (!$(this).next().hasClass('dropdown-select')) {
				$(this).after('<div class="dropdown-select wide ' + ($(this).attr('class') || '') + '" tabindex="0"><span class="current"></span><div class="list"><ul></ul></div></div>');
				var dropdown = $(this).next();
				var options = $(select).find('option');
				var selected = $(this).find('option:selected');
				dropdown.find('.current').html(selected.data('display-text') || selected.text());
				options.each(function (j, o) {
					var display = $(o).data('display-text') || '';
					dropdown.find('ul').append('<li class="option ' + ($(o).is(':selected') ? 'selected' : '') + '" data-children="'+ $(o).data('children') +'" data-value="' + $(o).val() + '" data-display-text="' + display + '">' + $(o).text() + '</li>');
				});
			}
		});
	
		$('.dropdown-select ul').before('<div class="dd-search"><input id="txtSearchValue" autocomplete="off" class="dd-searchbox" type="text"></div>');
	}
	
	// Event listeners
	
	// Open/close
	$(document).on('click', '.dropdown-select', function (event) {
		if($(event.target).hasClass('dd-searchbox')){
			return;
		}
		$('.dropdown-select').not($(this)).removeClass('open');
		$(this).toggleClass('open');
		if ($(this).hasClass('open')) {
			$(this).find('.option').attr('tabindex', 0);
			$(this).find('.selected').focus();
		} else {
			$(this).find('.option').removeAttr('tabindex');
			$(this).focus();
		}
	});
	
	// Close when clicking outside
	$(document).on('click', function (event) {
		if ($(event.target).closest('.dropdown-select').length === 0) {
			$('.dropdown-select').removeClass('open');
			$('.dropdown-select .option').removeAttr('tabindex');
		}
		event.stopPropagation();
	});

	$('body').on('keyup', '.dd-search', function() {
		var valThis = $('#txtSearchValue').val();
		$('.dropdown-select ul > li').each(function(){
		 var child = $(this).data('children');
		 var text = $(this).text();
			(text.toLowerCase().indexOf(valThis.toLowerCase()) > -1) || (child.toLowerCase().indexOf(valThis.toLowerCase()) > -1) ? $(this).show() : $(this).hide();         
	   });
	})

	
	// Option click
	$(document).on('click', '.dropdown-select .option', function (event) {
		$(this).closest('.list').find('.selected').removeClass('selected');
		$(this).addClass('selected');
		var text = $(this).data('display-text') || $(this).text();
		$(this).closest('.dropdown-select').find('.current').text(text);
		$(this).closest('.dropdown-select').prev('select').val($(this).data('value')).trigger('change');
	});
	
	// Keyboard events
	$(document).on('keydown', '.dropdown-select', function (event) {
		var focused_option = $($(this).find('.list .option:focus')[0] || $(this).find('.list .option.selected')[0]);
		// Space or Enter
		//if (event.keyCode == 32 || event.keyCode == 13) {
		if (event.keyCode == 13) {
			if ($(this).hasClass('open')) {
				focused_option.trigger('click');
			} else {
				$(this).trigger('click');
			}
			return false;
			// Down
		} else if (event.keyCode == 40) {
			if (!$(this).hasClass('open')) {
				$(this).trigger('click');
			} else {
				focused_option.next().focus();
			}
			return false;
			// Up
		} else if (event.keyCode == 38) {
			if (!$(this).hasClass('open')) {
				$(this).trigger('click');
			} else {
				var focused_option = $($(this).find('.list .option:focus')[0] || $(this).find('.list .option.selected')[0]);
				focused_option.prev().focus();
			}
			return false;
			// Esc
		} else if (event.keyCode == 27) {
			if ($(this).hasClass('open')) {
				$(this).trigger('click');
			}
			return false;
		}
	});

	//*********** */ ATHLETE DETAILS CUSTOMER INFORMATION

	$('.athlete-accounts .tab').on('click', function() {
		$('.athlete-details').slideUp()
		$('.athlete-details-container .absolute').show()

		let userId = $(this).data('user')

		$.ajax({
			type: 'GET',
			url: obj.ajaxurl,
			data: {
				action: 'get_athlete_details',
				user_id: userId,
			},
			success: function(response) {
				// console.log(response);
				response = JSON.parse(response)

				$('#athlete_first_name').text(response.first_name)
				$('#athlete_last_name').text(response.last_name)
				$('#athlete_status').text(response.status)
				$('#athlete_age').text(response.age)

				if (response.start_date == '') {
					$('#athlete_start_date').text('-')
				} else {
					$('#athlete_start_date').text(response.start_date)
				}

				if (response.reg_date == '') {
					$('#athlete_annual_reg').text('-')
				} else {
					$('#athlete_annual_reg').text(response.reg_date)
				}

				$('.athlete-details .user-actions').html(response.action_required)
				$('#enrolled_classes').html(response.enrolled_classes)
				$('#attendance_history').html(response.attendance)
				$('.athlete-details .submit_user_info').attr('href', '/wp-admin/admin.php?page=user-information-edit&user='+userId+'&child=yes')

				$('.athlete-details-container .absolute').hide()
				$('.athlete-details').slideDown()

			}
		});
	})

	//***************** */ DELETE CUSTOMER ACTIONS

	$('body').on('click', '.delete-action-item-icon', function() {
		let userId = $(this).data('user')
		let action = $(this).data('id')

		$(this).attr('disabled', true)
		$(this).addClass('disabled')

		$.ajax({
			type: 'GET',
			url: obj.ajaxurl,
			data: {
				action: 'delete_user_action',
				user_id: userId,
				user_action: action,
			},
			success: function(response) {
				response = JSON.parse(response)
				
				if (response) {
					$('#action_'+action).remove()
				}

				$(this).removeAttr('disabled')
				$(this).removeClass('disabled')
			}
		});
	})
	
	
	//*************** */ SAVE USER CUSTOMER INFORMATION ACTIONS

	$('.save-customer-actions').on('click', function() {
		let userId = $(this).data('user')
		let action = $('#action_required').val()
		let name = $('#action_name').val()

		$('.customer-actions-container .global-success').hide();
		$('.save-customer-actions').attr('disabled', true)
		$('.save-customer-actions').addClass('disabled')

		if (action !== '') {
			$.ajax({
				type: 'GET',
				url: obj.ajaxurl,
				data: {
					action: 'save_user_actions',
					user_id: userId,
					user_action: action,
					action_name: name,
				},
				success: function(response) {
					response = JSON.parse(response)
					if (response !== '') {
						
						$('.customer-actions-container .global-success').show()
						
						let html = `<li class="flex-container"><div>"${action}"`
						
						if (name !== '') {
							html += ` — ${name}`
						}

						html += `</div><button type="button" data-id="${response.key}" data-user="${response.user}" class="delete-action-item-icon"></button></li>`
						$('.actions-list').append(html)
					}
					$('.save-customer-actions').removeAttr('disabled')
					$('.save-customer-actions').removeClass('disabled')
				}
			});
		}

	})

	//************ */ ATTENDANCE/PROGRAM_STATUS DROPDOWN

	$('.dropdown-toggle').click(function() {
		$(this).next('.dropdown').slideToggle();
	});

	$(document).click(function(e) { 
		let target = e.target; 

		if (!$(target).is('.dropdown-toggle') && !$(target).parents().is('.dropdown-toggle')) { 
			$('.dropdown').slideUp();
		}
	});

	//************ */ PARENT FORM PAYMENT METHODS

	$('.customer-payment-methods .payment-methods-header .add-card-payment-method').click(function() {
		$('.customer-payment-methods .add-payment-method-container').removeClass('hidden')
	});


	//************* */ IMPORT USER PROGRESS BAR
	
	let progressBar = $('#import_users_container #progressBar');
	progressBar.val(0);

	$('#import_users_container form').on('submit', function() {
		progressBar.css('display', 'block')

		let percentComplete = 0;
		let interval = setInterval(function() {
			percentComplete++;
			progressBar.val(percentComplete);
			if (percentComplete === 100) {
				clearInterval(interval);
			}
		}, 100);
	})


	jQuery(".answer").hide();
	jQuery("#chkProdTomove").click(function() {
		if(jQuery(this).is(":checked")) {
			jQuery(".answer").show();
		} else {
			jQuery(".answer").hide();
		}
	});

	//************** */ FETCH EMAILS SUBJECT AND BODY
	
	$('.email_template').on('change', function() {
		let templateId = $(this).val()
		let target = $(this).data('id')
	
		if (templateId === '') {
			return;
		}

		// Fetch email template content using REST API
		fetch(`/wp-json/wp/v2/email_template/${templateId}`)
			.then(response => response.json())
			.then(data => {
				const title = data.title.rendered;
				const content = data.content.rendered;

				$('#'+target+' #email_subject').val(title)
				
				if (target == 'tab1') {
					tinyMCE.get('email_content').setContent(content);
				} else {
					tinyMCE.get('email_content_schedule').setContent(content);
				}
			});
	})


	//***************** */ MY-ACCOUNT SHOP SETTINGS

	let woocommerceMyAccount =  $('.edit-container .woocommerce-MyAccount-navigation ul').children();

	$.each(woocommerceMyAccount, function(i, el) {
		if (el.classList.contains('woocommerce-MyAccount-navigation-link--dashboard') || 
			el.classList.contains('woocommerce-MyAccount-navigation-link--edit-address') || 
			el.classList.contains('woocommerce-MyAccount-navigation-link--multiaccounts') || 
			el.classList.contains('woocommerce-MyAccount-navigation-link--edit-account')) {
				el.remove()
		}
	})

	//*************** */ CREATE CHILD PROGRAM MULTISELECT

	$('body').on('click', '#enrolled_classes .delete-class-item-icon', function() {
		console.log('object');
		let classId = $(this).data('class')
		let slotId = $(this).data('slot')

		if ($(this).data('type') == 'no-auto') {
			$('#enrolled_classes button[data-class="'+classId+'"][data-slot="'+slotId+'"]').parent().remove()
		}
	})

	$('.class-slot-filter #submit_classes_slots').on('click', function() {
		
		$('.enrolled-classes .global-error').addClass('hidden')
		$('.enrolled-classes .global-success').addClass('hidden')

		let classId = $('#class-filter-dropdown').val()
		let slotId = $('#slot-filter-dropdown').val()

		if (classId !== '' && slotId !== '') {
			let selectedPrograms = $('#selected_programs').val()
			let selectedSlots = $('#selected_slots').val()
	
			let uniquePrograms
			let uniqueSlots
	
			if (selectedPrograms !== '' && selectedSlots !== '') {
				selectedPrograms = selectedPrograms.split(',')
				selectedPrograms.push(classId)
				uniquePrograms = selectedPrograms.filter((value, index, array) => array.indexOf(value) === index).join(',');
		
				selectedSlots = selectedSlots.split(',')
				selectedSlots.push(slotId)
				uniqueSlots = selectedSlots.filter((value, index, array) => array.indexOf(value) === index).join(',');
			} else {
				uniquePrograms = classId
				uniqueSlots = slotId
			}
	
			$('#selected_programs').val(uniquePrograms)
			$('#selected_slots').val(uniqueSlots)
	
			let athleteId = $('#athlete_enroll_id').val()

			let isClass = false
			$.each($('#enrolled_classes button'), function(i, el) {
				if ($(el).data('class') == classId && $(el).data('slot') == slotId) {
					isClass = true
				}
			})

			if (!isClass) {
				if ($(this).data('type') !== 'no-auto') {
					enrollAthlete(uniquePrograms, uniqueSlots, athleteId, classId, slotId, 'enroll')
				} else {
					let title = $('#class-filter-dropdown option[value="'+classId+'"]').first().text()
					let slot = $('#slot-filter-dropdown option[value="'+slotId+'"]').first().data('number')
					let html = `<li>${title} ${slot} <button type="button" data-class="${classId}" data-slot="${slotId}" data-type="no-auto" class="delete-class-item-icon"></button></li>`
					$('#enrolled_classes').append(html)
				}
			}
	
		} else {
			$('.enrolled-classes .global-error').text('Error: Please select a class and a slot.')
			$('.enrolled-classes .global-error').removeClass('hidden')
		}

	})

	function unenrollAthlete(classId, slotId) {
		if (classId !== '' && slotId !== '') {
			let selectedPrograms = $('#selected_programs').val().split(',')
			let selectedSlots = $('#selected_slots').val().split(',')

			if ($('#enrolled_classes button[data-class="'+classId+'"]').length == 1) {
				selectedPrograms = $.grep(selectedPrograms, function(value) {
					return value != classId;
				}).join(',');
			} else {
				selectedPrograms = selectedPrograms.join(',')
			}
	
			selectedSlots = $.grep(selectedSlots, function(value) {
				return value != slotId;
			}).join(',');
	
			$('#selected_programs').val(selectedPrograms)
			$('#selected_slots').val(selectedSlots)
	
			let athleteId = $('#athlete_enroll_id').val()
			enrollAthlete(selectedPrograms, selectedSlots, athleteId, classId, slotId, 'unenroll')
		}
	}

	$('#confirm_delete_class #confirm_delete_class_btn').on('click', function() {
		let classId = $('#confirm_delete_class #delete_class_id').val()
		let slotId = $('#confirm_delete_class #delete_slot_id').val()
		$('#confirm_delete_class').hide()
        $('body').css('overflow', 'auto')

		unenrollAthlete(classId, slotId)
	})

	function enrollAthlete(uniquePrograms, uniqueSlots, athleteId, classId, slotId, type) {
		$.ajax({
			url: obj.ajaxurl,
			data: {
				action: 'enroll_athlete',
				athleteId: athleteId,
				programs: uniquePrograms,
				slots: uniqueSlots,
			},
			success: function(response) {
				response = JSON.parse(response)

				if (type == 'enroll') {
					if (response) {
						let title = $('#class-filter-dropdown option[value="'+classId+'"]').first().text()
						let slot = $('#slot-filter-dropdown option[value="'+slotId+'"]').first().data('number')
						let html = `<li>${title} ${slot} <button type="button" data-class="${classId}" data-slot="${slotId}" data-modal="#confirm_delete_class" class="edit-btn delete-class-item-icon"></button></li>`
						$('#enrolled_classes').append(html)
						$('.enrolled-classes .global-success').text('Enrolled to class successfully.')
						$('.enrolled-classes .global-success').removeClass('hidden')
					}
				} else {
					$('#enrolled_classes button[data-class="'+classId+'"][data-slot="'+slotId+'"]').parent().remove()
					$('.enrolled-classes .global-success').text('Unenrolled from class successfully.')
					$('.enrolled-classes .global-success').removeClass('hidden')
				}
			}, 
			error: function(error) {
				$('.enrolled-classes .global-error').text('Unknown Error: Please try again later.')
				$('.enrolled-classes .global-error').removeClass('hidden')
			}
		});
	}


	//************* */ STAFF SCRIPTS
		// Get the current page URL
		var currentPageURL = window.location.href;
		var entryTitleElement = $('.entry-header .entry-title');
	
		// Check if the current URL ends with "/my-account/"
		if (currentPageURL.endsWith('/my-account/')) {
			// Replace the content in .woocommerce-MyAccount-content with your new content
			jQuery('.woocommerce-MyAccount-content').html('<p>Welcome to your account!</p><p>Here you can see your past invoices, update your payment method, and more!</p><p>If you have any questions, please <a href="/contact/">contact us</a> or <a href="tel:+17173780101">give us a call</a>.</p>');
		
			// Change the text of the .entry-title element
			entryTitleElement.text('My Account');
		}
	
		if (window.location.href.includes('/my-account/view-subscription/')) {
			// Change the text of the .entry-title element
			entryTitleElement.text('Subscription Details');
		}
		
		if (window.location.href.includes('/my-account/orders/')) {
			// Change the text of the .entry-title element
			entryTitleElement.text('Order History');
		}
		
		// Check if the current page URL includes "/my-account/downloads/"
		if (window.location.href.includes('/my-account/downloads/')) {
			// Change the text of the .entry-title element
			entryTitleElement.text('Training Sheets');
			
			// Find the .woocommerce-Message element
			var woocommerceMessageElement = $('.woocommerce-Message');
	
			// Replace the content in .woocommerce-Message
			woocommerceMessageElement.html('<p>Training Sheets & Gymnastics Pages will be made available in the future.</p>');
		}
		
		if (window.location.href.includes('/my-account/payment-methods/')) {
			// Change the text of the .entry-title element
			entryTitleElement.text('Payment Methods');
		}
		
		if (window.location.href.includes('/my-account/?purchase-lists')) {
			// Change the text of the .entry-title element
			entryTitleElement.text('Shopping Lists');
			
			$('.woocommerce-MyAccount-content').css({'width': '100%','padding-left': '0'});
		}
		
		// Find the anchor element inside .woocommerce-MyAccount-navigation-link--orders
		var ordersAnchor = $('.woocommerce-MyAccount-navigation-link--orders a');
	
		// Change the text of the anchor
		ordersAnchor.text('Billing History');
		
		var paymentMethodsAnchor = $('.woocommerce-MyAccount-navigation-link--payment-methods a');
		
		// Change the text of the anchor
		paymentMethodsAnchor.text('Payment Methods');
		
		// Find the .entry-title element inside .entry-header
		var entryTitleElement = $('.entry-header .entry-title');


		//************************ */ SET CREDIT CARD FEE
		
		$( "#payment_method_stripe" ).on( "click", function() {
			$(document.body).trigger("update_checkout");
		});

		//************ */ Stripe elements

		let pmForm = $('#add-pm-form')

		if ($('.easy-pos .add_card').length > 0) {
			if ($('button[data-modal="#easy-pos-modal"]').length > 0) {
				card.mount('.add-payment-method-container .add_card');
			} else {
				card.mount('.easy-pos .add_card');
			}
			
			$('button[data-modal="#easy-pos-modal"]').on('click', function() {
				card.unmount('.add-payment-method-container .add_card');
				card.mount('.easy-pos .add_card');
			})
			
			$('#easy-pos-modal').on($.modal.BEFORE_CLOSE, function() {
				card.unmount('.easy-pos .add_card');
				$('.add-payment-method-container').append(pmForm);
				card.mount('.add-payment-method-container .add_card');
			});
	
			$('#easy-pos-modal').on($.modal.BEFORE_OPEN, function() {
				pmForm.detach()
			});
		}

		//************* */ CATEGORY EDITING PAGE

		$('.update-category .edit-cat').on('click', function() {
			let id = $(this).data('id')

			$('.cat_'+id+ '.not-editable').hide()
			$('.cat_'+id+ '.editable').show()
		})

		$('.update-category .cancel-btn').on('click', function() {
			let id = $(this).data('id')

			$('.cat_'+id+ '.not-editable').show()
			$('.cat_'+id+ '.editable').hide()
		})

		$('.update-category .delete-cat').on('click', function() {
			let id = $(this).data('id')
			let name = $(this).data('catname')

			$('#confirm_delete_category #cat_id').val(id)
			$('#confirm_delete_category #cat_name').text(name)
		})

		$('#confirm_delete_category .confirm-delete').on('click', function(e) {
			let catId = $('#cat_id').val()
	
			if (catId !== '') {
				$.ajax({
					type: 'GET',
					url: obj.ajaxurl,
					data: {
						action: 'delete_category',
						cat_id: catId,
					},
					success: function(response) {
						$( '.modal' ).modal( 'hide' );
						$( '.blocker' ).hide();
						$( 'body' ).css('overflow', 'scroll');

						if (JSON.parse(response)) {
							$('.cat_'+catId).remove()
						} else {
							alert('Unknown Error')
						}
					}
				});
			}
	
		})

		$('.update-category .save-cat').on('click', function(e) {
			let id = $(this).data('id')
			let description = $('.cat_'+id+' #cat_descr_'+id).val()
			let parent = $('.cat_'+id+' #cat_parent_'+id).val()
			let catName = $('.cat_'+id+' #cat_name_'+id).val()
	
			$.ajax({
				type: 'GET',
				url: obj.ajaxurl,
				data: {
					action: 'update_category',
					cat_id: id,
					cat_name: catName,
					cat_parent: parent,
					cat_descr: description,
				},
				success: function(response) {
					response = JSON.parse(response)
					if (!response) {
						alert('Unknown Error')
					} else {
						$('.cat_'+id+ '.not-editable .cat-name').text(response.name)
						$('.cat_'+id+ '.not-editable .cat-descr').text(response.descr)
						$('.cat_'+id+ '.not-editable .cat-parent').text(response.parent)

						$('.cat_'+id+ '.not-editable').show()
						$('.cat_'+id+ '.editable').hide()
					}
				}
			});
	
		})



		//********** */ REGISTRATION INTERNATIONAL PHONE CODES

		$(".int-phone").intlTelInput({
			//hiddenInput: "full_number",
			initialCountry: "auto",
			geoIpLookup: callback => {
				fetch("https://ipapi.co/json")
				  .then(res => res.json())
				  .then(data => callback(data.country_code))
				  .catch(() => callback("us"));
			  },
			separateDialCode: true,
			//autoPlaceholder: "off",
		});
		
		$('.int-phone').on('countrychange', function (e) {
		
			$(this).val('');
		
			var selectedCountry = $(this).intlTelInput('getSelectedCountryData');
			var dialCode = selectedCountry.dialCode;
			var maskNumber = intlTelInputUtils.getExampleNumber(selectedCountry.iso2, 0, 0);
			maskNumber = intlTelInputUtils.formatNumber(maskNumber, selectedCountry.iso2, 2);
			maskNumber = maskNumber.replace('+' + dialCode + ' ', '');
			mask = maskNumber.replace(/[0-9+]/ig, '0');
		
			$('#phone').mask(mask, { placeholder: maskNumber });
		});

	});


	// Manage the tabs for send email page
	function openTabs(evt, tabName) {
		var i, tabcontent, tablinks;
		tabcontent = document.querySelectorAll(".tab-content");
		tabcontent.forEach(element => {
			element.style.display = "none";
		});
		tablinks = document.getElementsByClassName("tab");
		for (i = 0; i < tablinks.length; i++) {
			tablinks[i].className = tablinks[i].className.replace(" active", "");
		}
		document.getElementById(tabName).style.display = "block";
		evt.currentTarget.className += " active";
	}
	jQuery(document).ready(function(){

		jQuery(".answer").hide();
		jQuery("#chkProdTomove").click(function() {
			if(jQuery(this).is(":checked")) {
				jQuery(".answer").show();
			} else {
				jQuery(".answer").hide();
			}
		});
		});

	// Merge Tags Manual Emails
	function insertMergeTag() {
		var mergeTag = document.getElementById('merge_tags').value;
		if (mergeTag) {
			var editor = tinyMCE.get('email_content');
			if (editor) {
				editor.focus();
				editor.selection.setContent(mergeTag);
			} else {
				document.getElementById('email_content').value += mergeTag;
			}
		}
	}

	// Merge Tags Schedule Emails
	function insertMergeScheduleTag() {
		var mergeTag = document.getElementById('merge_tags_schedule').value;
		if (mergeTag) {
			var editor = tinyMCE.get('email_content_schedule');
			if (editor) {
				editor.focus();
				editor.selection.setContent(mergeTag);
			} else {
				document.getElementById('email_content_schedule').value += mergeTag;
			}
		}
	}

	// Don't show shipping Local pickup
	jQuery(document).ready(function($) {
		// Get the complete row
		var shippingRow = $('.woocommerce-shipping-totals.shipping');
		
		// Check if the row contains "Local pickup".
		if (shippingRow.text().includes('Local pickup')) {
			// console.log(shippingRow[0]);
			// Hide entire row
			shippingRow.hide();
		}
	});

	// Add new parent modal
	jQuery(document).ready(function($) {
		$('#add-parent-open-modal').on('click', function() {
			$('#add-parent-modal').fadeIn();
		});
	
		$('#add-parent-close-modal').on('click', function() {
			$('#add-parent-modal').fadeOut();
		});
	
		$('#save-user-button').on('click', function() {
		
			var firstName = $('#first_name').val();
			var lastName = $('#last_name').val();
			var email = $('#email').val();
			var userName = $('#username').val();
			var password = $('#password').val();
			// Realizar una solicitud AJAX para crear un nuevo usuario
			$.ajax({
				type: 'POST',
				url: obj.ajaxurl,
				data: {
					action: 'create_new_parent',
					firstName:firstName,
					lastName:lastName,
					email:email,
					userName:userName,
					password:password
				},
				success: function(response) {
					const newUserId = JSON.parse(response).newUserId;
					if (typeof newUserId !== 'undefined') {
						const url = `/wp-admin/admin.php?page=user-information-edit&user=${newUserId}&child=no`;
						window.location.href = url;
					} else {
						const errorMsj = JSON.parse(response).error_message;
						if (errorMsj) {
							alert('Error creating the user: ' + errorMsj);
						} else {
							alert('There was an error creating the user. Please try again.');	
						}
					}
				}
			});
		});
	});
	
	//Edit Table billing history function
	jQuery(document).ready(function($) {
		$('body').on('click', '.edit-button', function() {
			var rowClass = $(this).data('row')
			var table = $(this).data('table')
			$(table+ ' .original-row.' + rowClass).hide(); 
			$(table+ ' .editable-row.' + rowClass).show(); 
		});
	
		$('body').on('click', '.cancel-button', function() {
			var rowClass = $(this).closest('tr.editable-row').data('row'); // Gets the class of the editable row
			var table = $(this).closest('tr.editable-row').data('table'); // Gets the class of the editable row
			$(table+ ' .editable-row.' + rowClass).hide(); 
			$(table+ ' .original-row.' + rowClass).show();
		});

		$('body').on('click', '.delete-btn', function() {
			const confirmed = confirm("Are you sure you want to remove this item?");
				
			if (confirmed) {
					var rowClass = $(this).data('row')
					var table = $(this).data('table')
					const row = $(this).closest("tr");
					var idRow = rowClass.split('-')[1]
					var itemId = $(table+ ' .item-'+idRow).text().split('#')[1]
					var item = $(table+ ' .item-'+idRow).text().split('#')[0]
					var rowId = $(this).data('item');
					var itemType = item.replace(/\s+/g, "");

					jQuery.ajax({
						type: 'POST',
						url: obj.ajaxurl, 
						data: {
							action: 'delete_item', 
							itemId : itemId,
							itemType : itemType,
							rowId : rowId,
						},
						success: function(response) {
							message = JSON.parse(response).response;
							const resp = confirm(message);
							if (resp){
								row.remove();
								location.reload();
							}
						}, 
						error: function(jqXHR, textStatus, errorThrown) {
							var errorMessage = jqXHR.responseJSON ? jqXHR.responseJSON.response : 'Unknown error';
							alert('Failed: ' + errorMessage);
						}
					});
			}
		})

		$('body').on('click', '.save-button', function() {
			var rowClass = $(this).closest('tr.editable-row').data('row');
			var table = $(this).closest('tr.editable-row').data('table');
			var idRow = rowClass.split('-')[1]
			var editedCredit = $(table+ ' .editable-row.' + rowClass + ' .edit-credit').val();
			var editedDebit = $(table+ ' .editable-row.' + rowClass + ' .edit-debit').val();
			var editedDescription = $(table+ ' .editable-row.' + rowClass + ' .edit-description').val();
			var editedDate = $(table+ ' .editable-row.' + rowClass + ' .edit-date').val();
			// Extract the order number of the element "Order #9328"

			var fullItem = $(table+ ' #item'+idRow).text();
			var orderNumber = $(table+ ' #item'+idRow).text().split('#')[1];
			
			// Obtain the original values
			var originalDescription = $(table+ ' #description'+idRow).text();
			var originalCredit = parseFloat($(table+ ' #credit'+idRow).text().replace("$", ""));
			var originalDebit = parseFloat($(table+ ' #debit'+idRow).text().replace("$", ""));
			// Perform AJAX call to update the order
			$.ajax({
				type: 'POST',
				url: obj.ajaxurl, 
				data: {
					action: 'update_order', 
					orderNumber: orderNumber,
					fullItem:fullItem,
					rowClass: rowClass,
					description: editedDescription,
					credit: editedCredit,
					debit: editedDebit,
					date: editedDate,
					originalDescription: originalDescription,
					originalCredit: originalCredit,
					originalDebit: originalDebit,
				},
				success: function(response) {
					location.reload();
				}
			});
		}); 


		//*********** */ REMOVE ACH MY ACCOUNT

		$('body').on('click', '.delete-ach', function() {
			let id = $(this).data('id')
			
			$('.woocommerce-MyAccount-content #ach_warning').hide()
			if (!$(this).attr('disabled')) {
				$(this).attr('disabled', true)
				$.ajax({
					url: obj.ajaxurl,
					data: {
						action: 'remove_ach_payment_method',
						id: id 
					},
					success: function(response) {
						window.location.reload()
					},
					error: function(jqXHR, textStatus, errorThrown) {
						let errorMessage = jqXHR.responseJSON ? jqXHR.responseJSON.message : 'Unknown Error. Please try again later';
						$('.woocommerce-MyAccount-content #ach_warning').text(errorMessage)
						$('.woocommerce-MyAccount-content #ach_warning').show()
						$(this).removeAttr('disabled')
					}
				});
			}
			
		});
	});


	//Email reset password button
	jQuery(document).ready(function($) {
		// Capture the click event on the button
		$('#reset-password-btn').on('click', function() {
			var userId = $(this).data('user');	
			// Make an AJAX request
			$.ajax({
				url: obj.ajaxurl, // Replace this with the correct route
				type: 'POST',
				data: {
					action: 'reset_password_button',
					userId:userId // Name of the action on the backend
				},
				success: function(response) {
					message = JSON.parse(response).message;	
					// console.log(message);	
					// Handle the response if necessary
					alert('Success: ' + message);
				},
				error: function(jqXHR, textStatus, errorThrown) {
					var errorMessage = jqXHR.responseJSON ? jqXHR.responseJSON.message : 'Unknown error';
					alert('Failed: ' + errorMessage);
				}
			});
		});
	});

	document.addEventListener('DOMContentLoaded', function() {
		const slotSelect = document.getElementById('slot-filter-select');

		if (slotSelect) {
			slotSelect.addEventListener('change', function() {
				const selectedSlot = slotSelect.value;
				const currentUrl = window.location.href;
		
				const newUrl = updateQueryStringParameter(currentUrl, 'slot', selectedSlot);
				window.location.href = newUrl;
			});
		}
	
	
		// Función para actualizar el parámetro en la URL
		function updateQueryStringParameter(uri, key, value) {
			const re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
			const separator = uri.indexOf('?') !== -1 ? "&" : "?";
			if (uri.match(re)) {
				return uri.replace(re, '$1' + key + "=" + value + '$2');
			} else {
				return uri + separator + key + "=" + value;
			}
		}

		// Stripe paymenth methods
		const form = document.querySelectorAll('.stripe-form');

		form.forEach(el => {
			el.addEventListener('submit', async (event) => {
				let cardExists = document.querySelector('#card_exists').checked
				let customerPm = document.querySelector('#add-pm-form')
				let paymentMethod = document.querySelector('#payment_method').value
				let achExists = document.querySelector('#ach_exists').checked
				let btn = document.querySelector('.easy-pos #submit_payment')
				let loader = document.querySelector('.easy-pos .absolute')

				if (customerPm) {
					cardExists = false
				}

				if (!cardExists && paymentMethod == 'card') {
					event.preventDefault();
					
					const {token, error} = await stripe.createToken(card);
					
					if (error) {
						const errorElement = document.getElementById('card_errors');
						errorElement.classList.remove('hidden')
						errorElement.textContent = error.message;
						loader.classList.add('hidden');
					} else {
						let targ = event.target.id
						loader.classList.remove('hidden');
						loader.style.display = 'block';
						stripeTokenHandler(token, targ);
					}
				} else {
					btn.setAttribute('disabled', true);
					btn.classList.add('disabled');
					loader.classList.remove('hidden');
					loader.style.display = 'block';
					el.submit()
				}
				
			});
		})

		if (document.querySelector('#add_ach_pos')) {
			document.querySelector('#add_ach_pos').addEventListener('click', function() {
				let btn = document.querySelector('.easy-pos #submit_payment')
				let loader = document.querySelector('.easy-pos .absolute')

				btn.setAttribute('disabled', true);
				btn.classList.add('disabled');
				loader.classList.add('hidden');

				let customer = document.querySelector('.easy-pos #customer').value
				let warning = document.querySelector('.easy-pos #ach-error-message')
				
				warning.classList.add('hidden')

				if (customer !== 'no_account' && customer !== '') {
					let setupId = document.querySelector('.easy-pos #setup_id')
					let setupPm = document.querySelector('.easy-pos #setup_pm')
					createAchMethod(btn, customer, warning, setupId, setupPm, null, loader)
				} else {
					warning.classList.remove('hidden')

					if (customer == 'no_account') {
						warning.textContent = 'Please enter a customer.'
					}
					warning.scrollIntoView({ behavior: 'smooth' });

					loader.classList.remove('hidden');
					loader.style.display = 'block';
					btn.removeAttribute('disabled');
					btn.classList.remove('disabled');
				}
			})
		}


		if (document.querySelector('.customer-payment-methods .add-ach-payment-method')) {
			document.querySelector('.customer-payment-methods .add-ach-payment-method').addEventListener('click', function() {
				let btn = document.querySelector('.customer-payment-methods .payment-methods-header .add-ach-payment-method')
				let customer = document.querySelector('.easy-pos #customer').value
				let warning = document.querySelector('.add-payment-method-container .global-error')
				let setupId = document.querySelector('.add-payment-method-container #setup_cm_id')
				let setupPm = document.querySelector('.add-payment-method-container #setup_cm_pm')
				let el = document.getElementById('add-pm-form')
	
				btn.setAttribute('disabled', true);
				btn.classList.add('disabled');
				warning.classList.add('hidden')
				
				createAchMethod(btn, customer, warning, setupId, setupPm, el)
			});
		}


		function createAchMethod(btn, customer, warning, setupId, setupPm, el = null, loader = null) {
			const xhr = new XMLHttpRequest();
			xhr.open('GET', "/wp-admin/admin-ajax.php?action=create_ach_setup_intent&customer="+customer, true);
			xhr.send();
			xhr.onload = function() {
				if (xhr.status === 200) {
					let response = JSON.parse(xhr.responseText)

					if (response.client_secret) {
						stripe.collectBankAccountForSetup({
							clientSecret: response.client_secret,
							params: {
								payment_method_type: 'us_bank_account',
								payment_method_data: {
										billing_details: {
										name: response.billing_details.name,
										email: response.billing_details.email,
									},
									customer: response.stripe_cus_id,
								},
							},
							expand: ['payment_method'],
						})
						.then(({setupIntent, error}) => {
							if (error) {
								warning.classList.remove('hidden')
								warning.textContent = error
								btn.removeAttribute('disabled');
								btn.classList.remove('disabled');
								if(loader) {
									loader.classList.add('hidden');
								}

							} 
							
							if (setupIntent.status === 'requires_confirmation') {
								setupId.value = setupIntent.id
								setupPm.value = setupIntent.payment_method.id

								if (el) {
									el.submit();
								} else {
									btn.removeAttribute('disabled')
									btn.classList.remove('disabled')
									if(loader) {
										loader.classList.add('hidden');
									}
									document.querySelector('#add_ach_pos').remove()
									document.querySelector('.easy-pos .disclaimer-sm').classList.remove('hidden')
								}
							}
						});
					} else {
						warning.classList.remove('hidden')
						warning.textContent = response[0]
						btn.removeAttribute('disabled');
						btn.classList.remove('disabled');
						if(loader) {
							loader.classList.add('hidden');
						}
					}

				} else {
					warning.classList.remove('hidden')
					warning.textContent = 'Unknown Error. Please try again later.'
					btn.removeAttribute('disabled');
					btn.classList.remove('disabled');
					if(loader) {
						loader.classList.add('hidden');
					}
				}
			}
		}


		const stripeTokenHandler = (token, targ) => {
			const form = document.getElementById(targ);
			const hiddenInput = document.createElement('input');
			hiddenInput.setAttribute('type', 'hidden');
			hiddenInput.setAttribute('name', 'stripeToken');
			hiddenInput.setAttribute('value', token.id);
			form.appendChild(hiddenInput);
			form.submit();

			let btn =  document.querySelector('.easy-pos #submit_payment')
			btn.setAttribute('disabled', true);
			btn.classList.add('disabled');
			
		}
	});


	document.addEventListener('DOMContentLoaded', function() {
		var firstNameInput = document.getElementById('first_name');
		var lastNameInput = document.getElementById('last_name');
		var usernameInput = document.getElementById('username');

		if (firstNameInput && lastNameInput) {
			firstNameInput.addEventListener('input', generateUsername);
			lastNameInput.addEventListener('input', generateUsername);
		}
	
	
		function generateUsername() {
			var firstName = firstNameInput.value.trim();
			var lastName = lastNameInput.value.trim();
	
			// Get the current time (hours and minutes)
			var now = new Date();
			var hours = now.getHours().toString().padStart(2, '0'); // Ensure 2-digit format
			var minutes = now.getMinutes().toString().padStart(2, '0'); // Ensure 2-digit format
	
			// Create the username by combining first name, last name, and current time
			var generatedUsername = (firstName + '.' + lastName + '.' + hours + minutes).toLowerCase();
	
			usernameInput.value = generatedUsername;
		}
		//***************** */ ACH PAYMENT METHODS
	
		let currentUrl = window.location.href;
	
		if (currentUrl.includes("/my-account/payment-methods/")) {
	
			let container = document.querySelector('.woocommerce-MyAccount-content')
			let customer = document.querySelector('.account-user #user_id').dataset.id
	
			let html = '<form action="" method="POST" id="ach_my_account_container">'
			html += '<button type="submit" id="ach_my_account">Connect Bank Account</button>'
			html += '<input type="hidden" name="my_account_setup_id" id="my_account_setup_id"/>'
			html += '<input type="hidden" name="my_account_setup_pm" id="my_account_setup_pm"/>'
			html += '</form>'
			html += '<div class="notice notice-warning is-dismissible hidden" id="ach_warning"></div>'
	
			container.insertAdjacentHTML('beforeend', html)
			const xhr = new XMLHttpRequest();

			xhr.open('GET', "/wp-admin/admin-ajax.php?action=get_ach_payment_methods&customer="+customer, true);
			xhr.send();
			xhr.onload = function() {
				if (xhr.status === 200) {
					let response = JSON.parse(xhr.responseText)

					let html = ''

					let table = document.querySelector('.woocommerce-MyAccount-paymentMethods')
					let tableContainer = document.querySelector('.woocommerce-MyAccount-content')

					if (table) {
						if (response !== '') {
							html += response
							table.insertAdjacentHTML('beforeend', html)
						}
					} else {
						if (response !== '') {
							html += '<table class="woocommerce-MyAccount-paymentMethods shop_table shop_table_responsive account-payment-methods-table">'
							html += '<thead>'
							html += '<tr>'
							html += '<th class="woocommerce-PaymentMethod woocommerce-PaymentMethod--method payment-method-method"><span class="nobr">Method</span></th>'
							html += '<th class="woocommerce-PaymentMethod woocommerce-PaymentMethod--expires payment-method-expires"><span class="nobr">Expires</span></th>'
							html += '<th class="woocommerce-PaymentMethod woocommerce-PaymentMethod--actions payment-method-actions"><span class="nobr">&nbsp;</span></th>'
							html += '</tr>'
							html += '</thead>'
							html += '<tbody>'
							html += response
							html += '</tbody>'
							html += '</table>'

							document.querySelector('.woocommerce-info').remove()

							tableContainer.insertAdjacentHTML('afterbegin', html)
						}
					}	
				}
			}
		}

		if (document.querySelector('#ach_my_account')) {
			document.querySelector('#ach_my_account').addEventListener('click', function(e) {
				e.preventDefault();
		
				const xhr = new XMLHttpRequest();
				let customer = document.querySelector('.account-user #user_id').dataset.id
				let btn = document.querySelector('#ach_my_account')
				let warning = document.querySelector('.woocommerce-MyAccount-content #ach_warning')
		
				btn.setAttribute('disabled', true);
				btn.classList.add('disabled');
				warning.classList.add('hidden')
		
				xhr.open('GET', "/wp-admin/admin-ajax.php?action=create_ach_setup_intent&customer="+customer, true);
				xhr.send();
				xhr.onload = function() {
		
					if (xhr.status === 200) {
						let response = JSON.parse(xhr.responseText)
		
						if (response.client_secret) {
							stripe.collectBankAccountForSetup({
								clientSecret: response.client_secret,
								params: {
									payment_method_type: 'us_bank_account',
									payment_method_data: {
											billing_details: {
											name: response.billing_details.name,
											email: response.billing_details.email,
										},
										customer: response.stripe_cus_id,
									},
								},
								expand: ['payment_method'],
							})
							.then(({setupIntent, error}) => {
								if (error) {
									warning.classList.remove('hidden')
									warning.textContent = error
									btn.removeAttribute('disabled');
									btn.classList.remove('disabled');
								} 
								
								if (setupIntent.status === 'requires_confirmation') {
									document.querySelector('.woocommerce-MyAccount-content #my_account_setup_id').value = setupIntent.id
									document.querySelector('.woocommerce-MyAccount-content #my_account_setup_pm').value = setupIntent.payment_method.id
		
									document.querySelector('#ach_my_account_container').submit();
								}
							});
						} else {
							warning.classList.remove('hidden')
							warning.textContent = response[0]
							btn.removeAttribute('disabled');
							btn.classList.remove('disabled');
						}
		
					} else {
						warning.classList.remove('hidden')
						warning.textContent = 'Unknown Error. Please try again later.'
						btn.removeAttribute('disabled');
						btn.classList.remove('disabled');
					}
				};
			})
		}
	
	});

	

	// Hover function to open Modal

	jQuery(document).ready(function() {
		jQuery("tr.original-row").each(function() {
			let id = jQuery(this).data('table')
			let index = jQuery(this).data('key')
			var item = jQuery(id+' .item-' + index);
			var desc = jQuery(id+' .description-' + index);

			var invo = item.text();
			var payment = desc.text();
			var isHovered = false; // Variable para rastrear si se está dentro del modal

			if (invo.startsWith("Invoice")) {
				jQuery(id+' .'+item[0].className).hover(function() {
					let id = jQuery(this).data('table')
					jQuery(this).addClass('item_class_modal');
					jQuery(id+ " #hover-"+index).show();
				}, function() {
					isHovered = false;
					let id = jQuery(this).data('table')
					setTimeout(function() {
						if (!isHovered) {
							jQuery(id+ " #hover-"+index).hide();
						}
					}, 300);
				});
				
				jQuery(id+ " #hover-"+index).hover(function() {
					// console.log('object');
					isHovered = true;
				}, function() {
					isHovered = false;
					setTimeout(function() {
						if (!isHovered) {
							jQuery(id+ " #hover-"+index).hide();
						}
					}, 300);
				});
			}

			jQuery(id+ ' .'+desc[0].className).hover(function() {
				jQuery(this).addClass('item_class_modal');
				let id = jQuery(this).data('table')
				jQuery(id+ " #payment-hover-"+index).show();
			}, function() {
				isHovered = false;
				let id = jQuery(this).data('table')
				setTimeout(function() {
					if (!isHovered) {
						jQuery(id+ " #payment-hover-"+index).hide();
					}
				}, 300);
			});
			
			jQuery("#payment-hover-"+index).hover(function() {
				isHovered = true;
			}, function() {
				let id = jQuery(this).data('table')
				isHovered = false;
				setTimeout(function() {
					if (!isHovered) {
						jQuery(id+ " #payment-hover-"+index).hide();
					}
				}, 300);
			});
		});
	});


	// Pagination
	jQuery(document).ready(function () {
		jQuery('.next-page, .last-page, .first-page, .prev-page').on('click', function (e) {
			e.preventDefault();
			var searchInputValue = jQuery('#search_id-search-input').val();
			var currentHref = jQuery(this).attr('href');
			
			//Gets the current value of the 'search' parameter of the URL
			var urlSearchParams = new URLSearchParams(currentHref);
			var searchParam = urlSearchParams.get('search');
	
			// Checks if the search field is empty and there is no 'search' parameter in the URL
			if (searchInputValue === '' && searchParam === null) {
				window.location.href = currentHref;
			} else {
				if (searchInputValue === '') {
					if (searchParam !== null) {
						// Construct the new URL with the existing value of 'search'.
						var newHref = currentHref;
					}
				} else {
					// Constructs the new URL with the value of 'search' from input
					var newHref = currentHref + '&search=' + searchInputValue;
				}
	
				// Update the href attribute of the link with the new GET value
				jQuery(this).attr('href', newHref);
	
				// Redirects to the new URL
				window.location.href = newHref;
			}
		});
	});

	jQuery(document).ready(function () {
        jQuery('#custom-search-input').on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var searchInputValue = jQuery(this).val();
				var currentUrl = location.origin + '/wp-admin/admin.php?page=user-information';
                var newUrl = addOrUpdateQueryParam(currentUrl, 'search', searchInputValue);
                window.location.href = newUrl;
            }
        });

        // Function to add or update a parameter in the URL
        function addOrUpdateQueryParam(url, key, value) {
            var urlObj = new URL(url);
            urlObj.searchParams.set(key, value);
            return urlObj.toString();
        }
    });
