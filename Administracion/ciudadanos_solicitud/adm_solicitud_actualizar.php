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
** Acceso: administrador
*****************************************************************************************/


$ruta_raiz = "../..";
session_start();
require_once "$ruta_raiz/funciones.php";
require_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/rec_session.php";

include_once "../ciudadanos/util_ciudadano.php";
$ciud = New Ciudadano($db);

$flag_login = true;
if ($_SESSION["admin_institucion"]!=1) die("Usted no tiene permisos para acceder a esta p&aacute;gina.");

$ciu_codigo = 0+limpiar_sql($_POST["ciu_codigo"]);

//Bandera para determinar si el rgistro de solicitud de firma para ciudadano existe o no
$banExisteSol = 0;
//campos adicionales para solicitud
$recordsolicitud = array();
unset ($recordsolicitud);

//Para editar
//Consultar si el registro en la tabla solicitud_firma_ciudadano existe
$sqlSol = "select * from solicitud_firma_ciudadano where ciu_codigo = $ciu_codigo";

$rsSol = $db->conn->query($sqlSol);

if($rsSol && !$rsSol->EOF){
    $recordsolicitud["SOL_CODIGO"] = $rsSol->fields["SOL_CODIGO"];
    $banExisteSol = 1;
}
//Si el registro existe actualiza
if($banExisteSol==1)
    $whereSol = "SOL_CODIGO";
else //Si el registro no existe inserta    
    $whereSol = "";    
    

//datos de solicitud
$recordsolicitud["CIU_CODIGO"]          = $ciu_codigo;
$recordsolicitud["CIU_CEDULA"]          = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_cedula"])));
$recordsolicitud["CIU_NOMBRE"]          = "initcap(".$db->conn->qstr(limpiar_sql(trim($_POST["ciu_nombre"]))).")";
$recordsolicitud["CIU_APELLIDO"]        = "initcap(".$db->conn->qstr(limpiar_sql(trim($_POST["ciu_apellido"]))).")";

//$recordsolicitud["PAIS_CODI"]           = limpiar_sql($_POST['cod_pais']);
//$recordsolicitud["PROVINCIA_CODI"]      = limpiar_sql($_POST['cod_prov']);
//$recordsolicitud["CANTON_CODI"]         = limpiar_sql($_POST['cod_canton']);
$recordsolicitud["CIUDAD_CODI"]         = limpiar_sql($_POST['ciu_ciudad']);

$recordsolicitud["CIU_TITULO"]          = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_titulo"])));
$recordsolicitud["CIU_ABR_TITULO"]      = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_abr_titulo"])));
$recordsolicitud["CIU_CARGO"]           = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_cargo"])));
$recordsolicitud["CIU_DOCUMENTO"]       = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_documento"])));

$recordsolicitud["CIU_DIRECCION"]       = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_direccion"])));
$recordsolicitud["CIU_REFERENCIA"]       = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_referencia"])));

$recordsolicitud["CIU_EMPRESA"]         = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_empresa"])));
$recordsolicitud["CIU_EMAIL"]           = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_email"])));
$recordsolicitud["CIU_TELEFONO"]        = $db->conn->qstr(limpiar_sql(trim($_POST["ciu_telefono"])));

$recordsolicitud["SOL_OBSERVACIONES"]   = $db->conn->qstr(limpiar_sql($_POST["sol_observaciones"]));
$recordsolicitud["SOL_ACUERDO_ESTADO"]  = limpiar_sql($_POST["acuerdo"]);

