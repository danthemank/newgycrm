<?php
add_action( 'wp_head', 'get_modal_child' );
function get_modal_child() { ?>
    <script type="text/javascript" >
    jQuery(document).ready(function($) {

        $('.child').on('click', function() {

            let childId = $(this).data('id')
            let parentId = $('#user_id').data('id')

            $.ajax({
                    url: <?php echo '"'.admin_url( 'admin-ajax.php' ).'"'; ?>,
                    data : {action: "get_child_edit", 
                            'child_id' : childId,
                            'parent_id' : parentId
                },
                success: function(response) {
                    let child = JSON.parse(response)

                    // console.log(child);

                    if (!child.notice) {
                        $('#child_id').attr('value', child.user.data.ID)
                        
                        $('#child_first_name').attr('value', child.meta.first_name)
                        $('#child_last_name').attr('value', child.meta.last_name)
                        $('#child_login').attr('value', child.user.data.user_login)
                        $('#child_email').attr('value', child.user.data.user_email)
                        $('#child_login').attr('value', child.user.data.nickname)
                        
                        $('#child_middle_name').attr('value', child.meta.child_middle_name)
                        
                        $('#suffix option').each(function() {
                            if ($(this).val() == child.meta.suffix) {
                                $(this).attr('selected', true)
                            }
                        })
                        
                        $('#preferred_name').attr('value', child.meta.preferred_name)
                        
                        $('#gender option').each(function() {
                            if ($(this).val() == child.meta.gender) {
                                $(this).attr('selected', true)
                            }
                        })

                        $('#cell_phone').attr('value', child.meta.cell_phone)
                        $('#child_birth').attr('value', child.meta.child_birth)
                        $('#team_level').attr('value', child.meta.team_level)
                        
                        $('#guardian_first_name_1').attr('value', child.meta.guardian_first_name_1)
                        $('#guardian_last_name_1').attr('value', child.meta.guardian_last_name_1)
                        $('#guardian_home_phone_1').attr('value', child.meta.guardian_home_phone_1)
                        $('#guardian_work_phone_1').attr('value', child.meta.guardian_work_phone_1)
                        $('#guardian_mobile_phone_1').attr('value', child.meta.guardian_mobile_phone_1)
                        
                        $('#guardian_first_name_2').attr('value', child.meta.guardian_last_name_2)
                        $('#guardian_last_name_2').attr('value', child.meta.guardian_last_name_2)
                        $('#guardian_home_phone_2').attr('value', child.meta.guardian_home_phone_2)
                        $('#guardian_work_phone_2').attr('value', child.meta.guardian_work_phone_2)
                        $('#guardian_mobile_phone_2').attr('value', child.meta.guardian_mobile_phone_2)
                        
                        $('#insurance_carrier').attr('value', child.meta.insurance_carrier)
                        $('#insurance_phone').attr('value', child.meta.insurance_phone)
                        
                        $('#emergency_name_1').attr('value', child.meta.emergency_name_1)
                        $('#emergency_phone_1').attr('value', child.meta.emergency_phone_1)
                        $('#emergency_name_2').attr('value', child.meta.emergency_name_2)
                        $('#emergency_phone_2').attr('value', child.meta.emergency_phone_2)
                        
                        $('#medic_name').attr('value', child.meta.medic_name)
                        $('#medic_phone').attr('value', child.meta.medic_phone)
                        $('#medic_notes').text(child.meta.medic_notes)
                        $('#medication').text(child.meta.medication)
                    } else {
                        $('.child-details-editing form').detach()
                        $('.child-details-editing').append(child.notice)
                    }

                }
            });
        })
            
    });
    </script> 
    <?php
}

add_action("wp_ajax_get_child_edit", "get_child_edit");

function get_child_edit() {

	$child_id = $_GET["child_id"];

    $user = get_user_by('id', $child_id);
    $meta = get_user_meta($child_id);

    if (!empty($user)) {
        $result = json_encode(array(
            'meta' => $meta,
            'user' => $user,
        ));
    } else {
        $result = json_encode(array(
            'notice' => '<div class="notice notice-warning is-dismissible"><p>Error: Multiaccount doesn\'t exist.</p></div>'
        ));
    }

	echo $result;

	die();
}

?>