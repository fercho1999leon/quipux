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

/*****************************************************************************************
**  Muestra la lista de archivos a descargar cuando el zip se parte en varios archivos  **
*****************************************************************************************/

$ruta_raiz = "..";
session_start();
require_once "$ruta_raiz/rec_session.php";

$codigo = 0 + $_GET["codigo_backup"];
$txt_resp_soli_codi = isset($_GET["resp_soli_codi"]) ? 0+$_GET["resp_soli_codi"] : 0;
$txt_descarga = isset($_POST["txt_descarga"]) ? 0+$_POST["txt_descarga"] : 0;

$flag_multiple_archivo = true;
$archivos = array();
$i = 1;
while ($flag_multiple_archivo and $i<100) {
    $cod_tmp = substr("00$i", -2);
    if (is_file("$ruta_raiz/bodega/respaldos/respaldo_$codigo.z$cod_tmp")) {
        $archivos[] = "$ruta_raiz/bodega/respaldos/respaldo_$codigo.z$cod_tmp";
        ++$i;
    } else {
        $archivos[] = "$ruta_raiz/bodega/respaldos/respaldo_$codigo.zip";
        $flag_multiple_archivo = false;
    }
}
$tamano_total = 0;

if (($txt_resp_soli_codi > 0 && $txt_descarga > 0)) {
  //Se guarda histórico de la acción  
  include_once "respaldo_funciones.php";
  $datos = ObtenerSolicitudPorCodigo($txt_resp_soli_codi,$db);
  $accion = array();
  unset($accion);
  $accion["RESP_SOLI_CODI"] = $datos["resp_soli_codi"];
  $accion["USUA_CODI"] = $_SESSION["usua_codi"];
  $accion["ACCION"] = 85;
  $accion["COMENTARIO"] = "El usuario ingresó a la descarga de su respaldo.";
  $accion["ESTADO_SOLICITUD"] = $datos["estado_solicitud"];
  $accion["ESTADO_RESPALDO"] = $datos["estado_respaldo"]; 
  GuardarHistoricoSolicitud($accion,$db);
}
include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

function transformar_tamanio_archivo ($tamano) {
    $tamano_medida = "b";
    if ($tamano>1024) { $tamano=$tamano/1024; $tamano_medida = "K";}
    if ($tamano>1024) { $tamano=$tamano/1024; $tamano_medida = "M";}
    if ($tamano>1024) { $tamano=$tamano/1024; $tamano_medida = "G";}
    $tamano = round($tamano, 2);
    return "$tamano$tamano_medida";
}
?>
<body>
    <script type="text/javascript">
        function descargar_archivo(extension) {
            //Se recarga página para guardar el histórico al descargar
            document.getElementById("txt_descarga").value = 1;
            solicitud = document.getElementById("txt_resp_soli_codi").value;
            codigo = document.getElementById("txt_codigo_respaldo").value;
            document.formulario.action = 'backup_usuarios_descargar_archivos.php?codigo_backup=' + codigo +'&resp_soli_codi='+solicitud;
            document.formulario.target = 'ifr_descargar_archivo_submit';
            document.formulario.submit();
            //Se abre la ventana para descargar archivo
            path_descarga = ('<?="$ruta_raiz/bodega/respaldos/respaldo_$codigo."?>' + extension).replace(/ /g,'');
            document.getElementById('ifr_descargar_archivo').src=path_descarga;
            
        }
        
        function solicitar_archivo(solicitud) {
           descarga = document.getElementById("txt_descarga").value;
           if(descarga == 0)
                alert("No ha intentado descargar el respaldo. Por favor realice la descarga, si tiene inconvenientes solicite a la STI.");
           else{             
                document.formulario.action = 'respaldo_notificacion.php?resp_soli_codi='+solicitud;
                document.formulario.target = '';
                document.formulario.submit();
           }
        }
    </script>
<form name="formulario" action="" method="post">
    <center>
        <input type="hidden" name="txt_descarga" id="txt_descarga" value="<?php echo $txt_descarga; ?>">
        <input type="hidden" name="txt_resp_soli_codi" id="txt_resp_soli_codi" value="<?php echo $txt_resp_soli_codi; ?>">
        <input type="hidden" name="txt_codigo_respaldo" id="txt_codigo_respaldo" size="20" value="<?php echo $codigo; ?>">
        <br><br>
        <table width="95%" align="center" border="0" cellpadding="0" cellspacing="5" class="borde_tab">
            <tr>
                <td class="listado1" colspan="3"><center>El respaldo se obtuvo en <?=$i?> archivos</center></td>
            </tr>
            <tr>
                <th width="33%"><center>No. de Archivo</center></th>
                <th width="33%"><center>Tama&ntilde;o</center></th>
                <th width="33%"><center>Acci&oacute;n</center></th>
            </tr>
<?
if (file_exists($archivos[0])){
    for ($i=0; $i<count($archivos); ++$i) {
        $tamano = filesize($archivos[$i]);
        $tamano_total = $tamano_total + $tamano;
        echo "<tr>
                <td class='listado1'>Parte ".($i+1)."</td>
                <td class='listado1'>".transformar_tamanio_archivo ($tamano)."</td>
                <td class='listado1'><a href='javascript:descargar_archivo(\"".substr($archivos[$i],-3)."\")' class='vinculos' target='_self'>Descargar</a></td>
              </tr>";

    }
}
?>
           <?php
           
              if ($_SESSION["usua_codi"]!=0){
              if (file_exists($archivos[0])){ ?>
                <tr>
                    <td class="listado1" colspan="3">
                        <br>
                        <center>Para unir los archivos en uno solo ejecutar: <br><b>&quot;zip -s 0 respaldo_<?=$codigo?>.zip --out respaldo_<?=$codigo?>_unificado.zip&quot;</b></center>
                        <br>
                    </td>                
                </tr>               
                <tr>
                    <td class="listado1" colspan="3">
                        <br>Su respaldo tiene un tamaño de <?php echo transformar_tamanio_archivo ($tamano_total); ?> . Si tiene algún inconveniente con la descarga de su respaldo, de click <a href='javascript:solicitar_archivo(<?= $txt_resp_soli_codi ?>)' class='vinculos' target='_self'>Aquí</a> para solicitar a la Subsecretaría de Tecnologías de la Información.
                        <br>
                    </td>                
                </tr>
            <? } else { ?>            
                <tr>
                    <td class="listado1" colspan="3">
                        <br>
                        <center><b>Su respaldo no se encuentra disponible, por favor solicite nuevamente.</b> <br></center>
                        <br>
                    </td>                
                </tr>                
            <? }
              }?>
        </table>
        <br><br>
        <input class="botones" type="button" name="cerrar" value="Cerrar" onclick="window.close()">
    </center>
</form>
    <iframe  name="ifr_descargar_archivo" id="ifr_descargar_archivo" style="display: none" src="">
            Su navegador no soporta iframes, por favor actualicelo.</iframe>
    <iframe  name="ifr_descargar_archivo_submit" id="ifr_descargar_archivo" style="display: none" src="">
            Su navegador no soporta iframes, por favor actualicelo.</iframe>
</body>