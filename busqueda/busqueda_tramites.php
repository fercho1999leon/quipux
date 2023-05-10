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
/**
*	Autor			Iniciales		Fecha (dd/mm/aaaa)
*       Mauricio Haro           MH                      2010-06-17
*
*	Modificado por		Iniciales		Fecha (dd/mm/aaaa)
*       David Gamboa            DG                      2011-08-19
**/

$ruta_raiz = "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
if (isset ($replicacion) && $replicacion && $config_db_replica_busqueda_tramites!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_busqueda_tramites);
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/obtenerdatos.php";

echo "<html>".html_head();
include_once "$ruta_raiz/js/ajax.js";

$paginador = new ADODB_Pager_Ajax($ruta_raiz, "div_buscar_documentos", "busqueda_paginador.php",
                  "txt_nume_documento,txt_nume_referencia,txt_fecha_desde,txt_fecha_hasta,txt_usua_destinatario,txt_usua_remitente,txt_depe_codi,txt_usua_codi,txt_texto","txt_tipo_busqueda=tramites");

$txt_nume_documento  = limpiar_sql($_POST["txt_nume_documento"]);
$txt_nume_referencia = limpiar_sql($_POST["txt_nume_referencia"]);

$txt_usua_remitente  = limpiar_sql($_POST["txt_usua_remitente"]);
$txt_usua_destinatario  = limpiar_sql($_POST["txt_usua_destinatario"]);

$txt_depe_codi  = limpiar_sql($_POST["txt_depe_codi"]);
$txt_usua_codi  = limpiar_sql($_POST["txt_usua_codi"]);
$txt_texto  = limpiar_sql($_POST["txt_texto"]);

if (isset($_POST["txt_fecha_desde"])) {
    $txt_fecha_desde = limpiar_sql($_POST["txt_fecha_desde"]);
    $txt_fecha_hasta = limpiar_sql($_POST["txt_fecha_hasta"]);
} else {
    $txt_fecha_desde = date("Y-m-d", strtotime(date("Y-m-d")." - 30 day"));
    $txt_fecha_hasta = date("Y-m-d");
}
$txt_buscar = 0+$_POST["txt_buscar"];



?>
<script language="JavaScript" type="text/javascript" >
    <? include_once "$ruta_raiz/js/spiffyCal/spiffyCal_v2_1.js";?>
    
    var dateAvailable1 = new ctlSpiffyCalendarBox("dateAvailable1", "formulario", "txt_fecha_desde","btnDate1","<?=$txt_fecha_desde?>",scBTNMODE_CUSTOMBLUE);
    var dateAvailable2 = new ctlSpiffyCalendarBox("dateAvailable2", "formulario", "txt_fecha_hasta","btnDate2","<?=$txt_fecha_hasta?>",scBTNMODE_CUSTOMBLUE);
    dateAvailable1.dateFormat="yyyy-MM-dd";
    dateAvailable2.dateFormat="yyyy-MM-dd";
    
    var timer_id_activar_boton_buscar = 0;
    var flag_activar_boton_buscar = false; //Permite ejecutar busqueda_buscar_documento() al dat click sobre buscar
    function busqueda_buscar_documento() {
        if (!flag_activar_boton_buscar) {
            alert("Espere por favor, su consulta ya se está procesando.");
            return;
        }

        numeroCaracteres=parseInt('<?=0+$numeroCaracteresTexto?>');
        
        if (document.getElementById('txt_nombre_texto_error').value==''){
            document.getElementById("txt_buscar").value = "1";
            flag_activar_boton_buscar = false;
            document.formulario.action = "busqueda_tramites.php";
            document.formulario.submit();
        }else{
            alert("Se requiere más información en los campos ingresados, debe ser al menos "+numeroCaracteres+ " caracteres");
        }
    }

    function realizar_busqueda() {
        paginador_reload_div();
        fjs_timer_activar_boton_buscar();
    }

    function fjs_timer_activar_boton_buscar() {
        try {
            document.getElementById("hid_flag_activar_boton_buscar").value = "0";
            flag_activar_boton_buscar = true;
        } catch(e) {
            setTimeout("fjs_timer_activar_boton_buscar()", 500);
        }
        return;
    }

    function cargar_combo_usuarios(txt_usua_codi) {
        area = document.getElementById("txt_depe_codi").value;
        nuevoAjax('div_combo_usuarios', 'POST', 'cargar_combo_usuarios.php', 'txt_depe_codi='+area+'&txt_usua_codi='+txt_usua_codi);
}
    
    function mostrar_documento(radi_nume, radi_text)
    {
        var_envio='<?=$ruta_raiz?>/verradicado.php?verrad='+radi_nume+'&textrad='+radi_text+'&menu_ver_tmp=3&tipo_ventana=popup';
        window.open(var_envio,radi_text,"height=450,width=750,scrollbars=yes");
    }
