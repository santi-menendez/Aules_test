<?
	// Incloem els fitxers auxiliars necessaris
	require "config_site.php";
	require "funcions.php";
	require "locales.php";
	require "forms.php";

	// Si no s'ha fet abans, hem de demanar la validacio de l'usuari
	session_name("$usr_session_name");
	session_start();
	if (!isset($_SESSION['uid'])) require ("validacio.php");
	
	$lang="";
	// Llegim tots els parametres que arriben per la linia de comandes (els pasem a min?scules)
	while (list($var,$value) = each($HTTP_GET_VARS)):
		$$var=strtolower($value);
	endwhile;

	// Si no s'indica cap idioma, posem l'idioma per defecte indicat a "config_site.php"
	if ($lang==""):
		$lang=$default_lang;
	endif;

	// Si no s'indica cap clau mostrarem la pagina inicial (home)
	if ($key==""):
		$key="home";
	endif;

	//------------------------------------------------------------------------------------
	// Iniciem la gestio amb les plantilles
	//------------------------------------------------------------------------------------
	// Incloem el fitxer de la classe
	include("class.FastTemplate.php3");

	// Instanciem un nou objecte
	$tpl = new FastTemplate($path_to_template);

	// Assignem noms als fitxers de plantilla
	$tpl->define(array(
//		"main"       => "mainframe.tpl",
		"main"       => "page.tpl",
		"menu"       => "menu.tpl",
		"items_menu" => "items_menu.tpl"
	));

	// Assignem valors de les variables interiors de les plantilles
	$tpl->assign(array(
		"STYLE"           => "./style.css",
		"MENU_SCRIPTS"    => "<script language=\"JavaScript\" src=\"./menu.js\" type=\"text/javascript\"></script>",
		"SCRIPTS"         => "<script language=\"JavaScript\" src=\"./scripts.js\" type=\"text/javascript\"></script>",
		"BGCOLOR_HEADER"  => $BGCOLOR_HEADER,
		"BGCOLOR_MENU"    => $BGCOLOR_MENU,
		"BGCOLOR_INFO"    => $BGCOLOR_INFO,
		"BGCOLOR_FOOTER"  => $BGCOLOR_FOOTER,
		"PAGE_TITLE"      => _PAGE_TITLE,
		"CHARSET"         => _CHARSET,
//		"DATA"            => WriteDataLongFormat("",$lang, TRUE),
		"DATA"            => getdate(),
		"LOGO"            => "<img src=\"$url_logo\" height=\"$height_logo\" width=\"$width_logo\">",
		"PRINTER"         => "<a href=\"javascript:print()\"><img border=\"0\" src=\"./pics/icons/print.gif\" align=\"middle\"></a>",
		"USERNAME"        => $_SESSION['nom_usuari']." (".$_SESSION['perfil'].")"
	));

	// Pintem el menu principal
//	PintarMenuPrincipal($status);

	//------------------------------------------------------------------------------------
	// Accedim a la Base de Dades per tal de llegir la informacio requerida
	//------------------------------------------------------------------------------------
	// Consultem les opcions del menu que siguin visibles i siguin de l'idioma seleccionat
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
	$query="SELECT * FROM tbl_menu WHERE CONTENTS_KEY='$key'";
	$result=@mysqli_query($conn,$query);
	$nRows=mysqli_num_rows($result);

	if ($nRows == 1):
		// Si la consulta genera una unica sortida
		$topic=mysqli_result($result, 0,"MENU_LITERAL");
		$contents=mysqli_result($result, 0,"CONTENTS");
		$act_date=mysqli_result($result, 0,"UPDATED");
	else:
		// Si no genera sortida (o mes d'una) donem missatge d'error
		$query="SELECT * FROM tbl_menu WHERE CONTENTS_KEY='404_not_found'";
		$result=@mysqli_query($conn,$query);
		$topic=mysqli_result($result, 0,"MENU_LITERAL");
		$contents=mysqli_result($result, 0,"CONTENTS");
		$act_date=mysqli_result($result, 0,"UPDATED");
	endif;

	// Assignem valors a les variables dins de les plantilles
	$tpl->assign(array(
		"PAGE_INFO" => $contents,
//		"FOOTER"    => "<font class=\"textSignature\">&nbsp;"._CONTACT_WEBADMIN."<br>&nbsp;"._LAST_UPDATE.WriteDataLongFormat($act_date, $lang, FALSE)."</font>"
		"FOOTER"    => "<font class=\"textSignature\">&nbsp;"._CONTACT_WEBADMIN."<br>&nbsp;"._LAST_UPDATE.getdate()."</font>"
	));

	// Fem la substitucio (parse)
	$tpl->parse(MENU, "menu");
	$tpl->parse(RESULTAT, "main");

	// Imprimim el resultat dels handlers obtinguts
//	$tpl->FastPrint(RESULTAT);
// Si no es membre del CCESAII, no ha de poder accedir a la pagina...
	Header("Location: ./");
?>