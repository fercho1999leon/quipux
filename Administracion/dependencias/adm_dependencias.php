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
/**
*	Autor			Iniciales		Fecha (dd/mm/aaaa)
*
*
*	Modificado por		Iniciales		Fecha (dd/mm/aaaa)
*	Santiago Cordovilla	SC			19-12-2008
*
*	Comentado por		Iniciales		Fecha (dd/mm/aaaa)
*	Santiago Cordovilla	SC			19-12-2008
**/
$ruta_raiz = "../..";
session_start();
if($_SESSION["usua_admin_sistema"]!=1) die("");
include_once "$ruta_raiz/rec_session.php";	

if (!isset($slc_dependencia)) $slc_dependencia = 0;
$txt_depe_codi = $slc_dependencia;

/**
* Llena los campos de las areas a editar
**/
if ($slc_dependencia != 0) {
    $sql = "select * from dependencia where depe_codi=$slc_dependencia";
    $rs = $db->conn->query($sql);
    $txt_nombre = $rs->fields['DEPE_NOMB'];
    $txt_sigla = $rs->fields['DEP_SIGLA'];
    $slc_padre = $rs->fields['DEPE_CODI_PADRE'];
    $txt_ciudad = $rs->fields['DEPE_PIE1']; //
    $slc_archivo = $rs->fields['DEP_CENTRAL'];
    $slc_plantilla = $rs->fields['DEPE_PLANTILLA'];
}
else {
    $txt_nombre = "";
    $txt_ciudad = "";
    $txt_sigla = "";
    $slc_padre = 0;
    $slc_archivo = 0;
    $slc_plantilla = 0;
}

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();
?>

<script language="JavaScript">


function ltrim(s) {
   return s.replace(/^\s+/, "");
}

/**
* Validación de campos obligatorios que se debe ingresar para creación de la dependencia.
* Campos obligatorios:
* Nombre y Siglas.
**/
function ValidarInformacion()
{	
	if (ltrim(document.form2.txt_nombre.value) == '')
	{
		alert('Ingrese el Nombre del área.');
		document.form2.txt_nombre.focus();
		return false;
	}
	if (ltrim(document.form2.txt_sigla.value) == '')
	{
		alert('Ingrese las Siglas para el área.');
		document.form2.txt_sigla.focus();
		return false;
	}
    if (ltrim(document.form2.txt_ciudad.value) == '0')
	{
		alert('Ingrese la Ciudad a la que pertence el área.');
		document.form2.txt_ciudad.focus();
		return false;
	}
	document.form2.txt_ok.value='1';
	document.form2.submit();
	return true;
}

/**
* Llamar a la página ../tbasicas/listados.php para desplegar listado.
**/
function ver_listado()
{
    var x = (screen.width - 900) / 2;
    var y = (screen.height - 540) / 2;
	preview = window.open('../tbasicas/listados.php?var=dpc&inst=<?=$_SESSION["inst_codi"]?>','', 'scrollbars=yes,menubar=no,height=540,width=900,resizable=yes,toolbar=no,location=no,status=no');
    preview.moveTo(x, y);
    preview.focus();
}

/**
* Permite escoger la plantilla en formato pdf, que se generará cuando se cree un documento
**/
function SeleccionarPlantilla()
{
	if (document.getElementById('slc_plantilla').value == <?=$slc_dependencia?> || document.getElementById('slc_plantilla').value == 0)
	{
		document.getElementById('tr_plantilla').style.display='';
	} else {
		document.getElementById('tr_plantilla').style.display='none';
	}
	return;
}

/**
* Valida la extensión de la plantilla
**/

function valida_extension()
{
    cadena=document.getElementById('arch_plantilla').value;
    cadena= cadena.substr(-3).toLowerCase();
    if (cadena!='pdf') {
	alert ('Solo se permite subir archivos con extensión "pdf".');
	document.getElementById('arch_plantilla').value = '';
    }
    return;
}

</script>
<body>
<center>
<form name="form1" id="form1" method="post" action="<?='adm_dependencias.php?accion='.$accion?>">
  <table width="80%" class="borde_tab">
    <tr>
      	<td colspan="2" height="40" align="center" class="titulos4">
	    <b>Administraci&oacute;n de <?=trim(($_SESSION["descDependencia"]))."s"?></b>
	</td>
    </tr>
<? if ($accion==2) { ?>
    <tr>
	<td width="30%" align="left" class="titulos2"><b>&nbsp;Seleccione el <?=$_SESSION["descDependencia"]?> que desea modificar</b></td>
	<td width="70%" class="listado2">
<?
	    $sql = "select depe_nomb, depe_codi from dependencia where depe_estado=1 and inst_codi=".$_SESSION["inst_codi"]." order by 1";
	    $rs=$db->conn->query($sql);
	    echo $rs->GetMenu2("slc_dependencia", $slc_dependencia, "0:&lt;&lt seleccione &gt;&gt;", false,"","class='select' Onchange='submit()'");
?>
	</td>
    </tr>
<? } ?>
  </table>
  <br/>
</form>

