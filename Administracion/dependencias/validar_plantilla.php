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

$ruta_raiz = "../..";
session_start();
include_once "$ruta_raiz/rec_session.php";
require_once "$ruta_raiz/interconexion/generar_pdf.php";

$plantilla = "$ruta_raiz/bodega/plantillas/".(0+$_GET["id_plantilla"]).".pdf";
$html = '
    <html>
        <head>
            <title>TEMP-IP-A1-2010-59</title>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        </head>
        <body style="text-align: justify;">
            <b>Asunto: </b>Validar plantilla<br>&nbsp;<br>&nbsp;<br>
            Se&ntilde;or Ingeniero<br>Juan P&eacute;rez<br><b>Director Administrativo</b><br><b>PRESIDENCIA DE LA REP&Uacute;BLICA</b><br>
            Presente.<br>&nbsp;<br>&nbsp;<br>
            De mi consideraci&oacute;n:<br>&nbsp;<br>
            Este es un documento de prueba para validar la plantilla<br>&nbsp;<br>
            Con sentimientos de distinguida consideraci&oacute;n.<br>&nbsp;<br>
            Atentamente, <br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>
            Dr. Marco Flores<br><b>DIRECTOR FINANCIERO</b>&nbsp;<br>
            <dl><dt><font size=2>Anexos: </font></dt><dd><font size=2> - Anexo 1<br> - Anexo 2</font></dd></dl>
        </body>';


$pdf = ws_generar_pdf_base64($html, $plantilla, $servidor_pdf, "1", "INSTITUCION-AREA-001-OF", "Quito, 02 de enero de 2013", "100", "");
$tamanio = strlen($pdf)/8*6;

header("Content-Disposition: attachment; filename=prueba_plantilla.pdf");
header("Content-Length: $tamanio");
header("Content-Type: application/pdf");
header("Content-Transfer-Encoding: binary");
echo base64_decode($pdf);

?>
