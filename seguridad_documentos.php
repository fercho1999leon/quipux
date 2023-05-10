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
/**
*	Autor			Iniciales		Fecha (dd/mm/aaaa)
*
*
*	Modificado por		Iniciales		Fecha (dd/mm/aaaa)
*
*
*	Comentado por		Iniciales		Fecha (dd/mm/aaaa)
*	Sylvia Velasco		SV			01-12-2008
**/

/**
* Funcion recursiva que permite obtener la lista de dependencias que son visibles para el usuario actual.
* @param string Requiere como parametro el codigo de la dependencia y la conección con la bdd.
* @param object Cadena de conexion a la base de datos
* @param int El tercer parametro no se lo debe enviar.
* @return array() Retorna un arreglo, en la posición "0" un query que devuelve el codigo de la dependencia y
* el nombre y en la posicion "1" parte del where de una consulta con los codigos de la dependencia relacionados
* con un "or"
**/
function visibilidad_dependencias($depe, $db, $i=0) {
    $sql = "select depe_nomb, depe_codi from dependencia where depe_codi_padre=$depe";
    if ($i==0) $sql .= " or depe_codi=$depe"; 	else $sql .= " and depe_codi<>$depe";
    $rs = $db->conn->Execute($sql);
    $cadena[0] = " union ".$sql;
    $cadena[1] = ",".$depe;
    if ($rs) {
        while (!$rs->EOF) {
        if ($rs->fields["DEPE_CODI"] != $depe) {
            $tmp = visibilidad_dependencias($rs->fields["DEPE_CODI"], $db,1);
            $cadena[0] .= $tmp[0];
            $cadena[1] .= $tmp[1];
        }
            $rs->MoveNext();
        }
    }
    if ($i==0) {
	$cadena[0] = substr($cadena[0],7);
	$cadena[1] = " depe_codi in (".substr($cadena[1],1).")";
    }
    return $cadena;
}


