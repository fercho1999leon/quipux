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
*	DAVID GAMBOA    	SC			12/11/2011
*
**/
$ruta_raiz = "../..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

session_start();
if($_SESSION["usua_admin_sistema"]!=1) die("");
include_once "$ruta_raiz/rec_session.php";

$error = "";

$rec_log=array();
$rec_log["FECHA_ACTUALIZA"] = $db->conn->sysTimeStamp;
$rec_log["USUA_CODI_ACTUALIZA"] = $_SESSION["usua_codi"];

//Permite grabar el area en la base de datos con todos sus atributos
if (isset($_GET)){
    $usr_mod= limpiar_sql(substr($_GET['usr_mod'], 1));
    if ($_GET['accion']==1){
       if ($_GET['radio_filtro']=='SI')
         $permiso_mod= substr($_GET['permiso_mod'], 1);//extrae la coma del ultimo
       else  
           $permiso_mod= $_GET['permiso_mod'];
       $usr_mod_list = split(",",$usr_mod);
         
         for($i=0;$i<sizeof($usr_mod_list);$i++)//usuarios seleccionados
                {//for
                   //Ingresa si selecciono los permisos de la tabla
                   if ($_GET['txt_permiso']==0){//inicio si                 
                    $permiso_mod = substr($_GET['permiso_mod'], 1);
                    $perm_mod_list = split(",",$permiso_mod);//permisos
                     $usr_modificar= limpiar_numero($usr_mod_list[$i]);
                     for($j=0;$j<sizeof($perm_mod_list);$j++){//permisos seleccionados
                         $perm_modificar = limpiar_numero($perm_mod_list[$j]);
                         $record = array();
                         $record['ID_PERMISO'] = $db->conn->qstr(limpiar_sql($perm_modificar));
                         $record['USUA_CODI'] = $db->conn->qstr(limpiar_sql($usr_modificar));
                         $sqlComprueba = "select * from permiso_usuario where id_permiso = $perm_modificar and usua_codi = $usr_modificar";                         
                         $rs1=$db->conn->query($sqlComprueba);
                         $idpermiso=$rs1->fields['ID_PERMISO'];                         
                         if ($idpermiso=='') {//si ya tiene permiso no guarde el registro
                            $ok1 = $db->conn->Replace("PERMISO_USUARIO", $record, "", false,false,true,false);
                            $rec_log["USUA_CODI"] = $usr_modificar;
                            $rec_log["ID_PERMISO"] = $permiso_mod;
                            $rec_log["ACCION"] = 1;
                            $db->conn->Replace("LOG_USR_PERMISOS", $rec_log, "", false,false,false,false);
                         }
                     }
                   }//fin si
                   else{//ingresa a este else si el permiso
                       //es seleccionado del combo
                    $permiso_mod = limpiar_numero($_GET['permiso_mod']);
                    $usr_modificar= limpiar_numero($usr_mod_list[$i]);
                    $record = array();
                         $record['ID_PERMISO'] = $db->conn->qstr(limpiar_sql($permiso_mod));
                         $record['USUA_CODI'] = $db->conn->qstr(limpiar_sql($usr_modificar));
                          $sqlComprueba = "select * from permiso_usuario where id_permiso = $permiso_mod and usua_codi = $usr_modificar";                         
                         $rs1=$db->conn->query($sqlComprueba);
                         $idpermiso=$rs1->fields['ID_PERMISO'];                         
                         if ($idpermiso=='') {//si ya tiene permiso no guarde el registro
                            $ok1 = $db->conn->Replace("PERMISO_USUARIO", $record, "", false,false,true,false);
                            $rec_log["USUA_CODI"] = $usr_modificar;
                            $rec_log["ID_PERMISO"] = $permiso_mod;
                            $rec_log["ACCION"] = 1;
                            $db->conn->Replace("LOG_USR_PERMISOS", $rec_log, "", false,false,false,false);
                         }
                   }//else
                }//for
         
    }
    else{
        $permiso_mod= limpiar_numero($_GET['permiso_mod']);
        $sql="delete from permiso_usuario  where usua_codi in ($usr_mod) and id_permiso = $permiso_mod";
        $ok1= $db->conn->Execute($sql);
        $rec_log["ID_PERMISO"] = $permiso_mod;
        $rec_log["ACCION"] = 0;
        $usr_mod_list = split(",",$usr_mod);
        for($i=0;$i<sizeof($usr_mod_list);$i++) {
            $rec_log["USUA_CODI"] = limpiar_numero($usr_mod_list[$i]);
            $db->conn->Replace("LOG_USR_PERMISOS", $rec_log, "", false,false,false,false);
        }
        
    }
    //echo $sql;
    if ($_GET['accion']==1)
    echo "<center><font size='2' color='blue'>Permisos Registrados Satisfactoriamente</font></center>";    
    else
        echo "<center><font size='2' color='blue'>Permisos Eliminados</font></center>";    
        
}else
echo "<font size='2' color='red'>Existe un error por favor comuníquese con el Administrador del
    Sistema</font>";

?>