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

@ob_start();
$height="50";
$bgcolor="#FFFFFF";
$color="#333366";
$type="png";
$encode = "CODE128";
$fechah = date("YmdHms");
$height = "50";
$scale = "1.5";
$bdata = $nurad;
$file = "$ruta_raiz/bodega/tmp/$fileDat";
include("barcode.php");
echo $file;
?>
<!--
<table border=1 background="<?="$file".".png"?>" width=360 height=90 class="borde_tab">
<TR height=50>
	<TD>
	</TD>
</TR>
<tr><td class=listado1>Numero de Radicado <?=$nurad?>
</td></tr>
</table>
-->
<img src='<?="$file".".png"?>'>
