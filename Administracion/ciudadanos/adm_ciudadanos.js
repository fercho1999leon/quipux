/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * Funciones javascript utilizadas en varias paginas
 */
/*
 * adm_ciudadano_solconfirmar.php
 * adm_usuario_ext_combinar.php
 * adm_usuario_ext_confirmar.php
 * adm_usuario_ext.php
 **/
/*
 *funcion para ver por arbol las ciudades
 **/
var dataanterior='';
function datosCiudad(ciudadCodi){              
       

            
            document.getElementById('ciu_ciudad').value = ciudadCodi;
            
            datos = 'ciudad='+ciudadCodi;
            //nuevoAjax('div_area_selecciona', 'GET', 'ciudad_ajax_grabar.php', datos);
            
            //datos="usrCodigo="+usrCodigo;
            nuevoAjax('div_datos', 'GET', '../usuarios/refrescarPanelArbol.php', datos);
            //refrescarDatos();
            
  
    }
/*
 * buscar las ciudades
 **/

function lookup(obj) {
         
           inputString = obj.value;
           inputString = inputString + " ";
//		if(inputString.length == 0) {
//			// Hide the suggestion box.
//			$('#suggestions').hide();
//		} else {
                if(inputString.length > 2){ 
                    nombreBuscar = obj.value;
                   
			$.post("../usuarios/ajax_ciudad.php", {queryString: ""+inputString+""}, function(data){
                           
                          
				if(data.length >0) {
					$('#suggestions').show();
					$('#autoSuggestionsList').html(data);
                                }
			});
                   }     
//		}
                //return dataanterior;
} // lookup
/*
 * buscar las ciudades
 **/	

function fill(thisValue) {        
        $('#inputString').val(thisValue);

        setTimeout("$('#suggestions').hide();", 200);
}
/*
 * setear el codigo de la ciudad
 **/
function codigoFus(idCiudad){
    document.getElementById("ciu_ciudad").value=idCiudad;

}
/*
 *  verificar la cedula
 **/        
function cambio_cedulajs(obj,tipo) {       
        document.getElementById("img_reg"+tipo).style.display = '';            
            cedula = obj.value;
            if (cedula==''){
                document.getElementById("img_reg"+tipo).style.display = 'none';
                document.getElementById('div_datos_registro_civil').style.display = 'none';
            }
            else{
                document.getElementById("img_reg"+tipo).style.display = '';
                document.getElementById('div_datos_registro_civil').style.display = '';
            }
            validar_cambio_cedulajs(cedula);
        }
/*
 * ejecuta el ws
 **/      
function validar_cambio_cedulajs(cedula) {
    
        if (!cedula)
        cedula = document.getElementById('ciu_cedula').value;
        
        nuevoAjax('div_datos_registro_civil', 'POST', '../usuarios/validar_datos_registro_civil.php', 'cedula='+cedula);
        nuevoAjax('div_datos_usuario_multiple', 'POST', '../usuarios/validar_datos_usuario_multiple.php', 'usr_codigo=<?=$ciu_codigo?>&cedula='+cedula);
        
}
/*
 * mostrar dados de ciudadanos cuando se edita
 **/
function openDivciudadano(){
        document.getElementById('div_datos_registro_civil').style.display = '';
        document.getElementById('div_datos_registro_civilimg_menos').style.display = '';
        document.getElementById('div_datos_registro_civilimg_mas').style.display = 'none';
        //mostrar_div('div_datos_usuario_multiple');
}
/*
 * limpiar Cadenas
 **/
function ltrim(s) {
           return s.replace(/^\s+/, "");
}
/*
 * Poner mayusculas las letras de las palabras
 **/
