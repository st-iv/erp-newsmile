;(function () {
    window.CalendarFilter = function(params)
    {
        this.params = params;
        this.$filterBlock = $('.shld_filter');
        this.init();
    };

    window.CalendarFilter.prototype.init = function()
    {
        var _this = this;

        $(document).ready(function()
        {
            $.widget( "custom.iconselectmenu", $.ui.selectmenu, {
                _renderItem: function(ul, item) {
                    var li = $("<li>"),
                        wrapper = $("<div>", {text: item.label});

                    if (item.disabled) {
                        li.addClass("ui-state-disabled");
                    }

                    if (item.element.attr("data-color") == "fff") {
                        li.addClass("df-doctor");
                    }

                    $( "<span>", {
                        style: "background-color: #" + item.element.attr("data-color") +";",
                        "class": "doctor-color"
                    }).prependTo(wrapper);

                    return li.append(wrapper).appendTo(ul);
                },
                _renderButtonItem: function( item ) {
                    var buttonItem = $( "<span>", {
                        "class": "ui-selectmenu-text"
                    });

                    this._setText( buttonItem, item.label );

                    if (item.element.attr("data-color") == "fff") {
                        buttonItem.addClass("df-doctor");
                    }

                    $( "<span>", {
                        style: "background-color: #" + item.element.attr("data-color") +";",
                        "class": "doctor-color"
                    }).prependTo(buttonItem);

                    return buttonItem;
                }
            });

            $("#doctor").iconselectmenu().iconselectmenu("menuWidget");

            $("#speс").selectmenu();

            _this.$filterBlock.find('input[type=reset]').click(function(e)
            {
                setTimeout(function(){
                    _this.reset();
                }, 300)
            });

            _this.initTimeRange();
        });
    };

    window.CalendarFilter.prototype.initTimeRange = function()
    {
        var _this = this;

        var minRange = this.getTimeRangeValue(this.params.startTime);
        var maxRange = this.getTimeRangeValue(this.params.endTime);

        $( "#time-range" ).slider({
            range: true,
            min: minRange,
            max: maxRange,
            values: [minRange, maxRange],
            slide: function(event,ui)
            {
                _this.setTimeRange(ui.values[0], ui.values[1]);
            }
        });

        _this.setTimeRange(minRange, maxRange);
    };

    window.CalendarFilter.prototype.getFilterData = function()
    {
        return General.getParamsObject(this.$filterBlock.parents('form').serialize());
    };

    window.CalendarFilter.prototype.setFilterParam = function(paramName, paramValue)
    {
        this.$filterBlock.find('input[name=' + paramName + ']').val(paramValue);
    };

    window.CalendarFilter.prototype.reset = function()
    {
        var minRange = this.getTimeRangeValue(this.params.startTime);
        var maxRange = this.getTimeRangeValue(this.params.endTime);
        $("#time-range").slider('option', 'values', [minRange, maxRange]);

        this.setTimeRange(minRange, maxRange);

        this.$filterBlock.parents('form').submit();
    };

    window.CalendarFilter.prototype.setTimeRange = function(minRange, maxRange)
    {
        function prepareTime(x)
        {
            var hours = parseInt(x / 4),
                minutes = (x - hours * 4) * 15;

            if (hours.toString().length === 1){
                hours = '0' + hours;
            }
            if (minutes === 0){
                minutes = '00';
            }
            return hours + ':' + minutes;
        }

        $("#time-range_from span").text(prepareTime(minRange));
        $("#time-range_to span").text(prepareTime(maxRange));
        $('#time-range-from-input').val(General.Date.formatMinutes(minRange * 15));
        $('#time-range-to-input').val(General.Date.formatMinutes(maxRange * 15));
    };

    window.CalendarFilter.prototype.getTimeRangeValue = function(strTime)
    {
        var strTimeArray = strTime.split(':');
        var hours = Number(strTimeArray[0]);
        return (Number(strTimeArray[1]) / 15) + hours * 4;
    };
})();