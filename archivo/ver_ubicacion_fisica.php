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
$ruta_raiz = "..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

session_start();
$ruta_raiz = "..";
include_once "$ruta_raiz/rec_session.php";
include_once "obtener_datos_archivo.php";

$ubicacion = ObtenerUbicacionFisica($_GET["arch_codi"],$db);

?>

<html>
<head>
<title>Verificacion de Documento</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="<?=$ruta_raiz?>/estilos/light_slate.css" rel="stylesheet" type="text/css">
<link href="<?=$ruta_raiz?>/estilos/splitmenu.css" rel="stylesheet" type="text/css">
<link href="<?=$ruta_raiz?>/estilos/template_css.css" rel="stylesheet" type="text/css">
</head>
<body>
    <br/><br/><center>
    <table cellspace=2 cellpad=2 WIDTH=70%  class="borde_tab" id=tb_general >
	<tr><td colspan="2" align="center" class="titulos2">Ubicaci&oacute;n F&iacute;sica de Documentos</td></tr>
	<tr>
	    <td colspan="2" align="center" class="listado2">
		<center>El Documento No. <?=$radi_nume?><br/>se encuentra ubicado en <?=$ubicacion?></center>
	    </td>
	</tr>
	<!--tr>
	    <td colspan="2" align="center" class="listado2">
		<center><a href="<?=$ruta_raiz.'/bodega'.str_replace('.p7m','',$arch_path)?>" class='vinculos'>Ver Documento</a></center>
	    </td>
	</tr-->
    </table>
    </center>
    <br/>
    <center><input type='button' onClick='window.close();' name='cerrar' value="Cerrar " class="botones_largo"></center>
</body>
</html>

