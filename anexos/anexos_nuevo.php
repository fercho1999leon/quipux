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
$ruta_raiz= "..";
include_once "$ruta_raiz/config.php";

$asociar_imagen = (isset ($_POST["chk_asociar_imagen"])) ? (0 + $_POST["chk_asociar_imagen"]) : 0;
$asocImgRad = (isset($_POST["asocImgRad"])) ? "<input type='hidden' name='asocImgRad' id='asocImgRad' value='".$_POST["asocImgRad"]."'>" : "";

?>
<div id="div_anexos_estado" style="display:none"></div>
<b>Puede subir archivos con un tamaño máximo de: <?=str_replace('M','',ini_get('upload_max_filesize'));?> MB</b>
<form id="frm_anexos_cargar_nuevo_archivo" method="post" enctype="multipart/form-data" action="" target="ifr_anexos_cargar_nuevo_archivo">
    <input type="hidden" name="txt_radi_nume" id="txt_radi_nume" value="">
    <?=$asocImgRad?>
    <table width="100%" align="center" border="0" cellpadding="0" cellspacing="3" class="borde_tab">
        <tr><th width="40%">Archivo</th><th width="45%">Descripci&oacute;n</th><th width="15%">Medio de Almacenamiento</th></tr>
<?php
    for ($i=0 ; $i<10 ; ++$i) {
?>
        <tr id="tr_archivo_nuevo_<?=$i?>" <? if ($i>0) echo "style='display: none;'";?> class="listado<?=($i%2)+1?>">
            <td align="left" style="vertical-align: middle;">
                &nbsp;&nbsp;&nbsp;&nbsp;
                <input type="file" name="fil_archivo_nuevo_<?=$i?>" id="fil_archivo_nuevo_<?=$i?>" onchange="anexos_validar_tipo_archivo('<?=$i?>')"/>
                &nbsp;
                <span id="lbl_archivo_nuevo_<?=$i?>"></span>
                <span style="position: relative; right: 0px; float: right;">
                    <img src='<?=$nombre_servidor?>/iconos/trash.png' id="img_archivo_nuevo_borrar_<?=$i?>" alt='X' title='Eliminar archivo'
                         style='width: 23px; height: 23px; display: none;' onclick="fjs_anexos_borrar_archivo_nuevo('<?=$i?>')">
                    &nbsp;&nbsp;
                </span>
            </td>
            <td align="center">
                <textarea name="txt_descripcion_nuevo_<?=$i?>" class="tex_area" cols="65" rows="1"></textarea>
                <?php if ($asociar_imagen == 1) { ?>
                    <br/>
                    <input type="checkbox" name="chk_asociar_imagen_<?=$i?>" id="chk_asociar_imagen_<?=$i?>" value="1" 
                           class="ebutton" onchange="fjs_anexos_validar_chk_asociar_imagen(<?=$i?>)">
                    <span class="leidos">¿Desea colocar este archivo como imagen del documento?</span><br/>
                <? } ?>
            </td>
            <td>
                <input type="radio" name="chk_medio_nuevo_<?=$i?>" value="0" checked>Electr&oacute;nico<br/>
                <input type="radio" name="chk_medio_nuevo_<?=$i?>" value="1">F&iacute;sico
            </td>
        </tr>
<? } ?>
        <tr>
            <td colspan="3" class="listado1" align="center">
                <br>
                <input type="button" name="btn_anexo" value="Grabar Anexos" class="botones_largo" onClick="anexos_cargar_archivo_nuevo()">
                <br>&nbsp;
            </td>
        </tr>
    </table>
    <iframe name="ifr_anexos_cargar_nuevo_archivo" id="ifr_anexos_cargar_nuevo_archivo" src="" width="400" height="100" style="display: none;"></iframe>
</form>