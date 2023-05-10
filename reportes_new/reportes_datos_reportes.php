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

// Defino la lista de reportes disponibles
$lista_reportes = array();
$lista_reportes["01"] = array ("nombre" => "Consulta de registros por estado del documento",
                            "descripcion"=>"Muestra el n&uacute;mero de documentos clasificados por su estado.",
                            "mostrar" => "SI");
$lista_reportes["01_1"] = array ("nombre" => "Lista de documentos por estado.",
                            "descripcion"=>"Muestra el n&uacute;mero de documentos clasificados por su estado.",
                            "mostrar" => "NO");
$lista_reportes["02"] = array ("nombre" => "Tiempo de demora al tramitar documentos.",
                            "descripcion"=>"Muestra las principales acciones que se realizaron con los documentos y el tiempo que cada usuario tard&oacute; en realizar cada acci&oacute;n mientras se lo tramitaba.",
                            "mostrar" => "SI");
$lista_reportes["03"] = array ("nombre" => "Tiempo de demora al registrar documentos externos.",
                            "descripcion"=>"Muestra las principales acciones que se realizaron con los documentos y el tiempo que cada usuario se demor&oacute; en el proceso de registro hasta que el documento fue enviado al destinatario.",
                            "mostrar" => "SI");
$lista_reportes["04"] = array ("nombre" => "Tiempo de demora al crear nuevos documentos.",
                            "descripcion"=>"Muestra las principales acciones que se realizaron con los documentos y el tiempo que cada usuario se demor&oacute; hasta que el documento fue firmado y enviado.",
                            "mostrar" => "SI");
$lista_reportes["05"] = array ("nombre" => "Tiempo de demora al responder documentos registrados manualmente.",
                            "descripcion"=>"Muestra el tiempo que se demoró un documento en ser respondido desde que se lo registró hasta que se envió la respuesta.",
                            "mostrar" => "SI");
$lista_reportes["06"] = array ("nombre" => "Documentos reasignados.",
                            "descripcion"=>"Muestra el las reasignaciones de documentos realizadas por los usuarios.",
                            "mostrar" => "SI");
$lista_reportes["06_1"] = array ("nombre" => "Lista de documentos reasignados.",
                            "descripcion"=>"Muestra el detalle de los documentos reasignados.",
                            "mostrar" => "NO");
$lista_reportes["07"] = array ("nombre" => "Tareas.",
                            "descripcion"=>"Muestra las tareas asignadas y recibidas por los usuarios.",
                            "mostrar" => "SI");
$lista_reportes["07_1"] = array ("nombre" => "Lista de tareas asignadas.",
                            "descripcion"=>"Muestra el detalle de los documentos y tareas asignadas.",
                            "mostrar" => "NO");
$lista_reportes["08"] = array ("nombre" => "Tipos de documentos.",
                            "descripcion"=>"Muestra el numero de documentos registrados clasificados seg&uacute;n el tipo de documento.",
                            "mostrar" => "SI");
$lista_reportes["08_1"] = array ("nombre" => "Lista de tareas asignadas.",
                            "descripcion"=>"Muestra el detalle de los documentos y tareas asignadas.",
                            "mostrar" => "NO");
$lista_reportes["09"] = array ("nombre" => "Documentos sin respuesta enviados electr&oacute;nicamente a otras instituciones.",
                            "descripcion"=>"Muestra los documentos enviados electr&oacute;nicamente a otras instituciones y el n&uacute;mero del documento respuesta.",
                            "mostrar" => "SI");
$lista_reportes["10"] = array ("nombre" => "Documentos sin respuesta recibidos electr&oacute;nicamente desde otras instituciones.",
                            "descripcion"=>"Muestra los documentos recibidos electr&oacute;nicamente desde otras instituciones y el n&uacute;mero del documento respuesta.",
                            "mostrar" => "SI");

// Creo el combo de reportes y la funcion que controla los mensajes de descripción de los mensajes
$combo_reportes = "<select name='slc_tipo_reporte' id='slc_tipo_reporte' class='select' onChange='reportes_cambiar_reporte();'>";
foreach ($lista_reportes as $id => $reporte) {
    if ($reporte["mostrar"] == "SI") {
        $tmp = "";
        if ($txt_tipo_reporte == $id) {
            $tmp = "selected";
        }
        $combo_reportes .= "<option value='$id' $tmp>" . $reporte["nombre"] . "</option>";
    }
}
$combo_reportes .= "</select>";


