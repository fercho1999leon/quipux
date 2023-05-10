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
require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
require_once "$ruta_raiz/obtenerdatos.php";  //formar la observacion de edicion
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/include/tx/Tx.php";




//permite grabar el usuario, solo a campos requeridos
$graba_usr = 1;


if ($_SESSION["usua_admin_sistema"] != 1) {
    echo html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
    die("");
}
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/include/tx/Tx.php";
include_once "$ruta_raiz/backup/permiso_autoriza_grabar_ajax.php";

$tx=new Tx($db);
include_once "../ciudadanos/util_ciudadano.php";
$ciud = New Ciudadano($db);

$recargar=true;
$radicado=array();

$usr_cedula = trim($usr_cedula);

if (!isset($usr_estado) || $usr_estado == null) {
    $usr_estado = 0;
    if($tiene_subrogacion == 1)
        $usr_estado = 1;
}
if ($accion==1) $usr_estado = 1;
if($_REQUEST['usr_area_responsable']==""){
    $usr_area_responsable=0;
    $usr_sumilla=strtolower($usr_sumilla);
}elseif($_REQUEST['usr_area_responsable']==0){
    $sql = "select * from usuarios where depe_codi=$usr_depe and usua_responsable_area=1 ";
    $rs = $db->conn->query($sql);
    
    if(!$rs->EOF) {
                
                die("<script>alert('Ya existe un responsable de documentación para esta Area'); history.back();</script>");
    }else{
        $usr_area_responsable=1;
        $usr_sumilla=strtoupper($usr_sumilla);
    }
}




if($usr_estado!=0) {
	if ($accion == 2) {
        $sql2 .= " and usua_codi<>$usr_codigo";
    } else {
        $sql2 = "";
    }
	if ($usr_perfil==1) {
	    $sql = "select * from usuario where cargo_tipo=1 and usua_esta=1 and depe_codi=$usr_depe" . $sql2;
	    $rs = $db->conn->query($sql);
	    if(!$rs->EOF) {
	    	include "./adm_usuario.php";
	    	die("<script>alert('Ya existe el usuario \"Jefe\" para esta Área')</script>");
	    }
	}
	if ($usr_perfil==2) {
	    $sql = "select * from usuario where cargo_tipo=2 and usua_esta=1 and depe_codi=$usr_depe" . $sql2;
	    $rs = $db->conn->query($sql);
	    if(!$rs->EOF) {
	    	include "./adm_usuario.php";
	    	die("<script>alert('Ya existe el usuario \"Asistente\" para esta Área')</script>");
	    }
	}
}

$record = array();
$flag_copiar_contrasena = false;

if ($accion == 1) {
    $record["INST_CODI"] = $_SESSION["inst_codi"];
    $usr_codigo = $db->nextId("usuarios_usua_codi_seq");
    $flag_copiar_contrasena = true;

} else {
    $sql = "select usua_cedula from usuarios where usua_codi=$usr_codigo";
    $rs= $db->conn->query($sql);
    if ($rs && !$rs->EOF && $rs->fields['USUA_CEDULA']!=$usr_cedula) {
        $flag_copiar_contrasena = true;        
    }
   
}

if ($flag_copiar_contrasena) {
    $sql = "select usua_pasw from usuarios where usua_nuevo=1 and usua_esta=1 and usua_cedula='$usr_cedula' and usua_codi<>$usr_codigo";
    $rs= $db->conn->query($sql);
    if ($rs && !$rs->EOF) {
        $record["USUA_PASW"] = $db->conn->qstr($rs->fields['USUA_PASW']);
        unset ($usr_nuevo);
        $mensaje = "<b>La contrase&ntilde;a registrada es la que se encuentra definida para las otras cuentas del usuario.</b><br>";
    }
}

$usuarioSubrEst=usrMensajeSubrogacion($db,$usr_codigo,"perfil");

$record["USUA_CODI"] = $usr_codigo;

