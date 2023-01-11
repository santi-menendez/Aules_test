<?php

//------------------------------------------------------------------------------------
// Procediment: PintarMesTaller()
// Operativa  : Donat un recurs qualsevol dels que estan donats d'alta en el sistema,
//              es pinta el calendari de reserves per al mes i any indicats
//------------------------------------------------------------------------------------
function PintarMesTaller($mes, $any, $recurs){
	global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;

	//LIMIT DEL CALENDARI
	$LIMIT=24;

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
				$activities_tmp="$activities_tmp <font color=\"Navy\"><b>".substr(mysqli_result($query, $j, 'hora_inici'),0,5)."-".substr(mysqli_result($query, $j, 'hora_final'),0,5)."</b></font><br><a class=\"reserva_cal\" href=\"javascript:openpopup('$PHP_SELF?id_reserva=".mysqli_result($query,$j,'id_reserva')."&op=82')\"><img title=\"Reservat per: ".mysqli_result($query, $j, 'qui_reserva')."\nMotiu: ".mysqli_result($query, $j, 'motiu_reserva')."\nAssignatura: ".mysqli_result($query, $j, 'aula')."\" align=\"right\" src=\"./img/info.gif\" border=\"0\"></a>&nbsp;<br><hr>";
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
				$activities_tmp="$activities_tmp <font color=\"Navy\"><b>".substr(mysqli_result($query_periodics, $k, 'hora_inici'),0,5)."-".substr(mysqli_result($query_periodics, $k, 'hora_final'),0,5)."</b></font><br><a class=\"reserva_cal\" href=\"javascript:openpopup_taller('$PHP_SELF?id_reserva=".mysqli_result($query_periodics,$k,'id_reserva')."&op=82')\"><img title=\"Reservat per: ".mysqli_result($query_periodics, $k, 'qui_reserva')."\nMotiu: ".mysqli_result($query_periodics, $k, 'motiu_reserva')."\nAssignatura: ".mysqli_result($query_periodics, $j, 'assig')."\" align=\"right\" src=\"./img/info.gif\" border=\"0\"></a>&nbsp;<br><hr>";
				$portem++;
			endif;
			$k++;
			if($k<$nFiles_periodics):
				$numero_dia_bd=mysqli_result($query_periodics, $k, "num_dia");
			endif;
		endwhile;

		$f_tmp=getdate();
		if(strtotime("$f_tmp[year]/$f_tmp[mon]/$f_tmp[mday]")<=strtotime("$any/$mes/$i")):
			$f_tmp= E_ADD;
			$str_funcions="&nbsp;<A class=\"m_function\"  class=\"m_function\" href=\"javascript:openpopup('$PHP_SELF?op=16&dia=$i&mes=$mes&any=$any&recurs=$recurs')\" title=\"$f_tmp\"><img align='middle' src='./img/puntual.gif' title='Fer reserva puntual / Veure reserves del dia' alt='Browse' border='0'></A>";
			$str_funcions.="&nbsp;<a href=\"javascript:openpopup_taller('$PHP_SELF?recurs=$recurs&op=55')\"><img align='center'  title='Fer reserva peri&ograve;dica' alt='Peri&ograve;dica' src='./img/periodic.gif' border='0'></a>";
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
	$endavant=sprintf("<a title='Mes seg&uuml;ent' href=\"$PHP_SELF?mes=%s&any=%s&recurs=%s&op=32\"><img align='absmiddle' alt='>>' src='./img/forward.jpg' border='0'></a>",$p_mes,$p_any,$recurs);
	if($mes==1):
		$a_mes=12;
		$a_any=$any-1;
	else:
		$a_mes=$mes-1;
		$a_any=$any;
	endif;
	$endarrera=sprintf("<a title='Mes anterior' href=\"$PHP_SELF?mes=%s&any=%s&recurs=%s&op=32\"><img align='absmiddle' alt='<<' src='./img/back.jpg' border='0'></a>",$a_mes,$a_any,$recurs);
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
// Procediment: CheckPeriodicReservation_Taller()
// Operativa  : Comprova una determinada reserva periodica per al recurs indicat
//-----------------------------------------------------------------------------------------------------------
function CheckPeriodicReservation_Taller($data_inici, $data_final, $dia, $hora_inici, $hora_final, $recurs, $qui, $motiu, $patro, $alum, $quin, $maquina, $fungible, $qquant){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir, $enviar_correu;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/aceptar_reserva_taller.tpl"
	));

	$resultat=0;
	$a_date=dateToArray($data_inici);
	$dia0=$a_date[0]; $mes0=$a_date[1]; $any0=$a_date[2];
	$a_date=dateToArray($data_final);
	$dia1=$a_date[0]; $mes1=$a_date[1]; $any1=$a_date[2];

	if((!checkdate($mes0,$dia0,$any0)) || (!checkdate($mes1,$dia1,$any1))):
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_DATA_NO_VAL);
	elseif (($qui=="") || ($motiu=="")):
		// No han omplert el camp QUI o MOTIU
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_MOTIU_NO_VAL);
	else:
		//if(($hora_inici<$hora_minima) || (($hora_inici>=$hora_final)&&($dia0==$dia1)) || ($hora_final>$hora_maxima)):
		if((mktime($hora_inici,0,0,$mes0,$dia0,$any0)<mktime($hora_minima,0,0,$mes0,$dia0,$any0)) || (mktime($hora_inici,0,0,$mes0,$dia0,$any0)>=mktime($hora_final,0,0,$mes1,$dia1,$any1)) || (mktime($hora_final,0,0,$mes1,$dia1,$any1)>mktime($hora_maxima,0,0,$mes1,$dia1,$any1))):
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

			if(@mysqli_num_rows($query)>0):
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
				if(@mysqli_num_rows($query_periodics)>0):
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
// Procediment: accept_periodic_reservation_taller()
// Operativa  : Comprova una determinada reserva periodica per al recurs indicat i continua amb el formulari
//				de reserva.
//-----------------------------------------------------------------------------------------------------------
function accept_periodic_reservation_taller($data_inici,$data_final,$dia,$hora_inici,$hora_final,$recurs,$qui,$motiu,$patro,$alum,$quin,$maquina,$fungible,$qquant){
	global $tpl;
	global $hora_maxima;
	global $hora_minima;
	global $dbname;
	global $dbuser;
	global $dbpass;
	global $dbserver;
	global $lang;
	global $locales;
	global $template_dir;
	global $enviar_correu;

    	$tpl->define( array(page => "$template_dir/minipage.tpl",
        					reserva => "$template_dir/aceptar_reserva_taller.tpl") );
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
		        //if(($hora_inici<$hora_minima)||(($hora_inici>=$hora_final)&&($dia0==$dia1))||($hora_final>$hora_maxima)){
				if((mktime($hora_inici,0,0,$mes0,$dia0,$any0)<mktime($hora_minima,0,0,$mes0,$dia0,$any0)) || (mktime($hora_inici,0,0,$mes0,$dia0,$any0)>=mktime($hora_final,0,0,$mes1,$dia1,$any1)) || (mktime($hora_final,0,0,$mes1,$dia1,$any1)>mktime($hora_maxima,0,0,$mes1,$dia1,$any1))){
		        	$resultat=1;
		        	$tpl->assign("E_MOTIUS", E_ERR_HORA_NO_VAL);
		
		        }else{
		        	
		        	$f_hora_inici="$hora_inici:00:00";
		        	$f_hora_final="$hora_final:00:00";
					//En cas d'especificar ALTRES en la selecció de la reserva del Vaixell, em de inserir l'esmentada explicació, en lloc del motiu
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
						//($dia,$mes,$any,$recurs,$qui,$motiu,$hora_inici,$hora_final){
						$consulta_insert="INSERT INTO tbl_reserves(id_recurs,qui_reserva,motiu_reserva,assig,aula,
								data_reserva,data_final,hora_inici,hora_final,num_dia,tipus_reserva,assistents,desc_activitat,maq_eines,consum,desc_consum)
								VALUES (\"$recurs\",\"$qui\",\"$motiu\",\"$patro\",\"$aula\",\"$f_data_inici\",\"$f_data_final\",\"$f_hora_inici\",
								\"$f_hora_final\",\"$dia\",\"$f_tipus_reserva\",\"$alum\",\"$quin\",\"$maquina\",\"$fungible\",\"$qquant\")";
						$query_periodics=@mysqli_query($conn,$consulta_insert);
						if (mysqli_affected_rows($conn) == 1) {
		       					$tpl->assign("E_MOTIUS","");
		     				} else {
		       					$resultat=4;
		       					$tpl->assign("E_MOTIUS", E_ERR_INSERT_DB);
		     				}
		        		}
		        	}
			}

	};

	if($resultat==0):
		// S'ha fet la reserva sense problemes.
		// Ho notifiquem per pantalla i, si procedeix, enviem el mail de confirmacio...
		$tpl->assign("E_MSG_RESERVA", E_MSG_RESERVA_RSLT);

		//$enviar_correu=1;
		//if($enviar_correu):
		$consulta_email="SELECT re.avisar_resp as avisar, u.email as email, u.email_fnb as email_fnb, re.nom_recurs as recurs
							FROM tbl_recursos re, tbl_usuaris u
							WHERE re.id_responsable=u.id_usuari AND re.id_recurs='$recurs'";

		$query_email=@mysqli_query($conn,$consulta_email);
		if (mysqli_result($query_email,0,"avisar_resp")=="1"):
			//Si cal avisar de les reserves del recurs, enviem email al responsable
			$from="No respondre <centre.calcul@fnb.upc.edu>";
			$to=mysqli_result($query_email,0,"email_fnb");
			$cc="<cap.serveis@fnb.upc.edu>";
			$cc1="<responsable.taller@fnb.upc.edu>";
			$bcc="<centre.calcul@fnb.upc.edu>";
			$subject="Reservator v.1.1: Nova reserva peri&ograve;dica de \"".mysqli_result($query_email,0,"recurs")."\"";
			$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Si us plau, no el contesteu.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
			$body.="S'ha fet una nova reserva de \"".mysqli_result($query_email,0,"recurs")."\".<br><br>&nbsp;&nbsp;&nbsp;<b>Usuari</b>: $qui.<br>&nbsp;&nbsp;&nbsp;<b>Activitat</b>: $motiu.<br>&nbsp;&nbsp;&nbsp;<b>Data</b>: del $data_inici al $data_final.<br>&nbsp;&nbsp;&nbsp;<b>Horari</b>: de $hora_inici a $hora_final.<p>";
			$body.="<p>Clicar <a href='./index.php?any=$any&mes=$mes&recurs=$recurs&op=31'>aqui</a> per veure el recurs.</p>";
			$signature="Centre de C&agrave;lcul FNB</font>";

			EnviarEmail($from, $to, $cc, $cc1, $bcc, $subject, $body, $signature, $err_msg);
			//mail($f_mail,,"L'usuari $qui ha fet una reserva de $f_recurs pel motiu: $motiu","From: reservator@$SERVER_NAME");
		endif;
		//endif;
	endif;

		$tpl->assign("E_MSG_RESERVA", E_MSG_RESERVA_RSLT);
		$tpl->assign("E_DATA_RESERVA", $f_data_inici_reserva);
		$tpl->assign("E_DATA_FINAL", $f_data_final_reserva);
		$tpl->assign("E_RECURS", $recurs);
		$tpl->assign("E_QUI", $qui);
		$tpl->assign("E_MOTIU", $motiu);
		$tpl->assign("E_HORA_INICI", $f_hora_inici);
		$tpl->assign("E_HORA_FINAL", $f_hora_final);
		$tpl->assign("E_RESP_ACTI", $patro);
		$tpl->assign("E_QUIN", $quin);
		$tpl->assign("E_ALUM", $alum);
		$tpl->assign("E_MAQ", $maquina);
		$tpl->assign("E_FUNG", $fungible);
		$tpl->assign("E_QQUANT", $qquant);
		$tpl->assign("E_AVIS_LEGAL", NULL);
	$tpl->parse(PAGE_CONTENT,"reserva");
}

