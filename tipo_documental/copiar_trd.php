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
if (isset ($replicacion) && $replicacion && $config_db_replica_trd_copiar_trd!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_trd_copiar_trd);
include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();
include_once "$ruta_raiz/js/ajax.js";

//$where = "select distinct depe_codi from trd_nivel";
$where = "select distinct depe_codi from trd";
$sql = "select depe_nomb, depe_codi from dependencia where depe_estado=1 and inst_codi=".$_SESSION["inst_codi"]." and depe_codi in ($where) order by depe_nomb";
$rs=$db->conn->query($sql);
$menu_area_origen = $rs->GetMenu2("txt_area_origen", "0", "0:&lt;&lt seleccione un &aacute;rea &gt;&gt;", false,"","id='txt_area_origen' class='select' Onchange='cambiar_area_origen()'");
$sql = "select depe_nomb, depe_codi from dependencia where depe_estado=1 and inst_codi=".$_SESSION["inst_codi"]." order by depe_nomb"; // and depe_codi not in ($where)
$rs=$db->conn->query($sql);
$menu_area_destino = $rs->GetMenu2("txt_area_destino", "0", "0:&lt;&lt seleccione un &aacute;rea &gt;&gt;", false,"","id='txt_area_destino' class='select' Onchange='cambiar_area_destino()'");



?>
<script type="text/javascript">
    function cambiar_area_origen() {
        area = document.getElementById('txt_area_origen').value;
        nuevoAjax('div_trd_origen', 'POST', 'consultar_lista_trd.php', 'mostrar_botones=no&depe_actu='+ area);
    }
    function cambiar_area_destino() {
        area = document.getElementById('txt_area_destino').value;
        nuevoAjax('div_trd_destino', 'POST', 'consultar_lista_trd.php', 'mostrar_botones=no&depe_actu='+ area);
    }
    function copiar_trd() {
        if (document.getElementById('txt_area_origen').value == '0') {
            alert ('Por favor seleccione el área desde la que se copiarán las Carpetas Virtuales');
            return;
        }
        if (document.getElementById('txt_area_destino').value == '0') {
            alert ('Por favor seleccione el área a la que se copiarán las Carpetas Virtuales');
            return;
        }

        if (confirm('¿Esta seguro de copiar las carpetas virtuales?')) {
            document.getElementById("btn_copiar").disabled=true;
            document.getElementById("btn_cancelar").disabled=true;

            document.getElementById("frm_copiar_trd").action='copiar_trd_grabar.php';
            document.getElementById("frm_copiar_trd").submit();
        }
    }

    function MostrarFila(fila, ruta_raiz){
         var elemento=document.getElementsByName(fila);
            imgAgregar = "agregar.png";
            imgQuitar = "quitar.png";

            for (var i=0; i<elemento.length; i++){

                if (elemento[i].style.display=='none')
                {
                    if(document.getElementById("spam_"+fila)!=null)
                       document.getElementById("spam_"+fila).innerHTML = '<img src='+ruta_raiz+'/imagenes/'+ imgQuitar +' border="0" height="15px" width="15px">';
                    elemento[i].style.display='';
                }
                else{
                   if(document.getElementById("spam_"+fila)!=null)
                        document.getElementById("spam_"+fila).innerHTML = '<img src='+ruta_raiz+'/imagenes/'+ imgAgregar  +' border="0" height="15px" width="15px">';
                   elemento[i].style.display='none';
                }
               MostrarFila(elemento[i].id, ruta_raiz);
            }
    }
</script>
<body>
  <form id="frm_copiar_trd" method="post" action="">
    <center>
        <br>
        <table class="borde_tab" width="100%" border="0" cellpadding="0" cellspacing="2">
            <tr><td class="titulos2"><center>Copiar <?=$descTRDpl?> entre <?=$descDependencia?></center></td></tr>
        </table>
        <br>
        <table class="borde_tab" width="100%" border="0" cellpadding="0" cellspacing="2">
            <tr>
                <td class="titulos2" width="50%"><center>Origen</center></td>
                <td class="titulos2" width="50%"><center>Destino</center></td>
            </tr>
            <tr>
                <td class="listado2">&Aacute;rea: <?=$menu_area_origen?></td>
                <td class="listado2">&Aacute;rea: <?=$menu_area_destino?></td>
            </tr>
            <tr>
                <td class="listado1" valign="top"><div id="div_trd_origen"></div></td>
                <td class="listado1" valign="top"><div id="div_trd_destino"></div></td>
            </tr>
        </table>
        <br>
        <input type="button" name="btn_copiar" id="btn_copiar" value="Copiar" class="botones" onClick="copiar_trd()">
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="button" name="btn_cancelar" id="btn_cancelar" value="Regresar" class="botones" onClick="window.location='./menu_trd.php';">
    </center>
  </form>
</body>
</html>