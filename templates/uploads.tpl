<h1>Uploads</h1>
<div class="breadcrumb clearfix">
	<ul class="clearfix">
		<li><a href="/app/mailinglist">Mailings</a></li>
		<li><a href="/app/uploads">Uploads</a></li>
	</ul>
</div>
<div>
	<div class="form-row">
		<form id="form" name="form" action="/app/uploads" method="post" enctype="multipart/form-data">
			<label for="file">Datei hochladen</label>
			<input type="file" id="file" name="file" onchange="document.getElementById('form').submit()"></input>
		</form>
	</div>
	<?php foreach ($this->get_value("direntries") as $entry) { ?>
		<div class="form-row">
			<div class="direname">Name: <?= $entry["name"] ?></div>
			<div class="diresize">Größe: <?= $entry["size"] ?> Bytes</div>
			<div class="direlink">Link: <a href="<?= BASE_URL."/files/csv/".$entry["name"] ?>"><?= BASE_URL."/files/csv/".$entry["name"] ?></a></div>
			<div class="action">
				<a href="#" class="btn btn-primary" onclick="delete_file('<?= $entry["name"] ?>')">Löschen</a>
			</div>
		</div>
	<?php } ?>
</div>
<script type="text/javascript">

function delete_file(name) {
	var doit = confirm("Wollen Sie " + name + " wirklich löschen?");
	if (doit) {
		var url = "/app/uploads/" + escape(name);
		var xhr = new XMLHttpRequest();
		xhr.open('DELETE', url, true);
		xhr.onload = function(response) {
			console.log(response.currentTarget.response);
			window.location.reload();
		};
		xhr.send();
	}
}

</script>