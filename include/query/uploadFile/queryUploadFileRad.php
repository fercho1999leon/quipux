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
**/

switch($db->driver)
{
    case 'postgres':
//	$sqlFecha = "r.RADI_FECH_RADI";
    $sqlFecha = "substr(r.radi_fech_radi::text, 1,19)";
    $fecha_documento = "(case when radi_nume_temp::text like '%0' then radi_fech_ofic else radi_fech_radi end)";
    $where_fecha_documento = " and ($fecha_documento::date between '$txt_fecha_desde' and '$txt_fecha_hasta')";

    if ($orderNo=='') { $orderNo = 5; $orderNo2 = 4; }
    //Asociar imagen
    $query = "SELECT --Cargar Documento Digitalizado
            radi_nume_radi as \"CHR_DATO\"
            , '<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
            , 'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
            , case when radi_imagen is not null then '<img src=\"$ruta_raiz/iconos/visto.png\" border=0 style=\"width:10px; height: 10px;\">' else '' end as \" \"
            , radi_nume_text as \"Número $descRadicado\"
            , substr(fecha_documento::text, 1,19) || '$descZonaHoraria' as \"DAT_Fecha $descRadicado\"
            , radi_nume_radi as \"HID_RADI_NUME_RADI\"
            , radi_asunto as \"Asunto\"
            , ver_usuarios(radi_usua_rem,',<br>') as \"De\"
            , ver_usuarios(radi_usua_dest,',<br>') as \"Para\"
            from (
                select b.radi_nume_radi, 1, 2
                , b.radi_imagen, b.radi_nume_text, b.fecha_documento, 3
                , b.radi_asunto, b.radi_usua_rem, b.radi_usua_dest, b.radi_usua_redirigido
                from (
                    select r.radi_nume_radi, r.radi_nume_text
                        , r.radi_asunto, r.radi_usua_rem, r.radi_usua_dest, r.radi_usua_redirigido
                        , $fecha_documento as fecha_documento, radi_nume_temp, r.radi_imagen, r.radi_fech_firma
                    from radicado r
                    where radi_inst_actu=".$_SESSION["inst_codi"] ." $busq_radicados_tmp $where_fecha_documento
                        and ((radi_nume_temp::text like '%0' and esta_codi in (2,5,6,9,10) and radi_fech_firma is null) or (radi_nume_radi::text like '%2'))
                ) as b
                left outer join radicado rp on b.radi_nume_temp = rp.radi_nume_radi
                where b.radi_nume_radi::text not like '%1' 
                    or rp.radi_fech_firma is not null
                    or rp.radi_inst_actu!=".$_SESSION["inst_codi"] ."
                order by " . ($orderNo+1) . " $orderTipo *LIMIT**OFFSET*
            ) as r
            order by ".($orderNo+1)." $orderTipo";

        $query3 = "SELECT --Anexar Documentos (cargar doc. dig.)
            radi_nume_radi as \"CHR_DATO\"
            , '<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
            , 'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
            , case when radi_fech_firma is not null then '<img src=\"$ruta_raiz/imagenes/key-yellow.png\" border=0 style=\"width:10px; height: 10px;\">' else '' end as \"  \"
            , radi_nume_text as \"Número $descRadicado\"
            , substr(fecha_documento::text, 1,19) || '$descZonaHoraria' as \"DAT_Fecha $descRadicado\"
            , radi_nume_radi as \"HID_RADI_NUME_RADI\"
            , radi_asunto as \"Asunto\"
            , ver_usuarios(radi_usua_rem,',<br>') as \"De\"
            , ver_usuarios(radi_usua_dest,',<br>') as \"Para\"
            from (
                select b.radi_nume_radi, 1, 2
                , b.radi_fech_firma, b.radi_nume_text, b.fecha_documento, 3
                , b.radi_asunto, b.radi_usua_rem, b.radi_usua_dest, b.radi_usua_redirigido
                from (
                    select r.radi_nume_radi, r.radi_nume_text
                        , r.radi_asunto, r.radi_usua_rem, r.radi_usua_dest, r.radi_usua_redirigido
                        , $fecha_documento as fecha_documento, radi_nume_temp, r.radi_imagen, r.radi_fech_firma
                    from radicado r
                    where radi_inst_actu=".$_SESSION["inst_codi"] ." $busq_radicados_tmp $where_fecha_documento
                        and ((radi_nume_temp::text like '%0' and esta_codi in (2,5,6,9,10)) or (radi_nume_radi::text like '%2'))
                ) as b
                left outer join radicado rp on b.radi_nume_temp = rp.radi_nume_radi
                where b.radi_nume_radi::text not like '%1' or rp.radi_inst_actu!=".$_SESSION["inst_codi"] ."
                order by " . ($orderNo+1) . " $orderTipo *LIMIT**OFFSET*
            ) as r
            order by ".($orderNo+1)." $orderTipo";

//    echo $query;
    //Imprimir Comprobantes
    if (isset ($orderNo2)) $orderNo=4;
    $sqlFecha = "substr(radi_fech_radi::text, 1,19)";
    $query1 = "select --Imprimir Comprobantes
                radi_nume_radi as \"CHR_Dato\"
                ,'<img src=\"$ruta_raiz/iconos/popup.png\" border=0>' as \"SCR_ \"
                ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\",\"'||$carpeta||'\")' as \"HID_POPUP\"
                ,radi_nume_text as \"No. $descRadicado\"
                ,$sqlFecha || '$descZonaHoraria' as \"DAT_Fecha $descRadicado\"
                ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                ,radi_asunto as \"Asunto\"
                ,ver_usuarios(radi_usua_rem,',<br>') as \"De\"
                ,ver_usuarios(radi_usua_dest,',<br>') as \"Para\"
                from (select radi_nume_radi,2,3,radi_nume_text,radi_fech_radi,6,radi_asunto,radi_usua_rem,radi_usua_dest
                    from radicado where radi_nume_radi::text like '%2' $where_fecha_documento
                and radi_inst_actu=".$_SESSION["inst_codi"].
                $busq_radicados_tmp .
              " order by " . ($orderNo+1) . " $orderTipo *LIMIT**OFFSET*) as r
                order by ".($orderNo+1)." $orderTipo";

    //uploadTx.php
    $query2 = "SELECT
                RADI_NUME_TEXT as \"Numero $descRadicado\",
                substr($fecha_documento::text, 1,19) || '$descZonaHoraria' as \"DAT_Fecha $descRadicado\",
                RADI_NUME_RADI as \"HID_RADI_NUME_RADI\",
                RADI_ASUNTO as \"Asunto\"
                FROM RADICADO
                WHERE radi_inst_actu=".$_SESSION["inst_codi"]."
                $busq_radicados_tmp order by 2 ";
    break;
}
?>
