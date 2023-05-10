<?
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
/*****************************************************************************************
**											**
*****************************************************************************************/
$ruta_raiz = "../..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

session_start();
$accion_btn_aceptar = "setTime(ValidarInformacion());";
//Cambio para desadocs VJ
/*if($_SESSION["usua_admin_sistema"]!=1 and $_SESSION["usua_perm_ciudadano"]!=1) {
    echo html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
    die("");
}*/
include_once "$ruta_raiz/rec_session.php";
$codigo1=$_REQUEST['codigo'];
$cod_impresion=$_REQUEST['cod_impresion'];

    $read = "";
    if ($accion == 1) {	//Nuevo
        $accionForm = "adm_usuario_ext_confirmar.php?cerrar=$cerrar&accion=$accion";
        $tituloForm = "Registrar Datos de Ciudadano";
        $ciu_estado = "checked";
    } else {
        if (!isset($recargar)) {
            $sql = "select * from ciudadano where ciu_codigo=$ciu_codigo";
            //echo $sql;
            $rs = $db->conn->query($sql);
            $ciu_cedula 	= $rs->fields["CIU_CEDULA"];
            $ciu_documento 	= $rs->fields["CIU_DOCUMENTO"];
            $ciu_nombre 	= $rs->fields["CIU_NOMBRE"];
            $ciu_apellido 	= $rs->fields["CIU_APELLIDO"];
            $ciu_titulo         = $rs->fields["CIU_TITULO"];
            $ciu_abr_titulo	= $rs->fields["CIU_ABR_TITULO"];
            $ciu_empresa 	= $rs->fields["CIU_EMPRESA"];
            $ciu_cargo          = $rs->fields["CIU_CARGO"];
            $ciu_direccion 	= $rs->fields["CIU_DIRECCION"];
            $ciu_email          = $rs->fields["CIU_EMAIL"];
            $ciu_telefono 	= $rs->fields["CIU_TELEFONO"];
            $ciu_nuevo   	= $rs->fields["CIU_NUEVO"];
            $ciu_estado   	= $rs->fields["CIU_ESTADO"];
            $ciu_desactiva      = ($rs->fields["CIU_ESTADO"] == 0) ? "" : "checked";
            $usua_codi_actualiza = $rs->fields["USUA_CODI_ACTUALIZA"];
            $ciu_fecha_actualiza = $rs->fields["CIU_FECHA_ACTUALIZA"];
            $ciu_obs_actualiza = $rs->fields["CIU_OBS_ACTUALIZA"];
            $ciu_ciudad        = $rs->fields["CIUDAD_CODI"];

            //Si el usuario esta inactivo por defecto se mostraran las cajas de texto deshabilitadas
            if($ciu_estado==0)
            {
                $read_cedula = "readonly";
                $deshabilitar_campos = "disabled";
            }
            else
            {
                $read_cedula = "";
                $deshabilitar_campos = "";
            }
        }
    }
    //echo $accion;
    if ($accion == 2) { //Editar
        $accionForm = "grabar_usuario_ext.php?cerrar=$cerrar&accion=$accion&codigo1=$codigo1&cod_impresion=$cod_impresion";
        
        $tituloForm = "Modificar Datos de Ciudadano";
    }
    if ($accion == 3) {	//Consultar
        $accionForm = "";
    	$read = "readonly";
        $tituloForm = "Consultar Datos Ciudadano";
    }

if(trim($usua_codi_actualiza)!='')
{
    include_once "$ruta_raiz/obtenerdatos.php";
    //Obtener datos del suncionario que actualizo por última ves al ciudadano
    $usua_actualiza = ObtenerDatosUsuario($usua_codi_actualiza, $db);

    $usua_nombre_act = $usua_actualiza['usua_nombre'].' '.$usua_actualiza['usua_apellido'];
    $usua_institucion_act = $usua_actualiza['institucion'];
    $usua_email_act = $usua_actualiza['email'];
}

