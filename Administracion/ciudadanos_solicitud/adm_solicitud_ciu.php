<?
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
/*****************************************************************************************
 * Acceso: Ciudadanos
 * Permite subir el archivo, guarda en bodega/ciudadanos/codigo_ciudadano_acuerdo.pdf.p7m
 * Permite enviar la solicitud al administrador 
*****************************************************************************************/
$ruta_raiz = "../..";
session_start();

require_once "$ruta_raiz/funciones.php";
require_once("$ruta_raiz/funciones_interfaz.php");
include_once "$ruta_raiz/rec_session.php";

include_once "../ciudadanos/util_ciudadano.php";
$ciud = New Ciudadano($db);

$ciu_codi = limpiar_sql($_SESSION["usua_codi"]);

$accion_btn_cancelar =  "window.location='$ruta_raiz/Administracion/ciudadanos/adm_ciudadano.php'";
$flag_login = true;
$ciu_ciudad =0;
$carp_codi = 80;
$nombre = "Documentos Enviados";
    //Si no existe lo busca por codigo
    $sql = "select * from ciudadano where ciu_codigo=" . $ciu_codi . "";
    $rs = $db->conn->query($sql);
    if (!$rs or $rs->EOF) {
        echo html_error("No se encontr&oacute; el usuario en el sistema.");
        die("");
    }
    
//comprobar si el ciudadano está en temporal

$ciud->consultar_ciudadano_tmp($ciu_codi,'',2,'..');
$cedula_temporal = substr($rs->fields["CIU_CEDULA"], 0,4);

if ($cedula_temporal == '9999'){
   echo "<html>".html_head();
    echo "<center>
        <br />
        <table width='40%' border='2' align='center' class='borde_tab'>
            <tr>
            <td width='100%' height='30' class='listado2'>
                <span class='listado5'><center><B>Por favor edite sus datos personales antes de ingresar una solicitud.</B></center></span>
            </td>
            </tr>
            <tr>
            <td height='30' class='listado2'>
                <center><input class='botones' type='button' value='Aceptar' onClick=\"$accion_btn_cancelar\"></center>
            </td>
            </tr>
        </table>
    </center>";
    die ();
}

    $ciu_nuevo   	= $rs->fields["CIU_NUEVO"];
    //cargar datos de solicitud_firma o ciudadano
    $reg=array();
    
    if ($rs->fields["CIU_CODIGO"]!=''){
        $reg=$ciud->cargar_datos_ciudadano($rs->fields["CIU_CODIGO"],2);
        $ciu_codigo     =   $reg['ciu_codigo'];
        
        $ciu_cedula     =   $reg['ciu_cedula'];
        $ciu_documento  =   $reg['ciu_documento'];
        $ciu_nombre     =   $reg['ciu_nombre'];
        $ciu_apellido   =   $reg['ciu_apellido'];
        $ciu_titulo     =   $reg['ciu_titulo'];
        $ciu_abr_titulo =   $reg['ciu_abr_titulo'];
        $ciu_empresa    =   $reg['ciu_empresa'];
        $ciu_cargo      =   $reg['ciu_cargo'];
        $ciu_direccion  =   $reg['ciu_direccion'];
        $ciu_email      =   $reg['ciu_email'];
        $ciu_telefono   =   $reg['ciu_telefono'];
        $ciu_ciudad    =   $reg['ciu_ciudad'];
        if ($ciu_ciudad=='')
          $ciu_ciudad = 0;
       //echo $codi_ciudad."-".$ciu_pais."-".$ciu_provincia."-".$ciu_canton;
        $ciu_referencia     =   $reg['ciu_referencia'];
        //echo $ciu_pais;
        $sol_codigo     =   $reg['sol_codigo'];    
        $sol_firma      =   $reg['sol_firma'];
        $sol_estado     =   $reg['sol_estado'];
        $sol_planilla   =   $reg['sol_planilla'];
        $sol_cedula     =   $reg['sol_cedula'];
        $sol_acuerdo    =   $reg['sol_acuerdo'];
        $sol_observaciones = $reg['sol_observaciones'];
    }

if($sol_estado == 2)
    
    $visible_autorizar = "display:none";
    
else
    
    $visible_autorizar = "display:visible";
    
?>

<html>
<? echo html_head(); /*Imprime el head definido para el sistema*/
include_once "$ruta_raiz/js/ajax.js"
?>
 <!--// Recursos para subir archivos //-->
 <script type="text/javascript" src="../ciudadanos/jquerysubir/jquery-1.3.2.min.js"></script>
<script type="text/javascript" language="JavaScript" src="../ciudadanos/adm_ciudadanos.js"></script>

 
<script type="text/javascript" language="JavaScript" src="<?=$ruta_raiz?>/js/formchek.js"></script>
<script type="text/javascript" language="JavaScript" src="<?= $ruta_raiz ?>/js/validar_datos_usuarios.js"></script>

