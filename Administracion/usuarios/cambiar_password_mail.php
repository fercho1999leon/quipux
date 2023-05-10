<?php
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

// Genera un password aleatorio para un usuario o ciudadano y envía mail de notificación
// Se necesitan las variables:
// $usr_tipo    -- 1 para funcionarios
// $usr_codigo  -- Código del usuario
// $usr_nombre  -- Nombre del usuario
// $usr_login   -- Login
// $usr_email
// $ruta_raiz
// include funciones.php


$sql = "select usua_codi, usua_cedula, usua_email, usua_nombre, tipo_usuario
        from usuario
        where usua_esta=1 and usua_login like upper('$usr_login')
        order by tipo_usuario asc";

$rs_pass = $db->conn->query($sql);
if (!$rs_pass or $rs_pass->EOF)
    die(html_error("No se encontr&oacute; el usuario en el sistema."));

$usr_cedula = $rs_pass->fields["USUA_CEDULA"];
$usr_nombre = $rs_pass->fields["USUA_NOMBRE"];
$usr_tipo = $rs_pass->fields["TIPO_USUARIO"];
$cambio_pass_usr_codigo = $rs_pass->fields["USUA_CODI"];

$flag_ciudadano = true;
$usr_email = "";
while (!$rs_pass->EOF) {
    // Selecciono los mails de todas las cuentas; si es funcionario ya no se ponen las cuentas de los ciudadanos
    if ($rs_pass->fields["TIPO_USUARIO"]==1) $flag_ciudadano = false;
    if (trim($rs_pass->fields["USUA_EMAIL"])!="" and ($rs_pass->fields["TIPO_USUARIO"]==1 or $flag_ciudadano)) {
        $usr_email .= ",".trim($rs_pass->fields["USUA_EMAIL"]);
    }
    $rs_pass->MoveNext();
}
$usr_email = trim ($usr_email, ",");


if ($usr_email != "") {
    $clave = generar_password(30);
    $sql = "update usuarios set usua_nuevo=1, usua_pasw='".substr(md5($clave),1,26)."' where usua_cedula='$usr_cedula'";
    $db->query($sql);
    $sql = "update ciudadano set ciu_nuevo=1, ciu_pasw='".substr(md5($clave),1,26)."' where ciu_cedula='$usr_cedula'";
    $db->query($sql);
    $direccion = "$nombre_servidor/usuarionuevo.php?krd=".base64_encode($usr_login)."&code=".base64_encode($clave);

    // Enviamos un mail de notificación
    $mail = "<html><title>Informaci&oacute;n Quipux</title>";
    $mail .= "<body><center><h1>QUIPUX</h1><br /><h2>Sistema de Gesti&oacute;n Documental</h2><br /><br /></center>";
    $mail .= "Estimado(a) $usr_nombre.<br /><br />";
    $mail .= "El Sistema de Gesti&oacute;n Documental Quipux le da la bienvenida. Su cuenta ha sido registrada con el usuario &quot;<b>".substr($usr_login,1)."</b>&quot;. <br /><br />";
    $mail .= "Para poder acceder al sistema deber&aacute; definir su contrase&ntilde;a ingresando a:<br />
              <a href='$direccion' target='_blank'>$direccion</a>";
    $mail .= "<br /><br />Saludos cordiales,<br /><br />Soporte Quipux.";
    $mail .= "<br /><br /><b>Nota: </b>Este mensaje fue enviado autom&aacute;ticamente por el sistema, por favor no lo responda.";
    $mail .= "<br />Si tiene alguna inquietud respecto a este mensaje, comun&iacute;quese con <a href='mailto:$cuenta_mail_soporte'>$cuenta_mail_soporte</a>";
    $mail .= "</body></html>";
//    echo "enviarMail($mail, 'Quipux: Registro de nueva cuenta.', $usr_email, $usr_nombre, $ruta_raiz);";
    enviarMail($mail, "Quipux: Cambio de contraseña.", $usr_email, $usr_nombre, $ruta_raiz);
}
?>
