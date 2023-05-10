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

**************************************************************************************
** Código HTML de los respaldos                                                     **
** Conjunto de funciones que generan strings con código HTML que formarán la        **
** interfaz de cada archivo de los respaldos                                        **
**                                                                                  **
** Desarrollado por:                                                                **
**      Mauricio Haro A. - mauricioharo21@gmail.com                                 **
*************************************************************************************/

function cargar_html() {
    //pantalla que engloba los datos del documento
    global $radi_nume, $db, $path_anexos;
    $html = "
        <html>
          <head>
            <title>Respaldo</title>
            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
            <link href='../documentos/estilos.css' rel='stylesheet' type='text/css'>
            <script type='text/javascript'>
                function seleccionar(boton) {
                    for (i=1 ; i<=3 ; i++) {
                        if (i == boton) {
                            document.getElementById('div_datos'+i).style.display='block';
                            document.getElementById('btn_boton'+i).style.backgroundColor='#6a819d';
                        } else {
                            document.getElementById('div_datos'+i).style.display='none';
                            document.getElementById('btn_boton'+i).style.backgroundColor='#e3e8ec';
                        }
                    }
                }
            </script>
          </head>
          <body>
            <center>
              <br>
              <table width='100%' align='center' border='0' cellspacing='2' cellpadding='0'>
                <tr>
                  <td width='1%' class='normal0'>
                    <a href='javascript:history.back(1);' title='Regresar a la p&aacute;gina anterior' style='text-decoration:none'>
                        <img src='../archivos/regresar.png' name='img_regresar' style='border:none;' width='40px' height:'30px';>
                    </a>
                  </td>
                  <td width='99%' class='normal0'>
                    <a href='javascript:history.back(1);' title='Regresar a la p&aacute;gina anterior' style='text-decoration:none'>
                        Atr&aacute;s
                    </a>
                  </td>
                </tr>
                <tr>
                  <td valign='bottom' align='left' colspan='2'>
                    <a href='javascript:seleccionar(1)' style='text-decoration:none'>
                      <div id='btn_boton1' class='boton'>Info. General</div>
                    </a>
                    <a href='javascript:seleccionar(2)' style='text-decoration:none'>
                        <div id='btn_boton2' class='boton' style='left:2px;'>Anexos</div>
                    </a>
                    <a href='javascript:seleccionar(3)' style='text-decoration:none'>
                        <div id='btn_boton3' class='boton' style='left:4px;'>Recorrido</div>
                    </a>
                  </td>
                </tr>
                <tr style='border:thin solid #999999;'>
                  <td width='100%' colspan='2'>
                      <div id='div_datos1' style='display:none; width:100%'>".cargar_info_general()."</div>
                      <div id='div_datos2' style='display:none; width:100%'>";
    if (substr($radi_nume,-1) == "1") {
        $radi_padre = ObtenerCampoRadicado("radi_nume_temp",$radi_nume,$db);
        $html .= cargar_info_anexos($radi_padre) . "<br>";
    }
    $html .= cargar_info_anexos($radi_nume) . "</div>
                      <div id='div_datos3' style='display:none; width:100%'>".cargar_info_recorrido()."</div>
                  </td>
                </tr>
              </table>
            </center>
            <script>seleccionar(1);</script>
          </body>
        </html>";
    return $html;
}

