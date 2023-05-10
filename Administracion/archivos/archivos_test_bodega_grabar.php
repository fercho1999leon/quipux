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

**************************************************************************************
** Respalda uno por uno los documentos de los usuarios                              **
** Busca los documentos que se deberán respaldar y los respalda uno por uno         **
** llamando a backup_usuarios_respaldar_documentos.php utilizando Ajax              **
**                                                                                  **
** Desarrollado por:                                                                **
**      Mauricio Haro A. - mauricioharo21@gmail.com                                 **
*************************************************************************************/

$ruta_raiz = "../..";
session_start();
if($_SESSION["perm_actualizar_sistema"]!=1) die("ERROR: Usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
include_once "$ruta_raiz/rec_session.php";

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();
include_once "$ruta_raiz/js/ajax.js";

?>
<script type="text/javascript">

    var timer_id_archivos_revertir_modulo = 0; // Temporizador
    var anio='2008';
    var offset = 0;
    var ejecutar = false;

    function fjs_play_copia() {
        ejecutar = true;
        document.getElementById('img_play').setAttribute('onclick', "");
        document.getElementById('spn_estado').innerHTML = 'Ejecutandose';
        for (i=0 ; i<300 ; ++i) {
            fjs_ejecutar_copia();
        }
        return;
    }

    function fjs_pausar_copia() {
        ejecutar = false;
        document.getElementById('spn_estado').innerHTML = 'Detenido';
        document.getElementById('img_play').setAttribute('onclick', "fjs_play_copia()");
        return;
    }

    function fjs_ejecutar_copia() {
        if (ejecutar == false) return;
        nuevoAjax('div_ejecutar_copia', 'POST', 'archivos_test_bodega_grabar_ejecutar.php', 'anio='+anio+'&offset='+offset, 'fjs_ejecutar_copia()');
        document.getElementById('spn_estado').innerHTML = 'Migrados '+offset+' archivos';
        ++offset;
        return;
    }

</script>

<body>
  <center>
    <br>
    <table width="90%" align="center" class=borde_tab border="0">
        <tr>
            <th width="100%" colspan="3">
              <center>
                  <br><b>TEST M&Oacute;DULO DE ARCHIVO</b><br>Copiar archivos del File System a la BDD<br>&nbsp;
              </center>
            </th>
        </tr>
        <tr>
            <td width="10%">&nbsp;</td>
            <td width="80%" align="left">
                <br>Ejecutar proceso:<br><br>
                <center>
                    A&ntilde;o que se realizará la migración:
                    <select name="slc_anio" class="select" id="slc_anio" onclick="anio=this.value; offset=0;">
                        <option value="2008">2008</option>
                        <option value="2009">2009</option>
                        <option value="2010">2010</option>
                        <option value="2011">2011</option>
                        <option value="2012">2012</option>
                        <option value="2013">2013</option>
                    </select>
                    <br><br>
                    <img src="<?=$ruta_raiz?>/imagenes/play.png" id="img_play" alt="ejecutar" style="width: 20px; height: 20px;" onclick='fjs_play_copia()'>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <img src="<?=$ruta_raiz?>/imagenes/pause.png" id="img_pause" alt="detener" style="width: 20px; height: 20px;" onclick='fjs_pausar_copia()'>
                </center>
                <br>
            </td>
            <td width="10%" align="right" valign="middle">
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td align="left">
                <br>Estado de la Ejecución: <b><span id="spn_estado">No iniciado</span></b><br><br>
                <div id="div_ejecutar_copia" style="width: 100%; text-align: center;"></div>
                <br>&nbsp;
            </td>
            <td>&nbsp;</td>
        </tr>
    </table>
    <br>

    <input type="button" name="btn_cancelar" value="Cerrar" class="botones_largo" onClick="window.close();">
  </center>

</body>
</html>
<?

?>