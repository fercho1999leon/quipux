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
require_once "$ruta_raiz/funciones_interfaz.php";

session_start();
include_once "$ruta_raiz/rec_session.php";
$flag_login = true;
if ($_SESSION["admin_institucion"]!=1) die("Usted no tiene permisos para acceder a esta p&aacute;gina.");

$ciu_codigo = limpiar_sql($_POST["ciu_codigo"]);


$ciu_cedula       = limpiar_sql(trim($_POST["ciu_cedula"]));
$ciu_documento    = limpiar_sql(trim($_POST["ciu_documento"]));
$ciu_nombre      = limpiar_sql(trim($_POST["ciu_nombre"]));
$ciu_apellido    = limpiar_sql(trim($_POST["ciu_apellido"]));
$ciu_titulo       = limpiar_sql(trim($_POST["ciu_titulo"]));
$ciu_abr_titulo  = limpiar_sql(trim($_POST["ciu_abr_titulo"]));
$ciu_empresa      = limpiar_sql(trim($_POST["ciu_empresa"]));
$ciu_cargo        = limpiar_sql(trim($_POST["ciu_cargo"]));
$ciu_direccion    = limpiar_sql(trim($_POST["ciu_direccion"]));
$ciu_email       = limpiar_sql(trim($_POST["ciu_email"]));
$ciu_telefono     = limpiar_sql(trim($_POST["ciu_telefono"]));
$sol_observaciones    = limpiar_sql($_POST["sol_observaciones"]);
$chk_planillah   = limpiar_sql($_POST['chk_planillah']);
$chk_cedulah    = limpiar_sql($_POST["chk_cedulah"]);
$chk_acuerdoh    = limpiar_sql($_POST["chk_acuerdoh"]);
$sol_accion = $_POST["sol_accion"] ;
$ciudad_codi = limpiar_sql(trim($_POST["codi_ciudad"]));
$sol_firma = limpiar_sql(trim($_POST["sol_firma"]));



$accion_btn_cancelar = "history.back();";

//verificar si ya existe un usuario con este numero de cedula
$ciu_cedula_verificar = limpiar_sql($_POST["ciu_cedula"]);
$sqlusua = "select * from usuarios where usua_cedula = '$ciu_cedula_verificar'";

$rsusua = $db->conn->query($sqlusua);

    $ciu_nombre_verificar = $rsusua->fields["USUA_NOMB"]. " " .$rsusua->fields["USUA_APELLIDO"];

