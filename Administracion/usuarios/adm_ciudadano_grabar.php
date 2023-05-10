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
$ciu_codigo = limpiar_sql($_SESSION["usua_codi"]);
$accion_btn_cancelar = "history.back();";
$flag_login = true;

require_once("$ruta_raiz/funciones_interfaz.php");

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

$record = array();
unset ($record);
$record["CIU_CODIGO"]       = limpiar_sql($_POST["ciu_codigo"]);
$record["CIU_CEDULA"]       = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_cedula"])));
$record["CIU_DOCUMENTO"]    = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_documento"])));
$record["CIU_NOMBRE"]       = "initcap(".$db->conn->qstr(limpiar_sql(trim($_POST["ciu_nombre"]))).")";
$record["CIU_APELLIDO"]     = "initcap(".$db->conn->qstr(limpiar_sql(trim($_POST["ciu_apellido"]))).")";
$record["CIU_TITULO"]       = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_titulo"])));
$record["CIU_ABR_TITULO"]   = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_abr_titulo"])));
$record["CIU_EMPRESA"]      = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_empresa"])));
$record["CIU_CARGO"]        = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_cargo"])));
$record["CIU_DIRECCION"]    = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_direccion"])));
$record["CIU_EMAIL"]        = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_email"])));
$record["CIU_TELEFONO"]     = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_telefono"])));
if (isset($_POST["codi_ciudad"]))
$record["CIUDAD_CODI"]       = $db->conn->qstr(limpiar_sql(trim($_POST["codi_ciudad"])));
else
    $record["CIUDAD_CODI"]=1;
$record["CIU_ESTADO"]       = 1;
$ok1 = $db->conn->Replace("CIUDADANO_TMP", $record, "CIU_CODIGO", false,false,false,false);

if($ok1)
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
    enviarMail($mail, "Quipux: Actualización de datos de ciudadano.", $amd_email, "Administrador", $ruta_raiz);
}
?>

<html>
<? echo html_head(); /*Imprime el head definido para el sistema*/?>
    <script type="text/javascript">
       function cerrarpag(){
           window.close();
       }
       function redireccionar(){          
           window.location='mnuUsuarios_ext.php';
       }
    </script>
<body>
    <div id='wrapper'>
    <div id='mainbody'><div class='shad-1'><div class='shad-2'><div class='shad-3'><div class='shad-4'><div class='shad-5'>
    <br /><br /><br />
    <table align='center' width='100%' cellpadding='0' cellspacing='0' class='mainbody'>
        <tr valign='top' align='center'>
            <td class='left'  align='center' width='100%'>
                <h3>Datos guardados exitosamente.<br />
                    Estos ser&aacute;n verificados por el administrador del sistema antes de ser modificados definitivamente.</h3>
                    <?
                    if ($_SESSION["tipo_usuario"]==1) {                        
                        if($_GET['buscar'] == 'S' and $_GET['cerrar']=='No')
                            echo "<br /><br /><input type='button' value='Aceptar' class='botones' name='btn_aceptar' onClick=redireccionar();>";
                        else                            
                            echo "<br /><br /><input type='button' value='Aceptar' class='botones' name='btn_aceptar' onClick=cerrarpag()>";
                    }
                    ?>
            </td>
        </tr>
    </table>
    <br /><br /><br />
    </div></div></div></div></div></div>
    </div>
</body>
</html>