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

include "../config.php";

function grabar_archivos_firmados($usuario, $radi_nume, $archivo) {

$t1 = $t2 = $t3 = $t4 = $t5 = $t6 = "null";
list($useg, $seg) = explode(" ", microtime());
$t1 = "('".date("Y-m-d H:i:s").substr($useg."0",1,7)."'::timestamp)";

    $ruta_raiz = "..";
    include_once "$ruta_raiz/funciones.php";
    include_once "$ruta_raiz/obtenerdatos.php";
    include_once "$ruta_raiz/include/db/ConnectionHandler.php";
    include_once "$ruta_raiz/include/tx/Tx.php";
    include_once "$ruta_raiz/include/tx/Firma_Digital.php";

    $radi_nume = limpiar_numero(trim($radi_nume));
    if (strlen($radi_nume)!=20) return "0a";

    $db = new ConnectionHandler($ruta_raiz);
    $db_bodega = new ConnectionHandler($ruta_raiz, "bodega");
    $tx = new Tx($db);

//    $usr = ObtenerDatosUsuario($usuario, $db, "C");
    $radicado = ObtenerDatosRadicado($radi_nume, $db);
    if ($radicado["estado"] != 3) return "0b"; // Validamos que el documento este en un estado válido

    $usr = ObtenerDatosUsuario(str_replace("-", "",$radicado["usua_rem"]), $db);
    if (substr($usr["cedula"], 0, 10) != $usuario) return "0c";

    $archivo = limpiar_sql($archivo);
list($useg, $seg) = explode(" ", microtime());
$t2 = "('".date("Y-m-d H:i:s").substr($useg."0",1,7)."'::timestamp)";
    $rs_archivo = $db_bodega->query("select func_grabar_archivo(E'$radi_nume.pdf.p7m', E'$archivo') as arch_codi");
    if (!$rs_archivo or $rs_archivo->EOF or (0+$rs_archivo->fields["ARCH_CODI"])==0)
        return "0d";
    $arch_codi_firma = 0+$rs_archivo->fields["ARCH_CODI"];

list($useg, $seg) = explode(" ", microtime());
$t3 = "('".date("Y-m-d H:i:s").substr($useg."0",1,7)."'::timestamp)";
    $firma = verificar_firma_archivo($archivo);
list($useg, $seg) = explode(" ", microtime());
$t4 = "('".date("Y-m-d H:i:s").substr($useg."0",1,7)."'::timestamp)";
    $fecha_firma = $db->conn->sysTimeStamp;
    $datos_firma = "null";
    $arch_codi = 0;
    if ($firma["flag"]==1) {
        $datos_firma = $db->conn->qstr(limpiar_sql($firma["datos_firma"], false));
        $archivo_sin_firma = limpiar_sql($firma["archivo"]);
        $rs_archivo = $db_bodega->query("select func_grabar_archivo(E'$radi_nume.pdf', E'$archivo_sin_firma') as arch_codi");
list($useg, $seg) = explode(" ", microtime());
$t5 = "('".date("Y-m-d H:i:s").substr($useg."0",1,7)."'::timestamp)";
        if ($rs_archivo and !$rs_archivo->EOF)
            $arch_codi = 0+$rs_archivo->fields["ARCH_CODI"];
    }

//    if (trim($radicado["radi_path"])=="") {
//        $radi_path = "/".substr(trim($radi_nume),0,4)."/".substr(trim($radi_nume),4,6)."/$radi_nume.pdf.p7m";
//        $path_arch = "$ruta_raiz/bodega".$radi_path;
//    } else {
//        $radi_path = trim($radicado["radi_path"]);
//        while (strtoupper(substr($radi_path,-4)) == ".P7M") {
//            $radi_path = substr($radi_path,0,-4);
//        }
//        $radi_path .= ".p7m";
//        $path_arch = "$ruta_raiz/bodega".$radi_path;
//    }
//
//    $ok = file_put_contents($path_arch, base64_decode($archivo));
//    if (!$ok) return "0d";

//    $firma = verificaFirma("$path_arch",$ruta_raiz);
	//if ($firma["flag"]!=1) return "0";


    $sql = "update radicado 
            set radi_fech_firma=$fecha_firma, radi_tipo_archivo=1
                , radi_nomb_usua_firma=$datos_firma, arch_codi=$arch_codi
                , arch_codi_firma=$arch_codi_firma
            where radi_nume_temp=$radi_nume and (esta_codi=4 or esta_codi=3 or radi_nume_radi=$radi_nume)";
    $ok = $db->conn->Execute($sql);

    if (!$ok) return "0e";
//	Registramos el histórico
    $tx->insertarHistorico($radi_nume, $usr["usua_codi"],  $usr["usua_codi"], "Documento Firmado Electrónicamente", 40);	//Firma Digital
//	Enviamos el documento a los usuarios
    $respFirma = $tx->envioElectronicoDocumento($radi_nume,  $usr["usua_codi"]);
list($useg, $seg) = explode(" ", microtime());
$t6 = "('".date("Y-m-d H:i:s").substr($useg."0",1,7)."'::timestamp)";
$sql = "insert into log_tiempo_ws_firma (radi_nume_radi, t1, t2, t3, t4, t5, t6) values ($radi_nume, $t1, $t2, $t3, $t4, $t5, $t6)";
$db->conn->Execute($sql);
    return "1";
}



    // Averiguar ruta servidor

    ini_set("soap.wsdl_cache_enabled", "0");
    $sServer = new SoapServer("$nombre_servidor/interconexion/firma.wsdl");
    //  $sServer = new SoapServer($ruta_servidor);
    $sServer->addFunction("grabar_archivos_firmados");
    $sServer->handle();
?>
