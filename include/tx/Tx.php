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

/***************************************************************************************
**                                                                                    **
**           ☠☠☠☠☠☠☠☠                                                                 **
**        ☠☠☠☠☠☠☠☠☠☠☠☠☠☠            ☠☠☠    ☠☠   ☠☠☠☠☠☠                                **
**       ☠☠☠☠☠☠☠☠☠☠☠☠☠☠☠☠           ☠☠ ☠☠  ☠☠  ☠☠    ☠☠                               **
**      ☠☠☠☠☠☠☠☠☠☠☠☠☠☠☠☠☠☠          ☠☠  ☠☠ ☠☠  ☠☠    ☠☠                               **
**      ☠☠☠☠☠☠☠☠☠☠☠☠☠☠☠☠☠☠          ☠☠   ☠☠ ☠  ☠☠    ☠☠                               **
**      ☠☠☠☠☠ ☠☠☠☠☠☠ ☠☠☠☠☠          ☠☠    ☠☠☠   ☠☠☠☠☠☠                                **
**       ☠☠☠   ☠☠☠☠   ☠☠☠                                                             **
**        ☠☠   ☠☠☠☠   ☠☠                                                              **
**        ☠☠   ☠☠☠☠   ☠☠            ☠☠☠☠☠☠☠☠   ☠☠☠☠☠☠   ☠☠☠☠☠☠☠    ☠☠☠     ☠☠☠☠☠☠☠    **
**         ☠☠☠☠☠  ☠☠☠☠☠             ☠☠☠☠☠☠☠☠  ☠☠    ☠☠  ☠☠    ☠☠  ☠☠ ☠☠    ☠☠    ☠☠   **
**         ☠☠☠☠    ☠☠☠☠                ☠☠     ☠☠    ☠☠  ☠☠☠☠☠☠☠  ☠☠   ☠☠   ☠☠☠☠☠☠☠    **
**          ☠☠☠☠☠☠☠☠☠☠                 ☠☠     ☠☠    ☠☠  ☠☠      ☠☠☠☠☠☠☠☠☠  ☠☠    ☠☠   **
**           ☠☠☠☠☠☠☠☠                  ☠☠      ☠☠☠☠☠☠   ☠☠      ☠☠     ☠☠  ☠☠    ☠☠   **
**           ☠☠    ☠☠                                                                 **
**   ☠☠☠      ☠☠☠☠☠☠       ☠☠☠                                                        **
**    ☠☠☠☠     ☠☠☠☠     ☠☠☠☠                                                          **
**  ☠☠☠☠☠☠☠            ☠☠☠☠☠☠☠                                                        **
**   ☠☠☠☠☠☠☠☠☠      ☠☠☠☠☠☠☠☠☠       En este archivo se manejan las acciones que se    **
**    ☠☠    ☠☠☠☠☠☠☠☠☠☠    ☠☠        realizan con los documentos (reasignar, informar, **
**            ☠☠☠☠☠☠                archivar, tareas, etc.)                           **
**           ☠☠☠☠☠☠☠☠                                                                 **
**         ☠☠☠☠   ☠☠☠☠☠             Pueden hacer cambios únicamente bajo la           **
**      ☠☠☠☠☠       ☠☠☠☠☠           supervisión de Mauricio Haro A.                   **
**   ☠☠☠☠☠☠☠         ☠☠☠☠☠☠☠                                                          **
**  ☠☠☠☠☠☠☠            ☠☠☠☠☠☠☠                                                        **
**    ☠☠☠☠              ☠☠☠☠                                                          **
**      ☠☠              ☠☠                                                            **
**                                                                                    **
***************************************************************************************/


