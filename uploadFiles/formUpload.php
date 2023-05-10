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
require_once("$ruta_raiz/funciones.php");
p_register_globals(array());

session_start();
include_once "$ruta_raiz/rec_session.php";

/**
 * Retorna la cantidad de bytes de una expresion como 7M, 4G u 8K.
 *
 * @param char $var
 * @return numeric
 */

$txt_depe_codi = limpiar_sql($_POST['txt_depe_codi']);$txt_depe_codi="'".$txt_depe_codi."'";
$txt_fecha_desde = limpiar_sql($_POST['txt_fecha_desde']);$txt_fecha_desde="'".$txt_fecha_desde."'";
$txt_fecha_hasta = limpiar_sql($_POST['txt_fecha_hasta']);$txt_fecha_hasta="'".$txt_fecha_hasta."'";
$txt_usua_codi = limpiar_sql($_POST['txt_usua_codi']);
$busqRadicados = limpiar_sql($_POST['busqRadicados']);$busqRadicados = "'".$busqRadicados."'";

function return_bytes($val)
{	$val = trim($val);
	$ultimo = strtolower($val{strlen($val)-1});
	switch($ultimo)
	{	// El modificador 'G' se encuentra disponible desde PHP 5.1.0
		case 'g':	$val *= 1024;
		case 'm':	$val *= 1024;
		case 'k':	$val *= 1024;
	}
	return $val;
}
?>


<script type="text/javascript">
<?/*
	$rs = $db->conn->query("select * from anexos_tipo where anex_tipo_estado=1");
	$i=0;
	echo "var tipo= new Array();";
	while(!$rs->EOF) {
	    echo "tipo[$i]='" . $rs->fields["ANEX_TIPO_EXT"] . "';";
	    $rs->MoveNext();
	    $i++;
	}*/
?>

function valMaxChars(maxchars)
{
	if (document.realizarTx.observa.value.length > maxchars)
	{	alert('Demasiados caracteres en el texto, solo se permiten '+ maxchars);
 		setSel(maxchars,document.realizarTx.observa.value.length);
 		document.realizarTx.observa.focus();
		return false;
	}
	else return true;
}

function validar()
{	if (valMaxChars(100))
	{	if (document.realizarTx.upload.value.length == 0)
		{	alert('Seleccione la imagen que desea asociar al documento');
			document.realizarTx.upload.focus();
			return false;
		}
		else return true;
	}
	else return false;
}

var tipo= new Array();
tipo[0]="jpg";
tipo[1]="gif";
tipo[2]="png";
tipo[3]="pdf";

function validar_archivo()
{
    mensaje = '';

    arch = document.getElementById('upload').value.toLowerCase();
    arch = arch.replace(/.p7m/g, "");
    arr_ext = arch.split('.');
    cadena = arr_ext[arr_ext.length-1].toLowerCase();
    flag=true;
    for (j = 0;j <= 3; ++j) {
	if (tipo[j]==cadena) flag=false;
    }
    if (flag) {
	alert ('No está permitido anexar archivos con extensión '+cadena+'.\n'+mensaje+'Consulte con su administrador del sistema.');
	document.getElementById('upload').value = '';
	return;
    }
    return;
}
function regresar(){
    var txt_depe_codi = <?=$txt_depe_codi?>;
    var txt_fecha_desde = <?=$txt_fecha_desde?>;
    var txt_fecha_hasta = <?=$txt_fecha_hasta?>;
    var txt_usua_codi = <?=$txt_usua_codi?>;
    var busqRadicados = <?=$busqRadicados?>;
    document.location.href = "cargar_doc_digitalizado.php?txt_depe_codi="+txt_depe_codi+"&txt_fecha_desde="+txt_fecha_desde+"&txt_fecha_hasta="+txt_fecha_hasta+"&txt_usua_codi="+txt_usua_codi+'&busqRadicados='+busqRadicados;
}
</script>
<?php
 /*
	* Genreamos el encabezado que envia las variable a la paginas siguientes.
	* Por problemas en las sesiones enviamos el usuario.
	* @$encabezado  Incluye las variables que deben enviarse a la singuiente pagina.
	* @$linkPagina  Link en caso de recarga de esta pagina.
	*/
        $getbusqueda = "txt_depe_codi=$txt_depe_codi&txt_fecha_desde=$txt_fecha_desde&txt_fecha_hasta=$txt_fecha_hasta&txt_usua_codi=$txt_usua_codi";
	$encabezado = "depeBuscada=$depeBuscada&filtroSelect=$filtroSelect&tpAnulacion=$tpAnulacion&$getbusqueda";
	$linkPagina = "$PHP_SELF?$encabezado&orderTipo=$orderTipo&orderNo=";

    include_once "$ruta_raiz/funciones_interfaz.php";
    echo "<html>".html_head();


    if (!strlen(trim ($valRadio)))
    {
        $mensajeError = "<center><br><table class='borde_tab' width=100% CELSPACING=5>
                            <tr class=titulosError>
                                <td align='center'>No hay Documento seleccionado para realizar la Asociaci&oacute;n de Imagen
                                </td>
                            </tr>
                            <tr>
                                <td align='center'><input type='button' value='Regresar' onClick='history.back();' name='enviardoc' class='botones' id='Cancelar'></td>
                            </tr>
                        </table></center>";
        die ($mensajeError);
            //die ("<table class='borde_tab' width=100% CELSPACING=5><tr class=titulosError><td><center>No hay Documentos seleccionados para realizar la Impresi&oacute;n</CENTER></td></tr></table>");
    }
