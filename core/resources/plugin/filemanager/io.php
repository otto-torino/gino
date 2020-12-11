<?php
/**
 * @file io.php
 * @brief Input/output functions
 *
 * @copyright 2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.Plugin.Filemanager
 * @description Namespace che comprende classi di tipo plugin.filemanager
 */
namespace Gino\Plugin\Filemanager;

function clean_int($value) {

	if(is_null($value) or $value == '') {
		return null;
	}
	settype($value, 'int');

	return $value;
}

function clean_text($value) {

	if($value === null) {
		return null;
	}

	$value = trim($value);
	settype($value, 'string');
	$value = strip_tags($value);

	return $value;
}

/**
 * Returns a safe filename by replacing all dangerous characters with an underscore
 * 
 * @param string $newFilename The source filename to be "sanitized"
 * @param boolean single_extension Force single extension (dot)
 * @return string A safe version of the input filename
 */
function SanitizeFileName($newFilename, $single_extension = true) {

	$newFilename = stripslashes($newFilename);
	
	if($single_extension) {
		$newFilename = preg_replace('/\\.(?![^.]*$)/', '_', $newFilename);
	}
	
	// Remove \ / | : ? * " < >
	$newFilename = preg_replace('/\\\\|\\/|\\||\\:|\\?|\\*|"|<|>|[[:cntrl:]]/', '_', $newFilename);
	
	return $newFilename;
}

// Do a cleanup of the folder name to avoid possible problems
function SanitizeFolderName($newFolderName) {
	
	$newFolderName = stripslashes($newFolderName);

	// Remove . \ / | : ? * " < >
	$newFolderName = preg_replace('/\\.|\\\\|\\/|\\||\\:|\\?|\\*|"|<|>|[[:cntrl:]]/', '_', $newFolderName) ;

	return $newFolderName;
}

function IsAllowedExt($sExtension, $allowed, $denied) {
	
	if (count($allowed) > 0 && !in_array($sExtension, $allowed)) {
		return false;
	}

	if (count($denied) > 0 && in_array($sExtension, $denied)) {
		return false;
	}

	return true;
}

function IsAllowedType($resourceType, $allowedTypes) {
	
	if (!in_array($resourceType, $allowedTypes)) {
		return false;
	}

	return true;
}

function CombinePaths($sBasePath, $sFolder) {
	
	return RemoveFromEnd($sBasePath, '/') . '/' . RemoveFromStart($sFolder, '/');
}

/**
 * This is the function that sends the results of the uploading process
 * 
 * @param integer $errorNumber
 * @param string $fileUrl
 * @param string $fileName
 * @param string $customMsg
 */
function SendUploadResults($errorNumber, $fileUrl = '', $fileName = '', $customMsg = '')
{
	// Minified version of the document.domain automatic fix script (#1919).
	// The original script can be found at _dev/domain_fix_template.js
	echo <<<EOF
<script type="text/javascript">
(function(){var d=document.domain;while (true){try{var A=window.parent.document.domain;break;}catch(e) {};d=d.replace(/.*?(?:\.|$)/,'');if (d.length==0) break;try{document.domain=d;}catch (e){break;}}})();
EOF;

	if($errorNumber && $errorNumber != 201) {
		$fileUrl = "";
		$fileName = "";
	}

	$rpl = array( '\\' => '\\\\', '"' => '\\"' );
	echo 'window.parent.OnUploadCompleted(' . $errorNumber . ',"' . strtr( $fileUrl, $rpl ) . '","' . strtr( $fileName, $rpl ) . '", "' . strtr( $customMsg, $rpl ) . '") ;' ;
	echo '</script>';
	exit;
}

/**
 * This is the function that sends the results of the uploading process to CKEditor
 * 
 * @param integer $errorNumber
 * @param string $CKECallback
 * @param string $fileUrl
 * @param string $fileName
 * @param string $customMsg
 */
function SendCKEditorResults($errorNumber, $CKECallback, $fileUrl, $fileName, $customMsg ='')
{
	// Minified version of the document.domain automatic fix script (#1919).
	// The original script can be found at _dev/domain_fix_template.js
	echo <<<EOF
<script type="text/javascript">
(function(){var d=document.domain;while (true){try{var A=window.parent.document.domain;break;}catch(e) {};d=d.replace(/.*?(?:\.|$)/,'');if (d.length==0) break;try{document.domain=d;}catch (e){break;}}})();
EOF;

	if ($errorNumber && $errorNumber != 201) {
		$fileUrl = "";
		$fileName= "";
	}

	$msg = "";

	switch ($errorNumber)
	{
		case 0 :
			$msg = "Upload successful";
			break;
		case 1 :	// Custom error.
			$msg = $customMsg;
			break;
		case 201 :
			$msg = 'A file with the same name is already available. The uploaded file has been renamed to "' . $fileName . '"';
			break;
		case 202 :
			$msg = 'Invalid file';
			break;
		default :
			$msg = 'Error on file upload. Error number: ' + $errorNumber;
			break;
	}

	$rpl = array( '\\' => '\\\\', '"' => '\\"' );
	echo 'window.parent.CKEDITOR.tools.callFunction("'. $CKECallback. '","'. strtr($fileUrl, $rpl). '", "'. strtr( $msg, $rpl). '");';
	echo '</script>';
}

?>
