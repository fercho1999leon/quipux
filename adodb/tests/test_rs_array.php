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

include_once('../adodb.inc.php');
$rs = new ADORecordSet_array();

$array = array(
array ('Name', 'Age'),
array ('John', '12'),
array ('Jill', '8'),
array ('Bill', '49')
);

$typearr = array('C','I');


$rs->InitArray($array,$typearr);

while (!$rs->EOF) {
	print_r($rs->fields);echo "<br>";
	$rs->MoveNext();
}

echo "<hr> 1 Seek<br>";
$rs->Move(1);
while (!$rs->EOF) {
	print_r($rs->fields);echo "<br>";
	$rs->MoveNext();
}

echo "<hr> 2 Seek<br>";
$rs->Move(2);
while (!$rs->EOF) {
	print_r($rs->fields);echo "<br>";
	$rs->MoveNext();
}

echo "<hr> 3 Seek<br>";
$rs->Move(3);
while (!$rs->EOF) {
	print_r($rs->fields);echo "<br>";
	$rs->MoveNext();
}



die();
?>