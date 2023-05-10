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



Se recomienda OCULTAR las contraseñas de conección con la base de datos y todos los
datos que de ser sustraidos podrían ser utilizados para atacar los sistemas.

En nuestro caso sugerimos ocultar cierta información que se encuentra en el archivo
"config.php" en un archivo de configuración de apache.

Por ejemplo podemos crear un archivo que contenga la siguiente información:
    /etc/apache2/conf.d/quipux.conf

******************************************************************************
**                                                                          **
**  # Variables de configuración del sistema QUIPUX                         **
**                                                                          **
**  # Variables de conexión con la BDD                                      **
**  SetEnv DB_USER "postgres"                                               **
**  SetEnv DB_PASS "postgres"                                               **
**  SetEnv DB_SERVER "192.168.0.2:5432"                                     **
**  SetEnv DB_DRIVER "postgres"                                             **
**  SetEnv DB_NAME "quipux"                                                 **
**                                                                          **
**  # Recuerde reiniciar el apache luego de realizar cualquier cambio       **
**  # en este archivo.                                                      **
**                                                                          **
******************************************************************************

Esta información es protegida por el servidor apache y se accede a ella por medio
de las variables del servidor $_SERVER[]

En el archivo "config.php" se hace el llamado a estas variables de la siguiente manera:

******************************************************************************
**                                                                          **
**  $usuario     = $_SERVER['DB_USER'];                                     **
**  $contrasena  = $_SERVER['DB_PASS'];                                     **
**  $servidor    = $_SERVER['DB_SERVER'];                                   **
**  $driver      = $_SERVER['DB_DRIVER'];                                   **
**  $db          = $_SERVER['DB_NAME'];                                     **
**                                                                          **
******************************************************************************

/* */

?>