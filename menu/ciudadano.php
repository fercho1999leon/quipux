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
** Bandejas del grupo Bandejas de ciudadanos                                            **
*****************************************************************************************/

$bandeja  = crear_item_bandeja(80, "Enviados", "Documentos enviados por el ciudadano a cualquier Instituci&oacute;n P&uacute;blica");
$bandeja .= crear_item_bandeja(81, "Recibidos", "Documentos recibidos por el ciudadano desde cualquier Instituci&oacute;n P&uacute;blica");
echo crear_grupo_bandeja("bandejas_ciudadanos", "Bandejas", $bandeja);

/*****************************************************************************************
** Bandejas del grupo Administración                                                    **
*****************************************************************************************/

$bandeja  = crear_item_bandeja(0, "Cambiar Contrase&ntilde;a", "Permite al usuario cambiar la clave de ingreso al sistema", "Administracion/usuarios/cambiar_password.php");
$bandeja .= crear_item_bandeja(0, "Editar Datos Personales", "Permite al usuario modificar sus datos registrados en el sistema", "Administracion/ciudadanos/adm_ciudadano.php");
$bandeja .= crear_item_bandeja(0, "Generar/Firmar Documentos", "Solicitud para que el usuario pueda firmar electr&oacute;nicamente documentos", "Administracion/ciudadanos_solicitud/adm_solicitud_ciu.php");
echo crear_grupo_bandeja("administracion", "Administraci&oacute;n", $bandeja);

?>