//-----------------------------------------------------------------------------------------------------------
// Procediment: PintarReservesDia_Taller()
// Operativa  : Mostra totes les reserves que hi ha per a un recurs i dia determinats
//-----------------------------------------------------------------------------------------------------------
function PintarReservesDia_Taller($dia, $mes, $any, $recurs){
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
	
	//Em d'averiguar el uid, ja que un usuari pot tenir mes de un username ja que es poden validar a més d'un servidor LDAP diferent o tenir
	//un alias. I el uid es únic per usuari.
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
	$f_desc_recurs=mysqli_result($query,0,"nom_recurs");
	$f_id_responsable=mysqli_result($query,0,"id_responsable");
	$f_nom_responsable=mysqli_result($query,0,"nom_usuari");
	$f_id_aula=mysqli_result($query,0,"aula");
	
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

	for($i=$hora_minima;$i<$hora_maxima;$i++):
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
				$tpl->assign("ASSIG", mysqli_result($query_periodics, $k, "assig"));
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
			$str_funcions="<a  href=\"javascript:openpopup_taller('$PHP_SELF?hora_inici=$i&op=54&recurs=$recurs&dia=$dia&mes=$mes&any=$any')\" title=\"$f_tmp\"><img align='middle' src='./img/reservar.gif' alt='Reservar' border='0'></a>";
			//reservation($dia,$mes,$any,$recurs,$hora_inici)
		else:
			$consulta="SELECT *
				FROM tbl_usuaris
				WHERE nom_usuari='$f_qui_reserva'";
			$query=@mysqli_query($conn,$consulta);
			$f_uid_reserva=mysqli_result($query,0,"uid");
			$activities_tmp="<a href=\"javascript:openpopup_taller('$PHP_SELF?id_reserva=$id_reserva&op=82')\">$activities_tmp</a>";
			if(($f_nom_responsable==$_SESSION['nom_usuari'])||($_SESSION['perfil']=="CCESAII")||($f_uid_reserva==$f_uid_sessio)):
				$f_tmp=E_DELETE;
//**				$str_funcions="<a href=\"$PHP_SELF?hora_inici=$i&op=$f_op&recurs=$recurs&dia=$dia&mes=$mes&any=$any&id_reserva=$id_reserva\" title=\"$f_tmp\"><img align='middle' src='./img/anular.gif' alt='Anul&middot;lar' border='0'></a>";
				$str_funcions="<a href=\"$PHP_SELF?op=73&recurs=$recurs&dia=$dia&mes=$mes&any=$any&id_reserva=$id_reserva\" title=\"$f_tmp\"><img align='middle' src='./img/anular.gif' alt='Anul&middot;lar' border='0'></a>";
			endif;
		endif;
		$tpl->assign("ADD",$str_funcions);
		$tpl->assign("ACTIVITY", $activities_tmp);
		$iPlus=$i+1;
		$tpl->assign("TIME", "$i:00-$iPlus:00");

		$tpl->parse(TABLE_ROWS, ".table_row");
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
function MostrarInfoReserva_Taller($id_reserva){
	global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/info_reserva_taller.tpl"
	));
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
							re.assistents AS assistents,
							re.desc_activitat AS desc_activitat,
							re.maq_eines AS maq_eines,
							re.consum AS consum,
							re.desc_consum AS desc_consum
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

		$tpl->assign(array(
			"E_MSG_INF_RESERVA"    => E_MSG_INF_RESERVA,
			"E_DATA_INICI"         => E_DATA_INICI,
			"E_DATA_FINAL"         => E_DATA_FINAL,
			"E_HORA_INICI"         => E_HORA_INICI,
			"E_HORA_FINAL"         => E_HORA_FINAL,
			"E_PERIODICA"          => E_PERIODICA,
			"E_QUI"                => E_QUI,
			"E_QUIN"               => E_QUIN,
			"E_RESPONSABLE_RECURS" => E_RESPONSABLE_RECURS,
			"E_RESP_ACTI"		   => E_RESP_ACTI,
			"E_ALUM"			   => E_ALUM,
			"E_ACTIVITAT"		   => E_ACTIVITAT,
			"E_MAQ"				   => E_MAQ,
			"E_FUNG"			   => E_FUNG,
			"E_QQUANT"		  	   => E_QQUANT,
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
			"HORA_INICI" => mysqli_result($query_vaixell,0,"hora_inici"),
			"HORA_FINAL" => mysqli_result($query_vaixell,$num_rows_vaixell-1,"hora_final"),
			"QUI" => mysqli_result($query,0,"qui"),
			"EMAIL" => mysqli_result($query,0,"email"),
			"RESPONSABLE" => mysqli_result($query,0,"respon"),
			"ACTIVITAT" => mysqli_result($query,0,"motiu"),
			"DIA" => $dia,
			"RESP_ACTI" => mysqli_result($query,0,"assig"),
			"ALUM" => mysqli_result($query,0,"assistents"),
			"QUIN" => mysqli_result($query,0,"desc_activitat"),
			"MAQUINA" => mysqli_result($query,0,"maq_eines"),
			"FUNGIBLE" => mysqli_result($query,0,"consum"),
			"QUANTITAT" => mysqli_result($query,0,"desc_consum")
		));
	endif;
	$tpl->parse(PAGE_CONTENT,"reserva");
}


