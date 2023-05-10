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
/*************************************************************************************
** Permite crear los metadatos para la institución y/o áreas                        **
**                                                                                  **
** Desarrollado por:                                                                **
**      Lorena Torres J.                                                            **
*************************************************************************************/

$ruta_raiz = isset($ruta_raiz) ? $ruta_raiz : "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
if (isset ($replicacion) && $replicacion && $config_db_replica_trd_lista_expediente!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_trd_lista_expediente);

include_once "$ruta_raiz/metadatos/metadatos_funciones.php";
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/obtenerdatos.php";
include_once "$ruta_raiz/seguridad_documentos.php";

$mensaje_error = "";
$whereFiltro= "0";

$radi_nume = trim(limpiar_sql($_GET['verrad']));
$radi_nume_text = trim(limpiar_sql($_GET['textrad']));
$txt_tipo_ventana= trim(limpiar_sql($_GET["tipo_ventana"]));

/**
* Filtro de datos según documentos seleccionados
*/
if(isset($radi_nume)) {
    if (trim($radi_nume)!="") {
        $flag = validar_transacciones($codTx, $radi_nume, $db);
        if ($flag == "")
            $whereFiltro = $radi_nume;
        else
            $mensaje_error .= $flag;
    }   
    if ($mensaje_error != "") 
        $mensaje_error = "<br><center><span style='color: red; font-weight: bold;'>Existieron inconvenientes al realizar esta acci&oacute;n con los siguientes documentos:<br><br></span></center>" . $mensaje_error . "<br>";
} else    //Si no se escogio ningún radicado
    $mensaje_error .= "<br><center><span style='color: red; font-weight: bold;'>No hay documentos seleccionados.</span></center><br>";

//Consulta de metadato asociado al documento si existe
$datos = ConsultarMetadatosRadi($db, $radi_nume);

echo "<html>".html_head();

?>

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
    
    function SeleccionarMET(codigo, nombre_completo) {         
	     
            var listaMetadatos = "";
            nombre_completo = trim(nombre_completo);

            //Se valida si el texto ya fue seleccionado
            var txtlistaMetadatos = trim(document.getElementById('txtListaMetadatos').value);            
            if(txtlistaMetadatos.indexOf(nombre_completo)!=-1)
                return

            //Códigos seleccionados
            document.getElementById('txtMetCodigo').value = codigo;
            if(document.getElementById('txtListaCodMetadatos').value == "")
                document.getElementById('txtListaCodMetadatos').value = codigo;
            else
                document.getElementById('txtListaCodMetadatos').value = document.getElementById('txtListaCodMetadatos').value + ", "+codigo;

            //Metadatos seleccionados
            var texto = trim(document.getElementById('txtTexto').value);
            if(txtlistaMetadatos == "")                
                listaMetadatos = nombre_completo;
            else
                listaMetadatos = txtlistaMetadatos + ", " + nombre_completo;

            document.getElementById('txtListaMetadatos').value = listaMetadatos;            

            //Texto escrito por el usuario
            if(texto == "")
                document.getElementById('txtMetadatosTexto').value = listaMetadatos;         
            else
                document.getElementById('txtMetadatosTexto').value = listaMetadatos + ", " + texto;         
	}
    
    function SeleccionarTextoMET() { 
        var texto = trim(document.getElementById('txtTexto').value);
        var listaMetadatos = document.getElementById('txtListaMetadatos').value;
        if(listaMetadatos == "")
            document.getElementById('txtMetadatosTexto').value = texto;         
        else
            document.getElementById('txtMetadatosTexto').value = listaMetadatos + ", " + texto;
    }
    
    function CargarMET() {           
	   
            var listaMetadatos = "";
            var txtlistaMetadatos = trim(document.getElementById('txtListaMetadatos').value);
            var texto = trim(document.getElementById('txtTexto').value);
            nombre_completo = trim(nombre_completo);
            
            if(txtlistaMetadatos == "")                
                listaMetadatos = nombre_completo;
            else
                listaMetadatos = txtlistaMetadatos + ", " + nombre_completo;
            
            document.getElementById('txtListaMetadatos').value = listaMetadatos;            
            
            if(texto == "")
                document.getElementById('txtMetadatosTexto').value = listaMetadatos;         
            else
                document.getElementById('txtMetadatosTexto').value = listaMetadatos + ", " + texto;         
	}
        
     function BorrarTexto(){
         if(confirm("¿Está seguro que desea borrar el contenido del texto y metadato?")){
            document.getElementById('txtTexto').value = "";
            document.getElementById('txtMetadatosTexto').value = "";
            document.getElementById('txtMetCodigo').value = "";
            document.getElementById('txtListaMetadatos').value = "";
            document.getElementById('txtListaCodMetadatos').value = "";
         }
     }
     
     function VerificarChk() {
        for(i=0;i<document.frmMetadatos.elements.length;i++) {
            if(document.frmMetadatos.elements[i].checked==1 )
                return true;
        }
        return false;
     }
     
    function MetodoGuardar(accion){
        
        //Se validan datos de formulario
        var mensaje ="";
        if(document.getElementById('txtRadiCodi').value == "")
            mensaje = "No existe documento seleccionado.";
        if(document.getElementById('txtMetCodigo').value == "")
            mensaje = "No ha seleccionado el metadato.";

        if(mensaje != ""){
            alert (mensaje);
            return false;            
        }

        //Se guardan datos
        document.getElementById('txtAccion').value = accion;
        var respuesta = true;
        if(accion == 2)
            respuesta = confirm("¿Está seguro que desea eliminar el metadato para el documento?");
        
        if(respuesta == true)
            document.frmMetadatos.submit();
     }
     
    function metodoCerrar()
    {     
      if ('<?=$txt_tipo_ventana?>' == "popup") {
        window.close();
      }
      else{
          history.back();        
      }
   }
   
