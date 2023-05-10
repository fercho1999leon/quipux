<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$ruta_raiz = "../..";
    session_start();
    if ($_SESSION["usua_admin_sistema"] != 1) {
    die( html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.") );
}
    
    include_once "$ruta_raiz/rec_session.php";
    include_once "$ruta_raiz/obtenerdatos.php";
    if (isset($_GET)){
        $usuarioR = 0+$_GET["usrResponsable"];
        $sql = "update usuarios set usua_responsable_area = 0, usua_sumilla=lower(usua_sumilla) where usua_codi = ".$usuarioR;       
        $db->conn->query($sql);
        echo "Se elimino con éxito";
     }
?>