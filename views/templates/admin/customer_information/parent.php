<div id="add-parent-modal" class="add-parent-modal">
    <div class="add-parent-modal-content">
        <h2>Add New Customer</h2>
        <div class="parent-registration-form-container">
            <form id="parent_registration_form" class="registration parent-registration-form" action="" method="post">

                <!-- Parent Information Fields -->
                <div class="form-row">
                    <label for="first_name">First Name</label>
                    <input type="text" class="reg-input" name="first_name" id="first_name" required />
                </div>
                <div class="form-row">
                    <label for="last_name">Last Name</label>
                    <input type="text" class="reg-input" name="last_name" id="last_name" required />
                </div>
                <div class="form-row">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" required />
                </div>
                <div class="form-row">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required />
                </div>
                <div class="form-row">
                    <label for="password">Password</label>
                    <input type="password" name="password" min="8" id="password" required />
                </div>

                <!-- Submit Button -->
                <div class="form-row submit-container">
                    <button type="submit" id="save-user-button" class="btn submit-btn" data-form="parent_registration_form">Register</button>
                </div>
            </form>
        </div>
        
        </form>
       <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
        id="add-parent-close-modal" style="width: 24px; height: 24px;">
  <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
</svg>

    </div>
</div>




<div class="plugin_content" style="padding-right: 20px;">
    <div class="wrap">
    </div>
    <h1>Customer Information</h1>
    <button id="add-parent-open-modal" class="button" style="vertical-align: initial;">Add New Parent</button>
    <form method="post">
        <?php // Creating an instance
        $table = new Parent_table();

        // Prepare table
        $table->prepare_items();

        // Search form
        $table->search_box('Search', 'search_id');

        // Display table
        $table->display(); ?>
    </form>
</div>
