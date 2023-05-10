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
session_start();
$ruta_raiz = "../..";

include_once "$ruta_raiz/rec_session.php";

require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
include_once "$ruta_raiz/funciones_interfaz.php";
p_register_globals(array());

if($_SESSION["usua_admin_sistema"]!=1 and $_SESSION["usua_perm_ciudadano"]!=1) {
    echo html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
    die("");
}

// Esta función genera parte de un query para buscar si existen ciudadanos con nombres similares
function query_buscar_nombre ($nombre, $campo) {
    $campo = "translate(upper($campo), 'ÀÁÈÉÌÍÒÓÙÚÑ', 'AAEEIIOOUUN')";
    $nombre = trim($nombre);
    $nombre = strtoupper($nombre);
    $nombre = str_replace(array('À','Á','È','É','Ì','Í','Ò','Ó','Ù','Ú','Ñ'),array('A','A','E','E','I','I','O','O','U','U','N'),$nombre);
    $nombre = str_replace(array('à','á','è','é','ì','í','ò','ó','ù','ú','ñ'),array('A','A','E','E','I','I','O','O','U','U','N'),$nombre);

    $arr_buscar = explode(" ",$nombre);
    $glue = "";
    $resp = "";
    if(sizeof($arr_buscar)>1)
    {
        foreach ($arr_buscar as $tmp)
        {
            if(strlen($tmp)>2 && $tmp!="DEL" && $tmp!="LOS" && $tmp!="LAS") {
                //$resp .= " $glue $campo like '%$tmp%'";
                $resp .= " $glue $campo like '%$tmp'";
                //$glue = 'or';
                $glue = 'and';
            }
        }
    }
    else
        $resp = " $glue $campo like '$arr_buscar[0]%'";
    return $resp;
}


function query_buscar_nombre_ciudadano ($nombre, $campo) {
    //$campo1=strtoupper($campo);
    /*$Cadena="Este es un gran artículo de PHP";
    $Reemplazar="gran";
    $CadenaNueva="estupendo";
    $CadenaMod=ereg_replace($Reemplazar,$CadenaNueva,$Cadena); */

    $campo = "translate(upper($campo), 'ÀÁÈÉÌÍÒÓÙÚÑ.,', 'AAEEIIOOUUN  ')";
    $nombre = trim($nombre);
    $nombre = strtoupper($nombre);
    $nombre = str_replace(array('À','Á','È','É','Ì','Í','Ò','Ó','Ù','Ú','Ñ','.',','),array('A','A','E','E','I','I','O','O','U','U','N','',''),$nombre);
    $nombre = str_replace(array('à','á','è','é','ì','í','ò','ó','ù','ú','ñ','.',','),array('A','A','E','E','I','I','O','O','U','U','N','',''),$nombre);

    $arr_buscar = explode(" ",$nombre);
    
    $glue = "";
    $resp = "";
    $i=1;


    if(sizeof($arr_buscar)==1){
        $resp = " $glue $campo like '%$arr_buscar[0]%'";}

    else if(sizeof($arr_buscar)==2){
             $i=1;
          foreach ($arr_buscar as $tmp)
            {
                    if ($i==1){
                        $resp .= " $glue $campo like '$tmp%'";
                        }
                    else{
                        $resp .= " $glue $campo like '%$tmp'";}

                    //$glue = 'and';
                    $glue = 'or';
                    $i=$i+1;


            }
    }
    else if(sizeof($arr_buscar)>2){
          $i=1;
          foreach ($arr_buscar as $tmp)
            {
                if(strlen($tmp)>2 && $tmp!="DEL" && $tmp!="LOS" && $tmp!="LAS") {

                    if ($i==1){
                        $resp .= " $glue $campo like '$tmp%'";}
                    else{
                        $resp .= " $glue $campo like '%$tmp'";}
                    //$glue = 'and';
                    $glue = 'or';
                    $i=$i+1;
                }
            }
    }
  return $resp;
}


/**
* Verificar si se va a insertar (accion = 1) o actualizar (else) a un ciudadano.
**/
$tmp_cedula = $ciu_cedula;
if ($ciu_password != 1) {
    $ciu_password = 0;
}
// En el caso de que el ciudadano no ingrese su numero de cedula se genera un numero automaticamente igual a 9999999999 menos el codigo del usuario
if ($accion==1) {
    if ($ciu_sincedula==1) {
        $sql = "select last_value from usuarios_usua_codi_seq";
        $rs = $db->conn->Execute($sql);
        $tmp_cedula = 9999999998-$rs->fields["LAST_VALUE"];
    }
    $sql2 = "";

} else {
    if ($ciu_sincedula==1)
	$tmp_cedula = 9999999999-$ciu_codigo;
    $sql2 = " and ciu_codigo<>$ciu_codigo";
}
?>

