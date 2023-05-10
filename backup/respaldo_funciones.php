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

**************************************************************************************
** Graba las solicitudes de respaldos de la documentacion de los usuarios           **
**                                                                                  **
** Desarrollado por:                                                                **
**      Lorena Torres J.                                                            **
*************************************************************************************/

//$ruta_raiz = "..";
//include_once "$ruta_raiz/rec_session.php";
//require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post

/*SOLICITUD*/
function GuardarSolicitud($datos, $db){
   
    $record = array();
    unset($record);
    $accion = array();
    unset($accion);
    
    //Datos a guardar usua_codi_accion    
    $record["RESP_SOLI_CODI"] = $datos["RESP_SOLI_CODI"];
    $record["USUA_CODI_SOLICITA"] = $datos["USUA_CODI_SOLICITA"];    
    $record["FECHA_INICIO_DOC"] = $datos["FECHA_INICIO_DOC"];
    $record["FECHA_FIN_DOC"] = $datos["FECHA_FIN_DOC"];    
    $record["COMENTARIO"] = "'".$datos["COMENTARIO"]."'";
    $record["FECHA_SOLICITA"] = $db->conn->sysTimeStamp;
    if($datos["RADI_NUME_RADI"]!="")
        $record["RADI_NUME_RADI"] = $datos["RADI_NUME_RADI"];

    //Se envía la solicitud
    if($datos["TXT_ACCION"]=="1"){ //Se guarda solicitud
        $txt_estado_solicitud = 1; //En edición
        $txt_estado_respaldo = 8;  //Por Generar
        $record["ESTADO_SOLICITUD"] = $txt_estado_solicitud;
        $record["ESTADO_RESPALDO"] = $txt_estado_respaldo;
        if($datos["SOL_NUEVA"] == 1)
        {
            $record["USUA_CODI_ACCION"] = $_SESSION["usua_codi"];
            $accion["ACCION"] = 71;
        }
        else
            $accion["ACCION"] = 81;
    }
    else if($datos["TXT_ACCION"]=="2" or $datos["TXT_ACCION"]=="7"){ //Se guarda y se envía solicitud
        $record["USUA_CODI_ACCION"] = $_SESSION["usua_codi"];
        $txt_estado_solicitud = 2; //Enviada
        $txt_estado_respaldo = 8;  //Por Generar
        $record["ESTADO_SOLICITUD"] = $txt_estado_solicitud;
        $record["ESTADO_RESPALDO"] = $txt_estado_respaldo;
        $accion["ACCION"] = 72;
    }
        
    //Se guarda la solicitud
    $insertSQL=$db->conn->Replace("RESPALDO_SOLICITUD", $record, "RESP_SOLI_CODI", false,false,true,false);
    //Se guarda el histórico
    if($insertSQL) {
        $accion["RESP_SOLI_CODI"] = $record["RESP_SOLI_CODI"];
        $accion["USUA_CODI"] = $_SESSION["usua_codi"];
        $accion["ESTADO_SOLICITUD"] = $record["ESTADO_SOLICITUD"];
        $accion["ESTADO_RESPALDO"] = $record["ESTADO_RESPALDO"];        
        $accion["COMENTARIO"] = $datos["COMENTARIO"];       
        
        //Se guarda histórico
        $insertSQL=GuardarHistoricoSolicitud($accion, $db);        
    }
    
//Se comenta autorización   
//    //Se autoriza la solicitud en caso de usuarios "Jefe" o que el super administrador realiza una solicitud
//    if(($datos["USUA_CARGO_TIPO"] == "1" and $datos["TXT_ACCION"]=="2") or ($datos["TXT_ACCION"]=="7")){
    if($datos["TXT_ACCION"]=="2" or $datos["TXT_ACCION"]=="7"){
       $insertSQL = AutorizarSolicitud($datos, $db);
    }

    return $insertSQL;
}

function AutorizarSolicitud($datos, $db){
   
    $record = array();
    unset($record);
    $accion = array();
    unset($accion);
    
    //Al autorizar la solicitud    
    $record["RESP_SOLI_CODI"] = $datos["RESP_SOLI_CODI"];
    $record["USUA_CODI_AUTORIZA"] = $datos["USUA_CODI_AUTORIZA"];
    $record["FECHA_EJECUTAR"] = $db->conn->sysTimeStamp;
    $record["ESTADO_SOLICITUD"] = 3; //Estado Aprobado
    $record["ESTADO_RESPALDO"] = 8;
 
    //Se autoriza la solicitud
    $insertSQL=$db->conn->Replace("RESPALDO_SOLICITUD", $record, "RESP_SOLI_CODI", false,false,true,false);
    //Se guarda el histórico
    if($insertSQL) {
        $accion["RESP_SOLI_CODI"] = $record["RESP_SOLI_CODI"];
        $accion["USUA_CODI"] = $_SESSION["usua_codi"];
        $accion["ESTADO_SOLICITUD"] = $record["ESTADO_SOLICITUD"];
        $accion["ESTADO_RESPALDO"] = $record["ESTADO_RESPALDO"];
        $accion["COMENTARIO"] = "";
        $accion["ACCION"] = 73;
        
        //Se guarda histórico
        $insertSQL=GuardarHistoricoSolicitud($accion, $db);

        //Se consulta datos de solicitud
        $resp_soli_codi = $record["RESP_SOLI_CODI"];
         if(($datos["USUA_CARGO_TIPO"] == "1" and $datos["TXT_ACCION"]=="2") or ($datos["TXT_ACCION"]=="7")){
            $usua_codi_solicita = $datos["USUA_CODI_SOLICITA"];
            $fecha_inicio_doc = $datos["FECHA_INICIO_DOC"];
            $fecha_fin_doc = $datos["FECHA_FIN_DOC"];
         }
         else{
            $solicitud = ObtenerSolicitudPorCodigo($resp_soli_codi,$db);
            $usua_codi_solicita = $solicitud["usua_codi_solicita"];
            $fecha_inicio_doc = "'".$solicitud["fecha_inicio_doc"]."'";
            $fecha_fin_doc = "'".$solicitud["fecha_fin_doc"]."'";
         }

        //Se agrega registro en respaldo usuario para ejecutar los respaldos
        $insertSQL = SolicitarRespaldo($usua_codi_solicita,$resp_soli_codi,$fecha_inicio_doc, $fecha_fin_doc, $db);
    }  

    return $insertSQL;
}

