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
ini_set('mssql.datetimeconvert',0); 

function tmssql()
{
	print "<h3>mssql</h3>";
	$db = mssql_connect('JAGUAR\vsdotnet','adodb','natsoft') or die('No Connection');
	mssql_select_db('northwind',$db);
	
	$rs = mssql_query('select getdate() as date',$db);
	$o = mssql_fetch_row($rs);
	print_r($o);
	mssql_free_result($rs);
	
	print "<p>Delete</p>"; flush();
	$rs2 = mssql_query('delete from adoxyz',$db);
	$p = mssql_num_rows($rs2);
	mssql_free_result($rs2);

}

function tpear()
{
include_once('DB.php');

	print "<h3>PEAR</h3>";
	$username = 'adodb';
	$password = 'natsoft';
	$hostname = 'JAGUAR\vsdotnet';
	$databasename = 'northwind';
	
	$dsn = "mssql://$username:$password@$hostname/$databasename";
	$conn = &DB::connect($dsn);
	print "date=".$conn->GetOne('select getdate()')."<br>";
	@$conn->query('create table tester (id integer)');
	print "<p>Delete</p>"; flush();
	$rs = $conn->query('delete from tester');
	print "date=".$conn->GetOne('select getdate()')."<br>";
}

function tadodb()
{
include_once('../adodb.inc.php');

	print "<h3>ADOdb</h3>";
	$conn = NewADOConnection('mssql');
	$conn->Connect('JAGUAR\vsdotnet','adodb','natsoft','northwind');
//	$conn->debug=1;
	print "date=".$conn->GetOne('select getdate()')."<br>";
	$conn->Execute('create table tester (id integer)');
	print "<p>Delete</p>"; flush();
	$rs = $conn->Execute('delete from tester');
	print "date=".$conn->GetOne('select getdate()')."<br>";
}
?>
<a href=tmssql.php?do=tmssql>mssql</a>
<a href=tmssql.php?do=tpear>pear</a>
<a href=tmssql.php?do=tadodb>adodb</a>
<?php
if (!empty($_GET['do'])) {
	$do = $_GET['do'];
	$do();
}
?>