<?
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
**/
/*****************************************************************************************
**											**
*****************************************************************************************/
$ruta_raiz = "../..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

session_start();
if($_SESSION["usua_admin_sistema"]!=1) die("");
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/obtenerdatos.php";

$read = "";
$read2 = "";
$db = new ConnectionHandler("$ruta_raiz");
$db->conn->SetFetchMode(ADODB_FETCH_ASSOC);


if (!isset($recargar)) {
    if ($accion == 4 ) {
	$tituloForm = "Asignar Usuarios por Areas";
        $sql = "select u.usua_codi, u.usua_nombre, u.usua_cedula, u.inst_codi  from usuario u where coalesce(u.depe_codi,-1)=-1 and u.usua_esta=1 and u.inst_codi =".$_SESSION['inst_codi']. " order by u.usua_nombre";
//.$institucion_codigo;
        
	$rsusuarios = $db->conn->Execute($sql);
       // $rs = $db->conn->query($sql);
      // VAR_DUMP($rsusuarios);
        $depe_codi_admin = obtenerAreasAdmin($_SESSION["usua_codi"],$_SESSION["inst_codi"],$_SESSION["usua_admin_sistema"],$db);
        $sql2 = "select * from dependencia d where d.depe_estado=1 and d.inst_codi =". $_SESSION['inst_codi'];
        if ($depe_codi_admin!=0)
         $sql2.=" and depe_codi in ($depe_codi_admin)";
        $sql2.= " order by depe_nomb";
	
        $rsarea = $db->conn->Execute($sql2);
        //VAR_DUMP($rsarea);
    }

 /*  if ($accion == 1) {	//Nuevo
	$tituloForm = "CREACI&Oacute;N DE USUARIOS";
	$usr_nuevo = "checked";
	$usr_estado = "checked";
	$sqlp = "select descripcion, id_permiso, '0' as permiso from permiso where estado=1 order by orden asc";
    } else {
	$sql = "select * from usuarios where usua_codi=$usr_codigo";
	$rs = $db->conn->Execute($sql);
	$usr_depe 	= $rs->fields["DEPE_CODI"];
	$usr_perfil 	= $rs->fields["CARGO_TIPO"];
	$usr_login 	= $rs->fields["USUA_LOGIN"];
	$usr_cedula 	= $rs->fields["USUA_CEDULA"];
	$usr_nombre 	= $rs->fields["USUA_NOMB"];
	$usr_apellido 	= $rs->fields["USUA_APELLIDO"];
	$usr_titulo 	= $rs->fields["USUA_TITULO"];
	$usr_abr_titulo	= $rs->fields["USUA_ABR_TITULO"];
	$usr_cargo 	= $rs->fields["USUA_CARGO"];
	$usr_email 	= $rs->fields["USUA_EMAIL"];
	if ($rs->fields["USUA_NUEVO"]==1) $usr_nuevo = ""; else $usr_nuevo = "checked";
	if ($rs->fields["USUA_ESTA"]==0) $usr_estado = ""; else $usr_estado = "checked";
	$sqlp = "select p.descripcion, p.id_permiso, count(pc.id_permiso) as permiso
		from permiso p left outer join permiso_usuario pc on p.id_permiso=pc.id_permiso 
		and pc.usua_codi=$usr_codigo where p.estado=1 group by p.descripcion, p.id_permiso, p.orden order by p.orden asc";
    }*/
} else {
	if (!isset($_POST["usr_nuevo"])) $usr_nuevo = ""; else $usr_nuevo = "checked";
	if (!isset($_POST["usr_estado"])) $usr_estado = ""; else $usr_estado = "checked";
	$sqlp = "select descripcion, id_permiso, '0' as permiso from permiso where estado=1 order by orden asc";
}

  /*  if ($accion == 2) { //Editar
	$tituloForm = "MODIFICACI&Oacute;N DE USUARIOS";


    }
    if ($accion == 3) {	//Consultar
    	$read = "readonly";
	$read2 = "disabled";
	$tituloForm = "CONSULTA DE USUARIOS";
    }*/

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

?>

 <script language="JavaScript" src="<?=$ruta_raiz?>/js/prototype.js" type ="text/javascript"></script> 
<script language="JavaScript" src="<?=$ruta_raiz?>/js/general1.js"  type="text/javascript"></script> 
<script language="javascript">
function ltrim(s) {
   return s.replace(/^\s+/, "");
}

