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
//////////////   LISTA DE ANEXOS   ////////////////
if ($nurad=="") $nurad=$verrad;
if (!$ruta_raiz) $ruta_raiz=".";
if (str_replace("/","",str_replace(".","",$ruta_raiz))!="") die ("");
include_once "$ruta_raiz/js/ajax.js";

$flag = true;
$estado_doc = 1;
$SinAnexos="<span class='listado1'><b></br>El documento no tiene archivos adjuntos<br/></b></span>";
?>
    <script type="text/javascript">
        function cargar_lista_anexos (radi_nume, flag) {
            div = 'div_lista_anexos_padre';
            if (radi_nume.substr(19,1) == '1')
                div = 'div_lista_anexos_documento';
            nuevoAjax(div, 'GET', '<?=$ruta_raiz?>/anexos_lista.php', 'nivel=<?=$nivel_seguridad_documento?>&radi_temp=' + radi_nume + '&fl=' + flag + '&ruta_ajax=<?=$ruta_raiz?>');
        }
        
        function verificar_firma(val_path, nomb_arch) {
            windowprops = "top=100,left=100,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=600,height=400";
            tmp = <?="'$ruta_raiz/VerificarFirma.php?archivo='"?>;
            URL = <?="'$ruta_raiz/VerificarFirma.php?archivo='"?> + val_path + '&nombre_archivo=' + nomb_arch;
            window.open(URL , "VerificarFirma_<?=$verrad?>", windowprops);
        }

        function acciones(anexo, opcion){
            var flag = false;
            if (opcion==1)
                if (confirm('Está seguro de borrar este archivo anexo?'))
                    flag=true;
            if (opcion==2)
                if (confirm('Está seguro de colocar este archivo como imagen del documento?'))
                    flag=true;
            if (opcion==3)
                if (confirm('El medio de almacenamiento del documento cambiará a físico.\nEste documento deberá ser incluido en el archivo de la institución.'))
                    flag=true;
            if (opcion==4)
                if (confirm('El medio de almacenamiento del documento cambiará a electrónico.'))
                    flag=true;
            if (flag) {
                nuevoAjax('div_anexos_accion', 'GET', '<?=$ruta_raiz?>/anexos_borrar.php', 'anexo='+anexo+'&accion='+opcion);
                cargar_lista_anexos (anexo.substr(0,20),'1');
            }
            return;
        }
    </script>

<?php
if($nurad) {	//SI EL DOCUMENTO YA ESTABA REGISTRADO VERIFICAMOS EL ESTADO Y SI YA TENIA ANEXOS

    $rad=ObtenerDatosRadicado($nurad,$db);
    $flag = false;
    $estado_doc = $rad["estado"];
    if ($nivel_seguridad_documento>4)
    {	$flag=true;	$fl=1;	}
    if (substr($rad["radi_nume_radi"],-1) != 1 and $rad["estado"]==6 and $rad["usua_actu"]==$_SESSION["usua_codi"])
    {	$flag=true; } //Permitir anexar archivos a documentos enviados

    echo "<div name='div_anexos_accion' id ='div_anexos_accion'></div>";
    echo "<div name='div_lista_anexos_padre' id='div_lista_anexos_padre'></div>";
    echo "<div name='div_lista_anexos_documento' id='div_lista_anexos_documento'></div>";

    echo "<script>";
    if (substr($nurad,-1) == "1") {
        echo "cargar_lista_anexos ('".$rad["radi_nume_temp"]."', '0');";
    }
    echo "cargar_lista_anexos ('$nurad', '$fl');";
    echo "</script>";
    
}else {
    $flag = false;
    //echo "<table name='anexos' id='anexos'><tr><td><b><br>El documento no tiene archivos adjuntos<br></b></td></tr></table><br/>";
}
if ($boton_anexos!="Si") $flag = false;

 //////   ANEXAR NUEVOS ARCHIVOS    ////////////////  
