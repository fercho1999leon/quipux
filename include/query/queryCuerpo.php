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

// Codigo modificado por M. Haro - email: mauricioharo21@gmail.com
// se incluyo un select adicional y *LIMIT**OFFSET* en varios queries para mejorar el rendimiento de la BDD
// La función ver_usuarios terda mucho tiempo en ejecutarse y cuando son muchos registros la ejecución de
// esta consulta se hace muy pesada.
// Para mejorar esto se cambiaron algunas librerias de ADODB para que al momento de realizar el count elimine la función
// y el limit y el offset se los pone en el query interior para que la función se ejecute solo para los registros que se van a mostrar.
// Adicionalmente se elimino la ejecución de la función en el count del paginador
// Archivos ADODB: (revisión svn 456)
// - adodb/adodb-lib.inc.php    - function _adodb_getcount()
// - adodb/drivers/adodb-postgres7.inc.php  - function SelectLimit()

 
switch($db->driver) {
    case 'postgres':
    $datos_usuarios = "(select usua_codi, usua_nomb||' '||usua_apellido as \"usua_nombre\", depe_codi, inst_codi from usuarios
                        union all select ciu_codigo, ciu_nombre||' '||ciu_apellido,0,0 from ciudadano) as ";

    $datos_usuarios_imprimir = "(select usua_codi, coalesce(usua_nomb,'')||' '||coalesce(usua_apellido,'') as \"usua_nombre\", depe_codi, inst_codi from usuarios where depe_codi=".$_SESSION["depe_codi"].") as ";

    $fecha_documento = "(case when radi_nume_temp::text like '%0' then radi_fech_ofic else radi_fech_radi end)";
    $where_fecha_documento = "";
    if ($version_light)
        $where_fecha_documento = " and ($fecha_documento::date between '$txt_fecha_desde' and '$txt_fecha_hasta')";

    $info_usuario_query = " - USR ".$_SESSION["usua_codi"]." - ".date("Y-m-d H:i:s")."<br>";
    $firma='<div align="center"><a href="#" class="Ntooltip"><img src="'.$ruta_raiz.'/imagenes/key-yellow.png" alt="Firmado digitalmente" border="0"><span>Firmado digitalmente</span></div>';            
    $urgente='<div align="center"><a href="#" class="Ntooltip"><img src="'.$ruta_raiz.'/imagenes/urgente.png" width="13" height="15" alt="Urgente" border="0"><span>Urgente</span></div>';            
    
    switch ($carpeta) {

        case 1:  // En elaboración
            if (isset ($replicacion) && $replicacion && $config_db_replica_cuerpo_paginador!=$config_db_replica_bandeja_elaboracion) $db = new ConnectionHandler($ruta_raiz,$config_db_replica_bandeja_elaboracion);
            if ($orderNo=='') $orderNo=7;
            $isql = "select -- En elaboracion $info_usuario_query
                    radi_nume_radi as \"CHK_CHKANULAR\"
                    ,case when upper(cat_descr)='URGENTE' then '$urgente' else '' end as \" \"
                    ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    ,ver_usuarios(radi_usua_rem,',') as \"De\"
                    ,ver_usuarios(radi_usua_dest,',') as \"Para\"
                    ,radi_asunto as \"Asunto\"
                    ,substr(fecha_documento::text,1,19)||' $descZonaHoraria' as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text as \"Número Documento\"
                    ,radi_cuentai as \"No. Referencia\"                                   
                    ,ver_usuarios(radi_usua_ante::text,',') as \"Usuario Anterior\"
                    ,radi_leido as \"HID_RADI_LEIDO\"
                    from (
                        select b.radi_nume_radi,cat.cat_descr,1,2, b.radi_usua_rem, b.radi_usua_dest
                        , b.radi_asunto
                        , b.fecha_documento, 3, b.radi_nume_text, b.radi_cuentai,b.radi_usua_ante
                        , b.radi_nume_temp, b.radi_path, b.radi_tipo , b.radi_leido
                        from (select *, $fecha_documento as fecha_documento from radicado b where radi_usua_actu=".$_SESSION["usua_codi"]
                    . " and esta_codi=1 and radi_inst_actu = " . $_SESSION["inst_codi"] . " $whereFiltro $where_fecha_documento
                        and radi_nume_radi not in (select radi_nume_radi from tarea where estado=1 and usua_codi_ori=".$_SESSION["usua_codi"].")) as b
                        left outer join categoria cat on coalesce(b.cat_codi,0) = cat.cat_codi
                        order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo *LIMIT**OFFSET*
                    ) as a order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo";
            //ECHO $isql ;
            break;
            
        case 2:  // Recibidos
            if (isset ($replicacion) && $replicacion && $config_db_replica_cuerpo_paginador!=$config_db_replica_bandeja_recibidos) $db = new ConnectionHandler($ruta_raiz,$config_db_replica_bandeja_recibidos);
            if ($orderNo=='') $orderNo=8;
            $recibido='<div align="center"><a href="#" class="Ntooltip"><img src="'.$ruta_raiz.'/imagenes/folder_page.png" alt="Recibido" border="0"><span>Recibido</span></div>';
            $vencido='<div align="center"><a href="#" class="Ntooltip"><img src="'.$ruta_raiz.'/imagenes/vencidos.png"  alt="Vencido" border="0"><span>Vencido Reasignado</span></div>';
            $reasignado='<div align="center"><a href="#" class="Ntooltip"><img src="'.$ruta_raiz.'/imagenes/email_go.png"  alt="Reasignado" border="0"><span>Reasignado</span></div>';
            $recibido_de_tarea='<div align="center"><a href="#" class="Ntooltip"><img src="'.$ruta_raiz.'/imagenes/recibido_tareas.png"  alt="Recibido de Tarea" border="0"><span>Recibido de Tarea</span></div>';
            $isql = "select -- Recibidos $info_usuario_query
                    radi_nume_radi as \"CHK_CHKANULAR\"
                    ,case when upper(cat_descr)='URGENTE' then '$urgente' else '' end as \" \"                    
                    ,case when (select count(t.tarea_codi) from tarea t where t.radi_nume_radi = a.radi_nume_radi and t.estado in (2,3) and t.usua_codi_ori=".$_SESSION["usua_codi"].")::integer>0 then '$recibido_de_tarea' else
                       (case when radi_fech_asig is null then '$recibido' else
                       (case when radi_fech_asig::date < now()::date then '$vencido'
                       else '$reasignado' end) end) end as \"SCR_  \"
                    ,case when radi_fech_firma is not null then '$firma' else '' end as \"SCR_   \"
            
                    ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"                    
                    ,ver_usuarios(radi_usua_rem,',') as \"De\"
                    ,radi_asunto as \"Asunto\"
                    ,substr(fecha_documento::text,1,19)||' $descZonaHoraria' as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text as \"Número Documento\"
                    ,radi_cuentai as \"No. Referencia\"                   
                    ,ver_usuarios(radi_usua_ante::text,',') as \"Usuario Anterior\"
                    ,radi_leido as \"HID_RADI_LEIDO\"
                    from (
                        select b.radi_nume_radi, cat.cat_descr,b.radi_fech_asig, b.radi_fech_firma,5,6, b.radi_usua_rem, b.radi_asunto, b.fecha_documento, 7
                        ,b.radi_nume_text, b.radi_cuentai, b.radi_usua_ante
                        ,b.radi_nume_temp, b.radi_path, b.radi_tipo, b.radi_leido 
                        from (select *, $fecha_documento as fecha_documento from radicado b where radi_usua_actu=".$_SESSION["usua_codi"]
                    . " and esta_codi = 2 and radi_inst_actu = " . $_SESSION["inst_codi"] . " $whereFiltro $where_fecha_documento
                        and radi_nume_radi not in (select radi_nume_radi from tarea where estado=1 and usua_codi_ori=".$_SESSION["usua_codi"].")) as b
                        left outer join categoria cat on coalesce(b.cat_codi,0) = cat.cat_codi
                        order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo *LIMIT**OFFSET*
                    ) as a order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo";
            //echo $isql;
            break;

        case 6:  // Eliminados
        case 84:  // Ciudadanos Firma - Eliminados
            if (isset ($replicacion) && $replicacion && $config_db_replica_cuerpo_paginador!=$config_db_replica_bandeja_eliminados) $db = new ConnectionHandler($ruta_raiz,$config_db_replica_bandeja_eliminados);
            if ($orderNo=='') $orderNo=5;
            
            if ($version_light) $where_fecha_documento = " and (radi_fech_radi::date between '$txt_fecha_desde' and '$txt_fecha_hasta')";

            $isql = "select -- Eliminados $info_usuario_query
                    radi_nume_radi as \"CHK_CHKANULAR\"
                    ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    ,ver_usuarios(radi_usua_dest,',') as \"Para\"
                     ,radi_asunto as \"Asunto\"
                    ,substr(radi_fech_radi::text,1,19)||' $descZonaHoraria' as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text as \"Número Documento\"
                    ,estado as \"Estado\"
                    ,radi_leido as \"HID_RADI_LEIDO\"
                    from (
                        select radi_nume_radi, 1, 2, radi_usua_dest, radi_asunto, radi_fech_radi, 1, radi_nume_text
                            , case when radi_nume_radi::text like '%1' then 'Anulado' else 'Eliminado' end as estado, radi_leido
                        from radicado b where b.radi_usua_actu = " . $_SESSION["usua_codi"] .
                      " and b.esta_codi=7 and b.radi_inst_actu = " . $_SESSION["inst_codi"] . " $whereFiltro $where_fecha_documento
                        order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo *LIMIT**OFFSET*
                    ) as a order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo";
            break;

        case 7:  // No enviados, sin firma digital
            if (isset ($replicacion) && $replicacion && $config_db_replica_cuerpo_paginador!=$config_db_replica_bandeja_no_enviados) $db = new ConnectionHandler($ruta_raiz,$config_db_replica_bandeja_no_enviados);
            if ($orderNo=='') $orderNo=6;

            if ($version_light) $where_fecha_documento = " and (radi_fech_radi::date between '$txt_fecha_desde' and '$txt_fecha_hasta')";

            $isql = "select -- No enviados $info_usuario_query
                    radi_nume_radi as \"CHK_CHKANULAR\"
                    ,case when upper(cat_descr)='URGENTE' then '$urgente' else '' end as \" \"                    
                    ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    ,ver_usuarios(radi_usua_dest,',') as \"Para\"
                    ,radi_asunto as \"Asunto\"
                    ,substr(radi_fech_ofic::text,1,19)||' $descZonaHoraria' as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text as \"Número Documento\"
                    ,radi_cuentai as \"No. Referencia\"                    
                    ,radi_leido as \"HID_RADI_LEIDO\"
                    from (
                        select b.radi_nume_radi, cat.cat_descr,1, 2, b.radi_usua_dest, b.radi_asunto, b.radi_fech_ofic, 1,
                        b.radi_nume_text, b.radi_cuentai, b.radi_leido
                        from (select * from radicado b where radi_usua_actu = " . $_SESSION["usua_codi"] .
                      " and esta_codi=3 and radi_nume_radi = radi_nume_temp and
                        radi_inst_actu = " . $_SESSION["inst_codi"] . " $whereFiltro $where_fecha_documento) as b
                        left outer join categoria cat on coalesce(b.cat_codi,0) = cat.cat_codi
                        order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo *LIMIT**OFFSET*
                    ) as a order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo";
                
            break;
            
        case 8:  // Enviados
            if (isset ($replicacion) && $replicacion && $config_db_replica_cuerpo_paginador!=$config_db_replica_bandeja_enviados) $db = new ConnectionHandler($ruta_raiz,$config_db_replica_bandeja_enviados);
            if ($orderNo=='') $orderNo=8;

            $isql = "select -- Bandeja Enviados $info_usuario_query
                    radi_nume_radi as \"CHK_CHKANULAR\"
                    ,case when radi_fech_firma is not null then '$firma' else '' end as \"  \"
                    ,case when upper(cat_descr)='URGENTE' then '$urgente' else '' end as \" \"                    
                    ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    ,ver_usuarios(radi_usua_rem,',') as \"De\"
                    ,ver_usuarios(radi_usua_dest,',') as \"Para\"
                    ,radi_asunto as \"Asunto\"
                    ,substr(fecha_documento::text,1,19)||' $descZonaHoraria' as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text as \"Número Documento\"
                    ,radi_cuentai as \"No. Referencia\"                                    
                    ,radi_leido as \"HID_RADI_LEIDO\"
                    from (
                        select radi_nume_radi, radi_fech_firma,cat.cat_descr,1, 2, radi_usua_rem, radi_usua_dest, radi_asunto, fecha_documento
                        ,radi_nume_temp, radi_nume_text, radi_cuentai, radi_leido
                        from
                        ( select *, $fecha_documento as fecha_documento from radicado b where esta_codi=6 and radi_nume_radi=radi_nume_temp
                            and radi_usua_actu = " . $_SESSION["usua_codi"]. " and radi_inst_actu = " . $_SESSION["inst_codi"] . " $whereFiltro $where_fecha_documento) as b
                        left outer join categoria cat on coalesce(b.cat_codi,0) = cat.cat_codi                        
                        order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo *LIMIT**OFFSET*
                    ) as a order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo";

            break;

        case 10: //Archivados
            if (isset ($replicacion) && $replicacion && $config_db_replica_cuerpo_paginador!=$config_db_replica_bandeja_archivados) $db = new ConnectionHandler($ruta_raiz,$config_db_replica_bandeja_archivados);
            if ($orderNo=='') $orderNo=8;

            $isql = "select -- Bandeja Archivados $info_usuario_query
                    radi_nume_radi as \"CHK_CHKANULAR\"
                    ,case when radi_fech_firma is not null then '$firma' else '' end as \"  \"
                    ,case when upper(cat_descr)='URGENTE' then '$urgente' else '' end as \" \"                    
                    ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    ,ver_usuarios(radi_usua_rem,',') as \"De\"
                    ,ver_usuarios(radi_usua_dest,',') as \"Para\"
                    ,radi_asunto as \"Asunto\"
                    ,substr(fecha_documento::text,1,19)||' $descZonaHoraria' as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text as \"Número Documento\"
                    ,radi_cuentai as \"No. Referencia\"                    
                    ,radi_leido as \"HID_RADI_LEIDO\"
                    from (
                        select radi_nume_radi, radi_fech_firma,cat.cat_descr,1, 2, radi_usua_rem, radi_usua_dest, radi_asunto, fecha_documento
                        ,3, radi_nume_text, radi_cuentai, radi_leido
                        from
                        ( select *, $fecha_documento as fecha_documento from radicado b where radi_usua_actu = " . $_SESSION["usua_codi"]
                        . " and esta_codi=0 and radi_inst_actu = " . $_SESSION["inst_codi"] . " $whereFiltro $where_fecha_documento) as b
                        left outer join categoria cat on coalesce(b.cat_codi,0) = cat.cat_codi
                        order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo *LIMIT**OFFSET*
                    ) as a order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo";
            break;

        case 12:  // Bandeja Reasignados
            if (isset ($replicacion) && $replicacion && $config_db_replica_cuerpo_paginador!=$config_db_replica_bandeja_reasignados) $db = new ConnectionHandler($ruta_raiz,$config_db_replica_bandeja_reasignados);
            if ($orderNo=='') $orderNo=6;
            if ($estado !=-1 ) $whereFiltro .= " and b.esta_codi=".$estado; //Combo de estados

            $isql = "select -- Bandeja Reasignados $info_usuario_query
                    radi_nume_radi as \"CHK_CHKANULAR\"
                    ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    ,substr(fecha_documento::text,1,19)||' $descZonaHoraria' as \"Fecha Documento\"
                    ,usua_dest as \"Reasignado a\"
                    ,hist_obse as \"Comentario\"
                    ,substr(hist_fech::text,1,19)||' $descZonaHoraria' as \"DAT_Fecha Reasignación\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,hist_referencia as \"Fecha Max. de Respuesta\"
                    ,ver_usuarios(radi_usua_rem,',') as \"De\"
                    ,ver_usuarios(radi_usua_dest,',') as \"Para\"
                    ,radi_asunto as \"Asunto\"
                    ,radi_nume_text as \"Número Documento\"
                    ,esta_desc as \"Estado\"
                    ,radi_leido as \"HID_RADI_LEIDO\"
                    from (
                        select b.radi_nume_radi, 1, 2
                        , $fecha_documento as fecha_documento
                        , usuDest.usua_nomb || ' ' || usuDest.usua_apellido as usua_dest
                        , h2.hist_obse
                        , h2.hist_fech
                        , 3
                        , h2.hist_referencia
                        , b.radi_usua_rem, b.radi_usua_dest, b.radi_asunto
                        , b.radi_nume_text, es.esta_desc,b.radi_leido
                        from
                        (select radi_nume_radi, hist_fech, usua_codi_dest, hist_obse, hist_referencia from hist_eventos
                            where usua_codi_ori=".$_SESSION["usua_codi"]." and sgd_ttr_codigo=9 and hist_fech::date between '$txt_fecha_desde' and '$txt_fecha_hasta') as h2
                        left outer join usuarios usuDest on h2.usua_codi_dest = usuDest.usua_codi
                        left outer join radicado as b on h2.radi_nume_radi=b.radi_nume_radi
                        left outer join estado es on b.esta_codi=es.esta_codi
                        where 1=1 $whereFiltro
                        order by " . ($orderNo+1) . " $orderTipo, radi_nume_radi $orderTipo *LIMIT**OFFSET*
                    ) as a order by " . ($orderNo+1). " $orderTipo, radi_nume_radi $orderTipo";
            //echo $isql;
            break;

        case 13: // Bandeja Informados
            if (isset ($replicacion) && $replicacion && $config_db_replica_cuerpo_paginador!=$config_db_replica_bandeja_informados) $db = new ConnectionHandler($ruta_raiz,$config_db_replica_bandeja_informados);
            if ($orderNo=='') $orderNo=6;

            $where_filtro_info = "";
            if ($version_light) $where_filtro_info = " and info_fech::date between '$txt_fecha_desde' and '$txt_fecha_hasta'";
            if($tipoLectura!='2') $where_filtro_info .= " and info_leido = ".$tipoLectura;
            
            $isql = "select -- Bandeja Informados $info_usuario_query
                    radi_nume_radi AS \"CHK_checkValue\"
                    ,case when upper(cat_descr)='URGENTE' then '$urgente' else '' end as \" \"                    
                    ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    ,ver_usuarios(usua_info::text,',')  AS \"Informador\"
                    ,radi_asunto AS \"Asunto\"
                    ,substr(info_fech::text,1,19)||' $descZonaHoraria' as \"DAT_Fecha Información\"
                    ,radi_nume_radi AS \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text AS \"Número Documento\"                     
                    ,info_leido as \"HID_RADI_LEIDO\"
                    from (
                        select b.radi_nume_radi, cat.cat_descr,1, 2, i.usua_info, b.radi_asunto, i.info_fech
                        , b.radi_nume_temp, b.radi_nume_text, i.info_leido
                        from 
                        ( select * from informados where usua_codi = " . $_SESSION["usua_codi"] . " $where_filtro_info) as i
                        left outer join radicado b on b.radi_nume_radi=i.radi_nume_radi
                        left outer join categoria cat on coalesce(b.cat_codi,0) = cat.cat_codi
                        where 1=1 $whereFiltro
                        order by " . ($orderNo+1) . " $orderTipo, radi_nume_radi $orderTipo *LIMIT**OFFSET*
                    ) as a order by " . ($orderNo+1) . " $orderTipo, radi_nume_radi $orderTipo";
            //echo $isql;
            break;

        case 14: //Bandeja Compartida solo Documentos Recibidos
            if (isset ($replicacion) && $replicacion && $config_db_replica_cuerpo_paginador!=$config_db_replica_bandeja_compartida_recibidos) $db = new ConnectionHandler($ruta_raiz,$config_db_replica_bandeja_compartida_recibidos);
            if ($orderNo=='') $orderNo=7;

            $isql = "select -- Bandeja Compartida $info_usuario_query
                    radi_nume_radi as \"CHK_CHKANULAR\"
                    ,case when radi_fech_firma is not null then '$firma' else '' end as \"  \"
                    ,case when upper(cat_descr)='URGENTE' then '$urgente' else '' end as \" \"                    
                    ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    ,ver_usuarios(radi_usua_rem,',') as \"De\"
                    ,radi_asunto as \"Asunto\"
                    ,substr(fecha_documento::text,1,19)||' $descZonaHoraria' as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text as \"Número Documento\"
                    ,radi_cuentai as \"No. Referencia\"
                    ,esta_desc as \"Estado\"                    
                    ,radi_leido as \"HID_RADI_LEIDO\"
                    from (
                        select distinct b.radi_nume_radi, b.radi_fech_firma,cat.cat_descr
                        ,1, 2, b.radi_usua_rem,b.radi_asunto, b.fecha_documento, 1,
                        b.radi_nume_text, b.radi_cuentai, es.esta_desc,  b.radi_leido,
                        b.radi_nume_temp, b.radi_path, b.radi_tipo
                        from (select *, $fecha_documento as fecha_documento from radicado b where radi_usua_actu=".$_SESSION["usua_codi_jefe"]
                    . " and esta_codi = 2 and radi_inst_actu = " . $_SESSION["inst_codi"] . " $whereFiltro $where_fecha_documento
                        and radi_nume_radi not in (select radi_nume_radi from tarea where estado=1 and usua_codi_ori=".$_SESSION["usua_codi_jefe"].")) as b
                        left outer join estado es on b.esta_codi = es.esta_codi
                        left outer join categoria cat on coalesce(b.cat_codi,0) = cat.cat_codi
                        order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo *LIMIT**OFFSET*
                    ) as a order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo";
            break;

        case 15: //Tareas Recibidas o asignadas al usuario actual
            if (isset ($replicacion) && $replicacion && $config_db_replica_cuerpo_paginador!=$config_db_replica_bandeja_tareas_recibidas) $db = new ConnectionHandler($ruta_raiz,$config_db_replica_bandeja_tareas_recibidas);
            if ($orderNo=='') $orderNo=9;

            $where_tarea = "";
            if ($slc_tarea_estado != 0) $where_tarea .= " and estado=$tarea_estado";
            if (trim($whereFiltro) != "") $whereFiltro = "where 1=1 $whereFiltro";
            if ($version_light) $where_tarea .= " and (fecha_inicio::date between '$txt_fecha_desde' and '$txt_fecha_hasta')";

            $isql = "select -- Tareas Recibidas $info_usuario_query
                    radi_nume_radi as \"CHK_CHKANULAR\"
                    ,fecha_inicio::date ||'$descZonaHoraria' as \"Fecha Asignación\"
                    ,ver_usuarios(usua_codi_ori::text, ',') as \"Asignado por\"
                    ,comentario as \"Comentario\"
                    ,fecha_maxima::date ||'$descZonaHoraria' as \"Fecha Máxima\"
                    ,avance||'%' as \"Avance\"
                    ,estado as \"Estado\"
                    ,case when dias_retraso>0 then '<font color=\"red\">'||dias_retraso||' d&iacute;as</font>' else '' end as \"Dias Retraso\"
                    ,radi_nume_text as \"Número Documento\"
                    ,substr(fecha_documento::text,1,19)||'$descZonaHoraria' as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,ver_usuarios(radi_usua_rem,',') as \"De\"
                    ,ver_usuarios(radi_usua_dest,',') as \"Para\"
                    ,radi_asunto as \"Asunto\"
                    from (
                        select b.radi_nume_radi, t.fecha_inicio, t.usua_codi_ori, th.comentario, t.fecha_maxima, t.avance
                        , case when t.estado=1 then 'Pendiente' else (case when t.estado=2 then 'Finalizado' else 'Cancelado' end) end as \"estado\"
                        , coalesce(t.fecha_fin,now())::date-t.fecha_maxima::date as \"dias_retraso\"
                        , b.radi_nume_text, $fecha_documento as fecha_documento, 1, b.radi_usua_rem, b.radi_usua_dest, b.radi_asunto
                        from
                            (select * from tarea where usua_codi_dest=".$_SESSION["usua_codi"]." $where_tarea) as t
                            left outer join tarea_hist_eventos th on th.tarea_hist_codi=t.comentario_inicio
                            left outer join radicado b on t.radi_nume_radi=b.radi_nume_radi
                        $whereFiltro
                        order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo *LIMIT**OFFSET*
                    ) as a order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo";
            break;

        case 16: //Tareas Enviadas o Asignadas a otros funcionarios
            if (isset ($replicacion) && $replicacion && $config_db_replica_cuerpo_paginador!=$config_db_replica_bandeja_tareas_enviadas) $db = new ConnectionHandler($ruta_raiz,$config_db_replica_bandeja_tareas_enviadas);
            if ($orderNo=='') $orderNo=9;

            $where_tarea = "";
            if ($slc_tarea_estado != 0) $where_tarea .= " and estado=$tarea_estado";
            if (trim($whereFiltro) != "") $whereFiltro = "where 1=1 $whereFiltro"; //Para que se ejecute solo si hay algun filtro
            if ($version_light) $where_tarea .= " and (fecha_inicio::date between '$txt_fecha_desde' and '$txt_fecha_hasta')";

            $isql = "select -- Tareas Enviadas $info_usuario_query
                    radi_nume_radi as \"CHK_CHKANULAR\"
                    ,fecha_inicio::date ||'$descZonaHoraria' as \"Fecha Asignación\"
                    ,ver_usuarios(usua_codi_dest::text, ',') as \"Asignado para\"
                    ,comentario as \"Comentario\"
                    ,fecha_maxima::date ||'$descZonaHoraria' as \"Fecha Máxima\"
                    ,avance||'%' as \"Avance\"
                    ,estado as \"Estado\"
                    ,case when dias_retraso>0 then '<font color=\"red\">'||dias_retraso||' d&iacute;as</font>' else '' end as \"Dias Retraso\"
                    ,radi_nume_text as \"Número Documento\"
                    ,substr(fecha_documento::text,1,19)|| '$descZonaHoraria' as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,ver_usuarios(radi_usua_rem,',') as \"De\"
                    ,ver_usuarios(radi_usua_dest,',') as \"Para\"
                    ,radi_asunto as \"Asunto\"
                    from (
                        select b.radi_nume_radi, t.fecha_inicio, t.usua_codi_dest, th.comentario, t.fecha_maxima, t.avance
                        , case when t.estado=1 then 'Pendiente' else (case when t.estado=2 then 'Finalizado' else 'Cancelado' end) end as \"estado\"
                        , coalesce(t.fecha_fin,now())::date-t.fecha_maxima::date as \"dias_retraso\"
                        , b.radi_nume_text, $fecha_documento as fecha_documento, 1, b.radi_usua_rem, b.radi_usua_dest, b.radi_asunto
                        from
                            (select * from tarea where usua_codi_ori=".$_SESSION["usua_codi"]." $where_tarea) as t
                            left outer join tarea_hist_eventos th on th.tarea_hist_codi=t.comentario_inicio
                            left outer join radicado b on t.radi_nume_radi=b.radi_nume_radi
                        $whereFiltro
                        order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo *LIMIT**OFFSET*
                    ) as a order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo";
            break;
            
        case 80:  // Enviados Ciudadano
            if (isset ($replicacion) && $replicacion && $config_db_replica_cuerpo_paginador!=$config_db_replica_bandeja_enviados_ciudadanos) $db = new ConnectionHandler($ruta_raiz,$config_db_replica_bandeja_enviados_ciudadanos);
            if ($orderNo=='') $orderNo=6;

            if ($version_light) $where_fecha_documento = " and (radi_fech_radi::date between '$txt_fecha_desde' and '$txt_fecha_hasta')";

            $isql = "select -- Enviados Ciudadanos $info_usuario_query
                      radi_nume_radi as \"CHK_CHKANULAR\",'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    , 'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    , coalesce(u.usua_nomb,'')||' '||coalesce(u.usua_apellido,'') as \"Para\"
                    , i.inst_nombre as \"Institución\"
                    , b.radi_asunto as \"Asunto\"
                    , substr(radi_fech_radi::text,1,19)||' $descZonaHoraria' as \"DAT_Fecha Documento\"
                    , b.radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    , b.radi_nume_text as \"Número Documento\"
                    , b.radi_cuentai as \"No. Referencia\"
                    , es.esta_desc as \"Estado\"
                    from (select * from radicado b where string_to_array(trim(both '-' from radi_usua_rem), '--') @> array[".$_SESSION["usua_codi"]."::text]
                            and radi_nume_radi::text like '%1' and esta_codi in (0,2) $whereFiltro $where_fecha_documento) as b
                        left outer join estado es on b.esta_codi=es.esta_codi
                        left outer join usuarios u on replace(b.radi_usua_dest,'-','')::integer = u.usua_codi
                        left outer join institucion i on u.inst_codi=i.inst_codi
                    order by " . ($orderNo+1) . " $orderTipo, radi_nume_radi $orderTipo";
            break;

        case 81:  // Recibidos Ciudadano
            if (isset ($replicacion) && $replicacion && $config_db_replica_cuerpo_paginador!=$config_db_replica_bandeja_recibidos_ciudadanos) $db = new ConnectionHandler($ruta_raiz,$config_db_replica_bandeja_recibidos_ciudadanos);
            if ($orderNo=='') $orderNo=6;

            if ($version_light) $where_fecha_documento = " and (radi_fech_radi::date between '$txt_fecha_desde' and '$txt_fecha_hasta')";
            
            $isql = "select  -- Recibidos Ciudadanos $info_usuario_query
                      radi_nume_radi as \"CHK_CHKANULAR\",'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    , 'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    , coalesce(u.usua_nomb,'')||' '||coalesce(u.usua_apellido,'') as \"De\"
                    , i.inst_nombre as \"Institución\"
                    , b.radi_asunto as \"Asunto\"
                    , substr(radi_fech_ofic::text,1,19)||' $descZonaHoraria' as \"DAT_Fecha Documento\"
                    , b.radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    , b.radi_nume_text as \"Número Documento\"
                    , b.radi_cuentai as \"No. Referencia\"
                    , es.esta_desc as \"Estado\"
                    from (select * from radicado b where string_to_array(trim(both '-' from radi_usua_dest), '--') @> array[".$_SESSION["usua_codi"]."::text]
                            and radi_nume_radi::text like '%1' and esta_codi=6 $whereFiltro $where_fecha_documento) as b
                        left outer join estado es on b.esta_codi=es.esta_codi
                        left outer join usuarios u on replace(b.radi_usua_rem,'-','')::integer = u.usua_codi
                        left outer join institucion i on u.inst_codi=i.inst_codi
                    order by " . ($orderNo+1) . " $orderTipo, radi_nume_radi $orderTipo";
            break;

        case 90: //Pendientes recibidos de ciudadanos
            if (isset ($replicacion) && $replicacion && $config_db_replica_cuerpo_paginador!=$config_db_replica_bandeja_pendientes_ciudadanos) $db = new ConnectionHandler($ruta_raiz,$config_db_replica_bandeja_pendientes_ciudadanos);
            if ($orderNo=='') $orderNo=6;

            if ($version_light) $where_fecha_documento = " and (radi_fech_ofic::date between '$txt_fecha_desde' and '$txt_fecha_hasta')";

            $isql = "select -- Bandeja Pendientes recibidos de ciudadanos $info_usuario_query                    
                    radi_nume_radi as \"CHK_CHKANULAR\"
                    ,case when radi_fech_firma is not null then '$firma' else '' end as \"SCR_\"
                    ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    ,ver_usuarios(radi_usua_rem,',') as \"De\"
                    ,ver_usuarios(radi_usua_dest,',') as \"Para\"
                    ,radi_asunto as \"Asunto\"
                    ,substr(radi_fech_ofic::text,1,19)||' $descZonaHoraria' as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text as \"Número Documento\"
                    ,radi_cuentai as \"No. Referencia\"
                    ,ver_usuarios(usua_redirigido::text,',') as \"Redirigir a\"
                    ,radi_leido as \"HID_RADI_LEIDO\"
                    from (
                        select radi_nume_radi, radi_fech_firma,1, 2, radi_usua_rem, radi_usua_dest, radi_asunto, radi_fech_ofic
                        ,radi_nume_temp, radi_nume_text, radi_cuentai
                        , case when radi_usua_redirigido=0 then radi_usua_actu else radi_usua_redirigido end as usua_redirigido ,radi_leido
                        
                        from
                        ( select * from radicado b where esta_codi=9 and radi_inst_actu = " . $_SESSION["inst_codi"] . " $whereFiltro $where_fecha_documento) as b
                        order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo *LIMIT**OFFSET*
                    ) as a order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo";            
            break;

        case 99:  // Documentos por imprimir, envío manual
            if (isset ($replicacion) && $replicacion && $config_db_replica_cuerpo_paginador!=$config_db_replica_bandeja_por_imprimir) $db = new ConnectionHandler($ruta_raiz,$config_db_replica_bandeja_por_imprimir);
            if ($orderNo=='') $orderNo=9;

            if ($version_light) $where_fecha_documento = " and (radi_fech_ofic::date between '$txt_fecha_desde' and '$txt_fecha_hasta')";
            
            $isql = "select -- Documentos por Imprimir $info_usuario_query
                    radi_nume_radi as \"CHK_CHKANULAR\"
                    ,case when upper(cat_descr)='URGENTE' then '$urgente' else '' end as \" \"                    
                    ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    ,case when confidencial=0 then '<img src=\"$ruta_raiz/imagenes/document_down.jpg\" title=\"Descargar documento\" border=0 style=\"width: 24px; height: 20px;\">' end as \"SCR_   \"
                    ,case when confidencial=0 then 'anexos_descargar_archivo(\"'||radi_nume_radi||'\", \"\", 0, \"download\")' end as \"HID_PREVIEW\"
                    ,usua_remitente as \"De\"
                    ,ver_usuarios(radi_usua_dest,',') as \"Para\"
                    ,radi_asunto as \"Asunto\"
                    ,substr(radi_fech_ofic::text,1,19)||' $descZonaHoraria' as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text as \"Número Documento\"
                    ,radi_cuentai as \"No. Referencia\"
                    ,'No Enviado' as \"Estado\"                    
                    ,usua_radi_nombre as \"Redactado por\"
                    ,radi_leido as \"HID_RADI_LEIDO\"
                    from (
                        select distinct b.radi_nume_radi, cat.cat_descr, 1, 2, 3
                        , case when (b.radi_permiso=0 or (b.radi_permiso=1 and b.radi_usua_actu=".$_SESSION["usua_codi"].")) then 0 else 1 end as confidencial
                        , coalesce(ur.usua_nomb,'')||' '||coalesce(ur.usua_apellido,'') as usua_remitente
                        , b.radi_usua_dest, b.radi_asunto,b.radi_fech_ofic, b.radi_nume_temp, b.radi_nume_text, b.radi_cuentai, 3
                        , coalesce(ugr.usua_nomb,'')||' '||coalesce(ugr.usua_apellido,'') as usua_radi_nombre, b.radi_leido
                        from 
                        ( select * from radicado b where radi_inst_actu=".$_SESSION["inst_codi"]." and esta_codi=5 $whereFiltro $where_fecha_documento) as b
                        left outer join usuarios ur on split_part(b.radi_usua_rem,'-',2)::integer=ur.usua_codi and ur.depe_codi=".$_SESSION["depe_codi"]."
                        left outer join categoria cat on coalesce(b.cat_codi,0) = cat.cat_codi
                        left outer join radicado rp on b.radi_nume_temp=rp.radi_nume_radi
                        left outer join usuarios ugr on rp.radi_usua_radi=ugr.usua_codi
                        where ur.depe_codi is not null
                        order by " . ($orderNo+1) . " $orderTipo, radi_nume_radi $orderTipo *LIMIT**OFFSET*
                    ) as a order by " . ($orderNo+1) . " $orderTipo, radi_nume_radi $orderTipo";
                    //echo $isql;
            break;

        case 82:  // Ciudadanos Firma - En elaboración
            if (isset ($replicacion) && $replicacion && $config_db_replica_cuerpo_paginador!=$config_db_replica_bandeja_elaboracion_ciudadanos_firma) $db = new ConnectionHandler($ruta_raiz,$config_db_replica_bandeja_elaboracion_ciudadanos_firma);
            if ($orderNo=='') $orderNo=5;

            if ($version_light) $where_fecha_documento = " and (radi_fech_radi::date between '$txt_fecha_desde' and '$txt_fecha_hasta')";

            $isql = "select -- En elaboracion - Ciudadanos Firma $info_usuario_query
                    radi_nume_radi as \"CHK_CHKANULAR\"
                    ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    ,ver_usuarios(radi_usua_dest,',') as \"Para\"
                    ,radi_asunto as \"Asunto\"
                    ,substr(radi_fech_radi::text,1,19)||' $descZonaHoraria' as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text as \"Número Documento\"
                    ,radi_cuentai as \"No. Referencia\"
                    ,radi_leido as \"HID_RADI_LEIDO\"
                    from (
                        select b.radi_nume_radi, 1, 2, b.radi_usua_dest, b.radi_asunto
                        , b.radi_fech_radi, 3, b.radi_nume_text, b.radi_cuentai, b.radi_leido
                        from (select * from radicado b where radi_usua_actu=".$_SESSION["usua_codi"]
                    . " and esta_codi=1 and radi_inst_actu = " . $_SESSION["inst_codi"] . " $whereFiltro $where_fecha_documento) as b
                        order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo *LIMIT**OFFSET*
                    ) as a order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo";
            //ECHO $isql ;
            break;

        case 83:  // Recibidos
            if (isset ($replicacion) && $replicacion && $config_db_replica_cuerpo_paginador!=$config_db_replica_bandeja_recibidos_ciudadanos_firma) $db = new ConnectionHandler($ruta_raiz,$config_db_replica_bandeja_recibidos_ciudadanos_firma);
            if ($orderNo=='') $orderNo=6;

            if ($version_light) $where_fecha_documento = " and (radi_fech_ofic::date between '$txt_fecha_desde' and '$txt_fecha_hasta')";

            $isql = "select -- Recibidos - Ciudadanos Firma $info_usuario_query                    
                    radi_nume_radi as \"CHK_CHKANULAR\"
                    ,case when radi_fech_firma is not null then '$firma' else '' end as \"SCR_\"
                    ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    ,ver_usuarios(radi_usua_rem,',') as \"De\"
                    ,radi_asunto as \"Asunto\"
                    ,substr(radi_fech_ofic::text,1,19)||' $descZonaHoraria' as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text as \"Número Documento\"
                    ,radi_cuentai as \"No. Referencia\"
                    --,CASE WHEN radi_fech_firma is not null THEN 'SI' ELSE 'NO' END as \"Firma Digital\"            
                    ,1 as \"HID_RADI_LEIDO\"
                    from (
                        select b.radi_nume_radi,  b.radi_fech_firma,2, 3, b.radi_usua_rem, b.radi_asunto, b.radi_fech_ofic, 1,
                        b.radi_nume_text, b.radi_cuentai
                        from (select * from radicado b where string_to_array(trim(both '-' from radi_usua_dest), '--') @> array[".$_SESSION["usua_codi"]."::text]
                        and radi_nume_radi::text like '%1' and esta_codi in (2,6) $whereFiltro $where_fecha_documento) as b
                        order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo *LIMIT**OFFSET*
                    ) as a order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo";
            break;

        case 85:  // No enviados, sin firma digital
            if (isset ($replicacion) && $replicacion && $config_db_replica_cuerpo_paginador!=$config_db_replica_bandeja_no_enviados_ciudadanos_firma) $db = new ConnectionHandler($ruta_raiz,$config_db_replica_bandeja_no_enviados_ciudadanos_firma);
            if ($orderNo=='') $orderNo=5;

            if ($version_light) $where_fecha_documento = " and (radi_fech_ofic::date between '$txt_fecha_desde' and '$txt_fecha_hasta')";

            $isql = "select -- No enviados - Ciudadanos Firma $info_usuario_query
                    radi_nume_radi as \"CHK_CHKANULAR\"
                    ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    ,ver_usuarios(radi_usua_dest,',') as \"Para\"
                    ,radi_asunto as \"Asunto\"
                    ,substr(radi_fech_ofic::text,1,19)||' $descZonaHoraria' as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text as \"Número Documento\"
                    ,radi_cuentai as \"No. Referencia\"
                    ,1 as \"HID_RADI_LEIDO\"
                    from (
                        select b.radi_nume_radi, 1, 2, b.radi_usua_dest, b.radi_asunto, b.radi_fech_ofic, 1,
                        b.radi_nume_text, b.radi_cuentai, b.radi_leido
                        from (select * from radicado b where radi_usua_actu = " . $_SESSION["usua_codi"] .
                      " and esta_codi=3 and radi_nume_radi = radi_nume_temp and
                        radi_inst_actu = " . $_SESSION["inst_codi"] . " $whereFiltro $where_fecha_documento) as b
                        order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo *LIMIT**OFFSET*
                    ) as a order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo";

            break;

        case 86:  // Enviados
            if (isset ($replicacion) && $replicacion && $config_db_replica_cuerpo_paginador!=$config_db_replica_bandeja_enviados_ciudadanos_firma) $db = new ConnectionHandler($ruta_raiz,$config_db_replica_bandeja_enviados_ciudadanos_firma);
            if ($orderNo=='') $orderNo=7;

            if ($version_light) $where_fecha_documento = " and (radi_fech_radi::date between '$txt_fecha_desde' and '$txt_fecha_hasta')";

            $isql = "select -- Bandeja Enviados - Ciudadanos Firma $info_usuario_query                    ,case when radi_fech_firma is not null then '$firma' else '' end as \"SCR_\"
                    radi_nume_radi as \"CHK_CHKANULAR\"
                    ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    ,ver_usuarios(radi_usua_rem,',') as \"De\"
                    ,ver_usuarios(radi_usua_dest,',') as \"Para\"
                    ,radi_asunto as \"Asunto\"
                    ,substr(fecha::text,1,19)||' $descZonaHoraria' as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text as \"Número Documento\"
                    ,radi_cuentai as \"No. Referencia\"                    
                    ,1 as \"HID_RADI_LEIDO\"
                    from (
                        select radi_nume_radi, radi_fech_firma, 1, 2, radi_usua_rem, radi_usua_dest, radi_asunto
                        ,CASE WHEN radi_fech_firma is not null THEN radi_fech_firma ELSE radi_fech_ofic END as \"fecha\"
                        ,radi_nume_temp, radi_nume_text, radi_cuentai, radi_leido
                        from
                        ( select * from radicado b where 
                            string_to_array(trim(both '-' from radi_usua_rem), '--') @> array[".$_SESSION["usua_codi"]."::text]
                            and ((radi_nume_radi::text like '%1' and esta_codi in (0,2)) or (radi_nume_radi::text like '%0' and esta_codi in (6)))
                            $whereFiltro $where_fecha_documento
                        ) as b
                        order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo *LIMIT**OFFSET*
                    ) as a order by ".($orderNo+1)." $orderTipo, radi_nume_radi $orderTipo";

                //echo $isql;
            break;

        default:
            if ($orderNo=='') $orderNo=5;
            //$db = new ConnectionHandler("$ruta_raiz","busqueda");
            
            $isql = "select  -- Bandeja Nuevos
                    b.radi_nume_radi as \"CHK_CHKANULAR\"
                    ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    --, coalesce(ua.usua_nomb,'')||' '||coalesce(ua.usua_apellido,'') as \"Enviado Por\"
                    , ver_usuarios('-' || ua.usua_codi || '-',',') as \"Enviado Por\"
                    , b.radi_asunto as \"Asunto\"
                    ,substr(b.radi_fech_radi::text,1,19)||' $descZonaHoraria' as \"DAT_Fecha Documento\"
                    , b.radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    , b.radi_nume_text as \"Número Documento\"
                    , b.radi_cuentai as \"No. Referencia\"
                    , es.esta_desc as \"Estado\"
                    ,CASE WHEN b.radi_fech_firma is not null THEN 'SI' ELSE 'NO' END as \"Firma Digital\"
                    ,case when cat_descr is null then 'Normal' else case when upper(cat_descr)='URGENTE' then '<font color=\"red\">'||cat_descr||'</font>' else cat_descr end end as \"Categoría\"
                    ,b.radi_leido as \"HID_RADI_LEIDO\"
                    from (select * from radicado b where radi_usua_actu=".$_SESSION["usua_codi"]
                    . " and (esta_codi=1 or esta_codi=2) and radi_leido=0 $whereFiltro) as b
                    left outer join estado es on b.esta_codi=es.esta_codi
                    left outer join usuarios ua on b.radi_usua_ante=ua.usua_codi
                    left outer join categoria cat on b.cat_codi = cat.cat_codi
                    order by " . ($orderNo+1) . " $orderTipo, radi_nume_radi $orderTipo";

            
            break;
    }
    break;
}

//echo "filtro--".$whereFiltro."<br><br>";
//echo "<pre>".str_replace("<", "&lt;", $isql)."</pre>";
?>