require_once "$ruta_raiz/js/ajax.js";
include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();
?>

<script type="text/javascript" language="JavaScript" src="<?=$ruta_raiz?>/js/formchek.js"></script>
<script type="text/javascript" language="JavaScript" src="<?=$ruta_raiz?>/js/validar_cedula.js"></script>
<script type="text/javascript" language="javascript">
function ltrim(s) {
   return s.replace(/^\s+/, "");
}

//var marcado = 0;
//function Obtener_val(formulario,estado,accion){
//    if(document.forms[0].ciu_desactiva.checked) {
//        marcado=formulario.value
//        document.getElementById("ciu_desactiva").checked=marcado;
//        if (marcado==1 && estado==0 && accion==2 ){
//            document.getElementById('btn_aceptar').style.visibility="visible";
//            //document.getElementById('ciu_cedula').value=document.getElementById('caja').value
//        }
//        //document.getElementById('btn_aceptar').style.visibility="hidden";
//    }
//    else{
//        if (estado==0 && accion==2){
//        document.getElementById('btn_aceptar').style.visibility="hidden";
//        document.getElementById('ciu_cedula').disabled=true;
//        document.getElementById('ciu_sincedula').disabled=true;
//        }
//    }
//}

function desactivarCiudadano(){
    if(document.forms[0].ciu_desactiva.checked) {
        //Para no desactivar el usario o volver a activar
        document.forms[0].desactivar.value = '1';
        document.forms[0].ciu_cedula.value = document.forms[0].ciu_cedula.value.substr(0, 10);
        document.forms[0].ciu_nombre.disabled = false;
        document.forms[0].ciu_apellido.disabled = false;
        document.forms[0].ciu_cedula.readOnly = false;
        document.forms[0].ciu_documento.disabled = false;
        document.forms[0].ciu_titulo.disabled = false;
        document.forms[0].ciu_abr_titulo.disabled = false;
        document.forms[0].ciu_empresa.disabled = false;
        document.forms[0].ciu_cargo.disabled = false;
        document.forms[0].ciu_direccion.disabled = false;
        document.forms[0].ciu_telefono.disabled = false;
        document.forms[0].ciu_email.disabled = false;
        document.forms[0].ciu_ciudad.disabled = false;
        document.forms[0].ciu_sincedula.disabled = false;
        document.forms[0].ciu_password.disabled = false;
    }
    else{
        document.forms[0].desactivar.value = '0';
        document.forms[0].ciu_nombre.disabled = true;
        document.forms[0].ciu_apellido.disabled = true;
        document.forms[0].ciu_cedula.readOnly = true;
        document.forms[0].ciu_documento.disabled = true;
        document.forms[0].ciu_titulo.disabled = true;
        document.forms[0].ciu_abr_titulo.disabled = true;
        document.forms[0].ciu_empresa.disabled = true;
        document.forms[0].ciu_cargo.disabled = true;
        document.forms[0].ciu_direccion.disabled = true;
        document.forms[0].ciu_telefono.disabled = true;
        document.forms[0].ciu_email.disabled = true;
        document.forms[0].ciu_ciudad.disabled = true;
        document.forms[0].ciu_sincedula.disabled = true;
        document.forms[0].ciu_password.disabled = true;
    }
}

function EnableButton(estado,accion){
    if (estado==0 && accion==2)
        document.getElementById('ciu_sincedula').disabled=true;
}


function ValidarInformacion()
{
	
        if(!document.forms[0].ciu_sincedula.checked)
        if (!validarCedula(document.forms[0].ciu_cedula)) {
            alert ('El número de cédula ingresado no es correcto. Por favor intente de nuevo.');
            document.forms[0].ciu_cedula.focus();
	    	return false;
        }
	if(ltrim(document.forms[0].ciu_nombre.value)=='' || ltrim(document.forms[0].ciu_apellido.value)=='')
	{	alert("Los campos de Nombres y Apellidos son obligatorios.");
		return false;
	}
        if (document.forms[0].ciu_ciudad.value==0){
           alert("Seleccione la Ciudad");
           return false;
        }
/*	if (!isEmail(document.forms[0].ciu_email.value,true))
	{	alert("El campo Email no tiene formato correcto.");
		return false;
	}*/

//teya 20110421
//        if(ltrim(document.getElementById("div_validar_email").innerHTML)!= 'OK'){
//            alert("Ingrese un correo electrónico válido.");
//            return false;
//        }
	return true;
}


