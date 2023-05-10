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
//////////////   LISTA DE ANEXOS   ////////////////
if (!$ruta_raiz) $ruta_raiz="..";
if (str_replace("/","",str_replace(".","",$ruta_raiz))!="") die ("");
include_once "$ruta_raiz/js/ajax.js";
include_once "$ruta_raiz/obtenerdatos.php";
?>

<script type="text/javascript">
    var flag_accion_realizada = false;
    var id_confirmacion = 0;
    var titulo = '';
    var codTx = 0;

    function tx_realizar_accion(accion, radi_nume, parametros) {
        codTx = accion;
        tx_bloquear_pantalla(0);
        document.getElementById('div_tx_validar_radicado').innerHTML = '';
        nuevoAjax('div_tx_validar_radicado', 'POST', './tx/tx_seguridad_documentos.php', 'txt_radicados='+radi_nume+'&codTx='+codTx);
        document.getElementById('txt_tx_parametros').value = parametros;

        switch (codTx) {
            case '30':
                titulo = "Asignar Nueva Tarea";
                objetos = 'combo_areas,combo_usuarios,comentario,calendario';
                tx_mostrar_objetos_transaccion (objetos);
                break;
            case '31':
                titulo = "Finalizar Tarea";
                objetos = 'comentario,reasignar_documento';
                tx_mostrar_objetos_transaccion (objetos);
                break;
            case '32':
                titulo = "Cancelar Tarea";
                objetos = 'comentario';
                tx_mostrar_objetos_transaccion (objetos);
                break;
            case '33':
                titulo = "Comentar Tarea";
                objetos = 'comentario';
                tx_mostrar_objetos_transaccion (objetos);
                break;
            case '34':
                titulo = "Reabrir Tarea Finalizada o Cancelada";
                objetos = 'comentario,calendario';
                tx_mostrar_objetos_transaccion (objetos);
                break;
            case '35':
                titulo = "Editar Tarea";
                objetos = 'comentario,calendario';
                tx_mostrar_objetos_transaccion (objetos);
                break;
            case '36':
                titulo = "Reportar Avance de Tarea";
                objetos = 'comentario,avance,reasignar_documento';
                tx_mostrar_objetos_transaccion (objetos);
                break;
            default:
                titulo = "";
                break;
        }
        document.getElementById('span_tx_titulo').innerHTML = 'Acci&oacute;n: ' + titulo;
    }

    function tx_realizar_accion_confirmar() {
        radi_nume = document.getElementById('txt_tx_radi_nume').value;
        parametros = document.getElementById('txt_tx_parametros').value;
        comentario = document.getElementById('txt_tx_comentario').value;
        fecha_tarea = document.getElementById('tx_fecha_tramite').value;
        reasignar_respuesta = 0;
        try {
            if (document.getElementById('txt_tx_reasignar_respuesta_tarea').checked) reasignar_respuesta = 1;
        } catch (e) {}
        switch (codTx) {
            case '30': // Asignar_tareas
                if (!tx_validar_datos('radicados,combo_usuarios,comentario,calendario')) return false;
                usuario = tx_obtener_datos_combo ('txt_tx_usua_codi');

                parametros_tx = 'txt_radicados='+radi_nume+'&codTx='+codTx+'&txt_comentario='+comentario+
                                '&txt_usua_codi='+usuario+'&txt_fecha_tarea='+fecha_tarea+'&'+parametros;
                break;
            case '31': // Finalizar Tareas
            case '32': // Cancelar Tareas
            case '33': // Comentar Tareas
                if (!tx_validar_datos('radicados,comentario')) return false;
                parametros_tx = 'txt_radicados='+radi_nume+'&codTx='+codTx+'&txt_comentario='+comentario+'&txt_reasignar_respuesta='+reasignar_respuesta+'&'+parametros;
                break;
            case '34': // Reabrir Tareas
            case '35': // Editar Tareas
                if (!tx_validar_datos('radicados,comentario,calendario')) return false;
                parametros_tx = 'txt_radicados='+radi_nume+'&codTx='+codTx+'&txt_comentario='+comentario+'&txt_fecha_tarea='+fecha_tarea+'&'+parametros;
                break;
            case '36': // Avance Tareas
                if (!tx_validar_datos('radicados,comentario')) return false;
                avance = tx_obtener_datos_combo ('txt_tx_avance_tarea');
                parametros_tx = 'txt_radicados='+radi_nume+'&codTx='+codTx+'&txt_comentario='+comentario+'&txt_avance_tarea='+avance+'&txt_reasignar_respuesta='+reasignar_respuesta+'&'+parametros;
                break;

        }
        tx_div_respuesta = tx_anadir_respuesta();
        nuevoAjax(tx_div_respuesta, 'POST', './tx/tx_realizar_tx.php', parametros_tx);
        document.getElementById('div_tx_btn_aceptar').style.display = 'none';
        flag_accion_realizada = true; // Para que se recargue la página
        
    }


    function tx_mostrar_objetos_transaccion (objetos) {

        objeto = objetos.split(',');
        for (i=0 ; i<objeto.length ; ++i) {
            switch (objeto[i]) {
                case 'avance':
                    document.getElementById('tr_tx_avance_tarea').style.display = '';
                    break;
                case 'calendario':
                    document.getElementById('tr_tx_fecha_tramite').style.display = '';
                    break;
                case 'combo_areas':
                    document.getElementById('tr_tx_combos').style.display = '';
                    document.getElementById('td_tx_combo_areas').style.display = '';
                    nuevoAjax('div_tx_combo_areas', 'POST', './tx/tx_cargar_combos.php', 'txt_tipo_combo=area&codTx='+codTx);
                    break;
                case 'combo_usuarios':
                    try {
                        area = tx_obtener_datos_combo ('txt_tx_depe_codi');
                    } catch (e) {
                        area = '<?=$_SESSION["depe_codi"]?>';
                    }
                    document.getElementById('tr_tx_combos').style.display = '';
                    document.getElementById('td_tx_combo_usuarios').style.display = '';
                    nuevoAjax('div_tx_combo_usuarios', 'POST', './tx/tx_cargar_combos.php', 'txt_tipo_combo=usuarios&codTx='+codTx+'&area='+area);
                    break;
                case 'comentario':
                    document.getElementById('tr_tx_comentario').style.display = '';
                    break;
                case 'reasignar_documento':
                    document.getElementById('tr_tx_reasignar_documento_tarea').style.display = '';
                    parametros = document.getElementById('txt_tx_parametros').value;
                    nuevoAjax('div_tx_reasignar_documento_tarea', 'POST', './tx/tx_cargar_combos.php', 'txt_tipo_combo=reasignar_respuesta&'+parametros);
                    break;

            }
        }
        tx_esperar_ajax('div_tx_validar_radicado');
    }

    function tx_esperar_ajax(nombre_div) {
        try {
            if (document.getElementById(nombre_div).innerHTML == '') {
                timerID = setTimeout("tx_esperar_ajax('"+nombre_div+"')", 500);
            } else {
                switch (nombre_div) {
                    case "div_tx_validar_radicado":
                        if (document.getElementById('txt_tx_radi_nume').value != '0') {
                            document.getElementById('div_tx_btn_aceptar').style.display = '';
                            //fecha maxima de tarea
                            document.getElementById('tx_fecha_tramite').value = document.getElementById('txt_tx_fecha_maxima_tarea').value;
                        }
                        break;
                    case "div_tx_realizar_accion":
                        if (document.getElementById(nombre_div).innerHTML == 'OK') {
                            flag_accion_realizada = true;
                            tx_bloquear_pantalla(1);
                        }
                        break;
                }
            }
        } catch (e) {
            timerID = setTimeout("tx_esperar_ajax('"+nombre_div+"')", 500);
        }
    }

    function tx_bloquear_pantalla(accion) {
        if (accion == 0) {
            document.getElementById('div_tx_bloquear_pantalla').style.display = '';
            document.getElementById('div_tx_pantalla_pequena').style.display = '';
        } else {
            if (flag_accion_realizada) {
                window.location.reload();
            } else {
                document.getElementById('div_tx_bloquear_pantalla').style.display = 'none';
                document.getElementById('div_tx_pantalla_pequena').style.display = 'none';
                calphp_ocultar_calendario('tx_fecha_tramite');
                document.getElementById('div_tx_btn_aceptar').style.display = 'none';
                document.getElementById('tr_tx_combos').style.display = 'none';
                document.getElementById('td_tx_combo_areas').style.display = 'none';
                document.getElementById('td_tx_combo_usuarios').style.display = 'none';
                document.getElementById('td_tx_combo_listas').style.display = 'none';
                document.getElementById('tr_tx_fecha_tramite').style.display = 'none';
                document.getElementById('tr_tx_avance_tarea').style.display = 'none';
                document.getElementById('tr_tx_reasignar_documento_tarea').style.display = 'none';
                document.getElementById('div_tx_respuesta').innerHTML = '';
                document.getElementById('div_tx_reasignar_documento_tarea').innerHTML = '';
            }
        }
    }

    function tx_obtener_datos_combo (nombre_combo, tipo_combo) {
        tipo_combo = tipo_combo || 'value';
        var i = 0;
        combo = document.getElementById(nombre_combo);
        coma = '';
        respuesta = '';

        for(i=0 ; i<combo.options.length;i++) {
            if (combo.options[i].selected) {
                respuesta += coma + eval('combo.options['+i+'].'+tipo_combo);
                coma = ',';
            }
        }
        return respuesta;
    }

    function tx_validar_datos(objetos) {
        var i=0;
        objeto = objetos.split(',');
        for (i=0 ; i<objeto.length ; ++i) {
            switch (objeto[i]) {
                case "calendario":
                    var fechaActual = new Date(<?=date("Y")?>,<?=date("n")?>,<?=date("d")?>);
                    fecha_doc = document.getElementById('tx_fecha_tramite').value;
                    var fecha = new Date(fecha_doc.substring(0,4),fecha_doc.substring(5,7), fecha_doc.substring(8,10));
                    var tiempoRestante = fecha.getTime() - fechaActual.getTime();
                    var dias = Math.floor(tiempoRestante / (1000 * 60 * 60 * 24));
                    if (dias < 0) {
                        document.getElementById('hidden_bandera_cerrar').value=0;
                        alert ("La fecha máxima de trámite debe ser mayor a la fecha actual");
                        return false;
                    }else
                        document.getElementById('hidden_bandera_cerrar').value=1;
                    break;
                case "combo_usuarios":
                    if (tx_obtener_datos_combo ('txt_tx_usua_codi')=='0') {
                        document.getElementById('hidden_bandera_cerrar').value=0;
                        alert ('Por favor seleccione un usuario.');
                        return false;
                    }else
                    document.getElementById('hidden_bandera_cerrar').value=1;
                    break;
                case "comentario":
                    if (document.getElementById('txt_tx_comentario').value.replace(/^\s*|\s*$/g,"") == '') {
                        document.getElementById('hidden_bandera_cerrar').value=0;
                        alert ("Por favor ingrese un comentario para la tarea.");
                        return false;
                    }else
                       document.getElementById('hidden_bandera_cerrar').value=1;

                    break;
                case "radicados":
                    if (document.getElementById('txt_tx_radi_nume').value == '0') {
                        document.getElementById('hidden_bandera_cerrar').value=0;
                        alert ('No existen documentos seleccionados');
                        return false;
                    }else
                       document.getElementById('hidden_bandera_cerrar').value=1;
                    break;
            }
        }
        return true;
    }

    var tx_respuesta_id = 0;
    function tx_anadir_respuesta(){
        texto_div = document.getElementById('div_tx_respuesta').innerHTML;
        if (texto_div == '') {
            // Ponemos el encabezado de la tabla
            texto_div = '<br><table width="100%" class="borde_tab">'+
                '<tr><th width="30%">Acci&oacute;n Realizada</th>'+
                '<th width="30%">Comentario</th><th width="40%">Estado</th></tr></table>';
        }
        //Añadimos un nuevo registro a la tabla con un div llamado "div_tx_respuesta_#" en donde se cargara el resultado de la accion
        texto = '<tr><td>'+titulo+'</td>'+
            '<td>'+document.getElementById('txt_tx_comentario').value+'</td>'+
            '<td><div id="div_tx_respuesta_'+(++tx_respuesta_id).toString()+'">Esperando</div></td></tr>';

        texto_div = texto_div.replace('</table>',texto+'</table>');
        document.getElementById('div_tx_respuesta').innerHTML = texto_div;
        return 'div_tx_respuesta_'+tx_respuesta_id.toString();
    }

    function tx_validar_avance_tarea(avance) {
        try {
            if (avance == "100") {
                document.getElementById('txt_tx_reasignar_respuesta_tarea').checked = true;
                document.getElementById('tr_tx_reasignar_documento_tarea').style.display = '';
            } else {
                document.getElementById('txt_tx_reasignar_respuesta_tarea').checked = false;
                document.getElementById('tr_tx_reasignar_documento_tarea').style.display = 'none';
            }
        } catch (e) {}

    }

    //Validar si la fecha maxima de tarea seleccionada por el usuario es mayor a la fecha maxima que puede tener
    //fechaMaximaFinal
    function validar_fecha_maxima(fechaMaximaFinal){
        var fechaMaximaTarea = fechaMaximaFinal;
        //var fechaMaximaTarea = document.getElementById('txt_tx_fecha_maxima_tarea').value;
        var fechaSeleccionada = document.getElementById('tx_fecha_tramite').value;
        var fechavalida = document.getElementById('txt_valida_fecha').value;
        if (fechavalida==1)
        if(<?=(0+$carpeta)?> == 15 || <?=(0+$carpeta)?> == 16) //Validamos fechas solo si la tarea va a ser creada desde la bandeja de "Tareas Recibidas"
            if(validarFechas(fechaSeleccionada, fechaMaximaTarea)==2)
            //if (validarFechas(fechaSeleccionada,fechaMaximaFinal)==2)
            {
                alert('La fecha máxima de tarea no puede ser mayor a: ' + fechaMaximaTarea);
                document.getElementById('tx_fecha_tramite').value = fechaMaximaTarea;
                document.getElementById('hidden_bandera_cerrar').value=0;
            }
            else
                document.getElementById('hidden_bandera_cerrar').value=1;
    }
    function cerrar_tx_pantalla(){        
        if (document.getElementById('hidden_bandera_cerrar').value==1)
            timerID = setTimeout("tx_bloquear_pantalla(1)", 4000);        
    }
