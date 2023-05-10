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

**************************************************************************************
** Respalda uno por uno los documentos de los usuarios                              **
** Busca los documentos que se deberán respaldar y los respalda uno por uno         **
** llamando a backup_usuarios_respaldar_documentos.php utilizando Ajax              **
**                                                                                  **
** Desarrollado por:                                                                **
**      Mauricio Haro A. - mauricioharo21@gmail.com                                 **
*************************************************************************************/

$ruta_raiz = "..";

include_once "$ruta_raiz/config.php";
include_once "$ruta_raiz/include/db/ConnectionHandler.php";
require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
include_once "$ruta_raiz/funciones_interfaz.php";

$db = new ConnectionHandler("$ruta_raiz");
$db->conn->SetFetchMode(ADODB_FETCH_ASSOC);

// Eliminamos los documentos que pudieron ingresarse el paso anterior y por algún error no deben ser ejecutados.
$sql = "select distinct resp_codi from respaldo_usuario
        where coalesce(fecha_inicio::text,'')='' and
        resp_codi in (select distinct resp_codi from respaldo_usuario_radicado)";
$rs = $db->query($sql);
while (!$rs->EOF) {
    $sql = "delete from respaldo_usuario_radicado where resp_codi=".$rs->fields["RESP_CODI"];
    $db->query($sql);
    $rs->MoveNext();
}

echo "<html>".html_head();
include_once "$ruta_raiz/js/ajax.js";

?>
<script language="JavaScript" type="text/JavaScript">
    function ejecutar_respaldo(resp_codi) {
        // Ejecuta el respaldo y cambia los botones play/pause
        document.getElementById('img_play_'+resp_codi).style.display='none';
        document.getElementById('img_pause_'+resp_codi).style.display='';
        contador_respaldos[resp_codi][2] = 1;  //Estado = Ejecutar
        respaldar_documento(resp_codi);
        return;
    }
    function pausar_respaldo(resp_codi) {
        // Pausa la ejecución del respaldo y cambia los botones play/pause
        document.getElementById('img_play_'+resp_codi).style.display='';
        document.getElementById('img_pause_'+resp_codi).style.display='none';
        contador_respaldos[resp_codi][2] = 2;  //Estado = Pausa
        return;
    }
    function detener_respaldo(resp_codi) {
        // Pone en estado finalizado el respaldo y oculata los botones play/pause
        try {
            document.getElementById('img_play_'+resp_codi).style.display='none';
            document.getElementById('img_pause_'+resp_codi).style.display='none';
            contador_respaldos[resp_codi][2] = 0;  //Estado = Detener: Finalizado - Error
        } catch (e) {}
        return;
    }

    function finalizar_respaldo(resp_codi) {
        // verifica si se finalizaron todos los respaldos y pasa a generar los zips
        detener_respaldo(resp_codi);
        for (var key in contador_respaldos) {
            if (contador_respaldos[key][2] == 1) return;
        }
        window.location = 'backup_usuarios_generar_zip.php';
        return;
    }

    function respaldar_documento(resp_codi) {
        // Ejecuta el respaldo de los documentos uno por uno;  y que no existan errores en la e
        if (contador_respaldos[resp_codi][2] == 1) { // Se controla el estado del respaldo: Estado = Ejecutar
            if (document.getElementById('div_procesar_respaldo_'+resp_codi).innerHTML.substring(0, 2) == 'OK') { // Verifica que no existan errores en la ejecución del respaldo
                document.getElementById('div_procesar_respaldo_'+resp_codi).innerHTML = 'Procesando...';
                nuevoAjax('div_procesar_respaldo_'+resp_codi, 'POST', 'backup_usuarios_respaldar_documentos.php', 'resp_codi='+resp_codi, 'respaldar_documento('+resp_codi+')');
                actualizar_barra(resp_codi);
                return;
            } else { // Si respondió cualquier cosa que no sea "OK" termina la ejecución
                finalizar_respaldo(resp_codi);
                return;
            }
        }
        return;
    }

    function actualizar_barra(resp_codi) { // Controla el movimiento de la barra general y la de cada respaldo
        try {
            // Verifica que la barra no sobrepase el 100%
            if (contador_respaldos[0][1]>=contador_respaldos[0][0] || contador_respaldos[resp_codi][1]>=contador_respaldos[resp_codi][0]) return;
            // Actualiza la barra general
            ++contador_respaldos[0][1];
            tamano_barra = Math.round(500*contador_respaldos[0][1]/contador_respaldos[0][0]);
            porcentaje =  Math.round(100*contador_respaldos[0][1]/contador_respaldos[0][0]);
            document.getElementById('div_barra').style.width=tamano_barra;
            document.getElementById('div_barra_mensaje').innerHTML = '<center>'+ porcentaje + ' %</center>';
            document.getElementById('div_mensaje').innerHTML='<center>Procesando documento No. '+ contador_respaldos[0][1]+' de '+contador_respaldos[0][0]+'</center>';
            // Actualiza la barra del respaldo
            ++contador_respaldos[resp_codi][1];
            tamano_barra = Math.round(200*contador_respaldos[resp_codi][1]/contador_respaldos[resp_codi][0]);
            porcentaje =  Math.round(100*contador_respaldos[resp_codi][1]/contador_respaldos[resp_codi][0]);
            document.getElementById('div_barra_'+resp_codi).style.width=tamano_barra;
            document.getElementById('div_barra_mensaje_'+resp_codi).innerHTML = '<center>'+ porcentaje + ' %</center>';
            document.getElementById('div_mensaje_'+resp_codi).innerHTML='<center>Procesando documento No. '+ contador_respaldos[resp_codi][1]+' de '+contador_respaldos[resp_codi][0]+'</center>';
        } catch (e) {
            return;
        }
    }

    function espacio_disco() {
        nuevoAjax('div_espacio_disco', 'POST', 'backup_espacio_disco.php', '');
        espacio_disco_timer_id = setTimeout("espacio_disco()", 300000);
        return;
    }
