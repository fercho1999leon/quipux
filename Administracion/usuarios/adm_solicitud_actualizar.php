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


$ruta_raiz = "../..";

require_once "$ruta_raiz/funciones.php";
require_once "$ruta_raiz/funciones_interfaz.php";

session_start();
include_once "$ruta_raiz/rec_session.php";
$flag_login = true;
if ($_SESSION["admin_institucion"]!=1) die("Usted no tiene permisos para acceder a esta p&aacute;gina.");

$ciu_codigo = limpiar_sql($_POST["ciu_codigo"]);

//campos adicionales para solicitud

//Bandera para determinar si el rgistro de solicitud de firma para ciudadano existe o no
$banExisteSol = 0;
//campos adicionales para solicitud
$recordsolicitud = array();
unset ($recordsolicitud);

//Para editar
//Consultar si el registro en la tabla solicitud_firma_ciudadano existe
$sqlSol = "select * from solicitud_firma_ciudadano where ciu_codigo = $ciu_codigo";

$rsSol = $db->conn->query($sqlSol);
//echo($rsSol);
if($rsSol && !$rsSol->EOF){
    $recordsolicitud["SOL_CODIGO"] = $rsSol->fields["SOL_CODIGO"];
    $banExisteSol = 1;
}
//Si el registro existe actualiza
if($banExisteSol==1)
    $whereSol = "SOL_CODIGO";
else //Si el registro no existe inserta
    {
    $whereSol = "";    
    }

