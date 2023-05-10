<?php
/*	
* Modificado por		Iniciales		Fecha (dd/mm/aaaa)
*	DAVID GAMBOA    	SC			12/11/2011
* 
*/

$ruta_raiz = "../..";
session_start();
include_once "$ruta_raiz/rec_session.php";
require_once("$ruta_raiz/funciones.php");
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/obtenerdatos.php";

if (isset($_POST["txt_tipo_reporte"]))
    $txt_tipo_reporte = limpiar_sql($_POST["txt_tipo_reporte"]);
else
    $txt_tipo_reporte = "01";
//include "reportes_datos_reportes.php";


echo "<html>".html_head();
//echo "<script language='JavaScript' src='$ruta_raiz/js/spiffyCal/spiffyCal_v2_1.js'></script>";
include_once "$ruta_raiz/js/ajax.js";

// Defino las fechas para los combos
$txt_fecha_desde = date("Y-m-d", strtotime(date("Y-m-d")." - 30 day"));
$txt_fecha_hasta = date("Y-m-d");


?>

<script type="text/JavaScript">   
    var columnas_nombre = new Array();
    var columnas_desc = new Array();
    var columnas_tipo   = new Array();
    var columnas_orden  = new Array();
    var columnas_selec_disponibles = '';
    var columnas_selec_reporte = '';
    var num_grupo1 = 0;
    var num_grupo2 = 0;
    var str2="";
  function realizar_busqueda(){      
      limpiar_datos();
      
      depeCodi = document.getElementById('txt_dependencia').value;
      codPermiso = document.getElementById('txt_permiso').value;
      radio_filtro = radioValor();
     
      datos = 'depe_codi='+depeCodi+"&codPermiso="+codPermiso+"&radio_filtro="+radio_filtro;
      nuevoAjax('div_reporte', 'GET', 'reporte_permiso_usuarios.php', datos);      
      timerID = setTimeout("mostrar_permisos()", 100);
      document.getElementById("bandera_todos").value=0;
      
      columnas_selec_disponibles='';
}
//boton seleciona todos lo usuarios
function usuarios_todos(){
    timerID = setTimeout("mostrar_botones()", 100);
    
    strT=document.getElementById("sel_todos_usuarios").value;
    

    bandera=document.getElementById("bandera_todos").value;    
    nom_tr = 'tr_usr_disponibles_';
    col = new Array();
    col = strT.split(',');
    if (bandera==1){
        document.getElementById("bandera_todos").value=0;
        for (i=1 ; i<col.length ; ++i) {            
            if (document.getElementById("todos_usuarios").value!='')
                document.getElementById(nom_tr+col[i]).style.cssText = 'background-color:#e3e8ec';
            else{
            document.getElementById(nom_tr+col[i]).style.cssText = 'background-color:#a8bac6';
            document.getElementById("bandera_todos").value=1;
            }
        }
    }else{
        
        document.getElementById("bandera_todos").value=1;
        for (i=1 ; i<col.length ; ++i) {     
            if (document.getElementById("todos_usuarios").value=='')
                document.getElementById(nom_tr+col[i]).style.cssText = 'background-color:#e3e8ec';
                else
                document.getElementById(nom_tr+col[i]).style.cssText = 'background-color:#a8bac6';
        }
    }
    document.getElementById("todos_usuarios").value=strT;
}
//columna, es el id del usuarios
//tipo si es usuarios o permisos
function ver_nombre(columna,tipo){
    comprueba = document.getElementById("todos_usuarios").value;    
    timerID = setTimeout("mostrar_botones()", 100);
    bandera=document.getElementById("bandera_todos").value;
    if (bandera==1)
        document.getElementById("cod_usr_mod").value = "";
    if (tipo==1){
        //document.getElementById("bandera_todos").value=0;//para que no se envie todos los usuarios
        str = columnas_selec_disponibles;
        nom_tr = 'tr_usr_disponibles_';
    }else{
         str = columnas_selec_reporte;
         nom_tr = 'tr_permisos_disponibles_';
    }  
        flag = true;
        col = new Array();
        col = str.split(',');
        str = '';
        for (i=1 ; i<col.length ; ++i) {
            if (col[i]==columna) {
                flag = false;
                document.getElementById(nom_tr+columna).style.cssText = 'background-color:#e3e8ec';
             
            } else {                
                str += ','+col[i];
            }
        
        }
        //Todos
        if (bandera==1 && tipo==1 && comprueba!=''){
           flag = true;
            col = new Array();
            strs = document.getElementById("todos_usuarios").value;
            col = strs.split(',');
            strs = '';
            for (i=1 ; i<col.length ; ++i) {
                if (col[i]==columna) {
                    flag = false;
                    document.getElementById(nom_tr+columna).style.cssText = 'background-color:#e3e8ec';

                } else {                
                    strs += ','+col[i];
                }
            } 
            document.getElementById("todos_usuarios").value=strs;
       }
        else{
            if (flag) {
                document.getElementById(nom_tr+columna).style.cssText = 'background-color:#a8bac6';
                str += ','+columna;
            }
            if (tipo==1){//selecciono usuarios            
                 columnas_selec_disponibles = str;             
                 document.getElementById("cod_usr_mod").value = columnas_selec_disponibles;
                }
            else{

                columnas_selec_reporte = str;
                document.getElementById("cod_perm_mod").value = columnas_selec_reporte;
            }
        }
}
function mostrar_botones(){
    var radio_filtro="NO";
    codPermiso = document.getElementById('cod_perm_mod').value;
    permiso_mod = document.getElementById("txt_permiso").value;
    
    radio_filtro = radioValor();

    if (permiso_mod==0){//muestra botones guardar
        datos="mostrar_boton=1";
        nuevoAjax('div_botones', 'GET', 'mostrar_botones.php', datos); 
    }
     else{//muestra botones eliminar
         
         
        if (radio_filtro=='NO'){
            datos="mostrar_boton=1";
            nuevoAjax('div_botones', 'GET', 'mostrar_botones.php', datos);
        }else{
         datos="mostrar_boton=0&radio_filtro="+radio_filtro;
         nuevoAjax('div_botones', 'GET', 'mostrar_botones.php', datos);
        }
     }
}
//despliega la tabla de permisos
function mostrar_permisos() { 
    datos="ver_datos=1";
    permiso_mod = document.getElementById("txt_permiso").value;
    
       if (permiso_mod==0){
        nuevoAjax('div_permisos', 'GET', 'criterios_permisos.php', datos);
        document.getElementById('div_permisos').style.display='';
       }
    else//en el caso de que desea eliminar
        document.getElementById('div_permisos').style.display='none';
        
}
//para guardar usuarios y permisos
function guardar(){
     mensaje = "";
      
     bandera=document.getElementById("bandera_todos").value;
     if (bandera==1){
       usuario_mod = document.getElementById("todos_usuarios").value;
       if(usuario_mod=='')
           usuario_mod = document.getElementById("cod_usr_mod").value;
     }
     else
      usuario_mod = document.getElementById("cod_usr_mod").value;
     
     permiso_mod = document.getElementById("cod_perm_mod").value;
     if (permiso_mod=='')
     permiso_mod = document.getElementById("txt_permiso").value;
     txt_permiso = document.getElementById("txt_permiso").value;
     
     radio_filtro = radioValor();
     
 
    
     if (usuario_mod=='')
         mensaje = mensaje + " Seleccione Usuarios";
     if (permiso_mod=='' || bandera==0 || permiso_mod==0){          
         if (mensaje=='')
            mensaje = mensaje + " Seleccione Permisos";
         else
             mensaje = mensaje + " y Seleccione Permisos";
     }
     
     accion="1";
     datos="usr_mod="+usuario_mod+"&txt_permiso="+txt_permiso+"&permiso_mod="+permiso_mod+"&accion="+accion+"&radio_filtro="+radio_filtro;
     
     if (usuario_mod!='' && permiso_mod!=0){
        nuevoAjax('div_guardar', 'GET', 'adm_permisos_grabar.php', datos);
        timerID = setTimeout("realizar_busqueda()", 100);
     }
     else         
         alert(mensaje);
     //refrescar busqueda
     
     
     
     
}
//Para eliminar usuarios y permisos
function eliminar(){
     
     bandera=document.getElementById("bandera_todos").value;
     if (bandera==1){
       usuario_mod = document.getElementById("todos_usuarios").value;
       if(usuario_mod=='')
           usuario_mod = document.getElementById("cod_usr_mod").value;
     }
     else
      usuario_mod = document.getElementById("cod_usr_mod").value;
     permiso_mod = document.getElementById("txt_permiso").value;
     
     accion="0";
     datos="usr_mod="+usuario_mod+"&permiso_mod="+permiso_mod+"&accion="+accion;
     if (usuario_mod!='' && permiso_mod!=''){
         var respuesta = confirm("Está seguro que desea eliminar los permisos?")
	if (respuesta){
            nuevoAjax('div_guardar', 'GET', 'adm_permisos_grabar.php', datos);
            timerID = setTimeout("realizar_busqueda()", 100);
        }
     }
     else{
         if (usuario_mod!='')
             alert("Seleccione Usuarios para eliminar el permiso seleccionado");
         if(permiso_mod==0)
           alert("Seleccione el combo el permiso que desea eliminar");
     }
     
     
     
}
function limpiar_datos(){
    
    document.getElementById("cod_usr_mod").value='';    
    document.getElementById("cod_perm_mod").value=''; 
   
   
}
function radioValor(){
 for(i=0; i <document.form1.radio_filtro.length; i++){
        if(document.form1.radio_filtro[i].checked){
            valorSeleccionado = document.form1.radio_filtro[i].value;
        }
    }
   return valorSeleccionado;
}
</script>

  
  <body >
    <div id="spiffycalendar" class="text"></div>

    <center>
      <form name="form1" action="" id="form1" method="post">

        <input type="hidden" name="cod_usr_mod" id="cod_usr_mod" value=""/>
        <input type="hidden" name="bandera_todos" id="bandera_todos" value=""/>
        <input type="hidden" name="cod_perm_mod" id="cod_perm_mod" value=""/>
        
        
       
        <div id="div_datos_reporte" style="width: 99%">
            <br>
            <table width="100%" align="center" class="borde_tab" border="0">
              <tr>
                <th width="100%" colspan="3">
                  <center>
                    ADMINISTRACI&Oacute;N DE PERMISOS
                  </center>
                </th>
              </tr>
              
              <tr>
                        <td class="listado5">Área</td>
                        <td>
                            <?php
                            $depe_codi_admin = obtenerAreasAdmin($_SESSION["usua_codi"],$_SESSION["inst_codi"],$_SESSION["usua_admin_sistema"],$db);
                            
                            $sql="select depe_nomb, depe_codi from dependencia where depe_estado=1 and inst_codi=".$_SESSION["inst_codi"];
                            if ($depe_codi_admin!=0)
                            $sql.=" and depe_codi in ($depe_codi_admin)";
                            $sql.=" order by 1 asc";
                            $rs=$db->conn->query($sql);
                            if($rs) print $rs->GetMenu2("txt_dependencia", "0", "0:&lt;&lt; Seleccione &gt;&gt;", false,"","class='select' id='txt_dependencia'" );
                            ?>
                        </td>
                        <td align="left">
                        
                        </td>                    
                    </tr>
               <tr>
                        <td class="listado5">Permiso</td>
                        <td>
