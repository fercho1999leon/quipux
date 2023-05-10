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

****************************************************************************************
** Empaqueta uno por uno los respaldos que ya finalizaron                             **
** Busca los backups de los que ya se respaldaron todos los documentos y los comprime **
** Genera los archivos de bandejas, cabeceras, menú, etc. y copia las imágenes        **
** necesarias, limpia los temporales utilizados y genera un archivo ".zip" para       **
** entregarlo al usuario.                                                             **
**                                                                                    **
** Desarrollado por:                                                                  **
**      Mauricio Haro A. - mauricioharo21@gmail.com                                   **
***************************************************************************************/

$ruta_raiz= "..";
include_once "$ruta_raiz/config.php";
include_once "$ruta_raiz/include/db/ConnectionHandler.php";
include_once "$ruta_raiz/funciones.php";
include_once "$ruta_raiz/obtenerdatos.php";
include_once "$ruta_raiz/backup/backup_usuarios_generar_zip_html.php";
include_once "respaldo_funciones.php";

$db = new ConnectionHandler("$ruta_raiz");
$db->conn->SetFetchMode(ADODB_FETCH_ASSOC);

try {
    $resp_codi=limpiar_sql($_POST["txt_resp_codi"]);

    $sql = "select count(resp_codi) as \"num\" from respaldo_usuario_radicado where fila is null and resp_codi=$resp_codi";
    $rs = $db->query($sql);
    if ($rs->fields["NUM"] > 0) die ("OK");

    $path = "$ruta_raiz/bodega/respaldos/respaldo_$resp_codi";

    // Copiamos las imágenes y creamos los archivos index, menú, bandejas, etc.
    copy ("$ruta_raiz/img/content/down_icon.png" , "$path/archivos/descargar.png");
    copy ("$ruta_raiz/img/content/regresar.png" , "$path/archivos/regresar.png");
    copy ("$ruta_raiz/quipux-logo.png" , "$path/archivos/logo.png");
    copy ("$ruta_raiz/quipux-logo.png" , "$path/archivos/logo.png");
    copy_r ("$ruta_raiz/estilos/jquery" , "$path/documentos/jquery");
    copy ("$ruta_raiz/js/jquery.js" , "$path/documentos/jquery/jquery.js");
    copy ("$ruta_raiz/js/jquery_tablas.js" , "$path/documentos/jquery/jquery_tablas.js");
    $html = cargar_estilos();
    file_put_contents ("$path/documentos/estilos.css", $html);
    $html = cargar_index();
    file_put_contents ("$path/index.html", $html);
    $html = cargar_top ($resp_codi);
    file_put_contents ("$path/documentos/top.html", $html);
    $html = cargar_menu ();
    file_put_contents ("$path/documentos/menu.html", $html);
    $html = cargar_informacion($resp_codi);
    file_put_contents ("$path/documentos/informacion.html", $html);
    $html = cargar_bandejas ($resp_codi, 2);
    file_put_contents ("$path/documentos/recibidos.html", $html);
    $html = cargar_bandejas ($resp_codi, 1);
    file_put_contents ("$path/documentos/enviados.html", $html);

    $path_actual = exec("pwd");

    chdir($path);
    shell_exec("zip -s 1g -r ../respaldo_$resp_codi.zip *");
    if (!is_file("../respaldo_$resp_codi.zip")) {
        shell_exec("zip -r ../respaldo_$resp_codi.zip *");
        if (!is_file("../respaldo_$resp_codi.zip")) die ("ERROR - No se pudo generar el archivo ZIP");
    }
    chdir($path_actual);

    $sql = "update respaldo_usuario set fecha_fin=".$db->conn->sysTimeStamp." where resp_codi=$resp_codi";
    $db->query($sql);

    //Se consulta fecha de inicio y fin de la solicitud
    $sql_sol = "select * from respaldo_solicitud where resp_codi = $resp_codi";
    $rs_sol = $db->query($sql_sol);

    if($rs_sol && !$rs_sol->EOF){
        $resp_soli_codi = $rs_sol->fields["RESP_SOLI_CODI"];
        $estado_solicitud = 6;
        $estado_respaldo = 12;
        
        //Se actualiza solicitud de respaldo
        $sql = "update respaldo_solicitud set fecha_fin_ejec=".$db->conn->sysTimeStamp.",
            estado_solicitud = $estado_solicitud,
            estado_respaldo  = $estado_respaldo
            where resp_codi=$resp_codi";
        $db->query($sql);

        //Se inserta el histórico
        $usua_codi = 0; //$_SESSION["usua_codi"];
        $fecha_accion = $db->conn->sysTimeStamp;
        $accion = 77;
        $sql = "INSERT INTO respaldo_hist_eventos(resp_soli_codi, usua_codi, fecha, accion, estado_solicitud, estado_respaldo)
        VALUES ($resp_soli_codi, $usua_codi, $fecha_accion, $accion, $estado_solicitud, $estado_respaldo)";
        $db->query($sql);

        //Se envía correo
        //Se consulta datos de solicitud
        $txt_accion = 7;
        $codigo = $rs_sol->fields["RESP_SOLI_CODI"];
        $datos = ObtenerSolicitudPorCodigo($codigo,$db);
        if($datos["estado_solicitud"] == 6){
            $destinatario = $datos["usua_codi_solicita"];
            $remitente = $datos["usua_codi_autoriza"];
            //Se envía correo
            EnviarCorreo($txt_accion, $destinatario, $remitente, $datos, $ruta_raiz, $db);
        }

    }
    $rs->MoveNext();
} catch (Exception $e) {
    die ("OK");
}

die ("OK");


function copy_r( $path, $dest ) {
    if (is_dir($path)) {
        @mkdir( $dest );
        $objects = scandir($path);
        if( sizeof($objects) > 0 ) {
            foreach( $objects as $file ) {
                if( $file == "." || $file == ".." ) continue;
                if( is_dir( "$path/$file" ) )
                    copy_r( "$path/$file", "$dest/$file" );
                else
                    copy( "$path/$file", "$dest/$file" );
            }
        }
        return true;
    } elseif( is_file($path) ) {
        return copy($path, $dest);
    } else {
        return false;
    }
}
?>