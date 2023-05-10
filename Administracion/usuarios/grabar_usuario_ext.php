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
/*************************************************************************************/
/*                                                                                   */
/*************************************************************************************/

/**
*	Autor			Iniciales		Fecha (dd/mm/aaaa)
*
*
*	Modificado por		Iniciales		Fecha (dd/mm/aaaa)
*
*
*	Comentado por		Iniciales		Fecha (dd/mm/aaaa)
*	Sylvia Velasco		SV			02-12-2008
**/
$ruta_raiz = "../..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
require_once("$ruta_raiz/obtenerdatos.php"); //formar la observacion de edicion
include_once "$ruta_raiz/funciones_interfaz.php";
p_register_globals(array());


//$accion = $_GET['accion']; //agregado por register_globals

session_start();
if($_SESSION["usua_admin_sistema"]!=1 and $_SESSION["usua_perm_ciudadano"]!=1) {
    echo html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
    die("");
}

include_once "$ruta_raiz/rec_session.php";

$record = array();
$recargar=true;

$tmp_cedula = $ciu_cedula;
if (!isset($ciu_nuevo)) $ciu_nuevo = 1;
if ($ciu_password == 1) {
    $ciu_nuevo = 0;
}
//variable validar en servidor el grabar
$grabar_ciu = 1;
// Verificar si se va a insertar (accion = 1) o actualizar (else) a un ciudadano.
// En el caso de que el ciudadano no ingrese su numero de cedula se genera un numero automaticamente igual a 9999999999 menos el codigo del usuario
$flag_copiar_contrasena = false;

if ($accion==1) {
    $record["INST_CODI"] = $_SESSION["inst_codi"];

    $ciu_codigo = $db->nextId("usuarios_usua_codi_seq");;
    $flag_copiar_contrasena = true;

    $ciu_nuevo = 0;
} else {

    // Valido el cambio de contraseña segun la cedula actual del usuario
    $sql = "select usua_cedula from usuario where usua_codi=$ciu_codigo";
    $rs= $db->conn->query($sql);
    if ($rs && !$rs->EOF && $rs->fields['USUA_CEDULA']!=$tmp_cedula) {
        $flag_copiar_contrasena = true;
    }
}
if ($ciu_sincedula==1)
    $tmp_cedula = 9999999999-$ciu_codigo;
if (substr($tmp_cedula,0,2)=="99" or trim($tmp_cedula)=="")
    $tmp_cedula = 9999999999-$ciu_codigo;

if ($flag_copiar_contrasena) {
    $sql = "select usua_pasw from usuario where usua_nuevo=1 and usua_esta=1 and usua_cedula='$tmp_cedula' and usua_codi<>$ciu_codigo";
    $rs= $db->conn->query($sql);
    if ($rs && !$rs->EOF) {
        $record["CIU_PASW"] = $db->conn->qstr($rs->fields['USUA_PASW']);
        $ciu_nuevo=1;
        $mensaje = "<b>La contrase&ntilde;a registrada es la que se encuentra definida para las otras cuentas del usuario.</b><br>";
    }
}


// Verifico si existen usuarios o ciudadanos creados con el mismo numero de cedula
$sql = "select * from usuario where usua_cedula='$tmp_cedula'" . $sql2;


if(isset ($_POST["desactivar"]))
    $desactivar = $_POST["desactivar"];
else
    $desactivar = "1";

$record["CIU_CODIGO"]       = $ciu_codigo;

