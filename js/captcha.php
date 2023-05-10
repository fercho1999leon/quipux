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

************************************************************************************
**                                                                                **
** Código que genera imágenes aleatoreas para que no puedan ser leidas por robots **
** Este código es utilizado en la funcionalidad "Olvide mi contraseña"            **
**                                                                                ** 
** Código obtenido de http://blog.unijimpe.net/crear-captcha-con-php/             ** 
**                                                                                ** 
***********************************************************************************/

$ruta_raiz = "..";
session_start();
$texto = generar_clave(4);
$_SESSION['captcha'] = $texto;
$texto = substr($texto,0,1)." ".substr($texto,1,1)." ".substr($texto,2,1)." ".substr($texto,3,1);
$img_captcha = imagecreatefromjpeg("$ruta_raiz/imagenes/bgcaptcha.jpg");
$color_texto = imagecolorallocate($img_captcha, 0, 0, 0);
imagestring($img_captcha, 5, 16, 7, $texto, $color_texto);
header("Content-type: image/jpg");
imagegif($img_captcha);


function generar_clave($num=8) {
    $chars = "abcdefghijkmnopqrstuvwxyz2345678";
    $pass = "";
    for ($i=0 ; $i<$num ; ++$i) {
        $pass .= substr($chars, rand(0,31), 1);
    }
    return $pass;
}
?>
