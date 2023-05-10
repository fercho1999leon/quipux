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

// Archivo en el que se configura a que replica apunta cada página
// En  el caso de ir a la BDD principal no poner un nombre de replica

$config_db_replica_rep_reportes_generar = "reportes";
$config_db_replica_rep_usuarios_conectados = "reportes";

// busqueda
$config_db_replica_busqueda = "busqueda";
$config_db_replica_busqueda_paginador = "busqueda";
$config_db_replica_busqueda_tramites = "busqueda";
$config_db_replica_busqueda_cargar_combo_usuario = "busqueda";

// radicacion - Buscar de/para
$config_db_replica_buscar_usuario_nuevo = "busqueda";
$config_db_replica_buscar_usuario_de_para = "busqueda";
$config_db_replica_buscar_usuario_resultado = "busqueda";
$config_db_replica_lista_concopia = "busqueda";

// Carpetas Virtuales
$config_db_replica_trd_consultar_lista_trd = "busqueda";
$config_db_replica_trd_copiar_trd = "busqueda";
$config_db_replica_trd_lista_expediente = "busqueda";
$config_db_replica_trd_nuevo_trd = "busqueda";
$config_db_replica_trd_seleccionar_trd = "busqueda";

// Bandejas
$config_db_replica_menu_correspondencia = "busqueda";
$config_db_replica_cuerpo_paginador = "busqueda";
$config_db_replica_bandeja_elaboracion = "busqueda";
$config_db_replica_bandeja_recibidos = "busqueda";
$config_db_replica_bandeja_enviados = "busqueda";
$config_db_replica_bandeja_eliminados = "busqueda";
$config_db_replica_bandeja_no_enviados = "busqueda";
$config_db_replica_bandeja_archivados = "busqueda";
$config_db_replica_bandeja_reasignados = "busqueda";
$config_db_replica_bandeja_informados = "busqueda";
$config_db_replica_bandeja_compartida_recibidos = "busqueda";
$config_db_replica_bandeja_tareas_recibidas = "busqueda";
$config_db_replica_bandeja_tareas_enviadas = "busqueda";
$config_db_replica_bandeja_enviados_ciudadanos = "busqueda";
$config_db_replica_bandeja_recibidos_ciudadanos = "busqueda";
$config_db_replica_bandeja_pendientes_ciudadanos = "busqueda";
$config_db_replica_bandeja_por_imprimir = "busqueda";
$config_db_replica_bandeja_elaboracion_ciudadanos_firma = "busqueda";
$config_db_replica_bandeja_enviados_ciudadanos_firma = "busqueda";
$config_db_replica_bandeja_recibidos_ciudadanos_firma = "busqueda";
$config_db_replica_bandeja_no_enviados_ciudadanos_firma = "busqueda";

// Información del documento
$config_db_replica_info_lista_asociados = "busqueda";
$config_db_replica_info_lista_general = "busqueda";
$config_db_replica_info_ver_historico = "busqueda";

// Listas
$config_db_replica_lst_listas = "busqueda";
$config_db_replica_lst_listas_cargar_usuarios = "busqueda";
$config_db_replica_lst_listas_datos_lista = "busqueda";

// Administracion
$config_db_replica_adm_buscar_usuario_nuevo_subr = "busqueda";
$config_db_replica_adm_buscar_usuario_nuevo_subr_des = "busqueda";
$config_db_replica_adm_busqueda_paginador_areas = "busqueda";
$config_db_replica_adm_busqueda_paginador_usuarios = "busqueda";
$config_db_replica_adm_criterios_permisos = "busqueda";
$config_db_replica_adm_validar_usuario_multiple = "busqueda";

// Notificaciones
$config_db_replica_not_cargar_combo_area = "busqueda";
$config_db_replica_not_listas_cargar_usuarios = "busqueda";
$config_db_replica_not_listas_datos_lista = "busqueda";
$config_db_replica_not_paginador_notificaciones = "busqueda";

// uploadFiles
$config_db_replica_uf_cargar_doc_digitalizado = "busqueda";
$config_db_replica_uf_upload_file_radicado = "busqueda";

// Tx
$config_db_replica_tx_formenvio_ajax = "busqueda";
$config_db_replica_tx_cargar_combos = "busqueda";

//Consulta de contenido de Index y Ayuda
$config_db_replica_contenido_index = "busqueda";

?>