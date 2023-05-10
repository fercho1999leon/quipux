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

**************************************************************************************
** Verifica el estado de los respaldos                                              **
** Muestra un informe del estado de los respaldos, permite descargar el respaldo    **
** si es que ya finalizó y eliminarlos.                                             **
**                                                                                  **
** Desarrollado por:                                                                **
**      Mauricio Haro A. - mauricioharo21@gmail.com                                 **
*************************************************************************************/

$ruta_raiz = "..";
include_once "$ruta_raiz/config.php";
include_once "$ruta_raiz/include/db/ConnectionHandler.php";
include_once "$ruta_raiz/funciones_interfaz.php";

$db = new ConnectionHandler("$ruta_raiz");
$db->conn->SetFetchMode(ADODB_FETCH_ASSOC);

echo "<html>".html_head();
include_once "$ruta_raiz/js/ajax.js";
$paginador = new ADODB_Pager_Ajax($ruta_raiz, "div_buscar_respaldos", "backup_usuarios_estado_buscar.php", "cmb_institucion,cmb_estado,txt_fecha_inicio_sol,txt_fecha_fin_sol","");

$fecha_inicio_sol = date("Y-m-d", strtotime(date("Y-m-d")." - 6 month"));
$fecha_fin_sol = date("Y-m-d");

?>

<link rel="stylesheet" type="text/css" href="<?=$ruta_raiz?>/js/spiffyCal/spiffyCal_v2_1.css">
<script type="text/javascript" src="<?=$ruta_raiz?>/js/spiffyCal/spiffyCal_v2_1.js"></script>
<script type="text/javascript" src="<?=$ruta_raiz?>/js/funciones.js"></script>

<script language="JavaScript" type="text/javascript" >
    var dateAvailable1 = new ctlSpiffyCalendarBox('dateAvailable1', 'formulario', 'txt_fecha_inicio_sol','btnDate1','<?=$fecha_inicio_sol?>',scBTNMODE_CUSTOMBLUE);
    var dateAvailable2 = new ctlSpiffyCalendarBox('dateAvailable2', 'formulario', 'txt_fecha_fin_sol','btnDate2','<?=$fecha_fin_sol?>',scBTNMODE_CUSTOMBLUE);
</script>
<script language="JavaScript" type="text/JavaScript">
    function eliminar_respaldo(codigo, codigo_solicitud) {
        if (confirm ('¿Desea eliminar el respaldo?')) {
            if(codigo_solicitud != "" && codigo_solicitud != 0){
                document.getElementById("txt_tipo_lista").value = 13; //Verificar respaldos
                document.getElementById("txt_accion").value = 6;
                document.getElementById("txt_resp_soli_codi").value = codigo_solicitud
                document.getElementById("txt_codigo_respaldo").value = codigo                              
                document.formulario.action = "respaldo_acciones.php";
                document.formulario.submit();
            }
            else{
                nuevoAjax('div_eliminar_respaldo', 'POST', 'backup_usuarios_eliminar.php', 'txt_resp_codi=' + codigo);
            }
        }
        buscar_respaldos();
    }
    function buscar_respaldos() {
        cmb_institucion = document.getElementById('cmb_institucion').value;       
        cmb_estado = document.getElementById('cmb_estado').value;
        txt_fecha_inicio_sol = document.getElementById('txt_fecha_inicio_sol').value;
        txt_fecha_fin_sol = document.getElementById('txt_fecha_fin_sol').value;
        nuevoAjax('div_buscar_respaldos', 'GET', 'backup_usuarios_estado_buscar.php', 
        'cmb_institucion=' + cmb_institucion+
        '&cmb_estado=' + cmb_estado+
        '&txt_fecha_inicio_sol=' + txt_fecha_inicio_sol+
        '&txt_fecha_fin_sol=' + txt_fecha_fin_sol);
    }

    function descargar_respaldo(codigo, solicitud) {
        windowprops = "top=50,left=50,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=700,height=500";
        url = 'backup_usuarios_descargar_archivos.php?codigo_backup=' + codigo+'&resp_soli_codi='+solicitud;
        ventana = window.open(url , "descargar_respaldo_" + codigo, windowprops);
        ventana.focus();
    }

    function seleccionar_solicitud(codigo, lista) {
         var_envio='respaldo_informacion.php?txt_resp_soli_codi='+codigo+'&tipo_ventana=popup&txt_tipo_lista='+lista;
         window.open(var_envio,codigo,"height=615,width=900,scrollbars=yes");
    }

</script>
<body>
  <div id="spiffycalendar" class="text"></div>
  <center>
    <form name="formulario" action="" method="post">
        <input type="hidden" name="txt_tipo_lista" id="txt_tipo_lista" value="0" size="20">
        <input type="hidden" name="txt_accion" id="txt_accion" size="20" maxlength="10" value="0">
        <input type="hidden" name="txt_resp_soli_codi" id="txt_resp_soli_codi" size="20" value="0">
        <input type="hidden" name="txt_codigo_respaldo" id="txt_codigo_respaldo" size="20" value="0">
        <table width="100%" align="center" class=borde_tab border="0">
            <tr>
                <td width="100%" class="titulos5">
                  <center>
                    <br>Estado de los respaldos<br>&nbsp;
                  </center>
                </td>
            </tr>
        </table>
        <br>
        <table width="100%" align="center" class=borde_tab border="0">
            <tr>
                <td width="10%" class="titulos2">Instituci&oacute;n:: </td>
                <td width="40%" class="listado2">
<?
                $sql="select inst_nombre, inst_codi from institucion where inst_codi<>0 order by 1 asc";
                $rs=$db->conn->query($sql);
                if($rs) print $rs->GetMenu2("cmb_institucion", 0, "0:&lt;&lt; Todas las instituciones &gt;&gt;", false,"","class='select' id='cmb_institucion'" );
?>
                </td>
                <td rowspan="5" width="25%" class="titulos5" valign="middle">
                    <center><input type='button' value='Buscar' name='btn_buscar' class='botones' onClick='buscar_respaldos();'></center>
                </td>
                <tr>
                <td width="10%" class="titulos2">Estado: </td>
                <td width="40%" class="listado2">
                    <select name="cmb_estado" id="cmb_estado" class="select">
                        <option value="0"><< Todos los Estados >></option>
                        <option value="1">Pendiente</option>
                        <option value="2">Finalizado</option>
                        <option value="3">Eliminado</option>                       
                    </select>
                </td>
                <tr>
            <td width="10%" class="titulos2">Fecha Inicio: </td>
            <td width="40%" class="listado2">
                <script type="text/javascript">
                   dateAvailable1.dateFormat="yyyy-MM-dd";
                   dateAvailable1.writeControl();
                </script>
            </td>           
            </tr>
            <tr>
            <td width="10%" class="titulos2">Fecha Fin: </td>
            <td width="40%" class="listado2">
                <script type="text/javascript">
                   dateAvailable2.dateFormat="yyyy-MM-dd";
                   dateAvailable2.writeControl();
                </script>
            </td>
            </tr>
        </table>        
        <div id='div_eliminar_respaldo'></div>      
        <div id='div_buscar_respaldos'></div>
        <div id='div_descargar_respaldo'></div>
        <br>
        <input type="button" name="btn_cancelar" value="Regresar" class="botones" 
               onClick="window.location='backup_usuarios_menu.php';">
      </form>
      <script  language="JavaScript" type="text/JavaScript">buscar_respaldos();</script>
    </center>
  </body>
</html>
