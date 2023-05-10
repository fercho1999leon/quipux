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
//se incluyo por register_globals
$archivo = $_GET['archivo'];
if (isset ($_GET['nombre_archivo']))
    $nombre_archivo = $_GET['nombre_archivo'];
else
    $nombre_archivo = "";

$ruta_raiz = ".";

include_once "$ruta_raiz/include/tx/Firma_Digital.php";
$firma = verificaFirma("$ruta_raiz/bodega$archivo",$ruta_raiz);
if (!$nombre_archivo) {
    $tmp = explode("/",$archivo);
    $nombre_archivo = $tmp[count($tmp)-1];
    if (isset($textrad)){
	$nombre_archivo = $textrad . substr($nombre_archivo,strpos($nombre_archivo,"."));
    }
}
$path_descarga = "$ruta_raiz/archivo_descargar.php?path_arch=$archivo&nomb_arch=$nombre_archivo";

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

?>
  <body>
    <br/>
    <center>
<?
    echo "<font color='red' face='Arial' size='3'>".$firma["mensaje"]."</font><br/><br/>";

    if ($firma["flag"]==1) { 
        echo str_replace("<table>","<table border='2' cellspace='2' cellpad='2' WIDTH='100%'  class='t_bordeGris'>",$firma["datos_firma"]);
        echo "<script>window.open('" . str_replace(".p7m","",$path_descarga) . "','','');</script>";
    ?>
        <br><br>
        <a onClick="javascript:window.open('<?=$path_descarga?>','_self','');" href="javascript:;" class='vinculos'>Descargar Archivo Firmado</a>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a onClick="javascript:window.open('<?=str_replace('.p7m','',$path_descarga)?>','_self','');" href="javascript:;" class='vinculos'>Ver Documento</a>
<? } else { ?>
        <a onClick="javascript:window.open('<?=$path_descarga?>','_self','');"  href="javascript:;" class='vinculos'>Descargar Archivo Firmado</a>
        <br><br>
<? } ?>
      <br/><br/><br/>
      <input type='button' onClick='window.close();' name='cerrar' value="CERRAR VENTANA" class="botones_largo">
    </center>
  </body>
</html>




