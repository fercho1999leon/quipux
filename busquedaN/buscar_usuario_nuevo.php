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
/**
*       Basado Sistema de Compras Públicas y Búsqueda Avanzada (Anterior) Quipux 
*	Autor			Iniciales		Fecha (dd/mm/aaaa)
*       David Gamboa            DG, josedavo@gmail.com  2014-11-28
*       Se evita enviar like para consultas
*
**/
$ruta_raiz = "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
if (isset ($replicacion) && $replicacion && $config_db_replica_buscar_usuario_nuevo!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_buscar_usuario_nuevo);

include_once "$ruta_raiz/obtenerdatos.php";
include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();
$pres = 0+$_GET["pres"];
?>
<!-- LIBRERIAS PARA GENERADOR DE ARBOL AJAX -->
<!--<link rel="StyleSheet" href="<?=$ruta_raiz?>/js/nornix-treemenu-2.2.0/example/style/menu_uno.css" type="text/css" media="screen" />
<script type="text/javascript" src="<?=$ruta_raiz?>/js/nornix-treemenu-2.2.0/treemenu/nornix-treemenu.js"></script>-->
<?php
include_once "$ruta_raiz/js/ajax.js";

if (!isset($buscar_tipo)) $buscar_tipo = 1;
?>

<script type="text/javascript" Language="JavaScript">

    var documento=new Array();
    var tipo_us=new Array();
    var accion_cancelar = '';


function pasar_datos() {
    var destinatario_anterior = '';
    if (accion_cancelar=='C') return true;
    if(document.formu1.documento_us1.value != '' &&  document.formu1.documento_us2.value != '')
    {
        destinatario_anterior=opener.document.formulario.documento_us1.value;
        opener.document.formulario.documento_us1.value = document.formu1.documento_us1.value;
        opener.document.formulario.documento_us2.value = document.formu1.documento_us2.value;
        opener.document.formulario.concopiaa.value = document.formu1.concopiaa.value;
        opener.document.formulario.fl_modificar1.value = "1";
        //actualizar opciones de impresion
        radicadonum=opener.document.formulario.num_rad.value;

        if (radicadonum!=''){
            if (destinatario_anterior!=opener.document.formulario.documento_us1.value){
                opener.document.formulario.hidden_actualiza_opciones.value = 1;
            }
        }
        /**
         * Pasar tipo de impresion para el caso de las listas
         **/
        /*if(document.formu1.lista_usr.value!='0')
        {
            opener.document.formulario.radi_tipo_impresion.value = document.formu1.tipo_impresion.value;
            opener.document.formulario.radi_lista_dest.value = "-" + document.formu1.lista_usr.value + "-";
        }*/

        /**
        * Pasar nombre de listas seleccionadas.
        **/
        opener.document.formulario.radi_lista_dest.value=document.formu1.radi_lista_dest.value;
        opener.document.formulario.radi_lista_nombre.value=document.formu1.radi_lista_nombre.value;
        /**
        * Pasar id de instituciones.
        **/
        //Para determinar que tipo de documento se tiene que generar Memo u Oficio
        opener.document.formulario.flag_inst.value=document.getElementById('flag_inst').value;
        opener.document.formulario.flag_inst_m.value=document.getElementById('flag_inst_m').value;

        accion_cancelar='C'; //Para que no se ejecute 2 veces esta función.

//        timerID = setTimeout(" window.close()", 100);
        opener.refrescar_pagina(""); //Recargar Página Origen
        window.close();
    }
    else
    {
        try {
            alert("Por favor, seleccione el Remitente y Destinatario(s) del documento.");
        } catch (e) {}
        return false;
    }
}

// Coloca un usuario como destinatario o remitente
function pasar(cod_usr, tipo,nombre_usuario ,inst_codi,flag_lista)
{
    
  if(tipo==1){
       opener.document.formulario.txt_de_para_copia.value = tipo;
        opener.document.formulario.txt_usua_destinatario.value = nombre_usuario;
        opener.document.formulario.txt_usua_destinatario_h.value = cod_usr;
        
        
  
    opener.document.formulario.txt_inst_codi.value = inst_codi;
    }
    else if (tipo==2  || tipo==3){
        opener.document.formulario.txt_de_para_ex.value = tipo;
        opener.document.formulario.txt_usua_para.value = nombre_usuario;
        opener.document.formulario.txt_usua_para_h.value = cod_usr;
       
    }
    opener.cambiarDePara();
    window.close();
   
}

