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
*
*
* Este archivo contiene los scripts para validar datos al crear usuarios y ciudadanos en el sistema
*
**/


// Valida el numero de cedula del usuario;
// recibe: numero (string) - No. de cedula
// retorna: (boolean)
function validarCedula (numero)
{
    var suma = 0;
    var residuo = 0;
    var pri = false;
    var pub = false;
    var nat = false;
    var numeroProvincias = 24;
    var modulo = 11;

    // Verifico que el número de cédula tenga 10 o 13 digitos
    numero = numero.replace(/^\s+|\s+$/gi, '');
    numero = numero.replace(' ', '');
    numero = numero.replace('-','');
    if (numero.length!=10 && numero.length!=13)
        return false;
    /* Verifico que el campo no contenga letras */
    if (isNaN(numero))
        return false;
    valIf = numero.substr(0,2);
    if (parseInt(valIf,10)<=0 || parseInt(valIf,10)>numeroProvincias) {
        if (parseInt(valIf,10)!=30)
            return false;
    }

    /* Aqui almacenamos los digitos de la cedula en variables. */
    d1 = numero.substr(0,1);
    d2 = numero.substr(1,1);
    d3 = numero.substr(2,1);
    d4 = numero.substr(3,1);
    d5 = numero.substr(4,1);
    d6 = numero.substr(5,1);
    d7 = numero.substr(6,1);
    d8 = numero.substr(7,1);
    d9 = numero.substr(8,1);
    d10 = numero.substr(9,1);

    /* El tercer digito es: */
    /* 9 para sociedades privadas y extranjeros */
    /* 6 para sociedades publicas */
    /* menor que 6 (0,1,2,3,4,5) para personas naturales */

    if (d3==7 || d3==8){
    //                alert('El tercer dígito ingresado es inválido');
            return false;
    }

    /* Solo para personas naturales (modulo 10) */
    if (d3 < 6){
            nat = true;
            p1 = d1 * 2;if (p1 >= 10) p1 -= 9;
            p2 = d2 * 1;if (p2 >= 10) p2 -= 9;
            p3 = d3 * 2;if (p3 >= 10) p3 -= 9;
            p4 = d4 * 1;if (p4 >= 10) p4 -= 9;
            p5 = d5 * 2;if (p5 >= 10) p5 -= 9;
            p6 = d6 * 1;if (p6 >= 10) p6 -= 9;
            p7 = d7 * 2;if (p7 >= 10) p7 -= 9;
            p8 = d8 * 1;if (p8 >= 10) p8 -= 9;
            p9 = d9 * 2;if (p9 >= 10) p9 -= 9;
            modulo = 10;
    }

    /* Solo para sociedades publicas (modulo 11) */
    /* Aqui el digito verficador esta en la posicion 9, en las otras 2 en la pos. 10 */
    else if(d3 == 6){
            pub = true;
            p1 = d1 * 3;
            p2 = d2 * 2;
            p3 = d3 * 7;
            p4 = d4 * 6;
            p5 = d5 * 5;
            p6 = d6 * 4;
            p7 = d7 * 3;
            p8 = d8 * 2;
            p9 = 0;
    }

    /* Solo para entidades privadas (modulo 11) */
    else if(d3 == 9) {
            pri = true;
            p1 = d1 * 4;
            p2 = d2 * 3;
            p3 = d3 * 2;
            p4 = d4 * 7;
            p5 = d5 * 6;
            p6 = d6 * 5;
            p7 = d7 * 4;
            p8 = d8 * 3;
            p9 = d9 * 2;
    }

    suma = p1 + p2 + p3 + p4 + p5 + p6 + p7 + p8 + p9;
    residuo = suma % modulo;

    /* Si residuo=0, dig.ver.=0, caso contrario 10 - residuo*/
    digitoVerificador = residuo==0 ? 0: modulo - residuo;

    /* ahora comparamos el elemento de la posicion 10 con el dig. ver.*/
    if(nat == true){
        if (digitoVerificador != d10){
            alert('El número de cédula es incorrecto.');
            return false;
        }
        if (numero.length >10 && numero.substr(10,3) != '001' ){
            alert('El ruc de la persona natural debe terminar con 001');
            return false;
        }
    }
    else if (pub==true){
        if (digitoVerificador != d9){
            alert('El ruc de la empresa del sector público es incorrecto.');
            return false;
        }
        /* El ruc de las empresas del sector publico terminan con 0001*/
        if ( numero.substr(9,4) != '0001' ){
            alert('El ruc de la empresa del sector público debe terminar con 0001');
            return false;
        }
    }
    else if(pri == true){
        if (digitoVerificador != d10){
            alert('El ruc de la empresa del sector privado es incorrecto.');
            return false;
        }
        if ( numero.substr(10,3) != '001' ){
            alert('El ruc de la empresa del sector privado debe terminar con 001');
            return false;
        }
    }

    return true;
}


