<?php
/**  Programa para el manejo de gestion documental, oficios, memorandos, circulares, acuerdos
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
// Limpiamos el cache
//header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
//header("Last-Modified: " . gmdate("D, d M Y H:i") . " GMT");
//header("Cache-Control: no-store, no-cache, must-revalidate");
//header("Cache-Control: post-check=0, pre-check=0", false);
//header("Pragma: no-cache");

session_start();
$ruta_raiz = ".";
include_once "$ruta_raiz/rec_session.php";
if (isset ($replicacion) && $replicacion && $config_db_replica_menu_correspondencia!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_menu_correspondencia);
include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head(false);
include_once "$ruta_raiz/js/ajax.js";

if (!$carpeta) $carpeta = 0;
if ($_SESSION["tipo_usuario"]==2) $carpeta = 80;
$num = 1;

function crear_grupo_bandeja($id, $nombre, $items) {
    $grupo = '<tr onclick="mostrar_ocultar_grupo_carpetas(\'tr_menu_'.$id.'\')">
                <td class="menu_titulo">
                    <img id="tr_menu_'.$id.'_img_com" src="./iconos/bandeja_comprimida.png" alt="+" style="display: none">
                    <img id="tr_menu_'.$id.'_img_exp" src="./iconos/bandeja_expandida.png" alt="-">
                    '.$nombre.'
                </td>
            </tr>
            <tr id="tr_menu_'.$id.'">
                <td width="100%">
                    <table width="100%" border="0" cellpadding="0" cellspacing="3">'.$items.'</table>
                </td>
            </tr>';
    return $grupo;
}

function crear_item_bandeja ($carpeta, $nombre, $descripcion, $destino="") {
    global $num;
    $descripcion = str_replace("*usuario*", $_SESSION["usua_nomb"], $descripcion);

    $script = "";
    ++$num;
    if ($carpeta != 0) { //Si llama desde una bandeja
        $destino = "cuerpo.php?carpeta=$carpeta&nomcarpeta=$nombre";
        $nombre .= " <spam id='spam_carpeta_$carpeta'></spam>";
        $script = "<script type='text/javascript'>cambiar_contador('$carpeta','".cargar_contadores_bandejas($carpeta)."');</script>";
    }

    $tr = "<tr class='menu_fondo1' name='menu_tr$num' id='menu_tr$num' onMouseOver='cambiar_fondo(this,1)' onMouseOut='cambiar_fondo(this,0)'>
            <td>&nbsp;&nbsp;
                <a onclick=\"llamaCuerpo('$destino'); cambioMenu($num);\" class=\"menu_princ\"
                    target='mainFrame' title='$descripcion' href='javascript:void(0);'>$nombre
                </a>
                $script
            </td>
        </tr>
       ";
    return $tr;
}

?>

<script type="text/javascript" src="<?=$ruta_raiz?>/js/shortcut.js"></script>
<script type="text/javascript">
    var carpeta = 2;

    function bloquear_menu (accion) {
        try {
            if (accion == 1) {
                document.getElementById('div_bloquear_menu').style.height = '100%';
                timerID = setTimeout("bloquear_menu(0)", 2500);
            } else {
                document.getElementById('div_bloquear_menu').style.height = '0%';
                clearTimeout(timerID);
            }
        } catch (e) {}
    }

    function cambioMenu(valor) {
        
        try {            
            document.getElementById('menu_tr' + carpeta).style.cssText = 'background-color:#FFFFFF; font-size: 10px;';
            document.getElementById('menu_tr' + valor).style.cssText = 'background-color:#a8bac6; font-size: 10px;';
            bloquear_menu(1);
            carpeta = valor;
        } catch (e) {}
	//document.location='correspondencia.php?carpeta='+valor;
    }

    function cambiar_contador(bandeja, valor) {           
        try {
            if (valor.toString() == '-1' || trim(valor.toString())=='')
                document.getElementById("spam_carpeta_"+bandeja).innerHTML = '';
            else
                document.getElementById("spam_carpeta_"+bandeja).innerHTML = '('+ valor +')';
            bloquear_menu(0);
        } catch (e) { }
    }

    function cambiar_fondo(fila, fondo) {        
        if (fila.id=='menu_tr'+carpeta) return;
        color = "#e3e8ec";
        if (fondo == 0)
            color = "#ffffff";
        fila.style.cssText = 'background-color:'+color;
    }

    function llamaCuerpo(parametros){
        try {
            top.frames['mainFrame'].location.href=parametros;
        } catch (e) {
            window.top.frames['mainFrame'].location.href=parametros;
        }
    }

    var menuTimerId = 0;
    function recargar_estadisticas() {
        clearTimeout(menuTimerId);
        if ('<?if (is_file('./bodega/estadisticas_menu.html')) echo "Si";?>' != 'Si') return;
        nuevoAjax('div_estadisticas_menu', 'POST', './bodega/estadisticas_menu.html', '');
        menuTimerId = setTimeout("recargar_estadisticas()", 300000);
    }

    function init_menu() {
        // Pintar la bandeja de recibidos
        if ('<?=$_SESSION["inst_codi"]?>'=='0') {
            cambioMenu('3'); // Bandeja recibidos de ciudadanos
        } else {
            cambioMenu('4'); // Bandeja recibidos funcionarios y ciudadanos con firma
        }
        // comprimir la bandeja "Otras Bandejas"
        mostrar_ocultar_grupo_carpetas("tr_menu_otras_bandejas");
    }

    function mostrar_ocultar_grupo_carpetas(id) {
        try {
            var grupo = document.getElementById(id);
            if (grupo.style.display == 'none') {
                grupo.style.display = '';
                document.getElementById(id+'_img_exp').style.display = '';
                document.getElementById(id+'_img_com').style.display = 'none';
            } else {
                grupo.style.display = 'none';
                document.getElementById(id+'_img_exp').style.display = 'none';
                document.getElementById(id+'_img_com').style.display = '';
            }
        } catch(e) {}
        return;
    }

</script>

<body onload="recargar_estadisticas(); init_menu();">
<center>
    <div id="div_bloquear_menu" style="width: 100%; height: 0%; z-index: 1000; position: fixed; top: 0; left: 0;"></div>
    <br>
    <table width="160px" border="0" cellpadding="0" cellspacing="3">

<?php
    if ($_SESSION["tipo_usuario"]==2) {
        if ($_SESSION["inst_codi"] == 1) {
            include "$ruta_raiz/menu/nuevo_salida.php";
            include "$ruta_raiz/menu/ciudadano_firma.php";
        } else {
            include "$ruta_raiz/menu/ciudadano.php";
        }
    } else {
        if ($_SESSION["depe_codi"]!=0) { // Si no tiene un área no puede realizar ninguna accion porque genera errores de secuencias.            
            include "$ruta_raiz/menu/nuevo_salida.php";
            include "$ruta_raiz/menu/bandeja.php";
            include "$ruta_raiz/menu/radicacion.php";
        }
        include "$ruta_raiz/menu/menuPrimero.php";
    }
?>
    </table>
    <br>
    <div id="div_estadisticas_menu" style="width: 160px;"></div>
   
<?php
if (!$version_light)
    include_once "$ruta_raiz/menu/alertas_documentos_vencidos.php";
?>

</center>
</body>
</html>