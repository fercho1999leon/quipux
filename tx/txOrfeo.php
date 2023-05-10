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
*    Modificado Por: David Gamboa       Fecha:2011-02-14
*------------------------------------------------------------------------------
**/
$ruta_raiz = ".";
?>

<script type="text/javascript">

   
    function markAll()
    {
        if(document.form1.elements['checkAll'].checked)
            for(i=1;i<document.form1.elements.length;i++)
            document.form1.elements[i].checked=1;
        else
            for(i=1;i<document.form1.elements.length;i++)
            document.form1.elements[i].checked=0;
    }

    function envioTx()
    {       
       sw=0;
       <?
       if(!$verrad) {
       ?>
        for(i=1;i<document.form1.elements.length;i++)
            if (document.form1.elements[i].checked)
                sw=1;
        if (sw==0) {
            alert ("Debe seleccionar uno o mas registros");
            return;
        }
        <?	}  ?>    
        if (document.form1.codTx.value==12){
           if (document.getElementById('compResponder').value==1)
              document.form1.action="<?=$dirresponder.'&compResponder=1'?>";
            else
              document.form1.action="<?=$dirresponder?>";
          }
        if (document.form1.codTx.value==86)
             document.form1.action="<?=$dirresponderTodos?>";
       if (document.form1.codTx.value==16)
             document.form1.action="<?=$dirmodificar?>";
       if (document.form1.codTx.value==82)
             document.form1.action="<?=$dircopiar?>";
       document.form1.submit();
    }

    function window_onload(numrad)
    {
       
       mostrar_botones(<? if (trim($estado)=="") echo $carpeta; else echo $estado; ?>,'<?=$carpeta?>',numrad);
    }
    //mostrar documento
    function mostrar_documento(numdoc, txtdoc,carpeta)
    {
        tipo_ventana = 'popup';
	var_envio='<?=$ruta_raiz?>/verradicado.php?verrad='+numdoc+'&textrad='+txtdoc+'&carpeta='+carpeta+'&menu_ver_tmp=3&tipo_ventana='+tipo_ventana;
	window.open(var_envio,numdoc,"height=650,width=900,scrollbars=yes,left=800,top=200");
    }

<?
require_once("$ruta_raiz/pestanas.js");
?>

    // Variable que guarda la ultima opcion de la barra de herramientas de funcionalidades seleccionada
    seleccionBarra = -1;
    <!--
    function MM_swapImgRestore() { //v3.0
      var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
    }

    function MM_preloadImages() { //v3.0
      var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
        var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
        if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
    }

    function MM_findObj(n, d) { //v4.01
      var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
        d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
      if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
      for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
      if(!x && d.getElementById) x=d.getElementById(n); return x;
    }

    function MM_swapImage() { //v3.0
      var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
       if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}       
    }
    //-->

</script>

<script type="text/javascript">
function buscarFiltro(objt,parametros,valorEnviar){
   
    window.location="cuerpo.php?"+parametros+"&tipoLectura="+valorEnviar;
    }
</script>
<script language="JavaScript" type="">
    MM_preloadImages('<?=$ruta_raiz?>/imagenes/internas/overVobo.gif','<?=$ruta_raiz?>/imagenes/internas/overEditar.gif'
        ,'<?=$ruta_raiz?>/imagenes/internas/overFirmar.gif','<?=$ruta_raiz?>/imagenes/internas/overReasignar.gif'
        ,'<?=$ruta_raiz?>/imagenes/internas/overInformar.gif','<?=$ruta_raiz?>/imagenes/internas/overDevolver.gif'
        ,'<?=$ruta_raiz?>/imagenes/internas/overArchivar.gif','<?=$ruta_raiz?>/imagenes/internas/overEliminar.gif'
        ,'<?=$ruta_raiz?>/imagenes/internas/overNoEliminar.gif','<?=$ruta_raiz?>/imagenes/internas/overEnviar.gif'
        ,'<?=$ruta_raiz?>/imagenes/internas/overEnviarFisico.gif','<?=$ruta_raiz?>/imagenes/internas/overImprimirSobre.gif'
        ,'<?=$ruta_raiz?>/imagenes/internas/overEnvioM.gif','<?=$ruta_raiz?>/imagenes/internas/overEnvioE.gif'
        ,'<?=$ruta_raiz?>/imagenes/internas/overTareaNueva.gif'
        ,'<?=$ruta_raiz?>/imagenes/internas/overCopiar.gif');//
</script>

<style type="text/css">

