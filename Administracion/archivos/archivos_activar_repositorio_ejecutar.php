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

$ruta_raiz = "../..";
session_start();
if($_SESSION["perm_actualizar_sistema"]!=1) die("ERROR: Usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
require_once "$ruta_raiz/rec_session.php";
$dba =  new ConnectionHandler($ruta_raiz, "bodega");

$repos_codi = 0 + limpiar_numero($_POST["repos_codi"]);
$repos_estado = 0 + limpiar_numero($_POST["repos_estado"]);

if ($repos_estado == 1) $accion = "Suspender";
elseif ($repos_estado == 2) $accion = "Activar";
elseif ($repos_estado == 3) $accion = "Cerrar";
else die("Acci&oacute;n no permitida");

$sql = "select esta_codi, round(pg_table_size(nombre_tabla)::numeric/tamanio_maximo::numeric,2) as uso
            , pg_table_size(nombre_tabla) as tamanio_actual
        from indice
        where indi_codi = $repos_codi";
$rs = $dba->conn->query($sql);
if (!$rs or $rs->EOF) die ("<center><h2><font color='red'>No se encontr&oacute; el repositorio</font></h2></center><br>");

if ($rs->fields["ESTA_CODI"]==3)
    die ("<center><h2><font color='red'>El repositorio ya se encuentra cerrado.</font></h2></center><br>");

if ($repos_estado==3 and $rs->fields["USO"]<0.8)
    die ("<center><h2><font color='red'>Para desactivar el repositorio, este debe tener un porcentaje de ocupaci&oacute;n superior al 80%.</font></h2></center><br>");

$record["indi_codi"] = $repos_codi;
$record["esta_codi"] = $repos_estado;
$record["tamanio"] = "pg_table_size(nombre_tabla)::numeric";
$ok = $dba->conn->Replace("indice", $record, "indi_codi", false, false, true, false);
if (!$ok) {
    $mensaje = "<center><h2><font color='red'>No se pudo $accion el repositorio.</font></h2></center>";
} else {
    $mensaje = "<center><h2><font color='blue'>La acci&oacute;n &quot;$accion Repositorio&quot; se realiz&oacute; correctamente.</font></h2></center>";
}

$sql = "select i.nombre_tablespace, i.nombre_tabla, i.esta_codi, e.nombre
            , round(i.tamanio::numeric/1073741824,3)::text||' Gb' as tamanio_actual
            , round(i.tamanio_maximo::numeric/1073741824,3)::text||' Gb' as tamanio_maximo
            , round(i.tamanio::numeric/i.tamanio_maximo::numeric*100,2) as uso
        from indice i
            left outer join estado_indice e on e.esta_codi = i.esta_codi
        where i.indi_codi = $repos_codi";
$rs = $dba->conn->query($sql);

$repos_estado = $rs->fields["NOMBRE"];
$repos_tabla = $rs->fields["NOMBRE_TABLA"];
$repos_tablespace = $rs->fields["NOMBRE_TABLESPACE"];
$repos_tamanio_actual = $rs->fields["TAMANIO_ACTUAL"];
$repos_tamanio_maximo = $rs->fields["TAMANIO_MAXIMO"];
$repos_uso = $rs->fields["USO"]." %";

?>
<table width="100%" cellpadding="0" cellspacing="3" border="0" class="borde_tab">
    <tr><th colspan="2">Informaci&oacute;n del repositorio</th></tr>
    <tr>
        <td width="20%">No. de Repositorio:</td>
        <td width="80%"><b><?=$repos_codi?></b></td>
    </tr>
    <tr>
        <td>Estado:</td>
        <td><b><?=$repos_estado?></b></td>
    </tr>
    <tr>
        <td>Nombre de la Tabla:</td>
        <td><b><?=$repos_tabla?></b></td>
    </tr>
    <tr>
        <td>Espacio Utilizado:</td>
        <td><b><?=$repos_tamanio_actual?></b></td>
    </tr>
    <tr>
        <td>Tama&ntilde;o M&aacute;ximo:</td>
        <td><b><?=$repos_tamanio_maximo?></b></td>
    </tr>
    <tr>
        <td>Porcentaje de Ocupaci&oacute;n:</td>
        <td><b><?=$repos_uso?></b></td>
    </tr>
    <tr>
        <td colspan="2"><?=$mensaje?></td>
    </tr>
</table>
