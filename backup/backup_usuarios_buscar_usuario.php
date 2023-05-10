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

$txt_nombre = trim(limpiar_sql($_GET["txt_nombre"]));
$cmb_institucion = trim(limpiar_sql($_GET["cmb_institucion"]));

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

    $isql = "select u.usua_nombre AS \"SCR_Nombre\"
            ,'seleccionar_usuario(\"'|| u.usua_codi ||'\");' as \"HID_FUNCION\"
            , substring(u.usua_login,2,length (u.usua_login)) AS \"Login\"
            , u.usua_email AS \"Email\"
            , u.depe_nomb AS \"Área\"
            , case when u.usua_esta = 1 then 'Activo' else 'Inactivo' end AS  \"Estado \"
            from usuario u
            where u.tipo_usuario='1' and u.usua_codi >= 0 ";
    if ($cmb_institucion != "0") $isql .= " and u.inst_codi=$cmb_institucion ";
    if ($txt_nombre != "") $isql .= ' and ' . buscar_nombre_cedula($txt_nombre); 
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
	$pager->Render($rows_per_page=10,$linkPagina,$checkbox=chkAnulados);
//    $db->query('set enable_nestloop = on');
    
?>

  </body>
</html>

