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
** Permite administrar títulos académicos                                           **
**                                                                                  **
** Desarrollado por:                                                                **
**      Lorena Torres J.                                                            **
*************************************************************************************/

    $ruta_raiz = "../..";
    session_start();
    include_once "$ruta_raiz/rec_session.php";
    require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
    include_once "$ruta_raiz/funciones_interfaz.php";

    $txt_codigo_titulo = trim(limpiar_numero($_POST["txt_cod_titulo"]));
    $txt_nombre_titulo = trim(limpiar_sql($_POST["txt_nombre_titulo"]));
    $txt_abreviatura_titulo = trim(limpiar_sql($_POST["txt_abreviatura_titulo"]));


    if ($db->transaccion==0) $db->conn->BeginTrans();

    $sql = "select tit_nombre from titulo where (translate(upper(tit_nombre),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN')  like translate(upper('".$txt_nombre_titulo."'),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN'))";
    if($txt_codigo_titulo != "")
        $where = " and tit_codi <> $txt_codigo_titulo";
    $sql = $sql.$where;
    $rs = $db->conn->Execute($sql);
    $nombre_consultado = $rs->fields["TIT_NOMBRE"];

    if($nombre_consultado==""){       
        //Datos de Título
        if($txt_codigo_titulo != "")
            $record["TIT_CODI"] = $txt_codigo_titulo;
        $record["TIT_NOMBRE"] = "'".ucwords(strtolower(trim($txt_nombre_titulo)))."'";
        $record["TIT_ABREVIATURA"] = "'".trim($txt_abreviatura_titulo)."'";
        $insertSQL=$db->conn->Replace("TITULO", $record, "TIT_CODI", false,false,true,false);
    }

    //Se finaliza transacción
    if(!$insertSQL) {
        if ($db->transaccion==0){
            $db->conn->RollbackTrans();
            if($nombre_consultado!="")
                $mensaje = "El título académico ". $txt_nombre_titulo . " ya existe. <br> ";
            else
                $mensaje = "Error no se guardó el título académico. <br> "; //SQL: ".$db->conn->querySql;
        }
        else return 0;
    } else {

        if ($db->transaccion==0){
            $db->conn->CommitTrans();
            $mensaje = "Datos de título académico guardados correctamente. <br> ";
        }
    }

    echo "<html>".html_head();
    echo "<center><br>$mensaje</center></br>";
?>
<form name="formulario" action="" method="post">
<center>
<input type='button' name='btn_aceptar' value='Aceptar' class='botones' onClick="window.location='titulo_usuario.php'">
</center>
  </body>
</html>
</form>