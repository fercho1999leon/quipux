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

$ruta_raiz = "..";
include "$ruta_raiz/config.php";

//if ($nombre_servidor != $nombre_servidor_reportes)
//    session_id ($_GET["id_sess"]);

session_start();
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/funciones_interfaz.php";

if ($version_light) {
    die ("<br/><center><font size='3' color='blue'><b>Lo sentimos, esta funcionalidad se encuentra actualmente en mantenimiento.<br>Por favor vuelva a intentarlo m&aacute;s tarde. </b></font>");
}

if (isset($_POST["txt_tipo_reporte"]))
    $txt_tipo_reporte = limpiar_sql($_POST["txt_tipo_reporte"]);
else
    $txt_tipo_reporte = "01";
include "reportes_datos_reportes.php";


echo "<html>".html_head();
echo "<script language='JavaScript' src='$ruta_raiz/js/spiffyCal/spiffyCal_v2_1.js'></script>";
include_once "$ruta_raiz/js/ajax.js";

// Defino las fechas para los combos
$txt_fecha_minima = date("Y-m-d", strtotime(date("Y-m-d")." - $config_numero_meses month")); //Usada en javascript para ver los meses máximo para el reporte
$txt_fecha_desde = date("Y-m-d", strtotime(date("Y-m-d")." - 1 month"));
$txt_fecha_hasta = date("Y-m-d");

