<?php
?>


<div class="plugin_content" style="padding-right: 20px;">
    <div class="wrap">
    </div>
    <h1>Athlete Information</h1>
    <form method="post">
        <?php // Creating an instance
        $table = new Children_table();

        // Prepare table
        $table->prepare_items();

        // Search form
        $table->search_box('Search', 'search_id');

        ?>
        <select id="filter_athlete_tags">
            <option value="">Filter by Tag</option>
            <?= get_athlete_tags() ?>
        </select>
        <?php

        // Display table
        $table->display(); ?>
    </form>
</div>