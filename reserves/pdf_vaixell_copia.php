<?php
include "./config.php";
$data_reserva=$_REQUEST["data_reserva"];
$data_final=$_REQUEST["data_final"];
$recurs=$_REQUEST["recurs"];
$qui=utf8_decode($_REQUEST["qui"]);
$motiu=utf8_decode($_REQUEST["motiu"]);
$hora_inici=$_REQUEST["hora_inici"];
$hora_final=$_REQUEST["hora_final"];
$patro=utf8_decode($_REQUEST["patro"]);
$dni_patro=$_REQUEST["dni_patro"];
$titol=$_REQUEST["titol"];
$titulacio_patro=$_REQUEST["titulacio_patro"];
$embarcats=$_REQUEST["embarcats"];
$motiu=utf8_decode($_REQUEST["motiu"]);
$altres=utf8_decode($_REQUEST["altres"]);
   echo "<html>
	 <head><title></title>
	 </head>
	 <body>
	<table border=\"0px\"> \n";
		echo "<tr><td><img src=\"/images/logotip/0280lFNB.gif\" width=\"300\" border=\"0\"></td></tr>\n";
		echo "<tr><td><center><strong><h1>Formulari de reserva del Vaixell BARCELONA</h1></strong></center></td></tr>\n";
		echo "</table> \n";
		
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"50%\">&nbsp;</td><td width=\"50%\">&nbsp;</td></tr>\n";
		if ($data_reserva==$data_final):
			echo "<tr><td width=\"38%\"><b>Data de la reserva:</b></td><td width=\"62%\">El ".$data_reserva." des de les  ".substr($hora_inici, 0, 5)." hores fins a les ".substr($hora_final, 0, 5)." hores</td></tr>\n";
	    else:  	
			echo "<tr><td width=\"38%\"><b>Data de la reserva:</b></td><td width=\"62%\">Des del ".$_REQUEST["data_reserva"]." a les  ".substr($hora_inici, 0, 5)." hores fins al ".$data_final." a les ".substr($hora_final, 0, 5)." hores</td></tr>\n";
		endif;
		echo "<tr><td width=\"37%\"><b>Professor responsable de la reserva:</b></td><td>  ".utf8_decode($qui)."</td></tr>\n";
	    echo "<tr><td width=\"37%\"><b>Nom i DNI del Patr&oacute;/ona responsable:</b></td><td>  ".utf8_decode($patro."&nbsp;-&nbsp;".$dni_patro)."</td></tr>\n";
	    echo "<tr><td width=\"37%\"><b>T&iacute;tol igual o superior a Patr&oacute; de iot:</b></td><td> SI&nbsp;-&nbsp;".utf8_decode($titulacio_patro)."</td></tr>\n";
	    echo "<tr><td width=\"37%\"><b>N&deg; total d'embarcats igual o inferior a 12:</b></td><td> SI</td></tr>\n";
		echo "<tr><td width=\"37%\"><b>Motiu de la reserva:</b></td><td>  ".utf8_decode($motiu)."</td></tr>\n";
		echo "<tr><td width=\"37%\"><b>Assignatura/Activitat:</b></td><td>  ".utf8_decode($altres)."</td></tr>\n";
		echo "</table> \n";
		
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"70%\"><b>Relaci&oacute; de persones embarcades:</b></td><td width=\"30%\"></td></tr>\n";
		echo "<tr><td width=\"80%\">Nom i Cognoms</td><td width=\"20%\">DNI</td></tr>\n";
			echo "<table border=\"1px\"> \n";
			echo "<tr><td width=\"70%\">1.-</td><td width=\"30%\">&nbsp;</td></tr>\n";
			echo "<tr><td width=\"70%\">2.-</td><td width=\"30%\">&nbsp;</td></tr>\n";
			echo "<tr><td width=\"70%\">3.-</td><td width=\"30%\">&nbsp;</td></tr>\n";
			echo "<tr><td width=\"70%\">4.-</td><td width=\"30%\">&nbsp;</td></tr>\n";
			echo "<tr><td width=\"70%\">5.-</td><td width=\"30%\">&nbsp;</td></tr>\n";
			echo "<tr><td width=\"70%\">6.-</td><td width=\"30%\">&nbsp;</td></tr>\n";
			echo "<tr><td width=\"70%\">7.-</td><td width=\"30%\">&nbsp;</td></tr>\n";
			echo "<tr><td width=\"70%\">8.-</td><td width=\"30%\">&nbsp;</td></tr>\n";
			echo "<tr><td width=\"70%\">9.-</td><td width=\"30%\">&nbsp;</td></tr>\n";
			echo "<tr><td width=\"70%\">10.-</td><td width=\"30%\">&nbsp;</td></tr>\n";
			echo "<tr><td width=\"70%\">11.-</td><td width=\"30%\">&nbsp;</td></tr>\n";
			echo "</table> \n";
		echo "</table> \n";
		
		echo "<table border=\"0px\"> \n";
		echo "<tr><td>&nbsp;</td></tr>\n";
		echo "<tr><td width=\"50%\"><b>Estat de l'embarcaci&oacute; a la recepci&oacute;:</b></td><td width=\"50%\">( ) Favorable</td></tr>\n";
		echo "<tr><td width=\"50%\">&nbsp;</td><td width=\"50%\">( ) Desfavorable. Especificar:</td></tr>\n";
		echo "</table> \n";
			echo "<br> \n";
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"50%\"><b>Ordre i neteja de l'embarcaci&oacute; a la recepci&oacute;:</b></td><td width=\"50%\">( ) Favorable</td></tr>\n";
		echo "<tr><td width=\"50%\">&nbsp;</td><td width=\"50%\">( ) Desfavorable. Especificar:</td></tr>\n";
		echo "</table> \n";
			echo "<br> \n";
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"50%\"><b>Estat de l'embarcaci&oacute; al finalitzar l'&uacute;s:</b></td><td width=\"50%\">( ) Favorable</td></tr>\n";
		echo "<tr><td width=\"50%\">&nbsp;</td><td width=\"50%\">( ) Desfavorable. Especificar:</td></tr>\n";
		echo "</table> \n";
			echo "<br> \n";
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"50%\"><b>Ordre i neteja de l'embarcaci&oacute; al finalitzar l'&uacute;s:</b></td><td width=\"50%\">( ) Favorable</td></tr>\n";
		echo "<tr><td width=\"50%\"><td width=\"50%\">( ) Desfavorable. Especificar:</td></tr>\n";
		echo "</table> \n";
			echo "<table border=\"0px\"> \n";
			echo "<tr><td width=\"100%\" height=\"2%\">&nbsp;</td></tr>\n";
			echo "</table> \n";
		
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"50%\"><b>Contador d'hores de funcionament del motor:</b></td><td width=\"50%\">Inici:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;hores</td></tr>\n";
		echo "<tr><td width=\"50%\"><td width=\"50%\">Final:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;hores</td></tr>\n";
		echo "</table> \n";
		
		echo "<table border=\"0px\"> \n";
		echo "<tr><td><strong><h1></h1></strong>&nbsp;</td></tr>\n";
		echo "<tr><td><b>Signatura del patr&oacute; i del sol&middot;licitant. Barcelona a&nbsp;&nbsp;&nbsp;&nbsp;de&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;de&nbsp;&nbsp;&nbsp;&nbsp;20__</b></td></tr>\n";
		echo "</table> \n";

			echo "<table border=\"1px\"> \n";
			echo "<tr><td width=\"50%\" height=\"10%\" align=\"center\" valign=\"top\">Nom i Cognoms del Sol&middot;licitant</td><td width=\"50%\" height=\"10%\" align=\"center\" valign=\"top\">Nom i Cognoms del Patr&oacute;/ona</td></tr>\n";
			echo "</table></body></html> \n";
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"100%\" height=\"7%\"><em>S'ha de lliurar una copia complimentada i signada de l'activitat a l'Administraci&oacute; del Centre.</em></td></tr>\n";
		echo "</table> \n";
?>