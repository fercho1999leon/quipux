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
** Bandejas del grupo de Registro de Documentos Externos                                **
*****************************************************************************************/

$bandeja = "";
if($_SESSION["usua_prad_tp2"] == 1 or $_SESSION["usua_perm_digitalizar"] == 1 or $_SESSION["perm_tramitar_docs_ciudadano"] == 1) {
    if($_SESSION["usua_prad_tp2"] == 1) {
        $bandeja .= crear_item_bandeja(0, "Registrar", "Registro de documentos externos", "radicacion/NEW.php?&ent=2&accion=Nuevo");
//        $bandeja .= crear_item_bandeja(91, "Doc. Otras Instituciones", "Registro de documentos enviados f&iacute;sicamente desde otras instituciones");
        $bandeja .= crear_item_bandeja(0, "Comprobante", "Imprimir Comprobantes", "uploadFiles/cargar_doc_digitalizado.php?imprimir=si");
    }
    if($_SESSION["usua_perm_digitalizar"] == 1) {
        $bandeja .= crear_item_bandeja(0, "Cargar Doc. Digitalizado", "Asociar imagen digitalizada del Documento", "uploadFiles/cargar_doc_digitalizado.php");
        $bandeja .= crear_item_bandeja(0, "Cargar Anexos al Doc.", "Cargar nuevos anexos al documento", "uploadFiles/cargar_doc_digitalizado.php?tipo_archivo=anex");
    }
    if($_SESSION["usua_prad_tp2"] == 1) {
        $bandeja .= crear_item_bandeja(0, "Devoluci&oacute;n", "Registrar devoluciones de documentos", "devolver_documentos.php");
    }
    if($_SESSION["perm_tramitar_docs_ciudadano"] == 1) {
        $bandeja .= crear_item_bandeja(90, "Docs. Ciudadanos", "Documentos recibidos, firmados electr&oacute;nicamente por ciudadanos");
    }
    echo crear_grupo_bandeja("registro_externos", "Bandeja de Entrada", $bandeja);
}
?>