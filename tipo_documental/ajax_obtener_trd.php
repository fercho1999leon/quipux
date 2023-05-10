<?php
$ruta_raiz = "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
/*require_once("$ruta_raiz/funciones_interfaz.php");*/
include_once "$ruta_raiz/tipo_documental/obtener_datos_trd.php";
		// Is there a posted query string?

		if(isset($_POST['queryString'])) {
 
                   $query = limpiar_sql($_POST['queryString']);
                   $queryString = pg_escape_string(trim($query));                        
                   $queryString = strtoupper($queryString);
		   $sql = SqlCarpetaVirtual($db, $_SESSION["depe_codi"], 1);                   
//                  
                  
                   if(strlen($queryString) >0) {
                        $sql.= " and trd_codi in (select trd_codi from trd where ";     
                   $busquedaArr = explode(" ", $queryString);
                       for ($i=0;$i<sizeof($busquedaArr);$i++){
                           $busquedaArr[$i]=str_replace("/"," ",$busquedaArr[$i]);
                           if ($i==0)
                          $sql.= " upper(trd_nombre) like upper('%$busquedaArr[$i]%')";
                           else
                               $sql.=" or upper(trd_nombre) like upper('%$busquedaArr[$i]%')";
                       }
                         $sql.= " and depe_codi = ".$_SESSION["depe_codi"].")";
                   }
              
             
                   $sql.= " order by trd_codi,trd_padre, trd_nivel, trd_nombre limit 200 offset 0";
			
                                $rs = $db->conn->query($sql);
				echo "<ul id='result'>";
                                
                                //echo "<li>$sql</li>";
                                $i=1;
					while (!$rs->EOF) {						
                                            $idTrd = $rs->fields['TRD_CODI'];
                                            $idTrdPadre = $rs->fields['TRD_PADRE'];
                                            $trd_nombre = $rs->fields['TRD_NOMBRE'];
                                            $trd_padre_cv = $rs->fields['TRD_PADRE_CV'];
                                            //echo "<li>select count(*) as cont from trd where trd_padre = $idTrd</li>";
                                            
                                            if ($trd_padre_cv != ''){                                                
                                             $html.= str_replace(",,",",",dibujarhija($db,$trd_padre_cv,$trd_nombre));                                             
                                            }else{
                                                 
                                               if ($idTrdPadre==0 and $trd_nombre!=''){ 
                                                  $contDep = contarTrd($db,$idTrd,$_SESSION["depe_codi"]);//                                                
                                                   if ($contDep==0)
                                                   $html.="$idTrd|$trd_nombre,";
                                                }
                                                   else
                                                   $html.= str_replace(",,",",",dibujarTRD($db,$idTrd,$trd_nombre));
                                             }
                                            
                                        $rs->MoveNext();
                                        $i++;
                                        }     
                                        //echo "<li>$html</li>";
                                $html = explode(",",$html);
                                $listafinal = array_unique($html);
                                $listafinal = array_values($listafinal);
				for ($j=0;$j<sizeof($listafinal);$j++){                                    
                                    $codigosHt = explode("|",$listafinal[$j]);
                                    for($z=0;$z<sizeof($codigosHt);$z++){
                                        $codigoFinal = $codigosHt[0];
                                        $nombreFinal = $codigosHt[1];
                                    }
                                    if ($codigoFinal!='' and $nombreFinal!='')
                                    echo '<li onClick="fill(\''.$nombreFinal.'\'); codigoFusC(\''.$codigoFinal.'\')">'.$nombreFinal.'</li>';                                     
                                }
                                    
                                echo "</ul>";

		}
 
function dibujarTRD($db,$idTrd,$nombre){
   $sql ="select --TRD
            * from trd    
            where trd_codi = $idTrd and trd_estado=1";
   
   $rsDepePadre=$db->conn->query($sql);
   $trdCodi = $rsDepePadre->fields['TRD_CODI'];
   $nombreHija = $rsDepePadre->fields['TRD_NOMBRE'];
   //echo $ciudadHija."<br>";
   $carpPadre = $rsDepePadre->fields['TRD_PADRE'];
   if (!$rsDepePadre->EOF){
       if ($carpPadre>0){
           $nombre = buscarHijaDehija($db,$trdCodi,$nombreHija);
       }else{
           $nombre = "|".$nombre.'/'.$nombreHija;
           
       }
   }
   return $nombre;
}

