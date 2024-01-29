<?php
add_action( 'wp_footer', 'ajax_registration' );
function ajax_registration() { ?>
    <script type="text/javascript" >

    jQuery(document).ready(function($) {

	const elements = stripe.elements();
	const style = {
			base: {
				fontSize: '16px',
				color: '#32325d',
			},
	};

	let	card = elements.create('card', {style});

	const url = new URL(window.location.href);
	const params = url.searchParams;

	if (params.get('retry')) {
		renderCard()
	}

	function getAthleteFields(athletes, input, id, enrolled) {
		input.each(function(ind, ele) {
			let name = $(ele).data('name')

			if (name) {
				if (!athletes[id]) {
					athletes[id] = {}
				}

				athletes[id][name] = {};
				if (name === 'enrolled') {
					if ($(ele).is(':checked')) {
						enrolled.push($(ele).val())
					}

					athletes[id][name] = enrolled
					
				} else {
					athletes[id][name] = $(ele).val();
				}

			}
		});

		return athletes
	}

	function renderCard() {
		$('.submit-btn').attr('disabled', true)
		$('.submit-btn').addClass('disabled')

		$.ajax({
			type: 'GET',
			url: obj.ajaxurl,
			data: {
				action: 'render_payment_section'
			},
			success: function(response) {
				response = JSON.parse(response)
				$('#card_placement').html(response)
				card.mount('#membership_form .add_card');
				$('.submit-btn').removeAttr('disabled')
				$('.submit-btn').removeClass('disabled')
			}
		});
	}

	//*************** */ CHANGE REGISTRATION FORM CHECK ICONS

	$('body').on('change', '.check-btn', function() {
		let check = $(this).data('id')
		let val = $(this).val()
		let athlete = $(this).data('athlete')
		let programsSelected = false;

		if ($(this).is(':checked')) {
			$(check+'_checked').show()
			$(check+'_unchecked').hide()
			
			$(this).attr('disabled', true)
			$(check+'_loader').show()

			let id = $(this).attr('id')

			getSelectedEnrollment(athlete)
			
			$.ajax({
				type: 'GET',
				url: obj.ajaxurl,
				data: {
					action: 'render_enrollment_inputs',
					value: val,
					athlete: athlete,
				},
				success: function(response) {
					// console.log(response);
					response = JSON.parse(response)

					$('#'+id).removeAttr('disabled')
					$(check+'_loader').hide()

					if (response) {
						$(check+'_container').html(response)
					} else {
						$(check+'_full').show()

						setTimeout(function() {
							$(check+'_full').hide()
						}, 5000);

						$('#'+id).removeAttr('checked')
						$(check+'_checked').hide()
						$(check+'_unchecked').show()
						getSelectedEnrollment(athlete)
					}
					
				}
			});

			if (val == 'classes') {
				let paymentSection = $('#card_placement div').length

				if (!paymentSection) {
					renderCard()
				}
			}
			
		} else {
			$(check+'_checked').hide()
			$(check+'_unchecked').show()
			$(check+'_container div').remove()

			getSelectedEnrollment(athlete)
		}

		
		$('#membership_form .programs-checked').each(function(i, el) {
			if ($(el).is(':checked')) {
				programsSelected = true
			}
		})

		if (!programsSelected) {
			$('#card_placement div').remove()
		}
	})

	function getSelectedEnrollment(athlete) {
		let selected = []

		$('#athlete_'+athlete+' .registration_athlete_enroll input').each(function(i, el) {
			if ($(el).is(':checked')) {
				let val = $(el).val()

				if ($(el).val() == 'lessons') {
					val = 'Private '+val
				}

				selected.push(val)
			}
			
		})

		if (selected.length) {
			let message = selected.join(', ')
			$('#athlete_'+athlete+'_enrollment_warning span').text(message)
			$('#athlete_'+athlete+'_enrollment_warning .enrollment-warning').show()
		} else {
			$('#athlete_'+athlete+'_enrollment_warning .enrollment-warning').hide()
		}
	}

        $(document).on('click', '.edit-form .submit-btn', function(e) {
			$('.notice').hide()
			e.preventDefault();

			let targetForm = $(this).data('form')

			myFuncs[targetForm](targetForm);
		})

		$('#referral_customer_name').on('keyup', function() {
			let word = $(this).val()
			let wordLength = word.length

			if (wordLength >= 4) {
				$('#referral_customers_list').show()

				$('#referral_customers_list li').each(function(i, el) {
					let name = $(el).text().toLowerCase()
	
					if (name.includes(word.toLowerCase())) {
						$(el).show();
					} else {
						$(el).hide();
					}
				})
				
			} else {
				$('#referral_customers_list').hide();
			}

		})

		$('#referral_customers_list li').on('click', function() {
			$('#referral_customer_name').val($(this).text())
			$('#referral_customer_id').val($(this).data('id'))
			$('#referral_customers_list').hide()
		})

		$('#referral_type').on('change', function() {
			if ($(this).val() == 'Another Customer') {
				$('.referral-customers-container').show()
			} else {
				$('.referral-customers-container').hide()
			}
		})

		responsiveSlots()

		$(window).resize(function() {
			responsiveSlots()
		});
		function responsiveSlots() {
			if ($(window).width() < 900) {
				$('.slots-container .remove-slot').each(function(i, el) {
					let th = $(el).parent().parent().parent().find('th').eq($(el).index());
					th.hide();
					$(el).hide();
				})
			} else {
				$('.slots-container .remove-slot').show()
				$('.slots-container th').show()
			}
		}

		$('body').on('click', '.programs-container', function() {
			let id = $(this).data('slots')
			$('.panel').hide()
			$(id).slideDown()

			$('html, body').animate({
				scrollTop: $(id).offset().top
			}, 500);
		})

		$('body').on('click', '.slots-container .btn', function() {
			$(this).toggleClass('selected')

			addToField($(this).data('athlete'))
		})

			
		function addToField(id) {
			let programs = $('#athlete_'+id+' .slots-container .btn.selected')
			
			let selected_programs = []
			let selected_slots = []

			$.each(programs, function(i, el) {
				selected_programs.push($(el).data('program'))
				selected_slots.push($(el).data('slot'))
			})

			let uniquePrograms = selected_programs.filter((value, index, array) => {
				return array.indexOf(value) === index;
			}).join(',');

			let uniqueSlots = selected_slots.filter((value, index, array) => {
				return array.indexOf(value) === index;
			}).join(',');

			$('#classes_'+id).val(uniquePrograms)
			$('#slots_'+id).val(uniqueSlots)

		}

		const stripeTokenHandler = (token) => {
			const form = $('#membership_form');
			const hiddenInput = document.createElement('input');
			hiddenInput.setAttribute('type', 'hidden');
			hiddenInput.setAttribute('name', 'stripeToken');
			hiddenInput.setAttribute('value', token.id);
			form.append(hiddenInput);
			form.submit();
		}

		$('#membership_form .submit-btn').on('click', async (e) => {
			e.preventDefault();
			$('.notice-warning').addClass('hidden')

			let btn =  $('#membership_form .submit-btn')
			btn.attr('disabled', true);
			btn.addClass('disabled');

			let loader =  $('#membership_form .absolute')
			loader.removeClass('hidden');

			let requiredFields = ['first_name', 'last_name', 'email', 'password', 'child_first_name', 'child_last_name', 'gender', 'child_birth', 'slots', 'stripeToken', 'start_date', 'billing_address_1', 'billing_phone', 'billing_state', 'billng_city', 'billing_postcode', 'enrolled'];
			
			let fields = {}
			let athletes = {}
			let isInadmissible = false

			const promise = new Promise((resolve, reject) => {
			$('#membership_form input, #membership_form select').each(async function(i, el){ 
			if ($(el).attr('name')) {
				if (!$(el).attr('name').includes('athletes')) {

				if ($(el).hasClass('int-phone')) {

					var number = $(el).intlTelInput('getNumber');
					iso = $(el).intlTelInput('getSelectedCountryData').iso2;
					let id = $(el).attr('id')

					if (number !== '') {
					let isValid = validatePhoneNumber(number, iso, id)

					if (isValid == 0) {
						fields[$(el).attr('name')] = number
					} else {
						isInadmissible = true
						$('.notice-warning[data-id="'+id+'"]').text(isValid)
						$('.notice-warning[data-id="'+id+'"]').removeClass('hidden')
						$('.notice-warning[data-id="'+id+'"]').show()

						let warnings = $('.notice-warning').filter(':not(.hidden)').first();

						if (warnings.length) {
							$('html, body').animate({
								scrollTop: warnings.offset().top
							}, 500);
						}
					
					}
					}


				} else {
					fields[$(el).attr('name')] = $(el).val()
					}
				}
			}
			});

			$('#membership_form .athlete-section').children().each(function(i, el){ 
				let enrolled = []
				let id = $(el).attr('id');
				let input = $('#'+id+' *[name^="athletes"]')

				athletes = getAthleteFields(athletes, input, id, enrolled)
			});

			fields['athletes'] = athletes

			resolve(fields)
		});

		await promise.then(function() {
			$.ajax({
			url : <?php echo '"'.admin_url( 'admin-ajax.php' ).'"'; ?>,
			data : {
					action: "validate_membership_form",
					fields: JSON.stringify(fields),
					requiredFields: requiredFields,
				},
			success: async function(response) {
				// console.log(response);
				response = JSON.parse(response)

				if (response.alerts) {
					$.each(response.alerts, function(i, el) {
						if (i == 'athletes') {
							$.each(el, function(id, ath) {
								$.each(ath, function(item, val) {
									$('#'+id+' *[data-name="'+item+'"] + .notice-warning').removeClass('hidden')
									$('#'+id+' *[data-name="'+item+'"] + .notice-warning').show()
								})
							})
						} else {
							$('*[name="'+i+'"] + .notice-warning').removeClass('hidden')
							$('*[name="'+i+'"] + .notice-warning').show()
						}

					})

					let warnings = $('.notice-warning').filter(':not(.hidden)').first();
					
					if (warnings.length) {
						$('html, body').animate({
							scrollTop: warnings.offset().top
						}, 500);
					}

					btn.removeAttr('disabled');
					btn.removeClass('disabled');
					loader.addClass('hidden');

				} else if (!isInadmissible) {
					programsSelected = false

					$('#membership_form .programs-checked').each(function(i, el) {
						if ($(el).is(':checked')) {
							programsSelected = true
						}
					})

					if (programsSelected) {
						let card = elements.getElement('card');
						const {token, error} = await stripe.createToken(card)
						if (error) {
							const errorElement = $('#card_errors');
							errorElement.removeClass('hidden')
							errorElement.show()
							errorElement.text(error.message);
	
							btn.removeAttr('disabled');
							btn.removeClass('disabled');
							loader.addClass('hidden');
						} else {
							stripeTokenHandler(token);
						}
					} else {
						let form = $('#membership_form');
						form.submit();
					}

				}


			}
		});
	})

	})

	$('input').on('keydown', function() {
		$('#'+$(this).attr('id')+' + .notice-warning').hide()
	})

	$('select').on('change', function() {
		$('#'+$(this).attr('id')+' + .notice-warning').hide()
	})

	$('#email').on('keydown', function() {
		$('.email-exists').hide()
	})

	$('#email').on('focusout', function() {

		let email = $(this).val()

		$.ajax({
			url : <?php echo '"'.admin_url( 'admin-ajax.php' ).'"'; ?>,
			data : {
					action: "validate_email_exists",
					email: email,
				},
			success: function(response) {
				response = JSON.parse(response)

				if (response == 0) {
					$('.email-exists').show()
				}

			}
		});

	})

	$('.int-phone').on('keydown', function() {
		$('.notice-warning[data-id="'+$(this).attr('id')+'"]').hide()
	})

	$('.int-phone').on('focusout', function() {
		var number = $(this).intlTelInput('getNumber');
		iso = $(this).intlTelInput('getSelectedCountryData').iso2;

		let id = $(this).attr('id')

		validatePhoneNumber(number, iso, id)

	})

	function validatePhoneNumber(number, iso, id) {
		var exampleNumber = intlTelInputUtils.getExampleNumber(iso, 0, 0);

		var validationError = intlTelInputUtils.getValidationError(number, iso);

		const validationErrors = {
			1: 'Invalid country code',
			2: 'Phone number too short',
			3: 'Phone number too long',
			5: 'The number is longer than all valid numbers for this region',
		};

		if (validationErrors[validationError]) {
			$('.notice-warning[data-id="'+id+'"]').text(validationErrors[validationError])
			$('.notice-warning[data-id="'+id+'"]').show()
			return validationErrors[validationError]
		} else {
			return validationError
		}

	}

	$('body').on('click', '.remove-athlete', function() {
		let id = $(this).data('id')
		
		$('#'+id).remove()
	})

	function getRandomId() {
		let rowId = ''
		let letters = 'abcdefghijklmnopqrstuvwxyz';
		for (let i = 0; i < 3; i++) {
			rowId += letters.charAt(Math.floor(Math.random() * letters.length));
		}

		if ($('#membership_form #athlete_'+rowId).length > 0) {
			getRandomId()
		} else {
			return rowId
		}

	}

	$('.add-athlete-btn').on('click', function() {
		let container = $('.athlete-section')

		let rowId = getRandomId()

		let rowCount = 1
		if (container.children().length >= 1) {
			rowCount = container.children().length + 1
		}

		$.each($('.athlete-section .collapse + div'), function(i, el) {
			$(el).slideUp()
		})

		$.ajax({
			url : <?php echo '"'.admin_url( 'admin-ajax.php' ).'"'; ?>,
			data : {
					action: "render_program_cards",
					id: rowId,
					count: rowCount,
				},
			success: function(response) {
				let html = JSON.parse(response)

				container.append(html)
			}
		});

	})

    const myFuncs = {
		main_account: function (targetForm) { 
            $('.edit-container #success-notice').empty()

			targetFields = {
                'first_name' : $('input#first_name').val(),
                'last_name' : $('input#last_name').val(),
                'user_login' : $('input#user_login').val(),
                'user_email' : $('input#user_email').val(),
                'curr_pass' : $('input#curr_pass').val(),
                'user_pass' : $('input#user_pass').val(),
                'nonce' : $('input[name="main_edit_nonce"]').val(),
            }

            ajaxResponse(targetFields, targetForm, true).then(json => {
				$( '.modal' ).modal( 'hide' );
				$('.blocker').hide();
				$('body').css('overflow', 'auto')
				$('.edit-container #success-notice').append(json)
            })
		},
        billing_account: function (targetForm) { 
			$('.edit-container #success-notice').empty()

            targetFields = {
                'alternate_email_1' : $('input[name="alternate_email_1"]').val(),
                'alternate_email_2' : $('input[name="alternate_email_2"]').val(),
                'mobile_sms' : $('input[name="mobile_sms"]').val(),
                'carrier' : $('select[name="carrier"]').val(),
                'customer_id' : $('input[name="customer_id"]').val(),
                'billing_first_name' : $('input[name="billing_first_name"]').val(),
                'billing_last_name' : $('input[name="billing_last_name"]').val(),
                'billing_address_1' : $('input[name="billing_address_1"]').val(),
                'billing_city' : $('input[name="billing_city"]').val(),
                'billing_state' : $('input[name="billing_state"]').val(),
                'billing_postcode' : $('input[name="billing_postcode"]').val(),
                'billing_phone' : $('input[name="billing_phone"]').val(),
                'billing_phone_2' : $('input[name="billing_phone_2"]').val(),
				'nonce' : $('input[name="bill_edit_nonce"]').val(),
            }
            
            ajaxResponse(targetFields, targetForm, true).then(json => {
				$( '.modal' ).modal( 'hide' );
				$('.blocker').hide();
				$('.edit-container #success-notice').append(json)
				$('body').css('overflow', 'auto')
            })
         },
        child_details: function (targetForm) { 
			$('.edit-container #success-notice').empty()

            targetFields = {
                'child_id' : $('input[name="child_id"]').val(),
                'first_name' : $('input#child_first_name').val(),
                'last_name' : $('input#child_last_name').val(),
                'child_middle_name' : $('input[name="child_middle_name"]').val(),
                'user_login' : $('input#child_login').val(),
                'user_email' : $('input#child_email').val(),
                'curr_pass' : $('input#child_curr_pass').val(),
                'user_pass' : $('input#child_pass').val(),
                'suffix' : $('select[name="suffix"]').val(),
                'child_birth' : $('select[name="child_birth"]').val(),
                'preferred_name' : $('input[name="preferred_name"]').val(),
                'gender' : $('select[name="gender"]').val(),
                'guardian_first_name_1' : $('input[name="guardian_first_name_1"]').val(),
                'guardian_last_name_1' : $('input[name="guardian_last_name_1"]').val(),
                'guardian_home_phone_1' : $('input[name="guardian_home_phone_1"]').val(),
                'guardian_work_phone_1' : $('input[name="guardian_work_phone_1"]').val(),
                'guardian_mobile_phone_1' : $('input[name="guardian_mobile_phone_1"]').val(),
                'guardian_first_name_2' : $('input[name="guardian_first_name_2"]').val(),
                'guardian_last_name_2' : $('input[name="guardian_last_name_2"]').val(),
                'guardian_home_phone_2' : $('input[name="guardian_home_phone_2"]').val(),
                'guardian_work_phone_2' : $('input[name="guardian_work_phone_2"]').val(),
                'guardian_mobile_phone_2' : $('input[name="guardian_mobile_phone_2"]').val(),
                'insurance_carrier' : $('input[name="insurance_carrier"]').val(),
                'insurance_phone' : $('input[name="insurance_phone"]').val(),
                'emergency_name_1' : $('input[name="emergency_name_1"]').val(),
                'emergency_phone_1' : $('input[name="emergency_phone_1"]').val(),
                'emergency_name_2' : $('input[name="emergency_name_2"]').val(),
                'emergency_phone_2' : $('input[name="emergency_phone_2"]').val(),
                'medic_name' : $('input[name="medic_name"]').val(),
                'medic_phone' : $('input[name="medic_phone"]').val(),
                'medic_notes' : $('textarea[name="medic_notes"]').val(),
                'medication' : $('input[name="medication"]').val(),
				'nonce' : $('input[name="child_edit_nonce"]').val(),
            }

			ajaxResponse(targetFields, targetForm, true).then(json => {
				$( '.modal' ).modal( 'hide' );
				$('.blocker').hide();
				$('.edit-container #success-notice').append(json)
				$('body').css('overflow', 'auto')
            })
         }, add_subaccount: function (targetForm) { 
			$('.edit-container #success-notice').empty()

            requiredFields = ['child_first_name', 'child_last_name', 'child_birth', 'gender', 'slots', 'enrolled']
            let input = $('.add_subaccount *[name^="athletes"]')
			let fields = {}
			let athletes = {}
			let enrolled = []

			fields['athletes'] = getAthleteFields(athletes, input, 'athlete_0', enrolled)

			$.ajax({
				url: <?php echo '"'.admin_url( 'admin-ajax.php' ).'"'; ?>,
				data : {action: "validate_membership_form", 
						fields : JSON.stringify(fields), 
						requiredFields: requiredFields
				},
				success: function(response) {
					response = JSON.parse(response)
					console.log(response);
					if (response.alerts) {
						$.each(response.alerts, function(i, el) {
							if (i == 'athletes') {
								$.each(el, function(id, ath) {
									$.each(ath, function(item, val) {
										$('#'+id+' *[data-name="'+item+'"] + .notice-warning').removeClass('hidden')
										$('#'+id+' *[data-name="'+item+'"] + .notice-warning').show()
									})
								})
							}
						})

						let warnings = $('.notice-warning').filter(':not(.hidden)').first();
						
						if (warnings.length) {
							$('html, body').animate({
								scrollTop: warnings.offset().top
							}, 500);
						}

					} else {
						$.ajax({
							url: <?php echo '"'.admin_url( 'admin-ajax.php' ).'"'; ?>,
							data : {action: "create_new_subaccount", 
									fields : fields, 
							},
							success: function(response) {
								response = JSON.parse(response)

								$( '.modal' ).modal( 'hide' );
								$('.blocker').hide();
								$('.edit-container #success-notice').append(response)
								$('body').css('overflow', 'auto')
							}
						})	

					}
				}
			});
		}
	};

      async function ajaxResponse(targetFields, targetForm, edit = false) {
          let response = await $.ajax({
                url: <?php echo '"'.admin_url( 'admin-ajax.php' ).'"'; ?>,
                data : {action: "validate_form", 
                        targetFields : targetFields, 
                        'form' : targetForm,
						'edit' : edit
            }
        });
			// console.log(response);
			return Promise.resolve(JSON.parse(response));
        }
	
});
    </script> 
    <?php
}

