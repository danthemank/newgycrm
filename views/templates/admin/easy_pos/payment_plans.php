<div id="payment_plans_list">
    <h1>Payment Plans</h1>
    <table class="wp-list-table widefat fixed posts centered-table">
        <thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Months</th>
                <th>Original Balance</th>
                <th>Remaining Balance</th>
            </tr>
        </thead>
        <tbody>
            <?= $this->get_payment_plans() ?>
        </tbody>
    </table>
</div>