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
/*****************************************************************************************
**  Muestra el componente para embeber los archivos PDF                                 **
*****************************************************************************************/
?>
<!DOCTYPE html>
<html dir="ltr" mozdisallowselectionprint>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <title>Documento: <?=$arch_nombre?></title>
        <link rel="stylesheet" href="<?=$ruta_raiz?>/js/pdf_js/web/viewer.css"/>

        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/web/compatibility.js"></script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/external/webL10n/l10n.js"></script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/src/core.js"></script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/src/util.js"></script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/src/api.js"></script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/src/canvas.js"></script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/src/obj.js"></script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/src/function.js"></script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/src/charsets.js"></script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/src/cidmaps.js"></script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/src/colorspace.js"></script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/src/crypto.js"></script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/src/evaluator.js"></script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/src/fonts.js"></script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/src/glyphlist.js"></script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/src/image.js"></script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/src/metrics.js"></script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/src/parser.js"></script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/src/pattern.js"></script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/src/stream.js"></script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/src/worker.js"></script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/external/jpgjs/jpg.js"></script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/src/jpx.js"></script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/src/jbig2.js"></script>
        <script type="text/javascript">
            PDFJS.workerSrc = '<?=$ruta_raiz?>/js/pdf_js/src/worker_loader.js';
//            var DEFAULT_URL = 'data:application/pdf;base64,XXXX...';
            var DEFAULT_URL = '<?=$url?>';

        </script>
        <script type="text/javascript" src="<?=$ruta_raiz?>/js/pdf_js/web/viewer.js"></script>
    </head>
    <body>
        <div id="div_visor_pdf">
            <div id="outerContainer">

              <div id="sidebarContainer" style="display: none;">
                <div id="toolbarSidebar">
                  <div class="splitToolbarButton toggled">
                    <button id="viewThumbnail" class="toolbarButton group toggled" title="Show Thumbnails" tabindex="1" data-l10n-id="thumbs">
                       <span data-l10n-id="thumbs_label">Thumbnails</span>
                    </button>
                    <button id="viewOutline" class="toolbarButton group" title="Show Document Outline" tabindex="2" data-l10n-id="outline">
                       <span data-l10n-id="outline_label">Document Outline</span>
                    </button>
                  </div>
                </div>
                <div id="sidebarContent" style="display: none;">
                  <div id="thumbnailView">
                  </div>
                  <div id="outlineView" class="hidden">
                  </div>
                </div>
              </div>  <!-- sidebarContainer -->

              <div id="mainContainer">
                <div class="toolbar">
                  <div id="toolbarContainer">
                    <div id="toolbarViewer">
                      <div id="toolbarViewerLeft">
                            <div class="splitToolbarButton">
                          <button class="toolbarButton pageUp" title="Previous Page" id="previous" tabindex="5" data-l10n-id="previous">
                            <span data-l10n-id="previous_label">Previous</span>
                          </button>
                          <div class="splitToolbarButtonSeparator"></div>
                          <button class="toolbarButton pageDown" title="Next Page" id="next" tabindex="6" data-l10n-id="next">
                            <span data-l10n-id="next_label">Next</span>
                          </button>
                        </div>
                        <label id="pageNumberLabel" class="toolbarLabel" for="pageNumber" data-l10n-id="page_label">Page: </label>
                        <input type="number" id="pageNumber" class="toolbarField pageNumber" value="1" size="4" min="1" tabindex="7">
                        </input>
                        <span id="numPages" class="toolbarLabel"></span>
                          <div class="splitToolbarButton">
                            <button class="toolbarButton zoomOut" title="Zoom Out" tabindex="8" data-l10n-id="zoom_out">
                              <span data-l10n-id="zoom_out_label">Zoom Out</span>
                            </button>
                            <div class="splitToolbarButtonSeparator"></div>
                            <button class="toolbarButton zoomIn" title="Zoom In" tabindex="9" data-l10n-id="zoom_in">
                              <span data-l10n-id="zoom_in_label">Zoom In</span>
                             </button>
                          </div>
                          <span id="scaleSelectContainer" class="dropdownToolbarButton">
                             <select id="scaleSelect" title="Zoom" oncontextmenu="return false;" tabindex="10" data-l10n-id="zoom">
                              <option id="pageAutoOption" value="auto" selected="selected" data-l10n-id="page_scale_auto">Automatic Zoom</option>
                              <option id="pageActualOption" value="page-actual" data-l10n-id="page_scale_actual">Actual Size</option>
                              <option id="pageFitOption" value="page-fit" data-l10n-id="page_scale_fit">Fit Page</option>
                              <option id="pageWidthOption" value="page-width" data-l10n-id="page_scale_width">Full Width</option>
                              <option id="customScaleOption" value="custom"></option>
                              <option value="0.5">50%</option>
                              <option value="0.75">75%</option>
                              <option value="1">100%</option>
                              <option value="1.25">125%</option>
                              <option value="1.5">150%</option>
                              <option value="2">200%</option>
                            </select>
                          </span>
                          <menu type="context" id="viewerContextMenu">
                              <menuitem label="First Page" id="first_page"
                                        data-l10n-id="first_page" ></menuitem>
                              <menuitem label="Last Page" id="last_page"
                                        data-l10n-id="last_page" ></menuitem>
                              <menuitem label="Rotate Counter-Clockwise" id="page_rotate_ccw"
                                        data-l10n-id="page_rotate_ccw" ></menuitem>
                              <menuitem label="Rotate Clockwise" id="page_rotate_cw"
                                        data-l10n-id="page_rotate_cw" ></menuitem>
                          </menu>
                      </div>
                    </div>
                  </div>
                </div>

                <div id="viewerContainer" style="width: 100%">
                  <div id="viewer" contextmenu="viewerContextMenu"></div>
                </div>

                <div id="loadingBox">
                  <div id="loading"></div>
                  <div id="loadingBar"><div class="progress"></div></div>
                </div>

                <div id="errorWrapper" hidden='true'>
                  <div id="errorMessageLeft">
                    <span id="errorMessage"></span>
                    <button id="errorShowMore" onclick="" oncontextmenu="return false;" data-l10n-id="error_more_info">
                      More Information
                    </button>
                    <button id="errorShowLess" onclick="" oncontextmenu="return false;" data-l10n-id="error_less_info" hidden='true'>
                      Less Information
                    </button>
                  </div>
                  <div id="errorMessageRight">
                    <button id="errorClose" oncontextmenu="return false;" data-l10n-id="error_close">
                      Close
                    </button>
                  </div>
                  <div class="clearBoth"></div>
                  <textarea id="errorMoreInfo" hidden='true' readonly="readonly"></textarea>
                </div>
              </div> <!-- mainContainer -->

            </div> <!-- outerContainer -->
            <div id="printContainer"></div>
        </div>
    </body>
</html>