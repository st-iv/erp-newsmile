;( function( $, window, document, undefined ) {
	"use strict";
		var pluginName = "customCalendar",
			defaults = {
				propertyName: "value"
			};
		function Plugin ( element, options ) {
			this.element = element;
			this.settings = $.extend( {}, defaults, options );
			this._defaults = defaults;
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
			}
			this._name = pluginName;
			this.init();
		}

		// Avoid Plugin.prototype conflicts
		$.extend( Plugin.prototype, {
			init: function() {
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

				if (startDWeekday !== 1){
					startD = startD.day(1);
				}
				if (endDWeekday !== 7){
					endD = endD.day(7);
				}

				while (endD.diff(startD, 'days') >= 0) {
					var curDateString = startD.format('YYYY-MM-DD'),
					curDate = startD.date(),
					curMonth = startD.month(),
					dayData = this.settings.dateData[curDateString],
					dayTooltip = '',
					dayClass = '',
					dayStyle = '';

					if (dayData){
						if (dayData.color){
							dayStyle = ' style="background-color: #' + dayData.color + ';"';
						}
						if (dayData.timeFree || dayData.timeAvlble || dayData.patients){
							dayTooltip += ' data-toggle="tooltip" data-html="true" title="';
							
							if (dayData.patients){
								dayTooltip += '<div>Пациентов - ' + dayData.patients + '</div>';
							}
							if (dayData.timeFree && dayData.timeAvlble){
								dayTooltip += '<div>Свободно - ' + dayData.timeFree + ' из ' + dayData.timeAvlble + '</div>';
							}		
							dayTooltip += '"';	

							dayClass += ' day_tooltip'			
						}
					}

					if(curDateString === this.settings.curDate){
						dayClass += ' day_current';
					}

					$body.append($('<div class="custCalendar_day '+ dayClass +'" '+
						dayTooltip +
						'><div' + dayStyle + '>' +
						'<div class="custCalendar_day_d">' + curDate + '</div>' +
						'<div class="custCalendar_day_m">' + this._months[curMonth] + '</div>' +
					'</div></div>'));	

					startD.add(1, 'd');
				}

				$body.appendTo($( this.element ));
				$controls_right.appendTo($( this.element ));
				$controls_bottom.appendTo($( this.element ));

				$(this.element).tooltip({
				    selector: '.day_tooltip'
				});

				$body.mCustomScrollbar({
					autoHideScrollbar: false,
					mouseWheel: { 
						scrollAmount: 123 
					},
					snapAmount: 41
				});

				$body.find('.custCalendar_day').on('click', function(){
					var $this = $(this);

					if (!$this.hasClass('active')){
						$this.siblings('.custCalendar_day.active').removeClass('active');
						$this.addClass('active');
					}
				});

				$controls_right.find('.custCalendar_month_up').on('click', function(){
					$body.mCustomScrollbar("scrollTo","+=205");
				});
				$controls_right.find('.custCalendar_month_down').on('click', function(){
					$body.mCustomScrollbar("scrollTo","-=205");
				});
				$controls_right.find('.custCalendar_week_up').on('click', function(){
					$body.mCustomScrollbar("scrollTo","+=41");
				});
				$controls_right.find('.custCalendar_week_down').on('click', function(){
					$body.mCustomScrollbar("scrollTo","-=41");
				});
			},
			yourOtherFunction: function( text ) {

				// some logic
				
			}
		});
		$.fn[ pluginName ] = function(options) {
			return this.each(function(){
				if (!$.data(this,"plugin_" + pluginName)){
					$.data(this,"plugin_" + pluginName, new Plugin(this,options));
				}
			});
		};
})(jQuery,window,document);