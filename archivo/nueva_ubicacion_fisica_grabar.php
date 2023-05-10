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
$ruta_raiz = "..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

$ruta_raiz = "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
include_once "obtener_datos_archivo.php";

  $sql = "select coalesce(dep_central,depe_codi) as archivo from dependencia where depe_codi=".$_SESSION['depe_codi']."";
  $rs=$db->conn->query($sql);
  $depe_archivo = $rs->fields["ARCHIVO"];

if (trim($txtOk)=="1") {
    $record = array();
    if (trim($txtCodigo)!="") {
	$mensaje = "Se ha modificado el Item ";
    	$record["ARCH_CODI"] = trim($txtCodigo);
    } else {
	$mensaje = "Se ha creado el Item ";
    	$record["ARCH_CODI"] = $db->nextId("sec_archivo");
    }
    if (trim($txtPadre)!="") $record["ARCH_PADRE"] = trim($txtPadre);
    $record["ARCH_NOMBRE"] = $db->conn->qstr($txtNombre);
    $record["ARCH_SIGLA"] = $db->conn->qstr($txtSigla);
    $record["DEPE_CODI"] = $depe_archivo;
    //$record["ARCH_OCUPADO"] = "1";
    if ($txtEstado==1 or $txtEstado==0)
        $record["ARCH_ESTADO"] = "$txtEstado";
    $ok = $db->conn->Replace("ARCHIVO", $record, "ARCH_CODI", false,false,true,false);
    $mensaje .= ObtenerUbicacionFisica($record["ARCH_CODI"],$db) . "<br/><br/>";
    if (trim($txtCodigo)=="" and $txtEstado==1) {
    	ActivarArchivo($record["ARCH_CODI"], "E", $db);
    	ActivarArchivo($record["ARCH_CODI"], "O", $db);
    }
}
if (trim($txtOk)=="2") {
    $mensaje = "Se ha eliminado el Item " . ObtenerUbicacionFisica($txtCodigo,$db) . "<br/><br/>";
    DesactivarArchivo($txtCodigo, "E", $db);
    DesactivarArchivo($txtCodigo, "O", $db);
    $sql = "delete from archivo where arch_codi=$txtCodigo";
    $db->conn->Execute($sql);
}

if (trim($txtOk)=="3") {
    $mensaje = "Se ha activado el Item " . ObtenerUbicacionFisica($txtCodigo,$db) . "<br/><br/>";
    ActivarArchivo($txtCodigo, "E", $db);
}
if (trim($txtOk)=="4") {
    $mensaje = "Se ha Desactivado el Item " . ObtenerUbicacionFisica($txtCodigo,$db) . "<br/><br/>";
    DesactivarArchivo($txtCodigo, "E", $db);
}

include_once "./nueva_ubicacion_fisica.php";
?>

