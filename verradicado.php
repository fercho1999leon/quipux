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
/*****************************************************************************************
**											**
******************************************************************************************/
session_start();
$ruta_raiz = ".";

include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/tipo_documental/obtener_datos_trd.php";
include_once "$ruta_raiz/seguridad/obtener_nivel_seguridad.php";
include_once "$ruta_raiz/obtenerdatos.php";
include_once "$ruta_raiz/metadatos/metadatos_funciones.php";
include_once "$ruta_raiz/funciones_interfaz.php";

if (strpos($_SERVER["HTTP_REFERER"],"cuerpo.php") !== false) {
    $menu_ver=3;
    if ($carpeta==15 or $carpeta==16) $menu_ver=7; //Tareas
}

$nivel_seguridad_documento = obtener_nivel_seguridad_documento($db, $verrad);
$pagactual=1;	//Variable necesaria para txOrfeo.php, muestra los botones editar o responder
$numrad = trim($verrad);

$datosrad = ObtenerDatosRadicado($verrad,$db);
$usr_actual = ObtenerDatosUsuario($datosrad["usua_actu"],$db);
$estado = $datosrad["estado"];

$textrad = $datosrad["radi_nume_text"];
if (!$menu_ver) $menu_ver=3;	//define la pestaña de vista general por defecto
if (!$estadisticas) $estadisticas=0;

//habilitar envio fisico
$radi_tipo = $datosrad["radi_tipo"];
$radi_refe = $datosrad['radi_padre'];

$path_descarga = "return;";
$path_archivo_embebido = "";
if ($nivel_seguridad_documento >= 2 and $menu_ver==3) {
    $path_descarga = "fjs_radicado_descargar_archivo('".$datosrad["radi_nume_radi"]."', '".$datosrad["radi_imagen"]."', 0, 'download');";
    if ($datosrad["radi_path"]!="" or $datosrad["radi_imagen"]!="" or $datosrad["arch_codi"]!=0 or ($datosrad["estado"]==1 and substr($datosrad["radi_nume_radi"], -1)=="0"))
        $path_archivo_embebido = "fjs_radicado_descargar_archivo('".$datosrad["radi_nume_radi"]."', '".$datosrad["radi_imagen"]."', 0, 'embeded');";
}

echo "<html>".html_head();
include_once "$ruta_raiz/js/ajax.js";

?>
<script type="text/javascript" src="<?=$ruta_raiz?>/js/base64.js"></script>
<script type="text/javascript">
    function fjs_radicado_descargar_archivo(radicado, anex_codigo, arch_tipo, tipo_descarga) {
        path_descarga = './anexos/anexos_descargar_archivo.php?radi_nume='+radicado+'&anex_codigo=' + anex_codigo + '&arch_tipo=' + arch_tipo + '&tipo_descarga=' + tipo_descarga;
        if (tipo_descarga=='embeded' && fjs_verificar_plugin_navegador ('acrobat')) path_descarga += '_ar';
        if (tipo_descarga=='embeded')
            document.getElementById('ifr_mostrar_archivo').src=path_descarga;
        else
            document.getElementById('ifr_descargar_archivo').src=path_descarga;
        return;
    }

    function regresar() {
        window.location.reload();
    }

    function CambiarTRD() {
        ventana = window.open("./tipo_documental/seleccionar_trd.php?verrad=<?=$verrad?>&textrad=<?=$textrad?>", "Tipificacion_Documento", "height=500,width=750,scrollbars=yes");
        ventana.focus();
    }

    function AsociarDocumento() {
        ventana = window.open("./asociar_documentos/asociar_documento.php?radi_nume=<?=$verrad?>&radi_refe=<?=$radi_refe?>&cerrar=NO", "asociar_documentos", "height=600,width=900,scrollbars=yes");
        ventana.focus();
    }

    function popup_ver_documento(radicado) {    
        windowprops = "top=50,left=50,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=700,height=500";
        url = '<?=$ruta_raiz?>/verradicado.php?verrad=' + radicado + '&estadisticas=0&menu_ver=3&tipo_ventana=popup';
        ventana = window.open(url , "ver_documento_" + radicado, windowprops);
        ventana.focus();
    }

     function desplegarContraer(cual,desde) {
          var elElemento=document.getElementById(cual);
          if(elElemento.className == 'elementoVisible') {
               elElemento.className = 'elementoOculto';
               desde.className = 'linkContraido';
          } else {
               elElemento.className = 'elementoVisible';
               desde.className = 'linkExpandido';
          }
    }
    function vista_previa() {
        <?=$path_descarga?>
    }

      function ventanaNueva(url){
	window.open(url,'nuevaVentana','width=600, height=600');

    }

    function verificar_firma() {
        var url = '<?=$ruta_raiz?>/anexos/anexos_verificar_firma.php';
        var parametros = 'radi_nume=<?=$verrad?>&anex_codigo=';
        fjs_popup_activar ('Verificación de Firma Electrónica', url, parametros);
        return;
    }

    function DefinirMetadato() {
        ventana = window.open("./metadatos/metadatos_radi.php?verrad=<?=$verrad?>&textrad=<?=$textrad?>&tipo_ventana=popup", "", "height=600,width=800,scrollbars=yes");
        ventana.focus();
    }


