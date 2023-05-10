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
*  Acceso: Administrador 
*   Fecha (dd/mm/aaaa)
*   David Gamboa                16-04-2012
*   se añade paginador											**
*****************************************************************************************/
$ruta_raiz = "../..";
session_start();
require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/rec_session.php";

include_once "util_ciudadano.php";
include_once "../usuarios/mnuUsuariosH.php";
$ciud = New Ciudadano($db);

if($_SESSION["usua_admin_sistema"]!=1 and $_SESSION["usua_perm_ciudadano"]!=1) {
    echo html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
    die("");
}

$paginador = new ADODB_Pager_Ajax($ruta_raiz, "div_ciudadano_confirmar", "busqueda_paginador.php",
        "txt_buscar_nombre,tipo_query");
?>

<html>
<?

    echo html_head(); /*Imprime el head definido para el sistema*/
    require_once "$ruta_raiz/js/ajax.js";
?>
     <script type="text/javascript" src="jquerysubir/jquery-1.3.2.min.js"></script>
    <script type="text/javascript" src="adm_ciudadanos.js"></script>
   
    <script type="text/javascript" src="<?=$ruta_raiz?>/js/validar_datos_usuarios.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?=$ruta_raiz?>/js/formchek.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?=$ruta_raiz?>/js/validar_cedula.js"></script>
    <script type="text/javascript">
     
        function realizar_busqueda() {
            nombre=document.getElementById('txt_buscar_nombre').value;
            document.getElementById('div_ciudadano_confirmar').style.display = '';
            if (nombre.length<=100){
               paginador_reload_div('');
                }else{
                    document.getElementById('txt_buscar_nombre').value='';
                    alert("El texto no tienen coincidencias en la búsqueda");
                }
        } 
        
        function validar_cambio_cedula(cedula) { 
        if (trim(cedula)!='') 
            nuevoAjax('div_datos_registro_civil', 'POST', '../usuarios/validar_datos_registro_civil.php', 'cedula='+cedula);            
        }        
        function comparar_ciudadanos(codigo,cedula) {
            document.getElementById('div_ciudadano_confirmar').style.display = 'none';
            validar_cambio_cedula(cedula);
            document.getElementById("ciu_codigo").value = codigo;
            nuevoAjax('div_comparar', 'POST', 'adm_usuario_ext_comparar.php', 'old_codigo='+codigo);
            document.getElementById('div_ciudadano_confirmarimg_mas').style.display = '';
            document.getElementById('div_ciudadano_confirmarimg_menos').style.display = 'none';
            
            document.getElementById('div_datos_registro_civilimg_mas').style.display = 'none';
            document.getElementById('div_datos_registro_civilimg_menos').style.display = '';
            
            /*if (document.getElementById('div_ciudadano_confirmarimg_mas')){
                document.getElementById('div_ciudadano_confirmarimg_mas').style.display = 'none';
                document.getElementById('div_ciudadano_confirmarimg_menos').style.display = '';
            }*/
                
            return;
        }

        function mover_dato(campo) {
            document.getElementById('btn_aceptar2').style.visibility="visible";
            document.getElementById("old_"+campo).value = document.getElementById("new_"+campo).value;

            return;
        }

        function ValidarInformacion(grupo)
        {
            if (trim(document.getElementById(grupo+'_cedula').value) != "")
            {
                if (!validarCedula(document.getElementById(grupo+'_cedula'))) {
                    return false;
                }
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
                document.getElementById('old_cedula').style.display='';
        }

        function aceptar_cambios() {
            
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
            copiar('nuevo');
            
            copiar('ciudad');
            
            copiar('referencia');
            document.getElementById('ciu_sincedula').value = 0;
            document.frm_confirmar.submit();
        }
        
        function rechazar_cambios() {
            document.frm_confirmar.action = 'adm_ciudadano_borrar.php';
            document.frm_confirmar.submit();
            return;
        }
        
        function crear_nuevo(){
            if (ValidarInformacion('new')!=true) {
                return false;
            }
            
            document.getElementById('ciu_cod_elim').value = document.getElementById('old_codigo').value;
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
            copiar('ciudad');
            
            copiar('referencia');
            if(trim(document.getElementById('ciu_cedula').value)=="" || document.getElementById('ciu_cedula').value.substr(0,2)=="99")
                document.getElementById('ciu_sincedula').value = 1;
            else
                document.getElementById('ciu_sincedula').value = 0;
            
            document.frm_confirmar.action='grabar_usuario_ext.php?accion=1';
            document.frm_confirmar.submit();
        }
        
    </script>
    <body onload="realizar_busqueda();">        
        <form name='frm_confirmar' action="grabar_usuario_ext.php?accion=2" method="post">
            <?php $ciud->cajaHidden('tipo_query', 1);//tipo busqueda
        ?>
            <?php graficarTabsCiud();?>
            <table border=0 width="100%" class="borde_tab" cellpadding="0" cellspacing="5">
	<tr class="titulos5">
	    <td width="15%"><font class="tituloListado">Buscar ciudadano: </font></td>
	   	
                        <td width="15%" class="listado2"><span>Nombre / C.I. <br>
                            Puesto / Correo / Institución</span> </td>
			<td width="30%" class="listado2">
                            <input type=text id="txt_buscar_nombre" name="txt_buscar_nombre" value="<?=$txt_buscar_nombre?>" class="tex_area" size="50" onKeyPress='if (event.keyCode==13) return false;'/></td>
                        <td width="20%" align="center" class="titulos5">
                            <input type="button" id="btn_buscar" name="btn_buscar" value="Buscar" class="botones" onClick="realizar_busqueda();"/>
                        </td>
<!--                            <td width="20%" align="center" class="titulos5">
                            <input  name="btn_accion" type="button" class="botones" value="Regresar" onclick="window.location='../usuarios/mnuUsuarios_ext.php'"/>
                        </td>-->
        </tr>
            </table>
            <input type='hidden' name='pagina_anterior' id='pagina_anterior' value='confirmar'/>
<?
            
            
            
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
            $ciud->cajaHidden('ciu_cod_elim', $ciu_cod_elim);
            $ciud->cajaHidden('ciu_ciudad', $ciu_ciudad);
            
             $ciud->cajaHidden('ciu_referencia', $ciu_referencia);
            echo "<center><font size='2'><b>Existen ciudadanos con datos por actualizar.</b></font></center>";
           
?>
            <br />
            
          
            <?php echo $ciud->mostrar_div('div_ciudadano_confirmar','','si','menos');?>
            <center><div name="div_ciudadano_confirmar" id="div_ciudadano_confirmar"></div>
                <?php
            echo $ciud->mostrar_div('div_datos_registro_civil','Datos Registro Civil');
            ?>
            <div name="div_datos_registro_civil" id="div_datos_registro_civil"></div>
            <div name="div_comparar" id="div_comparar"></div>
            </center>
            <br/>
            <table width="100%"  align="center" cellpadding="0" cellspacing="0" >
              <tr>
            	
              </tr>
            </table>
        </form>
    </body>
</html>
