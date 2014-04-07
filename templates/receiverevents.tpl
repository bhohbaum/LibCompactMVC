<h1>Auswertung für "<?= UTF8::encode($this->get_value("receiver")->email) ?>"</h1>

<div class="breadcrumb clearfix">
	<ul class="clearfix">
			<li><a href="/app/mailinglist">Mailings</a></li>
			<li><a href="/app/mailingedit/<?= UTF8::encode($this->get_value("mailing")->id) ?>"><?= UTF8::encode($this->get_value("mailing")->name) ?></a></li>
			<li><a href="/app/trackingstats/<?= UTF8::encode($this->get_value("mailing")->id) ?>">Auswertung</a></li>
			<li><?= UTF8::encode($this->get_value("receiver")->email) ?></li>
	</ul>
</div>

<table class="dataTable">
	<thead>
		<tr>
			<th>Timestamp</th>
			<th>Event Type</th>
			<th>Mailpart No.</th>
			<th>Link</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($this->get_value("events") as $event) { ?>
			<tr>
				<td><?= $event->te_ts ?></td>
				<td><?= $event->et_name ?></td>
				<td><?= @$event->mailpart->ordinal ?></td>
				<td><?= @$event->mp_link ?></td>
			</tr>
		<?php } ?>
	</tbody>
</table>

<?php /* ?>
<pre>
<?php foreach ($this->get_value("events") as $event) { ?>
	<?= var_export($event, true)."\n" ?>
<?php } ?>
</pre>

<?php */ ?>