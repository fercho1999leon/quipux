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
$paginador = new ADODB_Pager_Ajax($ruta_raiz, "div_listado_repositorios", "archivos_crear_repositorio_listado.php", "", "accion=1");

?>
<script type="text/javascript">
    function fjs_seleccionar_repositorio(repos_codi) {
        nuevoAjax('div_estado_repositorio', 'POST', 'archivos_activar_repositorio_estado.php', 'repos_codi='+repos_codi);
    }

    function fjs_cambiar_estado_repositorio(repos_codi, repos_estado) {
        if (repos_estado==3) {
            if (!confirm('Está seguro de desactivar el repositorio?\nEsta acción no podrá deshacerse.'))
                return;
        }

        nuevoAjax('div_estado_repositorio', 'POST', 'archivos_activar_repositorio_ejecutar.php',
                  'repos_codi='+repos_codi+'&repos_estado='+repos_estado, "paginador_reload_div('');");
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
            <tr>
                <td align="center">
                    <input type="button" name="btn_regresar" value="Regresar" class="botones_largo" onclick="history.back();">
                    <br><br>
                </td>
            </tr>
        </table>

        <br>
        <div id="div_estado_repositorio" style="width: 99%"></div>
    </center>
</body>
</html>