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
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/obtenerdatos.php";
echo "<html>".html_head();
require_once "$ruta_raiz/anexos/anexos_js.php";

if ($_SESSION["depe_codi"]==0 and $_SESSION["tipo_usuario"]==1) {
    die ("<br><center><font size='3' color='blue'><b>Su usuario no tiene definida un &Aacute;rea.<br>Por favor comun&iacute;quese con el administrador del sistema. </b></font></center>");
}

$carpeta = 0 + $_GET["carpeta"];
$noLeidos = 0 + $_GET['noLeidos'];

$nombre_carpeta = $_GET['nomcarpeta'];

if ($carpeta==0) {
    if ($_SESSION["tipo_usuario"]==1) $carpeta = 2; //Por defecto al ingresar ir a bandeja recibidos de funcionario
    elseif ($_SESSION["inst_codi"]==1) $carpeta = 83; // Si es ciudadano con firma, carpeta por defecto al ingresar, bandeja recibidos ciudadano firma
    else $carpeta = 81; // Si es ciudadano, carpeta por defecto al ingresar, bandeja recibidos ciudadano
    $nombre_carpeta = "Recibidos";
}

//paginador
$paginador = new ADODB_Pager_Ajax($ruta_raiz, "div_cuerpo", "cuerpo_paginador.php",
                                  "txt_fecha_desde,txt_fecha_hasta,estado,busqRadicados,tipoLectura,slc_tarea_estado,radi_tipo","carpeta=$carpeta");


if ($config_numero_meses < 3) $txt_fecha_desde = date("Y-m-d", strtotime(date("Y-m-d")." - 1 month"));
else $txt_fecha_desde = date("Y-m-d", strtotime(date("Y-m-d")." - 3 month"));
$txt_fecha_hasta = date("Y-m-d");

?>
<script type="text/javascript">
    <?php require_once("$ruta_raiz/pestanas.js"); ?>

    function vista_previa1(radicado, path, texto) {
        if(path!="")
        {
            var nomArchivo = path.split(".");
            var pathPDF = nomArchivo[0] + "." + nomArchivo[1];
        }
        else
            pathPDF = "";
        //window.open('<?=str_replace('.p7m','',$path_descarga)?>','_self','');
        var x = (screen.width - 10) / 2;
        var y = (screen.height - 10) / 2;
        windowprops = "top=100,left=100,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=10,height=10";
        URL = '<?=$ruta_raiz?>/VistaPrevia.php?verrad=' + radicado + '&archivo=' + pathPDF + '&textrad=' + texto;
        //alert(URL);
        ventana = window.open(URL , "Vista_Previa_" + radicado, windowprops);
        ventana.moveTo(x, y);
        ventana.focus();
    }

    function cambiar_contador() {
        try {
            contador = document.getElementById('txt_contador').value;
            parent.leftFrame.cambiar_contador('<?=$carpeta?>',contador);
        } catch (e) {
            timerID = setTimeout("cambiar_contador()", 1000);
        }
    }

    function fecha_ver(){
        if (validar_fechas()) {
            if (trim(document.getElementById('txt_nombre_texto_error').value)=='')
                paginador_reload_div('');
            else
                alert("Se requiere más información en los campos ingresados, debe ser al menos <?=$numeroCaracteresTexto?> caracteres");
        }
    }

    function validar_fechas () {
        function convertir_texto_a_fecha(cadena) {
            try {
                var cad = cadena.split('-');
                var fecha = new Date(cad[0],cad[1],cad[2]);
            } catch (e) {
                fecha = 0;
            }
            return fecha;
        }
        var fecha_desde = document.getElementById('txt_fecha_desde').value;
        var fecha_hasta = document.getElementById('txt_fecha_hasta').value;

        var tiempo1 = convertir_texto_a_fecha(fecha_hasta) - convertir_texto_a_fecha(fecha_desde);
        if (tiempo1 < 0) {
            alert ('La fecha de inicio no puede superar a la fecha final.\nPor favor modifique las fechas antes de continuar.')
            return false;
        }
        var tiempo2 = convertir_texto_a_fecha('<?=date("Y-m-d")?>') - convertir_texto_a_fecha('<?=date("Y-m-d", strtotime(date("Y-m-d")." - $config_numero_meses month"))?>');
        if (tiempo1 > tiempo2) {
            alert ('El rango de fechas no puede superar los <?=$config_numero_meses?> meses.\nPor favor modifique las fechas antes de continuar.')
            return false;
        }
        return true;
    }

    function pulsar(e) {
        // Realiza la busqueda si el usuario presiona enter
        tecla = (document.all) ? e.keyCode : e.which;
        if (tecla==13) {
            fecha_ver();
        }
    }

    function llamarListado(nombreCarpeta, codigoCarpeta){
        location.href= 'cuerpo.php?nomcarpeta='+nombreCarpeta+'&carpeta='+codigoCarpeta+'&adodb_next_page=1';
    //    document.getElementById('btn_Buscar').focus();
    }

