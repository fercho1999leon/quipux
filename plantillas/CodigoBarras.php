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
$ruta_raiz = "..";
include_once "$ruta_raiz/rec_session.php";
if ($nuevo=="no") {
    $verrad = $valRadio;
    if (!strlen(trim ($valRadio))){
        echo "<link rel='stylesheet' href='$ruta_raiz/estilos/orfeo.css'>";

        include_once "$ruta_raiz/funciones_interfaz.php";
        $mensajeError = "<html>".html_head();
        $mensajeError .= "<center><br><table class='borde_tab' width=100% CELSPACING=5>
                            <tr class=titulosError>
                                <td align='center'>No hay Documento seleccionado para realizar la Impresi&oacute;n
                                </td>
                            </tr>
                            <tr>
                                <td align='center'><input type='button' value='Regresar' onClick='history.back();' name='enviardoc' class='botones' id='Cancelar'></td>
                            </tr>
                        </table></center></body>
                        </html>";
        die ($mensajeError);
    	//die ("<table class='borde_tab' width=100% CELSPACING=5><tr class=listado1><td><h2>No hay Documentos seleccionados para la impresión de comprobante</h2></td><td><A class=vinculos HREF='javascript:history.back();'>Regresar</A></td></tr></table>");
    }
}

function strtoupper2($cadena) {
    $cadena = strtoupper($cadena);
    $cadena = str_replace("á","Á",str_replace("é","É",str_replace("í","Í",str_replace("ó","Ó",str_replace("ú","Ú",str_replace("ñ","Ñ",$cadena))))));
    return $cadena;
}

include "$ruta_raiz/include/barcode/index.php";
include "$ruta_raiz/class_control/class_gen.php";
include "$ruta_raiz/obtenerdatos.php";

$registro = ObtenerDatosRadicado($verrad,$db);
//$tmp2 = "";
//$usr_login = "";
foreach (explode('-',$registro["usua_rem"]) as $tmp) {
    if (trim($tmp)!="") {
	$usr = ObtenerDatosUsuario($tmp,$db);
	$usr_login = substr($usr["login"],1);
//	$usr_login .= $tmp2 . "C" . $usr["cedula"];
//	$tmp2 = " - ";
    }
}
$institucion = ObtenerDatosInstitucion($registro["inst_actu"],$db);
$usr = ObtenerDatosUsuario($registro["usua_radi"],$db);
//$gen_fecha = new CLASS_GEN();
//$date = substr(ObtenerCampoRadicado("radi_fech_radi",$verrad,$db),0,10);
//$fecha = $gen_fecha->traducefecha($date);
//$fecha = trim(substr($fecha,strpos($fecha,",")+1));
$date = ObtenerCampoRadicado("radi_fech_radi",$verrad,$db);
$fecha = substr($date,0,19)." GMT ".substr($date,-3);

$tamano_papel = "a4";
$orientacion_papel = "portrait";

$inicio = '
<html>
<head>
<title>.: IMPRIMIR COMPROBANTES :.</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
';

if ($tipo_comp==1 || $tipo_comp==0) {
$codigo_barras = '
&nbsp;
<script type="text/php">
    $obj_id = $pdf->open_object();
    $max_x = $pdf->get_width();
    $max_y = $pdf->get_height();
    $fuente = "serif";
    $pdf->image("'.$ruta_raiz.$institucion["logo"].'","'. substr($institucion["logo"],-3).'", $max_x-180, 25, 60, 20);
    $pdf->image("'.$file.'.png", "png", $max_x-180, 50, 170, 40);
    $cadena = "'.iconv("ISO-8859-1", "UTF-8", $institucion["nombre"]).'";
    $pdf->text ($max_x-120, 25, $cadena, $fuente, 8);
    $cadena = "'.$registro["radi_nume_text"].'";
    $pdf->text ($max_x-120, 35, $cadena, $fuente, 8);
    $pdf->close_object();
    $pdf->add_object($obj_id, "add");
</script>';
}

if ($tipo_comp==0)
    $pagina = '$pdf-> new_page ();';
else
    $pagina='';

