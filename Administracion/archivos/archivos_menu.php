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
if($_SESSION["perm_actualizar_sistema"]!=1) die("ERROR: Usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
include_once "$ruta_raiz/rec_session.php";

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();
?>
<script type="text/javascript">
    function llamaCuerpo(parametros){
        top.frames['mainFrame'].location.href=parametros;
    }

    function popup_ejecutar_revertir_bodega() {
        windowprops = "top=50,left=50,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=700,height=420";
        url = './archivos_revertir_modulo.php';
        ventana = window.open(url , "popup_revertir_bodega", windowprops);
        ventana.focus();
    }

    function popup_test_bodega() {
        windowprops = "top=100,left=0,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=700,height=420";
        url = './archivos_test_bodega_grabar.php';
        ventana0 = window.open(url , "popup_test_bodega_grabar", windowprops);
        url = './archivos_test_bodega_leer.php';
        windowprops = "top=100,left=700,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=700,height=420";
        ventana2 = window.open(url , "xxpopupxx_xxtestxx_bodegaxx_leer", windowprops);
        ventana0.focus();
    }
</script>


<body>
    <center>
        <br><br>
        <table width="50%" align="center" border="0" cellpadding="0" cellspacing="5" class="borde_tab">
            <tr>
                <th colspan="2" class="titulos4"><center><strong>M&oacute;dulo de Administraci&oacute;n del Repositorio de Archivos</strong></center></th>
            </tr>
<?php
    $num_menu = 0;
    echo dibujar_opcion_menu("archivos_activar_repositorio.php","Activar nuevo repositorio", "Cierra el repositorio actual y activa uno nuevo");
    echo dibujar_opcion_menu("archivos_crear_repositorio.php", "Crear nuevo repositorio", "Crea un nuevo repositorio y lo deja en estado pendiente para ser activado luego");
?>
        </table>
        <br>
        <input type="button" name="btn_cancelar" class="botones" value="Regresar" onclick="window.location='../formAdministracion.php'">
    </center>
</body>
</html>

<?php
function dibujar_opcion_menu ($pagina, $nombre, $descripcion="", $flag_javascript=false) {
    global $num_menu;
    $funcion = "llamaCuerpo('$pagina');";
    if ($flag_javascript) $funcion=$pagina;
    $texto = "<tr>
                <td class=\"listado2\">
                    <a onclick=\"$funcion\" href='javascript:void(0);' target='mainFrame' class='vinculos' title='$descripcion'>".(++$num_menu).". $nombre</a>
                </td>
              </tr>";
    return $texto;
}
?>