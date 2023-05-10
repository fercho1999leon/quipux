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

session_start();
$ruta_raiz = isset($ruta_raiz) ? $ruta_raiz : "..";

//Datos para Reporte-Tareas
include_once "$ruta_raiz/rec_session.php";
$db = new ConnectionHandler("$ruta_raiz","reportes");

// Obtener plantilla del area del usuario actual
include_once "$ruta_raiz/obtenerdatos.php";
$area = ObtenerDatosDependencia($_SESSION["depe_codi"],$db);

//Incluir funciones para consultar tareas del radicado.
include_once "$ruta_raiz/tareas/tareas_funciones.php";

$verrad = $_GET["verrad"];

$doc_pdf = "<html>
<head>
<title>.: QUIPUX - TAREAS :.</title>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
</head>
<body><br>";

$doc_pdf .= dibujar_tareas($db, 0, $verrad, $ruta_raiz, "PDF");
$doc_pdf .= "<br>";
$doc_pdf .= dibujar_tareas($db, 1, $verrad, $ruta_raiz, "PDF");
$doc_pdf .= "</body></html>";

$doc_pdf = str_replace("width='95%'","width='95%' rules='rows'", $doc_pdf);

//GENERACION DEL PDF
include "$ruta_raiz/config.php";
require_once("$ruta_raiz/interconexion/generar_pdf.php");
$plantilla = "";
$plantilla = "$ruta_raiz/bodega/plantillas/".$area["plantilla"].".pdf";
$pdf = ws_generar_pdf($doc_pdf, $plantilla, $servidor_pdf,"","","","","R");
$nomArch="Reporte_Tareas.pdf";
header( "Content-Disposition: attachment; filename=$nomArch");
header("Content-Type:application/pdf");//.application/pdf
header("Content-Transfer-Encoding: binary");
echo  $pdf;
?>