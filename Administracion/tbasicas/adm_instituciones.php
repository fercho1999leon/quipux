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
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

session_start();
if($_SESSION["usua_admin_sistema"]!=1) die("");
include_once "$ruta_raiz/rec_session.php";
include_once "../usuarios_dependencias/area_ajax_grabar.php";
/*include_once($ruta_raiz.'/config.php'); 			// incluir configuracion.
include_once($ruta_raiz."/include/db/ConnectionHandler.php");

$db = new ConnectionHandler("$ruta_raiz");
$db->conn->SetFetchMode(ADODB_FETCH_ASSOC);
*/
$inst_estado=0;
if (!isset($slc_institucion) and $_SESSION["admin_institucion"]==1) $slc_institucion = 0;
if (!isset($slc_institucion)) $slc_institucion = $_SESSION["inst_codi"];
$error = "";
if (isset($_POST['btn_accion'])) {
    $record = array();
    if ($_POST['btn_accion'] == 'Aceptar') {
	$db->conn->BeginTrans();
	if ($slc_institucion==0) 
	    $txt_id_inst = $db->conn->nextId('sec_institucion');
	else
	    $txt_id_inst = $slc_institucion;
	$record['INST_CODI'] = $txt_id_inst;
	$record['INST_RUC'] = $db->conn->qstr(limpiar_sql($_POST['txtRuc']));
	$record['INST_NOMBRE'] = $db->conn->qstr(limpiar_sql($_POST['txtNombre']));
	$record['INST_SIGLA'] = $db->conn->qstr(limpiar_sql(strtoupper($_POST['txtSigla'])));
	$record['INST_ESTADO'] = "1";
        if($_POST['chkCoordinador'] == '') $_POST['chkCoordinador'] = '0';
        $record['INST_COORDINADOR'] = $db->conn->qstr(limpiar_sql($_POST['chkCoordinador']));
	$record['INST_TELEFONO'] = $db->conn->qstr(limpiar_sql(strtoupper($_POST['txtTelefono'])));
        $record['INST_EMAIL'] = $db->conn->qstr(limpiar_sql($_POST['txtCorreo']));//no se guarda con mayusculas
        $record['INST_DESPEDIDA_OFI'] = $db->conn->qstr(limpiar_sql(strtoupper($_POST['txtDespedidaOfi'])));
        $arch_logo = $_FILES["arch_logo"]['tmp_name'];
	if (trim($arch_logo!="")) {
	    $path_arch = "/bodega/logos/$txt_id_inst.".limpiar_sql($_POST['txtExt']);
	    $record['INST_LOGO'] = $db->conn->qstr($path_arch);
	    $ok2 = move_uploaded_file($arch_logo,$ruta_raiz.$path_arch);
	    if(!$ok2) $error = "Error al subir el Logo de la Instituci&oacute;n";
	} else
	    $ok2 = true;

	$ok1 = $db->conn->Replace("INSTITUCION", $record, "INST_CODI", false,false,true,false);
        // Para eliminar los registros donde la institucion esta como ministerio coordinador de otras
        if($ok1 and $_POST['elimCoor']=='S')
        {
            $sqlEliminarCoor = 'delete from institucion_coordinador where inst_codi_coor = '.$txt_id_inst;
            $db->conn->query($sqlEliminarCoor);
        }
	if(!$ok1) $error = "Error al crear la Instituci&oacute;n";
	if($ok1==2){

       // Creamos el área padre de la institución
        $txt_id_depe = $db->conn->nextId('sec_dependencia');
        unset($record);
        $record['DEPE_CODI']        = $txt_id_depe;
        $record['DEPE_NOMB']        = $db->conn->qstr(limpiar_sql($_POST['txtNombre']));
        $record['DEPE_CODI_PADRE']  = $txt_id_depe;
        $record['DEP_SIGLA']        = $db->conn->qstr(strtoupper(limpiar_sql($_POST['txtSigla'])));
        $record['DEP_CENTRAL']      = $txt_id_depe;
        $record['DEPE_ESTADO']      = "1";
        $record['INST_CODI']        = $txt_id_inst;
        $record['INST_ADSCRITA']    = $txt_id_inst;
        $record['DEPE_PIE1']        = "1";
        $ok3 = $db->conn->Replace("DEPENDENCIA", $record, "", false,false,true,false);

       	$dependencia = str_pad($txt_id_depe,6,"0", STR_PAD_LEFT);
	    mkdir($ruta_raiz."/bodega/".date('Y')."/".$dependencia."/docs",0777,true);


	} else
	    $ok3 = true;

	if ($ok1 && $ok2 && $ok3) {
	    $db->conn->CommitTrans();
	    $slc_institucion = $txt_id_inst;
	    $error = "Los cambios se realizaron exitosamente";
	} else
	    $db->conn->RollbackTrans();
    }
}

