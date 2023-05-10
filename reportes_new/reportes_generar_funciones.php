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

$drill = "";
function drill_anadir($reporte, $campo="", $valor="") {
    global $drill, $sql, $group, $nomb_as;
    $campo = trim($campo);
    $valor = trim($valor);
    $nomb_as_drill = trim(substr($nomb_as, 0,-1)) . '_drill"';
    $drill2 = $drill;
    if ($campo!="" and $valor!="")
        $drill2 .= "||'&$campo='||$valor";

    $sql["select"] .= "'generar_reporte(\"$reporte\",\"'$drill2||'\")' $nomb_as_drill, ";
    $sql["group"] .= ++$group . ", ";
}

function drill_parametro($campo, $valor) {
    global $drill;
    $campo = trim($campo);
    $valor = trim($valor);
    $drill .= "||'&$campo='||$valor";
}



?>