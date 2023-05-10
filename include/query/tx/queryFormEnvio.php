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

/**
*	Autor			Iniciales		Fecha (dd/mm/aaaa)
*	Mauricio Haro		MH
*
*	Modificado por		Iniciales		Fecha (dd/mm/aaaa)
*	Sylvia Velasco		SV			13-01-2009
*
*	Comentado por		Iniciales		Fecha (dd/mm/aaaa)
*	Sylvia Velasco		SV			14-01-2009
**/

/**
* Obtiene la lista de los documentos que van a ser enviados ya sea electronica o manualmente.
**/

switch($db->driver)
{
    case 'postgres':
    {
        $orderNo = 4;
        $orderTipo = "desc";
        $fecha_documento = "(case when radi_nume_temp::text like '%0' then radi_fech_ofic else radi_fech_radi end)";
        $isql = "select b.RADI_NUME_RADI AS \"CHK_CHKANULAR\"
                ,b.RADI_ASUNTO  as \"Asunto\"
                ,ver_usuarios(radi_usua_rem,',<br>') AS \"De\"
                ,ver_usuarios(radi_usua_dest,',<br>') AS \"Para\"
                ,substr($fecha_documento::text, 1,19) || '$descZonaHoraria' as \"DAT_Fecha $descRadicado\"
                ,b.RADI_NUME_RADI as \"HID_RADI_NUME_RADI\"
                ,b.RADI_NUME_TEXT as \"Número Documento\"
                ,e.esta_desc as \"Estado\"
                from radicado b left outer join estado e on b.esta_codi=e.esta_codi
                where b.radi_nume_radi in ($whereFiltro)
                order by ".($orderNo+1)." $orderTipo";
        break;
    }
}
?>