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

$ruta_raiz = "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "respaldo_funciones.php";

//if($_SESSION["usua_perm_backup"]!=1) die("");

echo "<html>".html_head();

//Se consulta usuario que autoriza
$var_aprueba = 0;
//Se comenta autorización
//$usua_codi_autoriza = ObtenerCodigoUsuarioAutoriza(33,0,0,$_SESSION["usua_codi"],$db);
//if ($usua_codi_autoriza == $_SESSION["usua_codi"])
//    $var_aprueba = 1;

?>
<body>
<center>
<form name='frmAdministracion' action='../Administracion/formAdministracion.php' method="post">
  <br><br>
  <table width="32%" align="center" border="0" cellpadding="0" cellspacing="5" class="borde_tab">
  <tr>
    <td colspan="2" class="titulos4"><center><strong>Solicitudes personales</strong></center></td>
  </tr>
  <tr>
    <td class="listado2" width="98%"><a href="respaldo_informacion.php?txt_tipo_lista=4" class="vinculos" target='mainFrame'>1. Solicitar respaldos</a></td>
  </tr>
  <tr>
    <td class="listado2" width="98%"><a href="respaldo_lista.php?txt_tipo_lista=1" class="vinculos" target='mainFrame'>2. Mis Solicitudes</a></td>
  </tr> 
  <?php if($var_aprueba == 1){?>
  <tr>
    <td colspan="2" class="titulos4"><center><strong>Autorización de Solicitudes</strong></center></td>
  </tr>
  <tr>
    <td class="listado2" width="98%"><a href="respaldo_lista.php?txt_tipo_lista=2" class="vinculos" target='mainFrame'>1. Solicitudes por autorizar</a></td>
  </tr>
  <tr>
    <td class="listado2" width="98%"><a href="respaldo_lista.php?txt_tipo_lista=3" class="vinculos" target='mainFrame'>2. Listado de Solicitudes</a></td>
  </tr>
   <?php }?>

  <?php if($_SESSION["usua_admin_sistema"]==1){ ?>
  <tr>
    <td colspan="2" class="titulos4"><center><strong>Solicitudes de la Institución</strong></center></td>
  </tr>
  <tr>
    <td class="listado2" width="98%"><a href="respaldo_informacion.php?txt_tipo_lista=5" class="vinculos" target='mainFrame'>1. Solicitar respaldos</a></td>
  </tr>
  <tr>
    <td class="listado2" width="98%"><a href="respaldo_lista.php?txt_tipo_lista=6" class="vinculos" target='mainFrame'>2. Solicitudes por enviar</a></td>
  </tr>
  <tr>
    <td class="listado2" width="98%"><a href="respaldo_lista.php?txt_tipo_lista=7" class="vinculos" target='mainFrame'>3. Listado de Solicitudes</a></td>
  </tr>
   <?php }?> 
  <tr>
    <td align="center" class="listado2">
      <center><input align="middle" class="botones" type="submit" name="Submit" value="Regresar"></center>
    </td>
  </tr>
</table>
</form>
</center>
</body>
</html>