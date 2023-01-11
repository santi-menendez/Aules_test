<?php


///-----------------------------------------------------------------------------------
// Procediment: PintaEdificisAmbRecursos()
// Operativa  : Llista tots els edificis que tenen recursos comuns definits. Cada
//              edifici enllaca a una pagina on es llisten NOMES els recursos
//              d'aquell edifici
//-----------------------------------------------------------------------------------
function PintaEdificisAmbRecursos(){
	global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;

	$tpl->define(array(
		resources    => "$template_dir/listresources.tpl",
		classes      => "$template_dir/listclass.tpl",
		resource_row => "$template_dir/resourcerow.tpl"
	));

	// Connectem amb el servidor i seleccionem la BD corresponent
	@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysql_select_db($dbname) or die(E_ERR_SELECT_DB);
	mysql_set_charset('utf8');

	// Consultem els recursos del sistema
	$consulta="SELECT DISTINCT e.nom_edifici, e.id_edifici
					FROM `tbl_recursos` r, `tbl_edificis` e
					WHERE r.id_edifici=e.id_edifici
					ORDER BY e.nom_edifici";


	// Fem la consulta i averiguem quants resultats ens retorna
	$query=mysql_query($consulta);
	$nFiles=mysql_num_rows($query);

	$i=0;
	$llista_edificis="<ul>\n";
	while($i<$nFiles):
		$idEdifici=mysql_result($query, $i, "e.id_edifici"); // identificador de l'edifici
		$nomEdifici=mysql_result($query, $i, "e.nom_edifici"); // Nom de l'edifici
		$llista_edificis.="\t<li><a href='".$_SERVER['REQUEST_URI']."?op=45&edifici=$idEdifici' title=\"Mostra una llista de tots els recursos compartits a l'$nomEdifici\">$nomEdifici</a></li><p/>\n";
		$i++;
	endwhile;
	$llista_edificis.="</ul>\n";

	$tpl->assign("LST_CLASS", $llista_edificis);
	$tpl->assign("E_LST_RECURSOS", E_EDIFICIS_AMB_RECURSOS);
	$tpl->parse(PAGE_CONTENT, "resources");
}




//-----------------------------------------------------------------------------------
// Procediment: PintaLlistaRecursosEdifici()
// Operativa  : Donat l'identificdor d'un edifici determinat, pintem tots els
//              recursos que s'hi hagin definit
//
// Nota: La veritat es que no m'hi he matat. He agafat la funcio que pinta
//       tots els recursos classificats per edifici (tal qual) i he restringit
//       el select, de forma que, en comptes de pintar els recursos de tots els
//       edificis, nomes pinti els de l'edifici demanat...
//-----------------------------------------------------------------------------------
function PintaLlistaRecursosEdifici($idEdifici){
	global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;
	
	$tpl->define(array(
			resources    => "$template_dir/listresources.tpl",
			classes      => "$template_dir/listclass.tpl",
			resource_row => "$template_dir/resourcerow.tpl"
		));
	
	// Connectem amb el servidor i seleccionem la BD corresponent
	@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysql_select_db($dbname) or die(E_ERR_SELECT_DB);
	mysql_set_charset('utf8');

	//Averiguamos que privilegios tienes para ver que recursos puede ver
	if ($_SESSION['perfil']=='Usuari PDI' OR $_SESSION['perfil']=='Usuari PDI VAIXELL' OR $_SESSION['perfil']=='Usuari NT3') :
		//$id_tipus=1;
		$id_tipus='t.id_tipus';
	else:
		if ($_SESSION['perfil']=='CCESAII') :
			$id_tipus='t.id_tipus';
		else:
			if ($_SESSION['perfil']=='Usuari Sales') :
				$id_tipus='t.id_tipus';
			else:
				die ("No tens privilegis per accedir-hi");
			endif;
			
		endif;
	endif;

	// Consultem els recursos del sistema
	$consulta="SELECT r.id_recurs AS id,
						r.nom_recurs AS nom,
						r.descripcio AS descr,
						t.id_tipus AS id_t,
						u.nom_usuari AS resp,
						u.id_usuari AS uid,
						e.nom_edifici AS edifici,
						e.id_edifici AS id_e,
						u.email AS email,
						e.ordre AS eordre
				FROM 	tbl_recursos r,
						tbl_edificis e,
						tbl_tipus t,
						tbl_usuaris u
				WHERE e.id_edifici=$idEdifici AND
						r.id_edifici=e.id_edifici AND
						t.id_tipus=$id_tipus AND
						r.id_tipus=t.id_tipus AND
						u.id_usuari=r.id_responsable
				ORDER BY e.ordre, r.ordre";

	// Fem la consulta i averiguem quants resultats ens retorna
	$query=mysql_query($consulta);
	$nFiles=mysql_num_rows($query);
//echo $nFiles;exit;
	// Capturem la data d'avui
	$now=getdate();
	$mes=$now[mon];
	$any=$now[year];

	if($nFiles>0) $tipus_actual=$f_id_t=mysql_result($query, $i, "id_e");

	$i=0;
	while($i<$nFiles):
		$f_id=mysql_result($query, $i, "id");             // Identificador del recurs
		$f_id_edif=mysql_result($query, $i, "id_e");      // Identificador de l'edifici
		$f_descripcio=mysql_result($query, $i, "descr");  // Descripcio del recurs
		$f_nom=mysql_result($query, $i, "nom");           // Nom del recurs
		$f_resp=mysql_result($query, $i, "resp");         // Responsable del recurs

		// Amb la informacio recollida de la BD, preparem el link a la reserva puntual
		if($idEdifici==30 && ($f_id==16 || $f_id==9 || $f_id==7)):
			//Pels vaixells un altre template
			$reserva_puntual="<a href=\"$PHP_SELF?any=$any&mes=$mes&recurs=$f_id&op=31\"><img align='center' title='Veure les reserves de \"$f_nom\"' alt='Veure reserves' src='./img/browse.gif' border='0'></a>";
			$f_nom=$f_nom;
		else:
			$reserva_puntual="<a href=\"$PHP_SELF?any=$any&mes=$mes&recurs=$f_id&op=30\"><img align='center' title='Veure les reserves de \"$f_nom\"' alt='Veure reserves' src='./img/browse.gif' border='0'></a>";
			$f_nom=$f_nom;
			/*if($f_id==15):
				//Taller mecànic
				$reserva_puntual="<a href=\"$PHP_SELF?any=$any&mes=$mes&recurs=$f_id&op=32\"><img align='center' title='Veure les reserves de \"$f_nom\"' alt='Veure reserves' src='./img/browse.gif' border='0'></a>";
				$f_nom=$f_nom;
			endif;*/
		endif;
	
		//Si canviem de'edifici recordem tota la informacio anterior
		if($tipus_actual!=$f_id_edif):
			$tpl->assign("TIPUS_RECURSOS", $f_edifici);
			$tpl->parse(LST_CLASS,".classes");
			$tpl->clear(TABLE_ROWS);
			$tipus_actual=$f_id_edif;
		endif;

		$f_edifici=mysql_result($query, $i, "edifici"); // Nom de l'edifici

		$tpl->assign(array(
			"EMAIL"             => mysql_result($query, $i, "email"),
			"DESCRIPCIO_RECURS" => $f_descripcio,
			"BROWSE"            => $reserva_puntual,
			"NOM_RECURS"        => $f_nom,
			"RESPONSABLE"       => $f_resp
		));

		$tpl->parse(TABLE_ROWS, ".resource_row");

		$i++;
	endwhile;

	$tpl->assign(array(
		"E_FUNCIONS" => E_FUNCIONS,
		"E_NOM" => E_RECURS,
		"E_RESPONSABLE" => E_RESPONSABLE,
		"E_EDIFICI" => E_LOCALITZACIO,
		"TIPUS_RECURSOS" => $f_edifici
	));
	$tpl->parse(LST_CLASS, ".classes");

	$tpl->assign("E_LST_RECURSOS", E_RECURSOS_EDIFICI);
	$tpl->parse(PAGE_CONTENT, "resources");
}




//-----------------------------------------------------------------------------------
// Procediment: PintaLlistaRecursos()
// Operativa  : Pinta tots els recursos que estiguin donats d'alta en el sistema de
//              forma que apareguin "agrupats" per edificis
//-----------------------------------------------------------------------------------
function PintaLlistaRecursos(){
	global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;

	$tpl->define(array(
		resources    => "$template_dir/listresources.tpl",
		classes      => "$template_dir/listclass.tpl",
		resource_row => "$template_dir/resourcerow.tpl"
	));

	// Connectem amb el servidor i seleccionem la BD corresponent
	@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysql_select_db($dbname) or die(E_ERR_SELECT_DB);
	mysql_set_charset('utf8');

	// Consultem els recursos del sistema
	$consulta="SELECT r.id_recurs AS id,
						r.nom_recurs AS nom,
						r.descripcio AS descr,
						t.id_tipus AS id_t,
						u.nom_usuari AS resp,
						u.id_usuari AS uid,
						e.nom_edifici AS edifici,
						e.id_edifici AS id_e,
						u.email AS email,
						e.ordre AS eordre
				FROM 	tbl_recursos r,
						tbl_edificis e,
						tbl_tipus t,
						tbl_usuaris u
				WHERE r.id_edifici=e.id_edifici AND
						t.id_tipus=r.id_tipus AND
						u.id_usuari=r.id_responsable
				ORDER BY e.ordre, r.ordre";

	// Fem la consulta i averiguem quants resultats ens retorna
	$query=mysql_query($consulta);
	$nFiles=mysql_num_rows($query);

	// Capturem la data d'avui
	$now=getdate();
	$mes=$now[mon];
	$any=$now[year];

	if($nFiles>0) $tipus_actual=$f_id_t=mysql_result($query, $i, "id_e");

	$i=0;
	while($i<$nFiles):
		$f_id=mysql_result($query, $i, "id");             // Identificador del recurs
		$f_id_edif=mysql_result($query, $i, "id_e");      // Identificador de l'edifici
		$f_descripcio=mysql_result($query, $i, "descr");  // Descripcio del recurs
		$f_nom=mysql_result($query, $i, "nom");           // Nom del recurs
		$f_resp=mysql_result($query, $i, "resp");         // Responsable del recurs

		// Amb la informacio recollida de la BD, preparem el link a la reserva puntual
		if (($f_id==16)||($f_id==7)||($f_id==9)):
			//Si el recurso es el Vaixell Barcelona (recurs 16) hemos de asignarle un formulario específico para su reserva
			$reserva_puntual="<a href=\"$PHP_SELF?any=$any&mes=$mes&recurs=$f_id&op=31\"><img align='center' title='Veure les reserves de \"$f_nom\"' alt='Veure reserves' src='./img/browse.gif' border='0'></a>";
		else:
			$reserva_puntual="<a href=\"$PHP_SELF?any=$any&mes=$mes&recurs=$f_id&op=30\"><img align='center' title='Veure les reserves de \"$f_nom\"' alt='Veure reserves' src='./img/browse.gif' border='0'></a>";
		endif;
		$f_nom=$f_nom;

		//Si canviem de'edifici recordem tota la informacio anterior
		if($tipus_actual!=$f_id_edif):
			$tpl->assign("TIPUS_RECURSOS", $f_edifici);
			$tpl->parse(LST_CLASS,".classes");
			$tpl->clear(TABLE_ROWS);
			$tipus_actual=$f_id_edif;
		endif;

		$f_edifici=mysql_result($query, $i, "edifici"); // Nom de l'edifici

		$tpl->assign(array(
			"EMAIL"             => mysql_result($query, $i, "email"),
			"DESCRIPCIO_RECURS" => $f_descripcio,
			"BROWSE"            => $reserva_puntual,
			"NOM_RECURS"        => $f_nom,
			"RESPONSABLE"       => $f_resp
		));

		$tpl->parse(TABLE_ROWS, ".resource_row");

		$i++;
	endwhile;

	$tpl->assign(array(
		"E_FUNCIONS" => E_FUNCIONS,
		"E_NOM" => E_RECURS,
		"E_RESPONSABLE" => E_RESPONSABLE,
		"E_EDIFICI" => E_LOCALITZACIO,
		"TIPUS_RECURSOS" => $f_edifici
	));
	$tpl->parse(LST_CLASS, ".classes");

	$tpl->assign("E_LST_RECURSOS", E_LST_RECURSOS_PER_EDIFICIS);
	$tpl->parse(PAGE_CONTENT, "resources");
}




