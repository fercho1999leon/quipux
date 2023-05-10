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
* Página para cambio de contraseña de usuario interno o ciudadano.
**/

$ruta_raiz = "../..";
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/funciones.php";

if (isset($_POST["krd"])) {
    include_once  "$ruta_raiz/include/db/ConnectionHandler.php";
    $db = new ConnectionHandler($ruta_raiz);
    $krd = limpiar_sql(trim($_POST["krd"]));
    $accion_aceptar = "window.location='$ruta_raiz/login.php'";
    $flag = false;
} else {
    session_start();
    include_once "$ruta_raiz/rec_session.php";
    $krd = $_SESSION["krd"];
    if (substr($krd,0,1)=="U")
        $accion_aceptar = "window.location='$ruta_raiz/Administracion/formAdministracion.php'";
    else
        $accion_aceptar = "window.location='$ruta_raiz/cuerpo.php?carpeta=81&adodb_next_page=1'";
    $flag = true;
}
$pass_old = limpiar_sql(trim($_POST["contraold"]));
$pass_new = limpiar_sql(trim($_POST["contradrd"]));
$pass_ver = limpiar_sql(trim($_POST["contraver"]));


$isql = "select usua_pasw, usua_cedula from usuario where USUA_LOGIN = upper('$krd')";
$rs = $db->query($isql);
if ($rs->EOF) {
    echo html_error("Su usuario no fue encontrado. Por favor comun&iacute;quese con su administrador del sistema.");
    die ("");
}

$usr_pass = $rs->fields["USUA_PASW"];
$usr_cedula = $rs->fields["USUA_CEDULA"];

if ($pass_new == $pass_ver and $pass_new != "" and substr($pass_old,1,26) == $usr_pass) {
    $isql = "update usuarios set usua_pasw='".substr($pass_new,1,26)."' where usua_cedula='$usr_cedula'";
    $ok2 = $db->query($isql);
    $isql = "update ciudadano set ciu_pasw='".substr($pass_new,1,26)."' where ciu_cedula='$usr_cedula'";
    $ok2 = $db->query($isql);
    $mensaje = "Su contrase&ntilde;a ha sido cambiada exitosamente.";
} else {
    $mensaje = "No se pudo cambiar su contrase&ntilde;a.";
}

?>

<html>
    <?echo html_head(); /*Imprime el head definido para el sistema*/?>

    <body  id="page-bg" class="f-default light_slate">
    <div id="wrapper">
    <? if (!$flag) echo html_encabezado(); /*Imprime el encabezado del sistema si es nuevo usuario*/ ?>
    <div id="mainbody">
	<div class="shad-1">
	<div class="shad-2">
	<div class="shad-3">
	<div class="shad-4">
	<div class="shad-5">

        <form name='formulario' class="moduletable">
        <center>
            <br /><br />
            <table align="center" cellpadding="0" cellspacing="0" class="mainbody" border=0>
                <tr>
                    <td align="center">
                        <h3><?=$mensaje?></h3>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <br />
                        <input type='button' value='Aceptar' class='botones' name='btn_aceptar' onClick="<?=$accion_aceptar?>">
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
            </table>
        </center>
        </form>
    </div>
    </div>
    </div>
    </div>
    </div>
    </div>
    <? if (!$flag) echo html_pie_pagina(); /*Imprime el pie de pagina del sistema si es nuevo usuario*/ ?>
    </div>

    </body>
</html>
