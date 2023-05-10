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
if($_SESSION["usua_admin_sistema"]!=1) die("");
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/obtenerdatos.php";
include_once "../usuarios_dependencias/refrescarArbol.php";

 

if (!isset($_GET['dependencia'])) 
    $slc_dependencia = 0;

$txt_depe_codi = 0+$_GET['dependencia'];


/**
* Llena los campos de las areas a editar
**/
$where_adscrita = "";
if (0+$_GET['dependencia'] != 0) {
    $sql = "select * from dependencia where depe_codi=".$txt_depe_codi;
    $rs = $db->conn->query($sql);
    $txt_nombre = $rs->fields['DEPE_NOMB'];
    $txt_sigla = $rs->fields['DEP_SIGLA'];
    $slc_padre = $rs->fields['DEPE_CODI_PADRE'];
    $txt_ciudad = $rs->fields['DEPE_PIE1']; //
    $slc_archivo = $rs->fields['DEP_CENTRAL'];
    $slc_plantilla = $rs->fields['DEPE_PLANTILLA'];
    $depe_estado = $rs->fields['DEPE_ESTADO'];
    $slc_dependencia = 0+$_GET['dependencia'];
    if (trim($rs->fields['INST_ADSCRITA']) != "") $where_adscrita = " and inst_adscrita=".trim($rs->fields['INST_ADSCRITA']);
}
else {
    $txt_nombre = "";
    $txt_ciudad = "";
    $txt_sigla = "";
    $slc_padre = 0+$_GET['padre'];
    $slc_archivo = 0;
    $slc_plantilla = 0;
}

//include_once "$ruta_raiz/funciones_interfaz.php";
//echo "<html>".html_head();
if($accion == 1)
    $titulo = 'Nueva &Aacute;rea';
else
    $titulo = 'Editar &Aacute;rea';
?>

<center>
    <table width="100%" class="borde_tab">        
        <tr>            
            <td align="left" class="titulos2">
              Nombre, Sigla: <input  name="txt_nombre_buscar" id="txt_nombre_buscar" type="text" size="40" maxlength="150" value="<?=$txt_nombre_buscar?>" onKeyPress="if (event.keyCode==13) return buscarArea();">  
            </td>
            <td class="listado2_ver">               
                    <input  name="btn_accion" type="button" class="botones" value="Buscar" onClick="return buscarArea();" title="Busca Área por nombre o sigla"/>
            </td>          
      </tr>
      </table>
           <div id="div_busqueda_area"></div>
<form name="form2" id="form2" ENCTYPE="multipart/form-data" method="post" action="<?='adm_dependencias_grabar.php?accion='.$accion?>">
<? if ($accion==1 or $slc_dependencia!=0) { ?>

  <input type="hidden" name="txt_ok" id="txt_ok" value="" >
  <input type="hidden" name="txt_estado" id="txt_estado" value="<?=$depe_estado?>"/>
  <?php 

    $editar_area = obtenerCodigos($_SESSION['usua_codi'],$txt_depe_codi,$db);
   
  ?>
  <input type="hidden" name="txt_depe_codi" id="txt_depe_codi" value="<?=$txt_depe_codi?>" />
  <table width="100%" class="borde_tab">      
      <tr><td align="center" class="titulos4" colspan="2"><font size="2"><?=$titulo?></font></td></tr>
    <tr>
	<td width="30%" align="left" class="titulos2"><b>* Nombre</b></td>
	<td width="70%" class="listado2_ver">
	    <input  name="txt_nombre" id="txt_nombre" type="text" size="65" maxlength="150" value="<?=$txt_nombre?>" onChange="buscar_area_ajax(<?=$_SESSION["inst_codi"];?>,this.value)">
            <input  name="hidden_nombre" id="hidden_nombre" type="hidden" size="65" maxlength="150" value="<?=$txt_nombre?>">
        <div id="div_nombre"></div>
        </td>        
    </tr>
    <tr>
	<td class="titulos2"><b>* Sigla</b></td>
	<td class="listado2_ver">
            <input name="txt_sigla" id="txt_sigla" type="text" size="20" maxlength="20" value="<?=$txt_sigla ?>" onChange="buscar_sigla_ajax(<?=$_SESSION["inst_codi"];?>,this.value)" onkeypress = "return pulsar_espacio(event)">
            <input name="hidden_sigla" id="hidden_sigla" type="hidden" size="20" maxlength="20" value="<?=$txt_sigla ?>">
        <div id="div_nombre1"></div>
        </td>
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
        if ($txt_depe_codi!='')
            $sub_area = substr(buscar_areas_dependientes_rec($txt_depe_codi),1);            
            //Llena el combo de dependencias
	    $sql = "select depe_nomb, depe_codi from dependencia where depe_estado=1 $where_adscrita and inst_codi=".$_SESSION["inst_codi"];
            if (trim($sub_area)!='')
	    $sql.= " and depe_codi not in (".substr(buscar_areas_dependientes_rec($txt_depe_codi),1).")";
            $sql.= " order by 1"; 

            $rs=$db->conn->query($sql);
	    echo $rs->GetMenu2('slc_padre',$slc_padre,'0:&lt;&lt Área Actual &gt;&gt;',false,false,'Class="select"');
	?>
	</td>
    </tr>
    <tr>
	<td class="titulos2"><b>Ubicaci&oacute;n del Archivo F&iacute;sico</b></td>
	<td class="listado2_ver">
	<?php
	    $sql = "select depe_nomb, depe_codi from dependencia where depe_estado=1 $where_adscrita and (coalesce(dep_central,depe_codi)=depe_codi
		    or depe_codi=".$_SESSION["depe_codi"].") and inst_codi=".$_SESSION["inst_codi"]." order by 1";            
	    $rs=$db->conn->query($sql);
	    echo $rs->GetMenu2('slc_archivo',$slc_archivo,'0:&lt;&lt Área Actual &gt;&gt;',false,false,'Class="select"');
	?>
	</td>
    </tr>
    <tr>
	<td class="titulos2"><b>&Aacute;rea de la que se copiar&aacute; la plantilla del documento</b></td>
	<td class="listado2_ver">
	<?php
	    $sql = "select depe_nomb, depe_codi from dependencia where depe_estado=1 $where_adscrita and (coalesce(depe_plantilla,depe_codi)=depe_codi
		    or depe_codi=".$_SESSION["depe_codi"].") and inst_codi=".$_SESSION["inst_codi"]." order by 1";
	    $rs=$db->conn->query($sql);
	    echo $rs->GetMenu2('slc_plantilla',$slc_plantilla,'0:&lt;&lt Área Actual &gt;&gt;',false,false,
			       'Class="select" id="slc_plantilla" onChange="SeleccionarPlantilla(\''.$_GET['dependencia'].'\')"');
	?>
	</td>
    </tr>
    <tr name="tr_plantilla" id="tr_plantilla">
	<td width="30%" align="left" class="titulos2"><b>Cargar Plantilla</b></td>
	<td width="70%" class="listado2_ver">
