<center>
<table cellspacing="0" cellpadding="8" class="reserva">
<tr><td height="30" class="titol_reserva">{E_CHECKLIST}{DESC_RECURS}</td></tr>
<tr><td>
	<form action='index.php' method=post>
	<table cellpadding="3">
		<tr><td align="right" class="fgNavy">{E_RECURS}&nbsp;</td><td><b>{DESC_RECURS}</b></td></tr>
		<tr><td align="right" class="fgNavy">{E_DATA}&nbsp;</td><td><b>{DATA}</b></td></tr>
		<tr><td align="right" class="fgNavy">{E_QUI}&nbsp;</td><td><b>{USUARI}</b></td></tr>
		<tr><td align="right" class="fgNavy">{E_HORA_INICI}&nbsp;</td><td>{FRANGES_HORARIES_INICI}</td></tr>
		<tr><td align="right" class="fgNavy">{E_HORA_FINAL}&nbsp;</td><td>{FRANGES_HORARIES_FINAL}</td></tr>
		
		<tr><td><br></td></tr>
		
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
	<input type=hidden name='op' value='49'>
	<input type=hidden name='recurs' value='{RECURS}'>
	<input type=hidden name='dia' value='{DIA}'>
	<input type=hidden name='mes' value='{MES}'>
	<input type=hidden name='any' value='{ANY}'>

	<p>&nbsp;</p>
	<center><input type=submit value='    Fer reserva    ' onClick="{E_AVIS_LEGAL}">&nbsp<input type="button" value="Tancar" onClick="{E_AVIS_LEGAL}javascript:window.opener.location.reload();javascript:window.close()"></center>
	</form>


</table>
&nbsp;
</center>