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


$ruta_raiz = "../..";
session_start();
include_once "$ruta_raiz/rec_session.php";

include_once "listaAreas.php";
/*if($_SESSION["usua_admin_sistema"]!=1) die("");

include_once("$ruta_raiz/include/db/ConnectionHandler.php");
$mh = new ConnectionHandler("$ruta_raiz");
//$inst = $_GET['inst'];
switch ($_GET['var'])
{	
	case 'dpc'	:
		{	$titulo = "Listado General de Areas";
			$tit_columnas = array('Id','Nombre Area','Sigla', 'Area Padre', 'Institución');
			$isql =	"SELECT D.DEPE_CODI as \"Id\", coalesce(D.DEPE_NOMB,' ') as \"Nombre Área\", coalesce(D.DEP_SIGLA,' ') as \"Sigla\",
				coalesce(P.DEPE_NOMB,' ') as \"Área Padre\", coalesce(I.INST_NOMBRE,' ') as \"Institución\"
				FROM DEPENDENCIA D LEFT JOIN DEPENDENCIA P ON D.DEPE_CODI_PADRE=P.DEPE_CODI, INSTITUCION I
				WHERE D.INST_CODI=I.INST_CODI AND D.INST_CODI=".$inst."
				ORDER BY D.DEPE_CODI";	
		}break;
	
	case 'inst'	:
		{	$titulo = "Listado General de Instituciones";
			$tit_columnas = array('Id Institucion','RUC','Nombre','Sigla');
			$isql =	"SELECT INST_CODI as \"Codigo\", coalesce(INST_RUC,' ') as \"RUC\", coalesce(INST_NOMBRE,' ') as \"Nombre\",
				coalesce(INST_SIGLA,' ') as \"Sigla\" FROM INSTITUCION WHERE INST_ESTADO <> 0 ORDER BY  ".($orderNo+1)." $orderTipo";//INST_NOMBRE";
			
		}break;

}
//$Rs_clta = $mh->conn->Execute($isql);

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();
?>
<script type='javascript'> 
   function cerrar_ventana()
        {
           window.close();
        }

</script>
    
<body>
<center>
<br>
<table width="70%" class="borde_tab">
    <tr><td class="titulos4"><div align="center"><strong><?=$titulo?></strong></div></td></tr>
</table>
<table width="70%" >
    <tr><td>


 <!--a  href="javascript:window.close();" > Cerrar </a-->
<?
$orderTipo = $_GET['orderTipo'];
$orderNo   = $_GET['orderNo'];
//echo $_GET['var'];
//echo $isql;
//rs2html($mh,$Rs_clta,'border=0 cellpadding=0',$tit_columnas,true,true,false,false,false,$ruta_raiz,false,false,false,false);

	if(trim($orderTipo)!="DESC")
	   $orderTipo="DESC";
	else
	    $orderTipo="ASC";


    $encabezado = "&inst=".$_GET['inst']."&var=".$_GET['var']."&orderTipo=$orderTipo&orderNo=";
    $linkPagina =$_SERVER['PHP_SELF'].'?'. $encabezado; //"$PHP_SELF?$encabezado";
	$pager = new ADODB_Pager($mh,$isql,'adodb', true,$orderNo,$orderTipo);
	$pager->checkAll = false;
	$pager->checkTitulo = false;
	$pager->toRefLinks = $linkPagina;
	$pager->toRefVars = $encabezado;

	$pager->Render($rows_per_page=20,$linkPagina,$checkbox=chkAnulados);

//$Rs_clta->Close();
?>
</td></tr>
</table>
<br/>
<?php
switch($_GET['de'])
{
    case 'menu':
       {
           ?><center>
            <input  name="btn_accion" type="button" class="botones" value="Regresar" onClick="location='../dependencias/mnu_dependencias.php'"/></center>

           <?php
           }
        break;
    default:
    {echo "<center><input type='button' value='Cerrar' class='botones' onclick='javascript:window.close();'></center>"; }
 break;
}
?>
</center>
</body>
</html>*/
?>

<!-- LIBRERIAS PARA GENERADOR DE ARBOL AJAX -->
<link rel="StyleSheet" href="<?=$ruta_raiz?>/js/nornix-treemenu-2.2.0/example/style/menu_uno.css" type="text/css" media="screen" />
<script type="text/javascript" src="<?=$ruta_raiz?>/js/nornix-treemenu-2.2.0/treemenu/nornix-treemenu.js"></script>
<? require_once "$ruta_raiz/js/ajax.js";?>
<link rel="stylesheet" href="<?=$ruta_raiz?>/estilos/orfeo.css">

<script type='text/JavaScript'>
    function datosArea(depeCodi){
        var nomDivInfoArea = 'info_area';
        nuevoAjax(nomDivInfoArea, 'GET', 'datosArea_ajax.php', 'verLis=<?=$_GET['verLista']?>&depeCodi=' + depeCodi);
    }


    function imprimirAreas(){
        // Generar pdf de areas
        var x = (screen.width - 20) / 2;
        var y = (screen.height - 20) / 2;
        preview = window.open('../dependencias/generarPDFAreas.php','', 'scrollbars=yes,menubar=no,height=20,width=20,resizable=yes,toolbar=no,location=no,status=no');
        preview.moveTo(x, y);
        preview.focus();
    }
</script>

<html>
<body>
    <form method="post" name="formu1" id="formu1" action="" >
        <table width="100%">
        <tr><td align="center" class="titulos4" colspan="2"><font size="2">Administraci&oacute;n de &Aacute;reas</font></td></tr>
            <tr>
                <td valign="top" width="10%">
                <table class="borde_tab">
                    <tr>
                    <td valign="middle" width="10%">
                        <div id="menu" class="menu"><a href="javascript:;" title="Exportar áreas a pdf" onclick="imprimirAreas();">Exportar &Aacute;reas a pdf</a>
                            <?php echo obtenerAreas(0+$_GET['var'], $db);?>
                        </div>
                    </td>
                    </tr>
                </table>
                </td>
                <td width="90%" valign="top">
                    <div id="info_area"><?=$infoArea?></div>
                </td>
            </tr>
        </table>
    </form>
</body>
</html>

<script type='text/JavaScript'>
        nuevoAjax('info_area', 'GET', 'datosArea_ajax.php', 'verLis=<?=$verLista?>');
</script>