if($_POST["sol_accion"] == 1)
{   
    $recordsolicitud["SOL_ESTADO"]       =  3;//autorizado
    $recordsolicitud["SOL_FECHA_AUTORIZADO"]       =  $db->conn->sysTimeStamp;//autorizado

    //verificamos si ya existe algun usuario con este numero de cedula
    $usr_cedula = limpiar_sql($_POST["ciu_cedula"]);
    $usr_login = "U".substr($usr_cedula,0,10);
    $sql = "select * from ciudadano where ciu_codigo=$ciu_codigo";

    $rsciu = $db->conn->query($sql);

    $sql1 =   "select depe_codi from dependencia where inst_codi=1";
    $rsdepe = $db->conn->query($sql1);
    $depe = $rsdepe->fields["DEPE_CODI"];

    //creamos el registro nuevo en la tabla de usuarios
    $recordusuarios = array();
    unset ($recordusuarios);
    $recordusuarios["usua_login"]       = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($usr_login))));
    $recordusuarios["usua_pasw"]        = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($rsciu->fields['CIU_PASW']))));
    $recordusuarios["usua_nomb"]        = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_nombre"]))));
    $recordusuarios["usua_cedula"]      = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_cedula"]))));
    $recordusuarios["usua_email"]       = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_email"]))));
    $recordusuarios["usua_titulo"]      = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_titulo"]))));
    $recordusuarios["usua_abr_titulo"]  = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_abr_titulo"]))));
    $recordusuarios["usua_esta"]        = "1";
    $recordusuarios["usua_codi"]        = $ciu_codigo;
    $recordusuarios["cargo_tipo"]       = "0";
    $recordusuarios["depe_codi"]        =  $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($depe))));//"9234";///////////////////////OJO
    $recordusuarios["usua_nuevo"]       = "1";
    $recordusuarios["usua_tipo"]        = "2";    
    $recordusuarios["usua_cargo"]       = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_cargo"]))));
    $recordusuarios["inst_codi"]        = "1";
    $recordusuarios["usua_apellido"]    = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_apellido"]))));
    $recordusuarios["cargo_id"]         = "1";
    $recordusuarios["usua_direccion"]   = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_direccion"]))));
    $recordusuarios["usua_telefono"]    = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_telefono"]))));
    $recordusuarios["usua_codi_actualiza"]      = $_SESSION['usua_codi'];
    $recordusuarios["usua_fecha_actualiza"]     = $db->conn->sysTimeStamp;
    $recordusuarios["usua_obs_actualiza"]       = "'Registro Nuevo'";
    $recordusuarios["inst_nombre"]              = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_empresa"]))));
    $recordusuarios["usua_cargo_cabecera"]      = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_cargo"]))));
    if (isset($_POST["ciudad_codi"]))
        $recordusuarios["ciu_codi"]             = 0+limpiar_sql($_POST["ciudad_codi"]);
    else
        $recordusuarios["ciu_codi"]             = "0";
   
    $recordusuarios["usua_tipo_certificado"]    = 0+limpiar_sql($_POST["sol_firma"]);
    
    $record["PAIS_CODI"]     = 0+limpiar_sql(trim($_POST["cod_pais"]));
    $record["PROVINCIA_CODI"]     = 0+limpiar_sql(trim($_POST["cod_prov"]));
    $record["CANTON_CODI"]     = 0+limpiar_sql(trim($_POST["cod_canton"]));
    $record["CIU_REFERENCIA"]     = $db->conn->qstr(limpiar_sql(trim($ciud->caracterEspecial($_POST["ciu_referencia"]))));
    
    
    
        $sql="select * from ciudadano_tmp where ciu_codigo = $ciu_codigo";
        $rs_old=$db->conn->query($sql);
        $deleteCiu_tmp= "delete from ciudadano_tmp where ciu_codigo=$ciu_codigo";
        $ok4 = $db->conn->query($deleteCiu_tmp);
        $rs_new=$db->conn->query($sql);
        $ciud->grabar_log_tabla('LOG_USR_CIUDADANOS',$rs_old, $rs_new, $_SESSION['usua_codi'],4,$db);
        
        $sql="select * from ciudadano where ciu_codigo = $ciu_codigo";
        $rs_old=$db->conn->query($sql);
        $deleteCiu= "delete from ciudadano where ciu_codigo=$ciu_codigo";
        $ok3 = $db->conn->query($deleteCiu);
        $rs_new=$db->conn->query($sql);        
        $ciud->grabar_log_tabla('LOG_USR_CIUDADANOS',$rs_old, $rs_new, $_SESSION['usua_codi'],2,$db);
    
    
    
    $sql="select * from usuarios where usua_codi = ".$recordusuarios["ciu_codi"];
    $rs_old=$db->conn->Execute($sql);    
    $ok1 = $db->conn->Replace("USUARIOS", $recordusuarios, "", false,false,true,false);  
    $rs_new=$db->conn->Execute($sql);
    $ciud->grabar_log_tabla('LOG_USR_CIUDADANOS',$rs_old, $rs_new, $_SESSION['usua_codi'],1,$db);
    
    
    
    
    
    //damos los permisos al usuario nuevo
    $permisos_usuarios = array(19,21,27);
    $record_permisos_usuarios = array();
    unset ($record_permisos_usuarios);
    foreach ($permisos_usuarios as $permiso) {
        $record_permisos_usuarios["id_permiso"]  = $permiso;
        $record_permisos_usuarios["usua_codi"]  = $ciu_codigo;
        $ok2 = $db->conn->Replace("PERMISO_USUARIO", $record_permisos_usuarios, "", false,false,true,false);
    }
    
   $tipomail = 'aceptada';
}
else {
   $recordsolicitud["SOL_ESTADO"]       = 0;//rechazado
   $recordsolicitud["SOL_FIRMA"]        = 0+limpiar_sql($_POST["sol_firma"]);
   $recordsolicitud["SOL_OBSERVACIONES"]= $db->conn->qstr(limpiar_sql($_POST["sol_observaciones"]));   
   $tipomail = 'rechazada';
}
$ciu_nombre = limpiar_sql(trim($_POST["ciu_nombre"]))." ".limpiar_sql(trim($_POST["ciu_apellido"]));
$ciu_mail = limpiar_sql(trim($_POST["ciu_email"]));
$ciu_ced = limpiar_sql(trim($_POST["ciu_cedula"]));
//accion solicitud

