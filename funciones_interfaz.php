<?php
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

// Maneja la interfaz del sistema, encabezados, pies de página, etc.
// Las funciones retornan código HTML y algunas funciones javascript
// Requiere que se haya definido previamente la variable $ruta_raiz

// Imprime el head de la página ya hace referencia a los estilos y todo lo necesario
function html_head ($flag_estilos=true, $flag_index=false) {
    global $ruta_raiz;
    $texto = "<head>
            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
            <title>.:: Quipux - Sistema de Gesti&oacute;n Documental ::.</title>
            <link href='$ruta_raiz/estilos/orfeo.css' rel='stylesheet' type='text/css'>
            ";
    if ($flag_estilos) {
        $texto .= " <link href='$ruta_raiz/estilos/light_slate.css' rel='stylesheet' type='text/css'>
            <link href='$ruta_raiz/estilos/splitmenu.css' rel='stylesheet' type='text/css'>
            <link href='$ruta_raiz/estilos/template_css.css' rel='stylesheet' type='text/css'>
            <link rel='shortcut icon' href='$ruta_raiz/imagenes/favicon.ico'>
            <link rel='stylesheet' type='text/css' href='$ruta_raiz/js/spiffyCal/spiffyCal_v2_1.css'>
            <link rel='stylesheet' type='text/css' href='$ruta_raiz/js/calendario_php/calendario_php.css'>";
    }

    $texto .= file_get_contents("$ruta_raiz/herramienta_monitoreo_quipux.js");
    $texto .= " <script type='text/JavaScript' src='$ruta_raiz/js/calendario_php/calendario_php.js'></script>
                <script type='text/JavaScript' src='$ruta_raiz/js/funciones_js.js'></script>
                <script type='text/JavaScript'>
                    //document.oncontextmenu = function(){return false} // Click derecho
              ";
    if (!$flag_index) {        
        $texto .= "window.focus();
                try {
                    if (window.menubar.visible || window.toolbar.visible) { // si estan activas las barras llame a index para que se bloqueen
                        if (detectarPhone()==0)
                            window.location='index.php';
                    }
                } catch (e) {}
                ";
    }

    $texto .= " ns4 = (document.layers)? true:false;
                ie4 = (document.all)? true:false;
                document.onkeydown = keyDown;
                if (ns4) document.captureEvents(Event.KEYDOWN);

                function keyDown(e){
                    var tecla, res = true;
                    if (ns4) tecla = e.which;
                    else if (ie4) tecla = event.keyCode;
                    else {
                        var evt = arguments.length ? arguments[0] : window.event;
                        tecla = evt.which;
                    }
                    switch(tecla){
                      case 116:
                      case 117:
                      case 118:
//                      case 222:
                        res = false;
                        break;
                      default:
                        res = true;
                        break;
                    }
                    //alert(res+'tecla----'+tecla);
                    return res;
                }

                function fjs_verificar_plugin_navegador (nombre_plugin) {
                    var plugin = '';
                    try {
                        for (var a = 0; a < navigator.plugins.length; a++) {
                            plugin = navigator.plugins[a];
                            if (plugin.name.toLowerCase().indexOf(nombre_plugin.toLowerCase()) >= 0)
                                return true;
                        }
                        return false;
                    } catch(e) {
                        return false;
                    }
                }
     
            </script>
            <script type='text/JavaScript' src='$ruta_raiz/js/shortcut.js'></script>            
        </head>";
    return $texto;
}

