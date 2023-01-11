<?php
/*error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);*/

//-----------------------------------------------------------------------------------------------------------
// Procediment: PintarMenu()
// Operativa  : S'encarrega de pintar les opcions de menu en la plantilla.
//              El menu consta de dues parts (part comuna i part d'administracio)
//              Tothom veu la part comuna i nomes els que tenen perfil CCESAII podran veure la
//              part administracio
//-----------------------------------------------------------------------------------------------------------
function PintarMenu(){
	global $tpl, $template_dir;

	$tpl->define( array(
		page => "$template_dir/page.tpl",
		menu => "$template_dir/menu.tpl"
	));
	$recurs = $_REQUEST['recurs'];
	$op = $_REQUEST['op'];
	$dias = $_REQUEST['p_dia'];
	$mess = $_REQUEST['mes'];
	$anys = $_REQUEST['any'];

	// Construim la part de menu d'administracio (nomes visible si el perfil es CCESAII)
	if ($_SESSION['perfil']=="CCESAII"):
		extract(Calcula_dilluns_actual());
		$menu_admin="<a href=\"javascript:openpopup('$PHP_SELF?op=2')\">Alta Usuaris</a> -
			<a href='http://www.fnb.upc.edu/?q=node/819'>Llistat d'aules</a> -
			<a href='$PHP_SELF?op=40'>Llistat recursos</a>";
		if($op==35): $menu_admin .= " - <a href='http://www.fnb.upc.edu/intrafnb/llistat/llistat_aula_setmanal_xlsx.php?dia=$dias&mes=$mess&any=$anys&recurs=$recurs'>".utf8_encode('Ocupaci� Aula')."</a>
									 - <a href='http://www.fnb.upc.edu/intrafnb/llistat/llistat_aules_setmanal_xlsx.php?dia=$dias&mes=$mess&any=$anys'>".utf8_encode('Ocupaci� Aules')."</a>"; endif;
	else:
		$menu_admin="<a href='http://www.fnb.upc.edu/?q=node/819'>Llistat d'Aules</a> - 
		<a href=\"javascript:finestra('http://www.fnb.upc.edu/?q=node/827')\">Ocupaci&oacute; Aula Inform&agrave;tica</a>";
	endif;
	// Construim la part del menu comu a tothom
	if ($_SESSION['perfil']=="CCESAII"):
		extract(Calcula_dilluns_actual());
		$menu_comu="<a target='_blank' href='./Reservator11-v10.pdf'>Ajuda</a> -
							<a href=\"$PHP_SELF\">".E_TRIA_RECURS."</a> -
							<a href=\"javascript:finestraTotal('$PHP_SELF?op=35&mes=$mes&any=$any&recurs=0&p_dia=$p_dia&u_dia=$u_dia')\">Sales d'Actes i Juntes</a> -
							<a href=\"$PHP_SELF?&op=100\">".E_LOGOUT."</a>";
	else:
		extract(Calcula_dilluns_actual());
		$menu_comu="<a target='_blank' href='./Reservator11-v10.pdf'>Ajuda</a> -
							<a href=\"$PHP_SELF\">".E_TRIA_RECURS."</a> - 
							<a href=\"javascript:finestraTotal('$PHP_SELF?op=35&mes=$mes&any=$any&recurs=0&p_dia=$p_dia&u_dia=$u_dia')\">Sales d'Actes i Juntes</a> - 
							<a href=\"$PHP_SELF?&op=100\">".E_LOGOUT."</a>";
	endif;
	//Anulem els menus en cas de fer un llistat d'ocupaci� que no necessita autentificaci�
	if ($_SESSION['nom_usuari']==""):
		extract(Calcula_dilluns_actual());
		$_SESSION['nom_usuari']="Usuari per consulta";
		$menu_comu="";
	else:
		//$menu_comu="<a href=\"$PHP_SELF?&op=100\">".E_LOGOUT."</a>";
	endif;
	
	$tpl->assign(array(
		"USERNAME"   => "<font color=\"Maroon\"><b>Usuari registrat:</b> ". $_SESSION['nom_usuari']."</font>",
		"MENU_COMU"  => $menu_comu,
		"MENU_ADMIN" => $menu_admin
	));

	$tpl->parse(MENU,"menu");
}

function PintarMenu_Consulta(){
	global $tpl, $template_dir;

	$tpl->define( array(
		page => "$template_dir/page_old.tpl",
		menu => "$template_dir/menu.tpl"
	));

	$tpl->parse(MENU,"menu");

}

