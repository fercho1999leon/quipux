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


$criterios = explode(",", $lista_criterios);

?>
<script type='text/javascript'>
    // Funciones que manejan las acciones de reportes_criterios.php
    function reportes_criterios_cargar_criterios() {
        try {
            txt_lista_criterios = "<?=$criterios_default?>";
            txt_lista_criterios = document.getElementById('txt_lista_criterios').value = txt_lista_criterios;

            tmp_array = txt_lista_criterios.split(",");
            for (i=0 ; i<=tmp_array.length ; ++i) {
                document.getElementById('tr_'+tmp_array[i]).style.display='';
            }
        } catch (e) {
            return;
        }
    }

    function reportes_criterios_cargar_combos(tipo) {
        if (tipo == 'I') {
            try {
                codigo = document.getElementById('txt_inst_codi').value;
            } catch (e) {
                codigo = 0;
            }
            nuevoAjax('div_combo_areas', 'POST', 'reportes_criterios_combos.php', 'txt_tipo=A&txt_inst_codi='+codigo);
            nuevoAjax('div_combo_usuarios', 'POST', 'reportes_criterios_combos.php', 'txt_tipo=U&txt_depe_codi=0');
        }
        if (tipo == 'A') {
            try {
                codigo = document.getElementById('txt_depe_codi').value;
                if (codigo == 0) {
                    codigo = document.getElementById('txt_all_depe_codi').value;
                }
            } catch (e) {
                codigo = 0;
            }
            nuevoAjax('div_combo_usuarios', 'POST', 'reportes_criterios_combos.php', 'txt_tipo=U&txt_depe_codi='+codigo);
        }
    }

</script>

<input type="hidden" name="txt_lista_criterios" id="txt_lista_criterios" value="">

<table width="100%" align="center" border="0">
    <tr>
        <th width="100%" colspan="2">
            <center>
                Criterios de b&uacute;squeda
            </center>
        </th>
    </tr>

    <!-- Lista de criterios -->

    <!-- Fecha Desde -->
    <tr id="tr_txt_fecha_desde" style="display: none;">
        <td width="35%" class="titulos5" valign="middle">Fecha desde:</td>
        <td width="65%" class="listado5" valign="middle">
            <script type="text/javascript">dateAvailable1.writeControl();</script>
            &nbsp;
        </td>
    </tr>

    <!-- Fecha Hasta -->
    <tr id="tr_txt_fecha_hasta" style="display: none;">
        <td class="titulos5" valign="middle">Fecha hasta:</td>
        <td class="listado5" valign="middle">
            <script type="text/javascript">dateAvailable2.writeControl();</script>
            &nbsp;
        </td>
    </tr>

    <!-- Institucion -->
    <tr id="tr_txt_inst_codi" style="display: none;">
        <td class="titulos5" valign="middle">Instituci&oacute;n:</td>
        <td class="listado5" valign="middle">
<?
    $sql = "select inst_nombre, inst_codi from institucion where inst_estado=1 order by 1 asc";
    $rs = $db->conn->Execute($sql);
    echo $rs->GetMenu2("txt_inst_codi", "0", "0:&lt;&lt; Todas las instituciones &gt;&gt;", false,"",
                       "id='txt_inst_codi' class='select'" ); // onChange=\"reportes_criterios_cargar_combos('I')\"
?>
        </td>
    </tr>

    <!-- Area -->
    <tr id="tr_txt_depe_codi" style="display: none;">
        <td class="titulos5" valign="middle">&Aacute;rea:</td>
        <td class="listado5" valign="middle">
            <div id="div_combo_areas">
<?
    $where_areas = "";
    if ($_SESSION["usua_perm_estadistica"] != 1) $where_areas = " and depe_codi=".$_SESSION["depe_codi"];
    $sql = "select depe_nomb, depe_codi from dependencia where depe_estado=1 $where_areas and inst_codi=".$_SESSION["inst_codi"]." order by 1 asc";
    $rs = $db->conn->Execute($sql);
    echo $rs->GetMenu2("txt_depe_codi", "0", "0:&lt;&lt; Todas las &aacute;reas &gt;&gt;", false,"",
                       "id='txt_depe_codi' class='select' onChange=\"reportes_criterios_cargar_combos('A')\"" );
?>
            </div>
            <span style="display: <?if ($_SESSION["usua_perm_estadistica"] == 1) echo "block"; else echo "none";?>;">
                <input type="checkbox" name="chk_areas_dependientes" id="chk_areas_dependientes" value="1"> Consultar &aacute;reas dependientes
            </span>
        </td>
    </tr>

    <!-- Usuario -->
    <tr id="tr_txt_usua_codi" style="display: none;">
        <td class="titulos5" valign="middle">Usuario:</td>
        <td class="listado5" valign="middle">
            <div id="div_combo_usuarios">
<?
    $sql = "select coalesce(usua_apellido,'')||' '||coalesce(usua_nomb,'')||case when usua_esta=0 then ' (Inactivo)' else '' end as usr_nombre, usua_codi
            from usuario where usua_codi>0 $where_areas and inst_codi=".$_SESSION["inst_codi"]." order by 1 asc";
    $rs = $db->conn->Execute($sql);
    echo $rs->GetMenu2("txt_usua_codi", "0", "0:&lt;&lt; Todos los usuarios &gt;&gt;", false,""," id='txt_usua_codi' class='select'" );
?>
            </div>
        </td>
    </tr>

</table>
<script type="text/javascript">reportes_criterios_cargar_criterios();</script>
