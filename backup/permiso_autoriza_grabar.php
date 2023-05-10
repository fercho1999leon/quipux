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
  require_once("$ruta_raiz/funciones.php");
  include_once "$ruta_raiz/funciones_interfaz.php";
  echo "<html>".html_head();

  $dependencia = $_POST['dependencia'];
  $usr_usua_codi = limpiar_numero($_POST["txt_usua_codi"]);
  $permiso = limpiar_numero($_POST["txt_permiso"]);
  if($dependencia)
    $dep = implode(",", $dependencia);
  $dependencia_ex = array();
  $dependencia_guardar = array();
  $mensaje_val = "";

  $sql = "select pd.usua_codi, pd.depe_codi, us.nombre_us, dep.depe_nomb
    from permiso_usuario_dep as pd
    left outer join (select usua_codi, usua_nomb || ' '  || usua_apellido as nombre_us from usuarios) as us
    on pd.usua_codi = us.usua_codi
    left outer join (select depe_nomb, depe_codi from dependencia) as dep
    on pd.depe_codi = dep.depe_codi
    where pd.id_permiso = $permiso
    and pd.depe_codi in ($dep)
    and pd.usua_codi <> $usr_usua_codi";
  //echo $sql. "<br>";
  $rs = $db->conn->Execute($sql);
  
  if($rs->_numOfRows != 0){
     $nombre_us = $rs->fields["NOMBRE_US"];
    
     $i = 0;
     while (!$rs->EOF) {
        $nombre_dep = $nombre_dep . "<br>" . $rs->fields["DEPE_NOMB"];
        $dependencia_ex[$i] = $rs->fields["DEPE_CODI"];        
        $rs->MoveNext();
        $i++;
     }
    
    $mensaje_val = "No se guardó el permiso para la(s) siguiente(s) área(s), porque el usuario " .$nombre_us . " ya las tiene asignada(s): <br> " . $nombre_dep;

  }

  //Se obtiene las dependencias que ningún otro usuario tiene asignadas para guardar
  if($dependencia){
    $i = 0;
    foreach ($dependencia as $valor=>$val) {
        
        if (in_array($val, $dependencia_ex, true)){
            //echo $val . " asignada <br>";
        }
        else{
            //echo $val . " No asignada <br>";
            $dependencia_guardar[$i] = $val;          
        }
            $i++;
     }
  }
  
  //Se inicia transacción
  if ($db->transaccion==0) $db->conn->BeginTrans();

  //Se elimina áreas anteriores
  $sql = "delete from permiso_usuario_dep
          where usua_codi = $usr_usua_codi
          and id_permiso = $permiso
          and depe_codi in (select depe_codi from permiso_usuario_dep
            where usua_codi = $usr_usua_codi
            and id_permiso = $permiso)";
  $insertSQL=$db->conn->Execute($sql);

  //Se elimina permiso principal
  $sql = "delete from permiso_usuario where usua_codi=$usr_usua_codi and id_permiso=$permiso";
  $insertSQL=$db->conn->Execute($sql);

  //Se guarda permiso por áreas actuales
  if($dependencia_guardar){
       //Se agrega el permiso principal
       $record["ID_PERMISO"] = $permiso;
       $record["USUA_CODI"] = $usr_usua_codi;
       $insertSQL = $db->conn->Replace("PERMISO_USUARIO", $record, "", false,false,true,false);

      //Se agrega el permiso para cada área
      foreach ($dependencia_guardar as $valor=>$dep) {
         $record["ID_PERMISO"] = $permiso;
         $record["USUA_CODI"] = $usr_usua_codi;
         $record["DEPE_CODI"] = $dep;
         $insertSQL = $db->conn->Replace("PERMISO_USUARIO_DEP", $record, "", false,false,true,false);
      }
  }

//Se finaliza transacción
if(!$insertSQL) {
    if ($db->transaccion==0){
        $db->conn->RollbackTrans();
        $mensaje = "Error no se guardó los permisos. <br> ";
        //$mensaje = "Error no se guardó los permisos. <br> SQL: ".$db->conn->querySql;
    }
    else return 0;
} else {
    if ($db->transaccion==0){
        $db->conn->CommitTrans();
        $mensaje = "Datos de permisos guardados correctamente.";
    }
}

?>
<center>
<table width="100%" align="center" border="0" cellpadding="0" cellspacing="3" class="borde_tab">
        <tr><td class="titulos2" align="center">Permiso para aprobar solicitudes de respaldos</td></tr>
        <tr><td class="listado1" align="center"><? echo $mensaje . "<br> " . $mensaje_val ?></td></tr>
</table>
<br>
<input type='button' name='btn_cancelar' value='Aceptar' class='botones' onClick='window.close()'>
</center>