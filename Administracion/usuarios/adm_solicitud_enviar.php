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
$ruta_raiz1 = ".";

require_once "$ruta_raiz/funciones.php";
require_once("$ruta_raiz/funciones_interfaz.php");

session_start();
include_once "$ruta_raiz/rec_session.php";

$flag_login = true;
$ciu_codigo = limpiar_sql($_SESSION["usua_codi"]);

// Obtener el codigo anterior del ciudadano $old_codigo si la actualización se realiza desde buscar de/para
if($_GET['buscar'] == 'S')
    if(isset($old_codigo))
        $ciu_codigo = $old_codigo;

$sql = "select * from ciudadano where ciu_codigo='$ciu_codigo'";
$rs = $db->conn->query($sql);
if ($rs->EOF) {
    echo html_error("No se encont&oacute; el usuario en el sistema.");
    die("");
}
//campos adicionales para solicitud

//Bandera para determinar si el rgistro de solicitud de firma para ciudadano existe o no
$banExisteSol = 0;
//campos adicionales para solicitud
$recordsolicitud = array();
unset ($recordsolicitud);

//Para editar


$recordsolicitud["CIU_CODIGO"]       = limpiar_sql($ciu_codigo);
$recordsolicitud["CIU_CEDULA"]       = $db->conn->qstr(limpiar_sql(trim($_POST["cedula_usu"])));
$recordsolicitud["CIU_DOCUMENTO"]    = $db->conn->qstr(limpiar_sql(trim($_POST["documento_usu"])));
$recordsolicitud["CIU_NOMBRE"]       = "initcap(".$db->conn->qstr(limpiar_sql(trim($_POST["nombre_usu"]))).")";
$recordsolicitud["CIU_APELLIDO"]     = "initcap(".$db->conn->qstr(limpiar_sql(trim($_POST["apellido_usu"]))).")";
$recordsolicitud["CIU_TITULO"]       = $db->conn->qstr(limpiar_sql(trim($_POST["titulo_usu"])));
$recordsolicitud["CIU_ABR_TITULO"]   = $db->conn->qstr(limpiar_sql(trim($_POST["abr_titulo_usu"])));
$recordsolicitud["CIU_EMPRESA"]      = $db->conn->qstr(limpiar_sql(trim($_POST["empresa_usu"])));
$recordsolicitud["CIU_CARGO"]        = $db->conn->qstr(limpiar_sql(trim($_POST["cargo_usu"])));
$recordsolicitud["CIU_DIRECCION"]    = $db->conn->qstr(limpiar_sql(trim($_POST["direccion_usu"])));
$recordsolicitud["CIU_EMAIL"]        = $db->conn->qstr(limpiar_sql(trim($_POST["mail_usu"])));
$recordsolicitud["CIU_TELEFONO"]     = $db->conn->qstr(limpiar_sql(trim($_POST["telefono_usu"])));
$recordsolicitud["SOL_OBSERVACIONES"]     = $db->conn->qstr(limpiar_sql(trim($_POST["observaciones_usu"])));
$recordsolicitud["SOL_FIRMA"]     = $db->conn->qstr(limpiar_sql(trim($_POST["firma_usu"])));
$recordsolicitud["CIUDAD_CODI"]     = $db->conn->qstr(limpiar_sql(trim($_POST["ciudad_usu"])));

//Consultar si el registro en la tabla solicitud_firma_ciudadano existe
$sqlSol = "select * from solicitud_firma_ciudadano where ciu_codigo = $ciu_codigo";
$rsSol = $db->conn->query($sqlSol);

if(!$rsSol->EOF){
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
$recordsolicitud["SOL_ESTADO"]       = 2;

$ok2 = $db->conn->Replace("solicitud_firma_ciudadano", $recordsolicitud, $whereSol, false,false,false,false);
//die();

if($ok2)
{
    //Enviar correo al Super Administrador para verificar datos del ciudadano actualizado
    $mail = "<html><title>Informaci&oacute;n Quipux</title>";
    $mail .= "Estimado(a) Administrad@r:";
    $mail .= "<body><center><h1>QUIPUX</h1><br /><h2>Sistema de Gesti&oacute;n Documental</h2><br /><br /></center>";
    $mail .= "Los datos del ciudadano ".limpiar_sql($_POST["ciu_nombre"])." "
            .limpiar_sql($_POST["ciu_apellido"])." han sido modificados por ".$_SESSION["usua_nomb"]." de la instituci&oacute;n "
            .$_SESSION["inst_nombre"].", por favor, verificar la informaci&oacute;n.";
    $mail .= "<br /><br />Le recordamos que para acceder al sistema deber&aacute; hacerlo con el usuario &quot;$tmp_cedula&quot;
              ingresando a <a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>";
    $mail .= "<br /><br />Saludos cordiales,<br /><br />Soporte Quipux.";
    $mail .= "<br /><br /><b>Nota: </b>Este mensaje fue enviado autom&aacute;ticamente por el sistema, por favor no lo responda.";
    $mail .= "<br />Si tiene alguna inquietud respecto a este mensaje, comun&iacute;quese con <a href='mailto:$cuenta_mail_soporte'>$cuenta_mail_soporte</a>";
    $mail .= "</body></html>";
    enviarMail($mail, "Quipux: Solicitud para generar y firmar documentos por parte de un ciudadano.", $amd_email, "Administrador", $ruta_raiz);
}
?>

<html>
<? echo html_head(); /*Imprime el head definido para el sistema*/?>
<body>
    <div id='wrapper'>
    <? if (!$flag_login) echo html_encabezado(); /*Imprime el encabezado del sistema*/ ?>
    <div id='mainbody'><div class='shad-1'><div class='shad-2'><div class='shad-3'><div class='shad-4'><div class='shad-5'>
    <form>
    <br /><br /><br />
    <table align='center' width='100%' cellpadding='0' cellspacing='0' class='mainbody'>
        <tr valign='top' align='center'>
            <td class='left'  align='center' width='100%'>
                <h3>Sus datos fueron enviados exitosamente.<br />
                    Estos ser&aacute;n verificados por el administrador del sistema antes de aprobar la solicitud.</h3>
                    <? if (!$flag_login) echo "<br /><br /><input type='button' value='Aceptar' class='botones' name='btn_aceptar' onClick=\"window.location='$ruta_raiz/login.php'\">"; ?>
                    <? echo "<br /><br /><input type='button' value='Aceptar' class='botones' name='btn_aceptar' onClick=\"window.location='adm_solicitud.php'\">";                       
                    ?>
            </td>
        </tr>
    </table>
    <br /><br /><br />
    </form>
    </div></div></div></div></div></div>
    <? if (!$flag_login) echo html_pie_pagina(); /*Imprime el pie de pagina del sistema*/ ?>
    </div>
</body>
</html>