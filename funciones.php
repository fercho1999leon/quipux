<?php

/*******************************************************************************
** Limpia cadenas numéricas, elimina cualquier caracter de otro tipo          **
*******************************************************************************/
function limpiar_numero($numero) {
    $numero = trim($numero);
    $flag_punto = false;
    $cadena = "";
    if (ltrim($numero, "0123456789.-") != "") {
        enviar_mail_intento_ataque("QUIPUX: NOTIFICACION INTENTO DE ATAQUE - TEXTO INTRODUCIDO EN CAMPO NUMERICO", $cadena);
    }
    for ($i=0; $i<strlen($numero); $i++){
        $num = substr($numero,$i,1);
        if (strpos("0123456789.-", $num) !== false) {
            if ($num == "-" and $i > 0) $num = ""; //Para numeros negativos el guion debe estar al inicio
            if ($num == ".") { // Para decimales
                if ($flag_punto) $num=""; //Controla que haya un solo punto
                if ($i == 0) $num = "0.";
                $flag_punto = true;
            }
            $cadena .= $num;
        }
    }
    return $cadena;
}


/*******************************************************************************
** Limpia las cadenas de ataques SQL INJECTION, CSS y HTML INJECTION          **
** Si $html==1 no limpia la cadena de código html                             **
*******************************************************************************/
function limpiar_sql($cadena, $validar_html=1) {
    // Limpiamos apóstrofes para ataques SQL Injection
    if (is_array($cadena)) return;
    $cadena = str_replace("\\", "", $cadena);
    $cadena = str_replace("'", "′", $cadena);
    $cadena = str_replace("”", '"', $cadena);
    $cadena = str_replace("“", '"', $cadena);

    $cadena_tmp = str_replace(array("&NBSP;", "\n", " "), "", strtoupper($cadena));
    $cadena_tmp = str_replace(array("&AMP;","&LT;"), array("&","<"), $cadena_tmp);
    if (strpos($cadena_tmp, "<SCRIPT") !== false) {
//        enviar_mail_intento_ataque("QUIPUX: NOTIFICACION INTENTO DE ATAQUE - CSS y HTML", $cadena);
        $cadena = str_ireplace(array("javascript","script"), array("jaiba_street","street"), $cadena);
    }

    if ($validar_html) {
        $cadena = str_replace(array("<"), array("&lt;"), $cadena);
//        $cadena = strip_tags($cadena);
//        $cadena = htmlspecialchars($cadena);
    }
    return trim($cadena);

}


/*******************************************************************************
** Registra las variables que se reciben por $GET, $_POST y $_SESSION y las   **
** limpia contra ataques SQL Injection.                                       **
** Permite dehabilitar la variable REGISTER_GLOBALS del archivo PHP.INI       **
*******************************************************************************/
function validar_register_globals() {
    // Lista de variables que se van a limpiar; $_POST se sobrepone a $_GET y $_SESSION a todas las demás
    $variables = array("_GET", "_POST", "_SESSION");
    foreach ($variables as $tipo_variable) {
        global $$tipo_variable;
        if (is_array($$tipo_variable)) {
            foreach ($$tipo_variable as $key => $value) {
                $key = trim(limpiar_sql($key));
                if ($key != "" && $key != "ruta_raiz") {
                    global $$key;
                    $$key = limpiar_sql($value);
                    if ($key == "orderNo") $$key = 0+$value;
                    if ($key == "adodb_next_page") $$key = 0+$value;
                    if ($key == "orderTipo" && strtolower($value)!="desc") $$key = "asc";
                }
            }
        }
    }
    return;
}
/*******************************************************************************
** ATENCIÓN: Ejecuto automáticamente la función; se llama al archivo desde    **
** SESSION_ORFEO.PHP para que se ejecute en todas las páginas                 **
*******************************************************************************/
validar_register_globals();



