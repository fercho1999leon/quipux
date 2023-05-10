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

include_once('../adodb-time.inc.php');
//adodb_date_test();
?>
<?php 
//require("adodb-time.inc.php"); 

$datestring = "1963-12-04"; // string normally from mySQL 
$stringArray = explode("-", $datestring);
$date = adodb_mktime(0,0,0,$stringArray[1],$stringArray[2],$stringArray[0]); 

$convertedDate = date("d-M-Y", $date); // converted string to UK style date

echo( "Birthday: $convertedDate" ); //why is string returned as one day (3 not 4) less for this example??

?>