function ver_cedula()
{
    
    if(document.forms[0].ciu_sincedula.checked){ 
	document.forms[0].ciu_cedula.style.display='none';
        //document.getElementById("img_reg2").style.display = 'none';
        document.getElementById('div_datos_registro_civil').style.display = 'none';
    }
    else{
	document.forms[0].ciu_cedula.style.display='';
        //document.getElementById("img_reg2").style.display = '';
        document.getElementById('div_datos_registro_civil').style.display = '';
    }
}

function cambioMayusculasDoc()
{
	document.frmCrear.ciu_documento.value = document.frmCrear.ciu_documento.value.toUpperCase();
}

function mostrar_div_observacion(accion) {
    if (accion == 1)
        document.getElementById('div_observacion').style.display = '';
    else
        document.getElementById('div_observacion').style.display = 'none';
}

//****
function changeCase_Articulos(frmObj,accion) {
        var index;
        var tmpStr;
        var tmpChar;
        var preString;
        var postString;
        var strlen;
        var obj;

        tmpStr = frmObj.value.toLowerCase();
        strLen = tmpStr.length;
        if (strLen > 0)  {
        for (index = 0; index < strLen; index++)  {
        if (index == 0)  {
        tmpChar = tmpStr.substring(0,1).toUpperCase();
        postString = tmpStr.substring(1,strLen);
        tmpStr = tmpChar + postString;
        }
        else {
        tmpChar = tmpStr.substring(index, index+1);
        if (tmpChar == " " && index < (strLen-1))  {
        tmpChar = tmpStr.substring(index+1, index+2).toUpperCase();
        preString = tmpStr.substring(0, index+1);
        postString = tmpStr.substring(index+2,strLen);
        tmpStr = preString + tmpChar + postString;
                 }
              }
           }
    }
        //frmObj.value=tmpStr;
   //Cambia los artículos a minúsculas
    var arrayori  = ['De' , 'Del', 'La', 'Las', 'Lo','Los','En','Y','E','Ff.Aa.'];
    var frase='';
    var frase1='';

    fragmentoTexto = tmpStr.split(' ');
    frase=fragmentoTexto[0]+' ';


    for(i=1;i<fragmentoTexto.length;i++){
        for(j=0;j<arrayori.length;j++){
            if(fragmentoTexto[i]==arrayori[j]){
                cadena=fragmentoTexto[i];
                parteFrase=cadena.toString().toLowerCase();
                frase1=parteFrase+' ';
            }
        }
        if(frase1=='')
           frase+= fragmentoTexto[i]+' ';
        else
            frase+= frase1;
    frase1='';
   }
   frmObj.value=trim(frase);   
}