// Imprime el encabezado en páginas como login.php y otras
function html_encabezado () {
    global $ruta_raiz;
    $rsw = 1;
    $rsw=base64_encode($rsw);
    $texto = "<div id='header'><div class='shad-r'><div class='shad-l'><div class='moduletable'>
                <table width='100%' cellpadding='0' cellspacing='0' >
                    <tr>
                        <td width='18%'><img alt='Escudo' src='$ruta_raiz/imagenes/logo_ing2.png' width='150'></td>
                        <td width='52%'><h2>Sistema de gestion documental ISTRED</h2></td>
                        <td  width='30%'><div id='nav-big'>
                           <ul><table align='right'>
                                <tr><td><li class='active_menu'><a href='' class='b6' onclick='ver_ayuda()'></a></li></td></tr>
                           </table></ul></div>
                        </td>
                    </tr>
                </table>
                </div></div></div></div>
                <script>
                    function ver_ayuda() {
                        windowprops = 'top=0,left=0,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=800,height=550';
                        preview = window.open('inf_soporte.php?rsw=$rsw' , 'ayuda', windowprops);
                    }
   
                </script>";
    return $texto;
}

// Imprime el pie de página en páginas como login.php y otras
function html_pie_pagina () {
    global $ruta_raiz;
    $texto = "<div id='footer'><div class='shad-r'><div class='shad-l'><div class='tabber' id='tab'><div class='tabbertab' title='Flushed Away'>
                <table width='100%'>
                    <tr>
                        <td align='center'>
                            <center>
                                <h3>Subsecretar&iacute;a de Gobierno Electr&oacute;nico
                                - Secretar&iacute;a Nacional de la Administraci&oacute;n P&uacute;blica - 2008</h3>
                                (Basado en el sistema de gesti&oacute;n documental ORFEO <a href='http://www.orfeogpl.org'>www.orfeogpl.org</a>)
                            </center>
                        </td>
                    </tr>
                </table>
            </div></div></div></div></div>";
    return $texto;
}

// Valida el tipo de browser en login.php y otras
function html_validar_browser () {
    global $ruta_raiz;
    include "$ruta_raiz/config.php";
    if (!isset($versionEstable) or trim($versionEstable)=='') $versionEstable = 17;
    $versionClienteFox = substr($_SERVER["HTTP_USER_AGENT"], strpos($_SERVER["HTTP_USER_AGENT"], 'Firefox/') + 8);
    $texto = "<script type='text/javascript' src='$ruta_raiz/js/validar_browser.js'></script>
    <div id='check_browser'><div class='shad-1'><div class='shad-2'><div class='shad-3'><div class='shad-4'><div class='shad-5'>
    <table align='center' width='100%' cellpadding='0' cellspacing='0' class='mainbody'>
        <tr>
            <td align='center' width='100%'>
            <script type='text/javascript'>
            var version = $versionEstable;                
                if (tipo_browser[cli_browser][cli_version] != true) {
                    document.write('<p class=\"accent\">AVISO:Usted esta utilizando ' + cli_browser + ' ' + cli_version  + '</p>');
                    document.write('<p class=\"accent\">Actualmente esta versi&oacute;n de navegador no es soportada por Quipux, algunas funciones podr&iacute;an no funcionar correctamente.' + '</p>');
                    document.write('<p class=\"accent\">Le recomendamos instalar:<h2> <a href=\"http://www.mozilla.com/en-US/\" ><img width=25 height=25 src=\"$ruta_raiz/imagenes/logo_mfox.png\" alt=\"Mozilla Firefox\" title=\"Mozilla Firefox\"> </a></h2></p>');
                }else{
                    versionFirefoxCl = navigator.userAgent.split('/').pop();                    
                    if (parseInt(versionFirefoxCl)>=version) 
                    document.write('<p class=\"accent\"><font color=\"Grey\" size=\"1\">Aseguramos el correcto funcionamiento del sistema de gestión documental Quipux, con versiones inferiores a la '+version+' del navegador de Internet Mozilla Firefox. </font><img width=25 height=25 src=\"$ruta_raiz/imagenes/logo_mfox.png\" alt=\"Mozilla Firefox\" title=\"Mozilla Firefox\">' + '</p>');
                }
            </script>
            </td>
        </tr>
        
    </table>
    </div></div></div></div></div></div>";
    return $texto;
}

