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
$ciu_codigo = limpiar_sql($_POST["ciu_codigo"]);

// Obtener el codigo anterior del ciudadano $old_codigo si la actualización se realiza desde buscar de/para
if($_GET['buscar'] == 'S')
    if(isset($old_codigo))
        $ciu_codigo = $old_codigo;

//campos adicionales para solicitud


//Bandera para determinar si el rgistro de solicitud de firma para ciudadano existe o no
$banExisteSol = 0;
//campos adicionales para solicitud
$recordsolicitud = array();
unset ($recordsolicitud);
$recordsolicitud["CIU_CODIGO"]       = limpiar_sql($_POST["ciu_codigo"]);
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
$recordsolicitud["CIUDAD_CODI"]       = limpiar_sql($_POST["codi_ciudad"]);
//Para editar
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
    $recordsolicitud["SOL_ESTADO"]       = 1;
    }
$recordsolicitud["CIU_CODIGO"]       =  limpiar_sql($ciu_codigo);
$recordsolicitud["SOL_OBSERVACIONES"]       = $db->conn->qstr($_POST["sol_observaciones"]);
$recordsolicitud["SOL_FIRMA"]       = $_POST["sol_firma"];


$ok2 = $db->conn->Replace("solicitud_firma_ciudadano", $recordsolicitud, $whereSol, false,false,false,false);



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
                <h3>Sus datos fueron guardados exitosamente.</h3>
                    <? if (!$flag_login) echo "<br /><br /><input type='button' value='Aceptar' class='botones' name='btn_aceptar' onClick=\"window.location='$ruta_raiz/login.php'\">"; ?>
                    <? 
                            echo "<br /><br /><input type='button' value='Aceptar' class='botones' name='btn_aceptar' onClick=\"window.location='adm_solicitud.php'\">";
                       
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