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

/*****************************************************************************************
** Bandejas del grupo Administración                                                    **
*****************************************************************************************/

$bandeja = crear_item_bandeja(0, "Administraci&oacute;n", "Opciones de administraci&oacute;n del sistema", "Administracion/formAdministracion.php");
if ($_SESSION["depe_codi"]!=0) { //Si no tiene definida el area no puede realizar acciones
    if ($_SESSION["usua_perm_trd"]==1) {
        $bandeja .= crear_item_bandeja(0, $descTRDpl, "Administraci&oacute;n de $descTRDpl", "tipo_documental/menu_trd.php");
    }
    if($_SESSION["usua_admin_archivo"]==1 or $_SESSION["usua_perm_archivo"]==1) {
        $bandeja .= crear_item_bandeja(0, "Archivo F&iacute;sico", "Archivo F&iacute;sico", "archivo/menu_archivo.php");
    }
}
echo crear_grupo_bandeja("administracion", "Administraci&oacute;n", $bandeja);

/*****************************************************************************************
** Bandejas del grupo Otros                                                             **
*****************************************************************************************/

//$bandeja = crear_item_bandeja(0, "B&uacute;squeda Avanzada", "B&uacute;squeda de documentos", "busqueda/busqueda.php");
$bandeja = crear_item_bandeja(0, "B&uacute;squeda Avanzada", "B&uacute;squeda de documentos", "busquedaN/busqueda.php");
$bandeja .= crear_item_bandeja(0, "Seguimiento de documentos", "B&uacute;squeda de documentos para hacer seguimiento", "busqueda/busqueda_tramites.php");
if($_SESSION["perm_buscar_doc_adscritas"] == 1)
    $bandeja .= crear_item_bandeja(0, "B&uacute;squeda de documentos", "B&uacute;squeda de documentos de Instituciones Adscritas", "busqueda/busqueda_adscritas.php");

if ($_SESSION["depe_codi"]!=0) { //Si no tiene definida el area no puede realizar acciones
    // Carpetas virtuales
    $bandeja .= crear_item_bandeja(0, $descTRDpl, "Consultar documentos por $descTRDpl", "tipo_documental/consultar_trd.php");

    if($_SESSION["usua_perm_impresion"] == 1) { // Bandeja por imprimir
        $bandeja .= crear_item_bandeja(99, "Por Imprimir", "Documentos para Imprimir");
    }

//    if($_SESSION["usua_perm_estadistica"]==1) {
        $bandeja .= crear_item_bandeja(0, "Reportes", "Reportes", "$nombre_servidor_reportes/reportes_new/reportes.php?id_sess=".session_id());       
//        if($_SESSION["usua_admin_sistema"]==1 || $_SESSION["usua_codi"]==0){
//            $bandeja .= crear_item_bandeja(0, "Reporte Uso del Sistema", "Usuarios", "$nombre_servidor_reportes/Administracion/usuarios/reporteUsuarios.php?id_sess=".session_id());
//        $bandeja .= crear_item_bandeja(0, "Reporte Usuarios por Área", "Usuarios por Área", "$nombre_servidor_reportes/Administracion/usuarios/reporteUsuariosAreas.php?id_sess=".session_id());
//        }
        
//    }
}
echo crear_grupo_bandeja("otros", "Otros", $bandeja);
?>