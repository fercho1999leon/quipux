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
session_start();
$ruta_raiz = "..";
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/funciones.php";
include_once "$ruta_raiz/funciones_interfaz.php";

echo "<html>".html_head();
include_once "$ruta_raiz/js/ajax.js";

$paginador = new ADODB_Pager_Ajax($ruta_raiz, "div_buscar_documentos", "incluir_doc_archivo_paginador.php", "txt_radi_nume");

?>

<script>
    function markAll()
    {
        if(document.form1.elements['checkAll'].checked)
            for(i=1;i<document.form1.elements.length;i++)
                document.form1.elements[i].checked=1;
        else
            for(i=1;i<document.form1.elements.length;i++)
                document.form1.elements[i].checked=0;
    }

    function archivar_documento()
    {
        sw=0;
        for(i=1;i<document.form1.elements.length;i++)
            if (document.form1.elements[i].checked)
                sw=1;
        if (sw==0) {
            alert ("Debe seleccionar por lo menos un registro.");
            return;
        }
        document.form1.action="incluir_doc_archivo_tx.php"
        document.form1.submit();
    }

    function buscar_documentos () {
        document.getElementById('div_buscar_documentos').innerHTML = 'Por favor espere mientras se realiza la b&uacute;squeda.<br>&nbsp;<br>' +
                                                           '<img src="<?=$ruta_raiz?>/imagenes/progress_bar.gif"><br>&nbsp;';
        paginador_reload_div('');
    }

    function VerUbicacion(arch_codi, radi_nume, arch_path) {
        windowprops = "top=100,left=100,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=600,height=400";
        URL = 'ver_ubicacion_fisica.php?arch_codi='+arch_codi+'&radi_nume='+radi_nume+'&arch_path='+arch_path;
        window.open(URL , "ubicacion", windowprops);
    }

</script>

<body>
  <center>
  <form name="form1" method="post" action="javascript:buscar_documentos()">
    <table width="99%" border="1" align="center" class="t_bordeGris">
        <tr>
            <td class="titulos4" colspan="2"><center><b>Ubicar Documentos en el Archivo F&iacute;sico</b></center></td>
        </tr>
    </table>
    <table width="99%" border="0" cellpadding="0" cellspacing="5" class="borde_tab">
        <tr>
            <td class="titulos1">
                Buscar <?=$descRadicado?>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <input name="txt_radi_nume" id="txt_radi_nume" type="text" size="60" class="tex_area" value="">
                &nbsp;&nbsp;&nbsp;&nbsp;
                <input type="submit" value="Buscar" name="btn_buscar" class='botones'>
            </td>

        </tr>
    </table>
    <br>
    <div id="div_buscar_documentos" style="width: 99%"></div>
    <br>
    <table width="100%" cellspacing="5">
        <tr>
            <td align="center">
                <input type="button" name="btn_archivar" value="Archivar en" class="botones_largo" onClick="archivar_documento();"
                       title="Se va ha seleccionar el archivo físico en donde se va(van) ha archivar el(los) documentos seleccionado(s)">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="button" name="btn_cancelar" value="Regresar" class="botones_largo" onClick="window.location='menu_archivo.php';"
                       title="Regresar a la pantalla anterior">
            </td>
        </tr>
    </table>
  </form>
  </center>
</body>
</html>
