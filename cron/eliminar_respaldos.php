<?php
/**  Programa para el manejo de gestion documental, oficios, memorandos, circulares, acuerdos
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
/*****************************************************************************
**  Elimina los respaldos de la bodega y de la bdd cuando superan una x     **
cantidad de días según la cofiguración.                                     **
**  Programar para que se ejecute cada día                                  **
******************************************************************************/

$ruta_raiz = "..";
include_once ("$ruta_raiz/include/db/ConnectionHandler.php");
include_once ("$ruta_raiz/config.php");
error_reporting(7);
$db = new ConnectionHandler("$ruta_raiz");
$db->conn->SetFetchMode(ADODB_FETCH_ASSOC);

//Se consulta respaldos mayores a la fecha y que aún no están eliminados
$sql = "select ru.resp_codi, coalesce(s.resp_soli_codi,0) as resp_soli_codi, s.estado_solicitud, s.estado_respaldo
from respaldo_usuario ru
left outer join respaldo_solicitud s on ru.resp_codi=s.resp_codi
where fecha_eliminado is null
and fecha_fin is not null
and fecha_fin::date < (current_date - $dias_descarga)";
$rs = $db->query($sql);

while (!$rs->EOF) {
    
    //Datos consultados
    $resp_codi = $rs->fields["RESP_CODI"];
    $resp_soli_codi = $rs->fields["RESP_SOLI_CODI"];
    $estado_solicitud = $rs->fields["ESTADO_SOLICITUD"];
    $estado_respaldo = 15; //Estado eliminado
    
    //Se establece el path según el código de respaldo
    $path = "$ruta_raiz/bodega/respaldos/respaldo_$resp_codi";    
        
    //Se elimina los respaldos de la BDD    
    $sql_elimina = "delete from respaldo_usuario_radicado where resp_codi=$resp_codi"; 
    $db->query($sql_elimina);
    
    //Se elimina archivos y directorios
    if (file_exists($path)){
        //echo "path " . $path . "<br>";
        if (is_dir($path)) exec("rm -rf $path");
        exec("rm -f $path.z*");
        exec("rm -R $path");
    }
    
    //Se actualiza fecha de eliminación en la BDD
    $sql_ac = "update respaldo_usuario set fecha_eliminado=".$db->conn->sysTimeStamp." where resp_codi=$resp_codi";
    $db->query($sql_ac);
    
    //Se actualiza el estado del respaldo
    $sql_sol = "update respaldo_solicitud set estado_respaldo=$estado_respaldo where resp_soli_codi=$resp_soli_codi";
    $db->query($sql_sol);
    
    //Se guarda histórico de eliminación en las solicitudes
    if($resp_soli_codi != 0){
        $record = array();
        unset($record);
        $record["RESP_SOLI_CODI"] = $resp_soli_codi;
        ///$record["USUA_CODI"] = 0; //Usuario "Sistema"
        $record["FECHA"] = $db->conn->sysTimeStamp;
        $record["ACCION"] = 87;
        $record["COMENTARIO"] = "'Los $dias_descarga días de vigencia han expirado.'";
        $record["ESTADO_SOLICITUD"] = $estado_solicitud;
        $record["ESTADO_RESPALDO"] = $estado_respaldo;
        $db->conn->Replace("RESPALDO_HIST_EVENTOS", $record, "RESP_HIST_EVENTOS", false,false,true,false);
    }

    $rs->MoveNext();
}
?>