</script>

<script type="text/javascript">


function Regresarteclado() {
        javascript:history.back();
                      }
function Editarteclado(carpeta1) {
    if(carpeta1 == 1 || carpeta1 == 82)
    {
        changedepesel(16);
    }
  }
  function Eliminarteclado(carpeta1) {     
    if(carpeta1 == 1 || carpeta1 == 6 || carpeta1 == 99 || carpeta1 == 82 || carpeta1 == 84)
    {
        changedepesel(2);
    }
  }

  function llamarListado(nombreCarpeta, codigoCarpeta){
     location.href= '<?=$ruta_raiz?>/cuerpo.php?nomcarpeta='+nombreCarpeta+'&carpeta='+codigoCarpeta+'&adodb_next_page=1';
     if(document.getElementById('btn_Buscar'))
     document.getElementById('btn_Buscar').focus();
}

//faltan mas validaciones en todos los casos
function init() {

    var nomCarpeta = ""; //Nombre de la bandeja que esta en la base de datos
    var codCarpeta = ""; //Codigo de la bandeja que esta en la base de datos (Primary Key)
    shortcut.add("Alt+b", function() {
        nomCarpeta = "En Elaboración";
        codCarpeta = "1";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenu(nomCarpeta);
    });
    shortcut.add("Alt+r", function() {
        nomCarpeta = "Recibidos";
        codCarpeta = "2";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenu(nomCarpeta);
    });
    shortcut.add("Alt+c", function() {
        nomCarpeta = "Eliminados";
        codCarpeta = "6";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenu(nomCarpeta);
    });
    shortcut.add("Alt+n", function() {
        nomCarpeta = "No Enviados";
        codCarpeta = "7";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenu(nomCarpeta);
    });
    shortcut.add("Alt+e", function() {
        nomCarpeta = "Enviados";
        codCarpeta = "8";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenu(nomCarpeta);
    });
    shortcut.add("Alt+p", function() {
        nomCarpeta = "Reasignados";
        codCarpeta = "12";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenu(nomCarpeta);
    });
    shortcut.add("Alt+a", function() {
        nomCarpeta = "Archivados";
        codCarpeta = "10";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenu(nomCarpeta);
    });
    shortcut.add("Alt+i", function() {
        nomCarpeta = "Informados";
        codCarpeta = "13";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenu(nomCarpeta);
    });
    shortcut.add("Alt+t", function() {
        nomCarpeta = "Tareas Recibidas";
        codCarpeta = "15";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenu(nomCarpeta);
    });
    shortcut.add("Alt+s", function() {
        nomCarpeta = "Tareas Enviadas";
        codCarpeta = "16";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenu(nomCarpeta);
    });

   //regresar
    shortcut.add("Ctrl+Shift+u", function() {
        Regresarteclado();
    });//reasignar
       shortcut.add("Ctrl+Shift+p", function() {
                changedepesel(9);
    });//informar
    shortcut.add("Ctrl+Shift+i", function() {
                changedepesel(8);
    });//firmar/enviar
    shortcut.add("Ctrl+Shift+e", function() {
                changedepesel(11);
    });//comentar
    shortcut.add("Ctrl+Shift+m", function() {       
                changedepesel(18);               
    });//dar fisico
    shortcut.add("Ctrl+Shift+f", function() {
                changedepesel(69);
    });
    //editar
    shortcut.add("Ctrl+Shift+b", function() {
                 Editarteclado('<?=$carpeta?>');
    });
    //eliminar
    shortcut.add("Ctrl+Shift+c", function() {
                 Eliminarteclado('<?=$carpeta?>');
    });//copiar
    shortcut.add("Ctrl+Shift+o", function() {
                changedepesel(82);
    });//responder
    shortcut.add("Ctrl+Shift+g", function() {
                changedepesel(12);
    });//archivar
    shortcut.add("Ctrl+Shift+a", function() {
                changedepesel(13);
    });//tarea nueva
    shortcut.add("Ctrl+Shift+t", function() {
                changedepesel(30);
    });//restaurar archivado
    shortcut.add("Ctrl+Shift+l", function() {
                changedepesel(17);
    });//eliminar informado
    shortcut.add("Ctrl+Shift+k", function() {
                changedepesel(7);
    });//envio manual
    shortcut.add("Ctrl+Shift+n", function() {
        changedepesel(5);
    });//envio electrónico
    shortcut.add("Ctrl+Shift+d", function() {
        changedepesel(4);
    });//imprimir sobre
    shortcut.add("Ctrl+Shift+s", function() {
        changedepesel(70);
    });//restaurar eliminado
    shortcut.add("Ctrl+Shift+r", function() {
       changedepesel(6);
    });//enviar de bandeja por imprimir
    shortcut.add("Ctrl+Shift+y", function() {
       changedepesel(3);
    });//devolver
    shortcut.add("Ctrl+Shift+q", function() {
       changedepesel(20);
    });
    shortcut.add("Ctrl+Shift+o", function() {
       changedepesel(83);
    });
}
window.onload=init();

