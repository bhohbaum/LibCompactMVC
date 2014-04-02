<div>
	<form action="index.php" method="post">
		<input type="text" id="email_receiver" name="email_receiver" value="<?= UTF8::encode($this->get_value("email_receiver")) ?>"><br/>
		<input type="submit">
	</form>
</div>
