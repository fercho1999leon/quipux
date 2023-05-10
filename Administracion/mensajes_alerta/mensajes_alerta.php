<?
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
** Administración de mensajes para el sistema                                           **
*****************************************************************************************/
$ruta_raiz = "../..";
session_start();
include_once "$ruta_raiz/rec_session.php";
require_once "$ruta_raiz/funciones.php";
require_once "$ruta_raiz/funciones_interfaz.php";
if ($_SESSION["admin_institucion"] != 1) {
    die( html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.") );
}

echo "<html>".html_head(); /*Imprime el head definido para el sistema*/
require_once "$ruta_raiz/js/ajax.js";

$bloq_codi = 0+ $_GET["txt_bloq_codi"];
$sql = "select * from bloqueo_sistema where bloq_codi=$bloq_codi";
$rs = $db->conn->Execute($sql);
$fecha_inicio = (isset ($rs->fields["FECHA_INICIO"])) ? $rs->fields["FECHA_INICIO"] : date ("Y-m-d")." 00:00:00";
$txt_fecha_inicio = substr($fecha_inicio, 0, 10);
$txt_hora_inicio = substr($fecha_inicio, 11, 2);
$txt_min_inicio = substr($fecha_inicio, 14, 2);
$fecha_fin = (isset ($rs->fields["FECHA_FIN"])) ? $rs->fields["FECHA_FIN"] : date ("Y-m-d")." 00:00:00";
$txt_fecha_fin = substr($fecha_fin, 0, 10);
$txt_hora_fin = substr($fecha_fin, 11, 2);
$txt_min_fin = substr($fecha_fin, 14, 2);
$txt_estado = (isset ($rs->fields["ESTADO"])) ? $rs->fields["ESTADO"] : 0;
$txt_descripcion = (isset ($rs->fields["DESCRIPCION"])) ? $rs->fields["DESCRIPCION"] : "";
$txt_mensaje  = (isset ($rs->fields["MENSAJE_USUARIO"])) ? $rs->fields["MENSAJE_USUARIO"] : "";
$txt_usua_acceso  = (isset ($rs->fields["USUA_ACCESO"])) ? $rs->fields["USUA_ACCESO"] : "";
$txt_tipo_mensaje = (isset ($rs->fields["TIPO_MENSAJE"])) ? $rs->fields["TIPO_MENSAJE"] : 2;
?>

<script type="text/javascript" src="<?=$ruta_raiz?>/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src='<?=$ruta_raiz?>/js/base64.js'></script>
<script type="text/javascript">
    function crearEditor()
    {
        CKEDITOR.replace('txt_mensaje');
//        var sBasePath = "<?=$ruta_raiz?>/js/fckeditor/" ;
//        var oFCKeditor = new FCKeditor( 'txt_mensaje' ) ;
//        oFCKeditor.BasePath = sBasePath;
//        oFCKeditor.ReplaceTextarea();
    }

    function grabar_mensaje_alerta() {
        //var oEditor = FCKeditorAPI.GetInstance('txt_mensaje') ;
        var oEditor = CKEDITOR.instances.txt_mensaje;
        var fecha_inicio = document.getElementById('txt_fecha_inicio').value + ' ' +
                           document.getElementById('txt_hora_inicio').value + ':' +
                           document.getElementById('txt_min_inicio').value + ':00-05';
        var fecha_fin = document.getElementById('txt_fecha_fin').value + ' ' +
                           document.getElementById('txt_hora_fin').value + ':' +
                           document.getElementById('txt_min_fin').value + ':00-05';
        if (!validar_fechas(fecha_inicio, fecha_fin)) {
            alert ('La fecha de inicio no puede ser superior a la fecha final');
            return false;
        }
        try {
            var bloq_codi = document.getElementById('txt_bloq_codi').value;
        } catch (e) {
            var bloq_codi = '<?=$bloq_codi?>';
        }
        var parametros = 'txt_bloq_codi=' + bloq_codi +
                         '&txt_descripcion='+Base64.encode(Base64.encode(document.getElementById('txt_descripcion').value)) +
                         '&txt_fecha_inicio=' + fecha_inicio + '&txt_fecha_fin=' + fecha_fin +
                         '&txt_estado='+document.getElementById('txt_estado').value +
                         '&txt_usua_acceso='+document.getElementById('documento_us1').value +
                         '&txt_tipo_mensaje='+document.getElementById('txt_tipo_mensaje').value +
                         '&txt_mensaje='+Base64.encode(Base64.encode(oEditor.getData()));

        nuevoAjax('div_grabar_alerta_mensaje', 'POST', 'mensajes_alerta_grabar.php', parametros);
        alert("Los cambios han sido guardados.");
    }

    function validar_fechas(fecha_inicio, fecha_fin) {
        try {
            var fecha1= new Date( fecha_inicio.toString().substr(0,4)
                                 ,fecha_inicio.toString().substr(5,2)
                                 ,fecha_inicio.toString().substr(8,2)
                                 ,fecha_inicio.toString().substr(11,2)
                                 ,fecha_inicio.toString().substr(14,2));
            var fecha2= new Date( fecha_fin.toString().substr(0,4)
                                 ,fecha_fin.toString().substr(5,2)
                                 ,fecha_fin.toString().substr(8,2)
                                 ,fecha_fin.toString().substr(11,2)
                                 ,fecha_fin.toString().substr(14,2));
            var tiempo = fecha2.getTime() - fecha1.getTime();
            if (tiempo > 0) return true;
        } catch (e) {}
        return false;
    }

    function buscar_usuarios() {
        var x = (screen.width - 1100) / 2;
        var y = (screen.height - 740) / 2;
        windowprops = "top=0,left=0,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=1100,height=740";
        url = '<?=$ruta_raiz?>/radicacion/buscar_usuario_nuevo.php?documento_us1=' + document.formulario.documento_us1.value + '&documento_us2=-&ent=0';
        preview = window.open(url , "preview", windowprops);
        preview.moveTo(x, y);
        preview.focus();

    }

    function ejecutar_cron_alertas() {
        nuevoAjax('div_ejecutar_cron_alertas', 'POST', '<?=$ruta_raiz?>/cron/generar_mensaje_alerta.php', '');
        alert ("Se ha ejecutado la alerta.");
    }

</script>

<body onload="crearEditor(); refrescar_pagina();">
    <center>
    <br>
    <table width="100%" border="1" align="center" class="t_bordeGris">
        <tr>
            <th colspan="4"><center>Administraci&oacute;n de Mensajes de Alerta y Bloqueos del Sistema</center></th>
        </tr>
    </table>
    <br>
    <div id="div_grabar_alerta_mensaje"><input type="hidden" name="txt_bloq_codi" id="txt_bloq_codi" value="<?=$bloq_codi?>"></div>
    <table width="100%" border="1" align="center" class="t_bordeGris">
        <tr>
            <td class="titulos2">Descripci&oacute;n: </td>
            <td colspan="3" class="listado2">
                <textarea name="txt_descripcion" id="txt_descripcion" cols="150" class="tex_area" rows="1"><?php echo $txt_descripcion ?></textarea>
            </td>
        </tr>
        <tr>
            <td width="15%" class="titulos2">Estado: </td>
            <td width="35%" class="listado2">
                <?php echo dibujar_combos("txt_estado", $txt_estado); ?>
            </td>
            <td width="15%" class="titulos2">Tipo de Alerta: </td>
            <td width="35%" class="listado2">
                <?php echo dibujar_combos("txt_tipo_mensaje", $txt_tipo_mensaje); ?>
            </td>
        </tr>
        <tr>
            <td class="titulos2">Desde Fecha:</td>
            <td class="listado2">
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="40%" valign="top" height="22"><?php echo dibujar_calendario("txt_fecha_inicio", $txt_fecha_inicio, $ruta_raiz, "");?></td>
                        <td width="30%"><b>Hora:</b> <?php echo dibujar_combos("txt_hora_inicio", $txt_hora_inicio); ?></td>
                        <td width="30%"><b>Minuto:</b> <?php echo dibujar_combos("txt_min_inicio", $txt_min_inicio); ?></td>
                    </tr>
                </table>
                
            </td>
            <td class="titulos2">Hasta Fecha:</td>
            <td class="listado2">
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="40%" valign="top" height="22"><?php echo dibujar_calendario("txt_fecha_fin", $txt_fecha_fin, $ruta_raiz, "");?></td>
                        <td width="30%"><b>Hora:</b> <?php echo dibujar_combos("txt_hora_fin", $txt_hora_fin); ?></td>
                        <td width="30%"><b>Minuto:</b> <?php echo dibujar_combos("txt_min_fin", $txt_min_fin); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="titulos2">Mensaje: </td>
            <td colspan="3" class="listado2">
                <textarea name="txt_mensaje" id="txt_mensaje" rows="10" cols="50" style="width: 100%; height: 200px"><?php echo $txt_mensaje ?></textarea>
            </td>
        </tr>
        <tr>
            <td class="titulos2" rowspan="2">Usuarios con acceso: </td>
            <td colspan="3" class="listado1">
                <iframe height="100" width="100%" name="ifr_usr" id="ifr_usr" src="" style="border: none;">
                    Su navegador no soporta iframes, por favor actualicelo.</iframe>
                <form name="formulario" action="">
                    <input type="hidden" name="documento_us1" id="documento_us1" value="<?=$txt_usua_acceso?>">
                    <input type="hidden" name="documento_us2" id="documento_us2" value="-">
                    <input type="hidden" name="concopiaa" id="concopiaa" value="">
                    <input type="hidden" name="fl_modificar1" id="fl_modificar1" value="">
                    <input type="hidden" name="num_rad" id="num_rad" value="">
                    <input type="hidden" name="hidden_actualiza_opciones" id="hidden_actualiza_opciones" value="">
                    <input type="hidden" name="radi_tipo_impresion" id="radi_tipo_impresion" value="">
                    <input type="hidden" name="radi_lista_dest" id="radi_lista_dest" value="">
                    <input type="hidden" name="radi_lista_nombre" id="radi_lista_nombre" value="">
                    <input type="hidden" name="flag_inst" id="flag_inst" value="">
                    <script type="text/javascript">
                        function refrescar_pagina(e) {
                            document.getElementById('ifr_usr').src = '<?=$ruta_raiz?>/radicacion/lista_concopiaa.php?documento_us1=' +
                                                                     document.getElementById('documento_us1').value;
                        }
                    </script>
                </form>
            </td>
        </tr>
        <tr>
            <td colspan="3" class="listado2">
                <center>
                    <input type="button" name="btn_usuarios" id="btn_usuarios" value="Buscar Usuarios" class="botones_largo"
                           title="Buscar los usuarios que tendran acceso al sistema cuando este se bloquea"
                           onClick="buscar_usuarios();">&nbsp;&nbsp;
                </center>
            </td>
        </tr>
    </table>

    <br/>
    <input name="btn_aceptar" type="button" class="botones_largo" value="Aceptar" onClick="grabar_mensaje_alerta();">
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input  name="btn_accion" type="button" class="botones_largo" value="Cancelar" onClick="history.back();"/>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input  name="btn_accion" type="button" class="botones_largo" value="Ejecutar Ahora" onClick="ejecutar_cron_alertas()"/>
    <br><br>
    <div id="div_ejecutar_cron_alertas"></div>

    </center>
</body>
</html>
<?php

function dibujar_combos($nombre, $valor_default, $javascript="") {
    $valores = array();
    switch ($nombre) {
        case "txt_estado":
            $valores["0"] = "Cancelado";
            $valores["1"] = "Activo";
            $valores["2"] = "Eliminado";
            break;
        case "txt_tipo_mensaje":
            $valores["0"] = "Bloqueo General del Sistema";
            $valores["1"] = "Bloqueo a Nuevos Usuarios";
            $valores["2"] = "Mensaje a todos los usuarios";
            break;
        case "txt_hora_inicio":
        case "txt_hora_fin":
            for ($i=0; $i<24; ++$i) {
                $hora = substr("00".$i,-2);
                $valores[$hora] = $hora;
            }
            break;
        case "txt_min_inicio":
        case "txt_min_fin":
            for ($i=0; $i<60; $i+=5) {
                $hora = substr("00".$i,-2);
                $valores[$hora] = $hora;
            }
            break;

    }
    $cadena = "<select name='$nombre' id ='$nombre' class='select' onchange=\"$javascript\">";
    foreach ($valores as $key => $value) {
        $sel = (trim($valor_default)==trim($key)) ? "selected" : "";
        $cadena .= "<option value='$key' $sel>$value</option>";
    }
    $cadena .= "</select>";
    return $cadena;
}

?>