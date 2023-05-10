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
 * Acceso: Ciudadanos
 * Permite subir el archivo, guarda en bodega/ciudadanos/codigo_ciudadano_acuerdo.pdf.p7m
 * Registra o modifica la solicitud											**
*****************************************************************************************/
$ruta_raiz = "../..";
$ruta_raiz1 = ".";
session_start();

require_once "$ruta_raiz/funciones.php";
require_once("$ruta_raiz/funciones_interfaz.php");

include_once "$ruta_raiz/rec_session.php";
include_once "../ciudadanos/util_ciudadano.php";
$ciud = New Ciudadano($db);
$accion_btn_cancelar = "window.location='adm_solicitud_ciu.php'";
$ciu_codigo = limpiar_sql($_SESSION["usua_codi"]);
//campos adicionales para solicitud
//Bandera para determinar si el rgistro de solicitud de firma para ciudadano existe o no
$banExisteSol = 0;
//Se actualiza con los datos que edito en la solicitud
$recordsolicitud = array();
unset ($recordsolicitud);
$recordsolicitud["CIU_CODIGO"]       = limpiar_sql($ciu_codigo);
$recordsolicitud["CIU_CEDULA"]       = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_cedula"]))));
$recordsolicitud["CIU_DOCUMENTO"]    = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_documento"]))));
$recordsolicitud["CIU_NOMBRE"]       = "initcap(".$db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_nombre"])))).")";
$recordsolicitud["CIU_APELLIDO"]     = "initcap(".$db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_apellido"])))).")";
$recordsolicitud["CIU_TITULO"]       = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_titulo"]))));
$recordsolicitud["CIU_ABR_TITULO"]   = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_abr_titulo"]))));
$recordsolicitud["CIU_EMPRESA"]      = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_empresa"]))));
$recordsolicitud["CIU_CARGO"]        = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_cargo"]))));
$recordsolicitud["CIU_DIRECCION"]    = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_direccion"]))));
$recordsolicitud["CIU_EMAIL"]        = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_email"]))));
$recordsolicitud["CIU_TELEFONO"]     = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_telefono"]))));
$recordsolicitud["CIUDAD_CODI"]      = 0+limpiar_sql($_POST["ciu_ciudad"]);
//$recordsolicitud["PAIS_CODI"]      = 0+limpiar_sql($_POST["cod_pais"]);
//$recordsolicitud["PROVINCIA_CODI"]      = 0+limpiar_sql($_POST["cod_prov"]);
//
//$recordsolicitud["CANTON_CODI"]      = 0+limpiar_sql($_POST["cod_canton"]);
$recordsolicitud["CIU_REFERENCIA"]      = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_referencia"]))));
//Para editar
//Consultar si el registro existe en la tabla solicitud_firma_ciudadano
$sqlSol = "select * from solicitud_firma_ciudadano where ciu_codigo = $ciu_codigo";

$rsSol = $db->conn->query($sqlSol);

if(!$rsSol->EOF){
    $recordsolicitud["SOL_CODIGO"] = $rsSol->fields["SOL_CODIGO"];
    $banExisteSol = 1;
}
//Si el registro existe actualiza
if($banExisteSol==1)
    $whereSol = "SOL_CODIGO";
else{//Si el registro no existe inserta
    $whereSol = "";
    $recordsolicitud["SOL_ESTADO"]       = 1;
}
$recordsolicitud["CIU_CODIGO"]          = limpiar_sql($ciu_codigo);
$recordsolicitud["SOL_OBSERVACIONES"]   = $db->conn->qstr($_POST["sol_observaciones"]);
$recordsolicitud["SOL_FIRMA"]           = 1;
$recordsolicitud["SOL_ACUERDO"]         = 1;
$recordsolicitud["SOL_FECHA_ENVIO"]     =  $db->conn->sysTimeStamp;//autorizado
//Subir Archivos
$url = "$ruta_raiz/bodega/ciudadanos/".$ciu_codigo."_acuerdo.pdf.p7m";
if (!is_file($url))//si no existe archivo
$ok_guardar=$ciud->guardarArchivos('acuerdo',"/bodega/ciudadanos/",$ruta_raiz,$ciu_codigo);
else
    $ok_guardar=1;

if ($ok_guardar==1){
    
    $sql="select * from solicitud_firma_ciudadano where ciu_codigo=$ciu_codigo";    
    $rs_old = $db->conn->query($sql);
    $db->conn->Replace("SOLICITUD_FIRMA_CIUDADANO", $recordsolicitud, $whereSol, false,false,false,false);
    $rs_new = $db->conn->query($sql);
    $ciud->grabar_log_tabla('LOG_USR_CIUDADANOS',$rs_old, $rs_new, $_SESSION['usua_codi'],3,$db);
}
?>

<html>
    <?echo html_head(); //Imprime el head definido para el sistema?>
<body>
    <form name="frmConfirmaCreacion" action="adm_solicitud_ciu.php" method="post">
        <br>
        &nbsp;
        </br>
        <center><table width="40%" border="2" align="center" class="t_bordeGris">
            <tr>
            <td width="100%" height="30" align="center" class="listado2">
           <?php 
                if ($ok_guardar==1)
                echo '<font size="1"><b>Sus datos fueron guardados exitosamente.</b></font>';
            else
                 echo '<font size="1" color="red"><b>Existe problemas al registrar la solicitud, favor verifique el tamaño o la extensión del archivo.</b></font>'
            
           ?>
            </td>
            </tr>
            <tr>
            <td height="30" class="listado2">
                <center>
                    <input  name="btn_accion" type="button" class="botones_largo" title="Aceptar" value="Aceptar" onClick="<?=$accion_btn_cancelar?>"/>
                    
                </center>
            </td>
            </tr>
        </table>
    </center>
    </form>
</body>
</html>