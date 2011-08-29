var TextTool = new Class({
	initialize: function(canvas, color, dim, text) {

		this.canvas = canvas;
		this.text = text;
		this.color = color.hexToRgb();
		this.dim = dim;
		this.ctx = this.canvas.getContext('2d');
		this.ctx.fillStyle = this.color;
		this.ctx.lineWidth = 1;
		this.ctx.font = this.dim+'px Arial';
		this.draw = false;

	},
	activate: function() {
		this.canvas.addEvent('click', this.start.bind(this));
	},
	deactivate: function() {
		this.canvas.removeEvents('click');	
    	},
	start: function(evt) {
		this.draw = true;
		var x,y;
		x = evt.page.x - $(this.canvas).getCoordinates().left;
		y = evt.page.y - $(this.canvas).getCoordinates().top;
		this.ctx.fillText(this.text, x, y);
       	}

})

