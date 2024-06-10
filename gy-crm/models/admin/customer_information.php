<?php
// Wp-table Archive
require_once GY_CRM_PLUGIN_DIR . 'models/admin/plugin-list-table.php';

// Register Admin Menu
class get_customer_information
{
    public $slot_week;
    public $team_level;
    public $carrier_options;
    public function __construct($slot_week, $team_level, $carrier_options)
    {
        $this->slot_week = $slot_week;
        $this->team_level = $team_level;
        $this->carrier_options = $carrier_options;

        add_action('init', array($this, 'gy_register_athlete_tags_taxonomy'));
        add_action('admin_menu', array($this, 'gy_customer_information_page'));
        add_action( 'admin_footer', array($this, 'search_bar_dropdown') );
        add_action( 'wp_ajax_get_customer_list', array($this, 'get_customer_list') );
        add_action( 'wp_ajax_get_athlete_details', array($this, 'get_athlete_details') );
        add_action( 'wp_ajax_save_user_actions', array($this, 'save_user_actions') );
        add_action( 'wp_ajax_delete_user_action', array($this, 'delete_user_action') );
        add_action( 'wp_ajax_save_billing_note', array($this, 'save_billing_note') );

        new edit_user_info();
        new  noter_user();
    }

    public function save_billing_note() {
        if ($_GET['note'] && $_GET['user_id']) {
            $note = $_GET['note'];
            $user_id = $_GET['user_id'];

            $is_note = update_user_meta($user_id, 'gycrm_billing_note', $note);

            if ($is_note) {
                echo json_encode(1);
            } else {
                echo json_encode(0);
            }
        }

        die();
    }

    public function save_user_actions() {
        if ($_GET['user_id'] && $_GET['user_action']) {
            $user_id = $_GET['user_id'];
            $user_action = $_GET['user_action'];
            $name = $_GET['action_name'];

            $new_action = array('action' => $user_action, 'name' => $name);
            
            $actions = get_user_meta($user_id, 'action_required', true);

            if (!empty($actions)) {
                $is_action = false;

                foreach($actions as $action) {
                    if ($action['action'] == $user_action &&
                        $action['name'] == $name) {
                            $is_action = true;
                    }
                }

                if (!$is_action) {
                    array_push($actions, $new_action);
                    update_user_meta($user_id, 'action_required', $actions);
                    echo json_encode(array('key' => count($actions) - 1, 'user' => $user_id));
                }
            } else {
                update_user_meta($user_id, 'action_required', array($new_action));
                echo json_encode(array('key' => 0, 'user' => $user_id));
            }
        }

        die();
    }

    public function gy_register_athlete_tags_taxonomy() {
        $labels = array(
            'name' => _x('Athlete Tags', 'taxonomy general name', 'tag'),
            'singular_name' => __('Athlete Tag', 'taxonomy singular name'),
            'search_items' => __('Search Tags', 'tag'),
            'all_items' => __('All Tags', 'tag'),
            'parent_item' => null,
            'parent_item_colon' => null,
            'edit_item' => __('Edit Tag', 'tag'),
            'update_item' => __('Update Tag', 'tag'),
            'add_new_item' => __('Add New Tag', 'tag'),
            'new_item_name' => __('New Tag Name', 'tag'),
            'separate_items_with_commas' => __('Separate Tags with commas', 'tag'),
            'add_or_remove_items' => __('Add or remove Tags', 'tag'),
            'choose_from_most_used' => __('Choose from the most used Tags', 'tag'),
            'not_found' => __('No Tags Found', 'tag'),
            'menu_name' => __('Athlete Tags', 'tag'),
        );
    
        $args = array(
            'labels' => $labels,
            'public' => true,
            "show_ui" => true,
            "show_in_menu" => true,
            "show_in_nav_menus" => true,
            'capability_type' => 'post',
            'hierarchical' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'update_count_callback' => '_update_post_term_count',
            'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
            'query_var' => true,
            'menu_icon' => 'dashicons-welcome-widgets-menus',
            'rewrite' => array('slug' => 'Athlete Tags'),
            'capabilities' => array ('edit_customer_information_parents' => true),
        );

        register_taxonomy('athlete_tags', '', $args);

    }

    function gy_customer_information_page()
    {
        add_menu_page(
            'Customer Information',
            'Customer Information',
            'read_customer_information', 
            'user-information',
            array($this, 'gy_customer_information_list_callback'),
            'dashicons-admin-users',
            6 
        );

        add_submenu_page(
            'user-information',
            'Billing Account',
            'Billing Account',
            'edit_customer_information_parents',
            'user-information-billing',
            array($this, 'gy_customer_information_billing_callback'),
        );

        add_submenu_page(
            'user-information',
            'Athletes',
            'Athletes',
            'read_customer_information',
            'user-information-children',
            array($this, 'gy_customer_information_children_callback'),
        );

        add_submenu_page(
            '',
            'Edit Information',
            'Edit Information',
            'read_customer_information',
            'user-information-edit',
            array($this, 'gy_customer_information_edit_callback'),
        );

        add_submenu_page(
            'user-information',
            'Actions',
            'Actions',
            'read_customer_information',
            'user-information-actions',
            array($this, 'gy_customer_actions_callback'),
        );

        add_submenu_page(
            'user-information',
            'Athlete Tags',
            'Athlete Tags',
            'edit_customer_information_parents',
            'edit-tags.php?taxonomy=athlete_tags',
            null,
        );
    }

    public function gy_customer_actions_callback() {
        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/customer_information/customer-actions.php';
    }
    public static function get_customer_information_capability($capability_name) {
        $is_capable = false;
        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;

        foreach ($user_roles as $role) {
            $role_object = get_role($role);
            $role_capabilities = $role_object->capabilities;

            if (array_key_exists($capability_name, $role_capabilities)) {
                $is_capable = true;
            }
        }

        return $is_capable;
    }

    function gy_customer_information_billing_callback()
    {
        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/customer_information/parent.php';
    }

    function gy_customer_information_list_callback()
    {
        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/customer_information/all_table.php';
    }

    public function delete_user_action() {
        if (isset($_GET['user_id']) && isset($_GET['user_action'])) {
            $user_id = $_GET['user_id'];
            $user_action = $_GET['user_action'];

            $actions = get_user_meta($user_id, 'action_required', true);    
            unset($actions[$user_action]);

            $actions = update_user_meta($user_id, 'action_required', $actions);

            echo json_encode(1);
        }

        die();
    }

    public function get_user_actions_list($id) {
        $actions = get_user_meta($id, 'action_required', true);
        $html = '';

        if (!empty($actions) && is_array($actions)) {
            if (count($actions) > 0) {
                foreach($actions as $key => $action) {
                    $html .= '<li class="flex-container" id="action_'.$key.'"><div>"'. $action['action'] .'"'. (!empty($action['name']) ? ' — '.$action['name'] : '') .'  </div><button type="button" data-user="'.$_GET['user'].'" data-id="'.$key.'" class="delete-action-item-icon"></button></li>';
                }
            }
        }

        return $html;
    }

