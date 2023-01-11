<?php

//-----------------------------------------------------------------------------------------------------------
// Procediment: CheckPeriodicReservation()
// Operativa  : Comprova una determinada reserva periodica per al recurs indicat
//-----------------------------------------------------------------------------------------------------------
function CheckPeriodicReservation($data_inici, $data_final, $dia, $recurs, $qui, $motiu, $hora_inici, $hora_final, $uid, $projector){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir, $enviar_correu, $hores_antelacio;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/aceptar_reserva.tpl"
	));

	$resultat=0;
	$a_date=dateToArray($data_inici);
	$dia0=$a_date[0]; $mes0=$a_date[1]; $any0=$a_date[2];
	$a_date=dateToArray($data_final);
	$dia1=$a_date[0]; $mes1=$a_date[1]; $any1=$a_date[2];
	$pos_hora_inici=stripos($hora_inici,':');
	$pos_hora_final=stripos($hora_final,':');
	$min_inici=substr($hora_inici,$pos_hora_inici+1);
	$hora_inici=substr($hora_inici,0,$pos_hora_inici);
	$min_final=substr($hora_final,$pos_hora_final+1);
	$hora_final=substr($hora_final,0,$pos_hora_final);

	if((!checkdate($mes0,$dia0,$any0)) || (!checkdate($mes1,$dia1,$any1))):
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_DATA_NO_VAL);
	elseif ($motiu==""):
		// No han omplert el camp MOTIU
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_MOTIU_NO_VAL);
	elseif ($qui==""):
		// No han omplert el camp QUI
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_QUI_NO_VAL);
	elseif(time()>mktime($hora_inici,$min_inici,0,$mes0,$dia0,$any0)):
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_ARA_NO_VAL);
	// Revisem que la reserva es fa amb 6 hores d'antelaci�
	elseif ((mktime($hora_inici,$min_inici,0,$mes0,$dia0,$any0)-time())<$hores_antelacio*60*60 && ($_SESSION['perfil']=='Usuari' OR $_SESSION['perfil']=='Usuari PDI')):
		// La petici� de reserva s'ha de fer amb 6 hores d'antelaci�
		$resultat=6;
		$tpl->assign("E_MOTIUS", E_ERR_DATA_6H_VAL);
	else:
		//if(($hora_inici<$hora_minim) || ($hora_inici>=$hora_final) || ($hora_final>$hora_maxima)):
		if((mktime($hora_inici,$min_inici,0,$mes,$dia,$any)<mktime($hora_minima,0,0,$mes,$dia,$any))||(mktime($hora_inici,$min_inici,0,$mes,$dia,$any)>=mktime($hora_final,$min_final,0,$mes,$dia,$any))||(mktime($hora_final,$min_final,0,$mes,$dia,$any)>mktime($hora_maxima,0,0,$mes,$dia,$any))):
			$resultat=1;
			$tpl->assign("E_MOTIUS", E_ERR_HORA_NO_VAL);
		else:
			//$f_hora_inici="$hora_inici:00:00";
			//$f_hora_final="$hora_final:00:00";
			$f_hora_inici="$hora_inici:$min_inici";
			$f_hora_final="$hora_final:$min_final";
			
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

			$query_periodics=@mysqli_query($conn,$consulta_periodics);
			
			if(mysqli_num_rows($query_periodics)>0):
				$resultat=3;
				$taula=mysqli_result_table($query_periodics);
				$f_tmp=E_ERR_HI_HA_RESERVA."<br><br>".$taula;
				$tpl->assign("E_MOTIUS",$f_tmp);
				$tpl->assign("E_MSG_RESERVA", E_MSG_NO_RESERVA_RSLT);
			else:
				$resultat=0;				
			endif;
		endif;
	endif;

	return $resultat;

}

//-----------------------------------------------------------------------------------------------------------
// Procediment: ValidarReserva()
// Operativa  : Arriben els paramentres d'una reserva i cal comprovar que sigui valida
//-----------------------------------------------------------------------------------------------------------
function ValidarReservaID($id){
    global $dbname, $dbuser, $dbpass, $dbserver;
    $conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
    @mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
    @mysqli_query($conn,"SET NAMES 'utf8'");
    $consulta_peticio=" SELECT * FROM tbl_peticions WHERE id='".$id."' ";
    $result_peticio = @mysqli_query($conn,$consulta_peticio);//echo $id."--".$dbname.$consulta_peticio.$row['peticions'];exit;
    $row = mysqli_fetch_array($result_peticio);
    $peticio = json_decode($row['peticions'],TRUE);//print_r($peticio);exit;
    ValidarReserva($peticio['dia'],$peticio['mes'],$peticio['any'],$peticio['recurs'],$peticio['qui'],$peticio['motiu'],$peticio['hora_inici'],$peticio['hora_final'],$peticio['assig'],NULL,NULL);
}

