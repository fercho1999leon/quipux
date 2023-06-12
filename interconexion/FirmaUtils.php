<?php
class FirmaUtils
{
    private $terms = [
        "issuedTo" => "Firmado por:",
        "issuedBy" => "Entidad certificadora:",
        "validFrom" => "Certificado valido desde:",
        "validTo" => "Certificado valido hasta:",
        "generated" => "Fecha de firma:",
        "validated" => "Valido:",
        "keyUsages" => "Usos:",
        "signVerify" => "Firma es valida:",
        "docReason" => "Razón:",
        "docLocation" => "Localización:",
        "datosUsuario" => "Datos del firmante:"
    ];

    public function FirmarDocumento($pdfBase64, $p12Base64, $password)
    {
        include "../config.php";
        // URL de la API REST en Spring Boot

        $url = "https://test.firma";
        $token = null;
        if (isset($api_firma_documento) and isset($token_server_api)) {
            $url = $api_firma_documento;
            $token = $token_server_api;
        } else {
            die("Falta el token de api o url");
        }
        // Datos a enviar en el cuerpo de la solicitud
        $data = array(
            'pdfBase64' => $pdfBase64,
            'p12Base64' => $p12Base64,
            'password' => $password,
            'token' => $token
        );
        return $this->enviarDatosApi($data, $url);
    }

    public function VerificarFirmaDocumento($pdfBase64)
    {
        include "../config.php";
        // URL de la API REST en Spring Boot

        $url = "https://test.firma";
        $token = null;
        if (isset($api_verificar_firma) and isset($token_server_api)) {
            $url = $api_verificar_firma;
            $token = $token_server_api;
        } else {
            die("Falta el token de api o url");
        }
        // Datos a enviar en el cuerpo de la solicitud
        $data = array(
            'pdfBase64' => $pdfBase64,
            'token' => $token
        );
        $response = $this->enviarDatosApi($data, $url);
        if ($this->isJson($response)) {
            $response = json_decode($response, true)[0];
            $response["flag"] = $response["signValidate"] ? 1 : 0;
            if ($response["flag"] == 1) {
                $response["archivo"] = $pdfBase64;
                $response["datos_firma"] = $response["certificado"][0];
                $response["mensaje"] = "La verificaci&oacute;n de la firma digital del documento fue exitosa.";
                return $response;
            }
            return $response;
        }
        return false;
    }

    private function enviarDatosApi($data, $url)
    {
        // Codificar los datos como JSON
        $jsonPayload = json_encode($data);

        // Configurar opciones de la solicitud
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $jsonPayload
            )
        );

        // Crear contexto de la solicitud
        $context = stream_context_create($options);

        // Realizar la solicitud a la API
        $response = file_get_contents($url, false, $context);

        // Verificar si la respuesta es exitosa
        if ($response !== false) {
            return $response;
        } else {
            // Error al realizar la solicitud
            return false;
        }
    }
    private function isJson($string)
    {
        return ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string))))) ? true : false;
    }

    public function translateTermsSignature($key)
    {
        return $this->terms[$key];
    }
}
