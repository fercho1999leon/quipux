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
//////////////   FUNCIONES DE TAREAS   ////////////////

function botones_tarea($accion, $tarea_codi, $verrad, $ruta_raiz=".") {
    include "$ruta_raiz/config.php";
    $imagen = "";
    switch ($accion) {
        case "nueva":
            $imagen .= "<img src='$nombre_servidor/iconos/page_new.gif' alt='Nueva tarea' border='0' title='Asignar nueva tarea'";
            $codTx = 30;
            break;
        case "finalizar":
            $imagen .= "<img src='$nombre_servidor/iconos/page_tick.gif' alt='Finalizar tarea' border='0' title='Finalizar la tarea asignada'";
            $codTx = 31;
            break;
        case "cancelar":
            $imagen .= "<img src='$nombre_servidor/iconos/page_delete.gif' alt='Cancelar tarea' border='0' title='Cancelar la tarea asignada'";
            $codTx = 32;
            break;
        case "comentar":
            $imagen .= "<img src='$nombre_servidor/iconos/comment.gif' alt='Comentar tarea' border='0' title='Comentar la tarea'";
            $codTx = 33;
            break;
        case "reabrir":
            $imagen .= "<img src='$nombre_servidor/iconos/page_refresh.gif' alt='Reabrir tarea' border='0' title='Reabrir tarea cancelada o finalizada'";
            $codTx = 34;
            break;
        case "editar":
            $imagen .= "<img src='$nombre_servidor/iconos/page_white_edit.png' alt='Editar tarea' border='0' title='Editar tarea'";
            $codTx = 35;
            break;
        case "avance":
            $imagen .= "<img src='$nombre_servidor/iconos/page_settings.gif' alt='Registrar Avance' border='0' title='Registrar avances en la tarea'";
            $codTx = 36;
            break;
        default :
            return "";
            break;
    }
    $imagen .= "onmouseover='this.style.cssText=\"cursor: pointer\"' onmouseout='this.style.cssText=\"cursor: default\"'
                onClick='tx_realizar_accion(\"$codTx\",\"$verrad\",\"tarea_codi=$tarea_codi\")'>";
    return "&nbsp;".$imagen."&nbsp;";
}

function dibujar_semaforo($color, $avance=0, $ruta_raiz='.') {
//    $color_r = "#FBEFEF";
//    $color_a = "#F5F6CE";
//    $color_v = "#E3F6CE";
//    if ($color=="rojo")     $color_r = "#FF0000";
//    if ($color=="amarillo") $color_a = "#FFFF00";
//    if ($color=="verde")    $color_v = "#01DF01";
//    $imagen = "<div style='border: #333333 1px solid; -moz-border-radius:10px; -webkit-border-radius:10px; width: 56px; height: 19px; float:left;'>
//                 <div style='border: #333333 1px solid; -moz-border-radius:10px; -webkit-border-radius:10px; width: 13px; height: 13px; position: relative; top: 2px; left: 3px; background-color: $color_v; float:left;'></div>
//                 <div style='border: #333333 1px solid; -moz-border-radius:10px; -webkit-border-radius:10px; width: 13px; height: 13px; position: relative; top: 2px; left: 6px; background-color: $color_a; float:left;'></div>
//                 <div style='border: #333333 1px solid; -moz-border-radius:10px; -webkit-border-radius:10px; width: 13px; height: 13px; position: relative; top: 2px; left: 9px; background-color: $color_r; float:left;'></div>
//               </div>";
    include "$ruta_raiz/config.php";
    switch ($color) {
        case "rojo":
            $color = "#FF0000";
            $titulo = "Vencido";
            $semaforo = "$nombre_servidor/iconos/semaforo_rojo.gif";
            break;
        case "amarillo":
            $color = "#FFFF00";
            $titulo = "P&oacute;ximo a vencer";
            $semaforo = "$nombre_servidor/iconos/semaforo_amarillo.gif";
            break;
        case "verde":
            $color = "#01DF01";
            $titulo = "A tiempo";
            $semaforo = "$nombre_servidor/iconos/semaforo_verde.gif";
            break;
        case "gris":
            $color = "#F2F2F2";
            $titulo = "Finalizado";
            $semaforo = "$nombre_servidor/iconos/semaforo_gris.gif";
            break;
        default:
            return "";
            break;
    }

    //$imagen = "<div style='border: #848484 2px solid; -moz-border-radius:12px; -webkit-border-radius:10px; width: 16px; height: 16px; position: relative; top: 1px; left: 3px; background-color: $color; float:left;' title='$titulo'></div>";
    $imagen = "<img src='$semaforo' alt='Editar tarea' border='0' title='$titulo' height='25' width='25'>";

    $barra = "<div align='left' style='color: #FFFFFF; height: 14px; width: 104px; border: thin solid #999999; float:left; position: relative; top: 2px; left: 10px;' title='Avance: $avance%'>
                  <div id='div_barra' style='background-color: #a8bac6; color: #FFFFFF; border: thin solid #a8bac6;
                      height: 10px; width: ".$avance."px; position:relative; top:1px; left:1px;'></div>
              </div>";

    return "$imagen&nbsp;&nbsp;$barra";
}