if ($slc_institucion != 0) {
    $sql = "select * from institucion where inst_codi=$slc_institucion";
    $rs = $db->conn->query($sql);
    $txtRuc = $rs->fields['INST_RUC'];
    $txtNombre = $rs->fields['INST_NOMBRE'];
    $txtSigla = $rs->fields['INST_SIGLA'];
    $txtLogo = $rs->fields['INST_LOGO'];
    $chkCoordinador = $rs->fields['INST_COORDINADOR'];
    $txtTelefono = $rs->fields['INST_TELEFONO'];
    $txtCorreo = $rs->fields['INST_EMAIL'];
    $txtDespedidaOfi = $rs->fields['INST_DESPEDIDA_OFI'];
    $inst_estado = $rs->fields['INST_ESTADO'];
//    $txtPie1 = $rs->fields['INST_PIE1'];
//    $txtPie2 = $rs->fields['INST_PIE2'];
//    $txtPie3 = $rs->fields['INST_PIE3'];
    //Consultar si es Ministerio coordinador de otros Ministerios
    $sqlCoordinador = 'select * from institucion_coordinador where inst_codi_coor = '.$slc_institucion;
    $rsCoordinador = $db->conn->query($sqlCoordinador);
    if(!$rsCoordinador->EOF){
        $siCoor = 'S';
    }
    else
        $siCoor = 'N';
}

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

$read = "";
if($_SESSION["admin_institucion"]==1 and $_SESSION["usua_codi"]!=0 and $slc_institucion ==$acceso_ciudadano_inst)
        $read = "readonly";

//Variable para mostrar check de ministerio coordinador
$mostrarCoor = 'N';
//verificar si tiene permisos para modificar institucion
$sql="select depe_codi as padre from dependencia where inst_codi = ".$_SESSION['inst_codi']."
and depe_codi = depe_codi_padre";

$rsDepPadre=$db->conn->query($sql);
$depeCodiPadre = $rsDepPadre->fields['PADRE'];//obtengo la dependencia padre de la institucion
//verifica si el usr administra la dependencia padre

$ok=obtenerCodigos($_SESSION['usua_codi'],$depeCodiPadre,$db,1);

//verifica si administra dependencias
$depe_codi_admin = obtenerAreasAdmin($_SESSION["usua_codi"],$_SESSION["inst_codi"],$_SESSION["usua_admin_sistema"],$db);
$gd=1;

if ($depe_codi_admin!=0){//si administra dependencias
    if ($ok==1)//si administra la dependencia padre
        $gd=1;//significa que puede guardar cambios en esta pantalla de instituciones
    else
        $gd=0;
}   

?>

<!-- Para utilizacion de Ajax -->
<?php require_once "$ruta_raiz/js/ajax.js"; ?>
<script language="JavaScript" src="<?=$ruta_raiz?>/js/prototype.js" type ="text/javascript"></script>
<script language="JavaScript" src="<?=$ruta_raiz?>/js/general1.js"  type="text/javascript"></script>
<script language="JavaScript" src="<?=$ruta_raiz?>/js/formchek.js"></script>
<script language="JavaScript">

function ltrim(s) {
   return s.replace(/^\s+/, "");
}

function ValidarInformacion(accion)
{	
	if (ltrim(document.formSeleccion.txtRuc.value) == '')
	{
		alert('Ingrese el Ruc de la Institución');
		document.formSeleccion.txtRuc.focus();
		return false;
	}
	if (ltrim(document.formSeleccion.txtNombre.value) == '')
	{
		alert('Ingrese el Nombre de la Institución');
		document.formSeleccion.txtNombre.focus();
		return false;
	}
	if (ltrim(document.formSeleccion.txtSigla.value) == '')
	{
		alert('Ingrese las Siglas para la Institución');
		document.formSeleccion.txtSigla.focus();
		return false;
	}
        if (ltrim(document.formSeleccion.esEmail.value)==0){
            alert("Correo no Existe o tiene formato no valido")
            return false;
            }
	return true;
}
 function e(condicion, mensaje) {
        msg = (condicion) ? msg + mensaje + ' \n' : msg;
    }
