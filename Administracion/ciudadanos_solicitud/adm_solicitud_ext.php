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
** Acceso Administrador
 * Administración de datos por parte del ciudadano										**
*****************************************************************************************/
$ruta_raiz = "../..";
$ruta_raiz2 = "..";
session_start();
require_once "$ruta_raiz/funciones.php";
require_once("$ruta_raiz/funciones_interfaz.php");
include_once "$ruta_raiz/rec_session.php";
include_once "../usuarios/mnuUsuariosH.php";
include_once "../ciudadanos/util_ciudadano.php";
$ciud = New Ciudadano($db);


if($_SESSION["usua_admin_sistema"]!=1) {
    echo html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
    die("");
}
if (isset($_GET))
$opcion = limpiar_sql($_GET['opcion']);
$ciu_codigo = 0+$_GET['ciu_codigo'];
$ciu_cedula = 0+$_GET['cedula'];
$accion_btn_cancelar = "window.location='cuerpoSolicitud_ext.php?opcion=$opcion'";
$flag_login = true;

//verifica si esta en temporal
$ciud->consultar_ciudadano_tmp($ciu_codigo,$cedula,0);
$reg=array();
$reg=$ciud->cargar_datos_ciudadano($ciu_codigo,2);

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
$ciu_ciudad     =   $reg['ciu_ciudad'];
$sol_accion     =   $reg['sol_codigo'];
$sol_firma      =   $reg['sol_firma'];
$sol_estado     =   $reg['sol_estado'];
$sol_planilla   =   $reg['sol_planilla'];
$sol_cedula     =   $reg['sol_cedula'];
$sol_acuerdo    =   $reg['sol_acuerdo'];
$ciu_pais    =   $reg['ciu_pais'];
$ciu_canton    =   $reg['ciu_canton'];
$ciu_provincia    =   $reg['ciu_provincia'];
$ciu_referencia    =   $reg['ciu_referencia'];
$sol_observaciones  =   $reg['sol_observaciones'];
       
if($sol_planilla ==1 && $sol_cedula ==1 && $sol_acuerdo ==1)
    $visible_enviar = "display:visible";
else
    $visible_enviar = "display:none";

if($sol_estado == 2)
    $visible_autorizar = "display:visible";
else
    $visible_autorizar = "display:none";

?>

<html>
<? echo html_head(); /*Imprime el head definido para el sistema*/
include_once "$ruta_raiz/js/ajax.js"
?>
    <script type="text/javascript" src="../ciudadanos/jquerysubir/jquery-1.3.2.min.js"></script>
    <script src="../ciudadanos/adm_ciudadanos.js" type="text/javascript"></script>
    <script type="text/javascript" language="JavaScript" src="<?=$ruta_raiz?>/js/formchek.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?=$ruta_raiz?>/js/validar_cedula.js"></script>
    <script type="text/javascript" language="javascript">
    function ltrim(s) {
       return s.replace(/^\s+/, "");
    }

    function verificar_firma(nombre,n) {
        windowprops = "top=100,left=100,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=600,height=400";
        URL = '<?="$ruta_raiz/VerificarFirma.php?archivo="?>'+ nombre +'<?="&nombre_archivo="?>' + n;
        window.open(URL , "Verificar Firma Acuerdo", windowprops);
    }

    

    var bandera = 0;

    function ValidarInformacionRegistroCivil(cedula)
    {
     window.open('adm_solicitud_validar.php?codigo='+cedula+'&nombre='+document.forms[0].ciu_apellido.value +' '+ document.forms[0].ciu_nombre.value,'Datos','height=180,width=450,left=350,top=300');
     bandera = 1;
     document.getElementById('verificacion').value=1;
    }

    function ValidarInformacion(accion)
    {
       
        if(accion == 1)//aprobar
        {
                if (!validarCedula(document.forms[0].ciu_cedula)){
                    alert ('Verifique el número de cédula.');
                    return false;
                }
                if(ltrim(document.forms[0].ciu_nombre.value)=='' || ltrim(document.forms[0].ciu_apellido.value)==''){	
                    alert("Los campos de Nombres y Apellidos son obligatorios.");
                    return false;
                }
                if (!isEmail(document.forms[0].ciu_email.value,true)){	
                    alert("Verifique que el Email tenga el formato correcto.");
                    return false;
                }
                         
                   if (document.forms[0].ciu_ciudad.value==0){
                    if (document.getElementById('inputString').value!='')
                       alert("Seleccione la Ciudad de la lista.");
                   else
                       alert("Seleccione la Ciudad")
                       return false;
                    }
         
          if(document.getElementById('verificacion').value==0){
               alert("Favor Valide los Datos de Registro Civil");
               return false;
           }else{
              if (document.getElementById('acuerdo').value==1){
                if (confirm("Esta seguro/a que desea autorizar la solicitud?")) {  
                     document.forms[0].sol_accion.value=1;
                    document.formulario.submit();
                    return true;
                }else
                    return false;
              }else
                  alert("Verifique el acuerdo")
         }
        }else if (accion==2){//rechazar
            document.forms[0].sol_accion.value=0;
            if(ltrim(document.forms[0].sol_observaciones.value)==''){
             alert("Ingresar la Observación");
             return false;
            }
            if (confirm("Esta seguro/a que desea rechazar la solicitud?")) {
                document.formulario.submit();
                return true;
            }else
                return false;
        }
    
     
}

    function coordinador(){       
        
        if(document.getElementById('chk_acuerdo').checked==true)
            document.formulario.acuerdo.value = '1';
        else
            document.formulario.acuerdo.value = '0';
     }
     function verificacion(){       
        
            document.formulario.verificacion.value = '1';
     }