add_action("wp_ajax_validate_form", "validate_form");
add_action("wp_ajax_nopriv_validate_form", "validate_form");

add_action("wp_ajax_get_added_fields", "get_added_fields");
add_action("wp_ajax_nopriv_get_added_fields", "get_added_fields");

add_action("wp_ajax_get_available_slots_athlete", "get_available_slots_athlete");
add_action("wp_ajax_nopriv_get_available_slots_athlete", "get_available_slots_athlete");

add_action("wp_ajax_validate_email_exists", "validate_email_exists");
add_action("wp_ajax_nopriv_validate_email_exists", "validate_email_exists");

add_action("wp_ajax_render_program_cards", "render_program_cards");
add_action("wp_ajax_nopriv_render_program_cards", "render_program_cards");

add_action("wp_ajax_validate_membership_form", "validate_membership_form");
add_action("wp_ajax_nopriv_validate_membership_form", "validate_membership_form");

add_action("wp_ajax_render_enrollment_inputs", "render_enrollment_inputs");
add_action("wp_ajax_nopriv_render_enrollment_inputs", "render_enrollment_inputs");

add_action("wp_ajax_render_payment_section", "render_payment_section");
add_action("wp_ajax_nopriv_render_payment_section", "render_payment_section");

add_action("wp_ajax_create_new_subaccount", "create_new_subaccount");
add_action("wp_ajax_nopriv_create_new_subaccount", "create_new_subaccount");

