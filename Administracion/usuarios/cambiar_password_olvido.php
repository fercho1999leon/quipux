<?
/**  Programa para el manejo de gestion documental, oficios, memorandos, circulares, acuerdos
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
/**
* Página para cambio de contraseña de usuario interno o ciudadano.
**/

$ruta_raiz = "../..";
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/funciones.php";
echo "<html>" . html_head();
include_once "$ruta_raiz/js/ajax.js";
?>
<script type="text/javascript" src="<?=$ruta_raiz?>/js/formchek.js"></script>
<script type="text/javascript" src="<?=$ruta_raiz?>/js/validar_cedula.js"></script>
<script type="text/javascript">
    function cambiar_password_validar_usuario() {
        parametros = "txt_login=" + document.getElementById("txt_login").value;
        nuevoAjax('div_validar_usuario', 'POST', 'cambiar_password_olvido_validar.php', parametros);
    }

    function cambiar_password() {
        try {
            if (document.getElementById("txt_login").value == "") {
                alert ('Por favor ingrese su número de cédula.');
                return;
            }
            if (document.getElementById("txt_ok").value == 0) {
                window.close();
            }
            if (document.getElementById("txt_ok").value == 1) {
                if (document.getElementById("txt_cedula").type=='text' && !validarCedula(document.getElementById("txt_cedula"))) {
                    alert ('Ingrese un número de cédula válido.');
                    return;
                }
                if (document.getElementById("txt_email").type=='text' && !isEmail(document.getElementById("txt_email").value,false)) {
                    alert("Ingrese una cuenta de correo válida.");
                    return;
                }

//                if(document.getElementById("div_validar_email").innerHTML!= 'OK'){
//                    alert("Ingrese un correo electrónico válido.");
//                    return;
//                }

                if (document.getElementById("txt_captcha").value=='') {
                    alert("Ingrese el código de validación que se muestra en la imagen.");
                    return;
                }
                document.formulario.action = 'cambiar_password_olvido_grabar.php';
                document.formulario.submit();
            }
        } catch (e) {}
    }
</script>
<body>
  <center>
    <form name="formulario" action="javascript:cambiar_password()" method="post">
        <br>
        <table class="borde_tab" width="90%" cellspacing="8">
            <tr>
                <th colspan="2"><center>Cambio de contrase&ntilde;as</center></th>
            </tr>
            <tr>
                <td width="50%" align="right">Ingrese su No. de c&eacute;dula o<br>el c&oacute;digo de usuario generado por Quipux</td>
                <td width="50%"><input type="text" id='txt_login' name="txt_login" size="20" maxlength="50" class="tex_area" onchange="cambiar_password_validar_usuario()"></td>
            </tr>
            <tr>
                <td colspan="2"><div id="div_validar_usuario"  ></div></td>
            </tr>
        </table>
        <br>
        <input type="submit" name="btn_accion" class="botones" value="Aceptar">
        &nbsp;&nbsp;
        <input type="button" name="btn_accion" class="botones" value="Cancelar" onclick="javascript:window.close()">
    </form>
  </center>
</body>
