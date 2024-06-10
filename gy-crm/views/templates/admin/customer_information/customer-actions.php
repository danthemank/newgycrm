<div id="customers_action_required" class="pos-page">
    <h1>Customer Actions</h1>

    <div class="action-filters">
        <div class="flex-container">
            <div class="flex-container">
                <label for="account_type">Account Type</label>
                <select id="account_type">
                    <option value="b" <?= isset($_GET['t']) && $_GET['t'] !== 'a' ? 'selected' : '' ?>>Billing Accounts</option>
                    <option value="a" <?= isset($_GET['t']) && $_GET['t'] == 'a' ? 'selected' : '' ?>>Athlete Accounts</option>
                    <option value="at" <?= isset($_GET['t']) && $_GET['t'] == 'at' ? 'selected' : '' ?>>Tagged Athlete Accounts</option>
                </select>
            </div>
            <div class="flex-container">
                <label for="action_required">Action Name</label>
                <select id="action_required" class="action-filter">
                    <option value="All">All</option>
                    <option value="Phone Call" <?= isset($_GET['action']) && $_GET['action'] == 'Phone Call' ? 'selected' : '' ?>>Phone Call</option>
                    <option value="Email" <?= isset($_GET['action']) && $_GET['action'] == 'Email' ? 'selected' : '' ?>>Email</option>
                    <option value="Billing Action" <?= isset($_GET['action']) && $_GET['action'] == 'Billing Action' ? 'selected' : '' ?>>Billing Action</option>
                    <option value="Move Up" <?= isset($_GET['action']) && $_GET['action'] == 'Move Up' ? 'selected' : '' ?>>Move Up</option>
                    <option value="Size Uniform" <?= isset($_GET['action']) && $_GET['action'] == 'Size Uniform' ? 'selected' : '' ?>>Size Uniform</option>
                    <option value="Follow Up" <?= isset($_GET['action']) && $_GET['action'] == 'Follow Up' ? 'selected' : '' ?>>Follow Up</option>
                </select>
            </div>
            <div class="flex-container">
                <label for="action_name">Responsability of</label>
                <select name="action_name" id="action_name" class="action-filter">
                    <option value="All">All</option>
                    <option value="Mr. A" <?= isset($_GET['action']) && $_GET['action'] == 'Mr. A' ? 'selected' : '' ?>>Mr. A</option>
                    <option value="Ms. Stacy" <?= isset($_GET['action']) && $_GET['action'] == 'Ms. Stacy' ? 'selected' : '' ?>>Ms. Stacy</option>
                    <option value="Michael"  <?= isset($_GET['action']) && $_GET['action'] == 'Michael' ? 'selected' : '' ?>>Michael</option>
                    <option value="Ms. Betty"  <?= isset($_GET['action']) && $_GET['action'] == 'Ms. Betty' ? 'selected' : '' ?>>Ms. Betty</option>
                    <option value="Dan Kemper"  <?= isset($_GET['action']) && $_GET['action'] == 'Dan Kemper' ? 'selected' : '' ?>>Dan Kemper</option>
                    <option value="Office" <?= isset($_GET['action']) && $_GET['action'] == 'Office' ? 'selected' : '' ?>>Office</option>
                    <option value="Marketing" <?= isset($_GET['action']) && $_GET['action'] == 'Marketing' ? 'selected' : '' ?>>Marketing</option>
                </select>
            </div>
        </div>
        
        <div class="flex-container">
            <div class="flex-container">
                <label for="search_account">Search</label>
                <input type="text" id="search_account">
            </div>
        </div>
    </div>

    <table class="wp-list-table widefat fixed posts">
        <thead>
            <tr>
                <th class="manage-column column-title sortable">
                    <a class="order-filter" href="/wp-admin/admin.php?page=user-information-actions&ord=<?= isset($_GET['ord']) && $_GET['ord'] == 'DESC' ? 'ASC' : 'DESC' ?>&by=first_name<?= (isset($_GET['t']) ? '&t='.$_GET['t'] : '') . (isset($_GET['action']) ? '&action='.$_GET['action'] : '')?>">First Name</a>
                </th>
                <th class="manage-column column-title sortable">
                    <a class="order-filter" href="/wp-admin/admin.php?page=user-information-actions&ord=<?= isset($_GET['ord']) && $_GET['ord'] == 'DESC' ? 'ASC' : 'DESC' ?>&by=last_name<?= (isset($_GET['t']) ? '&t='.$_GET['t'] : '') . (isset($_GET['action']) ? '&action='.$_GET['action'] : '')?>">Last Name</a>
                </th>
                <th class="manage-column column-title sortable">
                    <a class="order-filter" href="/wp-admin/admin.php?page=user-information-actions&ord=<?= isset($_GET['ord']) && $_GET['ord'] == 'DESC' ? 'ASC' : 'DESC' ?>&by=actions<?= (isset($_GET['t']) ? '&t='.$_GET['t'] : '') . (isset($_GET['action']) ? '&action='.$_GET['action'] : '')?>">Action Required</a>
                </th>
                <?php
                if (!isset($_GET['t']) || $_GET['t'] == 'b') {
                    echo '<th class="manage-column column-title sortable">
                            <a class="order-filter" href="/wp-admin/admin.php?page=user-information-actions&ord='. (isset($_GET['ord']) && $_GET['ord'] == 'DESC' ? 'ASC' : 'DESC') .'&by=user_email'. (isset($_GET['t']) ? '&t='.$_GET['t'] : '') . (isset($_GET['action']) ? '&action='.$_GET['action'] : '').'">Email Address</a>
                        </th>
                        <th>Phone Number</th>';
                } else {
                    echo '<th class="manage-column column-title sortable">
                            <a class="order-filter" href="/wp-admin/admin.php?page=user-information-actions&ord='. (isset($_GET['ord']) && $_GET['ord'] == 'DESC' ? 'ASC' : 'DESC') .'&by=tags'. (isset($_GET['t']) ? '&t='.$_GET['t'] : '') . (isset($_GET['action']) ? '&action='.$_GET['action'] : '').'">Tags</a>
                        </th>';
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <?= $this->get_customer_actions() ?>
        </tbody>
    </table>
</div>