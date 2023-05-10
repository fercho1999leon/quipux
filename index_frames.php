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

/*
// Borramos el cache del navegador
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Fecha en el pasado
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // siempre modificado
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0
/* */

session_start();
$ruta_raiz = "."; 
include_once "$ruta_raiz/rec_session.php";
?>
<html>
    <head>
        <title>.:: Quipux - Sistema de Gesti&oacute;n Documental ::.</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="imagenes/favicon.ico">
    </head>
    <frameset rows="97,864*" frameborder="yes" border="1" framespacing="0" cols="*">
        <frame name="topFrame" scrolling="no" noresize src="f_top.php" ></frame>
        <frameset cols="175,947*" border="1" framespacing="0" rows="*">
            <frame name="leftFrame" id="leftFrame" src="correspondencia.php" marginwidth="0" marginheight="0" scrolling="auto" border="1"></frame>
            <frame name="mainFrame" id="mainFrame" src="cuerpo.php" scrolling="auto"></frame>
        </frameset>
    </frameset>
    <noframes></noframes>
    <body><div id="div_session"></div></body>
</html>
