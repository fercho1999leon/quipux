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

$ruta_raiz = "../..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

session_start();
if($_SESSION["usua_admin_sistema"]!=1) {
  echo html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
  die("");
}
include_once "$ruta_raiz/rec_session.php";
?>

<table border=0 width="100%" class="borde_tab" cellpadding="0" cellspacing="5">
    <tr>
        <td width="30%" class="titulos5"><font class="tituloListado">Buscar usuarios por: </font></td>
        <td class="listado5" valign="middle">
        <table>
            <tr>
              <td><span class="listado5">Estado</span></td>
              <td>
                  <select name="cmb_estado" id="cmb_estado" class='select'>
                      <option value='1' selected>Activos</option>
                      <option value='0'>Inactivos</option>
                      <option value='2'>Todos</option>
                  </select>
              </td>
            </tr>
        </table>
        </td>
        <td width="20%" align="center" class="titulos5" >
            <input type="button" name="btn_buscar" value="Buscar" class="botones" onClick="consultar_usuarios(<?=$_GET['area']?>);">
        </td>
    </tr>
</table>