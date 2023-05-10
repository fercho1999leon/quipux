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
include_once "$ruta_raiz/funciones.php";
include_once "$ruta_raiz/obtenerdatos.php";
include_once "$ruta_raiz/include/tx/Tx.php";
include_once "$ruta_raiz/seguridad_documentos.php"; // Valida estados de los documentos y otras reglas dependiendo de la transacción realizada

$codTx = limpiar_sql($_POST['codTx']);
$comentario = limpiar_sql($_POST['txt_comentario']);
$fecha_max_tram = limpiar_sql($_POST['txt_fecha_tarea']);
$usua_codi_dest = $_POST['txt_usua_codi'];
$radicados = explode(",",$_POST['txt_radicados']);

$radicadosSel = array ();
foreach ($radicados as $radi_nume) {
    if (0+$radi_nume != 0) {
        $flag = validar_transacciones($codTx, $radi_nume, $db);
        if ($flag == "") {
            $whereFiltro .= ",$radi_nume";
            $radicadosSel[] = $radi_nume;
        } else
            $mensaje_error .= $flag;
    }
}

if ($whereFiltro === "0") {	//Si no se escogio ningun radicado
    die ("No hay documentos seleccionados.");
}

$tx = new Tx($db);

switch ($codTx)
{
    case 30:  //Eliminar Documentos
        $nombTx = "Asignar Tareas ";
        echo $tx->asignarTareas($radicadosSel, $usua_codi_dest, $fecha_max_tram, $comentario);
        break;
}

?>