if ($usr_depe=='' || $usr_depe==0) $graba_usr = 0;
$record["DEPE_CODI"] = $usr_depe;
//se desactiva al usuario
if ($usr_estado==0) {
    $usr_perfil=0;
    $usr_login = "l$usr_codigo";
    $record["USUA_CEDULA"] = $db->conn->qstr(substr($usr_cedula,0,10)."-$usr_codigo");

    //Documentos del usuario desactivado
    $sql = "select radi_nume_radi from radicado where esta_codi in (1,2) and radi_usua_actu=$usr_codigo";
    
    $rs= $db->conn->query($sql);
    unset($radicado);
    while (!$rs->EOF) {
        $radicado[]= $rs->fields['RADI_NUME_RADI'];
        $rs->MoveNext();
    }
    if($usr_destino!=0 and count($radicado)>0) {
            $tx->reasignar( $radicado, $usr_codigo, $usr_destino, 'Reasignado por desactivación de usuario',"",true);
    }

     //Tareas del usuario desactivado   
    if($usr_destino!=0){
        $tx->cambiarUsuarioTareasEnviadas($usr_codigo, $usr_destino);
        $tx->cambiarUsuarioTareasRecibidas($usr_codigo, $usr_destino);
    }
    
} else {//si es nuevo o modifica   
    if($usr_tipo_id==1)
        $usr_login = "UUSR".strtoupper($usr_cedula);
    else
        $usr_login = "U".substr($usr_cedula,0,10);
   
    if ($accion == 2) {
        $sql = "select usua_login,usua_responsable_area from usuarios where usua_codi=$usr_codigo and (usua_login like 'UADMIN%')";
        $rs = $db->conn->query($sql);
        if(!$rs->EOF){ 
            $usr_login = $rs->fields['USUA_LOGIN'];            
        }
    }
    if ($usr_cedula=='' || $usr_cedula==0) $grabar_usr = 0;
    if($usr_tipo_id==1)
        $record["USUA_CEDULA"] = $db->conn->qstr($usr_cedula);        
    else
        $record["USUA_CEDULA"] = $db->conn->qstr(substr($usr_cedula,0,10));
}
$record["USUA_LOGIN"]           = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($usr_login))));
$record["TIPO_IDENTIFICACION"]  = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($usr_tipo_id))));
if ($usr_nombre=='' || $usr_nombre==0) $grabar_usr = 0;
$record["USUA_NOMB"]            = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($usr_nombre))));
if ($usr_apellido=='' || $usr_apellido==0) $grabar_usr = 0;
$record["USUA_APELLIDO"]        = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($usr_apellido))));
$record["USUA_TITULO"]          = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($usr_titulo))));
$record["USUA_ABR_TITULO"]      = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($usr_abr_titulo))));
if ($usr_cargo=='' || $usr_cargo==0) $grabar_usr = 0;
$record["USUA_CARGO"]           = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($usr_cargo))));
if ($usr_cargo_cabecera=='' || $usr_cargo_cabecera==0) $grabar_usr = 0;
$record["USUA_CARGO_CABECERA"]  = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($usr_cargo_cabecera))));
if ($usr_sumilla=='' || $usr_sumilla==0) $grabar_usr = 0;
$record["USUA_SUMILLA"]         = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($usr_sumilla))));
if ($_SESSION["inst_codi"]==1) 
    $record["INST_NOMBRE"]      = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($usr_inst_nombre))));

if ($usr_estado==1){  
    if (isset($_POST['usr_area_responsable'])){        
        $record["USUA_RESPONSABLE_AREA"]       = 1;
        $usr_sumilla= strtoupper($usr_sumilla);
        $record["USUA_SUMILLA"]=$db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($usr_sumilla))));
        
    }else{ 
        $sql = "select usua_login,usua_responsable_area from usuarios where usua_codi=".$record["USUA_CODI"];
       
        $rs = $db->conn->query($sql);
        if(!$rs->EOF){ 
            $usr_responsable = $rs->fields['USUA_RESPONSABLE_AREA'];            
        }
        
            if($usr_responsable==1){
                
                $usr_sumilla=strtoupper($usr_sumilla);
                $record["USUA_RESPONSABLE_AREA"]       = 1;
                $record["USUA_SUMILLA"]=$db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($usr_sumilla))));
            }
            else{
                $record["USUA_RESPONSABLE_AREA"]       = 0;
                $record["USUA_SUMILLA"]=$db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($usr_sumilla))));
            }
    }
}else{
    $record["USUA_RESPONSABLE_AREA"]       = 0;
    $record["USUA_SUMILLA"]=$db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($usr_sumilla))));
}