include_once($ruta_raiz."/include/tx/Historico.php");
class Tx extends Historico
{
    var  $db;
    var  $ruta_raiz;
    var  $flag_firmar;

/**
* Constructor de la clase Tx
* @param $db variable en la cual se recibe la conexión con la BDD
*/
function Tx($db)
{
    $this->db=$db;
    $this->ruta_raiz = $this->getRutaRaiz();
    $this->flag_firmar = false;
}


function getRutaRaiz() {
    if (is_file("./config.php")) return ".";
    if (is_file("../config.php")) return "..";
    if (is_file("../../config.php")) return "../..";
    return "";
}

function comentarDocumento($radicados, $usua_codi, $observa)
{
    foreach($radicados as $radi_nume)
    {
        $rs = $this->validarEstado($radi_nume);
        $this->insertarHistorico($radi_nume, $usua_codi, $rs->fields["RADI_USUA_ACTU"], $observa, 21);
        if ($_SESSION["usua_codi"]!=$rs->fields["RADI_USUA_ACTU"]){
            $mail_param["comentario"] = $observa;
            $this->enviarMail($_SESSION["usua_codi"], $rs->fields["RADI_USUA_ACTU"], $radi_nume, "Documento Comentado", "21", $mail_param);
        }
    }
    return "";
}

function devolverDocumento($radicados, $usua_codi, $observa)
{
    foreach($radicados as $noRadicado)
    {
        $rs = $this->validarEstado($noRadicado);
        $this->insertarHistorico($noRadicado, $usua_codi, $usua_codi, $observa, 23);
        $this->insertarHistorico($rs->fields["RADI_NUME_TEMP"], $usua_codi, $usua_codi, $observa, 23);
    }
    return "";
}


function eliminarDocumento($radicados, $usua_codi, $observa)
{
    include_once "$this->ruta_raiz/funciones.php";
    include_once "$this->ruta_raiz/obtenerdatos.php";

    $usr_actu = ObtenerDatosUsuario($usua_codi,$this->db);

    foreach($radicados as $radi_nume)
    {
    	$flag = false;
        $rs = $this->validarEstado($radi_nume);
        if($rs->fields["ESTA_CODI"]==1 && $rs->fields["RADI_USUA_ACTU"]==$usua_codi) {
            // Si elimino un documento en estado de elaboracion
            $estado = 7;
            $flag = true;
            // Cancelamos todas las tareas pendientes
            $this->cancelarTodasTareasEnviadas($radi_nume, "Se eliminó el documento");
        }
        if($rs->fields["ESTA_CODI"]==7 && $rs->fields["RADI_USUA_ACTU"]==$usua_codi && substr($radi_nume,-1)!="1") {
            // Si elimino definitivamente un documento, solo se puede eliminar documentos en estado de elaboracion
            $estado = 8;
            $flag = true;
        }
        if($rs->fields["ESTA_CODI"]==5) {
            // Eliminar documentos pendientes para envio manual
            $estado = 7;
            $flag = true;
            $radi = ObtenerDatosRadicado ($radi_nume, $this->db);
            $usr_dest = ObtenerDatosUsuario(str_replace("-","",$radi["usua_dest"]),$this->db);
            $tmp_obs = "Se eliminó documento destinado a " . $usr_dest["nombre"] . ". $observa";

            $this->insertarHistorico($rs->fields["RADI_NUME_TEMP"], $usua_codi, $usua_codi, $tmp_obs, 16, $radi_nume);

            $usr_dest = ObtenerDatosUsuario($rs->fields["RADI_USUA_ACTU"],$this->db);
            if ($radicado["estado"]==1) $bandeja = "en Elaboraci&oacute;n"; else $bandeja = "Recibidos";
            $mail = "<html><title>Informaci&oacute;n Quipux</title>";
            $mail .= "<body><center><h1>QUIPUX</h1><br><h2>Sistema de Gesti&oacute;n Documental</h2></center>";
            $mail .= "<br><br>Estimado(a):<br><br>".$usr_dest["abr_titulo"] . " " . $usr_dest["nombre"] . "<br>" . $usr_dest["cargo"];
            $mail .= "<br><br>El funcionario ".$usr_actu["abr_titulo"] . " " . $usr_actu["nombre"] .
                     ", ha eliminado el documento No. " . $radi["radi_nume_text"] .
                     " que se encontraba en espera de ser firmado y enviado manualmente en la bandeja Por Imprimir.";
            $mail .= "<br><br>Por favor revise su bandeja de Documentos Eliminados en el sistema &quot;**SISTEMA**&quot;";
            $mail .= "**DESPEDIDA**</body></html>";
            enviarMail($mail, "Informaci&oacute;n documento eliminado", $usr_dest["email"], $usr_dest["nombre"], $this->ruta_raiz);
        }
        if ($flag) { // Si cumple los requerimientos para eliminar el documento
            $this->db->conn->Execute("update radicado set esta_codi=$estado where radi_nume_radi=$radi_nume");
            // Quitar la asociacion de documentos
            $this->db->conn->Execute("update radicado set radi_nume_asoc=null where radi_nume_asoc=$radi_nume");
            $this->insertarHistorico($radi_nume, $usua_codi, $usua_codi, $observa, 16);
        }
    }
    return $usr_actu["nombre"];
}


function noEliminarDocumento($radicados, $usua_codi, $observa, &$mensaje)
{
    include_once "$this->ruta_raiz/obtenerdatos.php";
    $usr_actu = ObtenerDatosUsuario($usua_codi,$this->db);
    $flag1 = false;
    $flag2 = false;

    foreach($radicados as $radi_nume)
    {
        $flag = false;
		$rs = $this->validarEstado($radi_nume);

		if($rs->fields["ESTA_CODI"]==7 && $rs->fields["RADI_USUA_ACTU"]==$usua_codi)
		{
            $estado = 1;
            if (substr($radi_nume,-1) == "1") {
                $estado = 5;

                $radi = ObtenerDatosRadicado ($radi_nume, $this->db);
                $usr_dest = ObtenerDatosUsuario(str_replace("-","",$radi["usua_dest"]),$this->db);
                $tmp_obs = "Se restauró documento destinado a " . $usr_dest["nombre"] . ". $observa";
                $this->insertarHistorico($rs->fields["RADI_NUME_TEMP"], $usua_codi, $usua_codi, $tmp_obs, 17, $radi_nume);
                $flag2 = true;
            } else
                $flag1 = true;
            $flag = true;
		}
    	if ($flag) {
            $this->db->conn->Execute("update radicado set esta_codi=$estado where radi_nume_radi=$radi_nume");
            $this->insertarHistorico($radi_nume, $usua_codi, $usua_codi, $observa, 17);
        }
    }
    if ($flag1 && $flag2) {
        $mensaje = "El/Los documento(s) est&aacute;n en las bandejas &quot;En Elaboración&quot; y &quot;Por Imprimir&quot;.";
    } else {
        if ($flag1)
            $mensaje = "El/Los documento(s) est&aacute;n en la bandeja &quot;En Elaboración&quot;.";
        if ($flag2) 
            $mensaje = "El/Los documento(s) est&aacute;n en la bandeja &quot;Por Imprimir&quot;.";
    }
    return $usr_actu["nombre"];
}


function informar($radicados, $usua_codi, $usua_dest, $observa)
{
    include_once "../obtenerdatos.php";		//Consulta de datos de los usuarios y radicados
    $usr_dest = ObtenerDatosUsuario($usua_dest,$this->db);

    //$observa = "A: " . $usr_dest["login"] . " - $observa";
    $mail_param["num_docs"] = 0;
    foreach($radicados as $radi_nume)
    {
        # Asignar el valor de los campos en el registro
        $record["RADI_NUME_RADI"] = $radi_nume;
        $record["INFO_DESC"] = $this->db->conn->qstr($observa);
        $record["INFO_FECH"] = $this->db->conn->sysTimeStamp;
        $record["USUA_CODI"] = $usua_dest;
        $record["USUA_INFO"] = $usua_codi;
        //Insertamos los datos
        $informaSql = $this->db->conn->Replace("INFORMADOS",$record,array('RADI_NUME_RADI','USUA_INFO','USUA_CODI'),false,false,true,false);
        $this->insertarHistorico($radi_nume, $usua_codi, $usua_dest, $observa, 8);
        ++$mail_param["num_docs"];
    }
    $mail_param["enviado_por"] = "Informado por:";
    $mail_param["bandeja"] = "Informados";
    if ($mail_param["num_docs"] == 1)
        $this->enviarMail($usua_codi, $usua_dest, $radi_nume, "Documento Informado", "0", $mail_param);
    elseif ($mail_param["num_docs"] > 1)
        $this->enviarMail($usua_codi, $usua_dest, $radi_nume, "Documento Informado", "9", $mail_param);
    return $usr_dest["nombre"];
}


function borrarInformado($radicados, $usua_codi, $observa)
{
	foreach($radicados as $noRadicado)
	{
		$sql = "select usua_info from informados WHERE RADI_NUME_RADI=$noRadicado and USUA_CODI=$usua_codi";
		$rs = $this->db->query($sql);
		$usua_dest = $rs->fields['USUA_INFO'];
		$deleteSQL = $this->db->conn->Execute("DELETE FROM INFORMADOS WHERE RADI_NUME_RADI=$noRadicado and USUA_CODI=$usua_codi");
		$this->insertarHistorico($noRadicado, $usua_codi, $usua_dest, $observa, 7);
	}
	return;
}



function reasignar( $radicados, $usua_codi, $usua_dest, $observa, $fecha_tramite="", $flag_administrador=false, $carpeta=0)
{
    if ($usua_dest == "0") return "";
    $ruta_raiz = $this->db->rutaRaiz;
    include_once "$ruta_raiz/obtenerdatos.php";		//Consulta de datos de los usuarios y radicados

    $mail_param["enviado_por"] = "Reasignado por:";
    $flag_bandeja_compartida = false; //en caso de que la reasignación sea por bandeja compartida
    $codTx = 9;

    if (!$flag_administrador) {
        foreach($radicados as $radi_nume) {
            $rs = $this->validarEstado($radi_nume);

            if(($rs->fields["ESTA_CODI"]!=2 && $rs->fields["ESTA_CODI"]!=1) || 
               ($rs->fields["RADI_USUA_ACTU"]!=$_SESSION["usua_codi"] && $rs->fields["RADI_USUA_ACTU"]!=$_SESSION["usua_codi_jefe"]))
                die ("No se puede realizar esta acci&oacute;n con este documento.");
        }
    } else {
        if ($_SESSION["usua_admin_sistema"] != 1) die ("Usted no tiene los permisos suficientes para realizar esta aci&oacute;n.");
        $codTx = 10;
    }

    if (trim($fecha_tramite)=="") $fecha_tramite = date("Y-m-d");
    $usr_dest = ObtenerDatosUsuario($usua_dest,$this->db);
    $radicadosIn = join(",",$radicados);
    $isql = "update radicado set
              RADI_USUA_ANTE=$usua_codi
             ,RADI_USUA_ACTU=$usua_dest
             ,RADI_LEIDO=0
             , radi_fech_asig=to_timestamp('$fecha_tramite', 'YYYY-MM-DD')
             where RADI_NUME_RADI in($radicadosIn)";
    $this->db->conn->Execute($isql);
    foreach($radicados as $radi_nume) {        
        // En caso de Bandeja compartida se reasigna primero el documento del jefe al asistente
        if($rs->fields["RADI_USUA_ACTU"]==$_SESSION["usua_codi_jefe"] and (0+$_SESSION["usua_codi_jefe"])!=0 and !$flag_administrador){
            $usr_rem = ObtenerDatosUsuario($_SESSION["usua_codi"],$this->db);
            $usr_jefe = ObtenerDatosUsuario($_SESSION['usua_codi_jefe'],$this->db);
            $observaJefe = 'Documento tomado por '.$usr_rem["nombre"].' de la Bandeja de Documentos Recibidos de '.$usr_jefe['nombre'].'.';
            if ($_SESSION["usua_codi"]==$usua_dest) // Si se está auto asignando el asistente el documento
                $observaJefe .= "\n".$observa;

            $this->insertarHistorico($radi_nume, $_SESSION["usua_codi_jefe"], $_SESSION["usua_codi"], $observaJefe, 9, $fecha_tramite);

            // Envio de correo de notificacion de que el documento ha sido tomado al jefe.
            $flag_bandeja_compartida = true;
            if (count($radicados) == 1) //Para validar que se envie un solo mail por todos los documentos
                $this->enviarMail($_SESSION["usua_codi"], $_SESSION['usua_codi_jefe'], $radi_nume,'Documento Reasignado','1');
        }
        // Fin
        if (($flag_bandeja_compartida and $_SESSION["usua_codi"]!=$usua_dest) or !$flag_bandeja_compartida) {
            $this->insertarHistorico($radi_nume, $_SESSION["usua_codi"], $usua_dest, $observa, $codTx, $fecha_tramite);
            if ($_SESSION["usua_codi"]!=$usua_dest) {
                if (count($radicados) == 1)
                    $this->enviarMail($_SESSION["usua_codi"], $usua_dest, $radi_nume, "Documento Reasignado", "0", $mail_param);
            }
        }
        $this->cambiarPropietarioTareas($radi_nume, $usua_dest, $usua_codi);
    }
    if (count($radicados) > 1) {
        $mail_param["num_docs"] = count($radicados);
        if ($_SESSION["usua_codi"]!=$usua_dest)
            $this->enviarMail($_SESSION["usua_codi"], $usua_dest, $radi_nume, "Documento Reasignado", "9", $mail_param);
        if ($flag_bandeja_compartida and $_SESSION["usua_codi_jefe"]!=$usua_dest)
            $this->enviarMail($_SESSION["usua_codi"], $_SESSION['usua_codi_jefe'], $radi_nume,'Documento Reasignado','1A', $mail_param);
    }
    return $usr_dest["nombre"];

}

function enviarfisico( $radicados, $usua_codi, $usua_dest, $observa, $usua_respo, $flag_administrador=false)
{
    if ($usua_dest == "0" or $usua_respo=="") return "";
    $ruta_raiz = $this->db->rutaRaiz;
    include_once "$ruta_raiz/obtenerdatos.php";//Consulta de datos de los usuarios y radicados

    $usr_dest = ObtenerDatosUsuario($usua_dest,$this->db);

    if ($_POST['opcDoc']!='') {
        $estado=$_POST['opcDoc'];
    } elseif ( !isset($_POST['opcDoc']) or ($_POST['opcDoc']=='')) {
        $estado="M";
    }

    foreach($radicados as $radi_nume) {
        if (trim($radi_nume)!='') {
            $this->insertarHistorico($radi_nume, $usua_codi, $usua_dest, $observa, 69,$usua_respo);
            $sql = "select max(hist_codi) as hist_codi from hist_eventos
                    WHERE RADI_NUME_RADI=$radi_nume and USUA_CODI_ORI=$usua_codi
                    and USUA_CODI_DEST=$usua_dest and SGD_TTR_CODIGO=69";
            $rs = $this->db->query($sql);
            $fecha = $this->db->conn->sysTimeStamp;
            $secuencial = $rs->fields['HIST_CODI'];
            $this->insertarHistoricoFisico($radi_nume, $secuencial, $fecha, $usua_codi, $usua_dest, $observa, $estado, $usua_respo, 1);
        }//if
    }//for
    return $usr_dest["nombre"];
}


//Esta funcion afecta a todos los documentos (Consultar con Mauricio Haro antes de algun cambio)

function GenerarDocumentosEnvio($radicados, $usua_codi, $observa, $ruta_raiz="..")
{
    include_once "Radicacion.php";				//Registro de radicados
    include_once "$ruta_raiz/obtenerdatos.php";			//Consulta de datos de los usuarios y radicados
    foreach($radicados as $radi_nume) {
        $rs = $this->validarEstado($radi_nume);
        if($rs->fields["ESTA_CODI"]!=1 || $rs->fields["RADI_USUA_ACTU"]!=$_SESSION["usua_codi"])
            die ("No se puede realizar esta acci&oacute;n con este documento.");
    }

    $rad = new Radicacion($this->db);
    $rad->transaccion=1;  //Indica que el commit o rollback de la transacción se manejará localmente
    $usua_nomb = "";
    $flag = false;  //Indica si por lo menos se generó un radicado
    $usr_actual = ObtenerDatosUsuario($usua_codi,$this->db);
    foreach ($radicados as $radi_nume) {
        $this->db->conn->BeginTrans();	//Inicia la transaccion
        // Cancelamos todas las tareas pendientes
        $this->cancelarTodasTareasEnviadas($radi_nume, "Se realizo la acción de \"Firmar y Enviar\" el documento");

        $this->insertarHistorico($radi_nume, $usua_codi, $usua_codi, $observa, 65);	//Firmar y enviar
        $tiporad = substr($radi_nume,-1);
        $radicado = ObtenerDatosRadicado($radi_nume,$this->db);

        $rad->radiNumeTemp = $radi_nume;
        $rad->radiTextTemp = $radicado["radi_text_temp"];
        $rad->radiNumeDeri = $radicado["radi_padre"];
        $rad->radiNumeAsoc = $radicado["radi_nume_asoc"];
        $rad->radiPath = $radicado["radi_path"];
        $rad->radiUsuaRadi = $usua_codi;
        $rad->radiDescAnex = $radicado["radi_desc_anexos"];
        $rad->radiAsunto = $radicado["radi_asunto"];
        $rad->radiResumen = $radicado["radi_resumen"];
        $rad->radiTexto = $radicado["radi_codi_texto"];
        $rad->usar_plantilla = $radicado["usar_plantilla"];
        $rad->ajust_texto = $radicado["ajust_texto"];
        $rad->radi_tipo_impresion = $radicado["radi_tipo_impresion"];
        $rad->cod_codi = $radicado["cod_codi"];
        $rad->cat_codi = $radicado["cat_codi"];
        $rad->radi_lista_dest = $radicado["radi_lista_dest"];
        $rad->flagRadiTexto = "1";
        $rad->radiFlagImprimir = "1";
        $rad->radiSeguridad = $radicado["seguridad"];
        $rad->radiUsuaRem = $radicado["usua_rem"];
        $rad->radiTipo = $radicado["radi_tipo"];
        $rad->radiCuentai = $radicado["radi_referencia"];
        $rad->radiNumeText = "";
        $rad->radiUsuaAnte = $usua_codi;
        $rad->radiUsuaActu = $usua_codi;
        $rad->radiInstActu = $usr_actual["inst_codi"];
        $rad->radiEstado = "4";	//No enviado, para envío electrónico
        $rad->radiFechOfic = "";
        $rad->usua_redirigido = "0";
        $rad->radi_imagen = $radicado["radi_imagen"];
        if ($tiporad == 2) {
            $rad->radiNumeText = $radicado["radi_nume_text"];
            $rad->radiFechOfic = $radicado["radi_fecha"];
            $rad->usua_redirigido = $radicado["usua_redirigido"];
        }

        // Guardamos datos de los destinatarios y remitentes en la tabla usuarios_radicado
        $this->db->conn->Execute("delete from usuarios_radicado where radi_nume_radi=$radi_nume");
        $this->GuardarUsuariosRadicado($radi_nume, $radicado["usua_rem"], 1,$radicado);
        $this->GuardarUsuariosRadicado($radi_nume, $radicado["usua_dest"], 2,$radicado);
        $this->GuardarUsuariosRadicado($radi_nume, $radicado["cca"], 3,$radicado);

        // Generamos un documento para cada uno de los destinatarios
        foreach (explode('-',$radicado["usua_dest"].$radicado["cca"]) as $usua_dest) {
            if (trim($usua_dest) != "") {
                $usr = ObtenerDatosUsuario($usua_dest,$this->db);
                $rad->radiUsuaDest = "-".$usua_dest."-";

                if ($tiporad==0 or ($tiporad==2 and $usr["inst_codi"]!=0 and $usr["inst_codi"]==$_SESSION["inst_codi"])) {
                    // Se crean documentos solo si es un documento de salida o en el caso de registro de docs externos si el destinatario es usuario de la institucion
                    $flag = true;
                    $usua_nomb .= $usr["nombre"].", ".$usr["institucion"]."<br>";
                    $noRad = $rad->newRadicado(1, $usr_actual["depe_codi"], $textrad);
                    $this->insertarHistorico($noRad, $usua_codi, $usua_dest, $observa, 2);	//registro
                    $observa2 = "Se generó documento para ".$usr["nombre"].".";
                    $this->insertarHistorico($radi_nume, $usua_codi, $usua_dest, $observa2, 2, $noRad);	//registro
                }
            }
        }

        if ($flag) { // Si se generaron documentos cambia el estado del documento padre
            $tmp = "";
            if ($tiporad == 0) $tmp = ", radi_fech_ofic = '" . $rad->radiFechOfic . "'::timestamp";
            $isql = "update radicado set radi_fech_agend=null, esta_codi=3 $tmp, radi_nume_text='".$rad->radiNumeText."' where RADI_NUME_RADI = $radi_nume";
            $this->db->conn->Execute($isql); //Cambio de estado del documento padre
        } else {
            echo "<br/><span><font color='Navy'><b>No existen destinatarios que pertenezcan a la instituci&oacute;n.<br/>
                  El documento ".$radicado["radi_nume_text"]." no ser&aacute; enviado.</b></font></span><br/>";
        }
        if ($noRad!=0 and $flag) {
            $this->db->conn->CommitTrans();
        } else {
            $this->db->conn->RollbackTrans();
            echo "<br/><span><font color='Red'><b>Existieron errores al firmar el documento No. " . $radicado["radi_nume_text"].".</b></font></span><br/>";
        }
    }
    return substr($usua_nomb,0,-4);
} 

function cambioEstadoDocumentoGenerado($radicados)
{
    include_once $this->ruta_raiz."/plantillas/generar_documento.php";	//Genera el archivo PDF
    $pdf = New GenerarDocumento($this->db);
    $flag_firma_digital = 0;
    foreach($radicados as $radi_nume) {
        $tiporad = substr(trim($radi_nume),-1);
        $sql = "select * from radicado where radi_nume_radi=$radi_nume
                union all
                select * from radicado where radi_nume_temp=$radi_nume and radi_nume_radi<>$radi_nume";
        // ordeno por fecha para que el padre sea el primer registro
        $rs = $this->db->conn->query($sql);
        if($rs->fields["ESTA_CODI"]==3) { //El documento padre debe estar en estado 3 (pendiente)
            
            if ($tiporad == "2") { //Documentos Externos
                $lista_destinatarios = $rs->fields["RADI_USUA_DEST"];
                $redirigido = 0+$rs->fields["RADI_USUA_REDIRIGIDO"];
                while (!$rs->EOF) {
                    if (substr($rs->fields["RADI_NUME_RADI"],-1) == "1") {
                        $destino = str_replace("-", "", $rs->fields["RADI_USUA_DEST"]);
                        $estado = 2;
                        // Redirigidos, valido que no se redirija al mismo destinatario, y que se redirija solo el documento del destinatario
                        if ($redirigido!=0 and $destino!=$redirigido and strpos($lista_destinatarios, $rs->fields["RADI_USUA_DEST"])!==false) { //redirigido
                            $this->insertarHistorico($radi_nume, $_SESSION["usua_codi"], $redirigido, "", 28); //Redirigir Documento
                            $this->insertarHistorico($rs->fields["RADI_NUME_RADI"], $_SESSION["usua_codi"], $redirigido, "", 28);
                            $this->informar(array($rs->fields["RADI_NUME_RADI"]), $_SESSION["usua_codi"], $destino, "Documento externo dirigido a otro usuario.");
                            $destino = $redirigido; // Para actualizar el radi_usua_actu
                        }
                        $this->insertarHistorico($rs->fields["RADI_NUME_RADI"], $_SESSION["usua_codi"], $destino, "", 18); //Envío Electrónico
                        //$this->enviarMail($_SESSION["usua_codi"], $destino, $rs->fields["RADI_NUME_RADI"]);
                        $remitente = str_replace("-", "", $rs->fields["RADI_USUA_REM"]);
                        $this->enviarMail($remitente, $destino, $rs->fields["RADI_NUME_RADI"], "Documento Recibido");
                    } else { // Estado del documento padre
                        $destino = $rs->fields["RADI_USUA_ACTU"];
                        $estado = 6;
                    }
                    // Cambiamos el estado y el usuario actual
                    $sql = "update radicado set esta_codi=$estado, radi_usua_actu=$destino where radi_nume_radi=".$rs->fields["RADI_NUME_RADI"];
                    $this->db->conn->Execute($sql);
                    $rs->MoveNext();
                }
            } // fin documentos externos


            if ($tiporad == "0") { //Documentos de salida
                if (!$this->flag_firmar or $_SESSION["firma_digital"]!=1) { // Si no firma electronicamente
                    // Pongo como documento por imprimir todas las copias
                    $sql = "update radicado set esta_codi=5 where radi_nume_radi<>$radi_nume and radi_nume_temp=$radi_nume";
                    $this->db->conn->Execute($sql);
                    // Pongo como enviado el documento original
                    $sql = "update radicado set esta_codi=6 where radi_nume_radi=$radi_nume";
                    $this->db->conn->Execute($sql);
                    // Genero el PDF
                    $pdf->GenerarPDF($radi_nume,"no");
                } else { // Si firma electronicamente
                    $radi_fisico = "";
                    $radi_electronico = "";
                    while (!$rs->EOF) {
                        if (substr($rs->fields["RADI_NUME_RADI"],-1) == "1") {
                            $destino = str_replace("-", "", $rs->fields["RADI_USUA_DEST"]);
                            $rs_dest = $this->db->conn->query("select count(1) as num from usuarios where usua_codi=$destino");
                            if ($rs_dest->fields["NUM"]==0) { // Si el destinatario es ciudadano
                                $radi_fisico = $rs->fields["RADI_NUME_RADI"];
                                $sql = "update radicado set esta_codi=5 where radi_nume_radi=$radi_fisico";
                                $this->db->conn->Execute($sql);
                            } else { // Si el destinatario es funcionario publico
                                $radi_electronico = $rs->fields["RADI_NUME_RADI"];
                                $flag_firma_digital = 1;
                            }
                        }
                        $rs->MoveNext();
                    }
                    if ($radi_fisico!="") { // Si se envia a algun ciudadano
                        if ($radi_electronico == "") { // Si todos eran ciudadanos
                            $sql = "update radicado set esta_codi=6 where radi_nume_radi=$radi_nume";
                            $this->db->conn->Execute($sql);
                            //$radi_fisico = $radi_nume;
                        }
                        $pdf->GenerarPDF($radi_fisico,"si");
                    }
                }
            }
        }
    }
    return $flag_firma_digital;
}


function forzarEnvioManualDocumentos($radicados, $observa="")
{
    foreach($radicados as $radi_nume) {
        $this->insertarHistorico($radi_nume, $_SESSION["usua_codi"], $_SESSION["usua_codi"], $observa, 20);
    }
    $this->flag_firmar = false;
    $this->cambioEstadoDocumentoGenerado($radicados);
    return "bandeja &quot;Por Imprimir&quot; de la secretaria";
}


function GuardarUsuariosRadicado($radicado, $usuario, $usua_tipo, $rad) {
    global $nombre_servidor;
    $tipoDoc = $rad["radi_tipo"];
    include_once "../obtenerdatos.php";		//Consulta de datos de los usuarios y radicados
    foreach (explode('-',$usuario) as $usua_codi) {
        if (trim($usua_codi) != "") {
            unset($recordSet);
            $usr = ObtenerDatosUsuario($usua_codi,$this->db);
            //$rad = ObtenerDatosRadicado($radicado,$this->db);
            $recordSet["RADI_NUME_RADI"] = $radicado;
            $recordSet["RADI_USUA_TIPO"] = $usua_tipo;
            $recordSet["USUA_CEDULA"] = $this->db->conn->qstr($usr["cedula"]);
            $recordSet["USUA_NOMBRE"] = $this->db->conn->qstr($usr["usua_nombre"]);
            $recordSet["USUA_APELLIDO"] = $this->db->conn->qstr($usr["usua_apellido"]);
            $recordSet["USUA_TITULO"] = $this->db->conn->qstr($usr["titulo"]);
            $recordSet["USUA_ABR_TITULO"] = $this->db->conn->qstr($usr["abr_titulo"]);
            $recordSet["USUA_INSTITUCION"] = $this->db->conn->qstr($usr["institucion"]);
            $recordSet["USUA_EMAIL"] = $this->db->conn->qstr($usr["email"]);
            $recordSet["USUA_AREA_CODI"] = 0+$usr["depe_codi"];
            $recordSet["USUA_CODI"] = 0+$usua_codi;
            $recordSet["INST_CODI"] = 0+$usr["inst_codi"];
            $recordSet["USUA_CIUDAD"] = $this->db->conn->qstr($usr["ciudad"]);
            $recordSet["USUA_AREA"] = $this->db->conn->qstr($usr["dependencia"]); //Area a la que pertenece el usuario
            $recordSet["USUA_CARGO"] = $this->db->conn->qstr($usr["cargo"]);
            if($usua_tipo==2 and $tipoDoc==1 and $usr["tipo_usuario"]==1)//Para, Oficio y Funcionario
                $recordSet["USUA_CARGO"] = $this->db->conn->qstr($usr["cargo_cabecera"]);
            
            //Obtener los nombres de las listas en caso de que los destinatarios se seleccionaron de una lista.
            if(trim($rad['radi_lista_dest'])!='' and trim($rad['radi_lista_dest'])!='0') {
                $radi_lista_dest = $rad['radi_lista_dest'];
                $codList = split("-",$radi_lista_dest);
                if(sizeof($codList)>2) {
                    for($j=1;$j<sizeof($codList)-2;$j+=2) {
                        $datosLista = ObtenerDatosLista(trim($codList[$j]),$this->db);
                        $radi_lista_nombre .= $datosLista['nombre'] . '<br>';
                    }
                    $datosLista = ObtenerDatosLista(trim($codList[$j]),$this->db);
                    $radi_lista_nombre .= $datosLista['nombre'];
                } else {
                    $datosLista = ObtenerDatosLista(trim($codList[$j]),$this->db);
                    $radi_lista_nombre .= $datosLista['nombre'];
                }
                $recordSet["LISTA_NOMBRE"] = $this->db->conn->qstr($radi_lista_nombre); //Nombre de listas para el caso de que el tipo de impresion sea con nombre de lista.
            }
            if ($usua_tipo==1 and trim($usr["usua_firma_path"]) != "")
                $recordSet["USUA_FIRMA_PATH"] = $this->db->conn->qstr($nombre_servidor."/".$usr["usua_firma_path"]);
            $this->db->conn->Replace("USUARIOS_RADICADO", $recordSet, "", false,false,false);
        }
    }
    return;
}


function enviarDocumentosFirmaElectronica($radicados, $file_firma = null, $password_firma = null)
{
    include $this->ruta_raiz."/config.php";			//Consulta de datos de los usuarios y radicados
    include_once $this->ruta_raiz."/obtenerdatos.php";			//Consulta de datos de los usuarios y radicados
    include_once $this->ruta_raiz."/plantillas/generar_documento.php";	//Genera el archivo PDF
    include_once $this->ruta_raiz."/interconexion/ws_cliente_firma_digital.php";	//Web service para realizar la firma digital de los documentos
    $pdf = New GenerarDocumento($this->db);

    $firma = array();
    $usr = ObtenerDatosUsuario($_SESSION["usua_codi"],$this->db);

    $clave_archivo = date('Y-m-d-H-i-s'); // En el caso que se envien varios documentos a la vez
    $flag_firmar = false;
    foreach ($radicados as $radi_nume) {
        $sql = "select * from radicado where radi_nume_radi=$radi_nume
                union all
                select * from radicado where radi_nume_temp=$radi_nume and radi_nume_radi<>$radi_nume";
        // ordeno por fecha para que el padre sea el primer registro
        $rs = $this->db->conn->query($sql);
        if($rs->fields["ESTA_CODI"]==3) { //El documento padre debe estar en estado 3 (pendiente)
        //  Generamos el archivo pdf
            $path_pdf = $pdf->GenerarPDF($radi_nume,"si");
        //  Firmamos digitalmente el archivo
            if ($path_pdf != "") {
                 //envio_documentos_para_firma($usr["cedula"], $radi_nume, $path_arch.$nomb_arch,$nombre_servidor,$clave_archivo,$servidor_wsfirma);
                $path_pdf = $this->ruta_raiz."/bodega".$path_pdf;
		        $flag_envio_documento = envio_documentos_para_firma(substr($usr["cedula"],0,10), $radi_nume, $path_pdf, $nombre_servidor, $clave_archivo, $servidor_firma, $file_firma, $password_firma);
                if ($flag_envio_documento!="0") {
                    $flag_firmar = true;
                    //var_dump($flag_envio_documento);
                } //DESCOMENTAR PARA FIRMA
            }
        }
    }
    if ($flag_firmar) {
        return $flag_firmar;
        //LLAMAR AL APPLET
    	//$this->mostrar_applet_firma_digital();
    } else {
        echo "<br/><span><font color='Navy'><b>Existieron errores al firmar los documentos. Por favor vuelva a intentarlo.</b></font></span><br/>";
    }
    return "";
}


function mostrar_applet_firma_digital() {
    include $this->ruta_raiz."/config.php";
    $rs = $this->db->conn->Execute("select usua_tipo_certificado from usuarios where usua_codi=".$_SESSION["usua_codi"]);
    echo "<script>
    function firma_electronica() {
        windowprops = 'top=100,left=100,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=600,height=400';
        URL = '$servidor_firma/applet.php?sistema=$nombre_servidor&tipo_certificado=".$rs->fields["USUA_TIPO_CERTIFICADO"]."&accion=firma';
        window.open(URL , 'Firma Electronica', windowprops);
    }
    firma_electronica();
    </script>";

    echo "<br/><span><font color='blue'><h4>Si la pantalla que le permite realizar la firma electrónica <br/>
          no aparece en unos segundos, por favor de click
          <a href=\"javascript:firma_electronica();\" class='aqui' ><b>&quot;AQU&Iacute;&quot;</b></a></h4></font></span><br/>";

}


function envioElectronicoDocumento($radi_nume, $usua_codi) {

    include_once "Radicacion.php";          //Registro de radicados
    $rad = new Radicacion($this->db);
    $radi_nume_text = array();              //Se la utiliza para que no se generen 2 codigos de documentos si se envia a funcionarios de la misma institución
    unset($radi_nume_text);
    $respInstitucion = "";

    $rs_usr = $this->db->conn->query("select depe_codi, inst_codi, cargo_tipo from usuarios where usua_codi=$usua_codi");

    $sql = "select r.radi_nume_radi, r.radi_nume_text, u.usua_codi, u.depe_codi, u.inst_codi, r.radi_nume_deri, r.radi_usua_rem, radi_usua_dest
            from radicado r
                left outer join usuarios u on replace(r.radi_usua_dest,'-','')::integer=u.usua_codi
            where r.esta_codi=4 and radi_nume_temp=$radi_nume";
    $rs = $this->db->conn->query($sql);

    $radi_nume_text[1] = $rs->fields["RADI_NUME_TEXT"]; //Para que no cambie el numero de documento si el destinatario es un ciudadano

    $estado = 2;
    if ($rs_usr->fields["INST_CODI"]==1) $estado = 9; // Si es un ciudadano el que firma para que se vaya a una bandeja de entrada

    while ($rs && !$rs->EOF) {
        $usr_destino = $rs->fields["USUA_CODI"];
        $sql = "update radicado set esta_codi=$estado, radi_usua_actu=$usr_destino";
        if ($rs_usr->fields["INST_CODI"] != $rs->fields["INST_CODI"]) {
            // Validamos para cuando se envien documentos a 2 funcionarios de otra institucion no se generen 2 codigos
            if (!isset($radi_nume_text[$rs->fields["INST_CODI"]])) {
                $tmp = date("Y").str_pad($rs->fields["DEPE_CODI"],6,"0", STR_PAD_LEFT)."0000000002";
                $radi_nume_text[$rs->fields["INST_CODI"]] = $rad->GenerarTextRadicado($tmp, 2, "N");
            }
            $sql .= ", radi_inst_actu=".$rs->fields["INST_CODI"];
            $sql .= ", radi_nume_text='".$radi_nume_text[$rs->fields["INST_CODI"]]."', radi_tipo=2 ";
            if ($rs->fields["INST_CODI"] != 1) $sql .= ", radi_cuentai='".$rs->fields["RADI_NUME_TEXT"]."'";
        }
        $sql .= " where radi_nume_radi=".$rs->fields["RADI_NUME_RADI"];
        $this->db->conn->Execute($sql);

        // Registramos el histórico
        $this->insertarHistorico($rs->fields["RADI_NUME_RADI"], $usua_codi, $usua_codi, "Documento Firmado Electrónicamente", 40);	//Firma Digital
        $this->insertarHistorico($rs->fields["RADI_NUME_RADI"], $usua_codi, $usua_codi, "", 18); //Envío Electrónico
        if (trim($rs->fields["RADI_NUME_DERI"])!="") {
            $sql = "select radi_nume_radi, radi_nume_temp from radicado where radi_nume_radi=".$rs->fields["RADI_NUME_DERI"];
            $rs_padre = $this->db->conn->Execute($sql);
            if ($rs_padre) { // Registramos el histórico en el padre
                $this->insertarHistorico($rs_padre->fields["RADI_NUME_RADI"], str_replace('-','',$rs->fields["RADI_USUA_REM"]), str_replace('-','',$rs->fields["RADI_USUA_DEST"]), "Se envió electrónicamente el documento de respuesta No: ".$rs->fields["RADI_NUME_TEXT"], 37, $rs->fields["RADI_NUME_RADI"]);
                $this->insertarHistorico($rs_padre->fields["RADI_NUME_TEMP"], str_replace('-','',$rs->fields["RADI_USUA_REM"]), str_replace('-','',$rs->fields["RADI_USUA_DEST"]), "Se envió electrónicamente el documento de respuesta No: ".$rs->fields["RADI_NUME_TEXT"], 37, $rs->fields["RADI_NUME_RADI"]);
            }
        }
        if ($rs_usr->fields["INST_CODI"]!=1)
            $this->enviarMail($usua_codi, $usr_destino, $rs->fields["RADI_NUME_RADI"], "Documento Recibido");
        $radiNumeRadi = $rs->fields["RADI_NUME_RADI"];

        // Envio de correo electronico a la asistente si el usuario es jefe
        if($rs_usr->fields["CARGO_TIPO"]==1) {
            // Obtener datos de la asistente de area
            $datosAsistente = ObtenerJefeArea($rs_usr->fields["INST_CODI"], $rs_usr->fields["DEPE_CODI"], '2', $this->db);
            // Envio de correo de notificacion a la asistente que el Jefe de area a firmado un documento digitalmente. accion 2
            $mail_param["usuario"] = $usr_destino;
            $this->enviarMail($usua_codi, $datosAsistente["usua_codi"], $radiNumeRadi,'Documento Recibido','2', $mail_param);
        }
        $rs->MoveNext();
    }
    $sql = "select count(radi_nume_radi) as num from radicado where esta_codi=4 and radi_nume_temp=$radi_nume";
    $rs = $this->db->conn->query($sql);
    if ($rs->fields["NUM"]==0) {
        $sql = "update radicado set esta_codi=6 where radi_nume_radi=$radi_nume";
        $this->db->conn->Execute($sql);
    }
    return;
}


function envioManualDocumento($radicados, $observa)
{
	$respEnvio = "";
	if (trim($observa)!="") $observa .= "<br/>";
//	$rs = $this->db->conn->query("select inst_codi from usuarios where usua_codi=$usua_codi");
//	$inst_codi = $rs->fields["INST_CODI"];
	foreach($radicados as $radi_nume)
	{
	    $sql = "select r.radi_nume_radi, r.radi_nume_temp, r.radi_nume_text, r.radi_usua_rem, r.radi_usua_dest, u.usua_codi, u.usua_nombre
                        , u.inst_codi, u.inst_nombre, u.usua_esta, r.radi_nume_deri
		    from radicado r left outer join usuario u on replace(r.radi_usua_dest,'-','')::integer=u.usua_codi
		    where radi_nume_radi=$radi_nume";
/*            $sql = "select r.radi_nume_radi, r.radi_nume_temp, r.radi_nume_text,r.radi_usua_rem, r.radi_usua_dest
            ,(f_datos_usuarios(replace(radi_usua_dest,'-','')::integer)).usua_codi
            ,(f_datos_usuarios(replace(radi_usua_dest,'-','')::integer)).usua_nombre as usua_nombre,
            (f_datos_usuarios(replace(radi_usua_dest,'-','')::integer)).inst_codi as inst_codi,
            (f_datos_usuarios(replace(radi_usua_dest,'-','')::integer)).inst_nombre,
            (f_datos_usuarios(replace(radi_usua_dest,'-','')::integer)).usua_esta,
            r.radi_nume_deri from radicado r where
            radi_nume_radi=$radi_nume";*/
            //echo $sql;
	    $rs = $this->db->conn->query($sql);
	    if (!$rs->EOF) {                
                if ($rs->fields["INST_CODI"]==$_SESSION["inst_codi"] and $rs->fields["USUA_ESTA"]==1)
                    $cadena = "esta_codi=2, radi_usua_actu=".$rs->fields["USUA_CODI"];
                else
                    $cadena = "esta_codi=6";
                $sql = "update radicado set $cadena, radi_nomb_usua_firma=null, radi_fech_firma=null, radi_leido=0 where radi_nume_radi=$radi_nume";
                $this->db->conn->Execute($sql);

                $this->insertarHistorico($radi_nume, $_SESSION["usua_codi"], $_SESSION["usua_codi"], $observa, 19);
                $cadena = $observa . "Envío manual del documento al usuario ".$rs->fields["USUA_NOMBRE"];
                $this->insertarHistorico($rs->fields["RADI_NUME_TEMP"], $_SESSION["usua_codi"], $_SESSION["usua_codi"], $cadena, 19);
                $respEnvio .= $rs->fields["USUA_NOMBRE"].", ".$rs->fields["INST_NOMBRE"]."<br/>";
                if ($rs->fields["INST_CODI"]==$_SESSION["inst_codi"] || $rs->fields["INST_CODI"]==0) {
                    $this->enviarMail(str_replace("-","",$rs->fields["RADI_USUA_REM"]), $rs->fields["USUA_CODI"], $radi_nume, "Documento Recibido");
                }

                if (trim($rs->fields["RADI_NUME_DERI"])!="") {
                    $sql = "select radi_nume_radi, radi_nume_temp from radicado where radi_nume_radi=".$rs->fields["RADI_NUME_DERI"];
                    $rs_padre = $this->db->conn->Execute($sql);
                    if ($rs_padre) { // Registramos el histórico en el padre
                        $this->insertarHistorico($rs_padre->fields["RADI_NUME_RADI"], str_replace('-','',$rs->fields["RADI_USUA_REM"]), str_replace('-','',$rs->fields["RADI_USUA_DEST"]), "Se envió manualmente el documento de respuesta No: ".$rs->fields["RADI_NUME_TEXT"], 38, $rs->fields["RADI_NUME_RADI"]);
                        $this->insertarHistorico($rs_padre->fields["RADI_NUME_TEMP"], str_replace('-','',$rs->fields["RADI_USUA_REM"]), str_replace('-','',$rs->fields["RADI_USUA_DEST"]), "Se envió manualmente el documento de respuesta No: ".$rs->fields["RADI_NUME_TEXT"], 38, $rs->fields["RADI_NUME_RADI"]);
                    }
                }

                // Envio de correo electronico a la asistente si el usuario es jefe
                if($_SESSION['cargo_tipo']==1)
                {
                    // Obtener datos de la asistente de area
                    $datosAsistente = ObtenerJefeArea($_SESSION['inst_codi'], $_SESSION['depe_codi'], '2', $this->db);
                    // Envio de correo de notificacion a la asistente que el Jefe de area a firmado un documento digitalmente. accion 2
                    $mail_param["usuario"] = $rs->fields["USUA_CODI"];
                    $this->enviarMail(str_replace("-","",$rs->fields["RADI_USUA_REM"]), $datosAsistente["usua_codi"], $radi_nume,'Documento Recibido','2', $mail_param);
                }
	    }
	}
	return $respEnvio;
}


function reintentarEnvioElectronicoDocumento($radicados, $usua_codi, $observa, $file_firma = null, $password_firma = null)
{
	$respEnvio = "";
    //echo $file_firma;
	$respEnvio = $this->enviarDocumentosFirmaElectronica($radicados,$file_firma,$password_firma);
    
	foreach($radicados as $noRadicado)
	{
	    $this->insertarHistorico($noRadicado, $usua_codi, $usua_codi, $observa, 18);
	}
	return $respEnvio;
}

function enviarDocumentoElectronicoCiudadano ($radicados, $observa) {
    foreach($radicados as $radi_nume) {
        $sql = "select * from radicado where radi_nume_radi=$radi_nume";
        $rs = $this->db->conn->query($sql);
        if($rs->fields["ESTA_CODI"]==9) { //El documento debe estar en estado 9 (pendiente envio ciudadanos)
            $redirigido = 0+$rs->fields["RADI_USUA_REDIRIGIDO"];
            $destino = 0+str_replace("-", "", $rs->fields["RADI_USUA_DEST"]);
            // Redirigidos, valido que no se redirija al mismo destinatario
            if ($redirigido!=0 and $destino!=$redirigido) { //redirigido
                $this->insertarHistorico($radi_nume, $_SESSION["usua_codi"], $redirigido, "", 28); //Redirigir Documento
                $this->informar(array($radi_nume), $_SESSION["usua_codi"], $destino, "Documento externo dirigido a otro usuario.");
                $destino = $redirigido; // Para actualizar el radi_usua_actu
            }
            $this->insertarHistorico($rs->fields["RADI_NUME_RADI"], $_SESSION["usua_codi"], $destino, "", 18); //Envío Electrónico
            $this->enviarMail(str_replace("-", "", $rs->fields["RADI_USUA_REM"]), $destino, $radi_nume, "Documento Recibido");
            // Cambiamos el estado y el usuario actual
            $sql = "update radicado set esta_codi=2, radi_usua_actu=$destino where radi_nume_radi=$radi_nume";
            $this->db->conn->Execute($sql);
        }
    }
}

