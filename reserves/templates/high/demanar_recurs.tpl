<center>
<table cellspacing="0" cellpadding="8" class="reserva">
<tr><td height="30" class="titol_reserva">{E_RECURS}</td></tr>
<tr><td>
	<form action='index.php' method=post>
	<table cellpadding="3">
		<tr><td colspan="2" class="fgNavy">{TXT_DEMANAR_RECURS}</tr>
		<tr height="20"><td colspan="2" class="fgNavy"></tr>
		<tr><td align="right" class="fgNavy"><b>{E_NOM_RECURS}</b>&nbsp;</td><td><input type=text name='nom_recurs' value='' size=30></td></tr>
		<tr><td valign="top" align="right" class="fgNavy"><b>{E_DESCRIPCIO}</b>&nbsp;</td><td><textarea rows=5 cols=27 name='descripcio'>{E_MSG_DEMANAR_RECURS}</textarea><br></td></tr>
		<tr><td align="right" class="fgNavy"><b>{E_RESPONSABLE}</b>&nbsp;</td><td><input type=text name='responsable' value='{RESPONSABLE}' size=30></td></tr>
	</table>
	<p>&nbsp;</p>
	<input type=hidden name='op' value='6'>
	<center><input type=submit value='{E_DEMANAR_RECURS}'>&nbsp<input type="button" value="{E_TANCAR}" onClick="javascript:window.close()"></center>
	</form>
	<p>
</td></tr>
</table>
&nbsp;
</center>
