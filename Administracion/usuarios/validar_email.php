<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$ruta_raiz = "../..";
include_once "$ruta_raiz/funciones.php";

$email = limpiar_sql($_POST["txt_email"]);
if (validar_mail($email)) echo "OK";
else echo "ERROR";
?>
