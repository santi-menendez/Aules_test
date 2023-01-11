<?php
//------------------------------------------------------------------------------------
// Procediment: PintarSetmanaAulaInformatica()
// Operativa  : Donat un recurs qualsevol dels que estan donats d'alta en el sistema,
//              es pinta el calendari de reserves per al mes i any indicats
//------------------------------------------------------------------------------------
function PintarSetmanaAulaInformatica($p_dia,$u_dia,$mes,$any,$recurs){
	global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $locales, $lang, $template_dir, $p_dia, $u_dia, $mes, $any, $recurs;

	$i_mes=$mes;
	$i_any=$any;
	$hora_maxima=20;
	$hora_minima=10;
	$f_hora_inici='10';
	
	//Calculem el últim dia de la setmana tenint en compte el possible canvi de mes i/o any
	extract(Calcula_ultim_dia_setmanal($p_dia,$mes,$any));

	//LIMIT DEL CALENDARI
	$LIMIT=24;

	$tpl->define(array(
		table     => "$template_dir/calendar.tpl",
		table_row => "$template_dir/week.tpl",
		day       => "$template_dir/day_week.tpl"
	));

	// Connectem amb el servidor i seleccionem la BD corresponent
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
	@mysqli_query($conn,"SET NAMES 'utf8'");

	$consulta="SELECT * FROM tbl_recursos r,tbl_edificis e
				WHERE r.id_recurs=$recurs AND e.id_edifici=r.id_edifici";

	$query=@mysqli_query($conn,$consulta);
	$f_desc_recurs=mysqli_result($query,0,"nom_recurs");
	$f_id_responsable=mysqli_result($query,0,"id_responsable");
	$f_edifici=mysqli_result($query,0,"nom_edifici");
	$numero_dies=days_in_month ( $mes, $any);

	$consulta_periodics="SELECT * FROM tbl_reserves
		WHERE ((data_reserva<=\"$any-$mes-$p_dia\" AND data_final>=\"$u_any-$u_mes-$u_dia\") OR
			(data_final>=\"$any-$mes-$p_dia\" AND data_final<=\"$u_any-$u_mes-$u_dia\") OR
			(data_reserva>=\"$any-$mes-$p_dia\" AND data_reserva<=\"$u_any-$u_mes-$u_dia\")) AND
			id_recurs=$recurs AND
			num_dia>=0
			ORDER BY num_dia, hora_inici";
	
	$query_periodics=@mysqli_query($conn,$consulta_periodics);
	$nFiles_periodics=mysqli_num_rows($query_periodics);

	$j=0;
	$canviat=0;

	for($z=0; $z<7; $z++):
		$i=$p_dia+$z;
		if($i>$numero_dies):
			$i=$i%$numero_dies;
			if($canviat==0):
				$mes++;
				if($mes>12):
					$mes=1;
					$any++;
				endif;
				$canviat++;
			endif;
		endif;
		//Indiquem si aquest dia en concret es festa o no
		$festa=CalculemSiEsFesta($dbserver, $dbname, $dbuser, $dbpass, $i, $mes, $any);
		//Indiquen si aquest dia es no lectiu
		$lectiu=CalculemSiEsNoLectiu($dbserver, $dbname, $dbuser, $dbpass, $i, $mes, $any);
		//Descripció del tipus de festa
		$comentari=ComentariFestius($dbserver, $dbname, $dbuser, $dbpass, $i, $mes, $any);
		
		$activities_tmp="";
		$portem=0;

		// Ara analitzarem les activitats PERIODIQUES que hi ha en aquest mes
		$f_femtemps=mktime(0,0,0,$mes,$i,$any);
		$femtemps=getdate($f_femtemps);
		$numero_dia=monsun($femtemps[wday])+1;
		$k=0;
		
		if($k<$nFiles_periodics):
			$numero_dia_bd=mysqli_result($query_periodics, $k, "num_dia");
		endif;
		//Revisem els dies que no hi ha res per obrir l'aula
		$cons_no_res="SELECT * FROM tbl_reserves
		WHERE ((data_reserva<=\"$any-$mes-$p_dia\" AND data_final>=\"$u_any-$u_mes-$u_dia\") OR
			(data_final>=\"$any-$mes-$p_dia\" AND data_final<=\"$u_any-$u_mes-$u_dia\") OR
			(data_reserva>=\"$any-$mes-$p_dia\" AND data_reserva<=\"$u_any-$u_mes-$u_dia\")) AND
			id_recurs=$recurs AND
			num_dia=$numero_dia";
		$query_no_res=@mysqli_query($conn,$cons_no_res);
		$nFiles_no_res=mysqli_num_rows($query_no_res);
		if (!$nFiles_no_res && $numero_dia<6 && !$festa && $mes!=7 && $mes!=8):
			$activities_tmp="$activities_tmp <font color=\"Green\"><b>".$hora_minima.":00-".$hora_maxima.":00</b></font><br><font color=\"Green\">&nbsp;&nbsp;Oberta <b>Aula 2</b></font><hr>";
		endif;
		
		//Revisem els dies que hi ha classe.
		while(($k<$nFiles_periodics)&&($numero_dia>=$numero_dia_bd)&&($portem<$LIMIT)):

			if(($numero_dia==$numero_dia_bd)&&
			(strtotime(mysqli_result($query_periodics, $k, "data_reserva"))<=$f_femtemps)&&
			(strtotime(mysqli_result($query_periodics, $k, "data_final"))>=$f_femtemps)):
				//Averiguem els professors de la assignatura
				if ($mes==7):
					if ($i<15):
						$curs=$any-1;
						$quad=2;
					else:
						$curs=$any;
						$quad=1;
					endif;
				elseif ($mes>=8):
					$curs=$any;
					$quad=1;
				elseif ($mes==1):
					$curs=$any-1;
					$quad=1;
				elseif ($mes>=2 || $mes<=7):
					$curs=$any-1;
					$quad=2;
				endif;
				//Comprovem que es una reserva d'una assignatura reglada
				$n_assig=mysqli_result($query_periodics, $k, 'assig');
				$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
				@mysqli_select_db($conn, "pruebas-oracle") or die(E_ERR_SELECT_DB);
				$cons_prof="SELECT DISTINCT NUCLI_VW_PERSONES_280.cognoms as cognoms, NUCLI_VW_PERSONES_280.nom as nom
				FROM NUCLI_VW_PERSONES_280, NUCLI_VW_PROFESSOR_UD_280 
				WHERE NUCLI_VW_PERSONES_280.codi_persona=NUCLI_VW_PROFESSOR_UD_280.codi_persona AND NUCLI_VW_PROFESSOR_UD_280.codi_upc_ud=\"$n_assig\" AND NUCLI_VW_PROFESSOR_UD_280.curs=\"$curs\" AND NUCLI_VW_PROFESSOR_UD_280.quad=\"$quad\"";
				$result_cons_prof = @mysqli_query($conn,$cons_prof);
				$n_prof=mysqli_num_rows($result_cons_prof);
				if ($row_codi_prof = mysqli_fetch_array($result_cons_prof)):
					$prof="\nProfessorat:";
					do {
						$prof .= "\n<font face=arial size=1px>\n".$row_codi_prof["cognoms"].", ".$row_codi_prof["nom"]."</font>";
					} while ($row_codi_prof = mysqli_fetch_array($result_cons_prof));
					$n_qui=NULL;
				else:
					$n_qui=mysqli_result($query_periodics, $k, 'qui_reserva')."<br>";
				endif;
				
				$hora_inici_classe = substr(mysqli_result($query_periodics, $k, 'hora_inici'),0,2);
				$hora_final_classe = substr(mysqli_result($query_periodics, $k, 'hora_final'),0,2);
				if ($hora_inici_classe <= $hora_minima):
					$f_hora_inici = $hora_final_classe;
				elseif ($hora_inici_classe>$hora_minima && $hora_inici_classe>$f_hora_inici):
					$activities_tmp="$activities_tmp <font color=\"Green\"><b>".$f_hora_inici.":00-".$hora_inici_classe.":00</b></font><br><font color=\"Green\">&nbsp;&nbsp;Oberta <b>Aula 2</b></font><hr>";
				elseif ($hora_final_classe < $f_hora_inici):
					$activities_tmp="$activities_tmp <font color=\"Green\"><b>".$hora_final_classe.":00-".$hora_maxima.":00</b></font><br><font color=\"Green\">&nbsp;&nbsp;Oberta <b>Aula 2</b></font><hr>";
				endif;
				$activities_tmp="$activities_tmp <font color=\"Navy\"><b>".substr(mysqli_result($query_periodics, $k, 'hora_inici'),0,5)."-".substr(mysqli_result($query_periodics, $k, 'hora_final'),0,5)."</b></font><br>".$n_qui."<i>".mysqli_result($query_periodics, $k, 'assig')."</i><br><font color=\"Red\">".mysqli_result($query_periodics, $k, 'motiu_reserva')."</font><br>".$prof."<br><a class=\"reserva_cal\" href=\"javascript:openpopup('$PHP_SELF?id_reserva=".mysqli_result($query_periodics,$k,'id_reserva')."&op=80')\"><img title=\"Mostra els detalls de la reserva\" align=\"right\" src=\"./img/info.gif\" border=\"0\"></a>&nbsp;<br><hr>";
				$portem++;
			endif;
			$k++;
			if($k<$nFiles_periodics):
				$numero_dia_bd=mysqli_result($query_periodics, $k, "num_dia");
			endif;
		endwhile;
		if ($hora_final_classe<$hora_maxima  && $numero_dia<6 && !$festa && $mes!=7 && $mes!=8 && $hora_inici_classe!=NULL):
			$activities_tmp="$activities_tmp <font color=\"Green\"><b>".$hora_final_classe.":00-".$hora_maxima.":00</b></font><br><font color=\"Green\">&nbsp;&nbsp;Oberta <b>Aula 2</b></font><hr>";
		endif;
		$hora_inici_classe = NULL;
		$hora_final_classe = NULL;
		$f_tmp=getdate();
		if((strtotime("$f_tmp[year]/$f_tmp[mon]/$f_tmp[mday]")<=strtotime("$any/$mes/$i"))&&($festa==0)&&($lectiu==1)):
			$f_tmp= E_ADD;
			$str_funcions="&nbsp;<A class=\"m_function\"  class=\"m_function\" href=\"javascript:openpopup('$PHP_SELF?op=11&dia=$i&mes=$mes&any=$any&recurs=$recurs')\" title=\"$f_tmp\"><img align='middle' src='./img/puntual.gif' title='Fer reserva puntual / Veure reserves del dia' alt='Browse' border='0'></A>";
			//if ($_SESSION['perfil']=="CCESAII"): Para que la reserva periódica solo aparezca en los autorizados.
				//print $_SESSION['perfil'];exit;
				$str_funcions.="&nbsp;<a href=\"javascript:openpopup('$PHP_SELF?recurs=$recurs&op=55')\"><img align='center'  title='Fer reserva peri&ograve;dica' alt='Peri&ograve;dica' src='./img/periodic.gif' border='0'></a>";
			//endif;
			$f_colour="enabled_day";
		elseif((strtotime("$f_tmp[year]/$f_tmp[mon]/$f_tmp[mday]")<=strtotime("$any/$mes/$i"))&&($festa==0)&&($lectiu==0)):
			$f_tmp= E_ADD;
			$str_funcions="&nbsp;<A class=\"m_function\"  class=\"m_function\" href=\"javascript:openpopup('$PHP_SELF?op=11&dia=$i&mes=$mes&any=$any&recurs=$recurs')\" title=\"$f_tmp\"><img align='middle' src='./img/puntual.gif' title='Fer reserva puntual / Veure reserves del dia' alt='Browse' border='0'></A><small>$comentari</small>";
			$f_colour="n_enabled_day";
		elseif((strtotime("$f_tmp[year]/$f_tmp[mon]/$f_tmp[mday]")<=strtotime("$any/$mes/$i"))&&($festa==1)):
			$f_tmp= E_ADD;
			$str_funcions="&nbsp;<A class=\"m_function\"  class=\"m_function\" href=\"javascript:openpopup('$PHP_SELF?op=11&dia=$i&mes=$mes&any=$any&recurs=$recurs')\" title=\"$f_tmp\"><img align='middle' src='./img/puntual.gif' title='Fer reserva puntual / Veure reserves del dia' alt='Browse' border='0'></A><small>$comentari</small>";
			$f_colour="disabled_day";
		elseif((strtotime("$f_tmp[year]/$f_tmp[mon]/$f_tmp[mday]")<=strtotime("$any/$mes/$i"))&&($festa>=2)):
			$f_tmp= E_ADD;
			$str_funcions="&nbsp;<A class=\"m_function\"  class=\"m_function\" href=\"javascript:openpopup('$PHP_SELF?op=11&dia=$i&mes=$mes&any=$any&recurs=$recurs')\" title=\"$f_tmp\"><img align='middle' src='./img/puntual.gif' title='Fer reserva puntual / Veure reserves del dia' alt='Browse' border='0'></A>";
			$str_funcions.="&nbsp;<a href=\"javascript:openpopup('$PHP_SELF?recurs=$recurs&op=55')\"><img align='center'  title='Fer reserva peri&ograve;dica' alt='Peri&ograve;dica' src='./img/periodic.gif' border='0'></a><small>$comentari</small>";
			if ($festa==2):
				$f_colour="p_enabled_day";
			elseif($festa==3):
				$f_colour="q_enabled_day";
			endif;
		else:
			$str_funcions="&nbsp";
			if ($festa==3):
				$str_funcions="&nbsp$comentari";
				$f_colour="q_enabled_day";
			else:
				$f_colour="disabled_day";
			endif;
		endif;
		$tpl->assign("D_FUNCTIONS", "$str_funcions");
		$tpl->assign("ACTIVITIES", "$activities_tmp");
		$tpl->assign("DAYNUM", $i);
		$tpl->assign("DAYCOLOUR", $f_colour);
		$tpl->assign("N_DAYCOLOUR", "n_$f_colour");

		$tpl->parse(DAY,".day");
	endfor;

	$enllas="<a href=\"$PHP_SELF?op=30&mes=$i_mes&any=$i_any&recurs=$recurs\"><img src='./img/browse.gif' title='Vista mensual' border='0'></a>";
	$tpl->assign("WEEK_ZOOM", $enllas);
	$tpl->parse(TABLE_ROWS, ".table_row");
	$tpl->clear(DAY);
	$p_u_dia=$u_dia+7;
	$p_p_dia=$p_dia+7;
	$p_mes=$i_mes;
	$p_any=$i_any;

	if($p_p_dia>$numero_dies):
		$p_p_dia=$p_p_dia%$numero_dies;
		$p_u_dia=$p_u_dia%$numero_dies;
		$p_mes++;
		if($p_mes>12):
			$p_mes=1;
			$p_any++;
		endif;
	elseif ($p_u_dia>$numero_dies):
		$p_u_dia=$p_u_dia%$numero_dies;
	endif;

	$mes_paraula=E_MES.$i_mes;

	$f_tmp=E_SETMANA_DEL;
	switch($i_mes):
		case  1: $setmana_mes_i_any=E_SETMANA_DEL.$p_dia." ".E_MES1." ".$i_any; break;
		case  2: $setmana_mes_i_any=E_SETMANA_DEL.$p_dia." ".E_MES2." ".$i_any; break;
		case  3: $setmana_mes_i_any=E_SETMANA_DEL.$p_dia." ".E_MES3." ".$i_any; break;
		case  4: $setmana_mes_i_any=E_SETMANA_DEL.$p_dia." ".E_MES4." ".$i_any; break;
		case  5: $setmana_mes_i_any=E_SETMANA_DEL.$p_dia." ".E_MES5." ".$i_any; break;
		case  6: $setmana_mes_i_any=E_SETMANA_DEL.$p_dia." ".E_MES6." ".$i_any; break;
		case  7: $setmana_mes_i_any=E_SETMANA_DEL.$p_dia." ".E_MES7." ".$i_any; break;
		case  8: $setmana_mes_i_any=E_SETMANA_DEL.$p_dia." ".E_MES8." ".$i_any; break;
		case  9: $setmana_mes_i_any=E_SETMANA_DEL.$p_dia." ".E_MES9." ".$i_any; break;
		case 10: $setmana_mes_i_any=E_SETMANA_DEL.$p_dia." ".E_MES10." ".$i_any; break;
		case 11: $setmana_mes_i_any=E_SETMANA_DEL.$p_dia." ".E_MES11." ".$i_any; break;
		case 12: $setmana_mes_i_any=E_SETMANA_DEL.$p_dia." ".E_MES12." ".$i_any; break;
	endswitch;

	$tpl->assign("YEARMONTH", $setmana_mes_i_any);

	$endavant=sprintf("<a title='Setmana seguent' href=\"$PHP_SELF?mes=%s&any=%s&recurs=%s&op=35&p_dia=%s&u_dia=%s\"><img align='absmiddle' alt='>>' src='./img/forward.jpg' border='0'></a>",$p_mes,$p_any,$recurs,$p_p_dia,$p_u_dia);
	$a_p_dia=$p_dia-7;
	$a_u_dia=$u_dia-7;
	$a_any=$i_any;
	$a_mes=$i_mes;
	if($a_p_dia<1):
		if($i_mes==1):
			$a_mes=12;
			$a_any--;
		else:
			$a_mes--;
		endif;
		$a_numero_dies=days_in_month ( $a_mes, $a_any);
		$a_p_dia=$a_numero_dies+$a_p_dia;
		//$a_u_dia=$a_numero_dies-$a_u_dia;
	endif;

	if ($a_u_dia<1):
		$a_numero_dies=days_in_month ( $a_mes, $a_any);
		$a_u_dia=$a_numero_dies+$a_u_dia;
	endif;

	$endarrera=sprintf("<a title='Setmana anterior' href=\"$PHP_SELF?mes=%s&any=%s&recurs=%s&op=35&p_dia=%s&u_dia=%s\"><img align='absmiddle' alt='<<' src='./img/back.jpg' border='0'></a>",$a_mes,$a_any,$recurs,$a_p_dia,$a_u_dia);
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

	$tpl->parse(PAGE_CONTENT,"table");
}

?>