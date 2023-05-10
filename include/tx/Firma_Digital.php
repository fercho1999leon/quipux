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


//  array function verificaFirma(string $path_archivo)
//  Verifica los datos del signatario de un documento firmado digitalmente
//  Parametros:
//    string $path_archivo: Path del archivo a verificar
//  Devuelve arreglo con la siguiente información
//    datos_firma : datos de los firmantes (tabla html)
//    mensaje     : en caso de error
//    archivo     : path del archivo destino
//    flag        : bandera que contiene 1 o 0 dependiendo si se realizó la verificación o existieron errores

    function verificaFirma($path_archivo, $ruta_raiz=".") {
        include "$ruta_raiz/config.php";
        $wsdl = "$servidor_firma/wsFirma.php?wsdl";;
        
        $archivo = base64_encode(file_get_contents($path_archivo));

        try {
            if(!@file_get_contents($wsdl)) {
                throw new SoapFault('Server', 'No WSDL found at ' . $wsdl);
            }
            ini_set('soap.wsdl_cache_enabled', '0');
            $oSoap = new SoapClient($wsdl,array("trace" => 1, "exceptions" => 0 ));
            $firma = $oSoap->__soapcall('verificar_firma',
                array(
                    new SoapParam($archivo, "set_archivo_verificar")
                )
            );

            $firma["datos_firma"] = base64_decode($firma["datos_firma"]);
            if ($firma["flag"] == "1") {
                $archivo_destino = str_replace(".p7m","",strtolower($path_archivo));
                file_put_contents($archivo_destino, base64_decode($firma["archivo"]));
                $firma["archivo"] = $archivo_destino;
                $firma["mensaje"] = "La verificaci&oacute;n de la firma digital del documento fue exitosa.";
            } else {
                $firma["mensaje"] = "No se pudo verificar la firma digital del documento.";
            }
/*
            // Display the request and response
            print "<pre>\n";
            print "Request :\n".htmlspecialchars($oSoap->__getLastRequest()) ."\n";
            print "Response:\n".htmlspecialchars($oSoap->__getLastResponse())."\n";
            print "</pre>";
            var_dump($firma);
            /* */


        } catch (SoapFault $e) {
            //var_dump($e);
            printf("No se pudo enviar documento");
            return "0";
        }
        return $firma;
    }

    function verificar_firma_archivo($archivo_base64) {
        global $servidor_firma;
        //include "$ruta_raiz/config.php";
        $wsdl = "$servidor_firma/wsFirma.php?wsdl";

        try {
            if(!@file_get_contents($wsdl)) {
                throw new SoapFault('Server', 'No WSDL found at ' . $wsdl);
            }
            ini_set('soap.wsdl_cache_enabled', '0');
            $oSoap = new SoapClient($wsdl,array("trace" => 1, "exceptions" => 0 ));
            $firma = $oSoap->__soapcall('verificar_firma',
                array(
                    new SoapParam($archivo_base64, "set_archivo_verificar")
                )
            );

            $firma["datos_firma"] = base64_decode($firma["datos_firma"]);
            if ($firma["flag"] == "1") {
                $firma["mensaje"] = "La verificaci&oacute;n de la firma digital del documento fue exitosa.";
            } else {
                $firma["mensaje"] = "No se pudo verificar la firma digital del documento.";
            }
/*
            // Display the request and response
            print "<pre>\n";
            print "Request :\n".htmlspecialchars($oSoap->__getLastRequest()) ."\n";
            print "Response:\n".htmlspecialchars($oSoap->__getLastResponse())."\n";
            print "</pre>";
            var_dump($firma);
            /* */
            
        } catch (SoapFault $e) {
            //var_dump($e);
            $firma["flag"] = "0";
            $firma["mensaje"] = "No se pudo verificar la firma digital del documento.";
        }
        return $firma;
    }

?>