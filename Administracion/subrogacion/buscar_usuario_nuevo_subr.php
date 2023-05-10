<?php
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
$ruta_raiz = "../..";
session_start();
if($_SESSION["usua_admin_sistema"]!=1) die("");
include_once "$ruta_raiz/rec_session.php";
if (isset ($replicacion) && $replicacion && $config_db_replica_adm_buscar_usuario_nuevo_subr!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_adm_buscar_usuario_nuevo_subr);

include_once "$ruta_raiz/obtenerdatos.php";

include_once "$ruta_raiz/funciones_interfaz.php";

include_once "../usuarios/mnuUsuariosH.php";
echo "<html>".html_head();

?>
<?php
include_once "$ruta_raiz/js/ajax.js";

if (!isset($buscar_tipo)) $buscar_tipo = 1;


?>

<script type="text/JavaScript">
// Coloca un usuario como destinatario o remitente
//cod_usr = usuario
//tipo = subrogado/subrogante
//dependencia = area del subrogado
//cargo_tipo = si es jefe o funcionario
function pasar(cod_usr, tipo, dependencia,cargo_tipo)
{    
   
    var nomDivDePara = "dePara";
    var subr_x_area = 1;
    if (document.getElementById('subr_x_area'))
    subr_x_area = document.getElementById('subr_x_area').value;//subrogacion por areas
    if (tipo==1){
        
        document.getElementById('usr_subrogado').value=cod_usr;
        document.getElementById('depe_subrogado').value=dependencia;//dependencia
        document.getElementById('cargo_subrogado').value=cargo_tipo;//dependencia
        subrogado = document.getElementById('usr_subrogado').value;
        subrogante = document.getElementById('usr_subrogante').value;
        bandera_periodo = document.getElementById('bandera_periodo').value; 
        fecha_desde = document.getElementById('fecha_desde').value;
          fecha_hasta = document.getElementById('fecha_hasta').value;
          hora_desde = document.getElementById('hora_desde').value;
          hora_hasta = document.getElementById('hora_hasta').value;
        cargosubrogado = cargo_tipo;
        depesubrogado = dependencia;
        cargosubrogante = 0;
        depesubrogante = 0;
        datos = "usr_subrogado="+subrogado+"&usr_subrogante="+subrogante+"&bandera_periodo="+bandera_periodo+"&fecha_desde="+fecha_desde+"&fecha_hasta="+fecha_hasta+"&hora_desde="+hora_desde+"&hora_hasta="+hora_hasta;
        nuevoAjax(nomDivDePara, 'GET', 'adm_usuario_subrogante.php', datos);
    }
    else{
        document.getElementById('usr_subrogante').value=cod_usr;
        document.getElementById('depe_subrogante').value=dependencia;//dependencia
        document.getElementById('cargo_subrogante').value=cargo_tipo;//cargo
        subrogado = document.getElementById('usr_subrogado').value;        
        subrogante = document.getElementById('usr_subrogante').value;
        fecha_desde = document.getElementById('fecha_desde').value;
          fecha_hasta = document.getElementById('fecha_hasta').value;
          hora_desde = document.getElementById('hora_desde').value;
          hora_hasta = document.getElementById('hora_hasta').value;
        cargosubrogante = cargo_tipo;
        depesubrogante = dependencia;
        cargosubrogado = 0;
        depesubrogado = 0;
        datos = "usr_subrogado="+subrogado+"&usr_subrogante="+subrogante+"&bandera_periodo="+bandera_periodo+"&fecha_desde="+fecha_desde+"&fecha_hasta="+fecha_hasta+"&hora_desde="+hora_desde+"&hora_hasta="+hora_hasta;
        
    }
    //cargo variables
        depesubrogante=document.getElementById('depe_subrogante').value;//dependencia
        cargosubrogante=document.getElementById('cargo_subrogante').value;//cargo
        depesubrogado=document.getElementById('depe_subrogado').value;//dependencia
        cargosubrogado=document.getElementById('cargo_subrogado').value;//cargo
        bandera_periodo=document.getElementById('bandera_periodo').value;//bandera periodo
       
    if (subrogado!=0){//primero debe seleccionar subrogado
        if (subrogado!='' || subrogante!=''){//si selecciono subrogado y subrogante
            if (subrogado!=subrogante){//para que el mismo subrogante no sea subrogado
                datos = "usr_subrogado="+subrogado+"&usr_subrogante="+subrogante+"&bandera_periodo="+bandera_periodo+"&fecha_desde="+fecha_desde+"&fecha_hasta="+fecha_hasta+"&hora_desde="+hora_desde+"&hora_hasta="+hora_hasta;
                if (depesubrogado!=0 && depesubrogante!=0){//si tiene dependencia
                    //si un jefe de area encarga a otro jefe de area
                    if ((cargosubrogante==1 && cargosubrogado==1) || bandera_periodo== 1)
                        nuevoAjax(nomDivDePara, 'GET', 'adm_usuario_subrogante.php', datos);
                    else{
                        //si un jefe de area encarga a un funcionario administrativo de la misma area
                        if (subr_x_area==1){
                            if ((depesubrogado==depesubrogante) || bandera_periodo== 1)
                             nuevoAjax(nomDivDePara, 'GET', 'adm_usuario_subrogante.php', datos);
                            else{
                             alert("Funcionarios no son de la misma Área");
                             limpiar('te');
                            }
                        }else{
                          nuevoAjax(nomDivDePara, 'GET', 'adm_usuario_subrogante.php', datos);  
                        }
                    }
                }
            }else
                alert("Funcionario Subrogado es igual a Funcionario Subrogante");
       }//si selecciono subrogado y subrogante
    }else{
        //Borro si selecciona antes subrogante.  
        document.formu1.usr_subrogante.value = "";
        document.getElementById('depe_subrogante').value='';//dependencia
        document.getElementById('cargo_subrogante').value='';//cargo    
        alert("Por Favor, Seleccione Funcionario Subrogado (Jefe de Área)");
    }
    
}

