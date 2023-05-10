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
require_once("$ruta_raiz/funciones.php");

$txt_fecha_desde = trim(limpiar_sql($_GET['txt_fecha_desde']));
$txt_fecha_hasta = trim(limpiar_sql($_GET["txt_fecha_hasta"]));
$txt_dependencia = trim(limpiar_sql($_GET['txt_depe_codi']));
$txt_usuario = trim(limpiar_sql($_GET["txt_usua_codi"]));
$busqRadicados = trim(limpiar_sql($_GET["busqRadicados"]));
$carpeta = 0 + $_GET["carpeta"];
$imprimir = $_GET["imprimir_comprobante"];

$encabezado = "carpeta=$carpeta&";

$busq_radicados_tmp = "";

//Pa buscar por Asunto, Número de Documento ó Número de Referencia
if ($busqRadicados != "") {
    $busq_radicados_tmp .= " and coalesce(radi_nume_text,'')||coalesce(radi_asunto,'')||coalesce(radi_cuentai,'') ilike '%$busqRadicados%' ";
}


//Para buscar por usuario
if($txt_dependencia!="0" and $txt_usuario!="0" and $txt_usuario!="")
    $busq_radicados_tmp .= " and (string_to_array(trim(both '-' from radi_usua_rem), '--') @> array[$txt_usuario::text] or string_to_array(trim(both '-' from radi_usua_dest), '--') @> array[$txt_usuario::text])";

//Para buscar todos
//echo "depen: ".$txt_dependencia;

if ($txt_dependencia!="0" and $txt_usuario=="0") {
                $usr = "'-";
                if ($txt_usua_codi == "0") {
                    $sql = "select usua_codi from usuarios where depe_codi=$txt_dependencia";
                    $rs = $db->conn->Execute($sql);
                    if (!$rs->EOF) {
                        while (!$rs->EOF) {
                            $usr .= $rs->fields["USUA_CODI"] . "-','-";
                            $rs->MoveNext();
                        }
                        $usr = substr($usr,0,-3);
                    }
                } else {
                    $usr = $txt_usua_codi;
                }
               //echo $usr;
    $busq_radicados_tmp .= " and (radi_usua_rem in ($usr) or radi_usua_dest in($usr))";           
}

if ($txt_dependencia=="0" and $txt_usuario!="0"){
    $busq_radicados_tmp .= " and (string_to_array(trim(both '-' from radi_usua_rem), '--') @> array[$txt_usuario::text] or string_to_array(trim(both '-' from radi_usua_dest), '--') @> array[$txt_usuario::text])";
}



if($orden_cambio==1) {
    if(strtolower($orderTipo)=="desc")
	$orderTipo="asc";
    else
        $orderTipo="desc";
}
//if (!$orderTipo) $orderTipo="desc";

if (!$orderTipo) {
    $orderTipo="desc";
}


?>
  <body>
    <br>
<?php
    include "$ruta_raiz/include/query/uploadFile/queryUploadFileRad.php";
    if (trim($imprimir)=="si")
        $query = $query1;
    if (isset($_GET["asocImgRad"]) && $_GET["asocImgRad"]=="0")
        $query = $query3;

    //echo $query;
    //    $db->query('set enable_nestloop = off');
    $pager = new ADODB_Pager($db,$query,'adodb',true,$orderNo,$orderTipo,true);
    $pager->checkAll = false;
    $pager->checkTitulo = true;
    $pager->toRefLinks = $linkPagina;
    $pager->toRefVars = $encabezado;
    $pager->descCarpetasGen=$descCarpetasGen;
    $pager->descCarpetasPer=$descCarpetasPer;
    $pager->Render($rows_per_page=20,$linkPagina,$checkbox=chkAnulados);
//    $db->query('set enable_nestloop = on');
?>
  </body>
</html>