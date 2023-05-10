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

if($_SESSION["usua_admin_sistema"]!=1)
    if($_SESSION["usua_perm_ciudadano"]!=1)
        die(html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina."));

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();
function javaMenu(){ 
   
$html = '<script> function MM_swapImgRestore() {     
      var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
    }

    function MM_preloadImages() { 
      var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
        var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
        if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
    }

    function MM_findObj(n, d) { 
      var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
        d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
      if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
      for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
      if(!x && d.getElementById) x=d.getElementById(n); return x;
    }

    function MM_swapImage() { 
      var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
       if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}       
    }
    </script>
    ';
    return $html;
 }
function graficarMenu($usr_codigo=0,$tiene_subrogacion=0,$usr_perfil=0,$usr_depe=0){ 
    $ruta_raiz = "../..";
    echo javaMenu();
    ?>
<center>
<table whidth="20%" border="0" cellpadding="0" cellspacing="0" align="left">
      <tr>
        
        <td valign="bottom" align="right" id="btn_img">
            <img name="principal_r4_c3" src="<?=$ruta_raiz?>/imagenes/internas/principal_r4_c3.gif" width="25" height="51" border="0" alt="">
        </td>
        <td valign="bottom" align="left" >
              <a href="javascript:;" onclick="window.location='<?=$ruta_raiz?>/Administracion/usuarios/mnuUsuarios.php';" 
                  onMouseOut="MM_swapImgRestore()"
               onMouseOver="MM_swapImage('Image1','','<?=$ruta_raiz?>/imagenes/internas/overRegresar.gif',1)"
                 target='mainFrame'>
               <img name="Image1" src='<?=$ruta_raiz?>/imagenes/internas/regresar.gif' alt='' border=0 /></a>
        </td>
        <td valign="bottom" align="left" >
              <a href="javascript:;" onclick="window.location='<?=$ruta_raiz?>/Administracion/usuarios/cuerpoUsuario.php?accion=2';" 
                 onMouseOut="MM_swapImgRestore()"
               onMouseOver="MM_swapImage('Image2','','<?=$ruta_raiz?>/imagenes/imgusuarios/icono-quipux-consultar-usuario-01-over.png',1)"
                 target='mainFrame'>
               <img name="Image2" src='<?=$ruta_raiz?>/imagenes/imgusuarios/icono-quipux-consultar-usuario-01.png' alt='' border=0 /></a>
        </td>
        <td valign="bottom" align="left" >
              <a href='javascript:;' onclick="window.location='<?=$ruta_raiz?>/Administracion/usuarios/adm_usuario.php?accion=1';" 
                 onMouseOut="MM_swapImgRestore()"
               onMouseOver="MM_swapImage('Image3','','<?=$ruta_raiz?>/imagenes/imgusuarios/icono-quipux-crear-usuario-over.png',1)"
                 target='mainFrame'>
               <img name='Image3' src='<?=$ruta_raiz?>/imagenes/imgusuarios/icono-quipux-crear-usuario.png' alt='' border=0 /></a>
        </td>
        <?php
        /*
        <td valign="bottom" align="left" >
              <a href='javascript:;' onclick="window.location='<?=$ruta_raiz?>/Administracion/subrogacion/buscar_usuario_nuevo_subr.php?accion=2';" target='mainFrame'>
              <img src='<?=$ruta_raiz?>/imagenes/imgusuarios/icono-quipux-crear-subrogacion.png' alt='' border=0 /></a>
        </td>
        <td valign="bottom" align="left" >
              <a href='javascript:;' onclick="window.location='<?=$ruta_raiz?>/Administracion/subrogacion/buscar_usuario_nuevo_subr_des.php?accion=3';" target='mainFrame'>
              <img src='<?=$ruta_raiz?>/imagenes/imgusuarios/icono-quipux-desactivar-subrogacion.png' alt='' border=0 /></a>
        </td>*/?>
        <td valign="bottom" align="left" >
            <img name="principal_r4_c4" src="<?=$ruta_raiz?>/imagenes/internas/principal_r4_c4.gif" width="25" height="51" border="0" alt="">
        </td>
        
      </tr></table></center>
        <?php } 
