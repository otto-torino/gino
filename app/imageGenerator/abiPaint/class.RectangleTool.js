var RectangleTool = new Class({
	initialize: function(canvas, color, dim, fill) {
		this.canvas = canvas;
		this.color = color.hexToRgb();
		this.dim = dim;
		this.fill = fill;
		this.ctx = this.canvas.getContext('2d');
		this.ctx.strokeStyle = this.color;
		this.ctx.fillStyle = this.color;
		this.ctx.lineWidth = this.dim;
		this.draw = false;
	},
	activate: function() {
		this.canvas.addEvent('mousedown', this.start.bind(this));
		this.canvas.addEvent('mouseup', this.stroke.bind(this));
		this.canvas.addEvent('mouseout', this.stop.bind(this));	
	},
	deactivate: function() {
		this.canvas.removeEvents('mousedown', 'mousemove', 'mouseup', 'mouseout');	
	},
	start: function(evt) {
		this.draw = true;
		var x,y;
		x = evt.page.x - $(this.canvas).getCoordinates().left;
		y = evt.page.y - $(this.canvas).getCoordinates().top;
		this.ctx.beginPath();
		this.ctx.moveTo(x, y);
		this.initX = x;
		this.initY = y;
	},
	stroke: function(evt) {
		if(this.draw) {
			var x,y;
			x = evt.page.x - $(this.canvas).getCoordinates().left;
			y = evt.page.y - $(this.canvas).getCoordinates().top;
			if(this.fill) this.ctx.fillRect(this.initX, this.initY, (x-this.initX), (y-this.initY));
			else this.ctx.strokeRect(this.initX, this.initY, (x-this.initX), (y-this.initY));
		}
	},
	stop: function(evt) {
		if (this.draw) {
			this.draw = false;
		}
	}
})

