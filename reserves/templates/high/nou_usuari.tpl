<center>
<table border width="100%" cellpadding="10" cellspacing="0" class="reserva">
	<tr><td height="30" class="titol_reserva">Afegir responsable</td></tr></td></tr>
	<tr><td>
		<form action='index.php' method=get>
		<table width="100%" cellpadding="4" cellspacing="0">
			<tr><td nowrap >{E_NOM_USUARI}&nbsp;</td><td width="100%"><input type=text name='nom_usuari' value='' size=30></td></tr>
			<tr>
				<td nowrap align="right">{E_PERFIL}&nbsp;</td>
				<td><select name='perfil'>
					<option value="">----- Seleccionar perfil ------</option>
					<option value="Usuari PDI">Usuari PDI PAS</option>
					<option value="Usuari Sales">Gestor Sala de Juntes i Actes</option>
					<option value="Usuari NT3">Gestor espais NT3</option>
					<option value="CCESAII">Administrador Espais</option>
				</td></tr>
		</table>
		<p>&nbsp;</p>
		<input type=hidden name='op' value='3'>
		<center><input type=submit value='&nbsp;&nbsp;&nbsp;&nbsp;Afegir nou responsable&nbsp;&nbsp;&nbsp;&nbsp;'></center>
		</form>
	</td></tr>
</table>
</center>
