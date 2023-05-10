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
/*****************************************************************************************
**											**
*****************************************************************************************/
$ruta_raiz = ".";
if (isset ($replicacion) && $replicacion && $config_db_replica_info_lista_general!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_info_lista_general);

/*Obtener la ultima observacion de reasignacion*/
$texto_reasignado = "";
if ($carpeta==1 or $carpeta==2 or $carpeta==12) {
    if ($carpeta==12) { // and $datosrad["usua_actu"]!=$_SESSION["usua_codi"]) {
	$sql = "select h.hist_obse, h.hist_referencia, u.usua_nomb||' '||u.usua_apellido as usua_nombre
                from hist_eventos h left outer join usuarios u on h.usua_codi_dest=u.usua_codi
                where h.radi_nume_radi=$verrad and h.sgd_ttr_codigo=9 and h.usua_codi_ori=".$_SESSION["usua_codi"].
              " order by hist_fech desc";
        $texto_reasignado = "Reasignado a:&nbsp;&nbsp;";
    }
    if ($carpeta==1 or $carpeta==2) {
	$sql = "select h.hist_obse, h.hist_referencia, u.usua_nomb||' '||u.usua_apellido as usua_nombre
                 from hist_eventos h left outer join usuarios u on h.usua_codi_ori=u.usua_codi
                 where h.radi_nume_radi=$verrad and h.sgd_ttr_codigo=9 and h.usua_codi_dest=".$_SESSION["usua_codi"]." order by hist_fech desc";
        $texto_reasignado = "Reasignado por:&nbsp;&nbsp;";
    }
    //echo "$sql<br>";
    $rs = $db->query($sql);
    if (!$rs or $rs->EOF) {
        $texto_reasignado = "";
    } else {
        $texto_reasignado = "
            <tr>
                <td bgcolor='#FAAC58' align='right'><font color='#000000'><b>$texto_reasignado</b></font></td>
                <td class='listado2'><b>".$rs->fields["USUA_NOMBRE"]."</b></td>
                <td bgcolor='#FAAC58' align='right'><font color='#000000'><b>Comentario &uacute;ltima reasignaci&oacute;n:&nbsp;&nbsp;</b></font></td>
                <td class='listado2'><b>".$rs->fields["HIST_OBSE"]."<br>Fecha m&aacute;xima de tr&aacute;mite:&nbsp;&nbsp; ".$rs->fields["HIST_REFERENCIA"]."</b></td>
            </tr>";
    }
}
?>

<script type="text/javascript">

    function modificar_opcion(opcion) {
        try {
            dato = document.getElementById("txt_"+opcion).options[document.getElementById("txt_"+opcion).selectedIndex].value;
        } catch (e) {
            dato=document.getElementById("txt_"+opcion).value;
        }
        dato = Base64.encode(Base64.encode(dato));
        nuevoAjax('div_modificar_opcion', 'POST', 'radicacion/cambiar_opciones_documento.php', 'txt_radi_nume=<?=$datosrad["radi_nume_radi"]?>&txt_opcion='+opcion+'&txt_dato='+dato);
        modificar_opcion_mostrar(opcion, 1);
    }

    function modificar_opcion_mostrar(opcion, mostrar) {
        document.getElementById("img_"+opcion).style.display = 'none';
        try {
            document.getElementById("span_"+opcion).innerHTML=document.getElementById("txt_"+opcion).options[document.getElementById("txt_"+opcion).selectedIndex].text;
        } catch (e) {
            if (document.getElementById("span_"+opcion))
                if (document.getElementById("txt_"+opcion))
            document.getElementById("span_"+opcion).innerHTML=document.getElementById("txt_"+opcion).value;
        }
        if (mostrar==2) { // Mostrar combo para editar opcion
            document.getElementById("span_"+opcion).style.display = 'none';
            document.getElementById("txt_"+opcion).style.display = '';
            try {
                document.getElementById("img_guardar_"+opcion).style.display = '';
            } catch (e) {}
        } else { // Ocultar combo para editar opcion
            document.getElementById("span_"+opcion).style.display = '';
            if (document.getElementById("txt_"+opcion))
            document.getElementById("txt_"+opcion).style.display = 'none';
            try {
                document.getElementById("img_guardar_"+opcion).style.display = 'none';
            } catch (e) {}
            if (mostrar == 1) document.getElementById("img_"+opcion).style.display = ''; // Mostrar imagen para editar si tiene permisos
        }
    }


