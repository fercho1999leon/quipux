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
include_once "$ruta_raiz/rec_session.php";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
require_once("$ruta_raiz/obtenerdatos.php");  //formar la observacion de edicion
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/include/tx/Tx.php";
if ($_SESSION["usua_admin_sistema"] != 1) {
    echo html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
    die("");
}
$tx=new Tx($db);

$recsbrte = array();
$recperm = array();
$recbancompartida = array();
$usr_subrogante = $_POST['usr_subrogante'];
//echo $usr_subrogante;
$usr_subrogado = $_POST['usr_subrogado'];
//busco usuario subrogante para posible actualizacion
$sqlSubrogante = "select usua_subrogante from usuarios_subrogacion where usua_subrogante = $usr_subrogante ";
        //echo $sqlSubrogante."<br>";
$rsSubrogante=$db->conn->query($sqlSubrogante);
$subrogante_inactivo = trim($rsSubrogante->fields["USUA_SUBROGANTE"]);

//}else{
    $txt_fecha_desde = $_POST['txt_fecha_desde'];
    $txt_fecha_hasta = $_POST['txt_fecha_hasta'];
    $txt_hora_desde = $_POST['txt_hora_desde'];
    $txt_hora_hasta = $_POST['txt_hora_hasta'];
    //Selecciono los datos del usuario subrogante para crear otro usuario con los mismos datos
    if ($usr_subrogante!=''){//si existe subrogante
        $sqlSubrogante = "select * from usuarios where usua_codi = ".$usr_subrogante;
        $rsSubrogante = $db->conn->Execute($sqlSubrogante);
        $sqlSubrogado = "select * from usuarios where usua_codi = ".$usr_subrogado;
        $rsSubrogado = $db->conn->Execute($sqlSubrogado);
        $usr_codigo = $db->nextId("usuarios_usua_codi_seq");
        $recsbrte['USUA_CODI'] = $usr_codigo;
        $recsbrte['USUA_LOGIN'] = $db->conn->qstr($rsSubrogante->fields["USUA_LOGIN"]);
        $recsbrte['USUA_PASW'] =  $db->conn->qstr($rsSubrogante->fields["USUA_PASW"]);
        $recsbrte['USUA_NOMB'] =  $db->conn->qstr($rsSubrogante->fields["USUA_NOMB"]);
        $recsbrte['USUA_CEDULA'] =  $db->conn->qstr($rsSubrogante->fields["USUA_CEDULA"]);
        $recsbrte['USUA_EMAIL'] =  $db->conn->qstr($rsSubrogante->fields["USUA_EMAIL"]);
        $emailsubrogante = $rsSubrogante->fields["USUA_EMAIL"];
        $recsbrte['USUA_TITULO'] =  $db->conn->qstr($rsSubrogante->fields["USUA_TITULO"]);
        $recsbrte['USUA_ABR_TITULO'] =  $db->conn->qstr($rsSubrogante->fields["USUA_ABR_TITULO"]);
        $recsbrte["USUA_ESTA"] = 1;
        $recsbrte["CARGO_TIPO"] = 0;
        $recsbrte["USUA_TIPO"] = $rsSubrogante->fields["USUA_TIPO"];
        $recsbrte["TIPO_IDENTIFICACION"] = $rsSubrogante->fields["TIPO_IDENTIFICACION"];
        $recsbrte['DEPE_CODI'] = $rsSubrogado->fields["DEPE_CODI"];//$_POST['dependencia_jefe'];
        $recsbrte['USUA_NUEVO'] = 1;
            
            $sqlSubrogado = "select * from usuarios where usua_codi = $usr_subrogado";    
            $rsSubrogado = $db->conn->query($sqlSubrogado);
            $usr_cargoJefearea = $rsSubrogado->fields["USUA_CARGO"];
           $repE = array(" Subrogante", ",");
        $encargado = str_replace($repE, "", $usr_cargoJefearea);        
        $encargado = $encargado.", Subrogante";
            $institucionSubrogante = $rsSubrogado->fields["INST_CODI"];
        $recsbrte["USUA_CARGO"] =  $db->conn->qstr($encargado);
        $recsbrte["INST_CODI"] = $rsSubrogado->fields["INST_CODI"];
        $instSubrogante =  $rsSubrogante->fields["INST_CODI"];
        $recsbrte["USUA_APELLIDO"] =  $db->conn->qstr($rsSubrogante->fields["USUA_APELLIDO"]);
        if ($rsSubrogado->fields["CARGO_ID"]!='')
            $recsbrte["CARGO_ID"] = $rsSubrogado->fields["CARGO_ID"];
        else
            $recsbrte["CARGO_ID"] = 99;//Le envio por defecto para no tener problemas en la base
        $recsbrte["USUA_OBS"] = $db->conn->qstr($rsSubrogante->fields["USUA_OBS"]);
        $recsbrte["CIU_CODI"] = 0+$rsSubrogante->fields["CIU_CODI"];
        $recsbrte["USUA_FIRMA_PATH"] = $db->conn->qstr($rsSubrogante->fields["USUA_FIRMA_PATH"]);
        $recsbrte["USUA_DIRECCION"] = $db->conn->qstr($rsSubrogante->fields["USUA_DIRECCION"]);
        $recsbrte["USUA_TELEFONO"] = $db->conn->qstr($rsSubrogante->fields["USUA_TELEFONO"]);
        $recsbrte["USUA_CODI_ACTUALIZA"] = $db->conn->qstr($_SESSION['usua_codi']);
        $fecha_subrogacion = $db->conn->sysTimeStamp;
        $recsbrte["USUA_FECHA_ACTUALIZA"] = $fecha_subrogacion;
        $recsbrte["USUA_OBS_ACTUALIZA"] = $db->conn->qstr($rsSubrogante->fields["USUA_OBS_ACTUALIZA"]);
        $recsbrte["USUA_CARGO_CABECERA"] =  $db->conn->qstr($encargado);
        $recsbrte["USUA_SUMILLA"] = $db->conn->qstr($rsSubrogante->fields["USUA_SUMILLA"]);
        $recsbrte["USUA_RESPONSABLE_AREA"] = 0+$rsSubrogante->fields["USUA_RESPONSABLE_AREA"];
        $recsbrte["INST_NOMBRE"] = $db->conn->qstr($rsSubrogante->fields["INST_NOMBRE"]);
        $recsbrte["USUA_TIPO_CERTIFICADO"] = $db->conn->qstr($rsSubrogante->fields["USUA_TIPO_CERTIFICADO"]);
        $nombresubrogante = $rsSubrogante->fields["USUA_NOMB"]." ".$rsSubrogante->fields["USUA_APELLIDO"];
        $cargosubrogante = $rsSubrogante->fields["USUA_CARGO"];
        $desde = $txt_fecha_desde." ".$txt_hora_desde.":00";
        $hasta = $txt_fecha_hasta." ".$txt_hora_hasta.":00";
        
        $recsbrte['USUA_SUBROGADO'] = $db->conn->qstr($usr_subrogado);
        //si ya existe el usuario
        if (trim($subrogante_inactivo)!=''){
        //si existe subrogante en la tablas de usuarios_subrogados no creo otro usuario, solo se activa
        //Realizo el update
         $cedulasinId = substr($recsbrte['USUA_CEDULA'],2,10);
         $login = "U".$cedulasinId;
         //Activo el suuario
         $sqlUsuario = "update usuarios set depe_codi = ".$recsbrte['DEPE_CODI'].",visible_sub = 1, usua_cargo_cabecera = '$encargado',usua_cargo = '$encargado', usua_esta = 1, cargo_tipo = 0,usua_subrogado = $usr_subrogado, usua_cedula = '$cedulasinId',usua_login = '$login' where usua_codi = $subrogante_inactivo";         
         $db->conn->Execute($sqlUsuario);
         //Inserto nuevamente los permisos del subrogado al subrogante inactivo
         ins_permisos_usr($db,$usr_subrogado,$subrogante_inactivo,$recper);         
        }else{//si es nuevo
            $ok1 = $db->conn->Replace("USUARIOS", $recsbrte, "", false,false,true,false);
            //die();
        }
        //NECESARIO TENGO QUE PONERLE AL JEFE ANTERIOR COMO NO VISIBLE
        $sqlJefeVisible = "update usuarios set visible_sub = 0,cargo_tipo=1 where usua_codi = $usr_subrogado";
        //echo $sqlJefeVisible;
        $db->conn->Execute($sqlJefeVisible);
    }