if ($tipo_comp==2 || $tipo_comp==0) {

$comprobante .= '
&nbsp;
<script type="text/php">
    '.$pagina.'
    $obj_id = $pdf->open_object();
    $max_x = $pdf->get_width();
    $max_y = $pdf->get_height();
    $fuente = "serif";
    $pdf->image("'.$ruta_raiz.$institucion["logo"].'","'. substr($institucion["logo"],-3).'", $max_x-215, $max_y-130, 60, 20);
    $cadena = "'.iconv("ISO-8859-1", "UTF-8", strtoupper2($institucion["nombre"])).'";
    $tf = 8;
    do {
    	$tamano = Font_Metrics::get_text_width($cadena, $fuente, $tf);
    	if ($tamano<=200)
	    break;
    	--$tf;
    } while ($tf >=3);';
 // Imprimir en el comprobante el número de teléfono de la institución en caso de tenerlo
 if(trim($institucion['telefono'])!='')
 {
    $comprobante .= '
    $pdf->text ($max_x-250, $max_y-118, $cadena, $fuente, $tf);
    $cadena = "'.iconv("ISO-8859-1", "UTF-8", 'Teléfono(s)'). ': '.$institucion['telefono'].'";';
 }
 
 $comprobante .= '
    $pdf->text ($max_x-250, $max_y-108, $cadena, $fuente, $tf);
    $cadena = "Documento No. : '.$registro["radi_nume_text"].'";
    $pdf->text ($max_x-250, $max_y-90, $cadena, $fuente, 10);
    $cadena = "Fecha                 : '.$fecha.'";
    $pdf->text ($max_x-250, $max_y-80, $cadena, $fuente, 10);
    $cadena = "Recibido por      : '.iconv("ISO-8859-1", "UTF-8", $usr["nombre"]).'";
    $pdf->text ($max_x-250, $max_y-70, $cadena, $fuente, 10);
    $cadena = "Para verificar el estado de su documento ingrese a";
    $pdf->text ($max_x-250, $max_y-60, $cadena, $fuente, 10);
    $cadena = "'.$nombre_servidor.'";
    $pdf->text ($max_x-220, $max_y-50, $cadena, $fuente, 10);';
	$comprobante .= '$cadena = "con el usuario: \"'.$usr_login.'\""; ';
    $comprobante .= '$pdf->text ($max_x-218, $max_y-40, $cadena, $fuente, 10);
    $pdf->close_object();
    $pdf->add_object($obj_id, "add");
</script>';
}
//Se imprime el comprobante en ticket
if ($tipo_comp==3 || $tipo_comp==0) {

    $tamano_papel = "ticket zb";
    $orientacion_papel = "letter";

    $comprobante .= '
    &nbsp;
    <script type="text/php">
        '.$pagina.'
        $obj_id = $pdf->open_object();
        $max_x = $pdf->get_width();
        $max_y = $pdf->get_height();
        $fuente = "serif";        
        $cadena = "'.iconv("ISO-8859-1", "UTF-8", strtoupper2($institucion["nombre"])).'";
        $tf = 8;
        do {
            $tamano = Font_Metrics::get_text_width($cadena, $fuente, $tf);
            if ($tamano<=200)
            break;
            --$tf;
        } while ($tf >=3);';
     // Imprimir en el comprobante el número de teléfono de la institución en caso de tenerlo
     if(trim($institucion['telefono'])!='')
     {
        $comprobante .= '
        $pdf->text (10, 10, $cadena, $fuente, $tf);
        $cadena = "'.iconv("ISO-8859-1", "UTF-8", 'Teléfono(s)'). ': '.$institucion['telefono'].'";';
     }

      $comprobante .= '
        $pdf->text (10, 20, $cadena, $fuente, $tf);
        $cadena = "Documento No. : '.$registro["radi_nume_text"].'";
        $pdf->text (10, 40, $cadena, $fuente, 10);
        $cadena = "Fecha                 : '.$fecha.'";
        $pdf->text (10, 50, $cadena, $fuente, 10);
        $cadena = "Recibido por      : '.iconv("ISO-8859-1", "UTF-8", $usr["nombre"]).'";
        $pdf->text (10, 60, $cadena, $fuente, 10);
        $cadena = "Para verificar el estado de su documento ingrese a";
        $pdf->text (10, 70, $cadena, $fuente, 10);
        $cadena = "'.$nombre_servidor.'";
        $pdf->text (20, 80, $cadena, $fuente, 10);';
        $comprobante .= '$cadena = "con el usuario: \"'.$usr_login.'\""; ';
        $comprobante .= '$pdf->text (20, 90, $cadena, $fuente, 10);
        $pdf->close_object();
        $pdf->add_object($obj_id, "add");
        </script>';
}
$fin = '
 &nbsp;
</body>
</html>
';

$doc_pdf = $inicio.$codigo_barras.$comprobante.$fin;

require_once("$ruta_raiz/js/dompdf/dompdf_config.inc.php");
$dompdf = new DOMPDF();
$dompdf->load_html($doc_pdf);
$dompdf->set_paper($tamano_papel, $orientacion_papel);
$dompdf->set_base_path(getcwd());
$dompdf->render();
$dompdf->stream("comprobante.pdf");
?>
