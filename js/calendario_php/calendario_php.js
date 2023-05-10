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

// FUNCIONES JAVASCRIPT PARA EL MANEJO DEL CALENDARIO

function calphp_mostrar_calendario(id_objeto) {
    document.getElementById('div_calphp_calendario_'+id_objeto).style.cssText = 'position: absolute; top:22px; left: 90px;';
    document.getElementById('img_calphp_mostrar_'+id_objeto).style.display = 'none';
    document.getElementById('img_calphp_ocultar_'+id_objeto).style.display = '';
    document.getElementById('div_calphp_calendario_'+id_objeto).style.display = '';
    calphp_generar_calendario(id_objeto);
}
function calphp_ocultar_calendario(id_objeto) {
    document.getElementById('img_calphp_mostrar_'+id_objeto).style.display = '';
    document.getElementById('img_calphp_ocultar_'+id_objeto).style.display = 'none';
    document.getElementById('div_calphp_calendario_'+id_objeto).style.display = 'none';
}

function calphp_seleccionar_fecha(id_objeto, fecha) {
    document.getElementById(id_objeto).value = fecha;
    calphp_ocultar_calendario(id_objeto);
    try {
        eval (document.getElementById('calphp_accion_'+id_objeto).innerHTML);
    } catch (e) {}
}

//Devuelve el numero de dias que tiene un mes y en caso de que se pase el parametro resta, devuelve los dias del mes anterior
function calphp_numero_dias_mes(mes, anio, resta) {
    var num_dias = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    resta = resta || 0;
    var anio1 = parseInt(anio,10);
    var mes1 = parseInt(mes,10) - resta;
    while (mes1 <= 0) {
        mes1 += 12;
        --anio1;
    }
    if (((anio1 % 4 == 0) && (anio1 % 100 != 0)) || (anio1 % 400 == 0))
        num_dias[2] = 29;
    return num_dias[mes1];
}

// Dibuja los dias del calendario
function calphp_generar_calendario(id_objeto) {
    // Calculamos la fecha actual para que se muestre el dia actual en rojo
    var estilo_fecha = '';
    var f = new Date();
    var fecha_actual = f.getFullYear() + "-" + (f.getMonth() +1) + "-" + f.getDate();
    if ((f.getMonth()+1) < 10) fecha_actual = f.getFullYear() + "-0" + (f.getMonth() +1) + "-" + f.getDate();

    // variables que manejan los dias de los meses anterior, actual y siguiente
    var anio = document.getElementById('calphp_combo_anio_'+id_objeto).value;
    var mes = document.getElementById('calphp_combo_mes_'+id_objeto).value;
    if (mes < 10) mes = '0'+mes.toString();

    var fecha = anio+'-'+mes+'-01';
    var d = new Date(fecha);
    var dia_semana = d.getUTCDay(); // Calculamos el dia de la semana del primer dia del mes

    var dia_actual = 1;
    var dia_fin_actual = calphp_numero_dias_mes(mes, anio, 0); // Ultimo dia del mes actual

    var dia_fin_antes = calphp_numero_dias_mes(mes, anio, 1); // Ultimo dia del mes anterior
    var dia_antes = dia_fin_antes - dia_semana +1; // Primer dia del mes anterior que se debera imprimir

    var dia_despues = 1;

    var calendario = '<table border="0" cellpadding="0" cellspacing="0" class="calphp_tabla">' +
                        '<tr><td class="calphp_dia_titulo">D</td><td class="calphp_dia_titulo">L</td>' +
                        '<td class="calphp_dia_titulo">M</td><td class="calphp_dia_titulo">M</td>' +
                        '<td class="calphp_dia_titulo">J</td><td class="calphp_dia_titulo">V</td>' +
                        '<td class="calphp_dia_titulo">S</td></tr>';

    while (dia_actual <= dia_fin_actual) {
        calendario += '<tr>';
        for (i=0 ; i<=6 ; ++i) {
            if (dia_antes <= dia_fin_antes) { // Imprimimos los dias del mes anterior
                calendario += '<td class="calphp_dia_amarillo">'+dia_antes.toString()+'</td>';
                ++dia_antes;
            } else if (dia_actual <= dia_fin_actual) { // Imprimimos los dias del mes actual
                // Fecha del dia que se va a imprimir
                fecha = anio.toString() + '-' + mes.toString() + '-' + dia_actual.toString();
                if (dia_actual < 10) fecha = anio.toString() + '-' + mes.toString() + '-0' + dia_actual.toString();

                // Estilo para que se muestre el dia actual en rojo
                estilo_fecha = 'color: black; ';
                if (fecha==fecha_actual)
                    estilo_fecha = 'color: red; ';

                // Si es fin de semana se imprime con fondo azul
                if (i==0 || i==6)
                    calendario += '<td class="calphp_dia_azul">';
                else
                    calendario += '<td class="calphp_dia_blanco">';

                calendario += '<a href="javascript: calphp_seleccionar_fecha(\''+id_objeto+'\',\''+fecha+'\')" class="calphp_dia_link" style="'+estilo_fecha+'"'+
                    'onmouseover="this.style.cssText = \''+estilo_fecha+'font-weight: bold;\'" onmouseout="this.style.cssText = \''+estilo_fecha+'font-weight: normal;\'">'+
                    dia_actual.toString()+'</a></td>';
                ++dia_actual;
            } else { // Mes siguiente
                calendario += '<td class="calphp_dia_amarillo">'+dia_despues.toString()+'</td>';
                ++dia_despues;
            }
        }
        calendario += '</tr>';
    }
    calendario += '</table>';
    document.getElementById('div_calphp_calendario_dias_'+id_objeto).innerHTML = calendario;
}

//Determina si la fecha1 es menor, mayor o igual a la fecha2
function validarFechas(fecha1, fecha2){
        // Verificamos la fecha de reasignación
        var fecha_uno = new Date(fecha1.substring(0,4),fecha1.substring(5,7), fecha1.substring(8,10));
        var fecha_dos = new Date(fecha2.substring(0,4),fecha2.substring(5,7), fecha2.substring(8,10));
        var tiempoRestante = fecha_uno.getTime() - fecha_dos.getTime();
        var dias = Math.floor(tiempoRestante / (1000 * 60 * 60 * 24));
        if (dias < 0) { //Si la fecha1 es menor a la fecha2 retorna 1
            return 1;
        }
        else if(dias > 0) //Si la fecha1 es mayor a la fecha2 retorna 2
            {return 2}
        return 0; //Si las fechas son iguales retorna 0
}