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
/*************************************************************************************/
/*                                                                                   */
/*************************************************************************************/

$ruta_raiz = "..";
session_start();
if($_SESSION["usua_admin_sistema"]!=1)
    die(html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina."));
require_once "$ruta_raiz/funciones.php";
require_once "$ruta_raiz/rec_session.php";
include "$ruta_raiz/obtenerdatos.php";
include "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

$txt_tipo_lista = $_GET["txt_tipo_lista"];


$paginador = new ADODB_Pager_Ajax($ruta_raiz, "div_buscar_usuarios", "respaldo_usuario_paginado.php",
                  "txt_nombre,txt_dependencia,txt_permiso,txt_estado,cmb_usr_perfil,cmb_institucion");
?>

<script type="text/javascript">

    function realizar_busqueda() {
       paginador_reload_div('');
    }
    
    function seleccionar_usuario(codigo) {
        window.location='respaldo_solicitud.php?txt_tipo_lista=<?=$txt_tipo_lista?>&usr_codigo=' + codigo;
        return true;
    }
    
</script>
<?php $td1='20%'; $td2='60%'?>
<body>
  <center>
    <table border=0 width="100%" class="borde_tab" cellpadding="0" cellspacing="5">
        <tr>
            <td width="20%" class="titulos5"><font class="tituloListado">Buscar usuario del sistema: </font></td>
            <td width="60%" class="listado5">
                <table width="100%" border="0">
                    <? if($_SESSION["usua_perm_backup"]==1 and $txt_tipo_lista == 8) { ?>
                    <tr>
                        <td width="<?=$td1?>" class="listado5">Instituci&oacute;n:</td>
                        <td width="<?=$td2?>">
                            <?
                            $sql="select inst_nombre, inst_codi from institucion where inst_codi<>0 order by 1 asc";
                            $rs=$db->conn->query($sql);
                            $cmb_institucion = $_SESSION["inst_codi"];
                            if($rs) print $rs->GetMenu2("cmb_institucion", $cmb_institucion, "0:&lt;&lt; Todas las instituciones &gt;&gt;", false,"","class='select' id='cmb_institucion' style='width: 400px;'" );
                            ?>
                        </td>
                    </tr>
                    <? } else echo "<input type=hidden id='cmb_institucion' name='cmb_institucion' value='0'>"; ?>
                    <tr>
                        <td width="<?=$td1?>" class="listado5">Nombre / C.I. <br>Puesto / Correo</td>
                        <td width="<?=$td2?>"><input type=text id="txt_nombre" name="txt_nombre" value="" class="tex_area"></td>
                    </tr>
                    <tr>
                        <td width="<?=$td1?>" class="listado5"><?=$descDependencia ?></td>
                        <td width="<?=$td2?>">
<?php           
            if ($_SESSION["usua_codi"] != 0)
                $depe_codi_admin = obtenerAreasAdmin($_SESSION["usua_codi"],$_SESSION["inst_codi"],$_SESSION["usua_admin_sistema"],$db);
            $sql="select depe_nomb, depe_codi from dependencia where depe_estado=1 and inst_codi=".$_SESSION["inst_codi"];
            if ($depe_codi_admin!=0)
                $sql.=" and depe_codi in ($depe_codi_admin)";
            $sql.=" order by 1 asc";           
            $rs=$db->conn->query($sql);
            if($rs) print $rs->GetMenu2("txt_dependencia", "0", "0:&lt;&lt; Seleccione &gt;&gt;", false,"","class='select' id='txt_dependencia'  style='width: 300px;'");
?>
                        </td>
                    </tr>
                    <tr>
                        <td width="<?=$td1?>" class="listado5">Permiso</td>
                        <td width="<?=$td2?>">
<?php
            $sql="select descripcion, id_permiso from permiso where estado=1 order by 1";
            $rs=$db->conn->query($sql);
            if($rs) print $rs->GetMenu2("txt_permiso", "0", "0:&lt;&lt; Seleccione &gt;&gt;", false,"","class='select' id='txt_permiso' style='width: 300px;'" );
?>
                        </td>
                    </tr>
                    <tr>
                        <td width="<?=$td1?>" class="listado5">Estado</td>
                        <? if($_SESSION["usua_admin_sistema"]==1 and $txt_tipo_lista == 5) {?>                            
                            <td width="<?=$td2?>">
                                <select name="txt_estado" id="txt_estado" class="select" >                                
                                <option value='0' selected>Inactivos</option>                                
                                </select>
                            </td>
                        <? } ?>
                        <? if($_SESSION["usua_perm_backup"]==1 and $txt_tipo_lista == 8) { ?>                            
                            <td width="<?=$td2?>">
                                <select name="txt_estado" id="txt_estado" class="select" >
                                <option value='1' selected>Activos</option>
                                <option value='0' >Inactivos</option>
                                <option value='2'>Todos</option>
                                </select>
                            </td>
                        <? } ?>
                    </tr>
                    <tr>
                        <td class="listado5" width="<?=$td1?>">Perfil</td>
                        <td class="listado2" width="<?=$td2?>">
                        <select name="cmb_usr_perfil" id="cmb_usr_perfil" class="select">
                            <option value='2'> Todos </option>
                            <option value='0'> Normal </option>
                            <option value='1'> Jefe </option>
                        </select>
                        </td>
                    </tr>
                </table>
            </td>
            <td width="20%" align="center" class="titulos5" >
                <input type="button" name="btn_buscar" value="Buscar" class="botones" onClick="realizar_busqueda();">
            </td>
        </tr>
    </table>
    <br>
    <input name="btn_accion" type="button" class="botones" value="Regresar" onClick="location='respaldo_informacion.php?txt_tipo_lista=<?=$txt_tipo_lista?>'"/>
    <br>&nbsp;
    <div id='div_buscar_usuarios' style="width: 99%"></div>
  </center>
</body>
</html>