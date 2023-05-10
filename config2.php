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
**/

// Archivo de configuracion del sistema QUIPUX

// Se recomienda OCULTAR las contraseñas y datos importantes como se explica en el archivo "config_recomendacion_seguridad.php".

// Archivo con algunas configuraciones de términos usados en el sistema
$FILE_LOCAL = "localEcuador.php";

// Activa la funcionalidad para bloquear el sistema;
// Se lo debe activar cuando se programe un bloqueo del sistema para disminuir las consultas a la BDD
$activar_bloqueo_sistema = false;

//Mejora algunos queries y bloquea algunas funcionalidades para reducir la carga a los servidores
$version_light=false;
$config_numero_meses = 60;
$numeroCaracteresTexto = 0;
$config_bloquear_acceso_ciudadano = false;


// Configuracion de la conexion con la BDD
$usuario = "postgres";
$contrasena= "postgres"; 
$servidor = "127.0.0.1:5432";
$driver = "postgres";
$db = "quipux";

$usuario_bodega = "postgres";
$contrasena_bodega = "postgres";
$servidor_bodega = "127.0.0.1:5432";
$db_bodega = "quipux_bodega";

// Indica si se manejan replicas o conexiones con otras BDD
$replicacion = false;

// Se definen las mismas variables que en la configuracion por defecto, seguidas por un guion bajo y un nombre que la distinga
// Para utilizar esta funcionalidad se debe enviar el nombre utilizado en las variables como parametro al crear la conexion
// Si se desea se puede ocultar los datos de la conexion en variables del servidor, como en el caso anterior
$usuario_busqueda = "postgres";
$contrasena_busqueda = "postgres";
$servidor_busqueda = "127.0.0.1:5432";
$db_busqueda = "quipux_replica";


//Codigo de aplicacion (en caso de que se manejen varios servidores para distribución de carga)
$appID = 'appID';

//Path en donde se guardan los archivos que anexa el ciudadano para petición de uso de QUIPUX con firma digital
$path_ciudadanos = "/var/www/quipux/bodega/ciudadanos";

//Logs y Mensajes de la aplicacion
//Muestra en pantalla los queries que se ejecutan en la bdd; 0 no muestra ningun mensaje, 1 muestra los errores, 2 muestra todos
$mostrar_logs = 0;
// Graba en la tabla logs de la bdd los queries (inserts y updates) mas importantes ejecutados; 0 no graba nada, 1 graba los errores, 2 graba todos
$grabar_logs = 2;
// Graba en una tabla de logs la página invocada y el IP que la invocó (para identificar posibles ataques desde páginas externas o desde páginas de orfeo...)
$grabar_log_paginas_visitadas = false;
$grabar_log_full_backup = false;


//Email del Super Administrador del Sistema QUIPUX
$amd_email = "administrador@dominio.com";
// email de la cuenta de soporte
$cuenta_mail_soporte = "soporte@dominio.com";
// email de la cuenta desde la que se enviarán los recordatorios a los usuarios
$cuenta_mail_envio = "recordatorio@dominio.com";

// Configuración para la conexión con otros servidores adicionales
$nombre_servidor="http://nombre_servidor_quipux";
$nombre_servidor_reportes = $nombre_servidor; // en caso que los reportes se lo quiera enviar a un servidor diferente
$nombre_servidor_respaldos = $nombre_servidor; // en caso que los respaldos se requiera sacar en un servidor diferente

$servidor_firma = "http://nombre_servidor_firma_electronica";

$servidor_viajes = "http://nombre_servidor_viajes";

$servidor_pdf = "http://nombre_servidor_html_a_pdf";

//Numero Meses en Reportes
$numeroMeses = 60;
//path de descarga del archivo
$path_acuerdo = "http://www.informatica.gob.ec/index.php/component/docman/doc_download/57-acuerdo-de-uso/Acuerdo de Uso.odt";
//Acceso para Institución de Ciudadanos
$acceso_ciudadano_inst = 1;
//Tipo de Documentos de Ciudadanos
$tipo_doc_ciudadano = "7";
//Número de días de vigencia para descarga de archivos de respaldos
$dias_descarga = 15;
//Correo para recibir notificaciones de respaldos para soporte
$cuenta_mail_respaldo = "respaldo@informatica.gob.ec";
$versionEstable = 25;//version de firefox menores a 17 es soportada
?>