</script>

<style type="text/css">

/*Tool Tip*/
a.Ntooltip {
position: relative; /* es la posición normal */
text-decoration: none !important; /* forzar sin subrayado */
color:#0080C0 !important; /* forzar color del texto */
font-weight:bold !important; /* forzar negritas */
}

a.Ntooltip:hover {
z-index:999; /* va a estar por encima de todo */
background-color:#000000; /* DEBE haber un color de fondo */
}

a.Ntooltip span {
display: none; /* el elemento va a estar oculto */
}

a.Ntooltip:hover span {
display: block; /* se fuerza a mostrar el bloque */
position: absolute; /* se fuerza a que se ubique en un lugar de la pantalla */
top:1em; left:1em; /* donde va a estar */
width:100px; /* el ancho por defecto que va a tener */
padding:5px; /* la separación entre el contenido y los bordes */
background-color: #FBFBEF; /* el color de fondo por defecto */
color: #000000; /* el color de los textos por defecto */
}
/*Tool Tip*/
/**/
</style>

<?php 
if ($tipo_ventana=='popup'){
?>
<script type="text/javascript">
function llamaCuerpo(parametros){   
     location.href = parametros;
}
</script>
<?php }else{
    ?>
<script type="text/javascript">
function llamaCuerpo(parametros){   
    top.frames['mainFrame'].location.href=parametros;

}

</script>
<?php }?>

<body bgcolor="#FFFFFF" onLoad="fjs_popup_crear_divs(); window_onload(<?=$radi_tipo?>); <?=$path_archivo_embebido?>">
    <form name="form1" id="form1" action="<?=$ruta_raiz.'/tx/formEnvio.php?carpeta='.$carpeta?>" method="post">        

