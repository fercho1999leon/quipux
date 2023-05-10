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
session_start();

include_once "$ruta_raiz/rec_session.php";
require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
require_once "$ruta_raiz/funciones_interfaz.php"; //para traer funciones p_get y p_post

if($_SESSION["usua_admin_sistema"]!=1 and $_SESSION["usua_perm_ciudadano"]!=1) {
    echo html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
    die("");
}
$ciu_codigo = 0 +limpiar_numero($_POST["ciu_codigo"]);

$rs = $db->conn->query("select * from ciudadano_tmp where ciu_codigo=$ciu_codigo and ciu_estado=1");
if (!$rs or $rs->EOF) {
    echo html_error("No se encont&oacute; el usuario en el sistema.");
    die("");
}

$usr_nombre = $rs->fields["CIU_NOMBRE"]." ".$rs->fields["CIU_APELLIDO"];
$usr_email = $rs->fields["CIU_EMAIL"];
$sql = "select i.inst_nombre
            ,c.ciu_nombre || ' ' || c.ciu_apellido as ciudadano_nombre
            ,c.ciu_email as ciudadano_email
        from ciudadano c left outer join institucion i on coalesce(c.inst_codi,0)=i.inst_codi
        where ciu_codigo=$ciu_codigo";

$rs = $db->conn->query($sql);
if (!$rs or $rs->EOF) {
    echo html_error("No se encont&oacute; el usuario en el sistema.");
    die("");
}

$usr_institucion = $rs->fields["INST_NOMBRE"];
$usr_nombreOri = $rs->fields["CIUDADANO_NOMBRE"];
$usr_emailOri = $rs->fields["CIUDADANO_EMAIL"];

//actualizar ciudadano estado
$sqlUp="update ciudadano_tmp set ciu_estado=0 where ciu_codigo=$ciu_codigo";
$db->conn->query($sqlUp);

$mail = "<html><title>Informaci&oacute;n Quipux</title>";
$mail .= "<body><center><h1>QUIPUX</h1><br /><h2>Sistema de Gesti&oacute;n Documental</h2><br /><br /></center>";
$mail .= "Estimado(a) $usr_nombreOri.<br /><br />";
$mail .= "Los cambios solicitados a la informaci&oacute;n de su usuario han sido rechazados.
          Por favor acerquese a la instituci&oacute;n &quot;$usr_institucion&quot;, en donde fue registrado,
          para que un funcionario de la misma actualice su informaci&oacute;n.";
$mail .= "<br /><br />Saludos cordiales,<br /><br />Soporte Quipux.";
$mail .= "<br /><br /><b>Nota: </b>Este mensaje fue enviado autom&aacute;ticamente por el sistema, por favor no lo responda.";
$mail .= "<br />Si tiene alguna inquietud respecto a este mensaje, comun&iacute;quese con <a href='mailto:$cuenta_mail_soporte'>$cuenta_mail_soporte</a>";
$mail .= "</body></html>";
if ($usr_emailOri)
enviarMail($mail, "Quipux: Actualización de datos.", $usr_emailOri, $usr_nombreOri, $ruta_raiz);
?>

<html>
    <script>
        window.location='adm_ciudadano_confirmar.php';
    </script>
    <body>
    </body>
</html>
