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

  $ruta_raiz = "..";
  session_start();
  include_once "$ruta_raiz/rec_session.php";

  if (isset ($replicacion) && $replicacion && $config_db_replica_adm_busqueda_paginador_usuarios!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_adm_busqueda_paginador_usuarios);

  include_once "respaldo_funciones.php";

  $txt_nombre = trim(limpiar_sql($_GET["txt_nombre"]));
  $txt_fecha_inicio_sol = trim(limpiar_sql($_GET["txt_fecha_inicio_sol"]));
  $txt_fecha_fin_sol = trim(limpiar_sql($_GET["txt_fecha_fin_sol"]));
  $cmb_estado = trim(limpiar_sql($_GET["cmb_estado"]));
  $txt_sol = trim(limpiar_numero($_GET["txt_sol"]));
  $txt_tipo_lista = trim(limpiar_numero($_GET["txt_tipo_lista"]));
  $codi_inst = trim(limpiar_numero($_GET["cmb_institucion"]));
  $codigo_usuario = trim(limpiar_numero($_GET["codigo_usuario"]));
  $codigo_dep = trim(limpiar_numero($_GET["codigo_dep"]));
  
  //$usr_depe_actual = $_SESSION["depe_codi"];
  $usr_depe_actual = $codigo_dep;
  $usr_codi_actual = $codigo_usuario;
  $usr_inst_actual = $_SESSION["inst_codi"];
 
    if($orden_cambio==1) {
        if(strtolower($orderTipo)=="desc")
            $orderTipo="asc";
        else
            $orderTipo="desc";
    }
    if (!$orderTipo) $orderTipo="desc";
    
    $check_seleccionar = "";   
    if($txt_tipo_lista == "2" or $txt_tipo_lista == "11")
        $check_seleccionar = "ru.resp_soli_codi AS \"CHK_Seleccionar\",";

    if($txt_tipo_lista == "11"){ //Monitoreo y candelarización de solicitudes
        $cant_documentos = ", case when (ru.num_documentos = -1) then ''::text else ru.num_documentos::text end AS \"Cant.\"";
        $fecha_ejecutar = ", substr(ru.fecha_ejecutar::text,1,10) || '$descZonaHoraria' AS \"Ejecución\"";
        $accion_eliminar = ", 'Eliminar' as \"SCR_Eliminar\", 'eliminar_respaldo(\"'|| ru.resp_codi ||'\", \"'|| ru.resp_soli_codi ||'\");' as \"HID_FUNCION_ELIMINAR\"";
        
        //Se arma el select y where para consultar si la solicitud ya fue calendarizada
        $acc_ejecutada_calendariza = " case when rh.acc_ejecutada > 0 then '<img src=\"$ruta_raiz/iconos/calendario.png\" border=0>' else '' end AS \"_*_\", ";
        
        $where_accion_calendariza = "left outer join (select resp_soli_codi, count(resp_soli_codi) as acc_ejecutada 
		  from respaldo_hist_eventos where accion = 80
		  group by resp_soli_codi) as rh
                  on ru.resp_soli_codi = rh.resp_soli_codi";
    }

    if($txt_tipo_lista == "1" or $txt_tipo_lista == "7"){
        $cant_documentos = ", case when (ru.num_documentos = -1) then ''::text else ru.num_documentos::text end AS \"Cant.\"";
        $descargar = ", case when fecha_eliminado is null and fecha_fin is not null then 'Descargar' else '' end as \"SCR_Descargar\"
                      , 'descargar_respaldo(\"'|| ru.resp_codi ||'\", \"'|| ru.resp_soli_codi ||'\");' as \"HID_FUNCION_DESCARGAR\"";
    }

    if($txt_tipo_lista == "9" or $txt_tipo_lista == "10" or $txt_tipo_lista == "11")
        $institucion = ",us.inst_nombre as \"Institución\"";
    
    if($txt_tipo_lista == "7" or $txt_tipo_lista == "10" or $txt_tipo_lista == "11"){ //Listado de Solicitudes AIQ, SA y Calendarización
        $estado_usuario = ", case when us.usua_esta = 1 then 'Activo' else 'Inactivo' end AS  \"Estado\"";
        $radi_nume_text = ", radi.radi_nume_text AS  \"SCR_Documento\"
                           , 'ver_documento_asociado(\"'|| radi.radi_nume_radi ||'\", \"'|| radi.radi_nume_text ||'\");' as \"HID_FUNCION_VER_DOC\"";
    }

  ?>
  <body>
    <br>
