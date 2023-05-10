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

$ruta_raiz = "../..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

session_start();
if($_SESSION["usua_admin_sistema"]!=1) {
  echo html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
  die("");
}
include_once "$ruta_raiz/rec_session.php";

include_once "../tbasicas/listaAreas.php";
include "$ruta_raiz/funciones_interfaz.php";
?>

<!-- LIBRERIAS PARA GENERADOR DE ARBOL AJAX -->
<link rel="StyleSheet" href="<?=$ruta_raiz?>/js/nornix-treemenu-2.2.0/example/style/menu_uno.css" type="text/css" media="screen" />
<script type="text/javascript" src="<?=$ruta_raiz?>/js/nornix-treemenu-2.2.0/treemenu/nornix-treemenu.js"></script>
<? require_once "$ruta_raiz/js/ajax.js";?>
<link rel="stylesheet" href="<?=$ruta_raiz?>/estilos/orfeo.css">

<!-- Para utilizacion de Ajax -->
<script language="JavaScript" src="<?=$ruta_raiz?>/js/prototype.js" type ="text/javascript"></script>
<script language="JavaScript" src="<?=$ruta_raiz?>/js/general1.js"  type="text/javascript"></script>

<script type='text/JavaScript'>
    function datosArea(depeCodi) {
        nuevoAjax('usua_estado', 'GET', 'usuariosArea_ajax.php', 'area='+depeCodi);
        nuevoAjax('usua_area', 'POST', 'reporte_usuarios_01_cargar.php', 'area='+depeCodi+'&estado=1');
        return;
    }

    function consultar_usuarios(depeCodi) {
        if(depeCodi==undefined)
        {
            alert('Por favor, seleccione en area.');
            return false;
        }
        area = depeCodi;
        estado = document.getElementById('cmb_estado').value;
        nuevoAjax('usua_area', 'POST', 'reporte_usuarios_01_cargar.php', 'area='+area+'&estado='+estado);
        return;
    }

    function ltrim(s) {
       return s.replace(/^\s+/, "");
    }

    function imprimirUsuariosAreas(){
        // Generar pdf de usuarios por areas areas
        var x = (screen.width - 20) / 2;
        var y = (screen.height - 20) / 2;
        preview = window.open('generarPDFUsuariosAreas.php','', 'scrollbars=yes,menubar=no,height=20,width=20,resizable=yes,toolbar=no,location=no,status=no');
        preview.moveTo(x, y);
        preview.focus();
    }
</script>

<?php echo "<html>" . html_head() . "<body>"; ?>
        <table width="100%">
        <tr><td align="center" class="titulos4" colspan="2"><font size="2">Reporte de Usuarios por &Aacute;reas</font></td></tr>
            <tr>
                <td valign="top" width="25%">
                <table class="borde_tab">
                    <tr>
                    <?php $listAreas = obtenerAreas($_SESSION['inst_codi'], $db); ?>
                    <td valign="middle" width="10%">
                        <div id="menu" class="menu"><a href="javascript:;" title="Exportar Usuarios a pdf" onclick="imprimirUsuariosAreas();"><br>&nbsp;&nbsp;&nbsp;&nbsp;Exportar Usuarios a pdf&nbsp;&nbsp;</a>
                            <?php
                                echo $listAreas;
                            ?>
                        </div>
                    </td>
                    </tr>
                </table>
                </td>
                <td width="75%" valign="top">
                    <div id="usua_estado"></div>
                    <div id="usua_area"></div></br>
                    <center>
                    <input type="button" name="regresar" value="Regresar" class="botones" onclick="window.location='mnuUsuarios.php'"></center>
                </td>
            </tr>
        </table>
</body>
</html>
<script>
    nuevoAjax('usua_estado', 'GET', 'usuariosArea_ajax.php', '');
</script>