<?php
add_action( 'admin_footer', 'get_classes_email_templates' );

function get_classes_email_templates() { ?>
    <script type="text/javascript" >
    jQuery(document).ready(function($) {

	    $('.email_type').on('change', function() {
            if ($(this).val() == 'tag') {
                $.ajax({
                    url: <?php echo '"'.admin_url( 'admin-ajax.php' ).'"'; ?>,
                    data : {
                        action: 'get_athletes_with_tags',
                    },
                    success: function(response) {
                        $('#tab1 #select-options-tags').html(JSON.parse(response))
                        $('#tab1 .user_select_tags').show()
                    }
                });
            }
            
            if ($(this).val() == 'no-credit') {
                $('#select-options-no + .absolute').show()
                $.ajax({
                    url: <?php echo '"'.admin_url( 'admin-ajax.php' ).'"'; ?>,
                    data : {
                        action: 'get_owing_users',
                        filter: 'not_file'
                    },
                    success: function(response) {
                        response = JSON.parse(response)
                        $('#select-options-no').html(response)
                        $('#select-options-no + .absolute').hide()
                    }
                });
            }

            if ($(this).val() == 'accounts-owing') {
                $('#select-options-account + .absolute').show()
                $.ajax({
                    url: <?php echo '"'.admin_url( 'admin-ajax.php' ).'"'; ?>,
                    data : {
                        action: 'get_owing_users',
                    },
                    success: function(response) {
                        response = JSON.parse(response)
                        $('#select-options-account').html(response)
                        $('#select-options-account + .absolute').hide()
                    }
                });
            }
        })


        $('#programs_classes').on('change', function() {
            let program = $(this).val();

            // Close the drop-down menu when changing programs
            $('#custom-select #select-options').removeClass('open'); 
            $('#tab1 .program_user_select_slots').hide()
            $('#custom-select-slots #select-trigger-slots').text('All Slots are Selected');
            $('#custom-select #select-trigger').text('All users are selected');

            if (program !== 'all_programs') {
                $.ajax({
                    url: <?php echo '"'.admin_url( 'admin-ajax.php' ).'"'; ?>,
                    data : {
                        action: "get_users_per_class", 
                        program: program,
                    },
                    success: function(response) {
                        let emails = JSON.parse(response);
                        let html = '';

                        $.each(emails, function(i, el) {
                            html += `<label><input type="checkbox" name="selected_users_programs[]" value="${el.ID}" checked>${el.user_email}</label><br>`;
                        });

                        // Place the generated HTML in the select-options div
                        $('#tab1 .program_user_select #select-options').html(html);

                        $('#tab1 .program_user_select').show();

                        // Drop-down menu toggle


                        // Handling menu selections and closing the menu
                        $('#custom-select #select-options label input[type="checkbox"]').on('change', function() {
                            let selectedText = [];
                            $('#custom-select #select-options label input:checked').each(function() {
                                selectedText.push($(this).parent().text());
                            });

                            if (selectedText.length > 0) {
                                $('#custom-select #select-trigger').text(selectedText.join(', '));
                            } else {
                                $('#custom-select #select-trigger').text('Select Users');
                            }
                        });
                    }
                });

                $.ajax({
                    url: <?php echo '"'.admin_url( 'admin-ajax.php' ).'"'; ?>,
                    data : {
                        action: 'get_multiselect_slots',
                        class: program,
                    },
                    success: function(response) {
                        $('#tab1 #select-options-slots').html(JSON.parse(response))
                        $('#tab1 .program_user_select_slots').show()
                    }
                });
            }
        });

        $('body').on('change', '#select-options-slots input', function() {
            let slots = []
            $.each($('#select-options-slots input'), function(i, el) {
                if ($(el).is(':checked')) {
                    slots.push($(el).val())
                }
            })

            $.ajax({
                url: <?php echo '"'.admin_url( 'admin-ajax.php' ).'"'; ?>,
                data : {
                    action: "get_users_per_class", 
                    slots: slots,
                },
                success: function(response) {
                    let emails = JSON.parse(response);
                    let html = '';

                    $.each(emails, function(i, el) {
                        html += `<label><input type="checkbox" name="selected_users_programs[]" value="${el.ID}" checked>${el.user_email}</label><br>`;
                    });

                    // Place the generated HTML in the select-options div
                    $('#tab1 .program_user_select #select-options').html(html);
                }
            })
        })

        $('body').on('click', '#custom-select #select-trigger', function() {
            $('#custom-select #select-options').toggleClass('open');
        });
        
        $('body').on('click', '#custom-select-tags #select-trigger-tags', function() {
            $('#custom-select-tags #select-options-tags').toggleClass('open');
        });
        
        $('body').on('click', '#custom-select-slots #select-trigger-slots', function() {
            $('#custom-select-slots #select-options-slots').toggleClass('open');
        });

        $('body').on('change', '.users-by-tags select', function() {
            let val = $(this).val()

            if (val !== '') {
                $.ajax({
                    method: 'GET',
                    url: <?php echo '"'.admin_url( 'admin-ajax.php' ).'"'; ?>,
                    data : {
                        action: 'get_athletes_with_tags',
                        tag: val
                    },
                    success: function(response) {
                        $('#tab1 #select-options-tags').html(JSON.parse(response))
                        $('#tab1 .user_select_tags').show()
                    }
                });
            }
        })

        $('body').on('change', '#select-options-tags label input[type="checkbox"]', function() {
            let selectedTags = []
            let selectedTagsText = []
            $('#select-options-tags label input[type="checkbox"]').each(function(i, el) {
                if ($(el).is(':checked')) {
                    selectedTags.push($(el).val())
                    selectedTagsText.push($(el).parent().text())
                }
            })

            if (selectedTagsText.length > 0) {
                $('#custom-select-tags #select-trigger-tags').text(selectedTagsText.join(', '));
            } else {
                $('#custom-select-tags #select-trigger-tags').text('Select Tag');
            }
        })

        $('body').on('change', '#select-options-slots label input[type="checkbox"]', function() {
            let selectedSlots = []
            let selectedSlotsText = []
            $('#select-options-slots label input[type="checkbox"]').each(function(i, el) {
                if ($(el).is(':checked')) {
                    selectedSlots.push($(el).val())
                    selectedSlotsText.push($(el).parent().text())
                }
            })

            if (selectedSlotsText.length > 0) {
                $('#custom-select-slots #select-trigger-slots').text(selectedSlotsText.join(', '));
            } else {
                $('#custom-select-slots #select-trigger-slots').text('Select Slots');
            }

        })
    });



    jQuery(document).ready(function ($) {
        $('#custom-select-account #select-options-account').removeClass('open'); 
        $('.email_type').on('change', function () {
            var selectedValue = $(this).val();

            if (selectedValue === 'accounts-owing') {

                $('#custom-select-account #select-trigger-account').on('click', function() {
                    $('#custom-select-account #select-options-account').toggleClass('open');
                });

                $('#custom-select-account #select-options-account label input[type="checkbox"]').on('change', function() {
                    let selectedText = [];
                    $('#custom-select-account #select-options-account label input:checked').each(function() {
                        selectedText.push($(this).parent().text());
                    });

                    if (selectedText.length > 0) {
                        $('#custom-select-account #select-trigger-account').text(selectedText.join(', '));
                    } else {
                        $('#custom-select-account #select-trigger-account').text('Select Users');
                    }
                });
            }
        });
    });

    jQuery(document).ready(function ($) {
        $('#custom-select-no #select-options-no').removeClass('open'); 
        $('.email_type').on('change', function () {
            var selectedValue = $(this).val();

            if (selectedValue === 'no-credit') {

                $('#custom-select-no #select-trigger-no').on('click', function() {
                    $('#custom-select-no #select-options-no').toggleClass('open');
                });

                $('#custom-select-no #select-options-no label input[type="checkbox"]').on('change', function() {
                    let selectedText = [];
                    $('#custom-select-no #select-options-no label input:checked').each(function() {
                        selectedText.push($(this).parent().text());
                    });

                    if (selectedText.length > 0) {
                        $('#custom-select-no #select-trigger-no').text(selectedText.join(', '));
                    } else {
                        $('#custom-select-no #select-trigger-no').text('Select Users');
                    }
                });

            }
        });
    });

</script> 
    <?php
}

