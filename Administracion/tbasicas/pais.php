<?php


$ruta_raiz = "../..";
$ruta_raiz2= "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
//if($_SESSION["usua_admin_sistema"]!=1) die("");

require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/obtenerdatos.php";

if (isset($_GET["code"]))
$id_codigo = 0+ limpiar_numero($_GET['code']);
else
    $id_codigo=0;
if ($id_codigo!=0){    
$sql="select nombre, id from ciudad where id_padre = $id_codigo";
//echo $sql;

$rsCmbPais = $db->conn->Execute($sql);
if (!$rsCmbPais->EOF){
    while(!$rsCmbPais->EOF){
        $codigo = $rsCmbPais->fields["ID"];
        $nombre = $rsCmbPais->fields["NOMBRE"];
        $registros[$codigo]=$nombre;
        $rsCmbPais->MoveNext();
    }
    foreach($registros as $key=>$value)
    {
                    echo "<option value=\"$key\">$value</option>";
    } 
   }//if
}
?>