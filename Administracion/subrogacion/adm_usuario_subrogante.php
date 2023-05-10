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
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/obtenerdatos.php";
p_register_globals(array('accion', 'usr_codigo', 'usr_destino'));

session_start();


if ($_SESSION["usua_admin_sistema"] != 1) {
    echo html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
    die("");
}
//$depe_destino = (isset($depe_destino)) ? $depe_destino : $_SESSION['depe_codi'];
if(isset($_GET['usr_subrogante']))
$usr_codigo = $_GET['usr_subrogante'];
include_once "$ruta_raiz/rec_session.php";

$read = "readonly";
$read2 = "";
$read3 = "readonly";        
        //DATOS DE USUARIO  A SER SUBROGANTE
        $sql = "select * from usuarios where usua_codi=$usr_codigo";
        $rs = $db->conn->Execute($sql);
        $usr_depe 	= $rs->fields["DEPE_CODI"];
        $area_inicio    = $rs->fields["DEPE_CODI"];
        $usr_perfil 	= $rs->fields["CARGO_TIPO"];
        $perfil_inicio  = $rs->fields["CARGO_TIPO"];
        $usr_login 	= $rs->fields["USUA_LOGIN"];
        $usr_cedula 	= $rs->fields["USUA_CEDULA"];
        $usr_nombre 	= $rs->fields["USUA_NOMB"];
        $usr_apellido 	= $rs->fields["USUA_APELLIDO"];
        $usr_titulo     = $rs->fields["USUA_TITULO"];
        $usr_abr_titulo = $rs->fields["USUA_ABR_TITULO"];
        $usr_cargo      = $rs->fields["USUA_CARGO"];
       
        $usr_cargo_cabecera= $rs->fields["USUA_CARGO_CABECERA"];
        $usr_sumilla    = $rs->fields["USUA_SUMILLA"];
        $usr_inst_nombre = $rs->fields["INST_NOMBRE"];
        $usr_responsable_area=($rs->fields["USUA_RESPONSABLE_AREA"] == 1)? "checked" : "";
        //$puesto         = $rs->fields["PUESTO"];
        $cargo_id       = $rs->fields["CARGO_ID"];
        $usr_email      = $rs->fields["USUA_EMAIL"];
        $usr_obs        = $rs->fields["USUA_OBS"];
        $codi_ciudad    = $rs->fields["CIU_CODI"];
        $usr_nuevo      = ($rs->fields["USUA_NUEVO"] == 1) ? "" : "checked";
        $usr_estado     = ($rs->fields["USUA_ESTA"] == 0) ? "" : "checked";
        $usr_firma_path =$rs->fields["USUA_FIRMA_PATH"];
        //direccion y telefono
        $usr_direccion  = $rs->fields["USUA_DIRECCION"];
        $usr_telefono   = $rs->fields["USUA_TELEFONO"];
        //Datos del ultimo usuario que actualizó el registro
        $usr_codi_actualiza     = $rs->fields["USUA_CODI_ACTUALIZA"];
        $usr_fecha_actualiza    = $rs->fields["USUA_FECHA_ACTUALIZA"];
        $usr_obs_actualiza      = $rs->fields["USUA_OBS_ACTUALIZA"];
        $checked_tipo_identificacion = ($rs->fields["TIPO_IDENTIFICACION"]) ? "checked" : "";
        //PARA SUBROGANTE
        //Selecciono el jefe de area
        $sqlJefeArea = "select * from usuarios where usua_codi = ".$_GET['usr_subrogado'];        
        $rsJefeArea = $db->conn->Execute($sqlJefeArea);
        $usr_nombreJefearea 	= $rsJefeArea->fields["USUA_NOMB"];
        $usr_apellidoJefearea 	= $rsJefeArea->fields["USUA_APELLIDO"];
        $usr_tituloJefearea     = $rsJefeArea->fields["USUA_TITULO"];
        $usr_abr_tituloJefearea = $rsJefeArea->fields["USUA_ABR_TITULO"];
        $usr_cargoJefearea      = $rsJefeArea->fields["USUA_CARGO"];
        
        $repE = array(" Subrogante", ",");
        $usr_cargo = str_replace($repE, "", $usr_cargoJefearea);
        
        $usr_cargo      = $usr_cargo.", Subrogante";

        
        $usr_emailJefearea      = $rsJefeArea->fields["USUA_EMAIL"];
        //--
        $usr_depeJefeArea = $rsJefeArea->fields["DEPE_CODI"];
        
        $sqlDepJefe = "select depe_codi,depe_nomb from dependencia where depe_codi = $usr_depeJefeArea";
        $rsDepeJefe = $db->conn->Execute($sqlDepJefe);
        $depNombJefe 	= $rsDepeJefe->fields["DEPE_NOMB"];
        $depCodJefe 	= $rsDepeJefe->fields["DEPE_CODI"];
        
