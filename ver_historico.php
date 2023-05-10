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

$ruta_raiz = ".";
include_once "$ruta_raiz/js/ajax.js";
if ($nivel_seguridad_documento == 0) die ("");
if (isset ($replicacion) && $replicacion && $config_db_replica_info_ver_historico!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_info_ver_historico);

?>
<style type="text/css">
    a:link, a:visited, a:hover {color: blue;}
</style>

<script language="javascript" type="text/javascript">
    function popup_ver_documentoFisico(usua_ori,radicado,Depedencia,hist_referencia,FechaReg,comentario,usuaDest,estadoD,usua_enviadoPor,hist_codi) {
       url = '<?=$ruta_raiz?>/reportes/reporte_TraspasoDocFisico.php?verrad=' + radicado + '&area='+ Depedencia + '&responsable=' + hist_referencia + ' &FechaReg='+ FechaReg +'&comentario='+ comentario +'&tipoenvio=88 &usuaDest= '+ usuaDest + '&estado=' + estadoD + '&hist_codi='+hist_codi+'&usua_enviaPor='+ usua_enviadoPor +'';
       window.open (url);
    }

    function ver_historico_imprimir_hoja_ruta() {
        imprimir_comentarios = 0;
        if (document.getElementById('chk_imprimir_comentarios').checked) imprimir_comentarios = 1;
        document.getElementById('ifr_descargar_archivo').src='./reportes/generar_reporte_recorrido.php?verrad=<?=$verrad?>&imprimir_comentarios='+imprimir_comentarios;
    }

    function ver_detalle_tarea(tarea_codi) {
        var url = '<?=$ruta_raiz?>/tareas/tareas_mostrar_tarea.php';
        var parametros = 'txt_tarea_codi='+tarea_codi;
        fjs_popup_activar ('Detalle de Tarea', url, parametros);
        return;
    }
    function mostrar_historico_tarea(tarea_codi) {
        return;
    }

    function ver_cambios_texto(txt_old, txt_new){
        fjs_popup_activar ('Modificaciones en el Texto', '<?=$ruta_raiz?>/ver_comparacion_texto.php', 'texto_anterior='+txt_old+'&texto_actual='+txt_new);
        return;
    }
</script>

<body>  
  <table width="100%" align="center" border="0" cellpadding="0" cellspacing="3" class="borde_tab">
    <tr align="left">
        <td width=20% class="titulos2">Usuario Actual del Documento:</td>
        <td width=30% class="listado1"><?=$usr_actual["nombre"]?></td>
        <td width=20% class="titulos2"><?=$descDependencia?> actual:</td>
        <td width=30% class="listado1"><?=$usr_actual["dependencia"]?></td>
    </tr>
<?
    if (ObtenerCampoRadicado("radi_leido",$verrad,$db) == 0)
        echo '<tr><td class="listado2" colspan="4"><b>El documento a&uacute;n no ha sido revisado por el destinatario.</b></td></tr>';
?>
    <tr>
        <td colspan="4">
            <table  width="100%" align="center" border="0" cellpadding="0" cellspacing="3" class="borde_tab" >
                <tr><td colspan="7" class="listado1"><b>Acciones realizadas en el Documento.</b></td></tr>
                <tr align="center">
                    <th><?=$descDependencia?></th>
                    <th>Fecha Hora</th>
                    <th>Acci&oacute;n</th>
                    <th>De</th>
                    <th>Para</th>
                    <th>No. d&iacute;as</th>
                    <?/*if ($nivel_seguridad_documento>=2) echo "<th>Comentario</th>"; */?>
                    <th>Comentario</th>
                </tr>
  <?
    $where = "";
    // Ocultar recorrido de los documentos
    if ($datosrad["ocultar_recorrido"]=="1" and ($datosrad["estado"]=="0" or $datosrad["estado"]=="3" or $datosrad["estado"]=="6")) {
        $where = " and ((h.sgd_ttr_codigo=2 and h.hist_referencia is not null) or h.sgd_ttr_codigo in (65,13))";
    }
    // Registros que se muestran solo para usuarios del archivo físico
    if ($_SESSION["usua_admin_archivo"] == 0 and $_SESSION["usua_perm_archivo"] == 0) {
        $where = " and h.sgd_ttr_codigo not in (57)";
    }
