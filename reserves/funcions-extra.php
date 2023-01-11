<?php

//Calculem el dilluns de la setmana
function Calcula_dilluns_actual(){
$date=getdate();
		$any=$date['year'];
		$mes=$date['mon'];
		$dia_semana=$date['wday'];
		if($dia_semana==1):
			$p_dia=$date['mday'];
		else:
			$p_dia=$date['mday']-$dia_semana+1;
				//Calculem el dilluns del mes anterior
				if($p_dia<=0):
					$u_mes=$mes-1;
					$ultimodia=strftime("%d",mktime(0, 0, 0, "$mes", 0, "$any"));
					$u_p_dia=$ultimodia+$p_dia;
					$p_dia=$u_p_dia;
					$mes=$u_mes;
					else:
					endif;
		endif;
		$u_dia=$date['mday']+7-$dia_semana;
		return compact('p_dia','u_dia','mes','any');
}

//Calculem el dilluns de la setmana
function Calcula_ultim_dia_setmanal($p_dia,$mes,$any){
	$u_dia=$p_dia+6;
	$k_mes=$mes+1;
	$ultimodia=strftime("%d",mktime(0, 0, 0, "$k_mes", 0, "$any"));
		if($u_dia>$ultimodia):
			$uu_dia=$u_dia-$ultimodia;
			$u_dia=$uu_dia;
			$u_mes=$mes+1;
				if($u_mes>12):
					$u_mes=1;
					$u_any=$any+1;
				else:
					$u_any=$any;
				endif;
		else:
		$u_mes=$mes;
		$u_any=$any;
		endif;
				
		return compact('u_dia','u_mes','u_any');
}

// Retorna el nombre de dies que te el mes de l'any indicat
function days_in_month($month, $year) /*{
   return $month == 2 ? $year % 4 ? 28 : 29 : ($month % 7 % 2 ? 31 : 30);
}*/{
 if (((fmod($year,4)==0) and (fmod($year,100)!=0)) or (fmod($year,400)==0)) {
       $dias_febrero = 29;
   } else {
       $dias_febrero = 28;
   }
   switch($month) {
       case 1: return 31; break;
       case 2: return $dias_febrero; break;
       case 3: return 31; break;
       case 4: return 30; break;
       case 5: return 31; break;
       case 6: return 30; break;
       case 7: return 31; break;
       case 8: return 31; break;
       case 9: return 30; break;
       case 10: return 31; break;
       case 11: return 30; break;
       case 12: return 31; break;
   }
} 

// Passa del format (DL:1 .. DG:7) al nom de la setmana
function nomdia($wday)
{ 
   switch ($wday) {
    case 1:
        $dia_setmana='Dilluns';
		break;
    case 2:
        $dia_setmana='Dimarts';
		break;
    case 3:
        $dia_setmana='Dimecres';
		break;
	case 4:
        $dia_setmana='Dijous';
		break;
	case 5:
        $dia_setmana='Divendres';
		break;
	case 6:
        $dia_setmana='Dissabte';
		break;
	case 7:
        $dia_setmana='Diumenge';
		break;
	}
   return $dia_setmana;
} 

// Passa del format (DG:0 .. DS:6) al format  (DL:1 .. DG:7)
function monsun($wday)
{ 
   return ($wday + 6) % 7;
} 

function sunmon($wday) 
{ 
   return ($wday + 1) % 7; 
} 

function dateToArray($date){
	$tok = strtok($date,"/-");
	while ($tok !== FALSE)
	{
		$toks[] = $tok;
		$tok = strtok(" /-\\");
	}
	return $toks;
}