// Cambia a mayusculas y minusculas un texto si llega solo en minusculas o solo en mayusculas;
// por ejm: JUAN PEREZ o juan perez por Juan Perez; Si llega jUaN pErEz no cambia la cadena; omite palabras de menos de 3 caracteres
// recibe: cadena (string) - cadena a formatear
// retorna: string
function ulCase(cadena) {
    var temp = new Array();
    var respuesta = '';
    var i;
    if (cadena == cadena.toLowerCase() || cadena == cadena.toUpperCase()) {
        cadena = cadena.toLowerCase();
        temp = cadena.split(' '); // Divido la cadena por espacios
        for (i=0 ; i<temp.length; i++) {
            temp[i] = temp[i].replace(/^\s+|\s+$/gi, ''); // hago un trim antes de cambiar la palabra
            if (temp[i].length>3) { // No cambio palabras pequeñas para omitir de, la, los, las, etc.
                respuesta += ' ' + temp[i].substring(0,1).toUpperCase() + temp[i].substring(1,temp[i].length);
            } else {
                respuesta += ' ' + temp[i];
            }
        }
    } else {
        respuesta = cadena;
    }
    return respuesta.replace(/^\s+|\s+$/gi, '');
}

// Copia los datos del registro civil a un campo dado
// recibe: campo_rc - Campo del registro civil (nombre, direccion)
// recibe: el id del campo donde se copiarán los datos
// retorna: void
function copiar_datos_registro_civil(campo_rc, campo_usr) {
    try {
        document.getElementById(campo_usr).value = document.getElementById('lbl_datos_rc_'+campo_rc).innerHTML;
    } catch (e) {}
}

// Valida que los nombres y apellidos ingresados coincidan con los devueltos por el registro civil
// recibe: nombre y apellido
// retorna: bool
function validar_datos_registro_civil(nombre, apellido) {
    // Obtenemos los datos de los campos a comparar
    try {
        var usr_nombre   = document.getElementById(nombre).value;
        var usr_apellido = document.getElementById(apellido).value;
        var rc_nombre    = document.getElementById('lbl_datos_rc_nombre').innerHTML;
        if (!str_in_str(rc_nombre, usr_apellido+' '+usr_nombre)) {
            if (!str_in_str(rc_nombre, usr_nombre+' '+usr_apellido)) {
                if (confirm ('Los datos ingresados no coinciden con los devueltos por el registro Civil.\n¿Desea continuar?'))
                    return true;
                else
                    return false;
            }
        }
        return true;
    } catch (e) {
        return true;
    }
}

// Verifica que una cadena esté contenida dentro de la otra
// recibe: cadena_padre - Cadena principal contiene la cadena que se esta buscando
// recibe: cadena_hija - Cadena que se busca
// retorna: bool
function str_in_str (cadena_padre, cadena_hija) {
    //valida si una cadena se encuentra dentro de otra, limpiando tildes y otros caracteres
    var cadena_p = str_limpiar_tildes(cadena_padre.toUpperCase()).split(' ');
    var cadena_h = str_limpiar_tildes(cadena_hija.toUpperCase()).split(' ');
    var i, j, orden = -1;

    for (i=0; i<cadena_h.length; ++i) {
        if (cadena_h[i]!='') {
            flag = false;
            for (j=(orden+1); j<cadena_p.length; ++j) {
                if (cadena_h[i]==cadena_p[j] || cadena_h[i]==cadena_p[j].substr(0,1)) {
                    orden = j;
                    flag = true;
                    j = cadena_p.length;
                }
            }
            if (!flag) {
                orden = -1;
                i = cadena_h.length;
            }
        }
    }

    if (orden == -1) return false;
    return true;
}

// Elimina tildes, tildes invertidas, diéresis, eñes de una cadena
// recibe: cadena - string
// retorna: string
function str_limpiar_tildes(cadena) {
    // Quita las tildes y otros carcateres de una cadena
    var origen  = new Array('Á','É','Í','Ó','Ú','À','È','Ì','Ò','Ù','Ä','Ë','Ï','Ö','Ü','Ñ'
                           ,'á','é','í','ó','ú','à','è','ì','ò','ù','ä','ë','ï','ö','ü','ñ',"'",'\\.');
    var destino = new Array('A','E','I','O','U','A','E','I','O','U','A','E','I','O','U','N'
                           ,'a','e','i','o','u','a','e','i','o','u','a','e','i','o','u','n','' ,''   );
    var j;
    for (j=0; j<origen.length; ++j) {
        cadena = eval('cadena.replace(/'+origen[j]+'/gi,destino[j]);');
    }
    return trim(cadena);
}
