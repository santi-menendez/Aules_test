<form name="f" action='index.php' method=get>
<table width="100%" cellpadding="10" cellspacing="0" class="reserva">
<tr>
  <td height="30" class="titol_reserva">{E_CHECKLIST}{DESC_RECURS} </td>
</tr>
<tr><td>
	<table width="100%" cellpadding="4" cellspacing="0">
		<tr><td nowrap class="fgNavy" align="right"><b>{E_RECURS}</b>&nbsp;</td><td class="fgRed"><b>{DESC_RECURS}</b></td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_QUI}</b>&nbsp;</td><td><b>{USUARI}</b></td></tr>

		<tr><td><hr></td></tr>
	
		<tr><td nowrap><font class="fgNavy" align="right"><b>{E_DATA_INICI}:</b></font></td><td><input readonly type=text name="data_inici"> <input type="button" onClick="c1.popup('data_inici')" value="Data"></td></tr>
		<tr><td nowrap><font class="fgNavy" align="right"><b>{E_HORA_INICI}:</b></font></td><td>{FRANGES_HORARIES_INICI}</td></tr>
	
		<tr><td><hr></td></tr>
	
		<tr><td nowrap><font class="fgNavy" align="right"><b>{E_DATA_FINAL}:</b></font></td><td><input readonly type=text name="data_final"> <input type="button" onClick="c1.popup('data_final')" value="Data"></td></tr>
		<tr><td nowrap><font class="fgNavy" align="right"><b>{E_HORA_FINAL}:</b></font></td><td>{FRANGES_HORARIES_FINAL}</td></tr>
	
		<tr><td><hr></td></tr>

		
		<tr><td align="right" class="fgNavy">{E_PATRO}&nbsp;</td><td><input type=text name='patro' size=30></td><td align="left" class="fgNavy">{E_DNI_PATRO}&nbsp;</td><td><input type=text name='dni_patro' size=10></td></tr>
		<tr>
				<td nowrap class="fgNavy" align="right" >{E_TITOL}&nbsp;</td>
				<td><select name='titol'>
					<option value="1">Si</option>
					<option value="0">No</option>
				</td>
				<td align="left" class="fgNavy">{E_TITULACIO_PATRO}&nbsp;</td><td><input type=text name='titulacio_patro' size=30></td>
		</tr>
		<tr>
				<td nowrap class="fgNavy" align="right">{E_EMBARCATS}&nbsp;</td>
				<td><select name='embarcats' value='1'>
					<option value="1">Si</option>
					<option value="0">No</option>
				</td>
		</tr>
		<tr>
				<td nowrap class="fgNavy" align="right">{E_MOTIU}&nbsp;</td>
				<td><select name='motiu'>
					<option value="0"></option>
					<option value="Sortida per practiques docents">Sortida per pr&agrave;ctiques docents</option>
					<option value="Pr&agrave;ctiques Patr&oacute; Embarcaci&oacute; Esbarjo">Pr&agrave;ctiques Patr&oacute; Embarcaci&oacute; Esbarjo</option>
					<option value="Pr&agrave;ctiques Patr&oacute; de Iot">Pr&agrave;ctiques Patr&oacute; Iot</option>
					<option value="Pr&agrave;ctiques Captit&agrave; de Iot">Pr&agrave;ctiques Captit&agrave; Iot</option>
					<option value="Activitat sota conveni/projecte">Activitat sota conveni/projecte</option>
					<option value="Sortida per accions de protocol/visites">Sortida per accions de protocol/visites</option>
					<option value="5">Altres</option>
				</td>
		</tr>
		<tr>
			<td class="fgNavy" align="right">{E_ALTRES}&nbsp;</td>
			<td><textarea name='altres' ROWS=5 COLS=30></textarea></td>
		</tr>
	</table>
		
				
					<input type=hidden name='op' value='57'>
					<input type=hidden name='recurs' value='{RECURS}'>
					
		
	<tr><td>
		
		<center><input type=submit value='&nbsp;&nbsp;&nbsp;Fer reserva peri&ograve;dica&nbsp;&nbsp;&nbsp;'>
		<input type="button" value="Tancar" onClick="javascript:window.opener.location.reload();javascript:window.close()"></center>
	</td></tr>
</table>
&nbsp;
</form>