//}//else
if ($usr_codigo!=''){//si existe subrogado
    //Institucion
    $sqlInstitucion = "select * from institucion where inst_codi = ".$rsSubrogante->fields["INST_CODI"];    
    $rsInstitucion = $db->conn->query($sqlInstitucion);
    $instNombre = $rsInstitucion->fields["INST_NOMBRE"];
    //datos subrogado    
    $sqlSubrogado = "select * from usuarios where usua_codi = $usr_subrogado";    
    $rsSubrogado = $db->conn->query($sqlSubrogado);
    $nombresubrogado = $rsSubrogado->fields["USUA_NOMB"]." ".$rsSubrogado->fields["USUA_APELLIDO"];
    $cargosubrogado = $rsSubrogado->fields["USUA_CARGO"];
    $instSubrogado = $rsSubrogado->fields["INST_CODI"];
    $emailsubrogado = $rsSubrogado->fields["USUA_EMAIL"];
    if (trim($subrogante_inactivo)==''){//solo si se crea un usuario nuevo para el subrogante
        //inserta los permisos del subrogado al subrogante nuevo
        ins_permisos_usr($db,$usr_subrogado,$usr_codigo,$recper);
    }
        //$sqlbandejacompartida = "select * from bandeja_compartida where usua_codi = $usr_subrogante";
        //para crear bandeja compartida al usuario subrogante nuevo
        $recbancompartida["USUA_CODI_JEFE"]= $usr_subrogado;

        if (trim($subrogante_inactivo)!='')
          $recbancompartida["USUA_CODI"] = $subrogante_inactivo;
        else
          $recbancompartida["USUA_CODI"] = $usr_codigo;
        $recbancompartida["BAN_COM_FECHA"]= $db->conn->sysTimeStamp;
        $db->conn->Replace("BANDEJA_COMPARTIDA", $recbancompartida, "", false,false,true,false);
    
    //GUARDAR EN TABLA SUBROGACION
      $recusuasub = array();
      $recusuasub['USUA_SUBROGADO'] = $usr_subrogado;
      if (trim($subrogante_inactivo)!='')
          $recusuasub['USUA_SUBROGANTE'] = $subrogante_inactivo;
      else
        $recusuasub['USUA_SUBROGANTE'] = $usr_codigo;
      $recusuasub['USUA_FECHA_INICIO'] = $db->conn->qstr($desde);
      $recusuasub['USUA_FECHA_FIN'] = $db->conn->qstr($hasta);      
      $recusuasub['USUA_VISIBLE'] = 1;
      $observacion = "Subrogacion de Puesto";
      $recusuasub['USUA_OBSERVACION'] = $db->conn->qstr($observacion);
      $recusuasub['USUA_FECHA_ACTUALIZACION'] =$db->conn->sysTimeStamp;
      $recusuasub['USUA_CODI_ACTUALIZA'] = $db->conn->qstr($_SESSION['usua_codi']);
      $ok2 = $db->conn->Replace("USUARIOS_SUBROGACION", $recusuasub, "", false,false,true,false);
      //PASAR LOS DOCUMENTOS DEL SUBROGADO AL SUBROGANTE
      if ($usr_subrogado!='')
      $sql = "select radi_nume_radi from radicado where esta_codi in (1) and radi_usua_actu=$usr_subrogado";
      if (trim($subrogante_inactivo)!='') //if ($usr_codigo!='')
        $usr_destino = $subrogante_inactivo;
      else
          $usr_destino = $usr_codigo;
    
	$rs= $db->conn->query($sql);
	unset($radicado);
	while (!$rs->EOF) {
			$radicado[]= $rs->fields['RADI_NUME_RADI'];
			$rs->MoveNext();
		}
                
	if($usr_destino!=0 and count($radicado)>0) {
            $tx->reasignar( $radicado, $usr_subrogado, $usr_destino, 'Reasignado por activación de subrogación de Puesto',"",true);
            }
            
       $sqlTarea = "select radi_nume_radi from tarea where estado = 1 and usua_codi_dest = $usr_subrogado";
       $rsTarea= $db->conn->query($sqlTarea);
       unset($radicadoTarea);
	while (!$rsTarea->EOF) {
			$radicadoTarea[]= $rsTarea->fields['RADI_NUME_RADI'];
			$rsTarea->MoveNext();
		}
     
      if($usr_destino!=0 and count($radicadoTarea)>0)
                $tx->cambiarPropietarioTareasSubrogacion($radicadoTarea, $usr_destino, $usr_subrogado,1);
}

