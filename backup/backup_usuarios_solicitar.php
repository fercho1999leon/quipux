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
/*************************************************************************************
** Permite solicitar respaldos de la documentacion de los usuarios                  **
**                                                                                  **
** Desarrollado por:                                                                **
**      Mauricio Haro A. - mauricioharo21@gmail.com                                 **
*************************************************************************************/

  $ruta_raiz = "..";
  session_start();
  include_once "$ruta_raiz/rec_session.php";
  require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
  include_once "$ruta_raiz/funciones_interfaz.php";

  if($_SESSION["usua_perm_backup"]!=1) {
      echo html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
      die("");
  }

  echo "<html>".html_head();
  include_once "$ruta_raiz/js/ajax.js";
  $paginador = new ADODB_Pager_Ajax($ruta_raiz, "div_buscar_usuarios", "backup_usuarios_buscar_usuario.php", "txt_nombre,cmb_institucion","");

?>
<script language="JavaScript" type="text/JavaScript">
    function buscar_usuarios() {
        txt_nombre = document.getElementById('txt_nombre').value;
        cmb_institucion = document.getElementById('cmb_institucion').value;
        nuevoAjax('div_buscar_usuarios', 'GET', 'backup_usuarios_buscar_usuario.php', 'txt_nombre=' + txt_nombre + '&cmb_institucion=' + cmb_institucion);
    }

    function seleccionar_usuario(codigo) {
        if (confirm ('¿Desea solicitar los respaldos de los documentos del usuario?')) {
            nuevoAjax('div_grabar_solicitud', 'POST', 'backup_usuarios_solicitar_grabar.php', 'txt_usua_codi=' + codigo);
        }
    }
</script>
<body>
  <center>
    <form name="formulario" action="" method="post">
        <table width="100%" align="center" class=borde_tab border="0">
            <tr>
                <td width="100%" class="titulos5">
                  <center>
                    <br>Solicitar respaldos completos de los documentos de un usuario<br>&nbsp;
                  </center>
                </td>
            </tr>
        </table>
        <br>
        <table width="100%" align="center" class=borde_tab border="0">
            <tr>
                <td width="25%" class="titulos5">
                    Nombre o C.I.:
                </td>
                <td width="50%" class="listado5" valign="middle">
                    <input name="txt_nombre" id="txt_nombre" type="text" size="60" class="tex_area" value="">
                </td>
                <td width="25%" class="titulos5" valign="middle" rowspan="2">
                    <center><input type='button' value='Buscar' name='btn_buscar' class='botones' onClick='buscar_usuarios()'></center>
                </td>
            </tr>
            <tr>
                <td width="25%" class="titulos5">
                    Instituci&oacute;n:
                </td>
                <td width="50%" class="listado5" valign="middle">
<?
                $sql="select inst_nombre, inst_codi from institucion where inst_codi<>0 order by 1 asc";
                $rs=$db->conn->query($sql);
                if($rs) print $rs->GetMenu2("cmb_institucion", $cmb_institucion, "0:&lt;&lt; Todas las instituciones &gt;&gt;", false,"","class='select' id='cmb_institucion'" );
?>
                </td>
            </tr>
        </table>
        <div id='div_grabar_solicitud'></div>
        <div id='div_buscar_usuarios'></div>
        <br>
        <input type="button" name="btn_cancelar" value="Regresar" class="botones" 
               onClick="window.location='backup_usuarios_menu.php';">
      </form>
    </center>
  </body>
</html>
