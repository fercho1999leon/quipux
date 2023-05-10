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
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

  session_start();
  $ruta_raiz = "..";
  include_once "$ruta_raiz/rec_session.php";
  include_once "obtener_datos_archivo.php";

////////////////	VARIABLES BASICAS	////////////////////////
  $sql = "select coalesce(dep_central,depe_codi) as archivo from dependencia where depe_codi=".$_SESSION['depe_codi']."";
  $rs=$db->conn->query($sql);
  $depe_archivo = $rs->fields["ARCHIVO"];

  if (!$mensaje) $mensaje="";
  $sql = "select arch_nombre from archivo_nivel where depe_codi=$depe_archivo";
  $rs=$db->conn->query($sql);
//var_dump($sql);
  $niveles = 0;
  $titulo = "";
  $span = "";
  $tmp = $rs->fields["ARCH_NOMBRE"];
  while (!$rs->EOF) {
    if ($titulo!="") $titulo .= " >> ";
    $titulo .= $rs->fields["ARCH_NOMBRE"];
    $niveles++;		//Numero de niveles de almacenamiento
    $rs->MoveNext();
  }


include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

?>

  <body >
  <form method="post" name="formulario"action="./nueva_ubicacion_fisica_grabar.php"> 
    <center>
    <table class="borde_tab" width="80%" cellspacing="5">
	<tr><td class=titulos2><center>Nueva Ubicaci&oacute;n F&iacute;sica</center></td></tr>
    </table>
    <br>
    <center><font color="red" face='Arial' size='3'><?=$mensaje?></font></center>
    <table class="borde_tab" width="80%" cellspacing="5" name="tblCrear" id="tblCrear" style="display:none">
	<tr><td class="titulos2" colspan="4"><center><span name='spn_accion' id='spn_accion'></span></center></td></tr>
    	<tr>
	    <td colspan="2" align="center" class="titulos2">Nombre Item</td>
	    <td colspan="2" align="center" class="titulos2">Sigla</td>
	</tr>
    	<tr>
	    <td align="center" class="listado2" colspan="2"><center>
	    	<input type="hidden" name="txtOk" id="txtOk" value="">
	    	<input type="hidden" name="txtCodigo" id="txtCodigo" value="">
	    	<input type="hidden" name="txtPadre" id="txtPadre" value="">
	    	<input type="hidden" name="txtEstado" id="txtEstado" value="">
	    	<input name="txtNombre" id="txtNombre" type="text" size="50" maxlength="40" value=""></center></td>
	    <td align="center" class="listado2" colspan="2"><center>
	    	<input name="txtSigla" id="txtSigla" type="text" size="10" maxlength="6" value=""></center></td>
    	</tr>
    	<tr>
	    <td width="25%" align="center" class="listado2"><center>Acciones</center></td>
	    <td width="25%" align="center" class="listado2"><center>
   		<input type="button" name="btn_guardar" value="Guardar" class="botones" onClick="ValidarForm();">
	    </center></td>
	    <td width="25%" align="center" class="listado2"><center>
   		<input type="button" name="btn_activar" id="btn_activar" value="Activar" class="botones" onClick="BotonesItem(3,'Activar');" style="display:none">
   		<input type="button" name="btn_desactivar" id="btn_desactivar" value="Desactivar" class="botones" onClick="BotonesItem(4,'Desactivar');" style="display:none">
	    </center></td>
	    <td width="25%" align="center" class="listado2"><center>
   		<input type="button" name="btn_borrar" id="btn_borrar" value="Borrar" class="botones" onClick="BotonesItem(2,'Borrar');" style="display:none">
	    </center></td>
    	</tr>
    </table>
    <br>

    <table class="borde_tab" width="80%">