<script>
function ValidarInformacion(tipo)
    {
        //Cola de mensajes de error:
        msg = '';
        
        //Evalúa, en base a la condición, si agrega el mensaje entre los errores o no:
        function e(condicion, mensaje) {
            msg = (condicion) ? msg + mensaje + ' \n' : msg;
        }

        e(!validarCedula(trim(document.forms[0].ciu_cedula.value)), "Verifique su número de cédula.");
        e(trim(document.forms[0].ciu_nombre.value) == '', "Ingrese los nombres.");
        e(trim(document.forms[0].ciu_apellido.value) == '', "Ingrese los apellidos.");         
        e(!isEmail(document.forms[0].ciu_email.value,true) , "El campo Email no tiene formato correcto.");

        if(msg != '' )
        {
            alert(msg);
            return false;
        }

            if (document.forms[0].ciu_ciudad.value==0){
               if (document.getElementById('inputString').value!='')
                       alert("Seleccione la Ciudad de la lista.");
                   else
                       alert("Seleccione la Ciudad")
                       return false;
                    }
        if (msg == '' && !validar_datos_registro_civil('ciu_nombre','ciu_apellido')) return false;
        
              
        if (tipo==1){            
           document.formulario.submit();
        }
        else
            EditarUsuario();
            
        return true;
    }    
    
    function EditarUsuario(){//envia ultimas modificaciones en los datos si lo hiso el ciudadano        
        
             document.getElementById("nombre_usu").value =  document.getElementById("ciu_nombre").value;
             document.getElementById("cedula_usu").value =  document.getElementById("ciu_cedula").value;
             document.getElementById("documento_usu").value =  document.getElementById("ciu_documento").value;
             document.getElementById("apellido_usu").value =  document.getElementById("ciu_apellido").value;
             document.getElementById("titulo_usu").value =  document.getElementById("ciu_titulo").value;
             document.getElementById("abr_titulo_usu").value =  document.getElementById("ciu_abr_titulo").value;
             document.getElementById("empresa_usu").value =  document.getElementById("ciu_empresa").value;
             document.getElementById("cargo_usu").value =  document.getElementById("ciu_cargo").value;
             document.getElementById("direccion_usu").value =  document.getElementById("ciu_direccion").value;
             document.getElementById("mail_usu").value =  document.getElementById("ciu_email").value;
             document.getElementById("telefono_usu").value =  document.getElementById("ciu_telefono").value;           
             document.getElementById("ciudad_usu").value =  document.getElementById("ciu_ciudad").value;
             document.getElementById("referencia_usua").value =  document.getElementById("ciu_referencia").value;
             document.getElementById("sol_estado").value = 2;
             document.getElementById("firma_usu").value =  document.getElementById("sol_firma").value;             
             document.formulario.submit();
    }
   
    function mostrar_boton(){
   
   datos = 'tipo=c';
            nuevoAjax('div_actualizar_sol', 'GET', 'adm_sol_ciu_borrar_anexo.php', datos);
   }
   function descargar_contenido() {
        path='<?=$path_acuerdo?>';
        windowprops = "top=100,left=100,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=500,height=300";
//
        url = "<?=$ruta_raiz?>/descargar_contenidos.php?archivo="+path;
        window.open(url , "Vista_Previa_Acuerdo", windowprops);
        return;
    }
    
    function anexo_borrar_archivo() {    
        if (confirm("Está seguro/a que desea eliminar el archivo?")) {            
            
            datos = 'tipo=b';
            window.location='adm_sol_ciu_borrar_anexo.php?tipo=b';            
            
        }
    }
    
</script>

