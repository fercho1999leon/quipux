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
/*************************************************************************************/
/*                                                                                   */
/*************************************************************************************/

  $ruta_raiz = "../..";

  session_start();
  
  include_once "$ruta_raiz/rec_session.php";
  include "$ruta_raiz/funciones_interfaz.php";
  include "$ruta_raiz/obtenerdatos.php";

  $sql = "select substr(u.usua_cedula,1,10) as \"Cedula\"
          ,u.usua_titulo as \"Titulo\"
          ,u.usua_apellido || ' ' || u.usua_nomb as \"Nombre\"
          ,u.usua_cargo as \"Puesto\"
          ,u.usua_email as \"Email\"
          ,d.depe_nomb as \"Area\"
          ,c.nombre as \"Ciudad\"
          ,case when u.usua_esta=0 then 'Inactivo' else 'Activo' end as \"Estado\" 
          from usuarios u
          left outer join dependencia d on u.depe_codi=d.depe_codi
          left outer join ciudad c on c.id::text=d.depe_pie1
          where usua_login not like 'UADM%' and u.inst_codi=".$_SESSION["inst_codi"];
  if ((0 + $_POST["area"]) != 0) 
      $sql .= " and u.depe_codi=" . (0 + $_POST["area"]);
  else{
      $depe_codi_admin = obtenerAreasAdmin($_SESSION["usua_codi"],$_SESSION["inst_codi"],$_SESSION["usua_admin_sistema"],$db);
      if ($depe_codi_admin!=0)
      $sql .= " and u.depe_codi in ($depe_codi_admin)";
  }
  if ((0 + $_POST["estado"]) != 2) 
      $sql .= " and u.usua_esta=" . (0 + $_POST["estado"]);

  $sql .= " order by 6,2 asc, 1 asc";
//echo $sql;
  echo "<html>" . html_head() . "<body>";

  $rs = $db->conn->Execute("select depe_nomb as nombre from dependencia where depe_codi = ".$_POST['area']);
  $nombreArea = $rs->fields["NOMBRE"];
  ?>
<table class="borde_tab" width="100%">
        <tr>
            <td align="center" class="listado2">
                <?php if ($nombreArea!=''){ ?>
                <font size="2"><?='Usuarios del &Aacute;rea: '.$nombreArea?></font>
                <?php }  else { ?>
                      <font size="2"><?='USUARIOS'?></font>
                  <?php }?>
            </td>
        </tr>
    </table>
  <?php
  $rs = $db->conn->Execute("select count(1) as num from ($sql) as a");
  $num = $rs->fields["NUM"];
  
  //echo $sql;

  /*$pager = new ADODB_Pager($db,$sql,'adodb', true,2,"desc");
  $pager->checkAll = false;
  $pager->checkTitulo = true;
  $pager->toRefLinks = "";
  $pager->toRefVars = "";
  $pager->Render($rows_per_page=$num,$linkPagina,$checkbox=chkEnviar);*/
  $datosUsuarios="<table class='borde_tab' width='100%'>";
  $datosUsuarios.="<tr><td colspan='6'><b>No. de Registros Encontrados ".$num."</b></td></tr>";
  $datosUsuarios.="<tr>";
  $datosUsuarios.="<td class='titulos5' align='center'>CÉDULA</td>";
  $datosUsuarios.="<td class='titulos5' align='center'>TÍTULO</td>";
  $datosUsuarios.="<td class='titulos5' align='center'>NOMBRE</td>";
  $datosUsuarios.="<td class='titulos5' align='center'>PUESTO</td>";
  $datosUsuarios.="<td class='titulos5' align='center'>EMAIL</td>";
  $datosUsuarios.="<td class='titulos5' align='center'>&Aacute;REA</td>";
  $datosUsuarios.="<td class='titulos5' align='center'>CIUDAD</td>";
  $datosUsuarios.="<td class='titulos5' align='center'>ESTADO</td>";
  $datosUsuarios.="</tr>";
  $rsUsuarios = $db->conn->Execute($sql);
  while(!$rsUsuarios->EOF)
  {
      $datosUsuarios.="<tr>";      
      $datosUsuarios.="<td class='listado2' align='center'>".$rsUsuarios->fields["CEDULA"]."</td>";
      $datosUsuarios.="<td class='listado2' align='center'>".$rsUsuarios->fields["TITULO"]."</td>";
      $datosUsuarios.="<td class='listado2' align='center'>".$rsUsuarios->fields["NOMBRE"]."</td>";
      $datosUsuarios.="<td class='listado2' align='center'>".$rsUsuarios->fields["PUESTO"]."</td>";
      $datosUsuarios.="<td class='listado2' align='center'>".$rsUsuarios->fields["EMAIL"]."</td>";
      $datosUsuarios.="<td class='listado2' align='center'>".$rsUsuarios->fields["AREA"]."</td>";
      $datosUsuarios.="<td class='listado2' align='center'>".$rsUsuarios->fields["CIUDAD"]."</td>";
      $datosUsuarios.="<td class='listado2' align='center'>".$rsUsuarios->fields["ESTADO"]."</td>";
      $datosUsuarios.="</tr>";
      $rsUsuarios->MoveNext();
  }
  $datosUsuarios.="</table>";
  echo $datosUsuarios;
?>
<table width="100%">
    <tr><td align="center">
    <input  name="btn_accion" type="button" class="botones" value="Imprimir" onClick="window.print();"/>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input  name="btn_accion" type="button" class="botones" value="Regresar" onClick="location='./mnuUsuarios.php'"/>
        </td></tr>
</table>
 
  </body>
</html>
