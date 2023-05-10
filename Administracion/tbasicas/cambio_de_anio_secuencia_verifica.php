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

$anio = 0+date("Y");
$mes =  0+date("m");
if ($mes == 12) ++$anio;
$mensaje = "";


$secuencia = "sec_radi_nume_radi";
//verificamos que exista la secuencia
$rs2 = $db->query("SELECT last_value from $secuencia");
if (!$rs2) {
    $mensaje .= "Error - No existe la secuencia $secuencia <br>";
}
if ($rs2->fields["LAST_VALUE"] != 1) {
    $mensaje .= "Error - No se inicializó la secuencia $secuencia <br>";
}

// Verificamos las secuencias para los numeros temporales
$rs = $db->query("select sum(secuencia) as num from radicado_sec_temp");
if ($rs->fields["NUM"] != 0)
$mensaje .= "Error - No se pudo inicializar secuencias en tabla RADICADO_SEC_TEMP";


// Actualizamos las secuencias para el texto de los documentos
$rs = $db->query("select sum(fn_contador) as num from formato_numeracion");
if ($rs->fields["NUM"] != 0)
$mensaje .= "Error - No se pudo inicializar secuencias en tabla FORMATO_NUMERACION";


if ($mensaje == "") $mensaje = "OK";

echo $mensaje;
?>