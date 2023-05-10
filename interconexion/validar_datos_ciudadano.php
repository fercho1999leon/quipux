<?php
function ws_validar_datos_ciudadano($cedula)
{
    global $servidor_registro_civil, $servidor_registro_civil_usuario, $servidor_registro_civil_password;
    $datos = array();
    //include_once "$ruta_raiz/config.php";
    try
    {
    	$wsdl = "$servidor_registro_civil";
        if(!@file_get_contents($wsdl)) {
            throw new SoapFault('Server', 'No WSDL found at ' . $wsdl);
        }

        //$servicio="http://webservice01.registrocivil.gob.ec:9763/services/ws_TEST_CLIENTS_BCedula_INTDB_des_run?wsdl"; //url del servicio
        $parametros=array(); //parámetros de la llamada
        $parametros[0]="--context_param p_macedu=".$cedula;
        $parametros[1]="--context_param p_usuario=$servidor_registro_civil_usuario";
        $parametros[2]="--context_param p_contrasenia=$servidor_registro_civil_password";
        $client = new SoapClient($wsdl, $parametros);
        $result = $client->runJob($parametros);//llamamos al método que nos interesa con los parámetros

        // El Registro civil retorna un XML, en las siquientes instrucciones parseamos esta cadena
        $data = $result->item->item;
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, $data, $values, $tags);
        xml_parser_free($parser);

//   echo "<pre>";
//   var_dump($values);
//   echo "</pre>";

        $datos["error"] = 0;
        foreach ($values as $datos_rc) {
            switch (strtolower($datos_rc["tag"])) {
                case "cedula":
                case "nombre":
                case "genero":
                case "domicilio":
                case "estado_civil":
                case "profesion":
                case "instruccion":
                case "descripcion": // Error
                    $datos[strtolower($datos_rc["tag"])] = ucwords(strtolower($datos_rc["value"]));
                    break;
                case "codigo_error": // Error
                    $datos["error"] = 1;
                    break;
                default:
                    break;
            }
        }

    } catch (SoapFault $e) { //Captura los errores
//        var_dump($e);
        $datos["error"] = 1;
        $datos["descripcion"] = "No se pudo establecer conexi&oacute;n con el Registro Civil.";
    }
    return $datos;
}
?>