function render_payment_section() {
	ob_start();
	require(GY_CRM_PLUGIN_DIR . 'views/templates/public/registration/payment_section.php');
	$html = ob_get_contents();
	ob_clean();

	echo json_encode($html);

	die();
}

function render_enrollment_inputs() {
	global $wpdb;

	if (isset($_GET['value']) && isset($_GET['athlete'])) {
		$value = $_GET['value'];
		$athlete_id = $_GET['athlete'];

		if ($value == 'classes') {
			ob_start();
			require(GY_CRM_PLUGIN_DIR . 'views/templates/public/registration/registration_classes.php');
			$html = ob_get_contents();
			ob_clean();

			echo json_encode($html);
		} else {
			$sql = 'SELECT ID, pm1.meta_value AS slot_id, pm2.meta_value AS max_enrollments
					FROM wp_posts
					JOIN wp_postmeta pm1
						ON ID = pm1.post_id
						AND pm1.meta_key = "slot_ids"
					JOIN wp_postmeta pm2
						ON ID = pm2.post_id
						AND pm2.meta_key LIKE "%max_enrollments%"
						AND post_title = "Private Lessons"';

			$results = $wpdb->get_results($sql);

			$slot_ids = unserialize($results[0]->slot_id);

			$sql = 'SELECT COUNT(user_id) AS users FROM wp_usermeta WHERE meta_key = "slots" AND meta_value LIKE "%'.$slot_ids[0].'%"';
			$enrolled = $wpdb->get_results($sql);

			if ($results[0]->max_enrollments > $enrolled[0]->users) {

				$slot_id = $slot_ids[0];
				$class_id = $results[0]->ID;
	
				ob_start();
				require(GY_CRM_PLUGIN_DIR . 'views/templates/public/registration/registration_private.php');
				$html = ob_get_contents();
				ob_clean();
				echo json_encode($html);
			} else {
				echo json_encode(0);
			}


		}
	}

	die();
}