function franges_horaries($hora_inicial,$hora_final,$defecte,$camp){
	$resultat="<SELECT name=\"$camp\">\n";
	$pos = strpos($defecte, ':');
	$min = substr($defecte, -2);
	$defecte = substr($defecte,0,$pos);

	for($i=$hora_inicial;$i<$hora_final;$i++){
	    if(($i==$defecte)&&(($min==00)||($min==30)||($min==15)||($min==45))){
				if($min==0){
					$resultat=sprintf("%s:00<OPTION SELECTED value=\"%s:00\">%s:00</OPTION>",$resultat,$i,$i);
					$resultat=sprintf("%s:15<OPTION value=\"%s:15\">%s:15</OPTION>",$resultat,$i,$i);
					$resultat=sprintf("%s:30<OPTION value=\"%s:30\">%s:30</OPTION>",$resultat,$i,$i);
					$resultat=sprintf("%s:45<OPTION value=\"%s:45\">%s:45</OPTION>",$resultat,$i,$i);
				}
				elseif($min==30){
					$resultat=sprintf("%s:00<OPTION value=\"%s:00\">%s:00</OPTION>",$resultat,$i,$i);
					$resultat=sprintf("%s:15<OPTION value=\"%s:15\">%s:15</OPTION>",$resultat,$i,$i);
					$resultat=sprintf("%s:30<OPTION SELECTED value=\"%s:30\">%s:30</OPTION>",$resultat,$i,$i);
					$resultat=sprintf("%s:45<OPTION value=\"%s:45\">%s:45</OPTION>",$resultat,$i,$i);
				}
				elseif($min==15){
				    $resultat=sprintf("%s:00<OPTION value=\"%s:00\">%s:00</OPTION>",$resultat,$i,$i);
				    $resultat=sprintf("%s:15<OPTION SELECTED value=\"%s:15\">%s:15</OPTION>",$resultat,$i,$i);
				    $resultat=sprintf("%s:30<OPTION value=\"%s:30\">%s:30</OPTION>",$resultat,$i,$i);
				    $resultat=sprintf("%s:45<OPTION value=\"%s:45\">%s:45</OPTION>",$resultat,$i,$i);
				}
				elseif($min==45){
				    $resultat=sprintf("%s:00<OPTION value=\"%s:00\">%s:00</OPTION>",$resultat,$i,$i);
				    $resultat=sprintf("%s:15<OPTION value=\"%s:15\">%s:15</OPTION>",$resultat,$i,$i);
				    $resultat=sprintf("%s:30<OPTION value=\"%s:30\">%s:30</OPTION>",$resultat,$i,$i);
				    $resultat=sprintf("%s:45<OPTION SELECTED value=\"%s:45\">%s:45</OPTION>",$resultat,$i,$i);
				}
			
		}else{
			for($j=1;$j<=4;$j++){
				if($j==1){
					$resultat=sprintf("%s:00<OPTION value=\"%s:00\">%s:00</OPTION>",$resultat,$i,$i);
				}
				elseif($j==2){
				    if($i==22):
				    //Para no representar las 22:15 (hora_maxima + media hora)
				    else:
				        $resultat=sprintf("%s:15<OPTION value=\"%s:15\">%s:15</OPTION>",$resultat,$i,$i);
				    endif;
				}
				elseif($j==3){
				    if($i==22):
				    //Para no representar las 22:30 (hora_maxima + media hora)
				    else:
				        $resultat=sprintf("%s:30<OPTION value=\"%s:30\">%s:30</OPTION>",$resultat,$i,$i);
				    endif;
				}
				else{
					if($i==22):
					//Para no representar las 22:45 (hora_maxima + media hora)
					else:
						$resultat=sprintf("%s:45<OPTION value=\"%s:45\">%s:45</OPTION>",$resultat,$i,$i);
					endif;
				}
				
			}			
		}
	}
	$resultat=sprintf("%s</SELECT>\n",$resultat);
	return $resultat;
}

function franges_horaries_vaixell($hora_inicial,$hora_final,$defecte,$camp){
	$resultat="<SELECT name=\"$camp\">\n";
	
	for($i=$hora_inicial;$i<$hora_final;$i++){
		if($i==$defecte){
			$resultat=sprintf("%s<OPTION SELECTED value=\"%s\">%s:00</OPTION>",$resultat,$i,$i);
		}else{
			$resultat=sprintf("%s<OPTION value=\"%s\">%s:00</OPTION>",$resultat,$i,$i);
		}
	}
	$resultat=sprintf("%s</SELECT>\n",$resultat);
	return $resultat;
}

function mysqli_result_table($result) {
	$resultat="";
   	$resultat='<table><tr>';
   	for($i = 0; $i < mysqli_num_fields($result); $i++) {
		$property = mysqli_fetch_field($result);
       	$resultat=sprintf("%s <td> %s </td>",$resultat,$property->name);
   	}
	$resultat=sprintf("%s </tr>",$resultat);
   	while($row = mysqli_fetch_array($result)) {
       		$resultat=sprintf("%s <tr> ",$resultat);
       		for($i = 0; $i < count($row); $i++) {
           		$resultat=sprintf("%s <td> %s </td>",$resultat,$row[$i]);
	        }
       		$resultat=sprintf("%s </tr>",$resultat);
	
   	}
       	$resultat=sprintf("%s </table>",$resultat);
   	return $resultat;
}

