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
session_start();
include_once "$ruta_raiz/rec_session.php";
if (isset ($replicacion) && $replicacion && $config_db_replica_lst_listas!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_lst_listas);

include_once "$ruta_raiz/obtenerdatos.php";
include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

// LIBRERIAS PARA GENERADOR DE ARBOL AJAX
echo '<link rel="StyleSheet" href="'.$ruta_raiz.'/js/nornix-treemenu-2.2.0/example/style/menu.css" type="text/css" media="screen" />
      <script type="text/javascript" src="'.$ruta_raiz.'/js/nornix-treemenu-2.2.0/treemenu/nornix-treemenu.js"></script>';
include_once "$ruta_raiz/js/ajax.js";

?>

<script type="text/JavaScript">

    var timerID;

    function cargar_datos_lista(lista_codi) {
        document.getElementById('div_datos_lista').innerHTML = '';
        nuevoAjax("div_datos_lista", "POST", "listas_datos_lista.php", "txt_lista_codi="+lista_codi);
        cargar_lista_usuarios();
    }

    function cambiar_tipo_usuario(tipo_usua) {
        if (tipo_usua=='1') {
            document.getElementById('tr_institucion').style.display = '';
            if (document.getElementById('txt_buscar_institucion').value != '0') {
                document.getElementById('tr_dependencia').style.display = '';
                document.getElementById('tr_area').style.display = '';
            }
        } else {
            document.getElementById('tr_institucion').style.display = 'none';
            document.getElementById('tr_dependencia').style.display = 'none';
            document.getElementById('tr_area').style.display = 'none';
        }
    }

    function buscar_usuarios() {
        document.getElementById('div_buscar_usuarios').innerHTML = '<center>Por favor espere mientras se realiza la b&uacute;squeda.<br>&nbsp;<br>' +
                                                               '<img src="<?=$ruta_raiz?>/imagenes/progress_bar.gif"><br>&nbsp;</center>';

        var datos = "txt_buscar_tipo_usuario=" + document.formulario.txt_buscar_tipo_usuario.value +
                    "&txt_buscar_nombre=" + document.formulario.txt_buscar_nombre.value +
                    "&txt_buscar_cargo=" + document.formulario.txt_buscar_cargo.value +
                    "&txt_buscar_institucion=" + document.formulario.txt_buscar_institucion.value +
                    "&txt_buscar_dependencia=" + document.formulario.txt_buscar_dependencia.value;

        nuevoAjax('div_buscar_usuarios', 'POST', 'listas_buscar_usuarios.php', datos);
        return;
    }

    function seleccionar_usuario(cod_usr) {
        try {
            // Verificamos que no se encuentren ya seleccionados los usuarios
            if (document.formulario.txt_usuarios_lista.value.indexOf('-' + cod_usr + '-') >= 0) {
                alert("El usuario ya esta en lista, por favor verifique.");
                return;
            }
            document.formulario.txt_usuarios_lista.value += '-' + cod_usr + '-';
            cargar_lista_usuarios();
        } catch (e) {}
    }

    function seleccionar_todos_usuarios() {
        try {
            flag = false;
            cod_usr = document.formulario.txt_buscar_usuarios_lista.value.split(",");
            for (i=0 ; i<cod_usr.length ; ++i) {
                if (cod_usr[i] != '') {
                    // Verificamos que no se encuentren ya seleccionados los usuarios
                    if (document.formulario.txt_usuarios_lista.value.indexOf('-' + cod_usr[i] + '-') >= 0) {
                        flag = true;
                    } else {
                        document.formulario.txt_usuarios_lista.value += '-' + cod_usr[i] + '-';
                    }
                }
            }
            cargar_lista_usuarios();
            if (flag)
                alert("Uno o varios usuarios ya estaban seleccionados previamente en lista, por favor verifique.");
        } catch (e) {}
    }


    function cargar_lista_usuarios() {
        try {
            if (document.getElementById('div_datos_lista').innerHTML != '' && document.formulario.txt_usuarios_lista.type == 'textarea') {
                clearTimeout(timerID);
                document.getElementById('div_lista_usuarios').innerHTML = '<center>Por favor espere mientras se realiza la b&uacute;squeda.<br>&nbsp;<br>' +
                                                                       '<img src="<?=$ruta_raiz?>/imagenes/progress_bar.gif"><br>&nbsp;</center>';
                var datos = "txt_usuarios_lista=" + document.formulario.txt_usuarios_lista.value;
                            //"&txt_lista_orden=" + document.formulario.txt_lista_orden.value;
                nuevoAjax('div_lista_usuarios', 'POST', 'listas_cargar_usuarios.php', datos);
            } else {
                timerID = setTimeout("cargar_lista_usuarios()", 200);
            }
            return;
        } catch (e) {
            timerID = setTimeout("cargar_lista_usuarios()", 200);
        }
    }

    function borrar_usuario_lista(codigo)
    {
        if (codigo == 0) {
            if (confirm ('¿Desea borrar todos los usuarios de la lista?'))
                document.formulario.txt_usuarios_lista.value = '';
            else
                return;
        } else {
            document.formulario.txt_usuarios_lista.value = document.formulario.txt_usuarios_lista.value.replace('-' + codigo + '-','');
        }
        cargar_lista_usuarios();
    }

    function grabar_lista_usuarios(datolista) {        
        if (document.formulario.txt_lista_nombre.value == '') {
            alert ('Por favor ingrese el nombre de la lista');
            return;
        }
        
        document.formulario.btn_aceptar.disabled = true;        
        document.formulario.action = 'listas_grabar.php';
        document.getElementById("txt_lista_estado").value = 1;
        document.formulario.submit();
        if (datolista!='')
        window.close();
    }
    function eliminar_lista(){
        mensaje="Esta seguro de eliminar la lista?";
        if (document.getElementById("txt_lista_codi").value!=0){
            if (!confirm(mensaje)) {
                return false;
            }
            document.getElementById("txt_lista_estado").value = 0;
            document.formulario.action = 'listas_grabar.php';
            document.formulario.submit();
        }else
            alert("Seleccione una lista");
        
    }
    function buscar_depePadre(){
        document.formulario.txt_buscar_dependencia.value = "";
        var cod_inst = document.formulario.txt_buscar_institucion.value;
        if (cod_inst == 0) {
            document.getElementById('tr_dependencia').style.display = 'none';
            document.getElementById('tr_area').style.display = 'none';
            document.getElementById('txt_buscar_dependencia').value = '';
            return;
        }
        document.getElementById('tr_dependencia').style.display = '';
        document.getElementById('tr_area').style.display = '';
        var nomDivPadre = 'mnu_depePadre';
        nuevoAjax('area', 'GET', 'formArea_ajax.php', 'codDepe=');
        if(document.getElementById(nomDivPadre).innerHTML == '')
            nuevoAjax(nomDivPadre, 'GET', 'formDepePadre_ajax.php', 'codInst=' + cod_inst);
        else
            document.getElementById(nomDivPadre).innerHTML = '';
        return;
    }

    function buscar_depeHijo(cod_depe){
        document.formulario.txt_buscar_dependencia.value = cod_depe;
        var nomDivHijo = 'mnu_depeHijo_' + cod_depe;
        nuevoAjax('area', 'GET', 'formArea_ajax.php', 'codDepe=' + cod_depe);
        if(document.getElementById(nomDivHijo).innerHTML == '')
            nuevoAjax(nomDivHijo, 'GET', 'formDepeHijo_ajax.php', 'codDepe=' + cod_depe);
        else
            document.getElementById(nomDivHijo).innerHTML = '';
        return;
    }

    function desplegarContraer(cual,desde) {
        var elElemento=document.getElementById(cual);
        if(elElemento.className == 'elementoVisible') {
            elElemento.className = 'elementoOculto';
            desde.className = 'linkContraido';
        } else {
            elElemento.className = 'elementoVisible';
            desde.className = 'linkExpandido';
        }
    }