////////////////////////////////////////ENVIO DE CORREOS/////////////////////////////////////
//CUERPO DEL MAIL
//$selectDepe= "select * from dependencia where depe_codi = $institucionSubrogante";
//dependencia
if ($_POST['dependencia_jefe']!='')
    $dependenciaSubrogado = 0 + $_POST['dependencia_jefe'];
$selectDepe= "select * from dependencia where depe_codi = ".$dependenciaSubrogado;
$rsDepe = $db->conn->query($selectDepe);
$dependenciaSubrogacion=$rsDepe->fields["DEPE_NOMB"];
//Institucion Subrogante
$sqlInsGate = "select * from institucion where inst_codi = ".$instSubrogante;

$rsInsGate = $db->conn->query($sqlInsGate);
$institucionGate=$rsInsGate->fields["INST_NOMBRE"];
//Institucion Subrogado
$sqlInsGado = "select * from institucion where inst_codi = $instSubrogado";
$rsInsGado = $db->conn->query($sqlInsGado);
$institucionGado=$rsInsGado->fields["INST_NOMBRE"];

//Institucion subrogado
$mail = "<html><title>Subrogacion de Puesto - Quipux</title>";
$mail .= "<body><center><h1>QUIPUX</h1><br /><h2>Sistema de Gesti&oacute;n Documental</h2><br /><br /></center>";
$mail .= "Estimado(a). <br /><br />";
$mail .= "Se notifica que se ha realizado la Subrogaci&oacute;n de Puesto en la Instituci&oacute;n <b>$instNombre</b>, para el periodo desde: ";
$mail .= " $desde hasta: $hasta <br>&nbsp;<br>";
$mail .= "<b>Subrogante: </b><br>&nbsp;<br>";
$mail .= "Funcionario P&uacute;blico: $nombresubrogante / $cargosubrogante al Puesto $encargado / $dependenciaSubrogacion / $institucionGate<br>&nbsp;<br>";
$mail .= "<b>Subrogado: </b><br>&nbsp;<br>";
$mail .= "Funcionario P&uacute;blico: $nombresubrogado / $cargosubrogado / $dependenciaSubrogacion / $institucionGado";
$mail .= "<br /><br />Saludos cordiales,<br /><br />Soporte Quipux.";
$mail .= "<br /><br /><b>Nota: </b>Este mensaje fue enviado autom&aacute;ticamente por el sistema, por favor no lo responda.";
$mail .= "<br />Si tiene alguna inquietud respecto a este mensaje, comun&iacute;quese con <a href='mailto:$cuenta_mail_soporte'>$cuenta_mail_soporte</a>";
$mail .= "</body></html>";    