function vista_previa2(ruta,numrad,textrad) {
        windowprops = "top=100,left=100,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=500,height=300";
        
        url = ruta+"/VistaPrevia.php?verrad="+numrad+"&archivo=&textrad="+textrad;
        window.open(url , "Vista_Previa_<?=$noRad?>", windowprops);
        return;
    }
</script>


<div id="div_modificar_opcion" style="display: block;"></div>

<table border="0" width="100%" align="left" class="borde_tab" id=tb_general>
    <?= $texto_reasignado ?>    
    <tr>
        <td width="20%" class="titulos2" align="right">Fecha de <?=$descRadicado4?>:&nbsp;&nbsp;</td>
    	<td width="25%" class="listado2"><?=substr($datosrad["radi_fecha"],0,10).$descZonaHoraria ?></td>
    	<td width="20%" class="titulos2" align="right">Tipo de Documento:&nbsp;&nbsp;</td>
    	<td width="35%" class="listado2">
            <span id="span_tipo_doc"></span>&nbsp;
            <img src="<?=$ruta_raiz?>/imagenes/internas/pencil_add.png" onclick='modificar_opcion_mostrar("tipo_doc",2);' id="img_tipo_doc" align="middle" border="0" title="Modifica el tipo de documento" alt="editar">
<?php
            if ($datosrad["radi_tipo"]==2) $trad_tipo='E'; else $trad_tipo='S';
            $rs = $db->query("select trad_descr, trad_codigo from tiporad where trad_tipo='$trad_tipo' and trad_inst_codi in (0,".$_SESSION["inst_codi"].") order by trad_descr");
            if($rs && !$rs->EOF)
                print $rs->GetMenu2("txt_tipo_doc", $datosrad["radi_tipo"], "", false,"","class='select' id='txt_tipo_doc' style='display:none;' onchange='modificar_opcion(\"tipo_doc\"); setTimeout(\"regresar()\", 1000);'" );
            if ($nivel_seguridad_documento == 7 and $datosrad["radi_tipo"]!=2 and $_SESSION["tipo_usuario"]==1)
                echo '<script type="text/javascript">modificar_opcion_mostrar("tipo_doc", 1);</script>';
            else
                echo '<script type="text/javascript">modificar_opcion_mostrar("tipo_doc", 0);</script>';
?>

        </td>
    </tr>
    <tr>
        <?php if(trim($datosrad["radi_referencia"]) == "" ) $numCol = 3; else $numCol = 1;?>
	<td class="titulos2" align="right">Asunto:&nbsp;&nbsp; </td>
	<td class='listado2' colspan="<?=$numCol?>"><?=$datosrad["radi_asunto"] ?> </td>
        <?php if(trim($datosrad["radi_referencia"]) != "" ) { ?>
        <td class="titulos2" align="right"><?=$descReferencia?>:&nbsp;&nbsp;</td>
            <td class='listado2' align="left"> 
             <?php
           if ($nivel_seguridad_documento >= 3) {
               $text_refe=$datosrad["radi_referencia"];
               $verrad=$datosrad["radi_nume_radi"];
               //selecciono el documento de referencia cuando es de una respuesta
               //es decir tiene un radi_nume_deri
               if (trim($datosrad["radi_padre"])!=''){//si tiene
                    $radRefe=ObtenerDatosRadicado($datosrad["radi_padre"],$db);
                    $mostrar=1;
               } elseif (trim($datosrad["radi_nume_asoc"])!="") {//si no tiene tomo la asociacion
                    $radRefe=ObtenerDatosRadicado($datosrad["radi_nume_asoc"],$db);
               }

                if (isset ($radRefe)) {
                    $verrad_refe=$radRefe["radi_nume_radi"];//documento de referencia

                    if (file_exists("$ruta_raiz/bodega".$radRefe["radi_path"]) or $radRefe["arch_codi"]>0 or $radRefe["radi_imagen"]!=''){//si existe eel archivo
                        $ventana = "documento_online.php?verrad=$verrad_refe";
                        $nivel_seguridad_refe = obtener_nivel_seguridad_documento($db, $verrad_refe);
                        if ($nivel_seguridad_refe >= 2)
                            $graficapath= "&nbsp;<img src='$ruta_raiz/imagenes/zoom_in.png' width='15' height='15' alt='Vista Previa' border='0'
                                            title='Ver en l&iacute;nea Documento de Referencia' onClick='ventanaNueva(\"$ventana\");'>";
                    }else{
                        $graficapath='<hr>
                                <font color="black">
                                El documento de referencia no se puede visualizar, favor comuníquese con el Administrador del Sistema
                                </font>';
                    }
                    //echo $graficapath;
                }
           }//nivel de seguridad
           
                echo $datosrad["radi_referencia"];
                if ($mostrar==1)
                echo $graficapath;
                ?>
            </td>
        <?php } ?>
    </tr>
    <tr>
