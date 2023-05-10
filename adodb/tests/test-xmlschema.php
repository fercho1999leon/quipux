<?PHP
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

// V4.50 6 July 2004

error_reporting(E_ALL);
include_once( "../adodb.inc.php" );
include_once( "../adodb-xmlschema.inc.php" );

// To build the schema, start by creating a normal ADOdb connection:
$db = ADONewConnection( 'mysql' );
$db->Connect( 'localhost', 'root', '', 'schematest' );

// To create a schema object and build the query array.
$schema = new adoSchema( $db );

// To upgrade an existing schema object, use the following 
// To upgrade an existing database to the provided schema,
// uncomment the following line:
#$schema->upgradeSchema();

print "<b>SQL to build xmlschema.xml</b>:\n<pre>";
// Build the SQL array
$sql = $schema->ParseSchema( "xmlschema.xml" );

print_r( $sql );
print "</pre>\n";

// Execute the SQL on the database
//$result = $schema->ExecuteSchema( $sql );

// Finally, clean up after the XML parser
// (PHP won't do this for you!)
//$schema->Destroy();


$db2 = ADONewConnection('mssql');
$db2->Connect('localhost','sa','natsoft','northwind') || die("Fail 2");

$db2->Execute("drop table simple_table");


print "<b>SQL to build xmlschema-mssql.xml</b>:\n<pre>";

$schema = new adoSchema( $db2 );
$sql = $schema->ParseSchema( "xmlschema-mssql.xml" );

print_r( $sql );
print "</pre>\n";

$db2->debug=1;

$db2->Execute($sql[0]);
?>