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
*
*   Ejecuta las actualizaciones de la BDD
*
***/
$ruta_raiz = "../..";
session_start();
if($_SESSION["perm_actualizar_sistema"]!=1) die("Usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
require_once "$ruta_raiz/rec_session.php";

$txt_anio = 0 + $_POST["txt_anio"];
$sql = "select count(1) as total_registros
            , count(case when radi_nume_radi::text like '%0' and esta_codi in (0,6) then 1 else null end) as total_pdf
        from radicado
        where radi_nume_temp::text like '$txt_anio%0' and radi_path is null and esta_codi in (0,2,5,6)";
$rs = $db->conn->query($sql);

echo "<font size=3>";
echo "A&ntilde;o: <b>$txt_anio</b><br>";
echo "No. archivos PDF por generar: <b>".$rs->fields["TOTAL_PDF"]."</b><br>";
echo "No. registros por actualizar: <b>".$rs->fields["TOTAL_REGISTROS"]."</b><br>";
echo "</font>"

?>
