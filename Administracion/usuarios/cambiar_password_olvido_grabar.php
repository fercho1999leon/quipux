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

$ruta_raiz = "../..";
session_start();

include_once "$ruta_raiz/include/db/ConnectionHandler.php";
include_once "$ruta_raiz/config.php";
include_once "$ruta_raiz/funciones.php";
require_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/interconexion/validar_datos_ciudadano.php";

    $txt_login   = trim(limpiar_sql($_POST["txt_login"]));
    $txt_cedula  = trim(limpiar_sql($_POST["txt_cedula"]));
    $txt_email   = trim(limpiar_sql($_POST["txt_email"]));
    $txt_captcha = trim(limpiar_sql($_POST["txt_captcha"]));
    $txt_tipo_usr = trim(limpiar_sql($_POST["txt_tipo_usr"]));

$boton = '<br><br><input type="button" name="btn_accion" class="botones" value="Aceptar" onclick="javascript:window.close()">';

if (trim($_SESSION["captcha"]) != $txt_captcha)
    die(html_error("No coincide el código de validaci&oacute;n ingresado.$boton"));

$db = new ConnectionHandler("$ruta_raiz");
$db->conn->SetFetchMode(ADODB_FETCH_ASSOC);

$usr_login = "U".$txt_login;

include "cambiar_password_mail.php";

$pass_mensaje = "";
if ($usr_email != "") { //Si se cambió la contraseña
//    $pass_mensaje = "Se ha reiniciado su contrase&ntilde;a.<br><br>
//                   En unos minutos recibir&aacute; un email en su cuenta de correo electr&oacute;nico &quot;$usr_email&quot;
//                   con las instrucciones para que pueda ingresar su nueva contrase&ntilde;a.<br><br>
//                   Si la direcci&oacute;n de correo electr&oacute;nico que se muestra no le pertenece, por favor escriba un email a
//                   &quot;$cuenta_mail_soporte&quot; en el que indique sus datos personales y una cuenta de correo electr&oacute;nico v&aacute;lida.$boton";
   if ($txt_tipo_usr==2){
        $pass_mensaje="Sus datos se han remitido al Administrador del Sistema, para la revisi&oacute;n y autorizaci&oacute;n de los cambios. 
                   <br><br>
                   Se notificar&aacute; un correo electr&oacute;nico a &quot;$usr_email&quot cuando sus datos se actualicen y 
                   pueda reiniciar su contrase&ntilde;a.
                   <br><br>
                   Si la direcci&oacute;n de correo electr&oacute;nico que se muestra no le pertenece, por favor escriba un email a
                   &quot;$cuenta_mail_soporte&quot; en el que indique sus datos personales y una cuenta de correo electr&oacute;nico v&aacute;lida.$boton";
   }else{
        $pass_mensaje="
                   En unos minutos recibir&aacute; un email en su cuenta de correo electr&oacute;nico &quot;$usr_email&quot;
                   con las instrucciones para que pueda ingresar su nueva contrase&ntilde;a.<br><br>
                   <br><br>
                   Si la direcci&oacute;n de correo electr&oacute;nico que se muestra no le pertenece, por favor escriba un email al Administrador Institucional                   
                   en el que indique sus datos personales y una cuenta de correo electr&oacute;nico v&aacute;lida.$boton";
   }
}

