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

$ruta_raiz = "..";
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/obtenerdatos.php";
//Permite grabar el area en la base de datos con todos sus atributos

include_once "$ruta_raiz/funciones_interfaz.php";

echo "<html>".html_head();

?>
<body>
    <form name="frmConfirmaCreacion" action="./mnu_dependencias.php" method="post">    
    <center>
      <table width="100%">
      <?php 
      $radiNumeRadi = 0+limpiar_numero($_GET['num_radicado']);
       
      $OpcImpr = ObtenerDatosOpcImpresion($radiNumeRadi, $db);
     
      $opcImpresion= array();
      $opcImpresion['OPC_IMP_DESTINO_DESTINATARIO'] = $db->conn->qstr(limpiar_sql(trim($_GET['txtSaludo'])));
      $opcImpresion['RADI_NUME_RADI'] = 0+limpiar_numero($_GET['num_radicado']);
      //echo $OpcImpr['OPC_IMP_CODI'];
      if($OpcImpr['OPC_IMP_CODI']){  
           $opcImpresion['OPC_IMP_CODI'] = $OpcImpr['OPC_IMP_CODI'];
           $ok1 = $db->conn->Replace("OPCIONES_IMPRESION", $opcImpresion, "OPC_IMP_CODI", false,false,false,false);            
      }
      else{
         
            $ok1 = $db->conn->Replace("OPCIONES_IMPRESION", $opcImpresion, "", false,false,false,false);
      }
      ?>
      
      </table>	
    </center>
    </form>
</body>
</html>