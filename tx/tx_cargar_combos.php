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
    include_once "$ruta_raiz/obtenerdatos.php";
    include_once "$ruta_raiz/rec_session.php";
    if (isset ($replicacion) && $replicacion && $config_db_replica_tx_cargar_combos!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_tx_cargar_combos);
    $titulo = "";
    $menu = "";

    switch ($_POST["txt_tipo_combo"]) {
        case "area":
            $titulo = "&Aacute;rea:";
            $sql = "select distinct depe_nomb, depe_codi from dependencia where depe_estado=1 and inst_codi=".$_SESSION["inst_codi"]." order by 1";

            if ($_POST["codTx"] == 9) {  //Buscamos las áreas con restriccion del organico funcional
                if ($_SESSION["perm_saltar_organico_funcional"]==1)
                    $where_area = "inst_codi=".$_SESSION["inst_codi"];
                elseif($_SESSION["cargo_tipo"]!=1 && $_SESSION["usua_publico"] !=1)
                    $where_area = "depe_codi=".$_SESSION["depe_codi"];
                else {
                    // Obtenermos el área padre del área actual
                    $sql = "select distinct coalesce(depe_codi_padre, depe_codi) as depe_codi from dependencia where depe_estado=1 and depe_codi=".$_SESSION["depe_codi"];
                    $rs = $db->conn->Execute($sql);
                    $where_area = $rs->fields["DEPE_CODI"];
                    if ($where_area != $_SESSION["depe_codi"]) {
                        $where_area .= "," . $_SESSION["depe_codi"];
                    }
//                    if ($_SESSION["perm_saltar_organico_funcional"]==1) {
//                        // Si el usuario tiene permisos para saltar el organico funcional, muestra un nivel mas.
//                        $sql = "select depe_codi from dependencia where depe_codi_padre=".$_SESSION["depe_codi"];
//
//                        $rs = $db->conn->Execute($sql);
//                        while(!$rs->EOF) {
//                            $where_area .= "," . $rs->fields['DEPE_CODI'];
//                            $rs->MoveNext();
//                        }
//                    }
                    $where_area = "coalesce(depe_codi_padre, depe_codi) in ($where_area) or depe_codi in ($where_area)";
                }
                $sql = "select distinct depe_nomb, depe_codi from dependencia where depe_estado=1 and ($where_area) order by 1";
            }elseif($_POST['codTx']==30){
                if ($_POST['codTx'] == 30) {  //Buscamos las áreas que se desplegarán en los combos de nueva tarea
                if($_SESSION["cargo_tipo"]!=1 && $_SESSION["usua_publico"] !=1){
                     if ($_SESSION["perm_saltar_organico_funcional"]==1)
                            $where_area = " depe_codi in (".$_SESSION["depe_codi"].",".substr(areasHijasNivelDependencia($db, $_SESSION["depe_codi"]),1).")";
                         else
                            $where_area = "depe_codi=".$_SESSION["depe_codi"];
                }                    
                else {
                    // Obtenermos el área padre del área actual
                    $sql = "select coalesce(depe_codi_padre, depe_codi) as depe_codi from dependencia where depe_codi=".$_SESSION["depe_codi"];
                    $rs = $db->conn->Execute($sql);
                    $where_area = $rs->fields["DEPE_CODI"];
                    $where_hijas="";
                    if ($where_area != $_SESSION["depe_codi"]) {
                        if ($_SESSION["perm_saltar_organico_funcional"]==1)
                        $where_hijas = areasHijasNivelDependencia($db, $_SESSION["depe_codi"]);
                        $where_area .= "," . $_SESSION["depe_codi"].$where_hijas;
                    }
                    if ($_SESSION["perm_saltar_organico_funcional"]==1) {
                        // Si el usuario tiene permisos para saltar el organico funcional, muestra un nivel mas.
                        $sql = "select depe_codi from dependencia where depe_codi_padre=".$_SESSION["depe_codi"];

                        $rs = $db->conn->Execute($sql);
                        while(!$rs->EOF) {
                            $where_area .= "," . $rs->fields['DEPE_CODI'];
                            $rs->MoveNext();
                        }
                    }
                    $where_area = "coalesce(depe_codi_padre, depe_codi) in ($where_area) or depe_codi in ($where_area)";
                }
                $sql = "select distinct depe_nomb, depe_codi from dependencia where depe_estado=1 and ($where_area) order by 1";
                //echo $sql;
                $rs = $db->query($sql);
                 
                }
            }
           
            $rs = $db->query($sql);
            if ($_POST["codTx"] == 8) {  //Mostramos los combos para accion informados (combo múltiple)
                $menu = $rs_usr->GetMenu2("txt_tx_depe_codi[]", 0, false, true, 8," id='txt_tx_depe_codi[]' class='select' onChange='tx_cambiar_combo_usuarios()'" );
            } else {
                $menu = $rs->GetMenu2("txt_tx_depe_codi", $_SESSION["depe_codi"], "", false,""," id='txt_tx_depe_codi' class='select' onChange='tx_mostrar_objetos_transaccion (\"combo_usuarios\")'" );
            }
            break;


        case "usuarios":
            $titulo = "Usuario:";
            $where = "";
            if (!isset ($_POST["area"])) $_POST["area"] = $_SESSION["depe_codi"];
            $area = 0 + $_POST["area"];
            if (trim($area,",0123456789 ")!="") $area = 0 + $area;

            if (($_POST["codTx"]==9 or $_POST["codTx"]==30)  and $_SESSION["depe_codi"] != $area)
                $where = " and (cargo_tipo=1 or usua_codi in (select usua_codi from permiso_usuario where id_permiso=29)) ";

            $sql = "select (usua_apellido || ' ' || usua_nomb)
                        || ' ' || case when usua_codi in (select usua_subrogado from usuarios_subrogacion where usua_visible=1) = true then '(Subrogado)' else '' end
                        || ' ' || case when usua_codi in (select usua_subrogante from usuarios_subrogacion where usua_visible=1) = true then '(Subrogante)' else '' end as usua_nombre
                        , usua_codi
                    from usuarios
                    where usua_codi>0 and usua_esta=1 and visible_sub=1 and usua_login not like 'UADM%'
                        and depe_codi in ($area) $where
                    order by 1";
            $rs = $db->conn->Execute($sql);

            if ($_POST["codTx"]==8  ){
                $menu  = $rs->GetMenu2("txt_tx_usua_codi[]", 0, false, true, 8," id='txt_tx_usua_codi' class='select'" );
            } else {
                $menu  = $rs->GetMenu2("txt_tx_usua_codi", 0, "0:&lt;&lt; Seleccione Usuario &gt;&gt;", false,""," id='txt_tx_usua_codi' class='select'" );
            }
            break;

        case "listas":
            break;

        case "reasignar_respuesta": // Si se respondio un documento mostrar el check
            $tarea_codi = 0 + $_POST["tarea_codi"];
            $sql = "select r.radi_nume_radi
                from (select radi_nume_resp from tarea_radi_respuesta where tarea_codi=$tarea_codi) as tr
                left outer join radicado r on tr.radi_nume_resp=r.radi_nume_radi where r.esta_codi=1 and r.radi_usua_actu=".$_SESSION["usua_codi"];
            $rs = $db->conn->Execute($sql);
            if ($rs && !$rs->EOF)
                $menu = "<br><input type='checkbox' name='txt_tx_reasignar_respuesta_tarea' id='txt_tx_reasignar_respuesta_tarea' value='1' checked> Reasignar documento de respuesta.";
            die($menu);
            break;

        default:
            $titulo = $_POST["txt_tipo_combo"];
            $menu = "No se pudo cargar el combo";
    }

?>
<br>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td valign="top" align="right" width="30%" class="listado1"><b><?=$titulo?>&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
        <td valign="top" align="left"  width="70%" class="listado1"><?=$menu?></td>
    </tr>
</table>
<br>