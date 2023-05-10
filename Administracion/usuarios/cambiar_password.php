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

if (isset($_GET["krd"]) && isset($_GET["code"])) {
    $flag = false;
    include_once ("$ruta_raiz/include/db/ConnectionHandler.php");
    include_once "$ruta_raiz/funciones.php";
    $db = new ConnectionHandler("$ruta_raiz");
    $krd = limpiar_sql(base64_decode($_GET["krd"]));
    $contraold = base64_decode($_GET["code"]);
    $sql = "select usua_pasw from usuario where USUA_LOGIN = upper('$krd')";
    $rs = $db->query($sql);
    if ($rs->EOF) {
        echo html_error("Su usuario no fue encontrado. Por favor comun&iacute;quese con su administrador del sistema.");
        die ("");
    }
    if (substr(md5($contraold),1,26) != $rs->fields["USUA_PASW"]) {
        echo html_error("Su contrase&ntilde;a ya fue cambiada. Por favor comun&iacute;quese con su administrador del sistema.");
        die ("");
    }
    $accion_cancelar="window.close()";
} else {
    $flag = true;
    session_start();
    include_once "$ruta_raiz/rec_session.php";
    $krd = $_SESSION["krd"];
    $accion_cancelar="history.back()";//window.location='$ruta_raiz/cuerpo.php?carpeta=81&adodb_next_page=1'";
}
?>

<html>
    <?echo html_head(); /*Imprime el head definido para el sistema*/?>

    <script type="text/javascript" src="<?=$ruta_raiz?>/js/md5.js"></script>
    <script language="JavaScript" type="text/JavaScript">

        function trim(s) {
            return s = s.replace(/^\s+|\s+$/gi, '');
        }

        function validar_formulario() {
            if (trim(document.getElementById('contraold').value).length==0) {
                alert ('Por favor ingrese su contraseña anterior');
                document.getElementById('contraold').focus();
                return false;
            }
            if (trim(document.getElementById('contradrd').value).length==0) {
                alert ('Por favor ingrese su nueva contraseña');
                document.getElementById('contradrd').focus();
                return false;
            }
            if (trim(document.getElementById('contraver').value).length==0) {
                alert ('Por favor vuelva a ingresar su nueva contraseña');
                document.getElementById('contraver').focus();
                return false;
            }
            
            if(!valida_caracteres(trim(document.getElementById('contradrd').value), 15)){
                alert ('La contraseña debe tener una longitud maxima de 15 caracteres \n y contener números y letras, por favor vuelva a ingresarla');
                document.getElementById('contradrd').value='';
                document.getElementById('contraver').value='';
                document.getElementById('contradrd').focus();
                return false;
            }
            
            if (!seguridad_password(trim(document.getElementById('contradrd').value))) {
                alert ('La contraseña debe tener una longitud mínima de 6 caracteres \n y contener números y letras, por favor vuelva a ingresarla');
                document.getElementById('contradrd').value='';
                document.getElementById('contraver').value='';
                document.getElementById('contradrd').focus();
                return false;
            }

            if (trim(document.getElementById('contradrd').value) != trim(document.getElementById('contraver').value)) {
                alert ('Las contraseñas no coinciden, por favor vuelva a ingresarlas');
                document.getElementById('contradrd').value='';
                document.getElementById('contraver').value='';
                document.getElementById('contradrd').focus();
                return false;
            }
            
            document.getElementById('contraold').value = MD5(document.getElementById('contraold').value);
            document.getElementById('contradrd').value = MD5(document.getElementById('contradrd').value);
            document.getElementById('contraver').value = MD5(document.getElementById('contraver').value);
            
            document.formulario.submit();
        }

        function seguridad_password(password) {
            flag_txt = false;
            flag_num = false;
            cad_num = '0123456789';
            cad_txt = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

            if (password.length<6) return false;

            for (i=0; i<password.length; i++) {
                if (cad_num.indexOf(password.charAt(i),0)!=-1)
                    flag_num=true;
                if (cad_txt.indexOf(password.charAt(i),0)!=-1)
                    flag_txt=true;
            }

            if (flag_txt && flag_num) return true;
            return false;
        }

        if (window.menubar.visible || window.toolbar.visible) { // si estan activas las barras llame a index para que se bloqueen
            window.location="<?=$ruta_raiz?>/index.php";
        }       
       
        
        function valida_caracteres(txtElemento, maxCaracteres) {   
            if (txtElemento.length > maxCaracteres) return false;           
            return true;
        }
    </script>

    <body  id="page-bg" class="f-default light_slate" valign='center'>
    <div id="wrapper">
    <? if (!$flag) echo html_encabezado(); /*Imprime el encabezado del sistema si es nuevo usuario*/ ?>
    <div id="mainbody">
	<div class="shad-1">
	<div class="shad-2">
	<div class="shad-3">
	<div class="shad-4">
	<div class="shad-5">

        <form name='formulario' action='cambiar_password_grabar.php' method=post>
        <center>
            <table align="center"  cellpadding="0" cellspacing="0" class="mainbody" border=0>
                <tr>
                    <td align="center">
                        <h3>Por favor ingrese los siguientes datos</h3>
                    </td>
                </tr>
                <tr>
                    <td  align="center">
                        <table border="0" cellpadding="0" cellspacing="7" align="center" width="350">
                    	    <tr>
                                <td align="right">Usuario:</td>
                                <td ><?=substr($krd,1)?><br></td>
                            </tr>
                            <? if ($flag) { ?>
                    	    <tr>
                                <td align="right">Contrase&ntilde;a anterior:</td>
                                <td ><input type='password' name='contraold' id='contraold' value='' class=tex_area><br></td>
                            </tr>
                            <? } else {
                                echo "<input type='hidden' name='krd' id='krd' value='$krd'>";
                                echo "<input type='hidden' name='contraold' id='contraold' value='$contraold'>";
                            }
                            ?>
                            <tr>
                                <td align="right">Contrase&ntilde;a: </td>
                                <td><input type=password name='contradrd' id='contradrd' value='' class=tex_area>
                                    <font color="blue">*</font>
                                    <br></td>
                            </tr>
                            <tr >
                                <td align="right">Re-escriba la contrase&ntilde;a: </td>                                
                                <td><input type=password name='contraver' id='contraver' class=tex_area value=''>
                                     <font color="blue">*</font>
                                </td>
                            </tr> 
                             <tr >    
                                <td align ="center" colspan="2"><br></td>
                            </tr> 
                             <tr >    
                                <td align ="center" colspan="2"><font color="blue">* La contraseña debe ser de mínimo 6 caracteres y máximo 15, entre números y letras.</font></td>
                            </tr>    
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <br /><br />
                        <input type='button' value='Aceptar' class='botones' name='btn_aceptar' onClick='validar_formulario();'>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <input type='button' value='Regresar' class='botones' name='btn_cancelar' onClick="<?=$accion_cancelar?>">
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
    <? if (!$flag) echo html_pie_pagina(); /*Imprime el pie de pagina del sistema*/ ?>

    </div>

    </body>
</html>



