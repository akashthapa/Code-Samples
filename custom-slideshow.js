/*
 * @author: Akash Thapa
 * This is script for slide show
 * @sample site : http://31337.canadahealthlinks.com/
 * All scripts are written from the scratch
 * The main version was designed in Flash but client did not like to have it in Flash because of its
   uncompatible issue with iphone/ipad
   So I created the script with jquery.
 * The good thing of this slide show is, it can be use by mouse scroll, onClick and auto rotation.
*/
		  var _ci, // current index
		  _y, // current position of thumbs
		  _dy, // distance to be animate
		  _liH = 76, // single thumb height
		  _center, // Center position of thumb
		  _thumbs, // thumbs wrapper
		  _big,
		  _ul, //
		  _top,//
		  _ulH, // thumbs ul height
		  _len, // total len of slide
		  _info_timer,
		  _timer,
		  _re_timer,
		  index = 3,
		  _drag = false;
		  
;(function($) { // it will prevent from being overide any $ value
	// Initialize script when documents get fully loaded
	$(document).ready(function(){
		_thumbs = $('#front-gallery .item-list');
		_big = $('#main_view');
		_ulH = _thumbs.height();
		_center = (_ulH - _liH) / 2 ;
		_ul = _thumbs.find('ul');
		_len = _ul.find('li').length;
		_y = _ul.position().top;
		//_ul.css('top', -_liH);
		calculate_distance();
		_big.find('ul').css('opacity', 0).animate({opacity:1}, 600);
		// MOUSE WHEEL
		_thumbs.mousewheel(function(e, v){
			stop_slide();
			_timer = setInterval('auto_slide()', 12000);
			single_add(v);
			e.preventDefault();
		});
		// MOUSE OVER
		
		//MOUSE CLICK
		_thumbs.click(function(e){
			if(!_drag){
				stop_slide();
				_timer = setInterval('auto_slide()', 20000);
				var x = e.pageY - $(this).offset().top;
				var jump = 0;
				var up = 1;
				var np = -((jump * _liH) + Math.abs(_top));
				if(x < _center){
					jump = (x < (_center - _liH)) ? 2 : 1;
					
				}else if( x > _center + _liH){
					up = 0;
					jump = ( x > _center + (_liH * 2)) ? 2 : 1;
					
				}
				
				if(jump)
				single_add(up);
			}
		});
		// reposition of title
		_thumbs.find('ul li').each(function(){
			var h = $(this).find('.item-title').height();
			var lh = $(this).height();
			$(this).find('.item-title').css('top', (lh-h)/2);
		});
				
	});
})(jQuery);
/*
 * place: @boolean
 * 1 for appendTo, -1 prependTo
 */
function single_add(place){
	_big.find('li .info').stop(true, true).css({bottom:-100, opacity:0});
	// Just copies the last or first element and inserts in the first or last
	add(place);
	_big_index = place == 1 ? 3 : 4;
	// Resposition of top position
	// If insterted into first, it moves 1 position up for animation
	var reset_position = place == 1 ? -_liH * 2 :  _liH + _top;
	_ul.css('top', reset_position);
	// animate thumbs and big
	slide_animation(_big_index);
	// delete thumb and big
	remove(place);
}


/*
 * Copy and Append or prepend before animation
 */
function add(place){
	if(place == 1){
		_thumbs.find('ul').find('li:last').clone(true).prependTo(_thumbs.find('ul'));
		_big.find('li:last').clone(true).prependTo(_big.find('ul'));
	}
	else{
		_thumbs.find('ul').find('li:first').clone(true).appendTo(_thumbs.find('ul'));
		_big.find('li:first').clone(true).appendTo(_big.find('ul'));
	}
}
/*
 * Remove li after animation just instered with add() method before animation 
 */
function remove(place){
	var index = place == 1 ? 'last' : 'first';
	_thumbs.find('ul').find('li:'+index).remove();
	_big.find('li:'+index).remove();
}
/* 
 * calculate new distance and initialiaze important variables
 */
function calculate_distance(){
	_y = _ul.position().top;
	if((_center + _y) / _liH != 2){
		_dy = 3 * _liH;
		_top = (_center - _dy);
		var big_top = _big.find('li').height() * 3;
		_big.find('ul').css('top', -big_top);
		slide_animation(3);
		init_timer();
	}
}
function re_timer(){
	stop_slide();
}
function init_timer(){
	_timer = setInterval('auto_slide()', 12000);
}
function auto_slide(){
	_info_timer = setTimeout('slide_info_in()', 500);
	single_add(0);
}
function stop_slide(){
	clearInterval(_timer);
	clearInterval(_re_timer);
}
/*
 * index = which index should be animated of Big slide it is 3 or 4
 */
function slide_animation(index){
	_big.find('ul').css('opacity', 1);
	if(!_ul.is(':animated')){
		_ul.stop(true, true).animate({top:_top}, 400, 'easeOutBounce');
		_big.find('ul').css('opacity', 0).show().stop(true, true).animate({opacity:1, bottom:10}, 1000);
		clearTimeout(_info_timer);
		_info_timer = setTimeout('slide_info_in()', 1000);
	}
}

function slide_info_in(){
		_big.find('ul').find('li:eq('+3+') .info').css('opacity', 0).show().stop(true, true).animate({opacity:1, bottom:10}, 500, 'easeInBounce', function(){
		// set timer for out
		//_info_timer = setTimeout('slide_info_out()', 7000);
	});
}

function slide_info_out(){
	_big.find('li:eq('+3+') .info').stop(true, true).animate({opacity:0, bottom:-100}, 500, 'easeOutBounce', function(){
		// set timer for out
		clearTimeout(_info_timer);
	});
}

