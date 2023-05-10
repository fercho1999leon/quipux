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
*
*   Ejecuta las actualizaciones de la BDD
 *
 *  Falta hacer el desarrollo para que ejecute queries individuales, para que ejecute automaticamente todas las sentencias,
 *  habilitar un botón para que consulte la siguiente sentencia, que se sincronice automaticamente con el servidor de actualizaciones, etc
*
***/
$ruta_raiz = "../..";
session_start();
if($_SESSION["perm_actualizar_sistema"]!=1) die("Usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
require_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/funciones_interfaz.php";

echo "<html>".html_head();
include_once "$ruta_raiz/js/ajax.js";
?>
<script type="text/javascript">
    var estado = "Pausa";
    var flag_detener = false; // Si aprieta el botón de pausar ejecucion
    var flag_validar_fechas = true; // Valida que se ejecute en el tiempo predefinido
    var timer_id_actualizar_bdd = 0;

    function consultar_siguiente_sentencia(txt_anio) {
        nuevoAjax('div_consultar_sentencia', 'POST', 'completar_bodega_consultar_estado.php', 'txt_anio='+txt_anio);
    }

    function ejecutar_sentencia() {
        if (estado == "Pausa" && document.getElementById('div_ejecutar_sentencia').innerHTML != "") {
            if (!validar_fechas()) {
                nuevoAjax('div_activar_sesion', 'POST', '../../radicacion/activar_session.php', '');
                tiempo_espera_ejecutar_sentencia(300); //tiempo en segundos
                return; // se validan las fechas ingresadas
            }

            document.getElementById('div_ejecutar_sentencia').innerHTML = "";
            txt_anio = document.getElementById('txt_anio').value;
            txt_num_registros = document.getElementById('txt_num_registros').value;
            nuevoAjax('div_ejecutar_sentencia', 'POST', 'completar_bodega_generar.php', 'txt_anio='+txt_anio+'&txt_num_registros='+txt_num_registros);
            estado = "Ejecutando";
            flag_detener = false;
            document.getElementById('lbl_estado').innerHTML = estado;
            miFecha = new Date();
            document.getElementById('lbl_hora_inicio_ejecucion').innerHTML = ('0'+miFecha.getHours()).slice(-2)+':'+('0'+miFecha.getMinutes()).slice(-2)+':'+('0'+miFecha.getSeconds()).slice(-2);
            document.getElementById('lbl_hora_fin').innerHTML = "";
            document.getElementById('btn_ejecutar').style.display = 'none';
            document.getElementById('btn_pausar').style.display = '';
            esperar_respuesta_ejecucion();
        }
        return;
    }

    function pausar_sentencia() {
        if(estado == "Ejecutando" || estado == "Pausa") {
            estado = "Pausa";
            flag_detener = true;
            document.getElementById('btn_ejecutar').style.display = '';
            document.getElementById('btn_pausar').style.display = 'none';
            document.getElementById('lbl_estado').innerHTML = estado;
            clearTimeout(timer_id_actualizar_bdd);
            document.getElementById('lbl_mensaje_tiempo_espera').innerHTML = '';
        }
        return;
    }

    var num_pdf_generados = 0;
    function esperar_respuesta_ejecucion() {
        try {
            if (estado == "Error") return;
            hora_inicio = trim(document.getElementById('txt_actu_hora_inicio').innerHTML);
            hora_fin = trim(document.getElementById('txt_actu_hora_fin').innerHTML);
            mensaje = trim(document.getElementById('txt_actu_mensaje').innerHTML);
            mensaje_error = trim(document.getElementById('txt_actu_mensaje_error').innerHTML);
            mensaje_fin = trim(document.getElementById('txt_actu_mensaje_fin').innerHTML);
            num_pdf_generados += parseInt(('0'+mensaje),10);

            if (trim(document.getElementById('lbl_hora_inicio').innerHTML) == '') {
                document.getElementById('lbl_hora_inicio').innerHTML = hora_inicio;
            }
            document.getElementById('lbl_hora_fin').innerHTML += hora_fin;

            // Verifico si existieron errores al ejecutar los queries y bloqueo todo
            if (mensaje_error == '') {
                document.getElementById('lbl_mensaje').innerHTML = 'Se han generado '+num_pdf_generados.toString()+' archivos PDF.';
                estado = "Pausa";
                document.getElementById('lbl_estado').innerHTML = estado;
            } else {
                document.getElementById('lbl_mensaje').innerHTML = mensaje_error;
                estado = "Error";
                document.getElementById('lbl_estado').innerHTML = estado;
                document.getElementById('lbl_mensaje_tiempo_espera').innerHTML = '';
                return;
            }

            if (mensaje_fin == "si" && document.getElementById('chk_ejecutar_siguiente').checked==true) {
                anio = parseInt(document.getElementById('txt_anio').value)+1;
                if (anio>=2008 && anio <=2012) {
                    obj = document.getElementById('txt_anio');
                    for (i=0; opt=obj.options[i];i++) {
                        if (opt.value == anio.toString()) opt.selected = true;
                    }
                    consultar_siguiente_sentencia(anio);
                    mensaje_fin = "no";
                    estado = "Pausa";
                    document.getElementById('lbl_estado').innerHTML = estado;
                }
            }

            if (mensaje_fin == "no" && !flag_detener) {
                // Si es un query que se ejecuta por partes
                tiempo_espera = 0+parseInt(document.getElementById('txt_tiempo_espera').value);
                if (isNaN(tiempo_espera)) tiempo_espera = 60;
                tiempo_espera_ejecutar_sentencia(tiempo_espera);

            } else {
                pausar_sentencia();
                if (mensaje_fin == "si") {
                    estado = "Finalizado";
                    document.getElementById('lbl_estado').innerHTML = estado;
                    document.getElementById('lbl_mensaje_tiempo_espera').innerHTML = '';
                }
                // Mostrar botón para ejecutar siguiente query
            }
        } catch (e) {
            timer_id_actualizar_bdd = setTimeout("esperar_respuesta_ejecucion()", 300);
            return;
        }
    }

    function tiempo_espera_ejecutar_sentencia(tiempo_espera) {
        var texto = '<img src="../../iconos/spinner.gif">&nbsp;&nbsp;';
        if (tiempo_espera <= 0) {
            document.getElementById('lbl_mensaje_tiempo_espera').innerHTML = texto + "Ejecutando sentencia";
            ejecutar_sentencia();
        } else {
            document.getElementById('lbl_mensaje_tiempo_espera').innerHTML = texto + "Faltan <b>"+tiempo_espera.toString()+"</b> segundos para volver a ejecutar la sentencia";
            timer_id_actualizar_bdd = setTimeout("tiempo_espera_ejecutar_sentencia("+(--tiempo_espera).toString()+");", 1000);
        }
    }

    function validar_fechas() {
        if (!flag_validar_fechas) return true; // si no se quiere que se validen las fechas
        var fecha_actual = new Date().getTime();
        var fecha_inicio = convertir_texto_a_fecha(document.getElementById('txt_fecha_inicio').value);
        var fecha_fin = convertir_texto_a_fecha(document.getElementById('txt_fecha_fin').value);
        if (fecha_inicio==0 || fecha_fin==0) {
            if (confirm('Las fechas ingresadas no son válidas. Desea ejecutar la sentencia de todos modos?')) {
                flag_validar_fechas = false;
                return true;
            } else {
                return false;
            }
        }
        if (fecha_inicio<fecha_actual && fecha_actual<fecha_fin) return true;
        return false;
    }

    function convertir_texto_a_fecha(cadena) {
        try {
            var cad = cadena.split(' ');
            var cad_fecha = cad[0].split('-');
            var cad_hora = cad[1].split(':');
            var fecha = new Date(parseInt(cad_fecha[0]),parseInt(cad_fecha[1])-1,parseInt(cad_fecha[2]),parseInt(cad_hora[0]),parseInt(cad_hora[1]),parseInt(cad_hora[2])).getTime();
            if (isNaN(fecha)) fecha = 0;
        } catch (e) {
            fecha = 0;
        }
        return fecha;
    }
</script>

<body onload="consultar_siguiente_sentencia('2008')">
  <center>
    <table width="90%" border="0" cellpadding="0" cellspacing="5" class="borde_tab">
        <tr>
            <th colspan="4">Estado actual de los documentos sin PDF</th>
        </tr>
        <tr>
            <td colspan="4" class="listado1" align="center">
                <div id="div_consultar_sentencia" class="borde_tab" style="height: 100px; width: 95%; overflow: auto; text-align: left;"></div>
                <br>
                A&ntilde;o a consultar: &nbsp;
                <select id="txt_anio" class="select" onchange="consultar_siguiente_sentencia(this.value)">
                    <option value="2008" selected>2008</option>
                    <option value="2009">2009</option>
                    <option value="2010">2010</option>
                    <option value="2011">2011</option>
                    <option value="2012">2012</option>
                </select>
            </td>

        </tr>
        <tr>
            <th colspan="4">Par&aacute;metros adicionales</th>
        </tr>
        <tr>
            <td colspan="4" class="listado1" align="center">
                <table width="95%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td width="30%"><b>Tiempo de espera entre ejecuci&oacute;n de queries:</b></td>
                        <td width="70%"><input type="text" name="txt_tiempo_espera" id="txt_tiempo_espera" value="20"> (tiempo en segundos)</td>
                    </tr>
                    <tr>
                        <td width="30%"><b>N&uacute;mero de registros por bloque:</b></td>
                        <td width="70%"><input type="text" name="txt_num_registros" id="txt_num_registros" value="10"></td>
                    </tr>
                    <tr>
                        <td><b>Fecha inicio de ejecuci&oacute;n:</b></td>
                        <td><input type="text" name="txt_fecha_inicio" id="txt_fecha_inicio" value="<?=date("Y-m-d 00:00:00")?>"> (yyyy-mm-dd HH:mm:ss)</td>
                    </tr>
                    <tr>
                        <td><b>Fecha fin de ejecuci&oacute;n:</b></td>
                        <td><input type="text" name="txt_fecha_fin" id="txt_fecha_fin" value="<?=date("Y-m-d 23:59:00")?>"> (yyyy-mm-dd HH:mm:ss)</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <br><input type="checkbox" name="chk_ejecutar_siguiente" id="chk_ejecutar_siguiente">
                            <b>Ejecutar el siguiente a&ntilde;o una vez que termine la ejecuci&oacute;n actual</b>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="4" class="listado1" align="center" valign="middle">
                <br><br>
                <input type="button" name="btn_ejecutar" id="btn_ejecutar" value="Ejecutar Sentencia" class="botones_largo" onclick="ejecutar_sentencia()" >
                <input type="button" name="btn_pausar" id="btn_pausar" value="Detener Ejecución" class="botones_largo" onclick="pausar_sentencia()" style="display: none;">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="button" name="btn_regresar" id="btn_regresar" value="Regresar" class="botones_largo" onclick="history.back();">
                <br>&nbsp;
            </td>
        </tr>
        <tr>
            <th colspan="4">Estado de la Ejecuci&oacute;n</th>
        </tr>
        <tr>
            <td width="45%" align="center"><b><font size="2">Avance</font></b></td>
            <td width="15%" align="center"><b><font size="2">Hora de Inicio</font></b></td>
            <td width="15%" align="center"><b><font size="2">Hora de Inicio Ejecuci&oacute;n</font></b></td>
            <td width="15%" align="center"><b><font size="2">Hora de Fin Ejecuci&oacute;n</font></b></td>
            <td width="10%" align="center"><b><font size="2">Estado</font></b></td>
        </tr>
        <tr>
            <td class="listado1" id="lbl_mensaje"></td>
            <td class="listado1" id="lbl_hora_inicio" align="center"></td>
            <td class="listado1" id="lbl_hora_inicio_ejecucion" align="center"></td>
            <td class="listado1" id="lbl_hora_fin" align="center"></td>
            <td class="listado1" id="lbl_estado" align="center"></td>
        </tr>
        <tr>
            <td colspan="4" class="listado1" align="center"><div id="lbl_mensaje_tiempo_espera"></div></td>
        </tr>
    </table>
    <div id="div_ejecutar_sentencia" class="borde_tab" style="display: none;">Iniciar</div>
    <div id="div_activar_sesion"></div>
  </center>
</body>