<?      // Mostrar links de descarga del documento
        $imagenv = "Documento no disponible para su descarga.";
        $imagenf = "";
        if ($nivel_seguridad_documento >= 2) {
            $imagenv = "<a href='javascript:;' onclick='vista_previa();' class='vinculos'>Ver Documento</a>";
            if ($estado==1 or $estado==7)
            $imagenv = "<a href='javascript:;' onclick='vista_previa();' class='vinculos'>Vista Previa del Documento</a>";
            if (substr($datosrad["radi_nume_temp"],-1)=="2" && trim($datosrad["radi_path"])=="" && trim($datosrad["radi_imagen"])=="")
                $imagenv = "<span class='vinculos'>Documento Digitalizado no disponible.</span>";

            if (trim($datosrad["fecha_firma"])!="" or trim($datosrad["usua_firma"])!="")
                $imagenf = "<a href='javascript:;' onclick='verificar_firma();' class='vinculos'>Verificar Firma</a>";
        }
?>
	
        <td class="titulos2" align="right">Documento:&nbsp;&nbsp;</td>
        <td class='listado2'><?="$imagenv&nbsp;&nbsp;&nbsp;&nbsp;$imagenf"?></td>
    	<td class="titulos2" align="right">Estado del Documento:&nbsp;&nbsp;</td>
    	<td class='listado2'><?=$datosrad["desc_estado"] ?>            
        <?php 
            //Se consulta los días en los que se respondió el documento
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
            ContarDiasRespuesta($db,$datosrad["radi_nume_radi"]);
        ?>
        </td>
        
    </tr>


<?php if (trim($datosrad["usua_firma"])!="") { ?>
    <tr>
        <td class="titulos2" align="right">Informaci&oacute;n de Firma:&nbsp;&nbsp;</td>
        <td class="listado2" colspan="3">
<?
            $tmp = $datosrad["usua_firma"];
            $tmp = preg_replace(':<tr><th.*?tr>:is', '', $tmp);
            $tmp = str_replace("<table>", "<table width='100%'>", $tmp);
            echo $tmp;
?>
        </td>
    </tr>
<? } ?>
    <tr>
	<td class="titulos2" align="right">De:&nbsp;&nbsp;</td>
	<td class='listado2' colspan="3"><?=lista_general_lista_usuarios($datosrad["radi_nume_temp"], $datosrad["usua_rem"], $datosrad["estado"], 1, $db,$datosrad["radi_tipo"])?></td>
    </tr>
    <tr>
	<td class="titulos2" align="right">Para:&nbsp;&nbsp;</td>
	<td class='listado2' colspan="3"> <?=lista_general_lista_usuarios($datosrad["radi_nume_temp"], $datosrad["usua_dest"], $datosrad["estado"], 2, $db,$datosrad["radi_tipo"])?></td>
    </tr>
<?php
    $lista_cca = lista_general_lista_usuarios($datosrad["radi_nume_temp"], $datosrad["cca"], $datosrad["estado"], 3, $db,$datosrad["radi_tipo"]);
    if(trim($lista_cca) != '') { ?>
        <tr>
            <td class="titulos2" align="right" valign="top">Con copia a:&nbsp;&nbsp;</td>
            <td class='listado2' colspan="3"><?=$lista_cca?></td>
        </tr>
<?php }
   
