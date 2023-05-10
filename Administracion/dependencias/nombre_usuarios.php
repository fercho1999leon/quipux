<?php
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

$ruta_raiz = "../..";
session_start();
if($_SESSION["usua_admin_sistema"]!=1) die("");
include_once "$ruta_raiz/rec_session.php";

if (isset($_GET)){  
    //$nombre="";
    $codbuscar=limpiar_sql($_GET['cod_buscar']);
    $nombres = limpiar_sql($_GET['nombres']);
    $codbuscar = substr($codbuscar, 1);
    echo "<br> Buscar: ".$codbuscar;
    
//    if ($_GET['tipo']==1){
        $sql = "select usua_nombre from usuario where usua_codi in ($codbuscar)";
        echo $sql;
        $rs = $db->conn->query($sql);
        $nombre=$rs->fields['USUA_NOMBRE'];
        $nombre = $nombres.",".$nombre;  
//    }
//    else{
//        $sql = "select descripcion from permiso where id_permiso = ($codbuscar)";
//        echo $sql;
//        $rs = $db->conn->query($sql);
//        $nombre=$rs->fields['DESCRIPCION'];
//    }
    
    
   
    echo '<input type="text" name="seleccionado" id="seleccionado" value="'.$nombre.'"/>';
    

}?>



