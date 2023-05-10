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
  include_once "$ruta_raiz/obtenerdatos.php";
  require_once("$ruta_raiz/funciones.php");

  $radi_nume = trim(limpiar_sql($_GET['radi_nume']));
  $txt_documento = trim(limpiar_sql($_GET["txt_documento"]));
  $txt_radi_asoc_ante = trim(limpiar_sql($_GET["txt_radi_asoc_ante"]));
  $txt_radi_asoc_cons = trim(limpiar_sql($_GET["txt_radi_asoc_cons"]));
  $adodb_next_page = trim(limpiar_sql($_GET["adodb_next_page"]));
  $txt_editar_refe = trim(limpiar_sql($_GET["txt_editar_refe"]));
  $radi_reg=ObtenerDatosRadicado($radi_nume, $db);
  $radi_nume_deri = $radi_reg['radi_padre'];//radi nume deri
  $radi_nume_tmp = $radi_reg['radi_nume_temp'];
  
include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

if($orden_cambio==1) {
    if(strtolower($orderTipo)=="desc")
	$orderTipo="asc";
    else
        $orderTipo="desc";
}
    if (!$orderTipo) $orderTipo="desc";
?>
  <body>
    <br>
<?

    if ($txt_documento == "") die("<center>Por favor ingrese un n&uacute;mero de documento v&aacute;lido.</center>");
    include "asociar_documento_buscar_query.php";

//    $db->query('set enable_nestloop = off');
	$pager = new ADODB_Pager($db,$isql,'adodb', true,$orderNo,$orderTipo,true);
	$pager->checkAll = false;
	$pager->checkTitulo = true;
	$pager->toRefLinks = $linkPagina;
	$pager->toRefVars = $encabezado;
	$pager->descCarpetasGen=$descCarpetasGen;
	$pager->descCarpetasPer=$descCarpetasPer;
	$pager->Render($rows_per_page=10,$linkPagina,$checkbox=chkAnulados);
//    $db->query('set enable_nestloop = on');
    
?>

  </body>
</html>