if (trim($usr_perfil)){
    $record["CARGO_TIPO"] = limpiar_sql(trim($usr_perfil));
}
else{    
    $subrogacion = usrMensajeSubrogacion($db,$usr_codigo,"perfil");    
    if ($subrogacion==" (Subrogado)")
        $record["CARGO_TIPO"]  = 1;
    else
        $record["CARGO_TIPO"] = 0;       
}
if ($usr_email=='' || $usr_email==0) $grabar_usr = 0;
$record["USUA_EMAIL"]           = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($usr_email))));
$record["USUA_OBS"]             = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($usr_obs))));
if($codi_ciudad != "" or $codi_ciudad != 0)
    $record["CIU_CODI"]         = limpiar_sql(trim($codi_ciudad));
$record["USUA_ESTA"]            = limpiar_sql(trim($usr_estado));
$record["USUA_NUEVO"]           = 1;

$record["USUA_DIRECCION"]       = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($usr_direccion))));
$record["USUA_TELEFONO"]        = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($usr_telefono))));
$record["USUA_CELULAR"]         = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($usr_celular))));
//Datos del usuario que modifico al funcionario la ultima ves.
$record["USUA_CODI_ACTUALIZA"]  = $_SESSION['usua_codi'];
$record["USUA_FECHA_ACTUALIZA"] = "CURRENT_TIMESTAMP";

// Guardar Path de firma digitalizada
if (trim($_FILES["firmaDigitalizada"]['name'])!="")
    $record["USUA_FIRMA_PATH"] = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial('bodega/firmas/'.$usr_codigo.'.'.$_POST["extarch"]))));

$record["USUA_TIPO_CERTIFICADO"] = 0;
if (isset($_POST["usr_permiso_19"])) {
    $record["USUA_TIPO_CERTIFICADO"] = 0 + $_POST["usr_tipo_certificado"];
    if ($record["USUA_TIPO_CERTIFICADO"] == 0) $record["USUA_TIPO_CERTIFICADO"] = 1;
}

//Armar observacion de campos modificados
if($accion == 1)
    $record["USUA_OBS_ACTUALIZA"] =  "'Registro Nuevo'";
else
    $record["USUA_OBS_ACTUALIZA"] = "'".ObtenerObservacionFuncionario($usr_codigo, $record, $db)."'";

//validacion usuarios
if ($graba_usr==1){
    $sqlUsr = "select * from usuarios where usua_codi = $usr_codigo";
    $rs_old = $db->conn->query($sqlUsr);
    $ok1 = $db->conn->Replace("USUARIOS", $record, "USUA_CODI", false,false,true,false);
    $rs_new = $db->conn->query($sqlUsr);
    $ciud->grabar_log_tabla('LOG_USR_CIUDADANOS',$rs_old, $rs_new, $_SESSION['usua_codi'],2,$db);
}

/************************************* GRABAR FIRMA *************************************/
if($ok1){

    if (trim($_FILES["firmaDigitalizada"]['name'])!="") {
        $nomb_arch = $_FILES["firmaDigitalizada"]['name'];
        $rutaFirma = "$ruta_raiz/bodega/firmas/".$usr_codigo.'.'.$_POST["extarch"];
        move_uploaded_file($_FILES["firmaDigitalizada"]['tmp_name'],$rutaFirma);
    }
}

