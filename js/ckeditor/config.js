/*
Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	 config.language = 'es';
         config.scayt_sLang = 'es_ES';
         config.scayt_autoStartup = false;
         config.disableNativeSpellChecker = false;
         config.enterMode = CKEDITOR.ENTER_BR;
         config.pasteFromWordRemoveFontStyles = true;
         config.pasteFromWordRemoveStyles = true;
         config.removeFormatTags = 'big,code,del,dfn,em,ins,kbd,q,samp,small,strike,tt,var,font,span';
         config.removeFormatAttributes = 'class,style';
         config.removePlugins = 'elementspath';
         config.toolbar = [ ['Undo','Redo','-','Cut','Copy','PasteText','PasteFromWord'],
            ['FontSize','Bold','Italic','Underline'],
            ['JustifyLeft','JustifyCenter','JustifyRight'], //'JustifyFull'
            ['NumberedList','BulletedList','-','Outdent','Indent'],
            ['SpellChecker','Find','Replace','SelectAll'], //,'Scayt'
            ['Table','HorizontalRule','SpecialChar','Font','TextColor','BGColor','Maximize','Source'] ];//,'Styles'
        
        config.height='300px';
	// config.uiColor = '#AADC6E';
};