</script>

<body >
    <form id="formulario" name='formulario' action="adm_solicitud_actualizar.php" method="post">
  <div id="wrapper">
  <? if (!$flag_login) echo html_encabezado(); /*Imprime el encabezado del sistema*/ ?>
  
    <input type="hidden" name="usr_codigo" id="usr_codigo" size="40" value="<?=$ciu_codigo?>" />
                <input type="hidden" name="ciu_ciudad" id="ciu_ciudad" size="40" value="<?=$ciu_ciudad?>" />
                
    
                <?php echo graficarTabsCiud();?>
    <br/>
    <table width="100%" border="1" align="center" class="t_bordeGris">
  	  <tr>
        <td class="titulos4">
            <center><p><B><span class=etexto>AUTORIZAR PERMISOS PARA GENERAR Y FIRMAR DOCUMENTOS EN EL SISTEMA</span></B> </p></center>
	    </td>
	  </tr>
    </table>
    <input type='hidden' name='ciu_codigo' value="<?=$ciu_codigo?>"/>
    <input type='hidden' name='acuerdo' id="acuerdo" value="0"/>
    <input type='hidden' name='verificacion' id="verificacion" value="0"/>
    <input type='hidden' name='sol_accion' id="sol_accion" value="0"/>

    <table width="100%" border="1" align="center" class="t_bordeGris">
