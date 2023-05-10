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
/*****************************************************************************************
**											**
*****************************************************************************************/

$ruta_raiz = "../..";
require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
//include_once "$ruta_raiz/funciones_interfaz.php";
session_start();
include_once "$ruta_raiz/rec_session.php";

if($orden_cambio==1) {
    if(strtolower($orderTipo)=="desc")
	$orderTipo="asc";
    else
        $orderTipo="desc";
}
if (!$orderTipo) $orderTipo="desc";
if (!$orderNo) $orderNo = 1;

    if (trim($_GET["txt_buscar_nombre"])=="")
        die("<center><h3>No se encontraron ciudadanos con esos datos.</h3></center></body></html>");

    echo "<center><h6>Funcionalidad únicamente para ciudadanos SIN firma.</h6></center></body></html>";

    // Verificar si existen ciudadanos con nombres similares o la misma cédula
    $where = buscar_2campos($_GET["txt_buscar_nombre"], "usua_nombre", "usua_cedula");

    //Se podrá combinar unicamente usuarios sin firma
    $sql = "select usua_cedula as \"Cédula\",
            usua_nombre as \"Nombre\",
            usua_titulo as \"Título\",
            usua_cargo as \"Cargo\",
            inst_nombre as \"Institución\",
            usua_email as \"Correo Electrónico\",
            'Seleccionar' as \"SCR_Usuario a Desactivar\",
            'usr_origen(\"'||usua_codi||'\");' as \"HID_FUNCION1\",
            'Seleccionar' as \"SCR_Usuario Final\",
            'usr_destino(\"'||usua_codi||'\",\"'||usua_cedula||'\");' as \"HID_FUNCION2\"
            from usuario where $where and inst_codi=0 and usua_esta=1 and tipo_usuario=2 order by " . ($orderNo+1) . " $orderTipo";

//echo $sql;

    	$pager = new ADODB_Pager($db,$sql,'adodb', true,$orderNo,$orderTipo,true);
	$pager->checkAll = false;
	$pager->checkTitulo = true;
	$pager->toRefLinks = $linkPagina;
	$pager->toRefVars = $encabezado;
	$pager->descCarpetasGen=$descCarpetasGen;
	$pager->descCarpetasPer=$descCarpetasPer;
	$pager->Render($rows_per_page=30,$linkPagina,$checkbox=chkAnulados);


?>