function ver_listado()
{
//    if (document.getElementById('slc_institucion'))
//      inst=document.getElementById('slc_institucion').value;
//  else
//      inst=<?=$_SESSION['inst_codi']?>;
//	window.open('listados.php?var='+inst,'','scrollbars=yes,menubar=no,height=600,width=800,resizable=yes,toolbar=no,location=no,status=no');
	window.open('lista_jquery.php?tipo_reporte=instituciones','','scrollbars=yes,menubar=no,height=600,width=800,resizable=yes,toolbar=no,location=no,status=no');
}

function valida_extension()
{
    cadena=document.getElementById('arch_logo').value;
    cadena= cadena.substr(-3).toLowerCase();
    if (cadena=='png' || cadena=='jpg' || cadena=='gif') {
	document.getElementById('txtExt').value = cadena;
	return;
    } else {
	alert ("Solo se permite subir archivos con extensiones gif, jpg y png.");
	document.getElementById('arch_logo').value = '';
	document.getElementById('txtExt').value = '';
	return;
    }
}

function coordinador(){
    if(document.formSeleccion.esCoor.value == 'S' && document.formSeleccion.chkCoordinador.checked == false)
    {
        if(confirm("La institución es Ministerio Coordinador de otra institucion, desea desactivarlo?")){
            document.formSeleccion.chkCoordinador.value = '0';
            document.formSeleccion.elimCoor.value = 'S';
        }
        else
        {
            document.formSeleccion.chkCoordinador.checked = true;
            document.formSeleccion.chkCoordinador.value = '1';
            document.formSeleccion.elimCoor.value = 'N';
        }

    }
    else
    {
        if(document.formSeleccion.chkCoordinador.checked == true)
            document.formSeleccion.chkCoordinador.value = '1';
        else
            document.formSeleccion.chkCoordinador.value = '0';
    }
}

function divCoordinadora(instCodi){
    var nomDiv = "inst_coor";
    var datos = "";
    if(instCodi=="")
        datos = instCodi;
    else{
        if (document.formSeleccion.slc_institucion)
            datos = document.formSeleccion.slc_institucion.value;
        else
            datos=<?=$_SESSION['inst_codi']?>;
    }
    nuevoAjax(nomDiv, 'GET', 'admInstitucionCoordinador_ajax.php', 'slc_institucion=' + datos);
}
function pulsar(e) {
    tecla = (document.all) ? e.keyCode : e.which;
    if (tecla==8) return true;
    patron =/\s/;
    te = String.fromCharCode(tecla);
    return !patron.test(te);
}
function desactivar(){
    if (document.getElementById("slc_institucion")){        
        id_institucion = document.getElementById("slc_institucion").value;
        if (id_institucion>0)
            window.location='des_institucion.php?id_institucion='+id_institucion;
        else
            alert("Seleccione la Institución.");
    }else{
        alert("No tiene permisos para realizar esta acción.");
    }
}
</script>
<body>
<center>
<form name="formSeleccion" id="formSeleccion" method='post' ENCTYPE='multipart/form-data' action="<?= $_SERVER['PHP_SELF']?>">
    <input type="hidden" name="esCoor" id="esCoor" value="<?=$siCoor?>">
    <input type="hidden" name="elimCoor" id="elimCoor" value="N">
    <div id="div_validar_email">
    
    </div>
  <table width="100%"class="borde_tab">
    <tr>
      	<td colspan="2" height="40" align="center" class="titulos4"><b>Administrador de Instituciones</b></td>
    </tr>
    
<?php 

