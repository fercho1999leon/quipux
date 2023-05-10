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
session_start();
$ruta_raiz = "..";
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/funciones.php";
include_once "$ruta_raiz/obtenerdatos.php";
include_once "$ruta_raiz/include/tx/Tx.php";
include_once "$ruta_raiz/seguridad_documentos.php"; // Valida estados de los documentos y otras reglas dependiendo de la transacción realizada
include_once "$ruta_raiz/funciones_interfaz.php";

echo "<html>".html_head();
?>
    <script>
        function redirigir_salida() {
            window.location = "<?=$ruta_raiz?>/cuerpo.php?nomcarpeta=Enviados&carpeta=8&adodb_next_page=1";
        }
    </script>
</head>
<?
	/**Generamos el encabezado que envia las variable a la paginas siguientes.
	* Por problemas en las sesiones enviamos el usuario.
	* @$encabezado  Incluye las variables que deben enviarse a la singuiente pagina.
	* @$linkPagina  Link en caso de recarga de esta pagina.
	**/

	$encabezado = "depeBuscada=$depeBuscada&filtroSelect=$filtroSelect&tpAnulacion=$tpAnulacion";
    // se incluyo por register globals.
    $chk_firma = $_POST['chk_firma'];
    $codTx = $_POST['codTx'];
    $observa = $_POST['observa'];
    $fechaAgenda= $_POST['fechaAgenda'];
    $checkValue = $_POST['checkValue'];
    $fecha_doc = $_POST['fecha_doc'];
    $codusuario = $_SESSION['codusuario'];
    $usua_nomb = $_SESSION['usua_nomb'];
    $depe_nomb = $_SESSION['depe_nomb'];
    $chk_reasigna_padre = $_POST['chk_reasigna_padre'];

    $whereFiltro = "0";
    if(isset($_POST['checkValue'])) {		//Si se escogieron radicados de la lista
        foreach ($_POST['checkValue'] as $radi_nume => $chk) {
            if (trim($radi_nume)!="") {
                $flag = validar_transacciones($codTx, $radi_nume, $db);
                if ($flag == "")
                    $whereFiltro .= ",$radi_nume";
                else
                    $mensaje_error .= $flag;
            }
        }
    } 
    if ($whereFiltro === "0") {	//Si no se escogio ningun radicado
        die ("No hay documentos seleccionados.");
    }

    $isql = "select radi_nume_radi, radi_nume_text,radi_asunto,ver_usuarios(radi_usua_rem,',') as Remitente,radi_usua_actu,radi_cuentai, radi_nume_asoc from radicado where radi_nume_radi in ($whereFiltro)";
    $rs = $db->conn->Execute($isql);
    while (!$rs->EOF) {
		$radicadosText[] = $rs->fields["RADI_NUME_TEXT"];
		$radicadosSel[] = $rs->fields["RADI_NUME_RADI"];
        $radicadosAsunto[]= $rs->fields["RADI_ASUNTO"];
        $radicadosRemitente[]= $rs->fields["REMITENTE"];
        $radicadosUserActu[]= $rs->fields["RADI_USUA_ACTU"];
        $radiReferencia[]=$rs->fields["RADI_CUENTAI"];
        $radiNumeAsoc[]=$rs->fields["RADI_NUME_ASOC"];
        $rs->MoveNext();
    }   
//echo $isql;
/*if($checkValue)
{
	$num = count($checkValue);
	$i = 0;
	while ($i < $num)
	{
		$record_id = substr(trim(key($checkValue)),-20);
		$setFiltroSelect .= $record_id ;
		$db->conn->SetFetchMode(ADODB_FETCH_ASSOC);
		$isqlTEXT = "select RADI_NUME_TEXT from RADICADO where RADI_NUME_RADI=".$record_id;
		$rsTEXT = $db->conn->Execute($isqlTEXT);
		$raditext = $rsTEXT->fields["RADI_NUME_TEXT"];

		$radicadosText[] = $raditext;
		$radicadosSel[] = $record_id;
		if($i<=($num-2))
		{
			$setFiltroSelect .= ",";
		}
  	next($checkValue);
	$i++;
	}
	if ($radicadosSel)
	{
		$whereFiltro = " and b.radi_nume_radi in($setFiltroSelect)";
	}
}
 if($setFiltroSelect)
 {
		$filtroSelect = $setFiltroSelect;
 }/**/
