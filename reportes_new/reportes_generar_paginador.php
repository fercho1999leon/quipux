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

$cols = split(",", $txt_lista_columnas);

$estilo_tabla  = "style='border: thin solid #377584;'";
$estilo_titulo = "bgcolor='#6a819d' align='center' valign='middle' height='20px' style='color: #FFFFFF;'";
$estilo_tr0    = "bgcolor='#FFFFFF' align='left' valign='middle' height='20px' style='color: #000000;'";
$estilo_tr1    = "bgcolor='#e3e8ec' align='left' valign='middle' height='20px' style='color: #000000;'";
$estilo_a      = "style='color: #000000;'";

$tabla = "<table $estilo_tabla border='0' width='100%'>";
$tabla .= "<tr $estilo_titulo><td colspan='2'><font size='1'><b>Reportes - Sistema de Gesti&oacute;n Documental &quot;Quipux&quot;</b></font></td></tr>";
$tabla .= "<tr><td $estilo_tr1><font size='1'>Tipo de Reporte:</font></td><td $estilo_tr0><font size='1'>".$lista_reportes[$txt_tipo_reporte]["nombre"]."</font></td></tr>";
$tabla .= "<tr><td $estilo_tr1><font size='1'>Descripci&oacute;n del Reporte:</font></td><td $estilo_tr0><font size='1'>".$lista_reportes[$txt_tipo_reporte]["descripcion"]."</font></td></tr>";
$tabla .= "<tr><td $estilo_tr1><font size='1'>Fecha:</font></td><td $estilo_tr0><font size='1'>".date("Y-m-d").$descZonaHoraria."</font></td></tr>";
$tabla .= "</table><br>";


$tabla  .= "<table $estilo_tabla border='0' width='100%'>";
$tabla .= "<tr $estilo_titulo>";

for ($i=1 ; $i<count($cols) ; ++$i) {
    if (isset($columnas[$cols[$i]]))
        $tabla .= "<td><font size='1'><b>".$columnas[$cols[$i]]."</b></font></td>";
    else
        $tabla .= "<td><font size='1'><b>".$cols[$i]."</b></font></td>";
}
$tabla .= "</tr>";

$num_filas = 0;
while (!$rs_paginador->EOF and ($num_filas < $num_max_registros or (0+$num_max_registros)==0)) {
    $tabla .= "<tr " . ${"estilo_tr".($num_filas % 2)} . ">";
    for ($i=1 ; $i<count($cols) ; ++$i) {
        if (isset($rs_paginador->fields[strtoupper($cols[$i])."_DRILL"])) {
            $tabla .= "<td><font size='1'><a href='javascript:".$rs_paginador->fields[strtoupper($cols[$i])."_DRILL"]."' $estilo_a>".$rs_paginador->fields[strtoupper($cols[$i])]."</a></font></td>";
        } else {
            $tabla .= "<td><font size='1'>".$rs_paginador->fields[strtoupper($cols[$i])]."</font></td>";
        }
    }
    $tabla .= "</tr>";
    $rs_paginador->MoveNext();
    ++$num_filas;
}

$mensaje = "No. total de registros: $num_filas.";
if ($num_filas >= $num_max_registros and (0+$num_max_registros)>0) $mensaje = "Se limit&oacute; el resultado a $num_max_registros registros.";

$tabla .= "<tr $estilo_titulo><td colspan='" . (count($cols)-1) . "'><font size='1'><b>$mensaje</b></font></td></tr>";
$tabla .= "</table><br>";

$tabla .= "<table $estilo_tabla border='0' width='100%'>";
$tabla .= "<tr $estilo_titulo><td colspan='2'><font size='1'><b>Descripci&oacute;n de las columnas mostradas en el reporte</b></font></td></tr>";
for ($i=1 ; $i<count($cols) ; ++$i) {
    if (isset($columnas[$cols[$i]])) {
        $tabla .= "<tr><td ".${"estilo_tr".(($i-1)%2)}." width='15%'><font size='1'><b>".$columnas[$cols[$i]].":</b></font></td>";
        $tabla .= "<td ".${"estilo_tr".(($i-1)%2)}." width='85%'><font size='1'>".$columnas_desc[$cols[$i]]."</font></td></tr>";
    }
}
$tabla .= "</table>";

$path_archivo = "/tmp/reporte_".$_SESSION["usua_codi"].".html";
file_put_contents("$ruta_raiz/bodega$path_archivo", $tabla);

echo $tabla;
?>