if ($flag) {
	if ($boton_anexos=="Si") //En el caso de que ya este abierto un formulario se lo debe cerrar y definir uno nuevo
	{
	    echo "</form><form action='$ruta_raiz/anexos_funciones.php?nurad=$verrad&textrad=$textrad&krd=$krd' ENCTYPE='multipart/form-data' method='post'>";
	}

	$na=10;		//Número máximo de archivos que se presentan
//	Cargamos los tipos de documentos que se pueden subir al servidor
    	$rs = $db->conn->query("select * from anexos_tipo where anex_tipo_estado=1");
	$i=0;
	echo "<script>var tipo= new Array();";
	while(!$rs->EOF) {
	    echo "tipo[$i]='" . $rs->fields["ANEX_TIPO_EXT"] . "';";
	    $rs->MoveNext();
	    $i++;
	}
	echo "</script>";
	?>
    <script type="text/javascript">
	function escogio_archivo(valor)	//ALMACENA EL NOMBRE DEL ARCHIVO Y MUESTA UNA NUEVA FILA
	{
	    mensaje = '';
	    arch = document.getElementById('userfile'+valor).value.toLowerCase();
	    arch = arch.replace(/.p7m/g, "");
	    arr_ext = arch.split('.');
	    cadena = arr_ext[arr_ext.length-1].toLowerCase();
	    flag=true;
	    for (j = 0;j < <?=$i?>; ++j) {
		if (tipo[j]==cadena) flag=false;
	    }
	    if (flag) {
		alert ('No está permitido anexar archivos con extensión '+cadena+'.\n'+mensaje+'Consulte con su administrador del sistema.');
		document.getElementById('userfile'+valor).value = '';
		document.getElementById('nombarch'+valor).value = '';
		return;
	    }
	    document.getElementById('nombarch'+valor).value = document.getElementById('userfile'+valor).value;
	    document.getElementById('filaanex'+(valor+1)).style.display='';
	    return;
	}

	function ocultar_filas_anexos()
	{
		for(i=2;i<=<?=$na+1?>;i++){
			document.getElementById('filaanex'+i).style.display='none';	
		}
		return;
	}

	function BorrarArchivoSG(valor){	//ELIMINA LOS DATOS DE LAS CAJAS DE TEXTO
		if (confirm('Esta seguro de eliminar este archivo?'))
		{
		  document.getElementById('userfile'+valor).value = '';
		  document.getElementById('nombarch'+valor).value = '';
		  document.getElementById('descarch'+valor).value = '';
		}
		return;
	}
        function detectarPhone(){
        var navegador = navigator.userAgent.toLowerCase();
            if ( navigator.userAgent.match(/iPad/i) != null)
              return 2;
            else{        
                if( navegador.search(/iphone|ipod|blackberry|android/) > -1 )
                   return 1;    
                else 
                    return 0;
            }
        }
        function mostrar_boton(){
            if (detectarPhone()==2)
                datos="mostrarAnexar=0";
            else
            datos="mostrarAnexar=1";        
            nuevoAjax("div_mostrarboton", 'GET', 'anexoBoton.php', datos);
        }
    </script>
	<table WIDTH="100%" align="center" border="0" cellpadding="0" cellspacing="3" id="nuevos_anexos" class='borde_tab'>
	<!--tr><td height="25" class="titulos4" colspan="4">Anexar archivos al Documetn</td></tr-->
	<tr >
            <td colspan="4"><center><font color='black' size="2">
        <?php         
        $tamanoPermitido = str_replace('M','',ini_get('upload_max_filesize'));
        echo "Puede subir archivos con un tamaño máximo de: ".$tamanoPermitido." MB";
        ?></font></center></td>
        </tr>
        <tr bgcolor='#6699cc' class='etextomenu' align='middle'>
	    <td width='35%'  class="titulos2">Archivo</td>
	    <td  width='45%' class="titulos2">Descripci&oacute;n</td>
	    <td  width='10%' class="titulos2">Tipo de Anexo</td>
	    <td  width='10%' class="titulos2">Acci&oacute;n</td>
	</tr>

	<?php
	if (!isset($ent)) $ent = substr($nurad,-1); //Variable de radicacion/NEW.php que indica si el documento es de entrada(2) o de salida (0-1)
						//si el documento es de salida, no se pone el anexo como imagen del documento
	for($i=1;$i<=$na;$i++)	//CREO LAS CAJAS DE TEXTO
	{
	    $nombarch="nombarch$i";
	    $descarch="descarch$i";
	    $userfile="userfile$i";
	?>
		<tr id="filaanex<?=$i?>" <?if ($i%2==0) echo 'class="listado2"';?>>
		    <td align="center">
			<input name="<?=$userfile?>" type="file" class="tex_area" onChange="escogio_archivo(<?=$i?>);" id="<?=$userfile?>" size="35">
			<input type="hidden" name="<?=$nombarch?>" value="<?=$$nombarch?>" id="<?=$nombarch?>">
		    </td>
		    <td align="center">
			<textarea name="<?=$descarch?>" class="tex_area" cols="65" rows="1"id="<?=$descarch?>" ><?=$$descarch?></textarea>
		    <?php if ($ent==2 and $estado_doc==1) { ?>
			<br/>
			<input type="checkbox" name="chk_imagen<?=$i?>" id="chk_imagen<?=$i?>" value="1" class="ebutton">
			<span class="leidos">¿Desea colocar este archivo como imagen del documento?</span><br/>
		    <? } ?>
		    </td>
		    <td align="left">
			<input type="radio" name="chk_fisico<?=$i?>" id="chk_fisico<?=$i?>" value="0"  checked>
			<span class="listado1">Electr&oacute;nico</span><br/>
			<input type="radio" name="chk_fisico<?=$i?>" id="chk_fisico<?=$i?>" value="1">
			<span class="listado1">F&iacute;sico</span>
		    </td>
		    <td align="center"> <a class="vinculos" href="javascript:;" onclick="BorrarArchivoSG(<?=$i?>);" title="Borrar el archivo seleccionado">Borrar</a> </td>
		</tr>
	<?php
	}	
	?>
		<tr id="filaanex<?=$i?>">
		    <td colspan="4" align="center" class='alarmas'>Para continuar añadiendo archivos, por favor guarde primero los archivos actuales.</td>
		</tr>
		<tr>
		    <td colspan="4" align="center"><b><br>
                Puede firmar electr&oacute;nicamente sus archivos desde la aplicaci&oacute;n
                &quot;<a href="javascript:;" onclick="window.parent.topFrame.popup_firma_digital();" style='color:blue'>Firma Digital</a>&quot;.
                </b>
            </td>
		</tr>
	<?php
	echo "<script>ocultar_filas_anexos();</script>";
        ?>
	</table>
        <br>
        <center><div id="div_mostrarboton">
        <?php
	if ($boton_anexos=="Si")
            //echo "<input type='submit' value='Anexar' class='botones' title='Graba los anexos que se encuentran en la lista '>";
	    echo "<script>mostrar_boton();</script></center></div><br></form>";//muestra el boton segun navegado de dispositivo
//código pruebas MHA -- echo "<br><br><center><input type='button' value='Anexar' class='botones' title='Nuevo Anexo' onClick='window.parent.anexosFrame.anexo_nuevo_archivo(\"$nurad\", \"1\")'></center><br></form>";

}?>
<!--        </center></div><br></form>-->