function buscar_resultado(desde){
   
    style_display = '';
    var nomDivResultado = "resultado";
    document.getElementById(nomDivResultado).innerHTML = '<center>Por favor espere mientras se realiza la b&uacute;squeda.<br>&nbsp;<br>' +
                                                           '<img src="<?=$ruta_raiz?>/imagenes/progress_bar.gif"><br>&nbsp;</center>';

    
    var datos = "ent=<?=$ent?>" +
                
                "&buscar_tipo=" + document.formu1.buscar_tipo.value +
                "&buscar_nom=" + document.formu1.buscar_nom.value +
                "&buscar_car=" + document.formu1.buscar_car.value +
                "&buscar_inst=" + document.formu1.buscar_inst.value +
                "&buscar_depe=" + document.formu1.buscar_depe.value;
            
    nuevoAjax(nomDivResultado, 'POST', 'buscar_usuario_resultado_subr.php', datos);
    
    //return;
}

function ver_de_para(){
    
    var nomDivDePara = "dePara";
    document.getElementById(nomDivDePara).innerHTML = '<center>Por favor espere mientras se realiza la b&uacute;squeda.<br>&nbsp;<br>' +
                                                           '<img src="<?=$ruta_raiz?>/imagenes/progress_bar.gif"><br>&nbsp;</center>';
    var datos = "krd=" + "<?=$krd?>" +
                "&ent=" + "<?=$ent?>" +
                "&usr_subrogante=" + document.formu1.usr_subrogante.value +
                "&usr_subrogado=" + document.formu1.usr_subrogado.value +
                "&bandera_periodo=" + document.formu1.bandera_periodo.value +
                "&fecha_desde=" + document.formu1.fecha_desde.value + 
                "&fecha_hasta=" + document.formu1.fecha_hasta.value +
                "&hora_desde=" + document.formu1.hora_desde.value +
                "&hora_hasta=" + document.formu1.hora_hasta.value;
    nuevoAjax(nomDivDePara, 'GET', 'adm_usuario_subrogante.php', datos);
    
}