function select_who($dbserver, $dbname, $dbuser, $dbpass, $qui) {
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
	$query_consultaqui = "SELECT * FROM tbl_usuaris WHERE disuser=0 ORDER BY nom_usuari ASC";
	$consultaqui = @mysqli_query($conn,$query_consultaqui) or die(mysqli_error());
	$totalRows_consultaqui = mysqli_num_rows($consultaqui);
	
	$persona="<SELECT name=\"$qui\">\n";
	for($i=0;$i<$totalRows_consultaqui;$i++){
		$consultausuari = mysqli_result($consultaqui, $i, "nom_usuari");
		$consultaid_usuari = mysqli_result($consultaqui, $i, "id_usuari");
		$persona = sprintf("%s<OPTION value=\"%s\">%s</OPTION>",$persona,$consultausuari,$consultausuari);
		$persona_sel = sprintf("%s<OPTION SELECTED value=\"%s\">%s</OPTION>",$persona,$consultausuari,$consultausuari);
	}
	$persona = sprintf("%s</SELECT>\n",$persona);
	return $persona;
}


function select_place2($dbserver, $dbname, $dbuser, $dbpass, $f_edifici, $sitio) {
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
	$query_consultalugar = "SELECT * FROM tbl_aules WHERE id_edifici=$f_edifici ORDER BY aula ASC";
	$consultalugar = @mysqli_query($conn,$query_consultalugar) or die(mysqli_error());
	$row_consultalugar = mysqli_fetch_assoc($consultalugar);
	$totalRows_consultalugar = mysqli_num_rows($consultalugar);
	
	$lugar="<SELECT name=\"$sitio\">\n";
		
	do {	

		$i=$row_consultalugar['id_aula'];
		$consultaaula=$row_consultalugar['aula'];
		$lugar = sprintf("%s<OPTION SELECTED value=\"%s\">%s</OPTION>",$lugar,$i,$consultaaula);
				} while ($row_consultalugar = mysqli_fetch_assoc($consultalugar));
				$rows = mysqli_num_rows($consultalugar);
				if($rows > 0) {
					mysqli_data_seek($consultalugar, 0);
					$row_consultalugar = mysqli_fetch_assoc($consultalugar);
				}
	$lugar = sprintf("%s</SELECT>\n",$lugar);
	return $lugar;
}

function Inserir_id_reserva_major($dbserver, $dbname, $dbuser, $dbpass) {
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
	$query_consultaid = "SELECT id_reserva FROM tbl_reserves ORDER BY 'id_reserva' ASC";
	$consultaid = @mysqli_query($conn,$query_consultaid) or die(mysqli_error());
	$totalRows_consultaid = mysqli_num_rows($consultaid);
	$id_reserva_major = mysqli_result($consultaid,$totalRows_consultaid-1,"id_reserva");
	mysqli_close();
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, 'reserves') or die(E_ERR_SELECT_DB);
	$query_consultaid = "SELECT * FROM tbl_reserves ORDER BY 'id_reserva' ASC";
	$consultaid = @mysqli_query($conn,$query_consultaid) or die(mysqli_error());
	$totalRows_consultaid = mysqli_num_rows($consultaid);
	$id_reserva_major_reserves = mysqli_result($consultaid,$totalRows_consultaid-1,"id_reserva");
	$consulta_insert="UPDATE tbl_reserves SET id_usuari='$id_reserva_major' WHERE id_reserva='$id_reserva_major_reserves'";
	$query_insert=@mysqli_query($conn,$consulta_insert);
	mysqli_close();
}

function getTimeForMysqlDateField($hora) {
          if ($hora < 10):
           $hora_mysql = "0".$hora.":00:00";
		  else:
		   $hora_mysql = $hora.":00:00";
		  endif;
   
   return $hora_mysql;
}

