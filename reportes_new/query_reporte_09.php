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
        $where_inst_origen = " and radi_usua_radi in (select usua_codi from usuarios where inst_codi=".$_SESSION["inst_codi"].")";
        if (isset($txt_depe_codi) && $txt_depe_codi != "0")
            $where_inst_origen = " and radi_usua_radi in (select usua_codi from usuarios where depe_codi in ($txt_depe_codi))";
        if (isset($txt_usua_codi) && $txt_usua_codi != "0")
            $where_inst_origen = " and radi_usua_radi in ($txt_usua_codi)";

        $where_inst_destino = " and radi_usua_actu not in (select usua_codi from usuarios where inst_codi=".$_SESSION["inst_codi"].")";
        if (isset($txt_inst_codi) && $txt_inst_codi != "0")
            $where_inst_destino = " and radi_usua_actu in (select usua_codi from usuarios where inst_codi=$txt_inst_codi)";
        
        $where_fecha = "";
        if (isset($txt_fecha_desde))
            $where_fecha .= " and radi_fech_radi::date >= '$txt_fecha_desde'::date ";
        if (isset($txt_fecha_hasta))
            $where_fecha .= " and radi_fech_radi::date <= '$txt_fecha_hasta'::date ";

        $main_sql = "select radi_nume_radi, radi_cuentai, radi_nume_text, radi_asunto
                        , radi_fech_ofic, radi_usua_rem, radi_usua_dest
                        , radi_leido, radi_usua_actu, radi_usua_radi, esta_codi
                    from radicado
                    where radi_fech_firma is not null $where_inst_origen $where_inst_destino $where_fecha";


        $sql["select"] = "select  -- Reporte 09 - Docs. enviados y no respondidos otras instituciones - USR: ".$_SESSION["inst_codi"]."\n";
        $sql["from"]   = " from ($main_sql) as r ";
        $sql["from"]  .= " left outer join usuario u on r.radi_usua_radi=u.usua_codi";
        if (strpos($txt_lista_columnas, 'num_resp')!==false or strpos($txt_lista_columnas, 'fecha_resp')!==false) {
            $sql["from"]  .= " left outer join hist_eventos h on r.radi_nume_radi=h.radi_nume_radi and sgd_ttr_codigo=12";
            $sql["from"]  .= " left outer join radicado rh on coalesce(h.hist_referencia::numeric,0)=rh.radi_nume_radi";
        }
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
                case "inst_nombre" :
                    $sql["select"] .= "u.inst_nombre $nomb_as, ";
                    break;
                case "num_doc" :
                    $sql["select"] .= "r.radi_cuentai $nomb_as, ";
                    $sql["select"] .= "'popup_ver_documento(\"'||r.radi_nume_radi||'\")' $nomb_as_drill, ";
                    ++$order;
                    break;
                case "num_doc_dest" :
                    $sql["select"] .= "r.radi_nume_text $nomb_as, ";
                    break;
                case "fecha_envio" :
                    $sql["select"] .= "substr(r.radi_fech_ofic::text,1,19) || '$descZonaHoraria' $nomb_as, ";
                    break;
                case "asunto" :
                    $sql["select"] .= "replace(r.radi_asunto,'\"','') $nomb_as, ";
                    break;
                case "usua_rem" :
                    $sql["select"] .= "ver_usuarios(r.radi_usua_rem,'') $nomb_as, ";
                    break;
                case "usua_dest" :
                    $sql["select"] .= "ver_usuarios(r.radi_usua_dest,'') $nomb_as, ";
                    break;
                case "usua_actu" :
                    $sql["select"] .= "ver_usuarios(r.radi_usua_actu::text,'') $nomb_as, ";
                    break;
                case "estado" :
                    $sql["select"] .= "e.esta_desc $nomb_as, ";
                    $sql["from"]  .= " left outer join estado e on r.esta_codi=e.esta_codi";
                    break;
                case "num_resp" :
                    $sql["select"] .= "rh.radi_nume_text $nomb_as, ";
                    break;
                case "fecha_resp" :
                    $sql["select"] .= "substr(rh.radi_fech_ofic::text,1,19) || '$descZonaHoraria' $nomb_as, ";
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