<?php
            $sql="select descripcion, id_permiso from permiso where estado=1 and perfil in (0,1,2,3,4) and id_permiso not in (26) order by 1";
            $rs=$db->conn->query($sql);
            if($rs) print $rs->GetMenu2("txt_permiso", "0", "0:&lt;&lt; Seleccione &gt;&gt;", false,"","class='select' id='txt_permiso'" );
?>
                        </td>
                        <td>
                            

                        </td>
                    </tr>
                 <tr>
                        <td class="listado5">Desplegar Usuarios</td>
                        <td>
<!--                            <input type="checkbox" name="radio_filtro" id="radio_filtro"/>-->
                            <input type="radio" id="radio_filtro" name="radio_filtro" value="SI" onclick="realizar_busqueda();"/> Tienen Permiso
                            <input type="radio" id="radio_filtro" name="radio_filtro" value="NO" onclick="realizar_busqueda();" checked/> No Tienen Permiso
                        </td>
                        <td>
                            <input type='button' value='Buscar' name='btn_generar' class='botones_largo' onClick='realizar_busqueda();'/>

                        </td>
                    </tr>
                    <tr>
                  <td colspan="3"><div id="div_guardar"></div></td>
                      
                
              </tr>
            </table>
            <br>
            <table width="100%" align="center" class="borde_tab" border="0">                
                <tr>
                    <td class="listado1" valign="top" width="50%">
                        
                        <div id='div_buscar_usuarios' style="width: 99%"></div>
                    </td>
                    <td class="listado1" valign="top" width="50%">
                        
                    </td>
                      
                </tr>
              <tr>
                  <td class="listado1" valign="top" width="50%">
                      <div id="div_reporte"></div>
                  </td>
                  
                  <td class="listado1" valign="top" width="50%"><div id="div_permisos"></div></td>
              </tr>
              <tr>
                  <td colspan="2"></td>
              </tr>
              <tr>
                  <td colspan="2"><div id="div_botones"></div></td>
              </tr>
            </table>            
            <br>            
        </div>
      </form>
      <br>
  
      <div id='div_reporte_guardar_como' style="width: 99%"></div>
    </center>
  </body>
</html>

