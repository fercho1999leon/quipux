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
*
* Consulta los datos de una persona al Registro Civil
*
**/

$ruta_raiz = "../..";
require_once "$ruta_raiz/config.php";
require_once "$ruta_raiz/funciones.php";
include_once "$ruta_raiz/interconexion/validar_datos_ciudadano.php";

$cedula = trim(limpiar_sql($cedula));
$cedula = str_replace(array(" ","-","."), "", $cedula);

if(isset($tipo_identificacion))
    if ($tipo_identificacion==1) die("");

if (strlen($cedula)!=10) die("<center>El n&uacute;mero de c&eacute;dula ingresado no es v&aacute;lido.</center><br>");

// Consulto al registro civil
$datos_rc = ws_validar_datos_ciudadano($cedula);

?>

<table width="100%" class="borde_tab" border="0" cellpadding="0" cellspacing="5">
    <tr>
        <th colspan="4">
            <center>Datos tra&iacute;dos desde el Registro Civil</center>
        </th>
    </tr>
<? if ($datos_rc["error"]) { ?>
    <tr>
        <td class="listado1" colspan="4">
            <center><?= $datos_rc["descripcion"]?></center>
        </td>
    </tr>

<? } else { ?>
    <tr>
        <td class="listado2" width="20%">Nombres:</td>
        <td class="listado1" width="30%"><span id="lbl_datos_rc_nombre"><?= $datos_rc["nombre"]?></span><span id="lbl_datos_rc_apellido" style="display: none"><?= $datos_rc["nombre"]?></span></td>
        <td class="listado2" width="20%">G&eacute;nero:</td>
        <td class="listado1" width="30%"><span id="lbl_datos_rc_genero"><?= $datos_rc["genero"]?></span></td>
    </tr>
    <tr>
        <td class="listado2">Estado Civil:</td>
        <td class="listado1"><span id="lbl_datos_rc_estado_civil"><?= $datos_rc["estado_civil"]?></span></td>
        <td class="listado2">Direcci&oacute;n:</td>
        <td class="listado1"><span id="lbl_datos_rc_direccion"><?= $datos_rc["domicilio"]?></span></td>
    </tr>
<? } // Fin IF Error?>
</table>
<br>
