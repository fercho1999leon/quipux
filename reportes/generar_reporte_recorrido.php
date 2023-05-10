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
if (isset ($replicacion) && $replicacion && $config_db_replica_info_ver_historico!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_info_ver_historico);

$imprimir_observacion = 0+$_GET['imprimir_comentarios'];
$verrad = limpiar_numero($_GET["verrad"]);

// Obtener plantilla del area del usuario actual
include_once "$ruta_raiz/obtenerdatos.php";
$area = ObtenerDatosDependencia($_SESSION["depe_codi"],$db);
$datosrad = ObtenerDatosRadicado($verrad,$db);
$usua_rem  = ObtenerListaUsuariosDocumento($db, $verrad, "R");
$usua_dest = ObtenerListaUsuariosDocumento($db, $verrad, "D");

$sql = "select -- Ver Historico
            substr(h.hist_fech::text,1,19) as hist_fech1
            , ver_usuarios(usua_codi_ori::text,',') as usua_ori
            , (select depe_nomb from usuario where usua_codi=usua_codi_ori) as depe_nomb
            , ver_usuarios(usua_codi_dest::text,',') as usua_dest
            , t.sgd_ttr_codigo
            , t.sgd_ttr_descrip
            , h.hist_obse
            , h.hist_referencia
            , h.hist_codi
            ,(h.hist_fech::date - '".$datosrad["fecha_radicado"]."'::date) as TOT_DIAS
            ,h.usua_codi_ori
        from (select * from hist_eventos h where h.radi_nume_radi=$verrad and h.sgd_ttr_codigo not in (57)) as h
            left outer join sgd_ttr_transaccion t on t.sgd_ttr_codigo=h.sgd_ttr_codigo
        order by hist_codi desc ";

$rs = $db->query($sql);

$html = "<html>
<head>
    <title>.: QUIPUX - HOJA DE RUTA :.</title>
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
</head>
<body>
    <table width='100%' border='0'>
        <tr><th align='center'><font size='5'>Hoja de Ruta</font></th></tr>
    </table>
    <table border='0' align='left'>
        <tr>
            <td align='left'><font size=2><b>Fecha y hora generaci&oacute;n:</b></font></td>
            <td align='left' ><font size=2>".date("Y-m-d H:i:s")."$descZonaHoraria</font></td>
        </tr>
        <tr>
            <td align='left'><font size=2><b>Generado por:</b></font></td>
            <td align='left' ><font size=2>".$_SESSION["usua_nomb"]."</font></td>
        </tr>
    </table>
    <table width='100%' align='center' cellspacing='1' cellpadding='4' border='1'><tr><td>
        <table width='100%' align='center' cellspacing='1' cellpadding='2' border='1'>
            <tr>
                <td colspan='4' align='left'><font size=2><b>Informaci&oacute;n del Documento</b></font>
            </td>
            <tr>
                <td align='left'><font size=2><b>No. Documento:</b></font></td>
                <td align='left'><font size=2>".$datosrad["radi_nume_text"]."</font></td>
                <td align='left'><font size=2><b>Doc. Referencia:</b></font></td>
                <td align='left'><font size=2>".rellenar_dato($datosrad["radi_referencia"])."</font></td>
            </tr>
            <tr>
                <td align='left'><font size=2><b>De:</b></font></td>
                <td align='left'><font size=2>".formatear_usuarios($usua_rem)."</font></td>
                <td align='left'><font size=2><b>Para:</b></font></td>
                <td align='left'><font size=2>".formatear_usuarios($usua_dest)."</font></td>
            </tr>
            <tr>
                <td align='left'><font size=2><b>Asunto:</b></font></td>
                <td align='left'><font size=2>".rellenar_dato($datosrad["radi_asunto"])."</font></td>
                <td align='left'><font size=2><b>Descripci&oacute;n Anexos:</b></font></td>
                <td align='left'><font size=2>".rellenar_dato($datosrad["radi_desc_anexos"])."</font></td>
            </tr>
            <tr>
                <td align='left'><font size=2><b>Fecha Documento:</b></font></td>
                <td align='left'><font size=2>".substr($datosrad["radi_fecha"],0,10)."$descZonaHoraria</font></td>
                <td align='left'><font size=2><b>Fecha Registro:</b></font></td>
                <td align='left'><font size=2>".substr($datosrad["fecha_radicado"],0,10)."$descZonaHoraria</font></td>
            </tr>
        </table>
    </td></tr></table>
    <table width='100%' align='center' cellspacing='2' cellpadding='2' border='1'>
        <tr><td colspan='".(($imprimir_observacion) ? "7" : "6")."' align='left'><font size=2><b>Ruta del documento</b></font></td>
        <tr>
            <td align='center'><font size=2><b>&Aacute;rea</b></font></td>
            <td align='center'><font size=2><b>De</b></font></td>
            <td align='center'><font size=2><b>Fecha/Hora</b></font></td>
            <td align='center'><font size=2><b>Acci&oacute;n</b></font></td>
            <td align='center'><font size=2><b>Para</b></font></td>
            <td align='center'><font size=2><b>No. D&iacute;as</b></font></td>".
            (($imprimir_observacion) ? "<td align='center'><font size=2><b>Comentario</b></font></td>" : "").
        "</tr>";

$flag_repetido = false;
while($rs && !$rs->EOF) {

    if($rs->fields["SGD_TTR_CODIGO"]!=11 or !$flag_repetido){
        $flag_repetido = ($rs->fields["SGD_TTR_CODIGO"]==11) ? true : false;

        $html .= "<tr>
                    <td><font size=1>".$rs->fields["DEPE_NOMB"]."</font></td>
                    <td><font size=1>".$rs->fields["USUA_ORI"]."</font></td>
                    <td><font size=1>".$rs->fields["HIST_FECH1"]."$descZonaHoraria</font></td>
                    <td><font size=1>".$rs->fields["SGD_TTR_DESCRIP"]."</font></td>";
        $html .= (($rs->fields["USUA_ORI"] == $rs->fields["USUA_DEST"])) ? "<td>&nbsp;</td>" : "<td><font size=1>".$rs->fields["USUA_DEST"]."</font></td>";
        $html .= "<td><font size=1>".$rs->fields["TOT_DIAS"]."</font></td>";
        $html .= ($imprimir_observacion) ? "<td><font size=1>".$rs->fields["HIST_OBSE"]."</font></td>" : "";
        $html .= "</tr>";
    }
    $rs->MoveNext();
}

$html .= "</table></body></html>";




//GENERACION DEL PDF
include "$ruta_raiz/config.php";
require_once("$ruta_raiz/interconexion/generar_pdf.php");
$plantilla = "$ruta_raiz/bodega/plantillas/".$area["plantilla"].".pdf";
$pdf = ws_generar_pdf($html, $plantilla, $servidor_pdf, "", "", "", "75", "R");
$nomArch="Hoja_de_ruta_".str_replace(" ", "_", $datosrad["radi_nume_text"]).".pdf";
header( "Content-Disposition: attachment; filename=$nomArch");
header("Content-Type:application/pdf");//.application/pdf
header("Content-Transfer-Encoding: binary");
echo  $pdf;


function rellenar_dato ($dato) {
    if (trim($dato) == "") $dato = "--";
    return trim($dato);
}

function formatear_usuarios ($usuarios) {
    $cadena = "";
    foreach ($usuarios as $usr ) {
        $cadena .= $usr["usua_abr_titulo"]." ".$usr["usua_nomb"]." ".$usr["usua_apellido"].", ".$usr["usua_cargo"].", ".$usr["usua_institucion"]."<br>";
    }
    return $cadena;
}

?>