//        $sqlp = "select p.descripcion, p.descripcion_larga, p.id_permiso, count(pc.id_permiso) as permiso, perfil
//                from permiso p left outer join permiso_usuario pc on p.id_permiso=pc.id_permiso and pc.usua_codi=$usr_codigo where p.estado=1
//                group by 5, p.descripcion, p.descripcion_larga, p.id_permiso, p.orden order by 5, p.orden asc";
   
?>

<? require_once "$ruta_raiz/js/ajax.js";?>

<? echo "<html>".html_head(); /*Imprime el head definido para el sistema*/?>
<script type="text/javascript" language="JavaScript" src="<?=$ruta_raiz?>/js/formchek.js"></script>
<script type="text/javascript" language="JavaScript" src="<?=$ruta_raiz?>/js/validar_cedula.js"></script>
<script type="text/javascript">
function ltrim(s) {
   return s.replace(/^\s+/, "");
}
function copia(){
        document.getElementById('usr_cargo_cabecera').value=document.getElementById('usr_cargo').value
}


</script>
<script type="text/javascript" src="<?=$ruta_raiz?>/js/spiffyCal/spiffyCal_v2_1.js"></script>
<body>
  <form name='frmCrear' action="grabar_usuario_subrogante.php" method="post" ENCTYPE='multipart/form-data'>
   <input type="hidden" name="usr_subrogante" id="usr_subrogante" value="<?=$usr_subrogante?>" />
   <input type="hidden" name="usr_subrogado" id="usr_subrogado" value="<?=$usr_subrogado?>" />   
   <input type="hidden" name="depe_subrogante" id="depe_subrogante" value="" />
   <input type="hidden" name="depe_subrogado" id="depe_subrogado" value="" /> 
   <input type="hidden" name="cargo_subrogante" id="cargo_subrogante" value="" />
   <input type="hidden" name="cargo_subrogado" id="cargo_subrogado" value="" /> 
   <input type="hidden" name="dependencia_jefe" id="dependencia_jefe" value="<?=$usr_depeJefeArea?>" />   
   <?php //$fecha_tarea= date('Y-m-d');?>
   <input type="hidden" name="fecha_hoy" id="fecha_hoy" value="<?=$fecha_tarea?>" />
    <table width="100%" border="1" align="center" class="t_bordeGris">
  	<tr>
    	    <td class="listado2" colspan="4">
		<center>
		<B><span class=etexto>Servidor Público Subrogado (Jefe de Área)</span></B></center>
            </td>
	</tr>
         <tr><td class="titulos2" width="11%">Subrogación de:</td>
             <td class="listado2" width="39%">
             <b><?php echo $usr_nombreJefearea." ".$usr_apellidoJefearea;?></b>                   
             </td>
         <td class="titulos2" width="11%">Área: </td>
             <td class="listado2" width="39%"><b><?php echo $depNombJefe;?></b>             
             </td>
         </tr>
         <tr><td class="titulos2" width="11%">Puesto:</td>
             <td class="listado2" width="39%">
             <b><?php echo $usr_cargoJefearea;?></b>                   
             </td>
         <td class="titulos2" width="11%">Correo Electrónico: </td>
             <td class="listado2" width="39%"><b><?php echo $usr_emailJefearea;?></b>             
             </td>
         </tr>
    </table>
    <input type='hidden' name='usr_codigo' value="<?=$usr_codigo?>">    
    <input type="hidden" name="area_inicio" id="area_inicio" value="<?=$area_inicio?>">
    <table width="100%" border="1" align="center" class="t_bordeGris" id="usr_datos">
        <tr>
    	    <td class="titulos4" colspan="4">
		<center>
		<B><span class=etexto>Servidor Público Subrogante</span></B></center>
	    </td>
	</tr>
        <tr>
            <td class="titulos2" width="20%">* C&eacute;dula </td>
            <td class="listado2" width="30%">
                <input type="text" name="usr_cedula" id="usr_cedula" value="<?php echo $usr_cedula; ?>" size="20" maxlength="10" <?php echo $read; ?>/>
                <input type="checkbox" name="usr_tipo_ident" id="usr_tipo_ident" value="0" <?php echo $checked_tipo_identificacion?> disabled>Es Pasaporte
            </td>
            <td class="titulos2" width="20%"> Usuario </td>
            <td class="listado2" width="30%"><?=substr($usr_login,1)?></td>
        </tr>
        <tr>
            <td class="titulos2">* Nombre &nbsp;&nbsp;&nbsp;                
            </td>
            <td class="listado2">
                <input type="text" name="usr_nombre" id="usr_nombre" value="<?php echo $usr_nombre; ?>" size="50" maxlength="100" <?php echo $read; ?>>
            <td class="titulos2">* Apellido &nbsp;&nbsp;&nbsp;                
            </td>
            <td class="listado2">
                <input type="text" name="usr_apellido" id="usr_apellido" value="<?php echo $usr_apellido; ?>" size="50" maxlength="100" <?php echo $read; ?>>
            </td>
        </tr>
        <tr>
            <td class="titulos2" width="20%"> <?php echo ($_SESSION["inst_codi"]==1) ? $descEmpresa : "* $descDependencia";?></td>
            <td class="listado2" width="30%">
            <?php
            echo $depNombJefe;
            ?>
            </td>
            <td class="titulos2"> * Ciudad </td>
            <td class="listado2">
                <div id='usr_ciu'><?=$usr_ciudad?></div>
            </td>
        </tr>
        <tr>
            <td class="titulos2"> Abr. y T&iacute;tulo </td>
            <td class="listado2">          
                <input type="text" name="usr_abr_titulo" id="usr_abr_titulo" value="<?=$usr_abr_titulo?>" size="4" maxlength="30" <?php echo $read3;?>>
                <input type="text" name="usr_titulo" id="usr_titulo" value="<?php echo $usr_titulo; ?>" size="24" maxlength="100" <?php echo $read3;?>>
            </td>
            <td class="titulos2"> * Correo electr&oacute;nico </td>
            <td class="listado2" colspan="<?=$colspan?>">
                <input type="text" name="usr_email" id="usr_email" value="<?=$usr_email?>" size="50" maxlength="50" <?php echo $read; ?> >
                <!-- onChange="nuevoAjax('div_validar_email', 'POST', 'validar_email.php', 'txt_email='+this.value);" -->
            </td>
        </tr>
        <tr>
            <td class="titulos2"> * Puesto </td>
            <td class="listado2">
               
                <input type="text" name="usr_cargo" id="usr_cargo" value="<?=$usr_cargo?>" size="50" maxlength="200" title="Nombre del puesto que se visualizará  en el pie de firma del documento" <?php echo $read; ?>/>
                
            </td>
            <td class="titulos2"> * Puesto Cabecera </td>
             <td class="listado2">
                 <input type="text" name="usr_cargo_cabecera" id="usr_cargo_cabecera" value="<?=$usr_cargo?>" size="50" maxlength="200"  title="Nombre del puesto que se visualizará en la cabecera del documento" <?php echo $read; ?>>
            </td>
        </tr>
        

        <tr <?php if ($_SESSION["inst_codi"]==1) echo "style='display:none'" ?>>
            <td class="titulos2" width="20%">* Perfil</td>
            <td class="listado2" width="30%"> Subrogante
