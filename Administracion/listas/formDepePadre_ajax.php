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
    $ruta_raiz = "../..";
    include_once "$ruta_raiz/rec_session.php";

    $sqlDepePadre = "select
                        depe_codi,
                        depe_nomb,
                        depe_codi_padre
                     from
                        dependencia
                     where
                        depe_estado=1 and inst_codi = ".$_GET["codInst"].
                        " and depe_codi = coalesce(depe_codi_padre,depe_codi) order by depe_nomb";
    $rsDepePadre=$db->conn->query($sqlDepePadre);
   // $menu_depePadre = "<ul>";
    while(!$rsDepePadre->EOF)
    {
        //$menu_depePadre .= "</a><div name='mnu_depeHijo_".$rsDepePadre->fields["DEPE_CODI"]."' id='mnu_depeHijo_".$rsDepePadre->fields["DEPE_CODI"]."'  class='menu'>";
        $sqlDepeHijo = "select
                            count(depe_codi) as depe_codi
                        from
                            dependencia
                        where
                            depe_estado=1 and depe_codi_padre = ".$rsDepePadre->fields["DEPE_CODI"].
                            " and depe_codi <> depe_codi_padre";
        $rsDepeHijo=$db->conn->query($sqlDepeHijo);
        $menu_depePadre .= '<li><a href="javascript:;" onclick="buscar_depeHijo('.$rsDepePadre->fields["DEPE_CODI"].');">';
        $menu_depePadre .= $rsDepePadre->fields["DEPE_NOMB"];
        if($rsDepeHijo->fields["DEPE_CODI"]!='0')
                $menu_depePadre .= " (".$rsDepeHijo->fields["DEPE_CODI"].")";
        $menu_depePadre .= "</a>";
        if($rsDepeHijo->fields["DEPE_CODI"]!='0')
        {
            $menu_depePadre .= "<ul id='mnu_depeHijo_".$rsDepePadre->fields["DEPE_CODI"]."' class='menu'>";
            $menu_depePadre .= "</ul>";
        }
        $menu_depePadre .= "</li>";
        //Llamada recursiva a la funcion para obtener
        //$depe_codi = $rsDepePadre->fields["DEPE_CODI"];
        //obtenerDependencia($depe_codi, $db);
        $rsDepePadre->MoveNext();
    }
   // $menu_depePadre .= "</ul>";
    echo $menu_depePadre;
    //echo htmlspecialchars($menu_depePadre);
?>

