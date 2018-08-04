<form  action="?controller=user&action=signup" id="signup_Form" method="POST" onsubmit="checkFormData(this)">
	<h1>Regisztrálás</h1><br>
	<input name="token" type="hidden" value="<?= getToken() ?>">
	<input id="signup_name" name="param[name]" type="text" placeholder="Teljes név" title="Kérem adja meg a nevét a magyar ABC betűit használva (5-50 karakter)" data-rule="NAME_HUN,6,64">
	<input id="signup_email" name="param[email]" type="email" placeholder="E-mail cím" title="Kérem egy valós email címet adjon meg" data-rule="EMAIL,6,64">
	<input id="signup_password" name="param[password]" type="password" placeholder="Jelszó" title="Kérem adjon meg egy jelszót, az angol ABC betűit és/-vagy számok felhasználasával (6-32 karakter)" data-rule="ALPHA_NUM,6,64">
	<input id="signup_password2" name="param[password2]" type="password" placeholder="Jelszó újra" title="Kérem adjon meg egy jelszót újra ami megegyezik a másik jelszó mezővels" data-same="signup_password"><br>
	<div class="text-center">
		<a class="btn btn-blue" onclick="return checkFormData(signup_Form);"> Rendben </a>
	</div>
</form>