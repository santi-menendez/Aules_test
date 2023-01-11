<?
include "./config.php";
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
   echo "<html>
	 <head><title></title>
	 </head>
	 <body>
	<table border=\"0px\"> \n";
		echo "<tr><td><img src=\"/images/logotip/0280lFNB.gif\" width=\"300\" border=\"0\"></td></tr>\n";
		echo "<tr><td><strong><h1></h1></strong>&nbsp;</td></tr>\n";
		echo "<tr><td><center><strong><h1>Formulari de reserva del TALLER per Activitats</h1></strong></center></td></tr>\n";
		echo "</table> \n";
		
		echo "<table border=\"0px\"> \n";
		echo "<tr><td><strong><h1></h1></strong>&nbsp;</td></tr>\n";
		if ($data_reserva==$data_final):
			echo "<tr><td width=\"38%\"><b>Data de la reserva:</b></td><td width=\"62%\">El ".$data_reserva." des de les  ".substr($hora_inici, 0, 2)." hores fins a les ".substr($hora_final, 0, 2)." hores</td></tr>\n";
	    else:  	
			echo "<tr><td width=\"38%\"><b>Data de la reserva:</b></td><td width=\"62%\">Des del ".$data_reserva." a les  ".substr($hora_inici, 0, 2)." hores fins al ".$data_final." a les ".substr($hora_final, 0, 2)." hores</td></tr>\n";
		endif;
		echo "<tr><td><strong><h1></h1></strong>&nbsp;</td></tr>\n";
		echo "<tr><td width=\"37%\"><b>Professor sol·licitant de la reserva:</b></td><td>  ".utf8_decode($qui)."</td></tr>\n";
		echo "<tr><td><strong><h1></h1></strong><hr></td><td><strong><h1></h1></strong><hr></td></tr>\n";
	    echo "<tr><td width=\"37%\"><b>Responsable de l'activitat:</b></td><td>  ".utf8_decode($resp_acti)."</td></tr>\n";
	    echo "<tr><td width=\"37%\"><b>Número d'alumnes/assistents:</b></td><td> ".$alum."</td></tr>\n";
	    echo "<tr><td width=\"37%\"><b>L'activitat a desenvolupar pertany a: </b></td><td> ".utf8_decode($motiu)."</td></tr>\n";
		echo "<tr><td width=\"37%\"><b>Quin/a:</b></td><td>  ".utf8_decode($quin)."</td></tr>\n";
		echo "<tr><td><strong><h1></h1></strong>&nbsp;</td></tr>\n";
		echo "<tr><td width=\"37%\"><b>Especificar quina maquinaria o eines a utilitzar seran necessaries:</b></td><td>  ".utf8_decode($maquina)."</td></tr>\n";
		echo "<tr><td><strong><h1></h1></strong>&nbsp;</td></tr>\n";
		if ($fungible==0):	
			echo "<tr><td width=\"37%\"><b>Consum de material fungible:</b></td><td>  NO</td></tr>\n";
		else:
			echo "<tr><td width=\"37%\"><b>Consum de material fungible:</b></td><td>  SI</td></tr>\n";
		endif;
		echo "<tr><td><strong><h1></h1></strong>&nbsp;</td></tr>\n";
		echo "<tr><td width=\"37%\"><b>Quin i quantitat de material fungible s'utilitzarà:</b></td><td>  ".$quantitat."</td></tr>\n";
		echo "</table> \n";
		
		echo "<tr><td><strong><h1></h1></strong>&nbsp;</td></tr>\n";
		echo "<tr><td><strong><h1></h1></strong>&nbsp;</td></tr>\n";
		echo "<tr><td><strong><h1></h1></strong>&nbsp;</td></tr>\n";
		echo "<table border=\"0px\"> \n";
		echo "<tr><td><strong><h1></h1></strong>&nbsp;</td></tr>\n";
		echo "<tr><td><b>Signatura del responsable de l'activitat i del sol·licitant. Barcelona a&nbsp;&nbsp;&nbsp;&nbsp;de&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;de&nbsp;&nbsp;&nbsp;&nbsp;20__</b></td></tr>\n";
		echo "</table> \n";
		echo "<br>";
			echo "<table border=\"1px\"> \n";
			echo "<tr><td width=\"50%\" height=\"10%\" align=\"center\" valign=\"top\">Nom i Cognoms del Sol·licitant</td><td width=\"50%\" height=\"10%\" align=\"center\" valign=\"top\">Nom i Cognoms del Responsable de l'activitat</td></tr>\n";
			echo "</table></body></html> \n";
		echo "<table border=\"0px\"> \n";
		echo "</table> \n";
?>