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
/*****************************************************************************************
**  Funcion que permite cargar los contadores desde el menú y las bandejas              **
*****************************************************************************************/

function cargar_contadores_bandejas($bandeja) {
    global $db, $version_light;
    if ($version_light) return "-1";
    $usuario = $_SESSION["usua_codi"];

    $sql = "";
    switch ($bandeja) {
        case 1: //En elaboración
            $sql = "select count(1) as total, count(case when radi_leido=0 then 1 else null end) as leidos from radicado where esta_codi=1 and radi_usua_actu=$usuario and radi_nume_radi not in (select radi_nume_radi from tarea where estado=1 and usua_codi_ori=$usuario)";
            break;
        case 2: //Recibidos
            $sql = "select count(1) as total, count(case when radi_leido=0 then 1 else null end) as leidos from radicado where esta_codi=2 and radi_usua_actu=$usuario and radi_nume_radi not in (select radi_nume_radi from tarea where estado=1 and usua_codi_ori=$usuario)";
            break;
        case 6:  //Eliminado
        case 84: //Eliminado - ciudadanos firma
            $sql = "select count(1) as total, 0 as leidos from radicado where esta_codi=7 and radi_usua_actu=$usuario";
            break;
        case 7:  //No enviado
        case 85: //No enviado - ciudadanos firma
            $sql = "select count(1) as total, 0 as leidos from radicado where esta_codi=3 and radi_usua_actu=$usuario";
            break;
        case 8: //Enviados
            $sql = "select count(1) as total, 0 as leidos from radicado where esta_codi=6 and radi_nume_radi=radi_nume_temp and radi_usua_actu=$usuario";
            break;
        case 10: //Archivado
            $sql = "select count(1) as total, 0 as leidos from radicado where esta_codi=0 and radi_usua_actu=$usuario";
            break;
        case 12: //Reasignados
            $sql = "select count(1) as total, 0 as leidos from hist_eventos where usua_codi_ori=$usuario and sgd_ttr_codigo=9";
            break;
        case 13: //Informados
            $sql = "select count(1) as total, count(case when info_leido=0 then 1 else null end) as leidos from informados where usua_codi=$usuario";
            break;
        case 14: //compartida
            $sql = "select count(1) as total, count(case when radi_leido=0 then 1 else null end) as leidos from radicado where esta_codi=2 and radi_usua_actu=".$_SESSION["usua_codi_jefe"]." and radi_nume_radi not in (select radi_nume_radi from tarea where estado=1 and usua_codi_ori=".$_SESSION["usua_codi_jefe"].")";            
            break;
        case 15: //Tareas Recibidas
            $sql = "select count(1) as total, count(case when leido=0 then 1 else null end) as leidos from tarea where estado=1 and $usuario=usua_codi_dest";
            break;
        case 16: //Tareas Enviadas
            $sql = "select count(1) as total, count(case when leido=0 then 1 else null end) as leidos from tarea where $usuario=usua_codi_ori";
            break;
        case 80:
            $sql = "select count(1) as total, 0 as leidos from radicado where radi_nume_radi::text like '%1' and esta_codi in (0,2)
                        and string_to_array(trim(both '-' from radi_usua_rem), '--') @> array['$usuario']";
            break;
        case 81: // Recibidos ciudadanos
            $sql = "select count(1) as total, 0 as leidos from radicado where radi_nume_radi::text like '%1' and esta_codi=6
                        and string_to_array(trim(both '-' from radi_usua_dest), '--') @> array['$usuario']";
            break;
        case 82: //Documentos en elaboración - ciudadanos firma
            $sql = "select count(1) as total, 0 as leidos from radicado where radi_usua_actu=$usuario and esta_codi=1";
            break;
        case 83: //Documentos recibidos - ciudadanos firma
            $sql = "select count(1) as total, 0 as leidos from radicado where radi_nume_radi::text like '%1' and esta_codi in (2,6)
                        and string_to_array(trim(both '-' from radi_usua_dest), '--') @> array['$usuario']";
            break;
        case 86: //Documentos enviados - ciudadanos firma
            $sql = "select count(1) as total, 0 as leidos from radicado where string_to_array(trim(both '-' from radi_usua_rem), '--') @> array['$usuario']
                        and ((radi_nume_radi::text like '%1' and esta_codi in (0,2)) or (radi_nume_radi::text like '%0' and esta_codi in (6)))";
            break;
        case 90: //Documentos firmados electrónicamente por ciudadanos
            $sql = "select count(1) as total, 0 as leidos from radicado where esta_codi=9 and radi_inst_actu=".$_SESSION["inst_codi"];
            break;
        case 99: //Bandeja Por Imprimir
            $sql = "select count(1) as total, 0 as leidos from radicado where esta_codi=5 and radi_usua_actu in (select usua_codi from usuarios where depe_codi=".$_SESSION["depe_codi"].")";
            break;
        default:
            return "-1";
    }
    if ($sql != "") {
        $rs = $db->query($sql);
        if ($rs->fields["LEIDOS"] > 0) $contador = $rs->fields["LEIDOS"]."/";
        $contador .= $rs->fields["TOTAL"];
    }
    return $contador;
    echo $sql;
}

?>