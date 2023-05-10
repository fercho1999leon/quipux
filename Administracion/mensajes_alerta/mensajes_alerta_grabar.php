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
/*****************************************************************************************
** Administración de mensajes para el sistema                                           **
*****************************************************************************************/
$ruta_raiz = "../..";
session_start();
if ($_SESSION["admin_institucion"] != 1) {
    die("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
}
include_once "$ruta_raiz/rec_session.php";
require_once "$ruta_raiz/funciones.php";

$record = array();
$bloq_codi = 0+$_POST["txt_bloq_codi"];
if ($bloq_codi == 0) $bloq_codi = $db->nextId("sec_bloqueo_sistema");
$record["bloq_codi"] = $bloq_codi;
$record["fecha_inicio"] = $db->conn->qstr(limpiar_sql($_POST["txt_fecha_inicio"]));
$record["fecha_fin"] = $db->conn->qstr(limpiar_sql($_POST["txt_fecha_fin"]));
$record["estado"] = 0 + $_POST["txt_estado"];
$record["descripcion"] = $db->conn->qstr(limpiar_sql(base64_decode(base64_decode($_POST["txt_descripcion"]))));
$record["mensaje_usuario"] = $db->conn->qstr(limpiar_sql(base64_decode(base64_decode($_POST["txt_mensaje"])),0));
$record["usua_acceso"] = $db->conn->qstr(limpiar_sql($_POST["txt_usua_acceso"]));
$record["tipo_mensaje"] = 0 + $_POST["txt_tipo_mensaje"];

$ok = $db->conn->Replace("BLOQUEO_SISTEMA", $record, "bloq_codi", false,false, true, false);
if (!$ok)
    echo "Existieron errores al momento de guardar los cambios.";
else
    echo "Los cambios se guardaron exitosamente.";
?>
<input type="hidden" name="txt_bloq_codi" id="txt_bloq_codi" value="<?=$bloq_codi?>">