// JavaScript Document

function isValidNumeric( textField ){
        var valor = textField.value;
        var len = 0;
        if (!isNaN(valor))
                return true;
        else
        {
//                alert("Por favor, ingrese solo números.");
                len = valor.length - 1;
                valor = valor.substring(0, len);
                textField.value=valor;
                return false;
        }
}

function validarCedula (campo)
{
        numero = campo.value;
        var suma = 0;
        var residuo = 0;
        var pri = false;
        var pub = false;
        var nat = false;
        var numeroProvincias = 24;
        var modulo = 11;

        // Verifico que el número de cédula tenga 10 o 13 digitos
        numero = numero.replace(/^\s+/, "");
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
        /*var ok=1;
        for (i=0; i<numeroProvincias){
                alert('El código de la provincia (dos primeros dígitos) es inválido'); return false;
        }*/

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
                p1 = d1 * 2; if (p1 >= 10) p1 -= 9;
                p2 = d2 * 1; if (p2 >= 10) p2 -= 9;
                p3 = d3 * 2; if (p3 >= 10) p3 -= 9;
                p4 = d4 * 1; if (p4 >= 10) p4 -= 9;
                p5 = d5 * 2; if (p5 >= 10) p5 -= 9;
                p6 = d6 * 1; if (p6 >= 10) p6 -= 9;
                p7 = d7 * 2; if (p7 >= 10) p7 -= 9;
                p8 = d8 * 1; if (p8 >= 10) p8 -= 9;
                p9 = d9 * 2; if (p9 >= 10) p9 -= 9;
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
        if (pub==true){
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
        else if(nat == true){
                if (digitoVerificador != d10){
                        alert('El número de cédula es incorrecto.');
                        return false;
                }
                if (numero.length >10 && numero.substr(10,3) != '001' ){
                        alert('El ruc de la persona natural debe terminar con 001');
                        return false;
                }
        }
        return true;
    }