function RechazarSolicitud($datos, $db){

    $record = array();
    unset($record);
    $accion = array();
    unset($accion);

    //Al autorizar la solicitud
    $record["RESP_SOLI_CODI"] = $datos["RESP_SOLI_CODI"];
    $record["USUA_CODI_AUTORIZA"] = $datos["USUA_CODI_AUTORIZA"];
    $record["ESTADO_SOLICITUD"] = 4; //Estado rechazado
    $record["ESTADO_RESPALDO"] = 10;  //Estado rechazado
   
    //Se autoriza la solicitud
        $insertSQL=$db->conn->Replace("RESPALDO_SOLICITUD", $record, "RESP_SOLI_CODI", false,false,true,false);
    //Se guarda el histórico
    if($insertSQL) {
        $accion["RESP_SOLI_CODI"] = $record["RESP_SOLI_CODI"];
        $accion["USUA_CODI"] = $_SESSION["usua_codi"];
        $accion["ACCION"] = 74;
        $accion["COMENTARIO"] = $datos["COMENTARIO"];
        $accion["ESTADO_SOLICITUD"] = $record["ESTADO_SOLICITUD"];
        $accion["ESTADO_RESPALDO"] = $record["ESTADO_RESPALDO"];

        //Se guarda histórico
        $insertSQL=GuardarHistoricoSolicitud($accion, $db);
    }   

    return $insertSQL;
}

function CancelarSolicitud($datos, $db){

    $record = array();
    unset($record);
    $accion = array();
    unset($accion);

    //Se consulta datos de solicitud    
    $solicitud = ObtenerSolicitudPorCodigo($datos["RESP_SOLI_CODI"],$db);
    if($solicitud["estado_respaldo"] == 12){ //Respaldo Generado

        //Se cambia estado de respaldo
        $estado_respaldo = 15; //Estado eliminado    
        $record["RESP_SOLI_CODI"] = $datos["RESP_SOLI_CODI"];
        $record["ESTADO_RESPALDO"] = $estado_respaldo;
        //Se guardan datos
        $insertSQL=$db->conn->Replace("RESPALDO_SOLICITUD", $record, "RESP_SOLI_CODI", false,false,true,false);

        //Se guarda el histórico
        if($insertSQL) {
            $accion["RESP_SOLI_CODI"] = $datos["RESP_SOLI_CODI"];
            $accion["USUA_CODI"] = $_SESSION["usua_codi"];
            $accion["ACCION"] = 78;
            $accion["COMENTARIO"] = $datos["COMENTARIO"];
            $accion["ESTADO_SOLICITUD"] = $solicitud["estado_solicitud"];
            $accion["ESTADO_RESPALDO"] = $estado_respaldo;
            //Se guardan datos
            $insertSQL=GuardarHistoricoSolicitud($accion, $db);
        }
    }
    else{
        $record["RESP_SOLI_CODI"] = $datos["RESP_SOLI_CODI"];
        $record["ESTADO_SOLICITUD"] = 14; //Estado cancelado
        $record["ESTADO_RESPALDO"] = 15;  //Estado eliminado

        //Se cancela la solicitud
        $insertSQL=$db->conn->Replace("RESPALDO_SOLICITUD", $record, "RESP_SOLI_CODI", false,false,true,false);

        //Se guarda el histórico
        if($insertSQL) {
            $accion["RESP_SOLI_CODI"] = $record["RESP_SOLI_CODI"];
            $accion["USUA_CODI"] = $_SESSION["usua_codi"];
            $accion["ACCION"] = 79;
            $accion["COMENTARIO"] = $datos["COMENTARIO"];
            $accion["ESTADO_SOLICITUD"] = $record["ESTADO_SOLICITUD"];
            $accion["ESTADO_RESPALDO"] = $record["ESTADO_RESPALDO"];

            //Se guarda histórico
            $insertSQL=GuardarHistoricoSolicitud($accion, $db);
        }
    }

    return $insertSQL;
}

function CambiarFechaEjecucion($datos, $db){

    $record = array();
    unset($record);
    $accion = array();
    unset($accion);

    //Se consulta datos de solicitud
    $solicitud = ObtenerSolicitudPorCodigo($datos["RESP_SOLI_CODI"],$db);

    //Al cambiar fecha de ejecución
    $record["RESP_SOLI_CODI"] = $datos["RESP_SOLI_CODI"];
    $record["FECHA_EJECUTAR"] = "'".$datos["FECHA_EJECUTAR"]."'";
   
    //Se autoriza la solicitud
    $insertSQL=$db->conn->Replace("RESPALDO_SOLICITUD", $record, "RESP_SOLI_CODI", false,false,true,false);
    //Se guarda el histórico
    if($insertSQL) {
        $accion["RESP_SOLI_CODI"] = $record["RESP_SOLI_CODI"];
        $accion["USUA_CODI"] = $_SESSION["usua_codi"];
        $accion["ACCION"] = 80;
        $accion["COMENTARIO"] = "Se cambia fecha de ejecución de respaldo a: " . $datos["FECHA_EJECUTAR"];
        $accion["ESTADO_SOLICITUD"] = $solicitud["estado_solicitud"];
        $accion["ESTADO_RESPALDO"] = $solicitud["estado_respaldo"];

        //Se guarda histórico
        $insertSQL=GuardarHistoricoSolicitud($accion, $db);
    }

    return $insertSQL;
}

