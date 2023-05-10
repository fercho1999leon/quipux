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
$ruta_raiz = "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
if (isset ($replicacion) && $replicacion && $config_db_replica_uf_upload_file_radicado!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_uf_upload_file_radicado);
$verrad = "";
$carpeta = 98;
/** PROGRAMA DE CARGA DE IMAGENES DE RADICADOS
  *@author JAIRO LOSADA - DNP - SSPD
  *@version Orfeo 3.5.1
  *
  *@param $varBuscada sTRING Contiene el nombre del campo que buscara
  *@param $krd  string Trae el Login del Usuario actual
  *@param $isql strig Variable temporal que almacena consulta
  */
    if($orden_cambio==1)
    {
        if(trim($orderTipo)=="DESC")
           $orderTipo="ASC";
        else
            $orderTipo="DESC";
    }
	if ($orderNo==null) $orderNo = 2;


include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

?>
<script type="text/javascript">
function mostrar_documento(numdoc, txtdoc,carpeta)
    {
	var_envio='<?=$ruta_raiz?>/verradicado.php?verrad='+numdoc+'&textrad='+txtdoc+'&carpetad='+carpeta+'&menu_ver_tmp=3';
	window.open(var_envio,numdoc,"height=650,width=800,scrollbars=yes,left=800,top=200");
    }
</script>
<BODY>
<FORM ACTION="<?=$_SERVER['PHP_SELF']?>?imprimir=<?=$imprimir?>" method="POST">
<?
/**
  *@param $varBuscada string Contiene el nombre del campo que buscara
  *@param $busq_radicados_tmp sting Almacena cadena de busqueda de radicados generada por pagina paBuscar.php
  */
$varBuscada = "RADI_NUME_TEXT";
//include "$ruta_raiz/envios/paEncabeza.php";
$pagina_actual = "uploadFileRadicado.php";
include "$ruta_raiz/envios/paBuscar.php";
$encabezado = "carpeta=$carpeta&busqRadicados=$busqRadicados&imprimir=$imprimir&adodb_next_page=1&orderTipo=$orderTipo&orderNo=";
$linkPagina = "$PHP_SELF?$encabezado";

if (trim($imprimir)=="si")
	$accion="$ruta_raiz/plantillas/CodigoBarras.php?nuevo=no";
else
	$accion="formUpload.php";
?>
</FORM>
<FORM ACTION="<?=$accion?>" name='formulario' id='formulario' method="POST">
<br>
<?
if (trim($imprimir)=="si") {
    echo "<input type='hidden' name='tipo_comp' id='tipo_comp' value='' >";
    echo "<center><input type='button' value='Imprimir C&oacute;digo de Barras' name=asocImgRad class='botones_largo' onclick='document.formulario.tipo_comp.value=\"1\"; document.formulario.submit();'>
    <input type='button' value='Imprimir Comprobante' name=asocImgRad class='botones_largo' onclick='document.formulario.tipo_comp.value=\"2\"; document.formulario.submit();'>
    <input type='button' value='Imprimir Ticket' name=asocImgRad class='botones_largo' onclick='document.formulario.tipo_comp.value=\"3\"; document.formulario.submit();'>
    </center>";
}
else
    echo "<center><input type='submit' value='Asociar Imagen' name=asocImgRad class='botones_largo' title='Permite asociar al documento una imagen resultado de escanear el documento fisico'></center>";
echo "<br>";

if (!$busq_radicados_tmp) $busq_radicados_tmp = "";

if(trim($busq_radicados_tmp)!="" or trim($imprimir)!="si")
{
//		$orderTipo=" DESC ";
    include "$ruta_raiz/include/query/uploadFile/queryUploadFileRad.php";
    if (trim($imprimir)=="si")
        $query = $query1;
    $pager = new ADODB_Pager($db,$query,'adodb', true,$orderNo,$orderTipo);
    $pager->checkAll = false;
    $pager->checkTitulo = true;
    $pager->toRefLinks = $linkPagina;
    $pager->toRefVars = $encabezado;
    $pager->descCarpetasGen=$descCarpetasGen;
    $pager->descCarpetasPer=$descCarpetasPer;
    $pager->Render($rows_per_page=20,$linkPagina,$checkbox=chkAnulados);
}
?>
</FORM>
</BODY>
</HTML>
