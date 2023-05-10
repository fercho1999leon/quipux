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
$db_bodega = new ConnectionHandler("$ruta_raiz", "bodega_test");
$db_bodega->conn->SetFetchMode(ADODB_FETCH_ASSOC);

$sql = "select last_value from sec_archivo";
$rs = $db_bodega->query($sql);

$arch_codi = round(rand(0, $rs->fields["LAST_VALUE"]));

$sql = "select func_recuperar_archivo($arch_codi) as archivo";

list($useg, $seg) = explode(" ", microtime());
$tiempo_inicio = 0 + $seg + $useg;

$rs_bodega = $db_bodega->query($sql);

list($useg, $seg) = explode(" ", microtime());
$tiempo_fin = 0 + $seg + $useg;

echo ($tiempo_fin-$tiempo_inicio) . " - ". $rs_bodega->fields["ARCH_CODI"]."<br>";

$sql = "insert into tmp_tiempo_read (arch_codi, tiempo) values ($arch_codi, ".($tiempo_fin-$tiempo_inicio).")";
$db_bodega->query($sql);

?>