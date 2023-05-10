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

$ruta_raiz = "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/obtenerdatos.php";
include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();
include_once "$ruta_raiz/js/ajax.js";

$verrad = limpiar_numero($_POST["valRadio"]);
$datosrad = ObtenerDatosRadicado($verrad,$db);
$usr_actual = ObtenerDatosUsuario($datosrad["usua_actu"],$db);

?>
<center>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td valign="bottom" align="left" id="btn_img" width="2%">
        <img name="principal_r4_c3" src="<?=$ruta_raiz?>/imagenes/internas/principal_r4_c3.gif" width="25" height="51" border="0" alt="">
    </td>
    <td valign="bottom" align="left" id="btn_imgRegresar" width="2%">
        <img src="<?=$ruta_raiz?>/imagenes/internas/regresar.gif"     id="Image1"  border="0" alt="Regresar" onmouseover="document.getElementById('Image1o').style.display=''; this.style.display='none';">
        <img src="<?=$ruta_raiz?>/imagenes/internas/overRegresar.gif" id="Image1o" border="0" alt="Regresar" onmouseout ="document.getElementById('Image1').style.display='';  this.style.display='none';" style="display: none;" onclick="history.back();">
    </td>
    <td valign="bottom" align="left" id="btn_img" width="2%">
        <img name="principal_r4_c4" src="<?=$ruta_raiz?>/imagenes/internas/principal_r4_c4.gif" width="25" height="51" border="0" alt="">
    </td>
    <td width="98%">&nbsp;</td>
  </tr>
</table>
<table width="100%" border="0" cellpadding="0" cellspacing="1" >
    <tr>
        <td class="titulos4" width="40%">No. Documento: &nbsp;&nbsp;&nbsp;&nbsp;<?=$datosrad["radi_nume_text"] ?></td>
        <td class="titulos4" align="left" width="35%">&nbsp;&nbsp;Usuario actual: &nbsp;&nbsp;&nbsp;&nbsp;<?=$usr_actual["nombre"]?></td>
        <td class="titulos4" align="left" width="25%">&nbsp;&nbsp;<?=$descDependencia?> actual: &nbsp;&nbsp;&nbsp;&nbsp;<?=$usr_actual["dependencia"]?></td>
    </tr>
</table>
<br>
<table width="98%" border="0" cellpadding="0" cellspacing="0" >
    <tr>
        <td class="listado2">
            <center>
                <table width="94%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td valign="bottom" align="left"><img src="<?=$ruta_raiz?>/imagenes/documentos_R.gif" alt="Anexos" border="0" width="110" height="25"></td>
                    </tr>
                    <tr>
                        <td class="listado1">
<?
include_once "$ruta_raiz/anexos/anexos.php";
?>
                        </td>
                    </tr>
                </table>
                <br>
            </center>
        </td>
    </tr>
</table>
</center>

        <iframe  name="ifr_descargar_archivo" id="ifr_descargar_archivo" style="display: none;" src="">
            Su navegador no soporta iframes, por favor actualicelo.</iframe>
    </body>
</html>