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

//$ruta_raiz = "..";
//include_once "$ruta_raiz/rec_session.php";


// desplegamos la lista de columnas
?>

<script type="text/javascript">
    var columnas_nombre = new Array();
    var columnas_desc = new Array();
    var columnas_tipo   = new Array();
    var columnas_orden  = new Array();
    var columnas_selec_disponibles = '';
    var columnas_selec_reporte = '';
    var num_grupo1 = 0;
    var num_grupo2 = 0;
<?
    $i = 0;
    $tipo = "G1";
    foreach ($columnas as $id => $desc) {
        echo "columnas_nombre['$id'] = '$desc';\n";
        echo "columnas_desc['$id'] = '".$columnas_desc["$id"]."';\n";
        echo "columnas_tipo['$id'] = '$tipo';\n";
        if (++$i>=$columnas_grupo1) $tipo = "G2";
    }
?>

    function reportes_estructura_anadir_columnas(columnas) {
        tmp_array = new Array;
        //Valida si se recibe las columnas como parametro (al momento de cargar la pagina) o se las selecciono por la aplicacion
        str = columnas || columnas_selec_disponibles;
        tmp_array = str.split(",");
        for (i=1 ; i<tmp_array.length ; ++i) {
            // Validamos a que grupo pertenece, si es del G2 se añade al final el campo
            if (columnas_tipo[tmp_array[i]] == 'G2') {
                columnas_orden[(num_grupo1+num_grupo2)] = tmp_array[i];
                ++num_grupo2;
            } else {
                // Si pertenece al G1, desplazamos todos los campos del G2 un espacio y añadimos el campo al final del G1
                for (j=(num_grupo1+num_grupo2) ; j>num_grupo1 ; --j) {
                    columnas_orden[j] = columnas_orden[j-1];
                }
                columnas_orden[num_grupo1] = tmp_array[i];
                ++num_grupo1;
            }
        }
        // Movemos el orden en el campo que se enviara en el submit y ocultamos el campo en la tabla 1
        str = '';
        for (i=0 ; i<(num_grupo1+num_grupo2) ; ++i) {
            str += ',' + columnas_orden[i];
            document.getElementById('tr_columnas_disponibles_'+columnas_orden[i]).style.display = 'none';
        }
        document.getElementById('txt_lista_columnas').value = str;
        // Limpiamos las variables y dibujamos la tabla
        columnas_selec_disponibles = '';
        reportes_estructura_dibujar_columnas_reporte();
    }

    function reportes_estructura_quitar_columnas() {
        tmp_array = new Array;
        tmp_array = columnas_selec_reporte.split(",");
        for (i=1 ; i<tmp_array.length ; ++i) {
            if (columnas_tipo[tmp_array[i]] == 'G2') {
                --num_grupo2;
            } else {
                --num_grupo1;
            }
            flag = false;
            for (j=0 ; j<=(num_grupo1+num_grupo2) ; ++j) {
                if (columnas_orden[j] == tmp_array[i]) {
                    flag = true;
                    document.getElementById('tr_columnas_disponibles_'+tmp_array[i]).style.display = '';
                    document.getElementById('tr_columnas_disponibles_'+tmp_array[i]).style.cssText = 'background-color:#e3e8ec';
                }
                if (flag) columnas_orden[j] = columnas_orden[j+1];
            }
        }
        str = '';
        for (i=0 ; i<(num_grupo1+num_grupo2) ; ++i) {
            str += ',' + columnas_orden[i];
        }
        document.getElementById('txt_lista_columnas').value = str;
        columnas_selec_reporte = '';
        reportes_estructura_dibujar_columnas_reporte();
    }

    function reportes_estructura_subir_columnas() {
        tmp_array = new Array;
        tmp_array = columnas_selec_reporte.split(",");

        for (i=0 ; i<(num_grupo1+num_grupo2) ; ++i) {
            for (j=1 ; j<tmp_array.length ; ++j) {
                if (columnas_orden[i] == tmp_array[j]) {
                    if (columnas_tipo[tmp_array[j]] == 'G2') {
                        limite1 = num_grupo1;
                        limite2 = num_grupo1 + num_grupo2 -1;
                    } else {
                        limite1 = 0;
                        limite2 = num_grupo1 - 1;
                    }
                    if (i>limite1 && i<=limite2) {
                        tmp = columnas_orden[i];
                        columnas_orden[i] = columnas_orden[(i-1)];
                        columnas_orden[(i-1)] = tmp;
                    } else {
                        i = num_grupo1 + num_grupo2;
                    }
                }
            }
        }
        str = '';
        for (i=0 ; i<(num_grupo1+num_grupo2) ; ++i) {
            str += ',' + columnas_orden[i];
        }
        document.getElementById('txt_lista_columnas').value = str;
        reportes_estructura_dibujar_columnas_reporte();
    }

    function reportes_estructura_bajar_columnas() {
        tmp_array = new Array;
        tmp_array = columnas_selec_reporte.split(",");

        for (i=(num_grupo1+num_grupo2-1) ; i>=0 ; --i) {
            for (j=1 ; j<tmp_array.length ; ++j) {
                if (columnas_orden[i] == tmp_array[j]) {
                    if (columnas_tipo[tmp_array[j]] == 'G2') {
                        limite1 = num_grupo1;
                        limite2 = num_grupo1 + num_grupo2 -1;
                    } else {
                        limite1 = 0;
                        limite2 = num_grupo1 - 1;
                    }
                    if (i>=limite1 && i<limite2) {
                        tmp = columnas_orden[i];
                        columnas_orden[i] = columnas_orden[(i+1)];
                        columnas_orden[(i+1)] = tmp;
                    } else {
                        i = -1;
                    }
                }
            }
        }
        str = '';
        for (i=0 ; i<(num_grupo1+num_grupo2) ; ++i) {
            str += ',' + columnas_orden[i];
        }
        document.getElementById('txt_lista_columnas').value = str;
        reportes_estructura_dibujar_columnas_reporte();
    }

    function reportes_estructura_seleccionar_columna(tipo, columna) {
        if (tipo == 'D') {
            str = columnas_selec_disponibles;
            nom_tr = 'tr_columnas_disponibles_';
        } else {
            str = columnas_selec_reporte;
            nom_tr = 'tr_columnas_reporte_';
        }
        flag = true;
        col = new Array();
        col = str.split(',');
        str = '';
        for (i=1 ; i<col.length ; ++i) {
            if (col[i]==columna) {
                flag = false;
                document.getElementById(nom_tr+columna).style.cssText = 'background-color:#e3e8ec';
            } else {
                str += ','+col[i];
            }
        }
        if (flag) {
            document.getElementById(nom_tr+columna).style.cssText = 'background-color:#a8bac6';
            str += ','+columna;
        }
        
        if (tipo == 'D') {
            columnas_selec_disponibles = str;
        } else {
            columnas_selec_reporte = str;
        }
    }

    function reportes_estructura_dibujar_columnas_reporte() {
        col = new Array();
        col = document.getElementById('txt_lista_columnas').value.split(',');
        str = '<center><table width="90%" align="center" border="0" cellspacing="2" class="borde_tab">\n';
        for (i=1 ; i<col.length ; ++i) {
            str += "<tr id='tr_columnas_reporte_"+col[i]+"' class='listado2' ";
            str += "onclick='reportes_estructura_seleccionar_columna(\"R\",\""+col[i]+"\")'>";
            str += "<td title='"+columnas_desc[col[i]]+"'>" + columnas_nombre[col[i]] + "</td></tr>\n";
            //if (i==(num_grupo1)) str += '</table>\n<table width="90%" align="center" border="0" cellspacing="2">\n';
        }
        str += '</table></center>';
        document.getElementById('div_columnas_reporte').innerHTML = str;
        col = columnas_selec_reporte.split(',');
        for (i=1 ; i<col.length ; ++i) {
            document.getElementById('tr_columnas_reporte_'+col[i]).style.cssText = 'background-color:#a8bac6';
        }

    }


