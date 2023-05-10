<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
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
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/config.php";
include_once ("$ruta_raiz/include/db/ConnectionHandler.php");

if (!isset ($config_db_replica_contenido_index)) $config_db_replica_contenido_index = "";
$db = new ConnectionHandler($ruta_raiz,$config_db_replica_contenido_index);
$db->conn->SetFetchMode(ADODB_FETCH_ASSOC);

$implanta = "";
$titulo_implanta = "";
$procedimiento = "";
$titulo_procedimiento = "";
$soporte = "";
$titulo_soporte = "";
            
//Se consulta contenido de catalogos de la pagina Index
$sql = "select * from contenido c
left outer join contenido_tipo  ct
on c.cont_tipo_codi = ct.cont_tipo_codi
where ct.funcionalidad = 'Index'";
$rs = $db->conn->query($sql);
if($rs){
    while (!$rs->EOF) {    
        $cont_tipo_codo = $rs->fields['CONT_TIPO_CODI'];
        switch ($cont_tipo_codo) {
            case "1": 
                $desc_implanta = $rs->fields['TEXTO'];
                $titulo_implanta = "<h2>".$rs->fields['DESCRIPCION']."</h2>"; 
                break;
            case "2": 
                $desc_procedimiento = $rs->fields['TEXTO'];
                $titulo_procedimiento = "<h2>".$rs->fields['DESCRIPCION']."</h2>";
                break;
            case "3":
                $desc_soporte = $rs->fields['TEXTO'];
                $titulo_soporte = "<h2>".$rs->fields['DESCRIPCION']."</h2>";
                break;
            default:
                break;
        }   
        $rs->MoveNext();
    }
}

//$boton = '<input onclick="irLogin(0);" name="Submit" type="submit" class="botones_index" value="Ingresar al Sistema">';
$boton = '<a href="#" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage(\'boton ingreso\',\'\',\'imagenes/index/marco-boton-03.png\',1)">
            <img src="imagenes/index/boton.png" width="127" height="27" border="0" id="boton ingreso" onclick="irLogin(0);" />
          </a>';
//$boton ="<a href='#' onmouseout='MM_swapImgRestore()' onmouseover='MM_swapImage('\boton ingreso\','','\imagenes/index/marco-boton-03.png\',1)'>
                        //<img src='imagenes/index/boton.png' width='100%' height='27' border='0' id='boton ingreso' /></a>";
$boton_oculto = '';
if ($activar_bloqueo_sistema) {
    if (is_file("./bodega/mensaje_bloqueo_sistema.html")) {
        $mensaje = file_get_contents("./bodega/mensaje_bloqueo_sistema.html");
        $boton = '<div id="div_mensaje_alerta_top" style="border: #B40404 1px solid; width: 450px; height: 65px; overflow: auto; background-color: #F5A9A9; -moz-border-radius:10px; -webkit-border-radius:10px;">
                    <center>
                        <table width=96% border="0" cellpadding="0" cellspacing="2">
                            <tr>
                                <td style="width: 100%; font-size: 10px; text-align: left; color: ;">'.$mensaje.'</td>
                            </tr>
                          </table>
                      </center>
                    </div>
                    <script type="text/javascript">indexTimerId = setTimeout("window.location.reload();", 300000);</script>';
        $boton_oculto = '<a href="javascript: void(0);" onclick="irLogin(1);" style="color: none;">...</a>';
    }
}

