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
session_start();
include_once "$ruta_raiz/rec_session.php";
require_once "$ruta_raiz/funciones.php";
include_once "$ruta_raiz/funciones_interfaz.php";

echo "<html>".html_head();
include_once "$ruta_raiz/js/ajax.js";
?>
<script language="JavaScript" type="text/JavaScript">
    
    function cerrar_session() {
        if (confirm('¿Está seguro de Cerrar la Sesión?'))
        {
            document.formulario.action = 'cerrar_session.php?accion=cerrar';
            document.formulario.submit();
        }
    }

    function reiniciar_session(){
        document.formulario.action = 'reiniciar_session.php';
        document.formulario.submit();
    }

    function popup_firma_digital() {
        var x = (screen.width) / 2;
        var y = (screen.height) / 2;
        windowprops = "top=100,left=100,scrollbars=yes, resizable=yes,width="+x+",height="+y;
        url = "http://firmadigital.informatica.gob.ec";
        ventana = window.open(url , "FirmaDigital");//, windowprops);
        ventana.focus();
    }

    var topTimerId = 0;
    var topTimerIdVerifica = 0;
    var topIntentosVerifica = 0; // Para que si no encuentra la alerta no se quede colgado
    var codigo_mensaje_alerta_top = '';

    function cargar_alerta_mensaje() {
        clearTimeout(topTimerId);
        nuevoAjax('div_mensaje_alerta', 'POST', './bodega/mensaje_alerta_top.html');
        topTimerIdVerifica = setInterval( "validar_cambio_mensaje()", 5000 ); // Verifica si el mensaje se modifico
        topTimerId = setInterval( "cargar_alerta_mensaje()", 300000 ); // Cada 5 minutos recarga el mensaje
    }

    function validar_cambio_mensaje() {
        try {
            if (document.getElementById('txt_codigo_mensaje_alerta_top').innerHTML != codigo_mensaje_alerta_top) {
                codigo_mensaje_alerta_top = document.getElementById('txt_codigo_mensaje_alerta_top').innerHTML;
                document.getElementById('div_mensaje_alerta').style.display = '';
            }
        } catch (e) { // Si no se cargo aun el mensaje
            if (++topIntentosVerifica <= 3) //Valida para que no se quede en un lazo infinito
                topTimerIdVerifica = setInterval( "validar_cambio_mensaje()", 5000 );
        }
        clearTimeout(topTimerIdVerifica);
    }

    function ocultar_mensaje_alerta() {
        document.getElementById('div_mensaje_alerta').style.display = 'none';
    }
</script>
<body  class="f-default light_slate bg-primary" onload="cargar_alerta_mensaje()">
  <form name='formulario' action="" target="_parent" method="post"> 
    <div id="header" >
        <div id="div_mensaje_alerta" style="height: 70px; width: 522px; overflow: auto; position: fixed; top: 2px; right: 210px; z-index: 5; display: none;"></div>
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr height="74px">
                <td width="10%" align="rigth" valign="middle">&nbsp;&nbsp;&nbsp;
                    <img src="<?=$ruta_raiz?>/imagenes/logo_ing2.png" width="100" alt="Quipux"/></td>
                <td width="70%">
                    <h2 class="text-light">Sistema de gestion documental ISTRED</h2>
                </td>
                <td width="20%">
                    <div id="nav-big">
                        <table align='right'>
                            <tr>
                                <td>
                                    <br>
                                </td>
                                <!--<td>
                                    <ul>
                                        <li class="active_menu">
                                            <a href='#' onClick="popup_firma_digital();" class="b20" target="_self"></a>
                                        </li>
                                    </ul>
                                </td>-->
                                <td>
                                    <ul>
                                        <li class="active_menu d-flex flex-row-reverse align-items-center ">
                                            <a href="inf_soporte.php" id="help-animation"  class="b6 p-2 d-flex flex-row-reverse align-items-center" target="mainFrame"><i class="fa-solid fa-circle-question text-light " style="font-size: 2rem;"></i></a>
                                        </li>
                                    </ul>
                                </td>
                                <td>
                                    <ul>
                                        <li class="active_menu d-flex flex-row-reverse align-items-center ">
                                            <a href='#' onClick="cerrar_session();" id="close-animation" class="b51 p-2 d-flex flex-row-reverse align-items-center"><i class="fa-solid fa-arrow-right-from-bracket text-light " style="font-size: 2rem;"></i></a>
                                        </li>
                                    </ul>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
            <tr >
                <td width="100%" colspan="3" align="left" style="height: 22px; vertical-align: middle;">
                    <?php require "$ruta_raiz/cargo_usuario.php";?>
                </td>
            </tr>
        </table>
    </div>
  </form>
  
  <script>
    
    $('#help-animation').on('mouseenter',function(){
        $('#help-animation > svg').addClass('fa-bounce');
    });
    $('#help-animation').on('mouseleave',function(){
        $('#help-animation > svg').removeClass('fa-bounce');
    });

    $('#close-animation').on('mouseenter',function(){
        $('#close-animation > svg').addClass('fa-beat-fade');
    });
    $('#close-animation').on('mouseleave',function(){
        $('#close-animation > svg').removeClass('fa-beat-fade');
    });
  </script>
  <?php echo html_pie_pagina() ?>
</body>
</html>