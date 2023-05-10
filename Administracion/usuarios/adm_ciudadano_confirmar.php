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
**											**
*****************************************************************************************/
$ruta_raiz = "../..";
require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
include_once "$ruta_raiz/funciones_interfaz.php";

session_start();
if($_SESSION["usua_admin_sistema"]!=1 and $_SESSION["usua_perm_ciudadano"]!=1) {
    echo html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
    die("");
}

include_once "$ruta_raiz/rec_session.php";
?>

<html>
<?
    echo html_head(); /*Imprime el head definido para el sistema*/
    require_once "$ruta_raiz/js/ajax.js";
?>
    <script type="text/javascript" src="<?=$ruta_raiz?>/js/validar_datos_usuarios.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?=$ruta_raiz?>/js/formchek.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?=$ruta_raiz?>/js/validar_cedula.js"></script>
    <script type="text/javascript">
        function cambio_cedula(obj,tipo) {
        //cedula = obj.value;
        //validar_cambio_cedula(cedula);
        document.getElementById("img_reg"+tipo).style.display = '';            
            cedula = obj.value;
            if (cedula==''){
                document.getElementById("img_reg"+tipo).style.display = 'none';
                document.getElementById('div_datos_registro_civil').style.display = 'none';
            }
            else{
                document.getElementById("img_reg"+tipo).style.display = '';
                document.getElementById('div_datos_registro_civil').style.display = '';
            }
            validar_cambio_cedula(cedula);
            
        
        }
        function validar_cambio_cedula(cedula) { 
        if (trim(cedula)!='') 
            nuevoAjax('div_datos_registro_civil', 'POST', 'validar_datos_registro_civil.php', 'cedula='+cedula);            
        }        
        function comparar_ciudadanos(codigo,cedula) {
            validar_cambio_cedula(cedula);
            document.getElementById("ciu_codigo").value = codigo;
            nuevoAjax('div_comparar', 'POST', 'adm_usuario_ext_comparar.php', 'old_codigo='+codigo);
            return;
        }

        function mover_dato(campo) {
            document.getElementById('btn_aceptar2').style.visibility="visible";
            document.getElementById("old_"+campo).value = document.getElementById("new_"+campo).value;
//             if (campo == 'ciudad') {
//                document.getElementById('old_nombreciudad').value = document.getElementById('new_nombreciudad').value;
//            }
            return;
        }

        function ltrim(s) {
           return s.replace(/^\s+/, "");
        }

        function trim(s) {
            return s = s.replace(/^\s+|\s+$/gi, '');
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
            function copiar(campo) {
                document.getElementById('ciu_'+campo).value = document.getElementById('old_'+campo).value;
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
            //document.getElementById('ciu_nuevo').value = 1;
            document.frm_confirmar.action='grabar_usuario_ext.php?accion=1';
            document.frm_confirmar.submit();
        }
        function ver_datos(tipo){
            if (tipo==2)
            cedula=document.getElementById('old_cedula').value;
        else
            cedula=document.getElementById('new_cedula').value;
           if (cedula!=''){
                validar_cambio_cedula(cedula);
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
            <input type='hidden' name='pagina_anterior' id='pagina_anterior' value='confirmar'>
<?
            function caja_hidden($campo) {
               echo "<input type='hidden' name='$campo' id='$campo' value=''>";
                return;
            }
            caja_hidden("ciu_cod_elim");
            caja_hidden("ciu_codigo");
            caja_hidden("ciu_cedula");
            caja_hidden("ciu_sincedula");
            caja_hidden("ciu_documento");
            caja_hidden("ciu_nombre");
            caja_hidden("ciu_apellido");
            caja_hidden("ciu_titulo");
            caja_hidden("ciu_abr_titulo");
            caja_hidden("ciu_empresa");
            caja_hidden("ciu_cargo");
            caja_hidden("ciu_direccion");
            caja_hidden("ciu_email");
            caja_hidden("ciu_telefono");
            caja_hidden("ciu_nuevo");
            caja_hidden("ciu_ciudad");

            // Verificar si existen ciudadanos con nombres similares o la misma cédula
            $sql = "select ciu_cedula as \"Cédula\",
                    ciu_nombre||' '||ciu_apellido as \"Nombre\",
                    ciu_titulo as \"Titulo\",
                    ciu_cargo as \"Cargo\",
                    ciu_empresa as \"Institución\",
                    ciu_email as \"Correo Electrónico\",
                    'Editar' as \"SCR_Función\",
                    'comparar_ciudadanos(\"'||ciu_codigo||'\",\"'||ciu_cedula||'\");' as \"HID_FUNCION\"
                    from ciudadano_tmp
                    where ciu_estado=1";
            //echo $sql;
            $rs = $db->conn->query($sql);

            echo "<center><h3>Existen ciudadanos con datos por actualizar.</h3></center>";
            $pager = new ADODB_Pager($db,$sql,'adodb', false,1,"");
            $pager->toRefLinks = "";
            $pager->toRefVars = "";
            $pager->checkAll = true;
            $pager->checkTitulo = false;
            $pager->Render($rows_per_page=50,$linkPagina,$checkbox=chkAnulados);
?>
            <br />
            <center>
            <div id="div_datos_registro_civil" style="width: 100%;"></div>
            </center>
            <div name="div_comparar" id="div_comparar"></div>
            <br/>
            <table width="100%"  align="center" cellpadding="0" cellspacing="0" >
              <tr>
            	<td>
                    <center>
                        <input  name="btn_accion" type="button" class="botones_largo" value="Cancelar" onclick="window.location='./mnuUsuarios_ext.php'"/>
                    </center>
                </td>
              </tr>
            </table>
        </form>
    </body>
</html>
