
<div class="Row896">
	<div class="RowBox Shadow">
		<div class="headline">
			<p>Direkte Anmeldung...</p>
		</div>
		<form name="loginform" method="post" action="index.php" id="NLFCol">   
			<div class="rowcontent">
				<div class="divclear">
					<div class="w50p">
						<label>Bearbeiter ausw&auml;hlen:</label>
						<select class="NLFCase Case" name="employee">
<?php foreach ($this->get_value("employees") as $e) { ?>
							<option value='<?= $e['name'] ?>'><?= $e['name'] ?></option>
<?php } ?>
						</select>
					</div>
					<div class="clear">
					</div>
				</div>
			</div>
			<div class="rowcontent">
				<div class="w50p">
					<input type="submit" class="NLFStart Button ZoneColor" value="Weiter" />
				</div>
			</div>
		</form>
		<br /><br />
	</div>
</div>
