<div class="wrap gycrm-admin-settings">
    <h1>GYCRM Settings</h1>
    <div>
        <ul class="tabs">
            <li class="tab" data-id="#settings_pricing">Classes Pricing</li>
            <li class="tab" data-id="#settings_tasks">Automatic Tasks</li>
            <li class="tab" data-id="#settings_roles">Manage Roles</li>
            <li class="tab" data-id="#settings_notes">Manage Email</li>
            <li class="tab" data-id="#settings_coupons">Manage Coupons</li>
        </ul>
    </div>
    <div class="main-section">
        <form method="post" class="settings-section" id="settings_pricing" action="options.php">
            <?php settings_fields('gy_crm_settings_pricing_group'); ?>
            <?php do_settings_sections('gy_crm_settings_pricing_group'); ?>
            <div class="flex-container gycrm-admin-settings-btn"><?php submit_button(); ?></div>
        </form>
        <form method="post" class="settings-section hidden" id="settings_tasks" action="options.php">
            <?php settings_fields('gy_crm_settings_tasks_group'); ?>
            <?php do_settings_sections('gy_crm_settings_tasks_group'); ?>
            <div class="flex-container gycrm-admin-settings-btn"><?php submit_button(); ?></div>
        </form>
        <form method="post" class="settings-section hidden" id="settings_roles" action="options.php">
            <?php settings_fields('gy_crm_settings_roles_group'); ?>
            <?php do_settings_sections('gy_crm_settings_roles_group'); ?>
            <div class="flex-container gycrm-admin-settings-btn"><?php submit_button(); ?></div>
        </form>
        <form method="post" class="settings-section hidden" id="settings_notes" action="options.php">
            <?php settings_fields('gy_crm_settings_notes_group'); ?>
            <?php do_settings_sections('gy_crm_settings_notes_group'); ?>
            <div class="flex-container gycrm-admin-settings-btn"><?php submit_button(); ?></div>
        </form>
        <form method="post" class="settings-section hidden" id="settings_coupons" action="options.php">
            <?php settings_fields('gy_crm_settings_coupons_group'); ?>
            <?php do_settings_sections('gy_crm_settings_coupons_group'); ?>
            <div class="flex-container gycrm-admin-settings-btn"><?php submit_button(); ?></div>
        </form>
    </div>
</div>