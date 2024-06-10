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
            </tr>
        </tfoot>
        <tbody>
            <?php
            if (!empty($in_class)) {
                foreach($in_class as $key => $user) {
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