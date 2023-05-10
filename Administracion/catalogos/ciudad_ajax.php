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
/*************************************************************************************
** Permite administrar ciudades                                                     **
**                                                                                  **
** Desarrollado por:                                                                **

 *      David Gamboa
*************************************************************************************/

    $ruta_raiz = "../..";
    session_start();
    include_once "$ruta_raiz/rec_session.php";
    require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
    include_once "$ruta_raiz/funciones_interfaz.php";

    
    $codigo = 0+trim(limpiar_numero($_POST["codigo"]));    
    $nombre = trim(limpiar_sql($_POST["nombre"]));
    
    //echo $txt_codigo_ciudad."-".$txt_codigo_ciudad_dep;
    if ($db->transaccion==0) $db->conn->BeginTrans();
        if ($nombre!=''){//cuando es nueva ciudad
            $sql = "select nombre from ciudad where (translate(upper(nombre),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN')  like translate(upper('".$nombre."'),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN'))";
            
            $sql = $sql;
        }
        
    $nombreExiste="";
    $rs = $db->conn->Execute($sql);
    $nombre_consultado = $rs->fields["NOMBRE"];
    if ($nombre_consultado!=''){
        $nombreExiste= $nombre_consultado;
        echo "<font color='blue' size='1'>Ya existe el nombre: $nombreExiste</font>";
    }
    