function validate_membership_form() {
	$fields = json_decode(stripslashes($_GET['fields']), true);
	$required_fields = $_GET['requiredFields'];
	
	$show_alerts = [];
	$empty_fields = [];

	$is_inadmissible = false;

	foreach ($fields as $key => $field) {
		if ($key !== 'athletes') {			
			$validated = sanitize_text_field( $field );
			
			if (empty($validated) ) {
				$empty_fields[$key] = $field;
				$show_alerts[$key] = $field;
			} else {
				if ($key == 'start_date') {
					if ($field < date('Y-m-d')) {
						$empty_fields[$key] = $field;
						$show_alerts[$key] = $field;
					}
				}

				if ($key == 'password') {
					if (strlen($field) < 8) {
						$empty_fields[$key] = $field;
						$show_alerts[$key] = $field;
					}
				}
			}
		} 
		else {
			foreach($field as $i => $athlete) {
				foreach($athlete as $it => $item) {
					
					if ($it == 'enrolled') {
						foreach($item as $enroll) {
							if (empty($athlete[$enroll])) {
								$empty_fields[$it] = $item;
								$show_alerts['athletes'][$i][$it] = $enroll;
							}
						}
					} else {
						$validated = sanitize_text_field( $item);

						if (empty($validated)) {
							$empty_fields[$it] = $item;
							$show_alerts['athletes'][$i][$it] = $item;
						}
					}
					
				}
			}
		}
	}

	foreach($required_fields as $required) {
		if (array_key_exists($required, $empty_fields)) {
			$is_inadmissible = true;
		}
	}

	if (isset($fields->email)) {
		$is_email = get_user_by('email', $fields->email);

		if (!empty($is_email)) {
			$show_alerts['email'] = 1;
			$is_inadmissible = true;
		}
	}

	if ($is_inadmissible) {
		echo json_encode(array('alerts' => $show_alerts));
	} else {
		echo json_encode(1);
	}

	die();
}

