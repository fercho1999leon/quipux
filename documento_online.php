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
/*****************************************************************************************
**											**
******************************************************************************************/
session_start();
$ruta_raiz = ".";
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/seguridad/obtener_nivel_seguridad.php";
include_once "$ruta_raiz/obtenerdatos.php";

$radi_nume = limpiar_numero($_GET["verrad"]);
$nivel_seguridad_documento = obtener_nivel_seguridad_documento($db, $radi_nume);
if ($nivel_seguridad_documento < 2) die ("Lo sentimos, usted no tiene permisos para ver este documento");

$datosrad = ObtenerDatosRadicado($radi_nume,$db);
$usr_actual = ObtenerDatosUsuario($datosrad["usua_actu"],$db);


$path_descarga = "fjs_radicado_descargar_archivo('".$datosrad["radi_nume_radi"]."', '".$datosrad["radi_imagen"]."', 0, 'download')";
if ($datosrad["radi_path"]!="" or $datosrad["radi_imagen"]!="" or $datosrad["arch_codi"]!=0 or ($datosrad["estado"]==1 and substr($datosrad["radi_nume_radi"], -1)=="0"))
    $path_archivo_embebido = "fjs_radicado_descargar_archivo('".$datosrad["radi_nume_radi"]."', '".$datosrad["radi_imagen"]."', 0, 'embeded');";


include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();
?>
<script type="text/javascript">
    function fjs_radicado_descargar_archivo(radicado, anex_codigo, arch_tipo, tipo_descarga) {
        path_descarga = './anexos/anexos_descargar_archivo.php?radi_nume='+radicado+'&anex_codigo=' + anex_codigo + '&arch_tipo=' + arch_tipo + '&tipo_descarga=' + tipo_descarga;
        if (tipo_descarga=='embeded' && fjs_verificar_plugin_navegador ('acrobat')) path_descarga += '_ar';
        if (tipo_descarga=='embeded')
            document.getElementById('ifr_mostrar_archivo').src=path_descarga;
        else
            document.getElementById('ifr_descargar_archivo').src=path_descarga;
        return;
    }

    function vista_previa() {
        <?=$path_descarga?>
    }
</script>

<body onload="<?=$path_archivo_embebido?>">
    <table width="100%" border="0" cellpadding="0" cellspacing="1" >
        <tr style="height: 20px">
            <td class="titulos4" width="15%">
                <a href="javascript:vista_previa();">
                    <img src="<?=$ruta_raiz?>/imagenes/zoom_in.png" width="15" height="15" alt="Vista Previa" border="0">
                    Descargar Documento
                </a>
            </td>
            <td class="titulos4" width="25%">&nbsp;&nbsp;No. Documento: &nbsp;&nbsp;&nbsp;&nbsp;<?=$datosrad["radi_nume_text"] ?></td>
            <td class="titulos4" width="30%">&nbsp;&nbsp;Usuario actual: &nbsp;&nbsp;&nbsp;&nbsp;<?=$usr_actual["nombre"]?></td>
            <td class="titulos4" width="30%">&nbsp;&nbsp;<?=$descDependencia?> actual: &nbsp;&nbsp;&nbsp;&nbsp;<?=$usr_actual["dependencia"]?></td>
        </tr>
    </table>
    
    <iframe  name="ifr_descargar_archivo" id="ifr_descargar_archivo" style="display: none;" src="">
                Su navegador no soporta iframes, por favor actualicelo.</iframe>
    <iframe name='ifr_mostrar_archivo' id='ifr_mostrar_archivo' style='width:100%; height:95%; overflow: hidden; border: none;'
                src=''>
                Su navegador no soporta iframes, por favor actualicelo.</iframe>

</body>
</html>