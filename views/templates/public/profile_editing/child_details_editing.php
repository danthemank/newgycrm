<div class="child-details-editing edit-form" id="child_details">
    <div class="modal-header"></div>
    <form action="<?= get_permalink() ?>" method="post">
        <input type="hidden" id="child_edit_nonce" name="child_edit_nonce" value="<?= $nonce ?>">

        <ul class="nav-tab flex-container">

            <li class="tab">Account
            <div class="hidden content-holder">
                <div>
                    <h3 class="custom-registration-form-heading">Child Account</h3>
                    <div class="form-row flex-container custom-registration-form-field">
                        <div class="form-row">
                            <label for="first_name">Legal First Name</label>
                            <input type="text" class="reg-input" id="child_first_name" name="first_name" />
                        </div>
                        <div class="form-row">
                            <label for="last_name">Legal Last Name</label>
                            <input type="text" class="reg-input" id="child_last_name" name="last_name" />
                        </div>
                    </div>

                </div>
            </div>
            </li>

            <li class="tab">Details
            <div class="hidden content-holder">
                <div>
                    <h3 class="custom-registration-form-heading">Child Details</h3>
                    <div class="form-row flex-container custom-registration-form-field">
                        <div class="form-row">
                            <label for="child-middle-name">Middle Name</label>
                            <input type="text" class="reg-input" name="child_middle_name" id="child_middle_name"/>
                        </div>
                    </div>
            
                    <div class="form-row flex-container input-container-md custom-registration-form-field">
                        <div class="form-row edit-input">
                            <label for="suffix">Suffix</label>
                            <select id="suffix" class="reg-input edit-input" name="suffix">
                                <?php 
                                    foreach($this->suffix_options as $key => $value) {
                                        echo '<option value="'.$key.'">'.$value.'</option>';
                                    }

                                ?>
                            </select>
                        </div>
                        <div class="form-row">
                            <label for="preferred_name">Preferred First Name</label>
                            <input type="text" class="reg-input" name="preferred_name" id="preferred_name"/>
                        </div>
                        <div class="form-row">
                            <label for="gender">Gender</label>
                            <select id="gender" class="reg-input" name="gender">
                                <?php 
                                    foreach($this->gender_options as $key => $value) {
                                        echo '<option value="'.$key.'">'.$value.'</option>';
                                    }?>
                            </select>
                        </div>
                    </div>
            
                    <div class="form-row flex-container input-container-md custom-registration-form-field">
                        <div class="form-row">
                            <label for="child_birth">Date Of Birth</label>
                            <input type="date" class="reg-input" id="child_birth" name="child_birth"  />
                        </div>
                    </div>
                </div>
            </div>
            </li>

            <li class="tab">Guardians
            <div class="hidden content-holder">
                <div class="child-details-form-guardians-section">
                    <h3 class="custom-registration-form-heading">Parents/Guardians</h3>
                    <div class="flex-container input-container-md">
                        <div>
                            <h3>Guardian 1</h3>
                            <div class="form-row flex-container custom-registration-form-field">
                                <div class="form-row">
                                    <label for="guardian_first_name_1">First Name</label>
                                    <input type="text" class="reg-input" id="guardian_first_name_1" name="guardian_first_name_1"   />
                                    <div class="notice notice-warning is-dismissible hidden"><p>Please enter a guardian's first name.</p></div>
                                </div>
                                <div class="form-row">
                                    <label for="guardian_last_name_1">Last Name</label>
                                    <input type="text" class="reg-input" id="guardian_last_name_1" name="guardian_last_name_1"  />
                                    <div class="notice notice-warning is-dismissible hidden"><p>Please enter a guardian's phone.</p></div>
                                </div>
                            </div>
                            <div class="form-row flex-container input-container-md custom-registration-form-field">
                                <div class="form-row">
                                    <label for="guardian_home_phone_1">Home Phone</label>
                                    <input type="tel" class="reg-input" id="guardian_home_phone_1" name="guardian_home_phone_1"/>
                                </div>
                                <div class="form-row">
                                    <label for="guardian_work_phone_1">Work Phone</label>
                                    <input type="tel" class="reg-input" id="guardian_work_phone_1" name="guardian_work_phone_1"/>
                                </div>
                                <div class="form-row">
                                    <label for="guardian_mobile_phone_1">Mobile Phone</label>
                                    <input type="tel" class="reg-input" id="guardian_mobile_phone_1" name="guardian_mobile_phone_1"/>
                                </div>
                            </div>
                        </div>
            
                        <div>
                            <h3>Guardian 2</h3>
                            <div class="form-row flex-container custom-registration-form-field">
                                <div class="form-row">
                                    <label for="guardian_first_name_2">First Name</label>
                                    <input type="text" class="reg-input" id="guardian_first_name_2" name="guardian_first_name_2"/>
                                </div>
                                <div class="form-row">
                                    <label for="guardian_last_name_2">Last Name</label>
                                    <input type="text" class="reg-input" id="guardian_last_name_2" name="guardian_last_name_2"/>
                                </div>
                            </div>
                            <div class="form-row flex-container input-container-md custom-registration-form-field">
                                <div class="form-row">
                                    <label for="guardian_home_phone_2">Home Phone</label>
                                    <input type="tel" class="reg-input" id="guardian_home_phone_2" name="guardian_home_phone_2"/>
                                </div>
                                <div class="form-row">
                                    <label for="guardian_work_phone_2">Work Phone</label>
                                    <input type="ntel" class="reg-input" id="guardian_work_phone_2" name="guardian_work_phone_2"/>
                                </div>
                                <div class="form-row">
                                    <label for="guardian_mobile_phone_2">Mobile Phone</label>
                                    <input type="tel" class="reg-input" id="guardian_mobile_phone_2" name="guardian_mobile_phone_2"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </li>

            <li class="tab">Insurance
            <div class="hidden content-holder">
                <div>
                    <h3>Insurance</h3>
                    <div class="form-row flex-container custom-registration-form-field">
                        <div class="form-row">
                            <label for="insurance_carrier">Insurance Carrier</label>
                            <input type="text" class="reg-input" id="insurance_carrier" name="insurance_carrier"/>
                        </div>
                        <div class="form-row">
                            <label for="insurance_phone">Insurance Phone</label>
                            <input type="tel" class="reg-input" id="insurance_phone" name="insurance_phone"/>
                        </div>
                    </div>
                </div>
            </div>
            </li>
            

            <li class="tab">Emergency
            <div class="hidden content-holder">
                <div>
                    <h3>Emergencies</h3>
                    <div class="form-row input-container-md flex-container custom-registration-form-field">
                        <div class="form-row">
                            <label for="emergency_name_1">Emergency Contact Full Name</label>
                            <input type="text" class="reg-input" id="emergency_name_1" name="emergency_name_1"  />
                            <div class="notice notice-warning is-dismissible hidden"><p>Please enter an emergency contact name.</p></div>
                        </div>
                        <div class="form-row">
                            <label for="emergency_phone_1">Emergency Phone</label>
                            <input type="tel" class="reg-input" id="emergency_phone_1" name="emergency_phone_1"  />
                            <div class="notice notice-warning is-dismissible hidden"><p>Please enter an emergency contact phone.</p></div>
                        </div>
                    </div>
                    <div class="form-row flex-container input-container-md custom-registration-form-field">
                    <div class="form-row">
                            <label for="emergency_name_2">Secondary Emergency Contact Full Name</label>
                            <input type="text" class="reg-input" id="emergency_name_2" name="emergency_name_2"/>
                        </div>
                        <div class="form-row">
                            <label for="emergency_phone_2">Secondary Emergency Phone</label>
                            <input type="tel" class="reg-input" id="emergency_phone_2" name="emergency_phone_2"/>
                        </div>
                    </div>
                </div>
            </div>
            </li>
            



            <li class="tab">Medical
            <div class="hidden content-holder">
                    <div>
                        <h3>Medical</h3>
                        <div class="form-row flex-container input-container custom-registration-form-field">
                            <div class="form-row">
                                <label for="medic_name">Physician Name</label>
                                <input type="text" class="reg-input" id="medic_name" name="medic_name"/>
                            </div>
                            <div class="form-row">
                                <label for="medic_phone">Physician Office Phone</label>
                                <input type="number" class="reg-input" id="medic_phone" name="medic_phone"/>
                            </div>
                        </div>
                        <div>
                            <div class="form-row">
                                <label for="medic_notes">Medical Information / Notes</label>
                                <textarea name="medic_notes" id="medic_notes" cols="30" rows="3"></textarea>
                            </div>
                        </div>
                        <div>
                            <div class="form-row">
                                <label for="medication">Medication</label>
                                <textarea class="reg-input" id="medication" cols="30" rows="3" name="medication"/></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                </li>
            </ul>
                
            <div class="form-row submit-container">
                <input type="hidden" name="child_id" id="child_id" value="">
                <button type="submit" class="btn submit-btn" data-form="child_details">Update</button>
            </div>
    </form>
</div>