function validate_email_exists() {
	$email = $_GET['email'];

	$is_email = get_user_by('email', $email);

	if (!empty($is_email)) {
		echo json_encode(0);
	} else {
		echo json_encode(1);
	}

	die();
}
function get_available_slots_athlete() {
	$slots = get_available_class_slot();

	echo json_encode($slots);

	die();
}

function get_available_class_slot() {
	$slot_week = ['_slot_time_monday' => 'MON', '_slot_time_tuesday' => 'TUE', '_slot_time_wednesday' => 'WED', '_slot_time_thursday' => 'THU', '_slot_time_friday' => 'FRI', '_slot_time_saturday' => 'SAT', '_slot_time_sunday' => 'SUN'];
	$html = '';
	global $wpdb;

	$classes = get_posts(array(
		'post_type' => 'class',
		'posts_per_page' => -1,
	));

	$programs_schedules = [];

	foreach($classes as $class) {
		$slot_ids = get_post_meta($class->ID, 'slot_ids', true);
		$count = 0;

		if (!empty($slot_ids)) {
			foreach ($slot_ids as $slot) {
				$count += 1;

				$sql = 'SELECT * FROM wp_postmeta WHERE meta_key LIKE %s AND post_id = %s';
				$where = ["%$slot%", $class->ID];

				$results = wp_list_pluck($wpdb->get_results($wpdb->prepare($sql, $where)), 'meta_value', 'meta_key');

				$hours_per_week = get_post_meta($class->ID, 'hours_per_week', true);

				if (!empty($results) && !empty($hours_per_week)) {
					array_push($programs_schedules, array('id' => $class->ID,
						'class' => $class->post_title . ' SLOT #'.$count,
						'slot_id' => $slot));
				}
			}
		}

	}

	foreach ($programs_schedules as $class) {
		$available = get_post_meta($class['id'], $class['slot_id'].'_slot_registration_available', true);
		$max_enrollments = get_post_meta($class['id'], $class['slot_id'].'_slot_max_enrollments', true);
		$users_enrolled = get_enrolled_athletes($class['slot_id']);
		
		if ($available == 1 && $users_enrolled < $max_enrollments || empty($max_enrollments)) {
			foreach($slot_week as $meta => $day) {
				if (!empty(get_post_meta($class['id'], $class['slot_id'].$meta, true))) {
					$html .= '<option data-meta="'.$class['slot_id'].'" value="'.$class['id'].'">'.$class['class'].'</option>';
				}
			}
		}
	}

	return $html;
}

function get_enrolled_athletes($key) {
    global $wpdb;

    $sql = 'SELECT count(user_id) AS users_enrolled FROM wp_usermeta WHERE meta_key = %s AND meta_value = "active"';
    $where = ['status_program_participant_'.$key];

    $results = $wpdb->get_results($wpdb->prepare($sql, $where));
    $users_enrolled = $results[0]->users_enrolled;

    return $users_enrolled;
}

