<?php
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
/*****************************************************************************
**  Muestra las alertas programadas por el administrador del sistema        **
**  Programar para que se ejecute cada 5 minutos                            **
******************************************************************************/

$ruta_raiz = "..";
include_once ("$ruta_raiz/include/db/ConnectionHandler.php");
include_once ("$ruta_raiz/config.php");
error_reporting(7);
$db = new ConnectionHandler("$ruta_raiz");
$db->conn->SetFetchMode(ADODB_FETCH_ASSOC);
if (!$db->conn->_connectionID) die ("Error: No se pudo conectar con la BDD");

$archivo_mensaje = "$ruta_raiz/bodega/mensaje_alerta_top.html";
$archivo_bloqueo = "$ruta_raiz/bodega/mensaje_bloqueo_sistema.html";

// El campo tiempo restante es para que mande alertas en diferentes colores si faltan menos de 8 o 20 minutos en bloqueos generales
$tipo_mensaje = " and tipo_mensaje=2 "; //Solo muestra alertas si $activar_bloqueo_sistema == FALSE
if ($activar_bloqueo_sistema == true) $tipo_mensaje = " and tipo_mensaje in (0,1,2) ";
$sql = "select *
        , case when fecha_inicio<=now() then 0 else (case when (fecha_inicio-'8 minute'::interval)<=now() then 1 else 2 end) end as tiempo_restante
        from bloqueo_sistema
        where estado=1 and now()<fecha_fin $tipo_mensaje
        and (fecha_inicio<=now() or ((fecha_inicio-'20 minute'::interval)<=now() and tipo_mensaje=0))
        order by tipo_mensaje asc, tiempo_restante asc, fecha_inicio asc
        limit 1 offset 0";
$rs = $db->query($sql);

$texto = "";
$mensaje = "";

if ($rs and !$rs->EOF) {
    $mensaje = $rs->fields["MENSAJE_USUARIO"];
    $texto = dibujar_mensaje($rs);
}

// Este texto sirve para validar si cambio el mensaje para que le muestre al usuario
$texto .= '<span id="txt_codigo_mensaje_alerta_top" style="display: none;">'.md5($texto)."</span>";
file_put_contents($archivo_mensaje, $texto);
echo date('Y-m-d H:i:s');//."<br>".$texto;


// Validamos los bloqueos en el sistema
if (isset($rs->fields["TIPO_MENSAJE"]) and $rs->fields["TIPO_MENSAJE"]==0) {
    // si es bloqueo general bota a todos los usuarios del sistema y restringe el acceso a nuevos usuarios
    file_put_contents($archivo_bloqueo, $mensaje);
    $sql = "update usuarios_sesion set usua_sesion='FIN - BLOQUEO ".$rs->fields["BLOQ_CODI"]."'
            where usua_sesion not like 'FIN%'
                and usua_codi not in (". str_replace("-", "", str_replace("--", ",", "-0-".$rs->fields["USUA_ACCESO"])).")";
    $db->query($sql);
} elseif (isset($rs->fields["TIPO_MENSAJE"]) and $rs->fields["TIPO_MENSAJE"]==1) {
    file_put_contents($archivo_bloqueo, $mensaje);
} else {
    if (is_file($archivo_bloqueo))
        unlink ($archivo_bloqueo);
}



// Crea el div que se mostrará al usuario con el mensaje
function dibujar_mensaje($rs) {
    $texto = "";
    $titulo = "";
    if ($rs and !$rs->EOF) {
        $mensaje = $rs->fields["MENSAJE_USUARIO"];
        $boton_cerrar = '<img src="./imagenes/close_button.gif" title="cerrar" alt="X" onclick="ocultar_mensaje_alerta()">';
        $text_color = "#000000";
        $back_color = "#e3e8ec";
        $bord_color = "#086478";

        if ($rs->fields["TIPO_MENSAJE"] == 0) { //Bloqueos Generales
            $boton_cerrar = "";
            if ($rs->fields["TIEMPO_RESTANTE"] == 0) { // Ya se prodijo el bloqueo (Rojo)
                $titulo = "<center><blink><b>&iexcl; ATENCI&Oacute;N !</b></blink></center><br>
                    El sistema ha sido bloqueado.<br>";
                $back_color = "#F5A9A9";
                $bord_color = "#B40404";
            } elseif ($rs->fields["TIEMPO_RESTANTE"] == 1) { // Si faltan 5 minutos (Naranja)
                $titulo = "<center><blink><b>&iexcl; ATENCI&Oacute;N !</b></blink></center><br>
                           El sistema ser&aacute; bloqueado a las ".substr($rs->fields["FECHA_INICIO"],11,5).
                         " por el administrador del sistema. Por favor guarde sus cambios y salga del sistema.<br>";
                $back_color = "#FAD184";
                $bord_color = "#DF7401";
            } else { // Si faltan 20 minutos (Amarillo)
                $titulo = "<center><b>&iexcl; ATENCI&Oacute;N !</b></center><br>
                    El sistema ser&aacute; bloqueado a las ".substr($rs->fields["FECHA_INICIO"],11,5)." por el administrador del sistema.<br>";
                $back_color = "#FFFFCC";
                $bord_color = "#D7DF01";
            }
        }

        // dibujamos la alerta
        $texto .= '<div id="div_mensaje_alerta_top" style="border: '.$bord_color.' 1px solid; width: 500px; background-color: '.$back_color.';">
            <center>
            <table width=99% border="0" cellpadding="0" cellspacing="2">
                    <tr>
                        <td style="width: 95%; font-size: 10px; text-align: left; color: '.$text_color.';">'.$titulo.$rs->fields["MENSAJE_USUARIO"].'</td>
                        <td style="width: 5%; text-align: right; vertical-align: top;">'.$boton_cerrar.'</td>
                    </tr>
                  </table>
                  </center>
                  </div>';

        if ($rs->fields["TIPO_MENSAJE"] == 1) $texto = ""; // si es bloqueo parcial no muestra alertas para los usuarios conectados
    }
    return $texto;
}



?>