if($desactivar==0 && (!isset($_POST['ciu_desactiva']) || $_POST['ciu_desactiva']==null)){
    //Si se va ha desactivar el ciudadano se modifica el numero de cedula y el login
    $tmp_cedula = substr($tmp_cedula,0,10)."-$ciu_codigo";
    $ciu_estado = "0";

    $record["CIU_ESTADO"]       = $ciu_estado;
    $record["CIU_CEDULA"]       = $db->conn->qstr(limpiar_sql(trim($tmp_cedula)));

}
else
{
    //$tmp_cedula = substr($tmp_cedula,0,10);
    $ciu_estado = "1";

    $record["CIU_ESTADO"]       = $ciu_estado;
    $record["CIU_CEDULA"]       = $db->conn->qstr(limpiar_sql(trim($tmp_cedula)));
    $record["CIU_DOCUMENTO"]    = $db->conn->qstr(limpiar_sql(trim($ciu_documento)));
    
    if (trim($ciu_nombre)=='') 
        $grabar_ciu=0;
    $record["CIU_NOMBRE"]       = $db->conn->qstr(limpiar_sql(trim($ciu_nombre)));
    
    if (trim($ciu_apellido)=='') 
        $grabar_ciu=0;
    
    $record["CIU_APELLIDO"]     = $db->conn->qstr(limpiar_sql(trim($ciu_apellido)));
    $record["CIU_TITULO"]       = $db->conn->qstr(limpiar_sql(trim($ciu_titulo)));
    $record["CIU_ABR_TITULO"]   = $db->conn->qstr(limpiar_sql(trim($ciu_abr_titulo)));
    $record["CIU_EMPRESA"]      = $db->conn->qstr(limpiar_sql(trim($ciu_empresa)));
    $record["CIU_CARGO"]        = $db->conn->qstr(limpiar_sql(trim($ciu_cargo)));
    $record["CIU_DIRECCION"]    = $db->conn->qstr(limpiar_sql(trim($ciu_direccion)));
    $record["CIU_EMAIL"]        = $db->conn->qstr(limpiar_sql(trim($ciu_email)));
    $record["CIU_TELEFONO"]     = $db->conn->qstr(limpiar_sql(trim($ciu_telefono)));

    if(isset($ciu_ciudad))
        $record["CIUDAD_CODI"] = $ciu_ciudad;
    else
        $record["CIUDAD_CODI"] = "0";

    if (trim($ciu_nuevo)!="")  $record["CIU_NUEVO"] = "$ciu_nuevo";
}

//Datos del usuario que modifico al ciudadano la ultima ves.
$record["USUA_CODI_ACTUALIZA"] = $_SESSION['usua_codi'];
$record["CIU_FECHA_ACTUALIZA"] = "CURRENT_TIMESTAMP";

//Armar observacion de campos modificados
if($accion == 1)
    $record["CIU_OBS_ACTUALIZA"] =  "'Registro Nuevo'";
else
    $record["CIU_OBS_ACTUALIZA"] = "'".ObtenerObservacionCiudadano($ciu_codigo, $record, $db)."'";
if ($grabar_ciu==1)
$ok1 = $db->conn->Replace("CIUDADANO", $record, "CIU_CODIGO", false,false,true,false);
//Si son ciudadanos con nombre homónimos no modificar el ciudadano existente crear nuevo y eleminar de la tabla tmp.

$upSql="update ciudadano_tmp set ciu_estado = 0 where ciu_codigo=$ciu_codigo";

$db->conn->query($upSql);

// Cambiamos la contraseña del usuario y le mandamos un mail
if ($ciu_nuevo==0 and trim($ciu_email)!="") {
    $usr_tipo = 2;
    $usr_codigo = $ciu_codigo;
    $usr_nombre = $ciu_nombre . " " . $ciu_apellido;
    $usr_login = "U".$tmp_cedula;
    $usr_cedula = $tmp_cedula;
    $usr_email = $ciu_email;
    include "cambiar_password_mail.php";

}

if (isset($pagina_anterior) and trim($ciu_email)!="") {
    $mail = "<html><title>Informaci&oacute;n Quipux</title>";
    $mail .= "<body><center><h1>QUIPUX</h1><br /><h2>Sistema de Gesti&oacute;n Documental</h2><br /><br /></center>";
    $mail .= "Estimado(a) $ciu_nombre $ciu_apellido.<br /><br />";
    $mail .= "Se han realizado los siguientes cambios en la informaci&oacute;n personal de su usuario:<br /><br />";
    $mail .= "<table border='0'>
              <tr><td><b>C&eacute;dula:</b></td><td>$tmp_cedula</td></tr>
              <tr><td><b>Nombre:</b></td><td>$ciu_nombre</td></tr>
              <tr><td><b>Apellido:</b></td><td>$ciu_apellido</td></tr>
              <tr><td><b>Abr. T&iacute;tulo:</b></td><td>$ciu_abr_titulo</td></tr>
              <tr><td><b>T&iacute;tulo:</b></td><td>$ciu_titulo</td></tr>
              <tr><td><b>Instituci&oacute;n:</b></td><td>$ciu_empresa</td></tr>
              <tr><td><b>Cargo:</b></td><td>$ciu_cargo</td></tr>
              <tr><td><b>Direcci&oacute;n:</b></td><td>$ciu_direccion</td></tr>
              <tr><td><b>E-mail:</b></td><td>$ciu_email</td></tr>
              </table>";
    $mail .= "<br /><br />Le recordamos que para acceder al sistema deber&aacute; hacerlo con el usuario &quot;$tmp_cedula&quot;
              ingresando a <a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>";
    $mail .= "<br /><br />Saludos cordiales,<br /><br />Soporte Quipux.";
    $mail .= "<br /><br /><b>Nota: </b>Este mensaje fue enviado autom&aacute;ticamente por el sistema, por favor no lo responda.";
    $mail .= "<br />Si tiene alguna inquietud respecto a este mensaje, comun&iacute;quese con <a href='mailto:$cuenta_mail_soporte'>$cuenta_mail_soporte</a>";
    $mail .= "</body></html>";
    enviarMail($mail, "Quipux: Actualización de datos.", $ciu_email, "$ciu_nombre $ciu_apellido", $ruta_raiz);
}