</script>

<input type="hidden" name="txt_tx_parametros" id="txt_tx_parametros" value="">
<div id="div_tx_bloquear_pantalla" style="width: 100%; height: 100%; z-index: 1000; position: fixed; top: 0; left: 0; opacity:0.3; filter:alpha(opacity=30); background-color: black; display: none;"></div>
<div id="div_tx_pantalla_pequena" style="width: 70%; height: 80%; z-index: 1001; position: fixed; top: 5%; left: 15%; background-color: white; border: #333333 2px solid; display: none">
    <div id="div_tx_titulo" style="font-weight: bold; text-align: center; font-size: small; color: #FFFFFF; background-color:#006394; width: 100%; height: 20px; position: relative;">
        <table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td width="98%" height="18px" style="font-weight: bold; text-align: center; font-size: small; color: #FFFFFF; vertical-align: middle"><span id="span_tx_titulo"></span></td><td align="left" valign="middle" width="20px"><img src="./imagenes/close_button.gif" onclick="tx_bloquear_pantalla(1);"></td></tr></table>
    </div>
    <input type="hidden" name="hidden_bandera_cerrar" id="hidden_bandera_cerrar" value="1"/>
    <div id="div_tx_pantalla_tabajo" style="background-color:white; width: 100%; height: 90%; position: relative;">
        <table width="100%" border="0" cellpadding="0" cellspacing="0">
            <tr id="tr_tx_validar">
                <td width="100%">
                    <div id="div_tx_validar_radicado"></div>
                </td>
            </tr>
            <tr id="tr_tx_combos" style="display: none">
                <td width="100%">
                    <br>
                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td id="td_tx_combo_areas" width="34%" style="display: none" align="left">
                                <div id="div_tx_combo_areas"></div>
                            </td>
                            
                        </tr>
                        <tr>
                            <td id="td_tx_combo_usuarios" width="34%" style="display: none" align="left">
                                <div id="div_tx_combo_usuarios"></div>
                            </td>
                            
                        </tr>
                        <tr>
                            <td id="td_tx_combo_listas" width="34%" style="display: none" align="left">
                                <div id="div_tx_combo_listas"></div>
                            </td>
                        </tr>
                    </table>
                    
                </td>
            </tr>
            <tr id="tr_tx_fecha_tramite" style="display: none">
                <td width="100%" align="center" class="listado1">
                    <br><?php
                        if (isset($verrad)){
                            $sql="select min(tarea_codi) as tarea_codi,tarea_codi_padre from tarea where usua_codi_ori = ".$_SESSION["usua_codi"]." 
                            and radi_nume_radi = $verrad
                            group by tarea_codi, tarea_codi_padre";
                            //echo $sql;
                            $rs=$db->conn->query($sql);
                            $codi_padre = $rs->fields["TAREA_CODI_PADRE"];
                            $min_tarea_codi = $rs->fields["TAREA_CODI"];
                            
                            if ($codi_padre=='' and $min_tarea_codi!=''){
                                echo '<input type="hidden" name="txt_valida_fecha" id="txt_valida_fecha" value="0">';
                            }
                            else
                                echo '<input type="hidden" name="txt_valida_fecha" id="txt_valida_fecha" value="1">';
                            $fechaMaximaFinal = "'".substr(obtenerFechaTareaRadicado($db, $verrad, $_SESSION["usua_codi"]),0,10)."'";
                        }else{
                            $fechaMaximaFinal = "'".date('Y-m-d')."'";
                            
                        }//                      
                        ?>
                    <b>Fecha M&aacute;xima de Tarea (aaaa-mm-dd): 
                    </b>
                    <?  $fechavalida = str_replace("'", "", $fechaMaximaFinal);