//Calculem el nombre de dies que fem la reserva
function Dies_Reserva($data_inici,$data_final) {
	$a_date=dateToArray($data_inici);
    $mes0=$a_date[1];
    $dia0=$a_date[0];
    $any0=$a_date[2];
	$a_date=dateToArray($data_final);
    $mes1=$a_date[1];
    $dia1=$a_date[0];
    $any1=$a_date[2];
	//calculo timestamp de las dos fechas 
	$timestamp0 = mktime(0,0,0,$mes0,$dia0,$any0); 
	$timestamp1 = mktime(0,0,0,$mes1,$dia1,$any1); 
	//resto a una fecha la otra 
	$segons_diferencia = $timestamp1 - $timestamp0; 
	//echo $segundos_diferencia; 

	//convierto segundos en d�as 
	$dies_diferencia = $segons_diferencia / (60 * 60 * 24);
	
	return $dies_diferencia;
}

function CalculemSiEsFesta($dbserver, $dbname, $dbuser, $dbpass, $dia, $mes, $any) {
	$festa=0;
	if($dia<10):
		$dia="0$dia";
	endif;
	if($mes<10):
		$mes="0$mes";
	endif;
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
	$data="$any-$mes-$dia";
	$query_consultaqui = "SELECT * FROM tbl_festes WHERE data=\"$data\"";
	$consultaqui = @mysqli_query($conn,$query_consultaqui) or die(mysqli_error());
	$totalRows_consultaqui = mysqli_num_rows($consultaqui);
	//date("N",strtotime($any."-".$mes."-".$dia);exit;
	if(date("N",strtotime($any."/".$mes."/".$dia))=='7') $festa = 7;
	if(date("N",strtotime($any."/".$mes."/".$dia))=='6') $festa = 8;
	if($totalRows_consultaqui>0):
		$festa=mysqli_result($consultaqui, 0, "festiu");
	endif;
	
	return $festa;
}
function CalculemSiEsNoLectiu($dbserver, $dbname, $dbuser, $dbpass, $dia, $mes, $any) {
	$lectiu=1;
	if($dia<10):
		$dia="0$dia";
	endif;
	if($mes<10):
		$mes="0$mes";
	endif;
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
	$data="$any-$mes-$dia";
	$query_consultaqui = "SELECT * FROM tbl_festes WHERE data=\"$data\" ";
	$consultaqui = @mysqli_query($conn,$query_consultaqui) or die(mysqli_error());
	$totalRows_consultaqui = mysqli_num_rows($consultaqui);
	if(date("N",strtotime($any."/".$mes."/".$dia))=='7') $festa = 7;
	if(date("N",strtotime($any."/".$mes."/".$dia))=='6') $festa = 8;
	if($totalRows_consultaqui>0):
		$lectiu=mysqli_result($consultaqui, 0, "festiu");
	endif;
	return $lectiu;
}

function ComentariFestius($dbserver, $dbname, $dbuser, $dbpass, $dia, $mes, $any) {
	if($dia<10):
		$dia="0$dia";
	endif;
	if($mes<10):
		$mes="0$mes";
	endif;
	$conn = @mysqli_connect($dbserver, $dbuser, $dbpass) or die(E_ERR_BD_CONNECT);
	@mysqli_select_db($conn, $dbname) or die(E_ERR_SELECT_DB);
	$data="$any-$mes-$dia";
	$query_consultaqui = "SELECT * FROM tbl_festes WHERE data=\"$data\" ";
	$consultaqui = @mysqli_query($conn,$query_consultaqui) or die(mysqli_error());
	$totalRows_consultaqui = mysqli_num_rows($consultaqui);
	if($totalRows_consultaqui>0):
		$comentari=mysqli_result($consultaqui, 0, "comentari");
	endif;
	return $comentari;
}

function PrimerDiaEnPeriode($data_inici,$data_final,$num_dia) {
	$a_date=dateToArray($data_inici);
    $mes0=$a_date[1];
    $dia0=$a_date[2];
    $any0=$a_date[0];
	$a_date=dateToArray($data_final);
    $mes1=$a_date[1];
    $dia1=$a_date[2];
    $any1=$a_date[0];
	//calculo timestamp de las dos fechas
	$hihadies=0;
	$timestamp = mktime(0,0,0,$mes0,$dia0,$any0);
	$timestamp0 = mktime(0,0,0,$mes0,$dia0,$any0); 
	$timestamp1 = mktime(0,0,0,$mes1,$dia1,$any1); 
	//resto a una fecha la otra 
	while($timestamp<=$timestamp1): 
	//echo $segundos_diferencia; 
		$dia_setmana = date("N", $timestamp);
		if($num_dia==$dia_setmana):
			$primer_dia_en_periode=$timestamp;
			return date("Y-m-d",$primer_dia_en_periode);
			break;
		endif;
		$timestamp = $timestamp + (60 * 60 * 24);
	endwhile;
}

