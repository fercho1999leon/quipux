<?php
/*------------------------------------------------------------------------------
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
*   Autor: David Gamboa 
**/

function grabar_historico_opciones_impresion($rs_old, $rs_new, $db) {
    if ($rs_new->EOF) return 0;
    $cadena = "";
    foreach ($rs_new->fields as $campo => $valor) {
        if ($rs_old->EOF) { // Si es nuevo registro
            if (trim($valor) != "") { // Se se puso un valor inicial diferente de null
                $hist_op_imp["id_transaccion"] = "'0'";
                $cadena .= "**$campo**: $valor<br>";
            }
        } else {
            $hist_op_imp["id_transaccion"] = "'1'"; //Modificado
            if (trim($rs_new->fields[$campo]) != trim($rs_old->fields[$campo])) {
                if (trim($rs_new->fields[$campo])!="" and trim($rs_old->fields[$campo])!="") {
                    $cadena .= "**$campo**: de ".trim($rs_old->fields[$campo])." a $valor<br>";
                } elseif (trim($rs_new->fields[$campo])=="") {
                    $cadena .= "**$campo**: Se eliminó ".trim($rs_old->fields[$campo])."<br>";
                } else {
                    $cadena .= "**$campo**: $valor<br>";
                }
            }
        }
    } // Fin Foreach
    $hist_op_imp["hist_fech_impresion"] = $db->conn->sysTimeStamp;
    $hist_op_imp["radi_nume_radi"]=$rs_new->fields["RADI_NUME_RADI"];
    $hist_op_imp["usua_codi_ori"]=$_SESSION["usua_codi"];
    $hist_op_imp["hist_observacion"] = $db->conn->qstr($cadena);
    if ($cadena != "") //Graba solo si existieron cambios en los datos
        $db->conn->Replace("HIST_OPC_IMPRESION", $hist_op_imp, false,false,false,false);
    return 1;
}

//para mostrar los tipos de impresion
function descimpresion($id_tipo_impresion){
   switch ($id_tipo_impresion) {
            case 1: 
                $descripcion="Generar Documento con datos de los destinatarios
                    (título, nombre, puesto, institución)";
            break;
            case 4: 
                $descripcion="Generar Documento con datos de los destinatarios
                    (título, nombre, puesto)";
            break;
            case 5: 
                $descripcion="Generar Documento con datos de los destinatarios
                    (título, nombre, institución)";
            break;
            case 6: 
                $descripcion="Generar Documento con datos de los destinatarios
                    (título, puesto, institución)";
            break;
            case 2: 
                $descripcion="Generar Documento con datos de los destinatarios
                    (puesto, institución)";
            break;
            case 3: 
                $descripcion="Generar Documento con nombre de la lista";
            break;                
            case 999:
                $descripcion="Generar una copia del documento para cada destinatario";
            break;                
            default :             
            return "";
            break;
   }
   return $descripcion;
}
//para mostrar los tipos de nota
function desc_tipo_nota($id_tipo_nota){
   switch ($id_tipo_nota) {
            case 3: 
                $descripcion="Verbal";
            break;
            case 1: 
                $descripcion="Diplomática";
            break;
            case 2: 
                $descripcion="Reversal";
            break;                            
            default :             
            return "";
            break;
   }
   return $descripcion;
}
?>