//    if ($datosrad["radi_tipo"]==2 and (0+$datosrad["usua_redirigido"])==0) $datosrad["usua_redirigido"] = str_replace ("-", "", str_replace ("--", ",", $datosrad["usua_dest"]));
    if($datosrad["radi_tipo"]==2) {
        $mostrarDirigido=verificarInstitucion($datosrad["usua_dest"],$_SESSION['inst_codi'],$db);       
?>
        <tr <?php if ($mostrarDirigido==0) echo 'style="display: none"';?>>
            <td class="titulos2" align="right" valign="top">Dirigido a:&nbsp;&nbsp;</td>
            <td class='listado2' colspan="3">
                <span id="span_usua_redirigido"></span>&nbsp;
                <img src="<?=$ruta_raiz?>/imagenes/internas/pencil_add.png" onclick='modificar_opcion_mostrar("usua_redirigido",2);' id="img_usua_redirigido" align="middle" border="0" title="Modifica el usuario a quien sera dirigido el documento" alt="editar">
<?php
        $sql = "select usua_apellido || ' ' || usua_nomb || 
                case when usua_subrogado<>1 then ' (Subrogante)' else '' end || ' /' || usua_cargo 
                || ' /' || depe_nomb  as usua_nombre  , 
                usua_codi from usuario where inst_codi=".$_SESSION["inst_codi"].
               " and usua_codi>0 and usua_esta = 1 and usua_codi in (select usua_codi from permiso_usuario where id_permiso=6) order by 1";        
        $rs = $db->query($sql);
        if($rs && !$rs->EOF)
        print $rs->GetMenu2("txt_usua_redirigido", 0+$datosrad["usua_redirigido"], "0:No dirigir", false,"","class='select' id='txt_usua_redirigido' style='display:none;' onchange='modificar_opcion(\"usua_redirigido\")'" );
        if ($nivel_seguridad_documento==7 or ($nivel_seguridad_documento==6 and ($datosrad["estado"]==9 or $datosrad["estado"]==10)))
            echo '<script type="text/javascript">modificar_opcion_mostrar("usua_redirigido", 1);</script>';
        else
            echo '<script type="text/javascript">modificar_opcion_mostrar("usua_redirigido", 0);</script>';
        echo "</td></tr>";
    }
    
