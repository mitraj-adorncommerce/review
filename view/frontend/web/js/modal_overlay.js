require(
    [
        'uiComponent',
        'jquery',
        'Magento_Ui/js/modal/modal'
    ],
    function (
        Component,
        $,
        modal,
    ) {
        'use strict';
        if (window.popup === "1") {
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                buttons: [{
                    text: $.mage.__('Continue'),
                    class: 'mymodal1',
                    click: function () {
                        this.closeModal();
                    }
                }]
            };
            var popup = modal(options, $('#modal-overlay'));
            $("#write-a-review").on('click', function () {
                $("#modal-overlay").modal("openModal");
                $(".modal-footer").hide();
            });
        }
        $("#product_review_image").on('change', function () {
            $(".review-thumbnail").empty();
            for (let i = 0; i < this.files.length; ++i) {
                let filereader = new FileReader();
                if (this.files[0].size > 2000000){
                    $('#size-error').html($.mage.__('Please upload file less than 2MB. Thanks!!'));
                    $('#review-btn').prop('disabled', true);
                    return false;
                }else {
                    $('#size-error').html('');
                    $('#review-btn').prop('disabled', false);
                }
                let $img = jQuery.parseHTML("<div class='review-photo'></div>");
                filereader.onload = function () {
                    $img[0].style.backgroundImage = 'url(' +this.result+')';
                };
                filereader.readAsDataURL(this.files[i]);
                $(".review-thumbnail").append($img);
            }
        });
    }
);
