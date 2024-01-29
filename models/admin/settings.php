<?php 

class AdminSettings{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_menu_link'));
        add_action('admin_init', array($this, 'register_admin_settings'));
    }

    public function add_menu_link() {
        add_submenu_page('options-general.php', 'GYCRM Settings', 'GYCRM Settings', 'manage_options', 'gy_crm_settings', array($this, 'admin_settings_page'));
    }

	public function admin_settings_page() {
		require GY_CRM_PLUGIN_DIR . 'views/templates/admin/settings/settings.php';
    }


    public function register_admin_settings() {

        register_setting('gy_crm_settings_pricing_group', 'halfhour_week');
        register_setting('gy_crm_settings_pricing_group', 'onehour_week');
        register_setting('gy_crm_settings_pricing_group', 'onehalfhour_week');
        register_setting('gy_crm_settings_pricing_group', 'twohour_week');
        register_setting('gy_crm_settings_pricing_group', 'threehour_week');
        register_setting('gy_crm_settings_pricing_group', 'fourhour_week');
        register_setting('gy_crm_settings_pricing_group', 'fivehour_week');
        register_setting('gy_crm_settings_pricing_group', 'sixhour_week');
        register_setting('gy_crm_settings_pricing_group', 'sevenhour_week');
        register_setting('gy_crm_settings_pricing_group', 'eighthour_week');
        register_setting('gy_crm_settings_pricing_group', 'ninehour_week');
        register_setting('gy_crm_settings_pricing_group', 'twelvehour_week');
        register_setting('gy_crm_settings_pricing_group', 'fifteenhour_week');
        register_setting('gy_crm_settings_pricing_group', 'twentyhour_week');
		register_setting('gy_crm_settings_pricing_group', 'registration_fee');
		register_setting('gy_crm_settings_pricing_group', 'referral_credit');
		register_setting('gy_crm_settings_pricing_group', 'applied_late_fees_type');
		register_setting('gy_crm_settings_pricing_group', 'applied_late_fees');
		register_setting('gy_crm_settings_pricing_group', 'days_before_late_fees');

        register_setting('gy_crm_settings_tasks_group', 'pause_classes_to_products');
		register_setting('gy_crm_settings_tasks_group', 'automatic_monthly_invoices');
		register_setting('gy_crm_settings_tasks_group', 'automatic_applied_late_fees');
		register_setting('gy_crm_settings_tasks_group', 'pause_email_schedules');

		register_setting('gy_crm_settings_roles_group', 'role_custom_capabilities');

		register_setting('gy_crm_settings_notes_group', 'custom_note_subject');
		register_setting('gy_crm_settings_notes_group', 'custom_note_replyto');
		register_setting('gy_crm_settings_notes_group', 'custom_note_bcc');
		
        add_settings_section(
            'admin_settings_pricing_section',
            'Classes Pricing',
            array( $this, 'admin_settings_main_section_callback' ),
            'gy_crm_settings_pricing_group'
        );  

        add_settings_section(
            'admin_settings_tasks_section',
            'Automated tasks',
            array( $this, 'admin_settings_main_section_callback' ),
            'gy_crm_settings_tasks_group'
        );  

        add_settings_section(
            'admin_settings_roles_section',
            'Manage role capabitilies',
            array( $this, 'admin_settings_main_section_callback' ),
            'gy_crm_settings_roles_group'
        );

        add_settings_section(
            'admin_settings_notes_section',
            'Manage emails from Customer Information "Notes"',
            array( $this, 'admin_settings_main_section_callback' ),
            'gy_crm_settings_notes_group'
        );
		
		

		add_settings_field(
			'halfhour_duration',
			'Half an Hour a Week Price',
			array($this, 'halfhour_week_callback'),
			'gy_crm_settings_pricing_group',
			'admin_settings_pricing_section'
		);

		add_settings_field(
			'onehour_week',
			'1 hour duration a Week Price',
			array($this, 'onehour_week_callback'),
			'gy_crm_settings_pricing_group',
			'admin_settings_pricing_section'
		);

		add_settings_field(
			'onehalfhour_week',
			'1 and a Half hour duration a Week Price',
			array($this, 'onehalfhour_week_callback'),
			'gy_crm_settings_pricing_group',
			'admin_settings_pricing_section'
		);

		add_settings_field(
			'twohour_week',
			'2 hour duration a Week Price',
			array($this, 'twohour_week_callback'),
			'gy_crm_settings_pricing_group',
			'admin_settings_pricing_section'
		);

		add_settings_field(
			'threehour_week',
			'3 hour duration a Week Price',
			array($this, 'threehour_week_callback'),
			'gy_crm_settings_pricing_group',
			'admin_settings_pricing_section'
		);

		add_settings_field(
			'fourhour_week',
			'4 hour duration a Week Price',
			array($this, 'fourhour_week_callback'),
			'gy_crm_settings_pricing_group',
			'admin_settings_pricing_section'
		);

		add_settings_field(
			'fivehour_week',
			'5 hour duration a Week Price',
			array($this, 'fivehour_week_callback'),
			'gy_crm_settings_pricing_group',
			'admin_settings_pricing_section'
		);

		add_settings_field(
			'sixhour_week',
			'6 hour duration a Week Price',
			array($this, 'sixhour_week_callback'),
			'gy_crm_settings_pricing_group',
			'admin_settings_pricing_section'
		);

		add_settings_field(
			'sevenhour_week',
			'7 hour duration a Week Price',
			array($this, 'sevenhour_week_callback'),
			'gy_crm_settings_pricing_group',
			'admin_settings_pricing_section'
		);

		add_settings_field(
			'eighthour_week',
			'8 hour duration a Week Price',
			array($this, 'eighthour_week_callback'),
			'gy_crm_settings_pricing_group',
			'admin_settings_pricing_section'
		);

		add_settings_field(
			'ninehour_week',
			'9 hour duration a Week Price',
			array($this, 'ninehour_week_callback'),
			'gy_crm_settings_pricing_group',
			'admin_settings_pricing_section'
		);

		add_settings_field(
			'twelvehour_week',
			'12 hour duration a Week Price',
			array($this, 'twelvehour_week_callback'),
			'gy_crm_settings_pricing_group',
			'admin_settings_pricing_section'
		);

		add_settings_field(
			'fifteenhour_week',
			'15 hour duration a Week Price',
			array($this, 'fifteenhour_week_callback'),
			'gy_crm_settings_pricing_group',
			'admin_settings_pricing_section'
		);

		add_settings_field(
			'twentyhour_week',
			'20 hour duration a Week Price',
			array($this, 'twentyhour_week_callback'),
			'gy_crm_settings_pricing_group',
			'admin_settings_pricing_section'
		);

		add_settings_field(
			'registration_fee',
			'Registration fee',
			array($this, 'registration_fee_callback'),
			'gy_crm_settings_pricing_group',
			'admin_settings_pricing_section'
		);

		add_settings_field(
			'referral_credit',
			'Referral Credit',
			array($this, 'referral_credit_callback'),
			'gy_crm_settings_pricing_group',
			'admin_settings_pricing_section'
		);

		add_settings_field(
			'applied_late_fees_type',
			'Applied Late Fees Type',
			array($this, 'applied_late_fees_type_callback'),
			'gy_crm_settings_pricing_group',
			'admin_settings_pricing_section'
		);

		add_settings_field(
			'applied_late_fees',
			'Applied Late Fees',
			array($this, 'applied_late_fees_callback'),
			'gy_crm_settings_pricing_group',
			'admin_settings_pricing_section'
		);

		add_settings_field(
			'days_before_late_fees',
			'Days before applying late fee',
			array($this, 'days_before_late_fees_callback'),
			'gy_crm_settings_pricing_group',
			'admin_settings_pricing_section'
		);

		add_settings_field(
			'automatic_monthly_invoices',
			'Invoice creation',
			array($this, 'automatic_monthly_invoices_callback'),
			'gy_crm_settings_tasks_group',
			'admin_settings_tasks_section'
		);

		add_settings_field(
			'automatic_applied_late_fees',
			'Applied Late Fees',
			array($this, 'automatic_applied_late_fees_callback'),
			'gy_crm_settings_tasks_group',
			'admin_settings_tasks_section'
		);

		add_settings_field(
			'pause_classes_to_products',
			'Programs to Products conversion',
			array($this, 'pause_classes_to_products_callback'),
			'gy_crm_settings_tasks_group',
			'admin_settings_tasks_section'
		);
		
		add_settings_field(
			'pause_email_schedules',
			'Email schedules',
			array($this, 'pause_email_schedules_callback'),
			'gy_crm_settings_tasks_group',
			'admin_settings_tasks_section'
		);
		
		add_settings_field(
			'staff_roles_capabilities',
			'Role',
			array($this, 'role_custom_capabilities_callback'),
			'gy_crm_settings_roles_group',
			'admin_settings_roles_section'
		);
		
		add_settings_field(
			'custom_note_subject',
			'Email Subject',
			array($this, 'custom_note_subject_callback'),
			'gy_crm_settings_notes_group',
			'admin_settings_notes_section'
		);

		add_settings_field(
			'custom_note_from',
			'From Email Address',
			array($this, 'custom_note_from_callback'),
			'gy_crm_settings_notes_group',
			'admin_settings_notes_section'
		);
		
		add_settings_field(
			'custom_note_replyto',
			'Reply To Email Address',
			array($this, 'custom_note_replyto_callback'),
			'gy_crm_settings_notes_group',
			'admin_settings_notes_section'
		);
		
		add_settings_field(
			'custom_note_bcc',
			'BCC Email Address',
			array($this, 'custom_note_bcc_callback'),
			'gy_crm_settings_notes_group',
			'admin_settings_notes_section'
		);
		
    }

    public function admin_settings_main_section_callback($input) {

        $new_input = array();
        if( isset( $input['halfhour_week'] ) ) {
            $new_input['halfhour_week'] = absint( $input['halfhour_week'] );
        }
        if( isset( $input['onehour_week'] ) ) {
            $new_input['onehour_week'] = absint( $input['onehour_week'] );
        }
        if( isset( $input['onehalfhour_week'] ) ) {
            $new_input['onehalfhour_week'] = absint( $input['onehalfhour_week'] );
        }
		if( isset( $input['twohour_week'] ) ) {
			$new_input['twohour_week'] = absint( $input['twohour_week'] );
		}
        if( isset( $input['threehour_week'] ) ) {
            $new_input['threehour_week'] = absint( $input['threehour_week'] );
        }
        if( isset( $input['fourhour_week'] ) ) {
            $new_input['fourhour_week'] = absint( $input['fourhour_week'] );
        }
        if( isset( $input['fivehour_week'] ) ) {
            $new_input['fivehour_week'] = absint( $input['fivehour_week'] );
        }
        if( isset( $input['sixhour_week'] ) ) {
            $new_input['sixhour_week'] = absint( $input['sixhour_week'] );
        }
        if( isset( $input['sevenhour_week'] ) ) {
            $new_input['sevenhour_week'] = absint( $input['sevenhour_week'] );
        }
        if( isset( $input['eighthour_week'] ) ) {
            $new_input['eighthour_week'] = absint( $input['eighthour_week'] );
        }
        if( isset( $input['ninehour_week'] ) ) {
            $new_input['ninehour_week'] = absint( $input['ninehour_week'] );
        }
        if( isset( $input['twelvehour_week'] ) ) {
            $new_input['twelvehour_week'] = absint( $input['twelvehour_week'] );
        }
        if( isset( $input['fifteenhour_week'] ) ) {
            $new_input['fifteenhour_week'] = absint( $input['fifteenhour_week'] );
        }
        if( isset( $input['twentyhour_week'] ) ) {
            $new_input['twentyhour_week'] = absint( $input['twentyhour_week'] );
        }
        if( isset( $input['registration_fee'] ) ) {
            $new_input['registration_fee'] = absint( $input['registration_fee'] );
        }
        if( isset( $input['pause_classes_to_products'] ) ) {
            if ($input['pause_classes_to_products'] == 1 ||  $input['pause_classes_to_products'] == 0) {
                $new_input['pause_classes_to_products'] = $input['pause_classes_to_products'];
            }
        }
        if( isset( $input['automatic_monthly_invoices'] )) {
            if ($input['automatic_monthly_invoices'] == 1 ||  $input['automatic_monthly_invoices'] == 0) {
                $new_input['automatic_monthly_invoices'] = $input['automatic_monthly_invoices'];
            }
        }
        if(  isset( $input['pause_email_schedules'] )) {
            if ($input['pause_email_schedules'] == 1 ||  $input['pause_email_schedules'] == 0) {
                $new_input['pause_email_schedules'] = $input['pause_email_schedules'];
            }
        }

        return $new_input;

	}

    public function role_custom_capabilities_callback() {
		$roles = array('staff', 'seniorstaff', 'regularstaff', 'juniorstaff', 'entrystaff');

		echo '<select id="edit_gycrm_roles" style="margin-bottom: 2rem">';
			foreach ($roles as $role) {
				
				$role_slug = get_role($role);
				$is_role = $role ? wp_roles()->get_names()[ $role ] : '';

				if (!empty($is_role)) {
					echo '<option value="'.$role_slug->name.'">'.$is_role.'</option>';
				}
			}
			echo '</select>';
			$manager_capabilities = get_role($roles[0])->capabilities;

		?>

		<div id="gycrm_roles_capabilities">
			<div style="margin-bottom: .5rem">
				<input type="checkbox" value="1" class="gycrm-capability" <?= isset($manager_capabilities['read_customer_information']) ? 'checked' : '' ?> data-id="read_customer_information">
				<label for="read_customer_information">Show Customer Information Page</label>
			</div>
			<div style="margin-bottom: .5rem">
				<input type="checkbox" value="1" class="gycrm-capability" <?= isset($manager_capabilities['edit_customer_information']) ? 'checked' : '' ?> data-id="edit_customer_information">
				<label for="edit_customer_information">Edit Customer Information Page</label>
			</div>
			<div style="margin-bottom: .5rem">
				<input type="checkbox" value="1" class="gycrm-capability" <?= isset($manager_capabilities['edit_customer_information_parents']) ? 'checked' : '' ?> data-id="edit_customer_information_parents">
				<label for="edit_customer_information_parents">Show parents in Customer Information Page</label>
			</div>
			<div style="margin-bottom: .5rem">
				<input type="checkbox" value="1" class="gycrm-capability" <?= isset($manager_capabilities['edit_customer_information_children_parents']) ? 'checked' : '' ?> data-id="edit_customer_information_children_parents">
				<label for="edit_customer_information_children_parents">Show children parents names in Customer Information Page</label>
			</div>
			<div style="margin-bottom: .5rem">
				<input type="checkbox" value="1" class="gycrm-capability" <?= isset($manager_capabilities['edit_classes']) && isset($manager_capabilities['read_private_classes']) && isset($manager_capabilities['delete_classes']) ? 'checked' : '' ?> data-id="[&#34edit_classes&#34, &#34publish_classes&#34, &#34edit_others_classes&#34, &#34edit_published_classes&#34, &#34edit_private_classes&#34, &#34read_private_classes&#34, &#34delete_classes&#34, &#34delete_others_classes&#34, &#34delete_private_classes&#34, &#34delete_published_classes&#34]">
				<label for="edit_classes">Show/create programs in Programs Page</label>
			</div>
			<div style="margin-bottom: .5rem">
				<input type="checkbox" value="1" class="gycrm-capability" <?= isset($manager_capabilities['edit_pos_payments']) && isset($manager_capabilities['edit_pos']) ? 'checked' : '' ?> data-id="[&#34edit_pos_payments&#34, &#34edit_pos&#34]">
				<label for="edit_pos_payments">Create payments in Easy Point of Sale Page</label>
			</div>
			<div style="margin-bottom: .5rem">
				<input type="checkbox" value="1" class="gycrm-capability" <?= isset($manager_capabilities['edit_email_templates']) && isset($manager_capabilities['read_private_email_templates']) && isset($manager_capabilities['delete_email_templates']) ? 'checked' : '' ?> data-id="[&#34edit_email_templates&#34, &#34publish_email_templates&#34, &#34edit_others_email_templates&#34, &#34edit_published_email_templates&#34, &#34edit_private_email_templates&#34, &#34read_private_email_templates&#34, &#34delete_email_templates&#34, &#34delete_others_email_templates&#34, &#34delete_private_email_templates&#34, &#34delete_published_email_templates&#34]">
				<label for="edit_email_templates">Show Email Templates Page</label>
			</div>
			<div style="margin-bottom: .5rem">
				<input type="checkbox" value="1" class="gycrm-capability" <?= isset($manager_capabilities['edit_attendance']) ? 'checked' : '' ?> data-id="edit_attendance">
				<label for="edit_attendance">Show Attendance Page</label>
			</div>
		</div>

		<?php
	}

    public function custom_note_from_callback() {
		?>
		<input type="text" id=" custom_note_from" name="custom_note_from" value="<?= !empty(get_option( 'custom_note_from' )) ? get_option( 'custom_note_from' ) : 'ca@gymnasticsofyork.com' ?>" />
        <?php
	}

    public function custom_note_subject_callback() {
		?>
		<input type="text" id=" custom_note_subject" name="custom_note_subject" value="<?= !empty(get_option( 'custom_note_subject' )) ? get_option( 'custom_note_subject' ) : 'Gymnastics of York Account Message' ?>" />
        <?php
	}

    public function custom_note_replyto_callback() {
		?>
		<input type="email" id="custom_note_replyto" name="custom_note_replyto" value="<?= !empty(get_option( 'custom_note_replyto' )) ? get_option( 'custom_note_replyto' ) : 'ca@gymnasticsofyork.com' ?>" />
        <?php
	}

    public function custom_note_bcc_callback() {
		?>
		<input type="email" id="custom_note_bcc" name="custom_note_bcc" value="<?= !empty(get_option( 'custom_note_bcc' )) ? get_option( 'custom_note_bcc' ) : 'ca@gymnasticsofyork.com' ?>" />
        <?php
	}

    public function halfhour_week_callback() {
		?>
		<span>$</span>
		<input type="number" id="halfhour_week" name="halfhour_week" value="<?= get_option( 'halfhour_week' ) ?>" />
        <?php
	}

    public function onehour_week_callback() {
		?>
		<span>$</span>
		<input type="number" id="onehour_week" name="onehour_week" value="<?= get_option( 'onehour_week' ) ?>" />
        <?php
	}
    public function onehalfhour_week_callback() {
		?>
		<span>$</span>
		<input type="number" id="onehalfhour_week" name="onehalfhour_week" value="<?= get_option( 'onehalfhour_week' ) ?>" />
        <?php
	}

    public function twohour_week_callback() {
		?>
		<span>$</span>
		<input type="number" id="twohour_week" name="twohour_week" value="<?= get_option( 'twohour_week' ) ?>"/>
        <?php
	}

    public function threehour_week_callback() {
		?>
		<span>$</span>
		<input type="number" id="threehour_week" name="threehour_week" value="<?= get_option( 'threehour_week' ) ?>"/>
        <?php
	}

    public function fourhour_week_callback() {
		?>
		<span>$</span>
		<input type="number" id="fourhour_week" name="fourhour_week" value="<?= get_option( 'fourhour_week' ) ?>"/>
        <?php
	}

    public function fivehour_week_callback() {
		?>
		<span>$</span>
		<input type="number" id="fivehour_week" name="fivehour_week" value="<?= get_option( 'fivehour_week' ) ?>"/>
        <?php
	}

    public function sixhour_week_callback() {
		?>
		<span>$</span>
		<input type="number" id="sixhour_week" name="sixhour_week" value="<?= get_option( 'sixhour_week' ) ?>"/>
        <?php
	}

    public function sevenhour_week_callback() {
		?>
		<span>$</span>
		<input type="number" id="sevenhour_week" name="sevenhour_week" value="<?= get_option( 'sevenhour_week' ) ?>"/>
        <?php
	}

    public function eighthour_week_callback() {
		?>
		<span>$</span>
		<input type="number" id="eighthour_week" name="eighthour_week" value="<?= get_option( 'eighthour_week' ) ?>"/>
        <?php
	}

    public function ninehour_week_callback() {
		?>
		<span>$</span>
		<input type="number" id="ninehour_week" name="ninehour_week" value="<?= get_option( 'ninehour_week' ) ?>"/>
        <?php
	}

    public function twelvehour_week_callback() {
		?>
		<span>$</span>
		<input type="number" id="twelvehour_week" name="twelvehour_week" value="<?= get_option( 'twelvehour_week' ) ?>"/>
        <?php
	}

    public function fifteenhour_week_callback() {
		?>
		<span>$</span>
		<input type="number" id="fifteenhour_week" name="fifteenhour_week" value="<?= get_option( 'fifteenhour_week' ) ?>"/>
        <?php
	}

    public function twentyhour_week_callback() {
		?>
		<span>$</span>
		<input type="number" id="twentyhour_week" name="twentyhour_week" value="<?= get_option( 'twentyhour_week' ) ?>"/>
        <?php
	}

    public function registration_fee_callback() {
		?>
		<span>$</span>
		<input type="number" id="registration_fee" name="registration_fee" value="<?= get_option( 'registration_fee' ) ?>"/>
        <?php
	}

    public function referral_credit_callback() {
		?>
		<span>$</span>
		<input type="number" id="referral_credit" name="referral_credit" value="<?= get_option( 'referral_credit' ) ?>"/>
        <?php
	}

    public function applied_late_fees_type_callback() {
		$fee_type = get_option( 'applied_late_fees_type' );
		?>
		<select name="applied_late_fees_type" id="applied_late_fees_type">
			<option value="">Select Option</option>
			<option value="amount" <?= isset($fee_type) && $fee_type == 'amount' ? 'selected' : '' ?>>Fixed Amount $</option>
			<option value="percentage" <?= isset($fee_type) && $fee_type == 'percentage' ? 'selected' : '' ?>>Percentage %</option>
		</select>
        <?php
	}

    public function days_before_late_fees_callback() {
		?>
		<input type="number" id="days_before_late_fees" name="days_before_late_fees" value="<?= get_option( 'days_before_late_fees' ) ?>"/>
        <?php
	}

    public function applied_late_fees_callback() {
		?>
		<input type="number" id="applied_late_fees" name="applied_late_fees" value="<?= get_option( 'applied_late_fees' ) ?>"/>
        <?php
	}

    public function pause_classes_to_products_callback() {
		?>
        <label for="pause_classes_to_products">Pause the classes to products daily update</label>
		<input type="checkbox" id="pause_classes_to_products" name="pause_classes_to_products" value="1" <?php checked( '1', get_option( 'pause_classes_to_products' ) ); ?> />
		<p style="font-style:italic;">Checking this option will pause the classes to products daily update</p>
		<?php
	}

    public function automatic_monthly_invoices_callback() {
		?>
        <label for="automatic_monthly_invoices">Automatic monthly invoice creation</label>
		<input type="checkbox" id="automatic_monthly_invoices" name="automatic_monthly_invoices" value="1" <?php checked( '1', get_option( 'automatic_monthly_invoices' ) ); ?>/>
		<p style="font-style:italic;">Checking this option will automatically create monthly invoices</p>
		<?php
	}

    public function automatic_applied_late_fees_callback() {
		?>
        <label for="automatic_applied_late_fees">Automatic applied late fees</label>
		<input type="checkbox" id="automatic_applied_late_fees" name="automatic_applied_late_fees" value="1" <?php checked( '1', get_option( 'automatic_applied_late_fees' ) ); ?>/>
		<p style="font-style:italic;">Checking this option will automatically apply late fees to invoices</p>
		<?php
	}

    public function pause_email_schedules_callback() {
		?>
        <label for="pause_email_schedules">Pause the current email schedules</label>
		<input type="checkbox" id="pause_email_schedules" name="pause_email_schedules" value="1" <?php checked( '1', get_option( 'pause_email_schedules' ) ); ?>/>
		<p style="font-style:italic;">Checking this option will pause the current email schedules</p>
		<?php
	}

}