<?
 $isql = "select $check_seleccionar $acc_ejecutada_calendariza ru.resp_soli_codi AS \"SCR_Solic.\",
        'seleccionar_solicitud(\"'|| ru.resp_soli_codi ||'\", \"'|| $txt_tipo_lista ||'\");' as \"HID_FUNCION_SELECCIONAR\",        
        substr(ru.fecha_solicita::text,1,19) || '$descZonaHoraria'  AS \"Fecha Solic.\",
        (us.usua_nomb || ' '  || us.usua_apellido) AS \"Solicitante\",
        case when us.cargo_tipo = 1 then 'Jefe' else 'Normal' end  as \"Perfil\"
        $estado_usuario $radi_nume_text,
        substr(ru.fecha_inicio_doc::text,1,10) || '$descZonaHoraria' AS \"Fecha Desde\",
        substr(ru.fecha_fin_doc::text,1,10) || '$descZonaHoraria' AS \"Fecha Hasta\",
        estsol.est_nombre_estado as \"Estado Solic.\"
        $cant_documentos $fecha_ejecutar $accion_eliminar $descargar $institucion
        from respaldo_solicitud as ru
        left outer join usuario as us
        on us.usua_codi = ru.usua_codi_solicita
        left outer join usuario as usa
        on usa.usua_codi = ru.usua_codi_autoriza
        left outer join (select * from respaldo_estado where est_tipo=1) as estsol
        on ru.estado_solicitud = estsol.est_codi
        left outer join (select * from respaldo_estado where est_tipo=2) as estresp
        on ru.estado_solicitud = estresp.est_codi       
        left outer join respaldo_usuario u
        on ru.resp_codi=u.resp_codi
        left outer join radicado radi 
        on ru.radi_nume_radi = radi.radi_nume_radi
        $where_accion_calendariza
        where ru.fecha_solicita between '$txt_fecha_inicio_sol 00:00:00' and '$txt_fecha_fin_sol 23:59:59'";

switch ($txt_tipo_lista) {
    case 1: //Solicitudes del usuario logueado
        if ($usr_depe_actual!='')
        $isql .= " and us.depe_codi=$usr_depe_actual and us.usua_codi=$usr_codi_actual";
        break;   
    default:
        break;
        
}
//echo $isql;
   
    if ($txt_sol != "") $isql .= " and ru.resp_soli_codi=$txt_sol ";
    if ($cmb_estado != "0") $isql .= " and ru.estado_solicitud=$cmb_estado ";  
    
    $isql .= " order by ".($orderNo+1)." $orderTipo ";
    //echo $isql;
    echo "<br>";
//   echo $txt_tipo_lista . " - " . $isql;
//    $db->query('set enable_nestloop = off');
	$pager = new ADODB_Pager($db,$isql,'adodb', true,$orderNo,$orderTipo,true);
	$pager->checkAll = false;
	$pager->checkTitulo = true;
	$pager->toRefLinks = $linkPagina;
	$pager->toRefVars = $encabezado;
	$pager->descCarpetasGen=$descCarpetasGen;
	$pager->descCarpetasPer=$descCarpetasPer;
	$pager->Render($rows_per_page=10,$linkPagina,$checkbox=chkAnulados);
//    $db->query('set enable_nestloop = on');
?>