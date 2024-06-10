<div class="update-category">
    <h1>Manage Categories</h1>
    <form action="">
        <table class="wp-list-table widefat fixed posts centered-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Parent Category</th>
                    <th>Description</th>
                    <th colspan="2">Action</th>
                </tr>
            </thead>
            <tbody>
                <?= get_categories_info() ?>
            </tbody>
        </table>
    </form>

    <div class="hidden" id="confirm_delete_category">
        <form method="post" action="" class="flex-container confirm-delete custom-modal">
            <h2>Are you sure you want to delete <span id="cat_name"></span>?</h2>
            
            <input type="hidden" id="cat_id">

            <div class="flex-container confirm-action">
                <input type="button" class="submit_user_info confirm-delete" value="Delete">
                <button class="submit_user_info cancel-btn" type="button">Cancel</button>
            </div>
        </form>
    </div>

</div>