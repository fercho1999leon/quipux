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
if($_SESSION["perm_actualizar_sistema"]!=1) die("Usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
require_once "$ruta_raiz/rec_session.php";
$dba =  new ConnectionHandler($ruta_raiz, "bodega");

$repos_codigo = 0 + limpiar_numero($_POST["repos_codi"]);

$sql = "select i.nombre_tablespace, i.nombre_tabla, i.esta_codi, e.nombre
            , round(i.tamanio::numeric/1073741824,3)::text||' Gb' as tamanio_actual
            , round(i.tamanio_maximo::numeric/1073741824,3)::text||' Gb' as tamanio_maximo
            , round(i.tamanio::numeric/i.tamanio_maximo::numeric*100,2) as uso
        from indice i
            left outer join estado_indice e on e.esta_codi = i.esta_codi
        where i.indi_codi = $repos_codigo";
$rs = $dba->conn->query($sql);

if (!$rs or $rs->EOF) die ("<center><h2><font color='red'>No se encontr&oacute; el repositorio</font></h2></center><br>");

$repos_estado = $rs->fields["NOMBRE"];
$repos_tabla = $rs->fields["NOMBRE_TABLA"];
$repos_tablespace = $rs->fields["NOMBRE_TABLESPACE"];
$repos_tamanio_actual = $rs->fields["TAMANIO_ACTUAL"];
$repos_tamanio_maximo = $rs->fields["TAMANIO_MAXIMO"];
$repos_uso = $rs->fields["USO"]." %";

$botones = "<input type='button' name='btn_estado_repositorio' value='Activar Repositorio'
            class='botones_largo' onclick='fjs_cambiar_estado_repositorio($repos_codigo,2)'>";
if (0+$rs->fields["ESTA_CODI"] == 2) {
    $botones = "<input type='button' name='btn_estado_repositorio' value='Suspender Repositorio'
                class='botones_largo' onclick='fjs_cambiar_estado_repositorio($repos_codigo,1)'>";
}

if ((0+$rs->fields["USO"])>80) {
    $botones .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type='button' name='btn_estado_repositorio' value='Cerrar Repositorio'
                class='botones_largo' onclick='fjs_cambiar_estado_repositorio($repos_codigo,3)'>";
}
?>
<table width="100%" cellpadding="0" cellspacing="3" border="0" class="borde_tab">
    <tr><th colspan="2">Informaci&oacute;n del repositorio</th></tr>
    <tr>
        <td width="20%">No. de Repositorio:</td>
        <td width="80%"><b><?=$repos_codigo?></b></td>
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
</table>
<br>
<center><?=$botones?></center>
<br>