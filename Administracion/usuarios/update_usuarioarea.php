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
if($_SESSION["usua_admin_sistema"]!=1) die("");
$ruta_raiz = "../..";
include_once "$ruta_raiz/rec_session.php";

$db = new ConnectionHandler("$ruta_raiz");

$path_to_classes = "class_control/";
//echo $path_to_classes;

//////////////////////////////////////
//                                  //
//     A partir de aqu� no tocar    //
//                                  //
//////////////////////////////////////

//define ( "ABS_PATH", "/var/www/orfeo" ."/" );
//define ( "REL_CLASS_PATH", $path_to_classes );
//define ( "ABS_CLASS_PATH", ABS_PATH . REL_CLASS_PATH );
//echo (ABS_CLASS_PATH . "JSON.php");
if ( empty($_POST) ) exit( "Error, no se han pasado variables" );

$clazz = $_POST["__class"];
$action = $_POST["__action"];

//require_once ( ABS_CLASS_PATH . "JSON.php" );
require_once "$ruta_raiz/class_control/JSON.php";
//$clazz = "usuarios";
//require_once ( ABS_CLASS_PATH . $clazz . ".php" );
require_once "$ruta_raiz/class_control/". $clazz . ".php";
//echo (ABS_CLASS_PATH . "JSON.php");
//$clazz = $_POST["__class"];
//$clazz = "usuario";
//$action = $_POST["__action"];
//$db = new ConnectionHandler("/var/www/orfeo_multiempresa/");
//$instance = new $clazz ($db);
//var_dump($instance );
//$result = $instance->$action ( $_POST );
$instance = new $clazz ($db);
$result = $instance->$action ( $_POST );
//var_dump( $_POST);
$json = new Services_JSON();

$res = $json->encode($result);
header ( 'X-JSON:('. $res .')' );

?>
