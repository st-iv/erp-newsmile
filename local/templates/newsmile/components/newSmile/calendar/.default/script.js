;(function () {
    window.Calendar = function(params, calendarFilter)
    {
        this.params = params;
        this.calendarFilter = calendarFilter;

        var _this = this;

        $(document).ready(function()
        {
            _this.init();
        });
    };

    window.Calendar.prototype.init = function()
    {
        var dateData = this.getDateData(this.params.dateInfo);
        var _this = this;


        $( "#left-calendar" ).customCalendar({
            dateFrom: this.params.startDay,
            dateTo: this.params.endDay,
            curDate: this.params.curDay,
            defDateColor: 'eaeaea',
            defDateTextColor: '454545',
            dateData: dateData,
            callbacks: {
                onScrollEnd: this.loadNewDates.bind(_this)
            }
        });

        $('#left-calendar').on('click', '.custCalendar_day', function()
        {
            _this.loadSchedule($(this).data('date'));
        });

        Ajax.registerLoadHandler(Ajax.getAreaCode($( "#left-calendar" )), this.handleAjaxUpdate.bind(this));
    };

    window.Calendar.prototype.getColor = function(freeMinutes)
    {
        var color = {
            BACKGROUND: 'eaeaea',
            TEXT: '454545'
        };

        var minutesBounds = Object.keys(this.params.colors);

        for(var i=0; i < minutesBounds.length; i++)
        {
            if((i == (minutesBounds.length - 1)) || ((freeMinutes >= minutesBounds[i]) && (freeMinutes < minutesBounds[i+1])))
            {
                color = $.extend(color, this.params.colors[minutesBounds[i]]);
                break;
            }
        }

        return color;
    };

    window.Calendar.prototype.loadSchedule = function(date)
    {
        this.calendarFilter.setFilterParam('THIS_DATE', date);
        Ajax.load(this.params.ajaxUrl, this.params.calendarDayAjaxArea, this.calendarFilter.getFilterData());
    };

    window.Calendar.prototype.handleAjaxUpdate = function(response)
    {
        if(typeof response.dateInfo === 'object')
        {
            var updateFields = {};

            if(response.dateInfo)
            {
                //$.extend(this.params.dateInfo, response.dateInfo);
                updateFields.dateData = this.getDateData(response.dateInfo);
            }

            if(response.startDay)
            {
                updateFields.dateFrom = response.startDay
            }

            if(response.endDay)
            {
                updateFields.dateTo = response.endDay;
            }

            $('#left-calendar').customCalendar('update', updateFields);
        }
    };

    window.Calendar.prototype.getDateData = function(rawDateData)
    {
        var dateData = {};

        if(rawDateData)
        {
            for(var date in rawDateData)
            {
                var curDateInfo = rawDateData[date];

                dateData[date] = {
                    timeAvlble: General.Date.formatMinutes(curDateInfo['GENERAL_MINUTES']),
                    timeFree: General.Date.formatMinutes(curDateInfo['GENERAL_MINUTES'] - curDateInfo['ENGAGED_MINUTES']),
                    patients: curDateInfo['PATIENTS'],
                    color: this.getColor(curDateInfo['GENERAL_MINUTES'] - curDateInfo['ENGAGED_MINUTES'])
                };
            }
        }

        return dateData;
    };

    window.Calendar.prototype.loadNewDates = function(direction, unit)
    {
        var $calendar = $( "#left-calendar" );
        var startDate, endDate;
        var loadWeeksCount = ((unit == 'week') ? 1 : 4);

        if(direction == 'top')
        {
            endDate = $('#left-calendar').customCalendar('firstDate');
            startDate = moment(endDate).add('-' + loadWeeksCount, 'w').format('YYYY-MM-DD');
            this.calendarFilter.setFilterParam('DATE_FROM', startDate);
        }
        else
        {
            startDateMoment = moment($('#left-calendar').customCalendar('lastDate')).add('+1', 'd');
            startDate = startDateMoment.format('YYYY-MM-DD');
            endDate = startDateMoment.add('+' + loadWeeksCount - 1, 'w').add('-1', 'd').format('YYYY-MM-DD');
            this.calendarFilter.setFilterParam('DATE_TO', endDate);
        }

        var data = this.calendarFilter.getFilterData();
        data.DATE_FROM = startDate;
        data.DATE_TO = endDate;

        Ajax.load(this.params.ajaxUrl, Ajax.getAreaCode($calendar), data, false, function()
        {
            $scrollKoef = (direction == 'top' ? 1 : -1);
            $calendar.customCalendar('scroll', $scrollKoef, unit)
        });
    }

})();