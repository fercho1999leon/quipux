<?php
/**  Programa para el manejo de gestion documental, oficios, memorandos, circulares, acuerdos
*    Desarrollado y en otros Modificado por la SubSecretar&iacute;a de Inform&aacute;tica del Ecuador
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
$ruta_raiz = "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();
?>

<script type="text/javascript">
    function llamaCuerpo(parametros){
        top.frames['mainFrame'].location.href=parametros;
    }
</script>

<body>
    <center>
    <br><br>
<?php 
/**
* Si el usuario que ingresa al sistema es el usuario super-administrador cargar el combo con la lista de las 
* instituciones.
**/
    if($_SESSION["usua_codi"]==0 or $_SESSION["admin_institucion"]==1) {
        if (isset($_POST["inst_actu"])) {
            $inst_codi = 0+$_POST["inst_actu"];
            if ($inst_codi != 0) {
                $_SESSION["inst_codi"] = $inst_actu;
        }
    } else
	$inst_actu = $_SESSION["inst_codi"];
   
    $sql = "select inst_nombre, inst_codi from institucion where inst_estado =1 order by inst_nombre asc";
    $rs = $db->conn->query($sql);
    $menu_institucion =  $rs->GetMenu2("inst_actu", $inst_actu, "0:&lt;&lt seleccione &gt;&gt;", false,"","class='select' Onchange='document.formulario.submit()'");

?>
    <form name="formulario" id="formulario" method="post" action="">
        <table width="50%" border="0" cellpadding="0" cellspacing="5" class="borde_tab">
            <tr>
                <td colspan="2" class="titulos4"><center><strong>Instituciones para Administrar</strong></center></td>
            </tr>
            <tr>
                <td align="center" class="listado2"><?= $menu_institucion ?></td>
            </tr>
        </table>
    </form>
    <br>
<?php } ?>

    <table width="50%" align="center" border="0" cellpadding="0" cellspacing="5" class="borde_tab">
        <tr>
            <td colspan="2" class="titulos4"><center><strong>M&oacute;dulo de Administraci&oacute;n</strong></center></td>
        </tr>
<?php
    $num_menu = 0;
    echo dibujar_opcion_menu("usuarios/cambiar_password.php","Cambio de contrase&ntilde;a","Opci&oacute;n para cambiar la contrase&ntilde;a del usuario actual");
    
    if($_SESSION["tipo_usuario"]==1) { // Valido si es funcionario publico
        echo dibujar_opcion_menu("listas/listas.php", "Listas de env&iacute;o", "Opci&oacute;n para Administrar Lista de usuarios para env&iacute;o de correspondencia");

        if($_SESSION["usua_admin_sistema"]==1 or $_SESSION["usua_perm_ciudadano"]==1)
            echo dibujar_opcion_menu("ciudadanos/cuerpoUsuario_ext.php?accion=2", "Ciudadanos", "Opci&oacute;n para administrar Usuarios Ciudadanos");

        if($_SESSION["usua_admin_sistema"]==1) {
            echo dibujar_opcion_menu("usuarios/mnuUsuarios.php", "Usuarios internos", "Opci&oacute;n para administrar Usuarios del Sistema de la Instituci&oacute;n Actual");
            echo dibujar_opcion_menu("dependencias/mnu_dependencias.php", "&Aacute;reas", "Opci&oacute;n para administrar &Aacute;reas de la Instituci&oacute;n");
            echo dibujar_opcion_menu("tbasicas/adm_instituciones.php", "Instituciones", "Opci&oacute;n para administrar Instituciones");            
            echo dibujar_opcion_menu("tbasicas/adm_formato_doc.php", "Numeraci&oacute;n de documentos", "Opci&oacute;n para administrar la numeraci&oacute;n de los documentos");
        }

        if($_SESSION["usua_codi"]==0) {
            echo dibujar_opcion_menu("$ruta_raiz/tx/revertir_firma_digital.php", "Regeneraci&oacute;n de archivo PDF", "Revertir la firma digital en los documentos");
            echo dibujar_opcion_menu("mensajes_alerta/mensajes_alerta_menu.php", "Administrar Alertas del Sistema", "Opci&oacute;n para administrar alertas.");
            echo dibujar_opcion_menu("catalogos/ciudad.php", "Ciudad", "Opci&oacute;n para administrar las ciudades");
            echo dibujar_opcion_menu("catalogos/titulo_usuario.php", "Título Acad&eacute;mico", "Opci&oacute;n para administrar los títulos académicos");
            echo dibujar_opcion_menu("catalogos/contenido.php", "Administración de Contenidos", "Opci&oacute;n para administrar los contenidos del sistema");
            if(date("m")==1 and date("d")==1)
                echo dibujar_opcion_menu("confirmar_inicio_secuencias();", "Inicializar Secuencias para el a&ntilde;o ". date("Y"), "Opci&oacute;n para inicializar secuencias al comenzar un nuevo a&ntilde;o", true);
        }

        if($_SESSION["usua_codi"]!=0)
            echo dibujar_opcion_menu("$ruta_raiz/backup/respaldo_menu.php", "Respaldo de Documentos", "Opci&oacute;n para solicitar el respaldo de documentos del usuario actual.");

        if($_SESSION["usua_admin_sistema"]==1 or $_SESSION["usua_codi"]==0) {
            echo dibujar_opcion_menu("$ruta_raiz/metadatos/metadatos_menu.php", "Metadatos de Documentos", "Opci&oacute;n para administrar metadatos.");
        }
        
        if ($_SESSION["perm_actualizar_sistema"] == 1) {
            echo dibujar_opcion_menu("archivos/archivos_menu.php", "Administrar repositorio de archivos", "Administra el repositorio para los archivos anexos y generados en Quipux");
        }       
    } // IF Si es funcionario publico


    if($_SESSION["usua_codi"]==0 and date("m")==1 and date("d")==1) { ?>
        <script type="text/Javascript">
            function confirmar_inicio_secuencias() {
                var texto = prompt('Por favor ingrese el siguiente texto:\n"QuiPux 2012"');
                if (texto == 'QuiPux 2012') {
                    if (confirm('¿Seguro que desea inicializar todas las secuencias del sistema?')) {
                        window.location = 'tbasicas/cambio_de_anio.php';
                    }
                }else {
                    if (texto != null)
                    alert ('Error en el texto de validacion.\nUsted ingreso la cadena: "'+texto+'"');
                }
            }
        </script>
<?php } ?>


  </table>
</center>
</body>
</html>

<?php

function dibujar_opcion_menu ($pagina, $nombre, $descripcion="", $flag_javascript=false) {
    global $num_menu;
    $funcion = "llamaCuerpo('$pagina');";
    if ($flag_javascript) $funcion=$pagina;
    $texto = "<tr>
                <td class=\"listado2\">
                    <a onclick=\"$funcion\" href='javascript:void(0);' target='mainFrame' class='vinculos' title='$descripcion'>".(++$num_menu).". $nombre</a>
                </td>
              </tr>";
    return $texto;
}
?>