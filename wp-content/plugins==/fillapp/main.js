
jQuery(document).ready(function(){


	var raster, cfg;

	var bucket = Bucket(canvas, cfg, raster);


	var hiddenColor = document.getElementById('hiddenColor');


	jQuery("#hiddenColor").change(function(event){

		var color = document.getElementById('hiddenColor').value;

	    var colour = color;
	    bucket.colour = colour;		
	})


	jQuery('.clearCanvas').click(function(){

	            redraw();         

	})



	jQuery(".color").click(function(){

		var color = jQuery('.color input[type=radio]:checked').val();

		jQuery('#hiddenColor').attr('value', color);

		jQuery('#hiddenColor').change();

	})


	jQuery('#favcolor').on('change', function(){

		jQuery('#hiddenColor').attr('value', jQuery(this).val());

		// jQuery('#hiddenColor').val(jQuery(this).val());

		jQuery('.color input[type=radio]:checked').prop('checked', false);

		 jQuery('#hiddenColor').change();

		console.log('color change');

	})


	jQuery('.fillBtn').click(function(){

		if(jQuery('.fillBtn input').is(':checked'))
		{
			jQuery('.canvasCon').addClass('paintCon');
		}
		else{
			jQuery('.canvasCon').removeClass('paintCon');

		}

	})




	jQuery('.saveClick').click(function(){

		var jpegUrl = canvas.toDataURL('image/jpeg');
		var pngUrl = canvas.toDataURL();

		//window.open(pngUrl);

        jQuery('.baseImage').val(pngUrl);

        jQuery('#baseImageForm').submit();

		// var newTab = window.open();
		// newTab.document.body.innerHTML = '<img src=\"' + pngUrl + '\" >';


		// console.log(pngUrl);

	})	

})


window.addEventListener("load", () => {
	const canvas = document.querySelector("#canvas");
	const ctx = canvas.getContext('2d');

	const img = new Image();
	img.src = jQuery('#image').attr('src');

	img.onload = () => {
		const [img_scaled_width, img_scaled_height] = drawImageToScale(img, ctx);
		canvas.width = img_scaled_width;
		canvas.height = img_scaled_height;
		window.addEventListener('resize', drawImageToScale(img,ctx));

	}

	// variables
	let painting = false;

	function startPosition(e){
		painting = true;
		draw(e);
	}

	function finishedPosition(){
		painting = false;
		ctx.beginPath();
	}

	function draw(e){

		if(!jQuery('.fillSet').is(':checked'))
		{
			var rect = e.currentTarget.getBoundingClientRect(),
			    offsetX = e.clientX - rect.left,
	      		offsetY = e.clientY - rect.top;

			if (!painting)
				return;
			ctx.lineWidth = 3;
			
			ctx.lineCap = 'round';

			//console.log(e.clientX, e.clientY);
			ctx.strokeStyle = jQuery('#hiddenColor').val();
			ctx.lineTo(offsetX, offsetY);
			ctx.stroke();
			ctx.beginPath();
			ctx.moveTo(offsetX, offsetY);			
		}

	}

	// eventListeners
	canvas.addEventListener("mousedown", startPosition);
	canvas.addEventListener("mouseup", finishedPosition);
	canvas.addEventListener("mousemove", draw);


    function startTouch(e){
        painting = true;
        draw(e);
    }

    function finishedTouch(){
        painting = false;
        ctx.beginPath();
    }

    function Touch(e){

        e.preventDefault();

        if(!jQuery('.fillSet').is(':checked'))
        {
            var rect = e.currentTarget.getBoundingClientRect(),
                offsetX = e.touches[0].clientX - rect.left,
                offsetY = e.touches[0].clientY - rect.top;

            if (!painting)
                return;
            ctx.lineWidth = 3;
            
            ctx.lineCap = 'round';

            //console.log(e.clientX, e.clientY);
            ctx.strokeStyle = jQuery('#hiddenColor').val();
            ctx.lineTo(offsetX, offsetY);
            ctx.stroke();
            ctx.beginPath();
            ctx.moveTo(offsetX, offsetY);           
        }

    }


    canvas.addEventListener("touchstart", startTouch);
    canvas.addEventListener("touchend", finishedTouch);
    canvas.addEventListener("touchmove", Touch)

});

function drawImageToScale(img, ctx){

    var image = jQuery('.hiddenImage img').width();

	const img_width = image;
	const scaleFactor = img_width / img.width;
	const img_height = img.height * scaleFactor;
	ctx.drawImage(img, 0, 0,img_width,img_height);
	return [img_width,img_height];
}



function redraw(){


	var canvas = document.getElementById('canvas');

	var context = canvas.getContext("2d");
		
	context.clearRect(0, 0, context.canvas.width, context.canvas.height); // Clears the canvas

	const img = new Image();
	img.src = jQuery('#image').attr('src');

	const [img_scaled_width, img_scaled_height] = drawImageToScale(img, context);
	canvas.width = img_scaled_width;
	canvas.height = img_scaled_height;
	window.addEventListener('resize', drawImageToScale(img,context));

  
}




