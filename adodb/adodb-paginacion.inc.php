<?php

/*
	V4.52 10 Aug 2004  (c) 2000-2004 John Lim (jlim@natsoft.com.my). All rights reserved.
	  Released under both BSD license and Lesser GPL library license.
	  Whenever there is any discrepancy between the two licenses,
	  the BSD license will take precedence.
	  Set tabs to 4 for best viewing.

  	This class provides recordset pagination with
	First/Prev/Next/Last links.

	Feel free to modify this class for your own use as
	it is very basic. To learn how to use it, see the
	example in adodb/tests/testpaging.php.

	"Pablo Costa" <pablo@cbsp.com.br> implemented Render_PageLinks().

	Please note, this class is entirely unsupported,
	and no free support requests except for bug reports
	will be entertained by the author.

*/
class ADODB_Pager {
	var $id; 	// unique id for pager (defaults to 'adodb')
	var $db; 	// ADODB connection object
	var $sql; 	// sql used
	var $rs;	// recordset generated
	var $curr_page;	// current page number before Render() called, calculated in constructor
	var $rows;		// number of rows per page
	var $linksPerPage=20; // number of links per page in navigation bar
	var $showPageLinks;

	var $gridAttributes = 'width="100%"  border="0"  cellpadding="0" cellspacing="1" ';

	// Localize text strings here
	var $first = '<code>|&lt;</code>';
	var $prev = '<code>&lt;&lt;</code>';
	var $next = '<code>>></code>';
	var $last = '<code>>|</code>';
	var $moreLinks = '...';
	var $startLinks = '...';
	var $gridHeader = false;
	var $htmlSpecialChars = true;
	var $page = 'Página';
	var $linkSelectedColor = 'green';
	var $cache = 0;  #secs to cache with CachePageExecute()
	var $toRefVar;

	// Variables Orfeo
	var $toRefLink;
	var $ordenActual;
	var $orderTipo;
	var $rutaRaiz;
	var $checkAll;
	var $checkTitulo;
	var $descCarpetasGen; // Trae las Carpetas Generales del Usuario
	var $descCarpetasPer; // Trae las Carpetas Personales del Usuario
	var $linkCabecera =true; // Trae las Carpetas Personales del Usuario


    // Paginador con AJAX (desarrollado por Mauricio Haro A. mauricioharo21@gmail.com)
    // Requiere que se incluya previamente el archivo 
    var $paginador_ajax=false;
    var $link_ajax; // pagina que se llamará
    var $div_name_ajax; // Nombre del div que se cargara
    var $num_rows = 0; //Numero total de registros encontrados


