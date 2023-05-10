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

  $ruta_raiz = "..";
  session_start();
  include_once "$ruta_raiz/rec_session.php";
  require_once("$ruta_raiz/funciones.php");
  require_once("$ruta_raiz/obtenerdatos.php");

  $radi_nume = trim(limpiar_sql($_POST['radi_nume']));

  $radi_asoc_ante = trim(limpiar_sql($_POST['txt_radi_asoc_ante']));
  $radi_asoc_cons = substr(trim(limpiar_sql($_POST['txt_radi_asoc_cons'])),1);
  $radi_refe = trim(limpiar_sql($_POST['radi_refe']));
  //$txt_editar_refe = trim(limpiar_sql($_POST["txt_editar_refe"]));
  $txt_editar_refe=1;
      //
      $vradi_nume = substr($radi_nume,19,1);//radicado
      $rad_aso = array();
      $rad_aso =ObtenerDatosRadicado($radi_nume, $db);      
      $radi_tmp = substr($rad_aso['radi_nume_temp'],19,1);
      $rad_deri=$rad_aso['radi_padre'];
      
      if (($vradi_nume==0 and $rad_deri!='') || ($vradi_nume==1 and $radi_tmp==2))
      $txt_editar_refe=0;
  //echo $txt_editar_refe;
  function mostrar_datos ($documento, $tipo,$radi_refe,$txt_editar_refe) {
      
      global $db;
      $lista = explode(',',$documento);
      $cad = "";
      foreach ($lista as $doc) {
        if (trim($doc) != "") {
          $datos = ObtenerDatosRadicado($doc,$db);
          
          if ($datos["estado"]!="7" and $datos["estado"]!="8") {
              $cad .= "<tr>".
                      "<td>" . $datos["radi_nume_text"] . "</td>".
                      "<td>" . substr($datos["radi_fecha"],0,19) . "</td>".
                      "<td>" . $datos["radi_asunto"] ."</td>"; 
              if ($tipo=='A'){                  
                      if ($radi_refe==''){                          
                          if ($txt_editar_refe==1 || $txt_editar_refe==2){                         
                            $cad.= "<td align='center'><a href='javascript:seleccionar_documento_borrar(\"$doc\", \"$tipo\")' style='color:blue;'>Borrar</a></td>";
                          }else
                              $cad.= "<td align='center'><a href='javascript:seleccionar_documento_borrar(\"$doc\", \"$tipo\")' style='color:blue;'>Borrar</a></td>";
                      }else{                          
                        if ($txt_editar_refe==1 || $txt_editar_refe==2)                         
                            $cad.= "<td align='center'><a href='javascript:seleccionar_documento_borrar(\"$doc\", \"$tipo\")' style='color:blue;'>Borrar</a></td>";
                        //verificar si el documento es externo y esta en edicion
                        elseif($datos["estado"]==1 and $datos["radi_tipo"]==2)
                            $cad.= "<td align='center'><a href='javascript:seleccionar_documento_borrar(\"$doc\", \"$tipo\")' style='color:blue;'>Borrar</a></td>";
                        $cad.= "<td>&nbsp;</td>";
                      }
              }
                      else
                          $cad.= "<td align='center'><a href='javascript:seleccionar_documento_borrar(\"$doc\", \"$tipo\")' style='color:blue;'>Borrar</a></td>";
                      $cad.= "</tr>";
          }
        }
      }
      if ($cad=='') $cad = "<tr><td colspan='4' align='center'><b>No hay documentos relacionados.</b></td></tr>";
      return $cad;
  }

/*
  $datos_radi = ObtenerDatosRadicado($radi_nume,$db);
  $radi_nume_padre = "";
  if (0+$datos_radi["radi_padre"] != 0) {
      $radi_nume_padre = "El documento No. ".$datos_radi["radi_nume_text"]." esta asociado al documento No. ".
                          ObtenerCampoRadicado("radi_nume_text",$datos_radi["radi_padre"],$db).".";
  }
  $datos_asoc = ObtenerDatosRadicado($radi_asoc,$db);
  $radi_asoc_padre = "";
  if (0+$datos_asoc["radi_padre"] != 0) {
      $radi_asoc_padre = "El documento No. ".$datos_asoc["radi_nume_text"]." esta asociado al documento No. ".
                          ObtenerCampoRadicado("radi_nume_text",$datos_asoc["radi_padre"],$db).".";
  }
*/
  include_once "$ruta_raiz/funciones_interfaz.php";
  echo "<html>".html_head();
?>
  <body>
    <center>
        <br>
        <table width="100%" align="center" class=borde_tab border="0">
            <tr>
                <td colspan='4'>
                    <b>Antecedente del documento</b>
                </td>
            </tr>
            <tr>
                <th width='20%' align='center'>No. documento</th>
                <th width='20%' align='center'>Fecha</th>
                <th width='40%' align='center'>Asunto</th>
                <th width='20%' align='center'>Acci&oacute;n</th>
            </tr>
            <?= mostrar_datos ($radi_asoc_ante, "A",$radi_refe,$txt_editar_refe); ?>
            <tr>
                <td colspan='4'>
                    <br><b>Consecuentes del documento</b>
                </td>
            </tr>
            <tr>
                <th width='20%' align='center'>No. documento</th>
                <th width='20%' align='center'>Fecha</th>
                <th width='40%' align='center'>Asunto</th>
                <th width='20%' align='center'>Acci&oacute;n</th>
            </tr>
           <?= mostrar_datos ($radi_asoc_cons, "C",$radi_refe,$txt_editar_refe); ?>
        </table>
    </center>
  </body>
</html>

