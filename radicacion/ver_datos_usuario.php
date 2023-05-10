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
   $ruta_raiz = isset($ruta_raiz) ? $ruta_raiz : "..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

session_start();
if (!$ruta_raiz) $ruta_raiz = "..";
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/obtenerdatos.php";
include_once "$ruta_raiz/funciones_interfaz.php";
    if ($_GET['destinatario']){
    $codiDestinatario = $_GET['destinatario'];
    $tipo_busqueda = $_GET['tipo_busqueda'];
    $usuadest = ObtenerDatosUsuario(str_replace("-","",$codiDestinatario),$db);
    
    
        $usuadest = ObtenerDatosUsuario(str_replace("-","",$codiDestinatario),$db);        
        $cargo=$usuadest['cargo'];        
        $titulo = $usuadest['titulo'];
        $institucion = $usuadest["institucion"];
        $tipoUser = $usuadest['tipo_usuario'];        
  if ($tipo_busqueda==6 || $tipo_busqueda==1){      
    if ( trim($cargo)== '' || trim($titulo) == '' || trim($institucion)=='')
        echo "<font color='red'>El destinatario no tiene completos los datos, Favor cambie el Tipo de Impresión</font>";
  }elseif($tipo_busqueda==2){
       if (trim($cargo)== '' || trim($institucion)=='')
           echo "<font color='red'>El destinatario no tiene completos los datos, Favor cambie el Tipo de Impresión</font>";
  }
  elseif($tipo_busqueda==5){
       if (trim($titulo)=='' || trim($institucion)==''){           
           echo "<font color='red'>El destinatario no tiene completos los datos, Favor cambie el Tipo de Impresión</font>";
       }
  }
  elseif($tipo_busqueda==4){
       if (trim($titulo)=='' || trim($cargo)=='')
           echo "<font color='red'>El destinatario no tiene completos los datos, Favor cambie el Tipo de Impresión</font>";
  }
    }
     
?>