  function archivar($radicados, $usua_codi, $observa)
  {
    foreach ($radicados as $radi_nume) {
        $sql = "update radicado set
                radi_fech_agend=null
                ,esta_codi=0
                where RADI_NUME_RADI = $radi_nume";
        $this->db->conn->Execute($sql); # Ejecuta la modificacion
        $this->insertarHistorico($radi_nume, $usua_codi, $usua_codi, $observa, 13);
        // Cancelamos todas las tareas pendientes
        $this->cancelarTodasTareasEnviadas($radi_nume, "Se archivó el documento");
    }
    return "Archivo";
  }

  function noArchivar($radicados, $usua_codi, $observa)
  {
    foreach ($radicados as $radi_nume) {
        $estado = 2;
        if (substr($radi_nume,-1) !=1 ) $estado = 6;
        $isql = "update radicado set
                RADI_LEIDO=0
                ,radi_fech_agend=null
                ,esta_codi=$estado
                where RADI_NUME_RADI = $radi_nume";
        $this->db->conn->Execute($isql); # Ejecuta la modificacion
        $this->insertarHistorico($radi_nume, $usua_codi, $usua_codi, $observa, 25);
    }
    return "Archivo";
  }

  function asignarTareas($radicados, $usua_codi_dest, $fecha_max_tram, $comentario)
  {
    if ($usua_codi_dest == $_SESSION["usua_codi"]) return "<font color='#c90a0a'>Error: No se puede asignar una tarea al mismo usuario.</font>";
    $mensaje = "La tarea fue asignada y deber&aacute; ser ejecutada antes del $fecha_max_tram.";
    foreach($radicados as $radi_nume) {
//TODO: Validar que no tenga tareas asignadas y el documento no le pertenezca al jefe
        $sql = "select radi_usua_actu, (select count(tarea_codi) from tarea where radi_nume_radi=$radi_nume and estado=1) as num
                from radicado where radi_nume_radi=$radi_nume";
        $rs = $this->db->conn->Execute($sql);
        if ($rs->fields["RADI_USUA_ACTU"]==$_SESSION["usua_codi_jefe"] && $_SESSION["usua_codi_jefe"]!=$_SESSION["usua_codi"] && $rs->fields["NUM"]==0) {
            $this->reasignar(array($radi_nume), $_SESSION["usua_codi_jefe"], $_SESSION["usua_codi"], "Asignación de tareas desde bandeja compartida");
        }
//        $mensaje .= "Documento No. ". $rs->fields["RADI_NUME_TEXT"]."<br>";

        $sql = "select tarea_codi from tarea where radi_nume_radi=$radi_nume and estado=1 and usua_codi_dest=$usua_codi_dest";
        $rs = $this->db->conn->Execute($sql);
        if (!$rs or !$rs->EOF) {
            $mensaje = "<font color='#c90a0a'>Error: El usuario seleccionado ya tiene una tarea asignada, por favor, verifique.</font><br>";
        } else {
            $tarea_codi = $this->db->nextId("sec_tarea");
            $record["tarea_codi"] = $tarea_codi;
            $record["radi_nume_radi"] = $radi_nume;
            $record["fecha_inicio"] = $this->db->conn->sysTimeStamp;
            $record["fecha_maxima"] = "'$fecha_max_tram'::timestamp";
            $record["usua_codi_ori"] = $_SESSION["usua_codi"];
            $record["usua_codi_dest"] = $usua_codi_dest;
            $record["estado"] = "1";
            $record["leido"] = "0";
            $record["avance"] = "0";
            $sql = "select tarea_codi from tarea where radi_nume_radi=$radi_nume and estado=1 and usua_codi_dest=".$_SESSION["usua_codi"];
            $rs = $this->db->conn->Execute($sql);
            if (!$rs->EOF) $record["tarea_codi_padre"] = $rs->fields["TAREA_CODI"];
            $ok = $this->db->conn->Replace("tarea" ,$record, "", false, false, true, false);
            $this->insertarHistorico($radi_nume, $_SESSION["usua_codi"], $usua_codi_dest, $comentario, 50, $tarea_codi);
            $hist_codi = $this->insertarHistoricoTarea($tarea_codi, $radi_nume, $comentario, 50, $fecha_max_tram);
            $sql = "update tarea set comentario_inicio=$hist_codi where tarea_codi=$tarea_codi";
            $this->db->conn->Execute($sql);
//            $mensaje .= "&nbsp;&nbsp;&nbsp;&nbsp;Tarea Asignada<br>";
            $mail_param["fecha_maxima"] = $fecha_max_tram;
            $mail_param["comentario"] = $comentario;
            $this->enviarMail($_SESSION["usua_codi"], $usua_codi_dest, $radi_nume, "Tarea Asignada", "50", $mail_param);
        }
    }
    return $mensaje;
  }