function buscar_cadena($cadena, $campo) {
//Arma el query para buscar una cadena separada por espacios en un campo de la bdd quitando las tildes y eñes

    $resp = "";
    $cadena = limpiar_sql($cadena);
    //$cadena = str_replace('á','A',str_replace('é','E',str_replace('í','I',str_replace('ó','O',str_replace('ú','U',str_replace('ñ','N',$cadena))))));
    //$cadena = str_replace('Á','A',str_replace('É','E',str_replace('Í','I',str_replace('Ó','O',str_replace('Ú','U',str_replace('Ñ','N',$cadena))))));

    $arr_buscar = explode(" ", $cadena);
    $glue = '';
    foreach ($arr_buscar as $tmp) {
        if ($tmp != "" && strlen($tmp)>=3) {
            $resp .= " $glue translate(UPPER($campo),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN')
		      LIKE translate(upper('%" . trim($tmp) . "%'),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') ";
            $glue = 'and';
        }
    }
    $resp = (empty($resp)) ? 'true' : $resp;
    return $resp;
}

function buscar_cadena_tsearch($cadena, $campo, $idioma="es") {
//Arma el query para buscar una cadena separada por espacios en un campo de la bdd quitando las tildes y eñes

    $resp = "";
    $cadena = limpiar_sql($cadena);
    //$cadena = str_replace('á','A',str_replace('é','E',str_replace('í','I',str_replace('ó','O',str_replace('ú','U',str_replace('ñ','N',$cadena))))));
    //$cadena = str_replace('Á','A',str_replace('É','E',str_replace('Í','I',str_replace('Ó','O',str_replace('Ú','U',str_replace('Ñ','N',$cadena))))));

    $arr_buscar = explode(" ", $cadena);
    $glue = '';
    foreach ($arr_buscar as $tmp) {
        if ($tmp != "" && strlen($tmp)>=3) {
            $resp .= " $glue to_tsvector('$idioma',
                                          upper($campo))
                             @@ to_tsquery('$idioma', upper('%" . trim($tmp) . "%')) ";
            $glue = 'and';
        }
    }
    $resp = (empty($resp)) ? 'true' : $resp;
    return $resp;
}

function buscar_nombre_cedula($cadena, $buscarInstitucion = 'N') {
    $filtro = '((' . buscar_cadena($cadena, "usua_nombre") . ') or (' . buscar_cadena($cadena, "usua_cedula") . ')';
    if($buscarInstitucion == 'S')
        $filtro .= ' or (' . buscar_cadena($cadena, "inst_nombre") . ')';
    $filtro .= ')';
    return $filtro;
}

function str_limpiar_tildes($cadena) {
//Arma el query para buscar una cadena separada por espacios en un campo de la bdd quitando las tildes y eñes

    $resp = "";
    $cadena = limpiar_sql($cadena);
    $mayusculas = array ("Á", "É", "Í", "Ó", "Ú", "À", "È", "Ì", "Ò", "Ù", "Ä", "Ë", "Ï", "Ö", "Ü", "Â", "Ê", "Î", "Ô", "Û", "Ã", "Õ", "Ñ");
    $minusculas = array ("á", "é", "í", "ó", "ú", "à", "è", "ì", "ò", "ù", "ä", "ë", "ï", "ö", "ü", "â", "ê", "î", "ô", "û", "ã", "õ", "ñ");
    $limpiar_may = array ("A", "E", "I", "O", "U", "A", "U", "I", "O", "U", "A", "E", "I", "O", "U", "A", "E", "I", "O", "U", "A", "O", "N");
    $limpiar_min = array ("a", "e", "i", "o", "u", "a", "e", "i", "o", "u", "a", "e", "i", "o", "u", "a", "e", "i", "o", "u", "a", "o", "n");
    $cadena = str_replace($minusculas, $limpiar_min, $cadena);
    $cadena = str_replace($mayusculas, $limpiar_may, $cadena);
    return $cadena;
}


function buscar_datos_usuario($cadena) {
    //Arma el query para buscar una cadena separada por espacios en los datos del usuario usuario->usua_datos

    $resp = "";
    $cadena = strtoupper(str_limpiar_tildes($cadena));

    $arr_buscar = explode(" ", $cadena);
    foreach ($arr_buscar as $tmp) {
        if (trim($tmp) != "" && strlen($tmp)>=3) {
            $resp .= " and usua_datos like '%" . trim($tmp) . "%'";
        }
    }
    return $resp;
}

