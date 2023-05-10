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
$ruta_raiz = "..";
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/obtenerdatos.php";

//Variables
$radi_nume = limpiar_numero($_POST['radi_nume']);

$datos_usr = ObtenerDatosUsuario($_SESSION["usua_codi"], $db);
$datos_opc_imp = ObtenerDatosOpcImpresion($radi_nume, $db);

?>

<br/><br/>
<div id="div_opciones_acuerdos" >
    <fieldset  class="borde_tab">
        <legend>OPCIONES GENERALES DEL DOCUMENTO</legend>
            <table>
                <tr>
                    <td class="listado1_ver" width="13%">Dado en:</td>
                    <td width="25%">
                       <input type="text" id="txt_ciudad_dado_en" name="txt_ciudad_dado_en" class="text_transparente" value="<?=$datos_usr["ciudad"]?>" readonly/>
                    </td>
                    <td width="30%">
                        <input type="text" id="txt_opc_ciudad_dado_en" name="txt_opc_ciudad_dado_en" class="text_transparente" size="30" value="<?=$datos_opc_imp["CIUDAD_DADO_EN"]?>" onblur="deshabilitaObj('ciu_ori'); histop('f',this,<?="'".$datos_opc_imp["CIUDAD_DADO_EN"]."'"?>);" readonly>
                    </td>
                    <td>
                        <img src="<?=$ruta_raiz?>/imagenes/internas/pencil_add.png" name="Image1" align="middle" border="0" title="Modifica la ciudad del texto &quot;Dado en&quot;" onclick="habilitaObj('ciu_ori')">
                    </td>
                                   
                </tr>
            </table>
           
        </fieldset>
</div>