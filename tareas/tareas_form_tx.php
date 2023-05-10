<?
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
include_once "$ruta_raiz/obtenerdatos.php";
include_once "$ruta_raiz/seguridad_documentos.php"; // Valida estados de los documentos y otras reglas dependiendo de la transacción realizada
require_once "$ruta_raiz/funciones.php" ;
include_once "$ruta_raiz/funciones_interfaz.php";

$mensaje_error = "";
$whereFiltro= "0";


/**
* FILTRO DE DATOS
*/


if(isset($_POST['checkValue'])) {               //Si se escogieron radicados de la lista
    foreach ($_POST['checkValue'] as $radi_nume => $chk) {
        if (trim($radi_nume)!="") {
            $flag = validar_transacciones($codTx, $radi_nume, $db);
            if ($flag == "")
                $whereFiltro .= ",$radi_nume";
            else
                $mensaje_error .= $flag;
        }
    }
    $validar_fecha = $_POST['txt_fech_tarea'];//para validar fecha
} else {        //Si no se escogio ningun radicado
        $mensaje_error .= "No hay documentos seleccionados.";
}

//Fecha maxima que puede tener la tarea
$fechaMaximaTarea = obtenerFechaMaximaTarea($db, $whereFiltro, $_SESSION["usua_codi"]);
$fecha_tarea = $fechaMaximaTarea;
if ($validar_fecha==16){ 
    
    $usua_codi_ori = $_SESSION["usua_codi"];
    $sqlFechaTarea = "select substr(max(fecha_maxima::text),1,10) as fecha_maxima from tarea where radi_nume_radi in ($whereFiltro) and estado=1 and usua_codi_ori=$usua_codi_ori";    
    
    $rsFechaTarea = $db->query($sqlFechaTarea);
    $fechaMaximaTarea = $rsFechaTarea->fields["FECHA_MAXIMA"];
    $fecha_tarea = $rsFechaTarea->fields["FECHA_MAXIMA"]; 
    if (trim($fechaMaximaTarea)==''){//si no tiene fecha        
        $fechaMaximaTarea=date('Y-m-d');
        $fecha_tarea = $fechaMaximaTarea;
    }
    $validar_fecha=1;    
}
echo "<html>".html_head();
require_once "$ruta_raiz/js/ajax.js";

?>

<script type="text/javascript" src="../js/spiffyCal/spiffyCal_v2_1.js"></script>

