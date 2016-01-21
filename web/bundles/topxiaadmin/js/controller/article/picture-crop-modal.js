define(function(require, exports, module) {
    require("jquery.jcrop-css");
    require("jquery.jcrop");
    var Notify = require('common/bootstrap-notify');
    var ImageCrop = require('edusoho.imagecrop');

    exports.run = function() {

        var $modal = $("#modal");
        var imageCrop = new ImageCrop({
            element: "#article-pic-crop",
            group: 'article',
            cropedWidth: 216,
            cropedHeight: 120
        });

        imageCrop.on("afterCrop", function(response) {
            var url = $("#upload-picture-crop-btn").data("gotoUrl");
            var target = $("#upload-picture-crop-btn").data("target");
            var fix = '';
            switch (target) {
                case 'detail':
                    fix = 'detail_';

                    break;
                case 'carousel01':
                    fix = 'carousel01_';
                    break;
                case 'carousel02':
                    fix = 'carousel02_';
                    break;

                case 'carousel03':
                    fix = 'carousel03_';
                    break;


                default:

                    break;

            }
            var thumb_container = '#article-' + fix + 'thumb-container';
            var thumb_remove = "#article-" + fix + "thumb-remove";
            var thumb = "#article-" + fix + "thumb";
            var originalThumb = "#article-" + fix + "originalThumb";
            var thumb_preview = "#article-" + fix + "thumb-preview";



            $.post(url, {
                images: response
            }, function(data) {
                $modal.modal('hide');
                $(thumb_container).show();
                $(thumb_remove).show();
                $(thumb).val(data.large.file.uri);
                $(originalThumb).val(data.origin.file.uri);
                $(thumb_preview).attr('src', data.large.file.url);
                $(thumb_container).html("<img src='" + data.large.file.url + "'>")
            });

        });


        $("#upload-picture-crop-btn").click(function(e) {
            e.stopPropagation();

            var postData = {
                imgs: {
                    large: [216, 120]
                },
                deleteOriginFile: 0
            };

            imageCrop.crop(postData);

        });

    };

});