function cargar_info_general() {
    // Información general del documento
    global $radi_nume, $db, $path_anexos, $pdf;
    $datos = ObtenerDatosRadicado($radi_nume,$db);
    $usuarios = cargar_datos_usuarios($datos["radi_nume_temp"],"L");

    // Si no tiene una imagen relacionada generamos el PDF
    if (trim($datos["radi_path"])=="" && (0+$datos["arch_codi"])==0 && (0+$datos["arch_codi_firma"])==0
            && trim($datos["radi_imagen"])=='' && substr($datos["radi_nume_temp"], -1)=="0") {
        $ok = $pdf->GenerarPDF($radi_nume,"no",$db->rutaRaiz,1,$db);
        $datos = ObtenerDatosRadicado($radi_nume,$db);
    }

    $path_archivo = array(trim($datos["radi_path"]), $datos["arch_codi"], $datos["arch_codi_firma"], "pdf"); // Añadimos a la lista de archivos por copiar

    // Si tiene un anexo asociado como imagen
    if (strpos($datos["radi_path"], "/docs/")!==false) {
        $tmp = trim(str_replace(".p7m", "", strtolower($datos["radi_path"])));
        $tmp=explode(".",  $tmp);
        $path_archivo = array(trim($datos["radi_path"]), $datos["arch_codi"], $datos["arch_codi_firma"], $tmp[count($tmp)-1]); // Añadimos a la lista de archivos por copiar
    }
    if (trim($datos["radi_imagen"])!='') {
        $sql = "select a.anex_path, a.arch_codi, a.arch_codi_firma, t.anex_tipo_ext
                from anexos a left outer join anexos_tipo t on a.anex_tipo=t.anex_tipo_codi
                where a.anex_codigo='".$datos["radi_imagen"]."'";
        $rs_anex = $db->query($sql);
        if ($rs_anex && !$rs_anex->EOF)
            $path_archivo = array(trim($rs_anex->fields["ANEX_PATH"]), $rs_anex->fields["ARCH_CODI"], $rs_anex->fields["ARCH_CODI_FIRMA"], $rs_anex->fields["ANEX_TIPO_EXT"]); // Añadimos a la lista de archivos por copiar
    }
    $path_anexos[] = $path_archivo;


    if ($path_archivo[0]!="" or (0+$path_archivo[1])!=0) {
        $anex_path = transformar_path($path_archivo, 0);
        $img_documento = "
            <tr>
              <td class='normal1'>Documento:</td>
              <td class='normal0'>&nbsp;
                <a href='$anex_path' title='Descargar Archivo' style='text-decoration:none'>
                    <img src='../archivos/descargar.png' name='img_doc' style='border:none;'>
                </a> Descargar documento (".$path_archivo[3].").
              </td>
            </tr>";
    } else {
        $img_documento = "
            <tr>
              <td class='normal1'>Documento:</td>
              <td class='normal0'>&nbsp;Documento Digitalizado no disponible.</td>
            </tr>";
    }
    
    $firma = "";
    if (trim($datos["fecha_firma"]) != "" or trim($datos["usua_firma"]) != "" or (0+$path_archivo[2])!=0) {
        $anex_path = transformar_path($path_archivo, 1);
        $firma = "
            <tr>
              <td class='normal1'>Firma Digital:</td>
    	      <td class='normal0'>&nbsp;
                <a href='$anex_path' title='Descargar Archivo' style='text-decoration:none'>
                  <img src='../archivos/descargar.png' name='img_doc' style='border:none;'>
                </a> Descargar archivo firmado digitalmente.<br>
                &nbsp;".str_replace("<table>","<table width='100%' border='1' cellspacing='0' cellpadding='0' class='normal0'>",$datos["usua_firma"]).
              "</td>
             </tr>";
    }

    $html = "
      <table width='100%' border='1' cellspacing='0' cellpadding='0'>
        <tr>
    	  <td width='30%' class='normal1'>No. de Documento:</td>
          <td width='70%' class='normal0'>&nbsp;" . $datos["radi_nume_text"] . "&nbsp;</td>
        </tr>
        <tr>
    	  <td class='normal1'>Tipo de Documento:</td>
    	  <td class='normal0'>&nbsp;" . $datos["radi_tipo_desc"] . "&nbsp;</td>
        </tr>
        <tr>
    	  <td class='normal1'>Fecha: (aaaa-mm-dd)</td>
    	  <td class='normal0'>&nbsp;" . substr($datos["radi_fecha"],0,19) . " GMT -05</td>
        </tr>
        <tr>
          <td class='normal1'>Asunto: </td>
          <td class='normal0'>&nbsp;" . $datos["radi_asunto"] . "&nbsp;</td>
        </tr>
        $img_documento
        $firma
        <tr>
    	  <td class='normal1'>De:</td>
          <td class='normal0'>" . $usuarios[1] . "&nbsp;</td>
        </tr>
        <tr>
          <td class='normal1'>Para:</td>
          <td class='normal0'>" . $usuarios[2] . "&nbsp;</td>
        </tr>
        <tr>
          <td class='normal1'>Con copia a:</td>
          <td class='normal0'>" . $usuarios[3] . "&nbsp;</td>
        </tr>
    </table>
    ";

    return $html;
}

