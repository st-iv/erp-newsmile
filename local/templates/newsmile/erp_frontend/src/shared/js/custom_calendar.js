;( function( $, window, document, undefined ) {
	"use strict";

		var pluginName = "customCalendar",
			defaults = {
				propertyName: "value"
			};


		var methods = {
			init : function()
			{
                this.init();
			},
			update : function(options)
			{
				this.update(options);
			},
		};

		function Plugin ( element, options ) {
			this.element = element;
            this._months = {
                0: 'янв',
                1: 'фев',
                2: 'мар',
                3: 'апр',
                4: 'май',
                5: 'июн',
                6: 'июл',
                7: 'авг',
                8: 'сен',
                9: 'окт',
                10: 'ноя',
                11: 'дек'
            };

            this._name = pluginName;
            this.limitPosition = 'top';

            this.settings = $.extend( {}, defaults, options );
            this._defaults = defaults;

            this.init();
		}

		// Avoid Plugin.prototype conflicts
		$.extend( Plugin.prototype, {
			init: function()
			{
			    var _this = this;
                var $header = $('<div class="custCalendar_header">'+
                    '<div>Пн</div>'+
                    '<div>Вт</div>'+
                    '<div>Ср</div>'+
                    '<div>Чт</div>'+
                    '<div>Пт</div>'+
                    '<div>Сб</div>'+
                    '<div>Вс</div>'+
                    '</div>'),
                    $controls_right = $('<div class="custCalendar_controls_right">'+
                        '<div class="custCalendar_month_up day_tooltip" data-toggle="tooltip" data-html="true" title="<div>На месяц назад</div>"></div>'+
                        '<div class="custCalendar_week_up day_tooltip" data-toggle="tooltip" data-html="true" title="<div>На неделю назад</div>"></div>'+
                        '<div class="custCalendar_week_down day_tooltip" data-toggle="tooltip" data-html="true" title="<div>На неделю вперёд</div>"></div>'+
                        '<div class="custCalendar_month_down day_tooltip" data-toggle="tooltip" data-html="true" title="<div>На месяц вперёд</div>"></div>'+
                        '</div>'),
                    $controls_bottom = $('<div class="custCalendar_controls_bottom">'+
                        '<div class="custCalendar_frame"></div>'+
                        '<div class="custCalendar_more"></div>'+
                        '<div class="custCalendar_print"></div>'+
                        '</div>'),
                    $body = $('<div class="custCalendar_body clearfix"></div>');

                $header.prependTo($( this.element ));

                var startD = moment(this.settings.dateFrom),
                    startDWeekday = startD.day(),
                    endD = moment(this.settings.dateTo),
                    endDWeekday = endD.day();

                /*if (startDWeekday !== 1){
                    startD = startD.day(1);
                }
                if (endDWeekday !== 7){
                    endD = endD.day(7);
                }*/


                while (endD.diff(startD, 'days') >= 0) {
                    var curDateString = startD.format('YYYY-MM-DD'),
                        curDate = startD.date(),
                        curMonth = startD.month(),
                        dayData = this.settings.dateData[curDateString];

                    $body.append(
                        $(this.getDayHtml(curDateString, curDate, curMonth, dayData, (curDateString === this.settings.curDate)))
                    );

                    startD.add(1, 'd');
                }

                $body.appendTo($( this.element ));
                $controls_right.appendTo($( this.element ));
                $controls_bottom.appendTo($( this.element ));

                $(this.element).tooltip({
                    selector: '.day_tooltip'
                });

                var scrollbarConfig = {
                    autoHideScrollbar: false,
                    mouseWheel: {
                        scrollAmount: 123
                    },
                    snapAmount: 41,
                    callbacks: {}
                };

                if(typeof _this.settings.callbacks.onScrollEnd === 'function')
                {
                    scrollbarConfig.callbacks.onTotalScroll = function()
                    {
                        _this.limitPosition = 'bottom';
                    };

                    scrollbarConfig.callbacks.onTotalScrollBack = function()
                    {
                        _this.limitPosition = 'top';
                    };

                    scrollbarConfig.callbacks.onScroll = function()
                    {
                        _this.limitPosition = '';
                    }
                }

                $body.mCustomScrollbar(scrollbarConfig);

                $body.on('click', '.custCalendar_day', function(e){
                    var $this = $(e.target);

                    if (!$this.hasClass('active')){
                        $this.siblings('.custCalendar_day.active').removeClass('active');
                        $this.addClass('active');
                    }
                });

                $controls_right.find('.custCalendar_month_up').on('click', function()
                {
                    if(_this.limitPosition == 'top')
                    {
                        _this.settings.callbacks.onScrollEnd(_this.limitPosition, 'month');
                    }
                    else
                    {
                        $body.mCustomScrollbar("scrollTo","+=205");
                    }
                });
                $controls_right.find('.custCalendar_month_down').on('click', function()
                {
                    if(_this.limitPosition == 'bottom')
                    {
                        _this.settings.callbacks.onScrollEnd(_this.limitPosition, 'month');
                    }
                    else
                    {
                        $body.mCustomScrollbar("scrollTo", "-=205");
                    }
                });
                $controls_right.find('.custCalendar_week_up').on('click', function()
                {
                    if(_this.limitPosition == 'top')
                    {
                        _this.settings.callbacks.onScrollEnd(_this.limitPosition, 'week');
                    }
                    else
                    {
                        $body.mCustomScrollbar("scrollTo", "+=41");
                    }
                });
                $controls_right.find('.custCalendar_week_down').on('click', function()
                {
                    if(_this.limitPosition == 'bottom')
                    {
                        _this.settings.callbacks.onScrollEnd(_this.limitPosition, 'week');
                    }
                    else
                    {
                        $body.mCustomScrollbar("scrollTo","-=41");
                    }
                });
			},

			update: function(options)
			{
			    var _this = this;
			    var allowUpdateOps = ['dateData', 'dateTo', 'dateFrom'];

			    for(var optionName in options)
                {
                    if(!allowUpdateOps.includes(optionName))
                    {
                        delete options[optionName];
                    }
                }

                var dateFrom = moment(options.dateFrom ? options.dateFrom : this.settings.dateFrom);
                var dateTo = moment(options.dateTo ? options.dateTo : this.settings.dateTo);

                // обновление dateData (содержание подсказок и цвет)
                if(typeof options['dateData'] != 'object')
                {
                    options['dateData'] = {};
                }

                $(this.element).find('.custCalendar_day').each(function()
                {
                    var dateData;
                    var date = $(this).data('date');
                    var curDateMoment = moment(date);
                    var $dayContent = $(this).find('.custCalendar_day_content');

                    dateData = options['dateData'][date];


                    if(dateData)
                    {
                        $(this).attr('title', _this.getTooltipContent(dateData));
                        $(this).addClass('day_tooltip');

                        $dayContent.css('background-color', '#' + ((dateData.color && dateData.color.BACKGROUND) ? dateData.color.BACKGROUND : _this.defDateColor));
                        $dayContent.css('color', '#' + ((dateData.color && dateData.color.TEXT) ? dateData.color.TEXT : _this.defDateTextColor));
                    }
                    else if(curDateMoment.isBetween(dateFrom, dateTo) || curDateMoment.isSame(dateFrom) || curDateMoment.isSame(dateTo))
                    {
                        $(this).attr('title', '');
                        $(this).data('original-title', '');
                        $(this).removeClass('day_tooltip');
                        $dayContent.css('background-color', '#' + _this.settings.defDateColor);
                        $dayContent.css('color', '#' + _this.settings.defDateTextColor);
                    }
                });

                $.extend(this.settings.dateData, options.dateData);



                var $body = $(this.element).find('.custCalendar_day').first().parent();

                var currentIterDate,
                    newDaysHtml = '',
                    date = '';

                // обновление dateFrom (добавление новый дней)
			    if(options.dateFrom)
                {
                    currentIterDate = moment(options.dateFrom);
                    var currentDateFrom = moment(this.settings.dateFrom);
                    var newWeeksCount = currentDateFrom.diff(currentIterDate, 'days') / 7;

                    while (currentDateFrom.diff(currentIterDate, 'days') > 0)
                    {
                        date = currentIterDate.format('YYYY-MM-DD');

                        newDaysHtml += this.getDayHtml(
                            date,
                            currentIterDate.date(),
                            currentIterDate.month(),
                            this.settings.dateData[date]
                        );

                        currentIterDate.add(1, 'd');
                    }

                    if(newDaysHtml)
                    {
                        $body.prepend(newDaysHtml);

                        // После добавления новых недель перемещаем скролл на прежнее место.
                        // Это делается не через метод плагина скрола, чтобы пользователь не успел заметить перемещение

                        var $scrollCont =  $(this.element).find('.mCSB_container');
                        var newTop = parseInt($scrollCont.css('top')) - 41 * newWeeksCount;
                        $scrollCont.css('top', newTop);

                        this.settings.dateFrom = options.dateFrom;
                    }
                }

                // обновление dateTo (добавление новых дней)
                if(options.dateTo)
                {
                    newDaysHtml = '';
                    currentIterDate = moment(this.settings.dateTo).add(1, 'd');
                    var newDateTo = moment(options.dateTo);

                    while (newDateTo.diff(currentIterDate, 'days') >= 0)
                    {
                        date = currentIterDate.format('YYYY-MM-DD');

                        newDaysHtml += this.getDayHtml(
                            date,
                            currentIterDate.date(),
                            currentIterDate.month(),
                            this.settings.dateData[date]
                        );

                        currentIterDate.add(1, 'd');
                    }

                    if(newDaysHtml)
                    {
                        $body.append(newDaysHtml);
                        this.settings.dateTo = options.dateTo;
                    }
                }

                $(this.element).find('.custCalendar_day').tooltip('dispose');
			},

            getFirstDate: function()
            {
                return $(this.element).find('.custCalendar_day').first().data('date');
            },

            getLastDate: function()
            {
                return $(this.element).find('.custCalendar_day').last().data('date');
            },

			getTooltipContent: function(dayData)
            {
                var result = '';

                result += '<div>Пациентов - ' + Number(dayData.patients) + '</div>';

                if (dayData.timeFree && dayData.timeAvlble){
                    result += '<div>Свободно - ' + dayData.timeFree + ' из ' + dayData.timeAvlble + '</div>';
                }

                return result;
            },

            getDayHtml: function(date, day, month, dayData, isCurrent = false)
            {
                var dayStyle = '';
                var dayTooltip = '';
                var dayClass = '';

                if (dayData){
                    if (dayData.color)
                    {
                        dayStyle = ' style="';

                        if(dayData.color.BACKGROUND)
                        {
                            dayStyle += 'background-color: #' + dayData.color.BACKGROUND + ';';
                        }

                        if(dayData.color.TEXT)
                        {
                            dayStyle += 'color: #' + dayData.color.TEXT + ';';
                        }

                        dayStyle += '"';
                    }
                    if (dayData.timeFree || dayData.timeAvlble || dayData.patients){
                        dayTooltip += ' data-toggle="tooltip" data-html="true" title="';

                        dayTooltip += this.getTooltipContent(dayData) + '"';

                        dayClass += ' day_tooltip'
                    }
                }

                if(isCurrent)
                {
                    dayClass += ' day_current active';
                }

                return '<div class="custCalendar_day '+ dayClass +'" '+ 'data-date="' + date + '"' +
                    dayTooltip +
                    '><div class="custCalendar_day_content"' + dayStyle + '>' +
                    '<div class="custCalendar_day_d">' + day + '</div>' +
                    '<div class="custCalendar_day_m">' + this._months[month] + '</div>' +
                    '</div></div>';
            },

            scroll: function(koef, unit, bInstantly = false)
            {
                koef = Number(koef) * 41;
                if(unit == 'month')
                {
                    koef *= 5;
                }

                var scrollParam = ((koef < 0) ? '-' : '+') + '=' + Math.abs(koef);
                var scrollOptions = {};

                if(bInstantly)
                {
                    scrollOptions.scrollInertia = false;
                }

                $(this.element).find('.custCalendar_body').mCustomScrollbar('scrollTo', scrollParam, scrollOptions);
            }
		});

        $.fn[ pluginName ] = function(method)
        {
            var methods = {
                init: 'init',
                update: 'update',
                firstDate: 'getFirstDate',
                lastDate: 'getLastDate',
                scroll: 'scroll',
            };

            var methodArguments = Array.prototype.slice.call(arguments, 1);
            var result;

            this.each(function()
            {
                var plugin = $.data(this,"plugin_" + pluginName);
                if (!plugin || typeof method === 'object' || !method )
                {
                    $.data(this,"plugin_" + pluginName, new Plugin(this,method));
                }
                else if(methods[method])
                {
                    if(result)
                    {
                        plugin[methods[method]].apply(plugin, methodArguments);
                    }
                    else
                    {
                        result = plugin[methods[method]].apply(plugin, methodArguments);
                    }
                }
                else
                {
                    $.error( 'Метод с именем ' +  method + ' не существует для jQuery.' + pluginName );
                }
            });

            return result;
        };
})(jQuery,window,document);
