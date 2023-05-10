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
	V4.50 6 July 2004 
	
	Run multiple copies of this php script at the same time
	to test unique generation of id's in multiuser mode
*/
include_once('../adodb.inc.php');
$testaccess = true;
include_once('testdatabases.inc.php');

function testdb(&$db,$createtab="create table ADOXYZ (id int, firstname char(24), lastname char(24), created date)")
{
	$table = 'adodbseq';
	
	$db->Execute("drop table $table");
	//$db->debug=true;
	
	$ctr = 5000;
	$lastnum = 0;
	
	while (--$ctr >= 0) {
		$num = $db->GenID($table);
		if ($num === false) {	
			print "GenID returned false";
			break;
		}
		if ($lastnum + 1 == $num) print " $num ";
		else {
			print " <font color=red>$num</font> ";
			flush();
		}
		$lastnum = $num;
	}
}
?>