/*SOLICITUD HISTÓRICO*/
function GuardarHistoricoSolicitud($accion, $db){
   //Datos a guardar
    $record = array();
    unset($record);
    $record["RESP_SOLI_CODI"] = $accion["RESP_SOLI_CODI"];
    $record["USUA_CODI"] = $accion["USUA_CODI"];
    $record["FECHA"] = $db->conn->sysTimeStamp;
    $record["ACCION"] = $accion["ACCION"];
    $record["COMENTARIO"] = "'".$accion["COMENTARIO"]."'";   
    $record["ESTADO_SOLICITUD"] = $accion["ESTADO_SOLICITUD"];
    $record["ESTADO_RESPALDO"] = $accion["ESTADO_RESPALDO"];
    $insertSQL=$db->conn->Replace("RESPALDO_HIST_EVENTOS", $record, "RESP_HIST_EVENTOS", false,false,true,false);
    return $insertSQL;

}

function ObtenerSolicitudPorCodigo($codigo, $db){

    $sql = "select ru.*, estsol.est_nombre_estado as estado_nombre_sol, estresp.est_nombre_estado 
    as estado_nombre_resp, resp_hist.comentario as comentario_hist, us.usua_codi 
    as usua_codi_sol, us.usua_cedula as usua_ced_sol, (us.usua_nomb || ' ' || us.usua_apellido) 
    as usua_nombre_sol, us.usua_email as usua_email_sol, us.usua_cargo as usua_cargo_sol, 
    us.usua_abr_titulo as abr_titulo_sol, us.depe_codi as usua_depe_codi, 
    us.inst_codi as usua_inst_codi, us.depe_nomb as usua_depe_nomb,us.inst_nombre as 
    usua_inst_nomb, usa.usua_codi as usua_codi_aut, usa.usua_cedula as usua_ced_aut, 
    (usa.usua_nomb || ' ' || usa.usua_apellido) as usua_nombre_aut, usa.usua_email as
    usua_email_aut, usa.usua_cargo as usua_cargo_aut, usa.usua_abr_titulo as abr_titulo_aut,
    usac.usua_codi as usua_codi_acc, usac.usua_cedula as usua_ced_acc, 
    (usac.usua_nomb || ' ' || usac.usua_apellido) as usua_nombre_acc, usac.usua_email as
    usua_email_acc, usac.usua_cargo as usua_cargo_acc, usac.usua_abr_titulo as abr_titulo_acc
    from respaldo_solicitud ru
    left outer join (select * from respaldo_estado where est_tipo=1) as estsol
    on ru.estado_solicitud = estsol.est_codi
    left outer join (select * from respaldo_estado where est_tipo=2) as estresp
    on ru.estado_respaldo = estresp.est_codi
    left outer join (select * from respaldo_hist_eventos
    where resp_hist_eventos = (select max(resp_hist_eventos)
    from respaldo_hist_eventos where resp_soli_codi=$codigo and (estado_solicitud = 4 or estado_respaldo = 15))) as resp_hist
    on ru.resp_soli_codi = resp_hist.resp_soli_codi
    left outer join usuario as us on us.usua_codi = ru.usua_codi_solicita 
    left outer join usuario as usa on usa.usua_codi = ru.usua_codi_autoriza 
    left outer join usuario as usac on usac.usua_codi = resp_hist.usua_codi
    where ru.resp_soli_codi=$codigo";
    
    $rs = $db->conn->Execute($sql);
//    echo $sql;
    $vector = array();
    unset($vector);
    $vector["resp_soli_codi"] = trim($rs->fields["RESP_SOLI_CODI"]);
    $vector["usua_codi_solicita"] = trim($rs->fields["USUA_CODI_SOLICITA"]);
    $vector["usua_codi_autoriza"] = $rs->fields["USUA_CODI_AUTORIZA"];
    $vector["usua_codi_accion"] = $rs->fields["USUA_CODI_ACC"];
    $vector["fecha_solicita"] = $rs->fields["FECHA_SOLICITA"];
    $vector["fecha_inicio_doc"] = $rs->fields["FECHA_INICIO_DOC"];
    $vector["fecha_fin_doc"] = $rs->fields["FECHA_FIN_DOC"];
    $vector["fecha_inicio_ejec"] =$rs->fields["FECHA_INICIO_EJEC"];
    $vector["fecha_fin_ejec"] = $rs->fields["FECHA_FIN_EJEC"];
    $vector["estado_solicitud"] = $rs->fields["ESTADO_SOLICITUD"];
    $vector["estado_respaldo"] = $rs->fields["ESTADO_RESPALDO"];
    $vector["comentario"] = $rs->fields["COMENTARIO"];
    $vector["estado_nombre_sol"] = $rs->fields["ESTADO_NOMBRE_SOL"];
    $vector["estado_nombre_resp"] = $rs->fields["ESTADO_NOMBRE_RESP"];
    $vector["comentario_hist"] = $rs->fields["COMENTARIO_HIST"];

    $vector["usua_nombre_sol"] = $rs->fields["USUA_NOMBRE_SOL"];
    $vector["usua_email_sol"] = $rs->fields["USUA_EMAIL_SOL"];
    $vector["usua_cargo_sol"] = $rs->fields["USUA_CARGO_SOL"];
    $vector["abr_titulo_sol"] = $rs->fields["ABR_TITULO_SOL"];
    $vector["usua_depe_nomb"] = $rs->fields["USUA_DEPE_NOMB"];
    $vector["usua_inst_nomb"] = $rs->fields["USUA_INST_NOMB"];

    $vector["usua_nombre_aut"] = $rs->fields["USUA_NOMBRE_AUT"];
    $vector["usua_email_aut"] = $rs->fields["USUA_EMAIL_AUT"];
    $vector["usua_cargo_aut"] = $rs->fields["USUA_CARGO_AUT"];
    $vector["abr_titulo_aut"] = $rs->fields["ABR_TITULO_AUT"];

    //var_dump($vector);
    return $vector;

}