// Defino lista de criterios disponibles
$criterios_nombres["txt_fecha_desde"] = "Fecha desde";
$criterios_nombres["txt_fecha_hasta"] = "Fecha hasta";
$criterios_nombres["txt_depe_codi"] = "&Aacute;rea";
$criterios_nombres["txt_usua_codi"] = "Usuario";
$criterios_nombres["txt_inst_origen"] = "Instituci&oacute;n origen";
$criterios_nombres["txt_inst_destino"] = "Instituci&oacute;n destino";

$agrupar_reporte = false;
switch ($txt_tipo_reporte) {
    case "01":
        $columnas["fecha1"]  = "Fecha (aaaa)";
        $columnas["fecha2"]  = "Fecha (aaaa-mm)";
        $columnas["fecha3"]  = "Fecha (aaaa-mm-dd)";
        $columnas["area"]    = "&Aacute;rea";
        $columnas["usuario"] = "Usuario";
        $columnas["estado0"] = "Archivados";
        $columnas["estado1"] = "En Elaboraci&oacute;n";
        $columnas["estado2"] = "En Tr&aacute;mite";
        $columnas["estado3"] = "No Enviados";
        $columnas["estado5"] = "Por imprimir";
        $columnas["estado6"] = "Env. Manual";
        $columnas["firmados"] = "Env. F. Digital";
        $columnas["estado6r"] = "Registrados";
        
        $columnas["estadot"] = "Todos los estados";

        $columnas_desc["fecha1"]  = "A&ntilde;o en que se generaron los documentos que tiene el usuario en alguna de sus bandejas";
        $columnas_desc["fecha2"]  = "A&ntilde;o y mes en que se generaron los documentos que tiene el usuario en alguna de sus bandejas";
        $columnas_desc["fecha3"]  = "A&ntilde;o, mes y d&iacute;a en que se generaron los documentos que tiene el usuario en alguna de sus bandejas";
        $columnas_desc["area"]    = "Nombre del &aacute;rea a la que pertenece el usuario que tiene el documento actualmente en alguna de sus bandejas";
        $columnas_desc["usuario"] = "Nombre del funcionario p&uacute;blico o usuario que tiene el documento actualmente en alguna de sus bandejas";
        $columnas_desc["estado0"] = "N&uacute;mero de documentos que el usuario tiene en su bandeja de archivados";
        $columnas_desc["estado1"] = "N&uacute;mero de documentos que el usuario tiene en su bandeja en elaboraci&oacute;n";
        $columnas_desc["estado2"] = "N&uacute;mero de documentos que el usuario tiene en su bandeja Recibidos";
        $columnas_desc["estado3"] = "N&uacute;mero de documentos que el usuario tiene en su bandeja No Enviados";
        $columnas_desc["estado5"] = "N&uacute;mero de documentos que el usuario tiene en su bandeja por imprimir";
        $columnas_desc["estado6"] = "N&uacute;mero de documentos que el usuario tiene en su bandeja de enviados y que fueron firmados manualmente";
        $columnas_desc["firmados"] = "N&uacute;mero de documentos que el usuario tiene en su bandeja de enviados y que fueron firmados electr&oacute;nicamente";
        $columnas_desc["estado6r"] = "N&uacute;mero de documentos que el usuario tiene en su bandeja de enviados y que son documentos externos registrados";
        $columnas_desc["estadot"] = "N&uacute;mero total de documentos que el usuario tiene en sus bandejas en cualquier estado";

        $columnas_grupo1 = 5; //Columnas que pertenecen al primer bloque
        $columnas_default = "fecha2,area,usuario,estado0,estado1,estado2,estado3,estado5,estado6,firmados,estado6r,estadot";
        $criterios_default = "txt_fecha_desde,txt_fecha_hasta,txt_depe_codi,txt_usua_codi";
        $num_max_registros = 300; // Es el tope de registros que se mostrarán en el reporte
        break;
    case "01_1":
        $columnas["remitente"]     = "De";
        $columnas["destinatario"]  = "Para";
        $columnas["asunto"]        = "Asunto";
        $columnas["fecha"]         = "Fecha";
        $columnas["num_doc"]       = "No. Documento";
        $columnas["referencia"]    = "No. Referencia";
        $columnas["tipo"]          = "Tipo Doc.";
        $columnas["area"]          = "&Aacute;rea Actual";
        $columnas["usuario"]       = "Usuario Actual";

        $columnas_desc["remitente"]     = "Remitente del documento";
        $columnas_desc["destinatario"]  = "Destinatario del documento";
        $columnas_desc["asunto"]        = "Asunto";
        $columnas_desc["fecha"]         = "Fecha del documento";
        $columnas_desc["num_doc"]       = "N&uacute;mero de documento";
        $columnas_desc["referencia"]    = "N&uacute;mero de referencia";
        $columnas_desc["tipo"]          = "Tipo de documento";
        $columnas_desc["area"]          = "&Aacute;rea donde se encuentra actualmente el documento";
        $columnas_desc["usuario"]       = "Usuario actual del documento";

        $columnas_grupo1 = 0; //Columnas que pertenecen al primer bloque
        $columnas_default = ",remitente,destinatario,asunto,fecha,num_doc,usuario,area,referencia,tipo";
        $criterios_default = "";
        $num_max_registros = 200; // Es el tope de registros que se mostrarán en el reporte
        break;

    case "02":
    case "03":
    case "04":
        $columnas["num_doc"]    = "No. Documento";
        $columnas["fecha_doc"]  = "Fecha de Registro";
        $columnas["asunto"]     = "Asunto";
        $columnas["usua_actu"]  = "Usuario Actual";
        $columnas["estado"]     = "Estado Actual";
        if ($txt_tipo_reporte == "02"){
            $columnas["num_ref"]    = "No. Referencia";
            $columnas["tipo_doc"] = "Tipo Documento";
            $columnas["firma"] = "Firma Digital";
        }
        if ($txt_tipo_reporte == "04") $columnas["num_respondido"]  = "Respondido a";
        
        $columnas["hist_ori_usua"]      = "Usuario Origen";
        $columnas["hist_ori_desc"]      = "Acci&oacute;n Origen";
        $columnas["hist_ori_fecha"]     = "Fecha Origen";
        $columnas["hist_dest_usua"]     = "Usuario Destino";
        $columnas["hist_dest_desc"]     = "Acci&oacute;n Realizada";
        $columnas["hist_dest_fecha"]    = "Fecha Destino";
        $columnas["hist_tiempo"]        = "Tiempo Demora";

        $columnas_desc["num_doc"]    = "N&uacute;mero del documento";
        $columnas_desc["fecha_doc"]  = "Fecha de Registro del documento externo";
        $columnas_desc["asunto"]     = "Texto que se encuentra como Asunto del documento";
        $columnas_desc["usua_actu"]  = "Nombre del Usuario que actualmente tiene el documento en sus bandejas de Recibidos o Archivados";
        $columnas_desc["estado"]     = "Estado en que se encuentra actualmente el documento";
        $columnas_desc["num_respondido"]   = "N&uacute;mero del documento al que se dio respuesta con el documento actual";
        $columnas_desc["hist_ori_usua"]    = "Nombre del Usuario que registr&oacute; o reasign&oacute; el documento al usuario destino";
        $columnas_desc["hist_ori_desc"]    = "Acci&oacute;n realizada por el usuario origen, para entregar el documento a la bandeja del usuario destino";
        $columnas_desc["hist_ori_fecha"]   = "Fecha en la se realiz&oacute; la acci&oacute;n para entregar el documento a la bandeja del usuario destino";
        $columnas_desc["hist_dest_usua"]   = "Nombre del usuario que recibe el documento en la bandeja de recibidos o en elaboraci&oacute;n, usuario que recibe del usuario origen";
        $columnas_desc["hist_dest_desc"]   = "Acci&oacute;n que realiza el usuario destino despu&eacute;s de recibir el documento en su bandjea de recibidos o en elaboraci&oacute;n";
        $columnas_desc["hist_dest_fecha"]  = "Fecha en la que se entreg&oacute; en la bandeja del usuario destino despu&eacute;s de la acci&oacute;n realizada por el usuario origen";
        $columnas_desc["hist_tiempo"]      = "Tiempo que se demor&oacute; el usuario destino en realizar una acci&oacute;n desde que le lleg&oacute; el documento a su bandeja de recibidos o en elaboraci&oacute;n";
        $columnas_desc["tipo_doc"]   = "Tipo de Documento como: Oficio, Memorando, Circular, etc.";
        $columnas_desc["num_ref"]    = "N&uacute;mero de referencia";
        $columnas_desc["firma"]    = "Documento firmado electr&oacute;nicamente";

        $columnas_grupo1 = 8; //Columnas que pertenecen al primer bloque
        $columnas_default = "num_doc,fecha_doc,usua_actu,asunto,estado,hist_ori_fecha,hist_ori_usua,hist_ori_desc,hist_dest_fecha,hist_dest_usua,hist_dest_desc,hist_tiempo";
        if ($txt_tipo_reporte == "04") $columnas_default .= ",num_respondido";
        $criterios_default = "txt_fecha_desde,txt_fecha_hasta,txt_depe_codi,txt_usua_codi";
        $agrupar_reporte = true;
        $num_max_registros = 0; // Es el tope de registros que se mostrarán en el reporte
        break;

    case "05":
        $columnas["fecha_reg"]  = "Fecha Registro";
        $columnas["num_doc"]    = "No. Documento";
        $columnas["estado"]     = "Estado Actual";
        $columnas["usua_actu"]  = "Usuario Actual";
        $columnas["asunto"]     = "Asunto";
        $columnas["num_ref"]    = "No. Referencia";
        $columnas["fecha_ref"]  = "Fecha Referencia";
        $columnas["accion"]     = "Acci&oacute;n Realizada";
        $columnas["num_resp"]   = "No. Respuesta";
        $columnas["fecha_resp"] = "Fecha Respuesta";
        $columnas["observa"]    = "Observaci&oacute;n";
        $columnas["tiempo"]     = "Tiempo Demora";        

        $columnas_desc["fecha_reg"]  = "Fecha de registro";
        $columnas_desc["num_doc"]    = "N&uacute;mero de documento";
        $columnas_desc["usua_actu"]  = "Nombre del Usuario que actualmente tiene el documento en sus bandejas";
        $columnas_desc["estado"]     = "Estado en que se encuentra actualmente el documento";
        $columnas_desc["asunto"]     = "Asunto";
        $columnas_desc["num_ref"]    = "N&uacute;mero de referencia";
        $columnas_desc["fecha_ref"]  = "Fecha de referencia";
        $columnas_desc["accion"]     = "Acci&oacute;n realizada con el documento";
        $columnas_desc["num_resp"]   = "N&uacute;mero de la respuesta enviada";
        $columnas_desc["fecha_resp"] = "Fecha en que se respondi&oacute; el documento";
        $columnas_desc["observa"]    = "Observaciones en el caso que se haya eliminado o archivado el documento";
        $columnas_desc["tiempo"]     = "Tiempo que tard&oacute; el tr&aacute;mite en ser respondido";
       
        $columnas_grupo1 = 0; //Columnas que pertenecen al primer bloque
        $columnas_default = "fecha_reg,num_doc,estado,usua_actu,accion,num_resp,fecha_resp,observa,tiempo";
        $criterios_default = "txt_fecha_desde,txt_fecha_hasta,txt_depe_codi";//,txt_usua_codi";
        $agrupar_reporte = false;
        $num_max_registros = 0; // Es el tope de registros que se mostrarán en el reporte
        break;

    case "06": // Documentos Reasignados
        $columnas["fecha1"]  = "Fecha (aaaa)";
        $columnas["fecha2"]  = "Fecha (aaaa-mm)";
        $columnas["fecha3"]  = "Fecha (aaaa-mm-dd)";
        $columnas["area"]    = "&Aacute;rea";
        $columnas["usuario"] = "Usuario";
        $columnas["usua_ori"] = "Reasignados por el usuario";
        $columnas["usua_dest"] = "Reasignados al usuario";

        $columnas_desc["fecha1"]  = "A&ntilde;o en que se reasignaron los documentos";
        $columnas_desc["fecha2"]  = "A&ntilde;o y mes en que se reasignaron los documentos";
        $columnas_desc["fecha3"]  = "A&ntilde;o, mes y d&iacute;a en que se reasignaron los documentos";
        $columnas_desc["area"]    = "&Aacute;rea a la que pertenece el usuario";
        $columnas_desc["usuario"] = "Nombre del servidor p&uacute;blico o usuario que tiene el documento actualmente en alguna de sus bandejas";
        $columnas_desc["usua_ori"]  = "N&uacute;mero de documentos que el usuario reasign&oacute; a otros usuarios";
        $columnas_desc["usua_dest"] = "N&uacute;mero de documentos que le fueron reasignados al usuario";

        $columnas_grupo1 = 5; //Columnas que pertenecen al primer bloque
        $columnas_default = "fecha2,area,usuario,usua_ori,usua_dest";
        $criterios_default = "txt_fecha_desde,txt_fecha_hasta,txt_depe_codi,txt_usua_codi";
        $num_max_registros = 300; // Es el tope de registros que se mostrarán en el reporte
        break;
    case "06_1":
        $columnas["num_doc"]       = "No. Documento";
        $columnas["asunto"]        = "Asunto";
        $columnas["remitente"]     = "De";
        $columnas["destinatario"]  = "Para";
        $columnas["fecha_asign"]   = "Fecha Reasignaci&oacute;n";
        $columnas["fecha_max"]     = "Fecha m&aacute;xima de tr&aacute;mite";
        $columnas["usua_ori"]      = "Usuario Origen";
        $columnas["usua_dest"]     = "Usuario Destino";
        $columnas["usua_actu"]     = "Usuario Actual";
        $columnas["comentario"]    = "Comentario";
        $columnas["estado"]        = "Estado";

        $columnas_desc["num_doc"]       = "N&uacute;mero de documento";
        $columnas_desc["remitente"]     = "Remitente del documento";
        $columnas_desc["destinatario"]  = "Destinatario del documento";
        $columnas_desc["asunto"]        = "Asunto";
        $columnas_desc["fecha_asign"]   = "Fecha en la que se reasign&oacute; el documento";
        $columnas_desc["fecha_max"]     = "Fecha m&aacute;xima para tramitar el documento";
        $columnas_desc["usua_ori"]      = "Usuario que reasign&oacute; el documento (origen)";
        $columnas_desc["usua_dest"]     = "Usuario al que le reasignaron el documento (destino)";
        $columnas_desc["usua_actu"]     = "Usuario actual del documento";
        $columnas_desc["comentario"]    = "Observaciones realizadas al momento de reasignar el documento";
        $columnas_desc["estado"]        = "Estado actual del documento";

        $columnas_grupo1 = 0; //Columnas que pertenecen al primer bloque
        $columnas_default = ",num_doc,remitente,destinatario,asunto,fecha_asign,fecha_max,usua_ori,usua_dest,comentario,usua_actu,estado";
        $criterios_default = "";
        $num_max_registros = 200; // Es el tope de registros que se mostrarán en el reporte
        break;

    case "07": // Tareas
        $columnas["fecha1"]  = "Fecha (aaaa)";
        $columnas["fecha2"]  = "Fecha (aaaa-mm)";
        $columnas["fecha3"]  = "Fecha (aaaa-mm-dd)";
        $columnas["area"]    = "&Aacute;rea";
        $columnas["usuario"] = "Usuario";
        $columnas["tar_env_0"] = "Total Tareas Enviadas";
        $columnas["tar_env_1"] = "Tareas Enviadas Pendientes";
        $columnas["tar_env_2"] = "Tareas Enviadas Finalizadas";
        $columnas["tar_env_3"] = "Tareas Enviadas Canceladas";
        $columnas["tar_rec_0"] = "Total Tareas Recibidas";
        $columnas["tar_rec_1"] = "Tareas Recibidas Pendientes";
        $columnas["tar_rec_2"] = "Tareas Recibidas Finalizadas";
        $columnas["tar_rec_3"] = "Tareas Recibidas Canceladas";

        $columnas_desc["fecha1"]  = "A&ntilde;o en que se asign&oacute; la tarea";
        $columnas_desc["fecha2"]  = "A&ntilde;o y mes en que se asign&oacute; la tarea";
        $columnas_desc["fecha3"]  = "A&ntilde;o, mes y d&iacute;a en que se asign&oacute; la tarea";
        $columnas_desc["area"]    = "Nombre del &aacute;rea a la que pertenece el usuario";
        $columnas_desc["usuario"] = "Nombre del servidor p&uacute;blico";
        $columnas_desc["tar_env_0"]  = "N&uacute;mero total de tareas que el usuario asign&oacute; a otros usuarios";
        $columnas_desc["tar_env_1"]  = "N&uacute;mero total de tareas que el usuario asign&oacute; a otros usuarios y que se encuentran en estado pendiente";
        $columnas_desc["tar_env_2"]  = "N&uacute;mero total de tareas que el usuario asign&oacute; a otros usuarios y que se encuentran finalizadas";
        $columnas_desc["tar_env_3"]  = "N&uacute;mero total de tareas que el usuario asign&oacute; a otros usuarios y que fueron canceladas por el usuario";
        $columnas_desc["tar_rec_0"]  = "N&uacute;mero total de tareas que le fueron asignadas al usuario";
        $columnas_desc["tar_rec_1"]  = "N&uacute;mero total de tareas que le fueron asignadas al usuario y que se encuentran en estado pendiente";
        $columnas_desc["tar_rec_2"]  = "N&uacute;mero total de tareas que le fueron asignadas al usuario y que se encuentran finalizadas";
        $columnas_desc["tar_rec_3"]  = "N&uacute;mero total de tareas que le fueron asignadas al usuario y que fueron canceladas por el usuario";

        $columnas_grupo1 = 5; //Columnas que pertenecen al primer bloque
        $columnas_default = "fecha2,area,usuario,tar_env_1,tar_env_2,tar_env_3,tar_env_0,tar_rec_1,tar_rec_2,tar_rec_3,tar_rec_0";
        $criterios_default = "txt_fecha_desde,txt_fecha_hasta,txt_depe_codi,txt_usua_codi";
        $num_max_registros = 300; // Es el tope de registros que se mostrarán en el reporte
        break;
    case "07_1":
        $columnas["num_doc"]       = "No. Documento";
        $columnas["remitente"]     = "De";
        $columnas["destinatario"]  = "Para";
        $columnas["asunto"]        = "Asunto";
        $columnas["fecha_inicio"]  = "Fecha de Asignaci&oacute;n";
        $columnas["fecha_fin"]     = "Fecha de Finalizaci&oacute;n";
        $columnas["fecha_max"]     = "Fecha m&aacute;xima de tr&aacute;mite";
        $columnas["dias_retraso"]  = "No. de d&iacute;as de retraso";
        $columnas["avance"]        = "Avance de la tarea";
        $columnas["usua_ori"]      = "Usuario Origen";
        $columnas["usua_dest"]     = "Usuario Destino";
//        $columnas["usua_actu"]     = "Usuario Actual";
        $columnas["comentario"]    = "Comentario";
        $columnas["estado"]        = "Estado";

        $columnas_desc["num_doc"]       = "N&uacute;mero de documento";
        $columnas_desc["remitente"]     = "Remitente del documento";
        $columnas_desc["destinatario"]  = "Destinatario del documento";
        $columnas_desc["asunto"]        = "Asunto";
        $columnas_desc["fecha_inicio"]  = "Fecha de Asignaci&oacute;n";
        $columnas_desc["fecha_fin"]     = "Fecha de Finalizaci&oacute;n";
        $columnas_desc["fecha_max"]     = "Fecha m&aacute;xima de tr&aacute;mite";
        $columnas_desc["dias_retraso"]  = "No. de d&iacute;as de retraso";
        $columnas_desc["avance"]        = "Avance de la tarea";
        $columnas_desc["usua_ori"]      = "Usuario que reasign&oacute; el documento (origen)";
        $columnas_desc["usua_dest"]     = "Usuario al que le reasignaron el documento (destino)";
//        $columnas_desc["usua_actu"]     = "Usuario actual del documento";
        $columnas_desc["comentario"]    = "Observaciones realizadas al momento de asignar la tarea";
        $columnas_desc["estado"]        = "Estado actual de la tarea";

        $columnas_grupo1 = 0; //Columnas que pertenecen al primer bloque
        $columnas_default = ",num_doc,remitente,destinatario,asunto,fecha_inicio,fecha_fin,fecha_max,dias_retraso,avance,usua_ori,usua_dest,comentario,estado";
        $criterios_default = "";
        $num_max_registros = 200; // Es el tope de registros que se mostrarán en el reporte
        break;

    case "08": // Tipos de documentos
        $columnas["fecha1"]  = "Fecha (aaaa)";
        $columnas["fecha2"]  = "Fecha (aaaa-mm)";
        $columnas["fecha3"]  = "Fecha (aaaa-mm-dd)";
        $columnas["area"]    = "&Aacute;rea";
        $columnas["usuario"] = "Usuario";

        $columnas_desc["fecha1"]  = "A&ntilde;o en que se asign&oacute; la tarea";
        $columnas_desc["fecha2"]  = "A&ntilde;o y mes en que se asign&oacute; la tarea";
        $columnas_desc["fecha3"]  = "A&ntilde;o, mes y d&iacute;a en que se asign&oacute; la tarea";
        $columnas_desc["area"]    = "Nombre del &aacute;rea a la que pertenece el usuario";
        $columnas_desc["usuario"] = "Nombre del servidor p&uacute;blico";

        $columnas_grupo1 = 5; //Columnas que pertenecen al primer bloque
        $columnas_default = "fecha2,area,usuario";
        $criterios_default = "txt_fecha_desde,txt_fecha_hasta,txt_depe_codi,txt_usua_codi";
        $num_max_registros = 300; // Es el tope de registros que se mostrarán en el reporte

        // Completamos las columnas segun los tipos de documentos existentes en la BDD
        $rs_docs = $db->query("select trad_codigo, trad_descr from tiporad where trad_inst_codi in (0,".$_SESSION["inst_codi"].") order by 2");
        while ($rs_docs && !$rs_docs->EOF) {
            $columnas["tipo_".$rs_docs->fields["TRAD_CODIGO"]] = $rs_docs->fields["TRAD_DESCR"];
            $columnas_desc["tipo_".$rs_docs->fields["TRAD_CODIGO"]] = "N&uacute;mero de documentos tipo ".$rs_docs->fields["TRAD_DESCR"].
                                                                       (($rs_docs->fields["TRAD_CODIGO"]==2) ? " registrados." : " creados.");
            $columnas_default .= ",tipo_".$rs_docs->fields["TRAD_CODIGO"];
            $rs_docs->MoveNext();
        }

        break;
    case "08_1":
        $columnas["num_doc"]       = "No. Documento";
        $columnas["remitente"]     = "De";
        $columnas["destinatario"]  = "Para";
        $columnas["asunto"]        = "Asunto";
        $columnas["fecha"]         = "Fecha";
        $columnas["usua_actu"]     = "Usuario Actual";
        $columnas["estado"]        = "Estado";

        $columnas_desc["num_doc"]       = "N&uacute;mero de documento";
        $columnas_desc["remitente"]     = "Remitente del documento";
        $columnas_desc["destinatario"]  = "Destinatario del documento";
        $columnas_desc["asunto"]        = "Asunto";
        $columnas_desc["fecha"]         = "Fecha";
        $columnas_desc["usua_actu"]     = "Usuario actual del documento";
        $columnas_desc["estado"]        = "Estado actual del documento";

        $columnas_grupo1 = 0; //Columnas que pertenecen al primer bloque
        $columnas_default = ",num_doc,remitente,destinatario,asunto,fecha,usua_actu,estado";
        $criterios_default = "";
        $num_max_registros = 200; // Es el tope de registros que se mostrarán en el reporte
        break;

    case "09":
        $columnas["inst_nombre"]  = "Instituci&oacute;n";
        $columnas["num_doc"]      = "No. Documento";
        $columnas["num_doc_dest"] = "No. Documento Instituci&oacute;n Destino";
        $columnas["fecha_envio"]  = "Fecha Env&iacute;o";
        $columnas["asunto"]       = "Asunto";
        $columnas["usua_rem"]     = "Remitente";
        $columnas["usua_dest"]    = "Destinatario";
        $columnas["usua_actu"]    = "Usuario Actual";
        $columnas["estado"]       = "Estado";
        $columnas["num_resp"]     = "No. Respuesta";
        $columnas["fecha_resp"]   = "Fecha Respuesta";

        $columnas_desc["inst_nombre"]  = "Instituci&oacute;n a la que se envi&oacute; el documento";
        $columnas_desc["num_doc"]      = "N&uacute;mero de documento en la instituci&oacute;n ".$_SESSION["inst_nombre"];
        $columnas_desc["num_doc_dest"] = "N&uacute;mero de documento en la instituci&oacute;n destino";
        $columnas_desc["fecha_envio"]  = "Fecha en la que se envi&oacute; el documento";
        $columnas_desc["asunto"]       = "Asunto";
        $columnas_desc["usua_rem"]     = "Remitente del documento";
        $columnas_desc["usua_dest"]    = "Destinatario del documento";
        $columnas_desc["usua_actu"]    = "Usuario que tiene actualmente el documento en sus bandejas de recibidos o archivados";
        $columnas_desc["estado"]       = "Estado actual del documento";
        $columnas_desc["num_resp"]     = "N&uacute;mero de la respuesta enviada";
        $columnas_desc["fecha_resp"]   = "Fecha en que se respondi&oacute; el documento";

        $columnas_grupo1 = 0; //Columnas que pertenecen al primer bloque
        $columnas_default = "inst_nombre,num_doc,num_doc_dest,fecha_envio,asunto,usua_rem,usua_dest,usua_actu,estado,num_resp,fecha_resp";
        $criterios_default = "txt_fecha_desde,txt_fecha_hasta,txt_inst_codi,txt_depe_codi,txt_usua_codi";
        $agrupar_reporte = false;
        $num_max_registros = 300; // Es el tope de registros que se mostrarán en el reporte
        break;

    case "10":
        $columnas["inst_nombre"]  = "Instituci&oacute;n";
        $columnas["num_doc"]      = "No. Documento";
        $columnas["num_doc_dest"] = "No. Documento Instituci&oacute;n Origen";
        $columnas["fecha_envio"]  = "Fecha Recepci&oacute;n";
        $columnas["asunto"]       = "Asunto";
        $columnas["usua_rem"]     = "Remitente";
        $columnas["usua_dest"]    = "Destinatario";
        $columnas["usua_actu"]    = "Usuario Actual";
        $columnas["estado"]       = "Estado";
        $columnas["num_resp"]     = "No. Respuesta";
        $columnas["fecha_resp"]   = "Fecha Respuesta";

        $columnas_desc["inst_nombre"]  = "Instituci&oacute;n desde la que se envi&oacute; el documento";
        $columnas_desc["num_doc"]      = "N&uacute;mero de documento en la instituci&oacute;n ".$_SESSION["inst_nombre"];
        $columnas_desc["num_doc_dest"] = "N&uacute;mero de documento en la instituci&oacute;n que envi&oacute; el documento";
        $columnas_desc["fecha_envio"]  = "Fecha en la que se recibi&oacute; el documento";
        $columnas_desc["asunto"]       = "Asunto";
        $columnas_desc["usua_rem"]     = "Remitente del documento";
        $columnas_desc["usua_dest"]    = "Destinatario del documento";
        $columnas_desc["usua_actu"]    = "Usuario que tiene actualmente el documento en sus bandejas de recibidos o archivados";
        $columnas_desc["estado"]       = "Estado actual del documento";
        $columnas_desc["num_resp"]     = "N&uacute;mero de la respuesta enviada";
        $columnas_desc["fecha_resp"]   = "Fecha en que se respondi&oacute; el documento";

        $columnas_grupo1 = 0; //Columnas que pertenecen al primer bloque
        $columnas_default = "inst_nombre,num_doc,num_doc_dest,fecha_envio,asunto,usua_rem,usua_dest,usua_actu,estado,num_resp,fecha_resp";
        $criterios_default = "txt_fecha_desde,txt_fecha_hasta,txt_inst_codi,txt_depe_codi,txt_usua_codi";
        $agrupar_reporte = false;
        $num_max_registros = 300; // Es el tope de registros que se mostrarán en el reporte
        break;

    default:
        $columnas = array();
        $columnas_grupo1 = 0;
        $columnas_default = "";
        $lista_criterios = "txt_fecha_desde,txt_fecha_hasta";
}

?>
