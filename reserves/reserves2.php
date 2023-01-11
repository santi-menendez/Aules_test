<?php



//-----------------------------------------------------------------------------------------------------------
// Procediment: CheckPeriodicReservation()
// Operativa  : Comprova una determinada reserva periodica per al recurs indicat
//-----------------------------------------------------------------------------------------------------------
function CheckPeriodicReservation($data_inici, $data_final, $dia, $recurs, $qui, $motiu, $hora_inici, $hora_final, $uid, $projector){
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
	elseif (($qui=="") || ($motiu=="")):
		// No han omplert el camp QUI o MOTIU
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_MOTIU_NO_VAL);
	else:
		if(($hora_inici<$hora_minim) || ($hora_inici>=$hora_final) || ($hora_final>$hora_maxima)):
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
			@mysql_connect($dbserver,$dbuser,$dbpass) or die(E_ERR_BD_CONNECT);
			//Seleccionem la BD corresponent
			@mysql_select_db($dbname) or die(E_ERR_SELECT_BD);
			$f_numdia=sunmon($dia);
			$f_data_inici="$any0/$mes0/$dia0";
			$f_data_final="$any1/$mes1/$dia1";

			/*	S'han d'agafar totes les periodiques que puguin estar entre data_inici
				i data_final, que coincideixin en que dia=num_dia. */
	
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

			$query_periodics=mysql_query($consulta_periodics);
			if(mysql_num_rows($query_periodics)>0):
				$resultat=3;
				$taula=mysql_result_table($query_periodics);
				$f_tmp=E_ERR_HI_HA_RESERVA."<br><br>".$taula;
				$tpl->assign("E_MOTIUS",$f_tmp);
				$tpl->assign("E_MSG_RESERVA", E_MSG_NO_RESERVA_RSLT);

			else:
				$resultat=0;
				
				if($projector==1):
					/*	S'han d'agafar totes les periodiques que puguin estar entre data_inici
					i data_final, que coincideixin en que dia=num_dia, per veure si es posible reservar
					un projector de forma periòdica. */
				
					// Consultar la base de dades per si es pot fer la reserva
					mysql_close();
					@mysql_connect($dbserver,$dbuser,$dbpass) or die(E_ERR_BD_CONNECT);
					//Seleccionem la BD corresponent
					@mysql_select_db('reserves') or die(E_ERR_SELECT_BD);
	
					$consulta_periodics_projector="SELECT id_recurs, qui_reserva, motiu_reserva, data_reserva, data_final, hora_inici, hora_final
									FROM tbl_reserves
									WHERE (((data_reserva<=\"$f_data_inici\")AND(data_final>\"$f_data_inici\")) OR
					((data_reserva<\"$f_data_final\")AND(data_final>=\"$f_data_final\")) OR
					((data_reserva<=\"$f_data_inici\")AND(data_final>=\"$f_data_final\"))	OR
					((data_reserva>=\"$f_data_inici\")AND(data_final<=\"$f_data_final\")) )AND
					(((hora_inici<=\"$f_hora_inici\") AND (hora_final>=\"$f_hora_inici\")) OR
					((hora_final>=\"$f_hora_final\") AND (\"$f_hora_final\">hora_inici)) OR
					((hora_inici<=\"$f_hora_inici\") AND (\"$f_hora_final\"<=hora_final)) OR
					((hora_inici>=\"$f_hora_inici\") AND (hora_final<=\"$f_hora_final\")))
					AND num_dia=$dia 
									ORDER BY hora_inici";

					$query_periodics_projector=mysql_query($consulta_periodics_projector);
					if(mysql_num_rows($query_periodics_projector)>0):
						$resultat=3;
						$taula=mysql_result_table($query_periodics_projector);
						$f_tmp=E_ERR_HI_HA_RESERVA."<br><br>".$taula;
						$tpl->assign("E_MOTIUS",$f_tmp);
						$tpl->assign("E_MSG_RESERVA", E_MSG_NO_RESERVA_RSLT);
					else:
						$resultat=0;
					endif;
				mysql_close();
				else:
				endif;
				
			endif;
		endif;
	endif;

	return $resultat;

}



//-----------------------------------------------------------------------------------------------------------
// Procediment: ValidarReserva()
// Operativa  : Arriben els paramentres d'una reserva i cal comprovar que sigui valida
//-----------------------------------------------------------------------------------------------------------
function ValidarReserva($dia,$mes,$any,$recurs,$qui,$motiu,$hora_inici,$hora_final,$assig,$uid,$projector){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir, $enviar_correu, $url_reserves;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/aceptar_reserva.tpl"
	));
	$resultat=0;

	if(!checkdate($mes,$dia,$any)):
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_DATA_NO_VAL);

	elseif(time()>mktime($hora_inici,0,0,$mes,$dia,$any)):
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_ARA_NO_VAL);
	elseif(($qui=="") || ($motiu=="")):
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_MOTIU_NO_VAL);
	elseif(($qui=="") || ($assig=="")):
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_ASSIG_NO_VAL);
	else:
		if(($hora_inici<$hora_minim)||($hora_inici>=$hora_final)||($hora_final>$hora_maxima)):
			$resultat=1;
			$tpl->assign("E_MOTIUS", E_ERR_HORA_NO_VAL);

		else:
			$f_hora_inici="$hora_inici:00:00";
			$f_hora_final="$hora_final:00:00";
			$f_data="$any/$mes/$dia";

			// Connectem amb el servidor i seleccionem la BD corresponent
			@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
			@mysql_select_db($dbname) or die(E_ERR_SELECT_DB);
			
			$consulta_aula="SELECT *
								FROM tbl_recursos
								WHERE id_recurs=$recurs";
			$query_aula = mysql_query($consulta_aula);
			$aula = mysql_result($query_aula,0,"nom_recurs");
			$f_tipus_reserva = mysql_result($query_aula,0,"id_tipus");
			
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
							
			$query=mysql_query($consulta);

			if(mysql_num_rows($query)>0):
				$resultat=2;
				//printf("$query");

				$taula=mysql_result_table($query);
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

				$query_periodics=mysql_query($consulta_periodics);
				if(mysql_num_rows($query_periodics)>0):
					$resultat=3;
					$taula=mysql_result_table($query_periodics);
					$f_tmp=E_ERR_HI_HA_RESERVA."<br><br>$taula";
					$tpl->assign("E_MOTIUS",$f_tmp);
				else:
					//S'ha demanat la reserva de un projector també
					if($projector==1):
						$lloc=$aula;
						//$f_projector=ValidarInserir_Projector($dia,$mes,$any,$qui,$motiu,$hora_inici,$hora_final,$lloc);
						extract(ValidarInserir_Projector($dia,$mes,$any,$qui,$motiu,$hora_inici,$hora_final,$lloc));
								
					endif;
					//Evaluem dos condicions: 1-No hem demanat reserva de projector i l'aula està llire, i 2- Hem demanat
					//reserva de projector i tant l'aula com el projector es poden demanar
					if(($projector==0)||(($projector==1)&&($f_projector==1))):
						// Ara ja podem inserir i recollir el resultat
						//($dia,$mes,$any,$recurs,$qui,$motiu,$hora_inici,$hora_final){
						@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
						@mysql_select_db('aules') or die(E_ERR_SELECT_DB);
						$consulta_insert="INSERT INTO tbl_reserves(id_recurs,qui_reserva,motiu_reserva,assig,aula,
								data_reserva,data_final,hora_inici,hora_final,num_dia,tipus_reserva)
								VALUES (\"$recurs\",\"$qui\",\"$motiu\",\"$assig\",\"$aula\",\"$f_data\",\"$f_data\",\"$f_hora_inici\",
								\"$f_hora_final\",\"$numero_dia\",\"$f_tipus_reserva\")";
						$query_periodics=mysql_query($consulta_insert);
						if (mysql_affected_rows() == 1) :
							$tpl->assign("E_MOTIUS","");
							//if($projector==1):
								//Inserim a id_usuari de la taula reserves el codi id_reserves de la taula aules per controlar el projector
								Inserir_id_reserva_major($dbserver, $dbname, $dbuser, $dbpass);
							//endif;
						else:
							$resultat=4;
							$tpl->assign("E_MOTIUS", E_ERR_INSERT_DB);
						endif;
					else:
						//Tots els projectors estan ocupats
						if($resultat==8):
							print($k);
							print($i);
							//$taula=mysql_result_table($query_periodics);
							$f_tmp=E_ERR_TOTS_OCUPATS."<br><br>$taula";
							$tpl->assign("E_MOTIUS",$f_tmp);
						else:
							$resultat=7;
							$f_tmp=E_ERR_HI_HA_AULA_SENSE_PROJECTOR."<br><br>$taula";
							$tpl->assign("E_MOTIUS",$f_tmp);
						endif;
					endif;
				endif;
			endif;
		endif;
	endif;

	if($resultat==0):
		// Sha fet la reserva sense problemes.
		// Ho notifiquem per pantalla i, si procedeix, enviem el mail de confirmacio...
		$tpl->assign("E_MSG_RESERVA", E_MSG_RESERVA_RSLT);

		if($enviar_correu):
			$consulta_email="SELECT re.avisar_resp as avisar,
											u.email as email,
											re.nom_recurs as recurs
									FROM tbl_recursos re,
											tbl_usuaris u
									WHERE re.id_responsable=u.id_usuari AND
											re.id_recurs='$recurs'";

			$query_email=mysql_query($consulta_email);
			if (mysql_result($query_email,0,"avisar")=="1"):
				//Si cal avisar de les reserves del recurs, enviem email al responsable
				$from="No respondre <centrecalcul@fnb.upc.edu>";
				$to=mysql_result($query_email,0,"email");
				$subject="Reservator v.1.1: Nova reserva de \"".mysql_result($query_email,0,"recurs")."\"";
				$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Si us plau, no el contesteu.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
				$body.="S'ha fet una nova reserva de \"".mysql_result($query_email,0,"recurs")."\".<br><br>&nbsp;&nbsp;&nbsp;<b>Usuari</b>: $qui.<br>&nbsp;&nbsp;&nbsp;<b>Activitat</b>: $motiu.<br>&nbsp;&nbsp;&nbsp;<b>Dia</b>: $dia/$mes/$any.<br>&nbsp;&nbsp;&nbsp;<b>Horari</b>: de $hora_inici a $hora_final.<p>";
				$body.="<p>Clicar <a href='$url_reserves?any=$any&mes=$mes&recurs=$recurs&op=30'>aqui</a> per veure el recurs.</p>";
				$signature="Centre de C&agrave;lcul FNB</font>";

				EnviarEmail($from, $to, $cc, $bcc, $subject, $body, $signature, $err_msg);
				//mail($f_mail,,"L'usuari $qui ha fet una reserva de $f_recurs pel motiu: $motiu","From: reservator@$SERVER_NAME");
			endif;
		endif;


	else:
		$tpl->assign("E_MSG_RESERVA", E_MSG_NO_RESERVA_RSLT);
	endif;

	$tpl->parse(PAGE_CONTENT,"reserva");
}


//-----------------------------------------------------------------------------------------------------------
// Procediment: Validar_Inserir_Projector()
// Operativa  : Arriben els paramentres d'una reserva i cal comprovar que sigui valida
//-----------------------------------------------------------------------------------------------------------
function ValidarInserir_Projector($dia,$mes,$any,$qui,$motiu,$hora_inici,$hora_final,$lloc){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir, $enviar_correu, $url_reserves;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/aceptar_reserva.tpl"
	));

	$resultat=0;

	$f_hora_inici="$hora_inici:00:00";
	$f_hora_final="$hora_final:00:00";
	$f_data="$any/$mes/$dia";

	// Connectem amb el servidor i seleccionem la BD corresponent
	@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysql_select_db('reserves') or die(E_ERR_SELECT_DB);
			
	// Ara llegim les diferents reserves que hi ha d'aquell recurs per aquell lloc							
	$f_lloc=$lloc;
	
	$consulta_recurs="SELECT *
					FROM tbl_recursos";
	$query_recurs=mysql_query($consulta_recurs);
	$i=mysql_num_rows($query_recurs);
	$k=0;	

	// Ara llegim les diferents reserves que hi han per aquella hora periódiques i no periòdiques incloent-hi tots 
	// els recursos

	//$taula=mysql_result_table($query);
	do {
	$id_recurs=mysql_result($query_recurs, $k, "id_recurs");
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
									num_dia=$numero_dia AND id_recurs=$id_recurs
								ORDER BY hora_inici";
	$query_periodics=mysql_query($consulta_periodics);
	if(mysql_num_rows($query_periodics)>0):
		$resultat=3;
		$taula=mysql_result_table($query_periodics);
		$f_tmp=E_ERR_HI_HA_RESERVA."<br><br>$taula";
		$tpl->assign("E_MOTIUS",$f_tmp);
	else:
		// Ara ja podem inserir i recollir el resultat
		//($dia,$mes,$any,$recurs,$qui,$motiu,$hora_inici,$hora_final){
		// Connectem amb el servidor i seleccionem la BD corresponent
		@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
		@mysql_select_db('reserves') or die(E_ERR_SELECT_DB);
		$consulta_insert="INSERT INTO tbl_reserves(id_recurs,qui_reserva,motiu_reserva,aula,
					data_reserva,data_final,hora_inici,hora_final,num_dia,id_usuari)
					VALUES (\"$id_recurs\",\"$qui\",\"$motiu\",\"$f_lloc\",\"$f_data\",\"$f_data\",\"$f_hora_inici\",
					\"$f_hora_final\",\"$numero_dia\",\"$id_reserva_major\")";
				$query_insert=mysql_query($consulta_insert);
				$resultat=0;
				if (mysql_affected_rows() == 1) :
					$tpl->assign("E_MOTIUS","");
				else:
					$resultat=4;
					$tpl->assign("E_MOTIUS", E_ERR_INSERT_DB);
				endif;
	endif;
	$k++;
	} while (($k<$i)&&(@mysql_num_rows($query_periodics)>0));


	if($resultat==0):
		// Sha fet la reserva sense problemes.
		$f_projector=1;
		$tpl->assign("E_MSG_RESERVA", E_MSG_RESERVA_RSLT);

	else:
		$f_projector=0;
		if($k==$i):
			$resultat=8;
			$f_tmp=E_ERR_TOTS_OCUPATS."<br><br>$taula";
			$tpl->assign("E_MOTIUS",$f_tmp);
			$tpl->assign("E_MSG_RESERVA", E_MSG_NO_RESERVA_RSLT);
		else:
			$tpl->assign("E_MSG_RESERVA", E_MSG_NO_RESERVA_RSLT);
		endif;
	endif;
	mysql_close();
	return compact('f_projector','resultat');
	$tpl->parse(PAGE_CONTENT,"reserva");
}



//-----------------------------------------------------------------------------------------------------------
// Procediment: MostrarInfoReserva()
// Operativa  : Arriben els paramentres d'una reserva i cal comprovar que sigui valida
//-----------------------------------------------------------------------------------------------------------
function MostrarInfoReserva($id_reserva){
	global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/info_reserva.tpl"
	));
	$resultat=0;

	// Connectem amb el servidor i seleccionem la BD corresponent
	@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysql_select_db($dbname) or die(E_ERR_SELECT_DB);

	//Comprovem que realment pot eliminar aquesta reserva

	//$consulta="SELECT * FROM tbl_reserves WHERE id_responsable=\"$_SESSION['r_id_usuari']\"
	//	AND id_recurs=\"$recurs\"";

	$consulta="SELECT r.nom_recurs AS recurs,
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
							re.assig AS assig
					FROM  tbl_recursos r,
							tbl_reserves re,
							tbl_usuaris u
					WHERE r.id_recurs=re.id_recurs AND
							r.id_responsable=u.id_usuari AND
							re.id_reserva=$id_reserva";

	$query=mysql_query($consulta);

	// Si la consulta retorna resultats...
	if($query!=NULL):
		$tpl->assign(array(
			"E_MSG_INF_RESERVA"    => E_MSG_INF_RESERVA,
			"E_DATA_INICI"         => E_DATA_INICI,
			"E_DATA_FINAL"         => E_DATA_FINAL,
			"E_HORA_INICI"         => E_HORA_INICI,
			"E_HORA_FINAL"         => E_HORA_FINAL,
			"E_PERIODICA"          => E_PERIODICA,
			"E_QUI"                => E_QUI,
			"E_MOTIU"              => E_MOTIU,
			"E_RESPONSABLE_RECURS" => E_RESPONSABLE_RECURS,
			"E_ASSIG"			   => E_ASSIG,
			"DESC_RECURS"          => mysql_result($query,0,"recurs")
		));

		switch(mysql_result($query,0,"dia")):
			case 0: $dia=E_DIA0; break;
			case 1: $dia=E_DIA1; break;
			case 2: $dia=E_DIA2; break;
			case 3: $dia=E_DIA3; break;
			case 4: $dia=E_DIA4; break;
			case 5: $dia=E_DIA5; break;
			case 6: $dia=E_DIA6; break;
			case 7: $dia=E_DIA7; break;
		endswitch;
		$data_ini=strtotime(mysql_result($query,0,"data_inici"));
		$data_fi=strtotime(mysql_result($query,0,"data_final"));

		$tpl->assign(array(
			"DATA_RESERVA" => date("d/m/Y",$data_ini),
			"DATA_FINAL" => date("d/m/Y", $data_fi),
			"HORA_INICI" => mysql_result($query,0,"hora_inici"),
			"HORA_FINAL" => mysql_result($query,0,"hora_final"),
			"QUI" => mysql_result($query,0,"qui"),
			"EMAIL" => mysql_result($query,0,"email"),
			"RESPONSABLE" => mysql_result($query,0,"respon"),
			"MOTIU" => mysql_result($query,0,"motiu"),
			"DIA" => $dia,
			"ASSIG" => mysql_result($query,0,"assig")
		));
	endif;
	$tpl->parse(PAGE_CONTENT,"reserva");
}


// Funcions de reserves



//-----------------------------------------------------------------------------------------------------------
// Procediment: error_periodic_reservation()
// Operativa  : Indica si la reserva es pot realitzar. Verifiquem que el recurs no està ocupat.
//-----------------------------------------------------------------------------------------------------------
function error_periodic_reservation(){
	global $tpl, $template_dir;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/aceptar_reserva.tpl"
	));
	$tpl->assign("E_MSG_RESERVA", E_MSG_NO_RESERVA_RSLT);
	$tpl->assign("E_MOTIUS", E_ERR_MARCA_UN_DIA);
	$tpl->parse("PAGE_CONTENT", "reserva");
}


//-----------------------------------------------------------------------------------------------------------
// Procediment: accept_periodic_reservation()
// Operativa  : Inserta les dates de les reserves
//-----------------------------------------------------------------------------------------------------------
function accept_periodic_reservation($data_inici,$data_final,$dia,$recurs,$qui,$motiu,$hora_inici,$hora_final,$assig,$uid,$projector){
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
        					reserva => "$template_dir/aceptar_reserva.tpl") );
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
			if(($hora_inici<$hora_minim)||($hora_inici>=$hora_final)||($hora_final>$hora_maxima)):
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
				@mysql_connect($dbserver,$dbuser,$dbpass) or printf(E_ERR_BD_CONNECT);

				//Seleccionem la BD corresponent
				@mysql_select_db($dbname) or printf(E_ERR_SELECT_BD);
				$f_numdia=sunmon($dia);
				$f_data_inici=sprintf("%s/%s/%s",$any0,$mes0,$dia0);
				$f_data_final=sprintf("%s/%s/%s",$any1,$mes1,$dia1);
				//echo $f_data_inici;
				//echo $f_data_final;
				
				// Ara averigüen quina aula es en funció de la variable recurs							
				$consulta_aula="SELECT *
								FROM tbl_recursos
								WHERE id_recurs=$recurs";
				$query_aula=mysql_query($consulta_aula);
				$aula=mysql_result($query_aula,0,"nom_recurs");
				$f_tipus_reserva=mysql_result($query_aula,0,"id_tipus");
			
        		$consulta_periodics="SELECT id_recurs, qui_reserva, motiu_reserva, data_reserva, data_final,
							 hora_inici, hora_final 
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

				$query_periodics=mysql_query($consulta_periodics);
				if(mysql_num_rows($query_periodics)>0):
					$resultat=3;
					//$taula=mysql_result_table($query_periodics);
	    				$f_tmp=sprintf("%s<br><br> %s",E_ERR_HI_HA_RESERVA,$taula);
	    				$tpl->assign("E_MOTIUS",$f_tmp);
				else:
					//S'ha demanat la reserva de un projector també
					if($projector==1):
						$lloc=$aula;
						//$f_projector=ValidarInserir_Projector($dia,$mes,$any,$qui,$motiu,$hora_inici,$hora_final,$lloc);
						extract(ValidarInserir_Projector_Periodic($data_inici,$data_final,$dia,$qui,$motiu,$hora_inici,$hora_final,$lloc));
								
					endif;
					//Evaluem dos condicions: 1-No hem demanat reserva de projector i l'aula està llire, i 2- Hem demanat
					//reserva de projector i tant l'aula com el projector es poden demanar
					if(($projector==0)||(($projector==1)&&($f_projector==1))):
						// Ara ja podem inserir i recollir el resultat
						//($dia,$mes,$any,$recurs,$qui,$motiu,$hora_inici,$hora_final){
						@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
						@mysql_select_db('aules') or die(E_ERR_SELECT_DB);
						// Ara ja podem inserir i recollir el resultat
						//($dia,$mes,$any,$recurs,$qui,$motiu,$hora_inici,$hora_final){
						$consulta_insert="INSERT INTO tbl_reserves(id_recurs,qui_reserva,motiu_reserva,assig,aula,
							data_reserva,data_final,hora_inici,hora_final,num_dia,tipus_reserva)
							VALUES (\"$recurs\",\"$qui\",\"$motiu\",\"$assig\",\"$aula\",\"$f_data_inici\",\"$f_data_final\",\"$f_hora_inici\",
							\"$f_hora_final\",\"$dia\",\"$f_tipus_reserva\")";
						$query_periodics=mysql_query($consulta_insert);
						if (mysql_affected_rows() == 1):
	       					$tpl->assign("E_MOTIUS","");
							Inserir_id_reserva_major($dbserver, $dbname, $dbuser, $dbpass);
		     			else:
	       					$resultat=4;
	       					$tpl->assign("E_MOTIUS", E_ERR_INSERT_DB);
	    	 			endif;
	        		endif;
				endif;
			endif;
//		}

	};

	if($resultat==0):
		// S'ha fet la reserva sense problemes.
		// Ho notifiquem per pantalla i, si procedeix, enviem el mail de confirmacio...
		$tpl->assign("E_MSG_RESERVA", E_MSG_RESERVA_RSLT);

		if($enviar_correu):
			$consulta_email="SELECT re.avisar_resp as avisar,
											u.email as email,
											re.nom_recurs as recurs
									FROM tbl_recursos re,
											tbl_usuaris u
									WHERE re.id_responsable=u.id_usuari AND
											re.id_recurs='$recurs'";

			$query_email=mysql_query($consulta_email);
			if (mysql_result($query_email,0,"avisar")=="1"):
				//Si cal avisar de les reserves del recurs, enviem email al responsable
				$from="No respondre <centrecalcul@fnb.upc.edu>";
				$to=mysql_result($query_email,0,"email");
				$subject="Reservator v.1.1: Nova reserva peri&ograve;dica de \"".mysql_result($query_email,0,"recurs")."\"";
				$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Si us plau, no el contesteu.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
				$body.="S'ha fet una nova reserva de \"".mysql_result($query_email,0,"recurs")."\".<br><br>&nbsp;&nbsp;&nbsp;<b>Usuari</b>: $qui.<br>&nbsp;&nbsp;&nbsp;<b>Activitat</b>: $motiu.<br>&nbsp;&nbsp;&nbsp;<b>Data</b>: del $data_inici al $data_final.<br>&nbsp;&nbsp;&nbsp;<b>Horari</b>: de $hora_inici a $hora_final.<p>";
				$body.="<p>Clicar <a href='./index.php?any=$any&mes=$mes&recurs=$recurs&op=30'>aqui</a> per veure el recurs.</p>";
				$signature="Centre de C&agrave;lcul FNB</font>";

				EnviarEmail($from, $to, $cc, $bcc, $subject, $body, $signature, $err_msg);
				//mail($f_mail,,"L'usuari $qui ha fet una reserva de $f_recurs pel motiu: $motiu","From: reservator@$SERVER_NAME");
			endif;
		endif;
	endif;


	$tpl->parse(PAGE_CONTENT,"reserva");
}

//-----------------------------------------------------------------------------------------------------------
// Procediment: Validar_Inserir_Projector_Periodic()
// Operativa  : Arriben els paramentres d'una reserva i cal comprovar que sigui valida
//-----------------------------------------------------------------------------------------------------------
function ValidarInserir_Projector_Periodic($data_inici,$data_final,$dia,$qui,$motiu,$hora_inici,$hora_final,$lloc){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir, $enviar_correu, $url_reserves;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/aceptar_reserva.tpl"
	));

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
	}
	else if((strlen(trim($qui))==0)||(strlen(trim($motiu))==0)){
		$resultat=5;
	    $tpl->assign("E_MOTIUS", E_ERR_MOTIU_NO_VAL);
	}
	else{
		if(($hora_inici<$hora_minim)||($hora_inici>=$hora_final)||($hora_final>$hora_maxima)):
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
			// Connectem amb el servidor i seleccionem la BD corresponent
			@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
			@mysql_select_db('reserves') or die(E_ERR_SELECT_DB);
			
			// Ara llegim les diferents reserves que hi ha d'aquell recurs per aquell lloc							
			$f_lloc=$lloc;
			$f_numdia=sunmon($dia);
			$f_data_inici=sprintf("%s/%s/%s",$any0,$mes0,$dia0);
			$f_data_final=sprintf("%s/%s/%s",$any1,$mes1,$dia1);
	
			$consulta_recurs="SELECT *
					FROM tbl_recursos";
			$query_recurs=mysql_query($consulta_recurs);
			$i=mysql_num_rows($query_recurs);
			$k=0;	

			// Ara llegim les diferents reserves que hi han per aquella hora periódiques i no periòdiques incloent-hi tots 
			// els recursos
	
			//$taula=mysql_result_table($query);
			do {
			$id_recurs=mysql_result($query_recurs, $k, "id_recurs");
			
			$consulta_periodics="SELECT id_recurs, qui_reserva, motiu_reserva, data_reserva, data_final,
							 hora_inici, hora_final 
					FROM tbl_reserves
					WHERE (((data_reserva<=\"$f_data_inici\")AND(data_final>\"$f_data_inici\")) OR
							((data_reserva<\"$f_data_final\")AND(data_final>=\"$f_data_final\")) OR
							((data_reserva<=\"$f_data_inici\")AND(data_final>=\"$f_data_final\")) OR
							((data_reserva>=\"$f_data_inici\")AND(data_final<=\"$f_data_final\"))) AND
							(((hora_inici<=\"$f_hora_inici\") AND (hora_final>\"$f_hora_inici\")) OR
							((hora_final>=\"$f_hora_final\") AND (\"$f_hora_final\">hora_inici)) OR
							((hora_inici<=\"$f_hora_inici\") AND (\"$f_hora_final\"<=hora_final)) OR
							((hora_inici>=\"$f_hora_inici\") AND (hora_final<=\"$f_hora_final\")))
					 		AND num_dia=$dia AND id_recurs=$id_recurs ORDER BY hora_inici";
			$query_periodics=mysql_query($consulta_periodics);
			if(mysql_num_rows($query_periodics)>0):
				$resultat=3;
				$taula=mysql_result_table($query_periodics);
				$f_tmp=E_ERR_HI_HA_RESERVA."<br><br>$taula";
				$tpl->assign("E_MOTIUS",$f_tmp);
			else:
				// Ara ja podem inserir i recollir el resultat
				//($dia,$mes,$any,$recurs,$qui,$motiu,$hora_inici,$hora_final){
				$consulta_insert="INSERT INTO tbl_reserves(id_recurs,qui_reserva,motiu_reserva,aula,
							data_reserva,data_final,hora_inici,hora_final,num_dia,id_usuari)
							VALUES (\"$id_recurs\",\"$qui\",\"$motiu\",\"$f_lloc\",\"$f_data_inici\",\"$f_data_final\",\"$f_hora_inici\",
							\"$f_hora_final\",\"$dia\",\"$id_reserva_major\")";
				$query_insert=mysql_query($consulta_insert);
				$resultat=0;
				if (mysql_affected_rows() == 1):
					$tpl->assign("E_MOTIUS","");
				else:
					$resultat=4;
					$tpl->assign("E_MOTIUS", E_ERR_INSERT_DB);
				endif;
			endif;
			$k++;
			} while (($k<$i)&&(@mysql_num_rows($query_periodics)>0));
		endif;
	}

			if($resultat==0):
				// Sha fet la reserva sense problemes.
				$f_projector=1;
				$tpl->assign("E_MSG_RESERVA", E_MSG_RESERVA_RSLT);

			else:
				$f_projector=0;
				if($k==$i):
					$resultat=8;
					$f_tmp=E_ERR_TOTS_OCUPATS."<br><br>$taula";
					$tpl->assign("E_MOTIUS",$f_tmp);
					$tpl->assign("E_MSG_RESERVA", E_MSG_NO_RESERVA_RSLT);
				else:
					$tpl->assign("E_MSG_RESERVA", E_MSG_NO_RESERVA_RSLT);
				endif;
			endif;
			
			mysql_close();
			return compact('f_projector','resultat');
			$tpl->parse(PAGE_CONTENT,"reserva");
	
}



//-----------------------------------------------------------------------------------------------------------
// Procediment: ConfirmarEliminacioReserva()
// Operativa  : Elimina les dates de les reserves
//-----------------------------------------------------------------------------------------------------------
function ConfirmarEliminacioReserva($id_reserva, $recurs, $dia, $mes, $any){
	global $tpl, $template_dir;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/conf_eliminar_reserva.tpl"
	));
	$f_si="<a href=\"$PHP_SELF?op=74&id_reserva=$id_reserva&recurs=$recurs\">".E_YES."</a>";
	$f_no="<a href=\"$PHP_SELF?op=11&dia=$dia&mes=$mes&any=$any&recurs=$recurs\">".E_NO."</a>";

	$tpl->assign(array(
		"E_MSG_RESERVA" => E_MSG_CONF_ELIMINAR_RESERVA,
		"E_MOTIUS"      => E_MSG_CONF_ELIMINAR_RESERVA2,
		"E_OPCIONS"     => "$f_si | $f_no"
	));
	$tpl->parse(PAGE_CONTENT, "reserva");
}


function EliminarReserva($id_reserva, $recurs){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;

	$tpl->define(array(
			page    => "$template_dir/minipage.tpl",
			reserva => "$template_dir/eliminar_reserva.tpl"
	));

	$resultat=0;

	// Connectem amb el servidor i seleccionem la BD corresponent
	@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysql_select_db($dbname) or die(E_ERR_SELECT_DB);

	//Comprovem que realment pot eliminar aquesta reserva
	$consulta="SELECT * 
				FROM tbl_recursos r,tbl_usuaris u
				WHERE r.id_responsable=u.id_usuari AND
				u.nom_usuari=\"$nom_usuari\" AND
				r.id_recurs=\"$recurs\"";

	$query=mysql_query($consulta);

	if($query!=NULL):
		// Si es tenen permisos, procedim a esborrar la reserva indicada
		$consulta_delete="DELETE FROM tbl_reserves WHERE id_reserva=$id_reserva";
		mysql_query($consulta_delete);
		if (mysql_affected_rows() == 1):
			$tpl->assign("E_MOTIUS","");
			EliminarReserva_Projector($id_reserva);
		else:
			$resultat=4;
			$tpl->assign("E_MOTIUS", E_ERR_DELETE_DB);
		endif;
	else:
		$tpl->assign("E_MOTIUS", E_ERR_DELETE_NO_ADMIN);
		$resultat=1;
	endif;

	if($resultat==0):
		$tpl->assign("E_MSG_RESERVA", E_MSG_DEL_RESERVA_RSLT);
	else:
		$tpl->assign("E_MSG_RESERVA", E_MSG_NO_DEL_RESERVA_RSLT);
	endif;

	$tpl->parse(PAGE_CONTENT,"reserva");
}

function EliminarReserva_Projector($id_reserva){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;

	$tpl->define(array(
			page    => "$template_dir/minipage.tpl",
			reserva => "$template_dir/eliminar_reserva.tpl"
	));

	$resultat=0;

	// Connectem amb el servidor i seleccionem la BD corresponent
	@mysql_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysql_select_db('reserves') or die(E_ERR_SELECT_DB);

	//Comprovem que realment pot eliminar aquesta reserva
	$consulta="SELECT * FROM tbl_reserves WHERE id_usuari='$id_reseva'";

	$query=mysql_query($consulta);

	if($query!=NULL):
		// Si es tenen permisos, procedim a esborrar la reserva indicada
		$consulta_delete="DELETE FROM tbl_reserves WHERE id_usuari=$id_reserva";
		mysql_query($consulta_delete);
		if (mysql_affected_rows() == 1):
			$tpl->assign("E_MOTIUS","");
		else:
			$resultat=4;
			$tpl->assign("E_MOTIUS", E_ERR_DELETE_DB);
		endif;
	else:
		$tpl->assign("E_MOTIUS", E_ERR_DELETE_NO_ADMIN);
		$resultat=1;
	endif;

	if($resultat==0):
		$tpl->assign("E_MSG_RESERVA", E_MSG_DEL_RESERVA_RSLT);
	else:
		$tpl->assign("E_MSG_RESERVA", E_MSG_NO_DEL_RESERVA_RSLT);
	endif;

	$tpl->parse(PAGE_CONTENT,"reserva");
}

?>