//-----------------------------------------------------------------------------------------------------------
// Procediment: FerReservaPuntual_Taller()
// Operativa  : Permet fer una reserva puntual d'un recurs donat
//-----------------------------------------------------------------------------------------------------------
function FerReservaPuntual_Taller($dia,$mes,$any,$recurs,$hora_inici){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/reserva_taller.tpl"
	));

	// Connectem amb el servidor i seleccionem la BD corresponent
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
	@mysqli_query($conn,"SET NAMES 'utf8'");

	$consulta="SELECT * FROM tbl_recursos WHERE id_recurs=$recurs";

	$query=@mysqli_query($conn,$consulta);
	$f_desc_recurs=mysqli_result($query,0,"nom_recurs");
	$f_edifici = mysqli_result($query,0,"id_edifici");
	$tpl->assign("DESC_RECURS",$f_desc_recurs);


	$f_tmp="$dia/$mes/$any";
	$tpl->assign(array(
		"DATA"         => $f_tmp,
		"E_DATA"       => E_DATA,
		"E_RECURS"     => E_RECURS,
		"USUARI"       => $_SESSION['nom_usuari'],
		"E_CHECKLIST"  => E_CHECKLIST,
		"E_QUI"        => E_QUI,
		"E_HORA_INICI" => E_HORA_INICI,
		"E_HORA_FINAL" => E_HORA_FINAL,
		"E_RESERVA"    => E_RESERVA,
		"E_RESP_ACTI"  => E_RESP_ACTI,
		"E_TITOL"      => E_TITOL,
		"E_ASSIG"      => E_ASSIG,
		"E_ALUM" 	   => E_ALUM,
		"E_ACTIVITAT"  => E_ACTIVITAT,
		"E_QUIN"       => E_QUIN,
		"E_FUNG"       => E_FUNG,
		"E_MAQ"        => E_MAQ,
		"E_QQUANT"     => E_QQUANT,
		"E_ALTRES"     => E_ALTRES,
		"E_AVIS_LEGAL" => NULL
	));
	$f_tmp=franges_horaries($hora_minima, $hora_maxima, $hora_inici, "hora_inici");
	$tpl->assign("FRANGES_HORARIES_INICI",$f_tmp);

	$f_tmp=franges_horaries($hora_minima, $hora_maxima+1, $hora_inici+1, "hora_final");
	$tpl->assign(array(
		"FRANGES_HORARIES_FINAL" => $f_tmp,
		"HORA_INICI"             => "$hora_inici"
	));
	$hora_final=$hora_inici+1;
	
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
	if($f_perfil=='CCESAII') {
		$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/reserva_admin_taller.tpl"
		));
		$f_persona=select_who($dbserver, $dbname, $dbuser, $dbpass, "qui");
		$tpl->assign("USUARI",$f_persona);
	}
	
	$tpl->parse(PAGE_CONTENT, "reserva");
}