if ($_SESSION["admin_institucion"]==1) { 
    $mostrarCoor = 'S';
    ?>
    <tr>
	<td width="30%" align="left" class="titulos2"><b>Seleccione Instituci&oacute;n (si desea modificarla)</b></td>
	<td width="70%" class="listado2">
<?php
	    $sql = "select inst_nombre, inst_codi from institucion where inst_codi>0 order by inst_nombre";
	    $rs=$db->conn->query($sql);
	    echo $rs->GetMenu2("slc_institucion", $slc_institucion, "0:&lt;&lt Nueva Institución &gt;&gt;", false,"","id='slc_institucion' class='select' Onchange='submit()'");
	    $rs->Move(0);
?>
	</td>
    </tr>
<?php } else $slc_institucion = $_SESSION["inst_codi"]; ?>
  </table>

  <br/>
  <table width="100%"class="borde_tab">
    <tr>
        <td align="left" class="titulos2"><b>Nombre:</b></td>
        <td colspan="3" class="listado2">
	<?php if ($txtNombre=='') { ?>
	    <input  name="txtNombre" id="txtNombre" type="text" size="95" maxlength="100" value="<?=$txtNombre?>" >
	<?php } else {
                if ($_SESSION["admin_institucion"]==0) { ?>
                    <input  name="txtNombre" id="txtNombre" type="text" size="95" maxlength="100" value="<?=$txtNombre?>" readonly="true">
                <?php } else{ ?>
                    <input  name="txtNombre" id="txtNombre" type="text" size="95" maxlength="100" value="<?=$txtNombre?>" <?php echo $read; ?>>
                <?php } 
                } ?>
    </td>
    </tr>
    <tr>
	<td align="left" class="titulos2" width="20%"><b>Ruc:</b></td>
        <td class="listado2" width="30%">
	    <input  name="txtRuc" id="txtRuc" type="text" size="13" maxlength="13" value="<?=$txtRuc?>" <?php echo $read; ?>>
        </td>
        <td class="titulos2" width="20%"><b>Sigla:</b></td>
        <td class="listado2" width="30%"><input name="txtSigla" id="txtSigla" type="text" size="20" maxlength="10" value="<?=$txtSigla ?>" onkeypress = "return pulsar(event)" <?php echo $read; ?>></td>
    </tr>
    <tr>
        <td align="left" class="titulos2" width="20%"><b>Correo:</b></td>
        <td class="listado2" width="30%">            
	    <input  name="txtCorreo" id="txtCorreo" type="text" size="35" maxlength="50" value="<?=$txtCorreo?>" onChange=" nuevoAjax('div_validar_email', 'POST', 'validar_email.php', 'txt_email='+this.value)" <?php echo $read; ?>>
        </td>        
        <td align="left" class="titulos2" width="20%"><b>Tel&eacute;fono:</b></td>
         <td class="listado2" width="30%" <?php if($mostrarCoor == 'N') echo 'colspan="3" ';?> >
	    <input  name="txtTelefono" id="txtTelefono" type="text" size="13" maxlength="13" value="<?=$txtTelefono?>" <?php echo $read; ?>>
        </td>
        
    </tr>
    <tr>
        
        <?php if($mostrarCoor == 'S') { ?>
        <td class="titulos2" width="20%"><b>Es Ministerio Coordinador?</b></td>
        <td class="listado2" width="30%" colspan="3"><input type="checkbox" name="chkCoordinador" id="chkCoordinador" value="<?=$chkCoordinador?>" <? if($chkCoordinador=='1') echo 'checked'?> onclick="coordinador();" <?php echo $read; ?>></td>
        <?php } ?>
    </tr>
    <tr>
        <td align="left" class="titulos2" width="20%"><b>Frase despedida para oficios:</b></td>
        <td class="listado2" colspan="3" >
	    <input  name="txtDespedidaOfi" id="txtDespedidaOfi" type="text" size="35" maxlength="35" value="<?=$txtDespedidaOfi?>" <?php echo $read; ?>>
        </td>
    </tr>
    <tr>
	<td class="titulos2"><b>Logo:</b></td>
	<td width="35%" class="listado2" colspan="2">
	    <input name="arch_logo" type="file" class="tex_area" onChange="valida_extension();" id="arch_logo" size="0" <?php echo $read; ?>>
	    <input type="hidden" name="txtExt" value="" id="txtExt">
	</td>
        <td width="35%" class="listado2" colspan="2">
	    <? if (trim($txtLogo)!="") {?>
	    	<center><img src="<?=$ruta_raiz.$txtLogo?>" width="180" height="60" border=0 alt="Logo Instituci&oacute;n"></center>
	    <? } ?>
	</td>
    </tr>
<? if ($_SESSION["usua_codi"]==0) { ?>
    <tr style="display: none;">
        <!--td class="titulos2"><b>Activar / Desactivar la Instituci&oacute;n:</b></td>
	<td width="35%" class="listado2">
	    <input type="checkbox" name="chkCoordinador" id="chkCoordinador" value="<?=$chkCoordinador?>" <? if($chkCoordinador=='1') echo 'checked'?> onclick="coordinador();">
	</td-->
	<td class="titulos2"><b>Fusionar dos Instituciones:</b></td>
        <td class="listado2" colspan="3">
            <input  name="btn_accion" type="button" class="botones_largo" value="Fusionar Instituciones" title="Mueve &aacute;reas, usuarios y documentos de una instituci&oacute;n a otra" onClick="location='instituciones_fusionar.php'"/>
	</td>
    </tr>
<? } ?>
    
  </table>
	