if (isset($_POST["ciu_codigo_eliminar"])) {
    if ($_POST["ciu_codigo_eliminar"]!=$ciu_codigo) {
        $ciu_codigo_eliminar = limpiar_sql($_POST["ciu_codigo_eliminar"]);
        //movemos los documentos en los que el ciudadano es el remitente
        $sql = "select radi_nume_radi, radi_usua_rem from radicado where radi_usua_rem like '%-$ciu_codigo_eliminar-%';";
        $rs = $db->conn->query($sql);
        while (!$rs->EOF) {
            unset ($record);
            $record["RADI_NUME_RADI"] = $rs->fields["RADI_NUME_RADI"];
            $record["RADI_USUA_REM"] = $db->conn->qstr(str_replace("-$ciu_codigo_eliminar-","-$ciu_codigo-",$rs->fields["RADI_USUA_REM"]));
            $ok1 = $db->conn->Replace("RADICADO", $record, "RADI_NUME_RADI", false,false,true,false);
            $rs->MoveNext();
        }

        //movemos los documentos en los que el ciudadano es el destinatario
        $sql = "select radi_nume_radi, radi_usua_dest from radicado where radi_usua_dest like '%-$ciu_codigo_eliminar-%';";
        $rs = $db->conn->query($sql);
        while (!$rs->EOF) {
            unset ($record);
            $record["RADI_NUME_RADI"] = $rs->fields["RADI_NUME_RADI"];
            $record["RADI_USUA_DEST"] = $db->conn->qstr(str_replace("-$ciu_codigo_eliminar-","-$ciu_codigo-",$rs->fields["RADI_USUA_DEST"]));
            $ok1 = $db->conn->Replace("RADICADO", $record, "RADI_NUME_RADI", false,false,true,false);
            $rs->MoveNext();
        }

        //movemos los documentos en los que el ciudadano tiene copias (cca)
        $sql = "select radi_nume_radi, radi_cca from radicado where radi_cca like '%-$ciu_codigo_eliminar-%';";
        $rs = $db->conn->query($sql);
        while (!$rs->EOF) {
            unset ($record);
            $record["RADI_NUME_RADI"] = $rs->fields["RADI_NUME_RADI"];
            $record["RADI_CCA"] = $db->conn->qstr(str_replace("-$ciu_codigo_eliminar-","-$ciu_codigo-",$rs->fields["RADI_CCA"]));
            $ok1 = $db->conn->Replace("RADICADO", $record, "RADI_NUME_RADI", false,false,true,false);
            $rs->MoveNext();
        }
        // desactivamos el usuario

        $sql = "select ciu_cedula, ciu_email from ciudadano where ciu_codigo=$ciu_codigo_eliminar";
        $rs = $db->conn->query($sql);
        $old_cedula = $rs->fields["CIU_CEDULA"];

        unset ($record);
        $record["CIU_CODIGO"] = "$ciu_codigo_eliminar";
        $record["CIU_ESTADO"] = "0";
        $record["CIU_CEDULA"] = $db->conn->qstr("$old_cedula-$ciu_codigo_eliminar");

        //Datos del usuario que modifico al ciudadano la ultima ves.
        $record["USUA_CODI_ACTUALIZA"] = $_SESSION['usua_codi'];
        $record["CIU_FECHA_ACTUALIZA"] = "CURRENT_TIMESTAMP";

        $ok1 = $db->conn->Replace("CIUDADANO", $record, "CIU_CODIGO", false,false,true,false);

        $mail = "<html><title>Informaci&oacute;n Quipux</title>";
        $mail .= "<body><center><h1>QUIPUX</h1><br /><h2>Sistema de Gesti&oacute;n Documental</h2><br /><br /></center>";
        $mail .= "Estimado(a) $ciu_nombre $ciu_apellido.<br /><br />";
        $mail .= "Se ha unificado la información de los usuarios &quot;$old_cedula&quot; y &quot;$tmp_cedula&quot; en uno solo.<br /><br />";
        $mail .= "Todos los documentos pertenecientes al usuario &quot;$old_cedula&quot; fueron movidos a las bandejas del usuario &quot;$tmp_cedula&quot; y el usuario &quot;$old_cedula&quot; ha sido desactivado.<br /><br />";
        $mail .= "Le recordamos que para acceder al sistema deber&aacute; hacerlo con el usuario &quot;$tmp_cedula&quot;
                  ingresando a <a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>";
        $mail .= "<br /><br />Saludos cordiales,<br /><br />Soporte Quipux.";
        $mail .= "<br /><br /><b>Nota: </b>Este mensaje fue enviado autom&aacute;ticamente por el sistema, por favor no lo responda.";
        $mail .= "<br />Si tiene alguna inquietud respecto a este mensaje, comun&iacute;quese con <a href='mailto:$cuenta_mail_soporte'>$cuenta_mail_soporte</a>";
        $mail .= "</body></html>";
        if (trim($ciu_email)!="")
            if ($grabar_ciu==1)
            enviarMail($mail, "Quipux: Actualización de datos.", $ciu_email, "$ciu_nombre $ciu_apellido", $ruta_raiz);
        if (trim($rs->fields["CIU_EMAIL"])!="" && $rs->fields["CIU_EMAIL"]!=$ciu_email)
                 if ($grabar_ciu==1)
            enviarMail($mail, "Quipux: Actualización de datos.", $rs->fields["CIU_EMAIL"], "$ciu_nombre $ciu_apellido", $ruta_raiz);
    }
}

