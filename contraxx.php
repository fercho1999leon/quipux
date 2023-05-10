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
/**
* Página para activacion de cuentas de usuario interno o ciudadano.
**/

// Inicializar conexiones a la bdd y validar el usuario enviado
$ruta_raiz = ".";
include_once "$ruta_raiz/funciones_interfaz.php";

include_once "$ruta_raiz/funciones.php";
$usr = strtoupper(limpiar_sql($_POST["krd"]));
$usr_tipo = strtoupper(limpiar_sql($_POST["tipo_usuario"]));
$usr_login = "U".$usr;

include_once "$ruta_raiz/include/db/ConnectionHandler.php";
$db = new ConnectionHandler($ruta_raiz);


//Destruimos la session
session_start();
unset ($_SESSION);
session_destroy();

// Buscamos el usuario y validamos si el usuario recibido es el correcto.
// Únicamente entrará a estas validaciones si es un intento de ataque.
$isql = "select * from usuario where usua_login = upper('$usr_login') order by tipo_usuario asc";
$rs=$db->query($isql);
if ($rs->EOF) {
    echo html_error("El usuario $usr no fue encontrado en el sistema.");
    die("");
}
if ($rs->fields["USUA_NUEVO"] != 0) {
    echo html_error ("El usuario $usr ya est&aacute; activo.");
    die("");
}

?>

<html>
    <?echo html_head(); /*Imprime el head definido para el sistema*/?>
    <script type="text/javascript">
        function login_olvido_contraseña() {
            windowprops = "top=50,left=50,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=750,height=550";
            url = '<?=$ruta_raiz?>/Administracion/usuarios/cambiar_password_olvido.php';
            ventana = window.open(url , "cambiar_password_quipux", windowprops);
            ventana.focus();
        }

    </script>
    <body  id="page-bg" class="f-default light_slate">
        <div id="wrapper">
        <? echo html_encabezado(); /*Imprime el encabezado del sistema*/ ?>

        <div id="mainbody">
		<div class="shad-1">
		<div class="shad-2">
		<div class="shad-3">
		<div class="shad-4">
		<div class="shad-5">

        <form name='formulario' action='usuarionuevo.php' method='post'>
            <center>
                <br><br>
            <table align="center"  cellpadding="0" cellspacing="0" class="mainbody" border=0>
                <tr>
                    <td align="center">
<?
        $usr_nombre = $rs->fields["USUA_ABR_TITULO"] . " " . $rs->fields["USUA_NOMBRE"];
        $usr_email = $rs->fields["USUA_EMAIL"];
        $usr_tipo = $rs->fields["TIPO_USUARIO"];
        $usr_codigo = $rs->fields["USUA_CODI"];
        // Validamos que la estructura del email sea correcta.
        if ($usr_email!="" and strpos($usr_email,"@") and strpos($usr_email,".",strpos($usr_email,"@"))) {
            // Generamos un password aleatorio y actualizamos el usuario.
            include_once "$ruta_raiz/Administracion/usuarios/cambiar_password_mail.php";

            // Desplegamos el mensaje
            echo "<h3>Estimado(a) $usr_nombre.<br /><br />";
            echo "Su cuenta ha sido activada. En unos minutos recibir&aacute; un mensaje a su e-mail indicandole su nueva contraseña.<br /><br />";
            echo "Para m&aacute;s informaci&oacute;n comun&iacute;quese con su administrador del sistema.<br /><br />";
            echo "Gracias por utilizar el Sistema de Gesti&oacute;n Documental QUIPUX.</h3>";
            $boton = "<input type='button' value='Aceptar' class='botones' name='btn_aceptar' onClick=\"window.location='login.php'\">";
        } else {
            // Desplegamos el mensaje. no se puede activar cuentas si no tiene un email valido
            echo "<h3>Lo sentimos, usted no tiene registrada una cuenta de correo electr&oacute;nica v&aacute;lida.<br /><br />";
            if ($usr_tipo==2) {
                $sql = "select * from ciudadano_tmp where ciu_codigo=$usr_codigo";
                $rs2 = $db->conn->query($sql);
                if ($rs2->EOF) {
                    echo "Para activar su cuenta por favor actualice sus datos en el siguiente formulario disponible en Actualizar Información.";
                    $boton = "<center>
                                <input type='button' value='Actualizar Informaci&oacute;n' class='botones_largo' name='btn_siguiente' onClick='login_olvido_contraseña()'>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type='button' value='Cancelar' class='botones_largo' name='btn_aceptar' onClick=\"window.location='login.php'\">
                              </center>";
                } else {
                    echo "Los datos que ingres&oacute; anteriormente a&uacute;n no han sido verificados por el funcionario encargado.";
                    $boton = "<center><input type='button' value='Aceptar' class='botones_largo' name='btn_aceptar' onClick=\"window.location='login.php'\"></center>";//&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                    //$boton .= "<input type='button' value='Cambiar datos' class='botones_largo' name='btn_siguiente' onClick=\"window.location='Administracion/usuarios/adm_ciudadano.php?krd=$usr_login'\"></center>";
                }
            } else {
                echo "Por favor comun&iacute;quese con su administrador del sistema.<br /><br />";
                echo "Gracias por utilizar el Sistema de Gesti&oacute;n Documental QUIPUX.</h3>";
                $boton = "<input type='button' value='Aceptar' class='botones' name='btn_aceptar' onClick=\"window.location='login.php'\">";
            }
        }
?>
                    </td>
                </tr>

                <tr>
                    <td align="center">
                        <br /><br />
                            <?=$boton?>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
            </table>
            <br><br>
            </center>
        </form>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>

        <? echo html_pie_pagina(); /*Imprime el pie de pagina del sistema*/ ?>

        </div>

    </body>
</html>