function send_resource($nom_recurs,$descripcio,$responsable){
	global $tpl, $hora_maxima, $hora_minima ,$dbname, $dbuser, $dbpass, $dbserver, $lang, $locales, $template_dir, $enviar_correu;

	$tpl->define( array(
		page    => "$template_dir/minipage.tpl",
		reserva => "$template_dir/aceptar_recurs.tpl"
	));

	if ($nom_recurs==""):
		$tpl->assign("E_MSG_RESERVA", E_MSG_NO_ADD_RESOURCE_RSLT);
		$tpl->assign("E_MOTIUS", E_MSG_ADD_RESOURCE_NOM);
	elseif ($descripcio==""):
		$tpl->assign("E_MSG_RESERVA", E_MSG_ADD_RESOURCE_RSLT);
		$tpl->assign("E_MOTIUS", E_MSG_ADD_RESOURCE_DESCRIPCIO);
	elseif ($responsable==""):
		$tpl->assign("E_MSG_RESERVA", E_MSG_NO_ADD_RESOURCE_RSLT);
		$tpl->assign("E_MOTIUS", E_MSG_ADD_RESOURCE_RESPONSABLE);
	else:
		// Tot ha anat be. Enviem la peticio de nou recurs
		$from="";
		$to="centrecalcul@fnb.upc.edu";
		$cc="";
		$cc1="";
		$bcc="";
		$subject="Reservator v1.1: Alta Nou Recurs ($nom_recurs)";
		$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Si us plau, no el contesteu.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
		$body.="S'ha demanat la creaci&oacute; d'un nou recurs compartit:.<br><br><table><tr><td><b>Nom recurs: &nbsp;</b></td><td>$nom_recurs</td></tr><tr><td><b>Descripci&oacute;: &nbsp;</b></td><td>".nl2br(htmlentities($descripcio))."</td></tr><tr><td><b>Responsable: &nbsp;</b></td><td>$responsable</td></tr></table><p>";
		$signature="Centre de C&agrave;lcul FNB</font>";
		EnviarEmail($from, $to, $cc, $cc1, $bcc, $subject, $body, $signature, $err_msg);

		$tpl->assign("E_MSG_RESERVA", E_MSG_ADD_RESOURCE_RSLT);
		$tpl->assign("E_MOTIUS", E_MSG_ADD_RESOURCE_PACIENCIA);
    endif;

	$tpl->parse(PAGE_CONTENT,"reserva");
}

function accept_login($login,$password){
	global $dbname;
	global $dbuser;
	global $dbpass;
	global $dbserver;
	global $lang;
	global $locales;
	// Encriptem el password rebut

	$f_password=crypt($password,$login);

	$conn = @mysqli_connect($dbserver,$dbuser,$dbpass) or printf(E_ERR_BD_CONNECT);

	//Seleccionem la BD corresponent
	@mysqli_select_db($conn, $dbname) or printf(E_ERR_SELECT_BD);

	// Ara llegim les diferents reserves que hi ha d'aquell recurs per aquella hora
	$consulta="SELECT * FROM tbl_usuaris WHERE username=\"$login\" AND password=\"$f_password\"";

	$query=@mysqli_query($conn,$consulta);

	if(mysqli_num_rows($query)==1){
		if(mysqli_result($query, 0, "disuser")==0){
			$_SESSION['r_username']=mysqli_result($query, 0, "username");
			$_SESSION['r_password']=mysqli_result($query, 0, "password");
			$_SESSION['r_id_usuari']=mysqli_result($query, 0, "id_usuari");
			$_SESSION['r_nom_usuari']=mysqli_result($query, 0, "nom_usuari");
			$_SESSION['r_email']=mysqli_result($query, 0, "email");
			$_SESSION['r_tipus']=mysqli_result($query, 0, "tipus");
			$responsable=$_SESSION['r_id_usuari'];
			return 0;
		}
	}
	return 1;
}

function accept_chg_password($password,$password_again){
	global $dbname;
	global $dbuser;
	global $dbpass;
	global $dbserver;
	global $lang;
	global $locales;

	if($password==$password_again){
		//Acceptem el password
		$f_password=crypt($password,$_SESSION['r_username']);
		$_SESSION['r_password']=$f_password;
		$conn = @mysqli_connect($dbserver,$dbuser,$dbpass) or printf(E_ERR_BD_CONNECT);

		//Seleccionem la BD corresponent
		@mysqli_select_db($conn, $dbname) or printf(E_ERR_SELECT_BD);

		// Ara llegim les diferents reserves que hi ha d'aquell recurs per aquella hora
		$consulta=sprintf("UPDATE tbl_usuaris SET password=\"$f_password\" WHERE id_usuari=%s",$_SESSION['r_id_usuari']);
		//echo $consulta;
		$query=@mysqli_query($conn,$consulta);
		if (mysqli_affected_rows($conn) == 1) {
			return 0;
			//echo $f_password;
		}
	}
	//No acceptem el password
	return 1;


}

//funcion accept_logout(

function PrintPage($title,$res_login) {
	global $tpl;
	global $lang;
	global $locales;
	
	$tpl->assign("PAGE_TITLE", $title);
	$f_scripts='<script language="javascript" src="script/codethatcalendarstd.js"></script>';
	$f_scripts=sprintf("%s\n<script language=\"javascript\" src=\"script/scroller_ex%s.js\"></script>",$f_scripts,$lang);
	$f_scripts2='<script language="javascript">var c1 = new CodeThatCalendar(caldef1);</script>';
	$f_scripts3='<script language="javascript" src="script/scripts.js"></script>';
	if($res_login==1):
		$f_scripts4=sprintf("<script language=\"javascript\">alert(\"%s\");</script>", E_ERR_LOGIN);
	elseif ($res_login==2):
		$f_scripts4=sprintf("<script language=\"javascript\">alert(\"%s\");</script>", E_ERR_PASSWORD_NOT_SAME);
	else:
		$f_scripts4="";
	endif;
	
	$tpl->assign("PAGE_SCRIPTS","$f_scripts\n$f_scripts2\n$f_scripts3\n$f_scripts4");
	$tpl->parse(FINAL1, "page");
	$tpl->FastPrint(FINAL1);
}

