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
*****************************************************************************************/
$ruta_raiz = "../..";
session_start();
require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/rec_session.php";
include_once "util_ciudadano.php";
include_once "../usuarios/mnuUsuariosH.php";
$ciud = New Ciudadano($db);

$paginador = new ADODB_Pager_Ajax($ruta_raiz, "div_lista_personas", "busqueda_paginador.php", 'txt_buscar_nombre,tipo_query');

$ciu_nuevo = 1;


echo "<html>" . html_head(); /*Imprime el head definido para el sistema*/
require_once "$ruta_raiz/js/ajax.js";
?>
 <script type="text/javascript" src="jquerysubir/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="adm_ciudadanos.js"></script>
<script type="text/javascript" src="<?=$ruta_raiz?>/js/validar_datos_usuarios.js"></script>
<script type="text/javascript" language="JavaScript" src="<?=$ruta_raiz?>/js/formchek.js"></script>
<script type="text/javascript" language="JavaScript" src="<?=$ruta_raiz?>/js/validar_cedula.js"></script>
<script type="text/javascript">
        codigo_usr_origen = '';
        codigo_usr_destino = '';
        cedula_usr_destino = '';
        function buscar_personas() {
            nombre=document.getElementById('txt_buscar_nombre').value;
            document.getElementById('div_lista_personas').style.display = '';
            if (nombre.length<=100){
                paginador_reload_div('');            
                return;
            }else{
                    document.getElementById('txt_buscar_nombre').value='';
                    alert("El texto no tienen coincidencias en la búsqueda");
                }
                
        }
        function comparar_ciudadanos() {
            nuevoAjax('div_comparar', 'POST', 'adm_usuario_ext_comparar.php', 'old_codigo='+codigo_usr_destino+'&new_codigo='+codigo_usr_origen+'&flag_desactivar=1');
            return;
        }
        function usr_origen(codigo) {
            
            
            
            codigo_usr_origen = codigo;
            document.getElementById("ciu_codigo_eliminar").value = codigo;
            comparar_ciudadanos();
            if (document.getElementById('ver_resultado').value>1)
                document.getElementById('ver_resultado').value='';
            document.getElementById('ver_resultado').value=document.getElementById('ver_resultado').value+1;
            if (document.getElementById('ver_resultado').value>=11)
                document.getElementById('div_lista_personas').style.display = 'none';
            return;
        }        
        function usr_destino(codigo,cedula) {
            document.getElementById('div_datos_registro_civilimg_menos').style.display = '';
            document.getElementById('div_datos_registro_civilimg_mas').style.display = 'none';
            
            
            codigo_usr_destino = codigo;            
            comparar_ciudadanos();
            validar_cambio_cedula(cedula);
            
            document.getElementById("td_btn_aceptar").style.display = '';           
            if (document.getElementById('ver_resultado').value>1)
                document.getElementById('ver_resultado').value='';
            document.getElementById('ver_resultado').value=document.getElementById('ver_resultado').value+1;
            if (document.getElementById('ver_resultado').value>=11){
                
                document.getElementById('div_lista_personas').style.display = 'none';
                }
                document.getElementById('div_lista_personasimg_mas').style.display = '';
            document.getElementById('div_lista_personasimg_menos').style.display = 'none';
            return;
            
            
        }
        function cambio_cedula(obj,tipo){            
            document.getElementById("img_reg"+tipo).style.display = '';            
            cedula = obj.value;
            if (cedula==''){
                document.getElementById("img_reg"+tipo).style.display = 'none';
                document.getElementById('div_datos_registro_civil').style.display = 'none';
            }else{
                document.getElementById("img_reg"+tipo).style.display = '';
                document.getElementById('div_datos_registro_civil').style.display = '';
            }
            validar_cambio_cedula(cedula);         
        }
        function validar_cambio_cedula(cedula) {  
           
        if (trim(cedula)!='') 
            nuevoAjax('div_datos_registro_civil', 'POST', '../usuarios/validar_datos_registro_civil.php', 'cedula='+cedula);
        else
             nuevoAjax('div_datos_registro_civil', 'POST', '../usuarios/validar_datos_registro_civil.php', 'cedula=999');
        }  
        function mover_dato(campo) {
             if (campo=='cedula'){
                if (document.getElementById("new_"+campo).value!=''){
                document.getElementById("old_"+campo).value = document.getElementById("new_"+campo).value;
                if (document.getElementById("old_sincedula"))
                document.getElementById("old_sincedula").checked = false;
                ver_cedula();
                return;
               }else
                    alert("Cédula no tiene valor");
            }else
            document.getElementById("old_"+campo).value = document.getElementById("new_"+campo).value;
            return;
        }


        function ValidarInformacion(grupo)
        {

            flag = true;
            if (grupo=='ciu') {
                if(document.getElementById(grupo+'_sincedula').value==1) flag = false;
            } else {
                if(document.getElementById(grupo+'_cedula').value.substr(0, 2)=='99' || trim(document.getElementById(grupo+'_cedula').value)=="") flag = false;
            }
            if(flag)
                if (!validarCedula(document.getElementById(grupo+'_cedula'))) {
                    alert ("Por favor ingrese un número de cédula válido.");
                    return false;
                }
            if(ltrim(document.getElementById(grupo+'_nombre').value)=='' || ltrim(document.getElementById(grupo+'_apellido').value)=='')
            {	alert("Los campos de Nombres y Apellidos son obligatorios.");
                return false;
            }
            if(trim(document.getElementById(grupo+'_email').value) != "")
            {
                if (!isEmail(document.getElementById(grupo+'_email').value,true))
                {	alert("El campo Email no tiene formato correcto.");
                    return false;
                }
            }
            return true;
        }

        function ver_cedula()
        {            
            if (document.getElementById('old_cedula').value.substr(0, 2)=='99' || trim(document.getElementById('old_cedula').value)=="")                
                document.getElementById('old_sincedula').checked = true;
            if(document.getElementById('old_sincedula'))
                if (document.getElementById('old_sincedula').checked){
               document.getElementById('old_cedula').style.display='none';
               document.getElementById("img_reg2").style.display = 'none';
               document.getElementById('div_datos_registro_civil').style.display = 'none';
            }
            else{
                document.getElementById('old_cedula').style.display='';
                document.getElementById("img_reg2").style.display = '';
                document.getElementById('div_datos_registro_civil').style.display = '';
            }            
        }

        function aceptar_cambios() {
            if (document.getElementById("ciu_codigo_eliminar").value == '') {
                alert ('Seleccione el usuario a desactivar');
                return false;
            }
            mensaje = '¿Desea desactivar el ciudadano ' + document.getElementById('new_nombre').value + ' ' + document.getElementById('new_apellido').value + '?';
            if (!confirm(mensaje)) {
                return false;
            }
            if (ValidarInformacion('old')!=true) {
                return false;
            }
            
            copiar('codigo');
            copiar('cedula');
            copiar('documento');
            copiar('nombre');
            copiar('apellido');
            copiar('titulo');
            copiar('abr_titulo');
            copiar('empresa');
            copiar('cargo');
            copiar('direccion');
            copiar('email');
            copiar('telefono');
            if(document.getElementById('old_cedula').value.substr(0, 2)=='99' || trim(document.getElementById('old_cedula').value)=="")
                document.getElementById('ciu_sincedula').value = 1;
            else
                document.getElementById('ciu_sincedula').value = 0;
            document.frm_confirmar.action='grabar_usuario_ext.php?cerrar=<?=$cerrar?>&accion=2';
            document.frm_confirmar.submit();
        }

        function rechazar_cambios() {
            window.location='../usuarios/mnuUsuarios_ext.php?cerrar=No';
        }       
        
        function evento_buscar(e){
              tecla = (document.all) ? e.keyCode : e.which;
        if (tecla==13) 
            buscar_personas();
        
        }
    </script>
    <body onload='buscar_personas()'>
        <form name='frm_confirmar' action="javascript:buscar_personas()" method="post">
            <?php graficarTabsCiud();?>
        <?php $ciud->cajaHidden('tipo_query', 3);//tipo busqueda
        ?>
            <input type=hidden name="ver_resultado" id="ver_resultado" size="30" value=""/>
        <?  
            //Creamos algunos campos ocultos en los que se pasaran los datos que se modificaran
            $ciud->cajaHidden('ciu_codigo', $ciu_codigo);
            $ciud->cajaHidden('ciu_cedula', $ciu_cedula);
            $ciud->cajaHidden('ciu_sincedula', $ciu_sincedula);
            $ciud->cajaHidden('ciu_documento', $ciu_documento);
            $ciud->cajaHidden('ciu_nombre', $ciu_nombre);
            $ciud->cajaHidden('ciu_apellido', $ciu_apellido);
            $ciud->cajaHidden('ciu_titulo', $ciu_titulo);
            $ciud->cajaHidden('ciu_abr_titulo', $ciu_abr_titulo);
            $ciud->cajaHidden('ciu_empresa', $ciu_empresa);
            $ciud->cajaHidden('ciu_cargo', $ciu_cargo);
            $ciud->cajaHidden('ciu_direccion', $ciu_direccion);
            $ciud->cajaHidden('ciu_email', $ciu_email);
            $ciud->cajaHidden('ciu_telefono', $ciu_telefono);
            $ciud->cajaHidden('ciu_nuevo', $ciu_nuevo);
            $ciud->cajaHidden('ciu_codigo_eliminar', $ciu_codigo_eliminar);
            
