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
$db = new ConnectionHandler("$ruta_raiz","reportes");

include_once "$ruta_raiz/obtenerdatos.php";
$area = ObtenerDatosDependencia($_SESSION["depe_codi"],$db);

//documento traspaso fisico.
if (isset($_GET['verrad'])){
    $radi_nume = $_GET['verrad'];    
    $hist_codi = $_GET['hist_codi'];
    if ($radi_nume=='')
        $radi_nume=0;
    $datosRad = array();
    $datosRefe = array();
    $datosRemitente = array();
    $datosRegistraPor = array();
    $datosDestinatario = array();
    $datosRad = ObtenerDatosRadicado($radi_nume, $db);
    //informacion del documento
    //-------------------------
    $noDocumento        = $datosRad['radi_nume_text'];//numero documento    
    $noReferencia       = $datosRad['radi_nume_asoc'];//radi de referencia
    //obtener referencia    
    $noReferenciaTxt = $datosRad['radi_referencia'];//numero de referencia
    if ($noReferenciaTxt==''){
        $datosRefe = ObtenerDatosRadicado($noReferencia, $db);
        $noReferenciaTxt = $datosRefe['radi_nume_text'];//numero de referencia
    }
      if ($noReferenciaTxt=='')
        $noReferenciaTxt = '------------------';
    //remitente
    $cod_remitente      = str_replace('-','',$datosRad['usua_rem']);//codigo remitente
    $datosRemitente     = ObtenerDatosUsuario($cod_remitente,$db);
    $remitente          = $datosRemitente['nombre'];    
    $asunto             = $datosRad['radi_asunto'];//asunto
    //registrado por
    $cod_registradoPor  = $datosRad['usua_radi'];//registrado por
    //echo $cod_registradoPor;
    $datosRegistradoPor   = ObtenerDatosUsuario($cod_registradoPor,$db);
    $registradoPor      = $datosRegistradoPor['nombre'];
    $fecha_creacion     = substr($datosRad['fecha_radicado'],0,16);//fecha de creacion
    //obtener fecha envio 19 envio manual del documento
    $radi_nume_padre        = $datosRad['radi_nume_temp'];//numero documento   
    
    $sql = "select hist_fech as fecha_envio from hist_eventos where radi_nume_radi = $radi_nume_padre and sgd_ttr_codigo = 19";    
    
    $rs = $db->query($sql);
    $fecha_envio = $rs->fields['FECHA_ENVIO'];
    if ($fecha_envio==''){
        //envio electronico
        $sql = "select hist_fech as fecha_envio from hist_eventos where radi_nume_radi = $radi_nume_padre and sgd_ttr_codigo = 18";    
        $rs = $db->query($sql);
        $fecha_envio = $rs->fields['FECHA_ENVIO'];
        if ($fecha_envio==''){
            $sql = "select hist_fech as fecha_envio from hist_eventos where radi_nume_radi = $radi_nume_padre and sgd_ttr_codigo = 65";    
        $rs = $db->query($sql);
        $fecha_envio = $rs->fields['FECHA_ENVIO'];
        }
    }
    $fecha_envio        = substr($fecha_envio,0,16);//fecha de envio
    //creacion del pdf
    $cabecerapdf = '<html>
                <head>
                 <title>.: QUIPUX - VISTA PREVIA :.</title>
                 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                </head>
                <body style="margin: 5 20 30 5;">';
    $tituloDocumento = 
    '<br>
	<table align="center">
		<b><tr><th align="center"><font size="6">Traspaso de documentos físicos</th></tr></b>
	</table>
    <br />';
    $subtitulo=
    '<table width="100%" >
        <tr>
            <td>Información del documento</td>
        </tr>
    </table>';
    $tablaInformacion=
    '<table border="1" width="100%" >
        <tr>
            <td width="25%">No.documento:</td>
            <td width="30%">'.$noDocumento.'</td>
            <td width="20%">Referencia:</td>
            <td width="25%">'.$noReferenciaTxt.'</td>
        </tr>
        <tr>
            <td width="25%">Remitente:
            </td>
            <td colspan="3">'.$remitente.'
            </td>
        </tr>
        <tr>
            <td width="25%">Asunto:
            </td>
            <td colspan="3">'.$asunto.'
            </td>
        </tr>
        <tr>
            <td width="25%">Registrado por:
            </td>
            <td colspan="3">'.$registradoPor.'
            </td>
        </tr>
        <tr>
            <td width="25%">Fecha de Creación:
            </td>
            <td colspan="3">'.$fecha_creacion.' '.$descZonaHoraria.'
            </td>
        </tr>
        <tr>
            <td width="25%">Fecha de Envío:
            </td>
            <td colspan="3">'.$fecha_envio.' '.$descZonaHoraria.'
            </td>
        </tr>
        </table>';
    $informacion = $subtitulo.$tablaInformacion;
    
    
    //obtiene codigo del que recibe
    //echo $radi_nume."<br>";
    //Select para desplegar desde historico
    if (isset($_GET['hist_codi'])){
    $sql="select usua_codi_dest as recibidopor
          ,usua_codi_ori as enviadopor, hist_obse as comentario,hist_fech
        from hist_eventos where hist_codi = $hist_codi    
        and sgd_ttr_codigo = 69 ";
    }else{
        //cuando es nuevo
        $sqlmax="select max(hist_codi) as hist_codi 
        from hist_eventos where radi_nume_radi=$radi_nume and sgd_ttr_codigo = 69";
        //echo "Max: ".$sqlmax;
        $rsmax = $db->query($sqlmax);
        $hist_codi = $rsmax->fields['HIST_CODI'];
        //echo "<br>MAxnum: ".$hist_codi;
        $sql="select usua_codi_dest as recibidopor
          ,usua_codi_ori as enviadopor, hist_obse as comentario,hist_fech
        from hist_eventos where hist_codi = $hist_codi
        and sgd_ttr_codigo = 69";
        //echo $sql;
    }
    //echo "Final: ".$sql."<br>";
    $rs = $db->query($sql);
    $codRecibidoPor     = $rs->fields['RECIBIDOPOR'];
    $fecha_entrega      = substr($rs->fields['HIST_FECH'],0,16);    
    $datosRecibidoPor   = array();
    $datosRecibidoPor   = ObtenerDatosUsuario($codRecibidoPor,$db);
    $recibidoPor        = $datosRecibidoPor['nombre'];    
    $nombreAreaA        = $datosRecibidoPor["dependencia"];
    $codEnviadoPor      = $rs->fields['ENVIADOPOR'];
    //echo $codEnviadoPor;
    $datosEnviadoPor    = array();
    $datosEnviadoPor    = ObtenerDatosUsuario($codEnviadoPor,$db);
    $enviadoPor         = $datosEnviadoPor['nombre'];    
    $nombreAreaE        = $datosEnviadoPor["dependencia"];
    $observacion        = $rs->fields['COMENTARIO'];
    //tabla informacion de traspaso
    $datosEnvio         = array();
    unset($datosEnvio);
    $datosEnvio = ObtenerDatosEnvioFisico($hist_codi,$db);
    $responsableTraslado = $datosEnvio['responsable'];
    $responsableTraslado=formatear_datos_documento($responsableTraslado);
    $estado = $datosEnvio['estado'];
        
     if(strlen($observacion) > 70) {  // comprobamos que el texto tiene mas de 70 caracteres
        $pos = strpos($observacion, "/");        
        $observacion = substr($observacion,0,$pos);
        $observacionJustificado = formatear_datos_documento($observacion);
        $observacionJustificado = wordwrap($observacion,70,"<br />\n",true);        
        }
    else{
        $pos = strpos($observacion, "/");
        $observacion = substr($observacion,0,$pos);
        $observacionJustificado = formatear_datos_documento($observacion);
        $observacionJustificado=$observacionJustificado;
    }
    if ($observacionJustificado=='')
        $observacionJustificado='';
    
    if ($estado == 'B')
        $estadoDesc = 'Bueno';
    elseif ($estado=='M')
        $estadoDesc = 'Malo';
    elseif ($estado=='R')
        $estadoDesc = 'Regular';
   
    if ($estado=='')
        if (isset($_GET['estado']))
       $estadoDesc=$_GET['estado'];
     
    $subtituloInf=
    '<table width="100%" >
        <tr>
            <td>Información del traspaso</td>
        </tr>
    </table>';
    
    
   
    $tablaTraspaso=
    '<table border="1" width="100%" >
        <tr>
            <td width="25%">Área (Enviado a):</td>
            <td width="30%">'.$nombreAreaA.'</td>
            <td width="20%">Recibido por:</td>
            <td width="25%">'.$recibidoPor.'</td>
        </tr>
        <tr>
            <td width="25%">Fecha entrega:</td>
            <td colspan="3">'.$fecha_entrega.' '.$descZonaHoraria.'</td>
        </tr>        
        <tr>
            <td width="25%">Área (Enviado por):</td>
            <td width="30%">'.$nombreAreaE.'</td>
            <td width="20%">Enviado por:</td>
            <td width="25%">'.$enviadoPor.'</td>
        </tr>
        <tr>
            <td width="25%">Responsable Traslado:</td>
            <td colspan="3">'.$responsableTraslado.'</td>
        </tr>
        <tr>
            <td width="25%">Comentario:</td>
            <td colspan="3">'.$observacionJustificado.'</td>
        </tr>
        <tr>
            <td width="25%">Estados:</td>
            <td colspan="3">'.$estadoDesc.'</td>
        </tr></table>';
        $tablaTraspaso.='<table width="100%"><tr><td colspan="3">&nbsp;</td></tr></table>';
     //firmas
     $firmas ='<table width="100%"><tr>
    <td align="center" >Recibido por</td>
    <td align="center">Enviado por</td>
    <td align="center">Responsable traslado</td>
    </tr>
    <tr></tr><tr></tr>
    <tr>
    <td align="center">------------------------------------</td>
    <td align="center">------------------------------------</td>
    <td align="center">------------------------------------</td>
    </tr>
    <tr>
    <td align="center" >'.$recibidoPor.'</td>
    <td align="center">'.$enviadoPor.'</td>
    <td align="center">'.$responsableTraslado.'</td>
    </tr>
    <tr>
    <td align="center" >'.$nombreAreaA.'</td>
    <td align="center">'.$nombreAreaE.'</td>
    </tr>
     </table>';
     $traspaso = $subtituloInf.$tablaTraspaso;
     
     $doc_pdf = $cabecerapdf.$tituloDocumento.$informacion.$traspaso.$firmas.$piepdf;
     
     //echo $doc_pdf;
     enviarPdf($ruta_raiz,$area["plantilla"],$doc_pdf,$servidor_pdf);
}

