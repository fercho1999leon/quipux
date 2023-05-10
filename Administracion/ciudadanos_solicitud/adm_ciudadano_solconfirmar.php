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
*   Acceso Administradores
*   Realizado por               Fecha (dd/mm/aaaa)
*   David Gamboa                16-04-2012
*   se realiza el get desde adm_solicitud_ext.php
*   Confirmar los datos desde la temporal
*****************************************************************************************/
$ruta_raiz = "../..";
$ruta_raiz2 = "..";
session_start();
require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz2/ciudadanos/util_ciudadano.php";
$ciud = New Ciudadano($db);
if($_SESSION["usua_admin_sistema"]!=1 and $_SESSION["usua_perm_ciudadano"]!=1) {
    echo html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
    die("");
}
if (isset($_GET['ciu_codigo']))
  $ciu_codigo = limpiar_sql($_GET['ciu_codigo']);
    
?>

<html>
<?
    echo html_head(); /*Imprime el head definido para el sistema*/
    require_once "$ruta_raiz/js/ajax.js";
?>
    <script type="text/javascript" src="<?=$ruta_raiz?>/Administracion/ciudadanos/adm_ciudadanos.js"></script>
    <script type="text/javascript" src="<?=$ruta_raiz?>/js/validar_datos_usuarios.js"></script>    
    <script type="text/javascript" language="JavaScript" src="<?=$ruta_raiz?>/js/formchek.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?=$ruta_raiz?>/js/validar_cedula.js"></script>    
    <script type="text/javascript">
            
        function comparar_ciudadanos(codigo,cedula) {
           validar_cambio_cedulajs(cedula);
            document.getElementById("ciu_codigo").value = codigo;
            nuevoAjax('div_comparar', 'POST', 'adm_usuario_ext_comparar.php', 'old_codigo='+codigo);
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
            }else{
                alert("No se ha definido la Cédula para el ciudadano.");
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
            function copiar(campo) {
                document.getElementById('ciu_'+campo).value = document.getElementById('new_'+campo).value;
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
            if(trim(document.getElementById('ciu_cedula').value)=="" || document.getElementById('ciu_cedula').value.substr(0,2)=="99")
                document.getElementById('ciu_sincedula').value = 1;
            else
                document.getElementById('ciu_sincedula').value = 0;
            
            document.frm_confirmar.action='grabar_usuario_ext.php?accion=1';
            document.frm_confirmar.submit();
        }
        function ver_datos(tipo){
            if (tipo==2)
            cedula=document.getElementById('old_cedula').value;
        else
            cedula=document.getElementById('new_cedula').value;
           if (cedula!=''){
                validar_cambio_cedulajs(cedula);
                document.getElementById('div_datos_registro_civil').style.display = '';
           }
            else{
                alert('No existe Cédula');
                document.getElementById('div_datos_registro_civil').style.display = 'none';
            }
          
        }
    </script>
    <body>
    
        <form name='frm_confirmar' action="grabar_usuario_ext.php?accion=2" method="post">
            <center><table><tr><td width="100%">
            <?           
            echo "<center><font size='2'><b>Para aprobar la solicitud, por favor actualice los datos del ciudadano.</b></font></center>";           
            ?>
            </td></tr></table></center>
                       
        </form>
    </body>
</html>