<? if ($accion==1 or $slc_dependencia!=0) { ?>
<form name="form2" id="form2" ENCTYPE="multipart/form-data" method="post" action="<?='adm_dependencias_grabar.php?accion='.$accion?>">
  <input type="hidden" name="txt_ok" id="txt_ok" value="" >
  <input type="hidden" name="txt_depe_codi" id="txt_depe_codi" value="<?=$txt_depe_codi?>" >
  <table width="80%" class="borde_tab">
    <tr>
	<td width="30%" align="left" class="titulos2"><b>* Nombre</b></td>
	<td width="70%" class="listado2_ver">
	    <input  name="txt_nombre" id="txt_nombre" type="text" size="50" maxlength="70" value="<?=$txt_nombre?>" > </td>
    </tr>
    <tr>
	<td class="titulos2"><b>* Sigla</b></td>
	<td class="listado2_ver"><input name="txt_sigla" id="txt_sigla" type="text" size="20" maxlength="20" value="<?=$txt_sigla ?>"></td>
    </tr>
    <tr>
	<td class="titulos2"><b>* Ciudad</b></td>
	<td class="listado2_ver"><?php
	    $sql1 = "select nombre, id from ciudad order by 1"; //selecciona las ciudades de la tabla ciudad, para llenar el combobox
	    $rs=$db->conn->query($sql1);
	    echo $rs->GetMenu2('txt_ciudad',$txt_ciudad,"0:&lt;&lt seleccione &gt;&gt;",false,"","Class='select'");
	?>
        </td>
    </tr>
    <tr>
	<td class="titulos2"><b><?=$_SESSION["descDependencia"]?> Padre</b></td>
	<td class="listado2_ver">
	<?php
		 //Llena el combo de dependencias
	    $sql = "select depe_nomb, depe_codi from dependencia where depe_estado=1 and inst_codi=".$_SESSION["inst_codi"]." order by 1";
	    $rs=$db->conn->query($sql);
	    echo $rs->GetMenu2('slc_padre',$slc_padre,'0:&lt;&lt Área Actual &gt;&gt;',false,false,'Class="select"');
	?>
	</td>
    </tr>
    <tr>
	<td class="titulos2"><b>Ubicaci&oacute;n del Archivo F&iacute;sico</b></td>
	<td class="listado2_ver">
	<?php
	    $sql = "select depe_nomb, depe_codi from dependencia where depe_estado=1 and (coalesce(dep_central,depe_codi)=depe_codi
		    or depe_codi=".$_SESSION["depe_codi"].") and inst_codi=".$_SESSION["inst_codi"]." order by 1";
	    $rs=$db->conn->query($sql);
	    echo $rs->GetMenu2('slc_archivo',$slc_archivo,'0:&lt;&lt Área Actual &gt;&gt;',false,false,'Class="select"');
	?>
	</td>
    </tr>
    <tr>
	<td class="titulos2"><b>Área de la que se copiar&aacute; la plantilla del documento</b></td>
	<td class="listado2_ver">
	<?php
	    $sql = "select depe_nomb, depe_codi from dependencia where depe_estado=1 and (coalesce(depe_plantilla,depe_codi)=depe_codi
		    or depe_codi=".$_SESSION["depe_codi"].") and inst_codi=".$_SESSION["inst_codi"]." order by 1";
	    $rs=$db->conn->query($sql);
	    echo $rs->GetMenu2('slc_plantilla',$slc_plantilla,'0:&lt;&lt Área Actual &gt;&gt;',false,false,
			       'Class="select" id="slc_plantilla" onChange="SeleccionarPlantilla()"');
	?>
	</td>
    </tr>
    <tr name="tr_plantilla" id="tr_plantilla">
	<td width="30%" align="left" class="titulos2"><b>Cargar Plantilla</b></td>
	<td width="70%" class="listado2">
<?
	if ($slc_plantilla==0 or $slc_plantilla==$slc_dependencia) {
	    if (is_file("$ruta_raiz/bodega/plantillas/$slc_dependencia.pdf")) {
		$path_descarga = "$ruta_raiz/archivo_descargar.php?path_arch=/plantillas/$slc_dependencia.pdf&nomb_arch=plantilla.pdf";
		echo "<b>Ya est&aacute; cargada una plantilla para el &aacute;rea.</b>&nbsp;&nbsp;&nbsp;";
		echo "<a href=\"javascript:window.open('$path_descarga','_self','');\" class='vinculos'>Ver Plantilla</a><br>";
	    } else
	    	echo "<b>Por favor cargue una plantilla para los documentos del &aacute;rea.</b><br>";
	}
?>
	    <input type="file" name="arch_plantilla" id="arch_plantilla" class="tex_area" onChange="valida_extension();" size="70">
            <br><b>La plantilla debe estar en formato &quot;pdf&quot; y su tama&ntilde;o no debe superar los 100 Kb.</b>
	</td>
    </tr>
  </table>
  <br/>
</form>
<script>SeleccionarPlantilla();</script>
<? } ?>	

  <table width="80%"cellpadding="0" cellspacing="0">
    <tr>
    	<td align="center">
	    <input name="btn_accion" type="button" class="botones" value="Listar Áreas" title="Lista todas las áreas que estan creadas en la institución" onClick="ver_listado();"/>
    	</td>
<? if ($accion==1 or $slc_dependencia!=0) { ?>
	<td align="center">
	    <input name="btn_accion" type="button" class="botones" value="Aceptar" title="Almacena los cambios realizados" onClick="return ValidarInformacion();"/>
	</td>
<? } ?>
	<td align="center">
	    <input  name="btn_accion" type="button" class="botones" value="Regresar" onClick="location='mnu_dependencias.php'" title="Regresa a la página anterior, sin guardar los cambios"/>
	</td>
    </tr>
  </table>
</center>
</body>
</html>
