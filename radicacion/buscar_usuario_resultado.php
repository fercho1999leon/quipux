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


function restaFechas($dFecIni, $dFecFin)
{
    $dFecIni = str_replace("-","",$dFecIni);
    $dFecIni = str_replace("/","",$dFecIni);
    $dFecFin = str_replace("-","",$dFecFin);
    $dFecFin = str_replace("/","",$dFecFin);

    ereg( "([0-9]{1,2})([0-9]{1,2})([0-9]{2,4})", $dFecIni, $aFecIni);
    ereg( "([0-9]{1,2})([0-9]{1,2})([0-9]{2,4})", $dFecFin, $aFecFin);

    $date1 = mktime(0,0,0,$aFecIni[2], $aFecIni[1], $aFecIni[3]);
    $date2 = mktime(0,0,0,$aFecFin[2], $aFecFin[1], $aFecFin[3]);

    return round(($date2 - $date1) / (60 * 60 * 24));
}

$ruta_raiz="..";
session_start();
include_once "$ruta_raiz/rec_session.php";
if (isset ($replicacion) && $replicacion && $config_db_replica_buscar_usuario_resultado!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_buscar_usuario_resultado);
include_once("$ruta_raiz/obtenerdatos.php");

if (!$buscar_inst) $buscar_inst="0";
if (!$buscar_depe) $buscar_depe="0";
if (!$lista_usr) $lista_usr="0";
$usuarios_lista = "";

?>

<table class=borde_tab width="100%" cellpadding="0" cellspacing="4">
    <tr class="grisCCCCCC" align="center">
        <td width="2%" class="titulos5">Tipo</td>
        <td width="10%" class="titulos5">Nombres</td>
        <td width="10%" class="titulos5">Instituci&oacute;n</td>
        <td width="5%"  class="titulos5">T&iacute;tulo</td>
        <td width="10%"  class="titulos5"><?=$descCargo?></td>
        <td width="10%" class="titulos5">&Aacute;rea</td>
        <td width="8%"  class="titulos5">E-mail</td>
        <td width="5%"  class="titulos5">Uso</td> <!--VJ-->
        <?if ($lista_usr=="0") {?><td colspan="5" class="titulos5">Colocar como</td><? } ?>
    </tr>
<?
$buscar_nom = trim(limpiar_sql($buscar_nom));
$buscar_car = trim(limpiar_sql($buscar_car));
$sql="";
$usr_sesion=", '" . date('Y-m-d') . "'::date - coalesce(substr(g.usua_fech_sesion::text,1,10), '2010-01-01')::date as \"uso\" ";
$usr_tipo = ", (CASE WHEN u.inst_codi=0 THEN 3 ELSE (CASE WHEN u.inst_codi=".$_SESSION["inst_codi"]." THEN 1 ELSE 2 END) END) as \"usua_tipo\" ";

$where = "";

