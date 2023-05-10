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

$ruta_raiz = "..";
$inst_codi = 422;
$dateconcatenarinicio = "";
$dateconcatenarfin = "";
$dateconcatenarinicio = " 00:00:00";
$dateconcatenarfin = " 23:59:59.999999";
if (!$db->driver){ $db = $this->db; }	//Esto sirve para cuando se llama este archivo dentro de clases donde no se conoce $db.

switch($db->driver)
{
    case 'postgres':                
        $where = "";
        $txt_fecha_desde = $txt_fecha_desde."".$dateconcatenarinicio;
        $txt_fecha_hasta = $txt_fecha_hasta."".$dateconcatenarfin;
        if (trim($txt_nume_documento) != ""){
            if (strlen($txt_nume_documento)>=4)
                $where .= " and radi_nume_text = upper('%".trim(limpiar_sql($txt_nume_documento))."%')";
//              $where .= " and upper(radi_nume_text) like upper('%".trim(limpiar_sql($txt_nume_documento))."%')";
        }
        if (trim($txt_nume_referencia) != ""){
             if (strlen($txt_nume_referencia)>=4)
            $where .= " and upper(radi_cuentai) like upper('%".trim(limpiar_sql($txt_nume_referencia))."%')";
        }
        //REMITENTE
        if(trim($txt_usua_remitente) != "") {
            $where .= " and r1.radi_nume_temp in (select radi_nume_radi from usuarios_radicado where radi_usua_tipo=1 ";
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

//        if(trim($txt_usua_remitente) != "") {
//             if (strlen($txt_usua_remitente)>=4){
//                $where .= " and radi_nume_temp in (select radi_nume_radi from usuarios_radicado where radi_usua_tipo=1 and inst_codi = ".$_SESSION["inst_codi"];
//                $tmp1 = explode(" ",limpiar_sql($txt_usua_remitente));
//                foreach($tmp1 as $tmp) {
//                    if(trim($tmp) != "")
//                        $where .= " and " . buscar_2campos($tmp,"coalesce(usua_nombre,'')||' '||coalesce(usua_apellido,'')","usua_institucion");
//                }
//                $where .= ")";
//             }
//        }
        //DESTINATARIO
        if(trim($txt_usua_destinatario) != "") {
            $where .= " and r1.radi_nume_temp in (select radi_nume_radi from usuarios_radicado where radi_usua_tipo in (2,3) ";
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
//        if(trim($txt_usua_destinatario) != "") {
//            if (strlen($txt_usua_destinatario)>=4){
//                $where .= " and radi_nume_temp in (select radi_nume_radi from usuarios_radicado where radi_usua_tipo in (2,3) and inst_codi = ".$_SESSION["inst_codi"];
//                $tmp1 = explode(" ",limpiar_sql($txt_usua_destinatario));
//                foreach($tmp1 as $tmp) {
//                    if(trim($tmp) != "")
//                        $where .= " and " . buscar_2campos($tmp,"coalesce(usua_nombre,'')||' '||coalesce(usua_apellido,'')","usua_institucion");
//                }
//                $where .= ")";
//            }
//        }
        //ASUNTO O NOTA
        //echo $txt_texto;
        if (trim($txt_texto) != "") {
             if (strlen($txt_texto)>=4)
            $where .= " and ((" . buscar_cadena($txt_texto,'r1.radi_asunto').") or (" . buscar_cadena($txt_texto,'r1.radi_resumen').")) ";
        }
        //DEPENDENCIAS
        if ($txt_depe_codi != "0" or $txt_usua_codi != "0") {               
                $usr = "";
                if ($txt_usua_codi == "0") {
                    $sql = "select usua_codi from usuarios where depe_codi=$txt_depe_codi";
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
                    $where .= " and r1.radi_nume_radi in (select radi_nume_radi from hist_eventos where usua_codi_ori in ($usr) or usua_codi_dest in ($usr))";
                }
            }
       
        $isql = "select -- Busqueda Avanzada Tramites - USR ".$_SESSION["usua_codi"]." - ".date("Y-m-d H:i:s")."<br>
        radi_nume_text as \"No. Documento\"
        ,substr(radi_fech_ofic::text,1,19) || '$descZonaHoraria' as \"SCR_Fecha Documento\"
        ,'mostrar_documento(\"'||radi_nume_radi||'\",\"'||radi_nume_text||'\")' as \"HID_RADI_NUME_RADI\"
        ,radi_cuentai as \"No. de Referencia\"
        ,ver_usuarios(radi_usua_rem,',<br>') AS \"De\"
        ,ver_usuarios(radi_usua_dest,',<br>') AS \"Para\"
        ,radi_asunto  as \"Asunto\"
        ,resp1_radi_nume_text as \"No. Respuesta\"
        ,substr(resp1_radi_fech_ofic::text,1,19) || '$descZonaHoraria' as \"SCR_Fecha Respuesta\"
        ,'mostrar_documento(\"'||resp1_radi_nume_radi||'\",\"'||resp1_radi_nume_text||'\")' as \"HID_RESP1_RADI_NUME_RADI\"
        ,ver_usuarios(resp1_radi_usua_rem,',<br>') AS \"De\"
        ,ver_usuarios(resp1_radi_usua_dest,',<br>') AS \"Para\"
        ,resp1_radi_asunto  as \"Asunto Respuesta\"
        ,case when resp1_esta_codi is not null then (case when resp1_esta_codi=6 then 'Manual' else (case when resp1_esta_codi in (0,2) then 'Electr&oacute;nico' else 'Pendiente' end) end) end as \"Tipo Envío\"
        ,resp2_radi_nume_text as \"No. Respuesta\"
        ,substr(resp2_radi_fech_ofic::text,1,19) || '$descZonaHoraria' as \"SCR_Fecha Respuesta\"
        ,'mostrar_documento(\"'||resp2_radi_nume_radi||'\",\"'||resp2_radi_nume_text||'\")' as \"HID_RESP2_RADI_NUME_RADI\"
        ,ver_usuarios(resp2_radi_usua_rem,',<br>') AS \"De\"
        ,ver_usuarios(resp2_radi_usua_dest,',<br>') AS \"Para\"
        ,resp2_radi_asunto  as \"Asunto Respuesta\"
        from (
            select r.radi_nume_text, r.radi_fech_ofic, r.radi_nume_radi, r.radi_cuentai, r.radi_usua_rem, r.radi_usua_dest, r.radi_asunto
            , rr1.radi_nume_text as resp1_radi_nume_text
            , rr1.radi_fech_ofic as resp1_radi_fech_ofic
            , rr1.radi_nume_radi as resp1_radi_nume_radi
            , rr1.radi_usua_rem  as resp1_radi_usua_rem
            , rr1.radi_usua_dest as resp1_radi_usua_dest
            , rr1.radi_asunto as resp1_radi_asunto
            , '1' as tmp1
            , rr2.radi_nume_text as resp2_radi_nume_text
            , rr2.radi_fech_ofic as resp2_radi_fech_ofic
            , rr2.radi_nume_radi as resp2_radi_nume_radi
            , rr2.radi_usua_rem  as resp2_radi_usua_rem
            , rr2.radi_usua_dest as resp2_radi_usua_dest
            , rr2.radi_asunto as resp2_radi_asunto
            , rr1.esta_codi as resp1_esta_codi
            from (
                select r1.radi_nume_radi, r1.radi_nume_text, r1.radi_cuentai, r1.radi_fech_ofic, r1.radi_asunto
                    , r1.radi_usua_rem, r1.radi_usua_dest
                from radicado r1
                where r1.radi_inst_actu=" . $_SESSION["inst_codi"] . "";
        //$isql.="and r1.radi_nume_radi::text like '%1'";
//        if ($_SESSION["inst_codi"]!=$inst_codi)
//            $isql.=" and r1.radi_nume_radi::text like '%1' ";
//           else
            $isql.=" and mod(r1.radi_nume_radi,10) = 1";
           
           $isql.=" and esta_codi in (0,2) $where ";
        $isql.=" and r1.radi_fech_ofic >= '$txt_fecha_desde'";
        $isql.=" and r1.radi_fech_ofic <= '$txt_fecha_hasta'";
        
//        $isql.=" and esta_codi in (0,2) $where
//                    and r1.radi_fech_ofic::date >= '$txt_fecha_desde'::date
//                    and r1.radi_fech_ofic::date <= '$txt_fecha_hasta'::date";
                    
                    
           $isql.=" ) as r
            left outer join hist_eventos h1 on r.radi_nume_radi=h1.radi_nume_radi and h1.sgd_ttr_codigo in (12)
            left outer join radicado rr1 on (rr1.radi_nume_temp=coalesce(h1.hist_referencia::numeric,0) and rr1.radi_nume_radi::text like '%1' and rr1.esta_codi in (0,2,4,5,6)) or (rr1.radi_nume_radi=coalesce(h1.hist_referencia::numeric,0) and rr1.esta_codi in (1))
            left outer join hist_eventos h2 on rr1.radi_nume_radi=h2.radi_nume_radi and h2.sgd_ttr_codigo in (12)
            left outer join radicado rr2 on (rr2.radi_nume_temp=coalesce(h2.hist_referencia::numeric,0) and rr2.radi_nume_radi::text like '%1' and rr2.esta_codi in (0,2,4,5,6)) or (rr2.radi_nume_radi=coalesce(h2.hist_referencia::numeric,0) and rr2.esta_codi in (1))
            order by " . ($orderNo+1) . " $orderTipo *LIMIT**OFFSET*
        ) as a";
        

//            echo "<br>".$isql."<hr>";
//            die();

	break;
}
?>
