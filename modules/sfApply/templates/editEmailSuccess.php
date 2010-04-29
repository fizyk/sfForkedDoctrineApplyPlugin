<?php //TODO! fix this later ?>
<form method="POST" action="<?php echo url_for("sfApply/editEmail") ?>" name="sf_apply_settings_form" id="sf_apply_settings_form">
<ul>
<?php echo $form ?>
<li>
<input type="submit" value="<?php echo __("Save", array(), 'sfForkedApply') ?>" />