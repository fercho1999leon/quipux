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
$ruta_raiz = "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
if (isset ($replicacion) && $replicacion && $config_db_replica_buscar_usuario_nuevo!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_buscar_usuario_nuevo);

include_once "$ruta_raiz/obtenerdatos.php";
include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

?>
<!-- LIBRERIAS PARA GENERADOR DE ARBOL AJAX -->
<!--<link rel="StyleSheet" href="<?=$ruta_raiz?>/js/nornix-treemenu-2.2.0/example/style/menu_uno.css" type="text/css" media="screen" />
<script type="text/javascript" src="<?=$ruta_raiz?>/js/nornix-treemenu-2.2.0/treemenu/nornix-treemenu.js"></script>-->
<?php
include_once "$ruta_raiz/js/ajax.js";

if (!isset($buscar_tipo)) $buscar_tipo = 1;
?>

<script type="text/JavaScript">

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
function pasar(cod_usr, tipo, flag_lista)
{
    var band_usr1 = true;
    var band_usr2 = true;
    var band_cca = true;
    flag_lista = flag_lista || false;

    // Verificamos que no se encuentren ya seleccionados los usuarios
    if (document.formu1.documento_us1.value.indexOf('-' + cod_usr + '-') >= 0) {
        if (!flag_lista) alert("El usuario ya esta en lista de destinatarios, por favor verifique.");
        band_usr1 = false;
    }

    if (document.formu1.documento_us2.value.indexOf('-' + cod_usr + '-') >= 0) {
        if (!flag_lista) alert("El usuario ya esta en la lista de remitentes, por favor verifique.");
        band_usr2 = false;
    }

    if (document.formu1.concopiaa.value.indexOf('-' + cod_usr + '-') >= 0) {
        if (!flag_lista) alert("El usuario ya esta en la lista con copia, por favor verifique.");
        band_cca = false;
    }

    //Agregamos los usuarios al final de la lista
    if(tipo==1 && band_usr1) {
        document.formu1.documento_us1.value += '-' + cod_usr + '-';
    }
    if(tipo==2 && band_usr2) {
        document.formu1.documento_us2.value <?if ($ent==2) echo "+";?>= '-' + cod_usr + '-';
    }
    if(tipo==3 && band_cca) {
        document.formu1.concopiaa.value += '-' + cod_usr + '-';
    }

    // Refrescamos la lista de usuarios seleccionados
    if (!flag_lista) ver_de_para();

    return band_usr1 && band_usr2 && band_cca;
}

// Pone los usuarios de una lista como destinatarios o como copia
function pasar_lista(tipo) {
    var codUsr = new Array();
    flag_mensaje = true;
    if (document.formu1.radi_lista_dest.value.indexOf('-' + document.formu1.lista_usr.value + '-') < 0 && document.formu1.lista_usr.value!=0) {
        if(tipo==1) {
            codUsr = document.formu1.usuarios_lista.value.split("-");

            //añadir codigo y nombre de lista
            document.formu1.radi_lista_dest.value += "-" + document.formu1.lista_usr.value + "-";
            if (document.formu1.radi_lista_nombre.value == "")
                document.formu1.radi_lista_nombre.value = document.formu1.lista_usr.options[document.formu1.lista_usr.selectedIndex].text;
            else
                document.formu1.radi_lista_nombre.value += ", " + document.formu1.lista_usr.options[document.formu1.lista_usr.selectedIndex].text;
        } else { // tipo = 3
            codUsr = document.formu1.usuarios_lista.value.split("-");
        }

        // Cargamos la lista de usuarios
        for(i=1 ; i<codUsr.length-1 ; i+=2) {
            if (!pasar(codUsr[i], tipo, true)) flag_mensaje = false;
        }

        // Refrescamos la lista de usuarios seleccionados
        ver_de_para();
        if (!flag_mensaje) alert("Uno o varios usuarios ya estaban seleccionados previamente, por favor verifique.");
    }
}

