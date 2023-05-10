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

include('adodb-errorpear.inc.php');
include('adodb.inc.php');
include('tohtml.inc.php');
error_reporting(7);
$c = NewADOConnection('oci8');
if($c->PConnect('atlas','fldoc','Fldoc','bdprueba'))
{
 echo "entro";
 $rs=$c->Execute('select * from usuario');
}else
{
$e = ADODB_Pear_Error();
	echo '<p>',$e->message,'</p>';
} #invalid table productsz');
if ($rs) rs2html($rs);
else {
	$e = ADODB_Pear_Error();
	echo '<p>',$e->message,'</p>';
}
?>