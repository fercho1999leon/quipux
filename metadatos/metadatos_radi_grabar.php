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

**************************************************************************************
** Graba las solicitudes de respaldos de la documentacion de los usuarios           **
**                                                                                  **
** Desarrollado por:                                                                **
**      Lorena Torres J.                                                            **
*************************************************************************************/

$ruta_raiz = "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/include/tx/Historico.php";


//Se guarda datos de radicado y metadato
$hist = new Historico($db);
$record = array();
unset($record);

var_dump($_POST);

//$radicados = $_POST['checkValue'];
$txtMetRadiCodigo = $_POST['txtMetRadiCodi'];
$radi_nume_radi = $_POST['txtRadiCodi'];
$txtMetCodigo = $_POST['txtMetCodigo'];
$txtListaMetadatos = $_POST['txtListaMetadatos'];
$txtTexto = $_POST['txtTexto'];
$txtListaCodMetadatos = $_POST['txtListaCodMetadatos'];
$txtMetadatosTexto =  $_POST['txtMetadatosTexto'];
$usua_codi = $_SESSION["usua_codi"];
$depe_codi = $_SESSION["depe_codi"];
$txtAccion = $_POST['txtAccion'];

if($txtAccion == 1){ //Guardar
    if($txtMetRadiCodigo!="")
        $record["MET_RADI_CODI"] = $txtMetRadiCodigo;
    $record["RADI_NUME_RADI"] = $radi_nume_radi;  
    $record["MET_CODI"] = $txtMetCodigo;    
    $record["DEPE_CODI"] = $depe_codi;
    $record["USUA_CODI"] = $usua_codi;    
    $record["TEXTO"] = "'".$txtTexto."'";
    $record["METADATO"] = "'".$txtListaMetadatos."'";
    $record["METADATO_TEXTO"] = "'".$txtMetadatosTexto."'";
    $record["METADATO_CODI"] = "'".$txtListaCodMetadatos."'";
    $record["FECHA"] = $db->conn->sysTimeStamp;
}

if($txtAccion == 2){ //Eliminar
    $record["MET_RADI_CODI"] = $txtMetRadiCodigo;   
    $record["ESTADO"] = 0;
    $record["FECHA"] = $db->conn->sysTimeStamp;
}

$insertSQL=$db->conn->Replace("METADATOS_RADICADO", $record, "MET_RADI_CODI", false,false,true,false);    

//Se guarda histórico
$hist->insertarHistorico($radi_nume_radi, $usua_codi, $usua_codi, $txtMetadatosTexto, 84);

echo "<script>opener.regresar();window.close();</script>";

?>