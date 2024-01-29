<div id="in_class">

    <div class="flex-container">
        <div class="flex-container filters-containers">
            <div>
                <?php $current_time = current_datetime();?>
                <label for="attendance-date-filter">Select Date:</label>
                <input type="date" id="attendance-date-filter" name="attendance-date-filter" value="<?= isset($_GET['date']) ? $_GET['date'] : $current_time->format('Y-m-d');  ?>">
            </div>
            <div class="flex-container attendance-class-filter" id="class-filter">
                <label for="class-filter-dropdown">Select Class:</label>
                <select id="class-filter-dropdown">

                </select>
            </div>
            <div class="flex-container attendance-slot-filter">
                <div class="hidden" id="slot-filter">
                    <label for="slot-filter-dropdown">Select Slot:</label>
                    <select id="slot-filter-dropdown">
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div>
        <form class="class-search" style="margin-bottom: 0 !important;" action="<?= get_permalink() ?>">
            <label class="screen-reader-text" for="search_id-search-input">Search:</label>
            <div data-id="in_class">
                <input type="search" class="search-bar" name="search">
                <button type="submit" class="search-submit" class="button">Search</button>
            </div>
        </form>
    </div>

    <h3>
        <?= isset($_GET['class']) && !$is_not_date ? $this->get_classes($_GET['class']) : 'Select Class'  ?>
    </h3>

    <table class="wp-list-table widefat fixed posts">
        <thead>
            <tr>
                <th class="manage-column column-title sortable">
                    <a class="order-filter" href="<?=
                        $this->get_links();
                    ?>">Name</a>
                </th>
                <th>Attendance</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th class="manage-column column-title sortable">
                    <a class="order-filter" href="<?=
                        $this->get_links();
                    ?>">Name</a>
                </th>
                <th>Attendance</th>
                <th>Notes</th>
            </tr>
        </tfoot>
        <tbody>
            <?php
            if (!empty($in_class)) {
                foreach($in_class as $key => $user) {
                    $balance = 0;
                    $parent = get_user_meta($user->ID, 'smuac_account_parent', true);
                    if (!empty($parent)) {
                        $parent_user = get_user_by('id', $parent);
                        $full_name = $parent_user->first_name . ' '. $parent_user->last_name;
                        $balance = get_invoice_balance($parent)['amount'];
                    } 
                echo '<tr>
                        <td>'.$user->name.'</td>
                        <td class="flex-container attendance-options">
                            <div class="flex-container">
                                <label for="absent_attendance">Absent</label>
                                <input type="radio" name="save_attendance-'.$key.'"';
                                echo isset($user->attendance) && $user->attendance == 'absent' ? 'checked' : '';
                                echo ' data-user="'.$user->ID.'" value="absent" id="absent_attendance">
                            </div>
                            <div class="flex-container">
                                <label for="present_attendance">Present</label>
                                <input type="radio" name="save_attendance-'.$key.'"';
                                echo isset($user->attendance) && $user->attendance == 'present' ? 'checked' : '';
                                echo ' data-user="'.$user->ID.'" value="present" id="present_attendance">
                            </div>
                        </td>
                        <td class="attendance-note">';
                            if ($balance !== 0) {
                                echo '<svg xmlns="http://www.w3.org/2000/svg" height="25" width="25" viewBox="0 0 448 512"><path fill="#d8782d" d="M64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H288V368c0-26.5 21.5-48 48-48H448V96c0-35.3-28.7-64-64-64H64zM448 352H402.7 336c-8.8 0-16 7.2-16 16v66.7V480l32-32 64-64 32-32z"/></svg>
                                        <div class="hidden">'.$full_name.'\'s account due for '.wc_price($balance).'</div>';
                            }
                        echo '</td>
                    </tr>';
                }
            } else {
                echo '<tr class="class_deselected">
                        <td>No items</td>
                        <td></td>
                    </tr>';
            }
            ?>
            <tr class="class_deselected hidden">
                <td>No items</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>