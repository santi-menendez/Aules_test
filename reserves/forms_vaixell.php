<?php

//------------------------------------------------------------------------------------
// Procediment: PintarMesVaixell()
// Operativa  : Donat un recurs qualsevol dels que estan donats d'alta en el sistema,
//              es pinta el calendari de reserves per al mes i any indicats
//------------------------------------------------------------------------------------
function PintarMesVaixell($mes, $any, $recurs){
	global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;

	//LIMIT DEL CALENDARI
	$LIMIT=24;
	
	//if($recurs==16):
		//Header("Location: ./fora_servei.php");
	//endif;
	
	// Demanem quants dies te el mes que hem de pintar i l'anterior
	$numero_dies=days_in_month ($mes, $any);
	$numero_dies_ant=days_in_month ($mes-1, $any);

	$tpl->define(array(
		table     => "$template_dir/calendar.tpl",
		table_row => "$template_dir/week.tpl",
		day       => "$template_dir/day.tpl"
	));

	// Connectem amb el servidor i seleccionem la BD corresponent
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
	@mysqli_query($conn,"SET NAMES 'utf8'");
	
	// Consultem dades referents al recurs demanat
	$consulta="SELECT *
				FROM tbl_recursos r, tbl_edificis e
				WHERE r.id_recurs=$recurs AND
						e.id_edifici=r.id_edifici";
	$query=@mysqli_query($conn,$consulta);
	$f_desc_recurs=mysqli_result($query,0,"nom_recurs");
	$f_id_responsable=mysqli_result($query,0,"id_responsable");
	$f_edifici=mysqli_result($query,0,"nom_edifici");
	

	// Consultem, per al recurs indicat, les reserves puntuals (num_dia=0) d'aquell mes
	$consulta="SELECT *
				FROM tbl_reserves
				WHERE data_reserva>='$any-$mes-01' AND
						data_reserva<='$any-$mes-$numero_dies' AND
						id_recurs=$recurs AND
						num_dia=0
				ORDER BY data_reserva,hora_inici";
	$query=@mysqli_query($conn,$consulta);
	$nFiles=mysqli_num_rows($query);

	// Consultem, per al recurs indicat, les reserves periodiques (num_dia>0) d'aquell mes
	$consulta_periodics="SELECT *
								FROM tbl_reserves
								WHERE id_recurs=$recurs AND num_dia>0 AND
										((data_reserva<='$any/$mes/01' AND data_final>='$any/$mes/$numero_dies') OR
											(data_final>='$any/$mes/01' AND data_final<='$any/$mes/$numero_dies') OR
											(data_reserva>='$any/$mes/01' AND data_reserva<='$any/$mes/$numero_dies'))
								ORDER BY num_dia, data_reserva, hora_inici";
	$query_periodics=@mysqli_query($conn,$consulta_periodics);
	$nFiles_periodics=mysqli_num_rows($query_periodics);

	// Obtenim informacio de la data a partir del timestamp UNIX del primer dia del mes
	//   * wday es el dia de la setmana en numero (DG:0, DL:1, ..., DS:6)
	$timestamp=getdate(mktime(0,0,0,$mes,1,$any));

	// Si el primer dia del mes no es dilluns, cal fer un tractament especial
	if(monsun($timestamp[wday])>0):
		// Pintem els dies que hi ha abans del dia 1
		for($i=0;$i<monsun($timestamp[wday]);$i++):
			$tpl->assign(array(
				"DAYCOLOUR"   => "disabled_day", // Aquella cel.la esta inactiva
				"ACTIVITIES"  => "&nbsp",		// Ho deixem en blanc
				"D_FUNCTIONS" => "&nbsp",		// No es pot reservar !!!
				"DAYNUM"      => $numero_dies_ant-monsun($timestamp[wday])+$i+1,
				"DIA_MES"     => "daynum_altre_mes"
			));
			$tpl->parse(DAY, ".day");
		endfor;

		// Calculem el primer dia de la setmana per poder fer el zoom per setmanes
		if($mes==1):
			$primer_dia=days_in_month(12,$any-1)-monsun($timestamp[wday]);
			$mes_zoom=12;
			$any_zoom=$any-1;
		else:
			$primer_dia=days_in_month($mes-1,$any)-monsun($timestamp[wday])+1;
			$mes_zoom=$mes-1;
			$any_zoom=$any;
		endif;

	// Si el primer dia del mes es dilluns, cap problema
	else:
		$primer_dia=1;
		$mes_zoom=$mes;
		$any_zoom=$any;
	endif;


	$j=0;
	// Recorrem tots els dies del mes
	for($i=1;$i<=$numero_dies;$i++):
		$activities_tmp="";
		$portem=0;

		// (1) Analitzem les activitats NO PERIODIQUES del mes actual
		if($nFiles>$j):
			$bd_dia=intval(substr(mysqli_result($query, $j, "data_reserva"),8,2));
		endif;

		while(($nFiles>$j)&&($bd_dia==$i)):
			if($portem<$LIMIT):
				$activities_tmp="$activities_tmp <font color=\"Navy\"><b>".substr(mysqli_result($query, $j, 'hora_inici'),0,5)."-".substr(mysqli_result($query, $j, 'hora_final'),0,5)."</b></font><br><a class=\"reserva_cal\" href=\"javascript:openpopup('$PHP_SELF?id_reserva=".mysqli_result($query,$j,'id_reserva')."&op=81')\"><img title=\"Reservat per: ".mysqli_result($query, $j, 'qui_reserva')."\n - Motiu: ".mysqli_result($query, $j, 'motiu_reserva')."\n - Patro: ".mysqli_result($query, $j, 'aula')."\" align=\"right\" src=\"./img/info.gif\" border=\"0\"></a>&nbsp;<br><hr>";
				$portem++;
			endif;
			$j++;
			if($nFiles>$j):
				$bd_dia=intval(substr(mysqli_result($query, $j, "data_reserva"),8,2));
			endif;
		endwhile;

		// (2) Analitzem les activitats PERIODIQUES del mes
		$f_timestamp=mktime(0,0,0,$mes,$i,$any);
		$timestamp=getdate($f_timestamp);
		$numero_dia=monsun($timestamp[wday])+1;
		$k=0;

		if($k<$nFiles_periodics):
			$numero_dia_bd=mysqli_result($query_periodics, $k, "num_dia");
		endif;


		while(($k<$nFiles_periodics)&&($numero_dia>=$numero_dia_bd)&&($portem<$LIMIT)):

			if(($numero_dia==$numero_dia_bd)&&
			(strtotime(mysqli_result($query_periodics, $k, "data_reserva"))<=$f_timestamp)&&
			(strtotime(mysqli_result($query_periodics, $k, "data_final"))>=$f_timestamp)):
				//$activities_tmp="$activities_tmp <font color=\"Navy\"><b>".substr(mysqli_result($query_periodics, $k, 'hora_inici'),0,5)."-".substr(mysqli_result($query_periodics, $k, 'hora_final'),0,5)."<br><i>".substr(mysqli_result($query_periodics, $k, "motiu_reserva"),0,12)."</i></A><br>";
				$activities_tmp="$activities_tmp <font color=\"Navy\"><b>".substr(mysqli_result($query_periodics, $k, 'hora_inici'),0,5)."-".substr(mysqli_result($query_periodics, $k, 'hora_final'),0,5)."</b></font><br><a class=\"reserva_cal\" href=\"javascript:openpopup('$PHP_SELF?id_reserva=".mysqli_result($query_periodics,$k,'id_reserva')."&op=81')\"><img title=\"Reservat per: ".mysqli_result($query_periodics, $k, 'qui_reserva')."\n - Motiu: ".mysqli_result($query_periodics, $k, 'motiu_reserva')."\n - Patro: ".mysqli_result($query_periodics, $k, 'assig')."\" align=\"right\" src=\"./img/info.gif\" border=\"0\"></a>&nbsp;<br><hr>";
				$portem++;
			endif;
			$k++;
			if($k<$nFiles_periodics):
				$numero_dia_bd=mysqli_result($query_periodics, $k, "num_dia");
			endif;
		endwhile;

		$f_tmp=getdate();
		//N�mero de dies amb la que es pot fer la reserva del vaixell
		//$ndias=2;
		$ndias=0;
		if(strtotime("$f_tmp[year]/$f_tmp[mon]/$f_tmp[mday]")<=(strtotime("$any/$mes/$i")-$ndias * 24 * 60 * 60)):
			$f_tmp= E_ADD;
			$str_funcions="&nbsp;<A class=\"m_function\"  class=\"m_function\" href=\"javascript:openpopup('$PHP_SELF?op=15&dia=$i&mes=$mes&any=$any&recurs=$recurs')\" title=\"$f_tmp\"><img align='middle' src='./img/puntual.gif' title='Fer reserva puntual / Veure reserves del dia' alt='Browse' border='0'></A>";
			$str_funcions.="&nbsp;<a href=\"javascript:openpopup_vaixell('$PHP_SELF?recurs=$recurs&op=55')\"><img align='center'  title='Fer reserva per m&eacute;s de un dia seguit' alt='Peri&ograve;dica' src='./img/periodic.gif' border='0'></a>";
			$f_colour="enabled_day";
		else:
			$str_funcions="&nbsp";
			$f_colour="disabled_day";
		endif;

		$tpl->assign(array(
			"D_FUNCTIONS" => $str_funcions,
			"ACTIVITIES"  => $activities_tmp,
			"DAYNUM"      => $i,
			"DAYCOLOUR"   => $f_colour,
			"N_DAYCOLOUR" => "n_$f_colour",
			"DIA_MES"     => "daynum_mes_actual"
		));
		$tpl->parse(DAY,".day");

		if($numero_dia==7):
			// Inserim l'enllas per el zoom setmanal (de moment, deshabilitat)
//			$tpl->assign("WEEK_ZOOM", "<img src='./img/setmanal.gif' title='Vista setmanal (No operatiu)' border='0'>");
			$zoom_setmanal="<a href=\"$PHP_SELF?op=35&u_dia=$i&p_dia=$primer_dia&mes=$mes_zoom&any=$any_zoom&recurs=$recurs\"><img src='./img/setmanal.gif' title='Vista setmanal' border='0'></a>";
			$tpl->assign("WEEK_ZOOM", $zoom_setmanal);
			$primer_dia=$i+1;
			$mes_zoom=$mes;
			$tpl->parse(TABLE_ROWS, ".table_row");
			$tpl->clear(DAY);
		endif;
	endfor;

	if($numero_dia!=7):
		// Si el mes acaba a mitja setmana, l'acabem de pintar amb els dies del mes seguent
		for ($kk=$numero_dia; $kk<7; $kk++):
			$tpl->assign(array(
				"DAYCOLOUR"   => "disabled_day", // Aquella cel.la esta inactiva
				"ACTIVITIES"  => "&nbsp",		// Ho deixem en blanc
				"D_FUNCTIONS" => "&nbsp",		// No es pot reservar !!!
				"DAYNUM"      => $kk-$numero_dia+1,
				"DIA_MES"     => "daynum_altre_mes"
			));
			$tpl->parse(DAY, ".day");
		endfor;
		// Inserim l'enllas per el zoom setmanal (de moment, deshabilitat)
//		$tpl->assign("WEEK_ZOOM", "<img src='./img/setmanal.gif' title='Vista setmanal (No operatiu)' border='0'>");
		$i=7-$numero_dia;
		$zoom_setmanal="<a href=\"$PHP_SELF?op=35&u_dia=$i&p_dia=$primer_dia&mes=$mes_zoom&any=$any_zoom&recurs=$recurs\"><img src='./img/setmanal.gif' title='Vista setmanal' border='0'></a>";
		$tpl->assign("WEEK_ZOOM", $zoom_setmanal);
		$tpl->parse(TABLE_ROWS, ".table_row");
		$tpl->clear(DAY);
	endif;

	switch($mes):
		case  1: $mes_i_any=E_MES1." ".$any; break;
		case  2: $mes_i_any=E_MES2." ".$any; break;
		case  3: $mes_i_any=E_MES3." ".$any; break;
		case  4: $mes_i_any=E_MES4." ".$any; break;
		case  5: $mes_i_any=E_MES5." ".$any; break;
		case  6: $mes_i_any=E_MES6." ".$any; break;
		case  7: $mes_i_any=E_MES7." ".$any; break;
		case  8: $mes_i_any=E_MES8." ".$any; break;
		case  9: $mes_i_any=E_MES9." ".$any; break;
		case 10: $mes_i_any=E_MES10." ".$any; break;
		case 11: $mes_i_any=E_MES11." ".$any; break;
		case 12: $mes_i_any=E_MES12." ".$any; break;
	endswitch;


	$tpl->assign("YEARMONTH", "$mes_i_any");
	if($mes==12):
		$p_mes=1;
		$p_any=$any+1;
	else:
		$p_mes=$mes+1;
		$p_any=$any;
	endif;
	$endavant=sprintf("<a title='Mes seg&uuml;ent' href=\"$PHP_SELF?mes=%s&any=%s&recurs=%s&op=31\"><img align='absmiddle' alt='>>' src='./img/forward.jpg' border='0'></a>",$p_mes,$p_any,$recurs);
	if($mes==1):
		$a_mes=12;
		$a_any=$any-1;
	else:
		$a_mes=$mes-1;
		$a_any=$any;
	endif;
	$endarrera=sprintf("<a title='Mes anterior' href=\"$PHP_SELF?mes=%s&any=%s&recurs=%s&op=31\"><img align='absmiddle' alt='<<' src='./img/back.jpg' border='0'></a>",$a_mes,$a_any,$recurs);
	//$f_tmp=sprintf("%s %s",$endarrera,$endavant);
	$tpl->assign("FUNC_AV_CALENDAR", $endavant);
	$tpl->assign("FUNC_EN_CALENDAR", $endarrera);
	$tpl->assign("DESC_RECURS", $f_desc_recurs);
	$tpl->assign("EDIFICI", $f_edifici);

	$tpl->assign(array(
		"E_DILLUNS"   => E_M_DILLUNS,
		"E_DIMARTS"   => E_M_DIMARTS,
		"E_DIMECRES"  => E_M_DIMECRES,
		"E_DIJOUS"    => E_M_DIJOUS,
		"E_DIVENDRES" => E_M_DIVENDRES,
		"E_DISSABTE"  => E_M_DISSABTE,
		"E_DIUMENGE"  => E_M_DIUMENGE
	));

	$tpl->parse(PAGE_CONTENT, "table");
}


