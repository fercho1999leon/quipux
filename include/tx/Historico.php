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

class Historico
{
 /**
   * Clase que maneja los Historicos de los documentos
   * @db Objeto conexion
   */
    var $db;
    //var $FechaEnvioFisico;

    function Historico($db)
    {
 	//Constructor de la clase Historico
	$this->db = $db;
    }

//  Clase que inserta el recorrido de los documentos
    function insertarHistorico($radicado, $usua_ori, $usua_dest, $observacion, $tipoTx, $referencia="")
    {
        $ruta_raiz = $this->db->rutaRaiz;
        include_once "$ruta_raiz/funciones.php";

        $observacion = str_replace("\n", "<br>", limpiar_sql($observacion)); // Para que conserve los saltos de línea

        $record["RADI_NUME_RADI"] = limpiar_sql($radicado);
        $record["USUA_CODI_ORI"] = limpiar_sql($usua_ori);
        $record["USUA_CODI_DEST"] = limpiar_sql($usua_dest);
        $record["SGD_TTR_CODIGO"] = limpiar_sql($tipoTx);
        $record["HIST_OBSE"] = $this->db->conn->qstr(substr($observacion,0,600));
        $record["HIST_FECH"] = $this->db->conn->sysTimeStamp;

        /*$FechaEnvioFisico=$record["HIST_FECH"];
        echo $FechaEnvioFisico;*/
        
        if (trim($referencia) != "")
            $record["HIST_REFERENCIA"] =$this->db->conn->qstr(limpiar_sql(substr($referencia,0,50)));
        $insertSQL = $this->db->insert("HIST_EVENTOS", $record, "true");
        return ($radicado);
    }


    function insertarHistoricoTarea($tarea, $radicado, $observacion, $tipoTx, $referencia="")
    {
        $ruta_raiz = $this->db->rutaRaiz;
        include_once "$ruta_raiz/funciones.php";

        $hist_codi = $this->db->nextId("sec_tarea_hist_eventos");
        $observacion = str_replace("\n", "<br>", limpiar_sql($observacion));

        $record["tarea_hist_codi"] = $hist_codi;
        $record["tarea_codi"] = 0 + $tarea;
        $record["radi_nume_radi"] = limpiar_sql($radicado);
        $record["usua_codi_ori"] = $_SESSION["usua_codi"];
        $record["accion"] = limpiar_sql($tipoTx);
        $record["comentario"] = $this->db->conn->qstr($observacion);
        $record["fecha"] = $this->db->conn->sysTimeStamp;
        if (trim($referencia) != "")
            $record["referencia"] =$this->db->conn->qstr(limpiar_sql(substr($referencia,0,50)));
        $insertSQL = $this->db->insert("tarea_hist_eventos", $record, "true");
        
        $sql = "update tarea set comentario_fin=$hist_codi where tarea_codi=$tarea";
        $this->db->conn->Execute($sql);

        return $hist_codi;
    }

    //  Clase que inserta el historico de envios fìsicos
    function insertarHistoricoFisico($radicado,$secuencial,$fecha, $usua_ori, $usua_dest, $observacion, $estado, $usua_resp,$estadoEnv)
    {
        $ruta_raiz = $this->db->rutaRaiz;
        include_once "$ruta_raiz/funciones.php";

        
        if (trim($fecha) != "")
           $record["HIST_FECH_ENVIO"] = $this->db->conn->qstr(limpiar_sql($fecha));

        $record["HIST_CODI"] =  limpiar_sql($secuencial);
        $record["RADI_NUME_RADI"] = limpiar_sql($radicado);
        $record["USUA_CODI_ENVIADO"] = limpiar_sql($usua_ori);

        if (trim($usua_resp) != "")
            $record["USUA_RESPONSABLE"] = $this->db->conn->qstr(limpiar_sql($usua_resp));

        if (trim($estado) != "")
            $record["ESTADO"] = $this->db->conn->qstr(limpiar_sql($estado));

        if (trim($estadoEnv) != "")
            $record["ESTADOENVIO"] = $this->db->conn->qstr(limpiar_sql($estadoEnv));
        
        $this->db->conn->Replace("HIST_ENVIO_FISICO", $record, "", false, false, true, false);
        

        return ($radicado);
    }


//  Clase que inserta el recorrido en todos los documentos que tienen el mismo temporal, 
//  es decir, en el documento original y en las copias que se envían a los distintos destinatarios del documento
    function insertarHistoricoTemporal($radicado, $usua_ori, $usua_dest, $observacion, $tipoTx, $referencia="")
    {
        $sql = "select radi_nume_radi from radicado where radi_nume_temp=$radicado";
        $rs = $this->db->conn->Execute($sql);
        while (!$rs->EOF) {
            $this->insertarHistorico($rs->fields["RADI_NUME_RADI"], $usua_ori, $usua_dest, $observacion, $tipoTx, $referencia);
            $rs->MoveNext();
        }
        return ($radicado);
    } 

} // end of Historico
?>
