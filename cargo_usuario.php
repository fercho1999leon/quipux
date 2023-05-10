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

//$ruta_raiz = ".";
//session_start();
//include_once "$ruta_raiz/rec_session.php";
//require_once "$ruta_raiz/funciones.php";

$usuaCedula = limpiar_sql($_SESSION["krd"]);
$where = "";
if ($config_bloquear_acceso_ciudadano) $where = " and tipo_usuario=1 "; //No cargamos combo con ciudadanos en caso de bloqueo
// Verificamos si es usuario
$sql = "select usua_codi, inst_nombre, depe_nomb, usua_cargo, usua_nombre,tipo_usuario
        from usuario
        where usua_login like upper('$usuaCedula') and usua_esta=1 $where
        order by tipo_usuario asc, usua_nombre, inst_nombre";
//echo $sql;
$rs = $db->query($sql);
//Verificar si existe mas de un registro
if ($rs and !$rs->EOF)
{ // Muestra el combo de cargos
    $nombre = "&nbsp;&nbsp;Usuario: ";
    $cargoCombo = "<select name='cargo_usuario' id='cargo_usuario' class='selectCargo' style='width:850px' onchange='reiniciar_session();'>";
    while (!$rs->EOF){
        if($rs->fields["USUA_CODI"] == $_SESSION["usua_codi"])
            $seleccion = 'selected';
        else
            $seleccion = "";
        $tipo_usuario = ($rs->fields["TIPO_USUARIO"]==1) ? "<i>(Serv.) </i>" : "<i>(Ciu.) </i>";

        $cargoCombo .= "<option value='".$rs->fields["USUA_CODI"]."' $seleccion>
                            ".$tipo_usuario . $rs->fields["USUA_NOMBRE"]."
                        / Institución: ".$rs->fields["INST_NOMBRE"];
         if ($rs->fields["TIPO_USUARIO"]==1)
          $cargoCombo .= " / Área: ".$rs->fields["DEPE_NOMB"];
          $cargoCombo .= " / Puesto: ".$rs->fields["USUA_CARGO"];
                                
        $cargoCombo .= "</option>";
        $rs->MoveNext();
    }
    $cargoCombo .= "</select>";
    echo "<table border='0' cellspacing='2' cellpadding='0' class='selectCargo' style='border: none;' width='100%'><tr><td>$nombre</td><td>$cargoCombo</td></tr></table>";
}
?>