function pagescripts($title,$res_login) {
	global $tpl;
	global $lang;
	global $locales;
	
	$tpl->assign("PAGE_TITLE", $title);
	$f_scripts='<script language="javascript" src="script/codethatcalendarstd.js"></script>';
	$f_scripts=sprintf("%s\n<script language=\"javascript\" src=\"script/scroller_ex%s.js\"></script>",$f_scripts,$lang);
	$f_scripts2='<script language="javascript">var c1 = new CodeThatCalendar(caldef1);</script>';
	$f_scripts3='<script language="javascript" src="script/scripts.js"></script>';
	if($res_login==1):
		$f_scripts4=sprintf("<script language=\"javascript\">alert(\"%s\");</script>", E_ERR_LOGIN);
	elseif ($res_login==2):
		$f_scripts4=sprintf("<script language=\"javascript\">alert(\"%s\");</script>", E_ERR_PASSWORD_NOT_SAME);
	else:
		$f_scripts4="";
	endif;

	$tpl->assign("PAGE_SCRIPTS","$f_scripts\n$f_scripts2\n$f_scripts3\n$f_scripts4");
	$tpl->parse(FINAL1, "page");
	//$tpl->FastPrint(FINAL);
}



function PintarVariablesRegistrades(){
	global $tpl, $template_dir;

	$tpl->define(array(
		page   => "$template_dir/minipage.tpl"
	));

	// Composem el texte que volem pintar per a mostrar les variables
	$texte ="Nom: ".$_SESSION['nom_usuari']."<br>";
	$texte.="Email: ".$_SESSION['email']."<br>";
	$texte.="Departament: ".$_SESSION['departament']."<br>";
	$texte.="Manager: ".$_SESSION['pac_pas']."<br>";
	$texte.="UID: ".$_SESSION['uid']."<br>";
	$texte.="Perfil: ".$_SESSION['perfil']."<br>";

	$tpl->assign(PAGE_CONTENT, "<h4>Variables registrades en el sistema</h4>$texte<p>&nbsp;</p><input type=button value='&nbsp;&nbsp;&nbsp;Tancar finestra&nbsp;&nbsp;&nbsp;' onclick='javascript:window.close()'>");
}


//-----------------------------------------------------------------------------------------------------------
// Procediment: EnviarEmail()
// Operativa  : Envia un missatge de correu electronic amb les caracteristiques indicades
//-----------------------------------------------------------------------------------------------------------
function EnviarEmail($from, $to, $cc, $cc1, $bcc, $subject, $body, $signature, $err_msg)
{
	// Nomes enviem el missatge si el cos del missatge no esta buit i hi ha destinatari
	if (($body<>"") &&(($to<>"") || ($cc<>"") || ($bcc<>""))):
		// Muntem la capcalera del missatge
		if ($from=="") $from="No respondre <centre.calcul@fnb.upc.edu>";
		$hdrEmail .= "MIME-Version: 1.0" . "\r\n";
		$hdrEmail .= "Content-type: text/html; charset=UTF-8" . "\r\n";
		$hdrEmail .= "From: ".$from."\n";
		if ($cc<>"") $hdrEmail .= "Cc: ".$cc."\n";
		if ($bcc<>"") $hdrEmail .= "Bcc: ".$bcc."\n";
		if ($cc1<>"") $hdrEmail .= "Cc: ".$cc1."\n";

		// Muntem el cos del missatge
		$bodyEmail="<p>$body</p>";
		if ($signature<>"") $bodyEmail.="---<br>".$signature;

		// Enviem el correu
		@mail($to, $subject, $bodyEmail, $hdrEmail) or die ($err_msg);
		
		return TRUE;
	else:
		return FALSE;
	endif;
}


