<?php
$data_reserva=$_REQUEST["data_reserva"];
$data_final=$_REQUEST["data_final"];
$recurs=$_REQUEST["recurs"];
$qui=$_REQUEST["qui"];
$motiu=$_REQUEST["motiu"];
$hora_inici=$_REQUEST["hora_inici"];
$hora_final=$_REQUEST["hora_final"];
$resp_acti=$_REQUEST["resp_acti"];
$dni_patro=$_REQUEST["dni_patro"];
$quin=$_REQUEST["quin"];
$alum=$_REQUEST["alum"];
$maquina=$_REQUEST["maquina"];
$fungible=$_REQUEST["fungible"];
$quantitat=$_REQUEST["quantitat"];
header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=\"pdf_taller.pdf\"");
passthru("htmldoc --format pdf --left 2.5cm --right 1.5cm --top 1cm --bottom 1cm " .
		"--headfootsize 9  --header '.' --footer '.' " .
         "--size 'a4' --fontsize 12 --charset 8859-15 " .
	 "--webpage http://www.fnb.upc.edu/intrafnb/aules/reserves/pdf_taller_copia.php?data_reserva=$data_reserva\"&data_final=$data_final&recurs=$recurs&qui=$qui&motiu=$motiu&hora_inici=$hora_inici&hora_final=$hora_final&resp_acti=$resp_acti&quin=$quin&alum=$alum&maquina=$maquina&fungible=$fungible&quantitat=$quantitat\"");
?>