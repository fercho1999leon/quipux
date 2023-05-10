<?php
/**  Programa para el manejo de gestion documental, oficios, memorandos, circulares, acuerdos
*    Desarrollado y en otros Modificado por la SubSecretaría de Informática del Ecuador
*    Quipux    www.gestiondocumental.gov.ec
*------------------------------------------------------------------------------
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU Affero General Public License as
*    published by the Free Software Foundation, either version 3 of the
*    License, or (at your option) any later version.
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU Affero General Public License for more details.
*
*    You should have received a copy of the GNU Affero General Public License
*    along with this program.  If not, see http://www.gnu.org/licenses.
*------------------------------------------------------------------------------
**/
/*****************************************************************************************
**											**
*****************************************************************************************/
$ruta_raiz = ".";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
require_once("$ruta_raiz/obtenerdatos.php");  //formar la observacion de edicion
include_once "$ruta_raiz/funciones_interfaz.php";

//
//session_start();
//if ($_SESSION["usua_admin_sistema"] != 1) {
//    echo html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
//    die("");
//}
$usua_subrogante = $_SESSION['usua_codi'];
include_once "$ruta_raiz/rec_session.php";
$sqlSubre = "select * from usuarios_subrogacion where usua_subrogante = $usua_subrogante and usua_visible=1";
$rsSubre = $db->conn->query($sqlSubre);
$usua_visible = $rsSubre->fields['USUA_VISIBLE'];
$usua_fechaini = substr($rsSubre->fields['USUA_FECHA_INICIO'],0,10);//dias
$usua_fechafin = substr($rsSubre->fields['USUA_FECHA_FIN'],0,10);//dias
$usua_horaini = substr(date("H:i:s"),0,2);
$usua_horafin = substr($rsSubre->fields['USUA_FECHA_FIN'],11,2);//dias
if ($usua_visible == 1){
    $dias = CalculaEntreFechas($usua_fechaini,$usua_fechafin);
    $hora = $usua_horafin-$usua_horaini;
    if ($dias<=3){
        $mensajesubro="<br>Estimado Usuario, la subrogación se terminará en $dias día(s)";
        if ($dias==0){
          $mensajesubro="Estimado Usuario, la subrogación se terminará en $hora hora(s)";
        }
    }
    else
        $mensajesubro="";
}
else
    $mensajesubro="";
