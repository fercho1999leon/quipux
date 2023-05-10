<?php
/**  Programa para el manejo de gestion documental, oficios, memorandos, circulares, acuerdos
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
/*****************************************************************************
**  Muestra las alertas programadas por el administrador del sistema        **
**  Programar para que se ejecute cada 5 minutos                            **
******************************************************************************/

$ruta_raiz = "../..";
include_once "$ruta_raiz/config.php";
include_once "$ruta_raiz/include/db/ConnectionHandler.php";

error_reporting(7);
$db = new ConnectionHandler("$ruta_raiz");
$db->conn->SetFetchMode(ADODB_FETCH_ASSOC);

$db_bodega = new ConnectionHandler("$ruta_raiz", "bodega");
$db_bodega->conn->SetFetchMode(ADODB_FETCH_ASSOC);

$sql = "select arch_codi, nombre from archivo where arch_codi>(select arch_codi from tmp_revertir) order by arch_codi asc limit 1";
$rs = $db_bodega->query($sql);

if (!$rs or $rs->EOF) {
    sleep(15);
    die ("Error - No se encuentran m&aacute;s archivos");
}

$archivo_codigo   = $rs->fields["ARCH_CODI"];
$archivo_nombre = $rs->fields["NOMBRE"];

//Validamos la extensión del archivo
$tmp = explode(".", $archivo_nombre);
$flag_firma = false;
$i = 1;
$archivo_extension = "";
do {
    $archivo_extension_tmp = strtoupper(trim($tmp[count($tmp)-$i]));
    $archivo_extension = ".".trim($tmp[count($tmp)-$i]) . $archivo_extension;
    if ($archivo_extension_tmp == "P7M") $flag_firma = true;
    ++$i;
} while ($archivo_extension_tmp=="P7M");


$archivo_path = "$ruta_raiz/bodega/2013/reversa/$archivo_codigo$archivo_extension";

$rs_arch = $db_bodega->query("select func_recuperar_archivo($archivo_codigo) as archivo");
if (!$rs_arch or $rs_arch->EOF or $rs_arch->fields["ARCHIVO"]=="") {
    sleep(15);
    die ("Error - No se pudo recuperar el archivo $archivo_codigo");
}

$ok = file_put_contents($archivo_path, base64_decode($rs_arch->fields["ARCHIVO"]));
if (!$ok) {
    sleep(15);
    die ("Error - No se pudo grabar el archivo $archivo_codigo en la direcci&oacute;n &quot;$archivo_path&quot;");
}

$sql = "update tmp_revertir set arch_codi=$archivo_codigo";
$rs = $db_bodega->query($sql);

$sql = "insert into tmp_revertir_bodega (arch_codi, arch_path) values ($archivo_codigo,E'/2013/reversa/$archivo_codigo$archivo_extension')";
$rs = $db->query($sql);

?>
<table border="0" width="100%" cellpadding="0" cellspacing="2">
    <tr>
        <td width="30%" class="titulos2">Codigo Archivo:</td>
        <td width="70%" class="listado2"><?=$archivo_codigo?></td>
    </tr>
    <tr>
        <td class="titulos2">Nombre:</td>
        <td class="listado2"><?=$archivo_nombre?></td>
    </tr>
    <tr>
        <td class="titulos2">Path:</td>
        <td class="listado2"><?=$archivo_path?></td>
    </tr>
    <tr>
        <td class="titulos2">Fecha:</td>
        <td class="listado2"><?=date("Y-m-d H:i:s")?></td>
    </tr>
</table>
