<?php
// Este archivo debe estar en el cliente.

// Esta función genera un archivo PDF a partir de código HTML
// Retorna el archivo en base 64
function ws_generar_pdf_base64($html, $plantilla, $servidor, $estado="", $numDocu="", $fechDocu = "", $numPag = "", $orientPag="V")
{

    try
    {
    	$wsdl = "$servidor/html_a_pdf.php?wsdl";
    	//var_dump($wsdl,!@file_get_contents($wsdl));
        if(!@file_get_contents($wsdl)) {
            throw new SoapFault('Server', 'No WSDL found at ' . $wsdl);
            return "0";
        }

        //Lamado a la clase SOAP PHP para instanciar clienteSOAP
        ini_set('soap.wsdl_cache_enabled', '0');
        
        $archivo = "";
        if (trim($plantilla) != "") {
            if (is_file($plantilla))
                $archivo = base64_encode(file_get_contents($plantilla));
        }
        $oSoap = new SoapClient("$wsdl",array("trace" => 1, "exceptions" => 0, 'soap_version' => 'SOAP_1_1','wsdl_cache' => 0));
       
        //VERIFICACION DE CHK FIRMA ELECTRONICA
        $envioDatos=$oSoap->__soapcall('html_a_pdf',
            array(
              new SoapParam(base64_encode($html), "set_html"),
              new SoapParam($archivo, "set_pdf"),
              new SoapParam($estado, "set_estado"),
              new SoapParam($numDocu, "set_num_docu"),
              new SoapParam($fechDocu, "set_fech_docu"),
              new SoapParam($numPag, "set_num_pag"),
              new SoapParam($orientPag, "set_orient_pag")
           )
        );

    //Comentar
    /*
        var_dump($envioDatos);

            // Display the request and response
        print "<pre>\n";
        print "Request :\n".htmlspecialchars($oSoap->__getLastRequest()) ."\n";
        print "Response:\n".htmlspecialchars($oSoap->__getLastResponse())."\n";
        print "</pre>";
        */
    //Hasta aqui
        if (strtoupper(substr(trim($envioDatos),0,4)) == "SOAP" or strlen($envioDatos)<1000) {
            throw new SoapFault('Server', 'Error SOAP: ' . $envioDatos);
            return "0";
        }
    	return $envioDatos;
    } catch (SoapFault $e) { //Captura los errores
        //var_dump($e);
        printf("No se generó correctamente el archivo PDFs.");
        return "0";
       }
}

// Esta función une varios archivos PDF en uno solo
// Recibe un arreglo de archivos PDF en base 64
// Retorna el archivo PDF en base 64
function ws_unir_archivos_pdf($archivos_base64, $servidor)
{
    try
    {
        $wsdl = "$servidor/html_a_pdf.php?wsdl";
        if(!@file_get_contents($wsdl)) {
            throw new SoapFault('Server', 'No WSDL found at ' . $wsdl);
        }

        //Lamado a la clase SOAP PHP para instanciar clienteSOAP
        ini_set('soap.wsdl_cache_enabled', '0');
        $oSoap = new SoapClient("$wsdl",array("trace" => 1, "exceptions" => 0));

        $envioDatos=$oSoap->__soapcall('unir_archivos_pdf',
            array(
                new SoapParam($archivos_base64, "set_archivos_pdf")
            )
        );
    //Comentar
    
/*        var_dump($envioDatos);
/*
            // Display the request and response
        print "<pre>\n";
        print "Request :\n".htmlspecialchars($oSoap->__getLastRequest()) ."\n";
        print "Response:\n".htmlspecialchars($oSoap->__getLastResponse())."\n";
        print "</pre>";
        /**/
    //Hasta aqui

        return $envioDatos;
    } catch (SoapFault $e) { //Captura los errores
//        var_dump($e);
        printf("No se generó correctamente el archivo PDF.");
        return "";
       }
}


// Contiene la función generar_pdf y recibe los parámetros $html(codigo html) y $plantilla (archivo PDF)
// Retorna un archivo pdf

function ws_generar_pdf($html, $plantilla, $servidor, $estado="", $numDocu="", $fechDocu = "", $numPag = "", $orientPag="V")
{

    try
    {
    	$wsdl = "$servidor/html_a_pdf.php?wsdl";
        if(!@file_get_contents($wsdl)) {
            throw new SoapFault('Server', 'No WSDL found at ' . $wsdl);
        }

        //Lamado a la clase SOAP PHP para instanciar clienteSOAP
        ini_set('soap.wsdl_cache_enabled', '0');
        $archivo = "";
        if (trim($plantilla) != "") {
            if (is_file($plantilla))
                $archivo = base64_encode(file_get_contents($plantilla));
        }
        $oSoap = new SoapClient("$wsdl",array("trace" => 1, "exceptions" => 0));

        $envioDatos=$oSoap->__soapcall('html_a_pdf',
            array(
              new SoapParam(base64_encode($html), "set_html"),
              new SoapParam($archivo, "set_pdf"),
              new SoapParam($estado, "set_estado"),
              new SoapParam($numDocu, "set_num_docu"),
              new SoapParam($fechDocu, "set_fech_docu"),
              new SoapParam($numPag, "set_num_pag"),
              new SoapParam($orientPag, "set_orient_pag")
           )
        );
    //Comentar
    /*
        var_dump($envioDatos);

            // Display the request and response
        print "<pre>\n";
        print "Request :\n".htmlspecialchars($oSoap->__getLastRequest()) ."\n";
        print "Response:\n".htmlspecialchars($oSoap->__getLastResponse())."\n";
        print "</pre>";
        /**/
    //Hasta aqui

        return base64_decode($envioDatos);
    } catch (SoapFault $e) { //Captura los errores
//        var_dump($e);
        printf("No se generó correctamente el archivo PDF.");
        return "0";
       }
}
?>