//-----------------------------------------------------------------------------------------------------------
// Procediment: ValidarReserva_Taller()
// Operativa  : Arriben els paramentres d'una reserva i cal comprovar que sigui valida
//-----------------------------------------------------------------------------------------------------------
function ValidarReserva_Taller($dia,$mes,$any,$recurs,$qui,$motiu,$hora_inici,$hora_final,$patro,$alum,$quin,$maquina,$fungible,$qquant){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir, $enviar_correu, $url_reserves;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/aceptar_reserva_taller.tpl"
	));
	$resultat=0;

	if(!checkdate($mes,$dia,$any)):
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_DATA_NO_VAL);

	elseif(time()>mktime($hora_inici,0,0,$mes,$dia,$any)):
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_ARA_NO_VAL);
	elseif(($patro=="") || ($motiu=="0")):
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_MOTIU_NO_VAL);
	else:
		//if(($hora_inici<$hora_minima)||($hora_inici>=$hora_final)||($hora_final>$hora_maxima)):
		if((mktime($hora_inici,0,0,$mes,$dia,$any)<mktime($hora_minima,0,0,$mes,$dia,$any)) || (mktime($hora_inici,0,0,$mes,$dia,$any)>=mktime($hora_final,0,0,$mes,$dia,$any)) || (mktime($hora_final,0,0,$mes,$dia,$any)>mktime($hora_maxima,0,0,$mes,$dia,$any))):
			$resultat=1;
			$tpl->assign("E_MOTIUS", E_ERR_HORA_NO_VAL);