function ObtenerSolicitudPorCodigoResp($resp_codi, $db){    

    $sql = "select * from respaldo_solicitud where resp_codi = $resp_codi";   
    $rs = $db->conn->Execute($sql);

    $vector = array();
    unset($vector);
    $vector["resp_soli_codi"] = trim($rs->fields["RESP_SOLI_CODI"]);
    $vector["usua_codi_solicita"] = trim($rs->fields["USUA_CODI_SOLICITA"]);
    $vector["usua_codi_autoriza"] = $rs->fields["USUA_CODI_AUTORIZA"];
    $vector["fecha_solicita"] = $rs->fields["FECHA_SOLICITA"];
    $vector["fecha_inicio_doc"] = $rs->fields["FECHA_INICIO_DOC"];
    $vector["fecha_fin_doc"] = $rs->fields["FECHA_FIN_DOC"];
    $vector["fecha_inicio_ejec"] =$rs->fields["FECHA_INICIO_EJEC"];
    $vector["fecha_fin_ejec"] = $rs->fields["FECHA_FIN_EJEC"];
    $vector["estado_solicitud"] = $rs->fields["ESTADO_SOLICITUD"];
    $vector["estado_respaldo"] = $rs->fields["ESTADO_RESPALDO"];
    $vector["comentario"] = $rs->fields["COMENTARIO"];
    $vector["usua_codi_accion"] = $rs->fields["USUA_CODI_ACCION"];
    $vector["resp_codi"] = $rs->fields["RESP_CODI"];

    //var_dump($vector);
    return $vector;

}

function ObtenerCodigoUsuarioAutoriza($permiso, $usua_cargo_tipo, $usua_codi_solicita,$usua_codi_act, $db){

//Se comenta autorización
//    if($_SESSION["usua_codi"] != 0)
//    {
//        if($usua_cargo_tipo == 1){ //Perfil Jefe
//            $usua_codi = $usua_codi_solicita;
//        }
//        else { //Perfil Normal
//            $sql = "select pd.usua_codi from permiso_usuario_dep as pd
//            where pd.id_permiso = $permiso
//            and pd.usua_codi = $usua_codi_act limit 1";            
//            $rs = $db->conn->Execute($sql);
//            $usua_codi = trim($rs->fields["USUA_CODI"]);
//        }         
//    }
//    else
//         $usua_codi = $_SESSION["usua_codi"];
    
    $usua_codi = 0;
    return $usua_codi;

}

//Obtiene el codigo del usuario que autoriza en una dependencia
function ObtenerCodigoUsuarioAutorizaPorDep($permiso,$usua_cargo_tipo, $usua_codi_solicita, $usua_depe, $db){

    if($_SESSION["usua_codi"] != 0)
    {
        if($usua_cargo_tipo == 1){ //Perfil Jefe
            $usua_codi = $usua_codi_solicita;
        }
        else { //Perfil Normal
            $sql = "select pd.usua_codi from permiso_usuario_dep as pd
            where pd.id_permiso = $permiso
            and pd.depe_codi = $usua_depe";            
            $rs = $db->conn->Execute($sql);  
            $usua_codi = trim($rs->fields["USUA_CODI"]);
        }         
    }
    else
         $usua_codi = $_SESSION["usua_codi"];
    return $usua_codi;

}

