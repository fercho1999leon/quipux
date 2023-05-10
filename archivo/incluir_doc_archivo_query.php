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
/*****************************************************************************************
**											**
*****************************************************************************************/

$ruta_raiz = "..";
if (!$db->driver){ $db = $this->db; }	//Esto sirve para cuando se llama este archivo dentro de clases donde no se conoce $db.

switch($db->driver)
{
    case 'postgres':
        
        $sql = "select coalesce(dep_central,depe_codi) as archivo from dependencia where depe_codi=".$_SESSION['depe_codi']."";
        $rs=$db->conn->query($sql);
        $depe_archivo = $rs->fields["ARCHIVO"];

        $sqlFecha = "substr(radi_fech_ofic::text,1,19)";

    	$isql = "select --Archivo Fisico
                    radi_nume_radi||'-1' as \"CHK_ARCHIVO\"
                    ,case when arch_codi is not null then 'Si' end as \"SCR_Archivado\"
                    ,'VerUbicacion(\"'|| arch_codi || '\",\"'||radi_nume_text || '\",\"\");' as \"HID_FUNCION\"
                    , case when radi_nume_radi::text like '%1' then '&nbsp;&nbsp;<b>&rarr;</b>&nbsp;' else '' end || radi_nume_text as \"No. Documento\"
                    ,$sqlFecha || '$descZonaHoraria' as \"DAT_Fecha\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,radi_asunto  as \"Asunto\"
                    ,ver_usuarios(radi_usua_rem,',<br>') AS \"De\"
                    ,ver_usuarios(radi_usua_dest,',<br>') AS \"Para\"
                    ,depe_nomb as  \"$descDependencia\"
                    ,trad_descr  as \"Tipo Documento\"
                    ,num_anexos  as \"No. Anexos\"

                from (
                    select 1, afr.arch_codi, 2, r.radi_nume_text, r.radi_fech_ofic, r.radi_nume_radi, r.radi_asunto , r.radi_usua_rem, r.radi_usua_dest
                        , d.depe_nomb, t.trad_descr, count(a.anex_radi_nume) as num_anexos
                    from (
                            select radi_nume_radi, radi_nume_temp, radi_fech_ofic, radi_fech_radi,radi_tipo
                                , radi_nume_text, radi_asunto, radi_usua_rem, radi_usua_dest,
                                case when radi_nume_temp::text like '%0' then replace(radi_usua_rem,'-','') else case when radi_nume_radi::text like '%1' then replace(radi_usua_dest,'-','') else radi_usua_radi::text end end as radi_usua_codi
                            from radicado
                            where (upper(radi_nume_text) like upper('%$txt_radi_nume%') or radi_nume_radi::text like '$txt_radi_nume')
                                and ((radi_nume_radi::text like '%1' and esta_codi in (0,2)) or (radi_nume_radi::text not like '%1' and esta_codi in (0,6)))
                                and radi_inst_actu=".$_SESSION["inst_codi"]."
                    ) as r
                        left outer join usuarios u on u.usua_codi=r.radi_usua_codi::integer
                        left outer join dependencia d on u.depe_codi=d.depe_codi
                        left outer join tiporad t on t.trad_codigo=r.radi_tipo
                        left outer join anexos a on (a.anex_radi_nume=r.radi_nume_radi or a.anex_radi_nume=r.radi_nume_temp) and a.anex_borrado='N'
                        left outer join archivo_radicado afr on r.radi_nume_radi=afr.radi_nume_radi and afr.depe_codi=$depe_archivo

                    group by 1,2,3,4,5,6,7,8,9,10,11, r.radi_fech_radi
                    order by ".($orderNo+1)." $orderTipo, r.radi_fech_radi asc *LIMIT**OFFSET*
                ) as b";

//echo "<br>$isql<br>";
	break;
}
?>
