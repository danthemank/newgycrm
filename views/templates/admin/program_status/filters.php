<div id="admin_filters" class="flex-container classes_slots">
    <div class="flex-container class-slot-filter">
        <div class="flex-container program-status-class-filter" id="class-filter">
            <label for="class-filter-dropdown">Select Class:</label>
            <select id="class-filter-dropdown" data-id="classes_slots" data-id="classes_slots">
                <?= self::get_classes($_GET['class'], 'get_class'); ?>
            </select>
        </div>
        <div class="flex-container program-status-class-filter">
            <label for="slot-filter-dropdown">Select Slot:</label>
            <div id="slot_options">
                <label for="slot_checked">Select Option</label>
                <input type="checkbox" id="slot_checked" style="display: none !important;">
                <ul id="slot-filter-dropdown" class="hidden" data-id="classes_slots">
                    <?php
                        if ($_GET['slot'] && $_GET['meta']) {
                            echo $this->get_class_slots($_GET['class']);
                        }
                    ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="flex-container">
        <div>
            <label for="status_filter">Status</label>
            <select id="status_filter">
                <option value="all" <?= isset($_GET['status']) && $_GET['status'] == 'all' ? 'selected' : '' ?>>All</option>
                <option value="active" <?= !isset($_GET['status']) || $_GET['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= isset($_GET['status']) && $_GET['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="pending" <?= isset($_GET['status']) && $_GET['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="suspended" <?= isset($_GET['status']) && $_GET['status'] == 'suspended' ? 'selected' : '' ?>>Suspended</option>
            </select>
        </div>
    </div>
</div>

<div class="flex-container pos-page">
    <label for="search_account">Search</label>
    <input type="text" id="search_account">
</div>
