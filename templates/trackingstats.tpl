<h1>Auswertung von "<?= UTF8::encode($this->get_value("mailing")->name) ?>"</h1>
<div class="breadcrumb clearfix">
	<ul class="clearfix">
			<li><a href="/app/mailinglist">Mailings</a></li>
			<li><a href="/app/mailingedit/<?= $this->get_value("mailing")->id ?>"><?= UTF8::encode($this->get_value("mailing")->name) ?></a></li>
			<li>Auswertung</li>
	</ul>
</div>

<table class="dataTable">
	<thead>
		<tr>
			<th><a href="/app/trackingstats/<?= $this->get_value("mailing")->id ?>/r_email/<?= $this->get_value("sort_type") ?>">Empfänger E-Mail</a></th>
			<th><a href="/app/trackingstats/<?= $this->get_value("mailing")->id ?>/opened/<?= $this->get_value("sort_type") ?>">Status</a></th>
			<?php for ($i = 1; $i <= $this->get_value("maxord"); $i++) { ?>
				<th><a href="/app/trackingstats/<?= $this->get_value("mailing")->id ?>/ord_<?= $i ?>/<?= $this->get_value("sort_type") ?>">Klicks auf Abschnitt <?= $i ?></a></th>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
		<?php if ($this->get_value("events") != "") { ?>
			<?php foreach ($this->get_value("events") as $event) { ?>
				<tr>
					<td style="width: 220px"><a href="/app/trackingstats/mail/<?= $event["mhr_id"] ?>"><?= $event["r_email"] ?></a></td>
					<td><a href="/app/trackingstats/mail/<?= $event["mhr_id"] ?>" style="color: <?= ($event["opened"] == 0) ? "red" : "green" ?>"><?= ($event["opened"] == 0) ? "not opened" : "opened" ?></a></td>
					<?php for ($i = 1; $i <= $this->get_value("maxord"); $i++) { ?>
						<?php if (isset($event["ord_".$i])) { ?>
							<td><a href="/app/trackingstats/mail/<?= $event["mhr_id"] ?>"><?= $event["ord_".$i] ?></a></td>
						<?php } else { ?>
							<td><a href="/app/trackingstats/mail/<?= $event["mhr_id"] ?>">0</a></td>
						<?php } ?>
					<?php } ?>
				</tr>
			<?php } ?>
			<tr>
				<td></td>
				<td></td>
			<?php for ($i = 1; $i <= $this->get_value("maxord"); $i++) { ?>
				<?php if ($this->get_value("sum_ord_".$i) != "") { ?>
					<td><?= $this->get_value("sum_ord_".$i) ?></td>
				<?php } else { ?>
					<td>0</td>
				<?php } ?>
			<?php } ?>
			</tr>
		<?php } ?>
	</tbody>
</table>
