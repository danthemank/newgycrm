<div id="scheduled_emails_list">
    <h1>Scheduled Emails List</h1>
    <table class="wp-list-table widefat fixed posts centered-table">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Message</th>
                <th>Schedule day</th>
                <th>Group</th>
                <th>Emails</th>
                <th>Status</th>
                <th>Created by</th>
                <th colspan="2">Action</th>
            </tr>
        </thead>
        <tbody>
            <?= $list ?>
        </tbody>
    </table>

    <div class="hidden custom-modal" id="edit_schedule">
        <form action="" method="POST">
            <input type="hidden" name="timestamp" id="timestamp">
            <input type="hidden" name="event_id" id="event_id">
            <div class="modal-header">
                <h3 class="schedule-title">Edit "<span></span>"</h3>
            </div>
            <div class="form-section">
                <div class="form-row">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject"/>
                </div>
                <div class="template-message">
                    <label for="message">Message</label>
                    <textarea name="message" id="message" cols="30" rows="10"></textarea>
                </div>
                <div class="form-row">
                    <label for="email_schedule">Schedule day</label>
                    <input type="number" min="1" max="31" id="email_schedule" name="email_schedule">
                </div>
                <div class="form-row flex-container">
                    <div>
                        <label for="email_type">Group</label>
                        <select name="email_type" id="email_type"">
                            <option value="all_customers">All Customers</option>
                            <option value="all_admin">All Administrators</option>
                            <option value="accounts-owing">All Accounts Owing</option>
                            <option value="comma">Comma Separated List</option>
                        </select>
                    </div>
                    <div>
                        <label for="comma_email">Emails</label>
                        <input type="text" name="comma_email" id="comma_email" disabled/>
                    </div>
                </div>
                <div class="form-row">
                    <label for="schedule_status">Status</label>
                    <select name="schedule_status" id="schedule_status">
                        <option value="on">On</option>
                        <option value="off">Off</option>
                    </select>
                </div>
            </div>
            <div class="form-section schedule-actions flex-container">
                <button type="submit" class="btn submit_user_info" name="save_schedule">Save</button>
                <button type="button" class="submit_user_info cancel-btn">Cancel</button>
            </div>
        </form>
        
    </div>

    <div class="hidden" id="confirm_delete_schedule">
        <div class="modal-header"></div>
        <form method="post" action="" class="flex-container confirm-delete custom-modal">
            <h2 class="schedule-title">Are you sure you want to delete "<span></span>" email?</h2>
            
            <input type="hidden" id="schedule_id" name="schedule_id">

            <div class="flex-container confirm-action">
                <input type="submit" name="delete_schedule" class="submit_user_info confirm-delete" value="Delete">
                <button class="submit_user_info cancel-btn" type="button">Cancel</button>
            </div>
        </form>
    </div>

</div>