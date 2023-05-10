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
include_once "$ruta_raiz/rec_session.php";
include_once "obtener_datos_archivo.php";
include_once "$ruta_raiz/obtenerdatos.php";

////////////////	VARIABLES BASICAS	////////////////////////
  $sql = "select coalesce(dep_central,depe_codi) as archivo from dependencia where depe_codi=".$_SESSION['depe_codi']."";
  $rs=$db->conn->query($sql);
  $depe_archivo = $rs->fields["ARCHIVO"];

  $radicados = "";
  foreach($_POST["checkValue"] as $num => $tmp)
	$radicados .= $num.",";
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


include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

?>

  <body >
  <form method="post" name="formulario" action="./incluir_doc_archivo_grabar.php"> 
    <center>
    <table class="borde_tab" width="80%" cellspacing="5">
	<tr><td class=titulos2>Seleccionar archivo  f&iacute;sico en donde se archivar&aacute; los Documentos</td></tr>
    </table>
    <br>

    <table class="borde_tab" width="80%">
	<tr><td class="titulos2" colspan="3"><?=$titulo?></td></tr>
	<tr><td class="titulos4" width="70%">Nombre Item</td>
	    <td class="titulos4" width="15%">Tipo</td>
	    <td class="titulos4" width="15%">Acci&oacute;n</td>
	</tr>
	<tr><td  colspan="3">
	    <table width="100%">
		<?echo ArbolSeleccionarArchivo(0, 0 , $depe_archivo, "", $db, $ruta_raiz,"S","E",1);?>
	    </table></td>
	</tr>
    </table>
    <br>

    <table class="borde_tab" width="80%" cellspacing="5"  id='tb_archivo' style="display:none">
	<tr><td class="titulos2" colspan="3">Archivar Documentos en el archivo f&iacute;sico</td></tr>
    	<tr>
	    <td width="25%" align="center" class="listado2">Nueva ubicaci&oacute;n</td>
	    <td width="50%" align="center" class="listado2"><span name='spn_archivo' id='spn_archivo'></span></td>
	    <td width="25%" rowspan="2" align="center" class="listado2"><center>
	    	<input type="hidden" name="txt_codigo" id="txt_codigo" value="">
	    	<input type="hidden" name="txt_radicado" id="txt_radicado" value="<?=$radicados?>">
	    	<input type="hidden" name="txt_anterior" id="txt_anterior" value="<?=$anterior?>">
   		<input type="button" name="btn_aceptar" id="btn_aceptar" value="Archivar" title="Incluye los documentos en el archivo fìsico" class="botones" onClick="ValidarForm();" style="display:none">
	    </center></td>
	</tr>

    </table>
    <br>

<?
////////////////////////	BOTONES 	/////////////////////////
?>
    <table width="80%" cellspacing="5">
	<tr>
    	    <td > <center>
    		<input type="button" name="btn_cancelar" value="Regresar" class="botones" title="Regresa a la pantalla anterior" onClick="window.location='incluir_doc_archivo_cuerpo.php';">
    	    </center></td>
	</tr>
    </table>

    <script>

	function ValidarForm() {
	    if (document.getElementById('txt_anterior').value != '') {
		if (!confirm('El documento pertenece a una Carpeta Virtual. Desea reemplazarla?'))
		    return;
	    }
	    document.formulario.submit();
	}


	function MostrarFila(fila) {
	    if (document.getElementById(fila).style.display=='none') 
		document.getElementById(fila).style.display='';
	    else
		document.getElementById(fila).style.display='none';
	}

	function SeleccionarArchivo(codigo, nombre, nivel, nom_nivel, descripcion) {
        document.getElementById('tb_archivo').style.display='';
	    document.getElementById('txt_codigo').value=codigo;
	    document.getElementById('btn_aceptar').style.display='';
	    document.getElementById('spn_archivo').innerHTML = descripcion;
	}

    </script>
  </center>
  </form>
  </body>
</html>