function EnviarCorreo($accion, $destinatario, $remitente, $datos, $ruta_raiz, $db){

    include_once "$ruta_raiz/obtenerdatos.php";		//Consulta de datos de los usuarios y radicados
    
    if ($remitente == $destinatario) return;

    if($accion == 9){ //Acción de envío de correo a soporte@informatica.gob.ec
        //Se consulta datos de remitente
        $remite = ObtenerDatosUsuario($remitente, $db);
    }else{
        //Se consulta si el destinatario puede recibir notificaciones
        if (ObtenerPermisoUsuario($destinatario, 21, $db) == 0) return;
        //Se consulta datos de destinatario
        $dest = ObtenerDatosUsuario($destinatario, $db);
    }
    
    //   
    include "$ruta_raiz/config.php";
            
    if(($accion == 9) || ($dest["email"]!="" and strpos($dest["email"],"@") and strpos($dest["email"],".",strpos($dest["email"],"@"))))
    {          

            /**
            * Estructura de la descripcion de email.
            */
            if($accion == 9){ //Acción de envío de correo a soporte@informatica.gob.ec
                $asunto = " - Solicitud para Descarga de Respaldo ".$datos["resp_soli_codi"];
                $mail_body = "<html><title>Informaci&oacute;n Quipux</title>";
                $mail_body .= "<body><center><h2>Sistema de Gesti&oacute;n Documental Quipux</h2><br><br></center>";
                $mail_body .= "Estimado:<br>Equipo de Soporte<br><br>";
            }else{
                 $asunto = " - Solicitud de Respaldo ".$datos["resp_soli_codi"];
                $mail_body = "<html><title>Informaci&oacute;n Quipux</title>";
                $mail_body .= "<body><center><h2>Sistema de Gesti&oacute;n Documental Quipux</h2><br><br></center>";
                $mail_body .= "Estimado(a):<br><br>".$dest["abr_titulo"] . " " . $dest["nombre"] . "<br>" . $dest["cargo"]. "<br><br>";
            }

            switch ($accion) {
                case 2: //Envío de solicitud
                    $mail_body .= "Ha recibido una solicitud de respaldo de documentos.";
                    $mail_body .= "<br><br>Informaci&oacute;n de la solicitud:<br><br>";
                    $mail_body .= "<table border='0' width='100%'><tr><td width='30%'><b>Fecha:</b></td><td width='70%'>".date("Y-m-d H:i")."</td></tr>";
                    $mail_body .= "<tr><td><b>No. de solicitud:</b></td><td>".$datos["resp_soli_codi"]."</td></tr>";
                    $mail_body .= "<tr><td><b>Fecha de solicitud:</b></td><td>". date('Y-m-d H:i', strtotime($datos["fecha_solicita"]))."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Solicitado por:</b></td><td>".$datos["abr_titulo_sol"] . " " . $datos["usua_nombre_sol"] .
                                  "<br>" . $datos["usua_cargo_sol"] . "<br><a href='mailto:" . $datos["usua_email_sol"]. "'>" . $datos["usua_email_sol"]. "</a></td></tr>";
                    $mail_body .= "<tr><td><b>Estado:</b></td><td>".$datos["estado_nombre_sol"]."</td></tr></table>";                    
                    $mail_body .= "<br><br>Por favor revise su bandeja de Solicitudes por Autorizar en el sistema &quot;<a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>&quot;";
                    break;
                case 3: //Autorización de solicitud
                    $mail_body .= "Se ha realizado un cambio de estado de la solicitud de respaldo de documentos.";
                    $mail_body .= "<br><br>Informaci&oacute;n de la solicitud:<br><br>";
                    $mail_body .= "<table border='0' width='100%'><tr><td width='30%'><b>Fecha:</b></td><td width='70%'>".date("Y-m-d H:i")."</td></tr>";
                    $mail_body .= "<tr><td><b>No. de solicitud:</b></td><td>".$datos["resp_soli_codi"]."</td></tr>";
                    $mail_body .= "<tr><td><b>Fecha de solicitud:</b></td><td>".  date('Y-m-d H:i', strtotime($datos["fecha_solicita"]))."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Aprobado por:</b></td><td>".$datos["abr_titulo_aut"] . " " . $datos["usua_nombre_aut"] .
                                  "<br>" . $datos["usua_cargo_aut"] . "<br><a href='mailto:" . $datos["usua_email_aut"]. "'>" . $datos["usua_email_aut"]. "</a></td></tr>";
                    $mail_body .= "<tr><td><b>Estado:</b></td><td>".$datos["estado_nombre_sol"]."</td></tr></table>";
                    $mail_body .= "<br><br>Por favor revise su bandeja de Solicitudes en el sistema &quot;<a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>&quot;";
                    break;
                case 4: //Rechazo de solicitud
                    $mail_body .= "Se ha realizado un cambio de estado de la solicitud de respaldo de documentos.";
                    $mail_body .= "<br><br>Informaci&oacute;n de la solicitud:<br><br>";
                    $mail_body .= "<table border='0' width='100%'><tr><td width='30%'><b>Fecha:</b></td><td width='70%'>".date("Y-m-d H:i")."</td></tr>";
                    $mail_body .= "<tr><td><b>No. de solicitud:</b></td><td>".$datos["resp_soli_codi"]."</td></tr>";
                    $mail_body .= "<tr><td><b>Fecha de solicitud:</b></td><td>".  date('Y-m-d H:i', strtotime($datos["fecha_solicita"])) ."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Rechazado por:</b></td><td>".$datos["abr_titulo_aut"] . " " . $datos["usua_nombre_aut"] .
                                  "<br>" . $datos["usua_cargo_aut"] . "<br><a href='mailto:" . $datos["usua_email_aut"]. "'>" . $datos["usua_email_aut"]. "</a></td></tr>";
                    $mail_body .= "<tr><td><b>Estado:</b></td><td>".$datos["estado_nombre_sol"]."</td></tr>";
                    $mail_body .= "<tr><td><b>Comentario:</b></td><td>".$datos["comentario_hist"]."</td></tr></table>";
                    $mail_body .= "<br><br>Por favor revise su bandeja de Solicitudes en el sistema &quot;<a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>&quot;";
                    break;
                case 6: //Solicitud Eliminada                    
                    $mail_body .= "Se ha realizado un cambio de estado de la solicitud de respaldo de documentos.";
                    $mail_body .= "<br><br>Informaci&oacute;n de la solicitud:<br><br>";
                    $mail_body .= "<table border='0' width='100%'><tr><td width='30%'><b>Fecha:</b></td><td width='70%'>".date("Y-m-d H:i")."</td></tr>";
                    $mail_body .= "<tr><td><b>No. de solicitud:</b></td><td>".$datos["resp_soli_codi"]."</td></tr>";
                    $mail_body .= "<tr><td><b>Fecha de solicitud:</b></td><td>".  date('Y-m-d H:i', strtotime($datos["fecha_solicita"])) ."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Eliminado por:</b></td><td>".$datos["abr_titulo_aut"] . " " . $datos["usua_nombre_aut"] .
                                  "<br>" . $datos["usua_cargo_aut"] . "</td></tr>";
                    $mail_body .= "<tr><td><b>Estado:</b></td><td>".$datos["estado_nombre_resp"]."</td></tr>";
                    $mail_body .= "<tr><td><b>Comentario:</b></td><td>".$datos["comentario_hist"]."</td></tr></table>";
                    $mail_body .= "<br><br>Por favor revise su bandeja de Solicitudes en el sistema &quot;<a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>&quot;";
                    break;
                case 7: //Solicitud atendida
                    $mail_body .= "La generaci&oacute;n de respaldos ha sido realizada.";
                    $mail_body .= "<br><br>Informaci&oacute;n de la solicitud:<br><br>";
                    $mail_body .= "<table border='0' width='100%'><tr><td width='30%'><b>Fecha:</b></td><td width='70%'>".date("Y-m-d H:i")."</td></tr>";
                    $mail_body .= "<tr><td><b>No. de solicitud:</b></td><td>".$datos["resp_soli_codi"]."</td></tr>";
                    $mail_body .= "<tr><td><b>Fecha de solicitud:</b></td><td>".  date('Y-m-d H:i', strtotime($datos["fecha_solicita"])) ."</td></tr>";                    
                    $mail_body .= "<tr><td><b>Estado:</b></td><td>".$datos["estado_nombre_sol"]."</td></tr></table>";
                    $mail_body .= "<br><br>Por favor revise su bandeja de Solicitudes en el sistema &quot;<a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>&quot;";
                    $mail_body .= "<br><b>Recuerde que tiene $dias_descarga días para descargar su respaldo.</b>";
                    break;
                case 8: //Notificación para administradores
                    $mail_body .= "El área <b>&quot;".$datos["usua_depe_nomb"]. "&quot;</b> no tiene asignado una persona que apruebe la solicitud de respaldos realizada por un Servidor Público de la Institución <b>&quot;".$datos["usua_inst_nomb"]. "&quot;</b>.";
                    $mail_body .= "<br><br>Informaci&oacute;n de la solicitud:<br><br>";
                    $mail_body .= "<table border='0' width='100%'><tr><td width='30%'><b>Fecha:</b></td><td width='70%'>".date("Y-m-d H:i")."</td></tr>";
                    $mail_body .= "<tr><td><b>No. de solicitud:</b></td><td>".$datos["resp_soli_codi"]."</td></tr>";
                    $mail_body .= "<tr><td><b>Fecha de solicitud:</b></td><td>".  date('Y-m-d H:i', strtotime($datos["fecha_solicita"])) ."</td></tr>";
                    $mail_body .= "<tr><td><b>Estado:</b></td><td>".$datos["estado_nombre_sol"]."</td></tr></table>";
                    $mail_body .= "<br><br>Por favor asigne el permiso de &quot;Aprobar Solicitudes de Respaldo&quot; a la persona correspondiente para el área en mención, en el sistema &quot;<a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>&quot;";
                    break;
                case 9: //Notificación para soporte@informatica.gob.ec
                    $mail_body .= "Solicitud para descargar respaldos de usuario.";
                    $mail_body .= "<br><br>Informaci&oacute;n de la solicitud:<br><br>";
                    $mail_body .= "<table border='0' width='100%'><tr><td width='30%'><b>Fecha:</b></td><td width='70%'>".date("Y-m-d H:i")."</td></tr>";                    
                    $mail_body .= "<tr><td><b>No. de solicitud:</b></td><td>".$datos["resp_soli_codi"]."</td></tr>";
                    $mail_body .= "<tr><td><b>Fecha de solicitud:</b></td><td>".  date('Y-m-d H:i', strtotime($datos["fecha_solicita"])) ."</td></tr>";                    
                    $mail_body .= "<tr><td><b>Estado:</b></td><td>".$datos["estado_nombre_sol"]."</td></tr>";
                    $mail_body .= "<tr><td><b>Solicitante:</b></td><td>".$remite["abr_titulo"] . " " . $remite["nombre"] . "<br>" . $remite["cargo"]. "<br>" . $remite["email"].  "<br>" . $remite["institucion"]. "</td></tr>";
                    $mail_body .= "<tr><td><b>Motivo:</b></td><td>".$datos["comentario_solicita"]."</td></tr>";
                    $mail_body .= "</table>";
                    $mail_body .= "<br><br>Por favor revise su bandeja de Solicitudes en el sistema &quot;<a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>&quot;";
                    $mail_body .= "<br><b>Recuerde que tiene $dias_descarga días para descargar su respaldo.</b>";
                    break;
                default:
                    break;
            }
        }
        
        $mail_body .= "<br><br>Saludos cordiales,<br><br>Soporte Quipux.";
        $mail_body .= "<br><br><b>Nota: </b>Este mensaje fue enviado autom&aacute;ticamente por el sistema, por favor no lo responda.";
        $mail_body .= "<br>Si tiene alguna inquietud respecto a este mensaje, comun&iacute;quese con <a href='mailto:$cuenta_mail_soporte'>$cuenta_mail_soporte</a>";
        $mail_body .= "</body></html>";

        if($accion == 9){ //Acción de envío de correo a respaldo@informatica.gob.ec
            $header  = 'MIME-Version: 1.0' . "\r\n";
            $header .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
            //$header .= "To: ".$dest["titulo"] . " " . $dest["nombre"] . " <" . $var_correo . ">" . "\r\n";
            $header .= "From: Quipux <$cuenta_mail_envio>" . "\r\n";
            $email = $cuenta_mail_respaldo; //recipient
            $subject = "Quipux: $nombre_accion $asunto"; //asunto
            ini_set('sendmail_from', "$cuenta_mail_envio");
            mail($email, $subject, $mail_body, $header);
            //echo "Correo " . $email . "<br>" . $subject . "<br>" . $mail_body . "<br>" . $header;
        }
        else{
            $tmp = explode(",", $dest["email"]);
            foreach ($tmp as $var_correo) {
                $header  = 'MIME-Version: 1.0' . "\r\n";
                $header .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
                $header .= "To: ".$dest["titulo"] . " " . $dest["nombre"] . " <" . $var_correo . ">" . "\r\n";
                $header .= "From: Quipux <$cuenta_mail_envio>" . "\r\n";
                $email = $var_correo; //recipient
                $subject = "Quipux: $nombre_accion $asunto"; //asunto
                ini_set('sendmail_from', "$cuenta_mail_envio");
                mail($email, $subject, $mail_body, $header);
                //echo "Correo " . $email . "<br>" . $subject . "<br>" . $mail_body . "<br>" . $header;
            }
       }
}