?>
<body>
<?
$txSql = "";
//$db->conn->debug=true;
$okTx = 0;
$tx = new Tx($db);

switch ($codTx)
{
	case 2:  //Eliminar Documentos
            $nombTx = "Eliminar Documentos ";
            $usCodDestino = $tx->eliminarDocumento( $radicadosSel, $_SESSION["usua_codi"], $observa);
            if($usCodDestino!='')
                $MensajeTx="El/Los documento(s) est&aacute;n en la bandeja &quot;Eliminados&quot;.";
            else
                echo "<br/><span><font color='Navy'>No se pudo realizar esta acci&oacute;n. Solo los documentos en estado de Elaboraci&oacute;n pueden ser eliminados.</b></font></span><br/>";
            break;
	case 6:  //Sacar de eliminados
            $nombTx = "Restaurar Documentos Eliminados ";
            $usCodDestino = $tx->noEliminarDocumento( $radicadosSel, $_SESSION["usua_codi"], $observa, $MensajeTx);
            if($usCodDestino=='')
                echo "<br/><span><font color='Navy'>El/Los documento(s) no puede(n) ser restaurados(s).</b></font></span><br/>"; //, ya no se encuentra(n) en la bandeja <b>&quot;Eliminados&quot;
            break;
	case 3:  //envio manual de documentos
            $nombTx = "Enviar Documentos Manualmente ";
            $usCodDestino = $tx->envioManualDocumento($radicadosSel, $observa);
            //de obtener php, envia a bandeja compartida
            correoBandejaCompartidas($radicadosSel,$db,$ruta_raiz,0);
            $MensajeTx= "El/Los documento(s) est&aacute;n en la bandeja &quot;Enviado&quot;.";
            break;
	case 4: //envío electrónico de documentos
            $nombTx = "Envío Electrónico de Documentos";
            if ($_SESSION["firma_digital"]==0) {
                include "./formEnvio.php";
                die("<script>alert('Usted no tiene permiso para firmar digitalmente documentos.".
                    " Consulte con su administrador del sistema.');</script>");
            }
            $tx->reintentarEnvioElectronicoDocumento($radicadosSel, $_SESSION["usua_codi"], $observa);
            $usCodDestino=ObtenerUsuariosRadicado($db,$radicadosSel,'para');
            correoBandejaCompartidas($radicadosSel,$db,$ruta_raiz,1);
            break;
	case 5:  //Obligar envio manual de documentos
            $nombTx = "Envío Manual de Documentos ";
            $usCodDestino = $tx->forzarEnvioManualDocumentos($radicadosSel, $observa);
            $MensajeTx= "El/Los documento(s) est&aacute;n en la bandeja &quot;Por Imprimir&quot; de la secretaria";
            break;

	case 7:
            $nombTx = "Borrar Informados";
            $radicadosSel = $tx->borrarInformado($radicadosSel, $_SESSION["usua_codi"],$observa);
            $MensajeTx= "El/Los documento(s) ha(n) sido eliminado(s).";
            break;
	case 8:
            $nombTx = "Informar Documentos";
            if (isset($_POST['slc_lista'])) { // Envío a listas. No se envía a ciudadanos ni a usuarios de otras instituciones
                $todos=implode(",",$_POST['slc_lista']);
                if ($todos=="-1"){
                    $sqlTodos = "select * from usuarios 
                        where usua_esta = 1 and usua_email is not null 
                        and usua_email <> 'des@informatica.gob.ec' 
                        and inst_codi = ".$_SESSION["inst_codi"];
                    //echo $sqlTodos;
                    $rs = $db->conn->Execute($sqlTodos);
                    while (!$rs->EOF) { // Pongo los resultados en la variable $_POST para reutilizar el codigo... y memoria...
                        $_POST['usCodSelect'][] = $rs->fields["USUA_CODI"];
                        $rs->MoveNext();
                    }//while
                }else{
                $sql = "select distinct l.usua_codi from lista_usuarios l, usuarios u where l.lista_codi in (" . implode(",",$_POST['slc_lista']) . ")";
                $sql .= " and u.usua_codi=l.usua_codi and u.inst_codi=".$_SESSION["inst_codi"];
                if (isset($_POST['usCodSelect']))
                    $sql .= " and l.usua_codi not in (" . implode(",",$_POST['usCodSelect']) . ")";
//echo $sql;
                $rs = $db->conn->Execute($sql);
                while (!$rs->EOF) { // Pongo los resultados en la variable $_POST para reutilizar el codigo... y memoria...
                    $_POST['usCodSelect'][] = $rs->fields["USUA_CODI"];
                    $rs->MoveNext();
                }
               }//fin else
            }
            if (isset($_POST['usCodSelect'])) {
                foreach ($_POST['usCodSelect'] as $tmp) {
                    $usua_dest = $tmp;
                    $usCodDestino .= $tx->informar($radicadosSel, $_SESSION["usua_codi"], $usua_dest, $observa).", ";
                }
                $usCodDestino = substr(trim($usCodDestino),0,-1);
                $MensajeTx= "Se informó a los destinatarios. Si en la lista de destinatarios hay un ciudadano el no será informado";
            } else {
                if ($todos!="-1")
                echo "&Uacute;nicamente se puede Informar a funcionarios p&uacute;blicos.";
            }

            break;
	case 9:
            $fecha_max_tram = substr($fecha_doc,6 ,4)."-".substr($fecha_doc,3 ,2)."-".substr($fecha_doc,0 ,2);
            $nombTx = "Reasignar Documentos ";
            $MensajeTx= "El/Los documento(s) est&aacute;n en la bandeja &quot;Reasignados&quot;.";
            $usua_dest = $_POST['usCodSelect'];
            //$observa .= "<br>Fecha máxima de trámite: ".$fecha_max_tram;
            $usCodDestino = $tx->reasignar( $radicadosSel, $_SESSION["usua_codi"], $usua_dest, $observa, $fecha_max_tram, false, $carpeta);
            if($chk_reasigna_padre == "true"){
                //Se consulta los documentos padre
                foreach($radiNumeAsoc as $radi_nume) {
                    if($radi_nume != ""){
                        $flag = validar_transacciones($codTx, $radi_nume, $db);
                        if($flag == "")
                        {
                            $radiNumeAsociados[] = $radi_nume;
                            $radi_nume_asoc .= $radi_nume."," ;
                        }
                    }
                }
                $radi_nume_asoc = substr($radi_nume_asoc, 0,-1);
                if($radi_nume_asoc != "")
                {
                    $isql = "select radi_nume_text from radicado where radi_nume_radi in ($radi_nume_asoc)";
                    $rs = $db->conn->Execute($isql);
                    while (!$rs->EOF) {
                        $radiAsociadosText[] = $rs->fields["RADI_NUME_TEXT"];
                        $rs->MoveNext();
                    }

                    //Se reasigna los documentos padre validados
                    $usCodDestino = $tx->reasignar($radiNumeAsociados, $_SESSION["usua_codi"], $usua_dest, $observa, $fecha_max_tram, false, $carpeta);
                }                
            }

            break;
        case 11:
            $nomServidor = $nombre_servidor;
            $nombTx = "Firmar y Enviar Documentos";
            if (isset($chk_firma) && $chk_firma != 0) $tx->flag_firmar=true;
            $tx->GenerarDocumentosEnvio($radicadosSel, $_SESSION["usua_codi"], $observa);
            $flag_firmar = $tx->cambioEstadoDocumentoGenerado($radicadosSel);
            if ($flag_firmar==1){ 
                $tx->enviarDocumentosFirmaElectronica($radicadosSel);
                correoBandejaCompartidas($radicadosSel,$db,$ruta_raiz,1);
                }
                foreach ($radicadosSel as $tmpRad) {                
                    $radtmp=ObtenerDatosRadicado($tmpRad, $db);
                    $radi_usua_re= $radtmp['usua_redirigido'];//cargo redirigido de los documentos
                    if ($radi_usua_re!=0){                        
                        $usrDest = ObtenerDatosUsuario($radi_usua_re, $db);
                        //cargo de todos los doc seleccionados
                        $usCodDestino.= $usrDest['nombre']."<br>";
                    }else{
                        if ($tmpRad!='')
                        $radiPen[] = $tmpRad;
                    }
            }
            //hago la busqueda con los que quedaron pendientes
            //documentos que no tienen redirigido
            if (isset($radiPen))
                $usCodDestino = $usCodDestino."<br>".ObtenerUsuariosRadicado($db,$radiPen,'para');
            else
                $usCodDestino = $usCodDestino;
            break;
        case 13:
            $nombTx = "Archivo de Documentos";
            $tx->archivar( $radicadosSel, $_SESSION["usua_codi"],$observa);            
            $usCodDestino=$_SESSION['usua_nomb'];            
            $MensajeTx = "El/Los documento(s) est&aacute;n en la bandeja &quot;Archivados&quot;.";
            break;
        case 17:
            $nombTx = "Reestablecer Documentos Archivados";
            $usCodDestino = $tx->noArchivar ($radicadosSel, $_SESSION["usua_codi"], $observa);
            $usCodDestino = ObtenerUsuariosRadicado($db,$radicadosSel,'para');
            $MensajeTx = "El/Los documento(s) est&aacute;n en la bandeja &quot;Recibidos&quot;.";
            break;
        case 18:
            $nombTx = "Comentar Documentos";
            $tx->comentarDocumento ($radicadosSel, $_SESSION["usua_codi"], $observa);
            $usCodDestino=ObtenerUsuariosRadicado($db,$radicadosSel,'para');
            $MensajeTx = "";
            break;
        case 20:
            $nombTx = "Devoluci&oacute;n de Documentos";
            $usCodDestino = $tx->devolverDocumento ($radicadosSel, $_SESSION["usua_codi"], $observa);
            $MensajeTx = "";
            break;
        case 90:            
            $nombTx = "Acci&oacute;n: Enviar Documentos Firmados Electr&oacute;nicamente por Ciudadanos";
            $tx->enviarDocumentoElectronicoCiudadano ($radicadosSel, $observa);
            $radtmp=array();
            $usrDest=array();
            $usCodDestino = "";
            $radiPen=array();
            foreach ($radicadosSel as $tmpRad) {                
                    $radtmp=ObtenerDatosRadicado($tmpRad, $db);
                    $radi_usua_re= $radtmp['usua_redirigido'];//cargo redirigido de los documentos
                    if ($radi_usua_re!=0){                        
                        $usrDest = ObtenerDatosUsuario($radi_usua_re, $db);
                        //cargo de todos los doc seleccionados
                        $usCodDestino.= $usrDest['nombre']."<br>";
                    }else{//si tienen redirigido en 0, cargo en array los doc
                         if ($tmpRad!='')
                        $radiPen[] = $tmpRad;
                    }
            }
            //hago la busqueda con los que quedaron pendientes
            //documentos que no tienen redirigido
            if (isset($radiPen))
            $usCodDestino = $usCodDestino."<br>".ObtenerUsuariosRadicado($db,$radiPen,'para');
            else
                $usCodDestino = $usCodDestino;
            $MensajeTx = "";
            break;
        case 69:        
            //Enviar fisico
            $nombTx = "Enviar Físico";
            $usua_dest = 0+$_POST['usCodSelect'];            
            $usua_resp= limpiar_sql(trim($_POST['nombre']));
            $usCodDestino = $tx->enviarfisico( $radicadosSel, $_SESSION["usua_codi"], $usua_dest, $observa,$usua_resp);//$fecha_max_tram
        
		break;
        case 83:            
            $nombTx = "Recuperar Documentos";            
            $usCodDestino = $tx->recuperarReasignado ($radicadosSel,$_SESSION['usua_codi'],$observa);            
            //$MensajeTx = "";
            break;
        case 88:            
            $nombTx = "Asociar Carpetas Virtuales";            
            $trd_codigo = 0 + $_POST['txt_check_carpeta'];            
            $usCodDestino = $tx->AsoCarpetasVirtuales ($radicadosSel,$_SESSION['usua_codi'],$_SESSION["depe_codi"],$trd_codigo,$observa);
            //$MensajeTx = "";
            break;
/*	case 14:
		$nombTx = "Poner en Pendientes";
		$txSql = $tx->agendar( $radicadosSel, $_SESSION["usua_codi"], $observa, $fechaAgenda);
		echo "<br/><span><font color='Navy'>El/Los documento(s) ha(n) sido enviado(s) a la bandeja de documentos <b>&quot;Pendientes&quot;.</b></font></span><br/>";
		break;
	case 15:
		$nombTx = "Sacar de Pendientes";
		$txSql = $tx->noAgendar( $radicadosSel, $_SESSION["usua_codi"], $observa);
		break;/**/
}

    // Consultamos nuevamente radicados con numero de documento definitivo
    $isql = "select radi_nume_text, ver_usuarios(radi_usua_rem,',') as Remitente from radicado where radi_nume_radi in ($whereFiltro)";
    $rs = $db->conn->Execute($isql);
    unset ($radicadosText);
    while (!$rs->EOF) {
		$radicadosText[] = $rs->fields["RADI_NUME_TEXT"];
        $rs->MoveNext();
    }

