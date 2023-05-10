<?php


$ruta_raiz = "../..";
$ruta_raiz2 = "..";
session_start();

include_once "$ruta_raiz/rec_session.php";


require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post

include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/obtenerdatos.php";
include_once "refrescarArbol.php";

?>

<table width="100%" border="1">
<tr>
             <td class="titulos2" width="15%">
		* Ciudad/País
                </td>
         
            <td class="listado2" colspan="3" width="85%">                
                <input type="text" size="30" value="<?php $ciud->dibujarCiudad($ciu_ciudad);?>" id="inputString" name="inputString" onkeypress="lookup(this);" autocomplete="off"/>
                <font size="1">Ingrese los primeros caracteres de la Ciudad o País y seleccione de la lista.</font>
                <div class="suggestionsBox" id="suggestions" style="display:none; width:300px; height:60px; overflow-x:hidden; autoflow-y:scroll;">
				
				<div class="suggestionList" id="autoSuggestionsList">
					&nbsp;
				</div>
			</div>                
          </td>
          
          
      </tr>
      
      
   
</table>
    