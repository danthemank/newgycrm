<div class="add_subaccount edit-form membership_form" id="add_subaccount">
    <div class="modal-header"></div>
    <form action="<?= get_permalink() ?>" method="post">
    <input type="hidden" id="child_create_nonce" name="child_create_nonce" value="<?= $nonce ?>">

        <div>
            <?php
                $athlete_id = 0;
                $count = 1; 
                require GY_CRM_PLUGIN_DIR . 'views/templates/public/registration/athlete_form.php'
            ?>
        </div>

        <div class="form-row submit-container">
            <button type="submit" class="btn submit-btn" data-form="add_subaccount">Create</button>
        </div>
            
    </form>
</div>
