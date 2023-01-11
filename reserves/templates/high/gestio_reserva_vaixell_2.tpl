
<form name="f" action='index.php' method=get accept-charset="UTF-8">
<center>
<table cellpadding="10" cellspacing="0" width="350" class="reserva">
<tr><td height="30" class="titol_reserva">{E_MSG_GEST_RESERVA}</td></tr>
<tr>
	<td>
	<p align="center" class="desc_recurs">{DESC_RECURS}</p>
	<center>
	<table width="90%" cellpadding="0" cellspacing="5" border="0">
		<tr><td nowrap class="fgNavy" align="right"><b>{E_PETICIO}</b>&nbsp;</td><td width="100%">{QUI}<input type=hidden name="qui" value="{QUI}"></td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_DATA_INICI}</b>&nbsp;</td><td width="100%"><input type=hidden name="data_reserva" value="{DATA_RESERVA}">{DATA_RESERVA}</td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_DATA_FINAL}</b>&nbsp;</td><td width="100%"><input type=hidden name="data_final" value="{DATA_FINAL}">{DATA_FINAL}</td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_HORA_INICI}</b>&nbsp;</td><td width="100%"><input type=hidden name="hora_inici" value="{HORA_INICI}">{HORA_INICI}</td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_HORA_FINAL}</b>&nbsp;</td><td width="100%"><input type=hidden name="hora_final" value="{HORA_FINAL}">{HORA_FINAL}</td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_DIA_SETMANA}</b>&nbsp;</td><td width="100%"><input type=hidden name="dia_txt" value="{DIA_TXT}">{DIA_TXT}</td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_PATRO}</b>&nbsp;</td><td width="100%"><input type=hidden name="patro" value="{PATRO}">{PATRO}</td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_TITULACIO_PATRO}</b>&nbsp;</td><td width="100%"><input type=hidden name="titulacio_patro" value="{TITULACIO_PATRO}">{TITULACIO_PATRO}</td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_DNI_PATRO}</b>&nbsp;</td><td width="100%"><input type=hidden name="dni_patro" value="{DNI_PATRO}">{DNI_PATRO}</td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_MOTIU}</b>&nbsp;</td><td width="100%"><input type=hidden name="motiu" value="{MOTIU}">{MOTIU}</td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_ACTIVITAT}</b>&nbsp;</td><td width="100%"><input type=hidden name="altres" value="{ALTRES}">{ALTRES}</td></tr>
	</table>
	<p>
	<input type="radio" name="gestio" value="1" checked/> Acceptar petici&oacute;<br />
	<input type="radio" name="gestio" value="2" /> Rebutjar petici&oacute;<br />
	Comentaris:<BR>
	<textarea name='motius' COLS=30 ROWS=6></textarea>
	<input type=hidden name='validar_de_nuevo' value='1'>
	<input type=hidden name='recurs' value='{RECURS}'>
	<input type=hidden name='aula' value='{DESC_RECURS}'>
	<input type=hidden name='embarcats' value='{EMBARCATS}'>
	<input type=hidden name='titol' value='{TITOL}'>
	<input type=hidden name="data_inici" value="{DATA_RESERVA}">
	<input type=hidden name='op' value='57'></p>
	<p align="center">
		<input type=submit value='&nbsp;&nbsp;&nbsp;Enviar&nbsp;&nbsp;&nbsp;'>
		<input type="button" value="Tancar" onClick="javascript:window.opener.location.reload();javascript:window.close()">
	</form>
	</p>
	</center>
	</td>
</tr>
</table>
</center>
