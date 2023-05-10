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
$fechaActual = date('Y-m-d');

    if($_SESSION["usua_codi"]!=0 or $_SESSION["admin_institucion"]!=1) 
        $inst_codi = $_SESSION["inst_codi"];    
switch($db->driver)	{
    case 'postgres':
        switch ($tipoReporte){
        
        case "utilSistema":
           
            $sql="select u.usua_cedula as \"Cédula/Pasaporte\"
                 , u.usua_nombre as \"Nombre\",u.usua_cargo as \"Actividad\"
                ,u.usua_email as \"Correo\",u.depe_nomb as \"Área\"
                ,'$fechaActual'::date - coalesce(substr(g.usua_fech_sesion::text,1,10), '2010-01-01')::date as \"dias transcurridos de uso\"
                ,case when '$fechaActual'::date - coalesce(substr(g.usua_fech_sesion::text,1,10), '2010-01-01')::date >=1498 then 
                'No registra ingreso al Sistema'
                    else 
                substr(g.usua_fech_sesion::text,1,10)
                end as \"Último ingreso al Sistema\"
                from (select ur.*,us.usua_fecha_actualiza from usuario ur
                inner join usuarios us on us.usua_codi = ur.usua_codi
                where ur.usua_esta<>0 and upper(ur.usua_login) not like 'UADM%' 
                and ur.usua_codi>0 and ur.tipo_usuario=1 and ur.inst_codi=$inst_codi";
                    if ($dependencia>0)
                    $sql.=" and ur.depe_codi = $dependencia"; 
                     
                $sql.=")as u left outer join usuarios_sesion g on u.usua_codi=g.usua_codi";
                 if ($orderNo == '') $orderNo=0;   
                         $sql .= " order by ".($orderNo+1)." $orderTipo ";  
                
         break;
        case "utilSistemaReporte":
           
            $sql="select u.usua_cedula as \"Cédula/Pasaporte\"
                 , u.usua_nombre as \"Nombre\",u.usua_cargo as \"Actividad\"
                ,u.usua_email as \"Correo\",u.depe_nomb as \"Área\"
                ,'$fechaActual'::date - coalesce(substr(g.usua_fech_sesion::text,1,10), '2010-01-01')::date as \"dias transcurridos de uso\"
                ,case when '$fechaActual'::date - coalesce(substr(g.usua_fech_sesion::text,1,10), '2010-01-01')::date >=1498 then 
                'No registra ingreso al Sistema'
                    else 
                substr(g.usua_fech_sesion::text,1,10)
                end as \"Último ingreso al Sistema\"
                from (select ur.*,us.usua_fecha_actualiza from usuario ur
                inner join usuarios us on us.usua_codi = ur.usua_codi
                where ur.usua_esta<>0 and upper(ur.usua_login) not like 'UADM%' 
                and ur.usua_codi>0 and ur.tipo_usuario=1 and ur.inst_codi=$inst_codi";
                    if ($dependencia>0)
                    $sql.=" and ur.depe_codi = $dependencia"; 
                     
                $sql.=")as u left outer join usuarios_sesion g on u.usua_codi=g.usua_codi";
//                 if ($orderNo == '') $orderNo=0;   
                         $sql .= " order by 5,2 ";  
                //echo $sql;
         break;
        case 'usuariosxArea':
            $sql="select u.usua_ciudad as \"Ciudad\" ,u.depe_nomb as \"Área\""
                . ", count(depe_nomb) as \"Cantidad de Usuarios\" from usuario u 
            where inst_codi = $inst_codi and u.depe_nomb <>''";
                if ($estado!=2) $sql .= " and u.usua_esta=$estado";
            $sql.=" group by u.depe_nomb,u.usua_ciudad";// order by u.usua_ciudad ,u.depe_nomb";
             if ($orderNo == '') $orderNo=0;   
             $sql .= " order by ".($orderNo+1)." $orderTipo ";
                           
            break;
  
           
        }
           

        
    //echo $sql;
        break;
}
?>
