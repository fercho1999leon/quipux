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
if (isset($_GET["cod_ciu"]))
$cod_prov = 0+ limpiar_numero($_GET['cod_ciu']);
else
    $cod_prov=0;

if ($id_codigo!=0){    
$sql="select nombre, id from ciudad where id_padre = $id_codigo";
//echo $sql;
$rsCmbPais = $db->conn->Execute($sql);
echo $rsCmbPais->GetMenu2('cod_prov',$cod_prov,"0:&lt;&lt seleccione &gt;&gt;",false,"","onchange='buscarDep(2);' id='cod_prov' Class='select' $deshabilitar_campos");

}
?>