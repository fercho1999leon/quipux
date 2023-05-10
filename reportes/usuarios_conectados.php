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


list($useg, $seg) = explode(" ", microtime());
$fecha = date("Y-m-d") . "&nbsp;&nbsp;&nbsp;&nbsp;Hora: " . date("H:i:s").substr($useg."0",1,7);

$sql = "select i.inst_nombre as institucion, count(us.usua_codi) as usuarios, i.inst_codi
            from (select usua_codi from usuarios_sesion where usua_fech_sesion>=(now()-'2 hour'::interval) and usua_sesion not like 'FIN%') as us
                left outer join usuarios u on us.usua_codi=u.usua_codi
                left outer join institucion i on i.inst_codi=coalesce(u.inst_codi,1)
        group by 1,3 order by 2 desc";
$rs = $db->query($sql);
//echo $sql;

echo "<html><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8'></head>";
include_once "$ruta_raiz/js/ajax.js";
?>

<script type="text/javascript">
    function fjs_popup_activar (titulo, url, parametros, script_onload) {
        try {
            document.getElementById('div_popup_pantalla_tabajo').innerHTML = '';
        } catch (e) {
            fjs_popup_crear_divs ();
        }
        document.getElementById('span_popup_titulo').innerHTML = titulo;
        document.getElementById('div_popup_bloquear_pantalla').style.display = '';
        document.getElementById('div_popup_pantalla_pequena').style.display = '';
        nuevoAjax('div_popup_pantalla_tabajo', 'POST', url, parametros, script_onload);
    }
    
    function fjs_popup_cerrar () {
        document.getElementById('div_popup_bloquear_pantalla').style.display = 'none';
        document.getElementById('div_popup_pantalla_pequena').style.display = 'none';
    }

    function fjs_popup_crear_divs () {
        var texto = '';
        texto = '<div id="div_popup_bloquear_pantalla" style="width: 100%; height: 100%; z-index: 1000; position: fixed; top: 0; left: 0; opacity:0.3; filter:alpha(opacity=30); background-color: black; display: none;"></div>\n' +
                '    <div id="div_popup_pantalla_pequena" style="width: 80%; height: 80%; z-index: 1001; position: fixed; top: 5%; left: 10%; background-color: white; border: #333333 2px solid; display: none">\n' +
                '        <div id="div_popup_titulo" style="font-weight: bold; text-align: center; font-size: small; color: #FFFFFF; background-color:#006394; width: 100%; height: 20px; position: relative;">\n' +
                '            <table width="100%" border="0" cellpadding="0" cellspacing="0">\n' +
                '               <tr height="18px">\n' +
                '                   <td width="3%">&nbsp;</td>\n' +
                '                   <td width="94%" style="font-weight: bold; text-align: center; font-size: small; color: #FFFFFF; vertical-align: middle"><span id="span_popup_titulo"></span></td>\n' +
                '                   <td width="3%" align="right" valign="bottom"><img src="../imagenes/close_button.gif" onclick="fjs_popup_cerrar();">&nbsp;</td>\n' +
                '               </tr>\n' +
                '            </table>\n' +
                '        </div>\n' +
                '        <div id="div_popup_pantalla_tabajo" style="background-color:white; width: 100%; height: 90%; position: relative; overflow: auto;"></div>\n' +
                '    </div>';
        document.body.innerHTML += texto;
        return;
    }


    function ver_usuarios_por_institucion(inst_codi) {
        titulo = 'N&uacute;mero de usuarios conectados al Sistema &quot;Quipux&quot; por Instituci&oacute;n';
        fjs_popup_activar (titulo, 'usuarios_conectados_por_institucion.php', 'institucion='+inst_codi);
    }
</script>

<?

echo "<body><center><br><h3>N&uacute;mero de usuarios conectados al Sistema &quot;Quipux&quot;</h3><h5>Fecha: $fecha</h5><br>";
echo "<table border='1' width='65%'><tr><th>&nbsp;</th><th>Instituci&oacute;n</th><th>N&uacute;mero de Usuarios</th></tr>";
$i = 0;
$total = 0;

if (!$rs or $rs->EOF) die ("<tr><td colspan=3 align='center'>No se encontraron usuarios conectados</td></tr></table>");

while (!$rs->EOF) {
    echo "<tr><td>&nbsp;".(++$i)."&nbsp;</td>
              <td><a href='javascript:' onclick=\"ver_usuarios_por_institucion('".$rs->fields["INST_CODI"]."')\">".$rs->fields["INSTITUCION"]."&nbsp;</a></td>
              <td align='center'>&nbsp;".$rs->fields["USUARIOS"]."</td>
          </tr>";
    $total += $rs->fields["USUARIOS"];
    $rs->MoveNext();
}

echo "<tr><th>&nbsp;</th><th>Total de usuarios conectados</th><th>$total</th></tr>";
echo "</table></center></body>";
?>

