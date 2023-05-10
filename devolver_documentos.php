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
require_once("$ruta_raiz/funciones.php");
p_register_globals(array());

session_start();
include_once "$ruta_raiz/rec_session.php";

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

    $txt_documento = trim(limpiar_sql($txt_documento));
    $estado=20;
    $carpeta=20;
    if($orden_cambio==1)
    {
        if(trim($orderTipo)=="DESC")
           $orderTipo="ASC";
        else
            $orderTipo="DESC";
    }
    if (!$orderTipo) $orderTipo="DESC";
    $encabezado = "carpeta=$carpeta&txt_documento=$txt_documento&orderTipo=$orderTipo&orderNo=";
    $linkPagina = "$PHP_SELF?$encabezado";
?>
<body onLoad="window_onload();">
    <form name="form_buscar" id="form_buscar" action="<?=$linkPagina?>" method="post">
        <!--<table width="100%" align="center" class=borde_tab border="0">
            <tr>
                <td width="25%" class="titulos5">
                    <br>No. de Documento:<br>&nbsp;
                </td>
                <td width="50%" class="listado5" valign="middle">
                    <input name="txt_documento" type="text" size="60" class="tex_area" value="<?=$txt_documento?>">
                </td>
                <td width="25%" class="titulos5" valign="middle">
                    <center><input type=submit value='Buscar' name='btn_buscar class='botones'></center>
                </td>
            </tr>
        </table>-->
        <table width="100%" align="center" cellspacing="5" cellpadding="0" borde=1>
            <tr>
                <td width="20%">
                    <!---    Buscar <?=$_SESSION["descRadicado"]?>(s) (Separados por coma) -->
                    <input name="busqRadicados" type="text" size="40" class="tex_area" value="<?=$_POST['busqRadicados']?>">
                </td>
                <td>
                    <input type=submit value='Buscar' name=Buscar valign='middle' class='botones' title="Busca el texto ingresado en: Numero Documento, Asunto, No. Referencia y Fecha">
                </td>
                <td align="left" width="80%">
                    <a target='mainFrame'  onclick="cambioMenu(<?=$num?>);"
                    href="busqueda/busqueda.php" class="aqui">B&uacute;squeda Avanzada</a>
                </td>
            </tr>
        </table>
    </form>
<br>
    <form name="form1" id="form1" action="./tx/formEnvio.php?<?=$encabezado?>" method="POST">

<?
    include "$ruta_raiz/tx/txOrfeo.php";

    //if ($txt_documento == "") $txt_documento = "NO BUSCAR NADA";
    if ($busqRadicados == "") $busqRadicados = "NO BUSCAR NADA";
    include "$ruta_raiz/include/query/query_devolver_documentos.php";

//    $db->query('set enable_nestloop = off');
	$pager = new ADODB_Pager($db,$isql,'adodb', true,$orderNo,$orderTipo);
	$pager->checkAll = false;
	$pager->checkTitulo = true;
	$pager->toRefLinks = $linkPagina;
	$pager->toRefVars = $encabezado;
	$pager->descCarpetasGen=$descCarpetasGen;
	$pager->descCarpetasPer=$descCarpetasPer;
	$pager->Render($rows_per_page=20,$linkPagina,$checkbox=chkAnulados);
//    $db->query('set enable_nestloop = on');
    
?>

    </form>
</body>
</html>

