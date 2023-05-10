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
** interfaz de de los respaldos                                                     **
**                                                                                  **
** Desarrollado por:                                                                **
**      Mauricio Haro A. - mauricioharo21@gmail.com                                 **
*************************************************************************************/

function cargar_estilos() {
    $html = "
            .titulo {
                font-family:  Arial, Helvetica, sans-serif;
                font-size: 11px;
                font-style: normal;
                font-weight: bolder;
                color: #000000;
                background-color: #a8bac6;;
                text-indent: 5pt;
                vertical-align: middle;
                text-align: center;
                height:30px;
            }
            .normal0 {
                font-family:  Arial, Helvetica, sans-serif;
                font-size: 11px;
                font-style: normal;
                font-weight: normal;
                color: #000000;
                vertical-align: middle;
                height:30px;
            }
            .normal1 {
                font-family:  Arial, Helvetica, sans-serif;
                font-size: 11px;
                font-style: normal;
                font-weight: normal;
                color: #000000;
                vertical-align: middle;
                background-color: #e3e8ec;
                height:30px;
            }
            .boton {
                border: thin solid #999999;
                height:20px;
                width: 100px;
                font-family:  Arial, Helvetica, sans-serif;
                font-size: 11px;
                font-style: normal;
                font-weight: bold;
                color: #000000;
                text-align: center;
                position: relative;
                float:left;
            }
            .menu {
                border: thin solid #FFFFFF;
                height:30px;
                width: 100%;
                font-family:  Arial, Helvetica, sans-serif;
                font-size: 11px;
                font-style: normal;
                font-weight: bold;
                color: #000000;
            }
            img {border:0;}
    ";
    return $html;
}

function cargar_index() {
    $html = "
        <html>
          <head>
            <title>Respaldos .: Sistema de Gesti&oacute;n Documental &quot;Quipux&quot; :.</title>
            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
          </head>
          <frameset rows='97,864*' frameborder='YES' border='0' framespacing='0' cols='*'>
            <frame name='top_frame' scrolling='NO' noresize src='./documentos/top.html'></frame>
            <frameset cols='175,947*' border='0' framespacing='0' rows='*'>
              <frame name='left_frame' scrolling='AUTO' marginwidth='0' marginheight='0' border=1 src='./documentos/menu.html'></frame>
              <frame name='main_frame' id='main_frame' src='./documentos/informacion.html' scrolling='AUTO'></frame>
            </frameset>
          </frameset>
          <noframes></noframes>
        </html>";

    return $html;
}


function cargar_top($respaldo) {
    global $db;
    $rs = $db->query("select usua_codi from respaldo_usuario where resp_codi=$respaldo");
    $usuario = ObtenerDatosUsuario($rs->fields["USUA_CODI"],$db);
    $html = "
        <html>
          <head>
            <title>Respaldo</title>
            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
          </head>
          <body>
            <table width='100%' cellpadding='0' cellspacing='0' border='0'>
              <tr>
                <td width='20%' valign='center' align='left'><img src='../archivos/logo.png'  height='70' width='100' alt='Salir'></td>
                <td width='60%' valign='center' align='center'><h2>Gobierno Nacional de la Rep&uacute;blica del Ecuador</h2></td>
                <td width='20%' valign='center' align='right'></td>
              </tr>
            </table>
            <center><b>Respaldo de documentos de " . $usuario["abr_titulo"] . " " . $usuario["nombre"] . "</b></center>
          </body>
        </html>";
    return $html;
}

function cargar_menu() {
    $html = "
        <html>
          <head>
            <title>Menu</title>
            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
            <link href='../documentos/estilos.css' rel='stylesheet' type='text/css'>
            <script type='text/javascript'>
                function seleccionar_menu(boton) {
                    for (i=1 ; i<=3 ; i++) {
                        if (i == boton) {
                            document.getElementById('div_menu_'+i).style.backgroundColor='#6a819d';
                        } else {
                            document.getElementById('div_menu_'+i).style.backgroundColor='#e3e8ec';
                        }
                    }
                }
            </script>
          </head>
          <body>
             <br>
             <a href='../documentos/informacion.html' onclick='seleccionar_menu(1)' target='main_frame'
                     title='Informaci&oacute;n general del respaldo' style='text-decoration:none'>
               <div id='div_menu_1' class='menu'>&nbsp;&nbsp;Informaci&oacute;n General</div>
             </a>
             <a href='../documentos/recibidos.html' onclick='seleccionar_menu(2)' target='main_frame'
                     title='Documentos recibidos' style='text-decoration:none'>
               <div id='div_menu_2' class='menu'>&nbsp;&nbsp;Recibidos</div>
             </a>
             <a href='../documentos/enviados.html' onclick='seleccionar_menu(3)' target='main_frame'
                     title='Documentos enviados' style='text-decoration:none'>
               <div id='div_menu_3' class='menu'>&nbsp;&nbsp;Enviados</div>
             </a>
             <script>seleccionar_menu(1);</script>
          </body>
        </html>";

    return $html;
}

