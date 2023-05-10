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

//Tipos de anexos permitidos
$rs = $db->conn->query("select anex_tipo_ext from anexos_tipo where anex_tipo_estado=1");
$extenciones_archivos = "";
while(!$rs->EOF) {
    $extenciones_archivos .= strtolower(trim($rs->fields["ANEX_TIPO_EXT"])) . ",";
    $rs->MoveNext();
}

$parametros_post = "";
if (isset ($_POST["asocImgRad"])) $parametros_post = "&asocImgRad=".$_POST["asocImgRad"];
?>
<script type="text/javascript">
    var ruta_raiz = '<?=$ruta_raiz?>';
    var radi_nume = '<?=$radi_nume?>';
    var extenciones_archivos = '<?=$extenciones_archivos?>';
    var timer_id_anexos_esperar_carga_archivo = 0;
    var chk_asociar_imagen = '<?=$chk_asociar_imagen?>';
    var flag_anexos_estado_carga_archivos = false;
    var parametros_post = '<?=$parametros_post?>';

    // Validamos que no se esten subiendo archivos y el usuario intente salir de la página
    window.onbeforeunload = function () {
        if (flag_anexos_estado_carga_archivos) {
            return "Aún no se han cargado todos los archivos. \nSi sale de esta página sus datos se perderán.";
        }
        return;
    }

    function anexos_cargar_div_lista_anexos () {
        div = 'div_anexos_lista_archivos';
        nuevoAjax(div, 'POST', ruta_raiz+'/anexos/anexos_lista.php', 'radi_nume=' + radi_nume + parametros_post + '&chk_asociar_imagen='+chk_asociar_imagen, 'fjs_popup_crear_divs();');
        return;
    }

    function anexos_cargar_div_nuevo_anexo () {
        nuevoAjax('div_anexos_cargar_nuevo_archivo', 'POST', ruta_raiz+'/anexos/anexos_nuevo.php', 'chk_asociar_imagen=' + chk_asociar_imagen + parametros_post);
        return;
    }

    function anexos_validar_tipo_archivo(id_archivo) {
        var nombre_archivo = document.getElementById('fil_archivo_nuevo_'+id_archivo).value.toLowerCase();
        var lista_extensiones = extenciones_archivos.split(',');
        nombre_archivo = nombre_archivo.replace(/.p7m/g, "");
        var tmp = nombre_archivo.split('.');
        var extension = tmp[tmp.length-1];

        for (i = 0; i < lista_extensiones.length; i++) {
            if (lista_extensiones[i].toLowerCase() == extension) {
                if (parseInt(id_archivo)<9)
                    document.getElementById('tr_archivo_nuevo_'+(parseInt(id_archivo)+1).toString()).style.display = '';

                document.getElementById('fil_archivo_nuevo_'+id_archivo).style.display = 'none';
                document.getElementById('img_archivo_nuevo_borrar_'+id_archivo).style.display = '';
                document.getElementById('lbl_archivo_nuevo_'+id_archivo).innerHTML = document.getElementById('fil_archivo_nuevo_'+id_archivo).value;
                return true;
            }
        }
        alert ('No está permitido anexar archivos con extensión "'+extension+'".\nConsulte con su administrador del sistema.');
        document.getElementById('fil_archivo_nuevo_'+id_archivo).value = '';
        return false;
    }

    function fjs_anexos_borrar_archivo_nuevo(id_archivo) {
        document.getElementById('lbl_archivo_nuevo_'+id_archivo).innerHTML = '';
        document.getElementById('fil_archivo_nuevo_'+id_archivo).value = '';
        document.getElementById('fil_archivo_nuevo_'+id_archivo).style.display = ''
        document.getElementById('img_archivo_nuevo_borrar_'+id_archivo).style.display = 'none';
        return;
    }

    function fjs_anexos_validar_chk_asociar_imagen(id_archivo) {
        var i=0;
        for ( i=0 ; i<10 ; ++i ) {
            if (i != id_archivo)
                document.getElementById('chk_asociar_imagen_'+i).checked = false;
        }
        return true;
    }

    function anexos_cargar_archivo_nuevo() {
        var nombre_archivo = '';
        var i = 0;
        var flag_validar_archivos = false;
        for (i=0 ; i<10 ; ++i) {
            if (trim(document.getElementById('fil_archivo_nuevo_'+i.toString()).value) != '') {
                nombre_archivo += '<br><b>&quot;' + document.getElementById('fil_archivo_nuevo_'+i.toString()).value+'&quot;</b>';
                if (anexos_validar_tipo_archivo(i.toString())) flag_validar_archivos = true;
            }
        }

        if (flag_validar_archivos) {
            document.getElementById('lbl_nombre_archivo_nuevo').innerHTML = nombre_archivo;
            document.getElementById('div_anexos_cargar_nuevo_archivo').style.display = 'none';
            document.getElementById('div_anexos_cargar_nuevo_archivo_estado').style.display = '';
            document.getElementById('txt_radi_nume').value = radi_nume;
            document.getElementById('frm_anexos_cargar_nuevo_archivo').action = ruta_raiz+'/anexos/anexos_grabar.php';
            document.getElementById('frm_anexos_cargar_nuevo_archivo').submit();
            flag_anexos_estado_carga_archivos = true;
        } else {
            alert ('Por favor seleccione los archivos que desea subir.');
        }
        return;
    }

    function anexos_cargar_archivo_nuevo_finalizar() {
        flag_anexos_estado_carga_archivos = false;
        document.getElementById('lbl_nombre_archivo_nuevo').innerHTML ='';
        document.getElementById('div_anexos_cargar_nuevo_archivo').style.display = '';
        document.getElementById('div_anexos_cargar_nuevo_archivo_estado').style.display = 'none';
        anexos_cargar_div_lista_anexos ();
        anexos_cargar_div_nuevo_anexo ();
        return;
    }


    function anexos_descargar_archivo(radicado, anex_codigo, arch_tipo, tipo_descarga) {
        path_descarga = ruta_raiz+'/anexos/anexos_descargar_archivo.php?radi_nume='+radicado+'&anex_codigo=' + anex_codigo + '&arch_tipo=' + arch_tipo;
        if (tipo_descarga && tipo_descarga=='embeded') {
            path_descarga += '&tipo_descarga=embeded';
            if (fjs_verificar_plugin_navegador ('acrobat')) path_descarga += '_ar';
            fjs_popup_activar ('Vista Previa', '', '');
            document.getElementById('div_popup_pantalla_tabajo').innerHTML = '<center><iframe name="ifr_anexos_mostrar_archivo" id="ifr_anexos_mostrar_archivo" ' +
                'style="width:99%; height:99%; overflow: auto; border: 1;" src="' + path_descarga + '">' +
                'Su navegador no soporta iframes, por favor actualicelo.</iframe></center>';
        } else {
            path_descarga += '&tipo_descarga=download';
            document.getElementById('ifr_descargar_archivo').src=path_descarga;
        }
        return;
    }

    function anexos_verificar_firma(radicado, anex_codigo) {
        var url = ruta_raiz+'/anexos/anexos_verificar_firma.php';
        var parametros = 'radi_nume=' + radicado + '&anex_codigo=' + anex_codigo;
        fjs_popup_activar ('Verificación de Firma Electrónica', url, parametros);
        return;
    }

    function fjs_anexos_acciones(radicado, anexo, accion){
        var funcion_ejecutar = '';
        var parametros = '';
        switch (accion) {
            case '1':
                if (!confirm('Está seguro de borrar este archivo anexo?')) return;
                document.getElementById('tr_anexo_'+anexo).style.display = 'none';
                document.getElementById('tr_anexo_detalle_'+anexo).style.display = 'none';
                break;
            case '2':
                try {
                    modificar_opcion_mostrar('imagen_'+anexo, 1);
                } catch (e) {}
                break;
            case '3':
                modificar_opcion_mostrar('imagen_'+anexo, 1);
                break;
            case '4':
//                    alert ('Este documento deberá ser incluido en el archivo de la institución.');
                modificar_opcion_mostrar('medio_'+anexo, 1);
                break;
            case '5':
                modificar_opcion_mostrar('medio_'+anexo, 1);
                break;
            case '6':
                modificar_opcion_mostrar('descripcion_'+anexo, 1);
                parametros = '&txt_descripcion='+document.getElementById('txt_descripcion_'+anexo).value;
                break;
            default:
                return;
        }

        if (accion==2)
            funcion_ejecutar = 'anexos_cargar_div_lista_anexos ();';

        nuevoAjax('div_anexos_acciones', 'POST', ruta_raiz + '/anexos/anexos_acciones.php', 'radi_nume='+radicado+'&anexo='+anexo+'&accion='+accion+parametros + parametros_post, funcion_ejecutar);
        return;
    }

    function modificar_opcion_mostrar(opcion, mostrar) {
        try {
            document.getElementById("span_"+opcion).innerHTML=document.getElementById("txt_"+opcion).options[document.getElementById("txt_"+opcion).selectedIndex].text;
        } catch (e) {
            document.getElementById("span_"+opcion).innerHTML=document.getElementById("txt_"+opcion).value;
        }
        if (mostrar==2) { // Mostrar combo para editar opcion
            document.getElementById("img_"+opcion).style.display = 'none';
            document.getElementById("span_"+opcion).style.display = 'none';
            document.getElementById("txt_"+opcion).style.display = '';
            try {
                document.getElementById("img_guardar_"+opcion).style.display = '';
            } catch (e) {}
        } else { // Ocultar combo para editar opcion
            document.getElementById("img_"+opcion).style.display = '';
            document.getElementById("span_"+opcion).style.display = '';
            document.getElementById("txt_"+opcion).style.display = 'none';
            try {
                document.getElementById("img_guardar_"+opcion).style.display = 'none';
            } catch (e) {}
        }
    }

    function fjs_anexos_mostrar_detalle_archivo (anex_codigo) {
        if (document.getElementById('tr_anexo_detalle_'+anex_codigo).style.display=='none') {
            document.getElementById('tr_anexo_detalle_'+anex_codigo).style.display='';
            document.getElementById('img_anexos_ocultar_detalle_'+anex_codigo).style.display='';
            document.getElementById('img_anexos_mostrar_detalle_'+anex_codigo).style.display='none';
        } else {
            document.getElementById('tr_anexo_detalle_'+anex_codigo).style.display='none';
            document.getElementById('img_anexos_ocultar_detalle_'+anex_codigo).style.display='none';
            document.getElementById('img_anexos_mostrar_detalle_'+anex_codigo).style.display='';
        }
        return;
    }

    if(typeof(fjs_radicado_descargar_archivo) != 'function')  {
        function fjs_radicado_descargar_archivo(radicado, anex_codigo, arch_tipo, tipo_descarga) {
            path_descarga = ruta_raiz+'/anexos/anexos_descargar_archivo.php?radi_nume='+radicado+'&anex_codigo=' + anex_codigo + '&arch_tipo=' + arch_tipo + '&tipo_descarga=' + tipo_descarga;
            if (tipo_descarga=='embeded')
                document.getElementById('ifr_mostrar_archivo').src=path_descarga;
            else
                document.getElementById('ifr_descargar_archivo').src=path_descarga;
            return;
        }
    }
</script>
