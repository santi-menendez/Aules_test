<form>
<center>
<table width="100%" cellpadding="10" cellspacing="0" class="reserva">
	<tr><td height="30" class="titol_reserva">RESERVA</td></tr>
	<tr><td>
		<p><b>{E_MSG_RESERVA}</b></p>
		<p class="fgRed">{E_MOTIUS}</p>
		<p>&nbsp;</p>
		<p align="center"><input type="button" value="Tancar" onClick="javascript:window.close()"><input type="button" value="Generar PDF" onClick="javascript:finestraTotal('gen_pdf_taller.php?data_reserva={E_DATA_RESERVA}&data_final={E_DATA_FINAL}&recurs={E_RECURS}&qui={E_QUI}&motiu={E_MOTIU}&quin={E_QUIN}&hora_inici={E_HORA_INICI}&hora_final={E_HORA_FINAL}&resp_acti={E_RESP_ACTI}&alum={E_ALUM}&maquina={E_MAQ}&fungible={E_FUNG}&quantitat={E_QQUANT}');javascript:window.close()"></p>
	</p>
	</td></tr>
</table>
</center>
</form>
