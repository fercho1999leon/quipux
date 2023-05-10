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
  require_once("$ruta_raiz/funciones.php");

  $radi_nume = trim(limpiar_sql($_GET['radi_nume']));
  $txt_documento = trim(limpiar_sql($_GET["txt_documento"]));
  $txt_cerrar = trim(limpiar_sql($_GET["cerrar"]));
  $radi_refe = trim(limpiar_sql($_GET["radi_refe"]));
  $txt_editar_refe = trim(limpiar_sql($_GET["modificar"]));
  
  if (strpos($_SERVER["HTTP_REFERER"],"asociar_documento.php")===false) { 
      // Si es la primera vez que ingresa a la página carga los datos de la BDD
      $rs = $db->query("select radi_nume_asoc from radicado where radi_nume_radi=$radi_nume");
      $txt_radi_asoc_ante = $rs->fields["RADI_NUME_ASOC"];
      $rs = $db->query("select radi_nume_radi from radicado where radi_nume_asoc=$radi_nume");
      $txt_radi_asoc_cons = "";
      while (!$rs->EOF) {
          $txt_radi_asoc_cons .= ",".$rs->fields["RADI_NUME_RADI"];
          $rs->MoveNext();
      }
  } else {
      $txt_radi_asoc_ante = trim(limpiar_sql($_GET['txt_radi_asoc_ante']));
      $txt_radi_asoc_cons = trim(limpiar_sql($_GET['txt_radi_asoc_cons']));
  } // end if (strpos...

  include_once "$ruta_raiz/funciones_interfaz.php";
  echo "<html>".html_head();
  include_once "$ruta_raiz/js/ajax.js";

$paginador = new ADODB_Pager_Ajax($ruta_raiz, "div_buscar_documentos", "asociar_documento_buscar.php", 
                                  "txt_documento,txt_radi_asoc_ante,txt_radi_asoc_cons,radi_refe,txt_editar_refe","radi_nume=$radi_nume");