function dibujarPadre($db,$idPadre,$carpetaOri,$codigoSel){
    $sql ="select --Padre
        * from trd    
            where trd_codi = $idPadre and trd_estado=1";
    //echo $sql;
   $rsDepePadre=$db->conn->query($sql);
   $carpetaHija = $rsDepePadre->fields['TRD_NOMBRE'];
   $carpetaPadre = $rsDepePadre->fields['TRD_PADRE'];
   $carpetaOri = $carpetaOri."/".$carpetaHija;
   
   if ($carpetaPadre!=0){
       $nombre = dibujarPadre($db,$carpetaPadre,$carpetaOri,$codigoSel);
   }else{
       $nombre = $nombre.'/'.$carpetaOri;
   }
   if ($nombre!='' || $nombre='/'){
           $nombre = $nombre;
           
       }
       $nombre = str_replace(",,",",",$nombre);
   return  $nombre;
}


function dibujarhija($db,$idPadre,$nombre){
    //$html="ss";
    $sql ="select --hija
        * from trd    
            where trd_padre = $idPadre and trd_estado=1";    
    $rsDepePadre=$db->conn->query($sql);
    while(!$rsDepePadre->EOF){
        //$ciudadHija = $rsDepePadre->fields['TRD_NOMBRE'];
        $codiHijo = $rsDepePadre->fields['TRD_CODI'];
        
        $html.=buscarHijaDehija($db, $codiHijo, $nombre);
        $rsDepePadre->MoveNext();
    }
     $html = str_replace(",,",",",$html);
    return $html;
    

}

function buscarhijos($db,$idPadre,$nombre){
    $html=",";
    $sql ="select --hijos
        * from trd    
            where trd_codi = $idPadre and trd_estado=1";    
    $rsDepePadre=$db->conn->query($sql);
    while(!$rsDepePadre->EOF){
        
        $codiHijo = $rsDepePadre->fields['TRD_CODI'];
        
        $html.=dibujarhija($db, $codiHijo, $nombre);
        $rsDepePadre->MoveNext();
    }
    
    return $html;
    

}

function buscarHijaDehija($db,$codigoHijo,$nombre){
    $sql ="select --hija de hija
        * from trd 
    left outer join 
(select distinct trd_padre as trd_padre_cv from trd where depe_codi = ". $_SESSION["depe_codi"]."
    and trd_estado <> 2) as cv on trd.trd_codi = cv.trd_padre_cv
            where trd_codi = $codigoHijo and trd_estado=1 ";
    
    $rsDepePadre=$db->conn->query($sql);
    
        if (!$rsDepePadre->EOF){
                while(!$rsDepePadre->EOF){
                $nombrePadre = $rsDepePadre->fields['TRD_NOMBRE'];

                $trdCodi = $rsDepePadre->fields['TRD_CODI'];
                $trdCodiPadre = $rsDepePadre->fields['TRD_PADRE'];
                if ($rsDepePadre->fields['TRD_PADRE_CV']==''){                
                    $nombre2.=dibujarPadre($db,$trdCodiPadre,$nombrePadre,$trdCodi);                
                    
                }
                else
                    $nombre2.=buscarhijos ($db, $trdCodi, $nombre);//"s:".$trdCodi."|/$nombre/,";
                $rsDepePadre->MoveNext();
            }
                 
        }    
        return $trdCodi."|".$nombre2.",";

}
function contarTrd($db,$idTrd,$depe_codi){
    $sql = "select count(*) as contador from trd where trd_padre = $idTrd and depe_codi = $depe_codi";
    $rs=$db->conn->query($sql);
    
    if (!$rs->EOF)
        return $rs->fields["CONTADOR"];
    else
      return 1;
}
?>