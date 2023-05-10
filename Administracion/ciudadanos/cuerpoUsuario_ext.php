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
/*************************************************************************************
* Realiza la busqueda de Ciudadanos
*   Reestructuracion de codigo
*   Realizado por               Fecha (dd/mm/aaaa)
*   David Gamboa                16-04-2012
*   se añada paginador
*   Se eliminan funciones 
*/
/*************************************************************************************/

$ruta_raiz = "../..";
session_start();
if($_SESSION["usua_admin_sistema"]!=1)
{
    if ($_SESSION["usua_perm_ciudadano"]!=1)
    {
        include "$ruta_raiz/funciones_interfaz.php";
        die(html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina."));
    }
}
require_once "$ruta_raiz/funciones.php";
require_once "$ruta_raiz/rec_session.php";
include "$ruta_raiz/obtenerdatos.php";
include "$ruta_raiz/funciones_interfaz.php";
include_once "util_ciudadano.php";
include_once "../usuarios/mnuUsuariosH.php";
$ciud = New Ciudadano($db);

$accion = 0 + $_GET["accion"];

$paginador = new ADODB_Pager_Ajax($ruta_raiz, "div_buscar_ciudadanos", "busqueda_paginador.php",
                  "ciu_ver,txt_buscar_nombre,tipo_query");
?>
<html>
<?echo html_head(); ?>
<script type="text/javascript" src="jquerysubir/jquery-1.3.2.min.js"></script>
<script type="text/javascript" language="JavaScript" src="adm_ciudadanos.js"></script>


<script type="text/javascript">

function realizar_busqueda() {
    nombre=document.getElementById('txt_buscar_nombre').value;
        if (nombre.length>=0 && nombre.length<=100){
           if(trim(document.getElementById('txt_buscar_nombre').value)!='0'){
                paginador_reload_div('');
                document.getElementById('mensaje_error').style.display ='none';
           }
            else
                document.getElementById('mensaje_error').style.display ='';
                

        }else{
            document.getElementById('txt_buscar_nombre').value='';
            alert("El texto no tienen coincidencias en la búsqueda");
        }
}    
function seleccionar_usuario(codigo) {
            window.location='adm_usuario_ext.php?accion=<?=$accion?>&ciu_codigo=' + codigo;
            return true;
}
function validarEv(e){   
          tecla = (document.all) ? e.keyCode : e.which;
          if (tecla==13) realizar_busqueda();

}
</script>

<body>
<?php $ciud->cajaHidden('tipo_query', 2);//tipo busqueda
?>
<?php graficarTabsCiud();?>
<div id="destino"></div>

  <center>
    <table border=0 width="100%" class="borde_tab" cellpadding="0" cellspacing="5">
	<tr>
	    <td width="10%" class="titulos5"><font class="tituloListado">Buscar ciudadano: </font></td>
	    <td class="listado5" valign="middle">
	    	<table >
 	  	    <tr>
                        <td><span class="listado5">Nombre / C.I. <br>
                            Puesto / Correo / Institución</span> </td>
			<td><input type=text id="txt_buscar_nombre" name="txt_buscar_nombre" value="<?=$txt_buscar_nombre?>" onkeypress="validarEv(event);" class="tex_area" size="30"/>                            
                        </td>
                        
                    </tr>
	    	</table>
	    </td>
        
        <? 
       
        if ($accion==2){//Edición de ciudadano
            //si es administrador
             if ($_SESSION["usua_codi"]==0 or substr($_SESSION["krd"],0,6)=="UADMIN"){  ?>
        <td class="titulos5">
                <input type="radio"  name="ciu_ver" id="ciu_ver" value="a" onclick="realizar_busqueda();" checked /> Ver Activos
                <input type="radio"  name="ciu_ver" id="ciu_ver" value="b" onclick="realizar_busqueda();"/>Ver Inactivos
                <input type="radio"  name="ciu_ver" id="ciu_ver" value="c" onclick="realizar_busqueda();"/>Ver Todos

        </td>
        <?
           }else //si no es administrador se hace la consultar por la opcion ver todos (c)
            echo '<input type="hidden"  name="ciu_ver" id="ciu_ver" value="a" />';      
         }else 
            echo '<input type="hidden"  name="ciu_ver" id="ciu_ver" value="a" />';  ?>

        <td width="10%" align="center" class="titulos5" >
        <? if ($accion==2 and ($_SESSION["usua_codi"]==0 or substr($_SESSION["krd"],0,6)=="UADMIN") ){?>
                <input type="button" id="btn_buscar" name="btn_buscar" value="Buscar" class="botones" onClick="realizar_busqueda();">
         <?}elseif($accion==2 and ($_SESSION["usua_codi"]!=0 or substr($_SESSION["krd"],0,6)!="UADMIN") ){?>
             <input type="button" id="btn_buscar" name="btn_buscar" value="Buscar" class="botones" onClick="realizar_busqueda();">
         <?}elseif($accion!=2 ){?>
             <input type="button" id="btn_buscar" name="btn_buscar" value="Buscar" class="botones" onClick="realizar_busqueda();">
         <?}?>             
	    </td>
<!--            <td  width="10%" align="center" class="titulos5">
                <input  name="btn_accion" type="button" class="botones" value="Regresar" onClick="location='../usuarios/mnuUsuarios_ext.php?cerrar=<?=$cerrar?>'"/>
            </td>-->
    </tr>    
    </table>
      <div id="mensaje_error" name="mensaje_error" style="display:none">
                                <font size="1" color="red">Ingrese información para la búsqueda</font>
                            </div>
    <div id='div_buscar_ciudadanos' style="width: 100%"></div>
   
  </center>
 
</body>
</html>