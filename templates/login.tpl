<style type="text/css">
		
		
			
			.login-container {
				box-sizing: border-box;
				-moz-box-sizing: border-box;
				-webkit-box-sizing: border-box;
				margin: 20px auto;
				width: 400px;
				padding: 20px;
				border: 1px solid #D9D9D9;
				box-shadow: 0px 0px 10px rgba(0,0,0,0.05);
				border-radius: 10px;
				-moz-border-radius: 10px;
				-webkit-border-radius: 10px;
				overflow: hidden;
				background: #FFF;
			}
			
			.text-right {
				text-align: right;
			}
			
			.form-row input[type=text],
			.form-row input[type=password],
			textarea {
				display: block;
				font-size: 14px;
				width: 100%;
				padding: 15px;
				border-radius: 5px;
				border: 1px solid #c3c7c9;
				-webkit-box-sizing: border-box; 
			    -moz-box-sizing: border-box;    
			    box-sizing: border-box;
			    transition: border-color 0.2s;
				-webkit-transition: border-color 0.2s;
				-moz-transition: border-color 0.2s;        
			}
			
			input[type=text]:active, 
			input[type=text]:focus,
			input[type=text]:hover,
			input[type=password]:active, 
			input[type=password]:focus,
			input[type=password]:hover,
			textarea:active,
			textarea:focus, 
			textarea:hover {
				border-color: #00B7D9;
				box-shadow: 0px 0px 4px rgba(0,185,217,0.31);
			}

			.login-logo {
				display: block;
				margin: 0 auto;
			}
			
</style>
		



<div class="container">
			<div class="login-container">
				<form action="/app/login" method="post">
				<div class="login">
						<img class="login-logo" src="/assets/img/php_logo.jpg">
						<p id="errorline" style="color: red">
							<?= UTF8::encode($this->get_value("error")) ?>
						</p>
						<div class="form-row">
							<label for="user">Benutzername</label>
							<input type="text" name="user" placeholder="Benutzername eingeben" autofocus="autofocus" class="form-control full-width" id="benutzer" />
							
						</div>
						<div class="form-row">
							<label for="pass">Passwort</label>
							<input type="password" name="pass" placeholder="Passwort eingeben" class="form-control full-width" id="pass" />
							
						</div>
						<div class="form-row text-right">
							<input type="submit" value="Einloggen" class="btn btn-primary">
						</div>
				</div>
				</form>
			</div>
		</div>
		
		