function cargar_info_anexos($radi_nume) {
    // Listado de anexos del documento
    global $db, $path_anexos;
    $sql = "select a.anex_nombre, a.anex_path, a.anex_desc, a.anex_fecha, a.anex_tamano
                , a.anex_datos_firma, a.arch_codi, a.arch_codi_firma, t.anex_tipo_ext
            from anexos a left outer join anexos_tipo t on a.anex_tipo=t.anex_tipo_codi
            where anex_radi_nume=$radi_nume and anex_borrado='N'";
    $rs = $db->query($sql);

    $mensaje = "";
    if (substr($radi_nume, -1) == "1") $mensaje = "adicionales";

    $html = "
      <table width='100%' border='1' cellspacing='0' cellpadding='0'>
        <tr>
    	  <td class='titulo'>Archivos $mensaje anexos al documento</td>
        </tr>
      </table>
      <br>";

    if (!$rs or $rs->EOF) {
        $html .= "
          <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
              <td class='normal0' align='center' valign='middle'>No se encontraron archivos $mensaje anexos al documento</td>
            </tr>
          </table>";
    } else {
        while (!$rs->EOF) {
            $path_archivo = array(trim($rs->fields["ANEX_PATH"]), 0+$rs->fields["ARCH_CODI"], 0+$rs->fields["ARCH_CODI_FIRMA"], $rs->fields["ANEX_TIPO_EXT"]); // Añadimos a la lista de archivos por copiar
            $path_anexos[] = $path_archivo; // Añadimos a la lista de archivos por copiar

            $firma = "";
            if (trim($rs->fields["ANEX_DATOS_FIRMA"]) != "") {
                $anex_path = transformar_path($path_archivo, 1);
                $firma = "
                    <tr>
                      <td class='normal1' valign='top'>Firma Digital:</td>
                      <td class='normal0'>&nbsp;
                        <a href='$anex_path' title='Descargar Archivo' style='text-decoration:none'>
                          <img src='../archivos/descargar.png' name='img_doc' style='border:none;'>
                        </a> Descargar archivo firmado digitalmente.<br>
                        &nbsp;".str_replace("<table>","<table width='100%' border='1' cellspacing='0' cellpadding='0' class='normal0'>",$rs->fields["ANEX_DATOS_FIRMA"]) .
                      "</td>
                     </tr>";
            }

            $anex_path = transformar_path($path_archivo, 0);
            $html .= "
              <table width='100%' border='1' cellspacing='0' cellpadding='0'>
                <tr>
                  <td width='30%' class='normal1'>Nombre:</td>
                  <td width='70%' class='normal0'>&nbsp;" . $rs->fields["ANEX_NOMBRE"] . "</td>
                </tr>
                <tr>
                  <td class='normal1'>Descripci&oacute;n:</td>
                  <td class='normal0'>&nbsp;" . $rs->fields["ANEX_DESC"] . "</td>
                </tr>
                <tr>
                  <td class='normal1'>Fecha: (aaaa-mm-dd)</td>
                  <td class='normal0'>&nbsp;" . substr($rs->fields["ANEX_FECHA"],0,19) . " GMT -05</td>
                </tr>
                <tr>
                  <td class='normal1'>Tama&ntilde;o: </td>
                  <td class='normal0'>&nbsp;" . $rs->fields["ANEX_TAMANO"] . " Kb.</td>
                </tr>
                <tr>
                  <td class='normal1'>Archivo:</td>
                  <td class='normal0'>&nbsp;
                    <a href='$anex_path' title='Descargar Archivo' style='text-decoration:none'>
                        <img src='../archivos/descargar.png' name='img_doc' style='border:none;'>
                    </a> Descargar documento (" . $rs->fields["ANEX_TIPO_EXT"] . ").
                  </td>
                </tr>
                $firma
            </table>
            <br>
            ";
            $rs->MoveNext();
        }
    }


    return $html;
}

