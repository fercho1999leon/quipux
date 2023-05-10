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


$estilo_tabla  = "style='border: thin solid #377584;'";
$estilo_titulo = "bgcolor='#6a819d' align='center' valign='middle' height='20px' style='color: #FFFFFF;'";
$estilo_tr0    = "bgcolor='#FFFFFF' align='left' valign='middle' height='20px' style='color: #000000;'";
$estilo_tr1    = "bgcolor='#e3e8ec' align='left' valign='middle' height='20px' style='color: #000000;'";
$estilo_a      = "style='color: #000000;'";

$tabla = "<table $estilo_tabla border='0' width='100%'>";
$tabla .= "<tr $estilo_titulo><td colspan='2'><font size='1'><b>Reportes - Sistema de Gesti&oacute;n Documental &quot;Quipux&quot;</b></font></td></tr>";
$tabla .= "<tr><td $estilo_tr1><font size='1'>Tipo de Reporte:</font></td><td $estilo_tr0><font size='1'>Documentos de la Carpeta Virtual '". $nombre_completo. "'</font></td></tr>";

$tabla .= "<tr><td $estilo_tr1><font size='1'>Fecha:</font></td><td $estilo_tr0><font size='1'>".date("Y-m-d").$descZonaHoraria."</font></td></tr>";
$tabla .= "</table><br>";


$tabla  .= "<table $estilo_tabla border='0' width='100%'>";
$tabla .= "<tr $estilo_titulo>";

$tabla .= "<td><font size='1'><b>De</b></font></td>";
$tabla .= "<td><font size='1'><b>Para</b></font></td>";
$tabla .= "<td><font size='1'><b>Asunto</b></font></td>";
$tabla .= "<td><font size='1'><b>Fecha Documento</b></font></td>";        
$tabla .= "<td><font size='1'><b>No. Documento</b></font></td>";
$tabla .= "<td><font size='1'><b>Usuario Actual</b></font></td>";
$tabla .= "<td><font size='1'><b>Estado</b></font></td>";

$tabla .= "</tr>";

$num_filas = 0;

if($rs_paginador){
    while (!$rs_paginador->EOF) {
        $tabla .= "<tr " . ${"estilo_tr".($num_filas%2)} .">";
        foreach ($rs_paginador->fields as $campo => $dato) {
            if (trim($campo, "0123456789")!="")
                $tabla .= "<td><font size='1'>$dato</font></td>";
        }
        $tabla .= "</tr>\n";
        $rs_paginador->MoveNext();
        ++$num_filas;
    }
}

$mensaje = "No. total de registros: $num_filas.";
$tabla .= "<tr $estilo_titulo><td colspan='7'><font size='1'><b>$mensaje</b></font></td></tr>";
$tabla .= "</table><br>";

$path_archivo = "/tmp/reporte_cv_pdf_".$_SESSION["usua_codi"].".html";
file_put_contents("$ruta_raiz/bodega$path_archivo", $tabla);
echo $tabla;

?>
