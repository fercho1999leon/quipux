<script type="text/javascript">
    var arr_flag_respuesta_ejecucion_nuevo_ajax_objeto = new Array(); // Lista de objetos (div o span) que se estan recargando
    var arr_flag_respuesta_ejecucion_nuevo_ajax_funcion = new Array(); // lista de funciones que se ejecutaran luego de recargar los objetos
    var arr_timer_id_esperar_respuesta_ejecucion_nuevo_ajax = new Array(); // Temporizador

    // Esta función hace un llamado a otra página utilizando ajax
    // objeto: es el id del objeto (div, span, etc.) donde se cargara la respuesta del llamado
    // metodo: GET o POST
    // url: Es la dirección de la página que se está llamando
    // variables: son los parámetros que se envian a la página
    // script_respuesta: ejecuta un script o una función javascript cuando finalizó la carga de la información
    function nuevoAjax(objeto, metodo, url, variables, script_respuesta) {
        var xmlhttp = false;
        var codigo_validar_respuesta = '';
        script_respuesta = script_respuesta || '';
        try {
            xmlhttp = new ActiveXObject('Msxml2.XMLHTTP');
        } catch (e) {
            try {
                xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
            } catch (E) {
                xmlhttp = false;
            }
        }

        if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
            xmlhttp = new XMLHttpRequest();
        }

        //Creo una caja de texto en base al la que se validará si se ejecutó el codigo ajax (solo si se debe ejecutar alguna función)
        if (script_respuesta != '') codigo_validar_respuesta = '<input type="hidden" id="txt_flag_respuesta_ejecucion_nuevo_ajax_'+objeto+'" value="0">';
        metodo = metodo.toUpperCase();
        if (metodo=='GET') {
            xmlhttp.open('GET', url+'?'+variables, true);
            xmlhttp.onreadystatechange=function() {
                if (xmlhttp.readyState==4) {
                    try {
                        // añado al final de la respuesta ajax el código de validación
                        document.getElementById(objeto).innerHTML = xmlhttp.responseText + codigo_validar_respuesta;
                    } catch (e) { }
                }
            }
            xmlhttp.send(null);
        }

        if (metodo=='POST') {
            xmlhttp.open('POST', url, true);
            xmlhttp.onreadystatechange=function() {
                if (xmlhttp.readyState==4) {
                    try {
                        // añado al final de la respuesta ajax el código de validación
                        document.getElementById(objeto).innerHTML = xmlhttp.responseText + codigo_validar_respuesta;
                    } catch (e) { }
                }
            }
            xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xmlhttp.send(variables);
        }

        //Valido si hubo alguna respuesta luego de la ejecución del script ajax
        if (script_respuesta != '') {
            // guardo en arreglos el objeto que se esta recargando y la función javascript que se debe ejecutar
            var id_objeto = arr_flag_respuesta_ejecucion_nuevo_ajax_objeto.length;
            var i;
            for (i=0; i<arr_flag_respuesta_ejecucion_nuevo_ajax_objeto.length; ++i) {
                if (arr_flag_respuesta_ejecucion_nuevo_ajax_objeto[i] == objeto)
                    id_objeto = i;
            }
            arr_flag_respuesta_ejecucion_nuevo_ajax_objeto[id_objeto] = objeto;
            arr_flag_respuesta_ejecucion_nuevo_ajax_funcion[id_objeto] = script_respuesta;
            // inicializo un temporizador que espera a que responda la petición ajax
            arr_timer_id_esperar_respuesta_ejecucion_nuevo_ajax[id_objeto] = setTimeout("esperar_respuesta_ejecucion_nuevo_ajax("+id_objeto+")", 300);
        }
        return;
    }

    // función que maneja los temporizadores que esperan la respuesta de la petición ajax
    function esperar_respuesta_ejecucion_nuevo_ajax(id) {
        var objeto; //Nombre del objeto (div o span) que se esta recargando
        var flag_detener_ejecucion = true;
//        var i = 0;
        //
//        for (i=0; i<arr_flag_respuesta_ejecucion_nuevo_ajax_objeto.length; ++i) {
//            if (arr_flag_respuesta_ejecucion_nuevo_ajax_objeto != '') {
                objeto = arr_flag_respuesta_ejecucion_nuevo_ajax_objeto[id];
                try {
                    // Valido si se cargó ya la caja de texto de validación; en caso de que aún no se haya recargado el objeto se levantara una excepción
                    //  porque la caja de texto "txt_flag_respuesta_ejecucion_nuevo_ajax_objeto" no existe y el temporizador se vuelve a ejecutar
                    document.getElementById('txt_flag_respuesta_ejecucion_nuevo_ajax_'+objeto).value = 1;
                    padre = document.getElementById('txt_flag_respuesta_ejecucion_nuevo_ajax_'+objeto).parentNode;
                    padre.removeChild(document.getElementById('txt_flag_respuesta_ejecucion_nuevo_ajax_'+objeto)); //Elimino la caja de validación
                } catch (e) {
                    // En caso que no se haya cargado aún el pedido ajax, cambio la bandera para que se vuelva a ejecutar el temporizador
                    flag_detener_ejecucion = false;
                }
//            }
//        }

        if (flag_detener_ejecucion==true) {
            // elimino los temporizadores si ya se recargaron todos los objetos
            clearTimeout(arr_timer_id_esperar_respuesta_ejecucion_nuevo_ajax[id]);
            eval(arr_flag_respuesta_ejecucion_nuevo_ajax_funcion[id]); // ejecuto la función javascript solicitada
        } else {
            // Ejecuto de nuevo el temporizador si existen objetos por recargar
            timer_id_esperar_respuesta_ejecucion_nuevo_ajax = setTimeout("esperar_respuesta_ejecucion_nuevo_ajax("+id+")", 300);
        }
        return;
    }
</script> 