</script>
<?php

if ($_GET["lst_codigo"]!='')
$codigo_lista= $_GET["lst_codigo"];
else
    $codigo_lista= 0;
?>
<body bgcolor="#FFFFFF" onload="cargar_datos_lista(<?php echo $codigo_lista;?>);">
<form method="post" name="formulario" id="formulario" action="" >
  <center>

    <div id="div_datos_lista"></div>

    <table border=0 width="100%" class="borde_tab" cellpadding="0" cellspacing="5">
      <tr>
        <td width="15%" class="listado5"><font class="tituloListado">Buscar Usuarios: </font></td>
        <td width="70%" class="listado5" valign="middle">
            <table>
                <tr>
                    <td width="25%" align="right"><span class="listado5">Tipo de Usuario: </span></td>
                    <td colspan="3">
                        <select name='txt_buscar_tipo_usuario' id='txt_buscar_tipo_usuario' class='select' onChange='cambiar_tipo_usuario(this.value)'>
                            <option value="0" selected>Todos los usuarios</option>
                            <option value="1">Servidor P&uacute;blico</option>
                            <option value="2">Ciudadanos</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td width="25%" align="right" class="listado5">Nombre / C.I.:</td>
                    <td width="25%"><input type=text name="txt_buscar_nombre" id="txt_buscar_nombre" value="" class="tex_area"/></td>
                    <td width="25%" align="right"><span class="listado5"><?=$descCargo?>: </span> </td>
                    <td width="25%"><input type=text name="txt_buscar_cargo" id="txt_buscar_cargo" value="" class="tex_area"></td>
                </tr>
                <tr id="tr_institucion" style="display:none">
                    <td align="right"><span class="listado5"><?=$descEmpresa?>: </span></td>
                    <td colspan="3">
        <?
                        $sql = "select distinct inst_nombre, inst_codi from institucion where inst_estado=1 and inst_codi>0 order by 1";
                        $rs=$db->conn->query($sql);
                        echo $rs->GetMenu2("txt_buscar_institucion", "0", "0:&lt;&lt; Todas las Instituciones &gt;&gt;", false,"","id='txt_buscar_institucion' class='select' onChange='buscar_depePadre()'" );
        ?>
                    </td>
                </tr>
                <tr id="tr_area" style="display: none">
                    <td  align="right"><span class="listado5">&Aacute;rea Seleccionada: </span></td>
                    <td colspan="3">
                        <div id="area" class="divArea">&nbsp;&nbsp;<?=$inputArea?></div>
                    </td>
                </tr>
                <tr id="tr_dependencia" style="display:none">
                    <td colspan="4" valign="middle">
                        <input type="hidden" name="txt_buscar_dependencia" id="txt_buscar_dependencia" value=""/>
                        <div onclick="desplegarContraer('unaLista',this);" class="linkContraido">Selecionar &Aacute;rea</div>
                        <ul id="unaLista" class='elementoOculto'>
                            <div id="wrapper">
                                <div id="menu" class="menu"><a href="#" onclick="buscar_depePadre();">&Aacute;reas </a>
                                    <ul id="mnu_depePadre"></ul>
                                </div>
                            </div>
                        </ul>
                    </td>
                </tr>
            </table>
        </td>
        <td width="15%" align="center" class="listado5" >
            <input type="button" name="btn_buscar" value="Buscar" class="botones" title="Buscar Persona" onclick="buscar_usuarios()">
        </td>
      </tr>
    </table>

    <br>
    <table class=borde_tab width="100%" cellpadding="0" cellspacing="4">
        <tr class=listado2>
            <td width="100%">
                <center><b>RESULTADO DE LA B&Uacute;SQUEDA</b></center>
            </td>
        </tr>
    </table>
    <div id="div_buscar_usuarios" class="estiloDivPeq"></div>

    <br>
    <table class=borde_tab width="100%" cellpadding="0" cellspacing="4">
        <tr class=listado2>
            <td width="100%">               
                <center><b>DATOS A COLOCAR EN EL <?php if ($lista_codi !='') echo strtoupper($descRadicado); else echo "LISTADO";?></b></center>
            </td>
        </tr>
    </table>
    <div id="div_lista_usuarios" class="estiloDivPeq"></div>

    <br>
    <input type='button' name="btn_aceptar" value='Aceptar' class="botones_largo" onclick='grabar_lista_usuarios(<?php echo $codigo_lista;?>)'>    
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
     <?php if ($_GET['lst_codigo']==''){  ?>
    <input type='button' value='Cancelar' class="botones_largo" onclick="window.location='../formAdministracion.php'">
    <?php }else{ ?>    
     <input type='button' value='Cancelar' class="botones_largo" onclick="window.close();">   
    <?php }?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;         
     <input type='button' name="btn_eliminar" value='Eliminar Lista' class="botones_largo" onclick='eliminar_lista()'/>
    <br>&nbsp;
    
  </center>
</form>

</body>
</html>