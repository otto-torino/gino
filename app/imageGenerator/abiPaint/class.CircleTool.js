var CircleTool = new Class({
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
		this.canvas.removeEvents('mousedown', 'mouseup', 'mouseout');	
	},
	start: function(evt) {
		this.draw = true;
		var x,y;
		x = evt.page.x - $(this.canvas).getCoordinates().left;
		y = evt.page.y - $(this.canvas).getCoordinates().top;
		this.ctx.beginPath();
		this.initX = x;
		this.initY = y;
	},
	stroke: function(evt) {
		if(this.draw) {
			var x,y;
			x = evt.page.x - $(this.canvas).getCoordinates().left;
			y = evt.page.y - $(this.canvas).getCoordinates().top;
			var r = Math.sqrt(Math.pow((x-this.initX),2)+Math.pow((y-this.initY),2));
			this.ctx.arc(this.initX, this.initY, r, 0, 2*Math.PI, true);
			if(this.fill) this.ctx.fill();
			else this.ctx.stroke();
			this.stop();
		}
	},
	stop: function(evt) {
		if (this.draw) {
			this.draw = false;
		}
	}
})

