<center>
<table cellspacing="0" cellpadding="8" class="reserva">
<tr><td height="30" class="titol_reserva">{E_CHECKLIST}{DESC_RECURS}</td></tr>
<tr><td>
	<form action='index.php' method=post>
	<table cellpadding="3">
		<tr><td align="right" class="fgNavy">{E_RECURS}&nbsp;</td><td><b>{DESC_RECURS}</b></td></tr>
		<tr><td align="right" class="fgNavy">{E_QUI}&nbsp;</td><td><b>{USUARI}</b></td></tr>
		
		<tr><td><hr></td></tr>
		
		<tr><td align="right" class="fgNavy">{E_DATA_INICI}:</td><td><b>{DATA}</b></td></tr>
		<tr><td align="right" class="fgNavy">{E_HORA_INICI}&nbsp;</td><td>{FRANGES_HORARIES_INICI}</td></tr>
		
		<tr><td><hr></td></tr>
		
		<tr><td align="right" class="fgNavy">{E_DATA_FINAL}:</td><td><input readonly type=text name="data_final"> <input type="button" onClick="c1.popup('data_final')" value="Data"></td></tr>
		<tr><td align="right" class="fgNavy">{E_HORA_FINAL}&nbsp;</td><td>{FRANGES_HORARIES_FINAL}</td></tr>
		
		<tr><td><hr></td></tr>
		
		<tr><td align="right" class="fgNavy">{E_PATRO}&nbsp;</td><td><input type=text name='patro' size=30></td><td align="right" class="fgNavy">{E_DNI_PATRO}&nbsp;</td><td><input type=text name='dni_patro' size=10></td></tr>
		<tr>
				<td nowrap align="right" class="fgNavy">{E_TITOL}&nbsp;</td>
				<td><select name='titol'>
					<option value="1">Si</option>
					<option value="0">No</option>
				</td>
				<td align="right" class="fgNavy">{E_TITULACIO_PATRO}&nbsp;</td><td><input type=text name='titulacio_patro' size=30></td>
		</tr>
		<tr>
				<td nowrap align="right" class="fgNavy">{E_EMBARCATS}&nbsp;</td>
				<td><select name='embarcats'>
					<option value="1">Si</option>
					<option value="0">No</option>
				</td>
		</tr>
		<tr>
				<td nowrap align="left" class="fgNavy"></td><td nowrap align="left" class="fgNavy"></td>
				<td nowrap align="right" class="fgNavy"></td>
				<td><select name='motiu'>
					<option value="0"></option>
					<option value="Sortida per practiques docents">Sortida per pr&agrave;ctiques docents</option>
					<option value="Pr&agrave;ctiques Patr&oacute; Embarcaci&oacute; Esbarjo">Pr&agrave;ctiques Patr&oacute; Embarcaci&oacute; Esbarjo</option>
					<option value="Pr&agrave;ctiques Patr&oacute; de Iot">Pr&agrave;ctiques Patr&oacute; Iot</option>
					<option value="Pr&agrave;ctiques Captit&agrave; de Iot">Pr&agrave;ctiques Capit&agrave; Iot</option>
					<option value="Activitat sota conveni/projecte">Activitat sota conveni/projecte</option>
					<option value="Sortida per accions de protocol/visites">Sortida per accions de protocol/visites</option>
					<option value="5">Altres</option>
				</td>
		</tr>
		<tr>
			<td></td><td></td>
			<td align="right" class="fgNavy">{E_ALTRES}&nbsp;</td>
			<td><textarea name='altres' ROWS=5 COLS=30></textarea></td>
		</tr>
	</table>
	<input type=hidden name='op' value='53'>
	<input type=hidden name='recurs' value='{RECURS}'>
	<input type=hidden name='dia' value='{DIA}'>
	<input type=hidden name='mes' value='{MES}'>
	<input type=hidden name='any' value='{ANY}'>

	<p>&nbsp;</p>
	<center><input type=submit value='    Fer reserva    '>&nbsp<input type="button" value="Tancar" onClick="javascript:window.opener.location.reload();javascript:window.close()"></center>
	</form>


</table>
&nbsp;
</center>