function buscar_ciudadano()
{
    //option_index = <?php echo $_SESSION["inst_codi"];?>;
    style_display = '';
    style_display_ciu = 'none';

    if (document.formu1.buscar_tipo.value == 2) {
        //option_index = 0;
        style_display_ciu = '';
        style_display = 'none';
        document.getElementById('lbl_datos_usua').innerHTML = "Nombre / C.I. / Institución:";
    }
    else
        document.getElementById('lbl_datos_usua').innerHTML = "Nombre / C.I.:";
    //document.getElementById('buscar_inst').options[option_index].selected = true;
    document.getElementById('tr_institucion').style.display = style_display;
//    document.getElementById('tr_dependencia').style.display = style_display;
//    document.getElementById('tr_area').style.display = style_display;
    document.getElementById('td_ciudad').style.display = style_display_ciu;
    document.getElementById('div_buscar_nom').style.display = 'none';
    return;
}

function buscar_depePadre(){
    return;
    document.formu1.buscar_depe.value = "";
    var cod_inst = document.formu1.buscar_inst.value;
    var nomDivPadre = 'mnu_depePadre';
    nuevoAjax('area', 'GET', 'formArea_ajax.php', 'codDepe=');
    if (document.getElementById(nomDivPadre)!=null){
        if(document.getElementById(nomDivPadre).innerHTML == '')
            nuevoAjax(nomDivPadre, 'GET', 'formDepePadre_ajax.php', 'codInst=' + cod_inst);
        else
            document.getElementById(nomDivPadre).innerHTML = '';
    }
    return;
}

function buscar_depeHijo(cod_depe){
    document.formu1.buscar_depe.value = cod_depe;
    var nomDivHijo = 'mnu_depeHijo_' + cod_depe;
    nuevoAjax('area', 'GET', 'formArea_ajax.php', 'codDepe=' + cod_depe);
    if (document.getElementById(nomDivHijo)!=null){
        if(document.getElementById(nomDivHijo).innerHTML == '')
            nuevoAjax(nomDivHijo, 'GET', 'formDepeHijo_ajax.php', 'codDepe=' + cod_depe);
        else
            document.getElementById(nomDivHijo).innerHTML = '';
    }
    return;
}

function buscar_resultado(desde){



    style_display = '';
    var nomDivResultado = "resultado";
    document.getElementById(nomDivResultado).innerHTML = '<center>Por favor espere mientras se realiza la b&uacute;squeda.<br>&nbsp;<br>' +
                                                           '<img src="<?=$ruta_raiz?>/imagenes/progress_bar.gif"><br>&nbsp;</center>';

    if(desde=='boton')
    {
        document.formu1.tipo_lista.value='';

    }

    pres = document.formu1.hidd_pres.value;
    datos = "&buscar_nom=" + document.formu1.buscar_nom.value;
    datos = datos + "&buscar_tipo=" + document.formu1.buscar_tipo.value;
    datos = datos + "&pres=" + pres;
    datos = datos +"&buscar_inst=" + document.formu1.buscar_inst.value;
    nuevoAjax(nomDivResultado, 'POST', 'buscar_usuario_resultado.php', datos);
    return;


}

