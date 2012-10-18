<?php
/**
 * @file captchaImage.php
 * @brief Libreria per generare una immagine captcha
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

require_once('../configuration.php');
session_name(SESSION_NAME);
session_start();

header("Content-Type: image/png"); 	// or image/jpg

// custom parameters
$box_w 				= 162;			// Width of the captha box
$box_h 				= 40;			// Height of the captha box
$font 				= '../extra/arial.ttf';	// Used font
$font_size 			= 20; 			// Size of the font
$font_angle 		= 2; 			// Angle of text
$font_x 			= 10; 			// Margin left
$font_y 			= 10; 			// Margin top
$color_background 	= 'red'; 		// Bakground color: black, white, green, blu, red
$color_text 		= 'green';		// Text color: 		black, white, green, blu, red
$color_lines 		= 'green';		// Lines color:		black, white, green, blu, red
$thickness			= 3;			// Thickness of lines
$lines_angle		= 8;			// angle of lines (from 1 to 10)
$lines_number		= 6;			// numbers of lines

// set a passcode 
$pass = '';
$nchar = 7;							// number of characters in image
for($i=1;$i<=$nchar;$i++){
	$charOnumber = rand(1,2);
	if($charOnumber == 1){
		$chars = 'ABEFHKMNRSTVWX';	// custom used characters
		$n = strlen($chars)-1;
		$x = rand(1,$n);
		$char = substr($chars,$x,1);
		$pass .= $char;
	} else {
		//$number = rand(3,7);
		$numbers = array(1,2,3,4,7,9);	// custom used numbers
		$n = count($numbers)-1;
		$number = $numbers[rand(1,$n)];
		$pass .= $number;
	}
}

// set the session 
$_SESSION["pass"] = $pass;

// create the image resource
$image = ImageCreatetruecolor($box_w,$box_h);

// set colors
$white 	= ImageColorAllocate($image, 255, 255, 255);
$black 	= ImageColorAllocate($image, 0, 0, 0);
$green 	= ImageColorAllocate($image, 0, 255, 0);
$red 	= ImageColorAllocate($image, 255, 0, 0);
$blu 	= ImageColorAllocate($image, 0, 0, 255);

switch($color_background){
	case 'black':
	$color_background = $black;
	break;
	case 'white':
	$color_background = $white;
	break;
	case 'green':
	$color_background = $green;
	break;
	case 'blu':
	$color_background = $blu;
	break;
	case 'red':
	$color_background = $red;
	break;
	default:
	$color_background = $black;
}
switch($color_text){
	case 'black':
	$color_text = $black;
	break;
	case 'white':
	$color_text = $white;
	break;
	case 'green':
	$color_text = $green;
	break;
	case 'blu':
	$color_text = $blu;
	break;
	case 'red':
	$color_text = $red;
	break;
	default:
	$color_text = $black;
}
switch($color_lines){
	case 'black':
	$color_lines = $black;
	break;
	case 'white':
	$color_lines = $white;
	break;
	case 'green':
	$color_lines = $green;
	break;
	case 'blu':
	$color_lines = $blu;
	break;
	case 'red':
	$color_lines = $red;
	break;
	default:
	$color_lines = $white;
}

// set background 
imagefill($image, 0, 0, $color_background);

// set text 
imagettftext($image, $font_size, $font_angle, $font_x, $font_size + $font_y, $color_text, $font, $pass);

// set lines
imagesetthickness($image,$thickness);

$step = $box_w/$lines_number;

switch($lines_angle){
	case 1:
	$start 	= 5;
	$end	= 5;
	break;
	case 2:
	$start 	= 5;
	$end	= 10;
	break;
	case 3:
	$start 	= 5;
	$end	= 15;
	break;
	case 4:
	$start 	= 5;
	$end	= 20;
	break;
	case 5:
	$start 	= 5;
	$end	= 25;
	break;
	case 6:
	$start 	= 5;
	$end	= 30;
	break;
	case 7:
	$start 	= 5;
	$end	= 35;
	break;
	case 8:
	$start 	= 5;
	$end	= 40;
	break;
	case 9:
	$start 	= 5;
	$end	= 45;
	break;
	case 10:
	$start 	= 5;
	$end	= 50;
	break;
}

$a = $start;
$b = $end;

for($i=1;$i<=$lines_number;$i++){
	$l = $start;
	$l1 = $end;
	imageline($image, $l, 1, $l1, $box_h, $color_lines);
	$start = $a + ($step*$i-1);
	$end = $start + $b;
}

// created image 
imagejpeg($image);
imagedestroy($image);
?>