function llamarListado(nombreCarpeta, codigoCarpeta){
     location.href= '<?=$ruta_raiz?>/cuerpo.php?nomcarpeta='+nombreCarpeta+'&carpeta='+codigoCarpeta+'&adodb_next_page=1';
     document.getElementById('btn_Buscar').focus();
}

//faltan mas validaciones en todos los casos
function init() {

    var nomCarpeta = ""; //Nombre de la bandeja que esta en la base de datos
    var codCarpeta = ""; //Codigo de la bandeja que esta en la base de datos (Primary Key)
    shortcut.add("Alt+b", function() {
        nomCarpeta = "En Elaboración";
        codCarpeta = "1";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenuAsociado(nomCarpeta);
    });
    shortcut.add("Alt+r", function() {
        nomCarpeta = "Recibidos";
        codCarpeta = "2";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenuAsociado(nomCarpeta);
    });
    shortcut.add("Alt+c", function() {
        nomCarpeta = "Eliminados";
        codCarpeta = "6";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenuAsociado(nomCarpeta);
    });
    shortcut.add("Alt+n", function() {
        nomCarpeta = "No Enviados";
        codCarpeta = "7";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenuAsociado(nomCarpeta);
    });
    shortcut.add("Alt+e", function() {
        nomCarpeta = "Enviados";
        codCarpeta = "8";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenuAsociado(nomCarpeta);
    });
    shortcut.add("Alt+p", function() {
        nomCarpeta = "Reasignados";
        codCarpeta = "12";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenuAsociado(nomCarpeta);
    });
    shortcut.add("Alt+a", function() {
        nomCarpeta = "Archivados";
        codCarpeta = "10";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenuAsociado(nomCarpeta);
    });
    shortcut.add("Alt+i", function() {
        nomCarpeta = "Informados";
        codCarpeta = "13";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenuAsociado(nomCarpeta);
    });
    shortcut.add("Alt+t", function() {
        nomCarpeta = "Tareas Recibidas";
        codCarpeta = "15";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenuAsociado(nomCarpeta);
    });
    shortcut.add("Alt+s", function() {
        nomCarpeta = "Tareas Enviadas";
        codCarpeta = "16";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenuAsociado(nomCarpeta);
    });

}
window.onload=init();

