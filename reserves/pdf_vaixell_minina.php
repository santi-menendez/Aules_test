<?
include "./config.php";
			

   echo "<html>
	 <head><title></title>
	 </head>
	 <body>
	<table border=\"0px\"> \n";
		echo "<tr><td><img src=\"/images/logotip/0280lFNB.gif\" width=\"300\" border=\"0\"></td></tr>\n";
		echo "<tr><td><center><strong><h1>Formulari de reserva del Vaixell MININA II</h1></strong></center></td></tr>\n";
		echo "</table> \n";
		
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"50%\">&nbsp;</td><td width=\"50%\">&nbsp;</td></tr>\n";
		if ($data_reserva==$data_final):
			echo "<tr><td width=\"38%\"><b>Data de la reserva:</b></td><td width=\"62%\">El ".$data_reserva." des de les  ".substr($hora_inici, 0, 2)." hores fins a les ".substr($hora_final, 0, 2)." hores</td></tr>\n";
	    else:  	
			echo "<tr><td width=\"38%\"><b>Data de la reserva:</b></td><td width=\"62%\">Des del ".$data_reserva." a les  ".substr($hora_inici, 0, 2)." hores fins al ".$data_final." a les ".substr($hora_final, 0, 2)." hores</td></tr>\n";
		endif;
		echo "<tr><td width=\"37%\"><b>Professor responsable de la reserva:</b></td><td>  ".$qui."</td></tr>\n";
	    echo "<tr><td width=\"37%\"><b>Nom i DNI del Patró/ona responsable:</b></td><td>  ".$patro."&nbsp;-&nbsp;".$dni_patro."</td></tr>\n";
	    echo "<tr><td width=\"37%\"><b>Títol igual o superior a Patró de iot:</b></td><td> SI&nbsp;-&nbsp;".$titulacio_patro."</td></tr>\n";
	    echo "<tr><td width=\"37%\"><b>Nº total d'embarcats igual o inferior a 5:</b></td><td> SI</td></tr>\n";
		if ($motiu==5):
			echo "<tr><td width=\"37%\"><b>Motiu de la reserva:</b></td><td>  ".$altres."</td></tr>\n";
		else:
		    echo "<tr><td width=\"37%\"><b>Motiu de la reserva:</b></td><td>  ".$motiu."</td></tr>\n";
		endif;
		echo "</table> \n";
		
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"50%\"><b>Relació de persones embarcades:</b></td><td width=\"50%\"></td></tr>\n";
		echo "<tr><td width=\"75%\">Nom i Cognoms</td><td width=\"25%\">DNI</td></tr>\n";
			echo "<table border=\"1px\"> \n";
			echo "<tr><td width=\"60%\">1.-</td><td width=\"40%\">&nbsp;</td></tr>\n";
			echo "<tr><td width=\"60%\">2.-</td><td width=\"40%\">&nbsp;</td></tr>\n";
			echo "<tr><td width=\"60%\">3.-</td><td width=\"40%\">&nbsp;</td></tr>\n";
			echo "<tr><td width=\"60%\">4.-</td><td width=\"40%\">&nbsp;</td></tr>\n";
			echo "<tr><td width=\"60%\">5.-</td><td width=\"40%\">&nbsp;</td></tr>\n";
			echo "</table> \n";
		echo "</table> \n";
		
		echo "<table border=\"0px\"> \n";
		echo "<tr><td>&nbsp;</td></tr>\n";
		echo "<tr><td width=\"50%\"><b>Estat de l'embarcació a la recepció:</b></td><td width=\"50%\">( ) Favorable</td></tr>\n";
		echo "<tr><td width=\"50%\">&nbsp;</td><td width=\"50%\">( ) Desfavorable. Especificar:</td></tr>\n";
		echo "</table> \n";
			echo "<table border=\"0px\"> \n";
			echo "<tr><td width=\"100%\" height=\"2%\">&nbsp;</td></tr>\n";
			echo "</table> \n";
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"50%\"><b>Ordre i neteja de l'embarcació a la recepció:</b></td><td width=\"50%\">( ) Favorable</td></tr>\n";
		echo "<tr><td width=\"50%\">&nbsp;</td><td width=\"50%\">( ) Desfavorable. Especificar:</td></tr>\n";
		echo "</table> \n";
			echo "<table border=\"0px\"> \n";
			echo "<tr><td width=\"100%\" height=\"2%\">&nbsp;</td></tr>\n";
			echo "</table> \n";
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"50%\"><b>Estat de l'embarcació al finalitzar l'ús:</b></td><td width=\"50%\">( ) Favorable</td></tr>\n";
		echo "<tr><td width=\"50%\">&nbsp;</td><td width=\"50%\">( ) Desfavorable. Especificar:</td></tr>\n";
		echo "</table> \n";
			echo "<table border=\"0px\"> \n";
			echo "<tr><td width=\"100%\" height=\"2%\">&nbsp;</td></tr>\n";
			echo "</table> \n";
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"50%\"><b>Ordre i neteja de l'embarcación al finalitzar l'ús:</b></td><td width=\"50%\">( ) Favorable</td></tr>\n";
		echo "<tr><td width=\"50%\"><td width=\"50%\">( ) Desfavorable. Especificar:</td></tr>\n";
		echo "</table> \n";
			echo "<table border=\"0px\"> \n";
			echo "<tr><td width=\"100%\" height=\"2%\">&nbsp;</td></tr>\n";
			echo "</table> \n";
		
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"50%\"><b>Nombre d'hores de de motor:</b></td><td width=\"50%\">Inici:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;hores</td></tr>\n";
		echo "<tr><td width=\"50%\"><td width=\"50%\">Final:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;hores</td></tr>\n";
		echo "</table> \n";
		
		echo "<table border=\"0px\"> \n";
		echo "<tr><td><strong><h1></h1></strong>&nbsp;</td></tr>\n";
		echo "<tr><td><b>Signatura del patró i del sol·licitant. Barcelona a&nbsp;&nbsp;&nbsp;&nbsp;de&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;de&nbsp;&nbsp;&nbsp;&nbsp;20__</b></td></tr>\n";
		echo "</table> \n";
		echo "<br>";
			echo "<table border=\"1px\"> \n";
			echo "<tr><td width=\"50%\" height=\"10%\" align=\"center\" valign=\"top\">Nom i Cognoms del Sol·licitant</td><td width=\"50%\" height=\"10%\" align=\"center\" valign=\"top\">Nom i Cognoms del Patr&oacute;/ona</td></tr>\n";
			echo "</table></body></html> \n";
		echo "<table border=\"0px\"> \n";
		echo "<tr><td width=\"100%\" height=\"7%\"><em>S'ha de lliurar una copia complimentada i signada de l'activitat al Centre (Administracio Centre)</em></td></tr>\n";
		echo "</table> \n";
?>