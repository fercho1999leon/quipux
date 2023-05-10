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

**************************************************************************************
** Graba las solicitudes de respaldos de la documentacion de los usuarios           **
**                                                                                  **
** Modificado por:                                                                **
**      David   Gamboa                                                          **
*************************************************************************************/


  $ruta_raiz = "../..";
  session_start();

  $txt_tipo_lista = trim(limpiar_numero($_GET["txt_tipo_lista"])); //1: Muestra listado del usuario actual, 2: Muestra listado por autorizar
  
  
  $paginador = new ADODB_Pager_Ajax($ruta_raiz, "div_buscar_solicitudes", "respaldo_buscar.php", "cmb_estado,txt_nombre,txt_fecha_inicio_sol,txt_fecha_fin_sol,txt_sol,txt_tipo_lista,cmb_institucion","");

  $fecha_inicio_sol = date("Y-m-d", strtotime(date("Y-m-d")." - 6 month"));
  $fecha_fin_sol = date("Y-m-d");

  //Se consulta usuario que autoriza
  $var_aprueba = 0;


  //Se establece titulo seǵun el tipo de lista
  switch ($txt_tipo_lista) {
    case 1:
        $txt_titulo = "Mis Solicitudes de Respaldo";
        break;
    
      default:
        break;
}
?>

<!--<link rel="stylesheet" type="text/css" href="<?=$ruta_raiz?>/js/spiffyCal/spiffyCal_v2_1.css">
<script type="text/javascript" src="<?=$ruta_raiz?>/js/spiffyCal/spiffyCal_v2_1.js"></script>-->

<html>
<?echo html_head(); ?>

<script type="text/javascript" src="<?=$ruta_raiz?>/js/funciones.js"></script>
<script language="JavaScript" type="text/JavaScript">
    function buscar_solicitudes() {
      
      document.getElementById('div_mensaje').style.display='none';      
      if(valida_datos() == 1)
      {       
        txt_var_apru = document.getElementById('txt_var_apru').value;
        txt_tipo_lista= document.getElementById('txt_tipo_lista').value;
        cmb_estado_sol = document.getElementById('cmb_estado').value;
        txt_nombre = document.getElementById('txt_nombre').value;
        txt_fecha_inicio_sol = document.getElementById('txt_fecha_inicio_sol').value;
        txt_fecha_fin_sol = document.getElementById('txt_fecha_fin_sol').value;
        txt_sol = document.getElementById('txt_sol').value;
        cmb_institucion = document.getElementById('cmb_institucion').value;
        codigo_usuario = document.getElementById('codigo_usuario').value;
        codigo_dep = document.getElementById('codigo_dep').value;
        parametros = 'cmb_estado=' + cmb_estado_sol
                    +'&txt_nombre=' + txt_nombre
                    +'&txt_fecha_inicio_sol=' + txt_fecha_inicio_sol
                    +'&txt_fecha_fin_sol=' + txt_fecha_fin_sol
                    +'&txt_sol=' + txt_sol
                    +'&txt_tipo_lista=' + txt_tipo_lista
                    +'&cmb_institucion=' + cmb_institucion
                    +'&codigo_usuario='+codigo_usuario
                    +'&codigo_dep='+codigo_dep; 
        
       //paginador_reload_div('');
        nuevoAjax('div_buscar_solicitudes', 'GET', '<?=$ruta_raiz?>/backup/respaldo_buscar_tab.php', parametros, '');
        document.getElementById('div_buscar_solicitudes').style.display='';
      }
      else{
          document.getElementById('div_buscar_solicitudes').style.display='none';
          document.getElementById('div_mensaje').style.display='';
      }
    }
  
    function valida_datos(){

        valido = 1;
        mensaje = "";
        if(esRangoFechaValido(document.frmCrear.txt_fecha_inicio_sol.value, document.frmCrear.txt_fecha_fin_sol.value)==0){
            valido = 0;
            mensaje += "La Fecha Inicio debe ser menor o igual que la Fecha Fin. \n";
        }
        if (isNaN(document.getElementById('txt_sol').value)){
            valido = 0;
            mensaje += "En el campo 'Nº solicitud' se debe ingresar sólo números y sin espacios. \n";
        }
        
        if(valido == 0)
            alert(mensaje);
   
        return valido;
    }

    function seleccionar_solicitud(codigo, lista) {
        tipo_lista = document.getElementById("txt_tipo_lista").value;
        
        if(tipo_lista == 1 || tipo_lista == 2 || tipo_lista == 6 || tipo_lista == 9 || tipo_lista == 11){
            var_envio='<?=$ruta_raiz?>/backup/respaldo_informacion.php?txt_resp_soli_codi='+codigo+'&txt_tipo_lista='+lista;
            window.location=var_envio;
        }
        else{
            var_envio='<?=$ruta_raiz?>/backup/respaldo_informacion.php?txt_resp_soli_codi='+codigo+'&tipo_ventana=popup&txt_tipo_lista='+lista;
            window.open(var_envio,codigo,"height=615,width=900,scrollbars=yes");
        }
    }


  function descargar_respaldo(codigo, solicitud) {
        windowprops = "top=50,left=50,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=700,height=500";
        url = '<?=$ruta_raiz?>/backup/backup_usuarios_descargar_archivos.php?codigo_backup=' + codigo +'&resp_soli_codi='+solicitud;
        ventana = window.open(url , "descargar_respaldo_" + codigo, windowprops);
        ventana.focus();
    }

    

    
            
    function ver_documento_asociado(numdoc, txtdoc){                     
        var_envio='<?=$ruta_raiz?>/verradicado.php?verrad='+numdoc+'&textrad='+txtdoc+'&menu_ver_tmp=3&tipo_ventana=popup';
        window.open(var_envio,numdoc,"height=450,width=750,scrollbars=yes");
    }

