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

$ruta_raiz = "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
if (isset ($replicacion) && $replicacion && $config_db_replica_busqueda_paginador!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_busqueda_paginador);

if($orden_cambio==1) {
    if(strtolower($orderTipo)=="desc")
	$orderTipo="asc";
    else
        $orderTipo="desc";
}
if (!$orderTipo) $orderTipo="desc";

$nestloop = "on";
switch ($txt_tipo_busqueda) {
    case "tramites":
        include "busqueda_tramites_query.php";
        break;
    case "adscritas";
        include "busqueda_adscritas_query.php";
        break;
    default:
        include "busqueda_query.php";
        break;
}

//    echo str_replace("<", "&lt;", $isql)."<br>";
//     echo $nestloop;
//    if ($nestloop=="off") $db->query("set enable_nestloop = $nestloop");
    if ($txt_reporte!=1){
        $pager = new ADODB_Pager($db,$isql,'adodb', true,$orderNo,$orderTipo,true);
        $pager->checkAll = false;
        $pager->checkTitulo = true;
        $pager->toRefLinks = $linkPagina;
        $pager->toRefVars = $encabezado;
        $pager->descCarpetasGen=$descCarpetasGen;
        $pager->descCarpetasPer=$descCarpetasPer;
        $db->conn->pageExecuteCountRows=false;
        $pager->Render($rows_per_page=30,$linkPagina,$checkbox=chkAnulados);
    } else {
        $rs_paginador=$db->conn->query($isql2);
        include "busqueda_generar_paginador.php";
?>
    <table width="60%" border="0">
        <tr>
            <td width="33%" align="center">
                <input type="button" name="btn_accion" class="botones_largo" value="Guardar como XLS" onclick="reportes_generar_guardar_como('XLS')">
            </td>
            <td width="33%" align="center">
                <input type="button" name="btn_accion" class="botones_largo" value="Guardar como PDF" onclick="reportes_generar_guardar_como('PDF')">
            </td>
        </tr>
    </table>
<?php
    }
    //if ($nestloop=="off") $db->query("set enable_nestloop = on");
?>
<input type="hidden" name="hid_flag_activar_boton_buscar" id="hid_flag_activar_boton_buscar" value="1">