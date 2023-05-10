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

//Datos del formulario
$txt_resp_soli_codi = trim(limpiar_sql($_POST["txt_resp_soli_codi"]));
$txt_usua_codi_solicita = trim(limpiar_sql($_POST["txt_usua_codi_solicita"]));
$txt_fecha_solicita = trim(limpiar_sql($_POST["txt_fecha_solicita"]));
$txt_fecha_inicio_doc = trim(limpiar_sql($_POST["txt_fecha_inicio_doc"]));
$txt_fecha_fin_doc = trim(limpiar_sql($_POST["txt_fecha_fin_doc"]));
$txt_comentario = trim(limpiar_sql($_POST["txt_comentario"]));
$txt_estado_solicitud = trim(limpiar_sql($_POST["txt_estado_solicitud"]));
$txt_estado_respaldo = trim(limpiar_sql($_POST["txt_estado_respaldo"]));
$txt_estado_nombre = trim(limpiar_sql($_POST["txt_estado_nombre"]));

$txt_usr_nombre = trim(limpiar_sql($_POST["txt_usr_nombre"]));
$txt_usr_apellido = trim(limpiar_sql($_POST["txt_usr_apellido"]));
$txt_accion = trim(limpiar_sql($_POST["txt_accion"]));
$txt_usr_depe = trim(limpiar_sql($_POST["txt_usr_depe"]));

$txt_comentario_rechazo = trim(limpiar_sql($_POST["txt_comentario_rechazo"]));
$txt_tipo_ventana= trim(limpiar_sql($_POST["txt_tipo_ventana"]));
$txt_cargo_tipo = trim(limpiar_sql($_POST["txt_cargo_tipo"]));
$txt_comentario_cancela = trim(limpiar_sql($_POST["txt_comentario_cancela"]));
$txt_tipo_lista= trim(limpiar_sql($_POST["txt_tipo_lista"]));

$fecha_ejecutar = trim(limpiar_sql($_POST['txt_fecha_ejecutar']));
$usr_depe_nombre = trim(limpiar_sql($_POST['txt_usr_depe_nombre']));

//var_dump($_POST);

//Datos de formulario
$datos = array();
unset($datos);
$datos["RESP_SOLI_CODI"] = $txt_resp_soli_codi;
$datos["USUA_CODI_SOLICITA"] = $txt_usua_codi_solicita;
$datos["FECHA_INICIO_DOC"] = "'".$txt_fecha_inicio_doc."'";
$datos["FECHA_FIN_DOC"] = "'".$txt_fecha_fin_doc." 23:59:59'";
$datos["COMENTARIO"] = $txt_comentario;
$datos["FECHA_SOLICITA"] = $txt_fecha_solicita;

$datos["USR_NOMBRE"] = $txt_usr_nombre;
$datos["USR_APELLIDO"] =$txt_usr_apellido;
$datos["TXT_ACCION"] = $txt_accion;
$datos["ESTADO_ACTUAL_SOL"] = $txt_estado_solicitud;
$datos["ESTADO_ACTUAL_RES"] = $txt_estado_respaldo;
$datos["ESTADO_SOL_NOMBRE"] = $txt_estado_nombre;
$datos["USUA_CARGO_TIPO"] = $txt_cargo_tipo;
$datos["USUA_DEPE"] = $txt_usr_depe;
$datos["SOL_NUEVA"] = 0;
$datos["RADI_NUME_RADI"] = $txt_codigo_documento;

//Se inicia transacción
if ($db->transaccion==0) $db->conn->BeginTrans();

//Se consulta usuario que autoriza
$usua_codi_autoriza = ObtenerCodigoUsuarioAutoriza(33,$txt_cargo_tipo,$txt_usua_codi_solicita,$_SESSION["usua_codi"], $db);

