<?php
include_once "$ruta_raiz/funciones.php";

include_once "$ruta_raiz/Administracion/ciudadanos/util_ciudadano.php";
$ciud = New Ciudadano($db);
    switch($db->driver)	{
	case 'postgres':       
        $buscar_nom = trim(strtoupper($buscar_nom));        
        $sql= "select case when trim(usua_nombre)='' then 'S/N' else usua_nombre end AS \"SCR_Nombre\"
                               ,'seleccionar_usuario(\"'|| usua_codi ||'\");' as \"HID_FUNCION\"
                               , usua_cedula AS \"Cédula\"
                                        ,usua_email AS \"Email\"
                                        , usua_cargo AS \"".$descCargo."\"
                                        , inst_nombre AS \"".$descEmpresa."\"
                                        ,case usua_esta
                                         when 1 then 'Activo'
                                         else 'Inactivo'
                                         end as \"Estado\"
                               from usuario
                               where inst_codi=0";
        if (trim($opc)=="a")//activos
            $sql.=" and usua_esta=1";
        elseif(trim($opc)=="b")
            $sql.=" and usua_esta=0";
        $sql.=" and usua_codi>0 and tipo_usuario=2 and usua_nombre <> '- -' 
            and usua_nombre <> ', ,' and usua_nombre <> '. .'";
        if ($ciud->esNumeroTxt($buscar_nom)==1){
            $sql.=" and (" . buscar_cadena($buscar_nom,'usua_cedula').")";           
        }elseif ($buscar_nom!="") {
            if ($ciud->esEmail($buscar_nom)==1)//si es mail
                $sql .= " and (" . buscar_cadena($buscar_nom,'usua_email').")";
                else{
                    $sql .=  ' and (' . buscar_cadena($buscar_nom,'usua_nombre');
                    $sql .= " or ((" . buscar_cadena($buscar_nom,'inst_nombre').")";

                    $sql .= " or (" . buscar_cadena($buscar_nom,'usua_cargo')."))) ";
                }            
            }
            $sql .= " order by ".($orderNo+1)." $orderTipo ";
           
            
            
        //echo '<font size=1>'.$sql.'</font>';
        //die();
break;
}

?>