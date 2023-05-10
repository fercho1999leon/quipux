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

//Por David Gamboa, los cambios que se realiza, es agregar filtros en los casos 12 y 13, respectivamente
//los filtros, son leidos, no leidos y todos.
switch($db->driver) {
    case 'postgres':
    $datos_usuarios = "(select usua_codi, usua_nomb||' '||usua_apellido as \"usua_nombre\", depe_codi, inst_codi from usuarios
                        union all select ciu_codigo, ciu_nombre||' '||ciu_apellido,0,0 from ciudadano) as ";

    $datos_usuarios_imprimir = "(select usua_codi, coalesce(usua_nomb,'')||' '||coalesce(usua_apellido,'') as \"usua_nombre\", depe_codi, inst_codi from usuarios where depe_codi=".$_SESSION["depe_codi"].") as ";
    //echo $carpeta;
    switch ($carpeta) {

        case 1:  // En elaboración
            if ($orderNo=='') $orderNo=4;
            $isql = "select -- En elaboracion light
                    radi_nume_radi as \"CHK_CHKANULAR\"
                    ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    ,radi_asunto as \"Asunto\"
                    ,substr(radi_fech_radi::text,1,19) as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text as \"Numero Documento\"
                    ,radi_cuentai as \"No. Referencia\"
                    ,trad_descr as \"Tipo Documento\"
                    ,radi_leido as \"HID_RADI_LEIDO\"
                    from (
                        (select * from radicado b where radi_usua_actu=".$_SESSION["usua_codi"]
                    . " and esta_codi=1 and radi_inst_actu = " . $_SESSION["inst_codi"] . " $whereFiltro ) as b
                        left outer join tiporad td on b.radi_tipo = td.trad_codigo
                    ) as a order by ".($orderNo+1)." $orderTipo";           
            break;
            
        case 2:  // Recibidos
            if ($orderNo=='') $orderNo=4;
             $recibido='<div align="center"><a href="#" class="Ntooltip"><img src="'.$ruta_raiz.'/imagenes/folder_page.png" width="15" height="15" alt="Recibido" border="0"><span>Recibido</span></div>';
            $vencido='<div align="center"><a href="#" class="Ntooltip"><img src="'.$ruta_raiz.'/imagenes/vencidos.png" width="15" height="15" alt="Vencido" border="0"><span>Vencido Reasignado</span></div>';
            $reasignado='<div align="center"><a href="#" class="Ntooltip"><img src="'.$ruta_raiz.'/imagenes/email_go.png" width="15" height="15" alt="Reasignado" border="0"><span>Reasignado</span></div>';
            $isql = "select
                    case when radi_fech_asig is null then '$recibido' else
                    (case when radi_fech_asig::date < now()::date then '$vencido'
                     else '$reasignado' end) end as \" \"
                    ,'Ayuda' as \"HID_Ayuda\"
                    -- Recibidos light
                    ,radi_nume_radi as \"CHK_CHKANULAR\"
                    ,radi_asunto as \"Asunto\"
                    ,CASE WHEN radi_fech_firma is not null THEN substr(radi_fech_firma::text,1,19)
                     ELSE substr(radi_fech_ofic::text,1,19) END as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text as \"Numero Documento\"
                    ,radi_cuentai as \"No. Referencia\"
                    ,esta_desc as \"Estado\"
                    ,CASE WHEN radi_fech_firma is not null THEN 'SI' ELSE 'NO' END as \"Firma Digital\"
                    ,radi_leido as \"HID_RADI_LEIDO\"
                    from (
                        (select * from radicado b where radi_usua_actu=".$_SESSION["usua_codi"]
                    . " and esta_codi = 2 and radi_inst_actu = " . $_SESSION["inst_codi"] . " $whereFiltro) as b
                        left outer join estado es on b.esta_codi = es.esta_codi
                    ) as a order by ".($orderNo+1)." $orderTipo";
            break;

        case 6:  // Eliminados
            if ($orderNo=='') $orderNo=4;
            //$db = new ConnectionHandler("$ruta_raiz","busqueda");

            $isql = "select -- Eliminados light
                    radi_nume_radi as \"CHK_CHKANULAR\"
                    ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    ,radi_asunto as \"Asunto\"
                    ,substr(radi_fech_radi::text,1,19) as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text as \"Numero Documento\"
                    ,radi_cuentai as \"No. Referencia\"
                    ,trad_descr as \"Tipo Documento\"
                    ,radi_leido as \"HID_RADI_LEIDO\"
                    from (
                        (select * from radicado b where b.radi_usua_actu = " . $_SESSION["usua_codi"] .
                      " and b.esta_codi=7 and b.radi_inst_actu = " . $_SESSION["inst_codi"] . " $whereFiltro) as b
                        left outer join tiporad td on b.radi_tipo = td.trad_codigo
                    ) as a order by ".($orderNo+1)." $orderTipo";
            break;

        case 7:  // No enviados, sin firma digital
            if ($orderNo=='') $orderNo=4;
            //$db = new ConnectionHandler("$ruta_raiz","busqueda");

            $isql = "select -- No enviados
                    radi_nume_radi as \"CHK_CHKANULAR\"
                    ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    ,radi_asunto as \"Asunto\"
                    ,substr(radi_fech_ofic::text,1,19) as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text as \"Numero Documento\"
                    ,radi_cuentai as \"No. Referencia\"
                    ,trad_descr as \"Tipo Documento\"
                    ,radi_leido as \"HID_RADI_LEIDO\"
                    from (
                        (select * from radicado b where radi_usua_actu = " . $_SESSION["usua_codi"] .
                      " and esta_codi=3 and radi_nume_radi = radi_nume_temp and
                        radi_inst_actu = " . $_SESSION["inst_codi"] . " $whereFiltro) as b
                        left outer join tiporad td on b.radi_tipo = td.trad_codigo
                    ) as a order by ".($orderNo+1)." $orderTipo";
                
            break;
            
        case 8:  // Enviados
            if ($orderNo=='') $orderNo=4;
//            $db = new ConnectionHandler("$ruta_raiz","busqueda");

            $isql = "select -- Bandeja Enviados light
                    radi_nume_radi as \"CHK_CHKANULAR\"
                     ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    ,radi_asunto as \"Asunto\"
                    ,substr(CASE WHEN radi_fech_firma is not null THEN radi_fech_firma ELSE radi_fech_ofic END::text,1,19) as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text as \"Numero Documento\"
                    ,radi_cuentai as \"No. Referencia\"
                    ,trad_descr as \"Tipo Documento\"
                    ,CASE WHEN radi_fech_firma is not null THEN 'SI' ELSE 'NO' END as \"Firma Digital\"
                    ,radi_leido as \"HID_RADI_LEIDO\"
                    from (
                        (select * from radicado b where esta_codi=6 and radi_nume_radi=radi_nume_temp
                            and radi_usua_actu = " . $_SESSION["usua_codi"]. " and radi_inst_actu = " . $_SESSION["inst_codi"] . " $whereFiltro) as b
                        left outer join tiporad td on b.radi_tipo = td.trad_codigo
                    ) as a order by ".($orderNo+1)." $orderTipo";

                //echo $isql;
            break;

        case 10: //Archivados
            if ($orderNo=='') $orderNo=4;
            //$db = new ConnectionHandler("$ruta_raiz","busqueda");

            $isql = "select -- Bandeja Archivados
                    radi_nume_radi as \"CHK_CHKANULAR\"
                    ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    ,radi_asunto as \"Asunto\"
                    ,substr(CASE WHEN radi_fech_firma is not null THEN radi_fech_firma ELSE radi_fech_ofic END::text,1,19) as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text as \"Numero Documento\"
                    ,radi_cuentai as \"No. Referencia\"
                    ,trad_descr as \"Tipo Documento\"
                    ,radi_leido as \"HID_RADI_LEIDO\"
                    from (
                        ( select * from radicado b where radi_usua_actu = " . $_SESSION["usua_codi"]
                        . " and esta_codi=0 and radi_inst_actu = " . $_SESSION["inst_codi"] . " $whereFiltro) as b
                        left outer join tiporad td on b.radi_tipo = td.trad_codigo
                    ) as a order by ".($orderNo+1)." $orderTipo";
            break;

        case 12:  // Bandeja Reasignados
            if ($orderNo=='') $orderNo=2;
            //$db = new ConnectionHandler("$ruta_raiz","busqueda");
            //echo "Estado: ".$_POST['estado'];
            $isql = "select -- Bandeja Reasignados
                    radi_nume_radi as \"CHK_CHKANULAR\"
                     ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    ,substr(radi_fech_ofic::text,1,19) as \"Fecha Documento\"
                    ,usua_dest as \"Reasignado a\"
                    ,hist_obse as \"Comentario\"
                    ,substr(hist_fech::text,1,19) as \"DAT_Fecha Reasignación\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,hist_referencia as \"Fecha Max. de Respuesta\"
                    ,radi_asunto as \"Asunto\"
                    ,radi_nume_text as \"Número Documento\"
                    ,radi_leido as \"HID_RADI_LEIDO\"
                    from (
                        select b.radi_nume_radi, h.hist_fech, hist_obse, h.hist_referencia, b.radi_leido, b.radi_asunto, b.radi_nume_text, b.radi_fech_ofic
                        , ud.usua_nomb ||' '|| ud.usua_apellido as usua_dest
                        from
                            (select radi_nume_radi, hist_fech, usua_codi_dest, hist_obse, hist_referencia from hist_eventos where usua_codi_ori=".$_SESSION["usua_codi"]." and sgd_ttr_codigo=9) as h
                            left outer join usuarios ud on h.usua_codi_dest = ud.usua_codi
                            left outer join radicado as b on h.radi_nume_radi=b.radi_nume_radi";
            if($_GET["tipoLectura"]=='' or $_GET["tipoLectura"]=='2')
                $isql.=" where radi_leido in (0,1) and radi_inst_actu = " . $_SESSION["inst_codi"] . " $whereFiltro";
            else
                $isql.=" where radi_leido = ".$_GET["tipoLectura"]." and radi_inst_actu = " . $_SESSION["inst_codi"] . " $whereFiltro";
                if ($_POST['estado']=='')
                 $isql.= "and b.esta_codi=6";
                 else
                $isql.= "and b.esta_codi=".$_POST['estado'];
                // if ( $_POST["txt_fecha_desde"]!='' and  $_POST["txt_fecha_hasta"]!='')//si selecciono fechas
                $isql.=" and radi_fech_ofic between '".$txt_fecha_desde."' and '".$txt_fecha_hasta."'";
                $isql.=") as a order by " . ($orderNo+1). " $orderTipo";
            break; 
            
        case 13: // Bandeja Informados
            if ($orderNo=='') $orderNo=4;
            //$db = new ConnectionHandler("$ruta_raiz","busqueda");

            $isql = "select -- Bandeja Informados light
                    radi_nume_radi AS \"CHK_checkValue\"
                    ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    ,radi_asunto AS \"Asunto\"
                    ,substr(info_fech::text,1,19) as \"DAT_Fecha Información\"
                    ,radi_nume_radi AS \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text AS \"Numero Documento\"
                    ,trad_descr as \"Tipo Documento\"
                    ,info_leido as \"HID_RADI_LEIDO\"
                    from (
                        select b.radi_nume_radi, b.radi_asunto, i.info_fech
                        , b.radi_nume_text, i.info_leido, td.trad_descr
                        from 
                        ( select * from informados where usua_codi = " . $_SESSION["usua_codi"] . ") as i
                        left outer join radicado b on b.radi_nume_radi=i.radi_nume_radi
                        left outer join tiporad td on b.radi_tipo = td.trad_codigo";           
            if($_GET["tipoLectura"]=='' or $_GET["tipoLectura"]=='2')//{//si no selecciono fechas
                $isql.=" where info_leido in (0,1) and b.radi_inst_actu = " . $_SESSION["inst_codi"] . " $whereFiltro";
            else
                $isql.=" where info_leido = ".$_GET["tipoLectura"]." and b.radi_inst_actu = " . $_SESSION["inst_codi"] . " $whereFiltro";
               //if ( $_POST["txt_fecha_desde"]!='' and  $_POST["txt_fecha_hasta"]!='')//si selecciono fechas
                //$isql.=" and info_fech between '".$txt_fecha_desde."' and '".$txt_fecha_hasta."'";
                $isql.=") as a order by " . ($orderNo+1) . " $orderTipo";           
           // echo $txt_fecha_hasta;
            //echo $isql;
            break;

        case 14: //Bandeja Compartida solo Documentos Recibidos
            if ($orderNo=='') $orderNo=3;

            $isql = "select -- Bandeja Compartida
                    radi_nume_radi as \"CHK_CHKANULAR\"
                    ,ver_usuarios(radi_usua_rem,',') as \"De\"
                    ,radi_asunto as \"Asunto\"
                    ,substr(radi_fech_ofic::text,1,19) as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text as \"Numero Documento\"
                    ,radi_cuentai as \"No. Referencia\"
                    ,esta_desc as \"Estado\"
                    ,CASE WHEN radi_fech_firma is not null THEN 'SI' ELSE 'NO' END as \"Firma Digital\"
                    ,radi_leido as \"HID_RADI_LEIDO\"
                    from (
                        select distinct b.radi_nume_radi, b.radi_usua_rem,b.radi_asunto, b.radi_fech_ofic, 1,
                        b.radi_nume_text, b.radi_cuentai, es.esta_desc, b.radi_fech_firma, b.radi_leido,
                        b.radi_nume_temp, b.radi_path, b.radi_tipo
                        from (select * from radicado b where radi_usua_actu=".$_SESSION["usua_codi_jefe"]
                    . " and esta_codi = 2 and radi_inst_actu = " . $_SESSION["inst_codi"] . " $whereFiltro) as b
                        left outer join estado es on b.esta_codi = es.esta_codi
                        order by ".($orderNo+1)." $orderTipo *LIMIT**OFFSET*
                    ) as a order by ".($orderNo+1)." $orderTipo";
            break;

        case 80:  // Enviados Ciudadano
            if ($orderNo=='') $orderNo=4;
            $db = new ConnectionHandler("$ruta_raiz","busqueda");

            $isql = "select -- Enviados Ciudadanos
                    distinct u.usua_nombre as \"Para\"
                    , u.inst_nombre as \"Institucion\"
                    , b.radi_asunto as \"Asunto\"
                    , substr(radi_fech_radi::text,1,19) as \"DAT_Fecha Documento\"
                    , b.radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    , b.radi_nume_text as \"Numero Documento\"
                    , b.radi_cuentai as \"No. Referencia\"
                    , es.esta_desc as \"Estado\"
                    from (select * from radicado b where radi_usua_rem like '%-".$_SESSION["usua_codi"]."-%' and radi_nume_radi::text like '%1' $whereFiltro) as b
                        left outer join estado es on b.esta_codi=es.esta_codi
                        left outer join usuario u on replace(b.radi_usua_dest,'-','')::integer = u.usua_codi
                    order by " . ($orderNo+1) . " $orderTipo";
//            echo $isql;
            break;

        case 81:  // Recibidos Ciudadano
            if ($orderNo=='') $orderNo=4;
            $db = new ConnectionHandler("$ruta_raiz","busqueda");

            $isql = "select  -- Recibidos Ciudadanos
                    distinct u.usua_nombre as \"De\"
                    , u.inst_nombre as \"Institucion\"
                    , b.radi_asunto as \"Asunto\"
                    , substr(radi_fech_ofic::text,1,19) as \"DAT_Fecha Documento\"
                    , b.radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    , b.radi_nume_text as \"Numero Documento\"
                    , b.radi_cuentai as \"No. Referencia\"
                    , es.esta_desc as \"Estado\"
                    from (select * from radicado b where radi_usua_dest||coalesce(radi_cca,'') like '%-".$_SESSION["usua_codi"]."-%' and radi_nume_radi::text like '%1' $whereFiltro) as b
                        left outer join estado es on b.esta_codi=es.esta_codi
                        left outer join usuario u on replace(b.radi_usua_rem,'-','')::integer = u.usua_codi
                    order by " . ($orderNo+1) . " $orderTipo";
            break;

        case 99:  // Documentos por imprimir, envío manual
            if ($orderNo=='') $orderNo=4;
            //$db = new ConnectionHandler("$ruta_raiz","busqueda");
            $isql = "select -- Documentos por Imprimir
                    radi_nume_radi as \"CHK_CHKANULAR\"
                    ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    ,ver_usuarios(radi_usua_rem,',') as \"De\"
                    ,ver_usuarios(radi_usua_dest,',') as \"Para\"
                    ,radi_asunto as \"Asunto\"
                    ,substr(radi_fech_ofic::text,1,19) as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text as \"Numero Documento\"
                    ,radi_cuentai as \"No. Referencia\"
                    ,'No Enviado' as \"Estado\"
                    ,radi_leido as \"HID_RADI_LEIDO\"
                    from (
                        select distinct b.radi_nume_radi, b.radi_usua_rem, b.radi_usua_dest, b.radi_asunto
                        ,b.radi_fech_ofic, b.radi_nume_temp, b.radi_nume_text, b.radi_cuentai, '1', b.radi_leido
                        from 
                        ( select * from radicado b where radi_inst_actu=".$_SESSION["inst_codi"]." and esta_codi=5 $whereFiltro) as b
                        left outer join $datos_usuarios_imprimir ur on replace(b.radi_usua_rem,'-','')::integer=ur.usua_codi
                        where ur.depe_codi is not null
                        order by " . ($orderNo+1) . " $orderTipo *LIMIT**OFFSET*
                    ) as a order by " . ($orderNo+1) . " $orderTipo";
                    //echo $isql;
            break;

        default:
            if ($orderNo=='') $orderNo=3;
            //$db = new ConnectionHandler("$ruta_raiz","busqueda");
            
            $isql = "select  -- Bandeja Nuevos
                    b.radi_nume_radi as \"CHK_CHKANULAR\"
                    ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                    ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                    , b.radi_asunto as \"Asunto\"
                    , substr(b.radi_fech_radi::text,1,19) as \"DAT_Fecha Documento\"
                    , b.radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    , b.radi_nume_text as \"Numero Documento\"
                    , b.radi_cuentai as \"No. Referencia\"
                    ,CASE WHEN b.radi_fech_firma is not null THEN 'SI' ELSE 'NO' END as \"Firma Digital\"
                    ,b.radi_leido as \"HID_RADI_LEIDO\"
                    from radicado b
                    where radi_usua_actu=".$_SESSION["usua_codi"]
                    . " and esta_codi in (1,2) and radi_leido=0 $whereFiltro
                    order by " . ($orderNo+1) . " $orderTipo";
            break;
    }
	break;	
}

//echo "filtro--".$whereFiltro."<br><br>";
//echo $isql;
?>