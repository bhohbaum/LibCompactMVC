//*****************************************************************************************************
// Slideshow
//*****************************************************************************************************
function Slideshow(div1, div2, images, interval) {
	this.timer = null;
	this.interval = 5000;
	this.div1 = div1;
	this.div2 = div2;
	this.toggle = false;
	this.randomize = false;
	if (interval != undefined)
		this.interval = interval;
	if (images != undefined) {
		this.images = images;
		this.pos = Math.round(Math.random() * (images.length - 1));
	}
}

Slideshow.prototype.stop = function() {
	if (this.timer != null)
		clearInterval(this.timer);
	this.timer = null;
}

Slideshow.prototype.start = function(interval, randomize) {
	var self = this;
	this.stop();
	if (interval != undefined)
		this.interval = interval;
	if (randomize == undefined)
		randomize = true;
	this.randomize = randomize;
	clearTimeout(this.timer);
	self.step(randomize);
}

Slideshow.prototype.pause = function() {
	this.stop();
}

Slideshow.prototype.cont = function() {
	this.start(this.interval, this.randomize);
}

Slideshow.prototype.set_images = function(images, randomize) {
	var was_running = (this.timer != null);
	if (randomize == undefined)
		randomize = true;
	this.randomize = randomize;
	this.stop();
	this.images = [];
	var i = images.length;
	while (i--) this.images[i] = images[i];
	this.size = this.images.length;
	if (this.randomize) {
		this.pos = Math.round(Math.random() * (this.size - 1));
		this.shuffle();
	} else {
		this.pos = 0;
	}
	if (was_running)
		this.start(randomize);
}

Slideshow.prototype.set_interval = function(interval) {
	var was_running = (this.timer != null);
	this.stop();
	this.interval = interval;
	this.pos = Math.round(Math.random() * (images.length - 1));
	if (was_running)
		this.start();
}

Slideshow.prototype.step = function(randomize) {
	var self = this;
	var factor = 1;
	if (randomize != undefined)
		if (randomize)
			factor = Math.random() * 1.5;
	this.randomize = randomize;
	this.timer = setTimeout(function() {
		self.step();
	}, self.interval * factor);
	if (this.images.length == 0) {
		$(this.div1).css("background", "black");
		$(this.div2).css("background", "black");
		return;
	}
	if (this.pos >= this.images.length)
		this.pos = 0;
	if (this.images[this.pos] != undefined) {
		if (this.toggle) {
			$(this.div1).css("background", "url('/assets/img/" + this.images[this.pos].name + "')");
			$(this.div1).stop().animate({
				opacity: 1
			}, 1500);
			$(this.div2).stop().animate({
				opacity: 0
			}, 1500);
			this.toggle = false;
		} else {
			$(this.div2).css("background", "url('/assets/img/" + this.images[this.pos].name + "')");
			$(this.div1).stop().animate({
				opacity: 0
			}, 1500);
			$(this.div2).stop().animate({
				opacity: 1
			}, 1500);
			this.toggle = true;
		}
	}
	this.pos++;
}

Slideshow.prototype.shuffle = function() {
	var currentIndex = this.images.length, temporaryValue, randomIndex;
	while (0 !== currentIndex) {
		randomIndex = Math.floor(Math.random() * currentIndex);
		currentIndex -= 1;
		temporaryValue = this.images[currentIndex];
		this.images[currentIndex] = this.images[randomIndex];
		this.images[randomIndex] = temporaryValue;
	}
}
