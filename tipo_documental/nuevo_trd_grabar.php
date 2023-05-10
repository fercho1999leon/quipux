<?
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
**											**
*****************************************************************************************/
$ruta_raiz = isset($ruta_raiz) ? $ruta_raiz : "..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

$ruta_raiz = "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
  include_once "obtener_datos_trd.php";

if (trim($txtOk)=="1") {
    $record = array();
    if (trim($txtCodigo)!="") {
	$mensaje = "Se ha modificado el Item ";
    	$record["TRD_CODI"] = trim($txtCodigo);
    } else {
	$mensaje = "Se ha creado el Item ";
    	$record["TRD_CODI"] = $db->nextId("sec_trd");
    	$record["TRD_FECHA_DESDE"] = $db->conn->sysTimeStamp;
    }
    if (trim($txtPadre)!="") $record["TRD_PADRE"] = trim($txtPadre);
    $record["TRD_NOMBRE"] = $db->conn->qstr(limpiar_sql($txtNombre));
    $record["TRD_ARCH_GESTION"] = $db->conn->qstr($txtArch1);
    $record["TRD_ARCH_CENTRAL"] = $db->conn->qstr($txtArch2);
    $record["TRD_NIVEL"] = $db->conn->qstr($txtNivel);
    $record["DEPE_CODI"] = $depe_actu;
    ////$record["TRD_OCUPADO"] = "1";
    if ($txtEstado==1 or $txtEstado==0)
        $record["TRD_ESTADO"] = "$txtEstado";    
    $ok = $db->conn->Replace("TRD", $record, "TRD_CODI", false,false,true,false);
    //Se comenta este codigo porque es para actualizar el campo trd_ocupado que no se usa
    /*if (trim($txtCodigo)=="" and $txtEstado==1) {
    	ActivarTRD($record["TRD_CODI"], "E", $db);
    	ActivarTRD($record["TRD_CODI"], "O", $db);
    }*/
    $mensaje .= ObtenerNombreCompletoTRD($record["TRD_CODI"],$db) . "<br/><br/>";

}
if (trim($txtOk)=="2") {
    $mensaje = "Se ha eliminado el Item " . ObtenerNombreCompletoTRD($txtCodigo,$db) . "<br/><br/>";
    ModificarEstadoTRD($txtCodigo, 2, $db);
}
if (trim($txtOk)=="3") {
    $mensaje = "Se ha activado el Item " . ObtenerNombreCompletoTRD($txtCodigo,$db) . "<br/><br/>";
    ModificarEstadoTRD($txtCodigo, 1, $db);
}
if (trim($txtOk)=="4") {
    $mensaje = "Se ha Desactivado el Item " . ObtenerNombreCompletoTRD($txtCodigo,$db) . "<br/><br/>";
    ModificarEstadoTRD($txtCodigo, 0, $db);
}
include_once "./nuevo_trd.php";
?>