// Bucket tool for canvas, using the flood_fill function
function Bucket(canvas, cfg, raster) {
    if(!(this instanceof Bucket)) {
        return new Bucket(canvas, cfg, raster);
    }

    console.log(raster);
    
    var _this = this,
        context = canvas.getContext('2d');
    
    this.canvas = canvas;
    cfg = cfg || {};
    
    // Apply defaults
    this.colour = cfg.colour || '#ff0000';
    
    this.active = cfg.active === undefined ? true : !!cfg.active;
    
    this.tolerance = cfg.tolerance === undefined || isNaN('' + cfg.tolerance) ? 20 : cfg.tolerance;
    
    this.fill_tolerance = cfg.fill_tolerance === undefined || isNaN('' + cfg.fill_tolerance) ? 1 : cfg.fill_tolerance;
    
    // Attach the click listener
    canvas.addEventListener('click', function(event) {
        if(!_this.active || !jQuery('.fillSet').is(':checked')) {
            return;
        }
        
        var x = event.offsetX, 
            y = event.offsetY,
            canvas_size = canvas.getClientRects()[0],
            image_data = context.getImageData(0, 0, canvas_size.width, canvas_size.height),
            // PERF: Compile a function for quickly getting the offset into image_data.data that corresponds 
            // to an x-y pixel coordinate
            get_point_offset = new Function('x', 'y', 'return 4 * (y * ' + image_data.width + ' + x)'),
            // Find the offset, in image_data.data, of the clicked pixel
            target_offset = get_point_offset(x, y),
            target = image_data.data.slice(target_offset, target_offset + 4),
            result;
        
        if(tolerance_equal(target, 0, _this.parsed_colour, _this.fill_tolerance)) {
            // Trying to fill something which is (essentially) the fill colour
            return;
        }
        
        // Perform fill - this mutates the image_data.data array
        flood_fill(
            image_data.data, 
            get_point_offset, 
            { x: x, y: y }, 
            _this.parsed_colour, 
            target, _this.tolerance, 
            image_data.width, 
            image_data.height
        );

        // raster.clear();
        
        // Push the updated image data back to the canvas
        context.putImageData(image_data, 0, 0);

    });
}

// Getter/setter for colour which validates and parses the set value
Object.defineProperty(Bucket.prototype, 'colour', {
    get: function() {
        return this.__colour;
    },
    set: function(value) {
        // Try to extract the hex values from the colour string
        var parsed = Bucket.__parse_colour_rgx.exec(value || '');
        if(!parsed) {
            // Don't update if string isn't parsable
            if(window.DEBUG) {
                console.warn('Invalid colour set: ', value);
            }
            
            return;
        }
    
        // Store the value, and the parsed data
        this.__colour = value;
        this.__parsed_colour = parsed.slice(1).map(function(value) { 
            // Get int from hex
            var parsed_int = parseInt(value, 16);
            
            // Default to 255 if value isn't an int or is out of uint8 range
            return isNaN(parsed_int) || parsed_int < 0 || parsed_int > 255 ? 255 : parsed_int;
        });
    }
});

// Accessor for the parsed colour value
Object.defineProperty(Bucket.prototype, 'parsed_colour', {
    get: function() {
        return this.__parsed_colour;
    }
});

// Static regex for extracting rgb(a) colours from a hex string (e.g. '#ff0000')
Bucket.__parse_colour_rgx = /^#?([0-9a-f]{1,2})([0-9a-f]{1,2})([0-9a-f]{1,2})([0-9a-f]{1,2})?$/i;


// Compare subsection of array_one's values to array_two's values, with an optional tolerance
function tolerance_equal(array_one, offset, array_two, tolerance) {
    var length = array_two.length,
    start = offset + length;
    
    tolerance = tolerance || 0;
    
    // Iterate (in reverse) the items being compared in each array, checking their values are 
    // within tolerance of each other
    while(start-- && length--) {
        if(Math.abs(array_one[start] - array_two[length]) > tolerance) {
            return false;
        }
    }
    
    return true;
}

// The actual flood fill implementation
function flood_fill(image_data, get_point_offset, point, colour, target, tolerance, width, height) {
    var points = [point],
        seen = {},
        steps = flood_fill.steps,
        key,
        x,
        y,
        offset,
        i,
        x2,
        y2;
    
    // Keep going while we have points to walk
    while(!!(point = points.pop())) {
        x = point.x;
        y = point.y;
        offset = get_point_offset(x, y);
        
        // Move to next point if this pixel isn't within tolerance of the colour being filled
        if(!tolerance_equal(image_data, offset, target, tolerance)) {
            continue;
        }
        
        // Update the pixel to the fill colour and add neighbours onto stack to traverse 
        // the fill area
        i = flood_fill.fill_ways;
        while(i--) {
            // Use the same loop for setting RGBA as for checking the neighbouring pixels
            if(i < 4) {
                image_data[offset + i] = colour[i];
            }
        
            // Get the new coordinate by adjusting x and y based on current step
            x2 = x + steps[i][0];
            y2 = y + steps[i][1];
            key = x2 + ',' + y2;
            
            // If new coordinate is out of bounds, or we've already added it, then skip to 
            // trying the next neighbour without adding this one
            if(x2 < 0 || y2 < 0 || x2 >= width || y2 >= height || seen[key]) {
                continue;
            }
            
            // Push neighbour onto points array to be processed, and tag as seen
            points.push({ x: x2, y: y2 });
            seen[key] = true;
        }
    }
}

// Static props for adjustment steps to use in fill algorithm (4-way is default) and a getter 
// to dynamically check how many steps are set
flood_fill.steps = [[1, 0], [0, 1], [0, -1], [-1, 0]];
Object.defineProperty(flood_fill, 'fill_ways', {
    get: function() {
        return flood_fill.steps.length;
    }
});





