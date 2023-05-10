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
include_once "$ruta_raiz/include/db/ConnectionHandler.php";
include_once "$ruta_raiz/config.php";
include_once "$ruta_raiz/funciones.php";
$db = new ConnectionHandler("$ruta_raiz");
$db->conn->SetFetchMode(ADODB_FETCH_ASSOC);

$txt_login = limpiar_sql($_POST["txt_login"]);
$tipo_usuario = "";

// Verificamos si es usuario
$sql = "select usua_codi from usuarios where usua_login like upper('U$txt_login')";
$rs = $db->query($sql);
if ($rs and !$rs->EOF) $tipo_usuario .= "u";

// Verificamos si es ciudadano
$sql = "select ciu_codigo from ciudadano where ciu_cedula like '$txt_login'";
$rs = $db->query($sql);
if ($rs and !$rs->EOF) $tipo_usuario .= "c";

if ($tipo_usuario == "uc") { // Muestra el combo
    echo "<table border='0' cellpadding='0' cellspacing='0' width='100%'>
            <tr>
                <td align='right' width='30%'>Tipo de Usuario:</td>
                <td width='70%'>&nbsp;
                    <select name='tipo_usuario' id='tipo_usuario' class='select'>
                        <option value='u' selected>Funcionario P&uacute;blico</option>
                        <option value='c'>Ciudadano</option>
                    </select>
                </td>
            </tr>
          </table>";
} else {
    echo "<input type='hidden' name='tipo_usuario' id='tipo_usuario' value='$tipo_usuario'>";
}
?>
