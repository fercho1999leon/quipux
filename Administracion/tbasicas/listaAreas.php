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
    /*session_start();
    $ruta_raiz = "../..";
    include_once "$ruta_raiz/rec_session.php";*/

function obtenerAreas($codInst, $db)
{
    $sqlDepePadre = "select
                        depe_codi,
                        depe_nomb as depe_nomb,
                        depe_codi_padre
                     from
                        dependencia
                     where
                        depe_estado=1 and inst_codi = ".$codInst.
                        " and depe_codi = coalesce(depe_codi_padre,depe_codi) order by depe_nomb";

    $rsDepePadre=$db->conn->query($sqlDepePadre);
   // $menu_areas = "<ul>";
    while(!$rsDepePadre->EOF)
    {
        //Contando si la dependencia tiene hijos
        $sqlDepeHijo = "select
                            count(depe_codi) as depe_codi
                        from
                            dependencia
                        where
                            depe_estado=1 and depe_codi_padre = ".$rsDepePadre->fields["DEPE_CODI"].
                            " and depe_codi <> depe_codi_padre";
        $rsDepeHijo=$db->conn->query($sqlDepeHijo);
        if($rsDepeHijo->fields["DEPE_CODI"]!='0')
        {
            $menu_areas .= '<li><a href="javascript:;" onclick="datosArea('.$rsDepePadre->fields["DEPE_CODI"].');">';
            //$menu_areas .= " (".$rsDepeHijo->fields["DEPE_CODI"].")";
        }
        else
            $menu_areas .= '<li><a href="javascript:;" onclick="datosArea('.$rsDepePadre->fields["DEPE_CODI"].');">';
        $menu_areas .= $rsDepePadre->fields["DEPE_NOMB"];

        $menu_areas .= "</a>";
        if($rsDepeHijo->fields["DEPE_CODI"]!='0')
        {
            //Si es diferente de cero consultar los hijos recursiva
            $menu_areas .= "<ul>";
            $menu_areas .= obtenerDependencia($rsDepePadre->fields["DEPE_CODI"], $db);
            $menu_areas .= "</ul>";
        }
        $menu_areas .= "</li>";
        $rsDepePadre->MoveNext();
    }
   // $menu_areas .= "</ul>";
    return $menu_areas;
}

// Funcion para consultar las areas hijas
function obtenerDependencia($depe_codi, $db){

    $sqlDepeHijo = "select
                        depe_codi,
                        depe_nomb,
                        depe_codi_padre
                     from
                        dependencia
                     where
                        depe_estado=1 and depe_codi_padre = ".$depe_codi.
                        " and depe_codi <> depe_codi_padre order by depe_nomb";

    $rsDepeHijo=$db->conn->query($sqlDepeHijo);

    while(!$rsDepeHijo->EOF)
    {
        $sqlCountHijo = "select
                            count(depe_codi) as depe_codi
                        from
                            dependencia
                        where
                            depe_estado=1 and depe_codi_padre = ".$rsDepeHijo->fields["DEPE_CODI"].
                            " and depe_codi <> depe_codi_padre";
        $rsCountHijo=$db->conn->query($sqlCountHijo);
        if($rsCountHijo->fields["DEPE_CODI"]!='0')
        {
            $menu_depeHijo .= '<li><a href="javascript:;" onclick="datosArea('.$rsDepeHijo->fields["DEPE_CODI"].');">';
            //$menu_depeHijo .= " (".$rsCountHijo->fields["DEPE_CODI"].") - ";
        }
        else
            $menu_depeHijo .= '<li><a href="javascript:;" onclick="datosArea('.$rsDepeHijo->fields["DEPE_CODI"].');">';
        $menu_depeHijo .= $rsDepeHijo->fields["DEPE_NOMB"];
        $menu_depeHijo .= "</a>";
        if($rsCountHijo->fields["DEPE_CODI"]!='0')
        {
            $menu_depeHijo .= "<ul>";
            $menu_depeHijo .= obtenerDependencia($rsDepeHijo->fields["DEPE_CODI"], $db);
            $menu_depeHijo .= "</ul>";
        }
        $menu_depeHijo .= "</li>";
        $rsDepeHijo->MoveNext();
    }
    return $menu_depeHijo;
}
?>