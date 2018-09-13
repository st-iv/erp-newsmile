;(function () {
    window.calendar = function (params) {

        var _this = this;

        this.params = params;

        this.init = function () {
            _this.initSelectable();
        };

        this.initSelectable = function () {

            $('.calendar-month').selectable({
                filter: ".calendar-month--day",
                selected: function () {
                    $('.calendar-days').html('');
                    $('.calendar-month--day.ui-selected').each(function () {
                        date = $(this).data('date');
                        _this.loadCalendarDay(date);
                    });
                }
            });

        };

        this.loadCalendarDay = function (date) {
            $.post(
                '/ajax/calendar-day.php',
                {
                    THIS_DATE: date
                },
                function (data) {
                    $('.calendar-days').append(data);
                },
                'html'
            ).fail(function (data) {
                console.log(data);
            });
        };

        this.sendPost = function (data, action, callback) {
            if (!action || !callback) {
                console.log('No found action or callback');
                return false;
            }
            data.action = action;
            $.post(
                '/local/components/newSmile/calendar.day/ajax.php',
                data,
                callback,
                'json'
            ).fail(function (data) {
                console.log(data);
            });
        };


        _this.init();
    };

})();