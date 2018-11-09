;(function () {
    window.CalendarFilter = function(params)
    {
        this.params = params;
        this.$filterBlock = $('.shld_filter');
        this.$form = this.$filterBlock.closest('form');
        this.$submit = this.$filterBlock.find('.shld_btn_acc');
        this.$reset = this.$filterBlock.find('.shld_btn_dcl');

        this.requestedSnapshot = '';
        this.defaultSnapshot = '';

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

            $("#doctor").iconselectmenu({
                select: _this.onFilterUpdate.bind(_this)
            }).iconselectmenu("menuWidget");

            $("#speс").selectmenu({
                select: _this.onFilterUpdate.bind(_this)
            });


            _this.$reset.click(function(e)
            {
                setTimeout(_this.reset.bind(_this), 100);
            });

            //init form ajax load
            _this.$form.submit(function(e)
            {
                _this.submit();
                e.preventDefault();
            });

            _this.initTimeRange();

            // делаем снимок формы как актуальное состояние (которое не нужно отправлять на сервер)
            _this.defaultSnapshot = _this.getSnapshot();
            _this.requestedSnapshot = _this.defaultSnapshot;
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
                _this.onFilterUpdate();
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

        this.submit();
        this.hideReset();
        this.hideSubmit();
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

    /**
     *
     */
    window.CalendarFilter.prototype.submit = function()
    {
        var self = this;
        var data = this.$form.serialize();
        var snapshot = self.getSnapshot();

        Ajax.load(this.$form.attr('action'), Ajax.getAreaCode(this.$form), data, function()
        {
            self.requestedSnapshot = snapshot;
            self.onFilterUpdate();
        });
    };

    /**
     * Обработчик изменения полей фильтра
     */
    window.CalendarFilter.prototype.onFilterUpdate = function()
    {
        var currentSnapshot = this.getSnapshot();

        if(this.defaultSnapshot === currentSnapshot)
        {
            if(this.requestedSnapshot === this.defaultSnapshot)
            {
                this.onDataUnChangeDefault();
            }
        }
        else
        {
            this.onDataChangeDefault();
        }

        if(this.requestedSnapshot === currentSnapshot)
        {
            this.onDataUnChange();
        }
        else
        {
            this.onDataChange();
        }
    };

    /**
     * Обработчик изменения конфигурации фильтра
     */
    window.CalendarFilter.prototype.onDataChange = function()
    {
        this.showSubmit();
    };

    /**
     * Обработчик отмены изменения конфигурации фильтра. Срабатывает, когда фильтр приводится в ту же конфигурацию, которая
     * была отправлена на сервер в последний раз
     */
    window.CalendarFilter.prototype.onDataUnChange = function()
    {
        this.hideSubmit();
    };

    /**
     * Обработчик изменения конфигурации фильтра с установленной изначально при загрузке страницы
     */
    window.CalendarFilter.prototype.onDataChangeDefault = function()
    {
        this.showReset();
    };

    /**
     * Обработчик отмены изменения конфигурации фильтра с установленной изначально при загрузке страницы.
     * Срабатывает, когда фильтр приводится в ту же конфигурацию, которая была изначально
     */
    window.CalendarFilter.prototype.onDataUnChangeDefault = function()
    {
        this.hideReset();
    };

    window.CalendarFilter.prototype.showReset = function()
    {
        this.$reset.stop().fadeIn(200);
    };
    window.CalendarFilter.prototype.hideReset = function()
    {
        this.$reset.stop().fadeOut(200);
    };

    window.CalendarFilter.prototype.showSubmit = function()
    {
        this.$submit.stop().fadeIn(200);
    };
    window.CalendarFilter.prototype.hideSubmit = function()
    {
        this.$submit.stop().fadeOut(200);
    };

    /**
     * Возвращает "снимок" формы - сериализованная строка тех параметров фильтра, которые может изменять пользователь через поля
     * фильтра. Используется для скрытия / показа кнопок фильтра
     * @returns {string}
     */
    window.CalendarFilter.prototype.getSnapshot = function()
    {
        var snapshot = '';

        this.$form.find('select').each(function()
        {
            var $this = $(this);

            if($this.val())
            {
                snapshot += $this.attr('name') + '=' + $this.val() + '&';
            }
        });

        var timeFrom = this.$form.find('input[name=TIME_FROM]').val();
        if(timeFrom)
        {
            snapshot += 'TIME_FROM=' + timeFrom + '&';
        }

        var timeTo = this.$form.find('input[name=TIME_TO]').val();
        if(timeTo)
        {
            snapshot += 'TIME_TO=' + timeTo + '&';
        }

        return snapshot;
    };

})();