<script type="text/javascript">

    function markAll(noRad) {
       if( noRad >=1) {
            for(i=3;i<document.formulario.elements.length;i++)
                document.formulario.elements[i].checked=1;
        } else {
            for(i=3;i<document.formulario.elements.length;i++)
                document.formulario.elements[i].checked=0;
        }
    }

    function verificar_chk() {
        for(i=0;i<document.formulario.elements.length;i++) {
            if(document.formulario.elements[i].checked==1 )
                return true;
        }
        return false;
    }

    function verificar_combo(nombre)
    {
        for(i=0;i<document.getElementById(nombre).options.length;i++)
        {
            if(document.getElementById(nombre).options[i].selected && document.getElementById(nombre).options[i].value!='0')
                return true;
        }
        return false;
    }


    function okTx() {
         // Verificamos que existan documentos seleccionados
        if(!verificar_chk()) {
            alert ('No existen documentos seleccionados.');
            return false;
        }

        // Si es reasignar
        if (<?=$codTx?> == 30) {
            // Verificamos que existan usuarios seleccionados
            if(!verificar_combo('usCodSelect')) {
                alert ('Seleccione el usuario al que se le asignará la tarea.');
                return false;
            }

            // Verificamos la fecha de reasignación
            var fechaActual = new Date(<?=date("Y")?>,<?=date("n")?>,<?=date("d")?>);
            fecha_doc = document.getElementById('fecha_tarea').value;
            var fecha = new Date(fecha_doc.substring(0,4),fecha_doc.substring(5,7), fecha_doc.substring(8,10));
            var tiempoRestante = fecha.getTime() - fechaActual.getTime();
            var dias = Math.floor(tiempoRestante / (1000 * 60 * 60 * 24));
            if (dias < 0) {
            alert ("La fecha máxima de tarea debe ser mayor a la fecha actual");
                return false;
            }
        }

        document.realizarTx.observa.value = document.realizarTx.observa.value.substr(0,550);
        document.realizarTx.submit();
    }

    var flag_primera_tarea = true;
    var tarea_ejecutada = 0;
    function aceptar_tarea() {
    // Verificamos que existan documentos seleccionados
        if(!verificar_chk()) {
            alert ('No existen documentos seleccionados.');
            return false;
        }

        // Verificamos que existan usuarios seleccionados
        if(!verificar_combo('usCodSelect')) {
            alert ('Seleccione el usuario al que se le asignará la tarea.');
            return false;
        }

        // Verificamos la fecha de reasignación
        var fechaActual = new Date(<?=date("Y")?>,<?=date("n")?>,<?=date("d")?>);
        fecha_doc = document.getElementById('fecha_tarea').value;
        var fecha = new Date(fecha_doc.substring(0,4),fecha_doc.substring(5,7), fecha_doc.substring(8,10));
        var tiempoRestante = fecha.getTime() - fechaActual.getTime();
        var dias = Math.floor(tiempoRestante / (1000 * 60 * 60 * 24));
        if (dias < 0) {
            alert ("La fecha máxima de tarea debe ser mayor a la fecha actual");
            return false;
        }

        // Comentario
        comentario = document.getElementById('txt_comentario').value;
        if (comentario.replace(/^\s*|\s*$/g,"") == '') {
            alert ("Por favor ingrese un comentario para la tarea.");
            return false;
        }

        // Cargamos la lista de documentos seleccionados
        lista_radicados = '0';
        for(i=0;i<document.formulario.elements.length;i++) {
            if(document.formulario.elements[i].checked==1 )
                lista_radicados += ','+document.formulario.elements[i].name.substr(11,20); //checkValue[20100000110000000181]
        }

        // Nombre del usuario seleccionado
        nombre_usuario = '';
        codigo_usuario = '';
        for(i=0;i<document.getElementById('usCodSelect').options.length;i++) {
            if(document.getElementById('usCodSelect').options[i].selected) {
                nombre_usuario = document.getElementById('usCodSelect').options[i].text;
                codigo_usuario = document.getElementById('usCodSelect').options[i].value;
            }
        }

        if (flag_primera_tarea) {
            document.getElementById('div_tareas_asignadas').innerHTML = '<br><table width="100%" class="borde_tab">'+
                '<tr><th width="20%">Servidor Público</th><th width="20%">Fecha M&aacute;xima de Tr&aacute;mite</th><th width="30%">Comentario</th><th width="30%">Estado</th></tr></table>';
            flag_primera_tarea = false;
        }
        document.getElementById('div_tareas_asignadas').innerHTML = document.getElementById('div_tareas_asignadas').innerHTML.replace('</table>','<tr><td>'+nombre_usuario+'</td><td>'+fecha_doc+'<?=$descZonaHoraria?>'+'</td><td>'+comentario+'</td><td><div id="div_ejecutar_tarea'+(++tarea_ejecutada).toString()+'">Esperando</div></td></tr></table>');

//        document.getElementById('div_ejecutar_tarea').innerHTML = '';
        nuevoAjax('div_ejecutar_tarea'+tarea_ejecutada.toString(), 'POST', '../tareas/tareas_realizar_tx.php',
            'txt_usua_codi='+codigo_usuario+'&txt_radicados='+lista_radicados+'&txt_comentario='+comentario+'&txt_fecha_tarea='+fecha_doc+'&codTx=<?=$codTx?>');
//        tx_esperar_ajax('div_ejecutar_tarea');

        // limpio la tarea anterior
        document.getElementById('usCodSelect').options[0].selected = true;
        document.getElementById('txt_comentario').value = '';

        return false;

    }

    function tx_esperar_ajax(nombre_div) {
        try {
            if (document.getElementById(nombre_div).innerHTML == '') {
                timerID = setTimeout("tx_esperar_ajax('"+nombre_div+"')", 500);
            } else {
                switch (nombre_div) {
                    case "div_ejecutar_tarea":
                        texto = document.getElementById(nombre_div).innerHTML;
                        if (texto == 'OK') {
                            text = 'Tarea Asignada';
                        }
                        document.getElementById("div_tarea_pendiente").innerHTML = tarea;
                        document.getElementById("div_tarea_pendiente").id = 'div_tarea_ok';
                        break;
                }
            }
        } catch (e) {
            timerID = setTimeout("tx_esperar_ajax('"+nombre_div+"')", 500);
        }
    }

    function cambiar_combo_usuarios() {
        var area = '';
        var coma = '';
        for(i=0;i<document.getElementById('depsel').options.length;i++) {
            if (document.getElementById('depsel').options[i].selected) {
                area += coma +document.getElementById('depsel').options[i].value;
                coma = ',';
            }
        }
        if (area != '')
        nuevoAjax('mnu_usr', 'GET', '../tx/formEnvio_ajax.php', 'area='+area+'&codTx=9');
        return;
    }

    //Validar si la fecha maxima de tarea seleccionada por el usuario es mayor a la fecha maxima que puede tener
    function validar_fecha_maxima(){       
        var fechaMaximaTarea = document.getElementById('fecha_maxima_tarea').value;
        var fechaSeleccionada = document.getElementById('fecha_tarea').value;
        var seleccionBandeja = document.getElementById("txt_fech_tarea").value;
        if(document.getElementById('carpeta').value == 15 || seleccionBandeja==1) //Validamos fechas solo si la tarea va a ser creada desde la bandeja de "Tareas Recibidas"        
            if(validarFechas(fechaSeleccionada, fechaMaximaTarea)==2)
            {
                alert('La fecha máxima de tarea no puede ser mayor a: ' + fechaMaximaTarea);
                document.getElementById('fecha_tarea').value = fechaMaximaTarea;
            }
    }
