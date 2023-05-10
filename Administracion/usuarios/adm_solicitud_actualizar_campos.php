<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$ruta_raiz = "../..";
session_start();

include_once "$ruta_raiz/rec_session.php";

include_once "$ruta_raiz/obtenerdatos.php";

include_once "$ruta_raiz/funciones_interfaz.php";


if (isset($_GET)){    
    $tipo=$_GET['tipo'];
    
    if ($tipo=='a'){//ejecuta cuando borra un anexo
        //buscar archivos para actualizar el campo acuerdo
        $path_file = $ruta_raiz.'/bodega/ciudadanos/'.$_SESSION["usua_codi"].'_acuerdo.pdf.p7m';        
        
        $sql = "update solicitud_firma_ciudadano";        
         
            if (file_exists($path_file))
              $sql.=" set sol_acuerdo = 1";
            else//no se subio forzado update acuerdo = 0
                $sql.=" set sol_acuerdo = 0";       
         $sql.= " where ciu_codigo = ".$_SESSION["usua_codi"];        
         $db->query($sql);
         
         echo ver_datos_solicitud($db);
           
         
       
    }elseif($tipo=='b'){
        $sql.= "update solicitud_firma_ciudadano set sol_acuerdo=0 where ciu_codigo = ".$_SESSION["usua_codi"];        
         $db->query($sql);
    }else
        echo ver_datos_solicitud($db);
  
}
function ver_datos_solicitud($db){
    $datos = array();
    $sql = "select * from solicitud_firma_ciudadano where ciu_codigo=".$_SESSION["usua_codi"]."";
         //echo $sql;
         $rs2 = $db->conn->query($sql);
         $acuerdo_sol = $rs2->fields['SOL_ACUERDO'];
         
         $estado_sol = $rs2->fields['SOL_ESTADO'];  
         echo "&nbsp;";
        
        
              
             if ($acuerdo_sol==1 and ($estado_sol==0 or $estado_sol==1))//sol rechazado o en edicion
                echo '<input name="btn_enviar" type="submit" class="botones_largo" title="Enviar" value="Enviar" readonly="<?=$estado_read?>" onclick="return ValidarInformacion(2);"/>';
             if ($estado_sol==0 || $estado_sol==1)
                 echo '<br>&nbsp;<br><font color="black">Su solicitud de Firma aún no ha sido enviada.</font>';
         
}
?>