function refrescar_pagina() {
    ver_de_para();
}


//tipo=te,todo
function limpiar(tipos){    
    if (tipos=='todo'){
        subro="";
        document.formu1.usr_subrogante.value = "";
        document.formu1.usr_subrogado.value = "";    
        document.getElementById('depe_subrogante').value='';//dependencia
        document.getElementById('cargo_subrogante').value='';//cargo
        document.getElementById('depe_subrogado').value='';//dependencia
        document.getElementById('cargo_subrogado').value='';//cargo
    }else{
         subro="";
         document.getElementById('depe_subrogante').value='';//dependencia
         document.getElementById('cargo_subrogante').value='';//cargo
         document.formu1.usr_subrogante.value = "";
    }
    var nomDivDePara = "dePara";
    var datos = "krd=" + "<?=$krd?>" +
                "&ent=" + "<?=$ent?>" +
                "&usr_subrogante=" + document.formu1.usr_subrogante.value +
                "&usr_subrogado=" + document.formu1.usr_subrogado.value+
                "&bandera_periodo=" + document.formu1.bandera_periodo.value;
    
    nuevoAjax(nomDivDePara, 'GET', 'adm_usuario_subrogante.php', datos);
}

function validar_fecha_maxima(){
        var fecha_desde = document.getElementById('txt_fecha_desde').value;
        var fecha_hasta = document.getElementById('txt_fecha_hasta').value;
        
        var hora_desde = document.getElementById('txt_hora_desde').value;
        var hora_hasta = document.getElementById('txt_hora_hasta').value;
        fechaini = fecha_desde.split("-");
        comienzaD = fechaini[0] + fechaini[1] + fechaini[2];
        fechafin = fecha_hasta.split("-");
	finalizaD = fechafin[0] + fechafin[1] + fechafin[2];
	
        horaini = hora_desde.split(":");
        comienzaH = horaini[0];
        //alert(horaini)
        horafin = hora_hasta.split(":");
        finalizaH = horafin[0];
        
        if (comienzaD==finalizaD)
            {
                
                comienzaH = parseInt(comienzaH,10);// a entero
                finalizaH = parseInt(finalizaH,10);//a entero
                
                if (comienzaH>=5 && finalizaH>=5){
                    if (comienzaH < finalizaH){
                        document.getElementById('bandera_periodo').value = 1;
                        grabar_subrogante();
                        return true;
                    }                        
                    else{
                        alert("Hora Fin debe ser mayor a Hora Inicio");                        
                        document.getElementById('bandera_periodo').value = 0;
                        grabar_subrogante();
                        return false;
                    }
                }else{
                    alert("Horas deben ser mayores a las 5am");
                    document.getElementById('bandera_periodo').value = 0;
                    grabar_subrogante();
                    return false;
                }
//            
            
         }else if(comienzaD>finalizaD){
             alert ("Fecha Inicio debe ser Mayor a Fecha Fin");
             document.getElementById('bandera_periodo').value = 0;
             grabar_subrogante();
             return false;
         }
         else{
             document.getElementById('bandera_periodo').value = 1;
             grabar_subrogante();
          return true;   
         }
}
//funcion valida la hora y fecha
//bandera_periodo determina si aparece los botones
function grabar_subrogante(){
        document.getElementById('fecha_desde').value = document.getElementById('txt_fecha_desde').value;
        document.getElementById('fecha_hasta').value = document.getElementById('txt_fecha_hasta').value;
        document.getElementById('hora_desde').value = document.getElementById('txt_hora_desde').value;
        document.getElementById('hora_hasta').value = document.getElementById('txt_hora_hasta').value;
        bandera_periodo = document.getElementById('bandera_periodo').value;
        subrogado = document.getElementById('usr_subrogado').value;        
        subrogante = document.getElementById('usr_subrogante').value;
        fecha_desde = document.getElementById('fecha_desde').value;
        fecha_hasta = document.getElementById('fecha_hasta').value;
        hora_desde = document.getElementById('hora_desde').value;
        hora_hasta = document.getElementById('hora_hasta').value;
        datos = "usr_subrogado="+subrogado+"&usr_subrogante="+subrogante+"&bandera_periodo="+bandera_periodo+"&fecha_desde="+fecha_desde+"&fecha_hasta="+fecha_hasta+"&hora_desde="+hora_desde+"&hora_hasta="+hora_hasta;
        var nomDivDePara = "dePara";
        nuevoAjax(nomDivDePara, 'GET', 'adm_usuario_subrogante.php', datos);
//        
        
    }       

