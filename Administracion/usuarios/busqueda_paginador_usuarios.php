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
require_once "$ruta_raiz/obtenerdatos.php";
if (isset ($replicacion) && $replicacion && $config_db_replica_adm_busqueda_paginador_usuarios!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_adm_busqueda_paginador_usuarios);

$nombre = trim(limpiar_sql($_GET['txt_nombre']));
$dependencia = 0+$_GET['txt_dependencia'];
$permiso = 0+$_GET['txt_permiso'];
$estado = 0+$_GET['txt_estado'];
$perfil = 0+$_GET['cmb_usr_perfil'];
$txt_reporte = 0+$_GET['txt_reporte'];

$depe_codi_admin = obtenerAreasAdmin($_SESSION["usua_codi"],$_SESSION["inst_codi"],$_SESSION["usua_admin_sistema"],$db);

//$puesto_cabecera = trim(limpiar_sql($_GET['txt_puesto_cabecera']));
//$correo = trim(limpiar_sql($_GET['txt_correo']));
if($orden_cambio==1) {
    if(strtolower($orderTipo)=="desc")
	$orderTipo="asc";
    else
        $orderTipo="desc";
}
if (!$orderTipo) $orderTipo="asc";

include "$ruta_raiz/include/query/administracion/queryCuerpoUsuario.php";
      
//    echo str_replace("<", "&lt;", $isql)."<br>";
if ($txt_reporte!=1){
    $pager = new ADODB_Pager($db,$sql,'adodb', true,$orderNo,$orderTipo,true);
    $pager->checkAll = false;
    $pager->checkTitulo = true;
    $pager->toRefLinks = $linkPagina;
    $pager->toRefVars = $encabezado;
    $pager->descCarpetasGen=$descCarpetasGen;
    $pager->descCarpetasPer=$descCarpetasPer;
    $pager->Render($rows_per_page=20,$linkPagina,$checkbox=chkAnulados);
}else{
     $rs_paginador=$db->conn->query($sql);
     include "busqueda_reporte_manual_usuarios_exportar.php";


?><table width="60%" border="0">
        <tr>
            <td width="33%" align="center">
                <input type="button" name="btn_accion" class="botones_largo" value="Guardar como XLS" onclick="reportes_generar_guardar_como('XLS')">
            </td>
            <td width="33%" align="center">
                <input type="button" name="btn_accion" class="botones_largo" value="Guardar como PDF" onclick="reportes_generar_guardar_como('PDF')">
            </td>
        </tr>
    </table>
<?php } ?>
  </body>
</html>

