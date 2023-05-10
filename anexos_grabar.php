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
function GrabarAnexo($db, $numrad, $file, $nombre, $desc_arch, $usua_codi, $ruta_raiz, $fisico=0, $imagen=0)
{
//$ruta_raiz erronea
if (str_replace("/","",str_replace(".","",$ruta_raiz))!="") 
    die ("<br/><center><font size='6' color='red'><b>HA SIDO DETECTADO UN INTENTO DE VIOLACI&Oacute;N DE LAS SEGURIDADES DEL SISTEMA
	<br/>SU N&Uacute;MERO IP SER&Aacute; BLOQUEDO PERMANENTEMENTE</b></font>");
//Respuesta si hubo un error al anexar el archivo
    $ok = 0; //Error
//Fecha del anexo
    $anex_fecha = $db->conn->sysTimeStamp;
//Numero de anexo
    $rs = $db->query("select coalesce(max(anex_numero),0) as num from anexos where anex_radi_nume=$numrad");
    $anex_numero = $rs->fields["NUM"] + 1;
//Nombre del archivo
    $tmp=explode("/",strtolower($nombre));
    $tmp=explode("\\",$tmp[count($tmp)-1]);
    $nomb_arch=$tmp[count($tmp)-1];
//Extension del archivo
    $tmp=explode(".",$nomb_arch);
    $flag_firma = false;
    $i = 1;
    do {
        $tmp_ext = $tmp[count($tmp)-$i];
        $ext_arch = "." . $tmp_ext . $ext_arch;
        ++$i;
    } while ($tmp_ext=="p7m");

//Tipo de archivo
    $isql = "select ANEX_TIPO_CODI from anexos_tipo where upper(anex_tipo_ext) like '".trim(strtoupper($tmp_ext))."'";
    $rs=$db->query($isql);
    if ($rs->EOF) {
    	echo "<script>alert('Tipo de archivo no permitido.');</script>";
	return 0;   //Extencion no permitida
    }
    $tipo_arch = $rs->fields["ANEX_TIPO_CODI"];
//Nombre y Path con el que se guardará el archivo
    $anex_codi = $numrad."_".str_pad($anex_numero,5,"0",STR_PAD_LEFT);
    $anex_path = "/".substr(trim($numrad),0,4)."/".substr(trim($numrad),4,6)."/docs/";
    $anex_nombre = $anex_codi.$ext_arch;
//Tamaño del archivo
    $tamano = (filesize($file)/1000);
    if (filesize($file) == 0) {
        echo "<script>alert('No se pudo subir el archivo anexo: $nomb_arch.');</script>";
	return 0;   //Extencion no permitida
    }

//Guardamos los datos en la tabla anexos
    $db->conn->BeginTrans();
    $recordSet["ANEX_RADI_NUME"] = $numrad;
    $recordSet["ANEX_CODIGO"] = $db->conn->qstr($anex_codi);
    $recordSet["ANEX_TIPO"] = $tipo_arch;
    $recordSet["ANEX_TAMANO"] = $tamano;
    $recordSet["ANEX_DESC"] = $db->conn->qstr(substr($desc_arch,0,512));
    $recordSet["ANEX_NUMERO"] = $anex_numero;
    $recordSet["ANEX_PATH"] = $db->conn->qstr($anex_path.$anex_nombre);
    $recordSet["ANEX_BORRADO"] = "'N'";
    $recordSet["ANEX_FECHA"] = $anex_fecha;
    $recordSet["ANEX_NOMBRE"] = $db->conn->qstr(substr($nomb_arch,0,100));
    $recordSet["ANEX_USUA_CODI"] = $usua_codi;
    $recordSet["ANEX_FISICO"] = $fisico;
    $ok1 = $db->conn->Replace("ANEXOS", $recordSet, "ANEX_CODIGO", false,false,true,false);//true al final para ver la cadena del insert
    $ok2 = true;
    $ok3 = true;

    if ($ok1==2)  //Si inserto correctamente
    {
	$bien2 = false;
	$archivo = "$ruta_raiz/bodega".$anex_path.$anex_nombre;
	$bien2 = move_uploaded_file($file,$archivo);	//Grabamos el archivo
	if (!$bien2) $bien2 = rename($file,$archivo);	//Habilitado mientras esté activo el servlet para interconexion con firma decretos
        if (filesize($archivo) == 0) $bien2 = false;

	if ($bien2) {	//Si el archivo subio correctamente
	    // Verificar firma digital
	    if ($flag_firma) {
		include_once "$ruta_raiz/include/tx/Firma_Digital.php";
		$firma = verificaFirma($archivo);
		if ($firma["flag"] == 1) {
		    $tmp_nomb = $db->conn->qstr($firma["datos_firma"]);
		    $query = "update anexos set anex_datos_firma=$tmp_nomb, anex_fecha_firma=$anex_fecha where anex_codigo like '$anex_codi'";
		    $ok3 = $db->conn->Execute($query);
		    $query = ", radi_nomb_usua_firma=$tmp_nomb, radi_fech_firma=$anex_fecha ";
		} else {
		    $tmp_nomb = "'No se pudo verificar la firma digital del documento'";
		    $query = "update anexos set anex_datos_firma=$tmp_nomb, anex_fecha_firma=$anex_fecha where anex_codigo like '$anex_codi'";
		    $ok3 = $db->conn->Execute($query);
		    $query = ", radi_nomb_usua_firma=$tmp_nomb, radi_fech_firma=$anex_fecha ";
/*		    $query = "";
		    exec( "rm -f " . $archivo );
		    exec( "rm -f " . $firma["archivo"] );*/
		    echo "<script>alert('No se pudo verificar la firma digital del archivo anexo $nomb_arch.');</script>";
		}
	    } else 
	    	$query = ", radi_nomb_usua_firma=null, radi_fech_firma=null ";
	    // Si el anexo debera ser colocado como imagen del documento
//echo "Imagen - ";
	    if ($imagen==1) {
		$query = "update radicado set radi_path='$anex_path$anex_nombre', radi_tipo_archivo=1 $query 
			where radi_nume_temp=$numrad and (radi_tipo_archivo=0 or radi_nume_temp::text like '%2')";
//echo $query;
	    	$ok2 = $db->conn->Execute($query);
	    }
	    if ($ok2 and $ok3) { //Si inserto correctamente
	    	$db->conn->CommitTrans();	
		$ok = 1;
	    } else
		$db->conn->RollbackTrans();
	} else	{    
	    		$db->conn->RollbackTrans(); //mensaje en el caso de que no se haya anexado el documento
	    		echo "<script>alert('No se pudo subir el archivo anexo: $nomb_arch.');</script>";
		}
    } else
	$db->conn->RollbackTrans();
    return $ok;
}

?>
