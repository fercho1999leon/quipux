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

$ruta_raiz = ".";
$usua_nuevo=3;
error_reporting(0);
include_once "config.php";

$txt_administrador = 0 + $_GET["txt_administrador"];
if ($activar_bloqueo_sistema and $txt_administrador != 1) {
    if (is_file("./bodega/mensaje_bloqueo_sistema.html")) {
        include_once "$ruta_raiz/funciones_interfaz.php";
        $mensaje = file_get_contents("./bodega/mensaje_bloqueo_sistema.html");
        die (html_error($mensaje));
    }
}
    /**
    * Verificar si el campo usuario (login) es diferente de vacio
    **/
    $krd = $_POST['krd'];
    if ($krd) {
        // Validar si el usuario y contraseña son corectos
        include_once "$ruta_raiz/session_orfeo.php";
        
        require_once "$ruta_raiz/securesession.class.php";  //Esta clase maneja seguridad en sessiones

        //Cambios contra Session Fixation
        if (!isset($_SESSION['initiated']) && isset($_SESSION["krd"]))
        {
          $ss = new SecureSession();
          $ss->check_browser = true;
          $ss->check_ip_blocks = 2;
          $ss->secure_word = 'QUIPUX_COMUNIDAD_V4';
          $ss->regenerate_id = false; //true;
          $ss->Open();
          //  session_regenerate_id();
            $_SESSION['initiated'] = true;
        }

	// Verificar si es usuario nuevo o no en caso de ser usuario nuevo pide cambio de contraseña.
    	if($usua_nuevo==0) {
            include($ruta_raiz."/contraxx.php");
            die("");
        }
        if (isset($_SESSION["krd"])) {
            echo "<script>window.location = 'index_frames.php';</script>";
            die ("");
        }
    }
    include_once "funciones_interfaz.php";
    // En caso de bloqueo general del sistema
    $mensaje = "Estamos experimentando dificultades t&eacute;cnicas.<br>Por favor vuelva a intentarlo m&aacute;s tarde.";
    $mensaje .= "<br><br>Para ir a la pantalla de ingreso, haga click&nbsp<a href=\"$ruta_raiz/login.php\" target=\"_parent\" class=\"aqui\">&quot;AQUI&quot;</a>";
//    die (html_error($mensaje));
?>
<html>
    <?echo html_head(); /*Imprime el head definido para el sistema*/?>
    <style type="text/css"> a:link, a:visited, a:hover {color: blue;} </style>

    <script type="text/javascript" src="<?=$ruta_raiz?>/js/md5.js"></script>
    <? include_once "$ruta_raiz/js/ajax.js"; ?>
    <script language="JavaScript" type="text/JavaScript">
    <!--
        var intento_login = true;
        var timerID;

        function trim(s) {
            return s = s.replace(/^\s+|\s+$/gi, '');
        }

        function login_olvido_contraseña() {
            windowprops = "top=50,left=50,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=750,height=550";
            url = '<?=$ruta_raiz?>/Administracion/usuarios/cambiar_password_olvido.php';
            ventana = window.open(url , "cambiar_password_quipux", windowprops);
            ventana.focus();
        }

       function validar_login () {
            if (!intento_login) {
                // Si ya se hizo submit bloquea el submit para que no se haga varias veces (teniendo presionada la tecla enter)
                // Ataque de negacion de servicios
                return false;
            }
            usr = trim(document.getElementById("krd").value);
            pass = trim(document.getElementById("drd").value);
            flag = true;
            if (usr.length == 0) {
                flag = false;
                document.getElementById("krd").focus();
            }
            if (pass.length == 0) {
                flag = false;
                document.getElementById("drd").focus();
            }
            if (flag) {
                intento_login = false;
                timerID = setTimeout("activar_intento_login()", 2000);
                document.form_login.action = 'login.php?acceso=login&txt_administrador=<?=$txt_administrador?>';
                document.getElementById("drd").value = MD5(document.getElementById("drd").value);
                document.form_login.submit();
            } else {
                alert('Asegúrese de ingresar su usuario y contraseña.'); //\nSi es la primera vez que ingresa al sistema, su contraseña es "123"');
            }
            return flag;
        }

        function activar_intento_login() {
            clearTimeout(timerID);
            intento_login = true;
            return;
        }
        function detectarPhone(){
            var navegador = navigator.userAgent.toLowerCase();
            if ( navigator.userAgent.match(/iPad/i) != null)//detectar ipad
              return 2;
            else{//detectar phone        
                if( navegador.search(/iphone|ipod|blackberry|android/) > -1 )
                   return 1;    
                else 
                    return 0;
            }
        }
        
        window.focus();
        
        if (window.menubar.visible || window.toolbar.visible) { // si estan activas las barras llame a index para que se bloqueen
          if (detectarPhone()==0)
            window.location="index.php";
        }

        // -->
    </script>


    <body class="f-default light_slate" onLoad='document.getElementById("krd").focus();'>
        <div id="wrapper">
        <? echo html_encabezado(); /*Imprime el encabezado del sistema*/ ?>
        <div id="mainbody">
            <div class="shad-1">
                <div class="shad-2">
                    <div class="shad-3">
                        <div class="shad-4">
                            <div class="shad-5">
<table align="center" width="100%" cellpadding="0" cellspacing="0"><!-- class="mainbody"-->

 <tr valign="top" align="center">
	<td class="left"  align="center" width="100%">
	<div class="moduletable">
        <h1>Ingreso de Usuarios al sistema</h1>
        <hr />
        <table cellspacing="3" cellpadding="0" border="0" align="center" width="100%">
        <tbody>
            <tr>
                <td align="center" width="100%">
                    <? echo html_validar_browser(); /*Valida el browser*/ ?>
                </td>
            </tr>
            <tr >
                <td width="100%" align="center">                    
        <form name="form_login" action="" method="post" onSubmit="return validar_login();">
            <table width="350" cellpadding="0" cellspacing="7">
                <tr>
                    <td align="center" colspan="2"><h2> Por favor Ingrese su n&uacute;mero de C&eacute;dula y contraseña </h2></td>
                </tr>
                <tr>
                    <td align="center" colspan="2"> </td>
                </tr>
                <tr>
                    <td align="right" width="30%">C&eacute;dula:</td>
<!--                    <td width="70%"><input type="text" id='krd' name="krd" size="20" maxlength="15" class="tex_area" onchange="login_buscar_tipo_usuario()"></td>-->
                    <td width="70%"><input type="text" id='krd' name="krd" size="20" maxlength="50" class="tex_area"></td>
            	</tr>
                <tr>
                    <td align="right">Contrase&ntilde;a:</td>
                    <td><input type=password name="drd" id="drd" size="20" class="tex_area"></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div id="div_tipo_usuario"></div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <a href="javascript:login_olvido_contraseña()">¿Olvid&oacute; su contrase&ntilde;a?</a>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <br>
                        <input name="Submit" type="submit" class="botones" value="Ingresar">
                        &nbsp;&nbsp;
                        <input type="reset" value="Borrar" class="botones" name="reset">
                    </td>
                </tr>
            </table>
        </form>
                     </td>
                 </tr>
            </tbody>
            </table>
        </div>
        </td>
    </tr>
</table>


                        </div>
                     </div>
                </div>
             </div>
         </div>
      </div>
<br><br>

        <? echo html_pie_pagina(); /*Imprime el pie de pagina del sistema*/ ?>
        </div>
 
    <script type="text/javascript">
//        if (trim(document.getElementById("krd").value) != "") {
//            login_buscar_tipo_usuario();
//        }
    </script>
</body>
</html>
