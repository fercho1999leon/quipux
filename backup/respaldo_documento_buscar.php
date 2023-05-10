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
  
  $txt_documento = trim(limpiar_sql($_GET["txt_documento"]));
  $adodb_next_page = trim(limpiar_sql($_GET["adodb_next_page"]));  
  
include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

if($orden_cambio==1) {
    if(strtolower($orderTipo)=="desc")
	$orderTipo="asc";
    else
        $orderTipo="desc";
}
    if (!$orderTipo) $orderTipo="desc";
    if($orderNo == 0) $orderNo = 2;    
?>
  <body>
    <br>
<?

    if ($txt_documento == "") die("<center>Por favor ingrese un n&uacute;mero de documento v&aacute;lido.</center>");
    
    //Se realiza la búsqueda
    if (!$db->driver){ $db = $this->db; }	//Esto sirve para cuando se llama este archivo dentro de clases donde no se conoce $db.
    
    switch($db->driver)
    {
        case 'postgres':
            $sqlFecha = "substr(radi_fech_ofic::text,1,19)";

            $usuarioSel = 0+$_SESSION["usua_codi"];
//            $from_usr_recorrido = " radi_nume_radi in (select distinct radi_nume_radi from hist_eventos ".
//                                " where usua_codi_ori=$usuarioSel or usua_codi_dest=$usuarioSel) ";

            $isql = "select -- Asociacion de documentos
                    'Seleccionar' AS \"SCR_Acción\",'seleccionar_documento(\"'|| radi_nume_radi ||'\",\"'|| radi_nume_text ||'\");' as \"HID_FUNCIONA\"
                    , radi_nume_text as \"Número Documento\"
                    ,$sqlFecha as \"SCR_Fecha Documento\"
                    ,'ver_documento_asociado(\"'|| radi_nume_radi ||'\",\"'|| radi_nume_text ||'\");' as \"HID_FUNCION_VER\"
                    ,radi_asunto  as \"Asunto\"
                    ,ver_usuarios(radi_usua_actu::text,',') AS \"Usuario Actual\"
                    ,ver_usuarios(radi_usua_rem,',<br>') AS \"De\"
                    ,ver_usuarios(radi_usua_dest,',<br>') AS \"Para\"
                    ,trad_descr as \"Tipo de Documento\"";
           
                //$isql.=",'Seleccionar' AS \"SCR_Acción\",'seleccionar_documento(\"'|| radi_nume_radi ||'\",\"'|| radi_nume_text ||'\");' as \"HID_FUNCIONA\"
            $isql.="from (
                    select r.radi_nume_text, r.radi_fech_ofic, r.radi_nume_radi ,r.radi_asunto ,r.radi_usua_actu
                    , r.radi_usua_rem, r.radi_usua_dest, t.trad_descr, r.radi_nume_asoc
                    from (select * from radicado b where 
                        radi_nume_text like '%".strtoupper($txt_documento)."%'
                        and radi_inst_actu = " . $_SESSION["inst_codi"] . "
                        and (radi_nume_radi::text like '%1')
                        and esta_codi in (2,0)) as r
                    left outer join tiporad t on r.radi_tipo=t.trad_codigo
                    order by ".($orderNo)." $orderTipo *LIMIT**OFFSET*
                ) as a order by ".($orderNo)." $orderTipo";

            //echo $isql."<hr>";

            break;
    }
    
    
    //Se arma la consulta
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