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

/** PROGRAMA DE CARGA DE IMAGENES DE RADICADOS
  *@author JAIRO LOSADA - DNP - SSPD
  *@version Orfeo 3.5.1
  *
  *@param $varBuscada sTRING Contiene el nombre del campo que buscara
  *@param $krd  string Trae el Login del Usuario actual
  *@param $isql strig Variable temporal que almacena consulta
  */

$ruta_raiz = "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
if (isset ($replicacion) && $replicacion && $config_db_replica_uf_cargar_doc_digitalizado!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_uf_cargar_doc_digitalizado);

include_once "$ruta_raiz/obtenerdatos.php";

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();
    
$verrad = "";
$carpeta = 98;


//paginador
$paginador = new ADODB_Pager_Ajax($ruta_raiz, "div_cuerpo", "cargar_doc_digitalizado_paginador.php",
                                  "txt_fecha_desde,txt_fecha_hasta,txt_depe_codi,txt_usua_codi,busqRadicados,imprimir_comprobante,asocImgRad","carpeta=$carpeta");

$txt_fecha_desde = date("Y-m-d", strtotime(date("Y-m-d")." - 30 day"));
$txt_fecha_hasta = date("Y-m-d");
$txt_depe_codi = 0+$_GET['txt_depe_codi'];
if ($_GET['txt_fecha_desde']!='')
$txt_fecha_desde = limpiar_sql ($_GET['txt_fecha_desde']);
if ($_GET['txt_fecha_hasta']!='')
$txt_fecha_hasta = limpiar_sql ($_GET['txt_fecha_hasta']);
if ($_GET['busqRadicados']!='')
$busqRadicados = limpiar_sql ($_GET['busqRadicados']);
if (trim($imprimir)=="si")
	$accion="$ruta_raiz/plantillas/CodigoBarras.php?nuevo=no";
else
//	$accion="formUpload.php";
	$accion="$ruta_raiz/anexos/cargar_imagen_digitalizada.php";

if (isset ($_GET["tipo_archivo"])) {
    $asocImgRad = "0";
    $nombre_boton = "Cargar Anexo";
    $tool_tip_boton = "Permite anexar nuevos archivos al documento";
} else {
    $asocImgRad = "1";
    $nombre_boton = "Asociar Imagen";
    $tool_tip_boton = "Permite asociar al documento una imagen resultado de escanear el documento f&iacute;sico";
}

?>
<script type="text/javascript">
function mostrar_documento(numdoc, txtdoc,carpeta)
{
    var_envio='<?=$ruta_raiz?>/verradicado.php?verrad='+numdoc+'&textrad='+txtdoc+'&carpetad='+carpeta+'&menu_ver_tmp=3&tipo_ventana=popup';
    window.open(var_envio,numdoc,"height=650,width=800,scrollbars=yes,left=800,top=200");
}

function fecha_ver(){
    if (document.getElementById('txt_fecha_hasta').value!='')
    {
        if(validar_fecha_maxima() == false)
        {
            alert("La fecha "+document.getElementById('txt_fecha_hasta').value+" no puede ser menor a "+document.getElementById('txt_fecha_desde').value)
            document.getElementById('txt_fecha_hasta').value='';
        }
        //else if(document.getElementById('txt_depe_codi').value!='0' && document.getElementById('txt_usua_codi').value=='0')
          //  alert("Por favor, seleccione un funcionario.");
        else
        {
            paginador_reload_div('');
        }
    }//si tiene fechas
    else
    {
        alert("Por favor, verifique las fechas")
    }
}

//Validar fechas de busqueda
function validar_fecha_maxima(){
    var fechaHasta = document.getElementById('txt_fecha_hasta').value;
    var fechaDesde = document.getElementById('txt_fecha_desde').value;

    if(validarFechas(fechaDesde, fechaHasta)==2)
    {
        alert('La fecha ' + fechaHasta +' no puede ser menor a la fecha '+ fechaDesde);
        document.getElementById('txt_fecha_hasta').value = "";
        return false;
    }
    return true;
}

//Cargar combo para usuarios
function cargar_combo_usuarios(txt_usua_codi) {
    area = document.getElementById("txt_depe_codi").value;    
    nuevoAjax('div_combo_usuarios', 'POST', '<?=$ruta_raiz?>/busqueda/cargar_combo_usuarios.php', 'txt_depe_codi='+area+'&txt_usua_codi='+txt_usua_codi);
}

    function verificar_radio_button() {
        for(i=0;i<document.formulario.elements.length;i++) {
            if(document.formulario.elements[i].checked==1 )
                return true;
        }
        return false;
    }