if($okTx== -1)  $okTxDesc = " NO ";
	else  $okTxDesc = " ";


?>
    
        <br>
        <table border=0 cellspace=2 cellpad=2 WIDTH=50%  class="t_bordeGris" id=tb_general align="left">
            <tr>
                <td colspan="2" class="titulos4">ACCION REQUERIDA <?=$okTxDesc?> COMPLETADA </td>
            </tr>
            <tr>
                <td align="right" class="titulos2">ACCION REQUERIDA :</td>
                <td  width="65%" class="listado2"><?=$nombTx?>.<br> <?php //$MensajeTx?></td>
            </tr>
            <tr>
                <td align="right" class="titulos2">DOCUMENTO (S) INVOLUCRADOS :</td>
                <td class="listado2"><?=join("<BR> ",$radicadosText)?></td>
            </tr>

            <?php if (sizeof($radiAsociadosText) > 0){  ?>
            <tr>
                <td align="right" class="titulos2">DOCUMENTO (S) ANTECEDENTES:</td>
                <td class="listado2"><?=join("<BR> ",$radiAsociadosText)?></td>
            </tr>
            <?php } ?>
            <tr>
                <td align="right" class="titulos2">USUARIO DESTINO :</td>
                <?php if (isset($_POST['slc_lista'])){
                    if (implode(",",$_POST['slc_lista'])==-1){
                 ?>
                <td class="listado2">Enviado a todos los Usuarios Activos de la Institución</td>
                    <?php
                    }
                }else{ ?>
                <td class="listado2"><?=$usCodDestino?></td>
                <?php } ?>
            </tr>
            <tr>
                <td align="right" class="titulos2">FECHA Y HORA :</td>
                <td class="listado2"><?=date("m-d-Y  H:i:s").$descZonaHoraria?></td>
            </tr>
            <tr>
                <td align="right" class="titulos2">USUARIO ORIGEN:</td>
                <td class="listado2"><?=$usua_nomb?></td>
            </tr>
            <tr>
                <td align="right" class="titulos2"> <?=strtoupper($_SESSION["descDependencia"]);?> ORIGEN:</td>
                <td class="listado2"><?=$depe_nomb?></td>
            </tr>
            <?php
            
            if ($codTx==11 and $_SESSION['firma_digital']==0 and substr($radicadosText[0],-1)!='E'){ ?>
            <tr>
                <td align="right" class="titulos2">FIRMA DIGITAL:</td>
                <td class="listado2">Usted no tiene firma digital, el documento no le llegará al destinatario.<br>
                Por favor imprimir y enviarlo, revise la bandeja (Por Imprimir).</td>
            </tr>
            <?php } ?>
        </table>
   
<?php
$radifi = "'".$radicadosSel[0]."'";
?>


<script>

<?if($codTx==69){?>
    //alert('../reportes/reporte_TraspasoDocFisico.php');
    window.open ("<?=$ruta_raiz?>/reportes/reporte_TraspasoDocFisico.php?verrad=<?=$radifi?>&responsable=<?=$usua_resp?>&comentario=<?=$observa?>");

<?}?>
</script>


    
</body>
</html>