<?
     
        echo "<tr>";

        if (!$rs3->EOF) {

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
            <td class='titulos3' width='20%'> Firma Electr&oacute;nica </td>
                    <td class='listado3' width='50%'>

                        <?php echo combo_firma_ciudadano($sol_firma,$db);?>
                   </td>
        <?
    	echo "</tr><tr>";
        echo "<tr>";
            $ciud->dibujar_campo ("ciu_cedula", $ciu_cedula,"* C&eacute;dula", 13,"sol_estado",0);
            $ciud->dibujar_campo ("ciu_documento", $ciu_documento, "Otro Documento", 50,"sol_estado",0);
    	echo "</tr><tr>";
            $ciud->dibujar_campo ("ciu_nombre", $ciu_nombre,"* Nombre", 150,"sol_estado",0); //, "onKeyUp='this.value=this.value.toUpperCase();'");
            $ciud->dibujar_campo ("ciu_apellido", $ciu_apellido,"* Apellido", 150,"sol_estado",0); //, "onKeyUp='this.value=this.value.toUpperCase();'");
    	echo "</tr><tr>";
            $ciud->dibujar_campo ("ciu_titulo", $ciu_titulo,"T&iacute;tulo", 100,"sol_estado",0);
            $ciud->dibujar_campo ("ciu_abr_titulo", $ciu_abr_titulo,"Abr. T&iacute;tulo", 30,"sol_estado",0);
    	echo "</tr><tr>";
            $ciud->dibujar_campo ("ciu_empresa", $ciu_empresa,"Instituci&oacute;n", 150,"sol_estado",0);
            $ciud->dibujar_campo ("ciu_cargo", $ciu_cargo,"Puesto", 150,"sol_estado",0);
    	echo "</tr><tr>";
            $ciud->dibujar_campo ("ciu_telefono", $ciu_telefono,"Tel&eacute;fono", 50,"sol_estado",0);
            $ciud->dibujar_campo ("ciu_email", $ciu_email," Email", 50,"sol_estado",0);
    	echo "</tr>";
            //echo $ciu_canton;
        //echo $ciud->graficarGeo($ciu_pais,$ciu_provincia,$ciu_ciudad,$ciu_canton,$ciu_direccion,$ciu_referencia);

        
          ?>
                   <tr>
        <td class="titulos2">
		Dirección Principal (Barrio/Número)</td>
        <td class="listado2"><input class="caja_texto" type="text" name="ciu_direccion" id="ciu_direccion" onblur="javascript:changeCase_Articulos(this)" value="<?=$ciu_direccion?>" size="50" maxlength="150">
                   </td>
          <td class="titulos2">Referencia (Calles/Transversales)</td><td class="listado2"><input class="caja_texto" type="text" name="ciu_referencia" id="ciu_referencia" onblur="javascript:changeCase_Articulos(this)" value="<?=$ciu_referencia?>" size="50" maxlength="150">
	    </td></tr>
                   
       
                   <tr>
                           <?php
                           
                            echo $ciud->dibujar_campoobs ("sol_observaciones", $sol_observaciones,"Observaciones", 200,"sol_estado");
                           ?>
                       </tr>
                   <tr><td colspan="4">
                <?php 
                
                include_once "../catalogos/ciudad_buscar.php"?>
          </td>
                </tr>
       
        <?if($sol_estado == 3) {?>
        <script type="text/javascript">document.getElementById("sol_firma").disabled = true; </script>
        <?} else {?>
        <script type="text/javascript">document.getElementById("sol_firma").disabled = false; </script>
        <?}?>

    </table>
</div>

<div id="div_anexo_nuevo_archivo_acuerdo"></div>
<?php

if(!$rs3->EOF){
    if($sol_acuerdo == 1){
   ?>
   <table width='100%' align='center' border='1' class='t_bordeGris'><tr><td class='titulos3' width='13%'>* Acuerdo</td><td>
   <input type='checkbox' name='chk_acuerdo' id='chk_acuerdo' value='' onclick="coordinador();"/>
   <?php
    $archivo_desc = $ciu_codigo."_acuerdo.pdf.p7m";
    $nombre_archivo = $ciu_codigo."_acuerdo.pdf.p7m";
    $nombre_firma = "/ciudadanos/".$ciu_codigo."_acuerdo.pdf.p7m";
    $path_descarga = "$ruta_raiz/bodega/ciudadanos/".$ciu_codigo."_acuerdo.pdf.p7m";
    if (is_file($path_descarga)){       
        ?>

   <a href="javascript:window.open('<?=$ruta_raiz?>/archivo_descargar.php?path_arch=/ciudadanos/<?=$archivo_desc?>&amp;nomb_arch=<?=$nombre_archivo?>','_self','');" class="vinculos">
                           <?=$archivo_desc?>
        </a>&nbsp;
       
        <a href='javascript:;' onclick='verificar_firma("<?=$nombre_firma?>","<?=$nombre?>");' class='vinculos'>Verificar Firma</a>
   </table>
          
   <?php }
      }    
   }            
   ?>
    </td>
    </tr>
<?php 
echo $ciud->verHistorico($ciu_codigo,1,'3');?>
    </table>
 
 <br>
    <table width="100%" align="center" cellpadding="0" cellspacing="0" >
        <tr>
            <td><center><input name="btn_autorizar" type="button" class="botones_largo" value="Autorizar" onClick="return ValidarInformacion(1);" title="Autorizar Solicitud" style="<?=$visible_autorizar?>"/></center></td>
            <td><center><input name="btn_delvolver" type="button" class="botones_largo" value="Rechazar"  onClick="return ValidarInformacion(2);" title="Rechazar Solicitud" style="<?=$visible_autorizar?>"/></center></td>
            <td><center><input name="btn_validar" title="Validar datos del ciudadano" type="button" class="botones_largo" value="Validar Registro Civil" onclick="ValidarInformacionRegistroCivil(<?=$ciu_codigo?>)"/></center></td>
            <td><center><input  name="btn_accion" type="button" class="botones_largo" title="Cancelar" value="Cancelar" onClick="<?=$accion_btn_cancelar?>"/></center></td>
<!--    -            <td><center><input  name="btn_accion" type="button" class="botones_largo" title="Cerrar" value="Cerrar" onClick="window.close();"/></center></td>-->
        </tr>
    </table>
     
</form>
</body>
</html>
