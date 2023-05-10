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
/**
* Cierra la session, actualiza fecha en la que el usuario salio del sistema y destruye las variables de 
* session que fueron creadas.
* Se direcciona a la pantalla de login.php.
**/

session_start();
$ruta_raiz = ".";
include_once "$ruta_raiz/config.php";
include_once "$ruta_raiz/include/db/ConnectionHandler.php";
error_reporting(7);

$db = new ConnectionHandler($ruta_raiz);

$fecha = "E'FIN  ".date("Y-m-d H:i:s")."'";
//$db->conn->debug=true;

/*$sql = "update usuarios_sesion 
        set usua_sesion=".$fecha."
        where usua_sesion like '%".substr(session_id(),0,29)."%'";*/

$sql="update usuarios_sesion set usua_sesion=".$fecha." where usua_sesion='".session_id()."'";
if (!$db->conn->query($sql))
{
        echo "<p><center>No pude actualizar<p><br>";
}

//  fin cierre session
foreach ($_SESSION as $key => $value) {
    unset ($_SESSION[$key]);
}
session_destroy();

if (isset($_GET["cerrar_ventana"])) {
    die("<script>window.close();</script>");
}
if(!isset($_GET["accion"])) {
    die (include "./paginaError.php");
}
die (include "./login.php");
?>
