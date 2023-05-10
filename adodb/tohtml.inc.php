<?php
/*
  V4.52 10 Aug 2004  (c) 2000-2004 John Lim (jlim@natsoft.com.my). All rights reserved.
  Re  library license.
  Whenever there is any discrepancy between the two licenses,
  the BSD license will take precedence.

  Some pretty-printing by Chris Oxenreider <oxenreid@state.net>
*/


// specific code for tohtml

GLOBAL $gSQLMaxRows,$gSQLBlockRows, $_GET;



$gSQLMaxRows = 1000; // max no of rows to download
$gSQLBlockRows=20; // max no of rows per table block



// RecordSet to HTML Table
//------------------------------------------------------------
// Convert a recordset to a html table. Multiple tables are generated
// if the number of rows is > $gSQLBlockRows. This is because
// web browsers normally require the whole table to be downloaded
// before it can be rendered, so we break the output into several
// smaller faster rendering tables.
//
// $rsTmp: the recordset
// $ztabhtml: the table tag attributes (optional)
// $zheaderarray: contains the replacement strings for the headers (optional)
//
//  USAGE:
//	include('adodb.inc.php');
//	$db = ADONewConnection('mysql');
//	$db->Connect('mysql','userid','password','database');
//	$rsTmp = $db->Execute('select col1,col2,col3 from table');
//	rs2html($rsTmp, 'BORDER=2', array('Title1', 'Title2', 'Title3'));
//	$rsTmp->Close();
//
// RETURNS: number of rows displayed

