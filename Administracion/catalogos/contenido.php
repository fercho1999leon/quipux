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
** Permite administrar contenidos                                                   **
**                                                                                  **
** Desarrollado por:                                                                **
**      Lorena Torres J.                                                            **
*************************************************************************************/

    $ruta_raiz = "../..";
    session_start();
    include_once "$ruta_raiz/rec_session.php";
    require_once "$ruta_raiz/funciones.php";
    require_once "$ruta_raiz/funciones_interfaz.php";
    echo "<html>".html_head(); /*Imprime el head definido para el sistema*/
    require_once "$ruta_raiz/js/ajax.js";

    $ancho1 = "25%";
    $ancho2 = "75%";   
    $txt_texto = "</br>";

    if($cmb_tipo_contenido != "" and $cmb_tipo_contenido != 0){
        $sql = "select * from contenido
        where cont_tipo_codi = $cmb_tipo_contenido
        and fecha_actualiza = (select max(fecha_actualiza) from contenido where cont_tipo_codi = $cmb_tipo_contenido)";        
        $rs = $db->conn->Execute($sql);

        $cont_codi = $rs->fields["CONT_CODI"];
        $descripcion = $rs->fields["DESCRIPCION"];
        $txt_texto = $rs->fields["TEXTO"];
        $fecha_actualiza = $rs->fields["FECHA_ACTUALIZA"];
        $fecha_actualiza = substr($fecha_actualiza,0,19) . " ". $descZonaHoraria;
    }   
?>
<script type="text/javascript" src="<?=$ruta_raiz?>/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src='<?=$ruta_raiz?>/js/base64.js'></script>
<script type="text/javascript">
    function crearEditor()
    {
        CKEDITOR.replace('txt_texto');
//        var sBasePath = "<?=$ruta_raiz?>/js/fckeditor/" ;
//        //var oFCKeditor = new FCKeditor( 'txt_texto' ) ;
//        var oFCKeditor = new FCKeditor( 'txt_texto', 1000, 400, '');
//        oFCKeditor.BasePath = sBasePath;
//        oFCKeditor.ReplaceTextarea();
    }

   function metodoGuardar()
   {
       var oEditor = CKEDITOR.instances.txt_texto;
//    var oEditor = FCKeditorAPI.GetInstance('txt_texto') ;
    if(oEditor.getData()=="" || oEditor.getData()==null || oEditor.getData()=='<br />'){
        alert("Debe ingresar el texto del contenido");
        return false;
    }

    if(document.getElementById("txt_descripcion").value==""){
        alert("Debe ingresar la descripción");
        return false;
    }

    if(document.getElementById("cmb_tipo_contenido").value=="" || document.getElementById("cmb_tipo_contenido").value==0){
        alert("Debe seleccionar un tipo de contenido válido");
        return false;
    }
    var varTexto = Base64.encode(Base64.encode(oEditor.getData()));
    document.getElementById("txt_texto_usuario").value =  varTexto;
    document.formulario.action = "contenido_grabar.php";
    document.formulario.submit();
   }

   function cargar_datos(){

       document.formulario.action = "contenido.php";
       document.formulario.submit();
       
   }
</script>

<body onload="crearEditor();">
 <center>
    <form name="formulario" action="" method="post">
<table width="90%" border="0" align="center" class="t_bordeGris" id="usr_datos">
    <tr><td class="titulos2" colspan="4" align="center">ADMINISTRACIÓN DE CONTENIDOS</td></tr>      
    <input type="hidden" name="txt_texto_usuario" id="txt_texto_usuario" value="">
    <input type="hidden" name="txt_cont_codi" id="txt_cont_codi" value="<?php echo $cont_codi; ?>">
        <tr >
            <td class="titulos2" width="<?php echo $ancho1; ?>">Funcionalidad:</td>
            <td class="listado2" width="<?php echo $ancho2; ?>">
                <?
                    $sql="select funcionalidad || ' - ' || categoria as tipo, cont_tipo_codi from contenido_tipo order by funcionalidad, categoria asc";
                    $rs=$db->conn->query($sql);                    
                    if($rs) print $rs->GetMenu2("cmb_tipo_contenido", $cmb_tipo_contenido, "0:&lt;&lt; Seleccione el tipo de contenido &gt;&gt;", false,"","class='select' id='cmb_tipo_contenido' style='width: 250px;' onChange='cargar_datos()'" );
                ?>
            </td>
            <td class="titulos2" width="<?php echo $ancho1; ?>"></td>
            <td class="listado2" width="<?php echo $ancho2; ?>">                
            </td>
        </tr>               
         <tr >
            <td class="titulos2" width="<?php echo $ancho1; ?>">Descripción:</td>
            <td class="listado2" width="<?php echo $ancho2; ?>">
                <input type="text" name="txt_descripcion" id="txt_descripcion" value="<?php echo $descripcion; ?>" size="40">
            </td>
            <td class="titulos2" width="<?php echo $ancho1; ?>">Fecha Actualización:</td>
            <td class="listado2" width="<?php echo $ancho2; ?>"><?php echo $fecha_actualiza; ?></td>
        </tr>
        <tr>
            <td  class="titulos2" width="<?php echo $ancho1; ?>">Texto:</td>
            <td class="listado2" width="<?php echo $ancho2; ?>" colspan ="4"></td>               
        </tr>
        <tr>
            <td class="listado2" colspan ="4" align ="center" width="<?php echo $ancho1; ?>">
               <textarea name="txt_texto" id="txt_texto" rows="100" cols="50" style="width: 100%; height: 500px"><?php echo $txt_texto ?></textarea>
            </td>
        </tr>
        <tr>
        <td class="listado2" colspan ="4" align ="center">
            <input type='button' name='btn_guardar' value='Guardar' class='botones' onClick='metodoGuardar();'>
            <input type='button' name='btn_regresar' value='Regresar' class='botones' onClick="window.location='../formAdministracion.php'">
        </td>
        </tr>
    </table>
  </form>
 </center>
</body>
</html>