</script>


<input type="hidden" name="txt_lista_columnas" id="txt_lista_columnas" value="">
<table width="100%" align="center" border="0">
  <tr>
    <th width="100%" colspan="5">
      <center>
        Estructura del reporte
      </center>
    </th>
  </tr>
  <tr>
      <td width="40%" valign="middle">
        <center>
          <table width="90%" align="center" border="0" cellspacing="2" class="borde_tab">
<?
            //$i = 0;
            foreach ($columnas as $col => $desc) {
                echo "<tr id='tr_columnas_disponibles_$col' class='listado2' onclick='reportes_estructura_seleccionar_columna(\"D\",\"$col\")'>
                        <td title='".$columnas_desc[$col]."'>$desc</td>
                      </tr>";
                //if ($i == $columnas_grupo1-1) echo '</table><table width="90%" align="center" border="1" cellspacing="0">';
                //++$i;
            }
?>
          </table>
        </center>
      </td>
      <td width="10%" valign="middle">
        <center>
          <input type="button" name="btn_accion" class="botones_2" value="&gt;" onclick="reportes_estructura_anadir_columnas()" title="Añadir columnas al reporte.">
          <br>&nbsp;<br>
          <input type="button" name="btn_accion" class="botones_2" value="&lt;" onclick="reportes_estructura_quitar_columnas()" title="Quitar columnas del reporte.">
        </center>
      </td>
      <td width="40%" valign="middle"><center><div id="div_columnas_reporte"></div></center></td>
      <td width="10%" valign="middle">
        <center>
          <input type="button" name="btn_accion" class="botones_2" value="&and;" onclick="reportes_estructura_subir_columnas()" title="Añadir columnas al reporte.">
          <br>&nbsp;<br>
          <input type="button" name="btn_accion" class="botones_2" value="&or;" onclick="reportes_estructura_bajar_columnas()" title="Quitar columnas del reporte.">
        </center>
      </td>
  </tr>
</table>
<script type="text/javascript">
    reportes_estructura_anadir_columnas(',<?=$columnas_default?>');
    //reportes_estructura_dibujar_columnas_reporte();
    //reportes_estructura_cargar_lista_columnas();
    //
</script>
