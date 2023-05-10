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

// BASIC ADO test

	include_once('../adodb.inc.php');

	$db = &ADONewConnection("ado_access");
	$db->debug=1;
	$access = 'd:\inetpub\wwwroot\php\NWIND.MDB';
	$myDSN =  'PROVIDER=Microsoft.Jet.OLEDB.4.0;'
		. 'DATA SOURCE=' . $access . ';';
		
	echo "<p>PHP ",PHP_VERSION,"</p>";
	
	$db->Connect($myDSN) || die('fail');
	
	print_r($db->ServerInfo());
	
	try {
	$rs = $db->Execute("select $db->sysTimeStamp,* from adoxyz where id>02xx");
	print_r($rs->fields);
	} catch(exception $e) {
	print_r($e);
	echo "<p> Date m/d/Y =",$db->UserDate($rs->fields[4],'m/d/Y');
	}
?>