if (($buscar_nom!="" or $buscar_car!="" or $buscar_inst!="0" or $buscar_depe!="0") and $lista_usr=="0") {

    $where .= buscar_datos_usuario($buscar_nom);
    if ($_SESSION["tipo_usuario"]==2) // Si es ciudadano
        $where .= " and usua_codi in (select usua_codi from permiso_usuario where id_permiso=9)";
    if ($buscar_tipo != 0)
        $where .= " and tipo_usuario=$buscar_tipo ";
    if ($buscar_inst != "0" && ($buscar_tipo == 1 || $buscar_tipo == 0))
        $where .= " and inst_codi=$buscar_inst";
    if ($buscar_depe != "0" && ($buscar_tipo == 1 || $buscar_tipo == 0))
        $where .= " and depe_codi=$buscar_depe";

    $sql = "select u.* $usr_tipo $usr_sesion 
            from (select * from usuario where usua_esta<>0 and upper(usua_login) not like 'UADM%' and usua_codi>0 $where) as u
                left outer join usuarios_sesion g on u.usua_codi=g.usua_codi
             order by u.usua_nombre asc limit 300 offset 0";

//echo $sql;
}
if ($lista_usr!="0") {
    $sql = "select u.* $usr_sesion
            from (select * from lista_usuarios where lista_codi=$lista_usr) as l
                left outer join usuario u on u.usua_codi=l.usua_codi
                left outer join usuarios_sesion g on l.usua_codi=g.usua_codi
            where u.usua_login not like 'UADM%' order by l.orden asc, u.usua_nombre asc";
}
//echo 'SQL: >' . $sql . "<";
if ($sql!="") {
    $rs=$db->query($sql);
    $i=0;
    if ($rs->EOF) {
        if ($lista_usr=="0")
            echo "<tr><td colspan=12><center><span class='titulosError'>No se encontraron Usuarios con ese Nombre o número de CI</span></center></td></tr>";
        else
            echo "<tr><td colspan=12><center><span class='titulosError'>La lista se encuentra vac&iacute;a</span></center></td></tr>";
    }

    while(!$rs->EOF)
    {
        $codigo = trim($rs->fields["USUA_CODI"]);
        $tipous = trim($rs->fields["USUA_TIPO"]);
        $inst_codi_doc = trim($rs->fields["INST_CODI"]);
        $usuarios_lista .= '-'.$codigo.'-';
        $usua_firma = ((0+$rs->fields["USUA_TIPO_CERTIFICADO"])==0) ? "" : "_fd";
        $img_imagen = "<img src='$ruta_raiz/imagenes/internas/user3$usua_firma.jpg' name='img_uso' border='0' title='Sin Uso'>";
        if ($rs->fields["USO"] <= 50)
            $img_imagen = "<img src='$ruta_raiz/imagenes/internas/user2$usua_firma.jpg' name='img_uso' border='0' title='".$rs->fields["USO"]." d&iacute;as sin uso'>";
        if ($rs->fields["USO"] <= 7)
            $img_imagen = "<img src='$ruta_raiz/imagenes/internas/user1$usua_firma.jpg' name='img_uso' border='0' title='".$rs->fields["USO"]." d&iacute;as sin uso'>";

        //Imprimir en resultado siglas de la institución a la que pertenece el usuario
        if($rs->fields["INST_SIGLA"] != "")
            $sigla_institucion = ' / '.$rs->fields["INST_SIGLA"];
        else
            $sigla_institucion = "";
?>
    <tr onmouseover="this.style.background='#e3e8ec'" onmouseout="this.style.background='white', this.style.color='black'">
        <td><font size=1><? if ($rs->fields["TIPO_USUARIO"]==1) echo "<i>(Serv.)</i>"; else echo "<i>(Ciu.)</i>"; ?></font></td>
        <td><font size=1><?=substr($rs->fields["USUA_NOMBRE"],0,120).$sigla_institucion ?></font></td>
        <td><font size=1><?=substr($rs->fields["INST_NOMBRE"],0,100) ?></font></td>
        <td><font size=1><?=substr($rs->fields["USUA_TITULO"],0,70) ?></font></td>
        <td><font size=1><?=$rs->fields["USUA_CARGO"] ?> </font></td>
        <td><font size=1><?=$rs->fields["DEPE_NOMB"] ?></font></td>
        <td><font size=1><?=$rs->fields["USUA_EMAIL"] ?></font></td>
        <td align="center" valign="middle"><font size=1><?=$img_imagen ?></font></td>
<?
        if ($lista_usr=="0") {?>
            <td width="6%" align="center" valign="middle" ><font size=1>
                <?php                
//                if ($ent==2){ //documentos externos no para para ciudadanos                  
//                    if($rs->fields["TIPO_USUARIO"]==1){?>
                <input class='botones_azul' title='Para' type='button' value='Para' onClick="pasar('<?=$codigo?>','1');"/></font>
                <?php //}
                //}else{//demas documentos ?>
<!--                <input class='botones_azul' title='Para' type='button' value='Para' onClick="pasar('<?=$codigo?>','1');"></font>-->
               <?php //} ?>
            </td>
            <td width="6%" align="center" valign="middle" ><font size=1>
            <? if (($ent!=1 or $tipous==1) and $_SESSION["tipo_usuario"]!=2)
               echo "<input class='botones_azul' title='De' type='button' value='De' onClick=\"pasar('$codigo','2');\">";?></font>
            </td>
            <td width="7%" align="center" valign="middle" ><font size=1>
                <input class='botones_azul' title='Copia' type='button' value='Copia' onClick="pasar('<?=$codigo?>','3');"></font>
            </td>
        <? } ?>
    </tr>
  <?
        $i++;
        $rs->MoveNext();
    }
}
?>

</table>
<textarea id="usuarios_lista" name="usuarios_lista" style="display: none" cols="1" rows="1"><?=$usuarios_lista?></textarea>