function enviarPdf($ruta_raiz,$area,$doc_pdf,$servidor_pdf){
//GENERACION DEL PDF
//
require_once("$ruta_raiz/interconexion/generar_pdf.php");
$plantilla = "$ruta_raiz/bodega/plantillas/".$area.".pdf";

$pdf = ws_generar_pdf($doc_pdf, $plantilla, $servidor_pdf,"","","",90,"R");
$nomArch="Reporte_TraspasoDocumentosF.pdf";
header( "Content-Disposition: attachment; filename=$nomArch");
header("Content-Type:application/pdf");//.application/pdf
header("Content-Transfer-Encoding: binary");
echo  $pdf;
}

function formatear_datos_documento($texto, $case="",$tipo=2) {
        $mayusculas = array ("Á", "É", "Í", "Ó", "Ú", "À", "È", "Ì", "Ò", "Ù", "Ä", "Ë", "Ï", "Ö", "Ü", "Â", "Ê", "Î", "Ô", "Û", "Ã", "Õ", "Ñ", "Ç");
        $minusculas = array ("á", "é", "í", "ó", "ú", "à", "è", "ì", "ò", "ù", "ä", "ë", "ï", "ö", "ü", "â", "ê", "î", "ô", "û", "ã", "õ", "ñ", "ç");
        if ($case == "U") { // Pasar a mayúsculas
            $texto = str_replace($minusculas, $mayusculas, strtoupper($texto));
        } elseif ($case == "L") { //Minúsculas
            $texto = str_replace($mayusculas, $minusculas, strtolower($texto));
        } elseif ($case == "I") { //Intercalar
            // Por versiones anteriores en las que todo el texto podía estar todo en mayúsculas o minúsculas
            if ($texto == strtoupper($texto) or $texto == strtolower($texto)) {
                $texto = str_replace($mayusculas, $minusculas, strtolower($texto));
                $cadena = "";
                foreach (explode(" ", $texto) as $palabra) {
                    if (strlen($palabra)<=3)
                        $cadena .= " ".$palabra;
                    else {
                        if (ltrim($palabra,"áéíóúàèìòùäëïöüâêîôûãõñ") == $palabra)
                            $cadena .= " ".strtoupper(substr($palabra,0,1)).substr($palabra,1);
                        else // en caso que comience por un caracter especial
                            $cadena .= " ".strtoupper(str_replace($minusculas, $mayusculas, substr($palabra,0,2))).substr($palabra,2);
                    }
                }
                $origen  = array("Ff.Aa.");
                $destino = array("FF.AA.");
                $texto = str_ireplace($origen, $destino, $cadena);
            }
        }
      
        // Cambiamos letras con tildes a formato html
        $origen  = array ("á", "é", "í", "ó", "ú", "à", "è", "ì", "ò", "ù", "ä", "ë", "ï", "ö", "ü"
                        , "â", "ê", "î", "ô", "û", "ã", "õ", "ñ", "ç"
                        , "Á", "É", "Í", "Ó", "Ú", "À", "È", "Ì", "Ò", "Ù", "Ä", "Ë", "Ï", "Ö", "Ü"
                        , "Â", "Ê", "Î", "Ô", "Û", "Ã", "Õ", "Ñ", "Ç","&");
        $destino = array ("&aacute;", "&eacute;", "&iacute;", "&oacute;", "&uacute;"
                        , "&agrave;", "&egrave;", "&igrave;", "&ograve;", "&ugrave;"
                        , "&auml;", "&euml;", "&iuml;", "&ouml;", "&uuml;"
                        , "&acirc;", "&ecirc;", "&icirc;", "&ocirc;", "&ucirc;"
                        , "&atilde;", "&otilde;", "&ntilde;", "&ccedil;"
                        , "&Aacute;", "&Eacute;", "&Iacute;", "&Oacute;", "&Uacute;"
                        , "&Agrave;", "&Egrave;", "&Igrave;", "&Ograve;", "&Ugrave;"
                        , "&Auml;", "&Euml;", "&Iuml;", "&Ouml;", "&Uuml;"
                        , "&Acirc;", "&Ecirc;", "&Icirc;", "&Ocirc;", "&Ucirc;"
                        , "&Atilde;", "&Otilde;", "&Ntilde;", "&Ccedil;","&");
        $texto = str_ireplace($destino, $origen, $texto);
       
        if ($tipo==2){
                
        $ree_text=array('prime;','&prime');
        $texto = str_replace($ree_text, '', $texto);        
        
        $car_especial = array('prime;','&prime','/','(',')','=','{','}','amp;','*','+','-','¬','|');
        $texto = str_replace($car_especial, "", $texto);
        }
        return trim($texto);
    }
?>