    public function get_customer_actions() {
        global $wpdb;

        if (isset($_GET['t']) && $_GET['t'] !== 'b') {
            $account_type = 'smuac_account_parent';

            if ($_GET['t'] == 'a') {
                $join = 'LEFT';
            } else {
                $join = '';
            }

            $sql = 'SELECT ID, u2.meta_value AS first_name, u3.meta_value AS last_name, u1.meta_value AS actions, u4.meta_value AS tags
                    FROM wp_users
                    JOIN wp_usermeta u1
                        ON u1.user_id = ID
                        AND u1.meta_key = "action_required"
                        AND u1.meta_value != "None"
                        AND u1.user_id IN (
                                SELECT user_id FROM wp_usermeta WHERE meta_key = "'.$account_type.'"
                            )   
                    JOIN wp_usermeta u2
                        ON ID = u2.user_id
                        AND u2.meta_key = "first_name"
                    JOIN wp_usermeta u3
                        ON ID = u3.user_id
                        AND u3.meta_key = "last_name"
                    '.$join.' JOIN wp_usermeta u4
                        ON ID = u4.user_id
                        AND u4.meta_key = "athlete_tags"';
        } else {
            $account_type = 'smuac_multiaccounts_list'; 

            $sql = 'SELECT ID, u2.meta_value AS first_name, u3.meta_value AS last_name, u1.meta_value AS actions, user_email
                    FROM wp_users
                    JOIN wp_usermeta u1
                        ON u1.user_id = ID
                        AND u1.meta_value != "None"
                        AND u1.user_id IN (
                                SELECT user_id FROM wp_usermeta WHERE meta_key = "'.$account_type.'"
                            )
                    JOIN wp_usermeta u2
                        ON u1.user_id = u2.user_id
                    JOIN wp_usermeta u3
                        ON u2.user_id = u3.user_id
                        AND u3.meta_key = "last_name"
                        AND u2.meta_key = "first_name"
                        AND u1.meta_key = "action_required"';
        }

        if (isset($_GET['ord'])) {
            $order = $_GET['ord'];
        } else {
            $order = 'ASC';
        }

        if (isset($_GET['by']) && $_GET['by'] !== 'billing_phone' && $_GET['by'] !== 'action') {
            $by = $_GET['by'];
        } else {
            $by = 'first_name';
        }

        if (isset($_GET['action'])) {
            $sql .= ' AND u1.meta_value LIKE "%'.$_GET['action'].'%"';
        }

        $sql .= ' ORDER BY '.$by.' '.$order;

        $results = $wpdb->get_results($sql);

        $html = '';

        if (!empty($results)) {
            foreach($results as $key => $res) {

            if (isset($_GET['t']) && $_GET['t'] == 'at') {
                if (empty($res->tags)) {
                    continue;
                }
            }

            $actions = unserialize($res->actions);
                if (is_array($actions) && count($actions) > 0) {
                    if (isset($_GET['t']) && $_GET['t'] !== 'b') {
                        $html .= '<tr>
                                <td><a target="_blank" href="/wp-admin/admin.php?page=user-information-edit&user='.$res->ID.'&child=yes">'.$res->first_name.'</a></td>
                                <td><a target="_blank" href="/wp-admin/admin.php?page=user-information-edit&user='.$res->ID.'&child=yes">'.$res->last_name.'</a></td>
                                <td><select class="actions-list">';
                                    foreach($actions as $action) {
                                        $html .= '<option>"'. $action['action'] .'"'. (!empty($action['name']) ? ' — '.$action['name'] : '') .'</option>';
                                    }
                                $html .= '</select></td>
                                    <td>';

                                if (!empty($res->tags)) {

                                    $html .= '<select>';
                                    $tags = explode(',', $res->tags);

                                    foreach($tags as $tag) {
                                        if (!empty($tag)) {
                                            $term = get_term($tag);
                                            $html .= '<option>'.$term->name.'</option>';
                                        }
                                    }
                                    $html .= '</select>';
                                }

                            $html .= '</td>
                                        </tr>';
                    } else {
                        $phone = get_user_meta($res->ID, 'billing_phone', true);
                        $html .= '<tr>
                                <td><a target="_blank" href="/wp-admin/admin.php?page=user-information-edit&user='.$res->ID.'&child=no">'.$res->first_name.'</a></td>
                                <td><a target="_blank" href="/wp-admin/admin.php?page=user-information-edit&user='.$res->ID.'&child=no">'.$res->last_name.'</a></td>
                                <td><select class="actions-list">';
                                foreach($actions as $action) {
                                    $html .= '<option>"'. $action['action'] .'"'. (!empty($action['name']) ? ' — '.$action['name'] : '') .'</option>';
                                }
                                
                        $html .= '</select></td>
                                    <td><a href="mailto:'.$res->user_email.'">'.$res->user_email.'</a></td>
                                            <td class="phone-number"><a href="tel:'.$phone.'">'.$phone.'</a></td>
                                        </tr>';
                    }
                }
            }
        } else {
            $html .= '<tr>
                    <td colspan="4">No items</td>
                </tr>';
        }

        return $html;

    }

    public function gy_customer_information_edit_callback()
    {
        global $wpdb; 

        $user_data = get_user_by('ID', $_GET["user"]);
        $user_meta =  get_user_meta($_GET["user"]);
        $user_meta['user_email'] = $user_data->user_email;
        $user_meta['password'] = $user_data->user_pass;
        $user_meta['user_login'] = $user_data->user_login;

        $id = $_GET['user'];
        $payment_methods = $this->get_customer_payment_methods($_GET['user']);

        require GY_CRM_PLUGIN_DIR . 'views/js/easy-pos.php';
        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/customer_information/edit.php';
    }

    public function get_all_classes() {
        $slot_ids = get_user_meta($_GET["user"], 'slots', true);
        $slot_ids = is_array($slot_ids[0]) ? $slot_ids[0] : $slot_ids;

        $classes = get_posts(array(
            'post_type' => 'class',
            'posts_per_page' => -1,
            'order' => 'ASC',
            'orderby' => 'title',
        ));

        foreach($classes as $class) {
            foreach($this->slot_week as $key => $day) {
                $class_slot = get_class_days($key, $day, $class);

                if (!empty($class_slot)) {
                    $classes_slots[] = $class_slot;
                }
            }
        }

        if (!empty($classes_slots)) {
            $output = '<option value="">Select Option</option>';
    
            foreach($classes_slots as $class) {
                if (!empty($slot_ids)) {
                    if (in_array($class[0]['slot_id'], $slot_ids)) {
                        $output .= '<option data-meta="'.$class[0]['slot_id'].'" value="'.$class[0]['id'].'" selected>'.$class[0]['class'].' '. $class[0]['start_time'].'</option>';
                        } else {
                            $output .= '<option data-meta="'.$class[0]['slot_id'].'" value="'.$class[0]['id'].'">'.$class[0]['class'].' '. $class[0]['start_time'].'</option>';
                        }
                    } else {
                        $output .= '<option data-meta="'.$class[0]['slot_id'].'" value="'.$class[0]['id'].'">'.$class[0]['class'].' '. $class[0]['start_time'].'</option>';
                    }
            }
        }

        return $output;
    }

    public function get_athlete_details() {
        if ($_GET['user_id']) {
            $user_id = $_GET['user_id'];

            $first_name = get_user_meta($user_id, 'first_name', true);
            $last_name = get_user_meta($user_id, 'last_name', true);
            $birth = get_user_meta($user_id, 'child_birth', true);
            $status = get_user_meta($user_id, 'status_program_participant', true);
            $start_date = get_user_meta($user_id, 'start_date', true);
            $reg_date = get_user_meta($user_id, 'due_registration_date', true);
            $actions = get_user_meta($user_id, 'action_required', true);
            if (isset($birth)) {
                $unix = strtotime($birth);
                $actual_date = new DateTime();
                $difference = $actual_date->getTimestamp() - $unix;
                $age = $difference / (60 * 60 * 24 * 365);
                $age = intval($age);   
            }
            $enrolled_classes = $this->get_athletes_classes($user_id, ['option' => 1]);
            $attendance = get_attendance_history($user_id);

            $html = '';

            if (!empty($actions)) {
                foreach($actions as $action) {
                    $role_name = $action['name'] ? wp_roles()->get_names()[ $action['name'] ] : '';
                    $html .= '<option>"'. $action['action'] .'" '. $role_name. ' ' . $action['date'].'</option>';
                }
            } else {
                $html .= '<option>None</option>';
            }

            echo json_encode(array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'age' => 'Age '.$age,
                'status' => $status,
                'start_date' => $start_date,
                'reg_date' => $reg_date,
                'enrolled_classes' => $enrolled_classes,
                'attendance' => $attendance,
                'action_required' => $html,
            ));
        }