function dibujar_tareas($db, $tipo_tarea, $verrad, $ruta_raiz=".", $tipo_impresion="") {
    include "$ruta_raiz/include/local/localEcuador.php";
    $estilo_tabla = "style='border: thin solid #377584;'";
    $estilo_font = "color='white' style='font-weight:bold;'";
    $tabla = "";
    $estilo = 1;

    $botones_e1 = array();
    $botones_e2 = array();
    $botones_e3 = array();
    if ($tipo_tarea == 1) { // Asignadas por mi
        $titulo = "Tareas Asignadas a otros usuarios por ".$_SESSION["usua_nomb"];
        $titulo2 = "a";
        $sql = "select t.tarea_codi, t.estado, coalesce(tp.estado,1) as estado_padre
            from (select tarea_codi, tarea_codi_padre, estado from tarea where radi_nume_radi=$verrad and usua_codi_ori=".$_SESSION["usua_codi"].") as t
                left outer join tarea tp on t.tarea_codi_padre=tp.tarea_codi
            order by t.tarea_codi asc";
        //echo $sql;
        $rst = $db->conn->Execute($sql);
        //$botones_e1 = array("cancelar","editar","comentar");
        $botones_e1 = array("comentar","editar","cancelar");
        if ($rst->fields["ESTADO_PADRE"] == 1) {
            $botones_e2 = array("reabrir");
            $botones_e3 = array("reabrir");
        }
    } else { // Asignadas a mi
        $titulo = "Tareas Asignadas a ".$_SESSION["usua_nomb"];
        $titulo2 = "por";
        $sql = "select tarea_codi, estado from tarea where radi_nume_radi=$verrad and usua_codi_dest=".$_SESSION["usua_codi"]." order by tarea_codi asc";
        $rst = $db->conn->Execute($sql);
        //$botones_e1 = array("nueva","finalizar","avance","comentar");
        $botones_e1 = array("comentar","avance","finalizar","nueva");
    }

    if ($rst && !$rst->EOF) {
        $tabla .= "<table width='100%' border='0' cellpadding='0' cellspacing='3' $estilo_tabla>
                <tr bgcolor='#6a819d'>
                    <td height='20'><font $estilo_font>$titulo</font></td>
                </tr>";
        while (!$rst->EOF) {
            $tabla .= "<tr><td>".dibujar_detalle_tarea($db, $rst->fields["TAREA_CODI"], ${"botones_e".$rst->fields["ESTADO"]}, $ruta_raiz, $tipo_impresion)."</td></tr>";
            $rst->MoveNext();
        }
        $tabla .= "</table>";
    }
    return $tabla;
}