?>
  <script>
    function buscar_documentos() {
        txt_documento = document.getElementById('txt_documento').value;
        if (txt_documento.length<=100){
        paginador_reload_div('')
        
            txt_radi_asoc_ante = document.getElementById('txt_radi_asoc_ante').value;
            txt_radi_asoc_cons = document.getElementById('txt_radi_asoc_cons').value;
            radi_refe = document.getElementById('radi_refe').value;
            txt_editar_refe = document.getElementById('txt_editar_refe').value;

            document.getElementById('div_buscar_documentos').style.display = '';
            nuevoAjax('div_buscar_documentos', 'GET', 'asociar_documento_buscar.php', 
                      'txt_documento=' + txt_documento +'&txt_editar_refe='+txt_editar_refe+'&radi_refe='+radi_refe+'&txt_radi_asoc_ante=' + txt_radi_asoc_ante + '&txt_radi_asoc_cons=' + txt_radi_asoc_cons +
                      '<?="&radi_nume=$radi_nume"?>');  
        }else
            alert("Demasiados caracteres ingresados");
       
    }

    function cargar_lista_asociados() {        
        stra = document.getElementById('txt_radi_asoc_ante').value;
        strc = document.getElementById('txt_radi_asoc_cons').value;
        txt_editar_refe = document.getElementById('txt_editar_refe').value;
        document.getElementById('div_asociar_documentos').innerHTML = 'Por favor espere mientras se asocia el documento.<br>&nbsp;<br>' +
                                                                   '<img src="<?=$ruta_raiz?>/imagenes/progress_bar.gif"><br>&nbsp;';
        nuevoAjax('div_asociar_documentos', 'POST', 'asociar_documento_seleccionar.php', 
                  'txt_radi_asoc_ante='+stra+'&txt_radi_asoc_cons='+strc+'&txt_editar_refe='+txt_editar_refe+'&radi_refe=<?=$radi_refe?>&radi_nume=<?=$radi_nume?>');
    }

    function seleccionar_documento(codigo, tipo) {
        
        stra = document.getElementById('txt_radi_asoc_ante').value;
        strc = document.getElementById('txt_radi_asoc_cons').value;
        
        
        if (tipo == 'A') {
	    if (strc.indexOf(codigo) >= 0) {
		alert('Este documento se encuentra en la lista de consecuentes.');
                return;
	    }
            document.getElementById('txt_radi_asoc_ante').value = codigo;
        } else {
	    if (stra == codigo) {
		alert('Este documento ya se encuentra seleccionado como antecedente.');
                return;
	    }
	    if (strc.indexOf(codigo) < 0) {
		strc = strc + ',' + codigo;	
	    }
            document.getElementById('txt_radi_asoc_cons').value = strc;
        }
        cargar_lista_asociados();
    }

    function seleccionar_documento_borrar(codigo, tipo) {
        
        if (tipo=='A') {
            txt_radi_nume = document.getElementById('txt_radi_nume').value;
           
            nuevoAjax('div_elimina_ref', 'POST', 'asociar_borrar_referencia.php', 
                  'radi_nume='+txt_radi_nume);
		document.getElementById('txt_radi_asoc_ante').value = '';
	}
	if (tipo=='C') {
            str = document.getElementById('txt_radi_asoc_cons').value;
            str = str.replace(','+codigo, '');
            document.getElementById('txt_radi_asoc_cons').value = str;
	}
        cargar_lista_asociados();
}

    function grabar_asociacion_documentos() {
        if (document.getElementById('txt_radi_nume').value=='')
            return;
        if (confirm('¿Desea modificar las asociaciones de estos documentos?')) {
            document.formulario.submit();
        }
    }
          
    
  </script>
  <body >
    <center>
      <form name="formulario" id="formulario" action="asociar_documento_grabar.php" method="post">
        <table width="100%" align="center" class=borde_tab border="0">
            <tr>
                <td width="100%" class="titulos5">
                  <center>
                    <br>Asociaci&oacute;n de Documentos<br>&nbsp;
                  </center>
                </td>
            </tr>
        </table>
        <br>
        <table width="100%" align="center" class=borde_tab border="0">
            <tr>
                <td width="25%" class="titulos5">
                    <br>No. de Documento:<br>&nbsp;
                </td>
                <td width="50%" class="listado5" valign="middle">
                    <input name="txt_documento" id="txt_documento" type="text" size="60" class="tex_area" value="<?=$txt_documento?>"/>
                </td>
                <td width="25%" class="titulos5" valign="middle">
                    <center><input type='button' value='Buscar' name='btn_buscar' class='botones' onClick='buscar_documentos();'></center>
                </td>
            </tr>
        </table>
        <input type='hidden' name='txt_editar_refe' id='txt_editar_refe' value="<?=$txt_editar_refe?>">
        <input type='hidden' name='txt_cerrar' id='txt_cerrar' value="<?=$txt_cerrar?>">
        <input type='hidden' name='txt_radi_nume' id='txt_radi_nume' value="<?=$radi_nume?>">
        <input type='hidden' name='txt_radi_asoc_ante' id='txt_radi_asoc_ante' value="<?=$txt_radi_asoc_ante?>">
        <input type='hidden' name='txt_radi_asoc_cons' id='txt_radi_asoc_cons' value="<?=$txt_radi_asoc_cons?>">
        <!--Referencia-->
        <input type='hidden' name='radi_refe' id='radi_refe' value="<?=$radi_refe?>">
        
        <div id='div_buscar_documentos'></div>        
        <div id='div_asociar_documentos'></div>
        <div id='div_asociar_documentos_grabar'></div>
        <div id='div_elimina_ref'></div>
        <br>
        <input type="button" name="btn_aceptar" value="Aceptar" class="botones" onClick="grabar_asociacion_documentos();">
        <input type="button" name="btn_cancelar" value="Cancelar" class="botones" onClick="window.close();">
      </form>
    </center>
<? 
  echo "<script>cargar_lista_asociados();</script>";
  if ($txt_documento != "")
    echo "<script>buscar_documentos();</script>";
?>
  </body>
</html>

