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

function obtenerAreas($codInst, $db,$usrCodigo)
{
    $usua_admin = $_SESSION['usua_codi'];
    $admin_areas=pertenece($_SESSION['inst_codi'],$usua_admin,$db,1);//si no administra nada 
    
    
    $sqlDepePadre = "select
                        depe_codi,
                        depe_nomb as depe_nomb,
                        depe_codi_padre
                        , case when depe_codi in 
                        (select depe_codi from usuario_dependencia 
                        where usua_codi = $usrCodigo)= true
                        then 'SI' else 'NO' end as existe_dep
                     from
                        dependencia
                     where
                        depe_estado=1 and inst_codi = ".$codInst.
                        " and depe_codi = coalesce(depe_codi_padre,depe_codi) 
     
     order by depe_nomb";
   
    $rsDepePadre=$db->conn->query($sqlDepePadre);
   
    while(!$rsDepePadre->EOF)
    {
         

        //Contando si la dependencia tiene hijos
        $sqlDepeHijo = "select
                            count(depe_codi) as depe_codi
                        from
                            dependencia
                        where
                            depe_estado=1 and depe_codi_padre = ".$rsDepePadre->fields["DEPE_CODI"].
                            " and depe_codi <> depe_codi_padre";
        
        $rsDepeHijo=$db->conn->query($sqlDepeHijo);
        $opcTipo = "'".$rsDepePadre->fields["EXISTE_DEP"]."'";//permite guardar las hijas, si seleciona el padre
        $cuantos =$rsDepeHijo->fields["DEPE_CODI"];
        if ($cuantos==0)
            $cuantos = '';
        else
        $cuantos = '('.$rsDepeHijo->fields["DEPE_CODI"].')';
        
        $menu_areas .= '<li id="phtml_'.$rsDepePadre->fields["DEPE_CODI"].'" '.graficacheck($rsDepePadre->fields["EXISTE_DEP"],$rsDepePadre->fields["DEPE_CODI"],$db).'><a href="javascript:;">';
       
        $menu_areas .= '<input type="hidden" name="hidd_pd'.$rsDepeHijo->fields["DEPE_CODI"].'" id="hidd_pd'.$rsDepeHijo->fields["DEPE_CODI"].'" value="'.$rsDepeHijo->fields["DEPE_CODI"].'" size="4"/>';
        
        $existe=0;
        $existeAdmin = 0;
        //usuario a dar permisos
        $existe=obtenerCodigos($usrCodigo,$rsDepePadre->fields["DEPE_CODI"],$db);
        //usuario que da permisos
        $existeAdmin=obtenerCodigos($usua_admin,$rsDepePadre->fields["DEPE_CODI"],$db);//pertenece a administrador        
               
        $menu_areas.=graficarCheckDesabled($rsDepePadre->fields["DEPE_CODI"],$rsDepePadre->fields["DEPE_CODI_PADRE"],$admin_areas,$existeAdmin,$opcTipo,$existe);
       
         $menu_areas .= '<font size=1>'.$rsDepePadre->fields["DEPE_NOMB"].'</font>';
         $menu_areas .= "</a> ";
        if($rsDepeHijo->fields["DEPE_CODI"]!='0')
        {
            //Si es diferente de cero consultar los hijos recursiva
            $menu_areas .= "<ul>";
            $menu_areas .= obtenerDependencia($rsDepePadre->fields["DEPE_CODI"], $db,$usrCodigo);
            $menu_areas .= "</ul>";
        }
        $menu_areas .= "</li>";
       
       $rsDepePadre->MoveNext();
       
    }
   
    return $menu_areas;
}
function graficarCheckDesabled($depe_codi,$depe_codi_padre,$admin_areas,$existeAdmin,$opcTipo,$existe){
    
    if ($admin_areas=='')
        $admin_areas=0;
    if ($existeAdmin=='')
        $existeAdmin = 0;
    
    if ($admin_areas==0){//administra areas
            if ($existe==1)//administra el area                
                $menu_areas = '<input type="checkbox" name="'.$depe_codi.'" value="'.$depe_codi.'" onclick="datosArea('.$depe_codi.','.$opcTipo.','.$depe_codi.','.$depe_codi_padre.',this);" checked>';            
            else                
                $menu_areas = '<input type="checkbox" name="'.$depe_codi.'" value="'.$depe_codi.'" onclick="datosArea('.$depe_codi.','.$opcTipo.','.$depe_codi.','.$depe_codi_padre.',this);">';            
        }else{//no administra
            if ($existeAdmin==1){
                if ($existe==1)                
                    $menu_areas = '<input type="checkbox" name="'.$depe_codi.'" value="'.$depe_codi.'" onclick="datosArea('.$depe_codi.','.$opcTipo.','.$depe_codi.','.$depe_codi_padre.',this);" checked>';
                else                
                    $menu_areas = '<input type="checkbox" name="'.$depe_codi.'" value="'.$depe_codi.'" onclick="datosArea('.$depe_codi.','.$opcTipo.','.$depe_codi.','.$depe_codi_padre.',this);">';                
            }else
                $menu_areas = '<INPUT NAME="area_desa_'.$depe_codi.'"  id="area_desa_'.$depe_codi.'" TYPE=CHECKBOX DISABLED>';
        }
        return $menu_areas;
}
// Funcion para consultar las areas hijas
function obtenerDependencia($depe_codi, $db,$usrCodigo){
    $usua_admin = $_SESSION['usua_codi'];
    $admin_areas=pertenece($_SESSION['inst_codi'],$usua_admin,$db,1);//si no administra nada
    
    $sqlDepeHijo = "select
                        depe_codi,
                        depe_nomb,
                        depe_codi_padre
                        
                     from
                        dependencia
                     where
                        depe_estado=1 and depe_codi_padre = ".$depe_codi.
                        " and depe_codi <> depe_codi_padre order by depe_nomb";

    $rsDepeHijo=$db->conn->query($sqlDepeHijo);

    while(!$rsDepeHijo->EOF)
    {
            
            
            $sqlCountHijo = "select
                                count(depe_codi) as depe_codi
                            from
                                dependencia
                            where
                                depe_estado=1 and depe_codi_padre = ".$rsDepeHijo->fields["DEPE_CODI"].
                                " and depe_codi <> depe_codi_padre";
            $rsCountHijo=$db->conn->query($sqlCountHijo);
            //$opcTipo = "'".$rsDepeHijo->fields["EXISTE_DEP"]."'";
            $cuantos= $rsCountHijo->fields["DEPE_CODI"];
             if ($cuantos==0)
                $cuantos='';
             else
            $cuantos=' ('.$rsCountHijo->fields["DEPE_CODI"].')';
             $opcTipo = 0;//ultimo nivel, no necesita guardar mas hijas
           $depe_codi_fin=obtenerCodigos($usrCodigo,$rsDepeHijo->fields["DEPE_CODI"],$db);
            
           if (obtenerCodigos($usrCodigo,$rsDepeHijo->fields["DEPE_CODI"],$db)==1)//si ya administra el usuario la dependencia
                $menu_depeHijo .= '<li id="phtml_'.$rsDepeHijo->fields["DEPE_CODI"].'" ><a href="javascript:;" onclick="datosArea('.$rsDepeHijo->fields["DEPE_CODI"].','.$opcTipo.','.$rsCountHijo->fields["DEPE_CODI"].','.$rsDepeHijo->fields["DEPE_CODI_PADRE"].');">';
           else
               $menu_depeHijo .= '<li id="phtml_'.$rsDepeHijo->fields["DEPE_CODI"].'" '.graficacheck($rsDepeHijo->fields["EXISTE_DEP"],$rsDepeHijo->fields["DEPE_CODI"],$db).'><a href="javascript:;" onclick="datosArea('.$rsDepeHijo->fields["DEPE_CODI"].','.$opcTipo.','.$rsCountHijo->fields["DEPE_CODI"].','.$rsDepeHijo->fields["DEPE_CODI_PADRE"].');">';
            
            
            $menu_depeHijo .= '<input type="hidden" name="hidd_pd'.$rsDepeHijo->fields["DEPE_CODI"].'" id="hidd_pd'.$rsDepeHijo->fields["DEPE_CODI"].'" value="'.$rsCountHijo->fields["DEPE_CODI"].'" size="4"/>';
            
            $existe=0;
        $existeAdmin = 0;
        
        $existe=obtenerCodigos($usrCodigo,$rsDepeHijo->fields["DEPE_CODI"],$db);
        
        $existeAdmin=obtenerCodigos($usua_admin,$rsDepeHijo->fields["DEPE_CODI"],$db);//pertenece a administrador        
          
        $menu_depeHijo.=graficarCheckDesabled($rsDepeHijo->fields["DEPE_CODI"],$rsDepeHijo->fields["DEPE_CODI_PADRE"],$admin_areas,$existeAdmin,$opcTipo,$existe);
        
            
           $menu_depeHijo .= '<font size=1>'.$rsDepeHijo->fields["DEPE_NOMB"].'</font>';
            $menu_depeHijo .= "</a>";
            if($rsCountHijo->fields["DEPE_CODI"]!='0')//si tiene hijas
            {
                $menu_depeHijo .= "<ul>";
                $menu_depeHijo .= obtenerDependencia($rsDepeHijo->fields["DEPE_CODI"], $db,$usrCodigo);
                $menu_depeHijo .= "</ul>";
            }
            $menu_depeHijo .= "</li>";
            $existe=0;
            $rsDepeHijo->MoveNext();
        
    }
    return $menu_depeHijo;
}
//opcion: si ya esta seleccionado
//dependencia
function graficacheck($opcion,$depe_codi,$db){
    if (countPadre($depe_codi,$db)==1)//si tiene hijos
         $html='class="jstree-closed jstree-undetermined"';
    else{
        if ($opcion=='SI')//si la dependencia ya esta administrando el usuario
            $html='class="jstree-checked"';
        else//si no administra el usuario la dependencia
            $html='class="jstree-leaf jstree-unchecked"';
    }
    return $html;    
}
//devuelve cuantas dependencias existe en la dependencia padre
function countPadre($depe_codi,$db){
    $sql="select count(depe_codi) as contador from dependencia where depe_estado = 1 and depe_codi_padre = $depe_codi"; 
    //echo $sql;
    $rs=$db->conn->query($sql);
     if (!$rs->EOF)
             $contador = $rs->fields['CONTADOR'];
         if ($contador>1)
             return 1;
     return 0;
}
//retorna si el usuario ya esta administrando en esa dependencia
function obtenerCodigos($usr_codigo,$depe_codigo,$db){    
    $sql="select depe_codi_tmp from usuario_dependencia where usua_codi = $usr_codigo";
    //echo $sql;
    $rs=$db->conn->query($sql);
     if (!$rs->EOF){
             $depe_codi_tmp = $rs->fields["DEPE_CODI_TMP"];
            
             $depe_codigos = split(",",$depe_codi_tmp);
         
            for($i=0;$i<sizeof($depe_codigos);$i++){ 
                if ($i!=0){                              
                    if ($depe_codigos[$i]==$depe_codigo){
                        return 1;
                        break;
                    }                   
                }
         }
     }
     else return 0;
}
function obtenerCodigosPadres($usr_codigo,$depe_codigo,$db){    
   
     $depe_codi_tmp = areas_admin($usr_codigo,$db);
     //echo $depe_codi_tmp;
     $adm=0;
          if ($depe_codi_tmp!=''){
             $depe_codigos = split(",",$depe_codi_tmp);
         
            for($i=0;$i<sizeof($depe_codigos);$i++){ 
                if ($i!=0){                              
                    if ($depe_codigos[$i]==$depe_codigo){
                        $adm= 1;
                        break;
                    }                   
                }
         }
         return $adm;
     }
     else return '';
}

