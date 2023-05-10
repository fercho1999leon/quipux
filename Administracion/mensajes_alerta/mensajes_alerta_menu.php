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
include_once "$ruta_raiz/funciones_interfaz.php";
if ($_SESSION["admin_institucion"] != 1) {
    die( html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.") );
}

echo "<html>".html_head();
include_once "$ruta_raiz/js/ajax.js";

$paginador = new ADODB_Pager_Ajax($ruta_raiz, "div_paginador_lista_mensajes", "mensajes_alerta_paginador.php", "", "");
?>
<script type="text/javascript">
    function editar_mensaje(id_mensaje) {
        window.location = 'mensajes_alerta.php?txt_bloq_codi='+id_mensaje;
    }
    function ejecutar_cron_alertas() {
        nuevoAjax('div_ejecutar_cron_alertas', 'POST', '<?=$ruta_raiz?>/cron/generar_mensaje_alerta.php', '');
        alert ("Se ha ejecutado la alerta.");
    }
</script>
<body onload="paginador_reload_div('');">
    <center>
    <br>
    <table width="100%" border="1" align="center" class="t_bordeGris">
        <tr>
            <th colspan="4"><center>Administraci&oacute;n de Mensajes de Alerta y bloqueos del Sistema</center></th>
        </tr>
    </table>
    <br>
    <table width="100%" border="0" align="center" class="t_bordeGris">
        <tr>
            <td colspan="4" class="listados1"><center><div id="div_paginador_lista_mensajes"></div></center></td>
        </tr>
    </table>

    <br/>
    <input name="btn_aceptar" type="button" class="botones_largo" value="Nuevo Mensaje" onClick="editar_mensaje('0');">
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input  name="btn_accion" type="button" class="botones_largo" value="Cancelar" onClick="window.location='../formAdministracion.php'"/>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input  name="btn_accion" type="button" class="botones_largo" value="Ejecutar Ahora" onClick="ejecutar_cron_alertas()"/>
    <br><br>
    <div id="div_ejecutar_cron_alertas"></div>
    </center>
</body>