//    $estado = ObtenerCampoRadicado("radi_leido",$verrad,$db);
//    if (ObtenerCampoRadicado("radi_leido",$verrad,$db) == 0)
    $sqlFecha = "substr(h.hist_fech::text,1,19)"; //$db->conn->SQLDate("Y-m-d H:i A","h.hist_fech");
    $isql = "select -- Ver Historico
                $sqlFecha || '$descZonaHoraria' as hist_fech1
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
            from (select * from hist_eventos h where h.radi_nume_radi=$verrad $where) as h
                left outer join sgd_ttr_transaccion t on t.sgd_ttr_codigo=h.sgd_ttr_codigo
            order by hist_codi desc ";
    //echo $isql."<hr>";

    $rs = $db->query($isql);
    if(!$rs or $rs->EOF) die ("</table>");
    $i = 0;
    while(!$rs->EOF)
    {
        $observacion = ver_historico_obtener_observacion();
        if ($cod_tx != $rs->fields["SGD_TTR_CODIGO"] or ($old_observacion != $observacion and $observacion!="") or
                $usua_ori != $rs->fields["USUA_ORI"] or $usua_dest != $rs->fields["USUA_DEST"]) {
            $cod_tx = $rs->fields["SGD_TTR_CODIGO"];
            $usua_ori = $rs->fields["USUA_ORI"];
            $usua_dest = $rs->fields["USUA_DEST"];
            $old_observacion = $observacion;
           
            // Si el documento fue tomado de la bandeja compartida
//            $autoReasignado = substr($rs->fields["HIST_OBSE"],0,20);
//            $condicionAR = 'Documento tomado por';
?>
            <tr class="listado<?=($i%2 + 1)?>">
                <td><?=$rs->fields["DEPE_NOMB"]?></td>
                <td><?=$rs->fields["HIST_FECH1"]?></td>
                <td><?=$rs->fields["SGD_TTR_DESCRIP"]?></td>
                <td><?echo $usua_ori; //if ($autoReasignado == $condicionAR) echo $usua_dest; else echo $usua_ori;?></td>
                <td><?if ($usua_ori != $usua_dest) echo $usua_dest?></td>
                <td><?=$rs->fields["TOT_DIAS"]?></td>
                <?/* if ($nivel_seguridad_documento >= 2) echo "<td>$observacion</td>"; */?>
                <td><?=$observacion?></td>
            </tr>
<?
            ++$i;
        }
        $rs->MoveNext();
    }
?>
            </table>
        </td>
    </tr>
</table>

<center>
    <br>
    <input type=button class="botones_largo" name="btn_print" onclick="ver_historico_imprimir_hoja_ruta()" value="Imprimir">
    <br><input type="checkbox" value="1" id="chk_imprimir_comentarios" name="chk_imprimir_comentarios">&iquest;Desea imprimir los comentarios en el reporte?
    <br>&nbsp;
</center>
</body>
</html>

<?

