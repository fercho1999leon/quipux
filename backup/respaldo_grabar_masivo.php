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

**************************************************************************************
** Graba las solicitudes de respaldos de la documentacion de los usuarios           **
**                                                                                  **
** Desarrollado por:                                                                **
**      Lorena Torres J.                                                            **
*************************************************************************************/

$ruta_raiz = "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
include_once "$ruta_raiz/funciones_interfaz.php";
require "respaldo_funciones.php";


//if($_SESSION["usua_perm_backup"]!=1) {
//    echo html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
//    die("");
//}

$txt_accion = trim(limpiar_numero($_POST["txt_accion"]));
$nombre_accion = "";

$lista_aprobar = $_POST["checkValue"];
$lista_rechazar = $_POST['txt_lista_soli_codigos'];
$fecha_ejecutar = trim(limpiar_sql($_POST['txt_fecha_ejecutar']));
$txt_comentario_rechazo = trim(limpiar_sql($_POST["txt_comentario_rechazo"]));

if($txt_accion==3)
    $titulo="Aprobación de Solicitudes";
else if($txt_accion==4)
    $titulo="Rechazo de Solicitudes";

if($txt_accion == 3 or $txt_accion == 4){
    $ruta_ventana = $ruta_raiz."/backup/respaldo_lista.php?txt_tipo_lista=2";
    $tipo_mensaje = "Debe seleccionar las solicitudes que requiere aprobar o rechazar.";
}
else if($txt_accion == 5){
    $ruta_ventana = $ruta_raiz."/backup/respaldo_lista.php?txt_tipo_lista=11";
    $tipo_mensaje = "Debe seleccionar las solicitudes que requiere calendarizar.";
}
else
    $ruta_ventana = $ruta_raiz."/backup/respaldo_menu.php";

if(!$lista_aprobar and !$lista_rechazar)
    $mensaje = $tipo_mensaje;
else{

    //Se inicia transacción
    if ($db->transaccion==0) $db->conn->BeginTrans();

    //Se consulta usuario que autoriza
    $usua_codi_autoriza = ObtenerCodigoUsuarioAutoriza(33,0,0,$_SESSION["usua_codi"],$db);   

    //Se ejecuta las acciones de: Autorizar o Rechazar
    switch ($txt_accion) {
        case "3": //Autoriza la solicitud
            $nombre_accion = "Aprobar";                     
            foreach ($lista_aprobar as $idLista=>$valor) {
                $datos["RESP_SOLI_CODI"] = limpiar_numero($idLista);
                $datos["USUA_CODI_AUTORIZA"] = $usua_codi_autoriza;
                $insertSQL = AutorizarSolicitud($datos, $db);  
            }
            $ruta_ventana = $ruta_raiz."/backup/respaldo_lista.php?txt_tipo_lista=2";
           break;
        case "4": //Rechaza la solicitud
            $nombre_accion = "Rechazar";
            foreach ($lista_rechazar as $idLista) {
                $datos["RESP_SOLI_CODI"] =limpiar_numero($idLista);
                $datos["USUA_CODI_AUTORIZA"] = $usua_codi_autoriza;
                $datos["COMENTARIO"] = $txt_comentario_rechazo;
                $insertSQL = RechazarSolicitud($datos, $db);
            }
            $ruta_ventana = $ruta_raiz."/backup/respaldo_lista.php?txt_tipo_lista=2";
            break;
        case "5": //Calendarizar fechas de ejecución de solicitud
            $nombre_accion = "Cambiar fecha de ejecución";
            foreach ($lista_rechazar as $idLista) {
                $datos["RESP_SOLI_CODI"] =limpiar_numero($idLista);
                $datos["FECHA_EJECUTAR"] = $fecha_ejecutar;
                $insertSQL = CambiarFechaEjecucion($datos, $db);
            }
            $ruta_ventana = $ruta_raiz."/backup/respaldo_lista.php?txt_tipo_lista=11";
            break;      
        default:
            break;
    }

   // echo "tran " . $insertSQL;
    
    //Se finaliza transacción
    if(!$insertSQL) {
        if ($db->transaccion==0){
            $db->conn->RollbackTrans();
            $mensaje = "Error no se realizó la acción de $nombre_accion sobre la(s) solicitud(es). <br> SQL: ".$db->conn->querySql;
        }
        else return 0;
    } else {
        if ($db->transaccion==0){
            $db->conn->CommitTrans();

            //Envío de correo
            switch ($txt_accion) {
                case "3": //Autoriza la solicitud
                    $nombre_accion = "Aprobar";
                    foreach ($lista_aprobar as $idLista=>$valor) {
                        //Se consulta datos de solicitud
                        $codigo = limpiar_numero($idLista);
                        $datos = ObtenerSolicitudPorCodigo($codigo,$db);
                        $destinatario = $datos["usua_codi_solicita"];
                        $remitente = $usua_codi_autoriza;
//                        //Se envía correo - Se comenta la notificación por requerimiento
//                        EnviarCorreo($txt_accion, $destinatario, $usua_codi_autoriza, $datos, $ruta_raiz, $db);
                    }
                   break;
                case "4": //Rechaza la solicitud
                    $nombre_accion = "Rechazar";
                    foreach ($lista_rechazar as $idLista) {
                        //Se consulta datos de solicitud
                        $codigo = limpiar_numero($idLista);
                        $datos = ObtenerSolicitudPorCodigo($codigo,$db);
                        $destinatario = $datos["usua_codi_solicita"];
                        $remitente = $usua_codi_autoriza;
                        //Se envía correo
                        EnviarCorreo($txt_accion, $destinatario, $usua_codi_autoriza, $datos, $ruta_raiz, $db);
                    }
                    break;
                default:
                    break;
            }           

            $mensaje = "Datos de solicitud guardados correctamente.";
        }
    }
}

echo "<html>".html_head();
echo "<center><br>
<table width='100%' border='1' align='center' class='t_bordeGris' id='usr_datos'>
 <tr><td class='titulos2' colspan='4' align='center'>$titulo</td></tr>
 <tr><td class='listado2' colspan='4' align='center'>$mensaje</td></tr>
</table></center></br>";
?>
<center>
<input type='button' name='btn_aceptar' value='Aceptar' class='botones' onClick='window.location="<?=$ruta_ventana?>"'>
</center>
</body>
</html>