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

$ruta_raiz = "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
require_once("$ruta_raiz/funciones.php");

//p_register_globals($_POST);

$path_archivo = "/tmp/reporte_cv_pdf_".$_SESSION["usua_codi"].".html";
$html = file_get_contents("$ruta_raiz/bodega$path_archivo");

//$html = base64_decode(base64_decode($reporte));


switch ($tipo) {
    case "PDF":
        require_once("$ruta_raiz/interconexion/generar_pdf.php");
        require_once("$ruta_raiz/obtenerdatos.php");

        $html = preg_replace(':<a .*?>:is', "", $html);
        $html = str_replace("</a>", "", $html);
        $html = "<html><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8'></head><body>$html</body></html>";
        $area = ObtenerDatosDependencia($_SESSION["depe_codi"],$db);
        $plantilla = "$ruta_raiz/bodega/plantillas/".$area["plantilla"].".pdf";
        $pdf = ws_generar_pdf($html, $plantilla, $servidor_pdf, "", "", "", 100,"R");

        $path_archivo = "/tmp/reporte_cv_pdf_".$_SESSION["usua_codi"].".pdf";
        file_put_contents("$ruta_raiz/bodega$path_archivo", $pdf);
        $path_descarga = "$ruta_raiz/archivo_descargar.php?path_arch=$path_archivo&nomb_arch=reporte.pdf";


        break;

    case "XLS":
        $html = preg_replace(':<a.*?>:is', '', $html);
        $html = str_replace("</a>", "", $html);
        $html = reemplaza_caracteres_html($html);
        $path_archivo = "/tmp/reporte_".$_SESSION["usua_codi"].".xls";
        file_put_contents("$ruta_raiz/bodega$path_archivo", $html);
        $path_descarga = "$ruta_raiz/archivo_descargar.php?path_arch=$path_archivo&nomb_arch=reporte.xls";
        break;

    default:
        die("");
        break;
}
?>

<iframe  name="ifr_descargar_archivo" id="ifr_descargar_archivo" style="display: none" src="<?=$path_descarga?>">
            Su navegador no soporta iframes, por favor actualicelo.</iframe>
