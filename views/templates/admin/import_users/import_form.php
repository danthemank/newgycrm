<h3>Import Users</h3>
<form enctype="multipart/form-data" action="<?= get_permalink() ?>" method="post">
    <?= wp_nonce_field('import_nonce') ?>;
    <div class="flex-container">
        <label for="import_users">Add a XLSX, ODS or CSV File</label>
        <input type="file" accept=".xlsx,.ods,.csv" name="import_users" id="import_users">
    </div>
    <div class="submit-btn"><input type="submit" name="submit_import" value="Import"></div>
</form>

<progress id="progressBar" value="0" max="100"></progress>
