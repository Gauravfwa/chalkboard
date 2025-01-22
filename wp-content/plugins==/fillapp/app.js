var PaintApp = (function() {

	var backgrounds = ["1.png"];

	var c;

	var ctx;

	var bgImg;

	var layerData;

	var fillColor = { r: 106, g: 199, b: 185, a: 255 };



	window.onload = setTimeout(function() {
		init();
	}, 50);

	

	function init() {

		var resetTimer;

		c = document.getElementById("drawing-canvas");

		ctx = c.getContext("2d");

		
		var w = window.innerWidth;
		if (w > 700) w = 700;
		c.width = w;
		c.height = (w / 7) * 9;

		

		window.addEventListener('resize', function(e) {

			var w = window.innerWidth;
			if (w > 700) w = 700;
			c.width = w;

			c.height = (w / 7) * 9;

			clear();

		});



		var randBg = Math.floor(Math.random() * 0);

		bgImg = new Image();

		bgImg.onload = function (e) {

			// BG Image done loading.

			ctx.drawImage(bgImg, 0, 0, c.width, c.height);

			layerData = ctx.getImageData(0, 0, c.width, c.height);

			

		}

		bgImg.src = "/wp-content/plugins/fillapp/images/" + backgrounds[randBg];

		

		c.addEventListener("mousedown", function(e) {
			if (e.button == 0) {
					
				
				fillAt(e.offsetX, e.offsetY);

				

				if(resetTimer)

					clearTimeout(resetTimer);

				resetTimer = setTimeout(clear, 120000);
			}

		});

		

		c.addEventListener("touchend", function(e) {

			fillAt(e.offsetX, e.offsetY);

			

			if(resetTimer)

				clearTimeout(resetTimer);

			resetTimer = setTimeout(clear, 120000);

		});

		

		var clearBtn = document.getElementById("clear-button");

		clearBtn.addEventListener("click", clear);

		clearBtn.addEventListener("touchend", clear);

		

		var colors = document.getElementsByClassName("color");

		for(var i=0;i < colors.length;i++) {

			colors[i].addEventListener("click", selectColor);	

			colors[i].addEventListener("touchend", selectColor);

		}

		

		function selectColor(e) {

			var rgb = e.target.getAttribute("data-color").split(",");

			if(rgb.length >= 3) {

				setFillColor(parseInt(rgb[0]), parseInt(rgb[1]), parseInt(rgb[2]), 255);

			}

			

			for(var j=0;j < colors.length; j++) {

				colors[j].classList.remove("selected");

			}

			e.target.classList.add("selected");

		}

		

		function clear() {

			clearCanvas();

			

			var randBg = Math.floor(Math.random() * 0);

			bgImg = new Image();

			bgImg.onload = function (e) {

				// BG Image done loading.

				ctx.drawImage(bgImg, 0, 0, c.width, c.height);

				layerData = ctx.getImageData(0, 0, c.width, c.height);

				

			}

			bgImg.src = "/wp-content/plugins/fillapp/" + backgrounds[randBg];

		}

	}

	

	

	

	function redraw() {

		clearCanvas();	

		ctx.drawImage(bgImg, 0, 0, ctx.canvas.width, ctx.canvas.height);

		ctx.putImageData(layerData, 0, 0);

	}

	

	function clearCanvas() {

		ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);

	}

	

	function setFillColor(r, g, b, a) {

		fillColor.r = r;

		fillColor.g = g;

		fillColor.b = b;

		fillColor.a = a;

	}

	

	function floodFill (startX, startY, startR, startG, startB, startA) {

		var newPos,

			x,

			y,

			pixelPos,

			reachLeft,

			reachRight,

			pixelStack = [[startX, startY]];



		while (pixelStack.length) {



			newPos = pixelStack.pop();

			x = newPos[0];

			y = newPos[1];



			// Get current pixel position

			pixelPos = (y * c.width + x) * 4;



			// Go up as long as the color matches and are inside the canvas

			while (y >= 0 && matchStartColor(pixelPos, startR, startG, startB, startA)) {

				y -= 1;

				pixelPos -= c.width * 4;

			}



			pixelPos += c.width * 4;

			y += 1;

			reachLeft = false;

			reachRight = false;



			// Go down as long as the color matches and in inside the canvas

			while (y <= c.height - 1 && matchStartColor(pixelPos, startR, startG, startB, startA)) {

				y += 1;



				colorPixel(pixelPos, fillColor.r, fillColor.g, fillColor.b, fillColor.a);



				if (x > 0) {

					if (matchStartColor(pixelPos - 4, startR, startG, startB, startA)) {

						if (!reachLeft) {

							// Add pixel to stack

							pixelStack.push([x - 1, y]);

							reachLeft = true;

						}

					} else if (reachLeft) {

						reachLeft = false;

					}

				}



				if (x < c.width - 1) {

					if (matchStartColor(pixelPos + 4, startR, startG, startB, startA)) {

						if (!reachRight) {

							// Add pixel to stack

							pixelStack.push([x + 1, y]);

							reachRight = true;

						}

					} else if (reachRight) {

						reachRight = false;

					}

				}



				pixelPos += c.width * 4;

			}

		}

	}

	

	function matchOutlineColor (r, g, b, a) {

		return (r + g + b < 100 && a === 255);

	}

	

	function matchStartColor (pixelPos, startR, startG, startB, startA) {

		var r = layerData.data[pixelPos];

		var g = layerData.data[pixelPos + 1];

		var b = layerData.data[pixelPos + 2];

		var a = layerData.data[pixelPos + 3];





		// If current pixel of the outline image is black

		if (matchOutlineColor(r, g, b, a)) {

			return false;

		}



		// If the current pixel matches the clicked color

		if (r === startR && g === startG && b === startB && a === startA) {

			return true;

		}



		// If current pixel matches the new color

		if (r === fillColor.r && g === fillColor.g && b === fillColor.b && a === fillColor.a) {

			return false;

		}



		return true;

	}

	

	function colorPixel (pixelPos, r, g, b, a) {

		layerData.data[pixelPos] = r;

		layerData.data[pixelPos + 1] = g;

		layerData.data[pixelPos + 2] = b;

		layerData.data[pixelPos + 3] = a !== undefined ? a : 255;

	}

	

	function fillAt (startX, startY) {

		var pixelPos = (startY * c.width + startX) * 4,

			r = layerData.data[pixelPos],

			g = layerData.data[pixelPos + 1],

			b = layerData.data[pixelPos + 2],

			a = layerData.data[pixelPos + 3];



		if (r === fillColor.r && g === fillColor.g && b === fillColor.b && a === fillColor.a) {

			// Return because trying to fill with the same color

			return;

		}



		floodFill(startX, startY, r, g, b, a);

		redraw();

	}

	

	return {

		setFillColor: setFillColor

	};

}());