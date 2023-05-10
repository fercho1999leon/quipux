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

$ruta_raiz = "../..";
session_start();
if($_SESSION["perm_actualizar_sistema"]!=1) die("Usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
require_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/funciones_interfaz.php";

echo "<html>".html_head();
include_once "$ruta_raiz/js/ajax.js";
$paginador = new ADODB_Pager_Ajax($ruta_raiz, "div_listado_repositorios", "archivos_crear_repositorio_listado.php", "", "");

?>
<script type="text/javascript">
    function mostrar_estado_nuevo_repositorio() {
        document.getElementById('tr_crear_repositorio_botones_1').style.display = 'none';
        document.getElementById('tab_estado_nuevo_repositorio').style.display = '';
        nuevoAjax('div_estado_nuevo_repositorio', 'POST', 'archivos_crear_repositorio_estado.php', '');
    }

    function ocultar_estado_nuevo_repositorio() {
        document.getElementById('tr_crear_repositorio_botones_1').style.display = '';
        document.getElementById('tab_estado_nuevo_repositorio').style.display = 'none';
    }

    var timer_id_esperar_crear_nuevo_repositorio;
    function crear_nuevo_repositorio() {
        var parametros='';
        var txt_repos_tamanio = 0;
        var slc_repos_tablespace = trim(document.getElementById('slc_repos_tablespace').value);
        if (slc_repos_tablespace=='0') {
            alert ('Por favor seleccione el tablespace donde se creará el repositorio.');
            return;
        }
        try {
            txt_repos_tamanio = 0+parseInt(document.getElementById('txt_repos_tamanio').value);
        } catch (e) {
            txt_repos_tamanio = 0;
        }
        if (isNaN(txt_repos_tamanio) || txt_repos_tamanio==0) {
            alert ('Por favor ingrese un tamaño válido para el repositorio.');
            return;
        }
        document.getElementById('tr_crear_repositorio_botones_2').style.display = 'none';
        document.getElementById('tab_crear_nuevo_repositorio').style.display = '';
        document.getElementById('btn_aceptar_2').style.display='none';
        document.getElementById('div_crear_nuevo_repositorio').innerHTML = '<center><br><img src="../../iconos/spinner.gif">&nbsp;&nbsp;Por favor espere, se est&aacute; creando el nuevo repositorio<br><br></center>';
        parametros = 'slc_repos_tablespace=' + slc_repos_tablespace + '&txt_repos_tamanio=' + txt_repos_tamanio;
        nuevoAjax('div_crear_nuevo_repositorio', 'POST', 'archivos_crear_repositorio_ejecutar.php', parametros, "document.getElementById('btn_aceptar_2').style.display='';");
    }

    function recargar_nuevo_repositorio() {
        document.getElementById('tr_crear_repositorio_botones_1').style.display = '';
        document.getElementById('tr_crear_repositorio_botones_2').style.display = '';
        document.getElementById('tab_estado_nuevo_repositorio').style.display = 'none';
        document.getElementById('tab_crear_nuevo_repositorio').style.display = 'none';
        paginador_reload_div('');
    }


</script>

<body onLoad="paginador_reload_div('');">
    <center>
        <br>
        <table width="99%" cellpadding="0" cellspacing="3" border="0" class="borde_tab">
            <tr>
                <th width="100%">Listado de Repositorios</th>
            </tr>
            <tr>
                <td><div id="div_listado_repositorios"></div><br></td>
            </tr>
            <tr id="tr_crear_repositorio_botones_1">
                <td align="center">
                    <input type="button" name="btn_crear_nuevo" value="Crear Nuevo Repositorio" class="botones_largo" onclick="mostrar_estado_nuevo_repositorio()">
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type="button" name="btn_regresar" value="Regresar" class="botones_largo" onclick="history.back();">
                    <br><br>
                </td>
            </tr>
        </table>

        <br>
        <table width="99%" cellpadding="0" cellspacing="3" border="0" class="borde_tab" id="tab_estado_nuevo_repositorio" style="display: none;">
            <tr>
                <th width="100%">Datos del Nuevo Repositorio</th>
            </tr>
            <tr>
                <td><div id="div_estado_nuevo_repositorio" class="borde_tab"></div><br></td>
            </tr>
            <tr id="tr_crear_repositorio_botones_2">
                <td align="center">
                    <input type="button" name="btn_aceptar" value="Aceptar" class="botones_largo" onclick="crear_nuevo_repositorio();">
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type="button" name="btn_cancelar" value="Cancelar" class="botones_largo" onclick="ocultar_estado_nuevo_repositorio();">
                    <br><br>
                </td>
            </tr>
        </table>

        <br>
        <table width="99%" cellpadding="0" cellspacing="3" border="0" class="borde_tab" id="tab_crear_nuevo_repositorio" style="display: none;">
            <tr>
                <th width="100%">Creci&oacute;n de un Nuevo Repositorio</th>
            </tr>
            <tr>
                <td>
                    <br>
                    <div id="div_crear_nuevo_repositorio" style="width: 100%; text-align: center;">
                        <img src="../../iconos/spinner.gif" alt="">&nbsp;&nbsp;Por favor espere, se est&aacute; creando el nuevo repositorio
                    </div>
                    <br><br>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <input type="button" name="btn_aceptar_2" id="btn_aceptar_2" value="Aceptar" class="botones_largo" onclick="recargar_nuevo_repositorio()" style="display: none;">
                    <br><br>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>