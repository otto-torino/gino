/**
 * @license Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	
	// %REMOVE_START%
	// The configuration options below are needed when running CKEditor from source files.
	config.plugins = 'dialogui,dialog,about,a11yhelp,dialogadvtab,basicstyles,bidi,blockquote,notification,button,toolbar,clipboard,panelbutton,panel,floatpanel,colorbutton,colordialog,templates,menu,contextmenu,copyformatting,div,resize,elementspath,enterkey,entities,popup,filetools,filebrowser,find,fakeobjects,flash,floatingspace,listblock,richcombo,font,forms,format,horizontalrule,htmlwriter,iframe,wysiwygarea,image,indent,indentblock,indentlist,smiley,justify,menubutton,language,link,list,liststyle,magicline,maximize,newpage,pagebreak,pastetext,pastefromword,preview,print,removeformat,save,selectall,showblocks,showborders,sourcearea,specialchar,scayt,stylescombo,tab,table,tabletools,tableselection,undo,lineutils,widgetselection,widget,notificationaggregator,uploadwidget,uploadimage,wsc';
	config.skin = 'moono-lisa';
	// %REMOVE_END%

	// Define changes to default configuration here. For example:
	// config.language = 'it';
	// config.uiColor = '#AADC6E';
	
	config.removePlugins = 'forms';
	// This plugin displays tooltips with information about editor dialog windows (it is useful in the development phase)
	config.extraPlugins = 'devtools';
	
	// Sovrascrive il file styles.js
	config.stylesSet = 'bootstrap_rules';
	
	config.addStylesSet = [
	    
	];
	
	// If you want to skip loading the styles that are used in CKEditor by default, you may set the CKEDITOR.config.stylesSet option 
    // to an empty value:
    // config.stylesSet = [];
	
	// to open modal
	config.extraAllowedContent = 'a[data-type,data-title,data-footer,data-overlay,data-buttons,data-body,data-esc-close,data-any-close,data-class,data-event-open,data-event-close]';
	
	// Customization
	var base_href = $('base').attr('href');
	var file_path = 'lib/plugin/filemanager/plugin.ckeditor.php';
	config.baseHref = base_href;
	
	// The config.filebrowserBrowseUrl setting contains the location of an external file browser 
	// that should be launched when the Browse Server button is pressed.
	// The config.filebrowserUploadUrl setting contains the location of a script that handles file uploads. 
	// If set, the Upload tab will appear in some dialog windows — the ones where such functionality is available, 
	// i.e. Link, Image and Flash Properties.
	config.filebrowserBrowseUrl = base_href + file_path + '?type=file&action=browse';
    config.filebrowserImageBrowseUrl = base_href + file_path + '?type=image&action=browse';
    config.filebrowserFlashBrowseUrl = base_href + file_path + '?type=flash&action=browse';
    config.filebrowserMediaBrowseUrl = base_href + file_path + '?type=media&action=browse';
    
    config.filebrowserUploadMethod = 'form';
    
    config.filebrowserUploadUrl = base_href + file_path + '?type=file&action=upload';
    config.filebrowserImageUploadUrl = base_href + file_path + '?type=image&action=upload';
    config.filebrowserFlashUploadUrl = base_href + file_path + '?type=flash&action=upload';
    config.filebrowserMediaUploadUrl = base_href + file_path + '?type=media&action=upload';
};

CKEDITOR.stylesSet.add( 'bootstrap_rules', [
	//Block-level styles.
	{ name: 'Container responsive per tabella', element: 'div', attributes: { 'class': 'table-responsive' } },
	{ name: 'Video responsive', element: 'div', attributes: { 'class': 'video-wrapper' } },
	{ name: 'Container centrato', element: 'div', attributes: { 'class': 'center-block' } },
	{ name: 'Paragrafo in risalto', element: 'p', attributes: { 'class': 'lead' } },
	{ name: 'Citazione', element: 'p', attributes: { 'class': 'blockquote' } },
	{ name: 'Lista non stilizzata', element: 'ul', attributes: { 'class': 'list-unstyled' } },
	{ name: 'Lista breadcrumb', element: 'ol', attributes: { 'class': 'breadcrumb' } },
	{ name: 'Tbl righe', element: 'table', attributes: { 'class': 'table-striped' } },
	{ name: 'Tbl bordo', element: 'table', attributes: { 'class': 'table-bordered' } },
	{ name: 'Tbl hover', element: 'table', attributes: { 'class': 'table-hover' } },
	{ name: 'Tr active', element: 'tr', attributes: { 'class': 'table-active' } },
	{ name: 'Tr info', element: 'tr', attributes: { 'class': 'table-info' } },
	{ name: 'Tr succes', element: 'tr', attributes: { 'class': 'table-success' } },
	{ name: 'Tr warning', element: 'tr', attributes: { 'class': 'table-warning' } },
	{ name: 'Tr danger', element: 'tr', attributes: { 'class': 'table-danger' } },
	{ name: 'Alert info', element: 'p', attributes: { 'class': 'alert alert-info' } },
	{ name: 'Alert success', element: 'p', attributes: { 'class': 'alert alert-success' } },
	{ name: 'Alert warning', element: 'p', attributes: { 'class': 'alert alert-warning' } },
	{ name: 'Alert danger', element: 'p', attributes: { 'class': 'alert alert-danger' } },
	{ name: 'Txt info', element: 'p', attributes: { 'class': 'text-info' } },
	{ name: 'Txt muted', element: 'p', attributes: { 'class': 'text-muted' } },
	{ name: 'Txt success', element: 'p', attributes: { 'class': 'text-success' } },
	{ name: 'Txt primary', element: 'p', attributes: { 'class': 'text-primary' } },
	{ name: 'Txt warning', element: 'p', attributes: { 'class': 'text-warning' } },
	{ name: 'Txt danger', element: 'p', attributes: { 'class': 'text-danger' } },
	{ name: 'Bg info', element: 'p', attributes: { 'class': 'bg-info' } },
	{ name: 'Bg muted', element: 'p', attributes: { 'class': 'bg-muted' } },
	{ name: 'Bg success', element: 'p', attributes: { 'class': 'bg-success' } },
	{ name: 'Bg primary', element: 'p', attributes: { 'class': 'bg-primary' } },
	{ name: 'Bg warning', element: 'p', attributes: { 'class': 'bg-warning' } },
	{ name: 'Bg danger', element: 'p', attributes: { 'class': 'bg-danger' } },

	// Inline styles.
	{ name: 'Txt evidenziato', element: 'mark', attributes: { 'class': '' } },
	{ name: 'Img responsive', element: 'img', attributes: { 'class': 'img-responsive' } },
	{ name: 'Img arrotondata', element: 'img', attributes: { 'class': 'img-rounded' } },
	{ name: 'Img circolare', element: 'img', attributes: { 'class': 'img-circle' } },
	{ name: 'Thumbnail', element: 'img', attributes: { 'class': 'img-thumbnail' } },
	{ name: 'Btn primario', element: 'a', attributes: { 'class': 'btn btn-primary' } },
	{ name: 'Btn secondario', element: 'a', attributes: { 'class': 'btn btn-secondary' } },
	{ name: 'Btn info', element: 'a', attributes: { 'class': 'btn btn-info' } },
	{ name: 'Btn success', element: 'a', attributes: { 'class': 'btn btn-success' } },
	{ name: 'Btn warning', element: 'a', attributes: { 'class': 'btn btn-warning' } },
	{ name: 'Btn danger', element: 'a', attributes: { 'class': 'btn btn-danger' } },
	{ name: 'Alert link', element: 'a', attributes: { 'class': 'alert-link' } },
	
	{ name: 'Big',				element: 'big' },
	{ name: 'Small',			element: 'small' },
	{ name: 'Typewriter',		element: 'tt' },
	{ name: 'Keyboard Phrase',	element: 'kbd' },
	{ name: 'Sample Text',		element: 'samp' },
	{ name: 'Deleted Text',		element: 'del' },
	{ name: 'Inserted Text',	element: 'ins' },
	{ name: 'Cited Work',		element: 'cite' },
	{ name: 'Inline Quotation',	element: 'q' },
	{ name: 'Language: LTR',	element: 'span', attributes: { 'dir': 'ltr' } },
	
	// Object styles
	{ name: 'Square Bulleted List',	element: 'ul',		styles: { 'list-style-type': 'square' } },
                                        	
]);

