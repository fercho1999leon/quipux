<?php
/**  Programa para el manejo de gestion documental, oficios, memorandos, circulares, acuerdos
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

/*  FUNCION PARA CREAR EL CALENDARIO */

function dibujar_calendario($objeto, $fecha, $ruta_raiz=".", $accion = "") {
    $anio_desde = "2008";
    $anio_hasta = date('Y')+1;

    // Genero el combo de los años
    $combo_anios="<select id='calphp_combo_anio_$objeto' class='calphp_combos' onchange='calphp_generar_calendario(\"$objeto\")'>";
    $anio = substr($fecha, 0, 4);
    for ($i=$anio_desde ; $i<=$anio_hasta ; ++$i) {
        $combo_anios .= "<option value='$i'";
        if ((0+$anio) == $i) $combo_anios .= " selected";
        $combo_anios .= ">$i</option>";
    }
    $combo_anios .= "</select>";

    // Genero el combo de los meses
    $combo_meses = "<select id='calphp_combo_mes_$objeto' class='calphp_combos' onchange='calphp_generar_calendario(\"$objeto\")'>";
    $meses = array ('','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic');
    $mes = substr($fecha, 5, 2);
    for ($i=1 ; $i<=12 ; ++$i) {
    $combo_meses .= "<option value='$i'";
    if ((0+$mes) == $i) $combo_meses .= " selected";
        $combo_meses .= ">".$meses[$i]."</option>";
    }
    $combo_meses .= "</select>";

    $calendario =
        "<span style='border: none; height: 22px; vertical-align: middle; position: absolute;'>
            <input type='text' name='$objeto' id='$objeto' value='$fecha' size='10' maxlength='10' class='calphp_fecha' readonly>
            <span id='calphp_accion_$objeto' style='display: none;'>$accion</span>
            <img src='$ruta_raiz/js/calendario_php/btn_date1_up.gif'   id='img_calphp_mostrar_$objeto' alt='Mostrar' title='Mostrar calendario' style='vertical-align: middle' onclick='calphp_mostrar_calendario(\"$objeto\")'>
            <img src='$ruta_raiz/js/calendario_php/btn_date1_down.gif' id='img_calphp_ocultar_$objeto' alt='Ocultar' title='Ocultar calendario' style='display: none;vertical-align: bottom' onclick='calphp_ocultar_calendario(\"$objeto\")'>
            <div id='div_calphp_calendario_$objeto' class='calphp_div_calendario' style='display: none;'>
                <table width='100%' border='0' cellpadding='0' cellspacing='0'>
                    <tr>
                        <td width='100%' align='center' height='22px' valign='middle'>$combo_meses&nbsp;$combo_anios</td>
                    </tr>
                    <tr>
                        <td><div id='div_calphp_calendario_dias_$objeto'></div></td>
                    </tr>
                </table>
            </div>
        </span>";
    return $calendario;
}
?>
