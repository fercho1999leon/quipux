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
require_once("$ruta_raiz/funciones.php");
p_register_globals($_GET);
include_once "$ruta_raiz/rec_session.php";
if ($_SESSION["usua_codi"] != "0") die ("");

if($orden_cambio==1) {
    if(strtolower($orderTipo)=="desc")
	$orderTipo="asc";
    else
        $orderTipo="desc";
}
if (!$orderTipo) $orderTipo="desc";
if (!$orderNo) $orderNo = 2;


    $where = " and upper(r.radi_nume_text) like upper('%".trim(limpiar_sql($txt_nume_documento))."%')";

    $isql = "select -- revertir firma digital
            radi_nume_radi as \"CHR_rad_numero_documento\"
            ,radi_nume_text as \"No. Documento\"
            ,substr(radi_fech_ofic::text,1,19) as \"DAT_Fecha Documento\"
            ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
            ,radi_cuentai as \"No. de Referencia\"
            ,radi_asunto  as \"Asunto\"
            ,usua_nombre AS \"Usuario Actual\"
            ,depe_nomb as \"Área Actual\"
            ,ver_usuarios(radi_usua_rem,',<br>') AS \"De\"
            ,ver_usuarios(radi_usua_dest,',<br>') AS \"Para\"
            ,trad_descr as \"Tipo de Documento\"
            ,CASE WHEN radi_fech_firma is not null THEN 'SI' ELSE 'NO' END as \"Firma Digital\"
            from (
                select '1', b.radi_nume_text, b.radi_fech_ofic, b.radi_nume_radi, b.radi_cuentai, b.radi_asunto
                    , coalesce(u.usua_nomb,'') || ' ' || coalesce(u.usua_apellido,'') as usua_nombre, d.depe_nomb
                    , b.radi_usua_rem, b.radi_usua_dest, t.trad_descr, b.radi_fech_firma
                from (
                    select r.radi_nume_text, r.radi_fech_ofic, r.radi_nume_radi, r.radi_cuentai, r.radi_asunto, r.radi_usua_actu,
                        r.radi_usua_rem, r.radi_usua_dest, r.radi_fech_firma, r.radi_tipo
                    from radicado r
                    where r.radi_inst_actu=" . $_SESSION["inst_codi"] . "
                        and r.esta_codi in (0,6)
                        and r.radi_nume_radi::text like '%0'
                        $where
                ) as b
                left outer join usuarios u on b.radi_usua_actu=u.usua_codi
                left outer join dependencia d on u.depe_codi=d.depe_codi
                left outer join tiporad t on b.radi_tipo=t.trad_codigo
                order by " . ($orderNo+1) . " $orderTipo *LIMIT**OFFSET*
            ) as a";

//    echo $isql."<hr>";

//    $db->query('set enable_nestloop = off');
	$pager = new ADODB_Pager($db, $isql, 'adodb', true, $orderNo, $orderTipo, true);
	$pager->checkAll = false;
	$pager->checkTitulo = true;
	$pager->toRefLinks = $linkPagina;
	$pager->toRefVars = $encabezado;
	$pager->descCarpetasGen=$descCarpetasGen;
	$pager->descCarpetasPer=$descCarpetasPer;
	$pager->Render($rows_per_page=20,$linkPagina,$checkbox=chkAnulados);
//    $db->query('set enable_nestloop = on');

?>