</script>
<body>
    <div id="spiffycalendar" class="text"></div>

    <center>
    <form name="formulario" method="post" action="javascript:busqueda_buscar_documento()">
        <table width="90%" class="borde_tab">
            <tr>
                <td class="titulos4" colspan="3"><center><b>B&uacute;squeda Avanzada de Documentos</b></center></td>
            </tr>
            <input type="hidden" name="txt_nombre_texto_error" id="txt_nombre_texto_error" class="tex_area" value=""/>
            <tr>
                <td class="titulos2">No. <?=$descRadicado?>:</td>
                <td class="listado2">
                    <input type="text" name="txt_nume_documento" id="txt_nume_documento" class="tex_area" value='<?=$txt_nume_documento?>' onblur="numeroCarecteresDiv(this,<?=$numeroCaracteresTexto?>,'txt_nume_documento',1)"
                            size="70" title="Por favor ingrese el texto completo del n&uacute;mero del documento.">
                </td>
                <td width="30%" class="listado2">
                    Por favor ingrese el texto completo del n&uacute;mero del documento.<br>
                    <?php echo dibujarDiv($ruta_raiz,'div_txt_nume_documento',$numeroCaracteresTexto);?>
                </td>
            </tr>
            <tr>
                <td class="titulos2"><?=$descReferencia?>:</td>
                <td class="listado2">
                    
                    <input type="text" name="txt_nume_referencia" id="txt_nume_referencia" class="tex_area" value='<?=$txt_nume_referencia?>' onblur="numeroCarecteresDiv(this,<?=$numeroCaracteresTexto?>,'txt_nume_referencia',1)"
                            size="70" title="Ingrese el n&uacute;mero o parte del n&uacute;mero de referencia del documento.">
                </td>
                <td width="30%" class="listado2">&nbsp;
                <?php echo dibujarDiv($ruta_raiz,'div_txt_nume_referencia',$numeroCaracteresTexto);?>
                </td>
            </tr>
              <tr>
                <td class="titulos2">De (remitente):</td>
                <td class="listado2">
                    
                    <input type="text" name="txt_usua_remitente" id="txt_usua_remitente" class="tex_area" value='<?=$txt_usua_remitente?>' onblur="numeroCarecteresDiv(this,<?=$numeroCaracteresTexto?>,'txt_usua_remitente',1)"
                            size="70" title="Ingrese el nombre o parte del nombre del remitente del documento.">
                </td>
                <td width="30%" class="listado2">&nbsp;
                <?php echo dibujarDiv($ruta_raiz,'div_txt_usua_remitente',$numeroCaracteresTexto);?>
                </td>
            </tr>
            <tr>
                <td class="titulos2">Para (destinatario):</td>
                <td class="listado2">                    
                    <input type="text" name="txt_usua_destinatario" id="txt_usua_destinatario" class="tex_area" value='<?=$txt_usua_destinatario?>' onblur="numeroCarecteresDiv(this,<?=$numeroCaracteresTexto?>,'txt_usua_destinatario',1)"
                            size="70" title="Ingrese el nombre o parte del nombre del destinario del documento.">
                </td>
                <td width="30%" class="listado2">&nbsp;
                <?php echo dibujarDiv($ruta_raiz,'div_txt_usua_destinatario',$numeroCaracteresTexto);?>
                </td>
            </tr>
            <tr>
                <td class="titulos2">Buscar en el texto (asunto/notas):</td>
                <td class="listado2">                    
                    <input type="text" name="txt_texto" id="txt_texto" class="tex_area" value='<?=$txt_texto?>' maxlength="70" size="70" onblur="numeroCarecteresDiv(this,<?=$numeroCaracteresTexto?>,'txt_texto',1)"
                           title="Ingrese parte del asunto o del texto del documento.">
                </td>
                <td width="30%" class="listado2">&nbsp;
                <?php echo dibujarDiv($ruta_raiz,'div_txt_texto',$numeroCaracteresTexto);?>
                </td>
            </tr>
            <tr>
                <td class="titulos2">Desde Fecha (yyyy/mm/dd):</td>
                <td class="listado2">
                    <script language="javascript">
                        dateAvailable1.writeControl();
                    </script>
                </td>
                <td width="30%" class="listado2">&nbsp;
                </td>
            </tr>
            <tr>
                <td class="titulos2">Hasta Fecha (yyyy/mm/dd):</td>
                <td class="listado2">
                    <script language="javascript">
                        dateAvailable2.writeControl();
                    </script>
                </td>
                <td width="30%" class="listado2">&nbsp;
                </td>
            </tr>
            <tr>
                <td class="titulos2"><?=$descDependencia?>:</td>
                <td class="listado2">
                <?php
                $depe_codi_admin = obtenerAreasAdmin($_SESSION["usua_codi"],$_SESSION["inst_codi"],$_SESSION["usua_admin_sistema"],$db);
                $sql = "select depe_nomb, depe_codi from dependencia where depe_estado=1 and inst_codi=".$_SESSION["inst_codi"];
                if ($depe_codi_admin!=0)
                $sql.= " and depe_codi in ($depe_codi_admin)";
                $sql.= " order by 1 asc";
                $rs = $db->conn->query($sql);
                if($rs && !$rs->EOF)
                    print $rs->GetMenu2("txt_depe_codi", $txt_depe_codi, "0:&lt;&lt Todas las &Aacute;reas &gt;&gt;", false,"","class='select' id='txt_depe_codi' onChange='cargar_combo_usuarios(\"0\");'" );
                ?>
                </td>
                <td width="30%" class="listado2">&nbsp;
                </td>
            </tr>
            <tr>
                <td class="titulos2">Funcionario:</td>
                <td class="listado2">
                    <div id="div_combo_usuarios"><input type="hidden" name="txt_usua_codi" id="txt_usua_codi" value='<?=$txt_usua_codi?>'></div>
                </td>
                <td width="30%" class="listado2">&nbsp;
                </td>
            </tr>
        </table>
        <br>
        <input type="button" name="btn_buscar" class="botones_largo" value="Buscar" onclick="busqueda_buscar_documento();">
        
        <br><br>
        <div id='div_buscar_documentos' style="width: 200%" align="left"></div>
        <input type="hidden" name="txt_buscar" id="txt_buscar" value='<?=$txt_buscar?>'>
    </form>
    </center>
</body>
<script language="javascript" type="text/javascript">
    cargar_combo_usuarios("<?=$txt_usua_codi?>");
    if (document.getElementById("txt_buscar").value == "1") {       
        realizar_busqueda();
    } else {
        flag_activar_boton_buscar = true; //Habilita el boton buscar si es la primera vez que ingresa a la pagina
    }
</script>
</html>