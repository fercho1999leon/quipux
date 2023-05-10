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
include_once "metadatos_funciones.php";

if (trim($txtOk)=="1") {
    $record = array();
    if (trim($txtCodigo)!="") {
	$mensaje = "Se ha modificado el Item ";
    	$record["MET_CODI"] = trim($txtCodigo);
    } else {
	$mensaje = "Se ha creado el Item ";
        $record["MET_CODI"] = $db->nextId("sec_metadatos");
        $record["MET_ESTADO"] = 1;
    }
    if (trim($txtPadre)!="") 
        $record["MET_PADRE"] = trim($txtPadre);
    $record["INST_CODI"] = $_SESSION["inst_codi"];
    if($depe_actu != 0)
        $record["DEPE_CODI"] = $depe_actu;
    $record["MET_NOMBRE"] = $db->conn->qstr(limpiar_sql($txtNombre));   
    $record["MET_NIVEL"] = $db->conn->qstr($txtNivel);    
   
    $ok = $db->conn->Replace("METADATOS", $record, "MET_CODI", false,false,true,false);  
    $mensaje .= ObtenerNombreCompletoMET($record["MET_CODI"],$db) . "<br/><br/>";

}
if (trim($txtOk)=="2") {
    $mensaje = "Se ha eliminado el Item " . ObtenerNombreCompletoMet($txtCodigo,$db) . "<br/><br/>";
    ModificarEstadoMet($txtCodigo, 2, $db);
}
if (trim($txtOk)=="3") {
    $mensaje = "Se ha activado el Item " . ObtenerNombreCompletoMet($txtCodigo,$db) . "<br/><br/>";
    ModificarEstadoMet($txtCodigo, 1, $db);
    ModificarEstadoMetAsc($txtCodigo, $txtPadre, 1, $db);
}
if (trim($txtOk)=="4") {
    $mensaje = "Se ha Desactivado el Item " . ObtenerNombreCompletoMet($txtCodigo,$db) . "<br/><br/>";
    ModificarEstadoMet($txtCodigo, 0, $db);
}
include_once "./metadatos.php";
?>