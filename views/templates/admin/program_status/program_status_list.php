<div>
    <h3 id="is_class" data-class="<?= isset($_GET['class']) ? $_GET['class'] : 'no' ?>">
        <?= isset($_GET['class']) && $_GET['class'] !== 'no' ? $this->get_classes($_GET['class'], null) : 'Un-enrolled'  ?>
    </h3>

    <table class="wp-list-table widefat fixed posts">
        <thead>
            <tr>
                <th class="manage-column column-title sortable">
                    <a class="order-filter" href="<?=
                        $this->get_links();
                    ?>">Name</a>
                </th>
                <th>Status</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th class="manage-column column-title sortable">
                    <a class="order-filter" href="<?=
                        $this->get_links();
                    ?>">Name</a>
                </th>
                <th>Status</th>
            </tr>
        </tfoot>
        <tbody>
            <?php
            global $wpdb;
            if (!empty($data)) {
                foreach($data as $user) {
                echo '<tr>
                        <td><a href="/wp-admin/admin.php?page=user-information-edit&user='.$user->ID.'&child=yes">'.$user->name.'</a></td>';
                        if (isset($user->status_program_participant)) {
                            echo '<td>'.$user->status_program_participant.'</td>';
                        } else {
                            echo '<td></td>';
                        }
                    echo '</tr>';
                }
            } else {
                echo '<tr class="class_deselected">
                        <td>No items</td>
                        <td></td>
                    </tr>';
            }
            ?>
        </tbody>
    </table>
</div>