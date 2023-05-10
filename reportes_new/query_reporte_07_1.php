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

if (!$db->driver){ $db = $this->db; }	//Esto sirve para cuando se llama este archivo dentro de clases donde no se conoce $db.

switch($db->driver) {
    case 'postgres':

        $where_usr = "";
        if (isset($txt_depe_codi) && $txt_depe_codi != "0")
            $where_usr = " and depe_codi in ($txt_depe_codi)";
        if (isset($txt_usua_codi) && $txt_usua_codi != "0")
            $where_usr = " and usua_codi in ($txt_usua_codi)";

        $where_fecha = "";
        if (isset($txt_fecha_desde))
            $where_fecha .= " and t.fecha_inicio::date >= '$txt_fecha_desde'::date ";
        if (isset($txt_fecha_hasta))
            $where_fecha .= " and t.fecha_inicio::date <= '$txt_fecha_hasta'::date ";
        if (isset($txt_fecha_sel))
            $where_fecha  .= " and t.fecha_inicio::text like '$txt_fecha_sel%' ";

        $where_hist = "";
        if ($txt_tarea == "e0") $where_hist = " t.usua_codi_ori=u.usua_codi";
        if ($txt_tarea == "e1") $where_hist = " t.usua_codi_ori=u.usua_codi and t.estado=1";
        if ($txt_tarea == "e2") $where_hist = " t.usua_codi_ori=u.usua_codi and t.estado=2";
        if ($txt_tarea == "e3") $where_hist = " t.usua_codi_ori=u.usua_codi and t.estado=3";
        if ($txt_tarea == "r0") $where_hist = " t.usua_codi_dest=u.usua_codi";
        if ($txt_tarea == "r1") $where_hist = " t.usua_codi_dest=u.usua_codi and t.estado=1";
        if ($txt_tarea == "r2") $where_hist = " t.usua_codi_dest=u.usua_codi and t.estado=2";
        if ($txt_tarea == "r3") $where_hist = " t.usua_codi_dest=u.usua_codi and t.estado=3";

        $sql["select"] = "select ";
        $sql["from"]   = " from (select * from usuario where inst_codi=".$_SESSION["inst_codi"]." $where_usr) as u ";
        $sql["from"]  .= " left outer join tarea t on $where_hist $where_fecha ";
        $sql["from"]  .= " left outer join radicado r on r.radi_nume_radi=t.radi_nume_radi";
        $sql["from"]  .= " left outer join tarea_hist_eventos th on t.comentario_inicio=th.tarea_hist_codi ";
        $sql["where"]  = " where r.radi_nume_radi is not null";
        $sql["order"]  = " order by 1,2,3,4,5  ";
        $sql["limit"]  = "";
        if ((0 + $num_max_registros) > 0) 
            $sql["limit"]  = " limit $num_max_registros offset 0";

        $cols = split(",", $txt_lista_columnas);

        for ($i=1 ; $i<count($cols) ; ++$i) {
            $nomb_as = 'as "' . $cols[$i] . '"'; //Nombre de la columna en el query
            $nomb_as_drill = 'as "' . $cols[$i] . '_drill"'; //Nombre de la columna en el query
            switch ($cols[$i]) {
                case "num_doc" :
                    $sql["select"] .= "r.radi_nume_text $nomb_as, ";
                    $sql["select"] .= "'popup_ver_documento(\"'||r.radi_nume_radi||'\")' $nomb_as_drill, ";
                    break;
                case "asunto" :
                    $sql["select"] .= "replace(r.radi_asunto,'\"','') $nomb_as, ";
                    break;
                case "remitente" :
                    $sql["select"] .= "ver_usuarios(r.radi_usua_rem,',<br>') $nomb_as, ";
                    break;
                case "destinatario" :
                    $sql["select"] .= "ver_usuarios(r.radi_usua_dest,',<br>') $nomb_as, ";
                    break;
                case "fecha_inicio" :
                    $sql["select"] .= "substr(t.fecha_inicio::text,1,19) || '$descZonaHoraria' $nomb_as, ";
                    break;
                case "fecha_fin" :
                    $sql["select"] .= "substr(t.fecha_fin::text,1,19) || '$descZonaHoraria' $nomb_as, ";
                    break;
                case "fecha_max" :
                    $sql["select"] .= "t.fecha_maxima::date $nomb_as, ";
                    break;
                case "dias_retraso" :
                    $sql["select"] .= "(case when (coalesce(t.fecha_fin,now())::date-t.fecha_maxima::date)<0 then 0 else (coalesce(t.fecha_fin,now())::date-t.fecha_maxima::date) end)::text||' d&iacute;as' $nomb_as, ";
                    break;
                case "avance" :
                    $sql["select"] .= "t.avance::text||' %' $nomb_as, ";
                    break;
                case "usua_ori" :
                    $sql["select"] .= "ver_usuarios(t.usua_codi_ori::text,',') $nomb_as, ";
                    break;
                case "usua_dest" :
                    $sql["select"] .= "ver_usuarios(t.usua_codi_dest::text,',') $nomb_as, ";
                    break;
                case "usua_actu" :
                    $sql["select"] .= "ver_usuarios(r.radi_usua_actu::text,',') $nomb_as, ";
                    break;
                case "comentario" :
                    $sql["select"] .= "replace(th.comentario,'\"','') $nomb_as, ";
                    break;
                case "estado" :
                    $sql["select"] .= "case when t.estado=1 then 'Pendiente' else (case when t.estado=2 then 'Finalizado' else 'Cancelado' end) end $nomb_as, ";
                    break;

                default:
                    $sql["select"] .= "'' $nomb_as, ";
                    break;

            }
        }


        $isql = substr($sql["select"],0,-2) . $sql["from"] . $sql["where"] . $sql["group"] . $sql["order"] . $sql["limit"];

//echo $isql."<hr>";

	break;
}

?>