function HiHaDiaEnPeriode($data_inici,$data_final,$num_dia) {
	$a_date=dateToArray($data_inici);
    $mes0=$a_date[1];
    $dia0=$a_date[2];
    $any0=$a_date[0];
	$a_date=dateToArray($data_final);
    $mes1=$a_date[1];
    $dia1=$a_date[2];
    $any1=$a_date[0];
	//calculo timestamp de las dos fechas
	$hihadies=0;
	$timestamp = mktime(0,0,0,$mes0,$dia0,$any0);
	$timestamp0 = mktime(0,0,0,$mes0,$dia0,$any0); 
	$timestamp1 = mktime(0,0,0,$mes1,$dia1,$any1); 
	//resto a una fecha la otra 
	while($timestamp<=$timestamp1): 
	//echo $segundos_diferencia; 
		$dia_setmana = date("N", $timestamp);
		if($num_dia==$dia_setmana):
			$hihadies=1;
		endif;
		$timestamp = $timestamp + (60 * 60 * 24);
	endwhile;
	return $hihadies;
}

function Calcul_x_hores_laborales($hora_inici,$min_inici,$dia,$mes,$any){
	global $tpl, $hora_maxima, $hora_minima, $hores_antelacio_x_eliminar_reserva, $dbname, $dbuser, $dbpass, $dbserver, $template_dir;

	$afegim_hores = 0;
	$data_aux = time();
	$dia_setmana = date("N",$data_aux_avui);
	$festa_hoy = CalculemSiEsFesta($dbserver, $dbname, $dbuser, $dbpass, date("d",$data_aux), date("m",$data_aux), date("Y",$data_aux));
	$data_final = mktime($hora_inici,$min_inici,0,$mes,$dia,$any);
	$dia_aux_ini = mktime(0,0,0,date("m",$data_aux),date("d",$data_aux),date("Y",$data_aux));
	$dia_aux_fin = mktime(0,0,0,$mes,$dia,$any);
	//Revisem el dia que fem la petici�
	if(!$festa_hoy && $dia_setmana<6 && $data_aux==time() && $dia_aux_ini!=$dia_aux_fin) $afegim_hores = mktime(24,0,0,date("m",$data_aux),date("d",$data_aux),date("Y",$data_aux)) - time();
	if(!$festa_hoy && $dia_setmana<6 && $data_aux==time() && $dia_aux_ini==$dia_aux_fin) $afegim_hores = mktime($hora_inici,$min_inici,0,date("m",$data_aux),date("d",$data_aux),date("Y",$data_aux)) - time();
	$data_aux = $data_aux + 24*60*60;
	$dia_aux_ini = mktime(0,0,0,date("m",$data_aux),date("d",$data_aux),date("Y",$data_aux));
	$dia_setmana = date("N",$data_aux);
	$festa_hoy = CalculemSiEsFesta($dbserver, $dbname, $dbuser, $dbpass, date("d",$data_aux), date("m",$data_aux), date("Y",$data_aux));
	//Revisem els dies intermitjos
	while($dia_aux_ini < $dia_aux_fin) {
		if(!$festa_hoy && $dia_setmana<6) $afegim_hores = $afegim_hores + 24*60*60;
		$data_aux = $data_aux + 24*60*60;
		$dia_setmana = date("N",$data_aux);
		$festa_hoy = CalculemSiEsFesta($dbserver, $dbname, $dbuser, $dbpass, date("d",$data_aux), date("m",$data_aux), date("Y",$data_aux));
		$dia_aux_ini = mktime(0,0,0,date("m",$data_aux),date("d",$data_aux),date("Y",$data_aux));
	}
	//Revisem l'ultim dia que es el dia que volem eliminar o on comen�a el periode que volem esborrar
	if(!$festa_hoy && $dia_setmana<6 && $dia_aux_ini==$dia_aux_fin) $afegim_hores = $afegim_hores + mktime($hora_inici,$min_inici,0,date("m",$data_aux),date("d",$data_aux),date("Y",$data_aux)) - $dia_aux_ini;

	return $afegim_hores;
}

function mysqli_result($res, $row, $field) {
    $res->data_seek($row);
    $datarow = $res->fetch_array();
    return $datarow[$field];
}  
?>
