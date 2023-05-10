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
    unset($rsArea);
    if($_GET["codDepe"]!='')
    {
        $sqlArea = "select
                            depe_nomb
                         from
                            dependencia
                         where
                            depe_estado=1 and depe_codi = ".$_GET["codDepe"];
        $rsArea=$db->conn->query($sqlArea);
        while(!$rsArea->EOF)
        {
            $inputArea = $rsArea->fields["DEPE_NOMB"];
            $rsArea->MoveNext();
        }
    }
    else
        $inputArea = '';
    echo '&nbsp;&nbsp;'.$inputArea;
    //echo htmlspecialchars($menu_depeHijo);
?>