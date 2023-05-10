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
/*if (!$_SESSION['permiso_archivar_documentos']) {
    $htm= "<br><br><table align='center' ><tr><td>No cuenta con los permisos necesarios para usar esta parte del sistema. </td></tr><tr><td>&nbsp;</td></tr><tr><td> Si tiene los permisos, comuníquese con su administrador del sistema </td></tr></table>";
    die($htm);
}*/
  $ruta_raiz = "..";
  include_once "$ruta_raiz/rec_session.php";
  if (isset ($replicacion) && $replicacion && $config_db_replica_trd_seleccionar_trd!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_trd_seleccionar_trd);
include_once "obtener_datos_trd.php";

////////////////	VARIABLES BASICAS	////////////////////////
  $verrad = $_GET['verrad'];    //se incluyo por register_globals
  if (!$verrad) echo "<script>window.close();</script>";

  if (!$mensaje) $mensaje="";

//  $sql = "select trd_nombre from trd_nivel where depe_codi=".$_SESSION['depe_codi']."";
//  $rs=$db->conn->query($sql);
//  //var_dump($sql);
//  $niveles = 0;
//  $titulo = "";
//  $tmp = strtolower($rs->fields["TRD_NOMBRE"]);
//  while (!$rs->EOF) {
//    if ($titulo!="") $titulo .= " >> ";
//    $titulo .= $rs->fields["TRD_NOMBRE"];
//    $niveles++;		//Numero de niveles de almacenamiento
//    $rs->MoveNext();
//  }

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

?>
  <style>a:link, a:visited, a:hover {color: blue;}</style>
  <body >
  <form method="post" name="formulario" action="./seleccionar_trd_grabar.php"> 
    <center>
    <table class="borde_tab" width="80%" cellspacing="5">
	<tr><td class=titulos2><center>SELECCIONAR <?=strtoupper($_SESSION["descTRD"])?></center></td></tr>
    </table>
    <br>
    <center><font color="red" face='Arial' size='3'><?=$mensaje?></font></center>

    <table class="borde_tab" width="80%">
	<tr><td class="titulos2" colspan="3"><?=$titulo?></td></tr>
	<tr><td class="titulos5" width="60%"><center>Nombre de Carpeta</center></td>
	    <td class="titulos5" width="15%"><center>Estado</center></td>
	    <td class="titulos5" width="15%"><center>Acci&oacute;n</center></td>
	</tr>
	<tr><td  colspan="3">
	    <table width="100%">
		<?
                    $lista = ConsultarCarpetaVirtual($db, $_SESSION["depe_codi"], 1);
                    ArmarArbolCarpetaVirtual($lista, 0, "..","S", "", "Seleccionar");
               ?>
	    </table></td>
	</tr>
    </table>
    <br>
<?
    $sql = "select t.trd_codi, d.depe_nomb from trd_radicado t, dependencia d where d.depe_codi=t.depe_codi and t.radi_nume_radi=$verrad";
    $rs=$db->conn->query($sql);
    $anterior = "";
    $verfila = "";
    $span = "";
    if (!$rs->EOF) {
    	$anterior = $rs->fields["TRD_CODI"];
	$span = ObtenerNombreCompletoTRD($anterior,$db);
    }else
    	$verfila = 'style="display:none"';

?>
    <table class="borde_tab" width="80%" cellspacing="5">
	<tr><td class="titulos2" colspan="3"><center><?=strtoupper($_SESSION["descTRD"])?> EN EL ÁREA ACTUAL</center></td></tr>
    	<tr>
	    <td width="25%" align="center" class="listado2">NUEVA UBICACI&Oacute;N</td>
	    <td width="50%" align="center" class="listado2"><span name='spn_trd' id='spn_trd'></td>
	    <td width="25%" rowspan="2" align="center" class="listado2"><center>
	    	<input type="hidden" name="txtCodigo" id="txtCodigo" value="">
	    	<input type="hidden" name="txtAnterior" id="txtAnterior" value="<?=$anterior?>">
	    	<input type="hidden" name="txtRadicado" id="txtRadicado" value="<?=$verrad?>">
   		<input type="button" name="btn_aceptar" id="btn_aceptar" value="Aceptar" class="botones" onClick="ValidarForm();" style="display:none">
	    </center></td>
	</tr>
    	<tr <?=$verfila?>>
	    <td width="25%" align="center" class="listado2">UBICACI&Oacute;N ANTERIOR</td>
	    <td width="50%" align="center" class="listado2"><?=$span?></td>
	</tr>

    </table>
    <br>

<?
////////////////////////	BOTONES 	/////////////////////////
?>
    <table class=borde_tab width="80%" cellspacing="5">
	<tr>
    	    <td class="titulos2"> <center>
    		<input type="button" name="btn_cancelar" value="Cancelar" class="botones" onClick="window.close();">	  
    	    </center></td>
	</tr>
    </table>

<script language="JavaScript" type="text/JavaScript">

	function ValidarForm() {
	    if (document.getElementById('txtAnterior').value != '') {
		if (!confirm('El documento pertenece a una Carpeta Virtual. Desea reemplazarla?'))
		    return;
	    }
	    document.formulario.submit();
	}
        
	function MostrarFila(fila, ruta_raiz){
            var elemento=document.getElementsByName(fila);
            imgAgregar = "agregar.png";
            imgQuitar = "quitar.png";

            for (var i=0; i<elemento.length; i++){

                if (elemento[i].style.display=='none')
                {
                    if(document.getElementById("spam_"+fila)!=null)
                       document.getElementById("spam_"+fila).innerHTML = '<img src='+ruta_raiz+'/imagenes/'+  imgQuitar +' border="0" height="15px" width="15px">';
                    elemento[i].style.display='';
                }
                else{
                   if(document.getElementById("spam_"+fila)!=null)
                        document.getElementById("spam_"+fila).innerHTML = '<img src='+ruta_raiz+'/imagenes/'+ imgAgregar  +' border="0" height="15px" width="15px">';
                   elemento[i].style.display='none';
                }
               MostrarFila(elemento[i].id, ruta_raiz);
            }
	}

	function SeleccionarTRD(codigo, nombre_completo) {           
	    document.getElementById('txtCodigo').value=codigo;
	    document.getElementById('btn_aceptar').style.display='';
	    document.getElementById('spn_trd').innerHTML = nombre_completo;
	}

    </script>
  </center>
  </form>
  </body>
</html>



