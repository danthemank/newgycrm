<div id="admin_filters" class="flex-container class-slot-filter">
    <div class="flex-container program-status-class-filter" id="class-filter">
        <label for="class-filter-dropdown">Select Class:</label>
        <select id="class-filter-dropdown">
            <?= self::get_classes($_GET['class'], 'get_class'); ?>
        </select>
    </div>

    <div class="flex-container program-status-class-filter">
        <div class="hidden" id="slot-filter">
            <label for="slot-filter-dropdown">Select Slot:</label>
            <select id="slot-filter-dropdown">
                <?php
                    if ($_GET['slot'] && $_GET['meta']) {
                        echo $this->get_class_slots($_GET['class'], $_GET['slot']);
                    }
                ?>
            </select>

        </div>
    </div>

    <form action="<?= get_permalink() ?>">
        <p class="search-box">
            <label class="screen-reader-text" for="search_id-search-input">Search:</label>
            <input type="search" id="search_id-search-input" name="search">
            <button type="submit" class="search-submit" class="button">Search</button>
        </p>
    </form>
</div>
