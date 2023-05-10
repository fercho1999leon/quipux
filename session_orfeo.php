<?
/**  Programa para el manejo de gestion documental, oficios, memorandos, circulares, acuerdos
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
/*****************************************************************************************
**											**
*****************************************************************************************/
if ($_SESSION["session_dos_bloquear_usuario"]) {
    die ("<center><font color='red'>
              <br><br>Se est&aacute;n recibiendo muchas peticiones desde su cuenta de usuario.
              <br><br>Esto puede ser producido por un error en el navegador.
              <br><br>Por favor cierre su navegador y vuelva a ingresar al sistema.</font>
          </center>");
}

header("Cache-Control: max-age=600");

if (str_replace("/","",str_replace(".","",$ruta_raiz))!="")
    die ("<br/><center><font size='6' color='red'><b>HA SIDO DETECTADO UN INTENTO DE VIOLACI&Oacute;N DE LAS SEGURIDADES DEL SISTEMA<br/>SU N&Uacute;MERO IP SER&Aacute; BLOQUEDO PERMANENTEMENTE</b></font>");
include_once ("$ruta_raiz/include/db/ConnectionHandler.php");
include_once ("$ruta_raiz/config.php");
include_once ("$ruta_raiz/config_replicacion.php");
error_reporting(7);
$db = new ConnectionHandler("$ruta_raiz");
$db->conn->SetFetchMode(ADODB_FETCH_ASSOC);
if(!$db->conn->_connectionID){
    die ("<script>top.window.location='$ruta_raiz/paginasinConexion.php'</script>");
}

// Bloquear ataques tipo DoS
if ($_SESSION["session_dos_pagina"] == $_SERVER["REQUEST_URI"]) {
    if ((mktime()-$_SESSION["session_dos_hora"])>2) {
        $_SESSION["session_dos_num_accesos"] = 1;
        $_SESSION["session_dos_hora"] = mktime();
    } else {
        if ($_SESSION["usua_codi"]!=0 and substr($_SESSION["session_dos_pagina"],0,38)!="/Administracion/usuarios_dependencias/") {
                ++$_SESSION["session_dos_num_accesos"];
        }
    }
    if ($_SESSION["session_dos_num_accesos"]>=10) {
        $_SESSION["session_dos_bloquear_usuario"] = true;
        $log_dos["fecha"] = $db->conn->sysTimeStamp;
        $log_dos["usua_codi"] = $_SESSION["usua_codi"];
        $log_dos["pagina"] = $db->conn->qstr($_SERVER["REQUEST_URI"]);
        $log_dos["navegador"] = $db->conn->qstr($_SERVER["HTTP_USER_AGENT"]);
        $log_dos["ip"] = $db->conn->qstr($_SERVER['HTTP_X_FORWARDED_FOR']." - ".$_SERVER['HTTP_CLIENT_IP']." - ".$_SERVER['REMOTE_ADDR']);
        $log_dos["num_accesos"] = $_SESSION["session_dos_num_accesos"];
        $db->conn->Replace("log_bloqueos_dos", $log_dos, "", false,false,false,false);
        die ("<center><font color='red'>
                  <br><br>Se est&aacute;n recibiendo muchas peticiones desde su cuenta de usuario.
                  <br><br>Esto puede ser producido por un error en el navegador.
                  <br><br>Por favor cierre su navegador y vuelva a ingresar al sistema.</font>
              </center>");
    }
} else {
    $_SESSION["session_dos_pagina"] = $_SERVER["REQUEST_URI"];
}


//Validación de las varibles de configuración de la BDD
if (!isset ($replicacion)) $replicacion = false; // Indica que no hay replicación
if (!isset ($version_light)) $version_light = false;

include "$ruta_raiz/include/local/$FILE_LOCAL";
require_once "$ruta_raiz/funciones.php";

/**
* Validar si la variable de sesion de login es diferente e vacio.
* Si guarda en la variable $krd el valor de la variable de sesion
* No guarda en la variable $krd el valor de la variable cambio a mayusculas de $krd pasada por url.
**/
//Se incluyo por register globals
  $drd = (isset($_POST['drd'])) ? $_POST['drd'] : "";
  $drd = limpiar_sql($drd);
  $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
  $acceso = (isset($_GET['acceso'])) ? $_GET['acceso'] : "";
  $usua_codi=$_SESSION["usua_codi"];
  $recordSet = array();

//La variable acceso es traida desde el formulario login.php
if (!isset($acceso)) $acceso = "";
if ($acceso=="login") {
    if(isset($_SESSION["krd"]))
        unset($_SESSION["krd"]);
    /**
    * Si el usuario es ciudadano se añade una letra 'C' al login.
    **/
//    if ($tipo_usuario=="c")
//       $krd="C".$krd;
//    if ($tipo_usuario=="u")
       $krd="U".$krd;
}

if (isset($_SESSION["krd"]))
    $krd = $_SESSION["krd"];

$krd = limpiar_sql(strtoupper($krd));

$ValidacionKrd = "";
$flag = true;

/**
* Validacion de permisos de acceso por usuario al sistema, si el usuario intenta acceder al sistema y a tenido
* mas de 5 intentos fallidos en el ingreso de su contraseña el usuario sera bloqueado por unos minutos.
**/

if ($acceso=="login") {
    $query = "SELECT usua_codi, usua_pasw FROM usuario WHERE usua_login='$krd'";
    $tmp_rs = $db->query($query);

    //Guardar log de accesos
    unset($recordSet);
    $recordSet["FECHA"] = $db->conn->sysTimeStamp;
    $recordSet["USUARIO"] = $db->conn->qstr(substr($krd,1,50));
    $recordSet["IP"] = $db->conn->qstr($_SERVER['HTTP_X_FORWARDED_FOR']." - ".$_SERVER['HTTP_CLIENT_IP']." - ".$_SERVER['REMOTE_ADDR']);
    $recordSet["INTENTOS"] = ($tmp_rs->EOF) ? "0" : "1";
    $recordSet["ACCESO"] = (substr($drd,1,26)==$tmp_rs->fields["USUA_PASW"]) ? "1" : "0";
    $db->conn->Replace("LOG_ACCESO", $recordSet, "", false,false,false,false);

    if (!$tmp_rs->EOF) {
        $tmp_usr = $tmp_rs->fields["USUA_CODI"];
        $tmp_pwd = $tmp_rs->fields["USUA_PASW"];
        $query = "SELECT *, (".$db->conn->sysTimeStamp."-usua_fech_sesion) as \"tiempo\"
                  FROM usuarios_sesion WHERE usua_codi=$tmp_usr";
        $tmp_rs = $db->query($query);
        $tmp_tiempo = $tmp_rs->fields["TIEMPO"];
        $tmp_intentos = $tmp_rs->fields["USUA_INTENTOS"]+1;

        if (substr($tmp_tiempo,0,2)==0 and substr($tmp_tiempo,3,2)<=5 and $tmp_intentos>5) {
            echo "<script>alert('Su cuenta ha sido bloqueada por seguridad. Por favor espere 5 minutos y vuelva a intentarlo');</script>";
            include_once "$ruta_raiz/funciones_interfaz.php";
            $mensaje = "Su cuenta ha sido bloqueada por seguridad.<br>Por favor espere 5 minutos y vuelva a intentarlo.";
            $mensaje .= "<br><br>Para ir a la pantalla de ingreso, haga click&nbsp<a href=\"$ruta_raiz/login.php\" target=\"_parent\" class=\"aqui\">&quot;AQUI&quot;</a>";
            die (html_error($mensaje));
            include "$ruta_raiz/login.php";
            die ($error);
        }

        if (!(substr($tmp_tiempo,0,2)==0 and substr($tmp_tiempo,3,2)<=5))
            $tmp_intentos = 1;
        unset ($recordSet);
        $recordSet["USUA_FECH_SESION"] = $db->conn->sysTimeStamp;
        $recordSet["USUA_CODI"] = "$tmp_usr";
        $recordSet["USUA_INTENTOS"] = "$tmp_intentos";
        $db->conn->Replace("USUARIOS_SESION", $recordSet, "USUA_CODI", false,false,false,false);
    }
}


if ($acceso=="login") {
    // Consultamos todos los datos del usuario si accede desde el login
    $query = "SELECT * FROM USUARIO WHERE USUA_LOGIN ='$krd' AND (USUA_PASW ='". SUBSTR($drd,1,26) ."' or USUA_NUEVO=0) and (inst_estado=1 or usua_codi=0) order by tipo_usuario asc, usua_codi asc";
    $flag = false;
} else {
    // Verificamos que no se conecten desde 2 maquinas con el mismo usuario
    if (trim($_SESSION["usua_codi"])=="") $_SESSION["usua_codi"] = -99;
    $query = "SELECT * FROM usuarios_sesion WHERE usua_codi=".$_SESSION["usua_codi"]." and usua_sesion LIKE '".session_id()."'";
}
//echo $query;
$rs = $db->query($query);

// Bloqueo del ingreso a ciudadanos
if ($config_bloquear_acceso_ciudadano && ($rs->fields["TIPO_USUARIO"]==2 or $_SESSION["tipo_usuario"]==2)) {
    include_once "$ruta_raiz/funciones_interfaz.php";
    $mensaje .= "Lo sentimos, al momento se encuentra restingido el acceso al sistema Quipux para usuarios ciudadanos.";
    $mensaje .= "<br><br>Para ir a la pantalla de ingreso, haga click&nbsp<a href=\"$ruta_raiz/login.php\" target=\"_parent\" class=\"aqui\">&quot;AQUI&quot;</a>";
    die (html_error($mensaje));
}


if ($activar_bloqueo_sistema) {
    /* Valida bloqueos en el sistema, sacando a todos los usuarios hasta que el bloqueo sea desactivado */
    $tipo_mensaje = "0";
    if ($acceso=="login") $tipo_mensaje = "0,1";
    $query = "SELECT * FROM bloqueo_sistema WHERE estado=1 and tipo_mensaje in ($tipo_mensaje) and
                fecha_inicio <= ".$db->conn->sysTimeStamp." and ".$db->conn->sysTimeStamp."<fecha_fin
                and coalesce(usua_acceso,'')||'-0-' not like '%-".$rs->fields["USUA_CODI"]."-%'";
    $rs2 = $db->query($query);
    if ($rs2 && !$rs2->EOF) {
        include_once "$ruta_raiz/funciones_interfaz.php";
        $mensaje = $rs2->fields["MENSAJE_USUARIO"];
        //$mensaje .= "<br><br>Para ir a la pantalla de ingreso, haga click&nbsp<a href=\"$ruta_raiz/login.php\" target=\"_parent\" class=\"aqui\">&quot;AQUI&quot;</a>";
        die (html_error($mensaje));
    }
}

/**
* Si la variable $flag = true despliega mensaje de error
* Caso contrario carga de datos en variables tipo $_SESSION.
**/
if ($flag) {
    if ($rs->EOF) { // Si no encontró la sesión. Crea log de session
        //throw new Exception($query . "Tiene".$krd);
        unset($recordSet);
        $recordSet["FECHA"] = $db->conn->sysTimeStamp;
        $recordSet["USUARIO"] = "E'$krd - ".$_SESSION["krd"]."'";
        $dir_cliente = $_SERVER['HTTP_X_FORWARDED_FOR'] . " - " . $_SERVER['HTTP_CLIENT_IP'] . " - " . $_SERVER['REMOTE_ADDR'];
        if (trim($krd)=="") {
            $recordSet["DESCRIPCION"] = $db->conn->qstr(session_id()."Se perdió la sesión para el usuario de la máquina $dir_cliente");
        } else {
            $query = "SELECT s.ip_cliente FROM USUARIOS u, usuarios_sesion s WHERE u.USUA_LOGIN ='$krd' and u.usua_codi=s.usua_codi";
            $rs = $db->query($query);
            $recordSet["DESCRIPCION"] = $db->conn->qstr("Ingresaron desde otra máquina con el mismo usuario. IP máquina actual = $dir_cliente. IP del otro usuario: ".$rs->fields["IP_CLIENTE"]);
        }
        $db->conn->Replace("LOG_SESION", $recordSet, "", false,false,false,false);
       // echo "Aqui fue antes Log Session";
        include "$ruta_raiz/paginaError.php";
        die ("<script>top.window.location='$ruta_raiz/paginaError.php'</script>");
    } else {
        if ((mktime()-$_SESSION["hora_session"])<=1800) { //Tiempo en segundos 30min = 1800seg
            $_SESSION["hora_session"] = mktime();
            $ValidacionKrd = "Si";
        } else {  // Si exedió el tiempo de conexión. Crea log de sessión
            unset($recordSet);
            $recordSet["FECHA"] = $db->conn->sysTimeStamp;
            $recordSet["USUARIO"] = $db->conn->qstr(session_id());
            $recordSet["DESCRIPCION"] = $db->conn->qstr("Tiempo - ".(mktime()-$_SESSION["hora_session"])." Segundos");
            $db->conn->Replace("LOG_SESION", $recordSet, "", false,false,false,false);
            include "$ruta_raiz/paginaError.php";
            die ("<script>top.window.location='$ruta_raiz/paginaError.php'</script>");
        }
    }
} else {
	/**
	* Verifica si el login del usuario ingresado desde la página de login es igual al de la BDD, Caso contrario
	* error de usuario o contraseña incorrectos.
	**/
	if (trim($rs->fields["USUA_LOGIN"])==$krd) {
	    $perm_radi_salida_tp = 0;

	    /**
	    * Verifica si el usuario esta activo caso contrario presenta mensaje de error.
	    **/
		//echo "login---".$rs->fields["USUA_LOGIN"]."---estado usuario---".$rs->fields["USUA_ESTA"];
	    if (trim($rs->fields["USUA_ESTA"])==1) {
		//echo "usuarioo--".$_SESSION["usua_codi"];
            if (!isset($_SESSION["usua_codi"])) {
                $dependencia=$rs->fields["DEPE_CODI"];
                $depe_nomb =$rs->fields["DEPE_NOMB"];
                $inst_codi = $rs->fields["INST_CODI"];
                $inst_nombre = $rs->fields["INST_NOMBRE"];
                $codusuario =$rs->fields["USUA_CODI"];
                $usua_codi =$rs->fields["USUA_CODI"];
                $cargo_tipo =$rs->fields["CARGO_TIPO"];
                $usua_doc =$rs->fields["USUA_CEDULA"];
                $usua_nomb =$rs->fields["USUA_NOMBRE"];
                $usua_nuevo = $rs->fields["USUA_NUEVO"];
                $usua_email =$rs->fields["USUA_EMAIL"];
                $contraxx=$rs->fields["USUA_PASW"];
                $tipo_usuario=$rs->fields["TIPO_USUARIO"];
                $nivelus = "1";

                /**
                * Inicia nueva session
                **/
                session_id(str_replace(".","o",$_SERVER['REMOTE_ADDR'])."o$krd"."o".time("His")."o$appID");
                session_start();

                if (!$dependencia) $dependencia=0;
    //                $fechah = date("Ymd"). "_". time("hms");
                $carpeta = 0;
                $dirOrfeo = str_replace("login.php","",$PHP_SELF);
                $_SESSION["mostrar_logs"] = $mostrar_logs;
                $_SESSION["grabar_logs"] = $grabar_logs;
                $_SESSION["inst_codi"] = $inst_codi;
                $_SESSION["inst_nombre"] = $inst_nombre;
                $_SESSION["krd"] = $krd;
                $_SESSION["dirOrfeo"] = $dirOrfeo;
                $_SESSION["drde"] = $contraxx;
                $_SESSION["usua_doc"] = trim($usua_doc);
                $_SESSION["dependencia"] = $dependencia;
                $_SESSION["depe_codi"] = $dependencia;
                $_SESSION["depe_nomb"] = $depe_nomb;
                $_SESSION["codusuario"] = $codusuario;
                $_SESSION["usua_codi"] = $codusuario;
                $_SESSION["cargo_tipo"] = $cargo_tipo;
                $_SESSION["usua_nomb"] = $usua_nomb;
                $_SESSION["usua_nuevo"] = $usua_nuevo;
                $_SESSION["tipo_usuario"] = $tipo_usuario;
                $_SESSION["usua_email"] = $usua_email;
                $_SESSION["nivelus"] = $nivelus;
                $_SESSION["depe_codi_padre"] = $depe_codi_padre;
                // Bandeja compartida
                $rsComp = $db->query("select usua_codi_jefe from bandeja_compartida where usua_codi=$codusuario");
                $_SESSION["usua_codi_jefe"] = (!$rsComp->EOF) ? 0+$rsComp->fields["USUA_CODI_JEFE"] : 0;

                
//                $_SESSION["radi_nume_radi"] = "";
                $_SESSION["tpNumRad"] = $tpNumRad;
                $_SESSION["tpDescRad"] = $tpDescRad;
                $_SESSION["fechah"] = $fechah;

                //Hora en la que el usuario inicia o actualiza su session
                $_SESSION["hora_session"] = mktime();
                $_SESSION["session_dos_pagina"] = "";
                $_SESSION["session_dos_num_accesos"] = 0;
                $_SESSION["session_dos_hora"] = 0;
                $_SESSION["session_dos_bloquear_usuario"] = false;

                /**
                * cargamos los permisos del usuario.
                **/
                $query = "select p.nombre, count(pc.id_permiso) as permiso
                from permiso p left outer join permiso_usuario pc on p.id_permiso=pc.id_permiso
                and pc.usua_codi=$usua_codi group by p.nombre";
                $rs = $db->query($query);
                //echo "<hr>$query<hr>";
                while($rs && !$rs->EOF) {
                    $nom_perm = $rs->fields["NOMBRE"];
                    $_SESSION[$nom_perm] = $rs->fields["PERMISO"];
                    $rs->MoveNext();
                }
                //	var_dump($_SESSION);
                //	die("");
                //  incluimos las Variables locales del sistema
                // include "$ruta_raiz/include/local/varSession.php";
            }

            $dir_cliente = $_SERVER['HTTP_X_FORWARDED_FOR'] . " - " . $_SERVER['HTTP_CLIENT_IP'] . " - " . $_SERVER['REMOTE_ADDR'];
            unset($recordSet);
            $recordSet["USUA_SESION"] = $db->conn->qstr(session_id());
            $recordSet["USUA_FECH_SESION"] = $db->conn->sysTimeStamp;
            $recordSet["USUA_CODI"] = "$usua_codi";
            $recordSet["USUA_INTENTOS"] = "0";
            $recordSet["IP_CLIENTE"] = $db->conn->qstr($dir_cliente);
            $db->conn->Replace("USUARIOS_SESION", $recordSet, "USUA_CODI", false,false,true,false);
            $ValidacionKrd = "Si";
	    } else {
    		$ValidacionKrd="Errado ....";
?>
            <script language="JavaScript" type="text/JavaScript">
                alert('El usuario "<?=substr($krd,1)?>" se encuentra inactivo \n por favor consulte con el administrador del sistema');
            </script>
<?
	    }

	} else {
	    if($recOrfeo=="Seguridad") {
            unset($recordSet);
            $recordSet["FECHA"] = $db->conn->sysTimeStamp;
            $recordSet["USUARIO"] = "E'$krd'";
            $recordSet["DESCRIPCION"] = $db->conn->qstr("Error2 - " . $rs->fields["USUA_LOGIN"] . "== $krd - $query");
            $db->conn->Replace("LOG_SESION", $recordSet, "", false,false,false,false);
            //echo "Aqui fue Seguridad";
            include "$ruta_raiz/paginaError.php";
            die ("<script>top.window.location='$ruta_raiz/paginaError.php'</script>");
        }
?>
        <script type="text/javascript" language="javascript">
            alert('Usuario o Contraseña incorrectos, Por favor intente de nuevo');
        </script>
<?

    }
}
?>
