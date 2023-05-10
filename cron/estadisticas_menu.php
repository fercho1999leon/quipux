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
**  Genera estadisticas para la pagina inicial del sistema                  **
**  Programar para que se ejecute cada 5 minutos                            **
******************************************************************************/

$ruta_raiz = "..";
include_once ("$ruta_raiz/include/db/ConnectionHandler.php");
include_once ("$ruta_raiz/config.php");
error_reporting(7);
$db = new ConnectionHandler("$ruta_raiz");
$db->conn->SetFetchMode(ADODB_FETCH_ASSOC);


$sql = "select a.*, c.* --Estadisticas Menu
        from (select sum(num_documentos) as num_documentos, sum(num_doc_firma) as num_doc_firma, sum(no_usuarios) as num_usuarios from tmpreporteestadisticas) as a
--        left outer join (select sum(num_documentos)::integer as num_doc_dia from tmpreporteestadisticas_hora) as b on 1=1
        left outer join (select count(usua_codi) as num_usua_conec from usuarios_sesion
                         where usua_fech_sesion::date = now()::date and usua_sesion not like 'FIN%'
                         and substr((current_timestamp-usua_fech_sesion)::text,1,2)::integer <=1
        ) as c on 1=1";
$rs = $db->query($sql);

$texto = '<table style="border: #FFAE4A 1px solid; -moz-border-radius:10px; -webkit-border-radius:10px; width: 98%; background-color: #FAD184;" border="0" cellpadding="0" cellspacing="3">
            <tr>
                <td style="font-size: 11px; font-weight: bold; text-align: center;">Estad&iacute;sticas de uso</td>
            </tr>
            <tr>
                <td style="font-size: 10px; text-align: left">
                    &nbsp;Usuarios conectados: <b>'.$rs->fields["NUM_USUA_CONEC"].'</b><br>
                    &nbsp;Usuarios registrados: <b>'.$rs->fields["NUM_USUARIOS"].'</b><br>
                    &nbsp;Docs. generados: <b>'.$rs->fields["NUM_DOCUMENTOS"].'</b><br>
                    &nbsp;Otras estad&iacute;sticas:
                        <a href="http://www.informatica.gob.ec/index.php?option=com_reporte_usuarios_quipux" target="_blank">
                            <font color="blue"><b>aqu&iacute; <b></font>
                </td>
            </tr>
          </table>
          <br>';
echo date('Y-m-d H:i:s');//."<br>".$texto;
file_put_contents("$ruta_raiz/bodega/estadisticas_menu.html", $texto)
?>
