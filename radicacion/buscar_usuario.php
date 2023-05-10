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
session_start();
include_once "$ruta_raiz/rec_session.php";
include_once("$ruta_raiz/obtenerdatos.php");

$buscar_tipo = limpiar_sql($_POST['buscar_tipo']);
$buscar_nom  = limpiar_sql($_POST['buscar_nom']);
$buscar_car  = limpiar_sql($_POST['buscar_car']);
$buscar_inst  = limpiar_sql($_POST['buscar_inst']);
$lista_usr  = limpiar_sql($_POST['lista_usr']);
   // Las siguientes lineas se incluyeron porque, hay dos fuentes de esta variable cuando se llama desde NEW.php viene por GET
 if ( !isset( $_GET['documento_us1'] ) )
   $documento_us1  = limpiar_sql($_POST['documento_us1']);
 else
   $documento_us1  = limpiar_sql($_GET['documento_us1']);

  if ( !isset( $_GET['documento_us2'] ) )
   $documento_us2  = limpiar_sql($_POST['documento_us2']);
 else
   $documento_us2  = limpiar_sql($_GET['documento_us2']);

  if ( !isset( $_GET['concopiaa'] ) )
   $concopiaa  = limpiar_sql($_POST['concopiaa']);
 else
   $concopiaa  = limpiar_sql($_GET['concopiaa']);


$flag_inst  = limpiar_sql($_POST['concopiaa']);
//$krd = $_GET['krd'];
$ent=limpiar_sql($_GET['ent']);


if (!$buscar_inst) $buscar_inst="0";
if (!$lista_usr) $lista_usr="0";

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

?>
<SCRIPT Language="JavaScript" SRC="../js/crea_combos_2.js"></SCRIPT>
<script LANGUAGE="JavaScript">


documento=new Array();
tipo_us=new Array();


function pasar_datos()
{
	opener.document.formulario.documento_us1.value = document.formu1.documento_us1.value;
	opener.document.formulario.documento_us2.value = document.formu1.documento_us2.value;
	opener.document.formulario.concopiaa.value = document.formu1.concopiaa.value;
	opener.document.formulario.fl_modificar1.value = "1";

	/**
	* Pasar id de instituciones.
	**/
	opener.document.formulario.flag_inst.value=document.formu1.flag_inst.value;	

	opener.refrescar_pagina(""); //Recargar Página Origen
	window.close();
}

function pasar(indice,tipo,sbmit)
{
	cadena = '-'+documento[indice]+'-';
	if(tipo=='1')	{
	    str = document.formu1.documento_us1.value;
	    if (str.indexOf(cadena) < 0) {
		str = str + cadena;	
	    }
	    document.formu1.documento_us1.value = str;
	}
	if(tipo==2)	{
	    str = document.formu1.documento_us2.value;
	    if (str.indexOf(cadena) < 0) {
		str = <?if ($ent==2) echo "str + ";?> cadena;	
	    }
	    document.formu1.documento_us2.value = str;
	}
	if(tipo==3) {
	    str=document.formu1.concopiaa.value;
	    if (str.indexOf(cadena) < 0) {
		str = str + cadena;	
	    }
	    document.formu1.concopiaa.value = str;
	}
	if (sbmit=='S')
	    document.formu1.submit();
}

function pasar_lista(tipo)
{
     	num = documento.length;
	for (i=0; i<num; i++) 
	    pasar(i, tipo, 'N');
    	document.formu1.submit();
}


function borrarCCA(codigo,tipo)
{
	if (tipo=='D') {
		str = document.formu1.documento_us1.value;
		str = str.replace('-'+codigo+'-','');
		document.formu1.documento_us1.value = str;
	}
	if (tipo=='R') {
		str = document.formu1.documento_us2.value;
		str = str.replace('-'+codigo+'-','');
		document.formu1.documento_us2.value = str;
	}
	if (tipo=='C') {
		str=document.formu1.concopiaa.value;
		str = str.replace('-'+codigo+'-','');
		document.formu1.concopiaa.value=str;
	}
	document.formu1.submit();
}

