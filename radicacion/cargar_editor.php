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
require "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/obtenerdatos.php";

$codi_texto = 0 + limpiar_numero($_POST["codi_texto"]);
$tipo_docu = 0 + limpiar_numero($_POST["tipo_docu"]);

if($codi_texto>0) {
    $sql = "select text_texto from radi_texto where text_codi=$codi_texto";
    $rs = $db->conn->Execute($sql);
    $raditexto = $rs->fields["TEXT_TEXTO"];
} else {
    $sql = "select trad_texto_inicio from tiporad where trad_codigo=$tipo_docu";
    $rs = $db->conn->Execute($sql);
    $raditexto = $rs->fields["TRAD_TEXTO_INICIO"];
}

if ($accion == "Responder" and $tipo_docu == "1") {
    //Para añadir texto de En respuesta al Documento No.
    if (isset($_POST['referencia'])) {
        $referencia = limpiar_sql($_POST["referencia"]);
        $sqlRef = "select radi_cuentai from radicado where radi_nume_text='$referencia'";
        $rsRef = $db->conn->Execute($sqlRef);
        $referenciaExterno = $rsRef->fields["RADI_CUENTAI"];
        if (trim($referenciaExterno)!='')
            $referenciadoc = "En respuesta al Documento No. $referenciaExterno";
        else
            $referenciadoc = "En respuesta al Documento No. ".$_POST['referencia'];
    }

    $raditexto = "De mi consideraci&oacute;n:<br><br>$referenciadoc<br><br>$raditexto<br><br>Con sentimientos de distinguida consideraci&oacute;n.<br>&nbsp;<br>&nbsp;";
}

if ($_POST["esphone"]==1){
    $txtmobil = array("<br />","<br>");
    $raditexto=str_replace($txtmobil, '\n', $raditexto);
}

if (in_array($tipo_docu, array(5,6,8,9))) {
    $usr = ObtenerDatosUsuario($_SESSION["usua_codi"], $db);
    $raditexto = str_replace("**QUIPUX_DATOS_DOC_NOMBRE_INSTITUCION**", $_SESSION["inst_nombre"], $raditexto);
    $raditexto = str_replace("**QUIPUX_DATOS_DOC_REMITENTE_CIUDAD**", $usr["ciudad"], $raditexto);
    $raditexto = str_replace("**QUIPIX_DATOS_DOC_FECHA_LARGA**", fechaAtexto(date('Y-m-d')), $raditexto);
}

echo base64_encode($raditexto);

?>