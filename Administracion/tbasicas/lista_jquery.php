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
/*******************************************************************************
** código general para crear listado de catalogos;                            **
** basta con definir el query en el case                                      **
*******************************************************************************/


$ruta_raiz = "../..";
session_start();
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();
require_once "$ruta_raiz/js/ajax.js";
switch ($_GET['tipo_reporte']) {
    case 'instituciones':
        $titulo = "Listado General de Instituciones";
        $sql = "select inst_codi as \"CÓDIGO\", coalesce(inst_ruc,' ') as \"RUC\", coalesce(inst_nombre,' ') as \"NOMBRE\",
                 coalesce(inst_sigla,' ') as \"SIGLA\" from institucion where inst_estado <> 0";
        break;
    default :
        die ("No se encontr&oacute; el reporte solicitado");
}
$rs = $db->conn->query($sql);
if (!$rs or $rs->EOF) die("No se encontraron registros para el reporte solicitado");

$thead = "";
$tbody = "";
foreach ($rs->fields as $id_campo => $valor) {
    $thead .= "<th><center>$id_campo</center></th>";
}
$thead = "<tr>$thead</tr>\n";

while (!$rs->EOF) {
    $tbody .= "<tr>";
    foreach ($rs->fields as $id_campo => $valor) {
        $tbody .= "<td>$valor</td>";
    }
    $tbody .= "</tr>\n";
    $rs->MoveNext();
}


?>
    <style type="text/css" title="currentStyle">
        @import "<?=$ruta_raiz?>/estilos/jquery/style_datatables.css";
    </style>
    <script type="text/javascript" src="<?=$ruta_raiz?>/js/jquery.js"></script>
    <script type="text/javascript" src="<?=$ruta_raiz?>/js/jquery_tablas.js"></script>
    <script type="text/javascript" charset="utf-8">
        $(document).ready(function() {
            $('#tbl_listado').dataTable({"iDisplayLength": 20});
        });
    function excel(tipo) {
        if (tipo==1)
            nuevoAjax('div_reporte', 'POST', 'reporte_instituciones.php', 'tipo=xls');
        else
            nuevoAjax('div_reporte', 'POST', 'reporte_instituciones.php', 'tipo=pdf');
    }
    
    </script>
    <body>
        <table cellpadding="0" cellspacing="0" border="0" class="display" id="tbl_listado" width="100%">
            <thead><?=$thead?></thead>
            <tbody><?=$tbody?></tbody>
        </table>
        <table width='100%'><tr><td>
                    <?php
                    echo '<input type="button" name="btn_buscar" class="botones_largo" value="Exportar a XLS" onclick="excel(1);" title="Exporta todas las instituciones">';?>
                    <?php
                    //echo '<input type="button" name="btn_buscar" class="botones_largo" value="Exportar a PDF" onclick="excel(2);" title="Exporta todas las instituciones">';?>
                    
                </td></tr>
        <tr><td><div id='div_reporte' style="width: 99%"></div>
            </td></tr></table>
        <?php 
        //include "reporte_instituciones.php";
        ?>
    </body>
</html>
