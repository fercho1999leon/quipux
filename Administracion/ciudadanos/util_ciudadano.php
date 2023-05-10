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
/****************************************************************************************
*   Reestructuracion de codigo ciudadanos
*   Realizado por               Fecha (dd/mm/aaaa)
*   David Gamboa                16-04-2012
* 											
*****************************************************************************************/

class Ciudadano {
    var $db;
    var $ruta_raiz;

    var $ciu_cod;
    var $inst_cod;
    
    function Ciudadano($db) {//constructor
	$this->db = $db;
        $this->ruta_raiz = $db->rutaRaiz;
    }
    
    /**********************************************************************************
    ** Funcion consultar ciudadano en tabla temporal    
    ***********************************************************************************/
    function consultar_ciudadano_tmp ($ciu_codigo,$cedula="",$tipoUsr=0,$raiz='') {
        $sql = "select count(1) as num from ciudadano_tmp 
        where ciu_codigo=$ciu_codigo and ciu_estado = 1";
       //echo $sql;
        $rs = $this->db->conn->query($sql);
        if ($tipoUsr==2){
          if ((0+$rs->fields["NUM"])>0)
              if($raiz=='')
                    echo "<script>window.location='adm_datos_temporales.php'</script>";  
              else
                  echo "<script>window.location='$raiz/ciudadanos/adm_datos_temporales.php'</script>";  
          else
              return;
        }else{
            if ((0+$rs->fields["NUM"])>0)                    
                 echo "<script>window.location='adm_ciudadano_solconfirmar.php?ciu_codigo=$ciu_codigo&cedula=$cedula'</script>";
            else
                return;
        }
        return $tipoUsr;

    }//fin funcion consultar_ciudadano_tmp
    /***********************************************************************************
    ** Cargar los datos desde solicitud o ciudadano
    ** tipo 1 = cargar usuarios de ciudadano,2 verificar
    ** en las dos tablas solicitud firma o ciudadano
    ***********************************************************************************/
    function cargar_datos_ciudadano($ciu_codigo,$tipo){
        $solicitud=0;
        if ($tipo==2){
            $sql="select * from solicitud_firma_ciudadano where ciu_codigo =".$ciu_codigo;
            //echo $sql;
            $rs=$this->db->conn->query($sql);
            $solicitud = 1;
            if ($rs->EOF){
                $sql="select * from ciudadano where ciu_codigo=".$ciu_codigo;
                $rs=$this->db->conn->query($sql);
                $solicitud=0;
            }
        }
        else{
            $sql="select * from ciudadano where ciu_codigo=".$ciu_codigo;
            $rs=$this->db->conn->query($sql);
            $solicitud=0;
        }
        //echo $sql;
        if (!$rs->EOF){
            $reg=array(); 
            $reg['ciu_codigo']      =   $rs->fields["CIU_CODIGO"];
            $reg['ciu_cedula']      =   $rs->fields["CIU_CEDULA"];
            if (substr($reg['ciu_cedula'],0,2)==99) $reg['ciu_cedula']="";
            $reg['ciu_documento']   =   $rs->fields["CIU_DOCUMENTO"];
            $reg['ciu_nombre']      =   $rs->fields["CIU_NOMBRE"];
            $reg['ciu_apellido'] 	=   $rs->fields["CIU_APELLIDO"];
            $reg['ciu_titulo']      =   $rs->fields["CIU_TITULO"];
            $reg['ciu_abr_titulo']  =   $rs->fields["CIU_ABR_TITULO"];
            $reg['ciu_empresa']     =   $rs->fields["CIU_EMPRESA"];
            $reg['ciu_cargo']       =   $rs->fields["CIU_CARGO"];
            $reg['ciu_direccion']   =   $rs->fields["CIU_DIRECCION"];
            $reg['ciu_email']       =   $rs->fields["CIU_EMAIL"];
            $reg['ciu_telefono']    =   $rs->fields["CIU_TELEFONO"];
            $reg['ciu_ciudad']      =   $rs->fields["CIUDAD_CODI"];
            
            $reg['ciu_referencia']      =   $rs->fields["CIU_REFERENCIA"];
           //print_r($reg);
            if ($solicitud==1){//si ejecuta el select de solicitud
                $reg['sol_codigo']         =   $rs->fields["SOL_CODIGO"];
                $reg['sol_observaciones']  =   $rs->fields["SOL_OBSERVACIONES"];
                $reg['sol_firma']          =   $rs->fields["SOL_FIRMA"];
                $reg['sol_estado']         =   $rs->fields["SOL_ESTADO"];
                $reg['sol_planilla']       =   $rs->fields["SOL_PLANILLA"];
                $reg['sol_cedula']         =   $rs->fields["SOL_CEDULA"];
                $reg['sol_acuerdo']        =   $rs->fields["SOL_ACUERDO"];
            }
            return $reg;
        }else
            return 0;
        
        
    }
    /***********************************************************************************
    ** Dibujar cajas de texto
    ***********************************************************************************/
          
        function dibujar_campo ($campo, $valor,$label, $tamano, $opciones="",$tipoUsrAd=2) {
                    if ($tipoUsrAd==2){
                         if($sol_estadod == 3 || $sol_estadod == 2)
                            $estado_read = "readonly='readonly'";
                         else
                            $estado_read = "";
                    }else{
                        if ($_SESSION["usua_codi"]==0 && ($sol_estadod == 3 || $sol_estadod == 0))
                             $estado_read = "readonly='readonly'";
                        else
                            $estado_read = "";
                    }
                    
                    $cad = "<td class='titulos2' width='20%'> $label </td>
                            <td class='listado3' width='30%'>
                            <input type='text' name='$campo' id='$campo' value='".$valor."' size='55' maxlength='$tamano' $opciones class='caja_texto' $estado_read>
                            </td>";
                    echo $cad;
                    return;
        }
        function dibujar_campoprueba ($campo, $label, $tamano, $estado,$opciones="") {
                    $cad = "<td class='titulos2' width='20%'> $label </td>
                            <td class='listado3' width='30%'>
                            <input type='text' name='$campo' id='$campo' value='$estado' size='63' maxlength='$tamano' $opciones class='caja_textoSinBorde' readonly='readonly'>
                            </td>";
                    echo $cad;
                    return;
        }
        function dibujar_campoobs ($campo, $valor,$label, $tamano,$sol_estadod, $opciones="",$tipoUsrAd=2) {

                if ($tipoUsrAd==2){
                    if($sol_estadod == 3 || $sol_estadod == 2)
                        $estado_read = "readonly='readonly'";
                    else
                        $estado_read = "";
                }else{
                     if ($_SESSION["usua_codi"]==0 && ($sol_estadod == 3 || $sol_estadod == 0))
                        $estado_read = "readonly='readonly'";
                    else
                        $estado_read = "";
                }
                     

                    $cad = "<td class='titulos2' width='10%'> $label </td>
                            <td class='listado3' width='90%' colspan='3'>
                            <input type='text' name='$campo' id='$campo' value='".$valor."' size='132' maxlength='$tamano' class='caja_texto' $estado_read>
                            </td>";
                    echo $cad;
                    return;
        }
       