?>
<html>
<? echo html_head(); /*Imprime el head definido para el sistema*/?>
<body>
    <div id='wrapper'>    
    <div id='mainbody'><div class='shad-1'><div class='shad-2'><div class='shad-3'><div class='shad-4'><div class='shad-5'>
    <form name='formulario' action="adm_solicitud_actualizar.php" method="post">
    <br /><br /><br />
    <input type="hidden" id="sol_accion" name="sol_accion" value="<?=$sol_accion?>">
    <input type="hidden" id="ciu_codigo" name="ciu_codigo" value="<?=$ciu_codigo?>">
    <input type="hidden" id="ciu_cedula" name="ciu_cedula" value="<?=$ciu_cedula?>">
    <input type="hidden" id="ciu_documento" name="ciu_documento" value="<?=$ciu_documento?>">
    <input type="hidden" id="ciu_nombre" name="ciu_nombre" value="<?=$ciu_nombre?>">
    <input type="hidden" id="ciu_apellido" name="ciu_apellido" value="<?=$ciu_apellido?>">
    <input type="hidden" id="ciu_titulo" name="ciu_titulo" value="<?=$ciu_titulo?>">
    <input type="hidden" id="ciu_abr_titulo" name="ciu_abr_titulo" value="<?=$ciu_abr_titulo?>">
    <input type="hidden" id="ciu_empresa" name="ciu_empresa" value="<?=$ciu_empresa?>">
    <input type="hidden" id="ciu_cargo" name="ciu_cargo" value="<?=$ciu_cargo?>">
    <input type="hidden" id="ciu_direccion" name="ciu_direccion" value="<?=$ciu_direccion?>">
    <input type="hidden" id="ciu_email" name="ciu_email" value="<?=$ciu_email?>">
    <input type="hidden" id="ciu_telefono" name="ciu_telefono" value="<?=$ciu_telefono?>">
    <input type="hidden" id="sol_observaciones" name="sol_observaciones" value="<?=$sol_observaciones?>">
    <input type="hidden" id="chk_planillah" name="chk_planillah" value="<?=$chk_planillah?>">
    <input type="hidden" id="chk_cedulah" name="chk_cedulah" value="<?=$chk_cedulah?>">
    <input type="hidden" id="chk_acuerdoh" name="chk_acuerdoh" value="<?=$chk_acuerdoh?>">
    <input type="hidden" id="ciudad_codi" name="ciudad_codi" value="<?=$ciudad_codi?>">
    <input type="hidden" id="sol_firma" name="sol_firma" value="<?=$sol_firma?>">

    <table align='center' width='100%' cellpadding='0' cellspacing='0' class='mainbody'>
        <tr valign='top' align='center'>
            <td class='left'  align='center' width='100%'>                
                    <?
                    if($sol_accion == 1){

                    if ($rsusua && !$rsusua->EOF)
                            echo "<center>
        <br />
        <table width='40%' border='2' align='center' class='t_bordeGris'>
            <tr>
            <td width='100%' height='30' class='listado2' colspan='2'>
                <span class='listado5'><center><B>El ciudadano con el número de cédula $ciu_cedula_verificar ya existe como usuario del sistema</B></center></span>
                <span class='listado5'><center><B>¿Desea aprobar la solicitud y crear un nuevo usuario?</B></center></span>
            </td>
            </tr>
         <tr >
         <td class='listado5' width='40%'>Cédula:</td>
	 <td class='listado5' width='60%'>$ciu_cedula_verificar</td>
         </tr>
         <tr >
         <td class='listado5' width='40%'>Nombres:</td>
	 <td class='listado5' width='60%'>$ciu_nombre_verificar </td>
         </tr>


            <tr>
            <td height='30' class='listado2' >
                <center><input class='botones' type='submit' value='Aceptar'></center>

            </td>
            <td height='30' class='listado2'>

                <center><input class='botones' type='button' value='Regresar' onClick=\"$accion_btn_cancelar\"></center>
            </td>
            </tr>
        </table>
    </center>";
                       else
                            echo "<center>
        <br />
        <table width='40%' border='2' align='center' class='t_bordeGris'>
            <tr>
            <td width='100%' height='30' class='listado2' colspan='2'>
                <span class='listado5'><center><B>¿Desea aprobar la solicitud y crear un nuevo usuario?</B></center></span>
            </td>
            </tr>
            <tr>
            <td height='30' class='listado2' >
                <center><input class='botones' type='submit' value='Aceptar'></center>
                
            </td>
            <td height='30' class='listado2'>

                <center><input class='botones' type='button' value='Regresar' onClick=\"$accion_btn_cancelar\"></center>
            </td>
            </tr>
        </table>
    </center>";
                  }
                  else
                      echo "<center>
        <br />
        <table width='40%' border='2' align='center' class='t_bordeGris'>
            <tr>
            <td width='100%' height='30' class='listado2' colspan='2'>
                <span class='listado5'><center><B>¿Desea rechazar la solicitud?</B></center></span>
            </td>
            </tr>
            <tr>
            <td height='30' class='listado2' >
                <center><input class='botones' type='submit' value='Aceptar'></center>

            </td>
            <td height='30' class='listado2'>

                <center><input class='botones' type='button' value='Regresar' onClick=\"$accion_btn_cancelar\"></center>
            </td>
            </tr>
        </table>
    </center>";


                  ?>
            </td>
        </tr>
    </table>
    <br /><br /><br />
    </form>
    </div></div></div></div></div></div>    
    </div>
</body>
</html>
