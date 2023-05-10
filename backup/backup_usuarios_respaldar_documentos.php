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

**************************************************************************************
** Respalda la documentacion de los usuarios                                        **
** Genera un archivo HTML con la documentación de los usuarios y lo guarda en la    **
** carpeta bodega/respaldos/respaldo, copia también los anexos al documento y todo  **
** lo necesario para el respaldo.                                                   **
** Genera un solo respaldo por documento y guarda esta información en todos los     **
** respaldos que requieren este documento.                                          **
**                                                                                  **
** Desarrollado por:                                                                **
**      Mauricio Haro A. - mauricioharo21@gmail.com                                 **
*************************************************************************************/

$ruta_raiz= "..";
include_once "$ruta_raiz/config.php";
include_once "$ruta_raiz/include/db/ConnectionHandler.php";
include_once "$ruta_raiz/funciones.php";
include_once "$ruta_raiz/obtenerdatos.php";
include_once "$ruta_raiz/plantillas/generar_documento.php";
include_once "$ruta_raiz/backup/backup_usuarios_respaldar_documentos_html.php";


$db = new ConnectionHandler("$ruta_raiz");
$db->conn->SetFetchMode(ADODB_FETCH_ASSOC);
$db_bodega = new ConnectionHandler("$ruta_raiz", "bodega");
$db_bodega->conn->SetFetchMode(ADODB_FETCH_ASSOC);
$pdf = New GenerarDocumento($db);

$resp_codi = limpiar_numero($_POST["resp_codi"]);

$sql = "select radi_nume_radi from respaldo_usuario_radicado where resp_codi=$resp_codi and fila is null and num_error=0 limit 1";
$rs_respaldo = $db->query($sql);
if (!$rs_respaldo or $rs_respaldo->EOF) {
    $sql = "select radi_nume_radi from respaldo_usuario_radicado where resp_codi=$resp_codi and fila is null and num_error<3 order by num_error limit 1";
    $rs_respaldo = $db->query($sql);
    if (!$rs_respaldo or $rs_respaldo->EOF) die ("Finalizado");
}
$radi_nume = $rs_respaldo->fields["RADI_NUME_RADI"];

unset($path_anexos);
$path_anexos = array(); // Se guardan todos los paths de los archivos que se deben copiar

try {
    $html = cargar_html(); // Genera el html para cada documento
    $fila = base64_encode(cargar_fila()); // Genera lso datos para la pagina principal
} catch (Exception $e) {
    set_error("Error al generar HTML: ". $e->__toString());
}
try {
    $path = "$ruta_raiz/bodega/respaldos/respaldo_$resp_codi";
    file_put_contents ("$path/documentos/$radi_nume.html", $html);
    copiar_archivos ($path);
} catch (Exception $e) {
    set_error("Error copiar archivos: ". $e->__toString());
}

$sql = "update respaldo_usuario_radicado set fila=E'$fila', num_error=0 where radi_nume_radi=$radi_nume and resp_codi=$resp_codi";
$db->query($sql);

function copiar_archivos ($path) {
    global $path_anexos, $ruta_raiz, $db_bodega;
    foreach ($path_anexos as $path_archivo) {
        $nomb_arch = "";
        if ($path_archivo[0]!='' && (0+$path_archivo[1])==0 && (0+$path_archivo[2])==0) {
            $path_origen = "$ruta_raiz/bodega".trim($path_archivo[0]);
            $tmp=explode("/",strtolower($path_origen));
            $tmp=explode("\\",$tmp[count($tmp)-1]);
            $path_destino = "$path/archivos/".trim($tmp[count($tmp)-1]);
            if (is_file($path_origen) and !is_file ($path_destino)) {
                $ok = copy ($path_origen , $path_destino);
                if (!$ok) set_error("Error al copiar archivo: $path_origen");
            }
            if (substr(strtolower($path_origen),-4) == ".p7m") {
                if (is_file(substr($path_origen,0,-4)) and !is_file(substr($path_destino,0,-4))) {
                    $ok = copy (substr($path_origen,0,-4) , substr($path_destino,0,-4));
                    if (!$ok) set_error("Error al copiar archivo: ".substr($path_origen,0,-4));
                }
            }
        } else {
            if ((0+$path_archivo[1])!=0) {
                $nomb_arch = $path_archivo[1].".".$path_archivo[3];
                $rs_bodega = $db_bodega->query("select func_recuperar_archivo(".(0+$path_archivo[1]).") as archivo");
                if (!$rs_bodega or $rs_bodega->EOF or trim($rs_bodega->fields["ARCHIVO"])=="")
                    set_error("Error al obtener archivo: ".$path_archivo[1]);
                $ok = file_put_contents("$path/archivos/$nomb_arch", base64_decode(trim($rs_bodega->fields["ARCHIVO"])));
                if (!$ok) set_error("Error al grabar archivo: "."$path/archivos/$nomb_arch");
            }
            if ((0+$path_archivo[2])!=0) {
                $nomb_arch = $path_archivo[2].".".$path_archivo[3].".p7m";
                $rs_bodega = $db_bodega->query("select func_recuperar_archivo(".(0+$path_archivo[2]).") as archivo");
                if (!$rs_bodega or $rs_bodega->EOF or trim($rs_bodega->fields["ARCHIVO"])=="")
                    set_error("Error al obtener archivo: ".$path_archivo[2]);
                $ok = file_put_contents("$path/archivos/$nomb_arch", base64_decode(trim($rs_bodega->fields["ARCHIVO"])));
                if (!$ok) set_error("Error al grabar archivo: "."$path/archivos/$nomb_arch");
            }
        }
    }
}

function set_error ($mensaje) {
    global $db, $radi_nume;
    $sql = "update respaldo_usuario_radicado
            set num_error=num_error+1, error=E'$mensaje', fila=null
            where radi_nume_radi=$radi_nume and resp_codi=$resp_codi";
    $db->query($sql);
    die ("OK");
}

die ("OK");
?>