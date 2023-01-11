<?php
$data_reserva=$_REQUEST["data_reserva"];
$data_final=$_REQUEST["data_final"];
$recurs=$_REQUEST["recurs"];
$qui=$_REQUEST["qui"];
$motiu=$_REQUEST["motiu"];
$hora_inici=$_REQUEST["hora_inici"];
$hora_final=$_REQUEST["hora_final"];
$patro=$_REQUEST["patro"];
$dni_patro=$_REQUEST["dni_patro"];
$titol=$_REQUEST["titol"];
$titulacio_patro=$_REQUEST["titulacio_patro"];
$embarcats=$_REQUEST["embarcats"];
$motiu=$_REQUEST["motiu"];
$altres=$_REQUEST["altres"];
header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=\"pdf_vaixell_barcelona.pdf\"");
passthru("htmldoc --format pdf --left 2.5cm --right 1.5cm --top 1cm --bottom 1cm " .
		"--headfootsize 9  --header '.' --footer '.' " .
         "--size 'a4' --fontsize 10 --charset 8859-15 " .
	 "--webpage http://www.fnb.upc.edu/intrafnb/aules/reserves/pdf_vaixell_barcelona.php?data_reserva=$data_reserva\"&data_final=$data_final&recurs=$recurs&qui=$qui&motiu=$motiu&hora_inici=$hora_inici&hora_final=$hora_final&patro=$patro&dni_patro=$dni_patro&titol=$titol&titulacio_patro=$titulacio_patro&embarcats=$embarcats&motiu=$motiu&altres=$altres\"");
?>