function ObtenerDatosSolicitud($codigo, $usuario_solicita, $db){

    //echo "cod "  . $codigo . " usua " . $usuario_solicita;
    
    if($codigo != "" and $codigo != 0)
    {
        $sql = "select ru.*,
        estsol.est_nombre_estado as estado_nombre_sol,
        estresp.est_nombre_estado as estado_nombre_resp,
        us.usua_codi as usua_codi_sol,
        us.usua_cedula as usua_ced_sol,
        us.usua_nomb as usua_nomb_sol,
        us.usua_apellido as usua_ape_sol,
        us.usua_email as usua_email_sol,
        us.usua_cargo as usua_cargo_sol,
        us.cargo_tipo as usua_cargo_tipo,
        us.depe_codi as depe_codi,
        case when us.cargo_tipo = 1 then 'Jefe' else 'Normal' end as usua_perfil,
        us.depe_nomb as depe_nomb_sol,
        usa.usua_codi as usua_codi_aut,
        usa.usua_cedula as usua_ced_aut,
        usa.usua_nomb as usua_nomb_aut,
        usa.usua_apellido as usua_ape_aut,
        usa.usua_email as usua_email_aut,
        usa.usua_cargo as usua_cargo_aut,
        usa.depe_nomb as depe_nomb_aut,
        us.inst_nombre,radi.radi_nume_text,
        resp_hist.comentario as observacion_hist
        from respaldo_solicitud ru
        left outer join usuario us
        on ru.usua_codi_solicita=us.usua_codi      
        left outer join usuario usa
        on ru.usua_codi_autoriza = usa.usua_codi
        left outer join (select * from respaldo_estado where est_tipo=1) as estsol
        on ru.estado_solicitud = estsol.est_codi
        left outer join (select * from respaldo_estado where est_tipo=2) as estresp
        on ru.estado_respaldo = estresp.est_codi        
        left outer join radicado radi on ru.radi_nume_radi = radi.radi_nume_radi
        left outer join (select * from respaldo_hist_eventos
	where resp_hist_eventos = (select max(resp_hist_eventos)
	from respaldo_hist_eventos where resp_soli_codi=$codigo 
	and (estado_solicitud = 4 or estado_respaldo = 15))) as resp_hist
	on ru.resp_soli_codi = resp_hist.resp_soli_codi
        where ru.resp_soli_codi=$codigo";
    }
    else
    {
        //Se consulta datos del usuario en sesión
        $sql = "select ru.*,
        estsol.est_nombre_estado as estado_nombre_sol,
        estresp.est_nombre_estado as estado_nombre_resp,
        us.usua_codi as usua_codi_sol,
        us.usua_cedula as usua_ced_sol,
        us.usua_nomb as usua_nomb_sol,
        us.usua_apellido as usua_ape_sol,
        us.usua_email as usua_email_sol,
        us.usua_cargo as usua_cargo_sol,
        us.cargo_tipo as usua_cargo_tipo,
        us.depe_codi as depe_codi,
        case when us.cargo_tipo = 1 then 'Jefe' else 'Normal' end as usua_perfil,
        us.depe_nomb as depe_nomb_sol,
        us.inst_nombre,radi.radi_nume_text
        from usuario us
        left outer join (select * from respaldo_solicitud
        where (estado_solicitud = 1 or estado_solicitud = 2 or estado_solicitud = 3 or estado_solicitud = 5)
        and usua_codi_accion =$usuario_solicita) as ru
        on us.usua_codi = ru.usua_codi_solicita
        left outer join (select * from respaldo_estado where est_tipo=1) as estsol
        on ru.estado_solicitud = estsol.est_codi
        left outer join (select * from respaldo_estado where est_tipo=2) as estresp
        on ru.estado_respaldo = estresp.est_codi       
        left outer join radicado radi on ru.radi_nume_radi = radi.radi_nume_radi
        where us.usua_codi=$usuario_solicita";
    }
    
    $rs = $db->conn->Execute($sql);

    //Datos de usuario
    $solicitud["usua_cedula"] = $rs->fields["USUA_CED_SOL"];
    $solicitud["usua_nomb_sol"] = $rs->fields["USUA_NOMB_SOL"];
    $solicitud["usua_ape_sol"] = $rs->fields["USUA_APE_SOL"];
    $solicitud["usua_depe_codi"] = $rs->fields["DEPE_CODI"];
    $solicitud["usua_titulo_sol"] = $rs->fields["USUA_TITULO"];
    $solicitud["usua_email_sol"] = $rs->fields["USUA_EMAIL_SOL"];
    $solicitud["usua_cargo_sol"] = $rs->fields["USUA_CARGO_SOL"];
    $solicitud["usua_depe_nombre"] = $rs->fields["DEPE_NOMB_SOL"];
    $solicitud["usua_inst_nombre"] = $rs->fields["INST_NOMBRE"];
    $solicitud["usua_cargo_tipo"] = $rs->fields["USUA_CARGO_TIPO"];
    $solicitud["usua_perfil"] = $rs->fields["USUA_PERFIL"];    

    if($rs->fields["USUA_CODI_SOLICITA"] != "")
        $solicitud["usua_codi_solicita"] =$rs->fields["USUA_CODI_SOLICITA"];
    else
        $solicitud["usua_codi_solicita"] = $usuario_solicita;

    //Datos de solicitud
    $solicitud["resp_soli_codi"] = $rs->fields["RESP_SOLI_CODI"];
    $solicitud["estado_solicitud"] = $rs->fields["ESTADO_SOLICITUD"];
    $solicitud["estado_respaldo"] = $rs->fields["ESTADO_RESPALDO"];
    $solicitud["comentario"] = $rs->fields["COMENTARIO"];
    $solicitud["fecha_solicita"] = $rs->fields["FECHA_SOLICITA"];
    $solicitud["estado_nombre_sol"] = $rs->fields["ESTADO_NOMBRE_SOL"];
    $solicitud["estado_nombre_resp"] = $rs->fields["ESTADO_NOMBRE_RESP"];
    $solicitud["radi_nume_radi"] = $rs->fields["RADI_NUME_RADI"];
    $solicitud["radi_nume_text"] = $rs->fields["RADI_NUME_TEXT"];
    $solicitud["observacion_hist"] = $rs->fields["OBSERVACION_HIST"];
    
    if(!$solicitud["fecha_solicita"]) $solicitud["fecha_solicita"] = date("Y-m-d");
    else $solicitud["fecha_solicita"] = date('Y-m-d', strtotime($solicitud["fecha_solicita"]));
    $solicitud["fecha_inicio_doc"] = $rs->fields["FECHA_INICIO_DOC"];
    if(!$solicitud["fecha_inicio_doc"]) $solicitud["fecha_inicio_doc"] = date("Y-m-d");
    else $solicitud["fecha_inicio_doc"] = date('Y-m-d', strtotime($solicitud["fecha_inicio_doc"]));
    $solicitud["fecha_fin_doc"] = $rs->fields["FECHA_FIN_DOC"];
    if(!$solicitud["fecha_fin_doc"]) $solicitud["fecha_fin_doc"] = date("Y-m-d");
    else $solicitud["fecha_fin_doc"] = date('Y-m-d', strtotime($solicitud["fecha_fin_doc"]));

    $solicitud["fecha_inicio_ejec"] = $rs->fields["FECHA_INICIO_EJEC"];
    if(!$solicitud["fecha_inicio_ejec"]) $solicitud["fecha_inicio_ejec"] = "- - - ";
    else $solicitud["fecha_inicio_ejec"] = date('Y-m-d h:m:s', strtotime($solicitud["fecha_inicio_ejec"]));

    $solicitud["fecha_fin_ejec"] = $rs->fields["FECHA_FIN_EJEC"];
    if(!$solicitud["fecha_fin_ejec"]) $solicitud["fecha_fin_ejec"] = "- - - ";
    else $solicitud["fecha_fin_ejec"] = date('Y-m-d h:m:s', strtotime($solicitud["fecha_fin_ejec"]));
    
    $solicitud["fecha_ejecutar"] = $rs->fields["FECHA_EJECUTAR"];
    if(!$solicitud["fecha_ejecutar"]) $solicitud["fecha_ejecutar"] = date("Y-m-d");
    else $solicitud["fecha_ejecutar"] = date('Y-m-d', strtotime($solicitud["fecha_ejecutar"]));

    //var_dump($vector);
    return $solicitud;

}