<?
	if ($slc_plantilla==0 or $slc_plantilla==$slc_dependencia) {
	    if (is_file("$ruta_raiz/bodega/plantillas/$slc_dependencia.pdf")) {
		$path_descarga = "$ruta_raiz/archivo_descargar.php?path_arch=/plantillas/$slc_dependencia.pdf&nomb_arch=plantilla.pdf";
		echo "<b>Ya est&aacute; cargada una plantilla para el &aacute;rea.</b>&nbsp;&nbsp;&nbsp;";
		echo "<a href=\"javascript:window.open('$path_descarga','_self','');\" class='vinculos'>Ver Plantilla</a>";
                echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"javascript:validar_plantilla('$slc_dependencia');\" class='vinculos'>Validar Plantilla</a><br>";
	    } else
	    	echo "<b>Por favor cargue una plantilla para los documentos del &aacute;rea.</b><br>";
	}
?>
	    <input type="file" name="arch_plantilla" id="arch_plantilla" class="tex_area" onChange="valida_extension();" size="70">
	    <br><b>La plantilla debe estar en formato &quot;pdf&quot; y su tama&ntilde;o no debe superar los 100 Kb.</b>
	</td>
    </tr>
  </table>
<script>SeleccionarPlantilla("<?=$_GET['dependencia']?>");</script>
<? } else { ?>
  <table width="100%" class="borde_tab">
    <tr><td align="center" class="titulos4" colspan="2"><font size="2"><?=$titulo?></font></td></tr>
  </table>
<? } ?>
  <table width="100%"cellpadding="0" cellspacing="0" class="borde_tab">
    <tr>
    	<? 
        
        if ($accion==1 or $slc_dependencia!=0) { ?>
            <td align="center" class="listado2_ver" >
                <?php 
               
                    if($accion==1)
                      echo '<input name="btn_accion" type="button" class="botones" value="Aceptar" title="Almacena los cambios realizados" onmouseover="validarRepetidos();" onClick="return ValidarInformacion();"/>';
                    else{
                      
                          if (trim($editar_area)==1 || $_SESSION['usua_codi']==0 || $_SESSION['perm_admin_institucional']==1)       
                            echo '<input name="btn_accion" type="button" class="botones" value="Aceptar" title="Almacena los cambios realizados" onmouseover="validarRepetidos();" onClick="return ValidarInformacion();"/>';
                    }
            ?></td>
        <div id="info_area_eliminar"></div>
        
       <?php } ?>
        <td align="center" class="listado2_ver">
            <input  name="btn_accion" type="button" class="botones" value="Regresar" onClick="location='mnu_dependencias.php'" title="Regresa a la página anterior, sin guardar los cambios"/>
        </td>
    </tr>
  </table>
</form>
<iframe  name="ifr_descargar_plantilla" id="ifr_descargar_plantilla" style="display: none;" src="">
            Su navegador no soporta iframes, por favor actualicelo.</iframe>
</center>