  function finalizarTareas($tarea_codi, $comentario, $reasignar_respuesta=0)
  {
    $mensaje = "";
    $record = array();
    $sql = "select radi_nume_radi, usua_codi_ori, fecha_maxima from tarea where estado = 1 and tarea_codi=$tarea_codi and usua_codi_dest=".$_SESSION["usua_codi"];
    $rs = $this->db->conn->Execute($sql);
    if (!$rs or $rs->EOF) {
        $mensaje = "<font color='#c90a0a'>Error: No se encontro la tarea.</font><br>";
    } else {
        // Cancelamos las tareas hijas
        $sql = "select tarea_codi from tarea where estado=1 and tarea_codi_padre=$tarea_codi";
        $rsh = $this->db->conn->Execute($sql);
        while ($rsh and !$rsh->EOF) {
            $mensaje = $this->cancelarTareas($rsh->fields["TAREA_CODI"], $comentario, 1);
            $rsh->MoveNext();
        }

        $mensaje = "La tarea fue finalizada.".$mensaje;
        $record["tarea_codi"] = $tarea_codi;
        $record["estado"] = "2";
        $record["avance"] = "100";
        $record["fecha_fin"] = $this->db->conn->sysTimeStamp;
        $ok = $this->db->conn->Replace("tarea" ,$record, "tarea_codi", false, false, true, false);

        $hist_codi = $this->insertarHistoricoTarea($tarea_codi, $rs->fields["RADI_NUME_RADI"], $comentario, 51);
        $this->insertarHistorico($rs->fields["RADI_NUME_RADI"], $_SESSION["usua_codi"], $_SESSION["usua_codi"], $comentario, 51, $tarea_codi);
        $mail_param["tarea_codi"] = $tarea_codi;
        $mail_param["comentario"] = $comentario;
        $mail_param["fecha_maxima"] = substr($rs->fields["FECHA_MAXIMA"],0,10);
        $this->enviarMail($_SESSION["usua_codi"], $rs->fields["USUA_CODI_ORI"], $rs->fields["RADI_NUME_RADI"], "Tarea Finalizada", "51", $mail_param);

        // Reasignar respuestas
        if ($reasignar_respuesta == 1) {
            $sql = "select r.radi_nume_radi
                    from (select radi_nume_resp from tarea_radi_respuesta where tarea_codi=$tarea_codi) as tr
                    left outer join radicado r on tr.radi_nume_resp=r.radi_nume_radi where r.esta_codi=1 and r.radi_usua_actu=".$_SESSION["usua_codi"];
            $rsr = $this->db->conn->Execute($sql);
            while ($rsr and !$rsr->EOF) {
                $this->reasignar( array($rsr->fields["RADI_NUME_RADI"]), $_SESSION["usua_codi"], $rs->fields["USUA_CODI_ORI"], $comentario);
                $rsr->MoveNext();
            }
        }
    }
    return $mensaje;
  }

