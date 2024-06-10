
<div class="customer-information-container">
<?php

     if ($_GET["child"] == "no") {
        if (isset($_GET['create'])) {
            require GY_CRM_PLUGIN_DIR . 'views/templates/admin/customer_information/create_child.php';
        } else {
            require GY_CRM_PLUGIN_DIR . 'views/templates/admin/customer_information/parent_form.php';
        }
    } else if ($_GET["child"] == "yes") {
        require GY_CRM_PLUGIN_DIR . 'views/templates/admin/customer_information/children_form.php';
     }
?>
</div>


