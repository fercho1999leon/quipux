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
        $dependenciaC = 0+$_GET["dependenciaRes"];
        $existeResponsable = obtenerResponsableArea($dependenciaC,$db,0);
     }
if ($existeResponsable==0){
    ?>
<input type="checkbox" name="usr_area_responsable" id="usr_area_responsable" value="0" onclick="Obtener_val(this)" title="Iniciales del usuario que se visualizará el pie de página (mayùculas) de un documento" <?php echo $usr_responsable_area ."  " .$read; ?>/>Responsable de Área
<?php }else{
   echo "<br> Ya existe responsable de documentación para esta Area.<br>";
   ?><table><tr><td>
    Desactivar como Responsable de Area a: <?=obtenerResponsableArea($dependenciaC,$db,1);?>
    <a class="menu_princ" onclick="eliminarResponsable(<?=$existeResponsable?>,<?=$dependenciaC?>);" href="javascript:void(0);">&nbsp;<img name="quitar" title="Quitar Responsable" alt="Quitar Responsable" src="<?=$ruta_raiz?>/iconos/visto.png" width="20" align="left" border="0"></a>
           </td></tr>

   <?php
}?>