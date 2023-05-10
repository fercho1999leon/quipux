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
** Permite administrar los títulos académicos de los usuarios                       **
**                                                                                  **
** Desarrollado por:                                                                **
**      Lorena Torres J.                                                            **
*************************************************************************************/

    $ruta_raiz = "../..";
    session_start();
    include_once "$ruta_raiz/rec_session.php";
    require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
    include_once "$ruta_raiz/funciones_interfaz.php";

    echo "<html>".html_head();

    $ancho1 = "50%";
    $ancho2 = "50%";
?>

<script language="JavaScript" type="text/javascript" >

   function metodoBuscar()
   {
       document.formulario.action = "titulo_usuario.php";
       document.formulario.submit();
   }

   function metodoSeleccionar(codigo, valor)
   {
        if(codigo == 0)
        {
            document.getElementById("txt_cod_titulo").value = "";
            document.getElementById("txt_nombre_titulo").value = "";
            document.getElementById("txt_abreviatura_titulo").value = "";
        }
        else{
            document.getElementById("txt_cod_titulo").value = codigo;
            nombre = valor.split(" - ");
            document.getElementById("txt_nombre_titulo").value = nombre[0];
            document.getElementById("txt_abreviatura_titulo").value = nombre[1];
        }
   }

   function metodoGuardar()
   {
       if(trim(document.getElementById("txt_nombre_titulo").value) == "")
       {
           alert("El campo de título no debe ser vacío.");
           return false;
       }
       if(trim(document.getElementById("txt_nombre_titulo").value) == "undefined")
       {
           alert("Por favor ingrese un título válido.");
           return false;
       }
       if(trim(document.getElementById("txt_abreviatura_titulo").value) == "")
       {
           alert("El campo de abreviatura no debe ser vacío.");
           return false;
       }
       if(trim(document.getElementById("txt_abreviatura_titulo").value) == "undefined")
       {
           alert("Por favor ingrese una abreviatura válida.");
           return false;
       }
       document.formulario.action = "titulo_usuario_grabar.php";
       document.formulario.submit();
   }

</script>

<body>
 <center>
    <form name="formulario" action="" method="post">
<table width="75%" border="0" align="center" class="t_bordeGris" id="usr_datos">
    <tr><td class="titulos2" colspan="4" align="center">ADMINISTRACIÓN DE TÍTULOS ACADÉMICOS</td></tr>
    <tr>
        <td width="<?php echo $ancho1; ?>" class="titulos2" align="center">Consulta</td>
        <td width="<?php echo $ancho2; ?>" class="titulos2" align="center">Creación y Modificación</td>
    </tr>
    <?
    if($txt_titulo_buscar != "")
        $sql = "select tit_nombre || ' - ' || tit_abreviatura, tit_codi from titulo where translate(upper(tit_nombre),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') like '%' || translate(upper('".$txt_titulo_buscar."'),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') || '%'";
    else
        $sql = "select tit_nombre || ' - ' || tit_abreviatura, tit_codi from titulo order by tit_nombre asc";
    $rs_ciudad = $db->conn->Execute($sql);
    $menu_titulo  = $rs_ciudad->GetMenu2("slc_ciudad[]",0, "0:&lt;&lt; Nuevo Título Académico &gt;&gt;", false, 20," id='slc_ciudad' class='select' style='height:200px;' onchange='metodoSeleccionar(this.value, this.options[selectedIndex].text)'" );
    ?>
        <tr>
            <td width="<?php echo $ancho1; ?>" class="listado2">&nbsp; &nbsp;Buscar por Título:
                <input type="text" name="txt_titulo_buscar" id="txt_titulo_buscar" value="" size="20">
                <input type='button' name='btn_buscar' value='Buscar' class='botones' onClick='metodoBuscar();'>
            </td>
            <td width="<?php echo $ancho2; ?>" class="listado2" valign ="top"></td>
        </tr>
        <tr>
        <td width="<?php echo $ancho1; ?>" class="listado2">
            &nbsp; &nbsp;<?echo $menu_titulo?>
        </td>
        <td width="<?php echo $ancho2; ?>" class="listado2" valign ="top">
            <table width="100%" border="0" align="center" class="t_bordeGris" id="usr_datos2">
            <tr>
                <td align ="left" width="15%"><b>Título:</b></td>
                <td align ="left">
                    <input type="hidden" name="txt_cod_titulo" id="txt_cod_titulo"  size="20">
                    <input type="text" name="txt_nombre_titulo" id="txt_nombre_titulo"  size="25" maxlength="100">
                </td>
            </tr>           
           <tr>
               <td align ="left" width="15%"><b>Abreviatura:</b></td>
                <td align ="left">
                    <input type="text" name="txt_abreviatura_titulo" id="txt_abreviatura_titulo"  size="25" maxlength="50">
                </td>
            </tr>
            <td align ="left">&nbsp;</td>
            <tr>
            </tr>
            <tr>
                <td align ="center" colspan="2">
                    <input type='button' name='btn_guardar' value='Guardar' class='botones' onClick='metodoGuardar();'>
                    <input type='button' name='btn_regresar' value='Regresar' class='botones' onClick="window.location='../formAdministracion.php'">
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