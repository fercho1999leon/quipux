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
require_once("$ruta_raiz/funciones.php"); 
p_register_globals(array());

session_start();

/*if (!$_SESSION['permiso_archivar_documentos']) {
    die('No cuenta con los permisos necesarios para usar esta parte del sistema. Si cree que es un error, comuníquese con su administrador del sistema para que le habilite esta funcionalidad.');
}
*/
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/include/tx/Historico.php";

    $hist = new Historico($db);
    $record = array();
    $where = array();
    
    $txtRadicado = limpiar_numero($_POST['txtRadicado']);
    $record["RADI_NUME_RADI"] = $txtRadicado;

    $txtCodigo = limpiar_numero($_POST['txtCodigo']);
    $record["TRD_CODI"] = $txtCodigo;
    
    $record["FECHA"] = $db->conn->sysTimeStamp;
    $record["USUA_CODI"] = $_SESSION['usua_codi'];
    $record["DEPE_CODI"] = $_SESSION['depe_codi'];

    $where[]="RADI_NUME_RADI";
    $where[]="DEPE_CODI";
    
    $ok = $db->conn->Replace("TRD_RADICADO", $record, $where, false,false,true,false);
    $hist->insertarHistorico($txtRadicado, $_SESSION['usua_codi'], $_SESSION['usua_codi'], "Incluir documento en $descTRD", 32, $txtCodigo);

echo "<script>opener.regresar();window.close();</script>";
?>

