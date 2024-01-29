<div class="wrap" id="gycrm_send_emails">
    <h1>Send Emails</h1>
    <div class="tabs">
        <div class="tab" onclick=" openTabs(event, 'tab1' )">Send Email </div>
        <div class="tab" onclick=" openTabs(event, 'tab2' )">Schedule Email</div>
    </div>

    <?php
        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/email_templates/manual_email.php';
        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/email_templates/schedule_email.php';
    ?>

</div>