        die();
    }

    public function get_athletes_classes($user_id, $type) {

        $classes = get_user_meta($user_id, 'classes_slots', true);
        $html = '';

        if (!empty($classes)) {
            
            if (isset($classes[0])) {
                $classes = $classes[0];
            }

            foreach($classes as $key => $class) {
                $post = get_post($key);
                $slot_ids = get_post_meta($key, 'slot_ids', true);
                
                foreach ($class as $slots) {
                    if (!empty($slot_ids)) {
                        $slot_number = array_search($slots, $slot_ids);
                        if (isset($type['option'])) {
                            $html .= '<option>'.$post->post_title . ' SLOT #'.$slot_number+1 .'</option>';
                        } else {
                            $html .= '<li>'.$post->post_title . ' SLOT #'.$slot_number+1 .' <button type="button" data-class="'.$post->ID.'" data-slot="'.$slots.'" data-modal="#confirm_delete_class" class="edit-btn delete-class-item-icon"></button></li>';
                        }
                    }
                }
            }
        } else {
            if (isset($type['option'])) {
                $html .= '<option>Empty</option>';
            }
        }

        return $html;

    }

    public function get_all_athletes() {
        global $wpdb;

        $sql = 'SELECT ID, us2.meta_value AS first_name, us3.meta_value AS last_name FROM wp_users 
                JOIN `wp_usermeta` us1 
                    ON us1.user_id = ID
                JOIN wp_usermeta us2 
                    ON us2.user_id = ID 
                JOIN wp_usermeta us3
                    ON us3.user_id = ID 
                AND us2.meta_key = "first_name"
                AND us3.meta_key = "last_name"
                AND us1.meta_key = "smuac_account_type"';

        $results = $wpdb->get_results($wpdb->prepare($sql));

        $html = '';

        foreach ($results as $res) {
            $html .= '<option value="'.$res->ID.'">'.$res->first_name.' '.$res->last_name.'</option>';
        }

        return $html;
    }

    public function get_customer_payment_methods($customer_id) {
        $cards = [];
        $stripe_cus = get_user_meta($customer_id, 'wp__stripe_customer_id', true);

        if (!empty($stripe_cus)) {
            $stripe = new \Stripe\StripeClient(STRIPE_TEST_KEY);
            
            $stripe_pm = $stripe->paymentMethods->all([
                'customer' => $stripe_cus,
            ]);

            if (!empty($stripe_pm->data)) {

                foreach ($stripe_pm->data as $card) {
                    if (isset($card['card']->last4)) {
                        $cards[] = array('id' => $card->id,
                        'last4' => $card['card']->last4,
                        'exp_month' => $card['card']->exp_month,
                        'exp_year' => $card['card']->exp_year);
                    }

                    if (isset($card['us_bank_account']->last4)) {
                        $cards[] = array('id' => $card->id,
                        'last4' => $card['us_bank_account']->last4,
                        'exp_month' => isset($card['us_bank_account']->exp_month) ? $card['us_bank_account']->exp_month : '',
                        'exp_year' => isset($card['us_bank_account']->exp_year) ? $card['us_bank_account']->exp_month : '');
                    }
                }
            }
        }
        return $cards;
    }

    function gy_customer_information_children_callback()
    {

        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/customer_information/children.php';
    }

    public function search_bar_dropdown() {
        ?>

        <script>
            (function ($) {
                $(document).ready(function () {

                $('p.search-box').append('<ul class="keyup-dropdown"></ul>')

                $('#search_id-search-input').on('keyup', function() {
                        $.ajax({
                            url: '<?php echo admin_url("admin-ajax.php"); ?>',
                            type: 'POST',
                            data: {
                                action: 'get_customer_list',
                                input: $(this).val()
                            },
                            success: function (response) {
                                console.log(response);
                                response = JSON.parse(response)

                                let html = ''
                                $.each(response, function(i, el) {
                                    html += `
                                        <li><input type="submit" name="s" value="${el.user_login}"/></li>
                                    `
                                })

                                if (html == '') {
                                    $(".keyup-dropdown").hide();
                                } else {
                                    $(".keyup-dropdown").show();
                                    $(".keyup-dropdown").html(html);
                                }


                            }
                        });
                })

                $('body').on('click', function(e) {
                    if ($(event.target).is(".keyup-dropdown")) {
                        return;
                    }
                    $(".keyup-dropdown").hide();
                })

                $('#search_id-search-input').on('click', function() {
                    if ($(".keyup-dropdown").children().length >= 1) {
                        $(".keyup-dropdown").show();
                    }
                })
            })
        })(jQuery);

        </script>

        <?php
    }

    public function get_customer_list() {
        if (isset($_POST['input'])) {
            $search = $_POST['input'];

            $users = get_users();

            foreach($users as $user) {
                $children = get_user_meta($user->ID, 'smuac_multiaccounts_list', true);
                if (!empty($children)) {

                $parent_first_name = get_user_meta($user->ID, 'first_name', true);
                $parent_last_name = get_user_meta($user->ID, 'last_name', true);

                if (str_contains($parent_first_name, $search) || str_contains($parent_last_name, $search)) {
                    $data = array(
                        'user_login' => $parent_first_name.' '.$parent_last_name
                    );

                    $datas[$user->ID] = $data;
                }

                $children = explode(',', $children);
                    foreach($children as $child) {
                        if (!empty($child)) {
                            $first_name = get_user_meta($child, 'first_name', true);
                            $last_name = get_user_meta($child, 'last_name', true);
    
                            if (str_contains($first_name, $search) || str_contains($last_name, $search)) {
                                $data = array(
                                    'user_login' => $parent_first_name.' '.$parent_last_name
                                );
    
                                $datas[$user->ID] = $data;
                            }
                        }
                    }
                }
            }

            echo json_encode($datas);
        }

        die();
    } 
}
// Create a WP Table
class Parent_table extends plugin_list_table
{
    // define $table_data property
    private $table_data;

