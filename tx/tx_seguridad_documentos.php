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
$ruta_raiz = "..";
include_once "$ruta_raiz/rec_session.php";
require_once "$ruta_raiz/funciones.php";
include_once "$ruta_raiz/seguridad_documentos.php";
include_once "$ruta_raiz/obtenerdatos.php";

$radicados = limpiar_sql($_POST["txt_radicados"]);
$codTx = limpiar_sql($_POST["codTx"]);
$tareaCodi = limpiar_sql($_POST['tarea_codi']);
$lista_radicados = "0";
$mensaje_error = "";
$sql = "select radi_nume_radi from radicado where radi_nume_radi in ($radicados)"; //,20110000110000000060
$rs = $db->conn->Execute($sql);
if ($rs && !$rs->EOF) {
    while (!$rs->EOF) {
        $ok = validar_transacciones($codTx, $rs->fields["RADI_NUME_RADI"], $db);
        if ($ok == "")
            $lista_radicados .= ",".$rs->fields["RADI_NUME_RADI"];
        else
            $mensaje_error .= $ok;
        $rs->MoveNext();
    }
} else {
    $mensaje_error = "No se encontraron documentos para realizar esta acci&oacute;n";
}

//Fecha maxima que puede tener la tarea
$fechaMaximaTarea = obtenerFechaMaximaTarea($db, $radicados, $_SESSION["usua_codi"]);
//if ($fechaMaximaTarea==date('Y-m-d'))
//$fechaMaximaTarea = obtenerFechaMaximaTareaNueva($db, $radicados, $_SESSION["usua_codi"]);
//if ($fechaMaximaTarea=='')
//    $fechaMaximaTarea = date('Y-m-d');
if ($mensaje_error != "" )
    echo ("<table class='borde_tab' width='100%' celspacing='5'><tr class='titulosError'><td align='center'>$mensaje_error</td></tr></table>");

echo "<input type='hidden' name='txt_tx_radi_nume' id='txt_tx_radi_nume' value='$lista_radicados'>";
echo "<input type='hidden' name='txt_tx_fecha_maxima_tarea' id='txt_tx_fecha_maxima_tarea' value='$fechaMaximaTarea'>";

?>