<?php

define("APfolderPATH", "app/imageGenerator/abiPaint"); 
define("APicoPATH", APfolderPATH."/img");
define("APsfPATH", CONTENT_WWW."/imageGenerator");
define("APsfPATHDIR", CONTENT_DIR.OS."imageGenerator");

class abiPaint extends AbstractEvtClass {

	public $lineButton, $pencilButton, $eraserButton, $rectangleButton, $filledRectangleButton, $circleButton, $filledCircleButton, $textButton;

	private $_canvas_width, $_canvas_height;

	function __construct() {
	
		parent::__construct();

		$this->_canvas_width = 561;
		$this->_canvas_height = 400;

		$this->lineButton = "<img src=\"".APicoPATH."/ico_straightLine.gif\" id=\"straightLineButton\" class=\"APico\" title=\"<b>Straight line tool</b><br/>Click on the stage where you want one of the line’s endpoints to be, then drag over to where you want the other endpoint to be and relase the button.\" onclick=\"toggleStraightLine(this)\"/>";
		$this->pencilButton = "<img src=\"".APicoPATH."/ico_pencil.gif\" id=\"pencilButton\" class=\"APico\" title=\"<b>Pencil tool</b><br/>Click on the stage to begin painting drag to continue and release the mouse button to stop painting\" onclick=\"togglePencil(this)\"/>";
		$this->eraserButton = "<img src=\"".APicoPATH."/ico_eraser.gif\" id=\"eraserButton\" class=\"APico\" title=\"<b>Eraser tool</b><br/>click and drag over the area of an image to be erased\" onclick=\"toggleEraser(this)\"/>";
		$this->rectangleButton = "<img src=\"".APicoPATH."/ico_rectangle.gif\" id=\"rectangleButton\" class=\"APico\" title=\"<b>Rectangle tool</b><br/>Click on the stage where you want one vertex to be, then drag over to where you want the opposite vertex to be and relase the button.\" onclick=\"toggleRectangle(this)\"/>";
		$this->filledRectangleButton = "<img src=\"".APicoPATH."/ico_filledRectangle.gif\" id=\"filledRectangleButton\" title=\"<b>Filled rectangle tool</b><br/>Click on the stage where you want one vertex to be, then drag over to where you want the opposite vertex to be and relase the button.\" class=\"APico\" onclick=\"toggleFilledRectangle(this)\" />";
		$this->circleButton = "<img src=\"".APicoPATH."/ico_circle.gif\" id=\"circleButton\" class=\"APico\" title=\"<b>Circle tool</b><br/>Click on the stage where you want the center to be, then drag over to define the radius and relase the button.\" onclick=\"toggleCircle(this)\"/>";
		$this->filledCircleButton = "<img src=\"".APicoPATH."/ico_filledCircle.gif\" id=\"filledCircleButton\" class=\"APico\" title=\"<b>Filled circle tool</b><br/>Click on the stage where you want the center to be, then drag over to define the radius and relase the button\" onclick=\"toggleFilledCircle(this)\"/>";
		$this->textButton = "<img src=\"".APicoPATH."/ico_text.gif\" id=\"textButton\" class=\"APico\" title=\"<b>Text tool</b><br/>Click on the stage where you want the text to be. Put the desired text in the field on the right\" onclick=\"toggleText(this)\"/>";
	
	}

	public function render($imageSrc=null) {

		$buffer = "<script type=\"text/javascript\" src=\"".APfolderPATH."/abiPaint.js\"></script>";

		$buffer .= "<div id=\"abiPaintContainer\">";
		$buffer .= "<div id=\"APdownload\">";
		$buffer .= "</div>";
		$buffer .= "<table>";
		$buffer .= "<tr class=\"iconRow\">";
		$buffer .= "<td>".$this->lineButton."</td>";
		$buffer .= "<td>".$this->pencilButton."</td>";
		$buffer .= "<td class=\"preSeparator\">".$this->eraserButton."</td>";
		$buffer .= "<td class=\"postSeparator\">".$this->rectangleButton."</td>";
		$buffer .= "<td>".$this->filledRectangleButton."</td>";
		$buffer .= "<td>".$this->circleButton."</td>";
		$buffer .= "<td class=\"preSeparator\">".$this->filledCircleButton."</td>";
		$buffer .= "<td class=\"postSeparator\">".$this->textButton."</td>";
	        $buffer .= "<td class=\"preSeparator\"><input id=\"textContent\" type=\"text\" size=\"8\" onBlur=\"updateText(this.value)\"/></td>";
		$buffer .= "<td class=\"postSeparator\"><select id=\"pencilDim\" class=\"APtooltip\" title=\"Pencil dimensions in px. Text font dimension in case of Text tool\" onchange=\"updateDim(this.value)\">";
		for($i=1;$i<51;$i++) $buffer .= "<option value=\"$i\">$i</option>"; 
		for($i=60;$i<101;$i=$i+10) $buffer .= "<option value=\"$i\">$i</option>"; 
		$buffer .= "</select></td>";
		$buffer .= "<td class=\"preSeparator\"><span class=\"sharp\">#</span> <input id=\"pencilColor\" class=\"APtooltip\" title=\"Pencil color in hexadecimal format\" type=\"text\" size=\"6\" maxlength=\"6\" value=\"33ccff\" onblur=\"updateColor(this.value)\"/></td>";
		$buffer .= "<td class=\"postSeparator\"><input id=\"clearStageButton\" class=\"APtooltip\" title=\"Click to clear the stage\" type=\"button\" value=\"clear\" onclick=\"clearStage($('canvasArea'))\"/></td>";
		//$buffer .= "<td><input id=\"saveImageButton\" class=\"APtooltip\" title=\"Click to save the image on the system and get the link to download the file appearing above\" type=\"button\" value=\"save\" onclick=\"saveImage($('canvasArea'))\"/></td>";
		$buffer .= "</tr>";
		$buffer .= "<tr>";
		$buffer .= "<td class=\"canvasCell\" colspan=\"13\">";
		$buffer .= "<canvas id=\"canvasArea\" height=\"".$this->_canvas_height."\" width=\"".$this->_canvas_width."\">";
		$buffer .= "Your browser can't display canvas, update it or change it if you have IE.";
		$buffer .= "</canvas>";
		$buffer .= "</td>";
		$buffer .= "</tr>";
		$buffer .= "</table>";
		$buffer .= "</div>";

		if($imageSrc) $buffer .= "<script>initImage($('canvasArea'), '$imageSrc')</script>";
		
		return $buffer;

	}

	public function saveImage($name) {

		$data = $_POST['imageCode'];

		$file = APsfPATHDIR."/".$name.".png";

		$data = preg_replace("/data:image\/png;base64,/i", "", $data);
		$data = str_replace(" ", "+", $data);
		$data = base64_decode($data);
		
		$fo = fopen($file, 'w');
		fwrite($fo, $data);
		fclose($fo);
		//echo "Download: <a target=\"_blank\" href=\"$this->_home?evt[$this->_className-download]\" onclick=\"$('APdownload').set('text', '');\">image.png</a>";exit;
	}


	public function download() {
	
		download(APsfPATHDIR."/image.png");

	}


}

?>