function dibujar_detalle_tarea($db, $tarea_codi, $lista_botones, $ruta_raiz=".", $tipo_impresion="") {
    include "$ruta_raiz/include/local/localEcuador.php";
    $estilo_tabla = "style='border: thin solid #377584;'";
    $estilo_font = "color='white' style='font-weight:bold;'";

    $sql = "select t.*, (t.fecha_maxima::date - now()::date) as \"num_dias\", h.comentario, u.usua_nomb, u.usua_apellido, coalesce(tp.estado,1) as estado_padre
        from (select * from tarea where tarea_codi=$tarea_codi) as t
            left outer join tarea_hist_eventos h on t.comentario_inicio=h.tarea_hist_codi
            left outer join usuarios u on t.usua_codi_dest=u.usua_codi
            left outer join tarea tp on t.tarea_codi_padre=tp.tarea_codi
        order by t.tarea_codi asc";
    //echo $sql;
    $rst = $db->conn->Execute($sql);

    if ($rst && !$rst->EOF) {
        $mostrar_historico = ""; //none
        $color = "gris"; // "blue";
        if ($rst->fields["ESTADO"] == 1) {
            $color = "verde";//"green";
            if ($rst->fields["NUM_DIAS"] == 0) {
                $color = "amarillo";//"yellow";
            } else if ($rst->fields["NUM_DIAS"] < 0) {
                $color = "rojo";// "red";
            }
            //if ($tipo_tarea == 0)
                $mostrar_historico = "";
        }

        $botones = "";
        if (is_array($lista_botones)) {
            foreach ($lista_botones as $boton) {
                $botones .= botones_tarea($boton, $rst->fields["TAREA_CODI"], $rst->fields["RADI_NUME_RADI"], $ruta_raiz);
            }
        }
        if(trim($tipo_impresion)!="") $botones = "";

        //cabecera de la tarea
        $tabla .= "
            <table width='100%' border='0' cellpadding='0' cellspacing='0'>
                <tr class='listado2' >
                    <td width='60%' onclick='mostrar_historico_tarea(\"".$rst->fields["TAREA_CODI"]."\")'>
                        <table width='100%' border='0' cellpadding='0' cellspacing='0' >
                            <tr><td width='30%'><b>Tarea:</b></td><td width='70%'>".$rst->fields["COMENTARIO"]."</td></tr>
                            <tr><td width='30%'><b>Asignado $titulo2:</b></td><td>".$rst->fields["USUA_NOMB"]." ".$rst->fields["USUA_APELLIDO"]."</td></tr>
                            <tr><td width='30%'><b>Fecha m&aacute;xima de tarea: </b></td><td>".substr($rst->fields["FECHA_MAXIMA"],0,10).$descZonaHoraria."</td></tr>
                        </table>
                    </td>
                    <td width='20%' valign='middle' align='center' valign='middle' onclick='mostrar_historico_tarea(\"".$rst->fields["TAREA_CODI"]."\")'>";

        if(trim($tipo_impresion)=="PDF")
            $tabla .= "<b>Avance:</b> ".$rst->fields["AVANCE"]."% ";
        $tabla .= dibujar_semaforo($color, $rst->fields["AVANCE"],$ruta_raiz)."</td>";
        
        $tabla .= "<td width='20%' align='right'>$botones</td>";
        $tabla .="</tr>";

        // Historico de la tarea
        $tabla .= "<tr id='tr_descripcion_".$rst->fields["TAREA_CODI"]."' style='display: $mostrar_historico'>
                <td align='center' colspan='3'>
                  <table width='95%' border='0' cellpadding='0' cellspacing='3' $estilo_tabla>
                    <tr bgcolor='#6a819d' align='left'>
                      <td width='15%'><font $estilo_font>Fecha Hora</font></td>
                      <td width='15%'><font $estilo_font>Acci&oacute;n</font></td>
                      <td width='30%'><font $estilo_font>Usuario</font></td>
                      <td width='40%'><font $estilo_font>Comentario</font></td>
                    </tr>";

        $sql = "select h.accion, h.referencia, h.fecha, h.comentario, a.sgd_ttr_descrip, u.usua_nombre
                from (select * from tarea_hist_eventos where tarea_codi=".$rst->fields["TAREA_CODI"].") as h
                    left outer join usuario u on u.usua_codi=h.usua_codi_ori
                    left outer join sgd_ttr_transaccion a on h.accion=a.sgd_ttr_codigo
                order by h.tarea_hist_codi asc";
        $rsh = $db->conn->Execute($sql);
        while (!$rsh->EOF) {
            $observacion = ver_historico_observacion_tarea($db,$rsh->fields['ACCION'],$rsh->fields['REFERENCIA']);
            $tabla .= "<tr>
                    <td>".substr($rsh->fields["FECHA"],0,19).$descZonaHoraria."</td>
                    <td>".$rsh->fields["SGD_TTR_DESCRIP"]."</td>
                    <td>".$rsh->fields["USUA_NOMBRE"]."</td>";
            if(trim($observacion)!="")
                $tabla .= "<td>$observacion</td>";
            else
                 $tabla .= "<td>".$rsh->fields["COMENTARIO"]."</td>";
            $tabla .= "</tr>";
            $rsh->MoveNext();
        }
        $tabla .= "</table>
                </td>
              </tr><tr><td>&nbsp;</td></tr>";
    }
    $tabla .= "</table>";

    return $tabla;
}


function ver_historico_observacion_tarea($db,$accion,$docRefecencia) {
    switch ($accion) {
        case 58: // Responder
            if (trim($docRefecencia)!="") {
                $nume_text = ObtenerCampoRadicado("radi_nume_text",$docRefecencia,$db);
                $observacion = "Se gener&oacute; documento de respuesta No. $nume_text<br>";
                $observacion .= "<a href=\"javascript:;\" onclick=\"popup_ver_documento('$docRefecencia');\"><font color='blue'>Ver Documento</font></a>";
                if (ObtenerCampoRadicado("radi_leido",$docRefecencia,$db) == 0)
                    $observacion .= "<br><b>El documento a&uacute;n no ha sido revisado por el destinatario.</b>";
            }
            break;
        default:
            break;
    }
    return $observacion;
}
?>