    // Get table data
    private function get_table_data($search = '', $tag = '')
    {

        global $wpdb;

        $where = !empty($search) ? "%$search%" : "%%";

        $datas = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT
                u.ID,
                first_name.meta_value as first_name, last_name.meta_value as last_name,
                u.user_email,
                phone.meta_value AS phone
            FROM (
                SELECT DISTINCT wu.ID, user_login, user_email
                FROM wp_users wu
                WHERE id NOT IN (
                    SELECT user_id
                    FROM wp_usermeta
                    WHERE meta_key LIKE 'smuac_account_parent'
                )
                AND ID IN (
                    SELECT user_id
                    FROM wp_usermeta
                    WHERE meta_value LIKE %s
                )
            ) u
            LEFT JOIN wp_usermeta AS first_name ON u.ID = first_name.user_id AND first_name.meta_key = 'first_name'
            LEFT JOIN wp_usermeta AS last_name ON u.ID = last_name.user_id AND last_name.meta_key = 'last_name'
            LEFT JOIN wp_usermeta AS phone ON u.ID = phone.user_id AND phone.meta_key = 'billing_phone';",
            $where,
            $where
        )); 
        $decode = [];
        foreach ($datas as $key => $data) {
            $decode[] = json_decode(json_encode($data, true), true);
        };
        return $decode;
    }
    // Define table columns
    function get_columns()
    {
        $columns = array(
            'first_name'   => 'First Name',
            'last_name'    => 'Last Name',
            'user_email'   => 'Email',
            'phone'     => 'Phone',
        );
        return $columns;
    }
    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'first_name':
            case 'last_name':
            case 'user_email':
                        return "<a href='edit.php?post_type=email_template&page=send-emails&user_id={$item['ID']}'>$item[$column_name]</a>";
            case 'phone':
                return "<a href='tel:+1{$item['phone']}'>$item[$column_name]</a>";
            default:
                return $item[$column_name];
        }
    }
    protected function get_sortable_columns()
    {
        $sortable_columns = array(
            'first_name'  => array('first_name', true),
            'last_name'  => array('last_name', true),
            'user_email'  => array('user_email', true),
        );
        return $sortable_columns;
    }
    // Sorting function
    function usort_reorder($a, $b)
    {
        // If no sort, default to user_login
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : "first_name";

        // If no order, default to asc
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';

        // Determine sort order
        $result = strcmp($a[$orderby], $b[$orderby]);

        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
    }


    // Adding action links to column
    function column_first_name($item)
    {
        $actions = array(
            'edit'      => sprintf("<a href='?page=user-information-edit&user=$item[ID]&child=no'>" . __('Edit', 'supporthost-admin-table') . '</a>', $_REQUEST['page'], 'edit', $item['ID']),
        );

        return sprintf('%1$s %2$s', "<a href='/wp-admin/admin.php?page=user-information-edit&user=$item[ID]&child=no' style='cursor:pointer'> $item[first_name]</a>", $this->row_actions($actions));
    }

    function column_last_name($item)
    {
        $actions = array(
            'edit'      => sprintf("<a href='?page=user-information-edit&user=$item[ID]&child=no'>" . __('Edit', 'supporthost-admin-table') . '</a>', $_REQUEST['page'], 'edit', $item['ID']),
        );

        return sprintf('%1$s %2$s', "<a href='/wp-admin/admin.php?page=user-information-edit&user=$item[ID]&child=no' style='cursor:pointer'> $item[last_name]</a>", $this->row_actions($actions));
    }

    function prepare_items()
    {
        //data search
        if (isset($_POST['s']) || isset($_GET['search'])) {
            $this->table_data = $this->get_table_data(isset($_POST['s']) ? $_POST['s'] : $_GET['search']);
        } else {
            $this->table_data = $this->get_table_data();
        }
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        usort($this->table_data, array(&$this, 'usort_reorder'));

        // pagination 
        $per_page = $this->get_items_per_page('elements_per_page', 20);
        $current_page = $this->get_pagenum();
        $total_items = count($this->table_data);
        $this->table_data = array_slice($this->table_data, (($current_page - 1) * $per_page), $per_page);
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total number of items
            'per_page'    => $per_page, // items to show on a page
            'total_pages' => ceil($total_items / $per_page) // use ceil to round up
        ));

        $this->items = $this->table_data;
    }
}


// Create a WP Table for children
class Children_table extends plugin_list_table
{
    // define $table_data property
    private $table_data;

    // Get table data
    private function get_table_data($search = '', $tag = '')
    {

        global $wpdb;

        $where = !empty($search) ? "%$search%" : "%%";

        $datas = $wpdb->get_results($wpdb->prepare(
            'SELECT DISTINCT
                wu.ID, 
                um2.meta_value AS parent_id,
                um3.meta_value AS first_name, 
                um7.meta_value AS last_name,
                wu.user_email,  
                CONCAT(um5.meta_value, " ", um8.meta_value) guardian_1,
                CONCAT(um6.meta_value, " ", um9.meta_value) guardian_2,
                CONCAT(um4.meta_value, " ", um0.meta_value) parent_name
                FROM 
                    wp_users wu 
                INNER JOIN wp_usermeta um2 ON (wu.ID = um2.user_id AND um2.meta_key ="smuac_account_parent")
                LEFT JOIN wp_usermeta um3 ON (wu.ID = um3.user_id AND um3.meta_key = "first_name")
                LEFT JOIN wp_usermeta um4 ON (um2.meta_value = um4.user_id AND um4.meta_key = "first_name")
                LEFT JOIN wp_usermeta um0 ON (um2.meta_value = um0.user_id AND um0.meta_key = "last_name")
                LEFT JOIN wp_usermeta um5 ON (wu.ID = um5.user_id AND um5.meta_key = "guardian_first_name_1") 
                LEFT JOIN wp_usermeta um6 ON (wu.ID = um6.user_id AND um6.meta_key = "guardian_first_name_2") 
                LEFT JOIN wp_usermeta um7 ON (wu.ID = um7.user_id AND um7.meta_key = "last_name") 
                LEFT JOIN wp_usermeta um8 ON (wu.ID = um8.user_id AND um8.meta_key = "guardian_last_name_1")
                LEFT JOIN wp_usermeta um9 ON (wu.ID = um9.user_id AND um9.meta_key = "guardian_last_name_2")
                '.(!empty($tag) ? ' JOIN wp_usermeta um10 ON (wu.ID = um10.user_id AND um10.meta_key = "athlete_tags" AND um10.meta_value LIKE "%'.$tag.'%") ' : '').'
                WHERE um3.meta_value LIKE %s OR um7.meta_value LIKE %s OR wu.user_email LIKE %s',
            $where,
            $where,
            $where
        ));

        $decode = [];
        foreach ($datas as $key => $data) {
            $decode[] = json_decode(json_encode($data, true), true);
        };
        return $decode;
    }
    
