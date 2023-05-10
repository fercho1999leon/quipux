<?php

/*
  SecureSession class
  Written by Vagharshak Tozalakyan <vagh@armdex.com>
  Released under GNU Public License
  Recomendacion: Cambiar al formato de clases estandar en php5 con OO
*/

class SecureSession
{
    // Include browser name in fingerprint?
    var $check_browser = true;

    // How many numbers from IP use in fingerprint?
    var $check_ip_blocks = 0;

    // Control word - any word you want.
    var $secure_word = 'SECURESTAFF';

    // Regenerate session ID to prevent fixation attacks?
    var $regenerate_id = true;

    // Call this when init session.
    function Open()
    {
        $_SESSION['ss_fprint'] = $this->_Fingerprint();
        $this->_RegenerateId();
    }

    // Call this to check session.
    function Check()
    {
        $this->_RegenerateId();
        return (isset($_SESSION['ss_fprint']) && $_SESSION['ss_fprint'] == $this->_Fingerprint());
    }

    // Internal function. Returns MD5 from fingerprint.
    function _Fingerprint()
    {
        $fingerprint = $this->secure_word;
        if ($this->check_browser) {
            $fingerprint .= $_SERVER['HTTP_USER_AGENT'];
        }
        if ($this->check_ip_blocks) {
            $num_blocks = abs(intval($this->check_ip_blocks));
            if ($num_blocks > 4) {
                $num_blocks = 4;
            }
            $blocks = explode('.', $_SERVER['REMOTE_ADDR']);
            for ($i = 0; $i < $num_blocks; $i++) {
                $fingerprint .= $blocks[$i] . '.';
            }
        }
        return md5($fingerprint);
    }

    // Internal function. Regenerates session ID if possible.
    function _RegenerateId()
    {
        if ($this->regenerate_id && function_exists('session_regenerate_id')) {
//            if (version_compare('5.1.0', phpversion(), '>=')) {
                session_regenerate_id(true);
               $this-> _ActualizaSessionBD();  //llama a funcion para actualizar la sesion bd
//            } else {
//                session_regenerate_id();
//               $this-> _ActualizaSessionBD();  //llama a funcion para actualizar la sesion bd
//            }
        }
    }
    /**
     * _ActualizaSessionBD Esta funcion actualiza la session en la tabla usuarios_session de quipux
     */
    function _ActualizaSessionBD(){
      //grabar la session regenerada en una tabla orfeo
      $ruta_raiz = ".";
       require_once ("$ruta_raiz/include/db/ConnectionHandler.php");
        $db = new ConnectionHandler("$ruta_raiz");
        $db->conn->SetFetchMode(ADODB_FETCH_ASSOC);
        //Actualiza la session del usuario
        $nueva_session = "E'".session_id()."'";
        $query = "UPDATE USUARIOS_SESION set usua_sesion=".$nueva_session ." WHERE usua_codi ='".$_SESSION["usua_codi"]."'";
        $ejecuta_update = $db->query($query);

    }
}

?>