<?	if (trim($titulo)=="") { ?>
	    <tr><td class="titulos2" colspan="4">Defina la organizaci&oacute;n f&iacute;sica del Archivo antes de continuar</td></tr>
<?	} else { ?>
	    <tr><td class="titulos2" colspan="4"><?=$titulo?></td></tr>
	    <tr><td class="titulos2" width="56%">Nombre Item</td>
	    	<td class="titulos2" width="10%">Sigla</td>
	    	<td class="titulos2" width="10%">Tipo</td>
	    	<td class="titulos2" width="22%">Acci&oacute;n</td>
	    </tr>
	    <tr><td  colspan="4">
	    	<table width="100%">
		    <tr><td class="listado2" colspan="5">&nbsp;</td>
	    		<td class="listado2"><a class='grid' href="#" onClick="CrearItem(0,-1,'<?=$tmp?>','')">Crear <?=strtolower($tmp)?></a></td>
		    </tr>
			<?echo ArbolModificarArchivo(0, $niveles,0 , $depe_archivo, "", $db);?>
	    	</table></td>
	    </tr>
<?	} ?>
    </table>
    <br>

<?
////////////////////////	BOTONES 	/////////////////////////
?>
    <table  width="80%" cellspacing="5">
	<tr>
    	    <td > <center>
    		<input type="button" name="btn_archivo" value="Regresar" class="botones" onClick="window.location='./menu_archivo.php';">
    	    </center></td>
	</tr>
    </table>

    <script>
	function ValidarForm() {
	    if (document.getElementById('txtNombre').value!='' && document.getElementById('txtSigla').value!='') {
		document.getElementById('txtOk').value = '1';
		document.formulario.submit();
	    } else
		alert('El nombre y la sigla del item son obligatorios');
	}

	function BotonesItem(dato, accion) {
	    if (confirm('Desea ' + accion + ' el Item Seleccionado?')) {
		document.getElementById('txtOk').value = dato;
		document.formulario.submit();
	    }
	}

	function MostrarFila(fila) {
	    if (document.getElementById(fila).style.display=='none') 
		document.getElementById(fila).style.display='';
	    else
		document.getElementById(fila).style.display='none';
	}

	function EditarItem(codigo, nombre, sigla, nivel, nom_nivel, estado, borrar, descripcion) {
	    document.getElementById('txtCodigo').value=codigo;
	    document.getElementById('txtPadre').value='';
	    document.getElementById('txtNombre').value=nombre;
	    document.getElementById('txtSigla').value=sigla;
	    document.getElementById('tblCrear').style.display='';
	    document.getElementById('txtEstado').value=estado.toString();
	    document.getElementById('spn_accion').innerHTML = 'MODIFICAR ' + nom_nivel + descripcion;



	    document.getElementById('tblCrear').style.display='';
	    if (borrar == 0)
		document.getElementById('btn_borrar').style.display='';
	    else
		document.getElementById('btn_borrar').style.display='none';
	    document.getElementById('btn_activar').style.display='none';
	    document.getElementById('btn_desactivar').style.display='none';
	    if (estado == 0) 
	    	document.getElementById('btn_activar').style.display='';
	    if (estado == 1) 
	    	document.getElementById('btn_desactivar').style.display='';

	}

	function CrearItem(codigo, nivel, nom_nivel, descripcion) {
	    document.getElementById('txtCodigo').value='';
	    document.getElementById('txtPadre').value=codigo;
	    document.getElementById('txtNombre').value='';
	    document.getElementById('txtSigla').value='';
	    if (nivel == <?=$niveles-2?>)
	    	document.getElementById('txtEstado').value='1';
	    else
	    	document.getElementById('txtEstado').value='0';
	    document.getElementById('tblCrear').style.display='';
	    document.getElementById('btn_borrar').style.display='none';
	    document.getElementById('btn_activar').style.display='none';
	    document.getElementById('btn_desactivar').style.display='none';
	    document.getElementById('spn_accion').innerHTML = 'CREAR ' + nom_nivel + descripcion;
	}
    </script>
  </center>
  </form>
  </body>
</html>



