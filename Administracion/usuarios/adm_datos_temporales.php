<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
/*****************************************************************************************
** Administración de datos por parte del ciudadano										**
*****************************************************************************************/
$ruta_raiz = "../..";

require_once "$ruta_raiz/funciones.php";
require_once("$ruta_raiz/funciones_interfaz.php");
session_start();
include_once "$ruta_raiz/rec_session.php";
$ciu_login = limpiar_sql($_SESSION["krd"]);
$html="";
$sql = "select * from ciudadano where ciu_cedula='".substr($ciu_login,1)."'";

$rs = $db->conn->query($sql);
$sql = "select * from ciudadano_tmp where ciu_codigo=".$rs->fields['CIU_CODIGO'];

$rs2 = $db->conn->query($sql);
function ciudad_ciu($db,$ciu_codigo){
$sqlCmbCiu = "select nombre, id from ciudad where id = $ciu_codigo order by 1";
$rsCmbCiu = $db->conn->query($sqlCmbCiu);
    return $rsCmbCiu->fields['NOMBRE'];
}

function dibuja_datos($rs,$rsCmbCiu,$db){
    $html='<tr><td width="25%" class="titulos2">Cédula</td>
     <td class="listado2" width="25%">'.$rs->fields["CIU_CEDULA"].'</td>
     <td width="25%" class="titulos2">Otro Documento</td>
     <td class="listado2" width="25%">'.$rs->fields["CIU_DOCUMENTO"].'</td></tr>';
    
    $html.='<tr><td width="25%" class="titulos2">Nombre</td>
     <td class="listado2">'.$rs->fields["CIU_NOMBRE"].'</td>
     <td width="25%" class="titulos2">Apellido</td>
     <td class="listado2">'.$rs->fields["CIU_APELLIDO"].'</td></tr>';
    
    $html.='<tr><td width="25%" class="titulos2">Título</td>
     <td class="listado2">'.$rs->fields["CIU_TITULO"].'</td>
     <td width="25%" class="titulos2">Abr.Título</td>
     <td class="listado2">'.$rs->fields["CIU_ABR_TITULO"].'</td></tr>';
    
    $html.='<tr><td width="25%" class="titulos2">Institución</td>
     <td class="listado2">'.$rs->fields["CIU_CARGO"].'</td>
     <td width="25%" class="titulos2">Puesto</td>
     <td class="listado2">'.$rs->fields["CIU_EMPRESA"].'</td></tr>';
    
    $html.='<tr><td width="25%" class="titulos2">Dirección</td>
     <td class="listado2">'.$rs->fields["CIU_DIRECCION"].'</td>
     <td width="25%" class="titulos2">Email</td>
     <td class="listado2">'.$rs->fields["CIU_EMAIL"].'</td></tr>';
 
     $html.='<tr><td width="25%" class="titulos2">Teléfono</td>
     <td class="listado2">'.$rs->fields["CIU_TELEFONO"].'</td>
     <td width="25%" class="titulos2">Ciudad</td>
     <td class="listado2">'.ciudad_ciu($db,$rs->fields["CIUDAD_CODI"]).'</td></tr>';
 return $html;
}
echo "<html>".html_head(); 
?>

<body>
     <table width="100%" border="1" align="center" class="t_bordeGris" id="usr_datos">
  	<tr>
        <td class="listado2" colspan="4">
        <center><p><span class=etexto>Por el momento no está disponible el acceso a esta pantalla, <br>
                    sus datos han sido modificados y serán revisados por el Administrador del Sistema.</span></p></center>
	    </td>
	</tr>
        <tr>
        <td class="titulos4" colspan="4">
            <center><span class=etexto>Datos Actuales.</span></center>
	    </td>
	</tr>
        <?php 
        echo dibuja_datos($rs,$rsCmbCiu,$db);
        ?>
        <tr>
        <td class="titulos4" colspan="4">
            <center><span class=etexto>Datos Pendientes.</span></center>
	    </td>
	</tr>
        <?php
        echo dibuja_datos($rs2,$rsCmbCiu,$db);
        ?>
    </table>    
</body>
</html>