function crear_ciudadano()
{
    windowprops = "top=0,left=0,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=1100,height=550";
    //URL = '../Administracion/usuarios/mnuUsuarios_ext.php?cerrar=Si';
    URL = '../Administracion/ciudadanos/adm_usuario_ext.php?cerrar=Si&accion=1';
    window.open(URL , "Crear Usuario Externo", windowprops);
    return;
}

function buscar_ciudadano()
{
    //option_index = <?php echo $_SESSION["inst_codi"];?>;
    style_display = '';
    style_display_ciu = 'none';

    if (document.formu1.buscar_tipo.value == 2) {
        //option_index = 0;
        style_display = 'none';
        style_display_ciu = '';
    }
    //document.getElementById('buscar_inst').options[option_index].selected = true;
    document.getElementById('tr_institucion').style.display = style_display;
    document.getElementById('td_btn_ciudadano').style.display = style_display_ciu;
    return;
}
</script>

<body bgcolor="#FFFFFF">
<?$varenvio="buscar_usuario.php?krd=$krd&ent=$ent";?>
<form method="post" name="formu1" id="formu1" action="<?=$varenvio?>" >

<table border=0 width="100%" class="borde_tab" cellpadding="0" cellspacing="5">

<tr>
	<td width="30%" class="listado5"><font class="tituloListado">Buscar Persona: </font></td>
	<td width="50%" class="listado5" valign="middle">
	    <table>
 	      <tr>
		<td width="30%"><span class="listado5">Tipo de Usuario</span></td>
		<td width="70%"> 
		    <select name='buscar_tipo' id='buscar_tipo' class='select' onChange='buscar_ciudadano()'>
			<option value="1" <?if ($buscar_tipo==1) echo "selected"?>>Funcionario P&uacute;blico</option>
			<option value="2" <?if ($buscar_tipo==2) echo "selected"?>>Ciudadano</option>
		    </select>
 	      	</td>
	      </tr>
 	      <tr>
		<td><span class="listado5">Nombre / C.I.</span> </td>
		<td><input type=text name="buscar_nom" value="<?=$buscar_nom?>" class="tex_area"></td>
	      </tr>
 	      <tr>
		<td><span class="listado5"><?=$_SESSION["descCargo"] ?> </span> </td>
		<td><input type=text name="buscar_car" value="<?=$buscar_car?>" class="tex_area"></td>
 	      </tr>
 	      <tr name="tr_institucion" id="tr_institucion" <?if ($buscar_tipo==2) echo "style='display:none'"?>>
		<td><span class="listado5"><?=$_SESSION["descEmpresa"] ?></span></td><td> 
<?		//$_SESSION["inst_codi"]
		$sql = "select distinct inst_nombre, inst_codi from institucion where inst_estado=1 order by 1";

		$rs=$db->conn->query($sql);
		if($rs) 
		    if($buscar_inst!=0)
			print $rs->GetMenu2("buscar_inst", $buscar_inst, "0:&lt;&lt; Seleccione &gt;&gt;", false,"","id='buscar_inst' class='select'" );
		    else
			print $rs->GetMenu2("buscar_inst", $_SESSION["inst_codi"], "0:&lt;&lt; Seleccione &gt;&gt;", false,"","id='buscar_inst' class='select'" );
?>			
 	      </td></tr>
	    </table>
	</td>
	<td width="20%" align="center" class="listado5" > 
		<input type="button" name="btn_buscar" value="Buscar" class="botones" onClick="document.formu1.lista_usr.value='0'; submit();">
	</td>
</tr>
    <tr>
	<td class="listado5"><font class="tituloListado">Listas de env&iacute;o: </font></td>
	<td class="listado5" valign="middle">
	    <table>
 	      <tr>
		<td><span class="listado5">Nombre de la lista</span></td><td> 
<?
		$sql="select lista_nombre, lista_codi from lista where (usua_codi=0 and inst_codi=".$_SESSION["inst_codi"].") or usua_codi=".$_SESSION["usua_codi"]." and lista_estado = 1 order by 1 asc";
		$rs=$db->conn->query($sql);
		if($rs) print $rs->GetMenu2("lista_usr", $lista_usr, "0:&lt;&lt; Seleccione &gt;&gt;", false,"","class='select' onChange='submit()'" );
