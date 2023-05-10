<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once "$ruta_raiz/funciones.php";
include_once "$ruta_raiz/Administracion/ciudadanos/util_ciudadano.php";
$ciud = New Ciudadano($db);
    switch($db->driver)	{
	case 'postgres': 
            $buscar_nom = trim(strtoupper($buscar_nom));            
            $sql = "select ciu_cedula as \"Cédula\",
                    ciu_nombre||' '||ciu_apellido as \"Nombre\",
                    ciu_titulo as \"Titulo\",
                    ciu_cargo as \"Cargo\",
                    ciu_empresa as \"Institución\",
                    ciu_email as \"Correo Electrónico\",
                    'Editar' as \"SCR_Acción\",
                    'comparar_ciudadanos(\"'||ciu_codigo||'\",\"'||ciu_cedula||'\");' as \"HID_FUNCION\"
                    from ciudadano_tmp
                    where ciu_estado=1";
             if ($buscar_nom!="") {                 
                 if ($ciud->esNumeroTxt($buscar_nom)==1)
                     $sql .= " and " . buscar_cadena($buscar_nom,'ciu_cedula')."";
                 else{                  
                $sql .=  ' and (' . nombre_cedula_tmp($buscar_nom,$ciud,'S');
                $sql .= " or ((" . buscar_cadena($buscar_nom,'ciu_email').")                                     
                    or (" . buscar_cadena($buscar_nom,'ciu_cargo')."))) ";		                
                    }
             }
             $sql .= " order by ".($orderNo+1)." $orderTipo";
             //echo $sql;
            break;
}

?>
