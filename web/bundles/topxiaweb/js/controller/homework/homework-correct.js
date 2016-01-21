define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');
//var $ = require('jquery');
  require('jquery.ui.core');
  require('jquery.ui.widget');
  require('jquery.ui.mouse');
require('jquery.ui.draggable');


require('wPaint');
require('wColorPicker');
    exports.run = function() {
    	var pic = $('#student_pic').attr('src');
    	if(!pic){
    		pic = '/assets/libs/wPaint/images/demo/wPaint.jpg';
    	}
    	
$("#wPaint2").wPaint({
			menuOrientation: 'horizontal',
			menuOffsetX          : 5,
			menuOffsetY          : -50 ,
			imageBg: pic
		});
//清除
$('#clear_canvas').on('click',function(){
	$("#wPaint2").wPaint('clear');
});
//上传
$('#upload_image').on('click',function(){
	var imageData = $("#wPaint2").wPaint("image");

			$.ajax({
				url: '/homework/upload/imageData',
				data: {image: imageData},
				type: 'post',
				success: function(resp)
				{
					$('#pic').val(resp.pic);
					Notify.success('successfully uploaded image!');
				}
			});
});

//
//
function clearCanvas()
		{
			$("#wPaint2").wPaint("clear");
		}

		function saveImage2()
		{
			var imageData2 = $("#wPaint2").wPaint("image");

			$("#canvasImage2").attr('src', imageData2);
		}

		function updateBg2()
		{
			$("#wPaint2").wPaint("imageBg", '../images/demo/wPaint.jpg');
		}
				function upload_image(id)
		{
			var imageData = $("#" + id).wPaint("image");

			$.ajax({
				url: '../upload.php',
				data: {image: imageData},
				type: 'post',
				success: function(resp)
				{
					alert('successfully uploaded image!');
				}
			});
		}
    };

});