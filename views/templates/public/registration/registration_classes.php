<div>
    <?= available_programs($athlete_id) ?>
    <input type="hidden" id="classes_<?= $athlete_id ?>" data-name="classes" name="athletes[<?= $athlete_id ?>][classes]">
    <input type="hidden" id="slots_<?= $athlete_id ?>" data-name="slots" name="athletes[<?= $athlete_id ?>][slots]">
    <div class="notice notice-warning is-dismissible hidden"><p>Error: Please select a program.</p></div>
</div>