function validate_form() {

	$form = $_GET["form"];
	$targetFields = $_GET["targetFields"];

	$nonce = $targetFields['nonce'];

	if (wp_verify_nonce($nonce, 'edit_nonce') || is_user_logged_in()) {
		if ($_GET["edit"] == 'true') {
			$user_id = get_current_user_id();
	
			$validated_fields = [];
			
			foreach ($targetFields as $key => $value) {
				$field = validate_fields($targetFields, $key);
				
				if ($field) {
					$validated_fields[$key] = $field;
				}
			}
	
			$result = $form($user_id, $validated_fields);
		} else {
			$result = $form($targetFields);
		}
		
		$result = json_encode($result);
		echo $result;

	} else {
		echo json_encode(0);
	}

	die();
}


function get_added_fields() {

	if (wp_verify_nonce($_GET['nonce'], 'registration_nonce') || is_user_logged_in()) {
		$parent_id = $_GET["parent_id"];
	
		$child_id = get_user_meta($parent_id, 'smuac_multiaccounts_list', true);
		$child_id =  str_replace(',', '', $child_id);
		$child_first_name = get_user_meta($child_id, 'first_name', true);
		$child_last_name = get_user_meta($child_id, 'last_name', true);
		$child_birth = get_user_meta($child_id, 'child_birth', true);
		$phone_number = get_user_meta($parent_id, 'billing_phone', true);
		$first_name = get_user_meta($parent_id, 'first_name', true);
		$last_name = get_user_meta($parent_id, 'last_name', true);
	
		$result = array(
			'parent_first_name' => $first_name,
			'parent_last_name' => $last_name,
			'phone_number' => $phone_number,
			'child_first_name' => $child_first_name,
			'child_last_name' => $child_last_name,
			'child_birth' => $child_birth,
			'child_id' => $child_id
		);
	
		$result = json_encode($result);
		echo $result;
	} else {
		echo json_encode(0);
	}
	
	die();
}

function create_new_subaccount() {
	global $wpdb;
	$notices = array();

	if (isset($_GET['fields'])) {
		$fields = $_GET['fields']['athletes']['athlete_0'];
		$parent_user_id = get_current_user_id();
		$parent_user = get_user_by('id', $parent_user_id);
		$multiaccounts_maximum_limit = 500;
	
		$current_multiaccounts_number = get_user_meta( $parent_user_id, 'smuac_multiaccounts_number', true );
		if ( '' == $current_multiaccounts_number ) {
			$current_multiaccounts_number = 0;	
		}
		if ( intval( $current_multiaccounts_number ) < $multiaccounts_maximum_limit ) {
	
			$date = date('mdYHis');
			$email_domain_extension = '@gymnasticsofyork.com';
			$parent_name = $parent_user->first_name;
			$parent_name = preg_replace('/[^a-zA-Z0-9._@-]/', '', $parent_name);
			$childemail = $parent_name.'_'.$fields['child_first_name'].'_'.$date.$email_domain_extension;
	
			$childusername = $fields['child_first_name'].$fields['child_last_name'];
			$validated_childusername = check_existing_athlete($childusername);
	
			$child_user_id = wc_create_new_customer( $childemail, $validated_childusername, $parent_user->user_pass );
	
			if ( ! ( is_wp_error( $child_user_id ) ) ) {
	
				$slots = explode(',', $fields['slots']);
				$selected_programs = explode(',', $fields['classes']);
				
				foreach($slots as $slot) {
					$sql = 'SELECT post_id FROM wp_postmeta WHERE meta_value LIKE %s';
					$where = ["%$slot%"];
	
					$results = $wpdb->get_results($wpdb->prepare($sql, $where));
					$slot_ids[$results[0]->post_id][] = $slot;
				}
				
				if (isset($fields['lessons'])) {
					$slot_ids[$fields['private_lessons']][] = $fields['lessons'];
					$slots[] = $fields['lessons'];
					$selected_programs[] = $fields['private_lessons'];
				}
	
				update_user_meta( $child_user_id, 'status_program_participant', 'active' );
				update_user_meta( $child_user_id, 'classes', array( $selected_programs ) );
				update_user_meta( $child_user_id, 'classes_slots', array( $slot_ids ) );
				update_user_meta( $child_user_id, 'slots', array($slots));
				update_user_meta( $child_user_id, 'first_name', $fields['child_first_name'] );
				update_user_meta( $child_user_id, 'last_name', $fields['child_last_name'] );
				update_user_meta( $child_user_id, 'child_birth', $fields['child_birth'] );
				update_user_meta( $child_user_id, 'smuac_account_type', 'multiaccount' );
				update_user_meta( $child_user_id, 'smuac_account_type', 'multiaccount' );
				update_user_meta( $child_user_id, 'smuac_account_parent', $parent_user_id );
				update_user_meta( $child_user_id, 'smuac_account_name', $validated_childusername );
				update_user_meta( $child_user_id, 'smuac_account_phone', '' );
				update_user_meta( $child_user_id, 'smuac_account_job_title', '' );
				update_user_meta( $child_user_id, 'smuac_account_permission_buy', '' ); // true or false
				update_user_meta( $child_user_id, 'smuac_account_permission_view_orders', '' ); // true or false
				update_user_meta( $child_user_id, 'smuac_account_permission_view_bundles', '' ); // true or false
				update_user_meta( $child_user_id, 'smuac_account_permission_view_discussions', ''); // true or false
				update_user_meta( $child_user_id, 'smuac_account_permission_view_lists', '' ); // true or false
				!empty($fields['gender']) ? update_user_meta($child_user_id, 'gender', $fields['gender']) : null;
				!empty($fields['child_middle_name']) ? update_user_meta($child_user_id, 'child_middle_name', $fields['child_middle_name']) : null;
	
				// set parent multiaccount details meta
				$current_multiaccounts_number++;
				update_user_meta( $parent_user_id, 'smuac_multiaccounts_number', $current_multiaccounts_number );
	
				$current_multiaccounts_list = get_user_meta( $parent_user_id, 'smuac_multiaccounts_list', true );
				$current_multiaccounts_list = $current_multiaccounts_list . ',' . $child_user_id;
				update_user_meta( $parent_user_id, 'smuac_multiaccounts_list', $current_multiaccounts_list );
	
				$userobj = new WP_User( $child_user_id );
				$userobj->set_role( 'customer' );
	
				if ($userobj) {
					array_push($notices, '<div class="notice notice-success is-dismissible registration-notice"><p>Success: Athlete succesfully created.</p></div>' );
				} else {
					array_push($notices, '<div class="notice notice-warning is-dismissible registration-notice"><p>Error</p></div>' );
				}
	
			}
		}
	}

	echo json_encode($notices);
	die();
}

