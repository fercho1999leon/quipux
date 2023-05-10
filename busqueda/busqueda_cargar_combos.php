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
if (isset ($replicacion) && $replicacion && $config_db_replica_busqueda_cargar_combo_usuario!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_busqueda_cargar_combo_usuario);

if ($_POST["tipo_combo"]=="inst" && $_SESSION["perm_buscar_doc_adscritas"]!=1) die("");

$txt_inst_codi = ($_SESSION["perm_buscar_doc_adscritas"]==1) ? 0+limpiar_numero($_POST["txt_inst_codi"]) : $_SESSION["inst_codi"];
$txt_depe_codi = 0+limpiar_numero($_POST["txt_depe_codi"]);
$txt_usua_codi = 0+limpiar_numero($_POST["txt_usua_codi"]);
$tipo_combo = limpiar_sql($_POST["tipo_combo"]);
$default = "";

switch ($tipo_combo) {
    case "inst":
        $sql = "select inst_nombre, inst_codi from institucion where inst_estado=1 order by 1 asc";
        break;
    case "depe":
        $sql = "select depe_nomb, depe_codi from dependencia where depe_estado=1 and inst_codi=$txt_inst_codi order by 1 asc";
        $default = "0:&lt;&lt Todas las &Aacute;reas &gt;&gt;";
        break;
    case "usua":
        $where =  ($txt_depe_codi!=0) ? $where = " and depe_codi=$txt_depe_codi" : "";
 
        $sql = "select usua_apellido || ' ' || usua_nomb
                || case when usua_esta = 0 then ' (Inactivo)' else '' end
                || case when usua_codi
                 in (select usua_subrogado from usuarios_subrogacion
                 where usua_visible=1) = true then ' (Subrogado)' else '' end
                 || case when usua_codi
                 in (select usua_subrogante from usuarios_subrogacion
                 where usua_visible=1) = true then ' (Subrogante)' else '' end
                as nombre,
                usua_codi from usuario where inst_codi=$txt_inst_codi and usua_codi>0 $where order by 1 asc";
        $default = "0:&lt;&lt; Todos los usuarios &gt;&gt;";
        break;
    default:
        die ("No se puede cargar el combo.");
        break;
}

$rs = $db->conn->query($sql);
if($rs && !$rs->EOF)
    print $rs->GetMenu2("slc_$tipo_combo"."_combo", ${"txt_$tipo_combo"."_codi"}, $default, false,"","class='select' id='slc_$tipo_combo"."_combo' onChange='fjs_buscar_cargar_combo(\"$tipo_combo\", this);'");
?>