function changeCase_Articulos_Direccion(frmObj) {
        var index;
        var tmpStr;
        var tmpChar;
        var preString;
        var postString;
        var strlen;
        var obj;

        tmpStr = frmObj.value.toLowerCase();
        strLen = tmpStr.length;
        if (strLen > 0)  {
        for (index = 0; index < strLen; index++)  {
        if (index == 0)  {
            tmpChar = tmpStr.substring(0,1).toUpperCase();
            postString = tmpStr.substring(1,strLen);
            tmpStr = tmpChar + postString;
        }
        else {
            tmpChar = tmpStr.substring(index, index+1);
            if (tmpChar == " " && index < (strLen-1))  {
                tmpChar = tmpStr.substring(index+1, index+2).toUpperCase();
                preString = tmpStr.substring(0, index+1);
                postString = tmpStr.substring(index+2,strLen);
                tmpStr = preString + tmpChar + postString;
                 }
              }
           }
    }

   
   //Cambia los artículos a minúsculas
    var arrayori  = ['De' , 'Del', 'La', 'Las', 'Lo','En','Y','E','O','Ff.Aa.'];
    var frase='';
    var frase1='';

    fragmentoTexto = tmpStr.split(' ');
    frase=fragmentoTexto[0]+' ';


    for(i=1;i<fragmentoTexto.length;i++){
        for(j=0;j<arrayori.length;j++){
            if(fragmentoTexto[i]==arrayori[j]){
                cadena=fragmentoTexto[i];
                parteFrase=cadena.toString().toLowerCase();
                frase1=parteFrase+' ';
            }
        }
        if(frase1=='')
           frase+= fragmentoTexto[i]+' ';
        else
            frase+= frase1;
    frase1='';
   }
   frmObj.value=trim(frase)
   }

function changeCase_Articulos_CargoCabec(frmObj) {
        var index;
        var tmpStr;
        var tmpChar;
        var preString;
        var postString;
        var strlen;
        var obj;

        tmpStr = frmObj.value.toLowerCase();
        strLen = tmpStr.length;
        if (strLen > 0)  {
        for (index = 0; index < strLen; index++)  {
        if (index == 0)  {
        tmpChar = tmpStr.substring(0,1).toUpperCase();
        postString = tmpStr.substring(1,strLen);
        tmpStr = tmpChar + postString;
        }
        else {
        tmpChar = tmpStr.substring(index, index+1);
        if (tmpChar == " " && index < (strLen-1))  {
        tmpChar = tmpStr.substring(index+1, index+2).toUpperCase();
        preString = tmpStr.substring(0, index+1);
        postString = tmpStr.substring(index+2,strLen);
        tmpStr = preString + tmpChar + postString;
                 }
              }
           }
    }
        //frmObj.value=tmpStr;
   //Cambia los artículos a minúsculas
    var arrayori  = ['De' , 'Del', 'La', 'Las', 'Lo','En','Y','E','O','Ff.Aa.'];
    var frase='';
    var frase1='';

    fragmentoTexto = tmpStr.split(' ');
    frase=fragmentoTexto[0]+' ';


    for(i=1;i<fragmentoTexto.length;i++){
        for(j=0;j<arrayori.length;j++){
            if(fragmentoTexto[i]==arrayori[j]){
                cadena=fragmentoTexto[i];
                parteFrase=cadena.toString().toLowerCase();
                frase1=parteFrase+' ';
            }
        }
        if(frase1=='')
           frase+= fragmentoTexto[i]+' ';
        else
            frase+= frase1;
    frase1='';
   }
   frmObj.value=trim(frase);
   
   }

function changeCase(frmObj) {

        var index;
        var tmpStr;
        var tmpChar;
        var preString;
        var postString;
        var strlen;
        var obj;

        tmpStr = frmObj.value.toLowerCase();
        strLen = tmpStr.length;
        if (strLen > 0)  {
        for (index = 0; index < strLen; index++)  {
        if (index == 0)  {
        tmpChar = tmpStr.substring(0,1).toUpperCase();
        postString = tmpStr.substring(1,strLen);
        tmpStr = tmpChar + postString;
        }
        else {
        tmpChar = tmpStr.substring(index, index+1);
        if (tmpChar == " " && index < (strLen-1))  {
        tmpChar = tmpStr.substring(index+1, index+2).toUpperCase();
        preString = tmpStr.substring(0, index+1);
        postString = tmpStr.substring(index+2,strLen);
        tmpStr = preString + tmpChar + postString;
                 }
              }
           }
        }

   frmObj.value=trim(tmpStr);
   }

    function validar_cambio_cedula() {
        cedula = document.getElementById('ciu_cedula').value;
        if (trim(cedula)!='')
            nuevoAjax('div_datos_registro_civil', 'POST', 'validar_datos_registro_civil.php', 'cedula='+cedula);
            nuevoAjax('div_datos_usuario_multiple', 'POST', 'validar_datos_usuario_multiple.php', 'usr_codigo=<?=$ciu_codigo?>&cedula='+cedula);
    }

    function copiar_datos_registro_civil(campo_rc, campo_usr) {
        try {
            document.getElementById(campo_usr).value = document.getElementById('lbl_datos_rc_'+campo_rc).innerHTML;
        } catch (e) {}
    }