function areas_admin($usr_codigo,$db){
    $sql ="select * from usuario_dependencia u    
            where usua_codi = $usr_codigo";
   
           $rsDepePadre=$db->conn->query($sql);
           $depeCodiTmp = $rsDepePadre->fields['DEPE_CODI_TMP'];
           $depeOrdenTmp = substr($depeCodiTmp,1);
           if ($depeCodiTmp!=''){
               $sql_orden= "select depe_codi,depe_codi_padre from dependencia 
                        where depe_codi in ($depeOrdenTmp) and depe_estado = 1 
               and depe_codi <> depe_codi_padre";
               $sql_orden.=" group by depe_codi,depe_codi_padre,depe_nomb
                        order by depe_codi_padre";
               //echo $sql_orden;
               $rs=$db->conn->query($sql_orden);

               while(!$rs->EOF){
                   $padre = $rs->fields['DEPE_CODI_PADRE'];
                    if ($padre!=$padre2){
                    $depe_codi.= ",".$rs->fields['DEPE_CODI_PADRE'];
                    $sqlp="select depe_codi_padre as padre_padre from dependencia where depe_codi<> depe_codi_padre and depe_codi = ".$rs->fields['DEPE_CODI_PADRE'];
                    //echo $sqlp;
                     $rsP=$db->conn->query($sqlp);
                        if(!$rsP->EOF){
                            $depe_codi.=",".$rsP->fields['PADRE_PADRE'];
                        }
                    }
                    $depe_codi.= ",".$rs->fields['DEPE_CODI'];
                    $padre2 = $rs->fields['DEPE_CODI_PADRE'];
                    $rs->MoveNext();                 
                }
           }
           return $depe_codi;
}
function pertenece($inst_codi,$usua_codi,$db,$tipo){
    $sql="select depe_codi_tmp from usuario_dependencia 
    where usua_codi = $usua_codi and inst_codi = $inst_codi";
    //echo $sql;
    $rs=$db->conn->query($sql);
    
     if (!$rs->EOF){
         
             if ($tipo==1)
                 return 1;
             else
                 return $rs->fields['DEPE_CODI_TMP'];
             }
             return 0;
}
?>