<?php
$ruta_raiz = "../..";
session_start();
include_once "$ruta_raiz/rec_session.php";
require_once("$ruta_raiz/funciones_interfaz.php");

		// Is there a posted query string?
		if(isset($_POST['queryString'])) {
                    $query = limpiar_sql($_POST['queryString']);
                   $queryString = pg_escape_string(limpiar_sql($query));                        
                        $queryString = strtoupper($queryString);
			
			// Is the string length greater than 0?
			
			if(strlen($queryString) >=2) {
				
                            $sql = "select id,nombre from ciudad where ";
                            $busquedaArr = explode(" ",$queryString);
                            for ($i=0;$i<sizeof($busquedaArr);$i++){
                                   if ($i==0){
                                      if (strlen(trim($busquedaArr[$i]))>=2)
                                       $sql.="translate(upper(nombre),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') like '%' || translate(upper('".$busquedaArr[$i]."'),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') || '%'";  
                                   }
                                  //$sql.= " upper(nombre) like upper('%$busquedaArr[$i]%')";
                                     
                                   else
                                       if (strlen(trim($busquedaArr[$i]))>=2)
                                       $sql.=" or translate(upper(nombre),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') like '%' || translate(upper('".$busquedaArr[$i]."'),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') || '%'";
                                       //$sql.=" or upper(nombre) like upper('%$busquedaArr[$i]%')";
                                }
                            
                                $rs = $db->conn->query($sql);
                            
				echo '<ul id="result">';
					while (!$rs->EOF) {                                           
                                            $ciudad = $rs->fields['ID'];
                                            echo dibujarCiudad($db,$ciudad,$nombre);
                                        $rs->MoveNext();
	         		}
                                echo "</ul>";
				
			} //else {
//				// Dont do anything.
//			} // There is a queryString.
		}
function dibujarCiudad($db,$ciudad,$nombre){
   $sql ="select * from ciudad    
            where id = $ciudad";
   
   $rsDepePadre=$db->conn->query($sql);
   $ciudadHija = $rsDepePadre->fields['NOMBRE'];
   //echo $ciudadHija."<br>";
   $ciudadPadre = $rsDepePadre->fields['ID_PADRE'];
   if ($ciudadPadre!=0){
       dibujarPadre($db,$ciudadPadre,$ciudadHija,$ciudad);
   }else
       $nombre = $nombre.'/'.$ciudadHija;
   if ($nombre!=''){
       $nombre = substr($nombre,1);
       echo '<li onClick="fill(\''.$nombre.'\'); codigoFus(\''.$ciudad.'\')">'.$nombre.'/'.$ciudadHija.'</li>';
   }
   //return $ciudadHija;
  
}
function dibujarPadre($db,$idPadre,$ciudadOri,$codigoSel){
    $sql ="select * from ciudad    
            where id = $idPadre";
    //echo $sql;
   $rsDepePadre=$db->conn->query($sql);
   $ciudadHija = $rsDepePadre->fields['NOMBRE'];
   $ciudadPadre = $rsDepePadre->fields['ID_PADRE'];
   $ciudadOri = $ciudadOri."/".$ciudadHija;
   
   if ($ciudadPadre!=0){
       $ciudadOri = dibujarPadre($db,$ciudadPadre,$ciudadOri,$codigoSel);
   }else{
       $nombre = $nombre.'/'.$ciudadOri;
   }
   if ($nombre!=''){
       $nombre = substr($nombre,1);
       echo '<li onClick="fill(\''.$nombre.'\'); codigoFus(\''.$codigoSel.'\')">'.$nombre.'</li>';
   }
   //return $ciudadOri;
}
function reemplazarArticulos($string){
    $cadena = array('El','La','EL','LA','el','la','eL','lA','de','DE','De','dE','Santa','SANTA','santa','San','san','sAn','SAN');
    $string = str_replace($cadena,"", $string);
    return $string;
}	
?>