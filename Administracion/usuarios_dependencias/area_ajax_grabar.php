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

session_start();
$ruta_raiz = "../..";
include_once "$ruta_raiz/rec_session.php";
if($_SESSION["usua_admin_sistema"]!=1) die("");

require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post

include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/obtenerdatos.php";

//$depe_destino = (isset($depe_destino)) ? $depe_destino : $_SESSION['depe_codi'];
if(isset($_GET['area'])){
    
    $depe_codi=0+limpiar_sql($_GET['area']);//area hija
    $depe_codi_padre=0+limpiar_sql($_GET['areaPadre']);//area padre    
    $usr_duenio=0+limpiar_sql($_GET['usrCodigo']);//usuario al que se le asigna
    $instancia=0+limpiar_sql($_GET['instancia']);
    $cuantos=0+limpiar_sql($_GET['cuantos']);//numero de hijas del padre (depedencias)
    $tipo=0+limpiar_sql($_GET['tipo']);//inserta o borra
    $todo=0+limpiar_sql($_GET['todo']);
    $Arbol=0+limpiar_sql($_GET['Arbol']);
    $existe=0;
        $existeAdmin = 0;
        
        $existe=obtenerDependenciaUso($usr_duenio,$depe_codi,$db);
        
        $existeAdmin=obtenerDependenciaUso($_SESSION['usua_codi'],$depe_codi,$db);//pertenece a administrador        
        $admin_areas=pertenece($_SESSION['inst_codi'],$_SESSION['usua_codi'],$db,1);
       
     if ($usr_duenio!=''){
            $sql="select depe_codi from usuario_dependencia where depe_codi=$instancia and usua_codi = $usr_duenio";
            
            $rs=$db->conn->query($sql);
            if (!$rs->EOF) {
                $usrInstancia=$rs->fields['DEPE_CODI'];
            }
            if ($usrInstancia=='')
                $usrInstancia=0;
        }       
  
   if ($Arbol==0){//Grabar
       if ($todo==1)//grabar o borrar toda la institucion      
           grabarInstitucion($_SESSION['usua_codi'],$usr_duenio,$_SESSION['inst_codi'],$db,$tipo);
       else{//grarbar o borrar de uno en uno
           
           if ($tipo==1){//grabar de 1 en 1              
                if (opcionGrabar($depe_codi,$depe_codi_padre,$admin_areas,$existeAdmin,$existe)==1)
                grabar_instancia($depe_codi,$_SESSION['usua_codi'],$usr_duenio,$depe_codi_padre,$_SESSION['inst_codi'],$db,1);
           }else//borrar las dependencias            
                borrar_instancia($depe_codi,$depe_codi_padre,$usr_duenio,$_SESSION['inst_codi'],$db);
       }   
   }else{//Grabar el arbol
       if ($tipo==1){//grabar dependencia principal de la cabecera
           if (opcionGrabar($depe_codi,$depe_codi_padre,$admin_areas,$existeAdmin,$existe)==1)
           grabar_instancia($depe_codi,$_SESSION['usua_codi'],$usr_duenio,$depe_codi_padre,$_SESSION['inst_codi'],$db,1);
       }
       else//borrar depedencia principal de la cabecera
           borrar_instancia($depe_codi,$depe_codi_padre,$usr_duenio,$_SESSION['inst_codi'],$db);
       //grabar o borrar el arbol
       grabarDependencia($depe_codi,$_SESSION['usua_codi'],$usr_duenio,$depe_codi_padre,$_SESSION['inst_codi'],$db,$tipo,$instancia);
       
   }
            
           

}

function grabar_instancia($depe_codi,$usr_registra,$usr_duenio,$depe_codi_padre,$institucion,$db,$tipo){
   
     $recUsrDep = array(); 
     $recUsrDep['USUA_CODI'] = $usr_duenio;
     $recUsrDep['INST_CODI'] = $institucion;
     $recUsrDep['USUA_CODI_ACTUALIZA'] = $usr_registra;
     //si no existen registros primera vez
    if (pertenece($institucion,$usr_duenio,$db,1)==0){
        $recUsrDep['DEPE_CODI_PADRE'] = $depe_codi_padre;        
        $recUsrDep['DEPE_CODI_TMP'] = "',$depe_codi'";
        //guarda
        $db->conn->Replace("USUARIO_DEPENDENCIA", $recUsrDep, "", false,false,true,false);
    }
    else{//caso contrario acualizo el campo
        $depe_tmp=pertenece($institucion,$usr_duenio,$db,0);//cargo las dependecias que administra
        if (obtenerCodigos($usr_duenio,$depe_codi,$db,1)!=1)//si no encuentra la dependencia guardo
        $depe_tmp="'".$depe_tmp.",".$depe_codi."'";       
        $ev=substr($depe_tmp,-1);
        if ($ev!="'")
            $depe_tmp="'$depe_tmp'";
        $recUsrDep['DEPE_CODI_TMP'] = $depe_tmp;
        
        $db->conn->Replace("USUARIO_DEPENDENCIA", $recUsrDep, "USUA_CODI", false,false,true,false);
    }
   
}
function borrar_instancia($depe_codi,$depe_codi_padre,$usr_codigo,$institucion,$db){ 
    
    
    if (obtenerCodigos($usr_codigo,$depe_codi,$db,0)!=''){//borra las areas
        $recUsrDep['DEPE_CODI_TMP'] = "'".obtenerCodigos($usr_codigo,$depe_codi,$db,0)."'";
        $recUsrDep['USUA_CODI'] = $usr_codigo;
        $recUsrDep['INST_CODI'] = $institucion;
        $recUsrDep['USUA_CODI_ACTUALIZA'] = $_SESSION['usua_codi'];
       
        $db->conn->Replace("USUARIO_DEPENDENCIA", $recUsrDep, "USUA_CODI", false,false,true,false);
        //para borrar area
        $sql="select depe_codi_tmp from usuario_dependencia where usua_codi = $usr_codigo";
        $rs=$db->conn->query($sql);
        if (!$rs->EOF){
            $depe_codi_adm = $rs->fields['DEPE_CODI_TMP']; 
        }

        $sql_i="select depe_codi from dependencia 
        where depe_codi = depe_codi_padre and inst_codi = $institucion";
        $rs_i=$db->conn->query($sql_i);
        if (!$rs->EOF){
            $depe_codi_ins = $rs_i->fields['DEPE_CODI']; 
        }
   
    }else{//si es la ultima
        $del="delete from usuario_dependencia where usua_codi = $usr_codigo";        
        $db->conn->query($del);
    }
     
}

