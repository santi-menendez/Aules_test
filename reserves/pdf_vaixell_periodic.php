<?
include "./config.php";
			

   echo "<html>
	 <head><title></title>
	 </head>
	 <body>
	<table border=\"0px\"> \n";
		echo "<tr><td><img src=\"/images/logotip/0280lFNB.gif\" width=\"300\" border=\"0\"></td></tr>\n";
		echo "<tr><td><strong><h1></h1></strong>&nbsp;</td></tr>\n";
		echo "<tr><td><center><strong><h1>Formulari de reserva del Vaixell BARCELONA</h1></strong></center></td></tr>\n";
		echo "</table> \n";
		
		echo "<table border=\"0px\"> \n";
		echo "<tr><td><strong><h1></h1></strong>&nbsp;</td></tr>\n";
      	echo "<tr><td width=\"38%\"><b>Data de la reserva:</b></td><td width=\"62%\">Des del ".$dia0."/".$mes0."/".$any0." a les  ".$hora_inici." hores fins al ".$dia1."/".$mes1."/".$any1." a les ".$hora_final." hores</td></tr>\n";
		echo "<tr><td width=\"37%\"><b>Professor responsable de la reserva:</b></td><td>  ".$qui."</td></tr>\n";
	    echo "<tr><td width=\"37%\"><b>Nom del Patr� responsable del iot:</b></td><td>  ".$patro."</td></tr>\n";
	    echo "<tr><td width=\"37%\"><b>T�tol igual o superior a Patr� de iot:</b></td><td> SI</td></tr>\n";
	    echo "<tr><td width=\"37%\"><b>N� total d'embarcats igual o inferior a 12:</b></td><td> SI</td></tr>\n";
	    echo "<tr><td width=\"37%\"><b>Motiu de la reserva:</b></td><td>  ".$motiu."</td></tr>\n";
		echo "</table> \n";

		echo "<table border=\"0px\"> \n";
		echo "<tr><td>&nbsp;</td></tr>\n";
		echo "<tr><td width=\"50%\"><b>Estat de l'embarcaci� en rebre-la:</b></td><td width=\"50%\">( ) B�</td></tr>\n";
		echo "<tr><td width=\"50%\">&nbsp;</td><td width=\"50%\">( ) Dolent. Especificar:</td></tr>\n";
		echo "</table> \n";
			echo "<table border=\"1px\"> \n";
			echo "<tr><td width=\"100%\" height=\"7%\">&nbsp;</td></tr>\n";
			echo "</table> \n";
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"50%\"><b>Ordre i neteja de l'embarcaci� al inici:</b></td><td width=\"50%\">( ) B�</td></tr>\n";
		echo "<tr><td width=\"50%\">&nbsp;</td><td width=\"50%\">( ) Dolent. Especificar:</td></tr>\n";
		echo "</table> \n";
			echo "<table border=\"1px\"> \n";
			echo "<tr><td width=\"100%\" height=\"7%\">&nbsp;</td></tr>\n";
			echo "</table> \n";
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"50%\"><b>Estat de l'embarcaci� al finalitzar el �s:</b></td><td width=\"50%\">( ) B�</td></tr>\n";
		echo "<tr><td width=\"50%\">&nbsp;</td><td width=\"50%\">( ) Dolent. Especificar:</td></tr>\n";
		echo "</table> \n";
			echo "<table border=\"1px\"> \n";
			echo "<tr><td width=\"100%\" height=\"7%\">&nbsp;</td></tr>\n";
			echo "</table> \n";
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"50%\"><b>Ordre i netega de l'embarcaci�n al finalitzar el �s:</b></td><td width=\"50%\">( ) B�</td></tr>\n";
		echo "<tr><td width=\"50%\"><td width=\"50%\">( ) Dolent. Especificar:</td></tr>\n";
		echo "</table> \n";
			echo "<table border=\"1px\"> \n";
			echo "<tr><td width=\"100%\" height=\"7%\">&nbsp;</td></tr>\n";
			echo "</table> \n";
		
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"50%\"><b>Contador d'hores de funcionament del motor:</b></td><td width=\"50%\">Inici:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;hores</td></tr>\n";
		echo "<tr><td width=\"50%\"><td width=\"50%\">Final:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;hores</td></tr>\n";
		echo "</table> \n";
		
		echo "<table border=\"0px\"> \n";
		echo "<tr><td><strong><h1></h1></strong>&nbsp;</td></tr>\n";
		echo "<tr><td><b>Signatura del patr� i del sol�licitant. Barcelona a&nbsp;&nbsp;&nbsp;&nbsp;de&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;de&nbsp;&nbsp;&nbsp;&nbsp;20__</b></td></tr>\n";
		echo "</table> \n";
		echo "<br>";
			echo "<table border=\"1px\"> \n";
			echo "<tr><td width=\"50%\" height=\"10%\" align=\"center\" valign=\"top\">Nom i Cognoms del Sol�licitant</td><td width=\"50%\" height=\"10%\" align=\"center\" valign=\"top\">Nom i Cognoms del Patr&oacute;</td></tr>\n";
			echo "</table></body></html> \n";
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"100%\" height=\"7%\"><em>S'ha d'entregar una copia complimentada i signada en finalitzar l'activitat al Centre (Piedad Barruz)</em></td></tr>\n";
		echo "</table> \n";
?>