<?
        $dirresponder = "$ruta_raiz/radicacion/NEW.php?nurad=$verrad&radi_padre=$verrad&textrad=$textrad&carpeta=$carpeta&accion=Responder";
        $dirresponderTodos = "$ruta_raiz/radicacion/NEW.php?nurad=$verrad&radi_padre=$verrad&textrad=$textrad&carpeta=$carpeta&accion=ResponderTodos";
        $dirmodificar = "$ruta_raiz/radicacion/NEW.php?nurad=$verrad&textrad=$textrad&carpeta=$carpeta&accion=Editar";
        $dircopiar = "$ruta_raiz/radicacion/NEW.php?nurad=$verrad&radi_padre=$verrad&textrad=$textrad&carpeta=$carpeta&accion=Copiar";
        $controlAgenda=1;
        if ($estadisticas==1 or $_SESSION["usua_tipo"]==2) $estado=100;
        if ($carpeta==20) $estado = 20;
        if ($datosrad["usua_actu"]==$_SESSION["usua_codi"] and $_SESSION["tipo_usuario"]==2) { // Ciudadanos Firma
            if ($carpeta==82 or $estado==1) $estado=82; // En elaboracion
            if ($carpeta==83) $estado=83; // Ciudadanos Firma
            if ($carpeta==84) $estado=84; // Ciudadanos Firma
            if ($carpeta==85) $estado=85; // Ciudadanos Firma
            if ($carpeta==86) $estado=86; // Ciudadanos Firma
            if ($carpeta==87) $estado=87; // Ciudadanos Firma
        }
        // Si ingresa desde la bandeja de documentos compartidos, tareas, informados
        if ($datosrad["usua_actu"] != $_SESSION["usua_codi"]) {
            $rs = $db->conn->Execute("select count(1) as inf from informados where radi_nume_radi=$verrad and usua_codi=".$_SESSION["usua_codi"]);
            if(($estado == 1 or $estado == 2) && $nivel_seguridad_documento==5) {
                $estado = 15;
            }
            else if ($estado != 5) $estado = 100; // Si no es bandeja compartida restringir el acceso
            if ($rs->fields["INF"] != 0 && ($carpeta==13 || $estado!=15)) $estado=13; // Informados
        }
        if($carpeta == 14 ) $estado = 14;
        if ($datosrad["estado"]==9) $estado=90;
        if ($datosrad["estado"]==10) $estado=91;
        include "$ruta_raiz/tx/txOrfeo.php";
?>
        <input type=hidden name="checkValue[<?=$verrad?>]" value='CHKANULAR'>
    </form>

<!-- Redireccionar a NEW.php si el usuario es JEFE y el documento esta en elaboracion -->
<?php if($nivel_seguridad_documento==7 and (strpos($_SERVER["HTTP_REFERER"],"cuerpo.php") !== false) and $_SESSION["usua_perm_redireccionar_edicion"]==1) { ?>
    <script language="JavaScript" type="">
        window.location = "<?=$dirmodificar?>";
    </script>
<?php } ?>

<table width="100%" border="0" cellpadding="0" cellspacing="1" >
    <tr>
        <td class="titulos4" width="40%">No. Documento: &nbsp;&nbsp;&nbsp;&nbsp;<?=$datosrad["radi_nume_text"] ?></td>
        <td class="titulos4" align="left" width="35%">&nbsp;&nbsp;Usuario actual: &nbsp;&nbsp;&nbsp;&nbsp;<?=$usr_actual["nombre"]?></td>
        <td class="titulos4" align="left" width="25%">&nbsp;&nbsp;<?=$descDependencia?> actual: &nbsp;&nbsp;&nbsp;&nbsp;<?=$usr_actual["dependencia"]?></td>
    </tr>
</table>

<div onclick="desplegarContraer('documento',this);" class="linkExpandido">Datos del Documento</div>
<ul id="documento" class='elementoVisible'>
<table border=0 align='center' cellpadding="0" cellspacing="0" width="100%" >
    <form action='verradicado.php?<?="verrad=$verrad&carpeta=$carpeta&textrad=$textrad&estadisticas=$estadisticas"?>' method=post name='form2'>
   <?

    //Cambia la bandera indicando si el documento fue leido o no
