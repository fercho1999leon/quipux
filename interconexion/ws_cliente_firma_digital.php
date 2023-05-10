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
/*************************************************************************************************
**  Funciones que consumen un web service del sistema de gestion documental Quipux		**
**  en www.gestiondocumental.gov.ec								**
**												**
**  FUNCIONES:											**
**    	codificar_archivo	Transforma un archivo binario a cadena de texto			**
**    	ws_envio_radicado	Consume el web service del sistema quipux el cual permite 	**
** 				generar un nuevo documento en este sistema en base a los	**
**				parámetros que se envian desde este.				**
**												**
**  Desarrollado por:										**
**	- Mauricio Haro A. - Subsecretaría de Informática					**
**												**
**************************************************************************************************/
   
//Transforma un archivo binario a cadena de texto, recibe el path de un archivo y devuelve una cadena de texto
    function codificar_archivo($file) {
    	$handle = fopen($file,'rb');
    	$file_content = fread($handle,filesize($file));
    	fclose($handle);
    	$encoded = base64_encode($file_content);
    	return $encoded; 
    }
   

// Crea un nuevo documento borrador en el sistema Quipux
// Parámetros:
//	string $usuario			Cédula del usuario que recibirá el documento en Quipux
//	string $asunto			Asunto del documento en Quipux
//	string $nom_archivo		nombre del archivo que se envia que se envía
//	string $archivo			Archivo que se anexará al documento en Quipux
// La función devuelve 0 en caso de error o el número de documento en el sistema Quipux

function envio_documentos_para_firma($usuario, $documento, $path_archivo, $sistema, $clave_archivo, $servidor_wsfirma)
{
    try
    { 
	$wsdl = "$servidor_wsfirma/wsFirma.php?wsdl";
	if(!@file_get_contents($wsdl)) {
            throw new SoapFault('Server', 'No WSDL found at ' . $wsdl);
	}
	//Lamado a la clase SOAP PHP para instanciar clienteSOAP 	
    	ini_set('soap.wsdl_cache_enabled', '0');
    	$oSoap = new SoapClient("$wsdl",array(
   	         "trace"      => 1,
    	         "exceptions" => 0));

        if (!is_file($path_archivo)) throw new SoapFault('Server', "No se encontro el archivo $path_archivo");
    	$archivo = base64_encode(file_get_contents($path_archivo));
        if (strlen($archivo) <= 70) throw new SoapFault('Server', "Tamaño del archivo muy pequeño: " . strlen($archivo));

    	$envioDatosOrfeo=$oSoap->__soapcall('envio_desde_otros_sistemas',
	    array(
           	new SoapParam($usuario, "get_var_usuario"),
           	new SoapParam($documento, "get_var_documento"),
           	new SoapParam($archivo, "get_var_archivo"),
           	new SoapParam($sistema, "get_var_sistema"),
           	new SoapParam($clave_archivo, "get_var_clave_archivo")
	    )
    	);
//Comentar
/*	var_dump($envioDatosOrfeo);

        // Display the request and response
  	print "<pre>\n";
  	print "Request :\n".htmlspecialchars($oSoap->__getLastRequest()) ."\n";
  	print "Response:\n".htmlspecialchars($oSoap->__getLastResponse())."\n";
  	print "</pre>";        
//Hasta aqui
*/
    	return $envioDatosOrfeo;
    } catch (SoapFault $e) { //Captura los errores 
        //var_dump($e);
	printf("No se pudo enviar documento");
	return "0";
    }  
} 
 

?>
