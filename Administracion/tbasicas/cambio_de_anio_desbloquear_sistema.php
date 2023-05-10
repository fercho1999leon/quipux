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

$ruta_raiz = "../..";
session_start();
include_once "$ruta_raiz/rec_session.php";
if($_SESSION["usua_codi"]!=0) die ("Usted no tiene los permisos suficientes para acceder a esta p&aacute;gina.");

function grabar_log ($sentencia, $tabla, $flag) {
    global $db;
    $flag_log = 1;
    if (!$flag) $flag_log = 0;
    $fecha = $db->conn->sysTimeStamp;
    $sentencia = $db->conn->qstr($sentencia);
    $tabla = $db->conn->qstr($tabla);
    $usr = $_SESSION["usua_codi"];
    $sql = "insert into log (fecha, usua_codi, tabla, sentencia, tipo) values ($fecha,$usr,$tabla,$sentencia,$flag_log)";
    $db->query($sql);
    return;
}

$fecha = $db->conn->sysTimeStamp;
$sql = "update bloqueo_sistema set fecha_fin=$fecha, estado=0 where bloq_codi=1";
$flag = $db->query($sql);
grabar_log ($sql, "BLOQUEO_SISTEMA", $flag);
if (!$flag) die ("Error - Al desbloquear el sistema<br>");

echo "OK";
?>