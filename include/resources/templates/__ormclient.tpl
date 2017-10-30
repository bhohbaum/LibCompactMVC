<?php foreach ($this->get_value("tables") as $table) { ?>
	//<?= $table ?> ************************************************************************************************
	//constructor
	var <?= $table ?> = function() {
		$DbObject.call(this, '<?= $this->get_value("endpoint_" . $table) ?>');
	};
	//set prototype (derive from $DbObject)
	<?= $table ?>.prototype = Object.create($DbObject.prototype);
	//fix constructor
	<?= $table ?>.prototype.constructor = <?= $table ?>;
	//methods
<?php foreach ($this->get_value("methods_" . $table) as $method) { ?>
<?php if ($this->get_value("method_" . $table. "::" . $method)) { ?>
	<?= $table ?>.prototype.<?= $method ?> = function(cb, param) {
		this.callMethod(cb, "<?= $method ?>", param);
	}
<?php } else { ?>
	<?= $table ?>.prototype.<?= $method ?> = function(cb) {
		this.callMethod(cb, "<?= $method ?>");
	}
<?php } ?>
<?php } ?>


<?php } ?>
