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
//////////////   ANEXOS   ////////////////

session_start();
if (!isset ($ruta_ajax)) $ruta_ajax = ".";
if ($ruta_ajax != "."  and $ruta_ajax != "..") $ruta_ajax = ".";

$ruta_raiz = ".";

if (str_replace("/","",str_replace(".","",$ruta_raiz))!="")
    die ("<br/><center><font size='6' color='red'><b>HA SIDO DETECTADO UN INTENTO DE VIOLACI&Oacute;N DE LAS SEGURIDADES DEL SISTEMA
	<br/>SU N&Uacute;MERO IP SER&Aacute; BLOQUEDO PERMANENTEMENTE</b></font>");
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/funciones.php";

$radi_temp = limpiar_sql($_GET['radi_temp']);
$nivel_seguridad_documento = 0+$_GET["nivel"];

$mensaje="Archivos anexos al documento";
if (substr($radi_temp,-1)=="1") $mensaje="Archivos adicionales anexos al documento";
$SinAnexos="<span class='listado1'><b><br>El documento actual no tiene archivos adjuntos<br></b></span>";

?>

    <table WIDTH="100%" align="center" border="0" cellpadding="0" cellspacing="5" class="borde_tab">
        <tr>
            <td height="25" class="titulos4" colspan="7"><?=$mensaje?></td>
        </tr>
        <tr>
            <td width='20%'  class="titulos2">ARCHIVO</td>
            <td width='5%'  class="titulos2">TAMA&Ntilde;O</td>
            <td width='25%' class="titulos2">DESCRIPCI&Oacute;N</td>
            <td width='10%' class="titulos2">FECHA</td>
            <td width='8%' class="titulos2">CREADOR</td>
            <td width='8%' class="titulos2">MEDIO</td>
            <td width='24%' class="titulos2">ACCI&Oacute;N</td>
        </tr>

<?
$flag=false;
if ($fl==1)
    $flag=true;

if($radi_temp) {	/////////////	SI YA EXISTIAN DOCUMENTOS	////////////////////

    $isql = "select anex_codigo, anex_nombre, anex_desc, anex_path as archivo
		,anex_tamano ,usua_nombre ,anex_fecha, anex_fisico
		from anexos left outer join usuario on anex_usua_codi=usua_codi
		where anex_radi_nume=$radi_temp and anex_borrado='N'
		order by anex_codigo";

//echo "<hr>$isql";
    $rs=$db->conn->query($isql);
    if ($rs->EOF) {
        die ("<tr><td class='listados1' align='center' colspan='7'>El documento no tiene archivos anexos.</td></tr></table><br>");
    }
//var_dump($rs);
    while(!$rs->EOF)
    {
	$coddocu = $rs->fields["ANEX_CODIGO"];
	$nombre = str_replace(" ","_",trim(strtolower($rs->fields["ANEX_NOMBRE"])));
	$linkarchivo = trim(strtolower($rs->fields["ARCHIVO"]));
	$url_archivo = trim(strtolower($rs->fields["ARCHIVO"]));
	$tipo = substr($linkarchivo,-3);
	$descripcion = $rs->fields["ANEX_DESC"];
	$fecha = $rs->fields["ANEX_FECHA"];
	$usuario = $rs->fields["USUA_NOMBRE"];
	$tamano = $rs->fields["ANEX_TAMANO"];
	$fisico = $rs->fields["ANEX_FISICO"];
?>
	<tr>
	    <td><font size=1>
            <?
                if ($nivel_seguridad_documento>2) {
                    $path_descarga = "$ruta_ajax/archivo_descargar.php?path_arch=$linkarchivo&nomb_arch=".str_replace(" ","_",$nombre);
                    echo "<a href=\"javascript:void(0);\" onclick=\"window.open('$path_descarga','_self','');\" class=vinculos>$nombre</a>";
                } else {
                    echo $nombre;
                }
            ?>
	    </font></td>
	    <td><font size=1> <?=$tamano?></font></td>
	    <td><font size=1> <?=$descripcion?></font></td>
            <td><font size=1> <?=substr($fecha, 0,19).$descZonaHoraria?></font></td>
	    <td><font size=1> <?=$usuario?></font></td>
	    <td><font size=1> <?if($fisico==0) echo "Electr&oacute;nico"; else echo "F&iacute;sico"?></font></td>
	    <td align="center"><font size=1>
	<? 	$espacios = "&nbsp;&nbsp;&nbsp;&nbsp;";
		if ($flag) {
		    if ($fisico==0)
		    	echo "<a class=vinculos href=\"javascript:;\" onclick=\"acciones('$coddocu',3)\"
				title='Cambiar el medio de almacenamiento del documento a F&iacute;sico'>F&iacute;sico</a>$espacios";
		    else
		    	echo "<a class=vinculos href=\"javascript:;\" onclick=\"acciones('$coddocu',4)\"
				title='Cambiar el medio de almacenamiento del documento a Electr&oacute;nico'>Electr&oacute;nico</a>$espacios";
		    if (substr($radi_temp,-1)==2)
			echo "<a class=vinculos href=\"javascript:;\" onclick=\"acciones('$coddocu',2)\"
				title='Poner archivo como imagen del documento'>Imagen</a>$espacios";
		    echo "<a class=vinculos href=\"javascript:;\" onclick=\"acciones('$coddocu',1)\" title='Borrar Anexo'>Borrar</a>$espacios";
		}
		if ($tipo=="p7m" and $nivel_seguridad_documento>2)
		    echo "<a class=vinculos href=\"javascript:;\" onclick=\"verificar_firma('$url_archivo','$nombre')\"
			title='Verificar la firma digital del documento'>Verificar_Firma</a>";
	?>
	    </font></td>
	</tr>

<?
	$rs->MoveNext();
    }	//fin del while
}	//fin del if

?>
</table>
<br>