//Se ejecuta las acciones de: Guardar, Modificar, Autorizar o Rechazar
switch ($txt_accion) {
        case "1": //Guarda la solicitud
            if($txt_resp_soli_codi==""){
                $txt_resp_soli_codi = $db->nextId("sec_respaldo_solicitud");
                $datos["RESP_SOLI_CODI"] = $txt_resp_soli_codi;
                $datos["SOL_NUEVA"] = 1;
            }    
            $insertSQL = GuardarSolicitud($datos, $db);
            $mensaje = "Datos de solicitud guardados correctamente. <br> ";
            break;
        case "2": //Guarda y envía la solicitud
            if($txt_resp_soli_codi==""){
                $txt_resp_soli_codi = $db->nextId("sec_respaldo_solicitud");
                $datos["RESP_SOLI_CODI"] = $txt_resp_soli_codi;
                $datos["SOL_NUEVA"] = 1;
            }
//Se comenta autorización          
//            $usua_codi_autoriza_env = ObtenerCodigoUsuarioAutorizaPorDep(33, $txt_cargo_tipo,$txt_usua_codi_solicita,$txt_usr_depe, $db);
//            $datos["USUA_CODI_AUTORIZA"] = $usua_codi_autoriza_env;            
            $datos["USUA_CODI_AUTORIZA"] = $usua_codi_autoriza;
            $insertSQL = GuardarSolicitud($datos, $db);
            $destinatario = $usua_codi_autoriza;
            $remitente = $txt_usua_codi_solicita;
            $mensaje = "Datos de solicitud guardados correctamente. <br> ";
            break;
        case "3": //Autoriza la solicitud
            $datos["USUA_CODI_AUTORIZA"] = $usua_codi_autoriza;
            $insertSQL = AutorizarSolicitud($datos, $db);
            $destinatario = $txt_usua_codi_solicita;
            $remitente = $usua_codi_autoriza;
            $datos["ESTADO_SOL_NOMBRE"] = "Aprobada";
            $mensaje = "La solicitud de respaldo fue aprobada correctamente. <br> ";
            break;
        case "4": //Rechaza la solicitud            
            $datos["USUA_CODI_AUTORIZA"] = $usua_codi_autoriza;
            $datos["COMENTARIO"] = $txt_comentario_rechazo;
            $insertSQL = RechazarSolicitud($datos, $db);
            $destinatario = $txt_usua_codi_solicita;
            $remitente = $usua_codi_autoriza;
            $datos["ESTADO_SOL_NOMBRE"] = "Rechazada";
            $mensaje = "La solicitud de respaldo fue rechazada correctamente. <br> ";
            break;
        case "5": //Calendarizar fechas de ejecución de solicitud           
            $datos["FECHA_EJECUTAR"] = $fecha_ejecutar;
            $insertSQL = CambiarFechaEjecucion($datos, $db);
            $mensaje = "Datos de solicitud guardados correctamente. <br> ";
            break;
        case "6": //Eliminar solicitud
            $nombre_accion = "Eliminar";  
            $datos["RESP_SOLI_CODI"] =$txt_resp_soli_codi;
            $datos["COMENTARIO"] = $txt_comentario_cancela;
            $insertSQL = CancelarSolicitud($datos, $db);
            $destinatario = -1; //se consulta antes de enviar correo
            $remitente = 0; //se consulta antes de enviar correo
            $mensaje = "La solicitud de respaldo fue eliminada correctamente. <br> ";                       
            break;
        case "7": //Guarda, envía a aprueba la solicitud                 
            if($txt_resp_soli_codi==""){
                $txt_resp_soli_codi = $db->nextId("sec_respaldo_solicitud");
                $datos["RESP_SOLI_CODI"] = $txt_resp_soli_codi;
            }
            $datos["USUA_CODI_AUTORIZA"] = $usua_codi_autoriza;
            $insertSQL = GuardarSolicitud($datos, $db);
            $mensaje = "Datos de solicitud guardados correctamente. <br> ";
            break;
        default:
            break;
    }  

