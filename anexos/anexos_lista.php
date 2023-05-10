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
//////////////   ANEXOS   ////////////////

session_start();
$ruta_raiz = "..";
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/obtenerdatos.php";
include_once "$ruta_raiz/seguridad/obtener_nivel_seguridad.php";

$radi_nume = limpiar_numero($_POST['radi_nume']);

$nivel_seguridad_documento = obtener_nivel_seguridad_documento($db, $radi_nume);
if ($nivel_seguridad_documento<2 and !($_SESSION["usua_perm_digitalizar"]==1 and isset($_POST["asocImgRad"])))
    die ("Usted no tiene los permisos suficientes para visualizar estos archivos");

$datos_radicado = ObtenerDatosRadicado($radi_nume,$db);

$lista_anexos_antes = "";
$lista_anexos_despues = "";

// Verificamos la fecha en que se firmó el documento
$fecha_firma_documento = date("Y-m-d H:i:").(date("s")+10);
if (substr($radi_nume,-1)=="1") {
    $fecha_firma_documento = $datos_radicado["fecha_radicado"];
} else {
    if (!in_array($datos_radicado["estado"], array(1,7,8))) {
        $sql = "select radi_fech_radi from radicado where radi_nume_temp=$radi_nume and radi_nume_radi::text like '%1' limit 1";
        $rs_fecha = $db->conn->query($sql);
        if ($rs_fecha && !$rs_fecha->EOF)
            $fecha_firma_documento = $rs_fecha->fields["RADI_FECH_RADI"];
    }
}

// Consulto los archivos anexos
$sql = "select anex_codigo, anex_nombre, anex_desc, anex_path
            , anex_tamano, ver_usuarios(anex_usua_codi::text, '') as usua_nombre, anex_usua_codi
            , anex_fecha, anex_fisico, arch_codi, arch_codi_firma, anex_datos_firma, anex_tipo
        from anexos
        where anex_radi_nume in ($radi_nume,".$datos_radicado["radi_nume_temp"].") and anex_borrado='N'
        order by anex_fecha asc";
//echo "<hr>$isql";
$rs = $db->conn->query($sql);

