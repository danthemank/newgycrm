<?php 

class ProgramClasses {

    public $price_per_hour;

    public function __construct($price_per_hour)
    {
        $this->price_per_hour = $price_per_hour;
        
        add_action('init', array($this, 'create_programs_taxonomy'));

        add_action('init', array($this, 'add_member_role'));
        
        add_action('admin_init', array($this, 'prevent_member_dashboard_access'));

        add_action('pre_post_update', array($this, 'update_product'), 10, 2);

        add_action( 'add_meta_boxes_class', array($this, 'add_meta_boxes_class_callback'), 10, 2);
        add_action( 'save_post_class', array( $this, 'save_class_meta_box_data') );
    }

    public function save_class_meta_box_data($post_id) {

        if (!wp_verify_nonce($_POST['nonce'], 'slot_nonce')) {
            return;
        }

        $slot_ids = get_post_meta($post_id, 'slot_ids', true);

        if (isset($_POST['slots'])) {
            foreach ($_POST['slots'] as $id => $slot) {
                $date = date('mdYHis');

                foreach($slot as $key => $item) {
                    $slot_id = $id.$date;
                    if (empty($slot_ids)) {
                        update_post_meta($post_id, $slot_id.'_'.$key, $item);
                    } else {
                        if (in_array($id, $slot_ids)) {
                            $slot_id = $id;
                            update_post_meta($post_id, $slot_id.'_'.$key, $item);
                        } else {
                            update_post_meta($post_id, $slot_id.'_'.$key, $item);
                        }
                    }

                    if (!isset($slot['slot_registration_available'])) {
                        update_post_meta($post_id, $slot_id.'_slot_registration_available', '0');
                    }
                    update_post_meta($post_id, $slot_id.'_slot_registered', date('Y-m-d'));
                    update_post_meta($post_id, $slot_id.'_slot_parent', $post_id);
                }
                $new_slot_ids[] = $slot_id;
            }
            update_post_meta($post_id, 'slot_ids', $new_slot_ids);
        }
    }

    public function add_meta_boxes_class_callback() {
        add_meta_box('custom_post_type_data_meta_box', 'Class Slots', array($this,'class_slots'), 'class', 'normal','high' );
    }

    public function class_slots($post){

        $nonce = wp_create_nonce('slot_nonce');

        echo '
        <div class="class-slots">
            <input type="hidden" name="nonce" value="'.$nonce.'"/>
            <input type="hidden" id="post_id" value="'.$post->ID.'">
            <div class="new-slot"><button type="button" id="add_button" class="add-item">New Slot</button></div>
            <hr class="divider">

            <div class="slots-container">';

           $this->get_classes_slots($post->ID);
        echo '</div>
        </div>
        ';

        $this->slots_scripts();
	
	}