//En caso de que la subrogacion sea de Ministro a Ministro, se notifica por correo
//a los administradores
//ADMINISTRADOR
if ($instSubrogado !='' and $instSubrogante!='')
    $andlike = " and usua_login like ('UADM%')";
    if ($instSubrogado==$instSubrogante){        
        $sqlInstituciones= "select usua_email from usuarios where inst_codi = $instSubrogante $andlike";        
        $rsInstituciones = $db->conn->query($sqlInstituciones);
        $emailAdminInst=$rsInstituciones->fields["USUA_EMAIL"];
        enviarMail($mail, "Quipux: Subrogacion de Puesto.", $emailAdminInst, "Administrador Quipux", $ruta_raiz);
        //enviarMail($mail, "Quipux: Subrogacion de Cargo.", 'josedavo@hotmail.com', "Administrador Quipux", $ruta_raiz);
    }else{
        $sqlInstituciones= "select usua_email from usuarios where inst_codi in ($instSubrogante,$instSubrogado) $andlike";
        $rsInstituciones = $db->conn->query($sqlInstituciones);
        while (!$rsInstituciones->EOF) { //Cargamos los permisos especiales  
            //inserto los nuevos permisos al subrogado
            $emailAdminInst=$rsInstituciones->fields["USUA_EMAIL"];
            enviarMail($mail, "Quipux: Subrogacion de Puesto.", $emailAdminInst, "Administrador Quipux", $ruta_raiz);
            //enviarMail($mail, "Quipux: Subrogacion de Cargo.", 'josedavo@hotmail.com', "Administrador Quipux", $ruta_raiz);
            $rsInstituciones->MoveNext();
        }
    }
