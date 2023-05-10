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


$isql = "select a.anex_codigo as \"CHK_chk_copiar_anexos\"
        , a.anex_nombre as \"Nombre\"
        , a.anex_desc as \"Descripción\"
        , a.anex_tamano as \"Tamaño\"
        from anexos a 
        where a.anex_borrado='N' 
        and a.anex_radi_nume in (".$radicado["radi_nume_radi"].",".$radicado["radi_nume_temp"].")
        order by anex_fecha asc";

echo "<br><table width='100%' border='0' cellpadding='0' cellspacing='0'><tr><td><b>Copiar anexos pertenecientes al documento padre.</b></td></tr></table><br>";

$pager = new ADODB_Pager($db,$isql,'adodb', true,$orderNo,$orderTipo);
$pager->checkAll = false;
$pager->checkTitulo = false;
$pager->toRefLinks = $linkPagina;
$pager->toRefVars = $encabezado;
$pager->descCarpetasGen=$descCarpetasGen;
$pager->descCarpetasPer=$descCarpetasPer;
$pager->Render($rows_per_page=30,$linkPagina,$checkbox=chkAnulados);

//echo "<script> parent.leftFrame.cambiar_contador('$carpeta','$pager->num_rows'); </script>";

?>
<br>&nbsp;
