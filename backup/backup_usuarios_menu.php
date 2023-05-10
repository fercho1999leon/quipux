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
if($_SESSION["usua_perm_backup"]!=1) die("");
include_once "$ruta_raiz/rec_session.php";

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();
?>
<script type="text/javascript">
    function popup_ejecutar_respaldo() {
        windowprops = "top=50,left=50,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=700,height=500";
        url = '<?=$nombre_servidor_respaldos?>/backup/backup_usuarios_ejecutar.php?id_sess=<?=session_id()?>';
        ventana = window.open(url , "popup_respaldo", windowprops);
        ventana.focus();
    }
</script>
<body>
<center>
<form name='frmMnuUsuarios' action='../Administracion/usuarios/mnuUsuarios.php' method="post">
  <br><br>
  <table width="32%" align="center" border="0" cellpadding="0" cellspacing="5" class="borde_tab">
  <tr>
    <td colspan="2" class="titulos4"><center><strong>Solicitudes Institucionales</strong></center></td>
  </tr>
  <tr>
    <td class="listado2" width="98%"><a href="respaldo_informacion.php?txt_tipo_lista=8" class="vinculos" target='mainFrame'>1. Solicitar respaldos por fecha</a></td>
  </tr>
  <tr>
    <td class="listado2" width="98%"><a href="respaldo_lista.php?txt_tipo_lista=9" class="vinculos" target='mainFrame'>2. Solicitudes por enviar</a></td>
  </tr>
  <tr>
    <td class="listado2" width="98%"><a href="respaldo_lista.php?txt_tipo_lista=10" class="vinculos" target='mainFrame'>3. Listado de Solicitudes</a></td>
  </tr>
   <tr>
    <td class="listado2" width="98%"><a href="respaldo_lista.php?txt_tipo_lista=11" class="vinculos" target='mainFrame'>4. Calendarización de Solicitudes</a></td>
  </tr>
  <tr>
    <td colspan="2" class="titulos4"><center><strong>Respaldo de Documentos</strong></center></td>
  </tr>
  <tr>
    <td class="listado2" width="98%"><a href="backup_usuarios_solicitar.php" class="vinculos" target='mainFrame'>1. Solicitar respaldos de documentos</a></td>
  </tr>
  <tr>
    <td class="listado2" width="98%"><a href="backup_usuarios_estado.php" class="vinculos" target='mainFrame'>2. Verificar estado de los respaldos</a></td>
  </tr>
  <tr>
      <td class="listado2" width="98%"><a href="javascript:void(0);" onclick="popup_ejecutar_respaldo();" class="vinculos">3. Ejecutar proceso para respaldar documentos</a></td>
  </tr>
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
