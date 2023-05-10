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

switch($db->driver)	{
    case 'postgres':
        if ($orderNo == '') $orderNo=0;        
        $nombre = trim(strtoupper($nombre));
        $sql = "select u.usua_nombre AS \"SCR_Nombre\"            
            ,'seleccionar_usuario(\"'|| u.usua_codi ||'\");' as \"HID_FUNCION\"";
           /* ,  case when usua_subrogado<>1 then 'Subrogante' else '' 
                    end as \"Subrogación\"";*/
        //Subrogacion
        $sql.= ", case when cargo_tipo = 1 then 'Jefe' else 'Normal' end  as \"Perfil\"
                , case when u.usua_codi
                          in (select usua_subrogado from usuarios_subrogacion 
                          where usua_visible=1) = true then ' (Subrogado)' else '' end 
                          || case when u.usua_codi
                          in (select usua_subrogante from usuarios_subrogacion 
                          where usua_visible=1) = true then ' (Subrogante)' else '' end 
                          AS \"Subrogación\"";
        $sql.=", u.usua_email AS \"Email\"
            , u.depe_nomb AS \"Área\"
            , u.usua_cargo AS \"Puesto\"
            , u.usua_cargo_cabecera AS \"Puesto Cabecera\"
            , case when u.usua_esta = 1 then 'Activo' else 'Inactivo' end AS  \"Estado\"";
        if ($_SESSION["usua_codi"]==0) $sql .= ", u.inst_nombre as \"Institución\"";
       

        $sql .= " from usuario u";
        if ($permiso!="0") $sql .= " left outer join permiso_usuario p on u.usua_codi=p.usua_codi and p.id_permiso=$permiso";

        $sql .= " where u.inst_codi>0 and u.usua_codi>0 and visible_sub=1 and u.inst_codi=".$_SESSION["inst_codi"];

        if ($estado!=2) $sql .= " and usua_esta=$estado";
        if ($nombre != ""){
            $sql .= buscar_datos_usuario($nombre);
//            $sql .= ' and (' . buscar_nombre_cedula($nombre);
//            $sql .= " or ((" . buscar_cadena($nombre,'usua_email').")
//                or (" . buscar_cadena($nombre,'usua_cargo_cabecera').")
//                    or (" . buscar_cadena($nombre,'usua_cargo')."))) ";
//
        }
        
        if ($dependencia > 0) {
            $sql .= " and u.depe_codi=$dependencia";
        } else {
            if ($depe_codi_admin!=0)
                $sql.= " and u.depe_codi in ($depe_codi_admin)";
        }
       
        if ($permiso!=0) $sql .= " and p.id_permiso is not null";
        
       
        if ($perfil!=2) $sql .= " and cargo_tipo=$perfil";        
        $sql .= " order by 7";
//echo $sql;
        break;
}
?>