function cargar_informacion($respaldo) {
    global $db;
    $sql = "select count(resp_codi) as \"num\" from respaldo_usuario_radicado where fila is not null and resp_codi=$respaldo";
    $rs = $db->query($sql);
    $total_documentos = $rs->fields["NUM"];
    $sql = "update respaldo_usuario set num_documentos=$total_documentos where resp_codi=$respaldo";
    $db->query($sql);

    //Se actualiza solicitud de respaldo
    $sql = "update respaldo_solicitud set num_documentos=$total_documentos where resp_codi=$respaldo";
    $db->query($sql);
    
    ////$rs = $db->query("select usua_codi from respaldo_usuario where resp_codi=$respaldo");
    $rs = $db->query("select ru.usua_codi, substr(rs.fecha_inicio_doc::text,1,10) as fecha_inicio_doc, substr(rs.fecha_fin_doc::text,1,10) as fecha_fin_doc
    from respaldo_usuario as ru
    left outer join (select * from respaldo_solicitud) as rs on ru.resp_codi=rs.resp_codi
    where ru.resp_codi=$respaldo");
    $usuario = ObtenerDatosUsuario($rs->fields["USUA_CODI"],$db);
    $html = "
        <html>
          <head>
            <title>Respaldo</title>
            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
            <link href='../documentos/estilos.css' rel='stylesheet' type='text/css'>
          </head>
          <body>
            <center>
            <br>
            <table width='80%' border='1' cellspacing='0' cellpadding='0'>
              <tr class='titulo'><td><center>Respaldo de documentos generados en el Sistema de Gesti&oacute;n Documental &quot;Quipux&quot;</center></td></tr>
            </table>
            <br>
            <table width='80%' border='1' cellspacing='0' cellpadding='0'>
              <tr>
                <td width='30%' class='normal1'>Usuario:</td>
                <td width='70%' class='normal0'>&nbsp;" . $usuario["abr_titulo"] . " " . $usuario["nombre"] . "</td>
              </tr>
              <tr>
                <td class='normal1'>C&eacute;dula:</td>
                <td class='normal0'>&nbsp;" . substr($usuario["cedula"],0,10) . "</td>
              </tr>
              <tr>
                <td class='normal1'>Cargo:</td>
                <td class='normal0'>&nbsp;" . $usuario["cargo"] . "</td>
              </tr>
              <tr>
                <td class='normal1'>&Aacute;rea:</td>
                <td class='normal0'>&nbsp;" . $usuario["dependencia"] . "</td>
              </tr>
              <tr>
                <td class='normal1'>Instrituci&oacute;n:</td>
                <td class='normal0'>&nbsp;" . $usuario["institucion"] . "</td>
              </tr>
            </table>
            <br>
            <table width='80%' border='1' cellspacing='0' cellpadding='0'>
              <tr>
                <td width='30%' class='normal1'>Fecha del respaldo: (aaaa-mm-dd)</td>
                <td width='70%' class='normal0'>&nbsp;" . date('Y-m-d H:i:s') . " GMT -05</td>
              </tr>
              <tr>
                <td class='normal1'>No. Documentos:</td>
                <td class='normal0'>&nbsp; Se respaldaron $total_documentos documentos</td>
              </tr>";
            if($rs->fields["FECHA_INICIO_DOC"] != '' and ($rs->fields["FECHA_FIN_DOC"])){
               $fecha_inicio_doc = $rs->fields["FECHA_INICIO_DOC"];
               $fecha_fin_doc = $rs->fields["FECHA_FIN_DOC"];
               $html .= "<tr>
                <td class='normal1'>Periodo de respaldo:</td>
                <td class='normal0'>&nbsp; Desde:" . $fecha_inicio_doc. " Hasta: " . $fecha_fin_doc. "</td>
              </tr>";
            }
         $html .= "</table>
            </center>
          </body>
        </html>";
    return $html;
}


function cargar_bandejas ($respaldo, $tipo) {
    global $db;
    $num_registros = 50;
    $sql = "select fila from respaldo_usuario_radicado where resp_codi=$respaldo and tipo=$tipo and fila is not null order by resp_radi_codi asc";
    $rs = $db->query($sql);
    
    $mensaje = "Enviados";
    if ($tipo==2) $mensaje = "Recibidos";

    $html = "
        <html>
          <head>
            <title>Bandeja</title>
            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
            <link href='../documentos/estilos.css' rel='stylesheet' type='text/css'>
            <style type='text/css' title='currentStyle'>
                @import '../documentos/jquery/style_datatables.css';
            </style>
            <script type='text/javascript' src='../documentos/jquery/jquery.js'></script>
            <script type='text/javascript' src='../documentos/jquery/jquery_tablas.js'></script>
            <script type='text/javascript'>
                $(document).ready(function() {
                        $('#tbl_documentos').dataTable({'iDisplayLength': 20});
                });
            </script>
          </head>
          <body>
            <br>
            <table width='100%' border='1' cellspacing='0' cellpadding='0'>
              <tr class='titulo'><td><center>Listado de documentos $mensaje</center></td></tr>
            </table>
            <br>
            ";
    $html .= "
        <table width='100%' border='0' cellspacing='0' cellpadding='0' class='display' id='tbl_documentos'>
            <thead>
              <tr>
                <th width='15%'>Fecha</th>
                <th width='15%'>No. Documento</th>
                <th width='18%'>De</th>
                <th width='18%'>Para</th>
                <th width='18%'>Asunto</th>
                <th width='8%'>Tipo Documento</th>
                <th width='8%'>Firma Digital</th>
              </tr>
            </thead>
            <tbody>";
    while (!$rs->EOF) {
        $html .= base64_decode($rs->fields["FILA"]);
        $rs->MoveNext();
    }
    $html .= "
              </tbody>
              </table>
          </body>
        </html>";
    return $html;
}


?>