//-----------------------------------------------------------------------------------------------------------
// Procediment: EnviarCorreuPeticioReserva();
// Operativa  : Enviem un correu de petici� de reserva a la Secretaria de Direcci� i al usuari.
//-----------------------------------------------------------------------------------------------------------
function EnviarCorreuPeticioReserva($dia,$mes,$any,$recurs,$qui,$assig,$motiu,$hora_inici,$hora_final,$aula,$avis_legal, $f_tipus_reserva){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir, $enviar_correu, $url_reserves;
	
	if($avis_legal) $uid = utf8_encode(E_AVIS_LEGAL_INFO);
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
	$consulta_email="SELECT email,email_fnb,nom_usuari
							FROM tbl_usuaris
							WHERE nom_usuari='$qui'";
	
	$query_email=@mysqli_query($conn,$consulta_email);
	//Si cal avisar de les reserves del recurs, enviem email al responsable
	$from="Reserva Sales<".mysqli_result($query_email,0,"email").">";
	$assig = stripslashes($assig);
	$motiu = stripslashes($motiu);
	$data = date("Y-m-d H:i:s");
	$peticio = array('dia'=>$dia, 'mes'=>$mes, 'any'=>$any, 'recurs'=>$recurs, 'qui'=>$qui, 'assig'=>$assig, 'motiu'=>$motiu, 'hora_inici'=>$hora_inici, 'hora_final'=>$hora_final,
	    'aula'=>$aula, 'avis_legal'=>$avis_legal, 'f_tipus_reserva'=>$f_tipus_reserva);
	$peticio_json = json_encode($peticio);
	$consulta_insert="INSERT INTO tbl_peticions (peticions,user,data) VALUES ('".$peticio_json."','".$qui."','".$data."')";
	$consulta_id="SELECT id FROM tbl_peticions ORDER BY id DESC";
	$result_id=@mysqli_query($conn,$consulta_id);
	$row_id = mysqli_fetch_array($result_id);
	//Si cal avisar de les reserves del recurs, enviem email a
	$query_insert=@mysqli_query($conn,$consulta_insert);
	
	if ($f_tipus_reserva==3):
		//$to="smenendez@fnb.upc.edu";
		$to="secretaria.direccio@fnb.upc.edu";
		//$cc="cap.serveis@fnb.upc.edu";
		$cc1="<".mysqli_result($query_email,0,"email_fnb").">";
		$bcc="centre.calcul@fnb.upc.edu";
		
	elseif ($f_tipus_reserva==2):
		$to="<".mysqli_result($query_email,0,"email_fnb").">";
		//$cc="cap.serveis@fnb.upc.edu";
		$cc1="centre.calcul@fnb.upc.edu";
		$bcc="";
		
	elseif ($f_tipus_reserva==6):
		$to="aula.professional@fnb.upc.edu";
		//$cc="cap.serveis@fnb.upc.edu";
		$cc1="<".mysqli_result($query_email,0,"email_fnb").">";
		//$bcc="centre.calcul@fnb.upc.edu";
		
	else:
		$to="gestio.academica@fnb.upc.edu";
		//$cc="cap.serveis@fnb.upc.edu";
		$cc1="<".mysqli_result($query_email,0,"email_fnb").">";
		$bcc="centre.calcul@fnb.upc.edu";
		
	endif;
	$subject="".html_entity_decode('Petici&oacute;', ENT_NOQUOTES, 'UTF-8')." de reserva dels Espais Docents de \"".html_entity_decode(mysqli_result($query_email,0,"nom_usuari"))."\"";
	$body="<b>Missatge generat autom&agrave;ticament. Aviat rebreu confirmaci&oacute; de la vostra petici&oacute;.</b><br>";
	$body.="S'ha fet una petici&oacute; de reserva de \"".mysqli_result($query_email,0,"nom_usuari")."\".<br><br><b>Usuari</b>: $qui.<br><b>Activitat/Assignatura</b>: $assig.<br><b>Motiu</b>: $motiu.<br><b>Dia</b>: $dia/$mes/$any.<br><b>Horari</b>: de $hora_inici a $hora_final.<br><b>Lloc</b>: $aula.<br><b>Comentari</b>: $uid.";
	//$body.="<p>Clicar <a href=\"$url_reserves?dia=$dia&mes=$mes&any=$any&recurs=$recurs&qui=$qui&assig=$assig&motiu=$motiu&hora_inici=$hora_inici&hora_final=$hora_final&aula=$aula&uid=$avis_legal&projector=$projector&f_tipus_reserva=$f_tipus_reserva&op=60\">aqui</a> per validar la reserva.</p>";
	$params = utf8_encode($url_reserves.'?dia='.$dia.'&mes='.$mes.'&any='.$any.'&recurs='.$recurs.'&qui='.$qui.'&assig='.utf8_decode($assig).'&motiu='.utf8_decode($motiu).'&hora_inici='.$hora_inici.'&hora_final='.$hora_final.'&aula='.utf8_decode($aula).'&uid='.$avis_legal.'&projector='.$projector.'&f_tipus_reserva='.$f_tipus_reserva.'&op=60');
	$body.="<p>Clicar <a href=\"$params\">aqui</a> per validar la reserva. <em><b><small>".html_entity_decode('Opci&oacute; v&agrave;lida nom&eacute;s pel gestor que realitza la validació de la petici&oacute;')."</small></b></em></p>";
	$params = utf8_encode($url_reserves.'?id='.$row_id["id"].'&op=48');
	$body.="<p><a href=\"$params\">Info pels Serveis TIC FNB</a> per validar la reserva. <em><b><small>".html_entity_decode('Opci&oacute; v&agrave;lida nom&eacute;s pels Serveis TIC que realitza la validaci&oacute; de la petici&oacute;')."</small></b></em></p>";
	$signature="Centre de C&agrave;lcul de la FNB</font>";
	
	EnviarEmail($from, $to, $cc, $cc1, $bcc, $subject, $body, $signature, $err_msg);
	//mail($f_mail,,"L'usuari $qui ha fet una reserva de $f_recurs pel motiu: $motiu","From: reservator@$SERVER_NAME");
	
	$peticio = array('dia'=>$dia, 'mes'=>$mes, 'any'=>$any, 'recurs'=>$recurs, 'qui'=>$qui, 'assig'=>$assig, 'motiu'=>$motiu, 'hora_inici'=>$hora_inici, 'hora_final'=>$hora_final,
                    'aula'=>$aula, 'avis_legal'=>$avis_legal, 'f_tipus_reserva'=>$f_tipus_reserva);
	$peticio_json = json_encode($peticio);
}