if ($_SESSION["tipo_usuario"]==1) { // Si no es ciudadano

?>
    <tr>
        <td class="titulos2" align="right">Nivel de Seguridad:&nbsp;&nbsp; </td>
        <td class='listado2' colspan="1">
            <span id="span_nivel_seguridad"></span>&nbsp;
            <img src="<?=$ruta_raiz?>/imagenes/internas/pencil_add.png" onclick='modificar_opcion_mostrar("nivel_seguridad",2);' id="img_nivel_seguridad" align="middle" border="0" title="Modifica el nivel de seguridad del documento" alt="editar">
            <select name="txt_nivel_seguridad" class="select"  id="txt_nivel_seguridad" style="display:none;" onchange='modificar_opcion("nivel_seguridad")'>
                <option value="0" <?if($datosrad["seguridad"]==0) echo "selected"?>>P&uacute;blico</option>
                <option value="1" <?if($datosrad["seguridad"]==1) echo "selected"?>>Confidencial</option>
            </select>
<?
            if ($nivel_seguridad_documento >= 6)
                echo '<script type="text/javascript">modificar_opcion_mostrar("nivel_seguridad", 1);</script>';
            else
                echo '<script type="text/javascript">modificar_opcion_mostrar("nivel_seguridad", 0);</script>';
?>
	</td>
<?
// Si tiene permiso básicos no se muestra mas información
if ($nivel_seguridad_documento <= 1 ) die('<td class="listado2" colspan="2"></td></tr></table>');
?>
	<td class="titulos2" align="right"> <?=$descTRDpl?>:&nbsp;&nbsp; </td>
	<td class="listado2" colspan="1">
<?
            echo MostrarTRD($verrad, $usr_actual["depe_codi"], $db);
            if($nivel_seguridad_documento > 3)
                echo "&nbsp;&nbsp;<img src='$ruta_raiz/imagenes/internas/pencil_add.png' id='img_trd' border='0' title='Incluir el documento en una $descTRD' alt='editar' onClick='CambiarTRD();'>";
?>
	</td>
    </tr>
    <tr>
        <td class="titulos2" align="right">Categor&iacute;a:&nbsp;&nbsp; </td>
        <td class="listado2">
            <span id="span_categoria"></span>&nbsp;
            <img src="<?=$ruta_raiz?>/imagenes/internas/pencil_add.png" onclick='modificar_opcion_mostrar("categoria",2);' id="img_categoria" align="middle" border="0" title="Modifica la categor&iacute;a del documento" alt="editar">
<?php
            $rs = $db->query("select cat_descr, cat_codi from categoria order by cat_descr");
            if($rs && !$rs->EOF)
                print $rs->GetMenu2("txt_categoria", $datosrad["cat_codi"], "", false,"","class='select' id='txt_categoria' style='display:none;' onchange='modificar_opcion(\"categoria\")'" );
            if ($nivel_seguridad_documento==7 or ($nivel_seguridad_documento==6 and $datosrad["estado"]==9))
                echo '<script type="text/javascript">modificar_opcion_mostrar("categoria", 1);</script>';
            else
                echo '<script type="text/javascript">modificar_opcion_mostrar("categoria", 0);</script>';
?>
        </td>
        <td class="titulos2" align="right">Tipificaci&oacute;n:&nbsp;&nbsp; </td>
        <td class="listado2">
            <span id="span_tipificacion"></span>&nbsp;
            <img src="<?=$ruta_raiz?>/imagenes/internas/pencil_add.png" onclick='modificar_opcion_mostrar("tipificacion",2);' id="img_tipificacion" align="middle" border="0" title="Modifica la tipificaci&oacute;n del documento" alt="editar">
<?php
            $rs = $db->query("select cod_descripcion, cod_codi from codificacion where inst_codi in (0,".$_SESSION["inst_codi"].") order by cod_descripcion");
            if($rs && !$rs->EOF)
                print $rs->GetMenu2("txt_tipificacion", $datosrad["cod_codi"], "", false,"","class='select' id='txt_tipificacion' style='display:none;' onchange='modificar_opcion(\"tipificacion\")'" );
            if ($nivel_seguridad_documento==7 or ($nivel_seguridad_documento==6 and $datosrad["estado"]==9))
                echo '<script type="text/javascript">modificar_opcion_mostrar("tipificacion", 1);</script>';
            else
                echo '<script type="text/javascript">modificar_opcion_mostrar("tipificacion", 0);</script>';
?>
        </td>
    </tr>
<?

    if (trim($datosrad["radi_resumen"])!="" or $nivel_seguridad_documento>=6 or ($datosrad["estado"]==6 and $datosrad["usua_actu"]==$_SESSION["usua_codi"])) {
?>
    <tr>
        <td class="titulos2" align="right"> Notas:&nbsp;&nbsp;</td>
        <td class="listado2" colspan="3" valign="top">
            <span id="span_radi_resumen"></span>&nbsp;
            <img src="<?=$ruta_raiz?>/imagenes/internas/pencil_add.png" onclick='modificar_opcion_mostrar("radi_resumen",2);' id="img_radi_resumen" align="middle" border="0" title="Modifica el resumen del documento" alt="editar">
            <textarea name="txt_radi_resumen" id="txt_radi_resumen" cols="100" class="tex_area" rows="3" onchange='this.value=this.value.substring(0,950)'><?=trim($datosrad["radi_resumen"])?></textarea>
            &nbsp;&nbsp;
            <img src="<?=$ruta_raiz?>/imagenes/disk.png" onclick='modificar_opcion("radi_resumen");' id="img_guardar_radi_resumen" style="vertical-align: top" border="0" title="Graba el resumen del documento" alt="editar">
<?php
            if ($nivel_seguridad_documento>=6 or ($datosrad["estado"]==6 and $datosrad["usua_actu"]==$_SESSION["usua_codi"]))
                echo '<script type="text/javascript">modificar_opcion_mostrar("radi_resumen", 1);</script>';
            else
                echo '<script type="text/javascript">modificar_opcion_mostrar("radi_resumen", 0);</script>';
?>
        </td>
    </tr>
<?
    }
} // IF si es ciudadano (oculta tipificacion, carpetas virtuales y categoria)
    if (substr($datosrad["radi_nume_temp"],-1)==2) {
        $sql = "select text_texto from radi_texto where text_codi=".$datosrad["radi_codi_texto"];
        $rs = $db->query($sql);
    ?>
    <tr>
	<td class="titulos2" align="right"> Resumen:&nbsp;&nbsp;</td>
	<td class="listado2" colspan="3"><?= stripcslashes($rs->fields["TEXT_TEXTO"])?> </td>
    </tr>    
    <? } 
    // si es telefono movil, muestre el texto del documento
    if (substr($datosrad["radi_nume_temp"],-1)==0 and validar_telefono_movil() and $nivel_seguridad_documento>=3) {
        $sql = "select text_texto from radi_texto where text_codi=".$datosrad["radi_codi_texto"];
        $rs = $db->query($sql);
    ?>
    <tr>
        <td class="titulos2" align="right" valign="top"> Texto del Documento:&nbsp;&nbsp;</td>
	<td class="listado2" colspan="3"><?= stripcslashes($rs->fields["TEXT_TEXTO"])?> </td>
    </tr>
    <? }
    if(trim($datosrad["radi_desc_anexos"]) != '') { ?>
    <tr>
	<td class="titulos2" align="right"> Descripci&oacute;n de anexos:&nbsp;&nbsp;</td>
	<td class='listado2' colspan="3"><?=$datosrad["radi_desc_anexos"]?> </td>
    </tr>
    <?php } ?>
    <tr>
	<td class="titulos2" align="right"> Metadatos:&nbsp;&nbsp;</td>
	<td class="listado2" colspan="3">        
        <?
            echo MostrarMetadato($verrad, $usr_actual["depe_codi"], $db);
            if($nivel_seguridad_documento > 3)
                echo "&nbsp;&nbsp;<img src='$ruta_raiz/imagenes/internas/pencil_add.png' id='img_trd' border='0' title='Agregar Metadatos' alt='editar' onClick='DefinirMetadato();'>";
        ?>        
    </tr>
