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
        $sql["from"]   = " from (select radi_nume_radi, esta_codi, radi_usua_actu, radi_fech_ofic, radi_fech_firma from radicado where radi_inst_actu=".$_SESSION["inst_codi"].") as r ";
        $sql["from"]  .= " left outer join usuarios u on r.radi_usua_actu=u.usua_codi ";
        $sql["where"]  = " where u.inst_codi=" . $_SESSION["inst_codi"];
        $sql["group"]  = " group by ";
        $sql["order"]  = " order by ";
        $sql["limit"]  = "";
        if ((0 + $num_max_registros) > 0) 
            $sql["limit"]  = " limit $num_max_registros offset 0";
        if ($txt_depe_codi != "0") {
            $sql["where"] .= " and u.depe_codi in ($txt_depe_codi)";
        }

        $cols = split(",", $txt_lista_columnas);

        $drill = "";
        $group = 0;

        for ($i=1 ; $i<count($cols) ; ++$i) {
            $nomb_as = 'as "' . $cols[$i] . '"'; //Nombre de la columna en el query
            switch ($cols[$i]) {
                case "fecha1" :
                    $sql["select"] .= "substr(r.radi_fech_ofic::text,1,4) $nomb_as, ";
                    $sql["group"] .= ++$group . ", ";
                    drill_parametro("txt_fecha_sel", "substr(r.radi_fech_ofic::text,1,4)");
//                    drill_anadir("01");
                    break;
                case "fecha2" :
                    $sql["select"] .= "substr(r.radi_fech_ofic::text,1,7) $nomb_as, ";
                    $sql["group"] .= ++$group . ", ";
                    drill_parametro("txt_fecha_sel", "substr(r.radi_fech_ofic::text,1,7)");
//                    drill_anadir("01");
                    break;
                case "fecha3" :
                    $sql["select"] .= "substr(r.radi_fech_ofic::text,1,10) || '$descZonaHoraria' $nomb_as, ";
                    $sql["group"] .= ++$group . ", ";
                    drill_parametro("txt_fecha_sel", "substr(r.radi_fech_ofic::text,1,10)");
//                    drill_anadir("01");
                    break;
                case "area" :
                    $sql["select"] .= "d.depe_nomb $nomb_as, ";
                    $sql["from"] .= "left outer join dependencia d on d.depe_codi=u.depe_codi ";
                    $sql["group"] .= ++$group . ", ";
                    drill_parametro("txt_depe_codi", "d.depe_codi");
//                    drill_anadir("01");
                    break;
                case "usuario" :
                    $sql["select"] .= "u.usua_nomb || ' ' || u.usua_apellido $nomb_as, ";
                    $sql["group"] .= ++$group . ", ";
                    drill_parametro("txt_usua_codi", "u.usua_codi");
//                    drill_anadir("01");
                    break;
                case "estado0" :
                    $sql["select"] .= "count(case when r.esta_codi=0 then 1 else null end) $nomb_as, ";
                    ++$group;
                    drill_anadir("01_1","txt_estado","'0'");
                    break;
                case "estado1" :
                    $sql["select"] .= "count(case when r.esta_codi=1 then 1 else null end) $nomb_as, ";
                    ++$group;
                    drill_anadir("01_1","txt_estado","'1'");
                    break;
                case "estado2" :
                    $sql["select"] .= "count(case when r.esta_codi=2 then 1 else null end) $nomb_as, ";
                    ++$group;
                    drill_anadir("01_1","txt_estado","'2'");
                    break;
                case "estado3" :
                    $sql["select"] .= "count(case when r.esta_codi=3 then 1 else null end) $nomb_as, ";
                    ++$group;
                    drill_anadir("01_1","txt_estado","'3'");
                    break;
                case "estado5" :
                    $sql["select"] .= "count(case when r.esta_codi in (5) then 1 else null end) $nomb_as, ";
                    ++$group;
                    drill_anadir("01_1","txt_estado","'5'");
                    break;
                case "estado6" :
                    $sql["select"] .= "count(case when r.esta_codi=6 and radi_nume_radi::text like '%0' and r.radi_fech_firma is null then 1 else null end) $nomb_as, ";
                    ++$group;
                    drill_anadir("01_1","txt_estado","'6'");
                    break;
                case "estado6r" :
                    $sql["select"] .= "count(case when r.esta_codi=6 and radi_nume_radi::text like '%2' then 1 else null end) $nomb_as, ";
                    ++$group;
                    drill_anadir("01_1","txt_estado","'6r'");
                    break;
                case "estadot" :
                        $sql["select"] .= "count(case when r.esta_codi in (0,1,2,3,5) or (r.esta_codi=6 and r.radi_nume_radi::text not like '%1') then 1 else null end) $nomb_as, ";
                        ++$group;
                    break;
                case "firmados" :
                    $sql["select"] .= "count(case when r.esta_codi in (6,0) and r.radi_nume_radi::text like '%0' and r.radi_fech_firma is not null then 1 else null end) $nomb_as, ";
                    ++$group;
                    drill_anadir("01_1","txt_estado","'firma'");
                    break;
                default:
                    $sql["select"] .= "'' $nomb_as, ";
                    $sql["group"] .= ++$group . ", ";
                    break;

            }
            $sql["order"] .= "$i asc, ";
        }

        if (isset($txt_usua_codi)) {
            if ($txt_usua_codi != "0")
                $sql["where"]  .= "and u.usua_codi in ($txt_usua_codi) ";
        }
        if (isset($txt_fecha_desde))
            $sql["where"]  .= " and r.radi_fech_ofic::date >= '$txt_fecha_desde'::date ";
        if (isset($txt_fecha_hasta))
            $sql["where"]  .= " and r.radi_fech_ofic::date <= '$txt_fecha_hasta'::date ";
        if (isset($txt_fecha_sel))
            $sql["where"]  .= " and r.radi_fech_ofic::text like '$txt_fecha_sel%' ";

        $isql = substr($sql["select"],0,-2) . $sql["from"] . $sql["where"] . substr($sql["group"],0,-2) . substr($sql["order"],0,-2). $sql["limit"];

//echo "<hr>$isql<hr>";

	break;
}


?>
