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
 $recOrfeo = "Seguridad";
 include "$ruta_raiz/session_orfeo.php";
 
 //Se incluyo por una mejora de seguridad de sesiones
  require_once "$ruta_raiz/securesession.class.php";
  $ss = new SecureSession();
  $ss->check_browser = true;
  $ss->check_ip_blocks = 2;
  $ss->secure_word = 'QUIPUX_COMUNIDAD_V4';
  $ss->regenerate_id = false; //true
  if (!$ss->Check() || !isset($_SESSION['initiated']) || !$_SESSION['initiated'])
  {
//      echo "Entro aqui";
    include "$ruta_raiz/paginaError.php";
     die();
  }


?>
