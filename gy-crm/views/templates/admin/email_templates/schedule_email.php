<div id="tab2" class="tab-content">
        <form method="post" action="">
            <?php wp_nonce_field('send_schedule_emails_action'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Status:</th>
                    <td>
                        <select name="schedule_status" data-id="tab2" class="schedule_status" required>
                            <option value="on">On</option>
                            <option value="off">Off</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Email Template:</th>
                    <td>
                        <select name="email_template" data-id="tab2" class="email_template" required>
                            <?= get_email_templates() ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Schedule for day:</th>
                    <td>
                        <input data-id="tab2" name="email_schedule" class="email_schedule" type="number" min="1" max="31" required>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Type Of Email:</th>
                    <td>
                        <select name="email_type" data-id="tab2" class="email_type" required>
                            <option value="">Select a group</option>
                            <option value="all_customers">All Customers</option>
                            <option value="all_admin">All Administrators</option>
                            <option value="accounts-owing">All Accounts Owing</option>
                            <option value="comma">Comma Separated List</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top" class="comma_select" id="container_select" style="display: none;">
                    <th scope="row">Input Emails:</th>
                    <td>
                        <input type="text" id="comma_email" name="comma_email" class="regular-text" placeholder="email@example.com, email2@example.com">
                    </td>
                </tr>
                <tr valign="top" class="account_owing_select">
                    <th scope="row">Select User Email:</th>
                    <td>
                        <div class="custom-select" id="custom-select-account" data-id="tab2">
                            <div class="select-trigger" id="select-trigger-account">All class users are selected</div>
                            <div class="select-options" id="select-options-account">
                            </div>
                        </div>
                    </td>
                </tr>
                <tr valign="top" class="no_credit_select">
                    <th scope="row">Select User Email:</th>
                    <td>
                        <div class="custom-select" id="custom-select-no" data-id="tab2">
                            <div class="select-trigger" id="select-trigger-no" >All class users are selected</div>
                            <div class="select-options" id="select-options-no">
                            </div>
                        </div>
                    </td>
                </tr>
                <tr valign="top" class="single_select">
                    <th scope="row">Select User Email:</th>
                    <td>
                        <select name="single_user" data-id="tab2" class="single_user">
                            <option value="">Select a User </option>
                            <?php foreach ($users as $key => $user) : ?>
                                <option value="<?php echo $user->user_email; ?>"><?php echo "$user->display_name - $user->user_email"; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top" class="hide">
                    <th scope="row">To:</th>
                    <td><input type="text" name="to" id="to" class="regular-text"/></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Email Subject:</th>
                    <td><input type="text" name="email_subject" id="email_subject" class="regular-text"  required/></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Merge Tags:</th>
                    <td>
                        <select id="merge_tags_schedule" class="regular-text" onchange="insertMergeScheduleTag()">
                            <option value=""> {{...}} </option>
                            <option value="{{user_name}}">User Name</option>
                            <option value="{{first_name}}">First Name</option>
                            <option value="{{last_name}}">Last Name</option>
                            <option value="{{full_name}}">Full Name</option>
                            <option value="{{athletes_first_name}}">Athletes First Name</option>
                            <option value="{{athletes_last_name}}">Athletes Last Name</option>
                            <option value="{{athletes_full_name}}">Athletes Full Name</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Email Content:</th>
                    <td>
                        <?php wp_editor('', 'email_content_schedule', array('textarea_name' => 'email_content_schedule',  'default_editor' => 'tmce', 'editor_height' => '450px')); ?>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" class="button-primary" name="submit_schedule" value="<?php _e('Schedule Email') ?>" />
            </p>

            <div class="box-modal">
                <div class="overlay"></div>
                <div class="body-modal">
                    <div class="inner-body-modal">
                        <h2>Please enter a name for the Template</h2>
                        <input type="text" name="template_name" id="template_name">
                        <p class="submit">
                            <input type="submit" class="button-primary" name="save_template_email" value="<?php _e('Save Template') ?>" />
                        </p>
                    </div>
                </div>
            </div>

        </form>
    </div>