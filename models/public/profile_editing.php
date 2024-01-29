<?php

class ProfileEditing
{

    public $carrier_options;
    public $suffix_options;
    public $gender_options;

    public function __construct($carrier_options, $suffix_options, $gender_options)
    {
        $this->carrier_options = $carrier_options;
        $this->suffix_options = $suffix_options;
        $this->gender_options = $gender_options;
        add_shortcode( 'gy_profile_editing_form', array($this, 'gy_profile_editing_form_shortcode') );
    }

    public function gy_profile_editing_form_shortcode()
    {
        if (!is_user_logged_in()) {

            // The user is not logged in. Redirect them to the home page.
            wp_redirect(home_url());
            exit;
        } else {

            if (isset($_POST['my_account_setup_id']) && isset($_POST['my_account_setup_pm'])) {
                $setup_id = $_POST['my_account_setup_id'];
                $setup_pm = $_POST['my_account_setup_pm'];
                $is_invalid = save_ach_method($setup_id, $setup_pm);

                if (!empty($is_invalid)) {
                    echo '<script>
                        document.addEventListener("DOMContentLoaded", function() {
                            document.querySelector("#ach_warning").classList.remove("hidden")
                            document.querySelector("#ach_warning").textContent = "'.$is_invalid[0].'"
                        })
                    </script>';
                } else {
                    wp_redirect('/my-account/payment-methods/', 301);
                }
            }

            $user_id = get_current_user_id();

            $meta = get_user_meta($user_id);
            $user = get_userdata($user_id);

            $multiaccount_number = get_user_meta($user_id, 'smuac_multiaccounts_number', true);
            $multiaccount_list = get_user_meta($user_id, 'smuac_multiaccounts_list', true);
            $multiaccounts = explode(',', $multiaccount_list);
            $child_name = array();

            foreach ($multiaccounts as $multiaccount) {

                $child_user = get_user_by('id', $multiaccount);

                if ($child_user) {
                    $child_first_name = get_user_meta($multiaccount, 'first_name', true);
                    $child_last_name = get_user_meta($multiaccount, 'last_name', true);
    
                    array_push($child_name, array(
                        'child_first_name' => $child_first_name,
                        'child_last_name' => $child_last_name,
                        'child_id' => $multiaccount,
                    ));
                }

            }

            require GY_CRM_PLUGIN_DIR . 'views/templates/public/profile_editing/edit_container.php';
        }
    }
}

?> 