?>
<body topmargin="0">
<form action="uploadTx.php?<?=$encabezado?>" method="post" name="realizarTx" enctype="multipart/form-data">
    <table border=0 width=100% cellpadding="0" cellspacing="0">
	<tr>
	    <td width=100%>
		<br>
		<table width="98%" border="0" cellpadding="0" cellspacing="5" class="borde_tab">
		    <!--tr>
			<td width="30%" class="titulos4">USUARIO:<br><br><?=$_SESSION['usua_nomb']?></td>
			<td width="30%" class="titulos4">AREA:<br><br><?=$_SESSION['depe_nomb']?></td>
			<td class="titulos4">Asociacion de Imagen al <?=$_SESSION["descRadicado"]?></td>
		    </tr-->
		    <tr align="center">
			<td colspan="3" class="celdaGris" align=center><br>
			    <textarea name="observa" id="observa" cols=70 rows=3 class=tex_area></textarea>
			</td>
		    </tr>
		    <tr>
			<td colspan="3" align="center">
  			    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo return_bytes(ini_get('upload_max_filesize')); ?>"><br>
			    <span class="leidos">Seleccione un Archivo (pdf, jpg, gif o png.   Tama&ntilde;o Max. <?=ini_get('upload_max_filesize')?>)
				  <input type="file" name="upload" id="upload" size="50" class=tex_area onChange="validar_archivo();"></span>
			    <input type="hidden" name="replace" value="y">
			    <input type="hidden" name="valRadio" value="<?=$valRadio?>">
			    <input name="check" type="hidden" value="y" checked>
  			</td>
  		    </tr>
            <tr>
			<td colspan=5 align="center">
  			    <input type="checkbox" name="chk_fisico" id="chk_fisico" value="1" class="ebutton" checked>
			    <span class="leidos">Documento F&iacute;sico</span>
			    <br/><br/>
  			</td>
  		    </tr>
		    <tr>
			<td colspan=5 align="center">
			    <input type="submit" value="Aceptar" name="Realizar" align="bottom" class="botones" id="Realizar" onclick="return validar();">
                            <input align='right' class='botones' value='Regresar' onclick='regresar();'>
			</td>
		    </tr>
		</table>
		<br>
	    </td>
	</tr>
    </table>
<?
	/*  GENERACION LISTADO DE RADICADOS
	 *  Aqui utilizamos la clase adodb para generar el listado de los radicados
	 *  Esta clase cuenta con una adaptacion a las clases utiilzadas de orfeo.
	 *  el archivo original es adodb-pager.inc.php la modificada es adodb-paginacion.inc.php
	 */
	if(!$orderNo)  $orderNo=0;
	$order = $orderNo + 1;
	$sqlFecha = $db->conn->SQLDate("d-m-Y H:i A","b.RADI_FECH_RADI");
	$busq_radicados_tmp = " and radi_nume_radi=$valRadio";

        include_once "$ruta_raiz/include/query/uploadFile/queryUploadFileRad.php";

        //echo $query2;
	if($codTx==12)
	{
            $isql = str_replace("Enviado Por" ,"Devolver a",$isql);
	}
	$pager = new ADODB_Pager($db,$query2,'adodb', true,$orderNo,$orderTipo);
	$pager->toRefLinks = $linkPagina;
	$pager->toRefVars = $encabezado;
	$pager->checkAll = true;
	$pager->checkTitulo = true;
	$pager->Render($rows_per_page=20,$linkPagina,$checkbox=chkAnulados);
?>
<input type='hidden' name=depsel value='<?=$depsel?>'>
</form>
</body>
</html>