</table>

<?php

// Funciones de la página.
function lista_general_lista_usuarios($radicado, $usuario, $estado, $tipo, $db,$tipoDoc)
{
    $cadena = "";
    if ($estado==1 or $estado==7 or $estado==8) {//borradores o eliminados        
        foreach (explode('-',$usuario) as $usua_codi) {
	    if (trim($usua_codi!="")) {
                $usr = ObtenerDatosUsuario($usua_codi,$db);
                $cargo=$usr["cargo"];
                if($tipo==2) { //$tipo="Para"  y $usr["tipo_usuario"]="func." y $tipodoc=oficio
                    $opc_impr = ObtenerDatosOpcImpresion($radicado, $db);
                    if (trim($opc_impr["CARGO_CABECERA"]) != "")
                        $cargo = $opc_impr["CARGO_CABECERA"];
                    else if (trim($usr["cargo_cabecera"])!="")
                        $cargo=$usr["cargo_cabecera"];                        
                }
                
                if ($usr["tipo_usuario"]==1)
                    $cadena .= "<i>(Serv.)&nbsp;</i>";
                else
                    $cadena .= "<i>(Ciu.)&nbsp;</i>";
                
                $color = "#F7BE81";
                if ($usr["usua_estado"]==0)                    
                    $cadena .= "<span style='background-color: $color'>".$usr["abr_titulo"]." ".$usr["nombre"].", ".$cargo.", ".$usr["institucion"]."</span> (Inactivo)<br/>";
                else
	        $cadena .= $usr["abr_titulo"]." ".$usr["nombre"].", ".$cargo.", ".$usr["institucion"]."<br/>";                
	    }
        }
    } else {
	$sql = "select usua_nombre, usua_apellido, usua_abr_titulo, usua_cargo, usua_institucion, usua_area, inst_codi
		from usuarios_radicado where radi_nume_radi=$radicado and radi_usua_tipo=$tipo";        
	$rs=$db->conn->query($sql);
//        $cargo = "";
//        if($tipo==2) { //$tipo="Para"  y $usr["tipo_usuario"]="func." y $tipodoc=oficio
//            $opc_impr = ObtenerDatosOpcImpresion($radicado, $db);
//            if (trim($opc_impr["CARGO_CABECERA"]) != "")
//                $cargo = $opc_impr["CARGO_CABECERA"];
//        }
        
    	while($rs && !$rs->EOF)
    	{
//            if (trim($cargo)== "")
            $cargo = $rs->fields["USUA_CARGO"];
            if (trim($rs->fields["USUA_AREA"])=='' or trim($rs->fields["INST_CODI"])=='0' or trim($rs->fields["INST_CODI"])=='1')
                $cadena .= "<i>(Ciu.)&nbsp;</i>"; else $cadena .= "<i>(Serv.)&nbsp;</i>";
            $cadena .= $rs->fields["USUA_ABR_TITULO"]." ".$rs->fields["USUA_NOMBRE"]." ".$rs->fields["USUA_APELLIDO"];
            $cadena .= ", $cargo, ".$rs->fields["USUA_INSTITUCION"]."<br/>";
            $rs->MoveNext();
	}
    }

    return $cadena;
}