    // Define table columns
    function get_columns()
    {
        $is_capable = false;
        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;

        foreach ($user_roles as $role) {
            $role_object = get_role($role);
            $role_capabilities = $role_object->capabilities;
            
            if (array_key_exists('edit_customer_information_children_parents', $role_capabilities)) {
                $is_capable = true;
            }
        }

        if ($is_capable) {
            $columns = array(
                'first_name'  => 'Name',
                'last_name'   => 'Last name',
                'guardian_1'  => 'Guardian 1',
                'guardian_2'  => 'Guardian   2',
                'parent_name' => 'Parent Name'
            );
        } else {
            $columns = array(
                'first_name' => 'Name',
                'last_name'  => 'Last name',
                'parent_name' => 'Parent Name'
            );
        }
        return $columns;
    }
    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'first_name':
            case 'last_name':
            case 'parent_name':
            case 'dad':
            default:
                return $item[$column_name];
        }
    }
    protected function get_sortable_columns()
    {
        $sortable_columns = array(
            'first_name'  => array('first_name', true),
            'last_name'  => array('last_name', true),
            'user_email'  => array('user_email', true),
            'parent_name'  => array('parent_name', true),
        );
        return $sortable_columns;
    }
    // Sorting function
    function usort_reorder($a, $b)
    {
        // If no sort, default to first_name
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : "first_name";

        // If no order, default to asc
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';

        // Determine sort order
        $result = strcmp($a[$orderby], $b[$orderby]);

        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
    }

    function prepare_items()
    {
        //data search
        if (isset($_POST['s']) || isset($_GET['search'])) {
            $this->table_data = $this->get_table_data(isset($_POST['s']) ? $_POST['s'] : $_GET['search']);
        } else if (isset($_GET['tag'])) {
            $this->table_data = $this->get_table_data('', $_GET['tag']);
        } else {
            $this->table_data = $this->get_table_data();
        }

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        usort($this->table_data, array(&$this, 'usort_reorder'));

        /* pagination */
        $per_page = $this->get_items_per_page('elements_per_page', 20);
        $current_page = $this->get_pagenum();
        $total_items = count($this->table_data);

        $this->table_data = array_slice($this->table_data, (($current_page - 1) * $per_page), $per_page);

        $this->set_pagination_args(array(
            'total_items' => $total_items, // total number of items
            'per_page'    => $per_page, // items to show on a page
            'total_pages' => ceil($total_items / $per_page) // use ceil to round up
        ));

        $this->items = $this->table_data;
    }

    // Adding action links to column
    function column_first_name($item)
    {
        $actions = array(
            'edit'      => sprintf("<a href='?page=user-information-edit&user=$item[ID]&child=yes'>" . __('Edit', 'supporthost-admin-table') . '</a>', $_REQUEST['page'], 'edit', $item['ID']),
        );

        return sprintf('%1$s %2$s', "<a href='/wp-admin/admin.php?page=user-information-edit&user=$item[ID]&child=yes' style='cursor:pointer'> $item[first_name]</a>", $this->row_actions($actions));
    }

    // Adding action links to column
    function column_last_name($item)
    {
        $actions = array(
            'edit'      => sprintf("<a href='?page=user-information-edit&user=$item[ID]&child=yes'>" . __('Edit', 'supporthost-admin-table') . '</a>', $_REQUEST['page'], 'edit', $item['ID']),
        );

        return sprintf('%1$s %2$s', "<a href='/wp-admin/admin.php?page=user-information-edit&user=$item[ID]&child=yes' style='cursor:pointer'> $item[last_name]</a>", $this->row_actions($actions));
    }

    // Adding action links to column
    function column_parent_name($item)
    {
        $actions = array(
            'edit'      => sprintf("<a href='?page=user-information-edit&user=$item[parent_id]&child=yes'>" . __('Edit', 'supporthost-admin-table') . '</a>', $_REQUEST['page'], 'edit', $item['parent_id']),
        );

        return sprintf('%1$s %2$s', "<a href='/wp-admin/admin.php?page=user-information-edit&user=$item[parent_id]&child=no' style='cursor:pointer'> $item[parent_name]</a>", $this->row_actions($actions));
    }
}

class All_table extends plugin_list_table
{
    // define $table_data property
    private $table_data;

    // Get table data
    private function get_table_data($search = '')
    {

        global $wpdb;

        $where = !empty($search) ? "%$search%" : "%%";
        
        $is_capable = get_customer_information::get_customer_information_capability('edit_customer_information_parents');

        if ($is_capable) {
            $datas = $wpdb->get_results($wpdb->prepare(
                "SELECT DISTINCT
                    u.ID AS parent_id,
                    null as child_id,
                    first_name.meta_value AS first_name,
                    last_name.meta_value AS last_name,
                    u.user_email AS user_email
                FROM (
                    SELECT DISTINCT wu.ID, user_login, user_email
                    FROM wp_users wu
                    WHERE id NOT IN (
                        SELECT user_id
                        FROM wp_usermeta
                        WHERE meta_key LIKE 'smuac_account_parent'
                    )
                    AND ID IN (
                        SELECT user_id
                        FROM wp_usermeta
                        WHERE meta_value LIKE %s
                    )
                ) u
                LEFT JOIN wp_usermeta AS first_name ON u.ID = first_name.user_id AND first_name.meta_key = 'first_name'
                LEFT JOIN wp_usermeta AS last_name ON u.ID = last_name.user_id AND last_name.meta_key = 'last_name'
                LEFT JOIN wp_usermeta AS phone ON u.ID = phone.user_id AND phone.meta_key = 'billing_phone'
                UNION
                SELECT DISTINCT
                    null AS parent_id,
                    wu.ID AS child_id,
                    um3.meta_value AS first_name,
                    um7.meta_value AS last_name,
                    wu.user_email  AS user_email
                FROM wp_users wu
                INNER JOIN wp_usermeta um2 ON (wu.ID = um2.user_id AND um2.meta_key = 'smuac_account_parent')
                LEFT JOIN wp_usermeta um3 ON (wu.ID = um3.user_id AND um3.meta_key = 'first_name')
                LEFT JOIN wp_usermeta um4 ON (um2.meta_value = um4.user_id AND um4.meta_key = 'first_name')
                LEFT JOIN wp_usermeta um0 ON (um2.meta_value = um0.user_id AND um0.meta_key = 'last_name')
                LEFT JOIN wp_usermeta um5 ON (wu.ID = um5.user_id AND um5.meta_key = 'guardian_first_name_1')
                LEFT JOIN wp_usermeta um6 ON (wu.ID = um6.user_id AND um6.meta_key = 'guardian_first_name_2')
                LEFT JOIN wp_usermeta um7 ON (wu.ID = um7.user_id AND um7.meta_key = 'last_name')
                LEFT JOIN wp_usermeta um8 ON (wu.ID = um8.user_id AND um8.meta_key = 'guardian_last_name_1')
                LEFT JOIN wp_usermeta um9 ON (wu.ID = um9.user_id AND um9.meta_key = 'guardian_last_name_2')
                WHERE um3.meta_value LIKE %s OR um7.meta_value LIKE %s OR wu.user_email LIKE %s;",
                $where,
                $where,
                $where,
                $where
            )); 
        } else {
            $datas = $wpdb->get_results($wpdb->prepare(
                "SELECT DISTINCT
                    null AS parent_id,
                    wu.ID AS child_id,
                    um3.meta_value AS first_name,
                    um7.meta_value AS last_name,
                    wu.user_email  AS user_email
                FROM wp_users wu
                INNER JOIN wp_usermeta um2 ON (wu.ID = um2.user_id AND um2.meta_key = 'smuac_account_parent')
                LEFT JOIN wp_usermeta um3 ON (wu.ID = um3.user_id AND um3.meta_key = 'first_name')
                LEFT JOIN wp_usermeta um4 ON (um2.meta_value = um4.user_id AND um4.meta_key = 'first_name')
                LEFT JOIN wp_usermeta um0 ON (um2.meta_value = um0.user_id AND um0.meta_key = 'last_name')
                LEFT JOIN wp_usermeta um5 ON (wu.ID = um5.user_id AND um5.meta_key = 'guardian_first_name_1')
                LEFT JOIN wp_usermeta um6 ON (wu.ID = um6.user_id AND um6.meta_key = 'guardian_first_name_2')
                LEFT JOIN wp_usermeta um7 ON (wu.ID = um7.user_id AND um7.meta_key = 'last_name')
                LEFT JOIN wp_usermeta um8 ON (wu.ID = um8.user_id AND um8.meta_key = 'guardian_last_name_1')
                LEFT JOIN wp_usermeta um9 ON (wu.ID = um9.user_id AND um9.meta_key = 'guardian_last_name_2')
                WHERE um3.meta_value LIKE %s OR um7.meta_value LIKE %s OR wu.user_email LIKE %s;",
                $where,
                $where,
                $where,
            )); 

        }

        $decode = [];
        foreach ($datas as $key => $data) {
            $decode[] = json_decode(json_encode($data, true), true);
        };
        return $decode;
    }
    // Define table columns
    function get_columns()
    {
        $columns = array(
            'first_name'   => 'First Name',
            'last_name'    => 'Last Name',
            'user_email'   => 'Email'
        );
        return $columns;
    }
    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'first_name':
            case 'last_name':
            case 'user_email':
                if( !is_null($item['parent_id']) ) {

                    return "<a href='edit.php?post_type=email_template&page=send-emails&user_id={$item['parent_id']}'>$item[$column_name]</a>";

                } else  if( !is_null($item['child_id']) ) {
        
                    return "<a href='edit.php?post_type=email_template&page=send-emails&user_id={$item['child_id']}'>$item[$column_name]</a>";

                }
                /* case 'phone':
                    return "<a href='tel:+1{$item['phone']}'>$item[$column_name]</a>"; */
            default:
                return $item[$column_name];
        }
    }
    protected function get_sortable_columns()
    {
        $sortable_columns = array(
            'first_name'  => array('first_name', true),
            'last_name'  => array('last_name', true),
            'user_email'  => array('user_email', true),
        );
        return $sortable_columns;
    }
    // Sorting function
    function usort_reorder($a, $b)
    {
        // If no sort, default to user_login
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : "first_name";

        // If no order, default to asc
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';

        // Determine sort order
        $result = strcmp($a[$orderby], $b[$orderby]);

        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
    }


    // Adding action links to column
    function column_first_name($item)
    {
        if( !is_null($item['parent_id']) ) {

            $actions = array(
                'edit'      => sprintf("<a href='?page=user-information-edit&user=$item[parent_id]&child=no'>" . __('Edit', 'supporthost-admin-table') . '</a>', $_REQUEST['page'], 'edit', $item['parent_id']),
            );
            return sprintf('%1$s %2$s', "<a href='/wp-admin/admin.php?page=user-information-edit&user=$item[parent_id]&child=no' style='cursor:pointer'> $item[first_name]</a>", $this->row_actions($actions));

        } else  if( !is_null($item['child_id']) ) {

            $actions = array(
                'edit'      => sprintf("<a href='?page=user-information-edit&user=$item[child_id]&child=yes'>" . __('Edit', 'supporthost-admin-table') . '</a>', $_REQUEST['page'], 'edit', $item['child_id']),
            );
            return sprintf('%1$s %2$s', "<a href='/wp-admin/admin.php?page=user-information-edit&user=$item[child_id]&child=yes' style='cursor:pointer'> $item[first_name]</a>", $this->row_actions($actions));

        }
    }

    function column_last_name($item)
    {
        if( !is_null($item['parent_id']) ) {

            $actions = array(
                'edit'      => sprintf("<a href='?page=user-information-edit&user=$item[parent_id]&child=no'>" . __('Edit', 'supporthost-admin-table') . '</a>', $_REQUEST['page'], 'edit', $item['parent_id']),
            );
            return sprintf('%1$s %2$s', "<a href='/wp-admin/admin.php?page=user-information-edit&user=$item[parent_id]&child=no' style='cursor:pointer'> $item[last_name]</a>", $this->row_actions($actions));


        } else  if( !is_null($item['child_id']) ) {

            $actions = array(
                'edit'      => sprintf("<a href='?page=user-information-edit&user=$item[child_id]&child=yes'>" . __('Edit', 'supporthost-admin-table') . '</a>', $_REQUEST['page'], 'edit', $item['child_id']),
            );
            return sprintf('%1$s %2$s', "<a href='/wp-admin/admin.php?page=user-information-edit&user=$item[child_id]&child=yes' style='cursor:pointer'> $item[last_name]</a>", $this->row_actions($actions));

        }
    }

    function prepare_items()
    {
        //data search
        if (isset($_POST['s']) || isset($_GET['search'])) {
            $this->table_data = $this->get_table_data(isset($_POST['s']) ? $_POST['s'] : $_GET['search']);
        } else if ($_POST['custom-search-input']) {
            $this->table_data = $this->get_table_data($_POST['custom-search-input']);
        }else{
            $this->table_data = $this->get_table_data();
        }

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        usort($this->table_data, array(&$this, 'usort_reorder'));

        /* pagination */
        $per_page = $this->get_items_per_page('elements_per_page', 20);
        $current_page = $this->get_pagenum();
        $total_items = count($this->table_data);

        $this->table_data = array_slice($this->table_data, (($current_page - 1) * $per_page), $per_page);

        $this->set_pagination_args(array(
            'total_items' => $total_items, // total number of items
            'per_page'    => $per_page, // items to show on a page
            'total_pages' => ceil($total_items / $per_page) // use ceil to round up
        ));

        $this->items = $this->table_data;
    }
}