function cargar_info_recorrido() {
    // Recorrido del documento
    global $radi_nume, $db;

    $sql = "select h.hist_fech, h.hist_obse, sgd_ttr_descrip, u1.usua_nombre as \"usua_origen\", u2.usua_nombre as \"usua_destino\"
            from hist_eventos h
            left outer join usuario u1 on h.usua_codi_ori=u1.usua_codi
            left outer join usuario u2 on h.usua_codi_dest=u2.usua_codi
            left outer join sgd_ttr_transaccion t on h.sgd_ttr_codigo=t.sgd_ttr_codigo
            where radi_nume_radi=$radi_nume";
    $rs = $db->query($sql);
    $html = "
      <table width='100%' border='1' cellspacing='0' cellpadding='0'>
        <tr>
    	  <td width='15%' class='titulo'>Fecha</td>
    	  <td width='20%' class='titulo'>De</td>
    	  <td width='20%' class='titulo'>Para</td>
    	  <td width='20%' class='titulo'>Acci&oacute;n</td>
    	  <td width='25%' class='titulo'>Observaci&oacute;n</td>
        </tr>";

    $i = 0;
    while (!$rs->EOF) {
        $html .= "
            <tr>
              <td class='normal$i'>&nbsp;" . substr($rs->fields["HIST_FECH"],0,19) . " GMT -05</td>
              <td class='normal$i'>&nbsp;" . $rs->fields["USUA_ORIGEN"] . "</td>
              <td class='normal$i'>&nbsp;" . $rs->fields["USUA_DESTINO"] . "</td>
              <td class='normal$i'>&nbsp;" . $rs->fields["SGD_TTR_DESCRIP"] . "</td>
              <td class='normal$i'>&nbsp;" . $rs->fields["HIST_OBSE"] . "</td>
            </tr>";
        $i = ($i + 1) % 2;
        $rs->MoveNext();
    }
    $html .= "</table>";

    return $html;
}

function transformar_path($path_archivo, $firmado) {
    $nomb_arch = "";
    if ($path_archivo[0]!="" and $path_archivo[1]==0 and $path_archivo[2]==0) {
        $tmp=explode("/",  strtolower($path_archivo[0]));
        $tmp=explode("\\", $tmp[count($tmp)-1]);
        $tmp=explode(".",  $tmp[count($tmp)-1]);
        $nomb_arch = $tmp[0];
    }
    if ($path_archivo[1]!=0 && $firmado==0)
        $nomb_arch = $path_archivo[1];
    if ($path_archivo[2]!=0 && $firmado==1)
        $nomb_arch = $path_archivo[2];
    
    $nomb_arch .= ".".$path_archivo[3];
    if ($firmado==1) $nomb_arch .= ".p7m";

    return "../archivos/$nomb_arch";
}

function cargar_datos_usuarios($radicado, $tipo="C") {
    // Carga la información de los usuarios destinatario, remitente y con copia
    global $db;
    $datos = array();
    unset ($datos);
    $datos[1] = "";
    $datos[2] = "";
    $datos[3] = "";

    $sql = "select usua_nombre, usua_apellido, usua_abr_titulo, usua_cargo, usua_institucion, radi_usua_tipo
		from usuarios_radicado where radi_nume_radi=$radicado";
    $rs = $db->conn->query($sql);
    while(!$rs->EOF) {
        if ($tipo == "L") {
            $datos[$rs->fields["RADI_USUA_TIPO"]] .= "&nbsp;" . $rs->fields["USUA_ABR_TITULO"] . " " .
                   $rs->fields["USUA_NOMBRE"] . " " . $rs->fields["USUA_APELLIDO"] .
                   ", ".$rs->fields["USUA_CARGO"].", ".$rs->fields["USUA_INSTITUCION"].".<br>";
        } else {
            $datos[$rs->fields["RADI_USUA_TIPO"]] .= $rs->fields["USUA_NOMBRE"] . " " . $rs->fields["USUA_APELLIDO"] . "<br>";
        }
        $rs->MoveNext();
    }
    return $datos;

}

function cargar_fila() {
    // Genera el código html para las bandejas de documentos
    global $radi_nume, $db;
    $datos = ObtenerDatosRadicado($radi_nume,$db);
    $usuarios = cargar_datos_usuarios($datos["radi_nume_temp"],"C");

    $html = "
        <tr>
    	  <td>&nbsp;<a href='../documentos/$radi_nume.html'>" . substr($datos["radi_fecha"],0,19) . " GMT -05</a></td>
          <td>&nbsp;" . $datos["radi_nume_text"] . "</td>
          <td>" . $usuarios[1] . "</td>
          <td>" . $usuarios[2] . "</td>
          <td>&nbsp;" . $datos["radi_asunto"] . "</td>
    	  <td>&nbsp;" . $datos["radi_tipo_desc"] . "</td>
    	  <td>&nbsp;";
    if (trim($datos["fecha_firma"]) == "") $html .= "No"; else $html .= "Si";
    $html .= "</td>
        </tr>";

    return $html;
}


?>