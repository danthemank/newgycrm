<div class="hidden easy-pos-order" id="pos_add_product">

    <form action="" method="POST" class="add-product flex-container">
        <div class="modal-header">
            <h3>Create Product</h3>
        </div>
        <div class="form-section">
            <div class="form-row">
                <label for="product_name">Product Name</label>
                <input type="text" name="product_name" required/>
            </div>
            <div class="form-row">
                <label for="parent_category">Product Category</label>
                <label class="pos-dd">
                    <div class="dd-button">Categories</div>
                    <input type="checkbox" class="dd-input">
                    <ul class="dd-menu">
                        <li class="dd-search"><input type="text" placeholder="Search Category" id="search_dd_category"></li>
                        <?= pos_get_categories() ?>
                    </ul>
                </label>
            </div>
            <div class="form-row">
                <label for="product_description">Product Description</label>
                <input type="text" name="product_description" />
            </div>
            <div class="form-row">
                <label for="product_price">Product Price</label>
                <input type="number" step="0.1" name="product_price" required/>
            </div>
        </div>
        <div class="form-section">
            <button type="submit" class="btn submit-btn add-item" name="save_product">Save</button>
        </div>
    </form>
    
</div>