//-----------------------------------------------------------------------------------------------------------
// Procediment: ValidarReserva()
// Operativa  : Arriben els paramentres d'una reserva i cal comprovar que sigui valida
//-----------------------------------------------------------------------------------------------------------
function ValidarReserva($dia,$mes,$any,$recurs,$qui,$motiu,$hora_inici,$hora_final,$assig,$uid,$projector){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir, $enviar_correu, $url_reserves, $hores_antelacio;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/aceptar_reserva.tpl"
	));
	$resultat=0;
	//Desglosem les hores i els minuts.
	$pos_hora_inici=stripos($hora_inici,':');
	$pos_hora_final=stripos($hora_final,':');
	$min_inici=substr($hora_inici,$pos_hora_inici+1);
	$hora_inici=substr($hora_inici,0,$pos_hora_inici);
	$min_final=substr($hora_final,$pos_hora_final+1);
	$hora_final=substr($hora_final,0,$pos_hora_final);
	
	// Hem de revisar que si seleccionem un aula amb projector ens assegurem que no fem reserva del projector per aquella aula.
	if (($recurs==19)||($recurs==20)||($recurs==21)||($recurs==22)||($recurs==23)||($recurs==24)||($recurs==25)||($recurs==26)||($recurs==27)||($recurs==28)||($recurs==29)):
		// Hi han aules que ja disposen de projector fixe
		$projector=0;
	endif;

	if(!checkdate($mes,$dia,$any)):
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_DATA_NO_VAL);

	elseif(time()>mktime($hora_inici,$min_inici,0,$mes,$dia,$any)):
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_ARA_NO_VAL);
	elseif(($qui=="") || ($motiu=="")):
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_MOTIU_NO_VAL);
	elseif ((mktime($hora_inici,$min_inici,0,$mes,$dia,$any)-time())<$hores_antelacio*60*60 && ($_SESSION['perfil']=='Usuari' OR $_SESSION['perfil']=='Usuari PDI')):
		// La petici� de reserva s'ha de fer amb 6 hores d'antelaci�
		$resultat=6;
		$tpl->assign("E_MOTIUS", E_ERR_DATA_6H_VAL); 
	else:
		if((mktime($hora_inici,$min_inici,0,$mes,$dia,$any)<mktime($hora_minima,0,0,$mes,$dia,$any))||(mktime($hora_inici,$min_inici,0,$mes,$dia,$any)>=mktime($hora_final,$min_final,0,$mes,$dia,$any))||(mktime($hora_final,$min_final,0,$mes,$dia,$any)>mktime($hora_maxima,0,0,$mes,$dia,$any))):
			$resultat=1;
			$tpl->assign("E_MOTIUS", E_ERR_HORA_NO_VAL);

		else:
			$f_hora_inici="$hora_inici:$min_inici";
			$f_hora_final="$hora_final:$min_final";
			$f_data="$any/$mes/$dia";

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
			$f_avis_legal = mysqli_result($query_aula,0,"avis_legal");
			$enviar_correu = mysqli_result($query_aula,0,"avisar_resp");

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
					//S'ha demanat la reserva de un projector tamb�
					if($projector==1):
						$lloc=$aula;
	
					endif;
					//Evaluem dos condicions: 1-No hem demanat reserva de projector i l'aula est� lliure, i 2- Hem demanat
					//reserva de projector i tant l'aula com el projector es poden demanar
					//if(($projector==0)||(($projector==1)&&($f_projector==1))):
						//Avaluem que sigui un usuari amb permisos per reservar les Sales d'Actes o Juntes.
						if(($_SESSION['perfil']=='CCESAII' || ($_SESSION['perfil']=='Usuari Sales'&&($f_tipus_reserva=='3')) || ($_SESSION['perfil']=='Usuari NT3'&&($f_tipus_reserva=='6')) || ($_SESSION['perfil']=='Usuari PDI' && (/*$f_tipus_reserva==2 ||*/ $f_tipus_reserva==4)))):
							//print $_SESSION['perfil'];print $f_tipus_reserva;exit;
							// Ara ja podem inserir i recollir el resultat
							if($f_avis_legal==1) $uid = utf8_encode(E_AVIS_LEGAL_INFO);
							$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
							@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
							@mysqli_query($conn,"SET NAMES 'utf8'");
							$consulta_insert="INSERT INTO tbl_reserves(id_recurs,qui_reserva,motiu_reserva,assig,aula,
								data_reserva,data_final,hora_inici,hora_final,num_dia,tipus_reserva,comentari)
								VALUES (\"$recurs\",\"$qui\",\"$motiu\",\"$assig\",\"$aula\",\"$f_data\",\"$f_data\",\"$f_hora_inici\",
								\"$f_hora_final\",\"$numero_dia\",\"$f_tipus_reserva\",'".addslashes($uid)."')";
							$query_periodics=@mysqli_query($conn,$consulta_insert);
							if (mysqli_affected_rows($conn) == 1) :
								$tpl->assign("E_MOTIUS","");
								//if($projector==1):
								//Inserim a id_usuari de la taula reserves el codi id_reserves de la taula aules per controlar el projector
									//Inserir_id_reserva_major($dbserver, $dbname, $dbuser, $dbpass);
								//endif;
							else:
								$resultat=4;
								$tpl->assign("E_MOTIUS", E_ERR_INSERT_DB);
							endif;
						else:
							EnviarCorreuPeticioReserva($dia,$mes,$any,$recurs,$qui,$assig,$motiu,$f_hora_inici,$f_hora_final,$aula,$f_avis_legal,$f_tipus_reserva);
							$resultat=9;
						endif;
					/*else:
						//Tots els projectors estan ocupats
						if($resultat==8):
							print($k);
							print($i);
							//$taula=mysqli_result_table($query_periodics);
							$f_tmp=E_ERR_TOTS_OCUPATS."<br><br>$taula";
							$tpl->assign("E_MOTIUS",$f_tmp);
						else:
							$resultat=7;
							$f_tmp=E_ERR_HI_HA_AULA_SENSE_PROJECTOR."<br><br>$taula";
							$tpl->assign("E_MOTIUS",$f_tmp);
						endif;
					endif;*/
				endif;
			endif;
		endif;
	endif;

	if($resultat==0):
		// Sha fet la reserva sense problemes.
		// Ho notifiquem per pantalla i, si procedeix, enviem el mail de confirmacio...
		$tpl->assign("E_MSG_RESERVA", E_MSG_RESERVA_RSLT);

		if($enviar_correu):
			$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
			@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
			@mysqli_query($conn,"SET NAMES 'utf8'");
			
			//Averiguem l'usuari responsable del recurs
			$consulta_email="SELECT re.id_tipus as id_tipus,
											u.email as email, u.email_fnb as email_fnb,
											re.nom_recurs as recurs
									FROM tbl_recursos re,
											tbl_usuaris u
									WHERE re.id_responsable=u.id_usuari AND
											re.id_recurs=$recurs";

			$query_email=@mysqli_query($conn,$consulta_email);
			
			//Averiguem l'usuari que demana el recurs
			$consulta_email_fnb="SELECT u.email as email, u.email_fnb as email_fnb
										FROM tbl_usuaris u
									WHERE u.nom_usuari='$qui'";

			$query_email_fnb=@mysqli_query($conn,$consulta_email_fnb);
			$assig = stripslashes($assig);
			$motiu = stripslashes($motiu);
			
			if (mysqli_result($query_email,0,"id_tipus")=="1"):
				//Si cal avisar de les reserves del recurs de les aules docents, enviem email al responsable
				$from="No respondre <centre.calcul@fnb.upc.edu>";
				$to="gestio.academica@fnb.upc.edu";
				//$cc="cap.serveis@fnb.upc.edu";
				$cc1=mysqli_result($query_email_fnb,0,"email_fnb");
				$bcc="centre.calcul@fnb.upc.edu";
			elseif (mysqli_result($query_email,0,"id_tipus")=="3"):
				//Si cal avisar de les reserves del recurs de les sales de juntes i acte, enviem email al responsable
				$from="No respondre <centre.calcul@fnb.upc.edu>";
				$to=mysqli_result($query_email_fnb,0,"email_fnb");
				//$cc="cap.serveis@fnb.upc.edu";
				$cc1="secretaria.direccio@fnb.upc.edu";
				$bcc="centre.calcul@fnb.upc.edu";
			elseif (mysqli_result($query_email,0,"id_tipus")=="6"):
				//Si cal avisar de les reserves del recurs de les sales de juntes i acte, enviem email al responsable
				$from="No respondre <centre.calcul@fnb.upc.edu>";
				//$to="cap.serveis@fnb.upc.edu";
				$to="aula.professional@fnb.upc.edu";
				$cc1="<".mysqli_result($query_email_fnb,0,"email_fnb").">";
				$bcc="centre.calcul@fnb.upc.edu";
			elseif (mysqli_result($query_email,0,"id_tipus")=="7"):
				//Si cal avisar de les reserves del recurs de les sales de juntes i acte, enviem email al responsable
				$from="No respondre <centre.calcul@fnb.upc.edu>";
				$to="jtorralbo@cen.upc.edu";
				$cc1="<".mysqli_result($query_email_fnb,0,"email_fnb").">";
				$bcc="centre.calcul@fnb.upc.edu";
			else:
				//Si cal avisar de les reserves del recurs de les sales de juntes i acte, enviem email al responsable
				$from="No respondre <centre.calcul@fnb.upc.edu>";
				$to="<".mysqli_result($query_email_fnb,0,"email_fnb").">";
				$cc="centre.calcul@fnb.upc.edu";
				//$cc1="cap.serveis@fnb.upc.edu";
				$bcc="";
			endif;
				$dd1 = html_entity_decode(mysqli_result($query_email,0,"recurs"), ENT_NOQUOTES, 'UTF-8');
				//$to=mysqli_result($query_email,0,"email");
				$subject="Reservator v.1.1: Nova reserva de ".html_entity_decode(mysqli_result($query_email,0,"recurs"), ENT_NOQUOTES, 'UTF-8')."";
				$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Si us plau, no el contesteu.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
				$body.="S'ha fet una nova reserva de \"".mysqli_result($query_email,0,"recurs")."\".<br><br>&nbsp;&nbsp;&nbsp;<b>Usuari</b>: $qui.<br>&nbsp;&nbsp;&nbsp;<b>Activitat/Assignatura</b>: $assig.<br>&nbsp;&nbsp;&nbsp;<b>Motiu</b>: $motiu.<br>&nbsp;&nbsp;&nbsp;<b>Dia</b>: $dia/$mes/$any.<br>&nbsp;&nbsp;&nbsp;<b>Horari</b>: de $f_hora_inici a $f_hora_final.<br>&nbsp;&nbsp;&nbsp;<b>Comentari</b>: $uid<p>";
				//$body.="<p>Clicar <a href='$url_reserves?any=$any&mes=$mes&recurs=$recurs&op=30'>aqui</a> per veure el recurs.</p>";
				$body.="<p>Clicar <a href='".html_entity_decode($url_reserves."?any=$any&mes=$mes&recurs=$recurs&op=30", ENT_NOQUOTES, 'UTF-8')."'>aqui</a> per veure el recurs.</p>";
				$signature="Centre de C&agrave;lcul FNB</font>";

				EnviarEmail($from, $to, $cc, $cc1, $bcc, $subject, $body, $signature, $err_msg);
				//mail($f_mail,,"L'usuari $qui ha fet una reserva de $f_recurs pel motiu: $motiu","From: reservator@$SERVER_NAME");
		endif;


	else:
		if($resultat==9):
			if($f_tipus_reserva==3):
				$tpl->assign("E_MSG_RESERVA", E_MSG_PETICIO_RESERVA_RSLT);
			elseif($f_tipus_reserva==1):
				$tpl->assign("E_MSG_RESERVA", E_MSG_PETICIO_RESERVA_RSLT_AULES);
			elseif($f_tipus_reserva==2):
				$tpl->assign("E_MSG_RESERVA", E_MSG_PETICIO_RESERVA_RSLT_INF);
			elseif($f_tipus_reserva==6):
				$tpl->assign("E_MSG_RESERVA", E_MSG_PETICIO_RESERVA_RSLT_SIMU);
			endif;
			$tpl->assign("E_MOTIUS","");
		else:
			$tpl->assign("E_MSG_RESERVA", E_MSG_NO_RESERVA_RSLT);
		endif;
	endif;

	$tpl->parse(PAGE_CONTENT,"reserva");
}

