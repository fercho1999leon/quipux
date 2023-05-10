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
include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head()."<body>";

$txt_tipo = limpiar_sql($_POST["txt_tipo"]);


switch ($txt_tipo) {
    case "A":
        $where_areas = "";
        if ($_SESSION["usua_perm_estadistica"] != 1) $where_areas = " and depe_codi=".$_SESSION["depe_codi"];
        $sql = "select depe_nomb, depe_codi from dependencia where depe_estado=1 $where_areas and inst_codi=".$_SESSION["inst_codi"]." order by 1 asc";
        $rs = $db->conn->Execute($sql);
        $menu  = $rs->GetMenu2("txt_depe_codi", "0", "0:&lt;&lt; Todas las &aacute;reas &gt;&gt;", false,""," id='txt_depe_codi' class='select' onChange=\"cargar_combos('A')\"" );
        break;

    case "U":
        $txt_depe_codi = limpiar_numero($_POST["txt_depe_codi"]);
        $where_areas = "";
        if ($txt_depe_codi != "0") $where_areas = " and depe_codi=$txt_depe_codi";
        if ($_SESSION["usua_perm_estadistica"] != 1) $where_areas = " and depe_codi=".$_SESSION["depe_codi"];
        $sql = "select coalesce(usua_apellido,'')||' '||coalesce(usua_nomb,'')||case when usua_esta=0 then ' (Inactivo)' else '' end as usr_nombre, usua_codi
                from usuario where usua_codi>0 $where_areas and inst_codi=".$_SESSION["inst_codi"]." order by 1 asc";
        $rs = $db->conn->Execute($sql);
        $menu  = $rs->GetMenu2("txt_usua_codi", "0", "0:&lt;&lt; Todos los usuarios &gt;&gt;", false,""," id='txt_usua_codi' class='select'" );
        //  $menu_usr  = $rs_usr->GetMenu2("usCodSelect[]", 0, false, true, 8," id='usCodSelect' class='select'" );
        break;

    default:
        die ("Error al cargar combo");
        break;
}

echo $menu;
//echo "<br>$sql";
?>

</body>
</html>