<html>
<?
    echo html_head(); /*Imprime el head definido para el sistema*/
    require_once "$ruta_raiz/js/ajax.js";
    $param_ajax = "new_cedula=$ciu_cedula&new_sincedula=$ciu_sincedula&new_documento=$ciu_documento&new_nombre=$ciu_nombre&new_apellido=$ciu_apellido&new_titulo=$ciu_titulo&new_abr_titulo=$ciu_abr_titulo&new_empresa=$ciu_empresa&new_cargo=$ciu_cargo&new_direccion=$ciu_direccion&new_email=$ciu_email&new_telefono=$ciu_telefono";
?>
    <script type="text/javascript" src="<?=$ruta_raiz?>/js/formchek.js"></script>
    <script type="text/javascript" src="<?=$ruta_raiz?>/js/validar_cedula.js"></script>
    <script type="text/javascript">


        function comparar_ciudadanos(codigo) {
            nuevoAjax('div_comparar', 'POST', 'adm_usuario_ext_comparar.php', 'old_codigo='+codigo+"&<?=$param_ajax?>");
            return;
        }

        function mover_dato(campo) {
            document.getElementById('btn_aceptar2').style.visibility="visible";
            document.getElementById("old_"+campo).value = document.getElementById("new_"+campo).value;
            if (campo == 'cedula') {
                document.getElementById("old_sincedula").checked = false;
                ver_cedula();
            }
            return;
        }

        function ltrim(s) {
           return s.replace(/^\s+/, "");
        }

        function ValidarInformacion(grupo)
        {
            flag = true;
            if (grupo=='ciu') {
                if(document.getElementById(grupo+'_sincedula').value==1) flag = false;
            } else {
                if(document.getElementById(grupo+'_sincedula').checked) flag = false;
            }
            if(flag)
                if (!validarCedula(document.getElementById(grupo+'_cedula')))
                    return false;
            if(ltrim(document.getElementById(grupo+'_nombre').value)=='' || ltrim(document.getElementById(grupo+'_apellido').value)=='')
            {	alert("Los campos de Nombres y Apellidos son obligatorios.");
                return false;
            }
            if (!isEmail(document.getElementById(grupo+'_email').value,true))
            {	alert("El campo Email no tiene formato correcto.");
                return false;
            }
            return true;
        }

        function ver_cedula()
        {
            if(document.getElementById('old_sincedula').checked){
            document.getElementById('old_cedula').style.display='none';
            document.getElementById("img_reg2").style.display = 'none';
            document.getElementById('div_datos_registro_civil').style.display = 'none';
            }
            else{
                document.getElementById('old_cedula').style.display='';
                document.getElementById("img_reg2").style.display = '';
                document.getElementById('div_datos_registro_civil').style.display = '';
            }
            /*document.getElementById[grupo+'_cedula'].value='';*/
        }

        function aceptar_cambios() {
            
            if (ValidarInformacion('old')!=true) {
                return false;
            }
            function copiar(campo) {
                document.getElementById('ciu_'+campo).value =document.getElementById('old_'+campo).value;
            }
            copiar('codigo');
            copiar('cedula');
            copiar('documento');
            copiar('nombre');
            copiar('apellido');
            copiar('titulo');
            copiar('abr_titulo');
            copiar('empresa');
            copiar('cargo');
            copiar('direccion');
            copiar('email');
            copiar('telefono');
            if(document.getElementById('old_sincedula').checked)
                document.getElementById('ciu_sincedula').value = 1;
            else
                document.getElementById('ciu_sincedula').value = 0;
            //document.frm_confirmar.action='grabar_usuario_ext.php?cerrar=<?=$cerrar?>&accion=2';
            document.frm_confirmar.action='adm_ciudadano_grabar.php?buscar=S&cerrar=<?=$cerrar?>';
            document.frm_confirmar.submit();
        }       
        function validar_cambio_cedula(cedula) { 
           
        if (trim(cedula)!='') 
            nuevoAjax('div_datos_registro_civil', 'POST', 'validar_datos_registro_civil.php', 'cedula='+cedula);
        else
             nuevoAjax('div_datos_registro_civil', 'POST', 'validar_datos_registro_civil.php', 'cedula=999');
        } 
        function cambio_cedula(obj,tipo){            
            cedula = obj.value;
            if (cedula=='')
                document.getElementById("img_reg"+tipo).style.display = 'none';
            else
                document.getElementById("img_reg"+tipo).style.display = '';
            validar_cambio_cedula(cedula);
            document.getElementById('div_datos_registro_civil').style.display = '';
        }
        function ver_datos(tipo){
            if (tipo==2)
            cedula=document.getElementById('old_cedula').value;
        else
            cedula=document.getElementById('new_cedula').value;
            if (cedula!=''){
                validar_cambio_cedula(cedula);
                document.getElementById('div_datos_registro_civil').style.display = '';
            }
            else{
                alert('No existe Cédula');
                document.getElementById('div_datos_registro_civil').style.display = 'none';
            }
        }
    </script>
    <body>
        <form name='frm_confirmar' action="grabar_usuario_ext.php?cerrar=<?=$cerrar?>&accion=<?=$accion?>" method="post">