echo "<html>".html_head();
?>
<body>
    <br><br>
    <?php
     if ($grabar_ciu==0){
         ?>
    <center><table width="40%" border="2" align="center" class="t_bordeGris">
	    <tr> 
                <td width="100%" height="30" class="listado2">
                    Existió un problema al guardar el ciudadano, comuníquese con el Administrador
                    del Sistema.
                    <center><input class="botones" type="button" name="Atras" value="Aceptar" onclick="window.location='./mnuUsuarios_ext.php';"/></center>
                </td>
            </tr>
    </table></center>
     <?php      
     }else{
    ?>
    <center>        
        <?=$mensaje?><br>
	<table width="40%" border="2" align="center" class="t_bordeGris">
	    <tr> 
		<td width="100%" height="30" class="listado2">
		<?
		    /**
		    * Mensaje en pantalla si el usuario fue creado o si sus datos fueron actualizados
		    * correctamente.
		    **/
		    if ($accion==1) {?>
		    <span class=etexto><center><B>El ciudadano <?="$ciu_nombre $ciu_apellido"?><br/>fue creado correctamente con el usuario &quot;<?=$tmp_cedula?>&quot;</B></center></span>
		<? } else {
                 
                 ?>
                <span class=etexto><center><B>Los cambios en el ciudadano <?="$ciu_nombre $ciu_apellido"?>
                            <br/> se realizaron correctamente</B></center></span>
		<? } ?>
		</td> 
	    </tr>
	    <tr>	
		<td height="30" class="listado2">
            <?php           
            if($codigo1=="ciu_s"){?>
                <center><input  name="btn_accion" type="button" class="botones" title="Cerrar" value="Cerrar" onclick="window.close();"></center>
            <?}elseif($accion==2){
                $cod_impresion = "'".$_GET['cod_impresion']."'";
                ?>
                <center><input class="botones" type="submit" name="Submit" value="Aceptar" onclick="<?php echo ($cerrar == 'Si') ? "window.opener.refrescar_pagina('OI',".$cod_impresion."); window.close();" : "location='./mnuUsuarios_ext.php?cerrar=$cerrar&accion=$accion'"?>"></center>
            <?}else{?>
                <center><input class="botones" type="submit" name="Submit" value="Aceptar" onclick="<?php echo ($cerrar == 'Si') ? "window.close()" : "location='./mnuUsuarios_ext.php?cerrar=$cerrar'"?>"></center>
            <?}?>
		</td> 
	    </tr>
	</table>
       
    </center>
<?php } ?>
</body>
</html>