function validate_fields($target, $key)
{
	$field = sanitize_text_field( $target[$key] );

	if (!empty($field) ) {
		return $field;
	}
}

function billing_account($user_id, $fields) {
	foreach($fields as $key => $field) {
		if ($key !== 'billing_account') {
			update_user_meta($user_id, $key, $field);
		}
	}

	return '<div class="notice notice-success is-dismissible"><p>Success: Billing updated.</p></div>';
}

function main_account($user_id, $fields)
    {
        $notice = array();

        $user = wp_get_current_user();

        if (isset($fields['curr_pass']) && isset($fields['user_pass'])) {
            if (wp_check_password($fields['curr_pass'], $user->user_pass, $user_id)) {
                $user->user_pass = $fields['user_pass'];
            } else {
                array_push($notice, '<div class="notice notice-warning is-dismissible"><p>Error: Wrong password</p></div>');
            }
        } else if (!isset($fields['curr_pass']) && isset($fields['user_pass'])) {
            array_push($notice, '<div class="notice notice-warning is-dismissible"><p>Error: Enter your current password to update.</p></div>');
        }

        foreach($fields as $key => $field) {

            if ($key == 'first_name' || $key == 'last_name') {
                update_user_meta($user_id, $key, $field);
            } 

            if ($key !== 'curr_pass' && $key !== 'user_pass') {
                $user->$key = $field;
            }
        }

        if (wp_update_user($user)) {
            array_push($notice, '<div class="notice notice-success is-dismissible"><p>Success: Account updated.</p></div>');
        } else {
            array_push($notice, '<div class="notice notice-warning is-dismissible"><p>Error.</p></div>');
        }


        return $notice;
    }

	function child_details($user_id, $fields) 
    {
        $notice = array();
        $parent = wp_get_current_user();

        if (isset($fields['child_id']) && get_user_meta($fields['child_id'], 'smuac_account_parent', true) == $parent->ID) {
            $child = get_user_by('id', $fields['child_id']);
    
            foreach($fields as $key => $field) {
    
                if ($key !== 'child_details' && $key !== 'child_birth' && $key !== 'user_email' && $key !== 'user_pass' && $key !== 'curr_pass' && $key !== 'child_id') {
                    update_user_meta($fields['child_id'], $key, $field);
                } 

                if ($key == 'child_birth') {
					
					$max_age = date('Y-m-d', strtotime('-18 years'));
					$min_age = date('Y-m-d', strtotime('-1 year'));

					$field = date('Y-m-d', strtotime($field));
					if ( $field >= $max_age && $field <= $min_age ) {
                        update_user_meta($fields['child_id'], $key, $field);
					} else {
                        array_push($notice, '<div class="notice notice-warning is-dismissible"><p>Error: Child age must be between 1 and 18 years old.</p></div>');
                    }

				}
    
				$child->$key = $field;
            }
    
            if (wp_update_user($child)) {
                array_push($notice, '<div class="notice notice-success is-dismissible"><p>Success: '.$fields['first_name'].' Account updated.</p></div>');
            } else {
                array_push($notice, '<div class="notice notice-warning is-dismissible"><p>Error.</p></div>');
            }
        } else {
            array_push($notice, '<div class="notice notice-warning is-dismissible"><p>Error: Multiaccount doesn\'t exist</p></div>');
        }

        return $notice;
    }

	function render_program_cards() {
		if ($_GET['id']) {
			$athlete_id = $_GET['id'];
			$count = $_GET['count'];

			ob_start();
			require(GY_CRM_PLUGIN_DIR . 'views/templates/public/registration/athlete_form.php');
			$html = ob_get_contents();
			ob_clean();

			echo json_encode($html);
		}

		die();
	}

	function available_programs($id) {
		$slot_week = ['_slot_time_monday' => 'MON', '_slot_time_tuesday' => 'TUE', '_slot_time_wednesday' => 'WED', '_slot_time_thursday' => 'THU', '_slot_time_friday' => 'FRI', '_slot_time_saturday' => 'SAT', '_slot_time_sunday' => 'SUN'];
		$html = '';

        global $wpdb;

        $terms = get_terms( 'programs', array(
            'orderby'           => 'name', 
            'order'             => 'ASC',
            'hide_empty'        => true, 
            'fields'            => 'all', 
            'hierarchical'      => true, 
            'child_of'          => 0,
            'childless'         => false,
            'pad_counts'        => false, 
            'cache_domain'      => 'core',
        ) );

        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ( $terms as $term ) {
                $post_count = 0;

                //***************** */ GET ALL CLASSES BY TITLE

                $args = array('post_type' => 'class',
                    'publish_status' => 'published',
                    'posts_per_page' => -1,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'programs',
                            'field' => 'slug',
                            'terms' => $term->slug,
                        ),
                        )
                );
                
                $posts = get_posts( $args );

                if (!empty($posts)) {

                    foreach($posts as $post) {
                        $slots = [];
                        $slot_count = 0;
                        $slot_ids = get_post_meta($post->ID, 'slot_ids', true);
                        
                        if (!empty($slot_ids)) {
    
                            foreach ($slot_ids as $slot) {
                                $is_not_empty = false;

                                $sql = 'SELECT * FROM wp_postmeta WHERE meta_key LIKE %s AND post_id = %s';
                                $where = ["%$slot%", $post->ID];
                
                                $results = wp_list_pluck($wpdb->get_results($wpdb->prepare($sql, $where)), 'meta_value', 'meta_key');

                                if (!empty($results)) {
                                    foreach($results as $key => $result) {
                                        if (!empty($result)) {
                                            $is_not_empty = true;
                                        }
                                    }
                                    
                                    $slot_status = get_post_meta($post->ID, $slot.'_slot_status', true);
                                    $is_available = get_post_meta($post->ID, $slot.'_slot_registration_available', true);
                                    
                                    if ($is_not_empty && !empty($is_available) && $is_available !== '0' && $slot_status !== 'inactive') {
                                        $slot_count += 1;
                                        $post_count += 1;
                                        $slots[$slot] = $results;
                                    }
                                }
                            }

                            if ($slot_count >= 1) {
                                $classes[] = array(
                                    'term_id' => $term->term_id,
                                    'post_id' => $post->ID,
                                    'title' => $post->post_title,
                                    'duration' => get_field('duration', $post->ID),
                                    'age_from' =>  get_field('age_from', $post->ID),
                                    'age_to' =>  get_field('age_to', $post->ID),
                                    'slots' => $slots
                                );
                            }
    
                        }
                    }

                    if ($post_count >= 1) {
                        $programs[$term->term_id]['name'] = $term->name;
                        $programs[$term->term_id]['excerpt'] = get_field('excerpt', $term);
                        $programs[$term->term_id]['thumbnail'] = get_field('thumbnail', $term);
                    }
                }
        }

        if (isset($programs)) {

            $html .= '<div class="enrollment">
                <div class="enroll-container">';
            
            foreach ($programs as $key => $program) {
                $html .=  '<div class="programs-container" data-slots=".program_'.$key.$id.'">
                        <div class="program-card" style="background-image: url('.$program['thumbnail'] .')"">
                            <div class="program-card-title">'. $program['name'] .'</div>
                            <div class="program-card-excerpt">'.$program['excerpt'] .'</div>
                            </div>
                    </div>';
            }

            $html .= '</div>
			<div class="slots-container">';
            
            foreach ($classes as $class) {
				$cell_days[$class['post_id']] = [];
				foreach ($class['slots'] as $key => $slot) {
					foreach($slot_week as $meta => $day) {
						if (!isset($cell_days[$class['post_id']][0][$day])) {
							$cell_days[$class['post_id']][0][$day] = 0;
						}
						if (!empty($slot[$key.$meta])) {
							$cell_days[$class['post_id']][0][$day] += 1;
						}
					}
				}
			}

            foreach ($classes as $class) {

                $html .= '<div class="panel program_'.$class['term_id'].$id.'">
                    <div class="panel-header">
                        <p>'.$class['title'].'</p>
                    </div>
                    <div class="flex-container">
                        <p><span class="panel-description">Duration: </span>'.$class['duration'] * 60 .' min</p>';
                        if (isset($class['age_from']) && isset($class['age_to']) && $class['age_from'] > 0 && $class['age_to'] > 0) {
                            $html .= '<p><span class="panel-description">From: </span>'.$class['age_from'] .'yr - '.$class['age_to'] .'yr</p>';
                        }
				$table = '</div>
					<table>
						<thead>
							<tr>
								<th>Slot</th>
								<th>MON</th>
								<th>TUE</th>
								<th>WED</th>
								<th>THU</th>
								<th>FRI</th>
								<th>SAT</th>
								<th>SUN</th>
								<th></th>
							</tr>
						</thead>
						<tbody>';

				$count = 0;

				foreach ($class['slots'] as $key => $slot) {
					
					$count += 1;
					$table .= '<tr>
					<td>#'. $count.'</td>';

					foreach($slot_week as $meta => $day) {

						if (!empty($slot[$key.$meta])) {
							$time = date('g:i A', strtotime($slot[$key.$meta]));
							$start = strtotime($time);

							if (!empty($slot[$key.'_slot_duration'])) {
								$duration = $slot[$key.'_slot_duration'];
							} else {
								$duration = $class['duration'];
							}

							switch($duration) {
								case '0.5':
									$end = floatval($start) + 3600 * floatval($duration) + 30 * 00;
								break;
								case '1.5':
									$end = floatval($start) + 3600 * floatval($duration) + 30 * 60;
								break;
								default:
									$end = floatval($start) + 3600 * floatval($duration) + 00 * 60;
								break;
							}

							$hours = date('H', $end);
							$minutes = date('i', $end);

							if ($hours >= 12) {
								$hours = $hours - 12;
								$ampm = ' PM';
							} else {
								$ampm = ' AM';
							}

							$table .= '<td>'.$time .' - '.$hours.':'.$minutes.$ampm.'</td>';
						} else {
							if ($cell_days[$class['post_id']][0][$day] > 0) {
								$table .= '<td>x</td>';
							} else {
								$table .= '<td class="remove-slot" data-id="program_'.$class['term_id'].$id.'">x</td>';
							}
						}
					}

					if (isset($slot[$key.'_slot_max_enrollments'])) {
						$users_enrolled = get_enrolled_athletes($key);

						if ($users_enrolled == $slot[$key.'_slot_max_enrollments']) {
							$table .= '<td class="program-class-full">Full</td>';
						} else {
							$table .= '<td class="btn" data-athlete="'.$id.'" data-program="'.$class['post_id'].'" data-slot="'.$key.'"></td>';
						}
					}
				}
				$table .= '<tr>
				</tbody>
				</table>';

			$html .= $table;
			$html .= '</div>';

			}

			$html .= '</div>
				</div>';

		}
	}

		return $html;
}

?>