//-----------------------------------------------------------------------------------------------------------
// Procediment: CheckPeriodicReservation_Vaixell()
// Operativa  : Comprova una determinada reserva periodica per al recurs indicat
//-----------------------------------------------------------------------------------------------------------
function CheckPeriodicReservation_Vaixell($data_inici, $data_final, $dia, $recurs, $qui, $motiu, $hora_inici, $hora_final, $uid){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir, $enviar_correu;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/aceptar_reserva.tpl"
	));

	$resultat=0;
	$a_date=dateToArray($data_inici);
	$dia0=$a_date[0]; $mes0=$a_date[1]; $any0=$a_date[2];
	$a_date=dateToArray($data_final);
	$dia1=$a_date[0]; $mes1=$a_date[1]; $any1=$a_date[2];
	//Agreguem 48 hores a la data que ens indiquen
	$hores_antelacio=1;
	//data de la reserva en format UNIX per fer la comparaci�
	$data_reserva=mktime($hora_inici,0,0,$mes0,$dia0,$any0);
	//Data actual + 48 hores
	$add_48=mktime(date('H')+$hores_antelacio,date('i'),date('s'),date('m'),date('d'),date('Y'));
	//print $add_48;
	//echo "<br>";
	//print $data_reserva;

	if((!checkdate($mes0,$dia0,$any0)) || (!checkdate($mes1,$dia1,$any1))):
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_DATA_NO_VAL);
	elseif ("$add_48">"$data_reserva"):
		// No han omplert el camp QUI o MOTIU
		$resultat=6;
		$tpl->assign("E_MOTIUS", E_ERR_DATA_48_VAL);
	elseif (($qui=="") || ($motiu=="")):
		// No han omplert el camp QUI o MOTIU
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_MOTIU_NO_VAL);
	else:
		if(($hora_inici<$hora_minim) || (($hora_inici>=$hora_final)&&($dia0==$dia1)) || ($hora_final>$hora_maxima)):
			$resultat=1;
			$tpl->assign("E_MOTIUS", E_ERR_HORA_NO_VAL);
		else:
			$f_hora_inici="$hora_inici:00:00";
			$f_hora_final="$hora_final:00:00";
			//$f_data="$any/$mes/$dia";
			/*	S'han d'agafar totes aquelles reserves que estan dins l'interval on
				la seva data coincideix amb el dia que ens han donat. Fent servir el DAYOFWEEK
				passar el $dia per sunmon+1
			*/
			// Consultar la base de dades per si es pot fer la reserva
			$conn = @mysqli_connect($dbserver,$dbuser,$dbpass) or die(E_ERR_BD_CONNECT);
			//Seleccionem la BD corresponent
			@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_BD);
			@mysqli_query($conn,"SET NAMES 'utf8'");
			$f_numdia=sunmon($dia);
			$f_data_inici="$any0/$mes0/$dia0";
			$f_data_final="$any1/$mes1/$dia1";
			//echo $f_data_inici;
			//echo $f_data_final;
			// Ara llegim les diferents reserves que hi ha d'aquell recurs per aquella hora
			$consulta="SELECT id_recurs, qui_reserva, motiu_reserva, data_reserva, data_final, hora_inici,  hora_final
						FROM tbl_reserves
						WHERE ((data_reserva>='$f_data_inici') AND (data_reserva<='$f_data_final')) AND
								id_recurs=$recurs AND
								DAYOFWEEK(data_reserva)=$f_numdia AND
								(((hora_inici<='$f_hora_inici') AND (hora_final>'$f_hora_inici')) OR
									((hora_final>='$f_hora_final') AND (hora_inici<'$f_hora_final')) OR
									((hora_inici<=\"$f_hora_inici\") AND (\"$f_hora_final\"<=hora_final)) OR
									((hora_inici>=\"$f_hora_inici\") AND (hora_final<=\"$f_hora_final\"))) AND
									num_dia=0
						ORDER BY hora_inici";

			$query=@mysqli_query($conn,$consulta);

			if(mysqli_num_rows($query)>0):
				$resultat=2;
				//printf("$query");

				$taula=mysqli_result_table($query);
				$f_tmp=E_ERR_HI_HA_RESERVA."<br><br>".$taula;
					$tpl->assign("E_MOTIUS",$f_tmp);
			else:
				/*	S'han d'agafar totes les periodiques que puguin estar entre data_inici
					i data_final, que coincideixin en que dia=num_dia.
				*/

					$consulta_periodics="SELECT id_recurs, qui_reserva, motiu_reserva, data_reserva, data_final, hora_inici, hora_final
										FROM tbl_reserves
										WHERE (((data_reserva<=\"$f_data_inici\")AND(data_final>\"$f_data_inici\")) OR
					((data_reserva<\"$f_data_final\")AND(data_final>=\"$f_data_final\")) OR
					((data_reserva<=\"$f_data_inici\")AND(data_final>=\"$f_data_final\"))	OR
					((data_reserva>=\"$f_data_inici\")AND(data_final<=\"$f_data_final\")) )AND
					(((hora_inici<=\"$f_hora_inici\") AND (hora_final>\"$f_hora_inici\")) OR
					((hora_final>=\"$f_hora_final\") AND (\"$f_hora_final\">hora_inici)) OR
					((hora_inici<=\"$f_hora_inici\") AND (\"$f_hora_final\"<=hora_final)) OR
					((hora_inici>=\"$f_hora_inici\") AND (hora_final<=\"$f_hora_final\")))
					AND num_dia=$dia AND id_recurs=$recurs ORDER BY hora_inici";

				$query_periodics=@mysqli_query($conn,$consulta_periodics);
				if(mysqli_num_rows($query_periodics)>0):
					$resultat=3;
						$f_tmp=E_ERR_HI_HA_RESERVA."<br><br>".$taula;

				else:
					$resultat=0;
				endif;
			endif;
		endif;
	endif;
	return $resultat;
}

//-----------------------------------------------------------------------------------------------------------
// Procediment: accept_periodic_reservation_vaixell()
// Operativa  : Comprova una determinada reserva periodica per al recurs indicat i continua amb el formulari
//				de reserva.
//-----------------------------------------------------------------------------------------------------------
function accept_periodic_reservation_vaixell($data_inici,$data_final,$dia,$recurs,$qui,$motiu,$hora_inici,$hora_final,$patro,$dni_patro,$titol,$titulacio_patro,$embarcats,$i,$hora_final_pdf,$altres,$id_periodic){
	global $tpl;
	global $hora_maxima_vaixell;
	global $hora_minima_vaixell;
	global $dbname;
	global $dbuser;
	global $dbpass;
	global $dbserver;
	global $lang;
	global $locales;
	global $template_dir;
	global $enviar_correu;
	global $url_reserves;

		if($recurs=='16') :
    		$tpl->define( array(page => "$template_dir/minipage.tpl",
        						reserva => "$template_dir/aceptar_reserva_vaixell_periodic.tpl"
			));
		elseif($recurs=='7' || $recurs=='9') :
    		$tpl->define( array(page => "$template_dir/minipage.tpl",
        						reserva => "$template_dir/aceptar_reserva_vaixell_periodic_minina.tpl"
			));
		endif;	
        $resultat=0;
        $a_date=dateToArray($data_inici);
        $mes0=$a_date[1];
        $dia0=$a_date[0];
        $any0=$a_date[2];
        $a_date=dateToArray($data_final);
        $mes1=$a_date[1];
        $dia1=$a_date[0];
        $any1=$a_date[2];

        if((!checkdate($mes0,$dia0,$any0))||(!checkdate($mes1,$dia1,$any1))){
        	$resultat=5;
	        $tpl->assign("E_MOTIUS", E_ERR_DATA_NO_VAL);
	}else if((strlen(trim($qui))==0)||(strlen(trim($motiu))==0)){
		$resultat=5;
	        $tpl->assign("E_MOTIUS", E_ERR_MOTIU_NO_VAL);
	
	}else{

/*		if(time()>mktime(0,0,0,$dia0,$mes0,$any0)){
			echo time();
			echo mktime(0,0,0,$dia0,$mes0,$any0);
			
			$resultat=6;
			$tpl->assign(E_MOTIUS,$locales[$lang]["E_ERR_DATA_INICI_NO_VAL"]);
		}else{
*/
		        if(($hora_inici<$hora_minim_vaixell)||(($hora_inici>=$hora_final)&&($dia0==$dia1))||($hora_final>$hora_maxima_vaixell)){
		        	$resultat=1;
		        	$tpl->assign("E_MOTIUS", E_ERR_HORA_NO_VAL);
		
		        }else{
		        	
		        	$f_hora_inici="$hora_inici:00:00";
		        	$f_hora_final="$hora_final:00:00";
					//En cas d'especificar ALTRES en la selecci� de la reserva del Vaixell, em de inserir l'esmentada explicaci�, en lloc del motiu
					//ja tipificat
					if ($motiu==5):
						$motiu=$altres;
					endif;
		        	//$f_data="$any/$mes/$dia";
		        	/*	S'han d'agafar totes aquelles reserves que estan dins l'interval on
		        		la seva data coincideix amb el dia que ens han donat. Fent servir el DAYOFWEEK
		        		passar el $dia per sunmon+1
		        	*/
		        	// Consultar la base de dades per si es pot fer la reserva
				$conn = @mysqli_connect($dbserver,$dbuser,$dbpass) or printf(E_ERR_BD_CONNECT);

				//Seleccionem la BD corresponent
				@mysqli_select_db($conn, $dbname) or printf(E_ERR_SELECT_BD);
				@mysqli_query($conn,"SET NAMES 'utf8'");
				$f_numdia=sunmon($dia);
				$f_data_inici=sprintf("%s/%s/%s",$any0,$mes0,$dia0);
				$f_data_final=sprintf("%s/%s/%s",$any1,$mes1,$dia1);
				$f_data_inici_reserva=sprintf("%s/%s/%s",$dia0,$mes0,$any0);
				$f_data_final_reserva=sprintf("%s/%s/%s",$dia1,$mes1,$any1);
		
				// Ara assignem el valor del identificador de l'aula amb l'aula	
				$consulta_aula="SELECT *
								FROM tbl_recursos
								WHERE id_recurs=$recurs";
				$query_aula=@mysqli_query($conn,$consulta_aula);
				$aula=mysqli_result($query_aula,0,"nom_recurs");
				$f_tipus_reserva=mysqli_result($query_aula,0,"id_tipus");
				// Ara llegim les diferents reserves que hi ha d'aquell recurs per aquella hora
				$consulta="SELECT id_recurs, qui_reserva, motiu_reserva, data_reserva, data_final,
						 hora_inici,  hora_final FROM tbl_reserves
						 WHERE ((data_reserva>=\"$f_data_inici\") AND (data_reserva<=\"$f_data_final\")) AND 
						 	id_recurs=$recurs AND DAYOFWEEK(data_reserva)=$f_numdia AND
						 	(((hora_inici<=\"$f_hora_inici\") AND (hora_final>\"$f_hora_inici\")) OR
						 	((hora_final>=\"$f_hora_final\") AND (\"$f_hora_final\">hora_inici)) OR 
						 	((hora_inici<=\"$f_hora_inici\")AND(\"$f_hora_final\"<=hora_final)) OR
						 	((hora_inici>=\"$f_hora_inici\")AND(hora_final<=\"$f_hora_final\"))) 
						 	AND num_dia=0 ORDER BY hora_inici";
					
				$query=@mysqli_query($conn,$consulta);
				
				if(mysqli_num_rows($query)>0){
					$resultat=2;
					//printf("$query");

					//$taula=mysqli_result_table($query);
					$f_tmp=sprintf("%s<br><br> %s", E_ERR_HI_HA_RESERVA, $taula);
		    			$tpl->assign("E_MOTIUS",$f_tmp);
				}else{
					/*	S'han d'agafar totes les periodiques que puguin estar entre data_inici
						i data_final, que coincideixin en que dia=num_dia.
					*/


					//$numero_dia=monsun($femtemps[wday])+2;


		        		$consulta_periodics="SELECT id_recurs, qui_reserva, motiu_reserva, data_reserva, data_final,
						 hora_inici,  hora_final FROM tbl_reserves
						 WHERE (((data_reserva<=\"$f_data_inici\")AND(data_final>\"$f_data_inici\")) OR
						  ((data_reserva<\"$f_data_final\")AND(data_final>=\"$f_data_final\")) OR
						  ((data_reserva<=\"$f_data_inici\")AND(data_final>=\"$f_data_final\"))	OR
						  ((data_reserva>=\"$f_data_inici\")AND(data_final<=\"$f_data_final\")) )AND
						 (((hora_inici<=\"$f_hora_inici\") AND (hora_final>\"$f_hora_inici\")) OR
						 ((hora_final>=\"$f_hora_final\") AND (\"$f_hora_final\">hora_inici)) OR
						 ((hora_inici<=\"$f_hora_inici\") AND (\"$f_hora_final\"<=hora_final)) OR
						 ((hora_inici>=\"$f_hora_inici\") AND (hora_final<=\"$f_hora_final\")))
						 AND num_dia=$dia AND id_recurs=$recurs ORDER BY hora_inici";

				$query_periodics=@mysqli_query($conn,$consulta_periodics);
					if(mysqli_num_rows($query_periodics)>0){
						$resultat=3;
						//$taula=mysqli_result_table($query_periodics);
		    				$f_tmp=sprintf("%s<br><br> %s",E_ERR_HI_HA_RESERVA,$taula);
		    				$tpl->assign("E_MOTIUS",$f_tmp);
					}else{
						// Ara ja podem inserir i recollir el resultat
						/*$cometes = array("'","\"");
						str_replace($cometes, "�", $titulacio_patro);*/
						$consulta_insert="INSERT INTO tbl_reserves(id_recurs,qui_reserva,motiu_reserva,assig,aula,
								data_reserva,data_final,hora_inici,hora_final,num_dia,tipus_reserva,maq_eines,desc_consum,desc_activitat,assistents,consum,id_periodic)
								VALUES (\"$recurs\",\"$qui\",\"$motiu\",\"$patro\",\"$aula\",\"$f_data_inici\",\"$f_data_final\",\"$f_hora_inici\",
								\"$f_hora_final\",\"$dia\",\"$f_tipus_reserva\",\"$dni_patro\",\"$titulacio_patro\",\"$altres\",\"$embarcats\",\"$titol\",\"$id_periodic\")";
						$query_periodics=@mysqli_query($conn,$consulta_insert);
						if (mysqli_affected_rows($conn) == 1) {
		       					$tpl->assign("E_MOTIUS","");
								$last_id=mysqli_insert_id();
		     				} else {
		       					$resultat=4;
		       					$tpl->assign("E_MOTIUS", E_ERR_INSERT_DB);
		     				}
		        		}
		        	}
			}

	};

	if($resultat==0 && $i==0):
		// S'ha fet la reserva sense problemes.
		// Ho notifiquem per pantalla i, si procedeix, enviem el mail de confirmacio...
		$tpl->assign("E_MSG_RESERVA", E_MSG_RESERVA_RSLT);

		$consulta_enviar_correu="SELECT re.avisar_resp as avisar,
											u.email as email, u.email_fnb as email_fnb,
											re.nom_recurs as recurs
									FROM tbl_recursos re,
											tbl_usuaris u
									WHERE re.id_responsable=u.id_usuari AND
											re.id_recurs='$recurs'";
		$query_enviar_correu=@mysqli_query($conn,$consulta_enviar_correu);
		
		if(mysqli_result($query_enviar_correu,0,"avisar")=="1"):
			$consulta_email="SELECT email,email_fnb,nom_usuari
							FROM tbl_usuaris
							WHERE nom_usuari='$qui'";
			$query_email=@mysqli_query($conn,$consulta_email);
			//Si cal avisar de les reserves del recurs, enviem email al responsable
			$from="No respondre <noreply@fnb.upc.edu>";
			$to=mysqli_result($query_email,0,"email_fnb");
			$cc="<jmateu@fnb.upc.edu>";
			$bcc="<centre.calcul@fnb.upc.edu>";
			$subject="Reservator v.1.1: Nova reserva ".html_entity_decode('petici&oacute;', ENT_NOQUOTES, 'UTF-8')." de \"".$aula."\"";
			$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Si us plau, no el contesteu.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
			$body.="S'ha fet una nova reserva del \"".$aula."\".<br><br><b>Usuari</b>: $qui.<br><b>Activitat/Assignatura</b>: $altres.<br><b>Motiu</b>: $motiu.<br><b>Dia de inici</b>: $data_inici <b>a les</b> $hora_inici. <br><b>Data de finalitzaci&oacute;</b>: $data_final <b>a les</b> $hora_final_pdf.<br><b>Patr&oacute;</b>: $patro.<br><b>Titulaci&oacute; Patr&oacute;</b>: $titulacio_patro.<br><b>DNI Patr&oacute;</b>: $dni_patro.";
			$body.="<p>Clicar <a href='$url_reserves?any=$any0&mes=$mes0&recurs=$recurs&op=31'>aqui</a> per veure la reserva.</p>";
			$body.="<p>Clicar <a href='$url_reserves?id_reserva=$last_id&op=81'>aqui</a> per veure el document PDF a omplir i lliurar degudament complimentat a l'Administraci&oacute; del Centre.</p>";
			$signature="Centre de C&agrave;lcul de la FNB</font>";

			EnviarEmail($from, $to, $cc, $cc1, $bcc, $subject, $body, $signature, $err_msg);
			//mail($f_mail,,"L'usuari $qui ha fet una reserva de $f_recurs pel motiu: $motiu","From: reservator@$SERVER_NAME");
		endif;
	endif;

		$tpl->assign("E_MSG_RESERVA", E_MSG_RESERVA_RSLT);
		$tpl->assign("E_MSG_RESERVA_VAIXELL", E_MSG_RESERVA_VAIXELL);
		$tpl->assign("E_DATA_RESERVA", $f_data_inici_reserva);
		$tpl->assign("E_DATA_FINAL", $f_data_final_reserva);
		$tpl->assign("E_RECURS", $recurs);
		$tpl->assign("E_QUI", $qui);
		$tpl->assign("E_MOTIU", $motiu);
		$tpl->assign("E_HORA_INICI", $hora_inici);
		$tpl->assign("E_HORA_FINAL", $hora_final_pdf);
		$tpl->assign("E_PATRO", $patro);
		$tpl->assign("E_DNI_PATRO", $dni_patro);
		$tpl->assign("E_TITULACIO_PATRO", $titulacio_patro);
		$tpl->assign("E_TITOL", $titol);
		$tpl->assign("E_EMBARCATS", $embarcats);
		$tpl->assign("E_ALTRES", $altres);
		
	$tpl->parse(PAGE_CONTENT,"reserva");
}

//-----------------------------------------------------------------------------------------------------------
// Procediment: ValidarReserva_Vaixell_2()
// Operativa  : Arriben els paramentres d'una reserva i cal comprovar que sigui valida
//-----------------------------------------------------------------------------------------------------------
function ValidarReserva_Vaixell_2($data_inici,$data_final,$recurs,$qui,$motiu,$hora_inici,$hora_final,$patro,$dni_patro,$titol,$titulacio_patro,$embarcats,$motiu,$altres){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir, $enviar_correu, $url_reserves;

	if ($recurs==7 || $recurs==9):
		$tpl->define(array(
			page    => "$template_dir/minipage.tpl",
			reserva => "$template_dir/aceptar_reserva_vaixell_minina.tpl"
		));
	elseif ($recurs==16):
		$tpl->define(array(
			page    => "$template_dir/minipage.tpl",
			reserva => "$template_dir/aceptar_reserva_vaixell.tpl"
		));
	endif;
	
// Connectem amb el servidor i seleccionem la BD corresponent
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
	@mysqli_query($conn,"SET NAMES 'utf8'");
	$consulta_aula="SELECT *
		FROM tbl_recursos
		WHERE id_recurs=$recurs";
	$query_aula = @mysqli_query($conn,$consulta_aula);
	$aula = mysqli_result($query_aula,0,"nom_recurs");
	$f_tipus_reserva = mysqli_result($query_aula,0,"id_tipus");
			
	if($_SESSION['perfil']=='CCESAII' OR $_SESSION['perfil']=='Usuari PDI VAIXELL'):
		return $gestio=1;
	else:
		EnviarCorreuPeticioReserva_Vaixell_2($data_inici,$data_final,$recurs,$qui,$motiu,$hora_inici,$hora_final,$patro,$dni_patro,$titol,$titulacio_patro,$embarcats,$motiu,$altres,$f_tipus_reserva);
		$resultat=9;
	endif;

	if($resultat==0):
		// Sha fet la reserva sense problemes.
		// Ho notifiquem per pantalla i, si procedeix, enviem el mail de confirmacio...
		$tpl->assign("E_MSG_RESERVA", E_MSG_RESERVA_RSLT);
		$tpl->assign("E_MSG_RESERVA_VAIXELL", E_MSG_RESERVA_VAIXELL);
		$tpl->assign("E_DATA_RESERVA", $f_data_inici);
		$tpl->assign("E_DATA_FINAL", $data_final);
		$tpl->assign("E_RECURS", $recurs);
		$tpl->assign("E_QUI", $qui);
		$tpl->assign("E_MOTIU", $motiu);
		$tpl->assign("E_HORA_INICI", $hora_inici);
		$tpl->assign("E_HORA_FINAL", $hora_final);
		$tpl->assign("E_PATRO", $patro);
		$tpl->assign("E_DNI_PATRO", $dni_patro);
		$tpl->assign("E_TITOL", $titol);
		$tpl->assign("E_TITULACIO_PATRO", $titulacio_patro);
		$tpl->assign("E_EMBARCATS", $embarcats);
		$tpl->assign("E_ALTRES", $altres);
		//Mirem si el responsable del recurs ha de rebre correu
		$consulta_enviar_correu="SELECT re.avisar_resp as avisar,
											u.email as email, u.email_fnb as email_fnb,
											re.nom_recurs as recurs
									FROM tbl_recursos re,
											tbl_usuaris u
									WHERE re.id_responsable=u.id_usuari AND
											re.id_recurs='$recurs'";
		$query_enviar_correu=@mysqli_query($conn,$consulta_enviar_correu);
		if(mysqli_result($query_enviar_correu,0,"avisar")=="1"):
			$consulta_email="SELECT email,email_fnb,nom_usuari
							FROM tbl_usuaris
							WHERE nom_usuari='$qui'";

			$query_email=@mysqli_query($conn,$consulta_email);
			if ($enviar_correu=="1"):
				//Si cal avisar de les reserves del recurs, enviem email al responsable
				$from="No respondre <noreply@fnb.upc.edu>";
				$to=mysqli_result($query_email,0,"email_fnb");
				$to="<jmateu@cen.upc.edu>";
				$bcc="<centre.calcul@fnb.upc.edu>";
				$subject="Reservator v.1.1: Nova reserva de \"".$aula."\"";
				$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Si us plau, no el contesteu.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
				$body.="S'ha fet una nova reserva de \"".$aula."\".<br><br><b>Usuari</b>: $qui.<br><b>Activitat/Assignatura</b>: $altres.<br><b>Motiu</b>: $motiu.<br><b>Dia</b>: $data_inici.<br><b>Horari</b>: de $f_hora_inici a $f_hora_final.<br><b>Patr&oacute;</b>: $patro.<br><b>Titulaci&oacute; Patr&oacute;</b>: $titulacio_patro.<br><b>DNI Patr&oacute;</b>: $dni_patro.";
				$body.="<p>Clicar <a href='$url_reserves?any=$any&mes=$mes&recurs=$recurs&op=31'>aqui</a> per veure la reserva.</p>";
				$body.="<p>Clicar <a href='$url_reserves?id_reserva=$last_id&op=81'>aqui</a> per veure el document PDF a omplir i lliurar degudament complimentat a l'Administraci&oacute; del Centre.</p>";
				$signature="Centre de C&agrave;lcul FNB</font>";

				EnviarEmail($from, $to, $cc, $cc1, $bcc, $subject, $body, $signature, $err_msg);
				//mail($f_mail,,"L'usuari $qui ha fet una reserva de $f_recurs pel motiu: $motiu","From: reservator@$SERVER_NAME");
			endif;
		endif;


	else:
		if($resultat==9):
			if($f_tipus_reserva==5):
				$tpl->assign("E_MSG_RESERVA", E_MSG_PETICIO_RESERVA_RSLT_VAIXELL);
			endif;
			$tpl->assign("E_MOTIUS","");
			$tpl->assign("E_MSG_RESERVA_VAIXELL", "");
		else:
			$tpl->assign("E_MSG_RESERVA", E_MSG_NO_RESERVA_RSLT);
		endif;
	endif;

	$tpl->parse(PAGE_CONTENT,"reserva");
	
}

//-----------------------------------------------------------------------------------------------------------
// Procediment: GestioReserva_Vaixell_2()
// Operativa  : Arriben els paramentres d'una reserva i cal comprovar que sigui valida
//-----------------------------------------------------------------------------------------------------------
function GestioReserva_Vaixell_2($data_inicial,$data_final,$recurs,$qui,$motiu,$hora_inici,$hora_final,$patro,$dni_patro,$titol,$titulacio_patro,$embarcats,$motiu,$altres,$f_tipus_reserva){
	global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/gestio_reserva_vaixell_2.tpl"
	));
	$resultat=0;

	// Connectem amb el servidor i seleccionem la BD corresponent
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
	@mysqli_query($conn,"SET NAMES 'utf8'");
	//Comprovem que realment pot eliminar aquesta reserva

	//$consulta="SELECT * FROM tbl_reserves WHERE id_responsable=\"$_SESSION['r_id_usuari']\"
	//	AND id_recurs=\"$recurs\"";

	$consulta="SELECT *	FROM tbl_recursos, tbl_usuaris WHERE tbl_recursos.id_recurs=\"$recurs\"";

	$query=@mysqli_query($conn,$consulta);

		$tpl->assign(array(
			"E_MSG_GEST_RESERVA"   => E_MSG_GEST_RESERVA,
			"E_DATA_INICI"         => E_DATA_INICI,
			"E_DATA_FINAL"         => E_DATA_FINAL,
			"E_HORA_INICI"         => E_HORA_INICI,
			"E_HORA_FINAL"         => E_HORA_FINAL,
			"E_DIA_SETMANA"        => E_DIA_SETMANA,
			"E_PETICIO"            => E_PETICIO,
			"E_RESPONSABLE_RECURS" => E_RESPONSABLE_RECURS,
			"E_QUI"                => E_QUI,
			"E_MOTIU"              => E_MOTIU,
			"E_PATRO"			   => E_PATRO,
			"E_DNI_PATRO"		   => E_DNI_PATRO,
			"E_TITULACIO_PATRO"	   => E_TITULACIO_PATRO,
			"E_TITOL"			   => E_TITOL,
			"E_EMBARCATS"		   => E_EMBARCATS,
			"E_ALTRES"			   => E_ALTRES,
			"E_ACTIVITAT"		   => E_ACTIVITAT

		));

		switch (strftime("%u", mktime(0, 0, 0, $mes, $dia, $any))):
			case 0: $dia_txt=E_DIA0; break;
			case 1: $dia_txt=E_DIA1; break;
			case 2: $dia_txt=E_DIA2; break;
			case 3: $dia_txt=E_DIA3; break;
			case 4: $dia_txt=E_DIA4; break;
			case 5: $dia_txt=E_DIA5; break;
			case 6: $dia_txt=E_DIA6; break;
			case 7: $dia_txt=E_DIA7; break;
		endswitch;
		$assig=stripslashes($assig);
		$motiu = stripslashes($motiu);
		$tpl->assign(array(
			"DATA_RESERVA"	=> $data_inicial,
			"DATA_FINAL" 	=> $data_final,
			"HORA_INICI" 	=> $hora_inici,
			"HORA_FINAL" 	=> $hora_final,
			"QUI" 			=> $qui,
			"EMAIL" 		=> $email,
			"MOTIU" 		=> $motiu,
			"DIA_TXT" 		=> $dia_txt,
			"PATRO" 		=> $patro,
			"ASSIG" 		=> $assig,
			"DNI_PATRO"		=> $dni_patro,
			"TITULACIO_PATRO"=>$titulacio_patro,
			"TITOL"			=> $titol,
			"EMBARCATS"		=> $embarcats,
			"ALTRES"		=> $altres,
			"DIA" 			=> $dia,
			"MES" 			=> $mes,
			"ANY" 			=> $any,
			"RECURS" 		=> $recurs,
			"RESPONSABLE"	=> mysqli_result($query,0,"tbl_usuaris.nom_usuari"),
			"DESC_RECURS"   => mysqli_result($query,0,"nom_recurs")
		));
	
	$tpl->parse(PAGE_CONTENT,"reserva");
}

//-----------------------------------------------------------------------------------------------------------
// Procediment: EnviarCorreuPeticioReserva_Vaixell_2();
// Operativa  : Enviem un correu de petici� de reserva a la Secretaria de Direcci� i al usuari.
//-----------------------------------------------------------------------------------------------------------
function EnviarCorreuPeticioReserva_Vaixell_2($data_inici,$data_final,$recurs,$qui,$motiu,$hora_inici,$hora_final,$patro,$dni_patro,$titol,$titulacio_patro,$embarcats,$motiu,$altres,$f_tipus_reserva){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir, $enviar_correu, $url_reserves;
	
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
	$consulta_email="SELECT email,email_fnb,nom_usuari
							FROM tbl_usuaris
							WHERE nom_usuari='$qui'";
	
	$query_email=@mysqli_query($conn,$consulta_email);
	//Si cal avisar de les reserves del recurs, enviem email al responsable
	$from="Reserva Vaixell <".mysqli_result($query_email,0,"email").">";
	$patro = stripslashes($patro);
	$motiu = stripslashes($motiu);
	if ($f_tipus_reserva==5):
		//$to="centre.calcul@fnb.upc.edu";
		//$cc="cap.serveis@fnb.upc.edu";
		$to="jmateu@cen.upc.edu";
		$cc="<".mysqli_result($query_email,0,"email_fnb").">";
		$bcc="centre.calcul@fnb.upc.edu";
		$subject="".html_entity_decode('Petici&oacute;', ENT_NOQUOTES, 'UTF-8')." de reserva del Vaixell BARCELONA per part de \"".html_entity_decode(mysqli_result($query_email,0,"nom_usuari"))."\"";
		//$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Aviat rebreu confirmaci&oacute; de la vostra petici&oacute;.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
		$body="<b>Missatge generat autom&agrave;ticament. Aviat rebreu confirmaci&oacute; de la vostra petici&oacute;.</b><br>";
		$body.="S'ha fet una petici&oacute; de reserva de \"".mysqli_result($query_email,0,"nom_usuari")."\".<br><br><b>Usuari</b>: $qui.<br><b>Activitat/Assignatura</b>: $altres.<br><b>Motiu</b>: $motiu.<br><b>Dia de inici</b>: $data_inici <b>a les</b> $hora_inici. <br><b>Data de finalitzaci&oacute;</b>: $data_final <b>a les</b> $hora_final.<br><b>Patr&oacute;</b>: $patro.<br><b>Titulaci&oacute; Patr&oacute;</b>: $titulacio_patro.<br><b>DNI Patr&oacute;</b>: $dni_patro.";
		$body.="<p>Clicar <a href=\"$url_reserves?data_inici=$data_inici&data_final=$data_final&recurs=$recurs&qui=$qui&patro=$patro&motiu=$motiu&hora_inici=$hora_inici&hora_final=$hora_final&dni_patro=$dni_patro&titol=$titol&titulacio_patro=$titulacio_patro&embarcats=$embarcats&altres=$altres&f_tipus_reserva=$f_tipus_reserva&$validar_de_nuevo=0&op=57\">aqui</a> per validar la reserva.</p>";
		$signature="Centre de C&agrave;lcul de la FNB</font>";
	else:
		$to="centre.calcul@fnb.upc.edu";
		//$cc="cap.serveis@fnb.upc.edu";
		//$cc1="<".mysqli_result($query_email,0,"email_fnb").">";
		//$bcc="centre.calcul@fnb.upc.edu";
		$subject="".html_entity_decode('Petici&oacute;', ENT_NOQUOTES, 'UTF-8')." de reserva dels Espais Docents de \"".html_entity_decode(mysqli_result($query_email,0,"nom_usuari"))."\"";
		//$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Aviat rebreu confirmaci&oacute; de la vostra petici&oacute;.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
		$body="<b>Missatge generat autom&agrave;ticament. Aviat rebreu confirmaci&oacute; de la vostra petici&oacute;.</b><br>";
		$body.="S'ha fet una petici&oacute; de reserva de \"".mysqli_result($query_email,0,"nom_usuari")."\".<br><br><b>Usuari</b>: $qui.<br><b>Activitat/Assignatura</b>: $assig.<br><b>Motiu</b>: $motiu.<br><b>Dia</b>: $dia/$mes/$any.<br><b>Horari</b>: de $hora_inici a $hora_final.<br><b>Lloc</b>: $aula.<br><b>Projector</b>: $project.";
		$body.="<p>Clicar <a href=\"$url_reserves?dia=$dia&mes=$mes&any=$any&recurs=$recurs&qui=$qui&assig=$assig&motiu=$motiu&hora_inici=$hora_inici&hora_final=$hora_final&aula=$aula&uid=0&projector=$projector&f_tipus_reserva=$f_tipus_reserva&op=62\">aqui</a> per validar la reserva.</p>";
		$signature="Centre de C&agrave;lcul de la FNB</font>";
	endif;
	EnviarEmail($from, $to, $cc, $cc1, $bcc, $subject, $body, $signature, $err_msg);
	//mail($f_mail,,"L'usuari $qui ha fet una reserva de $f_recurs pel motiu: $motiu","From: reservator@$SERVER_NAME");
}

//-----------------------------------------------------------------------------------------------------------
// Procediment: PintarReservesDia_Vaixell()
// Operativa  : Mostra totes les reserves que hi ha per a un recurs i dia determinats
//-----------------------------------------------------------------------------------------------------------
function PintarReservesDia_Vaixell($dia, $mes, $any, $recurs){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;

	$tpl->define(array(
	 	page      => "$template_dir/minipage.tpl",
		table     => "$template_dir/oneday.tpl",
		table_row => "$template_dir/activity.tpl"
	));

	// Connectem amb el servidor i seleccionem la BD corresponent
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
	@mysqli_query($conn,"SET NAMES 'utf8'");
	//Em d'averiguar el uid, ja que un usuari pot tenir mes de un username ja que es poden validar a m�s d'un servidor LDAP diferent o tenir
	//un alias. I el uid es �nic per usuari.
	$uid_sessio=$_SESSION['nom_usuari'];
	$consulta="SELECT *
				FROM tbl_usuaris
				WHERE nom_usuari='$uid_sessio'";
	$query=@mysqli_query($conn,$consulta);
	$f_uid_sessio=mysqli_result($query,0,"uid");

	// Consultem les reserves per a un dia determinat

	$consulta="SELECT *
				FROM tbl_recursos r,
					tbl_usuaris u,
					tbl_aules a
				WHERE r.id_recurs=$recurs AND
					u.id_usuari=r.id_responsable";
	
	$query=@mysqli_query($conn,$consulta);
	$f_desc_recurs=mysqli_result($query,0,"r.nom_recurs");
	$f_id_responsable=mysqli_result($query,0,"r.id_responsable");
	$f_nom_responsable=mysqli_result($query,0,"u.nom_usuari");
	$f_id_aula=mysqli_result($query,0,"a.aula");
	
	// Ara llegim les diferents reserves que hi ha d'aquell recurs per aquella hora
	$consulta="SELECT *
				FROM tbl_reserves
				WHERE data_reserva=\"$any/$mes/$dia\" AND id_recurs=$recurs AND num_dia=0
				ORDER BY hora_inici";

	$query=@mysqli_query($conn,$consulta);
	if($query==FALSE):
		$nFiles=0;
	else:
		$nFiles=mysqli_num_rows($query);
	endif;

	$femtemps=getdate(mktime(0,0,0,$mes,$dia,$any));
	$numero_dia=monsun($femtemps[wday])+1;

	// Consulta per agafar els PERIODICS no optimitzada
	$consulta_periodics="SELECT *
							FROM tbl_reserves
							WHERE (data_reserva<=\"$any/$mes/$dia\" AND data_final>=\"$any/$mes/$dia\") AND
									id_recurs=$recurs AND
									num_dia=$numero_dia
							ORDER BY num_dia, data_reserva, hora_inici";

	$query_periodics=@mysqli_query($conn,$consulta_periodics);
	$nFiles_periodics=mysqli_num_rows($query_periodics);
	$id_reserva=-1;
	$j=0;

	//for($i=$hora_minima;$i<$hora_maxima;$i++):
	for($i=00;$i<24;$i++):
		//Amb aquest for posem les mitjes hores.
		for($m=1;$m<3;$m++):
			$min_aux=($m-1)*3;
			$min=$min_aux."0";
			
			$k=0;
			$activities_tmp="";
			if($k<$nFiles_periodics):
				$numero_dia_bd=mysqli_result($query_periodics, $k, "num_dia");
			endif;
	
			while($k<$nFiles_periodics):
				if((intval(substr(mysqli_result($query_periodics, $k, "hora_inici"),0,2))<=$i)&&(intval(substr(mysqli_result($query_periodics, $k, "hora_final"),0,2))>$i)):
					$activities_tmp=mysqli_result($query_periodics, $k, "motiu_reserva");
					$f_qui_reserva=mysqli_result($query_periodics, $k, "qui_reserva");
					//$activities_tmp=sprintf("%s [P]%s-%s<br><i>%s</i><br>",$activities_tmp,substr(mysqli_result($query_periodics, $k, "hora_inici"),0,5),substr(mysqli_result($query_periodics, $k, "hora_final"),0,5),substr(mysqli_result($query_periodics, $k, "qui_reserva"),0,12));
					$tpl->assign("WHO", mysqli_result($query_periodics, $k, "qui_reserva"));
					$tpl->assign("ASSIG", mysqli_result($query_periodics, $k, "aula"));
					$id_reserva=mysqli_result($query_periodics,$k,"id_reserva");
	//**				$f_op=73;
					$str_funcions="";
				endif;
				$k++;
				if($k<$nFiles_periodics):
					$numero_dia_bd=mysqli_result($query_periodics, $k, "num_dia");
				endif;
			endwhile;
	
			// Comprovem si en aquella hora hi ha alguna reserva a la base de dades
	
			if (($nFiles>$j) && (intval(substr(mysqli_result($query, $j, "hora_inici"),0,2))<=$i)):
				$activities_tmp=mysqli_result($query, $j, "motiu_reserva");
				$id_reserva=mysqli_result($query,$j, "id_reserva");
				//$tpl->assign(ACTIVITY,)
				$f_qui_reserva=mysqli_result($query, $j, "qui_reserva");
				$tpl->assign("WHO", mysqli_result($query, $j, "qui_reserva"));
				$tpl->assign("ASSIG", mysqli_result($query, $j, "aula"));
	//**			$f_op=74;
				$str_funcions="";
				//$tpl->assign(ADD,"&nbsp");
				if(intval(substr(mysqli_result($query, $j, "hora_final"),0,2))==$i+1) $j++;
			endif;
	
			if(strcmp($activities_tmp,"")==0):
				$activities_tmp="&nbsp";
				$tpl->assign("WHO", "&nbsp");
				$tpl->assign("ASSIG", "&nbsp");
				$f_tmp=E_ADD;
				$k_hora_inici="$i:$min";
				$str_funcions="<a  href=\"javascript:finestraTotal_vaixell('$PHP_SELF?hora_inici=$k_hora_inici&op=52&recurs=$recurs&dia=$dia&mes=$mes&any=$any')\" title=\"$f_tmp\"><img align='middle' src='./img/reservar.gif' alt='Reservar' border='0'></a>";
				//reservation($dia,$mes,$any,$recurs,$hora_inici)
			else:
				$consulta="SELECT *
					FROM tbl_usuaris
					WHERE nom_usuari='$f_qui_reserva'";
				$query=@mysqli_query($conn,$consulta);
				$f_uid_reserva=mysqli_result($query,0,"uid");
				$activities_tmp="<a href=\"javascript:openpopup('$PHP_SELF?id_reserva=$id_reserva&op=81')\">$activities_tmp</a>";
				if(($f_nom_responsable==$_SESSION['nom_usuari'])||($_SESSION['perfil']=="CCESAII")||($f_uid_reserva==$f_uid_sessio)):
					$f_tmp=E_DELETE;
	//**				$str_funcions="<a href=\"$PHP_SELF?hora_inici=$i&op=$f_op&recurs=$recurs&dia=$dia&mes=$mes&any=$any&id_reserva=$id_reserva\" title=\"$f_tmp\"><img align='middle' src='./img/anular.gif' alt='Anul&middot;lar' border='0'></a>";
					$str_funcions="<a href=\"$PHP_SELF?op=73&recurs=$recurs&dia=$dia&mes=$mes&any=$any&id_reserva=$id_reserva\" title=\"$f_tmp\"><img align='middle' src='./img/anular.gif' alt='Anul&middot;lar' border='0'></a>";
				endif;
			endif;
			$tpl->assign("ADD",$str_funcions);
			$tpl->assign("ACTIVITY", $activities_tmp);
			$iPlus=$i+1;
			if($m==1):
				$tpl->assign("TIME", "$i:00-$i:30");
			else:
				$tpl->assign("TIME", "$i:30-$iPlus:00");
			endif;
			$tpl->parse(TABLE_ROWS, ".table_row");
		endfor;
	endfor;

	if ($dia<10) $dia="0$dia";
	if ($mes<10) $mes="0$mes";
	$tpl->assign("DIA", "$dia/$mes/$any");

	//localitzar la web
	$tpl->assign("DESC_RECURS", $f_desc_recurs);

	$tpl->assign(array(
		"E_HORA" => E_HORA,
		"E_ACTIVITAT" => E_ACTIVITAT,
		"E_QUI" => E_QUI,
		"E_ASSIG"=> E_ASSIG,
		"E_FUNCIONS" => E_FUNCIONS,
		"E_SUG_RESERVA" => E_SUG_RESERVA
	));

	$tpl->parse(PAGE_CONTENT,"table");
}

//-----------------------------------------------------------------------------------------------------------
// Procediment: MostrarInfoReserva()
// Operativa  : Arriben els paramentres d'una reserva i cal comprovar que sigui valida per al vaixell
//-----------------------------------------------------------------------------------------------------------
function MostrarInfoReserva_Vaixell($id_reserva){
	global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;
	
	$resultat=0;

	// Connectem amb el servidor i seleccionem la BD corresponent
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
	@mysqli_query($conn,"SET NAMES 'utf8'");
	//Comprovem que realment pot eliminar aquesta reserva

	//$consulta="SELECT * FROM tbl_reserves WHERE id_responsable=\"$_SESSION['r_id_usuari']\"
	//	AND id_recurs=\"$recurs\"";

	$consulta="SELECT r.nom_recurs AS recurs,
							re.id_recurs AS id_recurs,
							re.data_reserva AS data_inici,
							re.data_final AS data_final,
							re.num_dia AS dia,
							u.nom_usuari AS respon,
							re.qui_reserva AS qui,
							re.motiu_reserva AS motiu,
							re.hora_inici AS hora_inici,
							re.hora_final AS hora_final,
							u.email AS email,
							re.aula AS aula,
							re.assig AS assig,
							re.maq_eines AS maq_eines,
							re.desc_consum AS desc_consum,
							re.desc_activitat AS desc_activitat
					FROM  tbl_recursos r,
							tbl_reserves re,
							tbl_usuaris u
					WHERE r.id_recurs=re.id_recurs AND
							r.id_responsable=u.id_usuari AND
							re.id_reserva=$id_reserva";

	$query=@mysqli_query($conn,$consulta);
	
	// Si la consulta retorna resultats...
	if($query!=NULL):
		$recurs_vaixell = mysqli_result($query,0,"id_recurs");
		$data_reserva_vaixell = mysqli_result($query,0,"data_inici");
		$data_final_vaixell = mysqli_result($query,0,"data_final");
		$consulta_vaixell = "SELECT *
					FROM  tbl_reserves
					WHERE  id_recurs = '$recurs_vaixell' AND
							data_reserva = '$data_reserva_vaixell' AND
							data_final = '$data_final_vaixell'";
		$query_vaixell = @mysqli_query($conn,$consulta_vaixell);
		$num_rows_vaixell  = mysqli_num_rows($query_vaixell);
		//$hora_inici = mysqli_result($query,0,"hora_inici");
		//print $num_rows_vaixell;
		//$hora_final = mysqli_result($query,$num_rows_vaixell-1,"hora_final");
		
		if ($recurs_vaixell=='16'):
			$tpl->define(array(
				page    => "$template_dir/minipage.tpl",
				reserva => "$template_dir/info_reserva_vaixell.tpl"
			));
		elseif ($recurs_vaixell=='7' || $recurs_vaixell=='9'):
			$tpl->define(array(
				page    => "$template_dir/minipage.tpl",
				reserva => "$template_dir/info_reserva_vaixell_minina.tpl"
			));
		endif;
		
		$tpl->assign(array(
			"E_MSG_INF_RESERVA"    => E_MSG_INF_RESERVA,
			"E_DATA_INICI"         => E_DATA_INICI,
			"E_DATA_FINAL"         => E_DATA_FINAL,
			"E_HORA_INICI"         => E_HORA_INICI,
			"E_HORA_FINAL"         => E_HORA_FINAL,
			"E_ACTIVITAT"          => E_ACTIVITAT,
			"E_QUI"                => E_QUI,
			"E_MOTIU"              => E_MOTIU,
			"E_RESPONSABLE_RECURS" => E_RESPONSABLE_RECURS,
			"E_PATRO"			   => E_PATRO,
			"E_DNI_PATRO"		   => E_DNI_PATRO,
			"E_TITULACIO_PATRO"	   => E_TITULACIO_PATRO,
			"DESC_RECURS"          => mysqli_result($query,0,"recurs")
		));

		switch(mysqli_result($query,0,"dia")):
			case 0: $dia=E_DIA0; break;
			case 1: $dia=E_DIA1; break;
			case 2: $dia=E_DIA2; break;
			case 3: $dia=E_DIA3; break;
			case 4: $dia=E_DIA4; break;
			case 5: $dia=E_DIA5; break;
			case 6: $dia=E_DIA6; break;
			case 7: $dia=E_DIA7; break;
		endswitch;
		$data_ini=strtotime(mysqli_result($query,0,"data_inici"));
		$data_fi=strtotime(mysqli_result($query,0,"data_final"));

		$tpl->assign(array(
			"DATA_RESERVA" => date("d/m/Y",$data_ini),
			"DATA_FINAL" => date("d/m/Y", $data_fi),
			"HORA_INICI" => mysqli_result($query_vaixell,$num_rows_vaixell-1,"hora_inici"),
			"HORA_FINAL" => mysqli_result($query_vaixell,0,"hora_final"),
			"QUI" => mysqli_result($query,0,"qui"),
			"EMAIL" => mysqli_result($query,0,"email"),
			"RESPONSABLE" => mysqli_result($query,0,"respon"),
			"MOTIU" => mysqli_result($query,0,"motiu"),
			"ALTRES" => mysqli_result($query,0,"desc_activitat"),
			"ASSIG" => mysqli_result($query,0,"assig"),
			"DNI_PATRO" => mysqli_result($query,0,"maq_eines"),
			"TITULACIO_PATRO" => mysqli_result($query,0,"desc_consum")
		));
	endif;
	$tpl->parse(PAGE_CONTENT,"reserva");
}


//-----------------------------------------------------------------------------------------------------------
// Procediment: FerReservaPuntual_Vaixell()
// Operativa  : Permet fer una reserva puntual d'un recurs donat
//-----------------------------------------------------------------------------------------------------------
function FerReservaPuntual_Vaixell($dia,$mes,$any,$recurs,$hora_inici){
	global $tpl, $hora_maxima, $hora_minima, $hora_maxima_vaixell, $hora_minima_vaixell, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/reserva_vaixell.tpl"
	));
	if($recurs=='16') :
		$tpl->assign(array(
					"E_DNI_PATRO"	=>	E_DNI_PATRO,
					"E_EMBARCATS"	=>	E_EMBARCATS,
					"E_TITULACIO_PATRO"		=>	E_TITULACIO_PATRO
		));
	elseif($recurs=='7' || $recurs=='9') :
		$tpl->assign(array(
					"E_DNI_PATRO"	=>	E_DNI_PATRO,
					"E_EMBARCATS"	=>	E_EMBARCATS_MININA,
					"E_TITULACIO_PATRO"		=>	E_TITULACIO_PATRO
		));
	endif;
	// Connectem amb el servidor i seleccionem la BD corresponent
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);

	$consulta="SELECT * FROM tbl_recursos WHERE id_recurs=$recurs";

	$query=@mysqli_query($conn,$consulta);
	$f_desc_recurs=mysqli_result($query,0,"nom_recurs");
	$f_edifici = mysqli_result($query,0,"id_edifici");
	$tpl->assign("DESC_RECURS",$f_desc_recurs);


	$f_tmp="$dia/$mes/$any";
	$tpl->assign(array(
		"DATA"         => $f_tmp,
		"E_DATA"       => E_DATA,
		"E_DATA_INICI"  =>  E_DATA_INICI,
		"E_DATA_FINAL"  =>  E_DATA_FINAL,
		"E_RECURS"     => E_RECURS,
		"USERNAME"     => $_SESSION['nom_usuari'],
		"E_CHECKLIST"  => E_CHECKLIST,
		"E_QUI"        => E_QUI,
		"E_HORA_INICI" => E_HORA_INICI,
		"E_HORA_FINAL" => E_HORA_FINAL,
		"E_RESERVA"    => E_RESERVA,
		"E_PATRO"      => E_PATRO,
		"E_DNI_PATRO"  => E_DNI_PATRO,
		"E_TITULACIO_PATRO"  => E_TITULACIO_PATRO,
		"E_TITOL"      => E_TITOL,
		"E_ASSIG"      => E_ASSIG,
		"E_RELACIO_EMBARCATS"  => E_RELACIO_EMBARCATS,
		"E_NOM_EMBARCATS"  => E_NOM_EMBARCATS,
		"E_DNI_EMBARCATS"  => E_DNI_EMBARCATS,
		"E_MOTIU"      => E_MOTIU,
		"E_ALTRES"     => E_ALTRES
	));
	$f_tmp=franges_horaries($hora_minima_vaixell, $hora_maxima_vaixell, $hora_inici, "hora_inici");
	$tpl->assign("FRANGES_HORARIES_INICI",$f_tmp);

	$k_hora_final=explode(":",$hora_inici);
	$hora_final=$k_hora_final[0]+1;
	$hora_final=$hora_final.":00";
	$f_tmp=franges_horaries($hora_minima_vaixell, $hora_maxima_vaixell+1, $hora_final, "hora_final");
	$tpl->assign(array(
		"FRANGES_HORARIES_FINAL" => $f_tmp,
		"HORA_INICI"             => "$hora_inici"
	));
	
	$tpl->assign(array(
		"HORA_FINAL" => "$hora_final",
		"RECURS" => $recurs,
		"DIA" => $dia,
		"MES" => $mes,
		"ANY" => $any
	));
	
	// Connectem amb la BBDD per pintar les aules disponibles
	//$f_lloc=select_place2($dbserver, $dbname, $dbuser, $dbpass, $f_edifici, "lloc");
	//$tpl->assign("PLACE",$f_lloc);

	// Connectem amb la BBDD per pintar les persones disponibles, en cas que pertanyin al grup
	// CCFNB prodran reservar el projector pels altres usuaris
	$nom_usuari=$_SESSION['nom_usuari'];
	$consulta="SELECT * FROM tbl_usuaris WHERE nom_usuari='$nom_usuari'";

	$query=@mysqli_query($conn,$consulta);
	$f_perfil=mysqli_result($query,0,"perfil");
	if($f_perfil=='CCESAII' OR $f_perfil=='Usuari PDI VAIXELL') {
		$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/reserva_admin_vaixell.tpl"
		));
		$f_persona=select_who($dbserver, $dbname, $dbuser, $dbpass, "qui");
		$tpl->assign("USUARI",$f_persona);
	}
	
	$tpl->parse(PAGE_CONTENT, "reserva");
}


//-----------------------------------------------------------------------------------------------------------
// Procediment: ValidarReserva_Vaixell()
// Operativa  : Arriben els paramentres d'una reserva i cal comprovar que sigui valida
//-----------------------------------------------------------------------------------------------------------
function ValidarReserva_Vaixell($dia,$mes,$any,$data_final,$recurs,$qui,$motiu,$hora_inici,$hora_final,$patro,$dni_patro,$titol,$titulacio_patro,$embarcats,$motiu,$altres){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir, $enviar_correu, $url_reserves;

	if ($recurs==7 || $recurs==9):
		$tpl->define(array(
			page    => "$template_dir/minipage.tpl",
			reserva => "$template_dir/aceptar_reserva_vaixell_minina.tpl"
		));
	elseif ($recurs==16):
		$tpl->define(array(
			page    => "$template_dir/minipage.tpl",
			reserva => "$template_dir/aceptar_reserva_vaixell.tpl"
		));
	endif;
	$resultat=0;
	
	//Desglosem les hores i els minuts.
	$pos_hora_inici=stripos($hora_inici,':');
	$pos_hora_final=stripos($hora_final,':');
	$min_inici=substr($hora_inici,$pos_hora_inici+1);
	$hora_inici=substr($hora_inici,0,$pos_hora_inici);
	$min_final=substr($hora_final,$pos_hora_final+1);
	$hora_final=substr($hora_final,0,$pos_hora_final);

	//Agreguem 48 hores a la data que ens indiquen
	$hores_antelacio=1;
	//data de la reserva en format UNIX per fer la comparaci�
	$data_reserva=mktime($hora_inici,0,0,$mes,$dia,$any);
	//Data actual + 48 hores
	$add_48=mktime(date('H')+$hores_antelacio,date('i'),date('s'),date('m'),date('d'),date('Y'));

	if(!checkdate($mes,$dia,$any)):
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_DATA_NO_VAL);

	elseif(time()>mktime($hora_inici,$min_inici,0,$mes,$dia,$any)):
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_ARA_NO_VAL);
	elseif(($titol==0) || ($embarcats==0)):
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_CONDICIONS_NO_ACCEPTADES);
	elseif(($patro=="") || ($motiu=="0")):
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_MOTIU_NO_VAL);
	elseif ("$add_48">"$data_reserva"):
		// No han omplert el camp QUI o MOTIU
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_DATA_48_VAL);
	else:
		$a_date=dateToArray($data_final);
       	$mes1=$a_date[1];
       	$dia1=$a_date[0];
       	$any1=$a_date[2];
		if(mktime($hora_inici,$min_inici,0,$mes,$dia,$any)>=mktime($hora_final,$min_final,0,$mes,$dia,$any)):
			$resultat=1;
			$tpl->assign("E_MOTIUS", E_ERR_HORA_VAIXELL_NO_VAL);

		else:
			$f_hora_inici="$hora_inici:$min_inici";
			$f_hora_final="$hora_final:$min_final";
			$f_data="$any/$mes/$dia";
			$f_data_reserva="$dia/$mes/$any";
			$a_date=dateToArray($data_final);
        	$mes1=$a_date[1];
        	$dia1=$a_date[0];
        	$any1=$a_date[2];
			$f_data_final=sprintf("%s/%s/%s",$any1,$mes1,$dia1);
			//En cas d'especificar ALTRES en la selecci� de la reserva del Vaixell, em de inserir l'esmentada explicaci�, en lloc del motiu
			//ja tipificat
			if ($motiu==5):
				$motiu=$altres;
			endif;
			
			//Calculem els dies que fem reserva del vaixell
			$dies_diferencia = Dies_Reserva($f_data_reserva,$data_final);
			
			// Connectem amb el servidor i seleccionem la BD corresponent
			$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
			@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
			@mysqli_query($conn,"SET NAMES 'utf8'");
			$consulta_aula="SELECT *
								FROM tbl_recursos
								WHERE id_recurs=$recurs";
			$query_aula = @mysqli_query($conn,$consulta_aula);
			$aula = mysqli_result($query_aula,0,"nom_recurs");
			$f_tipus_reserva = mysqli_result($query_aula,0,"id_tipus");
			
			// Ara llegim les diferents reserves que hi ha d'aquell recurs per aquella hora
			$i = 0;
			$data=$f_data;
			while ($i <= $dies_diferencia):
			$consulta="SELECT id_recurs, qui_reserva, motiu_reserva, data_reserva,
									data_final, hora_inici,  hora_final
							FROM tbl_reserves
							WHERE data_reserva=\"$data\" AND id_recurs=$recurs AND
									(((hora_inici<=\"$f_hora_inici\") AND (hora_final>\"$f_hora_inici\")) OR
										((hora_final>=\"$f_hora_final\") AND (\"$f_hora_final\">hora_inici)) OR
										((hora_inici<=\"$f_hora_inici\")AND(\"$f_hora_final\"<=hora_final)) OR
										((hora_inici>=\"$f_hora_inici\")AND(hora_final<=\"$f_hora_final\"))) AND
									num_dia=0
							ORDER BY hora_inici";
							
			$query=@mysqli_query($conn,$consulta);
			list($ano_query,$mes_query,$dia_query)=explode("/",$data); 
			$nueva = mktime(0,0,0, $mes_query,$dia_query,$ano_query) + 24 * 60 * 60; 
			$data=date("Y/m/d",$nueva);
			$i++;
			//Si qualsevol dels dies que volem reservar est� ocupat ho detectem i no fem m�s comprovacions
			if(mysqli_num_rows($query)>0): $i = $dies_diferencia + 1; endif;
			endwhile;

			if(mysqli_num_rows($query)>0):
				$resultat=2;
				$tpl->assign("E_MSG_RESERVA_VAIXELL", E_ERR_HI_HA_RESERVA);
				$taula=mysqli_result_table($query);
				$f_tmp=E_ERR_HI_HA_RESERVA."<br><br>$taula";
				$tpl->assign("E_MOTIUS",$f_tmp);
			else:
				$femtemps=getdate(mktime(0,0,0,$mes,$dia,$any));
				$numero_dia=monsun($femtemps[wday])+1;
				//Inicialitzem variables per la comprovaci� de que la reserva no es solapa amb un altre
				$i = 0;
				$numero_dia_0 = $numero_dia;
				$data=$f_data;
				while ($i <= $dies_diferencia):
				if($data==$f_data && $dies_diferencia==0):
					$hora_final_0=$f_hora_final;
					$hora_inici_0=$f_hora_inici;
				elseif($data==$f_data && $dies_diferencia>0):
					$hora_final_0="24:00:00";
					$hora_inici_0=$f_hora_inici;
				elseif($data!=$f_data && $data!=$f_data_final):
					$hora_final_0="24:00:00";
					$hora_inici_0="00:00:00";
				elseif($data==$f_data_final && $dies_diferencia>0):
					$hora_final_0=$f_hora_final;
					$hora_inici_0="00:00:00";
				endif;
				$consulta_periodics="SELECT id_recurs, qui_reserva, motiu_reserva,
													data_reserva, data_final, hora_inici, hora_final
											FROM tbl_reserves
											WHERE ((data_reserva<=\"$data\") AND (data_final>=\"$data\")) AND
													(((hora_inici<=\"$hora_inici_0\") AND (hora_final>\"$hora_inici_0\")) OR
														((hora_final>=\"$hora_final_0\") AND (\"$hora_final_0\">hora_inici)) OR
														((hora_inici<=\"$hora_inici_0\") AND (\"$hora_final_0\"<=hora_final)) OR
														((hora_inici>=\"$hora_inici_0\") AND (hora_final<=\"$hora_final_0\"))) AND
													num_dia=$numero_dia_0 AND id_recurs=$recurs
											ORDER BY hora_inici";

				$query_periodics=@mysqli_query($conn,$consulta_periodics);
				list($ano_query_periodics_0,$mes_query_periodics_0,$dia_query_periodics_0)=explode("/",$data); 
				$nueva = mktime(0,0,0, $mes_query_periodics_0,$dia_query_periodics_0,$ano_query_periodics_0) + 24 * 60 * 60; 
				$data=date("Y/m/d",$nueva);
				$numero_dia_0++;
				$i++;
				//Si qualsevol dels dies que volem reservar est� ocupat ho detectem
				if(mysqli_num_rows($query_periodics)>0): $i = $dies_diferencia + 1; endif;
				endwhile;
				if(mysqli_num_rows($query_periodics)>0):
					print "numdia_periodic";print $data;print $f_data;print $f_data_final;
					$resultat=3;
					$taula=mysqli_result_table($query_periodics);
					$f_tmp=E_ERR_HI_HA_RESERVA."<br><br>$taula";
					$tpl->assign("E_MSG_RESERVA_VAIXELL", E_ERR_HI_HA_RESERVA);
					$tpl->assign("E_MOTIUS",$f_tmp);
				else:
					// Ara ja podem inserir i recollir el resultat
					//($dia,$mes,$any,$recurs,$qui,$motiu,$hora_inici,$hora_final){
					if($_SESSION['perfil']=='CCESAII' OR $_SESSION['perfil']=='Usuari PDI VAIXELL'):
						$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
						@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
						$i = 0;
						$data=$f_data;
						$numero_dia_0=$numero_dia;
						while ($i <= $dies_diferencia):
							if($data==$f_data && $dies_diferencia==0):
								$hora_final_0=$f_hora_final;
								$hora_inici_0=$f_hora_inici;
							elseif($data==$f_data && $dies_diferencia>0):
								$hora_final_0="24:00:00";
								$hora_inici_0=$f_hora_inici;
							elseif($data!=$f_data && $data!=$f_data_final):
								$hora_final_0="24:00:00";
								$hora_inici_0="00:00:00";
							elseif($data==$f_data_final && $dies_diferencia>0):
								$hora_final_0=$f_hora_final;
								$hora_inici_0="00:00:00";
							endif;
							//ereg( "([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4})", $data, $data_0); 
							//$data_mysql=$data_0[3]."-".$data_0[2]."-".$data_0[1];
							$consulta_insert="INSERT INTO tbl_reserves(id_recurs,qui_reserva,motiu_reserva,assig,maq_eines,aula,desc_consum,
								data_reserva,data_final,hora_inici,hora_final,num_dia,tipus_reserva,desc_activitat,assistents,consum)
								VALUES (\"$recurs\",'".utf8_decode(addslashes($qui))."','".utf8_decode(addslashes($motiu))."','".utf8_decode(addslashes($patro))."',\"$dni_patro\",'".utf8_decode(addslashes($aula))."','".utf8_decode(addslashes($titulacio_patro))."',\"$data\",\"$data\",\"$hora_inici_0\",
								\"$hora_final_0\",\"$numero_dia_0\",\"$f_tipus_reserva\",'".utf8_decode(addslashes($altres))."','".utf8_decode(addslashes($embarcats))."','".utf8_decode(addslashes($titol))."')";
							$query_periodics=@mysqli_query($conn,$consulta_insert);
							if (mysqli_affected_rows($conn) == 1) :
								$tpl->assign("E_MOTIUS","");
								$last_id=mysqli_insert_id();
							else:
								$resultat=4;
								$tpl->assign("E_MOTIUS", E_ERR_INSERT_DB);
							endif;
							list($ano_query_periodics,$mes_query_periodics,$dia_query_periodics)=explode("/",$data); 
							$nueva = mktime(0,0,0, $mes_query_periodics,$dia_query_periodics,$ano_query_periodics) + 24 * 60 * 60; 
							$data=date("Y/m/d",$nueva);
							$numero_dia_0++;
							if($numero_dia_0>7):$numero_dia_0=1;endif;
							$i++;
						endwhile;
					else:
						EnviarCorreuPeticioReserva_Vaixell($dia,$mes,$any,$data_final,$recurs,$qui,$motiu,$f_hora_inici,$f_hora_final,$patro,$dni_patro,$titol,$titulacio_patro,$embarcats,$motiu,$altres,$f_tipus_reserva);
						$resultat=9;
					endif;
				endif;
			endif;
		endif;
	endif;

	if($resultat==0):
		// Sha fet la reserva sense problemes.
		// Ho notifiquem per pantalla i, si procedeix, enviem el mail de confirmacio...
		$tpl->assign("E_MSG_RESERVA", E_MSG_RESERVA_RSLT);
		$tpl->assign("E_MSG_RESERVA_VAIXELL", E_MSG_RESERVA_VAIXELL);
		$tpl->assign("E_DATA_RESERVA", $f_data_reserva);
		$tpl->assign("E_DATA_FINAL", $data_final);
		$tpl->assign("E_RECURS", $recurs);
		$tpl->assign("E_QUI", $qui);
		$tpl->assign("E_MOTIU", $motiu);
		$tpl->assign("E_HORA_INICI", $hora_inici);
		$tpl->assign("E_HORA_FINAL", $hora_final);
		$tpl->assign("E_PATRO", $patro);
		$tpl->assign("E_DNI_PATRO", $dni_patro);
		$tpl->assign("E_TITOL", $titol);
		$tpl->assign("E_TITULACIO_PATRO", $titulacio_patro);
		$tpl->assign("E_EMBARCATS", $embarcats);
		$tpl->assign("E_ALTRES", $altres);
		
		$enviar_correu=1;
		if($enviar_correu):
			$consulta_email="SELECT re.avisar_resp as avisar,
											u.email as email, u.email_fnb as email_fnb,
											re.nom_recurs as recurs
									FROM tbl_recursos re,
											tbl_usuaris u
									WHERE re.id_responsable=u.id_usuari AND
											re.id_recurs='$recurs'";

			$query_email=@mysqli_query($conn,$consulta_email);
			if (mysqli_result($query_email,0,"avisar")=="1"):
				//Si cal avisar de les reserves del recurs, enviem email al responsable
				$from="No respondre <centre.calcul@fnb.upc.edu>";
				$to=mysqli_result($query_email,0,"email_fnb");
				$cc="<jmateu@cen.upc.edu>";
				$bcc="<centre.calcul@fnb.upc.edu>";
				$subject="Reservator v.1.1: Nova reserva de \"".mysqli_result($query_email,0,"recurs")."\"";
				$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Si us plau, no el contesteu.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
				$body.="S'ha fet una nova reserva de \"".mysqli_result($query_email,0,"recurs")."\".<br><br><b>Usuari</b>: $qui.<br><b>Activitat/Assignatura</b>: $altres.<br><b>Motiu</b>: $motiu.<br><b>Dia</b>: $dia/$mes/$any.<br><b>Horari</b>: de $f_hora_inici a $f_hora_final.<br><b>Patr&oacute;</b>: $patro.<br><b>Titulaci&oacute; Patr&oacute;</b>: $titulacio_patro.<br><b>DNI Patr&oacute;</b>: $dni_patro.";
				$body.="<p>Clicar <a href='$url_reserves?any=$any&mes=$mes&recurs=$recurs&op=31'>aqui</a> per veure la reserva.</p>";
				$body.="<p>Clicar <a href='$url_reserves?id_reserva=$last_id&op=81'>aqui</a> per veure el document PDF a omplir i lliurar degudament complimentat a l'Administraci&oacute; del Centre.</p>";
				$signature="Centre de C&agrave;lcul FNB</font>";

				EnviarEmail($from, $to, $cc, $cc1, $bcc, $subject, $body, $signature, $err_msg);
				//mail($f_mail,,"L'usuari $qui ha fet una reserva de $f_recurs pel motiu: $motiu","From: reservator@$SERVER_NAME");
			endif;
		endif;


	else:
		if($resultat==9):
			if($f_tipus_reserva==5):
				$tpl->assign("E_MSG_RESERVA", E_MSG_PETICIO_RESERVA_RSLT_VAIXELL);
			endif;
			$tpl->assign("E_MOTIUS","");
			$tpl->assign("E_MSG_RESERVA_VAIXELL", "");
		else:
			$tpl->assign("E_MSG_RESERVA", E_MSG_NO_RESERVA_RSLT);
		endif;
	endif;

	$tpl->parse(PAGE_CONTENT,"reserva");
	
}

//-----------------------------------------------------------------------------------------------------------
// Procediment: EnviarCorreuPeticioReserva_Vaixell();
// Operativa  : Enviem un correu de petici� de reserva a la Secretaria de Direcci� i al usuari.
//-----------------------------------------------------------------------------------------------------------
function EnviarCorreuPeticioReserva_Vaixell($dia,$mes,$any,$data_final,$recurs,$qui,$motiu,$hora_inici,$hora_final,$patro,$dni_patro,$titol,$titulacio_patro,$embarcats,$motiu,$altres,$f_tipus_reserva){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir, $enviar_correu, $url_reserves;
	
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
	$consulta_email="SELECT email,email_fnb,nom_usuari
							FROM tbl_usuaris
							WHERE nom_usuari='$qui'";
	
	$query_email=@mysqli_query($conn,$consulta_email);
	//Si cal avisar de les reserves del recurs, enviem email al responsable
	$from="Reserva Vaixell <".mysqli_result($query_email,0,"email").">";
	$patro = stripslashes($patro);
	$motiu = stripslashes($motiu);
	if ($f_tipus_reserva==5):
		$to="jmateu@cen.upc.edu";
		$cc="<".mysqli_result($query_email,0,"email_fnb").">";
		$bcc="centre.calcul@fnb.upc.edu";
		$subject="".html_entity_decode('Petici&oacute;', ENT_NOQUOTES, 'UTF-8')." de reserva del Vaixell BARCELONA per part de \"".html_entity_decode(mysqli_result($query_email,0,"nom_usuari"))."\"";
		//$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Aviat rebreu confirmaci&oacute; de la vostra petici&oacute;.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
		$body="<b>Missatge generat autom&agrave;ticament. Aviat rebreu confirmaci&oacute; de la vostra petici&oacute;.</b><br>";
		$body.="S'ha fet una petici&oacute; de reserva de \"".mysqli_result($query_email,0,"nom_usuari")."\".<br><br><b>Usuari</b>: $qui.<br><b>Activitat/Assignatura</b>: $altres.<br><b>Motiu</b>: $motiu.<br><b>Dia</b>: $dia/$mes/$any.<br><b>Horari</b>: de $hora_inici a $hora_final.<br><b>Patr&oacute;</b>: $patro.<br><b>Titulaci&oacute; Patr&oacute;</b>: $titulacio_patro.<br><b>DNI Patr&oacute;</b>: $dni_patro.";
		$body.="<p>Clicar <a href=\"$url_reserves?dia=$dia&mes=$mes&any=$any&recurs=$recurs&qui=$qui&patro=$patro&motiu=$motiu&hora_inici=$hora_inici&hora_final=$hora_final&dni_patro=$dni_patro&titol=$titol&titulacio_patro=$titulacio_patro&embarcats=$embarcats&altres=$altres&f_tipus_reserva=$f_tipus_reserva&op=62\">aqui</a> per validar la reserva.</p>";
		$signature="Centre de C&agrave;lcul de la FNB</font>";
	else:
		$to="centre.calcul@fnb.upc.edu";
		//$cc="cap.serveis@fnb.upc.edu";
		//$cc1="<".mysqli_result($query_email,0,"email_fnb").">";
		//$bcc="centre.calcul@fnb.upc.edu";
		$subject="".html_entity_decode('Petici&oacute;', ENT_NOQUOTES, 'UTF-8')." de reserva dels Espais Docents de \"".html_entity_decode(mysqli_result($query_email,0,"nom_usuari"))."\"";
		//$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Aviat rebreu confirmaci&oacute; de la vostra petici&oacute;.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
		$body="<b>Missatge generat autom&agrave;ticament. Aviat rebreu confirmaci&oacute; de la vostra petici&oacute;.</b><br>";
		$body.="S'ha fet una petici&oacute; de reserva de \"".mysqli_result($query_email,0,"nom_usuari")."\".<br><br><b>Usuari</b>: $qui.<br><b>Activitat/Assignatura</b>: $assig.<br><b>Motiu</b>: $motiu.<br><b>Dia</b>: $dia/$mes/$any.<br><b>Horari</b>: de $hora_inici a $hora_final.<br><b>Lloc</b>: $aula.<br><b>Projector</b>: $project.";
		$body.="<p>Clicar <a href=\"$url_reserves?dia=$dia&mes=$mes&any=$any&recurs=$recurs&qui=$qui&assig=$assig&motiu=$motiu&hora_inici=$hora_inici&hora_final=$hora_final&aula=$aula&uid=0&projector=$projector&f_tipus_reserva=$f_tipus_reserva&op=62\">aqui</a> per validar la reserva.</p>";
		$signature="Centre de C&agrave;lcul de la FNB</font>";
	endif;
	EnviarEmail($from, $to, $cc, $cc1, $bcc, $subject, $body, $signature, $err_msg);
	//mail($f_mail,,"L'usuari $qui ha fet una reserva de $f_recurs pel motiu: $motiu","From: reservator@$SERVER_NAME");
}

//-----------------------------------------------------------------------------------------------------------
// Procediment: EnviarCorreuDenegacioReserva_Vaixell();
// Operativa  : Enviem un correu de petici� de reserva a la Secretaria de Direcci� i al usuari.
//-----------------------------------------------------------------------------------------------------------
function EnviarCorreuDenegacioReserva_Vaixell($dia,$mes,$any,$data_final,$recurs,$qui,$motiu,$hora_inici,$hora_final,$patro,$dni_patro,$titol,$titulacio_patro,$embarcats,$motiu,$altres,$f_tipus_reserva,$motius){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir, $enviar_correu, $url_reserves;
	
	switch($projector){
		case 0: $project = NO;
				break;
		case 1: $project = SI;
		}
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
	$consulta_email="SELECT email,email_fnb,nom_usuari
							FROM tbl_usuaris
							WHERE nom_usuari='$qui'";
	
	$query_email=@mysqli_query($conn,$consulta_email);
	//Si cal avisar de les reserves del recurs, enviem email al responsable
	$from="Reserva Vaixell <".mysqli_result($query_email,0,"email").">";
	$patro = stripslashes($patro);
	$motiu = stripslashes($motiu);
	if ($f_tipus_reserva==5):
		//$cc="cap.serveis@fnb.upc.edu";
		$to="jmateu@fnb.upc.edu";
		$cc="<".mysqli_result($query_email,0,"email_fnb").">";
		$bcc="centre.calcul@fnb.upc.edu";
		$subject="Descartada la ".html_entity_decode('petici&oacute;dica', ENT_NOQUOTES, 'UTF-8')." de reserva de la sala de Juntes o Actes de \"".html_entity_decode(mysqli_result($query_email,0,"nom_usuari"))."\"";
		//$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Aviat rebreu confirmaci&oacute; de la vostra petici&oacute;.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
		$body="<b>Missatge generat autom&agrave;ticament.</b><br>";
		$body.="S'ha descartat la vostra petici&oacute; de reserva de \"".mysqli_result($query_email,0,"nom_usuari")."\".<br><br><b>Usuari</b>: $qui.<br><b>Activitat/Assignatura</b>: $altres.<br><b>Motiu</b>: $motiu.<br><b>Dia</b>: $dia/$mes/$any.<br><b>Horari</b>: de $hora_inici a $hora_final.<br><b>Patr&oacute;</b>: $patro.<br><b>Titulaci&oacute; Patr&oacute;</b>: $titulacio_patro.<br><b>DNI Patr&oacute;</b>: $dni_patro.";
		$body.="<p>Motius de la denegaci&oacute;: $motius.</p>";
		$signature="Centre de C&agrave;lcul de la FNB</font>";
	else:
		$to="centre.calcul@fnb.upc.edu";
		//$cc="cap.serveis@fnb.upc.edu";
		//$cc1="<".mysqli_result($query_email,0,"email_fnb").">";
		//$bcc="centre.calcul@fnb.upc.edu";
		$subject="Descartada la ".html_entity_decode('petici&oacute;dica', ENT_NOQUOTES, 'UTF-8')." de reserva dels Espais Docents de \"".html_entity_decode(mysqli_result($query_email,0,"nom_usuari"))."\"";
		//$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Aviat rebreu confirmaci&oacute; de la vostra petici&oacute;.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
		$body="<b>Missatge generat autom&agrave;ticament.</b><br>";
		$body.="S'ha descartat la petici&oacute; de reserva de \"".mysqli_result($query_email,0,"nom_usuari")."\".<br><br><b>Usuari</b>: $qui.<br><b>Activitat/Assignatura</b>: $assig.<br><b>Motiu</b>: $motiu.<br><b>Dia</b>: $dia/$mes/$any.<br><b>Horari</b>: de $hora_inici a $hora_final.<br><b>Lloc</b>: $aula.<br><b>Projector</b>: $project.";
		$body.="<p>Motius: $motius.</p>";
		$signature="Centre de C&agrave;lcul de la FNB</font>";
	endif;
	EnviarEmail($from, $to, $cc, $cc1, $bcc, $subject, $body, $signature, $err_msg);
	//mail($f_mail,,"L'usuari $qui ha fet una reserva de $f_recurs pel motiu: $motiu","From: reservator@$SERVER_NAME");
}

//-----------------------------------------------------------------------------------------------------------
// Procediment: GestioReservaPuntual_Vaixell()
// Operativa  : Arriben els paramentres d'una reserva i cal comprovar que sigui valida
//-----------------------------------------------------------------------------------------------------------
function GestioReservaPuntual_Vaixell($dia,$mes,$any,$data_final,$recurs,$qui,$motiu,$hora_inici,$hora_final,$patro,$dni_patro,$titol,$titulacio_patro,$embarcats,$motiu,$altres,$f_tipus_reserva){
	global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/gestio_reserva_vaixell.tpl"
	));
	$resultat=0;

	// Connectem amb el servidor i seleccionem la BD corresponent
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
	@mysqli_query($conn,"SET NAMES 'utf8'");
	//Comprovem que realment pot eliminar aquesta reserva

	//$consulta="SELECT * FROM tbl_reserves WHERE id_responsable=\"$_SESSION['r_id_usuari']\"
	//	AND id_recurs=\"$recurs\"";

	$consulta="SELECT *	FROM tbl_recursos, tbl_usuaris WHERE tbl_recursos.id_recurs=\"$recurs\"";

	$query=@mysqli_query($conn,$consulta);

		$tpl->assign(array(
			"E_MSG_GEST_RESERVA"   => E_MSG_GEST_RESERVA,
			"E_DATA_INICI"         => E_DATA_INICI,
			"E_DATA_FINAL"         => E_DATA_FINAL,
			"E_HORA_INICI"         => E_HORA_INICI,
			"E_HORA_FINAL"         => E_HORA_FINAL,
			"E_DIA_SETMANA"        => E_DIA_SETMANA,
			"E_PETICIO"            => E_PETICIO,
			"E_RESPONSABLE_RECURS" => E_RESPONSABLE_RECURS,
			"E_QUI"                => E_QUI,
			"E_MOTIU"              => E_MOTIU,
			"E_PATRO"			   => E_PATRO,
			"E_DNI_PATRO"		   => E_DNI_PATRO,
			"E_TITULACIO_PATRO"	   => E_TITULACIO_PATRO,
			"E_TITOL"			   => E_TITOL,
			"E_EMBARCATS"		   => E_EMBARCATS,
			"E_ALTRES"			   => E_ALTRES,
			"E_ACTIVITAT"		   => E_ACTIVITAT

		));

		switch (strftime("%u", mktime(0, 0, 0, $mes, $dia, $any))):
			case 0: $dia_txt=E_DIA0; break;
			case 1: $dia_txt=E_DIA1; break;
			case 2: $dia_txt=E_DIA2; break;
			case 3: $dia_txt=E_DIA3; break;
			case 4: $dia_txt=E_DIA4; break;
			case 5: $dia_txt=E_DIA5; break;
			case 6: $dia_txt=E_DIA6; break;
			case 7: $dia_txt=E_DIA7; break;
		endswitch;
		$assig=stripslashes($assig);
		$motiu = stripslashes($motiu);
		$tpl->assign(array(
			"DATA_RESERVA"	=> $dia."-".$mes."-".$any,
			"DATA_FINAL" 	=> $dia."-".$mes."-".$any,
			"HORA_INICI" 	=> $hora_inici,
			"HORA_FINAL" 	=> $hora_final,
			"QUI" 			=> $qui,
			"EMAIL" 		=> $email,
			"MOTIU" 		=> $motiu,
			"DIA_TXT" 		=> $dia_txt,
			"PATRO" 		=> $patro,
			"ASSIG" 		=> $assig,
			"DNI_PATRO"		=> $dni_patro,
			"TITULACIO_PATRO"=>$titulacio_patro,
			"TITOL"			=> $titol,
			"EMBARCATS"		=> $embarcats,
			"ALTRES"		=> $altres,
			"DIA" 			=> $dia,
			"MES" 			=> $mes,
			"ANY" 			=> $any,
			"RECURS" 		=> $recurs,
			"RESPONSABLE"	=> mysqli_result($query,0,"tbl_usuaris.nom_usuari"),
			"DESC_RECURS"   => mysqli_result($query,0,"nom_recurs")
		));
	
	$tpl->parse(PAGE_CONTENT,"reserva");
}


//-----------------------------------------------------------------------------------------------------------
// Procediment: ValidarReserva_Vaixell()
// Operativa  : Arriben els paramentres d'una reserva i cal comprovar que sigui valida
//-----------------------------------------------------------------------------------------------------------
function MostrarPDF_Vaixell($dia,$mes,$any,$data_final,$recurs,$qui,$motiu,$hora_inici,$hora_final,$patro,$titol,$embarcats,$motiu,$altres){
header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=\"pdf_vaixell.pdf\"");
passthru("htmldoc --format pdf --left 2.5cm --right 1.5cm --top 1.5cm --bottom 1.5cm " .
         "--headfootsize 9 --header 't' --footer '/' " .
         "--size 'a4' --fontsize 10 --charset 8859-15 " .
	 "--webpage http://www.fnb.upc.edu/intrafnb/aules/reserves/pdf_vaixell.php?dia=$dia\"&mes=$mes&any=$any&data_final=$data_final&recurs=$recurs&qui=$qui&motiu=$motiu&hora_inici=$hora_inici&hora_final=$hora_final&patro=$patro&titol=$titol&embarcats=$embarcats&motiu=$motiu&altres=$altres\"");
}


//-----------------------------------------------------------------------------------------------------------
// Procediment: CheckDiesSetmana_Vaixell()
// Operativa  : Comprova una determinada reserva periodica per al recurs indicat
//-----------------------------------------------------------------------------------------------------------
function CheckDiesSetmana_Vaixell($data_inici,$data_final,$hora_inici,$hora_final){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir, $enviar_correu;
	
	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/aceptar_reserva.tpl"
	));
	
	$resultat=0;
	$a_date=dateToArray($data_inici);
	$dia0=$a_date[0]; $mes0=$a_date[1]; $any0=$a_date[2];
	$a_date=dateToArray($data_final);
	$dia1=$a_date[0]; $mes1=$a_date[1]; $any1=$a_date[2];

	if((!checkdate($mes0,$dia0,$any0)) || (!checkdate($mes1,$dia1,$any1))):
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_DATA_NO_VAL);
		$tpl->assign("E_MSG_RESERVA", E_MSG_NO_RESERVA_RSLT);
	
	else:
		if(($hora_inici<$hora_minima_vaixell) || (($hora_inici>=$hora_final)&&($dia0==$dia1)) || ($hora_final>$hora_maxima)):
			$resultat=1;
			$tpl->assign("E_MOTIUS", E_ERR_HORA_NO_VAL);
		else:
			
			while ($dia0<=$dia1):
				$f_numdia=date("w",mktime(0, 0, 0, "$mes0", "$dia0", "$any0"));
				switch ($f_numdia) {
					case 0:
						$_7=true;
						break;
					case 1:
						$_1=true;
						break;
					case 2:
						$_2=true;
						break;
					case 3:
						$_3=true;
						break;
					case 4:
						$_4=true;
						break;
					case 5:
						$_5=true;
						break;
					case 6:
						$_6=true;
						break;
					}
					$dia0++;
			endwhile;
		endif;
	endif;		
return compact('_1','_2','_3','_4','_5','_6','_7');			
}

//-----------------------------------------------------------------------------------------------------------
// Procediment: CalculaHoraInici()
// Operativa  : Comprova una determinada reserva periodica per al recurs indicat
//-----------------------------------------------------------------------------------------------------------
function CalculaHoraInici($data_inici,$data_final,$hora_inici,$_i){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir, $enviar_correu;
	
	$a_date=dateToArray($data_inici);
	$dia0=$a_date[0]; $mes0=$a_date[1]; $any0=$a_date[2];
	$a_date=dateToArray($data_final);
	$dia1=$a_date[0]; $mes1=$a_date[1]; $any1=$a_date[2];

	
	$f_numdia=date("w",mktime(0, 0, 0, "$mes0", "$dia0", "$any0"));
	if ($_i==$f_numdia):
		$hora_inici_i=$hora_inici;
	else:
		$hora_inici_i=$hora_minima_vaixell;
	endif;
	
	return $hora_inici_i;			
}

//-----------------------------------------------------------------------------------------------------------
// Procediment: CalculaHoraFinal()
// Operativa  : Comprova una determinada reserva periodica per al recurs indicat
//-----------------------------------------------------------------------------------------------------------
function CalculaHoraFinal($data_inici,$data_final,$hora_final,$_i){
	global $tpl, $hora_maxima_vaixell, $hora_minima_vaixell, $dbname, $dbuser, $dbpass, $dbserver, $template_dir, $enviar_correu;
	
	$a_date=dateToArray($data_inici);
	$dia0=$a_date[0]; $mes0=$a_date[1]; $any0=$a_date[2];
	$a_date=dateToArray($data_final);
	$dia1=$a_date[0]; $mes1=$a_date[1]; $any1=$a_date[2];

	
	$f_numdia=date("w",mktime(0, 0, 0, "$mes1", "$dia1", "$any1"));
	if ($_i==$f_numdia):
		$hora_final_j=$hora_final;
	else:
		$hora_final_j=$hora_maxima_vaixell;
	endif;
	
	return $hora_final_j;			
}
?>
