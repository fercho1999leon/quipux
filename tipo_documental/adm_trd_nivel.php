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
$ruta_raiz = isset($ruta_raiz) ? $ruta_raiz : "..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

session_start();
include_once "$ruta_raiz/rec_session.php";

$flag_nivel = false;
if ($depe_actu) {
    $rs=$db->conn->query("select count(*) as num from trd where depe_codi=$depe_actu");
    if ($rs->fields["NUM"]==0)
        $max_nivel = 10;
    else {
        $flag_nivel = true;
    	$rs=$db->conn->query("select count(*) as num from trd_nivel where depe_codi=$depe_actu");
        $max_nivel = $rs->fields["NUM"];
    }
} else
    $max_nivel = 10;

if ($_POST['txt_ok']=="1")
{
	$j=0;
	$db->conn->Execute("delete from trd_nivel where depe_codi=$depe_actu");
	for ($i=0;$i<10;$i++)
	{
	    $nom=trim(strtoupper($_POST["nom_$i"]));
	    $desc=$_POST["desc_$i"]; if ($desc=="") $desc="null"; else $desc="'$desc'";
	    if ($nom!="") {
		$db->conn->Execute("insert into trd_nivel (trd_codi,depe_codi,trd_nombre,trd_descripcion) values ($j, $depe_actu, '$nom', $desc)");
	    	$j++;
	    }
	}
}

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

?>

<script language="Javascript">
    function validar_form()
    {
	<?if ($flag_nivel) {?>
	    for (i=0;i < <?=$max_nivel?>;i++) {
		if (document.getElementById('nom_'+i).value.replace(/ /g, '')=='') {
		    alert ('El nombre en los <?=$max_nivel?> items es obligatorio');
		    return;
		}
	    }
	<? } ?>
	document.getElementById('txt_ok').value='1';
	document.formulario.submit();
    }

    function ver_datos(num)
    {	
	if (num < <?=$max_nivel?>)
	    document.getElementById('tr_'+num).style.display='';
	
    }

    function ocultar_datos()
    {	
	for (i=0;i < <?=$max_nivel-1?>;i++) {
	    j=i+1;
	    if (document.getElementById('nom_'+i).value=='')
		document.getElementById('tr_'+j).style.display='none';
	}
    }
</script>
<body>
<center>
<form name="formulario" id="formulario" method="post">
<input type="hidden" name="txt_ok" id="txt_ok" value="">
<table width="80%"  class="borde_tab">
    <tr>
	<td colspan="6" height="40" align="center" class="titulos4"><b>Organizaci&oacute;n de <?=($_SESSION["descTRDpl"])?></b></td>
    </tr>
    <tr>
	<td width="25%" align="left" class="titulos2"><b>&nbsp;Seleccione <?=$descDependencia?></b></td>
	<td width="75%" colspan="5" class="listado2">
<?
	$sql = "select DEPE_NOMB, DEPE_CODI from dependencia where depe_estado=1 and inst_codi=".$_SESSION["inst_codi"]." order by depe_nomb";
	$rs=$db->conn->query($sql);
	echo $rs->GetMenu2("depe_actu", $depe_actu, "0:&lt;&lt seleccione &gt;&gt;", false,"","class='select' Onchange='document.formulario.submit()'");
	$rs->Move(0);
?>
	</td>
    </tr>
</table>

<?if ($depe_actu) {?>
    <br>
    <table width="80%"  class="borde_tab">
    	<tr>
	    <td width="10%" align="center" class="titulos2"><b>No.</b></td>
	    <td width="30%" align="center" class="titulos2"><b>Nombre Item</b></td>
	    <td width="40%" align="center" class="titulos2"><b>Descripci&oacute;n Item</b></td>
    	</tr>
<?
	$sql = "select * 
		from trd_nivel
		where depe_codi=$depe_actu order by trd_codi ASC";
//echo $sql;
	$rs2=$db->conn->query($sql);

	for ($i=0;$i<$max_nivel;$i++) {
	    if (!$rs2->EOF) {
		${"nom_$i"}=$rs2->fields["TRD_NOMBRE"];
		${"desc_$i"}=$rs2->fields["TRD_DESCRIPCION"];
		$rs2->MoveNext();
	    } else {
		${"nom_$i"}="";
		${"desc_$i"}="";
	    }
?>
	    <tr name="tr_<?=$i?>" id="tr_<?=$i?>">
		<td class='listado2'><center><?=$i+1?></center></td>
		<td class='listado2'><center>
		    <input type="text" name="nom_<?=$i?>" id="nom_<?=$i?>" class='ecajasfecha' size=30 maxlength=50 value="<?=${'nom_'.$i}?>" onfocus="ver_datos(<?=$i+1?>)">
		</center></td>
		<td class='listado2'><center>
		    <input type="text" name="desc_<?=$i?>" id="desc_<?=$i?>" class='ecajasfecha' size=60 maxlength=100 value="<?=${'desc_'.$i}?>">
		</center></td>
	    </tr>
<?	}	?>

    </table>
    <script>ocultar_datos();</script>
<? } ?>

<br>
<table width="80%"   cellpadding="0" cellspacing="0" >
    <tr>
	<?if ($depe_actu) {?>
	    <td align="center">
            <input name="btn_accion" type="button" class="botones" id="btn_accion" value="Aceptar" onClick="validar_form()">
	    </td>
	<? } ?>
	<td align="center">
	    <input name="btn_accion" type="button" class="botones" id="btn_accion" value="Regresar" onClick="window.location='<?=$ruta_raiz?>/tipo_documental/menu_trd.php';">
	</td>
    </tr>
</table>

</form>
</center>
</body>
</html>
