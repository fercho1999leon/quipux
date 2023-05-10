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

**************************************************************************************
** Permite eliminar los respaldos ya finalizados o en ejecución                     **
**                                                                                  **
** Desarrollado por:                                                                **
**      Mauricio Haro A. - mauricioharo21@gmail.com                                 **
*************************************************************************************/

$ruta_raiz = "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post

$resp_codi = limpiar_sql($_POST["txt_resp_codi"]);


$sql = "select coalesce(fecha_fin::text,'NO') as \"fecha_fin\" from respaldo_usuario where resp_codi=$resp_codi";
$rs = $db->query($sql);

$path = "$ruta_raiz/bodega/respaldos/respaldo_$resp_codi";

if (trim($rs->fields["FECHA_FIN"]) == "NO") {
    if (is_dir($path)) exec("rm -rf $path");
    $sql = "delete from respaldo_usuario_radicado where resp_codi=$resp_codi";
    $db->query($sql);
} else {
//    unlink ("$path.zip");
    exec("rm -f $path.z*");
    exec("rm -R $path");

    $sql = "delete from respaldo_usuario_radicado where resp_codi=$resp_codi";
    $db->query($sql);
}
$sql = "update respaldo_usuario set fecha_eliminado=".$db->conn->sysTimeStamp." where resp_codi=$resp_codi";
$db->query($sql);
die ("El respaldo se elimin&oacute; correctamente.");
?>