	//----------------------------------------------
	// constructor
	//
	// $db	adodb connection object
	// $sql	sql statement
	// $id	optional id to identify which pager,
	//		if you have multiple on 1 page.
	//		$id should be only be [a-z0-9]*
	//
	function ADODB_Pager(&$db,$sql,$id = 'adodb', $showPageLinks = false, $ordenActual,$orderTipo="asc",$ajax=false,$div_name_ajax="div")
	{

		$this->db = $db;
		$this->ordenActual = $ordenActual;
		$this->orderTipo = $orderTipo;
                $this->div_name_ajax = $div_name_ajax;
		global $_SERVER,$PHP_SELF,$_GET,$_GET;

		$curr_page = $id.'_curr_page';
		if (empty($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];

		$this->sql = $sql;
		$this->id = $id;
		$this->db = $db;
		$this->rutaRaiz = $db->rutaRaiz;
		$this->showPageLinks = $showPageLinks;
		$this->paginador_ajax = $ajax;
		$this->link_ajax = "javascript:paginador_reload_$div_name_ajax('orderNo=$this->ordenActual&orderTipo=$this->orderTipo&$this->id"."_next_page=1');";

		//$this->rutaRaiz = $_GET['ruta_raiz'];

		$next_page = $id.'_next_page';

		if (isset($_GET[$next_page])) {
			$_GET[$curr_page] = $_GET[$next_page];
		}
		if (empty($_GET[$curr_page])) $_GET[$curr_page] = "1"; ## at first page

		$this->curr_page = $_GET[$curr_page];

	}

	//---------------------------
	// Display link to first page
	function Render_First($anchor=true)
	{

	global $PHP_SELF;
		if ($anchor) {
			if ($this->paginador_ajax) 
				$dato = $this->link_ajax;
			else
				$dato = $this->toRefLinks.'&'.$this->id."_next_page=1";
			print "<a href=\"$dato\">$this->first</a> &nbsp;";
		} else {
			print "$this->first &nbsp; ";
		}
	}

	//--------------------------
	// Display link to next page
	function render_next($anchor=true)
	{
	global $PHP_SELF;
		if ($anchor) {
			if ($this->paginador_ajax) 
				$dato = str_replace("next_page=1","next_page=".($this->rs->AbsolutePage() + 1), $this->link_ajax);
			else
				$dato = $this->toRefLinks.'&'.$this->id."_next_page=".($this->rs->AbsolutePage() + 1);
			print "<a href=\"$dato\">$this->next</a> &nbsp;";
		} else {
			print "$this->next &nbsp; ";
		}
	}

	//------------------
	// Link to last page
	//
	// for better performance with large recordsets, you can set
	// $this->db->conn->pageExecuteCountRows = false, which disables
	// last page counting.
	function render_last($anchor=true)
	{
	global $PHP_SELF;

		if (!$this->db->conn->pageExecuteCountRows) return;

		if ($anchor) {
			if ($this->paginador_ajax) 
				$dato = str_replace("next_page=1","next_page=".$this->rs->LastPageNo(), $this->link_ajax);
			else
				$dato = $this->toRefLinks.'&'.$this->id."_next_page=".$this->rs->LastPageNo();
			print "<a href=\"$dato\">$this->last</a> &nbsp;";
		} else {
			print "$this->last &nbsp; ";
		}
	}

	//---------------------------------------------------
	// original code by "Pablo Costa" <pablo@cbsp.com.br>
	function render_pagelinks()
	{
            global $PHP_SELF;
            $pages        = $this->rs->LastPageNo();
            if (!$this->db->conn->pageExecuteCountRows) {
                $pages = 0+$this->rs->AbsolutePage();
                if (!$this->rs->AtLastPage()) ++$pages;
            }

            $linksperpage = $this->linksPerPage ? $this->linksPerPage : $pages;
            for($i=1; $i <= $pages; $i+=$linksperpage) {
                if($this->rs->AbsolutePage() >= $i) {
                    $start = $i;
                }
            }
            $numbers = '';
            $end = $start+$linksperpage-1;
            $link = "order_tipo=".$this->orderTipo."&".$this->id . "_next_page";
            if($end > $pages) $end = $pages;

            if ($this->startLinks && $start > 1) {
                $pos = $start - 1;
                if ($this->paginador_ajax)
                    $dato = str_replace("next_page=1","next_page=".$pos, $this->link_ajax);
                else
                    $dato = "$this->toRefLinks&$link=$pos";

                $numbers .= "<a href=\"$dato\">$this->startLinks</a>  ";
            }

            for($i=$start; $i <= $end; $i++) {
                if ($this->rs->AbsolutePage() == $i)
                    $numbers .= "$i  ";
                else {
                    if ($this->paginador_ajax)
                        $dato = str_replace("next_page=1","next_page=".$i, $this->link_ajax);
                    else
                        $dato = "$this->toRefLinks&$link=$i";
                    $numbers .= "<a href=\"$dato\">$i</a> ";
                }
            }

            if ($this->moreLinks && $end < $pages) {
                if ($this->paginador_ajax)
                    $dato = str_replace("next_page=1","next_page=".$i, $this->link_ajax);
                else
                    $dato = "$this->toRefLinks&$link=$i";
                $numbers .= "<a href=\"$dato\">$this->moreLinks</a>  ";
            }
            print $numbers . ' &nbsp; ';

	}
	// Link to previous page
	function render_prev($anchor=true)
	{
	global $PHP_SELF;
		if ($anchor) {
			if ($this->paginador_ajax) 
				$dato = str_replace("next_page=1","next_page=".($this->rs->AbsolutePage() - 1), $this->link_ajax);
			else
				$dato = $this->toRefLinks.'&'.$this->id."_next_page=".($this->rs->AbsolutePage() - 1);
			print "<a href=\"$dato\">$this->prev</a> &nbsp;";
		} else {
			print "$this->prev &nbsp; ";
		}
	}

	//--------------------------------------------------------
	// Simply rendering of grid. You should override this for
	// better control over the format of the grid
	//
	// We use output buffering to keep code clean and readable.
	function RenderGrid()
	{
	global $gSQLBlockRows; // used by rs2html to indicate how many rows to display
	$rutaRaiz = $this->rutaRaiz;
		include_once(ADODB_DIR.'/tohtml.inc.php');
		ob_start();
		$gSQLBlockRows = $this->rows;
		//if(!$this->checkAll) $this->checkAll = false;
		//if(!$this->checkTitulo) $this->checkTitulo = true;
                $this->num_rows = $this->rs->_maxRecordCount;
                if ($this->paginador_ajax)
                    rs2html($this->db,$this->rs,$this->gridAttributes,$this->gridHeader,$this->htmlSpecialChars,true,$this->toRefVars,$this->orderTipo,$this->ordenActual,$this->rutaRaiz, $this->checkAll, $this->checkTitulo, $this->descCarpetasGen, $this->descCarpetasPer,$this->div_name_ajax);
                else
                    rs2html($this->db,$this->rs,$this->gridAttributes,$this->gridHeader,$this->htmlSpecialChars,true,$this->toRefVars,$this->orderTipo,$this->ordenActual,$this->rutaRaiz, $this->checkAll, $this->checkTitulo, $this->descCarpetasGen, $this->descCarpetasPer);
		$s = ob_get_contents();
		ob_end_clean();
		return $s;
	}

	//-------------------------------------------------------
	// Navigation bar
	//
	// we use output buffering to keep the code easy to read.
	function RenderNav()
	{
        ob_start();
		if (!$this->rs->AtFirstPage()) {
			$this->Render_First();
			$this->Render_Prev();
		} else {
			$this->Render_First(false);
			$this->Render_Prev(false);
		}
//        if ($this->showPageLinks){
            $this->Render_PageLinks();
//        }
		if (!$this->rs->AtLastPage()) {
			$this->Render_Next();
			$this->Render_Last();
		} else {
			$this->Render_Next(false);
			$this->Render_Last(false);
		}
		$s = ob_get_contents();
		ob_end_clean();
		return $s;

	}

	//-------------------
	// This is the footer
	function RenderPageCount()
	{
		if (!$this->db->conn->pageExecuteCountRows) return '';
		$lastPage = $this->rs->LastPageNo();
		if ($lastPage == -1) $lastPage = 1; // check for empty rs.
		if ($this->curr_page > $lastPage) $this->curr_page = 1;
		return " $this->page ".$this->curr_page."/".$lastPage."";
	}

	//-----------------------------------
	// Call this class to draw everything.
	function Render($rows=10,$toRefVar)
	{
	global $ADODB_COUNTRECS;
	$this->toRefVar = $toRefVar;
		$this->rows = $rows;

		$savec = $ADODB_COUNTRECS;
		if ($this->db->conn->pageExecuteCountRows) $ADODB_COUNTRECS = true;
		if ($this->cache)
			$rs = &$this->db->conn->CachePageExecute($this->cache,$this->sql,$rows,$this->curr_page);
		else
			$rs = &$this->db->conn->PageExecute($this->sql,$rows,$this->curr_page);
		$ADODB_COUNTRECS = $savec;

		$this->rs = &$rs;
		if (!$rs) {
			//print "<h3>Fallo en una Consulta: $this->sql</h3>";
            print "<h3>Tiempo de consulta expirado.</h3>";
			return;
		}
		if (!$rs->EOF && (!$rs->AtFirstPage() || !$rs->AtLastPage()))
			$header =$this->RenderNav();
		else
			$header = "&nbsp;";

		$grid = $this->RenderGrid();
		$footer = $this->RenderPageCount();
		$rs->Close();
		$this->rs = false;

		$this->RenderLayout($header,$grid,$footer," class=borde_tab");
	}

	//------------------------------------------------------
	// override this to control overall layout and formating

	function RenderLayout($header,$grid,$footer,$attributes='class=borde_tab')
	{

		echo "<table width=100% class=borde_tab border=0 >
			<tr ><td >",
				$grid,
			"</td></tr>
            <tr>
				<th ><center>",
				$header,
			"</center></th></tr>
			<tr class=paginacion align=center><td>",
				$footer,
			"</td></tr>
			</table>";
	}
}


class ADODB_Pager_Ajax {

/*
	var $ajax_variables; // Array (Se lo llena con add_variable_ajax)
	var $ajax_constantes; // str con datos adicionales
	var $ajax_div; // div en donde se guardará el resultado
	var $ajax_pagina; // pagina que se llamará
/* */	
    function ADODB_Pager_Ajax($ruta_raiz, $div, $pagina, $variables="", $constantes="") {
        include_once "$ruta_raiz/js/ajax.js";
        $ajax_variables = explode(',',$variables);
        echo "<script>\n
                function paginador_reload_div(parametros) {\n
                    paginador_reload_$div(parametros);\n
                }\n
                function paginador_reload_$div(parametros) {\n
                    document.getElementById('$div').innerHTML = '<table width=\"50%\" border=\"0\"><tr><td align=\"center\">".
                    "<br><br>Por favor espere mientras se procesa su petici&oacute;n.<br>&nbsp;<br>".
                    "<img src=\"$ruta_raiz/imagenes/progress_bar.gif\"><br>&nbsp;</td></tr></table>';\n";
        if ($constantes != "") echo "parametros += '&$constantes';\n";
        foreach ($ajax_variables as $tmp) {
            if (trim($tmp != ""))
                echo "parametros += '&$tmp=' + paginador_reload_obtener_dato('$tmp');\n";
        }
        echo "nuevoAjax('$div', 'GET', '$pagina', parametros);\n
                }\n
                function paginador_reload_obtener_dato(objeto) {
                    switch (document.getElementById(objeto).type.toLowerCase()) {
                        case 'radio':
                            var i;
                            var elementos = document.getElementsByName(objeto);
                            for (i=0 ; i<elementos.length ; i++) {
                                if (elementos[i].checked)
                                    return elementos[i].value;
                            }
                            break;
                        case 'span':
                        case 'div':
                            return document.getElementById(objeto).innerHTML;
                            break;
                        default:
                            return document.getElementById(objeto).value;
                            break;
                    }
                }
            </script>\n";
    }
}

?>