    public function slots_scripts() {
        ?>

            <script>
                (function ($) {
                    $(document).ready(function () {
                        $("body").on("click", '.slots-container .accordion', function() {
                            $(this).toggleClass("active");
                            $(this).next().slideToggle(200);
                        });

                        $('.class-slots .add-item').on('click', function() {
                            let post_id = $('.class-slots #post_id').val()
                            let container = $('.class-slots .slots-container'); 
                            
                            let rowId = ''
                            let letters = 'abcdefghijklmnopqrstuvwxyz';
                            for (let i = 0; i < 3; i++) {
                                rowId += letters.charAt(Math.floor(Math.random() * letters.length));
                            }
                            
                            let rowCount = 1
                            if (container.children().length >= 1) {
                                rowCount = container.children().length + 1
                            }

                                let html = `
                                    <div class="accordion-container" id="slot-${rowId}">
                                        <div class="accordion flex-container">
                                            <p>Slot #<span class="row-count">${rowCount}</span></p>
                                            <div class="delete-slot-item-icon"></div>
                                        </div>
                                        <div class="panel hidden">
                                            <table>
                                                <tr>
                                                    <td><label for="slot_status">Status</label></td>
                                                    <td>
                                                        <select name="slots[${rowId}][slot_status]" id="slot_status">
                                                            <option value="active">Active</option>
                                                            <option value="inactive">Inactive</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label for="slot_duration">Slot duration in hours</label></td>
                                                    <td><input type="number" name="slots[${rowId}][slot_duration]" id="slot_duration"></td>
                                                </tr>
                                                <tr>
                                                    <td><label for="slot_max_enrollments">Max enrollments</label></td>
                                                    <td><input type="number" name="slots[${rowId}][slot_max_enrollments]" id="slot_max_enrollments"></td>
                                                </tr>
                                                <tr>
                                                    <td><label for="slot_time_monday">Monday Start Time</label></td>
                                                    <td><input type="time" name="slots[${rowId}][slot_time_monday]" id="slot_time_monday"></td>
                                                </tr>
                                                <tr>
                                                    <td><label for="slot_time_tuesday">Tuesday Start Time</label></td>
                                                    <td><input type="time" name="slots[${rowId}][slot_time_tuesday]" id="slot_time_tuesday"></td>
                                                </tr>
                                                <tr>
                                                    <td><label for="slot_time_wednesday">Wednesday Start Time</label></td>
                                                    <td><input type="time" name="slots[${rowId}][slot_time_wednesday]" id="slot_time_wednesday"></td>
                                                </tr>
                                                <tr>
                                                    <td><label for="slot_time_thursday">Thursday Start Time</label></td>
                                                    <td><input type="time" name="slots[${rowId}][slot_time_thursday]" id="slot_time_thursday"></td>
                                                </tr>
                                                <tr>
                                                    <td><label for="slot_time_friday">Friday Start Time</label></td>
                                                    <td><input type="time" name="slots[${rowId}][slot_time_friday]" id="slot_time_friday"></td>
                                                </tr>
                                                <tr>
                                                    <td><label for="slot_time_saturday">Saturday Start Time</label></td>
                                                    <td><input type="time" name="slots[${rowId}][slot_time_saturday]" id="slot_time_saturday"></td>
                                                </tr>
                                                <tr>
                                                    <td><label for="slot_time_sunday">Sunday Start Time</label></td>
                                                    <td><input type="time" name="slots[${rowId}][slot_time_sunday]" id="slot_time_sunday"></td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" value="1" name="slots[${rowId}][slot_registration_available]" id="slot_registration_available"></td>
                                                    <td><label for="slot_registration_available">Available for registration</label></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>`

                                $(container).append(html);
                                $( '.modal' ).modal( 'hide' );
                                $('.blocker').hide();
                                $('body').css('overflow', 'auto')
                            
                        })

                        $("body").on("click", '.class-slots .delete-slot-item-icon', function(e){
                            $(this).parent().parent().remove();
                            let container = $('.class-slots .slots-container'); 
                            let rowCount = $('.class-slots .slots-container .row-count'); 

                            $.each(rowCount, function(i, el) {
                                i += 1;
                                $(el).text(i)
                            })
                            return false;
                        });
                    });
                })(jQuery);
            </script>

        <?php
    }

