<form action="?action=setmysql" id="db_Form" method="POST">
	<h1>MySQL</h1><br>
	<font size="3"><i>eltarthat egy kicsit a művelet</i></font>
	<br><br>
	<input name="token" type="hidden" value="<?= getToken() ?>">
	<input id="db_host" name="param[host]" type="text" placeholder="Host" title="Kérem adjon meg egy címet" data-rule="HOST,1,50">
	<input id="db_user" name="param[user]" type="text" placeholder="MySQL User" title="Kérem adjon meg a felhasználó nevet a MySQL szerverhez" data-rule="ALPHA_NUM_,1,50">
	<input id="db_pass" name="param[pass]" type="text" placeholder="MySQL Pass" title="Kérem adjon meg a jelszót a MySQL szerverhez" data-rule="ALPHA_NUM_,0,50">
	<input id="db_name" name="param[db]" type="text" placeholder="MySQL DB" title="Kérem adjon meg az adatbázis nevet" data-rule="ALPHA_NUM_,1,50">
	<br>
	<div class="text-center">
		<a href="?action=setup" class="btn btn-blue"> Elöző </a>
		<a class="btn btn-blue" onclick="return checkFormData(db_Form);"> Kapcsolódás </a>
	</div>
</form>