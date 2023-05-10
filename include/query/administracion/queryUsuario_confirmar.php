<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once "$ruta_raiz/funciones.php";
    switch($db->driver)	{
	case 'postgres':
           $queryLimit=100;
           $buscar_nom = trim(strtoupper($buscar_nom));            
           
            $sql = "select --Crear ciudadanos confirmacion
                    case when u.tipo_usuario = 2 then '<i>(Ciu.)</i>' else '<i>(Serv.)</i>' end as \"SCR_Tipo\",
                    u.usua_cedula as \"Cédula\",
                    u.usua_nomb||' '||usua_apellido as \"Nombre\",
                    u.usua_titulo as \"Título\",
                    u.usua_cargo as \"Cargo\",
                    u.inst_nombre as \"Institución\",
                    u.usua_email as \"Correo Electrónico\",
                    case when u.tipo_usuario = 2 then 'Editar' else '' end as \"SCR_Acción\",
                    'comparar_ciudadanos(\"'||u.usua_codi||'\");' as \"HID_FUNCION\"
                     from 
                     (
                        select usua_codi,usua_cedula,usua_nomb,usua_apellido,usua_titulo
                        ,usua_cargo,inst_nombre,usua_email,tipo_usuario from usuario where ";
                        if ($b_cedula!='')
                            $sql .= "usua_cedula = '$b_cedula' or ";
                        //otro tratamiento para la busqueda no es posible utilizar las funciones
                       if ($ciu_nombre!='')
                            $sql.= " ( translate(UPPER(usua_nomb),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') 
                                     LIKE translate(upper('%$ciu_nombre%'),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') 
                                     ) ";
                       if ($ciu_apellido!='')
                            $sql.= " or ( translate(UPPER(usua_apellido),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') 
                                     LIKE translate(upper('%$ciu_apellido%'),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') 
                                     )";
                        $sql.= " and usua_esta = 1";
                        $sql.= " order by ".($orderNo+1)." $orderTipo  limit $queryLimit offset 0";
                        $sql.= " ) as u";
             
            break;
}

?>