<?
            function caja_hidden($campo) {
                global $$campo;
                echo "<input type='hidden' name='$campo' id='$campo' value='".$$campo."'>";
                return;
            }
            caja_hidden("ciu_codigo");
            caja_hidden("ciu_cedula");
            caja_hidden("ciu_sincedula");
            caja_hidden("ciu_documento");
            caja_hidden("ciu_nombre");
            caja_hidden("ciu_apellido");
            caja_hidden("ciu_titulo");
            caja_hidden("ciu_abr_titulo");
            caja_hidden("ciu_empresa");
            caja_hidden("ciu_cargo");
            caja_hidden("ciu_direccion");
            caja_hidden("ciu_email");
            caja_hidden("ciu_telefono");
            caja_hidden("ciu_nuevo");
            caja_hidden("ciu_ciudad");

            // Varificar si existen funcionarios con nombres similares o misma cedula
            $sqlFunc = "select usua_codi
                        from usuarios
                        where usua_cedula='$tmp_cedula'
                            or ((".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_nombre"]), "usua_nomb").") or (".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_apellido"]), "usua_apellido").") and usua_esta=1)";
            $rsFunc = $db->conn->query($sqlFunc);
            //Empresa en Ciudadano
                if (limpiar_sql($_POST["ciu_empresa"])!=""){
                    $conEmpr="and  (".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_empresa"]), "ciu_empresa").")";
                    //$conSoloEmpr="or  (".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_empresa"]), "ciu_empresa").")";
                }
                
            //Cargo en Ciudadano ""
                if (limpiar_sql($_POST["ciu_cargo"])!=""){
                    $conCarg="and  (".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_cargo"]), "ciu_cargo").")";
                    //$conSoloCarg="or  (".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_cargo"]), "ciu_cargo").")";
                }
                if (limpiar_sql($_POST["ciu_ciudad"])!=""){
                    $conCiudad="and  (".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_ciudad"]), "ciu_ciudad").")";
                    //$conSoloCarg="or  (".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_cargo"]), "ciu_cargo").")";
                }

                if(!$rsFunc->EOF and limpiar_sql($_POST["ciu_empresa"])!=""){

                        //Empresa en Funcionario
                        if (limpiar_sql($_POST["ciu_empresa"])!=""){
                            $conFunEmpr="and  (".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_empresa"]), "i.inst_nombre").")";
                            //$conFunSoloEmpr="or  (".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_empresa"]), "i.inst_nombre").")";
                        }

                        //Cargo en Funcionario
                         if (limpiar_sql($_POST["ciu_cargo"])!=""){
                            $conFunCarg="and  (".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_cargo"]), "u.usua_cargo").")";
                            //$conFunSoloCarg="or  (".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_cargo"]), "u.usua_cargo").")";
                        }
                }

            
            // Verificar si existen ciudadanos con nombres similares o la misma cédula

            //No ingreso Institución y Cargo
            if (limpiar_sql($_POST["ciu_empresa"])=="" and limpiar_sql($_POST["ciu_cargo"])==""){
                $sql = "select  ciu_cedula as \"Cédula\",
                    ciu_nombre||' '||ciu_apellido as \"Nombre\",
                    ciu_titulo as \"Título\",
                    ciu_cargo as \"Cargo\",
                    ciu_empresa as \"Institución\",
                    ciu_email as \"Correo Electrónico\",
                    'Editar' as \"SCR_Función\",
                    'comparar_ciudadanos(\"'||ciu_codigo||'\");' as \"HID_FUNCION\"
                    from ciudadano where ciu_cedula='$tmp_cedula' or
                    ((".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_nombre"]), "ciu_nombre").")
                    and (".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_apellido"]), "ciu_apellido").")) and ciu_estado=1";
                    //$conEmpr $conCarg)";
            }
            elseif ((limpiar_sql($_POST["ciu_empresa"])!="" and limpiar_sql($_POST["ciu_cargo"])!="")){
                $sql = "select  ciu_cedula as \"Cédula\",
                    ciu_nombre||' '||ciu_apellido as \"Nombre\",
                    ciu_titulo as \"Título\",
                    ciu_cargo as \"Cargo\",
                    ciu_empresa as \"Institución\",
                    ciu_email as \"Correo Electrónico\",
                    'Editar' as \"SCR_Función\",
                    'comparar_ciudadanos(\"'||ciu_codigo||'\");' as \"HID_FUNCION\"
                    from ciudadano where ciu_cedula='$tmp_cedula' or
                   (
                     (".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_nombre"]), "ciu_nombre")." )
                     and (".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_apellido"]), "ciu_apellido").") $conEmpr
                    )
            or
            (
                     (".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_nombre"]), "ciu_nombre")." )
                 and (".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_apellido"]), "ciu_apellido").") $conCarg
                    ) and ciu_estado=1";
            }
            elseif (limpiar_sql($_POST["ciu_empresa"])!=""){
                $sql = "select  ciu_cedula as \"Cédula\",
                    ciu_nombre||' '||ciu_apellido as \"Nombre\",
                    ciu_titulo as \"Título\",
                    ciu_cargo as \"Cargo\",
                    ciu_empresa as \"Institución\",
                    ciu_email as \"Correo Electrónico\",
                    'Editar' as \"SCR_Función\",
                    'comparar_ciudadanos(\"'||ciu_codigo||'\");' as \"HID_FUNCION\"
                    from ciudadano where ciu_cedula='$tmp_cedula' or
                    (
                     (".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_nombre"]), "ciu_nombre")." )
                 and (".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_apellido"]), "ciu_apellido").") $conEmpr
                    ) and ciu_estado=1";

            }
            elseif (limpiar_sql($_POST["ciu_cargo"])!=""){
                 $sql = "select  ciu_cedula as \"Cédula\",
                    ciu_nombre||' '||ciu_apellido as \"Nombre\",
                    ciu_titulo as \"Título\",
                    ciu_cargo as \"Cargo\",
                    ciu_empresa as \"Institución\",
                    ciu_email as \"Correo Electrónico\",
                    'Editar' as \"SCR_Función\",
                    'comparar_ciudadanos(\"'||ciu_codigo||'\");' as \"HID_FUNCION\"
                    from ciudadano where ciu_cedula='$tmp_cedula' or
                    (
                     (".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_nombre"]), "ciu_nombre")." )
                 and (".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_apellido"]), "ciu_apellido").") $conCarg
                    ) and ciu_estado=1";

            }

                    
                    
            //Arma query para Funcionario
            if(!$rsFunc->EOF) {

                 if (limpiar_sql($_POST["ciu_empresa"])=="" and limpiar_sql($_POST["ciu_cargo"])==""){
                      $sql.= " UNION
                    select u.usua_cedula as \"Cédula\",
                    u.usua_nomb||' '||u.usua_apellido as \"Nombre\",
                    u.usua_titulo as \"Título\",
                    u.usua_cargo as \"Cargo\",
                    i.inst_nombre as \"Institución\",
                    u.usua_email as \"Correo Electrónico\",
                    '' as \"SCR_Función\",
                    '' as \"HID_FUNCION\"
                    from usuarios u, institucion i
                    where
                    ( u.usua_cedula='$tmp_cedula' and u.inst_codi = i.inst_codi )
                    or
                    ((".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_nombre"]), "u.usua_nomb").")
                    and (".query_buscar_nombre_ciudadano(limpiar_sql($_POST["ciu_apellido"]), "u.usua_apellido").")
                    and u.usua_esta=1 and u.inst_codi = i.inst_codi)";
                 }
                
                ELSEif (limpiar_sql($_POST["ciu_empresa"])!="" and limpiar_sql($_POST["ciu_cargo"])!=""){

                $sql.= " UNION
                    select u.usua_cedula as \"Cédula\",
                    u.usua_nomb||' '||u.usua_apellido as \"Nombre\",
                    u.usua_titulo as \"Título\",
                    u.usua_cargo as \"Cargo\",
                    i.inst_nombre as \"Institución\",
                    u.usua_email as \"Correo Electrónico\",
                    '' as \"SCR_Función\",
                    '' as \"HID_FUNCION\"
                    from usuarios u, institucion i
                    where
                    ( u.usua_cedula='$tmp_cedula' and u.inst_codi = i.inst_codi )
                    or
                    ((".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_nombre"]), "u.usua_nomb").")
                    and (".query_buscar_nombre_ciudadano(limpiar_sql($_POST["ciu_apellido"]), "u.usua_apellido $conFunEmprdo").")
                    and u.usua_esta=1 and u.inst_codi = i.inst_codi) $conFunEmpr
                    or
                    ((".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_nombre"]), "u.usua_nomb").")
                    and (".query_buscar_nombre_ciudadano(limpiar_sql($_POST["ciu_apellido"]), "u.usua_apellido").")
                    and u.usua_esta=1 and u.inst_codi = i.inst_codi) $conFunCarg";
                 }
                 elseif (limpiar_sql($_POST["ciu_empresa"])!="" ){

                  $sql.= " UNION
                    select u.usua_cedula as \"Cédula\",
                    u.usua_nomb||' '||u.usua_apellido as \"Nombre\",
                    u.usua_titulo as \"Título\",
                    u.usua_cargo as \"Cargo\",
                    i.inst_nombre as \"Institución\",
                    u.usua_email as \"Correo Electrónico\",
                    '' as \"SCR_Función\",
                    '' as \"HID_FUNCION\"
                    from usuarios u, institucion i
                    where
                    ( u.usua_cedula='$tmp_cedula' and u.inst_codi = i.inst_codi )
                    or
                    ((".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_nombre"]), "u.usua_nomb").")
                    and (".query_buscar_nombre_ciudadano(limpiar_sql($_POST["ciu_apellido"]), "u.usua_apellido").")
                    and u.usua_esta=1 and u.inst_codi = i.inst_codi) $conFunEmpr
                    ";
                     


                 }
                 elseif (limpiar_sql($_POST["ciu_cargo"])!=""){


                    $sql.= " UNION
                    select u.usua_cedula as \"Cédula\",
                    u.usua_nomb||' '||u.usua_apellido as \"Nombre\",
                    u.usua_titulo as \"Título\",
                    u.usua_cargo as \"Cargo\",
                    i.inst_nombre as \"Institución\",
                    u.usua_email as \"Correo Electrónico\",
                    '' as \"SCR_Función\",
                    '' as \"HID_FUNCION\"
                    from usuarios u, institucion i
                    where
                    ( u.usua_cedula='$tmp_cedula' and u.inst_codi = i.inst_codi )
                    or
                    ((".query_buscar_nombre_ciudadano (limpiar_sql($_POST["ciu_nombre"]), "u.usua_nomb").")
                    and (".query_buscar_nombre_ciudadano(limpiar_sql($_POST["ciu_apellido"]), "u.usua_apellido").")
                    and u.usua_esta=1 and u.inst_codi = i.inst_codi) $conFunCarg";
                 }

            }
            //echo $sql;
            //die();
            $rs = $db->conn->query($sql);

            if($rs->EOF) { //si no existen
                echo "</form></body</html>";
                die("<script>document.frm_confirmar.submit()</script>");
            }

            if(!$rsFunc->EOF) {
                echo "<center><h3>Existen funcionarios con datos similares a los que acaba de ingresar.</h3></center>";
            }
            else{
                echo "<center><h3>Existen ciudadanos con datos similares a los que acaba de ingresar.</h3></center>";
            }

            echo "<center><h6>Por favor, realice nuevamente la b&uacute;squeda.</h6></center>";
            
            $pager = new ADODB_Pager($db,$sql,'adodb', false,1,"");
            $pager->toRefLinks = "";
            $pager->toRefVars = "";
            $pager->checkAll = true;
            $pager->checkTitulo = false;
            $pager->Render($rows_per_page=50,$linkPagina,$checkbox=chkAnulados);
?>
            <center>
            <div id="div_datos_registro_civil" style="width: 100%;"></div>
            </center>
            <br />
            <div id="div_comparar"></div>
            
            <br/>
            <center>
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                  <?php
                  if($_SESSION["usua_codi"]==0 || $_SESSION["usua_admin_sistema"]==1 || $_SESSION["usua_perm_ciudadano"]==1) {//|| $_SESSION["usua_perm_ciudadano"]==1 "Cambio para desadocs VJ"
                  ?>
                    <td>
                    <center>
                        <input name="btn_aceptar" type="submit" class="botones_largo" value="Crear Ciudadano" onClick="return ValidarInformacion('ciu');"/>
                    </center>
                    </td>
                 <?php
                 }
                 ?>
            	<td>
                    <center>
                        <input  name="btn_accion" type="button" class="botones_largo" value="Cancelar" onclick="<?php echo ($cerrar == 'Si') ? "window.close()" : "location='./mnuUsuarios_ext.php?cerrar=$cerrar'"?>"/>
                    </center>
                </td>
              </tr>
            </table>
            </center>
        </form>
    </body>
</html>