$sql="select * from solicitud_firma_ciudadano where ciu_codigo = ".$ciu_codigo;    
$rs_old=$db->conn->Execute($sql);    
$ok5 = $db->conn->Replace("solicitud_firma_ciudadano", $recordsolicitud, $whereSol, false,false,false,false);  
$rs_new=$db->conn->Execute($sql);
$ciud->grabar_log_tabla('LOG_USR_CIUDADANOS',$rs_old, $rs_new, $_SESSION['usua_codi'],3,$db);


//enviar mail
$ciud->enviarMail($tipomail,$ciu_mail,$ciu_nombre,$ciu_ced,$_SESSION['inst_nombre']);

?>
<html>
<?php echo html_head(); /*Imprime el head definido para el sistema*/?>
<body>
    <br /><br /><br /><center>
    <table width="40%" border="1" align="center" class="t_bordeGris">
        <tr valign='top' align='center'>
             <td width="100%" height="30" align="center" class="listado2">
                <?php
                    if($_POST["sol_accion"] == 1)
                        echo "La solicitud de: ". $_POST["ciu_nombre"]. " " . $_POST["ciu_apellido"] ." con CI: ". $_POST["ciu_cedula"] ." fue autorizada.";
                    else
                        echo "La solicitud  fue devuelta o rechazada.<br />";
                    
                ?>
            </td>
        </tr>
        <tr> <td width="100%" height="30" align="center" class="listado2">
                <?php 
                echo "<input type='button' value='Aceptar' class='botones' name='btn_aceptar' onClick=\"window.location='cuerpoSolicitud_ext.php'\">";
                ?>
            </td></tr>
    </table></center>
    <br /><br /><br />
    
</body>
</html>