</script>

<body onLoad="markAll(1);">
    <div id="spiffycalendar" class="text"></div>
    <br/>
    <center>
    
<?php
    //Si hay algun error, se muestra mensaje donde se indica que no se puede archivar el(los) radicado(s)
    if ($mensaje_error != "" )
        echo ("<table class='borde_tab' width='100%' celspacing='5'><tr class='titulosError'><td align='center'>$mensaje_error</td></tr></table></center>");
    
    if ($codTx == 30) {  //Buscamos las áreas que se desplegarán en los combos de nueva tarea
        
            if($_SESSION["cargo_tipo"]!=1 && $_SESSION["usua_publico"] !=1){

             if ($_SESSION["perm_saltar_organico_funcional"]==1)
                $where_area = " depe_codi in (".$_SESSION["depe_codi"].",".substr(areasHijasNivelDependencia($db, $_SESSION["depe_codi"]),1).")";
             else   
                $where_area = "depe_codi=".$_SESSION["depe_codi"];

            }
            else {
                
                // Obtenermos el área padre del área actual
                $sql = "select coalesce(depe_codi_padre, depe_codi) as depe_codi from dependencia where depe_codi=".$_SESSION["depe_codi"];
                $rs = $db->conn->Execute($sql);
                $where_area = $rs->fields["DEPE_CODI"];
                $where_hijas = "";
                if ($where_area != $_SESSION["depe_codi"]) {
                    if ($_SESSION["perm_saltar_organico_funcional"]==1)
                    $where_hijas = areasHijasNivelDependencia($db, $_SESSION["depe_codi"]);
                    
                    $where_area .= "," . $_SESSION["depe_codi"].$where_hijas;
                }
                if ($_SESSION["perm_saltar_organico_funcional"]==1) {
                    // Si el usuario tiene permisos para saltar el organico funcional, muestra un nivel mas.
                    $sql = "select depe_codi from dependencia where depe_codi_padre=".$_SESSION["depe_codi"];
                    
                    $rs = $db->conn->Execute($sql);
                    while(!$rs->EOF) {
                        $where_area .= "," . $rs->fields['DEPE_CODI'];

                        $rs->MoveNext();
                    }
                }
                
                $where_area = "coalesce(depe_codi_padre, depe_codi) in ($where_area) or depe_codi in ($where_area)";
            }
        $sql = "select distinct depe_nomb, depe_codi from dependencia where depe_estado=1 and ($where_area) order by 1";
        //echo $sql;
        
        $rs_area = $db->query($sql);
        $sql=utilSqlSubrogacion($_SESSION["depe_codi"]);
        $rs_usr = $db->conn->Execute($sql);
    }

    switch ($codTx) {
        case 30:
            $menu_area = $rs_area->GetMenu2('depsel', $_SESSION["depe_codi"], false, false, 0,
                                            " id='depsel' class='select' onChange='cambiar_combo_usuarios()' ");
            $menu_usr  = $rs_usr->GetMenu2("usCodSelect", $codi_usuario, "0:&lt;&lt; Seleccione Usuario &gt;&gt;", false,""," id='usCodSelect' class='select'" );
            $accion = "<table width='100%' border='0' cellspacing='1'>";
            $accion .= "<tr class='titulos4'><td>Acci&oacute;n:</td><td>Area:</td><td>Usuario:</td></tr>";
            $accion .= "<tr class='listado1'><td valign='top'>Asignar Nueva Tarea</td><td>$menu_area</td><td>
                        <div name='mnu_usr' id='mnu_usr'>$menu_usr</div></td><tr></table>";
            break;
        case 31:
            $accion = "Acci&oacute;n: Finalizar Tarea ";
                break;
        case 32:
            $accion = "Acci&oacute;n: Cancelar Tarea";
                break;
    }