function ValidarInformacion()
{
	if(ltrim(document.forms[0].usr_depe.value)=='0')
	{	alert("Seleccione el Area del Usuario.");
		return false;
	}
	if(ltrim(document.forms[0].usr_login.value)=='')
	{	alert("El campo Login del Usuario es obligatorio.");
		return false;
	}
	if(ltrim(document.forms[0].usr_nombre.value)=='' || ltrim(document.forms[0].usr_apellido.value)=='')
	{	alert("Los campos de Nombres y Apellidos son obligatorios.");
		return false;
	}
	if (document.forms[0].usr_estado.checked) {
	    if (!validarCedula(document.forms[0].usr_cedula.value)) 
	    	return false;
	}
	if (!isEmail(document.forms[0].usr_email.value,true))
	{	alert("El campo mail del Usuario no tiene formato correcto.");
		return false;
	}
	return true;
}
</script>
<body>
  <!-- <form name='frmCrear' id='frmCrear'  action="listadoUsuariosinarea.php?accion=<?//=$accion?>" method="post"> -->
    <table width="100%" border="1" align="center" class="t_bordeGris">
  	<tr>
    	    <td  class="titulos4">
		<center>
		<p><B><span class=etexto>Administración de Usuarios y Permisos</span></B> </p>
		<p><B><span class=etexto> <?=$tituloForm ?></span></B> </p></center>
	    </td>
	</tr>
    </table>

    <br/>
    
    <table border=0 width="100%" class="borde_tab" cellpadding="0" cellspacing="0" name="usr_datos" id="usr_datos">
	<tr>
         <td class="listado5"  >
         <td title="usuarios" class="listado5" align="center" >
         <select name="Usuarios" id="Usuarios" size="20"  id="usuarios" multiple  class="listado5" >
         <option  selected >---Elegir Usuario---</option>
         <? 
           $i=1;
	   while (!$rsusuarios->EOF) {
            echo "<option id='usuario".$i. "' value='".$rsusuarios->fields['USUA_CODI']."'>".$rsusuarios->fields['USUA_NOMBRE']."</option>" ;
            $rsusuarios->MoveNext();
            $i=$i+1;
	 }

        ?>      

       </select>
</td>
<td class="listado5"    > 
<input align="center"  class="listado5" name="btn_asignar" id="btn_asignar" type="submit" value="    Asignar Área >>  " onclick="asignar_area(<?=$_SESSION['inst_codi']?>);"  /> <div id="cargando_div" style="display:none"><img src="<?=$ruta_raiz?>/imagenes/progress_bar.gif" /></div> </td>
<td class="listado5"  >
<select name="Area" id="Area" size="20"  align="center" class="listado5">
      		<option selected>---Elegir Área---</option>
             <? 
               $i=1;
	       while (!$rsarea->EOF) {
                echo "<option id='area".$i."'     value='".$rsarea->fields['DEPE_CODI']."'>".$rsarea->fields['DEPE_NOMB']."</option>" ;
                $rsarea->MoveNext();
                $i=$i+1;
	        }
            ?>      
            </select>
