<?php
add_action( 'admin_footer', 'set_capabilities' );

function set_capabilities() { ?>
    <script type="text/javascript" >
    jQuery(document).ready(function($) {

        $('#edit_gycrm_roles').on('change', function() {
            let currentRole = $(this).val()

            $.ajax({
                url: <?php echo '"'.admin_url( 'admin-ajax.php' ).'"'; ?>,
                data : {action: "get_staff_capability", 
                        role: currentRole,
                },
                success: function(response) {
                    response = JSON.parse(response)

                    let html = `
                    <div style="margin-bottom: .5rem">
                        <input type="checkbox" value="1" class="gycrm-capability" `
                    html += response.read_customer_information ? 'checked' : ''
                    html += ` data-id="read_customer_information">
                        <label for="read_customer_information">Show Customer Information Page</label>
                    </div>
                    <div style="margin-bottom: .5rem">
                        <input type="checkbox" value="1" class="gycrm-capability" `
                    html += response.edit_customer_information ? 'checked' : ''
                    html += ` data-id="edit_customer_information">
                        <label for="edit_customer_information">Edit Customer Information Page</label>
                    </div>
                    <div style="margin-bottom: .5rem">
                        <input type="checkbox" value="1" class="gycrm-capability" `
                    html += response.edit_customer_information_parents ? 'checked' : ''
                    html += ` data-id="edit_customer_information_parents">
                        <label for="edit_customer_information_parents">Show parents in Customer Information Page</label>
                    </div>
                    <div style="margin-bottom: .5rem">
                        <input type="checkbox" value="1" class="gycrm-capability" `
                    html += response.edit_customer_information_children_parents ? 'checked' : ''
                    html += ` data-id="edit_customer_information_children_parents">
                        <label for="edit_customer_information_children_parents">Show children parents names in Customer Information Page</label>
                    </div>
                    <div style="margin-bottom: .5rem">
                        <input type="checkbox" value="1" class="gycrm-capability" `
                    html += response.edit_classes && response.read_private_classes && response.delete_classes ? 'checked' : ''
                    html += ` data-id="[&#34edit_classes&#34, &#34publish_classes&#34, &#34edit_others_classes&#34, &#34edit_published_classes&#34, &#34edit_private_classes&#34, &#34read_private_classes&#34, &#34delete_classes&#34, &#34delete_others_classes&#34, &#34delete_private_classes&#34, &#34delete_published_classes&#34]"> 
                        <label for="edit_classes">Show/create programs in Programs Page</label>
                    </div>
                    <div style="margin-bottom: .5rem">
                        <input type="checkbox" value="1" class="gycrm-capability" `
                        html += response.edit_pos_payments && response.edit_pos ? 'checked' : ''
                        html += ` data-id="[&#34edit_pos_payments&#34, &#34edit_pos&#34]"> 
                        <label for="edit_pos_payments">Create payments in Easy Point of Sale Page</label>
                    </div>
                    <div style="margin-bottom: .5rem">
                        <input type="checkbox" value="1" class="gycrm-capability" `
                        html += response.edit_email_templates && response.read_private_email_templates && response.delete_email_templates ? 'checked' : ''
                        html += ` data-id="[&#34edit_email_templates&#34, &#34publish_email_templates&#34, &#34edit_others_email_templates&#34, &#34edit_published_email_templates&#34, &#34edit_private_email_templates&#34, &#34read_private_email_templates&#34, &#34delete_email_templates&#34, &#34delete_others_email_templates&#34, &#34delete_private_email_templates&#34, &#34delete_published_email_templates&#34]"> 
                        <label for="edit_email_templates">Show Email Templates Page</label>
                    </div>
                    <div style="margin-bottom: .5rem">
                        <input type="checkbox" value="1" class="gycrm-capability" `
                        html += response.edit_attendance ? 'checked' : ''
                        html += ` data-id="edit_attendance"> 
                        <label for="edit_attendance">Show Attendance Page</label>
                    </div>
                    `

                    $('#gycrm_roles_capabilities').html(html)
                }
            });
            
        });

        $('body').on('change', '.gycrm-capability', function() {
            let isChecked = $(this).is(':checked')
            let capabilityName = $(this).data('id')
            let currentRole = $('#edit_gycrm_roles').val()

            if (currentRole == 'staff' ||
                currentRole == 'seniorstaff' ||
                currentRole == 'regularstaff' ||
                currentRole == 'juniorstaff' ||
                currentRole == 'entrystaff'
            ) {
                $.ajax({
                    url: <?php echo '"'.admin_url( 'admin-ajax.php' ).'"'; ?>,
                    data : {action: "save_staff_capability", 
                            is_checked: isChecked,
                            capability_name: capabilityName,
                            role: currentRole,
                    },
                    success: function(response) {
                        console.log(response);
                    }
                });
            }

            
        });

    })

    </script> 
    <?php
}


add_action("wp_ajax_get_staff_capability", "get_staff_capability");
add_action("wp_ajax_save_staff_capability", "save_staff_capability");


function get_staff_capability() {

    if (isset($_GET['role'])) {
        $role = $_GET['role'];
        $role_capabilities = get_role($role)->capabilities;

        echo json_encode($role_capabilities);
    }

	die();
}

function save_staff_capability() {

    if (isset($_GET['is_checked']) && isset($_GET['capability_name']) && isset($_GET['role'])) {
        $is_checked = $_GET['is_checked'];
        $capability_name = $_GET['capability_name'];
        $role_name = $_GET['role'];

        $role = get_role($role_name);
        if ($is_checked == 'true') {
            if (is_array($capability_name)) {
                foreach($capability_name as $capability) {
                    $role->add_cap($capability, true);
                }
            } else {
                $role->add_cap($capability_name, true);
            }
        } else {
            if (is_array($capability_name)) {
                foreach($capability_name as $capability) {
                    $role->remove_cap($capability);
                }
            } else {
                $role->remove_cap($capability_name);
            }
        }
    }

	die();
}

?>