//    echo "<input type='hidden' name='fechah' value='$fechah'>";
    $leido = ObtenerCampoRadicado("radi_leido",$verrad,$db);
    $row = array();
    $row1 = array();
    if($carpeta==13) {        
	$row["INFO_LEIDO"]=1;
	$row1["USUA_CODI"] = $_SESSION["usua_codi"];
	$row1["RADI_NUME_RADI"] = $verrad;
	$rs = $db->update("informados", $row, $row1);
    }
    elseif ($leido==0) {
	$row["RADI_LEIDO"]=1;
	$row1["radi_usua_actu"] = $_SESSION["usua_codi"];
	$row1["radi_nume_radi"] = $verrad;
	$rs = $db->update("radicado", $row, $row1);
    }
    
    $hdatos = "carpeta=$carpeta&verrad=$verrad&textrad=$textrad&estadisticas=$estadisticas&verPDF=1&irVerRad=1&tipo_ventana=$tipo_ventana&menu_ver=";
?>
    <tr>
      <td height="99" rowspan="4" width="3%" valign="top" class="listado2">&nbsp;</td>
      <td height="8" width="94%" class="listado2">
<?
	$datos1 = "";$datos2 = "";$datos3 = "";$datos4 = "";$datos5 = "";$datos6 = "";$datos7 = "";$datos8 = "";
	${"datos".$menu_ver} = "_R";	//Pone la pestaña resaltada que el usuario eligio
?>
        <table border=0 width=69% cellpadding="0" cellspacing="0">
          <tr>
            <td width="13%" valign="bottom" class="" >
                <?php
                $parametrosFuncion = "verradicado.php?$hdatos";
                $parametrosFuncion = "'".$parametrosFuncion."3"."'";
                $funcionjava = "llamaCuerpo($parametrosFuncion);";
                ?>
                <a onclick="<?php echo $funcionjava;?>" href='javascript:void(0);' ><img src='imagenes/infoGeneral<?=$datos3?>.gif' alt='' border=0 width="110" height="25"></a></td>
