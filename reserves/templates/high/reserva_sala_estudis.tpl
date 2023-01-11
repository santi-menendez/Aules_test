<meta charset="UTF-8">
<center>
<table cellspacing="0" cellpadding="8" class="reserva">
<tr><td height="30" class="titol_reserva">{E_RESERVA}</td></tr>
<tr><td>
	<form action='index.php' method=post>
	<table cellpadding="3">
		<tr><td align="right" class="fgNavy">{E_RECURS}&nbsp;</td><td><b>{DESC_RECURS}</b></td></tr>
		<tr><td align="right" class="fgNavy">{E_DATA}&nbsp;</td><td><b>{DATA}</b></td></tr>
		<tr><td align="right" class="fgNavy">{E_QUI}&nbsp;</td><td><b>{USERNAME}</b></td></tr>
		<tr><td align="right" class="fgNavy">{E_HORA_INICI}&nbsp;</td><td>{FRANGES_HORARIES_INICI}</td></tr>
		<tr><td align="right" class="fgNavy">{E_HORA_FINAL}&nbsp;</td><td>{FRANGES_HORARIES_FINAL}</td></tr>
		<tr><td align="right" class="fgNavy">{E_ASSIG}&nbsp;</td><td><input type=text name='assig' value='{ASSIG}' size=30><br><small><b>(Nom&eacute;s codi de l'assignatura en cas de doc&egrave;ncia reglada)</b></small></td></tr>
		<tr><td align="right" class="fgNavy">{E_MOTIU}&nbsp;</td><td><input type=text name='motiu' value='{MOTIU}' size=30><br></td></tr>
		<!--<tr>
		
				<td nowrap align="right" class="fgNavy"><b>{E_PROJECTOR}</b>&nbsp;</td>
				<td><select name='projector'>
					<option value="0">No</option>
					<option value="1">Si</option>
				</td>
		</tr>-->
	</table>
	<input type=hidden name='op' value='51'>
	<input type=hidden name='recurs' value='{RECURS}'>
	<input type=hidden name='qui' value='{USERNAME}'>
	<input type=hidden name='dia' value='{DIA}'>
	<input type=hidden name='mes' value='{MES}'>
	<input type=hidden name='any' value='{ANY}'>

	<p>&nbsp;</p>
	<center><input type=submit value='    Fer reserva    ' onClick="{E_AVIS_LEGAL}">&nbsp<input type="button" value="Tancar" onClick="javascript:window.opener.location.reload();javascript:window.close()"></center>
	</form>

</td></tr>
</table>
&nbsp;
</center>