//echo($ciu_codigo);
$autorizar = 0;
$recordsolicitud["CIU_CODIGO"]       =  $ciu_codigo;
$recordsolicitud["CIU_CEDULA"]       = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_cedula"])));
$recordsolicitud["CIU_DOCUMENTO"]    = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_documento"])));
$recordsolicitud["CIU_NOMBRE"]       = "initcap(".$db->conn->qstr(limpiar_sql(trim($_POST["ciu_nombre"]))).")";
$recordsolicitud["CIU_APELLIDO"]     = "initcap(".$db->conn->qstr(limpiar_sql(trim($_POST["ciu_apellido"]))).")";
$recordsolicitud["CIU_TITULO"]       = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_titulo"])));
$recordsolicitud["CIU_ABR_TITULO"]   = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_abr_titulo"])));
$recordsolicitud["CIU_EMPRESA"]      = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_empresa"])));
$recordsolicitud["CIU_CARGO"]        = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_cargo"])));
$recordsolicitud["CIU_DIRECCION"]    = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_direccion"])));
$recordsolicitud["CIU_EMAIL"]        = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_email"])));
$recordsolicitud["CIU_TELEFONO"]     = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_telefono"])));
$recordsolicitud["SOL_OBSERVACIONES"]     = $db->conn->qstr(limpiar_sql($_POST["sol_observaciones"]));
$recordsolicitud["CIUDAD_CODI"]       =  limpiar_sql($_POST['ciudad_codi']);
//$recordsolicitud["SOL_PLANILLA_ESTADO"]   = limpiar_sql($_POST['chk_planillah']);
//$recordsolicitud["SOL_CEDULA_ESTADO"]     = limpiar_sql($_POST["chk_cedulah"]);
$recordsolicitud["SOL_ACUERDO_ESTADO"]    = limpiar_sql($_POST["chk_acuerdoh"]);
if($_POST["sol_accion"] == 1)
{   $autorizar = 1;
    $recordsolicitud["SOL_ESTADO"]       =  3;//autorizado

    //verificamos si ya existe algun usuario con este numero de cedula
    $usr_cedula = limpiar_sql($_POST["ciu_cedula"]);
    $usr_login = "U".substr($usr_cedula,0,10);
    $sql = "select * from ciudadano where ciu_codigo=$ciu_codigo";

    $rsciu = $db->conn->query($sql);

    $sql1 =   "select depe_codi from dependencia where inst_codi=1";
    $rsdepe = $db->conn->query($sql1);
    $depe = $rsdepe->fields["DEPE_CODI"];
    //echo($e);

    //creamos el registro nuevo en la tabla de usuarios
    $recordusuarios = array();
    unset ($recordusuarios);
    $recordusuarios["usua_login"]  = $db->conn->qstr(limpiar_sql(trim($usr_login)));
    $recordusuarios["usua_pasw"]  = $db->conn->qstr(limpiar_sql(trim($rsciu->fields['CIU_PASW'])));
    $recordusuarios["usua_nomb"]  = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_nombre"])));
    $recordusuarios["usua_cedula"]  = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_cedula"])));
    $recordusuarios["usua_email"]  = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_email"])));
    $recordusuarios["usua_titulo"]  = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_titulo"])));
    $recordusuarios["usua_abr_titulo"]  = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_abr_titulo"])));
    $recordusuarios["usua_esta"]  = "1";
    $recordusuarios["usua_codi"]  = $ciu_codigo;
    $recordusuarios["cargo_tipo"]  = "0";
    $recordusuarios["depe_codi"]  =  $db->conn->qstr(limpiar_sql(trim($depe)));//"9234";///////////////////////OJO
    $recordusuarios["usua_nuevo"]  = "1";
    $recordusuarios["usua_tipo"]  = "2";
    //$recordusuarios["usua_cargo"]  = $db->conn->qstr(limpiar_sql(trim($rsciu->fields["CIU_CARGO"])));
    $recordusuarios["usua_cargo"]  = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_cargo"])));
    $recordusuarios["inst_codi"]  = "1";
    $recordusuarios["usua_apellido"]  = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_apellido"])));
    $recordusuarios["cargo_id"]  = "1";
    //       $recordusuarios["usua_obs"]  = "''";
    //$recordusuarios["ciu_codi"]  = "0";
    //       $recordusuarios["usua_firma_path"]  = "''";
    $recordusuarios["usua_direccion"]  = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_direccion"])));
    $recordusuarios["usua_telefono"]  = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_telefono"])));
    $recordusuarios["usua_codi_actualiza"]  = $_SESSION['usua_codi'];
    $recordusuarios["usua_fecha_actualiza"]  = $db->conn->sysTimeStamp;
    $recordusuarios["usua_obs_actualiza"]  = "'Registro Nuevo'";
    $recordusuarios["inst_nombre"]  = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_empresa"])));
    $recordusuarios["usua_cargo_cabecera"]  = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_cargo"])));
    if (isset($_POST["ciudad_codi"]))
        $recordusuarios["ciu_codi"]  = 0+limpiar_sql($_POST["ciudad_codi"]);
    else
        $recordusuarios["ciu_codi"]  = "0";
    //$recordusuarios["usua_sumilla"]  = "";
    //$recordusuarios["usua_responsable_area"]  = "";

    $recordusuarios["usua_tipo_certificado"]  = 0+limpiar_sql($_POST["sol_firma"]);

    //       die("GRABAR USUARIO");
   
   
        $deleteCiu_tmp= "delete from ciudadano_tmp where ciu_codigo=$ciu_codigo";
        $deleteCiu= "delete from ciudadano where ciu_codigo=$ciu_codigo";
        //var_dump($deleteCiu);
        $ok4 = $db->conn->query($deleteCiu_tmp);
        $ok3 = $db->conn->query($deleteCiu);        
        //var_dump($ok3);
        //die();
   
     $ok1 = $db->conn->Replace("usuarios", $recordusuarios, "", false,false,true,false);
    //damos los permisos al usuario nuevo
    $permisos_usuarios = array(19,21,27);
    $record_permisos_usuarios = array();
    unset ($record_permisos_usuarios);
    foreach ($permisos_usuarios as $permiso) {
        $record_permisos_usuarios["id_permiso"]  = $permiso;
        $record_permisos_usuarios["usua_codi"]  = $ciu_codigo;
        $ok2 = $db->conn->Replace("permiso_usuario", $record_permisos_usuarios, "", false,false,true,false);
    }
}
else {
   $recordsolicitud["SOL_ESTADO"]       =  0;//rechazado
   $recordsolicitud["SOL_FIRMA"]  = 0+limpiar_sql($_POST["sol_firma"]);
   $recordsolicitud["SOL_OBSERVACIONES"]     = $db->conn->qstr(limpiar_sql($_POST["sol_observaciones"]));
   $autorizar = 0;
//die("rechazado");
}

$ok5 = $db->conn->Replace("solicitud_firma_ciudadano", $recordsolicitud, $whereSol, false,false,false,false);