?>

            <table border=0 width="100%" class="borde_tab" cellpadding="0" cellspacing="5">                
                <tr>
                    <td width="15%" class="titulos2"><font class="tituloListado">Buscar Persona: </font></td>
                    <td width="15%" class="listado5">Nombre / C.I.</td>
                    <td width="40%" class="listado5">
                        <input type=text name="txt_buscar_nombre" id="txt_buscar_nombre" size="50" value="" class="tex_area" onkeypress="evento_buscar(event)"/></td>
                    <td width="20%" align="center" class="titulos2" >
                        <input type="button" name="btn_buscar" value="Buscar" class="botones" onClick="buscar_personas();">
                    </td>
<!--                    <td width="20%" align="center" class="titulos2" >
                        <input name="btn_cancelar" type="button" class="botones" value="Regresar" onClick="rechazar_cambios();"/>
                    </td>-->
                </tr>
    	    </table>
            <br>            
            <?php echo $ciud->mostrar_div('div_lista_personas','','si','menos');?>
            <center><div id="div_lista_personas"></div></center>
            <center>
            <?php echo $ciud->mostrar_div('div_datos_registro_civil','Datos del Registro Civil','si','mas');?>
            <div id="div_datos_registro_civil" style="width: 100%;"></div>
            </center>
            <br />        
                     
            <center><div id="div_comparar"></div></center>
            <br/>
            <table id="td_btn_aceptar" style="display:none" width="100%" cellpadding="0" cellspacing="0">
              <tr>
<!--        	 <td>
                    <center>
                        <input name="btn_cancelar" type="button" class="botones_largo" value="Regresar" onClick="rechazar_cambios();"/>
                    </center>
                </td>-->
            	<td   colspan="3">
                    <center>
                        <input  name="btn_aceptar" type="button" class="botones_largo" value="Aceptar" onclick="aceptar_cambios();"/>
                    </center>
                </td>
              </tr>
            </table>
        </form>
    </body>
</html>