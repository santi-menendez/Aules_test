<form name="f" action='index.php' method=get>
<table width="100%" cellpadding="10" cellspacing="0" class="reserva">
<tr>
  <td height="30" class="titol_reserva">{E_CHECKLIST}{DESC_RECURS} </td>
</tr>
<tr><td>
	<table width="100%" cellpadding="4" cellspacing="0">
		<tr><td nowrap class="fgNavy" align="right"><b>{E_RECURS}</b>&nbsp;</td><td width="100%" class="fgRed"><b>{DESC_RECURS}</b></td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_QUI}</b>&nbsp;</td><td><b>{USUARI}</b><input type=hidden name="qui" value="{USUARI}"></td></tr>

		<tr><td><hr></td></tr>
	</table>
	
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
	<table width="100%" cellpadding="4" cellspacing="0">
		<tr><td><hr></td></tr>

		<tr><td align="right" class="fgNavy">{E_RESP_ACTI}&nbsp;</td><td><input type=text name='patro' size=30></td></tr>
		<tr>
				<td nowrap align="right" class="fgNavy">{E_ALUM}&nbsp;</td>
				<td><select name='alum'>
					<option value="0"></option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option>
					<option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option>
					<option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option>
				</td>
		</tr>
		
		<tr>
				<td nowrap align="right" class="fgNavy">{E_ACTIVITAT}&nbsp;</td>
				<td><select name='motiu'>
					<option value="0"></option>
					<option value="Assignatura de formacio reglada a la FNB">Assignatura de formaci&oacute; reglada a la FNB</option>
					<option value="Curs de Postgrau o Master">Curs de Postgrau o Master</option>
					<option value="Altres cursos o activitats">Altres cursos o activitats</option>
				</td>
		</tr>
		
		<tr><td align="right" class="fgNavy">{E_QUIN}&nbsp;</td><td><input type=text name='quin' size=30><br></td></tr>
		
		<tr><td><br></td></tr>
		
		<tr>
			<td align="right" class="fgNavy">{E_MAQ}&nbsp;</td>
			<td><textarea name='maquina' ROWS=3 COLS=30></textarea></td>
		</tr>
		<tr>
				<td nowrap align="right" class="fgNavy">{E_FUNG}&nbsp;</td>
				<td><select name='fungible'>
					<option value="0">No</option><option value="1">Si</option>
				</td>
		</tr>
		<tr>
			<td align="right" class="fgNavy">{E_QQUANT}&nbsp;</td>
			<td><textarea name='qquant' ROWS=3 COLS=30></textarea></td>
		</tr>
	</table>
		
				
					<input type=hidden name='op' value='58'>
					<input type=hidden name='recurs' value='{RECURS}'>
					
		
	<tr><td>
		
		<center><input type=submit value='&nbsp;&nbsp;&nbsp;Fer reserva peri&ograve;dica&nbsp;&nbsp;&nbsp;' onClick="{E_AVIS_LEGAL}">
		<input type="button" value="Tancar" onClick="javascript:window.opener.location.reload();javascript:window.close()"></center>
	</td></tr>
</table>
&nbsp;
</form>