function buscar_nombre_cedula_solicitud($cadena) {
    return '((' . buscar_cadena($cadena, "ciu_nombre") . ') or (' . buscar_cadena($cadena, "ciu_cedula") . '))';
}

function buscar_2campos($cadena, $campo1, $campo2) {
    return '((' . buscar_cadena($cadena, $campo1) . ') or (' . buscar_cadena($cadena, $campo2) . '))';
}


function p_register_globals($list = null) {
    return;
}

// Envía un email al destinaratio especificado.
// El $mensaje debe estar en HTML
// $destinatario es el email
// $nombre_dest es el nombre del destinatario
function enviarMail($mensaje, $asunto, $destinatario_ori, $nombre_dest="", $ruta_raiz=".") {
    include "$ruta_raiz/config.php";
    $tmp = explode(",", $destinatario_ori);
    foreach ($tmp as $destinatario) {
        $destinatario = trim($destinatario);
        if ($destinatario != "" and strpos($destinatario, "@") and strpos($destinatario, ".", strpos($destinatario, "@"))) {
            // Estructura de la descripcion de email.
            $email = $destinatario; //recipient
            //$email = $para; //recipient
            $subject = $asunto;

            $despedida = "<br /><br />Saludos cordiales,<br /><br />Soporte Quipux.";
            $despedida .= "<br /><br /><b>Nota: </b>Este mensaje fue enviado autom&aacute;ticamente por el sistema, por favor no lo responda.";
            $despedida .= "<br />Si tiene alguna inquietud respecto a este mensaje, comun&iacute;quese con <a href='mailto:$cuenta_mail_soporte'>$cuenta_mail_soporte</a>";

            $mail_body = str_replace("**SISTEMA**", "<a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>", $mensaje);
            $mail_body = str_replace("**DESPEDIDA**", $despedida, $mail_body);

            $header = 'MIME-Version: 1.0' . "\r\n";
            $header .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
            $header .= "To: $nombre_dest <" . $destinatario . ">" . "\r\n";
            $header .= "From: Quipux <$cuenta_mail_envio>" . "\r\n";

            ini_set('sendmail_from', "$cuenta_mail_envio"); //Suggested by "Some Guy"
            mail($email, $subject, $mail_body, $header); //mail command :)
        }
    }
//echo "<hr>Para: $destinatario_ori<br><br>Asunto: $asunto<br><br>$mail_body<hr>";
}

// Genera un password randómico con un numero determinado de caracteres
function generar_password($num=8) {
    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    $pass = "";
    for ($i = 0; $i < $num; ++$i) {
        $pass .= substr($chars, rand(0, 33), 1);
    }
    return $pass;
}

/**
 *  ObtenerPath
 * */
function ObtenerPath($archivo) {
    $archivo = str_replace(".p7m", "", $archivo);
    return $path_arch = "$ruta_raiz/bodega" . $archivo;
}

/* Muestra las áreas hijas de un área.
 * Si tiene permiso de bandeja de entrada y $tipo="T" muestra todas las áreas de la institución
 */

function buscar_areas_dependientes($area, $tipo="T") {
    global $db;

    $areas = $area;
    // En caso que tenga permiso de "Bandeja de entrada" consulta todas las áreas
    if ($_SESSION["ver_todos_docu"] == 1 and $tipo == "T") {
        $sql = "select depe_codi from dependencia where depe_codi<>$area and depe_estado=1 and inst_codi=" . $_SESSION["inst_codi"];
        $rs = $db->conn->Execute($sql);
        while (!$rs->EOF) {
            $areas .= "," . $rs->fields["DEPE_CODI"];
            $rs->MoveNext();
        }
    } else {
        $areas .= buscar_areas_dependientes_rec($area);
    }
    return $areas;
}

