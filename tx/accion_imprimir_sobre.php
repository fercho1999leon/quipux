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

$datosrad = ObtenerDatosRadicado($radi_nume,$db);
//Obtener el radi_nume_temp del documento
$radi_nume_temp = $datosrad['radi_nume_temp'];
$OpcImpr = ObtenerDatosOpcImpresion($radi_nume_temp, $db);
$tmp = str_replace ("--", ",", $datosrad["usua_dest"].$datosrad["cca"]);
$tmp = str_replace ("-", "", $tmp);
$sql = "select usua_nombre || case when tipo_usuario='1' then ' (funcionario)' else ' (ciudadano)' end, usua_codi from usuario where usua_codi in ($tmp) and usua_cedula <> '' order by 1 asc";
$rs = $db->query($sql);
$menu_usr  = $rs->GetMenu2("txt_usua_codi", 0, "", false,""," id='txt_usua_codi' class='select' onchange='datos_imprimir();'" );

?>
<!--<script type="text/javascript" src="../js/fckeditor/fckeditor.js"></script>-->
<script type="text/javascript" src="../js/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src='../js/base64.js'></script>
<script type="text/javascript">

    function ltrim(s) {
       return s.replace(/^\s+/, "");
    }

    function imprimir_sobre(tipoGuardar) {
       
        var parametros = "txt_usua_codi="+document.getElementById("txt_usua_codi").value;
        var direc = '';
        var ciu = '';
        var ciuTxt = '';
        var telf = '';
        var obs = '';
        var cambio = '';
        datos = document.getElementsByName("chk_datos_sobre")
        for(i=0; i<datos.length; i++) {
            parametros += "&" + datos[i].value + "=";
            if(datos[i].checked) parametros += "1"; else parametros += "0";
        }

        tipo_documento = document.getElementsByName("rad_tipo_sobre");
        for(i=0;i<tipo_documento.length;i++) {
            if(tipo_documento[i].checked)
                parametros += "&rad_tipo_sobre=" + tipo_documento[i].value;
        }
        
        parametros += "&nume_radi_temp=<?=$radi_nume_temp?>";
        
        if(document.getElementById("hd_usua_direccion").value != document.getElementById("usua_direccion").value)
        {
            obs = "Dirección de " + document.getElementById("hd_usua_direccion").value + " a " + document.getElementById("usua_direccion").value + "<br>";
            direc = document.getElementById("usua_direccion").value;
        }
        
        if(document.getElementById("hd_usua_ciudad").value != document.getElementById("usua_ciudad").value)
        {
            obs += "Ciudad de " + document.getElementById("hd_usua_ciudad").value + " a " + document.getElementById("usua_ciudad").value + "<br>";
            ciu = document.getElementById("codi_ciudad").value;
            
            ciuTxt = document.getElementById("codi_ciudad").options[document.getElementById("codi_ciudad").selectedIndex].text;
           cambio = 'ok';
        }
        
        if(document.getElementById("hd_usua_telefono").value != document.getElementById("usua_telefono").value)
        {
            obs += "Teléfono de " + document.getElementById("hd_usua_telefono").value + " a " + document.getElementById("usua_telefono").value + "<br>";
            telf = document.getElementById("usua_telefono").value;
        }
        if (cambio!='ok'){
            obs += "Ciudad de " + document.getElementById("hd_usua_ciudad").value + " a " + document.getElementById("usua_ciudad").value + "<br>";
            ciu = document.getElementById("codi_ciudad").value;

            ciuTxt = document.getElementById("codi_ciudad").options[document.getElementById("codi_ciudad").selectedIndex].text;
        }        
        parametros += "&opcDireccion=" + direc;
        parametros += "&opcCiudad=" + ciu;
        parametros += "&opcCiudadTxt=" + ciuTxt;
        parametros += "&opcTelefono=" + telf;
        parametros += "&tipoUsuario=" + document.getElementById("hd_tipo_usuario").value;
        parametros += "&observacionEdit=" + obs;

//        var oEditor = FCKeditorAPI.GetInstance('txt_texto_sobre') ;
//        texto_sobre = Base64.encode(Base64.encode(oEditor.GetData()));
        var oEditor = CKEDITOR.instances.txt_texto_sobre;
//         alert("s: "+oEditor.getData())
        texto_sobre = Base64.encode(Base64.encode(oEditor.getData()));
        
        parametros += "&txt_texto_sobre=" + texto_sobre;
        parametros += "&tipoGuardar= " + tipoGuardar;     
        //Se comenta la ciudad
        
        //Imprimir sobre en PDF
        ciuTxt = document.getElementById("codi_ciudad").options[document.getElementById("codi_ciudad").selectedIndex].text;        
        nuevoAjax('div_imprimir_sobre', 'POST', '../plantillas/Sobres.php', parametros);
        document.getElementById("hd_usua_direccion").value = document.getElementById("usua_direccion").value;
    }

    function datos_imprimir(){
        var parametros = "";
        parametros = "txt_usua_codi="+document.getElementById("txt_usua_codi").value;
        parametros = parametros + "&nume_radi_temp=<?=$radi_nume_temp?>";
        nuevoAjax('div_datos_impresion', 'GET', 'datos_imprimir_sobre.php', parametros);
    }

    function habilitaObj(opcT){
        if (opcT=="d"){//Dirección            
            document.getElementById('usua_direccion').readOnly = false;
            document.getElementById('usua_direccion').className = 'caja_texto';            
            if(ltrim(document.getElementById('usua_direccion').value) == 'Sin dirección')
                document.getElementById('usua_direccion').value = '';
            document.getElementById('usua_direccion').focus();
            
        }else if (opcT=="c"){ //Ciudad
            document.getElementById('usua_ciudad').style.display = 'none';
            document.getElementById('codi_ciudad').style.display = '';
            if(ltrim(document.getElementById('usua_ciudad').value) == 'Sin ciudad')
                document.getElementById('usua_ciudad').value = '';
            document.getElementById('codi_ciudad').focus();
        }else if (opcT=="t"){ //Telefono
            document.getElementById('usua_telefono').readOnly = false;
            document.getElementById('usua_telefono').className = 'caja_texto';
            if(ltrim(document.getElementById('usua_telefono').value) == 'Sin teléfono')
                document.getElementById('usua_telefono').value = '';
            document.getElementById('usua_telefono').focus();
        }else if(opcT=="ver"){                       
               if(document.getElementById('usua_direccion').value==""){
                if(document.getElementById('hd_tipo_usuario').value==2)
                    alert("El Ciudadano no tiene dirección ingresada");
                else
                    alert("El funcionario no tiene dirección ingresada");
            }
            else{
                
                document.getElementById('usua_direccion').style.visibility = 'visible';
                document.getElementById('usua_direccion').value = document.getElementById('usua_direccion_original').value;
                
                document.getElementById("hd_usua_direccion").value = document.getElementById("usua_direccion").value;
            }

        }
        return true;
    }

    function deshabilitaObj(opcT){
        if (opcT=="d"){
            document.getElementById('usua_direccion').readOnly = true;
            document.getElementById('usua_direccion').className = 'text_transparente';
            if(ltrim(document.getElementById('usua_direccion').value) == '')
                document.getElementById('usua_direccion').value = 'Sin dirección';
            document.getElementById('usua_direccion').focus();
        }else if (opcT=="c"){
            document.getElementById('usua_ciudad').style.display = '';
            document.getElementById('codi_ciudad').style.display = 'none';
            var ciudad = document.getElementById("codi_ciudad").options[document.getElementById("codi_ciudad").selectedIndex].text;
            if(ltrim(ciudad) == '<< seleccione >>')
                document.getElementById('usua_ciudad').value = 'Sin ciudad';
            else
                document.getElementById('usua_ciudad').value = ciudad;
            document.getElementById('usua_ciudad').focus();
        }else if (opcT=="t"){
            document.getElementById('usua_telefono').readOnly = true;
            document.getElementById('usua_telefono').className = 'text_transparente';
            if(ltrim(document.getElementById('usua_telefono').value) == '')
                document.getElementById('usua_telefono').value = 'Sin teléfono';
            document.getElementById('usua_telefono').focus();
        }else if (opcT=="dO"){ //Direccion Original del destinatario
            document.getElementById('usua_direccion').readOnly = true;
            document.getElementById('usua_direccion').className = 'text_transparente';
            if(ltrim(document.getElementById('usua_direccion').value) == '')
                document.getElementById('usua_direccion').value = 'Sin dirección';
            else
                document.getElementById('usua_direccion').value = document.getElementById('usua_direccion_original').value;
            document.getElementById('usua_direccion').focus();
        }
        return true;
    }

    function cambiar_editor_sobre(tipoCambia){
        
        titulo = document.getElementById("usua_titulo").value;
        nombre = document.getElementById("usua_nombre").value;
        cargo = document.getElementById("usua_cargo").value;
        empresa = document.getElementById("usua_empresa").value;

        direccion = document.getElementById("hd_usua_direccion").value;
        if(document.getElementById("hd_usua_direccion").value != document.getElementById("usua_direccion").value)
            direccion = document.getElementById("usua_direccion").value;
        ciudad = document.getElementById("hd_usua_ciudad").value;
        //alert(ciudad)
        if(document.getElementById("hd_usua_ciudad").value != document.getElementById("usua_ciudad").value)
            ciudad = document.getElementById("codi_ciudad").value;
        //if (tipoCambia==2)
        ciudad = document.getElementById("codi_ciudad").options[document.getElementById("codi_ciudad").selectedIndex].text;
        telefono = document.getElementById("hd_usua_telefono").value;
        if(document.getElementById("hd_usua_telefono").value != document.getElementById("usua_telefono").value)
            telefono = document.getElementById("usua_telefono").value;

        datos = document.getElementsByName("chk_datos_sobre");        
        for(i=0; i<datos.length; i++) {
            try {
                campo = datos[i].value.replace('chk_', '');
                switch (datos[i].value) {
                    case 'chk_titulo':
                    case 'chk_direccion':
                    case 'chk_ciudad':
                    case 'chk_telefono':
                        if (datos[i].checked)
                            comando = campo + " += '<br>';";
                        else
                            comando = campo + " = '';";
                        break;
                    case 'chk_nombre':
                    case 'chk_empresa':
                    case 'chk_cargo':                       
                        if (datos[i].checked)
                            comando = campo + " = '<b>' + " + campo + " + '</b><br>';";
                        else
                            comando = campo + " = '';";
                        break;
                }
                eval(comando);
            } catch (e) {}
        }
       if (titulo.indexOf('tulo')!=-1)
           titulo = ''; 
       if (direccion.indexOf('direcc')!=-1)
           direccion = '';
       if (ciudad.indexOf('seleccione')!=-1) 
       ciudad='';
       if (telefono.indexOf('fono')!=-1) 
       telefono = '';
       if (cargo.indexOf('Ninguno')!=-1 || cargo.indexOf('Sin Puesto')!=-1 || cargo.indexOf('cargo')!=-1)
           cargo = '';
       if (empresa.indexOf('NINGUNA')!=-1)
          empresa ='';
        editorTexto = titulo + nombre + cargo + empresa + direccion + ciudad + telefono;
        // Get the editor instance that we want to interact with.
//        var oEditor = FCKeditorAPI.GetInstance('txt_texto_sobre') ;
//        oEditor.SetData( editorTexto ) ;
        var oEditor = CKEDITOR.instances.txt_texto_sobre;
        oEditor.setData( editorTexto );
      
//        alert(oEditor.GetData());
        // Check the active editing mode.
//        if ( oEditor.EditMode == FCK_EDITMODE_WYSIWYG )
//        {
//                // Insert the desired HTML.
//                oEditor.InsertHtml( editorTexto ) ;
//        }

}

