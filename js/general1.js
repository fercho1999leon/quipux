/*
 * Variables globales
 */

var HOST = location.href;

//HOS=location.hostname;
//alert(HOST);
//var HOST = "http://"+location.hostname+"/ProcesoContracion/app/webroot/compras/servicio/";

HOST = HOST.substring(0, HOST.lastIndexOf('\/'));

//HOST = HOST.substring(0, HOST.lastIndexOf('\/'));

//HOST = HOST.substring(0, HOST.lastIndexOf('\/'))+"/";


//var HOST = "http://192.168.3.54/mipymev1/servicio/";

/*
 * Funci�n para hacer las llamadas directas AJAX->PHP->AJAX
 * ( Para poder utilizar la clase Ajax se requiere la librer�a protoype.js )
 */
function ajax_call ( data , clazz, action, callbackFunct )
{
	
	var url = HOST + '/update_usuarioarea.php';
	var data2 = Array();
	//data2 = new Array(data);
        data2 = new Array(data);
	//alert(data2);
	var obj = {
		method: 'post',
		parameters: "__class="+ clazz +"&__action="+ action +"&"+ "data="+data2,
		onSuccess: function (resp,result) { callbackFunct.apply(this,[result,resp.responseText]); },
		onFailure: function (resp,result) { alert("desde ajax call Error de conexion: "+ resp.responseText); }
	}
	var myAjax = new Ajax.Request ( url, obj );
	
}

/*
 * Funci�n para hacer las llamadas directas AJAX->PHP->AJAX para bandeja compartida
 * ( Para poder utilizar la clase Ajax se requiere la librer�a protoype.js )
 */
function ajax_call_bandeja ( data , clazz, action, callbackFunct )
{
	var url = '../usuarios/update_usuarioarea.php';
	var data2 = Array();
	//data2 = new Array(data);
    data2 = new Array(data);

    var codigoDepe;
    var datos = new Array();
    datos = data.split(',');
    codigoDepe = datos[2];
    //alert(codigoDepe);
	var obj = {
		method: 'post',
		parameters: "__class="+ clazz +"&__action="+ action +"&"+ "data="+data2,
		onSuccess: function (resp,result,depeCodi) { callbackFunct.apply(this,[result,resp.responseText,codigoDepe]); },
		onFailure: function (resp,result,depeCodi) { alert("desde ajax call Error de conexion: "+ resp.responseText); }
	}
	var myAjax = new Ajax.Request ( url, obj );
}

/*
 * Funci�n para hacer las llamadas directas AJAX->PHP->AJAX para ministerios coordinadores
 * ( Para poder utilizar la clase Ajax se requiere la librer�a protoype.js )
 */
function ajax_call_coordinador ( data , clazz, action, callbackFunct )
{
    var url = '../usuarios/update_usuarioarea.php';
    var data2 = Array();
    //data2 = new Array(data);
    data2 = new Array(data);

    var codigoInst;
    var datos = new Array();
    datos = data.split(',');
    codigoInst = datos[1];
	var obj = {
		method: 'post',
		parameters: "__class="+ clazz +"&__action="+ action +"&"+ "data="+data2,
		onSuccess: function (resp,result,instCodi) { callbackFunct.apply(this,[result,resp.responseText,codigoInst]); },
		onFailure: function (resp,result,instCodi) { alert("desde ajax call Error de conexion: "+ resp.responseText); }
	}
	var myAjax = new Ajax.Request ( url, obj );
}