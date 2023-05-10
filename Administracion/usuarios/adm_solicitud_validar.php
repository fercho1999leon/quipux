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
require_once("$ruta_raiz/funciones_interfaz.php");

global $servidor_registro_civil;

session_start();
include_once "$ruta_raiz/rec_session.php";
$ciu_codigo = limpiar_sql($_POST["ciu_codigo"]);

if(isset ($_GET["codigo"]))
    $ciu_codigo = $_GET["codigo"];

$apellidosnombres = $_GET["nombre"];

$sql = "select * from solicitud_firma_ciudadano where ciu_codigo=$ciu_codigo";

$rs = $db->conn->query($sql);

if ($rs->EOF) {
    echo html_error("No se encontr&oacute; el usuario en el sistema.");
    die("");
}
$ciu_cedula1 = $rs->fields["CIU_CEDULA"];
if(strlen($ciu_cedula1)== 10){

        if (trim(substr($ciu_cedula1,0,2))!= "99") {
            include_once "$ruta_raiz/interconexion/validar_datos_ciudadano.php";            
            $datos_rc = ws_validar_datos_ciudadano($ciu_cedula1);
            }
}
else
    {
    echo "<html>".html_head();
    echo "<center>
        <br />
        <table width='40%' border='2' align='center' class='t_bordeGris'>
            <tr>
            <td width='100%' height='30' class='listado2'>
                <span class='listado5'><center><B>Solo valido para números de cédula</B></center></span>
            </td>
            </tr>
            <tr>
            <td height='30' class='listado2'>
                <center><input class='botones' type='button' value='Cerrar' onClick='window.close();'></center>
            </td>
            </tr>
        </table>
    </center>";
    die();
    }
//aqui me quede
        //var_dump($datos_rc);
        //die();   
?>
<html>
<head>
<title>Datos del Ciudadano</title>
<link href="<?=$ruta_raiz?>/estilos/light_slate.css" rel="stylesheet" type="text/css">
<link href="<?=$ruta_raiz?>/estilos/splitmenu.css" rel="stylesheet" type="text/css">
<link href="<?=$ruta_raiz?>/estilos/template_css.css" rel="stylesheet" type="text/css">
</head>
<body>

<form method="post" action="adm_solicitud_validar.php">
<table border=0 width=100% align="center" class="borde_tab" cellspacing="0">

    <?if($apellidosnombres != $datos_rc['nombre']) {?>

    <tr align="center" 	class="titulos2">
        <td class="titulos2"><font color="Maroon">Existen inconsistencias en los datos(Apellido, Nombre)</font></td>
    </tr>

    <?}?>

    <tr align="center" class="titulos2">
	<td class="titulos2">DATOS DEL CIUDADANO</td>
    </tr>
</table>
    
<table width="100%" border="0" cellspacing="1" cellpadding="0" align="center" class="borde_tab">
    <tr >
         <td class="listado5" width="30%">Cédula:</td>
	 <td class="listado1" width="70%"><?=$datos_rc["cedula"]?></td>
    </tr>
    <tr >
         <td class="listado5" width="30%">Nombres:</td>
	 <td class="listado1" width="70%"><?=$datos_rc["nombre"]?></td>
    </tr>
 
     <tr>
                <td class="listado5">Estado Civil:</td>
                <td class="listado1"><?=$datos_rc["estado_civil"]?></td>
     </tr>
     <tr>
                <td class="listado5">Domicilio:</td>
                <td class="listado1"><?=$datos_rc["domicilio"]?></td>
     </tr>
         <tr>
                <td class="listado5">Intrucción:</td>
                <td class="listado1"><?=$datos_rc["instruccion"]?></td>
     </tr>
         <tr>
                <td class="listado5">Profesión:</td>
                <td class="listado1"><?=$datos_rc["profesion"]?></td>
     </tr>
   
</table>

    <table width="100%" border="0" cellspacing="1" cellpadding="0" align="center" class="borde_tab">
     <tr>
 	<td class=listado2  align="center">
	    <center><input name="Cerrar" type="button" class="botones" id="envia22" onClick="window.close();"value="Cerrar"></center>
	</td>
    </tr>
    </table>

<br/>
</form>

</body>
</html>