//------------------------------------------------------------------------------------
// Procediment: PintarMes()
// Operativa  : Donat un recurs qualsevol dels que estan donats d'alta en el sistema,
//              es pinta el calendari de reserves per al mes i any indicats
//------------------------------------------------------------------------------------
function PintarMes($mes, $any, $recurs){
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
	@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysql_select_db($dbname) or die(E_ERR_SELECT_DB);
	mysql_set_charset('utf8');

	// Consultem dades referents al recurs demanat
	$consulta="SELECT *
				FROM tbl_recursos r, tbl_edificis e
				WHERE r.id_recurs=$recurs AND
						e.id_edifici=r.id_edifici";
	$query=mysql_query($consulta);
	$f_desc_recurs=mysql_result($query,0,"r.nom_recurs");
	$f_id_responsable=mysql_result($query,0,"r.id_responsable");
	$f_edifici=mysql_result($query,0,"e.nom_edifici");

	// Consultem, per al recurs indicat, les reserves puntuals (num_dia=0) d'aquell mes
	$consulta="SELECT *
				FROM tbl_reserves
				WHERE data_reserva>='$any-$mes-01' AND
						data_reserva<='$any-$mes-$numero_dies' AND
						id_recurs=$recurs AND
						num_dia=0
				ORDER BY data_reserva,hora_inici";
	$query=mysql_query($consulta);
	$nFiles=mysql_num_rows($query);

	// Consultem, per al recurs indicat, les reserves periodiques (num_dia>0) d'aquell mes
	$consulta_periodics="SELECT *
								FROM tbl_reserves
								WHERE id_recurs=$recurs AND num_dia>0 AND
										((data_reserva<='$any/$mes/01' AND data_final>='$any/$mes/$numero_dies') OR
											(data_final>='$any/$mes/01' AND data_final<='$any/$mes/$numero_dies') OR
											(data_reserva>='$any/$mes/01' AND data_reserva<='$any/$mes/$numero_dies'))
								ORDER BY num_dia, hora_inici, data_reserva";
	$query_periodics=mysql_query($consulta_periodics);
	$nFiles_periodics=mysql_num_rows($query_periodics);

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
			$any_zoom=$any;
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
		
		//Indiquem si aquest dia en concret es festa o no
		$festa=CalculemSiEsFesta($dbserver, $dbname, $dbuser, $dbpass, $i, $mes, $any);
		//Indiquen si aquest dia es no lectiu
		$lectiu=CalculemSiEsNoLectiu($dbserver, $dbname, $dbuser, $dbpass, $i, $mes, $any);
		//Descripció del tipus de festa
		$comentari=ComentariFestius($dbserver, $dbname, $dbuser, $dbpass, $i, $mes, $any);
		// (1) Analitzem les activitats NO PERIODIQUES del mes actual
		if($nFiles>$j):
			$bd_dia=intval(substr(mysql_result($query, $j, "data_reserva"),8,2));
		endif;

		while(($nFiles>$j)&&($bd_dia==$i)):
			if($portem<$LIMIT):
				//$activities_tmp="$activities_tmp <font color=\"Navy\"><b>".substr(mysql_result($query, $j, 'hora_inici'),0,5)."-".substr(mysql_result($query, $j, 'hora_final'),0,5)."</b></font><br><a class=\"reserva_cal\" href=\"javascript:openpopup('$PHP_SELF?id_reserva=".mysql_result($query,$j,'id_reserva')."&op=80')\"><img title=\"Reservat per: ".mysql_result($query, $j, 'qui_reserva')."\nMotiu1: ".mysql_result($query, $j, 'motiu_reserva')."\nAssignatura: ".mysql_result($query, $j, 'aula')."\" align=\"right\" src=\"./img/info.gif\" border=\"0\"></a>&nbsp;<br><hr>";
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
				$n_assig=mysql_result($query_periodics, $k, 'assig');
				$link2=@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
				@mysql_select_db("pruebas-oracle",$link2) or die(E_ERR_SELECT_DB);
				$cons_prof="SELECT NUCLI_VW_PERSONES_280.cognoms as cognoms, NUCLI_VW_PERSONES_280.nom as nom
				                FROM NUCLI_VW_PERSONES_280, NUCLI_VW_PROFESSOR_UD_280 
				                WHERE NUCLI_VW_PERSONES_280.codi_persona=NUCLI_VW_PROFESSOR_UD_280.codi_persona 
                                    AND NUCLI_VW_PROFESSOR_UD_280.codi_upc_ud=\"$n_assig\" 
                                    AND NUCLI_VW_PROFESSOR_UD_280.curs=\"$curs\" AND NUCLI_VW_PROFESSOR_UD_280.quad=\"$quad\"";
				$result_cons_prof = mysql_query($cons_prof,$link2);
				$n_prof=mysql_num_rows($result_cons_prof);
				if ($row_codi_prof = mysql_fetch_array($result_cons_prof)):
					$prof="\nProfessorat curs ".$curs."-".$quad."(".$n_prof."):";
					do {
						$prof .= "\n".$row_codi_prof["cognoms"].", ".$row_codi_prof["nom"];
					} while ($row_codi_prof = mysql_fetch_array($result_cons_prof));
				else:
					//$prof=NULL;
				endif;
				$activities_tmp="$activities_tmp <font color=\"Navy\"><b>".substr(mysql_result($query_periodics, $k, 'hora_inici'),0,5)."-".substr(mysql_result($query_periodics, $k, 'hora_final'),0,5)."</b></font><br><a class=\"reserva_cal\" href=\"javascript:openpopup('$PHP_SELF?id_reserva=".mysql_result($query_periodics,$k,'id_reserva')."&op=80&dia=".$i."&mes=".$mes."&any=".$any."')\"><img title=\"Reservat per: ".mysql_result($query_periodics, $k, 'qui_reserva')."\nMotiu: ".mysql_result($query_periodics, $k, 'motiu_reserva')."\nAssignatura: ".mysql_result($query_periodics, $k, 'assig')."".$prof."\" align=\"right\" src=\"./img/info.gif\" border=\"0\"></a>&nbsp;<br><hr>";
				$portem++;
			endif;
			$j++;
			if($nFiles>$j):
				$bd_dia=intval(substr(mysql_result($query, $j, "data_reserva"),8,2));
			endif;
		endwhile;

		// (2) Analitzem les activitats PERIODIQUES del mes
		$f_timestamp=mktime(0,0,0,$mes,$i,$any);
		$timestamp=getdate($f_timestamp);
		$numero_dia=monsun($timestamp[wday])+1;
		$k=0;

		if($k<$nFiles_periodics):
			$numero_dia_bd=mysql_result($query_periodics, $k, "num_dia");
		endif;


		while(($k<$nFiles_periodics)&&($numero_dia>=$numero_dia_bd)&&($portem<$LIMIT)):

			if(($numero_dia==$numero_dia_bd)&&
			(strtotime(mysql_result($query_periodics, $k, "data_reserva"))<=$f_timestamp)&&
			(strtotime(mysql_result($query_periodics, $k, "data_final"))>=$f_timestamp)&&($festa!=1)):
				//$activities_tmp="$activities_tmp <font color=\"Navy\"><b>".substr(mysql_result($query_periodics, $k, 'hora_inici'),0,5)."-".substr(mysql_result($query_periodics, $k, 'hora_final'),0,5)."<br><i>".substr(mysql_result($query_periodics, $k, "motiu_reserva"),0,12)."</i></A><br>";
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
				$n_assig=mysql_result($query_periodics, $k, 'assig');
				$link2=@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
				@mysql_select_db("pruebas-oracle",$link2) or die(E_ERR_SELECT_DB);
				$cons_prof="SELECT NUCLI_VW_PERSONES_280.cognoms as cognoms, NUCLI_VW_PERSONES_280.nom as nom
				FROM NUCLI_VW_PERSONES_280, NUCLI_VW_PROFESSOR_UD_280 
				WHERE NUCLI_VW_PERSONES_280.codi_persona=NUCLI_VW_PROFESSOR_UD_280.codi_persona AND NUCLI_VW_PROFESSOR_UD_280.codi_upc_ud=\"$n_assig\" AND NUCLI_VW_PROFESSOR_UD_280.curs=\"$curs\" AND NUCLI_VW_PROFESSOR_UD_280.quad=\"$quad\"";
				$result_cons_prof = mysql_query($cons_prof,$link2);
				$n_prof=mysql_num_rows($result_cons_prof);
				if ($row_codi_prof = mysql_fetch_array($result_cons_prof)):
					$prof="\nProfessorat curs ".$curs."-".$quad."(".$n_prof."):";
					do {
						$prof .= "\n".$row_codi_prof["cognoms"].", ".$row_codi_prof["nom"];
					} while ($row_codi_prof = mysql_fetch_array($result_cons_prof));
				else:
					$prof=NULL;
				endif;
				$activities_tmp="$activities_tmp <font color=\"Navy\"><b>".substr(mysql_result($query_periodics, $k, 'hora_inici'),0,5)."-".substr(mysql_result($query_periodics, $k, 'hora_final'),0,5)."</b></font><br><a class=\"reserva_cal\" href=\"javascript:openpopup('$PHP_SELF?id_reserva=".mysql_result($query_periodics,$k,'id_reserva')."&op=80&dia=".$i."&mes=".$mes."&any=".$any."')\"><img title=\"Reservat per: ".mysql_result($query_periodics, $k, 'qui_reserva')."\nMotiu: ".mysql_result($query_periodics, $k, 'motiu_reserva')."\nAssignatura: ".mysql_result($query_periodics, $k, 'assig')."".$prof."\" align=\"right\" src=\"./img/info.gif\" border=\"0\"></a>&nbsp;<br><hr>";
				$portem++;
			endif;
			$k++;
			if($k<$nFiles_periodics):
				$numero_dia_bd=mysql_result($query_periodics, $k, "num_dia");
			endif;
		endwhile;

		$f_tmp=getdate();
		if((strtotime("$f_tmp[year]/$f_tmp[mon]/$f_tmp[mday]")<=strtotime("$any/$mes/$i"))&&($lectiu==1 || $lectiu==5 || $lectiu==6 || $lectiu==7 || $lectiu==8)):
			$f_tmp= E_ADD;
			if($recurs!=0) $str_funcions="&nbsp;<A class=\"m_function\"  class=\"m_function\" href=\"javascript:openpopup('$PHP_SELF?op=11&dia=$i&mes=$mes&any=$any&recurs=$recurs')\" title=\"$f_tmp\"><img align='middle' src='./img/puntual.gif' title='Fer reserva puntual / Veure reserves del dia' alt='Browse' border='0'></A>";
			//if ($_SESSION['perfil']=="CCESAII"): Para que la reserva periódica solo aparezca en los autorizados.
				//print $_SESSION['perfil'];exit;
			if($recurs!=0) $str_funcions.="&nbsp;<a href=\"javascript:openpopup('$PHP_SELF?recurs=$recurs&op=55')\"><img align='center'  title='Fer reserva peri&ograve;dica' alt='Peri&ograve;dica' src='./img/periodic.gif' border='0'></a>";
			$f_colour="enabled_day";
			if($festa==7): $f_colour="sunday_day"; endif;
			if($festa==8): $f_colour="saturday_day"; endif;
			if($festa==4): $f_colour="no_class_day"; endif;
			if($festa==5): $f_colour="a_enabled_day"; endif;
			if($festa==6): $f_colour="r_enabled_day"; endif;
			if($festa==1): $f_colour="holy_day"; endif;
			
		elseif((strtotime("$f_tmp[year]/$f_tmp[mon]/$f_tmp[mday]")<=strtotime("$any/$mes/$i"))&&($festa==0)&&($lectiu==0)):
			$f_tmp= E_ADD;
			if($recurs!=0) $str_funcions="&nbsp;<A class=\"m_function\"  class=\"m_function\" href=\"javascript:openpopup('$PHP_SELF?op=11&dia=$i&mes=$mes&any=$any&recurs=$recurs')\" title=\"$f_tmp\"><img align='middle' src='./img/puntual.gif' title='Fer reserva puntual / Veure reserves del dia' alt='Browse' border='0'></A><small>$comentari</small>";
			$f_colour="n_enabled_day";
			
		elseif((strtotime("$f_tmp[year]/$f_tmp[mon]/$f_tmp[mday]")<=strtotime("$any/$mes/$i"))&&($festa==1)):
			$f_tmp= E_ADD;
			if($recurs!=0) $str_funcions="&nbsp;<A class=\"m_function\"  class=\"m_function\" href=\"javascript:openpopup('$PHP_SELF?op=11&dia=$i&mes=$mes&any=$any&recurs=$recurs')\" title=\"$f_tmp\"><img align='middle' src='./img/puntual.gif' title='Fer reserva puntual / Veure reserves del dia' alt='Browse' border='0'></A><small>$comentari</small>";
			$f_colour="disabled_day";
		
		elseif((strtotime("$f_tmp[year]/$f_tmp[mon]/$f_tmp[mday]")<=strtotime("$any/$mes/$i"))&&($festa>=2)):
			$f_tmp= E_ADD;
			if($recurs!=0) $str_funcions="&nbsp;<A class=\"m_function\"  class=\"m_function\" href=\"javascript:openpopup('$PHP_SELF?op=11&dia=$i&mes=$mes&any=$any&recurs=$recurs')\" title=\"$f_tmp\"><img align='middle' src='./img/puntual.gif' title='Fer reserva puntual / Veure reserves del dia' alt='Browse' border='0'></A>";
			if($recurs!=0) $str_funcions.="&nbsp;<a href=\"javascript:openpopup('$PHP_SELF?recurs=$recurs&op=55')\"><img align='center'  title='Fer reserva peri&ograve;dica' alt='Peri&ograve;dica' src='./img/periodic.gif' border='0'></a><small>$comentari</small>";
			if ($festa==2):
				$f_colour="p_enabled_day";
			elseif($festa==3):
				$f_colour="q_enabled_day";
			elseif($festa==4):
				$f_tmp= E_ADD;
				if($recurs!=0) $str_funcions="&nbsp;<A class=\"m_function\"  class=\"m_function\" href=\"javascript:openpopup('$PHP_SELF?op=11&dia=$i&mes=$mes&any=$any&recurs=$recurs')\" title=\"$f_tmp\"><img align='middle' src='./img/puntual.gif' title='Fer reserva puntual / Veure reserves del dia' alt='Browse' border='0'></A><small>$comentari</small>";
				if ($_SESSION['perfil']=='CCESAII'):
					if($recurs!=0) $str_funcions="&nbsp;<A class=\"m_function\"  class=\"m_function\" href=\"javascript:openpopup('$PHP_SELF?op=11&dia=$i&mes=$mes&any=$any&recurs=$recurs')\" title=\"$f_tmp\"><img align='middle' src='./img/puntual.gif' title='Fer reserva puntual / Veure reserves del dia' alt='Browse' border='0'></A>";
					if($recurs!=0) $str_funcions.="&nbsp;<a href=\"javascript:openpopup('$PHP_SELF?recurs=$recurs&op=55')\"><img align='center'  title='Fer reserva peri&ograve;dica' alt='Peri&ograve;dica' src='./img/periodic.gif' border='0'></a><small>$comentari</small>";
				endif;
				$f_colour="n_enabled_day";
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
	$endavant=sprintf("<a title='Mes seg&uuml;ent' href=\"$PHP_SELF?mes=%s&any=%s&recurs=%s&op=30\"><img align='absmiddle' alt='>>' src='./img/forward.jpg' border='0'></a>",$p_mes,$p_any,$recurs);
	if($mes==1):
		$a_mes=12;
		$a_any=$any-1;
	else:
		$a_mes=$mes-1;
		$a_any=$any;
	endif;
	$endarrera=sprintf("<a title='Mes anterior' href=\"$PHP_SELF?mes=%s&any=%s&recurs=%s&op=30\"><img align='absmiddle' alt='<<' src='./img/back.jpg' border='0'></a>",$a_mes,$a_any,$recurs);
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


//------------------------------------------------------------------------------------
// Procediment: PintarSetmana()
// Operativa  : Donat un recurs qualsevol dels que estan donats d'alta en el sistema,
//              es pinta el calendari de reserves per al mes i any indicats
//------------------------------------------------------------------------------------
function PintarSetmana($p_dia,$u_dia,$mes,$any,$recurs){
    global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $locales, $lang, $template_dir, $p_dia, $u_dia, $mes, $any, $recurs;
    
    $i_mes=$mes;
    $i_any=$any;
    
    //Calculem el últim dia de la setmana tenint en compte el possible canvi de mes i/o any
    extract(Calcula_ultim_dia_setmanal($p_dia,$mes,$any));
    
    //LIMIT DEL CALENDARI
    $LIMIT=24;
    
    $tpl->define(array(
    //		page      => "$template_dir/page.tpl",
        table     => "$template_dir/calendar.tpl",
        table_row => "$template_dir/week.tpl",
        day       => "$template_dir/day_week.tpl"
    ));
    
    // Connectem amb el servidor i seleccionem la BD corresponent
    @mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
    @mysql_select_db($dbname) or die(E_ERR_SELECT_DB);
    mysql_set_charset('utf8');
    
    if($recurs==0){
        $consulta="SELECT * FROM tbl_recursos r,tbl_edificis e";
    }elseif($recurs==1){
        $consulta="SELECT * FROM tbl_recursos r,tbl_edificis e
				WHERE r.id_tipus=$recurs AND
						e.id_edifici=r.id_edifici";
    }else{
        $consulta="SELECT * FROM tbl_recursos r,tbl_edificis e
				WHERE r.id_recurs=$recurs AND
						e.id_edifici=r.id_edifici";
    }
    
    $query=mysql_query($consulta);
    $f_desc_recurs=mysql_result($query,0,"r.nom_recurs");
    $f_id_responsable=mysql_result($query,0,"r.id_responsable");
    $f_edifici=mysql_result($query,0,"e.nom_edifici");
    $numero_dies=days_in_month ( $mes, $any);
    
    // Ara llegim les diferents reserves que hi ha d'aquell recurs per aquella hora
    //if($recurs==0){
    //	$consulta="SELECT * FROM tbl_reserves WHERE data_reserva>=\"$any-$mes-$p_dia\"
    //		AND data_reserva<=\"$u_any-$u_mes-$u_dia\" AND num_dia=0 AND (id_recurs='18' OR id_recurs='14')
    //		ORDER BY data_reserva,hora_inici";
    //}
    
    //else{
    //	$consulta="SELECT * FROM tbl_reserves WHERE data_reserva>=\"$any-$mes-$p_dia\"
    //		AND data_reserva<=\"$u_any-$u_mes-$u_dia\" AND id_recurs=$recurs
    //		AND num_dia=0 ORDER BY data_reserva,hora_inici";
    //}
    //$query=mysql_query($consulta);
    
    //$nFiles=mysql_num_rows($query);
    
    if($recurs==0){
        $consulta_periodics="SELECT *
								FROM tbl_reserves
								WHERE ((data_reserva<=\"$any-$mes-$p_dia\" AND data_final>=\"$u_any-$u_mes-$u_dia\") OR
											(data_final>=\"$any-$mes-$p_dia\" AND data_final<=\"$u_any-$u_mes-$u_dia\") OR
											(data_reserva>=\"$any-$mes-$p_dia\" AND data_reserva<=\"$u_any-$u_mes-$u_dia\")) AND
										num_dia>=0 AND (id_recurs='18' OR id_recurs='14')
								ORDER BY num_dia, hora_inici";
    }
    elseif($recurs==1){
        $consulta_periodics="SELECT *
								FROM tbl_reserves
								WHERE ((data_reserva<=\"$any-$mes-$p_dia\" AND data_final>=\"$u_any-$u_mes-$u_dia\") OR
											(data_final>=\"$any-$mes-$p_dia\" AND data_final<=\"$u_any-$u_mes-$u_dia\") OR
											(data_reserva>=\"$any-$mes-$p_dia\" AND data_reserva<=\"$u_any-$u_mes-$u_dia\")) AND
										tipus_reserva=$recurs AND
										num_dia>=0
								ORDER BY num_dia,aula,hora_inici";
    }
    else{
        $consulta_periodics="SELECT *
								FROM tbl_reserves
								WHERE ((data_reserva<=\"$any-$mes-$p_dia\" AND data_final>=\"$u_any-$u_mes-$u_dia\") OR
											(data_final>=\"$any-$mes-$p_dia\" AND data_final<=\"$u_any-$u_mes-$u_dia\") OR
											(data_reserva>=\"$any-$mes-$p_dia\" AND data_reserva<=\"$u_any-$u_mes-$u_dia\")) AND
										id_recurs=$recurs AND
										num_dia>=0
								ORDER BY num_dia, hora_inici";
    }
    $query_periodics=mysql_query($consulta_periodics);
    $nFiles_periodics=mysql_num_rows($query_periodics);
    
    //$mes=$avui[mon];
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
    // Analitzarem les activitats NO PERIODIQUES que hi han en aquest mes concret
    if($nFiles>$j):
    $bd_dia=intval(substr(@mysql_result($query, $j, "data_reserva"),8,2));
    endif;
    
    while(($nFiles>$j)&&($bd_dia==$i)):
    if($portem<$LIMIT):
    $activities_tmp="$activities_tmp <font color=\"Navy\"><b>".substr(mysql_result($query, $j, 'hora_inici'),0,5)."-".substr(mysql_result($query, $j, 'hora_final'),0,5)."</b></font><br>".mysql_result($query, $j, 'qui_reserva')."<br><i>".mysql_result($query, $j, 'assig')."</i><br><font color=\"Red\">".mysql_result($query_periodics, $k, 'motiu_reserva')."</font><br><a class=\"reserva_cal\" href=\"javascript:openpopup('$PHP_SELF?id_reserva=".mysql_result($query,$j,'id_reserva')."&op=80')\"><img title=\"Mostra els detalls de la reserva\" align=\"right\" src=\"./img/info.gif\" border=\"0\"></a>&nbsp;<br><hr>";
    $portem++;
    endif;
    $j++;
    if($nFiles>$j):
    $bd_dia=intval(substr(mysql_result($query, $j, "data_reserva"),8,2));
    endif;
    endwhile;
    
    // Ara analitzarem les activitats PERIODIQUES que hi ha en aquest mes
    $f_femtemps=mktime(0,0,0,$mes,$i,$any);
    $femtemps=getdate($f_femtemps);
    $numero_dia=monsun($femtemps[wday])+1;
    $k=0;
    
    if($k<$nFiles_periodics):
    $numero_dia_bd=mysql_result($query_periodics, $k, "num_dia");
    endif;
    
    
    while(($k<$nFiles_periodics)&&($numero_dia>=$numero_dia_bd)&&($portem<$LIMIT)):
    
    if(($numero_dia==$numero_dia_bd)&&
        (strtotime(mysql_result($query_periodics, $k, "data_reserva"))<=$f_femtemps)&&
        (strtotime(mysql_result($query_periodics, $k, "data_final"))>=$f_femtemps)):
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
    $n_assig=mysql_result($query_periodics, $k, 'assig');
    $link2=@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
    @mysql_select_db("pruebas-oracle",$link2) or die(E_ERR_SELECT_DB);
    $cons_prof="SELECT NUCLI_VW_PERSONES_280.cognoms as cognoms, NUCLI_VW_PERSONES_280.nom as nom
				FROM NUCLI_VW_PERSONES_280, NUCLI_VW_PROFESSOR_UD_280
				WHERE NUCLI_VW_PERSONES_280.codi_persona=NUCLI_VW_PROFESSOR_UD_280.codi_persona AND NUCLI_VW_PROFESSOR_UD_280.codi_upc_ud=\"$n_assig\" AND NUCLI_VW_PROFESSOR_UD_280.curs=\"$curs\" AND NUCLI_VW_PROFESSOR_UD_280.quad=\"$quad\"";
    $result_cons_prof = mysql_query($cons_prof,$link2);
    $n_prof=mysql_num_rows($result_cons_prof);
    if ($row_codi_prof = mysql_fetch_array($result_cons_prof)):
    $prof="\nProfessorat:";
    do {
        $prof .= "\n<font face=arial size=1px>\n".$row_codi_prof["cognoms"].", ".$row_codi_prof["nom"]."</font>";
    } while ($row_codi_prof = mysql_fetch_array($result_cons_prof));
    $n_qui=NULL;
    else:
    $n_qui=mysql_result($query_periodics, $k, 'qui_reserva')."<br>";
    endif;
    $activities_tmp="$activities_tmp <font color=\"Navy\"><b>".substr(mysql_result($query_periodics, $k, 'hora_inici'),0,5)."-".substr(mysql_result($query_periodics, $k, 'hora_final'),0,5)."</b></font><br>".$n_qui."<i>".mysql_result($query_periodics, $k, 'assig')."</i><br><font color=\"Red\">".mysql_result($query_periodics, $k, 'motiu_reserva')."</font><br>".$prof."<br><a class=\"reserva_cal\" href=\"javascript:openpopup('$PHP_SELF?id_reserva=".mysql_result($query_periodics,$k,'id_reserva')."&op=80')\"><img title=\"Mostra els detalls de la reserva\" align=\"right\" src=\"./img/info.gif\" border=\"0\"></a>&nbsp;<br><hr>";
    $portem++;
    endif;
    $k++;
    if($k<$nFiles_periodics):
    $numero_dia_bd=mysql_result($query_periodics, $k, "num_dia");
    endif;
    endwhile;
    
    $f_tmp=getdate();
    if((strtotime("$f_tmp[year]/$f_tmp[mon]/$f_tmp[mday]")<=strtotime("$any/$mes/$i"))&&($lectiu==5 || $lectiu==6 || $lectiu==1)):
    $f_tmp= E_ADD;
    if($recurs!=0) $str_funcions="&nbsp;<A class=\"m_function\"  class=\"m_function\" href=\"javascript:openpopup('$PHP_SELF?op=11&dia=$i&mes=$mes&any=$any&recurs=$recurs')\" title=\"$f_tmp\"><img align='middle' src='./img/puntual.gif' title='Fer reserva puntual / Veure reserves del dia' alt='Browse' border='0'></A>";
    //if ($_SESSION['perfil']=="CCESAII"): Para que la reserva periódica solo aparezca en los autorizados.
    //print $_SESSION['perfil'];exit;
    if($recurs!=0) $str_funcions.="&nbsp;<a href=\"javascript:openpopup('$PHP_SELF?recurs=$recurs&op=55')\"><img align='center'  title='Fer reserva peri&ograve;dica' alt='Peri&ograve;dica' src='./img/periodic.gif' border='0'></a>";
    //endif;
    $f_colour="enabled_day";
    if($festa==5): $f_colour="a_enabled_day"; endif;
    if($festa==6): $f_colour="r_enabled_day"; endif;
    
    elseif((strtotime("$f_tmp[year]/$f_tmp[mon]/$f_tmp[mday]")<=strtotime("$any/$mes/$i"))&&($festa==0)&&($lectiu==0)):
    $f_tmp= E_ADD;
    if($recurs!=0) $str_funcions="&nbsp;<A class=\"m_function\"  class=\"m_function\" href=\"javascript:openpopup('$PHP_SELF?op=11&dia=$i&mes=$mes&any=$any&recurs=$recurs')\" title=\"$f_tmp\"><img align='middle' src='./img/puntual.gif' title='Fer reserva puntual / Veure reserves del dia' alt='Browse' border='0'></A><small>$comentari</small>";
    $f_colour="n_enabled_day";
    elseif((strtotime("$f_tmp[year]/$f_tmp[mon]/$f_tmp[mday]")<=strtotime("$any/$mes/$i"))&&($festa==1)):
    $f_tmp= E_ADD;
    if($recurs!=0) $str_funcions="&nbsp;<A class=\"m_function\"  class=\"m_function\" href=\"javascript:openpopup('$PHP_SELF?op=11&dia=$i&mes=$mes&any=$any&recurs=$recurs')\" title=\"$f_tmp\"><img align='middle' src='./img/puntual.gif' title='Fer reserva puntual / Veure reserves del dia' alt='Browse' border='0'></A><small>$comentari</small>";
    $f_colour="disabled_day";
    elseif((strtotime("$f_tmp[year]/$f_tmp[mon]/$f_tmp[mday]")<=strtotime("$any/$mes/$i"))&&($festa>=2)):
    $f_tmp= E_ADD;
    if($recurs!=0) $str_funcions="&nbsp;<A class=\"m_function\"  class=\"m_function\" href=\"javascript:openpopup('$PHP_SELF?op=11&dia=$i&mes=$mes&any=$any&recurs=$recurs')\" title=\"$f_tmp\"><img align='middle' src='./img/puntual.gif' title='Fer reserva puntual / Veure reserves del dia' alt='Browse' border='0'></A>";
    if($recurs!=0) $str_funcions.="&nbsp;<a href=\"javascript:openpopup('$PHP_SELF?recurs=$recurs&op=55')\"><img align='center'  title='Fer reserva peri&ograve;dica' alt='Peri&ograve;dica' src='./img/periodic.gif' border='0'></a><small>$comentari</small>";
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

//------------------------------------------------------------------------------------
// Procediment: PintarSetmanaxhores()
// Operativa  : Donat un recurs qualsevol dels que estan donats d'alta en el sistema,
//              es pinta el calendari de reserves per al mes i any indicats
//------------------------------------------------------------------------------------
function PintarSetmanaxhores($p_dia,$u_dia,$mes,$any,$recurs){
	global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $locales, $lang, $template_dir, $p_dia, $u_dia, $mes, $any, $recurs;

	$i_mes=$mes;
	$i_any=$any;
	
	//Calculem el últim dia de la setmana tenint en compte el possible canvi de mes i/o any
	extract(Calcula_ultim_dia_setmanal($p_dia,$mes,$any));

	//LIMIT DEL CALENDARI
	$LIMIT=24;

	$tpl->define(array(
		page      => "$template_dir/page.tpl",
		table     => "$template_dir/calendar.tpl",
		table_row => "$template_dir/week4week.tpl",
		day       => "$template_dir/day_week4week.tpl",
	    hour      => "$template_dir/hour_day_week.tpl"
	));
	for($i=7;$i<=21;$i++):
    	$tpl->define(array(
    	   day_h."$i"   => "$template_dir/day_week_h08.tpl",
    	));
	endfor;

	// Connectem amb el servidor i seleccionem la BD corresponent
	@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysql_select_db($dbname) or die(E_ERR_SELECT_DB);
	mysql_set_charset('utf8');

	if($recurs==0){
	$consulta="SELECT * FROM tbl_recursos r,tbl_edificis e";
	}elseif($recurs==1){
	$consulta="SELECT * FROM tbl_recursos r,tbl_edificis e
				WHERE r.id_tipus=$recurs AND
						e.id_edifici=r.id_edifici";
	}else{
	$consulta="SELECT * FROM tbl_recursos r,tbl_edificis e
				WHERE r.id_recurs=$recurs AND
						e.id_edifici=r.id_edifici";
	}

	$query=mysql_query($consulta);
	$f_desc_recurs=mysql_result($query,0,"r.nom_recurs");
	$f_id_responsable=mysql_result($query,0,"r.id_responsable");
	$f_edifici=mysql_result($query,0,"e.nom_edifici");
	$numero_dies=days_in_month ( $mes, $any);
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

		$f_tmp=getdate();
		if((strtotime("$f_tmp[year]/$f_tmp[mon]/$f_tmp[mday]")<=strtotime("$any/$mes/$i"))&&($lectiu==5 || $lectiu==6 || $lectiu==1)):
			$f_tmp= E_ADD;
			if($recurs!=0) $str_funcions="&nbsp;<A class=\"m_function\"  class=\"m_function\" href=\"javascript:openpopup('$PHP_SELF?op=11&dia=$i&mes=$mes&any=$any&recurs=$recurs')\" title=\"$f_tmp\"><img align='middle' src='./img/puntual.gif' title='Fer reserva puntual / Veure reserves del dia' alt='Browse' border='0'></A>";
			//if ($_SESSION['perfil']=="CCESAII"): Para que la reserva periódica solo aparezca en los autorizados.
				//print $_SESSION['perfil'];exit;
			if($recurs!=0) $str_funcions.="&nbsp;<a href=\"javascript:openpopup('$PHP_SELF?recurs=$recurs&op=55')\"><img align='center'  title='Fer reserva peri&ograve;dica' alt='Peri&ograve;dica' src='./img/periodic.gif' border='0'></a>";
			//endif;
			$f_colour="enabled_day";
			if($festa==5): $f_colour="a_enabled_day"; endif;
			if($festa==6): $f_colour="r_enabled_day"; endif;

		elseif((strtotime("$f_tmp[year]/$f_tmp[mon]/$f_tmp[mday]")<=strtotime("$any/$mes/$i"))&&($festa==0)&&($lectiu==0)):
			$f_tmp= E_ADD;
			if($recurs!=0) $str_funcions="&nbsp;<A class=\"m_function\"  class=\"m_function\" href=\"javascript:openpopup('$PHP_SELF?op=11&dia=$i&mes=$mes&any=$any&recurs=$recurs')\" title=\"$f_tmp\"><img align='middle' src='./img/puntual.gif' title='Fer reserva puntual / Veure reserves del dia' alt='Browse' border='0'></A><small>$comentari</small>";
			$f_colour="n_enabled_day";
		elseif((strtotime("$f_tmp[year]/$f_tmp[mon]/$f_tmp[mday]")<=strtotime("$any/$mes/$i"))&&($festa==1)):
			$f_tmp= E_ADD;
			if($recurs!=0) $str_funcions="&nbsp;<A class=\"m_function\"  class=\"m_function\" href=\"javascript:openpopup('$PHP_SELF?op=11&dia=$i&mes=$mes&any=$any&recurs=$recurs')\" title=\"$f_tmp\"><img align='middle' src='./img/puntual.gif' title='Fer reserva puntual / Veure reserves del dia' alt='Browse' border='0'></A><small>$comentari</small>";
			$f_colour="disabled_day";
		elseif((strtotime("$f_tmp[year]/$f_tmp[mon]/$f_tmp[mday]")<=strtotime("$any/$mes/$i"))&&($festa>=2)):
			$f_tmp= E_ADD;
			if($recurs!=0) $str_funcions="&nbsp;<A class=\"m_function\"  class=\"m_function\" href=\"javascript:openpopup('$PHP_SELF?op=11&dia=$i&mes=$mes&any=$any&recurs=$recurs')\" title=\"$f_tmp\"><img align='middle' src='./img/puntual.gif' title='Fer reserva puntual / Veure reserves del dia' alt='Browse' border='0'></A>";
			if($recurs!=0) $str_funcions.="&nbsp;<a href=\"javascript:openpopup('$PHP_SELF?recurs=$recurs&op=55')\"><img align='center'  title='Fer reserva peri&ograve;dica' alt='Peri&ograve;dica' src='./img/periodic.gif' border='0'></a><small>$comentari</small>";
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
		$tpl->assign("DAYNUM", $i);
		$tpl->assign("DAYCOLOUR", $f_colour);
		$tpl->assign("N_DAYCOLOUR", "n_$f_colour");

		$tpl->parse(DAY,".day");
		for($h=7;$h<=21;$h++):
		    if($mes<10) $mes_tmp = "0".$mes; else $mes_tmp = $mes;
		    if($i<10) $dia_tmp = "0".$i; else $dia_tmp = $i;
		    if($h<10) $h_tmp = "0".$h.":00:00"; else $h_tmp = $h.":00:00";
		    if($h<10) $h_tmp_30 = "0".$h.":30:00"; else $h_tmp_30 = $h.":30:00";
		    if(($h+1)<10) $h_add1_tmp = "0".($h+1).":00:00"; else $h_add1_tmp = ($h+1).":00:00";
		    if(($h+1)<10) $h_add1_tmp_30 = "0".($h+1).":30:00"; else $h_add1_tmp_30 = ($h+1).":30:00";
		    $dia_actiu = $any."-".$mes_tmp."-".$dia_tmp;
		    //Mirem si per aquest hora hi ha reserva
		    @mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
		    @mysql_select_db($dbname) or die(E_ERR_SELECT_DB);
		    $consulta_hores="SELECT * FROM tbl_reserves
								WHERE data_reserva<='".$dia_actiu."' AND data_final>='".$dia_actiu."'
                                        AND (hora_inici<='".$h_tmp."' OR hora_inici<='".$h_tmp_30."') 
                                        AND (hora_final>='".$h_add1_tmp."' OR hora_final>='".$h_tmp_30."')
										AND id_recurs='".$recurs."' AND num_dia='".($z+1)."'
								ORDER BY hora_inici";
		    $query_hores=mysql_query($consulta_hores);
		    $nFiles_hores=mysql_num_rows($query_hores);
		    
		    if ($nFiles_hores):
    		    //Mirem els professors de l'assignatura
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
    		    $activities_x_hores = "<table>";
    		    for($f=0;$f<$nFiles_hores;$f++):
    		        $assig=mysql_result($query_hores, $f, "assig");
    		        //if(substr($assig,0,6)=='280616' && $h=='11'): echo $nFiles_hores;var_dump($query_hores);exit;endif; 
        		    $link2=@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
        		    @mysql_select_db("pruebas-oracle",$link2) or die(E_ERR_SELECT_DB);
        		    $cons_prof_hora="SELECT NUCLI_VW_PERSONES_280.cognoms as cognoms, NUCLI_VW_PERSONES_280.nom as nom
                        				FROM NUCLI_VW_PERSONES_280, NUCLI_VW_PROFESSOR_UD_280
                        				WHERE NUCLI_VW_PERSONES_280.codi_persona=NUCLI_VW_PROFESSOR_UD_280.codi_persona 
                                            AND NUCLI_VW_PROFESSOR_UD_280.codi_upc_ud='".substr($assig,0,6)."' 
                                            AND NUCLI_VW_PROFESSOR_UD_280.curs='".$curs."'
                                            AND NUCLI_VW_PROFESSOR_UD_280.quad='".$quad."'";
        		    $result_cons_prof_hora = mysql_query($cons_prof_hora,$link2);
        		    $n_prof_hora=mysql_num_rows($result_cons_prof_hora);
        		    if ($row_codi_prof_hora = mysql_fetch_array($result_cons_prof_hora)):
            		    $prof_hora="\nProfessorat:";
            		    do {
            		        $prof_hora .= "\n".$row_codi_prof_hora["cognoms"].", ".$row_codi_prof_hora["nom"];
            		    } while ($row_codi_prof_hora = mysql_fetch_array($result_cons_prof_hora));
            		else:
            		    $prof_hora = NULL;
            		endif;
        		    //$abbr_img_info = "<img title=\"Reservat per: ".mysql_result($query_hores, $f, 'qui_reserva')."\nHorari: ".substr(mysql_result($query_hores, $f, 'hora_inici'),0,5)." - ".substr(mysql_result($query_hores, $f, 'hora_final'),0,5)."\nMotiu: ".mysql_result($query_hores, $f, 'motiu_reserva')."\nAssignatura: ".mysql_result($query_hores, $f, 'assig')."".$prof_hora."\" align=\"right\" src=\"./img/info.gif\" border=\"0\"><img title=\"Mostra els detalls de la reserva\" align=\"right\" src=\"./img/info.gif\" border=\"0\"></a>&nbsp;<br>";
        		    $mes_info = "<a class=\"reserva_cal\" href=\"javascript:openpopup('$PHP_SELF?id_reserva=".mysql_result($query_hores,$f,'id_reserva')."&op=80&dia=$i&mes=$mes&any=$any')\">";
        		    $assigs_x_hora = array();
        		    $activities_x_hores .= "<tr><td class='assigs_x_hores'>".$assig.$mes_info.$abbr_img_info."</td></tr>";
    		    endfor;
    		    $activities_x_hores .= "</table>";
		    else: $activities_x_hores = NULL;
		    endif;
		    if($nFiles_hores=='1') $activities_x_hores = $assig.$mes_info.$abbr_img_info;
		    $tpl->assign("ACTIVITIES_H".$h, $activities_x_hores);
		    $tpl->assign("HORA", $h);
    		$tpl->parse(DAY_H.$h,".day_h".$h);
		endfor;
		
	endfor;
	$enllas="<a href=\"$PHP_SELF?op=30&mes=$i_mes&any=$i_any&recurs=$recurs\"><img src='./img/browse.gif' title='Vista mensual' border='0'></a>";
	$tpl->assign("WEEK_ZOOM", $enllas);
	$tpl->parse(TABLE_ROWS, ".table_row");
	//$tpl->clear(DAY);
	//$tpl->clear(DAY_H08);
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

//-----------------------------------------------------------------------------------------------------------
// Procediment: FerReservaPeriodica()
// Operativa  : Prepara el formulari per tal de fer una reserva periodica
//-----------------------------------------------------------------------------------------------------------
function FerReservaPeriodica($dia, $mes, $any, $recurs, $hora_inici){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;
	
	if($recurs=='16') :
		$tpl->define(array(
			page    => "$template_dir/minipage.tpl",
			reserva => "$template_dir/reserva_periodica_vaixell.tpl"
			));
			$tpl->assign(array(
					"E_DNI_PATRO"	=>	E_DNI_PATRO,
					"E_EMBARCATS"	=>	E_EMBARCATS,
					"E_TITULACIO_PATRO"		=>	E_TITULACIO_PATRO
		));
	/*elseif($recurs=='15') :
		$tpl->define(array(
			page    => "$template_dir/minipage.tpl",
			reserva => "$template_dir/reserva_periodica_taller.tpl"
		));
		$tpl->assign(array(
					"E_RESP_ACTI"	=>	E_RESP_ACTI,
					"E_ALUM"		=>	E_ALUM,
					"E_ACTIVITAT"	=>	E_ACTIVITAT,
					"E_QUIN"		=>	E_QUIN,
					"E_MAQ"			=>	E_MAQ,
					"E_FUNG"		=>	E_FUNG,
					"E_EMBARCATS"	=>	E_EMBARCATS,
					"E_QQUANT"		=>	E_QQUANT
		));*/
	elseif($recurs=='7' || $recurs=='9'):
		$tpl->define(array(
			page    => "$template_dir/minipage.tpl",
			reserva => "$template_dir/reserva_periodica_vaixell_minina.tpl"
			));
			$tpl->assign(array(
					"E_DNI_PATRO"	=>	E_DNI_PATRO,
					"E_EMBARCATS"	=>	E_EMBARCATS_MININA,
					"E_TITULACIO_PATRO"		=>	E_TITULACIO_PATRO
		));
	else:
	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/reserva_periodica.tpl"
	));
	endif;

	// Connectem amb el servidor i seleccionem la BD corresponent
	@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysql_select_db($dbname) or die(E_ERR_SELECT_DB);
	mysql_set_charset('utf8');

	$consulta="SELECT * FROM tbl_recursos WHERE id_recurs=$recurs";

	$query=mysql_query($consulta);
	$f_desc_recurs=mysql_result($query,0,"nom_recurs");
	$f_edifici = mysql_result($query,0,"id_edifici");
	
	$tpl->assign(array(
		"DESC_RECURS"   => $f_desc_recurs,
		"E_RECURS"      =>  E_RECURS,
		"E_CHECKLIST"	=>  E_CHECKLIST,
		"E_PERIODICA"   =>  E_PERIODICA,
		"E_QUI"         =>  E_QUI,
		"USUARI"        =>  $_SESSION['nom_usuari'],
		"E_PROJECTOR"   =>  E_PROJECTOR,
		"E_HORA_INICI"  =>  E_HORA_INICI,
		"E_HORA_FINAL"  =>  E_HORA_FINAL,
		"E_DATA_INICI"  =>  E_DATA_INICI,
		"E_DATA_FINAL"  =>  E_DATA_FINAL,
		"E_M_DILLUNS"   =>  E_M_DILLUNS,
		"E_M_DIMARTS"   =>  E_M_DIMARTS,
		"E_M_DIMECRES"  =>  E_M_DIMECRES,
		"E_M_DIJOUS"    =>  E_M_DIJOUS,
		"E_M_DIVENDRES" =>  E_M_DIVENDRES,
		"E_M_DISSABTE"  =>  E_M_DISSABTE,
		"E_M_DIUMENGE"  =>  E_M_DIUMENGE,
		"E_MOTIU"       =>  E_MOTIU,
		"E_MSG_RESERVA" =>  E_MSG_RESERVA,
		"E_MSG_RESERVA_VAIXELL" =>  E_MSG_RESERVA_VAIXELL,
		"E_MSG_DATES"   =>  E_MSG_DATES,
		"E_DIA_SETMANA" =>  E_DIA_SETMANA,
		"E_RESERVA"     =>  E_RESERVA,
		"E_ASSIG"		=>	E_ASSIG,
		"E_PATRO"		=>	E_PATRO,
		
		"E_TITOL"		=>	E_TITOL,
		"E_ALTRES"		=>	E_ALTRES
	));
	//$tpl->assign(E_DIA, "Dia en format AAAA/MM/DD");
	//$tpl->assign(DATA_RESERVA, "$any/$mes/$dia");
	$f_tmp=franges_horaries($hora_minima, $hora_maxima, $hora_inici, "hora_inici");
	$tpl->assign("FRANGES_HORARIES_INICI",$f_tmp);
	$f_tmp=franges_horaries($hora_minima, $hora_maxima+1, $hora_inici+1, "hora_final");
	$tpl->assign("FRANGES_HORARIES_FINAL", $f_tmp);
	//$tpl->assign(HORA_INICI, "$hora_inici");
	//$hora_final=$hora_inici+1;
	//$tpl->assign(HORA_FINAL, "$hora_final");
	$tpl->assign("DATA_INICI", "$any/$mes/$dia");
	$tpl->assign("DATA_FINAL", "$any/$mes/$dia");
	
		// Connectem amb la BBDD per pintar les aules disponibles
	//$f_lloc=select_place2($dbserver, $dbname, $dbuser, $dbpass, $f_edifici, "lloc");
	//$tpl->assign("PLACE",$f_lloc);
	
	// Connectem amb la BBDD per pintar les persones disponibles, en cas que pertanyin al grup
	// CCFNB prodran reservar el projector pels altres usuaris
	$nom_usuari=$_SESSION['nom_usuari'];
	$consulta="SELECT * FROM tbl_usuaris WHERE nom_usuari='$nom_usuari'";

	$query=mysql_query($consulta);
	$f_perfil=@mysql_result($query,0,"perfil");
		//Permet a l'usuari sales fer reserves de la Sala d'Actes i Juntes, a l'Administrdor tot.
	if($f_perfil=='CCESAII' || ($f_perfil=='Usuari Sales')&&(($recurs=='14')||($recurs=='18')||($recurs=='1'))) {
		if($recurs=='16' || $recurs=='7' || $recurs=='9'):
			$tpl->define(array(
				page    => "$template_dir/minipage.tpl",
				reserva => "$template_dir/reserva_periodica_admin_vaixell.tpl"
			));
			$f_persona=select_who($dbserver, $dbname, $dbuser, $dbpass, "qui");
			$tpl->assign("USUARI",$f_persona);
		/*elseif($recurs=='15'):
			$tpl->define(array(
				page    => "$template_dir/minipage.tpl",
				reserva => "$template_dir/reserva_periodica_taller.tpl"
			));
			$f_persona=select_who($dbserver, $dbname, $dbuser, $dbpass, "qui");
			$tpl->assign("USUARI",$f_persona);*/
		elseif($recurs=='7' || $recurs=='9'):
			$tpl->define(array(
				page    => "$template_dir/minipage.tpl",
				reserva => "$template_dir/reserva_periodica_vaixell_minina.tpl"
			));
			$f_persona=select_who($dbserver, $dbname, $dbuser, $dbpass, "qui");
			$tpl->assign("USUARI",$f_persona);
		else:
			$tpl->define(array(
				page    => "$template_dir/minipage.tpl",
				reserva => "$template_dir/reserva_periodica_admin.tpl"
			));
			$f_persona=select_who($dbserver, $dbname, $dbuser, $dbpass, "qui");
			$tpl->assign("USUARI",$f_persona);
		endif;
	}

	$tpl->assign("RECURS", $recurs);
	/*$tpl->assign(DIA,$dia);
	$tpl->assign(MES,$mes);
	$tpl->assign(ANY,$any);*/

	$tpl->parse(PAGE_CONTENT, "reserva");
}




//-----------------------------------------------------------------------------------------------------------
// Procediment: PintarReservesDia()
// Operativa  : Mostra totes les reserves que hi ha per a un recurs i dia determinats
//-----------------------------------------------------------------------------------------------------------
function PintarReservesDia($dia, $mes, $any, $recurs){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;

	$tpl->define(array(
	 	page      => "$template_dir/minipage.tpl",
		table     => "$template_dir/oneday.tpl",
		table_row => "$template_dir/activity.tpl"
	));

	// Connectem amb el servidor i seleccionem la BD corresponent
	@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysql_select_db($dbname) or die(E_ERR_SELECT_DB);
	mysql_set_charset('utf8');

	// Consultem les reserves per a un dia determinat
	$consulta="SELECT *
				FROM tbl_recursos r,
						tbl_usuaris u,
							tbl_aules a
				WHERE r.id_recurs=$recurs AND
						u.id_usuari=r.id_responsable";
	$query=mysql_query($consulta);
	$f_desc_recurs=mysql_result($query,0,"r.nom_recurs");
	$f_id_responsable=mysql_result($query,0,"r.id_responsable");
	$f_nom_responsable=mysql_result($query,0,"u.nom_usuari");
	$f_id_aula=mysql_result($query,0,"a.aula");

	// Ara llegim les diferents reserves que hi ha d'aquell recurs per aquella hora
	$consulta="SELECT *
				FROM tbl_reserves
				WHERE data_reserva=\"$any/$mes/$dia\" AND id_recurs=$recurs AND num_dia=0
				ORDER BY hora_inici";

	$query=mysql_query($consulta);
	if($query==FALSE):
		$nFiles=0;
	else:
		$nFiles=mysql_num_rows($query);
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

	$query_periodics=mysql_query($consulta_periodics);
	$nFiles_periodics=mysql_num_rows($query_periodics);
	$id_reserva=-1;
	$j=0;

	for($i=$hora_minima;$i<$hora_maxima;$i++):
    	//Amb aquest for posem les mitjes hores.
    	for($m=0;$m<=3;$m++):
        	switch ($m) {
        	    case 0:
        	        $min = '00';
        	        break;
        	    case 1:
        	        $min = '15';
        	        break;
        	    case 2:
        	        $min = '30';
        	        break;
        	    case 3:
        	        $min = '45';
        	        break;
        	}
    		/*$min_aux=($m-1)*3;
    		$min=$min_aux."0";*/
    
    		$k=0;
    		$activities_tmp="";
    		if($k<$nFiles_periodics):
    			$numero_dia_bd=mysql_result($query_periodics, $k, "num_dia");
    		endif;
    
    		while($k<$nFiles_periodics):
    			//if((intval(substr(mysql_result($query_periodics, $k, "hora_inici"),0,2))<=$i)&&(intval(substr(mysql_result($query_periodics, $k, "hora_final"),0,2))>$i)):
    			if((mktime(substr(mysql_result($query_periodics, $k, "hora_inici"),0,2),substr(mysql_result($query_periodics, $k, "hora_inici"),3,2),0,$mes,$dia,$any)<=mktime($i,$min,0,$mes,$dia,$any)) && (mktime(substr(mysql_result($query_periodics, $k, "hora_final"),0,2),substr(mysql_result($query_periodics, $k, "hora_final"),3,2),0,$mes,$dia,$any)>mktime($i,$min,0,$mes,$dia,$any))):
    				$activities_tmp=mysql_result($query_periodics, $k, "motiu_reserva");
    				$f_qui_reserva=@mysql_result($query_periodics, $k, "qui_reserva");
    				//$activities_tmp=sprintf("%s [P]%s-%s<br><i>%s</i><br>",$activities_tmp,substr(mysql_result($query_periodics, $k, "hora_inici"),0,5),substr(mysql_result($query_periodics, $k, "hora_final"),0,5),substr(mysql_result($query_periodics, $k, "qui_reserva"),0,12));
    				$tpl->assign("WHO", mysql_result($query_periodics, $k, "qui_reserva"));
    				$tpl->assign("ASSIG", mysql_result($query_periodics, $k, "aula"));
    				$id_reserva=mysql_result($query_periodics,$k,"id_reserva");
    //**				$f_op=73;
    				$str_funcions="";
    			endif;
    			$k++;
    			if($k<$nFiles_periodics):
    				$numero_dia_bd=mysql_result($query_periodics, $k, "num_dia");
    			endif;
    		endwhile;
    
    		// Comprovem si en aquella hora hi ha alguna reserva a la base de dades
    
    		if (($nFiles>$j) && (intval(substr(mysql_result($query, $j, "hora_inici"),0,2))<=$i)):
    			$activities_tmp=mysql_result($query, $j, "motiu_reserva");
    			$id_reserva=mysql_result($query,$j, "id_reserva");
    			//$tpl->assign(ACTIVITY,)
    			$f_qui_reserva=@mysql_result($query, $j, "qui_reserva");
    			$tpl->assign("WHO", mysql_result($query, $j, "qui_reserva"));
    			$tpl->assign("ASSIG", mysql_result($query, $j, "aula"));
    //**			$f_op=74;
    			$str_funcions="";
    			//$tpl->assign(ADD,"&nbsp");
    		
    			if(intval(substr(mysql_result($query, $j, "hora_final"),0,2))==$i+1) $j++;
    		endif;
    
    		if(strcmp($activities_tmp,"")==0):
    			$activities_tmp="&nbsp";
    			$tpl->assign("WHO", "&nbsp");
    			$tpl->assign("ASSIG", "&nbsp");
    			$f_tmp=E_ADD;
    			$f_hora_inici="$i:$min";
    			$str_funcions="<a  href=\"javascript:openpopup('$PHP_SELF?hora_inici=$f_hora_inici&op=50&recurs=$recurs&dia=$dia&mes=$mes&any=$any')\" title=\"$f_tmp\"><img align='middle' src='./img/reservar.gif' alt='Reservar' border='0'></a>";
    			//reservation($dia,$mes,$any,$recurs,$hora_inici)
    		else:
    			$activities_tmp="<a href=\"javascript:openpopup('$PHP_SELF?id_reserva=$id_reserva&op=80')\">$activities_tmp</a>";
    			//Revisem que si es el responsable del recurs, te perfil d'administracio, o es el usuari que va fer la reserva, li donem permis per esborrar.
    			//Permet a l'usuari sales fer reserves de la Sala d'Actes i Juntes, a l'Administrdor tot.
    			if((strtolower($f_nom_responsable)==strtolower($_SESSION['nom_usuari']))||($_SESSION['perfil']=="CCESAII")||(($_SESSION['perfil']=='Usuari Sales')&&(($recurs=='14')||($recurs=='18')||($recurs=='1')))||(strtolower($f_qui_reserva)==strtolower($_SESSION['nom_usuari']))):
    				$f_tmp=E_DELETE;
    //**				$str_funcions="<a href=\"$PHP_SELF?hora_inici=$i&op=$f_op&recurs=$recurs&dia=$dia&mes=$mes&any=$any&id_reserva=$id_reserva\" title=\"$f_tmp\"><img align='middle' src='./img/anular.gif' alt='Anul&middot;lar' border='0'></a>";
    				$str_funcions="<a href=\"$PHP_SELF?op=73&recurs=$recurs&dia=$dia&mes=$mes&any=$any&id_reserva=$id_reserva\" title=\"$f_tmp\"><img align='middle' src='./img/anular.gif' alt='Anul&middot;lar' border='0'></a>";
    			endif;
    		endif;
    		$tpl->assign("ADD",$str_funcions);
    		$tpl->assign("ACTIVITY", $activities_tmp);
    		$iPlus=$i+1;
    		if($m==0):
    			$tpl->assign("TIME", "$i:00-$i:15");
    		elseif($m==1):
    			$tpl->assign("TIME", "$i:15-$i:30");
    	    elseif($m==2):
    			$tpl->assign("TIME", "$i:30-$i:45");
    		elseif($m==3):
    			$tpl->assign("TIME", "$i:45-$iPlus:00");
    		endif;
    		$tpl->parse(TABLE_ROWS, ".table_row");
    	//Acabem el FOR de les mitges hores
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
// Procediment: FerReservaPuntual()
// Operativa  : Permet fer una reserva puntual d'un recurs donat
//-----------------------------------------------------------------------------------------------------------
function FerReservaPuntual($dia,$mes,$any,$recurs,$hora_inici){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/reserva.tpl"
	));

	// Connectem amb el servidor i seleccionem la BD corresponent
	@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysql_select_db($dbname) or die(E_ERR_SELECT_DB);
	mysql_query("SET NAMES 'utf8'");
	$consulta="SELECT * FROM tbl_recursos WHERE id_recurs=$recurs";

	$query=mysql_query($consulta);
	$f_desc_recurs=mysql_result($query,0,"nom_recurs");
	$f_edifici = mysql_result($query,0,"id_edifici");
	$tpl->assign("DESC_RECURS",$f_desc_recurs);


	$f_tmp="$dia/$mes/$any";
	$tpl->assign(array(
		"DATA"         => $f_tmp,
		"E_DATA"       => E_DATA,
		"E_RECURS"     => E_RECURS,
		"USERNAME"     => $_SESSION['nom_usuari'],
		"E_QUI"        => E_QUI,
		"E_HORA_INICI" => E_HORA_INICI,
		"E_HORA_FINAL" => E_HORA_FINAL,
		"E_RESERVA"    => E_RESERVA,
		"E_ASSIG"      => E_ASSIG,
		"E_MOTIU"      => E_MOTIU,
		"E_ASSIG"      => E_ASSIG,
		"E_PROJECTOR"  => E_PROJECTOR
		
	));
	$f_tmp=franges_horaries($hora_minima, $hora_maxima, $hora_inici, "hora_inici");
	$tpl->assign("FRANGES_HORARIES_INICI",$f_tmp);
	
	$pos = strpos($hora_inici, ':');
	$min = substr($hora_inici, -2);
	$hora_inici = substr($hora_inici,0,$pos);
	
	$min=$min+30;
	if ($min==60){
		$hora_final = $hora_inici+1;
		$min=0;
	}
	else {
		$hora_final = $hora_inici;
	}
	$hora_final = "$hora_final:$min";
	$f_tmp=franges_horaries($hora_minima, $hora_maxima+1, $hora_final, "hora_final");
	$tpl->assign(array(
		"FRANGES_HORARIES_FINAL" => $f_tmp,
		"HORA_INICI"             => "$hora_inici"
	));
	//$hora_final=$hora_inici+1;
	
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

	$query=mysql_query($consulta);
	$f_perfil=@mysql_result($query,0,"perfil");
	//Permet a l'usuari sales fer reserves de la Sala d'Actes i Juntes, a l'Administrdor tot.
	if($f_perfil=='CCESAII' || (($_SESSION['perfil']=='Usuari Sales')&&(($recurs=='14')||($recurs=='18')||($recurs=='1')))) {
		$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/reserva_admin.tpl"
		));
		$f_persona=select_who($dbserver, $dbname, $dbuser, $dbpass, "qui");
		$tpl->assign("USERNAME",$f_persona);
	}
	
	$tpl->parse(PAGE_CONTENT, "reserva");
}









// Formularis

function DemanarNouRecurs(){
	global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $lang, $locales, $template_dir;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/demanar_recurs.tpl"
	));

	$tpl->assign("E_RECURS", "Petici&oacute; nou recurs");
	$tpl->assign("TXT_DEMANAR_RECURS", TXT_DEMANAR_RECURS);
	$tpl->assign("E_DESCRIPCIO", E_DESCRIPCIO);
	$tpl->assign("E_RESPONSABLE", E_RESPONSABLE);
	$tpl->assign("RESPONSABLE", $_SESSION['nom_usuari']);

	$tpl->assign("E_NOM_RECURS", E_NOM_RECURS);
	$tpl->assign("E_DEMANAR_RECURS", E_DEMANAR_RECURS);
	$tpl->assign("E_MSG_DEMANAR_RECURS", E_MSG_DEMANAR_RECURS);
	$tpl->assign("E_TANCAR", E_TANCAR);

	$tpl->assign("RECURS", $recurs);

	$tpl->parse(PAGE_CONTENT, "reserva");
}








function oneClassResources($dia,$mes,$any,$tipus){
	global $tpl, $hora_maxima, $hora_minima, $lang, $locales, $template_dir;

	$tpl->define(array(
		page      => "$template_dir/page.tpl",
		table     => "$template_dir/oneclassresources.tpl",
		table_row => "$template_dir/resource.tpl",
		activity => "$template_dir/activity2.tpl"
	));

	$tpl->assign("ACTIVITY", "Recurs");
	$tpl->parse(ACTIVITIES, ".activity");

	for($i=$hora_minima;$i<$hora_maxima;$i++):
		$tpl->assign("ACTIVITY", "$i:00");
		$tpl->parse(ACTIVITIES, ".activity");
	endfor;

	$tpl->parse("TABLE_ROWS", ".table_row");
	$tpl->clear(ACTIVITIES);

	for($j=0;$j<6;$j++):
		$tpl->assign("ACTIVITY", "Recurs $j");
		$tpl->parse(ACTIVITIES, ".activity");
		for($i=$hora_minima;$i<$hora_maxima;$i++):
			$tpl->assign("ACTIVITY", E_OCUPAT);
			$tpl->parse(ACTIVITIES, ".activity");
		endfor;
		$tpl->parse(TABLE_ROWS, ".table_row");
		$tpl->clear(ACTIVITIES);
	endfor;
	$tpl->parse(PAGE_CONTENT, "table");
}




//-----------------------------------------------------------------------------------------------------------
// Procediment: login()
// Operativa  : Mostra una pantalla que permet autentificar-nos un usuari registrat (administracio)
//              o com a usuari generic (nomes reserva)
// Nota: Com que ha canviat la filosofia i ara autentifiquem contra LDAP-UPC, aquesta pantalla ja
//       no té sentit de ser...
//-----------------------------------------------------------------------------------------------------------
function login(){
	global $tpl, $template_dir;

	// Gestionem les plantilles que hem d'usar
	$tpl->define(array(
		page  => "$template_dir/page_login.tpl",
		login => "$template_dir/login.tpl"
	));

	// Assignem valors a les variables de la plantilla
	$tpl->assign(array(
		"LAST_OP" => $op,
		"E_MSG_ANONYMOUS" => E_MSG_ANONYMOUS
	));

	// Pem la substitucio (parse)
	$tpl->parse("LOGIN", "login");
}




//-----------------------------------------------------------------------------------------------------------
// Procediment: Logout()
// Operativa  : Esborra totes les variables de sessio introduides i retorna a l'inici del portal
//-----------------------------------------------------------------------------------------------------------
function logout($sso){
	unset($_SESSION['r_username']);
	unset($_SESSION['r_password']);
	unset($_SESSION['r_id_usuari']);
	unset($_SESSION['r_nom_usuari']);
	unset($_SESSION['r_email']);
	unset($_SESSION['r_tipus']);
	unset($_SESSION['nom_usuari']);
	unset($_SESSION['email']);
	unset($_SESSION['departament']);
	unset($_SESSION['pac_pas']);
	unset($_SESSION['uid']);
	unset($_SESSION['perfil']);
	unset($_SESSION['dni']);
	unset($_SESSION['email']);
	unset($_SESSION['addrEmail']);
	unset($_SESSION['junta']);
	unset($_SESSION['permanent']);
	unset($_SESSION['md5pass']);
	unset($_POST['user']);
	unset($_POST['pass']);
	unset($nom_usuari);
	
	
	// Destruye todas las variables de la sesi&oacute;n
	session_unset();
	// Finalmente, destruye la sesi&oacute;n
	session_destroy();
	
	// REdireccionem a l'inici del portal...
	Header("Location: ".$sso."/logout?url=https://www.fnb.upc.edu/?q=user/login&destination=node/1183");
	die();
}



/*function chg_password(){
	global $tpl, $lang, $locales, $template_dir;

	$tpl->define(array(
		page  => "$template_dir/page_chg_password.tpl",
		login => "$template_dir/chg-password.tpl"
	));

	$tpl->assign("E_CHANGE", E_CHANGE);
	$tpl->assign("E_NEW_PASSWORD", E_NEW_PASSWORD);
	$tpl->assign("E_PASSWORD_AGAIN", E_PASSWORD_AGAIN);

	$tpl->parse(LOGIN,"login");
}*/




//-----------------------------------------------------------------------------------------------------------
// Procediment: MantenimentResponsables()
// Operativa  : Presenta una pantalla en la que es pregunta el nom i el perfil de l'usuari
//              que es vol donar d'alta.
//-----------------------------------------------------------------------------------------------------------
function MantenimentResponsables(){
	global $tpl, $template_dir;

	$tpl->define(array(
		page   => "$template_dir/minipage.tpl",
		usuari => "$template_dir/nou_usuari.tpl"
	));

	$tpl->assign("E_NOM_USUARI", E_NOM_USUARI);
	$tpl->assign("E_PERFIL", E_PERFIL);

	$tpl->parse(PAGE_CONTENT, "usuari");
}




//------------------------------------------------------------------------------------
// Procediment: AltaResponsable()
// Operativa  : Donat el nom d'usuari i el seu perfil, donem d'alta l'usuari a la BD
//              Parem una consulta a l'LDAP de la UPC per tal d'obtenir la següent
//              informacio
//                -
//                -
//                -
//                -
//------------------------------------------------------------------------------------
function AltaResponsable($username, $perfil){
	global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $template_dir, $enviar_correu;
	global $ldapServer, $ldapPort, $usuari_ldap_adm, $password_ldap_adm;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/aceptar_responsable.tpl"
	));

	// Memoritzem les variables de sessio per restaura-les despres
	$bak_nom_usuari =$_SESSION['nom_usuari'];
	$bak_email=$_SESSION['email'];
	$bak_departament=$_SESSION['departament'];
	$bak_pac_pas=$_SESSION['pac_pas'];
	$bak_uid=$_SESSION['uid'];
	$bak_perfil=$_SESSION['perfil'];


	// Fem la consulta a l'LDAP de la UPC
	// Obrim una connexio
	$ds = ldap_connect($ldapServer, $ldapPort) or die (_ERR_LDAP_CONNECT_FAILED);

	// Fem la validacio com a usuari administrador
	if ($ds):
		// binding to ldap server
		$ldapbind = ldap_bind($ds,$usuari_ldap_adm,$password_ldap_adm) or die (_ERR_LDAP_ADM_NOT_VALID);
	endif;

	//Consultem els camps que ens calen
	$searchBase = "";
	$filtre = "(&(cn=".$username.")(|(manager=PAS)(manager=PAC)(manager=PDI)))"; // Usuaris que es diguin com s'indica i que siguin PAS o PAC
	$searchResult = ldap_search($ds, $searchBase, $filtre);
	$information = ldap_get_entries($ds, $searchResult);

	// Per a que tot vagi be hi ha d'haver uun (un i nomes un) sol element.
	// Si es aixi, recollim les dades a la nostra conveniencia
	if ($information['count']==1):
		$addrEmail=strtolower($information[0]['mail'][0]);
		$username=$information[0]['cn'][0];
		$uid=$information[0]['uid'][0];
	else:
		// Si no torna resultats (o en torna mes d'un) donem missatge d'error!
		// Fem una connexio amb l'LDAP de la FNB
	$ldap_server="nereo.upc.es";
	$cn=$username;
	$ds=ldap_connect($ldap_server);
		//Verifiquem que el servidor LDAP respon
		if($ds) {
			//Comprovem que l'usuari existeix i consultem els camps que volem
			//if (!@ldap_bind($ds, $login, $password)){
				$solonecesito = array( "givenname", "description", "ou", "mail", "sn" , "fullname");
               	$ldap_bind=ldap_bind($ds,"ldap_proxy_user");
               	$ldap_result = ldap_search($ds,"ou=PAS,ou=Docent,o=UPC-FNB","commonName=$cn",$solonecesito);
				$ldap_result2 = ldap_search($ds,"ou=PAC,ou=Docent,o=UPC-FNB","commonName=$cn",$solonecesito);
				$ldap_result3 = ldap_search($ds,"ou=CC,ou=Docent,o=UPC-FNB","commonName=$cn",$solonecesito);
               	$entry_dn=ldap_first_entry($ds, $ldap_result);
				$information=ldap_get_entries($ds, $ldap_result);
					if ($entry_dn==false) {
	        	        $entry_dn=ldap_first_entry($ds, $ldap_result2);
						$information=ldap_get_entries($ds, $ldap_result2);
					}
					if ($entry_dn==false) {
	          	      $entry_dn=ldap_first_entry($ds, $ldap_result3);
					  $information=ldap_get_entries($ds, $ldap_result3);
					}
               	if($entry_dn) {
                   	$dn=ldap_get_dn($ds, $entry_dn);
					//ldap_unbind($ds);
						$addrEmail=strtolower($information[0]['mail'][0]);
						$gn_sn=array($information[0]['givenname'][0],$information[0]['sn'][0]);
						$username=implode(" ",$gn_sn);
						$uid=$cn;
				} else{
					die(_ERR_LDAP_QUERY_RESULTS);
				}
		}
	endif;

	// Hem acabat de fer consultes. Ja podem tancar la connexio
	ldap_close($ds);

echo "$addrEmail<br>";
echo "$uid<br>";
echo "$username<br>";
echo "$perfil<br>";

	// Connectem amb el servidor i seleccionem la BD corresponent
	@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysql_select_db($dbname) or die(E_ERR_SELECT_DB);
	mysql_set_charset('utf8');
	$consulta_insert="INSERT INTO tbl_usuaris (nom_usuari, uid, email, perfil)
							VALUES ('$username', '$uid', '$addrEmail', '$perfil')";

	$query=mysql_query($consulta_insert);
	if (mysql_affected_rows() == 1) :
		$resultat=0;
		$tpl->assign("E_MOTIUS", "");
	else:
		$resultat=4;
		$tpl->assign("E_MOTIUS", E_ERR_INSERT_DB);
	endif;

	if($resultat==0):
		$tpl->assign("E_MSG_RESERVA", E_MSG_RESPONSABLE_RSLT);
		/*
			Ara tocaria enviar un email al responsable d'aquest recurs, si vol rebre
			cada reserva
		*/
	else:
		$tpl->assign("E_MSG_RESERVA", E_MSG_NO_RESPONSABLE_RSLT);
   endif;
	$tpl->parse(PAGE_CONTENT,"reserva");
}



//-----------------------------------------------------------------------------------------------------------
// Procediment: ConsultaLDAP_FNB()
// Operativa  :
//-----------------------------------------------------------------------------------------------------------
function ConsultaLDAP_FNB($username){
	// Fem una connexio amb l'LDAP de la FNB
	$ldap_server="nereo.upc.es";
	$cn=$username;
	$ds=ldap_connect($ldap_server);
		//Verifiquem que el servidor LDAP respon
		if($ds) {
			//Comprovem que l'usuari existeix i consultem els camps que volem
			//if (!@ldap_bind($ds, $login, $password)){
				$solonecesito = array( "givenname", "description", "ou", "mail", "sn" , "fullname");
               	$ldap_bind=ldap_bind($ds,"ldap_proxy_user");
               	$ldap_result = ldap_search($ds,"ou=PAS,ou=Docent,o=UPC-FNB","commonName=$cn",$solonecesito);
				$ldap_result2 = ldap_search($ds,"ou=PAC,ou=Docent,o=UPC-FNB","commonName=$cn",$solonecesito);
				$ldap_result3 = ldap_search($ds,"ou=CC,ou=Docent,o=UPC-FNB","commonName=$cn",$solonecesito);
               	$entry_dn=ldap_first_entry($ds, $ldap_result);
				$information=ldap_get_entries($ds, $ldap_result);
					if ($entry_dn==false) {
	        	        $entry_dn=ldap_first_entry($ds, $ldap_result2);
						$information=ldap_get_entries($ds, $ldap_result2);
					}
					if ($entry_dn==false) {
	          	      $entry_dn=ldap_first_entry($ds, $ldap_result3);
					  $information=ldap_get_entries($ds, $ldap_result3);
					}
               	if($entry_dn) {
                   	$dn=ldap_get_dn($ds, $entry_dn);
					//ldap_unbind($ds);
						$addrEmail=strtolower($information[0]['mail'][0]);
						$gn_sn=array($information[0]['givenname'][0],$information[0]['sn'][0]);
						$nom_usuari=implode(" ",$gn_sn);
						$uid=$cn;
						// Hem acabat de fer consultes. Ja podem tancar la connexio
						ldap_close($ds);
				} else{
					die(_ERR_LDAP_QUERY_RESULTS);
				}
		}
	return $addrEmail;
	return $nom_usuari;
	return $uid;
}



//-----------------------------------------------------------------------------------------------------------
// Procediment: MantenimentRecursos()
// Operativa  :
//-----------------------------------------------------------------------------------------------------------
function MantenimentRecursos(){
	global $tpl, $template_dir;

	$tpl->define(array(
		page   => "$template_dir/minipage.tpl"
	));

	$tpl->assign(PAGE_CONTENT, "<h4>En construcci&oacute;</h4>El manternimet dels recursos encara no est&agrave; disponible.<p> De moment cal fer-ho directament sobre la Base de Dades...<p>&nbsp;</p><input type=button value='&nbsp;&nbsp;&nbsp;Tancar finestra&nbsp;&nbsp;&nbsp;' onclick='javascript:window.close()'>");
}

//------------------------------------------------------------------------------------
// Procediment: ConsultarPerfil()
// Operativa  : Donat el nom d'usuari i el seu perfil, donem d'alta l'usuari a la BD
//              Parem una consulta a l'LDAP de la UPC per tal d'obtenir la següent
//              informacio
//                -
//                -
//                -
//                -
//------------------------------------------------------------------------------------
function ConsultarPerfil($nom_usuari){
	global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $perfil;
	
	// Connectem amb el servidor i seleccionem la BD corresponent
	$conn = @mysqli_connect($dbserver,$dbuser,$dbpass) or printf(E_ERR_BD_CONNECT);
	//Seleccionem la BD corresponent
	@mysqli_select_db($conn, $dbname) or printf(E_ERR_SELECT_BD);

	$consulta="SELECT * FROM tbl_usuaris WHERE tbl_usuaris.nom_usuari='$nom_usuari' ORDER BY id_usuari DESC";
	$query=mysqli_query($conn,$consulta);
	$perfil=@mysqli_result($query,0,"perfil");
	if(!mysqli_errno()==0) {
		$perfil="Usuari PDI";
	}
	return $perfil;
}

//------------------------------------------------------------------------------------
// Procediment: ConsultarPerfilPDI()
// Operativa  : Donat el nom d'usuari i el seu perfil, donem d'alta l'usuari a la BD
//              Parem una consulta a l'LDAP de la UPC per tal d'obtenir la següent
//              informacio
//                -
//                -
//                -
//                -
//------------------------------------------------------------------------------------

function CheckPerfil($nom_usuari,$uid,$dept){
    global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $perfil;
    
    // Connectem amb el servidor i seleccionem la BD corresponent
    $conn = @mysqli_connect($dbserver,$dbuser,$dbpass) or printf(E_ERR_BD_CONNECT);
    //Seleccionem la BD corresponent
    @mysqli_select_db($conn, $dbname) or printf(E_ERR_SELECT_BD);
    $consulta="SELECT * FROM tbl_usuaris WHERE tbl_usuaris.nom_usuari='$nom_usuari' ORDER BY id_usuari DESC";
    $query=mysqli_query($conn,$consulta);
    $perfil=@mysqli_result($query,0,"perfil");
    if(!mysqli_errno()==0) {
        @mysqli_select_db($conn,"pruebas-oracle") or die(E_ERR_SELECT_DB);
        $cons_prof="SELECT * FROM NUCLI_VW_PERSONES_280, NUCLI_VW_PROFESSOR_UD_GRUP_280
				WHERE NUCLI_VW_PERSONES_280.codi_persona=NUCLI_VW_PROFESSOR_UD_GRUP_280.codi_persona 
                    AND NUCLI_VW_PERSONES_280.usuari_notes='".str_replace(' ', '.', $nom_usuari)."' ";
        $result_cons_prof = mysqli_query($conn,$cons_prof);
        $n_prof=mysqli_num_rows($result_cons_prof);
        if ($row = mysqli_fetch_array($result_cons_prof)):
            @mysqli_select_db($conn, $dbname) or printf(E_ERR_SELECT_BD);
            $i = 0;
            $id = 0;
            while($id=='0'):
                $query = "SELECT id_usuari FROM tbl_usuaris WHERE id_usuari='".$i."' ";
                $result=mysqli_query($conn,$query);
                if($row = mysqli_fetch_array($result)) $i++;
                else $id = $i++;
            endwhile;
            if(strtolower($dept)=='cen') $subdomain = 'cen'; else $subdomain = strtolower($dept).'fnb';
            $consulta="INSERT INTO tbl_usuaris (id_usuari,nom_usuari,uid,dni,email,email_fnb,perfil)
                        VALUES ('".$id."','".ucwords($row['usuari_notes'])."','".$uid."','".$row['dni']."',".$row['email'].",'".$uid."@".$subdomain.".upc.edu','Usuari PDI')";
            $query=mysqli_query($conn,$consulta);
        endif;
    }
}

//------------------------------------------------------------------------------------
// Procediment: ConsultarDNI()
// Operativa  : Donat el nom d'usuari averigüem el seu DNI
//------------------------------------------------------------------------------------
function ConsultarDNI($nom_usuari){
	global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $dni;
	
	// Connectem amb el servidor i seleccionem la BD corresponent
	$conn = @mysqli_connect($dbserver,$dbuser,$dbpass) or printf(E_ERR_BD_CONNECT);
	//Seleccionem la BD corresponent
	@mysqli_select_db($conn, $dbname) or printf(E_ERR_SELECT_BD);

	$consulta="SELECT * FROM tbl_usuaris WHERE tbl_usuaris.nom_usuari='$nom_usuari' ORDER BY id_usuari DESC";
	$query=mysqli_query($conn,$consulta);
	$dni=@mysqli_result($query,0,"dni");
return $dni;
}

//------------------------------------------------------------------------------------
// Procediment: ConsultarJuntaFacultat()
// Operativa  : Donat el nom d'usuari averigüem si pertany a la Junta de la Facultat
//------------------------------------------------------------------------------------
function ConsultarJUNTA($nom_usuari){
        global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $dni;
	// Connectem amb el servidor i seleccionem la BD corresponent
	   @mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	   @mysql_select_db($dbname) or die(E_ERR_SELECT_DB);
           $consulta="SELECT * FROM tbl_usuaris WHERE tbl_usuaris.nom_usuari='$nom_usuari'";
           $query=mysql_query($consulta);
           $junta=@mysql_result($query,0,"junta");
     return $junta;
}

//------------------------------------------------------------------------------------
// Procediment: ConsultarComissioPermanent()
// Operativa  : Donat el nom d'usuari averigüem si pertany a la Junta de la Facultat
//------------------------------------------------------------------------------------
function ConsultarCOMISSIO_PERMANENT($nom_usuari){
        global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $dni;
	// Connectem amb el servidor i seleccionem la BD corresponent
	   @mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	   @mysql_select_db($dbname) or die(E_ERR_SELECT_DB);
           $consulta="SELECT * FROM tbl_usuaris WHERE tbl_usuaris.nom_usuari='$nom_usuari'";
           $query=mysql_query($consulta);
           $permanent=@mysql_result($query,0,"permanent");
     return $permanent;
}


//-----------------------------------------------------------------------------------------------------------
// Procediment: LlistatProfessor()
// Operativa  : Prepara un llistat de les aules on el professor sel·leccionat fa classe
//-----------------------------------------------------------------------------------------------------------
function LlistatProfessor(){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/llistat_professors.tpl"
	));

	// Connectem amb el servidor i seleccionem la BD corresponent
	@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysql_select_db($dbname) or die(E_ERR_SELECT_DB);

	//$tipus_reserva=1;
	//$consulta="SELECT * FROM tbl_recursos WHERE id_tipus='$tipus_reserva'";
	

	//$query=mysql_query($consulta);
	//$f_desc_recurs=mysql_result($query,0,"nom_recurs");
	//$f_desc_recurs="Totes les aules";
	//$f_edifici = mysql_result($query,0,"id_edifici");
	//$tpl->assign("DESC_RECURS",$f_desc_recurs);

	$f_date=getdate();
	$dia0=$f_date['mday'];
	if($dia0<10):
		$dia="0".$dia0;
	else:
		$dia=$dia0;
	endif;
	$mes0=$f_date['mon'];
	if($mes0<10):
		$mes="0".$mes0;
	else:
		$mes=$mes0;
	endif;
	$any=$f_date['year'];
	$f_tmp="$dia/$mes/$any";
	$tpl->assign(array(
		"DATA"         => $f_tmp,
		"E_DATA"       => E_DATA,
		"E_RECURS"     => E_RECURS,
		"USERNAME"     => $_SESSION['nom_usuari'],
		"E_QUI"        => E_QUI,
		"E_HORA_INICI" => E_HORA_INICI,
		"E_HORA_FINAL" => E_HORA_FINAL,
		"E_LLISTAT"    => E_LLISTAT,
		"E_PROFESSOR"  => E_PROFESSOR,
		"E_ASSIG"      => E_ASSIG,
		"E_MOTIU"      => E_MOTIU,
		"E_ASSIG"      => E_ASSIG//,
//		"TIPUS_RESERVA"=> $tipus_reserva
		
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

	$query=mysql_query($consulta);
	$f_perfil=@mysql_result($query,0,"perfil");
	$f_persona=select_who($dbserver, $dbname, $dbuser, $dbpass, "qui");
	$tpl->assign("USERNAME",$f_persona);
	
	$tpl->parse(PAGE_CONTENT, "reserva");
}


//------------------------------------------------------------------------------------
// Procediment: PintarMesProfessor()
// Operativa  : Donat un professor qualsevol dels que estan donats d'alta en el sistema,
//              es pinta el calendari de reserves per aquell professor a totes les aules
//------------------------------------------------------------------------------------
function PintarMesProfessor($mes, $any, $qui, $hora_inici, $hora_final, $tipus_reserva){
	global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;

//En cas de que no seleccionem cap professor pintarem la franja horaria seleccionada
if ($qui==""):
PintarFranjaHoraria($mes, $any, $hora_inici, $hora_final, $tipus_reserva);
//Pintem el professor selecionat
else:

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
	@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysql_select_db($dbname) or die(E_ERR_SELECT_DB);

	// Consultem dades referents al recurs demanat
	$consulta="SELECT *
				FROM tbl_recursos r, tbl_edificis e
				WHERE r.id_tipus=$tipus_reserva AND
						e.id_edifici=r.id_edifici";
	
	$query=mysql_query($consulta);
	$f_desc_recurs=mysql_result($query,0,"r.nom_recurs");
	$f_id_responsable=mysql_result($query,0,"r.id_responsable");
	$f_edifici=mysql_result($query,0,"e.nom_edifici");
	$f_descripcio=mysql_result($query,0,"r.descripcio");

	// Consultem, per al recurs indicat, les reserves puntuals (num_dia=0) d'aquell mes
	$consulta="SELECT *
				FROM tbl_reserves
				WHERE data_reserva>='$any-$mes-01' AND
						data_reserva<='$any-$mes-$numero_dies' AND
						num_dia=0 AND
						qui_reserva='$qui' AND
						tipus_reserva='$tipus_reserva'
				ORDER BY data_reserva,hora_inici";
	$query=mysql_query($consulta);
	$nFiles=mysql_num_rows($query);

	// Consultem, per al recurs indicat, les reserves periodiques (num_dia>0) d'aquell mes
	$consulta_periodics="SELECT *
								FROM tbl_reserves
								WHERE num_dia>0 AND qui_reserva='$qui' AND tipus_reserva='$tipus_reserva' AND
										((data_reserva<='$any/$mes/01' AND data_final>='$any/$mes/$numero_dies') OR
											(data_final>='$any/$mes/01' AND data_final<='$any/$mes/$numero_dies') OR
											(data_reserva>='$any/$mes/01' AND data_reserva<='$any/$mes/$numero_dies'))
								ORDER BY num_dia, data_reserva, hora_inici";
	$query_periodics=mysql_query($consulta_periodics);
	$nFiles_periodics=mysql_num_rows($query_periodics);

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
			$primer_dia=days_in_month($mes-1,$any)-monsun($timestamp[wday]);
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
			$bd_dia=intval(substr(mysql_result($query, $j, "data_reserva"),8,2));
		endif;

		while(($nFiles>$j)&&($bd_dia==$i)):
			if($portem<$LIMIT):
				$activities_tmp="$activities_tmp <font color=\"Navy\"><b>".substr(mysql_result($query, $j, 'hora_inici'),0,5)."-".substr(mysql_result($query, $j, 'hora_final'),0,5)."</b></font><br><a class=\"reserva_cal\" href=\"javascript:openpopup('$PHP_SELF?id_reserva=".mysql_result($query,$j,'id_reserva')."&op=80')\"><img title=\"Reservat per: ".mysql_result($query, $j, 'qui_reserva')."\nMotiu: ".mysql_result($query, $j, 'motiu_reserva')."\nAssignatura: ".mysql_result($query, $j, 'aula')."\" align=\"right\" src=\"./img/info.gif\" border=\"0\"></a>&nbsp;<br><br>";
				$portem++;
			endif;
			$j++;
			if($nFiles>$j):
				$bd_dia=intval(substr(mysql_result($query, $j, "data_reserva"),8,2));
			endif;
		endwhile;

		// (2) Analitzem les activitats PERIODIQUES del mes
		$f_timestamp=mktime(0,0,0,$mes,$i,$any);
		$timestamp=getdate($f_timestamp);
		$numero_dia=monsun($timestamp[wday])+1;
		$k=0;

		if($k<$nFiles_periodics):
			$numero_dia_bd=mysql_result($query_periodics, $k, "num_dia");
		endif;


		while(($k<$nFiles_periodics)&&($numero_dia>=$numero_dia_bd)&&($portem<$LIMIT)):

			if(($numero_dia==$numero_dia_bd)&&
			(strtotime(mysql_result($query_periodics, $k, "data_reserva"))<=$f_timestamp)&&
			(strtotime(mysql_result($query_periodics, $k, "data_final"))>=$f_timestamp)):
				//$activities_tmp="$activities_tmp <font color=\"Navy\"><b>".substr(mysql_result($query_periodics, $k, 'hora_inici'),0,5)."-".substr(mysql_result($query_periodics, $k, 'hora_final'),0,5)."<br><i>".substr(mysql_result($query_periodics, $k, "motiu_reserva"),0,12)."</i></A><br>";
				$activities_tmp="$activities_tmp <font color=\"Navy\"><b>".substr(mysql_result($query_periodics, $k, 'hora_inici'),0,5)."-".substr(mysql_result($query_periodics, $k, 'hora_final'),0,5)."</b></font><br><font color=\"Navy\">".(mysql_result($query_periodics, $k, 'aula'))."</font><br><a class=\"reserva_cal\" href=\"javascript:openpopup('$PHP_SELF?id_reserva=".mysql_result($query_periodics,$k,'id_reserva')."&op=80')\"><img title=\"Reservat per: ".mysql_result($query_periodics, $k, 'qui_reserva')."\nMotiu: ".mysql_result($query_periodics, $k, 'motiu_reserva')."\nAssignatura: ".mysql_result($query_periodics, $j, 'assig')."\" align=\"right\" src=\"./img/info.gif\" border=\"0\"></a>&nbsp;<br><br>";
				$portem++;
			endif;
			$k++;
			if($k<$nFiles_periodics):
				$numero_dia_bd=mysql_result($query_periodics, $k, "num_dia");
			endif;
		endwhile;

		$f_tmp=getdate();
		if(strtotime("$f_tmp[year]/$f_tmp[mon]/$f_tmp[mday]")<=strtotime("$any/$mes/$i")):
			$f_tmp= E_ADD;
			$str_funcions="&nbsp;<A class=\"m_function\"  class=\"m_function\" href=\"javascript:openpopup('$PHP_SELF?op=11&dia=$i&mes=$mes&any=$any&recurs=$recurs')\" title=\"$f_tmp\"><img align='middle' src='./img/puntual.gif' title='Fer reserva puntual / Veure reserves del dia' alt='Browse' border='0'></A>";
			$str_funcions.="&nbsp;<a href=\"javascript:openpopup('$PHP_SELF?recurs=$recurs&op=55')\"><img align='center'  title='Fer reserva peri&ograve;dica' alt='Peri&ograve;dica' src='./img/periodic.gif' border='0'></a>";
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
			$tpl->assign("WEEK_ZOOM", "<img src='./img/setmanal.gif' title='Vista setmanal (No operatiu)' border='0'>");
//			$zoom_setmanal="<a href=\"$PHP_SELF?op=35&u_dia=$i&p_dia=$primer_dia&mes=$mes_zoom&any=$any_zoom&recurs=$recurs\"><img src='./img/setmanal.gif' title='Vista setmanal' border='0'></a>";
//			$tpl->assign("WEEK_ZOOM", $zoom_setmanal);
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
		$tpl->assign("WEEK_ZOOM", "<img src='./img/setmanal.gif' title='Vista setmanal (No operatiu)' border='0'>");
//		$i=7-$numero_dia;
//		$zoom_setmanal="<a href=\"$PHP_SELF?op=35&u_dia=$i&p_dia=$primer_dia&mes=$mes_zoom&any=$any_zoom&recurs=$recurs\"><img src='./img/setmanal.gif' title='Vista setmanal' border='0'></a>";
//		$tpl->assign("WEEK_ZOOM", $zoom_setmanal);
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
	$endavant=sprintf("<a title='Mes seg&uuml;ent' href=\"$PHP_SELF?mes=%s&any=%s&tipus_reserva=$tipus_reserva&qui=$qui&op=36\"><img align='absmiddle' alt='>>' src='./img/forward.jpg' border='0'></a>",$p_mes,$p_any,$recurs);
	if($mes==1):
		$a_mes=12;
		$a_any=$any-1;
	else:
		$a_mes=$mes-1;
		$a_any=$any;
	endif;
	$endarrera=sprintf("<a title='Mes anterior' href=\"$PHP_SELF?mes=%s&any=%s&tipus_reserva=$tipus_reserva&qui=$qui&op=36\"><img align='absmiddle' alt='<<' src='./img/back.jpg' border='0'></a>",$a_mes,$a_any,$recurs);
	//$f_tmp=sprintf("%s %s",$endarrera,$endavant);
	$tpl->assign("FUNC_AV_CALENDAR", $endavant);
	$tpl->assign("FUNC_EN_CALENDAR", $endarrera);
	$tpl->assign("DESC_RECURS", $qui);
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
endif;
}



//------------------------------------------------------------------------------------
// Procediment: PintarFranjaHoraria()
// Operativa  : Donada una franja horaria qualsevol, es pinta el calendari de reserves 
//				per al mes i any indicats de la ocupació de les aules.
//------------------------------------------------------------------------------------
function PintarFranjaHoraria($mes, $any, $hora_inici, $hora_final, $tipus_reserva){
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
	@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysql_select_db($dbname) or die(E_ERR_SELECT_DB);

	// Consultem dades referents al recurs demanat
	$consulta="SELECT *
				FROM tbl_recursos r, tbl_edificis e
				WHERE r.id_tipus=$tipus_reserva AND
						e.id_edifici=r.id_edifici";
	
	$query=mysql_query($consulta);
	$f_desc_recurs=mysql_result($query,0,"r.nom_recurs");
	$f_id_responsable=mysql_result($query,0,"r.id_responsable");
	$f_edifici=mysql_result($query,0,"e.nom_edifici");

	//Agafem l'hora i li posem el mateix format que té en mySQL
	$hora_inici_mysql=getTimeForMysqlDateField($hora_inici);
	$hora_final_mysql=getTimeForMysqlDateField($hora_final);

	// Consultem, per al recurs indicat, les reserves puntuals (num_dia=0) d'aquell mes
	$consulta="SELECT *
				FROM tbl_reserves
				WHERE data_reserva>='$any-$mes-01' AND
						data_reserva<='$any-$mes-$numero_dies' AND
						num_dia=0 AND
						(hora_inici<='$hora_inici_mysql' OR hora_final>='$hora_final_mysql') AND
						(hora_inici<'$hora_final_mysql' AND hora_final>'$hora_inici_mysql') AND
						tipus_reserva='$tipus_reserva'
				ORDER BY data_reserva,hora_inici";
	$query=mysql_query($consulta);
	$nFiles=mysql_num_rows($query);

	// Consultem, per al recurs indicat, les reserves periodiques (num_dia>0) d'aquell mes
	$consulta_periodics="SELECT *
								FROM tbl_reserves
								WHERE num_dia>0 AND	tipus_reserva='$tipus_reserva' AND 
										((data_reserva<='$any/$mes/01' AND data_final>='$any/$mes/$numero_dies') OR
											(data_final>='$any/$mes/01' AND data_final<='$any/$mes/$numero_dies') OR
											(data_reserva>='$any/$mes/01' AND data_reserva<='$any/$mes/$numero_dies')) AND
										(hora_inici<='$hora_inici_mysql' OR hora_final>='$hora_final_mysql') AND
										(hora_inici<'$hora_final_mysql' AND hora_final>'$hora_inici_mysql')
								ORDER BY num_dia, data_reserva, hora_inici";
	$query_periodics=mysql_query($consulta_periodics);
	$nFiles_periodics=mysql_num_rows($query_periodics);


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
			$primer_dia=days_in_month($mes-1,$any)-monsun($timestamp[wday]);
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
			$bd_dia=intval(substr(mysql_result($query, $j, "data_reserva"),8,2));
		endif;

		while(($nFiles>$j)&&($bd_dia==$i)):
			if($portem<$LIMIT):
				$activities_tmp="$activities_tmp <font color=\"Navy\"><b>".substr(mysql_result($query, $j, 'hora_inici'),0,5)."-".substr(mysql_result($query, $j, 'hora_final'),0,5)."</b></font><br><a class=\"reserva_cal\" href=\"javascript:openpopup('$PHP_SELF?id_reserva=".mysql_result($query,$j,'id_reserva')."&op=80')\"><img title=\"Reservat per: ".mysql_result($query, $j, 'qui_reserva')."\nMotiu: ".mysql_result($query, $j, 'motiu_reserva')."\nAssignatura: ".mysql_result($query, $j, 'aula')."\" align=\"right\" src=\"./img/info.gif\" border=\"0\"></a>&nbsp;<br><br>";
				$portem++;
			endif;
			$j++;
			if($nFiles>$j):
				$bd_dia=intval(substr(mysql_result($query, $j, "data_reserva"),8,2));
			endif;
		endwhile;

		// (2) Analitzem les activitats PERIODIQUES del mes
		$f_timestamp=mktime(0,0,0,$mes,$i,$any);
		$timestamp=getdate($f_timestamp);
		$numero_dia=monsun($timestamp[wday])+1;
		$k=0;

		if($k<$nFiles_periodics):
			$numero_dia_bd=mysql_result($query_periodics, $k, "num_dia");
		endif;


		while(($k<$nFiles_periodics)&&($numero_dia>=$numero_dia_bd)&&($portem<$LIMIT)):

			if(($numero_dia==$numero_dia_bd)&&
			(strtotime(mysql_result($query_periodics, $k, "data_reserva"))<=$f_timestamp)&&
			(strtotime(mysql_result($query_periodics, $k, "data_final"))>=$f_timestamp)):
				//$activities_tmp="$activities_tmp <font color=\"Navy\"><b>".substr(mysql_result($query_periodics, $k, 'hora_inici'),0,5)."-".substr(mysql_result($query_periodics, $k, 'hora_final'),0,5)."<br><i>".substr(mysql_result($query_periodics, $k, "motiu_reserva"),0,12)."</i></A><br>";
				$activities_tmp="$activities_tmp <font color=\"Navy\"><b>".substr(mysql_result($query_periodics, $k, 'hora_inici'),0,5)."-".substr(mysql_result($query_periodics, $k, 'hora_final'),0,5)."</b></font><br><font color=\"Navy\">".(mysql_result($query_periodics, $k, 'aula'))."</font><br><font color=\"Navy\">".(mysql_result($query_periodics, $k, 'qui_reserva'))."</font><br><a class=\"reserva_cal\" href=\"javascript:openpopup('$PHP_SELF?id_reserva=".mysql_result($query_periodics,$k,'id_reserva')."&op=80')\"><img title=\"Reservat per: ".mysql_result($query_periodics, $k, 'qui_reserva')."\nMotiu: ".mysql_result($query_periodics, $k, 'motiu_reserva')."\nAssignatura: ".mysql_result($query_periodics, $j, 'assig')."\" align=\"right\" src=\"./img/info.gif\" border=\"0\"></a>&nbsp;<br><br>";
				$portem++;
			endif;
			$k++;
			if($k<$nFiles_periodics):
				$numero_dia_bd=mysql_result($query_periodics, $k, "num_dia");
			endif;
		endwhile;

		$f_tmp=getdate();
		if(strtotime("$f_tmp[year]/$f_tmp[mon]/$f_tmp[mday]")<=strtotime("$any/$mes/$i")):
			$f_tmp= E_ADD;
			$str_funcions="&nbsp;<A class=\"m_function\"  class=\"m_function\" href=\"javascript:openpopup('$PHP_SELF?op=11&dia=$i&mes=$mes&any=$any&recurs=$recurs')\" title=\"$f_tmp\"><img align='middle' src='./img/puntual.gif' title='Fer reserva puntual / Veure reserves del dia' alt='Browse' border='0'></A>";
			$str_funcions.="&nbsp;<a href=\"javascript:openpopup('$PHP_SELF?recurs=$recurs&op=55')\"><img align='center'  title='Fer reserva peri&ograve;dica' alt='Peri&ograve;dica' src='./img/periodic.gif' border='0'></a>";
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
			$tpl->assign("WEEK_ZOOM", "<img src='./img/setmanal.gif' title='Vista setmanal (No operatiu)' border='0'>");
//			$zoom_setmanal="<a href=\"$PHP_SELF?op=35&u_dia=$i&p_dia=$primer_dia&mes=$mes&any=$any&recurs=$recurs\"><img src='./img/setmanal.gif' title='Vista setmanal' border='0'></a>";
//			$tpl->assign("WEEK_ZOOM", $zoom_setmanal);
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
		$tpl->assign("WEEK_ZOOM", "<img src='./img/setmanal.gif' title='Vista setmanal (No operatiu)' border='0'>");
//		$i=7-$numero_dia;
//		$zoom_setmanal="<a href=\"$PHP_SELF?op=35&u_dia=$i&p_dia=$primer_dia&mes=$mes&any=$any&recurs=$recurs\"><img src='./img/setmanal.gif' title='Vista setmanal' border='0'></a>";
//		$tpl->assign("WEEK_ZOOM", $zoom_setmanal);
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
	$endavant=sprintf("<a title='Mes seg&uuml;ent' href=\"$PHP_SELF?mes=%s&any=%s&tipus_reserva=$tipus_reserva&hora_inici=$hora_inici&hora_final=$hora_final&op=36\"><img align='absmiddle' alt='>>' src='./img/forward.jpg' border='0'></a>",$p_mes,$p_any,$recurs);
	if($mes==1):
		$a_mes=12;
		$a_any=$any-1;
	else:
		$a_mes=$mes-1;
		$a_any=$any;
	endif;
	$endarrera=sprintf("<a title='Mes anterior' href=\"$PHP_SELF?mes=%s&any=%s&tipus_reserva=$tipus_reserva&hora_inici=$hora_inici&hora_final=$hora_final&op=36\"><img align='absmiddle' alt='<<' src='./img/back.jpg' border='0'></a>",$a_mes,$a_any,$recurs);
	//$f_tmp=sprintf("%s %s",$endarrera,$endavant);
	$tpl->assign("FUNC_AV_CALENDAR", $endavant);
	$tpl->assign("FUNC_EN_CALENDAR", $endarrera);
	$tpl->assign("DESC_RECURS", "Franja Horaria de ".substr($hora_inici_mysql,0,5)."-".substr($hora_final_mysql,0,5));
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

?>