function validar_transacciones($tx, $radi_nume, $db) {
    $mensaje_error = "";
    $radicado = ObtenerDatosRadicado($radi_nume,$db);


    if ($tx==4 || $tx==5 || $tx==6|| $tx==11 || $tx==13) {
        if ($radicado["usua_actu"]!=$_SESSION["usua_codi"])
            $mensaje_error .= "- ".$radicado["radi_nume_text"].". Usted no es el usuario actual del documento.<br>";
    }

    
    switch ($tx) {
        case 2: // Eliminar
            if ($radicado["usua_actu"]!=$_SESSION["usua_codi"] and $radicado["estado"]!="5")
                $mensaje_error .= "- ".$radicado["radi_nume_text"].". Usted no es el usuario actual del documento.<br>";
            if ($radicado["estado"]!="1" and $radicado["estado"]!="7" and $radicado["estado"]!="5")
                $mensaje_error .= "- ".$radicado["radi_nume_text"].".<br>";
            if ($radicado["estado"]=="7" and substr($radi_nume,-1)=="1") 
                $mensaje_error .= "- ".$radicado["radi_nume_text"].". El documento no es un borrador.<br>";
            break;

        case 3: //Envío Manual
            $_SESSION["existe_radi_path"] = $radicado["flag_archivo_asociado"];

            if ($radicado["estado"]!="5")
                $mensaje_error .= "- ".$radicado["radi_nume_text"].". El documento no ha sido marcado para env&iacute;o manual.<br>";
            // Verifico que el destinatario este activo
            $rs=$db->query("select usua_esta, usua_nombre from usuario where usua_codi in (".str_replace("-", "", $radicado["usua_dest"]).")");
            if ($rs->fields["USUA_ESTA"] != "1")
                $mensaje_error .= "- ".$radicado["radi_nume_text"].". El usuario ".$rs->fields["USUA_NOMBRE"]." se encuentra deshabilitado en el sistema.<br>";
            break;

        case 4: // Envío electrónico
        case 5: // Solicitar envio manual
             
            
            if ($radicado["estado"]!="3")
                $mensaje_error .= "- ".$radicado["radi_nume_text"].". No se puede realizar esta acci&oacute;n con el documento.<br>";
            break;

        case 6: // Sacar de eliminados
            if ($radicado["estado"]!="7")
                $mensaje_error .= "- ".$radicado["radi_nume_text"].". No se puede realizar esta acci&oacute;n con el documento.<br>";
            break;

        case 7: // Borrar informados
            $rs=$db->query("select radi_nume_radi from informados where radi_nume_radi=$radi_nume and usua_codi=".$_SESSION["usua_codi"]);
            if ($rs->EOF)
                $mensaje_error .= "- ".$radicado["radi_nume_text"].". Usted no lo tiene en su lista de informados<br>";
            break;

        case 8: // Informados
            // Permite informar si es el usuario actual del documento o si es un usuario que fue informado
            $sql = "select radi_nume_radi from hist_eventos where radi_nume_radi=$radi_nume and sgd_ttr_codigo in (8,9)
                    and (usua_codi_ori=".$_SESSION["usua_codi"]." or usua_codi_dest=".$_SESSION["usua_codi"] . ")";
            $rs=$db->query($sql);
//            $rs=$db->query("select radi_nume_radi from informados where radi_nume_radi=$radi_nume and usua_codi=".$_SESSION["usua_codi"]);
            if ($rs->EOF && $radicado["usua_actu"]!=$_SESSION["usua_codi"])
                $mensaje_error .= "- ".$radicado["radi_nume_text"].". No se puede realizar esta acci&oacute;n con el documento.<br>";/* */
            break;

        case 9: // Reasignar
            if ($radicado["usua_actu"]!=$_SESSION["usua_codi"] && $radicado["usua_actu"]!=$_SESSION["usua_codi_jefe"])
                $mensaje_error .= "- ".$radicado["radi_nume_text"].". Usted no es el usuario actual del documento.<br>";
            if ($radicado["estado"]!="1" and $radicado["estado"]!="2")
                $mensaje_error .= "- ".$radicado["radi_nume_text"].". No se puede realizar esta acci&oacute;n con el documento.<br>";
            break;

        case 11:  //Firmar y enviar
            $_SESSION["existe_radi_path"] = $radicado["flag_archivo_asociado"];
            $_SESSION["radi_tipo_doc"]=$radicado["radi_tipo"];
            
            
            //Verifica si el documento esta en estado de edicion
            if ($radicado["estado"]!="1")
                $mensaje_error .= "- ".$radicado["radi_nume_text"].". El documento no se encuentra en estado de edición.<br>";
            // Verifica si el usuario actual es el remitente
            if (str_replace("-","",$radicado["usua_rem"])!=$_SESSION["usua_codi"] && substr($radi_nume,-1)!=2)
                $mensaje_error .= "- ".$radicado["radi_nume_text"].". Usted no es el remitente del documento.<br>";
            // Verifica si el documento tiene texto en el cuerpo
            $rs = $db->query("select text_texto from radi_texto where text_codi=".$radicado["radi_codi_texto"]);
            $texto = preg_replace(':<.*?>:is', '', $rs->fields["TEXT_TEXTO"]); //limpiamos tags html
            $texto = str_ireplace("&nbsp;","",$texto);
            if (trim($texto)=="")
                $mensaje_error .= "- ".$radicado["radi_nume_text"].". El contenido del documento se encuentra en blanco.<br>";

            // Verifica si todos los destinatarios están activos
            $usr_dest = str_replace("-","",str_replace("--",",",$radicado["usua_dest"].$radicado["cca"]));
            $rs=$db->query("select usua_nombre from usuario where usua_esta=0 and usua_codi in ($usr_dest)");
            if (!$rs->EOF)
                $mensaje_error .= "- ".$radicado["radi_nume_text"].". El usuario ". $rs->fields["USUA_NOMBRE"] . " se encuentra desactivado.<br>";
            if ($radicado["radi_tipo"] == "2") {
                $rs=$db->query("select usua_codi from usuarios where usua_esta=1 and usua_codi in ($usr_dest)");
                if ($rs->EOF)
                    $mensaje_error .= "- ".$radicado["radi_nume_text"].". Por lo menos un destinatario debe pertenecer a la instituci&oacute;n.<br>";
            }
            break;

        case 13: //Archivar
            if ($radicado["estado"]!="2" and $radicado["estado"]!="6")
                $mensaje_error .= "- ".$radicado["radi_nume_text"].". No se puede realizar esta acci&oacute;n con el documento.<br>";
            break;
        case 17: // Sacar de ArchivadosArchivar
            if ($radicado["estado"]!="0")
                $mensaje_error .= "- ".$radicado["radi_nume_text"].". No se puede realizar esta acci&oacute;n con el documento.<br>";
            break;
        case 70:
            //$_SESSION["usua_remitente"]=$radicado["usua_rem"];
            $_SESSION["usua_destinatario"]=$radicado["usua_dest"];
        case 30: // Asignar Tareas
            $rs = $db->query("select estado from tarea where estado=1 and radi_nume_radi=$radi_nume and usua_codi_dest=".$_SESSION["usua_codi"]);
            if (!in_array($radicado["estado"], array("2","1")) 
            or ($radicado["usua_actu"]!=$_SESSION["usua_codi"] and $radicado["usua_actu"]!=$_SESSION["usua_codi_jefe"] and $rs->EOF))
                $mensaje_error .= "- ".$radicado["radi_nume_text"].". No se puede realizar esta acci&oacute;n con el documento.<br>";
            break;
        case 31: // finalizar Tareas
        case 36: // Registrar avance Tareas
            $rs = $db->query("select tarea_codi from tarea where estado=1 and radi_nume_radi=$radi_nume and usua_codi_dest=".$_SESSION["usua_codi"]);
            if ($rs->EOF)
                $mensaje_error .= "- ".$radicado["radi_nume_text"].". No se puede realizar esta acci&oacute;n sobre la tarea.<br>";
            break;
        case 32: // Cancelar Tareas
        case 34: // Reabrir Tareas
        case 35: // Editar Tareas
            $estado = "1";
            if ($tx==34) $estado = "2,3";
            $rs = $db->query("select tarea_codi from tarea where estado in ($estado) and radi_nume_radi=$radi_nume and usua_codi_ori=".$_SESSION["usua_codi"]);
            if ($rs->EOF)
                $mensaje_error .= "- ".$radicado["radi_nume_text"].". No se puede realizar esta acci&oacute;n sobre la tarea.<br>";
            break;
        case 33: // Comentar Tareas
            $rs = $db->query("select estado from tarea where radi_nume_radi=$radi_nume and (usua_codi_ori=".$_SESSION["usua_codi"]." or usua_codi_dest=".$_SESSION["usua_codi"].")");
            if ($radicado["usua_actu"]!=$_SESSION["usua_codi"] and $rs->EOF)
                $mensaje_error .= "- ".$radicado["radi_nume_text"].". No se puede realizar esta acci&oacute;n sobre la tarea.<br>";
            break;
        case 90:
            if ($radicado["estado"]!="9" or $_SESSION["perm_tramitar_docs_ciudadano"]!=1 or $_SESSION["inst_codi"]!=$radicado["inst_actu"])
                $mensaje_error .= "- ".$radicado["radi_nume_text"].". No se puede realizar esta acci&oacute;n con el documento.<br>";
            break;


    }
    return $mensaje_error;
}

?>