</script>

<script type='text/JavaScript' src='<?=$ruta_raiz?>/js/shortcut.js'></script>

</head>
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
width:70px; /* el ancho por defecto que va a tener */
padding:5px; /* la separación entre el contenido y los bordes */
background-color: #E0ECF8; /* el color de fondo por defecto */
color: #000000; /* el color de los textos por defecto */
}
/*Tool Tip*/
</style>
<?php

$mostrar_filtros =  'style="display:none"';
$mostrar_combo_estado = 'style="display:none"';
$mostrar_combos_fecha = "";
if ($version_light) $mostrar_filtros =  ""; //Si es version light muestra los filtros
//if ($carpeta==7) $mostrar_filtros =  'style="display:none"'; //Oculta lso filtros para "No enviados"

$combo_estado = "";
if($carpeta == 12) { //Reasignados
    $mostrar_filtros =  ""; // Muestra los filtros de fecha y estado
    $sql_estado="select esta_desc, esta_codi from estado where esta_codi in (0,1,2,3,6,7) order by 1";
    $rsE = $db->query($sql_estado);
    $combo_estado .= $rsE->GetMenu2("estado", -1, "-1:&lt;&lt Todos &gt;&gt;", false,""," id='estado' class='select'" );
    $mostrar_combo_estado = "";
} else {
    $combo_estado .= '<input type="hidden" name="estado" id="estado" value="-1">';
}

if($carpeta == 15 or $carpeta == 16) { //Tareas
    $mostrar_filtros =  ""; // En tareas se muestra solo el filtro de estado
    if (!$version_light) $mostrar_combos_fecha = 'style="display:none"'; //Se ocultan las fechas excepto cuando esta en versión light

    $tmp_selected = ($carpeta == 15) ? "selected" : "";
    $combo_estado .= '<select name="slc_tarea_estado" id="slc_tarea_estado" class="select">
                    <option value="0" selected>Todos</option>
                    <option value="1" '.$tmp_selected.'>Pendiente</option>
                    <option value="2">Finalizado</option>
                    <option value="3">Cancelado</option>
                </select>';
    $mostrar_combo_estado = "";
} else {
    $combo_estado .= '<input type="hidden" name="slc_tarea_estado" id="slc_tarea_estado" value="0">';
}

?>