function MostrarTRD($radicado, $dependencia, $db)
{
    global $descTRD;
        $sql = "select cv.trd_codi, d.dep_sigla, d.depe_codi
                     from trd_radicado cv left outer join dependencia d on d.depe_codi=cv.depe_codi
                     where cv.radi_nume_radi=$radicado order by d.depe_nomb";
        $rs = $db->conn->query($sql);
        $codexp = 0;
        $nombre_completo = "Este documento no ha sido incluido en ninguna $descTRD.";
        $br = "";
        while ($rs && !$rs->EOF) {
            $nombre_trd = ObtenerNombreCompletoTRD($rs->fields["TRD_CODI"],$db);
            echo $br.$rs->fields["DEP_SIGLA"].": $nombre_trd";
            $br = "<br>";
            if ($rs->fields["DEPE_CODI"] == $dependencia) {
                $codexp = $rs->fields["TRD_CODI"];
                $nombre_completo = $nombre_trd;
            }
            $rs->MoveNext();
        }
        if ($codexp == 0) {
            $rs = $db->conn->query("select dep_sigla from dependencia where depe_codi=$dependencia");
            echo $br.$rs->fields["DEP_SIGLA"].": $nombre_completo";
        }
}


function MostrarMetadato($radicado, $dependencia, $db)
{
        $rs = ConsultarMetadatosRadiDoc($db, $radicado);
        $met_codi = 0;
        $metadato_texto = "Este documento no tiene metadato definido.";
        $br = "";
        while ($rs && !$rs->EOF) {           
            echo $br.$rs->fields["DEP_SIGLA"].": ". $rs->fields["METADATO_TEXTO"];
            $br = "<br>";
            if ($rs->fields["DEPE_CODI"] == $dependencia) {
                $met_codi = $rs->fields["MET_CODI"];
                $metadato_texto = $rs->fields["METADATO_TEXTO"];
            }
            $rs->MoveNext();
        }
        if ($met_codi == 0) {
            $rs = $db->conn->query("select dep_sigla from dependencia where depe_codi=$dependencia");
            echo $br.$rs->fields["DEP_SIGLA"].": $metadato_texto";
        }
}

//Se consulta los días en los que se respondió el documento
function ContarDiasRespuesta($db, $radi_nume_radi){
    $sql = "select coalesce(extract(days from ((select min(r_deri.radi_fech_ofic) from radicado as r_deri
    where r_deri.radi_nume_deri = r.radi_nume_radi and r_deri.esta_codi in (0,6)) - r.radi_fech_radi::date)),-1) as cuenta_dias
    from radicado r where r.radi_nume_radi ='$radi_nume_radi'";    
    $rs=$db->conn->query($sql);
    $dias = $rs->fields["CUENTA_DIAS"];
    if($dias > -1)
        echo "<b>Días de respuesta: </b>". $dias;
   //echo $sql;
}

?>