</head>
<body onload="validar_cambio_cedulajs(); mostrar_boton(); datosCiudad(<?=$ciu_ciudad?>) ">
  <div id="wrapper">
  <? if (!$flag_login) echo html_encabezado(); /*Imprime el encabezado del sistema*/ ?>
  <form name='formulario' action="adm_sol_ciu_grabar.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="usr_codigo" id="usr_codigo" size="40" value="<?=$ciu_codigo?>" />
      <input type="hidden" name="ciu_ciudad" id="ciu_ciudad" size="40" value="<?=$ciu_ciudad?>" />
      <table width="100%" class="borde_tab" rules="rows">
  	  <tr>
        <td class="titulos4">
            <center><p><B><span class=etexto>SOLICITAR PERMISOS PARA GENERAR Y FIRMAR DOCUMENTOS EN EL SISTEMA</span></B> </p></center>
	    </td>
	  </tr>
    </table>
    <br/>
    <?php echo $ciud->divsInformacionUsrCiud($ciu_cedula);?>
    <input type='hidden' id="ciu_codigo" name='ciu_codigo' value="<?=$ciu_codigo?>">
    <input type='hidden' id="soli_enviar" name='soli_enviar' value="0"/>
    <input type='hidden' id="guardar_anexo" name='guardar_anexo' value="0"/>
    <table width="100%" class="borde_tab" border="1">
    <tr>
    <?
        if (!$rs2->EOF) {
        if($sol_estado == 0)
           $ciud->dibujar_campoprueba ("sol_estado", "Estado Solicitud", 13,"Rechazado");
        else if($sol_estado == 1)
           $ciud->dibujar_campoprueba ("sol_estado", "Estado Solicitud", 13,"En Edición");
        else if($sol_estado == 2)
            $ciud->dibujar_campoprueba ("sol_estado", "Estado Solicitud", 13,"Enviado");
        else if($sol_estado == 3)
            $ciud->dibujar_campoprueba ("sol_estado", "Estado Solicitud", 13,"Autorizado");

        }
        else
            $ciud->dibujar_campoprueba ("sol_estado", "Estado Solicitud", 13,"En Edición");
        ?>
            <td class='titulos2' width='20%'> Tengo Firma Electr&oacute;nica </td>
                    <td class='listado3' width='50%'>                        

                        <?php                         
                        echo combo_firma_ciudadano($sol_firma,$db);?>
                   </td>
        </tr>
        <?
    	
        echo "<tr>";
            $ciud->dibujar_campo ("ciu_cedula", $ciu_cedula,"* C&eacute;dula: ", 13,"sol_estado",2);
            $ciud->dibujar_campo ("ciu_documento", $ciu_documento,"Otro Documento: ", 50,"sol_estado",2);
    	echo "</tr><tr>";
            $label = "* Nombre: &nbsp;&nbsp;&nbsp;<img src=\"$ruta_raiz/iconos/copy.gif\" alt=\"copiar\" title=\"Copiar datos del Registro Civil\" onclick=\"copiar_datos_registro_civil('nombre', 'ciu_nombre')\">";
            $ciud->dibujar_campo ("ciu_nombre", $ciu_nombre,$label, 150,"sol_estado",2); //, "onKeyUp='this.value=this.value.toUpperCase();'");
            $label = "* Apellido: &nbsp;&nbsp;&nbsp;<img src=\"$ruta_raiz/iconos/copy.gif\" alt=\"copiar\" title=\"Copiar datos del Registro Civil\" onclick=\"copiar_datos_registro_civil('nombre', 'ciu_apellido')\">";
            $ciud->dibujar_campo ("ciu_apellido", $ciu_apellido,$label, 150,"sol_estado",2); //, "onKeyUp='this.value=this.value.toUpperCase();'");
    	echo "</tr><tr>";
            $ciud->dibujar_campo ("ciu_titulo", $ciu_titulo,"T&iacute;tulo: ", 100,"sol_estado",2);
            $ciud->dibujar_campo ("ciu_abr_titulo", $ciu_abr_titulo, "Abr. T&iacute;tulo: ", 30,"sol_estado",2);
    	echo "</tr><tr>";
            $ciud->dibujar_campo ("ciu_empresa", $ciu_empresa,"Instituci&oacute;n: ", 150,"sol_estado,2");
            $ciud->dibujar_campo ("ciu_cargo", $ciu_cargo,"Puesto: ", 150,"sol_estado",2);
    	echo "</tr><tr>";            
            $ciud->dibujar_campo ("ciu_email", $ciu_email," Email: ", 50,"sol_estado",2);
            $ciud->dibujar_campo ("ciu_telefono", $ciu_telefono,"Tel&eacute;fono: ", 50,"sol_estado",2);
    	echo "</tr>";
        ?>
       	<tr>
        <td class="titulos2">
		Dirección Principal (Barrio/Número)</td>
        <td class="listado2"><input class="caja_texto" type="text" name="ciu_direccion" id="ciu_direccion" onblur="javascript:changeCase_Articulos(this)" value="<?=$ciu_direccion?>" size="50" maxlength="150">
                   </td>
          <td class="titulos2">Referencia (Calles/Transversales)</td><td class="listado2"><input class="caja_texto" type="text" name="ciu_referencia" id="ciu_referencia" onblur="javascript:changeCase_Articulos(this)" value="<?=$ciu_referencia?>" size="50" maxlength="150">
	    </td></tr>
        <?php
        //echo $ciud->graficarGeo($ciu_pais,$ciu_provincia,$codi_ciudad,$ciu_canton,$ciu_direccion,$ciu_referencia);
        
             echo "<tr>";
            $ciud->dibujar_campoobs ("sol_observaciones", $sol_observaciones,"Observaciones: ", 200,"sol_estado");
    	echo "</tr>";
        ?>
       
            <tr>
                <td class="titulos2">Acuerdo de uso del Sistemas: </td>
                
                
            <td class="listado2" colspan="3">
                
                &nbsp<a href="javascript:;" onClick="descargar_contenido();" class="Ntooltip">
                    <img src="<?=$ruta_raiz?>/imagenes/document_down.jpg" width="17" height="17" alt="Descargar Acuerdo" border="0">
                        <span><font color="black">
                        Descargar Acuerdo
                        </font></span></a>
               
            </td>
                </tr>
                <tr>
             <?if($sol_estado == 2) {?>
                   <script type="text/javascript">document.getElementById("sol_firma").disabled = true; </script>
                   <?} else {?>
                   <script type="text/javascript">document.getElementById("sol_firma").disabled = false; </script>
                   <?}?>
                   <td class="titulos2">* Acuerdo: </td>
                   <td class="listado2" colspan="3">
                   <?php 
                   //  onchange='ValidarInformacion(1);'
                   $url = "$ruta_raiz/bodega/ciudadanos/".$ciu_codigo."_acuerdo.pdf.p7m";
                   $nombre_archivo = $ciu_codigo."_acuerdo.pdf.p7m";
                   $archivo_desc = $ciu_codigo."_acuerdo.pdf.p7m";
                   if (!is_file($url)){                    
                    $ciud->fileUpload('acuerdo',array('p7m','p7m'),1,"");
                   }
                   if (is_file($url)){ ?>       
                       <a href="javascript:" onClick="document.getElementById('ifr_descargar_archivo').src='../../archivo_descargar.php?path_arch=/ciudadanos/<?=$archivo_desc?>&nomb_arch=<?=$nombre_archivo?>';" class="vinculos">
                           <?=$archivo_desc?>
                       </a>
                       <iframe id="ifr_descargar_archivo" src="" style="display: none;"></iframe>
                       <?php
                       if($sol_estado!='3' && $sol_estado!='2'){                           
                            echo '<a href="javascript:" class="vinculos"><font size="1"></font>';
                            echo '&nbsp;<img src="'.$ruta_raiz.'/imagenes/close_button.gif" width="18" height="18" onClick="anexo_borrar_archivo(\'acuerdo\');" title="Eliminar Archivo" style="border: 0px solid gray;cursor:pointer;" alt="Eliminar Archivo"></a>';         
                       }else{ 
                           echo '<br>&nbsp;<font size="2" color="blue">Solicitud Enviada</font>';
                       }
               }
                    ?>
      </td>
    </tr>
     <tr><td colspan="4">
          <?php 
        include_once "../catalogos/ciudad_buscar.php"?></td>
      </tr>
     
