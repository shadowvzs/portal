<form  action="?controller=user&action=login" id="login_Form" method="POST" onsubmit="checkFormData(this)">
	<h1> Bejelentkezes </h1><br>
	<input name="token" type="hidden" value="<?= getToken() ?>">
	<input id="login_email" name="param[email]" type="email" placeholder="E-mail cím" title="Kérem egy valós email címet adjon meg" data-rule="EMAIL,6,64" value="<?= $autofill['email'] ?? '' ?>">
	<input id="login_password" name="param[password]" type="password" placeholder="Jelszó" title="Kérem adjon meg egy jelszót, az angol ABC betűit és/-vagy számok felhasználasával (6-32 karakter)" data-rule="ALPHA_NUM,6,64" value="<?= $autofill['password'] ?? '' ?>">
	<br>
	<div class="text-center">
		<a class="btn btn-blue" onclick="return checkFormData(login_Form);"> Rendben </a>
	</div>
</form>

<?php if (!empty($autofill['auto_send'])) { ?>
<script>
	checkFormData(document.getElementById("<?= $autofill['auto_send'] ?>"));
</script>
<?php } ?>