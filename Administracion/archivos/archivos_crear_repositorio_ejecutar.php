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
sleep(1);
$ruta_raiz = "../..";
session_start();
if($_SESSION["perm_actualizar_sistema"]!=1) die("ERROR: Usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
require_once "$ruta_raiz/rec_session.php";
$dba =  new ConnectionHandler($ruta_raiz, "bodega");

$slc_repos_tablespace = trim(limpiar_sql($_POST["slc_repos_tablespace"]));
$txt_repos_tamanio = 0 + limpiar_numero($_POST["txt_repos_tamanio"]);

//$rs_tbl = $dba->conn->query("SELECT spcname, spclocation FROM pg_tablespace where upper(spcname)=upper('$slc_repos_tablespace')");
$rs_tbl = $dba->conn->query("SELECT spcname FROM pg_tablespace where upper(spcname)=upper('$slc_repos_tablespace')");
if (!$rs_tbl or $rs_tbl->EOF) die ("ERROR: No se encontr&oacute; el tablespace solicitado.");
$nombre_tablespace = $rs_tbl->fields["SPCNAME"];

//Insertamos un nuevo registro en la BDD
$rs = $dba->conn->query("select coalesce(max(indi_codi),0) as codigo from indice");
if (!$rs or $rs->EOF) die ("ERROR: No se pudo obtener el ID de la tabla &quot;indice&quot;.");

$indi_codi = 1+$rs->fields["CODIGO"];
$nombre_tabla = "archivo_" . str_pad($indi_codi, 4, "0", STR_PAD_LEFT);

$dba->conn->BeginTrans();

if (isset($record)) unset($record);
$record["indi_codi"] = $indi_codi;
$record["nombre_tabla"] = $dba->conn->qstr($nombre_tabla);
$record["nombre_tablespace"] = $dba->conn->qstr($nombre_tablespace);
$record["esta_codi"] = "1";
$record["fecha_creacion"] = $dba->conn->sysTimeStamp;
$record["usua_codi_crea"] = $_SESSION["usua_codi"];
$record["tamanio_maximo"] = $txt_repos_tamanio*1024*1024*1024; // 1Gb
$ok = $dba->conn->Replace("indice", $record, "", false, false, true, false);
if (!$ok) die ("ERROR: No se pudo crear el registro en la tabla &quot;indice&quot;.");

$sql = "CREATE TABLE $nombre_tabla (
            arch_codi bigint NOT NULL,
            archivo character varying,
            CONSTRAINT pk_$nombre_tabla PRIMARY KEY (arch_codi),
            CONSTRAINT fk_$nombre_tabla FOREIGN KEY (arch_codi)
                REFERENCES archivo (arch_codi) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION
        )
        TABLESPACE $nombre_tablespace;";
$ok = $dba->conn->query($sql);
if (!$ok) {
    $dba->conn->RollbackTrans();
    die ("ERROR: No se pudo crear la tabla &quot;$nombre_tabla&quot;.");
}
$dba->conn->CommitTrans();
echo "Se cre&oacute; correctamente el repositorio &quot;$nombre_tabla&quot;";
?>