</script>


<body onload="EnableButton('<?=$ciu_estado?>','<?=$accion?>');">
  <form name='frmCrear'  id='frmCrear' action="<?=$accionForm?>" method="post">

    <table width="100%" border="1" align="center" class="t_bordeGris">
  	<tr>
    	    <td class="titulos4">
		<center>
		<p><B><span class=etexto> <?=$tituloForm ?></span></B> </p></center>
	    </td>
	</tr>
    </table>

    <br/>
    <center>
        <div id="div_datos_registro_civil" style="width: 100%;"></div>
        <div id="div_datos_usuario_multiple" style="width: 100%;"></div>
    </center>

    <input type='hidden' name='ciu_codigo' value='<?=$ciu_codigo?>'>
    <table width="100%" rules="all" align="center" class="borde_tab" name="usr_datos" id="usr_datos">
<? if ($accion != 1) { ?>
	<tr>
	    <td class="titulos2"> Usuario: </td>
	    <td class="listado2"><?=$ciu_cedula?></td>
	    <? if ($_SESSION["admin_institucion"]==1) { ?>               
                <td class="titulos2">Ciudadano: 
                <td class="listado2">&nbsp;                
                    <input class="tex_area" type="checkbox" name="ciu_desactiva" id="ciu_desactiva" value="1" <?=$ciu_desactiva?> onclick="desactivarCiudadano();">Ciudadano Activo
                    <input type="hidden" value="1" id="desactivar" name="desactivar">
           </td> 
           <?}else{?>
                <td class="titulos2">&nbsp;</td>
                <td class="listado2">&nbsp;</td>
           <?}?>
	</tr>
<? } ?>
    <tr>
            <!--<div id="div_validar_email" style="display: none;" ><?if(validar_mail($usr_email)) echo "OK";?></div>-->
	    <td class="titulos2" width="15%">* C&eacute;dula/RUC: </td>
	    <td class="listado2" width="35%">
        <?if($codigo1=="ciu_s"){?>
             <!--TextBox de cedula -->
             <input class="caja_texto" type="text" name="ciu_cedula" id="ciu_cedula" value='<?=$ciu_cedula?>' size="20" maxlength="13" readonly <?=$read?> <?=$read_cedula?>>
            <!-- Checbox de Sin Cédula -->
            <input type="checkbox"  disabled  name="ciu_sincedula" id="ciu_sincedula" value="1" onchange="ver_cedula()" <?=$deshabilitar_campos?>
            <? if (substr($ciu_cedula,0,2)=="99") echo "checked "; ?>> No tiene No. de c&eacute;dula
        <?}else{?>
            <!--TextBox de cedula -->
            <input class="caja_texto" type="text" name="ciu_cedula" id="ciu_cedula" value='<?=$ciu_cedula?>' size="20" maxlength="13" onChange='validar_cambio_cedula()' <?=$read?> <?=$read_cedula?>>
            <!-- Checbox de Sin Cédula -->
            <input type="checkbox"  name="ciu_sincedula" id="ciu_sincedula" value="1" onchange="ver_cedula()" <?=$deshabilitar_campos?>
            <? if (substr($ciu_cedula,0,2)=="99") echo "checked "; ?>> No tiene No. de c&eacute;dula
        <?}
        ?>
        </td>
        <td class="titulos2" width="15%">Otro Documento: </td>
	    <td class="listado2" width="35%">
		<input class="caja_texto" type="text" name="ciu_documento" id="ciu_documento" value='<?=$ciu_documento?>' size="50" maxlength="50" <?=$read?> <?=$deshabilitar_campos?>>
	    </td>
	</tr>
    <tr>
        <td class="titulos2">* Nombre: &nbsp;&nbsp;&nbsp;
            
               <img src="<?=$ruta_raiz?>/iconos/copy.gif" alt="copiar" title="Copiar datos del Registro Civil" onclick="copiar_datos_registro_civil('nombre', 'ciu_nombre')">
            
        </td>
        <td class="listado2">
            <input class="caja_texto" type="text" name="ciu_nombre" id="ciu_nombre" onblur="javascript:changeCase_Articulos(this,'<?=$accion?>')" value='<?=$ciu_nombre?>' size="50" maxlength="150" <?=$read?> <?=$deshabilitar_campos?>>
        </td>
        <td class="titulos2">* Apellido: &nbsp;&nbsp;&nbsp;
           
            <img src="<?=$ruta_raiz?>/iconos/copy.gif" alt="copiar" title="Copiar datos del Registro Civil" onclick="copiar_datos_registro_civil('nombre', 'ciu_apellido')">
            
        </td>
        <td class="listado2">
            <input class="caja_texto" type="text" name="ciu_apellido" id="ciu_apellido" onblur="javascript:changeCase_Articulos(this,'<?=$accion?>')" value='<?=$ciu_apellido?>' size="50" maxlength="150" <?=$read?> <?=$deshabilitar_campos?>>
        </td>
    </tr>
    <tr>
	    <td class="titulos2"> T&iacute;tulo: </td>
	    <td class="listado2">
		<input class="caja_texto" type="text" name="ciu_titulo" id="ciu_titulo" onblur="javascript:changeCase_Articulos(this,'<?=$accion?>')"  value='<?=$ciu_titulo?>' size="50" maxlength="100" <?=$read?> <?=$deshabilitar_campos?>>
	    </td>
	    <td class="titulos2"> Abr. T&iacute;tulo: </td>
	    <td class="listado2">
		<input class="caja_texto" type="text" name="ciu_abr_titulo" id="ciu_abr_titulo" onblur="javascript:changeCase_Articulos(this,'<?=$accion?>')" value='<?=$ciu_abr_titulo?>' size="50" maxlength="30" <?=$read?> <?=$deshabilitar_campos?>>
	    </td>
	</tr>
    	<tr>
	    <td class="titulos2"><?=$descEmpresa?>: </td>
	    <td class="listado2">
                <input class="caja_texto" type="text" name="ciu_empresa" id="ciu_empresa" onblur="this.value=this.value.toUpperCase();"  value='<?=strtoupper($ciu_empresa)?>' size="50" maxlength="150" <?=$read?> <?=$deshabilitar_campos?>>
	    </td>
	    <td class="titulos2"> <?=$descCargo?>: </td>
	    <td class="listado2">
		<input class="caja_texto" type="text" name="ciu_cargo" id="ciu_cargo" onblur="javascript:changeCase_Articulos_CargoCabec(this)" value='<?=$ciu_cargo?>' size="50" maxlength="150" <?=$read?> <?=$deshabilitar_campos?>>
	    </td>
	</tr>
    	<tr>
	    <td class="titulos2"> * Ciudad: </td>
	    <td class="listado2">
