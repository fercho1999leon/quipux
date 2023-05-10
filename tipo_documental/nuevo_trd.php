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
$ruta_raiz = isset($ruta_raiz) ? $ruta_raiz : "..";
session_start();
$ruta_raiz = "..";
include_once "$ruta_raiz/rec_session.php";
if (isset ($replicacion) && $replicacion && $config_db_replica_trd_nuevo_trd!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_trd_nuevo_trd);
include_once "obtener_datos_trd.php";

////////////////	VARIABLES BASICAS	////////////////////////
if (!$mensaje) $mensaje="";
if (!$depe_actu) $depe_actu=0;

//$sql = "select trd_nombre from trd_nivel where depe_codi=$depe_actu";
//$rs=$db->conn->query($sql);
////var_dump($sql);
//$niveles = 0;
//$titulo = "";
//$span = "";
//$tmp = $rs->fields["TRD_NOMBRE"];
//while (!$rs->EOF) {
//    if ($titulo!="") $titulo .= " >> ";
//    $titulo .= $rs->fields["TRD_NOMBRE"];
//    $niveles++;		//Numero de niveles de almacenamiento
//    $rs->MoveNext();
//}

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

?>
<script type="text/javascript" src="<?=$ruta_raiz?>/js/formchek.js"></script>

<body >
  <form method="post" name="formulario" action="./nuevo_trd_grabar.php">
    <center>
    <table class="borde_tab" width="80%" cellspacing="5">
	<tr><td class=titulos2 colspan="2"><center>Administraci&oacute;n de <?=$descTRDpl?></center></td></tr>
    	<tr>
	    <td width="25%" align="left" class="titulos2"><b>&nbsp;Seleccione el <?=$descDependencia?>:</b></td>
	    <td width="75%" class="listado2">
<?
	$sql = "select DEPE_NOMB, DEPE_CODI from dependencia where depe_estado=1 and inst_codi=".$_SESSION["inst_codi"]." order by depe_nomb";
	$rs=$db->conn->query($sql);
	echo $rs->GetMenu2("depe_actu", $depe_actu, "0:&lt;&lt seleccione &gt;&gt;", false,"","class='select' Onchange='document.formulario.submit()'");
	$rs->Move(0);
?>
	</td>
    </tr>
    </table>
    <br>
    <center><font color="red" face='Arial' size='3'><?=$mensaje?></font></center>
    <table class="borde_tab" width="80%" cellspacing="5" name="tblCrear" id="tblCrear" style="display:none">
	<tr><td class="titulos2" colspan="4"><center><span name='spn_accion' id='spn_accion'></span></center></td></tr>
    	<tr>
	    <td colspan="2"  class="titulos2">Nombre</td>
	    <td width="25%" class="titulos2">Tiempo Archivo Gesti&oacute;n</td>
	    <td width="25%" class="titulos2">Tiempo Archivo Central</td>
	</tr>
    	<tr>
	    <td colspan="2" align="center" class="listado2"><center>
	    	<input type="hidden" name="txtOk" id="txtOk" value="">
	    	<input type="hidden" name="txtCodigo" id="txtCodigo" value="">
	    	<input type="hidden" name="txtPadre" id="txtPadre" value="">
	    	<input type="hidden" name="txtEstado" id="txtEstado" value="">
                <input type="hidden" name="txtNivel" id="txtNivel" value="">
	    	<input name="txtNombre" id="txtNombre" type="text" size="50" maxlength="40" value=""></center></td>
	    <td align="center" class="listado2"><center>
	    	<input name="txtArch1" id="txtArch1" type="text" size="10" maxlength="3" value="" onkeypress="return validar_numero(event)">
			&nbsp;años</center></td>
	    <td align="center" class="listado2"><center>
	    	<input name="txtArch2" id="txtArch2" type="text" size="10" maxlength="3" value="" onkeypress="return validar_numero(event)">
			&nbsp;años</center></td>
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
<?	if ($depe_actu==0) {?>
	    <tr><td class="titulos2" colspan="4"><center>Seleccione el <?=$descDependencia?> antes de continuar</center></td></tr>
<?	} else { ?>
	    	<!--tr><td class="titulos2" colspan="4"><?=$titulo?></td></tr-->
	    	<tr><td class="titulos2" width="60%"><center>Nombre de Carpeta</center></td>
		    <td class="titulos2" width="15%"><center>Estado</center></td>
		    <td class="titulos2" width="25%"><center>Acci&oacute;n</center></td>
	    	</tr>
	    	<tr><td  colspan="4">
		    <table width="100%">
                        <tr>
                            <td class="listado5" colspan="4" width="85%">&nbsp;</td>
			    <td class="listado5" colspan="2" align="center" width="15%"><a href="#" class="vinculos" onClick="CrearItem(0,-1,'')">Crear</a></td>
		    	</tr>
			<?                        
                        $lista = ConsultarCarpetaVirtual($db, $depe_actu, 0);
                        ArmarArbolCarpetaVirtual($lista, 0, "..","S", "", "Editar");
                        ?>
		    </table></td>
	    	</tr>
<?	    
	}