echo "<html>".html_head(false,true); /*Imprime el head definido para el sistema*/
echo html_validar_browser(); /*Valida el browser*/
?>
<html>
    <head>
        <title>Quipux - Sistema de Gestión Documental</title>
        <link rel='shortcut icon' href='./imagenes/favicon.ico'>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="description" content="Quipux Sistema de Gestión Documental - Instituto Superior Tecnico Rey David" />
        <meta name="keywords" content="Quipux Istred, Itred, Sistema de Gestión Documental Istred, Sistema de Gestión Documental Itred, SGD ITRED, SGD ISTRED"/>
        <link rel="stylesheet" href="estilos/style.css" type="text/css" media="screen"/>     
        <!-- Persiana JavaScript -->
        <script type="text/javascript" src="js/jquery.min.js"></script>
        <script type="text/javascript">
            $(function() {
                $('#accordion > li').hover(
                    function () {
                        var $this = $(this);
                        $this.stop().animate({'width':'665px'},500);
                        $('.heading',$this).stop(true,true).fadeOut();
						$('.imagen1',$this).stop(true,true).fadeOut();
						$('.imagen2',$this).stop(true,true).fadeOut();
						$('.imagen3',$this).stop(true,true).fadeOut();
                        $('.bgDescription',$this).stop(true,true).slideDown(500);
                        $('.description',$this).stop(true,true).fadeIn();
                    },
                    function () {
                        var $this = $(this);
                        $this.stop().animate({'width':'170px'},500);
                        $('.heading',$this).stop(true,true).fadeIn();
						$('.imagen1',$this).stop(true,true).fadeIn();
						$('.imagen2',$this).stop(true,true).fadeIn();
						$('.imagen3',$this).stop(true,true).fadeIn();
                        $('.description',$this).stop(true,true).fadeOut(500);
                        $('.bgDescription',$this).stop(true,true).slideUp(700);
                    }
                );
            });
            <!-- Fin Persiana JavaScript -->
            function MM_swapImgRestore() { //v3.0
            var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
            }
            function MM_preloadImages() { //v3.0
            var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
                var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
                if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
            }

            function MM_findObj(n, d) { //v4.01
            var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
                d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
            if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
            for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
            if(!x && d.getElementById) x=d.getElementById(n); return x;
            }

            function MM_swapImage() { //v3.0
            var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
            if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
            }
        </script>
        <!--
        *********************************
        Start of necessary jFlow scripts: 
        *********************************
        -->
        <link href="js/jflow/styles/jflow.style.css" type="text/css" rel="stylesheet"/>
        <script src="js/jflow/scripts/jflow.plus.min.js" type="text/javascript"></script>
        <script type="text/javascript">
            $(document).ready(function(){
                $("#myController").jFlow({
                    controller: ".jFlowControl", // must be class, use . sign
                    slideWrapper : "#jFlowSlider", // must be id, use # sign
                    slides: "#mySlides",  // the div where all your sliding divs are nested in
                    selectedWrapper: "jFlowSelected",  // just pure text, no sign
                    effect: "flow", //this is the slide effect (rewind or flow)
                    width: "600px",  // this is the width for the content-slider
                    height: "150px",  // this is the height for the content-slider
                    duration: 400,  // time in milliseconds to transition one slide
                    pause: 5000, //time between transitions
                    prev: ".jFlowPrev", // must be class, use . sign
                    next: ".jFlowNext", // must be class, use . sign
                    auto: true	
                });
            }); 
        </script>
        <!--
        *********************************
        End necessary jFlow scripts: 
        *********************************
        -->
        <script type="text/JavaScript">
            function irLogin(admin) {
                try{
                var x = screen.width - 20;
                var y = screen.height - 80;
                var param = "";
                if (admin == 1) param = "?txt_administrador=1";
                ventana=window.open("./login.php"+param,"QUIPUX","toolbar=no,directories=no,menubar=no,status=no,scrollbars=yes, width="+x+", height="+y);
                ventana.focus();
                ventana.moveTo(10, 40);
                }
                catch(e){
                    
                }
            }
        </script>
    </head>
<body>
    <div class="lineacabecera"></div>
    <div class="cabecera">
        <table align="center" width="1024" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td align="left" valign="middle">
                    <img src="imagenes/index/logo-quipux.png" alt="" width="350" />
                </td>
                <!--<td align="left" valign="top">
                    <img src="imagenes/index/borde-blanco.jpg" alt="" width="60" height="150" />
                </td>-->
                <td align="left" valign="top">
                    <!--
                    **********************************************
                    Start of jFlow DOM
                    **********************************************
                    -->
                    <div id="sliderContainer">
                        <div id="mySlides">
                            <div id="slide1" class="slide"> <img src="imagenes/index/banner_fijo_index.png" alt=""  height="200" />
<!--                                <div class="slideContent">
                                <h3>You Asked, jFlow Delivered</h3>
                                <p>It's all about the Community and giving back.  To keep with this tradition, jFlow Plus now has more of the features you want.</p>
                                </div>-->
                            </div>
                        <div id="slide2" class="slide"> <img src="imagenes/index/banner_fijo_index2.png" alt=""  height="200" />
<!--                            <div class="slideContent">
                            <h3>W3C Valid</h3>
                            <p>Are you a stickler for writing valid code?  So is jFlow.  Run this puppy through W3C's validator to see it pass the test!</p>
                            </div>-->
                        </div>
                        </div>
                        <div id="myController"> 
                            <span class="jFlowControl"></span> 
                            <span class="jFlowControl"></span> 
                        </div>
                        <div class="jFlowPrev"></div>
                        <div class="jFlowNext"></div>
                    </div>
                    <!--end: jFlow DOM --> 
<!--                    <img src="imagenes/index/banner_fijo_index.jpg" alt="" width="600" height="150" />-->
                </td>
                <!-- Original de banner -->
