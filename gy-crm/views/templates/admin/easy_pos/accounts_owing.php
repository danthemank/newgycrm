<section id="accounts_owing">
    <h1 style="margin-top: 20px;">Accounts Owing</h1><br>
    <div>
        <div class="flex-container">
            <label for="card_filter">Credit Card</label>
            <select id="card_filter">
                <option value="">Select Option</option>
                <option value="file" <?= isset($_GET['card']) && $_GET['card'] == 'file' ? 'selected' : '' ?>>On File</option>
                <option value="not_file" <?= isset($_GET['card']) && $_GET['card'] == 'not_file' ? 'selected' : '' ?>>Not on File</option>
            </select>
        </div>
    </div>
    <table class="user_balance_table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Amount</th>
                <th>Profile</th>
            </tr>
            </thead>
            <tbody>
                <?php
            foreach ($clients as $client){
                if ($client->balance < 0 ){
                    $client_id = $client->ID; // Obtener el ID del cliente
                    $client_name = $client->usermeta['first_name'][0] . ' ' . $client->usermeta['last_name'][0];
                    $pos_admin_page_url = site_url('/wp-admin/admin.php?page=pos-admin-page&id=' . $client_id);
                    $customer_info = (site_url('wp-admin/admin.php?page=user-information-edit&user='. $client_id .'&child=no'));
                    echo '<tr>';
                    echo '<td><a href="' . esc_url($pos_admin_page_url) . '">' . esc_html($client_name) . '</a></td>';
                    echo '<td>'. $client->balance .'</td>';
                    echo '<td><a href="' . esc_url($customer_info) . '">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        class="your-custom-class" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    </a></td>
                    </tr>';
                }
            }
            ?>
        </tbody>
    </table>
</section>