// función recursiva que busca las áreas hijas de un área
function buscar_areas_dependientes_rec($codigo) {
    global $db;
    $areas = "";
    $sql = "select depe_codi from dependencia where depe_codi_padre=$codigo and depe_codi<>$codigo and depe_estado=1";
    $rs = $db->conn->Execute($sql);
    while (!$rs->EOF) {
        $areas .= "," . $rs->fields["DEPE_CODI"];
        $areas .= buscar_areas_dependientes_rec($rs->fields["DEPE_CODI"]);
        $rs->MoveNext();
    }
    return $areas;
}

/**
 * Guardar firma en la base de datos
 * */
function GrabarFirma($db, $firDigCodi, $usua_codi, &$file, $nombre, $extension, $ruta_raiz) {
    if ($ruta_raiz == '')
        $ruta_raiz = "..";

    //Respuesta si hubo un error al anexar el archivo
    $ok = 0; //Error
    //Nombre del archivo
    $tmp = explode("\\", strtolower($nombre));
    $nomb_arch1 = $tmp[count($tmp) - 1];
    $tmp = explode("/", strtolower($nomb_arch1));
    $nomb_arch = $tmp[count($tmp) - 1];
    //Extension del archivo
    $tmp = explode(".", $nomb_arch);
    $flag_firma = false;

    if ($tmp[count($tmp) - 1] == "p7m") {
        $tmp = explode(".", str_replace(".p7m", "", $nomb_arch));
        $flag_firma = true;
    }

    $tmp_ext = $tmp[count($tmp) - 1];
    $ext_arch = substr($nomb_arch, strpos($nomb_arch, $tmp_ext));

    $tipo_arch = $rs[0]["arch_tip_codi"];
    //$anex_nombre = str_replace(" ","_",$nomb_arch);

    unset($recordSet);
    if ($firDigCodi != '')
        $recordSet["FIR_DIG_CODI"] = $firDigCodi;
    $recordSet["USUA_CODI"] = $usua_codi;
    $recordSet["FIR_DIG_CUERPO"] = $db->conn->qstr(limpiar_sql(base64_encode(file_get_contents($file))));
    //$recordSet["FIR_DIG_NOMBRE"] = $db->conn->qstr(limpiar_sql($anex_nombre));
    $recordSet["FIR_DIG_EXT"] = $db->conn->qstr(limpiar_sql($extension));

    $ok = $db->conn->Replace("FIRMA_DIGITALIZADA", $recordSet, "FIR_DIG_CODI", false, false, false, false); //true al final para ver la cadena del insert

    if ($ok) { //Si inserto correctamente
        $ok = 0;
    } else
        $ok = 1;
    return $ok;
}

//Divide las cadenas en varias líneas según un ancho determinado
function dividir_cadenas($cadena, $separador="<br>", $tamanio=60) {
    $resultado = "";
    $pos = 0;
    while (true) {
        $cadena = trim($cadena);
        if (strlen($cadena) <= $tamanio or $pos === false) {
            $resultado .= $cadena;
            return $resultado;
        }
        $pos = strpos($cadena, " ", $tamanio);
        $resultado .= substr($cadena, 0, $pos) . $separador;
        $cadena = substr($cadena, $pos);
    }
}

