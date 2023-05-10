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

$ruta_raiz = isset($ruta_raiz) ? $ruta_raiz : "../..";
session_start();
include_once "$ruta_raiz/rec_session.php";

include_once "$ruta_raiz/class_control/class_gen.php";
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/obtenerdatos.php";
$areas_pdf = "<html>".html_head().'
            <body >';

// Obtener plantilla del area de usuario actual
$area = ObtenerDatosDependencia($_SESSION["depe_codi"],$db);
$plantilla = "$ruta_raiz/bodega/plantillas/".$area["plantilla"].".pdf";

$gen_fecha = new CLASS_GEN();
$date =  date("m/d/Y");
$fecha = $gen_fecha->traducefecha($date);

$datosFecha = "Fecha: ".$fecha."  -  Generado por: ".$_SESSION['usua_nomb'];

$areas_pdf .= '<center>';
$areas_pdf .= "<h5>&Aacute;reas de la Instituci&oacute;n ".$_SESSION["inst_nombre"]."</h5>";

include_once "$ruta_raiz/recursivas/funciones_recursivas.php";
$lista = new FuncionesRecursivas($db);
$lista->tabla = "dependencia";
$lista->id_tabla = "depe_codi";
$lista->id_padre = "depe_codi_padre";
$lista->tabulacion = 6;
$lista->condicion = "depe_estado=1";
$lista->buscar_padre('depe_codi', "inst_codi = ".$_SESSION['inst_codi']);
$lista->add_campo("Nombre", "80", "depe_nomb");
$lista->add_campo("Siglas", "20", "dep_sigla");
//$lista->display["debug"] = true;
$areas_pdf .= $lista->generar_tabla_recursiva();
$areas_pdf .= '</center></body></html>';

$areas_pdf = preg_replace(':<a.*?/a>:is', '	&diams;', $areas_pdf);
//echo "planitllas".$plantilla;
//echo "<br>areas_sesion_".$_SESSION["depe_codi"];
//echo $areas_pdf;
include "$ruta_raiz/config.php";
require_once("$ruta_raiz/interconexion/generar_pdf.php");

$pdf = ws_generar_pdf($areas_pdf, $plantilla, $servidor_pdf, "", $datosFecha, "", "");

$nombreArch = "reporteAreas.pdf";
header( "Content-Disposition: attachment; filename=".$nombreArch);

header("Content-Type: application/pdf");

print $pdf;
?>