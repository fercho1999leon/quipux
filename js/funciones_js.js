function detectarPhone() {
    var navegador = navigator.userAgent.toLowerCase();
    if ( navigator.userAgent.match(/iPad/i) != null) { //detectar ipad
        return 2;
    } else {//detectar phone
        if( navegador.search(/iphone|ipod|blackberry|android/) > -1 ) {
            return 1;
        } else {
            return 0;
        }
    }
}

function trim(s) {
    s = s.toString();
    return s = s.replace(/^\s+|\s+$/gi, '');
}

function numeroCarecteresDePara(obj,nrocaracteres,nombre,espacios) {
    valor =  obj.value;
    divA = 'div_' + nombre;
    if (espacios==1)
        validar = trimCar(valor,nrocaracteres);
    if (validar==1) {
        document.getElementById(divA).style.display='none';
        return;
    } else {
        document.getElementById(nombre).value='';
        document.getElementById(divA).style.display='';
    }
}

function numeroCarecteresDiv(obj,nrocaracteres,nombre,espacios) {
    valor =  obj.value;
    divA = 'div_'+nombre;
    if (espacios==1)
        validar = trimCar(valor,nrocaracteres);
    if (validar==1) {
        document.getElementById(divA).style.display='none';
        texto_er = document.getElementById('txt_nombre_texto_error').value;
        texto_er = texto_er.replace(','+nombre,'');
        document.getElementById('txt_nombre_texto_error').value=texto_er;
        return;
    } else {
        document.getElementById(divA).style.display='';
        buscarCadena(nombre);
    }
}
function evento_ver(e,obj,nrocaracteres,nombre,espacios) {
    if (e.keyCode == 13)
        numeroCarecteresDePara(obj,nrocaracteres,nombre,espacios);
}

function trimCar(s,nrocaracteres) {
    if (trim(s)=='')
        return 1;
    var cadena = s.split(' ');
    for (i=0; i<cadena.length; ++i) {
        if (cadena[i].length >= nrocaracteres) return 1;
    }
        return 0;
}

function buscarCadena(nombre) {
    var bandera=0;
    var textoEr = document.getElementById('txt_nombre_texto_error').value;
    if (textoEr!='') {
        var s= textoEr.split(',');
        for (i=1; i<s.length;i++) {
            if (s[i]==nombre) {
                cade = nombre;
                bandera=1;
            }
        }
    }
    if (bandera==0)
        document.getElementById('txt_nombre_texto_error').value +=','+nombre;

}


// Crear DIVS que funciones como si fuesen popups
function fjs_popup_activar (titulo, url, parametros, script_onload) {
    fjs_popup_crear_divs ();
    document.getElementById('span_popup_titulo').innerHTML = titulo;
    document.getElementById('div_popup_bloquear_pantalla').style.display = '';
    document.getElementById('div_popup_pantalla_pequena').style.display = '';
    if (url != '')
        nuevoAjax('div_popup_pantalla_tabajo', 'POST', url, parametros, script_onload);
    return;
}

function fjs_popup_cerrar () {
    document.getElementById('div_popup_bloquear_pantalla').style.display = 'none';
    document.getElementById('div_popup_pantalla_pequena').style.display = 'none';
}

function fjs_popup_crear_divs () {
    var texto = '';
    try {
        document.getElementById('div_popup_pantalla_tabajo').innerHTML = '';
    } catch (e) {
        texto = '<div id="div_popup_bloquear_pantalla" style="width: 100%; height: 100%; z-index: 1000; position: fixed; top: 0; left: 0; opacity:0.3; filter:alpha(opacity=30); background-color: black; display: none;"></div>\n' +
                '    <div id="div_popup_pantalla_pequena" style="width: 80%; height: 80%; z-index: 1001; position: fixed; top: 5%; left: 10%; background-color: white; border: #333333 2px solid; display: none">\n' +
                '        <div id="div_popup_titulo" style="font-weight: bold; text-align: center; font-size: small; color: #FFFFFF; background-color:#006394; width: 100%; height: 20px; position: relative;">\n' +
                '            <table width="100%" border="0" cellpadding="0" cellspacing="0">\n' +
                '               <tr height="18px">\n' +
                '                   <td width="3%">&nbsp;</td>\n' +
                '                   <td width="94%" style="font-weight: bold; text-align: center; font-size: small; color: #FFFFFF; vertical-align: middle"><span id="span_popup_titulo"></span></td>\n' +
                '                   <td width="3%" align="right" valign="bottom"><img src="data:image/gif;base64,R0lGODlhDwAPANU/AOh1ceFlZPKKf8G6uO64ublNVMR5gbVKU+JqaM1VWvF8dqeWlOlradNVWcFybNNaXqNEQqycmuRubPvZsMmJdfSzne6vmbNnXv36+8JqaalAUJo+OuOSgveNhKlBT7xRWN1kZMd+e+BoZ4R1dMtOUt98gKuKiLFDVa0/Sr1IS7tLWvWTiMuCiPbBwNRdYvvYv/a0sv/etPO+valMSMVTXc9aXuJrav+0ndlgYuWhpM5aXsF0bKlAT7pKWv///////yH5BAEAAD8ALAAAAAAPAA8AAAa1wB8r49gZjztHhvULCW6T2GQ6ld4EoYKgw6FULOAKhdMRFD4KRWh0WQlWl1Eo/SkAAjDfYqaYLXwwAQAFBRISNjI+ERsRPjI2hoQINjYBLT4DPi0BlAiEIiIIDBCZAxAMCKAFByAgo40viqetBwc4IBB/BA0EehAgOLUuKSY+uw+8PiYpLrU1CSU5DQ/UDTklCTUHKDQJCSQ64eEk3jQoBicqPevs7ConBj8GGjwe9Tz4+BrxQQA7" onclick="fjs_popup_cerrar();">&nbsp;</td>\n' +
                '               </tr>\n' +
                '            </table>\n' +
                '        </div>\n' +
                '        <div id="div_popup_pantalla_tabajo" style="background-color:white; width: 100%; height: 94%; position: relative; overflow: auto;"></div>\n' +
                '    </div>';
        try {
            document.body.innerHTML += texto;
        } catch (e) {}
    }
    return;
}

function fjs_validar_ingreso_teclas_funcion(event) {
    tecla = event.keyCode;
    switch (tecla) {
        case 8:  //Backspace
        case 13: //Enter
        case 46: //Suprimir
        case 37: //Flecha Izquierda
        case 38: //Flecha Arriba
        case 39: //Flecha Derecha
        case 40: //Flecha Abajo
            return true;
            break;
        default:
            return false;
            break;
    }
    return false;
}

function fjs_validar_ingreso_numeros(event) {
    var tecla = event.which;
    if (tecla >= 48 && tecla <= 57) return true; // Del 0 al 9
    if (tecla == 46 && event.currentTarget.value.indexOf('.') < 0) return true; // .
    if (tecla == 45 && event.currentTarget.value.indexOf('-') < 0 && event.currentTarget.selectionStart == 0) return true; // -
    return fjs_validar_ingreso_teclas_funcion(event);
}

function fjs_validar_ingreso_numeros_enteros(event) {
    var tecla = event.which;
    if (tecla >= 48 && tecla <= 57) return true; // Del 0 al 9
    return fjs_validar_ingreso_teclas_funcion(event);
}


