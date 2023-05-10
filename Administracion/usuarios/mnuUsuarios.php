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
session_start();
if($_SESSION["usua_admin_sistema"]!=1) die("");
include_once "$ruta_raiz/rec_session.php";

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();
?>

<body>
    <center>
        <br><br>
        <table width="40%" align="center" border="0" cellpadding="0" cellspacing="5" class="borde_tab">
            <tr>
                <td class="titulos4"><center>Administraci&oacute;n de usuarios y permisos</center></td>
            </tr>            
            <tr>
                <td class="listado2">&nbsp;&nbsp;&nbsp;&nbsp;
                    <a href='javascript:void(0);' onclick="window.location='cuerpoUsuario.php?accion=2';" class="vinculos" target='mainFrame'>
                        1. Usuario
                    </a>
                </td>
            </tr>
            <?php
            /*
            <tr>
                <td  class="listado2">&nbsp;&nbsp;&nbsp;&nbsp;
                    <a href='javascript:void(0);' onclick="window.location='cuerpoUsuario.php?accion=2';" class="vinculos" target='mainFrame'>
                        2. Editar Usuario
                    </a>
                </td>
            </tr> */
                ?>
            <tr>
                <td class="listado2">&nbsp;&nbsp;&nbsp;&nbsp;
                    <a href='javascript:void(0);' onclick="window.location='<?=$ruta_raiz?>/Administracion/subrogacion/buscar_usuario_nuevo_subr.php?accion=3';" class="vinculos" target='mainFrame'>
                        2. Crear Subrogación de Puesto
                    </a>
                </td>
            </tr>
            <tr>
                <td class="listado2">&nbsp;&nbsp;&nbsp;&nbsp;
                    <a href='javascript:void(0);' onclick="window.location='<?=$ruta_raiz?>/Administracion/subrogacion/buscar_usuario_nuevo_subr_des.php?accion=3';" class="vinculos" target='mainFrame'>
                        3. Desactivar Subrogación de Puesto
                    </a>
                </td>
            </tr>
            <tr>
                <td class="listado2">&nbsp;&nbsp;&nbsp;&nbsp;
                    <a href='javascript:void(0);' onclick="window.location='listadoUsuariosinarea.php?accion=4';" class="vinculos" target='mainFrame'>
                        4. Usuarios Sin Área
                    </a>
                </td>
            </tr>
            <tr>
                <td class="listado2">&nbsp;&nbsp;&nbsp;&nbsp;
                    <a href='javascript:void(0);' onclick="window.location='reporte_usuarios_01.php';" class="vinculos" target='mainFrame'>
                        5. Reporte Usuarios
                    </a>
                </td>
            </tr>
           
  <tr>
<? if($_SESSION["usua_perm_backup"]==1) { ?>
            <tr>
                <td class="listado2">&nbsp;&nbsp;&nbsp;&nbsp;
                    <a href='javascript:void(0);' onclick="window.location='<?=$ruta_raiz?>/backup/backup_usuarios_menu.php';" class="vinculos" target='mainFrame'>
                        6. Respaldos de documentos de usuarios
                    </a>
                </td>
            </tr>            
<? } ?>
               <!-- Permisos -->
           <tr>
            <td  class="listado2">&nbsp;&nbsp;&nbsp;&nbsp;
                <?php
                        $inst_codiv = $_SESSION['inst_codi'];
                        $parametrosFuncion = "../dependencias/permiso_areas_usuarios.php?accion=5&des_activar=3";
                        $parametrosFuncion = "'".$parametrosFuncion."'";
                    ?>
                <a href='javascript:void(0);' onclick="window.location='../dependencias/permiso_areas_usuarios.php?accion=5&des_activar=3';" class="vinculos" target='mainFrame'>
                    <?php if ($_SESSION["usua_perm_backup"]==1) 
                     echo "7. Permisos a Usuarios";
                    else 
                        echo "8. Permisos a Usuarios";
                    ?>
                </a>               
            </td>
          </tr>
        </table>
        <br>
        <input type="button" name="btn_cancelar" class="botones" value="Regresar" onclick="window.location='../formAdministracion.php'">
    </center>
</body>
</html>
