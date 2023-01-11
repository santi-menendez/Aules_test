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
		echo "</table></body></html> \n";
		
		echo "<table border=\"0px\"> \n";
		echo "<tr><td><strong><h1></h1></strong>&nbsp;</td></tr>\n";
      	echo "<tr><td><b>Data de la reserva:</b></td><td>".$dia." / ".$mes." / ".$any." de ".$hora_inici." hores fins el ".$data_final." a les ".$hora_final." hores</td></tr>\n";
		echo "<tr><td width=\"50%\"><b>Professor responsable de la reserva:</b></td><td>  ".$qui."</td></tr>\n";
	    echo "<tr><td width=\"50%\"><b>Nom del Patró responsable del iot:</b></td><td>  ".$patro."</td></tr>\n";
	    echo "<tr><td width=\"50%\"><b>Título igual o superior a Patró de iot:</b></td><td> SI</td></tr>\n";
	    echo "<tr><td width=\"50%\"><b>Número total d'embarcats igual o inferior a 12:</b></td><td> SI</td></tr>\n";
	    echo "<tr><td width=\"50%\"><b>Motiu de la reserva:</b></td><td>  ".$motiu."</td></tr>\n";
		
		echo "<tr><td>&nbsp;</td></tr>\n";
		echo "<tr><td width=\"50%\"><b>Estat de l'embarcació en rebre-la:</b></td><td width=\"50%\">( ) Bé</td></tr>\n";
		echo "<tr><td width=\"50%\">&nbsp;</td><td width=\"50%\">( ) Dolent. Especificar:</td></tr>\n";
		echo "</table></body></html> \n";
			echo "<table border=\"1px\"> \n";
			echo "<tr><td width=\"100%\" height=\"7%\">&nbsp;</td></tr>\n";
			echo "</table></body></html> \n";
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"50%\"><b>Ordre i neteja de l'embarcació al inici:</b></td><td width=\"50%\">( ) Bé</td></tr>\n";
		echo "<tr><td width=\"50%\">&nbsp;</td><td width=\"50%\">( ) Dolent. Especificar:</td></tr>\n";
		echo "</table></body></html> \n";
			echo "<table border=\"1px\"> \n";
			echo "<tr><td width=\"100%\" height=\"7%\">&nbsp;</td></tr>\n";
			echo "</table></body></html> \n";
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"50%\"><b>Estat de l'embarcació al finalitzar el ús:</b></td><td width=\"50%\">( ) Bé</td></tr>\n";
		echo "<tr><td width=\"50%\">&nbsp;</td><td width=\"50%\">( ) Dolent. Especificar:</td></tr>\n";
		echo "</table></body></html> \n";
			echo "<table border=\"1px\"> \n";
			echo "<tr><td width=\"100%\" height=\"7%\">&nbsp;</td></tr>\n";
			echo "</table></body></html> \n";
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"50%\"><b>Ordre i netega de l'embarcación al finalitzar el ús:</b></td><td width=\"50%\">( ) Bé</td></tr>\n";
		echo "<tr><td width=\"50%\"><td width=\"50%\">( ) Dolent. Especificar:</td></tr>\n";
		echo "</table></body></html> \n";
			echo "<table border=\"1px\"> \n";
			echo "<tr><td width=\"100%\" height=\"7%\">&nbsp;</td></tr>\n";
			echo "</table></body></html> \n";
		
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"50%\"><b>Contador d'hores de funcionament del motor:</b></td><td width=\"50%\">Inici:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;hores</td></tr>\n";
		echo "<tr><td width=\"50%\"><td width=\"50%\">Final:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;hores</td></tr>\n";
		echo "</table></body></html> \n";
		
		echo "<table border=\"0px\"> \n";
		echo "<tr><td><strong><h1></h1></strong>&nbsp;</td></tr>\n";
		echo "<tr><td><b>Signatura del patró i del sol·licitant. Barcelona a&nbsp;&nbsp;&nbsp;&nbsp;de&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;de&nbsp;&nbsp;&nbsp;&nbsp;20__</b></td></tr>\n";
		echo "</table></body></html> \n";
		echo "<br>";
			echo "<table border=\"1px\"> \n";
			echo "<tr><td width=\"50%\" height=\"10%\" align=\"center\" valign=\"top\">Nom i Cognoms del Sol·licitant</td><td width=\"50%\" height=\"10%\" align=\"center\" valign=\"top\">Nom i Cognoms del Patr&oacute;</td></tr>\n";
			echo "</table></body></html> \n";
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"100%\" height=\"7%\"><em>S'ha d'entregar una copia complimentada i signada en finalitzar l'activitat al Centre (Piedad Barruz)</em></td></tr>\n";
		echo "</table> \n";
?>