<div id="attendance_tables">
    <div class="notice notice-warning is-dismissible hidden"><p></p></div>

    <a title="Previous Class" class="hidden left-slide attendance-slide">
        <svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2023 Fonticons, Inc.--><path d="M41.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 256 246.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160zm352-160l-160 160c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L301.3 256 438.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0z"/></svg>
    </a>
        
    <div class="attendance-container">
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
    
    <a title="Upcoming Class" class="hidden right-slide attendance-slide">
        <svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2023 Fonticons, Inc.--><path d="M470.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L402.7 256 265.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160zm-352 160l160-160c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L210.7 256 73.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0z"/></svg>
    </a>
    
</div>