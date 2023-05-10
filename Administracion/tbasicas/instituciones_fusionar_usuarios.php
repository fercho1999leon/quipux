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
$depe_origen  = 0 + limpiar_numero($_POST["txt_depe_origen"]);

if ($inst_origen==0 or $inst_destino==0 or $inst_origen==$inst_destino)
    die ("Error-Por favor verifique las instituciones seleccionadas");

if ($depe_origen == 0) {
    $sql = "select usua_codi from usuarios where inst_codi=$inst_origen limit 1000";
} else {
    $lista_areas = "$depe_origen";

    $flag_detener = false;
    $i=0;
    while (!$flag_detener && $i<1000) {
        ++$i;
        $sql = "select depe_codi from dependencia where depe_codi_padre in ($lista_areas) and depe_codi not in ($lista_areas)";
        $rs = $db->query($sql);
        if (!$rs or $rs->EOF) {
            $flag_detener = true;
        } else {
            while(!$rs->EOF) {
                $lista_areas .= ",".$rs->fields["DEPE_CODI"];
                $rs->MoveNext();
            }
        }
    }
    $sql = "select usua_codi from usuarios where depe_codi in ($lista_areas) and inst_codi=$inst_origen limit 1000";
}


$db->conn->BeginTrans();

$record = array();

// Consulto las areas creadas en la institucion origen
$rs = $db->query($sql);

$contador = 0;
while($rs && !$rs->EOF) {
    $record["usua_codi"] = trim($rs->fields["USUA_CODI"]);
    $record["inst_codi"] = $inst_destino;
    $ok = $db->conn->Replace("usuarios", $record, "usua_codi", false,false,true,false);
    if ($ok != 1) {
        $db->conn->RollbackTrans();
        die ("Error-No se pudo mover el usuario ".trim($rs->fields["USUA_CODI"]));
    }
    ++$contador;
    $rs->MoveNext();
}

$db->conn->CommitTrans();

if ($contador==0)
    die ("Finalizado");
else
    die ("$contador");

?>