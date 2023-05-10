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
error_reporting(E_ALL);
include('../adodb.inc.php');



echo "New Connection\n";
$DB = NewADOConnection('pdo');
echo "Connect\n";
$pdo_connection_string = 'odbc:nwind';
$DB->Connect($pdo_connection_string,'','') || die("CONNECT FAILED");
echo "Execute\n";



//$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
$rs = $DB->Execute("select * from products where productid<3");
echo "e=".$DB->ErrorNo() . " ".($DB->ErrorMsg())."\n";


//print_r(get_class_methods($DB->_stmt));

if (!$rs) die("NO RS");
echo "FETCH\n";
$cnt = 0;
while (!$rs->EOF) {
	print_r($rs->fields);
	$rs->MoveNext();
	if ($cnt++ > 1000) break;
}

echo "<br>--------------------------------------------------------<br>\n\n\n";

$stmt = $DB->PrepareStmt("select * from products");
$rs = $stmt->Execute();
echo "e=".$stmt->ErrorNo() . " ".($stmt->ErrorMsg())."\n";
while ($arr = $rs->FetchRow()) {
	print_r($arr);
}
die("DONE\n");

?>