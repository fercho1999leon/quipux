<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
/*****************************************************************************************
** Administración de datos por parte del ciudadano
 * Acceso ciudadano										**
*****************************************************************************************/
$ruta_raiz = "../..";
session_start();
require_once "$ruta_raiz/funciones.php";
require_once("$ruta_raiz/funciones_interfaz.php");


include_once "$ruta_raiz/rec_session.php";
$ciu_login = limpiar_sql($_SESSION["krd"]);
include_once "util_ciudadano.php";
$ciud = New Ciudadano($db);

//$sql = "select * from ciudadano where ciu_cedula='".substr($ciu_login,1)."'";
$sql = "select * from ciudadano where ciu_codigo=".$_SESSION['usua_codi'];
$rs = $db->conn->query($sql);

$sql = "select * from ciudadano_tmp where ciu_codigo=".$_SESSION['usua_codi'];
//echo $sql;
$rs2 = $db->conn->query($sql);


function dibuja_datos($rs,$rsCmbCiu,$db,$ciud){
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
     <td class="listado2">'.$rs->fields["CIU_EMPRESA"].'</td>
     <td width="25%" class="titulos2">Puesto</td>
     <td class="listado2">'.$rs->fields["CIU_CARGO"].'</td></tr>';
    
    $html.='<tr><td width="25%" class="titulos2">Dirección</td>
     <td class="listado2">'.$rs->fields["CIU_DIRECCION"].'</td>
     <td width="25%" class="titulos2">Referencia</td>
     <td class="listado2">'.$rs->fields["CIU_REFERENCIA"].'</td></tr>';
    
    $html.='<tr><td width="25%" class="titulos2">Teléfono</td>
     <td class="listado2">'.$rs->fields["CIU_TELEFONO"].'</td>
     <td width="25%" class="titulos2">Email</td>
     <td class="listado2">'.$rs->fields["CIU_EMAIL"].'</td></tr>';
    

 return $html;
}
echo "<html>".html_head();
require_once "$ruta_raiz/js/ajax.js";
?>
<script type="text/javascript" src="adm_ciudadanos.js"></script>
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
            echo dibuja_datos($rs,$rsCmbCiu,$db,$ciud);
        ?>
        <tr><td width='25%' class='titulos2'>Ciudad</td><td>
                <input type="text" size="30" value="<?php $ciud->dibujarCiudad($rs->fields["CIUDAD_CODI"]);?>" id="inputString" onkeypress="lookup();" autocomplete="off" readonly/>
            </td></tr>
        <tr>
        
        <td class="titulos4" colspan="4">
            <center><span class=etexto>Datos Pendientes.</span></center>
	    </td>
	</tr>
        <?php
            echo dibuja_datos($rs2,$rsCmbCiu,$db,$ciud);
        ?>
        <tr><td width='25%' class='titulos2'>Ciudad</td><td>
                <input type="text" size="30" value="<?php $ciud->dibujarCiudad($rs2->fields["CIUDAD_CODI"]);?>" id="inputString" onkeypress="lookup();" autocomplete="off" readonly/>
            </td></tr>
       
    </table>
     <?php
            echo $ciud->verHistorico($_SESSION['usua_codi'],1,'1,4');
     ?>
</body>
</html>
