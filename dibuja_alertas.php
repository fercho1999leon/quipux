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
$ruta_raiz = ".";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

//session_start();
//	if($_SESSION["usua_admin_sistema"]!=1) die("");
//include_once "$ruta_raiz/rec_session.php";

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

?>
<script type="text/javascript">
function mostrarVentana()
{
    var ventana = document.getElementById('miVentana'); // Accedemos al contenedor
    ventana.style.marginTop = "100px"; // Definimos su posición vertical. La ponemos fija para simplificar el código
    ventana.style.marginLeft = ((document.body.clientWidth-350) / 2) +  "px"; // Definimos su posición horizontal
    ventana.style.display = 'block'; // Y lo hacemos visible
}

function ocultarVentana()
{
    var ventana = document.getElementById('miVentana'); // Accedemos al contenedor
    ventana.style.display = 'none'; // Y lo hacemos invisible
}
</script>
<body>
<?php 
$ruta_raiz = ".";
$_GET['tipo_mensaje'];
//$_GET['tipo_mensaje'];//lee el tipo de mensaje
include($ruta_raiz."/Administracion/adm_mensajes_txt.php");



if ($dia==1 or $dia==0){

  if ($bloqueaSistema==0){     
     // echo $mensaje;
?>
    
<div id="miVentana" style="position: fixed; width: 700px; height: 60px; top: 15; right: 200; font-family:Verdana, Arial, Helvetica, sans-serif; font-size: 12px; font-weight: normal; border: #333333 2px solid; background-color: #F2F2F2; color: #000000;"> 
    <div style="text-align: right; padding: 5px; background-color:#F2F2F2"><font size="1">Cerrar<a href="javascript:ocultarVentana();"><font color="black">[x]</font></a></font> </div>
      <?php
             graficaAreaTexto('text_alerta',$mensaje);
      ?>  
</div>
            
          
<?php }
}?>
</body>
</html>

<?php
function graficaAreaTexto($nombreCajaTexto,$valorpost){//caja texto
?>
        <textarea name="<?php echo $nombreCajaTexto;?>" id="<?php echo $nombreCajaTexto;?>" rows="1" cols="60" readonly class="transpa"><?php echo $valorpost;?></textarea>

      <?php
}//cajatexto
?>
