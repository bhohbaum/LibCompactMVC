<?php if ($this->is_active("main")) { ?>
LibCMVC System Help

Available actions:

gendto:         generate missing DTO classes based on current database
genep:          generate missing endpoints for DTOs

cc:             clear all caches
cc_file:        delete combined code cache file
cc_redis:       clear complete redis cache
cc_table:       clear table and query cache (redis)
cc_render:      clear render cache (redis)

<?php } ?><?php if ($this->is_active("newrouting")) { ?>
<?php if (count($this->get_value("tables")) > 0) { ?>

Do not forget do add the new routes!!
<?php } ?>

<?php foreach ($this->get_value("tables") as $table) { ?>
<?php if ($this->get_value("briddepth") == 1) { ?>
		$this->register_ep_2("<?= $this->get_value("bridarr")[0] ?>", "<?= $table ?>", new LinkProperty("/<?= $this->get_value("bridarr")[0] ?>/<?= $table ?>", false, "EP<?= $table ?>"));
<?php } ?>
<?php if ($this->get_value("briddepth") == 2) { ?>
		$this->register_ep_3("<?= $this->get_value("bridarr")[0] ?>", "<?= $this->get_value("bridarr")[1] ?>", "<?= $table ?>", new LinkProperty("/<?= $this->get_value("bridarr")[0] ?>/<?= $this->get_value("bridarr")[1] ?>/<?= $table ?>", false, "EP<?= $table ?>"));
<?php } ?>
<?php if ($this->get_value("briddepth") == 3) { ?>
		$this->register_ep_3("<?= $this->get_value("bridarr")[0] ?>", "<?= $this->get_value("bridarr")[1] ?>", "<?= $this->get_value("bridarr")[2] ?>", new LinkProperty("/<?= $this->get_value("bridarr")[0] ?>/<?= $this->get_value("bridarr")[1] ?>/<?= $this->get_value("bridarr")[2] ?>/<?= $table ?>", false, "EP<?= $table ?>"));
<?php } ?>
<?php } ?>

<?php } ?>
