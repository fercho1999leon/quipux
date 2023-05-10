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
$usua_codi_dest = limpiar_sql($_POST['txt_usua_codi']);
$radicados = limpiar_sql($_POST['txt_radicados']);

$radicadosSel = array();

$sql = "select radi_nume_radi from radicado where radi_nume_radi in ($radicados)"; //,20110000110000000060
$rs = $db->conn->Execute($sql);
if ($rs && !$rs->EOF) {
    while (!$rs->EOF) {
        $ok = validar_transacciones($codTx, $rs->fields["RADI_NUME_RADI"], $db);
        if ($ok == "")
            $radicadosSel[] = $rs->fields["RADI_NUME_RADI"];
        else
            $mensaje_error .= $ok;
        $rs->MoveNext();
    }
} else {
    $mensaje_error = "No se encontraron documentos para realizar esta acci&oacute;n";
}


if (count($radicadosSel) == 0) {	//Si no se escogio ningun radicado
    die ($mensaje_error);
}
if ($mensaje_error!="") echo $mensaje_error;

$tx = new Tx($db);

switch ($codTx)
{
    case 30:  //Asignar Tareas
        echo $tx->asignarTareas($radicadosSel, $usua_codi_dest, $fecha_max_tram, $comentario);
        break;
    case 31:  //finalizar tareas
        $tarea_codi = limpiar_sql($_POST['tarea_codi']);
        $reasignar_respuesta = 0 + $_POST["txt_reasignar_respuesta"];
        echo $tx->finalizarTareas($tarea_codi, $comentario, $reasignar_respuesta);
        break;
    case 32:  //Cancelar tareas
        $tarea_codi = limpiar_sql($_POST['tarea_codi']);
        echo $tx->cancelarTareas($tarea_codi, $comentario);
        break;
    case 33:  //comentar tareas
        $tarea_codi = limpiar_sql($_POST['tarea_codi']);
        echo $tx->comentarTareas($tarea_codi, $comentario);
        break;
    case 34:  //Reabrir tareas
        $tarea_codi = limpiar_sql($_POST['tarea_codi']);
        echo $tx->reabrirTareas($tarea_codi, $fecha_max_tram, $comentario);
        break;
    case 35:  //editar tareas
        $tarea_codi = limpiar_sql($_POST['tarea_codi']);
        echo $tx->editarTareas($tarea_codi, $fecha_max_tram, $comentario);
        break;
    case 36:  //Registrar avance tareas
        $tarea_codi = limpiar_sql($_POST['tarea_codi']);
        $reasignar_respuesta = 0 + $_POST["txt_reasignar_respuesta"];
        $tarea_avance = 0+$_POST["txt_avance_tarea"];
        echo $tx->registrarAvanceTareas($tarea_codi, $tarea_avance, $comentario, $reasignar_respuesta);
        break;
}

?>