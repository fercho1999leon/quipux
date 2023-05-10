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
    $ruta_raiz = "..";
    include_once "$ruta_raiz/rec_session.php";
    unset($rsDepeHijo);
    $sqlDepeHijo = "select
                        depe_codi,
                        depe_nomb,
                        depe_codi_padre
                     from
                        dependencia
                     where
                        depe_estado=1 and depe_codi_padre = ".$_GET["codDepe"].
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
        $menu_depeHijo .= '<li><a href="javascript:;" onclick="buscar_depeHijo('.$rsDepeHijo->fields["DEPE_CODI"].');">';
        $menu_depeHijo .= $rsDepeHijo->fields["DEPE_NOMB"];
        if($rsCountHijo->fields["DEPE_CODI"]!='0')
            $menu_depeHijo .= " (".$rsCountHijo->fields["DEPE_CODI"].")";
        $menu_depeHijo .= "</a>";
        if($rsCountHijo->fields["DEPE_CODI"]!='0')
        {
            $menu_depeHijo .= "<ul id='mnu_depeHijo_".$rsDepeHijo->fields["DEPE_CODI"]."' class='menu'>";
            $menu_depeHijo .= "</ul>";
        }
        //Llamada recursiva a la funcion para obtener
        //$depe_codi = $rsDepeHijo->fields["DEPE_CODI"];
        //obtenerDependencia($depe_codi, $db);
        $menu_depeHijo .= "</li>";
        $rsDepeHijo->MoveNext();
    }
    echo $menu_depeHijo;
    //echo htmlspecialchars($menu_depeHijo);
?>