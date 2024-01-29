<div id="add-parent-modal" class="hidden">
    <div class="modal-header"></div>
    <div>
        <h2>Add New Customer</h2>
        <div class="parent-registration-form-container">
            <form id="parent_registration_form" class="registration parent-registration-form" action="" method="post">

                <div class="form-row">
                    <label for="first_name">First Name</label>
                    <input type="text" class="reg-input" name="first_name" id="add_first_name" required />
                </div>
                <div class="form-row">
                    <label for="last_name">Last Name</label>
                    <input type="text" class="reg-input" name="last_name" id="add_last_name" required />
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

                <div class="form-row submit-container">
                    <button type="submit" id="save-user-button" class="btn submit-btn" data-form="parent_registration_form">Register</button>
                </div>
            </form>
        </div>
        
        </form>
    </div>
</div>




<div class="plugin_content" style="padding-right: 20px;">
    <div class="wrap">
    </div>
    <h1>Customer Information</h1>
    <button data-modal="#add-parent-modal" class="edit-btn button" style="vertical-align: initial;">Add New Parent</button>
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
