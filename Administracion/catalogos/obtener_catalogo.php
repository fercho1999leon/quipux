<?php


$ruta_raiz = "../..";
$ruta_raiz2= "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
//if($_SESSION["usua_admin_sistema"]!=1) die("");

require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/obtenerdatos.php";

if (isset($_POST["codigo"]))
$id_codigo = 0+ limpiar_numero($_POST['codigo']);
else
    $id_codigo=0;


 
$sql="select id_padre,nombre from ciudad where id = $id_codigo";

$rsCmbPais = $db->conn->Execute($sql);
if (!$rsCmbPais->EOF){    
        $codigo = $rsCmbPais->fields["ID_PADRE"];  
        
        //echo '<input type="hidden" name="txt_id_padre" id="txt_id_padre" value="'.$codigo.'" size="20">';
   }//if

   if ($codigo=='')
       $codigo = 0;
       
           $sql="select nombre, id from ciudad";
           if ($codigo!='')
           $sql.=" where id not in ($id_codigo)";
           $sql.=" order by nombre asc";
           
            $rsCiudad=$db->conn->query($sql);
            //print_r($rsCiudad);
            if($rsCiudad) print $rsCiudad->GetMenu2("txt_id_padre", $codigo, "0:&lt;&lt; Seleccione &gt;&gt;", false,"","class='select' id='txt_id_padre'  style='width: 300px;'");
                
   

?>