//                   
                    echo dibujar_calendario("tx_fecha_tramite", date('Y-m-d'), ".", "validar_fecha_maxima($fechaMaximaFinal);") ?>
                </td>
            </tr>
            <tr id="tr_tx_avance_tarea" style="display: none">
                <td width="100%" align="center" class="listado1">
                    <br>
                    <b>Avance de la tarea: </b>
                    <select name="txt_tx_avance_tarea" id ="txt_tx_avance_tarea" size="1" class="select" onchange="tx_validar_avance_tarea(this.value)">
                        <? for ($i=0; $i<=100; $i+=10) echo "<option value=\"$i\" selected>$i %</option>"; ?>
                    </select>
                </td>
            </tr>
            <tr id="tr_tx_reasignar_documento_tarea" style="display: none">
                <td width="100%" align="center" class="listado1">
                    <div id="div_tx_reasignar_documento_tarea"></div>
                </td>
            </tr>
            <tr id="tr_tx_comentario">
                <td width="100%">
                    <br>
                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="34%" valign="middle" align="right" class="listado1"><b>Comentario:&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
                            <td width='40%' align='center' valign='middle'>
                                <textarea name="txt_tx_comentario" id="txt_tx_comentario" cols="70" rows="3" class="ecajasfecha"></textarea>
                            </td>
                            <td width='30%' valign='middle'><br/>
                                <span><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr id="tr_tx_botones">
                <td width="100%" align="center">
                    <br>
                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            
                            <td id="div_tx_btn_aceptar" align="center" style="display: none;">
                        <center><input type='button' class='botones' name="btn_tx_aceptar" value='Aceptar' onClick="tx_realizar_accion_confirmar(); cerrar_tx_pantalla();"/>
                                &nbsp;&nbsp;
                                <input type='button' class='botones' name="btn_tx_cancelar" value='Regresar' onClick="tx_bloquear_pantalla(1);"/>
                                </center>
                            </td>
                            
                        </tr>
                    </table>
                </td>
            </tr>
            <tr id="tr_tx_realizar_accion">
                <td width="100%" align="center">
                    <br>
                    <div id="div_tx_respuesta" style="width: 80%;"></div>
                </td>
            </tr>
        </table>
    </div>
</div>
