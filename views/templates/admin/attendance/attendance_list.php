<div id="attendance_tables">

    <?php
        $nonce = wp_create_nonce('attendance_nonce');
        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/attendance/in_class.php';
        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/attendance/not_in_class.php';
    ?>


    <input type="hidden" id="post_id" value="<?= isset($_GET['class']) ? $_GET['class'] : '' ?>">
    <input type="hidden" id="date" value="<?= isset($_GET['date']) ? $_GET['date'] : '' ?>">
    <input type="hidden" id="schedule_id" value="<?= isset($_GET['sd']) ? $_GET['sd'] : '' ?>">
    <input type="hidden" id="nonce" value="<?= $nonce ?>">
    
</div>