<!--                <td align="left" valign="top">
                    <img src="imagenes/index/demo-baner.jpg" alt="" width="300" height="150" />
                </td>
                <td align="left" valign="top">
                    <img src="imagenes/index/demo-baner-02.jpg" alt="" width="300" height="150" />
                </td>-->
            </tr>
        </table>
    </div>
    <div>
        <table width="100%" border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td valign="top" class="tablaizq">&nbsp;</td>
                <td valign="top" class="tablacen">
                    <table width="1024" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td valign="top">
                                <img src="imagenes/index/marco-boton-01.png" alt="" width="763" height="46" />
                            </td>
                            <td valign="top">
                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td>
                                            <img src="imagenes/index/marco-boton-02.png" alt="" width="127" height="19" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <?=$boton?>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td valign="top">
                                <img src="imagenes/index/marco-boton-04.png" alt="" width="134" height="46" />
                            </td>
                        </tr>
                    </table>
                </td>
                <td valign="top" class="tablader">&nbsp;</td>
            </tr>
        </table>
    </div>
    <div class="contenedor" align="center">
        <ul class="accordion" id="accordion">
            <?php 
                /*//Implantación
                echo dibujarAcordion(1,$titulo_implanta,$desc_implanta);
                //Procedimeinto
                echo dibujarAcordion(2,$titulo_procedimiento,$desc_procedimiento);
                //Soporte y Capacitación
                echo dibujarAcordion(3,$titulo_soporte,$desc_soporte);*/
            ?>
        </ul>
    </div>
    <table frame="above" width="100%" bordercolor="#666666" cellspacing="0" cellpadding="0">
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td class="derechos">
                Subsecretar&iacute;a de Gobierno Electr&oacute;nico<br />
                Secretar&iacute;a Nacional de la Administraci&oacute;n P&uacute;blica<br />
            </td>
        </tr>
    </table>
    <br><?=$boton_oculto?>
</body>
</html>

<?php 
    function dibujarAcordion($tipo_texto,$titulo_base,$descripcion_base){
        $htmlAc="
            <li class='bg$tipo_texto'>
                <div class='imagen$tipo_texto'></div>
                <div class='bgDescription'></div>
                <div class='description'>".
                    obtener_descripcion($tipo_texto,$titulo_base,$descripcion_base)
                ."</div>
            </li>";
        return $htmlAc;
    }

    function obtener_descripcion($tipo_texto,$titulo="",$descripcion="") {
        switch ($tipo_texto) {
            case 1:
                if ($titulo == "")
                    $titulo ='<h2>Implantaci&oacute;n del Sistema prueba</h2>';
                if ($descripcion == "")
                    $descripcion ='
                        <br/>
                        <p>
                            El Sistema "Quipux" es un servicio web que la Presidencia de la República pone a disposición de las instituciones del sector público.<br /><br />
                            Para solicitar el acceso al sistema se debe:<br /><br />
                            - Enviar un oficio solicitando la creación de la cuenta institucional en el sistema, dirigido al Subsecretario de Tecnologías de Información.<br /><br />
                            - Nombrar a un administrador institucional, el cual se hará cargo de la administración del sistema en la institución.<br/><br/> 
                            El uso del sistema no tiene ningún costo para la institución.
                            Vea qué Instituciones están utilizando el sistema <a href="#">aquí.</a>
                        </p>';
                break;
            case 2:
                if ($titulo == "")
                    $titulo = '<h2>Procedimientos</h2>';
                if ($descripcion == "")
                    $descripcion ='
                        <br/>
                        <p>
                            Para dar un buen uso al sistema QUIPUX se recomienda seguir los siguientes procedimientos:<br /><br />
                            - Parametrización del sistema <a href="#">aquí</a><br /><br />
                            - Registro de Documentos Externos <a href="#">aquí</a><br /><br />
                            - Subrogación de Cargos <a href="#">aquí</a><br /><br />
                            - Obtener un respaldo de información <a href="#">aquí</a><br /><br />
                            - Trámite de documentación generada por ciudadanos <a href="#">aquí</a><br /><br />
                        </p> ';
                break;
            case 3:
                if ($titulo == "")
                    $titulo = '<h2>Ayuda Soporte y Capacitaci&oacute;n</h2>';
                if ($descripcion == "")
                    $descripcion = '
                        <br/>
                        <p>
                            Para cualquier duda o solicitud de nuevos requerimientos enviar un correo al administrador institucional del sistema.<br /><br />
                            Si se olvidó la contraseña:<br /><br />
                            - Funcionario Público remitir un correo al administrador institucional de la organización.<br /><br />
                            - Ciudadano debe seguir el siguiente procedimiento. <a href="#">aquí</a><br /><br /><br />

                            Para horarios de capacitación visite la página <a href="#">aquí</a>
                        </p>';
                break;
            default:
                break;
        }
        return $titulo.$descripcion;   
    }
?> 