function rs2html(&$db,&$rsTmp,$ztabhtml=false,$zheaderarray=false,$htmlspecialchars=true,$echo = true, $toRefVar,$orderTipo,$ordenActual,$rutaRaiz,$checkAll=false, $checkTitulo=false,$descCarpetasGen,$descCarpetasPer,$paginador_ajax="")
{
    if(strtoupper(trim($orderTipo))!="DESC")
    {
            $orderTipo = "asc";
    }else
    {
            $orderTipo = "desc";
    }
$s =''; $rows=0;$docnt = false;
GLOBAL $gSQLMaxRows,$gSQLBlockRows,$_GET;

	if (!$rsTmp) {
		printf(ADODB_BAD_RS,'rs2html');
		return false;
	}
	if (! $ztabhtml) $ztabhtml = " WIDTH='98%'";
	//else $docnt = true;
	$typearr = array();
	$ncols = $rsTmp->FieldCount();
	$nrows = $rsTmp->_maxRecordCount;
	$hdr = "<TABLE COLS=$ncols $ztabhtml>\n";
        if ($db->conn->pageExecuteCountRows)
            $hdr .= "<tr><td colspan='$ncols' class='listado1'>&nbsp;No. de registros encontrados: <b>$nrows</b></td></tr><tr>\n";
	$img_no = $ordenActual;
        //Por David Gamboa        
        //if ($_GET['carpeta']==2)
        //$hdr.="<th></th>";//Esta es la columna para las imagenes de estado
	for ($i=0; $i < $ncols; $i++)
	{//forma las columnas dinamicamente de acuerdo a la busqueda
		$field = $rsTmp->FetchField($i);
		if ($zheaderarray) $fname = $zheaderarray[$i];
		else $fname = htmlspecialchars($field->name);
		$typearr[$i] = $rsTmp->MetaType($field->type,$field->max_length);
			//print " $field->name $field->type $typearr[$i] ";
		if (strlen($fname)==0) $fname = '&nbsp;';
		if(isset($hor) && $hor)
		{
			$order = $i -$hor;
			$hor = 0;
		}else
		{
                        $order = $i;
		}
		$order = $i;
		$encabezado = $toRefVar.$order;
		if($fname == "HID_RADI_LEIDO")
		{
			$campoLeido = $i;
		}
		$vartemp = "IMG_Numero Documento";
		if($fname == $vartemp)
		{
			$iRad = $i;
		}
		$prefijo = substr($fname,0,4);
                switch(substr($fname,0,4))
		{
			case 'CHU_':
				break;
			case 'CHR_':
				break;
			case 'CHK_':
				break;
			case 'IDT_';
				$fname = substr($fname,4,20);
				break;
			case 'IMG_';
				$fname = substr($fname,4,20);
				break;
			case 'DAT_':
				$fname = substr($fname,4,20);
				break;
			case 'SCR_':
				$fname = substr($fname,4,20);
				break;
			case 'HOR_':
				$hor = 1;
				break;
			case 'HID_':
				$fname = substr($fname,4,20);
				$hor = 1;
				break;
		}
                
		if($checkTitulo)
		{
                        
			if($prefijo!="HID_" AND $prefijo!="CHU_" AND $prefijo!="CHR_" AND $prefijo!="CHK_" AND $prefijo!="HOR_")
			{                            
                            $hdr .= "<th >";
                            if($img_no==$i) { //Flecha del ordenamiento ascendente o descendente
                                 if ($orderTipo=="asc") {
                                     $alt_img="&#9650;";
                                     $img_base64 = "data:image/gif;base64,R0lGODlhDAAKANUAAP///////v/+/v/+/f+tKf+lGf+lIv+lIf+lEP+cEf+cEP+cCPecEPecCP+ZAPeUCfeUAPeUCO+UCP+EKe+MAOaMAOaMCd6MAP98Iv97Id6ECN6EANaECNaEAP9zGf90Ef9zENZ7AP9rEP9rCM57APdrEPdrCP9mAPdkCfdjAPdjCO9jCOZkCe9jAOZaAN5aCN5aANZaCNZaANZSAM5SAP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh+QQFPAA1ACwAAAAADAAKAAAGQ8CaUAhzDY9DGgt5nMlaKaaQ5kqhpLOXaaQyIWWx0umUMomOstV4bPoMYTDTegwCCVerkV4kAnkwNSN9dR4eGYcTGUEAIfkECTwANQAsAAAAAAwACgAABkTAmlC4qQyPQ5IFeQx1KBCmkFSBPKQhTWQRaSA7HMbCAWkojh3J2OFoJIabS4NNV5xrEvViYVcUDDV8CggIBYYHBwQHQQA7";
                                 } else {
                                     $alt_img="&#9660;";
                                     $img_base64 = "data:image/gif;base64,R0lGODlhDAAKANUAAP///////v/+/v+tKf+lIf+lGf+lEP+cEP+cCPecEPecCP+ZAPeUCPeUAO+UCO+UCf+EKe+MAOaMCOaMAN6MAP97Id6FAt6ECN6EAdaFAtaECNaEANaEAf9zGf9zENZ8As58Af9rCP9rEPdrEPdrCP9mAO9kCfdjCPdjAO9jCO9jAOZjCOZaAN5bAd5bAt5aCN5aANZbAdZaCNZbAtZaANZTAs5TAf///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh+QQFPAA3ACwAAAAADAAKAAAGQ8AKpELsdDxIUehWOYqUoVDKdLshS1gsCeaqWknZUirmvYlIKOxINivfSKcQ6VVzV08ols1eRalodXw3K3uCNywtbkEAIfkECTwANwAsAAAAAAwACgAABkFAwoBAKBgNhsMBcSsqlwiE43G7KRdYrIJiqVoVWSnH+20sEAlNhnxTMBCMy4ddZTQmIHq1EdnM9TcSeYA3ExhsQQA7";
                                 }
                                 $hdr .= "<font color=red><img src='$img_base64' border=0 alt='$alt_img'> </font>";
                            }
                            if ($paginador_ajax != "") { //Si usa el paginador ajax
                                $next_page = "";
                                if (isset ($_GET["adodb_next_page"])) $next_page = "&adodb_next_page=".$_GET["adodb_next_page"];
                                $hdr .= "<a href='javascript:paginador_reload_$paginador_ajax(\"orderNo=$i&orderTipo=$orderTipo&orden_cambio=1$next_page\")'>";
                            }else{ //Si usa el paginador normal
                                $hdr .= "<a href='".$_SERVER['PHP_SELF']."?$encabezado&orden_cambio=1'>";
                            }
                            $hdr .= "$fname </a></th>";
			}
			else
			{
				if(substr($fname,0,4)=="CHU_")
				{
					$hdr .= "<Th  width=1%><center><img src=$rutaRaiz/imagenes/estadoDoc.gif border=0 align=left width=130 height=32></Th>";
				}
				if(substr($fname,0,4)=="CHR_")
				{
				$hdr .= "<TH  width=1%><center></TH>";
				}
				if(substr($fname,0,4)=="CHK_")
				{
					if($checkAll==true) $valueCheck = " checked "; else $valueCheck = "";

					if($checkTitulo==true)
					{
					$fname = "<center><input type=checkbox name=checkAll value=checkAll onClick='markAll();' $valueCheck></center>";
					}
					else
					{
					$fname = " ";
					}
	/*			$hdr .= "<TH class=titulos2 width=1%>$fname</TH>"; */
				$hdr .= "<TH  width=1%>$fTitulo $fname</TH>";
				}
			}
		}
		else
		{
			if($prefijo!="HID_" AND $prefijo!="CHU_" AND $prefijo!="CHR_" AND $prefijo!="CHK_" AND $prefijo!="HOR_")
			{
				$hdr .= "<Th >";
				if($img_no==$i)
                                {
                                    if ($orderTipo=="asc") $alt_img="&#9650;"; else $alt_img="&#9660;";
                                    $hdr .= "<img src=$rutaRaiz/iconos/flecha$orderTipo.gif border=0 alt='$alt_img'> ";
                                }
				$hdr .= "$fname</span></Th>";
			}
			else
			{
				if(substr($fname,0,4)=="CHU_")
				{
					$hdr .= "<Th width=1%><center><img src=$rutaRaiz/imagenes/estadoDoc.gif border=0 align=left width=130 height=32></Th>";
				}
				if(substr($fname,0,4)=="CHR_")
				{
				$hdr .= "<TH  width=1%><center></TH>";
				}
				if(substr($fname,0,4)=="CHK_")
				{
					if($checkAll==true) $valueCheck = " checked "; else $valueCheck = "";

					if($checkTitulo==true)
					{
					$fname = "<center><input type=checkbox name=checkAll value=checkAll onClick='markAll();' $valueCheck></center>";
					}
					else
					{
					$fname = " ";
					}
	/*			$hdr .= "<TH class=titulos2 width=1%>$fname</TH>"; */
				$hdr .= "<TH  width=1%>$fTitulo $fname</TH>";
				}
			}
		}

	}//se forman las columnas dinamicamente
        
	$hdr .= "\n</tr>";
	if ($echo) print $hdr."\n\n";
	else $html = $hdr;
	// smart algorithm - handles ADODB_FETCH_MODE's correctly by probing...
	$numoffset = isset($rsTmp->fields[0]) ||isset($rsTmp->fields[1]) || isset($rsTmp->fields[2]);
	$ii = 0;
	$radText ="";
        //var_dump($rsTmp);
	while (!$rsTmp->EOF)
	{
	  if($ii==0)
		{
			$class_grid = "listado1";
			$ii=1;
		}
		else
		{
		  $class_grid = "listado2";
			$ii = 0;
		}
                //aqui comienza a dibujar los registros
		$s .= "<TR class=$class_grid valign=top>\n";
                /*
                //Por David Gamboa
                //Agregar imagenes               
                if ($_GET['carpeta']==2){
                    $sqlDgAsignados ="select r.radi_nume_text,r.radi_nume_radi, hd.hist_referencia, now()::date-hd.hist_referencia::date ";
                    $sqlDgAsignados.="from (select radi_nume_radi, radi_nume_text from radicado where esta_codi=2 ";
                    $sqlDgAsignados.="and radi_usua_actu=".$_SESSION["usua_codi"].") as r left outer join (select radi_nume_radi, ";
                    $sqlDgAsignados.="max(hist_codi) as hist_codi from hist_eventos ";
                    $sqlDgAsignados.="where sgd_ttr_codigo = 9 and usua_codi_dest = ".$_SESSION["usua_codi"]." group by 1) as h on ";
                    $sqlDgAsignados.=" r.radi_nume_radi=h.radi_nume_radi left outer join hist_eventos hd on hd.hist_codi=h.hist_codi";
                    $sqlDgAsignados.=" where hd.radi_nume_radi=".$rsTmp->fields["CHK_CHKANULAR"];
                    //echo "sqlDg: ".$sqlDgAsignados;
                    $rsDgAsignados= $db->query($sqlDgAsignados);
                    $fecha1 = $rsDgAsignados->fields['HIST_REFERENCIA'];
                    $compruebaReasignacion= $rsDgAsignados->fields['RADI_NUME_RADI'];
                   
                    if ($fecha1==''){
                        if($compruebaReasignacion=='')
                            $imagenEstado="<div align='center'><a href='#' class='Ntooltip'><img src='$rutaRaiz/imagenes/folder_page.png' width='15' height='15' alt='Recibido' border='0'><span>Recibido</span></div>";
                        else
                            $imagenEstado="<div align='center'><a href='#' class='Ntooltip'><img src='$rutaRaiz/imagenes/email_go.png' width='15' height='15' alt='Recibido' border='0'><span>Reasignado</span></div>";
                        $colorFiladg="black";
                    }
                    else{
                       
                            if (date('Y-m-d') > $fecha1){//documentos vencidos
                                $colorFiladg="red";
                                $imagenEstado="<div align='center'><a href='#' class='Ntooltip'><img src='$rutaRaiz/imagenes/vencidos.png' width='15' height='15' alt='Vencido' border='0'><span>Reasignado Vencido</span></div>";
                            }
                            else{
                                $colorFiladg="black";
                                if($compruebaReasignacion=='')
                                    $imagenEstado="<div align='center'><a href='#' class='Ntooltip'><img src='$rutaRaiz/imagenes/folder_page.png' width='15' height='15' alt='Recibido' border='0'><span>Nuevo</span></div>";
                                    else
                                $imagenEstado="<div align='center'><a href='#' class='Ntooltip'><img src='$rutaRaiz/imagenes/email_go.png' width='15' height='15' alt='Recibido' border='0'><span>Reasignado</span></div>";
                            }
                        }
                        
                        $s .= "<td align='center'>".$imagenEstado."</td>";                        

                    }
                    */

			$estadoRad = isset($rsTmp->fields["HID_RADI_LEIDO"]) ? $rsTmp->fields["HID_RADI_LEIDO"] : 1;
			$radicado = (isset($iRad) && isset($rsTmp->fields[$iRad])) ? $rsTmp->fields[$iRad] : "";
			if($radicado) include("$rutaRaiz/tx/imgRadicado.php");
			if($estadoRad==1)
			{
				$radFileClass = "leidos";
			}else
			{
				$radFileClass = "no_leidos";
			}
		if (strlen(trim($estadoRad)) == 0)
			$radFileClass = "leidos";
		for ($i=0; $i < $ncols; $i++)
		{
		  $special = "no";                  
			if ($i===0) $v=($numoffset) ? $rsTmp->fields[0] : reset($rsTmp->fields);
			else $v = ($numoffset) ? $rsTmp->fields[$i] : next($rsTmp->fields);
			$field = $rsTmp->FetchField($i);

			$vNext = isset($rsTmp->fields[($i+1)]) ? $rsTmp->fields[($i+1)] : "";
			$vNext1 = isset($rsTmp->fields[($i+2)]) ? $rsTmp->fields[($i+2)] : "";
			$fname = substr($field->name,0,4);

			switch($fname)
			{
			case 'HID_';
				if ($field->name=="HID_Numero_Radicado"||$field->name=="HID_RADI_NUME_RADI") {
					$verNumRadicado = $v;
				}
				break;
			 case 'CHU_';
				$chk_nomb = substr($field->name,4,20);
				$chk_value = $v;
				$valVNext = 0;
				if ($vNext ==99)  $valVNext = 99;
				if ($vNext ==0 OR $vNext ==NULL)  {$valVNext = 97;} else {if ($vNext > 0)  $valVNext = 98;}
					$fecha_dev  = $vNext1;
			 switch($valVNext)
				{
				case 99:
				$v =	"<img src='$rutaRaiz/imagenes/docDevuelto_tiempo.gif'  border=0 alt='Fecha Devolucion :$fecha_dev' title='Fecha Devolucion :$fecha_dev'>";
				break;
				case  98:
				$v =	"<img src='$rutaRaiz/imagenes/docDevuelto.gif'  border=0 alt='Fecha Devolucion :$fecha_dev' title='Fecha Devolucion :$fecha_dev'>";
					break;
				case 97:
					$fecha_dev = $rsTmp->fields["HID_SGD_DEVE_FECH"];
					if($rsTmp->fields["HID_DEVE_CODIGO1"]==99)
					{
						$v =	"<img src='$rutaRaiz/imagenes/docDevuelto_tiempo.gif'  border=0 alt='Fecha Devolucion :$fecha_dev' title='Devolucion por Tiempo de Espera'>";
						$noCheckjDevolucion = "enable";
						break;
					}
					if($rsTmp->fields["HID_DEVE_CODIGO"]>=1 and $rsTmp->fields["HID_DEVE_CODIGO"]<=98)
					{
						$v =	"<img src='$rutaRaiz/imagenes/docDevuelto.gif'  border=0 alt='Fecha Devolucion :$fecha_dev' title='Fecha Devolucion :$fecha_dev'>";
						$noCheckjDevolucion = "disable";
						break;
					}
					switch($v)
					{

						case 2;
						$v = "<img src=$rutaRaiz/imagenes/docRadicado.gif  border=0>";
						break;
						case 3;
						$v = "<img src=$rutaRaiz/imagenes/docImpreso.gif  border=0>";
						break;
						case 4;
						$v =	"<img src=$rutaRaiz/imagenes/docEnviado.gif  border=0>";

						break;
					}
				break;
				}
			$special = "si";
			break;
		case 'CHR_';
			$chk_value = $v;
			if ($vNext !=0 AND $vNext !=NULL AND $vNext1 ==3)
			$v = "<img src=$rutaRaiz/imagenes/check_x.jpg alt='Debe Modificar el Documento para poder reenviarlo'  title='Debe Modificar el Documento para poder reenviarlo' >";
			else
			$v = "<input type=radio    name='valRadio' value=$chk_value class='ebuttons2'>";
			$special = "si";
			break;
			case 'CHK_';
			$chk_nomb = substr($field->name,4,20);
			$chk_value = $v;
			if($checkAll==true) $valueCheck = " checked "; else $valueCheck = "";

			if ($noCheckjDevolucion=="disable")
			$v = "<img src=$rutaRaiz/imagenes/check_x.jpg alt='Debe Modificar el Documento para poder reenviarlo'  title='Debe Modificar el Documento para poder reenviarlo' >";
			else
			$v = "<input type=checkbox name='checkValue[$chk_value]' value='$chk_nomb' $valueCheck >";
			$special = "si";
			break;
		case ($fname =='IMG_' or $fname=='IDT_');
			$i_path = $i + 1;
			$fieldPATH = $rsTmp->FetchField($i_path);
			$fnamePATH = strtoupper($fieldPATH->name);
			$pathImagen = $rsTmp->fields[$fnamePATH];
			if($pathImagen)
			{
			$radText=trim($v);
			//$v = "<a class='grid' href=$rutaRaiz/bodega$pathImagen><span class=$radFileClass>$v</span></a>";
                        $v = "<img src='$v' title='$pathImagen' alt='[imagen]'>";
			}
			else
			{
			$v = "$v";
			$radText=trim($v);
			}

			if ($fname == 'IDT_')
			{
				$carpPer = $rsTmp->fields["HID_CARP_PER"];
				$carpCodi = $rsTmp->fields["HID_CARP_CODI"];
				$noHojas = $rsTmp->fields["HID_RADI_NUME_HOJA"];
				if($carpPer==0)
				{
					$nombreCarpeta = $descCarpetasGen[$carpCodi];
				}else
				{
					$nombreCarpeta = "(Personal)".$descCarpetasPer[$carpCodi]."";
				}
				$textCarpeta ="Carpeta Actual: ".$nombreCarpeta . " -- Numero de Hojas :". $noHojas;
				if($rsTmp->fields["HID_EANU_CODIGO"]==2)
				{
					$imgTp = "$rutaRaiz/iconos/anulacionRad.gif";
					$textCarpeta = " ** $descRadicado Anulado ** ".$textCarpeta;
				}else
				{
					if($rsTmp->fields["HID_RADI_TIPO_DERI"]==0 AND $rsTmp->fields["HID_RADI_NUME_DERI"]!=0)
					{
						$imgTp = "$rutaRaiz/iconos/anexos.gif";
					}else{
						$imgTp = "$rutaRaiz/iconos/comentarios.gif";
					}

                    /** �cono que indica si el radicado est� incluido en un expediente.
                      * Fecha de modificaci�n: 30-Junio-2006
                      * Modificador: Supersolidaria
                      */
                   include_once ("$rutaRaiz/include/tx/Expediente.php");
                    $expediente = new Expediente( $db );
$vartemp1 = "IDT_Numero_".$descRadicado;
$vartemp2 = "IDT_Numero ".$descRadicado;
                    if( $rsTmp->fields[$vartemp2] != "" )
                    {
                        $arrEnExpediente = $expediente->expedientesRadicado( $rsTmp->fields[$vartemp2] );
                    }
                    else if( $rsTmp->fields[$vartemp1] != "" )
                    {
                        $arrEnExpediente = $expediente->expedientesRadicado( $rsTmp->fields["vartemp1"] );
                    }
					// Modificado SGD 20-Septiembre-2007
					if( is_array( $arrEnExpediente ) )
					{
						if( $arrEnExpediente[0] !== 0 )
						{
							$imgExpediente = "<img src='$rutaRaiz/iconos/folder_open.gif' width=18 height=18 alt='$textCarpeta' title='$textCarpeta'>";
						}
						else
						{
							$imgExpediente = "";
						}
					}
				}
				$imgEstado = "<img src='$imgTp' width=18 height=18 alt='$textCarpeta' title='$textCarpeta'>";
			}else{
				$imgEstado = "";
			}
            /** �cono que indica si el radicado est� incluido en un expediente.
              * Fecha de modificaci�n: 30-Junio-2006
              * Modificador: Supersolidaria
              */
			// if($i ==$iRad)  $v = $imgEstado.$imgRad.$v;
			if($i ==$iRad)  $v = $imgEstado."&nbsp;".$imgExpediente.$imgRad.$v;
			break;
		case 'DAT_':
			$i_radicado = $i+1;
			$fieldDAT = $rsTmp->FetchField($i_radicado);
			$fnameDAT = $fieldDAT->name;
			// Modificado SGD 21-Septiembre-2007
			//var_dump($fnameDAT);
			$verNumRadicado = $rsTmp->fields[$fnameDAT];
//echo "<hr>".$v.$fnameDAT.$rsTmp->fields["0"]."<hr>";
//			$verNumRadicado = trim(strtoupper($rsTmp->fields[$fnameDAT]));
			// Modificado SI 7-Noviembre-2007 PRRO
			//var_dump($verNumRadicado);
                      
			$v = '<a href='.$rutaRaiz.'/verradicado.php?verrad='.trim($verNumRadicado).'&textrad='.trim($radText).'&'.$encabezado.' class=grid><span class=$radFileClass><font color="'.$colorFiladg.'">'.$v.'</font></span></a>';
                        
			$special = "si";
			break;
		case 'SCR_':
			$i_radicado = $i+1;
			$fieldDAT = $rsTmp->FetchField($i_radicado);
			$fnameDAT = $fieldDAT->name;
			$verFuncion = $rsTmp->fields[$fnameDAT];
                        if (trim($v) != "")
                            $v = "<a href='javascript:".trim($verFuncion)."' class='grid'><span class=$radFileClass>$v</span></a>";
                        else
                            $v = "<span class=$radFileClass>$v</span>";
			$special = "si";
			break;
		}
		$type = $typearr[$i];
		switch($type)
		{
		case 'D1':
			if (!strpos($v,':'))
			{
				$s .= "	<TD><a href='.$rutaRaiz.'/verradicado.php?verrad='.trim($verNumRadicado).'&textrad='.trim($radText).'&'.$encabezado.' class=grid><span class=$radFileClass>".$rsTmp->UserDate($v,"d-m-Y, H:i") ."&nbsp;</span></a></TD>\n";
                               
				break;
			}
		case 'T1':
		$s .= "	<TD><a href='.$rutaRaiz.'/verradicado.php?verrad='.trim($verNumRadicado).'&textrad='.trim($radText).'&'.$encabezado.' class=grid><span class=$radFileClass>".$rsTmp->UserTimeStamp($v,"d-m-Y, H:I") ."&nbsp;</span></a></TD>\n";
		break;
		case 'I':
		/*case 'N':
		  if($fname=="CHU_" or $fname=="CHK_")
			$s .= "	<TD align=right>".stripslashes((trim($v))) ."&nbsp;</TD>\n";

			case 'B':
				if (substr($v,8,2)=="BM" ) $v = substr($v,8);
				$mtime = substr(str_replace(' ','_',microtime()),2);
				$tmpname = "tmp/".uniqid($mtime).getmypid();
				$fd = @fopen($tmpname,'a');
				@ftruncate($fd,0);
				@fwrite($fd,$v);
				@fclose($fd);
				if (!function_exists ("mime_content_type")) {
				  function mime_content_type ($file) {
				    return exec("file -bi ".escapeshellarg($file));
				  }
				}
				$t = mime_content_type($tmpname);
				$s .= (substr($t,0,5)=="image") ? " <td><img src='$tmpname' alt='$t'></td>\\n" : " <td><a
				href='$tmpname'>$t</a></td>\\n";
				break;
			*/
			//break;
			default:
				//if ($htmlspecialchars and $special !="si") $v = htmlspecialchars(trim($v));
			$v = stripcslashes(trim($v));
			if (strlen($v) == 0) $v = '&nbsp;';
			if(substr($fname,0,4)!="HID_" AND substr($fname,0,4)!="HOR_"  )
			{
                            //<a href='.$rutaRaiz.'/verradicado.php?verrad='.trim($verNumRadicado).'&textrad='.trim($radText).'&'.$encabezado.' class=grid>
                            //$s .= "	<TD><span class=$radFileClass>". str_replace("\n",'<br>',$v) ."</span></TD>\n";
                            //$imagenFirmado="<img src='$rutaRaiz/imagenes/permiso.gif' width='15' height='15' alt='Recibido'>";
                            $s .= "	<TD><span class=$radFileClass>". str_replace("\n",'<br>',$v) ."</span></TD>\n";
			}
			}
		} // for
                $s .= "</TR>\n\n";
		$rows += 1;
		if ($rows >= $gSQLMaxRows) {
			$rows = "<p>Truncated at $gSQLMaxRows</p>";
			break;
		} // switch

		$rsTmp->MoveNext();

	// additional EOF check to prevent a widow header
		if (!$rsTmp->EOF && $rows % $gSQLBlockRows == 0) {

		//if (connection_aborted()) break;// not needed as PHP aborts script, unlike ASP
			if ($echo) print $s . "</TABLE>\n\n";
			else $html .= $s ."</TABLE>\n\n";
			$s = $hdr;
		}
	} // while
        

	if ($echo) print $s."</TABLE>\n\n";
	else $html .= $s."</TABLE>\n\n";
		if ($docnt) if ($echo) print "<H2>".$rows." Rows</H2>";
		return ($echo) ? $rows : $html;
 }//rs2html FIN DE FUNCION
// pass in 2 dimensional array
function arr2html(&$arr,$ztabhtml='',$zheaderarray='')
{
	if (!$ztabhtml) $ztabhtml = 'BORDER=1';
	$s = "<TABLE $ztabhtml  width=98%>";//';print_r($arr);
	if ($zheaderarray)
	{
		$s .= '<TR >';
		for ($i=0; $i<sizeof($zheaderarray); $i++)
		{
			$s .= "	<TH>{$zheaderarray[$i]}</TH>\n";
		}
		$s .= "\n</TR>";
	}
	for ($i=0; $i<sizeof($arr); $i++)
	{
		$s .= '<TR class=tparr>';
		$a = &$arr[$i];
		if (is_array($a))
			for ($j=0; $j<sizeof($a); $j++)
			{
				$val = $a[$j];
				if (empty($val)) $val = '&nbsp;';
				$s .= "	<TD>$val</TD>\n";
			}
		else if ($a)
		{
			$s .=  '	<TD>'.$a."</TD>\n";
		} else $s .= "	<TD>&nbsp;</TD>\n";
		$s .= "\n</TR>\n";
	}
	$s .= '</TABLE>';
	print $s;
}
?>
