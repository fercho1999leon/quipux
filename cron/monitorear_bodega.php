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
include_once "$ruta_raiz/include/db/ConnectionHandler.php";
include_once "$ruta_raiz/config.php";
include_once "$ruta_raiz/funciones.php";

error_reporting(7);
$db_bodega = new ConnectionHandler("$ruta_raiz", "bodega");
$db_bodega->conn->SetFetchMode(ADODB_FETCH_ASSOC);

$mensaje = "";
$porcentaje_uso_total = 0;

//if (!$db->conn->_connectionID) die ("Error: No se pudo conectar con la BDD");
if (!$db_bodega->conn->_connectionID) die ("Error: No se pudo conectar con la BDD");

$sql = "select -- cron/monitorear_bodega
              i.nombre_tabla
            , round(pg_table_size(i.nombre_tabla)::numeric/1073741824,2) as tamanio_actual
            , round(i.tamanio_maximo::numeric/1073741824,2) as tamanio_maximo
            , round(pg_table_size(i.nombre_tabla)::numeric/i.tamanio_maximo::numeric*100,2) as porcentaje_uso
            , i.indi_codi
            , pg_table_size(i.nombre_tabla) as tamanio_tabla
         from indice i
         where i.esta_codi=2 order by i.indi_codi";
$rs_tamanio = $db_bodega->query($sql);
if (!$rs_tamanio or $rs_tamanio->EOF) $mensaje .= "<br>No se encontraron repositorios activos";

while ($rs_tamanio and !$rs_tamanio->EOF) {
    $porcentaje_uso = 0+$rs_tamanio->fields["PORCENTAJE_USO"];
    $tamanio_maximo = 0+$rs_tamanio->fields["TAMANIO_MAXIMO"];
    $tamanio_actual = 0+$rs_tamanio->fields["TAMANIO_ACTUAL"];
    $nombre_tabla   = $rs_tamanio->fields["NOMBRE_TABLA"];

    $datos["indi_codi"] = 0+$rs_tamanio->fields["INDI_CODI"];
    $datos["tamanio"] = $rs_tamanio->fields["TAMANIO_TABLA"];
    $db_bodega->conn->Replace("indice", $datos, "indi_codi", false,false,true,false);

    $porcentaje_uso_total = ($porcentaje_uso_total < $porcentaje_uso) ? $porcentaje_uso : $porcentaje_uso_total;
    $mensaje .= "<br>Uso del repositorio $nombre_tabla: $tamanio_actual de $tamanio_maximo Gb ($porcentaje_uso%) al ".date("Y-m-d");
    $rs_tamanio->MoveNext();
}

echo "Fecha: ".date("Y-m-d H:i:s")."<br>$mensaje<br><br>";
if ($porcentaje_uso_total < 80) die (""); // Si el % de uso de todos los repositorios es menor al 80% no hace nada

// Busco a los destinatarios del mensaje
$db = new ConnectionHandler("$ruta_raiz");
$db->conn->SetFetchMode(ADODB_FETCH_ASSOC);

$sql = "select usua_email from usuario where (usua_codi in (select usua_codi from permiso_usuario where id_permiso=35) or usua_codi=0) and usua_esta=1";
$rs_usr = $db->query($sql);
$email = $amd_email;
while ($rs_usr && !$rs_usr->EOF) {
    $email .= ",".$rs_usr->fields["USUA_EMAIL"];
    $rs_usr->MoveNext();
}
echo "Notificado a: $email<br><br>";

$asunto = "QUIPUX - URGENTE: Estado del repositorio de archivos en la BDD";

$mensaje = "Fecha de la notificaci&oacute;n: ".date("Y-m-d H:i:s")."<br><br>
            Estimado Administrador:<br><br>
            El uso de los repositorios de archivos del Sistema de Gesti&oacute;n Documental Quipux **SISTEMA**
            es el siguiente:<br>$mensaje<br><br>Por favor gestione la asignaci&oacute;n de espacio
            y creaci&oacute;n de un nuevo repositorio. **DESPEDIDA**";

enviarMail($mensaje, $asunto, "$email,mauricioharo21@gmail.com", "", $ruta_raiz);
die ("$mensaje");
?>
