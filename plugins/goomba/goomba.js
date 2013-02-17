function Goomba() {
	var self = this;
	this.goomba = document.createElement('div');
	$(this.goomba).addClass('goomba').click(function () {
		self.stomp()
	});
	$('body').append(this.goomba);
	if(Math.random() > 0.5)
	{
		this.position = $(document).width();
		this.direction = -1;
	}
	else
	{
		this.position = -15;
		this.direction = 1;
	}
	setInterval(function () {
		if(self.stomped) return;
		self.position += self.direction;
		if(self.position > $(document).width() || self.position < -16)
		{
			if(Math.random() > 0.5)
			{
				self.position = $(document).width();
				self.direction = -1;
			}
			else
			{
				self.position = -15;
				self.direction = 1;
			}
		}
		self.goomba.style.left = self.position + "px";
		self.step = (self.step + 1) % 2;
		self.goomba.style.backgroundPosition = "-" + (self.step * 16) + "px 0px";
	}, 100);
}
Goomba.prototype.stomp = function ()
{
	var self = this;
	if (this.stomped) return;
	this.stomped = true;
	this.goomba.style.backgroundPosition = "-32px 0px";
	this.audio.play();
	clearInterval(this.interval);
	setTimeout(function () {
		self.goomba.style.display = "none";
	}, 500);
};
Goomba.prototype.step = 0;
Goomba.prototype.audio = new Audio(resourceLink("plugins/goomba/goomba.ogg"));
