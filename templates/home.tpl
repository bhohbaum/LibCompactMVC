test

<?= $this->component("cartcomponent") ?>
<?= $this->get_value("key") ?>
<?php if ($this->is_active("part")) { ?>
	<div>Beispiel</div>
<?php } ?>
