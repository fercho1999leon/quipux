<?php
include_once "$ruta_raiz/funciones.php";
    switch($db->driver)	{
	case 'postgres': 

// Verificar si existen ciudadanos con nombres similares o la misma cédula
    $where = buscar_2campos($buscar_nombre, "usua_nombre", "usua_cedula");

    //Se podrá combinar unicamente usuarios sin firma
    $sql = "select usua_cedula as \"Cédula\",
            usua_nombre as \"Nombre\",
            usua_titulo as \"Título\",
            usua_cargo as \"Cargo\",
            inst_nombre as \"Institución\",
            usua_email as \"Correo Electrónico\",
            'Seleccionar' as \"SCR_Usuario a Desactivar\",
            'usr_origen(\"'||usua_codi||'\");' as \"HID_FUNCION1\",
            'Seleccionar' as \"SCR_Usuario Final\",
            'usr_destino(\"'||usua_codi||'\",\"'||usua_cedula||'\");' as \"HID_FUNCION2\"
            from usuario where $where and inst_codi=0 and usua_esta=1 and tipo_usuario=2 
            order by 1 ";
             
   
            break;
    }
 ?>