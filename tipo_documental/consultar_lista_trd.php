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
include_once "$ruta_raiz/rec_session.php";
if (isset ($replicacion) && $replicacion && $config_db_replica_trd_consultar_lista_trd!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_trd_consultar_lista_trd);
include_once "obtener_datos_trd.php";

////////////////	VARIABLES BASICAS	////////////////////////
  $flag_botones = true;
  $tamano_tabla = "80%";
  if (isset($mostrar_botones) && $mostrar_botones == "no") {
      $flag_botones = false;
      $tamano_tabla = "99%";
  }
  if (!$depe_actu) $depe_actu=0;
//
//  $sql = "select trd_nombre from trd_nivel where depe_codi=$depe_actu";
//  $rs=$db->conn->query($sql);
//  $niveles = 0;
//  $titulo = "";
//  while (!$rs->EOF) {
//    if ($titulo!="") $titulo .= " >> ";
//    $titulo .= $rs->fields["TRD_NOMBRE"];
//    $niveles++;		//Numero de niveles de almacenamiento
//    $rs->MoveNext();
//  }

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

?>
<body>
  <form method="post" name="formulario" action="">
    <center>
    <br>
<?
////////////////////////	COMBO DE AREAS  	/////////////////////////
if ($flag_botones) {
?>

    <table class="borde_tab" width="<?=$tamano_tabla?>" cellspacing="5">
	<tr><td class=titulos2 colspan="2"><center>Consulta de <?=($descTRDpl)?></center></td></tr>
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
<? } //Combo areas?>
    
    <table class="borde_tab" width="<?=$tamano_tabla?>">
<?	if ($depe_actu==0) {?>
	    <tr><td class="titulos2" colspan="4"><center>Seleccione el <?=$descDependencia?> antes de continuar</center></td></tr>
<?	} else {  ?>
	    	<!--tr><td class="titulos2" colspan="4"><?=$titulo?></td></tr-->
	    	<tr><td class="titulos2" width="60%">Nombre de Carpeta</td>
		    <td class="titulos2" width="15%" align="center">Estado</td>
		    <td class="titulos2" width="15%" align="center">Acción</td>
	    	</tr>
	    	<tr><td  colspan="4">
		    <table width="100%">
			<?                            
                            $lista = ConsultarCarpetaVirtual($db, $depe_actu, 1);
                            ArmarArbolCarpetaVirtual($lista, 0, "..","S", "", "Consultar");
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
if ($flag_botones) {
?>
    <table width="80%" cellspacing="5">
	<tr>
    	    <td > <center>
    		<input type="button" name="btn_print" value="Imprimir" class="botones" onClick="window.print();">	  
    	    </center></td>
    	    <td > <center>
    		<input type="button" name="btn_cancelar" value="Regresar" class="botones" onClick="window.location='./menu_trd.php';">
    	    </center></td>
	</tr>
    </table>
<? } ?>
    <script type="text/javascript">
    function MostrarFila(fila, ruta_raiz){
        var elemento=document.getElementsByName(fila);
        imgAgregar = "agregar.png";
        imgQuitar = "quitar.png";

        for (var i=0; i<elemento.length; i++){

            if (elemento[i].style.display=='none')
            {
                if(document.getElementById("spam_"+fila)!=null)
                   document.getElementById("spam_"+fila).innerHTML = '<img src='+ruta_raiz+'/imagenes/'+ imgQuitar +' border="0" height="15px" width="15px">';
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
    </script>
  </center>
  </form>
  </body>
</html>