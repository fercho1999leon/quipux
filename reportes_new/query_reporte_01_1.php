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

        $sql["select"] = "select ";
        $sql["from"]   = " from radicado r";
        $sql["from"]  .= " left outer join tiporad td on r.radi_tipo = td.trad_codigo ";
        $sql["from"]  .= " left outer join usuarios u on r.radi_usua_actu=u.usua_codi ";
        $sql["from"]  .= " left outer join dependencia d on d.depe_codi=u.depe_codi ";
        $sql["where"]  = " where radi_inst_actu=".$_SESSION["inst_codi"]. " ";
        $sql["order"]  = " order by r.radi_fech_ofic asc, r.radi_nume_text asc  ";
        $sql["limit"]  = "";
        if ((0 + $num_max_registros) > 0) 
            $sql["limit"]  = " limit $num_max_registros offset 0";

        $cols = split(",", $txt_lista_columnas);

        for ($i=1 ; $i<count($cols) ; ++$i) {
            $nomb_as = 'as "' . $cols[$i] . '"'; //Nombre de la columna en el query
            $nomb_as_drill = 'as "' . $cols[$i] . '_drill"'; //Nombre de la columna en el query
            switch ($cols[$i]) {
                case "remitente" :
                    $sql["select"] .= "ver_usuarios(r.radi_usua_rem,',') $nomb_as, ";
                    break;
                case "destinatario" :
                    $sql["select"] .= "ver_usuarios(r.radi_usua_dest,',') $nomb_as, ";
                    break;
                case "fecha" :
                    $sql["select"] .= "substr(r.radi_fech_ofic::text,1,19) || '$descZonaHoraria' $nomb_as, ";
                    break;
                case "num_doc" :
                    $sql["select"] .= "r.radi_nume_text $nomb_as, ";
                    $sql["select"] .= "'popup_ver_documento(\"'||r.radi_nume_radi||'\")' $nomb_as_drill, ";
                    break;
                case "referencia" :
                    $sql["select"] .= "r.radi_cuentai $nomb_as, ";
                    break;
                case "asunto" :
                    $sql["select"] .= "replace(r.radi_asunto,'\"','') $nomb_as, ";
                    break;
                case "usuario" :
                    $sql["select"] .= "u.usua_nomb || ' ' || u.usua_apellido $nomb_as, ";
                    break;
                case "area" :
                    $sql["select"] .= "d.depe_nomb $nomb_as, ";
                    break;
                case "tipo" :
                    $sql["select"] .= "td.trad_descr $nomb_as, ";
                    break;
                default:
                    $sql["select"] .= "'' $nomb_as, ";
                    break;

            }
        }

        if ($txt_estado == "6")    $sql["where"] .= " and radi_nume_radi::text like '%0' and radi_fech_firma is null";
        if ($txt_estado == "6r")   {$txt_estado = "6"; $sql["where"] .= " and radi_nume_radi::text like '%2'";}
        if ($txt_estado == "firma")   {$txt_estado = "6,0"; $sql["where"] .= " and radi_nume_radi::text like '%0' and radi_fech_firma is not null ";}
        $sql["where"]  .= " and r.esta_codi in ($txt_estado)";
        
        if (isset($txt_depe_codi) && $txt_depe_codi != "0") $sql["where"] .= " and d.depe_codi in ($txt_depe_codi)";
        if (isset($txt_usua_codi) && $txt_usua_codi != "0") $sql["where"] .= " and r.radi_usua_actu in ($txt_usua_codi)";

        if (isset($txt_fecha_sel)) 
            $sql["where"]  .= " and r.radi_fech_ofic::text like '$txt_fecha_sel%' ";
        if (isset($txt_fecha_desde))
            $sql["where"]  .= " and r.radi_fech_ofic::date >= '$txt_fecha_desde'::date ";
        if (isset($txt_fecha_hasta))
            $sql["where"]  .= " and r.radi_fech_ofic::date <= '$txt_fecha_hasta'::date ";

        $isql = substr($sql["select"],0,-2) . $sql["from"] . $sql["where"] . $sql["group"] . $sql["order"] . $sql["limit"];

//echo $isql."<hr>";

	break;
}

?>