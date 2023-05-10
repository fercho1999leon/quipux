<?
/*	
* Modificado por		Iniciales		Fecha (dd/mm/aaaa)
*	DAVID GAMBOA    	SC			12/11/2011
* 
*/
$ruta_raiz = "../..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

session_start();
if($_SESSION["usua_admin_sistema"]!=1) die("");
include_once "$ruta_raiz/rec_session.php";

$error = "";
//Permite grabar el area en la base de datos con todos sus atributos
if (isset($_GET)){    
    if ($_GET['mostrar_boton']==1)
        echo "<input type='button' value='GUARDAR PERMISOS' name='btn_generar' class='botones_largo' onClick='guardar();'/>";
    else
        echo "<input type='button' value='ELIMINAR PERMISOS' name='btn_generar' class='botones_largo' onClick='eliminar();'/>";
}