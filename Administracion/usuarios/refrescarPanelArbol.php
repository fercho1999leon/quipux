<?
/**  Programa para el manejo de gestion documental, oficios, memorandus, circulares, acuerdos
*    Desarrollado y en otros Modificado por la SubSecretaría de Informática del Ecuador
*    Quipux    www.gestiondocumental.gov.ec
*------------------------------------------------------------------------------
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU Affero General Public License as
*    published by the Free Software Foundation, either version 3 of the
*    License, or (at your option) any later version.
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU Affero General Public License for more details.
*
*    You should have received a copy of the GNU Affero General Public License
*    along with this program.  If not, see http://www.gnu.org/licenses.
*------------------------------------------------------------------------------
**/
/*****************************************************************************************
**											**
*****************************************************************************************/
$ruta_raiz = "../..";
session_start();
include_once "$ruta_raiz/rec_session.php";


require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post

include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/obtenerdatos.php";


if(isset($_GET)){
    $ciudad = 0+limpiar_sql($_GET['ciudad']);   
    echo "<table width='100%'>";
    echo dibujarCiudad($db,$ciudad);
    echo "</table>";
}
function dibujarCiudad($db,$ciudad){
   $sql ="select * from ciudad    
            where id = $ciudad";
   $rsDepePadre=$db->conn->query($sql);
   $ciudadHija = $rsDepePadre->fields['NOMBRE'];
   //echo $ciudadHija."<br>";
   $ciudadPadre = $rsDepePadre->fields['ID_PADRE'];
   if ($ciudadPadre!=0){
       dibujarPadre($db,$ciudadPadre,$ciudadHija);
   }else{  
       $sql ="select * from ciudad    
            where id = $ciudad";       
   $rsDepePadre=$db->conn->query($sql);
   $ciudadNombre = $rsDepePadre->fields['NOMBRE'];
       echo "<tr><td class='titulos2' width='15%'>Ciudad</td>
           <td colspan='3' class='listado2'><input id='nombreCiudad' name='nombreCiudad' value='$ciudadNombre' size='45' readonly></td></tr>";
   }
  
}
function dibujarPadre($db,$idPadre,$ciudadOri){
    $sql ="select * from ciudad    
            where id = $idPadre";
    //echo $sql;
   $rsDepePadre=$db->conn->query($sql);
   $ciudadHija = $rsDepePadre->fields['NOMBRE'];
   $ciudadPadre = $rsDepePadre->fields['ID_PADRE'];
   $ciudadOri = $ciudadOri."/".$ciudadHija;
   
   if ($ciudadPadre!=0){
       dibujarPadre($db,$ciudadPadre,$ciudadOri);
   }else{      
      
       echo "<tr><td class='titulos2' width='15%'>Ciudad</td>
           <td colspan='3' class='listado2'><input id='nombreCiudad' name='nombreCiudad' value='$ciudadOri' size='45' readonly></td></tr>";
           
   }
}

?>