function devolver(){
    subrogado_get = document.getElementById('subrogado_get').value;
    depe_codi_get = document.getElementById('depe_codi_get').value;
    cargo_tipo_get = document.getElementById('cargo_tipo_get').value;
    
    pasar(subrogado_get,1,depe_codi_get,cargo_tipo_get);
}
</script>

<body bgcolor="#FFFFFF">
<form method="post" name="formu1" id="formu1" action="javascript: buscar_resultado('boton');" >
    
    <?php
    graficarMenu($usr_codigo,$tiene_subrogacion,$usr_perfil,$usr_depe,4);
    
    
    
if (isset($_GET['usr_subrogado'])){
    $usr_subrogado = 0 + $_GET['usr_subrogado'];
    $depe_codi_get = 0 + $_GET['depe_codi_get'];
    
    $cargo_tipo_get = 0 + $_GET['cargo_tipo_get'];
    ?><input type="hidden" name="subrogado_get" id="subrogado_get" value="<?=$usr_subrogado?>" />
    <input type="hidden" name="depe_codi_get" id="depe_codi_get" value="<?=$depe_codi_get?>" /> 
    <input type="hidden" name="depe_codi_get" id="cargo_tipo_get" value="<?=$cargo_tipo_get?>" />
        
<?php }else
    $depe_codi_get=0;
?>
    
    
 <textarea id="usr_subrogante" name="usr_subrogante" style="display:none" cols="1" rows="1"><?=$usr_subrogante?></textarea>
   <textarea id="usr_subrogado" name="usr_subrogado" style="display:none" cols="1" rows="1"><?=$usr_subrogado?></textarea>
<input type="hidden" name="depe_subrogante" id="depe_subrogante" value="" />
   <input type="hidden" name="depe_subrogado" id="depe_subrogado" value="" />   
   <input type="hidden" name="cargo_subrogante" id="cargo_subrogante" value="" />
   <input type="hidden" name="cargo_subrogado" id="cargo_subrogado" value="" />  
   <input type="hidden" name="bandera_periodo" id="bandera_periodo" value="" />
   <input type="hidden" name="fecha_desde" id="fecha_desde" value="" />  
   <input type="hidden" name="fecha_hasta" id="fecha_hasta" value="" />  
   <input type="hidden" name="hora_desde" id="hora_desde" value="" />  
   <input type="hidden" name="hora_hasta" id="hora_hasta" value="" /> 
   <input type="hidden" name="subr_x_area" id="subr_x_area" value="<?=$subrogacionXareas?>" /> 

<table border=0 width="100%" class="borde_tab" cellpadding="0" cellspacing="5">
  <tr>
      <td colspan="4" width="13%" class="listado5"><font class="tituloListado"><center><? if ($subrogacionXareas==1) echo "Subrogación por Áreas"; else echo "Subrogación Institucional";?></center></font></td>
</tr>
   <tr> 
<td width="13%" class="listado5"><font class="tituloListado"><center>BUSCAR<br>SERVIDOR PÚBLICO (SUBROGADO/SUBROGANTE):</center></font></td>
    <td width="75%" class="listado5" valign="middle">
    <table>
       
