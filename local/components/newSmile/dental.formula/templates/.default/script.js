;(function () {
    window.dentalFormila = function (params) {
        this.params = params;
        this.init();
    };

    window.dentalFormila.prototype.init = function () {
        
        $('.dental-formula__item').each(function () {
            $item = $(this);
            if ($item.find('.dental-formula__item__checked input').is(':checked')) {
                $item.find('.dental-formula__item--parent').addClass('active').show();
                $item.find('.dental-formula__item--child').removeClass('active').hide();
            } else {
                $item.find('.dental-formula__item--parent').removeClass('active').hide();
                $item.find('.dental-formula__item--child').addClass('active').show();
            }
        });

        $('.dental-formula__item__checked input').on('click', function () {
            $item = $(this).parents('.dental-formula__item');
            if ($item.find('.dental-formula__item__checked input').is(':checked')) {
                $item.find('.dental-formula__item--parent').addClass('active').show();
                $item.find('.dental-formula__item--child').removeClass('active').hide();
            } else {
                $item.find('.dental-formula__item--parent').removeClass('active').hide();
                $item.find('.dental-formula__item--child').addClass('active').show();
            }
        });

        var items = {};
        $.each(this.params.status, function (index, value) {
            items[value['CODE']] = {
                name: value['NAME'],
                callback: function (key, opt) {
                    $(this).data('status', key).attr('data-status', key);
                    $(this).text(key);
                }
            }
        });
        $.contextMenu({
            selector: '.dental-formula__item__tooth',
            items: items
        });

    };

})();