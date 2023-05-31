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
$usua_nuevo = 3;
error_reporting(0);
include_once "config.php";

$txt_administrador = 0 + $_GET["txt_administrador"];
if ($activar_bloqueo_sistema and $txt_administrador != 1) {
    if (is_file("./bodega/mensaje_bloqueo_sistema.html")) {
        include_once "$ruta_raiz/funciones_interfaz.php";
        $mensaje = file_get_contents("./bodega/mensaje_bloqueo_sistema.html");
        die(html_error($mensaje));
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
    if (!isset($_SESSION['initiated']) && isset($_SESSION["krd"])) {
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
    if ($usua_nuevo == 0) {
        include($ruta_raiz . "/contraxx.php");
        die("");
    }
    if (isset($_SESSION["krd"])) {
        echo "<script>window.location = 'index_frames.php';</script>";
        die("");
    }
}
include_once "funciones_interfaz.php";
// En caso de bloqueo general del sistema
$mensaje = "Estamos experimentando dificultades t&eacute;cnicas.<br>Por favor vuelva a intentarlo m&aacute;s tarde.";
$mensaje .= "<br><br>Para ir a la pantalla de ingreso, haga click&nbsp<a href=\"$ruta_raiz/login.php\" target=\"_parent\" class=\"aqui\">&quot;AQUI&quot;</a>";
//    die (html_error($mensaje));
?>
<html>
<? echo html_head(); /*Imprime el head definido para el sistema*/ ?>
<style type="text/css">
    a:link,
    a:visited,
    a:hover {
        color: blue;
    }
    .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
    }

    @media (min-width: 768px) {
        .bd-placeholder-img-lg {
        font-size: 3.5rem;
        }
    }

    .b-example-divider {
        width: 100%;
        height: 3rem;
        background-color: rgba(0, 0, 0, 0.1);
        border: solid rgba(0, 0, 0, 0.15);
        border-width: 1px 0;
        box-shadow: inset 0 0.5em 1.5em rgba(0, 0, 0, 0.1),
        inset 0 0.125em 0.5em rgba(0, 0, 0, 0.15);
    }

    .b-example-vr {
        flex-shrink: 0;
        width: 1.5rem;
        height: 100vh;
    }

    .bi {
        vertical-align: -0.125em;
        fill: currentColor;
    }

    .nav-scroller {
        position: relative;
        z-index: 2;
        height: 2.75rem;
        overflow-y: hidden;
    }

    .nav-scroller .nav {
        display: flex;
        flex-wrap: nowrap;
        padding-bottom: 1rem;
        margin-top: -1px;
        overflow-x: auto;
        text-align: center;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
    }

    .btn-bd-primary {
        --bd-violet-bg: #712cf9;
        --bd-violet-rgb: 112.520718, 44.062154, 249.437846;

        --bs-btn-font-weight: 600;
        --bs-btn-color: var(--bs-white);
        --bs-btn-bg: var(--bd-violet-bg);
        --bs-btn-border-color: var(--bd-violet-bg);
        --bs-btn-hover-color: var(--bs-white);
        --bs-btn-hover-bg: #6528e0;
        --bs-btn-hover-border-color: #6528e0;
        --bs-btn-focus-shadow-rgb: var(--bd-violet-rgb);
        --bs-btn-active-color: var(--bs-btn-hover-color);
        --bs-btn-active-bg: #5a23c8;
        --bs-btn-active-border-color: #5a23c8;
    }
    .bd-mode-toggle {
        z-index: 1500;
    }
    .img-circle::before {
        content:'';
        position: absolute;
        transform: translate(-50%,-70%);
        width: 120px; /* Ajusta el tamaño del círculo */
        height: 120px; /* Ajusta el tamaño del círculo */
        border-radius: 50%;
        border: 8px solid #e9ecef;
        background-color: #e9ecef;
      }

      .img-circle > img {
        content:'';
        position: absolute;
        transform: translate(-50%,-70%);
        width: 120px; /* Ajusta el tamaño del círculo */
        height: 120px; /* Ajusta el tamaño del círculo */
        border-radius: 50%;
        border: 8px solid #e9ecef;
        
      }

      body {
        background-image: url(<?echo "$ruta_raiz/img/logos/anniversary-istred.png"; ?>);
        background-repeat: no-repeat;
        background-size:cover;
      }

      main {
        opacity: 0.9;
      }
</style>

<script type="text/javascript" src="<?= $ruta_raiz ?>/js/md5.js"></script>
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
            url = '<?= $ruta_raiz ?>/Administracion/usuarios/cambiar_password_olvido.php';
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
                document.form_login.action = 'login.php?acceso=login&txt_administrador=<?= $txt_administrador ?>';
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

<body class="text-center" onLoad='document.getElementById("krd").focus();'>

    <div class="d-flex flex-column vh-100">
        <main class="form-signin m-auto bg-body-secondary p-4 rounded">
            <form name="form_login" action="" method="post" onSubmit="return validar_login();">
                <div class="img-circle" style="height: 40px; ">
                    <img src=<?echo "$ruta_raiz/img/logos/logo-istred-v2.png"; ?> width="120px" height="120px">
                </div>
                <h1 class="h3 mb-3 fw-normal">Ingreso de Usuarios al Sistema</h1>

                <div class="form-floating m-3">
                    <input type="text" class="form-control" placeholder="0000000000" id='krd' name="krd" size="20" maxlength="50" required/>
                    <label for="krd">Cedula</label>
                </div>
                <div class="form-floating m-3">
                    <input type="password" class="form-control" id="drd" placeholder="Password" name="drd" size="20" required/>
                    <label for="drd">Contraseña</label>
                </div>

                <div class="mb-3">
                    <label>
                        <a href="javascript:login_olvido_contraseña()">¿Olvidó su contraseña?</a>
                    </label>
                </div>
                <button class="btn btn-lg btn-primary" name="Submit" type="submit">Ingresar</button>

                <button class="btn btn-lg btn-primary" type="reset" name="reset">Borrar</button>

                <p class="mt-5 mb-3 text-body-secondary">&copy; 2023–2024</p>
            </form>
        </main>
    </div>

    <? echo html_pie_pagina(); /*Imprime el pie de pagina del sistema*/ ?>
</body>



</html>