/*
// Añadimos los permisos al usuario
unset($record);
$sql = "select id_permiso from permiso where estado=1";
if ($_SESSION["usua_codi"]!=0) $sql .= " and perfil <> 5";
$rs = $db->conn->query($sql);

while ($rs && !$rs->EOF) { //Cargamos los permisos del usuario   
 */ 
//Buscar permisos de usuario
$permisosAnteriores = $ciud->permisosUsr($usr_codigo);//cargar permisos
$permisosAnteriores = str_replace(",,", ",", $permisosAnteriores);//seteo
$permisosAnteriores = substr($permisosAnteriores, 1);//seteo
$permisosAnteriores = substr($permisosAnteriores,0,-1);//seteo
unset($codPermiAnteriores);
$codPermiAnteriores = explode(",",$permisosAnteriores);
$codigos = limpiar_sql($_POST['codigo_permisos']);
$codigos = str_replace(",,", ",", $codigos);
$permisos = array();
unset($permisos);
$permisos = explode(",", $codigos);

foreach ($permisos as $tmp=>$datos){
    $record["ID_PERMISO"] = $permisos[$tmp];//$rs->fields['ID_PERMISO'];
        $record["USUA_CODI"] = $usr_codigo;
        $id_permiso =  $permisos[$tmp];        
       if ($id_permiso!=''){          
           if (!in_array($id_permiso, $codPermiAnteriores, false)){               
            //guardar
            $ciud->guardar_permisos($usr_codigo, $id_permiso,true);
            }
       }
}

$codigos_eli = limpiar_sql($_POST['codigo_permisos_eli']);
$codigos_eli = str_replace(",,", ",", $codigos_eli);

$permisos_eli = array();
unset($permisos_eli);
$permisos_eli = explode(",", $codigos_eli);

foreach ($permisos_eli as $tmp=>$datos){
    $record["ID_PERMISO"] = $permisos_eli[$tmp];//$rs->fields['ID_PERMISO'];
       $record["USUA_CODI"] = $usr_codigo;
       $id_permiso =  $permisos_eli[$tmp];
        if ($id_permiso!=''){            
            if (in_array($id_permiso, $codPermiAnteriores, true))
            $ciud->guardar_permisos($usr_codigo, $id_permiso,false);
        }
}
        




if ($usuarioSubrEst==" (Subrogante)"){
    //$record["USUA_CODI"] = $usr_codigo;
    //$record["ID_PERMISO"] = 29;
    $ciud->guardar_permisos($usr_codigo, 29,'on');
    //$ok1 = $db->conn->Replace("PERMISO_USUARIO", $record, "", false,false,true,false);
    
}

$usr_nombre = $usr_nombre . " " . $usr_apellido;

if (isset($_POST["usr_contrasena"]) && $_POST["usr_contrasena"] != 'off' && $usr_estado!=0) {
    $usr_tipo = 1;    
    include "cambiar_password_mail.php";
    $mensaje = "<b>La contrase&ntilde;a del usuario ha cambiado y se le notific&oacute; a su cuenta de correo electr&oacute;nico.</b><br />";
    $rec["usua_codi"] = $usr_codigo;
    $rec["usua_codi_actualiza"] = $_SESSION["usua_codi"];
    $rec["accion"] = 1;
    $rec["id_permiso"] = 0;
    $rec["fecha_actualiza"] = $db->conn->sysTimeStamp;    
    $db->conn->Replace("log_usr_permisos", $rec, "",false,false,true,false);
}
//estado = 1 activo, 0 inactivos
if (trim($usr_estado)==0){//esta inactivo
    if($_POST['ban_compartida'] == 'S'){       
         //elimina la bandeja para usuarios normales
        //si es jefe       
        if (trim($usr_perfil)==0){            
            eliminaBandeja($usr_codigo,$db,$ciud);
        }
        else{
           
             $eliminaCompartida = 'delete from bandeja_compartida where usua_codi = '.$usr_codigo;
             $db->conn->Execute($eliminaCompartida);
        }
    }
    //Eliminar al usuario de las listas
    if ($usr_codigo!='')
        eliminarDlista($usr_codigo,$db);
}

