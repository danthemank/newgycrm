<?php
    $id = get_current_user_id();
    require GY_CRM_PLUGIN_DIR . 'views/js/easy-pos.php';
?>
<div class="account-billing-editing edit-form" id="add_payment">
    <?= do_shortcode('[easy_pos_shortcode set_user="'.$id.'" my_account_page="true"]') ?>
    </form>
</div>
