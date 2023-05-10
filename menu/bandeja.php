
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
/*****************************************************************************************
**											**
*****************************************************************************************/

    $carpetas_grupo_1 = "1,2,8,15,16";
    if ($_SESSION["firma_digital"]==1) $carpetas_grupo_1 .= ",7";
    if ($_SESSION["usua_codi_jefe"]!=0) $carpetas_grupo_1 .= ",14";
    $sql ="select * from carpeta where carp_codi in ($carpetas_grupo_1) order by carp_orden asc";
    $rs = $db->query($sql);
    $bandeja = "";
    while($rs && !$rs->EOF) {
        $bandeja .= crear_item_bandeja($rs->fields["CARP_CODI"], $rs->fields["CARP_NOMBRE"], $rs->fields["CARP_DESCRIPCION"]);
	$rs->MoveNext();                
    }
    echo crear_grupo_bandeja("bandejas", "Bandejas", $bandeja);

    //Traemos las bandejas restantes
    $carpetas_grupo_1 .= ",14"; //Quitamos bandejas compartidas
    $sql ="select * from carpeta where carp_codi not in ($carpetas_grupo_1) order by carp_orden asc";
    $rs = $db->query($sql);
    $bandeja = "";
    while($rs && !$rs->EOF) {
        $bandeja .= crear_item_bandeja($rs->fields["CARP_CODI"], $rs->fields["CARP_NOMBRE"], $rs->fields["CARP_DESCRIPCION"]);
	$rs->MoveNext();
    }
    echo crear_grupo_bandeja("otras_bandejas", "Otras Bandejas", $bandeja);
?>
