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
/**
*	Autor			Iniciales		Fecha (dd/mm/aaaa)
*       David Gamboa            DG			12-05-2011
*	Comentado por		Iniciales		Fecha (dd/mm/aaaa)
*	David Gamboa            DG			19-05-2011
**/
$ruta_raiz = "../..";
$ruta_raiz2="..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

session_start();
if($_SESSION["usua_admin_sistema"]!=1) die("");
include_once "$ruta_raiz/rec_session.php";

$txt_busqueda_texto = limpiar_sql(trim($_GET['txt_nombre_buscar']));
$des_activar = limpiar_sql(trim($_GET['des_activar']));

?>  

      <?php
      
            $sql = "select dm.nombre as \"SCR_Nombre Área \",
                'datosArea(\"'||hijo||'\")' as \"HID_POPUP\",
                dm.sigla as \"Sigla\", dm.padre as \"Área Padre\"";
            
            $sql.= ", case when dm.estado = 0 then
                 'Activar' else 'Desactivar'
                 end as \"SCR_Acción \"
           ,case when dm.estado = 0 then 
                 'desactivarArea(\"'||hijo||'\",1)' 
                 else 
                 'desactivarArea(\"'||hijo||'\",0)' 
                 end as \"HID_POPUP \"";
            $sql.=" from 
                (select d.depe_codi as hijo, d.depe_nomb as nombre, d.dep_sigla as sigla,dp.depe_nomb as padre";
            
            $sql.= ", d.depe_estado as estado";
            $sql.= " from dependencia d, dependencia dp";
            $sql.= " where";
            $sql.= " (translate(upper(d.depe_nomb),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN')";
            $sql.= " like translate(upper('%".$txt_busqueda_texto."%'),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') "; 
            $sql.= " or translate(upper(d.dep_sigla),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN')";
            $sql.= " like translate(upper('%".$txt_busqueda_texto."%'),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN'))";
            $sql.= " and d.inst_codi=".$_SESSION["inst_codi"];
            $sql.= " and dp.depe_codi=d.depe_codi_padre) as dm order by 1";
           
           ?>
   