if($autorizar == 0)
{
    $ciu_nomb = limpiar_sql(trim($_POST["ciu_nombre"]));
    $ciu_apel = limpiar_sql(trim($_POST["ciu_apellido"]));
    $ciu_mail = limpiar_sql(trim($_POST["ciu_email"]));
    $ciu_ced = limpiar_sql(trim($_POST["ciu_cedula"]));
    //Enviar correo al Super Administrador para verificar datos del ciudadano actualizado
    $mail = "<html><title>Informaci&oacute;n Quipux</title>";
    $mail .= "Estimado(a) Ciudadano(a):";
    $mail .= "<body><center><h1>QUIPUX</h1><br /><h2>Sistema de Gesti&oacute;n Documental</h2><br /><br /></center>";
    $mail .= "La solicitud del ciudadano(a) ".limpiar_sql($_POST["ciu_nombre"])." "
            .limpiar_sql($_POST["ciu_apellido"])." enviada a la instituci&oacute;n "
            .$_SESSION["inst_nombre"].", ha sido <b>Rechazada</b>.";
    $mail .= "<br /><br />Por favor verifique las observaciones de la solicitud y envie nuevamente al cumplir con lo solicitado.";
    $mail .= "<br /><br />Le recordamos que para acceder al sistema deber&aacute; hacerlo con el usuario &quot;$ciu_ced&quot;
              ingresando a <a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>";
    $mail .= "<br /><br />Saludos cordiales,<br /><br />Soporte Quipux.";
    $mail .= "<br /><br /><b>Nota: </b>Este mensaje fue enviado autom&aacute;ticamente por el sistema, por favor no lo responda.";
    $mail .= "<br />Si tiene alguna inquietud respecto a este mensaje, comun&iacute;quese con <a href='mailto:$cuenta_mail_soporte'>$cuenta_mail_soporte</a>";
    $mail .= "</body></html>";    
    enviarMail($mail, "Quipux: Solicitud para generar y firmar documentos por parte de un ciudadano.", $ciu_mail, $ciu_nomb. " " . $ciu_apel, $ruta_raiz);
//    echo "enviarMail($mail, \"Quipux: Solicitud para generar y firmar documentos por parte de un ciudadano.\", $ciu_mail, $ciu_nomb. \" \" . $ciu_apel, $ruta_raiz);";
}
else
    {
    $ciu_nomb = limpiar_sql(trim($_POST["ciu_nombre"]));
    $ciu_apel = limpiar_sql(trim($_POST["ciu_apellido"]));
    $ciu_mail = limpiar_sql(trim($_POST["ciu_email"]));
    $ciu_ced = limpiar_sql(trim($_POST["ciu_cedula"]));
    //Enviar correo al Super Administrador para verificar datos del ciudadano actualizado
    $mail = "<html><title>Informaci&oacute;n Quipux</title>";
    $mail .= "Estimado(a) Ciudadano(a):";
    $mail .= "<body><center><h1>QUIPUX</h1><br /><h2>Sistema de Gesti&oacute;n Documental</h2><br /><br /></center>";
    $mail .= "La solicitud del ciudadano(a) ".limpiar_sql($_POST["ciu_nombre"])." "
            .limpiar_sql($_POST["ciu_apellido"])." enviada a la instituci&oacute;n "
            .$_SESSION["inst_nombre"].", ha sido <b>Aceptada</b>.";    
    $mail .= "<br /><br />Le recordamos que para acceder al sistema deber&aacute; hacerlo con el usuario &quot;$ciu_ced&quot;
              ingresando a <a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>";
    $mail .= "<br /><br />Saludos cordiales,<br /><br />Soporte Quipux.";
    $mail .= "<br /><br /><b>Nota: </b>Este mensaje fue enviado autom&aacute;ticamente por el sistema, por favor no lo responda.";
    $mail .= "<br />Si tiene alguna inquietud respecto a este mensaje, comun&iacute;quese con <a href='mailto:$cuenta_mail_soporte'>$cuenta_mail_soporte</a>";
    $mail .= "</body></html>";
    enviarMail($mail, "Quipux: Solicitud para generar y firmar documentos por parte de un ciudadano.", $ciu_mail, $ciu_nomb. " " . $ciu_apel, $ruta_raiz);
//    echo "enviarMail($mail, \"Quipux: Solicitud para generar y firmar documentos por parte de un ciudadano.\", $ciu_mail, $ciu_nomb. \" \" . $ciu_apel, $ruta_raiz);";
}

if($ok5)
{
?>
<html>
<?php echo html_head(); /*Imprime el head definido para el sistema*/?>
<body>
    <div id='wrapper'>
    <div id='mainbody'><div class='shad-1'><div class='shad-2'><div class='shad-3'><div class='shad-4'><div class='shad-5'>
    <br /><br /><br />
    <table align='center' width='100%' cellpadding='0' cellspacing='0' class='mainbody'>
        <tr valign='top' align='center'>
            <td class='left'  align='center' width='100%'>
                <?php
                    if($_POST["sol_accion"] == 1)
                        echo "<h3>La solicitud de: ". $_POST["ciu_nombre"]. " " . $_POST["ciu_apellido"] ." con CI: ". $_POST["ciu_cedula"] ." fue autorizada.<br /></h3>";
                    else
                        echo "<h3>La solicitud  fue devuelta o rechazada.<br /></h3>";
                    echo "<br /><br /><input type='button' value='Aceptar' class='botones' name='btn_aceptar' onClick=\"window.location='cuerpoSolicitud_ext.php'\">";
                ?>
            </td>
        </tr>
    </table>
    <br /><br /><br />
    </div></div></div></div></div></div>
    </div>
</body>
</html>
<?php } ?>