function validar_mail($email) {
    //autor:                teya
    //fecha:                20110418
    //motivo:               funcion que valida la existencia del mail, pero solo se puede hacer que valide el dominio
    //1. verifica si el mail tiene el formato correcto
    if (trim($email) == "")
        return true; // porque cuando se ingresa por primera vez a la pag viene en blanco
//        if (!eregi("^[_\.0-9a-z\-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,6}$",$email)) {
//            return false; //echo 'direccion no tiene el formato adecuado';
//        }
    //2. verifica si existe el dominio
    //2.1 separamos en caso de venir mas de 1 direccion de correo
    $cadena = str_replace(";",",",$email);
    $correosc = split(",", $cadena );

    //$correospc = split(";", $email);
    $bandera = 0;

    foreach ($correosc as $email1) {

        list ( $Username, $dominio ) = split("@", $email1);
        $MXHost = '';

        if (checkdnsrr($dominio, 'A') || checkdnsrr($dominio, 'MX') || checkdnsrr($dominio, 'NS')
                || checkdnsrr($dominio, 'SOA') || checkdnsrr($dominio, 'PTR') || checkdnsrr($dominio, 'CNAME')
                || checkdnsrr($dominio, 'AAAA') || checkdnsrr($dominio, 'A6') || checkdnsrr($dominio, 'SRV')
                || checkdnsrr($dominio, 'NAPTR') || checkdnsrr($dominio, 'TXT') || checkdnsrr($dominio, 'ANY')
        ) {
            $bandera = 0;
//            if ( !(getmxrr ($dominio, $MXHost)))  {
//                return false; //echo 'no mailbox';
//            }
        } else {
            $bandera = 1; // no hay dominio
            break;
        }
        // return true; // email ok
    }
    if ($bandera == 0 ) return true;
    else return false;
}

