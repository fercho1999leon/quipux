<?php
/**  Programa para el manejo de gestion documental, oficios, memorandos, circulares, acuerdos
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

if($_SESSION["usua_codi"]!=0) die ("Usted no tiene los permisos suficientes para acceder a esta p&aacute;gina.");

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();
include_once "$ruta_raiz/js/ajax.js";

$sql = "select inst_nombre, inst_codi from institucion where inst_codi>1 order by 1";
$rs=$db->conn->query($sql);
$menu_inst_origen = $rs->GetMenu2("txt_inst_origen", "0", "0:&lt;&lt seleccione &gt;&gt;", false,"","id='txt_inst_origen' class='select' onchange='fjs_cargar_combo_areas(this.value);'");
$rs->Move(0);
$menu_inst_destino = $rs->GetMenu2("txt_inst_destino", "0", "0:&lt;&lt seleccione &gt;&gt;", false,"","id='txt_inst_destino' class='select'");


?>
    <script type="text/javascript">
        var proceso = new Array();
        proceso[0] = 'Deshabilitar instituciones y sacar a los usuarios conectados del sistema';
        proceso[1] = 'Mover documentos a la instituci&oacute;n destino';
        proceso[2] = 'Mover listas de usuarios a la instituci&oacute;n destino';
        proceso[3] = 'Mover usuarios a la instituci&oacute;n destino';
        proceso[4] = 'Encerar las secuencias de los documentos';
        proceso[5] = 'Quitar relaciones de plantillas, numeraci&oacute;n de documentos y archivo';
        proceso[6] = 'Mover &aacute;reas a la instituci&oacute;n destino';
        proceso[7] = 'Habilitar instituciones y permitir el acceso a los usuarios';

        var pagina = new Array();
        pagina[0] = 'instituciones_fusionar_desactivar.php';
        pagina[1] = 'instituciones_fusionar_documentos.php';
        pagina[2] = 'instituciones_fusionar_listas.php';
        pagina[3] = 'instituciones_fusionar_usuarios.php';
        pagina[4] = 'instituciones_fusionar_encerar_secuencias.php';
        pagina[5] = 'instituciones_fusionar_quitar_relaciones.php';
        pagina[6] = 'instituciones_fusionar_areas.php';
        pagina[7] = 'instituciones_fusionar_activar.php';

        var num_proceso = 0;
        var total_registros = 0;
        var parametros;
        
        function fjs_fusionar_instituciones() {
            inst_origen = document.getElementById("txt_inst_origen").value || '0';
            inst_destino = document.getElementById("txt_inst_destino").value || '0';
            if (inst_origen == '0') {
                alert ('Por favor seleccione la institución origen');
                return;
            }
            if (inst_destino == '0') {
                alert ('Por favor seleccione la institución destino');
                return;
            }
            if (inst_origen == inst_destino) {
                alert ('Por favor seleccione dos instituciones diferentes');
                return;
            }

            if (confirm('¿Está seguro de realizar esta acción?\nPor favor verifique que los datos ingresados son los correctos antes de continuar.')) {

//            confirm('¿Desea migrar todos sus datos desde la institución ' + document.getElementById("txt_inst_destino").options[document.getElementById("txt_inst_destino").selectedIndex].text
//                        + '\n hacia la institución ' + document.getElementById("txt_inst_origen").options[document.getElementById("txt_inst_origen").selectedIndex].text + '?')) {

                depe_origen = document.getElementById("txt_depe_origen").value;
                if(document.getElementById("chk_inst_adscrita").checked) inst_adscrita=1; else inst_adscrita=0;
                if(document.getElementById("chk_encerar_secuencias").checked) chk_encerar_secuencias=1; else chk_encerar_secuencias=0;
                txt_observacion = document.getElementById("txt_observacion").value;

                parametros = 'txt_inst_origen=' + inst_origen + '&txt_inst_destino=' + inst_destino +
                             '&txt_depe_origen=' + depe_origen + '&chk_inst_adscrita=' + inst_adscrita +
                             '&chk_encerar_secuencias=' + chk_encerar_secuencias + '&txt_observacion=' + txt_observacion;

                fjs_inicializar_proceso();
                fjs_ejecutar_proceso();
            } else {
                return;
            }
        }

        function fjs_inicializar_proceso() {
            document.getElementById("txt_inst_origen").disabled=true;
            document.getElementById("txt_inst_destino").disabled=true;
            document.getElementById("txt_depe_origen").disabled=true;
            document.getElementById("chk_inst_adscrita").disabled=true;
            document.getElementById("chk_encerar_secuencias").disabled=true;
            document.getElementById("txt_observacion").disabled=true;

            document.getElementById("btn_aceptar").disabled=true;
            document.getElementById("btn_cancelar").disabled=true;
            document.getElementById("tr_botones").style.display = 'none';
            tabla = '<h3><i>Estado de la migraci&oacute;n</i></h3><br>\n';
            tabla += '<table width="100%" border="0" cellspacing="5" cellpadding="0" class="borde_tab">\n';
            tabla += '<tr><th width="15%">Hora Inicio</th><th width="35%">Acci&oacute;n</th><th width="35%">Mensaje</th><th width="15%">Estado</th></tr>\n';
            for (i=0; i<proceso.length; ++i) {
                tabla += '<tr><td id="td_fecha_'+i+'"></td><td>'+proceso[i]+'</td><td id="td_mensaje_'+i+'"><td id="td_estado_'+i+'"></tr>\n';
            }
            tabla += '<table>';
            document.getElementById("div_proceso").innerHTML = tabla;

            return;
        }

        function fjs_ejecutar_proceso() {
            if (num_proceso>=proceso.length) {
                alert ('La ejecución del proceso de migración ha finalizado correctamente.');
                return;
            }
            if (trim(document.getElementById("td_fecha_"+num_proceso).innerHTML) == '') {
                fecha = new Date();
                h=("0" + fecha.getHours()).slice (-2);
                m=("0" + fecha.getMinutes()).slice (-2);
                s=("0" + fecha.getSeconds()).slice (-2);
                document.getElementById("td_fecha_"+num_proceso).innerHTML = h+":"+m+":"+s;
            }
            document.getElementById("td_estado_"+num_proceso).innerHTML = '<b>Ejecutando</b>';
            nuevoAjax('div_ejecutar_proceso', 'POST', pagina[num_proceso], parametros, 'fjs_finalizar_proceso()');
        }

        function fjs_finalizar_proceso() {
            var respuesta = document.getElementById('div_ejecutar_proceso').innerHTML;
            if (respuesta.indexOf('-') >= 0) {
                datos = respuesta.split('-', 2);
                document.getElementById("td_mensaje_"+num_proceso).innerHTML = datos[1];
                if (trim(datos[0])=='Error') {
                    document.getElementById("td_estado_"+num_proceso).innerHTML = '<font color="red"><b>Error</b></font>';
                    alert ('Existieron errores en la migración de datos.\nError: '+datos[1]);
                    return;
                }
                document.getElementById("td_estado_"+num_proceso).innerHTML = 'Finalizado';
                ++num_proceso;
                total_registros = 0;
                fjs_ejecutar_proceso();
                return;
            }
            if (trim(respuesta) == 'Finalizado') {
                document.getElementById("td_estado_"+num_proceso).innerHTML = 'Finalizado';
                ++num_proceso;
                total_registros = 0;
                fjs_ejecutar_proceso();
            } else {
                registros = parseInt(respuesta, 10);
                if (isNaN(registros)) registros = 0;
                total_registros += registros;
                document.getElementById("td_mensaje_"+num_proceso).innerHTML = 'Se han actualizado '+total_registros+' registros.';
                fjs_ejecutar_proceso();
            }
            return;
        }

        function fjs_cargar_combo_areas(inst_codi) {
        document.getElementById('tr_area_origen').style.display='';
            nuevoAjax('div_area_origen', 'POST', 'instituciones_fusionar_combos.php', 'txt_inst_codi='+inst_codi);
        }
    </script>
<body>
  <center>
    <table width="98%" border="0" cellspacing="5" cellpadding="0" class="borde_tab">
        <tr>
            <th width="100%" colspan="2"><center>Fusionar dos instituciones</center></th>
        </tr>
        <tr>
            <td class="listado1" width="30%">
                Instituci&oacute;n Origen
            </td>
            <td class="listado1" width="70%">
                <?=$menu_inst_origen?>
            </td>
        </tr>
        <tr id="tr_area_origen" style="display: none;">
            <td class="listado1">
                &Aacute;rea Origen
            </td>
            <td class="listado1">
                <div id="div_area_origen"></div>
            </td>
        </tr>
        <tr><td colspan="2">&nbsp;</td></tr>
        <tr>
            <td class="listado1">
                Instituci&oacute;n Destino
            </td>
            <td class="listado1">
                <?=$menu_inst_destino?>
            </td>
        </tr>
        <tr><td colspan="2">&nbsp;</td></tr>
        <tr>
            <td class="listado1" colspan="2">
                <input type="checkbox" id="chk_inst_adscrita" value="1">
                &nbsp;Migrar como instituci&oacute;n adscrita
            </td>
        </tr>
        <tr>
            <td class="listado1" colspan="2">
                <input type="checkbox" id="chk_encerar_secuencias" value="1">
                &nbsp;Encerar secuencias de los documentos
            </td>
        </tr>
        <tr>
            <td class="listado1" valign="middle">
                Observaci&oacute;n
            </td>
            <td class="listado1">
                <textarea id="txt_observacion" cols="100" class="tex_area" rows="3"></textarea>
            </td>
        </tr>
        <tr id="tr_botones">
            <td class="listado1" colspan="2">
                <center>
                    <br>
                    <input  name="btn_aceptar"  id="btn_aceptar"  type="button" class="botones" value="Aceptar" title="Inicia el proceso para fusionar las dos instituciones" onClick="fjs_fusionar_instituciones()"/>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <input  name="btn_cancelar" id="btn_cancelar" type="button" class="botones" value="Regresar" title="Regresa al men&uacute; de administraci&oacute;n" onClick="location='../formAdministracion.php'"/>
                    <br>&nbsp;
                </center>
            </td>
        </tr>
    </table>
    <br>
    <div id="div_proceso" style="width: 98%"></div>
    <br>
    <div id="div_ejecutar_proceso" style="display: none"></div>
  </center>
</body>
</html>