<!--        <tr>-->
<!--            <td width="20%" align="right"><span class="listado5">Tipo de Usuario: </span></td>-->
<!--            <td width="25%">-->
                <input type="hidden" name="buscar_tipo" id="buscar_tipo" value="1" />   

        <tr>
            <td width="20%" align="right"><span class="listado5"><label id="lbl_datos_usua">Nombre / C.I.:</label> </span> </td>
            <td width="16%"><input type=text name="buscar_nom" value="<?=$buscar_nom?>" class="tex_area"/></td>
            <td width="16%" align="right"><span class="listado5"><?=$descCargo?>: </span> </td>
            <td width="16%"><input type=text name="buscar_car" value="<?=$buscar_car?>" class="tex_area"></td>
        </tr>
        <tr id="tr_institucion" <?php if ($buscar_tipo==2) echo "style='display:none'";?>>
            <td  align="right"><span class="listado5"><?=$descEmpresa?>: </span></td><td colspan="5">
            <?php
            
                $where = "";
                $inst_codi = $_SESSION["inst_codi"];
                if ($_SESSION["usua_codi"]==0){
                    $sql = "select distinct inst_nombre, inst_codi from institucion where inst_estado=1 and inst_codi>1 $where order by 1";
                     $rs=$db->conn->query($sql);
                    if($rs) {
                        print $rs->GetMenu2("buscar_inst", "0", "0:&lt;&lt; Todas las Instituciones &gt;&gt;", false,"","id='buscar_inst' class='select'" );
                    }
                 }
                else{
                    ?>
                    <select id="buscar_inst" name=buscar_inst class='select' <?=$read2?>>
                    <option value='<?=$_SESSION["inst_codi"]?>' <? echo "selected"?>> <?php echo $_SESSION["inst_nombre"]?> </option>                    
                    </select>
                <?php }
                    
               
            
            ?>
            </td>
        </tr>
        <tr><td  align="right"><span class="listado5">Dependencia: </span></td><td>
         <?php
          
            $depe_codi_admin = obtenerAreasAdmin($_SESSION["usua_codi"],$_SESSION["inst_codi"],$_SESSION["usua_admin_sistema"],$db);
            $sql="select depe_nomb, depe_codi from dependencia where depe_estado=1 
            and inst_codi=".$_SESSION["inst_codi"];
            if ($depe_codi_admin!=0)
            $sql.=" and depe_codi in ($depe_codi_admin)";            
            $sql.=" order by 1 asc";
            //$sql="select depe_nomb, depe_codi from dependencia where depe_estado=1 and inst_codi=".$_SESSION["inst_codi"]." order by 1 asc";
            $rs=$db->conn->query($sql);
            $area = 0 +$depe_codi_get;
            if($rs) print $rs->GetMenu2("buscar_depe", $area, "0:&lt;&lt; Seleccione &gt;&gt;", false,"","class='select' id='buscar_depe'  style='width: 300px;'");
            ?>
                
            </td></tr>

      </table>
    </td>
    <td width="12%" align="center" class="listado5" >
        <input type="submit" name="btn_buscar" value="Buscar" class="botones" title="Buscar Persona">
    </td>
    <td width="12%" align="center" class="listado5" >
    <input  name="btn_accion" type="button" class="botones" value="Regresar" onClick="location='../usuarios/mnuUsuarios.php'"/>
    </td>
  </tr>


</table>
<br>
<table class=borde_tab width="100%" cellpadding="0" cellspacing="1">
    <tr class=listado2>	<td colspan="10">
        <center><b><?php echo "SELECCIONE SERVIDOR PÚBLICO SUBROGADO Y SUBROGANTE";?></b></center>
    </td></tr>
</table>
<div id="resultado" class="estiloDivPeq"></div>
<br>
<table class=borde_tab width="100%" cellpadding="0" cellspacing="4">
    <tr class=listado2>
        <td colspan="10">
            <center><b>EDICIÓN SUBROGACIÓN DE PUESTO</b></center>
        </td>
    </tr>
</table>
</form>
 <div id="dePara" height="60px"><input type='hidden' name='flag_inst' id='flag_inst' value='0'></div>
</body>
</html>
<?php
if (isset($_GET['usr_subrogado'])){
echo "<script>devolver()</script>";
}?>