function fechaAtexto($fecha){
    $dia = substr($fecha, 8, 2);
    $mes = substr($fecha, 5, 2);
    $anio = substr($fecha, 0, 4);

    /**
     * Creamos un array con los meses disponibles.
     * Agregamos un valor cualquiera al comienzo del array para que los números coincidan
     * con el valor tradicional del mes. El valor "Error" resultará útil
     **/
    $meses = array('Error', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

    /**
     * Si el número ingresado está entre 1 y 12 asignar la parte entera.
     * De lo contrario asignar "0"
     **/
    $num_limpio = $mes >= 1 && $mes <= 12 ? intval($mes) : 0;
    $fechaletras = "a los $dia día(s) del mes de $meses[$num_limpio] de ".anio($anio).".";
    return $fechaletras;
    //return $meses[$num_limpio];
    //return $dia;
}
function anio($anio){
   switch ($anio) {
         case '2011':
          $anioletras="dos mil once";
             break;
         case '2012':
          $anioletras="dos mil doce";
             break;
         case '2013':
          $anioletras="dos mil trece";
             break;
         case '2014':
          $anioletras="dos mil catorce";
             break;
         case '2015':
          $anioletras="dos mil quince";
             break;
         case '2016':
          $anioletras="dos mil dieciseis";
             break;
          default :
            return "";
            break;
   }
   return $anioletras;
}
//crea el combo para las pantallas de solicitud de firma de ciudadano
//$solfirma: recibe del select de cada pantalla
function combo_firma_ciudadano($sol_firma,$db){
    $sqlCmbCiu = "select descripcion, tipo_cert_codi from tipo_certificado where estado = 1 and tipo_cert_codi not in (0)";
    $rsCmbCiu = $db->conn->query($sqlCmbCiu);
    $usr_firma  = $rsCmbCiu->GetMenu2('sol_firma',$sol_firma,"",false,"","Class='select' id='sol_firma'");
    return $usr_firma;
}

function reemplaza_caracteres_html($texto){

    // Cambiamos letras con tildes a formato html
    $origen  = array ("á", "é", "í", "ó", "ú", "à", "è", "ì", "ò", "ù", "ä", "ë", "ï", "ö", "ü"
                    , "â", "ê", "î", "ô", "û", "ã", "õ", "ñ"
                    , "Á", "É", "Í", "Ó", "Ú", "À", "È", "Ì", "Ò", "Ù", "Ä", "Ë", "Ï", "Ö", "Ü"
                    , "Â", "Ê", "Î", "Ô", "Û", "Ã", "Õ", "Ñ"
                    , "ç", "Ç", "°", "º", "ª", "½", "¿", "·", "~"
                    , "©", "®", "™");
    $destino = array ("&aacute;", "&eacute;", "&iacute;", "&oacute;", "&uacute;"
                    , "&agrave;", "&egrave;", "&igrave;", "&ograve;", "&ugrave;"
                    , "&auml;", "&euml;", "&iuml;", "&ouml;", "&uuml;"
                    , "&acirc;", "&ecirc;", "&icirc;", "&ocirc;", "&ucirc;"
                    , "&atilde;", "&otilde;", "&ntilde;"
                    , "&Aacute;", "&Eacute;", "&Iacute;", "&Oacute;", "&Uacute;"
                    , "&Agrave;", "&Egrave;", "&Igrave;", "&Ograve;", "&Ugrave;"
                    , "&Auml;", "&Euml;", "&Iuml;", "&Ouml;", "&Uuml;"
                    , "&Acirc;", "&Ecirc;", "&Icirc;", "&Ocirc;", "&Ucirc;"
                    , "&Atilde;", "&Otilde;", "&Ntilde;"
                    , "&ccedil;", "&Ccedil;", "&deg;", "&ordm;", "&ordf;", "&frac12;", "&iquest;", "&middot;", "&sim;"
                    , "&copy;", "&reg;", "&trade;");
    $texto = str_ireplace($origen, $destino, $texto);

    return $texto;

}

function get_mime_tipe($archivo) {

    $tmp = explode(".",$archivo);
    $ext = $tmp[count($tmp)-1];
    switch( $ext ) {
      case "pdf": return "application/pdf"; break;
      case "exe": return "application/octet-stream"; break;
      case "zip": return "application/zip"; break;
      case "doc": return "application/msword"; break;
      case "xls": return "application/vnd.ms-excel"; break;
      case "ppt": return "application/vnd.ms-powerpoint"; break;
      case "gif": return "image/gif"; break;
      case "png": return "image/png"; break;
      case "jpeg":
      case "jpg": return "image/jpg"; break;
      case "mp3": return "audio/mpeg"; break;
      case "wav": return "audio/x-wav"; break;
      case "mpeg":
      case "mpg":
      case "mpe": return "video/mpeg"; break;
      case "mov": return "video/quicktime"; break;
      case "avi": return "video/x-msvideo"; break;

      case "php":
      case "htm":
      case "html":
      case "txt": return "text/plain"; break;

      default: return "application/force-download";
    }
}

function verificar_dispositivo_movil() {
    return eregi( 'ipod|iphone|ipad|android|opera mini|blackberry|palm os|windows ce|Bada|Windows Phone|Symbian', $_SERVER['HTTP_USER_AGENT'] );
}

function verificar_navegador_firefox() {
    return eregi( 'Firebird|Firefox', $_SERVER['HTTP_USER_AGENT'] );
}

// Dibuja pestañas con divs similares al estandar Quipux
// Requiere como parámetros:
// $id_grupo - nombre del grupo de pestañas en caso que haya más de una en la página y se requiere para ejecutar el onClick
// $pestanas - Arreglo con los nombres de las pestañas
// $default - El id (en el arreglo $pestanas) de la pestaña que sera seleccionada por defecto
// Requere de la función js/funciones_js.js -> fjs_pestanas_seleccionar (pestana)
// Es necesario crear una función con nombre fjs_seleccionar_pestana_ID_GRUPO(id_pestana) que se ejecutará con el onClick en la página que se lo invoque
function fphp_dibujar_pestanas ($id_grupo, $pestanas, $default=0) {
    $tabla = "<table border='0' cellspacing='0'><tr height='20px'>";
    foreach ($pestanas as $id => $pestana) {
        $estilo = ($id==$default) ? "1" : "0";
        $id = $id_grupo . "_" . $id;
        $tabla .= '<td width="115px">
                  <div id="div_pestana_'.$id.'" class="pestana" onClick=\'fjs_pestanas_seleccionar(this);\'>
                        <span id="div_pestana_'.$id.'_texto" class="pestana_texto_'.$estilo.'">'.$pestana.'</span>
                        <span id="div_pestana_'.$id.'_fondo" class="pestana_fondo_'.$estilo.'">&nbsp;</span>
                  </div>
                </td>';
    }
    $tabla .= "</tr></table>";
    return $tabla;
}



// Funciones para cargar los contadores de las bandejas y del menú
include_once "$ruta_raiz/menu/cargar_contadores.php";

// Funciones del calendario
include_once "$ruta_raiz/js/calendario_php/calendario_php.php";
?>