function Start(URL) {
    var x = (screen.width - 1100) / 2;
    var y = (screen.height - 540) / 2;
    var nombre ='';
    if(document.formu1.lista_usr.value!='0')
    {
        nombre = document.formu1.lista_usr.options[document.formu1.lista_usr.selectedIndex].text;
        //alert(nombre);
        windowprops = "top=0,left=0,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=1100,height=540";
        URL = URL + '?lst_codigo=' + document.formu1.lista_usr.value + '&lst_nombre=' + nombre + '&accion=2';
        //alert(URL);
        preview = window.open(URL , "editar_lista", windowprops);
        preview.moveTo(x, y);
        preview.focus();
    }
    else
        alert("Por favor, seleccione una lista");
}

 function desplegarContraer(cual,desde) {
     return;
      var elElemento=document.getElementById(cual);
      if(elElemento.className == 'elementoVisible') {
           elElemento.className = 'elementoOculto';
           desde.className = 'linkContraido';
      } else {
           elElemento.className = 'elementoVisible';
           desde.className = 'linkExpandido';

      }
}
function mostrar_div_usuarios() {
    ocultar = document.getElementById('txt_ocultar').value;
   if (ocultar==0)
       ocultar=1;
   else
       ocultar=0;
    if (document.getElementById('txt_ocultar').value == 0){
        lista_dest = document.getElementById('radi_lista_dest').value;
        usuarios_doc = document.getElementById('documento_us1').value;

        document.getElementById('div_lista_modificada').style.display = '';
        datos="radi_lista_dest="+lista_dest+"&usuarios_radi="+usuarios_doc;
        nuevoAjax('div_lista_modificada', 'GET', 'usuarios_lista_modificada.php', datos);
    }
    else{

        document.getElementById('div_lista_modificada').style.display = 'none';
    }
    document.getElementById('txt_ocultar').value = ocultar;
}


</script>
<body bgcolor="#FFFFFF" onload="" onunload="pasar_datos()">
<form method="post" name="formu1" id="formu1" action="javascript: buscar_resultado('boton');" >

<textarea id="documento_us1" name="documento_us1" style="display: none" cols="1" rows="1"><?=$documento_us1?></textarea>
<textarea id="documento_us2" name="documento_us2" style="display: none" cols="1" rows="1"><?=$documento_us2?></textarea>
<textarea id="concopiaa" name="concopiaa" style="display: none" cols="1" rows="1"><?=$concopiaa?></textarea>

<input type="hidden" name="hidd_pres" id="hidd_pres" value="<?=$pres?>">
<input type="hidden" name="hidd_ent" id="hidd_ent" value="<?=$ent?>">
<input type="hidden" name="radi_lista_dest" id="radi_lista_dest" value="<?=$radi_lista_dest?>">
<input type="hidden" name="tipo_lista" id="tipo_lista" value="">
<input type="hidden" name="txt_nombre_texto_error" id="txt_nombre_texto_error" class="tex_area" value=""/>
<?php if (isset($_GET['listado_listas'])){
    $lista_modificada = 0 + $_GET['lista_modificada'];
}
echo '<input type="hidden" name="hidden_lista_modificada" id="hidden_lista_modificada" class="tex_area" value="'.$lista_modificada.'"/>';
?>
<table border=0 width="100%" class="borde_tab" cellpadding="0" cellspacing="5">
  <tr>
    <td width="13%" class="listado5"><font class="tituloListado">BUSCAR PERSONA: </font></td>
    <td width="75%" class="listado5" valign="middle">
    <table>
        <?php

        if ($_SESSION["tipo_usuario"]==2) { // Si es ciudadano el que crea el documento
            echo "<input type='hidden' name='lista_usr' id='lista_usr' value='0'>
                  <input type='hidden' name='buscar_depe' id='buscar_depe' value='0'>
                  <div id='tb_listas' style='display:none'></div>
                  <input type='hidden' name='radi_lista_nombre' id='radi_lista_nombre' value=''>";
        }
        ?>
         <tr>
            <td width="30%" align="right"><span class="listado5">Tipo de Usuario: </span></td>
            <td width="70%">
                <select name='buscar_tipo' id='buscar_tipo' class='select' onChange='buscar_ciudadano()' <? if ($_SESSION["inst_codi"] == 1) echo "disabled"?>>
                <!--<option value="0" <?php if ($buscar_tipo==0) echo "selected"?>>Todos los usuarios</option>-->
                <option value="1" <?php if ($buscar_tipo==1) echo "selected"?>>Servidor P&uacute;blico</option>
                <option value="2" <?php if ($buscar_tipo==2) echo "selected"?>>Ciudadano</option>
                </select>
            </td>
        </tr>
        <tr>
            <td align="right"><span class="listado5"><label id="lbl_datos_usua">Datos Usuario:</label> </span> </td>
            <td >