    public function get_classes_slots($post_id) {
        global $wpdb;
        $slot_ids = get_post_meta($post_id, 'slot_ids', true);

        if (!empty($slot_ids)) {

            foreach ($slot_ids as $slot) {
                $sql = 'SELECT * FROM wp_postmeta WHERE meta_key LIKE %s AND post_id = %s';
                $where = ["%$slot%", $post_id];

                $results = wp_list_pluck($wpdb->get_results($wpdb->prepare($sql, $where)), 'meta_value', 'meta_key');

                if (!empty($results)) {
                    $slots[$slot] = $results;
                }
            }

            $html = '';

            $count = 0;
            foreach ($slots as $key => $slot) {
                $count++;

            $html .= '<div class="accordion-container" id="slot-'.$key.'">
                        <div class="accordion flex-container">
                            <p>Slot #<span class="row-count">'.$count.'</span></p>
                            <div class="delete-slot-item-icon"></div>
                        </div>
                        <div class="panel hidden">
                            <table>
                                <tr>
                                    <td><label for="slot_status">Status</label></td>
                                    <td>
                                        <select name="slots['.$key.'][slot_status]" id="slot_status">
                                        <option value="active" ';
                                        $html .= isset($slot[$key.'_slot_status']) && $slot[$key.'_slot_status'] == 'active' ? 'selected' : ' ';
                                        $html.= '>Active</option>
                                        <option value="inactive" ';
                                        $html .= isset($slot[$key.'_slot_status']) && $slot[$key.'_slot_status'] == 'inactive' ? 'selected' : ' ';
                                        $html .= '>Inactive</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="slot_duration">Slot duration in hours</label></td>
                                    <td><input type="number" ';
                                    $html .= isset($slot[$key.'_slot_duration']) ? 'value="'.$slot[$key.'_slot_duration'].'"' : ' ';
                                    $html .= ' name="slots['.$key.'][slot_duration]" id="slot_duration"></td>
                                </tr>
                                <tr>
                                    <td><label for="slot_max_enrollments">Max enrollments</label></td>
                                    <td><input type="number" ';
                                    $html .= isset($slot[$key.'_slot_max_enrollments']) ? 'value="'.$slot[$key.'_slot_max_enrollments'].'"' : ' ';
                                    $html .= ' name="slots['.$key.'][slot_max_enrollments]" id="slot_max_enrollments"></td>
                                </tr>
                                <tr>
                                    <td><label for="slot_time_monday">Monday Start Time</label></td>
                                    <td><input type="time" ';
                                    $html .= isset($slot[$key.'_slot_time_monday']) ? 'value="'.$slot[$key.'_slot_time_monday'].'"' : ' ';
                                    $html .= ' name="slots['.$key.'][slot_time_monday]" id="slot_time_monday"></td>
                                </tr>
                                <tr>
                                    <td><label for="slot_time_tuesday">Tuesday Start Time</label></td>
                                    <td><input type="time" ';
                                    $html .= isset($slot[$key.'_slot_time_tuesday']) ? 'value="'.$slot[$key.'_slot_time_tuesday'].'"' : ' ';
                                    $html .= ' name="slots['.$key.'][slot_time_tuesday]" id="slot_time_tuesday"></td>
                                </tr>
                                <tr>
                                    <td><label for="slot_time_wednesday">Wednesday Start Time</label></td>
                                    <td><input type="time" ';
                                    $html .= isset($slot[$key.'_slot_time_wednesday']) ? 'value="'.$slot[$key.'_slot_time_wednesday'].'"' : ' ';
                                    $html .= ' name="slots['.$key.'][slot_time_wednesday]" id="slot_time_wednesday"></td>
                                </tr>
                                <tr>
                                    <td><label for="slot_time_thursday">Thursday Start Time</label></td>
                                    <td><input type="time" ';
                                    $html .= isset($slot[$key.'_slot_time_thursday']) ? 'value="'.$slot[$key.'_slot_time_thursday'].'"' : ' ';
                                    $html .= ' name="slots['.$key.'][slot_time_thursday]" id="slot_time_thursday"></td>
                                </tr>
                                <tr>
                                    <td><label for="slot_time_friday">Friday Start Time</label></td>
                                    <td><input type="time" ';
                                    $html .= isset($slot[$key.'_slot_time_friday']) ? 'value="'.$slot[$key.'_slot_time_friday'].'"' : ' ';
                                    $html .= ' name="slots['.$key.'][slot_time_friday]" id="slot_time_friday"></td>
                                </tr>
                                <tr>
                                    <td><label for="slot_time_saturday">Saturday Start Time</label></td>
                                    <td><input type="time" ';
                                    $html .= isset($slot[$key.'_slot_time_saturday']) ? 'value="'.$slot[$key.'_slot_time_saturday'].'"' : ' ';
                                    $html .= ' name="slots['.$key.'][slot_time_saturday]" id="slot_time_saturday"></td>
                                </tr>
                                <tr>
                                    <td><label for="slot_time_sunday">Sunday Start Time</label></td>
                                    <td><input type="time" ';
                                    $html .= isset($slot[$key.'_slot_time_sunday']) ? 'value="'.$slot[$key.'_slot_time_sunday'].'"' : ' ';
                                    $html .= ' name="slots['.$key.'][slot_time_sunday]" id="slot_time_sunday"></td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" value="1" ';
                                    $html .= isset($slot[$key.'_slot_registration_available']) && $slot[$key.'_slot_registration_available'] == '1' ? 'checked' : ' ';
                                    $html .= ' name="slots['.$key.'][slot_registration_available]" id="slot_registration_available"></td>
                                    <td><label for="slot_registration_available">Available for registration</label></td>
                                </tr>
                            </table>
                        </div>
                    </div>';
            }
            echo $html;

        }
    }