function cargar_lista_documentos($sql, $resp_soli_codi, $tipo, $db) {
   // echo $sql;
    $rs = $db->query($sql);
    if (!$rs) die("OK");
    while (!$rs->EOF) {
        $sql = "insert into respaldo_solicitud_radicado (resp_soli_codi, radi_nume_radi, tipo)
                values ($resp_soli_codi, ".$rs->fields["RADI_NUME_RADI"].", $tipo)";
        $db->query($sql);
        $rs->MoveNext();
    }
}

function SolicitarRespaldo($txt_usua_codi, $resp_soli_codi, $fecha_inicio_doc, $fecha_fin_doc, $db){
    
    //Guarda solo nuevas solicitudes, si la solicitud ya ha sido eliminada o ha finalizado
    $sql = "select * from respaldo_usuario where usua_codi= $txt_usua_codi and fecha_fin is null and fecha_eliminado is null
    and resp_codi not in (select resp_codi from respaldo_solicitud where usua_codi_solicita = $txt_usua_codi)";   
    $rs = $db->query($sql);
    
    if ($rs->EOF) {      
        $record = array();
        unset($record);
        $resp_codi = $db->nextId("sec_respaldo_usuario");
        $record["RESP_CODI"] = $resp_codi;
        $record["USUA_CODI"] = $txt_usua_codi;
        $record["FECHA_SOLICITA"] = $db->conn->sysTimeStamp;
        $insertSQL=$db->conn->Replace("RESPALDO_USUARIO", $record, "RESP_CODI", false,false,true,false);

        //Se actualiza solicitud
        $record_sol = array();
        unset($record_sol);
        $record_sol["RESP_SOLI_CODI"] = $resp_soli_codi;
        $record_sol["RESP_CODI"] = $resp_codi;
        $insertSQL=$db->conn->Replace("RESPALDO_SOLICITUD", $record_sol, "RESP_SOLI_CODI", false,false,true,false);

    }
    return $insertSQL;
    
}