</table>
<br/>
<?php 

echo $ciud->verHistorico($_SESSION['usua_codi'],1,'3');?>
</form>
  <? if (!$flag_login) echo html_pie_pagina(); /*Imprime el pie de pagina del sistema*/ ?>
  </div>
<br/>
    <table width="100%" align="center" cellpadding="0" cellspacing="0" >
        <tr>
         <td><center>
            <input name="btn_aceptar" type="button" class="botones_largo" title="Aceptar" value="Aceptar"  onClick="return ValidarInformacion(1);" style="<?=$visible_autorizar?>"/>
        </center></td>
         <td><center>
         <form name='formulario_enviar' action="adm_sol_ciu_enviar.php" method="post">
            <div id="div_actualizar_sol" name="div_actualizar_sol"></div>
            <div id="div_enviar"></div>
             <input type="hidden" id="nombre_usu" name="nombre_usu" />
             <input type="hidden" id="cedula_usu" name="cedula_usu" />
             <input type="hidden" id="documento_usu" name="documento_usu" />
             <input type="hidden" id="apellido_usu" name="apellido_usu" />
             <input type="hidden" id="titulo_usu" name="titulo_usu" />
             <input type="hidden" id="abr_titulo_usu" name="abr_titulo_usu" />
             <input type="hidden" id="empresa_usu" name="empresa_usu" />
             <input type="hidden" id="cargo_usu" name="cargo_usu" />
             <input type="hidden" id="direccion_usu" name="direccion_usu" />
             <input type="hidden" id="mail_usu" name="mail_usu" />
             <input type="hidden" id="telefono_usu" name="telefono_usu" />
             <input type="hidden" id="observaciones_usu" name="observaciones_usu" />
             <input type="hidden" id="firma_usu" name="firma_usu" />
             <input type="hidden" id="ciudad_usu" name="ciudad_usu" />
             <input type="hidden" id="sol_estado" name="sol_estado" />
             <input type="hidden" id="referencia_usua" name="referencia_usua" />
            
             
         </form>
         </center></td>
            <td><center><input  name="btn_accion" type="button" class="botones_largo" title="Cancelar" value="Cancelar" onClick="<?=$accion_btn_cancelar?>"/></center></td>
        </tr>
    </table>
</body>
</html>