//-----------------------------------------------------------------------------------------------------------
// Procediment: MostrarInfoReserva()
// Operativa  : Arriben els paramentres d'una reserva i cal comprovar que sigui valida
//-----------------------------------------------------------------------------------------------------------
function MostrarInfoReserva($id_reserva,$dia,$mes,$any){
	global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/info_reserva.tpl"
	));
	$resultat=0;

	// Connectem amb el servidor i seleccionem la BD corresponent
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
	@mysqli_query($conn,"SET NAMES 'utf8'");
	

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
							re.assig AS assig,
							re.comentari AS comentari
					FROM  tbl_recursos r,
							tbl_reserves re,
							tbl_usuaris u
					WHERE r.id_recurs=re.id_recurs AND
							r.id_responsable=u.id_usuari AND
							re.id_reserva=$id_reserva";

	$query=@mysqli_query($conn,$consulta);

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
			"E_COMENTARI"		   => E_COMENTARI,
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
			"HORA_INICI" => mysqli_result($query,0,"hora_inici"),
			"HORA_FINAL" => mysqli_result($query,0,"hora_final"),
			"QUI" => mysqli_result($query,0,"qui"),
			"EMAIL" => mysqli_result($query,0,"email"),
			"RESPONSABLE" => mysqli_result($query,0,"respon"),
			"MOTIU" => mysqli_result($query,0,"motiu"),
			"DIA" => $dia,
			"COMENTARI" => mysqli_result($query,0,"comentari"),
			"ASSIG" => mysqli_result($query,0,"assig")
		));
	endif;
	$n_assig=mysqli_result($query,0,"assig");
	if(substr($n_assig,0,1)=='2') $n_assig = substr($n_assig,0,6);
	elseif(substr($n_assig,0,1)=='1') $n_assig = substr($n_assig,0,5);
	// Calculem el curs i el quadrimestre en el dia d'avui
	if ($mes==7):
		if ($dia<15):
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
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, "pruebas-oracle") or die(E_ERR_SELECT_DB);
	$cons_prof="SELECT DISTINCT NUCLI_VW_PERSONES_280.cognoms as cognoms, NUCLI_VW_PERSONES_280.nom as nom
	FROM NUCLI_VW_PERSONES_280, NUCLI_VW_PROFESSOR_UD_280
	WHERE NUCLI_VW_PERSONES_280.codi_persona=NUCLI_VW_PROFESSOR_UD_280.codi_persona AND NUCLI_VW_PROFESSOR_UD_280.codi_upc_ud=\"$n_assig\" AND NUCLI_VW_PROFESSOR_UD_280.curs=\"$curs\" AND NUCLI_VW_PROFESSOR_UD_280.quad=\"$quad\"";
	$result_cons_prof = @mysqli_query($conn,$cons_prof);
	//echo $n_assig;
	if ($row_codi_prof = mysqli_fetch_array($result_cons_prof)):
		do {
			$prof .= $row_codi_prof["cognoms"].", ".$row_codi_prof["nom"]."<br>";
		} while ($row_codi_prof = mysqli_fetch_array($result_cons_prof));
		$tpl->define(array(
			reserva => "$template_dir/info_reserva_prof.tpl"
		));
		$tpl->assign(array(
			"E_PROF" => E_PROF,
			"PROF" => $prof
		));
		//echo $prof."-".$curs."-".$quad;exit;
		$tpl->parse(PAGE_CONTENT,"reserva");
	else:
		$tpl->parse(PAGE_CONTENT,"reserva");
	endif;
}


// Funcions de reserves



//-----------------------------------------------------------------------------------------------------------
// Procediment: error_periodic_reservation()
// Operativa  : Indica si la reserva es pot realitzar. Verifiquem que el recurs no est� ocupat.
//-----------------------------------------------------------------------------------------------------------
function error_periodic_reservation(){
	global $tpl, $template_dir;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/aceptar_reserva.tpl"
	));
	$tpl->assign("E_MSG_RESERVA", E_MSG_NO_RESERVA_RSLT);
	if ($resultat==5):
		$tpl->assign("E_MOTIUS", E_ERR_MOTIU_NO_VAL);
	endif;
	if ($resultat==6):
		$tpl->assign("E_MOTIUS", E_ERR_DATA_24_VAL);
	endif;
	
	$tpl->parse("PAGE_CONTENT", "reserva");
}


