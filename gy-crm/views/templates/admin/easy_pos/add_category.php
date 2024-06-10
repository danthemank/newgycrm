<div class="hidden easy-pos-order" id="pos_add_category">

    <form action="" method="POST" class="add-category flex-container">
        <div class="modal-header">
            <h3>Create Category</h3>
        </div>
        <div class="form-section">
            <div class="form-row">
                <label for="category_name">Category Name</label>
                <input type="text" name="category_name" required/>
            </div>
            <div class="form-row">
                <label for="parent_category">Parent Category</label>
                <select id="parent_category" name="parent_category">
                    <option value="">Select Category</option>
                    <?= pos_get_categories(array('option' => 1)) ?>
                </select>
            </div>
            <div class="form-row">
                <label for="category_description">Category Description</label>
                <input type="text" name="category_description" />
            </div>
        </div>
        <div class="form-section">
            <button type="submit" class="btn submit-btn add-item" name="save_category">Save</button>
        </div>
    </form>
    
</div>