function changeCase_Articulos(frmObj) {
        var index;
        var tmpStr;
        var tmpChar;
        var preString;
        var postString;
        var strLen;
        

        tmpStr = frmObj.value.toLowerCase();
        strLen = tmpStr.length;
        if (strLen > 0)  {
        for (index = 0; index < strLen; index++)  {
            if (index == 0)  {
            tmpChar = tmpStr.substring(0,1).toUpperCase();
            postString = tmpStr.substring(1,strLen);
            tmpStr = tmpChar + postString;
            }
            else {
                tmpChar = tmpStr.substring(index, index+1);
                if (tmpChar == " " && index < (strLen-1))  {
                tmpChar = tmpStr.substring(index+1, index+2).toUpperCase();
                preString = tmpStr.substring(0, index+1);
                postString = tmpStr.substring(index+2,strLen);
                tmpStr = preString + tmpChar + postString;
                }//if
              }//else
           }//for
        }//if
   
   //Cambia los artículos a minúsculas
    var arrayori  = ['De' , 'Del', 'La', 'Las', 'Lo','Los','En','Y','E','Ff.Aa.'];
    var frase='';
    var frase1='';

    fragmentoTexto = tmpStr.split(' ');
    frase=fragmentoTexto[0]+' ';


    for(i=1;i<fragmentoTexto.length;i++){
        for(j=0;j<arrayori.length;j++){//for dentro
            if(fragmentoTexto[i]==arrayori[j]){
                cadena=fragmentoTexto[i];
                parteFrase=cadena.toString().toLowerCase();
                frase1=parteFrase+' ';
            }//if
        }//for dentro
        if(frase1=='')
           frase+= fragmentoTexto[i]+' ';
        else
            frase+= frase1;
        frase1='';
   }//for
   frmObj.value=trim(frase);   
}
/*
 * desplegar datos para el registro civil
 **/
function ver_datos(tipo){
            if (tipo==2)
            cedula=document.getElementById('old_cedula').value;
        else
            cedula=document.getElementById('new_cedula').value;
           if (cedula!=''){
                validar_cambio_cedula(cedula);
                document.getElementById('div_datos_registro_civil').style.display = '';
           }
            else{
                alert('No existe Cédula');
                document.getElementById('div_datos_registro_civil').style.display = 'none';
            }
          
}
/*
 * funcion para confirmar campos, copia de input a otro
 **/
function copiar(campo) {
                document.getElementById('ciu_'+campo).value = document.getElementById('old_'+campo).value;
}
function cambio_cedula(obj,tipo) {        
            document.getElementById("img_reg"+tipo).style.display = '';            
                cedula = obj.value;
                if (cedula==''){
                    document.getElementById("img_reg"+tipo).style.display = 'none';
                    document.getElementById('div_datos_registro_civil').style.display = 'none';
                }
                else{
                    document.getElementById("img_reg"+tipo).style.display = '';
                    document.getElementById('div_datos_registro_civil').style.display = '';
                }
                validar_cambio_cedula(cedula);
            
        
}
function mostrar_div_ciud(obj_div){
    if (obj_div=='div_informacion_ext'){
        document.getElementById('div_informacion_ext').style.display = ''; 
        document.getElementById('div_historico_ext').style.display = 'none';
    }else if (obj_div=='div_historico_ext'){
        document.getElementById('div_informacion_ext').style.display = 'none'; 
        document.getElementById('div_historico_ext').style.display = '';
    }
}
/*
 * desplegar el icono del div, tabla ciudadanos y usuarios
 **/
