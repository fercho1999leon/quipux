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
if($_SESSION["perm_actualizar_sistema"]!=1) die("Usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
require_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/plantillas/generar_documento.php";
$doc = New GenerarDocumento($db);

$dbr = new ConnectionHandler("$ruta_raiz","reportes");

$mensaje_error = "";
$mensaje = "";
$mensaje_fin = "si";
$hora_inicio = date("H:i:s");
$fecha_inicio = $db->conn->sysTimeStamp;

$contador = 0;
$sentencia = "0";

$txt_anio = 0 + $_POST["txt_anio"];
$txt_num_registros = 0 + $_POST["txt_num_registros"];
$sql = "select radi_nume_radi from radicado where radi_nume_radi::text like '$txt_anio%0' and radi_path is null and esta_codi in (0,6) limit $txt_num_registros offset 0";
$rs = $db->conn->query($sql);

if (!$rs) {
    $mensaje = 0;
    $mensaje_error = "No se pudo realizar la busqueda de documentos.";
} elseif ($rs->EOF) { //Si no se encontraron más documentos padres pero hay documentos hijos (firmados electronicamente para ciudadanos y funcionarios)
    $sql = "select radi_nume_radi from radicado where radi_nume_temp::text like '$txt_anio%0' and radi_path is null and esta_codi in (0,2,5,6) limit $txt_num_registros offset 0";
    $rs = $db->conn->query($sql);
    if (!$rs or $rs->EOF) $mensaje = 0;
}

if ($rs and !$rs->EOF) {
    while(!$rs->EOF) {
        $pdf = $doc->GenerarPDF($rs->fields["RADI_NUME_RADI"],"no");
        ++ $contador;
        $sentencia .= ",".$rs->fields["RADI_NUME_RADI"];
        $rs->MoveNext();
    }
    $mensaje = $contador;
    $mensaje_fin = "no";

}

$hora_fin = date("H:i:s");

$sql_log = "insert into log_actualizar_sistema (usua_codi,fecha_inicio,fecha_fin, sentencia, mensaje)
            values (".$_SESSION["usua_codi"].", $fecha_inicio, now()
                    , ".$db->conn->qstr($sentencia).", ".$db->conn->qstr("Se generaron $contador PDFs. $mensaje_error").")";
$db->conn->query($sql_log);

echo "<span id='txt_actu_hora_inicio'>$hora_inicio</span>";
echo "<span id='txt_actu_hora_fin'>$hora_fin</span>";
echo "<span id='txt_actu_mensaje'>$mensaje</span>";
echo "<span id='txt_actu_mensaje_error'>$mensaje_error</span>";
echo "<span id='txt_actu_mensaje_fin'>$mensaje_fin</span>";


//select count(1) as total_registros
//    , count(case when radi_nume_radi::text like '%0' and esta_codi in (0,6) then 1 else null end) as total_pdf
//from radicado
//where radi_nume_temp::text like '2008%0' and radi_path is null and esta_codi in (0,6,5,2)
//
//
//update radicado set radi_path=null where radi_nume_radi in (
//select radi_nume_radi
//from radicado
//where radi_nume_radi::text like '%0' and radi_path not like '%docs%' and radi_fech_firma is null
//)

?>