function graficarTabsMenuUsr($usr_codigo=0,$tiene_subrogacion=0,$usr_perfil=0,$usr_depe=0,$menu=''){
        $ruta_raiz = "../..";        
        $menu1='';$menu2='';$menu3='';$menu4='';        
        switch ($menu)  {
            case 1:
                $menu1='_sel';
                break;
            case 2:
                $menu2 = '_sel';
            break;
            case 3:
                $menu3 = '_sel';
            break;
            case 4:
                $menu4 = '_sel';
            break;
            case 5:
                $menu5 = '_sel';
            break;
        }      
        
    ?>
        
                <a href="javascript:;" onclick="mostrar_div_usr('div_informacion_usr');" 
                   target='mainFrame'>
                <img src='<?=$ruta_raiz?>/imagenes/imgusuarios/tab-info-usuario<?=$menu1?>.png' alt='' border=0 width="110" height="25"/>
                </a>
                 
                <a href="javascript:;" onclick="mostrar_div_usr('div_permisos_desp');" target='mainFrame'>
                <img src='<?=$ruta_raiz?>/imagenes/imgusuarios/tab-permisos<?=$menu2?>.png' alt='' border=0 width="110" height="25"/>
                </a>
                 
      <?php
      
      if ($usr_codigo>0){
          ?>
                 
                <a href="javascript:;" onclick="buscar_solicitudes();mostrar_div_usr('div_backup');" target='mainFrame'>
                <img src='<?=$ruta_raiz?>/imagenes/imgusuarios/tab-solicitudes<?=$menu3?>.png' alt='' border=0 width="110" height="25"/>
                </a>
                 
      <?php }
      ?>      
            <a href="javascript:;" onclick="mostrar_div_usr('div_recorrido');" target='mainFrame'>
            <img src='<?=$ruta_raiz?>/imagenes/imgusuarios/tab-modificaciones<?=$menu5?>.png' alt='' border=0 width="110" height="25"/>
            </a>
            
          <?php 
                //solo para jefes
                if ($usr_codigo>0){
                    if ($tiene_subrogacion==0 and $usr_perfil==1){//para jefes
                    ?>
            
                  <a href='javascript:;' onclick="window.location='<?=$ruta_raiz?>/Administracion/subrogacion/buscar_usuario_nuevo_subr.php?accion=2&usr_subrogado=<?=$usr_codigo?>&depe_codi_get=<?=$usr_depe?>&cargo_tipo_get=<?=$usr_perfil?>'" target='mainFrame'>
                  <img src='<?=$ruta_raiz?>/imagenes/imgusuarios/tab-subrogar<?=$menu4?>.png' alt='' border=0 width="110" height="25"/>
                  </a>
                    <?php
                    }
                }//usuario ?>
         
 <?php  
}
function graficarTabsCiud(){
 $ruta_raiz="../..";
if($_SESSION["usua_admin_sistema"]!=1)
    if($_SESSION["usua_perm_ciudadano"]!=1)
        die(html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina."));
if (!isset($cerrar)) $cerrar="No";
echo javaMenu();
?>
<center>
<table whidth="20%" border="0" cellpadding="0" cellspacing="0" align="left">
      <tr>
        
        <td valign="bottom" align="right" id="btn_img">
            <img name="principal_r4_c3" src="<?=$ruta_raiz?>/imagenes/internas/principal_r4_c3.gif" width="25" height="51" border="0" alt="">
        </td>
        <td valign="bottom" align="left" >
              <a href="javascript:;" onclick="window.location='<?=$ruta_raiz?>/Administracion/formAdministracion.php';" 
                 onMouseOut="MM_swapImgRestore()"
               onMouseOver="MM_swapImage('Image11','','<?=$ruta_raiz?>/imagenes/internas/overRegresar.gif',1)"
                 target='mainFrame'>
               <img name="Image11" src='<?=$ruta_raiz?>/imagenes/internas/regresar.gif' alt='' border=0 /></a>
        </td>
        <?php         
        
        if($_SESSION["perm_validar_ciudadano"]==1 || $_SESSION["usua_codi"]==0) { ?>
        <td valign="bottom" align="left" >
              <a href="javascript:;" onclick="window.location='<?=$ruta_raiz?>/Administracion/ciudadanos/adm_usuario_ext_combinar.php';" 
                 onMouseOut="MM_swapImgRestore()"
               onMouseOver="MM_swapImage('Image12','','<?=$ruta_raiz?>/imagenes/imgusuarios/ciudadanos/combinar_ciudadano-over.png',1)"
                 target='mainFrame'>
               <img name="Image12" src='<?=$ruta_raiz?>/imagenes/imgusuarios/ciudadanos/combinar_ciudadano.png' alt='' border=0 /></a>
        </td>
        <?php } ?>
        <td valign="bottom" align="left" >
              <a href="javascript:;" onclick="window.location='<?=$ruta_raiz?>/Administracion/ciudadanos/adm_usuario_ext.php?cerrar=$cerrar&accion=1';" 
                  onMouseOut="MM_swapImgRestore()"
               onMouseOver="MM_swapImage('Image13','','<?=$ruta_raiz?>/imagenes/imgusuarios/ciudadanos/crear_ciudadano-over.png',1)"
                 target='mainFrame'>
               <img name="Image13" src='<?=$ruta_raiz?>/imagenes/imgusuarios/ciudadanos/crear_ciudadano.png' alt='' border=0 /></a>
        </td>
        <td valign="bottom" align="left" >
              <a href="javascript:;" onclick="window.location='<?=$ruta_raiz?>/Administracion/ciudadanos/cuerpoUsuario_ext.php?cerrar=$cerrar&accion=2';" 
                 onMouseOut="MM_swapImgRestore()"
               onMouseOver="MM_swapImage('Image14','','<?=$ruta_raiz?>/imagenes/imgusuarios/ciudadanos/editar_ciudadano-over.png',1)"
                 target='mainFrame'>
              <img name="Image14" src='<?=$ruta_raiz?>/imagenes/imgusuarios/ciudadanos/editar_ciudadano.png' alt='' border=0 /></a>
        </td>
        <?php if($_SESSION["perm_validar_ciudadano"]==1 || $_SESSION["usua_codi"]==0) { ?>
        <td valign="bottom" align="left" >
              <a href="javascript:;" onclick="window.location='<?=$ruta_raiz?>/Administracion/ciudadanos/adm_ciudadano_confirmar.php'" 
                 onMouseOut="MM_swapImgRestore()"
               onMouseOver="MM_swapImage('Image15','','<?=$ruta_raiz?>/imagenes/imgusuarios/ciudadanos/confirmar_ciudadano-over.png',1)"
                 target='mainFrame'>
              <img name="Image15" src='<?=$ruta_raiz?>/imagenes/imgusuarios/ciudadanos/confirmar_ciudadano.png' alt='' border=0 /></a>
        </td>
        <td valign="bottom" align="left" >
              <a href="javascript:;" onclick="window.location='<?=$ruta_raiz?>/Administracion/ciudadanos_solicitud/cuerpoSolicitud_ext.php'" 
                 onMouseOut="MM_swapImgRestore()"
               onMouseOver="MM_swapImage('Image16','','<?=$ruta_raiz?>/imagenes/imgusuarios/ciudadanos/firma_ciudadano-over.png',1)"
                 target='mainFrame'>
              <img name="Image16" src='<?=$ruta_raiz?>/imagenes/imgusuarios/ciudadanos/firma_ciudadano.png' alt='' border=0 /></a>
        </td>
        <?php } ?>
        <td valign="bottom" align="left" >
            <img name="principal_r4_c4" src="<?=$ruta_raiz?>/imagenes/internas/principal_r4_c4.gif" width="25" height="51" border="0" alt="">
        </td>        
      </tr></table></center>


<?php
}


function graficarTabsMenuCiud($usr_codigo=0,$menu=''){
    $ruta_raiz="../..";    
    $menu1='';$menu2='';
        switch ($menu)  {
            case 1:
                $menu1='_sel';
                break;
            case 2:
                $menu2 = '_sel';
            break;
        }
?>

        <a href="javascript:;" onclick="mostrar_div_ciud('div_informacion_ext');" target='mainFrame'>
            <img src='<?=$ruta_raiz?>/imagenes/imgusuarios/tab-info-ciudadano<?=$menu1?>.png' alt='' border=0 width="110" height="25"/>
        </a>
        <a href="javascript:;" onclick="mostrar_div_ciud('div_historico_ext');" target='mainFrame'>
        <img src='<?=$ruta_raiz?>/imagenes/imgusuarios/tab-modificaciones<?=$menu2?>.png' alt='' border=0 width="110" height="25"/>
        </a>
<?php    
}
?>