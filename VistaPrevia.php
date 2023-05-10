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
    //Manejo de Sessiones
    session_start();
    $ruta_raiz = ".";
    include_once "$ruta_raiz/rec_session.php";

    //se incluyo por register_globals
    $verrad = limpiar_numero($_GET['verrad']);
    $archivo= limpiar_sql($_GET['archivo']);
    $textrad = limpiar_sql($_GET['textrad']);

    //$ruta_raiz = ".";
    include "$ruta_raiz/plantillas/generar_documento.php";
    include "$ruta_raiz/plantillas/GenerarDocumento.php";
    $doc = New GenerarDocumento($db);
    /*if (trim($archivo)=="")
        $archivo = $doc->GenerarPDF($verrad,"no");
    else
        $archivo = str_replace(".p7m","",$archivo);
    */
    if (trim($archivo)==""){
        $archivo = $doc->GenerarPDF($verrad,"no");
       
        if ($archivo=='')
        $archivo = GenerarPDF($verrad,"no",".");
        $archivo = str_replace(".p7m","",$archivo);        
    }
    else
        $archivo = str_replace(".p7m","",$archivo);
    if (!$nombre_archivo) {
        $tmp = explode("/",$archivo);
        $nombre_archivo = $tmp[count($tmp)-1];
        if (isset($textrad)){
            $nombre_archivo = $textrad . substr($nombre_archivo,strpos($nombre_archivo,"."));
        }
    }

    $path_arch = "$ruta_raiz/bodega".$archivo;
    //$cuenta=strlen($nombre_archivo);
        $ext = substr($nombre_archivo,-4);
        if ($ext == '.p7m')
        $nombre_archivo = str_replace(".p7m","",$nombre_archivo);
    $mime = get_mime_tipe($nombre_archivo);
    
    if($mime)
        header( "Content-Disposition: attachment; filename=".$nombre_archivo);
    else
        header( "Content-Disposition: attachment; filename=".$textrad.".pdf");

	header( "Content-Length: ".filesize($path_arch));

    if($mime)
        header("Content-Type: $mime");
    else
        header("Content-Type: application/pdf");
        
	header("Content-Transfer-Encoding: binary");

    //echo $archivo;
    readfile($path_arch);
    //$path_descarga = "$ruta_raiz/archivo_descargar.php?path_arch=$archivo&nomb_arch=$nombre_archivo";

/*include_once "./interconexion/wsCliente.php";
$ok=ws_envio_radicado("1711311074", "Prueba ws3", "prueba.pdf", $archivo);
var_dump($ok);

include_once "./interconexion/wsCliente2.php";
$tmp=ws_get_archivo();
$ok=ws_set_archivo($tmp);
var_dump($ok);
*/
/*
?>
--<html>
<head>
<title>Vista Previa del Documento</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="estilos/orfeo.css">
</head>
<body>
    <!--<br/>
    <script>window.open(<?="'".str_replace(".p7m","",$path_descarga)."'"?>,'','');</script>
    <table border=2 cellspace=2 cellpad=2 WIDTH=70%  class="t_bordeGris" id=tb_general align="center">
	<tr><td colspan="2" align="center" class="titulos4">VISTA PREVIA DEL DOCUMENTO</td></tr>
	<tr>
	    <td colspan="2" align="center" class="listado2">
		<center><a href="javascript:window.open('<?=str_replace('.p7m','',$path_descarga)?>','_self','');" class='vinculos'>Ver Documento</a></center>
	    </td>
	</tr>
    </table>
    <br/><br/>
    <center><input type='button' onClick='window.close();' name='cerrar' value="CERRAR VENTANA" class="botones_largo"></center>
    -->
    <script>
        //alert(<?="\"".str_replace(".p7m","",$path_descarga)."\""?>);
        //window.location = <?="'".str_replace(".p7m","",$path_descarga)."'"?>;
        //var x = (screen.width - 10) / 2;
        //var y = (screen.height - 10) / 2;
        //ventana = window.open(<?="'".str_replace(".p7m","",$path_descarga)."'"?>,'','');
        //ventana.moveTo(x, y);
        //ventana.focus();
        setTimeout('cerrar_ventana()',2000);

        function cerrar_ventana()
        {
            window.close();
        }
    </script>
</body>
</html>*/?>