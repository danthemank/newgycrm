<div id="cc_report" class="pos-page">
    <h1 style="margin-top: 20px;"> No Credit Card List </h1>
    
    <div class="cc-filters flex-container">
        <div class="flex-container">
            <label for="subaccount_filter">Subaccount</label>
            <select id="subaccount_filter" data-page="no_credit_card_list">
                <option value="all" <?= isset($_GET['sub']) && $_GET['sub'] == 'all' ? 'selected' : '' ?>>All</option>
                <option value="has_subaccount" <?= isset($_GET['sub']) && $_GET['sub'] == 'has_subaccount' ? 'selected' : '' ?>>Has Subaccount</option>
                <option value="no_subaccount" <?= isset($_GET['sub']) && $_GET['sub'] == 'no_subaccount' ? 'selected' : '' ?>>No Subaccount</option>
            </select>
        </div>
    
        <div class="flex-container">
            <label for="search_account">Search</label>
            <input type="text" id="search_account">
        </div>
    </div>
    
    <table class="wp-list-table widefat fixed posts">
        <thead>
            <tr>
                <th class="manage-column column-title sortable"><a href="/wp-admin/admin.php?page=no_credit_card_list&<?= isset($_GET['sub']) ? 'sub='.$_GET['sub'].'&' : '&' ?>ord=<?= isset($_GET['ord']) && $_GET['ord'] == 'DESC' ? 'ASC' : 'DESC' ?>&by=first_name">First Name</a></th>
                <th class="manage-column column-title sortable"><a href="/wp-admin/admin.php?page=no_credit_card_list&<?= isset($_GET['sub']) ? 'sub='.$_GET['sub'].'&' : '&' ?>ord=<?= isset($_GET['ord']) && $_GET['ord'] == 'DESC' ? 'ASC' : 'DESC' ?>&by=last_name">Last Name</a></th>
                <th class="manage-column column-title sortable"><a href="/wp-admin/admin.php?page=no_credit_card_list&<?= isset($_GET['sub']) ? 'sub='.$_GET['sub'].'&' : '&' ?>ord=<?= isset($_GET['ord']) && $_GET['ord'] == 'DESC' ? 'ASC' : 'DESC' ?>&by=subaccount">Subaccounts</a></th>
            </tr>
        </thead>
        <tbody>
            <?= $table ?>
        </tbody>
    </table>
</div>