<body onLoad="fjs_popup_crear_divs(); window_onload(); paginador_reload_div(''); cambiar_contador(); shortcuts_cuerpo();">
<form name="form1" id="form1" action="./tx/formEnvio.php?<?=$encabezado?>" method="POST">
    <input type="hidden" name="txt_nombre_texto_error" id="txt_nombre_texto_error" class="tex_area" value=""/>
    <input type="hidden" name="carpeta" id="carpeta"  value="<?=$carpeta?>"/>
    <table width="100%" class="borde_tab" border="0" cellpadding="0" cellspacing="3">
        <tr>
            <td width="90%">
    <table width="100%" border="0" >
        <tr valign="top" height="23" <?php echo $mostrar_filtros?>>
            <td width="19%" class="titulos2" <?php echo $mostrar_combos_fecha?>>Desde Fecha (yyyy-mm-dd):</td>
            <td width="18%" class="listado2" <?php echo $mostrar_combos_fecha?>><?php echo dibujar_calendario("txt_fecha_desde", $txt_fecha_desde, $ruta_raiz, ""); ?></td>
            <td width="19%" class="titulos2" <?php echo $mostrar_combos_fecha?>>Hasta Fecha (yyyy-mm-dd):</td>
            <td width="18%" class="listado2" <?php echo $mostrar_combos_fecha?>><?php echo dibujar_calendario("txt_fecha_hasta", $txt_fecha_hasta, $ruta_raiz, ""); ?></td>
            <td width="15%" class="titulos2" <?php echo $mostrar_combo_estado?>>Estado</td>
            <td width="85%" class="listado2" <?php echo $mostrar_combo_estado?>><?php echo $combo_estado ?></td>
        </tr>
        <tr>
            <td class="titulos2" width="19%" >Texto a Buscar</td>
            <td colspan="5" width="81%" >
                <!--input name="busqRadicados" id="busqRadicados" type="text" size="40" class="tex_area" value="" onkeypress="pulsar(event)"-->
                <?php echo cajaTextoValida('busqRadicados','',$numeroCaracteresTexto,"onkeypress=evento_ver(event,this,$numeroCaracteresTexto,'busqRadicados',1);pulsar(event);")?>
                Asunto, N&uacute;mero de Documento, N&uacute;mero de Referencia
                <div style="position: relative; top: 0; float: right; vertical-align: top; <?if ($mostrar_filtros!='') echo 'display: none;';?>">
                    Si desea ver todos los documentos, por favor modifique los filtros <blink><img src="<?=$ruta_raiz?>/iconos/img_alerta_2.gif" alt="&laquo;!&raquo;"></blink>
                </div>
            </td>
        </tr>
    </table>
            </td>
            <td width="10%" align="center">
                <input type=button value='Buscar' name=Buscar class='botones' title="Busca el texto ingresado en: Numero Documento, Asunto, No. Referencia y Fecha" onclick="fecha_ver();">
            </td>
        </tr>
    </table>
    <?php
    if ($carpeta==1 || $carpeta==8 || $carpeta==2) { ?>
    <table width="100%" class="borde_tab_tomate" border="0" cellpadding="0" cellspacing="3">
        <tr border="0">

                    <td width ="100%">Tipo de Documento:
                    <?php
                    //Filtro Tipo de documento
                    $query = "Select trad_descr, trad_codigo from tiporad where trad_estado=1 and trad_inst_codi in (1,0,".$_SESSION["inst_codi"].") order by 2";
                    //echo $query;
                    $rs=$db->conn->query($query);
                    $tmp='';$radi_tipo=1;
                    $fnjava='paginador_reload_div("");';
                     if(!$rs->EOF)
                        print $rs->GetMenu2("radi_tipo", 0, "0:Todos", false,"","class='select' id='radi_tipo' onChange='$fnjava $tmp'" );

                     ?>
                        <img src="<?=$ruta_raiz?>/imagenes/nuevo.jpeg" name="Image2" border="0" alt="Documentos"/>
                    </td>
                </tr>
    </table>
     <?php
      }else
         echo '<table width="100%" border="0" cellpadding="0" cellspacing="3"><td width ="20%"><input type="hidden" name="radi_tipo" id="radi_tipo" value="0"></table>';
      ?>

<?php if ($_SESSION["tipo_usuario"]!=2) { ?>
    <table width="100%" border="0" >
        <tr>
            <td width="25%">
                &nbsp;
<!--                <a target='mainFrame'  onclick="llamaCuerpo('busqueda/busqueda.php')" href="javascript:;" class="aqui">B&uacute;squeda Avanzada</a>-->
            </td>
            <td width="75%" align="right">
    <?php
            if ($carpeta == 14) { //Carpeta compartida
                $datosJefe = ObtenerDatosUsuario($_SESSION['usua_codi_jefe'],$db);
                echo "<div class='listado2' style='border: thin solid #377584; float: right;'>
                        <font size='2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bandeja de Documentos Recibidos de ". $datosJefe['nombre']."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</font>
                      </div>";
            }
    ?>
            </td>
        </tr>
    </table>

    <?php }

        if(!$tipo_carp) $tipo_carp = "0";

        $controlAgenda=1;
        $estado=0;
        if ($carpeta==99) $estado=5;
        if ($carpeta==80) $estado=100;
        if ($carpeta==81) $estado=100;
        if ($carpeta==1) $estado=1;
        if ($carpeta==2) $estado=2;
        if ($carpeta==6) $estado=7;
        if ($carpeta==8) $estado=6;
        if ($carpeta==7) $estado=3;
        if ($carpeta==12) $estado=12;
        if ($carpeta==13) $estado=13;
        if ($carpeta==14) $estado=14;
        if ($carpeta==15) $estado=15;
        if ($carpeta==16) $estado=16;
        if ($carpeta==82) $estado=82;
        if ($carpeta==83) $estado=83;
        if ($carpeta==84) $estado=84;
        if ($carpeta==85) $estado=85;
        if ($carpeta==86) $estado=86;
        if ($carpeta==87) $estado=87;
        if ($carpeta==90) $estado=90;
        // if ($carpeta==91) $estado=91; //Funcionalidad documentos enviados sin firma a otras instituciones

        // Parte LISTAR POR
        include "./tx/txOrfeo.php";

?>
    <center>
        <div id="div_cuerpo"></div>
    </center>
</form>
<iframe  name="ifr_descargar_archivo" id="ifr_descargar_archivo" style="display: none;" src="">
    Su navegador no soporta iframes, por favor actualicelo.</iframe>
</body>
</html>
<script type="text/javascript">
function llamaCuerpo(parametros){
    top.frames['mainFrame'].location.href=parametros;

}
</script>