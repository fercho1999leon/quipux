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
require_once "$ruta_raiz/rec_session.php";
if (isset ($replicacion) && $replicacion && $config_db_replica_adm_criterios_permisos!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_adm_criterios_permisos);

$sql="select descripcion, id_permiso from permiso where estado=1 and perfil in (0,1,2,3,4) and id_permiso not in (26) order by 1";
 
$rs = $db->conn->query($sql);
    ?>
    <table class="borde_tab" width="100%">
        <tr><td class="titulos1"><center>SELECCIONE PERMISOS</center></td></tr>
      
        <?php
        while (!$rs->EOF) {
            $idperm=$rs->fields['ID_PERMISO'];
             
            echo "<tr id='tr_permisos_disponibles_$idperm' class='listado2' onclick='ver_nombre($idperm,2)'>
             <td title='".$columnas_desc[$col]."'>".$rs->fields['DESCRIPCION']."</td>
                      </tr>";
            $rs->MoveNext();
    }
    ?>
        
    </table>

