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
** Permite administrar contenido                                                    **
**                                                                                  **
** Desarrollado por:                                                                **
**      Lorena Torres J.                                                            **
*************************************************************************************/

    $ruta_raiz = "../..";
    session_start();
    include_once "$ruta_raiz/rec_session.php";
    require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
    include_once "$ruta_raiz/funciones_interfaz.php";

    $txt_cont_codi = trim(limpiar_numero($_POST["txt_cont_codi"]));
    $txt_descripcion = trim(limpiar_sql($_POST["txt_descripcion"]));
    $txt_texto = limpiar_sql(base64_decode(base64_decode($_POST["txt_texto_usuario"])),0);
    $cmb_tipo_contenido = trim(limpiar_numero($_POST["cmb_tipo_contenido"]));
   
    if ($db->transaccion==0) $db->conn->BeginTrans();

    //$txt_cont_codi = 1;
    //Datos de Título
    if($txt_cont_codi != "")
        $record["CONT_CODI"] = $txt_cont_codi;
    else
        $record["FECHA_CREA"] = $db->conn->sysTimeStamp;
    $record["CONT_TIPO_CODI"] = $cmb_tipo_contenido;
    $record["DESCRIPCION"] = "'".trim($txt_descripcion)."'";
    $record["TEXTO"] = "'".trim($txt_texto)."'";
    $record["FECHA_ACTUALIZA"] =$db->conn->sysTimeStamp;
    $insertSQL=$db->conn->Replace("CONTENIDO", $record, "CONT_CODI", false,false,true,false);

    //Se finaliza transacción
    if(!$insertSQL) {
        if ($db->transaccion==0){
            $db->conn->RollbackTrans();           
            $mensaje = "Error no se guardó el contenido. <br> "; //SQL: ".$db->conn->querySql;
        }
        else return 0;
    } else {

        if ($db->transaccion==0){
            $db->conn->CommitTrans();
            $mensaje = "Datos de contenido guardados correctamente. <br> ";
        }
    }

    echo "<html>".html_head();
    echo "<center><br>$mensaje</center></br>";
?>
<form name="formulario" action="" method="post">
<center>
<input type='button' name='btn_aceptar' value='Aceptar' class='botones' onClick="window.location='contenido.php'">
</center>
  </body>
</html>
</form>