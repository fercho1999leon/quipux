<?php
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

$ruta_raiz = "../..";
session_start();
if($_SESSION["usua_admin_sistema"]!=1)
    die(html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina."));
require_once "$ruta_raiz/funciones.php";
require_once "$ruta_raiz/rec_session.php";
include "$ruta_raiz/obtenerdatos.php";
include "$ruta_raiz/funciones_interfaz.php";
include_once "mnuUsuariosH.php";
echo "<html>".html_head();

$accion = 0 + $_GET["accion"];

$paginador = new ADODB_Pager_Ajax($ruta_raiz, "div_buscar_usuarios", "busqueda_paginador_usuarios_reporte.php",
                  "txt_dependencia,txt_reporte,inst_actu");
?>

<script type="text/javascript">
    
   function realizar_busqueda() { 
       document.getElementById("txt_reporte").value=0;
       paginador_reload_div('');
    }
    function seleccionar_usuario(codigo) {
        window.location='./adm_usuario.php?accion=<?=$accion?>&usr_codigo=' + codigo;
        return true;
    }
    function reportes_generar_guardar_como(tipo) {
        nuevoAjax('div_reporte_guardar_como', 'POST', 'busqueda_generar_usuario_guardar_como.php', 'tipo='+tipo);
    }
    function generar_reporte(){
        document.getElementById("txt_reporte").value=1;
        paginador_reload_div('');
    }
</script>
<?php $td1='20%'; $td2='60%'?>
<body>
  <center>
    
        <?php 

/**
* Si el usuario que ingresa al sistema es el usuario super-administrador cargar el combo con la lista de las 
* instituciones.
**/
    
      
	$inst_actu = $_SESSION["inst_codi"];
    $sql = "select inst_nombre, inst_codi from institucion where inst_estado =1";
    if($_SESSION["usua_codi"]!=0 or $_SESSION["admin_institucion"]!=1) 
    $sql.=" and inst_codi = $inst_actu";
    $sql.=" order by inst_nombre asc";
   
//    echo $sql;
    $rs = $db->conn->query($sql);
    $menu_institucion =  $rs->GetMenu2("inst_actu", $inst_actu, "0:&lt;&lt seleccione &gt;&gt;", false,"","id='inst_actu' class='select'");

?>

      <table border=0 width="100%" class="borde_tab" cellpadding="0" cellspacing="5">
          <tr><td>
              
            </td>
        </tr>
         
        <tr>
            <td width="20%" class="titulos5"><font class="tituloListado">Buscar usuarios por: </font></td>
            <td width="60%" class="listado5">
                <table width="100%" border="0">
                    <tr>
                        <td width="<?=$td1?>" class="listado5">Institución</td>
                        <td width="<?=$td2?>"><?php echo $menu_institucion; ?></td>
                    </tr>
<!--                    <tr>
                        <td width="<?=$td1?>" class="listado5">Nombre / C.I. <br>Puesto / Correo</td>
                        <td width="<?=$td2?>"><input type=text id="txt_nombre" name="txt_nombre" value="" class="tex_area" onkeypress="if (event.keyCode==13) realizar_busqueda()"></td>
                    </tr>-->
                    <tr>
                        <td width="<?=$td1?>" class="listado5"><?=$descDependencia ?></td>
                        <td width="<?=$td2?>">
<?php
            $depe_codi_admin = obtenerAreasAdmin($_SESSION["usua_codi"],$_SESSION["inst_codi"],$_SESSION["usua_admin_sistema"],$db);
            //echo $depe_codi_admin;
            
            $sql="select depe_nomb, depe_codi from dependencia where depe_estado=1 
                and inst_codi=".$_SESSION["inst_codi"];
            if ($depe_codi_admin!=0)
            $sql.=" and depe_codi in ($depe_codi_admin)";            
            $sql.=" order by 1 asc";
            //$sql="select depe_nomb, depe_codi from dependencia where depe_estado=1 and inst_codi=".$_SESSION["inst_codi"]." order by 1 asc";
            $rs=$db->conn->query($sql);
            if($rs) print $rs->GetMenu2("txt_dependencia", "0", "0:&lt;&lt; Seleccione &gt;&gt;", false,"","class='select' id='txt_dependencia'  style='width: 300px;'");
?>
                        </td>
                    </tr>
<!--                    <tr>
                        <td width="<?=$td1?>" class="listado5">Permiso</td>
                        <td width="<?=$td2?>">
<?php
//            $sql="select descripcion, id_permiso from permiso where estado=1 order by 1";
//            $rs=$db->conn->query($sql);
//            if($rs) print $rs->GetMenu2("txt_permiso", "0", "0:&lt;&lt; Seleccione &gt;&gt;", false,"","class='select' id='txt_permiso' style='width: 300px;'" );
?>
                        </td>
                    </tr>-->
<!--                    <tr>
                        <td width="<?=$td1?>" class="listado5">Estado</td>
                        <td width="<?=$td2?>">
                            <select name="txt_estado" id="txt_estado" class="select">
                                <option value='1' selected>Activos</option>
                                <option value='0'>Inactivos</option>
                                <option value='2'>Todos</option>
                            </select>
                        </td>
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
                    </tr>-->
                </table>
            </td>
            <td width="20%" align="center" class="titulos5" >
                <table>
                    <tr>
                        <td>
                            <input type="button" name="btn_buscar" value="Buscar" class="botones" onClick="realizar_busqueda();"><br>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="button" name="btn_buscar" value="Reporte" class="botones" onClick="generar_reporte();">
                        </td>
                    </tr>
                </table>
                
                
            </td>
        </tr>
    </table>
      <div id='div_reporte_guardar_como' style="width: 99%"></div>
     <input type="hidden" name="txt_reporte" id="txt_reporte" value="0">
    <div id='div_buscar_usuarios' style="width: 99%"></div>
  </center>
</body>
</html>