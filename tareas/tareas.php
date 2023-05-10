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
//////////////   LISTA DE TAREAS   ////////////////
if (!$ruta_raiz) $ruta_raiz="..";
if (str_replace("/","",str_replace(".","",$ruta_raiz))!="") die ("");
include_once "$ruta_raiz/js/ajax.js";
include_once "tareas_funciones.php";

if ($datosrad["usua_actu"]==$_SESSION["usua_codi"] and $datosrad["estado"]==2)
    echo "<table border='0' width='100%'><tr><td width='100%' align='right'>".botones_tarea("nueva", 0, $verrad, $ruta_raiz)."</td></tr></table>";

echo dibujar_tareas($db, 0, $verrad, $ruta_raiz);
echo "<br>";
echo dibujar_tareas($db, 1, $verrad, $ruta_raiz);
echo "<br><table border='0' width='100%'><tr><td width='100%' align='center'><input type=button class='botones_largo' name='btn_imprimir' onclick='imprimir_historico_tareas();' value='Imprimir'></td></tr></table><br>";
?>

<script type="text/javascript">
    function mostrar_historico_tarea(tarea_codi) {
        if (document.getElementById("tr_descripcion_"+tarea_codi).style.display == '')
            document.getElementById("tr_descripcion_"+tarea_codi).style.display = 'none';
        else
            document.getElementById("tr_descripcion_"+tarea_codi).style.display = '';
    }

    function imprimir_historico_tareas() {
        document.getElementById('ifr_descargar_archivo').src='./reportes/generar_reporte_tareas.php?verrad=<?=$verrad?>';
    }
</script>