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

session_start();
$ruta_raiz = "..";

include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/tareas/tareas_funciones.php";
include_once "$ruta_raiz/obtenerdatos.php";

$tarea_codi = 0 + limpiar_numero($_POST["txt_tarea_codi"]);

?>

<center>
    <br>
    <div style="width: 98%">
        <? echo dibujar_detalle_tarea($db, $tarea_codi, "", $ruta_raiz, ""); ?>
    </div>
</center>
