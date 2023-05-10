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
$ruta_raiz = ".";
include_once "$ruta_raiz/rec_session.php";


/**
* Permite descargar los archivos en formato .pdf
**/

$path_arch = "bodega".$_GET["path_arch"];
if (strpos($path_arch,"../")===false) {
    if (is_file($path_arch)) {
	if (trim($_GET["nomb_arch"])!="") $nomb_arch = $_GET["nomb_arch"];
	    else $nomb_arch = basename($path_arch);

	/*header( "Content-Type: application/octet-stream"); 
	header( "Content-Length: ".filesize($path_arch)); 
	header( "Content-Disposition: attachment; filename=".$nomb_arch.""); 
	header( "Content-Type: application/download");*/

	//$mime = mime_content_type($path_arch);
	$mime = get_mime_tipe($nomb_arch);

	header( "Content-Disposition: attachment; filename=".$nomb_arch."");
	header( "Content-Length: ".filesize($path_arch));
	header("Content-Type: $mime");
	header("Content-Transfer-Encoding: binary");

	readfile($path_arch);
    } else
	die ("El archivo no fue encontrado.");
} else
    die ("No tiene permiso para acceder a archivos en este directorio.");

?>
