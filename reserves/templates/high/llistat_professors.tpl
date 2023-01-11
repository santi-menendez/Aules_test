<center>
<table cellspacing="0" cellpadding="8" class="reserva">
<tr><td height="30" class="titol_reserva">{E_LLISTAT} {E_PROFESSOR}</td></tr>
<tr><td>
	<form action='index.php' method=post>
	<table cellpadding="3">
		<tr><td align="right" class="fgNavy">{E_RECURS}&nbsp;</td><td><select name='tipus_reserva'>
					<option value="0">----- Seleccionar tipus d'aules ------</option>
					<option value="3">Sales de presentacions</option>
					<option value="1">Aules per docència</option>
					<option value="2">Aules informàtiques</option>
					<option value="4">Laboratoris</option>
				</td></tr>
		<tr><td align="right" class="fgNavy">{E_DATA}&nbsp;</td><td><b>{DATA}</b></td></tr>
		<tr><td align="right" class="fgNavy">&nbsp;</td><td>&nbsp;</td></tr>
		<tr><td align="right" class="fgNavy">&nbsp;</td><td>Sel·leccioneu un professor per veure les aules on imparteix docència</td></tr>
		<tr><td align="right" class="fgNavy">{E_PROFESSOR}&nbsp;</td><td><b>{USERNAME}</b></td></tr>
		<tr><td align="right" class="fgNavy">&nbsp;</td><td>&nbsp;</td></tr>
		<tr><td align="right" class="fgNavy">&nbsp;</td><td>Sel·leccioneu una franja horaria per veure la ocupació de les aules</td></tr>
		<tr><td align="right" class="fgNavy">{E_HORA_INICI}&nbsp;</td><td>{FRANGES_HORARIES_INICI}</td></tr>
		<tr><td align="right" class="fgNavy">{E_HORA_FINAL}&nbsp;</td><td>{FRANGES_HORARIES_FINAL}</td></tr>
	</table>
	<input type=hidden name='op' value='36'>
	<input type=hidden name='dia' value='{DIA}'>
	<input type=hidden name='mes' value='{MES}'>
	<input type=hidden name='any' value='{ANY}'>
	

	<p>&nbsp;</p>
	<center><input type=submit value='    Veure Llistat    '>&nbsp<input type="button" value="Tancar" onClick="javascript:window.opener.location.reload();javascript:window.close()"></center>
	</form>

</td></tr>
</table>
&nbsp;
</center>