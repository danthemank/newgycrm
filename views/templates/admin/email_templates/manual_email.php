<div id="tab1"  class="tab-content" style="display:block">
        <form method="post" action="" id="gycrm_send_manual">
            <?php wp_nonce_field('send_emails_action'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Email Template:</th>
                    <td>
                        <select name="email_template" data-id="tab1" class="email_template" required>
                            <?= get_email_templates() ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Type Of Email:</th>
                    <td>
                        <select name="email_type" data-id="tab1" class="email_type" required>
                            <option value="">Select is Single or a group</option>
                            <option value="single">Single User </option>
                            <option value="all">All Users</option>
                            <option value="accounts-owing" class="owing">All Accounts Owing</option>
                            <option value="no-credit">No Credit Card on Owing Account</option>
                            <option value="comma">Comma Separated List</option>
                            <option value="program">Users Related To a Program </option>
                            <option value="tag">Users Related To a Tag </option>
                        </select>
                    </td>
                </tr>
                <tr valign="top" class="users-by-tags hidden">
                    <th scope="row">Select Tag:</th>
                    <td>
                        <select name="tag" data-id="tab1" class="tag">
                            <option value="">All Tags</option>
                            <?= get_athlete_tags() ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top" class="single_select">
                    <th scope="row">Select User Email:</th>
                    <td>
                        <select name="single_user" data-id="tab1" class="single_user">
                            <option value="">Select a User </option>
                            <?php foreach ($users as $key => $user) : ?>
                                <option value="<?php echo $user->user_email; ?>" id= "<?php echo $user->ID; ?>"><?php echo "$user->display_name - $user->user_email"; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top" class="program_select">
                    <th scope="row">Select Program:</th>
                    <td>
                        <select name="programs_classes" id="programs_classes">
                            <option value="none">Select a Program </option>
                            <option value="all_programs">All Programs</option>
                            <?php foreach ($programs as $key => $program) : ?>
                                <option value="<?= $program->ID; ?>"><?php echo $program->post_title; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top" class="comma_select" id="container_select">
                    <th scope="row">Input Emails:</th>
                    <td>
                        <input type="text" id="comma_email" name="comma_email" class="regular-text" placeholder="email@example.com, email2@example.com">
                    </td>
                </tr>
                <tr valign="top" class="user_select_tags hidden">
                    <th scope="row">Select Users with Tags:</th>
                    <td>
                        <div class="custom-select" id="custom-select-tags" data-id="tab1">
                            <div class="select-trigger" id="select-trigger-tags">All Users are Selected</div>
                            <div class="select-options" id="select-options-tags">
                            </div>
                        </div>
                    </td>
                </tr>
                <tr valign="top" class="program_user_select_slots hidden">
                    <th scope="row">Select Slot:</th>
                    <td>
                        <div class="custom-select" id="custom-select-slots" data-id="tab1">
                            <div class="select-trigger" id="select-trigger-slots">All Slots are Selected</div>
                            <div class="select-options" id="select-options-slots">
                            </div>
                        </div>
                    </td>
                </tr>
                <tr valign="top" class="program_user_select">
                    <th scope="row">Select User Email:</th>
                    <td>
                        <div class="custom-select" id="custom-select" data-id="tab1">
                            <div class="select-trigger" id="select-trigger">All users are selected</div>
                            <div class="select-options" id="select-options">
                            </div>
                        </div>
                    </td>
                </tr>
                <tr valign="top" class="account_owing_select">
                    <th scope="row">Select User Email:</th>
                    <td>
                        <div class="custom-select" id="custom-select-account" data-id="tab1">
                            <div class="select-trigger" id="select-trigger-account" >All users are selected</div>
                            <div class="flex-container">
                                <div class="select-options" id="select-options-account">
                                </div>
                                <div class="absolute hidden">
                                    <div class="lds-ring">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr valign="top" class="no_credit_select">
                    <th scope="row">Select User Email:</th>
                    <td>
                        <div class="custom-select" id="custom-select-no" data-id="tab1">
                            <div class="select-trigger" id="select-trigger-no" >All users are selected</div>
                            <div class="flex-container">
                                <div class="select-options" id="select-options-no">
                                </div>
                                <div class="absolute hidden">
                                    <div class="lds-ring">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                        <select id="merge_tags" class="regular-text" onchange="insertMergeTag()">
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
                        <?php 
                        wp_editor('', 'email_content', array('textarea_name' => 'email_content', 'default_editor' => 'tmce', 'editor_height' => '450px')); ?>
                    </td>
                </tr>
                <input type="hidden" name="email_schedule" value="now" required>

            </table>
            <p class="submit">
                <input type="submit" id="gycrm_send_emails_btn" class="button-primary" name="submit_email" value="<?php _e('Send Emails') ?>" />
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