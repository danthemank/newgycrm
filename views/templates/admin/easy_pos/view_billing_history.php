<div class="hidden" id="show_billing_history">
    <div class="modal-header">
        <h3>Billing History - <a href="/wp-admin/admin.php?page=user-information-edit&user=<?= $_GET['user'] ?> &child=no"><?= get_user_by('id', $_GET['user'])->first_name .' '. get_user_by('id', $_GET['user'])->last_name ?></a></h3>
    </div>
    <hr class="divider">

    <div id="billing_history">
        <?= get_invoice_balance($_GET['user'], '', 'no_edit')['table'] ?>
    </div>
</div>
    
