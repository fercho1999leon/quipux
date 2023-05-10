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
/*************************************************************************************
** Permite administrar ciudades                                                     **
**                                                                                  **
** Desarrollado por:                                                                **
**      Lorena Torres J.                                                            **
 * Modificado por:
 *      David Gamboa
*************************************************************************************/

    $ruta_raiz = "../..";
    session_start();
    include_once "$ruta_raiz/rec_session.php";
    require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
    include_once "$ruta_raiz/funciones_interfaz.php";

    echo "<html>".html_head();
    require_once "$ruta_raiz/js/ajax.js";
    $ancho1 = "50%";
    $ancho2 = "50%";
?>

<script language="JavaScript" type="text/javascript" >
    
   function metodoBuscar()
   {
       esconderElementos();
       document.formulario.action = "ciudad.php";
       document.formulario.submit();
   }

   function metodoSeleccionar(codigo, valor)
   {
      
       document.getElementById("div_similar").style.display="none";
        if(codigo == 0)
        {
            document.getElementById("txt_cod_ciudad").value = "";
            document.getElementById("txt_nombre_ciudad").value = "";
            document.getElementById("tr_anterior").style.display = "";
            document.getElementById("tr_actual").style.display = "none";
        }
        else{
             document.getElementById("tr_anterior").style.display = "";
            document.getElementById("tr_actual").style.display = "";
            document.getElementById("txt_cod_ciudad").value = codigo;
            document.getElementById("txt_nombre_ciudad").value = valor;
        }
        
        nuevoAjax('div_padre', 'POST', 'obtener_catalogo.php', 'codigo='+codigo);
   }

   function metodoGuardar()
   {           
       //Para modificar ciudades
       /*if((trim(document.getElementById("txt_nombre_ciudad_dep").value)) == "")
       {
           alert("El campo de ciudad no debe ser vacío.");
           return false;
       }*/
       document.formulario.action = "ciudad_grabar.php";
       document.formulario.submit();
   }
   function esconderElementos(){       
       document.getElementById("tr_actual").style.display = "none";
       nuevoAjax('div_padre', 'POST', 'obtener_catalogo.php', 'codigo=0');
   }
   function buscarAjax(){
       nombre = document.getElementById("txt_nombre_ciudad_dep").value;
       codigo = document.getElementById("txt_cod_ciudad").value;
       document.getElementById("div_similar").style.display="";
       nuevoAjax('div_similar', 'POST', 'ciudad_ajax.php', 'codigo='+codigo+"&nombre="+nombre);
   }
</script>

<body onload="esconderElementos()">
 <center>
    <form name="formulario" action="" method="post">
<table width="75%" border="0" align="center" class="t_bordeGris" id="usr_datos">
    <tr><td class="titulos2" colspan="4" align="center">ADMINISTRACIÓN DE CIUDADES</td></tr>
    <tr>
        <td width="<?php echo $ancho1; ?>" class="titulos2" align="center">Consulta</td>
        <td width="<?php echo $ancho2; ?>" class="titulos2" align="center">Creación y Modificación</td>
    </tr>
    <?
    if($txt_ciudad_buscar != "")
        $sql = "select nombre, id from ciudad where translate(upper(nombre),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') like '%' || translate(upper('".$txt_ciudad_buscar."'),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') || '%'";
    else
        $sql = "select nombre, id from ciudad where id not in(0) order by nombre,id_padre";    
    $rs_ciudad = $db->conn->Execute($sql);
    $menu_ciudad  = $rs_ciudad->GetMenu2("slc_ciudad[]",0, "0:&lt;&lt; Nueva&gt;&gt;", false, 20," id='slc_ciudad' class='select' style='height:200px;' onchange='metodoSeleccionar(this.value, this.options[selectedIndex].text)'" );
    ?>
        <tr>
            <td width="<?php echo $ancho1; ?>" class="listado2">&nbsp; &nbsp;Buscar por Ciudad:
                <input type="text" name="txt_ciudad_buscar" id="txt_ciudad_buscar" value="" size="20">
                <input type='button' name='btn_buscar' value='Buscar' class='botones' onClick='metodoBuscar();'>
            </td>
            <td width="<?php echo $ancho2; ?>" class="listado2" valign ="top"></td>
        </tr>
        <tr>
        <td width="<?php echo $ancho1; ?>" class="listado2">
            &nbsp; &nbsp;<?echo $menu_ciudad?>
        </td>
        <td width="<?php echo $ancho2; ?>" class="listado2" valign ="top">
            <table width="100%" border="0" align="center" class="t_bordeGris" id="usr_datos2">
                <tr id='tr_anterior' name='tr_anterior'>
                    <td ><b>Anterior:</b></td>
                    <td><div id="div_padre" name="div_padre"></div></td>
                </tr>
                <tr id='tr_actual' name='tr_actual'>
                    <td  >
                        
                    <b>Actual: </b>
                    </td>
                    <td>
                        <input type="hidden" name="txt_cod_ciudad" id="txt_cod_ciudad"  size="20"/>
                        <input type="text" name="txt_nombre_ciudad" id="txt_nombre_ciudad"  size="30" maxlength="100"/>
                    </td>
                </tr>
            <tr id='tr_siguiente' name='tr_siguiente'>
                <td  >
                        
                    <b>Siguiente: </b></td>
                <td align ="left">
                    
                    <?php
                      $sql = "select max(id) as id_nuevo from ciudad";    
                      $rs_ciudad = $db->conn->Execute($sql);
                      $idNuevo=$rs_ciudad->fields['ID_NUEVO']+1;
                    ?>
                    <input type="hidden" name="txt_cod_ciudad_dep" id="txt_cod_ciudad_dep" value="<?=$idNuevo?>" size="20"/>
                    <input type="text" name="txt_nombre_ciudad_dep" id="txt_nombre_ciudad_dep"  size="30" maxlength="100" onblur="buscarAjax()">
                    <div id="div_similar" name="div_similar"></div>
                </td>            
            </tr>
            <td align ="left">&nbsp;</td>
            <tr>
            </tr>
            <td align ="left">&nbsp;</td>
            <tr>
            </tr>
            <tr>
                <td align ="center">
                    <input type='button' name='btn_guardar' value='Guardar' class='botones' onClick='metodoGuardar();'>
                </td>
                <td><input type='button' name='btn_regresar' value='Regresar' class='botones' onClick="window.location='../formAdministracion.php'">
                </td>
            </tr>
            </table>
        </td>
        </tr>        
    </table>
  </form>
 </center>
</body>
</html>