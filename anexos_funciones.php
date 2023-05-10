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
if(!isset($ruta_raiz)) $ruta_raiz = ".";
if (str_replace("/","",str_replace(".","",$ruta_raiz))!="")
    die ("<br/><center><font size='6' color='red'><b>HA SIDO DETECTADO UN INTENTO DE VIOLACI&Oacute;N DE LAS SEGURIDADES DEL SISTEMA
	<br/>SU N&Uacute;MERO IP SER&Aacute; BLOQUEDO PERMANENTEMENTE</b></font>");
session_start();
require_once "$ruta_raiz/rec_session.php";

include_once "$ruta_raiz/anexos_grabar.php";
include_once "$ruta_raiz/include/tx/Tx.php";
include_once "$ruta_raiz/funciones.php";

$hist = new Historico($db);

$usua_codi = $_SESSION["usua_codi"];
   //se incluyo por register_globals
$nurad = $_GET['nurad'];
$verrad = $_GET['verrad'];
$textrad = $_GET['textrad'];
$flag_error = true; // Valida cuando se suben archivos muy grandes

//////////////////////   GRABAR ANEXOS    /////////////////////////
for($i=1;$i<=10;$i++)
{
//  if (!isset($_POST["chk_fisico$i"])) $fisico = 0; else $fisico = 1;
    $fisico = $_POST["chk_fisico$i"];
    if (!isset($_POST["chk_imagen$i"])) $imagen = 0; else $imagen = 1;
    $userfile = "userfile" . $i;
    $nombarch = "nombarch" . $i;
    $descarch = "descarch" . $i;

    // las siguientes lineas se incluyo por register globals
    $nomb_arch_new = $_POST["nombarch$i"];
    $userfile_new  = $_FILES["userfile$i"]['tmp_name'];
    //$descarch_new  = $_POST["descarch$i"];
    $clean["descarch$i"] = trim ( limpiar_sql ( $_POST["descarch$i"] ) ) ;

    //$nomb_arch_new=explode("/",$nomb_arch_new);

    if ($nomb_arch_new != "") {
        $flag_error = false;
        $nomb_arch=explode("/",$nomb_arch_new);
        $nomb_arch=explode("\\",$nomb_arch[count($nomb_arch)-1]);
        $mens_hist = $nomb_arch[count($nomb_arch)-1];
        $ok=GrabarAnexo($db, $nurad, $userfile_new, $nomb_arch_new, $clean["descarch$i"], $usua_codi, $ruta_raiz, $fisico, $imagen);
        if($ok==1) {
            $hist->insertarHistorico($nurad, $usua_codi, $usua_codi, $mens_hist, 66); //Anexar Archivo
        }
    }
}

if ($flag_error) echo "<script>alert('No se pudo subir el archivo.\\nPor favor verifique el tamaño del mismo.');</script>";

$var_envio="$ruta_raiz/verradicado.php?verrad=$nurad&menu_ver=2";

?>
<html>
<head>
<meta http-equiv="refresh" content="0; url=<?=$var_envio?>">
</head>
</html>
