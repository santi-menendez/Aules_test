<center>
<table cellpadding="10" cellspacing="0" width="350" class="reserva">
<tr><td height="30" class="titol_reserva">{E_MSG_INF_RESERVA}</td></tr>
<tr>
	<td>
	<p align="center" class="desc_recurs">{DESC_RECURS}</p>
	<center>
	<table width="90%" cellpadding="0" cellspacing="5" border="0">
		<tr><td nowrap class="fgNavy" align="right"><b>{E_DATA_INICI}</b>&nbsp;</td><td width="100%">{DATA_RESERVA}</td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_DATA_FINAL}</b>&nbsp;</td><td width="100%">{DATA_FINAL}</td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_HORA_INICI}</b>&nbsp;</td><td width="100%">{HORA_INICI}</td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_HORA_FINAL}</b>&nbsp;</td><td width="100%">{HORA_FINAL}</td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_PERIODICA}</b>&nbsp;</td><td width="100%">{DIA}</td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_QUI}</b>&nbsp;</td><td width="100%">{QUI}</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_RESP_ACTI}</b>&nbsp;</td><td width="100%">{RESP_ACTI}</td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_ALUM}</b>&nbsp;</td><td width="100%">{ALUM}</td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_ACTIVITAT}</b>&nbsp;</td><td width="100%">{ACTIVITAT}</td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_QUIN}</b>&nbsp;</td><td width="100%">{QUIN}</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_MAQ}</b>&nbsp;</td><td width="100%">{MAQUINA}</td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_FUNG}</b>&nbsp;</td><td width="100%">{FUNGIBLE}</td></tr>
		<tr><td nowrap class="fgNavy" align="right"><b>{E_QQUANT}</b>&nbsp;</td><td width="100%">{QUANTITAT}</td></tr>
	</table>
	<p>
	<p align="center">{E_RESPONSABLE_RECURS}:	<a href="mailto:{EMAIL}">{RESPONSABLE}</a>
	<p>&nbsp;</p>
	<p align="center"><form>
		<input type="button" value="&nbsp;&nbsp;&nbsp;&nbsp;Tancar&nbsp;&nbsp;&nbsp;&nbsp;" onClick="javascript:window.opener.location.reload();javascript:window.close()">
		<input type="button" value="&nbsp;&nbsp;&nbsp;&nbsp;Generar PDF&nbsp;&nbsp;&nbsp;&nbsp;" onClick="javascript:finestraTotal('gen_pdf_taller.php?data_reserva={DATA_RESERVA}&data_final={DATA_FINAL}&recurs={RECURS}&qui={QUI}&motiu={ACTIVITAT}&quin={QUIN}&hora_inici={HORA_INICI}&hora_final={HORA_FINAL}&resp_acti={RESP_ACTI}&alum={ALUM}&maquina={MAQUINA}&fungible={FUNGIBLE}&quantitat={QUANTITAT}');javascript:window.close()">
	</form>
	</p>
	</center>
	</td>
</tr>
</table>
</center>