?>

    </table>
    <br>

<?
////////////////////////	BOTONES 	/////////////////////////
?>
    <table  width="80%" cellspacing="5">
	<tr>
    	    <td > <center>
    		<input type="button" name="btn_cancelar" value="Regresar" class="botones" onClick="window.location='./menu_trd.php';">
    	    </center></td>
	</tr>
    </table>

<script language="JavaScript" type="text/JavaScript">
	function ltrim(s) {
	   return s.replace(/^\s+/, "");
	}

	function ValidarForm() {
	    if (!isNonnegativeInteger(document.getElementById('txtArch1').value) || !isNonnegativeInteger(document.getElementById('txtArch2').value)) {
		alert ('El tiempo de permanencia de los documentos en los archivos debe ser un número');
		return;
	    }
	    if (document.getElementById('txtArch1').value < 5) {
		alert ('El tiempo mínimo de permanencia de los documentos en el archivo de gestión es de por lo menos 5 años');
		return;
	    }
	    if (document.getElementById('txtArch2').value < 15) {
		alert ('El tiempo mínimo de permanencia de los documentos en el archivo central es de por lo menos 15 años');
		return;
	    }
	    if (ltrim(document.getElementById('txtNombre').value)!='') {
		document.getElementById('txtOk').value = '1';
		document.formulario.submit();
	    } else
		alert('El nombre del item es obligatorio');
	}

	function validar_numero(e) {
		tecla = (document.all)?e.keyCode:e.which;
		if (tecla==8) return true;
		patron = /\d/;
		te = String.fromCharCode(tecla);
		return patron.test(te); 
	}

	function BotonesItem(dato, accion) {
	    if (confirm('Desea ' + accion + ' el Item Seleccionado?')) {
		document.getElementById('txtOk').value = dato;
		document.formulario.submit();
	    }
	}

	function MostrarFila(fila, ruta_raiz){
	    var elemento=document.getElementsByName(fila);
            imgAgregar = "agregar.png";
            imgQuitar = "quitar.png";

            for (var i=0; i<elemento.length; i++){

                if (elemento[i].style.display=='none')
                {
                    if(document.getElementById("spam_"+fila)!=null)
                       document.getElementById("spam_"+fila).innerHTML = '<img src='+ruta_raiz+'/imagenes/'+ imgQuitar  +' border="0" height="15px" width="15px">';
                    elemento[i].style.display='';
                }
                else{
                   if(document.getElementById("spam_"+fila)!=null)
                        document.getElementById("spam_"+fila).innerHTML = '<img src='+ruta_raiz+'/imagenes/'+ imgAgregar +' border="0" height="15px" width="15px">';
                   elemento[i].style.display='none';
                }
               MostrarFila(elemento[i].id, ruta_raiz);
            }
	}
	
	function EditarItem(codigo, nombre, tiempo1, tiempo2, estado, nivel, borrar, nombre_completo) {
	    document.getElementById('txtCodigo').value=codigo;
	    document.getElementById('txtPadre').value='';
	    document.getElementById('txtNombre').value=nombre;
	    document.getElementById('txtArch1').value=tiempo1;
	    document.getElementById('txtArch2').value=tiempo2;
            document.getElementById('txtEstado').value=estado;
            document.getElementById('txtNivel').value=nivel;           
	    document.getElementById('tblCrear').style.display='';           
	    if (borrar)
		document.getElementById('btn_borrar').style.display='';
	    else
		document.getElementById('btn_borrar').style.display='none';
	    document.getElementById('btn_activar').style.display='none';
	    document.getElementById('btn_desactivar').style.display='none';
	    if (estado == 0)
	    	document.getElementById('btn_activar').style.display='';
	    if (estado == 1)
	    	document.getElementById('btn_desactivar').style.display='';                      
	    document.getElementById('spn_accion').innerHTML = 'MODIFICAR ' + nombre_completo;
	}

	function CrearItem(codigo, nivel, nombre_completo) {
	    document.getElementById('txtCodigo').value='';
	    document.getElementById('txtPadre').value=codigo;
	    document.getElementById('txtNombre').value='';
	    document.getElementById('txtArch1').value='5';
	    document.getElementById('txtArch2').value='15';
            document.getElementById('txtNivel').value=nivel+1;           
	    document.getElementById('txtEstado').value='1';
	    document.getElementById('tblCrear').style.display='';
	    document.getElementById('btn_borrar').style.display='none';
	    document.getElementById('btn_activar').style.display='none';
	    document.getElementById('btn_desactivar').style.display='none';
	    document.getElementById('spn_accion').innerHTML = 'Crear ' + nombre_completo;
	}      
    </script>
  </center>
  </form>
  </body>
</html>