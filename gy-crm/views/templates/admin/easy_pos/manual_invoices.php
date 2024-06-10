<div id="manual_invoices_list" class="pos-page">
    <h1>Manual Invoices List</h1>
    <p>Invoices sent in <strong><?php

    if (isset($_GET['date'])) {
        $date = new DateTime($_GET['date']);
        $month_name = $date->format('F');
    } else {
        $month_name = date('F');
    }

    echo $month_name;
    ?>
    </strong></p>

    <div class="invoices-filters flex-container">
        <div class="flex-container">
            <label for="search_account">Search</label>
            <input type="text" id="search_account">
        </div>
        <div class="flex-container">
            <label for="month_switch">Month</label>
            <input type="month" id="month_switch" value="<?= isset($_GET['date']) ? $_GET['date'] : date('Y-m') ?>">
        </div>
    </div>

    <table class="wp-list-table widefat fixed posts">
        <thead>
            <tr>
                <th class="manage-column column-title sortable"><a href="/wp-admin/admin.php?page=manual_invoices<?= isset($_GET['date']) ? '&date='.$_GET['date'] : '' ?>&ord=<?= isset($_GET['ord']) && $_GET['ord'] == 'DESC' ? 'ASC' : 'DESC' ?>&by=first_name">First Name</a></th>
                <th class="manage-column column-title sortable"><a href="/wp-admin/admin.php?page=manual_invoices<?= isset($_GET['date']) ? '&date='.$_GET['date'] : '' ?>&ord=<?= isset($_GET['ord']) && $_GET['ord'] == 'DESC' ? 'ASC' : 'DESC' ?>&by=last_name">Last Name</a></th>
                <th class="manage-column column-title sortable"><a href="/wp-admin/admin.php?page=manual_invoices<?= isset($_GET['date']) ? '&date='.$_GET['date'] : '' ?>&ord=<?= isset($_GET['ord']) && $_GET['ord'] == 'DESC' ? 'ASC' : 'DESC' ?>&by=invoice_id">Invoice</a></th>
            </tr>
        </thead>
        <tbody>
            <?php
                echo $this->get_monthly_invoices();
            ?>
        </tbody>
    </table>
</div>