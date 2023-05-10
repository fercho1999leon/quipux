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
// La función ver_usuarios tarda mucho tiempo en ejecutarse y cuando son muchos registros la ejecución de
// esta consulta se hace muy pesada.
// Para mejorar esto se cambiaron algunas librerias de ADODB para que al momento de realizar el count elimine la función
// y el limit y el offset se los pone en el query interior para que la función se ejecute solo para los registros que se van a mostrar.
// Adicionalmente se elimino la ejecución de la función en el count del paginador
// Archivos ADODB: (revisión svn 456)
// - adodb/adodb-lib.inc.php    - function _adodb_getcount()
// - adodb/drivers/adodb-postgres7.inc.php  - function SelectLimit()
//echo "Si no tienen firma: ".$txt_sino_firma;
$ruta_raiz = "..";

switch($db->driver)
{
    case 'postgres':
        $nestloop = "off";
        $tipo_fecha = "radi_fech_ofic";
        if ($txt_tipo_fecha == "1") $tipo_fecha = "radi_fech_radi";
        $sqlFecha = "substr($tipo_fecha::text,1,19)";

        $txt_inst_codi = ($_SESSION["perm_buscar_doc_adscritas"]==1) ? 0+limpiar_numero($txt_inst_codi) : $_SESSION["inst_codi"];

        $txt_fecha_desde = limpiar_sql($txt_fecha_desde);
        $txt_fecha_hasta = limpiar_sql($txt_fecha_hasta);

        $where = "r.radi_inst_actu=$txt_inst_codi
                  and r.$tipo_fecha::date >= '$txt_fecha_desde'::date
                  and r.$tipo_fecha::date <= '$txt_fecha_hasta'::date";
        //$where_ext = " (r.esta_codi!=6 or r.radi_nume_radi::text not like '%1')";
	$where_ext = " (r.esta_codi!=6 or mod(r.radi_nume_radi,10)<>1)";
        $from = "";
        $distinct = "";
        if (trim($txt_nume_documento) != ""){
           if (strlen($txt_nume_documento)>=4) {
               if ((0+limpiar_numero($_GET["rad_nume_docu_exacto"]))==0)
                   $where .= " and r.radi_nume_text = upper('".trim(limpiar_sql($txt_nume_documento))."')";
               else {
                   $where .= " and r.radi_nume_text ilike '%".trim(limpiar_sql($txt_nume_documento))."%'";
                   $nestloop = "on";
               }
           }
        }
        if (trim($txt_nume_referencia) != ""){
            if (strlen($txt_nume_referencia)>=4) {
                $where .= " and r.radi_cuentai ilike upper('%".trim(limpiar_sql($txt_nume_referencia))."%')";
                $nestloop = "on";
            }
        }
        if (trim($txt_texto) != "") {
            if (strlen($txt_texto)>=4) {
                //Añadido para buscar en el campo radi_resumen donde se guarda la información de notas
                $where .= " and (" . buscar_cadena(limpiar_sql($txt_texto),"coalesce(r.radi_asunto,'')||' '||coalesce(r.radi_resumen,'')").") ";
                $nestloop = "on";
            }
        }

        if (trim($txt_tipo_documento) != "0")
            $where .= " and r.radi_tipo=".(0 + limpiar_numero($txt_tipo_documento));

        if (trim($txt_categoria) != "")
            $where_ext .= " and coalesce(r.cat_codi,0)=".(0 + limpiar_numero($txt_categoria));
        if (trim($txt_tipificacion) != "")
            $where_ext .= " and coalesce(r.cod_codi,0)=".(0 + limpiar_numero($txt_tipificacion));

        //Armar where para busqueda por De (remitente)
        if(trim($txt_usua_remitente) != "") {
            $where .= " and r.radi_nume_temp in (select radi_nume_radi from usuarios_radicado where radi_usua_tipo=1 ";
            $txt_usua_remitente = str_replace(array(".","(",")",'"',",",";"), " ", $txt_usua_remitente);
            $tmp1 = explode(" ",limpiar_sql($txt_usua_remitente));
            $campo_tsearch = "(((((COALESCE(usua_nombre, '' )) || ' ' ) ||
                                 (COALESCE(usua_apellido, '' ))) || ' ' ) ||
                                 (COALESCE(usua_institucion, '' )))";
            foreach($tmp1 as $tmp) {
                if(trim($tmp) != "")
                    $where .= " and " . buscar_cadena_tsearch ($tmp, $campo_tsearch);
            }
            $where .= ")";
        }

        //Armar where para busqueda por Para (destinatario)
        if(trim($txt_usua_destinatario) != "") {
            $where .= " and r.radi_nume_temp in (select radi_nume_radi from usuarios_radicado where radi_usua_tipo in (2,3) ";
            $txt_usua_destinatario = str_replace(array(".","(",")",'"',",",";"), " ", $txt_usua_destinatario);
            $tmp1 = explode(" ",limpiar_sql($txt_usua_destinatario));
            $campo_tsearch = "(((((COALESCE(usua_nombre, '' )) || ' ' ) ||
                                 (COALESCE(usua_apellido, '' ))) || ' ' ) ||
                                 (COALESCE(usua_institucion, '' )))";
            foreach($tmp1 as $tmp) {
                if(trim($tmp) != "")
                    $where .= " and " . buscar_cadena_tsearch($tmp, $campo_tsearch);
            }
            $where .= ")";
        }
        
        //METADATOS
       
        //Armar where para busqueda por Para (destinatario)
        if(trim($txt_campo_metadato) != "") {
            $where .= " and r.radi_nume_radi in (select radi_nume_radi from metadatos_radicado where depe_codi <> 0 ";
            $txt_campo_metadato = str_replace(array(".","(",")",'"',",",";"), " ", $txt_campo_metadato);
            $tmp1 = explode(" ",limpiar_sql($txt_campo_metadato));
            $campo_tsearch = "((COALESCE(metadato_texto, '' )) || ' ' )";
            foreach($tmp1 as $tmp) {
                if(trim($tmp) != "")
                    $where .= " and " . buscar_cadena_tsearch($tmp, $campo_tsearch);
            }
            $where .= ")";
        }
        
        if ($txt_depe_codi != "0" or $txt_usua_codi != "0") {
            $usr = "";
            if ($txt_usua_codi == "0") {
                $sql = "select usua_codi from usuarios where depe_codi=".limpiar_numero($txt_depe_codi);
                $rs = $rs = $db->conn->Execute($sql);
                if (!$rs->EOF) {
                    while (!$rs->EOF) {
                        $usr .= $rs->fields["USUA_CODI"] . ",";
                        $rs->MoveNext();
                    }
                    $usr = substr($usr,0,-1);
                }
            } else {
                $usr = limpiar_numero($txt_usua_codi);
            }
            if (trim($usr)!='') {
                $from .= "left outer join (( (( ((select radi_nume_radi from hist_eventos where (usua_codi_ori in ($usr) or usua_codi_dest in ($usr)) and hist_fech::date between '$txt_fecha_desde' and '$txt_fecha_hasta')) )) )) as h on r.radi_nume_radi=h.radi_nume_radi";
                $distinct = "distinct";
                $where_ext .= " and h.radi_nume_radi is not null";
            }
        }

        if ($txt_estado==999)
            $where .= " and r.esta_codi in (0,1,2,3,6,7,9)";
        else
            $where .= " and r.esta_codi = ".$txt_estado;
        if ($txt_sino_firma==0)
            $where .= " and r.radi_fech_firma is null";
        elseif($txt_sino_firma==1)
            $where .= " and r.radi_fech_firma is not null";

        //VER USUARIOS SUBROGACION
        $sbrgado='<b>(Subrogado)</b>';
        $sbrgate='<b>(Subrogante)</b>';
          
        
        $isql = "select -- Busqueda Avanzada - USR ".$_SESSION["usua_codi"]." - ".date("Y-m-d H:i:s")." - nestloop=$nestloop<br>
            case when (select 1 from radicado r2 where a.radi_nume_temp=r2.radi_nume_radi and a.radi_nume_radi<>r2.radi_nume_radi and r2.radi_inst_actu=$txt_inst_codi) is null then '' else '&nbsp;&nbsp;<b>&rarr;</b>&nbsp;' end || radi_nume_text as \"No. Documento\"
            ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
            ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\")' as \"HID_POPUP\"
            ,$sqlFecha || '$descZonaHoraria' as \"DAT_Fecha Documento\"
            ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
            ,radi_cuentai as \"No. de Referencia\"
            ,radi_asunto  as \"Asunto\"
            ,usua_nombre || case when radi_usua_actu
            in (select usua_subrogado from usuarios_subrogacion 
            where usua_visible=1) = true then ' $sbrgado' else '' end 
            || case when radi_usua_actu
            in (select usua_subrogante from usuarios_subrogacion 
            where usua_visible=1) = true then ' $sbrgate' else '' end as \"Usuario Actual\" 
            ,depe_nomb as \"Área Actual\"
            ,ver_usuarios(radi_usua_rem,',') as \"De\"
            ,ver_usuarios(radi_usua_dest,',') as \"Para\"
            ,trad_descr as \"Tipo de Documento\"
            ,esta_desc as \"Estado\"
            ,CASE WHEN radi_fech_firma is not null THEN 'SI' ELSE 'NO' END as \"Firma Digital\"
            from (
                select $distinct radi_nume_text, 'popup1', 'popup2', $tipo_fecha, r.radi_nume_radi, radi_cuentai, radi_asunto
                    , usua_nombre, radi_usua_actu, u.depe_nomb
                    , radi_usua_rem, radi_usua_dest, t.trad_descr, radi_fech_firma, radi_nume_temp";

                    $isql.=", case when mod(r.radi_nume_radi,10)=1";
		$isql.=" and r.esta_codi=7 then 'Anulado' else es.esta_desc end as esta_desc
                    ,fecha_orden
                from (
                    select r.radi_nume_text, r.$tipo_fecha, r.radi_nume_radi, r.radi_cuentai, r.radi_asunto, r.radi_usua_actu,
                        r.radi_usua_rem, r.radi_usua_dest, r.radi_fech_firma, r.radi_tipo, r.radi_fech_radi as \"fecha_orden\"
                        , r.radi_nume_temp, r.esta_codi, r.radi_resumen, r.cat_codi, r.cod_codi
                    from radicado r 
                    where $where                    
            ) as r
            left outer join estado es on r.esta_codi=es.esta_codi
            left outer join usuario u on r.radi_usua_actu=u.usua_codi
            left outer join tiporad t on r.radi_tipo=t.trad_codigo
            $from
            where $where_ext
            order by " . ($orderNo+1) . " $orderTipo, fecha_orden asc *LIMIT**OFFSET*
        ) as a";
        //echo str_replace("<", "&lt;", $isql);
        
        //Genera para pdf o xls
      
        if ($txt_reporte==1){
         //echo $sqlFecha; 
         $isql2 = "select -- Busqueda Avanzada Reporte - USR ".$_SESSION["usua_codi"]." - ".date("Y-m-d H:i:s")." - nestloop=$nestloop<br>
            case when (select 1 from radicado r2 where a.radi_nume_temp=r2.radi_nume_radi and a.radi_nume_radi<>r2.radi_nume_radi and r2.radi_inst_actu=$txt_inst_codi) is null then '' else '&nbsp;&nbsp;<b>&rarr;</b>&nbsp;' end || radi_nume_text as \"No. Documento\"
            ,$sqlFecha || '$descZonaHoraria' as \"Fecha Documento\"
            ,radi_cuentai as \"No. de Referencia\"
            ,radi_asunto  as \"Asunto\"
            ,usua_nombre || case when radi_usua_actu
            in (select usua_subrogado from usuarios_subrogacion 
            where usua_visible=1) = true then ' $sbrgado' else '' end 
            || case when radi_usua_actu
            in (select usua_subrogante from usuarios_subrogacion 
            where usua_visible=1) = true then ' $sbrgate' else '' end as \"Usuario Actual\" 
            ,depe_nomb as \"Área Actual\"
            ,ver_usuarios(radi_usua_rem,',') as \"De\"
            ,ver_usuarios(radi_usua_dest,',') as \"Para\"
            ,trad_descr as \"Tipo de Documento\"
            , esta_desc as \"Estado\"
            ,CASE WHEN radi_fech_firma is not null THEN 'SI' ELSE 'NO' END as \"Firma Digital\"
            from (
                select $distinct radi_nume_text, $tipo_fecha, radi_cuentai, radi_asunto
                    , usua_nombre, r.radi_usua_actu, u.depe_nomb, radi_usua_rem, radi_usua_dest, t.trad_descr";
		$isql2.=" , case when mod(r.radi_nume_radi,10)=1 ";
		$isql2.=" and r.esta_codi=7 then 'Anulado' else es.esta_desc end as esta_desc
                    , radi_fech_firma, radi_nume_temp, r.radi_nume_radi, fecha_orden
                    
                from (
                    select r.radi_nume_text, r.$tipo_fecha, r.radi_nume_radi, r.radi_cuentai, r.radi_asunto, r.radi_usua_actu, r.radi_usua_rem
                        , r.radi_usua_dest, r.radi_fech_firma, r.radi_tipo, r.radi_fech_radi as \"fecha_orden\", r.radi_nume_temp, r.esta_codi, r.cat_codi
                        
                    from radicado r
                    where $where
                ) as r
                left outer join estado es on r.esta_codi=es.esta_codi
                left outer join usuario u on r.radi_usua_actu=u.usua_codi
                left outer join tiporad t on r.radi_tipo=t.trad_codigo
                $from
                where $where_ext
                order by " . ($orderNo+1) . " $orderTipo, fecha_orden asc limit 1000 offset 0
            ) as a";
        }
        //echo $isql2;
	break;
}
?>