  function cancelarTareas($tarea_codi, $comentario, $forzar=0)
  {
    // Cancelamos las tareas hijas
    $mensaje = "";
    $sql = "select tarea_codi from tarea where estado=1 and tarea_codi_padre=$tarea_codi";
    $rs = $this->db->conn->Execute($sql);
    while ($rs and !$rs->EOF) {
        $mensaje = $this->cancelarTareas($rs->fields["TAREA_CODI"], $comentario, ($forzar+1));
        $rs->MoveNext();
    }

    $mensaje = "La tarea fue cancelada.".$mensaje;
    if ($forzar!=0) $mensaje = "<br>Fueron canceladas otras tareas dependientes.";
    $record = array();
    $sql = "select radi_nume_radi, usua_codi_dest, fecha_maxima from tarea where estado = 1 and tarea_codi=$tarea_codi";
    if ($forzar==0) $sql .=" and usua_codi_ori=".$_SESSION["usua_codi"];
    $rs = $this->db->conn->Execute($sql);
    if (!$rs or $rs->EOF) {
        $mensaje = "<font color='#c90a0a'>Error: No se encontro la tarea.</font>";
    } else {
        $record["tarea_codi"] = $tarea_codi;
        $record["estado"] = "3";
        $record["fecha_fin"] = $this->db->conn->sysTimeStamp;
//        $record["avance"] = "100";
        $ok = $this->db->conn->Replace("tarea" ,$record, "tarea_codi", false, false, true, false);

        $hist_codi = $this->insertarHistoricoTarea($tarea_codi, $rs->fields["RADI_NUME_RADI"], $comentario, 52);
        $this->insertarHistorico($rs->fields["RADI_NUME_RADI"], $_SESSION["usua_codi"], $_SESSION["usua_codi"], $comentario, 52, $tarea_codi);
        $mail_param["tarea_codi"] = $tarea_codi;
        $mail_param["comentario"] = $comentario;
        $mail_param["fecha_maxima"] = substr($rs->fields["FECHA_MAXIMA"],0,10);
        $this->enviarMail($_SESSION["usua_codi"], $rs->fields["USUA_CODI_DEST"], $rs->fields["RADI_NUME_RADI"], "Tarea Cancelada", "52", $mail_param);
    }
    return $mensaje;
  }


  function comentarTareas($tarea_codi, $comentario)
  {
    $mensaje = "Se a&ntilde;adi&oacute; un comentario a la tarea.";
    $sql = "select radi_nume_radi, usua_codi_ori, usua_codi_dest from tarea where tarea_codi=$tarea_codi";
    $rs = $this->db->conn->Execute($sql);
    if (!$rs or $rs->EOF) {
        $mensaje = "<font color='#c90a0a'>Error: No se encontro la tarea.</font>";
    } else {
        $hist_codi = $this->insertarHistoricoTarea($tarea_codi, $rs->fields["RADI_NUME_RADI"], $comentario, 53);

        $mail_param["tarea_codi"] = $tarea_codi;
        $mail_param["comentario"] = $comentario;
        $usua_dest = $rs->fields["USUA_CODI_DEST"];
        if ($rs->fields["USUA_CODI_DEST"] == $_SESSION["usua_codi"]) $usua_dest = $rs->fields["USUA_CODI_ORI"];
        $this->enviarMail($_SESSION["usua_codi"], $usua_dest, $rs->fields["RADI_NUME_RADI"], "Tarea Comentada", "53", $mail_param);
    }
    return $mensaje;
  }

function reabrirTareas($tarea_codi, $fecha_max_tram, $comentario)
  {
    $mensaje = "Se reabri&oacute; la tarea para que sea ejecutada hasta $fecha_max_tram.";
    $record = array();
    $sql = "select radi_nume_radi, usua_codi_dest from tarea where tarea_codi=$tarea_codi and estado in (2,3) and usua_codi_ori=".$_SESSION["usua_codi"];
    $rs = $this->db->conn->Execute($sql);
    if (!$rs or $rs->EOF) {
        $mensaje = "<font color='#c90a0a'>Error: No se encontro la tarea.</font>";
    } else {
        $record["tarea_codi"] = $tarea_codi;
        $record["estado"] = "1";
        $record["avance"] = "0";
        $record["fecha_maxima"] = "'$fecha_max_tram'::timestamp";
        $record["fecha_fin"] = "null";
        $ok = $this->db->conn->Replace("tarea" ,$record, "tarea_codi", false, false, true, false);

        $hist_codi = $this->insertarHistoricoTarea($tarea_codi, $rs->fields["RADI_NUME_RADI"], $comentario, 54, $fecha_max_tram);

        $mail_param["tarea_codi"] = $tarea_codi;
        $mail_param["comentario"] = $comentario;
        $mail_param["fecha_maxima"] = $fecha_max_tram;
        $this->enviarMail($_SESSION["usua_codi"], $rs->fields["USUA_CODI_DEST"], $rs->fields["RADI_NUME_RADI"], "Tarea Reabierta", "54", $mail_param);
    }
    return $mensaje;
  }

function editarTareas($tarea_codi, $fecha_max_tram, $comentario)
  {
    $fechaDebe=$this->buscarFechaTareaHija($tarea_codi,$fecha_max_tram,0);
    if ($this->buscarFechaTareaHija($tarea_codi,$fecha_max_tram,1)==1)
    $mensaje = "Se modific&oacute; la fecha m&aacute;xima para que se ejecute la tarea hasta $fecha_max_tram.";
    else
        $mensaje ="<font color='red'>No se actualizó la fecha, existen tareas hijas mayores a la fecha
            seleccionada (Fecha sugerida mayor a $fechaDebe)</font>";
    $record = array();
    $sql = "select radi_nume_radi, usua_codi_dest, comentario_inicio from tarea where tarea_codi=$tarea_codi and estado=1 and usua_codi_ori=".$_SESSION["usua_codi"];
    $rs = $this->db->conn->Execute($sql);
    if (!$rs or $rs->EOF) {
        $mensaje = "<font color='#c90a0a'>Error: No se encontro la tarea.</font><br>";
    } else {
        if ($this->buscarFechaTareaHija($tarea_codi,$fecha_max_tram,1)==1) 
        $hist_codi = $this->insertarHistoricoTarea($tarea_codi, $rs->fields["RADI_NUME_RADI"], $comentario, 55, $fecha_max_tram);
        else{
            
            $hist_codi = $this->insertarHistoricoTarea($tarea_codi, $rs->fields["RADI_NUME_RADI"], "La fecha debe ser mayor a $fechaDebe", 55, $fecha_max_tram);
        }
        $record["tarea_codi"] = $tarea_codi;
        $record["fecha_maxima"] = "'$fecha_max_tram'::timestamp";        
        $record["comentario_inicio"] = $hist_codi;    
        if ($this->buscarFechaTareaHija($tarea_codi,$fecha_max_tram,1)==1)                
        $ok = $this->db->conn->Replace("tarea" ,$record, "tarea_codi", false, false, true, false);
        $mail_param["comentario_inicio"] = $rs->fields["COMENTARIO_INICIO"];
        $mail_param["comentario"] = $comentario;
        $mail_param["fecha_maxima"] = $fecha_max_tram;
        $this->enviarMail($_SESSION["usua_codi"], $rs->fields["USUA_CODI_DEST"], $rs->fields["RADI_NUME_RADI"], "Tarea Modificada", "55", $mail_param);
    }
    return $mensaje;
  }
  //
  //busca la fecha de las tareas hijas para modificar la fecha de la tarea padre
function buscarFechaTareaHija($tarea_codi,$fecha_maxima_tram,$tipo){
    $sql = "select * from tarea where tarea_codi_padre = $tarea_codi";
    //echo $sql;
    $rs=$this->db->conn->query($sql);
    while(!$rs->EOF){
        $fechaMaxima=substr($rs->fields['FECHA_MAXIMA'],0,10);
        if($fechaMaxima<$fechaMaximaNext)
            $fechaFinal=$fechaMaximaNext;
        else 
             $fechaFinal=$fechaMaxima;       
            
            $fechaMaximaNext=substr($rs->fields['FECHA_MAXIMA'],0,10);
            $rs->MoveNext();
    }
    if ($tipo==1){        
        if ($fechaFinal<$fecha_maxima_tram)
            return 1;
        else
            return 0;
    }
    else
        return $fechaFinal;
     
     
}
  //


