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

////////////////	VARIABLES BASICAS	////////////////////////
  if (!$depe_actu) $depe_actu=0;
  $sql = "select arch_nombre from archivo_nivel where depe_codi=$depe_actu";
  $rs=$db->conn->query($sql);
  $niveles = 0;
  $titulo = "";
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
  <form method="post" name="formulario"> 
    <center>
    <table class="borde_tab" width="80%" cellspacing="5">
	<tr><td class=titulos2 colspan="2"><center>Consultar Estructura del Archivo F&iacute;sico</center></td></tr>
    	<tr>
	    <td width="25%" align="left" class="titulos2"><b>&nbsp;Seleccione <?=$descDependencia?></b></td>
	    <td width="75%" class="listado2">
<?
	$sql = "select DEPE_NOMB, DEPE_CODI from dependencia where coalesce(dep_central, depe_codi)=depe_codi 
		and depe_estado=1 and inst_codi=".$_SESSION["inst_codi"]." order by depe_nomb";
	$rs=$db->conn->query($sql);
	echo $rs->GetMenu2("depe_actu", $depe_actu, "0:&lt;&lt seleccione &gt;&gt;", false,"","class='select' Onchange='document.formulario.submit()'");
	$rs->Move(0);
?>
	</td>
    </tr>
    </table>
    <br>

    <table class="borde_tab" width="80%">
<?	if ($depe_actu==0) {?>
	    <tr><td class="titulos2" colspan="4"><center>Seleccione el <?=$descDependencia?> antes de continuar</center></td></tr>
<?	} else { 
	    if (trim($titulo)=="") { ?>
	    	<tr><td class="titulos2" colspan="4"><center>
		    Esta <?=$descDependencia?> no tiene definida la estructura del Archivo</center></td></tr>
<?	    } else { ?>
	    	<tr><td class="titulos2" colspan="4"><?=$titulo?></td></tr>
	    	<tr><td class="titulos2" width="70%">Nombre Item</td>
		    <td class="titulos2" width="15%">Estado</td>
		    <td class="titulos2" width="15%">Tipo</td>
	    	</tr>
	    	<tr><td  colspan="4">
		    <table width="100%">
			<?echo ArbolSeleccionarArchivo(0, 0 , $depe_actu, "", $db, $ruta_raiz,"L","T",0,0,"N");?>
		    </table></td>
	    	</tr>
<?	    } 
	}
?>

    </table>
    <br>

<?
////////////////////////	BOTONES 	/////////////////////////
?>
    <table width="80%" cellspacing="5">
	<tr>
    	    <td > <center>
    		<input type="button" name="btn_print" value="Imprimir" class="botones" onClick="window.print();">	  
    	    </center></td>
    	    <td > <center>
    		<input type="button" name="btn_cancelar" value="Regresar" class="botones" onClick="window.location='./menu_archivo.php';">
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

    </script>
  </center>
  </form>
  </body>
</html>