//SUPER ADMINISTRADOR
//echo $sqlInstituciones;
$sqlAdministrador = "select usua_email from usuarios where usua_login = 'UADMINISTRADOR'";
//echo $sqlAdministrador;
$rsAdmin = $db->conn->query($sqlAdministrador);
$emailAdmin = $rsAdmin->fields["USUA_EMAIL"];
enviarMail($mail, "Quipux: Subrogacion de Puesto.", $emailAdmin, "Administrador Quipux", $ruta_raiz);
enviarMail($mail, "Quipux: Subrogacion de Puesto.", $emailsubrogante, $nombresubrogante, $ruta_raiz);
enviarMail($mail, "Quipux: Subrogacion de Puesto.", $emailsubrogado, $nombresubrogado, $ruta_raiz);
//funcion para insertar permisos
function ins_permisos_usr($db,$usr_subrogado,$usr_codigo,$recper){
   $sqlPerimisos = "select * from permiso_usuario where usua_codi = $usr_subrogado";
        //echo $sqlPerimisos;
        $rspermisos = $db->conn->query($sqlPerimisos);
        while (!$rspermisos->EOF) { //Cargamos los permisos especiales  
            //inserto los nuevos permisos al subrogante (nuevo usuario)
            $recperm["ID_PERMISO"] = $db->conn->qstr($rspermisos->fields["ID_PERMISO"]);
            $recperm["USUA_CODI"] = $usr_codigo;
            $sqlPermExiste = "select id_permiso, usua_codi from permiso_usuario where id_permiso = ".$recperm["ID_PERMISO"]." and usua_codi = ".$usr_codigo;
            $rsExiste=$db->conn->query($sqlPermExiste);
            $permisoExiste = $rsExiste->fields["ID_PERMISO"];
            if (trim($permisoExiste)=='')
                $ok2 = $db->conn->Replace("PERMISO_USUARIO", $recperm, "", false,false,true,false);
                $rspermisos->MoveNext();
        }
        $sqlPermExiste = "select id_permiso, usua_codi from permiso_usuario where id_permiso = 29 and usua_codi = ".$usr_codigo;
        $rsExiste=$db->conn->query($sqlPermExiste);
        //permiso de usuario publico
        $permisoExiste = $rsExiste->fields["ID_PERMISO"];
           if (trim($permisoExiste)=='')
           {
               $recperm["ID_PERMISO"] = 29;
               $recperm["USUA_CODI"] = $usr_codigo;
               $db->conn->Replace("PERMISO_USUARIO", $recperm, "", false,false,true,false);
           } 
}
?>


<html>
    <?echo html_head(); //Imprime el head definido para el sistema?>
<body>
    <form name="frmConfirmaCreacion" action="../usuarios/mnuUsuarios.php" method="post">
    <center>
        <br /><br />
       <br />
        <table width="40%" border="2" align="center" class="t_bordeGris">
            <tr>
            <td width="100%" height="30" class="listado2">
           <?php if ($ok2==2){
               $nombrUsr = $recsbrte["USUA_APELLIDO"]." ".$recsbrte["USUA_NOMB"];
               $nombreUsr = str_replace(array("E","'"), '', $nombrUsr);
               ?>            
                <span class=etexto><center><B>Los cambios en el usuario <?=$nombreUsr?><br/> se realizaron correctamente.</B></center></span>
           <?php }else{ ?>
                <span class=etexto><center><B>Existe un problema, por favor comuníquese con el Administrador</B></center></span>
           <?php } ?>
            </td>
            </tr>
            <tr>
            <td height="30" class="listado2">
                <center><input class="botones" type="submit" name="Submit" value="Aceptar"></center>
            </td>
            </tr>
        </table>
    </center>
    </form>
</body>
</html>