while($rs && !$rs->EOF) {
    $anex_codigo = $rs->fields["ANEX_CODIGO"];
    $anex_nombre = trim(strtolower($rs->fields["ANEX_NOMBRE"]));
    $anex_path = $rs->fields["ANEX_PATH"];
    $anex_descripcion_texto = $rs->fields["ANEX_DESC"];
    $anex_tamano = $rs->fields["ANEX_TAMANO"];
    $anex_fecha = substr($rs->fields["ANEX_FECHA"], 0, 19);
    $anex_usuario = $rs->fields["USUA_NOMBRE"];
    $anex_usua_codi = 0 + $rs->fields["ANEX_USUA_CODI"];
    $anex_arch_codi = 0 + $rs->fields["ARCH_CODI"];
    $anex_arch_codi_firma = 0 + $rs->fields["ARCH_CODI_FIRMA"];
    $anex_datos_firma = $rs->fields["ANEX_DATOS_FIRMA"];
    $anex_tipo = (substr($anex_nombre, -4) == ".p7m") ? 1 : 0;

    $flag_imagen = (($datos_radicado["radi_path"]==$anex_path and trim($datos_radicado["radi_path"])!="" and $datos_radicado["radi_imagen"]=="")
                    or ($datos_radicado["radi_imagen"]==$anex_codigo)) ? true : false;

    $anex_descargar_img = "";
    $anex_vista_previa_img = "&nbsp;";
    $anex_firma_img = "&nbsp;";
    $anex_borrar_img="&nbsp;";
    $anex_descripcion = "<span id='span_descripcion_$anex_codigo'>$anex_descripcion_texto</span>&nbsp;";

    // Medio de almacenamiento
    $anex_medio = "<span id='span_medio_$anex_codigo'>Electr&oacute;nico</span>&nbsp;";
    if ($rs->fields["ANEX_FISICO"]==1)
        $anex_medio = "<span id='span_medio_$anex_codigo'>F&iacute;sico</span>&nbsp;";

    // Tipo de anexo (imagen digitalizada o anexo)
    $anex_imagen = "<span id='span_imagen_$anex_codigo'>Anexo</span>&nbsp;";
    if ($flag_imagen)
        $anex_imagen = "<span id='span_imagen_$anex_codigo'>Imagen Digitalizada</span>&nbsp;";


    if ($nivel_seguridad_documento>=2 or $_SESSION["usua_codi"]==$anex_usua_codi) {
        // Descargar Archivos
        $anex_descargar_img = "<img src='$nombre_servidor/imagenes/document_down.jpg' alt='Descargar' title='Descargar Archivo'
                            style='width: 26px; height: 22px;' onclick=\"anexos_descargar_archivo('$radi_nume','$anex_codigo',$anex_tipo)\">";

        if (in_array($rs->fields["ANEX_TIPO"], array(4,5,6,7,8,17))){ //Si es pdf, imagen o txt
            $anex_vista_previa_img = "<img src='$nombre_servidor/iconos/vista_previa.jpg' alt='Descargar' title='Vista Previa'
                                style='width: 26px; height: 22px;' onclick=\"anexos_descargar_archivo('$radi_nume','$anex_codigo',0,'embeded')\">";
        }

        // Firma Electronica
        $anex_firma = "";
        if (substr($anex_nombre, -4) == ".p7m" and $nivel_seguridad_documento>=2) {
            $anex_firma = "No se ha podido verficar la firma electr&oacute;nica del documento.";
            if (trim($anex_datos_firma) != "") {
                $anex_firma = preg_replace(':<tr><th.*?tr>:is', '', $anex_datos_firma);
                $anex_firma = str_replace("<table>", "<table width='100%'>", $anex_firma);
            }
            if ($anex_arch_codi > 0) {//Si se verifico la firma al momento de cargar el documento, $anex_arch_codi_firma > 0
                $anex_firma .= "<a class=vinculos href='javascript:;' onclick=\"anexos_descargar_archivo('$radi_nume','$anex_codigo', 0)\"
                                title='Ver archivo verificado'>Ver Archivo</a>
                                &nbsp;&nbsp;&nbsp;&nbsp;";
            } else {
                $anex_vista_previa_img = "&nbsp;";
            }
            $anex_firma .= "<a class=vinculos href='javascript:;' onclick=\"anexos_verificar_firma('','$anex_codigo')\"
                            title='Verificar la firma digital del documento'>Verificar Firma</a>";
            $anex_firma_img = "<img src='$nombre_servidor/imagenes/key-yellow.png' alt='Descargar' title='Verificar Firma Electr&oacute;nica'
                                style='width: 22px; height: 18px;' onclick=\"anexos_verificar_firma('','$anex_codigo')\">";
        }

    }


    // Eliminar anexos
    if ($nivel_seguridad_documento==7) {
//        $anex_borrar_img .= "<a class=vinculos href='javascript:;' onclick=\"fjs_anexos_acciones('$radi_nume','$anex_codigo','1')\"
//                                title='Borrar el archivo anexo'>Borrar archivo</a>";
        $anex_borrar_img = "<img src='$nombre_servidor/iconos/trash.png' alt='X' title='Eliminar archivo' style='width: 23px; height: 23px;'
                              onclick=\"fjs_anexos_acciones('$radi_nume','$anex_codigo','1')\">";


    // Si esta en elaboración permite modificar o eliminar el anexo
        $tmp_selected = ($rs->fields["ANEX_FISICO"]==1) ? "selected" : ""; //Si es físico lo marca por defecto
        $anex_medio .= "<img src='$nombre_servidor/imagenes/internas/pencil_add.png' onclick=\"modificar_opcion_mostrar('medio_$anex_codigo',2);\"
                            id='img_medio_$anex_codigo' title='Modifica el medio de almacenamiento del archivo' alt='editar'>
                        <select name='txt_medio_$anex_codigo' class='select' id='txt_medio_$anex_codigo' style='display: none;' onchange=\"fjs_anexos_acciones('$radi_nume','$anex_codigo',this.value)\">
                            <option value='4' selected>Electr&oacute;nico</option>
                            <option value='5' $tmp_selected>F&iacute;sico</option>
                        </select>";


        $anex_descripcion .= "<img src='$nombre_servidor/imagenes/internas/pencil_add.png' onclick=\"modificar_opcion_mostrar('descripcion_$anex_codigo',2);\"
                            id='img_descripcion_$anex_codigo' title='Modifica el texto ingresado como descripción del archivo' alt='editar'>
                        <textarea name='txt_descripcion_$anex_codigo' id='txt_descripcion_$anex_codigo' cols='65' rows='1' class='tex_area' onchange='this.value=this.value.substring(0,500)' style='display: none;'>$anex_descripcion_texto</textarea>
                        &nbsp;&nbsp;
                        <img src='$nombre_servidor/imagenes/disk.png' onclick=\"fjs_anexos_acciones('$radi_nume','$anex_codigo','6');\" id='img_guardar_descripcion_$anex_codigo' style='vertical-align: top; border none; display: none;' title='Graba la descripción del archivo anexo' alt='editar'>";

    } // If nivel de seguridad


    if ((substr($radi_nume, -1)=="2" and ($nivel_seguridad_documento==7 or ($nivel_seguridad_documento==5 and $datos_radicado["estado"]==1)))
    or ($_SESSION["usua_perm_digitalizar"]==1 and isset($_POST["asocImgRad"]) and isset($_POST["chk_asociar_imagen"]) and $_POST["chk_asociar_imagen"]=="1")) {//isset($_POST["asocImgRad"]) and trim($rs_radi->fields["RADI_FECH_FIRMA"])=="")) {
        // Si está en elaboración
        $tmp_selected = ($flag_imagen) ? "selected" : "";
        $anex_imagen .= "<img src='$nombre_servidor/imagenes/internas/pencil_add.png' onclick=\"modificar_opcion_mostrar('imagen_$anex_codigo',2);\"
                            id='img_imagen_$anex_codigo' title='Modifica el tipo de documento, si es un anexo o la imagen digitalizada del documento' alt='editar'>
                        <select name='txt_imagen_$anex_codigo' class='select' id='txt_imagen_$anex_codigo' style='display: none;' onchange=\"fjs_anexos_acciones('$radi_nume','$anex_codigo',this.value)\">
                            <option value='3' selected>Anexo</option>
                            <option value='2' $tmp_selected>Imagen Digitalizada</option>
                        </select>";
    }

    $lista_anexos_archivo = "
        <tr id='tr_anexo_$anex_codigo'>
            <td width='2%'  class='listado1' align='center'>$anex_descargar_img</td>
            <td width='2%'  class='listado1' align='center'>$anex_vista_previa_img</td>
            <td width='2%'  class='listado1' align='center'>$anex_firma_img</td>
            <td width='94%' class='listado2'>
                <table width='100%' border='0' cellpadding='0' cellspacing='0'>
                    <tr>
                        <td width='60%' class='listado2' rowspan='2' onclick=\"fjs_anexos_mostrar_detalle_archivo ('$anex_codigo');\">&nbsp;&nbsp;&nbsp;&nbsp;<b>$anex_nombre</b></td>
                        <td width='35%' class='listado2'><b>Fecha: </b>$anex_fecha $descZonaHoraria</td>
                        <td width='5%'  class='listado2' valign='top' align='right' rowspan='2'>
                            <img id='img_anexos_ocultar_detalle_$anex_codigo' src='$nombre_servidor/iconos/bandeja_comprimida2.png' alt='&Delta;' title='Ocultar detalle' onclick=\"fjs_anexos_mostrar_detalle_archivo ('$anex_codigo');\" style='display: none;'>
                            <img id='img_anexos_mostrar_detalle_$anex_codigo' src='$nombre_servidor/iconos/bandeja_expandida.png' alt='&nabla;' title='Mostrar detalle' onclick=\"fjs_anexos_mostrar_detalle_archivo ('$anex_codigo');\">
                            &nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td class='listado2'><b>Usuario: </b>$anex_usuario</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr id='tr_anexo_detalle_$anex_codigo' style='display: none;'>
            <td>&nbsp;</td>
            <td class='listado1' align='center' colspan='3'>
                <table width='100%' border='0' cellpadding='0' cellspacing='5'>
                    <tr>
                        <td width='20%' class='listado1'><b>Medio de Almacenamiento:</b></td>
                        <td width='15%' class='listado1'>$anex_medio</td>
                        <td width='15%' class='listado1'><b>Tipo de Anexo:</b></td>
                        <td width='15%' class='listado1'>$anex_imagen</td>
                        <td width='14%' class='listado1'><b>Tama&ntilde;o:</b></td>
                        <td width='14%' class='listado1'>$anex_tamano Kb</td>
                        <td width='2%' class='listado1' style='vertical-align: right; text-align: center;' rowspan='3'>$anex_borrar_img</td>
                    </tr>
                    <tr>
                        <td class='listado1'><b>Descripci&oacute;n:</b></td>
                        <td class='listado1' colspan='4'>$anex_descripcion</td>
                        <!--td class='listado1' align='right'>$anex_borrar_img</td-->
                    </tr>";
    if ($anex_firma != "")
        $lista_anexos_archivo .= "
                    <tr>
                        <td class='listado1' valign='top'><b>Informaci&oacute;n de Firma:</b></td>
                        <td class='listado1' colspan='5'>$anex_firma</td>
                    </tr>";
    $lista_anexos_archivo .= "
                </table>
            </td>
        </tr>";

    if ((strtotime($fecha_firma_documento)-strtotime($anex_fecha))>0)
        $lista_anexos_antes .= $lista_anexos_archivo;
    else
        $lista_anexos_despues .= $lista_anexos_archivo;
    $rs->MoveNext();
}	//fin del while

if ($lista_anexos_antes == "" && $lista_anexos_despues == "")
    echo crear_tabla_anexos("<tr><td colspan='5'>El documento no tiene archivos anexos.</td></tr>", "Archivos anexos al documento");
else {
    if ($lista_anexos_antes != "")
        echo crear_tabla_anexos($lista_anexos_antes, "Archivos anexos al documento");
    if ($lista_anexos_despues != "")
        echo crear_tabla_anexos($lista_anexos_despues, "Archivos subidos al sistema posterior a la firma del documento");
}


function crear_tabla_anexos($datos, $titulo) {
    $tabla = "
        <table width='100%' border='0' cellpadding='0' cellspacing='5' class='borde_tab'>
            <tr><th colspan='5'>$titulo</th></tr>
            $datos
        </table>
        <br>";
    return $tabla;
}
?>