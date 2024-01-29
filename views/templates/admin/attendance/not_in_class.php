<?php

if (isset($not_in_class)) {
?>


<div id="not_in_class">

    <form action="<?= get_permalink() ?>">
        <p class="search-box">
            <label class="screen-reader-text" for="search_id-search-input">Search:</label>
            <div data-id="not_in_class">
                <input type="search" class="search-bar" name="search">
                <button type="submit" class="search-submit" class="button">Search</button>
            </div>
        </p>
    </form>

    <h3> Other </h3>

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
            if (!empty($not_in_class)) {
                foreach($not_in_class as $key => $user) {
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
                                <label for="makeup_attendance">Makeup</label>
                                <input type="checkbox" name="save_attendance_nt-'.$key.'"';
                                echo isset($user->attendance) && $user->attendance == 'makeup' ? 'checked' : '';
                                echo ' data-user="'.$user->ID.'" value="makeup" id="makeup_attendance">
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
<script>
    $(document).ready(function() {
        $('input[type="checkbox"]').change(function() {
            if (this.checked) {
                var checkboxName = $(this).attr('name');
                $(this).val('makeup');
            } else {
                var checkboxName = $(this).attr('name');
                $(this).val('');
            }
        });
    });
</script>


<?php
}
?>