</script>
<body onLoad="" >
  <div id="spiffycalendar" class="text"></div>
  <center>
    <form name="formulario" action="" method="post">
        <input type="hidden" name="txt_var_apru" id="txt_var_apru" value="<?php echo $var_aprueba; ?>" size="20">
        <input type="hidden" name="txt_tipo_lista" id="txt_tipo_lista" value="1" size="20">
        <input type="hidden" name="txt_accion" id="txt_accion" size="20" maxlength="10" value="0">
        <input type="hidden" name="txt_resp_soli_codi" id="txt_resp_soli_codi" size="20" value="0">
        <input type="hidden" name="txt_codigo_respaldo" id="txt_codigo_respaldo" size="20" value="0">
        <input type="hidden" name="codigo_usuario" id="codigo_usuario" size="20" value="<?=$usr_codigo?>"/>
        <input type="hidden" name="codigo_dep" id="codigo_dep" size="20" value="<?=$usr_depe?>"/>
        <table width="100%" class="borde_tab" border="0">
        <tr>
            <td class="listado2">
                <?php echo graficarTabsMenuUsr($usr_codigo,$tiene_subrogacion,$usr_perfil,$usr_depe,3);?>
            </td>
        </tr>
    </table>
        
        <table width="100%" align="center" class=borde_tab border="0">
            <? if($_SESSION["usua_perm_backup"]==1 and ($txt_tipo_lista == 9 or $txt_tipo_lista == 10 or $txt_tipo_lista == 11)) { ?>
                <tr>
                    <td width="10%" class="titulos2">Instituci&oacute;n:</td>
                    <td width="40%" class="listado2">
                        <?
                        $sql="select inst_nombre, inst_codi from institucion where inst_codi>0 order by 1 asc";
                        $rs=$db->conn->query($sql);
                        if($txt_tipo_lista == 11)
                            $cmb_institucion = 0;
                        else
                            $cmb_institucion = $_SESSION["inst_codi"];
                        if($rs) print $rs->GetMenu2("cmb_institucion", $cmb_institucion, "0:&lt;&lt; Todas las instituciones &gt;&gt;", false,"","class='select' id='cmb_institucion' style='width: 400px;'" );
                        ?>
                    </td>
                     <td width="15%" class="titulos5" valign="middle"></td>
                </tr>
            <? } else echo "<input type=hidden id='cmb_institucion' name='cmb_institucion' value='0'>"; ?>
            <tr>
            <td width="10%" class="titulos2">Fecha Inicio: <br>(yyyy/mm/dd)</td>
            <td width="40%" class="listado2">
            <?php    $fecha_desde = date('Y-m-d');
                    echo dibujar_calendario("txt_fecha_inicio_sol", $fecha_desde, $ruta_raiz, ''); ?>
               
                <br>
            </td>
             <td rowspan="5" width="15%" class="titulos5" valign="middle">
                    <center><input type='button' value='Buscar' name='btn_buscar' class='botones' onClick='buscar_solicitudes()'></center>
             </td>
            </tr>
            <tr>
            <td width="10%" class="titulos2">Fecha Fin: <br>(yyyy/mm/dd)</td>
            <td width="40%" class="listado2">
                <?php    $fecha_desde = date('Y-m-d');
                    echo dibujar_calendario("txt_fecha_fin_sol", $fecha_desde, $ruta_raiz, ''); ?>

                <br>
            </td>
            </tr>
            <tr>
                <td width="10%" class="titulos2"> Nº Solicitud: </td>
                <td width="40%" class="listado2">
                    <input name="txt_sol" id="txt_sol" type="text" size="17" class="tex_area" value="">
                </td>
            </tr>
            <?if ($txt_tipo_lista <> 2){ ?>
            <tr>
               <td width="10%" class="titulos2"> Estado Solicitud: </td>
                <td width="40%" class="listado2">
                    <?
                        $condicion = "";
                        if($txt_tipo_lista == 3)
                            $condicion=" and est_codi<>1";

                        $sql="select est_nombre_estado, est_codi from respaldo_estado where est_tipo=1 and est_estado = 1 $condicion order by 1 asc";
                        $rs=$db->conn->query($sql);
                        if($rs) print $rs->GetMenu2("cmb_estado", $cmb_estado, "0:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;&lt; Todos &gt;&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", false,"","class='select' id='cmb_estado'" );
                    ?>
                </td>
            </tr>
            <?}
            else
                echo "<input type='hidden' id='cmb_estado' name='cmb_estado' value='0'>";
            ?>
            <?if ($txt_tipo_lista != 1){?>
              <tr>
                    <td width="10%" class="titulos2"> Nombre o C.I.: </td>
                    <td width="40%" class="listado2">                        
                        <input name="txt_nombre" id="txt_nombre" type="text" size="40" class="tex_area" value="<?=$usr_cedula?>" readonly/>
                    </td>
                </tr>
            <?} else echo "<input type='hidden' id='txt_nombre' name='txt_nombre' value=''>";?>
        </table>
        <div id='div_seleccionar_solicitud'></div>
        <?if ($txt_tipo_lista == 2 and $var_aprueba == 1){?>
            <br>
            <div id='div_seleccionar_masiva'></div>
            <input type="button" name="btn_aprobar" value="Aprobar" class="botones"onClick="aprobar_solicitud(3);">
            <input type="button" name="btn_rechazar" value="Rechazar" class="botones"onClick="aprobar_solicitud(4);">
        <?}?>
        <?if ($txt_tipo_lista == 11){?>
            <br>
            <input type="button" name="btn_calendario" value="Calendarizar" class="botones"onClick="calendarizar(5);">
        <?}?>
        <div id='div_buscar_solicitudes'></div>
        
        <div id='div_mensaje'><? echo "<br> No se encontraron los datos buscados. <br>"; ?></div>
        <br>
        
      </form>
    </center>
<!--  </body>
</html>-->