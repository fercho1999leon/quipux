<?php
$ruta_raiz = "../..";
session_start();
if ($_SESSION["usua_admin_sistema"] != 1) {
    die("SIN SESION DE ADMINISTRADOR");
}

require_once("$ruta_raiz/funciones.php"); 
p_register_globals(array(
    '_POST' => array(
        'depe_destino',
        'read2',
        'usr_destino'
    ),
    '_SESSION' => array(
        'inst_codi'
    )
));

if ($depe_destino == 'TODOS') {
    $where = "where inst_codi = " . $inst_codi ;
} else {
    $where = "where depe_codi = " . $depe_destino;
}

include_once "$ruta_raiz/rec_session.php";
$sql = "select usua_nombre, usua_codi from usuario $where order by usua_apellido asc";
$rs = $db->conn->Execute($sql);
echo $rs->GetMenu2("usr_destino", $usr_destino, "0:&lt;&lt seleccione &gt;&gt;", false,"","class='select' $read2");
?>