</script>

<body onload="espacio_disco()">
  <center>
    <br><br>
    <table width="90%" align="center" class=borde_tab border="0">
        <tr>
            <td width="100%" class="titulos5" colspan="3">
              <center>
                <br><b>PASO 2:</b> Respaldo de Documentos<br>&nbsp;
              </center>
            </td>
        </tr>
        <tr>
            <td width="20%">&nbsp;</td>
            <td width="60%" align="center">
                <br>Estado del proceso:<br><br>
                <div align='left' style='color: #FFFFFF; height: 26px; width: 506px; border: thin solid #999999; position: relative;'>
                    <div id='div_barra'
                         style='background-color: #a8bac6; color: #FFFFFF; border: thin solid #a8bac6;
                         height:20px; width: 0px; position:absolute; top:2px; left:2px; z-index:1;'></div>
                    <div id='div_barra_mensaje'
                         style='color: #000000; border: none; 
                         height:17px; width: 500px; position:absolute; top:5px; left:2px; z-index:100;'>

                    </div>
                </div><br>
                <div id="div_mensaje"></div><br>

            </td>
            <td width="20%" align="right" valign="middle">
                <div id="div_espacio_disco"></div>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <table class=borde_tab border="0" cellpadding="0" cellspacing="3" width="100%">
                    <tr>
                        <th width="40%">No. Respaldo</th>
                        <th width="35%">Avance</th>
                        <th width="5%" ></th>
                        <th width="20%">Estado</th>
                    </tr>
