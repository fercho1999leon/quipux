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
** Acceso Ciudadanos
 * Elimina el archivo de la bodega
 * Cambia de estado la solicitud rechazada									**
*****************************************************************************************/
$ruta_raiz = "../..";
session_start();

include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/obtenerdatos.php";
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/funciones.php";
include_once "../ciudadanos/util_ciudadano.php";
$ciud = New Ciudadano($db);

if (isset($_GET)){    
    $tipo=$_GET['tipo'];
    if($tipo=='b'){//eliminar el archivo
         $sql_log="select * from solicitud_firma_ciudadano where ciu_codigo = ".$_SESSION["usua_codi"];
         $rs_old=$db->conn->Execute($sql_log);
         $sql.= "update solicitud_firma_ciudadano set sol_acuerdo=0 where ciu_codigo = ".$_SESSION["usua_codi"];        
         $db->query($sql);
         $rs_new=$db->conn->Execute($sql_log);
         $ciud->grabar_log_tabla('LOG_USR_CIUDADANOS',$rs_old, $rs_new, $_SESSION['usua_codi'],3,$db);
         unlink("$ruta_raiz/bodega/ciudadanos/".$_SESSION["usua_codi"].'_acuerdo.pdf.p7m');         
    }else
        echo ver_datos_solicitud($db,$ruta_raiz);
  
}
?>
<?php if($tipo=='b'){

    ?><html>
    <?echo html_head(); //Imprime el head definido para el sistema?>
<body>
    <form name="frmConfirmaCreacion" action="adm_solicitud_ciu.php" method="post">
         <br>
        &nbsp;
        </br>
        <center><table width="40%" border="2" align="center" class="t_bordeGris">
            <tr>
            <td width="100%" height="30" align="center" class="listado2">
                <font size="1"><b>Se ha eliminado correctamente el archivo.</b></font>
           
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
<?php } ?>
<?
function ver_datos_solicitud($db,$ruta_raiz){
    $datos = array();
    $sql = "select * from solicitud_firma_ciudadano where ciu_codigo=".$_SESSION["usua_codi"]."";         
    $rs2 = $db->conn->query($sql);
    $acuerdo_sol = $rs2->fields['SOL_ACUERDO'];
    $estado_sol = $rs2->fields['SOL_ESTADO'];  
    echo "&nbsp;";
    if ($acuerdo_sol==1 and ($estado_sol==0 or $estado_sol==1)){//sol rechazado o en edicion        
         $url = "$ruta_raiz/bodega/ciudadanos/".$_SESSION["usua_codi"]."_acuerdo.pdf.p7m";
                   if (is_file($url))
                       echo '<input name="btn_enviar" type="submit" class="botones_largo" title="Enviar" value="Enviar" readonly="<?=$estado_read?>" onclick="return ValidarInformacion(2);"/>';
    }if ($estado_sol==0 || $estado_sol==1)
        echo '<br>&nbsp;<br><font color="black">Su solicitud de Firma aún no ha sido enviada.</font>';
         
}
?>

