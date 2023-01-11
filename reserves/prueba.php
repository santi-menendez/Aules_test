<?php
header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=\"pdf_vaixell.pdf\"");
passthru("htmldoc --format pdf --left 2.5cm --right 1.5cm --top 1.5cm --bottom 1.5cm " .
         "--headfootsize 9 --header 't' --footer '/' '/'" .
         "--size 'a4' --fontsize 10 --charset 8859-15 " .
	 "--webpage http://www.fnb.upc.edu/intrafnb/aules/reserves/pdf_vaixell.php?dia=$dia");
?>