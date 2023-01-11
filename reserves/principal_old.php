<?php
include "./locales.php";
include "./class.FastTemplate.php3";
include "./funcions.php";
include "./funcions-extra.php";
include "./config.php";
include "./reserves.php";
include "./forms.php";


// Si no s'ha fet abans, hem de demanar la validacio de l'usuari
session_name($usr_session_name);
session_start();
if (!isset($_SESSION['uid'])):
	$_SESSION['url_demanada']=$_SERVER['REQUEST_URI'];
	Header("Location: ./index.php");
	die();
endif;

//// Si no es membre del CCESAII, no ha de poder accedir a la pagina...
//if ($_SESSION['perfil']!='CCESAII') :
//	Header("Location: ../intrafnb.php");
//	die();
//endif;

//print "Nom: ".$_SESSION['nom_usuari']."<br>";
//print "Email: ".$_SESSION['email']."<br>";
//print "Departament: ".$_SESSION['departament']."<br>";
//print "Manager: ".$_SESSION['pac_pas']."<br>";
//print "UID: ".$_SESSION['uid']."<br>";
//print "Perfil: ".$_SESSION['perfil']."<br>";


// Capturem totes les variables que arriben per la linia de comandes
while (list($var, $value)=each($_GET)):
	$$var=$value;
	//echo "$var -> $value<br>";
endwhile;

$tpl = new FastTemplate(".");


//if (!isset($_SESSION['r_http_refer'])){
	//if($acces_intranet!=999){
	//	ERR_ACCES_DENEGAT;
	//	exit();
	//}else{
//		$_SESSION['r_http_refer']=1;
	//}
//}

//if ($log==1):
//	//Algu ha provat d'entrar, es comproven les dades enviades
//	$res_login=accept_login($login,$password);
//	if ($res_login==1) $op=0;
//elseif ($log==2) logout();
//elseif ($log==3)
//	if(0!=accept_chg_password($password,$password_again)):
//		$op=1;
//		$res_login=2;
//		$tpl->assign(E_MSG_ANONYMOUS, E_ERR_PASSWORD_NOT_SAME);
//	endif;
//endif;

/*if(isset($_SESSION['r_username'])){
	$tpl->assign(LOGIN,"<A href=\"principal.php?log=2&op=0\">logout</a>");
}else{
	$tpl->assign(LOGIN,"");
}*/

