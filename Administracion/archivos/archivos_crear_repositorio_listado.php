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
if($_SESSION["perm_actualizar_sistema"]!=1) die("Usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
require_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/funciones_interfaz.php";
$dba =  new ConnectionHandler($ruta_raiz, "bodega");

if($orden_cambio==1) {
    if(strtolower($orderTipo)=="desc")
	$orderTipo="asc";
    else
        $orderTipo="desc";
}
if (!$orderTipo) $orderTipo="desc";

$seleccionar = "";
if (isset($_GET["accion"]) and $_GET["accion"]==1) {
    $seleccionar = ", case when i.esta_codi in (1,2) then 'Seleccionar' else '' end as \"SCR_Accion\"
                    , 'fjs_seleccionar_repositorio(\"'||i.indi_codi||'\")' as \"HID_POPUP\"";
}

$isql = "select -- Repositorio de archivos
              i.indi_codi as \"No.\"
            , i.nombre_tabla as \"Tabla\"
            , i.nombre_tablespace as \"Tablespace\"
            , round(i.tamanio::numeric/1073741824,2)::text||' Gb' as \"Tamaño Actual\" --1073741824 Gb
            , round(i.tamanio_maximo::numeric/1073741824,2)::text||' Gb' as \"Tamaño Máximo\"
            , round(i.tamanio::numeric/i.tamanio_maximo::numeric*100,2) as \"% Uso\"
            , e.nombre as \"Estado\"
            $seleccionar
         from indice i
            left outer join estado_indice e on e.esta_codi = i.esta_codi
         order by ".(1+$orderNo)." $orderTipo";
//echo $isql;

$pager = new ADODB_Pager($dba,$isql,'adodb', true,$orderNo,$orderTipo,true);
$pager->checkAll = false;
$pager->checkTitulo = true;
$pager->toRefLinks = $linkPagina;
$pager->toRefVars = $encabezado;
$pager->descCarpetasGen=$descCarpetasGen;
$pager->descCarpetasPer=$descCarpetasPer;
$pager->Render($rows_per_page=20,$linkPagina,$checkbox=chkAnulados);


?>