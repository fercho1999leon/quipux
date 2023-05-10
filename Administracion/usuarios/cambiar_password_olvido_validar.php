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
include_once "$ruta_raiz/include/db/ConnectionHandler.php";
include_once "$ruta_raiz/config.php";
include_once "$ruta_raiz/funciones.php";
$db = new ConnectionHandler("$ruta_raiz");
$db->conn->SetFetchMode(ADODB_FETCH_ASSOC);

$txt_login = trim(limpiar_sql($_POST["txt_login"]));
$tipo_usuario = "";

$txt = "";
$txt_ok = "0";

// Verificamos si es usuario
$sql = "select usua_cedula, usua_email, tipo_usuario from usuario where usua_esta=1 and usua_codi<>0 and usua_login like upper('U$txt_login') order by tipo_usuario asc";

$rs = $db->query($sql);

if ($rs and !$rs->EOF) {
    $txt_cedula = "<input type='hidden' id='txt_cedula' name='txt_cedula' value='".$rs->fields["USUA_CEDULA"]."'>";
    $txt_email  = "<input type='hidden' id='txt_email'  name='txt_email'  value='a@b.c'>";
    $txt_tipo_usr  = "<input type='hidden' id='txt_tipo_usr'  name='txt_tipo_usr'  value='".$rs->fields["TIPO_USUARIO"]."'>";
    $txt .= "<table border='0' cellpadding='0' cellspacing='8' width='100%'>
                <tr>
                    <td colspan='2' align='center' valign='middle'>
                        <b>Para cambiar su contrase&ntilde;a requerimos que ingrese la siguiente información:</b>
                    </td>
                </tr>";
    // Verificamos si es ciudadano y no tiene cédula o email
    if ($rs->fields["TIPO_USUARIO"]==2 and (substr($rs->fields["USUA_CEDULA"],0,2)=="99" or trim($rs->fields["USUA_EMAIL"]==""))) {
        if (substr($rs->fields["USUA_CEDULA"],0,2)=="99") {
            $txt .= "<tr>
                        <td align='right' width='50%'>No. de c&eacute;dula:</td>
                        <td width='50%'><input type='text' id='txt_cedula' name='txt_cedula' size='20' maxlength='13' class='tex_area'></td>
                    </tr>";
            $txt_cedula = "";
        }
        if (trim($rs->fields["USUA_EMAIL"]=="")) {
            $txt.= "<tr>
                        <td align='right' width='50%'>Direcci&oacute;n de correo electr&oacute;nico:</td>
                        <td width='50%'><input type='text' id='txt_email' name='txt_email' size='50' maxlength='80' class='tex_area'></td>
                    </tr>";
            $txt_email = "";
        }
    }
    $txt .= "<tr>
                <td colspan='2' align='center' valign='middle'><br>Por favor ingrese el texto que se muestra en la imagen</td>
            </tr>
            <tr>
                <td valign='middle' align='right'>
                    <img src='$ruta_raiz/js/captcha.php' width='100' height='30'
                         title='Por favor ingrese las letras mostradas en la imagen' alt=''>
                </td>
                <td valign='middle'>
                    <input type='text' id='txt_captcha' name='txt_captcha' size='15' maxlength='5' class='tex_area'
                           title='Por favor ingrese las letras mostradas en la imagen'>
                </td>
            </tr>
          </table>";
    $txt_ok = "1";
} else {    
    $txt_cedula =""; $txt_email = ""; $txt_tipo_usr = "";
    $txt .= "<b>No se encontr&oacute; ning&uacute;n usuario o ciudadano que coincida con ese n&uacute;mero de c&eacute;dula</b><br>&nbsp;";
}

$txt = "<center>
            <br>$txt $txt_cedula $txt_email $txt_tipo_usr
            <input type='hidden' id='txt_login' name='txt_login' value='$txt_login'>
            <input type='hidden' name='txt_ok' id='txt_ok' value='$txt_ok'>
        </center>";

echo $txt;

?>