function ConsultarAdministradores($db){

    $sql = "select us.usua_codi from permiso p
    left outer join (select * from permiso_usuario) as pu
    on p.id_permiso = pu.id_permiso
    left outer join (select * from usuarios) as us
    on us.usua_codi = pu.usua_codi
    where p.id_permiso = 12
    and us.usua_esta = 1
    and us.inst_codi = ". $_SESSION["inst_codi"] . "
    and us.usua_email <> 'des@informatica.gob.ec'";
    $rs = $db->conn->Execute($sql);
    
    $vector = array();
    unset($vector);
    $i=0;
    while (!$rs->EOF) {
        $vector[$i]["usua_codi"] = trim($rs->fields["USUA_CODI"]);
        $rs->MoveNext();
        $i++;
    }
    //Código del superAdmin
    $vector[$i+1]["usua_codi"] = 0;

    //var_dump($vector);
    return $vector;
    
}

function ConsultarUsuarioAccion($soli_codi, $db){
    
    $sql = "select rh.usua_codi from respaldo_solicitud rs 
    left outer join respaldo_hist_eventos as rh 
    on rh.resp_soli_codi = rs.resp_soli_codi 
    where rs.resp_soli_codi = " . $soli_codi .
    " and rs.estado_solicitud = 1
    and rh.accion = 71";    
    $rs = $db->conn->Execute($sql);
    
    //Datos de usuario
    $usua_codi_hist = $rs->fields["USUA_CODI"];
    
    return $usua_codi_hist;
}
