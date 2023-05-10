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
include_once "obtener_datos_archivo.php";

////////////////	VARIABLES BASICAS	////////////////////////

  $sql = "select coalesce(dep_central,depe_codi) as archivo from dependencia where depe_codi=".$_SESSION['depe_codi']."";
  $rs=$db->conn->query($sql);
  $depe_archivo = $rs->fields["ARCHIVO"];

  if (!isset($txt_buscar)) $txt_buscar = "";
  if (!isset($txt_codigo)) $txt_codigo = "";

  if($orden_cambio==1) {
    if($orderTipo=="desc")
	$orderTipo="asc";
    else
	$orderTipo="desc";
  }
  $encabezado = "buscar=1&txt_buscar=$txt_buscar&txt_codigo=$txt_codigo&orderTipo=$orderTipo&orderNo=";
  $encabezado = str_replace('"', "”", $encabezado);
  //$encabezado = "orderTipo=$orderTipo&orderNo=";
  $linkPagina = $PHP_SELF."?$encabezado";

  $sql = "select arch_nombre from archivo_nivel where depe_codi=$depe_archivo";
  $rs=$db->conn->query($sql);
//var_dump($sql);
  $niveles = 0;
  $titulo = "";
  $tmp = strtolower($rs->fields["ARCH_NOMBRE"]);
  while (!$rs->EOF) {
    if ($titulo!="") $titulo .= " >> ";
    $titulo .= $rs->fields["ARCH_NOMBRE"];
    $niveles++;		//Numero de niveles de almacenamiento
    $rs->MoveNext();
  }

  $sql_buscar = "";
  if($txt_buscar != "")
  {
    $textElements = split (",", $txt_buscar);
    foreach ($textElements as $item)
    {
	if (trim ($item) != "")
	    $sql_buscar .= " UPPER(r.radi_nume_text) like '%".strtoupper($item)."%' or";
    }
    $sql_buscar = " and (".substr($sql_buscar, 0, -2).")";
  }

  if ($txt_codigo != "")
    $sql_buscar .= " and ar.arch_codi=$txt_codigo";


include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

?>

  <body >
  <form method="post" name="formulario" action="<?=$linkPagina?>"> 
    <center>
    <table class="borde_tab" width="80%" cellspacing="5">
	<tr><td class=titulos2><center>Consultar ubicaci&oacute;n F&iacute;sica de Documentos</center></td></tr>
    </table>
    <br>

    <table width="80%" align="center" cellspacing="5" class="borde_tab">
	<tr>
	    <td width="25%" class="titulos2"><span class="titulos2">Buscar <?=$descRadicado?>(s) <br/>(Separados por coma)</span></td>
	    <td width="55%" class="listado2">
		<input name="txt_buscar" id="txt_buscar" type="text" size="40" class="tex_area" value='<?=$txt_buscar?>'>
	    	<input type="hidden" name="txt_codigo" id="txt_codigo" value='<?=$txt_codigo?>'>
	    <td width="20%" rowspan="2" valign="center" align="center" class="listado2"><center>
	       	<input type=submit value='Buscar' name=Buscar valign='middle' class='botones'></center>
            </td>
	</tr>
	<tr>
	    <td class="titulos2">
		<span class="titulos2">Buscar en 
		</span>
	    </td>
	    <td class="listado2">
		
		    <? if ($txt_codigo != "") echo ObtenerUbicacionFisica($txt_codigo,$db); 
			else echo "Todo el archivo"?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		    <a href="#" class="grid" onClick="document.getElementById('tbl_archivo').style.display='';">Cambiar</a>
		
	    </td>
	</tr>


    </table>
    <table class="borde_tab" width="80%" name="tbl_archivo" id="tbl_archivo" style="display:none">
	<tr><td class="titulos2" colspan="3"><?=$titulo?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		    <a href="#" onClick="document.getElementById('tbl_archivo').style.display='none';">Ocultar</a>
	    </td>
	</tr>
	<tr><td class="titulos2" width="70%">Nombre Item</td>
	    <td class="titulos2" width="15%">Tipo</td>
	    <td class="titulos2" width="15%">Acci&oacute;n</td>
	</tr>
	<tr><td  colspan="3">
	    <table width="100%">
		<?echo ArbolSeleccionarArchivo(0, 0 , $depe_archivo, "", $db, $ruta_raiz,"S","T","E",1);?>
	    </table></td>
	</tr>
    </table>
    <br>