/**
* Creacion del objeto para el editor de texto
**/
function crearEditor()
{
	// Automatically calculates the editor base path based on the _samples directory.
	// This is usefull only for these samples. A real application should use something like this:
	// oFCKeditor.BasePath = '/fckeditor/' ;	// '/fckeditor/' is the default value.
//	var sBasePath = "../js/fckeditor/" ;
//	var oFCKeditor = new FCKeditor( 'txt_texto_sobre' ) ;
//	oFCKeditor.BasePath	= sBasePath ;
//	oFCKeditor.ReplaceTextarea() ;
    CKEDITOR.replace('txt_texto_sobre');
}


</script>
<body onload="datos_imprimir(); crearEditor();" onUnload="imprimir_sobre(2);">
    <center>
        <br>
        <table width="98%" border="0" cellpadding="0" cellspacing="5" class="borde_tab">
            <tr>
                <td class='titulos4' colspan="3">Acci&oacute;n: Imprimir sobre del documento No. <?=$datosrad["radi_nume_text"]?></td>
            </tr>
            <tr>
                <td class='listado1' align="right">Destinatario a imprimir en el sobre: </td>
                <td class='listado1' colspan="2"><?=$menu_usr?></td>
            </tr>
            <!--tr>
                <td class='listado1' align="right" width="34%">Datos a Imprimir: </td>
                <td class='listado1'  width="33%">
                    <input type="checkbox" name="chk_datos_sobre" id="chk_datos_sobre" value="chk_titulo" checked>T&iacute;tulo<br>
                    <input type="checkbox" name="chk_datos_sobre" id="chk_datos_sobre" value="chk_nombre" checked>Nombre Completo<br>
                    <input type="checkbox" name="chk_datos_sobre" id="chk_datos_sobre" value="chk_cargo"  checked>Cargo<br>
                </td>
                <td class='listado1' width="33%">
                    <input type="checkbox" name="chk_datos_sobre" id="chk_datos_sobre" value="chk_empresa"   checked>Empresa<br>
                    <input type="checkbox" name="chk_datos_sobre" id="chk_datos_sobre" value="chk_direccion" checked>Direcci&oacute;n<br>
                    <input type="checkbox" name="chk_datos_sobre" id="chk_datos_sobre" value="chk_ciudad"    checked>Ciudad<br>
                    <input type="checkbox" name="chk_datos_sobre" id="chk_datos_sobre" value="chk_telefono"  checked>Tel&eacute;fono<br>
                </td>
            </tr-->
        </table>
        <div id="div_datos_impresion"></div>
        <table width="99%" border="0" cellpadding="0" cellspacing="5">
            <tr>
                <td width="100%">
                <fieldset class="borde_tab">
                    <legend> Configuraci&oacute;n de Impresi&oacute;n </legend>
                    <table width="98%" border="0" cellspacing="0" cellpadding="3" rules="rows">
                        <tr>
                            <td class='listado1' colspan="2">
                                <input type="radio" name="rad_tipo_sobre" id="rad_tipo_sobre" value="SO" checked>Sobre Oficio&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="radio" name="rad_tipo_sobre" id="rad_tipo_sobre" value="SM">Sobre Mediano&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="radio" name="rad_tipo_sobre" id="rad_tipo_sobre" value="SP">Sobre Pequeño
                            </td>
                        </tr>
                    </table>
                </fieldset>
            </tr>
            <tr>
                <td class='listado1' colspan="3" align="center">
                    <br>
                    <input type='button' value='Vista Previa' onClick='cambiar_editor_sobre(1);' name='btn_aceptar' class='botones_largo' id='btn_aceptar' title="Imprimir sobre">
                    <?php
                    /*
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <input type='button' value='Regresar' onClick='history.back();' name='btn_regresar' class='botones_largo' id='btn_regresar' title="Regresa al men&uacute; anterior">
                     */ ?>
                </td>
            </tr>
            <tr>
                <td class='listado1' colspan="3" align="center">
                    <br>
                    <table width="800px" border="0" cellspacing="0" cellpadding="1">
                        <tr>
                            <td>
                                <textarea name="txt_texto_sobre" id="txt_texto_sobre" rows="10" cols="80" style="width: 100%; height: 200px"><?php echo $OpcImpr["TEXTO_SOBRE"] ?></textarea>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class='listado1' colspan="3" align="center">
                    <br>
                    <?php //1 guarda e imprime en la funcion imprime sobre
                    ?>                    
                    <input type='button' value='Imprimir' onClick='imprimir_sobre(1);' name='btn_aceptar' class='botones_largo' id='btn_aceptar' title="Imprimir sobre">
                    <input type='button' value='Regresar' onClick='history.back();' name='btn_regresar' class='botones_largo' id='btn_regresar' title="Regresa al men&uacute; anterior">
                </td>
            </tr>
        </table>
        <div id="div_imprimir_sobre"></div>
    </center>
</body>
</html>