</td>
	    <!-- <td class="titulos2" width="20%">* <?//=$_SESSION["descDependencia"]?></td> -->
             
	</tr>
    	
    </table>
     
    <table width="100%" class="borde_tab" align="center"  name="usr_permisos" id="usr_permisos">
     <tr><br> </tr> 
     <Tr > <h3> Listado de usuarios </h3> </Tr>
     <Tr>      
      <TH > Cédula</TH>
      <TH > Nombre</TH>
      <TH > Área Asignada</TH>
      <TH >Acción
      <div id="cargando_div1" style="display:none"><img src="<?=$ruta_raiz?>/imagenes/progress_bar.gif" /></div> </TH>
     </Tr>
    
     
    <? 
	$sq = "select u.usua_codi, u.usua_nombre, u.usua_cedula, u.depe_nomb, u.inst_codi 
            from usuario u where u.usua_esta = 1 and coalesce(u.depe_codi,-1)<>-1 
            and u.inst_codi = ".$_SESSION['inst_codi'];
        if ($depe_codi_admin!=0)
            $sq.=" and depe_codi in ($depe_codi_admin)";
        $sq.=" order by u.usua_nombre";
	
	$rsusuarea = $db->conn->Execute($sq);
    $i=0;
        while (!$rsusuarea->EOF) {
            if($i==1)
            {
         $regis = "<tr class='listado1'    >"."<td>";
         $i=0;
            }
         else
         {
         $regis= "<tr class='listado2'    >"."<td>";
 $i=1;
         }
       //  echo '-'.$i.'-';
	      $regis =  $regis . $rsusuarea->fields['USUA_CEDULA']."</td><td>".	$rsusuarea->fields['USUA_NOMBRE']."</td><td>".
              $rsusuarea->fields['DEPE_NOMB'] ."</td> ";
             echo  $regis;
//          $sn="select depe_codi from dependencia where dep_sigla = 'SN' and inst_codi = ". $_SESSION['inst_codi'];
//          $rsn = $db->conn->Execute($sn);
         //$i=$i+1;
         echo "<td> <input align='center'  class='listado2' name='btn_quitar' id='btn_quitar' type='submit' value='Quitar Área ' onclick='quitar_area(".$rsusuarea->fields['USUA_CODI'].",null,".$_SESSION["inst_codi"].");'  /></tr>";
	 $rsusuarea->MoveNext();
	}
 ?>


    </table>
    <br/>
    <br/>
    <center><input  name="btn_accion" type="button" class="botones" value="Regresar" onClick="location='./mnuUsuarios.php'"/></center>

    <br/>
    <br/>
<!--   </form> -->

    <script>
	function asignar_area(id_institucion) {
           var i = 1;
	   var indexarea;
           var usuario="usuario";
           var id_usuario;
           //var id_institucion;
           var id_area ;
           var data;
           var clazz = "usuario";  
           var action = "update_usuarioarea";
  
  
          //alert(frmCrear.Usuarios.value);
           //alert(document.getElementById("Area").selectedIndex);
           //alert(document.getElementById("Area").options[document.getElementById("Area").selectedIndex]);
            Element.show("cargando_div");
            indexarea = document.getElementById("Area").selectedIndex;
            indexarea="area"+indexarea;
           // alert(indexarea);
           
           if (document.getElementById("Area").selectedIndex  == 0 ){
               alert("No ha seleccionando el Area");
               Element.hide("cargando_div");
            }
            if (document.getElementById("Usuarios").selectedIndex  == 0 ){
               alert("No ha seleccionando Usuarios");
               Element.hide("cargando_div");
            } 
          if ( document.getElementById("Area").selectedIndex > 0 && document.getElementById("Usuarios").selectedIndex > 0 ){
          	for (i=1;i< document.getElementById("Usuarios").length;i++) {
            	     usuario="usuario"+i;
            	     //alert(usuario);
            	if (document.getElementById(usuario).selected ) {
	                //alert(document.getElementById(usuario).selected);
                        //alert(document.getElementById(indexarea).selected);
                	if (document.getElementById(indexarea).selected  ) {
                       	    id_usuario = document.getElementById(usuario).value; 
                            id_area = document.getElementById(indexarea).value;
                             //data="id_usuario="+id_usuario+"&id_area="+id_area+"&id_inistitucion="+5; 
                 	     //data='data=[id_usuario='+id_usuario+';id_area='+id_area+';id_institucion='+5+']';
			     data=id_usuario+','+id_area+','+id_institucion; 					 					
                             //data[1]='id_area='+id_area;
                             //data[2]='id_institucion='+5;
//id_institucion;
			     //alert(data);
                            ajax_call ( data, clazz, action, ver_datos );
                        
                            
                	}
			
		    }
          	}
	   }
          

	}
        function quitar_area(id_usuario,id_area,id_institucion){
        Element.show("cargando_div1");
	var data;
        var clazz = "usuario";  
        var action = "update_usuarioarea";         
        data=id_usuario+','+id_area+','+id_institucion; 	
        
        ajax_call ( data, clazz, action, ver_datos );
}
        function ver_datos(result,resp){	


	if (resp!="")  	{
	//alert("error en ajax ");
        Element.hide("cargando_div");
 	alert(resp);// si hay errores se mostrar� el alert
	}
	else {	  
	       
	Element.hide("cargando_div");
          Element.hide("cargando_div1");
	   //alert("Los usuarios fueron asignados al area seleccionada");
           window.location.reload();
	}

 

       }
    </script>

</body>
</html>
