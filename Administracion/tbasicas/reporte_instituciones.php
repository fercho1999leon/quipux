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
include_once "$ruta_raiz/funciones_interfaz.php";

$cols = 11;

$estilo_tabla  = "style='border: thin solid #377584;'";
$estilo_titulo = "bgcolor='#6a819d' align='center' valign='middle' height='20px' style='color: #FFFFFF;'";
$estilo_tr0    = "bgcolor='#FFFFFF' align='left' valign='middle' height='20px' style='color: #000000;'";
$estilo_tr1    = "bgcolor='#e3e8ec' align='left' valign='middle' height='20px' style='color: #000000;'";
$estilo_a      = "style='color: #000000;'";

$tabla = "<table $estilo_tabla border='0' width='100%'>";
$tabla .= "<tr $estilo_titulo><td colspan='2'><font size='1'><b>Reportes - Sistema de Gesti&oacute;n Documental &quot;Quipux&quot;</b></font></td></tr>";
$tabla .= "<tr><td $estilo_tr1><font size='1'>Fecha:</font></td><td $estilo_tr0><font size='1'>".date("Y-m-d").$descZonaHoraria."</font></td></tr>";
$tabla .= "</table><br>";


$tabla  .= "<table $estilo_tabla border='0' width='100%'>";
$tabla .= "<tr $estilo_titulo>";

        $tabla .= "<td><font size='1'><b>CÓDIGO</b></font></td>";
        $tabla .= "<td><font size='1'><b>NOMBRE</b></font></td>";
        $tabla .= "<td><font size='1'><b>RUC</b></font></td>";
        $tabla .= "<td><font size='1'><b>SIGLA</b></font></td>";
      
$tabla .= "</tr>";

$num_filas = 0;
$i=0;
$sql = "select inst_codi as codigo, coalesce(inst_ruc,' ') as ruc, coalesce(inst_nombre,' ') as nombre,
                 coalesce(inst_sigla,' ') as sigla from institucion where inst_estado <> 0 order by inst_codi";
$rs = $db->conn->query($sql);
if (!$rs or $rs->EOF) die("No se encontraron registros para el reporte solicitado");
while (!$rs->EOF) {
   $tabla .= "<tr>";
   
    
    $tabla .= "<td><font size='1'>".$rs->fields['CODIGO']."</font></td>";
    $tabla .= "<td><font size='1'>".$rs->fields['NOMBRE']."</font></td>";
    
    $tabla .= "<td><font size='1'>".$rs->fields['RUC']."</font></td>";
    $tabla .= "<td><font size='1'>".$rs->fields['SIGLA']."</font></td>";
    
    
   
   $tabla .= "</tr>";
    $rs->MoveNext();
    ++$num_filas;
}

$mensaje = "No. total de registros: $num_filas.";
$tabla .= "<tr $estilo_titulo><td colspan='4'><font size='1'><b>$mensaje</b></font></td></tr>";
$tabla .= "</table><br>";
$html = $tabla;

$tipo = $_POST["tipo"];

?>

            
<?php
if ($tipo=='xls') {
$html = preg_replace(':<a.*?>:is', '', $html);
        $html = str_replace("</a>", "", $html);
        $html = reemplaza_caracteres_html($html);
        $path_archivo = "/tmp/reporte_".$_SESSION["usua_codi"].".".$tipo;
        file_put_contents("../../bodega$path_archivo", $html);
        $path_descarga = "../../archivo_descargar.php?path_arch=$path_archivo&nomb_arch=reporte.xls";
}else{
    
    require_once("$ruta_raiz/interconexion/generar_pdf.php");
        require_once("$ruta_raiz/obtenerdatos.php");

        $html = preg_replace(':<a .*?>:is', "", $html);
        $html = str_replace("</a>", "", $html);
        $html = "<html><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8'></head><body>$html</body></html>";
        $area = ObtenerDatosDependencia($_SESSION["depe_codi"],$db);
        $plantilla = "$ruta_raiz/bodega/plantillas/".$area["plantilla"].".pdf";
        $pdf = ws_generar_pdf($html, $plantilla, $servidor_pdf, "", "", "", 100,"R");

        $path_archivo = "/tmp/reporte_".$_SESSION["usua_codi"].".pdf";
        file_put_contents("$ruta_raiz/bodega$path_archivo", $pdf);
        $path_descarga = "$ruta_raiz/archivo_descargar.php?path_arch=$path_archivo&nomb_arch=reporte.pdf";
}


?>
<iframe  name="ifr_descargar_archivo" id="ifr_descargar_archivo" style="display: none" src="<?=$path_descarga?>">
            Su navegador no soporta iframes, por favor actualicelo.</iframe>