        /***********************************************************************************
        ** Dibujar caja upload
         * nombre = input
         * tipoExt = tipo extensiones que acepta
         * $numeroArchivos = numero de archivos que acepta
         * $funjava = funcion java
        ***********************************************************************************/        
        function fileUpload($nombre,$tipoExt,$numeroArchivos,$funjava=""){
            echo "<input type='file' id='".$nombre."' name='".$nombre."[]' class='multi' maxlength='$numeroArchivos' $funjava accept='".$tipoExt[0]."'/>";
            echo "<br><font color='blue'>Se permiten archivos con extensión ".$tipoExt[1].'</font>';
        }
        /***********************************************************************************
        ** Dibujar cajas de texto
         * nombre de la caja de texto
         * path en donde se subira el archivo                        
        ***********************************************************************************/
        function guardarArchivos($nombre,$path="",$ruta_raiz="",$ciu_codigo=""){    
            foreach ($_FILES[$nombre]['error'] as $key => $error) {
               if ($error == UPLOAD_ERR_OK) {                   
                   $archivo_final = $_FILES[$nombre]["name"];
                   $ext_p7m = substr($archivo_final[$key],-3);
                   
                   $tmp_name = $_FILES[$nombre]["tmp_name"];                  
                   $directorio_archivos=$ruta_raiz.$path;
                   $archivo_acuerdo = $ciu_codigo.'_acuerdo.pdf.p7m';
                   $tamanio = $_FILES[$nombre]['size'];
                   if ($tamanio[$key]>0){                      
                     if ($ext_p7m=='p7m'){
                        move_uploaded_file($tmp_name[$key],$directorio_archivos.$archivo_acuerdo);//Grabamos el archivo                   
                        $ok_guardar = 1;
                     }else
                         $ok_guardar = 0;
                   }
                   else
                       $ok_guardar=0;
                       
               }
            }
            return $ok_guardar;
        }
        /***********************************************************************************
        ** Comparar ciudades
         * new_value, old_value, son los id de las ciudades para comparar
        ***********************************************************************************/
        function comparar_campo_ciudad($campo, $new_value,$old_value,$label, $tamano, $opciones="") {
           
            
                
            $id_ciudad_new = $new_value;
            $sql = "select * from ciudad order by nombre";
            $rs=$this->db->conn->query($sql);
            $cad_new = "<select name='new_$campo' id='new_$campo' class='select' disabled='disabled'>";
            
                while(!$rs->EOF){
                $id_ciudad = $rs->fields['ID'];
                $nombre_ciudad = $rs->fields['NOMBRE'];
                            if ($id_ciudad_new==$id_ciudad)
                            $cad_new.="<option value='$id_ciudad' selected>$nombre_ciudad</option>";
                            else
                                $cad_new.="<option value='$id_ciudad'>$nombre_ciudad</option>";
                         $rs->MoveNext();
                }
           
                $cad_new.="</select>";
            
            $id_ciudad_old = $old_value;
            $cad_old = "<select name='old_$campo' id='old_$campo' class='select' >";
            $sql = "select * from ciudad order by nombre";
            //echo $sql;
            $rs=$this->db->conn->query($sql);
            
                while(!$rs->EOF){
                $id_ciudad = $rs->fields['ID'];
                $nombre_ciudad = $rs->fields['NOMBRE'];
                            if ($id_ciudad_old==$id_ciudad)
                            $cad_old.="<option value='$id_ciudad' selected>$nombre_ciudad</option>";
                            else
                                $cad_old.="<option value='$id_ciudad'>$nombre_ciudad</option>";
                         $rs->MoveNext();
                }
            
            $cad_old.="</select>";
            echo "<tr>
                    <td class='titulos3'>$label</td>
                    <td class='listado3'>".$cad_new."</td>
                    <td class='tooltip' class='titulos3'><center>
            <input type='button' name='btn_accion' class='botones_2' value='&gt;&gt;' onclick=\"mover_dato('$campo')\"/ title='De click para mover los datos..'></center></td>
                    <td class='listado3'>".$cad_old."</td>
            </tr>";
            return;
       }
       /***********************************************************************************
        ** Comparar ciudades
         * new_value, old_value, valores que se desea comparar
         * tomar en cuenta la funcion javascript
        ***********************************************************************************/
        function comparar_campo($campo, $new_value, $old_value, $label, $tamano, $opciones="") {
            $opciones = "onKeyPress='if (event.keyCode==13) return false;'";
            $cad = "<tr>
            <td width='10%' class='titulos3'>$label</td>
            <td width='35%' class='listado3'><input type='text' name='new_$campo' readonly='readonly' id='new_$campo' value='".$new_value."' size='50' maxlength='$tamano' $opciones></td>";
            $cad.="<td width='10%' class='tooltip' class='titulos3'><center><input type='button' name='btn_accion' class='botones_2' value='&gt;&gt;' onclick=\"mover_dato('$campo')\"/ title='De click para mover los datos..'>
            </center></td>";        
            $cad.="<td width='35%' class='listado3'><input type='text' name='old_$campo' id='old_$campo' value='".$old_value."' size='50' maxlength='$tamano' $opciones></td>";
            $cad.="</tr>";    
            echo $cad;
            return;    
        }
       /***********************************************************************************
        ** Enviar Mail         
        ***********************************************************************************/
        function enviarMail($accion,$emailDestino,$ciu_nombre,$ciu_cedula,$institucion){
           $ruta_raiz = $this->db->rutaRaiz;
           include "$ruta_raiz/config.php";
           include_once "$ruta_raiz/funciones.php";
           if($emailDestino!="" and strpos($emailDestino,"@") and strpos($emailDestino,".",strpos($emailDestino,"@")))
            {
              switch ($accion)
              {
                case 'rechazada':
                    $mail = "<html><title>Informaci&oacute;n Quipux</title>";                    
                    $mail .= "<body><center><h1>QUIPUX</h1><br /><h2>Sistema de Gesti&oacute;n Documental</h2><br /><br /></center>";
                    $mail .= "Estimado(a) Ciudadano(a): <br><br/>";
                    $mail .= "La solicitud del ciudadano(a) ".$ciu_nombre." enviada a la instituci&oacute;n "
                            .$institucion.", ha sido <b>Rechazada</b>.";
                    $mail .= "<br /><br />Por favor verifique las observaciones de la solicitud y envie nuevamente al cumplir con lo solicitado.";
                    $mail .= "<br /><br />Le recordamos que para acceder al sistema deber&aacute; hacerlo con el usuario &quot;$ciu_cedula&quot;
                              ingresando a <a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>";
                    $mail .= "<br /><br />Saludos cordiales,<br /><br />Soporte Quipux.";
                    $mail .= "<br /><br /><b>Nota: </b>Este mensaje fue enviado autom&aacute;ticamente por el sistema, por favor no lo responda.";
                    $mail .= "<br />Si tiene alguna inquietud respecto a este mensaje, comun&iacute;quese con <a href='mailto:$cuenta_mail_soporte'>$cuenta_mail_soporte</a>";
                    $mail .= "</body></html>";    
                    enviarMail($mail, "Quipux: Solicitud para generar y firmar documentos por parte de un ciudadano.", $emailDestino, $ciu_nombre, $ruta_raiz);                    
                    break;
                case 'aceptada':
                    //Enviar correo al Super Administrador para verificar datos del ciudadano actualizado
                    $mail = "<html><title>Informaci&oacute;n Quipux</title>";                    
                    $mail .= "<body><center><h1>QUIPUX</h1><br /><h2>Sistema de Gesti&oacute;n Documental</h2><br /><br /></center>";
                    $mail .= "Estimado(a) Ciudadano(a): <br><br/><br/>";
                    $mail .= "La solicitud del ciudadano(a) ".$ciu_nombre." enviada a la instituci&oacute;n "
                            .$institucion.", ha sido <b>Aceptada</b>.";    
                    $mail .= "<br /><br />Por favor, para revisar la informaci&oacute;n. ingrese a <a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>";
                    $mail .= "<br /><br />Saludos cordiales,<br /><br />Soporte Quipux.";
                    $mail .= "<br /><br /><b>Nota: </b>Este mensaje fue enviado autom&aacute;ticamente por el sistema, por favor no lo responda.";
                    $mail .= "<br />Si tiene alguna inquietud respecto a este mensaje, comun&iacute;quese con <a href='mailto:$cuenta_mail_soporte'>$cuenta_mail_soporte</a>";
                    $mail .= "</body></html>";
                    enviarMail($mail, "Quipux: Solicitud para generar y firmar documentos por parte de un ciudadano.", $emailDestino, $ciu_nombre, $ruta_raiz);
                    break;
                case 'enviar':
                    //El ciudadano envia correo Al administrador
                    $mail = "<html><title>Informaci&oacute;n Quipux</title>";                    
                    $mail .= "<body><center><h1>QUIPUX</h1><br /><h2>Sistema de Gesti&oacute;n Documental</h2><br /><br /></center>";
                    $mail .= "Estimado(a) Administrad@r: <br><br/><br/>";
                    $mail .= "Los datos del ciudadano ".$ciu_nombre." han sido modificados por ".$ciu_nombre." "
                            .$institucion.", por favor verificar la informaci&oacute;n ingresando a <a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>";
                    $mail .= "<br /><br />Saludos cordiales,<br /><br />Soporte Quipux.";
                    $mail .= "<br /><br /><b>Nota: </b>Este mensaje fue enviado autom&aacute;ticamente por el sistema, por favor no lo responda.";
                    $mail .= "<br />Si tiene alguna inquietud respecto a este mensaje, comun&iacute;quese con <a href='mailto:$cuenta_mail_soporte'>$cuenta_mail_soporte</a>";
                    $mail .= "</body></html>";
                    enviarMail($mail, "Quipux: Solicitud para generar y firmar documentos por parte de un ciudadano.", $emailDestino, "Administrador", $ruta_raiz);
                    break;
                case 'mod_datos_ciu':
                    //El ciudadano envia correo Al administrador
                    $mail = "<html><title>Informaci&oacute;n Quipux</title>";
                    $mail .= "<body><center><h1>QUIPUX</h1><br /><h2>Sistema de Gesti&oacute;n Documental</h2><br /><br /></center>";
                    $mail .= "Estimado(a) $ciu_nombre..<br /><br />";
                    $mail .= "Los cambios solicitados a la informaci&oacute;n de su usuario han sido rechazados.
                              Por favor acerquese a la instituci&oacute;n &quot;$institucion&quot;, en donde fue registrado,
                              para que un funcionario de la misma actualice su informaci&oacute;n.";
                    $mail .= "<br /><br />Saludos cordiales,<br /><br />Soporte Quipux.";
                    $mail .= "<br /><br /><b>Nota: </b>Este mensaje fue enviado autom&aacute;ticamente por el sistema, por favor no lo responda.";
                    $mail .= "<br />Si tiene alguna inquietud respecto a este mensaje, comun&iacute;quese con <a href='mailto:$cuenta_mail_soporte'>$cuenta_mail_soporte</a>";
                    $mail .= "</body></html>";
                    break;
                case 'ciu_grabar':
                    //Enviar correo al Super Administrador para verificar datos del ciudadano actualizado
                    $mail = "<html><title>Informaci&oacute;n Quipux</title>";
                    $mail .= "Estimado(a) Administrad@r:";
                    $mail .= "<body><center><h1>QUIPUX</h1><br /><h2>Sistema de Gesti&oacute;n Documental</h2><br /><br /></center>";
                    $mail .= "Los datos del ciudadano ".$ciu_nombre." han sido modificados por ".$_SESSION["usua_nomb"]." de la instituci&oacute;n "
                            .$_SESSION["inst_nombre"].", por favor, verificar la informaci&oacute;n.";
                    $mail .= "<br /><br />Le recordamos que para acceder al sistema deber&aacute; hacerlo con el usuario &quot;$tmp_cedula&quot;
                              ingresando a <a href='$nombre_servidor' target='_blank'>$nombre_servidor</a>";
                    $mail .= "<br /><br />Saludos cordiales,<br /><br />Soporte Quipux.";
                    $mail .= "<br /><br /><b>Nota: </b>Este mensaje fue enviado autom&aacute;ticamente por el sistema, por favor no lo responda.";
                    $mail .= "<br />Si tiene alguna inquietud respecto a este mensaje, comun&iacute;quese con <a href='mailto:$cuenta_mail_soporte'>$cuenta_mail_soporte</a>";
                    $mail .= "</body></html>";
                    break;
                
              }
            }
        }
        /********************************************************************************************
         * graficar caja Hidden
        *********************************************************************************************/
        function cajaHidden($campo,$valor){
            echo "<input type='hidden' name='$campo' id='$campo' value='$valor'>";
            return;
            
        }
        /********************************************************************************************
         * Validar si es número o texto
        *********************************************************************************************/
        function esNumeroTxt($valor){
          if ($valor!=''){
            if (is_numeric($valor))
                return 1;
            else
                return 0;
          }else
              return 0;
        }
        /********************************************************************************************
         * validar si es mail
        *********************************************************************************************/
        function esEmail($email){
            if(eregi("^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email)) {
                return 1;
            }else {
            return 0;
            }
        }
        /********************************************************************************************
         * mostrar/esconder div
        *********************************************************************************************/
        function mostrar_div($nombre_div,$titulo='',$tabla='si',$imagen=''){
            $ruta_raiz = $this->db->rutaRaiz;
            if ($titulo=='')
                $titulo='Búsqueda';
            $fnjava='mostrar_div("'.$nombre_div.'")';
            if ($tabla=='si')
            $html ="<table width='100%' border='0' class='borde_tab'><tr><td>";
            $html.="<a href='javascript:void(0);' class='vinculos' target='mainFrame'>";
            //if ($imagen=='mas'){
            if ($imagen=='menos'){
                $html.="<img id='".$nombre_div."img_menos' name='".$nombre_div."img_menos' src='$ruta_raiz/iconos/menos.gif' border='1' title='Ver Consulta' onclick='$fnjava' alt='Ver Consulta'/>";
                $html.="<img id='".$nombre_div."img_mas' name='".$nombre_div."img_mas' src='$ruta_raiz/iconos/mas.gif' style='display:none' title='Ver Consulta' border='1' onclick='$fnjava' alt='Ver Consulta'/>";            
            }else{
                $html.="<img id='".$nombre_div."img_menos' name='".$nombre_div."img_menos' src='$ruta_raiz/iconos/menos.gif' style='display:none' border='1' title='Ver Consulta' onclick='$fnjava' alt='Ver Consulta'/>";
                $html.="<img id='".$nombre_div."img_mas' name='".$nombre_div."img_mas' src='$ruta_raiz/iconos/mas.gif' title='Ver Consulta' border='1' onclick='$fnjava' alt='Ver Consulta'/>";            
            }
            $html.="</a> $titulo";
             if ($tabla=='si')
            $html.="</td></tr></table>";
            
            return $html;
        }
        /********************************************************************************************
         * historico de cambios
         * Visualiza los cambios del ciudadano o el usuario
         * usr_ciud_codi: historia del usuario
         * tipo: determina la raiz desde donde se esta ejecutando
         * tabla_modificada: tabla a consultar
        *********************************************************************************************/
        function verHistorico($usr_ciud_codi,$tipo,$tabla_modificada,$nombre_div=''){
            
             $ruta_raiz = $this->db->rutaRaiz;
             if ($nombre_div=='')
             $nombre_div='div_historico';
             $html='<table width="100%" class="borde_tab"><tr><td>';
             $fnjava='mostrar_div_historico("'.$nombre_div.'","'.$usr_ciud_codi.'","'.$tipo.'","'.$tabla_modificada.'")';
             $html.="<table width='100%' border='0' class='border_tab'><tr><td>";
             $html.="<a href='javascript:void(0);' class='vinculos' target='mainFrame'>";            
             $html.="<img id='".$nombre_div."img_mas' name='".$nombre_div."img_mas' src='$ruta_raiz/iconos/mas.gif'  border='1' title='Ver Consulta' onclick='$fnjava' alt='Ver Consulta'/>";
             $html.="<img id='".$nombre_div."img_menos' name='".$nombre_div."img_menos' src='$ruta_raiz/iconos/menos.gif' style='display:none' title='Ver Consulta' border='1' onclick='$fnjava' alt='Ver Consulta'/>";            
             $html.="</a>Cambios Realizados</td></tr></table>";
             $html.="<center><div id='$nombre_div' name='$nombre_div' style='display:none'>";             
             $html.="</div></center>";
             $html.='</table>';
             return $html;
    
        }
        /*
         * funcion desplegar permisos limite 10
         */
        function verHistoricoPermisos($usr_codi,$nombre_div=''){
            
             $ruta_raiz = $this->db->rutaRaiz;
             if ($nombre_div=='')
             $nombre_div='div_historico';
             $html='<table width="100%" class="borde_tab"><tr><td>';
             $fnjava='mostrar_div_permisos("'.$nombre_div.'","'.$usr_codi.'")';
             $html.="<table width='100%' border='0' class='border_tab'><tr><td>";
             $html.="<a href='javascript:void(0);' class='vinculos' target='mainFrame'>";            
             $html.="<img id='".$nombre_div."img_mas' name='".$nombre_div."img_mas' src='$ruta_raiz/iconos/mas.gif' border='1' title='Ver Consulta' onclick='$fnjava' alt='Ver Consulta'/>";
             $html.="<img id='".$nombre_div."img_menos' name='".$nombre_div."img_menos' src='$ruta_raiz/iconos/menos.gif' style='display:none' title='Ver Consulta' border='1' onclick='$fnjava' alt='Ver Consulta'/>";            
             $html.="</a>Permisos Modificados</td></tr></table>";
             $html.="<center><div id='$nombre_div' name='$nombre_div' style='display:none'>";             
             
             $html.="</div></center>";
             $html.="</table>";
             return $html;
    
        }
        /********************************************************************************************
         * Buscar usuario
         * si existe ciudadano retorna el codigo, caso contrario 0
        *********************************************************************************************/
        function buscarUsuario($cedula){
           $sql="select * from usuario where usua_cedula = $cedula";
           $rs=$this->db->conn->query($sql);
           if(!$rs->EOF){
              $usua_codigo=$rs->fields['USUA_CODIGO'];
              return $usua_codigo;
           }
           else
               return 0;
        }
        /*************************************************************************************
        *   //Guarda Historico de tablas
        *   $tabla: mayusculas->tabla comprometida
        *   Para comparar
        *   $rs_old: datos antiguos
        *   $rs_new: datos nuevos
        *   $tabla_modificar: 1 ciudadanos,2 usuarios,3 solicitud_firma,4 ciudadano_tmp
        **************************************************************************************/
        function grabar_log_tabla($tabla,$rs_old, $rs_new, $usua_codi_actualiza,$tabla_modificar) {
            
            if ($rs_new->EOF) return 0;
            $cadena = "";
            foreach ($rs_new->fields as $campo => $valor) {
                if ($rs_old->EOF) { // Si es nuevo registro
                    if (trim($valor) != "") { // Se se puso un valor inicial diferente de null   

                        $log_tabla["id_transaccion"] = "'0'";                        
                        if ($campo=='CIU_CODIGO' || $campo=='USUA_CODI')
                        $modifica_usr=$rs_new->fields[$campo];            
                        if ($tabla_modificar!=3)  
                          if ($rs_new->fields[$campo]!='' and $campo!='USUA_PASW'){
                            if ($campo=='CIU_CODI' || $campo=='CIUDAD_CODI')
                             $cadena .= "**$campo**: ".$this->ciudad($rs_new->fields[$campo])."<br>";
                            elseif ($campo=='DEPE_CODI')
                                $cadena .= "**$campo**: ".$this->area($rs_new->fields[$campo])."<br>";
                            elseif ($campo=='USUA_TIPO_CERTIFICADO'){
                                if($rs_new->fields[$campo]!='')
                                    $cadena .= "**$campo**: ".$this->tipoCertificado($rs_new->fields[$campo])."<br>";
                            }
                            elseif ($campo=='USUA_RESPONSABLE_AREA'){
                                if ($rs_new->fields[$campo]==0)
                                    $cadena .= "**$campo**: NO <br>";
                                else
                                    $cadena .= "**$campo**: SI <br>";
                            }
                            else
                                if ($this->compruebaCampo($campo)==0)
                                    $cadena .= "**$campo**: $valor<br>";
                          }
                        }
                    
                } else {                   
                    $log_tabla["id_transaccion"] = "'1'"; //Modificado            
                    if ($campo=='CIU_CODIGO' || $campo=='USUA_CODI')
                        $modifica_usr=$rs_new->fields[$campo];            

                    if (trim($rs_new->fields[$campo]) != trim($rs_old->fields[$campo])) { 
                       //pasar los datos que solo se desea grabar en el log
                            $cadena=$this->switchCargarDatos($tabla_modificar,$rs_new,$rs_old,$campo,$cadena,$valor);

                    }
                }
            } // Fin Foreach
            $log_tabla["fecha_cambio"]          = $this->db->conn->sysTimeStamp;    
            $log_tabla["usua_codi_ori"]         = $usua_codi_actualiza;
            $log_tabla["usua_codi"]             = $modifica_usr;
            $log_tabla["logc_observacion"]      = $this->db->conn->qstr($cadena);
            $log_tabla["logc_tabla_modificada"] = $tabla_modificar;

            if (trim($cadena)!= ""){ //Graba solo si existieron cambios en los datos        
                $this->db->conn->Replace($tabla, $log_tabla, "",false,false,false,false);
            }
            return 1;
        }
        /*************************************************************************************                
        * retorna la cadena que se guarda en el log
        * $ciudad_id: codigo de la ciudad
        **************************************************************************************/
        function ciudad($ciudad_id){
            $nombre="";
            if ($ciudad_id!=0){
                $sql="select * from ciudad where id = $ciudad_id";
                
                $rs=$this->db->conn->query($sql);
                if (!$rs->EOF){
                    $nombre= $rs->fields['NOMBRE'];
                }
            }
            return $nombre;
        }
        function area($area_id){
            $nombre="";
            if ($area_id!=0){
                $sql="select * from dependencia where depe_codi = $area_id";
                
                $rs=$this->db->conn->query($sql);
                if (!$rs->EOF){
                    $nombre= $rs->fields['DEPE_NOMB'];
                }
            }
            return $nombre;
        }
        function tipoCertificado($tipoid){
            $nombre="";
            if ($tipoid!=0){
                $sql="select * from tipo_certificado where tipo_cert_codi = $tipoid";
                
                $rs=$this->db->conn->query($sql);
                if (!$rs->EOF){
                    $nombre= $rs->fields['DESCRIPCION'];
                }
            }
            return $nombre;
        }
        /*************************************************************************************
        *   //Guarda Historico de tablas        
        *   Para comparar
        *   $rs_old: datos antiguos
        *   $rs_new: datos nuevos
        *   $campo: tipo de campo que se guarda
        *   $valor: valor nuevo
        *   $tabla: 1 ciudadanos,2 usuarios,3 solicitud_firma,4 ciudadano_tmp
         * retorna la cadena que se guarda en el log
        **************************************************************************************/
        function switchCargarDatos($tabla,$rs_new,$rs_old,$campo,$cadena,$valor){
           
            $cadena2=$cadena;
            $cadena="";
            $ruta_raiz = $this->ruta_raiz;
            
                     if (trim($rs_new->fields[$campo])!="" and trim($rs_old->fields[$campo])!=""){ 
                         
                               if ($campo=='CIUDAD_CODI' || $campo=='PAIS_CODI' || $campo=='CANTON_CODI' || $campo=='PROVINCIA_CODI' || $campo=='CIU_CODI')//ciudad                                   
                                   $cadena = "**$campo**: ".$this->ciudad(trim($rs_old->fields[$campo]))." -> ".$this->ciudad($valor)." / ";
                              elseif ($campo=='DEPE_CODI')
                                  $cadena = "**$campo**: ".$this->area(trim($rs_old->fields[$campo]))." -> ".$this->area($valor)." / ";
                              elseif($campo=='USUA_TIPO_CERTIFICADO')
                                  $cadena = "**$campo**: ".$this->tipoCertificado(trim($rs_old->fields[$campo]))." -> ".$this->tipoCertificado($valor)." / ";
                                  else{                                      
                                       if ($this->compruebaCampo($campo)==0)//guardar datos necesarios
                                        $cadena = "**$campo**: ".trim($rs_old->fields[$campo])." -> $valor / ";
                                    }
                     }elseif (trim($rs_new->fields[$campo])=="" and $campo!='CIU_OBS_ACTUALIZA'){
                         
                                $cadena = "**$campo**: Se eliminó ".trim($rs_old->fields[$campo])." / <br>";
                     }else{
                         
                                if ($this->compruebaCampo($campo)==0){
                                      if ($campo=='CIUDAD_CODI' || $campo=='PAIS_CODI' || $campo=='CANTON_CODI' || $campo=='PROVINCIA_CODI' || $campo=='CIU_CODI')
                                       $cadena = "**$campo**: ".$this->ciudad(trim($rs_old->fields[$campo]))." -> ".$this->ciudad($valor)." / ";
                                      elseif($campo=='DEPE_CODI')
                                          $cadena = "**$campo**: ".$this->area(trim($rs_old->fields[$campo]))." -> ".$this->area($valor)." / ";
                                      elseif($campo=='USUA_TIPO_CERTIFICADO')
                                          $cadena = "**$campo**:  ".$this->tipoCertificado(trim($rs_old->fields[$campo]))." -> ".$this->tipoCertificado($valor)." / ";
                                     else
                                         if ($campo=='SOL_FECHA_AUTORIZADO')
                                             $cadena = "**$campo**: Se registró ".trim(substr($rs_new->fields[$campo],0,19))." / <br>";
                                             else
                                       $cadena = "**$campo**: Se registró ".trim($rs_new->fields[$campo])." / <br>";
                                }
                     }
        //cadena nueva con cadena anterior
        return $cadena.$cadena2;
     }
     //Para guardar datos que solo se modifican
    function compruebaCampo($stringCampo){
        //Estos campos no guardo en el log, ya que algunos no deberian registrarse
        $textoNoGuardar='DEPE_CODI,CIU_CODI,CIU_OBS_ACTUALIZA,USUA_CODI_ACTUALIZA
            ,CIU_FECHA_ACTUALIZA,USUA_OBS_ACTUALIZA,USUA_FECHA_ACTUALIZA,
            SOL_FIRMA,SOL_PLANILLA,SOL_PLANILLA_ESTADO,SOL_FECHA_ENVIO,SOL_CEDULA,
            CIU_CODIGO,INST_CODI,USUA_TIPO_CERTIFICADO';
        $pos=strpos(trim($textoNoGuardar), trim($stringCampo));
          
        if ($pos!='')
            return 1;
        else
            return 0;
    }
     
   function graficarGeo($ciu_pais,$ciu_provincia,$ciu_ciudad,$ciu_canton,$direccion,$referencia){
      $html="<input type='hidden' id='hcod_pais' name='hcod_pais' value='$ciu_pais'/>
      <input type='hidden' id='hcod_prov' name='hcod_prov' value='$ciu_provincia'/>
      <input type='hidden' id='hcod_ciu' name='hcod_ciu' value='$ciu_ciudad'/>
      <input type='hidden' id='hcod_canton' name='hcod_canton' value='$ciu_canton'/>";
       
       $sqlCmbPais = "select nombre, id from ciudad where id_padre = 0 order by 1";
       //echo $sqlCmbPais;
       $rsCmbPais=$this->db->conn->query($sqlCmbPais);
       $html.='<td class="titulos2"> Ubicación: </td>
             <td class="listado2">
                 <table><tr><td>* País</td>';               
        $html.="<td>".$rsCmbPais->GetMenu2('cod_pais',$ciu_pais,"0:&lt;&lt seleccione &gt;&gt;",false,"","onchange='buscarDep(1);' id='cod_pais' Class='select' $deshabilitar_campos")."</td></tr>";
        $html.='<tr><td>* Provincia/Estado</td><td><div id="div_prov" name="div_prov"></td></td></tr>';
        $html.='<tr><td>* Ciudad</td><td><div id="div_ciudad" name="div_ciudad"></td></td></tr>';
        $html.='<tr><td>Cantón</td><td><div id="div_canton" name="div_canton"></td></td></tr>';
        $html.='</table>
            </td>
	    <td class="titulos2"> Dirección: </td>';                
            $html.='<td class="listado2"><table><tr>
	    <td>
		Dirección Principal (Barrio/Número) </br><input class="caja_texto" type="text" name="ciu_direccion" id="ciu_direccion" onblur="javascript:changeCase_Articulos(this)" value="'.$direccion.'" size="50" maxlength="150">
                    </tr><tr><td>
                Referencia (Calles/Transversales) </br><input class="caja_texto" type="text" name="ciu_referencia" id="ciu_referencia" onblur="javascript:changeCase_Articulos(this)" value="'.$referencia.'" size="50" maxlength="150">
	    </td></tr></table></td>';
            return $html;
   }
   /*
    * Graficar Ciudad
    * parametro codigo de la ciudad
    */
   function dibujarCiudad($ciudad){
       if ($ciudad!=''){
       $sql ="select * from ciudad    
                where id = $ciudad";
       //echo $sql;
        $rsDepePadre=$this->db->conn->query($sql);
        $ciudadHija = $rsDepePadre->fields['NOMBRE'];
        //echo $ciudadHija."<br>";
        $ciudadPadre = $rsDepePadre->fields['ID_PADRE'];
        if ($ciudadPadre!=0){
            $this->dibujarPadre($ciudadPadre,$ciudadHija);
        }else{ 

            $sql ="select * from ciudad    
                 where id = $ciudad";       
        $rsDepePadre=$this->db->conn->query($sql);
        $ciudadNombre = $rsDepePadre->fields['NOMBRE'];
        echo $ciudadNombre;   
        }
       }
  
   }
   /*
    * Graficar ciudad padre
    */
    function dibujarPadre($idPadre,$ciudadOri){
        $sql ="select * from ciudad    
                where id = $idPadre";
        //echo $sql;
       $rsDepePadre=$this->db->conn->query($sql);
       $ciudadHija = $rsDepePadre->fields['NOMBRE'];
       $ciudadPadre = $rsDepePadre->fields['ID_PADRE'];
       $ciudadOri = $ciudadOri."/".$ciudadHija;

       if ($ciudadPadre!=0){
           $this->dibujarPadre($ciudadPadre,$ciudadOri);
       }else{ 
           echo $ciudadOri;

       }
    }
    /*
     * Funcion para guardar permisos
     * accion: 1 agrega permiso
     * accion: 0 borra permiso
     * inserta tambien en log de permisos
     */
    function guardar_permisos($usua_codi,$id_permiso,$accion){
        $rec=array();
        $record=array();
        $rec["FECHA_ACTUALIZA"] = $this->db->conn->sysTimeStamp;
        $rec["USUA_CODI"] = $usua_codi;
        $rec["USUA_CODI_ACTUALIZA"] = $_SESSION["usua_codi"];
        $rec["ID_PERMISO"] = $id_permiso;
        //Determinar accion
        //echo "Permiso: ".$id_permiso."accion: ".$accion."<br>";
        if ($accion)
            $rec["ACCION"] = 1;
        else
            $rec["ACCION"] = 0;
        $record["ID_PERMISO"] = $id_permiso;
        $record["USUA_CODI"] = $usua_codi;
        if ($accion){
            $this->db->conn->Replace("PERMISO_USUARIO", $record, "", false,false,true,false);
            $this->db->conn->Replace("LOG_USR_PERMISOS", $rec, "", false,false,false,false);
        }
        else{
             $sql = "delete from permiso_usuario where usua_codi=$usua_codi and id_permiso = $id_permiso";
                //echo $sql;
                //borro el permiso
                $this->db->conn->Execute($sql);
                //log de permisos
                $this->db->conn->Replace("LOG_USR_PERMISOS", $rec, "", false,false,false,false);
        }            
                 
    }
    /*
     * Buscar permiso del usuario
     */
    function buscarPermisoUsr($id_permiso,$usua_codi){
        if ($id_permiso!='' && $usua_codi !=''){
            $sql="select id_permiso,usua_codi from permiso_usuario 
                where id_permiso = $id_permiso and usua_codi = $usua_codi";
            //echo "<br>".$sql."<br>";
            $rsPermiso=$this->db->conn->query($sql);
             if (!$rsPermiso->EOF){                 
                 return 1;
             }else
                 return 0;
        }else
            return 0;
    }
    /*
     * Buscar permiso del usuario
     */
    function permisosUsr($usua_codi){
        if ($usua_codi !=''){
            $sql="select id_permiso,usua_codi from permiso_usuario 
                where usua_codi = $usua_codi";
            $rsPermiso=$this->db->conn->query($sql);
            $permisos = "";
            while(!$rsPermiso->EOF){
                $permisos = $permisos.",".$rsPermiso->fields['ID_PERMISO'].",";
                $rsPermiso->MoveNext();
            }
            //$permisos = $permisos.",";
            return $permisos;
        }else
            return '';
    }
    /*
     * Retornar Permisos
     * 
     */
    function nombrePermiso($id_permiso){
        if ($id_permiso!=''){
            $sql="select descripcion from permiso 
                where id_permiso = $id_permiso";
            //echo $sql;
            $rsPermiso=$this->db->conn->query($sql);
             if (!$rsPermiso->EOF){                 
                 return $rsPermiso->fields["DESCRIPCION"];
             }else
                 return "";
        }else
            return "";
    }
    
   /*
    * Funcion para dibujar los permisos en la pagina adm_usuario.php
    * Tomar en cuenta las funciones javascript
    */
    public function dibujarPermisosPerfiles($usr_codigo,$perfil,$ciud,$nombre,$countdep,$usr_nuevo,$usr_estado,$usuarioSubr,$read4,$read2){
        $ruta_raiz = $this->ruta_raiz;
            //$sql = "select * from permiso where perfil = $perfil and estado = 1";
            $sqlp = "select p.descripcion, p.descripcion_larga, p.id_permiso **SELECT**
                        from permiso p **FROM**
                        where p.estado=1 and perfil = $perfil **WHERE**
                        group by perfil, p.descripcion, p.descripcion_larga, p.id_permiso, p.orden order by perfil, p.orden asc";
                if ($usr_codigo=="") { // Si es usuario nuevo
                    $sqlp = str_replace ("**SELECT**", ", 0  as permiso **SELECT**", $sqlp);
                    $sqlp = str_replace ("**FROM**", "", $sqlp);
                } else {
                    $sqlp = str_replace ("**SELECT**", ", count(pc.id_permiso) as permiso **SELECT**", $sqlp);
                    $sqlp = str_replace ("**FROM**", " left outer join (select * from permiso_usuario where usua_codi=$usr_codigo) as pc on p.id_permiso=pc.id_permiso ", $sqlp);
                }
                if ($_SESSION["usua_codi"] != 0) // Se muestran permisos especiales para Super Admin
                    $sqlp = str_replace ("**WHERE**", " and perfil <> 5 **WHERE**", $sqlp);

                if ($_SESSION["inst_codi"]==1) { // Si es un usuario ciudadano se muestran solo ciertos permisos
                    $sqlp = str_replace ("**SELECT**", ", 0 as perfil", $sqlp);
                    //$sqlp = str_replace ("**WHERE**", " and p.id_permiso in (19,21,27) **WHERE**", $sqlp);
                    //Cambiado por Sylvia Velasco se suprimio la palabra **WHERE** para que el query se ejecute correctamente
                    $sqlp = str_replace ("**WHERE**", " and p.id_permiso in (19,21,27) ", $sqlp);
                } else {
                    $sqlp = str_replace ("**SELECT**", ", perfil", $sqlp);
                    $sqlp = str_replace ("**WHERE**", "", $sqlp);
                }

                $rs = $this->db->conn->query($sqlp);
            //echo $sqlp;
            //$rs = $db->conn->query($sql);
            //$html=$this->mostrar_div('div_'.$perfil,$nombre);

            $html.="<table width='100%' class='borde_tab' >";
            
            while (!$rs->EOF) {
                $id_permiso = $rs->fields["ID_PERMISO"];
                
                //$permisoTiene = $this->buscarPermisoUsr($id_permiso,$usr_codigo);
                $permisoTiene = $rs->fields["PERMISO"]==1;
                $checked = ($permisoTiene==1) ? "checked" : "";

                    if ($id_permiso==12){
                        
                        $htmlfunper="onclick='administrar(this,$countdep);cargarPermiso(this,$id_permiso,\"codigo_permisos\",\"codigo_permisos_eli\")'";
                    }
                    elseif ($id_permiso==29 || $id_permiso==33){
                        
                        $htmlfunper="onclick='cargarPermiso(this,$id_permiso,\"codigo_permisos\",\"codigo_permisos_eli\")'";
                    }
                    elseif ($id_permiso==19)
                        $htmlfunper="onclick='cargar_combo_firma();cargarPermiso(this,$id_permiso,\"codigo_permisos\",\"codigo_permisos_eli\")'";
                    else
                        $htmlfunper="onclick='cargarPermiso(this,$id_permiso,\"codigo_permisos\",\"codigo_permisos_eli\")'";
                    
                    if ($id_permiso==26){
                        if($_SESSION["usua_codi"]==0)//solo administrador
                      $checkboxhtml="<input type='checkbox' name='usr_permiso_$id_permiso' id='usr_permiso_$id_permiso'  $htmlfunper $checked $read2 />";
                      else
                        $checkboxhtml="<input type='checkbox' name='usr_permiso_$id_permiso' id='usr_permiso_$id_permiso'  $htmlfunper $checked disabled='desabled' $read2 />";
                    }else
                        $checkboxhtml="<input type='checkbox' name='usr_permiso_$id_permiso' id='usr_permiso_$id_permiso'  $htmlfunper $checked $read2 />";
                    $divPermiso = "<div id='div_permiso_$id_permiso' name='div_permiso_$id_permiso' style='display:none'></div>";
                $nombrePermiso = $rs->fields["DESCRIPCION"];
                $desc = $rs->fields["DESCRIPCION_LARGA"];
               
              $html.="<tr>
                    <td width='10%'>".$checkboxhtml."</td>
                    <td width='30%'>$nombrePermiso";                     
                         if ($id_permiso==12 && $checked=='checked')
                        $html.='&nbsp<a href="javascript:;" onClick="administrar(usr_permiso_'.$id_permiso.','.$countdep.');" class="Ntooltip"><span><font color="blue"> Administrar Áreas</font></span></a>';
              $html.="</td>
                    <td width='60%'>$desc</td></tr>";
              $html.="<tr><td width='10%' colspan='3'>$divPermiso</td></tr>";
              
              $rs->MoveNext();
            }
            if ($_SESSION["inst_codi"]>1 and $perfil==6) {          
                 $html.="<tr>
                    <td>
                        <input type='checkbox' name='usr_viajes' id='usr_viajes' value='1' $read2 onclick='administracion_viajes()'> 
                    </td><td>Administraci&oacute;n Viajes</td>
                </tr>";
              }
              
            $html.="</table>";

            return $html;
    }
    public function dibPerfiles($usr_codigo,$ciud,$countdep,$usr_nuevo,$usr_estado,$usuarioSubr,$read4,$read2){
       
        $perfiles = array("Generales","Asistentes ó Secretarias","Jefes","Bandeja de Entrada","Administrador","Usuarios Especiales");
        $html = "";
        $html.="<table width='100%' class='borde_tab' border='0'>";
        for($i=0;$i<sizeof($perfiles);$i++){
            if ($i==0){
            $html.="<tr><td colspan='2'>".$this->mostrar_div('div_'.$i,$perfiles[$i],'','menos');
            $html.="<tr><td><div id='div_$i' name='div_$i' >";
            }else{
                $html.="<tr><td colspan='2'>".$this->mostrar_div('div_'.$i,$perfiles[$i],'','');
                $html.="<tr><td><div id='div_$i' name='div_$i' style='display:none'>";
            }
            $html.=$this->dibujarPermisosPerfiles($usr_codigo, $i, $ciud, $perfiles[$i],$countdep,$usr_nuevo,$usr_estado,$usuarioSubr,$read4,$read2);
            $html.="";
            $html.="</div></td></tr>";
            

        }
        
          $html.="</table>";
          
        return $html;
    }
    /*
     * informacion de registro civil e informacion de usuario
     */
    public function divsInformacionUsrCiud($ciu_cedula){

        $html.='<table width="100%">';

        $html.='<tr style="display: none;"><td colspan="6">
        '.$this->mostrar_div('div_datos_registro_civil','Datos Registro Civil','si','mas').'
            <div id="div_datos_registro_civil" style="display:none; width: 100%;"></div>
            </td></tr>';

        $html.='<tr><td colspan="6">
                '.$this->mostrar_div('div_datos_usuario_multiple','Usuarios','si','menos').'
                <div id="div_datos_usuario_multiple" ></div>
            </td>
        </tr>
        </table>';

        return $html;
    }
    public function caracterEspecial($cadena){
      
      
      $charespecial = str_ireplace(array("&quot;","&amp;","&QUOT","&AMP"), array('"',"&",'"',"&"), $cadena);
      return $charespecial;
   }
            
}       
?>