function borrar_lista(){
    var nombList = document.formu1.radi_lista_nombre.value;
    var nombresList = new Array();
    var i = 0;

    var codList = document.formu1.radi_lista_dest.value;
    var codigoList = new Array();
    var j = 1;

    //Borrar nombre de listas seleccionadas.
    nombresList = nombList.split(", ");
    document.formu1.radi_lista_nombre.value = "";
    if(nombresList.length>1)
    {
        for(i=0;i<nombresList.length-2;i++)
        {
            document.formu1.radi_lista_nombre.value += nombresList[i] + ", ";
        }
        document.formu1.radi_lista_nombre.value += nombresList[i];
    }

    //Borrar codigo de listas seleccionadas.
    codigoList = codList.split("-");
    document.formu1.radi_lista_dest.value = "";
    if(codigoList.length>1)
    {
        while(j < codigoList.length-2)
        {
            document.formu1.radi_lista_dest.value += "-" + codigoList[j] + "-";
            j+=2;
        }
    }
}

function borrarCCA(codigo,tipo)
{
    if (tipo=='D') {
        document.formu1.documento_us1.value = document.formu1.documento_us1.value.replace('-' + codigo + '-','');
    }
    if (tipo=='R') {
        document.formu1.documento_us2.value = document.formu1.documento_us2.value.replace('-' + codigo + '-','');
    }
    if (tipo=='C') {
        document.formu1.concopiaa.value = document.formu1.concopiaa.value.replace('-' + codigo + '-','');
    }
    ver_de_para();
    if(document.formu1.documento_us2.value=='')
        alert("Por favor, seleccione el Remitente del documento.");
}

function borrarTodos(tipo)
{
    if (tipo=='D') {
        document.formu1.documento_us1.value = "";
    }
    if (tipo=='C') {
        document.formu1.concopiaa.value="";
    }
    ver_de_para();
}

function crear_ciudadano(usuario)
{
    accion = '&accion=1';
    if ((usuario||'0')!='0')
        accion = '&accion=2&ciu_codigo='+usuario;
    var x = (screen.width - 1100) / 2;
    var y = (screen.height - 550) / 2;
    windowprops = "top=0,left=0,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=1100,height=550";
    url = '<?=$ruta_raiz?>/Administracion/ciudadanos/adm_usuario_ext.php?cod_impresion=1cerrar=Si'+accion;
    preview = window.open(url , "Crear_Usuario_Externo", windowprops);
    preview.moveTo(x, y);
    preview.focus();
    return;
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
        document.formu1.lista_usr.value='0';
    }
    var datos = "ent=<?=$ent?>" +
                "&lista_usr=" + document.formu1.lista_usr.value +
                "&buscar_tipo=" + document.formu1.buscar_tipo.value +
                "&buscar_nom=" + document.formu1.buscar_nom.value +
//                "&buscar_car=" + document.formu1.buscar_car.value +
                "&buscar_inst=" + document.formu1.buscar_inst.value +
                "&buscar_depe=" + document.formu1.buscar_depe.value;
            //comento cuando se selecciona una persona teniendo listas,las listas pone en blanco
            //comentado funciona
    //if(document.formu1.lista_usr.value==0)
      //  document.formu1.radi_lista_dest.value = document.formu1.lista_usr.value;


    if( document.formu1.lista_usr.value == 0 )
        style_display = 'none';

    document.getElementById('tb_listas').style.display = style_display;

    //alert(datos);
    nuevoAjax(nomDivResultado, 'POST', 'buscar_usuario_resultado.php', datos);
    return;


}

