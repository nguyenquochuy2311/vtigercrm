/**
 * @license Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	// vtiger editor configuration (carried over from the CKEditor 4.3.1 build this folder used to hold)
	config.removePlugins = 'save,maximize,magicline';
	config.fullPage = true;
	config.allowedContent = true;
	config.disableNativeSpellChecker = false;
	config.enterMode = CKEDITOR.ENTER_BR;
	config.shiftEnterMode = CKEDITOR.ENTER_P;
	config.autoParagraph = false;
	config.fillEmptyBlocks = false;
	config.filebrowserBrowseUrl = 'kcfinder/browse.php?type=images';
	config.filebrowserUploadUrl = 'kcfinder/upload.php?type=images';
	// 'wsc' (WebSpellChecker) is not part of CKEditor 4.22 any more, and 'scayt' needs a paid service: neither is loaded
	config.plugins = 'dialogui,dialog,docprops,about,a11yhelp,dialogadvtab,basicstyles,bidi,blockquote,clipboard,button,panelbutton,panel,floatpanel,colorbutton,colordialog,menu,contextmenu,div,resize,toolbar,elementspath,enterkey,entities,popup,filebrowser,find,fakeobjects,floatingspace,listblock,richcombo,font,format,horizontalrule,htmlwriter,wysiwygarea,image,indent,indentblock,indentlist,justify,link,list,liststyle,magicline,pagebreak,preview,removeformat,selectall,showborders,sourcearea,specialchar,menubutton,stylescombo,tab,table,tabletools,undo';
	config.toolbarGroups = [
		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ] },
		{ name: 'insert', groups: [ 'blocks' ] },
		{ name: 'links' },
		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
		'/',
		{ name: 'styles' },
		{ name: 'colors' },
		{ name: 'tools' },
		{ name: 'others' },
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] }, { name: 'align' },
		{ name: 'paragraph', groups: [ 'list', 'indent' ] }
	];

	// Phone: the full toolbar wraps into eight rows and pushes the writing area off the screen.
	// Keep the buttons people use for a short email; everything else stays on the desktop toolbar.
	if ( window.matchMedia && window.matchMedia( '(max-width: 767px)' ).matches ) {
		config.toolbar = [
			[ 'Undo', 'Redo' ],
			[ 'Bold', 'Italic', 'Underline' ],
			[ 'NumberedList', 'BulletedList' ],
			[ 'Link', 'Unlink' ],
			[ 'TextColor', 'FontSize' ],
			[ 'RemoveFormat', 'Source' ]
		];
		config.toolbarCanCollapse = false;
	}

	// CKEditor 4.19+ asks an external server whether a newer version exists and shows a notice in the editor: not wanted here
	config.versionCheck = false;

	// Add new custom font names in the array below. The font plugin sets its default list only after this file has run,
	// so the default list is written out here; otherwise the custom names would replace it.
	var customFonts = [ 'FreeStyle Script', 'Brush Script STD', 'Bradley Hand ITC', 'Vladimir Script' ];
	config.font_names = 'Arial/Arial, Helvetica, sans-serif;Comic Sans MS/Comic Sans MS, cursive;Courier New/Courier New, Courier, monospace;Georgia/Georgia, serif;Lucida Sans Unicode/Lucida Sans Unicode, Lucida Grande, sans-serif;Tahoma/Tahoma, Geneva, sans-serif;Times New Roman/Times New Roman, Times, serif;Trebuchet MS/Trebuchet MS, Helvetica, sans-serif;Verdana/Verdana, Geneva, sans-serif';
	for ( var i = 0; i < customFonts.length; i++ ) {
		config.font_names = config.font_names + ';' + customFonts[ i ];
	}
};
