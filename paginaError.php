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

if (is_file("./config.php")) $ruta_raiz = ".";
elseif (is_file("../config.php")) $ruta_raiz = "..";
elseif (is_file("../../config.php")) $ruta_raiz = "../..";
else die ("Su sesi&oacute;n ha expirado o ha ingresado en otro equipo");
include "$ruta_raiz/config.php";
include_once "$ruta_raiz/funciones_interfaz.php";

$mensaje = "Su sesi&oacute;n ha expirado o ha ingresado en otro equipo <br><br>
            Para Ingresar haga, click &nbsp<a href='$nombre_servidor/login.php' target='_parent' class='aqui'>&quot;AQU&Iacute;&quot;</a><br>";

echo html_error($mensaje);
?>
