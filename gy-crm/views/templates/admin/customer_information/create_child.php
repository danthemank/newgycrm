<div id="admin_create_child">
    <form action="<?= get_permalink() ?>" method="POST">
        <h2>Associate Athlete</h2>
        <table class="form-table" role="presentation">
            <tr class="form-field">
                <th scope="row">
                    <label for="add_athlete">Add Athlete </label>
                </th>
                <td class="flex-container">
                    <select name="add_athlete" class="hidden" id="add_athlete" style="display:none;">
                        <option value="">Select existing Athlete</option>
                        <?php
                            echo $this->get_all_athletes()
                        ?>
                    </select>
                    <input type="submit" class="submit_user_info existing-athlete" name="save_exist_athlete" id="save_child" value="Save">
                </td>
            </tr>
        </table>

    </form>

    <hr class="divider">

    <div class="notice notice-warning is-dismissible hidden"><p>Error: Please enter all fields</p></div>
    <div class="notice notice-success is-dismissible hidden"><p>Success: Sub Account created</p></div>
    <form method="POST" action="<?= get_permalink() ?>">
        <h2>Add Athlete</h2>
        <?php wp_nonce_field('create_child'); ?>
        <table class="form-table" role="presentation">
            <tr class="form-field">
                <th scope="row">
                    <label for="child_first_name">Legal First Name </label>
                </th>
                <td>
                    <input name="child_first_name" type="text" id="child_first_name" required/>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row">
                    <label for="child_last_name">Legal Last Name </label>
                </th>
                <td>
                    <input name="child_last_name" type="text" id="child_last_name" required/>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row">
                    <label for="child_birth">Birth Date </label>
                </th>
                <td>
                    <input name="child_birth" type="date" id="child_birth" required/>
                    <div class="notice notice-warning is-dismissible hidden"><p>Error: Please enter your child's age between 1 and 18 year old.</p></div>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row">
                    <label for="gender">Gender </label>
                </th>
                <td>
                    <select id="gender" name="gender">
                        <option value="Female">Female</option>
                        <option value="Male">Male</option>
                        <option value="Non-Binary">Non-Binary</option>
                        <option value="Prefer not to say">Prefer not to say</option>
                    </select>
                </td>
            </tr>
            <tr class="form-field enrolled-classes">
                <th scope="row">
                    <label for="program_multiselect">Programs</label>
                </th>
                <td>
                    <div>
                        <ul id="enrolled_classes">
                        </ul>
                    </div>
                    <div class="flex-container class-slot-filter">
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
                        <button type="button" class="submit_user_info" data-type="no-auto" id="submit_classes_slots">Add Class</button>
                    </div>
                    <div class="global-success is-dismissible hidden">Success: Enrolled to class succesfully.</div>
                    <div class="global-error is-dismissible hidden"></div>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row">
                    <label for="status_program_participant">Program Status</label>
                </th>
                <td>
                    <select id="status_program_participant" name="status_program_participant" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="pending">Pending</option>
                    </select>
                </td>
            </tr>
        </table>
        <input type="hidden" name="selected_programs" id="selected_programs" required>
        <input type="hidden" name="selected_slots" id="selected_slots" required>

        <input type="submit" class="submit_user_info" name="save_child" id="save_child" value="Create Account">
    </form>
</div>