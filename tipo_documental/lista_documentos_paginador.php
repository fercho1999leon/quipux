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
  if (isset ($replicacion) && $replicacion && $config_db_replica_trd_lista_expediente!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_trd_lista_expediente);
  $codexp = limpiar_numero($_GET['trd_codi']);
  $nombre_completo = $_GET['trd_nombre_completo'];
  $fecha_inicio = limpiar_sql($_GET['txt_fecha_inicio']);
  $fecha_fin = limpiar_sql($_GET['txt_fecha_fin']);
  $txt_reporte = limpiar_numero($_GET['txt_reporte']); 

  if($orden_cambio==1) {
    if(strtolower($orderTipo)=="desc")
        $orderTipo="asc";
    else
        $orderTipo="desc";
    }

    include_once "lista_expediente_query.php";
    echo "<br>";
    echo "<table class='borde_tab' width='100%'><tr><td class='titulos2' colspan='4'>Carpeta Virtual: $nombre_completo</td></tr></table>";

    if ($txt_reporte!=1){
        $pager = new ADODB_Pager($db,$isql,'adodb', true,$orderNo,$orderTipo,true);
        $pager->checkAll = false;
        $pager->checkTitulo = true;
        $pager->toRefLinks = $linkPagina;
        $pager->toRefVars = $encabezado;
        $pager->descCarpetasGen=$descCarpetasGen;
        $pager->descCarpetasPer=$descCarpetasPer;
        $pager->Render($rows_per_page=20,$linkPagina,$checkbox=chkAnulados);
    } else {
        $rs_paginador=$db->conn->query($isql1);
        include "lista_generar_paginador.php";
        if($num_filas>0){
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
    }

    if (isset($_GET["trd_boton"]) && $_GET["trd_boton"]==0) die("");
?>
    <table width='80%' cellspacing='5'>
	<tr>
    	    <td > <center>
    		<input type='button' name='btn_cancelar' value='Regresar' class='botones' onClick='window.location="./consultar_trd.php";'>
    	    </center></td>
	</tr>
    </table>