?>
  <script language="JavaScript" type="text/JavaScript">
    // Datos necesarios para que funcione el calendatio que se lo llama en reportes_criterios.php
    var dateAvailable1 = new ctlSpiffyCalendarBox("dateAvailable1", "formulario", "txt_fecha_desde","btnDate1","<?=$txt_fecha_desde?>",scBTNMODE_CUSTOMBLUE);
    dateAvailable1.dateFormat="yyyy-MM-dd";
    var dateAvailable2 = new ctlSpiffyCalendarBox("dateAvailable2", "formulario", "txt_fecha_hasta","btnDate2","<?=$txt_fecha_hasta?>",scBTNMODE_CUSTOMBLUE);
    dateAvailable2.dateFormat="yyyy-MM-dd";

    var drill_num = 0;
    var drill_reporte = new Array;

    function reportes_cambiar_reporte() {
        document.getElementById('txt_tipo_reporte').value = document.getElementById('slc_tipo_reporte').value;
        document.formulario.submit();
    }

    function popup_ver_documento(radicado) {
        windowprops = "top=50,left=50,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=700,height=500";
        url = '<?=$nombre_servidor?>/verradicado.php?verrad=' + radicado + '&estadisticas=1&menu_ver=3&tipo_ventana=popup';
        ventana = window.open(url , "ver_documento_" + radicado, windowprops);
        ventana.focus();
    }

    function convertir_texto_a_fecha(cadena) {
        try {
            var cad = cadena.split('-');
            var fecha = new Date(cad[0],cad[1],cad[2]);
        } catch (e) {
            fecha = 0;
        }
        return fecha;
    }

    function generar_reporte(id_reporte, param_reporte) {
        var fecha_desde = document.getElementById('txt_fecha_desde').value;
        var fecha_hasta = document.getElementById('txt_fecha_hasta').value;
        txt_lista_criterios = document.getElementById('txt_lista_criterios').value.replace(' ', '');
        txt_lista_columnas = document.getElementById('txt_lista_columnas').value.replace(' ', '');
        if (txt_lista_columnas=='') {
            alert ('Por favor seleccione las columnas que se mostrarán en el reporte.');
            return;
        }
        // Validamos que la diferencia entre fechas no supere el límite establecido en config
        var tiempo1 = convertir_texto_a_fecha('<?=date("Y-m-d")?>') - convertir_texto_a_fecha('<?=date("Y-m-d", strtotime(date("Y-m-d")." - $config_numero_meses month"))?>');
        var tiempo2 = convertir_texto_a_fecha(fecha_hasta) - convertir_texto_a_fecha(fecha_desde);
        if (tiempo2 > tiempo1) {
            alert ('El rango de fechas no puede superar los <?=$config_numero_meses?> meses.\nPor favor modifique las fechas del reporte.')
            return;
        }

        // Mostramos un gif mientras se genera el reporte
        document.getElementById('div_reporte').innerHTML = 'Por favor espere mientras se genera su reporte.<br>&nbsp;<br>' +
                                                                   '<img src="<?=$ruta_raiz?>/imagenes/progress_bar.gif"><br>&nbsp;';

        // Validamos el tipo de reporte
        id_reporte = id_reporte || document.getElementById('slc_tipo_reporte').value || 1;
        document.getElementById('txt_tipo_reporte').value = id_reporte;

        // Cargamos la lista de criterios
        tmp_array = txt_lista_criterios.split(",");
        parametros = 'txt_tipo_reporte=' + id_reporte + '&txt_lista_columnas=' + txt_lista_columnas;
        if (document.getElementById('chk_areas_dependientes').checked==true)
            parametros += '&chk_areas_dependientes=1';
        if ((param_reporte||'')=='') {
            for (i=0 ; i<tmp_array.length ; ++i) {
                try {
                    parametros += '&' + tmp_array[i] + '=' + document.getElementById(tmp_array[i]).value;
                } catch (e) {}
            }
        } else {
            parametros += param_reporte;
            // Añadimos los parametros que estaban preseleccionados para el reporte
            for (i=0 ; i<tmp_array.length ; ++i) {
                try {
                    if (parametros.indexOf(tmp_array[i]) < 0) {
                        parametros += '&' + tmp_array[i] + '=' + document.getElementById(tmp_array[i]).value;
                    }
                } catch (e) {}
            }
        }
        drill_reporte[++drill_num] = parametros;
        nuevoAjax('div_reporte', 'POST', 'reportes_generar.php', parametros);
        document.getElementById('div_datos_reporte').style.display = 'none';
        document.getElementById('div_reporte').style.display = '';
    }

    function generar_reporte_guardado() {
        txt_lista_columnas = document.getElementById('txt_lista_columnas').value.replace(' ', '');
        if (txt_lista_columnas=='') {
            alert ('Por favor seleccione las columnas que se mostrarán en el reporte.');
            return;
        }
        // Mostramos un gif mientras se genera el reporte
        document.getElementById('div_reporte').innerHTML = 'Por favor espere mientras se genera su reporte.<br>&nbsp;<br>' +
                                                                   '<img src="<?=$ruta_raiz?>/imagenes/progress_bar.gif"><br>&nbsp;';

        nuevoAjax('div_reporte', 'POST', 'reportes_generar.php', drill_reporte[drill_num]);
        document.getElementById('div_datos_reporte').style.display = 'none';
        document.getElementById('div_reporte').style.display = '';
    }

    function reportes_regresar() {
        if (--drill_num <= 0) {
            document.getElementById('div_datos_reporte').style.display = '';
            document.getElementById('div_reporte').style.display = 'none';
            drill_num = 0;
        } else {
            generar_reporte_guardado();
        }
    }

    function reportes_generar_guardar_como(tipo) {
        nuevoAjax('div_reporte_guardar_como', 'POST', 'reportes_generar_guardar_como.php', 'tipo='+tipo);
    }
  </script>
  <body>
    <div id="spiffycalendar" class="text"></div>

    <center>
      <form name="formulario" action="" method="post">
        <div id="div_datos_reporte" style="width: 99%">
            <br>
            <table width="100%" align="center" class="borde_tab" border="0">
              <tr>
                <th width="100%" colspan="2">
                  <center>
                    Reportes - Sistema de Gesti&oacute;n Documental &quot;Quipux&quot;
                  </center>
                </th>
              </tr>
              <!--tr>
                <td width="20%">&nbsp;</td>
                <td width="80%">&nbsp;</td>
              </tr-->
              <tr>
                <td width="16%" class="titulos5" valign="middle"><strong>Tipo de reporte:</strong></td>
                <td width="84%" class="listado1" valign="middle">
                    <?= $combo_reportes ?>
                    <input type='hidden' name='txt_tipo_reporte' id='txt_tipo_reporte' value="<?=$txt_tipo_reporte?>">
                </td>
              </tr>
              <tr>
                  <td class="titulos2" valign="middle"><strong>Descripci&oacute;n:</strong></td>
                <td class="listado1" valign="middle">
                    <?=$lista_reportes[$txt_tipo_reporte]["descripcion"]?>
                </td>
              </tr>
              <!--tr><td class="listado1" colspan="4">&nbsp;</td></tr-->
            </table>
            <br>
            <table width="100%" align="center" class="borde_tab" border="0">
              <tr>
                  <td class="listado1" valign="top" width="50%"><div id="div_criterios"><? include "reportes_criterios.php"?></div></td>
                  <td class="listado1" valign="top" width="50%"><div id="div_estructura"><? include "reportes_estructura.php"?></div></td>
              </tr>
            </table>
            <br>
            <input type='button' value='Generar Reporte' name='btn_generar' class='botones_largo' onClick="generar_reporte();">
        </div>
      </form>
      <br>
      <div id='div_reporte' style="width: 99%"></div>
      <div id='div_reporte_guardar_como' style="width: 99%"></div>
    </center>
  </body>
</html>

