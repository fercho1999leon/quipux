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

/*****************************************************************************************
** Consulta los usuariosconectados al sistema Quipux.                           **
**                                                                                      **
** Desarrollado por: Mauricio Haro A. - moriz21@hotmail.com                             **
*****************************************************************************************/


$ruta_raiz = "..";
include_once "$ruta_raiz/include/db/ConnectionHandler.php";
include_once "$ruta_raiz/config.php";

$db = new ConnectionHandler("$ruta_raiz","$config_db_replica_rep_usuarios_conectados");
$db->conn->SetFetchMode(ADODB_FETCH_ASSOC);

$institucion = 0+ $_POST["institucion"];
if ($institucion==0) die ("No se encontr&oacute; la instituci&oacute;n");
if ($institucion==1) $institucion = "0,1";

list($useg, $seg) = explode(" ", microtime());
$fecha = date("Y-m-d") . "&nbsp;&nbsp;&nbsp;&nbsp;Hora: " . date("H:i:s").substr($useg."0",1,7);

$sql = "select u.usua_nombre, u.usua_cargo, us.usua_fech_sesion, u.inst_nombre
        from (select usua_codi, usua_fech_sesion from usuarios_sesion where usua_fech_sesion>=(now()-'2 hour'::interval) and usua_sesion not like 'FIN%') as us
            left outer join usuario u on us.usua_codi=u.usua_codi
        where u.inst_codi in ($institucion)
        order by 1 desc";
$rs = $db->query($sql);
//echo $sql;
if (!$rs or $rs->EOF) die ("<center><h3>No se encontraron usuarios conectados</h3><h5>Fecha: $fecha</h5></center>");

echo "<center><br><h3>".$rs->fields["INST_NOMBRE"]."</h3><h5>Fecha: $fecha</h5><br>";
echo "<table border='1' width='90%'><tr><th>&nbsp;</th><th>Usuario</th><th>Cargo</th><th>Hora Ingreso</th></tr>";
$i = 0;
$total = 0;

while (!$rs->EOF) {
    echo "<tr>
              <td align='center'>&nbsp;".(++$i)."&nbsp;</td><td>".$rs->fields["USUA_NOMBRE"]."&nbsp;</td>
              <td>".$rs->fields["USUA_CARGO"]."&nbsp;</td>
              <td align='center'>".substr($rs->fields["USUA_FECH_SESION"],11,8)."&nbsp;</td>
          </tr>";
    $rs->MoveNext();
}

echo "</table>
        <br><br>
        <input type='button' name='btn_cerrar' value='Cerrar' onClick='fjs_popup_cerrar()'>
      </center>";
?>