class edit_user_info
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'submit_form'));
    }

    public function submit_form()
    {

        if (isset($_POST['submit_data'])) {
            $this->update_billing_account();
        }

        if (isset($_POST['save_child'])) {
            $this->save_new_athlete();
        }

        if (isset($_POST['save_exist_athlete'])) {
            $this->associate_athlete();
        }

        if (isset($_POST['remove_card'])) {
            $this->remove_card();
        }

        if (isset($_POST['setup_cm_id']) && isset($_POST['setup_cm_pm'])) {
            $is_invalid = save_ach_method($_POST['setup_cm_id'], $_POST['setup_cm_pm']);

            if (!empty($is_invalid)) {
                echo '<script>
                        document.addEventListener("DOMContentLoaded", function() {
                            document.querySelector("#user_notes .global-error").classList.remove("hidden")
                            document.querySelector("#user_notes .global-error").textContent = "'.$is_invalid[0].'"
                        })
                    </script>';
            }
        }

        if (isset($_POST['stripeToken']) && isset($_POST['pm_nonce'])) {
            $is_invalid = $this->save_payment_method();

            if ($is_invalid) {
                ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        document.querySelector('.global-error').textContent = '<?= $is_invalid[0] ?>'
                        document.querySelector('.global-error').classList.remove('hidden')
                    });
                </script>
                <?php
            }
        }

        if (isset($_POST['submit_send_email'])) {
            $this->send_email();
        }

        if (isset($_POST['email_login_instructions'])) {
            $this->email_login_instructions();
        }
    }

    public function email_login_instructions() {
        $from = !empty(get_option('custom_note_from')) ? get_option('custom_note_from') : 'ca@gymnasticsofyork.com';
        $replyto = !empty(get_option('custom_note_replyto')) ? get_option('custom_note_replyto') : 'ca@gymnasticsofyork.com';
        $bcc  = !empty(get_option('custom_note_bcc')) ? get_option('custom_note_bcc') : 'ca@gymnasticsofyork.com';

        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: <'.$from.'>';
        $headers[] = 'Reply-To: <'.$replyto.'>';
        $headers[] = 'Bcc: '.$bcc;

        $user = get_user_by('id', $_GET['user']);

        $email = get_posts(array(
            'post_type' => 'email_template',
            'post_status' => 'publish',
            'title' => 'LogOn to Gymnastics of York',
            'orderby' => 'title',
            'order' => 'ASC',
        ));

        $message = $email[0]->post_content;
        $message = EmailTemplates::self_merge_tags($message, $user);
        $is_sent = wp_mail($user->user_email, $email[0]->post_title, $message, $headers);

        if ($is_sent) {
            $admin = wp_get_current_user();
            $comment_user = array(
                'comment_author' => $admin->display_name,
                'comment_content' => 'Email "'.$email[0]->post_title.'"  sent to '.$user->user_email,
                'user_id' => $user->ID,
                'comment_meta'         => array(
                    'is_customer_note'       => sanitize_text_field(1),
                    )
                );

            wp_insert_comment($comment_user);
        }
        
    }

    public function send_email() {
        $template_id = $_POST['send_email'];

        if (!empty($template_id)) {
            
            $from = !empty(get_option('custom_note_from')) ? get_option('custom_note_from') : 'ca@gymnasticsofyork.com';
            $replyto = !empty(get_option('custom_note_replyto')) ? get_option('custom_note_replyto') : 'ca@gymnasticsofyork.com';
            $bcc  = !empty(get_option('custom_note_bcc')) ? get_option('custom_note_bcc') : 'ca@gymnasticsofyork.com';
    
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
            $headers[] = 'From: <'.$from.'>';
            $headers[] = 'Reply-To: <'.$replyto.'>';
            $headers[] = 'Bcc: '.$bcc;

            $user = get_user_by('id', $_GET['user']);

            $email = get_post($template_id);

            switch($email->post_title) {
                case 'Subscription Renewal Invoice':
                    EmailTemplates::get_subscription_invoice($user->user_email, $email->post_content, $email->post_title, $headers, array('not_automatic' => 1, 'not_comment' => 1));
                break;  
                case 'No Card Saved on File':
                    $email_temp = get_posts(array(
                        'post_type' => 'email_template',
                        'post_status' => 'publish',
                        'title' => 'No Card Saved on File',
                        'orderby' => 'title',
                        'order' => 'ASC',
                    ));
                    $users_list = EmailTemplates::no_card_saved(array($user));
                break; 
                case 'No payment recorded':
                    $email_temp = get_posts(array(
                        'post_type' => 'email_template',
                        'post_status' => 'publish',
                        'title' => 'No Payment recorded',
                        'orderby' => 'title',
                        'order' => 'ASC',
                    ));
                    $users_list = EmailTemplates::no_payment_recorded(array($user), ['single' => 1]);
                break; 
                case 'Notice of Suspension of Enrollment':
                    $email_temp = get_posts(array(
                        'post_type' => 'email_template',
                        'post_status' => 'publish',
                        'title' => 'Notice of Suspension of Enrollment',
                        'orderby' => 'title',
                        'order' => 'ASC',
                    ));
                    $users_list = EmailTemplates::notice_suspension(array($user), ['single' => 1]);
                break; 
                case 'Suspension of Enrollment':
                    $email_temp = get_posts(array(
                        'post_type' => 'email_template',
                        'post_status' => 'publish',
                        'title' => 'Suspension of Enrollment',
                        'orderby' => 'title',
                        'order' => 'ASC',
                    ));
                    $users_list = EmailTemplates::suspension_enrollment(array($user), ['single' => 1]);
                break; 
                case 'One Month to go until Athletes\'s 10th Birthday':
                    $email_temp = get_posts(array(
                        'post_type' => 'email_template',
                        'post_status' => 'publish',
                        'title' => 'One month to go until his 10th birthday',
                        'orderby' => 'title',
                        'order' => 'ASC',
                    ));
                    $users_list = EmailTemplates::ten_years(array($user));
                break;
                default:
                    $message = $email->post_content;
                    $message = EmailTemplates::self_merge_tags($message, $user);
                
                    $is_sent = wp_mail($user->user_email, $email->post_title, $message, $headers);
                    if ($is_sent) {
                        $admin = wp_get_current_user();
                        $comment_user = array(
                            'comment_author' => $admin->display_name,
                            'comment_content' => 'Email "'.$email->post_title.'"  sent to '.$user->user_email,
                            'user_id' => $user->ID,
                            'comment_meta'         => array(
                                'is_customer_note'       => sanitize_text_field(1),
                                )
                            );
            
                        wp_insert_comment($comment_user);
                    }
                break; 

                if (isset($users_list)) {
                    $subject = $email_temp[0]->post_title;
                    $message = $email_temp[0]->post_content;
                    EmailTemplates::send_automated_email($subject, $message, $headers, $users_list);
                }
            }
        }
    }

    public function update_billing_account() {
        if (wp_verify_nonce($_POST['_wpnonce'], 'edit_user_info')) {
            // remove values for the post array
            $remove = ['_wpnonce', 'submit_data', 'password', '_wp_http_referer'];
            $values = array_diff_key($_POST, array_flip($remove));
            foreach ($values as $key => $value) {
                $this->update_userdata(sanitize_text_field($key), sanitize_text_field($value));
            }
        }
    }

    public function save_payment_method() {

        $stripe = new \Stripe\StripeClient(STRIPE_TEST_KEY);

        wp_verify_nonce($_POST['pm_nonce'], 'pm_nonce');

        $stripe_token = $_POST['stripeToken'];
        $stripe_cus_id = get_user_meta($_GET['user'], 'wp__stripe_customer_id', true);

        $is_invalid = [];

        
        if (!empty($stripe_cus_id)) {
            try {
                $stripe->customers->createSource($stripe_cus_id, ['source' => $stripe_token]);
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

        } else {
            $user = get_user_by('id', $_GET['user']);

            try {
                $customer = $stripe->customers->create([
                    'email' => $user->user_email,
                    'source' => $stripe_token
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

            
            if (!empty($is_invalid)) {
                return $is_invalid;
            }

            $stripe_cus_id = $customer->id;
            update_user_meta($_GET['user'], 'wp__stripe_customer_id', $stripe_cus_id);
        }

    }

    public function remove_card() {
        $stripe = new \Stripe\StripeClient(STRIPE_TEST_KEY);

        wp_verify_nonce($_POST['pm_nonce'], 'pm_nonce');
        $card_id = $_POST['remove_card'];
        $stripe_cus_id = get_user_meta($_GET['user'], 'wp__stripe_customer_id', true);

        if (!empty($stripe_cus_id) && !empty($card_id)) {
            $payment_method = $stripe->paymentMethods->retrieve(
                $card_id
            );

            if ($payment_method) {
                $stripe->paymentMethods->detach(
                    $card_id,
                    []
                );
            }
        }
    }

    public function associate_athlete() {
        $parent_user_id = $_GET['user'];
        $child_user_id = $_POST['add_athlete'];

        if (!empty($child_user_id)) {
            $multiaccounts_maximum_limit = 500;
            $current_multiaccounts_number = get_user_meta( $parent_user_id, 'smuac_multiaccounts_number', true );

            if ( null === $current_multiaccounts_number ) {
                $current_multiaccounts_number = 0;	
            }

            if ( intval( $current_multiaccounts_number ) < $multiaccounts_maximum_limit ) {
                $current_multiaccounts_number++;
                update_user_meta( $parent_user_id, 'smuac_multiaccounts_number', $current_multiaccounts_number );

                $current_multiaccounts_list = get_user_meta( $parent_user_id, 'smuac_multiaccounts_list', true );
                $current_multiaccounts_list = $current_multiaccounts_list . ',' . $child_user_id;
                update_user_meta( $parent_user_id, 'smuac_multiaccounts_list', $current_multiaccounts_list );
            }

            $customer_url = site_url('/wp-admin/admin.php?page=user-information-edit&user='.$parent_user_id.'&child=no');
            wp_redirect($customer_url); 
            exit;
        }
    }


    public function save_new_athlete() {
        global $wpdb;
        
        check_admin_referer('create_child');
        if (!empty($_POST['child_first_name']) && !empty($_POST['child_last_name']) && !empty($_POST['child_birth'])) {
                    
            $child_name = $_POST['child_first_name'];
            $child_lastname = $_POST['child_last_name'];
            $child_birth = $_POST['child_birth'];
            $gender = $_POST['gender'];

            $status_program_participant = $_POST['status_program_participant'];

            $parent_user_id              = $_GET['user'];
            $parent_user = get_user_by('id', $parent_user_id);

            $multiaccounts_maximum_limit = 500;

            $current_multiaccounts_number = get_user_meta( $parent_user_id, 'smuac_multiaccounts_number', true );

            if ( null === $current_multiaccounts_number ) {
                $current_multiaccounts_number = 0;	
            }

            if ( intval( $current_multiaccounts_number ) < $multiaccounts_maximum_limit ) {

                $date = date('mdYHis');
                $email_domain_extension = '@gymnasticsofyork.com';
                $parent_name = get_user_meta($parent_user_id, 'first_name', true) ;
                $parent_name = preg_replace('/[^a-zA-Z0-9._@-]/', '', $parent_name);
                $childemail = $parent_name.'_'.$child_name.'_'.$date.$email_domain_extension;

                $childusername = $child_name.$child_lastname;

                $validated_childusername = check_existing_athlete($childusername);

                $child_user_id = wc_create_new_customer( $childemail, $validated_childusername, $parent_user->user_pass);

                if ( ! ( is_wp_error( $child_user_id ) ) ) {
                    // no errors, proceed
                    // set user meta
                    update_user_meta( $child_user_id, 'first_name', $child_name );
                    update_user_meta( $child_user_id, 'last_name', $child_lastname );
                    update_user_meta( $child_user_id, 'child_birth', $child_birth );
                    update_user_meta( $child_user_id, 'smuac_account_type', 'multiaccount' );
                    update_user_meta( $child_user_id, 'smuac_account_type', 'multiaccount' );
                    update_user_meta( $child_user_id, 'smuac_account_parent', $parent_user_id );
                    update_user_meta( $child_user_id, 'smuac_account_name', $childusername );
                    update_user_meta( $child_user_id, 'smuac_account_phone', '' );
                    update_user_meta( $child_user_id, 'smuac_account_job_title', '' );
                    update_user_meta( $child_user_id, 'smuac_account_permission_buy', '' ); 
                    update_user_meta( $child_user_id, 'smuac_account_permission_view_orders', '' ); 
                    update_user_meta( $child_user_id, 'smuac_account_permission_view_bundles', '' ); 
                    update_user_meta( $child_user_id, 'smuac_account_permission_view_discussions', ''); 
                    update_user_meta( $child_user_id, 'smuac_account_permission_view_lists', '' );
                    update_user_meta($child_user_id, 'gender', $gender);

                    if (!empty($_POST['selected_programs']) && !empty($_POST['selected_slots'])) {
                        $selected_programs = explode(',', $_POST['selected_programs']);
                        $selected_slots = explode(',', $_POST['selected_slots']);
                        
                        $slot_ids = [];
                        foreach($selected_slots as $slot) {
                            $sql = 'SELECT post_id FROM wp_postmeta WHERE meta_value LIKE %s';
                            $where = ["%$slot%"];
            
                            $results = $wpdb->get_results($wpdb->prepare($sql, $where));
                            $slot_ids[$results[0]->post_id][] = $slot;
                        }
                    } else {
                        $selected_programs = '';
                        $selected_slots = '';
                        $slot_ids = '';
                    }
                    
                    update_user_meta( $child_user_id, 'classes', array( $selected_programs ) );
                    update_user_meta( $child_user_id, 'classes_slots', array( $slot_ids ) );
                    update_user_meta( $child_user_id, 'slots', array($selected_slots) );

                    if ($status_program_participant == 'active' ||
                    $status_program_participant == 'inactive' ||
                    $status_program_participant == 'pending'
                    ) {
                        update_user_meta( $child_user_id, 'status_program_participant', $status_program_participant );
                    }

                    // set parent multiaccount details meta
                    $current_multiaccounts_number++;
                    update_user_meta( $parent_user_id, 'smuac_multiaccounts_number', $current_multiaccounts_number );

                    $current_multiaccounts_list = get_user_meta( $parent_user_id, 'smuac_multiaccounts_list', true );
                    $current_multiaccounts_list = $current_multiaccounts_list . ',' . $child_user_id;
                    update_user_meta( $parent_user_id, 'smuac_multiaccounts_list', $current_multiaccounts_list );

                    $userobj = new WP_User( $child_user_id );
                    $userobj->set_role( 'customer' );

                    echo '<style>#admin_create_child > .notice-success  { display: block !important; }</style>';
                    $customer_url = site_url('/wp-admin/admin.php?page=user-information-edit&user='.$parent_user_id.'&child=no');
                    wp_redirect($customer_url); 
                    exit;
                }
            }
        } else {
            echo '<style>#admin_create_child > .notice-warning  { display: block !important; }</style>';
        }
    }

    function update_userdata($field_name, $meta_value)
    {
        // Data before send changes
        $user_data = get_user_by('ID', $_GET["user"]);
        $user_meta =  get_user_meta($_GET["user"]);
        $user_meta['user_email'] = [$user_data->user_email];
        $user_meta['password'] = $user_data->password;
        $user_meta['user_login'] = [$user_data->user_login];

        $comparation_value = isset($user_meta[$field_name][0]) ? $user_meta[$field_name][0] : "";

        if (!empty($meta_value) && $comparation_value != $meta_value) {
            if ($field_name == "user_login" || $field_name == "user_email") {
                global $wpdb;
                $wpdb->update(
                    $wpdb->users,
                    [$field_name => sanitize_text_field($meta_value)],
                    ['ID' => $_GET['user']]
                );
            } else {
                update_user_meta($_GET['user'], $field_name, sanitize_text_field($meta_value));
            }
        }
    }
}



// WP_ajax for the notes
class noter_user{

    public function __construct()
    {
       add_action("wp_ajax_get_notes",array($this, "get_notes"));
    }

    public function get_notes()
    {
        if(isset($_POST["save"]) && $_POST["save"]== "yes"){
            $this->insert_comment($_POST['user_id'], $_POST['normal']);
        }else if (isset($_POST["delete"])){
            $this->delete_commet($_POST["delete"]);
        }

        global $wpdb;
        $user_id = sanitize_text_field($_POST["user_id"]);
        $datas = $wpdb->get_results($wpdb->prepare(
            "SELECT  * FROM wp_comments wc 
                inner join wp_commentmeta cm on (wc.comment_ID  = cm.comment_id  and cm.meta_key  = 'is_customer_note') 
                where wc.user_id = %s 
                and wc.comment_approved  = '1'
                ORDER BY wc.comment_ID  DESC", $user_id   
        ));

        echo json_encode($datas);
        wp_die();
    }


    public function insert_comment($user_id, $normal)
    {
        $subject = !empty(get_option('custom_note_subject')) ? get_option('custom_note_subject') : 'Gymnastics of York Account Message';
        $from = !empty(get_option('custom_note_from')) ? get_option('custom_note_from') : 'ca@gymnasticsofyork.com';
        $replyto = !empty(get_option('custom_note_replyto')) ? get_option('custom_note_replyto') : 'ca@gymnasticsofyork.com';
        $bcc  = !empty(get_option('custom_note_bcc')) ? get_option('custom_note_bcc') : 'ca@gymnasticsofyork.com';

        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: <'.$from.'>';
        $headers[] = 'Reply-To: <'.$replyto.'>';
        $headers[] = 'Bcc: '.$bcc;

        $user = get_userdata($user_id);

        $current = get_current_user_id();
        $current_user = get_user_by('id', $current);

        $commentdata = array(
            'comment_content'      => sanitize_text_field($_POST['content']),
            'user_id'              => sanitize_text_field($user_id),
            'comment_author'       => $current_user->display_name,
            'comment_meta'         => array(
                'is_customer_note'       => sanitize_text_field($normal),
            )
        );
    
        $comment_id = wp_insert_comment($commentdata);
        if (!is_wp_error($comment_id)) {
            if ($normal == 1) {
                wp_mail($user->data->user_email, $subject, $_POST['content'], $headers);
            }
            return $comment_id;
        }
    
        return false;
    }

    public function delete_commet($comment_id){
        wp_delete_comment($comment_id);
    }

}
