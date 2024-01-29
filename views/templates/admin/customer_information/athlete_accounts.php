<div>
    <h2>Athlete Accounts</h2>
    <div class="athlete-accounts">
        <ul class="tabs">
        <?php
        foreach ($children_list as $key => $value) :
            $name = get_user_meta($value, 'first_name', true);
            echo '<li class="tab" data-user='.$value.'>'.$name.'</li>';
        endforeach;
        ?>
        </ul>
    </div>
    <form method="post" action="" class="athlete-details-container">
        <div class="athlete-details">
            <div class="user_field meta" style="display: flex; align-items: center;">
                <label for="athlete_action_required">Action required</label>
                <select id="athlete_action_required" class="user-actions" data-user="<?= $children_list[1] ?>">
                <?php
                $actions = get_user_meta($children_list[1], 'action_required', true);
                if (!empty($actions)) {
                    foreach($actions as $action) {
                        $role_name = $action['name'] ? wp_roles()->get_names()[ $action['name'] ] : '';
                        echo '<option>"'. $action['action'] .'" '. $role_name. ' ' . $action['date'].'</option>';
                    }
                } else {
                    echo '<option>None</option>';
                }
                ?>
                </select>
                <?php
                $birth = get_user_meta($children_list[1], 'child_birth', true);
                    if (isset($birth)) {
                        $unix = strtotime($birth);
                        $actual_date = new DateTime();
                        $difference = $actual_date->getTimestamp() - $unix;
                        $age = $difference / (60 * 60 * 24 * 365);
                        $age = intval($age);  
                    }
                ?>
                <p id="athlete_age"> Age <?php echo $age; ?></p>
            </div>

 
            <div class="user_field meta">
                <label for="athlete_first_name">First Name</label>
                <p id="athlete_first_name"><?= get_user_meta($children_list[1], 'first_name', true) ?></p>
            </div>
            
            <div class="user_field meta">
                <label for="athlete_last_name">Last Name</label>
                <p id="athlete_last_name"><?= get_user_meta($children_list[1], 'last_name', true) ?></p>
            </div>
            
            <div class="user_field meta">
                <label for="athlete_status">Status</label>
                <p id="athlete_status"><?= get_user_meta($children_list[1], 'status_program_participant', true) ?></p>
            </div>
            
            <div class="user_field">
                <label for="enrolled_classes">Enrolled classes</label>
                <select id="enrolled_classes">
                    <?= $this->get_athletes_classes($children_list[1], ['option' => 1, 'meta' => 'classes_slots']) ?>
                </select>
            </div>
            
            <div class="user_field">
                <label for="attendance_history">Attendance History</label>
                <select name="" id="attendance_history"">
                    <?= get_attendance_history($children_list[1]) ?>
                </select>
            </div>
            
            <div class="user_field meta">
                <label for="athlete_start_date">Start Date</label>
                <p id="athlete_start_date"><?= !empty(get_user_meta($children_list[1], 'start_date', true)) ? get_user_meta($children_list[1], 'start_date', true) : '-' ?></p>
            </div>
            
            <div class="user_field meta">
                <label for="athlete_annual_reg">Annual Registration</label>
                <p id="athlete_annual_reg"><?= !empty(get_user_meta($_GET['user'], 'due_registration_month', true)) ? get_user_meta($_GET['user'], 'due_registration_month', true) : '-' ?></p>
            </div>
            
            <div class="user_field">
                <a target='_blank' class="submit_user_info" href='/wp-admin/admin.php?page=user-information-edit&user=<?= $children_list[1] ?>&child=yes'>View/Edit Athlete</a>
            </div>
        </div>

        <div class="absolute hidden">
            <div class="lds-ring">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
    </form>
</div>