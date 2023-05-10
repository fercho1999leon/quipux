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
$ruta_raiz = isset($ruta_raiz) ? $ruta_raiz : "..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());


$accion="$ruta_raiz/plantillas/CodigoBarras.php?krd=$krd&nuevo=si&verrad=$verrad";
?>

<HTML>
<head>
<title>Impresi&oacute;n de Comprobantes</title>
<link rel="stylesheet" href="<?=$ruta_raiz?>/estilos/orfeo.css">
</head>
<BODY>

<FORM ACTION="<?=$accion?>" name='formulario' id='formulario' method="POST">
<center>
<br><br>
<table width="90%" border="0" align="center" cellpadding="4" cellspacing="5" class="borde_tab"><tr><td class="titulos2" width="100%">
<center>IMPRIMIR COMPROBANTES DEL REGISTRO NO. <?=$textrad?></center>
</td></tr></table>
<br>
<input type='hidden' name='tipo_comp' id='tipo_comp' value='' >
<input type='button' value='Imprimir C&oacute;digo de Barras' name=asocImgRad class='botones_largo' onclick='document.formulario.tipo_comp.value="1"; document.formulario.submit();'>
<input type='button' value='Imprimir Comprobante' name=asocImgRad class='botones_largo' onclick='document.formulario.tipo_comp.value="2"; document.formulario.submit();'>
<input type='button' value='Imprimir Comprobante en Ticket' name=asocImgRad class='botones_largo' onclick='document.formulario.tipo_comp.value="3"; document.formulario.submit();'>
<br><br>
<input type='button' value='Cerrar' name=asocImgRad class='botones_largo' onclick='window.close();'>
</center>
</FORM>
</BODY>
</HTML>

