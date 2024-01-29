<?php
$user = get_userdata($user_meta['smuac_account_parent'][0])->ID;
$name = get_user_meta($user, "first_name");
$last_name = get_user_meta($user, "last_name");
$name = $name[0]. ' ' .$last_name[0];
?>

<main class="two_columns athlete-profile">
    <div class="right user_form">
        <?php
        $is_capable = get_customer_information::get_customer_information_capability('edit_customer_information_children_parents');
        
        if ($is_capable) {
            echo '<div class="associated-Parent">
                    <h2>Associated Parent</h2>';
            $is_capable = get_customer_information::get_customer_information_capability('edit_customer_information_parents');
                    if ($is_capable) {
                        echo '<a target="_blank" href="/wp-admin/admin.php?page=user-information-edit&user='.$user_meta['smuac_account_parent'][0].'&child=no">'.$name.'</a>';
                    } else {
                        echo '<a disabled aria-disabled="true">'.$name.'</a>';
                    }
                echo '</div>';
        }

        ?>

        <form action="" method="POST" class="customer-actions-container">
            <h2>Customer Actions</h2>
            <div class="user_field">
                <label for="action_required">Action</label>
                <select name="action_required" id="action_required" class="user-actions">
                    <option value="">Select Action</option>
                    <option value="Move Up">Move Up</option>
                    <option value="Size Uniform">Size Uniform</option>
                    <option value="Billing Action">Billing Action</option>
                    <option value="Follow Up">Follow Up</option>
                </select>
            </div>
            <div class="user_field">
                <label for="action_name">Responsibility of</label>
                <select name="action_name" id="action_name">
                    <option value="">Select</option>
                    <option value="Mr. A">Mr. A</option>
                    <option value="Ms. Stacy">Ms. Stacy</option>
                    <option value="Michael" >Michael</option>
                    <option value="Ms. Betty" >Ms. Betty</option>
                    <option value="Dan Kemper" >Dan Kemper</option>
                    <option value="Office">Office</option>
                    <option value="Marketing">Marketing</option>
                </select>
            </div>

            <div id="current_actions_list">
                <div>
                    <label for="current_actions" class="current-actions">Current Actions</label>
                    <input type="checkbox" id="current_actions" style="display: none;"/>
                    <ul class="actions-list hidden">
                        <?= $this->get_user_actions_list($_GET['user']) ?>
                    </ul>
                </div>
            </div>

            <div>
                <button type="button" data-user="<?= $_GET['user'] ?>" class="add-item save-customer-actions" name="assign_actions">Save Action</button>
                <div class="global-success is-dismissible hidden">Success: Action saved.</div>
            </div>
        </form>
        
        <form method="post" action="">
            <h2>Athlete Information</h2>
            <?php wp_nonce_field('edit_user_info'); ?>

            <div class="user_field update-btn">
            <?php
                if ($is_capable) {
                    ?>
                <input type="submit" class="submit_user_info submit-user-data" name="submit_data" value="Update User">
                <?php 
                    }
                ?>
            </div>

            <div class="user_field">
                <label for="status_program_participant">Program Status</label>
                <?php

                $is_capable = get_customer_information::get_customer_information_capability('edit_customer_information');

                if ($is_capable) {
                    ?>
                    <select id="status_program_participant" class="reg-input" name="status_program_participant">
                            <option value="">None</option>
                            <option value="active" <?php echo (isset($user_meta['status_program_participant'][0]) && $user_meta['status_program_participant'][0] == "active" ? "selected" : "") ?>>Active</option>
                            <option value="inactive" <?php echo (isset($user_meta['status_program_participant'][0]) && $user_meta['status_program_participant'][0] == "inactive" ? "selected" : "") ?>>Inactive</option>
                            <option value="pending" <?php echo (isset($user_meta['status_program_participant'][0]) && $user_meta['status_program_participant'][0] == "pending" ? "selected" : "") ?>>Pending</option>
                            <option value="suspended" <?php echo (isset($user_meta['status_program_participant'][0]) && $user_meta['status_program_participant'][0] == "suspended" ? "selected" : "") ?>>Suspended</option>
                    </select>
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['status_program_participant'][0]) ? $user_meta['status_program_participant'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <div class="user_field">
                <label for="first_name">Legal First Name*</label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="first_name" placeholder="Legal First Name*" value="<?php echo (isset($user_meta['first_name'][0]) ? $user_meta['first_name'][0] : "") ?>"  >
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['first_name'][0]) ? $user_meta['first_name'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <div class="user_field">
                <label for="last_name">Legal Last Name*</label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="last_name" placeholder="Legal Last Name*" value="<?php echo (isset($user_meta['last_name'][0]) ? $user_meta['last_name'][0] : "") ?>"  >
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['last_name'][0]) ? $user_meta['last_name'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <div class="user_field">
                <label for="child_birth">Date of Birth*</label>
                <?php
                if (isset($user_meta['child_birth'])) {
                    $unix = strtotime($user_meta['child_birth'][0]);
                    $actual_date = new DateTime();
                    $difference = $actual_date->getTimestamp() - $unix;
                    $age = $difference / (60 * 60 * 24 * 365);
                    $age = intval($age);   
                }
                if ($is_capable) {
                    ?>
                    
                <input type="date" name="child_birth" style="width: 65%;" placeholder="Date of Birth*" value="<?php echo (isset($user_meta['child_birth'][0]) ? $user_meta['child_birth'][0] : "") ?>"  >
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['child_birth'][0]) ? $user_meta['child_birth'][0] : "-").'</p>';
                    }
                ?>
                <input type="text" name="age" value="Age <?php echo (isset($age) ? $age : "") ?> " style="width: 80px;" disabled  >

            </div>
            <div class="user_field">
                <label for="child_middle_name">Middle Name</label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="child_middle_name" placeholder="Middle Name" value="<?php echo (isset($user_meta['child_middle_name'][0]) ? $user_meta['child_middle_name'][0] : "") ?>">
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['child_middle_name'][0]) ? $user_meta['child_middle_name'][0] : "-").'</p>';
                    }
                ?>
            </div>
           <!--  <div class="user_field">
                <label for="email">Email Address*</label>
                <input type="email" name="user_email" id="user_email" value="<?php echo (isset($user_meta['user_email']) ? $user_meta['user_email'] : "") ?>"  >
            </div> -->
            <div class="user_field">
                <label for="suffix">Suffix</label>
                <?php
                if ($is_capable) {
                    ?>
                <select id="suffix" class="reg-input" name="suffix">
                    <option value="None" <?php echo (isset($user_meta['suffix'][0]) && $user_meta['suffix'][0] == "None" ? "selected" : "") ?>>None</option>
                    <option value="Jr." <?php echo (isset($user_meta['suffix'][0]) && $user_meta['suffix'][0] == "Jr." ? "selected" : "") ?>>Jr.</option>
                    <option value="Sr." <?php echo (isset($user_meta['suffix'][0]) && $user_meta['suffix'][0] == "Sr." ? "selected" : "") ?>>Sr.</option>
                    <option value="I" <?php echo (isset($user_meta['suffix'][0]) && $user_meta['suffix'][0] == "I" ? "selected" : "") ?>>I</option>
                    <option value="II" <?php echo (isset($user_meta['suffix'][0]) && $user_meta['suffix'][0] == "II" ? "selected" : "") ?>>II</option>
                    <option value="III" <?php echo (isset($user_meta['suffix'][0]) && $user_meta['suffix'][0] == "III" ? "selected" : "") ?>>III</option>
                    <option value="IV" <?php echo (isset($user_meta['suffix'][0]) && $user_meta['suffix'][0] == "IV" ? "selected" : "") ?>>IV</option>
                    <option value="V" <?php echo (isset($user_meta['suffix'][0]) && $user_meta['suffix'][0] == "V" ? "selected" : "") ?>>V</option>
                    <option value="Other" <?php echo (isset($user_meta['suffix'][0]) && $user_meta['suffix'][0] == "Other" ? "selected" : "") ?>>Other</option>
                </select>
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['suffix'][0]) ? $user_meta['suffix'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <div class="user_field">
                <label for="preferred_name">Preferred First Name</label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="preferred_name" placeholder="Preferred First Name" value="<?php echo (isset($user_meta['preferred_name'][0]) ? $user_meta['preferred_name'][0] : "") ?>">
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['preferred_name'][0]) ? $user_meta['preferred_name'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <div class="user_field">
                <label for="gender">Gender</label>
                <?php
                if ($is_capable) {
                    ?>
                <select name="gender">
                    <option>Select Gender</option>
                    <option value="Male" <?php echo (isset($user_meta['gender'][0]) && $user_meta['gender'][0] == "Male" ? "selected" : "") ?>>Male</option>
                    <option value="Female" <?php echo (isset($user_meta['gender'][0]) && $user_meta['gender'][0] == "Female" ? "selected" : "") ?>>Female</option>
                    <option value="Non-Binary" <?php echo (isset($user_meta['gender'][0]) && $user_meta['gender'][0] == "Non-binary" ? "selected" : "") ?>>Non-Binary</option>
                    <option value="Prefer not to say" <?php echo (isset($user_meta['gender'][0]) && $user_meta['gender'][0] == "Prefer not to say" ? "selected" : "") ?>>Prefer not to say</option>
                </select>
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['gender'][0]) ? $user_meta['gender'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <div class="user_field">
                <label for="cell_phone">Cell Phone</label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="cell_phone" placeholder="Cell Phone" value="<?php echo (isset($user_meta['cell_phone'][0]) ? $user_meta['cell_phone'][0] : "") ?>">
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['cell_phone'][0]) ? $user_meta['cell_phone'][0] : "-").'</p>';
                    }
                ?>
            </div>

            <div class="user_field">
                <label for="team_level">Team Level</label>
                <?php
                if ($is_capable) {
                    ?>
                <select name="team_level" id="team_level">
                    <?php
                        foreach($this->team_level as $level) {
                            if ($user_meta['team_level'][0] == $level) {
                                echo '<option value="'.$level.'" selected>'.$level.'</option>';
                            } else {
                                echo '<option value="'.$level.'">'.$level.'</option>';
                            }
                        }
                    ?>
                </select>
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['team_level'][0]) ? $user_meta['team_level'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <h2>Parents/Guardians</h2>
            <h2>Guardian 1</h2>
            <div class="user_field">
                <label for="guardian_first_name_1">First Name*</label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="guardian_first_name_1" placeholder="First Name*" value="<?php echo (isset($user_meta['guardian_first_name_1'][0]) ? $user_meta['guardian_first_name_1'][0] : "") ?>"  >
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['guardian_first_name_1'][0]) ? $user_meta['guardian_first_name_1'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <div class="user_field">
                <label for="guardian_last_name_1">Last Name*</label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="guardian_last_name_1" placeholder="Last Name*" value="<?php echo (isset($user_meta['guardian_last_name_1'][0]) ? $user_meta['guardian_last_name_1'][0] : "") ?>"  >
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['guardian_last_name_1'][0]) ? $user_meta['guardian_last_name_1'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <div class="user_field">
                <label for="guardian_home_phone_1">Home Phone</label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="guardian_home_phone_1" placeholder="Home Phone" value="<?php echo (isset($user_meta['guardian_home_phone_1'][0]) ? $user_meta['guardian_home_phone_1'][0] : "") ?>">
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['guardian_home_phone_1'][0]) ? $user_meta['guardian_home_phone_1'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <div class="user_field">
                <label for="guardian_work_phone_1">Work Phone</label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="guardian_work_phone_1" placeholder="Work Phone" value="<?php echo (isset($user_meta['guardian_work_phone_1'][0]) ? $user_meta['guardian_work_phone_1'][0] : "") ?>">
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['guardian_work_phone_1'][0]) ? $user_meta['guardian_work_phone_1'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <div class="user_field">
                <label for="guardian_mobile_phone_1">Mobile Phone</label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="guardian_mobile_phone_1" placeholder="Mobile Phone" value="<?php echo (isset($user_meta['guardian_mobile_phone_1'][0]) ? $user_meta['guardian_mobile_phone_1'][0] : "") ?>">
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['guardian_mobile_phone_1'][0]) ? $user_meta['guardian_mobile_phone_1'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <h2>Guardian 2</h2>
            <div class="user_field">
                <label for="guardian_first_name_2">First Name</label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="guardian_first_name_2" placeholder="First Name" value="<?php echo (isset($user_meta['guardian_first_name_2'][0]) ? $user_meta['guardian_first_name_2'][0] : "") ?>">
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['guardian_first_name_2'][0]) ? $user_meta['guardian_first_name_2'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <div class="user_field">
                <label for="guardian_last_name_2">Last Name</label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="guardian_last_name_2" placeholder="Last Name" value="<?php echo (isset($user_meta['guardian_last_name_2'][0]) ? $user_meta['guardian_last_name_2'][0] : "") ?>">
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['guardian_last_name_2'][0]) ? $user_meta['guardian_last_name_2'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <div class="user_field">
                <label for="guardian_home_phone_2">Home Phone</label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="guardian_home_phone_2" placeholder="Home Phone" value="<?php echo (isset($user_meta['guardian_home_phone_2'][0]) ? $user_meta['guardian_home_phone_2'][0] : "") ?>">
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['guardian_home_phone_2'][0]) ? $user_meta['guardian_home_phone_2'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <div class="user_field">
                <label for="guardian_work_phone_2">Work Phone</label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="guardian_work_phone_2" placeholder="Work Phone" value="<?php echo (isset($user_meta['guardian_work_phone_2'][0]) ? $user_meta['guardian_work_phone_2'][0] : "") ?>">
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['guardian_work_phone_2'][0]) ? $user_meta['guardian_work_phone_2'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <div class="user_field">
                <label for="guardian_mobile_phone_2">Mobile Phone</label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="guardian_mobile_phone_2" placeholder="Mobile Phone" value="<?php echo (isset($user_meta['guardian_mobile_phone_2'][0]) ? $user_meta['guardian_mobile_phone_2'][0] : "") ?>">
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['guardian_mobile_phone_2'][0]) ? $user_meta['guardian_mobile_phone_2'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <h2>Insurance</h2>
            <div class="user_field">
                <label for="insurance_carrier">Insurance Carrier</label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="insurance_carrier" placeholder="Insurance Carrier" value="<?php echo (isset($user_meta['insurance_carrier'][0]) ? $user_meta['insurance_carrier'][0] : "") ?>">
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['insurance_carrier'][0]) ? $user_meta['insurance_carrier'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <div class="user_field">
                <label for="insurance_phone">Insurance Phone</label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="insurance_phone" placeholder="Insurance Phone" value="<?php echo (isset($user_meta['insurance_phone'][0]) ? $user_meta['insurance_phone'][0] : "") ?>">
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['insurance_phone'][0]) ? $user_meta['insurance_phone'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <h2>Emergencies</h2>
            <div class="user_field">
                <label for="emergency_name_1">Emergency Contact Full Name</label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="emergency_name_1" placeholder="Emergency Contact Full Name" value="<?php echo (isset($user_meta['emergency_name_1'][0]) ? $user_meta['emergency_name_1'][0] : "") ?>">
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['emergency_name_1'][0]) ? $user_meta['emergency_name_1'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <div class="user_field">
                <label for="emergency_phone_1">Emergency phone</label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="emergency_phone_1" placeholder="Emergency phone" value="<?php echo (isset($user_meta['emergency_phone_1'][0]) ? $user_meta['emergency_phone_1'][0] : "") ?>">
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['emergency_phone_1'][0]) ? $user_meta['emergency_phone_1'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <div class="user_field">
                <label for="emergency_name_2">Secondary Emergency Contact Full Name</label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="emergency_name_2" placeholder="Secondary Emergency Contact Full Name" value="<?php echo (isset($user_meta['emergency_name_2'][0]) ? $user_meta['emergency_name_2'][0] : "") ?>">
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['emergency_name_2'][0]) ? $user_meta['emergency_name_2'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <div class="user_field">
                <label for="emergency_phone_2">Secondary Phone </label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="emergency_phone_2" placeholder="emergency phone" value="<?php echo (isset($user_meta['emergency_phone_2'][0]) ? $user_meta['emergency_phone_2'][0] : "") ?>">
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['emergency_phone_2'][0]) ? $user_meta['emergency_phone_2'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <h2>Medical</h2>
            <div class="user_field">
                <label for="medic_name">Physician Name</label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="medic_name" placeholder="Physician Name" value="<?php echo (isset($user_meta['medic_name'][0]) ? $user_meta['medic_name'][0] : "") ?>">
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['medic_name'][0]) ? $user_meta['medic_name'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <div class="user_field">
                <label for="medic_phone">Physician Office Phone</label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="medic_phone" placeholder="Physician Office Phone" value="<?php echo (isset($user_meta['medic_phone'][0]) ? $user_meta['medic_phone'][0] : "") ?>">
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['medic_phone'][0]) ? $user_meta['medic_phone'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <div class="user_field">
                <label for="medic_notes">Medical Information / Notes </label>
                <?php
                if ($is_capable) {
                    ?>
                <textarea name="medic_notes" rows="10" cols="50" placeholder="Medical Information / Notes"><?php echo (isset($user_meta['medic_notes'][0]) ? $user_meta['medic_notes'][0] : "") ?></textarea>
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['medic_notes'][0]) ? $user_meta['medic_notes'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <div class="user_field">
                <label for="medication">Medication</label>
                <?php
                if ($is_capable) {
                    ?>
                <input type="text" name="medication" placeholder="Medication" value="<?php echo (isset($user_meta['medication'][0]) ? $user_meta['medication'][0] : "") ?>">
                <?php 
                    } else {
                        echo '<p>'.(isset($user_meta['medication'][0]) ? $user_meta['medication'][0] : "-").'</p>';
                    }
                ?>
            </div>
            <div class="user_field update-btn">
            <?php
                if ($is_capable) {
                    ?>
                <input type="submit" class="submit_user_info" name="submit_data" value="Update User">
                <?php 
                    }
                ?>
            </div>
        </form>
    </div>
    <div class="left">
        <div class="user_form" id="athlete_tags">
            <input type="hidden" id="athlete_tags_id" value="<?= $_GET['user'] ?>">
            <h3>Athlete Tags</h3>
            <input type="hidden" id="tag_user" value="<?= $_GET['user'] ?>">
            <div class="flex-container">
                <ul class="tags-container flex-container">
                    <?php
                    $athlete_tags = get_user_meta($_GET['user'], 'athlete_tags', true);
                    $athlete_tags = explode(',', $athlete_tags);

                    foreach($athlete_tags as $tag) {
                        if (!empty($tag)) {
                            $term = get_term($tag);
                            ?><li class="flex-container" id="<?= $term->term_id ?>"><a target="_blank" href="/wp-admin/admin.php?page=user-information-children&tag=<?= $term->term_id ?>"><span><?= $term->name ?></span></a><button type="button" data-id="<?= $term->term_id ?>" class="delete-tag-item-icon"></button></li><?php
                        }
                    }
                    ?>
                </ul>
                <div class="add-tag">
                    <div class="add-tag-dropdown">
                        <input type="text" id="add_athlete_tag">
                        <div class="dropdown hidden" id="tags_dropdown">
                            <ul>
                                <?= get_athlete_tags(true) ?>
                            </ul>
                        </div>
                    </div>
                    <div><input type="button" id="add_new_tag" value="Add Tag"></div>
                </div>
            </div>
        </div>

        <div class="user_form enrolled-classes">
            <h3>Enrolled classes</h3>
            <div>
                <ul id="enrolled_classes">
                    <?= $this->get_athletes_classes($_GET['user'], ['li' => 1]) ?>
                </ul>
            </div>
            <div class="user_field flex-container class-slot-filter">
                <?php
                $selected_programs = get_user_meta($_GET['user'], 'classes', true);
                $slot_ids = get_user_meta($_GET['user'], 'slots', true);

                if (isset($selected_programs[0]) && is_array($selected_programs[0])) {
                    $selected_programs = $selected_programs[0];
                }

                if (isset($slot_ids[0]) && is_array($slot_ids[0])) {
                    $slot_ids = $slot_ids[0];
                }

                if ($is_capable) {
                    ?>
                <div class="flex-container class-choice">
                    <label for="class-filter-dropdown">Select Class:</label>
                    <select id="class-filter-dropdown">
                        <?= ProgramStatus::get_classes() ?>
                    </select>
                </div>

                <div class="flex-container class-choice">
                    <label for="slot-filter-dropdown">Select Slot:</label>
                    <select id="slot-filter-dropdown">
                    </select>
                </div>

                <input type="hidden" id="selected_programs" value="<?= !empty($selected_programs) ? implode(',', $selected_programs) : '' ?>">
                <input type="hidden" id="selected_slots" value="<?= !empty($slot_ids) ? implode(',', $slot_ids) : '' ?>">
                <input type="hidden" id="athlete_enroll_id" value="<?= $_GET['user'] ?>">
                <button type="button" class="submit_user_info" id="submit_classes_slots">Enroll</button>
                <?php
                    }
                ?>
            </div>
            <div class="global-success is-dismissible hidden"></div>
            <div class="global-error is-dismissible hidden"></div>
        </div>

        <div class="user_form">
            <div>
                <h3>Attendance History</h3>
                <select id="attendance_history">
                    <?= get_attendance_history($_GET['user']) ?>
                </select>
            </div>
        </div>
    </div>

    <div class="hidden" id="confirm_delete_class">
        <form method="post" action="" class="flex-container confirm-delete custom-modal">
            <div class="modal-header">
                <h2 style="text-align: center;">Are you sure you want to unenroll <?php $user = get_user_by('id', $_GET['user']); echo $user->first_name; ?> from this class?</h2>
            </div>
            
            <input type="hidden" id="delete_class_id">
            <input type="hidden" id="delete_slot_id">

            <div class="flex-container confirm-action">
                <input type="button" class="submit_user_info confirm-delete" id="confirm_delete_class_btn" value="Unenroll">
                <button class="submit_user_info cancel-btn" type="button">Cancel</button>
            </div>
        </form>
    </div>
</main>