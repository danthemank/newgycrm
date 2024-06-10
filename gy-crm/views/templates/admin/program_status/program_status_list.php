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
                if ( $_GET['slot'] ) {
                    foreach($data as $user) {
                        $sql = "SELECT u.ID, u.display_name
                                FROM $wpdb->users u
                                JOIN $wpdb->usermeta um
                                ON u.ID = um.user_id
                                AND meta_key = 'slots'
                                AND user_id = {$user->ID}
                                AND meta_value LIKE '%{$_GET['slot']}%'"; 
                        $result = $wpdb->get_results($sql);
                        if (isset($result[0]->ID)) {
                            echo '<tr>
                                    <td><a href="/wp-admin/admin.php?page=user-information-edit&user='.$result[0]->ID.'&child=yes">'.$result[0]->display_name.'</a></td>';
                                    if (isset($user->status_program_participant)) {
                                        echo '<td>'.$user->status_program_participant.'</td>';
                                    } else {
                                        echo '<td></td>';
                                    }
                            echo '</tr>';
                        }
                    }
                } else {
                    foreach($data as $user) {
                    echo '<tr>
                            <td><a href="/wp-admin/admin.php?page=user-information-edit&user='.$user->ID.'&child=yes">'.$user->display_name.'</a></td>';
                            if (isset($user->status_program_participant)) {
                                echo '<td>'.$user->status_program_participant.'</td>';
                            } else {
                                echo '<td></td>';
                            }
                        echo '</tr>';
                    }
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