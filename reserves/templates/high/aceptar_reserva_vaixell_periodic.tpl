<style type="text/css">
<!--
.Estilo1 {
	color: #FF0000;
	font-weight: bold;
}
-->
</style>
<form>
<center>
<table width="100%" cellpadding="10" cellspacing="0" class="reserva">
	<tr><td height="30" class="titol_reserva">RESERVA</td></tr>
	<tr><td>
		<p><b>{E_MSG_RESERVA}</b></p>
		<p class="Estilo1">{E_MSG_RESERVA_VAIXELL}</p>
		<p class="fgRed">{E_MOTIUS}</p>
		<p>&nbsp;</p>
		<p align="center"><input type="button" value="Generar Checklist PDF" onClick="javascript:finestraTotal('gen_pdf_vaixell.php?data_reserva={E_DATA_RESERVA}&data_final={E_DATA_FINAL}&recurs={E_RECURS}&qui={E_QUI}&motiu={E_MOTIU}&hora_inici={E_HORA_INICI}&hora_final={E_HORA_FINAL}&patro={E_PATRO}&dni_patro={E_DNI_PATRO}&titol={E_TITOL}&titulacio_patro={E_TITULACIO_PATRO}&embarcats={E_EMBARCATS}&altres={E_ALTRES}');javascript:window.close()"></p>
	</p>
	</td></tr>
</table>
</center>
</form>
