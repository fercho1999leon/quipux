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

$inst_origen  = 0 + limpiar_numero($_POST["txt_inst_origen"]);
$inst_destino = 0 + limpiar_numero($_POST["txt_inst_destino"]);

if ($inst_origen==0 or $inst_destino==0 or $inst_origen==$inst_destino)
    die ("Error-Por favor verifique las instituciones seleccionadas");

$db->conn->BeginTrans();
unset ($record);
$record = array();

//verifico si quedan áreas en la institución origen para volverla a activar
$sql = "select count(1) as num from dependencia where inst_codi=$inst_origen";
$rs = $db->query($sql);
if ((0+$rs->fields["NUM"])>0) {
    $record["inst_codi"]    = $inst_origen;
    $record["inst_estado"]  = "1";
    $ok = $db->conn->Replace("institucion", $record, "inst_codi", false,false,true,false);
    if ($ok != 1) {
        $db->conn->RollbackTrans();
        die ("Error-No se pudo activar la instituci&oacute;n origen");
    }
}

//Activo la institución destino
$record["inst_codi"]    = $inst_destino;
$record["inst_estado"]  = "1";
$ok = $db->conn->Replace("institucion", $record, "inst_codi", false,false,true,false);
if ($ok != 1) {
    $db->conn->RollbackTrans();
    die ("Error-No se pudo activar la instituci&oacute;n destino");
}

$db->conn->CommitTrans();

die ("Finalizado-Se habilit&oacute; el acceso a los usuarios de las instituciones afectadas.");

?>