//Se finaliza transacción
if(!$insertSQL) {
    if ($db->transaccion==0){
        $db->conn->RollbackTrans();
        $mensaje = "Error no se guardó la solicitud de respaldo. <br> "; //SQL: ".$db->conn->querySql;
    }
    else return 0;
} else {
        
    if ($db->transaccion==0){
        $db->conn->CommitTrans();

        //Envío de correo, se valida para no enviar la notificación de aprobación
        if(($txt_accion != "1" and $txt_accion != "3") and ($destinatario != "")){            
            if($txt_accion=="6"){
                $codigo = $datos["RESP_SOLI_CODI"];
                $datos = ObtenerSolicitudPorCodigo($codigo,$db);
                $destinatario = $datos["usua_codi_solicita"];
                $remitente = $datos["usua_codi_accion"];
            }
            else{
                $codigo = $txt_resp_soli_codi;      
                $datos = ObtenerSolicitudPorCodigo($codigo,$db);                 
            }
            EnviarCorreo($txt_accion, $destinatario, $remitente, $datos, $ruta_raiz, $db);
        }
    }
}

//echo "<br> Us. Autoriza " . $usua_codi_autoriza . " acc ". $txt_accion . "<br>";
$mensaje_ad = "";
//Se comenta autorización
//if($usua_codi_autoriza_env == "" and $txt_accion == 2 )
//{
//    $mensaje_ad = " El área $usr_depe_nombre no tiene asignado una persona que apruebe la solicitud de respaldo. <br> Por favor comuníquese con el Administrador del Sistema de su Institución.";
//    $codigo = $txt_resp_soli_codi;
//    $datos = ObtenerSolicitudPorCodigo($codigo,$db);
//    $administradores = ConsultarAdministradores($db);        
//    foreach ($administradores as $idAdmin) {
//        $destinatario = $idAdmin["usua_codi"];
//        $remitente = -1;
//        $txt_accion = 8;
//        EnviarCorreo($txt_accion, $destinatario, $remitente, $datos, $ruta_raiz, $db);
//    }
//}

echo "<html>".html_head();
echo "<center><br>$mensaje</center></br>";
echo "<center><font color='blue'>$mensaje_ad</font></center></br>";
?>

<script language="JavaScript" type="text/javascript" >

   function metodoCerrar(ruta_raiz)
   {     
       tipo_ventana = document.getElementById("txt_tipo_ventana").value;
       ruta_raiz = document.getElementById("txt_ruta").value;
       tipo_lista = document.getElementById("txt_tipo_lista").value;      
       if(tipo_ventana == "popup")          
            window.close();
       else{
            if(tipo_lista == 1 || tipo_lista == 2 || tipo_lista == 6 || tipo_lista == 9 || tipo_lista == 11)
                window.location=ruta_raiz+"/backup/respaldo_lista.php?txt_tipo_lista="+tipo_lista;
            else if(tipo_lista == 8 || tipo_lista == 10)
                window.location=ruta_raiz+'/backup/backup_usuarios_menu.php';
            else if(tipo_lista == 13)
               window.location="backup_usuarios_estado.php";            
            else
                window.location=ruta_raiz+'/backup/respaldo_menu.php';
       }
   }

</script>
<form name="formulario" action="respaldo_solicitud_grabar.php" method="post">
<center>
<input type="hidden" name="txt_tipo_ventana" id="txt_tipo_ventana" value="<?php echo $txt_tipo_ventana; ?>"  maxlength="10">
<input type="hidden" name="txt_ruta" id="txt_ruta" value="<?php echo $ruta_raiz; ?>"  maxlength="10">
<input type="hidden" name="txt_tipo_lista" id="txt_tipo_lista" value="<?php echo $txt_tipo_lista; ?>"  maxlength="10">

<input type='button' name='btn_aceptar' value='Aceptar' class='botones' onClick='metodoCerrar();'>
</center>
  </body>
</html>
</form>