//-----------------------------------------------------------------------------------------------------------
// Procediment: EnviarCorreuPeticioReservaPeriodica();
// Operativa  : Enviem un correu de petici� de reserva a la Secretaria de Direcci� i al usuari.
//-----------------------------------------------------------------------------------------------------------
function EnviarCorreuPeticioReservaPeriodica($dia0,$mes0,$any0,$dia1,$mes1,$any1,$recurs,$qui,$assig,$motiu,$hora_inici,$hora_final,$aula,$avis_legal,$dia,$f_tipus_reserva){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir, $enviar_correu, $url_reserves;
	
	if($avis_legal) $uid = utf8_encode(E_AVIS_LEGAL_INFO);
	switch($dia){
		case 7: $dia_setmana = DIUMENGE;
				break;
		case 1: $dia_setmana = DILLUNS;
				break;
		case 2: $dia_setmana = DIMARTS;
				break;
		case 3: $dia_setmana = DIMECRES;
				break;
		case 4: $dia_setmana = DIJOUS;
				break;
		case 5: $dia_setmana = DIVENDRES;
				break;
		case 6: $dia_setmana = DISSABTE;
				break;
		}
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
	$consulta_email="SELECT email,nom_usuari,email_fnb
							FROM tbl_usuaris
							WHERE nom_usuari='$qui'";
	
	$query_email=@mysqli_query($conn,$consulta_email);
	//Si cal avisar de les reserves del recurs, enviem email al responsable
	$from="Reserva Sales<".mysqli_result($query_email,0,"email").">";
	$assig = stripslashes($assig);
	$motiu = stripslashes($motiu);
	if ($f_tipus_reserva==3):
		$to="secretaria.direccio@fnb.upc.edu";
		//$cc="cap.serveis@fnb.upc.edu";
		$cc1="<".mysqli_result($query_email,0,"email_fnb").">";
		$bcc="centre.calcul@fnb.upc.edu";
		
	elseif ($f_tipus_reserva==2):
		$to="centre.calcul@fnb.upc.edu";
		//$cc="cap.serveis@fnb.upc.edu";
		$cc1="<".mysqli_result($query_email,0,"email_fnb").">";
		$bcc="";
		
	elseif ($f_tipus_reserva==6):
		$to="aula.professional@fnb.upc.edu";
		//$cc="cap.serveis@fnb.upc.edu";
		$cc1="<".mysqli_result($query_email,0,"email_fnb").">";
		$bcc="centre.calcul@fnb.upc.edu";
		
	else:
		$to="gestio.academica@fnb.upc.edu";
		//$cc="cap.serveis@fnb.upc.edu";
		$cc1="<".mysqli_result($query_email,0,"email_fnb").">";
		$bcc="centre.calcul@fnb.upc.edu";
		
	endif;
	$subject="".html_entity_decode('Petici&oacute;', ENT_NOQUOTES, 'UTF-8')." de reserva ".html_entity_decode('peri&oacute;dica', ENT_NOQUOTES, 'UTF-8')." dels Espais Docents de \"".html_entity_decode(mysqli_result($query_email,0,"nom_usuari"))."\"";
	$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Aviat rebreu confirmaci&oacute; de la vostra petici&oacute;.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
	$body.="S'ha fet una petici&oacute; de reserva de \"".mysqli_result($query_email,0,"nom_usuari")."\".<br><br>&nbsp;&nbsp;&nbsp;<b>Usuari</b>: $qui.<br>&nbsp;&nbsp;&nbsp;<b>Activitat/Assignatura</b>: $assig.<br>&nbsp;&nbsp;&nbsp;<b>Motiu</b>: $motiu.<br>&nbsp;&nbsp;&nbsp;<b>Data inici</b>: $dia0/$mes0/$any0.<br>&nbsp;&nbsp;&nbsp;<b>Data final</b>: $dia1/$mes1/$any1.<br>&nbsp;&nbsp;&nbsp;<b>Horari</b>: $dia_setmana de $hora_inici a $hora_final.<br>&nbsp;&nbsp;&nbsp;<b>Lloc</b>: $aula.<br>&nbsp;&nbsp;&nbsp;<b>Comentari</b>: $uid<p>";
	//$body.="<p>Clicar <a href=\"$url_reserves?data_inici=".$dia0."/".$mes0."/".$any0."&data_final=".$dia1."/".$mes1."/".$any1."&dia=$dia&recurs=$recurs&qui=$qui&assig=$assig&motiu=$motiu&hora_inici=$hora_inici&hora_final=$hora_final&aula=$aula&uid=$avis_legal&projector=$projector&f_tipus_reserva=$f_tipus_reserva&op=61\">aqui</a> per validar la reserva.</p>";
	$params = utf8_encode($url_reserves.'?data_inici='.$dia0.'/'.$mes0.'/'.$any0.'&data_final='.$dia1.'/'.$mes1.'/'.$any1.'&dia='.$dia.'&recurs='.$recurs.'&hora_inici='.$hora_inici.'&hora_final='.$hora_final.'&qui='.$qui.'&assig='.utf8_decode($assig).'&motiu='.utf8_decode($motiu).'&aula='.utf8_decode($aula).'&uid='.$avis_legal.'&projector='.$projector.'&f_tipus_reserva='.$f_tipus_reserva.'&op=61');
	$body.="<p>Clicar <a href=\"$params\">aqui</a> per validar la reserva. <em><b><small>".utf8_encode('Opci� v�lida nom�s pel gestor que realitza la validaci� de la petici�')."</small></b></em></p>";
	$signature="Centre de C&agrave;lcul de la FNB</font>";
	
	EnviarEmail($from, $to, $cc, $cc1, $bcc, $subject, $body, $signature, $err_msg);
	//mail($f_mail,,"L'usuari $qui ha fet una reserva de $f_recurs pel motiu: $motiu","From: reservator@$SERVER_NAME");
}

