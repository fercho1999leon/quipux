<?php
/**  Programa para el manejo de gestion documental, oficios, memorandus, circulares, acuerdos
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

session_start();
$ruta_raiz = "..";
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/obtenerdatos.php";
if (isset ($replicacion) && $replicacion && $config_db_replica_busqueda_cargar_combo_usuario!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_busqueda_cargar_combo_usuario);

$txt_depe_codi = limpiar_numero($_POST["txt_depe_codi"]);
$txt_usua_codi = limpiar_numero($_POST["txt_usua_codi"]);
//$depe_codi_admin = obtenerAreasAdmin($_SESSION["usua_codi"],$_SESSION["inst_codi"],$_SESSION["usua_admin_sistema"],$db);

$where = "depe_codi=$txt_depe_codi";
if ($txt_depe_codi==0) {
//    if((0+$depe_codi_admin)!=0)
//        $where = "depe_codi in (".$depe_codi_admin.")";
//    else
        $where = "inst_codi=".$_SESSION["inst_codi"];
}

//$sql = "select usua_apellido || ' ' || usua_nomb as nombre, usua_codi from datos_usuarios where $where and usua_codi>0 order by 1 asc";
//echo $sql."<br>";
$sql = "select usua_apellido || ' ' || usua_nomb 
|| case when usua_esta = 0 then ' (Inactivo)' else '' end 
|| case when usua_codi
 in (select usua_subrogado from usuarios_subrogacion 
 where usua_visible=1) = true then ' (Subrogado)' else '' end 
 || case when usua_codi
 in (select usua_subrogante from usuarios_subrogacion 
 where usua_visible=1) = true then ' (Subrogante)' else '' end
as nombre,
usua_codi from usuario where $where and usua_codi>0 order by 1 asc";
//echo $sql;
$rs = $db->conn->Execute($sql);
if ($rs)
    echo $rs->GetMenu2("txt_usua_codi", $txt_usua_codi, "0:&lt;&lt; Todos los usuarios &gt;&gt;", false,""," id='txt_usua_codi' class='select'" );
?>
