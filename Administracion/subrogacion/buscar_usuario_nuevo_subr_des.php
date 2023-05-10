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
if (isset ($replicacion) && $replicacion && $config_db_replica_adm_buscar_usuario_nuevo_subr_des!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_adm_buscar_usuario_nuevo_subr_des);

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
function desactivar(codigo_subrogante,codigo_subrogado){
    //alert(codigo_subrogante)
    desde = "";
    datos = "codigo_subrogante="+ codigo_subrogante+"&codigo_subrogado="+codigo_subrogado;
    if (codigo_subrogante!=''){
        var respuesta = confirm("Desea Desactivar la Subrogación?")
	if (respuesta){
		nuevoAjax('divActualizar', 'GET', 'desactivar_usuario_subrogante.php', datos);
                timerID = setTimeout("buscar_resultado(desde)", 200); 
	}	
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
            
    nuevoAjax(nomDivResultado, 'POST', 'buscar_usuario_resultado_subr_des.php', datos);
    
    //return;
}

function ver_de_para(){
    
    var nomDivDePara = "dePara";
    document.getElementById(nomDivDePara).innerHTML = '<center>Por favor espere mientras se realiza la b&uacute;squeda.<br>&nbsp;<br>' +
                                                           '<img src="<?=$ruta_raiz?>/imagenes/progress_bar.gif"><br>&nbsp;</center>';
    var datos = "krd=" + "<?=$krd?>" +
                "&ent=" + "<?=$ent?>" +
                "&usr_subrogante=" + document.formu1.usr_subrogante.value +
                "&usr_subrogado=" + document.formu1.usr_subrogado.value;
    
    nuevoAjax(nomDivDePara, 'GET', 'adm_usuario_subrogante.php', datos);
    
}



</script>
<body bgcolor="#FFFFFF">
<form method="post" name="formu1" id="formu1" action="javascript: buscar_resultado('boton');" >
 <textarea id="usr_subrogante" name="usr_subrogante" style='display:none' cols="1" rows="1"><?=$usr_subrogante?></textarea>
   <textarea id="usr_subrogado" name="usr_subrogado" style='display:none' cols="1" rows="1"><?=$usr_subrogado?></textarea>
<input type="hidden" name="depe_subrogante" id="depe_subrogante" value="" />
   <input type="hidden" name="depe_subrogado" id="depe_subrogado" value="" />   
   <input type="hidden" name="cargo_subrogante" id="cargo_subrogante" value="" />
   <input type="hidden" name="cargo_subrogado" id="cargo_subrogado" value="" />  
<?php
    graficarMenu($usr_codigo,$tiene_subrogacion,$usr_perfil,$usr_depe);
    ?>
<table border=0 width="100%" class="borde_tab" cellpadding="0" cellspacing="5">
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
                    //$sql = "select distinct inst_nombre, inst_codi from institucion where inst_estado=1 and inst_codi= $inst_codi $where order by 1";
               
            
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
            //echo $sql;
            //$sql="select depe_nomb, depe_codi from dependencia where depe_estado=1 and inst_codi=".$_SESSION["inst_codi"]." order by 1 asc";
            $rs=$db->conn->query($sql);
            if($rs) print $rs->GetMenu2("buscar_depe", "0", "0:&lt;&lt; Seleccione &gt;&gt;", false,"","class='select' id='buscar_depe'  style='width: 300px;'");
            ?>
                
            </td></tr>
      </table>
    </td>
    <td width="12%" align="center" class="listado5" >
        <input type="submit" name="btn_buscar" value="Buscar" class="botones" title="Buscar Persona">
    </td>
    <td width="12%" align="center" class="listado5" >
    <input  name="btn_accion" type="button" class="botones" value="Regresar" onClick="history.back();"/>
    </td>
  </tr>


</table>
<br>
<table class=borde_tab width="100%" cellpadding="0" cellspacing="1">
    <tr class=listado2>	<td colspan="10">
        <center><b><?php echo "DESACTIVAR SUBROGACIÓN";?></b></center>
    </td></tr>
</table>
<div id="resultado" class="estiloDivPeq"></div>
<br>
<table class=borde_tab width="100%" cellpadding="0" cellspacing="4">
   
</table>
    <div id="dePara" height="60px"><input type='hidden' name='flag_inst' id='flag_inst' value='0'></div>
    <div id="divActualizar" height="60px"></div>



</form>

</body>
</html>
