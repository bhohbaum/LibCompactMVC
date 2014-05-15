<h1>Mailings</h1>

<div class="add-new">
	<div class="info">
		<p>
			Verfassen Sie ein <a href="/app/mailingedit/new">neues Mailing</a> oder wählen Sie einen Bestehendes aus der nachfolgenden Liste.
		</p>
	</div>
	<div class="action">
		<a href="/app/mailingedit/new" class="btn btn-primary">Neues Mailing</a>
	</div>
</div>
<div class="add-new">
	<div class="info">
		<p>
			Laden Sie <a href="/app/uploads">Empfängerlisten hoch</a> oder entfernen sie nicht mehr benötigte.
		</p>
	</div>
	<div class="action">
		<a href="/app/uploads" class="btn btn-primary">Uploads verwalten</a>
	</div>
</div>

<!-- 
<div>
	<button class="btn btn-primary" onclick="window.location.href='/app/mailingedit/new'">Neu...</button>
</div>
-->

<table class="dataTable">
	<thead>
		<tr>
			<th>Titel</th>
			<th>Versand am</th>
			<th>Auswertung</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($this->get_value("mailings") as $mailing) { ?>
			<tr>
				<td><a href="/app/mailingedit/<?= $mailing["id"] ?>"><?= UTF8::encode($mailing["name"]) ?></a></td>
				<td><a href="/app/mailingedit/<?= $mailing["id"] ?>"><?= $mailing["send_date"] ?></a></td>
				<td><a href="/app/trackingstats/<?= $mailing["id"] ?>">Tracking</a></td>
			</tr>
		<?php } ?>
	</tbody>
</table>





