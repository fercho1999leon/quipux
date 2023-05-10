<?php
/**  Programa para el manejo de gestion documental, oficios, memorandos, circulares, acuerdos
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
if ($_SESSION["usua_codi"] != "0") die ("");
include_once "$ruta_raiz/funciones.php";
include_once "$ruta_raiz/funciones_interfaz.php";

echo "<html>".html_head();
include_once "$ruta_raiz/js/ajax.js";

$paginador = new ADODB_Pager_Ajax($ruta_raiz, "div_buscar_documentos", "revertir_firma_digital_busqueda.php", "txt_nume_documento");
?>

<script language="JavaScript" type="" >
    function realizar_busqueda() {
        paginador_reload_div();
    }

    function validar_documento_seleccionado() {
        try {
            j = formulario.valRadio.length || 0;
            if (j==0) return formulario.valRadio.checked
            for (i=0; i<j; ++i) {
                if (formulario.valRadio[i].checked)
                    return true;
            }
        } catch (e) {}
        return false;
    }

    function revertir_firma_digital() {
        if (validar_documento_seleccionado()) {
            formulario.action = 'revertir_firma_digital_confirmar.php';
            document.formulario.submit();
            return;
        }
        alert ('Por favor seleccione un documento.');
        return;
    }
</script>

<body>
    <div id="spiffycalendar" class="text"></div>

    <center>
    <form name="formulario" method="post" action="javascript:realizar_busqueda();">
        <table width="90%" class="borde_tab">
            <tr>
                <td class="titulos4" colspan="3"><center><b>B&uacute;squeda Avanzada de Documentos</b></center></td>
            </tr>
            <tr>
                <td width="25%" class="titulos2">No. <?=$descRadicado?>:</td>
                <td width="50%" class="listado2">
                    <input type="text" name="txt_nume_documento" id="txt_nume_documento" class="tex_area" value="<?=$txt_nume_documento?>"
                            size="70" title="Ingrese el n&uacute;mero o parte del n&uacute;mero del documento.">
                </td>
                <td width="25%" class="titulos2" align="center">
                    <input type="button" name="btn_buscar" class="botones" value="Buscar" onclick="realizar_busqueda();">
                </td>
            </tr>
        </table>
        <br>
        <input type="button" name="btn_aceptar" class="botones_largo" value="Aceptar" onclick="revertir_firma_digital();">
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="button" name="btn_aceptar" class="botones_largo" value="Regresar" onclick="window.location='../Administracion/formAdministracion.php';">
        <br><br>
        <div id='div_buscar_documentos' style="width: 99%"></div>
    </form>
    </center>
</body>
</html>