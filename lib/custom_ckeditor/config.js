/**
 * @license Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	
	config.removePlugins = 'forms';
	
	// Sovrascrive il file styles.js
	// config.stylesSet = [ ... ]
	
	config.addStylesSet = [
	    /*
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
		*/
	];
	
	var base_href = $$('base')[0].get('href');
	config.extraPlugins = 'imagebrowser';
	config.imageBrowser_listUrl = base_href + 'attachment/jsonImageList';
};
