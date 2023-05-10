

function datosArea(depeCodi,tipoOpc,cuantos,depeCodiPadre,objt){        
      
        
        todo=0;
       
        
            usrCodigo = document.getElementById('usr_codigo').value;
            instancia = document.getElementById('instancia').value;            
            checked = document.getElementById("phtml_"+depeCodi).className;
            if (objt)
                if (objt.checked==true)
                tipo =1;
                else
                    tipo=0;
            datos = 'area='+depeCodi+'&cuantos='+cuantos+'&tipoOpc='+tipoOpc+'&areaPadre='+depeCodiPadre+'&usrCodigo='+usrCodigo+'&instancia='+instancia+'&tipo='+tipo+'&todo='+todo+'&Arbol=0';
            nuevoAjax('div_area_selecciona', 'GET', 'area_ajax_grabar.php', datos);
            
            datos="usrCodigo="+usrCodigo;
            nuevoAjax('div_datos', 'GET', 'refrescarPanelArbol.php', datos);
            refrescarDatos();
            
  
    }
function ArbolAll(depeCodi,depeCodiPadre,tipo){        
            usrCodigo = document.getElementById('usr_codigo').value;
            instancia = document.getElementById('instancia').value;            
            checked = document.getElementById("phtml_"+depeCodi).className;
           
            datos = 'area='+depeCodi+'&areaPadre='+depeCodiPadre+'&usrCodigo='+usrCodigo+'&instancia='+instancia+'&Arbol=1&tipo='+tipo;
            nuevoAjax('div_area_selecciona', 'GET', 'area_ajax_grabar.php', datos);
            
            datos="usrCodigo="+usrCodigo;
            nuevoAjax('div_datos', 'GET', 'refrescarPanelArbol.php', datos);
            refrescarDatos();
            document.getElementById('imagen_div').innerHTML = "Por favor espere mientras se procesa su petición.<br>&nbsp;<br><img src='../../imagenes/progress_bar.gif'><br>&nbsp;";
            timerID = setTimeout("window.location.reload(true)", 1000);
  
    }
function refrescarDatos(){
    
        usrCodigo = document.getElementById('usr_codigo').value;
        datos="usrCodigo="+usrCodigo;
        nuevoAjax('div_datos', 'GET', 'refrescarPanelArbol.php', datos);
       
}

function cerrar(){
    window.close();
}
//1 guarda todo, 0 elimina todo
function seleccionar_todo(tipo){
    
    usrCodigo = document.getElementById('usr_codigo').value;
    instancia = document.getElementById('instancia').value;
    depeCodi=0;
    depeCodiPadre=0;
    todo=1;
    cuantos=0;
    tipoOpc=0;
   for (i=0;i<document.arbolAjax.elements.length;i++){
      if(document.arbolAjax.elements[i].type == "checkbox")
        if (tipo==1)
            document.arbolAjax.elements[i].checked=1;
            else
                document.arbolAjax.elements[i].checked=0;
            
        
   }
   datos = 'area='+depeCodi+'&cuantos='+cuantos+'&tipoOpc='+tipoOpc+'&areaPadre='+depeCodiPadre+'&usrCodigo='+usrCodigo+'&instancia='+instancia+'&tipo='+tipo+'&todo='+todo;
   
   nuevoAjax('div_area_selecciona', 'GET', 'area_ajax_grabar.php', datos);
   refrescarDatos();
   document.getElementById('imagen_div').innerHTML = "Por favor espere mientras se procesa su petición.<br>&nbsp;<br><img src='../../imagenes/progress_bar.gif'><br>&nbsp;";
   timerID = setTimeout("window.location.reload(true)", 1000);
}
function buscarArea(){
         paginador_reload_div('');
    }
