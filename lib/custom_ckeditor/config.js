/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'it';
	// config.uiColor = '#AADC6E';
	
	config.stylesSet = [
		// block
		{ name: 'block left', element : 'div', attributes: { 'class': 'left'} },
		{ name: 'block right', element : 'div', attributes: { 'class': 'right'} },
		{ name: 'left/right clear', element : 'p', attributes: { 'class': 'null'} },
		{ name: 'code', element:'div', attributes: { 'class': 'code'} },
		{ name: 'line', element : 'p', attributes: { 'class': 'line'} },
		{ name: 'dotted line', element : 'p', attributes: { 'class': 'line_dotted'} },
		{ name: 'title', element : 'p', attributes: { 'class': 'title'} },
		{ name: 'subtitle', element : 'p', attributes: { 'class': 'subtitle'} },
		// images
		{ name: 'image left', element : 'img', attributes: { 'class': 'left'} },
		{ name: 'image right', element : 'img', attributes: { 'class': 'right'} },
		// table
		{ name: 'table generic', element : 'table', attributes: { 'class': 'generic'} },
		// inline
		{ name: 'link', element : 'span', attributes: { 'class': 'link'} },
		{ name: 'evidence', element : 'span', attributes: { 'class': 'evidence'} },
		{ name: 'border top', element : 'span', attributes: { 'class': 'border_top'} },
		{ name: 'border bottom', element : 'span', attributes: { 'class': 'border_bottom'} },
		{ name: 'xx small', element : 'span', attributes: { 'class': 'xx-small'} },
		{ name: 'x small', element : 'span', attributes: { 'class': 'x-small'} },
		{ name: 'small', element : 'span', attributes: { 'class': 'small'} },
		{ name: 'normal', element : 'span', attributes: { 'class': 'normal'} },
		{ name: 'large', element : 'span', attributes: { 'class': 'large'} },
		{ name: 'x large', element : 'span', attributes: { 'class': 'x-large'} },
		{ name: 'xx large', element : 'span', attributes: { 'class': 'xx-large'} },

	];

	config.toolbar_Basic =
	[
	    { name: 'document',    items : [ 'Source' ] },
	    { name: 'tools',       items : [ 'Maximize', 'ShowBlocks','-','About' ] },
	    { name: 'clipboard',   items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
	    { name: 'insert',      items : [ 'SpecialChar' ] },
	    '/',
	    { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
	    { name: 'paragraph',   items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },
	    { name: 'links',       items : [ 'Link','Unlink','Anchor' ] },
	    '/',
	    { name: 'styles',      items : [ 'Styles','Format','Font','FontSize' ] },
	    { name: 'colors',      items : [ 'TextColor','BGColor' ] },
	];

	config.toolbar_Full =
	[
	    { name: 'document',    items : [ 'Source' ] },
	    { name: 'tools',       items : [ 'Maximize', 'ShowBlocks','-','About' ] },
	    { name: 'clipboard',   items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
	    { name: 'editing',     items : [ 'Find','Replace','-','SelectAll' ] },
	    { name: 'insert',      items : [ 'Image','Flash','Table', 'SpecialChar' ] },
	    '/',
	    { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
	    { name: 'paragraph',   items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
	    { name: 'links',       items : [ 'Link','Unlink','Anchor' ] },
	    '/',
	    { name: 'styles',      items : [ 'Styles','Format','Font','FontSize' ] },
	    { name: 'colors',      items : [ 'TextColor','BGColor' ] },
	];

    config.extraPlugins = 'imagebrowser';
    var base_href = $$('base')[0].get('href');
    config.imageBrowser_listUrl = base_href + 'attachment/jsonImageList';

};
