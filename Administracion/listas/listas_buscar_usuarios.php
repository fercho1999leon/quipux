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

$ruta_raiz="../..";
session_start();
include_once "$ruta_raiz/rec_session.php";

$buscar_nombre = trim(limpiar_sql($_POST["txt_buscar_nombre"]));
$buscar_cargo = trim(limpiar_sql($_POST["txt_buscar_cargo"]));
$buscar_tipo_usuario = 0 + $_POST["txt_buscar_tipo_usuario"];
$buscar_dependencia = 0 + $_POST["txt_buscar_dependencia"];
$buscar_institucion = 0 + $_POST["txt_buscar_institucion"];
$lista_usuarios = "";

?>

<table class=borde_tab width="100%" cellpadding="0" cellspacing="4">
    <tr>
        <td colspan="9" align="right">
            <input class='botones_azul' title='Seleccionar todos los usuarios encontrados' type='button' value='Seleccionar Todos' onClick="seleccionar_todos_usuarios();">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </td>
    </tr>
    <tr class="grisCCCCCC" align="center">
        <td width="10%"  class="titulos5">Tipo</td>
        <td width="15%" class="titulos5">Nombres</td>
        <td width="15%" class="titulos5">Instituci&oacute;n</td>
        <td width="5%"  class="titulos5">T&iacute;tulo</td>
        <td width="15%" class="titulos5"><?=$descCargo?></td>
        <td width="15%" class="titulos5">&Aacute;rea</td>
        <td width="10%" class="titulos5">E-mail</td>
        <td width="5%"  class="titulos5">Uso</td>
        <td width="10%" class="titulos5">Acci&oacute;n</td>
    </tr>
<?

if ($buscar_nombre!="" or $buscar_cargo!="" or $buscar_institucion!="0" or $buscar_dependencia!="0") {
    $sql = "select u.*, now()::date - coalesce(substr(s.usua_fech_sesion::text,1,10), '2010-01-01')::date as \"uso\"
            from usuario u
            left outer join usuarios_sesion s on u.usua_codi=s.usua_codi
            where usua_esta = 1 and upper(u.usua_login) not like 'UADM%' and u.usua_codi>0";
    if ($buscar_nombre != "") $sql .= ' and ' . buscar_nombre_cedula($buscar_nombre);
    if ($buscar_cargo != "")  $sql .= ' and ' . buscar_cadena($buscar_cargo, "usua_cargo");
    if ($buscar_tipo_usuario != 0) $sql .= " and u.tipo_usuario='$buscar_tipo_usuario' ";
    if ($buscar_institucion != "0" && $buscar_tipo_usuario == 1) $sql .= " and u.inst_codi=$buscar_institucion";
    if ($buscar_dependencia != "0" && $buscar_tipo_usuario == 1) $sql .= " and u.depe_codi=$buscar_dependencia";
    $sql .= " order by u.usua_nombre asc"; // limit 300 offset 0";
//echo $sql;
    $rs=$db->query($sql);
    $i=0;
    if ($rs->EOF) {
        echo "<tr><td colspan=6><center><span class='titulosError'>No se encontraron Usuarios con ese nombre</span></center></td></tr>";
    }

    while(!$rs->EOF)
    {
        $codigo = trim($rs->fields["USUA_CODI"]);
        $lista_usuarios .= ",$codigo";
        $img_imagen = "<img src='$ruta_raiz/imagenes/internas/user3.jpg' name='img_uso' border='0' title='Sin Uso'>";
        if ($rs->fields["USO"] <= 50)
            $img_imagen = "<img src='$ruta_raiz/imagenes/internas/user2.jpg' name='img_uso' border='0' title='".$rs->fields["USO"]." d&iacute;as sin uso'>";
        if ($rs->fields["USO"] <= 7)
            $img_imagen = "<img src='$ruta_raiz/imagenes/internas/user1.jpg' name='img_uso' border='0' title='".$rs->fields["USO"]." d&iacute;as sin uso'>";
?>
    <tr onmouseover="this.style.background='#e3e8ec'" onmouseout="this.style.background='white', this.style.color='black'">
        <td><font size=1><? if ($rs->fields["TIPO_USUARIO"]==1) echo "Funcionario"; else echo "Ciudadano"; ?></font></td>
        <td><font size=1><?=$rs->fields["USUA_NOMBRE"] ?></font></td>
        <td><font size=1><?=$rs->fields["INST_NOMBRE"] ?></font></td>
        <td><font size=1><?=$rs->fields["USUA_TITULO"] ?></font></td>
        <td><font size=1><?=$rs->fields["USUA_CARGO"] ?> </font></td>
        <td><font size=1><?=$rs->fields["DEPE_NOMB"] ?></font></td>
        <td><font size=1><?=$rs->fields["USUA_EMAIL"] ?></font></td>
        <td align="center" valign="middle"><font size=1><?=$img_imagen ?></font></td>
        <td width="6%" align="center" valign="middle" ><font size=1>
            <input class='botones_azul' title='Seleccionar usuario' type='button' value='Seleccionar' onClick="seleccionar_usuario('<?=$codigo?>');"></font>
        </td>
    </tr>
  <?
        $i++;
        $rs->MoveNext();
    }
}
?>
</table>
<textarea id="txt_buscar_usuarios_lista" name="txt_buscar_usuarios_lista" style="display: none" cols="10" rows="1"><?=substr($lista_usuarios,1)?></textarea>