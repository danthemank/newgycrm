<div class="main-account-editing edit-form" id="account_details">
    <form action="<?= get_permalink() ?>" method="post">

        <input type="hidden" id="main_edit_nonce" name="main_edit_nonce" value="<?= $nonce ?>">
    
        <div class="modal-header">
            <h3>Update main account</h3>
        </div>
        <div class="flex-container">
            <div class="form-row">
                <label for="first_name">First Name</label>
                <input type="text" class="reg-input" value="<?= $meta['first_name'][0] ?>" name="first_name" id="first_name"    />
            </div>
            <div class="form-row">
                <label for="last_name">Last Name</label>
                <input type="text" class="reg-input"  value="<?= $meta['last_name'][0] ?>" name="last_name" id="last_name"    />
            </div>
        </div>
        <div class="form-row">
            <label for="user_email">Email Address</label>
            <input type="email" name="user_email"  value="<?= $user->user_email ?>" id="user_email"   />
        </div>

        <div class="flex-container input-container-md">
            <div class="form-row">
                <label for="curr_pass">Current Password</label>
                <input type="password" name="curr_pass" id="curr_pass"   />
            </div>
            <div class="form-row">
                <label for="user_pass">New Password</label>
                <input type="password" name="user_pass" id="user_pass"   />
            </div>
        </div>
    
        <div class="form-row">
            <button type="submit" class="btn submit-btn" data-form="main_account">Update</button>
        </div>
    
    </form>
</div>