  function registrarAvanceTareas($tarea_codi, $tarea_avance, $comentario, $reasignar_respuesta=0)
  {
    $tarea_avance = 0+$tarea_avance;
    $mensaje = "Se registr&oacute; un avance en la tarea del $tarea_avance%.";
    $record = array();
    $sql = "select radi_nume_radi from tarea where tarea_codi=$tarea_codi and estado=1 and usua_codi_dest=".$_SESSION["usua_codi"];
    $rs = $this->db->conn->Execute($sql);
    if (!$rs or $rs->EOF) {
        $mensaje = "<font color='#c90a0a'>Error: No se encontro la tarea.</font><br>";
    } else {
        $record["tarea_codi"] = $tarea_codi;
        $record["avance"] = $tarea_avance;
        $ok = $this->db->conn->Replace("tarea" ,$record, "tarea_codi", false, false, true, false);
        $comentario .= " Avance: $tarea_avance%";
        $hist_codi = $this->insertarHistoricoTarea($tarea_codi, $rs->fields["RADI_NUME_RADI"], $comentario, 55, $tarea_avance);
        if ($tarea_avance==100) {
            $mensaje .= "<br>".$this->finalizarTareas($tarea_codi, "", $reasignar_respuesta);
        }
//        $this->enviarMail($usua_codi, $usua_dest, $radi_nume, "Informados");
    }
    return $mensaje;
  }

  function cambiarPropietarioTareas($radi_nume, $usua_dest, $usua_ori)
  {
    $rs = $this->db->conn->Execute("select usua_nombre from usuario where usua_codi=$usua_dest");
    $usua_nombre = $rs->fields["USUA_NOMBRE"];

    $mensaje = "Se cambi&oacute; propietario de la tarea.";
    $sql = "select tarea_codi from tarea where radi_nume_radi=$radi_nume and usua_codi_ori=".$usua_ori;
    $rs = $this->db->conn->Execute($sql);
    if (!$rs or $rs->EOF) {
        $mensaje = "<font color='#c90a0a'>Error: No se encontro la tarea.</font><br>";
    } else {
        while (!$rs->EOF) {
            $record["tarea_codi"] = $rs->fields["TAREA_CODI"];
            $record["usua_codi_ori"] = $usua_dest;
            $ok = $this->db->conn->Replace("tarea" ,$record, "tarea_codi", false, false, true, false);
            $comentario = "El documento fue reasignado a $usua_nombre";
            $hist_codi = $this->insertarHistoricoTarea($rs->fields["TAREA_CODI"], $radi_nume, $comentario, 57, $tarea_avance);
            $rs->MoveNext();
        }
    }
    return $mensaje;
  }
   /**
   * Funcion que permite asignar las tareas de los documentos de un usuario a otro
   * @autor David Gamboa, snap, 2014-02-06
   * @param array $radi_nume
   * @param integer $usua_dest->nuevo dueño de la tarea->subrogante
   * @param integer $usua_ori->usuario subrogado
   * @return string
   */
  
  function cambiarPropietarioTareasSubrogacion($radicados, $usua_dest, $usua_ori,$tipo=1)
  {
    $rs = $this->db->conn->Execute("select usua_nombre from usuario where usua_codi=$usua_dest");
    $usua_nombre = $rs->fields["USUA_NOMBRE"];

    $mensaje = "Se cambi&oacute; propietario de la tarea.";
    foreach($radicados as $radi_nume) {
        $sql = "select tarea_codi from tarea where radi_nume_radi=$radi_nume 
            and usua_codi_dest=".$usua_ori;
        //echo $sql;
        $rs = $this->db->conn->Execute($sql);
        if (!$rs or $rs->EOF) {
            $mensaje = "<font color='#c90a0a'>Error: No se encontro la tarea.</font><br>";
        } else {        
            while (!$rs->EOF) {
                $record["tarea_codi"] = $rs->fields["TAREA_CODI"];
                $record["usua_codi_dest"] = $usua_dest;                
                $ok = $this->db->conn->Replace("tarea" ,$record, "tarea_codi", false, false, true, false);
                if ($tipo==1)
                    $subrogacion="Por Subrogación";
                else
                $subrogacion="Por desactivación de Subrogación.";
                $comentario = "El documento fue reasignado a $usua_nombre, $subrogacion";
                $hist_codi = $this->insertarHistoricoTarea($rs->fields["TAREA_CODI"], $radi_nume, $comentario, 57, $tarea_avance);
                $rs->MoveNext();
            }
        }
    }
    return $mensaje;
  }
  /* Función que cambia el usuario de las tareas enviadas   
   * $usuario_actual: Código del usuario actual
   * $usuario_nuevo: Código del usuario nuevo
   */
  function cambiarUsuarioTareasEnviadas($usuario_actual, $usuario_nuevo)
  {
    $rs = $this->db->conn->Execute("select usua_nombre from usuario where usua_codi=$usuario_nuevo");
    $usua_nombre = $rs->fields["USUA_NOMBRE"];

    //Cambio de usuario de tareas enviadas
    $mensaje = "Se cambi&oacute; propietario de la tarea enviada por inactivación de usuario.";
    $sql = "select * from tarea where usua_codi_ori=$usuario_actual";   
    $rs = $this->db->conn->Execute($sql);
    while (!$rs->EOF) {
        $radi_nume = $rs->fields["RADI_NUME_RADI"];
        $tarea_codi = $rs->fields["TAREA_CODI"];        
        $record["tarea_codi"] = $rs->fields["TAREA_CODI"];
        $record["usua_codi_ori"] = $usuario_nuevo;
        $ok = $this->db->conn->Replace("tarea" ,$record, "tarea_codi", false, false, true, false);
        $comentario = "La tarea enviada fue asignada a $usua_nombre";
        $hist_codi = $this->insertarHistoricoTarea($tarea_codi, $radi_nume, $comentario, 59, $tarea_avance);
        $rs->MoveNext();
    }
    
    return $mensaje;
  }

  /* Función que cambia el usuario de las tareas recibidas  
   * $usuario_actual: Código del usuario actual
   * $usuario_nuevo: Código del usuario nuevo
   */
  function cambiarUsuarioTareasRecibidas($usuario_actual, $usuario_nuevo)
  {
    $rs = $this->db->conn->Execute("select usua_nombre from usuario where usua_codi=$usuario_nuevo");
    $usua_nombre = $rs->fields["USUA_NOMBRE"];

    //Cambio de usuario de tareas recibidas
    $mensaje = "Se cambi&oacute; propietario de la tarea recibida por inactivación de usuario.";
    $sql = "select * from tarea where usua_codi_dest=$usuario_actual";
    $rs = $this->db->conn->Execute($sql);
    while (!$rs->EOF) {
        $radi_nume = $rs->fields["RADI_NUME_RADI"];
        $tarea_codi = $rs->fields["TAREA_CODI"];       
        $record["tarea_codi"] = $rs->fields["TAREA_CODI"];
        $record["usua_codi_dest"] = $usuario_nuevo;
        $ok = $this->db->conn->Replace("tarea" ,$record, "tarea_codi", false, false, true, false);
        $comentario = "La tarea recibida fue asignada a $usua_nombre";
        $hist_codi = $this->insertarHistoricoTarea($tarea_codi, $radi_nume, $comentario, 59, $tarea_avance);
        $rs->MoveNext();
    }

    return $mensaje;
  }

  /* Función que cambia el usuario de las tareas recibidas
   * $usuario_actual: Código del usuario actual
   * $usuario_nuevo: Código del usuario nuevo
   */
  function cancelarTodasTareasEnviadas($radi_nume, $comentario)
  {
      // Cancelamos todas las tareas pendientes
      $sql = "select tarea_codi from tarea where radi_nume_radi=$radi_nume and estado=1 order by tarea_codi desc";
      $rst = $this->db->conn->Execute($sql);
      while (!$rst->EOF) {
          $this->cancelarTareas($rst->fields["TAREA_CODI"], $comentario, 1);
          $rst->MoveNext();
      }
      return;
  }


  function registrarDocumentoRespuestaTareas($radi_nume, $radi_respuesta, $comentario)
  {
    $sql = "select tarea_codi from tarea where radi_nume_radi=$radi_nume and estado=1 and usua_codi_dest=".$_SESSION["usua_codi"];
    $rs = $this->db->conn->Execute($sql);
    if (!$rs or $rs->EOF) {
        return 0;
    } else {
        $record = array();
        $record["tarea_codi"] = $rs->fields["TAREA_CODI"];
        $record["radi_nume_radi"] = $radi_nume;
        $record["radi_nume_resp"] = $radi_respuesta;
        $ok = $this->db->conn->Replace("tarea_radi_respuesta" ,$record, "", false, false, false, false);
//        $comentario = "Se registr&oacute; el documento de respuesta No. " . $comentario;
        $hist_codi = $this->insertarHistoricoTarea($rs->fields["TAREA_CODI"], $radi_nume, $comentario, 58, $radi_respuesta);
        return 1;
    }
    return 0;
  }