?>
 	      </td></tr>
	    </table>
	</td>
	<td align="center" class="listado5" >
	    <?if ($lista_usr!="0") {?>
	    <table width="70%">
		<tr><td width="50%" align="center"><font size=1><a href="#" onClick="pasar_lista('1');"class=vinculos >Para</a></font></td>
		    <td width="50%" align="center"><font size=1><a href="#" onClick="pasar_lista('3');" class=vinculos>Copia</a></font></td></tr>
	    </table>
	    <? } ?>
	</td>
    </tr>
</table>
<br/>
<table class=borde_tab width="100%" cellpadding="0" cellspacing="4">
    <tr class=listado2>	<td colspan="10" CLASS="titulos5">
	<center><?if ($lista_usr=="0") echo "Resultado de la Búsqueda"; else echo "Personas de la Lista";?></center>
    </td></tr>
    <tr class="grisCCCCCC" align="center"> 
	<td width="15%" CLASS="titulos5" >Nombres</td>
	<td width="8%" CLASS="titulos5" >T&iacute;tulo</td>
	<td width="15%" CLASS="titulos5" ><?=($_SESSION["descCargo"])?></td>
	<td width="15%" CLASS="titulos5"><?=($_SESSION["descEmpresa"])?></td>
	<td width="8%" CLASS="titulos5" >email</td>
	<?if ($lista_usr=="0") {?><td colspan="3" CLASS="titulos5" >Colocar como </td><? } ?>
    </tr> 
<?
$buscar_nom = trim(strtoupper($buscar_nom));
$buscar_car = trim(strtoupper($buscar_car));
$sql="";
if (($buscar_nom!="" or $buscar_car!="" or $buscar_inst!="0") and $lista_usr=="0") {
    $usr_tipo = ", (CASE WHEN u.inst_codi=0 THEN 3 ELSE (CASE WHEN u.inst_codi=".$_SESSION["inst_codi"]." THEN 1 ELSE 2 END) END) as \"usua_tipo\" ";
    $sql = "select * $usr_tipo from usuario u where usua_esta<>0 ";
    $sql .= ' and ' . buscar_nombre_cedula($buscar_nom);
    $sql .= ' and ' . buscar_cadena($buscar_car, "usua_cargo");
    $sql .= " and tipo_usuario=$buscar_tipo ";

    if ($buscar_inst != "0" && $buscar_tipo == 1) {
        $sql .= " and inst_codi=$buscar_inst";
    }

    $sql .= " and upper(usua_nombre) not like '%ADMIN%'";
    $sql .= " order by usua_nombre asc";
//echo $sql;
}
if ($lista_usr!="0") {
    $sql = "select u.* from usuario u, lista_usuarios as l
	    where u.usua_codi=l.usua_codi and lista_codi=$lista_usr and upper(usua_nombre) not like '%ADMIN%' order by usua_nombre asc";
}
//echo 'SQL: >' . $sql . "<";
if ($sql!="") {
  	$rs=$db->query($sql); 
	$i=0;
	if ($rs->EOF) {
	    if ($lista_usr=="0")
	 	echo "<tr><td colspan=6><center><span class='titulosError'>No se encontraron Usuarios con ese nombre</span></center></td></tr>";
	    else
	 	echo "<tr><td colspan=6><center><span class='titulosError'>La lista se encuentra vac&iacute;a</span></center></td></tr>";
	}

	while(!$rs->EOF)
	{	
	    $codigo = trim($rs->fields["USUA_CODI"]);
	    $tipous = trim($rs->fields["USUA_TIPO"]);
?>
	    <tr onmouseover="this.style.background='#68a0c6', this.style.color='white'" onmouseout="this.style.background='white', this.style.color='black'">
		<td ><font size=1><?=substr($rs->fields["USUA_NOMBRE"],0,120) ?></font></td>
		<td ><font size=1><?=substr($rs->fields["USUA_TITULO"],0,70) ?></font></td>
		<td ><font size=1><?=$rs->fields["USUA_CARGO"] ?> </font></td>
		<td ><font size=1><?=$rs->fields["INST_NOMBRE"] ?></font></td>
		<td ><font size=1><?=$rs->fields["USUA_EMAIL"] ?></font></td>
		<?if ($lista_usr=="0") {?>
		    <td width="6%" align="center" valign="top" ><font size=1>
			<?/* if ($ent!=2 or $tipous==1)*/ echo "<a href='#' onClick=\"pasar('$i','1','S')\"; class=\"grid\" >Para</a>" ?></font>
		    </td>
		    <td width="6%" align="center" valign="top" ><font size=1>
			<? if ($ent!=1 or $tipous==1) echo "<a href='#' onClick=\"pasar('$i','2','S');\" class=\"grid\"  >De</a>" ?></font>
		    </td>
		    <td width="7%" align="center" valign="top" ><font size=1>
			<a href="#" class="grid"  onClick="pasar('<?=$i?>','3','S');" >Copia</a></font>
		    </td>
		<? } ?>
	    </tr>
		<script>
			documento[<?=$i?>]= "<?=$codigo?>";
		</script>
  <?
	    $i++;
	    $rs->MoveNext();
	}
}
	?>