if(trim($_POST['eliminar_compartida']=='S') || limpiar_sql(trim($usr_estado))==0){
     eliminaBandeja($usr_codigo,$db,$ciud);
     $eliminaCompartida = 'delete from bandeja_compartida where usua_codi = '.$usr_codigo;
     $db->conn->Execute($eliminaCompartida);
}
function eliminaBandeja($usr_codigo,$db,$ciud){
    $sqlBandeja = 'select * from bandeja_compartida where usua_codi_jefe = '.$usr_codigo;
    $sqlUp = "select * from bandeja_compartida where usua_codi_jefe = $usr_codigo";
    $rs_old = $db->conn->query($sqlUp);
            $rsBan = $db->conn->query($sqlBandeja);
            while ($rsBan && !$rsBan->EOF) {
                $eliminaCompartida = 'delete from bandeja_compartida where usua_codi_jefe = '.$usr_codigo;
                $db->conn->Execute($eliminaCompartida);
                $rsBan->MoveNext();
            }
    $rs_new = $db->conn->query($sqlUp);
    $ciud->grabar_log_tabla('LOG_USR_CIUDADANOS',$rs_old, $rs_new, $_SESSION['usua_codi'],6,$db);
}

//Permisos Especiales backup
$codigos_esp = limpiar_sql($_POST['txt_depe_guardar']);
$codigos_esp = str_replace(",,", ",", $codigos_esp);
$codigos_esp_eli = limpiar_sql($_POST['txt_depe_guardar_eli']);
$codigos_esp_eli = str_replace(",,", ",", $codigos_esp_eli);

$mensaje.= guardarBackUp($db,$usr_codigo,33,$codigos_esp,$codigos_esp_eli,$_SESSION["inst_codi"]);



?>
<?php
$sqlper = "select id_permiso as permiso_adm from permiso_usuario where usua_codi = $usr_codigo 
and id_permiso=12";
$rsper = $db->conn->query($sqlper);
if ($rsper->EOF){
    $id_permiso12 = $rs->fields['PERMISO_ADM'];
    if ($id_permiso12!='' || $id_permiso12!=0)
       $db->conn->query("delete from usuario_dependencia where usua_codi = $usr_codigo");
}


?>
<html>
    <?echo html_head(); //Imprime el head definido para el sistema?>
<body>
    <form name="frmConfirmaCreacion" action="adm_usuario.php?accion=2&usr_codigo=<?=$usr_codigo?>" method="post">
    <?php 
    if ($graba_usr==1){
        $accion=2;
        ?>
    <center>
        <br><br>
        <table width="60%" border="1" align="center" class="borde_tab">
            <?php 
            if ($mensaje!=''){ ?>
            <tr>
                <td class="listado2" align="center">
                    <?=$mensaje?>
                </td>
            </tr>
            <?php } ?>
            <tr>
            <td width="100%" height="30" class="listado2">
            <input type="hidden" id="nuevo_codigo_usr" name="nuevo_codigo_usr" value="<?=$usr_codigo?>"/>
            <? if ($accion==1) { ?>
                <font size="2"><center>El usuario <?=$usr_nombre?><br/> fue creado correctamente</center></font>
            <? } else { ?>
                <font size="2"><center>Los cambios en el usuario <?=$usr_nombre?><br/> se realizaron correctamente</center></font>
            <? } 
           
            ?>
            </td>
            </tr>
            <tr>
            <td height="30" class="listado2">
                <center><input class="botones" type="submit" name="Submit" value="Aceptar"></center>
            </td>
            </tr>
        </table>
    </center>
    <?php }else{//si no guarda el usuario por problema de validacion
        echo '<center><br /><br />
            <table width="40%" border="0" align="center" class="t_bordeGris">
            <tr><td width="100%" height="30" class="listado2"><font size="2"><center><B>
            Existió un problema al guardar el usuario, comuníquese con el Administrador
                    del Sistema.</B></center></font></td></tr>
            </table></center>';
    }?>
    </form>
</body>
</html>