// Pintem les opcions del menu
PintarMenu();

	switch($op){
		case 11: // Per a un recurs determinat, mostra el llistat de reserves per a un dia concret
					PintarReservesDia($dia,$mes,$any,$recurs);
					break;
		case 12:	//
					oneClassResources($dia,$mes,$any,$tipus);
					break;
		case 50:	// Obre una finestra per a realitzar una reserva puntual
					FerReservaPuntual($dia,$mes,$any,$recurs,$hora_inici);
					break;
		case 51:	// Li arriben les dades d'una reserva i ha de validar-la
					ValidarReserva($dia,$mes,$any,$recurs,$qui,$motiu,$hora_inici,$hora_final,0);
					break;
		case 55:	// Permet crear reserves periodiques
					FerReservaPeriodica($dia,$mes,$any,$recurs,$hora_inici);
					break;
		case 56:	// Reserves periodiques
					if(!$_1 && !$_2 && !$_3 && !$_4 && !$_5 && !$_6 && !$_7):
						//no han marcat cap dia
						error_periodic_reservation();
					else:
						$resultat=0;
						$i=0;
						if(($_1)&&($resultat==0)): // Periodica en dilluns
							$resultat=$resultat + CheckPeriodicReservation($_GET['data_inici'],$data_final,1,$recurs,$qui,$motiu,$hora_inici,$hora_final,0);
							$t[$i]=1;
							$i++;
						endif;
						if(($_2)&&($resultat==0)): // Periodica en dimarts
							$resultat=$resultat + CheckPeriodicReservation($data_inici,$data_final,2,$recurs,$qui,$motiu,$hora_inici,$hora_final,0);
							$t[$i]=2;
							$i++;
						endif;
						if(($_3)&&($resultat==0)): // Periodica en dimecres
							$resultat=$resultat + CheckPeriodicReservation($_GET['data_inici'],$_GET['data_final'],3,$recurs,$_GET['qui'],$motiu,$hora_inici,$hora_final,0);
							$t[$i]=3;
							$i++;
						endif;
						if(($_4)&&($resultat==0)): // Periodica en dijous
							$resultat=$resultat + CheckPeriodicReservation($data_inici,$data_final,4,$recurs,$qui,$motiu,$hora_inici,$hora_final,0);
							$t[$i]=4;
							$i++;
						endif;
						if(($_5)&&($resultat==0)): // Periodica en divendres
							$resultat=$resultat + CheckPeriodicReservation($data_inici,$data_final,5,$recurs,$qui,$motiu,$hora_inici,$hora_final,0);
							$t[$i]=5;
							$i++;
						endif;
						if(($_6)&&($resultat==0)): // Periodica en dissabte
							$resultat=$resultat + CheckPeriodicReservation($data_inici,$data_final,6,$recurs,$qui,$motiu,$hora_inici,$hora_final,0);
							$t[$i]=6;
							$i++;
						endif;
						if(($_7)&&($resultat==0)): // Periodica en diumenge
							$resultat=$resultat + CheckPeriodicReservation($data_inici,$data_final,7,$recurs,$qui,$motiu,$hora_inici,$hora_final,0);
							$t[$i]=7;
							$i++;
						endif;

						if($resultat==0):
							// Si no hi ha hagut cap error en les comprovacions, es pot acceptar la reserva periodica
							$i--;
							while($i>=0):
								accept_periodic_reservation($data_inici,$data_final,$t[$i],$recurs,$qui,$motiu,$hora_inici,$hora_final,0);
								$i--;
							endwhile;
						else:
							//no es poden realitzar
							accept_periodic_reservation($data_inici,$data_final,$t[$i-1],$recurs,$qui,$motiu,$hora_inici,$hora_final,0);
						endif;
					endif;
					//accept_periodic_reservation($data_inici,$data_final,$dia,$recurs,$qui,$motiu,$hora_inici,$hora_final,0);
					break;

		case 30: // Per al recurs seleccionat, pinta el calendari del mes i any indicat
					PintarMes($mes, $any, $recurs);
					break;
		case 35: // Per al recurs seleccionat, pinta la setmana indicada
					PintarSetmana($p_dia, $u_dia, $mes, $any, $recurs);
					break;
		case 73: // Abans de procedir a eliminar una reserva, demanem la confirmacio
					ConfirmarEliminacioReserva($id_reserva, $recurs, $dia, $mes, $any);
					break;
		case 74: // S'ha confirmat que es vol esborrar la reserva i es procedeix
					EliminarReserva($id_reserva, $recurs);
					break;
		case 80: // Mostra la informaci? de la reserva d'un recurs
					MostrarInfoReserva($id_reserva);
					break;
		case 100:// Sortim del programa
					Logout();
					break;
		case 1:  // Afegir nous recursos al sistema
					MantenimentRecursos();
					break;
		case 2:	// Afegir Responsables de recursos
					MantenimentResponsables();
					break;
		case 3:	// Ens arriben els parametres $nom_usuari i $perfil. Ara donem d'alta l'usuari
					AltaResponsable($nom_usuari, $perfil);
					break;
		case 5:  // Demanar la creacio d'un nou recurs
					DemanarNouRecurs();
					break;
		case 6:  // Envia la peticio de creacio del recurs
					send_resource($nom_recurs, $descripcio, $responsable);
					break;
		case 7:	// Mostra en una finestra les variables "oficials" registrades de la sessio
					PintarVariablesRegistrades();
					break;
		case 40: // Llistem tots els recursos del sistema (agrupats per edifici)
					PintaLlistaRecursos();
					break;
		case 45: // Llistem tots els recursos d'un edifici determiniat
					PintaLlistaRecursosEdifici($edifici);
					break;
		default: // Llistem tots els edificis que tenen recursos
					PintaEdificisAmbRecursos();
					break;
	}

	Printpage("Reservator v.1.1", $res_login);
	/*}else{
		printf("S'esta fent un mal us dels parametres");
	}*/
?>