<!--		<div id='ciu_ciudad'><?=$ciu_ciudad?></div>-->
                <?php
                $sqlCmbCiu = "select nombre, id from ciudad order by 1";
                $rsCmbCiu = $db->conn->Execute($sqlCmbCiu);
                echo $rsCmbCiu->GetMenu2('ciu_ciudad',$ciu_ciudad,"0:&lt;&lt seleccione &gt;&gt;",false,"","id='ciu_ciudad' Class='select' $deshabilitar_campos");
    ?>
	    </td>
	    <td class="titulos2"> Direcci&oacute;n: &nbsp;&nbsp;&nbsp;
                 <?//if (substr($ciu_cedula,0,2)!="99"){ ?>
                <img src="<?=$ruta_raiz?>/iconos/copy.gif" alt="copiar" title="Copiar datos del Registro Civil" onclick="copiar_datos_registro_civil('direccion', 'ciu_direccion')">
                <? //} ?>
            </td>
	    <td class="listado2">
		<input class="caja_texto" type="text" name="ciu_direccion" id="ciu_direccion" onblur="javascript:changeCase_Articulos_Direccion(this)" value='<?=$ciu_direccion?>' size="50" maxlength="150" <?=$read?> <?=$deshabilitar_campos?>>
	    </td>
	</tr>
    	<tr>
	    <td class="titulos2"> Tel&eacute;fono: </td>
	    <td class="listado2">
		<input class="caja_texto" type="text" name="ciu_telefono" id="ciu_telefono" value='<?=$ciu_telefono?>' size="50" maxlength="50" <?=$read?> <?=$deshabilitar_campos?>>
	    </td>
	    <td class="titulos2"> Email: </td>
	    <td class="listado2">
		<input class="caja_texto" type="text" name="ciu_email" id="ciu_email" value='<?=$ciu_email?>' size="50" maxlength="50" <?=$read?> <?=$deshabilitar_campos?>>
                <!--       onChange="nuevoAjax('div_validar_email', 'POST', 'validar_email.php', 'txt_email='+this.value);">-->
	    </td>
	</tr>
        <tr>
	    <td class="titulos2"> Contrase&ntilde;a: </td>
            <td class="listado2" colspan="3">
                <input type="checkbox" name="ciu_password" id="ciu_password" value="1" <?if ($ciu_nuevo==0) echo "checked "; echo $read;?> <?=$deshabilitar_campos?>> Cambiar contrase&ntilde;a
	    </td>
            
        </tr>
    </table>


    <br/>
    <center>
    <table width="100%" cellpadding="0" cellspacing="0">
      <tr>
    	<? if ($accion == 1 ) {?>
          <!--teya 20110429 -->
           <td><center><input name="btn_aceptar" id="btn_aceptar" type="submit" class="botones"  title="Almacena los cambios realizados" value="Aceptar" onClick="return ValidarInformacion();"/></center></td>
           <!--<td><center><input name="btn_aceptar" id="btn_aceptar" type="submit" class="botones"  title="Almacena los cambios realizados" value="Aceptar" onClick="<?= $accion_btn_aceptar ?>"/></center></td>	    -->
            <?}elseif($accion==2) {
              /*if ($ciu_estado==0 ){?>
                  <td><center><input name="btn_aceptar" id="btn_aceptar" style="visibility: hidden" type="submit" class="botones"  title="Almacena los cambios realizados" value="Aceptar" onClick="return ValidarInformacion();"/></center></td>                  <!--<td><center><input name="btn_aceptar" id="btn_aceptar" style="visibility: hidden" type="submit" class="botones"  title="Almacena los cambios realizados" value="Aceptar" onClick="<?= $accion_btn_aceptar ?>"/></center></td>-->
              <?}elseif($ciu_estado==1 ){ */?>
                  <td><center><input name="btn_aceptar" id="btn_aceptar" type="submit" class="botones"  title="Almacena los cambios realizados" value="Aceptar" onClick="return ValidarInformacion();"/></center></td>
                  <!--<td><center><input name="btn_aceptar" id="btn_aceptar" type="submit" class="botones"  title="Almacena los cambios realizados" value="Aceptar" onClick="<?= $accion_btn_aceptar ?>"/></center></td>-->
              <?//}?>
            
        <?}elseif($accion==3 and $_SESSION["usua_perm_ciudadano"]==1){?>
            <td><center><input name="btn_aceptar" id="btn_aceptar" style="visibility: hidden" type="submit" class="botones"  title="Almacena los cambios realizados" value="Aceptar" onClick="return ValidarInformacion();"/></center></td>
        <?}?>
    	<td>

        <?if($codigo1=="ciu_s"){?>
                <input  name="btn_accion" type="hidden" class="botones" title="Regresa a la página anterior sin guardar los cambios" value="Regresar"/>
        <?}else if($accion==2 or $accion==3){?>
         <center><input  name="btn_accion" type="button" class="botones" title="Regresa a la página anterior sin guardar los cambios" value='<?php echo ($cerrar == 'Si') ? "Cerrar" : "Regresar"?>' onclick="<?php echo ($cerrar == 'Si') ? "window.close()" : "location='./cuerpoUsuario_ext.php?cerrar=$cerrar&accion=$accion'"?>"/></center>
        <?}else{?>
         <center><input  name="btn_accion" type="button" class="botones" title="Regresa a la página anterior sin guardar los cambios" value="Regresar" onclick="<?php echo ($cerrar == 'Si') ? "window.close()" : "location='./mnuUsuarios_ext.php?cerrar=$cerrar'"?>"/></center>
        <?}?>
	</td>
      </tr>
    </table>
        <br>
    <?php
    if($usua_codi_actualiza!='')
    {
        if($ciu_obs_actualiza!='')
        {
        ?>
        <img alt="" align="right" src='../../iconos/posit.jpg' title="Ver observaciones de última actualización." onclick="mostrar_div_observacion('1')">
        <table width="50%" align="right">
            <tr>
                <td>
                <div id="div_observacion" class="cal-TextBox" style="border: thin solid #006699; width: 100%; display: none; text-align: left;">
                    <table border="0">
                        <tr>
                            <td width="98%"><?=$ciu_obs_actualiza?></td>
                            <td width="2%" valign="top" align="center"><img src="../../iconos/x.gif" alt="Cerrar" title="Cerrar" width="12px" height="12px" onclick="mostrar_div_observacion('0')"></td>
                        </tr>
                    </table>
                </div>
                </td>
            </tr>
        </table>
        <br><br><? } ?>
        <table width="100%" cellpadding="0" cellspacing="0" border="1">
            <tr>
                <td class="titulos4" colspan="4">
                    <center>
                        <p><B><span class=etexto><font size="2">&Uacute;ltima actualizaci&oacute;n realizada por:</font></span></B> </p>
                    </center>
                </td>
            </tr>
            <tr>
                <td class="titulos2" width="20%">
                    Usuario:
                </td>
                <td class="listado2" width="30%">
                    &nbsp;&nbsp;<?=$usua_nombre_act?>
                </td>
                <td class="titulos2" width="20%">
                    E-mail:
                </td>
                <td class="listado2" width="30%">
                    &nbsp;&nbsp;<?=$usua_email_act?>
                </td>
            </tr>
            <tr>
                <td class="titulos2">
                    Instituci&oacute;n:
                </td>
                <td class="listado2">
                    &nbsp;&nbsp;<?=$usua_institucion_act?>
                </td>
                <td class="titulos2">
                    Fecha de Actualizaci&oacute;n:
                </td>
                <td class="listado2">
                    &nbsp;&nbsp;<?=substr($ciu_fecha_actualiza,0,19).$descZonaHoraria?>
                </td>
            </tr>
        </table>
    <?php
    }
    ?>
    </center>
  </form>
    <script language="javascript" type="">
        ver_cedula();
        //nuevoAjax('ciu_ciudad', 'GET', 'ciudad_ajax.php', 'area=&codigo=<?=$ciu_ciudad?>');
        validar_cambio_cedula();
    </script>
  <!--$ciu_estado==0 and $ciu_nombre!="" and $_SESSION["usua_codi"]==0-->
<?php
/*if ($cerrar == 'Si'):
?>
    <div style="text-align:center">
    <a href="mnuUsuarios_ext.php?cerrar=<?php echo $cerrar; ?>" class="aqui">Administrar ciudadanos</a>
    </div>
<?php
endif;*/
?>
</body>
</html>
