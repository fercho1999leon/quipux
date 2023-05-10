<?php
/**  Programa para el manejo de gestion documental, oficios, memorandos, circulares, acuerdos
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
$ruta_raiz = "../..";
session_start();
include_once "$ruta_raiz/funciones_interfaz.php";
if ($_SESSION["usua_admin_sistema"] != 1) {
    echo html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
    die("");
}
include_once "$ruta_raiz/rec_session.php";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
require_once("$ruta_raiz/obtenerdatos.php");  //formar la observacion de edicion

include_once "$ruta_raiz/include/tx/Tx.php";
$tx = new Tx($db);

$recsbrte = array();
$recperm = array();
$usr_subrogante = 0+$_GET['codigo_subrogante'];
$usr_subrogado = 0+$_GET['codigo_subrogado'];
//Selecciono los datos del usuario subrogante para desactivar
if ($usr_subrogante!=0 && $usr_subrogado!=0){//si existe subrogante
//
//  elimino carpeta compartida del subrogante
    $sqlcarpetacompartida = "delete from bandeja_compartida where usua_codi = ".$usr_subrogante;
    $db->conn->Execute($sqlcarpetacompartida);
    //Elimino los permisos del subrogante
    $sqlpermiso = "delete from permiso_usuario where usua_codi = ".$usr_subrogante;
    $db->conn->Execute($sqlpermiso);
    //Elimino de la lista
    eliminarDlista($usr_subrogante,$db);
    //actualiza en subrogacion de cargo
    $sqlSubrogante = "update usuarios_subrogacion set usua_visible = 0 where usua_subrogante = ".$usr_subrogante;    
    $db->conn->Execute($sqlSubrogante);
    //actualiza usuarios
    $usua_codi_actualiza = $_SESSION['usua_codi'];
    $observacion = $db->conn->qstr("Desactivación de usuario subrogante, se reasigna todos los documentos del usuario al subrogado y se desactiva este usuarios");
    $sqlUsuario = "update usuarios set usua_responsable_area = 0, usua_sumilla = lower(usua_sumilla),visible_sub=0, usua_esta=0, usua_cedula=usua_cedula||'-'||usua_codi, usua_login='l'||usua_codi, cargo_tipo=0, usua_codi_actualiza=$usua_codi_actualiza, usua_obs_actualiza=$observacion, usua_fecha_actualiza=".$db->conn->sysTimeStamp." where usua_codi=$usr_subrogante";
    $db->conn->Execute($sqlUsuario);
    //echo $sqlUsuario;
    //actualizo a visible el usuario subrogado
    $observacionsubrogado = $db->conn->qstr("Modificación por desactivación de usuario subrogante, se modifica el estado de visibilidad para las acciones de reasignar, informar y tareas");
    $sqlSubrogado = "update usuarios set usua_responsable_area = 0, usua_sumilla = lower(usua_sumilla),visible_sub=1, usua_codi_actualiza=$usua_codi_actualiza, usua_obs_actualiza=$observacionsubrogado, cargo_tipo=1, usua_fecha_actualiza=".$db->conn->sysTimeStamp." where usua_codi=$usr_subrogado";
    $db->conn->Execute($sqlSubrogado);
    //echo $sqlSubrogado;
    $ok2=2;
    
    $sql = "select radi_nume_radi from radicado where esta_codi in (1,6,2,0) and radi_usua_actu=$usr_subrogante";
    $rs = $db->conn->query($sql);
    unset($radicado);
    while ($rs && !$rs->EOF) {
        $radicado[] = $rs->fields['RADI_NUME_RADI'];
        $rs->MoveNext();
    }

    if(count($radicado)>0) {
        $tx->reasignar( $radicado, $usr_subrogante, $usr_subrogado, "Reasignado por desactivación de subrogación de Puesto","",true);
    }
    //Tareas Reasignar.
    
     $sqlTarea = "select radi_nume_radi from tarea where estado = 1 and usua_codi_dest = $usr_subrogante";
     
       $rsTarea= $db->conn->query($sqlTarea);
       unset($radicadoTarea);
	while (!$rsTarea->EOF) {
			$radicadoTarea[]= $rsTarea->fields['RADI_NUME_RADI'];
			$rsTarea->MoveNext();
		}
     
      if($usr_subrogado!=0 and count($radicadoTarea)>0)
                $tx->cambiarPropietarioTareasSubrogacion($radicadoTarea, $usr_subrogado, $usr_subrogante,0);
    
}


?>


       <center>
        <table width="100%" border="1" align="center" class="t_bordeGris">
            <tr>
            <td width="100%" height="30" class="listado2">
           <?php if ($ok2==2){?>            
                <span class=etexto><center><B>Se ha desactivado la subrogacion satisfactoriamente del usuario <br/></B></center></span>
           <?php }else{ ?>
                <span class=etexto><center><B>Existe un problema, por favor comuníquese con el Administrador</B></center></span>
           <?php } ?>
            </td>         
           <td height="30" class="listado2">
                <input name="btn_accion" class="botones" value="Regresar" onclick="window.location='../usuarios/cuerpoUsuario.php';" type="button"/>
            </td></tr>
       </table>       
    </center>
   