function ver_historico_obtener_observacion() {
    global $nivel_seguridad_documento;
    global $rs;
    global $db;
    global $verrad;

    //if ($nivel_seguridad_documento < 2) return "";

    $usua_ori = $rs->fields["USUA_ORI"];
    $usua_dest = $rs->fields["USUA_DEST"];
    $observacion = str_replace(array("&lt;br>", "&lt;br/>"), "<br>", $rs->fields["HIST_OBSE"]);
    $hist_referencia = $rs->fields["HIST_REFERENCIA"];
     $hist_codi = $rs->fields["HIST_CODI"];
    switch ($rs->fields["SGD_TTR_CODIGO"]) {
        case 69: // Envio Fisico del Documento
            $estadoD=substr($observacion, strrpos($observacion,':')+1);
            $sustituye = array("\r\n", "\n\r", "\n", "\r");
            $comentarioSinSaltosDeLinea = str_replace($sustituye, "", $observacion);//strip_tags($observacion);//=$observacion;//substr($observacion, 0, strpos($observacion, '/')-1);
            $comentario = strip_tags($comentarioSinSaltosDeLinea);
            $Depedencia=$rs->fields["DEPE_NOMB"];
            $FechaReg=$rs->fields["HIST_FECH1"];
            $usua_enviadoPor=$rs->fields["USUA_CODI_ORI"];
            if ($nivel_seguridad_documento > 2)
                $observacion .= "<br><a href=\"javascript:;\" onclick=\"popup_ver_documentoFisico('$usua_ori','$verrad','$Depedencia','$hist_referencia','$FechaReg','$comentario','$usua_dest','$estadoD','$usua_enviadoPor','$hist_codi');\">Ver traspaso de documento físico</a>";
            break;

        case 2: // Registrar documento
            if (trim($hist_referencia)!="") {
                $nume_text = ObtenerCampoRadicado("radi_nume_text",$hist_referencia,$db);
                $observacion = "Se registr&oacute; documento No. $nume_text<br/>".
                               "<a href=\"javascript:;\" onclick=\"popup_ver_documento('$hist_referencia');\">Ver Documento</a>";
                if (ObtenerCampoRadicado("radi_leido",$hist_referencia,$db) == 0)
                    $observacion .= "<br><b>El documento a&uacute;n no ha sido revisado por el destinatario.</b>";
            }
            break;

        case 9: // Reasignar
            if (substr($observacion,0,20) == 'Documento tomado por')
                $observacion = "<font COLOR=#000080>$observacion</font>";
            else
                $observacion .= "<br>Fecha m&aacute;xima de tr&aacute;mite: <b>$hist_referencia</b>";
            break;

        case 12: // Responder
            if (trim($hist_referencia)!="") {
                $nume_text = ObtenerCampoRadicado("radi_nume_text",$hist_referencia,$db);
                $observacion = "Se gener&oacute; documento de respuesta No. $nume_text<br/>".
                               "<a href=\"javascript:;\" onclick=\"popup_ver_documento('$hist_referencia');\">Ver Documento</a>";
            }
            break;

        case 11: // Modificar documento (comparar textos)          
            if (trim($hist_referencia)!="" and $nivel_seguridad_documento>2) {
                $tmp = split(",", $hist_referencia);
                if ($tmp[0] != $tmp[1])
                    $observacion .= "<a href=\"javascript:;\" onclick=\"ver_cambios_texto($hist_referencia);\">Ver Modificaciones en el Texto</a>";
            }
            break;

        case 26: // Asociar Documentos
            if (trim($hist_referencia)!="") {
                $nume_text = ObtenerCampoRadicado("radi_nume_text",$hist_referencia,$db);
                $observacion = "Se asoci&oacute; como $observacion el documento No. ".
                               "&quot;<a href=\"javascript:;\" onclick=\"popup_ver_documento('$hist_referencia');\">$nume_text</a>&quot;";
            }
            break;

	case 27: // Asociar Documentos
            if (trim($hist_referencia)!="") {
                $nume_text = ObtenerCampoRadicado("radi_nume_text",$hist_referencia,$db);
                $observacion = "Se elimin&oacute; la asociaci&oacute;n con el documento $observacion No. ".
                               "&quot;<a href=\"javascript:;\" onclick=\"popup_ver_documento('$hist_referencia');\">$nume_text</a>&quot;";
            }
            break;

        case 32: // Carpetas virtuales
            $observacion = "Incluir documento en $descTRD";
            if (trim($hist_referencia)!="") {
                $observacion .= ": ".ObtenerNombreCompletoTRD($hist_referencia,$db);
            }
            break;

	case 35: // Revertir Firma Digital
            $nume_text = ObtenerCampoRadicado("radi_nume_text",$verrad,$db);
            if (trim($hist_referencia)!="" and $nivel_seguridad_documento>2) {
                $observacion .= "<br>&quot;<a href=\"javascript:;\" onclick=\"vista_previa('$hist_referencia','$nume_text.pdf.p7m');\">Ver Documento anterior.</a>&quot;";
            }
            break;
	case 36: // Volver a generar PDF
            $nume_text = ObtenerCampoRadicado("radi_nume_text",$verrad,$db);
            if (trim($hist_referencia)!="" and $nivel_seguridad_documento>2) {
                $observacion .= "<br>&quot;<a href=\"javascript:;\" onclick=\"vista_previa('$hist_referencia','$nume_text.pdf');\">Ver Documento anterior.</a>&quot;";
            }
            break;

        case 50: // Tareas
            if (trim($hist_referencia)!="") {
                $observacion .= "<br><a href=\"javascript:;\" onclick=\"ver_detalle_tarea('$hist_referencia');\">Ver detalle</a>";
            }
            break;

       case 82: // Copiar
            if (trim($hist_referencia)!="") {
                $nume_text = ObtenerCampoRadicado("radi_nume_text",$hist_referencia,$db);
                $observacion = "Se gener&oacute; documento de copia No. $nume_text<br/>".
                               "<a href=\"javascript:;\" onclick=\"popup_ver_documento('$hist_referencia');\">Ver Documento</a>";
            }
            break;

        default:
            break;
    }
    return $observacion;
}
?>