//graba gerarquicamente el arbol sin padres solo el area
//tipo graba o elimina la dependencia
function grabarInstitucion($usr_registra,$usr_duenio,$institucion,$db,$check){    
   $admin_areas=pertenece($_SESSION['inst_codi'],$_SESSION['usua_codi'],$db,1);
   if ($check==1){//agregar
    $sqlDepeHijo = "select *                   
                     from
                        dependencia
                     where
                        depe_estado=1 and inst_codi = ".$institucion.
                        " order by depe_nomb";    
    //echo $sqlDepeHijo;
    $rsDepeHijo=$db->conn->query($sqlDepeHijo);
    $tmpDepencias=pertenece($institucion,$usr_duenio,$db,0);//dependencias
    //echo $tmpDepencias;
    $where=($tmpDepencias!=1)?"USUA_CODI" : $where="";
    
    $recUsrDep['USUA_CODI'] = $usr_duenio;
    $recUsrDep['INST_CODI'] = $institucion;
    $recUsrDep['USUA_CODI_ACTUALIZA'] = $usr_registra;
    while(!$rsDepeHijo->EOF)
    {   
        $existe=0;
        $existeAdmin = 0;
        
        $existe=obtenerDependenciaUso($usr_duenio,$rsDepeHijo->fields["DEPE_CODI"],$db);
        
        $existeAdmin=obtenerDependenciaUso($usr_registra,$rsDepeHijo->fields["DEPE_CODI"],$db);//pertenece a administrador        
       
        if (obtenerCodigos($usr_duenio,$rsDepeHijo->fields["DEPE_CODI"],$db,1)!=1)               
             if (opcionGrabar($rsDepeHijo->fields["DEPE_CODI"],$rsDepeHijo->fields["DEPE_CODI_PADRE"],$admin_areas,$existeAdmin,$existe)==1)
                if ($rsDepeHijo->fields["DEPE_CODI"]!='0')
                    $tmpDepencias.=",".$rsDepeHijo->fields["DEPE_CODI"];                
            $rsDepeHijo->MoveNext();
     }
     
     if (pertenece($institucion,$usr_duenio,$db,1)==1)
     $recUsrDep['DEPE_CODI_TMP']="'$tmpDepencias'"; 
     else
        $recUsrDep['DEPE_CODI_TMP']="',$tmpDepencias'"; 
     
     $ok=$db->conn->Replace("USUARIO_DEPENDENCIA", $recUsrDep, $where, false,false,true,false);
   }else{//borrar
       $sql ="delete from usuario_dependencia where usua_codi = $usr_duenio";
       $db->conn->query($sql);
   }
   
}
function grabarDependencia($depe_codi,$usr_registra,$usr_duenio,$depe_codi_padre,$institucion,$db,$tipo,$instancia){    
    $admin_areas=pertenece($_SESSION['inst_codi'],$_SESSION['usua_codi'],$db,1);
    $sqlDepeHijo = "select                       
                    depe_codi as hijo    
                    ,depe_codi_padre  as padre                     
                     from
                        dependencia
                     where
                        depe_estado=1 and depe_codi_padre = ".$depe_codi.
                        " order by depe_nomb";    
    $rsDepeHijo=$db->conn->query($sqlDepeHijo);
    //echo $sqlDepeHijo;
    while(!$rsDepeHijo->EOF)
    {
        $sqlCountHijo = "select
                            count(depe_codi) as depe_codi
                        from
                            dependencia
                        where
                            depe_estado=1 and depe_codi_padre = ".$depe_codi.
                            " and depe_codi <> depe_codi_padre";
       
          $rsCountHijo=$db->conn->query($sqlCountHijo);
          $existe=0;
        $existeAdmin = 0;
        
        $existe=obtenerDependenciaUso($usr_duenio,$rsDepeHijo->fields["HIJO"],$db);
        
        $existeAdmin=obtenerDependenciaUso($usr_registra,$rsDepeHijo->fields["HIJO"],$db);//pertenece a administrador                
          if ($tipo==1){
            if (opcionGrabar($rsDepeHijo->fields["HIJO"],$rsDepeHijo->fields["PADRE"],$admin_areas,$existeAdmin,$existe)==1)
             grabar_instancia($rsDepeHijo->fields["HIJO"],$_SESSION['usua_codi'],$usr_duenio,$rsDepeHijo->fields["PADRE"],$institucion,$db,1);
          }else
              borrar_instancia($rsDepeHijo->fields["HIJO"],$rsDepeHijo->fields["PADRE"],$usr_duenio,$institucion,$db); 
          
          if(countPadre($rsDepeHijo->fields["HIJO"],$db,1)==1 and $rsDepeHijo->fields["HIJO"]!=$rsDepeHijo->fields["PADRE"]){              
                  if ($instancia!=$rsDepeHijo->fields["HIJO"])                          
               grabarDependencia($rsDepeHijo->fields["HIJO"], $usr_registra,$usr_duenio,$rsDepeHijo->fields["PADRE"],$institucion,$db,$tipo,$instancia);            
                  
          }
        $rsDepeHijo->MoveNext();
        
    }   
}
//verifica si el usuario ya administra esa area
//tipo 0 devuelve las dependencias que administra

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
//tipo 1=devuelve true o false, 0 devuelve cuantos
function countPadre($depe_codi,$db,$tipo){
    $sql="select count(depe_codi) as contador from dependencia where depe_codi_padre = $depe_codi"; 
    //echo $sql;
    $rs=$db->conn->query($sql);
     if (!$rs->EOF){
           if ($tipo==1){
             $contador = $rs->fields['CONTADOR'];
                if ($contador>=1)//si tiene hijos
                return 1;
           }else{
               return $rs->fields['CONTADOR'];
           }
     }else//si no ecisten registros
     return 0;
           
          
}
//tipo 1 devuelve 1 si administra o 0 si no administra la dependencia
//0 devuelve todas las dependencias depe_codigo_tmp
function obtenerCodigos($usr_codigo,$depe_codigo,$db,$tipo){    
    $sql="select depe_codi_tmp from usuario_dependencia where usua_codi = $usr_codigo";
    //echo $sql;
    $rs=$db->conn->query($sql);
     if (!$rs->EOF){
         
             $depe_codi_tmp = $rs->fields["DEPE_CODI_TMP"];            
             $depe_codigos = split(",",$depe_codi_tmp);             
          if ($tipo==1){//para guardar   
         // $resultado = substr_count($depe_codi_tmp, $depe_codigo); 
         
            for($i=0;$i<sizeof($depe_codigos);$i++){ 
                if ($i!=0){                            
                    
                    if ($depe_codigos[$i]==$depe_codigo){                        
                        return 1;
                        break;
                    }//i
                 }//if
            }//for      
          }else{//recorro las dependencias en el campo
                     for($i=0;$i<sizeof($depe_codigos);$i++){ 
                         if ($i!=0){ 
                            if ($depe_codigos[$i]!=$depe_codigo)//elimino del campo la dependencia
                                if ($i==1)//si es el primer registro para el campo
                                 $depe_codigo_up=",".$depe_codigos[$i];
                                else//sigo concatenando
                                $depe_codigo_up=$depe_codigo_up.",".$depe_codigos[$i];
                         }
                    }
            
                  
            return $depe_codigo_up;
          }
         }
     
     else return 0;//si no hay registros
}
function obtenerDependenciaUso($usr_codigo,$depe_codigo,$db){    
    $sql="select depe_codi_tmp from usuario_dependencia where usua_codi = $usr_codigo";
    
    $rs=$db->conn->query($sql);
     if (!$rs->EOF){
         
             $depe_codi_tmp = $rs->fields["DEPE_CODI_TMP"];
            
             $depe_codigos = split(",",$depe_codi_tmp);
           
            for($i=0;$i<sizeof($depe_codigos);$i++){ 
                if ($i!=0){  
                    //echo $depe_codigos[$i]."==".$depe_codigo;
                    if ($depe_codigos[$i]==$depe_codigo){
                        return 1;
                        break;
                    }                   
                }
         }
     }
     else return 0;
}
//opcion grabar
function opcionGrabar($depe_codi,$depe_codi_padre,$admin_areas,$existeAdmin,$existe){
    
    if ($admin_areas=='')
        $admin_areas=0;
    if ($existeAdmin=='')
        $existeAdmin = 0;
    
    if ($admin_areas==0){//administra areas
            if ($existe==0)//administra el area                
                return 1;
            else                
                return 0;
        }else{//no administra           
            
            if ($existeAdmin==1){
                if ($existe==0)                
                    return 1;
                else                
                    return 0;
            }else
                return 0;
        }        
}

?>
