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
/*************************************************************************************
** Permite a cada usuario solicitar respaldos de la documentación                   **
**                                                                                  **
** Desarrollado por:                                                                **
**      Lorena Torres J.                                                            **
*************************************************************************************/

  $ruta_raiz = "..";
  session_start();
  include_once "$ruta_raiz/rec_session.php";
  require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
  include_once "$ruta_raiz/funciones_interfaz.php";

  include_once "$ruta_raiz/js/ajax.js";
//  if($_SESSION["usua_perm_backup"]!=1) {
//      echo html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
//      die("");
//  }
  //echo "<link rel='stylesheet' type='text/css' href='$ruta_raiz/js/spiffyCal/spiffyCal_v2_1.css'>";
  echo "<html>".html_head();
  $txt_resp_soli_codi = trim(limpiar_numero($_GET["txt_resp_soli_codi"]));
  $txt_tipo_lista= trim(limpiar_numero($_GET["txt_tipo_lista"]));
  $usua_codi_solicita=$_SESSION["usua_codi"];
  $txt_tipo_ventana= trim(limpiar_sql($_GET["tipo_ventana"]));
 
  if($txt_tipo_lista == 4 and $txt_resp_soli_codi==""){

      $sql = "select resp_soli_codi from respaldo_solicitud
            where (estado_solicitud = 1 or estado_solicitud = 2 or estado_solicitud = 3 or estado_solicitud = 5)
            and usua_codi_solicita = $usua_codi_solicita
            and usua_codi_accion =$usua_codi_solicita";
      $rs = $db->conn->Execute($sql);
      $txt_resp_soli_codi = $rs->fields["RESP_SOLI_CODI"];
  }

  $sql = "select rh.fecha,
        des_ttr.sgd_ttr_descrip as descripcion,
        coalesce(us.usua_nombre,'Sistema') as usuario_acc,
        depsol.depe_nomb as depe_nomb_acc,
        estsol_acc.est_nombre_estado as estado_nombre_sol,
        estresp_acc.est_nombre_estado as estado_nombre_resp,
        rh.comentario
        from respaldo_solicitud rs
        left outer join (select * from respaldo_hist_eventos) as rh on rh.resp_soli_codi = rs.resp_soli_codi
        left outer join usuario as us on us.usua_codi = rh.usua_codi
        left outer join (select * from respaldo_estado where est_tipo=1) as estsol
        on rs.estado_solicitud = estsol.est_codi
        left outer join (select * from respaldo_estado where est_tipo=2) as estresp
        on rs.estado_respaldo = estresp.est_codi
        left outer join (select * from respaldo_estado where est_tipo=1) as estsol_acc
        on rh.estado_solicitud = estsol_acc.est_codi
        left outer join (select * from respaldo_estado where est_tipo=2) as estresp_acc
        on rh.estado_respaldo = estresp_acc.est_codi
        left outer join (select * from dependencia) as depsol
        on us.depe_codi = depsol.depe_codi
        left outer join (select * from sgd_ttr_transaccion) as des_ttr
        on rh.accion = des_ttr.sgd_ttr_codigo
        where rs.resp_soli_codi=$txt_resp_soli_codi
        order by rh.resp_hist_eventos desc";
    $rs = $db->conn->Execute($sql);

?>

<script language="JavaScript" type="text/javascript" >

   function metodoCerrar(ruta_raiz)
   {
       tipo_ventana = document.getElementById("txt_tipo_ventana").value;
       ruta_raiz = document.getElementById("txt_ruta").value;
       <? if($txt_resp_soli_codi=="") $txt_soli_codi="0";
          else $txt_soli_codi = $txt_resp_soli_codi; ?>;
       codigo = <? echo $txt_soli_codi; ?>;
       tipo_lista = <? echo $txt_tipo_lista; ?>;
       if(tipo_ventana == "popup")
            window.close();
       else
           window.location=ruta_raiz+'/backup/respaldo_informacion.php?txt_resp_soli_codi='+codigo+'&txt_tipo_lista='+tipo_lista;
   }

</script>

<body>
<center>
    <form name="formulario" action="" method="post" >
        <input type="hidden" name="txt_tipo_ventana" id="txt_tipo_ventana" value="<?php echo $txt_tipo_ventana; ?>"  maxlength="10">
        <input type="hidden" name="txt_ruta" id="txt_ruta" value="<?php echo $ruta_raiz; ?>"  maxlength="10">
        <table width="100%" align="center" border="0" cellpadding="0" cellspacing="3" class="borde_tab">
        <tr><td class="titulos2" colspan="7" align="center">Recorrido de la Solicitud No. <?php echo $txt_resp_soli_codi; ?></td></tr>
        <tr>
            <td class="titulos2" width="10%">Fecha</td>
            <td class="titulos2" width="10%">Acción</td>
            <td class="titulos2" width="15%">Usuario</td>
            <td class="titulos2" width="10%">Área</td>
            <td class="titulos2" width="10%">Estado Sol.</td>
            <td class="titulos2" width="10%">Estado Resp.</td>
            <td class="titulos2" width="20%">Comentario</td>
        </tr>
        <?
         if (!$rs) die("");
         $i = 0;
         while (!$rs->EOF) {
            $fecha = substr($rs->fields["FECHA"], 0, 19) . " " . $descZonaHoraria;
            $descripcion = $rs->fields["DESCRIPCION"];
            $usr_nombre = $rs->fields["USUARIO_ACC"];
            $usr_depe_nombre = $rs->fields["DEPE_NOMB_ACC"];
            $estado_sol = $rs->fields["ESTADO_NOMBRE_SOL"];
            $estado_resp = $rs->fields["ESTADO_NOMBRE_RESP"];
            $comentario = $rs->fields["COMENTARIO"];
            ?>
            <tr class="listado<?=($i%2 + 1)?>">
                <td><?php echo $fecha; ?></td>
                <td><?php echo $descripcion; ?></td>
                <td><?php echo $usr_nombre; ?></td>
                <td><?php echo $usr_depe_nombre; ?></td>
                <td><?php echo $estado_sol; ?></td>
                <td><?php echo $estado_resp; ?></td>
                <td><?php echo $comentario; ?></td>
            </tr>
          <?
            ++$i;
            $rs->MoveNext();
         } ?>
        </table>
       <br>
       <input type='button' name='btn_cancelar' value='Regresar' class='botones' onClick='metodoCerrar();'>
      </form>
    </center>
</body>
</html>