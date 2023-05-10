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
*
*
*	Modificado por		Iniciales		Fecha (dd/mm/aaaa)
*       David Gamboa            DG                      04-25-2012
*
*
*	Comentado por		Iniciales		Fecha (dd/mm/aaaa)
*	Sylvia Velasco		SV			02-12-2008
**/

/**
* Consultar datos de ciudadano dependiendo del nombre para busqueda, realiza conversion del nombre a mayúsculas,
* reemplaza letras con tilde por letras sin tilde, las conversiones se realizan para obtener todos los datos
* esperados.
**/


include_once "$ruta_raiz/funciones.php";
include_once "$ruta_raiz/Administracion/ciudadanos/util_ciudadano.php";
$ciud = New Ciudadano($db);
$queryLimit = 200;
    switch($db->driver)	{
	case 'postgres':
            switch ($tipo_query){            
            case 1://adm_ciudadano_confirmar.php
        $buscar_nom = trim(strtoupper($buscar_nom));
                if ($orderNo=='') $orderNo=4;
                $sql = "select --Confirmar Datos
                        ciu_cedula as \"Cédula\"
                        ,ciu_nombre||' '||ciu_apellido as \"Nombre\"
                        ,ciu_titulo as \"Titulo\"
                        ,ciu_cargo as \"Cargo\"
                        ,ciu_empresa as \"Institución\"
                        ,ciu_email as \"Correo Electrónico\"
                        ,'Editar' as \"SCR_Acción\"
                        ,'comparar_ciudadanos(\"'||ciu_codigo||'\",\"'||ciu_cedula||'\");' as \"HID_FUNCION\"
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
                break;
            case 2://cuerpoUsuario_ext.php
        
                $buscar_nom = trim(strtoupper($buscar_nom));
                if ($orderNo=='') $orderNo=5;
                  $sql = "select --Buscar Ciudadanos
                         case when trim(s.usua_nombre)='' then 'S/N' 
                         else s.usua_nombre end AS \"SCR_Nombre\"
                         ,'seleccionar_usuario(\"'|| s.usua_codi ||'\");' as \"HID_FUNCION\" 
                         ,s.usua_cedula AS \"Cédula\",s.usua_email AS \"Email\"
                         ,s.usua_cargo AS \"".$descCargo."\"
                         , s.inst_nombre AS \"".$descEmpresa."\"
                         ,case s.usua_esta when 1 then 'Activo' else 'Inactivo' end as \"Estado\"
                         from 
                         (
                              select usua_codi,usua_nombre,usua_cedula,usua_email,usua_cargo,inst_nombre
                             ,usua_esta from usuario where inst_codi=0";
                             if (trim($opc)=="a")//activos
                            $sql.=" and usua_esta=1";
                            elseif(trim($opc)=="b")//inactivos
                                $sql.=" and usua_esta=0";
                            //excluyo usuarios sin datos correctos
                            $sql.=" and usua_codi>0 and tipo_usuario=2 and usua_nombre <> '- -' 
                                and usua_nombre <> ', ,' and usua_nombre <> '. .'";
                            if ($ciud->esNumeroTxt($buscar_nom)==1)
                                $sql.=" and (" . buscar_cadena($buscar_nom,'usua_cedula').")";           
                            elseif ($buscar_nom!="") {
                                if ($ciud->esEmail($buscar_nom)==1)//si es mail
                                    $sql .= " and (" . buscar_cadena($buscar_nom,'usua_email').")";
                                    else{
                                        $sql .=  ' and (' . buscar_cadena($buscar_nom,'usua_nombre');
                                        $sql .= " or ((" . buscar_cadena($buscar_nom,'inst_nombre').")";

                                        $sql .= " or (" . buscar_cadena($buscar_nom,'usua_cargo')."))) ";
                                    }            
                            }
                            $sql.=" order by ".($orderNo+1)." $orderTipo  limit $queryLimit offset 0";
                  $sql .= ") as s";
                 
                break;
            case 3://adm_usuario_ext_combinar.php
                if ($buscar_nom!=''){
                    if ($ciud->esNumeroTxt($buscar_nom)==1)
                        $where.=" and (" . buscar_cadena($buscar_nom,'usua_cedula').")";
                    else
                        $where.= " and ".buscar_cadena($buscar_nom, "usua_nombre");
                }
                //Se podrá combinar unicamente usuarios sin firma
                if ($orderNo=='') $orderNo=6;
                if ($orderNo>6) $orderNo=6;
                $sql = "select --Combinar Usuarioss
                        cb.usua_cedula as \"Cédula\"
                        ,cb.usua_nombre as \"Nombre\"
                        , cb.usua_titulo as \"Título\"
                        ,cb.usua_cargo as \"Cargo\"
                        ,cb.inst_nombre as \"Institución\"
                        ,cb.usua_email as \"Correo Electrónico\"
                        ,'Seleccionar' as \"SCR_Usuario a Desactivar\"
                        ,'usr_origen(\"'||cb.usua_codi||'\");' as \"HID_FUNCION1\"
                        ,'Seleccionar' as \"SCR_Usuario Final\"
                        ,'usr_destino(\"'||cb.usua_codi||'\",\"'||cb.usua_cedula||'\");' as \"HID_FUNCION2\"
                        from (
                            select usua_codi
                            ,usua_cedula ,usua_nombre,
                            usua_titulo ,usua_cargo,
                            inst_nombre ,usua_email                             
                            from usuario where inst_codi=0 and usua_esta=1 and tipo_usuario=2 
                            $where 
                            order by ".($orderNo+1)." $orderTipo  limit $queryLimit offset 0
                        ) as cb 
                        ";
                
                break;
            case 4://cuerpoSolicitud_ext.php
                $buscar_nom = trim(strtoupper($buscar_nom));
                //$orderNo="";
                if ($orderNo=='') $orderNo=4;
                if ($orderNo>4) $orderNo=4;
                //echo $orderNo;
                $sql= "select --Solicitud Externos
                               cb.ciu_nombre AS \"SCR_Nombre\"
                               ,'seleccionar_ciudadano(\"'||cb.ciu_codigo||'\",\"'||cb.ciu_cedula||'\");' as \"HID_FUNCION\"
                               ,cb.ciu_cedula AS \"Cédula\" ,cb.ciu_email AS \"Email\"
                               ,case (select sol_estado from solicitud_firma_ciudadano f 
                               where f.ciu_codigo = cb.ciu_codigo) when 0 then 'Rechazado'
                               when 1 then 'En Edición' when 2 then 'Enviado' when 3 then 'Autorizado'
                               else 'No existe estado' end AS \"Estado\" 
               from (
                    select ciu_nombre, ciu_codigo,ciu_cedula,ciu_email,sol_estado from 
               datos_solicitud as s
                               where ";
                            if($opc == a)//rechazado
                            $sql .=  ' sol_estado = 0';
                            else if($opc == c)//Enviado
                            $sql .=  ' sol_estado = 2';
                            else if($opc == d)//autorizado
                            $sql .=  ' sol_estado = 3';
                            else if($opc == e)//todos
                            $sql .=  ' (sol_estado = 0 or sol_estado = 2 or sol_estado = 3)';
      
                            if ($buscar_nom!="") 
                            $sql .=  ' and ' . buscar_nombre_cedula_solicitud($buscar_nom);

	    $sql .= " order by ".($orderNo+1)." $orderTipo  limit $queryLimit offset 0) as cb";
                 //echo $sql;      
break;
            
            default:
                break;
}
break;

}

//funciones necesarias
function nombre_cedula_tmp($cadena,$ciud, $buscarInstitucion = 'N') {
    $filtro = '(';
     if ($ciud->esNumeroTxt($cadena)==1)
    $filtro.=' (' . cadena_b($cadena, "ciu_cedula") . ')';
     else{
    $filtro.= '(' . cadena_b($cadena, "ciu_nombre") . ') 
        or (' . cadena_b($cadena, "ciu_apellido") . ')';
     if($buscarInstitucion == 'S')
        $filtro .= ' or (' . buscar_cadena($cadena, "ciu_empresa") . ')';
     }
    $filtro .= ')';
    return $filtro;
}
function cadena_b($cadena, $campo) {
    $resp = "";
    $cadena = limpiar_sql($cadena);
   
    $arr_buscar = explode(" ", $cadena);
    $glue = '';
    foreach ($arr_buscar as $tmp) {
        if ($tmp != "" && strlen($tmp)>=3) {
            $resp .= " $glue translate(UPPER($campo),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN')
		      LIKE translate(upper('%" . trim($tmp) . "%'),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') ";
            $glue = 'or';
        }
    }
    $resp = (empty($resp)) ? 'true' : $resp;
    return $resp;
}
?>