<!--                <input type=text id="buscar_nom" name="buscar_nom" value="<?=$buscar_nom?>" class="tex_area"/>                -->
                <?php echo cajaTextoValida('buscar_nom',$buscar_nom,$numeroCaracteresTexto,"onkeypress=evento_ver(event,this,$numeroCaracteresTexto,'buscar_nom',1)")?>
                &nbsp;&nbsp;(c&eacute;dula, nombre, cargo, correo electr&oacute;nico, instituci&oacute;n, &aacute;rea)
            </td>
<!--            <td width="16%" align="right"><span class="listado5"><?=$descCargo?>: </span> </td>
            <td width="16%">
                <?php echo cajaTextoValida('buscar_car',$buscar_car,$numeroCaracteresTexto,"")?>
                <input type=text name="buscar_car" value="<?=$buscar_car?>" class="tex_area">
            </td>-->

        </tr>
        <tr id="tr_institucion" <?php if ($buscar_tipo==2) echo "style='display:none'";?>>
            <td  align="right"><span class="listado5"><?=$descEmpresa?>: </span></td><td colspan="5">
            <?php
                $where = "";
                $inst_codi_consulta = $_SESSION["inst_codi"];
                if ($_SESSION["tipo_usuario"]==2) // Si es ciudadano
                    $where = "and inst_codi in (select distinct inst_codi from usuarios where usua_esta=1 and usua_codi in (select usua_codi from permiso_usuario where id_permiso=9))";
                $sql = "select distinct inst_nombre, inst_codi from institucion where inst_estado=1 $where order by 1";
                $rs=$db->conn->query($sql);
                if($rs) {
                    print $rs->GetMenu2("buscar_inst", $inst_codi_consulta, "0:&lt;&lt; Todas las Instituciones &gt;&gt;", false,"","id='buscar_inst' class='select' onChange='buscar_depePadre()'" );
                }
            ?>
            </td>
        </tr>

<?php if ($_SESSION["inst_codi"] > 1) { // Si es ciudadano el que crea el documento ?>
        <tr id="tr_area" <?php if ($buscar_tipo==2 or true) echo "style='display:none'"?>>
            <td  align="right"><span class="listado5">&Aacute;rea Seleccionada: </span></td><td colspan="3">
                <div id="area" class="divArea">&nbsp;&nbsp;<?=$inputArea?></div>
            </td>
            <td colspan="2">&nbsp;</td>
        </tr>
        <tr id="tr_dependencia" <?php if ($buscar_tipo==2 or true) echo "style='display:none'"?>>
            <td colspan="6" valign="middle"><input type="hidden" name="buscar_depe" id="buscar_depe"/>
                <div onclick="desplegarContraer('unaLista',this);" class="linkContraido">Selecionar &Aacute;rea</div><br>
                <ul id="unaLista" class='elementoOculto'>
                <div id="wrapper">
                    <div id="menu" class="menu"><a href="#" onclick="buscar_depePadre();">&Aacute;reas </a>
                        <ul id="mnu_depePadre"></ul>
                    </div>
                </div>
                </ul>
            </td>
        </tr>
<?php } ?>
      </table>
    </td>
    <td width="12%" align="center" class="listado5" >
        <input type="submit" name="btn_buscar" value="Buscar" class="botones" title="Buscar Persona">
    </td>
  </tr>

</table>
<br>
<table class=borde_tab width="100%" cellpadding="0" cellspacing="4">
    <tr class=listado2>	<td colspan="10">
        <center><b><?php if ($lista_usr=="0") echo "RESULTADO DE LA BUSQUEDA"; else echo "PERSONAS EN LA LISTA";?></b></center>
    </td></tr>
</table>
<div id="resultado" class="estiloDivPeq"></div>
<br>

</form>

</body>
</html>