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
*       Modificado                                      03-05-2012 
**/
$ruta_raiz = "../..";
session_start();
if($_SESSION["usua_admin_sistema"]!=1) die("");
include_once "$ruta_raiz/rec_session.php";


$nombre_o_sigla= trim(limpiar_sql($_GET['valor']));
$dep_codigo_js= 0+trim ($_GET['dep_codigo_js']);

//Permite grabar el area en la base de datos con todos sus atributos

        $sqlNomSigla = "select depe_nomb, dep_sigla from dependencia where inst_codi = ".$_SESSION['inst_codi'];         
        $sqlNomSigla.= " and (translate(upper(depe_nomb),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN')";        
        $sqlNomSigla.= " like translate(upper('".$nombre_o_sigla."'),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN')) "; 
        $sqlNomSigla.= " and depe_estado = 1";          
        if (trim($dep_codigo_js)!='')
        $sqlNomSigla.= " and depe_codi <> ".$dep_codigo_js;
        $rsNomSigla = $db->conn->query($sqlNomSigla);        
        $sirepite=0;
         if ($rsNomSigla && !$rsNomSigla->EOF)         
                    $sirepite = 1;
       ?>
         <?php          
         if ($sirepite==1){
             echo "<font color='red'>Intente otro nombre de Área</font>";             
         }
         ?>
          
          <input type="hidden" name="txt_modifica_area" id="txt_modifica_area" value="<?=$sirepite?>" >
