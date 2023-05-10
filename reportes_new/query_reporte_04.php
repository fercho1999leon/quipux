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
            $where_fecha .= " and radi_fech_radi::date >= '$txt_fecha_desde'::date ";
        if (isset($txt_fecha_hasta))
            $where_fecha .= " and radi_fech_radi::date <= '$txt_fecha_hasta'::date ";

        $main_sql = "select h1.radi_nume_radi
                    , h1.hist_codi as \"hist_codi_ori\"
                    , min(h2.hist_codi) as \"hist_codi_dest\"
                    from (
                        select radi_nume_radi, hist_codi, usua_codi_dest, usua_codi_ori
                        from hist_eventos 
                        where radi_nume_radi in (select radi_nume_radi from radicado where radi_inst_actu=".$_SESSION["inst_codi"]." and esta_codi in (1,6,0) $where_fecha)
                        and usua_codi_dest in (select usua_codi from usuarios where inst_codi=".$_SESSION["inst_codi"]." $where_usr)
                        and (sgd_ttr_codigo in (9,17,25) or (sgd_ttr_codigo=2 and hist_referencia is null))
                        and radi_nume_radi::text like '%0'
                    ) as h1 
                    left outer join hist_eventos h2 on h1.radi_nume_radi=h2.radi_nume_radi and h1.hist_codi<h2.hist_codi
                        and h1.usua_codi_dest=h2.usua_codi_ori and sgd_ttr_codigo in (9,13,16,65)
                    group by h1.hist_codi, h1.radi_nume_radi";

        $sql["select"] = "select ";
        $sql["from"]   = " from ($main_sql) as h ";
        $sql["from"]  .= " left outer join radicado r on h.radi_nume_radi=r.radi_nume_radi ";
        $sql["from"]  .= " left outer join estado er on r.esta_codi=er.esta_codi ";
        $sql["from"]  .= " left outer join usuarios ur on r.radi_usua_actu=ur.usua_codi ";
        $sql["from"]  .= " left outer join hist_eventos ho on h.hist_codi_ori=ho.hist_codi ";
        $sql["from"]  .= " left outer join sgd_ttr_transaccion tho on ho.sgd_ttr_codigo=tho.sgd_ttr_codigo ";
        $sql["from"]  .= " left outer join hist_eventos hd on h.hist_codi_dest=hd.hist_codi ";
        $sql["from"]  .= " left outer join sgd_ttr_transaccion thd on hd.sgd_ttr_codigo=thd.sgd_ttr_codigo ";
        $sql["where"]  = "  ";
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
                    $sql["select"] .= "r.radi_nume_text $nomb_as, ";
                    $sql["select"] .= "'popup_ver_documento(\"'||r.radi_nume_radi||'\")' $nomb_as_drill, ";
                    ++$order;
                    break;
                case "fecha_doc" :
                    $sql["select"] .= "substr(r.radi_fech_radi::text,1,10) || '$descZonaHoraria' $nomb_as, ";
                    $sql["order"] .= " r.radi_fech_radi asc, ";
                    break;
                case "usua_actu" :
                    $sql["select"] .= "coalesce(ur.usua_nomb,'')||' '||coalesce(ur.usua_apellido,'') $nomb_as, ";
                    break;
                case "asunto" :
                    $sql["select"] .= "r.radi_asunto $nomb_as, ";
                    break;
                case "estado" :
                    $sql["select"] .= "er.esta_desc $nomb_as, ";
                    break;
                case "num_respondido" :
                    $sql["select"] .= "rr.radi_nume_text $nomb_as, ";
                    $sql["from"]  .= " left outer join radicado rr on coalesce(r.radi_nume_deri,0)=rr.radi_nume_radi ";
                    break;

                case "hist_ori_fecha" :
                    $sql["select"] .= "substr(ho.hist_fech::text,1,19) || '$descZonaHoraria' $nomb_as, ";
                    $sql["order"] .= " ho.hist_fech asc, ";
                    break;
                case "hist_ori_usua" :
                    $sql["select"] .= "coalesce(uhoo.usua_nomb,'')||' '||coalesce(uhoo.usua_apellido,'') $nomb_as, ";
                    $sql["from"]  .= " left outer join usuarios uhoo on ho.usua_codi_ori=uhoo.usua_codi ";
                    $sql["order"] .= " ho.hist_fech asc, ";
                    break;
                case "hist_ori_desc" :
                    $sql["select"] .= "tho.sgd_ttr_descrip $nomb_as, ";
                    $sql["order"] .= " ho.hist_fech asc, ";
                    break;
                case "hist_dest_fecha" :
                    $sql["select"] .= "substr(hd.hist_fech::text,1,19) || '$descZonaHoraria' $nomb_as, ";
                    $sql["order"] .= " hd.hist_fech asc, ";
                    break;
                case "hist_dest_usua" :
                    $sql["select"] .= "coalesce(uhod.usua_nomb,'')||' '||coalesce(uhod.usua_apellido,'') $nomb_as, ";
                    $sql["from"]  .= " left outer join usuarios uhod on ho.usua_codi_dest=uhod.usua_codi ";
                    $sql["order"] .= " hd.hist_fech asc, ";
                    break;
                case "hist_dest_desc" :
                    $sql["select"] .= "thd.sgd_ttr_descrip $nomb_as, ";
                    $sql["order"] .= " hd.hist_fech asc, ";
                    break;
                case "hist_tiempo" :
                    $sql["select"] .= "replace(split_part((case when hd.hist_fech is not null then hd.hist_fech - ho.hist_fech else current_timestamp - ho.hist_fech end)::text,'.',1)::text,'day','d&iacute;a') $nomb_as, ";
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


/*
Externos
    select h1.radi_nume_radi , h1.hist_codi as "hist_codi_ori" , min(h2.hist_codi) as "hist_codi_dest"
    from (
        select radi_nume_radi, hist_codi, usua_codi_dest, usua_codi_ori
        from hist_eventos
        where radi_nume_radi in (select radi_nume_radi from radicado where radi_inst_actu=2 and esta_codi in (1,6,0) and radi_fech_radi::date >= '2010-09-01'::date and radi_fech_radi::date <= '2010-09-16'::date  and radi_nume_text not like 'SUBINFO%' and radi_nume_text not like 'PRESI-DIRPRO%')
            and usua_codi_dest in (select usua_codi from usuarios where inst_codi=2)
            and (sgd_ttr_codigo in (9,17,25) or (sgd_ttr_codigo=2 and hist_referencia is null)) and radi_nume_radi like '%2'
    ) as h1
    left outer join hist_eventos h2 on h1.radi_nume_radi=h2.radi_nume_radi and h1.hist_codi<h2.hist_codi and h1.usua_codi_dest=h2.usua_codi_ori and sgd_ttr_codigo in (9,13,16,65)
    group by h1.hist_codi, h1.radi_nume_radi

/* */
?>