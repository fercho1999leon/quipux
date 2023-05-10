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

  $ruta_raiz = "..";
  session_start();
  include_once "$ruta_raiz/rec_session.php";
  require_once("$ruta_raiz/funciones.php");
  require_once("$ruta_raiz/obtenerdatos.php");
  require_once("$ruta_raiz/include/tx/Historico.php");

  $tx = new Historico($db);

  $radi_nume = trim(limpiar_sql($_POST['txt_radi_nume']));
  $radi_asoc_ante = trim(limpiar_sql($_POST['txt_radi_asoc_ante']));
  $txt_cerrar = trim(limpiar_sql($_POST['txt_cerrar']));
  $radi_asoc_cons = explode(',',substr(trim(limpiar_sql($_POST['txt_radi_asoc_cons'])),1));

  $rs = $db->query("select radi_nume_asoc from radicado where radi_nume_radi=$radi_nume");
  $ori_radi_asoc_ante = $rs->fields["RADI_NUME_ASOC"];

  $rs = $db->query("select radi_nume_radi from radicado where radi_nume_asoc=$radi_nume");
  $ori_radi_asoc_cons = array();
  while (!$rs->EOF) {
      $ori_radi_asoc_cons[] = $rs->fields["RADI_NUME_RADI"];
      $rs->MoveNext();
  }
  include "$ruta_raiz/recursivas/funciones_recursivas.php";
  $lista = new FuncionesRecursivas($db);
  $lista->tabla = "radicado";
  $lista->id_tabla = "radi_nume_radi";
  $lista->id_padre = "radi_nume_asoc";

  $flag = true;
  // Asociamos el antecedente
  if ($ori_radi_asoc_ante !== $radi_asoc_ante) {
      if ($radi_asoc_ante == "") {
          $db->query("update radicado set radi_nume_asoc=null where radi_nume_radi=$radi_nume");
          $tx->insertarHistorico($radi_nume, $_SESSION["usua_codi"], $_SESSION["usua_codi"], "Antecedente", 27, $ori_radi_asoc_ante);
          if ($ori_radi_asoc_ante != "")
              $tx->insertarHistorico($ori_radi_asoc_ante, $_SESSION["usua_codi"], $_SESSION["usua_codi"], "Consecuente", 27, $radi_nume);
      } else {
          
            $radiOri=array();
            
            $radiOri=ObtenerDatosRadicado($radi_nume,$db);//obtener radicado original
            $tipoOri=$radiOri["radi_tipo"];//tipo
            
            if ($tipoOri!=2){//si no es externo no modifica el radi_nume_deri y radi_cuentai
               
                $radiAsoc=array();
                
                $radiAsoc=ObtenerDatosRadicado($radi_asoc_ante,$db);
                $radiAsoText=$radiAsoc["radi_nume_text"];                
                $db->query("update radicado set radi_cuentai='$radiAsoText' where radi_nume_radi=$radi_nume");
             
            }
          $ok = $lista->asociar_registros($radi_asoc_ante, $radi_nume);
          if ($ok) {
              $tx->insertarHistorico($radi_nume, $_SESSION["usua_codi"], $_SESSION["usua_codi"], "Antecedente", 26, $radi_asoc_ante);
              if ($ori_radi_asoc_ante != "")
                  $tx->insertarHistorico($ori_radi_asoc_ante, $_SESSION["usua_codi"], $_SESSION["usua_codi"], "Consecuente", 27, $radi_nume);
          } else $flag = false;
      }
  }

  // Asociamos los consecuentes
  foreach ($ori_radi_asoc_cons as $old) {
      $ok = true;
      foreach ($radi_asoc_cons as $new) {
          if ($old===$new) $ok = false;
      }
      if ($ok) {
          $db->query("update radicado set radi_nume_asoc=null where radi_nume_radi=$old");
          $tx->insertarHistorico($radi_nume, $_SESSION["usua_codi"], $_SESSION["usua_codi"], "Consecuente", 27, $old);
          $tx->insertarHistorico($old, $_SESSION["usua_codi"], $_SESSION["usua_codi"], "Antecedente", 27, $radi_nume);
      }
  }
  foreach ($radi_asoc_cons as $new) {
      $ok = true;
      foreach ($ori_radi_asoc_cons as $old) {
          if ($old===$new) $ok = false;
      }
      if ($ok) {
          if ($new != "") {
              $ok = $lista->asociar_registros($radi_nume, $new);
              if ($ok) {
                  $tx->insertarHistorico($radi_nume, $_SESSION["usua_codi"], $_SESSION["usua_codi"], "Consecuente", 26, $new);
                  $tx->insertarHistorico($new, $_SESSION["usua_codi"], $_SESSION["usua_codi"], "Antecedente", 26, $radi_nume);
              } else $flag = false;
          }
      }
  }
  if (!$flag) 
      $mensaje = "alert('algunos de los documentos ya se encuentran relacionados.');";
  if ($txt_cerrar=='SI')
      echo "<script>
          window.opener.location.reload();
            window.close();
        </script>";
  else
  echo "<script>
            $mensaje
            opener.regresar();
            window.close();
        </script>";
  
?>