//-----------------------------------------------------------------------------------------------------------
// Procediment: EnviarCorreuDenegacioReserva();
// Operativa  : Enviem un correu de petici� de reserva a la Secretaria de Direcci� i al usuari.
//-----------------------------------------------------------------------------------------------------------
function EnviarCorreuDenegacioReserva($dia,$mes,$any,$recurs,$qui,$assig,$motiu,$hora_inici,$hora_final,$aula,$projector, $f_tipus_reserva,$motius){
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
	$from="Reserva Sales<".mysqli_result($query_email,0,"email").">";
	$assig = stripslashes($assig);
	$motiu = stripslashes($motiu);
	if ($f_tipus_reserva==3):
		$to="secretaria.direccio@fnb.upc.edu";
		//$cc="cap.serveis@fnb.upc.edu";
		$cc1="<".mysqli_result($query_email,0,"email_fnb").">";
		$bcc="centre.calcul@fnb.upc.edu";
		$subject="Descartada la ".html_entity_decode('petici&oacute;dica', ENT_NOQUOTES, 'UTF-8')." de reserva de la sala de Juntes o Actes de \"".html_entity_decode(mysqli_result($query_email,0,"nom_usuari"))."\"";
		//$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Aviat rebreu confirmaci&oacute; de la vostra petici&oacute;.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
		$body="<b>Missatge generat autom&agrave;ticament.</b><br>";
		$body.="S'ha descartat la vostra petici&oacute; de reserva de \"".mysqli_result($query_email,0,"nom_usuari")."\".<br><br><b>Usuari</b>: $qui.<br><b>Activitat/Assignatura</b>: $assig.<br><b>Motiu</b>: $motiu.<br><b>Dia</b>: $dia/$mes/$any.<br><b>Horari</b>: de $hora_inici a $hora_final.<br><b>Lloc</b>: $aula.<br><b>Projector</b>: $project.";
		$body.="<p>Motius: $motius.</p>";
		$signature="Centre de C&agrave;lcul de la FNB</font>";
	elseif ($f_tipus_reserva==2):
		$to="<".mysqli_result($query_email,0,"email_fnb").">";
		//$cc="cap.serveis@fnb.upc.edu";
		//$cc1="<".mysqli_result($query_email,0,"email_fnb").">";
		$bcc="centre.calcul@fnb.upc.edu";
		$subject="Descartada la ".html_entity_decode('petici&oacute;dica', ENT_NOQUOTES, 'UTF-8')." dels Espais Docents de \"".html_entity_decode(mysqli_result($query_email,0,"nom_usuari"))."\"";
		//$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Aviat rebreu confirmaci&oacute; de la vostra petici&oacute;.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
		$body="<b>Missatge generat autom&agrave;ticament.</b><br>";
		$body.="S'ha descartat la vostra petici&oacute; de reserva de \"".mysqli_result($query_email,0,"nom_usuari")."\".<br><br><b>Usuari</b>: $qui.<br><b>Activitat/Assignatura</b>: $assig.<br><b>Motiu</b>: $motiu.<br><b>Dia</b>: $dia/$mes/$any.<br><b>Horari</b>: de $hora_inici a $hora_final.<br><b>Lloc</b>: \"$aula\".<br><b>Projector</b>: $project.";
		$body.="<p>Motius: $motius.</p>";
		$signature="Centre de C&agrave;lcul de la FNB</font>";
	elseif ($f_tipus_reserva==6):
		$to="aula.professional@fnb.upc.edu";
		//$cc="cap.serveis@fnb.upc.edu";
		$cc1="<".mysqli_result($query_email,0,"email_fnb").">";
		$bcc="centre.calcul@fnb.upc.edu";
		$subject="Descartada la ".html_entity_decode('petici&oacute;dica', ENT_NOQUOTES, 'UTF-8')." de reserva de la sala de Juntes o Actes de \"".html_entity_decode(mysqli_result($query_email,0,"nom_usuari"))."\"";
		//$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Aviat rebreu confirmaci&oacute; de la vostra petici&oacute;.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
		$body="<b>Missatge generat autom&agrave;ticament.</b><br>";
		$body.="S'ha descartat la vostra petici&oacute; de reserva de \"".mysqli_result($query_email,0,"nom_usuari")."\".<br><br><b>Usuari</b>: $qui.<br><b>Activitat/Assignatura</b>: $assig.<br><b>Motiu</b>: $motiu.<br><b>Dia</b>: $dia/$mes/$any.<br><b>Horari</b>: de $hora_inici a $hora_final.<br><b>Lloc</b>: $aula.<br><b>Projector</b>: $project.";
		$body.="<p>Motius: $motius.</p>";
		$signature="Centre de C&agrave;lcul de la FNB</font>";
	else:
		$to="gestio.academica@fnb.upc.edu";
		//$cc="cap.serveis@fnb.upc.edu";
		$cc1="<".mysqli_result($query_email,0,"email_fnb").">";
		$bcc="centre.calcul@fnb.upc.edu";
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
// Procediment: EnviarCorreuDenegacioReservaPeriodica();
// Operativa  : Enviem un correu de petici� de reserva a la Secretaria de Direcci� i al usuari.
//-----------------------------------------------------------------------------------------------------------
function EnviarCorreuDenegacioReservaPeriodica($data_inici,$data_final,$recurs,$qui,$assig,$motiu,$hora_inici,$hora_final,$aula,$projector,$dia,$f_tipus_reserva,$motius){
	global $tpl, $hora_maxima, $hora_minima, $dbname, $dbuser, $dbpass, $dbserver, $template_dir, $enviar_correu, $url_reserves;
	
	switch($projector){
		case 0: $project = NO;
				break;
		case 1: $project = SI;
		}
	switch($dia){
		case 7: $dia_setmana = DIUMENGE;
				break;
		case 1: $dia_setmana = DILLUNS;
				break;
		case 2: $dia_setmana = DIMARTS;
				break;
		case 3: $dia_setmana = DIMECRES;
				break;
		case 4: $dia_setmana = DIJOUS;
				break;
		case 5: $dia_setmana = DIVENDRES;
				break;
		case 6: $dia_setmana = DISSABTE;
				break;
		}
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
	$consulta_email="SELECT email,nom_usuari,email_fnb
							FROM tbl_usuaris
							WHERE nom_usuari='$qui'";
	
	$query_email=@mysqli_query($conn,$consulta_email);
	//Si cal avisar de les reserves del recurs, enviem email al responsable
	$from="Reserva Sales<".mysqli_result($query_email,0,"email").">";
	$assig = stripslashes($assig);
	$motiu = stripslashes($motiu);
	if ($f_tipus_reserva==3):
		$to="secretaria.direccio@fnb.upc.edu";
		//$cc="cap.serveis@fnb.upc.edu";
		$cc1="<".mysqli_result($query_email,0,"email_fnb").">";
		$bcc="centre.calcul@fnb.upc.edu";
		$subject="Descartada la ".html_entity_decode('petici&oacute;dica', ENT_NOQUOTES, 'UTF-8')." de reserva ".html_entity_decode('peri&ograve;dica', ENT_NOQUOTES, 'UTF-8')." de la sala de Juntes o Actes de \"".html_entity_decode(mysqli_result($query_email,0,"nom_usuari"))."\"";
		$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Aviat rebreu confirmaci&oacute; de la vostra petici&oacute;.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
		$body.="S'ha descartat la petici&oacute; de reserva de \"".mysqli_result($query_email,0,"nom_usuari")."\".<br><br>&nbsp;&nbsp;&nbsp;<b>Usuari</b>: $qui.<br>&nbsp;&nbsp;&nbsp;<b>Activitat/Assignatura</b>: $assig.<br>&nbsp;&nbsp;&nbsp;<b>Motiu</b>: $motiu.<br>&nbsp;&nbsp;&nbsp;<b>Data inici</b>: $data_inici.<br>&nbsp;&nbsp;&nbsp;<b>Data final</b>: $data_inici.<br>&nbsp;&nbsp;&nbsp;<b>Horari</b>: $dia_setmana de $hora_inici a $hora_final.<br>&nbsp;&nbsp;&nbsp;<b>Lloc</b>: $aula.<br>&nbsp;&nbsp;&nbsp;<b>Projector</b>: $project.<p>";
		$body.="<p>Motius: $motius.</p>";
		$signature="Centre de C&agrave;lcul de la FNB</font>";
	elseif ($f_tipus_reserva==2):
		$to="<".mysqli_result($query_email,0,"email_fnb").">";
		//$cc="cap.serveis@fnb.upc.edu";
		$cc1="";
		$bcc="centre.calcul@fnb.upc.edu";
		$subject="Descartada la ".html_entity_decode('petici&oacute;', ENT_NOQUOTES, 'UTF-8')." de reserva ".html_entity_decode('peri&ograve;dica', ENT_NOQUOTES, 'UTF-8')." dels Espais Docents de \"".html_entity_decode(mysqli_result($query_email,0,"nom_usuari"))."\"";
		$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Aviat rebreu confirmaci&oacute; de la vostra petici&oacute;.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
		$body.="S'ha descartat la petici&oacute; de reserva de \"".mysqli_result($query_email,0,"nom_usuari")."\".<br><br>&nbsp;&nbsp;&nbsp;<b>Usuari</b>: $qui.<br>&nbsp;&nbsp;&nbsp;<b>Activitat/Assignatura</b>: $assig.<br>&nbsp;&nbsp;&nbsp;<b>Motiu</b>: $motiu.<br>&nbsp;&nbsp;&nbsp;<b>Data inici</b>: $data_inici.<br>&nbsp;&nbsp;&nbsp;<b>Data final</b>: $data_final.<br>&nbsp;&nbsp;&nbsp;<b>Horari</b>: $dia_setmana de $hora_inici a $hora_final.<br>&nbsp;&nbsp;&nbsp;<b>Lloc</b>: $aula.<br>&nbsp;&nbsp;&nbsp;<b>Projector</b>: $project.<p>";
		$body.="<p>Motius: $motius.</p>";
		$signature="Centre de C&agrave;lcul de la FNB</font>";
	elseif ($f_tipus_reserva==6):
		$to="aula.professional@fnb.upc.edu";
		//$cc="cap.serveis@fnb.upc.edu";
		$cc1="<".mysqli_result($query_email,0,"email_fnb").">";
		$bcc="centre.calcul@fnb.upc.edu";
		$subject="Descartada la ".html_entity_decode('petici&oacute;dica', ENT_NOQUOTES, 'UTF-8')." de reserva ".html_entity_decode('peri&ograve;dica', ENT_NOQUOTES, 'UTF-8')." de la sala de Juntes o Actes de \"".html_entity_decode(mysqli_result($query_email,0,"nom_usuari"))."\"";
		$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Aviat rebreu confirmaci&oacute; de la vostra petici&oacute;.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
		$body.="S'ha descartat la petici&oacute; de reserva de \"".mysqli_result($query_email,0,"nom_usuari")."\".<br><br>&nbsp;&nbsp;&nbsp;<b>Usuari</b>: $qui.<br>&nbsp;&nbsp;&nbsp;<b>Activitat/Assignatura</b>: $assig.<br>&nbsp;&nbsp;&nbsp;<b>Motiu</b>: $motiu.<br>&nbsp;&nbsp;&nbsp;<b>Data inici</b>: $data_inici.<br>&nbsp;&nbsp;&nbsp;<b>Data final</b>: $data_inici.<br>&nbsp;&nbsp;&nbsp;<b>Horari</b>: $dia_setmana de $hora_inici a $hora_final.<br>&nbsp;&nbsp;&nbsp;<b>Lloc</b>: $aula.<br>&nbsp;&nbsp;&nbsp;<b>Projector</b>: $project.<p>";
		$body.="<p>Motius: $motius.</p>";
		$signature="Centre de C&agrave;lcul de la FNB</font>";
	else:
		//$to="gestio.academica@fnb.upc.edu";
		//$cc="cap.serveis@fnb.upc.edu";
		$cc1="<".mysqli_result($query_email,0,"email_fnb").">";
		$bcc="centre.calcul@fnb.upc.edu";
		$subject="Descartada la ".html_entity_decode('petici&oacute;', ENT_NOQUOTES, 'UTF-8')." de reserva ".html_entity_decode('peri&ograve;dica', ENT_NOQUOTES, 'UTF-8')." dels Espais Docents de \"".html_entity_decode(mysqli_result($query_email,0,"nom_usuari"))."\"";
		$body="<font face=\"Trebuchet MS,Verdana,Arial,Helvetica\"><p><table border><tr height='35'><td>&nbsp;&nbsp;&nbsp;&nbsp;Missatge generat autom&agrave;ticament. Aviat rebreu confirmaci&oacute; de la vostra petici&oacute;.&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></p>";
		$body.="S'ha descartat la petici&oacute; de reserva de \"".mysqli_result($query_email,0,"nom_usuari")."\".<br><br>&nbsp;&nbsp;&nbsp;<b>Usuari</b>: $qui.<br>&nbsp;&nbsp;&nbsp;<b>Activitat/Assignatura</b>: $assig.<br>&nbsp;&nbsp;&nbsp;<b>Motiu</b>: $motiu.<br>&nbsp;&nbsp;&nbsp;<b>Data inici</b>: $data_inici.<br>&nbsp;&nbsp;&nbsp;<b>Data final</b>: $data_final.<br>&nbsp;&nbsp;&nbsp;<b>Horari</b>: $dia_setmana de $hora_inici a $hora_final.<br>&nbsp;&nbsp;&nbsp;<b>Lloc</b>: $aula.<br>&nbsp;&nbsp;&nbsp;<b>Projector</b>: $project.<p>";
		$body.="<p>Motius: $motius.</p>";
		$signature="Centre de C&agrave;lcul de la FNB</font>";
	endif;
	EnviarEmail($from, $to, $cc, $cc1, $bcc, $subject, $body, $signature, $err_msg);
	//mail($f_mail,,"L'usuari $qui ha fet una reserva de $f_recurs pel motiu: $motiu","From: reservator@$SERVER_NAME");
}

?>