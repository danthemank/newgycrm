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
            if (!empty($not_in_class)) {
                foreach($not_in_class as $key => $user) {
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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