    public function delete_classes_products($post_id, $post) {
        global $wpdb;

        if ($post->post_type == 'class') {
            $sql = 'DELETE FROM '.$wpdb->postmeta.'
                    WHERE meta_key = "post_id"
                    AND meta_value = "'.$post_id.'"';

            $wpdb->query($sql);
        }

        if ($post->post_type == 'product') {
            $sql = 'DELETE FROM '.$wpdb->postmeta.'
                    WHERE meta_key = "product_id"
                    AND meta_value = "'.$post_id.'"';

            $wpdb->query($sql);
        }
    }

    public function create_classes_cpt()
    {
        
    }

    public function plugin_name_render_settings_field($args) {
		if($args['wp_data'] == 'option'){
			$wp_data_value = get_option($args['name']);
		} elseif($args['wp_data'] == 'post_meta'){
			$wp_data_value = get_post_meta($args['post_id'], $args['name'], true );
		}
		
		switch ($args['type']) {
			case 'input':
				$value = ($args['value_type'] == 'serialized') ? serialize($wp_data_value) : $wp_data_value;
				if($args['subtype'] != 'checkbox'){
					$prependStart = (isset($args['prepend_value'])) ? '<div class="input-prepend"> <span class="add-on">'.$args['prepend_value'].'</span>' : '';
					$prependEnd = (isset($args['prepend_value'])) ? '</div>' : '';
					$step = (isset($args['step'])) ? 'step="'.$args['step'].'"' : '';
					$min = (isset($args['min'])) ? 'min="'.$args['min'].'"' : '';
					$max = (isset($args['max'])) ? 'max="'.$args['max'].'"' : '';
					if(isset($args['disabled'])){
						// hide the actual input bc if it was just a disabled input the informaiton saved in the database would be wrong - bc it would pass empty values and wipe the actual information
						echo $prependStart.'<input type="'.$args['subtype'].'" id="'.$args['id'].'_disabled" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'_disabled" size="40" disabled value="' . esc_attr($value) . '" /><input type="hidden" id="'.$args['id'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />'.$prependEnd;
					} else {
						echo $prependStart.'<input type="'.$args['subtype'].'" id="'.$args['id'].'" "'.$args['required'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />'.$prependEnd;
					}
					/*<input required="required" '.$disabled.' type="number" step="any" id="'.$this->plugin_name.'_cost2" name="'.$this->plugin_name.'_cost2" value="' . esc_attr( $cost ) . '" size="25" /><input type="hidden" id="'.$this->plugin_name.'_cost" step="any" name="'.$this->plugin_name.'_cost" value="' . esc_attr( $cost ) . '" />*/
					
				} else {
					$checked = ($value) ? 'checked' : '';
					echo '<input type="'.$args['subtype'].'" id="'.$args['id'].'" "'.$args['required'].'" name="'.$args['name'].'" size="40" value="1" '.$checked.' />';
				}
				break;
			default:
				# code...
				break;
		}
	}

    public function add_member_role()
    {
        $role = get_role('subscriber');
        add_role('member', 'Member', $role->capabilities);
    }

    public function prevent_member_dashboard_access() {
        if (current_user_can('member')) {
            wp_redirect(home_url());
            exit;
        }
    }

    public function create_programs_taxonomy() {
        $labels = array(
            'name' => _x('Programs', 'taxonomy general name', 'class'),
            'singular_name' => __('Program', 'taxonomy singular name'),
            'search_items' => __('Search Programs', 'class'),
            'all_items' => __('All Programs', 'class'),
            'parent_item' => null,
            'parent_item_colon' => null,
            'edit_item' => __('Edit Program', 'class'),
            'update_item' => __('Update Program', 'class'),
            'add_new_item' => __('Add New Program', 'class'),
            'new_item_name' => __('New Program Name', 'class'),
            'separate_items_with_commas' => __('Separate Programs with commas', 'class'),
            'add_or_remove_items' => __('Add or remove Programs', 'class'),
            'choose_from_most_used' => __('Choose from the most used Programs', 'class'),
            'not_found' => __('No Programs Found', 'class'),
            'menu_name' => __('Programs', 'class'),
        );
    
        $args = array(
            'labels' => $labels,
            'public' => true,
            'show_in_menu' => true,
            'capability_type' => 'post',
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'update_count_callback' => '_update_post_term_count',
            'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
            'query_var' => true,
            'rewrite' => array('slug' => 'Programs'),
            'capabilities' => array ('edit_classes' => true),

        );
    
        register_taxonomy('programs', 'class', $args);

        $labels = array(
            'name' => 'Classes',
            'singular_name' => 'Class',
            'add_new' => 'Add New Class',
            'add_new_item' => 'Add New Class',
            'edit_item' => 'Edit Class',
            'new_item' => 'New Class',
            'all_items' => 'All Classes',
            'view_item' => 'View Class',
            'search_items' => 'Search Classes',
            'not_found' => 'No Classes found',
            'not_found_in_trash' => 'No Classes found in Trash',
            'parent_item_colon' => '',
            'menu_name' => 'Programs',
        );
    
        $args = array(
            'labels' => $labels,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'capability_type' => 'post',
            'hierarchical' => false,
            'menu_position' => 30,
            'menu_icon' => 'dashicons-welcome-widgets-menus',
            'capabilities' => array ('edit_classes' => true),
        );
    
        register_post_type('class', $args);
    }

    public function update_product($post_id, $post){

        global $wpdb;
        
        $args = array('post_type' => 'class',
                'p' => $post_id,
				'publish_status' => 'published',
        );
        $class = get_posts( $args );
        
        if ($post['post_status'] == 'publish' && $post['post_type'] == 'class'){
            
            if ($class) {
                
                $product_id = get_post_meta($post_id, 'product_id', true);
                $hours_per_week = get_field('hours_per_week', $post_id);
                
                if ($product_id && !empty($hours_per_week)) {

                    foreach ($this->price_per_hour as $hour => $price) {
                        if ($hours_per_week == $hour) {
                            update_post_meta($product_id, '_regular_price', $price);
                            update_post_meta($product_id, '_price', $price);
                            update_post_meta($product_id, '_subscription_price',  $price);
                        }
                    }
                    
                    $sql = 'UPDATE '.$wpdb->posts.' p
                    SET p.post_title = "'.$post['post_title'].'",
                        p.post_content = "'.$post['post_content'].'"
                    WHERE p.ID = '.$product_id;
                    

                    $wpdb->query($sql);
                }
            }
        }
    }

}