</table>
<br>

<table class=borde_tab width="100%" cellpadding="0" cellspacing="4">
    <tr class=listado2>
	<TD colspan="10" CLASS="titulos5">
	<center>Datos a colocar en el <?=($_SESSION["descRadicado"])?></center>
	</TD>
    </tr>
    <tr align="center" > 
	<td width='12%' CLASS=titulos5></td>
	<td width='20%' CLASS=titulos5 >Nombre</td>
	<td width='20%' CLASS=titulos5 >Titulo</td>
	<td width='20%' CLASS=titulos5 ><?=($_SESSION["descCargo"])?></td>
	<td width='20%' CLASS=titulos5 ><?=($_SESSION["descEmpresa"])?></td>
        <td width='8%'  CLASS=titulos5>Acci&oacute;n</td>
    </tr>

<input type="hidden" name="documento_us1" value="<?=$documento_us1?>" >
<input type="hidden" name="documento_us2" value="<?=$documento_us2?>" >
<input type="hidden" name="concopiaa" value="<?=$concopiaa?>">
<?
    $flag=0;
    for($j=0;$j<3;$j++) {
      	if ($j==0) { 	$cca = explode("-",$documento_us1);	$nom="Para";	$tip="D";	}
      	if ($j==1) { 	$cca = explode("-",$documento_us2);	$nom="De";	$tip="R";	}
      	if ($j==2) { 	$cca = explode("-",$concopiaa);		$nom="Copia a";		$tip="C";	} 
	for($i=0;$i<=count($cca)+1;$i++)
	{
	    $tmp = $cca[$i];
	    if (trim($tmp)!=""){
		$usr = ObtenerDatosUsuario(trim($tmp),$db);
		    $boton="<a class=vinculos href=javascript:borrarCCA(".$usr["usua_codi"].",'$tip')>Borrar</a>";
		echo "<tr onmouseover=\"this.style.background='#68a0c6', this.style.color='white'\" onmouseout=\"this.style.background='white', this.style.color='black'\"><td><font size=1>".$nom."</font></td>
		  <td><font size=1>".$usr["nombre"]."</font></td>
		  <td><font size=1>".$usr["titulo"]."</font></td>
		  <td><font size=1>".$usr["cargo"]."</font></td>
		  <td><font size=1>".$usr["institucion"]."</font></td>
		  <td><font size=1><center>".$boton."</center></font></td>
	        </tr>";
		$nom = "";
		if($j==0 && $usr["inst_codi"]!=$_SESSION["inst_codi"])
			$flag=1;
	    }
	}
    }
    echo "<input type='hidden' name='flag_inst' id='flag_inst' value='$flag'>";
?>
</table>


<br/>
<table  width=100% border="0" align="center" name='tbl_botones' id='tbl_botones' cellspacing="1" cellpadding="4">
    <tr>
	<? if ($_SESSION["usua_perm_ciudadano"]==1) { ?>
	    <td id="td_btn_ciudadano" <?if ($buscar_tipo!="2") echo "style='display:none'"?> ><center><input type='button' value="Crear Ciudadano" class="botones_largo" onclick='crear_ciudadano()'></center></td>
	<? } ?>
	<td><center><input type='button' value='Aceptar' class="botones" onclick='pasar_datos()'></center></td>
	<td><center><input type='button' value='Regresar' class="botones" onclick='window.close()'></center></td>
    </tr>
</table>
</form>

</body>
</html>
