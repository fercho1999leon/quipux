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
class ConnectionHandler {

//Almacena un error, resultado de una transacci�
/**
  * ESTA CLASE INICIA LA CONEXION A LA BD SELECCIONADA
	* @$conn  objeto  Variable que almacena la conexion;
	* @$driver char  Variable que almacena la bd Utilizada.
	* @$rutaRaiz char Indica la ruta para encontrar la ubicacion de la raiz de la aplicacion.
	* @$dirOrfeo char Directorio del servidor web en el cual se encuentra instalado Orfeo.
	*
	*/

var $Error;
var $id_query;

var $driver;
var $rutaRaiz;
var $conn;
var $entidad;
var $entidad_largo;
var $entidad_tel;
var $entidad_dir;
var $querySql;
/* Metodo constructor */
function ConnectionHandler($ruta_raiz, $servidor_bdd=""){
    // Si existe $servidor_bdd permite conectarse a otras BDD que no sea la BDD por defecto
    // se utiliza para conectarse y consultar de replicas en otros equipos o guardar logs en otras maquinas
    $servidor_bdd = trim($servidor_bdd);
    if ($servidor_bdd == "BLOQUEAR") {
        die ("<center><h3><br><br>Lo sentimos, actualmente esta funcionalidad no se encuentra disponible.
              <br><br>Por favor vuelva a intentarlo m&aacute;s tarde.</h3></center>");
    }
    if ($servidor_bdd != "") $servidor_bdd = "_" . $servidor_bdd;

    if (!defined('ADODB_ASSOC_CASE')) define('ADODB_ASSOC_CASE',1);
    include ("$ruta_raiz/adodb/adodb.inc.php");
    include_once ("$ruta_raiz/adodb/adodb-paginacion.inc.php");
    include_once ("$ruta_raiz/adodb/tohtml.inc.php");
    include ("$ruta_raiz/config.php");

    // $replicacion es una bandera que indica si existen replicas o no
    if (!$replicacion && $servidor_bdd != "_bodega") $servidor_bdd = "";
    $ADODB_COUNTRECS = false;
    $this->driver = $driver;
    $this->conn  = NewADOConnection("$driver");
    $this->rutaRaiz = $ruta_raiz;
    $this->conn->Connect(${"servidor".$servidor_bdd},${"usuario".$servidor_bdd},${"contrasena".$servidor_bdd},${"db".$servidor_bdd});
    // En el caso que se conecte a una réplica, se valida si la conección fue exitosa, caso contrario se vuelve a conectar a la BDD principal
    if (!$this->conn->_connectionID) {
        if ($servidor_bdd=="_reportes") {
            $servidor_bdd = "_busqueda";
            $this->conn->Connect(${"servidor".$servidor_bdd},${"usuario".$servidor_bdd},${"contrasena".$servidor_bdd},${"db".$servidor_bdd});
        } else if ($servidor_bdd=="_busqueda"){
            $servidor_bdd = "_reportes";
            $this->conn->Connect(${"servidor".$servidor_bdd},${"usuario".$servidor_bdd},${"contrasena".$servidor_bdd},${"db".$servidor_bdd});
        }
        if (!$this->conn->_connectionID)
            $this->conn->Connect($servidor,$usuario,$contrasena,$db);
    }
    // Modificamos las variables de tiempo para que no obtenga la fecha y hora del servidor de BDD sino del servidor web
    // Esto se utiliza para poder replicar las BDD con "pg pool" y tener replicación de BDD maestro maestro
    $rs = $this->conn->Execute("select now() as fecha1, now()::date as fecha2");
    if ($rs && !$rs->EOF) { // toma la hora de la BDD
        $this->conn->sysTimeStamp = "('".$rs->fields["FECHA1"]."'::timestamp)";
        $this->conn->sysDate = "'".$rs->fields["FECHA2"]."'::date";
    } else { //Toma la hora del servidor web si falla la BDD
        $this->conn->sysDate = "'".date("Y-m-d")."'::date";
        list($useg, $seg) = explode(" ", microtime());
        $this->conn->sysTimeStamp = "('".date("Y-m-d H:i:s").substr($useg."0",1,7)."'::timestamp)";
    }

    // Graba todos los queries ejecutados en la tabla log_full_backup
    if (isset ($grabar_log_full_backup) && $grabar_log_full_backup==true && $servidor_bdd=="") $this->conn->_log_full_backup=true;
    // si tiene encendida la variable $grabar_log_paginas_visitadas graba las paginas a las que acceden los usuarios,
    // sirve para verificar posibles ataques al sistema
    if (isset ($grabar_log_paginas_visitadas) && $grabar_log_paginas_visitadas==true && $servidor_bdd=="") {
        unset($recordSet);
        $recordSet["FECHA"] = $this->conn->sysTimeStamp;
        $recordSet["USUARIO"] = $this->conn->qstr($_SESSION["usua_codi"]);
        $recordSet["IP"] = $this->conn->qstr($_SERVER['HTTP_X_FORWARDED_FOR']." - ".$_SERVER['HTTP_CLIENT_IP']." - ".$_SERVER['REMOTE_ADDR']);
        $recordSet["PAGINA"] = $this->conn->qstr($_SERVER["PHP_SELF"]);
        $this->conn->Replace("LOG_PAGINAS_VISITADAS", $recordSet, "", false,false,false,false);
    }

//echo $_SERVER["PHP_SELF"]." - Connect(\${servidor$servidor_bdd},\${usuario$servidor_bdd},\${contrasena$servidor_bdd},\${db$servidor_bdd});<br>";
}
function imagen()
{
	switch($this->entidad)
	{
		case "CRA":
			$imagen = "png/logoCRA.gif";
		break;
		case "DNP":
			$imagen = "png/logoDNP.gif";
		break;
		case "SSPD":
			$imagen = "png/escudoColombia.jpg";
		break;
		case "SGD":
			$imagen = "png/logoSGD.gif";
		break;
		default:
			$imagen = "";
		break;
	}
	return($imagen);
}
//  Retorna False en caso de ocurrir error;
function query($sql)
{
  //$this->conn->debug=true;
$cursor = $this->conn->Execute($sql);
  return $cursor;
}


/* Devuelve un array correspondiente a la fila de una consulta */
/*	function fetch_row() {

	//return ifx_fetch_row($this->id_query);

	ora_fetch_into($this->idconnection,$row, ORA_FETCHINTO_NULLS|ORA_FETCHINTO_ASSOC);
	$this->id_query=$row;
	return ($row);

	}
*/

/* Devuelve el nmero de campos de una consulta */
/*
	function numfields() {

	return ifx_num_fields($this->id_query);

	}

 */

/* Devuelve el nmero de registros de una consulta */
/*
	function numrows(){

	return ifx_affected_rows($this->id_query);

	}
*/

/* Funcion miembro que carga dos arrays con los nombres de los campos y el tipo de dato respectivamente. */
/*
	function fieldsinfo() {

	$types = ifx_fieldtypes($this->id_query);

	for ($i = 0; $i < count($types); $i++) {

	$this->fieldsnames[$i] = key($types);

	$this->$fieldstypes[$i] = $types[$this->fieldsnames[$i]];

	next($types);

	}

	}

*/
/* Funcion miembro que realiza una consulta a la base de datos y devuelve un record set */

function getResult($sql) {
	if ($sql == "") {
		$this->Error = "No ha especificado una consulta SQL";
		print($this->Error);
		return 0;
	}
	return ($this->query($sql));
}


/* Funcion miembro que ejecuta una instruccion sql a la base de datos. */





/*
   Funcion miembro que recibe como parametros: nombre de la tabla, un array con los nombres de los campos,
   y un array con los valores respectivamente.
*/

	function insert($table,$record) {
  	$temp = array();
    $fieldsnames = array();
  	foreach($record as $fieldName=>$field )
  	{
      $fieldsnames[] = $fieldName;
    	$temp[] = $field;
    }
  	$sql = "insert into " . $table . "(" . join(",",$fieldsnames) . ") values (" . join(",",$temp) . ")";
  	if ($this->conn->debug==true)
  	{
  	 echo "<hr>(".$this->driver.") $sql<hr>";
  	}
		$this->querySql = $sql;
  	return ($this->conn->query($sql));


	}


/*
   Funcion miembro que recibe como parametros: nombre de la tabla,
   un array con los nombres de los campos
   ,un array con los valores, un array con los nombres de los campo id y
   un array con los valores de los campos id respectivamente.
*/



	function update($table, $record, $recordWhere) {

	$tmpSet = array();
	$tmpWhere = array();
	foreach($record as $fieldName=>$field )
	{
	  $tmpSet[] = $fieldName . "=" . $field;
	}

	foreach($recordWhere as $fieldName=>$field )
	{
	  $tmpWhere[] = " " . $fieldName . " = " . $field . " ";
	}
	$sql = "update " . $table ." set " . join(",",$tmpSet) . "    where " . join(" and ",$tmpWhere);
  	if ($this->conn->debug==true)
  	{
  	 echo "<hr>(".$this->driver.") $sql<hr>";
  	}
//$this->conn->debug=true;
  	return ($this->conn->Execute($sql));

}


/*
   Funcion miembro que recibe como parametros: nombre de la tabla, un array con los
   nombres de los campos id, y un array con los valores de los id.
*/


	function delete($table, $record) {

	$temp = array();

	foreach($record as $fieldName=>$field )
	{
	$tmpWhere[] = "  " . $fieldName . "=" . $field;
	}
	$sql = "delete from " . $table . " where " . join(" and ",$tmpWhere);

	//print("*** $sql ****");
 	if ($this->conn->debug==true)
  	{
  	 echo "<hr>(".$this->driver.") $sql<hr>";
  	}
	return ($this->query($sql));

	}

	function nextId($secName){
		if ($this->conn->hasGenID)
			return $this->conn->GenID($secName);
		else{
			$retorno=-1;

			if ($this->driver=="oracle"){
				$q= "select $secName.nextval as SEC from dual";
				$this->conn->SetFetchMode(ADODB_FETCH_ASSOC);
				$rs=$this->query($q);
				//$rs!=false &&
				if  ( !$rs->EOF){
					$retorno = $rs->fields['SEC'];
					//print ("Retorna en la funcion de secuencia($retorno)");
				}
			}
			return $retorno;
		}
	}

 /*
 function datoActualizado($mensaje) {
	echo  "<script>";
	echo  ("alert ('$mensaje');");
	echo  "</script>";

}

*/
/*
   Funcion miembro que libera los recursos de la consulta realizada.
*/

/*
	function free(){

	ifx_free_result($this->id_query);

	}

*/

/*
   Funcion miembro que cierra la conexion abierta a la base de datos.
*/


	function close(){

	ifx_close($this->idconnection);

	}
}

?>
