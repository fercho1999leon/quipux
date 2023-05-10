<?php
/**  Programa para el manejo de gestion documental, oficios, memorandos, circulares, acuerdos
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
/*****************************************************************************
**  Muestra las alertas programadas por el administrador del sistema        **
**  Programar para que se ejecute cada 5 minutos                            **
******************************************************************************/

$ruta_raiz = "../..";
include_once "$ruta_raiz/config.php";
include_once "$ruta_raiz/include/db/ConnectionHandler.php";

error_reporting(7);
$db = new ConnectionHandler("$ruta_raiz","busqueda");
$db->conn->SetFetchMode(ADODB_FETCH_ASSOC);

$db_bodega = new ConnectionHandler("$ruta_raiz", "bodega_test");
$db_bodega->conn->SetFetchMode(ADODB_FETCH_ASSOC);

$anio = 0 + $_POST["anio"];
$offset = 0 + $_POST["offset"];
$limit = 1;

$sql = "select anex_codigo, anex_path, anex_nombre from anexos where anex_codigo like '$anio%' order by anex_codigo limit $limit offset ".($limit*$offset);
$rs = $db->query($sql);

echo "$sql<br>";
if (!$rs or $rs->EOF) {
    //sleep(15);
    die ("Error - No se encuentran m&aacute;s archivos");
}

while (!$rs->EOF) {
    if (is_file("$ruta_raiz/bodega".$rs->fields["ANEX_PATH"])) {
        $archivo = base64_encode(file_get_contents("$ruta_raiz/bodega".$rs->fields["ANEX_PATH"]));
        $sql = "select func_grabar_archivo('".$rs->fields["ANEX_NOMBRE"]."', '$archivo') as arch_codi";

        list($useg, $seg) = explode(" ", microtime());
        $tiempo_inicio = 0 + $seg + $useg;

        $rs_bodega = $db_bodega->query($sql);

        list($useg, $seg) = explode(" ", microtime());
        $tiempo_fin = 0 + $seg + $useg;
    }
    echo ($tiempo_fin-$tiempo_inicio) . " - ". $rs_bodega->fields["ARCH_CODI"]."<br>";

    $sql = "insert into tmp_tiempo_insert (arch_codi, tiempo) values (".$rs_bodega->fields["ARCH_CODI"].", ".($tiempo_fin-$tiempo_inicio).")";
    $db_bodega->query($sql);

    $rs->MoveNext();
}
?>