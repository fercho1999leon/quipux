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

        // definimos las áreas de las que se generará el reporte

        $where_usr = "";
        if (isset($txt_depe_codi) && $txt_depe_codi != "0")
            $where_usr = " and depe_codi in ($txt_depe_codi)";
        if (isset($txt_usua_codi) && $txt_usua_codi != "0")
            $where_usr = " and usua_codi in ($txt_usua_codi)";

        $where_fecha = "";
        if (isset($txt_fecha_desde))
            $where_fecha .= " and r.radi_fecha::date >= '$txt_fecha_desde'::date ";
        if (isset($txt_fecha_hasta))
            $where_fecha .= " and r.radi_fecha::date <= '$txt_fecha_hasta'::date ";
        if (isset($txt_fecha_sel))
            $where_fecha .= " and r.radi_fecha::text like '$txt_fecha_sel%' ";

        $where_hist = "(t.usua_codi_ori=u.usua_codi or t.usua_codi_dest=u.usua_codi)";
//        if (strpos($txt_lista_columnas, "usua_ori") !== false) $where_hist = "h.usua_codi_ori=u.usua_codi";
//        if (strpos($txt_lista_columnas, "usua_dest") !== false) {
//            if ($where_hist != "") $where_hist .= " or ";
//            $where_hist .= "h.usua_codi_dest=u.usua_codi";
//        }
//        if ($where_hist != "") $where_hist = " and ($where_hist) ";

        $select_radicado = "select radi_nume_radi, radi_tipo
                                , (case when radi_nume_radi::text like '%0' then radi_fech_ofic else radi_fech_radi end) as radi_fecha
                                , (case when radi_nume_radi::text like '%0' then radi_usua_actu else radi_usua_radi end) as radi_usua
                            from radicado
                            where radi_inst_actu=".$_SESSION["inst_codi"]."
                                and radi_nume_radi=radi_nume_temp and esta_codi in (0,6)";

        $sql["select"] = "select -- Reporte 08 - Tipos de documentos - USR: ".$_SESSION["inst_codi"]."\n";
        $sql["from"]   = " from (select * from usuario where inst_codi=".$_SESSION["inst_codi"]." $where_usr) as u ";
        $sql["from"]  .= " left outer join ($select_radicado) r on r.radi_usua=u.usua_codi $where_fecha";
        $sql["where"]  = " where r.radi_fecha is not null";
        $sql["group"]  = " group by ";
        $sql["order"]  = " order by ";
        $sql["limit"]  = "";
        if ((0 + $num_max_registros) > 0) 
            $sql["limit"]  = " limit $num_max_registros offset 0";

        $cols = split(",", $txt_lista_columnas);

        $drill = "";
        $group = 0;

        for ($i=1 ; $i<count($cols) ; ++$i) {
            $nomb_as = 'as "' . $cols[$i] . '"'; //Nombre de la columna en el query
            switch ($cols[$i]) {
                case "fecha1" :
                    $sql["select"] .= "substr(r.radi_fecha::text,1,4) $nomb_as, ";
                    $sql["group"] .= ++$group . ", ";
                    drill_parametro("txt_fecha_sel", "substr(r.radi_fecha::text,1,4)");
//                    drill_anadir("01");
                    break;
                case "fecha2" :
                    $sql["select"] .= "substr(r.radi_fecha::text,1,7) $nomb_as, ";
                    $sql["group"] .= ++$group . ", ";
                    drill_parametro("txt_fecha_sel", "substr(r.radi_fecha::text,1,7)");
//                    drill_anadir("01");
                    break;
                case "fecha3" :
                    $sql["select"] .= "substr(r.radi_fecha,1,10) || '$descZonaHoraria' $nomb_as, ";
                    $sql["group"] .= ++$group . ", ";
                    drill_parametro("txt_fecha_sel", "substr(r.radi_fecha::text,1,10)");
//                    drill_anadir("01");
                    break;
                case "area" :
                    $sql["select"] .= "u.depe_nomb $nomb_as, ";
                    $sql["group"] .= ++$group . ", ";
                    drill_parametro("txt_depe_codi", "u.depe_codi");
//                    drill_anadir("01");
                    break;
                case "usuario" :
                    $sql["select"] .= "u.usua_nombre $nomb_as, ";
                    $sql["group"] .= ++$group . ", ";
                    drill_parametro("txt_usua_codi", "u.usua_codi");
//                    drill_anadir("01");
                    break;
                default:
                    if (substr($cols[$i], 0, 5) == "tipo_") {
                        $sql["select"] .= "count(case when r.radi_tipo=".substr($cols[$i], 5)." then 1 else null end) $nomb_as, ";
                        ++$group;
                        drill_anadir("08_1","txt_radi_tipo", substr($cols[$i], 5));
                    } else {
                        $sql["select"] .= "'' $nomb_as, ";
                        $sql["group"] .= ++$group . ", ";
                    }
                    break;

            }
            $sql["order"] .= "$i asc, ";
        }

        $isql = substr($sql["select"],0,-2) . $sql["from"] . $sql["where"] . substr($sql["group"],0,-2) . substr($sql["order"],0,-2). $sql["limit"];

//echo "<hr>$isql<hr>";

	break;
}


?>
