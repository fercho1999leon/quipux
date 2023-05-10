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
**      Lorena Torres J.                                                            **
 * Modificado por:
 *      David Gamboa
*************************************************************************************/

    $ruta_raiz = "../..";
    session_start();
    include_once "$ruta_raiz/rec_session.php";
    require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
    include_once "$ruta_raiz/funciones_interfaz.php";

    $txt_codigo_ciudad = 0+trim(limpiar_numero($_POST["txt_cod_ciudad"]));    
    $txt_nombre_ciudad = trim(limpiar_sql($_POST["txt_nombre_ciudad"]));
    
    $txt_codigo_ciudad_dep = 0+trim(limpiar_numero($_POST["txt_cod_ciudad_dep"]));//nuevo    
    $txt_nombre_ciudad_dep = trim(limpiar_sql($_POST["txt_nombre_ciudad_dep"]));
    $txt_id_padre = 0+trim(limpiar_numero($_POST["txt_id_padre"]));    
    //echo $txt_codigo_ciudad."-".$txt_codigo_ciudad_dep;
    if ($db->transaccion==0) $db->conn->BeginTrans();
        
            $sql = "select nombre from ciudad where (translate(upper(nombre),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN')  like translate(upper('".$txt_nombre_ciudad_dep."'),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN'))";
            if($txt_codigo_ciudad ==0 || $txt_codigo_ciudad=='')
                $where = " and id_padre in ($txt_id_padre)";
            else
                $where = " and id_padre in ($txt_codigo_ciudad)";
            $sql = $sql.$where;
        
    //echo $sql;
    $rs = $db->conn->Execute($sql);
    $nombre_consultado = $rs->fields["NOMBRE"];
    if ($nombre_consultado!=''){
        $nombreExiste= "El nombre ".$nombre_consultado;
    }else{
        if($txt_codigo_ciudad == ""){
            $sql = "select max(id) as id from ciudad";
            $rs = $db->conn->Execute($sql);
            $txt_codigo_ciudad = $rs->fields["ID"]+1;
        }
        //Datos de Ciudad
        //nuevo en casos de paises o ciudades que se modifiquen
        if ($txt_nombre_ciudad==''){
            $record["ID"] = $txt_codigo_ciudad;
            $record["NOMBRE"] = "'".ucwords(strtolower(trim($txt_nombre_ciudad_dep)))."'";
            if ($txt_id_padre>0)
            $record["ID_PADRE"] = $txt_id_padre;
            
            if ($record["ID"]!=$record["ID_PADRE"] && trim($txt_nombre_ciudad_dep)!='')                
            $insertSQL=$db->conn->Replace("CIUDAD", $record, "ID", false,false,true,false);
            
        }else{
            
            $record["ID"] = $txt_codigo_ciudad;
            $record["NOMBRE"] = "'".ucwords(strtolower(trim($txt_nombre_ciudad)))."'";
            $record["ID_PADRE"] = $txt_id_padre;
            if ($record["ID"]!=$record["ID_PADRE"])
            $insertSQL=$db->conn->Replace("CIUDAD", $record, "ID", false,false,true,false);

            //dependencia
            if (trim($txt_nombre_ciudad_dep)!=''){                
                $record["ID"] = $txt_codigo_ciudad_dep;
                $record["NOMBRE"] = "'".ucwords(strtolower(trim($txt_nombre_ciudad_dep)))."'";
                $record["ID_PADRE"] = $txt_codigo_ciudad;
                if ($record["ID"]!=$record["ID_PADRE"])
                $insertSQL=$db->conn->Replace("CIUDAD", $record, "ID", false,false,true,false);
            }
        }
    }

    //Se finaliza transacción
    if(!$insertSQL) {
        if ($db->transaccion==0){
            $db->conn->RollbackTrans();
            if($nombre_consultado!=""){
                
                $mensaje = "$nombreExiste ya existe<br> ";
                
             }    
            else
                $mensaje = "Se ha modificado correctamente. <br> "; //SQL: ".$db->conn->querySql;
        }
        else return 0;
    } else {

        if ($db->transaccion==0){
            $db->conn->CommitTrans();
            $mensaje = "Datos de ciudad guardados correctamente. <br> ";
        }
    }

    echo "<html>".html_head();
    
    //echo "<center><br>$nombreExiste</center></br>";
    echo "<center><br>$mensaje</center></br>";
?>
<form name="formulario" action="" method="post">
<center>
<input type='button' name='btn_aceptar' value='Aceptar' class='botones' onClick="window.location='ciudad.php'">
</center>
  </body>
</html>
</form>