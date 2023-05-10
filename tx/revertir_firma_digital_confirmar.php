<?
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
if ($_SESSION["usua_codi"] != "0") die ("");
require_once "$ruta_raiz/funciones.php";
include_once "$ruta_raiz/obtenerdatos.php";

if (!isset($_POST["valRadio"])) die ("<script>history.back();</script>");
$radi_nume = limpiar_sql($_POST["valRadio"]);

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

$firma = ObtenerCampoRadicado("radi_fech_firma",$radi_nume,$db);

$accion = "Volver a generar archivo PDF.";
if (trim($firma) != "")
    $accion = "Revertir firma digital del documento. ";




?>
<script type="text/javascript" >
    function revertir_firma_digital() {
        if (document.getElementById('txt_observacion').value == '') {
            alert ('Por favor ingrese un justificativo para realizar esta acción.')
            return;
        }
        if (confirm('¿Está seguro de realizar esta acción?')) {
            formulario.action = 'revertir_firma_digital_grabar.php';
            document.formulario.submit();
            return;
        }
        return;
    }

    function ver_documento(path_archivo , nombre_archivo) {
        path_descarga = '<?=$ruta_raiz?>/archivo_descargar.php?path_arch=' + path_archivo + '&nomb_arch=' + nombre_archivo;
        document.getElementById('ifr_descargar_archivo').src=path_descarga;
    }

</script>
<body>
  <br/>
  <center>
    <form name="formulario" method="post" action="">
        <input type="hidden" name="txt_radi_nume" id="txt_radi_nume" value="<?=$radi_nume?>">
        <table border="0" cellpadding="0" cellspacing="5" class="borde_tab" width="98%">
            <tr>
                <td class="titulos4" colspan="2" align='center'>Acci&oacute;n: <?=$accion?></td>
            </tr>
            <tr>
                <td width='25%' align='right' valign='middle'>
                    <b>Comentario: &nbsp;</b>
                </td>
                <td width='75%' class="listado1" align='left' valign='middle'>
                    
                    <textarea name="txt_observacion" id="txt_observacion" cols="70" rows="3" class="ecajasfecha"></textarea>
                </td>
            </tr>
            <tr>
                <td class="listado1" align='center' valign='top' colspan="2">
                    <br>
                    <input type='button' name="btn_accion" value='Aceptar' onClick="revertir_firma_digital()" class='botones'>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type='button' name="btn_accion" value='Regresar' onClick="history.back();" class='botones'>
                    <br><br>
                </td>
            </tr>
        </table>
        <br>

      <?
        $isql = "select -- revertir firma digital_confirmar
                case when substr(radi_nume_radi::text,20,1)='0' then '' else '&nbsp;&nbsp;<b>&rarr;</b>&nbsp;' end || radi_nume_text as \"SCR_No. Documento\"
                ,'ver_documento(\"'||radi_path||'\",\"'||radi_nume_text||'.pdf\");' as \"HID_F1\"
                ,substr(radi_fech_ofic::text,1,19) as \"Fecha Documento\"
                ,radi_cuentai as \"No. de Referencia\"
                ,radi_asunto  as \"Asunto\"
                ,usua_nombre AS \"Usuario Actual\"
                ,depe_nomb as \"Área Actual\"
                ,ver_usuarios(radi_usua_rem,',<br>') AS \"De\"
                ,ver_usuarios(radi_usua_dest,',<br>') AS \"Para\"
                ,trad_descr as \"Tipo de Documento\"
                ,CASE WHEN radi_fech_firma is not null THEN 'SI' ELSE 'NO' END as \"Firma Digital\"
                from (
                    select b.radi_nume_text, b.radi_fech_ofic, b.radi_nume_radi, b.radi_cuentai, b.radi_asunto
                        , coalesce(u.usua_nomb,'') || ' ' || coalesce(u.usua_apellido,'') as usua_nombre, d.depe_nomb
                        , b.radi_usua_rem, b.radi_usua_dest, t.trad_descr, b.radi_fech_firma, b.radi_path
                    from (
                        select r.radi_nume_text, r.radi_fech_ofic, r.radi_nume_radi, r.radi_cuentai, r.radi_asunto, r.radi_usua_actu,
                            r.radi_usua_rem, r.radi_usua_dest, r.radi_fech_firma, r.radi_tipo, r.radi_fech_radi, r.radi_path
                        from radicado r
                        where r.radi_nume_temp=$radi_nume
                    ) as b
                    left outer join usuarios u on b.radi_usua_actu=u.usua_codi
                    left outer join dependencia d on u.depe_codi=d.depe_codi
                    left outer join tiporad t on b.radi_tipo=t.trad_codigo
                    order by b.radi_fech_radi *LIMIT**OFFSET*
                ) as a";

        $pager = new ADODB_Pager($db,$isql,'adodb', false,1,"");
        $pager->toRefLinks = $linkPagina;
        $pager->toRefVars = $encabezado;
        $pager->checkAll = false;
        $pager->checkTitulo = false;
        $pager->Render($rows_per_page=20,$linkPagina,$checkbox=chkAnulados);
?>
    </form>
    <iframe  name="ifr_descargar_archivo" id="ifr_descargar_archivo" style="display: none" src="">
          Su navegador no soporta iframes, por favor actualicelo.</iframe>

  </center>
</body>
</html>
