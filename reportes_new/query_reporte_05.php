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
            $where_usr = " and radi_usua_radi in (select usua_codi from usuarios where depe_codi in ($txt_depe_codi))";
        if (isset($txt_usua_codi) && $txt_usua_codi != "0")
            $where_usr = " and radi_usua_radi in ($txt_usua_codi)";
        
        $where_fecha = "";
        if (isset($txt_fecha_desde))
            $where_fecha .= " and radi_fech_radi::date >= '$txt_fecha_desde'::date ";
        if (isset($txt_fecha_hasta))
            $where_fecha .= " and radi_fech_radi::date <= '$txt_fecha_hasta'::date ";

        $main_sql = "select -- Reporte 05
            r.radi_nume_radi, r.radi_nume_text, r.radi_fech_radi, h.hist_obse, coalesce(h.hist_referencia,'0')::numeric as hist_referencia,
            r.radi_usua_actu, r.esta_codi, h.sgd_ttr_codigo, r.radi_fech_ofic, r.radi_asunto, r.radi_cuentai, h.hist_fech
            from
                (
                  select coalesce(r2.radi_nume_radi,r1.radi_nume_radi) as radi_nume_radi, r1.radi_nume_text, r1.radi_fech_radi, r2.radi_nume_temp, r1.radi_fech_ofic, r1.radi_asunto, r1.radi_cuentai
                  , coalesce(r2.radi_usua_actu,r1.radi_usua_actu) as radi_usua_actu, coalesce(r2.esta_codi,r1.esta_codi) as esta_codi
                  from
                    ( select radi_nume_radi, radi_nume_text, radi_fech_radi, radi_fech_ofic, radi_asunto, radi_cuentai, radi_usua_actu, esta_codi
                      from radicado
                      where radi_nume_radi::text like '%2' and radi_inst_actu=".$_SESSION["inst_codi"]."
                        $where_fecha $where_usr
                    ) as r1
                    left outer join radicado r2 on r1.radi_nume_radi=r2.radi_nume_temp and r2.radi_nume_radi::text like '%1'
                ) as r
                left outer join hist_eventos h on r.radi_nume_radi=h.radi_nume_radi and h.sgd_ttr_codigo in (12,13,16)
            ";


        $sql["select"] = "select ";
        $sql["from"]   = " from ($main_sql) as ro ";
        $sql["from"]  .= " left outer join radicado rr on (rr.radi_nume_temp=ro.hist_referencia and rr.radi_nume_radi::text like '%1' and rr.esta_codi in (0,2,4,5,6)) or (rr.radi_nume_radi=ro.hist_referencia and rr.esta_codi in (1,7,8)) ";
        $sql["where"]  = "";
        $sql["order"]  = " order by ";
        $sql["limit"]  = "";
        if ((0 + $num_max_registros) > 0) 
            $sql["limit"]  = " limit $num_max_registros offset 0";

        $cols = split(",", $txt_lista_columnas);
        $order = 0;

        for ($i=1 ; $i<count($cols) ; ++$i) {
            $nomb_as = 'as "' . $cols[$i] . '"'; //Nombre de la columna en el query
            $nomb_as_drill = 'as "' . $cols[$i] . '_drill"'; //Nombre de la columna en el query
            switch ($cols[$i]) {
                case "num_doc" :
                    $sql["select"] .= "ro.radi_nume_text $nomb_as, ";
                    $sql["select"] .= "'popup_ver_documento(\"'||ro.radi_nume_radi||'\")' $nomb_as_drill, ";
                    ++$order;
                    break;
                case "fecha_reg" :
                    $sql["select"] .= "substr(ro.radi_fech_radi::text,1,19) || '$descZonaHoraria' $nomb_as, ";
                    break;
                case "usua_actu" :
                    $sql["select"] .= "ua.usua_nombre $nomb_as, ";
                    $sql["from"]  .= " left outer join usuario ua on ro.radi_usua_actu=ua.usua_codi ";
                    break;
                case "estado" :
                    $sql["select"] .= "es.esta_desc $nomb_as, ";
                    $sql["from"]  .= " left outer join estado es on ro.esta_codi = es.esta_codi ";
                    break;
                case "asunto" :
                    $sql["select"] .= "ro.radi_asunto $nomb_as, ";
                    break;
                case "num_ref" :
                    $sql["select"] .= "ro.radi_cuentai $nomb_as, ";
                    break;
                case "fecha_ref" :
                    $sql["select"] .= "substr(ro.radi_fech_ofic::text,1,10) || '$descZonaHoraria' $nomb_as, ";
                    break;
                case "accion" :
                    $sql["select"] .= "tr.sgd_ttr_descrip $nomb_as, ";
                    $sql["from"]  .= " left outer join sgd_ttr_transaccion tr on tr.sgd_ttr_codigo = ro.sgd_ttr_codigo ";
                    break;
                case "num_resp" :
                    $sql["select"] .= "(case when rr.esta_codi in (7,8) then rr.radi_nume_text||' (Eliminado)' else rr.radi_nume_text end) $nomb_as, ";
                    break;
                case "fecha_resp" :
                    $sql["select"] .= "substr(rr.radi_fech_ofic::text,1,19) || '$descZonaHoraria' $nomb_as, ";
                    break;
                case "observa" :
                    $sql["select"] .= "ro.hist_obse $nomb_as, ";
                    break;
                case "tiempo" :
                    $sql["select"] .= "(case when rr.radi_fech_ofic is not null then rr.radi_fech_ofic::date - ro.radi_fech_radi::date else (case when ro.hist_fech is not null then ro.hist_fech::date - ro.radi_fech_radi::date else current_timestamp::date - ro.radi_fech_radi::date end) end)::text || ' d&iacute;as' $nomb_as, ";
                    break;

                default:
                    $sql["select"] .= "'' $nomb_as, ";
                    break;

            }
            $sql["order"] .= ++$order . " asc, ";
        }

        
        $isql = substr($sql["select"],0,-2) . $sql["from"] . $sql["where"] . substr($sql["order"],0,-2). $sql["limit"];

//echo "<hr>".str_replace("<", "&lt;", $isql)."<hr>";

	break;
}

?>