function mostrar_div(obj_div){
    
    if (document.getElementById(obj_div).style.display=='none'){//esconder
        document.getElementById(obj_div).style.display = '';        
    }else{
        
        document.getElementById(obj_div).style.display = 'none';        
    }
    //imagenes
    
        if (document.getElementById(obj_div+'img_mas') && document.getElementById(obj_div+'img_menos'))
        if (document.getElementById(obj_div+'img_mas').style.display=='none'){//esconder
           document.getElementById(obj_div+'img_menos').style.display='none';
           document.getElementById(obj_div+'img_mas').style.display='';
        }else{
            document.getElementById(obj_div+'img_menos').style.display='';
            document.getElementById(obj_div+'img_mas').style.display='none';        
        }
     
}
function mostrar_div_usr(obj_div){

    if (obj_div=='div_informacion_usr'){
        document.getElementById('div_informacion_usr').style.display = '';
        
        document.getElementById('div_permisos_desp').style.display = 'none';
        document.getElementById('div_backup').style.display='none';
        document.getElementById('div_recorrido').style.display = 'none';
        if (document.getElementById('tr_informacion_usr'))
            document.getElementById('tr_informacion_usr').style.display = '';
        if (document.getElementById('tr_permisos_usr'))
            document.getElementById('tr_permisos_usr').style.display = 'none';
        if (document.getElementById('tr_backup'))
            document.getElementById('tr_backup').style.display = 'none';
    }
    else if(obj_div=='div_permisos_desp'){
        document.getElementById('div_informacion_usr').style.display = 'none'; 
        document.getElementById('div_permisos_desp').style.display = '';        
        document.getElementById('div_backup').style.display='none';
        document.getElementById('div_recorrido').style.display = 'none';
        //tr
        if (document.getElementById('tr_informacion_usr'))
            document.getElementById('tr_informacion_usr').style.display = 'none';
        if (document.getElementById('tr_permisos_usr'))    
            document.getElementById('tr_permisos_usr').style.display = '';
        if (document.getElementById('tr_backup'))    
            document.getElementById('tr_backup').style.display = 'none';
    }
    else if (obj_div=='div_backup'){        
        document.getElementById('div_informacion_usr').style.display = 'none'; 
        document.getElementById('div_permisos_desp').style.display='none';
        document.getElementById('div_backup').style.display = '';
        document.getElementById('div_recorrido').style.display = 'none';
        if (document.getElementById('tr_informacion_usr'))
            document.getElementById('tr_informacion_usr').style.display = 'none';
        if (document.getElementById('tr_permisos_usr'))
            document.getElementById('tr_permisos_usr').style.display = 'none';
        if (document.getElementById('tr_backup'))
            document.getElementById('tr_backup').style.display = '';
    }else if(obj_div=='div_recorrido'){
        document.getElementById('div_informacion_usr').style.display = 'none'; 
        document.getElementById('div_permisos_desp').style.display='none';
        document.getElementById('div_backup').style.display = 'none';
        document.getElementById('div_recorrido').style.display = '';
        if (document.getElementById('tr_informacion_usr'))
        document.getElementById('tr_informacion_usr').style.display = 'none';
        if (document.getElementById('tr_permisos_usr'))
        document.getElementById('tr_permisos_usr').style.display = 'none';
        if (document.getElementById('tr_backup'))
        document.getElementById('tr_backup').style.display = 'none';
        
        
        
    }
        
        
}
/*
 * desplegar div para cambios historicos
 **/
function mostrar_div_historico(obj_div,usr_codigo,tipo,tabla){    
    
    mostrar_div(obj_div);
    
    if (tipo==1)//ciudadanos
        ruta_raiz='../usuarios/log_historico.php';
    else//usuarios
        ruta_raiz='log_historico.php';
    
    nuevoAjax(obj_div, 'GET', ruta_raiz, 'usr_codigo='+usr_codigo+'&tabla_modificada='+tabla);
}
/*
* desplegar div para permisos
 **/
function mostrar_div_permisos(obj_div,usr_codigo){    
    
    mostrar_div(obj_div);
    ruta_raiz='log_historico_permisos.php';
    
    nuevoAjax(obj_div, 'GET', ruta_raiz, 'usr_codigo='+usr_codigo);
}

function buscarDep(tipo){
   
    if (tipo==1){
        var code = $("#cod_pais").val();
   
    }
    if(tipo==2){
        
        var code = $("#cod_prov").val();
        if (!code)
            code=document.getElementById("hcod_prov").value;
    }
    if (tipo==3){
        
        var code = $("#ciu_ciudad").val();
        if (!code)
            code=document.getElementById("hcod_ciu").value;
        
    }
	$.get("../tbasicas/pais.php", {code: code},
		function()
		{
			if (tipo==1){
                            
                            cod_prov=document.getElementById("hcod_prov").value;
                            nuevoAjax('div_prov', 'GET', '../tbasicas/opc_provincia.php', 'code='+code+"&cod_ciu="+cod_prov);
                            
                             
                        }
			if (tipo==2){
                            
                            cod_ciu=document.getElementById("hcod_ciu").value;
                            
                            
                            nuevoAjax('div_ciudad', 'GET', '../tbasicas/opc_ciudad.php', 'code='+code+"&cod_ciu="+cod_ciu);
                            if (document.getElementById("cod_canton"))
                            document.getElementById("cod_canton").value=0;
                            
                        }
                        if (tipo==3){
                            
                            cod_canton=document.getElementById("hcod_canton").value;
                           
                            nuevoAjax('div_canton', 'GET', '../tbasicas/opc_canton.php', 'code='+code+"&cod_canton="+cod_canton);
                           
                        
                        }
		}

	);
   
}
/*
$(document).ready(function(){
    
	$("#cod_pais").change(function(){            
            buscarDep(1);
           
            
        });
        $("#cod_prov").change(function(){  
            
            buscarDep(2);
           
            
            
        });
	$("#ciu_ciudad").change(function(){                        
           
            buscarDep(3);
            
            
        });
});
function cargarGeografia(){
    buscarDep(1);
    buscarDep(2);
    buscarDep(3);
    
}*/
