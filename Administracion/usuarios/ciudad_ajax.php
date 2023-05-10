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

session_start();
$ruta_raiz = "../..";
include_once "$ruta_raiz/rec_session.php";

$codi_ciudad = 0 + $_GET["codigo"];
$area = (isset ($_GET["area"])) ? 0 + limpiar_numero($_GET["area"]) : 0;
if($area!=0 and $codi_ciudad==0)
{
    $sql = "select depe_pie1 as ciu_id from dependencia where depe_codi = $area"; //selecciona las ciudades de la tabla ciudad, para llenar el combobox
    $rsCiudad = $db->conn->Execute($sql);
    $codi_ciudad = 0+$rsCiudad->fields["CIU_ID"];
}

$sqlCmbCiu = "select nombre, id from ciudad order by 1";
$rsCmbCiu = $db->conn->Execute($sqlCmbCiu);
$usr_ciudad  = $rsCmbCiu->GetMenu2('codi_ciudad',$codi_ciudad,"0:&lt;&lt seleccione &gt;&gt;",false,"","id='codi_ciudad' Class='select'");

echo $usr_ciudad

?>