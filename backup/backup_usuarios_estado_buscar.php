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
include_once "$ruta_raiz/config.php";
include_once "$ruta_raiz/include/db/ConnectionHandler.php";
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/funciones.php";

$db = new ConnectionHandler("$ruta_raiz");
$db->conn->SetFetchMode(ADODB_FETCH_ASSOC);

$cmb_institucion = trim(limpiar_sql($_GET["cmb_institucion"]));
$txt_fecha_inicio_sol = trim(limpiar_sql($_GET["txt_fecha_inicio_sol"]));
$txt_fecha_fin_sol = trim(limpiar_sql($_GET["txt_fecha_fin_sol"]));
$cmb_estado = trim(limpiar_sql($_GET["cmb_estado"]));

$orden_cambio = isset($_GET["orden_cambio"]) ? $_GET["orden_cambio"] : "";
$orderTipo = isset($_GET["orderTipo"]) ? $_GET["orderTipo"] : "";
$orderNo = isset($_GET["orderNo"]) ? $_GET["orderNo"] : "";
$linkPagina = isset($_GET["linkPagina"]) ? $_GET["linkPagina"] : "";
$encabezado = isset($_GET["encabezado"]) ? $_GET["encabezado"] : "";
$descCarpetasGen = isset($_GET["descCarpetasGen"]) ? $_GET["descCarpetasGen"] : "";
$descCarpetasPer = isset($_GET["descCarpetasPer"]) ? $_GET["descCarpetasPer"] : "";
$where = "";

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
   if ($cmb_institucion != "0") 
        $where .= " and u.inst_codi=$cmb_institucion ";
    
    switch ($cmb_estado) {
        case 1: //Pendiente
            $where .= " and fecha_fin is null and fecha_eliminado is null ";
            break;
        case 2: //Finalizado
            $where .= " and fecha_fin is not null and fecha_eliminado is null";
            break;    
        case 3: //Eliminado
            $where .= " and fecha_eliminado is not null";
            break;
        default:
            break;
    }   
    
    $txt_tipo_lista=10;
    $htmlDes = "<span><font color=\"blue\">Descarga Solicitada</font></span>";
    $isql = "select substr(r.fecha_solicita::text,1,19) as \"Fecha Solicitud\",
            case when  s.resp_soli_codi is null then 'A' || r.resp_codi::character varying else s.resp_soli_codi::character varying end as \"SCR_Solicitud\",
            'seleccionar_solicitud(\"'|| s.resp_soli_codi ||'\", \"'|| $txt_tipo_lista ||'\");' as \"HID_FUNCION_SELECCIONAR\",
            u.usua_nombre as \"Usuario\",
            u.inst_nombre as \"Institución\",
            substr(s.fecha_ejecutar::text,1,10) as \"Fecha a Ejecutar\",
            case when s.resp_soli_codi is null then (case when ((r.num_documentos is null or r.num_documentos = 0) and r.fecha_inicio is null) then ''::text else r.num_documentos::text end)  else
            (case when (r.num_documentos is null or r.num_documentos = 0) then
                (case when (s.num_documentos = -1) then ''::text else s.num_documentos::text end)
                else r.num_documentos::text end) end as \"No. Documentos\",
            case when fecha_eliminado is not null then 'Eliminado' else case when fecha_fin is not null then 'Finalizado' else 'Pendiente' end end as \"Estado\",            
            --case when fecha_eliminado is null and fecha_fin is not null then 'Descargar' else '' end as \"SCR_Descargar\",
            case when fecha_eliminado is null and fecha_fin 
            is not null then 
               case when s.resp_soli_codi in (select resp_soli_codi 
               from respaldo_hist_eventos where accion=86) then
               '$htmlDes'
               else
               'Descargar'
               end
            else '' end as \"SCR_Descargar\",
            'descargar_respaldo(\"' || r.resp_codi || '\", \"'|| coalesce(s.resp_soli_codi,0) ||'\");' as \"HID_FUNCION\",
            case when fecha_eliminado is null then 'Eliminar' else '' end as \"SCR_Eliminar\",
            'eliminar_respaldo(\"' || r.resp_codi || '\", \"'|| case when s.resp_soli_codi is null then  0 else s.resp_soli_codi end ||'\");' as \"HID_FUNCION_ELIMINAR\"
            from respaldo_usuario r 
            left outer join usuario u on r.usua_codi=u.usua_codi
            left outer join respaldo_solicitud s on r.resp_codi=s.resp_codi";
    $isql .= " where r.fecha_solicita between '$txt_fecha_inicio_sol 00:00:00' and '$txt_fecha_fin_sol 23:59:59' 
             $where";
    $isql .= " order by ".($orderNo+1)." $orderTipo";

//echo "$isql";
//    $db->query('set enable_nestloop = off');
	$pager = new ADODB_Pager($db,$isql,'adodb', true,$orderNo,$orderTipo,true);
	$pager->checkAll = false;
	$pager->checkTitulo = true;
	$pager->toRefLinks = $linkPagina;
	$pager->toRefVars = $encabezado;
	$pager->descCarpetasGen=$descCarpetasGen;
	$pager->descCarpetasPer=$descCarpetasPer;
	$pager->Render($rows_per_page=20,$linkPagina,$checkbox=null);
//    $db->query('set enable_nestloop = on');
    
?>
  </body>
</html>