function ver_de_para(){
    //alert(document.formu1.lista_usr.value);
    var nomDivDePara = "dePara";
    document.getElementById(nomDivDePara).innerHTML = '<center>Por favor espere mientras se realiza la b&uacute;squeda.<br>&nbsp;<br>' +
                                                           '<img src="<?=$ruta_raiz?>/imagenes/progress_bar.gif"><br>&nbsp;</center>';
    var datos = "krd=" + "<?=$krd?>" +
                "&ent=" + "<?=$ent?>" +
                "&documento_us1=" + document.formu1.documento_us1.value +
                "&documento_us2=" + document.formu1.documento_us2.value +
                "&concopiaa=" + document.formu1.concopiaa.value +
                //"&radi_lista_dest=" + document.formu1.radi_lista_dest.value+
                "&tipo_lista=" + document.formu1.tipo_lista.value +
                "&lista_destino=" + document.formu1.radi_lista_dest.value;

    nuevoAjax(nomDivDePara, 'GET', 'buscar_usuario_de_para.php', datos);
    return;
}

function refrescar_pagina() {
    ver_de_para();
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
<body bgcolor="#FFFFFF" onload="ver_de_para();" onunload="pasar_datos()">
<form method="post" name="formu1" id="formu1" action="javascript: buscar_resultado('boton');" >

<textarea id="documento_us1" name="documento_us1" style="display: none" cols="1" rows="1"><?=$documento_us1?></textarea>
<textarea id="documento_us2" name="documento_us2" style="display: none" cols="1" rows="1"><?=$documento_us2?></textarea>
<textarea id="concopiaa" name="concopiaa" style="display: none" cols="1" rows="1"><?=$concopiaa?></textarea>
<input type="hidden" name="hidd_ent" id="hidd_ent" value="<?=$ent?>">
<input type="hidden" name="radi_lista_dest" id="radi_lista_dest" value="<?=$radi_lista_dest?>">
<input type="hidden" name="tipo_lista" id="tipo_lista" value="">
<input type="hidden" name="txt_nombre_texto_error" id="txt_nombre_texto_error" class="tex_area" value=""/>
<? if (isset($_GET['listado_listas'])){
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
                <option value="0" <?php if ($buscar_tipo==0) echo "selected"?>>Todos los usuarios</option>
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
                if ($_SESSION["tipo_usuario"]==2) // Si es ciudadano
                    $where = "and inst_codi in (select distinct inst_codi from usuarios where usua_esta=1 and usua_codi in (select usua_codi from permiso_usuario where id_permiso=9))";
                $sql = "select distinct inst_nombre, inst_codi from institucion where inst_estado=1 and inst_codi>1 $where order by 1";
                $rs=$db->conn->query($sql);
                if($rs) {
                    print $rs->GetMenu2("buscar_inst", "0", "0:&lt;&lt; Todas las Instituciones &gt;&gt;", false,"","id='buscar_inst' class='select' onChange='buscar_depePadre()'" );
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
<? } ?>
      </table>
    </td>
    <td width="12%" align="center" class="listado5" >
        <input type="submit" name="btn_buscar" value="Buscar" class="botones" title="Buscar Persona">
    </td>
  </tr>

<?php if ($_SESSION["inst_codi"] > 1) { // Si es ciudadano el que crea el documento ?>
  <tr>
    <td class="listado5"><font class="tituloListado">LISTAS DE ENV&Iacute;O: </font></td>
    <td class="listado5" valign="middle">
      <table>
        <tr>
            <td align="right"><span class="listado5">Nombre de la lista:</span></td><td>
            <?php
                    $sql="select lista_nombre, lista_codi from lista where lista_estado = 1 and (usua_codi=0 and inst_codi=".$_SESSION["inst_codi"].") or usua_codi=".$_SESSION["usua_codi"]." order by 1 asc";
                    //echo $sql;
                    $rs=$db->conn->query($sql);
                    if($rs)
                    print $rs->GetMenu2("lista_usr", $lista_usr, "0:&lt;&lt; Seleccione &gt;&gt;", false,"","class='select' onChange='buscar_resultado(\"cmb\");'" );
            ?>
            </td>
        </tr>
        <tr>
            <td align="right"><span class="listado5">Listas Seleccionadas:</span></td>
            <td width="80%">
                <input name="radi_lista_nombre" id="radi_lista_nombre" class="select" value="<?=$radi_lista_nombre?>" size="52" readonly>
                &nbsp;&nbsp;
                <a class="vinculos" href="#" onclick="borrar_lista();" title="Borrará el nombre de la lista seleccionada">Borrar nombre de lista</a>
                &nbsp;&nbsp;
                <?php /*<a class="vinculos" href="#" onclick='Start("../Administracion/listas/editar_lista.php");'>Editar lista</a>*/ ?>
                <a class="vinculos" href="#" onclick='Start("../Administracion/listas/listas.php");' title="Pueden Editar solo sus listas personales">Editar lista</a>
            </td>
        </tr>
      </table>
    </td>
    <td align="center" class="listado5" >
      <table id="tb_listas" width="70%" style='display:none'>
        <tr>
            <td width="35%" align="center"><font size=1><a class="vinculos" href="#" onClick="pasar_lista('1');" >Para</a></font></td>
            <td width="25%" align="center"><font size=1><a class="vinculos" href="#" onClick="pasar_lista('3');" >Copia</a></font></td>
            <!--<td align="center"><font size=1><a class="vinculos" href="#" onClick="borrar_lista();" >Borrar</a></font></td>-->
        </tr>
      </table>
    </td>
  </tr>
<?php } ?>

</table>
<br>
<table class=borde_tab width="100%" cellpadding="0" cellspacing="4">
    <tr class=listado2>	<td colspan="10">
        <center><b><?php if ($lista_usr=="0") echo "RESULTADO DE LA BUSQUEDA"; else echo "PERSONAS EN LA LISTA";?></b></center>
    </td></tr>
</table>
<div id="resultado" class="estiloDivPeq"></div>
<br>
<table class=borde_tab width="100%" cellpadding="0" cellspacing="4">
    <tr class=listado2>
        <td colspan="10">
            <center><b>DATOS A COLOCAR EN EL <?=strtoupper($descRadicado)?></b></center>
        </td>
    </tr>
    <tr>
        <td colspan="10" align="right">
        <?php
        if (isset($_GET['lista_modificada'])){//con esto verificamos si existe cambios en la lista
           if ($_GET['lista_modificada']==1){//si es 1 ?
               //$listado_listas
               ?>
            <input type="hidden" name="txt_ocultar" id="txt_ocultar" value="0"/>

            <table width="50%" align="right">
                <tr>
                    <td class="cal-TextBox" style="border: thin solid #006699; width: 100%;">
                         De clic
                        <a onclick="mostrar_div_usuarios();" href='javascript:void(0);' align="right" class="vinculos" target='mainFrame'>
                            AQUÍ
                        </a>para modificar los destinatarios en la lista.
                    <div id="div_lista_modificada">
                    </div>
                </td>
                </tr>
            </table>

        <?php   }//si es uno
        }//fin verificamos
        ?>
        </td>
    </tr>
</table>
    <div id="dePara" class="estiloDivPeq"></div>


<table width=100% border="0" align="center" name='tbl_botones' id='tbl_botones' cellspacing="1" cellpadding="4">
    <tr>
    <td width="25%">&nbsp;</td>
	<?php if ($_SESSION["usua_perm_ciudadano"]==1 or $_SESSION["usua_admin_sistema"]==1) { ?> <!--Cambio para desadocs VJ-->
	    <td id="td_ciudad"  style='display:none'><center><input type='button' value="Crear Ciudadano" class="botones_largo" onclick='crear_ciudadano()'></center></td>
	<?php } ?> <!--Cambio para desadocs VJ-->
	<td height="10%"><center><input type='button' value='Aceptar' class="botones_largo" onclick='pasar_datos()' title="Almacena la información en el documento" ></center></td>
	<td><center><input type='button' value='Cancelar' class="botones_largo" onclick='accion_cancelar="C"; window.close();'></center></td>
    <td width="25%">&nbsp;</td>
    </tr>
</table>
</form>

</body>
</html>