</script>

<style>a:link, a:visited, a:hover {color: blue;}</style>
<body >
  <form method="post" name="frmMetadatos" action="<? echo $ruta_raiz;?>/metadatos/metadatos_radi_grabar.php">
      <input type="hidden" id="txtMetRadiCodi" name=txtMetRadiCodi value="<? echo $datos["met_radi_codi"];?>">
      <input type="hidden" id="txtRadiCodi" name=txtRadiCodi value="<? echo $radi_nume;?>">
      <input type="hidden" id="txtMetCodigo" name=txtMetCodigo value="<? echo $datos["met_codi"];?>">      
      <input type="hidden" id="txtListaMetadatos" name=txtListaMetadatos value="<? echo $datos["metadato"];?>">
      <input type="hidden" id="txtListaCodMetadatos" name=txtListaCodMetadatos value="<? echo $datos["metadato_codi"];?>">
      <input type="hidden" id="txtAccion" name=txtAccion value="">
    <center>
    <br>
    <?
    //Si hay algun error, se muestra mensaje donde se indica que no se puede archivar el(los) radicado(s)
    if ($mensaje_error != "" )
        echo ("<table class='borde_tab' width='100%' cellspacing=0>
               <tr class='listado2'><td width='100%'>$mensaje_error</td></tr>
               <tr><td width='100%' align='center'><input type='button' value='Regresar' onClick='history.back();' name='enviardoc' class='botones' id='Cancelar'></td></tr>               
               </table></center>");
    else{ ?>
    <table width="100%" border="0" cellpadding="0" cellspacing="5" class="borde_tab"> 
       <tr class='titulos4' align="left"><td>Definición de Metadatos del Documento No.: <? echo $radi_nume_text; ?></td></tr>
       <tr><td>       
        <table width='100%' border='0' cellspacing='1' class='borde_tab_blanco'> 
            <br>
            <tr><td></td></tr>      
            <tr>            
                <td width="45%" valign="top">
                    <table class="borde_tab" width="95%">
                        <tr>
                            <td class="titulos3" colspan="4" align="center">Lista de Metadatos</td>
                        </tr>                    
                        <tr><td  colspan="4">
                            <table width="100%">
                                <?         
                                    //Consulta por área actual
                                    $depe_actu=$_SESSION["depe_codi"];
                                    $lista = ConsultarMetadatos($db, $depe_actu, 1);
                                    //Si no hay datos por área, consulta por Institución
                                    if(sizeof($lista)== 0)
                                    {
                                        $depe_actu=0;
                                        $lista = ConsultarMetadatos($db, $depe_actu, 1);
                                    }
                                    ArmarArbolMetadatos($lista, 0, $ruta_raiz,"N", "", "Seleccionar");
                                ?>
                            </table></td>
                        </tr>
                    </table>
                </td>
                <td width="55%" valign="top">
                    <table class="borde_tab" width="90%">
                        <tr>
                            <td class="titulos3" colspan="4" align="center">Metadatos del Documento</td>
                        </tr> 
                        <tr>
                            <td class="titulos2">Texto adicional para metadato:</td> 
                        </tr>
                            <td>                           
                                <textarea id="txtTexto" name=txtTexto cols=60 rows=4 class=ecajasfecha onkeyup="SeleccionarTextoMET();" maxlength="250"><? echo $datos["texto"];?></textarea>   
                            </td>                          
                        </tr>
                        <tr>
                            <td class="titulos2">Metadatos:</td> 
                        </tr>
                        <tr >
                            <td  align="right">
                                <textarea id="txtMetadatosTexto" name=txtMetadatosTexto cols=60 rows=7 class=ecajasfecha readonly style="background-color: lightgray;"><? echo $datos["metadato_texto"];?></textarea>   
                                <input type='button' value='Borrar texto' onClick='BorrarTexto();' name='btnBorrar' class='botones' id='Borrar'>                                
                            </td>
                        </tr>
                        <tr><td>&nbsp;</td></tr>
                        <tr>
                            <td align="center">
                            <input type='button' value='Guardar' onClick="MetodoGuardar(1);" name='btnAceptar' class='botones' id='Guardar'>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type='button' value='Regresar' onClick='metodoCerrar();' name='btnRegresar' class='botones' id='Cancelar'>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            
                            <? if($datos["met_radi_codi"]!=""){ ?>
                            <input type='button' value='Eliminar' onClick="MetodoGuardar(2);" name='btnEliminar' class='botones' id='Eliminar'>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <? } ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>               
        </table>
        </td>
       </tr>      
    </table>
    <? } ?>
    </center>
  </form>
  </body>
</html>