function html_error($mensaje, $estilos=true) {
    global $ruta_raiz;
    if ($estilos) $mensaje = "<h3>$mensaje<h3>";
    $texto = "<html>";
    $texto .= html_head();
    $texto .= html_encabezado();
    $texto .= "<body>
        <div id='wrapper'><div id='mainbody'><div class='shad-1'><div class='shad-2'><div class='shad-3'><div class='shad-4'><div class='shad-5'>
        <br /><br /><br />
        <table align='center' width='100%' cellpadding='0' cellspacing='0' class='mainbody'>
            <tr valign='top' align='center'>
                <td class='left'  align='center' width='100%'>
                    <h1>Sistema de Gesti&oacute;n Documental - QUIPUX</h1><br />
                    $mensaje
                </td>
            </tr>
        </table>
        <br /><br /><br />
        </div></div></div></div></div></div>";
    $texto .= html_pie_pagina();
    $texto .= "</div></body></html>";
    return $texto;
}

function validar_telefono_movil() {
    // Verifica si estan accediendo desde un telefono
    $mobile_browser = false;
    //$_SERVER['HTTP_USER_AGENT'] -> el agente de usuario que está accediendo a la página.
    //preg_match -> Realizar una comparación de expresión regular
    if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone)/i',strtolower($_SERVER['HTTP_USER_AGENT']))){
        $mobile_browser = true;
    }
    //$_SERVER['HTTP_ACCEPT'] -> Indica los tipos MIME que el cliente puede recibir.
    if((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml')>0) or
            ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))){
        $mobile_browser = true;
    }
    $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
    $mobile_agents = array(
            'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
            'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
            'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
            'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
            'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
            'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
            'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
            'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
            'wapr','webc','winw','winw','xda','xda-','bb');
    //buscar agentes en el array de agentes
    if(in_array($mobile_ua,$mobile_agents)){
        $mobile_browser = true;
    }
    //$_SERVER['ALL_HTTP'] -> Todas las cabeceras HTTP
    //strpos -> Primera aparicion de una cadena dentro de otra
    if(strpos(strtolower($_SERVER['ALL_HTTP']),'OperaMini')>0) {
        $mobile_browser = true;
    }
    if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows')>0) {
        $mobile_browser = false;
    }

    return $mobile_browser;
}
//validar caja de texto
//tags html para imput text
//$nomCajaTexto= name o id
//$valorTexto= value
//$numeroCaracteresTexto, numero de caracteres (configurado en el config.php)
//$titulo='', title
//$size='30', tamaño predeterminado 30 por defecto
//$busqueda='0', si es 0 borra el contenido de la caja de texto
function cajaTextoValida($nomCajaTexto,$valorTexto,$numeroCaracteresTexto,$javascript='',$titulo='',$size='30',$busqueda='0'){
    $nomCajaTexto2 = '"'.$nomCajaTexto.'"';
    $html="<input type=text id='$nomCajaTexto' name='$nomCajaTexto' value='$valorTexto' onblur='evento_ver(event,this,$numeroCaracteresTexto,$nomCajaTexto2,1); numeroCarecteresDePara(this,$numeroCaracteresTexto,$nomCajaTexto2,1);' class='tex_area'  size='$size' title='$titulo' $javascript>";
    $html.='<div id="div_'.$nomCajaTexto.'" name=id="div_'.$nomCajaTexto.'" style="display:none"><font color="red">Se requiere mayor información para el criterio de búsqueda, por favor ingrese al menos '.$numeroCaracteresTexto.' caracteres</font></div>';
    return $html;
}

function dibujarDiv($ruta_raiz,$nom_div,$numeroCaracteresTexto){
    
     $html= '<div id="'.$nom_div.'" name="'.$nom_div.'" style="display:none">
                        Se requiere más información, ingrese al menos '.$numeroCaracteresTexto                        
                    .' caracteres </div>';
     return $html;
}
?>