<?php
    if ($error != "") 
	echo "<br/><table width='100%'><tr><td align='center'><font color='red' face='Arial' size='3'>$error</font></td></tr></table>";
?>

  <br/>
  <table width="100%"  cellpadding="0" cellspacing="0" class="borde_tab">
    <tr>
    	<td width="20%" align="center">
	    <input name="btn_accion" type="button" class="botones_largo" value="Listado de Instituciones" title="Lista todas las instituciones que estan creadas en sistema" onClick="ver_listado();"/>
    	</td>
	<td  width="20%" align="center">
	    <input  name="btn_accion" type="button" class="botones" value="Limpiar"  title="Borrar los datos del formulario" onClick="location='<?= $_SERVER['PHP_SELF']?>'"/>
        </td> 
	<td  width="20%" align="center">
            <?php           
            if ($_SESSION['usua_codi']==0){//
	    ?><input name="btn_accion" type="submit" class="botones" value="Aceptar" title="Almacena los cambios realizados" onClick="return ValidarInformacion();"/>
            <?php }else{
                if ($gd==1){
                ?>
            <input name="btn_accion" type="submit" class="botones" value="Aceptar" title="Almacena los cambios realizados" onClick="return ValidarInformacion();"/>
            <?php }
            }?>
	</td>
	<td  width="20%" align="center">
	    <input  name="btn_accion" type="button" class="botones" value="Regresar" title="Regresa a la página anterior, sin guardar los cambios" onClick="location='../formAdministracion.php'"/>
	</td>
        <?php if ($inst_estado==1){?>
        <td  width="20%" align="center">
	    <input  name="btn_accion" type="button" class="botones" value="Desactivar" title="Desactivar Institucion" onClick="desactivar()"/>
	</td>
        <?php }?>
    </tr>
    
  </table>
  <br>
  <?php if($txtSigla=='0'){ ?>
  <table width="80%" class="borde_tab">
    <tr>
        <td colspan="2" height="40" align="center" class="titulos4"><b>Ministerio Coordinador</b></td>
    </tr>
  </table>
  <?php } 
 
    if ($_SESSION["admin_institucion"]==1 and $gd==1)
        //include_once 'admInstitucionCoordinador_ajax.php';
        echo '<div id="inst_coor"></div>';
  ?>

</form>
</center>
</body>
</html>
    <?php //if ($_SESSION["admin_institucion"]==1) {
    if($_SESSION["admin_institucion"]==1 and $_SESSION["usua_codi"]!=0 and $slc_institucion == $acceso_ciudadano_inst)
        echo "";
    else{    
    ?>
    <script type='text/JavaScript'>
        divCoordinadora('<?=$slc_institucion?>');
    </script>
    <?php } ?>
<script type='text/JavaScript'>

    // Funcionalidad para añadir Ministerio coordinador
    function institucionCoordinadora(id_instCoor, instCodi, accion) {
        var i = 0;
        var data;
        var clazz = "institucion";
        var action = "";
        if(accion == 1)
        {
            action = "institucionCoordinadora";
            //alert(id_jefe);
            if (document.getElementById("institucion").selectedIndex  == -1 ){
                alert("No ha seleccionando una Institución");
            }
            if ( document.getElementById("institucion").selectedIndex >= 0 ){
                for (i=0;i< document.getElementById("institucion").length;i++) {
                    if ( document.getElementById('institucion').options[i].selected ) {
                        id_instCoor = document.getElementById('institucion').options[i].value;
                        data=id_instCoor+','+instCodi;
                        ajax_call_coordinador ( data, clazz, action, ver_datos );
                    }
                }
            }
        }
        else if (accion == 2)
        {
            action = "elim_institucionCoordinadora";
            data=id_instCoor+','+instCodi;
            //alert(data);
            ajax_call_coordinador ( data, clazz, action, ver_datos );
        }
    }

    function ver_datos(result,resp,instCodi){
        if (resp!="")  	{
            alert(resp);// si hay errores se mostrar� el alert
        }
        else {
            divCoordinadora('<?=instCodi?>');
        }
    }
</script>