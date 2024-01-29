<div class="athlete" id="athlete_<?= $athlete_id ?>">
    <div class="flex-container athlete-panel collapse">
        <h2 class="custom-registration-form-heading">Athlete <?= $count ?></h2>
    </div>
    <div>
        <div>
            <p>Athlete Information</p>
            <div class="flex-container input-container-lg hidden">
                <div class="form-section">
                    <div class="form-row">
                        <label for="child_first_name_<?= $athlete_id ?>">Legal First Name *</label>
                        <input type="text" class="reg-input" name="athletes[<?= $athlete_id ?>][child_first_name]" data-name="child_first_name" id="child_first_name_<?= $athlete_id ?>"  required />
                        <div class="notice notice-warning is-dismissible hidden"><p>Error: Please enter the athlete's first name</p></div>
                    </div>
            
                    <div class="form-row">
                        <label for="child_middle_name_<?= $athlete_id ?>">Middle Name</label>
                        <input type="text" name="athletes[<?= $athlete_id ?>][child_middle_name]" data-name="child_middle_name" id="child_middle_name_<?= $athlete_id ?>"/>
                    </div>
                    <div class="form-row">
                        <label for="child_last_name_<?= $athlete_id ?>">Legal Last Name *</label>
                        <input type="text" name="athletes[<?= $athlete_id ?>][child_last_name]" id="child_last_name_<?= $athlete_id ?>" data-name="child_last_name"  required />
                        <div class="notice notice-warning is-dismissible hidden"><p>Error: Please enter the athlete's last name</p></div>
                    </div>
                </div>
                <div class="form-section">
                    <div class="form-row">
                        <label for="gender_<?= $athlete_id ?>">Gender *</label>
                        <select id="gender_<?= $athlete_id ?>" class="reg-input" data-name="gender" name="athletes[<?= $athlete_id ?>][gender]">
                            <option value="">Select...</option>
                            <option value="Female">Female</option>
                            <option value="Male">Male</option>
                            <option value="Non-Binary">Non-Binary</option>
                            <option value="Prefer not to say">Prefer not to say</option>
                        </select>
                        <div class="notice notice-warning is-dismissible hidden"><p>Please enter the athlete's gender.</p></div>
                    </div>
                    <div class="form-row">
                        <label for="child_birth_<?= $athlete_id ?>">Date of Birth *</label>
                        <input type="date" class="reg-input" name="athletes[<?= $athlete_id ?>][child_birth]" data-name="child_birth" id="child_birth_<?= $athlete_id ?>" required />
                        <div class="notice notice-warning is-dismissible hidden"><p>Please enter the athlete's date of birth.</p></div>
                    </div>
                </div>
            </div>
        </div>


        <div class="registration_athlete_enroll">
            <div style="padding-bottom: 1rem;">
                <input type="checkbox" data-name="enrolled" data-athlete="<?=$athlete_id?>" data-id="#private_athlete_<?=$athlete_id?>" class="check-btn hidden" name="athletes[<?= $athlete_id ?>][enrolled][]" value="lessons" id="enroll_private_<?=$athlete_id?>">
                <label for="enroll_private_<?=$athlete_id?>" class="flex-container">
                    <svg id="private_athlete_<?=$athlete_id?>_unchecked" width="25px" height="25px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <circle cx="12" cy="12" r="10" stroke="#ffffff" stroke-width="1.5"></circle> <path d="M8.5 12.5L10.5 14.5L15.5 9.5" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
                    <svg id="private_athlete_<?=$athlete_id?>_checked" class="hidden" width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12ZM16.0303 8.96967C16.3232 9.26256 16.3232 9.73744 16.0303 10.0303L11.0303 15.0303C10.7374 15.3232 10.2626 15.3232 9.96967 15.0303L7.96967 13.0303C7.67678 12.7374 7.67678 12.2626 7.96967 11.9697C8.26256 11.6768 8.73744 11.6768 9.03033 11.9697L10.5 13.4393L12.7348 11.2045L14.9697 8.96967C15.2626 8.67678 15.7374 8.67678 16.0303 8.96967Z" fill="#D8782D"></path> </g></svg>
                    <span>Private Lessons</span>
                    <div id="private_athlete_<?=$athlete_id?>_loader" class="absolute hidden">
                        <div class="lds-ring">
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </div>
                </label>
                <div id="private_athlete_<?=$athlete_id?>_full" class="notice notice-warning is-dismissible hidden"><p>We are sorry. Private Lessons are full at the moment.</p></div>
            </div>
        <div>
            <input type="checkbox" data-name="enrolled" data-athlete="<?=$athlete_id?>" data-id="#programs_athlete_<?=$athlete_id?>" class="programs-checked check-btn hidden" name="athletes[<?= $athlete_id ?>][enrolled][]" value="classes" id="enroll_programs_<?=$athlete_id?>">
            <label for="enroll_programs_<?=$athlete_id?>" class="flex-container">
                <svg id="programs_athlete_<?=$athlete_id?>_unchecked" width="25px" height="25px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <circle cx="12" cy="12" r="10" stroke="#ffffff" stroke-width="1.5"></circle> <path d="M8.5 12.5L10.5 14.5L15.5 9.5" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
                <svg id="programs_athlete_<?=$athlete_id?>_checked" class="hidden" width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12ZM16.0303 8.96967C16.3232 9.26256 16.3232 9.73744 16.0303 10.0303L11.0303 15.0303C10.7374 15.3232 10.2626 15.3232 9.96967 15.0303L7.96967 13.0303C7.67678 12.7374 7.67678 12.2626 7.96967 11.9697C8.26256 11.6768 8.73744 11.6768 9.03033 11.9697L10.5 13.4393L12.7348 11.2045L14.9697 8.96967C15.2626 8.67678 15.7374 8.67678 16.0303 8.96967Z" fill="#D8782D"></path> </g></svg>
                <span>Classes</span>
                <div id="programs_athlete_<?=$athlete_id?>_loader" class="absolute hidden">
                    <div class="lds-ring">
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                </div>
            </label>
        </div>
    </div>
        
        <div id="private_athlete_<?= $athlete_id ?>_container">
        </div>
        <div id="programs_athlete_<?= $athlete_id ?>_container">
        </div>

        <div class="flex-container enrollment-warning-container" id="athlete_<?=$athlete_id?>_enrollment_warning">
            <div class="enrollment-warning hidden">
                <div class="flex-container">
                    <svg viewBox="0 0 1024 1024" width="20px" height="20px" xmlns="http://www.w3.org/2000/svg" fill="#008000" stroke="#008000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path fill="#008000" d="M512 64a448 448 0 1 1 0 896 448 448 0 0 1 0-896zm0 192a58.432 58.432 0 0 0-58.24 63.744l23.36 256.384a35.072 35.072 0 0 0 69.76 0l23.296-256.384A58.432 58.432 0 0 0 512 256zm0 512a51.2 51.2 0 1 0 0-102.4 51.2 51.2 0 0 0 0 102.4z"></path></g></svg>
                    <div class="warning"><div>You have chosen <span></span></div></div>
                </div>
            </div>
            <?php if ($athlete_id !== 0) { ?>
                <div class="remove-athlete submit_user_info" data-id="athlete_<?= $athlete_id ?>">Remove Athlete</div>
            <?php } ?>
        </div>
        
    </div>
</div>