if ($txt_tipo_usr==2 and ($txt_cedula!=$usr_cedula or $txt_email!="a@b.c")) { // Si es ciudadano y cambió su cédula o su email
    // Traemos los datos del ciudadano desde el registro civil
    $datos_ciudadano = &ws_validar_datos_ciudadano($txt_cedula);
    
    $txt_titulo = "Señor";
    $txt_abr_titulo = "Sr.";
    if ($datos_ciudadano["genero"] == "Femenino") {
        if (substr($datos_ciudadano["estado_civil"],0,3) == "Sol") {
            $txt_titulo = "Señorita";
            $txt_abr_titulo = "Srta.";
        } else {
            $txt_titulo = "Señora";
            $txt_abr_titulo = "Sra.";
        }
    }

    $record = array();
    $record["CIU_CODIGO"]       = $cambio_pass_usr_codigo;
    $record["CIU_CEDULA"]       = $db->conn->qstr($txt_cedula);
    $record["CIU_NOMBRE"]       = "initcap(".$db->conn->qstr(limpiar_sql(trim($datos_ciudadano["nombre"]))).")";
    $record["CIU_APELLIDO"]     = $db->conn->qstr("DATOS TRAIDOS DEL REGISTRO CIVIL");
    $record["CIU_TITULO"]       = $db->conn->qstr($txt_titulo);
    $record["CIU_ABR_TITULO"]   = $db->conn->qstr($txt_abr_titulo);
    $record["CIU_ESTADO"]   = 1;
    $record["CIU_DIRECCION"]    = $db->conn->qstr(limpiar_sql(trim($datos_ciudadano["domicilio"])));
    if ($txt_email!='a@b.c') $record["CIU_EMAIL"] = $db->conn->qstr($txt_email);
    $ok1 = $db->conn->Replace("CIUDADANO_TMP", $record, "CIU_CODIGO", false,false,false,false);
    $db->query("update ciudadano set ciu_nuevo=0 where ciu_codigo=".$cambio_pass_usr_codigo);

    if($ok1)
    {
        //Enviar correo al Super Administrador para verificar datos del ciudadano actualizado
        $mail = "<html><title>Informaci&oacute;n Quipux</title>";
        $mail .= "Estimado(a) Administrad@r:";
        $mail .= "<body><center><h1>QUIPUX</h1><br /><h2>Sistema de Gesti&oacute;n Documental</h2><br /><br /></center>";
        $mail .= "El ciudadano $usr_nombre solicit&oacute; que se reinicie su contrase&ntilde;a y "
                ."sus datos fueron validados con el Registro Civil; por favor, verificar la informaci&oacute;n.";
        $mail .= "<br /><br />Saludos cordiales,<br /><br />Soporte Quipux.";
        $mail .= "<br /><br /><b>Nota: </b>Este mensaje fue enviado autom&aacute;ticamente por el sistema, por favor no lo responda.";
        $mail .= "<br />Si tiene alguna inquietud respecto a este mensaje, comun&iacute;quese con <a href='mailto:$cuenta_mail_soporte'>$cuenta_mail_soporte</a>";
        $mail .= "</body></html>";
        enviarMail($mail, "Quipux: Actualización de datos de ciudadano.", $amd_email, "Administrador", $ruta_raiz);
    }

    if ($pass_mensaje=="") { //Mensaje en el caso que se haya cambiado la cuenta de correo electrónico
        $pass_mensaje = "Su cuenta de correo electr&oacute;nico ha sido registrada.<br><br>
                   En unos minutos recibir&aacute; un email en su cuenta de correo electr&oacute;nico &quot;$txt_email&quot; 
                   con las instrucciones para que pueda ingresar su nueva contrase&ntilde;a, 
                   previa autorizaci&oacute;n del Administrador del Sistema.<br><br>
                   Si la direcci&oacute;n de correo electr&oacute;nico que se muestra no le pertenece, por favor escriba un email a
                   &quot;$cuenta_mail_soporte&quot; en el que indique sus datos personales y una cuenta de correo electr&oacute;nico v&aacute;lida. $boton";
    }
}
if ($pass_mensaje=="") {
    $pass_mensaje = "Lo sentimos, no se pudo reiniciar su contrase&ntilde;a por favor coun&iacute;quese con soporte t&eacute;cnico
                enviando un mensaje a la cuenta de correo electr&oacute;nico &quot;$cuenta_mail_soporte&quot;
                en el que indique sus datos personales y una cuenta de correo electr&oacute;nico v&aacute;lida. $boton";
}

die(html_error($pass_mensaje));
?>
