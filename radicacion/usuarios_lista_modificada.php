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

$ruta_raiz = "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
if (isset ($replicacion) && $replicacion && $config_db_replica_buscar_usuario_de_para!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_buscar_usuario_de_para);

include_once("$ruta_raiz/obtenerdatos.php");

 if (isset($_GET['radi_lista_dest']) and isset($_GET['usuarios_radi'])){//lista destino
                $lista_destino = limpiar_sql($_GET["radi_lista_dest"]);
                $usuarios_codi=limpiar_sql($_GET['usuarios_radi']);
                $usr_ag = nombre_lista($lista_destino,$usuarios_codi,$db);
                $usr_el = eliminadosDeLista($lista_destino,$usuarios_codi,$db,0);
               
                $html= '<table><tr><td>';
                    if ($usr_ag!='')
                     $html.='<table class=borde_tab><tr><td>'.nombre_lista($lista_destino,$usuarios_codi,$db).'</td><tr></table>';
                     $html.='</td>';
                     $html.='<td>';
                     if ($usr_el!='')
                     $html.='<table class=borde_tab><tr><td>'.eliminadosDeLista($lista_destino,$usuarios_codi,$db,1).'</td><tr></table></td></tr>';
                     $html.='</table>';
                     echo $html;
               

                
}//lista destino
function nombre_lista($lista_codi,$lista_usuarios,$db){
    $htmli="<table><tr><td><b><font color='blue'>Usuarios Agregados a la lista
                    </font></b></td></tr>";    
    $lista_doc = explode("-",$lista_codi);
     for($l=0 ; $l<=count($lista_doc)+1 ; $l++) {//for
         if ($lista_doc[$l]!=''){
                $htmlm.=buscar_usuarios($lista_doc[$l],$lista_usuarios,$db);
         }
     }//for
    $htmlf.="</table>";
    if ($htmlm!='')
    return $htmli.$htmlm.$htmlf;
    else
        return;
}
function buscar_usuarios($lista_codi,$usuarios_codi,$db){
    //buscar usuarios
    $usuarios = str_replace('--', ',',$usuarios_codi);
    $usuarios = str_replace('-', '',$usuarios);
    if ($usuarios!='' and $lista_codi!=''){
    $sql="select * from usuario where usua_codi not in ($usuarios) and 
        usua_codi in (select usua_codi from lista_usuarios where lista_codi = $lista_codi)";
   
    $rs = $db->conn->query($sql);
          while(!$rs->EOF){
            $html.=enlaceUsuario($rs->fields["USUA_CODI"],$rs->fields["USUA_NOMB"],$rs->fields["USUA_APELLIDO"],'A');
            $rs->MoveNext();
          }
    return $html;          
    }else
        return;
    
    
}
//$tipo E Eliminar,A agregar
function enlaceUsuario($usua_codi,$usua_nombre,$usua_apellido,$tipo){
    $tipo_des = "D";
    if ($tipo=='E'){
        $funcion = "borrarCCA('$usua_codi','$tipo_des');";
        $descripcion = "Eliminar de Destinatarios";
        $imagen = '<img src="../imagenes/quitar.png" title="Eliminar del Documento" alt="Eliminar">';
    }
    else{
        $funcion = "pasar('$usua_codi','1');";
        $descripcion = "Agregar en Destinatarios";
        $imagen = '<img src="../imagenes/add.png" title="Agregar en el Documento" alt="Agregar">';
    }
    $html.="<tr><td>";        
    $html.="<a onclick=\"$funcion\" href='javascript:void(0);' target='mainFrame' class='vinculos' title='$descripcion'>".$imagen;
    $html.='</a>&nbsp;&nbsp;'.$usua_nombre." ".$usua_apellido.'</td></tr>';    
    return $html;
}
function eliminadosDeLista($lista_codi,$usua_codi,$db,$tipo){
    $usuarios = str_replace('--', ',',$usua_codi);
    $usuarios = str_replace('-', '',$usuarios);
    $listas = str_replace('--', ',',$lista_codi);
    $listas = str_replace('-', '',$listas);
    if ($usuarios!='' and $listas!=''){
    $sql = "select usua_nomb,usua_apellido,usua_codi from usuario 
            where usua_codi in ($usuarios) and usua_codi not in 
    (select usua_codi from lista_usuarios where lista_codi in ($listas))";
    //echo $sql;
    $rs = $db->conn->query($sql);
     $html.="<table><tr><td><b><font color='black'>Usuarios Eliminados de la lista</font></b></td></tr>";
        while(!$rs->EOF){
            if ($tipo==1){
            $htmlin.=enlaceUsuario($rs->fields["USUA_CODI"],$rs->fields["USUA_NOMB"],$rs->fields["USUA_APELLIDO"],'E');
            }else
                $usua_eli = $usua_eli.",".$rs->fields['USUA_CODI'];
            $rs->MoveNext();
          }
    $html2.="<table>";
    if ($tipo==1)
        if ($htmlin!='')
        return $html.$htmlin.$html2;
        else
            return;
    else
        return $usua_eli;
    }else
        return;

}
?>