const APfolderPATH = 'app/imageGenerator/abiPaint';
function include(jspath) {
	var script = new Element('script', {
		'src': jspath,
		'type': 'text/javascript'		
	})
	script.inject($$('head')[0]);
}
include(APfolderPATH+'/class.PencilTool.js');
include(APfolderPATH+'/class.StraightLineTool.js');
include(APfolderPATH+'/class.RectangleTool.js');
include(APfolderPATH+'/class.CircleTool.js');
include(APfolderPATH+'/class.TextTool.js');
include(APfolderPATH+'/class.EraserTool.js');

// include CSS
var APCSS = new Asset.css(APfolderPATH+'/abiPaint.css');

// tooltip
window.onload =  function() {
	var myTips = new Tips('.APico', {className: 'APtips'});
	var myTips2 = new Tips('.APtooltip', {className: 'APtips'});
}

// init variables
var pencildraw = straightLinedraw = eraserdraw = rectangledraw = filledRectangledraw = circledraw = filledCircledraw = textdraw = false;
var pencil, straightLine, eraser, rect, filledRect, circle, filledCircle, text;

// converts hex colors from string to array
function colorConverter(color) {
	return [color.substring(0,1), color.substring(2,3), color.substring(4,5)];
}

function selectButton(button) {
	if($$('img[class=APicoSel]')[0]) $$('img[class=APicoSel]')[0].setProperty('class', 'APico');
	button.setProperty('class', 'APicoSel');	
}

function unselectButton(button) {
	button.setProperty('class', 'APico');	
}

function togglePencil(el) {

	if(!pencildraw) {
		if(straightLinedraw) toggleStraightLine($('straightLineButton'));
		if(eraserdraw) toggleEraser($('eraserButton'));
		if(rectangledraw) toggleRectangle('rectangleButton');
		if(filledRectangledraw) toggleFilledRectangle('filledRectangleButton');
		if(circledraw) toggleCircle($('circleButton'));
		if(filledCircledraw) toggleFilledCircle($('filledCircleButton'));
		if(textdraw) toggleText($('textButton'));
		selectButton($(el));
		pencil = new PencilTool($('canvasArea'), colorConverter($('pencilColor').value), $('pencilDim').value);
		pencil.activate();
	}
	else {
		unselectButton($(el));
		pencil.deactivate();
	}
	pencildraw = !pencildraw;

}

function toggleStraightLine(el) {

	if(!straightLinedraw) {
		if(pencildraw) togglePencil($('pencilButton'));
		if(eraserdraw) toggleEraser($('eraserButton'));
		if(rectangledraw) toggleRectangle($('rectangleButton'));
		if(filledRectangledraw) toggleFilledRectangle('filledRectangleButton');
		if(circledraw) toggleCircle($('circleButton'));
		if(filledCircledraw) toggleFilledCircle($('filledCircleButton'));
		if(textdraw) toggleText($('textButton'));
		selectButton($(el));
		straightLine = new StraightLineTool($('canvasArea'), colorConverter($('pencilColor').value), $('pencilDim').value);
		straightLine.activate();
	}
	else {
		unselectButton($(el));
		straightLine.deactivate();
	}
	straightLinedraw = !straightLinedraw;

}

function toggleEraser(el) {

	if(!eraserdraw) {
		if(pencildraw) togglePencil($('pencilButton'));
		if(straightLinedraw) toggleStraightLine($('straightLineButton'));
		if(rectangledraw) toggleRectangle($('rectangleButton'));
		if(filledRectangledraw) toggleFilledRectangle($('filledRectangleButton'));
		if(circledraw) toggleCircle($('circleButton'));
		if(filledCircledraw) toggleFilledCircle($('filledCircleButton'));
		if(textdraw) toggleText($('textButton'));
		selectButton($(el));
		eraser = new eraserTool($('canvasArea'), $('pencilDim').value);
		eraser.activate();
	}
	else {
		unselectButton($(el));
		eraser.deactivate();
	}
	eraserdraw = !eraserdraw;

}


function toggleRectangle(el) {

	if(!rectangledraw) {
		if(pencildraw) togglePencil($('pencilButton'));
		if(straightLinedraw) toggleStraightLine($('straightLineButton'));
		if(eraserdraw) toggleEraser($('eraserButton'));
		if(filledRectangledraw) toggleFilledRectangle($('filledRectangleButton'));
		if(circledraw) toggleCircle($('circleButton'));
		if(filledCircledraw) toggleFilledCircle($('filledCircleButton'));
		if(textdraw) toggleText($('textButton'));
		selectButton($(el));
		rect = new RectangleTool($('canvasArea'), colorConverter($('pencilColor').value), $('pencilDim').value, false);
		rect.activate();
	}
	else {
		unselectButton($(el));
		rect.deactivate();
	}
	rectangledraw = !rectangledraw;

}

function toggleFilledRectangle(el) {

	if(!filledRectangledraw) {
		if(pencildraw) togglePencil($('pencilButton'));
		if(straightLinedraw) toggleStraightLine($('straightLineButton'));
		if(eraserdraw) toggleEraser($('eraserButton'));
		if(rectangledraw) toggleRectangle($('rectangleButton'));
		if(circledraw) toggleCircle($('circleButton'));
		if(filledCircledraw) toggleFilledCircle($('filledCircleButton'));
		if(textdraw) toggleText($('textButton'));
		selectButton($(el));
		filledRect = new RectangleTool($('canvasArea'), colorConverter($('pencilColor').value), $('pencilDim').value, true);
		filledRect.activate();
	}
	else {
		unselectButton($(el));
		filledRect.deactivate();
	}
	filledRectangledraw = !filledRectangledraw;

}

