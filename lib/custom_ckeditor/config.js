/**
 * @license Copyright (c) 2003-2016, CKSource - Frederico Knabben. All rights reserved.
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
	    
	];
	
	var base_href = $$('base')[0].get('href');
	var file_path = 'lib/plugin/filemanager/plugin.ckeditor.php';
	
	// prev version
	//config.extraPlugins = 'imagebrowser';
	//config.imageBrowser_listUrl = base_href + 'attachment/jsonImageList';
	
	config.extraPlugins = 'filebrowser';
	
	config.filebrowserBrowseUrl = base_href + file_path + '?type=file&action=browse';
    config.filebrowserImageBrowseUrl = base_href + file_path + '?type=image&action=browse';
    config.filebrowserFlashBrowseUrl = base_href + file_path + '?type=flash&action=browse';
    config.filebrowserMediaBrowseUrl = base_href + file_path + '?type=media&action=browse';
    
    config.filebrowserUploadUrl = base_href + file_path + '?type=file&action=upload';
    config.filebrowserImageUploadUrl = base_href + file_path + '?type=image&action=upload';
    config.filebrowserFlashUploadUrl = base_href + file_path + '?type=flash&action=upload';
    config.filebrowserMediaUploadUrl = base_href + file_path + '?type=media&action=upload';
};
