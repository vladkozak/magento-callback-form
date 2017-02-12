jQuery(document).ready(function($) {
    var fancyOptions = {
        helpers: {
            overlay: {
                css: {
                    'background': 'rgba(58, 42, 45, 0.5)'
                }
            },
            title: {
                type: null
            }
        },
        'ajax': { cache: true, dataType: 'html' },
        'transitionIn': 'none',
        'transitionOut': 'none',
        'speedIn': 600,
        'speedOut': 200,
        'width': 600,
        'height': 'auto',
        'topRatio': 0.2,
        'overlayShow': false,
        'autoSize': false,
        'closeBtn': true,
        'beforeShow': function () {
            if (this.element.hasClass('service')) {
                var sendSubmit = $('.fancybox-inner').find('input[type="submit"]');
                sendSubmit.before('<input type="button" class="button prev" value="Назад" />');
            }
        },
        'afterShow': function (prev, curr) {
            $('.datepicker').trigger('datepicker.refresh');
        }
    };

    $("#service-online-order, .service").fancybox(fancyOptions);

    /*var mainService = $('#service-online-order');
    $('body').on('click', '.button.prev', function (e) {
        mainService.trigger('click');
    });*/
});