<!--            <select name=usr_perfil class='select' <?=$read2?>>
                <option value='0' <?if ($usr_perfil==0) echo "selected"?>> Normal </option>
                <option value='1' <?if ($usr_perfil==1) echo "selected"?>> Jefe </option>
                <option value='2' <?if ($usr_perfil==2) echo "selected"?>> Asistente </option>
            </select>-->
            </td>
            <td class="titulos2"> * Iniciales Sumilla </td>
            <td class="listado2">
                <input type="text" name="usr_sumilla" id="usr_sumilla" value="<?=$usr_sumilla?>" size="5" maxlength="5" <?php echo $read; ?>>
<!--                <input type="checkbox" name="usr_area_responsable" id="usr_area_responsable" value="0" onclick="Obtener_val(this)" title="Iniciales del usuario que se visualizará el pie de página (mayùculas) de un documento" <?php echo $usr_responsable_area ."  " .$read; ?>>Responsable de Área-->
                
            </td>      
        </tr>
        <tr><td class="titulos2" colspan="4"><center>Periódo de Subrogación</center></td></tr>        
        <tr valign="top" height="13">
        <td width="15%" class="titulos2">Desde Fecha (yyyy/mm/dd):</td>
        
                    <td width="20%" class="listado2">                    
                    <?php 
                    
                    if (trim($_GET['fecha_desde'])!='')
                        $fecha_desde = $_GET['fecha_desde'];
                        else
                    $fecha_desde = date('Y-m-d');
                        
                    echo dibujar_calendario("txt_fecha_desde", $fecha_desde, $ruta_raiz, "validar_fecha_maxima();"); ?>                        
                    </td>
                    <td width="15%" class="titulos2">Hasta Fecha (yyyy/mm/dd):</td>
                    <td width="12%" class="listado2">                      
                    <?php 
                    if (trim($_GET['fecha_hasta'])!='')
                        $fecha_hasta = $_GET['fecha_hasta'];
                        else
                    $fecha_hasta = date('Y-m-d');
                      echo dibujar_calendario("txt_fecha_hasta", $fecha_hasta, $ruta_raiz, "validar_fecha_maxima();"); ?>
                    </td>
        </tr>
        <tr><td width="15%" class="titulos2">Hora Inicio</td><td width="20%" class="listado2">
                <select id="txt_hora_desde" name="txt_hora_desde" onChange="validar_fecha_maxima();">
                        <?php                        
                        for ($i=0;$i<24;$i++)//horas
                        {
                         
                           $h1=sprintf("%02d",$i).":00";
                          if ($i>='6:00' and $i<='22:00'){
                           ?>
                       <?php if ($_GET['hora_desde']==$h1){ ?>
                           <option value="<?php echo $_GET['hora_desde']; ?>" selected><?php echo $_GET['hora_desde']; ?></option>
                       <?php }else{?>
                           <option value="<?php echo $h1; ?>"><?php echo $h1; ?></option>
                           <?php } ?>
                        <?php }
                                          
                        }
                        ?>
                        </select></td>
                        
            <td width="15%" class="titulos2">Hora Fin</td><td width="20%" class="listado2">
                <select id="txt_hora_hasta" name="txt_hora_hasta" onChange="validar_fecha_maxima();">
                        <?php                        
                        for ($i=0;$i<24;$i++)//horas
                        {
                           $h1=sprintf("%02d",$i).":00";
                          if ($i>='6:00' and $i<='22:00'){
                           ?>
                           <?php if ($_GET['hora_hasta']==$h1){ ?>
                           <option value="<?php echo $_GET['hora_hasta']; ?>" selected><?php echo $_GET['hora_hasta']; ?></option>
                       <?php }else{?>
                           <option value="<?php echo $h1; ?>"><?php echo $h1; ?></option>
                           <?php } ?>
                        
                        <?php }
                                              
                        }
                        ?>
                        </select></td></tr>
    </table>
    <br>
    
        <center>
    <table width="100%"  cellpadding="0" cellspacing="0" >
      <tr>   
    <?php  
    
    if ($_GET['usr_subrogado']!='' and $_GET['usr_subrogante']!='' and $_GET['bandera_periodo']==1){ ?>          
	<td width="33%" align="center">	    
	    	<input name="btn_aceptar" id="btn_aceptar" type="submit" class="botones" value="Grabar"/>
	</td>
        
        <?php }else{
            ?>
        <td width="33%" align="center">
        <blink>Atención: </blink><font color="red">Favor Verifique el Periódo de Subrogación</font>
	</td>
        <?php } ?>
        <td width="33%" align="center">
            <input  class="botones" title='Borrar Datos Seleccionados' type='button' value='Limpiar' onClick="limpiar('todo');"/>
        </td>
	<td  width="33%" align="center">
	    <input  name="btn_accion" type="button" class="botones" value="Regresar" onClick="location='../usuarios/mnuUsuarios.php'"/> <!--location='./mnuUsuarios.php'-->
	</td>
      </tr>
    </table>   
    </center>
    
    <br>
    
  </form>
    

</body>
</html>