echo $hora_inici."-".$hora_minima."-------".$hora_final."-".$hora_maxima;exit;
		else:
			$f_hora_inici="$hora_inici:00:00";
			$f_hora_final="$hora_final:00:00";
			$f_data="$any/$mes/$dia";
			$f_data_reserva="$dia/$mes/$any";
			//En cas d'especificar ALTRES en la selecció de la reserva del Vaixell, em de inserir l'esmentada explicació, en lloc del motiu
			//ja tipificat
			if ($motiu==5):
				$motiu=$altres;
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
			
			// Ara llegim les diferents reserves que hi ha d'aquell recurs per aquella hora
			$consulta="SELECT id_recurs, qui_reserva, motiu_reserva, data_reserva,
									data_final, hora_inici,  hora_final
							FROM tbl_reserves
							WHERE data_reserva=\"$f_data\" AND id_recurs=$recurs AND
									(((hora_inici<=\"$f_hora_inici\") AND (hora_final>\"$f_hora_inici\")) OR
										((hora_final>=\"$f_hora_final\") AND (\"$f_hora_final\">hora_inici)) OR
										((hora_inici<=\"$f_hora_inici\")AND(\"$f_hora_final\"<=hora_final)) OR
										((hora_inici>=\"$f_hora_inici\")AND(hora_final<=\"$f_hora_final\"))) AND
									num_dia=0
							ORDER BY hora_inici";
							
			$query=@mysqli_query($conn,$consulta);

			if(mysqli_num_rows($query)>0):
				$resultat=2;
				//printf("$query");

				$taula=mysqli_result_table($query);
				$f_tmp=E_ERR_HI_HA_RESERVA."<br><br>$taula";
				$tpl->assign("E_MOTIUS",$f_tmp);
			else:
				$femtemps=getdate(mktime(0,0,0,$mes,$dia,$any));
				$numero_dia=monsun($femtemps[wday])+1;

				$consulta_periodics="SELECT id_recurs, qui_reserva, motiu_reserva,
													data_reserva, data_final, hora_inici, hora_final
											FROM tbl_reserves
											WHERE ((data_reserva<=\"$f_data\") AND (data_final>=\"$f_data\")) AND
													(((hora_inici<=\"$f_hora_inici\") AND (hora_final>\"$f_hora_inici\")) OR
														((hora_final>=\"$f_hora_final\") AND (\"$f_hora_final\">hora_inici)) OR
														((hora_inici<=\"$f_hora_inici\") AND (\"$f_hora_final\"<=hora_final)) OR
														((hora_inici>=\"$f_hora_inici\") AND (hora_final<=\"$f_hora_final\"))) AND
													num_dia=$numero_dia AND id_recurs=$recurs
											ORDER BY hora_inici";

				$query_periodics=@mysqli_query($conn,$consulta_periodics);
				if(mysqli_num_rows($query_periodics)>0):
					$resultat=3;
					$taula=mysqli_result_table($query_periodics);
					$f_tmp=E_ERR_HI_HA_RESERVA."<br><br>$taula";
					$tpl->assign("E_MOTIUS",$f_tmp);
				else:
					// Ara ja podem inserir i recollir el resultat
					//($dia,$mes,$any,$recurs,$qui,$motiu,$hora_inici,$hora_final){
					$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
					@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
					$consulta_insert="INSERT INTO tbl_reserves(id_recurs,qui_reserva,motiu_reserva,assig,aula,
							data_reserva,data_final,hora_inici,hora_final,num_dia,tipus_reserva,assistents,desc_activitat,maq_eines,consum,desc_consum)
							VALUES (\"$recurs\",\"$qui\",\"$motiu\",\"$patro\",\"$aula\",\"$f_data\",\"$f_data\",\"$f_hora_inici\",
							\"$f_hora_final\",\"$numero_dia\",\"$f_tipus_reserva\",\"$alum\",\"$quin\",\"$maquina\",\"$fungible\",\"$qquant\")";
					$query_periodics=@mysqli_query($conn,$consulta_insert);
					if (mysqli_affected_rows($conn) == 1) :
						$tpl->assign("E_MOTIUS","");
					else:
						$resultat=4;
						$tpl->assign("E_MOTIUS", E_ERR_INSERT_DB);
					endif;
				endif;
			endif;
		endif;
	endif;

	if($resultat==0):
		// Sha fet la reserva sense problemes.
		// Ho notifiquem per pantalla i, si procedeix, enviem el mail de confirmacio...
		$tpl->assign("E_MSG_RESERVA", E_MSG_RESERVA_RSLT);
		$tpl->assign("E_DATA_RESERVA", $f_data_reserva);
		$tpl->assign("E_DATA_FINAL", $f_data_reserva);
		$tpl->assign("E_RECURS", $recurs);
		$tpl->assign("E_QUI", $qui);
		$tpl->assign("E_MOTIU", $motiu);
		$tpl->assign("E_HORA_INICI", $f_hora_inici);
		$tpl->assign("E_HORA_FINAL", $f_hora_final);
		$tpl->assign("E_RESP_ACTI", $patro);
		$tpl->assign("E_QUIN", $quin);
		$tpl->assign("E_ALUM", $alum);
		$tpl->assign("E_MAQ", $maquina);
		$tpl->assign("E_FUNG", $fungible);
		$tpl->assign("E_QQUANT", $qquant);

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
				$cc="<cap.serveis@fnb.upc.edu>";
				$cc1="responsable.taller@fnb.upc.edu";
				$bcc="<centre.calcul@fnb.upc.edu>";
				$subject=utf8_encode("Reservator v.1.1: Nova reserva de \"".mysqli_result($query_email,0,"recurs")."\"");
				$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Si us plau, no el contesteu.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
				$body.="S'ha fet una nova reserva de \"".mysqli_result($query_email,0,"recurs")."\".<br><br>&nbsp;&nbsp;&nbsp;<b>Usuari</b>: $qui.<br>&nbsp;&nbsp;&nbsp;<b>Activitat</b>: $motiu.<br>&nbsp;&nbsp;&nbsp;<b>Dia</b>: $dia/$mes/$any.<br>&nbsp;&nbsp;&nbsp;<b>Horari</b>: de $hora_inici a $hora_final.<p>";
				$body.="<p>Clicar <a href='$url_reserves?any=$any&mes=$mes&recurs=$recurs&op=30'>aqui</a> per veure el recurs.</p>";
				$signature="Centre de C&agrave;lcul FNB</font>";

				EnviarEmail($from, $to, $cc, $cc1, $bcc, $subject, $body, $signature, $err_msg);
				//mail($f_mail,,"L'usuari $qui ha fet una reserva de $f_recurs pel motiu: $motiu","From: reservator@$SERVER_NAME");
			endif;
		endif;


	else:
		$tpl->assign("E_MSG_RESERVA", E_MSG_NO_RESERVA_RSLT);
	endif;

	$tpl->parse(PAGE_CONTENT,"reserva");
	
}


//-----------------------------------------------------------------------------------------------------------
// Procediment: ValidarReserva_Taller()
// Operativa  : Arriben els paramentres d'una reserva i cal comprovar que sigui valida
//-----------------------------------------------------------------------------------------------------------
function MostrarPDF_Taller($dia,$mes,$any,$recurs,$qui,$motiu,$hora_inici,$hora_final,$patro,$titol,$embarcats,$motiu,$altres){
header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=\"pdf_vaixell.pdf\"");
passthru("htmldoc --format pdf --left 2.5cm --right 1.5cm --top 1.5cm --bottom 1.5cm " .
         "--headfootsize 9 --header 't' --footer '/' " .
         "--size 'a4' --fontsize 10 --charset 8859-15 " .
	 "--webpage http://www.fnb.upc.edu/intrafnb/aules/reserves/pdf_vaixell.php?dia=$dia\"&mes=$mes&any=$any&recurs=$recurs&qui=$qui&motiu=$motiu&hora_inici=$hora_inici&hora_final=$hora_final&patro=$patro&titol=$titol&embarcats=$embarcats&motiu=$motiu&altres=$altres\"");
}


//-----------------------------------------------------------------------------------------------------------
// Procediment: CheckDiesSetmana_Vaixell()
// Operativa  : Comprova una determinada reserva periodica per al recurs indicat
//-----------------------------------------------------------------------------------------------------------
function CheckDiesSetmana_Taller($data_inici,$data_final,$hora_inici,$hora_final){
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
		//if(($hora_inici<$hora_minima) || (($hora_inici>=$hora_final)&&($dia0==$dia1)) || ($hora_final>$hora_maxima)):
		if((mktime($hora_inici,0,0,$mes0,$dia0,$any0)<mktime($hora_minima,0,0,$mes0,$dia0,$any0)) || (mktime($hora_inici,0,0,$mes0,$dia0,$any0)>=mktime($hora_final,0,0,$mes1,$dia1,$any1)) || (mktime($hora_final,0,0,$mes1,$dia1,$any1)>mktime($hora_maxima,0,0,$mes1,$dia1,$any1))):
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

?>