add_action("wp_ajax_get_users_per_class", "get_users_per_class");
add_action("wp_ajax_get_owing_users", "get_owing_users");

function get_owing_users() {
    if (isset($_GET['filter'])) {
        $result = get_clients_with_outstanding_payments($_GET['filter']);
    } else {
        $result = get_clients_with_outstanding_payments();
    }

    $html = '';

    foreach ($result as $user) {
        $html .= '<label><input type="checkbox" name="selected_users_credit[]" value="'.$user->ID.'" checked>'.$user->first_name.' '.$user->last_name.'</label><br>';
    }

    echo json_encode($html);
    die();
}

function get_users_per_class() {
    global $wpdb;

    $sql = 'SELECT ID, user_email,
            CONCAT(first_name.meta_value, " ", last_name.meta_value) user_email
                FROM wp_users wu
                LEFT JOIN wp_usermeta AS first_name ON wu.ID = first_name.user_id AND first_name.meta_key = "first_name"
                LEFT JOIN wp_usermeta AS last_name ON wu.ID = last_name.user_id AND last_name.meta_key = "last_name"
                    WHERE ID IN (
                        SELECT meta_value
                        FROM wp_usermeta
                            WHERE meta_key = "smuac_account_parent"
                                AND user_id IN (
                                SELECT user_id
                                FROM wp_usermeta
                                    WHERE meta_key = %s
                                    AND meta_value LIKE "%s"
                            )
                            AND user_id IN (
                                SELECT user_id
                                FROM wp_usermeta
                                WHERE meta_key = "status_program_participant"
                                AND meta_value = "active"
                            )
                    )';

    if (isset($_GET['program'])) {
        $class = $_GET['program'];

        $where = ['classes', "%$class%"];

        $result = $wpdb->get_results(
            $wpdb->prepare( $sql, $where)
        );
        
    } else if (isset($_GET['slots'])) {
        $slots = $_GET['slots'];

        $results = [];

        foreach($slots as $slot) {
            $where = ['slots', "%$slot%"];
            $results = array_merge($results, $wpdb->get_results($wpdb->prepare($sql, $where)));
        }

        $result = $results;
    } else {
        $result = 0;
    }

    echo json_encode($result);

	die();
}

?>