/*Tool Tip*/
a.Ntooltip {
position: relative; /* es la posición normal */
text-decoration: none !important; /* forzar sin subrayado */
color:#0080C0 !important; /* forzar color del texto */
font-weight:bold !important; /* forzar negritas */
}

a.Ntooltip:hover {
z-index:999; /* va a estar por encima de todo */
background-color:#000000; /* DEBE haber un color de fondo */
}

a.Ntooltip span {
display: none; /* el elemento va a estar oculto */
}

a.Ntooltip:hover span {
display: block; /* se fuerza a mostrar el bloque */
position: absolute; /* se fuerza a que se ubique en un lugar de la pantalla */
top:1em; left:1em; /* donde va a estar */
width:70px; /* el ancho por defecto que va a tener */
padding:5px; /* la separación entre el contenido y los bordes */
background-color: #E0ECF8; /* el color de fondo por defecto */
color: #000000; /* el color de los textos por defecto */
}
/*Tool Tip*/
</style>

<table width="100%" border="0" cellspacing="0" cellpadding="0" align="left" id='sin_botones'>
  <tr><td>
    <input type='hidden' name='codTx' value=''>
    <input type='hidden' name='compResponder' id="compResponder" value=''/>
    <input type='hidden' name='txt_fech_tarea' id="txt_fech_tarea" value=''/>
    <table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td width="2%" valign="bottom" id="sin_agenda"></td>
        <td valign="bottom" align="left" id="btn_img" width="2%">
            <img name="principal_r4_c3" src="<?=$ruta_raiz?>/imagenes/internas/principal_r4_c3.gif" width="25" height="51" border="0" alt="">
        </td>
        <td valign="bottom" align="left" id="btn_imgRegresar" width="2%"  style='display:none'>
              <a HREF='javascript:history.back();' onMouseOut="MM_swapImgRestore()"
               onMouseOver="MM_swapImage('Image1','','<?=$ruta_raiz?>/imagenes/internas/overRegresar.gif',1)" title="Shift+Ctrl+U">
               <img src="<?=$ruta_raiz?>/imagenes/internas/regresar.gif" name="Image1" border="0" alt="Regresar"></a>
        </td>
        <td valign="bottom" id="btn_eliminar" style='display:none'>
            <a href="javascript:void(0);" onMouseOut="MM_swapImgRestore()" onClick="seleccionBarra = 2;changedepesel(2);"
               onMouseOver="MM_swapImage('Image2','','<?=$ruta_raiz?>/imagenes/internas/overEliminar.gif',1)" title="Shift+Ctrl+C">
                <img src="<?=$ruta_raiz?>/imagenes/internas/eliminar.gif" name="Image2" border="0" alt="Eliminar"></a>
        </td>
        <td valign="bottom" id="btn_noeliminar" style='display:none'>
            <a href="javascript:void(0);" onMouseOut="MM_swapImgRestore()" onClick="seleccionBarra = 6;changedepesel(6);"
               onMouseOver="MM_swapImage('Image3','','<?=$ruta_raiz?>/imagenes/internas/overNoEliminar.gif',1)" title="Shift+Ctrl+R">
                <img src="<?=$ruta_raiz?>/imagenes/internas/noEliminar.gif" name="Image3" border="0" alt="Restaurar"></a>
        </td>
        <td valign="bottom" id="btn_enviar" style='display:none'>
            <a href="javascript:void(0);" onMouseOut="MM_swapImgRestore()" onClick="seleccionBarra = 3;changedepesel(3);"
               onMouseOver="MM_swapImage('Image4','','<?=$ruta_raiz?>/imagenes/internas/overEnviar.gif',1)" title="Shift+Ctrl+Y">
                <img src="<?=$ruta_raiz?>/imagenes/internas/enviar.gif" name="Image4" border="0" alt="Enviar"></a>
        </td>
        <td valign="bottom" id="btn_enviom" style='display:none'>
            <a href="javascript:void(0);" onMouseOut="MM_swapImgRestore()" onClick="seleccionBarra = 5;changedepesel(5);"
               onMouseOver="MM_swapImage('Image5','','<?=$ruta_raiz?>/imagenes/internas/overEnvioM.gif',1)" title="Shift+Ctrl+N">
                <img src="<?=$ruta_raiz?>/imagenes/internas/envioM.gif" name="Image5" border="0" alt="Envio Manual"></a>
        </td>
        <td valign="bottom" id="btn_envioe" style='display:none'>
            <a href="javascript:void(0);" onMouseOut="MM_swapImgRestore()" onClick="seleccionBarra = 4;changedepesel(4);"
               onMouseOver="MM_swapImage('Image6','','<?=$ruta_raiz?>/imagenes/internas/overEnvioE.gif',1)" title="Shift+Ctrl+D">
                <img src="<?=$ruta_raiz?>/imagenes/internas/envioE.gif" name="Image6" height="51" border="0" alt="Envio Electronico"></a>
        </td>
        <td valign="bottom" id="btn_responder" style='display:none'>
            <a href="javascript:void(0);" onMouseOut="MM_swapImgRestore()" onClick="seleccionBarra = 12;changedepesel(12);"
               onMouseOver="MM_swapImage('Image7','','<?=$ruta_raiz?>/imagenes/internas/overResponder.gif',1)" title="Shift+Ctrl+G">
                <img src="<?=$ruta_raiz?>/imagenes/internas/responder.gif" name="Image7" border="0" alt="Responder"></a>
        </td>
        <!-- RESPONDER A TODOS-->
         <td valign="bottom" id="btn_responderTodos" style='display:none'>
            <a href="javascript:void(0);" onMouseOut="MM_swapImgRestore()" onClick="seleccionBarra = 86;changedepesel(86);"
               onMouseOver="MM_swapImage('Image7r','','<?=$ruta_raiz?>/imagenes/internas/overResponderTodos.gif',1)" title="Shift+Ctrl+G">
                <img src="<?=$ruta_raiz?>/imagenes/internas/responderTodos.gif" name="Image7r" border="0" alt="Responder a Todos"></a>
        </td>
        <td valign="bottom" id="btn_editar" style='display:none'>
            <a href="javascript:void(0);" onmouseout="MM_swapImgRestore()" onclick="seleccionBarra = 16;changedepesel(16);"
               onmouseover="MM_swapImage('Image8','','<?=$ruta_raiz?>/imagenes/internas/overEditar.gif',1)" title="Shift+Ctrl+B">
                <img src="<?=$ruta_raiz?>/imagenes/internas/editar.gif" name="Image8" border="0" alt="Editar"></a>
        </td>
        <td valign="bottom" id="btn_corregir" style='display:none'>
            <a href="javascript:void(0);" onMouseOut="MM_swapImgRestore()" onClick="seleccionBarra = 9;changedepesel(9);"
               onMouseOver="MM_swapImage('Image9','','<?=$ruta_raiz?>/imagenes/internas/overReasignar.gif',1)" title="Shift+Ctrl+P">
                <img src="<?=$ruta_raiz?>/imagenes/internas/reasignar.gif" name="Image9" height="51" border="0" alt="Reasignar"></a>
        </td>
        <td valign="bottom" id="btn_tramitar" style='display:none'>
            <a href="javascript:void(0);" onMouseOut="MM_swapImgRestore()" onClick="seleccionBarra = 9;changedepesel(9);"
               onMouseOver="MM_swapImage('Image10','','<?=$ruta_raiz?>/imagenes/internas/overReasignar.gif',1)" title="Shift+Ctrl+P">
                <img src="<?=$ruta_raiz?>/imagenes/internas/reasignar.gif" name="Image10" border="0" alt="Tramitar"></a>
        </td>
        <td valign="bottom" align="left" id="btn_informar" style='display:none'>
            <a href="javascript:void(0);" onMouseOut="MM_swapImgRestore()" onClick="seleccionBarra = 8;changedepesel(8);"
               onMouseOver="MM_swapImage('Image11','','<?=$ruta_raiz?>/imagenes/internas/overInformar.gif',1)" title="Shift+Ctrl+I">
                <img src="<?=$ruta_raiz?>/imagenes/internas/informar.gif" name="Image11" border="0" alt="Informar"></a>
        </td>
        <td valign="bottom" align="left" id="btn_desinformar" style='display:none'>
            <a href="javascript:void(0);" onMouseOut="MM_swapImgRestore()" onClick="seleccionBarra = 7;changedepesel(7);"
               onMouseOver="MM_swapImage('Image12','','<?=$ruta_raiz?>/imagenes/internas/overEliminar.gif',1)" title="Shift+Ctrl+K">
                <img src="<?=$ruta_raiz?>/imagenes/internas/eliminar.gif" name="Image12" border="0" alt="Eliminar Informado"></a>
        </td>
        <td valign="bottom" id="btn_firmar" style='display:none'>
            <a href="javascript:void(0);" onMouseOut="MM_swapImgRestore()" onClick="seleccionBarra = 11;changedepesel(11);"
               onMouseOver="MM_swapImage('Image13','','<?=$ruta_raiz?>/imagenes/internas/overFirmar.gif',1)" title="Shift+Ctrl+E">
                <img src="<?=$ruta_raiz?>/imagenes/internas/firmar.gif" name="Image13" border="0" alt="Firmar y enviar"></a>
        </td>
        <td valign="bottom" id="btn_archivar" style='display:none'>
            <a href="javascript:void(0);" onMouseOut="MM_swapImgRestore()" onClick="seleccionBarra = 13;changedepesel(13);"
               onMouseOver="MM_swapImage('Image14','','<?=$ruta_raiz?>/imagenes/internas/overArchivar.gif',1)" title="Shift+Ctrl+A">
                <img src="<?=$ruta_raiz?>/imagenes/internas/archivar.gif" name="Image14" border="0" alt="Archivar"></a>
        </td>
        <td valign="bottom" id="btn_noarchivar" style='display:none'>
            <a href="javascript:void(0);" onMouseOut="MM_swapImgRestore()" onClick="seleccionBarra = 17;changedepesel(17);"
               onMouseOver="MM_swapImage('Image15','','<?=$ruta_raiz?>/imagenes/internas/overNoEliminar.gif',1)" title="Shift+Ctrl+L">
                <img src="<?=$ruta_raiz?>/imagenes/internas/noEliminar.gif" name="Image15" border="0" alt="Reataurar Archivado"></a>
        </td>
        <td valign="bottom" id="btn_comentar" style='display:none'>
            <a href="javascript:void(0);" onMouseOut="MM_swapImgRestore()" onClick="seleccionBarra = 18;changedepesel(18);"
               onMouseOver="MM_swapImage('Image16','','<?=$ruta_raiz?>/imagenes/internas/overComentar.gif',1)" title="Shift+Ctrl+M">
                <img src="<?=$ruta_raiz?>/imagenes/internas/comentar.gif" name="Image16" border="0" alt="Comentar"></a>
        </td>
        <td valign="bottom" id="btn_devolver" style='display:none'>
            <a href="#" onMouseOut="MM_swapImgRestore()" onClick="seleccionBarra = 20;changedepesel(20);"
               onMouseOver="MM_swapImage('Image20','','<?=$ruta_raiz?>/imagenes/internas/overDevolver.gif',1)" title="Shift+Ctrl+Q">
                <img src="<?=$ruta_raiz?>/imagenes/internas/devolver.gif" name="Image20" border="0" alt="Devolver"></a>
        </td>
        <td valign="bottom" id="btn_enviarFisico" style='display:none'>
            <a href="javascript:void(0);" onMouseOut="MM_swapImgRestore()" onClick="seleccionBarra = 69;changedepesel(69);"
               onMouseOver="MM_swapImage('Image17','','<?=$ruta_raiz?>/imagenes/internas/overEnviarFisico.gif',1)" title="Shift+Ctrl+F">
                <img src="<?=$ruta_raiz?>/imagenes/internas/enviarFisico.gif" name="Image17" border="0" alt="Enviar Fisico"></a>
        </td>
        <td valign="bottom" id="btn_ImprimirSobre" style='display:none'>
            <a href="javascript:void(0);" onMouseOut="MM_swapImgRestore()" onClick="seleccionBarra = 70;changedepesel(70);"
               onMouseOver="MM_swapImage('Image18','','<?=$ruta_raiz?>/imagenes/internas/overImprimirSobre.gif',1)" title="Shift+Ctrl+S">
                <img src="<?=$ruta_raiz?>/imagenes/internas/ImprimirSobre.gif" name="Image18" border="0" alt="Imprimir sobre"></a>
        </td>
        <td valign="bottom" id="btn_tarea_asignar" style='display:none'>
            <a href="javascript:void(0);" onMouseOut="MM_swapImgRestore()" onClick="seleccionBarra = 30; changedepesel(30);"
               onMouseOver="MM_swapImage('Image30','','<?=$ruta_raiz?>/imagenes/internas/overTareaNueva.gif',1)" title="Shift+Ctrl+T">
                <img src="<?=$ruta_raiz?>/imagenes/internas/tareaNueva.gif" name="Image30" border="0" alt="Asignar Nueva Tarea"></a>
        </td>
        <td valign="bottom" id="btn_enviar_ciudadano" style='display:none'>
            <a href="javascript:void(0);" onMouseOut="MM_swapImgRestore()" onClick="seleccionBarra = 90;changedepesel(90);"
               onMouseOver="MM_swapImage('Image90','','<?=$ruta_raiz?>/imagenes/internas/overEnviar.gif',1)">
                <img src="<?=$ruta_raiz?>/imagenes/internas/enviar.gif" name="Image90" border="0" alt="Enviar"></a>
        </td>
         <td valign="bottom" id="btn_copiar_documento" style='display:none'>
            <a href="javascript:void(0);" onMouseOut="MM_swapImgRestore()" onClick="seleccionBarra = 82;changedepesel(82);"
               onMouseOver="MM_swapImage('Image82','','<?=$ruta_raiz?>/imagenes/internas/overCopiar.gif',1)" title="Shift+Ctrl+O">
                <img src="<?=$ruta_raiz?>/imagenes/internas/Copiar.gif" name="Image82" border="0" alt="Copiar"></a>
        </td>
        <td valign="bottom" id="btn_recuperar" style='display:none'>
            <a href="javascript:void(0);" onMouseOut="MM_swapImgRestore()" onClick="seleccionBarra = 83;changedepesel(83);"
               onMouseOver="MM_swapImage('Image83','','<?=$ruta_raiz?>/imagenes/internas/overRecuperar.gif',1)" title="Shift+Ctrl+O">
                <img src="<?=$ruta_raiz?>/imagenes/internas/recuperar.gif" name="Image83" border="0" alt="Copiar"></a>
        </td>
        <td valign="bottom" id="btn_asociar_documento" style='display:none'>
            <a href="javascript:void(0);" onMouseOut="MM_swapImgRestore()" onClick="seleccionBarra = 88;changedepesel(88);"
               onMouseOver="MM_swapImage('Image88','','<?=$ruta_raiz?>/imagenes/internas/overAsociar.gif',1)" title="Carpetas Virtuales">
                <img src="<?=$ruta_raiz?>/imagenes/internas/asociar.gif" name="Image88" border="0" alt="Carpetas Virtuales"></a>
        </td>
        <td valign="bottom" align="left" id="btn_img" width="2%">
            <img name="principal_r4_c4" src="<?=$ruta_raiz?>/imagenes/internas/principal_r4_c4.gif" width="25" height="51" border="0" alt="">
        </td>
        <td width="95%">
        <?php if ($_GET['verrad']=='') { //Si estamos en cuerpo.php mostramos los filtros de leidos, datos de la carpeta y alertas?>
            <table width="100%">
               
                    <tr>
                    <td width="40%">
                        <input type="hidden" name="tipoLectura" id="tipoLectura" value="2">
                  <?php if ($carpeta!=15 and $carpeta!=16) { ?>
                        <input type="radio" name="cbmLeidoNoLeido" value="0" onclick="document.getElementById('tipoLectura').value=this.value; paginador_reload_div(''); " > No Le&iacute;dos
                        <input type="radio" name="cbmLeidoNoLeido" value="1" onclick="document.getElementById('tipoLectura').value=this.value; paginador_reload_div(''); " > Le&iacute;dos
                        <input type="radio" name="cbmLeidoNoLeido" value="2" onclick="document.getElementById('tipoLectura').value=this.value; paginador_reload_div(''); " checked> Todos
                  <?php } ?>
                    </td>
                    <td width="40%" rowspan="2" <?php if ($carpeta!=2) echo 'style="display: none;"';?>><div id="div_tx_orfeo" style="width: 100%; float: right"></div></td>
                </tr>
                <tr>
                    <td class="listado2" width="60%">
                     <?php // Mostramos el nombre de la carpeta
                         $sql ="select * from carpeta where carp_codi=$carpeta";
                         $rs_carp = $db->query($sql);
//                         $nombre_carpeta = $_GET['nomcarpeta'];
                         if (!$rs_carp->EOF)
                             $nombre_carpeta = $rs_carp->fields["CARP_NOMBRE"]." (".str_replace("*usuario*",$_SESSION["usua_nomb"],$rs_carp->fields["CARP_DESCRIPCION"]).")";
                         echo "<b>Bandeja:</b> $nombre_carpeta";
                         ?>
                    </td>
                </tr>
            </table>
        <?php } ?>
        </td>
           
	</table>
	</td>
  </tr>
</table>
<script type="text/javascript">
    function verReferencia()
    {
        var ventana = document.getElementById('miVentana');
        ventana.style.marginTop = "100px";
        ventana.style.left = ((document.body.clientWidth-350) / 2) +  "px";
        ventana.style.display = 'block';
    }
    function cerrarReferencia()
    {
        var ventana = document.getElementById('miVentana');
        ventana.style.display = 'none';
    }

    try {
        document.getElementById('div_tx_orfeo').innerHTML = top.frames['leftFrame'].document.getElementById('div_bandeja_alerta').innerHTML;
    } catch (e) {}
</script>
<?php

include_once "$ruta_raiz/tx/tx_pantalla.php";

?>