function cargar_doc_digitalizado_submit () {
    if (!verificar_radio_button()) {
        alert ('Por favor seleccione un documento.');
        return;
    }
    document.formulario.submit();
}

</script>
<body>
    <form action="<?=$accion?>" name='formulario' id='formulario' method="POST">
        <input type="hidden" id="imprimir_comprobante" name="imprimir_comprobante" value="<?=$imprimir?>">
        <input type="hidden" id="asocImgRad" name="asocImgRad" value="<?=$asocImgRad?>">
        <table width="100%" class="borde_tab" border="0" >
            <tr valign="top" height="23">
                <td width="20%" class="titulos2">Desde Fecha (yyyy/mm/dd):</td>
                <td width="26%" class="listado2">
                    <?php                     
                    echo dibujar_calendario("txt_fecha_desde", $txt_fecha_desde, $ruta_raiz, "validar_fecha_maxima();"); ?>
                </td>
                <td width="20%" class="titulos2">Hasta Fecha (yyyy/mm/dd):</td>
                <td width="26%" class="listado2">
                    <?php echo dibujar_calendario("txt_fecha_hasta", $txt_fecha_hasta, $ruta_raiz, "validar_fecha_maxima();"); ?>
                </td>
                <td width="8%" rowspan="3" valign="middle">
                    <input type=button value='Buscar' name=Buscar class='botones' title="Busca el texto ingresado en: Numero Documento, Asunto, No. Referencia y Fecha" onclick="fecha_ver();"/>
                </td>
            </tr>
            <tr>
                <td class="titulos2">Área:</td>
                <td class="listado2">
                <?php
                $sql = "select depe_nomb, depe_codi from dependencia where depe_estado=1 and inst_codi=".$_SESSION["inst_codi"]." order by 1 asc";
                $rs = $db->conn->query($sql);
                if($rs && !$rs->EOF)
                    print $rs->GetMenu2("txt_depe_codi", $txt_depe_codi, "0:&lt;&lt Todas las &Aacute;reas &gt;&gt;", false,"","class='select' id='txt_depe_codi' onChange='cargar_combo_usuarios(\"0\");'" );
                ?>
                </td>
                <td class="titulos2">Funcionario:</td>
                <td class="listado2">
                    <div id="div_combo_usuarios"><input type="hidden" name="txt_usua_codi" id="txt_usua_codi" value="<?=$txt_usua_codi?>"></div>
                </td>
            </tr>
            <tr>
                <td width="15%" class="titulos2">Texto a Buscar:</td>
                <td width="80%" colspan="4">
                    <input name="busqRadicados" id="busqRadicados" type="text" size="40" class="tex_area" value="<?=$busqRadicados?>"/>
                    Asunto, Número de Documento, Número de Referencia
                </td>
        </tr>
        </table>
        <?php
        if ($_SESSION["tipo_usuario"]!=2){
            $parametros="'$ruta_raiz/busqueda/busqueda.php'";
            ?>
            <a target='mainFrame'  onclick="llamaCuerpo(<?=$parametros?>)" href="javascript:;" class="aqui">B&uacute;squeda Avanzada</a>
        <?php }
        if (trim($imprimir)=="si") {
            echo "<input type='hidden' name='tipo_comp' id='tipo_comp' value='' >";
            echo "<center><input type='button' value='Imprimir C&oacute;digo de Barras' name=asocImgRad class='botones_largo' onclick='document.formulario.tipo_comp.value=\"1\"; document.formulario.submit();'>
            <input type='button' value='Imprimir Comprobante' name=asocImgRad class='botones_largo' onclick='document.formulario.tipo_comp.value=\"2\"; document.formulario.submit();'>
            <input type='button' value='Imprimir Ticket' name=asocImgRad class='botones_largo' onclick='document.formulario.tipo_comp.value=\"3\"; document.formulario.submit();'>
            </center>";
        }
        else
            echo "<center><input type='button' value='$nombre_boton' onClick='cargar_doc_digitalizado_submit();' name='btn_asociar' class='botones_largo' title='$tool_tip_boton'></center>";
        ?>
        <center>
        <div id="div_cuerpo"></div>
        </center>
    </form>
</body>
</html>
<script type="text/javascript">
function llamaCuerpo(parametros){
    top.frames['mainFrame'].location.href=parametros;
}
cargar_combo_usuarios("<?=$txt_usua_codi?>");
//paginador_reload_div('');
</script>