//-----------------------------------------------------------------------------------------------------------
// Procediment: accept_periodic_reservation()
// Operativa  : Inserta les dates de les reserves
//-----------------------------------------------------------------------------------------------------------
function accept_periodic_reservation($data_inici,$data_final,$dia,$recurs,$qui,$motiu,$hora_inici,$hora_final,$assig,$uid,$projector){
	global $tpl,$hora_maxima,$hora_minima,$dbname,$dbuser,$dbpass,$dbserver,$lang,$locales,$template_dir,$enviar_correu,$url_reserves;

    	$tpl->define( array(page => "$template_dir/minipage.tpl",
        					reserva => "$template_dir/aceptar_reserva.tpl") );
		$resultat=0;
		$a_date=dateToArray($data_inici);
		$dia0=$a_date[0]; $mes0=$a_date[1]; $any0=$a_date[2];
		$a_date=dateToArray($data_final);
		$dia1=$a_date[0]; $mes1=$a_date[1]; $any1=$a_date[2];
		$pos_hora_inici=stripos($hora_inici,':');
		$pos_hora_final=stripos($hora_final,':');
		$min_inici=substr($hora_inici,$pos_hora_inici+1);
		$hora_inici=substr($hora_inici,0,$pos_hora_inici);
		$min_final=substr($hora_final,$pos_hora_final+1);
		$hora_final=substr($hora_final,0,$pos_hora_final);
		
	// Hem de revisar que si seleccionem un aula amb projector ens assegurem que no fem reserva del projector per aquella aula.
	if (($recurs==19)||($recurs==20)||($recurs==21)||($recurs==22)||($recurs==23)||($recurs==24)||($recurs==25)||($recurs==26)||($recurs==27)||($recurs==28)||($recurs==29)):
		// Hi han aules que ja disposen de projector fixe
		$projector=0;
	endif;

    if((!checkdate($mes0,$dia0,$any0))||(!checkdate($mes1,$dia1,$any1))){
       	$resultat=5;
	    $tpl->assign("E_MOTIUS", E_ERR_DATA_NO_VAL);
	}else if((strlen(trim($qui))==0)||(strlen(trim($motiu))==0)){
		$resultat=5;
	    $tpl->assign("E_MOTIUS", E_ERR_MOTIU_NO_VAL);
	}else if(time()>mktime($hora_inici,$min_inici,0,$mes0,$dia0,$any0)){
		$resultat=5;
		$tpl->assign("E_MOTIUS", E_ERR_ARA_NO_VAL);
	}
	else{

			if((mktime($hora_inici,$min_inici,0,$mes,$dia,$any)<mktime($hora_minima,0,0,$mes,$dia,$any))||(mktime($hora_inici,$min_inici,0,$mes,$dia,$any)>=mktime($hora_final,$min_final,0,$mes,$dia,$any))||(mktime($hora_final,$min_final,0,$mes,$dia,$any)>mktime($hora_maxima,0,0,$mes,$dia,$any))):
	        	$resultat=1;
	        	$tpl->assign("E_MOTIUS", E_ERR_HORA_NO_VAL);
		
			else:
		        	
	        	//$f_hora_inici="$hora_inici:00:00";
	        	//$f_hora_final="$hora_final:00:00";
				$f_hora_inici="$hora_inici:$min_inici";
				$f_hora_final="$hora_final:$min_final";
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
				$nom_dia_setmana=nomdia($dia);
				$f_data_inici=sprintf("%s/%s/%s",$any0,$mes0,$dia0);
				$f_data_final=sprintf("%s/%s/%s",$any1,$mes1,$dia1);
				
				//echo $f_data_inici;
				//echo $f_data_final;
				
				// Ara averig�en quina aula es en funci� de la variable recurs							
				$consulta_aula="SELECT *
								FROM tbl_recursos
								WHERE id_recurs=$recurs";
				$query_aula=@mysqli_query($conn,$consulta_aula);
				$aula=mysqli_result($query_aula,0,"nom_recurs");
				$enviar_correu = mysqli_result($query_aula,0,"avisar_resp");
				$f_tipus_reserva = mysqli_result($query_aula,0,"id_tipus");
				$f_avis_legal = mysqli_result($query_aula,0,"avis_legal");
				
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

				$query_periodics=@mysqli_query($conn,$consulta_periodics);
				if(mysqli_num_rows($query_periodics)>0):
					$resultat=3;
					$taula=mysqli_result_table($query_periodics);
	    			$f_tmp=sprintf("%s<br><br> %s",E_ERR_HI_HA_RESERVA,$taula);
	    			$tpl->assign("E_MOTIUS",$f_tmp);
				else:
						//Avaluem que sigui un usuari amb permisos per reservar les Sales d'Actes o Juntes.
						if(($_SESSION['perfil']=='CCESAII' || ($_SESSION['perfil']=='Usuari Sales'&&($f_tipus_reserva=='3')) || ($_SESSION['perfil']=='Usuari NT3'&&($f_tipus_reserva=='6')) || ($_SESSION['perfil']=='Usuari PDI' && (/*$f_tipus_reserva==2 || */$f_tipus_reserva==4)))):
							// Ara ja podem inserir i recollir el resultat
							$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
							@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
							// Ara ja podem inserir i recollir el resultat
							
							//Averiguamos si hay algun dia de fiesta o no lectivo dentro de la consulta per�dica. Los dias de inicio y fin 
							//de cuatrimestre si aparecen (festiu!=3), los de Actividades complementarias se han de poner puntualmente.
							$consulta_festius="SELECT data FROM tbl_festes
							WHERE ((data>=\"$f_data_inici\")AND(data<=\"$f_data_final\"))
					 		AND num_dia=$dia AND (festiu=1 || festiu=4) ORDER BY data";
							$query_festius=@mysqli_query($conn,$consulta_festius);
							$f = mysqli_num_rows($query_festius);
							//Excluimos los dias festivos o no lectivos de la reserva peri�dica.
							$id_periodic=microtime(true);//Marca de temps en microsegons
							for ($fe=0; $fe<=$f; $fe++):
								//hasta el primer dia festivo o no lectivo
								if($fe==0):
									$festiu[$fe] = mysqli_result($query_festius, $fe, "data");
									$f_data_inici_aux[$fe] = $f_data_inici;
									//En el caso que no haya dias festivos o no lectivos.
									if ($f==0):
										$f_data_final_aux[$fe] = $f_data_final;
									else:
										$fe_date2 = dateToArray($festiu[$fe]);
										$dia_fe2 = $fe_date2[2]; $mes_fe2=$fe_date2[1]; $any_fe2=$fe_date2[0];
										$fe_date_aux2 = mktime(0, 0, 0, $mes_fe2, $dia_fe2-1, $any_fe2);
										$f_data_final_aux[$fe] = date('Y-m-d', $fe_date_aux2);
									endif;
								//periodo de dias festivos no lectivos intermedios
								elseif(0<$fe && $fe<$f):
									$festiu[$fe] = mysqli_result($query_festius, $fe, "data");
									$fe_date1 = dateToArray($festiu[$fe-1]);
									$dia_fe1 = $fe_date1[2]; $mes_fe1=$fe_date1[1]; $any_fe1=$fe_date1[0];
									$fe_date_aux1 = mktime(0, 0, 0, $mes_fe1, $dia_fe1+1, $any_fe1);
									$f_data_inici_aux[$fe] = date('Y-m-d', $fe_date_aux1);
									
									$fe_date2 = dateToArray($festiu[$fe]);
									$dia_fe2 = $fe_date2[2]; $mes_fe2=$fe_date2[1]; $any_fe2=$fe_date2[0];
									$fe_date_aux2 = mktime(0, 0, 0, $mes_fe2, $dia_fe2-1, $any_fe2);
									$f_data_final_aux[$fe] = date('Y-m-d', $fe_date_aux2);
								//�ltimo periodo entre los dia festivos.
								elseif($fe==$f):
									$fe_date1 = dateToArray($festiu[$fe-1]);
									$dia_fe1 = $fe_date1[2]; $mes_fe1=$fe_date1[1]; $any_fe1=$fe_date1[0];
									$fe_date_aux1 = mktime(0, 0, 0, $mes_fe1, $dia_fe1+1, $any_fe1);
									$f_data_inici_aux[$fe] = date('Y-m-d', $fe_date_aux1);
									
									$f_data_final_aux[$fe] = $f_data_final;
									
								endif;
								$hihadia=HiHaDiaEnPeriode($f_data_inici_aux[$fe],$f_data_final_aux[$fe],$dia);
								//Revisamos que en periodo a insertar exista un dia con reserva, sino no lo incluimos en la BBDD
								if($hihadia==1):
									if($f_avis_legal==1) $uid = utf8_encode(E_AVIS_LEGAL_INFO);
									$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
									@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
									@mysqli_query($conn,"SET NAMES 'utf8'");
									$consulta_insert="INSERT INTO tbl_reserves(id_recurs,qui_reserva,motiu_reserva,assig,aula,
										data_reserva,data_final,hora_inici,hora_final,num_dia,tipus_reserva,comentari,id_periodic)
										VALUES (\"$recurs\",\"$qui\",\"$motiu\",\"$assig\",\"$aula\",\"$f_data_inici_aux[$fe]\",\"$f_data_final_aux[$fe]\",\"$f_hora_inici\",
										\"$f_hora_final\",\"$dia\",\"$f_tipus_reserva\",'".addslashes($uid)."',\"$id_periodic\")";
									$query_periodics=@mysqli_query($conn,$consulta_insert);
									if (mysqli_affected_rows($conn) == 1):
		       							$tpl->assign("E_MOTIUS","");
										//Inserir_id_reserva_major($dbserver, $dbname, $dbuser, $dbpass);
			     					else:
		       							$resultat=4;
		       							$tpl->assign("E_MOTIUS", E_ERR_INSERT_DB);
		    	 					endif;
								endif;
							endfor;
						else:
							EnviarCorreuPeticioReservaPeriodica($dia0,$mes0,$any0,$dia1,$mes1,$any1,$recurs,$qui,$assig,$motiu,$f_hora_inici,$f_hora_final,$aula,$f_avis_legal,$dia,$f_tipus_reserva);
							$resultat=9;
						endif;
				endif;
			endif;
	};

	if($resultat==0):
		// S'ha fet la reserva sense problemes.
		// Ho notifiquem per pantalla i, si procedeix, enviem el mail de confirmacio...
		$tpl->assign("E_MSG_RESERVA", E_MSG_RESERVA_RSLT);

		if($enviar_correu):
			$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
			@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
			@mysqli_query($conn,"SET NAMES 'utf8'");
			//Averiguem el responsable del recurs
			$consulta_email="SELECT re.id_tipus as id_tipus,
											u.email as email, u.email_fnb as email_fnb,
											re.nom_recurs as recurs
									FROM tbl_recursos re,
											tbl_usuaris u
									WHERE re.id_responsable=u.id_usuari AND
											re.id_recurs='$recurs'";

			$query_email=@mysqli_query($conn,$consulta_email);
			//Averiguem l'usuari que demana el recurs
			$consulta_email_fnb="SELECT u.email as email, u.email_fnb as email_fnb, u.nom_usuari as nom_usuari
										FROM tbl_usuaris u
									WHERE u.nom_usuari='$qui'";

			$query_email_fnb=@mysqli_query($conn,$consulta_email_fnb);
			if (mysqli_result($query_email,0,"id_tipus")=="1"):
				//Si cal avisar de les reserves del recurs de les aules docents, enviem email al responsable
				$from="No respondre <centre.calcul@fnb.upc.edu>";
				$to="<".mysqli_result($query_email_fnb,0,"email_fnb").">";
				$cc="gestio.academica@fnb.upc.edu";
				//$cc1="cap.serveis@fnb.upc.edu";
				$bcc="centre.calcul@fnb.upc.edu";
			elseif (mysqli_result($query_email,0,"id_tipus")=="3"):
				//Si cal avisar de les reserves del recurs de les sales de juntes i acte, enviem email al responsable
				$from="No respondre <centre.calcul@fnb.upc.edu>";
				$to="<".mysqli_result($query_email_fnb,0,"email_fnb").">";
				//$cc="cap.serveis@fnb.upc.edu";
				$cc1="secretaria.direccio@fnb.upc.edu";
				$bcc="centre.calcul@fnb.upc.edu";
			elseif (mysqli_result($query_email,0,"id_tipus")=="6"):
				//Si cal avisar de les reserves del recurs de les sales de juntes i acte, enviem email al responsable
				$from="No respondre <centre.calcul@fnb.upc.edu>";
				//$to="cap.serveis@fnb.upc.edu";
				$to="aula.professional@fnb.upc.edu";
				$cc1="<".mysqli_result($query_email_fnb,0,"email_fnb").">";
				$bcc="centre.calcul@fnb.upc.edu";
			else:
				//Si cal avisar de les reserves del recurs de les sales de juntes i acte, enviem email al responsable
				$from="No respondre <centre.calcul@fnb.upc.edu>";
				$to="<".mysqli_result($query_email_fnb,0,"email_fnb").">";
				$cc="centre.calcul@fnb.upc.edu";
				//$cc1="cap.serveis@fnb.upc.edu";
				$bcc="";
			endif;
			//$to="smenendez@fnb.upc.edu";
			//$to=mysqli_result($query_email,0,"email");
			$subject="Reservator v.1.1: Nova reserva ".html_entity_decode('peri&ograve;dica', ENT_NOQUOTES, 'UTF-8')." de \"".html_entity_decode(mysqli_result($query_email,0,"recurs"), ENT_NOQUOTES, 'UTF-8')."\"";
			$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Si us plau, no el contesteu.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
			$body.="S'ha fet una nova reserva de \"".mysqli_result($query_email,0,"recurs")."\".<br><br>&nbsp;&nbsp;&nbsp;<b>Usuari</b>: $qui.<br>&nbsp;&nbsp;&nbsp;<b>Activitat/Assignatura</b>: $assig.<br>&nbsp;&nbsp;&nbsp;<b>Motiu</b>: $motiu.<br>&nbsp;&nbsp;&nbsp;<b>Data</b>: Tots els $nom_dia_setmana des del $data_inici al $data_final.<br>&nbsp;&nbsp;&nbsp;<b>Horari</b>: de $f_hora_inici a $f_hora_final.<br>&nbsp;&nbsp;&nbsp;<b>Comentari</b>: $uid<p>";
			$body.="<p>Clicar <a href='".html_entity_decode($url_reserves."?any=$any0&mes0=$mes&recurs=$recurs&op=30", ENT_NOQUOTES, 'UTF-8')."'>aqui</a> per veure el recurs.</p>";
			$signature="Centre de C&agrave;lcul FNB</font>";

			EnviarEmail($from, $to, $cc, $cc1, $bcc, $subject, $body, $signature, $err_msg);
			//mail($f_mail,,"L'usuari $qui ha fet una reserva de $f_recurs pel motiu: $motiu","From: reservator@$SERVER_NAME");
		endif;
	else:
		if($resultat==9):
			if($f_tipus_reserva==3):
				$tpl->assign("E_MSG_RESERVA", E_MSG_PETICIO_RESERVA_RSLT);
			elseif($f_tipus_reserva==2):
				$tpl->assign("E_MSG_RESERVA", E_MSG_PETICIO_RESERVA_RSLT_INF);
			elseif($f_tipus_reserva==1):
				$tpl->assign("E_MSG_RESERVA", E_MSG_PETICIO_RESERVA_RSLT_AULES);
			elseif($f_tipus_reserva==6):
				$tpl->assign("E_MSG_RESERVA", E_MSG_PETICIO_RESERVA_RSLT_SIMU);
			endif;
			$tpl->assign("E_MOTIUS","");
		else:
			$tpl->assign("E_MSG_RESERVA", E_MSG_NO_RESERVA_RSLT);
		endif;
	endif;


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
	$f_si="<a href=\"$PHP_SELF?op=74&id_reserva=$id_reserva&dia=$dia&mes=$mes&any=$any&&recurs=$recurs\">".E_YES."</a>";
	$f_no="<a href=\"$PHP_SELF?op=11&dia=$dia&mes=$mes&any=$any&recurs=$recurs\">".E_NO."</a>";

	$tpl->assign(array(
		"E_MSG_RESERVA" => E_MSG_CONF_ELIMINAR_RESERVA,
		"E_MOTIUS"      => E_MSG_CONF_ELIMINAR_RESERVA2,
		"E_OPCIONS"     => "$f_si | $f_no"
	));
	
	$g_si="<a href=\"$PHP_SELF?op=75&id_reserva=$id_reserva&dia=$dia&mes=$mes&any=$any&recurs=$recurs\">".E_YES."</a>";
	$tpl->assign(array(
		"E_MSG_RESERVA_DIA" => E_MSG_CONF_ELIMINAR_DIA_RESERVA,
		"E_MOTIUS2"      => E_MSG_CONF_ELIMINAR_DIA_RESERVA2,
		"E_OPCIONS2"     => "$g_si | $f_no"
	));
	$tpl->parse(PAGE_CONTENT, "reserva");
}

//-----------------------------------------------------------------------------------------------------------
// Procediment: EliminacioReserva()
// Operativa  : Elimina les dates de les reserves
//-----------------------------------------------------------------------------------------------------------
function EliminarReserva($id_reserva, $recurs){
	global $tpl, $hora_maxima, $hora_minima, $hores_antelacio_x_eliminar_reserva, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;

	$tpl->define(array(
			page    => "$template_dir/minipage.tpl",
			reserva => "$template_dir/eliminar_reserva.tpl"
	));

	$resultat=0;

	// Connectem amb el servidor i seleccionem la BD corresponent
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);

	//Comprovem que realment pot eliminar aquesta reserva
	$consulta="SELECT * 
				FROM tbl_recursos r,tbl_usuaris u
				WHERE r.id_responsable=u.id_usuari AND
				u.nom_usuari=\"$nom_usuari\" AND
				r.id_recurs=\"$recurs\"";

	$query=@mysqli_query($conn,$consulta);
	$consulta_aula="SELECT *
					FROM tbl_recursos
					WHERE id_recurs=$recurs";
				$query_aula=@mysqli_query($conn,$consulta_aula);
				$aula=mysqli_result($query_aula,0,"nom_recurs");
				$enviar_correu = mysqli_result($query_aula,0,"avisar_resp");
				$f_tipus_reserva = mysqli_result($query_aula,0,"id_tipus");
				
	$consulta_periodics="SELECT id_recurs, qui_reserva, motiu_reserva, data_reserva, data_final, hora_inici, hora_final, num_dia, id_periodic
					FROM tbl_reserves
					WHERE id_reserva=$id_reserva";

				$query_periodics=@mysqli_query($conn,$consulta_periodics);
				$qui=mysqli_result($query_periodics,0,"qui_reserva");
				$motiu=mysqli_result($query_periodics,0,"motiu_reserva");
				$data_inici=mysqli_result($query_periodics,0,"data_reserva");
				$data_final=mysqli_result($query_periodics,0,"data_final");
				$data_inici=mysqli_result($query_periodics,0,"data_reserva");
				$hora_inici=mysqli_result($query_periodics,0,"hora_inici");
				$hora_final=mysqli_result($query_periodics,0,"hora_final");
				$num_dia=mysqli_result($query_periodics,0,"num_dia");
				$nom_dia_setmana=nomdia($num_dia);
				$id_periodic=mysqli_result($query_periodics,0,"id_periodic");

				if($id_periodic>0) $data_inici = PrimerDiaEnPeriode($data_inici,$data_final,$num_dia);
				$a_date=dateToArray($data_inici);
				$any0=$a_date[0]; $mes0=$a_date[1]; $dia0=$a_date[2];
				$a_date=dateToArray($data_final);
				$any1=$a_date[0]; $mes1=$a_date[1]; $dia1=$a_date[2];
				$pos_hora_inici=stripos($hora_inici,':');
				$pos_hora_final=stripos($hora_final,':');
				$min_inici=substr($hora_inici,$pos_hora_inici+1);
				$hora_inici=substr($hora_inici,0,$pos_hora_inici);
				$min_final=substr($hora_final,$pos_hora_final+1);
				$hora_final=substr($hora_final,0,$pos_hora_final);

	$hores_antelacio = Calcul_x_hores_laborales($hora_inici,$min_inici,$dia0,$mes0,$any0);
	if($query_periodics!=NULL):
		if(time()>mktime($hora_inici,$min_inici,0,$mes0,$dia0,$any0) && time()<mktime($hora_final,$min_final,0,$mes1,$dia1,$any1)):
			$consulta_update="UPDATE tbl_reserves SET data_final=(now()-60*60*24) WHERE id_reserva=$id_reserva";
			@mysqli_query($conn,$consulta_update);
			
			$consulta_delete="DELETE FROM tbl_reserves WHERE id_reserva>$id_reserva AND id_periodic=$id_periodic";
			$query_delete=@mysqli_query($conn,$consulta_delete);
			$taula=mysqli_result_table($query_delete);
			$f_tmp .= sprintf("%s",$taula);
		    $tpl->assign("E_MOTIUS",$f_tmp);

		elseif(time()>mktime($hora_inici,$min_inici,0,$mes0,$dia0,$any0)):
			$resultat=3;
			$query_periodics=@mysqli_query($conn,$consulta_periodics);
			$taula=mysqli_result_table($query_periodics);
			$f_tmp=sprintf("%s<br><br> %s",E_ERR_DELETE_RESERVA_PERIODICA,$taula);
		    $tpl->assign("E_MOTIUS",$f_tmp);
		elseif($hores_antelacio < $hores_antelacio_x_eliminar_reserva && !$_SESSION['perfil']=='CCESAII' && !(($_SESSION['perfil']=='Usuari Sales'&&($f_tipus_reserva=='3')) || ($_SESSION['perfil']=='Usuari NT3'&&($f_tipus_reserva=='6')))):
			$resultat=5;
			$query_periodics=@mysqli_query($conn,$consulta_periodics);
			$taula=mysqli_result_table($query_periodics);
			$temps_antelacio = "El temps amb el que intentes eliminar la reserva del dia (".$dia0."-".$mes0."-".$any0." a les ".$hora_inici.":".$min_inici.") es de nom&eacute;s: ".gmdate("H", $hores_antelacio)."h:".gmdate("i", $hores_antelacio)."'";
			$f_tmp=sprintf("%s<br><br>%s<br><br>%s",E_ERR_DELETE_RESERVA_ANTELACIO,$taula,$temps_antelacio);
		    $tpl->assign("E_MOTIUS",$f_tmp);
		else:
			// Si es tenen permisos, procedim a esborrar la reserva indicada
			$query_periodics=@mysqli_query($conn,$consulta_periodics);
			$taula=mysqli_result_table($query_periodics);
			$consulta_delete="DELETE FROM tbl_reserves WHERE id_reserva=$id_reserva";
			@mysqli_query($conn,$consulta_delete);
			$f_tmp=sprintf("%s",$taula);
		    $tpl->assign("E_MOTIUS",$f_tmp);
			/*if (mysqli_affected_rows($conn) == 1):
				$tpl->assign("E_MOTIUS","");
				EliminarReserva_Projector($id_reserva);				
			else:
				$resultat=4;
				$tpl->assign("E_MOTIUS", E_ERR_DELETE_DB);
			endif;*/
		endif;
	else:
		$tpl->assign("E_MOTIUS", E_ERR_DELETE_NO_ADMIN);
		$resultat=1;
	endif;
	mysqli_close($conn);
	//Revisem si tenim mes registres de la consulta peri�dica degut a que tenim dies festiu o no lectius entre mig, i hem tingut que insertar m�s de un registre dintre de la BB.DD.
	if ($id_periodic>10 && $resultat==0):
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
		$consulta_id="SELECT * FROM tbl_reserves WHERE id_periodic=\"$id_periodic\"";
		$query_id=@mysqli_query($conn,$consulta_id);
		$num_rows = mysqli_num_rows($query_id);
		while ($row_t2 = mysqli_fetch_array($query_id, MYSQL_ASSOC)) {
			$data_inici=$row_t2["data_reserva"];
			$data_final=$row_t2["data_final"];
			$data_inici=$row_t2["data_reserva"];
			$hora_inici=$row_t2["hora_inici"];
			$hora_final=$row_t2["hora_final"];
			$id_reserva=$row_t2["id_reserva"];
			$a_date=dateToArray($data_inici);
			$any0=$a_date[0]; $mes0=$a_date[1]; $dia0=$a_date[2];
			$a_date=dateToArray($data_final);
			$any1=$a_date[0]; $mes1=$a_date[1]; $dia1=$a_date[2];
			$pos_hora_inici=stripos($hora_inici,':');
			$pos_hora_final=stripos($hora_final,':');
			$min_inici=substr($hora_inici,$pos_hora_inici+1);
			$hora_inici=substr($hora_inici,$p,$pos_hora_inici);
			$min_final=substr($hora_final,$pos_hora_final+1);
			$hora_final=substr($hora_final,$p,$pos_hora_final);
			if(time()<mktime($hora_inici,$min_inici,0,$mes0,$dia0,$any0)):
				$consulta_delete1="DELETE FROM tbl_reserves WHERE id_reserva=$id_reserva";
				@mysqli_query($conn,$consulta_delete1);
			elseif(mktime($hora_final,$min_final,0,$mes1,$dia1,$any1)<time()):
			elseif((mktime($hora_final,$min_final,0,$mes1,$dia1,$any1)>time())&&(mktime($hora_inici,$min_inici,0,$mes0,$dia0,$any0)<time())):
				$consulta_update="UPDATE tbl_reserves SET data_final=now() WHERE id_reserva=$id_reserva";
				@mysqli_query($conn,$consulta_update);
			endif;
		}
	endif;
	if($resultat==0):
		$tpl->assign("E_MSG_RESERVA", E_MSG_DEL_RESERVA_RSLT);
		if($enviar_correu):
			$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
			@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
			//Averiguem el responsable del recurs
			$consulta_email="SELECT re.id_tipus as id_tipus,
											u.email as email, u.email_fnb as email_fnb,
											re.nom_recurs as recurs
									FROM tbl_recursos re,
											tbl_usuaris u
									WHERE re.id_responsable=u.id_usuari AND
											re.id_recurs='$recurs'";
			//Averiguem l'usuari que demana el recurs
			$consulta_email_fnb="SELECT u.email as email, u.email_fnb as email_fnb
										FROM tbl_usuaris u
									WHERE u.nom_usuari='$qui'";

			$query_email_fnb=@mysqli_query($conn,$consulta_email_fnb);

			$query_email=@mysqli_query($conn,$consulta_email);
			if (mysqli_result($query_email,0,"id_tipus")=="1"):
				//Si cal avisar de les reserves del recurs de les aules docents, enviem email al responsable
				$from="No respondre <centre.calcul@fnb.upc.edu>";
				$to="gestio.academica@fnb.upc.edu";
				//$cc="cap.serveis@fnb.upc.edu";
				$cc1="<".mysqli_result($query_email_fnb,0,"email_fnb").">";
				$bcc="centre.calcul@fnb.upc.edu";
			elseif (mysqli_result($query_email,0,"id_tipus")=="3"):
				//Si cal avisar de les reserves del recurs de les sales de juntes i acte, enviem email al responsable
				$from="No respondre <centre.calcul@fnb.upc.edu>";
				//$to="cap.serveis@fnb.upc.edu";
				$to="secretaria.direccio@fnb.upc.edu";
				$cc1="<".mysqli_result($query_email_fnb,0,"email_fnb").">";
				$bcc="centre.calcul@fnb.upc.edu";
			elseif (mysqli_result($query_email,0,"id_tipus")=="5"):
				//Si cal avisar de les reserves del recurs de les sales de juntes i acte, enviem email al responsable
				$from="No respondre <centre.calcul@fnb.upc.edu>";
				//$to="cap.serveis@fnb.upc.edu";
				$to="jmateu@fnb.upc.edu";
				$cc1="<".mysqli_result($query_email_fnb,0,"email_fnb").">";
				$bcc="centre.calcul@fnb.upc.edu";
			elseif (mysqli_result($query_email,0,"id_tipus")=="6"):
				//Si cal avisar de les reserves del recurs de les sales de juntes i acte, enviem email al responsable
				$from="No respondre <centre.calcul@fnb.upc.edu>";
				//$to="cap.serveis@fnb.upc.edu";
				$to="aula.professional@fnb.upc.edu";
				$cc1="<".mysqli_result($query_email_fnb,0,"email_fnb").">";
				$bcc="centre.calcul@fnb.upc.edu";
			else:
				//Si cal avisar de les reserves del recurs de les sales de juntes i acte, enviem email al responsable
				$from="No respondre <centre.calcul@fnb.upc.edu>";
				$to="<".mysqli_result($query_email_fnb,0,"email_fnb").">";
				$cc="centre.calcul@fnb.upc.edu";
				//$cc1="cap.serveis@fnb.upc.edu";
				$bcc="";
			endif;
			//$to="smenendez@fnb.upc.edu";
			//$to=mysqli_result($query_email,0,"email");html_entity_decode(mysqli_result
			$subject="Reservator v.1.1: Eliminada reserva ".html_entity_decode('peri&ograve;dica', ENT_NOQUOTES, 'UTF-8')." de \"".html_entity_decode(mysqli_result($query_email,0,"recurs"), ENT_NOQUOTES, 'UTF-8')."\"";
			$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Si us plau, no el contesteu.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
			$body.="S'ha esborrat una reserva de \"".mysqli_result($query_email,0,"recurs")."\".<br><br>&nbsp;&nbsp;&nbsp;<b>Usuari</b>: $qui.<br>&nbsp;&nbsp;&nbsp;<b>Activitat</b>: $motiu.<br>&nbsp;&nbsp;&nbsp;<b>Data</b>: Tots els $nom_dia_setmana des del $data_inici al $data_final.<br>&nbsp;&nbsp;&nbsp;<b>Horari</b>: de $hora_inici a $hora_final.<p>";
			$signature="Centre de C&agrave;lcul FNB</font>";

			EnviarEmail($from, $to, $cc, $cc1, $bcc, $subject, $body, $signature, $err_msg);
			//mail($f_mail,,"L'usuari $qui ha fet una reserva de $f_recurs pel motiu: $motiu","From: reservator@$SERVER_NAME");
		endif;
	else:
		$tpl->assign("E_MSG_RESERVA", E_MSG_NO_DEL_RESERVA_RSLT);
	endif;

	$tpl->parse(PAGE_CONTENT,"reserva");
}

//-----------------------------------------------------------------------------------------------------------
// Procediment: EliminarDia_ReservaPeriodica()
// Operativa  : Elimina les dates de les reserves
//-----------------------------------------------------------------------------------------------------------
function EliminarDia_ReservaPeriodica($id_reserva,$recurs,$dia,$mes,$any){
	global $tpl, $hora_maxima, $hora_minima, $hores_antelacio_x_eliminar_reserva, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;

	$tpl->define(array(
			page    => "$template_dir/minipage.tpl",
			reserva => "$template_dir/eliminar_reserva.tpl"
	));

	$resultat=0;

	// Connectem amb el servidor i seleccionem la BD corresponent
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
	@mysqli_query($conn,"SET NAMES 'utf8'");

	//Comprovem que realment pot eliminar aquesta reserva
	$consulta="SELECT * 
				FROM tbl_recursos r,tbl_usuaris u
				WHERE r.id_responsable=u.id_usuari AND
				u.nom_usuari=\"$nom_usuari\" AND
				r.id_recurs=\"$recurs\"";

	$query=@mysqli_query($conn,$consulta);
	$consulta_aula="SELECT *
					FROM tbl_recursos
					WHERE id_recurs=$recurs";
				$query_aula=@mysqli_query($conn,$consulta_aula);
				$aula=mysqli_result($query_aula,0,"nom_recurs");
				$enviar_correu = mysqli_result($query_aula,0,"avisar_resp");
				$f_tipus_reserva=mysqli_result($query_aula,0,"id_tipus");
	$consulta_periodics="SELECT * 
					FROM tbl_reserves
					WHERE id_reserva=$id_reserva";

				$query_periodics=@mysqli_query($conn,$consulta_periodics);
				$qui=mysqli_result($query_periodics,0,"qui_reserva");
				$motiu=mysqli_result($query_periodics,0,"motiu_reserva");
				$aula=mysqli_result($query_periodics,0,"aula");
				$assig=mysqli_result($query_periodics,0,"assig");
				$data_inici=mysqli_result($query_periodics,0,"data_reserva");
				$data_final=mysqli_result($query_periodics,0,"data_final");
				$data_inici=mysqli_result($query_periodics,0,"data_reserva");
				$hora_inici=mysqli_result($query_periodics,0,"hora_inici");
				$hora_final=mysqli_result($query_periodics,0,"hora_final");
				$num_dia=mysqli_result($query_periodics,0,"num_dia");
				$tipus_reserva=mysqli_result($query_periodics,0,"tipus_reserva");
				$nom_dia_setmana=nomdia($num_dia);
				$pos_hora_inici=stripos($hora_inici,':');
				$min_inici=substr($hora_inici,$pos_hora_inici+1);
				$hora_inici=substr($hora_inici,0,$pos_hora_inici);

	$hores_antelacio = Calcul_x_hores_laborales($hora_inici,$min_inici,$dia,$mes,$any);
	if($query_periodics!=NULL):
		if(time()>mktime($hora_inici,$min_inici,0,$mes,$dia,$any)):
			$resultat=3;
			$query_periodics=@mysqli_query($conn,$consulta_periodics);
			$taula=mysqli_result_table($query_periodics);
			$f_tmp=sprintf("%s<br><br> %s",E_ERR_DELETE_DIA_RESERVA_PERIODICA,$taula);
		    $tpl->assign("E_MOTIUS",$f_tmp);
		elseif($hores_antelacio < $hores_antelacio_x_eliminar_reserva && !$_SESSION['perfil']=='CCESAII' && !(($_SESSION['perfil']=='Usuari Sales'&&($f_tipus_reserva=='3')) || ($_SESSION['perfil']=='Usuari NT3'&&($f_tipus_reserva=='6')))):
			$resultat=5;
			$query_periodics=@mysqli_query($conn,$consulta_periodics);
			$taula=mysqli_result_table($query_periodics);
			$temps_antelacio = "El temps amb el que intentes eliminar la reserva del dia (".$dia."-".$mes."-".$any." a les ".$hora_inici.":".$min_inici.") es de nom&eacute;s: ".gmdate("H", $hores_antelacio)."h:".gmdate("i", $hores_antelacio)."'";
			$f_tmp=sprintf("%s<br><br>%s<br><br>%s",E_ERR_DELETE_RESERVA_ANTELACIO,$taula,$temps_antelacio);
		    $tpl->assign("E_MOTIUS",$f_tmp);
		else:
			// Si es tenen permisos, procedim a modificar la reserva indicada, trencant-la en dues si hem de esborrar una data intermitja
			$inici=strtotime($data_inici);
			$final=strtotime($data_final);
			$menor_setmana=mktime(0,0,0,1,8,2000)-mktime(0,0,0,1,1,2000);
			$avui=mktime(0,0,0,$mes,$dia,$any);
			$data_avui=date("Y-m-d",$avui);
			$ahir=mktime(0,0,0,$mes,$dia-1,$any);
			$ultim_dia=date("Y-m-d",$ahir);
			$dema=mktime(0,0,0,$mes,$dia+1,$any);
			$seguent_dia=date("Y-m-d",$dema);
	
			if(($avui-$inici-$menor_setmana)<0):
				$consulta_update="UPDATE tbl_reserves SET data_reserva=\"$seguent_dia\" WHERE id_reserva=$id_reserva";
				@mysqli_query($conn,$consulta_update);
			elseif(($final-$avui-$menor_setmana)<0):
				$consulta_update="UPDATE tbl_reserves SET data_final=\"$ultim_dia\" WHERE id_reserva=$id_reserva";
				@mysqli_query($conn,$consulta_update);
			else:
				$consulta_update="UPDATE tbl_reserves SET data_final=\"$ultim_dia\" WHERE id_reserva=$id_reserva";
				@mysqli_query($conn,$consulta_update);
			endif;
				if (mysqli_affected_rows($conn) == 1):
					$tpl->assign("E_MOTIUS","");
				else:
					$resultat=4;
					$tpl->assign("E_MOTIUS", E_ERR_DELETE_DB);
				endif;
				
			if(($final-$avui-$menor_setmana)<0 || ($avui-$inici-$menor_setmana)<0):
				//Revisem que hagi quedat com reserva puntual
				if(($final-$avui-$menor_setmana)<0 && ($avui-$inici-$menor_setmana)<0):
					$query_periodics=@mysqli_query($conn,$consulta_periodics);
					$taula=mysqli_result_table($query_periodics);
					$consulta_delete="DELETE FROM tbl_reserves WHERE id_reserva=$id_reserva";
					@mysqli_query($conn,$consulta_delete);
					$f_tmp=sprintf("%s",$taula);
				    $tpl->assign("E_MOTIUS",$f_tmp);
					/*if (mysqli_affected_rows($conn) == 1):
						$tpl->assign("E_MOTIUS","");
						EliminarReserva_Projector($id_reserva);
					else:
						$resultat=4;
						$tpl->assign("E_MOTIUS", E_ERR_DELETE_DB);
					endif;*/
				endif;
				//En cas que hi hagi m�s de un dia a la reserva no fem res
			else:
				$hora_inici = $hora_inici.":".$min_inici;
				$consulta_insert="INSERT INTO tbl_reserves (id_recurs,qui_reserva,motiu_reserva,aula,assig,data_reserva,data_final,hora_inici,hora_final,num_dia,tipus_reserva)
				VALUES (\"$recurs\",\"$qui\",\"$motiu\",\"$aula\",\"$assig\",\"$seguent_dia\",\"$data_final\",\"$hora_inici\",\"$hora_final\",\"$num_dia\",\"$tipus_reserva\")";
				@mysqli_query($conn,$consulta_insert);
				if (mysqli_affected_rows($conn) == 1):
					$tpl->assign("E_MOTIUS","");
				else:
					$resultat=4;
					$tpl->assign("E_MOTIUS", E_ERR_DELETE_DB);
				endif;
			endif;
		endif;
	else:
		$tpl->assign("E_MOTIUS", E_ERR_DELETE_NO_ADMIN);
		$resultat=1;
	endif;

	if($resultat==0):
		$tpl->assign("E_MSG_RESERVA", E_MSG_DEL_RESERVA_RSLT);
		if($enviar_correu):
			$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
			@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
			//Averiguem el responsable del recurs
			$consulta_email="SELECT re.id_tipus as id_tipus,
											u.email as email, u.email_fnb as email_fnb,
											re.nom_recurs as recurs
									FROM tbl_recursos re,
											tbl_usuaris u
									WHERE re.id_responsable=u.id_usuari AND
											re.id_recurs='$recurs'";
			//Averiguem l'usuari que demana el recurs
			$consulta_email_fnb="SELECT u.email as email, u.email_fnb as email_fnb
										FROM tbl_usuaris u
									WHERE u.nom_usuari='$qui'";

			$query_email_fnb=@mysqli_query($conn,$consulta_email_fnb);

			$query_email=@mysqli_query($conn,$consulta_email);
			if (mysqli_result($query_email,0,"id_tipus")=="1"):
				//Si cal avisar de les reserves del recurs de les aules docents, enviem email al responsable
				$from="No respondre <centre.calcul@fnb.upc.edu>";
				$to="gestio.academica@fnb.upc.edu";
				//$cc="cap.serveis@fnb.upc.edu";
				$cc1="<".mysqli_result($query_email_fnb,0,"email_fnb").">";
				$bcc="centre.calcul@fnb.upc.edu";
			elseif (mysqli_result($query_email,0,"id_tipus")=="3"):
				//Si cal avisar de les reserves del recurs de les sales de juntes i acte, enviem email al responsable
				$from="No respondre <centre.calcul@fnb.upc.edu>";
				//$to="cap.serveis@fnb.upc.edu";
				$to="secretaria.direccio@fnb.upc.edu";
				$cc1="<".mysqli_result($query_email_fnb,0,"email_fnb").">";
				$bcc="centre.calcul@fnb.upc.edu";
			elseif (mysqli_result($query_email,0,"id_tipus")=="6"):
				//Si cal avisar de les reserves del recurs de les sales de juntes i acte, enviem email al responsable
				$from="No respondre <centre.calcul@fnb.upc.edu>";
				//$to="cap.serveis@fnb.upc.edu";
				$to="aula.professional@fnb.upc.edu";
				$cc1="<".mysqli_result($query_email_fnb,0,"email_fnb").">";
				$bcc="centre.calcul@fnb.upc.edu";
			else:
				//Si cal avisar de les reserves del recurs de les sales de juntes i acte, enviem email al responsable
				$from="No respondre <centre.calcul@fnb.upc.edu>";
				$to="centre.calcul@fnb.upc.edu";
				$cc="";
				$cc1="<".mysqli_result($query_email_fnb,0,"email_fnb").">";
				$bcc="";
			endif;
			//$to="smenendez@fnb.upc.edu";
			//$to=mysqli_result($query_email,0,"email");
			$subject="Reservator v.1.1: Eliminat dia de la reserva ".html_entity_decode('peri&ograve;dica', ENT_NOQUOTES, 'UTF-8')." de \"".html_entity_decode(mysqli_result($query_email,0,"recurs"), ENT_NOQUOTES, 'UTF-8')."\"";
			$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Si us plau, no el contesteu.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
			$body.="S'ha esborrat una reserva de \"".mysqli_result($query_email,0,"recurs")."\".<br><br>&nbsp;&nbsp;&nbsp;<b>Usuari</b>: $qui.<br>&nbsp;&nbsp;&nbsp;<b>Activitat</b>: $motiu.<br>&nbsp;&nbsp;&nbsp;<b>Data</b>: Eliminat el dia $nom_dia_setmana $dia-$mes-$any.<br>&nbsp;&nbsp;&nbsp;<b>Horari</b>: de $hora_inici a $hora_final.<p>";
			$signature="Centre de C&agrave;lcul FNB</font>";

			EnviarEmail($from, $to, $cc, $cc1, $bcc, $subject, $body, $signature, $err_msg);
			//mail($f_mail,,"L'usuari $qui ha fet una reserva de $f_recurs pel motiu: $motiu","From: reservator@$SERVER_NAME");
		endif;
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
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, 'reserves') or die(E_ERR_SELECT_DB);

	//Comprovem que realment pot eliminar aquesta reserva
	$consulta="SELECT * FROM tbl_reserves WHERE id_usuari=$id_reserva";

	$query2=@mysqli_query($conn,$consulta);

	if($query2!=NULL):
		// Si es tenen permisos, procedim a esborrar la reserva indicada
		$consulta_delete="DELETE FROM tbl_reserves WHERE id_usuari=$id_reserva";
		@mysqli_query($conn,$consulta_delete);
		if ((mysqli_affected_rows($conn) == 1)||(mysqli_affected_rows($conn) == 0)):
			$tpl->assign("E_MOTIUS","");
		else:
			$resultat=4;
			$tpl->assign("E_MOTIUS", E_ERR_DELETE_DB);
		endif;
	
	endif;

	if($resultat==0):
		$tpl->assign("E_MSG_RESERVA", E_MSG_DEL_RESERVA_RSLT);
	else:
		$tpl->assign("E_MSG_RESERVA", E_MSG_NO_DEL_RESERVA_RSLT);
	endif;

	$tpl->parse(PAGE_CONTENT,"reserva");
}

//-----------------------------------------------------------------------------------------------------------
// Procediment: GestioReservaPuntual()
// Operativa  : Arriben els paramentres d'una reserva i cal comprovar que sigui valida
//-----------------------------------------------------------------------------------------------------------
function GestioReservaPuntual($dia,$mes,$any,$recurs,$qui,$motiu,$hora_inici,$hora_final,$assig,$avis_legal,$projector){
	global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/gestio_reserva.tpl"
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
	if($avis_legal) $uid = utf8_encode(E_AVIS_LEGAL_INFO); else $uid = NULL;

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
			"E_ASSIG"			   => E_ASSIG

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
		$uid = stripslashes($uid);
		$tpl->assign(array(
			"DATA_RESERVA"	=> $dia."-".$mes."-".$any,
			"DATA_FINAL" 	=> $dia."-".$mes."-".$any,
			"HORA_INICI" 	=> $hora_inici,
			"HORA_FINAL" 	=> $hora_final,
			"QUI" 			=> urldecode($qui),
			"EMAIL" 		=> $email,
			"MOTIU" 		=> urldecode($motiu),
			"AVIS" 			=> $uid,
			"DIA_TXT" 		=> $dia_txt,
			"ASSIG" 		=> urldecode($assig),
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
// Procediment: GestioReservaPeriodica()
// Operativa  : Arriben els paramentres d'una reserva i cal comprovar que sigui valida
//-----------------------------------------------------------------------------------------------------------
function GestioReservaPeriodica($data_inici,$data_final,$dia,$recurs,$qui,$motiu,$hora_inici,$hora_final,$assig,$avis_legal,$projector){
	global $tpl, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;

	$tpl->define(array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/gestio_reserva_periodica.tpl"
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
	if($avis_legal) $uid = utf8_encode(E_AVIS_LEGAL_INFO); else $uid = NULL;
	
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
			"E_ASSIG"			   => E_ASSIG

		));

		switch ($dia):
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
			"DATA_RESERVA"	=> $data_inici,
			"DATA_FINAL" 	=> $data_final,
			"HORA_INICI" 	=> $hora_inici,
			"HORA_FINAL" 	=> $hora_final,
			"QUI" 			=> urldecode($qui),
			"EMAIL" 		=> $email,
			"MOTIU" 		=> urldecode($motiu),
			"AVIS" 			=> $uid,
			"DIA_TXT" 		=> $dia_txt,
			"DIA"	 		=> $dia,
			"ASSIG" 		=> urldecode($assig),
			"DIA" 			=> $dia,
			"MES" 			=> $mes,
			"ANY" 			=> $any,
			"RECURS" 		=> $recurs,
			"RESPONSABLE"	=> mysqli_result($query,0,"nom_usuari"),
			"DESC_RECURS"   => mysqli_result($query,0,"nom_recurs")
		));
	
	$tpl->parse(PAGE_CONTENT,"reserva");
}

?>