<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
 $ruta_raiz = "..";
  session_start();
  include_once "$ruta_raiz/rec_session.php";
  require_once("$ruta_raiz/funciones.php");
  require_once("$ruta_raiz/obtenerdatos.php");
  if (isset($_POST))
  $radi_nume = trim(limpiar_sql($_POST['radi_nume']));
  
  
  if ($radi_nume)
  $db->query("update radicado set radi_cuentai=null where radi_nume_radi=$radi_nume");
?>