//realizarTx.php
  ?>

    <form action="" name="formulario" method='post' >
        <input type='hidden' name='txt_fech_tarea' id="txt_fech_tarea" value='<?=$validar_fecha?>'/>
        <input type='hidden' name="carpeta" id="carpeta" value="<?=$carpeta?>">
        <input type='hidden' name="codTx" id="codTx" value="<?=$codTx?>">
        <table width="98%" border="0" cellpadding="0" cellspacing="5" class="borde_tab">
            <tr>
                <td class="titulos4" colspan="3" width='100%' align='center'><?=$accion?></td>
            </tr>



<?      if ($codTx==30) {        //Muestra la fecha maxima de tarea para reasignar documentos y firmar y enviar ?>
            <tr>
                <td  colspan="3" align='center'>
                    <input type='hidden' name='fecha_maxima_tarea' id='fecha_maxima_tarea' value='<?=$fechaMaximaTarea?>'>
                    <br>
                    <b>Fecha M&aacute;xima de Tarea (aaaa-mm-dd): </b>
                    <?php echo dibujar_calendario("fecha_tarea", $fecha_tarea, $ruta_raiz, "validar_fecha_maxima();"); ?>
                    <br>
                </td>
            </tr>
<?  }  ?>
            <tr align="center">
                <td width='30%' align='right' valign='middle'><br/>
                    <span><b>Comentario: &nbsp;</b></span>
                </td>
                <td width='40%' align='center' valign='middle'><br/>
                    <textarea name="txt_comentario" id="txt_comentario" cols="70" rows="3" class="ecajasfecha"></textarea>
                </td>
                <td width='30%' valign='middle'><br/>
                    <span><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></span>
                </td>
            </tr>
            <tr>
                <td  colspan="3" align='center'>
                <? if ($whereFiltro !=="0") { ?>
                    <input type="button" name="btn_aceptar" id="btn_aceptar" class='botones' value='Aceptar' onClick="aceptar_tarea();" >
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <? } ?>
                    <input type='button' value='Regresar' onClick='history.back();' name='enviardoc' class='botones' id='Cancelar'>
                    
                </td>
            </tr>
            <tr>
                <td  colspan="3" align='center'>
                    <div id="div_ejecutar_tarea"></div>
                    <div id="div_tareas_asignadas"></div>
                    <br>
                </td>
            </tr>
        </table>
    	<br>
        
        
        <script type="text/javascript">
            function add_fila() {
                alert(document.getElementById('div_pr').innerHTML);
                document.getElementById('div_pr').innerHTML = document.getElementById('div_pr').innerHTML.replace('</table>','<tr><td>hola</td><td>1</td></tr></table>');
            }
        </script>
<?
	/*  GENERACION LISTADO DE RADICADOS
	 *  Aqui utilizamos la clase adodb para generar el listado de los radicados
         *  Esta clase cuenta con una adaptacion a las clases utiilzadas de orfeo.
         *  el archivo original es adodb-pager.inc.php la modificada es adodb-paginacion.inc.php
         */

        include_once "../include/query/tx/queryFormEnvio.php";
        echo "<div style='width: 98%'>";
        $pager = new ADODB_Pager($db,$isql,'adodb', false,1,"");
        $pager->toRefLinks = $linkPagina;
        $pager->toRefVars = $encabezado;
        $pager->checkAll = true;
        $pager->checkTitulo = false;
        $pager->Render($rows_per_page=20,$linkPagina,$checkbox=chkAnulados);
        echo "</div>";
?>
    </form>
</center>
</body>
</html>
