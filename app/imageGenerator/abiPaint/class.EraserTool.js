var eraserTool = new Class({
	initialize: function(canvas, dim) {
		this.canvas = canvas;
		this.dim = dim;
		this.ctx = this.canvas.getContext('2d');
		this.ctx.lineWidth = this.dim;
		this.draw = false;
	},
	activate: function() {
		this.canvas.addEvent('mousedown', this.start.bind(this));
		this.canvas.addEvent('mousemove', this.stroke.bind(this));
		this.canvas.addEvent('mouseup', this.stop.bind(this));
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
		this.ctx.clearRect(x-(this.dim/2).round(), y-(this.dim/2).round(), this.dim, this.dim);
	},
	stroke: function(evt) {
		if (this.draw) {
			var x,y;
			x = evt.page.x - $(this.canvas).getCoordinates().left;
			y = evt.page.y - $(this.canvas).getCoordinates().top;
			this.ctx.clearRect(x-(this.dim/2).round(), y-(this.dim/2).round(), this.dim, this.dim);
		}
	},
	stop: function(evt) {
		if (this.draw) {
			this.draw = false;
		}
	}
})

