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

$ruta_raiz = "../..";
session_start();
require_once "$ruta_raiz/rec_session.php";
if (isset ($replicacion) && $replicacion && $config_db_replica_adm_busqueda_paginador_usuarios!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_adm_busqueda_paginador_usuarios);

if($orden_cambio==1) {
    if(strtolower($orderTipo)=="desc")
	$orderTipo="asc";
    else
        $orderTipo="desc";
}
if (!$orderTipo) $orderTipo="asc";

$buscar_nom = trim(limpiar_sql($_GET['txt_buscar_nombre']));

$tipo_query = trim(limpiar_sql($_GET['tipo_query']));
//opcion
$opc = trim(limpiar_sql($_GET['ciu_ver']));

include "$ruta_raiz/include/query/administracion/queryCuerpoSolicitud_ext.php";  
//$orderNo=$orderNo-1;
//echo $sql;
$pager = new ADODB_Pager($db,$sql,'adodb', true,$orderNo,$orderTipo,true);
$pager->checkAll = false;
$pager->checkTitulo = true;
$pager->toRefLinks = $linkPagina;
$pager->toRefVars = $encabezado;
$pager->descCarpetasGen=$descCarpetasGen;
$pager->descCarpetasPer=$descCarpetasPer;
$pager->Render($rows_per_page=15,$linkPagina,$checkbox=chkAnulados);

?>

  </body>
</html>