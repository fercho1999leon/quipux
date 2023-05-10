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

$ruta_raiz = ".";
session_start();
include_once "$ruta_raiz/rec_session.php";
if (isset ($replicacion) && $replicacion && $config_db_replica_cuerpo_paginador!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_cuerpo_paginador);

$txt_fecha_desde = trim(limpiar_sql($_GET['txt_fecha_desde']));
$txt_fecha_hasta = trim(limpiar_sql($_GET["txt_fecha_hasta"]));
$estado = 0+$_GET["estado"];
$busqRadicados = trim(limpiar_sql($_GET["busqRadicados"]));
$carpeta = 0 + $_GET["carpeta"];
$tipoLectura = 0+$_GET["tipoLectura"];
$tarea_tipo = 0+$_GET["slc_tarea_tipo"];
$tarea_estado = 0+$_GET["slc_tarea_estado"];

$slc_tipo_fecha = 0 +$_GET['slc_tipo_fecha'];
//tipo de documento
$radi_tipo = 0 + $_GET['radi_tipo'];

$encabezado = "carpeta=$carpeta&";

$whereFiltro = "";
if ($carpeta!=13)
    if($tipoLectura!='2') $whereFiltro .= " and b.radi_leido=$tipoLectura ";
 
if ($busqRadicados != "") {
    $whereFiltro .= " and (" . buscar_cadena($busqRadicados,"coalesce(b.radi_nume_text,'')||' '||coalesce(b.radi_asunto,'')||' '||coalesce(b.radi_cuentai,'')").") ";
}

//tipo de documento
//Realizar la busqueda Elaboracion o Enviados o Recibidos
if ($carpeta==1 || $carpeta==8 || $carpeta==2)
if ($radi_tipo!=0) $whereFiltro .= " and b.radi_tipo=$radi_tipo ";


if($orden_cambio==1) {
    if(strtolower($orderTipo)=="desc")
	$orderTipo="asc";
    else
        $orderTipo="desc";
}
//if (!$orderTipo) $orderTipo="desc";

if (!$orderTipo) {
    $orderTipo="desc";
    if ($carpeta == "1" or $carpeta=="99") $orderTipo="asc";
}

include "$ruta_raiz/include/query/queryCuerpo.php";

echo "<br>";
$pager = new ADODB_Pager($db,$isql,'adodb', true,$orderNo,$orderTipo,true);
$pager->checkAll = false;
$pager->checkTitulo = true;
$pager->toRefLinks = $linkPagina;
$pager->toRefVars = $encabezado;
$pager->descCarpetasGen=$descCarpetasGen;
$pager->descCarpetasPer=$descCarpetasPer;
$pager->Render($rows_per_page=20,$linkPagina,$checkbox=chkAnulados);


//echo $isql;

$contador == "-1";
if (!$version_light) {
    $contador = $pager->num_rows;
    if ($carpeta==1 or $carpeta==2 or $carpeta==12 or $carpeta==13 or $carpeta==14 or $carpeta==15 or $carpeta==16) //Poner las demás carpetas
        $contador = cargar_contadores_bandejas($carpeta);
}
?>
<input type="hidden" name="txt_contador" id="txt_contador" value="<?=$contador?>">