<?  

        if ($nivel_seguridad_documento>=2){
            $parametrosFuncion = "verradicado.php?$hdatos";
            $parametrosFuncion = "'".$parametrosFuncion."2"."'";
            $funcionjava = "llamaCuerpo($parametrosFuncion);";
            echo '<td width="13%" valign="bottom" class=""><a onclick="'.$funcionjava.'" href="javascript:void(0);"><img src="imagenes/documentos'.$datos2.'.gif" alt="" border="0" width="110" height="25" ></a></td>';
        }        
        if ($nivel_seguridad_documento>=1){
            $parametrosFuncion = "verradicado.php?$hdatos";
            $parametrosFuncion = "'".$parametrosFuncion."1"."'";
            $funcionjava = "llamaCuerpo($parametrosFuncion);";
            echo '<td width="13%" valign="bottom" class=""><a onclick="'.$funcionjava.'" href="javascript:void(0);"><img src="imagenes/historico' .$datos1.'.gif" alt="" border="0" width="110" height="25" ></a></td>';
        }
        if ($nivel_seguridad_documento>=2 and $_SESSION["tipo_usuario"]==1){
            $parametrosFuncion = "verradicado.php?$hdatos";
            $parametrosFuncion = "'".$parametrosFuncion."4"."'";
            $funcionjava = "llamaCuerpo($parametrosFuncion);";
            echo '<td width="13%" valign="bottom" class=""><a onclick="'.$funcionjava.'" href="javascript:void(0);"><img src="imagenes/expediente'.$datos4.'.gif" alt="" border="0" width="110" height="25" ></a></td>';
        }
        if ($nivel_seguridad_documento>=2 and $_SESSION["tipo_usuario"]==1){
            $parametrosFuncion = "verradicado.php?$hdatos";
            $parametrosFuncion = "'".$parametrosFuncion."6"."'";
            $funcionjava = "llamaCuerpo($parametrosFuncion);";
            echo '<td width="13%" valign="bottom" class=""><a onclick="'.$funcionjava.'" href="javascript:void(0);"><img src="imagenes/asociados'.$datos6.'.gif" alt="" border="0" width="110" height="25" ></a></td>';
        }    

        $sql = "select tarea_codi as num from tarea where radi_nume_radi=$verrad and ".$_SESSION["usua_codi"]." in (usua_codi_dest,usua_codi_ori)";
        $rs_tarea = $db->query($sql);
        if (($nivel_seguridad_documento==5 or $nivel_seguridad_documento==6 or ($rs_tarea && !$rs_tarea->EOF)) and $_SESSION["tipo_usuario"]==1 and $datosrad["estado"]!=9){
            $parametrosFuncion = "verradicado.php?$hdatos";
            $parametrosFuncion = "'".$parametrosFuncion."7"."'";
            $funcionjava = "llamaCuerpo($parametrosFuncion);";
            echo '<td width="13%" valign="bottom" class=""><a onclick="'.$funcionjava.'" href="javascript:void(0);"><img src="imagenes/tareas'.$datos7.'.gif" alt="" border="0" width="110" height="25" ></a></td>';
        }
        if ($nivel_seguridad_documento>=2){
            $parametrosFuncion = "verradicado.php?$hdatos";
            $parametrosFuncion = "'$parametrosFuncion"."8'";
            $funcionjava = "llamaCuerpo($parametrosFuncion);";
            echo '<td width="13%" valign="bottom" class=""><a onclick="'.$funcionjava.'" href="javascript:void(0);"><img src="imagenes/metadato'.$datos8.'.gif" alt="" border="0" width="110" height="25" ></a></td>';
        }
?>
     
            <td width="87%" valign="bottom" class="" >&nbsp;</td>
          </tr>
        </table>
      </td>
      <td height="149" rowspan="4" class="listado2" width="3%">&nbsp;</td>
      <td height="149" rowspan="4" class="" width="3%">&nbsp;&nbsp;&nbsp;</td>
    </tr>
    <tr >
        <td  bgcolor="" width="94%" height="100">
<?
            //error_reporting(7);
            
            switch ($menu_ver) {
                case 1://Recorrido
                    include "ver_historico.php";
                    break;
                case 2://Anexos
                    $boton_anexos="Si";	//Se necesita definir un nuevo formulario para los anexos así que debemos cerrar el actual
                    include "./anexos/anexos.php";
                    break;
                case 3://Informacion del documento
                    require "./lista_general.php";
                    break;
                case 4://carpetas
                    include "./tipo_documental/lista_expediente.php";
                    break;
                case 5:
                    include "plantilla.php";
                    break;
                case 6://Doc Asociados
                    include "./asociar_documentos/lista_asociados.php";
                    break;
                case 7:
                    include "./tareas/tareas.php";
                    break;
                case 8:
                    include "./metadatos/metadatos_listadoc.php";
                    break;
                default:
                    break;
            }
?>
      </td>
    </tr>
    <tr>
        <td height="15" width="94%" class="listado2">&nbsp;</td>
    </tr>
</form>
</table>
</ul>
<iframe  name="ifr_descargar_archivo" id="ifr_descargar_archivo" style="display: none;" src="">
            Su navegador no soporta iframes, por favor actualicelo.</iframe>

<?php
//Ver documento embebido
$fjs_ifr_mostrar_archivo_cargar = "";
if(($_SESSION["cargo_tipo"]=='1' or $_SESSION["usua_perm_mostrar_documento"]==1) and $nivel_seguridad_documento>=2 and $menu_ver==3)
{
    echo "<iframe name='ifr_mostrar_archivo' id='ifr_mostrar_archivo' style='width:100%; height:400px; overflow: hidden; border: none;'
            src=''>
            Su navegador no soporta iframes, por favor actualicelo.</iframe>";
}
?>

</body>
</html>