function toggleCircle(el) {

	if(!circledraw) {
		if(pencildraw) togglePencil($('pencilButton'));
		if(straightLinedraw) toggleStraightLine($('straightLineButton'));
		if(eraserdraw) toggleEraser($('eraserButton'));
		if(rectangledraw) toggleRectangle($('rectangleButton'));
		if(filledRectangledraw) toggleFilledRectangle($('filledRectangleButton'));
		if(filledCircledraw) toggleFilledCircle($('filledCircleButton'));
		if(textdraw) toggleText($('textButton'));
		selectButton($(el));
		circle = new CircleTool($('canvasArea'), colorConverter($('pencilColor').value), $('pencilDim').value, false);
		circle.activate();
	}
	else {
		unselectButton($(el));
		circle.deactivate();
	}
	circledraw = !circledraw;

}

function toggleFilledCircle(el) {

	if(!filledCircledraw) {
		if(pencildraw) togglePencil($('pencilButton'));
		if(straightLinedraw) toggleStraightLine($('straightLineButton'));
		if(eraserdraw) toggleEraser($('eraserButton'));
		if(rectangledraw) toggleRectangle($('rectangleButton'));
		if(filledRectangledraw) toggleFilledRectangle($('filledRectangleButton'));
		if(circledraw) toggleCircle($('circleButton'));
		if(textdraw) toggleText($('textButton'));
		selectButton($(el));
		filledCircle = new CircleTool($('canvasArea'), colorConverter($('pencilColor').value), $('pencilDim').value, true);
		filledCircle.activate();
	}
	else {
		unselectButton($(el));
		filledCircle.deactivate();
	}
	filledCircledraw = !filledCircledraw;

}

function toggleText(el) {

	if(!textdraw) {
		if(pencildraw) togglePencil($('pencilButton'));
		if(straightLinedraw) toggleStraightLine($('straightLineButton'));
		if(eraserdraw) toggleEraser($('eraserButton'));
		if(rectangledraw) toggleRectangle($('rectangleButton'));
		if(filledRectangledraw) toggleFilledRectangle($('filledRectangleButton'));
		if(circledraw) toggleCircle($('circleButton'));
		if(filledCircledraw) toggleFilledCircle($('filledCircleButton'));
		selectButton($(el));
		text = new TextTool($('canvasArea'), colorConverter($('pencilColor').value), $('pencilDim').value, $('textContent').value);
		text.activate();
	}
	else {
		unselectButton($(el));
		text.deactivate();
	}
	textdraw = !textdraw;

}

function updateDim(value) {
	if(pencil) {
		pencil.ctx.lineWidth = value;
		pencil.dim = value;
	}
	if(straightLine) {
		straightLine.ctx.lineWidth = value;
		straightLine.dim = value;
	}
	if(rect) {
		rect.ctx.lineWidth = value;
		rect.dim = value;
	}
	if(filledRect) {
		filledRect.ctx.lineWidth = value;
		filledRect.dim = value;
	}
	if(circle) {
		circle.ctx.lineWidth = value;
		circle.dim = value;
	}
	if(filledCircle) {
		filledCircle.ctx.lineWidth = value;
		filledCircle.dim = value;
	}
	if(text) {
		text.ctx.font = value+'px Arial';
		text.dim = value;
	}
	if(eraser) {
		eraser.ctx.lineWidth = value;
		eraser.dim = value;
	}


}

function updateColor(value) {
	if(pencil) {
		pencil.ctx.strokeStyle = colorConverter(value).hexToRgb();
		pencil.ctx.fillStyle = colorConverter(value).hexToRgb();
	}
	if(straightLine) {
		straightLine.ctx.strokeStyle = colorConverter(value).hexToRgb();
		straightLine.ctx.fillStyle = colorConverter(value).hexToRgb();
	}
	if(rect) {
		rect.ctx.strokeStyle = colorConverter(value).hexToRgb();
		rect.ctx.fillStyle = colorConverter(value).hexToRgb();
	}
	if(filledRect) {
		filledRect.ctx.strokeStyle = colorConverter(value).hexToRgb();
		filledRect.ctx.fillStyle = colorConverter(value).hexToRgb();
	}
	if(circle) {
		circle.ctx.strokeStyle = colorConverter(value).hexToRgb();
		circle.ctx.fillStyle = colorConverter(value).hexToRgb();
	}
	if(filledCircle) {
		filledCircle.ctx.strokeStyle = colorConverter(value).hexToRgb();
		filledCircle.ctx.fillStyle = colorConverter(value).hexToRgb();
	}
	if(text) {
		text.ctx.strokeStyle = colorConverter(value).hexToRgb();
		text.ctx.fillStyle = colorConverter(value).hexToRgb();
	}

}

function updateText(value) {
	if(text) {
		text.text = value;
	}
}

function saveImage(canvas){
	ajaxRequest('post', 'index.php?pt[imageGenerator-saveImage]', 'data='+canvas.toDataURL(), 'APdownload');
}

function clearStage(canvas) {
	var ctx = canvas.getContext('2d');
	ctx.clearRect(0, 0, canvas.width, canvas.height);
}

function initImage(canvas, imageSrc) {
	var img = new Image();
	img.src = imageSrc;
	img.onload = function() {
		var ctx = canvas.getContext('2d');
		ctx.drawImage(img, 0, 0);
	}

}