<?
if ($buscar==1) {
    $sqlFecha = "substr(radi_fech_ofic::text,1,19)";
    $isql = "select --Archivo Fisico Consulta
                case when radi_nume_radi::text like '%1' then '&nbsp;&nbsp;<b>&rarr;</b>&nbsp;' else '' end || radi_nume_text as \"No. Documento\"
                ,$sqlFecha as \"DAT_Fecha\"
                ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                ,radi_asunto  as \"Asunto\"
                ,ver_usuarios(radi_usua_rem,',<br>') AS \"De\"
                ,ver_usuarios(radi_usua_dest,',<br>') AS \"Para\"
                ,trad_descr  as \"Tipo Documento\"
                ,num_anexos  as \"No. Anexos\"
                ,'Ver' as \"SCR_Ubicacion\"
                ,'VerUbicacion(\"'|| arch_codi || '\",\"'||radi_nume_text || '\",\"\");' as \"HID_FUNCION\"
            from (
                select r.radi_nume_text, r.radi_fech_ofic, r.radi_nume_radi, r.radi_asunto , r.radi_usua_rem, r.radi_usua_dest
                    , t.trad_descr, count(a.anex_radi_nume) as num_anexos, ar.arch_codi
                from archivo_radicado ar
                    left outer join radicado r on r.radi_nume_radi=ar.radi_nume_radi
                    left outer join tiporad t on t.trad_codigo=r.radi_tipo
                    left outer join anexos a on (a.anex_radi_nume=r.radi_nume_radi or a.anex_radi_nume=r.radi_nume_temp) and a.anex_borrado='N'
                where r.radi_inst_actu=".$_SESSION["inst_codi"]." $sql_buscar
                group by 1,2,3,4,5,6,7,9, r.radi_fech_radi
                order by ".($orderNo+1)." $orderTipo, r.radi_fech_radi asc *LIMIT**OFFSET*
            ) as b";



	$pager = new ADODB_Pager($db,$isql,'adodb', true,$orderNo,$orderTipo);
	$pager->checkAll = false;
	$pager->checkTitulo = true; 
	$pager->linkCabecera =false;
	$pager->toRefLinks = $linkPagina;
	$pager->toRefVars = $encabezado;
	$pager->Render($rows_per_page=20,$linkPagina);
?>
    <br/>
<?
} //Fin buscar

////////////////////////	BOTONES 	/////////////////////////

?>
    <table width="80%" cellspacing="5">
	<tr>
    	    <td > <center>
    		<input type="button" name="btn_limpiar" value="Limpiar" class="botones_largo" onClick="window.location='<?=$PHP_SELF?>'">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    		<input type="button" name="btn_cancelar" value="Regresar" class="botones_largo" onClick="window.location='menu_archivo.php';">
    	    </center></td>
	</tr>
    </table>

    <script>

	function MostrarFila(fila) {
	    if (document.getElementById(fila).style.display=='none') 
		document.getElementById(fila).style.display='';
	    else
		document.getElementById(fila).style.display='none';
	}

	function SeleccionarArchivo(codigo, nombre, nivel, nom_nivel, descripcion) {
	    document.getElementById('txt_codigo').value=codigo;
//	    document.getElementById('btn_aceptar').style.display='';
//	    document.getElementById('spn_archivo').innerHTML = descripcion;
	    document.formulario.submit();
	}

	function VerUbicacion(arch_codi, radi_nume, arch_path) {
    windowprops = "top=100,left=100,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=600,height=400";
    URL = 'ver_ubicacion_fisica.php?arch_codi='+arch_codi+'&radi_nume='+radi_nume+'&arch_path='+arch_path;
    window.open(URL , "ubicacion", windowprops);
//		alert (codigo);
	}

    </script>
  </center>
  </form>
  </body>
</html>


