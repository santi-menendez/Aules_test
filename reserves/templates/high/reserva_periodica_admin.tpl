<form name="f" action='index.php' method=get>
<table width="100%" cellpadding="10" cellspacing="0" class="reserva">
<tr><td height="30" class="titol_reserva">{E_RESERVA} {E_PERIODICA}</td></tr>
<tr><td>
	<table width="100%" cellpadding="4" cellspacing="0">
		<tr><td nowrap class="fgNavy" align="right"><b>{E_RECURS}</b>&nbsp;</td><td width="100%" class="fgRed"><b>{DESC_RECURS}</b></td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_QUI}</b>&nbsp;</td><td><b>{USUARI}</b></td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_ASSIG}</b>&nbsp;</td><td><input type=text name='assig' size=30><br><small><b>(Nom&eacute;s codi de l'assignatura en cas de doc&egrave;ncia reglada)</b></small></td></tr>
		<tr>
				<td nowrap class="fgNavy" align="right"><b>{E_MOTIU}&nbsp;</td>
				<td width="100%"><input type=text name='motiu' size=30>
					<input type=hidden name='op' value='56'>
					<input type=hidden name='recurs' value='{RECURS}'>
				</td>
		</tr>
		<!--<tr>
				<td nowrap class="fgNavy" align="right"><b>{E_PROJECTOR}</b>&nbsp;</td>
				<td><select name='projector'>
					<option value="0">No</option>
					<option value="1">Si</option>
				</td>
		</tr>-->
	</table>

	<p><font class="fgNavy"><b>Nota:</b> {E_MSG_DATES}</font></p>

	<table width="100%" border=0 cellspacing=0 cellpaddign=0>
		<tr>
			<td nowrap><font class="fgNavy"><b>{E_DATA_INICI}</b></font>&nbsp;</td>
			<td width="100%"><input readonly type=text name="data_inici"> <input type="button" onClick="c1.popup('data_inici')" value="Data"></td>
		</tr>
		<tr>
			<td nowrap><font class="fgNavy"><b>{E_DATA_FINAL}</b></font>&nbsp;</td>
			<td width="100%"><input readonly type=text name="data_final"> <input type="button" onClick="c1.popup('data_final')" value="Data"></td>
		</tr>
	</table>

	<p><font class="fgNavy"><b>{E_HORA_INICI}:</b></font>&nbsp;{FRANGES_HORARIES_INICI}&nbsp;&nbsp;&nbsp;
	<font class="fgNavy"><b>{E_HORA_FINAL}:</b></font>&nbsp;{FRANGES_HORARIES_FINAL}</p>

	<p>
	<table width="100%" border=0 cellspacing=0 cellpaddign=0>
	<tr><td valign=top><font class="fgNavy"><b>{E_DIA_SETMANA}</b></font></td></tr>
	<tr><td><br>
			<input type=checkbox name='_1'> <b>{E_M_DILLUNS}</b>&nbsp;
			<input type=checkbox name='_2'> <b>{E_M_DIMARTS}</b>&nbsp;
			<input type=checkbox name='_3'> <b>{E_M_DIMECRES}</b>&nbsp;
			<input type=checkbox name='_4'> <b>{E_M_DIJOUS}</b>&nbsp;
			<input type=checkbox name='_5'> <b>{E_M_DIVENDRES}</b>&nbsp;
			<input type=checkbox name='_6'> <b>{E_M_DISSABTE}</b>&nbsp;
			<input type=checkbox name='_7'> <b>{E_M_DIUMENGE}</b>
	</td></tr>
	</table>
	</p>
		<p>&nbsp;</p>
		<center><input type=submit value='&nbsp;&nbsp;&nbsp;Fer reserva peri&ograve;dica&nbsp;&nbsp;&nbsp;' onClick="{E_AVIS_LEGAL}javascript:window.opener.location.reload()">
		<input type="button" value="Tancar" onClick="javascript:window.opener.location.reload();javascript:window.close()"></center>
</td></tr>
</table>
&nbsp;
</form>