<?php
        // Consultamos los respaldos que se van a ejecutar
        $sql = "select ru.resp_codi, ver_usuarios(ru.usua_codi::text,'') as usr, coalesce(total,0) as total, coalesce(procesados,0) as procesados, s.resp_soli_codi
                from respaldo_usuario ru
                    left outer join (select resp_codi, count(resp_codi) as total, count(fila) as procesados from respaldo_usuario_radicado group by 1) as rr on rr.resp_codi=ru.resp_codi
                    left outer join respaldo_solicitud s on ru.resp_codi=s.resp_codi
                where ru.fecha_fin is null and ru.fecha_eliminado is null
                    and (s.fecha_ejecutar is null or s.fecha_ejecutar::date <= now()::date)
                    and (s.estado_solicitud is null or s.estado_solicitud=3)
                    and rr.total > 0
                order by 1";
        $rs = $db->query($sql);

        $js_contadores = "var contador_respaldos = new Array();\n";
        $total = 0;
        $procesados=0;
        while(!$rs->EOF) { //Creo una barra para cada respaldo
            //creo un arreglo con los contadores y el estado de los respaldos (0 - Finalizado; 1 - Ejecutando; 2 - Pausa)
            $js_contadores .= "contador_respaldos[".$rs->fields["RESP_CODI"]."] = new Array(".$rs->fields["TOTAL"].",".$rs->fields["PROCESADOS"].",2);\n";
            $total += $rs->fields["TOTAL"];
            $procesados += $rs->fields["PROCESADOS"];
?>
                    <tr height="30px">
                        <td>
                            <b><? if (trim($rs->fields["RESP_SOLI_CODI"])!="") echo "Solicitud No. ".$rs->fields["RESP_SOLI_CODI"].". ";?>
                                Respaldo No. <? echo $rs->fields["RESP_CODI"]; ?></b><br><?=$rs->fields["USR"]?>
                        </td>
                        <td align="center">
                            <div align='left' style='color: #FFFFFF; height: 20px; width: 206px; border: thin solid #999999; position: relative;'>
                                <div id='div_barra_<?=$rs->fields["RESP_CODI"]?>'
                                     style='background-color: #a8bac6; color: #FFFFFF; border: thin solid #a8bac6;
                                     height:14px; width: 0px; position:absolute; top:2px; left:2px; z-index:1;'></div>
                                <div id='div_barra_mensaje_<?=$rs->fields["RESP_CODI"]?>'
                                     style='color: #000000; border: none;
                                     height:7px; width: 200px; position:absolute; top:4px; left:2px; z-index:100;'>
                                </div>
                            </div>
                            <div id="div_mensaje_<?=$rs->fields["RESP_CODI"]?>">Procesados <?=$rs->fields["PROCESADOS"]?> de <?=$rs->fields["TOTAL"]?> documentos.</div>
                        </td>
                        <td align="center">
                            <img src="<?=$ruta_raiz?>/imagenes/play.png" id="img_play_<?=$rs->fields["RESP_CODI"]?>" alt="ejecutar" style="width: 20px; height: 20px;" onclick='ejecutar_respaldo(<?=$rs->fields["RESP_CODI"]?>)'>
                            <img src="<?=$ruta_raiz?>/imagenes/pause.png" id="img_pause_<?=$rs->fields["RESP_CODI"]?>" alt="detener" style="width: 20px; height: 20px; display: none;" onclick='pausar_respaldo(<?=$rs->fields["RESP_CODI"]?>)'>
                        </td>
                        <td><div id='div_procesar_respaldo_<?=$rs->fields["RESP_CODI"]?>' style="">OK</div></td>
                    </tr>
<?php
            $rs->MoveNext();
        }
        $js_contadores .= "contador_respaldos[0] = new Array($total,$procesados,0);"; //Creo el contador general y lo pongo en estado finalizado
?>
                </table>
            </td>
        </tr>
    </table>
    <br>


    <div id='div_procesar_respaldo' style="display:none">OK</div>
    <br>
    <input type="button" name="btn_cancelar" value="Regresar" class="botones_largo" onClick="window.close();">
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input type="button" id="btn_finalizar" value="Generar ZIP" class="botones_largo" onClick="window.location = 'backup_usuarios_generar_zip.php';">
  </center>

  <script type="text/JavaScript">
      <?=$js_contadores?>
  </script>
</body>
</html>