    /**
     * Recuperar documentos reasignados
     */
  //$radicados al que se ejecuta la accion
  //$usua_codi_ori usuario que ejecuta la accion
  function recuperarReasignado($radicados, $usua_codi_ori,$observa)
  {
      $fecha_tramite = date("Y-m-d");
    foreach ($radicados as $radi_nume) {        
        $sqlr = "select radi_usua_actu,radi_usua_ante, radi_usua_rem, esta_codi 
        from radicado where radi_nume_radi=$radi_nume";
        //echo $sqlr;
        $rsr=$this->db->conn->Execute($sqlr);
        if (!$rsr->EOF){
            $usua_actu = $rsr->fields['RADI_USUA_ACTU'];//usuario actual del documento
            //$usua_ante = $rsr->fields['RADI_USUA_ANTE'];           
            $estadoDoc = $rsr->fields['ESTA_CODI'];//estado del documento
        }
        //busco el último evento de reasignacion
        
        $sql = "select max(hist_codi) as hist_codi 
        from hist_eventos where radi_nume_radi = $radi_nume and sgd_ttr_codigo = 9 and usua_codi_ori = ".$_SESSION['usua_codi'];
        //echo $sql;
        $rs=$this->db->conn->Execute($sql);
        if (!$rs->EOF){
            $hist_codi = $rs->fields['HIST_CODI'];
            if ($hist_codi!=''){//busco el usuario destino del evento de la reasignación
                $sqlh = "select usua_codi_dest,usua_codi_ori from hist_eventos where hist_codi = $hist_codi";
                $rsh=$this->db->conn->Execute($sqlh);
                if (!$rsh->EOF){
                    $re_usua_codi_dest = $rsh->fields['USUA_CODI_DEST'];
                    $re_usua_codi_ori = $rsh->fields['USUA_CODI_ORI'];
                }
            }
        }
        /**
         * verificar si el usuario actual del documento es igual al
         * ultimo del evento de reasignacion.
        */        
        //recuperar si son iguales, usuario destino y usuario actual, ademas que el documento
        //este en estado en Tramite
        $estadoUsrIn = 0;
        $datosUsrIn = ObtenerDatosUsuario($re_usua_codi_dest, $this->db);
        $estadoUsrIn = $datosUsrIn['usua_estado'];
        //Remitente
        $sql = "select usua_esta from usuarios where usua_codi = ".str_replace("-","",$usua_actu);        
        //echo $sql;
        $rsrem = $this->db->conn->Execute($sql);  
        
        if ($estadoDoc == 2 || $estadoDoc == 1){
            $sqlUp ="update radicado set radi_usua_actu = $re_usua_codi_ori";
            if ($rsrem->fields['USUA_ESTA']!='')//evitar errores de documentos externos
            $sqlUp.=" , radi_usua_ante = ".str_replace("-","",$usua_actu);
            $sqlUp.=" , radi_fech_asig=to_timestamp('$fecha_tramite', 'YYYY-MM-DD')";
            $sqlUp.=" where radi_nume_radi = $radi_nume";
            //volver el documento al usuario
            
            if ($re_usua_codi_dest==$usua_actu){                
                $this->db->conn->Execute($sqlUp);                
                $mensaje=$observa."<br> Se recuperó el documento desde Reasignación";                
                $this->insertarHistorico($radi_nume, $usua_codi_ori, $usua_codi_ori, $mensaje, 83);
                //recuperar tareas
                $this->recuperarTareas($radi_nume,$_SESSION["usua_codi"]);
                
            }else{                
                if($estadoUsrIn==0){
                    $mensaje=$observa."<br> Se recuperó el documento desde Reasignación (de un usuario Subrogante o Inactivo)";                   
                    $this->db->conn->Execute($sqlUp);                   
                    $this->insertarHistorico($radi_nume, $usua_codi_ori, $usua_codi_ori, $mensaje, 83);
                    //recuperar tareas
                    $this->recuperarTareas($radi_nume,$_SESSION["usua_codi"]);
                }
                elseif($_SESSION['usua_codi']==$usua_actu)
                    $mensaje="<font color='blue'>El documento ya fue recuperado, favor revise la bandeja de Recibidos</font>";
                    else                                
                    $mensaje="<font color='red'>No se puede ejecutar esta acción, ya que el documento se está procesando</font>";
            }            
            
        }else{
            $mensaje="<font color='red'>No se puede ejecutar esta acción, ya que el documento se está procesando</font>";
        }
    }
    return $mensaje;
  }
  /*
   * Buscar las tareas involucradas del documento con la persona de session
   */
  function recuperarTareas($radi_nume_radi,$usua_codi_ori){
      //busco la transaccion de tareas del usuario a recuperar (esta en sesion)
      $sql = "select * from hist_eventos where usua_codi_ori = $usua_codi_ori 
      and radi_nume_radi = $radi_nume_radi and sgd_ttr_codigo = 50";
      //echo $sql;
      $rs = $this->db->conn->Execute($sql);
      
      while (!$rs->EOF) {
          $ttr_codigo = $rs->fields["HIST_REFERENCIA"];  
          $this->actualizarTareaRecuperar($ttr_codigo,$usua_codi_ori,$radi_nume_radi);
          $rs->MoveNext();
      }
  }
  /*
   * Al recuperar el documento desde tareas
   */
  function actualizarTareaRecuperar($ttr_codigo,$usua_codi_ori,$radi_nume){
      $fecha_tramite = date("Y-m-d");
      $recTarea = array();
      unset($recTarea);
      $recTarea["tarea_codi"] = $ttr_codigo;      
      $recTarea["usua_codi_ori"] = $usua_codi_ori;      
      //print_r($recTarea);
      $this->db->conn->Replace("tarea" ,$recTarea, "tarea_codi", false, false, true, false);
      $comentario = "Se cambió propietario de la tarea.";
      //insertar historico tareas
      $this->insertarHistoricoTarea($ttr_codigo, $radi_nume, $comentario, 59, $tarea_avance);
  }
  /*
   * Funcion asociar a carpetas virtuales
   * radicadoSerl: listado de radicados
   * usua_codi: usuario quien realiza la accion
   * depe_codi: dependencia de usuario en sesion
   * trd_codigo: carpeta virtual a actualizar
   * observa: observaciones
   */
  function AsoCarpetasVirtuales($radicadosSel,$usua_codi,$depe_codi,$trd_codigo,$observa){
              $fecha_tramite = date("Y-m-d");
       //consultar la carpeta
      
    foreach ($radicadosSel as $radi_nume) {
         $record["FECHA"] = "to_timestamp('$fecha_tramite', 'YYYY-MM-DD')";
         $record["USUA_CODI"] = $usua_codi;
         $record["DEPE_CODI"] = $depe_codi;
         $record["TRD_CODI"] = $trd_codigo;
         $record["RADI_NUME_RADI"] = $radi_nume;
         
         $sql2="select fecha, trd_codi from trd_radicado 
         where radi_nume_radi = $radi_nume";
         $rs2 = $this->db->conn->Execute($sql2);
         //si el documento tiene registro        
                if (!$rs2->EOF){                    
                    $carpetaAnterior = $rs2->fields['TRD_CODI'];
                    if ($carpetaAnterior!=$trd_codigo){
                        $where[]="RADI_NUME_RADI";
                        $where[]="DEPE_CODI";
                        $mensaje = "Se actualiza la carpeta de ".$this->nombreCarpeta($carpetaAnterior)." a la carpeta ".$this->nombreCarpeta($trd_codigo);
                        $ok = $this->db->conn->Replace("TRD_RADICADO", $record, $where, false,false,true,false);
                        $this->insertarHistorico($radi_nume, $usua_codi, $usua_codi, $mensaje, 88);
                    }
                }else{//si no tiene registro
                    $mensaje = "Incluir documento en carpeta: ".$this->nombreCarpeta($trd_codigo);
                    $where = "";
                    $ok = $this->db->conn->Replace("TRD_RADICADO", $record, $where, false,false,true,false);
                    $this->insertarHistorico($radi_nume, $usua_codi, $usua_codi, $mensaje, 88);
                }
              
         }
         if (trim($mensaje)=='')
             $mensaje="EL Documento ya está incluido en la carpeta virtual ".$this->nombreCarpeta($trd_codigo);
    return $mensaje;
  }
  /*
   * Busqueda de nombre de carpeta
   */
  function nombreCarpeta($trd_codigo){
       $sql= "select * from trd where trd_codi = $trd_codigo";       
       $rs = $this->db->conn->Execute($sql);
       if (!$rs->EOF){
           $nombreCarpeta = $rs->fields['TRD_NOMBRE'];
       }  else {
           $nombreCarpeta="";
       }
       return $nombreCarpeta;
  }
    /**
    * Metodo que sirve para envio de mail.
    *
    * @param int $remitente quien envia el mail.
    * @param int $destinatario a quien se envia el mail.
    * @param string $asunto descripcion pequeña del mail.
    * @param string $desc texto contenido del mail.
    * @return confirmación.
    */
    function enviarMail($remitente, $destinatario, $radi_nume, $nombre_accion="", $accion="0", $parametros = array())
    {
        if ($remitente == $destinatario) return;
        $ruta_raiz = $this->db->rutaRaiz;
        include "$ruta_raiz/config.php";
        include_once "$ruta_raiz/obtenerdatos.php";		//Consulta de datos de los usuarios y radicados

        if (ObtenerPermisoUsuario($destinatario, 21, $this->db) == 0) return;
        $dest = ObtenerDatosUsuario ($destinatario, $this->db);

        if($dest["email"]!="" and strpos($dest["email"],"@") and strpos($dest["email"],".",strpos($dest["email"],"@")))
        {
            /**
            * Estructura de la descripcion de email.
            */
            $rem = ObtenerDatosUsuario ($remitente, $this->db);
            $radicado = ObtenerDatosRadicado ($radi_nume, $this->db);

            $asunto = " - ".$radicado["radi_asunto"];
            $mail_body = "<html><title>Informaci&oacute;n Quipux</title>";
            $mail_body .= "<body><center><h2>Sistema de Gesti&oacute;n Documental Quipux</h2><br><br></center>";
            $mail_body .= "Estimado(a):<br><br>".$dest["abr_titulo"] . " " . $dest["nombre"] . "<br>" . $dest["cargo"]. "<br><br>";


            switch ($accion)
            {
                case '0':
                    if (!isset ($parametros["bandeja"])) $parametros["bandeja"] = ($radicado["estado"]==1) ? "en Elaboraci&oacute;n" : "Recibidos";
                    if (!isset ($parametros["enviado_por"])) $parametros["enviado_por"] = "Remitente:";

                    $mail_body .= "Ha recibido un documento en el sistema, por favor revise su bandeja de Documentos ".$parametros["bandeja"].
                              " ingresando a &quot;<a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>&quot;";

                    if ($dest["tipo_usuario"]==2)
                        $mail_body .= " con el usuario: &quot;".$dest["cedula"]."&quot;";
                    $mail_body .= "<br><br>Informaci&oacute;n del Documento:<br><br>";
                    $mail_body .= "<table border='0' width='100%'><tr><td width='30%'><b>Fecha:</b></td><td width='70%'>".date("Y-m-d H:i:s")."</td></tr>";
                    $mail_body .= "<tr><td><b>No. de Documento:</b></td><td>".$radicado["radi_nume_text"]."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Asunto:</b></td><td>".$radicado["radi_asunto"]."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>".$parametros["enviado_por"]."</b></td><td>".$rem["abr_titulo"] . " " . $rem["nombre"] .
                                  "<br>" . $rem["cargo"] . "<br>" . $rem["institucion"]."</td></tr></table>";
                         // "<br><a href='mailto:" . $rem["email"]. "'>" . $rem["email"]. "</a></td></tr></table>";
                    break;
                case '9':
                    if (!isset ($parametros["bandeja"])) $parametros["bandeja"] = ($radicado["estado"]==1) ? "en Elaboraci&oacute;n" : "Recibidos";
                    if (!isset ($parametros["enviado_por"])) $parametros["enviado_por"] = "Remitente:";
                    $asunto = "";

                    $mail_body .= "Ha recibido varios documentos en el sistema, por favor revise su bandeja de Documentos ".$parametros["bandeja"].
                                  " ingresando a &quot;<a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>&quot;";
                    $mail_body .= "<br><br><table border='0' width='100%'><tr><td width='30%'><b>Fecha:</b></td><td width='70%'>".date("Y-m-d H:i:s")."</td></tr>";
                    $mail_body .= "<tr><td><b>No. de Documentos:</b></td><td>".$parametros["num_docs"]."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>".$parametros["enviado_por"]."</b></td><td>".$rem["abr_titulo"] . " " . $rem["nombre"] .
                                  "<br>" . $rem["cargo"] . "<br>" . $rem["institucion"]."</td></tr></table>";
                         // "<br><a href='mailto:" . $rem["email"]. "'>" . $rem["email"]. "</a></td></tr></table>";
                    break;
                case '1': // Envio de mail para el Jefe de área cuando un documento de su bandeja de recibidos ha sido tomado.
                    $nombre_accion = "Bandeja Compartida";
                    $mail_body .= "Un Documento ha sido tomado de su bandeja de Documentos Recibidos del sistema &quot;<a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>&quot;";
                    $mail_body .= "<br><br>Informaci&oacute;n del Documento:<br><br>";
                    $mail_body .= "<table border='0' width='100%'><tr><td width='30%'><b>Fecha:</b></td><td width='70%'>".date("Y-m-d H:i:s")."</td></tr>";
                    $mail_body .= "<tr><td><b>No. de Documento:</b></td><td>".$radicado["radi_nume_text"]."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Asunto:</b></td><td>".$radicado["radi_asunto"]."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Tomado por:</b></td><td>".$rem["abr_titulo"] . " " . $rem["nombre"] .
                                  "<br>" . $rem["cargo"] . "<br>" . $rem["institucion"].
                                  "<br><a href='mailto:" . $rem["email"]. "'>" . $rem["email"]. "</a></td></tr></table>";
                    $mail_body .= "<br><br>Si desea ver el contenido del documento, por favor, revise su Bandeja de Documentos Reasignados.";
                    break;
                case '1A': // Envio de mail para el Jefe de área cuando un documento de su bandeja de recibidos ha sido tomado.
                    $nombre_accion = "Bandeja Compartida";
                    $asunto = "";
                    $mail_body .= "Varios Documentos han sido tomados de su bandeja de Documentos Recibidos del sistema &quot;<a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>&quot;";
                    $mail_body .= "<br><br><table border='0' width='100%'><tr><td width='30%'><b>Fecha:</b></td><td width='70%'>".date("Y-m-d H:i:s")."</td></tr>";
                    $mail_body .= "<tr><td><b>No. de Documentos:</b></td><td>".$parametros["num_docs"]."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Tomado por:</b></td><td>".$rem["abr_titulo"] . " " . $rem["nombre"] .
                                  "<br>" . $rem["cargo"] . "<br>" . $rem["institucion"].
                                  "<br><a href='mailto:" . $rem["email"]. "'>" . $rem["email"]. "</a></td></tr></table>";
                    $mail_body .= "<br><br>Si desea ver el contenido de los documentos, por favor, revise su Bandeja de Documentos Reasignados.";
                    break;
                case '2': // Envio de mail a la asistente cuando el Jefe de área ha firmado un documento digitalmente
                    //Obtener los datos del destinatario del documento
                    $nombre_accion = "Documento Firmado";
                    $destRadi = ObtenerDatosUsuario ($parametros["usuario"], $this->db);

                    $mail_body .= "Documento enviado al jefe de &Aacute;rea, por favor revise su bandeja compartida ingresando a &quot;<a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>&quot;";
                    $mail_body .= "<br><br>Informaci&oacute;n del Documento:<br><br>";
                    $mail_body .= "<table border='0' width='100%'><tr><td width='30%'><b>Fecha:</b></td><td width='70%'>".date("Y-m-d H:i:s")."</td></tr>";
                    $mail_body .= "<tr><td><b>No. de Documento:</b></td><td>".$radicado["radi_nume_text"]."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Asunto:</b></td><td>".$radicado["radi_asunto"]."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Remitente:</b></td><td>".$rem["abr_titulo"] . " " . $rem["nombre"] .
                                  "<br>" . $rem["cargo"] . "<br>" . $rem["institucion"].
                                  "<br><a href='mailto:" . $rem["email"]. "'>" . $rem["email"]. "</a></td></tr>".
                                  "</table>";
                    $mail_body .= "<br><br> Si desea ver m&aacute;s informaci&oacute;n, por favor, buscar el documento en la opci&oacute;n \"B&uacute;squeda Avanzada\".";
                    break;
                case '21': // comentar Documento
                    $mail_body .= "Han realizado un comentario en uno de sus documentos, por favor revise el documento ingresando a &quot;<a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>&quot;";
                    $mail_body .= "<br><br>Informaci&oacute;n del documento:<br><br>";
                    $mail_body .= "<table border='0' width='100%'><tr><td width='30%'><b>Fecha:</b></td><td width='70%'>".date("Y-m-d H:i:s")."</td></tr>";
                    $mail_body .= "<tr><td><b>Comentario:</b></td><td>".$parametros["comentario"]."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Comentario realizado por:</b></td><td>".$rem["abr_titulo"] . " " . $rem["nombre"] .
                                  "<br>" . $rem["cargo"] . "<br><a href='mailto:" . $rem["email"]. "'>" . $rem["email"]. "</a></td></tr>";
                    $mail_body .= "<tr><td><b>No. de documento:</b></td><td>".$radicado["radi_nume_text"]."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Asunto del documento:</b></td><td>".$radicado["radi_asunto"]."</td></tr></table>";
                    //$mail_body .= "<br><br>Por favor revise el documento en el sistema &quot;<a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>&quot;";
                    break;
                case '50': // Asignar nueva Tarea
                    $mail_body .= "Le han asignado una nueva tarea.";
                    $mail_body .= "<br><br>Informaci&oacute;n de la tarea:<br><br>";
                    $mail_body .= "<table border='0' width='100%'><tr><td width='30%'><b>Fecha:</b></td><td width='70%'>".date("Y-m-d H:i:s")."</td></tr>";
                    $mail_body .= "<tr><td><b>Tarea asignada:</b></td><td>".$parametros["comentario"]."</td></tr>";
                    $mail_body .= "<tr><td><b>Fecha m&aacute;xima de tr&aacute;mite:</b></td><td>".$parametros["fecha_maxima"]."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Asignado por:</b></td><td>".$rem["abr_titulo"] . " " . $rem["nombre"] .
                                  "<br>" . $rem["cargo"] . "<br><a href='mailto:" . $rem["email"]. "'>" . $rem["email"]. "</a></td></tr>";
                    $mail_body .= "<tr><td><b>No. de documento:</b></td><td>".$radicado["radi_nume_text"]."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Asunto del documento:</b></td><td>".$radicado["radi_asunto"]."</td></tr></table>";
                    $mail_body .= "<br><br>Por favor revise su bandeja de Tareas Recibidas en el sistema &quot;<a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>&quot;";
                    break;
                case '51': // finalizar Tarea
                    $rs = $this->db->conn->Execute("select comentario from tarea_hist_eventos where tarea_hist_codi in (select comentario_inicio from tarea where tarea_codi=".$parametros["tarea_codi"].")");
                    $mail_body .= "Se ha finalizado una tarea asignada por usted.";
                    $mail_body .= "<br><br>Informaci&oacute;n de la tarea:<br><br>";
                    $mail_body .= "<table border='0' width='100%'><tr><td width='30%'><b>Fecha:</b></td><td width='70%'>".date("Y-m-d H:i:s")."</td></tr>";
                    $mail_body .= "<tr><td><b>Tarea asignada:</b></td><td>".$rs->fields["COMENTARIO"]."</td></tr>";
                    $mail_body .= "<tr><td><b>Fecha m&aacute;xima de tr&aacute;mite:</b></td><td>".$parametros["fecha_maxima"]."</td></tr>";
                    $mail_body .= "<tr><td><b>Comentario final:</b></td><td>".$parametros["comentario"]."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Finalizado por:</b></td><td>".$rem["abr_titulo"] . " " . $rem["nombre"] .
                                  "<br>" . $rem["cargo"] . "<br><a href='mailto:" . $rem["email"]. "'>" . $rem["email"]. "</a></td></tr>";
                    $mail_body .= "<tr><td><b>No. de documento:</b></td><td>".$radicado["radi_nume_text"]."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Asunto del documento:</b></td><td>".$radicado["radi_asunto"]."</td></tr></table>";
                    $mail_body .= "<br><br>Por favor revise el documento en el sistema &quot;<a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>&quot;";
                    break;
                case '52': // Cancelar Tarea
                    $rs = $this->db->conn->Execute("select comentario from tarea_hist_eventos where tarea_hist_codi in (select comentario_inicio from tarea where tarea_codi=".$parametros["tarea_codi"].")");
                    $mail_body .= "Se ha cancelado una tarea asignada a usted.";
                    $mail_body .= "<br><br>Informaci&oacute;n de la tarea:<br><br>";
                    $mail_body .= "<table border='0' width='100%'><tr><td width='30%'><b>Fecha:</b></td><td width='70%'>".date("Y-m-d H:i:s")."</td></tr>";
                    $mail_body .= "<tr><td><b>Tarea asignada:</b></td><td>".$rs->fields["COMENTARIO"]."</td></tr>";
                    $mail_body .= "<tr><td><b>Fecha m&aacute;xima de tr&aacute;mite:</b></td><td>".$parametros["fecha_maxima"]."</td></tr>";
                    $mail_body .= "<tr><td><b>Comentario final:</b></td><td>".$parametros["comentario"]."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Cancelada por:</b></td><td>".$rem["abr_titulo"] . " " . $rem["nombre"] .
                                  "<br>" . $rem["cargo"] . "<br><a href='mailto:" . $rem["email"]. "'>" . $rem["email"]. "</a></td></tr>";
                    $mail_body .= "<tr><td><b>No. de documento:</b></td><td>".$radicado["radi_nume_text"]."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Asunto del documento:</b></td><td>".$radicado["radi_asunto"]."</td></tr></table>";
                    $mail_body .= "<br><br>Por favor revise el documento en el sistema &quot;<a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>&quot;";
                    break;
                case '53': // Comentar Tarea
                    $rs = $this->db->conn->Execute("select comentario from tarea_hist_eventos where tarea_hist_codi in (select comentario_inicio from tarea where tarea_codi=".$parametros["tarea_codi"].")");
                    $mail_body .= "Se ha comentado una tarea en la que usted interviene.";
                    $mail_body .= "<br><br>Informaci&oacute;n de la tarea:<br><br>";
                    $mail_body .= "<table border='0' width='100%'><tr><td width='30%'><b>Fecha:</b></td><td width='70%'>".date("Y-m-d H:i:s")."</td></tr>";
                    $mail_body .= "<tr><td><b>Tarea:</b></td><td>".$rs->fields["COMENTARIO"]."</td></tr>";
                    $mail_body .= "<tr><td><b>Comentario:</b></td><td>".$parametros["comentario"]."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Comentario realizado por:</b></td><td>".$rem["abr_titulo"] . " " . $rem["nombre"] .
                                  "<br>" . $rem["cargo"] . "<br><a href='mailto:" . $rem["email"]. "'>" . $rem["email"]. "</a></td></tr>";
                    $mail_body .= "<tr><td><b>No. de documento:</b></td><td>".$radicado["radi_nume_text"]."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Asunto del documento:</b></td><td>".$radicado["radi_asunto"]."</td></tr></table>";
                    $mail_body .= "<br><br>Por favor revise el documento en el sistema &quot;<a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>&quot;";
                    break;
                case '54': // Reabrir Tarea
                    $rs = $this->db->conn->Execute("select comentario from tarea_hist_eventos where tarea_hist_codi in (select comentario_inicio from tarea where tarea_codi=".$parametros["tarea_codi"].")");
                    $mail_body .= "Se reabri&oacute; una tarea asignada a usted.";
                    $mail_body .= "<br><br>Informaci&oacute;n de la tarea:<br><br>";
                    $mail_body .= "<table border='0' width='100%'><tr><td width='30%'><b>Fecha:</b></td><td width='70%'>".date("Y-m-d H:i:s")."</td></tr>";
                    $mail_body .= "<tr><td><b>Tarea asignada:</b></td><td>".$rs->fields["COMENTARIO"]."</td></tr>";
                    $mail_body .= "<tr><td><b>Fecha m&aacute;xima de tr&aacute;mite:</b></td><td>".$parametros["fecha_maxima"]."</td></tr>";
                    $mail_body .= "<tr><td><b>Comentario:</b></td><td>".$parametros["comentario"]."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Reabierta por:</b></td><td>".$rem["abr_titulo"] . " " . $rem["nombre"] .
                                  "<br>" . $rem["cargo"] . "<br><a href='mailto:" . $rem["email"]. "'>" . $rem["email"]. "</a></td></tr>";
                    $mail_body .= "<tr><td><b>No. de documento:</b></td><td>".$radicado["radi_nume_text"]."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Asunto del documento:</b></td><td>".$radicado["radi_asunto"]."</td></tr></table>";
                    $mail_body .= "<br><br>Por favor revise el documento en el sistema &quot;<a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>&quot;";
                    break;
                case '55': // Editar Tarea
                    $rs = $this->db->conn->Execute("select comentario from tarea_hist_eventos where tarea_hist_codi in (".$parametros["comentario_inicio"].")");
                    $mail_body .= "Se modific&oacute; una tarea asignada a usted.";
                    $mail_body .= "<br><br>Informaci&oacute;n de la tarea:<br><br>";
                    $mail_body .= "<table border='0' width='100%'><tr><td width='30%'><b>Fecha:</b></td><td width='70%'>".date("Y-m-d H:i:s")."</td></tr>";
                    $mail_body .= "<tr><td><b>Tarea asignada:</b></td><td>".$rs->fields["COMENTARIO"]."</td></tr>";
                    $mail_body .= "<tr><td><b>Fecha m&aacute;xima de tr&aacute;mite:</b></td><td>".$parametros["fecha_maxima"]."</td></tr>";
                    $mail_body .= "<tr><td><b>Comentario:</b></td><td>".$parametros["comentario"]."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Modificada por:</b></td><td>".$rem["abr_titulo"] . " " . $rem["nombre"] .
                                  "<br>" . $rem["cargo"] . "<br><a href='mailto:" . $rem["email"]. "'>" . $rem["email"]. "</a></td></tr>";
                    $mail_body .= "<tr><td><b>No. de documento:</b></td><td>".$radicado["radi_nume_text"]."</td></tr>";
                    $mail_body .= "<tr><td valign='top'><b>Asunto del documento:</b></td><td>".$radicado["radi_asunto"]."</td></tr></table>";
                    $mail_body .= "<br><br>Por favor revise el documento en el sistema &quot;<a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>&quot;";
                    break;
                default:
                    return;
                    break;
            }
            $mail_body .= "<br><br>Saludos cordiales,<br><br>Soporte Quipux.";
            $mail_body .= "<br><br><b>Nota: </b>Este mensaje fue enviado autom&aacute;ticamente por el sistema, por favor no lo responda.";
            $mail_body .= "<br>Si tiene alguna inquietud respecto a este mensaje, comun&iacute;quese con <a href='mailto:$cuenta_mail_soporte'>$cuenta_mail_soporte</a>";
            $mail_body .= "</body></html>";

            $tmp = explode(",", $dest["email"]);
            foreach ($tmp as $destinatario) {

                $header  = 'MIME-Version: 1.0' . "\r\n";
                $header .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
                $header .= "To: ".$dest["titulo"] . " " . $dest["nombre"] . " <" . $destinatario . ">" . "\r\n";
                $header .= "From: Quipux <$cuenta_mail_envio>" . "\r\n";

                $email = $destinatario; //recipient
                $subject = "Quipux: $nombre_accion $asunto"; //asunto
//echo "$subject<br>$mail_body<hr>";
                ini_set('sendmail_from', "$cuenta_mail_envio");
                mail($email, $subject, $mail_body, $header);
            }
//            echo "<br/><span><font color='Navy'><b>El destinatario ha sido notificado a su cuenta de correo electr&oacute;nico.</b></font></span><br/>";
        }
//        else
//            echo "<br/><span><font color='Navy'><b>El sr(a). ".$dest["nombre"]." no fue notificado, no posee una cuenta de correo electr&oacute;nico. v&aacute;lida</b></font></span><br/>";
    }

    /**
    * Metodo que sirve para validar el estado del radicado o documento.
    *
    * @param int $numero de radicado (primary key).
    * @return arreglo.
    */
    function validarEstado($nume_rad){
	$isql = "select esta_codi, radi_usua_actu, radi_nume_temp from radicado where radi_nume_radi=".$nume_rad;
	$rs = $this->db->conn->query($isql);
	return $rs;
    }
}
?>
