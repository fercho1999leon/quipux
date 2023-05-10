// Checkear todos
function todos( frm )
{
	// frm = document.excExp;
    if( frm.check_todos.checked )
    {
        if( typeof frm.check_uno.length != "undefined" )
        {
            for ( i = 0; i < frm.check_uno.length; i++ )
            {
                frm.check_uno[i].checked = true;
            }
        }
        else
        {
            frm.check_uno.checked = true;
        }
    }
    else
    {
        if( typeof frm.check_uno.length != "undefined" )
        {
            for ( i = 0; i < frm.check_uno.length; i++ )
            {
                frm.check_uno[i].checked = false;
            }
        }
        else
        {
            frm.check_uno.checked = false;
        }
    }
}

// Checkea uno
function uno( frm )
{
    var verificacion = false;
    // frm = document.excExp;
    if( typeof frm.check_uno.length != "undefined" )
    {
        for ( i = 0; i < frm.check_uno.length; i++ )
        {
            if ( frm.check_uno[i].checked == false )
            {
                verificacion = true
                break;
            }
        }
    }
    else
    {
        if ( frm.check_uno.checked == false )
        {
            verificacion = true
        }
    }

    if( verificacion )
	{
		frm.check_todos.checked = false;
	}
    else
	{
		frm.check_todos.checked = true;
	}
}

function compareDate(date1, date2){
    var aux1;
    var aux2;
    var fechalimite;
    var fecha;

    aux1 = date1.split("-");
    aux2 = date2.split("-");

    if(aux1[0].length == 4){ //La fecha empieza con el año
        fechalimite = aux1[0] + aux1[1] + aux1[2];
        fecha = aux2[0] + aux2[1] + aux2[2];
    }
    else{
        fechalimite = aux1[2] + aux1[1] + aux1[0];
        fecha = aux2[2] + aux2[1] + aux2[0];
   }

    if(parseInt(fechalimite) < parseInt(fecha))
        return -1;
    else if (parseInt(fechalimite) == parseInt(fecha))
        return 1;
    else
        return 0;
}

function esRangoFechaValido(fechaInicio, fechaFin)
{
    rangofechaValido=compareDate(fechaInicio, fechaFin);  
    if(rangofechaValido != 0)
       rangofechaValido = 1;
   
    return rangofechaValido;
}

//Función para validar si se ingresa espacio en blanco
function validaEspacio( evt, textField, solo_lectura){

    //asignamos el valor de la tecla a keynum    
    if(solo_lectura == ""){
        if(window.event){// IE
            keynum = evt.keyCode;
        }else{
            keynum = evt.which;
        }
        //alert(keynum);
        if(keynum==32){
            textField.value=quitarCaracter(textField, ' ');
            alert('No se permite ingresar espacios en blanco');
            return false;
        }else
            return true;
    }
    return true;
}

//Función que elimina caracteres de un texto
function quitarCaracter( textField , caracter ){


	var str = textField.value;       
        str=str.replace(caracter,'');        

	for(i=0; i <= str.length; i++ )
	{
             str=str.replace(caracter,'');
//            if(str.charAt(i)==caracter)
//            {
//                str=str.replace(caracter,'');
//            }
	}      
        textField.value = str;
        return str;
}