;(function () {
    window.toothList = function (params) {
        this.params = params;
        this.init();
    };

    window.toothList.prototype.init = function () {
        id = '#' + this.params.id;
        $(id + ' .select-tooth-items').selectable({
            filter: ".tooth-item",
            stop: function () {
                window.arToothSelect = [];
                $(id + ' .tooth-item.ui-selected').each(function () {
                    window.arToothSelect.push($(this).data('tooth-id'));
                });
            }
        });
        $(id + ' .select-parent, ' + id + ' .select-child').on('click', function () {
            $(id + ' .select-parent, ' + id + ' .select-child').removeClass('active');
            if ($(this).is('.select-parent')) {
                $(id + ' .select-tooth-parent').addClass('active').show();
                $(id + ' .select-tooth-child').removeClass('active').hide();
            } else {
                $(id + ' .select-tooth-parent').removeClass('active').hide();
                $(id + ' .select-tooth-child').addClass('active').show();
            }
            $(this).addClass('active');
            window.arToothSelect = [];
        });
        $(id + ' .select-top-jowl, ' + id + ' .select-bottom-jowl').on('click', function () {
            if ($(this).is('.select-top-jowl')) {
                $(id + ' .select-tooth-items.active .tooth-items-top .tooth-item').addClass('ui-selected');
            } else {
                $(id + ' .select-tooth-items.active .tooth-items-bottom .tooth-item').addClass('ui-selected');
            }
            window.arToothSelect = [];
            $(id + ' .tooth-item.ui-selected').each(function () {
                window.arToothSelect.push($(this).data('tooth-id'));
            });
        })
        $(id + ' .select-clear').on('click', function () {
            $(id + ' .tooth-item.ui-selected').removeClass('ui-selected');
            window.arToothSelect = [];
        })
    };

})();